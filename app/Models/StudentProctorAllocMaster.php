<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentProctorAllocMaster extends Model
{
    use HasFactory;
    protected $table = 'student_proctor_alloc_master';
    protected $fillable = ['id','instId','checkerid','studid','createdAt','updatedAt'];
}
?>