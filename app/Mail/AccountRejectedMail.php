<?php

namespace App\Mail;

use App\Models\RejectedAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $rejectedAccount;

    /**
     * Create a new message instance.
     */
    public function __construct(RejectedAccount $rejectedAccount)
    {
        $this->rejectedAccount = $rejectedAccount;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Account Registration Update - E-Serbisyo')
                    ->view('emails.account_rejected');
    }
}
