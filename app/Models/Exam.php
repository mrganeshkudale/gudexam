<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;
    protected $table = 'test';
    protected $fillable = ['id','paper_code','marks','questions','marks1','marks2','marks3',
    'marks4','from_date','from_time','to_date','to_time','created_at','updated_at','exam_name','durations',
    'instructions','active','score_view','review_question','proctoring','photo_capture','capture_interval',
    'negative_marking','time_remaining_reminder','exam_switch_alerts','option_shuffle','question_marks',
    ];
}
