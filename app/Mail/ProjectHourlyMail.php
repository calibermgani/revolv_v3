<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProjectHourlyMail extends Mailable
{
    use Queueable, SerializesModels;
    public $mailHeader;
    public $mailBody;
    public $timeSlots;
    public $today;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mailHeader, $mailBody, $timeSlots, $today)
    {
        $this->mailHeader = $mailHeader;
        $this->mailBody = $mailBody;
        $this->timeSlots = $timeSlots;
        $this->today = $today;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->mailHeader)->view('emails.projectHourlyMail');
    }
}
