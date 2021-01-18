<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Admin\Admin;
use Illuminate\Http\Request;

class SessionsController extends Controller
{
    public function update(Request $request,Admin $a)
    {
        if(Auth::user())
        {
            if($request->type === 'clearsession')
            {
                return $a->clearSession($request->enrollNo);
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
