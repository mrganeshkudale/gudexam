<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Admin\Admin;
use App\Admin\Admin1;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function index(Request $request,Admin $a,Admin1 $a1)
    {
        if(Auth::user())
        {
            if($request->role!='')
            {
                
                if($request->instUid!='')
                {
                    return $a->getFilteredUsers($request->role,$request->instUid);
                }
                else if($request->instId!='')
                {
                    return $a->getFilteredUsersByInstCode($request->role,$request->instId);
                }
                else
                {
                    return $a->getAllUsers($request->role);
                }
            }
            else
            {
                if($request->username != '')
                {
                    if(Auth::user()->role == 'EADMIN')
                    {
                        return $a1->getUser($request->username,Auth::user()->username);
                    }
                }
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

    public function store(Request $request,Admin $a,Admin1 $a1)
    {
        if(Auth::user())
        {
            if($request->type=='student')
            {
                return $a->storeStudentUsers($request);
            }
            else if($request->type=='checker')
            {
                return $a1->storeCheckerUsers($request);
            }
            else if($request->type=='proctor')
            {
                return $a1->storeProctorUsers($request);
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

    public function upload(Request $request,Admin $a,Admin1 $a1)
    {
        if(Auth::user())
        {
            if($request->type == 'student')
            {
                return $a->uploadStudents($request);
            }
            else if($request->type == 'checker')
            {
                return $a1->uploadCheckers($request);
            }
            else if($request->type == 'proctor')
            {
                return $a1->uploadProctors($request);
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

    public function del(Request $request,Admin $a,Admin1 $a1)
    {
        if(Auth::user())
        {
            if($request->type == 'proctor')
            {
                $a1->deleteProctorSubjects($request->id);
                $a1->deleteProctorAllocationByProctorId($request->id);
                return $a->deleteUser($request->id);
            }
            else if($request->type == 'checker')
            {
                $a1->deleteCheckerSubjects($request->id);
                $a1->deleteCheckerAllocationByCheckerId($request->id);
                return $a->deleteUser($request->id);
            }
            else if($request->type == 'student')
            {
                $a1->deleteStudentWarningMessages($request->id);
                $a1->deleteStudentQuestions($request->id);
                $a1->deleteStudentSubjectMapping($request->id);
                $a1->deleteStudentCheckerMapping($request->id);
                $a1->deleteStudentProctorMapping($request->id);
                return $a->deleteUser($request->id);
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

    public function update($id,Request $request, Admin $a,Admin1 $a1)
    {
        if(Auth::user())
        {
            if($request->type == 'checker')
            {
                return $a1->updateChecker($id,$request);
            }
            else if($request->type == 'proctor')
            {
                return $a1->updateProctor($id,$request);
            }
            else
            {
                return $a->updateUser($id,$request);
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
