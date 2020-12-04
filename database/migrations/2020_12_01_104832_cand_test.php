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
          $table->string('stdid',20);
          $table->index('stdid');
          $table->string('inst',20);
          $table->index('inst');
          $table->string('paper_code',20);
          $table->index('paper_code');
          $table->string('course',20);
          $table->index('course');
          $table->string('starttime',100);
          $table->index('starttime');
          $table->string('endtime',100)->nullable();
          $table->index('endtime');
          $table->string('cqnid',500)->nullable();
          $table->string('wqnid',500)->nullable();
          $table->string('uqnid',500)->nullable();
          $table->string('status',20);
          $table->index('status');
          $table->string('entry_on',100)->nullable();
          $table->string('end_on',100)->nullable();
          $table->string('end_by',20)->nullable();
          $table->string('examip',20)->nullable();
          $table->string('continueexam',5)->nullable();
          $table->string('pa',5);
          $table->index('pa');
          $table->string('marksobt',50)->nullable();
          $table->primary(['stdid','inst','paper_code']);
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
