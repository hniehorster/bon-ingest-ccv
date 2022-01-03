<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTheDefaultLanguageToTheBusinessTokens extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('business_tokens', function (Blueprint $table) {
            $table->string('language', 2)->after('cluster')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('business_tokens', function (Blueprint $table) {
            $table->dropColumn('language');
        });
    }
}
