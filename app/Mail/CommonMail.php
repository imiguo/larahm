<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CommonMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param       $view
     * @param null  $subject
     * @param array $data
     */
    public function __construct($subject, $view, $data = [])
    {
        $this->subject = $subject;
        $this->view = $view;
        $this->viewData = array_merge($this->viewData, $data);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this;
    }
}
