<?php 
namespace VanguardLTE\Http\Controllers\Web\Backend
{

    use DateTime;
    use Illuminate\Support\Facades\Date;
    use Illuminate\Support\Facades\DB;
    use VanguardLTE\BonusSetting;
    use VanguardLTE\User;

    include_once(base_path() . '/app/ShopCore.php');
    include_once(base_path() . '/app/ShopGame.php');
    class DashboardController extends \VanguardLTE\Http\Controllers\Controller
    {
        private $users = null;
        private $activities = null;
        public function __construct(\VanguardLTE\Repositories\User\UserRepository $users, \VanguardLTE\Repositories\Activity\ActivityRepository $activities)
        {
            $this->middleware([
                'auth',                 
            ]);
            $this->middleware('permission:access.admin.panel');
            $this->users = $users;
            $this->activities = $activities;
        }
        
        public function index()
        {         
            if( \Illuminate\Support\Facades\Auth::check() && auth()->user()->hasRole('user') ) 
            {
                \Auth::logout();
                return redirect()->route('backend.dashboard');
            }

            if( \Auth::user()->hasRole('cashier') || \Auth::user()->hasRole('manager') ) {
                return redirect()->route('backend.user.player_create');
            }            
            return redirect()->route('backend.user.list');
        }
        
        public function bonus_setting()
        {
            $user = auth()->user();
            if(!$user->hasRole('admin'))
            {
                abort(403);
            }

            $wheel = BonusSetting::where('type', '=', '0')->get();
            if(count($wheel) < 1)
            {
                $values = [
                    '0' => 3,
                    '1' => 50,
                    '2' => 3,
                    '3' => 5,
                    '4' => 10,
                    '5' => 20,
                    '6' => 2,
                    '7' => 100,
                    '8' => 2,
                    '9' => 30,
                    '10' => 10,
                    '11' => 20,
                    '12' => 5,
                    '13' => 50,
                    '14' => 5,
                    '15' => 10,
                    '16' => 20,
                    '17' => 2,
                    '18' => 20,
                    '19' => 5,
                    '20' => 100,
                    '21' => 5,
                    '22' => 20,
                    '23' => 10,
                    '24' => 20,
                    '25' => 5,
                    '26' => 1,
                    '27' => 100,
                    '28' => 1,
                    '29' => 5,
                ];
                $data = ['type' => '0', 'subtype' => '0', 'name' => 'wheel', 'amount' => 0, 'values' => json_encode($values)];
                BonusSetting::create($data);
                $wheel = BonusSetting::where('type', '=', '0')->get();
            }
            $wheel = $wheel[0];
            $wheel_values = json_decode($wheel->values, true);
            return view('backend.bonus.setting', compact('user', 'wheel_values'));
        }

        public function bonus_update(\Illuminate\Http\Request $request)
        {
            $data = $request->all();
            $values = $data['values'];
            $values = json_encode($values);
            BonusSetting::where('id', '=', 1)->update(['values' => $values]);
            return '{"result":"success"}';
        }

        public function bonus_logs(\Illuminate\Http\Request $request)
        {
            $data = $request->all();            

            $search = '';
            if(isset($data['search']))
                $search = $data['search'];            
            
            $data = [];
            if(isset($request->all()['DateFilterForm']))
            {
                $data = $request->all()['DateFilterForm'];                
            }
            else
            {
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

            $logs = [];
            $parent = auth()->user();
            if($search != '')
            {
                $user = User::where('username', '=', $search)->where('parents', 'like', '%['.$parent->id.']%')->get();
                if(count($user) > 0)
                {
                    $user = $user[0];
                    $logs = DB::table('statistics')->join('users', function ($join) use($dateFrom, $dateTill, $user){
                        $join->on('users.id', '=', 'statistics.user_id')
                        ->whereBetween('statistics.created_at', [$dateFrom, $dateTill])
                        ->where('statistics.system', '=', 'bonus')
                        ->where('statistics.user_id', '=', $user->id);
                    })->selectRaw('description, sum, last_balance, result_balance, w_statistics.created_at, username')
                    ->paginate(20);
                }
            }
            else
            {
                $logs = DB::table('statistics')->join('users', function ($join) use($dateFrom, $dateTill, $parent){
                    $join->on('users.id', '=', 'statistics.user_id')
                    ->whereBetween('statistics.created_at', [$dateFrom, $dateTill])
                    ->where('statistics.system', '=', 'bonus')
                    ->where('users.parents', 'like', '%['.$parent->id.']%');
                })->selectRaw('description, sum, last_balance, result_balance, w_statistics.created_at, username')
                ->paginate(20);
            }
            
            $dateFrom = $dateFrom->format('m-d-Y');
            $dateTill = $dateTill->format('m-d-Y'); 
            $DateFilterForm = [
                'dateFrom' => $dateFrom,
                'timeFrom' => $timeFrom,
                'dateTill' => $dateTill,
                'timeTill' => $timeTill,
            ];

            return view('backend.bonus.logs', compact('DateFilterForm', 'logs', 'search'));
        }
    }

}
