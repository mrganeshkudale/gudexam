<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CollegeMaster extends Model
{
    use HasFactory;
    protected $table = 'colleges_master';
    protected $fillable = ['id','username','inst_id','inst_name','created_at','updated_at'];
}
