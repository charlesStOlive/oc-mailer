<?php namespace Waka\Mailer\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateWakaMailsTableU102 extends Migration
{
    public function up()
    {
        Schema::table('waka_mailer_wakamails', function (Blueprint $table) {
            $table->boolean('is_mjml')->default(1);
            $table->text('mjml')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('waka_mailer_wakamails', function (Blueprint $table) {
            $table->dropColumn('is_mjml');
        });
    }
}