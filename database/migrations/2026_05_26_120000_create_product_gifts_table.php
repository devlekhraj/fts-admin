<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_gifts')) {
            return;
        }

        Schema::create('product_gifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('gift_id')->constrained('products')->cascadeOnDelete();
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->unique(['product_id', 'gift_id']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('product_gifts')) {
            return;
        }

        Schema::dropIfExists('product_gifts');
    }
};
