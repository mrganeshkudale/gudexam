<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class StudentExam extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_exams', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username',20);
            $table->string('inst',20);
            $table->string('course',20);
            $table->string('paper_code',50);
            
            $table->index('username');
            $table->index('inst');
            $table->index('course');
            $table->index('paper_code');

            $table->unique(['username','inst','paper_code']);
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
        Schema::dropIfExists('student_exams');
    }
}
