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
