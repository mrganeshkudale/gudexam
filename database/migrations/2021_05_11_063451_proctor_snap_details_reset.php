<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ProctorSnapDetailsReset extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proctor_snap_details_reset', function (Blueprint $table) {
            $table->bigInteger('id');
            $table->index('id');
            $table->bigInteger('examid');
            $table->index('examid');
            $table->bigInteger('snapid');
            $table->index('snapid');
            $table->string('agerange',20);
            $table->boolean('beared');
            $table->index('beared');
            $table->boolean('eyeglasses');
            $table->index('eyeglasses');
            $table->boolean('eyesopen');
            $table->index('eyesopen');
            $table->string('gender',20);
            $table->index('gender');
            $table->boolean('mustache');
            $table->index('mustache');
            $table->boolean('smile');
            $table->index('smile');
            $table->boolean('sunglasses');
            $table->index('sunglasses');
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
        Schema::dropIfExists('proctor_snap_details_reset');
    }
}
