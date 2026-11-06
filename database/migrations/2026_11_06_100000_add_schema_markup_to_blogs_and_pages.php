<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Per-blog and per-page structured data set from the SEO section: { "faqs": [{question, answer}], "custom": "<json-ld string>" }.
return new class extends Migration
{
    public function up(): void
    {
        foreach (['blogs', 'pages'] as $tableName) {
            if (Schema::hasColumn($tableName, 'schema_markup')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->json('schema_markup')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['blogs', 'pages'] as $tableName) {
            if (Schema::hasColumn($tableName, 'schema_markup')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('schema_markup');
                });
            }
        }
    }
};
