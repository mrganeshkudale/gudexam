<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Admin\Admin;
use Illuminate\Http\Request;

class SessionsController extends Controller
{
    public function update(Request $request, Admin $a)
    {
        if ($request->type === 'clearsession') {
            return $a->clearSession($request->uid);
        } else if ($request->type === 'clearsessionMultiple') {
            return $a->clearSessionMulitiple($request->users, $request->instUid);
        }
    }
}
