<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaperSetterSubjectMaster extends Model
{
    use HasFactory;
    protected $table = 'paper_setter_subject_master';
    protected $fillable = ['id','uid','paperId','type','createdAt','updatedAt','instId'];
}
