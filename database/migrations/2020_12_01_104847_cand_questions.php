<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CandQuestions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('cand_questions', function (Blueprint $table) {
          $table->bigIncrements('id');
          $table->index('id');
          $table->bigInteger('exam_id');
          $table->index('exam_id');
          $table->bigInteger('stdid');
          $table->index('stdid');
          $table->string('inst',20);
          $table->index('inst');
          $table->integer('paper_id');
          $table->index('paper_id');
          $table->integer('program_id');
          $table->index('program_id');
          $table->integer('qnid');
          $table->index('qnid');
          $table->integer('qtopic');
          $table->integer('qtype');
          $table->string('answered',20);
          $table->string('stdanswer',20)->nullable();
          $table->integer('qnid_sr');
          $table->index('qnid_sr');
          $table->timestamp('entry_on', $precision = 3)->nullable();
          $table->string('answer_by',50)->nullable();
          $table->timestamp('answer_on', $precision = 3)->nullable();
          $table->string('cans',20);
          $table->index('cans');
          $table->integer('marks');
          $table->string('ip',50);
          $table->integer('obtmarks')->nullable();
          $table->unique(['stdid','inst','paper_id','qnid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cand_questions');
    }
}
