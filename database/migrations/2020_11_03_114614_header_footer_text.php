<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class HeaderFooterText extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('header_footer_text', function (Blueprint $table) {
          $table->increments('id');
          $table->string('header',50);
          $table->string('footer',50);
          $table->string('logo',500)->nullable();
          $table->index('id');
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
        Schema::dropIfExists('header_footer_text');
    }
}
