<?php namespace Waka\Mailer\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Schema;

class CreateLayoutsTable extends Migration
{
    public function up()
    {
        Schema::create('waka_mailer_layouts', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->text('contenu');
            $table->string('baseCss');
            $table->text('AddCss')->nullable();
            //reorder
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('waka_mailer_layouts');
    }
}
