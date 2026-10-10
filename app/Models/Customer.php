<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'notes'];

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
