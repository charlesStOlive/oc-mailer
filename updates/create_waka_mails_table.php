<?php namespace Waka\Mailer\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Schema;

class CreateWakaMailsTable extends Migration
{
    public function up()
    {
        Schema::create('waka_mailer_waka_mails', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('subject');
            $table->boolean('is_mjml')->nullable()->default(false);
            $table->text('mjml')->nullable();
            $table->text('html')->nullable();
            $table->text('model_functions')->nullable();
            $table->text('images')->nullable();
            $table->boolean('is_scope')->nullable()->default(false);
            $table->text('scopes')->nullable();
            $table->string('name');
            $table->string('slug');
            $table->string('data_source');
            $table->integer('layout_id')->unsigned()->nullable();
            $table->text('mjml_html')->nullable();
            //reorder
            $table->integer('sort_order')->default(0);
            //softDelete
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('waka_mailer_waka_mails');
    }
}