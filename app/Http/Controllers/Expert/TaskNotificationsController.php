<?php

namespace App\Http\Controllers\Expert;

use App\Enums\ViewPaths\Expert\Notifications;
use App\Contracts\Repositories\AdminNotificationRepositoryInterface;
use App\Http\Controllers\BaseController;
use App\Traits\CommonTrait;
use App\Traits\PaginatorTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskNotificationsController extends BaseController
{
    use PaginatorTrait;
    use CommonTrait;

    public function __construct(
        private readonly AdminNotificationRepositoryInterface  $notifRepo,
    ) {}

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getListView($request);
    }

  public function getNotifView(Request $request): View
    {
        $notifications = $this->notifRepo->getAllForExpert(auth('expert')->id(), [0, 1], 10);
        return view(Notifications::LIST[VIEW], compact('notifications'));
    }

    /** Full list (paginated) */
    public function list(Request $request): View
    {

        $paginate = getWebConfig('pagination_limit');
        $notifications = $this->notifRepo->getAllForExpert(
            auth('expert')->id(),
            $request->all(),
            $paginate
        );
        return view(Notifications::LIST[VIEW], compact('notifications'));
    }

    public function view(string $id): RedirectResponse
    {
        $notif = $this->notifRepo->markAsRead($id);
        return redirect($notif->link ?? route('admin.dashboard.index'));
    }

    public function show(string $id): View
    {
        $notif = $this->notifRepo->find($id);
        return view(Notifications::VIEW[VIEW], compact('notif'));
    }

}
