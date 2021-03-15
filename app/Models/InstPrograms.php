<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstPrograms extends Model
{
    use HasFactory;
    protected $table = 'inst_programs';
    protected $fillable = ['id','program_id','inst_uid','created_at','updated_at'];
}
