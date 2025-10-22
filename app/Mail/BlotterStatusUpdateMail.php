<?php

namespace App\Mail;

use App\Models\Blotter;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BlotterStatusUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public $blotter;
    public $status;
    public $oldStatus;

    /**
     * Create a new message instance.
     */
    public function __construct(Blotter $blotter, $oldStatus = null)
    {
        $this->blotter = $blotter->load(['createdBy']);
        $this->status = $blotter->status;
        $this->oldStatus = $oldStatus;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $statusText = ucfirst(str_replace('_', ' ', $this->status));
        
        return $this->subject("Blotter Status Update - {$this->blotter->case_number} - {$statusText}")
                    ->view('emails.blotter_status_update');
    }
}
