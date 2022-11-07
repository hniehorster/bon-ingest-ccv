<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHandShakes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('handshakes', function (Blueprint $table) {
            $table->id();
            $table->string('hash')->nullable();
            $table->string('api_public')->nullable();
            $table->string('api_secret')->nullable();
            $table->string('api_root')->nullable();
            $table->string('return_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('handshakes');
    }
}
