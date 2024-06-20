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
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mailHeader, $mailBody)
    {
        $this->mailHeader = $mailHeader;
        $this->mailBody = $mailBody;
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
