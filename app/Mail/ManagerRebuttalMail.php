<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ManagerRebuttalMail extends Mailable
{
    use Queueable, SerializesModels;
    public $mailHeader;
    public $mailBody;
    public $reportingPerson;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mailHeader, $mailBody, $reportingPerson)
    {
        $this->mailHeader = $mailHeader;
        $this->mailBody = $mailBody;
        $this->reportingPerson = $reportingPerson;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject("Pro-Code - ".$this->mailHeader)->view('emails.managerRebuttal');
    }
}
