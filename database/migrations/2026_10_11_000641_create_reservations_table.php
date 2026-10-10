<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('vehicle_id')->constrained()->restrictOnDelete();
            $table->foreignId('pickup_location_id')->constrained('locations')->restrictOnDelete();
            $table->foreignId('return_location_id')->constrained('locations')->restrictOnDelete();
            $table->date('starts_on');
            $table->date('ends_on'); // datum vracanja (iskljucivo, vozilo je slobodno tog dana)
            $table->unsignedSmallInteger('days');
            $table->string('status', 20)->default('pending'); // pending|confirmed|active|completed|cancelled
            $table->unsignedInteger('rental_total')->default(0);
            $table->unsignedInteger('discount_total')->default(0);
            $table->unsignedInteger('delivery_total')->default(0);
            $table->unsignedInteger('extras_total')->default(0);
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('deposit')->default(0);
            $table->json('price_breakdown')->nullable();
            $table->text('customer_note')->nullable();
            $table->text('internal_note')->nullable();
            // Priprema za fazu 2 (online placanje)
            $table->string('payment_method', 30)->default('pay_on_pickup');
            $table->string('payment_status', 20)->default('unpaid');
            $table->string('source', 20)->default('web'); // web|admin
            $table->timestamp('consent_at')->nullable();
            $table->timestamps();
            $table->index(['vehicle_id', 'starts_on', 'ends_on']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
