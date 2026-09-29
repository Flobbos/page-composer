<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The preview route looks pages up by slug on every request. Not unique
 * yet: existing installs may already hold duplicates, and new saves now
 * de-duplicate them per language.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('page_translations', function (Blueprint $table) {
            $table->index(['language_id', 'slug']);
        });
    }

    public function down()
    {
        Schema::table('page_translations', function (Blueprint $table) {
            $table->dropIndex(['language_id', 'slug']);
        });
    }
};
