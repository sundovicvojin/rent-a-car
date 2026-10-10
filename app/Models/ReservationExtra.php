<?php

namespace App\Models;

use App\Enums\BillingType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationExtra extends Model
{
    protected $fillable = ['reservation_id', 'extra_id', 'name', 'billing', 'unit_price', 'total'];

    protected function casts(): array
    {
        return ['billing' => BillingType::class, 'unit_price' => 'integer', 'total' => 'integer'];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}
