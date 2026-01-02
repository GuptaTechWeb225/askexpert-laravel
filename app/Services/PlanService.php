<?php

namespace App\Services;

use App\Traits\FileManagerTrait;

class PlanService
{
    use FileManagerTrait;


    public function getPlanData(object $request, string $oldImage = null): array
    {
        // Handle image upload/update
        if ($oldImage) {
            $imageName = $request->file('image') ? $this->update(dir: 'plan/', oldImage: $oldImage, format: 'webp', image: $request->file('image')) : $oldImage;
        } else {
            $imageName = $request->file('image') ? $this->upload(dir: 'plan/', format: 'webp', image: $request->file('image')) : null;
        }

        return [
            'plan_name' => $request->name,
            'description' => $request->description,
            'code' => $request->code,
            'type' => 'membership',
            'plan_type' => $request->plan_type,
            'price' => $request->price,
            'boost_duration' => $request->boosts,
            'push_notification_count' => $request->push_notification_count,
            'plan_duration' => $request->duration,
            'mail_count' => $request->mail_campaign_count,
            'plan_expiry_reminder' => $request->plan_expiry_reminder,
            'plan_addons' => $request->addons ?? [],
            'image' => $imageName,
            'status' => true, 
        ];
    }

    public function deleteImage(object|null $data): bool
    {
        if ($data && $data['image']) {
            $this->delete('profile/' . $data['image']);
        };
        return true;
    }
}
