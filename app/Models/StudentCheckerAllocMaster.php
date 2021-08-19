<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentCheckerAllocMaster extends Model
{
    use HasFactory;
    protected $table = 'student_checker_alloc_master';
    protected $fillable = ['id','instId','checkerid','paperId','studid','type','createdAt','updatedAt'];
}
