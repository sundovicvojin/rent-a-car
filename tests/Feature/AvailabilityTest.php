<?php

use App\Enums\ReservationStatus;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\VehicleBlock;
use App\Services\AvailabilityService;
use App\Support\DateRange;

function bookedReservation($vehicle, string $from, string $to, ReservationStatus $status = ReservationStatus::Confirmed): Reservation
{
    $loc = makeLocation();
    $customer = Customer::create(['name' => 'Test Klijent', 'phone' => '+381601234567', 'email' => 'test'.uniqid().'@example.com']);

    return Reservation::create([
        'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id,
        'pickup_location_id' => $loc->id, 'return_location_id' => $loc->id,
        'starts_on' => $from, 'ends_on' => $to, 'days' => (int) (new DateRange($from, $to))->days(),
        'status' => $status, 'total' => 0,
    ]);
}

beforeEach(fn () => $this->availability = app(AvailabilityService::class));

it('računa dane po datumima: 14. do 17. je 3 dana', function () {
    expect((new DateRange('2030-05-14', '2030-05-17'))->days())->toBe(3);
});

it('najkraći najam je 1 dan, čak i kad su datumi isti', function () {
    expect((new DateRange('2030-05-14', '2030-05-14'))->days())->toBe(1);
});

it('odbija datum vraćanja pre datuma preuzimanja', function () {
    new DateRange('2030-05-14', '2030-05-10');
})->throws(InvalidArgumentException::class);

it('vozilo je slobodno kad nema rezervacija', function () {
    $v = makeVehicle();
    expect($this->availability->isAvailable($v, new DateRange('2030-06-01', '2030-06-05')))->toBeTrue();
});

it('vozilo je zauzeto kad se period preklapa sa rezervacijom', function (string $from, string $to) {
    $v = makeVehicle();
    bookedReservation($v, '2030-06-10', '2030-06-15');
    expect($this->availability->isAvailable($v, new DateRange($from, $to)))->toBeFalse();
})->with([
    'unutar' => ['2030-06-11', '2030-06-13'],
    'preklapa početak' => ['2030-06-08', '2030-06-11'],
    'preklapa kraj' => ['2030-06-14', '2030-06-18'],
    'obuhvata' => ['2030-06-01', '2030-06-30'],
    'identičan' => ['2030-06-10', '2030-06-15'],
]);

it('isti dan vraćanja i preuzimanja je dozvoljen', function () {
    $v = makeVehicle();
    bookedReservation($v, '2030-06-10', '2030-06-15');
    expect($this->availability->isAvailable($v, new DateRange('2030-06-15', '2030-06-18')))->toBeTrue()
        ->and($this->availability->isAvailable($v, new DateRange('2030-06-07', '2030-06-10')))->toBeTrue();
});

it('otkazana rezervacija ne zauzima vozilo', function () {
    $v = makeVehicle();
    bookedReservation($v, '2030-06-10', '2030-06-15', ReservationStatus::Cancelled);
    expect($this->availability->isAvailable($v, new DateRange('2030-06-11', '2030-06-12')))->toBeTrue();
});

it('blokada za servis zauzima vozilo', function () {
    $v = makeVehicle();
    VehicleBlock::create(['vehicle_id' => $v->id, 'starts_on' => '2030-07-01', 'ends_on' => '2030-07-04', 'reason' => 'Servis']);
    expect($this->availability->isAvailable($v, new DateRange('2030-07-02', '2030-07-03')))->toBeFalse()
        ->and($this->availability->isAvailable($v, new DateRange('2030-07-04', '2030-07-06')))->toBeTrue();
});

it('neaktivno vozilo i vozilo na servisu nisu dostupni', function (string $status) {
    $v = makeVehicle(['status' => $status]);
    expect($this->availability->isAvailable($v, new DateRange('2030-08-01', '2030-08-03')))->toBeFalse();
})->with(['inactive', 'service']);

it('lista slobodnih vozila izostavlja zauzeta', function () {
    $free = makeVehicle();
    $busy = makeVehicle();
    bookedReservation($busy, '2030-09-01', '2030-09-10');

    $ids = $this->availability->availableVehicles(new DateRange('2030-09-03', '2030-09-05'))->pluck('id');

    expect($ids)->toContain($free->id)->not->toContain($busy->id);
});
