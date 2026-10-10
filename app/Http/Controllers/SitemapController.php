<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;

class SitemapController extends Controller
{
    public function __invoke()
    {
        $urls = collect([
            ['loc' => route('home'), 'priority' => '1.0'],
            ['loc' => route('fleet.index'), 'priority' => '0.9'],
            ['loc' => route('reservation.create'), 'priority' => '0.8'],
            ['loc' => route('contact'), 'priority' => '0.6'],
            ['loc' => route('terms'), 'priority' => '0.4'],
            ['loc' => route('privacy'), 'priority' => '0.3'],
        ])->concat(Vehicle::query()->bookable()->get()->map(fn (Vehicle $v) => [
            'loc' => route('fleet.show', $v), 'priority' => '0.8', 'lastmod' => $v->updated_at->toDateString(),
        ]));

        return response()->view('sitemap', ['urls' => $urls])->header('Content-Type', 'application/xml');
    }
}
