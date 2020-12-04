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
				$current_timestamp 		= Carbon::now()->timestamp;
				$current_time 				= Carbon::now();

				$role									=	Auth::user()->role;
        $ip 									= request()->ip();
        $user 								= Auth::user();
        $token 								= $user->createToken('GudExam')->accessToken;

				$session = Session::create([
					'username' => $this->username,
					'role' => $role,
					'ip' => $ip,
					'starttime' => $current_time,
					'endtime' => '0',
					'created_at' => $current_time,
					'updated_at' => $current_time,
				]);

				if(strtoupper($role)=='ADMIN')
				{
					return response()->json([
								'status' 		=> 'success',
                'token' 		=> $token,
								'message'		=> 'ADMIN Login Successfull...',
								'role'			=> 'ADMIN',
								'username' 	=> Auth::user()->username,
							],200);
				}
				else if(strtoupper($role)=='EADMIN')
				{
					return response()->json([
								'status' 		=> 'success',
                'token' 		=> $token,
								'message'		=> 'EADMIN Login Successfull...',
								'role'			=> 'EADMIN',
								'username' 	=> Auth::user()->username,
							],200);
				}
				else if(strtoupper($role)=='GADMIN')
				{
					return response()->json([
								'status' 		=> 'success',
                'token' 		=> $token,
								'message'		=> 'GADMIN Login Successfull...',
								'role'			=> 'GADMIN',
								'username' 	=> Auth::user()->username,
							],200);
				}
				else if(strtoupper($role)=='CADMIN')
				{
					return response()->json([
								'status' 		=> 'success',
                'token' 		=> $token,
								'message'		=> 'CDMIN Login Successfull...',
								'role'			=> 'CADMIN',
								'username' 	=> Auth::user()->username,
							],200);
				}
				else if(strtoupper($role)=='STUDENT')
				{
					return response()->json([
								'status' 		=> 'success',
                'token' 		=> $token,
								'message'		=> 'STUDENT Login Successfull...',
								'role'			=> 'STUDENT',
								'username' 	=> Auth::user()->username,
							],200);
				}
				else
				{
					return response()->json([
								'status' => 'failure',
								'message'		=> 'Unauthorized User...',
							],401);
				}
			}
			else
			{
				return response()->json([
							'status' => 'failure',
							'message'		=> 'Unauthorized User...',
						],401);
			}
    }

		public function customLogout()
		{
			$current_timestamp 		= Carbon::now()->timestamp;
			$current_time 				= Carbon::now();

			$username = Auth::user()->username;
			$result = DB::select("update sessions set endtime ='$current_time' where username='$username' order by created_at desc limit 1");
		}
}
?>
