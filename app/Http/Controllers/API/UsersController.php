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

    public function store(Request $request,Admin $a)
    {
        if(Auth::user())
        {
            if($request->type=='student')
            {
                return $a->storeStudentUsers($request);
            }
            else
            {
                return $a->storeUsers($request);
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

    public function upload(Request $request,Admin $a)
    {
        if(Auth::user())
        {
            if($request->type == 'student')
            {
                return $a->uploadStudents($request);
            }
            else
            {
                return $a->uploadUsers($request);
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

    public function del(Request $request,Admin $a)
    {
        if(Auth::user())
        {
            return $a->deleteUser($request->id);
        }
        else
        {
            return response()->json([
                "status","failure",
                "message"=>"Unauthorized User."
              ], 401);
        }
    }

    public function update($id,Request $request, Admin $a)
    {
        if(Auth::user())
        {
            return $a->updateUser($id,$request);
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
