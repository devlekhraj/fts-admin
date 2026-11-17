<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('exchange_requests') || Schema::hasColumn('exchange_requests', 'team_notified_at')) {
            return;
        }

        Schema::table('exchange_requests', function (Blueprint $table): void {
            // Set when the team has been emailed about this request. Requests already in the table are marked as notified.
            $table->timestamp('team_notified_at')->nullable()->index();
        });

        DB::table('exchange_requests')->whereNull('team_notified_at')->update(['team_notified_at' => now()]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('exchange_requests', 'team_notified_at')) {
            Schema::table('exchange_requests', function (Blueprint $table): void {
                $table->dropColumn('team_notified_at');
            });
        }
    }
};
