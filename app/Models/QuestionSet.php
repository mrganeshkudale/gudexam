<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionSet extends Model
{
    use HasFactory;
    protected $table = 'question_set';
    protected $fillable = ['id','qnid', 'paper_id', 'question', 'topic', 'subtopic', 'qu_fig', 'figure', 'optiona',
    'a1', 'optionb', 'a2', 'optionc', 'a3', 'optiond', 'a4', 'correctanswer', 'coption', 'ambiguity', 'marks',
    'psetter', 'moderator', 'updated_status', 'difficulty_level', 'created_at', 'updated_at'];

    protected $primaryKey = 'qnid';

    public function getSubject()
    {
        return $this->belongsTo('App\Models\SubjectMaster','paper_id');
    }

    public function answer()
    {
        return $this->belongsTo('App\Models\CandQuestion','qnid');
    }
}
