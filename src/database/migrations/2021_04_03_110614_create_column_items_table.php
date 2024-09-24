<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateColumnItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('column_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('column_id')->constrained()->cascadeOnDelete();
            $table->foreignId('element_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('sorting')->default(1);
            $table->json('attributes')->nullable();
            $table->boolean('active')->default(true);
            $table->json('content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('column_items');
    }
}
