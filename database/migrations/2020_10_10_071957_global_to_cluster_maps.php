<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GlobalToClusterMaps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('global_to_cluster_maps', function (Blueprint $table) {
            $table->increments('id');

            $table->string('global_username',200);
            $table->foreign('global_username')->references('username')->on('users')->onDelete('cascade');
            $table->string('gname',200);

            $table->string('cluster_username',200);
            $table->foreign('cluster_username')->references('username')->on('users')->onDelete('cascade');
            $table->string('cname',200);
            
            $table->index('global_username');
            $table->index('cluster_username');
            $table->unique(['global_username', 'cluster_username']);
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
            Schema::dropIfExists('global_to_cluster_maps');
    }
}
