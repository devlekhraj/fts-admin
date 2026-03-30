<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('user_shipping_addresses', 'order_id')) {
            Schema::table('user_shipping_addresses', function (Blueprint $table) {
                $table->foreignId('order_id')
                    ->after('user_id')
                    ->nullable()
                    ->constrained('orders')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('user_shipping_addresses', 'order_id')) {
            Schema::table('user_shipping_addresses', function (Blueprint $table) {
                $table->dropForeign(['order_id']);
                $table->dropColumn('order_id');
            });
        }
    }
};
