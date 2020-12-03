<?php namespace Waka\Mailer\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateWakaMailsTableU102 extends Migration
{
    public function up()
    {
        Schema::table('waka_mailer_waka_mails', function (Blueprint $table) {
            $table->string('test_id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('waka_mailer_waka_mails', function (Blueprint $table) {
            $table->dropColumn('test_id');
        });
    }
}