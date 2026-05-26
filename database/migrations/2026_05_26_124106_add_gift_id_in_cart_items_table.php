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
        Schema::table('cart_items', function (Blueprint $table) {
            if(!Schema::hasColumn('cart_items', 'gift_id')) {
                $table->foreignId('gift_id')->nullable()->after('vendor_id')->constrained('product_gifts')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            if(Schema::hasColumn('cart_items', 'gift_id')) {
                $table->dropForeign(['gift_id']);
                $table->dropColumn('gift_id');
            }
        });
    }
};
