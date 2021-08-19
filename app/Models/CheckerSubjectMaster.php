<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckerSubjectMaster extends Model
{
    use HasFactory;
    protected $table = 'checker_subject_master';
    protected $fillable = ['id','instId','uid','paperId','type','createdAt','updatedAt'];
}
?>
