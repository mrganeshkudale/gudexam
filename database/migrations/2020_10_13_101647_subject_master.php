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
            $table->string('program_code',20);
            $table->integer('semester');
            
            $table->index('paper_code');
            $table->index('program_code');
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
