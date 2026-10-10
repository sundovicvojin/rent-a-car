<?php

namespace App\Services;

/** Rezultat obračuna cene. Svi iznosi su u centima. */
final class PriceQuote
{
    /**
     * @param  array<int, array{extra_id:int, name:string, billing:string, unit_price:int, total:int}>  $extras
     * @param  array<int, array{from:string, to:string, days:int, daily_price:int, label:?string}>  $rentalLines
     */
    public function __construct(
        public readonly int $days,
        public readonly int $rentalTotal,
        public readonly array $rentalLines,
        public readonly int $discountPercent,
        public readonly int $discountTotal,
        public readonly int $pickupFee,
        public readonly int $returnFee,
        public readonly array $extras,
        public readonly int $extrasTotal,
        public readonly int $deposit,
    ) {}

    public function deliveryTotal(): int
    {
        return $this->pickupFee + $this->returnFee;
    }

    public function total(): int
    {
        return $this->rentalTotal - $this->discountTotal + $this->deliveryTotal() + $this->extrasTotal;
    }

    public function toArray(): array
    {
        return [
            'days' => $this->days,
            'rental_total' => $this->rentalTotal,
            'rental_lines' => $this->rentalLines,
            'discount_percent' => $this->discountPercent,
            'discount_total' => $this->discountTotal,
            'pickup_fee' => $this->pickupFee,
            'return_fee' => $this->returnFee,
            'delivery_total' => $this->deliveryTotal(),
            'extras' => $this->extras,
            'extras_total' => $this->extrasTotal,
            'deposit' => $this->deposit,
            'total' => $this->total(),
        ];
    }
}
