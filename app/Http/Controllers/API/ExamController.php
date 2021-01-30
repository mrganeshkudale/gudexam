<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Student\Student;
use App\Admin\Admin;
use Illuminate\Http\Request;

class ExamController extends Controller
{
  public function index(Student $s)
  {
    if(Auth::user())
    {
      return $s->getExams();
    }
    else
    {
      return response()->json([
        "status"          =>  "failure",
        "message"         =>  "Unauthorized User...",
      ], 200);
    }
  }

  public function show(Request $request,Admin $a)
  {
    if(Auth::user())
    {
        if($request->type === 'byprogramid')
        {
          return $a->getExams($request->id);
        }
    }
    else
    {
      return response()->json([
        "status"          =>  "failure",
        "message"         =>  "Unauthorized User...",
      ], 200);
    }
  }

  public function update(Student $s,Request $request,$id)
  {
    if(Auth::user())
    {
      if($request->status === 'start')
      {
        return $s->startExam($id);
      }
      else if($request->status === 'end')
      {
        return $s->endExam($id);
      }
      else if($request->status === 'windowswitch')
      {
        return $s->windowSwitchExam($id);
      }
      else
      {
        return response()->json([
          "status"          =>  "failure",
          "message"         =>  "Incorrect Input Parameters...",
        ], 401);
      }
    }
    else
    {
      return response()->json([
        "status"          =>  "failure",
        "message"         =>  "Unauthorized User...",
      ], 401);
    }
  }

  public function startexam(Student $s,Request $request)
  {
    if(Auth::user())
    {
      return $s->startExam($request->exam_id);
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
