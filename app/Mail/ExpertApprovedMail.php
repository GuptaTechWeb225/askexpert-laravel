<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExpertApprovedMail extends Mailable
{
    public $expert;

    public function __construct($expert)
    {
        $this->expert = $expert;
    }

    public function build()
    {
        return $this->subject('Your Expert Application is Approved 🎉')
            ->view('email-templates.approved');
    }
}