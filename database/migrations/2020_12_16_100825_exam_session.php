<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExamSession extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('exam_session', function (Blueprint $table) {
          $table->bigIncrements('id');
          $table->integer('exam_id');
          $table->index('exam_id');
          $table->string('session_start_time',50);
          $table->index('session_start_time');
          $table->string('last_update_time',50);
          $table->index('last_update_time');
          $table->string('session_state',20);
          $table->index('session_state');
          $table->integer('elapsed_time');
          $table->index('elapsed_time');
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
        Schema::dropIfExists('exam_session');
    }
}
