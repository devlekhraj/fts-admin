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
            $table->foreignId('emi_request_id')
                ->constrained('emi_requests')
                ->cascadeOnDelete();
            $table->foreignId('emi_bank_id')
                ->constrained('emi_banks')
                ->cascadeOnDelete();
            $table->json('application_data');
            
            $table->text('file_path');

            $table->enum('status', ['Pending', 'Processing','Approved','Finished','Cancelled'])
                ->default('Pending');

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
