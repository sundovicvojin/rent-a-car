<?php

namespace Database\Seeders;

use App\Enums\BillingType;
use App\Enums\PriceType;
use App\Enums\ReservationStatus;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Extra;
use App\Models\Location;
use App\Models\LongRentalDiscount;
use App\Models\SeasonalPrice;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleBlock;
use App\Models\VehicleImage;
use App\Services\ImageService;
use App\Services\PricingService;
use App\Support\DateRange;
use App\Support\PlaceholderImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/** Demo podaci za lokalni razvoj. Slike su generisane i privremene. */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Samo za lokalni razvoj: demo lozinka NE sme da se koristi na produkciji.
        User::updateOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Administrator',
            'password' => Hash::make('password'),
        ]);

        $cats = collect([['Ekonomska', 1], ['Kompaktna', 2], ['Limuzina', 3], ['SUV', 4], ['Kombi', 5]])
            ->mapWithKeys(fn ($c) => [$c[0] => Category::firstOrCreate(['name' => $c[0]], ['sort_order' => $c[1]])]);

        $locations = [
            ['Poslovnica Novi Sad', 'Bulevar oslobođenja 1, Novi Sad', 0, 45.2551, 19.8452],
            ['Aerodrom Nikola Tesla, Beograd', 'Surčin, Beograd', 6000, 44.8184, 20.3091],
            ['Železnička stanica Novi Sad', 'Bul. Jovana Dučića, Novi Sad', 1500, 45.2646, 19.8335],
            ['Petrovaradinska tvrđava', 'Petrovaradin', 1000, 45.2519, 19.8627],
        ];
        foreach ($locations as [$name, $addr, $fee, $lat, $lng]) {
            Location::firstOrCreate(['name' => $name], ['address' => $addr, 'delivery_fee' => $fee, 'latitude' => $lat, 'longitude' => $lng]);
        }

        $extras = [
            ['Dečije sedište', 'Sedište za decu od 9 do 36 kg.', 300, BillingType::PerDay],
            ['Dodatni vozač', 'Još jedna osoba može da vozi.', 400, BillingType::PerDay],
            ['GPS navigacija', 'Uređaj sa mapama Evrope.', 500, BillingType::PerDay],
            ['Potpuno osiguranje', 'Smanjuje učešće u šteti na nulu.', 1200, BillingType::PerDay],
            ['Zimski paket', 'Zimske gume i lanci.', 2500, BillingType::Once],
            ['Prva čišćenja i punjenje rezervoara', 'Vraćate vozilo bez brige o gorivu.', 3500, BillingType::Once],
        ];
        foreach ($extras as [$name, $desc, $price, $billing]) {
            Extra::firstOrCreate(['name' => $name], ['description' => $desc, 'price' => $price, 'billing' => $billing]);
        }

        LongRentalDiscount::firstOrCreate(['min_days' => 7], ['percent' => 10]);
        LongRentalDiscount::firstOrCreate(['min_days' => 14], ['percent' => 15]);
        LongRentalDiscount::firstOrCreate(['min_days' => 30], ['percent' => 20]);

        $year = (int) now()->format('Y');
        SeasonalPrice::firstOrCreate(['name' => 'Letnja sezona'], [
            'starts_on' => "$year-06-15", 'ends_on' => "$year-09-05", 'type' => PriceType::Percent, 'value' => 20,
        ]);
        SeasonalPrice::firstOrCreate(['name' => 'Novogodišnji praznici'], [
            'starts_on' => "$year-12-20", 'ends_on' => ($year + 1).'-01-08', 'type' => PriceType::Percent, 'value' => 15,
        ]);

        $vehicles = [
            // naziv, marka, model, god, kat, sedišta, vrata, prtljag, menjač, gorivo, tablica, cena, depozit, karoserija, boja, istaknuto
            ['Škoda Fabia', 'Škoda', 'Fabia', 2022, 'Ekonomska', 5, 5, 2, 'manual', 'petrol', 'NS 101-AA', 2800, 15000, 'hatchback', '#0E6E8C', true],
            ['Volkswagen Golf 8', 'Volkswagen', 'Golf', 2023, 'Kompaktna', 5, 5, 3, 'manual', 'diesel', 'NS 102-AB', 3500, 20000, 'hatchback', '#586A73', true],
            ['Toyota Corolla Hybrid', 'Toyota', 'Corolla', 2023, 'Limuzina', 5, 4, 3, 'automatic', 'hybrid', 'NS 103-AC', 4200, 25000, 'sedan', '#13222B', true],
            ['Škoda Octavia', 'Škoda', 'Octavia', 2022, 'Limuzina', 5, 4, 4, 'automatic', 'diesel', 'NS 104-AD', 4500, 25000, 'sedan', '#B3402E', false],
            ['Dacia Duster', 'Dacia', 'Duster', 2023, 'SUV', 5, 5, 3, 'manual', 'lpg', 'NS 105-AE', 4000, 25000, 'suv', '#1F7A4D', true],
            ['Hyundai Tucson', 'Hyundai', 'Tucson', 2024, 'SUV', 5, 5, 4, 'automatic', 'hybrid', 'NS 106-AF', 6500, 40000, 'suv', '#0E6E8C', false],
            ['Peugeot e-208', 'Peugeot', 'e-208', 2023, 'Kompaktna', 5, 5, 2, 'automatic', 'electric', 'NS 107-AG', 4800, 30000, 'hatchback', '#F2B300', false],
            ['Opel Vivaro Combi 9', 'Opel', 'Vivaro', 2022, 'Kombi', 9, 5, 5, 'manual', 'diesel', 'NS 108-AH', 8500, 50000, 'van', '#586A73', false],
        ];

        $images = app(ImageService::class);
        foreach ($vehicles as $v) {
            [$name, $brand, $model, $y, $cat, $seats, $doors, $lug, $trans, $fuel, $plate, $price, $dep, $body, $color, $featured] = $v;
            $vehicle = Vehicle::firstOrCreate(['plate' => $plate], [
                'name' => $name, 'brand' => $brand, 'model' => $model, 'year' => $y,
                'category_id' => $cats[$cat]->id, 'seats' => $seats, 'doors' => $doors, 'luggage' => $lug,
                'transmission' => $trans, 'fuel' => $fuel, 'daily_price' => $price, 'deposit' => $dep,
                'description' => "{$name} je pouzdan izbor za grad i duža putovanja. Vozilo se predaje očišćeno, sa punim rezervoarom i osnovnim osiguranjem. (Demo opis, zameniti pravim tekstom.)",
                'status' => 'active', 'is_featured' => $featured,
            ]);

            if ($vehicle->images()->doesntExist()) {
                foreach (range(0, 2) as $i) {
                    $path = PlaceholderImage::make("vehicles/{$vehicle->slug}-{$i}.jpg", $color, $body, $i);
                    $images->generateVariants($path);
                    VehicleImage::create([
                        'vehicle_id' => $vehicle->id, 'path' => $path, 'is_placeholder' => true,
                        'alt' => "{$name} – privremena ilustracija ".($i + 1), 'is_primary' => $i === 0, 'sort_order' => $i,
                    ]);
                }
            }
        }

        // Demo zauzetost: jedna blokada za servis i par rezervacija.
        $golf = Vehicle::where('plate', 'NS 102-AB')->first();
        VehicleBlock::firstOrCreate(['vehicle_id' => $golf->id, 'reason' => 'Redovan servis'], [
            'starts_on' => now()->addDays(10)->toDateString(), 'ends_on' => now()->addDays(13)->toDateString(),
        ]);

        if (\App\Models\Reservation::query()->doesntExist()) {
            $pricing = app(PricingService::class);
            $hq = Location::first();
            $demo = [
                ['Marko Marković', '+381 64 111 2233', 'marko@example.com', 'NS 101-AA', 0, 3, ReservationStatus::Active],
                ['Ana Jovanović', '+381 63 444 5566', 'ana@example.com', 'NS 103-AC', 1, 5, ReservationStatus::Confirmed],
                ['Petar Petrović', '+381 65 777 8899', 'petar@example.com', 'NS 105-AE', 4, 11, ReservationStatus::Pending],
            ];
            foreach ($demo as [$n, $ph, $em, $plate, $from, $to, $status]) {
                $customer = Customer::firstOrCreate(['email' => $em], ['name' => $n, 'phone' => $ph]);
                $vehicle = Vehicle::where('plate', $plate)->first();
                $range = new DateRange(now()->addDays($from - ($status === ReservationStatus::Active ? 1 : 0)), now()->addDays($to));
                $q = $pricing->quote($vehicle, $range, $hq, $hq);
                $r = \App\Models\Reservation::create([
                    'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id,
                    'pickup_location_id' => $hq->id, 'return_location_id' => $hq->id,
                    'starts_on' => $range->start, 'ends_on' => $range->end, 'days' => $q->days,
                    'status' => $status, 'rental_total' => $q->rentalTotal, 'discount_total' => $q->discountTotal,
                    'delivery_total' => $q->deliveryTotal(), 'extras_total' => $q->extrasTotal, 'total' => $q->total(),
                    'deposit' => $q->deposit, 'price_breakdown' => $q->toArray(), 'source' => 'admin',
                ]);
            }
        }
    }
}
