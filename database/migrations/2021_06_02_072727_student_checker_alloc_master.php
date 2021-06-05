<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class StudentCheckerAllocMaster extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_checker_alloc_master', function (Blueprint $table) {
            $table->increments('id');
            $table->string('instId',20);
            $table->index('instId');
            $table->bigInteger('checkerid');
            $table->index('checkerid');
            $table->integer('paperId');
            $table->index('paperId');
            $table->bigInteger('studid');
            $table->index('studid');
            $table->timestamps();
            $table->unique(['instId','checkerid','paperId','studid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_checker_alloc_master');
    }
}
