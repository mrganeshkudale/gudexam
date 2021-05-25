<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LoginLink extends Migration
{
    public function up()
    {
        Schema::create('login_link', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->index('id');
            $table->bigInteger('stduid');
            $table->index('stduid');
            $table->string('inst_id',20);
            $table->index('inst_id');
            $table->string('link',500);
            $table->unique(['stduid','inst_id']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('login_link');
    }
}
