<?php 
namespace VanguardLTE\Games\FiveLionsMegawaysPM
{

    use VanguardLTE\Lib\BasicSlotSettings;
    use VanguardLTE\Lib\JackpotHandler;

    class SlotSettings extends BasicSlotSettings
    {
        public $playerId = null;
        public $splitScreen = null;

        public $reelStrip0_0 = null;
        public $reelStrip0_1 = null;
        public $reelStrip0_2 = null;
        public $reelStrip1_0 = null;
        public $reelStrip1_1 = null;
        public $reelStrip1_2 = null;
        
        public $slotId = '';
        public $slotDBId = '';
        public $Line = null;
        public $scaleMode = null;
        public $numFloat = null;
        public $gameLine = null;
        public $Bet = null;
        public $isBonusStart = null;
        public $Balance = null;
        public $SymbolGame = null;
        public $GambleType = null;
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
        public $PayTable = [];
        public $CoinTable = [];
        public $slotSounds = [];
        public $jpgs = null;
        private $Bank = null;
        private $Percent = null;
        private $WinLine = null;
        private $WinGamble = null;
        private $Bonus = null;
        public $shop_id = null;
        public $licenseDK = null;
        public $currency = null;
        public $user = null;
        public $game = null;
        public $shop = null;
        public $credits = null;
        public $freespinCount = [];
        public $doubleWildChance = null;


        /* freespins */
        public $fsOpts = [];
        public $ReelSetMap = [];

        public function __construct($sid, $playerId)
        {
            $this->slotId = $sid;
            $this->playerId = $playerId;
            $user = \VanguardLTE\User::lockForUpdate()->find($this->playerId);
            
            $this->user = $user;
            $this->shop_id = $user->shop_id;
            $game = \VanguardLTE\Game::lockForUpdate()->where([
                'name' => $this->slotId, 
                'shop_id' => $this->shop_id
            ])->first();
            if (!$game)
            {
                exit('unlogged');
            }
            $this->shop = \VanguardLTE\Shop::find($this->shop_id);
            $this->game = $game;
            $this->increaseRTP = rand(0, 1);

            $this->doubleWildChance = 90;

            $this->CurrentDenom = $this->game->denomination;
            $this->scaleMode = 0;
            $this->numFloat = 0;
            $this->PayTable = [
                [0, 0, 0, 0, 0, 0],
                [0, 0, 0, 0, 0, 0],
                [0, 0, 0, 0, 0, 0],
                [500, 80, 40, 20, 0, 0],
                [100, 50, 25, 15, 0, 0],
                [100, 30, 15, 10, 0, 0],
                [50, 25, 10, 6, 0, 0],
                [50, 20, 10, 6, 0, 0],
                [25, 10, 6, 4, 0, 0],
                [25, 10, 6, 4, 0, 0],
                [20, 8, 4, 2, 0, 0],
                [20, 8, 4, 2, 0, 0],
                [20, 8, 4, 2, 0, 0],
                [0, 0, 0, 0, 0, 0],
            ];

            $this->ReelSetMap = [
                'buyBonus' => [6],
                'spin' => [4],
                'freespin' => [8],
            ];

            $this->fsOpts = [
                [
                    'spin_count' => 25,
                    'val1' => 1,
                    'val2' => 2,
                    'multipliers' => [2, 3, 5]
                ],
                [
                    'spin_count' => 20,
                    'val1' => 1,
                    'val2' => 2,
                    'multipliers' => [3, 5, 8]
                ],
                [
                    'spin_count' => 15,
                    'val1' => 1,
                    'val2' => 2,
                    'multipliers' => [5, 8, 10]
                ],
                [
                    'spin_count' => 13,
                    'val1' => 1,
                    'val2' => 2,
                    'multipliers' => [8, 10, 15]
                ],
                [
                    'spin_count' => 10,
                    'val1' => 1,
                    'val2' => 2,
                    'multipliers' => [10, 15, 30]
                ],
                [
                    'spin_count' => 6,
                    'val1' => 1,
                    'val2' => 2,
                    'multipliers' => [15, 30, 40]
                ],
                [
                    'spin_count' => -1,
                    'val1' => -1,
                    'val2' => 2,
                    'multipliers' => [-1]
                ],
            ];

            $reel = new GameReel();
            foreach( [
                'reelStrip0_0',
                'reelStrip0_1',
                'reelStrip0_2',
                'reelStrip0_3',
                'reelStrip0_4',
                'reelStrip0_5',

                'reelStrip1_0',
                'reelStrip1_1',
                'reelStrip1_2',
                'reelStrip1_3',
                'reelStrip1_4',
                'reelStrip1_5',

                'reelStrip2_0',
                'reelStrip2_1',
                'reelStrip2_2',
                'reelStrip2_3',
                'reelStrip2_4',
                'reelStrip2_5',

                'reelStrip3_0',
                'reelStrip3_1',
                'reelStrip3_2',
                'reelStrip3_3',
                'reelStrip3_4',
                'reelStrip3_5',

                'reelStrip4_0',
                'reelStrip4_1',
                'reelStrip4_2',
                'reelStrip4_3',
                'reelStrip4_4',
                'reelStrip4_5',

                'reelStrip5_0',
                'reelStrip5_1',
                'reelStrip5_2',
                'reelStrip5_3',
                'reelStrip5_4',
                'reelStrip5_5',

                'reelStrip6_0',
                'reelStrip6_1',
                'reelStrip6_2',
                'reelStrip6_3',
                'reelStrip6_4',
                'reelStrip6_5',

                'reelStrip7_0',
                'reelStrip7_1',
                'reelStrip7_2',
                'reelStrip7_3',
                'reelStrip7_4',
                'reelStrip7_5',

                'reelStrip8_0',
                'reelStrip8_1',
                'reelStrip8_2',
                'reelStrip8_3',
                'reelStrip8_4',
                'reelStrip8_5',
            ] as $reelStrip ) 
            {
                if( count($reel->reelsStrip[$reelStrip]) ) 
                {
                    $this->$reelStrip = $reel->reelsStrip[$reelStrip];
                }
            }
          
            $this->slotBonusType = 0;
            $this->slotScatterType = 0;
            $this->splitScreen = false;
            $this->slotBonus = true;
            $this->slotGamble = false;
            $this->slotFastStop = 1;
            $this->slotExitUrl = '/';
            $this->slotWildMpl = 1;
            $this->GambleType = 1;
            $this->slotFreeMpl = 1;
            $this->slotViewState = ($game->slotViewState == '' ? 'Normal' : $game->slotViewState);
            $this->hideButtons = [];
            $this->jpgs = \VanguardLTE\JPG::where('shop_id', $this->shop_id)->get();
            $this->Line = [1];
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
                15,
                16,
                17,
                18,
                19,
                20
            ];
            $this->Bet = explode(',', $game->bet); //[0.01,0.02,0.05,0.10,0.25,0.50,1.00,3.00,5.00]; 
            $this->Balance = $user->balance;

            $this->Bank = $game->get_gamebank();
            $this->Percent = $this->shop->percent;
            $this->WinGamble = $game->rezerv;
            $this->slotDBId = $game->id;
            $this->slotCurrency = $user->shop->currency;
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
        }
        public function SetGameData($key, $value)
        {
            $diffIndex = 864000;
            $this->gameData[$key] = [
                'timelife' => time() + $diffIndex, 
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
                return null;
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
            $ratioCount = 0;
            $totalPayRatio = 0;
            foreach( $this->PayTable as $vl ) 
            {
                foreach( $vl as $payRatio ) 
                {
                    if( $payRatio > 0 ) 
                    {
                        $ratioCount++;
                        $totalPayRatio += $payRatio;
                        break;
                    }
                }
            }
            return $totalPayRatio / $ratioCount / 2;
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
            $history = \VanguardLTE\GameLog::whereRaw('game_id=? and user_id=? ORDER BY id DESC LIMIT 1', [
                $this->slotDBId, 
                $this->playerId
            ])->get();
            $this->lastEvent = NULL;
            foreach( $history as $log ) 
            {
                $jsonLog = json_decode($log->str);
                $this->lastEvent = $jsonLog;
                break;
            }
            if( isset($jsonLog) ) 
            {
                return $jsonLog;
            }
            else
            {
                return NULL;
            }
        }
        public function ClearJackpot($jid)
        {
            $game = $this->game;
            $game->{'jp_' . ($jid + 1)} = sprintf('%01.4f', 0);
            $game->save();
        }
        public function UpdateJackpots($bet)
        {
            $this->AccumulateBonus($bet);
            JackpotHandler::updateJackpots($this, $bet);
        }
        public function GetBank($slotState = '')
        {
            if( /* $this->isBonusStart ||  */$slotState == 'bonus' || $slotState == 'freespin') 
            {
                $slotState = 'bonus';
            }
            else
            {
                $slotState = '';
            }
            $game = $this->game;
            $game->refresh();
            $this->Bank = $game->get_gamebank($slotState);
            return $this->Bank / $this->CurrentDenom;
        }
        public function GetPercent()
        {
            return $this->Percent;
        }
        public function GetCountBalanceUser()
        {
            $this->user->session = serialize($this->gameData);
            $this->user->save();
            $this->user->refresh();
            $this->gameData = unserialize($this->user->session);
            return $this->user->count_balance;
        }
        public function InternalError($errcode)
        {
            $_obf_strlog = '';
            $_obf_strlog .= "\n";
            $_obf_strlog .= date("Y-m-d H:i:s") . ' ';
            $_obf_strlog .= ('{"responseEvent":"error","responseType":"' . $errcode . '","serverResponse":"InternalError"}');
            $_obf_strlog .= "\n";
            $_obf_strlog .= ' ############################################### ';
            $_obf_strlog .= "\n";
            $_obf_strinternallog = '';
            if( file_exists(storage_path('logs/') . $this->slotId . 'Internal.log') ) 
            {
                $_obf_strinternallog = file_get_contents(storage_path('logs/') . $this->slotId . 'Internal.log');
            }
            file_put_contents(storage_path('logs/') . $this->slotId . 'Internal.log', $_obf_strinternallog . $_obf_strlog);
            // exit( '{"responseEvent":"error","responseType":"' . $errcode . '","serverResponse":"InternalError"}' );
        }
        public function SetBank(/* $slotState = '',  */$slotEvent = '', $sum, $isBuyFreespin = -1)
        {
            // if( /* $this->isBonusStart || */ $slotEvent == 'buyBonus' || $slotEvent == 'freespin') 
            // {
            //     $slotState = 'bonus';
            // }
            // else
            // {
            //     $slotState = '';
            // }
            // $sum = $sum * $this->CurrentDenom;
            // $game = $this->game;

            // if($isBuyFreespin == 0){
            //     $game->set_gamebank($sum, 'inc', 'bonus');
            //     $game->save();
            //     return $game;
            // }
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
            // if( $sum > 0 && $slotEvent == 'doSpin' ) 
            // {
            //     $this->toGameBanks = 0;
            //     $this->toSlotJackBanks = 0;
            //     $this->toSysJackBanks = 0;
            //     $this->betProfit = 0;
            //     $prc = $this->GetPercent();
            //     $prc_b = 45;
            //     if( $prc <= $prc_b ) 
            //     {
            //         $prc_b = 0;
            //     }
            //     $gameBet = $sum / $this->GetPercent() * 100;
            //     $bankBonusSum = $gameBet / 100 * $prc_b;
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
                $this->user->count_balance = $this->user->updateCountBalance($sum, $this->count_balance);
                $this->user->count_balance = $this->FormatFloat($this->user->count_balance);
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
        
        public function GetSpinSettings($garantType, $allbet, $lines, $isBuyBonus = false)
        {
            $this->AllBet = $allbet * $lines;
            if($garantType == 'doSpin')
                $garantType = 'bet';
            return $this->SpinSettings($garantType, $this->AllBet, $isBuyBonus);
            // $currentBonusWinChance = $this->game->get_lines_percent_config('bonus');
            // $currentSpinWinChance = $this->game->get_lines_percent_config('bet');
            // $bonusWin = rand(1, 100);
            // $spinWin = rand(1, 100);
            
            // $return = [
            //     'none', 
            //     0
            // ];
            
            // if( $bonusWin <= $currentBonusWinChance && $this->slotBonus ) 
            // {
            //     if ($isTumble || $garantType == 'freespin') {
            //         $return = [
            //             'none', 
            //             0
            //         ];
            //     }
            //     else {
            //         // $this->isBonusStart = true;
            //         $this->isBonusStart = true;
            //         $garantType = 'bonus';                
            //         $winLimit = $this->GetBank($garantType);
            //         if($winLimit >= $this->AllBet * 20)
            //         {
            //             $return = ['bonus', $winLimit];
            //         }
            //         else
            //         {
            //             $return = ['none', 0];
            //         }
            //     }
            // }
            // else if( $spinWin <= $currentSpinWinChance) 
            // {
            //     $winLimit = $this->GetBank($garantType);
            //     $return = [
            //         'win', 
            //         $winLimit
            //     ];
            //     if($winLimit < 0 && $garantType != 'freespin')
            //         $return = ['none', 0];
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
        public function getNewSpin($game, $spinWin = 0, $bonusWin = 0, $lines, $garantType = 'doSpin')
        {
            $_obf_linecount = 10;
            switch( $lines ) 
            {
                case 10:
                    $_obf_linecount = 10;
                    break;
                case 9:
                case 8:
                    $_obf_linecount = 9;
                    break;
                case 7:
                case 6:
                    $_obf_linecount = 7;
                    break;
                case 5:
                case 4:
                    $_obf_linecount = 5;
                    break;
                case 3:
                case 2:
                    $_obf_linecount = 3;
                    break;
                case 1:
                    $_obf_linecount = 1;
                    break;
                default:
                    $_obf_linecount = 10;
                    break;
            }
            if( $garantType != 'doSpin' /* && $garantType != 'tumblespin' */ ) 
            {
                $_obf_granttype = '_bonus';
            }
            else
            {
                $_obf_granttype = '';
            }
            if( $spinWin ) 
            {
                $win = explode(',', $game->game_win->{'winline' . $_obf_granttype . $_obf_linecount});
            }
            if( $bonusWin ) 
            {
                $win = explode(',', $game->game_win->{'winbonus' . $_obf_granttype . $_obf_linecount});
            }
            $number = random_int(0, count($win) - 1);
            return $win[$number];
        }

        public function GetGambleSettings()
        {
            $spinWin = random_int(1, $this->WinGamble);
            return $spinWin;
        }

        public function GenerateScatterCount($winType, $slotEvent){ // Scatter generation
            if($winType == 'bonus'){      
                if ($slotEvent == 'freespin') {
                    $probabilityMap = [
                        3 => 100
                    ];
                }
                else {
                    $probabilityMap = [
                        3 => 95,
                        4 => 5,
                        5 => 0
                    ];
                }
            }
            else {
                $probabilityMap = [
                    0 => 90,
                    1 => 9,
                    2 => 1
                ];
            }
            
            $sum = array_sum(array_values($probabilityMap));
            $randNum = random_int(1, $sum);

            $sum = 0;
            foreach ($probabilityMap as $key => $probability) {
                $sum += $probability;
                if ($randNum <= $sum) {
                    return $key;
                }
            }

            return 0;  
        }

        public function GenerateBonusStep() {
            $probabilityMap = [
                1 => 100,
                2 => 0,
                3 => 0,
            ];
            
            $sum = array_sum(array_values($probabilityMap));
            $randNum = random_int(1, $sum);

            $sum = 0;
            foreach ($probabilityMap as $key => $probability) {
                $sum += $probability;
                if ($randNum <= $sum) {
                    return $key;
                }
            }

            return 1;
        }

        public function GenerateWildCount($winType, $slotEvent) {
            if($winType == 'bonus'){      
                $probabilityMap = [
                    0 => 100,
                ];
            }
            else if ($winType == 'none') {
                $probabilityMap = [
                    0 => 80,
                    1 => 20,
                    2 => 0
                ];
            }
            else {
                if ($slotEvent == 'freespin') {
                    $probabilityMap = [
                        0 => 20,
                        1 => 50,
                        2 => 30
                    ];
                }
                else {
                    $probabilityMap = [
                        0 => 80,
                        1 => 15,
                        2 => 5
                    ];
                }
            }
               
            $sum = array_sum(array_values($probabilityMap));
            $randNum = random_int(1, $sum);

            $sum = 0;
            foreach ($probabilityMap as $key => $probability) {
                $sum += $probability;
                if ($randNum <= $sum) {
                    return $key;
                }
            }

            return 0;  
        }

        public function GenerateWildMultiplier($slotEvent, $multipliers) {
            if ($slotEvent == 'freespin') {
                if ($multipliers == null) {
                    return 1;
                }

                $probabilityMap = [];
                $probabilityMap[$multipliers[0]] = 65;
                $probabilityMap[$multipliers[1]] = 30;
                $probabilityMap[$multipliers[2]] = 5;
            }
            else {
                $probabilityMap = [
                    1 => 10,
                    2 => 45,
                    3 => 28,
                    5 => 15,
                    8 => 2,
                    10 => 0,
                    15 => 0,
                    30 => 0,
                    40 => 0,
                ];
            }
            
            $sum = array_sum(array_values($probabilityMap));
            $randNum = random_int(1, $sum);

            $sum = 0;
            foreach ($probabilityMap as $key => $probability) {
                $sum += $probability;
                if ($randNum <= $sum) {
                    return $key;
                }
            }

            return array_keys($probabilityMap)[0];
        }

        public function GetSymbolCount($default = 2){   // symbol count in one reel
            $probabilityMap = [
                2 => 15, 
                3 => 30, 
                4 => 35, 
                5 => 15, 
                6 => 4, 
                7 => 1, 
            ];

            $sum = array_sum(array_values($probabilityMap));
            $randNum = random_int(1, $sum);

            $sum = 0;
            foreach ($probabilityMap as $key => $probability) {
                $sum += $probability;
                if ($randNum <= $sum) {
                    return $key;
                }
            }

            return $default;
        }

        public function GetReelStrips($winType, $slotEvent, $lastSpin, $proposedScatterCount, $proposedWildCount, $availableMultipliers, $minorWin)
        {
            $REELCOUNT = 6;
            $MAXSYMBOLCOUNT = 7;
            $S_WILD = 2;
            $S_SCATTER = 1;
            $S_BLANK = 13;
            $S_REMOVED = -1;
           
            $reels = [
                'symbolsAfter' => [],
                'symbolsBefore' => [],
                'symbols' => [],
                'flatSymbols' => [],
                'scatterSymbols' => [],
                'wildSymbols' => [],
                'reelSetId' => 0,
            ];

            /* in tumble spin, keep orignal reel and generate tumble symbols only */
            if (isset($lastSpin)) {
                /* tumble spin */
                
                // load last reel
                $lastFlatSymbols = explode(",", $lastSpin->s);

                foreach ($lastFlatSymbols as $pos => $symbol) {
                    $reelPos = intdiv($pos, $REELCOUNT);
                    $reelId = $pos % $REELCOUNT;
                    $reels['symbols'][$reelId][$reelPos] = $symbol;
                }

                // tumble bonus, if there is scatter symbol to generate 
                $tumbleSymbols = explode("~", $lastSpin->tmb);

                $scatterPoses = [];
                $scatterReels = array_fill(0, $REELCOUNT, 0);
                
                // set scatter position for tumble symbols
                if ($proposedScatterCount > 0) {
                    /* scatter symbols in the last spin */
                    $remainingCount = array_reduce($reels['symbols'], function($carry, $symbols) use ($S_SCATTER) {
                        $carry += count(array_keys($symbols, $S_SCATTER));
                        return $carry;
                    }, 0);

                    /* determine random position for necessary amount */
                    if ($proposedScatterCount > $remainingCount) {
                        $proposedCount = $proposedScatterCount - $remainingCount;
                        if (count($tumbleSymbols) < $proposedCount) {
                            $proposedCount = count($tumbleSymbols);
                        }

                        $scatterPoses = array_rand($tumbleSymbols, $proposedCount);
                    }
                }
                
                // tumble symbol remove, win
                foreach ($tumbleSymbols as $key => $value) {
                    [$pos, $symbol] = explode(",", $value);
                    $reelPos = intdiv($pos, $REELCOUNT);
                    $reelId = $pos % $REELCOUNT;
                    $reels['symbols'][$reelId][$reelPos] = $S_REMOVED;

                    /* scatter reel info */
                    if ((is_array($scatterPoses) && array_search($key, $scatterPoses) !== false) || $scatterPoses == $key) {
                        $scatterReels[$reelId] += 1;
                    }
                }

                // if ($proposedWildCount > 0) {
                //     $wild_available_reels = array_filter($reels['symbols'], function ($symbols, $reelId) use ($S_REMOVED, $S_WILD, $S_SCATTER) {
                //         return ($reelId != 0) && ($reelId != 5) && 
                //                 (array_search($S_REMOVED, $symbols) !== false) && 
                //                 (array_search($S_WILD, $symbols) === false) && 
                //                 (array_search($S_SCATTER, $symbols) === false);
                //     }, ARRAY_FILTER_USE_BOTH);

                //     if (count($wild_available_reels) < $proposedWildCount) {
                //         $proposedWildCount = count($wild_available_reels);
                //     }

                //     $wild_reels = array_rand($wild_available_reels, $proposedWildCount);
                //     $wild_reels = is_array($wild_reels) ? $wild_reels : [$wild_reels];
                // }

                for ($reelId=0; $reelId < $REELCOUNT; $reelId++) { 
                    // skip if there is no removed tumble spin in the reel
                    $remainingReelSymbols = array_diff($reels['symbols'][$reelId], [$S_REMOVED]);
                    $removedSymbolCount = $MAXSYMBOLCOUNT - count($remainingReelSymbols);
                    
                    if ($removedSymbolCount == 0) {
                        continue;
                    }

                    // add typical symbols for removed tumble symbols
                    for ($i=0; $i < $removedSymbolCount; $i++) { 
                        if ($scatterReels[$reelId] > 0) {
                            /* generate scatter for tumble bonus */
                            array_unshift($remainingReelSymbols, $S_SCATTER);
                            $scatterReels[$reelId] -= 1;
                        }
                        else {
                            /* generate typical symbols */
                            if ($minorWin) {
                                array_unshift($remainingReelSymbols, random_int(8, 12));
                            }
                            else {
                                array_unshift($remainingReelSymbols, random_int(3, 12));
                            }
                        }
                    }
                    
                    $reels['symbols'][$reelId] = array_values($remainingReelSymbols);    
                }

                $reels['symbolsAfter'] = explode(",", $lastSpin->sb);
                $reels['symbolsBefore'] = explode(",", $lastSpin->sa);
                $reels['reelSetId'] = $lastSpin->reel_set;
            }
            else {
                /* non-tumble spion */

                if (array_key_exists($slotEvent, $this->ReelSetMap)) {
                    $reelsetIds = $this->ReelSetMap[$slotEvent];
                }
                else {
                    $reelsetIds = $this->ReelSetMap['spin'];
                }

                $randomId = random_int(0, count($reelsetIds) - 1);
                $reelSetId = $reelsetIds[$randomId];

                foreach( [
                    'reelStrip'.$reelSetId.'_0', 
                    'reelStrip'.$reelSetId.'_1', 
                    'reelStrip'.$reelSetId.'_2', 
                    'reelStrip'.$reelSetId.'_3', 
                    'reelStrip'.$reelSetId.'_4', 
                    'reelStrip'.$reelSetId.'_5', 
                ] as $index => $reelStrip ) 
                {
                    if( is_array($this->$reelStrip) && count($this->$reelStrip) > 0 ) 
                    {
                        $basePosOfReels[$index] = random_int(3, count($this->$reelStrip) - 3);
                    }
                }
                
                $scatterReels = [];
                $scatterCount = 0;

                $wildReels = [];
                $wildCount = 0;

                /* reel generation */
                $rand = random_int(0, 1);
                foreach( $basePosOfReels as $reelId => $basePos ) 
                {
                    if ($minorWin && $reelId == 0) {
                        $symbolCount = random_int(2, 3);
                        $lowSymbol = ($rand == 0 ? 12 : 7);
                        $highSymbol = ($rand == 0 ? 8 : 4);
                        
                        $symbols = $this->GenerateRandomSymbols($symbolCount, $lowSymbol, $highSymbol);
                    }
                    else if ($minorWin && $reelId == 1) {
                        $symbolCount = random_int(3, 5);
                        $lowSymbol = ($rand == 1 ? 12 : 7);
                        $highSymbol = ($rand == 1 ? 8 : 4);

                        $symbols = $this->GenerateRandomSymbols($symbolCount, $lowSymbol, $highSymbol);
                    }
                    else {
                        /* pick random symbol arrays */
                        $symbols = $this->SliceSymbols($basePos, $reelSetId, $reelId);
                    }
                    
                    $reels['symbols'][$reelId] = $symbols;
                    array_push($reels['symbolsBefore'], random_int(3, 12));
                    array_push($reels['symbolsAfter'], random_int(3, 12));

                    /* use in scatter generation */
                    $count = count(array_keys($reels['symbols'][$reelId], $S_SCATTER));
                    $scatterCount += $count;

                    if ($count > 0) {
                        array_push($scatterReels, $reelId);
                    }

                    /* use in wild generation */
                    $count = count(array_keys($reels['symbols'][$reelId], $S_WILD));
                    $wildCount += $count;

                    if ($count > 0) {
                        array_push($wildReels, $reelId);
                    }
                }

                /* check scatter sy mbol */
                if ($proposedScatterCount > $scatterCount) {
                    /* generate necessary scatter symbols */
                    $needCount = $proposedScatterCount - $scatterCount;
                    for ($i=0; $i < $needCount; $i++) { 
                        while (in_array(($randReelId = random_int(0, $REELCOUNT - 1)), $scatterReels));
                        
                        $availablePoses = array_where($reels['symbols'][$randReelId], function ($symbol, $key) use ($S_BLANK, $S_WILD) {
                            return $symbol != $S_BLANK && $symbol != $S_WILD;
                        });

                        if (count($availablePoses) === 0 ) {
                            $i--;
                            continue;
                        }

                        $randomPos = array_rand($availablePoses);         
                        $reels['symbols'][$randReelId][$randomPos] = $S_SCATTER;

                        array_push($scatterReels, $randReelId);
                    }    
                }
                else if ($proposedScatterCount < $scatterCount) {
                    /* remove unnecessary bonus symbol in general spin */
                    $removableCount = $scatterCount - $proposedScatterCount;

                    for ($reelId=0; $reelId < $REELCOUNT; $reelId++) { 
                        $count = count(array_keys($reels['symbols'][$reelId], $S_SCATTER));

                        if ($count > 0) {
                            $reels['symbols'][$reelId] = array_map(function($symbol) use ($S_SCATTER) {
                                return $symbol == $S_SCATTER ? random_int(8, 12) : $symbol;
                            }, $reels['symbols'][$reelId]);

                            $removableCount -= $count;
                        }

                        /* all removed */
                        if ($removableCount <= 0) {
                            break;
                        }
                    }
                }

                /* WILD symbol check */
                if ($proposedWildCount > $wildCount) {
                    /* generate necessary wild symbol in bonus spin */
                    $needCount = $proposedWildCount - $wildCount;
                    for ($i=0; $i < $needCount; $i++) { 
                        while (in_array(($randReelId = random_int(1, $REELCOUNT - 2)), $wildReels));
                        
                        $availablePoses = array_where($reels['symbols'][$randReelId], function ($symbol, $key) use ($S_BLANK, $S_SCATTER) {
                            return $symbol != $S_BLANK && $symbol != $S_SCATTER;
                        });

                        if (count($availablePoses) === 0 ) {
                            $i--;
                            continue;
                        }

                        $randomPos = array_rand($availablePoses);         
                        $reels['symbols'][$randReelId][$randomPos] = $S_WILD;

                        array_push($wildReels, $randReelId);
                    }    
                }
                else if ($proposedWildCount < $wildCount) {
                    /* remove unnecessary scatter symbols in general spin */
                    $removableCount = $wildCount - $proposedWildCount;

                    for ($reelId=1; $reelId < $REELCOUNT - 1; $reelId++) { 
                        $count = count(array_keys($reels['symbols'][$reelId], $S_WILD));

                        if ($count > 0) {
                            $reels['symbols'][$reelId] = array_map(function($symbol) use ($S_WILD) {
                                return $symbol == $S_WILD ? random_int(8, 12) : $symbol;
                            }, $reels['symbols'][$reelId]);

                            $removableCount -= $count;
                        }

                        /* all removed */
                        if ($removableCount <= 0) {
                            break;
                        }
                    }
                }

                $reels['reelSetId'] = $reelSetId;
            }

            /* reel table flat */
            $flatSymbols = [];
            $flatScatterSymbols = [];
            foreach ($reels['symbols'] as $reelId => $symbols) {
                foreach ($symbols as $k => $symbol) {
                    $flatPos = $reelId + $k * $REELCOUNT;
                    $flatSymbols[$flatPos] = $symbol;

                    if ($symbol == $S_SCATTER) {
                        array_push($reels['scatterSymbols'], $flatPos);
                    }
                    else if ($symbol == $S_WILD) {
                        array_push($reels['wildSymbols'], $flatPos);
                    }
                }
            }
            ksort($flatSymbols);

            $reels['flatSymbols'] = $flatSymbols;
            $reels['wildMultiplier'] = $this->GenerateWildMultiplier($slotEvent, $availableMultipliers);

            return $reels;
        }

        public function isValidReels($reels) {
            $highLevelSymbolCount = array_reduce($reels['flatSymbols'], function($carry, $symbol) {
                if ($symbol <= 7) {
                    $carry += 1;
                }

                return $carry;
            });

            if ($highLevelSymbolCount > 10){
                return false;
            }

            return true;
        }

        public function GetBonusWinMoney($bet, $scatterCount) {
            $multiplierMap = [
                3 => 3,
                4 => 5,
                5 => 25,
                6 => 100,
            ];

            if (isset($multiplierMap[$scatterCount])) {
                return $multiplierMap[$scatterCount] * $bet;
            }

            return null;
        }

        public function SliceSymbols($basePos, $reelSetId, $reelId) {
            $S_BLANK = 13;
            $REELCOUNT = 6;
            $MAXSYMBOLCOUNT = 7;
            $symbolCount = $this->GetSymbolCount();

            $orgReelSymbols = $this->{'reelStrip'.$reelSetId . '_' . $reelId};
            $orgReelSymbolsCount = count($orgReelSymbols);

            /* basepos random select */
            $startIdx = ($basePos - 5 < 0) ? 0 : ($basePos - 5);
            $length = ($basePos + 5 >= $orgReelSymbolsCount) ? ($orgReelSymbolsCount - $startIdx) : ($basePos + 5 - $startIdx);
            $slicedSymbols = array_slice($orgReelSymbols, $startIdx, $length);
            $randomSymbolIds = array_rand($slicedSymbols, $symbolCount);

            $symbols = array_fill(0, $MAXSYMBOLCOUNT, $S_BLANK);

            foreach ($randomSymbolIds as $idx => $symbolId) {
                $symbols[$idx] = $slicedSymbols[$symbolId];
            }

            return $symbols;
        }

        public function GenerateRandomSymbols($symbolCount, $lowSymbol, $highSymbol) {
            $MAXSYMBOLCOUNT = 7;
            $S_BLANK = 13;
            $symbols = array_fill(0, $MAXSYMBOLCOUNT, $S_BLANK);

            for ($pos=0; $pos < $symbolCount; $pos++) { 
                $symbols[$pos] = random_int($highSymbol, $lowSymbol);
            }

            return $symbols;
        }
    }
}
