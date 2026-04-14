<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('invoice_no', 50)->unique();
            $table->ulid('supplier_id')->nullable();
            $table->date('invoice_date');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('remaining', 12, 2)->default(0);
            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('supplier_id')->references('id')->on('suppliers')->nullOnDelete();
        });

        Schema::create('purchase_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->ulid('invoice_id');
            $table->ulid('item_id')->nullable();
            $table->string('item_name', 200);
            $table->decimal('qty', 10, 2);
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('purchase_invoices')->cascadeOnDelete();
            $table->foreign('item_id')->references('id')->on('inventory')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_invoice_items');
        Schema::dropIfExists('purchase_invoices');
    }
};
