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
        Schema::table('carts', function (Blueprint $table) {
            if (!Schema::hasColumn('carts', 'coupon_id')) {
                $table->foreignId('coupon_id')->after('is_processed')->nullable()->constrained('coupons')->onDelete('set null');
                $table->string('coupon_code')->after('coupon_id')->nullable();
                $table->decimal('discount_amount', 10, 2)->after('coupon_code')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            if (Schema::hasColumn('carts', 'coupon_id')) {
                $table->dropForeign(['coupon_id']);
                $table->dropColumn(['coupon_id', 'coupon_code', 'discount_amount']);
            }
        });
    }
};
