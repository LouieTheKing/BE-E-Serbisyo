<?php

namespace App\Mail;

use App\Models\Blotter;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BlotterCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $blotter;

    /**
     * Create a new message instance.
     */
    public function __construct(Blotter $blotter)
    {
        $this->blotter = $blotter->load(['createdBy']);
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject("Blotter Case Filed - {$this->blotter->case_number}")
                    ->view('emails.blotter_created');
    }
}
