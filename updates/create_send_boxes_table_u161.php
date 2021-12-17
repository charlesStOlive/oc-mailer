<?php namespace Waka\Mailer\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Schema;

class CreateSendBoxesTableU161 extends Migration
{
    public function up()
    {
        Schema::table('waka_mailer_send_boxes', function (Blueprint $table) {
            $table->boolean('is_embed')->nullable()->default(false);
        });
    }

    public function down()
    {
        Schema::table('waka_mailer_send_boxes', function (Blueprint $table) {
            $table->dropColumn('is_embed');
        });
    }
}