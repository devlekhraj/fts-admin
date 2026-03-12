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
        Schema::create('file_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')
                ->constrained('files')
                ->cascadeOnDelete();

            $table->string('usage_type');         // product, post, page
            $table->unsignedBigInteger('usage_id');

            $table->string('title')->nullable();
            $table->string('alt_text')->nullable();

            $table->json('meta')->nullable();

            $table->timestamps();

            $table->unique(['file_id', 'usage_type', 'usage_id']);
            $table->index(['usage_type', 'usage_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_usages');
    }
};
