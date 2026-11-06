<?php

namespace App\Mail;

use App\Models\PartnershipRequest;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Alerte interne : nouvelle demande reçue par le formulaire de partenariat. */
class NewPartnershipRequest extends Mailable
{
    public function __construct(public PartnershipRequest $demande) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nouvelle demande de partenariat : '.$this->demande->company,
            replyTo: [new Address($this->demande->email, $this->demande->contact_name ?: $this->demande->company)],
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.partnership');
    }
}
