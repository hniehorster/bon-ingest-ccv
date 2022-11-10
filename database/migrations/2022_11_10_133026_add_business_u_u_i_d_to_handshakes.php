<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBusinessUUIDToHandshakes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('handshakes', function (Blueprint $table) {
            $table->uuid('business_uuid')->after('external_identifier')->nullable();
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
            $table->dropColumn('business_uuid');
        });
    }
}
