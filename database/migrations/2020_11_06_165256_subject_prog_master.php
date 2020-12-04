<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SubjectProgMaster extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('subject_prog_master', function (Blueprint $table) {
          $table->increments('id');
          $table->string('paper_code',20)->references('paper_code')->on('subject_master')->onDelete('cascade');
          $table->string('program_code',20)->references('program_code')->on('program_master')->onDelete('cascade');
          $table->index('id');
          $table->index('paper_code');
          $table->index('program_code');
          $table->unique(['paper_code','program_code']);
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
        Schema::dropIfExists('subject_prog_master');
    }
}
