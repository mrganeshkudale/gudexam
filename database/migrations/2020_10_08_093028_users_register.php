<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UsersRegister extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_register', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username',200);
            $table->foreign('username')->references('username')->on('users')->onDelete('cascade');
            $table->index('username');
            $table->string('regi_type',20);
            $table->index('regi_type');
            $table->string('eadmin_name',200)->nullable();
            $table->string('inst_id',20)->default('0000');
            $table->index('inst_id');
            $table->string('college_name',500)->nullable();
            $table->bigInteger('mobile')->nullable();
            $table->index('mobile');
            $table->string('email',200)->nullable();
            $table->index('email');
            $table->string('password',500);
            $table->string('docpath',500)->nullable();
            $table->string('status',10);
            $table->string('verify_on',50)->nullable();
            $table->index('status');
            $table->bigInteger('wallet_balance');
            $table->index('wallet_balance');
            $table->timestamps();
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
        Schema::dropIfExists('users_register');
    }
}
