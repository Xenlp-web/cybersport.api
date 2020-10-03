<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutoTournOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pubg_auto_tourn_options', function (Blueprint $table) {
            $table->id();
            $table->string('mode');
            $table->integer('tickets');
            $table->integer('kill_award');
            $table->integer('mvp_award');
            $table->integer('max_players');
            $table->integer('placement_award');
            $table->integer('winners');
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
        Schema::dropIfExists('auto_tourn_options');
    }
}
