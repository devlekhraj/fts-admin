<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Marks the Administrator role as the one that can do everything.
 * Every other role is limited to the permissions listed on it. The flag cannot be set from the admin screens.
 */
return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('roles', 'is_super')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->boolean('is_super')->default(false)->after('permissions');
            });
        }

        // The role called Administrator. If there is none (an unusual install), the first role, so someone can always get in.
        $super = DB::table('roles')->whereRaw('LOWER(name) = ?', ['administrator'])->pluck('id')->all();
        if ($super === []) {
            $first = DB::table('roles')->orderBy('id')->value('id');
            $super = $first ? [$first] : [];
        }
        if ($super !== []) {
            DB::table('roles')->whereIn('id', $super)->update(['is_super' => true]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('roles', 'is_super')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropColumn('is_super');
            });
        }
    }
};
