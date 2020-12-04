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


class AuthController extends Controller
{
  public function login(Request $request, CustomLogin $clogin)
  {
    return $clogin->customLoginAuthentication();
  }

  public function getlogin(Request $request)
  {
    if(!Auth::user())
    {
      return response()->json([
        "status"  =>  "failure",
        "message" =>  "Unauthorized User. Logging you out"
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
        "message"=>"Unauthorized User. Logging you out"
      ], 401);
    }
  }

  public function getOTP(Request $request,Registration $regi)
  {
    return $regi->sendOTP();
  }

  public function resendOTP(Request $request,Registration $regi)
  {
    return $regi->sendOTP();
  }

  public function verifyOTP(Request $request,Registration $regi)
  {
    return $regi->verifyOTP();
  }

  public function registerUser(Request $request,Registration $regi)
  {
    return $regi->registerUser();
  }
}
?>
