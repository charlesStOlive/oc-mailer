<?php namespace Waka\Mailer\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Schema;

class CreateWakaMailsTableU170 extends Migration
{
    public function up()
    {
        Schema::table('waka_mailer_waka_mails', function (Blueprint $table) {
            $table->dropColumn('model_functions');
            $table->dropColumn('is_scope');
            $table->dropColumn('scopes');
            $table->dropColumn('has_asks');
            $table->dropColumn('asks');
            $table->dropColumn('images');
        });
    }

    public function down()
    {
        Schema::table('waka_mailer_waka_mails', function (Blueprint $table) {
            $table->text('model_functions')->nullable();
            $table->boolean('is_scope')->nullable()->default(false);
            $table->text('scopes')->nullable();
            $table->boolean('has_asks')->nullable();
            $table->text('asks')->nullable();
            $table->text('images')->nullable();
        });
    }
}