<?php 
namespace VanguardLTE\Http\Controllers\Web\Frontend
{
    use Illuminate\Support\Facade\Redis;
    use Carbon\Carbon;
    use Illuminate\Support\Facades\DB;
    use VanguardLTE\Bonus;
    use VanguardLTE\BonusSetting;
    use VanguardLTE\Statistic;

    include_once(base_path() . '/app/ShopCore.php');
    include_once(base_path() . '/app/ShopGame.php');
    class PagesController extends \VanguardLTE\Http\Controllers\Controller
    {
        public function policy()
        {
            return view('frontend.pages.policy');
        }

        public function empty()
        {
            return view('frontend.pages.empty');
        }

        public function new_license()
        {
			/*
			$licensed = false;
			$licensed = true;
            $checked = new \VanguardLTE\Lib\LicenseDK();
            $license_notifications_array = $checked->aplVerifyLicenseDK(null, 0);
            if( $license_notifications_array['notification_case'] == 'notification_license_ok' ) 
            {
                $licensed = true;
            }
            if( !file_exists(resource_path() . '/views/system/pages/new_license.blade.php') ) 
            {
                abort(404);
            }
            return view('system.pages.new_license', compact('licensed'));
			*/
        }
        public function new_license_post(\Illuminate\Http\Request $request)
        {
            $email = trim($request->email);
            $code = trim($request->code);
            file_put_contents(base_path() . '/' . config('LicenseDK.APL_LICENSE_FILE_LOCATION'), '');
            $checked = new \VanguardLTE\Lib\LicenseDK();
            $license_notifications_array = $checked->aplInstallLicenseDK($request->getSchemeAndHttpHost(), $email, $code);
            if( $license_notifications_array['notification_case'] == 'notification_license_ok' ) 
            {
                return redirect()->back()->withSuccess(trans('app.license_is_already_installed'));
            }
            if( $license_notifications_array['notification_case'] == 'notification_already_installed' ) 
            {
                return redirect()->back()->withSuccess(trans('app.license_is_already_installed'));
            }
            return redirect()->back()->withErrors([$license_notifications_array['notification_text']]);
        }

        public function jpstv($id = 0)
        {
            return view('system.pages.jpstv', compact('id'));
        }       

        public function jpstv_json(\Illuminate\Http\Request $request)
        {
            $user = auth()->user();
            if($user == null)
            {
                //return kicked user response
                $res = ['status' => 'logout'];
                return json_encode($res);
            }

            //save login status of player to redis
            $player_key = "player_time_";
            $redis = app()->make('redis');
            $data = ["time" => time(), "ip" => request()->ip()];
            $page = $request['page'];


            $redis->set($player_key . $user->id, json_encode($data));

            $jNames = [
                'bronze',
                'silver',
                'gold',
                'platinum'
            ];
            $jCnt = 0;
            $res = [
                'status' => 'success', 
                'content' => [], 
                'i' => 1
            ];
            $res['balance'] = $user->balance;
            $res['phone'] = $user->phone;
            $res['phone_ref'] = $user->phone_ref;
            
            $data = \VanguardLTE\JPG::where('shop_id', $user->shop_id)->get();
            foreach( $data as $jackpot ) 
            {
                $res['content'][] = [
                    'name' => $jNames[$jCnt], 
                    'jackpot' => $jackpot->balance,                     
                ];
                $jCnt++;
                if( $jCnt > 5 ) 
                {
                    break;
                }
            }

            //process cashback bonus
            $userid = $user->id;
            if($page == 'hub' && $user->balance <= 0.15 && $user->balance_cashback > 0)
            {
                //add cashback balance
                $balance = $user->balance;
                $cashback = $user->balance_cashback;
                $user->update(['balance' => $balance + $cashback, 'balance_cashback' => 0]);
                \VanguardLTE\Statistic::create([
                        'user_id' => $userid, 
                        'payeer_id' => 0, 
                        'title' => 'CB',                     
                        'description' => 'Cashback',
                        'system' => 'bonus',
                        'sum' => $cashback, 
                        'last_balance' => $balance,
                        'result_balance' => $balance + $cashback,
                        'last_payeer_balance' => 0,
                        'result_payeer_balance' => 0,
                        'sum2' => $cashback, 
                        'shop_id' => $user->shop_id
                    ]);            
                $user->balance_cashback = 0;
            }  

            $date = Carbon::now()->addDay(-2)->format('Y-m-d H:i:s');
            $won_bonuses = \VanguardLTE\Statistic::where('system', '=', 'bonus')->where('user_id', '=', $user->id)->where('created_at', '>=', $date)->get();
            foreach($won_bonuses as $won_bonus)
            {
                if($won_bonus->item_id != 4) //daily wheel bonus popup is alerted immediately
                    $res['won_bonuses'][] = ['id' => $won_bonus->id, 'description' => $won_bonus->description, 'amount' => $won_bonus->sum, 'date' => $won_bonus->created_at];
            }

            $wheel_bonus = \VanguardLTE\BonusSetting::where('name', '=', 'wheel')->get();
            if(count($wheel_bonus) > 0)
            {
                $values = json_decode($wheel_bonus[0]->values, true);
                //check last user deposit date
                $date = Carbon::now()->addDay(-2)->format('Y-m-d H:i:s');
                $deposit = \VanguardLTE\Statistic::where('user_id', '=', $user->id)->where('result_balance', '>', 'last_balance')->where('created_at', '>=', $date)->get();
                $bonus_active = false;

                //check last user wheel bonus
                $cur_date = Carbon::now();
                $last_wheels = \VanguardLTE\Statistic::where('user_id', '=', $user->id)->where('item_id', '=', 4)->orderBy('id', 'desc')->get();
                $bonus_time_condition = true;
                $next_bonus_date = Carbon::now();
                if(count($last_wheels) > 0)
                {
                    $last_wheel = $last_wheels[0];
                    $last_wheel_date = Carbon::createFromFormat('m-d-Y H:i:s', $last_wheel->created_at);
                    $diff = $cur_date->diffInSeconds($last_wheel_date);
                    if($diff < 3600 * 24)
                    {
                        $bonus_time_condition = false;
                        $next_bonus_date->addSeconds(3600 * 24 - $diff);
                    }
                }

                if(count($deposit) > 0 && $bonus_time_condition)
                    $bonus_active = true;
                
                $res['wheel'] = [
                    'values' => $values,
                    'bonus_time' => $next_bonus_date->format('Y-m-d H:i:s'),
                    'active' => $bonus_active
                ];
            }
            return json_encode($res);
        }

        public function collect_wheel(\Illuminate\Http\Request $request)
        {
            $user = auth()->user();
            $res = [];
            if($user == null)
            {
                //return kicked user response
                $res = ['status' => 'logout'];
                return json_encode($res);
            }
            $wheel_bonus = \VanguardLTE\BonusSetting::where('name', '=', 'wheel')->get();
            if(count($wheel_bonus) > 0)
            {
                $values = json_decode($wheel_bonus[0]->values, true);
                //check last user deposit date
                $date = Carbon::now()->addDay(-2)->format('Y-m-d H:i:s');
                $deposit = \VanguardLTE\Statistic::where('user_id', '=', $user->id)->where('result_balance', '>', 'last_balance')->where('created_at', '>=', $date)->get();
                $bonus_active = false;

                //check last user wheel bonus
                $cur_date = Carbon::now();
                $last_wheels = \VanguardLTE\Statistic::where('user_id', '=', $user->id)->where('item_id', '=', 4)->orderBy('id', 'desc')->get();
                $bonus_time_condition = true;
                $next_bonus_date = Carbon::now();
                if(count($last_wheels) > 0)
                {
                    $last_wheel = $last_wheels[0];
                    $last_wheel_date = Carbon::createFromFormat('m-d-Y H:i:s', $last_wheel->created_at);
                    $diff = $cur_date->diffInSeconds($last_wheel_date);
                    if($diff < 3600 * 24)
                    {
                        $bonus_time_condition = false;
                        $next_bonus_date->addSeconds(3600 * 24 - $diff);
                    }
                }

                if(count($deposit) > 0 && $bonus_time_condition)
                {
                    $val_index = rand(0, count($values) - 1);
                    $possibility = rand(0, 100);
                    $bigIndices = [];
                    $mediumIndices = [];
                    $smallIndices = [];
                    for($i = 0; $i < count($values); $i++)
                    {
                        if($values[$i] >= 30)
                            $bigIndiecs[] = $i;
                        else if($values[$i] >= 11 && $values[$i] < 30)
                            $mediumIndices[] = $i;
                        else
                            $smallIndices[] = $i;
                    }
                    if($possibility < 85)
                        $val_index = $smallIndices[rand(0, count($smallIndices) - 1)];
                    else if($possibility < 95)
                        $val_index = $mediumIndices[rand(0, count($mediumIndices) - 1)];
                    else
                        $val_index = $bigIndiecs[rand(0, count($bigIndiecs) - 1)];
                    $winning_amount = $values[$val_index];

                    $last_balance = $user->balance;
                    $user->increment('balance', $winning_amount);
                    $result_balance = $last_balance + $winning_amount;
                    $user->save();
                    
                    //save statistics log
                    Statistic::create([
                        'title' => 'Bonus',
                        'user_id' => $user->id,
                        'payeer_id' => 1,
                        'description' => 'Daily Wheel Bonus',
                        'system' => 'bonus',
                        'sum' => $winning_amount,
                        'last_balance' => $last_balance,
                        'result_balance' => $result_balance,
                        'shop_id' => $user->shop_id,
                        'item_id' => 4 //wheel bonus
                    ]);

                    $res = [
                        'status' => 'success',
                        'win_amount' => $winning_amount,
                        'stop_position' => $val_index
                    ];
                }
                else
                {
                    $res = ['status' => 'fail', 'result' => 'Bonus not available.'];
                }
            }
            else
                $res = ['status' => 'fail', 'result' => 'Bonus not available.'];
            return json_encode($res);
        }

        
        public function error_license()
        {
            return view('system.pages.error_license');
        }
    }

}
