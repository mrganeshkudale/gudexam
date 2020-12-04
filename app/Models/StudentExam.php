<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentExam extends Model
{
    use HasFactory;
    protected $table = 'student_exams';
    protected $fillable = ['id','username','inst','course','paper_code','created_at','updated_at'];
}
