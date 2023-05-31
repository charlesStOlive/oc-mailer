<?php namespace Waka\Mailer\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Schema;

class CreateSendBoxesTableU161 extends Migration
{
    public function up()
    {
        Schema::table('waka_mailer_send_boxes', function (Blueprint $table) {
            $table->boolean('has_reply_to')->default(false);
            $table->string('has_cci')->default(false);
            $table->string('cci')->nullable();
        });
    }

    public function down()
    {
        Schema::table('waka_mailer_send_boxes', function (Blueprint $table) {
            $table->dropColumn('has_reply_to');
            $table->dropColumn('has_cci');
            $table->dropColumn('cci');
        });
    }
}