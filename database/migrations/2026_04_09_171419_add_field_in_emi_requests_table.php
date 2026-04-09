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
            if (! Schema::hasColumn('emi_requests', 'emi_type')) {
                $table->string('emi_type')->after('id')->nullable();
            }
            if (Schema::hasColumn('emi_requests', 'bank')) {
                $table->integer('bank')->nullable()->change();
            }
            if (Schema::hasColumn('emi_requests', 'credit_card')) {
                $table->tinyInteger('credit_card')->nullable()->change();
            }
            if (! Schema::hasColumn('emi_requests', 'interest_rate')) {
                $table->double('interest_rate', 8, 2)->after('finance_amount')->nullable();
            }
            if (! Schema::hasColumn('emi_requests', 'product_variant')) {
                $table->json('product_variant')->after('product_id')->nullable();
            }
            if (! Schema::hasColumn('emi_requests', 'nid_number')) {
                $table->string('nid_number')->after('contact_number')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emi_requests', function (Blueprint $table) {
            if (Schema::hasColumn('emi_requests', 'emi_type')) {
                $table->dropColumn('emi_type');
            }
            if (Schema::hasColumn('emi_requests', 'interest_rate')) {
                $table->dropColumn('interest_rate');
            }
            if (Schema::hasColumn('emi_requests', 'product_variant')) {
                $table->dropColumn('product_variant');
            }
            if (Schema::hasColumn('emi_requests', 'nid_number')) {
                $table->dropColumn('nid_number');
            }
        });
    }
};
