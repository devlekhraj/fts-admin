<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Active blogs created before the admin set a publish date have none, and the website sorts them last.
// Give them their creation date. Drafts are left untouched so they get a date when first activated.
return new class extends Migration
{
    public function up(): void
    {
        DB::table('blogs')
            ->where('status', 1)
            ->whereNull('publish_date')
            ->update(['publish_date' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        // Intentionally empty: the original values were NULL and cannot be told apart from real dates afterwards.
    }
};
