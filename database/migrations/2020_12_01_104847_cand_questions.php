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
          $table->string('stdid',20);
          $table->index('stdid');
          $table->string('inst',20);
          $table->index('inst');
          $table->string('paper_code',20);
          $table->index('paper_code');
          $table->string('course',20);
          $table->index('course');
          $table->integer('qnid');
          $table->index('qnid');
          $table->integer('qtopic');
          $table->integer('qtype');
          $table->string('answered',20);
          $table->string('stdanswer',20)->nullable();
          $table->integer('qnid_sr');
          $table->index('qnid_sr');
          $table->string('entry_on',100)->nullable();
          $table->string('answer_by',50)->nullable();
          $table->string('answer_on',100)->nullable();
          $table->string('cans',20);
          $table->index('cans');
          $table->integer('marks');
          $table->string('ip',50);
          $table->integer('obtmarks')->nullable();
          $table->primary(['stdid','inst','paper_code','qnid']);
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
