<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CollegesMaster extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('colleges_master', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username',200);
            $table->foreign('username')->references('username')->on('users_register')->onDelete('cascade');
            $table->string('inst_id',20);
            $table->index('inst_id');
            $table->string('inst_name',500);
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
        Schema::dropIfExists('colleges_master');
    }
}
