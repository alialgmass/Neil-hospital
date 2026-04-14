<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('or_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->enum('status', ['available', 'occupied', 'cleaning', 'maintenance'])->default('available');
            $table->unsignedTinyInteger('total_beds')->default(1);
            $table->timestamps();
        });

        Schema::create('or_beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('or_rooms')->cascadeOnDelete();
            $table->string('bed_number', 10);
            $table->enum('status', ['available', 'occupied', 'reserved'])->default('available');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('or_beds');
        Schema::dropIfExists('or_rooms');
    }
};
