<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProctorSnapsReset extends Model
{
    use HasFactory;
    protected $table = 'proctor_snaps_reset';
    protected $fillable = [
        'id',
        'examid',
        'path'
    ];
}
