<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableForGamesAndUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('games_and_users', function (Blueprint $table) {
            $table->id();
            $table->foreign('game_id')->references('id')->on('games');
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('player_id');
            $table->string('player_name');
            $table->integer('matches');
            $table->integer('kills');
            $table->integer('deaths');
            $table->integer('rating');
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
        Schema::dropIfExists('table_for_games_and_users');
    }
}
