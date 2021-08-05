<?php
namespace App\CustomLogin;
use App\Models\User;
use App\Models\Session;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Validator;
use Illuminate\Support\Facades\Auth;

class CustomLogin
{
	/*private $username;
  	private $password;
	private $inst_id;
	private $flag;*/

	/*public function __construct(Request $request)
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
	}*/


	public function customLoginAuthentication($request)
	{
		$rrr = null;
		$validator = Validator::make([
		'username' => $request->username,
		'password' => $request->password,
		'flag' => $request->flag],

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
		$multylogin = Config::get('constants.MULTYLOGIN');
	
		if(strcmp(trim($spass),trim($request->password))!=0)
		{
				if($request->flag==0)
				{
					$rrr = User::where('username',$request->username)->where('inst_id',$request->inst_id)->first();
					$user_data = array(
						'username'  		=> $request->username,
						'password' 			=> $request->password,
						'inst_id' 			=> $request->inst_id,
						'status' 			=> 'ON'
					);
				}
				else
				{
					$rrr = User::where('username',$request->username)->first();
					$user_data = array(
						'username'  		=> $request->username,
						'password' 			=> $request->password,
						'status' 			=> 'ON'
					);
				}
		}
		else
		{
			if($request->flag==0)
			{
				$rrr = User::where('username',$request->username)->where('inst_id',$request->inst_id)->first();
				if($rrr)
				{
					$paass = $rrr->origpass;
					$user_data = array(
						'username'  				=> $request->username,
						'password' 					=> $paass,
						'inst_id' 					=> $request->inst_id,
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
				$rrr = User::where('username',$request->username)->first();
				if($rrr)
				{
					$paass = $rrr->origpass;
					$user_data = array(
						'username'  				=> $request->username,
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
				$AuthUser = Auth::user();
				if($multylogin == 'N' && $AuthUser->role =='STUDENT')
				{
					$sessionResult = Session::where('uid',$AuthUser->uid)->orderBy('session_id','DESC')->first();

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
				}
				//----------------------------------------------------------------------

				$ip = $this->getIp();

				$current_timestamp 		= Carbon::now()->timestamp;
				$current_time 			= Carbon::now();

				$role					= $AuthUser->role;
				$user 					= $AuthUser;
				$token 					= $user->createToken('GudExam')->accessToken;
				$uid   				    = $AuthUser->uid;

				$browser 				= $request->browser;
				$os      				= $request->os;
				$version 				= $request->version;
				$firebaseToken 			= $request->firebaseToken;
				
				$rrr->firebaseToken 	= $firebaseToken;
				$rrr->save();


				$rres = DB::statement("INSERT INTO `sessions`(`uid`, `role`, `ip`, `browser`, `os`, `version`, `starttime`, `created_at`, `updated_at`) 
				VALUES ($uid,'$role','$ip','$browser','$os','$version','$current_time','$current_time','$current_time')");

				if(strtoupper($role)=='ADMIN')
				{
					return response()->json([
								'status' 		=> 'success',
                				'token' 		=> $token,
								'data' 			=> $AuthUser,
							],200);
				}
				else if(strtoupper($role)=='EADMIN')
				{
					return response()->json([
								'status' 		=> 'success',
                				'token' 		=> $token,
								'data' 			=> $AuthUser,
							],200);
				}
				else if(strtoupper($role)=='GADMIN')
				{
					return response()->json([
								'status' 		=> 'success',
                				'token' 		=> $token,
								'data' 			=> $AuthUser,
							],200);
				}
				else if(strtoupper($role)=='CADMIN')
				{
					return response()->json([
								'status' 		=> 'success',
               					'token' 		=> $token,
								'data' 			=> $AuthUser,
							],200);
				}
				else if(strtoupper($role)=='STUDENT')
				{
					return response()->json([
								'status' 		=> 'success',
                				'token' 		=> $token,
								'data' 			=> $AuthUser,
							],200);
				}
				else if(strtoupper($role)=='CHECKER')
				{
					return response()->json([
								'status' 		=> 'success',
                				'token' 		=> $token,
								'data' 			=> $AuthUser,
							],200);
				}
				else if(strtoupper($role)=='PROCTOR')
				{
					return response()->json([
								'status' 		=> 'success',
                				'token' 		=> $token,
								'data' 			=> $AuthUser,
							],200);
				}
				else if(strtoupper($role)=='PAPERSETTER')
				{
					return response()->json([
								'status' 		=> 'success',
                				'token' 		=> $token,
								'data' 			=> $AuthUser,
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
				if($request->flag==0)
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

	public function getIp(){
		foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
			if (array_key_exists($key, $_SERVER) === true){
				foreach (explode(',', $_SERVER[$key]) as $ip){
					$ip = trim($ip); // just to be safe
					if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
						return $ip;
					}
				}
			}
		}
		return request()->ip(); // it will return server ip when no client ip found
	}

		public function customLogout()
		{
			$AuthUser = Auth::user();
			$current_timestamp 				= Carbon::now()->timestamp;
			$current_time 					= Carbon::now();
			$result 						= Session::where('uid', $AuthUser->uid)->orderBy('session_id', 'DESC')->first()->update(['endtime' 	=> $current_time]);
			
			$result 						= User::where('uid',$AuthUser->uid)->update(['firebaseToken' => null]);
		}
}
?>
