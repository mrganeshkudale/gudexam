<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectMaster extends Model
{
    use HasFactory;
    protected $table = 'subject_master';
    protected $fillable = ['id','paper_code','paper_name','program_code','semester','created_at','updated_at'];
}
