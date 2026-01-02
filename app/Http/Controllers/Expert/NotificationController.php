<?php

namespace App\Http\Controllers\Restaurant;

use App\Contracts\Repositories\RestaurantNotificationRepositoryInterface;
use App\Contracts\Repositories\ShopRepositoryInterface;
use App\Http\Controllers\BaseController;
use App\Repositories\NotificationSeenRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\RestaurantNotification;
use App\Models\Restaurant;
use App\Models\RestaurantMail;
use App\Models\PurchasedPlan;
use App\Models\User;
use App\Traits\FileManagerTrait;
use App\Traits\PushNotificationTrait;
use App\Services\RestaurantNotificationService;
use Illuminate\Support\Facades\Log;
use App\Enums\ViewPaths\Restaurant\Notification;
use App\Enums\WebConfigKey;
use App\Http\Requests\Restaurant\RestaurantNotificationRequest;
use Brian2694\Toastr\Facades\Toastr;
use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Events\RestaurantMailEvent;
use Illuminate\Support\Facades\Mail;

class NotificationController extends BaseController
{


    use PushNotificationTrait, FileManagerTrait {
        delete as deleteFile;
    }
    /**
     * @param RestaurantNotificationRepositoryInterface $notificationRepo
     * @param NotificationSeenRepository $notificationSeenRepo
     */
    public function __construct(
        private readonly RestaurantNotificationRepositoryInterface $notificationRepo,
        private readonly NotificationSeenRepository $notificationSeenRepo,
        private readonly RestaurantNotificationService             $notificationService,
        private readonly CustomerRepositoryInterface             $customerRepo,

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
     * @param RestaurantNotificationRequest $request
     * @return JsonResponse
     */
    public function getNotificationModalView(Request $request): JsonResponse
    {
        $restaurantId = auth('restaurant')->id();

        $notifications = RestaurantNotification::where('restaurant_id', $restaurantId)
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    public function getNotificationView($request): View
    {
        $searchValue = $request['searchValue'];
        $notifications = $this->notificationRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $searchValue,
            dataLimit: getWebConfig(WebConfigKey::PAGINATION_LIMIT),
        );
        return view(Notification::INDEX[VIEW], compact('searchValue', 'notifications'));
    }
    public function mailView(Request $request): View
    {
        $restaurantId = auth('restaurant')->id();
        $searchValue = $request->input('searchValue');

        $notifications = RestaurantMail::where('restaurant_id', $restaurantId)
            ->when($searchValue, function ($query, $searchValue) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('subject', 'like', "%{$searchValue}%")
                        ->orWhere('body', 'like', "%{$searchValue}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(getWebConfig(WebConfigKey::PAGINATION_LIMIT))
            ->appends(['searchValue' => $searchValue]);


        $customers = $this->customerRepo->getListWhereBetween(
            searchValue: $request['searchValue'],
            whereBetween: 'created_at',
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT),
            appends: $request->all(),
        );
        return view(Notification::MAIL_INFO[VIEW], compact('searchValue', 'notifications', 'customers'));
    }

    /**
     * @param RestaurantNotificationRequest $request
     * @return RedirectResponse
     */
    public function add(RestaurantNotificationRequest $request): RedirectResponse
    {
        $id = auth('restaurant')->id();
        $restaurant = Restaurant::find($id);
        $name = $restaurant->restaurant_name;

        // ✅ Check Plan Quota
        $plan = PurchasedPlan::where('restaurant_id', $id)
            ->where('payment_status', 'paid')
            ->whereRaw('(push_notification_total - push_notification_used) >= 1')
            ->first();

        if (!$plan) {
            Toastr::error(translate('You do not have any push notifications left in your plan.'));
            return redirect()->back();
        }

        $notification = $this->notificationRepo->add(
            data: $this->notificationService->getNotificationAddData($request, $id, $name)
        );

        try {
            $title = $request->title;
            $body = $request->description;
            $imageUrl = $notification->image ? asset('storage/notification/' . $notification->image) : null;

            // ✅ Send to ALL customers
            $tokens = User::whereNotNull('cm_firebase_token')->pluck('cm_firebase_token')->toArray();
            foreach ($tokens as $token) {
                $this->sendPushNotificationV1($token, $title, $body, $imageUrl);
            }
            Log::info("📩 Notification sent to ALL customers", ['count' => count($tokens)]);


            $plan->increment('push_notification_used', 1);
        } catch (\Exception $e) {
            Log::error("❌ Notification sending failed", ['error' => $e->getMessage()]);
            Toastr::warning(translate('push_notification_failed'));
            return redirect()->back();
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
     * @param RestaurantNotificationRequest $request
     * @param string|int $id
     * @return RedirectResponse
     */
    public function update(RestaurantNotificationRequest $request, string|int $id): RedirectResponse
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
    public function mailDelete(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $mail = RestaurantMail::findOrFail($id);
        $mail->delete();

        return response()->json([
            'status' => true,
            'message' => 'Mail deleted successfully.'
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function resendNotification(Request $request): JsonResponse
    {
        $restaurantId = auth('restaurant')->id();
        $data = [];

        try {
            $plan = PurchasedPlan::where('restaurant_id', $restaurantId)
                ->where('payment_status', 'paid')
                ->whereRaw('(push_notification_total - push_notification_used) >= 1')
                ->first();

            if (!$plan) {
                return response()->json([
                    'success' => false,
                    'message' => translate("You do not have any push notifications left in your plan."),
                    'mails_left' => 0,
                ], 400);
            }

            $notification = $this->notificationRepo->getFirstWhere(params: ['id' => $request['id']]);

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => translate("Notification not found."),
                ], 404);
            }

            $title = $notification->title;
            $body = $notification->description;
            $imageUrl = $notification->image ? asset('storage/notification/' . $notification->image) : null;

            $tokens = User::whereNotNull('cm_firebase_token')->pluck('cm_firebase_token')->toArray();
            foreach ($tokens as $token) {
                $this->sendPushNotificationV1($token, $title, $body, $imageUrl);
            }
            Log::info("📩 Resent notification to ALL customers", [
                'notification_id' => $notification->id,
                'count' => count($tokens)
            ]);

            $plan->increment('push_notification_used', 1);

            $count = $notification->notification_count + 1;
            $this->notificationRepo->update(id: $notification->id, data: ['notification_count' => $count]);

            $data['success'] = true;
            $data['message'] = translate("push_notification_successfully");
        } catch (\Exception $e) {
            Log::error("❌ Resend notification failed", ['error' => $e->getMessage()]);
            $data['success'] = false;
            $data['message'] = translate("push_notification_failed");
        }

        return response()->json($data);
    }


    public function mailStore(Request $request)
    {
        $request->validate([
            'subject' => 'required|string',
            'body' => 'required|string',
            'sent_to' => 'required|in:all,selected',
            'receiver_ids' => 'nullable|array',
            'image' => 'nullable|image',
        ]);

        $restaurant = auth('restaurant')->user();

        if ($request->sent_to === 'all') {
            $receiverCount = User::count(); // ya restaurant ke hi customers count
        } else {
            $receiverCount = count($request->receiver_ids ?? []);
        }

        if ($receiverCount <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => translate('No receivers selected.')
            ], 400);
        }

        $plan = PurchasedPlan::where('restaurant_id', $restaurant->id)
            ->where('payment_status', 'paid')
            ->latest()
            ->first();

        if (!$plan) {
            return response()->json([
                'status' => 'error',
                'message' => translate('You do not have any active mail plan.')
            ], 400);
        }

        $mailsLeft = $plan->mail_total - $plan->mail_used;

        if ($mailsLeft < $receiverCount) {
            return response()->json([
                'status' => 'error',
                'message' => translate("You do not have enough mails left in your plan. Buy an addon email plan for this feature"),
                'mails_left' => $mailsLeft,
                'needed' => $receiverCount
            ], 400);
        }

        $data = $request->only(['subject', 'body', 'sent_to']);
        $data['restaurant_id'] = $restaurant->id;
        $data['receiver_ids'] = $request->sent_to === 'selected' ? json_encode($request->receiver_ids) : null;
        $data['status'] = 'pending';

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('restaurant-mails', 'public');
            $data['image'] = basename($path);
        }

        $mail = RestaurantMail::create($data);

        $plan->increment('mail_used', $receiverCount);

        event(new RestaurantMailEvent($mail));

        return response()->json([
            'status' => 'success',
            'message' => translate("Mail queued for $receiverCount customers."),
            'mails_left' => $plan->mail_total - $plan->mail_used
        ]);
    }
}
