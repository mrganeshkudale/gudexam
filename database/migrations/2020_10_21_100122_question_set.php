<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class QuestionSet extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('question_set', function (Blueprint $table) {
            $table->increments('qnid');
            $table->integer('paper_id');
            $table->text('question')->nullable();
            $table->integer('topic')->nullable();
            $table->integer('subtopic')->nullable();
            $table->text('qu_fig')->nullable();
            $table->string('figure',2)->default('N');
            $table->text('optiona')->nullable();
            $table->text('a1')->nullable();
            $table->text('optionb')->nullable();
            $table->text('a2')->nullable();
            $table->text('optionc')->nullable();
            $table->text('a3')->nullable();
            $table->text('optiond')->nullable();
            $table->text('a4')->nullable();
            $table->text('correctanswer')->nullable();
            $table->string('coption',20);
            $table->string('ambiguity',2)->nullable();
            $table->integer('marks');
            $table->string('psetter',30)->nullable();
            $table->string('moderator',30)->nullable();
            $table->integer('updated_status')->nullable();
            $table->integer('difficulty_level')->nullable();

            $table->index('qnid');
            $table->index('paper_id');
            $table->index('topic');
            $table->index('subtopic');
            $table->index('figure');
            $table->index('ambiguity');
            $table->index('marks');
            $table->index('difficulty_level');
            $table->unique(['qnid']);
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
        Schema::dropIfExists('question_set');
    }
}
