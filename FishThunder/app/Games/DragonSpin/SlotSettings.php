<?php 
namespace VanguardLTE\Games\DragonSpin
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

            $this->Paytable['0'] = [0, 0, 0, 20, 50, 100];
            $this->Paytable['1'] = [0, 0, 0, 5, 20, 50];
            $this->Paytable['2'] = [0, 0, 0, 5, 20, 50];
            $this->Paytable['3'] = [0, 0, 0, 5, 20, 50];
            $this->Paytable['4'] = [0, 0, 0, 5, 20, 50];
            $this->Paytable['5'] = [0, 0, 0, 2, 10, 20];
            $this->Paytable['6'] = [0, 0, 0, 2, 10, 20];
            $this->Paytable['7'] = [0, 0, 0, 2, 10, 20];
            $this->Paytable['8'] = [0, 0, 0, 2, 10, 20];
            $this->Paytable['9'] = [0, 0, 0, 2, 10, 20];
            $this->Paytable['11'] = [0, 0, 0, 30, 30, 30];
            
            $this->awardIndices = [
                '0' => [
                    '0' => ['3' => 14,'4' => 24,'5' => 29],
                    '1' => ['3' => 8,'4' => 18,'5' => 28],
                    '2' => ['3' => 7,'4' => 17,'5' => 27],
                    '3' => ['3' => 6,'4' => 16,'5' => 26],
                    '4' => ['3' => 5,'4' => 15,'5' => 25],
                    '5' => ['3' => 4,'4' => 13,'5' => 23],
                    '6' => ['3' => 3,'4' => 12,'5' => 22],
                    '7' => ['3' => 2,'4' => 11,'5' => 21],
                    '8' => ['3' => 1,'4' => 10,'5' => 20],
                    '9' => ['3' => 0,'4' => 9,'5' => 19],
                    '11' => ['3' => 30]
                ],
                '1' => [
                    '0' => ['3' => 14,'4' => 24,'5' => 29],
                    '1' => ['3' => 8,'4' => 18,'5' => 28],
                    '2' => ['3' => 7,'4' => 17,'5' => 27],
                    '3' => ['3' => 6,'4' => 16,'5' => 26],
                    '4' => ['3' => 5,'4' => 15,'5' => 25],
                    '5' => ['3' => 4,'4' => 13,'5' => 23],
                    '6' => ['3' => 3,'4' => 12,'5' => 22],
                    '7' => ['3' => 2,'4' => 11,'5' => 21],
                    '8' => ['3' => 1,'4' => 10,'5' => 20],
                    '9' => ['3' => 0,'4' => 9,'5' => 19],
                    '11' => ['3' => 30]
                ],
                '3' => [
                    '0' => ['4' => 14,'5' => 19],
                    '1' => ['4' => 8,'5' => 18],
                    '2' => ['4' => 7,'5' => 17],
                    '3' => ['4' => 6,'5' => 16],
                    '4' => ['4' => 5,'5' => 15],
                    '5' => ['4' => 4,'5' => 13],
                    '6' => ['4' => 3,'5' => 12],
                    '7' => ['4' => 2,'5' => 11],
                    '8' => ['4' => 1,'5' => 10],
                    '9' => ['4' => 0,'5' => 9],
                ],
            ];
            
            $this->reelStrips = [];
            
            
            
            //reel strips of Balley games are inversed, so reel strip index must be returned as (length - position)
            
            $this->reelStrips['Reels01']=[9,7,8,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,8,4,5,8,9,2,6,3,5,7,4,5,9,2,5,4,7,8,5,7,9,10,10,10,10,10,10,10,10,10,10,10,4,6,3,4,5,6,3,5,4,8,5,6,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,9,2,6,8,7,1,6,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,2,4,3,9,7,5,3,6,9,4,7,9,8,2,4,7,5,9,3,5,2];
            $this->reelStrips['Reels02']=[7,3,2,7,4,3,11,5,2,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,5,6,11,7,3,8,11,5,7,8,6,2,11,6,9,8,4,6,9,5,1,6,5,7,3,5,9,7,11,5,9,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,9,3,11,8,4,5,9,11,4,1,2,7,1,11,3,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,11,7,8,5,11,6,5,7,8];
            $this->reelStrips['Reels03']=[5,11,6,1,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,6,3,4,3,6,11,5,8,4,10,10,10,10,10,10,10,10,10,10,10,10,10,10,9,8,2,1,6,9,8,6,4,7,8,5,11,9,7,8,10,10,10,10,10,10,10,10,10,10,10,10,7,9,5,6,7,4,9,6,11,9,4,3,11,4,7,6,5,2,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,8,4,1,5,2,11,3,6,7,4,2,8];
            $this->reelStrips['Reels04']=[6,11,1,7,6,11,7,8,10,10,10,10,8,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,8,5,11,2,5,11,6,9,1,2,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,2,11,5,6,8,5,11,9,6,7,9,4,3,8,7,9,5,2,7,11,5,6,3,1,10,10,10,10,10,10,10,10,10,10,10,10,10,10,5,11,8,6,4,11,5,6,9,11,6,5,11,3];
            $this->reelStrips['Reels05']=[3,7,9,6,4,8,7,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,9,5,8,3,2,4,1,8,2,5,6,2,7,8,5,4,1,8,7,9,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,9,5,6,8,5,7,9,5,6,7,3,6,5,1,6,5,3,2,7,8,2,9,10,10,10,10,10,10,10,10,10,10,7,1,6,5,3];

            $this->reelStrips['Reels11']=[5,6,9,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,6,8,7,5,10,10,10,10,6,8,7,1,4,9,2,6,3,2,9,5,8,7,1,4,8,5,3,6,9,5,7];
            $this->reelStrips['Reels12']=[5,7,2,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,5,6,4,9,10,10,10,10,9,7,3,6,8,1,5,6,9,8,3,6,4,7,8,7,5,2,9,1,5,6,8];
            $this->reelStrips['Reels13']=[6,5,4,8,7,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,6,9,1,6,9,10,10,10,10,10,10,10,6,8,7,5,9,7,8,5,3,2];
            $this->reelStrips['Reels14']=[6,4,5,1,8,7,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,6,9,8,5,6,9,10,10,10,10,10,10,10,6,8,7,5,3,7,9,2];
            $this->reelStrips['Reels15']=[7,6,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,6,9,7,8,5,6,9,10,10,10,10,10,10,10,8,7,5,1,9,3,2,6,5,4,8];

            $this->reelStrips['Reels21']=[6,8,1,5,8,1,5,6,10,10,10,10,10,10,5,6,0,7,5,8,10,10,10,10,10,10,10,10,6,7,0,9,6,8,7,5,8,0,7,5,2,7,9,0,7,3,9,6,4,7,3,6,0,5,4,1,4,9,2,9,3,8,9,4,2];
            $this->reelStrips['Reels22']=[6,6,6,8,8,8,0,0,0,5,5,5,8,8,8,1,1,1,10,10,10,10,10,10,10,10,10,9,9,9,5,5,5,6,6,6,0,0,0,6,6,6,8,8,8,10,10,10,10,10,10,10,10,10,9,9,9,7,7,7,5,5,5,6,6,6,7,7,7,1,1,1,5,5,5,10,10,10,10,10,10,10,10,10,6,6,6,7,7,7,8,8,8,5,5,5,6,6,6,0,0,0,7,7,7,5,5,5,8,8,8,9,9,9,7,7,7,5,5,5,2,2,2,7,7,7,9,9,9,0,0,0,7,7,7,3,3,3,9,9,9,6,6,6,4,4,4,7,7,7,3,3,3,6,6,6,6,6,6,5,5,5,4,4,4,2,2,2,4,4,4,9,9,9,0,0,0,9,9,9,3,3,3,8,8,8,9,9,9,5,5,5,2,2,2];
            $this->reelStrips['Reels23']=[6,6,6,8,8,8,0,0,0,5,5,5,8,8,8,1,1,1,10,10,10,10,10,10,10,10,10,9,9,9,5,5,5,6,6,6,0,0,0,6,6,6,8,8,8,10,10,10,10,10,10,10,10,10,9,9,9,7,7,7,5,5,5,6,6,6,7,7,7,1,1,1,5,5,5,10,10,10,10,10,10,10,10,10,6,6,6,7,7,7,8,8,8,5,5,5,6,6,6,0,0,0,7,7,7,5,5,5,8,8,8,9,9,9,7,7,7,5,5,5,2,2,2,7,7,7,9,9,9,0,0,0,7,7,7,3,3,3,9,9,9,6,6,6,4,4,4,7,7,7,3,3,3,6,6,6,6,6,6,5,5,5,4,4,4,2,2,2,4,4,4,9,9,9,0,0,0,9,9,9,3,3,3,8,8,8,9,9,9,5,5,5,2,2,2];
            $this->reelStrips['Reels24']=[6,6,6,8,8,8,0,0,0,5,5,5,8,8,8,1,1,1,10,10,10,10,10,10,10,10,10,9,9,9,5,5,5,6,6,6,0,0,0,6,6,6,8,8,8,10,10,10,10,10,10,10,10,10,9,9,9,7,7,7,5,5,5,6,6,6,7,7,7,1,1,1,5,5,5,10,10,10,10,10,10,10,10,10,6,6,6,7,7,7,8,8,8,5,5,5,6,6,6,0,0,0,7,7,7,5,5,5,8,8,8,9,9,9,7,7,7,5,5,5,2,2,2,7,7,7,9,9,9,0,0,0,7,7,7,3,3,3,9,9,9,6,6,6,4,4,4,7,7,7,3,3,3,6,6,6,6,6,6,5,5,5,4,4,4,2,2,2,4,4,4,9,9,9,0,0,0,9,9,9,3,3,3,8,8,8,9,9,9,5,5,5,2,2,2];
            $this->reelStrips['Reels25']=[6,8,1,5,8,1,5,6,10,10,10,10,10,10,5,6,9,7,5,8,10,10,10,10,10,10,10,10,6,7,8,5,9,8,0,5,8,6,7,5,2,7,9,0,7,3,9,6,4,7,3,6,7,0,4,1,4,9,2,9,3,8,9,5,2];
                        
            
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
            $this->SymbolGame = [0,1,2,3,4,5,6,7,8,9,10,11,12];            
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
        public function GetReelStrips($winType, $reelName, $type, $g, $lastRp)
        {
            $arrReels = [
                $reelName.'1', 
                $reelName.'2', 
                $reelName.'3',
                $reelName.'4',
                $reelName.'5',
            ];

            $endLen = 3;
            if($reelName == 'Reels2')
                $endLen = 12;
            $prs = [];
            $reel = [
                'rp' => []
            ];
            
            if($winType != 'bonus')
            {
                if($winType == 'win')
                {
                    $sym = rand(0, 10);
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
                $symb = '11';                
                $reelsId = [];
                foreach($arrReels as $index => $reelStrip ) 
                {
                    if( is_array($this->reelStrips[$reelStrip]) && count($this->reelStrips[$reelStrip]) > 0 ) 
                    {
                        $prs[$index + 1] = $this->GetRandomScatterPos($this->reelStrips[$reelStrip], $endLen, $symb);
                        $reelsId[] = $index + 1;
                    }
                }

                $scattersCnt = 5;                
                for( $i = 0; $i < count($reelsId); $i++ ) 
                {
                    if( $i < $scattersCnt ) 
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
                if($reelName == 'Reels2' && $index == 3) //to make blast reel, reel position must be divided by 3 completely
                {
                    $value = $value - $value % 3;
                    if($g > 0)
                        $value = $lastRp + 3;
                }
                $key[-1] = $key[count($key) - 1];
                $reel['reel' . $index][0] = $key[$value];
                $reel['reel' . $index][1] = $key[$value + 1];
                $reel['reel' . $index][2] = $key[$value + 2];                
                $reel['rp'][] = $value + 1;
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
            $linesId = [[1,1,1,1,1], [0,0,0,0,0], [2,2,2,2,2], [0,1,2,1,0], [2,1,0,1,2],
                        [1,0,0,0,1], [1,2,2,2,1], [0,0,1,2,2], [2,2,1,0,0], [1,0,1,0,1],
                        [1,2,1,2,1], [0,1,1,1,2], [2,1,1,1,0], [1,1,0,1,2], [1,1,2,1,0],
                        [0,1,0,1,0], [2,1,2,1,2], [0,0,2,0,0], [2,2,0,2,2], [1,0,2,0,1],
                        [1,2,0,2,1], [0,2,0,2,0], [2,0,2,0,2], [0,2,2,2,0], [2,0,0,0,2],
                        [0,2,1,2,0], [2,0,1,0,2], [1,1,2,1,1], [1,1,0,1,1], [0,2,0,1,1]
                    ];
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
