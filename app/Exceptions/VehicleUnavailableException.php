<?php

namespace App\Exceptions;

use RuntimeException;

class VehicleUnavailableException extends RuntimeException
{
    public function __construct(string $message = 'Vozilo više nije slobodno u izabranom terminu.')
    {
        parent::__construct($message);
    }
}
