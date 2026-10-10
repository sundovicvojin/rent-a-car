<?php

namespace App\Http\Controllers;

class ReservationController extends Controller
{
    public function create()
    {
        return view('pages.privacy');
    }

    public function confirmation()
    {
        abort(404);
    }
}
