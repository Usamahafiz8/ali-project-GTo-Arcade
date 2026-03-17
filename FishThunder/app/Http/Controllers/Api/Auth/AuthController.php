<?php 

namespace VanguardLTE\Http\Controllers\Api\Auth
{

    use Tymon\JWTAuth\Facades\JWTAuth;

    include_once(base_path() . '/app/ShopCore.php');
    include_once(base_path() . '/app/ShopGame.php');
    class AuthController extends \VanguardLTE\Http\Controllers\Api\ApiController
    {
        public function __construct()
        {
            // $this->middleware('guest')->only('login');
            // $this->middleware('auth')->only('logout');
        }
        public function login(\VanguardLTE\Http\Requests\Auth\LoginRequest $request, \VanguardLTE\Repositories\Session\SessionRepository $sessionRepository)
        {
            $credentials = $request->getCredentials();
            if( settings('use_email') ) 
            {
                if( filter_var($credentials['username'], FILTER_VALIDATE_EMAIL) ) 
                {
                    $credentials = [
                        'email' => $credentials['username'], 
                        'password' => $credentials['password']
                    ];
                }
                else
                {
                    $credentials = [
                        'username' => $credentials['username'], 
                        'password' => $credentials['password']
                    ];
                }
            }
            $user = \Auth::getProvider()->retrieveByCredentials($credentials);
            if($user == null)
            {
                return $this->errorUnauthorized('Invalid credentials.');
            }
            if( settings('reset_authentication') /*&& $user->hasRole('user')*/ && count($sessionRepository->getUserSessions(\Auth::id())) ) 
            {
                foreach( $sessionRepository->getUserSessions($user->id) as $session ) 
                {
                    $sessionRepository->invalidateSession($session->id);
                }
            }
            try
            {
                if( !($token = JWTAuth::attempt($credentials)) ) 
                {
                    return $this->errorUnauthorized('Invalid credentials.');
                }
            }
            catch( \Tymon\JWTAuth\Exceptions\JWTException $e ) 
            {
                return $this->errorInternalError('Could not create token.');
            }
            $user = auth()->user();
            if( $user->isBlocked() ) 
            {
                return $this->errorUnauthorized('Your account is blocked');
            }
            if( $user->isDeleted() ) 
            {
                return $this->errorUnauthorized('Invalid credentials.');
            }

            //check if user is logged in from other location
            // $redis = app()->make('redis');
            // $player_key = "player_time_"; 
            // $player_val = json_decode($redis->get($player_key . $user->id));
            // if($player_val != null && $player_val->time >= time() - 10)
            // {
            //     $old_ip_address = $player_val->ip;
            //     $ip_address = request()->ip();
            //     if($old_ip_address != $ip_address)
            //     {
            //         return $this->errorUnauthorized('Logged in from other device');
            //     }
            // }
            // $data = ["time" => time(), "ip" => request()->ip()];
            // $redis->set($player_key . $user->id, json_encode($data));

            if( settings('use_email') && $user->isUnconfirmed() ) 
            {
                return $this->errorUnauthorized(trans('app.please_confirm_your_email_first'));
            }
            if( $user->isBanned() ) 
            {
                $this->invalidateToken($token);
                return $this->errorUnauthorized('Your account is banned by administrators.');
            }
            if( !isset($request->skip_event) ) 
            {
                event(new \VanguardLTE\Events\User\LoggedIn());
            }
            $id = $user->id;
            \VanguardLTE\User::where('id', '=', $id)->update(['api_token' => $token]);
            return $this->respondWithArray(compact('token'));
        }
        private function invalidateToken($token)
        {
            JWTAuth::setToken($token);
            JWTAuth::invalidate();
        }

        public function logout(\Illuminate\Http\Request $request)
        {
            $data = $request->all();
            $username = $data['username'];
            $token = $data['token'];
            $users = \VanguardLTE\User::where('username', '=', $username)->get();
            $user = $users[0];
            if($user->api_token == $token)
            {
                $user->api_token = "";
                $user->save();
                return $this->respondWithSuccess();
            }
            else
            {
                return $this->errorForbidden('invalid authorization');
            }            
        }
    }
}
