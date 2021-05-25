<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopicMaster extends Model
{
    use HasFactory;
    protected $table = 'topic_master';
    protected $fillable = ['id','paper_id','topic','subtopic','questions','marks','created_at','updated_at','questType','questMode'];

    public function getSubject()
    {
        return $this->belongsTo('App\Models\SubjectMaster','paper_id');
    }
}
