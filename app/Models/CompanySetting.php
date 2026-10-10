<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'address', 'working_hours', 'base_latitude', 'base_longitude', 'fee_per_km', 'max_delivery_km'];

    /** @return array{lat: float, lng: float, fee_per_km: int, max_km: int} */
    public function delivery(): array
    {
        $d = config('rentacar.delivery');

        return [
            'lat' => (float) ($this->base_latitude ?? $d['base_lat']),
            'lng' => (float) ($this->base_longitude ?? $d['base_lng']),
            'fee_per_km' => (int) ($this->fee_per_km ?? $d['fee_per_km']),
            'max_km' => (int) ($this->max_delivery_km ?? $d['max_km']),
        ];
    }

    /** Jedini red podešavanja; ako ne postoji, vraća se podrazumevani (nesnimljen) red. */
    public static function current(): self
    {
        return static::query()->first() ?? new static(config('rentacar.default_company'));
    }
}
