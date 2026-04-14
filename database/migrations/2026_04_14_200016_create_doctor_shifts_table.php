<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_shifts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('doctor_id');
            $table->date('shift_date');
            $table->enum('dept', ['clinic', 'labs', 'surgery', 'lasik', 'laser']);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('cases_count')->default(0);
            $table->decimal('total_revenue', 12, 2)->default(0);
            $table->decimal('doctor_share', 12, 2)->default(0);
            $table->decimal('center_share', 12, 2)->default(0);
            $table->text('handover_notes')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('doctor_id')->references('id')->on('doctors')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_shifts');
    }
};
