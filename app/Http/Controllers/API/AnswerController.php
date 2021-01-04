<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Student\Student;
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

    public function update(Student $s,Request $request,$id)
    {
      if(Auth::user())
      {
        if($request->type == 'saveanswer')
        {
          return $s->updateAnswer($request,$id);
        }
        else if($request->type == 'savereview')
        {
          return $s->updateReview($request,$id);
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
}
