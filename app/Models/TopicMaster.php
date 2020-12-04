<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopicMaster extends Model
{
    use HasFactory;
    protected $table = 'topic_master';
    protected $fillable = ['id','paper_code','topic','subtopic','questions','created_at','updated_at'];
}
