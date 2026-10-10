<?php

use App\Http\Controllers\FleetController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/vozila', [FleetController::class, 'index'])->name('fleet.index');
Route::get('/vozila/{vehicle}', [FleetController::class, 'show'])->name('fleet.show');

Route::get('/rezervacija', [ReservationController::class, 'create'])->name('reservation.create');
Route::get('/rezervacija/potvrda/{reservation:number}', [ReservationController::class, 'confirmation'])
    ->middleware('signed')->name('reservation.confirmation');

Route::get('/kontakt', [PageController::class, 'contact'])->name('contact');
Route::post('/kontakt', [PageController::class, 'sendContact'])->middleware('throttle:5,1')->name('contact.send');
Route::get('/uslovi-najma', [PageController::class, 'terms'])->name('terms');
Route::get('/politika-privatnosti', [PageController::class, 'privacy'])->name('privacy');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
