<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramMaster extends Model
{
    use HasFactory;
    protected $table = 'program_master';
    protected $fillable = ['id','program_code','program_name','created_at','updated_at','inst_uid'];

    public function institute()
    {
        return $this->belongsTo('App\Models\User','inst_uid');
    }

    public function subjects()
    {
        return $this->hasMany('App\Models\SubjectMaster','program_id');
    }

    public function exams()
    {
        return $this->hasMany('App\Models\CandTest','program_id');
    }
}
