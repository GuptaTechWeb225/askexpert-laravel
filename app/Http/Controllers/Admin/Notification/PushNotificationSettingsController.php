<?php

namespace App\Http\Controllers\Admin\Notification;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\NotificationMessageRepositoryInterface;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Enums\ViewPaths\Admin\PushNotification;
use App\Http\Controllers\BaseController;
use App\Services\PushNotificationService;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PushNotificationSettingsController extends BaseController
{

    /**
     * @param BusinessSettingRepositoryInterface $businessSettingRepo
     * @param NotificationMessageRepositoryInterface $notificationMessageRepo
     * @param PushNotificationService $pushNotificationService
     * @param TranslationRepositoryInterface $translationRepo
     */
    public function __construct(
        private readonly BusinessSettingRepositoryInterface     $businessSettingRepo,
        private readonly NotificationMessageRepositoryInterface $notificationMessageRepo,
        private readonly PushNotificationService                $pushNotificationService,
            )
    {
    }

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View Index function is the starting point of a controller
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getFirebaseConfigurationView();
    }

    /**
     * @return View
     */
 
    /**
     * @param $userType
     * @return Collection
     */
  

    /**
     * @return View
     */
    public function getFirebaseConfigurationView(): View
    {
        $pushNotificationKey = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'push_notification_key'])->value ?? '';
        $configData = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'fcm_credentials'])->value ?? '';
        $projectId = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'fcm_project_id'])->value ?? '';
        return view(PushNotification::FIREBASE_CONFIGURATION[VIEW], [
            'pushNotificationKey' => $pushNotificationKey,
            'projectId' => $projectId,
            'configData' => json_decode($configData),
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws Exception
     */
    public function getFirebaseConfigurationUpdate(Request $request): RedirectResponse
    {
        $this->businessSettingRepo->updateOrInsert(type: 'fcm_project_id', value: $request['fcm_project_id']);
        $this->businessSettingRepo->updateOrInsert(type: 'push_notification_key', value: $request['push_notification_key']);

        $configData = $this->pushNotificationService->getFCMCredentialsArray(request: $request);
        $this->pushNotificationService->firebaseConfigFileGenerate(config: $configData);
        $this->businessSettingRepo->updateOrInsert(type: 'fcm_credentials', value: json_encode($configData));
        clearWebConfigCacheKeys();

        Toastr::success(translate('settings_updated'));
        return back();
    }

}
