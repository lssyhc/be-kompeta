<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AccountApprovedMail extends Mailable
{
    public User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pemberitahuan: Akun Anda Telah Disetujui',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-approved',
        );
    }
}
