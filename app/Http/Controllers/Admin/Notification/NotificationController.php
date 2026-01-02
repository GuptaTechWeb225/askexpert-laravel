<?php

namespace App\Http\Controllers\Admin\Notification;

use App\Contracts\Repositories\NotificationRepositoryInterface;
use App\Enums\ViewPaths\Admin\Notification;
use App\Enums\WebConfigKey;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\NotificationRequest;
use App\Services\NotificationService;
use App\Traits\FileManagerTrait;
use App\Traits\PushNotificationTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class NotificationController extends BaseController
{
    use PushNotificationTrait, FileManagerTrait {
        delete as deleteFile;
    }

    /**
     * @param NotificationRepositoryInterface $notificationRepo
     * @param NotificationService $notificationService
     */
    public function __construct(
        private readonly NotificationRepositoryInterface $notificationRepo,
        private readonly NotificationService             $notificationService,
    ) {}

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View|Collection|LengthAwarePaginator|callable|RedirectResponse|null
     */
    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return $this->getNotificationView(request: $request);
    }

    /**
     * @param $request
     * @return View
     */
    public function getNotificationView($request): View
    {
        $searchValue = $request['searchValue'];
        $notifications = $this->notificationRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $searchValue,
            filters: ['sent_to' => 'customer'],
            dataLimit: getWebConfig(WebConfigKey::PAGINATION_LIMIT),
        );
        return view(Notification::INDEX[VIEW], compact('searchValue', 'notifications'));
    }

    /**
     * @param NotificationRequest $request
     * @return RedirectResponse
     */
    public function add(NotificationRequest $request): RedirectResponse
    {
        if (env('APP_MODE') === 'demo') {
            Toastr::info(translate('push_notification_is_disable_for_demo'));
            return back();
        }

        $notification = $this->notificationRepo->add(
            data: $this->notificationService->getNotificationAddData(request: $request)
        );

        $title = $notification->title ?? "New Notification";
        $body  = $notification->description ?? "You have a new message";

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('public/notifications');
            $imageUrl = asset(str_replace('public/', 'storage/', $path));
        }

        try {
            if ($notification->sent_to === 'user') {
                $tokens = DB::table('users')
                    ->whereNotNull('cm_firebase_token')
                    ->pluck('cm_firebase_token')
                    ->unique() // ✅ duplicate tokens hata de
                    ->toArray();

                foreach ($tokens as $token) {
                    $this->sendPushNotificationV1($token, $title, $body, $imageUrl);
                }
            } elseif ($notification->sent_to === 'restaurant') {
                $tokens = DB::table('restaurants')
                    ->whereNotNull('cm_firebase_token')
                    ->pluck('cm_firebase_token')
                    ->unique() // ✅ duplicate tokens hata de
                    ->toArray();

                foreach ($tokens as $token) {
                    $this->sendPushNotificationV1($token, $title, $body, $imageUrl);
                }
            } else {
                $userTokens = DB::table('users')
                    ->whereNotNull('cm_firebase_token')
                    ->pluck('cm_firebase_token')
                    ->toArray();

                $restaurantTokens = DB::table('restaurants')
                    ->whereNotNull('cm_firebase_token')
                    ->pluck('cm_firebase_token')
                    ->toArray();

                // ✅ duplicate hatane ke liye unique
                $tokens = collect(array_merge($userTokens, $restaurantTokens))
                    ->unique()
                    ->toArray();

                foreach ($tokens as $token) {
                    $this->sendPushNotificationV1($token, $title, $body, $imageUrl);
                }
            }
        } catch (\Exception $e) {
            Log::error("❌ Notification sending failed", ['error' => $e->getMessage()]);
            Toastr::warning(translate('push_notification_failed'));
        }

        Toastr::success(translate('notification_sent_successfully'));
        return redirect()->back();
    }

    /**
     * @param string|int $id
     * @return View
     */
    public function getUpdateView(string|int $id): View
    {
        $notification = $this->notificationRepo->getFirstWhere(params: ['id' => $id]);
        return view(Notification::UPDATE[VIEW], compact('notification'));
    }

    /**
     * @param NotificationRequest $request
     * @param string|int $id
     * @return RedirectResponse
     */
    public function update(NotificationRequest $request, string|int $id): RedirectResponse
    {
        $notification = $this->notificationRepo->getFirstWhere(params: ['id' => $id]);
        $this->notificationRepo->update(
            id: $notification['id'],
            data: $this->notificationService->getNotificationUpdateData(
                request: $request,
                notificationImage: $notification['image']
            )
        );
        Toastr::success(translate('notification_updated_successfully'));
        return redirect()->route(Notification::INDEX[ROUTE]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateStatus(Request $request): JsonResponse
    {
        $notification = $this->notificationRepo->getFirstWhere(params: ['id' => $request['id']]);
        $this->notificationRepo->update(id: $notification['id'], data: ['status' => $request['status']]);
        return response()->json($request['status']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $notification = $this->notificationRepo->getFirstWhere(params: ['id' => $request['id']]);
        $this->deleteFile('/notification/' . $notification['image']);
        $this->notificationRepo->delete(params: ['id' => $notification['id']]);
        return response()->json();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
   public function resendNotification(Request $request): JsonResponse
{
    if (env('APP_MODE') === 'demo') {
        return response()->json([
            'success' => false,
            'message' => translate("push_notification_is_disable_for_demo")
        ]);
    }

    try {
        $notificationModel = $this->notificationRepo->getFirstWhere(params: ['id' => $request['id']]);
        if (!$notificationModel) {
            return response()->json([
                'success' => false,
                'message' => translate("user_notification_not_exist")
            ]);
        }

        $notification = $notificationModel->toArray();
        $title = $notification['title'] ?? "New Notification";
        $body  = $notification['description'] ?? "You have a new message";

        $imageUrl = $notification['image'] 
            ? asset('storage/notifications/' . $notification['image']) 
            : null;

        if ($notification['sent_to'] === 'user') {
            $tokens = DB::table('users')
                ->whereNotNull('cm_firebase_token')
                ->pluck('cm_firebase_token')
                ->unique() // ✅ duplicate hataya
                ->toArray();

            foreach ($tokens as $token) {
                $this->sendPushNotificationV1($token, $title, $body, $imageUrl);
            }
        } elseif ($notification['sent_to'] === 'restaurant') {
            $tokens = DB::table('restaurants')
                ->whereNotNull('cm_firebase_token')
                ->pluck('cm_firebase_token')
                ->unique() // ✅ duplicate hataya
                ->toArray();

            foreach ($tokens as $token) {
                $this->sendPushNotificationV1($token, $title, $body, $imageUrl);
            }
        } else {
            $userTokens = DB::table('users')
                ->whereNotNull('cm_firebase_token')
                ->pluck('cm_firebase_token')
                ->toArray();

            $restaurantTokens = DB::table('restaurants')
                ->whereNotNull('cm_firebase_token')
                ->pluck('cm_firebase_token')
                ->toArray();

            $tokens = collect(array_merge($userTokens, $restaurantTokens))
                ->unique() // ✅ sab duplicate hataya
                ->toArray();

            foreach ($tokens as $token) {
                $this->sendPushNotificationV1($token, $title, $body, $imageUrl);
            }
        }

        // ✅ Increase resend counter
        $count = $notificationModel->notification_count + 1;
        $this->notificationRepo->update(
            id: $notificationModel->id,
            data: ['notification_count' => $count]
        );

        return response()->json([
            'success' => true,
            'message' => translate("push_notification_successfully")
        ]);
    } catch (\Exception $e) {
        Log::error("❌ Resend Notification Failed", [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'success' => false,
            'message' => translate("push_notification_failed")
        ]);
    }
}

}
