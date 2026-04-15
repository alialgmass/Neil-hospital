<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_lists', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name', 150);
            $table->enum('type', ['cash', 'insurance', 'vip', 'special'])->default('cash');
            $table->ulid('ins_company_id')->nullable();
            $table->decimal('ins_coverage', 5, 2)->nullable();
            $table->decimal('discount_pct', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('ins_company_id')->references('id')->on('insurance_companies')->nullOnDelete();
        });

        Schema::create('price_list_items', function (Blueprint $table) {
            $table->id();
            $table->ulid('price_list_id');
            $table->ulid('service_id');
            $table->decimal('price', 10, 2)->default(0);
            $table->timestamps();

            $table->foreign('price_list_id')->references('id')->on('price_lists')->cascadeOnDelete();
            $table->foreign('service_id')->references('id')->on('services')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_list_items');
        Schema::dropIfExists('price_lists');
    }
};
