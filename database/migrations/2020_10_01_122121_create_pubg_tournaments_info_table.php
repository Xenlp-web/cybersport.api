<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePubgTournamentsInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pubg_tournaments_info', function (Blueprint $table) {
            $table->id();
            $table->integer('tournament_id');
            $table->string('map');
            $table->string('mode');
            $table->string('pov');
            $table->integer('current_players');
            $table->integer('max_players');
            $table->integer('winners');
            $table->integer('placement_award');
            $table->integer('kill_award');
            $table->integer('mvp_award');
            $table->string('lobby_id')->default('');
            $table->string('lobby_pass')->default('');
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
        Schema::dropIfExists('pubg_tournaments_info');
    }
}
