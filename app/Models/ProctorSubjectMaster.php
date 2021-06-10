<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProctorSubjectMaster extends Model
{
    use HasFactory;
    protected $table = 'proctor_subject_master';
    protected $fillable = ['id','uid','paperId','createdAt','updatedAt'];
}
