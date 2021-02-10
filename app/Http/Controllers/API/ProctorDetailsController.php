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
    public function store(Student $s,Request $request)
    {
        if(Auth::user())
        { 
            return $s->storeSnapshotDetails($request->examid,$request->snapid,$request->agerange,$request->beard,$request->eyeglasses,$request->eyesopen,$request->gender,$request->mustache,$request->smile,$request->sunglasses);
        }
        else
        {
        return response()->json([
            "status"          =>  "failure",
            "message"         =>  "Unauthorized User...",
        ], 401);
        }
    }
}
