<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name', 200);
            $table->string('code', 40)->unique()->nullable();
            $table->string('category', 80)->nullable();
            $table->string('unit', 30)->nullable();
            $table->decimal('quantity', 10, 2)->default(0);
            $table->decimal('min_quantity', 10, 2)->default(0);
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->decimal('sell_price', 10, 2)->default(0);
            $table->ulid('supplier_id')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('location', 80)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('supplier_id')->references('id')->on('suppliers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
