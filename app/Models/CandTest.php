<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandTest extends Model
{
    use HasFactory;
    protected $table = 'cand_test';
    protected $fillable = ['id','stdid','inst','paper_id','course_id','starttime','endtime','cqnid','wqnid','uqnid','status','entry_on','entry_by','examip','continueexam','pa','marksobt','switched'];

    public function user()
    {
        return $this->belongsTo('App\Models\User','stdid');
    }

    public function paper()
    {
        return $this->belongsTo('App\Models\SubjectMaster','paper_id');
    }

    public function answers()
    {
      return $this->hasMany('App\Models\CandQuestion','paper_id');
    }

    public function examSession()
    {
      return $this->hasMany('App\Models\ExamSession','exam_id');
    }

    public function program()
    {
      return $this->belongsTo('App\Models\ProgramMaster','program_id');
    }
}
