<?php

use Illuminate\Support\Facades\Route;

/*
| API v1 – rezervisano za fazu 2 (mobilna aplikacija).
| Sva poslovna logika (dostupnost, cena, rezervacija) živi u App\Services,
| tako da će kontroleri ovde biti tanki.
*/
Route::prefix('v1')->name('api.v1.')->group(function () {
    //
});
