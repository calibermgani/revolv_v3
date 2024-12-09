<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProjectWorkMail extends Mailable
{
    use Queueable, SerializesModels;
    public $mailHeader;
    public $mailBody;
    public $yesterday;
    public $totalAr;
    public $totalQA;
    public $yesterDayStartDate;
    public $yesterDayEndDate;
    public $projectIds;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mailHeader, $mailBody, $yesterday,$totalAr,$totalQA,$yesterDayStartDate,$yesterDayEndDate,$projectIds)
    {
        $this->mailHeader = $mailHeader;
        $this->mailBody = $mailBody;
        $this->yesterday = $yesterday;
        $this->totalAr = $totalAr;
        $this->totalQA = $totalQA;
        $this->yesterDayStartDate = $yesterDayStartDate;
        $this->yesterDayEndDate = $yesterDayEndDate;
        $this->projectIds = $projectIds;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->mailHeader)->view('emails.projectWorkMail');
    }
}
