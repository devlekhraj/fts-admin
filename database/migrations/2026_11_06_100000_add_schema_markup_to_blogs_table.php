<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Per-blog structured data set from the SEO tab: { "faqs": [{question, answer}], "custom": "<json-ld string>" }.
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('blogs', 'schema_markup')) {
            return;
        }

        Schema::table('blogs', function (Blueprint $table) {
            $table->json('schema_markup')->nullable();
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('blogs', 'schema_markup')) {
            Schema::table('blogs', function (Blueprint $table) {
                $table->dropColumn('schema_markup');
            });
        }
    }
};
