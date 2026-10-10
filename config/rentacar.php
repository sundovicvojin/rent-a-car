<?php

return [
    'currency' => 'EUR',
    'currency_symbol' => '€',
    'min_rental_days' => 1,
    'max_rental_days' => 60,
    'default_company' => [
        'name' => 'Rent a Car Novi Sad',
        'phone' => '+381 21 123 456',
        'email' => 'info@example.com',
        'address' => 'Bulevar oslobođenja 1, 21000 Novi Sad',
        'working_hours' => "Pon–Pet 08:00–20:00\nSub 09:00–15:00\nNedelja: po dogovoru",
    ],
    // Milestone 5: baza za obračun doplate po udaljenosti (Novi Sad)
    'base_lat' => 45.2551,
    'base_lng' => 19.8452,
    'distance_fee_per_km' => 50, // u centima
];
