<?php

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Extras\ExtraResource;
use App\Filament\Resources\Locations\LocationResource;
use App\Filament\Resources\LongRentalDiscounts\LongRentalDiscountResource;
use App\Filament\Resources\SeasonalPrices\SeasonalPriceResource;
use App\Filament\Resources\Vehicles\VehicleResource;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->actingAs(User::first());
});

it('zahteva prijavu za admin panel', function () {
    auth()->logout();
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('otvara sve resurse u panelu', function (string $resource) {
    $this->get($resource::getUrl('index'))->assertOk();
    $this->get($resource::getUrl('create'))->assertOk();
})->with([
    VehicleResource::class, LocationResource::class, ExtraResource::class,
    SeasonalPriceResource::class, LongRentalDiscountResource::class, CategoryResource::class,
]);

it('otvara izmenu vozila i podešavanja firme', function () {
    $vehicle = App\Models\Vehicle::first();
    $this->get(VehicleResource::getUrl('edit', ['record' => $vehicle]))->assertOk();
    $this->get('/admin/podesavanja-firme')->assertOk();
});

it('čuva podešavanja firme iz panela', function () {
    Livewire\Livewire::test(App\Filament\Pages\CompanySettings::class)
        ->fillForm(['name' => 'Nova Firma', 'phone' => '+381 21 000 111', 'email' => 'x@example.com'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(App\Models\CompanySetting::current()->name)->toBe('Nova Firma');
});

it('cenu vozila iz panela čuva u centima', function () {
    $cat = App\Models\Category::first();
    Livewire\Livewire::test(App\Filament\Resources\Vehicles\Pages\CreateVehicle::class)
        ->fillForm([
            'brand' => 'Fiat', 'model' => 'Tipo', 'year' => 2022, 'category_id' => $cat->id, 'plate' => 'NS 999-ZZ',
            'status' => 'active', 'seats' => 5, 'doors' => 5, 'luggage' => 2, 'transmission' => 'manual', 'fuel' => 'petrol',
            'daily_price' => '32.50', 'deposit' => '150',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $v = App\Models\Vehicle::where('plate', 'NS 999-ZZ')->first();
    expect($v->daily_price)->toBe(3250)->and($v->deposit)->toBe(15000)->and($v->name)->toBe('Fiat Tipo');
});

it('otvara admin stranice za rezervacije, klijente, blokade, kalendar i tablu', function () {
    $this->get('/admin')->assertOk();
    foreach (['/admin/reservations', '/admin/reservations/create', '/admin/customers', '/admin/customers/create', '/admin/vehicle-blocks', '/admin/vehicle-blocks/create', '/admin/kalendar'] as $url) {
        $this->get($url)->assertOk();
    }
    $r = App\Models\Reservation::first();
    $this->get(App\Filament\Resources\Reservations\ReservationResource::getUrl('edit', ['record' => $r]))->assertOk();
    $this->get(App\Filament\Resources\Customers\CustomerResource::getUrl('edit', ['record' => $r->customer_id]))->assertOk();
});

it('ručno pravi rezervaciju iz panela i sprečava preklapanje', function () {
    $v = App\Models\Vehicle::where('plate', 'NS 104-AD')->first();
    $c = App\Models\Customer::first();
    $loc = App\Models\Location::first();
    $data = [
        'customer_id' => $c->id, 'vehicle_id' => $v->id, 'starts_on' => now()->addDays(40)->toDateString(),
        'ends_on' => now()->addDays(43)->toDateString(), 'pickup_location_id' => $loc->id, 'return_location_id' => $loc->id,
        'status' => 'confirmed', 'payment_method' => 'pay_on_pickup', 'payment_status' => 'unpaid',
    ];

    Livewire\Livewire::test(App\Filament\Resources\Reservations\Pages\CreateReservation::class)
        ->fillForm($data)->call('create')->assertHasNoFormErrors();

    $r = App\Models\Reservation::where('vehicle_id', $v->id)->first();
    expect($r->source)->toBe('admin')->and($r->total)->toBe(4500 * 3);

    Livewire\Livewire::test(App\Filament\Resources\Reservations\Pages\CreateReservation::class)
        ->fillForm($data)->call('create');
    expect(App\Models\Reservation::where('vehicle_id', $v->id)->count())->toBe(1);
});

it('tabla prikazuje današnja preuzimanja, vraćanja i nadolazeće rezervacije', function () {
    $active = App\Models\Reservation::where('status', 'active')->first(); // demo: preuzeto juče, vraća se za 4 dana
    $active->update(['starts_on' => now()->subDay(), 'ends_on' => now()]);
    $pending = App\Models\Reservation::where('status', 'pending')->first();
    $pending->update(['starts_on' => now()]);

    Livewire\Livewire::test(App\Filament\Widgets\TodayReturns::class)->assertCanSeeTableRecords([$active]);
    Livewire\Livewire::test(App\Filament\Widgets\TodayPickups::class)->assertCanSeeTableRecords([$pending]);
    Livewire\Livewire::test(App\Filament\Widgets\UpcomingReservations::class)->assertCanSeeTableRecords(
        App\Models\Reservation::blocking()->where('starts_on', '>', now()->toDateString())->get()
    );
});
