<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExamSessionReset extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('exam_session_reset', function (Blueprint $table) {
          $table->bigInteger('id');
          $table->integer('exam_id');
          $table->index('exam_id');
          $table->timestamp('session_start_time', $precision = 0)->nullable();
          $table->index('session_start_time');
          $table->timestamp('last_update_time', $precision = 0)->nullable();
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
        Schema::dropIfExists('exam_session_reset');
    }
}
