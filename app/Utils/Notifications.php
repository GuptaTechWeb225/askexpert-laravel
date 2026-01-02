<?php

namespace App\Utils;

use App\Traits\PdfGenerator;
use App\Traits\CommonTrait;
use App\Contracts\Repositories\AdminNotificationRepositoryInterface;

class Notifications
{
    use CommonTrait, PdfGenerator;

    protected static function repo(): AdminNotificationRepositoryInterface
    {
        return app(AdminNotificationRepositoryInterface::class);
    }

    // Get notifications for a specific employee (admin/expert)
    public static function getNotifications($userId, $statuses = [0])
    {
        return self::repo()->getForAdmin($userId, $statuses);
    }
    public static function getExpertNotifications($userId, $statuses = [0])
    {
        return self::repo()->getForExpert($userId, $statuses);
    }

    // Get notifications for a user (general employee)
    public static function getUserNotifications($userId, $limit = 10)
    {
        return self::repo()->getForUser($userId, $limit);
    }

    // Get department notifications
    public static function getDepartmentNotifications($departmentId, $statuses = [0])
    {
        return self::repo()->getUnreadForDepartment($departmentId);
    }

    // Create a new notification
    public static function notify($userId, $title, $message, $type = 'general', $relatedId = null, $fromUserId = null)
    {
        $data = [
            'user_id' => $userId,
            'from_user_id' => $fromUserId ?? auth('admin')->id(),
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'related_id' => $relatedId,
        ];

        return self::repo()->create($data);
    }

    // Mark a notification as read
    public static function markAsRead($id)
    {
        return self::repo()->markAsRead($id);
    }

    // Bulk notify recipients
    public static function notifyRecipients($notifiableId, string $notifiableType, string $title, string $message, array $recipients)
    {
        return self::repo()->notifyRecipients($notifiableId, $notifiableType, $title, $message, $recipients);
    }

    // Get unread notifications for an employee
    public static function getUnreadForEmployee(int $employeeId)
    {
        return self::repo()->getUnreadForEmployee($employeeId);
    }

    // Get unread notifications for a customer
    public static function getUnreadForCustomer(int $customerId)
    {
        return self::repo()->getUnreadForCustomer($customerId);
    }

    // Get notifications for a customer
    public static function getForCustomer(int $customerId, array $statuses = [0,1], int $limit = 50)
    {
        return self::repo()->getForCustomer($customerId, $statuses, $limit);
    }
}
