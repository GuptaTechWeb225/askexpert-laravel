<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExpertRejectedMail extends Mailable
{
    public $expert;
    public $reason;

    public function __construct($expert, $reason)
    {
        $this->expert = $expert;
        $this->reason = $reason;
    }

    public function build()
    {
        return $this->subject('Your Expert Application Status')
            ->view('email-templates.rejected');
    }
}