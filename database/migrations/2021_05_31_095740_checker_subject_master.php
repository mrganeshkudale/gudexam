<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CheckerSubjectMaster extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checker_subject_master', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('instId');
            $table->index('instId');
            $table->bigInteger('uid');
            $table->index('uid');
            $table->integer('paperId');
            $table->index('paperId');
            $table->string('type',5);
            $table->index('type');
            $table->timestamps();
            $table->unique(['uid','paperId','type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('checker_subject_master');
    }
}
