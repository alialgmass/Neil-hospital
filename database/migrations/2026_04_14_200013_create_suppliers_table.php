<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name', 150);
            $table->string('phone', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('tax_no', 50)->nullable();
            $table->decimal('balance', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
