<?php

namespace App\Mail;

use App\Models\RestaurantMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RestaurantCustomMail extends Mailable
{
    use Queueable, SerializesModels;

    public $mailData;
    public $user;

    public function __construct(RestaurantMail $mailData, User $user)
    {
        $this->mailData = $mailData;
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject($this->mailData->subject)
            ->view('restaurant-views.emails.custom')
            ->with([
                'mailData' => $this->mailData,
                'user' => $this->user,
                'imageUrl' => $this->mailData->image ? asset('storage/restaurant-mails/' . $this->mailData->image) : null,
                'restaurant' => $this->mailData->restaurant,

            ]);
    }
}
