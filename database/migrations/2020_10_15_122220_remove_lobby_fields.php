<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveLobbyFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pubg_tournaments_info', function (Blueprint $table) {
            $table->dropColumn(['lobby_id', 'lobby_pass']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pubg_tournaments_info', function (Blueprint $table) {
            //
        });
    }
}
