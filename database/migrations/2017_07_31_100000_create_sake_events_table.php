<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSakeEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sake_events', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->unique()->index();
            $table->text('summary');
            $table->integer('prefecture_id')->index();
            $table->string('location');
            $table->text('description');
            $table->timestamp('started_at')->index();
            $table->timestamp('ended_at')->index();
            $table->boolean('is_all_day');
            $table->boolean('is_recommended');
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
        Schema::dropIfExists('sake_events');
    }
}
