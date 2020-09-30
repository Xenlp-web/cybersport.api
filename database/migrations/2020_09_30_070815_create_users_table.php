<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('pubg_id')->default('0');
            $table->string('pubg_name')->default('0');
            $table->string('password');
            $table->integer('team_id')->default('0');
            $table->integer('coins')->default('0');
            $table->integer('coins_bonus')->default('0');
            $table->integer('tickets')->default('0');
            $table->string('referal_code')->default('');
            $table->integer('coins_from_referals')->default('0');
            $table->integer('rating')->default('0');
            $table->integer('kills')->default('0');
            $table->integer('deaths')->default('0');
            $table->integer('matches')->default('0');
            $table->tinyInteger('confirmed_email')->default('0');
            $table->tinyInteger('banned')->default('0');
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
        Schema::dropIfExists('users');
    }
}
