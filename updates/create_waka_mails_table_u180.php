<?php namespace Waka\Mailer\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Schema;

class CreateWakaMailsTableU180 extends Migration
{
    public function up()
    {
        Schema::table('waka_mailer_waka_mails', function (Blueprint $table) {
            $table->text('rule_contents')->nullable();
        });
    }

    public function down()
    {
        Schema::table('waka_mailer_waka_mails', function (Blueprint $table) {
            $table->dropColumn('rule_contents');
        });
    }
}