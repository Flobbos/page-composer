<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * pages.category_id used to cascade on delete, so removing a category
 * hard-deleted every page in it (bypassing soft deletes). Pages now keep
 * living with a null category instead.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->change();
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable(false)->change();
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
        });
    }
};
