<?php

namespace App\Mail;

use App\Models\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountRegisteredMail extends Mailable
{
    use Queueable, SerializesModels;

    public $account;
    public $password;

    /**
     * Create a new message instance.
     */
    public function __construct(Account $account, $password = null)
    {
        $this->account = $account;
        $this->password = $password;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Welcome to Our System!')
                    ->view('emails.account_registered');
    }
}
