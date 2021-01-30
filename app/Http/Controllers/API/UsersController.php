<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Admin\Admin;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function index(Request $request,Admin $a)
    {
        if(Auth::user())
        {
            if($request->role!='')
            {
                return $a->getAllUsers($request->role);
            }
        }
        else
        {
            return response()->json([
                "status","failure",
                "message"=>"Unauthorized User."
              ], 401);
        }
    }

    public function show(Request $request,Admin $a)
    {
        if(Auth::user())
        {
            if($request->id!=='' && $request->instId!=='' && $request->flag!=='')
            {
                return $a->getUserDetails($request->id,$request->instId,$request->flag);
            }
        }
        else
        {
            return response()->json([
                "status","failure",
                "message"=>"Unauthorized User."
              ], 401);
        }
    }
}
