<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Mirrors the customer API's migration. Guarded, so running it before or after that one is safe.
return new class extends Migration
{
    public function up(): void
    {
        foreach (['emi_requests', 'emi_request_guarantors'] as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'grandfather_name')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->string('grandfather_name', 191)->nullable()->after('name');
                });
            }
        }
    }

    public function down(): void
    {
        // Intentionally empty: the column is owned by the customer API's migration.
    }
};
