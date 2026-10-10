<?php

namespace App\Services;

use App\Enums\BillingType;
use App\Enums\PriceType;
use App\Models\Extra;
use App\Models\Location;
use App\Models\LongRentalDiscount;
use App\Models\SeasonalPrice;
use App\Models\Vehicle;
use App\Support\DateRange;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Cena = najam (sa sezonskim pravilima) − popust za duže najmove + dostava na obe lokacije + dodaci.
 * Sve se računa u centima i isključivo na serveru.
 */
class PricingService
{
    /** @param  array<int,int>  $extraIds */
    public function quote(Vehicle $vehicle, DateRange $range, ?Location $pickup = null, ?Location $return = null, array $extraIds = []): PriceQuote
    {
        $days = $range->days();

        [$rentalTotal, $lines] = $this->rental($vehicle, $range);
        $discountPercent = $this->discountPercent($days);
        $discountTotal = (int) round($rentalTotal * $discountPercent / 100);

        $extras = $this->extras($extraIds, $days);

        return new PriceQuote(
            days: $days,
            rentalTotal: $rentalTotal,
            rentalLines: $lines,
            discountPercent: $discountPercent,
            discountTotal: $discountTotal,
            pickupFee: (int) ($pickup?->delivery_fee ?? 0),
            returnFee: (int) ($return?->delivery_fee ?? 0),
            extras: $extras,
            extrasTotal: (int) array_sum(array_column($extras, 'total')),
            deposit: $vehicle->deposit,
        );
    }

    /** Cena jednog dana najma za vozilo (sezonsko pravilo ili osnovna cena). */
    public function dailyPriceOn(Vehicle $vehicle, CarbonInterface $day, ?Collection $rules = null): int
    {
        $rules ??= $this->rulesFor($vehicle);
        $rule = $this->ruleFor($rules, $vehicle->id, $day);

        if (! $rule) {
            return $vehicle->daily_price;
        }

        return $rule->type === PriceType::Fixed
            ? $rule->value
            : (int) round($vehicle->daily_price * (100 + $rule->value) / 100);
    }

    /** @return array{0:int, 1:array} */
    private function rental(Vehicle $vehicle, DateRange $range): array
    {
        $rules = $this->rulesFor($vehicle);
        $lines = [];
        $total = 0;

        foreach ($range->eachDay() as $day) {
            $price = $this->dailyPriceOn($vehicle, $day, $rules);
            $label = $this->ruleFor($rules, $vehicle->id, $day)?->name;
            $total += $price;

            $last = array_key_last($lines);
            if ($last !== null && $lines[$last]['daily_price'] === $price && $lines[$last]['label'] === $label) {
                $lines[$last]['days']++;
                $lines[$last]['to'] = $day->toDateString();
            } else {
                $lines[] = ['from' => $day->toDateString(), 'to' => $day->toDateString(), 'days' => 1, 'daily_price' => $price, 'label' => $label];
            }
        }

        return [$total, $lines];
    }

    private function rulesFor(Vehicle $vehicle): Collection
    {
        return SeasonalPrice::query()->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('vehicle_id')->orWhere('vehicle_id', $vehicle->id))
            ->get();
    }

    /** Pravilo za vozilo ima prednost nad opštim; među jednakima pobeđuje novije. */
    private function ruleFor(Collection $rules, int $vehicleId, CarbonInterface $day): ?SeasonalPrice
    {
        return $rules
            ->filter(fn (SeasonalPrice $r) => $r->starts_on->lte($day) && $r->ends_on->gte($day))
            ->sortByDesc(fn (SeasonalPrice $r) => [$r->vehicle_id === $vehicleId ? 1 : 0, $r->id])
            ->first();
    }

    private function discountPercent(int $days): int
    {
        return (int) (LongRentalDiscount::query()->where('is_active', true)
            ->where('min_days', '<=', $days)->max('percent') ?? 0);
    }

    /** @return array<int, array{extra_id:int, name:string, billing:string, unit_price:int, total:int}> */
    private function extras(array $ids, int $days): array
    {
        if ($ids === []) {
            return [];
        }

        return Extra::query()->active()->whereIn('id', array_unique($ids))->orderBy('id')->get()
            ->map(fn (Extra $e) => [
                'extra_id' => $e->id,
                'name' => $e->name,
                'billing' => $e->billing->value,
                'unit_price' => $e->price,
                'total' => $e->billing === BillingType::PerDay ? $e->price * $days : $e->price,
            ])->all();
    }
}
