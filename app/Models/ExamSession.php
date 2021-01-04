<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSession extends Model
{
  use HasFactory;
  protected $table = 'exam_session';
  protected $fillable = ['id','exam_id','session_start_time','last_update_time','session_state','elapsed_time','created_at','updated_at'];

  public function exam()
  {
    return $this->belongsTo('App\Models\CandTest','exam_id');
  }
}
