<?php namespace Waka\Mailer\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Schema;

class CreateWakaMailsTableU150 extends Migration
{
    public function up()
    {
        Schema::table('waka_mailer_waka_mails', function (Blueprint $table) {
            $table->boolean('auto_send')->nullable()->default(true);
            $table->boolean('has_log')->nullable()->default(true);
            $table->boolean('open_log')->nullable()->default(false);
            $table->boolean('click_log')->nullable()->default(false);
            $table->boolean('has_sender')->nullable()->default(false);
            $table->string('sender')->nullable();
        });
    }

    public function down()
    {
        Schema::table('waka_mailer_waka_mails', function (Blueprint $table) {
            $table->dropColumn('auto_send');
            $table->dropColumn('has_log');
            $table->dropColumn('open_log');
            $table->dropColumn('click_log');
            $table->dropColumn('has_sender');
            $table->dropColumn('sender');
        });
    }
}