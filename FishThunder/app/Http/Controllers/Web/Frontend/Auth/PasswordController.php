<?php 
namespace VanguardLTE\Http\Controllers\Web\Frontend\Auth
{
    include_once(base_path() . '/app/ShopCore.php');
    include_once(base_path() . '/app/ShopGame.php');
    class PasswordController extends \VanguardLTE\Http\Controllers\Controller
    {
        private $users = null;
        private $max_users = 10000;
        public function __construct(\VanguardLTE\Repositories\User\UserRepository $users)
        {
            $this->middleware('auth');
            // $this->middleware('permission_api:users.manage');
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
        public function forgotPassword()
        {
            $frontend = $this->getBasicTheme();
            return view('frontend.' . $frontend . '.auth.password.remind');
        }
        public function sendPasswordReminder(\VanguardLTE\Http\Requests\Auth\PasswordRemindRequest $request, \VanguardLTE\Repositories\User\UserRepository $users)
        {
            $user = $users->findByEmail($request->email);
            $token = \Password::getRepository()->create($user);
            $user->notify(new \VanguardLTE\Notifications\ResetPassword($token));
            event(new \VanguardLTE\Events\User\RequestedPasswordResetEmail($user));
            return redirect()->to('password/remind')->with('success', trans('app.password_reset_email_sent'));
        }
        public function getReset($token = null)
        {
            if( is_null($token) ) 
            {
                throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
            }
            $frontend = $this->getBasicTheme();
            return view('frontend.' . $frontend . '.auth.password.reset')->with('token', $token);
        }
        public function postReset(\VanguardLTE\Http\Requests\Auth\PasswordResetRequest $request, \VanguardLTE\Repositories\User\UserRepository $users)
        {
            $credentials = $request->only('email', 'password', 'password_confirmation', 'token');
            $response = \Password::reset($credentials, function($user, $password)
            {
                $this->resetPassword($user, $password);
            });
            switch( $response ) 
            {
                case \Password::PASSWORD_RESET:
                    $user = $users->findByEmail($request->email);
                    \Auth::login($user);
                    return redirect('');
                default:
                    return redirect()->back()->withInput($request->only('email'))->withErrors(['email' => trans($response)]);
            }
        }
        protected function resetPassword($user, $password)
        {
            $user->password = $password;
            $user->save();
            event(new \VanguardLTE\Events\User\ResetedPasswordViaEmail($user));
        }

        public function changePassword(\Illuminate\Http\Request $request)
        {
            $data = $request->all();
            $user = auth()->user();

            if($data['password'] != $data['password_confirmation'])
                return json_encode(['success' => false, 'result' => 'password confirmation is wrong']);
            // $old_password = $data['old_password'];
            // if( !\Illuminate\Support\Facades\Hash::check($old_password, $user->password) ) 
            // {
            //     return response()->json(['error' => trans('passwords.current_password')], 422);
            // }

            $this->users->update($user->id, $request->only('password', 'password_confirmation'));
            return json_encode(['success' => true, 'result' => 'password is changed successfully']);
        }

        public function deleteUser()
        {
            $user = auth()->user();
            $user->is_blocked = 2;
            $user->update();
            \Auth::logout();
            return '{"result":"success"}';
        }
    }

}
