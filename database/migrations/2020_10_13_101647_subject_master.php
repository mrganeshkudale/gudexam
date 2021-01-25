<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SubjectMaster extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subject_master', function (Blueprint $table) {
            $table->increments('id');
            $table->string('paper_code',20);
            $table->string('paper_name',100);
            $table->integer('program_id');
            $table->integer('inst_uid')->nullable();
            $table->integer('semester');
            $table->string('exam_name',200)->nullable();
            $table->integer('marks')->default('0');
            $table->integer('questions')->default('0');
            $table->integer('marks1')->default('0');
            $table->integer('marks2')->default('0');
            $table->integer('marks3')->default('0');
            $table->integer('marks4')->default('0');
            $table->integer('durations')->default('0');
            $table->timestamp('from_date', $precision = 3)->nullable();
            $table->timestamp('to_date', $precision = 3)->nullable();


            $table->integer('active')->default('0');
            $table->integer('score_view')->default('0');
            $table->integer('review_question')->default('0');
            $table->integer('proctoring')->default('0');
            $table->integer('photo_capture')->default('0');
            $table->integer('capture_interval')->default('0');
            $table->integer('negative_marking')->default('0');
            $table->integer('negative_marks')->default('0');
            $table->integer('time_remaining_reminder')->default('0');
            $table->integer('exam_switch_alerts')->default('99999');
            $table->integer('option_shuffle')->default('0');
            $table->integer('question_marks')->default('0');
            $table->text('instructions')->nullable();
            $table->integer('ph_time')->default('0');

            $table->index('id');
            $table->index('inst_uid');
            $table->index('questions');
            $table->index('marks');
            $table->index('marks1');
            $table->index('marks2');
            $table->index('marks3');
            $table->index('marks4');
            $table->index('from_date');
            $table->index('to_date');

            $table->index('paper_code');
            $table->index('program_id');
            $table->index('semester');
            $table->unique(['paper_code']);
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
        Schema::dropIfExists('subject_master');
    }
}
