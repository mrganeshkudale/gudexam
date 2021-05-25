<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSessionReset extends Model
{
    use HasFactory;
  protected $table = 'exam_session_reset';
  protected $fillable = ['id','exam_id','session_start_time','last_update_time','session_state','elapsed_time','created_at','updated_at'];

}
