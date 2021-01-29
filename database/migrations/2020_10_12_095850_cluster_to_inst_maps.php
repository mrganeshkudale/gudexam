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
            $table->index('id');
            $table->bigInteger('cluster_uid');
            $table->string('cname',200)->nullable();
            $table->bigInteger('inst_uid');
            $table->string('inst_name',200)->nullable();
            $table->index('inst_uid');
            $table->index('cluster_uid');
            $table->unique(['inst_uid', 'cluster_uid']);
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
