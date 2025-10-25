<?php

namespace App\Mail;

use App\Models\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgotPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $account;
    public $password;

    /**
     * Create a new message instance.
     */
    public function __construct(Account $account, $password)
    {
        $this->account = $account;
        $this->password = $password;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Your Password Reset Request')
                    ->view('emails.forgot_password');
    }
}
