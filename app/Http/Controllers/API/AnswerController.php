<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Student\Student;
use App\Admin\Admin;
use Illuminate\Http\Request;


class AnswerController extends Controller
{
    public function index(Student $s,Request $request)
    {
      if(Auth::user())
      {
        return $s->getAnswers($request->exam_id);
      }
      else
      {
        return response()->json([
          "status"          =>  "failure",
          "message"         =>  "Unauthorized User...",
        ], 401);
      }
    }

    public function show(Student $s,$id)
    {
      if(Auth::user())
      {
        return $s->getAnswer($id);
      }
      else
      {
        return response()->json([
          "status"          =>  "failure",
          "message"         =>  "Unauthorized User...",
        ], 401);
      }
    }

    public function upload(Student $s,$id,Request $request)
    {
      if(Auth::user())
      {
        return $s->uploadAnswerImage($id,$request);
      }
      else
      {
        return response()->json([
          "status"          =>  "failure",
          "message"         =>  "Unauthorized User...",
        ], 401);
      }
    }

    public function update(Student $s,Request $request,$id)
    {
      if(Auth::user())
      {
        if($request->type == 'saveanswer')
        {
          return $s->updateAnswer($request,$id);
        }
        else if($request->type == 'savesubjectiveanswer')
        {
          return $s->updateSubjectiveAnswer($request,$id);
        }
        else if($request->type == 'savereview')
        {
          return $s->updateReview($request,$id);
        }
        else if($request->type == 'removeAnswerImage')
        {
          return $s->removeAnswerImage($request,$id);
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

    public function updateByExamId($qnidSr,$examId,Admin $a)
    {
      if(Auth::user())
      {
        return $a->clearResponse($qnidSr,$examId);
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
