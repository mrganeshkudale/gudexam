<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSettings extends Model
{
    use HasFactory;
    protected $table = 'exam_settings';
    protected $fillable = ['id','description','action','created_at','updated_at'];
}
