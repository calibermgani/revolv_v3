<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProcodeProjectFile extends Mailable
{
    use Queueable, SerializesModels;
    public $mailHeader;
    public $fileStatus;
    public $Inventory_wound_data;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mailHeader, $fileStatus, $Inventory_wound_data)
    {
        $this->mailHeader = $mailHeader;
        $this->fileStatus = $fileStatus;
        $this->Inventory_wound_data = $Inventory_wound_data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject("Pro-Code - ".$this->mailHeader)->view('emails.projectFileStatus');
    }
}
