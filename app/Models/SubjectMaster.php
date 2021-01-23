<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectMaster extends Model
{
    use HasFactory;
    protected $table = 'subject_master';
    
    protected $fillable = ['id','paper_code','paper_name','program_id','semester','created_at','updated_at','paper_id','marks','exam_name','questions','marks1','marks2','marks3',
    'marks4','from_date','from_time','to_date','to_time','created_at','updated_at','durations',
    'instructions','active','score_view','review_question','proctoring','photo_capture','capture_interval',
    'negative_marking','time_remaining_reminder','exam_switch_alerts','option_shuffle','question_marks','ph_time'];

    protected $hidden = [

    ];

    public function exam()
    {
        return $this->hasMany('App\Models\CandTest','paper_id');
    }

    public function getTopics()
    {
        return $this->hasMany('App\Models\TopicMaster','paper_id');
    }

    public function getQuestions()
    {
        return $this->hasMany('App\Models\QuestionSet','paper_id');
    }
}
?>
