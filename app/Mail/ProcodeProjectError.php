<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProcodeProjectError extends Mailable
{
    use Queueable, SerializesModels;
    public $mailHeader;
    public $fileStatus;
    public $error_description;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mailHeader, $fileStatus, $error_description)
    {
        $this->mailHeader = $mailHeader;
        $this->fileStatus = $fileStatus;
        $this->error_description = $error_description;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject("Pro-Code - ".$this->mailHeader)->view('emails.ProcodeProjectError');
    }
}
