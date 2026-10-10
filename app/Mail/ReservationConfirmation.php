<?php

namespace App\Mail;

use App\Models\CompanySetting;
use App\Models\Reservation;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\URL;

/** Potvrda prijema rezervacije za klijenta. */
class ReservationConfirmation extends Mailable
{
    public function __construct(public Reservation $reservation) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Rezervacija '.$this->reservation->number.' je primljena');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.reservation-customer', with: [
            'company' => CompanySetting::current(),
            'url' => URL::signedRoute('reservation.confirmation', ['reservation' => $this->reservation->number]),
        ]);
    }
}
