<?php namespace Waka\Mailer\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateWakaMailsTableU104 extends Migration
{
    public function up()
    {
        Schema::table('waka_mailer_wakamails', function (Blueprint $table) {
            $table->text('scope_type')->nullable();
        });
    }

    public function down()
    {
        Schema::table('waka_mailer_wakamails', function (Blueprint $table) {
            $table->dropColumn('scope_type');
        });
    }
}
