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
    public $startTime;
    public $endTime;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mailHeader, $mailBody, $timeSlots, $today,$startTime,$endTime)
    {
        $this->mailHeader = $mailHeader;
        $this->mailBody = $mailBody;
        $this->timeSlots = $timeSlots;
        $this->today = $today;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
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
