<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ProctorSnapsReset extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proctor_snaps_reset', function (Blueprint $table) {
            $table->bigInteger('id');
            $table->index('id');
            $table->bigInteger('examid');
            $table->index('examid');
            $table->string('path',2000);
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
        Schema::dropIfExists('proctor_snaps_reset');
    }
}
