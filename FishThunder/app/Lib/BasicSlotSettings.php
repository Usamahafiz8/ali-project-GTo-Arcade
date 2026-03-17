<?php
namespace VanguardLTE\Lib;

use VanguardLTE\GameWinSetting;

class BasicSlotSettings{
    public $user = null;
    public $game = null;
    public $shop = null;
    public $BetWinKey = "GameBetWin";
    private $highRtp = 130;
    private $lowRtp = 80;

    public function SpinSettings($garantType, $betamount, $buyBonus = false)
    {
        $rtp_tag = '';
        if($this->shop->rtp_type == 1)
            $rtp_tag = "_s" . $this->shop->id;
        $gamewin_setting_key = "game_winsetting_" . $this->game->original_id . $rtp_tag;
        $redis = app()->make('redis');
        $winsetting = $redis->get($gamewin_setting_key);
        if($winsetting == null)
        {
            //read setting from db
            $settingDB = GameWinSetting::where('gameid', '=', $this->game->original_id)->get();
            if(count($settingDB) == 0)
            {
                //if cannot find setting from db, add new setting
                GameWinSetting::create(['gameid' => $this->game->original_id, 'gamename' => $this->game->name]);
                $settingDB = GameWinSetting::where('gameid', '=', $this->game->original_id)->get();
            }
            $setting = $settingDB[0];
            //insert game win setting to redis
            $winsetting = [
                'bsc_min' => $setting->bsc_min,
                'bsc_max' => $setting->bsc_max,
                'cur_bsc' => 0,
                'target_bsc' => rand($setting->bsc_min, $setting->bsc_max),
                'bsw_min' => $setting->bsw_min,
                'bsw_max' => $setting->bsw_max,
                'bsw' => rand($setting->bsw_min, $setting->bsw_max),
                'bbc_min' => $setting->bbc_min,
                'bbc_max' => $setting->bbc_max,
                'cur_bbc' => 0,
                'target_bbc' => rand($setting->bbc_min, $setting->bbc_max),                
                'bbw_min' => $setting->bbw_min,
                'bbw_max' => $setting->bbw_max,
                'bbw' => rand($setting->bbw_min, $setting->bbw_max),
                'fc_min' => $setting->fc_min,
                'fc_max' => $setting->fc_max,
                'cur_fc' => 0,
                'target_fc' => rand($setting->fc_min, $setting->fc_max),
                'fw_min' => $setting->fw_min,
                'fw_max' => $setting->fw_max,
                'fw_bc_min' => $setting->fw_bc_min,
                'fw_bc_max' => $setting->fw_bc_max,
                'fw_bc' => rand($setting->fw_bc_min, $setting->fw_bc_max),
                'fw_bw_min' => $setting->fw_bw_min,
                'fw_bw_max' => $setting->fw_bw_max,
                'fw' => rand($setting->fw_min, $setting->fw_max),
                'bet' => 0,
                'direction' => 0, //0: winning situation, 1: recovery situation
            ];
            $redis->set($gamewin_setting_key, json_encode($winsetting)); 
        }
        else
        {
            $winsetting = json_decode($winsetting, true);
        }

        $spinType = ['none', 0];
        if($buyBonus)
        {
            $this->user->spin_bank = $winsetting['fw'] * $betamount;
            $winsetting['cur_fc'] = 0;
            $winsetting['target_fc'] = rand($winsetting['fc_min'], $winsetting['fc_max']);
            $winsetting['fw'] = rand($winsetting['fw_min'], $winsetting['fw_max']);
            $winsetting['fw_bc'] = rand($winsetting['fw_bc_min'], $winsetting['fw_bc_max']);
            $winsetting['bet'] = $betamount;
            $spinType = ['bonus', $this->user->spin_bank / 10];
            $redis->set($gamewin_setting_key, json_encode($winsetting));
            $this->SetGameData($this->slotId . 'FSBigWin', 0);
        }
        else if($garantType == 'bet' || $garantType == 'normal')
        {
            $percent = rand(0, 100);
            $winsetting['cur_bsc']++;
            if($percent < 50)
                $winsetting['cur_bbc']++;
            else
                $winsetting['cur_fc']++;

            $cur_rtp = $this->GetCurrentRtp();            

            $fcCnt = $winsetting['target_fc'];
            $bgCnt = $winsetting['target_bbc'];
            $smCnt = $winsetting['target_bsc'];
            if($winsetting['direction'] == 0)
            {
                if($cur_rtp >= $this->highRtp)
                {
                    $winsetting['direction'] = 1;
                    $fcCnt = (int)($winsetting['target_fc'] * 2.5);
                    $bgCnt = (int)($winsetting['target_bbc'] * 2.5);
                    $smCnt = (int)($winsetting['target_bsc'] * 2.5);
                }
            }
            else
            {
                $fcCnt = (int)($winsetting['target_fc'] * 2.5);
                $bgCnt = (int)($winsetting['target_bbc'] * 2.5);
                $smCnt = (int)($winsetting['target_bsc'] * 2.5);
                if($cur_rtp <= $this->lowRtp)
                {
                    $fcCnt = $winsetting['target_fc'];
                    $bgCnt = $winsetting['target_bbc'];
                    $smCnt = $winsetting['target_bsc'];
                    $winsetting['direction'] = 0;
                }
            }

            // if($betamount > 5)
            // {
            //     $fcCnt = (int)($fcCnt * 1.5);
            //     $bgCnt = (int)($bgCnt * 1.5);
            //     $smCnt = (int)($smCnt * 1.5);
            // }
            if($winsetting['cur_fc'] >= $fcCnt)
            {
                $this->user->spin_bank = $winsetting['fw'] * $betamount;
                $winsetting['cur_fc'] = 0;
                $winsetting['target_fc'] = rand($winsetting['fc_min'], $winsetting['fc_max']);
                $winsetting['fw'] = rand($winsetting['fw_min'] * 10, $winsetting['fw_max'] * 10) / 10;                    
                $this->SetGameData($this->slotId . 'FSBigWin', $winsetting['fw_bc']);
                $winsetting['fw_bc'] = rand($winsetting['fw_bc_min'], $winsetting['fw_bc_max']);                    
                $winsetting['bet'] = $betamount;
                $spinType = ['bonus', $this->user->spin_bank / 10];
            }
            else if($winsetting['cur_bbc'] >= $bgCnt)
            {                
                $this->user->spin_bank = $winsetting['bbw'] * $betamount;
                $spinWinLimit = $winsetting['bbw'] * $betamount;
                $winsetting['cur_bbc'] = 0;
                $winsetting['target_bbc'] = rand($winsetting['bbc_min'], $winsetting['bbc_max']);
                $winsetting['bbw'] = rand($winsetting['bbw_min'] * 10, $winsetting['bbw_max'] * 10) / 10;
                $spinType = ['win', $spinWinLimit];                    
            }
            else if($winsetting['cur_bsc'] >= $smCnt)
            {
                $this->user->spin_bank = $winsetting['bsw'] * $betamount;
                $spinWinLimit = $winsetting['bsw'] * $betamount;
                $winsetting['cur_bsc'] = 0;
                $winsetting['target_bsc'] = rand($winsetting['bsc_min'], $winsetting['bsc_max']);
                $winsetting['bsw'] = rand($winsetting['bsw_min'] * 10, $winsetting['bsw_max'] * 10) / 10;
                $spinType = ['win', $spinWinLimit];
            }
            
            $redis->set($gamewin_setting_key, json_encode($winsetting));
        }
        else
        {
            $bank = $this->user->spin_bank;
            if($this->GetGameData($this->slotId . 'FSBigWin') > 0 && rand(0, 100) < 70 && $bank > $winsetting['fw_bw_min'])
            {
                $highLimit = $winsetting['fw_bw_max'];
                if($highLimit > $bank)
                    $highLimit = $bank;
                $times = rand($winsetting['fw_bw_min'], $highLimit);
                $bigwin = $times * $winsetting['bet'];
                $this->SetGameData($this->slotId . 'FSBigWin', $this->GetGameData($this->slotId . 'FSBigWin') - 1);
                $spinType = ['win', $bigwin];                
            }
            else
            {
                if($bank > 0 && rand(0, 100) < 80)
                {
                    $totalFreespin = $this->GetGameData($this->slotId . 'FreeGames');
                    $currentFreespin = $this->GetGameData($this->slotId . 'CurrentFreeGame');                
                    $leftSpin = $totalFreespin - $currentFreespin;
                    if($leftSpin > 0)
                        $bank /= $leftSpin;
                    $spinType = ['win', $bank];
                }                    
                else
                    $spinType = ['none', 0];
            }            
        }
        return $spinType;
    }

    public function GetCurrentRtp()
    {
        $rtp_tag = '';
        if($this->shop->rtp_type == 1)
            $rtp_tag = "_s" . $this->shop->id;
        $redis = app()->make('redis');
        $betwinKey = $this->BetWinKey . $this->game->original_id . $rtp_tag;
        $bet = $redis->hGet($betwinKey, 'bet');
        if($bet == null)
            $bet = 0;
        $win = $redis->hGet($betwinKey, 'win');
        if($win == null)
            $win = 0;
        $rtp = 0;
        if($bet != 0)
        {
            $rtp = $win / $bet * 100;
        }
        else if($bet == 0 && $win > 0)
            $rtp = 1000;
        
        return $rtp;
    }

    public function SetBet($amount)
    {
        if($this->shop->is_demo == 1)
            return;
        $rtp_tag = '';
        if($this->shop->rtp_type == 1)
            $rtp_tag = "_s" . $this->shop->id;
        $redis = app()->make('redis');
        $betwinKey = $this->BetWinKey . $this->game->original_id. $rtp_tag;
        $bet = $redis->hGet($betwinKey, 'bet');
        if($bet == null)
        {
            $bet = 0;            
        }
        $bet += $amount;
        $redis->hSet($betwinKey, 'bet', $bet);
    }

    public function SetWin($amount)
    {
        if($this->shop->is_demo == 1)
            return;
        $rtp_tag = '';
        if($this->shop->rtp_type == 1)
            $rtp_tag = "_s" . $this->shop->id;
        $redis = app()->make('redis');
        $betwinKey = $this->BetWinKey . $this->game->original_id. $rtp_tag;
        $win = $redis->hGet($betwinKey, 'win');
        if($win == null)
        {
            $win = 0;           
        }
        $win += $amount;
        $redis->hSet($betwinKey, 'win', $win);
        $this->user->spin_bank -= $amount;
    }

    public function GetSymbolPos($rp, $sym, $endCnt, $position = -1)
    {
        $rpResult = [];
        for( $i = 0; $i < count($rp); $i++ ) 
        {
            if( $rp[$i] == $sym ) 
            {
                if($position == -1)
                {
                    if( isset($rp[$i + $endCnt]) && isset($rp[$i - 1]) ) 
                    {
                        array_push($rpResult, $i);
                    }
                    if( isset($rp[$i - 1 + $endCnt]) && isset($rp[$i - 2]) ) 
                    {
                        array_push($rpResult, $i - 1);
                    }
                    if( isset($rp[$i + 1 + $endCnt]) && isset($rp[$i + 2]) ) 
                    {
                        array_push($rpResult, $i + 1);
                    }
                }
                else
                {
                    if( isset($rp[$i - $position]) && isset($rp[$i - $position + $endCnt])) 
                        array_push($rpResult, $i - $position);
                }                
            }
        }
        shuffle($rpResult);
        if( !isset($rpResult[0]) ) 
        {
            $rpResult[0] = rand(2, count($rp) - $endCnt);
        }
        return $rpResult[0];
    }

    public function AccumulateBonus($bet)
    {
        // $percent = 1;
        // $daily_bonus_key = 'daily_bonus_amount';
        // $weekly_bonus_key = 'weekly_bonus_amount';
        // $redis = app()->make('redis');
        // $daily_bonus_amount = $redis->get($daily_bonus_key);
        // $weekly_bonus_amount = $redis->get($weekly_bonus_key);        

        // if($daily_bonus_amount == null)
        //     $daily_bonus_amount = 0;
        // if($weekly_bonus_amount == null)
        //     $weekly_bonus_amount = 0;
        // $daily_bonus_amount += $bet * $percent / 100;
        // $weekly_bonus_amount += $bet * $percent / 100;
        // $redis->set($daily_bonus_key, $daily_bonus_amount);
        // $redis->set($weekly_bonus_key, $weekly_bonus_amount);
    }

    public function SaveLogReport($spinSymbols, $bet, $win, $slotState)
    {
        $reportName = $this->slotId . ' ' . $slotState;
        if( $slotState == 'freespin' ) 
        {
            $reportName = $this->slotId . ' FG';
        }
        else if( $slotState == 'bet' ) 
        {
            $reportName = $this->slotId . '';
        }
        else if( $slotState == 'slotGamble' ) 
        {
            $reportName = $this->slotId . ' DG';
        }
        $game = $this->game;
        if($this->shop->is_demo == 0)
        {
            \VanguardLTE\GameLog::create([
                'game_id' => $game->id, 
                'user_id' => $this->playerId, 
                'ip' => $_SERVER['REMOTE_ADDR'], 
                'str' => $spinSymbols, 
                'shop_id' => $this->shop_id
            ]);
            \VanguardLTE\StatGame::create([
                'user_id' => $this->playerId, 
                'balance' => $this->GetBalance(), 
                'bet' => $bet, 
                'win' => $win, 
                'game' => $reportName,              
                'game_name' => $this->slotId,
                'shop_id' => $this->shop_id, 
                'game_id' => $game->original_id,
                'category' => $game->category_temp,
                'date_time' => \Carbon\Carbon::now()
            ]);
        }        
    }
}
?>