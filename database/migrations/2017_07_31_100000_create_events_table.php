<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type', 16);
            $table->string('code', 191)->unique()->index();
            $table->string('url')->nullable()->index();
            $table->text('summary')->nullable();
            $table->integer('prefecture_id')->index();
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('started_at')->index()->nullable();
            $table->timestamp('ended_at')->index()->nullable();
            $table->boolean('is_all_day');
            $table->boolean('is_recommended');
            $table->timestamps();
            $table->index(['created_at', 'updated_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
}
