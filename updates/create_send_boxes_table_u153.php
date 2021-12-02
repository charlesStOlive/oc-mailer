<?php namespace Waka\Mailer\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Schema;

class CreateSendBoxesTableU153 extends Migration
{
    public function up()
    {
        Schema::table('waka_mailer_send_boxes', function (Blueprint $table) {
            $table->string('reply_to')->nullable();
            $table->boolean('open_log')->nullable();
            $table->boolean('click_log')->nullable();
        });
    }

    public function down()
    {
        Schema::table('waka_mailer_send_boxes', function (Blueprint $table) {
            $table->dropColumn('reply_to');
            $table->dropColumn('open_log');
            $table->dropColumn('click_log');
        });
    }
}