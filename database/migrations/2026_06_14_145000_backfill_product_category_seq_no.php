<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_categories') || ! Schema::hasColumn('product_categories', 'seq_no')) {
            return;
        }

        $categories = DB::table('product_categories')
            ->orderByRaw('CASE WHEN created_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get(['id']);

        foreach ($categories as $index => $category) {
            DB::table('product_categories')
                ->where('id', $category->id)
                ->update(['seq_no' => $index + 1]);
        }
    }

    public function down(): void
    {
        //
    }
};
