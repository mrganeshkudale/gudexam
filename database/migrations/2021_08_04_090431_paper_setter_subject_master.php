<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PaperSetterSubjectMaster extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paper_setter_subject_master', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('uid');
            $table->index('uid');
            $table->integer('paperId');
            $table->index('paperId');
            $table->timestamps();
            $table->unique(['uid','paperId']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('paper_setter_subject_master');
    }
}
