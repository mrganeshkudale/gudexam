<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Student\Student;
use App\Admin\Admin;
use Illuminate\Http\Request;

class CandidateTestController extends Controller
{
    public function index()
    {

    }

    public function show(Student $s, $id)
    {
      if(Auth::user())
      {
        return $s->getCandidateTestData($id);
      }
      else
      {
        return response()->json([
          "status"          =>  "failure",
          "message"         =>  "Unauthorized User...",
        ], 401);
      }
    }

    public function store(Student $s,Request $request)
    {
      if(Auth::user())
      {
        return $s->storeCandidateTest($request->paper_id);
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
        return $s->updateCandidateTest($request,$id);
      }
      else
      {
        return response()->json([
          "status"          =>  "failure",
          "message"         =>  "Unauthorized User...",
        ], 401);
      }
    }

    public function delete(Admin $a, $stdid,$paper_id,$inst)
    {
      if(Auth::user())
      {
        return $a->deleteCandidateTest($stdid,$paper_id,$inst);
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
