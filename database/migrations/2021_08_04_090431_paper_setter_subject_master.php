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
            $table->integer('instId');
            $table->index('instId');
            $table->bigInteger('uid');
            $table->index('uid');
            $table->integer('paperId');
            $table->index('paperId');
            $table->string('type',5);
            $table->index('type');
            $table->integer('conf')->default(0);
            $table->index('conf');
            $table->timestamps();
            $table->unique(['instId','uid','paperId']);
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
