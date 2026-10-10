<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LongRentalDiscount extends Model
{
    protected $fillable = ['min_days', 'percent', 'is_active'];

    protected function casts(): array
    {
        return ['min_days' => 'integer', 'percent' => 'integer', 'is_active' => 'boolean'];
    }
}
