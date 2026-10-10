<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)->use(RefreshDatabase::class)->in('Feature', 'Unit');

// ---- Pomoćne funkcije za testove (bez seedera, brzo) ----

function makeCategory(string $name = 'Kompaktna'): App\Models\Category
{
    return App\Models\Category::firstOrCreate(['name' => $name]);
}

function makeLocation(array $attrs = []): App\Models\Location
{
    return App\Models\Location::create($attrs + ['name' => 'Poslovnica '.uniqid(), 'delivery_fee' => 0, 'is_active' => true]);
}

function makeVehicle(array $attrs = []): App\Models\Vehicle
{
    static $n = 0;
    $n++;

    return App\Models\Vehicle::create($attrs + [
        'brand' => 'Škoda', 'model' => 'Fabia '.$n, 'year' => 2022, 'category_id' => makeCategory()->id,
        'seats' => 5, 'doors' => 5, 'luggage' => 2, 'transmission' => 'manual', 'fuel' => 'petrol',
        'plate' => 'NS '.$n.'-TST', 'daily_price' => 3000, 'deposit' => 10000, 'status' => 'active',
    ]);
}
