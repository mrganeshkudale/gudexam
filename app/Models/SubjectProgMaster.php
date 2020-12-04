<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectProgMaster extends Model
{
    use HasFactory;
    protected $table = 'subject_prog_master';
    protected $fillable = ['id','paper_code','program_code','created_at','updated_at'];
}
