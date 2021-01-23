<?php
namespace App\Http\Controllers\API;

use App\Models\User;
use App\CustomLogin\CustomLogin;
use App\Registration\Registration;
use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
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
    return $regi->verifyOTP($request->OTP_id);
  }

  public function register(Request $request,Registration $regi)
  {
    return $regi->registerUser();
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
      ], 200);
    }
  }
}
?>
