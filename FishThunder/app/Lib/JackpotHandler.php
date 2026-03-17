<?php
namespace VanguardLTE\Lib;

use Carbon\Carbon;
use VanguardLTE\Bonus;
use VanguardLTE\StatGame;
use VanguardLTE\User;

use function PHPSTORM_META\elementType;

class JackpotHandler{
    public static function float_rand($Min, $Max, $round=0){
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

    public static function updateJackpots($slotSettings, $bet)
    {
        $bonus = Bonus::where('user_id', '=', $slotSettings->user->id)->get();
        if(count($bonus) != 0)
        {
            //daily slots total bet
            $bonus_data = $bonus->where('type', '=', 1)->where('subtype', '=', 2)->first();
            if($bonus_data->available_step < $bonus_data->total_step)
            {
                $bonus_data->step_progress = number_format($bonus_data->step_progress + $bet, 2);
                if($bonus_data->step_progress >= $bonus_data->step_size)
                {
                    $bonus_data->available_step += 1;
                    if($bonus_data->available_step < $bonus_data->total_step)
                        $bonus_data->step_progress = 0;
                    if($bonus_data->available_step >= $bonus_data->total_step)
                        $bonus_data->available_step = $bonus_data->total_step;
                }
            }
            $bonus_data->save();
            //weekly slots total bet
            $bonus_data = $bonus->where('type', '=', 2)->where('subtype', '=', 2)->first();
            if($bonus_data->available_step < $bonus_data->total_step)
            {
                $bonus_data->step_progress = number_format($bonus_data->step_progress + $bet, 2);
                if($bonus_data->step_progress >= $bonus_data->step_size)
                {
                    $bonus_data->available_step += 1;
                    if($bonus_data->available_step < $bonus_data->total_step)
                        $bonus_data->step_progress = 0;
                    if($bonus_data->available_step >= $bonus_data->total_step)
                        $bonus_data->available_step = $bonus_data->total_step;
                }
            }
            $bonus_data->save();
            
            //progress slot 
            $bonus_data = $bonus->where('type', '=', 0)->first();
            if($bonus_data->available_step < $bonus_data->total_step)
            {
                $bonus_data->step_progress = number_format($bonus_data->step_progress + $bet, 2);
                if($bonus_data->step_progress >= $bonus_data->step_size)
                {
                    $bonus_data->available_step += 1;
                    if($bonus_data->available_step < $bonus_data->total_step)
                        $bonus_data->step_progress = 0;
                    if($bonus_data->available_step >= $bonus_data->total_step)
                        $bonus_data->available_step = $bonus_data->total_step;
                }
            }
            $bonus_data->save();
        }   
        
        //process fake jackpot

        $shop = \VanguardLTE\Shop::where('id', '=', $slotSettings->shop_id)->get();
        if($shop[0]->jackpot_active == 0)
            return;
        $bet = $bet * $slotSettings->CurrentDenom;        
        $jsum = [];
        $payJack = 0;
        for( $i = 0; $i < count($slotSettings->jpgs); $i++ )
        {
            $jsum[$i] = $bet * $slotSettings->jpgs[$i]->percent + $slotSettings->jpgs[$i]->balance;
            if( $slotSettings->jpgs[$i]->get_pay_sum() < $jsum[$i] && $slotSettings->jpgs[$i]->get_pay_sum() > 0 ) 
            {
                if( $slotSettings->jpgs[$i]->user_id && $slotSettings->jpgs[$i]->user_id != $slotSettings->user->id ) 
                {
                }
                else
                {
                    $jsum[$i] = $slotSettings->jpgs[$i]->get_start_balance();                    
                    $slotSettings->jpgs[$i]->pay_sum = JackpotHandler::float_rand($slotSettings->jpgs[$i]->start_payout, $slotSettings->jpgs[$i]->end_payout, 2);
                    $slotSettings->jpgs[$i]->tick++;
                    if($slotSettings->jpgs[$i]->tick > $slotSettings->jpgs[$i]->fake_cnt)
                        $slotSettings->jpgs[$i]->tick = 0;
                }
            }
            $slotSettings->jpgs[$i]->balance = $jsum[$i];
            $slotSettings->jpgs[$i]->save();
            if( $slotSettings->jpgs[$i]->balance < $slotSettings->jpgs[$i]->start_balance ) 
            {
                $summ = $slotSettings->jpgs[$i]->get_start_balance();
                if( $summ > 0 ) 
                {
                    $slotSettings->jpgs[$i]->add_jpg('add', $summ);
                }
            }
        }
    }

    public static function processBonus($slotSettings)
    {
        
    }
}
?>