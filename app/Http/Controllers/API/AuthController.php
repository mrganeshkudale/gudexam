<?php
namespace App\Http\Controllers\API;

use App\Admin\Admin1;
use App\CustomLogin\CustomLogin;
use App\Registration\Registration;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;


class AuthController extends Controller
{
  public function index()
  {
    if(Auth::user())
    {
      return response()->json([
        "status"    =>  "Success",
        "data"      =>  Auth::user() 
      ], 200);
    }
    else
    {
      return response()->json([
        "status"    =>  "failure",
        "message"   =>  "Unauthorized User."
      ], 401);
    }
  }

  public function login(Request $request, CustomLogin $clogin)
  {
    $myRecaptcha    = $request->myRecaptcha;
    $client         = new Client;
    $response       = $client->post('https://www.google.com/recaptcha/api/siteverify',
      [
          'form_params' =>
              [
                  'secret'    => env('CAPTCHA_SECRET_KEY'),
                  'response'  => $myRecaptcha
              ]
      ]
    );

    $body = json_decode((string)$response->getBody());
    if($body->success == true)
    {
      return $clogin->customLoginAuthentication();
    }
    else
    {
      return json_encode([
        'status' => 'failure',
        'message'  => 'Please use Recaptcha for logging in...',
      ],200);
    }
  }

  public function appLogin(Request $request, CustomLogin $clogin)
  {
      return $clogin->customLoginAuthentication($request);
  }

  public function getlogin(Request $request)
  {
    if(!Auth::user())
    {
      return response()->json([
        "status"  =>  "failure",
        "message" =>  "Unauthorized User."
      ], 401);
    }
  }

  public function logout(Request $request, CustomLogin $clogin)
  {
    if(Auth::user())
    {
      $clogin->customLogout();
      $request->user()->token()->revoke();
      return response()->json([
        "status" => "Success",
        "message"=>"User logged out successfully..."
      ], 200);
    }
    else
    {
      return response()->json([
        "status","failure",
        "message"=>"Unauthorized User."
      ], 401);
    }
  }

  public function sendOTP(Request $request,Registration $regi)
  {
    return $regi->sendOTP();
  }

  public function resendOTP(Request $request,Registration $regi)
  {
    return $regi->sendOTP();
  }

  public function verifyOTP(Request $request,Registration $regi)
  {
    return $regi->verifyOTP($request->otp,$request->mobile);
  }

  public function register(Request $request,Registration $regi)
  {
    $myRecaptcha    = $request->myRecaptcha;
    $client         = new Client;
    $response       = $client->post('https://www.google.com/recaptcha/api/siteverify',
      [
          'form_params' =>
              [
                  'secret'    => env('CAPTCHA_SECRET_KEY'),
                  'response'  => $myRecaptcha
              ]
      ]
    );

    $body = json_decode((string)$response->getBody());
    if($body->success == true)
    {
      return $regi->registerUser();
    }
    else
    {
      return json_encode([
        'status' => 'failure',
        'message'  => 'Please use Recaptcha for logging in...',
      ],400);
    }
  }

  public function isLoggedIn()
  {
    if(Auth::user())
    {
      return response()->json([
        "status" => "success",
        "message"=>"Authenticated User"
      ], 200);
    }
    else
    {
      return response()->json([
        "status" => "failure",
        "message"=>"Unauthorized User"
      ], 400);
    }
  }

  public function loginLink($stdid,Request $request,Admin1 $a1)
  {
    if(Auth::user())
    {
      return $a1->loginLink($stdid,$request);
    }
    else
    {
      return response()->json([
        "status" => "failure",
        "message"=>"Unauthorized User"
      ], 401);
    }
  }
}
?>
