<?php

use App\Livewire\ReservationWizard;
use App\Models\CompanySetting;
use App\Models\Reservation;
use App\Services\DeliveryService;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    CompanySetting::create(['name' => 'Test', 'email' => 'f@example.com']); // baza Novi Sad, 0,50 € / km, do 30 km
});

it('računa udaljenost haversine formulom i doplatu po započetom kilometru', function () {
    $svc = app(DeliveryService::class);

    expect($svc->forPoint(45.2551, 19.8452))->toMatchArray(['km' => 0, 'fee' => 0, 'within_range' => true]);

    $zrenjanin = $svc->forPoint(45.3816, 20.3903); // ~ 45 km
    expect($zrenjanin['within_range'])->toBeFalse();

    $petrovaradin = $svc->forPoint(45.2519, 19.8627); // ~ 1,5 km
    expect($petrovaradin['km'])->toBe(2)->and($petrovaradin['fee'])->toBe(100);
});

it('koristi cenu po kilometru i granicu iz podešavanja firme', function () {
    CompanySetting::first()->update(['fee_per_km' => 200, 'max_delivery_km' => 5]);
    $svc = app(DeliveryService::class);

    expect($svc->forPoint(45.2519, 19.8627)['fee'])->toBe(400)
        ->and($svc->forPoint(45.3, 19.95)['within_range'])->toBeFalse();
});

it('čarobnjak odbija tačku van zone dostave', function () {
    Livewire::test(ReservationWizard::class)
        ->set('pickup_on_map', true)
        ->call('setPoint', 'pickup', 45.3816, 20.3903)
        ->assertHasErrors(['pickup_lat'])->assertSet('pickup_lat', null);
});

it('rezervacija sa tačkom na mapi dobija doplatu po udaljenosti', function () {
    Mail::fake();
    $v = makeVehicle(['daily_price' => 3000]);
    $loc = makeLocation(['name' => 'Poslovnica']);

    Livewire::test(ReservationWizard::class)
        ->set('starts_on', now()->addDays(5)->toDateString())->set('ends_on', now()->addDays(7)->toDateString())
        ->set('pickup_location_id', $loc->id)->set('return_location_id', $loc->id)
        ->set('pickup_on_map', true)->call('next')->assertHasErrors(['pickup_lat'])
        ->call('setPoint', 'pickup', 45.2519, 19.8627)->set('pickup_address', 'Tvrđava, ulaz')
        ->call('next')->assertSet('step', 2)
        ->call('selectVehicle', $v->id)->call('next')->call('next')
        ->set('name', 'Mika Mikić')->set('phone', '064 222 333')->set('email', 'mika@example.com')->set('consent', true)
        ->call('next')->assertSet('step', 5)
        ->call('submit')->assertRedirect();

    $r = Reservation::first();
    expect($r->delivery_total)->toBe(100)->and($r->total)->toBe(6000 + 100)
        ->and($r->pickup_custom['address'])->toBe('Tvrđava, ulaz')->and($r->pickup_custom['km'])->toBe(2)
        ->and($r->return_custom)->toBeNull()
        ->and($r->pickupLabel())->toContain('Tvrđava, ulaz');
});

it('ignoriše lokaciju ako je izabrana mapa i ne dodaje fiksnu doplatu lokacije', function () {
    $v = makeVehicle(['daily_price' => 3000]);
    $airport = makeLocation(['delivery_fee' => 6000]);
    $q = app(App\Services\PricingService::class)->quote($v, new App\Support\DateRange(now()->addDay(), now()->addDays(2)), $airport, $airport, [], ['lat' => 45.2519, 'lng' => 19.8627], null);
    expect($q->pickupFee)->toBe(100)->and($q->returnFee)->toBe(6000);
});
