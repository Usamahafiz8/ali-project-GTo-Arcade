<?php 
namespace VanguardLTE\Games\BookOfDeadPNG
{

    use VanguardLTE\Lib\BasicSlotSettings;
    use VanguardLTE\Lib\JackpotHandler;

    class SlotSettings extends BasicSlotSettings
    {
        public $winRate = 40;
        public $playerId = null;
        public $splitScreen = null;
        public $reelStrips = null;
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
        public $awardIndices = null;
        public $fsPos = null;
        
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
            
            $this->Paytable['0'] = [0, 0, 0, 5, 25, 100]; //Ten
            $this->Paytable['1'] = [0, 0, 0, 5, 25, 100]; //J
            $this->Paytable['2'] = [0, 0, 0, 5, 25, 100]; //Q
            $this->Paytable['3'] = [0, 0, 0, 5, 40, 150];  //K
            $this->Paytable['4'] = [0, 0, 0, 5, 40, 150];  //A
            $this->Paytable['5'] = [0, 0, 5, 30, 100, 750];  //Bird
            $this->Paytable['6'] = [0, 0, 5, 30, 100, 750];   //Horse
            $this->Paytable['7'] = [0, 0, 5, 40, 400, 2000];   //Statue
            $this->Paytable['8'] = [0, 0, 10, 100, 1000, 5000];   //Man
            $this->Paytable['9'] = [0, 0, 0, 2, 20, 200];   //Scatter
        

            $this->awardIndices = [
                '1' => ['2'=> 0, '3' => 9,'4' => 18,'5' => 27],
                '2' => ['3' => 8,'4' => 17,'5' => 26],
                '3' => ['3' => 7,'4' => 16,'5' => 25],
                '4' => ['3' => 6,'4' => 15,'5' => 24],
                '5' => ['3' => 5,'4' => 14,'5' => 23],
                '6' => ['3' => 4,'4' => 13,'5' => 22],
                '7' => ['3' => 3,'4' => 12,'5' => 21],
                '8' => ['3' => 2,'4' => 11,'5' => 20],
                '9' => ['3' => 1,'4' => 10,'5' => 19],                
            ];

            $this->reelStrips = [];
            
            //reel strips of Balley games are inversed, so reel strip index must be returned as (length - position)
            
            $this->reelStrips['Reels01']=[4,9,3,6,8,3,9,11,1,9,2,8,11,9,7,5,6,4,9,2,6,4,8,5,7,9,11,6,4,5,3,9,8,2,9,8,1,4,7,6,4,8,9,1,4,6,2,8,11,9,2,6,11,7,6,5,9,4,6,8,4,6,1,8];
            $this->reelStrips['Reels02']=[2,5,0,0,0,7,1,4,5,9,7,2,1,4,7,3,0,0,0,4,2,6,11,9,5,10,10,10,2,5,3,10,10,8,3,5,1,6,3,7,11,8,4,7,5,7,2,1,4,3,7,10,10,4,8,3,4,0,6,1,4,7,2,11,4,7];
            $this->reelStrips['Reels03']=[7,4,8,2,10,10,7,1,5,11,6,4,7,0,0,5,3,2,6,0,0,0,3,1,4,11,3,7,2,10,10,3,5,1,8,3,7,11,6,5,0,0,0,7,3,2,7,3,10,10,3,6,2,5,3,9,1,7,5,2,7,11,5,7,4,8,10,10,10,7,1,5,11,6,4,7,1,0,0,0,5,3,2,6,0,0,0,3,7,4,5,3,10,10,9,7,3,5,1,8,3,7,4,6,5,0,0,0,7,3,2,7,3,5,10,10,6,2,5,3,9,1,4,5,2,7,11,9,5,6,2];
            $this->reelStrips['Reels04']=[3,9,0,0,0,7,8,4,3,10,10,10,6,2,5,8,10,10,9,3,11,2,8,4,0,0,0,1,3,5,7,11,9,3,7,11,5,4,11,3,5,10,10,9,2,7,9,1,6,0,4,8,1,7,3,9,2,11,7,3,11,6,8,4,3,9,0,0,0,7,8,4,3,6,11,4,6,2,5,10,10,10,9,3,6,2,8,4,0,0,0,6,3,5,7,11,9,3,7,9,11,4,6,3,5,10,10,10,2,7,9,1,6,0,0,0,4,8,1,7,3,9,11,6,7,3,4,6,8,4,3,6,7,10,10,6,1,7,3,2,7,9,2,8,3,4,8,3,9,5,4,6,5,8,7,5];
            $this->reelStrips['Reels05']=[11,5,6,1,11,6,3,9,1,8,4,11,8,3,5,4,11,3,5,2,7,4,9,11,6,9,2,7,4,5,11,3,5,1,8,4,6,11,5,9,2,11,8,4,7,3,1,4,8,11,6,3,1,7,8,11,5,8,2,7,9,1,4,6,8,1,5,11,9,4,6,7,2,8,1,5,3,7,11,5,9,3,6,4,8,6,11,5,9,3,7,6,3,9,5,3,7,11,8,3,5,4,8,11,5,2,7,4,9,11,6,9,2,7,4,6,7,11,5,1,8,4,6,11,7,9,2,5,8,3,7,1,5,4,8,2,6,3,7,8,1,5,8,2,3,9,4,6,8,1,5,11,7,4,6,7,2,8,1,5,3,7,1,5,9,3,6,4,8,6];

            $this->reelStrips['Reels11']=[4,8,1,11,6,3,9,11,7,3,6,7,5,8,2,4,6,5,3,9,2,7,5,3,7,5,3,7,4,8,1,7,4,6,5,9,3,8,2,6,4,8,1,6,4,6,2,8,1,9,4,6,7,2,6,5,9,4,6,1,8,4,6,3];
            $this->reelStrips['Reels12']=[2,4,0,0,0,2,6,4,11,1,6,3,8,0,5,4,1,8,3,9,0,6,8,2,6,10,10,10,3,5,8,3,6,0,4,3,5,8,1,7,4,11,9,4,0,7,4,9,1,9,4,11,6,4,9,8,4,5,7,4,1,6,4,0];
            $this->reelStrips['Reels13']=[7,0,8,2,10,10,10,1,9,11,6,4,0,0,0,6,1,0,6,8,0,0,0,4,1,8,0,0,0,9,2,0,0,9,5,0,4,6,0,2,1,0,2,6,0,0,9,1,3,11,5,6,0,4,3,11,9,3,0,0,5,4,0,8];
            $this->reelStrips['Reels14']=[3,9,0,0,0,3,9,4,5,10,10,10,6,3,5,8,0,0,0,3,6,1,8,4,7,11,5,8,0,2,7,11,9,5,6,1,0,5,4,6,0,9,4,8,11,7,9,0,6,9,0,6,2,1,0,3,8,2,5,6,0,0,8,4];
            $this->reelStrips['Reels15']=[11,5,6,1,5,6,4,2,5,9,11,2,6,3,5,7,11,4,2,8,6,3,9,1,3,6,8,5,7,4,6,8,9,1,8,3,6,11,3,9,4,7,6,4,3,6,4,3,8,4,3,6,1,7,3,1,5,6,4,2,6,8,4,2];

            $this->reelStrips['Reels21']=[5,11,11,11,1,1,1,11,11,1,1,11,11,11,2,2,2,11,11,11,11,11,11,11,3,3,3,3,3,11,11,11,4,4,4,4,4,4,11,11,11,11,5,5,5,11,5,5,5,11,11,11,11,1,1,11,1,1,11,1,1,11,11,2,2,2,2,11,11,11,11,3,3,3,11,11,11,11,3,3,3,11,4,11,11,4,4,11,4,4,11,11,11,11,5,5,11,11,11,5];
            $this->reelStrips['Reels22']=[1,1,11,11,11,11,1,1,1,1,1,11,2,2,2,2,2,2,2,11,11,11,3,3,3,11,3,3,3,3,11,11,11,4,4,4,4,4,11,11,11,11,11,11,5,5,5,11,11,5,5,11,1,1,1,11,1,1,11,1,1,11,2,2,2,2,2,11,2,2,2,11,3,3,3,11,3,3,3,3,3,11,4,4,4,4,4,11,4,4,4,4,5,5,5,11,5,5,5,5];
            $this->reelStrips['Reels23']=[2,11,1,1,1,1,1,1,1,1,11,11,11,11,2,2,2,2,2,11,11,11,3,3,3,3,3,3,3,11,11,11,11,11,4,4,4,4,4,4,4,11,11,5,5,5,11,5,5,5,5,11,1,1,1,1,1,1,1,1,11,11,2,2,2,2,11,2,2,2,2,11,3,3,3,11,3,3,3,3,3,11,4,4,4,4,4,11,4,4,4,4,5,5,5,11,5,5,5,5];
            $this->reelStrips['Reels24']=[3,11,1,1,1,1,1,1,1,1,1,11,2,2,2,2,2,2,2,11,11,11,3,3,3,3,3,3,3,11,11,11,4,4,4,4,4,4,4,4,11,11,5,5,5,5,5,5,5,5,5,11,11,1,1,1,1,1,1,1,1,11,2,2,2,2,2,11,2,2,2,11,3,3,3,11,3,3,3,3,3,3,4,4,4,4,4,11,4,4,4,4,5,5,5,11,5,5,5,5];
            $this->reelStrips['Reels25']=[5,1,1,1,1,1,1,1,1,1,11,11,2,2,2,2,11,11,2,2,2,11,11,3,3,3,3,3,3,3,3,11,4,4,4,4,4,4,4,4,4,11,5,5,5,5,5,5,5,5,5,11,11,1,1,1,1,1,1,1,1,11,2,2,2,2,2,11,2,2,2,2,3,3,3,11,3,3,3,3,3,3,4,4,4,4,4,11,4,4,4,4,5,5,5,11,5,5,5,5];
            $this->reelStrips['Reels26']=[4,11,1,1,1,1,1,1,1,1,11,11,11,2,2,2,2,2,2,2,2,11,3,3,3,3,3,3,3,3,3,11,4,4,4,4,4,4,4,4,4,11,5,5,5,5,5,5,5,5,5,11,1,1,1,1,1,1,1,1,1,11,2,2,2,2,2,2,2,2,2,11,3,3,3,3,3,3,3,3,3,11,4,4,4,4,4,4,4,4,4,11,5,5,5,5,5,5,5,5];
            $this->reelStrips['Reels27']=[5,1,1,1,1,1,1,1,1,1,11,11,2,2,2,2,2,2,2,2,2,3,3,3,3,3,3,3,3,3,11,11,4,4,4,4,4,4,4,4,4,11,5,5,5,5,5,5,5,5,5,11,1,1,1,1,1,1,1,1,1,11,2,2,2,2,2,2,2,2,2,11,3,3,3,3,3,3,3,3,3,11,4,4,4,4,4,4,4,4,4,11,5,5,5,5,5,5,5,5];
            $this->reelStrips['Reels28']=[4,1,1,1,1,1,1,1,1,1,1,1,2,2,11,2,2,2,2,2,2,2,11,3,3,3,3,3,3,3,3,3,4,4,4,4,4,4,4,4,4,11,5,5,5,5,5,5,5,5,5,11,1,1,1,1,1,1,1,1,1,11,2,2,2,2,2,2,2,2,2,11,3,3,3,3,3,3,3,3,3,11,4,4,4,4,4,4,4,4,4,11,5,5,5,5,5,5,5,5];
            $this->reelStrips['Reels29']=[3,1,1,1,1,1,1,1,1,1,1,1,2,2,2,2,2,2,2,2,11,2,3,3,3,3,3,3,3,3,3,3,4,4,4,4,4,4,4,4,4,11,5,5,5,5,5,5,5,5,5,5,1,1,1,1,1,1,1,1,1,1,2,2,2,2,2,2,2,2,2,11,3,3,3,3,3,3,3,3,3,11,4,4,4,4,4,4,4,4,4,11,5,5,5,5,5,5,5,5];
            $this->reelStrips['Reels210']=[1,1,1,1,1,1,1,1,1,1,1,1,2,2,2,2,2,2,2,2,2,11,3,3,3,3,3,3,3,3,3,3,4,4,4,4,4,4,4,4,4,4,5,5,5,5,5,5,5,5,5,5,1,1,1,1,1,1,1,1,1,1,2,2,2,2,2,2,2,2,2,2,3,3,3,3,3,3,3,3,3,3,4,4,4,4,4,4,4,4,4,11,5,5,5,5,5,5,5,5];
            $this->reelStrips['Reels211']=[11,11,1,11,11,11,11,11,11,11,11,11,11,2,2,11,11,11,11,11,11,11,3,11,11,11,3,11,11,11,3,11,11,11,11,11,4,4,11,11,11,11,11,11,11,11,11,5,5,11,11,11,11,11,11,11,11,1,11,11,11,11,11,11,11,11,2,11,11,11,11,11,3,11,11,11,3,11,11,11,11,11,4,11,11,11,11,11,4,11,11,11,11,11,11,11,5,11,11,11];
            $this->reelStrips['Reels212']=[1,11,11,11,11,11,11,1,1,11,11,11,11,11,11,2,2,2,2,11,11,11,11,11,11,3,3,3,11,11,11,11,11,11,4,4,4,4,11,11,11,11,5,5,5,11,11,11,11,5,5,5,11,11,11,11,1,11,11,11,1,11,2,2,11,11,11,11,2,11,11,11,3,3,11,11,3,11,11,11,11,11,4,4,11,11,11,11,4,4,11,11,11,5,11,11,5,11,11,11];
            $this->reelStrips['Reels213']=[1,1,1,11,11,11,11,11,11,11,11,11,2,2,2,2,11,11,11,11,11,11,3,3,3,11,11,11,11,11,3,3,11,11,11,4,4,4,11,11,11,11,5,11,11,11,11,11,5,5,5,11,11,11,11,1,1,1,1,1,1,11,11,11,2,2,2,11,3,11,2,11,11,11,11,11,3,3,3,3,3,11,11,11,4,4,4,4,4,4,11,11,3,11,11,5,5,5,11,11];            

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
            $this->SymbolGame = [0,1,2,3,4,5,6,7,8,9];
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
        public function GetReelStrips($winType, $lines, $bonusSym)        
        {
            $symbols = [0,1,2,3,4,5,6,7,8];
            $scatter = 9;

            $sym_cnt = count($symbols);
            $reels = [];
            if($winType == 'none' || $winType == 'bonus')
            {
                $temp = $symbols;
                $symbols = [];
                foreach($temp as $t)
                {
                    if($t != $bonusSym)
                        $symbols[] = $t;
                }
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

                if($winType == 'bonus')
                {
                    $reels['reel1'][rand(0,2)] = $scatter;
                    $reels['reel3'][rand(0,2)] = $scatter;
                    $reels['reel5'][rand(0,2)] = $scatter;
                }                             
            }
            else 
            {
                for($r = 0; $r < 5; $r++)
                    $reels['reel'.($r+1)] = [-1,-1,-1];
                
                $paylines = $this->GetPaylines($lines);
                $selected_lines = [];
                
                $lineCnt = rand(1, $lines > 2 ? 2 : $lines);

                while(count($selected_lines) < $lineCnt)
                {
                    $lineId = rand(0, count($paylines) - 1);
                    if(!in_array($lineId, $selected_lines))
                        $selected_lines[] = $lineId;
                }
                
                $winningSym = $symbols[rand(0, $sym_cnt - 1)];

                for($i = 0; $i < $lineCnt; $i++)
                {
                    //place winning symbol on selected payline
                    $selected_line = $paylines[$selected_lines[$i]];
                    $cnt = rand(3, 4);
                    for($r = 0; $r < $cnt; $r++)
                    {
                        $c = $selected_line[$r];
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

                $poss = rand(0, 100);
                if($poss < 2)
                    $reels['reel1'][rand(0,2)] = $scatter;
                else if($poss < 4)
                    $reels['reel3'][rand(0,2)] = $scatter;
                else if($poss < 6)
                    $reels['reel5'][rand(0,2)] = $scatter;
            }
           
            return $reels;
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

        public function GetPaylines($lines)
        {
            $linesId = [[1,1,1,1,1], [0,0,0,0,0], [2,2,2,2,2], [0,1,2,1,0], [2,1,0,1,2],
                        [1,0,0,0,1], [1,2,2,2,1], [0,0,1,2,2], [2,2,1,0,0], [1,2,1,0,1]];
            $linesId = array_slice($linesId, 0, $lines);
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
        

        function getMultiplier($reel, $sym)
        {
            $multiplier = 0;
            $wild = '0';
            for($c = 0; $c < 3; $c++)
                if(($reel[$c] == $sym || $reel[$c] == $wild ))
                    $multiplier++;

            return $multiplier;
        }
    }

}
