<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'address', 'working_hours'];

    /** Jedini red podešavanja; ako ne postoji, vraća se podrazumevani (nesnimljen) red. */
    public static function current(): self
    {
        return static::query()->first() ?? new static(config('rentacar.default_company'));
    }
}
