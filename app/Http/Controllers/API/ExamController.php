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
  public function index(Student $s,Admin $a,Request $request)
  {
    if(Auth::user())
    {
      if(Auth::user()->role == 'STUDENT')
      {
        return $s->getExams();
      }
      else if(Auth::user()->role == 'ADMIN')
      {
        return $a->getAllExams();
      }
      else if(Auth::user()->role == 'EADMIN' && $request->type == '')
      {
        return $a->getFilteredExams($request->instId);
      }
      else if(Auth::user()->role == 'EADMIN' && $request->type == 'byEnrollno')
      {
        return $a->getExamsByEnrollno($request->enrollno);
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

  public function update(Student $s,Admin $a,Request $request,$id)
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
      else if($request->status==='preview')
      {
        return $a->previewExam(true);
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

  public function upload(Request $request, Admin $a)
  {
    if(Auth::user())
    {
      return $a->uploadStudSubjectMapping($request);
    }
    else
    {
      return response()->json([
        "status"          =>  "failure",
        "message"         =>  "Unauthorized User...",
      ], 400);
    }
  }

  public function del($id, Admin $a)
  {
    if(Auth::user())
    {
      return $a->delExam($id);
    }
    else
    {
      return response()->json([
        "status"          =>  "failure",
        "message"         =>  "Unauthorized User...",
      ], 400);
    }
  }

  public function examReportCount(Request $request,Admin $a)
  {
    if(Auth::user() && Auth::user()->role != 'STUDENT')
    {
      if($request->type == 'instwise')
      {
        return $a->examReportCount($request->instId);
      }
      else
      {
        if(Auth::user()->role == 'EADMIN')
        {
          return $a->examReportCount(Auth::user()->username);
        }
      }
    }
    else
    {
      return response()->json([
        "status"          =>  "failure",
        "message"         =>  "Unauthorized User...",
      ], 400);
    }
  }

  public function examByPaperIdAndType(Request $request, Admin $a)
  {    
    if(Auth::user())
    {
      return $a->examByPaperIdAndType($request);
    }
    else
    {
      return response()->json([
        "status"          =>  "failure",
        "message"         =>  "Unauthorized User...",
      ], 400);
    }
  }

  public function examLog($enrollno,$paperId, Admin $a)
  {
    if(Auth::user())
    {
      return $a->getExamLog($enrollno,$paperId);
    }
    else
    {
      return response()->json([
        "status"          =>  "failure",
        "message"         =>  "Unauthorized User...",
      ], 400);
    }
  }

}
