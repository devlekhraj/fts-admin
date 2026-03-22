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
        Schema::table('user_shipping_addresses', function (Blueprint $table) {
            if (!Schema::hasColumn('user_shipping_addresses', 'label')) {
                $table->string('label')->nullable()->after('landmark');
            }
            if (!Schema::hasColumn('user_shipping_addresses', 'lat')) {
                $table->double('lat')->nullable()->after('label');
            }
            if (!Schema::hasColumn('user_shipping_addresses', 'lng')) {
                $table->double('lng')->nullable()->after('lat');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_shipping_addresses', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('user_shipping_addresses', 'label')) {
                $columnsToDrop[] = 'label';
            }
            if (Schema::hasColumn('user_shipping_addresses', 'lat')) {
                $columnsToDrop[] = 'lat';
            }
            if (Schema::hasColumn('user_shipping_addresses', 'lng')) {
                $columnsToDrop[] = 'lng';
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};