<?php namespace Waka\Mailer\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Schema;

class CreateWakaMailsTableU200 extends Migration
{
    public function up()
    {
        Schema::table('waka_mailer_waka_mails', function (Blueprint $table) {
            $table->dropColumn('test_id');
            $table->dropColumn('data_source');
        });
    }

    public function down()
    {
        Schema::table('waka_mailer_waka_mails', function (Blueprint $table) {
            $table->string('test_id')->nullable();
            $table->string('data_source');
        });
    }
}