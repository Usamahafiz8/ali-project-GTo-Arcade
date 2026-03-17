<?php 
namespace VanguardLTE\Games\Zeus3
{

    use VanguardLTE\Lib\BasicSlotSettings;
    use VanguardLTE\Lib\JackpotHandler;

    class SlotSettings extends BasicSlotSettings
    {
        public $winRate = 40;
        public $playerId = null;
        public $splitScreen = null;
        public $reelStrips = null;       
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
        
        public function __construct($sid, $playerId)
        {
            $this->slotId = $sid;
            $this->playerId = $playerId;
            $user = \VanguardLTE\User::lockForUpdate()->find($this->playerId);
            $this->user = $user;
            $this->shop_id = $user->shop_id;
            $gamebank = \VanguardLTE\GameBank::where(['shop_id' => $this->shop_id])->lockForUpdate()->get();
            $game = \VanguardLTE\Game::where([
                'name' => $this->slotId, 
                'shop_id' => $this->shop_id
            ])->first();
            $this->shop = \VanguardLTE\Shop::find($this->shop_id);
            $this->game = $game;
            $this->MaxWin = $this->shop->max_win;
            $this->increaseRTP = 1;
            $this->CurrentDenom = $this->game->denomination;
            $this->scaleMode = 0;
            $this->numFloat = 0;
            
            // 0: Wild
            // 1: Red dragon
            // 2: Blue dragon
            // 3: Purple dragon
            // 4: Green dragon
            // 5: A
            // 6: K
            // 7: Q
            // 8: J
            // 9: Ten
            // 10: mystery symbol to replace
            // 11: bonus            

            $this->Paytable['0'] = [0, 0, 0, 1, 3, 10, 50];
            $this->Paytable['1'] = [0, 0, 0, 1, 3, 10, 60];
            $this->Paytable['2'] = [0, 0, 0, 1, 3, 10, 70];
            $this->Paytable['3'] = [0, 0, 0, 1, 3, 15, 80];
            $this->Paytable['4'] = [0, 0, 0, 1, 3, 20, 100];
            $this->Paytable['5'] = [0, 0, 0, 1, 3, 20, 150];
            $this->Paytable['6'] = [0, 0, 0, 1, 4, 25, 200];
            $this->Paytable['7'] = [0, 0, 0, 2, 5, 30, 250];
            $this->Paytable['8'] = [0, 0, 0, 2, 6, 40, 300];
            $this->Paytable['12'] = [0, 0, 0, 0, 0, 0, 0];
            $this->Paytable['14'] = [0, 0, 0, 3, 8, 50, 400];
            
            $this->awardIndices = [
                '0' => ['3' => 0,'4' => 10,'5' => 20, '6' => 30],
                '1' => ['3' => 1,'4' => 11,'5' => 21, '6' => 31],
                '2' => ['3' => 2,'4' => 12,'5' => 22, '6' => 32],
                '3' => ['3' => 3,'4' => 13,'5' => 23, '6' => 33],
                '4' => ['3' => 4,'4' => 14,'5' => 24, '6' => 34],
                '5' => ['3' => 5,'4' => 15,'5' => 25, '6' => 35],
                '6' => ['3' => 6,'4' => 16,'5' => 26, '6' => 36],
                '7' => ['3' => 7,'4' => 17,'5' => 27, '6' => 37],
                '8' => ['3' => 8,'4' => 18,'5' => 28, '6' => 38],
                '12' => ['2' => 40, '3' => 41,'4' => 42,'5' => 43],
                '14' => ['3' => 9,'4' => 19,'5' => 29, '6' => 39],                
            ];
            
            $this->reelStrips = [];
            
            $this->reelStrips['Reels01']=[1,1,9,11,1,1,8,8,13,0,0,5,5,13,1,1,4,4,7,7,13,0,0,3,3,2,2,13,4,4,2,2,6,6,0,0,3,3];
            $this->reelStrips['Reels02']=[4,4,4,2,2,2,1,1,1,6,6,6,4,4,4,7,7,7,13,1,1,1,3,3,3,13,8,8,8,0,0,0,13,5,5,5,0,0,0,9,10,10,10,11,0,0,0,8,8,8,1,1,1,12,2,2,2,3,3,3,13,4,4,4,7,7,7,0,0,0,3,3,3,1,1,1];
            $this->reelStrips['Reels03']=[0,0,0,0,3,3,3,3,12,1,1,1,1,4,4,4,4,13,1,1,1,1,4,4,4,4,6,6,6,6,2,2,2,2,9,10,10,10,10,10,11,2,2,2,2,4,4,4,4,13,0,0,0,0,3,3,3,3,0,0,0,0,12,3,3,3,3,0,0,0,0,5,5,5,5,13,6,6,6,6,7,7,7,7,2,2,2,2,12,1,1,1,1,7,7,7,7,2,2,2,2,13,8,8,8,8,1,1,1,1,5,5,5,5];
            $this->reelStrips['Reels04']=[1,1,1,1,1,6,6,6,6,6,5,5,5,5,5,13,8,8,8,8,8,0,0,0,0,0,9,10,10,10,10,10,11,0,0,0,0,0,6,6,6,6,6,1,1,1,1,1,12,0,0,0,0,0,5,5,5,5,5,1,1,1,1,1,12,3,3,3,3,3,8,8,8,8,8,4,4,4,4,4,13,7,7,7,7,7,2,2,2,2,2,9,10,10,10,10,11,2,2,2,2,2,12,0,0,0,0,0,3,3,3,3,3,7,7,7,7,7];
            $this->reelStrips['Reels05']=[1,1,1,1,1,1,12,3,3,3,3,3,3,13,5,5,5,5,5,5,13,4,4,4,4,4,4,13,7,7,7,7,7,7,13,8,8,8,8,8,8,0,0,0,0,0,0,9,10,10,10,10,10,11,0,0,0,0,0,0,12,1,1,1,1,1,1,13,5,5,5,5,5,5,13,4,4,4,4,4,4,13,8,8,8,8,8,8,13,7,7,7,7,7,7,13,2,2,2,2,2,2,9,10,10,10,10,10,11,2,2,2,2,2,2,6,6,6,6,6,6];
            $this->reelStrips['Reels06']=[1,1,1,1,1,1,1,6,6,6,6,6,6,6,5,5,5,5,5,5,5,13,8,8,8,8,8,8,8,0,0,0,0,0,0,0,9,10,10,10,10,10,10,10,10,11,0,0,0,0,0,0,0,13,6,6,6,6,6,6,6,1,1,1,1,1,1,1,12,0,0,0,0,0,0,0,5,5,5,5,5,5,5,1,1,1,1,1,1,1,3,3,3,3,3,3,3,13,8,8,8,8,8,8,8,4,4,4,4,4,4,4,7,7,7,7,7,7,7,2,2,2,2,2,2,2,9,10,10,10,10,10,10,10,11,2,2,2,2,2,2,2,12,0,0,0,0,0,0,0,3,3,3,3,3,3,3,13,7,7,7,7,7,7,7];

            $this->reelStrips['Reels11']=[3,3,3,3,3,3,3,9,10,10,10,10,10,11,3,3,3,3,3,3,3,0,0,0,0,0,0,0,12,2,2,2,2,2,2,2,5,5,5,5,5,5,5,13,8,8,8,8,8,8,8,4,4,4,4,4,4,4,7,7,7,7,7,7,7,13,2,2,2,2,2,2,2,1,1,1,1,1,1,1,4,4,4,4,4,4,4,13,2,2,2,2,2,2,2,9,10,10,10,10,10,11,2,2,2,2,2,2,2,1,1,1,1,1,1,1,12,0,0,0,0,0,0,0,6,6,6,6,6,6,6];
            $this->reelStrips['Reels12']=[0,0,0,0,0,0,13,6,6,6,6,6,6,2,2,2,2,2,2,9,10,10,10,10,11,2,2,2,2,2,2,6,6,6,6,6,6,13,1,1,1,1,1,1,7,7,7,7,7,7,5,5,5,5,5,5,0,0,0,0,0,0,9,10,10,10,10,10,10,11,0,0,0,0,0,0,8,8,8,8,8,8,1,1,1,1,1,1,12,3,3,3,3,3,3,5,5,5,5,5,5,13,4,4,4,4,4,4,7,7,7,7,7,7,8,8,8,8,8,8];
            $this->reelStrips['Reels13']=[5,5,5,5,5,0,0,0,0,0,12,1,1,1,1,1,4,4,4,4,4,13,8,8,8,8,8,1,1,1,1,1,13,6,6,6,6,6,2,2,2,2,2,9,10,10,10,11,2,2,2,2,2,4,4,4,4,4,0,0,0,0,0,3,3,3,3,3,13,0,0,0,0,0,4,4,4,4,4,1,1,1,1,1,5,5,5,5,5,13,6,6,6,6,6,7,7,7,7,7,1,1,1,1,1,12,0,0,0,0,0,7,7,7,7,7,3,3,3,3,3,9,10,10,10,11,3,3,3,3,3,2,2,2,2,2,5,5,5,5,5];
            $this->reelStrips['Reels14']=[1,1,1,1,12,3,3,3,3,1,1,1,1,13,5,5,5,5,0,0,0,0,9,10,10,11,0,0,0,0,6,6,6,6,1,1,1,1,12,0,0,0,0,5,5,5,5,6,6,6,6,3,3,3,3,13,1,1,1,1,5,5,5,5,7,7,7,7,2,2,2,2,0,0,0,0,12,2,2,2,2,1,1,1,1,13,7,7,7,7,0,0,0,0,2,2,2,2,13,8,8,8,8,0,0,0,0,4,4,4,4,2,2,2,2];
            $this->reelStrips['Reels15']=[1,1,1,12,2,2,2,5,5,5,13,4,4,4,7,7,7,13,8,8,8,0,0,0,9,10,11,0,0,0,5,5,5,3,3,3,12,1,1,1,6,6,6,2,2,2,12,3,3,3,7,7,7,13,8,8,8,4,4,4,13,6,6,6];
            $this->reelStrips['Reels16']=[5,5,13,6,6,1,1,4,4,7,7,8,8,0,0,9,11,0,0,3,3,5,5,6,6,1,1,4,4,2,2,7,7,13,8,8,2,2,3,3];

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
            $this->jpgs = \VanguardLTE\JPG::where('shop_id', $this->shop_id)->get();
          
            $this->Bet = explode(',', $game->bet);
            $this->Balance = $user->balance;
            $this->SymbolGame = [0,1,2,3,4,5,6,7,8,12,14];
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
                $tmpLog = json_decode($log->str);
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
            //     $prc_b = 45;
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
        
        public function GetSpinSettings($garantType = 'bet', $allbet)
        {
            $this->AllBet = $allbet;
            return $this->SpinSettings($garantType, $this->AllBet);            
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
        
        public function GetReelStrips($winType, $reelName, $type)        
        {
            $arrReels = [
                $reelName.'1', 
                $reelName.'2', 
                $reelName.'3',
                $reelName.'4',
                $reelName.'5',
                $reelName.'6',
            ];

            $endLen = 8;
            $prs = [];
            $reel = [
                'rp' => []
            ];
            if($winType != 'bonus')
            {
                if($winType == 'win')
                {
                    $sym = rand(0, 8);
                    foreach($arrReels as $index => $reelStrip ) 
                    {
                        if( is_array($this->reelStrips) && count($this->reelStrips[$reelStrip]) > 0 ) 
                        {
                            $prs[$index + 1] = $this->GetSymbolPos($this->reelStrips[$reelStrip], $sym, $endLen);
                        }
                    }
                }
                else
                {
                    foreach( $arrReels as $index => $reelStrip ) 
                    {
                        if( is_array($this->reelStrips) && count($this->reelStrips[$reelStrip]) > 0 ) 
                        {
                            $prs[$index + 1] = mt_rand(1, count($this->reelStrips[$reelStrip]) - $endLen);
                        }
                    }
                }                
            }
            else
            {
                $symb = 12;
                $reelsId = [];
                foreach($arrReels as $index => $reelStrip ) 
                {
                    if( is_array($this->reelStrips[$reelStrip]) && count($this->reelStrips[$reelStrip]) > 0 ) 
                    {
                        $prs[$index + 1] = $this->GetRandomScatterPos($this->reelStrips[$reelStrip], $endLen, $symb);
                        $reelsId[] = $index + 1;
                    }
                }

                $scattersCnt = 3;                
                for( $i = 0; $i < count($reelsId); $i++ ) 
                {
                    if( $i >= 1 && $i <= $scattersCnt ) 
                    {
                        $prs[$reelsId[$i]] = $this->GetRandomScatterPos($this->reelStrips[$reelName.$reelsId[$i]], $endLen, $symb);
                    }
                    else
                    {
                        $prs[$reelsId[$i]] = rand(1, count($this->reelStrips[$reelName.$reelsId[$i]]) - $endLen);
                    }
                }          
            }

            
            foreach( $prs as $index => $value )
            {
                $key = $this->reelStrips[$reelName.$index];
                $key[-1] = $key[count($key) - 1];
                $reel['reel' . $index][0] = $key[$value];
                $reel['reel' . $index][1] = $key[$value + 1];
                $reel['reel' . $index][2] = $key[$value + 2];                
                $reel['reel' . $index][3] = $key[$value + 3];                
                $reel['reel' . $index][4] = $key[$value + 4];                
                $reel['reel' . $index][5] = $key[$value + 5];                
                $reel['reel' . $index][6] = $key[$value + 6];                
                $reel['rp'][] = $value + 3;
            }

            if($type != 'freespin')
            {
                for($i = 0; $i < 6; $i++)
                for($j = $i + 2; $j < 7; $j++)
                    $reel['reel'.($i+1)][$j] = -1;
            }
            else
            {
                for($i = 1; $i < 6; $i++)
                for($j = 7 - $i; $j < 7; $j++)
                    $reel['reel'.($i+1)][$j] = -1;
            }
            
            return $reel;
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

        public function GetPaylines()
        {
            return [[0,0,0,0,0,0],[0,0,0,0,0,1],[0,0,0,0,1,1],[0,0,0,0,1,2],[0,0,0,1,1,1],[0,0,0,1,1,2],[0,0,0,1,2,2],[0,0,0,1,2,3],[0,0,1,1,1,1],[0,0,1,1,1,2],[0,0,1,1,2,2],[0,0,1,1,2,3],[0,0,1,2,2,2],[0,0,1,2,2,3],[0,0,1,2,3,3],[0,0,1,2,3,4],[0,0,2,2,2,2],[0,0,2,2,2,3],[0,0,2,2,3,3],[0,0,2,2,3,4],[0,0,2,3,3,3],[0,0,2,3,3,4],[0,0,2,3,4,4],[0,0,2,3,4,5],[0,0,3,3,3,3],[0,0,3,3,3,4],[0,0,3,3,4,4],[0,0,3,3,4,5],[0,0,3,4,4,4],[0,0,3,4,4,5],[0,0,3,4,5,5],[0,0,3,4,5,6],[0,1,0,0,0,0],[0,1,0,0,0,1],[0,1,0,0,1,1],[0,1,0,0,1,2],[0,1,0,1,1,1],[0,1,0,1,1,2],[0,1,0,1,2,2],[0,1,0,1,2,3],[0,1,1,1,1,1],[0,1,1,1,1,2],[0,1,1,1,2,2],[0,1,1,1,2,3],[0,1,1,2,2,2],[0,1,1,2,2,3],[0,1,1,2,3,3],[0,1,1,2,3,4],[0,1,2,2,2,2],[0,1,2,2,2,3],[0,1,2,2,3,3],[0,1,2,2,3,4],[0,1,2,3,3,3],[0,1,2,3,3,4],[0,1,2,3,4,4],[0,1,2,3,4,5],[0,1,3,3,3,3],[0,1,3,3,3,4],[0,1,3,3,4,4],[0,1,3,3,4,5],[0,1,3,4,4,4],[0,1,3,4,4,5],[0,1,3,4,5,5],[0,1,3,4,5,6],[0,2,0,0,0,0],[0,2,0,0,0,1],[0,2,0,0,1,1],[0,2,0,0,1,2],[0,2,0,1,1,1],[0,2,0,1,1,2],[0,2,0,1,2,2],[0,2,0,1,2,3],[0,2,1,1,1,1],[0,2,1,1,1,2],[0,2,1,1,2,2],[0,2,1,1,2,3],[0,2,1,2,2,2],[0,2,1,2,2,3],[0,2,1,2,3,3],[0,2,1,2,3,4],[0,2,2,2,2,2],[0,2,2,2,2,3],[0,2,2,2,3,3],[0,2,2,2,3,4],[0,2,2,3,3,3],[0,2,2,3,3,4],[0,2,2,3,4,4],[0,2,2,3,4,5],[0,2,3,3,3,3],[0,2,3,3,3,4],[0,2,3,3,4,4],[0,2,3,3,4,5],[0,2,3,4,4,4],[0,2,3,4,4,5],[0,2,3,4,5,5],[0,2,3,4,5,6],[1,0,0,0,0,0],[1,0,0,0,0,1],[1,0,0,0,1,1],[1,0,0,0,1,2],[1,0,0,1,1,1],[1,0,0,1,1,2],[1,0,0,1,2,2],[1,0,0,1,2,3],[1,0,1,1,1,1],[1,0,1,1,1,2],[1,0,1,1,2,2],[1,0,1,1,2,3],[1,0,1,2,2,2],[1,0,1,2,2,3],[1,0,1,2,3,3],[1,0,1,2,3,4],[1,0,2,2,2,2],[1,0,2,2,2,3],[1,0,2,2,3,3],[1,0,2,2,3,4],[1,0,2,3,3,3],[1,0,2,3,3,4],[1,0,2,3,4,4],[1,0,2,3,4,5],[1,0,3,3,3,3],[1,0,3,3,3,4],[1,0,3,3,4,4],[1,0,3,3,4,5],[1,0,3,4,4,4],[1,0,3,4,4,5],[1,0,3,4,5,5],[1,0,3,4,5,6],[1,1,0,0,0,0],[1,1,0,0,0,1],[1,1,0,0,1,1],[1,1,0,0,1,2],[1,1,0,1,1,1],[1,1,0,1,1,2],[1,1,0,1,2,2],[1,1,0,1,2,3],[1,1,1,1,1,1],[1,1,1,1,1,2],[1,1,1,1,2,2],[1,1,1,1,2,3],[1,1,1,2,2,2],[1,1,1,2,2,3],[1,1,1,2,3,3],[1,1,1,2,3,4],[1,1,2,2,2,2],[1,1,2,2,2,3],[1,1,2,2,3,3],[1,1,2,2,3,4],[1,1,2,3,3,3],[1,1,2,3,3,4],[1,1,2,3,4,4],[1,1,2,3,4,5],[1,1,3,3,3,3],[1,1,3,3,3,4],[1,1,3,3,4,4],[1,1,3,3,4,5],[1,1,3,4,4,4],[1,1,3,4,4,5],[1,1,3,4,5,5],[1,1,3,4,5,6],[1,2,0,0,0,0],[1,2,0,0,0,1],[1,2,0,0,1,1],[1,2,0,0,1,2],[1,2,0,1,1,1],[1,2,0,1,1,2],[1,2,0,1,2,2],[1,2,0,1,2,3],[1,2,1,1,1,1],[1,2,1,1,1,2],[1,2,1,1,2,2],[1,2,1,1,2,3],[1,2,1,2,2,2],[1,2,1,2,2,3],[1,2,1,2,3,3],[1,2,1,2,3,4],[1,2,2,2,2,2],[1,2,2,2,2,3],[1,2,2,2,3,3],[1,2,2,2,3,4],[1,2,2,3,3,3],[1,2,2,3,3,4],[1,2,2,3,4,4],[1,2,2,3,4,5],[1,2,3,3,3,3],[1,2,3,3,3,4],[1,2,3,3,4,4],[1,2,3,3,4,5],[1,2,3,4,4,4],[1,2,3,4,4,5],[1,2,3,4,5,5],[1,2,3,4,5,6]];
        }

        public function GetFreespinPaylines()
        {
            return [[0,0,0,0,0,0],[1,0,0,0,0,0],[1,1,0,0,0,0],[2,1,0,0,0,0],[1,1,1,0,0,0],[2,1,1,0,0,0],[2,2,1,0,0,0],[3,2,1,0,0,0],[1,1,1,1,0,0],[2,1,1,1,0,0],[2,2,1,1,0,0],[3,2,1,1,0,0],[2,2,2,1,0,0],[3,2,2,1,0,0],[3,3,2,1,0,0],[4,3,2,1,0,0],[2,2,2,2,0,0],[3,2,2,2,0,0],[3,3,2,2,0,0],[4,3,2,2,0,0],[3,3,3,2,0,0],[4,3,3,2,0,0],[4,4,3,2,0,0],[5,4,3,2,0,0],[3,3,3,3,0,0],[4,3,3,3,0,0],[4,4,3,3,0,0],[5,4,3,3,0,0],[4,4,4,3,0,0],[5,4,4,3,0,0],[5,5,4,3,0,0],[6,5,4,3,0,0],[0,0,0,0,1,0],[1,0,0,0,1,0],[1,1,0,0,1,0],[2,1,0,0,1,0],[1,1,1,0,1,0],[2,1,1,0,1,0],[2,2,1,0,1,0],[3,2,1,0,1,0],[1,1,1,1,1,0],[2,1,1,1,1,0],[2,2,1,1,1,0],[3,2,1,1,1,0],[2,2,2,1,1,0],[3,2,2,1,1,0],[3,3,2,1,1,0],[4,3,2,1,1,0],[2,2,2,2,1,0],[3,2,2,2,1,0],[3,3,2,2,1,0],[4,3,2,2,1,0],[3,3,3,2,1,0],[4,3,3,2,1,0],[4,4,3,2,1,0],[5,4,3,2,1,0],[3,3,3,3,1,0],[4,3,3,3,1,0],[4,4,3,3,1,0],[5,4,3,3,1,0],[4,4,4,3,1,0],[5,4,4,3,1,0],[5,5,4,3,1,0],[6,5,4,3,1,0],[0,0,0,0,2,0],[1,0,0,0,2,0],[1,1,0,0,2,0],[2,1,0,0,2,0],[1,1,1,0,2,0],[2,1,1,0,2,0],[2,2,1,0,2,0],[3,2,1,0,2,0],[1,1,1,1,2,0],[2,1,1,1,2,0],[2,2,1,1,2,0],[3,2,1,1,2,0],[2,2,2,1,2,0],[3,2,2,1,2,0],[3,3,2,1,2,0],[4,3,2,1,2,0],[2,2,2,2,2,0],[3,2,2,2,2,0],[3,3,2,2,2,0],[4,3,2,2,2,0],[3,3,3,2,2,0],[4,3,3,2,2,0],[4,4,3,2,2,0],[5,4,3,2,2,0],[3,3,3,3,2,0],[4,3,3,3,2,0],[4,4,3,3,2,0],[5,4,3,3,2,0],[4,4,4,3,2,0],[5,4,4,3,2,0],[5,5,4,3,2,0],[6,5,4,3,2,0],[0,0,0,0,0,1],[1,0,0,0,0,1],[1,1,0,0,0,1],[2,1,0,0,0,1],[1,1,1,0,0,1],[2,1,1,0,0,1],[2,2,1,0,0,1],[3,2,1,0,0,1],[1,1,1,1,0,1],[2,1,1,1,0,1],[2,2,1,1,0,1],[3,2,1,1,0,1],[2,2,2,1,0,1],[3,2,2,1,0,1],[3,3,2,1,0,1],[4,3,2,1,0,1],[2,2,2,2,0,1],[3,2,2,2,0,1],[3,3,2,2,0,1],[4,3,2,2,0,1],[3,3,3,2,0,1],[4,3,3,2,0,1],[4,4,3,2,0,1],[5,4,3,2,0,1],[3,3,3,3,0,1],[4,3,3,3,0,1],[4,4,3,3,0,1],[5,4,3,3,0,1],[4,4,4,3,0,1],[5,4,4,3,0,1],[5,5,4,3,0,1],[6,5,4,3,0,1],[0,0,0,0,1,1],[1,0,0,0,1,1],[1,1,0,0,1,1],[2,1,0,0,1,1],[1,1,1,0,1,1],[2,1,1,0,1,1],[2,2,1,0,1,1],[3,2,1,0,1,1],[1,1,1,1,1,1],[2,1,1,1,1,1],[2,2,1,1,1,1],[3,2,1,1,1,1],[2,2,2,1,1,1],[3,2,2,1,1,1],[3,3,2,1,1,1],[4,3,2,1,1,1],[2,2,2,2,1,1],[3,2,2,2,1,1],[3,3,2,2,1,1],[4,3,2,2,1,1],[3,3,3,2,1,1],[4,3,3,2,1,1],[4,4,3,2,1,1],[5,4,3,2,1,1],[3,3,3,3,1,1],[4,3,3,3,1,1],[4,4,3,3,1,1],[5,4,3,3,1,1],[4,4,4,3,1,1],[5,4,4,3,1,1],[5,5,4,3,1,1],[6,5,4,3,1,1],[0,0,0,0,2,1],[1,0,0,0,2,1],[1,1,0,0,2,1],[2,1,0,0,2,1],[1,1,1,0,2,1],[2,1,1,0,2,1],[2,2,1,0,2,1],[3,2,1,0,2,1],[1,1,1,1,2,1],[2,1,1,1,2,1],[2,2,1,1,2,1],[3,2,1,1,2,1],[2,2,2,1,2,1],[3,2,2,1,2,1],[3,3,2,1,2,1],[4,3,2,1,2,1],[2,2,2,2,2,1],[3,2,2,2,2,1],[3,3,2,2,2,1],[4,3,2,2,2,1],[3,3,3,2,2,1],[4,3,3,2,2,1],[4,4,3,2,2,1],[5,4,3,2,2,1],[3,3,3,3,2,1],[4,3,3,3,2,1],[4,4,3,3,2,1],[5,4,3,3,2,1],[4,4,4,3,2,1],[5,4,4,3,2,1],[5,5,4,3,2,1],[6,5,4,3,2,1]];
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

        public function GetNoWinSpin($reelName)
        {
            $isWin = true;            
            $linesId = $this->GetPaylines();            
            $lines = count($linesId);

            $scatter = "11";
            $wild = ["10"];
            $jackpot = '12';
            while($isWin)
            {               
                $reels = $this->GetReelStrips('none', $reelName, 'bet', 0, []);
                $win = 0;
                              
                for( $k = 0; $k < $lines; $k++ ) 
                {
                    for( $j = 0; $j < count($this->SymbolGame); $j++ ) 
                    {
                        $csym = $this->SymbolGame[$j];
                        $tmpWin = 0;
                        if( $csym == $scatter || !isset($this->Paytable[$csym]) ) 
                        {
                        }
                        else
                        {
                            $s = [];
                            $p0 = $linesId[$k][0];
                            $p1 = $linesId[$k][1];
                            $p2 = $linesId[$k][2];
                            $p3 = $linesId[$k][3];
                            $p4 = $linesId[$k][4];

                            $s[0] = $reels['reel1'][$p0];
                            $s[1] = $reels['reel2'][$p1];
                            $s[2] = $reels['reel3'][$p2];
                            $s[3] = $reels['reel4'][$p3];
                            $s[4] = $reels['reel5'][$p4];                            
                                                                                
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) ) 
                            {
                                $tmpWin = $this->Paytable[$csym][3];                                
                            }
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) ) 
                            {
                                $tmpWin = $this->Paytable[$csym][4];                                
                            }
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) ) 
                            {
                                $tmpWin = $this->Paytable[$csym][5];                                
                            }
                        }
                        $win += $tmpWin;
                    }
                }

                if($win == 0)
                {
                    //calc scatter syms
                    $scatterCnt = 0;
                    $jackpotCnt = 0;
                    for($r = 1; $r <= 5; $r++)
                        for($c = 0; $c < 3; $c++)
                        {
                            if($reels['reel'.$r][$c] == $scatter)
                            {
                                $scatterCnt++;
                            }                            
                            if($reels['reel'.$r][$c] == $jackpot)
                            {
                                $jackpotCnt++;
                            }                 
                        }
                    if($scatterCnt < 3 && $jackpotCnt < 2)
                        $isWin = false;
                }
            }
            return $reels;
        }
    }

}
