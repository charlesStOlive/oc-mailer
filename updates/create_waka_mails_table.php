<?php namespace Waka\Mailer\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateWakaMailsTable extends Migration
{
    public function up()
    {
        Schema::create('waka_mailer_wakamails', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->string('slug');
            $table->string('subject');
            $table->text('template')->nullable();
            $table->text('mjml')->nullable();

            $table->integer('data_source_id')->unsigned()->nullable();

            $table->text('scopes')->nullable();
            $table->text('model_functions')->nullable();
            $table->text('images')->nullable();
            $table->text('add_fields')->nullable();

            $table->integer('sort_order')->default(0);

            $table->softDeletes();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('waka_mailer_wakamails');
    }
}
