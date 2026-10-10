<?php

use App\Enums\ReservationStatus;
use App\Exceptions\VehicleUnavailableException;
use App\Livewire\ReservationWizard;
use App\Mail\ReservationConfirmation;
use App\Mail\ReservationReceived;
use App\Models\CompanySetting;
use App\Models\Customer;
use App\Models\Extra;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

function reservationData(array $over = []): array
{
    $loc = makeLocation();

    return array_replace_recursive([
        'starts_on' => now()->addDays(10)->toDateString(),
        'ends_on' => now()->addDays(13)->toDateString(),
        'pickup_location_id' => $loc->id,
        'return_location_id' => $loc->id,
        'customer' => ['name' => 'Ana Anić', 'phone' => '+381 64 123 4567', 'email' => 'ana@example.com'],
        'consent' => true,
    ], $over);
}

beforeEach(function () {
    CompanySetting::create(['name' => 'Test Firma', 'email' => 'firma@example.com']);
});

it('pravi rezervaciju sa cenom izračunatom na serveru i brojem rezervacije', function () {
    Mail::fake();
    $v = makeVehicle(['daily_price' => 3000]);

    $r = app(ReservationService::class)->create(reservationData(['vehicle_id' => $v->id]));

    expect($r->number)->toStartWith('RS-')
        ->and($r->days)->toBe(3)->and($r->total)->toBe(9000)->and($r->status)->toBe(ReservationStatus::Pending)
        ->and($r->payment_method->value)->toBe('pay_on_pickup')->and($r->payment_status->value)->toBe('unpaid')
        ->and(Customer::count())->toBe(1);
    Mail::assertSent(ReservationConfirmation::class, fn ($m) => $m->hasTo('ana@example.com'));
    Mail::assertSent(ReservationReceived::class, fn ($m) => $m->hasTo('firma@example.com'));
});

it('sprečava dvostruko rezervisanje istog vozila u istom terminu', function () {
    Mail::fake();
    $v = makeVehicle();
    $service = app(ReservationService::class);

    $service->create(reservationData(['vehicle_id' => $v->id]));
    $service->create(reservationData(['vehicle_id' => $v->id, 'customer' => ['email' => 'drugi@example.com']]));
})->throws(VehicleUnavailableException::class);

it('dozvoljava drugu rezervaciju posle otkazivanja prve', function () {
    Mail::fake();
    $v = makeVehicle();
    $service = app(ReservationService::class);

    $first = $service->create(reservationData(['vehicle_id' => $v->id]));
    $first->update(['status' => ReservationStatus::Cancelled]);
    $second = $service->create(reservationData(['vehicle_id' => $v->id]));

    expect($second->exists)->toBeTrue()->and(Reservation::count())->toBe(2);
});

it('ne duplira klijenta sa istim mejlom', function () {
    Mail::fake();
    $service = app(ReservationService::class);
    $service->create(reservationData(['vehicle_id' => makeVehicle()->id]));
    $service->create(reservationData(['vehicle_id' => makeVehicle()->id]));

    expect(Customer::count())->toBe(1);
});

it('prolazi ceo tok u čarobnjaku i preusmerava na potvrdu', function () {
    Mail::fake();
    $v = makeVehicle(['daily_price' => 3000]);
    $loc = makeLocation(['name' => 'Glavna']);
    $extra = Extra::create(['name' => 'GPS', 'price' => 500, 'billing' => 'per_day']);

    $component = Livewire::test(ReservationWizard::class)
        ->set('starts_on', now()->addDays(5)->toDateString())
        ->set('ends_on', now()->addDays(8)->toDateString())
        ->set('pickup_location_id', $loc->id)->set('return_location_id', $loc->id)
        ->call('next')->assertSet('step', 2)
        ->call('selectVehicle', $v->id)->call('next')->assertSet('step', 3)
        ->set('extra_ids', [(string) $extra->id])
        ->call('next')->assertSet('step', 4)
        ->set('name', 'Marko Marković')->set('phone', '064 111 222')->set('email', 'marko@example.com')
        ->call('next')->assertHasErrors(['consent'])
        ->set('consent', true)->call('next')->assertSet('step', 5);

    expect($component->get('quote')->total())->toBe(9000 + 1500);

    $component->call('submit')->assertRedirect();

    $r = Reservation::first();
    expect($r->total)->toBe(10500)->and($r->extras)->toHaveCount(1)->and($r->consent_at)->not->toBeNull();
    $this->get(URL::signedRoute('reservation.confirmation', $r->number))->assertOk()->assertSee($r->number);
});

it('prikazuje greške na srpskom pored polja', function () {
    Livewire::test(ReservationWizard::class)
        ->set('starts_on', null)->set('ends_on', null)->set('step', 1)
        ->call('next')
        ->assertHasErrors(['starts_on' => 'required', 'ends_on' => 'required'])
        ->assertSee('Izaberite datum preuzimanja.');
});

it('odbija datum vraćanja pre datuma preuzimanja', function () {
    Livewire::test(ReservationWizard::class)
        ->set('starts_on', now()->addDays(5)->toDateString())->set('ends_on', now()->addDays(3)->toDateString())
        ->call('next')->assertHasErrors(['ends_on' => 'after_or_equal']);
});

it('odbija zahtev kad je honeypot popunjen', function () {
    $v = makeVehicle();
    $loc = makeLocation();
    Livewire::test(ReservationWizard::class)
        ->set('starts_on', now()->addDays(5)->toDateString())->set('ends_on', now()->addDays(8)->toDateString())
        ->set('pickup_location_id', $loc->id)->set('return_location_id', $loc->id)->set('vehicle_id', $v->id)
        ->set('name', 'Bot Botić')->set('phone', '064 111 222')->set('email', 'bot@example.com')->set('consent', true)
        ->set('website', 'http://spam.example')->set('step', 5)
        ->call('submit')->assertHasErrors(['website']);

    expect(Reservation::count())->toBe(0);
});

it('čarobnjak ne dozvoljava zauzeto vozilo', function () {
    Mail::fake();
    $v = makeVehicle();
    $data = reservationData(['vehicle_id' => $v->id]);
    app(ReservationService::class)->create($data);

    Livewire::test(ReservationWizard::class)
        ->set('starts_on', $data['starts_on'])->set('ends_on', $data['ends_on'])
        ->set('pickup_location_id', $data['pickup_location_id'])->set('return_location_id', $data['return_location_id'])
        ->set('step', 2)->call('selectVehicle', $v->id)->call('next')
        ->assertHasErrors(['vehicle_id'])->assertSet('step', 2);
});

it('potvrda ne radi bez potpisanog linka', function () {
    Mail::fake();
    $r = app(ReservationService::class)->create(reservationData(['vehicle_id' => makeVehicle()->id]));
    $this->get('/rezervacija/potvrda/'.$r->number)->assertForbidden();
});
