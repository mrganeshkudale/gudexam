<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CandTest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('cand_test', function (Blueprint $table) {
          $table->bigIncrements('id');
          $table->bigInteger('stdid');
          $table->index('stdid');
          $table->string('inst',20);
          $table->index('inst');
          $table->integer('paper_id');
          $table->index('paper_id');
          $table->integer('program_id');
          $table->index('program_id');
          $table->integer('curQuestion')->default(0);
          $table->timestamp('starttime', $precision = 3)->nullable();
          $table->index('starttime');
          $table->timestamp('endtime', $precision = 3)->nullable();
          $table->index('endtime');
          $table->string('cqnid',5000)->nullable();
          $table->string('wqnid',5000)->nullable();
          $table->string('uqnid',5000)->nullable();
          $table->string('status',20)->nullable();
          $table->index('status');
          $table->timestamp('entry_on', $precision = 3)->nullable();
          $table->timestamp('end_on', $precision = 3)->nullable();
          $table->string('end_by',20)->nullable();
          $table->string('examip',20)->nullable();
          
          $table->integer('continueexam')->default('0');
          $table->index('continueexam');
          $table->string('pa',5)->nullable();
          $table->index('pa');
          $table->bigInteger('switched')->default('0');
          $table->index('switched');
          $table->integer('marksobt')->nullable();
          $table->integer('paper_checking')->default('0');
          $table->integer('result')->default('0');
          $table->string('endExamReason',500)->nullable();
          $table->unique(['stdid','inst','paper_id']);
          $table->timestamps();
          $table->string('answerFile',2000)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cand_test');
    }
}
