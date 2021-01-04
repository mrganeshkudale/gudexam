<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;

    protected $table = 'sessions';
    protected $fillable = ['uid','role','ip','starttime','endtime','created_at','updated_at'];

    protected $primaryKey = 'session_id';

    public function user()
    {
      return $this->belongsTo('App\Models\User','uid');
    }

}
