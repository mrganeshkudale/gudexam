<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ProctorStudentWarningMaster extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proctor_student_warning_master', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('examId');
            $table->index('examId');
            $table->bigInteger('paperId')->nullable();
            $table->index('paperId');
            $table->string('instId',100)->nullable();
            $table->index('instId');
            $table->string('proctor',100);
            $table->index('proctor');
            $table->string('student',100);
            $table->index('student');
            $table->string('warning',2000);
            $table->integer('noted')->default(0);
            $table->integer('warningNo');
            $table->index('warningNo');
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
        Schema::dropIfExists('proctor_student_warning_master');
    }
}
