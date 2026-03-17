<?php
namespace VanguardLTE\Http\Controllers\Web\Backend
{

    use Carbon\Carbon;
    use Illuminate\Pagination\Paginator;
    use Illuminate\Support\Facades\Crypt;
    use Illuminate\Support\Facades\DB;
    use VanguardLTE\Contact;
    use VanguardLTE\Http\Requests\Request;

include_once(base_path() . '/app/ShopCore.php');
    include_once(base_path() . '/app/ShopGame.php');
    class UsersController extends \VanguardLTE\Http\Controllers\Controller
    {
        private $users = null;
        private $max_users = 10000000;
        public function __construct(\VanguardLTE\Repositories\User\UserRepository $users)
        {
            $this->middleware([
                'auth',
            ]);
            $this->middleware('permission:access.admin.panel');
            $this->users = $users;
        }
        public function index(\Illuminate\Http\Request $request)
        {
            if(auth()->user()->role_id <= 3)
                abort(404);
            $users = \VanguardLTE\User::select('id', 'parent_id', 'parents', 'username', 'balance', 'shop_id', 'role_id', 'is_blocked', 'last_online', 'created_at')->orderBy('parents');
            if( !auth()->user()->shop_id )
            {
                if( auth()->user()->hasRole('admin') )
                {
                    $users = $users->whereIn('role_id', [
                        3,
                        4,
                        5
                    ]);
                }
                if( auth()->user()->hasRole('agent') || auth()->user()->hasRole('admin') )
                {
                    $agents = auth()->user()->availableUsersByRole('agent');
                    $managers = auth()->user()->availableUsersByRole('manager');
                    $ids = array_merge($managers, $agents);

                    if( $ids )
                    {
                        $users = $users->whereIn('id', $ids);
                        foreach($users as $user)
                        {
                            if(in_array($user->id, $managers))
                            {

                            }
                        }
                    }
                    else
                    {
                        $users = $users->where('id', 0);
                    }
                }
            }

            $users = $users->where('id', '!=', auth()->user()->id);

            // $users_records = $users->orderBy('parents', 'desc')->paginate(3)->withQueryString();
            $users_records = $users->orderBy('created_at', 'desc')->get();

            $hierarchy = [];
            $direct_children = [];
            $users = [];

            $sort_index = 0;
            foreach($users_records as $user)
            {
                $id = $user->id;
                $parents = explode("][", $user->parents);
                $selector = [];
                for($i = 0; $i < count($parents); $i++)
                {
                    $parent_id = str_replace(["[","]"], ["",""],$parents[$i]);
                    if($parent_id > auth()->user()->id)
                        $selector[] = $parent_id;
                }

                $hierarchy[$user->id] = $selector;
                $user->sortIndex = $sort_index;
                $users["'".$id."'"] = $user;
                $direct_children[$id] = 0;
                $sort_index++;

                if($user->role_id == 3)
                {
                    //in case of shop manager, return shop balance
                    $shop = \VanguardLTE\Shop::where('id', $user->shop_id)->get();
                    if(count($shop) > 0)
                    {
                        $user->balance = $shop[0]->balance;
                    }
                }
            }

            //sort
            foreach($users_records as $user)
            {
                $parents = explode("][", $user->parents);
                $last_parent = str_replace("]", "", $parents[count($parents) - 1]);
                if($last_parent > auth()->user()->id)
                {
                    $direct_children[$last_parent]++;
                    $offset = $this->findOffsetWithKey( $users, "'".$user->id."'");
                    if($offset !== false)
                    {
                        $slice = array_splice($users, $offset, 1, null);
                        $offset_parent = $this->findOffsetWithKey($users, "'".$last_parent."'");
                        if($offset_parent !== false)
                        {
                            // array_splice($users, $offset_parent+1, 0, array("'".$user->id."'" => $slice));
                            $users = array_slice($users, 0, $offset_parent+1) + $slice + array_slice($users, $offset_parent+1);
                        }
                    }
                }
            }

            $current_user = auth()->user()->id;
            //sort by parent-child relation
            return view('backend.user.list', compact('users', 'hierarchy', 'direct_children', 'current_user'));
        }

        public function findOffsetWithKey($arr, $key)
        {
            $offset = 0;
            $found = false;
            foreach($arr as $index => $element)
            {
                if($index == $key)
                {
                    $found = true;
                    break;
                }
                $offset++;
            }
            if($found)
                return $offset;
            else
                return false;
        }

        public function get_balance()
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
            $users = $users->where('id', '!=', auth()->user()->id)->get();
            $data = [];
            foreach( $users as $user )
            {
                $data[$user->id] = ['balance' => number_format(floatval($user->balance), 2, '.', ''),
                    'shop_limit' => $user->shop_limit];
            }
            return json_encode($data);
        }
        public function tree()
        {
            if( \Auth::user()->hasRole('cashier') ) {
                return redirect()->route('netpos');

            }
            $users = \VanguardLTE\User::where('id', auth()->user()->id)->get();
            if (auth()->user()->hasRole('admin')) {
                $users = \VanguardLTE\User::where('role_id', 5)->get();
            }
            $role = \jeremykenedy\LaravelRoles\Models\Role::where('id', auth()->user()->role_id - 1)->first();
            return view('backend.user.tree', compact('users', 'role'));

        }


        public function view(\VanguardLTE\User $user, \VanguardLTE\Repositories\Activity\ActivityRepository $activities)
        {
            $userActivities = $activities->getLatestActivitiesForUser($user->id, 10);
            if( auth()->user()->role_id < $user->role_id )
            {
                return redirect()->route('backend.user.list');
            }
            return view('backend.user.view', compact('user', 'userActivities'));
        }

        public function create()
        {
            //agent and shop manager
            $shop = \VanguardLTE\Shop::find(auth()->user()->shop_id);

            $roles = \jeremykenedy\LaravelRoles\Models\Role::where('level', '<', auth()->user()->level())->pluck('name', 'id');
            $statuses = \VanguardLTE\Support\Enum\UserStatus::lists();
            $shops = auth()->user()->shops();
            $availibleUsers = [];
            if( auth()->user()->hasRole('admin') )
            {
                $availibleUsers = \VanguardLTE\User::get();
            }

            if( auth()->user()->hasRole('agent') )
            {
                $me = \VanguardLTE\User::where('id', auth()->user()->id)->get();
                $distributors = \VanguardLTE\User::where([
                    'parent_id' => auth()->user()->id,
                    'role_id' => 4
                ])->get();
                if( $shopsIds = auth()->user()->shops(true) )
                {
                    $users = \VanguardLTE\ShopUser::whereIn('shop_id', $shopsIds)->pluck('user_id');
                    if( $users )
                    {
                        $availibleUsers = \VanguardLTE\User::whereIn('id', $users)->whereIn('role_id', [
                            2,
                            3
                        ])->get();
                    }
                }
                $me = $me->merge($distributors);
                $availibleUsers = $me->merge($availibleUsers);
            }

            else if( auth()->user()->hasRole([
                'manager',
                'cashier'
            ]) )
            {
                $me = \VanguardLTE\User::where('id', auth()->user()->id)->get();
                if( $shopsIds = auth()->user()->shops(true) )
                {
                    $users = \VanguardLTE\ShopUser::whereIn('shop_id', $shopsIds)->pluck('user_id');
                    if( $users )
                    {
                        $availibleUsers = \VanguardLTE\User::whereIn('id', $users)->whereIn('role_id', [
                            2,
                            3
                        ])->get();
                    }
                }
                $availibleUsers = $me->merge($availibleUsers);
            }
            $zones = \VanguardLTE\TimeZone::all();
            $selectedZone = 8;
            return view('backend.user.add', compact('roles', 'statuses', 'shops', 'availibleUsers', 'zones', 'selectedZone'));
        }

        public function cashier_create()
        {
            if(!auth()->user()->hasRole('manager'))
            {
                return redirect()->route('backend.user.cashier_create')->withErrors(['Invalid permission']);
            }
            $shop_id = auth()->user()->shop_id;
            $users = \VanguardLTE\ShopUser::where('shop_id', $shop_id)->pluck('user_id');
            $cashiers = [];
            if($users)
            {
                $cashiers = \VanguardLTE\User::whereIn('id', $users)->whereIn('role_id', [2])->get();
            }
            $user = auth()->user();
            return view('backend.user.add_cashier', compact('cashiers', 'user'));
        }

        public function cashier_edit(\Illuminate\Http\Request $request, \VanguardLTE\User $user)
        {
            if(!auth()->user()->hasRole('manager') || $user->parent_id != auth()->user()->id)
            {
                return redirect()->route('backend.user.cashier_create')->withErrors(['Invalid permission']);
            }
            $shop_id = auth()->user()->shop_id;
            $users = \VanguardLTE\ShopUser::where('shop_id', $shop_id)->pluck('user_id');
            $cashiers = [];
            if($users)
            {
                $cashiers = \VanguardLTE\User::whereIn('id', $users)->whereIn('role_id', [2])->get();
            }
            return view('backend.user.edit_cashier', compact('cashiers', 'user'));
        }

        public function cashier_update(\Illuminate\Http\Request $request, \VanguardLTE\User $user)
        {
            $request_data = $request->all()['Users'];
            if($request_data['password'] != '' || $request_data['password_confirmation'] != '')
            {
                $validator = \Illuminate\Support\Facades\Validator::make($request_data, [
                    'password' => 'required|confirmed|min:6',
                ]);
                if( $validator->fails() )
                {
                    return redirect()->route('backend.user.create')->withErrors($validator)->withInput();
                }
            }
            $user->update(['password' => $request_data['password']]);
            return redirect('/user/cashier_edit/'.$user->id)->withSuccess(trans('app.settings_updated'));
        }

        public function player_table(\Illuminate\Http\Request $request)
        {
            $currentPage = $request->page_num;
            // Set the paginator to the current page
            Paginator::currentPageResolver(function() use ($currentPage) {
                return $currentPage;
            });
            $data = $request->all();
            $search = '';
            if(isset($data['search']))
                $search = $data['search'];
            $shop_id = auth()->user()->shop_id;
            $users = \VanguardLTE\ShopUser::where('shop_id', $shop_id)->pluck('user_id');
            $players = [];
            if($users)
            {
                if($search == '')
                    $players = \VanguardLTE\User::whereIn('id', $users)->whereIn('role_id', [1])->orderBy('created_at','desc')->paginate(20);
                else
                    $players = \VanguardLTE\User::whereIn('id', $users)->whereIn('role_id', [1])->where('first_name', 'like', '%'.$search.'%' )->orderBy('created_at','desc')->paginate(20);
            }
            $redis = app()->make('redis');
            $player_key = "player_time_";

            foreach($players as $player)
            {
                $player_id = $player->id;
                $player_val = json_decode($redis->get($player_key . $player_id));
                if($player_val != null && $player_val->time >= time() - 10)
                {
                    $player->status = "online";
                }
                else
                {
                    $player->status = "offline";
                }
            }
            $user = auth()->user();
            return view('backend.user.add_player_table', compact('players', 'user', 'search'));
        }

        public function player_create(\Illuminate\Http\Request $request)
        {
            $data = $request->all();
            $search = '';
            if(isset($data['search']))
                $search = $data['search'];
            // $shop_id = auth()->user()->shop_id;
            // $users = \VanguardLTE\ShopUser::where('shop_id', $shop_id)->pluck('user_id');
            // $players = [];
            // if($users)
            // {
            //     if($search == '')
            //         $players = \VanguardLTE\User::whereIn('id', $users)->whereIn('role_id', [1])->paginate(20);
            //     else
            //         $players = \VanguardLTE\User::whereIn('id', $users)->whereIn('role_id', [1])->where('first_name', 'like', '%'.$search.'%' )->paginate(20);
            // }
            // $redis = app()->make('redis');
            // $player_key = "player_time_";

            // foreach($players as $player)
            // {
            //     $player_id = $player->id;
            //     $player_val = json_decode($redis->get($player_key . $player_id));
            //     if($player_val != null && $player_val->time >= time() - 10)
            //     {
            //         $player->status = "online";
            //     }
            //     else
            //     {
            //         $player->status = "offline";
            //     }
            // }
            $user = auth()->user();
            return view('backend.user.add_player', compact('user', 'search'));
        }

        public function player_store(\Illuminate\Http\Request $request)
        {
            //create player
            $request_data = $request->all()['Accounts'];
            $username = $request_data['username'];
            $password = $request_data['password'];

            $validator = \Illuminate\Support\Facades\Validator::make($request_data, [
                'name' => 'required|min:4',
                'username' => 'required|regex:/^[A-Za-z0-9-]+$/|unique:users,username|min:6|max:16',
                'password' => 'required|min:6|max:16',
                'balance' => 'required|integer|min:0|max:9999999'
            ]);
            if( $validator->fails() )
            {
                return redirect()->route('backend.user.player_create')->withErrors($validator)->withInput();
            }

            $request_balance = 0;
            if(isset($request_data['balance']))
                $request_balance = floatval($request_data['balance']);
            if( $request_balance > 0 )
            {
                $shop = \VanguardLTE\Shop::find(auth()->user()->shop_id);

                if( $shop->balance < $request_balance )
                {
                    return redirect()->back()->withErrors([trans('app.not_enough_money_in_the_shop', [
                        'name' => $shop->name,
                        'balance' => $shop->balance
                    ])]);
                }
            }

            //generate user id
            // $unique_id = $this->generateUserId();
            // $username = substr($unique_id, 0, 2) . '-' .substr($unique_id, 2, 2) . '-' .substr($unique_id, 4, 2) . '-' .substr($unique_id, 6, 2) . '-' .substr($unique_id, 8, 2);
            $data = [];
            $data['username'] = $username;
            $data['password'] = $password;
            $data['first_name'] = $request_data['name'];
            $data['email'] = '';
            $data['language'] = 'en';
            $data['status'] = 'Active';
            $data['shop_id'] = auth()->user()->shop_id;
            $data['is_blocked'] = 0;
            $data['balance'] = 0;
            $data['role_id'] = 1; //player role
            $data['parent_id'] = auth()->user()->id;
            $data['parents'] = auth()->user()->parents . '['.auth()->user()->id.']';

            $user = $this->users->create($data + ['status' => \VanguardLTE\Support\Enum\UserStatus::ACTIVE]);

            $role = \jeremykenedy\LaravelRoles\Models\Role::find($data['role_id']);
            $user->detachAllRoles();
            $user->attachRole($role);

            if($request_balance > 0)
            {
                $user->addBalance('add', $request_balance, 'Create account');
            }

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

            $route = 'backend.user.player_create';
            return redirect()->route($route)->withSuccess(trans('app.user_created'));
        }

        public function store(\Illuminate\Http\Request $request)
        {
            $request_data = $request->all()['Users'];
            if($request_data['type'] == 'cashier')
            {
                $request_data['username'] = auth()->user()->username .'_'. $request_data['username'];
            }
            $validator = \Illuminate\Support\Facades\Validator::make($request_data, [
                'type' => 'required',
                'username' => 'required|regex:/^[_A-Za-z0-9-]+$/|unique:users,username|min:6',
                'password' => 'required|confirmed|min:6|max:16',
                'balance' => 'required|numeric',
            ]);
            if( $validator->fails() )
            {
                $error = $validator->errors()->first();
                return json_encode(['status' => 'fail', 'message' => $error]);
            }

            $role = $request_data['type'];
            if($role == '')
                $role = 'agent';
            $data = [];
            if($role == 'agent')
            {
                if(!auth()->user()->hasRole('admin') && $request_data['balance'] > auth()->user()->balance)
                {
                    return json_encode(['status' => 'fail', 'message' => 'Not Enough Balance']);
                }
                //create agent account
                $data['username'] = $request_data['username'];
                $data['email'] = $request_data['email'] == null ? '' : $request_data['email'] == null;
                $data['language'] = 'en';
                $data['status'] = 'Active';
                $data['shop_id'] = 0;
                $data['is_blocked'] = $request_data['status'] == 'disabled' ? 1 : 0;
                $data['password'] = $request_data['password'];
                $data['password_confirmation'] = $request_data['password_confirmation'];
                $data['balance'] = 0;
                $data['role_id'] = 5;
            }
            else if($role == 'manager')
            {
                if(!auth()->user()->hasRole('admin') && $request_data['balance'] > auth()->user()->balance)
                {
                    return json_encode(['status' => 'fail', 'message' => 'Not Enough Balance']);
                }

                //create a shop
                $data['name'] = $request_data['username'];
                $data['frontend'] = 'Default';
                $data['currency'] = 'USD';
                $data['is_blocked'] = $request_data['status'] == 'disabled' ? 1 : 0;
                $data['orderby'] = 'RTP';
                $data['shop_limit'] = isset($request_data['low_balance_limit']['limit']) ? $request_data['low_balance_limit']['limit'] : 2000;
                $data['max_win'] = 2000;
                $data['balance'] = 0;
                $data['rtp_type'] = $request_data['rtp_type'];

                $shop = \VanguardLTE\Shop::create($data);

                //set category game
                if( isset($request_data['categories']) && count($request_data['categories']) )
                {
                    foreach( $request_data['categories'] as $category )
                    {
                        \VanguardLTE\ShopCategory::create([
                            'shop_id' => $shop->id,
                            'category_id' => $category
                        ]);
                    }
                }
                else
                {
                    //set category for all games
                    \VanguardLTE\ShopCategory::create([
                        'shop_id' => $shop->id,
                        'category_id' => 0
                    ]);
                }

                \VanguardLTE\ShopUser::create([
                    'shop_id' => $shop->id,
                    'user_id' => auth()->user()->id
                ]);

                \VanguardLTE\Task::create([
                    'category' => 'shop',
                    'action' => 'create',
                    'item_id' => $shop->id,
                    'shop_id' => auth()->user()->shop_id
                ]);
                \VanguardLTE\GameBank::create([
                    'shop_id'=>$shop->id,
                    'fish_skill' => 300
                ]);
                $g_table = (new \VanguardLTE\Game)->getTable();
                \DB::statement('insert '.\DB::getTablePrefix().$g_table.'(name,title, shop_id, jpg_id,label, device,gamebank, rezerv,cask,advanced,bet, scaleMode, slotViewState, view,denomination,category_temp, original_id, bids,stat_in, stat_out) (select name, title, '.$shop->id.' as shop_id, jpg_id, label, device,gamebank, rezerv,cask,advanced,bet, scaleMode, slotViewState, view,denomination,category_temp, original_id, bids,stat_in, stat_out from '.\DB::getTablePrefix().$g_table.' where shop_id=0)');
                $jpg_table = (new \VanguardLTE\JPG)->getTable();
                \DB::statement('insert '.\DB::getTablePrefix().$jpg_table.'(name,balance,start_balance,pay_sum,percent,shop_id,start_payout, end_payout, fake_cnt) (select name,balance,start_balance,pay_sum,percent, '.$shop->id.' as shop_id,start_payout, end_payout, fake_cnt from '.\DB::getTablePrefix().$jpg_table.' where shop_id=0)');

                //create shop manager
                $data = [];
                $data['username'] = $request_data['username'];
                $data['email'] = $request_data['email'] == null ? '' : $request_data['email'] == null;
                $data['language'] = 'en';
                $data['status'] = 'Active';
                $data['shop_id'] = $shop->id;
                $data['is_blocked'] = $request_data['status'] == 'disabled' ? 1 : 0;
                $data['password'] = $request_data['password'];
                $data['password_confirmation'] = $request_data['password_confirmation'];
                $data['balance'] = 0; //shop manager's balance is saved in the shop, his own balance is 0
                $data['role_id'] = 3; //shop manager role
            }
            else if($role == 'cashier')
            {
                //create cashier
                $data = [];
                $data['username'] = $request_data['username'];
                $data['email'] = '';
                $data['language'] = 'en';
                $data['status'] = 'Active';
                $data['shop_id'] = auth()->user()->shop_id;
                $data['is_blocked'] = 0;
                $data['password'] = $request_data['password'];
                $data['password_confirmation'] = $request_data['password_confirmation'];
                $data['balance'] = 0;
                $data['role_id'] = 2; //cashier role
            }

            $data['parent_id'] = auth()->user()->id;
            $data['parents'] = auth()->user()->parents . '['.auth()->user()->id.']';
            if(isset($request_data['time_zone']))
                $data['timezone'] = $request_data['time_zone'];
            else
            {
                //when creating cashier, there is no timezone, put creator's timezone
                $data['timezone'] = auth()->user()->timezone;
            }

            $new_user = $this->users->create($data + ['status' => \VanguardLTE\Support\Enum\UserStatus::ACTIVE]);

            $new_user_role = \jeremykenedy\LaravelRoles\Models\Role::find($data['role_id']);
            $new_user->detachAllRoles();
            $new_user->attachRole($new_user_role);

            if($data['role_id'] != 3)
            {
                //add user balance if new created user is not shop manager
                if($request_data['balance'] > 0)
                {
                    $new_user->addBalance('add', $request_data['balance'], 'Create account');
                }

                if($data['role_id'] == 2 || $data['role_id'] == 1)
                {
                    //set as shop user when cashier or player
                    \VanguardLTE\ShopUser::create([
                        'shop_id' => $new_user->shop_id,
                        'user_id' => $new_user->id
                    ]);
                }
            }
            else
            {
                //set new user's shop id when shop creation
                \VanguardLTE\ShopUser::create([
                    'shop_id' => $new_user->id,
                    'user_id' => $new_user->id
                ]);
                $shop->update(['user_id' => $new_user->id]);

                //add shop balance
                if($request_data['balance'] > 0)
                {
                    $sum =  $request_data['balance'];
                    $user = auth()->user();

                    $last_payeer_balance = 0;
                    $result_payeer_balance = 0;
                    if(!auth()->user()->hasRole('admin'))
                    {
                        $last_payeer_balance = $user->balance;
                        $user->update([
                            'balance' => $user->balance - $sum,
                            'count_balance' => $user->count_balance - $sum
                        ]);
                        $result_payeer_balance = $last_payeer_balance - $sum;
                    }
                    \VanguardLTE\Statistic::create([
                        'payeer_id' => auth()->user()->id,
                        'user_id' => $new_user->id,
                        'shop_id' => $shop->id,
                        'last_balance' => $shop->balance,
                        'result_balance' => $shop->balance + $sum,
                        'description' => 'Create account',
                        'last_payeer_balance' => $last_payeer_balance,
                        'result_payeer_balance' => $result_payeer_balance,
                        'type' => 'add',
                        'sum' => abs($sum),
                        'system' => 'shop'
                    ]);

                    //in shop creation, don't use user::add_balance
                    $shop->update(['balance' => $shop->balance + $sum]);
                }
            }

            if( !$new_user->shop_id && $new_user->hasRole([
                'manager',
                'cashier',
                'user'
            ]) )
            {
                $shops = $new_user->shops(true);
                if( count($shops) )
                {
                    $shop_id = $shops->first();
                    $new_user->update(['shop_id' => $shop_id]);
                }
            }

            //add contact info
            if($role == 'agent' || $role == 'manager')
            {
                \VanguardLTE\Contact::create([
                    'country' => $request_data['country'],
                    'city' => $request_data['city'],
                    'name' => $request_data['name'],
                    'email' => $request_data['email'],
                    'phone' => $request_data['phone'],
                    'finance' => 0,
                    'user_id' => $new_user->id
                ]);
            }
            $route = 'backend.user.list';
            if($role == 'cashier')
                $route = 'backend.user.cashier_create';

            return json_encode(['status' => 'success', 'message' => 'User Created Successfully']);
        }

        function generateUserId() {
            $number = mt_rand(1000000000, 9999999999); // better than rand()

            // call the same function if the barcode exists already
            if ($this->userIdExists($number)) {
                return $this->generateUserId();
            }

            // otherwise, it's valid and can be used
            return $number;
        }

        function userIdExists($number) {
            // query the database and return a boolean
            // for instance, it might look like this in Laravel
            $users = \VanguardLTE\User::where(['username' => $number])->get();
            return count($users) > 0;
        }

        public function massadd(\Illuminate\Http\Request $request)
        {
            $shop = \VanguardLTE\Shop::find(auth()->user()->shop_id);
            $count = \VanguardLTE\User::where([
                'shop_id' => auth()->user()->shop_id,
                'role_id' => 1
            ])->count();
            if( isset($request->count) && is_numeric($request->count) && isset($request->balance) && is_numeric($request->balance) )
            {

                if( $this->max_users < ($count + $request->count) )
                {
                    return redirect()->route('backend.user.list')->withErrors([trans('max_users', ['max' => $this->max_users])]);
                }
                if( $request->balance > 0 )
                {
                    if( $shop->balance < ($request->count * $request->balance) )
                    {
                        return redirect()->back()->withErrors([trans('app.not_enough_money_in_the_shop', [
                            'name' => $shop->name,
                            'balance' => $shop->balance
                        ])]);
                    }
                    $open_shift = \VanguardLTE\OpenShift::where([
                        'shop_id' => auth()->user()->shop_id,
                        'user_id' => auth()->user()->id,
                        'end_date' => null
                    ])->first();
                    if( !$open_shift )
                    {
                        return redirect()->back()->withErrors([trans('app.shift_not_opened')]);
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
                    auth()->user()->hierarchyUsers(false, true);
                }
            }
            return redirect()->route('backend.user.list')->withSuccess(trans('app.user_created'));
        }
        public function edit(\Illuminate\Http\Request $request, \VanguardLTE\Repositories\Activity\ActivityRepository $activitiesRepo, \VanguardLTE\User $user)
        {
            $edit = true;
            $roles = \jeremykenedy\LaravelRoles\Models\Role::where('level', '<=', auth()->user()->level())->pluck('name', 'id');
            $statuses = \VanguardLTE\Support\Enum\UserStatus::lists();
            $shops = $user->shops();
            $shop = \VanguardLTE\Shop::find(auth()->user()->shop_id);
            $userActivities = \VanguardLTE\Services\Logging\UserActivity\Activity::where([
                'user_id' => $user->id,
                'type' => 'user'
            ])->orderBy('created_at', 'DESC')->paginate(30)->withQueryString();
            $users = auth()->user()->availableUsers();
            if( count($users) && !in_array($user->id, $users) )
            {
                abort(404);
            }
            if( auth()->user()->role_id < $user->role_id )
            {
                return redirect()->route('backend.user.list');
            }
            $hasActivities = $this->hasActivities($user);
            $langs = [];
            foreach( glob(resource_path() . '/lang/*', GLOB_ONLYDIR) as $fileinfo )
            {
                $dirname = basename($fileinfo);
                $langs[$dirname] = $dirname;
            }

            //get parents
            $parents = explode("][", $user->parents);
            $selector = '';
            for($i = 0; $i < count($parents); $i++)
            {
                $parent_id = str_replace(["[","]"], ["",""],$parents[$i]);
                if($parent_id >= auth()->user()->id)
                {
                    $parent = \VanguardLTE\User::where('id', $parent_id)->get();
                    if(count($parent) > 0)
                    {
                    if($selector == '')
                        $selector = $parent[0]->username;
                    else
                        $selector .= ' -> ' . $parent[0]->username;
                    }
                }
            }

            $contact = \VanguardLTE\Contact::where('user_id', $user->id)->get();
            $contact_info = [];
            if(count($contact) > 0)
            {
                $contact_info = $contact[0];
            }
            else
            {
                $contact_info['country'] = '';
                $contact_info['city'] = '';
                $contact_info['name'] = '';
                $contact_info['email'] = '';
                $contact_info['phone'] = '';
                $contact_info['percent'] = '0.00';
            }
            $zones = \VanguardLTE\TimeZone::all();
            $selectedZone = $user->timezone;
            return view('backend.user.edit', compact('edit', 'user', 'roles', 'statuses', 'shops', 'userActivities', 'hasActivities', 'langs', 'selector', 'contact_info', 'zones', 'selectedZone'));
        }

        public function update(\VanguardLTE\User $user, \VanguardLTE\Http\Requests\User\UpdateDetailsRequest $request, \VanguardLTE\Repositories\Session\SessionRepository $sessionRepository)
        {
            $req_data = $request->all()['Users'];
            $data = [];
            $data['password'] = $req_data['password'];
            if( empty($req_data['password']) || empty($req_data['password_confirmation']) )
            {
                unset($data['password']);
            }
            else
            {
                if(strcmp($req_data['password'], $req_data['password_confirmation']) != 0)
                {
                    redirect()->route('backend.user.edit', $user->id)->withErrors('Password confirmation does not match');
                }
            }
            $data['timezone'] = $req_data['time_zone'];
            $data['is_blocked'] = $req_data['status'] == 'enabled' ? 0 : 1;
            $data['percent'] = $req_data['rtp'];
            $user->update($data);

            $contact_data = [
                'country' => $req_data['country'],
                'city' => $req_data['city'],
                'name' => $req_data['name'],
                'email' => $req_data['email'],
                'phone' => $req_data['phone'],
                'percent' => $req_data['percent'],
                'user_id' => $user->id
            ];
            $contact = \VanguardLTE\Contact::where('user_id', $user->id)->get();
            if(count($contact) > 0)
            {
                $contact[0]->update($contact_data);
            }
            else
            {
                \VanguardLTE\Contact::create($contact_data);
            }
            return redirect()->back()->withSuccess('User info updated successfully');
        }

        public function updateDetails(\VanguardLTE\User $user, \VanguardLTE\Http\Requests\User\UpdateDetailsRequest $request, \VanguardLTE\Repositories\Session\SessionRepository $sessionRepository)
        {
            $users = auth()->user()->availableUsers();

            if( count($users) && !in_array($user->id, $users) )
            {
                abort(404);
            }
            if( auth()->user()->role_id < $user->role_id )
            {
                return redirect()->route('backend.user.list');
            }
            $data = $request->only([
                'email',
                'username',
                'language',
                'shop_id',
                'status',
                'is_blocked',
                'password',
                'password_confirmation'
            ]);

            $validator = \Illuminate\Support\Facades\Validator::make($data, [
                'username' => 'required|unique:users,username,' . $user->id,
                'email' => 'nullable|unique:users,email,' . $user->id,
                'phone' => 'nullable|unique:users,phone,' . $user->id,
            ]);
            if( $validator->fails() )
            {
                return redirect()->route('backend.user.edit', $user->id)->withErrors($validator)->withInput();
            }
            $count = \VanguardLTE\User::where([
                'shop_id' => auth()->user()->shop_id,
                'role_id' => 1
            ])->count();
            if( empty($data['password']) || empty($data['password_confirmation']) )
            {
                unset($data['password']);
                unset($data['password_confirmation']);
            }
            if( !(auth()->user()->hasRole('admin') && $user->hasRole([
                'agent',
                'distributor'
            ])) )
            {
                unset($data['is_blocked']);
            }
            else if( isset($data['is_blocked']) )
            {
                $users = \VanguardLTE\User::whereIn('id', [$user->id] + $user->hierarchyUsers())->get();
                if( $users )
                {
                    foreach( $users as $userElem )
                    {
                        \DB::table('sessions')->where('user_id', $userElem->id)->delete();
                        $userElem->update([
                            'remember_token' => null,
                            'is_blocked' => $data['is_blocked']
                        ]);
                    }
                }
                $myShops = \VanguardLTE\Shop::whereIn('id', $user->availableShops())->get();
                if( $myShops )
                {
                    foreach( $myShops as $myShop )
                    {
                        $myShop->update(['is_blocked' => $data['is_blocked']]);
                    }
                }
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
                if( $request->status == \VanguardLTE\Support\Enum\UserStatus::BANNED )
                {
                    event(new \VanguardLTE\Events\User\Banned($user));
                }
            }
            if( isset($data['email']) && !$user->hasRole('admin') && ($return = \VanguardLTE\Lib\Filter::domain_filtered($data['email'])) )
            {
                return redirect()->route('backend.user.edit', $user->id)->withErrors([__('app.blocked_domain_zone', ['zone' => $return['domain']])]);
            }
            if( isset($request->phone) && $request->phone )
            {
                $phone = preg_replace('/[^0-9]/', '', $request->phone);
                $code = null;
                if( $phone != '' && !$user->phone )
                {
                    $code = rand(1111, 9999);
                    $data['phone'] = $phone;
                }
                if( $user->phone && $user->phone != $phone && !$user->phone_verified )
                {
                    $code = rand(1111, 9999);
                    $data['phone'] = $phone;
                }
                if( $user->phone_verified && auth()->user()->hasRole('admin') && $user->phone != $phone )
                {
                    $code = rand(1111, 9999);
                    $data['phone'] = $phone;
                    $data['phone_verified'] = 0;
                }
                if( $code )
                {
                    $sender = \VanguardLTE\Lib\SMS_sender::send($phone, 'Verification code: ' . $code, $user->id);
                    $this->users->update($user->id, [
                        'sms_token' => $code,
                        'sms_token_date' => \Carbon\Carbon::now()->addMinutes(settings('smsto_time'))
                    ]);
                    if( isset($sender['message_id']) )
                    {
                        \VanguardLTE\SMS::create([
                            'user_id' => $user->id,
                            'message' => $code,
                            'message_id' => $sender['message_id'],
                            'shop_id' => $user->shop_id,
                            'type' => 'verification',
                            'status' => 'Sent'
                        ]);
                    }
                }
            }
            else
            {
                $data['phone'] = '';
                $data['phone_verified'] = 0;
                $data['sms_token'] = null;
            }
            $this->users->update($user->id, $data);
            if( $user->hasRole([
                'distributor',
                'cashier',
                'user'
            ]) && $request->shops && count($request->shops) )
            {
                foreach( $request->shops as $shop )
                {
                    \VanguardLTE\ShopUser::create([
                        'shop_id' => $shop,
                        'user_id' => $user->id
                    ]);
                }
            }
            if( $request->sms_token )
            {
                if( $request->sms_token == $user->sms_token )
                {
                    $now = \Carbon\Carbon::now();
                    $seconds = $now->diffInSeconds(\Carbon\Carbon::parse($user->sms_token_date), false);
                    if( $seconds <= 0 )
                    {
                        return redirect()->route('backend.user.edit', $user->id)->withErrors(trans('app.time_is_up'));
                    }
                    $user->update([
                        'sms_token' => null,
                        'phone_verified' => 1
                    ]);
                    return redirect()->route('backend.user.edit', $user->id)->withSuccess(trans('app.phone_verified'));
                }
                else
                {
                    return redirect()->route('backend.user.edit', $user->id)->withErrors(trans('app.phone_verification_code_is_wrong'));
                }
            }
            event(new \VanguardLTE\Events\User\UpdatedByAdmin($user));
            if( $this->userIsBanned($user, $request) )
            {
                event(new \VanguardLTE\Events\User\Banned($user));
            }
            return redirect()->route('backend.user.edit', $user->id)->withSuccess(trans('app.user_updated'));
        }

        public function send_phone_code()
        {
            $code = rand(11111, 99999);
            $sender = \VanguardLTE\Lib\SMS_sender::send(auth()->user()->phone, 'Verification code: ' . $code, auth()->user()->id);
            if( isset($sender['error']) )
            {
                if( isset($sender['text']) )
                {
                    return redirect()->back()->withErrors($sender['text']);
                }
                return redirect()->back()->withErrors('Error sending message');
            }
            if( !isset($sender['success']) )
            {
                return redirect()->back()->withErrors(__('app.something_went_wrong'));
            }
            if( !$sender['success'] )
            {
                return redirect()->back()->withErrors($sender['message']);
            }
            \VanguardLTE\SMS::create([
                'user_id' => auth()->user()->id,
                'message' => $code,
                'message_id' => $sender['message_id'],
                'shop_id' => auth()->user()->shop_id,
                'type' => 'verification',
                'status' => 'Sent'
            ]);
            auth()->user()->update([
                'sms_token' => $code,
                'sms_token_date' => \Carbon\Carbon::now()->addMinutes(settings('smsto_time'))
            ]);
            return redirect()->back()->withSuccess('Code sent');
        }

        public function toggle(\VanguardLTE\User $user, \Illuminate\Http\Request $request)
        {
            $is_blocked = $user->is_blocked;
            $is_blocked = $is_blocked > 0 ? 0 : 1;
            $user->update(['is_blocked' => $is_blocked]);
            $children = \VanguardLTE\User::where('parents', 'like', '%['.$user->id.']%')->get();
            foreach($children as $child)
            {
                $sessionRepository->invalidateAllSessionsForUser($child->id);
            }
            $sessionRepository->invalidateAllSessionsForUser($user->id);
            return $is_blocked == 1 ? 'disabled' : 'enabled';
        }

        public function kickout(\VanguardLTE\User $user, \VanguardLTE\Repositories\Session\SessionRepository $sessionRepository)
        {
            if( settings('reset_authentication') && $user->hasRole('user') && count($sessionRepository->getUserSessions($user->id)) )
            {
                foreach( $sessionRepository->getUserSessions($user->id) as $session )
                {
                    $sessionRepository->invalidateSession($session->id);
                }
            }
            return "success";
        }

        public function isconnected()
        {
            $user = auth()->user();
            $res = [];
            if($user == null)
            {
                $res = ['status' => 'logout'];
            }
            else
            {
                $res = ['status' => 'connected'];
            }
            return json_encode($res);
        }

        public function updateBalance(\Illuminate\Http\Request $request)
        {
            $data = $request->all()['DepositeForm'];

            $amount = $data['amount'];
            $userid = $data['id'];

            //check player active
            $player_key = "player_time_";
            $redis = app()->make('redis');
            $player_val = json_decode($redis->get($player_key . $userid));
            if($player_val != null && $player_val->time >= time() - 10)
            {
                return redirect()->back()->withErrors("Player must be logged out");
            }

            if(!isset($data['type']))
            {
                if($amount > 0)
                {
                    $data['type'] = 'add';
                }
                else
                {
                    $data['type'] = 'out';
                    $amount = -$amount;
                }
            }

            $user = \VanguardLTE\User::find($userid);
            if( !$user )
            {
                return redirect()->back()->withErrors([__('app.wrong_user')]);
            }

            $request->summ = floatval($amount);

            $result = $user->addBalance($data['type'], $request->summ, $data['type'] == 'add' ? 'Deposit' : 'Reedem');
            $result = json_decode($result, true);
            if( $data['type'] == 'add' )
            {
                event(new \VanguardLTE\Events\User\MoneyIn($user, $request->summ));
            }
            else
            {
                event(new \VanguardLTE\Events\User\MoneyOut($user, $request->summ));
            }
            if( $result['status'] == 'error' )
            {
                return redirect()->back()->withErrors([$result['message']]);
            }
            return redirect()->back()->withSuccess($result['message']);
        }

        public function updateLimit(\Illuminate\Http\Request $request)
        {

            $data = $request->all();
            if( !array_get($data, 'type') )
            {
                $data['type'] = 'add';
            }

            $user = \VanguardLTE\User::find($request->user_id);

            if( !$user )
            {
                return redirect()->back()->withErrors([__('app.wrong_user')]);
            }
            $request->summ = floatval($request->summ);
            if( $request->all && $request->all == '1' )
            {
                $request->summ = $user->balance;
            }
            $result = $user->addLimit($data['type'], $request->summ);
            $result = json_decode($result, true);
            if( $result['status'] == 'error' )
            {
                return redirect()->back()->withErrors([$result['message']]);
            }
            return redirect()->back()->withSuccess($result['message']);
        }

        // public function statistics(\VanguardLTE\User $user, \Illuminate\Http\Request $request)
        // {
        //     $statistics = \VanguardLTE\Statistic::where('user_id', $user->id)->orderBy('created_at', 'DESC')->paginate(20)->withQueryString();
        //     return view('backend.stat.pay_stat', compact('user', 'statistics'));
        // }
        private function userIsBanned(\VanguardLTE\User $user, \Illuminate\Http\Request $request)
        {
            return $user->status != $request->status && $request->status == \VanguardLTE\Support\Enum\UserStatus::BANNED;
        }
        public function specauth(\Illuminate\Http\Request $request, \VanguardLTE\User $user)
        {
            if( !$user )
            {
                return redirect()->route('backend.auth.login')->withErrors([trans('app.wrong_user')]);
            }
            if( $user->auth_token == $request->token && auth()->user()->hasRole('admin') && !$user->hasRole('admin') )
            {
                if( auth()->user()->shop && auth()->user()->shop->pending )
                {
                    return redirect()->route('backend.dashboard')->withErrors(__('app.shop_is_creating'));
                }
                session(['beforeUser' => auth()->user()->id]);
                \Illuminate\Support\Facades\Auth::loginUsingId($user->id);
                if( !$user->hasRole('user') )
                {
                    if( !auth()->user()->hasPermission('dashboard') )
                    {
                        return redirect()->route('backend.user.list');
                    }
                    return redirect()->route('backend.dashboard');
                }
                return redirect()->intended();
            }
            return redirect()->route('backend.auth.login')->withErrors([trans('app.wrong_user')]);
        }
        public function back_login(\Illuminate\Http\Request $request)
        {
            if( $request->session()->exists('beforeUser') )
            {
                \Illuminate\Support\Facades\Auth::loginUsingId(session('beforeUser'));
                $request->session()->forget('beforeUser');
                return redirect()->route('backend.dashboard');
            }
            return redirect()->route('backend.dashboard')->withErrors([trans('app.wrong_user')]);
        }
        public function updateAvatar(\VanguardLTE\User $user, \VanguardLTE\Services\Upload\UserAvatarManager $avatarManager, \Illuminate\Http\Request $request)
        {
            $this->validate($request, ['avatar' => 'image']);
            $name = $avatarManager->uploadAndCropAvatar($user, $request->file('avatar'), $request->get('points'));
            if( $name )
            {
                $this->users->update($user->id, ['avatar' => $name]);
                event(new \VanguardLTE\Events\User\UpdatedByAdmin($user));
                return redirect()->route('backend.user.edit', $user->id)->withSuccess(trans('app.avatar_changed'));
            }
            return redirect()->route('backend.user.edit', $user->id)->withErrors(trans('app.avatar_not_changed'));
        }
        public function updateAvatarExternal(\VanguardLTE\User $user, \Illuminate\Http\Request $request, \VanguardLTE\Services\Upload\UserAvatarManager $avatarManager)
        {
            $avatarManager->deleteAvatarIfUploaded($user);
            $this->users->update($user->id, ['avatar' => $request->get('url')]);
            event(new \VanguardLTE\Events\User\UpdatedByAdmin($user));
            return redirect()->route('backend.user.edit', $user->id)->withSuccess(trans('app.avatar_changed'));
        }
        public function updateLoginDetails(\VanguardLTE\User $user, \VanguardLTE\Http\Requests\User\UpdateLoginDetailsRequest $request, \VanguardLTE\Repositories\Session\SessionRepository $sessionRepository)
        {
            $data = $request->all();
            if( trim($data['password']) == '' )
            {
                unset($data['password']);
                unset($data['password_confirmation']);
            }
            if( !(auth()->user()->hasRole('admin') && $user->hasRole([
                'agent',
                'distributor'
            ])) )
            {
                unset($data['is_blocked']);
            }
            else
            {
                $users = \VanguardLTE\User::whereIn('id', [$user->id] + $user->hierarchyUsers())->get();
                if( $users )
                {
                    foreach( $users as $userElem )
                    {
                        \DB::table('sessions')->where('user_id', $userElem->id)->delete();
                        $userElem->update([
                            'remember_token' => null,
                            'is_blocked' => 1
                        ]);
                    }
                }
            }
            $this->users->update($user->id, $data);
            event(new \VanguardLTE\Events\User\UpdatedByAdmin($user));
            return redirect()->route('backend.user.edit', $user->id)->withSuccess(trans('app.login_updated'));
        }
        public function delete(\VanguardLTE\User $user)
        {
            if( $user->id == auth()->user()->id )
            {
                return redirect()->route('backend.user.list')->withErrors(trans('app.you_cannot_delete_yourself'));
            }
            if( !auth()->user()->hasRole('admin') )
            {
                abort(403);
            }

            $managers = $user->availableUsersByRole('manager');

            $shops = \VanguardLTE\Shop::whereIn('user_id', $managers)->get();
            foreach($shops as $shop)
            {
                $shop->delete();
            }
            $user->delete();
            $users = \VanguardLTE\User::where('parents', 'like', '%['.$user->id.']%')->get();
            foreach($users as $user)
            {
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
            }

            return redirect()->route('backend.user.list')->withSuccess(trans('app.user_deleted'));
        }
        public function hard_delete(\VanguardLTE\User $user)
        {
            if( $user->id == auth()->user()->id )
            {
                return redirect()->route('backend.user.list')->withErrors(trans('app.you_cannot_delete_yourself'));
            }
            if( !(auth()->user()->hasRole('admin') && $user->hasRole([
                'agent',
                'distributor'
            ])) )
            {
                abort(403);
            }
            if( $user->hasRole('agent') )
            {
                $distributors = \VanguardLTE\User::where([
                    'parent_id' => $user->id,
                    'role_id' => 4
                ])->get();
            }
            if( $user->hasRole('distributor') )
            {
                $distributors = \VanguardLTE\User::where(['id' => $user->id])->get();
            }
            if( $distributors )
            {
                foreach( $distributors as $distributor )
                {
                    if( $distributor->rel_shops )
                    {
                        foreach( $distributor->rel_shops as $shop )
                        {
                            $shop->shop->delete();
                            \VanguardLTE\Task::create([
                                'category' => 'shop',
                                'action' => 'delete',
                                'item_id' => $shop->shop_id,
                                'user_id' => auth()->user()->id,
                                'shop_id' => auth()->user()->shop_id
                            ]);
                            $usersToDelete = \VanguardLTE\User::whereIn('role_id', [
                                1,
                                2,
                                3
                            ])->where('shop_id', $shop->shop_id)->get();
                            if( $usersToDelete )
                            {
                                foreach( $usersToDelete as $userDelete )
                                {
                                    $userDelete->delete();
                                }
                            }
                        }
                    }
                    $distributor->delete();
                }
            }
            if( $user->hasRole('agent') )
            {
                $user->delete();
            }
            if( auth()->user()->hasRole('admin') )
            {
                $admin = \VanguardLTE\User::find(auth()->user()->id);
                $admin->update(['shop_id' => 0]);
                \VanguardLTE\Jobs\UpdateTreeCache::dispatch($admin->hierarchyUsers());
            }
            return redirect()->route('backend.user.list')->withSuccess(trans('app.user_deleted'));
        }
        public function hasActivities($user)
        {
            if( $user->hasRole([
                'distributor',
                'manager',
                'cashier'
            ]) )
            {
                $stats = \VanguardLTE\Statistic::where('user_id', $user->id)->count();
                if( $stats )
                {
                    return true;
                }
                $stats = \VanguardLTE\StatGame::where('user_id', $user->id)->count();
                if( $stats )
                {
                    return true;
                }
                $open_shifts = \VanguardLTE\OpenShift::where('user_id', $user->id)->count();
                if( $open_shifts )
                {
                    return true;
                }
            }
            return false;
        }
        public function sessions(\VanguardLTE\User $user, \VanguardLTE\Repositories\Session\SessionRepository $sessionRepository)
        {
            $adminView = true;
            $sessions = $sessionRepository->getUserSessions($user->id);
            return view('backend.user.sessions', compact('sessions', 'user', 'adminView'));
        }
        public function invalidateSession(\VanguardLTE\User $user, $session, \VanguardLTE\Repositories\Session\SessionRepository $sessionRepository)
        {
            $sessionRepository->invalidateSession($session->id);
            return redirect()->route('backend.user.sessions', $user->id)->withSuccess(trans('app.session_invalidated'));
        }

        public function action($action)
        {
            if( !auth()->user()->hasRole('cashier') )
            {
                abort(403);
            }
            $open_shift = \VanguardLTE\OpenShift::where([
                'shop_id' => auth()->user()->shop_id,
                'user_id' => auth()->user()->id,
                'end_date' => null
            ])->first();
            if( !$open_shift )
            {
                return redirect()->back()->withErrors([trans('app.shift_not_opened')]);
            }
            $shop = \VanguardLTE\Shop::find(auth()->user()->shop_id);
            if( $action && in_array($action, ['users_out']) )
            {
                switch( $action )
                {
                    case 'users_out':
                        $users = \VanguardLTE\User::where('shop_id', $shop->id)->get();
                        foreach( $users as $user )
                        {
                            $sum = $user->balance;
                            if( $sum <= 0 )
                            {
                                continue;
                            }
                            $user->addBalance('out', $sum, $user->referral);
                        }
                        return redirect()->back()->withSuccess(trans('app.balance_updated'));
                        break;
                }
            }
        }

        /**
         *  player history, "Account History" of side menu
         */
        public function player_history(\Illuminate\Http\Request $request)
        {
            $data = [];
            if(isset($request->all()['DateFilterForm']))
            {
                $data = $request->all()['DateFilterForm'];
            }
            else
            {
                $data['DateFilterForm'] = [
                    'search' => '',
                ];
                if(isset($request['filter']))
                {
                    $filter = $request['filter'];
                    if($filter == 'today')
                    {
                        $data['dateFrom'] = date("m-d-Y");
                        $data['dateTill'] =  date("m-d-Y");
                    }
                    else if($filter == 'yesterday')
                    {
                        $data['dateFrom'] = date("m-d-Y", strtotime("-1 days"));
                        $data['dateTill'] =  date("m-d-Y", strtotime("-1 days"));
                    }
                    else if($filter == 'month')
                    {
                        $data['dateFrom'] = date("m-01-Y");
                        $data['dateTill'] =  date("m-d-Y");
                    }
                    else if($filter == 'week')
                    {
                        $data['dateFrom'] = date("m-d-Y", strtotime("monday this week"));
                        $data['dateTill'] =  date("m-d-Y");
                    }
                }
            }

            $search = '';
            if(isset($data['search']))
                $search = $data['search'];

            $dateFrom = date("m-d-Y", strtotime('-1 days'));
            $timeFrom = ' 00:00';
            if(isset($data['dateFrom']))
                $dateFrom = $data['dateFrom'];
            if(isset($data['timeFrom']))
                $timeFrom = ' ' . $data['timeFrom'];
            $dateFrom = $dateFrom . $timeFrom;
            $dateFrom = date_create_from_format('m-d-Y H:i', $dateFrom);

            $dateTill = date("m-d-Y");
            $timeTill = ' 23:59';
            if(isset($data['dateTill']))
                $dateTill = $data['dateTill'];
            if(isset($data['timeTill']))
                $timeTill = ' '  . $data['timeTill'];
            $dateTill = $dateTill . $timeTill;
            $dateTill = date_create_from_format('m-d-Y H:i', $dateTill);

            $sellerId = '';
            if(isset($data['sellerId']))
                $sellerId = $data['sellerId'];

            $user = auth()->user();

            $users = \VanguardLTE\User::where('shop_id', $user->shop_id)->pluck('id');
            $stats = [];
            $stats = \VanguardLTE\Statistic::where('statistics.shop_id', auth()->user()->shop_id)
                    ->join('users as A', 'statistics.user_id', '=', 'A.id')
                    ->join('users as B', 'statistics.payeer_id', '=', 'B.id')
                    ->whereBetween('statistics.created_at', [$dateFrom, $dateTill])->orderBy('statistics.created_at', 'DESC');

            if($search != '')
                $stats = $stats->where('A.username', 'like', '%'.$search.'%');
            if($sellerId != '')
                $stats = $stats->where('B.id', '=', $sellerId);

            $stats = $stats->select('statistics.*', 'A.username as account', 'A.first_name as name', 'B.username as cashier')->paginate(20);

            //get all cashiers
            $cashiers = \VanguardLTE\User::where('shop_id', $user->shop_id)->where('role_id', 2)->get();
            $dateFrom = $dateFrom->format('m-d-Y H:i');
            $dateTill = $dateTill->format('m-d-Y H:i');

            $DateFilterForm = [
                'search' => $search,
                'dateFrom' => $dateFrom,
                'timeFrom' => $timeFrom,
                'dateTill' => $dateTill,
                'timeTill' => $timeTill,
                'sellerId' => $sellerId,
            ];

            return view('backend.user.history', compact('stats', 'cashiers', 'DateFilterForm'));
        }

        /**
         * Game logs, "Reel logs" of account table
         */
        public function gamelogs(\Illuminate\Http\Request $request)
        {
            $data = [];
            if(isset($request->all()['DateFilterForm']))
            {
                $data = $request->all()['DateFilterForm'];
            }
            else
            {
                $data['DateFilterForm'] = [
                    'search' => '',
                ];
                if(isset($request['filter']))
                {
                    $filter = $request['filter'];
                    if($filter == 'today')
                    {
                        $data['dateFrom'] = date("m-d-Y");
                        $data['dateTill'] =  date("m-d-Y");
                    }
                    else if($filter == 'yesterday')
                    {
                        $data['dateFrom'] = date("m-d-Y", strtotime("-1 days"));
                        $data['dateTill'] =  date("m-d-Y", strtotime("-1 days"));
                    }
                    else if($filter == 'month')
                    {
                        $data['dateFrom'] = date("m-01-Y");
                        $data['dateTill'] =  date("m-d-Y");
                    }
                    else if($filter == 'week')
                    {
                        $data['dateFrom'] = date("m-d-Y", strtotime("monday this week"));
                        $data['dateTill'] =  date("m-d-Y");
                    }
                }
            }

            $search = '';
            if(isset($data['search']))
                $search = $data['search'];

            $dateFrom = date("m-d-Y", strtotime('-1 days'));
            $timeFrom = ' 00:00';
            if(isset($data['dateFrom']))
                $dateFrom = $data['dateFrom'];
            if(isset($data['timeFrom']))
                $timeFrom = ' ' . $data['timeFrom'];
            $dateFrom = $dateFrom . $timeFrom;
            $dateFrom = date_create_from_format('m-d-Y H:i', $dateFrom);


            $dateTill = date("m-d-Y");
            $timeTill = ' 23:59';
            if(isset($data['dateTill']))
                $dateTill = $data['dateTill'];
            if(isset($data['timeTill']))
                $timeTill = ' '  . $data['timeTill'];
            $dateTill = $dateTill . $timeTill;
            $dateTill = date_create_from_format('m-d-Y H:i', $dateTill);

            $game = '';
            if(isset($data['game']))
                $game = $data['game'];

            $user = auth()->user();

            $users = \VanguardLTE\User::where('shop_id', $user->shop_id)->pluck('id');
            $gamelogs = [];
            $gamelogs = \VanguardLTE\StatGame::where('stat_game.shop_id', auth()->user()->shop_id)
                    ->join('users as A', 'stat_game.user_id', '=', 'A.id')
                    ->whereBetween('stat_game.date_time', [$dateFrom, $dateTill])
                    ->orderBy('stat_game.date_time', 'desc');

            if($search != '')
                $gamelogs = $gamelogs->where('A.username', 'like', '%'.$search.'%');

            if($game != '')
                $gamelogs = $gamelogs->where('stat_game.game', '=', $game);

            $gamelogs = $gamelogs->select('stat_game.*', 'A.username as account', 'A.first_name as name')->paginate(20);

            $games = \VanguardLTE\Game::where('shop_id', '=', auth()->user()->shop_id)->get(['name']);

            $dateFrom = $dateFrom->format('m-d-Y H:i');
            $dateTill = $dateTill->format('m-d-Y H:i');

            $DateFilterForm = [
                'search' => $search,
                'dateFrom' => $dateFrom,
                'timeFrom' => $timeFrom,
                'dateTill' => $dateTill,
                'timeTill' => $timeTill,
                'game' => $game,
            ];

            return view('backend.user.gamelogs', compact('gamelogs', 'games', 'DateFilterForm'));
        }

        /**
         * Transactions, "Transaction History" of side menu
         */
        public function transactions(\Illuminate\Http\Request $request)
        {
            $data = [];
            if(isset($request->all()['DateFilterForm']))
            {
                $data = $request->all()['DateFilterForm'];
            }
            else
            {
                $data['DateFilterForm'] = [
                    'search' => '',
                ];
                if(isset($request['filter']))
                {
                    $filter = $request['filter'];
                    if($filter == 'today')
                    {
                        $data['dateFrom'] = date("m-d-Y");
                        $data['dateTill'] =  date("m-d-Y");
                    }
                    else if($filter == 'yesterday')
                    {
                        $data['dateFrom'] = date("m-d-Y", strtotime("-1 days"));
                        $data['dateTill'] =  date("m-d-Y", strtotime("-1 days"));
                    }
                    else if($filter == 'month')
                    {
                        $data['dateFrom'] = date("m-01-Y");
                        $data['dateTill'] =  date("m-d-Y");
                    }
                    else if($filter == 'week')
                    {
                        $data['dateFrom'] = date("m-d-Y", strtotime("monday this week"));
                        $data['dateTill'] =  date("m-d-Y");
                    }
                }
            }

            $dateFrom = date("m-d-Y", strtotime('-1 days'));
            $timeFrom = ' 00:00';
            if(isset($data['dateFrom']))
                $dateFrom = $data['dateFrom'];
            if(isset($data['timeFrom']))
                $timeFrom = ' ' . $data['timeFrom'];
            $dateFrom = $dateFrom . $timeFrom;
            $dateFrom = date_create_from_format('m-d-Y H:i', $dateFrom);


            $dateTill = date("m-d-Y");
            $timeTill = ' 23:59';
            if(isset($data['dateTill']))
                $dateTill = $data['dateTill'];
            if(isset($data['timeTill']))
                $timeTill = ' '  . $data['timeTill'];
            $dateTill = $dateTill . $timeTill;
            $dateTill = date_create_from_format('m-d-Y H:i', $dateTill);

            if(auth()->user()->hasRole('manager'))
            {
                $shop_id = auth()->user()->shop_id;
                $transactions = \VanguardLTE\Statistic::where('statistics.shop_id', '=', $shop_id)
                            ->join('users as A', 'statistics.user_id', '=', 'A.id')
                            ->join('users as B', 'statistics.payeer_id', '=', 'B.id')
                            ->whereBetween('statistics.created_at', [$dateFrom, $dateTill])
                            ->orderBy('statistics.created_at', 'DESC');
                            // ->where(function($q){
                            //     $q->where('A.id', '=', auth()->user()->id)
                            //     ->orWhere('B.id', '=', auth()->user()->id);
                            // });
            }
            else
            {
                $user_id = auth()->user()->id;
                $transactions = \VanguardLTE\Statistic::whereBetween('statistics.created_at', [$dateFrom, $dateTill])
                            // ->orWhere('statistics.payeer_id', '=', $user_id)
                            ->join('users as A', 'statistics.user_id', '=', 'A.id')
                            ->join('users as B', 'statistics.payeer_id', '=', 'B.id')
                            ->orderBy('statistics.created_at', 'DESC')
                            ->where(function($q) use($user_id){
                                $q->where('statistics.user_id', '=', $user_id)->orWhere('statistics.payeer_id', '=', $user_id);
                            });
            }

            $transactions = $transactions->select('statistics.created_at',
                        'statistics.user_id',
                        'statistics.payeer_id',
                        'statistics.sum',
                        'statistics.type',
                        'A.username as receipt',
                        'B.username as payeer',
                        'statistics.description',
                        'statistics.last_balance',
                        'statistics.result_balance',
                        'statistics.last_payeer_balance',
                        'statistics.result_payeer_balance',
                    )->paginate(20);

            $dateFrom = $dateFrom->format('m-d-Y H:i');
            $dateTill = $dateTill->format('m-d-Y H:i');
            $DateFilterForm = [
                'dateFrom' => $dateFrom,
                'timeFrom' => $timeFrom,
                'dateTill' => $dateTill,
                'timeTill' => $timeTill,
            ];

            return view('backend.user.transactions', compact('transactions', 'DateFilterForm'));
        }

        /**
         * statistics for "Statistics" menu in side menu
         */

        public function statistics(\Illuminate\Http\Request $request)
        {
            $roles = \jeremykenedy\LaravelRoles\Models\Role::where('level', '<', auth()->user()->level())->pluck('name', 'id');
            $roles->prepend(trans('app.all'), '0');
            $shop = \VanguardLTE\Shop::find(auth()->user()->shop_id);
            $users = \VanguardLTE\User::orderBy('parents');

            if( auth()->user()->hasRole('admin') )
            {
                $users = $users->whereIn('role_id', [3,4,5,6]);
            }
            if( auth()->user()->hasRole('agent') || auth()->user()->hasRole('manager') )
            {
                $agents = auth()->user()->availableUsersByRole('agent');
                $managers = auth()->user()->availableUsersByRole('manager');
                $ids = array_merge($managers, $agents);

                if( $ids )
                {
                    $users = $users->whereIn('id', $ids);
                }
                else
                {
                    $users = $users->where('id', 0);
                }
            }

            // $users = $users->where('id', '!=', auth()->user()->id);
            if( $request->search != '' )
            {
                $request->search = str_replace('_', '\_', $request->search);
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
            if( $request->active )
            {
                if( $request->active == 1 )
                {
                    $users = $users->whereHas('sessions');
                }
                else
                {
                    $users = $users->whereDoesntHave('sessions');
                }
            }
            if( count($users->pluck('id')) )
            {
                $activeUsers = \VanguardLTE\User::whereIn('id', $users->pluck('id'))->whereHas('sessions')->pluck('id');
            }
            else
            {
                $activeUsers = \VanguardLTE\User::where('id', 0)->whereHas('sessions')->pluck('id');
            }

            $users_records = $users->orderBy('created_at', 'desc')->get();

            $hierarchy = [];
            $direct_children = [];
            $users = [];

            $sort_index = 0;
            foreach($users_records as $user)
            {
                $id = $user->id;
                $parents = explode("][", $user->parents);
                $selector = [];
                for($i = 0; $i < count($parents); $i++)
                {
                    $parent_id = str_replace(["[","]"], ["",""],$parents[$i]);
                    if($parent_id > auth()->user()->id)
                        $selector[] = $parent_id;
                }

                $hierarchy[$user->id] = $selector;
                $user->sortIndex = $sort_index;
                $users["'".$id."'"] = $user;
                $direct_children[$id] = 0;
                $sort_index++;

                if($user->role_id == 3)
                {
                    //in case of shop manager, return shop balance
                    $shop = \VanguardLTE\Shop::where('id', $user->shop_id)->get();
                    if(count($shop) > 0)
                    {
                        $user->balance = $shop[0]->balance;
                    }
                }
            }

            //sort
            foreach($users_records as $user)
            {
                $parents = explode("][", $user->parents);
                $last_parent = str_replace(["]", "["], ["",""], $parents[count($parents) - 1]);
                if($last_parent > auth()->user()->id)
                {
                    $direct_children[$last_parent]++;
                    $offset = $this->findOffsetWithKey( $users, "'".$user->id."'");
                    if($offset !== false)
                    {
                        $slice = array_splice($users, $offset, 1, null);
                        $offset_parent = $this->findOffsetWithKey($users, "'".$last_parent."'");
                        if($offset_parent !== false)
                        {
                            // array_splice($users, $offset_parent+1, 0, array("'".$user->id."'" => $slice));
                            $users = array_slice($users, 0, $offset_parent+1) + $slice + array_slice($users, $offset_parent+1);
                        }
                    }
                }
            }

            $data = [];
            if(isset($request->all()['DateFilterForm']))
            {
                $data = $request->all()['DateFilterForm'];
            }
            else
            {
                $data['DateFilterForm'] = [
                    'search' => '',
                ];
                if(isset($request['filter']))
                {
                    $filter = $request['filter'];
                    if($filter == 'today')
                    {
                        $data['dateFrom'] = date("m-d-Y");
                        $data['dateTill'] =  date("m-d-Y");
                    }
                    else if($filter == 'yesterday')
                    {
                        $data['dateFrom'] = date("m-d-Y", strtotime("-1 days"));
                        $data['dateTill'] =  date("m-d-Y", strtotime("-1 days"));
                    }
                    else if($filter == 'month')
                    {
                        $data['dateFrom'] = date("m-01-Y");
                        $data['dateTill'] =  date("m-d-Y");
                    }
                    else if($filter == 'week')
                    {
                        $data['dateFrom'] = date("m-d-Y", strtotime("monday this week"));
                        $data['dateTill'] =  date("m-d-Y");
                    }
                }
            }

            $dateFrom = date("m-d-Y");
            $timeFrom = ' 00:00';
            if(isset($data['dateFrom']))
                $dateFrom = $data['dateFrom'];
            if(isset($data['timeFrom']))
                $timeFrom = ' ' . $data['timeFrom'];
            $dateFrom = $dateFrom . $timeFrom;
            $dateFrom = date_create_from_format('m-d-Y H:i', $dateFrom);

            $dateTill = date("m-d-Y");
            $timeTill = ' 23:59';
            if(isset($data['dateTill']))
                $dateTill = $data['dateTill'];
            if(isset($data['timeTill']))
                $timeTill = ' '  . $data['timeTill'];
            $dateTill = $dateTill . $timeTill;
            $dateTill = date_create_from_format('m-d-Y H:i', $dateTill);

            $deposits = [];
            $reedems = [];

            $user_ids = implode(',', array_keys($users));
            if($user_ids == '')
                $user_ids = 'NULL';


            $funds = DB::select(DB::raw("select sum(case when type = 0 then amount else 0 end) as deposit,
                                        sum(case when type = 1 then amount else 0 end) as reedem, user_id
                                        from w_fund_logs
                                        where user_id in (".$user_ids.") and created_at between '".$dateFrom->format('Y-m-d H:i')."' and '".$dateTill->format('Y-m-d H:i')."'
                                        group by user_id"));
            foreach($users as $user)
            {
                $deposits[$user->id] = 0;
                $reedems[$user->id] = 0;
            }
            foreach($funds as $fund)
            {
                $deposits[$fund->user_id] = $fund->deposit;
                $reedems[$fund->user_id] = $fund->reedem;
            }

            $dateFrom = $dateFrom->format('m-d-Y H:i');
            $dateTill = $dateTill->format('m-d-Y H:i');

            $current_user = auth()->user()->id;


            $DateFilterForm = [
                'dateFrom' => $dateFrom,
                'timeFrom' => $timeFrom,
                'dateTill' => $dateTill,
                'timeTill' => $timeTill,
            ];
            return view('backend.user.statistics', compact('DateFilterForm', 'users', 'roles', 'activeUsers', 'hierarchy', 'direct_children', 'current_user', 'deposits', 'reedems'));
        }

        public function daily_report(\Illuminate\Http\Request $request)
        {
            $data = [];
            if(isset($request->all()['DateFilterForm']))
            {
                $data = $request->all()['DateFilterForm'];
            }
            else
            {
                $data['DateFilterForm'] = [
                    'search' => '',
                ];
                if(isset($request['filter']))
                {
                    $filter = $request['filter'];
                    if($filter == 'today')
                    {
                        $data['dateFrom'] = date("m-d-Y");
                        $data['dateTill'] =  date("m-d-Y");
                    }
                    else if($filter == 'yesterday')
                    {
                        $data['dateFrom'] = date("m-d-Y", strtotime("-1 days"));
                        $data['dateTill'] =  date("m-d-Y", strtotime("-1 days"));
                    }
                    else if($filter == 'month')
                    {
                        $data['dateFrom'] = date("m-01-Y");
                        $data['dateTill'] =  date("m-d-Y");
                    }
                    else if($filter == 'week')
                    {
                        $data['dateFrom'] = date("m-d-Y", strtotime("monday this week"));
                        $data['dateTill'] =  date("m-d-Y");
                    }
                }
            }

            $search = '';
            if(isset($data['search']))
                $search = $data['search'];
            else
                $search = auth()->user()->username;

            $dateFrom = date("m-d-Y", strtotime('-1 days'));
            $timeFrom = ' 00:00';
            if(isset($data['dateFrom']))
                $dateFrom = $data['dateFrom'];
            if(isset($data['timeFrom']))
                $timeFrom = ' ' . $data['timeFrom'];
            $dateFrom = $dateFrom . $timeFrom;
            $dateFrom = date_create_from_format('m-d-Y H:i', $dateFrom);

            $dateTill = date("m-d-Y");
            $timeTill = ' 23:59';
            if(isset($data['dateTill']))
                $dateTill = $data['dateTill'];
            if(isset($data['timeTill']))
                $timeTill = ' '  . $data['timeTill'];
            $dateTill = $dateTill . $timeTill;
            $dateTill = date_create_from_format('m-d-Y H:i', $dateTill);

            $user = \VanguardLTE\User::where('username', '=', $search)->get();
            $reports = [];
            $total = [];
            if(count($user) > 0)
            {
                $user = $user[0];
                if($user->hasRole('manager') || $user->hasRole('admin'))
                {
                    $reports = \VanguardLTE\BetWin::where('parents', 'like', '%['.$user->id.']%')->whereBetween('created_at', [$dateFrom, $dateTill])
                            ->selectRaw('sum(bet) as bet, sum(win) as win, Date(created_at) as date')->groupBy(DB::raw('Date(created_at)'))->paginate(20);
                    $total = DB::select(DB::raw("select sum(bet) as bet, sum(win) as win from w_bet_wins where parents like :parents and created_at between :dateFrom and :dateTill"),
                            ['parents' => '%['.$user->id.']%', 'dateFrom' => $dateFrom->format('Y-m-d H:i'), 'dateTill' => $dateTill->format('Y-m-d H:i')]);
                }
                else
                {
                    $reports = \VanguardLTE\BetWin::where('user_id', '=', $user->id)->whereBetween('created_at', [$dateFrom, $dateTill])->paginate(20);
                    $total = DB::select(DB::raw("select sum(bet) as bet, sum(win) as win from w_bet_wins where user_id = :userid and created_at between :dateFrom and :dateTill"),
                            ['userid' => $user->id, 'dateFrom' => $dateFrom->format('Y-m-d H:i'), 'dateTill' => $dateTill->format('Y-m-d H:i')]);
                }
            }

            //get all shop and agents
            $agents = \VanguardLTE\User::where('role_id', '>=', 3)->get();
            $dateFrom = $dateFrom->format('m-d-Y H:i');
            $dateTill = $dateTill->format('m-d-Y H:i');

            $DateFilterForm = [
                'search' => $search,
                'dateFrom' => $dateFrom,
                'timeFrom' => $timeFrom,
                'dateTill' => $dateTill,
                'timeTill' => $timeTill,
            ];

            return view('backend.user.report_daily', compact('reports', 'total', 'DateFilterForm'));
        }

        public function report(\Illuminate\Http\Request $request)
        {
            if( !(auth()->user()->hasRole('admin') ))
            {
                abort(403);
            }
            $data = [];
            if(isset($request->all()['DateFilterForm']))
            {
                $data = $request->all()['DateFilterForm'];
            }
            else
            {
                $data['DateFilterForm'] = [
                    'search' => '',
                ];
                if(isset($request['filter']))
                {
                    $filter = $request['filter'];
                    if($filter == 'today')
                    {
                        $data['dateFrom'] = date("m-d-Y");
                        $data['dateTill'] =  date("m-d-Y");
                    }
                    else if($filter == 'yesterday')
                    {
                        $data['dateFrom'] = date("m-d-Y", strtotime("-1 days"));
                        $data['dateTill'] =  date("m-d-Y", strtotime("-1 days"));
                    }
                    else if($filter == 'month')
                    {
                        $data['dateFrom'] = date("m-01-Y");
                        $data['dateTill'] =  date("m-d-Y");
                    }
                    else if($filter == 'week')
                    {
                        $data['dateFrom'] = date("m-d-Y", strtotime("monday this week"));
                        $data['dateTill'] =  date("m-d-Y");
                    }
                }
            }

            // $dateFrom = date("m-d-Y", strtotime('-1 days'));
            $dateFrom = date("m-d-Y");
            $timeFrom = ' 00:00';
            if(isset($data['dateFrom']))
                $dateFrom = $data['dateFrom'];
            if(isset($data['timeFrom']))
                $timeFrom = ' ' . $data['timeFrom'];
            $dateFrom = $dateFrom . $timeFrom;
            $dateFrom = date_create_from_format('m-d-Y H:i', $dateFrom);

            $dateTill = date("m-d-Y");
            $timeTill = ' 23:59';
            if(isset($data['dateTill']))
                $dateTill = $data['dateTill'];
            if(isset($data['timeTill']))
                $timeTill = ' '  . $data['timeTill'];
            $dateTill = $dateTill . $timeTill;
            $dateTill = date_create_from_format('m-d-Y H:i', $dateTill);

            $games = DB::select(DB::raw("select sum(bet) as bet, sum(win) as win, count(bet) as betcount, game_name as name from w_stat_game where category <> 2 and date_time between '".$dateFrom->format('Y-m-d H:i')."' and '".$dateTill->format('Y-m-d H:i')."' group by game_name"));
            $fishing_games = DB::select(DB::raw("select sum(bet) as bet, sum(win) as win, count(bet) as betcount, game_name as name from w_stat_game where category = 2 and date_time between '".$dateFrom->format('Y-m-d H:i')."' and '".$dateTill->format('Y-m-d H:i')."' group by game_name"));

            $total = DB::select(DB::raw("select sum(bet) as bet, sum(win) as win, count(bet) as betcount from w_stat_game where category <> 2 and date_time between '".$dateFrom->format('Y-m-d H:i')."' and '".$dateTill->format('Y-m-d H:i')."'"));
            $total_fishing = DB::select(DB::raw("select sum(bet) as bet, sum(win) as win, count(bet) as betcount from w_stat_game where category = 2 and date_time between '".$dateFrom->format('Y-m-d H:i')."' and '".$dateTill->format('Y-m-d H:i')."'"));

            $dateFrom = $dateFrom->format('m-d-Y');
            $dateTill = $dateTill->format('m-d-Y');
            $DateFilterForm = [
                'dateFrom' => $dateFrom,
                'timeFrom' => $timeFrom,
                'dateTill' => $dateTill,
                'timeTill' => $timeTill,
            ];
            return view('backend.user.report', compact('DateFilterForm', 'games', 'total', 'fishing_games', 'total_fishing'));
        }

        public function timezone()
        {
            $user = auth()->user();
            $timezone = \VanguardLTE\TimeZone::where("id", "=", $user->timezone)->get();
            $res = array(
                'timezone' => $timezone[0]->value
            );
            return json_encode($res);
        }

        public function onlineusers()
        {
            $users = \VanguardLTE\User::where('is_blocked', "=", 0)->get();
            $online_cnt = 0;
            foreach($users as $user)
            {
                $redis = app()->make('redis');
                $player_key = "player_time_";

                $player_id = $user->id;
                $player_val = json_decode($redis->get($player_key . $player_id));
                if($player_val != null && $player_val->time >= time() - 10)
                    $online_cnt++;
            }
            $total_users = count($users);
            $res = [
                'total' => $total_users,
                'online' => $online_cnt
            ];
            return json_encode($res);
        }

        public function update_profile(\Illuminate\Http\Request $request)
        {
            $data = $request->all();
            $validator = \Illuminate\Support\Facades\Validator::make($data, [
                'profile-password' => 'required|min:4|max:16',
            ]);
            if( $validator->fails() )
            {
                return redirect()->route('backend.user.player_create')->withErrors($validator)->withInput();
            }

            $userid = $data['user-id'];
            $user = \VanguardLTE\User::find($userid);
            $user->password = $data['profile-password'];
            $user->save();
            return redirect()->route('backend.user.player_create')->withSuccess(trans('app.settings_updated'));
        }

        public function changePassword()
        {
            return view('backend.user.profile');
        }

        public function update_password(\Illuminate\Http\Request $request)
        {
            $data = $request->all();
            $validator = \Illuminate\Support\Facades\Validator::make($data, [
                'password' => 'required|confirmed|min:6'
            ]);
            if( $validator->fails() )
            {
                return redirect()->route('backend.user.changepassword')->withErrors($validator)->withInput();
            }

            $user = auth()->user();
            $user->password = $data['password'];
            $user->save();
            return redirect()->route('backend.user.changepassword')->withSuccess(trans('app.settings_updated'));
        }
    }

}
