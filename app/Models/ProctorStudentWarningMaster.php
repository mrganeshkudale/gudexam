<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProctorStudentWarningMaster extends Model
{
    use HasFactory;
    protected $table = 'proctor_student_warning_master';
    protected $fillable = [
        'id',
        'examId',
        'paperId',
        'instId',
        'proctor',
        'student',
        'warning',
        'noted',
        'warningNo',
        'created_at',
        'updated_at'
    ];
}
