<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dr_payments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('doctor_id');
            $table->decimal('amount', 12, 2);
            $table->date('period_from');
            $table->date('period_to');
            $table->date('paid_at');
            $table->enum('method', ['cash', 'transfer'])->default('cash');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('doctor_id')->references('id')->on('doctors')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dr_payments');
    }
};
