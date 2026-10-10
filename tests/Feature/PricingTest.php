<?php

use App\Enums\BillingType;
use App\Enums\PriceType;
use App\Models\Extra;
use App\Models\LongRentalDiscount;
use App\Models\SeasonalPrice;
use App\Services\PricingService;
use App\Support\DateRange;

beforeEach(function () {
    $this->pricing = app(PricingService::class);
    $this->vehicle = makeVehicle(['daily_price' => 4000]);
});

it('računa osnovnu cenu po danima', function () {
    $q = $this->pricing->quote($this->vehicle, new DateRange('2030-03-14', '2030-03-17'));
    expect($q->days)->toBe(3)->and($q->rentalTotal)->toBe(12000)->and($q->total())->toBe(12000);
});

it('primenjuje procentualno sezonsko pravilo samo na dane u periodu', function () {
    SeasonalPrice::create(['name' => 'Leto', 'starts_on' => '2030-07-01', 'ends_on' => '2030-07-31', 'type' => PriceType::Percent, 'value' => 25]);
    // 30.6 (osnovna) + 1.7. i 2.7. (+25% = 5000)
    $q = $this->pricing->quote($this->vehicle, new DateRange('2030-06-30', '2030-07-03'));
    expect($q->rentalTotal)->toBe(4000 + 5000 + 5000);
});

it('primenjuje fiksnu sezonsku cenu', function () {
    SeasonalPrice::create(['name' => 'Praznici', 'starts_on' => '2030-12-30', 'ends_on' => '2031-01-02', 'type' => PriceType::Fixed, 'value' => 6500]);
    $q = $this->pricing->quote($this->vehicle, new DateRange('2030-12-30', '2031-01-01'));
    expect($q->rentalTotal)->toBe(13000);
});

it('pravilo za vozilo ima prednost nad opštim', function () {
    SeasonalPrice::create(['name' => 'Opšte', 'starts_on' => '2030-07-01', 'ends_on' => '2030-07-31', 'type' => PriceType::Percent, 'value' => 50]);
    SeasonalPrice::create(['vehicle_id' => $this->vehicle->id, 'name' => 'Samo ovo vozilo', 'starts_on' => '2030-07-01', 'ends_on' => '2030-07-31', 'type' => PriceType::Percent, 'value' => 10]);
    $q = $this->pricing->quote($this->vehicle, new DateRange('2030-07-10', '2030-07-12'));
    expect($q->rentalTotal)->toBe(8800);
});

it('neaktivno sezonsko pravilo se ignoriše', function () {
    SeasonalPrice::create(['name' => 'Off', 'starts_on' => '2030-07-01', 'ends_on' => '2030-07-31', 'type' => PriceType::Percent, 'value' => 50, 'is_active' => false]);
    expect($this->pricing->quote($this->vehicle, new DateRange('2030-07-10', '2030-07-12'))->rentalTotal)->toBe(8000);
});

it('daje popust za duže najmove tek od zadatog broja dana', function () {
    LongRentalDiscount::create(['min_days' => 7, 'percent' => 10]);
    LongRentalDiscount::create(['min_days' => 14, 'percent' => 15]);

    $six = $this->pricing->quote($this->vehicle, new DateRange('2030-03-01', '2030-03-07'));
    $seven = $this->pricing->quote($this->vehicle, new DateRange('2030-03-01', '2030-03-08'));
    $fourteen = $this->pricing->quote($this->vehicle, new DateRange('2030-03-01', '2030-03-15'));

    expect($six->discountTotal)->toBe(0)
        ->and($seven->discountTotal)->toBe(2800)->and($seven->total())->toBe(25200)
        ->and($fourteen->discountPercent)->toBe(15)->and($fourteen->discountTotal)->toBe(8400);
});

it('naplaćuje dostavu na obe lokacije', function () {
    $a = makeLocation(['delivery_fee' => 1500]);
    $b = makeLocation(['delivery_fee' => 6000]);
    $q = $this->pricing->quote($this->vehicle, new DateRange('2030-03-14', '2030-03-15'), $a, $b);
    expect($q->deliveryTotal())->toBe(7500)->and($q->total())->toBe(4000 + 7500);
});

it('računa dodatke po danu i jednokratno, a ignoriše neaktivne', function () {
    $perDay = Extra::create(['name' => 'GPS', 'price' => 500, 'billing' => BillingType::PerDay]);
    $once = Extra::create(['name' => 'Zimski paket', 'price' => 2500, 'billing' => BillingType::Once]);
    $off = Extra::create(['name' => 'Isključen', 'price' => 999, 'billing' => BillingType::Once, 'is_active' => false]);

    $q = $this->pricing->quote($this->vehicle, new DateRange('2030-03-14', '2030-03-17'), null, null, [$perDay->id, $once->id, $off->id]);

    expect($q->extrasTotal)->toBe(500 * 3 + 2500)->and($q->extras)->toHaveCount(2);
});

it('ukupna cena je najam − popust + dostava + dodaci, u celim brojevima', function () {
    LongRentalDiscount::create(['min_days' => 7, 'percent' => 10]);
    $loc = makeLocation(['delivery_fee' => 1000]);
    $extra = Extra::create(['name' => 'GPS', 'price' => 500, 'billing' => BillingType::PerDay]);

    $q = $this->pricing->quote($this->vehicle, new DateRange('2030-03-01', '2030-03-08'), $loc, $loc, [$extra->id]);

    expect($q->total())->toBe(28000 - 2800 + 2000 + 3500)->toBeInt();
});
