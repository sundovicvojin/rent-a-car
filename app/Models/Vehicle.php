<?php

namespace App\Models;

use App\Enums\Fuel;
use App\Enums\Transmission;
use App\Enums\VehicleStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Vehicle extends Model
{
    protected $fillable = [
        'name', 'slug', 'brand', 'model', 'year', 'category_id', 'seats', 'doors', 'luggage',
        'transmission', 'fuel', 'plate', 'daily_price', 'deposit', 'description', 'status', 'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'transmission' => Transmission::class,
            'fuel' => Fuel::class,
            'status' => VehicleStatus::class,
            'daily_price' => 'integer',
            'deposit' => 'integer',
            'is_featured' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $v) {
            $v->name = $v->name ?: trim("{$v->brand} {$v->model}");
            if (! $v->slug) {
                $base = Str::slug($v->name.' '.$v->year);
                $slug = $base;
                $i = 2;
                while (static::where('slug', $slug)->where('id', '!=', $v->id ?? 0)->exists()) {
                    $slug = $base.'-'.$i++;
                }
                $v->slug = $slug;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(VehicleImage::class)->orderByDesc('is_primary')->orderBy('sort_order')->orderBy('id');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(VehicleImage::class)->orderByDesc('is_primary')->orderBy('sort_order')->orderBy('id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(VehicleBlock::class);
    }

    public function seasonalPrices(): HasMany
    {
        return $this->hasMany(SeasonalPrice::class);
    }

    public function scopeBookable(Builder $query): Builder
    {
        return $query->where('status', VehicleStatus::Active->value);
    }
}
