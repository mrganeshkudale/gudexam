<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Admin\Admin;

class SubjectsController extends Controller
{
  public function show(Request $request,Admin $a)
  {
    if(Auth::user())
    {
        return $a->getSubjects($request->program_id);
    }
    else
    {
        return response()->json([
            "status"          =>  "failure",
            "message"         =>  "Unauthorized User...",
        ], 200);
    }
  }

  public function store(Request $request,Admin $a)
  {
    if(Auth::user())
    {
        return $a->storeSubjects($request);
    }
    else
    {
        return response()->json([
            "status"          =>  "failure",
            "message"         =>  "Unauthorized User...",
        ], 200);
    }
  }

  public function upload(Request $request,Admin $a)
  {
    if(Auth::user())
    {
        return $a->uploadSubjects($request);
    }
    else
    {
        return response()->json([
            "status"          =>  "failure",
            "message"         =>  "Unauthorized User...",
        ], 200);
    }
  }

  public function index(Request $request,Admin $a)
  {
    if(Auth::user())
    {
      if($request->type == 'all')
      {
        return $a->getAllSubjects();
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

  public function del($id,Admin $a)
  {
    if(Auth::user())
    {
      return $a->delSubject($id);
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
