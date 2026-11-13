<?php

namespace App\Mail;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrganizationCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Organization $organization,
        public User $owner,
        public ?string $plainPassword = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your '.$this->organization->name.' CRM account is ready',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.organization-credentials',
            with: [
                'organization' => $this->organization,
                'owner' => $this->owner,
                'password' => $this->plainPassword,
                'loginUrl' => route('auth-login'),
            ],
        );
    }
}
