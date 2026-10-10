<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Vehicle;

class HomeController extends Controller
{
    public function __invoke()
    {
        return view('pages.home', [
            'featured' => Vehicle::query()->bookable()->where('is_featured', true)
                ->with(['category', 'primaryImage'])->orderBy('daily_price')->limit(3)->get(),
            'locations' => Location::query()->active()->orderBy('id')->get(),
            'minDate' => now()->toDateString(),
        ]);
    }
}
