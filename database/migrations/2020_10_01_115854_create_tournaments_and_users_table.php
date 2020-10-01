<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTournamentsAndUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tournaments_and_users', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('tournament_id');
            $table->integer('placement');
            $table->integer('regular_award');
            $table->integer('bonus_award');
            $table->integer('total_rating');
            $table->tinyInteger('mvp')->default('0');
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
        Schema::dropIfExists('tournaments_and_users');
    }
}
