<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Student\Student;
use App\Admin\Admin;
use App\Models\HeaderFooterText;
use Illuminate\Http\Request;

class ConfigurationsController extends Controller
{
    public function show(Request $request)
    {
            if($request->type === 'footerconfig')
            {
                $result = HeaderFooterText::find(1)->first();
                if($result)
                {
                return response()->json([
                    "status"        => "success",
                    "footer"        => $result->footer,
                ], 200);
                }
                else
                {
                return response()->json([
                    "status"        => "success",
                    "footer"        => "GudExams",
                ], 200);
                }
            }
            if($request->type === 'headerconfig')
            {
                $result = HeaderFooterText::find(1)->first();
                if($result)
                {
                    $url = Config::get('constants.PROJURL');
                    return response()->json([
                        "status"        => "success",
                        "header"        => $result->header,
                        "imgpath"       => stripslashes($url.'/'.$result->logo)
                    ], 200);
                }
                else
                {
                return response()->json([
                    "status"        => "success",
                    "header"        => "GudExams",
                ], 200);
                }
            }
    }

    public function update(Request $request,Admin $a)
    {
        if(Auth::user())
        {
            if($request->type === 'footerconfig')
            {
                return $a->updateFooter($request->orgName);
            }
        }
        else
        {
            return response()->json([
                "status"          =>  "failure",
                "message"         =>  "Unauthorized User...",
              ], 401);
        }
    }

    public function store(Request $request,Admin $a)
    {
        if(Auth::user())
        {
            if($request->type === 'headerconfig')
            {
                return $a->updateHeader($request);
            }
        }
    }
}
