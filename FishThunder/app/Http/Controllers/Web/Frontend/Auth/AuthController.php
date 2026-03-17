<?php 
namespace VanguardLTE\Http\Controllers\Web\Frontend\Auth
{
    use VanguardLTE\Shop;

    include_once(base_path() . '/app/ShopCore.php');
    include_once(base_path() . '/app/ShopGame.php');
    class AuthController extends \VanguardLTE\Http\Controllers\Controller
    {
        private $users = null;
        protected $redirectTo = null;
        public function __construct(\VanguardLTE\Repositories\User\UserRepository $users)
        {
            $this->middleware('guest', [
                'except' => [
                    'getLogout', 
                    'apiLogin'
                ]
            ]);
            $this->middleware('auth', [
                'only' => ['getLogout']
            ]);
            $this->middleware('registration', [
                'only' => [
                    'getRegister', 
                    'postRegister'
                ]
            ]);
            $this->users = $users;
        }
        public function getBasicTheme()
        {
            $frontend = settings('frontend');
            if( \Auth::check() ) 
            {
                $shop = \Shop::find(\Auth::user()->shop_id);
                if( $shop ) 
                {
                    $frontend = $shop->frontend;
                }
            }
            return $frontend;
        }
        public function getLogin()
        {
            // return redirect()->route('frontend.game.list')->with('modal', 'modal-login');
            $frontend = $this->getBasicTheme();
            $directories = [];
            foreach( glob(resource_path() . '/lang/*', GLOB_ONLYDIR) as $fileinfo ) 
            {
                $dirname = basename($fileinfo);
                $directories[$dirname] = $dirname;
            }
            return view('frontend.' . $frontend . '.auth.login', compact('directories'));
        }
        public function postLogin(\VanguardLTE\Http\Requests\Auth\LoginRequest $request, \VanguardLTE\Repositories\Session\SessionRepository $sessionRepository)
        {
            $throttles = settings('throttle_enabled');
            $to = ($request->has('to') ? '?to=' . $request->get('to') : '');
            if( $throttles && $this->hasTooManyLoginAttempts($request) ) 
            {
                return $this->sendLockoutResponse($request);
            }
            $credentials = $request->getCredentials();
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
            if( !\Auth::validate($credentials) ) 
            {
                if( $throttles ) 
                {
                    $this->incrementLoginAttempts($request);
                }
                return redirect()->to('login' . $to)->withErrors(trans('auth.failed'));
            }
            $user = \Auth::getProvider()->retrieveByCredentials($credentials);

            if( $user->isBlocked() ) 
            {
                return redirect()->to('login' . $to)->withErrors('Your account is blocked');
            }

            if( $user->isDeleted() ) 
            {
                return redirect()->to('login' . $to)->withErrors('Your account is deleted');
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
            //         return redirect()->to('login' . $to)->withErrors('Logged in from other device');
            //     }
            // }
            // $data = ["time" => time(), "ip" => request()->ip()];
            // $redis->set($player_key . $user->id, json_encode($data));
            
            if( $user->inviter_id != '' && $user->phone != '' && !$user->phone_verified ) 
            {
                $user->update([
                    'status' => \VanguardLTE\Support\Enum\UserStatus::ACTIVE, 
                    'phone_verified' => 1
                ]);
            }
            if( $user->hasRole('user') && $user->shop && $user->shop->pending ) 
            {
                return redirect()->to('login' . $to)->withErrors(__('app.shop_is_creating'));
            }
            if( settings('use_email') && $user->isUnconfirmed() ) 
            {
                return redirect()->to('login' . $to)->withErrors(trans('app.please_confirm_your_email_first'));
            }
            if( $user->isBanned() ) 
            {
                return redirect()->to('login' . $to)->withErrors(trans('app.your_account_is_banned'));
            }
            if( $request->lang ) 
            {
                $user->update(['language' => $request->lang]);
            }
            if( settings('reset_authentication') && $user->hasRole('user') && count($sessionRepository->getUserSessions(\Auth::id())) ) 
            {
                foreach( $sessionRepository->getUserSessions($user->id) as $session ) 
                {
                    $sessionRepository->invalidateSession($session->id);
                }
            }
            \Auth::login($user, true);
            return $this->handleUserWasAuthenticated($request, $throttles, $user);
        }

        public function postSignup(\Illuminate\Http\Request $request, \VanguardLTE\Repositories\Session\SessionRepository $sessionRepository)
        {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'username' => 'required|regex:/^[A-Za-z0-9-]+$/|unique:users,username|min:6|max:16',
                'password' => 'required|min:6|max:16',
                'phone' => 'required|digits:10',
                'email' => 'required|email|unique:users,email'
            ]);
            if($validator->fails())
            {
                $error = $validator->errors()->first();
                return json_encode(['result' => 'error', 'message' => $error]);
            }

            $data = $request->all();
            //get demo shop
            $demoshop = Shop::where('is_demo', '=', 1)->get();
            if(count($demoshop) == 0)
            {
                return json_encode(['result' => 'failure', 'message' => 'no demo shop']);                
            }
            $shop = $demoshop[0];
            unset($data['_token']);
            $data['username'] = $data['username'];
            $data['password'] = $data['password'];
            $data['first_name'] = '';
            $data['email'] = $data['email'];            
            $data['phone'] = $data['phone'];
            $data['language'] = 'en';
            $data['status'] = 'Active';
            $data['shop_id'] = $shop->id;
            $data['is_blocked'] = 0;                        
            $data['role_id'] = 1; //player role
            $data['parent_id'] = 0;
            $data['balance'] = 5;
            $data['parents'] = '[0]';

            $user = $this->users->create($data + ['status' => \VanguardLTE\Support\Enum\UserStatus::ACTIVE]);

            $role = \jeremykenedy\LaravelRoles\Models\Role::find($data['role_id']);
            $user->detachAllRoles();
            $user->attachRole($role);

            //set as shop user when cashier or player
            \VanguardLTE\ShopUser::create([
                'shop_id' => $user->shop_id, 
                'user_id' => $user->id
            ]);

            if( !$user->shop_id ) 
            {
                $shops = $user->shops(true);
                if( count($shops) ) 
                {
                    $shop_id = $shops->first();
                    $user->update(['shop_id' => $shop_id]);
                }
            }

            return json_encode(['status' => 'success', 'message' => 'Free user created successfully']);
        }

        public function specauth(\Illuminate\Http\Request $request, \VanguardLTE\User $user)
        {
            if( !$user ) 
            {
                abort(403);
            }
            if( $request->token != '' && $user->auth_token == $request->token ) 
            {
                \Auth::loginUsingId($user->id);
                if( !$user->hasRole('user') ) 
                {
                    if( !\Auth::user()->hasPermission('dashboard') ) 
                    {
                        return redirect()->route('backend.user.list');
                    }
                    return redirect()->route('backend.dashboard');
                }
                return redirect()->intended();
            }
            return redirect()->route('frontend.auth.login')->withErrors([trans('app.no_permission')]);
        }
        public function apiLogin($game, $token)
        {
            if( \Auth::check() ) 
            {
                event(new \VanguardLTE\Events\User\LoggedOut());
                \Auth::logout();
            }
            $us = \VanguardLTE\User::where('api_token', '=', $token)->get();
            if( isset($us[0]->id) ) 
            {
                \Auth::loginUsingId($us[0]->id, true);
                $ref = request()->server('HTTP_REFERER');
                $gameUrl = 'game/' . $game . '?api_exit=' . $ref;
                return redirect()->to($gameUrl);
            }
            else
            {
                return redirect()->to('');
            }
        }
        protected function handleUserWasAuthenticated(\Illuminate\Http\Request $request, $throttles, $user)
        {
            if( $throttles ) 
            {
                $this->clearLoginAttempts($request);
            }
            event(new \VanguardLTE\Events\User\LoggedIn());
            if( $request->has('to') ) 
            {
                return redirect()->to($request->get('to'));
            }
            if( !$user->hasRole('user') ) 
            {
                if( !\Auth::user()->hasPermission('dashboard') ) 
                {
                    return redirect()->route('backend.user.list');
                }
                return redirect()->route('backend.dashboard');
            }
            return redirect('/');
            // return redirect()->intended();
        }
        public function getLogout()
        {
            event(new \VanguardLTE\Events\User\LoggedOut());
            if( session()->exists('beforeUser') ) 
            {
                \Auth::loginUsingId(session('beforeUser'));
                session()->forget('beforeUser');
                return redirect()->route('backend.dashboard');
            }
            \Auth::logout();
            return redirect('/');
        }
        public function loginUsername()
        {
            return 'username';
        }
        protected function hasTooManyLoginAttempts(\Illuminate\Http\Request $request)
        {
            return app('Illuminate\Cache\RateLimiter')->tooManyAttempts($request->input($this->loginUsername()) . $request->ip(), $this->maxLoginAttempts());
        }
        protected function incrementLoginAttempts(\Illuminate\Http\Request $request)
        {
            app('Illuminate\Cache\RateLimiter')->hit($request->input($this->loginUsername()) . $request->ip(), $this->lockoutTime() / 60);
        }
        protected function retriesLeft(\Illuminate\Http\Request $request)
        {
            $attempts = app('Illuminate\Cache\RateLimiter')->attempts($request->input($this->loginUsername()) . $request->ip());
            return $this->maxLoginAttempts() - $attempts + 1;
        }
        protected function sendLockoutResponse(\Illuminate\Http\Request $request)
        {
            $seconds = app('Illuminate\Cache\RateLimiter')->availableIn($request->input($this->loginUsername()) . $request->ip());
            return redirect('/')->withInput($request->only($this->loginUsername(), 'remember'))->withErrors([$this->loginUsername() => $this->getLockoutErrorMessage($seconds)]);
        }
        protected function getLockoutErrorMessage($seconds)
        {
            return trans('auth.throttle', ['seconds' => $seconds]);
        }
        protected function clearLoginAttempts(\Illuminate\Http\Request $request)
        {
            app('Illuminate\Cache\RateLimiter')->clear($request->input($this->loginUsername()) . $request->ip());
        }
        protected function maxLoginAttempts()
        {
            return settings('throttle_attempts', 5);
        }
        protected function lockoutTime()
        {
            $lockout = (int)settings('throttle_lockout_time');
            if( $lockout <= 1 ) 
            {
                $lockout = 1;
            }
            return 60 * $lockout;
        }
        public function getRegister()
        {
            $frontend = $this->getBasicTheme();
            return view('frontend.' . $frontend . '.auth.register');
        }
        public function postRegister(\VanguardLTE\Http\Requests\Auth\RegisterRequest $request)
        {
            $data = $request->only('email', 'username', 'password');
            dd($data);
            // if( isset($data['email']) && ($return = \VanguardLTE\Lib\Filter::domain_filtered($data['email'])) ) 
            // {
            //     return redirect()->route('frontend.auth.login')->withErrors([__('app.blocked_domain_zone', ['zone' => $return['domain']])]);
            // }
            $user = $this->users->create(array_merge($data, [
                'role_id' => 1, 
                //'status' => (settings('use_email') ? \VanguardLTE\Support\Enum\UserStatus::UNCONFIRMED : \VanguardLTE\Support\Enum\UserStatus::ACTIVE)
                'status' => \VanguardLTE\Support\Enum\UserStatus::ACTIVE
            ]));
            $role = \jeremykenedy\LaravelRoles\Models\Role::where('name', '=', 'User')->first();
            $user->attachRole($role);
            event(new \VanguardLTE\Events\User\Registered($user));
            $message = (settings('use_email') ? trans('app.account_create_confirm_email') : trans('app.account_created_login'));
            //if( !settings('use_email') ) 
            //{
                \Auth::login($user, true);
            //}
            return redirect()->route('frontend.auth.login')->with('success', $message);
        }
        public function checkUsername($username)
        {
            $generated = false;
            $key = 1;
            $logins = [];
            $generate = $username;
            $tmp = explode(',', settings('bots_login'));
            foreach( $tmp as $item ) 
            {
                $item = trim($item);
                if( $item ) 
                {
                    $logins[] = $item;
                }
            }
            while( !$generated ) 
            {
                $count = \VanguardLTE\User::where('username', $generate)->count();
                if( $count || in_array($generate, $logins) ) 
                {
                    $generate = $username . '_' . $key;
                }
                else
                {
                    $generated = true;
                }
                $key++;
            }
            return $generate;
        }
        public function confirmEmail($token)
        {
            if( $user = \VanguardLTE\User::withoutGlobalScope('VanguardLTE\Scopes\DemoAgent')->where('confirmation_token', $token)->first() ) 
            {
                $user->update([
                    'status' => \VanguardLTE\Support\Enum\UserStatus::ACTIVE, 
                    'confirmation_token' => null, 
                    'is_demo_agent' => 0
                ]);
                return redirect()->to('/')->withSuccess(trans('app.email_confirmed_can_login'));
            }
            return redirect()->to('/')->withErrors(trans('app.wrong_confirmation_token'));
        }
    }

}
