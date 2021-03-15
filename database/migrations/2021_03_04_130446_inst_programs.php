<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InstPrograms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inst_programs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('program_id');
            $table->integer('inst_uid');
            $table->index('id');
            $table->index('program_id');
            $table->index('inst_uid');
            $table->unique(['program_id','inst_uid']);
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
        Schema::dropIfExists('inst_programs');
    }
}
