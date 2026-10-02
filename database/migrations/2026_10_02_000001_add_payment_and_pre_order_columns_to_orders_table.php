<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Mirrors the customer API's migrations. Every column is guarded so running this
// before or after the API's own migrations is safe against the shared database.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'payment_status')) {
                $table->string('payment_status')->default('unpaid')->after('payment_type');
            }
            if (! Schema::hasColumn('orders', 'is_pre_order')) {
                $table->boolean('is_pre_order')->default(false)->after('payment_status');
            }
            if (! Schema::hasColumn('orders', 'deposit_amount')) {
                $table->decimal('deposit_amount', 10, 2)->nullable()->after('is_pre_order');
            }
        });
    }

    public function down(): void
    {
        // Intentionally empty: the columns are owned by the customer API's migrations.
    }
};
