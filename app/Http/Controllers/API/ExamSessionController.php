<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Student\Student;
use Illuminate\Http\Request;

class ExamSessionController extends Controller
{
    public function update(Request $request,Student $s)
    {
      if(Auth::user())
      {
        return $s->updateExamSession($request->exam_id);
      }
      else
      {
        return response()->json([
          "status"          =>  "failure",
          "message"         =>  "Unauthorized User...",
        ], 401);
      }
    }

    public function show(Request $request,Student $s)
    {
      if(Auth::user())
      {
        return $s->getExamSession($request->exam_id);
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
