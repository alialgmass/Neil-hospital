<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_permits', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('permit_no', 50)->unique();
            $table->enum('type', ['in', 'out']);
            $table->string('department', 80)->nullable();
            $table->string('reason', 300)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('stock_permit_items', function (Blueprint $table) {
            $table->id();
            $table->ulid('permit_id');
            $table->ulid('item_id')->nullable();
            $table->string('item_name', 200);
            $table->decimal('qty', 10, 2);
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->timestamps();

            $table->foreign('permit_id')->references('id')->on('stock_permits')->cascadeOnDelete();
            $table->foreign('item_id')->references('id')->on('inventory')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_permit_items');
        Schema::dropIfExists('stock_permits');
    }
};
