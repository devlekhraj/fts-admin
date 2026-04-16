<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // What this activity belongs to
            // Example: Order, EmiRequest, Payment, Product
            $table->morphs('entity');

            // Who performed the action
            // Example: User, Admin
            $table->nullableMorphs('actor');

            // System key
            // Example: order_created, payment_received, emi_approved
            $table->string('action');

            // Human-readable title for UI
            // Example: Order placed
            $table->string('label');

            // Optional detail for timeline
            $table->text('description')->nullable();

            // Optional status tracking
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();

            // Extra data
            $table->json('meta')->nullable();

            // We only need created_at for logs
            $table->timestamp('created_at')->useCurrent();

            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
