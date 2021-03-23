<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GudExamMail extends Mailable
{
    use Queueable, SerializesModels;
    public $details;

    public function __construct($details)
    {
        $this->details = $details;
        //dd($details);
    }

    public function build()
    {
        return $this->subject('Welcome To Gudexams')
            ->view('emails.registration')
            ->from('gudexams@gmail.com');
    }
}
