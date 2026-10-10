<?php

namespace App\Mail;

use App\Models\CompanySetting;
use App\Models\Reservation;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Obaveštenje firmi o novoj rezervaciji. */
class ReservationReceived extends Mailable
{
    public function __construct(public Reservation $reservation) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Nova rezervacija '.$this->reservation->number);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.reservation-company', with: [
            'company' => CompanySetting::current(),
            'adminUrl' => url('/admin/reservations/'.$this->reservation->id.'/edit'),
        ]);
    }
}
