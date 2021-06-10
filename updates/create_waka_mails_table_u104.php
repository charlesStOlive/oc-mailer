<?php namespace Waka\Mailer\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Schema;

class CreateWakaMailsTableU104 extends Migration
{
    public function up()
    {
        Schema::table('waka_mailer_waka_mails', function (Blueprint $table) {
            $table->boolean('no_ds')->default(true)->after('subject');
            $table->string('data_source')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('waka_mailer_waka_mails', function (Blueprint $table) {
            $table->dropColumn('no_ds');
        });
    }
}
