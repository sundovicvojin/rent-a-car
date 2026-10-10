<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seasonal_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->nullable()->constrained()->cascadeOnDelete(); // null = sva vozila
            $table->string('name');
            $table->date('starts_on');
            $table->date('ends_on'); // ukljucivo
            $table->string('type', 20)->default('percent'); // percent|fixed
            $table->integer('value'); // procenat (npr. 20 ili -10) ili cena po danu u centima
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seasonal_prices');
    }
};
