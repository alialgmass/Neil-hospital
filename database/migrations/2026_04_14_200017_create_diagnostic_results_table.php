<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diagnostic_results', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('booking_id');
            $table->string('test_name', 150);
            $table->enum('eye', ['OD', 'OS', 'OU'])->nullable();
            $table->text('result_text')->nullable();
            $table->json('result_values')->nullable();
            $table->string('image_path', 255)->nullable();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('doctor_notes')->nullable();
            $table->dateTime('recorded_at');
            $table->timestamps();

            $table->foreign('booking_id')->references('id')->on('bookings')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnostic_results');
    }
};
