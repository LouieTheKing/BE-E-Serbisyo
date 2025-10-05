<?php

namespace App\Mail;

use App\Models\RequestDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RequestDocumentStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $requestDocument;
    public $status;

    /**
     * Create a new message instance.
     */
    public function __construct(RequestDocument $requestDocument)
    {
        // Ensure relationships are loaded
        $this->requestDocument = $requestDocument->load(['account', 'documentDetails']);
        $this->status = $requestDocument->status;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $statusText = ucfirst(str_replace('_', ' ', $this->status));

        return $this->subject("Document Request Status Update - {$statusText}")
                    ->view('emails.request_document_status');
    }
}
