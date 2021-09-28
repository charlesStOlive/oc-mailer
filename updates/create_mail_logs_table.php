<?php namespace Waka\Mailer\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Schema;

class CreateMailLogsTable extends Migration
{
    public function up()
    {
        Schema::create('waka_mailer_mail_logs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name')->nullable();
            $table->integer('waka_mail_id_id')->unsigned()->nullable();
            $table->string('logeable_type')->nullable();
            $table->integer('logeable_id')->nullable();
            $table->string('type')->nullable();
            $table->text('tags')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('waka_mailer_mail_logs');
    }
}