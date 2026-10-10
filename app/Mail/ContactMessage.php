<?php

namespace App\Mail;

use App\Models\CompanySetting;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ContactMessage extends Mailable
{
    public function __construct(public array $data) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->data['email'], $this->data['name'])],
            subject: 'Nova poruka sa sajta: '.$this->data['name'],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact', with: ['company' => CompanySetting::current()]);
    }
}
