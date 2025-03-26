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
    public $projectIds;
    public $subProjectIds;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mailHeader, $mailBody, $yesterday, $projectIds, $subProjectIds)
    {
        $this->mailHeader = $mailHeader;
        $this->mailBody = $mailBody;
        $this->yesterday = $yesterday;
        $this->projectIds =  $projectIds;
        $this->subProjectIds = $subProjectIds;
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
