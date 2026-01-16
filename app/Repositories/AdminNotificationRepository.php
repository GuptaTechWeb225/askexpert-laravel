<?php

namespace App\Repositories;

use App\Models\AdminNotification;
use App\Contracts\Repositories\AdminNotificationRepositoryInterface;
use Illuminate\Support\Collection; // <-- add this
class AdminNotificationRepository implements AdminNotificationRepositoryInterface
{
    protected $model;

    public function __construct(AdminNotification $model)
    {
        $this->model = $model;
    }

    /* -------------------------------------------------
     *  Basic CRUD
     * ------------------------------------------------*/
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function find($id)
    {
        return $this->model->findOrFail($id);
    }

    public function markAsRead($id)
    {
        $notification = $this->find($id);
        $notification->status = 1;
        $notification->save();
        return $notification;
    }

    /* -------------------------------------------------
     *  Unread helpers (per type)
     * ------------------------------------------------*/
    public function getUnreadForEmployee(int $employeeId): Collection
    {
        return $this->model
            ->forEmployee($employeeId)
            ->unread()
            ->get();
    }

    public function getUnreadForDepartment(int $departmentId): Collection
    {
        return $this->model
            ->forDepartment($departmentId)
            ->unread()
            ->get();
    }

    public function getUnreadForCustomer(int $customerId): Collection
    {
        return $this->model
            ->forCustomer($customerId)
            ->unread()
            ->get();
    }

    /* -------------------------------------------------
     *  Bulk notify (employee / department / customer)
     * ------------------------------------------------*/
    /**
     * @param  int|string  $notifiableId
     * @param  string      $notifiableType  // morph class (e.g. App\Models\SupportTicket::class)
     * @param  string      $title
     * @param  string      $message
     * @param  string|null $link
     * @param  array       $recipients   // each = ['type'=>'employee|department|customer', 'id'=>X]
     * @return Collection
     */
    public function notifyRecipients(
        $notifiableId,
        string $notifiableType,
        string $title,
        string $message,
        array $recipients
    ): Collection {
        $created = collect();

        foreach ($recipients as $recipient) {
            $data = [
                'notifiable_id'   => $notifiableId,
                'notifiable_type' => $notifiableType,
                'title'           => $title,
                'message'         => $message,
                'status'          => 0,
                'is_active'       => true,
            ];

            switch ($recipient['type']) {
                case 'expert':
                    $data['notification_for'] = 1;
                    $data['expert_id']      = $recipient['id'];
                    break;
                case 'customer':
                    $data['notification_for'] = 2;
                    $data['customer_id']      = $recipient['id'];
                    break;
                case 'admin':
                    $data['notification_for'] = 3;
                    $data['admin_id']      = $recipient['id'];
                    break;
                default:
                    continue 2;
            }
            $created->push($this->create($data));
        }

        return $created;
    }
    public function getForExpert(int $expertId, array $statuses = [0], int $limit = 50): Collection
    {
        return $this->model
            ->where(function ($q) use ($expertId) {
                // personal
                $q->where('notification_for', 1)
                    ->where('expert_id', $expertId);
            })
            ->whereIn('status', $statuses)
            ->whereDate('created_at', today())
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
    public function getForAdmin(int $adminId, array $statuses = [0], int $limit = 50): Collection
    {
        return $this->model
            ->forAdmin($adminId)
            ->whereIn('status', $statuses)
            ->whereDate('created_at', today())
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
    public function getForUser(int $userId, int $limit = 50): Collection
    {
        return $this->model
            ->where(function ($q) use ($userId) {
                $q->where('notification_for', 2)
                    ->where('customer_id', $userId);
            })
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function getAllForAdmin(int $adminId, array $filters = [], int $paginate = 20)
    {
        $query = $this->model->where(function ($q) use ($adminId) {
            $q->where('notification_for', 3)->where('admin_id', $adminId)
                ->orWhere('notification_for', 1);
        });
        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%')
                ->orWhere('message', 'like', '%' . $filters['search'] . '%');
        }

        return $query->orderByDesc('created_at')->paginate($paginate);
    }
   public function getAllForExpert(int $expertId, array $filters = [], int $paginate = 20)
{
    $query = $this->model
        ->where('notification_for', 1)
        ->where('expert_id', $expertId);

    if (!empty($filters['search'])) {
        $query->where(function ($q) use ($filters) {
            $q->where('title', 'like', '%' . $filters['search'] . '%')
              ->orWhere('message', 'like', '%' . $filters['search'] . '%');
        });
    }

    return $query
        ->orderByDesc('created_at')
        ->paginate($paginate);
}

    public function getForCustomer(int $customerId, array $statuses = [0, 1], int $limit = 50): Collection
    {
        return $this->model
            ->forCustomer($customerId)
            ->whereIn('status', $statuses)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
