<?php

namespace VanguardLTE\Console\Commands;

use DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use VanguardLTE\Bonus;
use VanguardLTE\BonusSetting;
use VanguardLTE\Statistic;
use VanguardLTE\User;

class ProcessBonus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'utils:processbonus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process Bonus';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //read available bonus types
        // $date = new DateTime();
        // $week_day_index = $date->format("w");
        // $daily_bonus_key = 'daily_bonus_amount';
        // $weekly_bonus_key = 'weekly_bonus_amount';
        // $redis = app()->make('redis');
        // $daily_bonus_amount = $redis->get($daily_bonus_key);
        // if($daily_bonus_amount == null)
        //     $daily_bonus_amount = 0;

        // //daily bonus
        // $daily_bonus = DB::select(DB::raw("select user_id from (select sum(current_step) as step, user_id from w_bonuses where type = 1 group by user_id) as A where A.step >= 8"));
        // $winning_amount = $daily_bonus_amount;
        // foreach($daily_bonus as $bonus)
        // {
        //     $user_id = $bonus->user_id;

        //     $user = User::find($user_id);
        //     $last_balance = $user->balance;
        //     $user->increment('balance', $winning_amount);
        //     $result_balance = $last_balance + $winning_amount;
        //     $user->save();
            
        //     //save statistics log
        //     Statistic::create([
        //         'title' => 'Bonus',
        //         'user_id' => $user->id,
        //         'payeer_id' => 1,
        //         'description' => 'Daily Bonus',
        //         'system' => 'bonus',
        //         'sum' => $winning_amount,
        //         'last_balance' => $last_balance,
        //         'result_balance' => $result_balance,
        //         'shop_id' => $user->shop_id,
        //         'item_id' => 1
        //     ]);
        // }
        // Bonus::where('type', '=', 1)->update(['available_step' => 0, 'current_step' => 0, 'step_progress' => 0, 'is_collected' => 0]);
        // $redis->set($daily_bonus_key, 0);        

        // //weekly bonus
        // if($week_day_index == 0)
        // {
        //     $weekly_bonus_amount = $redis->get($weekly_bonus_key);
        //     if($weekly_bonus_amount == null)
        //         $weekly_bonus_amount = 0;
        //     $weekly_bonus = DB::select(DB::raw("select user_id from (select sum(current_step) as step, user_id from w_bonuses where type = 2 group by user_id) as A where A.step >= 8"));
        //     $winning_amount = $weekly_bonus_amount;

        //     foreach($weekly_bonus as $bonus)
        //     {
        //         $user_id = $bonus->user_id;
    
        //         $user = User::find($user_id);
        //         $last_balance = $user->balance;
        //         $user->increment('balance', $winning_amount);
        //         $result_balance = $last_balance + $winning_amount;
        //         $user->save();
                
        //         //save statistics log
        //         Statistic::create([
        //             'title' => 'Bonus',
        //             'user_id' => $user->id,
        //             'payeer_id' => 1,
        //             'description' => 'Weekly Bonus',
        //             'system' => 'bonus',
        //             'sum' => $winning_amount,
        //             'last_balance' => $last_balance,
        //             'result_balance' => $result_balance,
        //             'shop_id' => $user->shop_id,
        //             'item_id' => 1
        //         ]);
        //     }
        //     Bonus::where('type', '=', 2)->update(['available_step' => 0, 'current_step' => 0, 'step_progress' => 0, 'is_collected' => 0]);
        //     $redis->set($weekly_bonus_key, 0);
        // }
    }
}
