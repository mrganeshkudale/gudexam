<?php
namespace App\Registration;
use App\Models\User;
use App\Models\UserRegister;
use App\Models\OTPVerify;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CollegeMaster;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Validator;

class Registration
{
    private $reg_type;
    private $name;
    private $inst_name;
    private $mobile;
    private $email;
    private $password;
    private $inst_id;
    private $username;
    private $otp;

	public function __construct(Request $request)
	{
    $this->username = $request->username;
    $this->reg_type = $request->reg_type;
    $this->name = $request->name;
    $this->inst_name = $request->inst_name;
    $this->mobile = $request->mobile;
    $this->email = $request->email;
    $this->password = $request->password;
    $this->otp = $request->otp;
  }

  public function getMaxInstId()
  {
      $max = User::max('inst_id');
      return ($max+1);
  }

  public function send_sms()
  {
      $apiKey = urlencode('WYb3gsH8qH0-aHvaDjhoWMpLGJGijgl34Iz9yXil6F');
    // Message details
    $numbers = array($this->mobile);
    $sender = urlencode('VTPLPN');
    $message = rawurlencode("Dear User,
Your Username is $this->username , password is $this->password and Institute code is $this->inst_id for GudExams.

Thank You.
Bynaric Systems Pvt. Ltd.");

    $numbers = implode(',', $numbers);

    // Prepare data for POST request
    $data = array('apikey' => $apiKey, 'numbers' => $numbers, "sender" => $sender, "message" => $message);

    // Send the POST request with cURL
    $ch = curl_init('https://api.textlocal.in/send/');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    //dd($response);
    curl_close($ch);
    $result=json_decode($response);

    if($result->status=='failure')
    {
      return '0';
    }
    else if($result->status=='success')
    {
              return '1';
    }
  }

  public function sendOTP()
  {
    if($this->mobile=='' || $this->mobile=='0')
    {
      return response()->json([
        'status'    => 'failure',
        'message'   => 'Mobile Number is Mandatory...',
      ],400);
    }

    $current_timestamp = Carbon::now()->timestamp;
    $current_time = Carbon::now();
    $otp=rand(100000,999999);
    $r = User::where('mobile',$this->mobile)->get();

    if(!count($r))
    {
        $result = OTPVerify::create([
            'mobile'      => $this->mobile,
            'otp'         => $otp,
            'created_at'  => $current_time,
            'updated_at'  => $current_time,
        ]);

        $apiKey = urlencode('WYb3gsH8qH0-aHvaDjhoWMpLGJGijgl34Iz9yXil6F');
        // Message details
        $numbers = $this->mobile;
        $sender = urlencode('VTPLPN');
    $message = rawurlencode("Dear User,

$otp is your Registration OTP for GudExams.

Thank You.
With Warm Regards,
Bynaric Systems Pvt. Ltd.");

        // Prepare data for POST request
        $data = array('apikey' => $apiKey, 'numbers' => $numbers, "sender" => $sender, "message" => $message);
        // Send the POST request with cURL
        $ch = curl_init('https://api.textlocal.in/send/');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        //dd($response);
        curl_close($ch);
        $result=json_decode($response);

        if($result->status=='failure')
        {
              return response()->json([
                'status'    => 'failure',
                'message'   => 'Problem Sending OTP',
              ],400);
        }
        else if($result->status=='success')
        {
              return response()->json([
                'status'    => 'success',
                'message'   => 'OTP Sent Successfully',
              ],200);
        }
    }
    else
    {
        return response()->json([
          'status'    => 'failure',
          'message'   => 'This Mobile Number is already registered. Can not ReVerify',
        ],400);
    }
  }

  public function verifyOTP()
  {
    if($this->mobile=='' || $this->mobile=='0')
    {
      return response()->json([
        'status'    => 'failure',
        'message'   => 'Mobile Number is Mandatory...',
      ],400);
    }

    if($this->otp=='' || $this->otp=='0')
    {
      return response()->json([
        'status'    => 'failure',
        'message'   => 'OTP is Mandatory...',
      ],400);
    }

    $result = DB::select("SELECT otp FROM `otp_verify` where mobile=$this->mobile order by created_at DESC limit 1");
    if($result)
    {
        if($result[0]->otp == $this->otp)
        {
            return response()->json([
              'status'    => 'success',
              'message'   => 'OTP Verified Successfully',
            ],200);
        }
        else
        {
            return response()->json([
              'status'    => 'failure',
              'message'   => 'Wrong OTP Entered',
            ],400);
        }
    }
    else
    {
        return response()->json([
          'status'    => 'failure',
          'message'   => 'Wrong OTP Entered',
        ],400);
    }
  }

  public function registerUser()
  {
    $request1 = [
    'reg_type'  => $this->reg_type,
    'name'      => $this->name,
    'inst_name' => $this->inst_name,
    'email'     => $this->email,
    'mobile'    => $this->mobile,
    'password'  => $this->password,
    ];

    $validator = Validator::make($request1, [
        'reg_type'  => 'required',
        'name'      => 'required',
        'inst_name' => 'required',
        'email'     => 'required|email',
        'mobile'    => 'required|digits:10',
        'password'  => 'required',
    ]);

    if ($validator->fails()) {
      return response()->json([
            'status' 		=> 'failure',
            'message'		=> $validator->errors()->first(),
          ],200);
    }


    $username = '';
    if($this->reg_type=='Student')
    {
        $users = User::where('username',$this->mobile)->first();
        if(!$users)
        {
            $current_timestamp    = Carbon::now()->timestamp;
            $current_time         = Carbon::now();
            $inst_id              = '1000';$password = $this->password;
            $this->inst_id        = $inst_id;
            if($this->username=='')
            {
              $this->username       = $this->mobile;
            }
            $user = User::create([
                'username'      => $this->username,
                'inst_id'       => $inst_id,
                'name'          => $this->name,
                'courses'       => 'GE',
                'semester'      => '1',
                'mobile'        => $this->mobile,
                'role'          => 'STUDENT',
                'password'      => Hash::make($password),
                'origpass'      => $this->password,
                'pa'            => 'P',
                'status'        => 'ON',
                'created_at'    => $current_time,
                'updated_at'    => $current_time,
            ]);


            if($user)
            {
              $this->send_sms();
              return response()->json([
                    'status' 		=> 'success',
                    'message'		=> 'Student Registration Successfull...',
                  ],200);
            }
            else
            {
              return response()->json([
                    'status' 		=> 'failure',
                    'message'		=> 'Problem Registering Student...',
                  ],400);
            }
        }
        else
        {
          return response()->json([
                'status' 		=> 'failure',
                'message'		=> 'User with this username already exists...',
              ],400);
        }
    }
    else
    {
        $current_timestamp      = Carbon::now()->timestamp;
        $current_time           = Carbon::now();
        $password               = $this->password;
        $inst_id                = $this->getMaxInstId();
        $this->inst_id          = $inst_id;
        $role                   = '';
        if($this->reg_type  ==  'GlobalController')
        {
            if(trim($this->username)  ==  '')
            {
                $username = 'G'.$inst_id;
            }
            else
            {
                $username = $this->username;
            }
            $role = 'GADMIN';
        }
        else if($this->reg_type ==  'ClusterController')
        {
            if(trim($this->username)  ==  '')
            {
                $username = 'C'.$inst_id;
            }
            else
            {
                $username = $this->username;
            }
            $role = 'CADMIN';
        }
        else
        {
            $username = 'P'.$inst_id;
            $role = 'EADMIN';
        }

        $this->username = $username;
        //-------------------------Do entry in Users Table--------------------
        try
        {
            $user = User::create([
                'username'      => $username,
                'inst_id'       => $this->inst_id,
                'name'          => $this->name,
                'mobile'        => $this->mobile,
                'email'         => $this->email,
                'role'          => $role,
                'password'      => Hash::make($password),
                'origpass'      => $this->password,
                'status'        => 'ON',
                'created_at'    => $current_time,
                'updated_at'    => $current_time,
            ]);
        }
        catch(\Exception $e)
        {
          return response()->json([
                'status' 		=> 'failure',
                'message'		=> 'Problem Registering User...',
              ],400);
        }
        //------------------Do Entry in Users Register Table---------------------
        try
        {
            $user = UserRegister::create([
                'regi_type'         => $this->reg_type,
                'username'          => $username,
                'eadmin_name'       => $this->name,
                'inst_id'           => $this->inst_id,
                'college_name'      => $this->inst_name,
                'email'             => $this->email,
                'mobile'            => $this->mobile,
                'password'          => Hash::make($password),
                'status'            => 'Verified',
                'verify_on'         => $current_time,
                'wallet_balance'    => '0',
                'docpath'           => '',
                'created_at'        => $current_time,
                'updated_at'        => $current_time,
            ]);
        }
        catch (\Exception $e)
        {
            $res=User::where('username',$username)->where('inst_id',$this->inst_id)
            ->delete();
            return response()->json([
                  'status' 		=> 'failure',
                  'message'		=> 'Problem Registering User...',
                ],400);
        }
        //--------------------Do Entry in College Master Table----------------------
        try
        {
            $user = CollegeMaster::create([
                'inst_name'       => $this->inst_name,
                'username'        => $username,
                'inst_id'         => $this->inst_id,
                'created_at'      => $current_time,
                'updated_at'      => $current_time,
            ]);
        }
        catch (\Exception $e)
        {
            $res=User::where('username',$username)->where('inst_id',$this->inst_id)
            ->delete();
            return response()->json([
                  'status' 		=> 'failure',
                  'message'		=> 'Problem Registering User...',
                ],400);
        }
        //---------------------------------------------------------------------------
        if($user)
        {
          $this->send_sms();
          return response()->json([
                'status' 		=> 'success',
                'message'		=> 'User Registration Successfull...',
              ],200);
        }
        else
        {
          return response()->json([
                'status' 		=> 'failure',
                'message'		=> 'Problem Registering User...',
              ],400);
        }
    }

  }
}
