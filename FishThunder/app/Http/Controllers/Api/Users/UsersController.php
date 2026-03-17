<?php 
namespace VanguardLTE\Http\Controllers\Api\Users
{
    use VanguardLTE\Shop;
    include_once(base_path() . '/app/ShopCore.php');
    include_once(base_path() . '/app/ShopGame.php');
    class UsersController extends \VanguardLTE\Http\Controllers\Api\ApiController
    {
        private $users = null;
        private $max_users = 10000;
        public function __construct(\VanguardLTE\Repositories\User\UserRepository $users)
        {
            $this->middleware('auth');
            // $this->middleware('permission_api:users.manage');
            $this->users = $users;
        }
        public function index(\Illuminate\Http\Request $request)
        {
            $users = \VanguardLTE\User::orderBy('created_at', 'DESC');
            if( !auth()->user()->shop_id ) 
            {
                if( auth()->user()->hasRole('admin') ) 
                {
                    $users = $users->whereIn('role_id', [
                        4, 
                        5
                    ]);
                }
                if( auth()->user()->hasRole('agent') ) 
                {
                    $distributors = auth()->user()->availableUsersByRole('distributor');
                    if( $distributors ) 
                    {
                        $users = $users->whereIn('id', $distributors);
                    }
                    else
                    {
                        $users = $users->where('id', 0);
                    }
                }
                if( auth()->user()->hasRole('distributor') ) 
                {
                    $managers = auth()->user()->availableUsersByRole('manager');
                    if( $managers ) 
                    {
                        $users = $users->whereIn('id', $managers);
                    }
                    else
                    {
                        $users = $users->where('id', 0);
                    }
                }
            }
            else
            {
                $users = $users->whereIn('id', auth()->user()->availableUsers())->whereHas('rel_shops', function($query)
                {
                    $query->where('shop_id', auth()->user()->shop_id);
                });
            }
            $users = $users->where('id', '!=', auth()->user()->id);
            if( $request->search != '' ) 
            {
                $users = $users->where('username', 'like', '%' . $request->search . '%');
            }
            if( $request->status != '' ) 
            {
                $users = $users->where('status', $request->status);
            }
            if( $request->role ) 
            {
                $users = $users->where('role_id', $request->role);
            }
            $users = $users->paginate(50);
            return $this->respondWithPagination($users, new \VanguardLTE\Transformers\UserTransformer());
        }
        
        public function player_store(\Illuminate\Http\Request $request)
        {
            //create player
            $request_data = $request->all();
            $username = $request_data['username'];
            $password = $request_data['password'];

            $validator = \Illuminate\Support\Facades\Validator::make($request_data, [
                'name' => 'required|min:4',
                'username' => 'required|unique:users,username|min:6|max:16',
                'password' => 'required|confirmed|min:6|max:16',
                'phone' => 'required|digits:10',
                'email' => 'required|email',                
            ]);
            if( $validator->fails() ) 
            {
                $error = $validator->errors()->first();
                return $this->respondWithArray(['status' => 'failure', 'error' => $error]);                
            }

            //get demo shop
            $demoshop = Shop::where('is_demo', '=', 1)->get();
            if(count($demoshop) == 0)
            {
                return $this->respondWithArray(['status' => 'failure', 'error' => 'no demo shop']);                
            }
            $shop = $demoshop[0];
            $data = [];
            $data['username'] = $username;
            $data['password'] = $password;
            $data['first_name'] = $request_data['name'];
            $data['email'] = $request_data['email'];            
            $data['phone'] = $request_data['phone'];
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
            
            return $this->respondWithArray(['status' => 'success']);
        }

        public function store(\VanguardLTE\Http\Requests\User\CreateUserRequest $request)
        {
            $count = \VanguardLTE\User::where([
                'shop_id' => auth()->user()->shop_id, 
                'role_id' => 1
            ])->count();
            if( $this->max_users <= $count ) 
            {
                return $this->setStatusCode(403)->respondWithError(trans('app.max_users', ['max' => $this->max_users]));
            }
            if( auth()->user()->role_id <= 1 ) 
            {
                return $this->setStatusCode(403)->respondWithError(trans('app.no_permission'));
            }
            $data = $request->only([
                'username', 
                'password'
            ]);
            $role = \jeremykenedy\LaravelRoles\Models\Role::find(auth()->user()->role_id - 1);
            $data += ['status' => \VanguardLTE\Support\Enum\UserStatus::ACTIVE];
            $data += ['parent_id' => auth()->user()->id];
            $data += ['role_id' => $role->id];
            $data += ['shop_id' => auth()->user()->shop_id];
            if( isset($request->balance) && $request->balance > 0 && $role->name == 'User' ) 
            {
                $shop = \VanguardLTE\Shop::find($data['shop_id']);
                $sum = floatval($request->balance);
                if( $shop->balance < $sum ) 
                {
                    return $this->setStatusCode(403)->respondWithError(trans('app.not_enough_money_in_the_shop', [
                        'name' => $shop->name, 
                        'balance' => $shop->balance
                    ]));
                }
                $open_shift = \VanguardLTE\OpenShift::where([
                    'shop_id' => $data['shop_id'], 
                    'user_id' => auth()->user()->id, 
                    'end_date' => null
                ])->first();
                if( !$open_shift ) 
                {
                    return $this->setStatusCode(403)->respondWithError(trans('app.shift_not_opened'));
                }
            }
            if( auth()->user()->hasRole('distributor') && $role->slug == 'manager' && \VanguardLTE\User::where([
                'role_id' => $role->id, 
                'shop_id' => $data['shop_id']
            ])->count() ) 
            {
                return $this->setStatusCode(403)->respondWithError(trans('app.only_1', ['type' => $role->slug]));
            }
            $user = $this->users->create($data);
            $user->detachAllRoles();
            $user->attachRole($role);
            if( isset($data['shop_id']) && $data['shop_id'] > 0 && $role->name == 'User' ) 
            {
                \VanguardLTE\ShopUser::create([
                    'shop_id' => $data['shop_id'], 
                    'user_id' => $user->id
                ]);
            }
            if( isset($request->balance) && $request->balance > 0 && $role->name == 'User' ) 
            {
                $user->addBalance('add', $request->balance);
            }
            return $this->setStatusCode(201)->respondWithItem($user, new \VanguardLTE\Transformers\UserTransformer());
        }

        public function mass(\Illuminate\Http\Request $request)
        {
            if( !auth()->user()->hasRole('cashier') ) 
            {
                return $this->setStatusCode(403)->respondWithError(trans('app.no_permission'));
            }
            if( isset($request->count) && is_numeric($request->count) && $request->count > 100 ) 
            {
                return $this->setStatusCode(403)->respondWithError('Max users 100 per request');
            }
            $shop = \VanguardLTE\Shop::find(auth()->user()->shop_id);
            $count = \VanguardLTE\User::where([
                'shop_id' => auth()->user()->shop_id, 
                'role_id' => 1
            ])->count();
            if( isset($request->count) && is_numeric($request->count) && isset($request->balance) && is_numeric($request->balance) ) 
            {
                if( $this->max_users < ($count + $request->count) ) 
                {
                    return $this->setStatusCode(403)->errorWrongArgs(trans('max_users', ['max' => $this->max_users]));
                }
                if( $request->balance > 0 ) 
                {
                    if( $shop->balance < ($request->count * $request->balance) ) 
                    {
                        return $this->setStatusCode(403)->respondWithError(trans('app.not_enough_money_in_the_shop', [
                            'name' => $shop->name, 
                            'balance' => $shop->balance
                        ]));
                    }
                    $open_shift = \VanguardLTE\OpenShift::where([
                        'shop_id' => auth()->user()->shop_id, 
                        'user_id' => auth()->user()->id, 
                        'end_date' => null
                    ])->first();
                    if( !$open_shift ) 
                    {
                        return $this->setStatusCode(403)->respondWithError(trans('app.shift_not_opened'));
                    }
                }
                if( auth()->user()->hasRole('cashier') ) 
                {
                    $role = \jeremykenedy\LaravelRoles\Models\Role::find(1);
                    for( $i = 0; $i < $request->count; $i++ ) 
                    {
                        $number = rand(111111111, 999999999);
                        $data = [
                            'username' => $number, 
                            'password' => $number, 
                            'role_id' => $role->id, 
                            'status' => \VanguardLTE\Support\Enum\UserStatus::ACTIVE, 
                            'parent_id' => auth()->user()->id, 
                            'shop_id' => auth()->user()->shop_id
                        ];
                        $newUser = $this->users->create($data);
                        $newUser->attachRole($role);
                        \VanguardLTE\ShopUser::create([
                            'shop_id' => auth()->user()->shop_id, 
                            'user_id' => $newUser->id
                        ]);
                        if( $request->balance > 0 ) 
                        {
                            $newUser->addBalance('add', $request->balance);
                        }
                    }
                }
            }
            return $this->respondWithSuccess();
        }
        public function show(\VanguardLTE\User $user)
        {
            $users = auth()->user()->availableUsers();
            if( count($users) && !in_array($user->id, $users) ) 
            {
                return $this->setStatusCode(403)->respondWithError(trans('app.no_permission'));
            }
            if( auth()->user()->role_id < $user->role_id ) 
            {
                return $this->setStatusCode(403)->respondWithError(trans('app.no_permission'));
            }
            return $this->respondWithItem($user, new \VanguardLTE\Transformers\UserTransformer());
        }
        public function update(\VanguardLTE\User $user, \VanguardLTE\Http\Requests\User\UpdateUserRequest $request)
        {
            $users = auth()->user()->availableUsers();
            if( count($users) && !in_array($user->id, $users) ) 
            {
                return $this->setStatusCode(403)->respondWithError(trans('app.no_permission'));
            }
            if( auth()->user()->role_id < $user->role_id ) 
            {
                return $this->setStatusCode(403)->respondWithError(trans('app.no_permission'));
            }
            $request->validate([
                'username' => 'required|unique:users,username,' . $user->id, 
                'email' => 'nullable|unique:users,email,' . $user->id
            ]);
            $data = $request->all();
            if( empty($data['password']) ) 
            {
                unset($data['password']);
            }
            if( empty($data['password_confirmation']) ) 
            {
                unset($data['password_confirmation']);
            }
            if( $request->status != $user->status ) 
            {
                if( $request->status == \VanguardLTE\Support\Enum\UserStatus::ACTIVE && $user->status == \VanguardLTE\Support\Enum\UserStatus::BANNED ) 
                {
                    event(new \VanguardLTE\Events\User\UserUnBanned($user));
                }
                if( $request->status == \VanguardLTE\Support\Enum\UserStatus::ACTIVE && $user->status == \VanguardLTE\Support\Enum\UserStatus::UNCONFIRMED ) 
                {
                    event(new \VanguardLTE\Events\User\UserConfirmed($user));
                }
            }
            $user = $this->users->update($user->id, $data);
            event(new \VanguardLTE\Events\User\UpdatedByAdmin($user));
            if( $this->userIsBanned($user, $request) ) 
            {
                event(new \VanguardLTE\Events\User\Banned($user));
            }
            return $this->respondWithItem($user, new \VanguardLTE\Transformers\UserTransformer());
        }
        private function userIsBanned(\VanguardLTE\User $user, \Illuminate\Http\Request $request)
        {
            return $user->status != $request->status && $request->status == \VanguardLTE\Support\Enum\UserStatus::BANNED;
        }
        public function destroy(\VanguardLTE\User $user)
        {
            if( $user->id == auth()->user()->id ) 
            {
                return $this->errorForbidden(trans('app.you_cannot_delete_yourself'));
            }
            if( !auth()->user()->hasRole('admin') ) 
            {
                $users = auth()->user()->availableUsers();
                if( count($users) && !in_array($user->id, $users) ) 
                {
                    return $this->setStatusCode(403)->respondWithError(trans('app.no_permission'));
                }
                if( $user->balance > 0 ) 
                {
                    return $this->errorForbidden(trans('app.balance_not_zero'));
                }
                if( (auth()->user()->hasRole('admin') && $user->hasRole('agent') || auth()->user()->hasRole('agent') && $user->hasRole('distributor') || auth()->user()->hasRole('distributor') && $user->hasRole('manager')) && ($count = \VanguardLTE\User::where('parent_id', $user->id)->count()) ) 
                {
                    return $this->errorForbidden(trans('app.has_users', ['name' => $user->username]));
                }
                if( (auth()->user()->hasRole('admin') && $user->hasRole('agent') || auth()->user()->hasRole('agent') && $user->hasRole('distributor') || auth()->user()->hasRole('distributor') && $user->hasRole('manager') || auth()->user()->hasRole('manager') && $user->hasRole('cashier')) && $this->hasActivities($user) ) 
                {
                    return $this->errorForbidden(trans('app.has_stats', ['name' => $user->username]));
                }
            }
            $user->detachAllRoles();
            \VanguardLTE\Statistic::where('user_id', $user->id)->delete();
            \VanguardLTE\StatisticAdd::where('user_id', $user->id)->delete();
            \VanguardLTE\ShopUser::where('user_id', $user->id)->delete();
            \VanguardLTE\StatGame::where('user_id', $user->id)->delete();
            \VanguardLTE\GameLog::where('user_id', $user->id)->delete();
            \VanguardLTE\UserActivity::where('user_id', $user->id)->delete();
            \VanguardLTE\Session::where('user_id', $user->id)->delete();
            \VanguardLTE\Info::where('user_id', $user->id)->delete();
            $user->delete();
            return $this->respondWithSuccess();
        }

        public function changePassword(\Illuminate\Http\Request $request)
        {
            $data = $request->all();
            $username = $data['username'];
            $token = $data['token'];
            $users = \VanguardLTE\User::where('username', '=', $username)->get();
            $user = $users[0];
            if($user->api_token != $token)
            {
                return $this->errorForbidden('invalid authorization');  
            }
            else
            {
                if($data['password'] != $data['password_confirmation'])
                    return $this->errorForbidden('password confirmation is wrong');  
                $old_password = $data['old_password'];
                if( !\Illuminate\Support\Facades\Hash::check($old_password, $user->password) ) 
                {
                    return response()->json(['error' => trans('passwords.current_password')], 422);
                }

                $this->users->update($user->id, $request->only('password', 'password_confirmation'));
                return $this->respondWithSuccess();
            }
        }

        public function getReward(\Illuminate\Http\Request $request)
        {
            $data = $request->all();
            $username = $data['username'];
            $token = $data['token'];
            $users = \VanguardLTE\User::where('username', '=', $username)->get();
            $user = $users[0];
            if($user->api_token != $token)
            {
                return $this->errorForbidden('invalid authorization');  
            }
            else
            {
                if($user->reward_base == 0)
                {
                    return $this->errorForbidden('reward bonus not available');  
                }

                $rand_percents = [10 * rand(1, 3), 10 * rand(1, 3), 10 * rand(1, 3), 10 * rand(1, 3), 10 * rand(1, 3)];
                $pick = $data['pick'];
                if($pick > 4)
                    $pick = rand(0, 4);
                $percent = $rand_percents[$pick];
                $available_win = [0,0,0,0,0];
                for($i = 0; $i < 5; $i++)
                    $available_win[$i] = (int)($user->reward_base * $rand_percents[$i] / 100);
                $reward_bonus = (int)($user->reward_base * $percent / 100);
                $user->balance = $user->balance + $reward_bonus;
                $user->reward_bonus = $user->reward_bonus + $reward_bonus * 2;
                $user->reward_base = 0;
                $user->save();
                $res = [
                    'status' => 'success',
                    'pick_items' => $available_win,
                    'picked_index' => $pick,
                    'win_amount' => $reward_bonus
                ];
                return $this->respondWithArray($res);
            }
        }
    }

}
