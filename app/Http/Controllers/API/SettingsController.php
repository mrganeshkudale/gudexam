<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LoginSettings;

class SettingsController extends Controller
{
  public function index(Request $request)
  {
    if ($request->type === "login") {
      $result = LoginSettings::find(1);
      if ($result) {
        return json_encode([
          'status' => 'success',
          'flag'  =>  $result->action
        ], 200);
      } else {
        return json_encode([
          'status' => 'failure'
        ], 400);
      }
    } else {
      return json_encode([
        'status' => 'failure'
      ], 400);
    }
  }
}
