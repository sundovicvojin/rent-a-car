<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            // Dostava na proizvoljnu adresu (mapa): baza i cena po kilometru
            $table->decimal('base_latitude', 10, 7)->nullable();
            $table->decimal('base_longitude', 10, 7)->nullable();
            $table->unsignedInteger('fee_per_km')->nullable(); // cent
            $table->unsignedSmallInteger('max_delivery_km')->nullable();
        });

        Schema::table('reservations', function (Blueprint $table) {
            // {address, lat, lng, km, fee} kada je preuzimanje/vraćanje na izabranoj tački na mapi
            $table->json('pickup_custom')->nullable();
            $table->json('return_custom')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reservations', fn (Blueprint $t) => $t->dropColumn(['pickup_custom', 'return_custom']));
        Schema::table('company_settings', fn (Blueprint $t) => $t->dropColumn(['base_latitude', 'base_longitude', 'fee_per_km', 'max_delivery_km']));
    }
};
