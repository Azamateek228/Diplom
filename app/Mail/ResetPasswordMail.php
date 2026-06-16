<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $token;
    public string $userName;
    public string $userEmail;
    public int $expiresHours;

    /**
     * Create a new message instance.
     */
    public function __construct(string $token, string $userName, string $userEmail, int $expiresHours = 24)
    {
        $this->token = $token;
        $this->userName = $userName;
        $this->userEmail = $userEmail;
        $this->expiresHours = $expiresHours;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔄 Сброс пароля для Кино на колёсах',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reset-password',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
