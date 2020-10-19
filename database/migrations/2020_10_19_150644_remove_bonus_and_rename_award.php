<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveBonusAndRenameAward extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tournaments_and_users', function (Blueprint $table) {
            $table->dropColumn('bonus_award');
            $table->renameColumn('regular_award', 'award');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tournaments_and_users', function (Blueprint $table) {
            //
        });
    }
}
