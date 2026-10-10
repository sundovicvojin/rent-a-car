<?php

namespace App\Models;

use App\Casts\DateOnly;
use App\Enums\PriceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeasonalPrice extends Model
{
    protected $fillable = ['vehicle_id', 'name', 'starts_on', 'ends_on', 'type', 'value', 'is_active'];

    protected function casts(): array
    {
        return [
            'starts_on' => DateOnly::class,
            'ends_on' => DateOnly::class,
            'type' => PriceType::class,
            'value' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
