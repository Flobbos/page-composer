<?php

use Flobbos\PageComposer\Models\PageTranslation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Slugs become unique per language. Existing duplicates are resolved first:
 * the oldest translation keeps the slug, later ones get -2, -3... Empty
 * slugs become NULL so they don't count as duplicates.
 */
return new class extends Migration
{
    public function up()
    {
        $table = (new PageTranslation())->getTable();

        DB::table($table)->where('slug', '')->update(['slug' => null]);

        $duplicates = DB::table($table)
            ->select('language_id', 'slug')
            ->whereNotNull('slug')
            ->groupBy('language_id', 'slug')
            ->havingRaw('count(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $taken = DB::table($table)
                ->where('language_id', $duplicate->language_id)
                ->pluck('slug')
                ->filter()
                ->flip();

            $ids = DB::table($table)
                ->where('language_id', $duplicate->language_id)
                ->where('slug', $duplicate->slug)
                ->orderBy('id')
                ->pluck('id')
                ->slice(1);

            foreach ($ids as $id) {
                $i = 2;
                while ($taken->has($duplicate->slug . '-' . $i)) {
                    $i++;
                }

                $slug = $duplicate->slug . '-' . $i;
                $taken->put($slug, true);

                DB::table($table)->where('id', $id)->update(['slug' => $slug]);
            }
        }

        Schema::table($table, function (Blueprint $blueprint) {
            // Created before the tables were prefixed, so it kept its old name.
            $blueprint->dropIndex('page_translations_language_id_slug_index');
            $blueprint->unique(['language_id', 'slug']);
        });
    }

    public function down()
    {
        $table = (new PageTranslation())->getTable();

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->dropUnique(['language_id', 'slug']);
            $blueprint->index(['language_id', 'slug'], 'page_translations_language_id_slug_index');
        });
    }
};
