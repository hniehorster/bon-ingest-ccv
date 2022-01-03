<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BusinessAuthTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('business_uuid')->nullable();
            $table->string('external_identifier')->nullable()->comment('External ID of the store');
            $table->string('cluster')->nullable()->default('eu1');
            $table->string('external_api_key')->nullable();
            $table->string('external_api_secret')->nullable();
            $table->string('internal_api_key')->nullable();
            $table->string('internal_api_secret')->nullable();
            $table->timestamps();

            $table->index('business_uuid');
            $table->unique('external_identifier');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('business_tokens');
    }
}
