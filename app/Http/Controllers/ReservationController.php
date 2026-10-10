<?php

namespace App\Http\Controllers;

use App\Models\Reservation;

class ReservationController extends Controller
{
    public function create()
    {
        return view('reservation.create');
    }

    public function confirmation(Reservation $reservation)
    {
        $reservation->load(['customer', 'vehicle.primaryImage', 'pickupLocation', 'returnLocation', 'extras']);

        return view('reservation.confirmation', ['reservation' => $reservation]);
    }
}
