<?php

namespace App\Models;

use App\Casts\DateOnly;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Reservation extends Model
{
    protected $fillable = [
        'number', 'customer_id', 'vehicle_id', 'pickup_location_id', 'return_location_id', 'starts_on', 'ends_on',
        'days', 'status', 'rental_total', 'discount_total', 'delivery_total', 'extras_total', 'total', 'deposit',
        'price_breakdown', 'pickup_custom', 'return_custom', 'customer_note', 'internal_note', 'payment_method', 'payment_status', 'source', 'consent_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => DateOnly::class,
            'ends_on' => DateOnly::class,
            'status' => ReservationStatus::class,
            'payment_method' => PaymentMethod::class,
            'payment_status' => PaymentStatus::class,
            'price_breakdown' => 'array',
            'pickup_custom' => 'array',
            'return_custom' => 'array',
            'consent_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $r) {
            $r->number ??= 'TMP-'.Str::uuid();
        });

        static::created(function (self $r) {
            if (str_starts_with($r->number, 'TMP-')) {
                $r->number = sprintf('RS-%s-%05d', $r->created_at->format('y'), $r->id);
                $r->saveQuietly();
            }
        });
    }

    /** Tekst mesta preuzimanja/vraćanja: naziv lokacije ili adresa sa mape. */
    public function pickupLabel(): string
    {
        return $this->pickup_custom ? 'Dostava na adresu: '.($this->pickup_custom['address'] ?: $this->coordsLabel($this->pickup_custom)) : $this->pickupLocation->name;
    }

    public function returnLabel(): string
    {
        return $this->return_custom ? 'Preuzimanje sa adrese: '.($this->return_custom['address'] ?: $this->coordsLabel($this->return_custom)) : $this->returnLocation->name;
    }

    private function coordsLabel(array $point): string
    {
        return sprintf('%.5f, %.5f', $point['lat'], $point['lng']);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function pickupLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'pickup_location_id');
    }

    public function returnLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'return_location_id');
    }

    public function extras(): HasMany
    {
        return $this->hasMany(ReservationExtra::class);
    }

    /** Rezervacije koje zauzimaju vozilo (sve osim otkazanih). */
    public function scopeBlocking(Builder $query): Builder
    {
        return $query->where('status', '!=', ReservationStatus::Cancelled->value);
    }
}
