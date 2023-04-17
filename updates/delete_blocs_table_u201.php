<?php namespace Waka\Mailer\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Schema;

class DeleteBlocsTableU201 extends Migration
{
    public function up()
    {
        Schema::dropIfExists('waka_mailer_blocs');
    }

    public function down()
    {
        
    }
}
