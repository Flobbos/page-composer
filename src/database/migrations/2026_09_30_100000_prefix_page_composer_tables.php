<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Moves the package tables under pagecomposer.table_prefix (default "pc_").
 * Earlier migrations still create the unprefixed tables, so fresh installs
 * and upgrades both end up here. Foreign keys follow a renamed table on
 * MySQL, PostgreSQL and SQLite, so they keep pointing at the right place.
 *
 * Set the prefix before running this; changing it afterwards needs a
 * migration of your own.
 */
return new class extends Migration
{
    private const TABLES = [
        'languages',
        'categories',
        'category_translations',
        'elements',
        'pages',
        'page_translations',
        'rows',
        'columns',
        'column_items',
        'tags',
        'tag_translations',
        'page_tag',
        'bugs',
        'comments',
        'page_templates',
    ];

    private function prefix(): string
    {
        return (string) config('pagecomposer.table_prefix', 'pc_');
    }

    public function up()
    {
        $prefix = $this->prefix();

        if ($prefix === '') {
            return;
        }

        foreach (self::TABLES as $table) {
            if (Schema::hasTable($table) && !Schema::hasTable($prefix . $table)) {
                Schema::rename($table, $prefix . $table);
            }
        }
    }

    public function down()
    {
        $prefix = $this->prefix();

        if ($prefix === '') {
            return;
        }

        foreach (array_reverse(self::TABLES) as $table) {
            if (Schema::hasTable($prefix . $table) && !Schema::hasTable($table)) {
                Schema::rename($prefix . $table, $table);
            }
        }
    }
};
