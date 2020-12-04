<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ClusterToInstMaps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cluster_to_inst_maps', function (Blueprint $table) {
            $table->increments('id');

            $table->string('cluster_username',200);
            $table->foreign('cluster_username')->references('username')->on('users')->onDelete('cascade');
            $table->string('cname',200)->nullable();

            $table->string('inst_username',200);
            $table->foreign('inst_username')->references('username')->on('users')->onDelete('cascade');
            $table->string('inst_name',200)->nullable();
            
            $table->index('inst_username');
            $table->index('cluster_username');
            $table->unique(['inst_username', 'cluster_username']);
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
        Schema::dropIfExists('cluster_to_inst_maps');
    }
}
