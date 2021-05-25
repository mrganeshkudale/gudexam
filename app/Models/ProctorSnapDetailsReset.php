<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProctorSnapDetailsReset extends Model
{
    use HasFactory;
    protected $table = 'proctor_snap_details_reset';
    protected $fillable = [
        'id',
        'examid',
        'snapid',
        'agerange',
        'beared',
        'eyeglasses',
        'eyesopen',
        'gender',
        'mustache',
        'smile',
        'sunglasses',
        'created_at',
        'updated_at'

    ];
}
