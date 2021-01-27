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
}
