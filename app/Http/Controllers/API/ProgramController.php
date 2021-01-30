<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Admin\Admin;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index(Admin $a)
    {
        if(Auth::user())
        {
            return $a->getPrograms();
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
            return $a->getUserPrograms($request->username);
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
