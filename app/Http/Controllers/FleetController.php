<?php

namespace App\Http\Controllers;

use App\Enums\Fuel;
use App\Enums\Transmission;
use App\Http\Requests\FleetFilterRequest;
use App\Models\Category;
use App\Models\Location;
use App\Models\Vehicle;
use App\Services\AvailabilityService;
use App\Services\PricingService;
use App\Services\VehicleCatalog;
use App\Support\DateRange;
use Illuminate\Http\Request;

class FleetController extends Controller
{
    public function index(FleetFilterRequest $request, VehicleCatalog $catalog, PricingService $pricing)
    {
        $range = $request->dateRange();
        $vehicles = $catalog->search($request->validated(), $range)->paginate(9)->withQueryString();

        $totals = [];
        if ($range) {
            foreach ($vehicles as $vehicle) {
                $totals[$vehicle->id] = $pricing->quote($vehicle, $range)->total();
            }
        }

        return view('fleet.index', [
            'vehicles' => $vehicles,
            'range' => $range,
            'totals' => $totals,
            'filters' => $request->validated(),
            'categories' => Category::query()->orderBy('sort_order')->get(),
            'transmissions' => Transmission::options(),
            'fuels' => Fuel::options(),
            'locations' => Location::query()->active()->orderBy('id')->get(),
            'minDate' => now()->toDateString(),
        ]);
    }

    public function show(Request $request, Vehicle $vehicle, AvailabilityService $availability, PricingService $pricing)
    {
        abort_unless($vehicle->status === \App\Enums\VehicleStatus::Active, 404);
        $vehicle->load(['category', 'images']);

        $range = null;
        if ($request->filled(['od', 'do'])) {
            try {
                $range = new DateRange($request->query('od'), $request->query('do'));
            } catch (\Throwable) {
                $range = null;
            }
        }

        return view('fleet.show', [
            'vehicle' => $vehicle,
            'busy' => $availability->busyPeriods($vehicle),
            'range' => $range,
            'available' => $range ? $availability->isAvailable($vehicle, $range) : null,
            'quote' => $range ? $pricing->quote($vehicle, $range) : null,
            'related' => Vehicle::query()->bookable()->where('id', '!=', $vehicle->id)
                ->where('category_id', $vehicle->category_id)->with('primaryImage', 'category')->limit(3)->get(),
            'query' => $request->only(['od', 'do', 'lokacija', 'vracanje']),
        ]);
    }
}
