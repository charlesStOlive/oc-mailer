<?php namespace Waka\Mailer\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateBlocsTable extends Migration
{
    public function up()
    {
        Schema::create('waka_mailer_blocs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->boolean('is_mjml')->nullable();
            $table->string('name');
            $table->text('contenu');
            $table->integer('data_source_id')->unsigned()->nullable();
            //reorder
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('waka_mailer_blocs');
    }
}