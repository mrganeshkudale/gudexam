<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandTestReset extends Model
{
    use HasFactory;
    protected $table = 'cand_test_reset';
    protected $fillable = ['id','stdid','inst','paper_id','program_id','course_id','starttime','endtime','cqnid','wqnid','uqnid','status','entry_on','entry_by','examip','continueexam','pa','marksobt','switched'];
}
