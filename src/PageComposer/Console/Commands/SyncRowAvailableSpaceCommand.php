<?php

namespace Flobbos\PageComposer\Console\Commands;

use Flobbos\PageComposer\Models\Row;
use Illuminate\Console\Command;

class SyncRowAvailableSpaceCommand extends Command
{
    protected $signature = 'page-composer:sync-row-space {--dry-run : Show changes without writing to the database}';

    protected $description = 'Recalculate rows.available_space from saved column sizes';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $rows = Row::query()
            ->with(['columns:id,row_id,column_size'])
            ->get(['id', 'available_space']);

        $updated = 0;
        $unchanged = 0;

        foreach ($rows as $row) {
            $usedSpace = (int) $row->columns->sum(fn($column) => (int) $column->column_size);
            $expectedAvailableSpace = max(0, 12 - $usedSpace);
            $currentAvailableSpace = (int) $row->available_space;

            if ($currentAvailableSpace === $expectedAvailableSpace) {
                $unchanged++;
                continue;
            }

            $this->line("Row {$row->id}: {$currentAvailableSpace} -> {$expectedAvailableSpace}");

            if (!$dryRun) {
                $row->update(['available_space' => $expectedAvailableSpace]);
            }

            $updated++;
        }

        if ($dryRun) {
            $this->info("Dry run complete. {$updated} row(s) would be updated, {$unchanged} already correct.");
        } else {
            $this->info("Sync complete. Updated {$updated} row(s); {$unchanged} already correct.");
        }

        return self::SUCCESS;
    }
}
