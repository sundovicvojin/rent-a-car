<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Filament\Tables\Table::configureUsing(fn (\Filament\Tables\Table $table) => $table
            ->defaultDateDisplayFormat('d.m.Y')
            ->defaultDateTimeDisplayFormat('d.m.Y H:i'));

        \Carbon\Carbon::setLocale('sr_Latn');

        \Illuminate\Support\Facades\View::composer(['components.layouts.*', 'emails.*', 'pages.*', 'fleet.*', 'reservation.*'], fn ($view) => $view->with('company', \App\Models\CompanySetting::current()));
        //
    }
}
