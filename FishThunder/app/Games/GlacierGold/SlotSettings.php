<?php 
namespace VanguardLTE\Games\GlacierGold
{

    // 0: scatter
    // 9: 9
    // 10: 10
    // j:  11
    // q: 12
    // k: 13
    // a: 14
    // ga: 15
    // ugei: 16
    // fly : 17
    // fireball: 18
    //Add Freespin
    use VanguardLTE\Lib\BasicSlotSettings;
    use VanguardLTE\Lib\JackpotHandler;

    class SlotSettings extends BasicSlotSettings
    {
        public $playerId = null;
        public $splitScreen = null;
        public $reelStrip1 = null;
        public $reelStrip2 = null;
        public $reelStrip3 = null;
        public $reelStrip4 = null;
        public $reelStrip5 = null;
        public $reelStrip6 = null;
        public $reelStripBonus1 = null;
        public $reelStripBonus2 = null;
        public $reelStripBonus3 = null;
        public $reelStripBonus4 = null;
        public $reelStripBonus5 = null;
        public $reelStripBonus6 = null;
        public $slotId = '';
        public $slotDBId = '';
        public $Line = null;
        public $scaleMode = null;
        public $numFloat = null;
        public $gameLine = null;
        public $Bet = null;
        public $isBonusStart = null;
        public $Balance = null;
        public $CashableBalance = null;
        public $SymbolGame = null;
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
        public $jpgPercentZero = false;        
        private $Bank = null;
        public $Percent = null;
        private $WinLine = null;
        private $WinGamble = null;
        private $Bonus = null;
        public $shop_id = null;
        public $licenseDK = null;
        public $currency = null;
        public $user = null;
        public $game = null;
        public $shop = null;
        public $normalPrizeTable = [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,2,2,2,2,2,3,3,3,3,4,4,4,5,5];
        public $bonusPrizeTable = [20,20,20,20,20,30,30,30,40,180];
        public $isDebug = false;

        public function __construct($sid)
        {
            $this->slotId = $sid;
            $this->playerId = \Auth::user()->id;
            $user = \VanguardLTE\User::lockForUpdate()->find($this->playerId);
            $this->user = $user;
            $this->shop_id = $user->shop_id;
            $game = \VanguardLTE\Game::where([
                'name' => $this->slotId, 
                'shop_id' => $this->shop_id
            ])->first();
            $this->shop = \VanguardLTE\Shop::find($this->shop_id);
            $this->game = $game;
            if (rand(0, 100) > 60)
            {
                $this->increaseRTP = 1;
            }else
            {
                $this->increaseRTP = 0;
            }
            $this->CurrentDenom = $this->game->denomination;
            $this->scaleMode = 0;
            $this->numFloat = 0;

            $this->Paytable['SYM_9'] = [
                0, 
                0, 
                0, 
                5, 
                10, 
                25
            ];
            $this->Paytable['SYM_10'] = [
                0, 
                0, 
                0, 
                5, 
                10, 
                25
            ];
            $this->Paytable['SYM_11'] = [
                0, 
                0, 
                0, 
                5, 
                10, 
                25
            ];
            $this->Paytable['SYM_12'] = [
                0, 
                0, 
                0, 
                5, 
                15, 
                50
            ];
            $this->Paytable['SYM_13'] = [
                0, 
                0, 
                0, 
                5, 
                15, 
                50
            ];
            $this->Paytable['SYM_14'] = [
                0, 
                0, 
                0, 
                5, 
                15, 
                50
            ];
            $this->Paytable['SYM_15'] = [
                0, 
                0, 
                0, 
                10, 
                25, 
                100
            ];
            $this->Paytable['SYM_16'] = [
                0, 
                0, 
                0,
                15, 
                50, 
                150
            ];
            $this->Paytable['SYM_17'] = [
                0, 
                0, 
                0, 
                25, 
                100, 
                250
            ];
            $this->Paytable['SYM_0'] = [
                0, 
                0, 
                0, 
                50, 
                150, 
                500
            ];
            $this->gambleLadder = [
                0, 
                10, 
                20, 
                30, 
                40, 
                -1, 
                50, 
                100, 
                150, 
                250, 
                400, 
                -1, 
                5000, 
                85000, 
                150000
            ];
            $reel = new GameReel();
            foreach( [
                'reelStrip1', 
                'reelStrip2', 
                'reelStrip3', 
                'reelStrip4', 
                'reelStrip5', 
                'reelStrip6'
            ] as $reelStrip ) 
            {
                if( count($reel->reelsStrip[$reelStrip]) ) 
                {
                    $this->$reelStrip = $reel->reelsStrip[$reelStrip];
                }
            }
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
            $this->slotWildMpl = 1;
            $this->GambleType = 1;
            $this->slotFreeCount = [
                0, 
                0, 
                0, 
                5, 
                0, 
                0
            ];
            $this->slotFreeMpl = 1;
            $this->slotViewState = ($game->slotViewState == '' ? 'Normal' : $game->slotViewState);
            $this->hideButtons = [];
            $this->jpgs = \VanguardLTE\JPG::where('shop_id', $this->shop_id)->get();            
            $this->slotJackPercent = [];
            $this->slotJackpot = [];
            for( $jp = 1; $jp <= 4; $jp++ ) 
            {
                $this->slotJackpot[] = $game->{'jp_' . $jp};
                $this->slotJackPercent[] = $game->{'jp_' . $jp . '_percent'};
            }
            $this->Line = [
                1, 
                2, 
                3, 
                4, 
                5, 
                6, 
                7, 
                8, 
                9, 
                10, 
            ];
            $this->gameLine = [
                1, 
                2, 
                3, 
                4, 
                5, 
                6, 
                7, 
                8, 
                9, 
                10, 
                11, 
                12, 
                13, 
                14, 
                15
            ];
            $this->Bet = explode(',', $game->bet);
            $this->SymbolGame = [
                1, 
                2, 
                3, 
                4, 
                5, 
                6, 
                7, 
                8, 
                9, 
                10, 
                11
            ];
            
            //$this->Percent = $this->shop->percent;
            if( !isset($this->user->session) || strlen($this->user->session) <= 0 ) 
            {
                $this->user->session = serialize([]);
            }
            $this->gameData = unserialize($this->user->session);
            $this->WinGamble = $game->rezerv;
            $this->slotDBId = $game->id;
        }

        public function SetGameData($key, $value)
        {
            $_obf_0D040604031A0C332A392C0F2E0C1018072E3C1C1B3C32 = 86400;
            $this->gameData[$key] = [
                'timelife' => time() + $_obf_0D040604031A0C332A392C0F2E0C1018072E3C1C1B3C32, 
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
            $this->user->refresh();
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
                $tmpLog = json_decode($log->str);
                if( $tmpLog->responseEvent != 'gambleResult' && $tmpLog->responseEvent != 'gambleResult2' && $tmpLog->responseEvent != 'jackpot' ) 
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
            return $this->shop->percent;
        }
        public function GetCashableBalance()
        {
            return 0;
        }
        public function GetCountBalanceUser()
        {
            $this->user->session = serialize($this->gameData);
            $this->user->save();
            $this->user->refresh();
            $this->gameData = unserialize($this->user->session);
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
            // if( $slotState == 'bonus' || $slotState == 'freespin' || $slotState == 'respin' ) 
            // {
            //     $slotState = 'bonus';
            // }
            // else
            // {
            //     $slotState = '';
            // }
            // $game = $this->game;
            // if( $this->GetBank($slotState) + $sum < 0 ) 
            // {                
            //     $lost = $this->GetBank($slotState) + $sum;
            //     if($slotState == 'bonus' && $this->GetBank('') > 0)
            //     {
            //         $normal_bank = $this->GetBank('');
            //         $exchange_amount = 0;
            //         if($normal_bank + $lost > 0)
            //             $exchange_amount = $lost;
            //         else
            //             $exchange_amount = -$normal_bank;

            //         $game->set_gamebank($exchange_amount, 'inc', 'bet');
            //         $game->set_gamebank(-$exchange_amount, 'inc', 'bonus');
            //     }  
            // }
            // $sum = $sum * $this->CurrentDenom;            
            // $bankBonusSum = 0;
            // if( $sum > 0 && $slotEvent == 'bet' ) 
            // {
            //     $this->toGameBanks = 0;
            //     $this->toSlotJackBanks = 0;
            //     $this->toSysJackBanks = 0;
            //     $this->betProfit = 0;
            //     $prc = $this->GetPercent();
            //     $prc_b = $prc / 2;
            //     if( $prc <= $prc_b ) 
            //     {
            //         $prc_b = 0;
            //     }
            //     $gameBet = $sum / $this->GetPercent() * 100;
            //     $bankBonusSum = $gameBet / 100 * $prc_b;
                                  
            //     for( $i = 0; $i < count($this->jpgs); $i++ ) 
            //     {
            //         if( !$this->jpgPercentZero ) 
            //         {
            //             $this->toSlotJackBanks += ($gameBet / 100 * $this->jpgs[$i]->percent);
            //         }
            //     }
            //     $this->toGameBanks = $sum;
            //     $this->betProfit = $gameBet - $this->toGameBanks - $this->toSlotJackBanks - $this->toSysJackBanks;
            // }
            // if( $sum > 0 ) 
            // {
            //     $this->toGameBanks = $sum;
            // }
            // if( $bankBonusSum > 0 ) 
            // {
            //     $sum -= $bankBonusSum;
            //     $game->set_gamebank($bankBonusSum, 'inc', 'bonus');
            // }
            // if( $sum == 0 && $slotEvent == 'bet' && isset($this->betRemains) ) 
            // {
            //     $sum = $this->betRemains;
            // }
            // $game->set_gamebank($sum, 'inc', $slotState);
            // $game->save();
            // return $game;
        }

        public function SetBalance($sum, $slotEvent = '')
        {
            if( $this->GetBalance() + $sum < 0 ) 
            {
                $this->InternalError('Balance_   ' . $sum);
            }
            $sum = $sum * $this->CurrentDenom;
            if( $sum < 0 && $slotEvent == 'bet' ) 
            {
                $user = $this->user;
                if( $user->count_balance == 0 ) 
                {
                    $remains = [];
                    $this->betRemains = 0;
                    $sm = abs($sum);
                    if( $user->address < $sm && $user->address > 0 ) 
                    {
                        $remains[] = $sm - $user->address;
                    }
                    for( $i = 0; $i < count($remains); $i++ ) 
                    {
                        if( $this->betRemains < $remains[$i] ) 
                        {
                            $this->betRemains = $remains[$i];
                        }
                    }
                }
                if( $user->count_balance > 0 && $user->count_balance < abs($sum) ) 
                {
                    $remains0 = [];
                    $sm = abs($sum);
                    $tmpSum = $sm - $user->count_balance;
                    $this->betRemains0 = $tmpSum;
                    if( $user->address > 0 ) 
                    {
                        $this->betRemains0 = 0;
                        if( $user->address < $tmpSum && $user->address > 0 ) 
                        {
                            $remains0[] = $tmpSum - $user->address;
                        }
                        for( $i = 0; $i < count($remains0); $i++ ) 
                        {
                            if( $this->betRemains0 < $remains0[$i] ) 
                            {
                                $this->betRemains0 = $remains0[$i];
                            }
                        }
                    }
                }
                $sum0 = abs($sum);
                if( $user->count_balance == 0 ) 
                {
                    $sm = abs($sum);
                    if( $user->address < $sm && $user->address > 0 ) 
                    {
                        $user->address = 0;
                    }
                    else if( $user->address > 0 ) 
                    {
                        $user->address -= $sm;
                    }
                }
                else if( $user->count_balance > 0 && $user->count_balance < $sum0 ) 
                {
                    $sm = $sum0 - $user->count_balance;
                    if( $user->address < $sm && $user->address > 0 ) 
                    {
                        $user->address = 0;
                    }
                    else if( $user->address > 0 ) 
                    {
                        $user->address -= $sm;
                    }
                }
                // $this->user->count_balance = $this->user->updateCountBalance($sum, $this->count_balance);
                // $this->user->count_balance = $this->FormatFloat($this->user->count_balance);
            }
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
        
        public function GetSpinSettings($garantType = 'bet', $bet)
        {
            $winType =  $this->SpinSettings($garantType, $bet);
            if($winType[0] == 'bonus')
            {
                if(rand(0, 100) < 50)
                    $winType[0] = 'freespin';
            }            
            return $winType;
            // $currentBonusWinChance = $this->game->get_lines_percent_config('bonus');
            // $currentSpinWinChance = $this->game->get_lines_percent_config('bet');
            // if($garantType == 'bonus')
            //     $currentSpinWinChance = 80;
            // $bonusWin = rand(1, 100);
            // $spinWin = rand(1, 100);
            
            // $return = [
            //     'none', 
            //     0
            // ];
            
            // if( $bonusWin <= $currentBonusWinChance && $this->slotBonus ) 
            // {
            //     $this->isBonusStart = true;
            //     $garantType = 'bonus';
            //     $winLimit = $this->GetBank($garantType);
            //     if($winLimit >= $bet * 20)
            //     {
            //         if(rand(0, 100) < 50)
            //             $return = ['bonus', $winLimit];
            //         else
            //             $return = ['freespin', $winLimit];
            //     }
            //     else
            //     {
            //         $return = ['none', 0];
            //     }
            // }
            // else if( $spinWin <= $currentSpinWinChance) 
            // {
            //     $winLimit = $this->GetBank($garantType);
            //     $return = [
            //         'win', 
            //         $winLimit
            //     ];
            // }
            // if( $garantType == 'bet' && $this->GetBalance() <= (2 / $this->CurrentDenom) ) 
            // {
            //     $randomPush = rand(1, 10);
            //     if( $randomPush == 1 ) 
            //     {
            //         $winLimit = $this->GetBank('');
            //         $return = [
            //             'win', 
            //             $winLimit
            //         ];
            //     }
            // }
            // return $return;
        }
        
        public function getNewSpin($game, $spinWin = 0, $bonusWin = 0, $lines, $garantType = 'bet')
        {
            $baselines = 10;
            switch( $lines ) 
            {
                case 10:
                    $baselines = 10;
                    break;
                case 9:
                case 8:
                    $baselines = 9;
                    break;
                case 7:
                case 6:
                    $baselines = 7;
                    break;
                case 5:
                case 4:
                    $baselines = 5;
                    break;
                case 3:
                case 2:
                    $baselines = 3;
                    break;
                case 1:
                    $baselines = 1;
                    break;
                default:
                    $baselines = 10;
                    break;
            }
            if( $garantType != 'bet' ) 
            {
                $fieldPrefix = '_bonus';
            }
            else
            {
                $fieldPrefix = '';
            }
            if( $spinWin ) 
            {
                $win = explode(',', $game->game_win->{'winline' . $fieldPrefix . $baselines});
            }
            if( $bonusWin ) 
            {
                $win = explode(',', $game->game_win->{'winbonus' . $fieldPrefix . $baselines});
            }
            $number = rand(0, count($win) - 1);
            return $win[$number];
        }
        public function GetRandomScatterPos($rp, $endCnt)
        {
            $rpResult = [];
            for( $i = 0; $i < count($rp); $i++ ) 
            {
                if( $rp[$i] == '2' ) 
                {
                    if(isset($rp[$i + $endCnt]))
                        array_push($rpResult, $i);
                    if( isset($rp[$i - 1]) && isset($rp[$i + $endCnt - 1]) ) 
                    {
                        array_push($rpResult, $i - 1);
                    }
                    if( isset($rp[$i - 2]) && isset($rp[$i + $endCnt - 2]) ) 
                    {
                        array_push($rpResult, $i - 2);
                    }
                    if( isset($rp[$i - 3]) && isset($rp[$i + $endCnt - 3]) ) 
                    {
                        array_push($rpResult, $i - 3);
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

        public function GetRandomFireBallPos($rp, $endCnt)
        {
            $rpResult = [];
            for( $i = 0; $i < count($rp); $i++ ) 
            {
                if( \intval($rp[$i]) >= 18) 
                {
                    if(isset($rp[$i + $endCnt]))
                        array_push($rpResult, $i);
                    if( isset($rp[$i - 1]) && isset($rp[$i + $endCnt - 1]) ) 
                    {
                        array_push($rpResult, $i - 1);
                    }
                    if( isset($rp[$i - 2]) && isset($rp[$i + $endCnt - 2]) ) 
                    {
                        array_push($rpResult, $i - 2);
                    }
                    if( isset($rp[$i - 3]) && isset($rp[$i + $endCnt - 3]) ) 
                    {
                        array_push($rpResult, $i - 3);
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
        public function GetGambleSettings()
        {
            $spinWin = rand(1, $this->WinGamble);
            return $spinWin;
        }
        public function GetReelStrips($winType, $slotEvent)  
        {
            $game = $this->game;
            $reel = new GameReel();
            $fArr = [];

            if($winType == "freespin") 
            {
                $reelsId = [];
                if($slotEvent == 0)
                {
                    foreach( [
                        'reelStrip1', 
                        'reelStrip2', 
                        'reelStrip3', 
                        'reelStrip4', 
                        'reelStrip5', 
                        'reelStrip6'
                    ] as $index => $reelStrip ) 
                    {
                        if ($index > 0 && $index < 4)
                        {
                            if( is_array($this->$reelStrip) && count($this->$reelStrip) > 0 ) 
                            {
                                $prs[$index + 1] = $this->GetRandomScatterPos($this->{'reelStrip' . ($index + 1)}, 4);
                            }
                        }else
                        {
                            if( is_array($this->$reelStrip) && count($this->$reelStrip) > 0 ) 
                            {
                                $prs[$index + 1] = rand(0, count($this->{'reelStrip' . ($index + 1)}) - 4);
                            }
                        }
                    }
                }
            }
            else if($winType == "bonus") 
            {
                $reelsId = [];
                $indexFireBall = \rand(0,4);
                foreach( [
                    'reelStrip1', 
                    'reelStrip2', 
                    'reelStrip3', 
                    'reelStrip4', 
                    'reelStrip5', 
                    'reelStrip6'
                ] as $index => $reelStrip ) 
                {
                    if ($index != $indexFireBall)
                    {
                        if( is_array($this->$reelStrip) && count($this->$reelStrip) > 0 ) 
                        {
                            $prs[$index + 1] = $this->GetRandomFireBallPos($this->{'reelStrip' . ($index + 1)}, 4);
                        }
                    }else
                    {
                        if( is_array($this->$reelStrip) && count($this->$reelStrip) > 0 ) 
                        {
                            $prs[$index + 1] = rand(0, count($this->{'reelStrip' . ($index + 1)}) - 4);
                        }
                    }
                }
            }else if( $slotEvent == 1 ) 
            {
                $prs = [];
                $fArr = $reel->reelsStripBonus;
                foreach( [
                    'reelStripBonus1', 
                    'reelStripBonus2', 
                    'reelStripBonus3', 
                    'reelStripBonus4', 
                    'reelStripBonus5', 
                    'reelStripBonus6'
                ] as $index => $reelsStripBonus ) 
                {
                    if( is_array($fArr[$reelsStripBonus]) && count($fArr[$reelsStripBonus]) > 0 ) 
                    {
                        $prs[$index + 1] = mt_rand(0, count($fArr[$reelsStripBonus]) - 4);
                    }
                }
            }else if($slotEvent == 0)  
            {
                $prs = [];
                foreach( [
                    'reelStrip1', 
                    'reelStrip2', 
                    'reelStrip3', 
                    'reelStrip4', 
                    'reelStrip5', 
                    'reelStrip6'
                ] as $index => $reelStrip ) 
                {
                    if( is_array($this->$reelStrip) && count($this->$reelStrip) > 0 ) 
                    {
                        
                        $prs[$index + 1] = mt_rand(0, count($this->$reelStrip) - 4);
                    }
                }
                

            }else if ($slotEvent == 2) 
            {
                $prs = [];
                foreach( [
                    'reelStrip1', 
                    'reelStrip2', 
                    'reelStrip3', 
                    'reelStrip4', 
                    'reelStrip5', 
                    'reelStrip6'
                ] as $index => $reelStrip ) 
                {
                    if( is_array($this->$reelStrip) && count($this->$reelStrip) > 0 ) 
                    {
                        $prs[$index + 1] = mt_rand(0, count($this->$reelStrip) - 8);
                    }
                }
            }
                
            $reel = [
                'rp' => []
            ];
            if ($slotEvent == 1)
            {
                foreach( $prs as $index => $value ) 
                {
                    $key = $fArr['reelStripBonus'.$index];
                    $reel['reel' . ($index)][0] = $key[$value];
                    $reel['reel' . ($index)][1] = $key[$value + 1];
                    $reel['reel' . ($index)][2] = $key[$value + 2];
                    $reel['reel' . ($index)][3] = $key[$value + 3];
                    $reel['reel' . ($index)][4] = '';
                    $reel['rp'][] = $value;
                }
            }else if ($slotEvent == 0)
            {
                foreach( $prs as $index => $value ) 
                {
                    $key = $this->{'reelStrip' . ($index)};
                    $reel['reel' . ($index)][0] = $key[$value];
                    $reel['reel' . ($index)][1] = $key[$value + 1];
                    $reel['reel' . ($index)][2] = $key[$value + 2];
                    $reel['reel' . ($index)][3] = $key[$value + 3];
                    $reel['reel' . ($index)][4] = '';
                    $reel['rp'][] = $value;
                }
            } else if ($slotEvent == 2)
            {
                foreach( $prs as $index => $value ) 
                {
                    $key = $this->{'reelStrip' . ($index)};
                    $reel['reel' . ($index)][0] = $key[$value];
                    $reel['reel' . ($index)][1] = $key[$value + 1];
                    $reel['reel' . ($index)][2] = $key[$value + 2];
                    $reel['reel' . ($index)][3] = $key[$value + 3];
                    $reel['reel' . ($index)][4] = $key[$value + 4];
                    $reel['reel' . ($index)][5] = $key[$value + 5];
                    $reel['reel' . ($index)][6] = $key[$value + 6];
                    $reel['reel' . ($index)][7] = $key[$value + 7];
                    $reel['reel' . ($index)][8] = '';
                    $reel['rp'][] = $value;
                }
            }
            return $reel;
        }
    }
}
