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
        Schema::table('emi_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('emi_requests', 'is_deleted')) {
                $table->boolean('is_deleted')->nullable()->after('status');
            }

            if (! Schema::hasColumn('emi_requests', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable()->after('is_deleted');
            }

            if (! Schema::hasColumn('emi_requests', 'deleted_by')) {
                $table->foreignId('deleted_by')
                    ->nullable()
                    ->after('deleted_at')
                    ->constrained('admins')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emi_requests', function (Blueprint $table) {
            if (Schema::hasColumn('emi_requests', 'deleted_by')) {
                $table->dropConstrainedForeignId('deleted_by');
            }

            if (Schema::hasColumn('emi_requests', 'deleted_at')) {
                $table->dropColumn('deleted_at');
            }

            if (Schema::hasColumn('emi_requests', 'is_deleted')) {
                $table->dropColumn('is_deleted');
            }
        });
    }
};

