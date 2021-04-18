<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandQuestionsCopy extends Model
{
    use HasFactory;protected $table = 'cand_questions_copy';

    protected $fillable = ['id','stdid','inst','paper_id','program_id','qnid','qtopic','qtype','answered','stdanswer','qnid_sr','entry_on','answer_by','answer_on','cans','marks','ip','obtmarks','exam_id'];

}
