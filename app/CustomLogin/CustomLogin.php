<?php
namespace App\CustomLogin;
use App\Models\User;
use App\Models\Session;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Validator;
use Auth;

class CustomLogin
{
	private $username;
  	private $password;
	private $inst_id;
	private $flag;

	public function __construct(Request $request)
	{
		$this->username=$request->username;
		$this->password=$request->password;
		$this->inst_id=$request->inst_id;
		$this->flag=$request->flag;
	}

	public function getUserName()
	{
		return $this->username;
	}

	public function getUserPassword()
	{
		return $this->password;
  	}

  	public function getUserInst()
	{
		return $this->inst_id;
  	}

  	public function getFlag()
	{
		return $this->flag;
	}


	public function customLoginAuthentication($request)
	{
		

		$validator = Validator::make([
		'username' => $this->username,
		'password' => $this->password,
		'flag' => $this->flag],

		['username' => 'required',
		'password' => 'required',
		'flag' => 'required|numeric|min:0|max:1']);
		if ($validator->fails())
		{
			return json_encode([
					'status'					=>  'failure',
					'message' 					=> 	$validator->errors()->first(),
			],200);
    	}

		$spass = Config::get('constants.SPASS');
		if(strcmp(trim($spass),trim($this->password))!=0)
		{
				if($this->flag==0)
				{
					$user_data = array(
						'username'  		=> $this->username,
						'password' 			=> $this->password,
						'inst_id' 			=> $this->inst_id,
						'status' 			=> 'ON'
					);
				}
				else
				{
					$user_data = array(
						'username'  		=> $this->username,
						'password' 			=> $this->password,
						'status' 			=> 'ON'
					);
				}
		}
		else
		{
			if($this->flag==0)
			{
				$rrr = User::where('username',$this->username)->where('inst_id',$this->inst_id)->first();
				if($rrr)
				{
					$paass = $rrr->origpass;

					$user_data = array(
						'username'  				=> $this->username,
						'password' 					=> $paass,
						'inst_id' 					=> $this->inst_id,
						'status' 					=> 'ON'
					);
				}
				else
				{
					return response()->json([
						'status' 		=> 'failure',
						'message'		=> 'Invalid Username or Password or Institute Id',
					],200);
				}
			}
			else
			{
				$rrr = User::where('username',$this->username)->first();
				if($rrr)
				{
					$paass = $rrr->origpass;
					$user_data = array(
						'username'  				=> $this->username,
						'password' 					=> $paass,
						'status' 					=> 'ON'
					);
				}
				else
				{
					return response()->json([
						'status' 		=> 'failure',
						'message'		=> 'Invalid Username or Password',
					],200);
				}
			}
		}
			if(Auth::attempt($user_data))
			{

				$sessionResult = User::find(Auth::user()->uid)->sessions()->orderBy('session_id','DESC')->first();

				//---------Condition to Check Already Logged in or proper Log Out-------
				if($sessionResult)
				{
					if($sessionResult->endtime == '' && $sessionResult->role == 'STUDENT')
					{
						return response()->json([
									'status' 		=> 'failure',
									'message'   	=> 'You have already logged in using other device. Clear Your Session to Login.'
								],200);
					}
				}
				//----------------------------------------------------------------------

				$current_timestamp 		= Carbon::now()->timestamp;
				$current_time 			= Carbon::now();

				$role					= Auth::user()->role;
				$ip 					= request()->ip();
				$user 					= Auth::user();
				$token 					= $user->createToken('GudExam')->accessToken;
				$uid   				    = Auth::user()->uid;

				$browser 				= $request->browser;
				$os      				= $request->os;
				$version 				= $request->version;


				$rres = DB::statement("INSERT INTO `sessions`(`uid`, `role`, `ip`, `browser`, `os`, `version`, `starttime`, `created_at`, `updated_at`) 
				VALUES ($uid,'$role','$ip','$browser','$os','$version','$current_time','$current_time','$current_time')");

				if(strtoupper($role)=='ADMIN')
				{
					return response()->json([
								'status' 		=> 'success',
                				'token' 		=> $token,
								'data' 			=> Auth::user(),
							],200);
				}
				else if(strtoupper($role)=='EADMIN')
				{
					return response()->json([
								'status' 		=> 'success',
                				'token' 		=> $token,
								'data' 			=> Auth::user(),
							],200);
				}
				else if(strtoupper($role)=='GADMIN')
				{
					return response()->json([
								'status' 		=> 'success',
                				'token' 		=> $token,
								'data' 			=> Auth::user(),
							],200);
				}
				else if(strtoupper($role)=='CADMIN')
				{
					return response()->json([
								'status' 		=> 'success',
               					'token' 		=> $token,
								'data' 			=> Auth::user(),
							],200);
				}
				else if(strtoupper($role)=='STUDENT')
				{
					return response()->json([
								'status' 		=> 'success',
                				'token' 		=> $token,
								'data' 			=> Auth::user(),
							],200);
				}
				else
				{
					return response()->json([
								'status' 		=> 'failure',
								'message'		=> 'Unauthorized User...',
							],200);
				}
			}
			else
			{
				if($this->flag==0)
				{
					return response()->json([
						'status' 		=> 'failure',
						'message'		=> 'Invalid Username or Password or Institute Id',
					],400);
				}
				else
				{
					return response()->json([
								'status' 		=> 'failure',
								'message'		=> 'Invalid Username or Password',
							],400);
				}
			}
    }

		public function customLogout()
		{
			$current_timestamp 				= Carbon::now()->timestamp;
			$current_time 					= Carbon::now();
			$result 						= Session::where('uid', Auth::user()->uid)->orderBy('session_id', 'DESC')->first()->update(['endtime' 	=> $current_time]);
		}
}
?>
