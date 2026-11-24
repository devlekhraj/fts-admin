<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The SEO title, keywords and description were varchar(191). Descriptions and keyword lists longer than that failed to
 * save with a server error ("Data too long for column 'meta_keywords'"). They become TEXT, so nothing saved is ever cut.
 * Existing text is kept as it is; whether a column may be empty does not change.
 */
return new class extends Migration
{
    private const TABLES = ['products', 'product_categories', 'product_brands', 'blogs', 'blog_categories', 'pages'];

    private const COLUMNS = ['meta_title', 'meta_keywords', 'meta_description'];

    public function up(): void
    {
        $database = DB::getDatabaseName();

        foreach (self::TABLES as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $parts = [];
            foreach (self::COLUMNS as $column) {
                $info = DB::selectOne(
                    'SELECT DATA_TYPE AS t, IS_NULLABLE AS n FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                    [$database, $table, $column]
                );
                if ($info === null || strtolower((string) $info->t) === 'text') {
                    continue;
                }
                $parts[] = "MODIFY `{$column}` TEXT " . ($info->n === 'YES' ? 'NULL' : 'NOT NULL');
            }

            if ($parts !== []) {
                DB::statement("ALTER TABLE `{$table}` " . implode(', ', $parts));
            }
        }
    }

    public function down(): void
    {
        // Not reverted on purpose: shrinking the columns again would cut any longer text saved since.
    }
};
