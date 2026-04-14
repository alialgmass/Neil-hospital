<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treasury_entries', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->enum('type', ['in', 'out']);
            $table->string('description', 300);
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->string('reference_no', 50)->nullable();
            $table->string('beneficiary', 150)->nullable();
            $table->ulid('account_id')->nullable();
            $table->enum('source', ['manual', 'booking', 'payment', 'purchase'])->default('manual');
            $table->ulid('booking_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts')->nullOnDelete();
            $table->foreign('booking_id')->references('id')->on('bookings')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treasury_entries');
    }
};
