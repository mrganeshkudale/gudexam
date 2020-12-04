<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TopicMaster extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('topic_master', function (Blueprint $table) {
            $table->increments('id');
            $table->string('paper_code',20);
            $table->integer('topic');
            $table->integer('subtopic')->default(0);
            $table->integer('questions');
            $table->index('paper_code');
            $table->index('topic');
            $table->index('subtopic');
            $table->index('id');
            $table->unique(['paper_code','topic','subtopic']);
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
        Schema::dropIfExists('topic_master');
    }
}
