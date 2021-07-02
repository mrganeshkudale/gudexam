<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProctorSnaps extends Model
{
    use HasFactory;
    protected $table = 'proctor_snaps';
    protected $fillable = [
        'id',
        'examid',
        'path'
    ];
}
