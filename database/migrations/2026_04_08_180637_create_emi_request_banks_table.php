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
        Schema::create('emi_request_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emi_request_id')->constrained('emi_requests')->onDelete('cascade');
            $table->foreignId('bank_id')->constrained('emi_banks')->onDelete('cascade');
            $table->string('account_number');
            $table->string('branch');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emi_request_banks');
    }
};
