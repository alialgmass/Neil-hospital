<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surgeries', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('or_bed_id')->nullable()->constrained('or_beds')->nullOnDelete();
            $table->foreignUlid('surgeon_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->enum('dept', ['surgery', 'lasik', 'laser'])->default('surgery');
            $table->string('eye', 4)->nullable();           // OD, OS, OU
            $table->string('procedure', 300)->nullable();
            $table->enum('anaesthesia', ['local', 'general', 'topical', 'sedation'])->nullable();
            $table->enum('status', ['scheduled', 'prep', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->text('pre_op_notes')->nullable();
            $table->text('op_report')->nullable();
            $table->text('post_op_notes')->nullable();
            $table->text('complications')->nullable();
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->json('supplies_used')->nullable();      // [{item_id, name, qty, unit_cost, total}]
            $table->decimal('supply_total', 10, 2)->default(0);
            $table->timestamps();

            $table->index(['dept', 'status']);
            $table->index('surgeon_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surgeries');
    }
};
