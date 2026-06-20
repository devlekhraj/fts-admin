<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_brands') || ! Schema::hasColumn('product_brands', 'seq_no')) {
            return;
        }

        $brands = DB::table('product_brands')
            ->orderByRaw('CASE WHEN created_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get(['id']);

        foreach ($brands as $index => $brand) {
            DB::table('product_brands')
                ->where('id', $brand->id)
                ->update(['seq_no' => $index + 1]);
        }
    }

    public function down(): void
    {
        //
    }
};
