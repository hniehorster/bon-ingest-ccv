<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultsToHandshak extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('handshakes', function (Blueprint $table) {
            $table->text('language', 2)->after('external_identifier')->nullable();
            $table->text('defaults')->after('return_url')->nullable();
            $table->text('internal_api_key')->after('defaults')->nullable();
            $table->text('internal_api_secret')->after('internal_api_key')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('handshakes', function (Blueprint $table) {
            $table->dropColumn('external_identifier');
            $table->dropColumn('defaults');
            $table->dropColumn('internal_api_key');
            $table->dropColumn('internal_api_secret');
        });
    }
}
