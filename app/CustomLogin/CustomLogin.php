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


	public function customLoginAuthentication()
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
					'status'						=>  'failure',
					'message' 					=> 	$validator->errors()->first(),
			],200);
    }

		$spass = Config::get('constants.SPASS');
		if(strcmp($spass,$this->password)!=0)
		{
				if($this->flag==0)
				{
					$user_data = array(
						'username'  => $this->username,
						'password' => $this->password,
						'inst_id' => $this->inst_id,
						'status' => 'ON'
					);
				}
				else
				{
					$user_data = array(
						'username'  => $this->username,
						'password' => $this->password,
						'status' => 'ON'
					);
				}
		}
		else
		{
			$user_data = array(
				'username'  => $this->username,
				'password' => $this->password,
				'status' => 'ON'
			);
		}

			if(Auth::attempt($user_data))
			{

				$sessionResult = User::find(Auth::user()->uid)->sessions()->orderBy('session_id','DESC')->first();

				//---------Condition to Check Already Logged in or proper Log Out-------
				if($sessionResult)
				{
					if($sessionResult->endtime == '')
					{
						return response()->json([
									'status' 		=> 'failure',
									'message'   => 'You have already logged in using other device. Clear Your Session to Login.'
								],200);
					}
				}
				//----------------------------------------------------------------------

				$current_timestamp 		= Carbon::now()->timestamp;
				$current_time 				= Carbon::now();

				$role									=	Auth::user()->role;
        $ip 									= request()->ip();
        $user 								= Auth::user();
        $token 								= $user->createToken('GudExam')->accessToken;

				$session = Session::create([
					'uid' 						=> Auth::user()->uid,
					'role' 						=> $role,
					'ip' 							=> $ip,
					'starttime' 			=> $current_time,
					'created_at' 			=> $current_time,
					'updated_at' 			=> $current_time,
				]);

				if(strtoupper($role)=='ADMIN')
				{
					return response()->json([
								'status' 		=> 'success',
                'token' 		=> $token,
								'session_id'=> $session->session_id,
								'data' 			=> Auth::user(),
							],200);
				}
				else if(strtoupper($role)=='EADMIN')
				{
					return response()->json([
								'status' 		=> 'success',
                'token' 		=> $token,
								'session_id'=> $session->session_id,
								'data' 	=> Auth::user(),
							],200);
				}
				else if(strtoupper($role)=='GADMIN')
				{
					return response()->json([
								'status' 		=> 'success',
                'token' 		=> $token,
								'session_id'=> $session->session_id,
								'data' 	=> Auth::user(),
							],200);
				}
				else if(strtoupper($role)=='CADMIN')
				{
					return response()->json([
								'status' 		=> 'success',
                'token' 		=> $token,
								'session_id'=> $session->session_id,
								'data' 	=> Auth::user(),
							],200);
				}
				else if(strtoupper($role)=='STUDENT')
				{
					return response()->json([
								'status' 		=> 'success',
                'token' 		=> $token,
								'session_id'=> $session->session_id,
								'username' 	=> Auth::user(),
							],200);
				}
				else
				{
					return response()->json([
								'status' => 'failure',
								'message'		=> 'Unauthorized User...',
							],200);
				}
			}
			else
			{
				return response()->json([
							'status' => 'failure',
							'message'		=> 'Invalid Username or Password',
						],200);
			}
    }

		public function customLogout()
		{
			$current_timestamp 		= Carbon::now()->timestamp;
			$current_time 				= Carbon::now();
			$result 							= Session::where('uid', Auth::user()->uid)->orderBy('session_id', 'DESC')->first()->update(['endtime' => $current_time]);
		}
}
?>
