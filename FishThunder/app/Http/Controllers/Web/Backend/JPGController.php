<?php 
namespace VanguardLTE\Http\Controllers\Web\Backend
{

    use VanguardLTE\Http\Requests\Request;
    use VanguardLTE\JPG;

include_once(base_path() . '/app/ShopCore.php');
    include_once(base_path() . '/app/ShopGame.php');
    class JPGController extends \VanguardLTE\Http\Controllers\Controller
    {
        public function __construct()
        {
            $this->middleware([
                'auth',                 
            ]);
            $this->middleware('permission:access.admin.panel');
            $this->middleware('permission:jpgame.manage');
            // $this->middleware('shopzero');
        }
        public function index(\Illuminate\Http\Request $request)
        {
            $jackpots = \VanguardLTE\JPG::where('shop_id', auth()->user()->shop_id)->get();
            return view('backend.jpg.list', compact('jackpots'));
        }

        public function editjp()
        {
            if( !auth()->user()->hasRole('admin') ) 
            {
                return redirect()->route('backend.user.list')->withErrors(['Invalid permission']);
            }
            $jackpots = \VanguardLTE\JPG::where('shop_id', 0)->get();
            return view('backend.jpg.edit', compact('jackpots'));
        }

        public function updatejp(\Illuminate\Http\Request $request)
        {
            $data = $request->all();
            
            if( !auth()->user()->hasRole('admin') ) 
            {
                return redirect()->route('backend.user.list')->withErrors(['Invalid permission']);
            }
            $bronze = $data['Bronze'];
            $silver = $data['Silver'];
            $gold = $data['Gold'];
            $platinum = $data['Platinum'];
            $percent = $data['percent'];
            JPG::where('name', '=', 'Bronze')->update([
                'start_balance' => $bronze['start_balance'],
                'start_payout' => $bronze['start_payout'],
                'end_payout' => $bronze['end_payout'],
                'percent' => $percent,
                'balance' => $bronze['start_balance'],
                'pay_sum' => $this->float_rand($bronze['start_payout'], $bronze['end_payout'], 2)]);

            JPG::where('name', '=', 'Silver')->update([
                'start_balance' => $silver['start_balance'],
                'start_payout' => $silver['start_payout'],
                'end_payout' => $silver['end_payout'],
                'percent' => $percent,
                'balance' => $silver['start_balance'],
                'pay_sum' => $this->float_rand($silver['start_payout'], $silver['end_payout'], 2)]);

            JPG::where('name', '=', 'Gold')->update([
                'start_balance' => $gold['start_balance'],
                'start_payout' => $gold['start_payout'],
                'end_payout' => $gold['end_payout'],
                'percent' => $percent,
                'balance' => $gold['start_balance'],
                'pay_sum' => $this->float_rand($gold['start_payout'], $gold['end_payout'], 2)]);

            JPG::where('name', '=', 'Platinum')->update([
                'start_balance' => $platinum['start_balance'],
                'start_payout' => $platinum['start_payout'],
                'end_payout' => $platinum['end_payout'],
                'percent' => $percent,
                'balance' => $platinum['start_balance'],
                'pay_sum' => $this->float_rand($platinum['start_payout'], $platinum['end_payout'], 2)]);

            return redirect()->route('backend.jp.edit')->withSuccess(trans('app.jackpot_updated'));
        }

        public function regenerate(\Illuminate\Http\Request $request)        
        {
            $data = $request->all();
            $jackpot_name = $data['name'];
            $jackpot = JPG::where('name', '=', $jackpot_name)
                ->where('shop_id', '=', 0)->get();
            $start_payout = $jackpot[0]->start_payout;
            $end_payout = $jackpot[0]->end_payout;
            $pay_sum = $this->float_rand($start_payout, $end_payout, 2);
            JPG::where('name', '=', $jackpot_name)->update([
                'pay_sum' => $pay_sum,
                'balance' => $jackpot[0]->start_balance
            ]);
            return '{"result":"success"}';
        }

        public function float_rand($Min, $Max, $round=0){
            //validate input
            $min = $Min;
            $max = $Max;
            if ($min > $Max) { $min=$Max; $max=$Min; }
                else { $min=$Min; $max=$Max; }
            $randomfloat = $min + mt_rand() / mt_getrandmax() * ($max - $min);
            if($round > 0)
                $randomfloat = round($randomfloat,$round);
         
            return $randomfloat;
        }

        public function edit($jackpot)
        {
            $jackpot = \VanguardLTE\JPG::where('id', $jackpot)->first();
            if( !$jackpot ) 
            {
                abort(404);
            }
            if( !in_array($jackpot->shop_id, auth()->user()->availableShops()) ) 
            {
                abort(404);
            }
            $activity = \VanguardLTE\Services\Logging\UserActivity\Activity::where([
                'system' => 'jackpot', 
                'item_id' => $jackpot->id
            ])->take(2)->get();
            $users = \VanguardLTE\User::where([
                'role_id' => 1, 
                'shop_id' => auth()->user()->shop_id
            ])->pluck('username', 'id');
            if( $users ) 
            {
                $users = $users->toArray();
            }
            return view('backend.jpg.edit', compact('jackpot', 'activity', 'users'));
        }
        public function update(\Illuminate\Http\Request $request, \VanguardLTE\JPG $jackpot)
        {
            if( !in_array($jackpot->shop_id, auth()->user()->availableShops()) ) 
            {
                abort(404);
            }
            if( auth()->user()->hasPermission('jpgame.edit') ) 
            {
                $data = $request->only([
                    'name', 
                    'pay_sum', 
                    'percent', 
                    'start_balance'
                ]);
            }
            else
            {
                $data = $request->only(['name']);
            }
            if( auth()->user()->hasRole('admin') && isset($request->balance) && $request->balance >= 0 ) 
            {
                $request->balance = str_replace(',', '.', $request->balance);
                $statSum = intval($request->balance - $jackpot->balance);
                $request->balance = $jackpot->balance + $statSum;
                if( abs($statSum) != 0 && $jackpot->shop_id >= 0 ) 
                {
                    $data = $data + ['balance' => $request->balance];
                    \VanguardLTE\Statistic::create([
                        'title' => $jackpot->name, 
                        'user_id' => 1, 
                        'system' => 'jpg', 
                        'type' => ($jackpot->balance < $request->balance ? 'add' : 'out'), 
                        'sum' => abs($statSum), 
                        'old' => $jackpot->balance, 
                        'shop_id' => $jackpot->shop_id
                    ]);
                }
            }
            foreach( $data as $key => &$item ) 
            {
                $item = str_replace(',', '.', $item);
                if( $item == '' ) 
                {
                    unset($data[$key]);
                }
            }
            $data = $data + $request->only(['user_id']);
            $jackpot->update($data);
            return redirect()->route('backend.jpgame.list')->withSuccess(trans('app.jackpot_updated'));
        }
        public function global(\Illuminate\Http\Request $request)
        {
            if( !(auth()->user()->hasRole('admin') || auth()->user()->hasPermission('jpgame.edit')) ) 
            {
                abort(403);
            }
            if( !$request->checkbox || !count($request->checkbox) ) 
            {
                return redirect()->back()->withErrors([trans('app.shops_not_selected')]);
            }
            $ids = [];
            foreach( $request->checkbox as $id => $val ) 
            {
                $ids[] = $id;
            }
            $jackpots = \VanguardLTE\JPG::whereIn('id', $ids)->where('shop_id', auth()->user()->shop_id)->get();
            return view('backend.jpg.global', compact('ids', 'jackpots'));
        }
        public function global_update(\Illuminate\Http\Request $request)
        {
            if( !$request->ids ) 
            {
                return redirect()->route('backend.jpgame.list')->withErrors([trans('app.jackpots_not_selected')]);
            }
            if( !(auth()->user()->hasRole('admin') || auth()->user()->hasPermission('jpgame.edit')) ) 
            {
                abort(403);
            }
            $data = [];
            if( auth()->user()->hasPermission('jpgame.edit') ) 
            {
                $data = $request->only([
                    'pay_sum', 
                    'percent', 
                    'start_balance'
                ]);
            }
            if( auth()->user()->hasRole('admin') ) 
            {
                $data = $data + $request->only(['balance']);
            }
            $update = [];
            if( count($data) ) 
            {
                foreach( $data as $key => $item ) 
                {
                    if( $item != '' ) 
                    {
                        $update[$key] = $item;
                    }
                }
            }
            $ids = explode(',', $request->ids);
            if( count($update) ) 
            {
                foreach( $ids as $id ) 
                {
                    $jpg = \VanguardLTE\JPG::where([
                        'id' => $id, 
                        'shop_id' => auth()->user()->shop_id
                    ])->first();
                    if( $jpg ) 
                    {
                        $temp = $update;
                        if( isset($temp['balance']) ) 
                        {
                            $temp['balance'] = floatval(str_replace(',', '.', $temp['balance']));
                            if( $temp['balance'] < 0 ) 
                            {
                                continue;
                            }
                            if( $jpg->balance < $temp['balance'] ) 
                            {
                            }
                            if( $jpg->balance < $temp['balance'] ) 
                            {
                                $sum = ceil($temp['balance'] - $jpg->balance);
                            }
                            else
                            {
                                $sum = intval($temp['balance'] - $jpg->balance);
                            }
                            $temp['balance'] = $jpg->balance + $sum;
                            if( abs($sum) == 0 ) 
                            {
                                continue;
                            }
                            if( $jpg->shop_id > 0 ) 
                            {
                                \VanguardLTE\Statistic::create([
                                    'title' => $jpg->name, 
                                    'user_id' => 1, 
                                    'system' => 'jpg', 
                                    'type' => ($jpg->balance < $temp['balance'] ? 'add' : 'out'), 
                                    'sum' => abs($sum), 
                                    'old' => $jpg->balance, 
                                    'shop_id' => $jpg->shop_id
                                ]);
                            }
                        }
                        $jpg->update($temp);
                    }
                }
            }
            return redirect()->route('backend.jpgame.list')->withSuccess(trans('app.jackpot_updated'));
        }

    }

}
