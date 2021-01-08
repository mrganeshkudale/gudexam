<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CandTest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('cand_test', function (Blueprint $table) {
          $table->bigIncrements('id');
          $table->bigInteger('stdid');
          $table->index('stdid');
          $table->string('inst',20);
          $table->index('inst');
          $table->integer('paper_id');
          $table->index('paper_id');
          $table->integer('program_id');
          $table->index('program_id');
          $table->string('starttime',100);
          $table->index('starttime');
          $table->string('endtime',100)->nullable();
          $table->index('endtime');
          $table->string('cqnid',5000)->nullable();
          $table->string('wqnid',5000)->nullable();
          $table->string('uqnid',5000)->nullable();
          $table->string('status',20);
          $table->index('status');
          $table->string('entry_on',100)->nullable();
          $table->string('end_on',100)->nullable();
          $table->string('end_by',20)->nullable();
          $table->string('examip',20)->nullable();
          $table->integer('continueexam')->default('0');
          $table->index('continueexam');
          $table->string('pa',5);
          $table->index('pa');
          $table->integer('switched')->default('0');
          $table->index('switched');
          $table->string('marksobt',50)->nullable();
          $table->unique(['stdid','inst','paper_id']);
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
        Schema::dropIfExists('cand_test');
    }
}
