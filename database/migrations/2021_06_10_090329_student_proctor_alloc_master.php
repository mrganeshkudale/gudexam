<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class StudentProctorAllocMaster extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_proctor_alloc_master', function (Blueprint $table) {
            $table->increments('id');
            $table->string('instId',20);
            $table->index('instId');
            $table->bigInteger('proctorid');
            $table->index('proctorid');
            $table->integer('paperId');
            $table->index('paperId');
            $table->bigInteger('studid');
            $table->index('studid');
            $table->timestamps();
            $table->unique(['instId','proctorid','paperId','studid'],'uniqinstproctstud');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_proctor_alloc_master');
    }
}
