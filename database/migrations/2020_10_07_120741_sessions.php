<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Sessions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('sessions', function (Blueprint $table) {
          $table->increments('session_id');
          $table->index('session_id');
          $table->bigInteger('uid');
          $table->index('uid');
          $table->string('role',20);
          $table->index('role');
          $table->string('ip',50);
          $table->string('browser',100);
          $table->index('browser');
          $table->string('os',100);
          $table->index('os');
          $table->string('version',100);
          $table->index('version');
          $table->index('ip');
          $table->timestamp('starttime', $precision = 3)->nullable();
          $table->timestamp('endtime', $precision = 3)->nullable();
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
        Schema::dropIfExists('sessions');
    }
}
