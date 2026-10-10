<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('brand');
            $table->string('model');
            $table->unsignedSmallInteger('year');
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('seats')->default(5);
            $table->unsignedTinyInteger('doors')->default(5);
            $table->unsignedTinyInteger('luggage')->default(2);
            $table->string('transmission', 20)->default('manual'); // manual|automatic
            $table->string('fuel', 20)->default('petrol'); // petrol|diesel|hybrid|electric|lpg
            $table->string('plate')->unique();
            $table->unsignedInteger('daily_price'); // cent
            $table->unsignedInteger('deposit')->default(0); // cent
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active')->index(); // active|inactive|service
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
