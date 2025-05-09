<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Forgout extends Mailable {
    use Queueable, SerializesModels;

    public function __construct(public readonly array $data) {
        
    }

    public function envelope(): Envelope {
        return new Envelope(
            subject: $this->data['subject'] ?? 'Recuperação de Senha - '.env('APP_NAME'),
            from: new Address (
                $this->data['toEmail'], 
                $this->data['toName']
            ),
        );
    }

    public function content(): Content {
        return new Content(
            view: 'mail.forgout',
        );
    }

    public function attachments(): array {
        return [];
    }
}
