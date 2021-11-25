<?php namespace Waka\Mailer\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Schema;

class CreateSendBoxesTableU160 extends Migration
{
    public function up()
    {
        Schema::table('waka_mailer_send_boxes', function (Blueprint $table) {
            $table->longtext('content')->change();
        });
    }

    public function down()
    {
        Schema::table('waka_mailer_send_boxes', function (Blueprint $table) {
            $table->text('content')->change();
        });
    }
}