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
        Schema::table('product_categories', function (Blueprint $table) {
            if(!Schema::hasColumn('product_categories', 'seq_no')) {
                $table->integer('seq_no')->default(0)->after('title');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            if(Schema::hasColumn('product_categories', 'seq_no')) {
                $table->dropColumn('seq_no');
            }
        });
    }
};
