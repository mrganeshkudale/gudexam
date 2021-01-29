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
            $table->index('id');
            $table->bigInteger('global_uid');
            $table->index('global_uid');
            $table->string('gname',200);
            $table->bigInteger('cluster_uid');
            $table->index('cluster_uid');
            $table->string('cname',200);
            $table->unique(['global_uid', 'cluster_uid']);
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
