<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Users extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('uid');
            $table->string('username',200);
            $table->index('username');
            $table->string('pcode',50)->nullable();
            $table->index('pcode');
            $table->string('seatno',50)->nullable();
            $table->index('seatno');
            $table->string('inst_id',20)->default('0000');
            $table->index('inst_id');
            $table->string('region',50)->nullable();
            $table->index('region');
            $table->string('course_code',10)->nullable();
            $table->index('course_code');
            $table->integer('semester')->nullable();
            $table->index('semester');
            $table->bigInteger('mobile')->nullable();
            $table->index('mobile');
            $table->string('email',200)->nullable();
            $table->index('email');
            $table->string('role',20);
            $table->index('role');
            $table->string('password',500);
            $table->string('origpass',500);
            $table->string('pa',5)->nullable();
            $table->index('pa');
            $table->string('status',5);
            $table->index('status');
            $table->string('name',500);
            $table->string('regi_type',20);
            $table->index('regi_type');
            $table->string('college_name',500)->nullable();
            $table->string('docpath',500)->nullable();
            $table->string('verified',10);
            $table->timestamp('verify_on', $precision = 3)->nullable();
            $table->index('verified');
            $table->bigInteger('wallet_balance')->default('0');
            $table->index('wallet_balance');
            $table->timestamps();
            $table->unique(['username','inst_id']);
            $table->unique(['mobile']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
