<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncFilesMetaCommand extends Command
{
    protected $signature = 'files:sync-meta
        {--dry-run : Show changes without updating DB}
        {--chunk=500 : Chunk size for processing rows}';

    protected $description = 'Sync only files.meta (and updated_at) from Foundation seed data by id';

    public function handle(): int
    {
        $seedFile = app_path('Foundation/Infrastructure/Persistence/SeedData/files.php');
        if (! file_exists($seedFile)) {
            $this->error(sprintf('Seed file not found: %s', $seedFile));

            return self::FAILURE;
        }

        $rows = require $seedFile;
        if (! is_array($rows)) {
            $this->error('Seed file must return an array.');

            return self::FAILURE;
        }

        $chunkSize = max(1, (int) $this->option('chunk'));
        $dryRun = (bool) $this->option('dry-run');

        $total = 0;
        $updated = 0;
        $missing = 0;
        $skipped = 0;
        $now = now();

        foreach (array_chunk($rows, $chunkSize) as $chunk) {
            foreach ($chunk as $row) {
                $total++;

                if (! is_array($row) || ! isset($row['id'])) {
                    $skipped++;
                    continue;
                }

                $id = (int) $row['id'];
                if ($id <= 0) {
                    $skipped++;
                    continue;
                }

                $exists = DB::table('files')->where('id', $id)->exists();
                if (! $exists) {
                    $missing++;
                    continue;
                }

                if ($dryRun) {
                    $updated++;
                    continue;
                }

                DB::table('files')
                    ->where('id', $id)
                    ->update([
                        'meta' => $row['meta'] ?? null,
                        'updated_at' => $now,
                    ]);

                $updated++;
            }
        }

        $this->info(sprintf(
            'Processed: %d | Updated: %d | Missing IDs: %d | Skipped rows: %d%s',
            $total,
            $updated,
            $missing,
            $skipped,
            $dryRun ? ' [dry-run]' : ''
        ));

        return self::SUCCESS;
    }
}
