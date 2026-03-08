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
        Schema::create('emi_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_id')->unique();
            $table->foreignId('emi_request_id')->constrained('emi_requests');
            $table->foreignId('emi_bank_id')->constrained('emi_banks');
            $table->string('file_path');
            $table->json('application_data');
            $table->enum('status',['generated','approved','rejected','cancelled'])->default('generated');
            $table->index('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emi_applications');
    }
};
