<?php

namespace App\Services;

use App\Traits\FileManagerTrait;

class BoostPlanService
{
    use FileManagerTrait;


    public function getPlanData(object $request, string $oldImage = null): array
    {
        // Handle image upload/update
        if ($oldImage) {
            $imageName = $request->file('image')
                ? $this->update(dir: 'plan/', oldImage: $oldImage, format: 'webp', image: $request->file('image'))
                : $oldImage;
        } else {
            $imageName = $request->file('image')
                ? $this->upload(dir: 'plan/', format: 'webp', image: $request->file('image'))
                : null;
        }

        // Base data
        $data = [
            'plan_name'          => $request->name,
            'code'          => $request->code,
            'type'          => 'boost',
            'plan_type'          => 'paid',
            'boost_type'    => $request->boost_type,
            'price'         => $request->price,
            'description'   => $request->description,
            'plan_duration' => $request->duration,
            'image'         => $imageName,
            'status'        => true,
        ];

        // Extra fields based on boost_type
        if ($request->boost_type === 'Notification') {
            $data['push_notification_count'] = $request->notification_count;
        } elseif ($request->boost_type === 'Boost') {
            $data['boost_duration'] = $request->boost_count;
        } elseif ($request->boost_type === 'Email') {
            $data['mail_count'] = $request->email_count;
        }

        return $data;
    }


    public function deleteImage(object|null $data): bool
    {
        if ($data && $data['image']) {
            $this->delete('profile/' . $data['image']);
        };
        return true;
    }
}
