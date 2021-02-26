<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Admin\Admin;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index(Request $request,Admin $a)
    {
        if(Auth::user())
        {
            if($request->type=='')
            {
                return $a->getPrograms();
            }
            else if($request->type=='all')
            {
                return $a->getAllPrograms();
            }
            else if($request->type=='instUid')
            {
                return $a->getByInstPrograms($request->instUid);
            }
            else if($request->type=='instId')
            {
                return $a->getByInstIdPrograms($request->instId);
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

    public function store(Request $request,Admin $a)
    {
        if(Auth::user())
        {
            return $a->storeProgram($request);
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
            return $a->uploadProgram($request);
        }
        else
        {
            return response()->json([
                "status"          =>  "failure",
                "message"         =>  "Unauthorized User...",
            ], 200);
        }
    }

    public function del(Request $request,Admin $a)
    {
        if(Auth::user())
        {
            return $a->deleteProgram($request);
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
