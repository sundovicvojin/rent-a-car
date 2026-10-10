<?php

namespace App\Models;

use App\Enums\BillingType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Extra extends Model
{
    protected $fillable = ['name', 'description', 'price', 'billing', 'is_active'];

    protected function casts(): array
    {
        return ['price' => 'integer', 'billing' => BillingType::class, 'is_active' => 'boolean'];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
