<?php namespace Waka\Mailer\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Schema;

class CreateMailLogsTableU203 extends Migration
{
    public function up()
    {
        Schema::table('waka_mailer_mail_logs', function (Blueprint $table) {
            $table->integer('count')->nullable();
        });
    }

    public function down()
    {
        Schema::table('waka_mailer_mail_logs', function (Blueprint $table) {
            $table->dropColumn('count');
        });
    }
}