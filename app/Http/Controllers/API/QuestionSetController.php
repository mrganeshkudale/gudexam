<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Student\Student;
use App\Admin\Admin;
use Illuminate\Http\Request;

class QuestionSetController extends Controller
{
  public function show(Admin $a,Request $request)
  {
    if(Auth::user())
    {
        if($request->type == 'preview')
        {
            $paper_id = $request->paper_id;
            return $a->getPreviewQuestions($paper_id);
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

  public function specificationCompare(Request $request,Admin $a)
  {
    if(Auth::user())
    {
      if($request->type=='match')
      {
        return $a->specificationMatch();
      }
      else
      {
        return $a->specificationCompare();
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
}
