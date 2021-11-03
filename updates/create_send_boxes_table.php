<?php namespace Waka\Mailer\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Schema;

class CreateSendBoxesTable extends Migration
{
    public function up()
    {
        Schema::create('waka_mailer_send_boxes', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->text('content');
            $table->string('name')->default('Actif');
            $table->timestamp('send_at')->nullable();
            $table->string('methode')->nullable();
            $table->string('state')->nullable();
            $table->string('maileable_type');
            $table->integer('maileable_id');
            $table->string('targeteable_type')->nullable();
            $table->string('targeteable_id')->nullable();
            $table->text('meta')->nullable();
            $table->string('tos')->nullable();
            $table->text('mail_vars')->nullable();
            $table->text('mail_tags')->nullable();
            //anaymisation
            $table->boolean('is_anonymized')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('waka_mailer_send_boxes');
    }
}