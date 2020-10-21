<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConfirmationCode extends Mailable
{
    use Queueable, SerializesModels;

    protected $confirmationCode;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($confirmationCode)
    {
        $this->confirmationCode = $confirmationCode;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('70@xenforce.ru')->view('mails.confirmation')->with([
            'confirmationCode' => $this->confirmationCode
        ]);
    }
}
