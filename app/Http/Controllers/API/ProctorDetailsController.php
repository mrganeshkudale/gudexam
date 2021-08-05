<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Admin\Admin;
use App\Student\Student;
use Illuminate\Http\Request;

class ProctorDetailsController extends Controller
{
    public function store($id, Request $request, Student $s)
    {
        return $s->storeSnapshotDetails($id, $request->snapid, $request->agerange, $request->beard, $request->eyeglasses, $request->eyesopen, $request->gender, $request->mustache, $request->smile, $request->sunglasses);
    }
}
