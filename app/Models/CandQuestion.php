<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandQuestion extends Model
{
    use HasFactory;protected $table = 'cand_questions';
    protected $fillable = ['id','stdid','inst','paper_id','program_id','qnid','qtopic','qtype','answered','stdanswer','qnid_sr','entry_on','answer_by','answer_on','cans','marks','ip','obtmarks'];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo('App\Models\User','stdid');
    }

    public function question()
    {
        return $this->hasOne('App\Models\QuestionSet','qnid');
    }

    public function exam()
    {
        return $this->belongsTo('App\Models\CandTest','paper_id');
    }
}
