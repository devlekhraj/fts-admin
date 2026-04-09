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
        Schema::create('emi_request_credit_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emi_request_id')->constrained('emi_requests')->onDelete('cascade');
            $table->string('card_number');
            $table->string('card_holder');
            $table->foreignId('card_provider')->constrained('emi_banks')->onDelete('cascade');
            $table->string('expiry_date');
            $table->decimal('credit_limit', 10, 2);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emi_request_credit_cards');
    }
};
