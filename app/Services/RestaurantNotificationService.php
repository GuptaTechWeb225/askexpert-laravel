<?php

namespace App\Services;

use App\Traits\FileManagerTrait;

class RestaurantNotificationService
{
    use FileManagerTrait;
    public function getNotificationAddData(object $request, $id ,$name): array
    {
        $image = $request['image'] ? $this->upload(dir: 'notification/', format: 'webp', image: $request->file('image')) : '';
        return [
            'title' => $request['title'],
            'description' => $request['description'],
            'sent_to' => 'customer',
            'sent_by' => $name,
            'image' => $image,
            'restaurant_id' => $id,
            'notification_count' => 1
        ];
    }
    public function getNotificationUpdateData(object $request, string|null $notificationImage): array
    {
        $image = $request['image'] ? $this->update(dir: 'notification/', oldImage: $notificationImage, format: 'webp', image: $request->file('image')) : $notificationImage;
        return [
            'title' => $request['title'],
            'description' => $request['description'],
            'sent_to' => $request['sent_to'],
            'image' => $image,
        ];
    }
}
