<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramMaster extends Model
{
    use HasFactory;
    protected $table = 'program_master';
    protected $fillable = ['id','program_code','program_name','created_at','updated_at'];
}
