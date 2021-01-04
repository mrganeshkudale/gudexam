<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClusterToInstMap extends Model
{
    use HasFactory;
    protected $table = 'cluster_to_inst_maps';
    protected $fillable = ['id','cluster_uid','cname','inst_uid','inst_name','created_at','updated_at'];
}
