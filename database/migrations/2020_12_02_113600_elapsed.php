<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Elapsed extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('elapsed', function (Blueprint $table) {
          $table->string('stdid',20);
          $table->index('stdid');
          $table->string('inst',20);
          $table->index('inst');
          $table->string('paper_code',20);
          $table->index('paper_code');
          $table->integer('elapsedTime');
          $table->index('elapsedTime');
          $table->primary(['stdid','inst','paper_code']);
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
        Schema::dropIfExists('elapsed');
    }
}
