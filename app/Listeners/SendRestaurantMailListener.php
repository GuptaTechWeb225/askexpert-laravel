<?php

namespace App\Listeners;

use App\Events\RestaurantMailEvent;
use App\Mail\RestaurantCustomMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendRestaurantMailListener implements ShouldQueue
{
    public function handle(RestaurantMailEvent $event): void
    {
        $mail = $event->mail;

        if ($mail->sent_to === 'all') {
            $users = User::where('is_active', 1)->get();
        } elseif ($mail->sent_to === 'selected') {
            $ids = json_decode($mail->receiver_ids, true) ?? [];
            $users = User::whereIn('id', $ids)->get();
        } else {
            $users = collect();
        }

        foreach ($users as $user) {
            Mail::to($user->email)->send(new RestaurantCustomMail($mail, $user));
        }


        $companyEmail = getWebConfig(name: 'company_email');
        if (!empty($companyEmail)) {
            $dummyUser = new User([
                'name' => 'Company',
                'email' => $companyEmail,
            ]);
            Mail::to($companyEmail)->send(new RestaurantCustomMail($mail, $dummyUser));
        }
        $mail->update(['status' => 'sent']);
    }
}
