<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountRejectedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;

    public ?string $reason;

    public function __construct(User $user, ?string $reason = null)
    {
        $this->user = $user;
        $this->reason = $reason;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pemberitahuan: Akun Anda Ditolak',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-rejected',
        );
    }
}
