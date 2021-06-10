<?php namespace Waka\Mailer\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Schema;

class CreateWakaMailsTableU103 extends Migration
{
    public function up()
    {
        Schema::table('waka_mailer_waka_mails', function (Blueprint $table) {
            $table->text('pjs')->nullable();
        });
    }

    public function down()
    {
        Schema::table('waka_mailer_waka_mails', function (Blueprint $table) {
            $table->dropColumn('pjs');
        });
    }
}
