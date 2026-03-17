<?php 
namespace VanguardLTE\Games\CoinPusherPirateKing
{

    use VanguardLTE\Lib\BasicSlotSettings;
    use VanguardLTE\Lib\JackpotHandler;

    class SlotSettings extends BasicSlotSettings
    {
        public $winRate = 40;
        public $playerId = null;
        public $splitScreen = null;
        public $reelStrips = null;
        public $freeReelStrips = null;

        public $slotId = '';
        public $slotDBId = '';
        public $Line = null;
        public $scaleMode = null;
        public $numFloat = null;
        public $gameLine = null;
        public $Bet = null;
        public $isBonusStart = null;
        public $Balance = null;        
        public $SymbolString = null;
        public $SymbolGame = null;
        public $HighSymbol = null;
        public $GambleType = null;
        public $lastEvent = null;
        public $Jackpots = [];
        public $keyController = null;
        public $slotViewState = null;
        public $hideButtons = null;
        public $slotReelsConfig = null;
        public $slotFreeCount = null;
        public $slotFreeMpl = null;
        public $slotWildMpl = null;
        public $slotExitUrl = null;
        public $slotBonus = null;
        public $slotBonusType = null;
        public $slotScatterType = null;
        public $slotGamble = null;
        public $Paytable = [];
        public $slotSounds = [];
        public $jpgs = null;
        private $Bank = null;
        private $Percent = null;
        private $WinLine = null;
        private $WinGamble = null;
        private $Bonus = null;
        public $shop_id = null;
        public $currency = null;
        public $user = null;
        public $game = null;
        public $shop = null;
        public $jpgPercentZero = false;
        public $count_balance = null;
        public $wheelFeature = null;
        public $wheelChoices = null;
        public $awardIndices = null;
        public $bonusReels = [];
        public $bets = [1,2,5,10,20,50,80,100];
        public function __construct($sid, $playerId)
        {
            $this->slotId = $sid;
            $this->playerId = $playerId;
            $user = \VanguardLTE\User::lockForUpdate()->find($this->playerId);
            $this->user = $user;
            $this->shop_id = $user->shop_id;
            $game = \VanguardLTE\Game::where([
                'name' => $this->slotId, 
                'shop_id' => $this->shop_id
            ])->lockForUpdate()->first();
            $this->shop = \VanguardLTE\Shop::find($this->shop_id);
            $this->game = $game;
            $this->MaxWin = $this->shop->max_win;
            $this->increaseRTP = 1;
            $this->CurrentDenom = $this->game->denomination;
            $this->scaleMode = 0;
            $this->numFloat = 0;
            
             $this->keyController = [
                '13' => 'uiButtonSpin,uiButtonSkip', 
                '49' => 'uiButtonInfo', 
                '50' => 'uiButtonCollect', 
                '51' => 'uiButtonExit2', 
                '52' => 'uiButtonLinesMinus', 
                '53' => 'uiButtonLinesPlus', 
                '54' => 'uiButtonBetMinus', 
                '55' => 'uiButtonBetPlus', 
                '56' => 'uiButtonGamble', 
                '57' => 'uiButtonRed', 
                '48' => 'uiButtonBlack', 
                '189' => 'uiButtonAuto', 
                '187' => 'uiButtonSpin'
            ];
            $this->slotReelsConfig = [
                [
                    425, 
                    142, 
                    3
                ], 
                [
                    669, 
                    142, 
                    3
                ], 
                [
                    913, 
                    142, 
                    3
                ], 
                [
                    1157, 
                    142, 
                    3
                ], 
                [
                    1401, 
                    142, 
                    3
                ]
            ];
            $this->slotBonusType = 1;
            $this->slotScatterType = 0;
            $this->splitScreen = false;
            $this->slotBonus = true;
            $this->slotGamble = true;
            $this->slotFastStop = 1;
            $this->slotExitUrl = '/';
            $this->slotWildMpl = 2;
            $this->GambleType = 1;
            $this->slotFreeCount = 10;
            $this->slotFreeMpl = 1;
            $this->slotViewState = ($game->slotViewState == '' ? 'Normal' : $game->slotViewState);
            $this->hideButtons = [];
            $this->jpgs = \VanguardLTE\JPG::where('shop_id', $this->shop_id)->lockForUpdate()->get();
          
            $this->Balance = $user->balance;
            
            $this->SymbolGame = [0,1,2,3,4,5,6,7,8];
            /**
             * 0: 30x
             * 1: 25x
             * 2: 20x
             * 3: 15x
             * 4: 10x
             * 5: 8x
             * 6: 5x
             * 7: 3x
             * 8: 2x
             * 9: 1x
             */
            $this->Bank = $game->get_gamebank();
            $this->Percent = $this->shop->percent;
            $this->WinGamble = $game->rezerv;
            $this->slotDBId = $game->id;
            
            $this->count_balance = $user->count_balance;
            // if( $user->address > 0 && $user->count_balance == 0 ) 
            // {
            //     $this->Percent = 0;
            //     $this->jpgPercentZero = true;
            // }
            // else if( $user->count_balance == 0 ) 
            // {
            //     $this->Percent = 100;
            // }
            if( !isset($this->user->session) || strlen($this->user->session) <= 0 ) 
            {
                $this->user->session = serialize([]);
            }
            $this->gameData = unserialize($this->user->session);
            if( count($this->gameData) > 0 ) 
            {
                foreach( $this->gameData as $key => $vl ) 
                {
                    if( $vl['timelife'] <= time() ) 
                    {
                        unset($this->gameData[$key]);
                    }
                }
            }
            if( !isset($this->game->advanced) || strlen($this->game->advanced) <= 0 ) 
            {
                $this->game->advanced = serialize([]);
            }
            $this->gameDataStatic = unserialize($this->game->advanced);
            if( count($this->gameDataStatic) > 0 ) 
            {
                foreach( $this->gameDataStatic as $key => $vl ) 
                {
                    if( $vl['timelife'] <= time() ) 
                    {
                        unset($this->gameDataStatic[$key]);
                    }
                }
            }
        }
        public function is_active()
        {
            if( $this->game && $this->shop && $this->user && (!$this->game->view || $this->shop->is_blocked || $this->user->is_blocked || $this->user->status == \VanguardLTE\Support\Enum\UserStatus::BANNED) ) 
            {
                \VanguardLTE\Session::where('user_id', $this->user->id)->delete();
                $this->user->update(['remember_token' => null]);
                return false;
            }
            if( !$this->game->view ) 
            {
                return false;
            }
            if( $this->shop->is_blocked ) 
            {
                return false;
            }
            if( $this->user->is_blocked ) 
            {
                return false;
            }
            if( $this->user->status == \VanguardLTE\Support\Enum\UserStatus::BANNED ) 
            {
                return false;
            }
            return true;
        }
        public function SetGameData($key, $value)
        {
            $timeLife = 86400;
            $this->gameData[$key] = [
                'timelife' => time() + $timeLife, 
                'payload' => $value
            ];
        }
        public function GetGameData($key)
        {
            if( isset($this->gameData[$key]) ) 
            {
                return $this->gameData[$key]['payload'];
            }
            else
            {
                return 0;
            }
        }
        public function FormatFloat($num)
        {
            $str0 = explode('.', $num);
            if( isset($str0[1]) ) 
            {
                if( strlen($str0[1]) > 4 ) 
                {
                    return round($num * 100) / 100;
                }
                else if( strlen($str0[1]) > 2 ) 
                {
                    return floor($num * 100) / 100;
                }
                else
                {
                    return $num;
                }
            }
            else
            {
                return $num;
            }
        }
        public function SaveGameData()
        {
            $this->user->session = serialize($this->gameData);            
            $this->user->save();
        }
        public function CheckBonusWin()
        {
            $allRateCnt = 0;
            $allRate = 0;
            foreach( $this->Paytable as $vl ) 
            {
                foreach( $vl as $vl2 ) 
                {
                    if( $vl2 > 0 ) 
                    {
                        $allRateCnt++;
                        $allRate += $vl2;
                        break;
                    }
                }
            }
            return $allRate / $allRateCnt;
        }
        public function GetRandomPay()
        {
            $allRate = [];
            foreach( $this->Paytable as $vl ) 
            {
                foreach( $vl as $vl2 ) 
                {
                    if( $vl2 > 0 ) 
                    {
                        $allRate[] = $vl2;
                    }
                }
            }
            shuffle($allRate);
            if( $this->game->stat_in < ($this->game->stat_out + ($allRate[0] * $this->AllBet)) ) 
            {
                $allRate[0] = 0;
            }
            return $allRate[0];
        }
        public function HasGameDataStatic($key)
        {
            if( isset($this->gameDataStatic[$key]) ) 
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        public function SaveGameDataStatic()
        {
            $this->game->advanced = serialize($this->gameDataStatic);
            $this->game->save();
            $this->game->refresh();
        }
        public function SetGameDataStatic($key, $value)
        {
            $timeLife = 86400;
            $this->gameDataStatic[$key] = [
                'timelife' => time() + $timeLife, 
                'payload' => $value
            ];
        }
        public function GetGameDataStatic($key)
        {
            if( isset($this->gameDataStatic[$key]) ) 
            {
                return $this->gameDataStatic[$key]['payload'];
            }
            else
            {
                return 0;
            }
        }
        public function HasGameData($key)
        {
            if( isset($this->gameData[$key]) ) 
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        
        public function GetHistory()
        {
            $history = \VanguardLTE\GameLog::whereRaw('game_id=? and user_id=? ORDER BY id DESC LIMIT 10', [
                $this->slotDBId, 
                $this->playerId
            ])->get();
            $this->lastEvent = 'NULL';
            foreach( $history as $log ) 
            {
                $tmpLog = json_decode($log->str, true);
                // if( $tmpLog->responseEvent != 'gambleResult' ) 
                {
                    $this->lastEvent = $log->str;
                    break;
                }
            }
            if( isset($tmpLog) ) 
            {
                return $tmpLog;
            }
            else
            {
                return 'NULL';
            }
        }
        public function UpdateJackpots($bet)
        {
            $this->AccumulateBonus($bet);
            JackpotHandler::updateJackpots($this, $bet);
        }
        public function GetBank($slotState = '')
        {
            if( $slotState == 'bonus' || $slotState == 'freespin' || $slotState == 'respin' ) 
            {
                $slotState = 'bonus';
            }
            else
            {
                $slotState = '';
            }
            $game = $this->game;
            $this->Bank = $game->get_gamebank($slotState);
            
            return $this->Bank / $this->CurrentDenom;
        }
        public function GetPercent()
        {
            return $this->Percent;
        }
        public function GetCountBalanceUser()
        {
            return $this->user->count_balance;
        }
        public function InternalErrorSilent($errcode)
        {
            $strLog = '';
            $strLog .= "\n";
            $strLog .= ('{"responseEvent":"error","responseType":"' . $errcode . '","serverResponse":"InternalError","request":' . json_encode($_REQUEST) . ',"requestRaw":' . file_get_contents('php://input') . '}');
            $strLog .= "\n";
            $strLog .= ' ############################################### ';
            $strLog .= "\n";
            $slg = '';
            if( file_exists(storage_path('logs/') . $this->slotId . 'Internal.log') ) 
            {
                $slg = file_get_contents(storage_path('logs/') . $this->slotId . 'Internal.log');
            }
            file_put_contents(storage_path('logs/') . $this->slotId . 'Internal.log', $slg . $strLog);
        }
        public function InternalError($errcode)
        {
            $strLog = '';
            $strLog .= "\n";
            $strLog .= ('{"responseEvent":"error","responseType":"' . $errcode . '","serverResponse":"InternalError","request":' . json_encode($_REQUEST) . ',"requestRaw":' . file_get_contents('php://input') . '}');
            $strLog .= "\n";
            $strLog .= ' ############################################### ';
            $strLog .= "\n";
            $slg = '';
            if( file_exists(storage_path('logs/') . $this->slotId . 'Internal.log') ) 
            {
                $slg = file_get_contents(storage_path('logs/') . $this->slotId . 'Internal.log');
            }
            file_put_contents(storage_path('logs/') . $this->slotId . 'Internal.log', $slg . $strLog);
            exit( '' );
        }
        
        public function SetBank($slotState = '', $sum, $slotEvent = '')
        {
           
        }

        public function SetBalance($sum, $slotEvent = '')
        {
            if( $this->GetBalance() + $sum < 0 ) 
            {
                $this->InternalError('Balance_   ' . $sum);
            }
            $sum = $sum * $this->CurrentDenom;
           
            $this->user->increment('balance', $sum);
            $this->user->balance = $this->FormatFloat($this->user->balance);
            $this->user->save();
            JackpotHandler::processBonus($this);
            return $this->user;
        }

        public function GetBalance()
        {
            $user = $this->user;
            $this->Balance = $user->balance / $this->CurrentDenom;
            return $this->Balance;
        }
        
        public function GetSpinSettings($garantType = 'bet', $allbet)
        {
            return $this->SpinSettings($garantType, $allbet);
        }

        public function GetRandomScatterPos($rp, $endCnt, $sb)
        {
            $rpResult = [];
            for( $i = 0; $i < count($rp); $i++ ) 
            {
                if( $rp[$i] == $sb ) 
                {
                    if( isset($rp[$i]) && isset($rp[$i + $endCnt - 1]) ) 
                    {
                        array_push($rpResult, $i);
                    }
                    if( isset($rp[$i - 1]) && isset($rp[$i + $endCnt - 1]) ) 
                    {
                        array_push($rpResult, $i - 1);
                    }
                    if(isset($rp[$i - 2]) && isset($rp[$i + $endCnt - 2]) ) 
                    {
                        array_push($rpResult, $i - 2);
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

        public function GetRandomHanzoPos($rp, $endCnt, $sb)
        {
            $rpResult = [];
            for( $i = 0; $i < count($rp); $i++ ) 
            {
                if( $rp[$i] == $sb ) 
                {
                    array_push($rpResult, $i);
                }
            }
            shuffle($rpResult);
            if( !isset($rpResult[0]) ) 
            {
                $rpResult[0] = rand(0, count($rp) - $endCnt);
            }
            return $rpResult[0];
        }

        public function GetGambleSettings()
        {
            $spinWin = rand(1, $this->WinGamble);
            return $spinWin;
        }

        public function GetReelStripsMint($winType)
        {
            $symbols = [14,15,16,19];
            $coin = 18;
            $paylines = [[0,0,0,0,0], [1,1,1,1,1], [2,2,2,2,2]];
            $sym_cnt = count($symbols);
            $reels = [];
            if($winType == 'none')
            {
                $sym_cnt = count($symbols);

                $first_reel_syms = [];
                
                //generate first reel
                $reel = [];
                for($c = 0; $c < 3; $c++)
                {
                    $sym = $symbols[rand(0, $sym_cnt - 1)];
                    $reel[] = $sym;
                    if(!in_array($sym, $first_reel_syms))
                        $first_reel_syms[] = $sym;
                }
                $reels['reel1'] = $reel;
                for($r = 1; $r < 3; $r++)
                {
                    $reel = [];
                    for($c = 0; $c < 3; $c++)
                    {
                        if($r == 1)
                        {
                            $sym = $symbols[rand(0, $sym_cnt - 1)];
                            while(in_array($sym, $first_reel_syms))
                            {
                                $sym = $symbols[rand(0, $sym_cnt - 1)];
                            }
                        }
                        else
                            $sym = $symbols[rand(0, $sym_cnt - 1)];
                        $reel[] = $sym;
                    }
                    $reels['reel'.($r+1)] = $reel;
                }
            }
            else 
            {
                for($r = 0; $r < 3; $r++)
                    $reels['reel'.($r+1)] = [-1,-1,-1];
                $winningSym = $symbols[rand(0, $sym_cnt - 1)];                 
                $lines = count($paylines);
                $selected_lines = [];
                
                $lineCnt = rand(1, 2);

                while(count($selected_lines) < $lineCnt)
                {
                    $lineId = rand(0, count($paylines) - 1);
                    if(!in_array($lineId, $selected_lines))
                        $selected_lines[] = $lineId;
                }                    

                for($i = 0; $i < $lineCnt; $i++)
                {
                    //place winning symbol on selected payline
                    $selected_line = $paylines[$selected_lines[$i]];
                    $cnt = rand(3, 5);

                    for($r = 0; $r < $cnt; $r++)
                    {
                        $c = $selected_line[$r];
                        $reels['reel'.($r+1)][$c] = $winningSym;
                    }
                }

                for($r = 0; $r < 3; $r++)
                    for($c = 0; $c < 3; $c++)
                    {
                        if($reels['reel'.($r+1)][$c] == -1)
                        {
                            $sym = $symbols[rand(0, $sym_cnt - 1)];
                            $reels['reel'.($r+1)][$c] = $sym;
                            if(rand(0, 100) < 15)
                                $reels['reel'.($r+1)][$c] = $coin;
                        }
                    }
            }
            $reels['reel4'] = [0, 0, 0];
            $reels['reel5'] = [0, 0, 0];
            return $reels;
        }

        public function GetReelStrips($winType, $reelName, $slotEvent = 'bet')        
        {
            $symbols = [0,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17];
            $scatter = 1;

            $sym_cnt = count($symbols);
            $reels = [];
            if($winType == 'none' || $winType == 'bonus')
            {
                $sym_cnt = count($symbols);

                $first_reel_syms = [];
                
                //generate first reel
                $reel = [];
                for($c = 0; $c < 3; $c++)
                {
                    $sym = $symbols[rand(0, $sym_cnt - 1)];
                    $reel[] = $sym;
                    if(!in_array($sym, $first_reel_syms))
                        $first_reel_syms[] = $sym;
                }
                $reels['reel1'] = $reel;
                for($r = 1; $r < 5; $r++)
                {
                    $reel = [];
                    for($c = 0; $c < 3; $c++)
                    {
                        if($r == 1)
                        {
                            $sym = $symbols[rand(0, $sym_cnt - 1)];
                            while(in_array($sym, $first_reel_syms))
                            {
                                $sym = $symbols[rand(0, $sym_cnt - 1)];
                            }
                        }
                        else
                            $sym = $symbols[rand(0, $sym_cnt - 1)];
                        $reel[] = $sym;
                    }
                    $reels['reel'.($r+1)] = $reel;
                }

                if($winType == 'bonus' && $slotEvent == 'bet')
                {
                    $reels['reel1'][rand(0,2)] = $scatter;
                    if(rand(0, 100) < 5)
                        $reels['reel2'][rand(0,2)] = $scatter;
                    $reels['reel3'][rand(0,2)] = $scatter;
                    if(rand(0, 100) < 5)
                        $reels['reel4'][rand(0,2)] = $scatter;
                    $reels['reel5'][rand(0,2)] = $scatter;
                }
            }
            else 
            {
                for($r = 0; $r < 5; $r++)
                    $reels['reel'.($r+1)] = [-1,-1,-1];
                if(rand(0, 100) < 5)
                {
                    //generate v-type winning reel                    
                    for($r = 0; $r < 5; $r++)
                        for($c = 0; $c < 3; $c++)
                        {
                            if($reels['reel'.($r+1)][$c] == -1)
                            {
                                $sym = $symbols[rand(0, $sym_cnt - 1)];
                                $reels['reel'.($r+1)][$c] = $sym;
                            }
                        }
                    $poss = rand(0, 100);
                    if($poss < 50)
                    {
                        $reels['reel1'][0] = 6;
                        $reels['reel2'][1] = 6;
                        $reels['reel3'][2] = 5;
                        $reels['reel4'][1] = 7;
                        $reels['reel5'][0] = 7;
                    }
                    else
                    {
                        $reels['reel1'][2] = 4;
                        $reels['reel2'][1] = 4;
                        $reels['reel3'][0] = 2;
                        $reels['reel4'][1] = 3;
                        $reels['reel5'][2] = 3;
                    }
                }
                else
                {   
                    $winningSym = rand(8, 17);                 
                    $paylines = $this->GetPaylines($winningSym);
                    $lines = count($paylines);
                    $selected_lines = [];
                    
                    $lineCnt = rand(1, $lines > 2 ? 2 : $lines);

                    while(count($selected_lines) < $lineCnt)
                    {
                        $lineId = rand(0, count($paylines) - 1);
                        if(!in_array($lineId, $selected_lines))
                            $selected_lines[] = $lineId;
                    }                    

                    for($i = 0; $i < $lineCnt; $i++)
                    {
                        //place winning symbol on selected payline
                        $selected_line = $paylines[$selected_lines[$i]];
                        $cnt = rand(3, 5);

                        for($r = 0; $r < $cnt; $r++)
                        {
                            $c = $selected_line[$r];
                            if($c != -1)
                                $reels['reel'.($r+1)][$c] = $winningSym;                                
                        }
                    }

                    for($r = 0; $r < 5; $r++)
                        for($c = 0; $c < 3; $c++)
                        {
                            if($reels['reel'.($r+1)][$c] == -1)
                            {
                                $sym = $symbols[rand(0, $sym_cnt - 1)];
                                $reels['reel'.($r+1)][$c] = $sym;
                            }
                        }

                    $poss = rand(0, 50);
                    if($slotEvent == 'bet')
                    {
                        if($poss == 2)
                            $reels['reel1'][rand(0,2)] = $scatter;
                        else if($poss == 4)
                            $reels['reel3'][rand(0,2)] = $scatter;
                        else if($poss == 6)
                            $reels['reel5'][rand(0,2)] = $scatter;
                    }
                }
            }

            return $reels;
        }

        // public function GetReelStrips($winType, $reelName, $type)        
        // {
        //     $arrReels = [
        //         $reelName.'1', 
        //         $reelName.'2', 
        //         $reelName.'3',
        //         $reelName.'4',
        //         $reelName.'5',
        //     ];

        //     $endLen = 3;
        //     $prs = [];
        //     $reel = [
        //         'rp' => []
        //     ];
        //     $scatter = 1;
        //     $symbols = [2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17];
        //     if($winType == 'bonus')
        //     {
        //         $cnt = rand(3, 5);
        //         for($i = 0; $i < 5; $i++)
        //         {
        //             $reelStrip = $reelName . ($i+1);
        //             if($i < $cnt)
        //             {
        //                 $prs[$i + 1] = $this->GetSymbolPos($this->reelStrips[$reelStrip], $scatter, $endLen);
        //             }
        //             else
        //             {
        //                 $prs[$i + 1] = mt_rand(1, count($this->reelStrips[$reelStrip]) - $endLen);
        //             }
        //         }
        //     }
        //     else if($winType == 'win')
        //     {
        //         $sym = $symbols[rand(0, count($symbols) - 1)];
        //         $cnt = rand(3, 5);
        //         for($i = 0; $i < 5; $i++)
        //         {
        //             $reelStrip = $reelName . ($i+1);
        //             if($i < $cnt)
        //             {
        //                 $prs[$i + 1] = $this->GetSymbolPos($this->reelStrips[$reelStrip], $sym, $endLen);
        //             }
        //             else
        //             {
        //                 $prs[$i + 1] = mt_rand(1, count($this->reelStrips[$reelStrip]) - $endLen);
        //             }
        //         }
        //     }
        //     else
        //     {
        //         foreach( $arrReels as $index => $reelStrip ) 
        //         {
        //             if( is_array($this->reelStrips) && count($this->reelStrips[$reelStrip]) > 0 ) 
        //             {
        //                 $prs[$index + 1] = mt_rand(1, count($this->reelStrips[$reelStrip]) - $endLen > 1 ? count($this->reelStrips[$reelStrip]) - $endLen : 1);
        //             }
        //         }
        //     }
            
            
        //     foreach( $prs as $index => $value )
        //     {
        //         $key = $this->reelStrips[$reelName.$index];
        //         $key[-1] = $key[count($key) - 1];
        //         $reel['reel' . $index][0] = $key[$value];
        //         $reel['reel' . $index][1] = $key[($value + 1)];
        //         $reel['reel' . $index][2] = $key[($value + 2)];
        //         $reel['rp'][] = $value + 1;
        //     }

        //     return $reel;
        // }

        public function OffsetReels($reels, $spinType)
        {
            $newReels = [];
            $newReels['reel1'] = [];
            $newReels['reel2'] = [];
            $newReels['reel3'] = [];
            $newReels['reel4'] = [];
            $newReels['reel5'] = [];

            for( $r = 1; $r <= 5; $r++ ) 
            {
                for( $p = 2; $p >= 0; $p-- ) 
                {
                    if( $reels['reel' . $r][$p] != -1 ) 
                    {
                        array_unshift($newReels['reel' . $r], $reels['reel' . $r][$p]);
                    }
                }
            }
            $reelName = 'Reels0';
            if($spinType == 'freespin')
                $reelName = 'Reels1';
            
            $rp = [];
            for( $r = 1; $r <= 5; $r++ ) 
            {
                $reelstrip = $this->reelStrips[$reelName.$r];
                $pos = $reels['rp'][$r - 1];
                $cnt = count($newReels['reel' . $r]);
                for( $p = count($newReels['reel' . $r]); $p <= 2; $p++ ) 
                {
                    $pos = $reels['rp'][$r-1] - ($p - $cnt + 2);
                    if($pos < 0)
                        $pos += count($reelstrip);
                    $sym = $reelstrip[$pos];
                    array_unshift($newReels['reel' . $r], $sym);
                }
                $rp[] = $pos + 1;
            }
            $newReels['rp'] = $rp;
            return $newReels;
        }
        
        public function GetNudgedReel($reelName, $reelIndex, $rp, $nudge)
        {
            $arr = $this->reelStrips[$reelName.($reelIndex+1)];
            if($nudge == -1 && $rp < 3)
                return [];
            if($nudge == 1 && $rp > count($arr) - 4)
                return [];
            $res = array_slice($arr, ($rp + $nudge) - 1, 5);
            
            return $res;
        }

        public function GetPaylines($sym)
        {           
            if($sym >= 14 && $sym <= 17 || $sym == 19) //1, 5, 10, 20, 50
            {
                $linesId = [[1, 1, 1, 1, 1, 0], [0, 0, 0, 0, 0, 1], [2, 2, 2, 2, 2, 2]];
            }
            else if($sym >= 11 && $sym <= 13) // 10\, 20\, 50\
            {
                $linesId = [[-1, -1, -1, 0, 1, 3], [-1, -1, 0, 1, 2, 4], [-1, 0, 1, 2, -1, 5], [0, 1, 2, -1, -1, 6], [1, 2, -1, -1, -1, 7]];
            }
            else if($sym >= 8 && $sym <= 10)
            {
                $linesId = [[-1, -1, -1, 2, 1, 8], [-1, -1, 2, 1, 0, 9], [-1, 2, 1, 0, -1, 10], [2, 1, 0, -1, -1, 11], [1, 0, -1, -1, -1, 12]];
            }            
            else
                $linesId = [];
            return $linesId;
        }

        public function GetReelSymbol($reels)
        {
            $reelSyms = [];
            foreach($reels as $index => $value)
            {
                if(strpos($index, 'reel') !== false)
                {
                    $reel = [];
                    foreach($value as $sym)
                        $reel[] = $sym;
                    $reelSyms[] = $reel;
                }
            }
            return $reelSyms;
        }
    }

}
?>