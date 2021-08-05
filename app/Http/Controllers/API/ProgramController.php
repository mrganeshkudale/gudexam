<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Admin\Admin;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index(Request $request, Admin $a)
    {
        if ($request->type == '') {
            return $a->getPrograms();
        } else if ($request->type == 'all') {
            return $a->getAllPrograms();
        } else if ($request->type == 'instUid') {
            return $a->getByInstPrograms($request->instUid);
        } else if ($request->type == 'instId') {
            return $a->getByInstIdPrograms($request->instId);
        }
    }

    public function show(Request $request, Admin $a)
    {
        return $a->getUserPrograms($request->username);
    }

    public function store(Request $request, Admin $a)
    {
        return $a->storeProgram($request);
    }

    public function upload(Request $request, Admin $a)
    {
        return $a->uploadProgram($request);
    }

    public function uploadProgInst(Request $request, Admin $a)
    {
        return $a->uploadProgInst($request);
    }

    public function del(Request $request, Admin $a)
    {
        return $a->deleteProgram($request);
    }

    public function indexProgInst(Request $request, Admin $a)
    {
        if ($request->type == 'all') {
            return $a->getAllProgInsts();
        } else if ($request->type == 'instwise') {
            return $a->getAllProgInstwise($request->instId);
        }
    }

    public function deleteProgInst($id, Admin $a)
    {
        return $a->delProgInst($id);
    }

    public function update($id, Request $request, Admin $a)
    {
        return $a->updateProgram($id, $request);
    }
}
