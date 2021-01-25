<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ProgramMaster extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('program_master', function (Blueprint $table) {
            $table->increments('id');
            $table->string('program_code',20);
            $table->string('program_name',200);
            $table->index('program_code');
            $table->index('id');
            $table->unique(['program_code']);
            $table->integer('inst_uid')->nullable();
            $table->index('inst_uid');
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
        Schema::dropIfExists('program_master');
    }
}
