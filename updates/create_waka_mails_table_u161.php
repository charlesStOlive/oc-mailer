<?php namespace Waka\Mailer\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Schema;

class CreateWakaMailsTableU161 extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('waka_mailer_waka_mails', 'is_embed')){
            Schema::table('waka_mailer_waka_mails', function (Blueprint $table) {
                $table->boolean('is_embed')->nullable()->default(false);
            });
        }
    }

    public function down()
    {
        Schema::table('waka_mailer_waka_mails', function (Blueprint $table) {
            $table->dropColumn('is_embed');
        });
    }
}