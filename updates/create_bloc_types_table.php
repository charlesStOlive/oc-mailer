<?php namespace Waka\Mailer\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateBlocTypesTable extends Migration
{
    public function up()
    {
        Schema::create('waka_mailer_bloc_types', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->string('code');
            $table->text('config');
            $table->text('template_html');
            $table->text('datasource_accepted')->nullable();
            $table->boolean('use_icon')->default(0);
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('waka_mailer_bloc_types');
    }
}
