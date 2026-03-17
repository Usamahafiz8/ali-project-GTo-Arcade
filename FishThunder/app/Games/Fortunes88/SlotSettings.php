<?php 
namespace VanguardLTE\Games\Fortunes88
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
            
            $this->Paytable['0'] = [0, 0, 0, 0, 0, 0];
            $this->Paytable['1'] = [0, 0, 0, 40, 80, 400];
            $this->Paytable['2'] = [0, 0, 0, 5, 10, 50];
            $this->Paytable['3'] = [0, 0, 0, 5, 10, 50];
            $this->Paytable['4'] = [0, 0, 0, 5, 10, 50];
            $this->Paytable['5'] = [0, 0, 0, 5, 10, 50];
            $this->Paytable['6'] = [0, 0, 0, 5, 10, 50];
            $this->Paytable['7'] = [0, 0, 0, 5, 10, 50];
            $this->Paytable['8'] = [0, 0, 0, 5, 10, 50];
            $this->Paytable['9'] = [0, 0, 0, 5, 10, 50];
            $this->Paytable['10'] = [0, 0, 0, 5, 10, 50];
            $this->Paytable['11'] = [0, 0, 0, 5, 10, 50];
            $this->Paytable['12'] = [0, 0, 0, 10, 20, 100];
            $this->Paytable['13'] = [0, 0, 0, 25, 50, 250];
            $this->Paytable['14'] = [0, 0, 0, 40, 80, 400];
            $this->Paytable['15'] = [0, 0, 0, 50, 100, 500];            
            $this->Paytable['16'] = [0, 0, 0, 100, 200, 1000];        

            $this->awardIndices = [
                '0' => ['1' => 48, '2' => 49, '3' => 50, '4' => 51,'5' => 52],
                '1' => ['3' => 0,'4' => 12,'5' => 28],
                '2' => ['3' => 1,'4' => 13,'5' => 29],
                '3' => ['3' => 2,'4' => 14,'5' => 30],
                '4' => ['3' => 3,'4' => 15,'5' => 31],
                '5' => ['3' => 4,'4' => 16,'5' => 32],
                '6' => ['3' => 5,'4' => 17,'5' => 33],
                '7' => ['3' => 6,'4' => 18,'5' => 34],
                '8' => ['3' => 7,'4' => 19,'5' => 35],
                '9' => ['3' => 8,'4' => 20,'5' => 36],
                '10' => ['3' => 9,'4' => 21,'5' => 37],
                '11' => ['3' => 10,'4' => 22,'5' => 38],
                '12' => ['3' => 11,'4' => 23,'5' => 42],
                '13' => ['3' => 24,'4' => 27,'5' => 44],
                '14' => ['3' => 25,'4' => 39,'5' => 45],
                '15' => ['3' => 26,'4' => 41,'5' => 46],
                '16' => ['3' => 40,'4' => 43,'5' => 47],
            ];

            $this->reelStrips = [];
            
            //reel strips of Balley games are inversed, so reel strip index must be returned as (length - position)
            
            $this->reelStrips['Reels01']=[4,12,7,11,2,8,7,12,3,10,12,3,4,12,12,12,5,1,9,6,12,9,6,4,12,11,6,10,7,12,3,5,12,6,8,2,12,3,9,8,5,12,3,1,2,12,8,3,12,5,11,9,2,12,4,10];
            $this->reelStrips['Reels02']=[3,8,5,11,8,1,4,7,9,1,6,2,8,3,2,11,5,7,8,4,3,8,7,5,8,3,7,12,12,12,0,7,9,3,10,5,12,3,10,4,6,12,5,2,12,5,4,9,2,6,12,2,11,6,10,2,7,9];
            $this->reelStrips['Reels03']=[4,7,8,3,2,9,3,5,0,2,8,5,6,8,2,3,12,5,3,8,1,2,8,6,1,10,5,11,3,9,2,6,11,7,9,1,2,8,7,5,12,12,12,5,10,0,6,4,10,1,5,11,8,7,1,3,5,9,11];
            $this->reelStrips['Reels04']=[3,8,8,3,1,6,3,12,12,7,4,0,11,3,5,12,1,7,2,9,0,8,3,7,10,4,2,11,6,7,0,6,2,10,8,2,6,9,0,4,11,7,6,8,4,3,10,7,6,9,4,8,2,4,12,2,5,9];
            $this->reelStrips['Reels05']=[3,8,5,7,12,5,7,9,3,7,10,6,12,3,1,7,4,8,3,5,11,2,7,9,4,7,1,2,12,12,2,7,11,6,7,10,5,8,6,2,8,3,7,9,3,6,9,4,7,11,4,7,12,4,6,8,5,4,9,7,2,9,7,10,5,6,12,2];

            $this->reelStrips['Reels11']=[4,12,7,11,2,13,7,12,3,10,7,13,4,12,12,12,5,1,9,9,13,6,4,12,11,6,10,7,12,6,13,5,12,6,13,2,12,3,9,13,5,12,3,1,13,13,13,3,5,11,3,9,2,13,4,10];
            $this->reelStrips['Reels12']=[3,13,5,11,13,1,4,7,9,1,13,13,13,1,6,11,5,7,12,4,3,12,7,5,13,3,7,12,12,12,0,7,2,12,10,5,12,3,10,4,6,12,5,2,12,5,4,9,9,6,12,2,11,6,10,2,7,9];
            $this->reelStrips['Reels13']=[4,7,13,3,2,9,3,5,0,13,13,13,5,6,13,2,3,12,5,3,13,1,3,6,1,10,5,11,3,9,2,6,11,7,9,3,12,7,5,12,12,12,5,10,0,6,4,1,5,2,11,7,1,3,5,9,12];
            $this->reelStrips['Reels14']=[3,13,13,13,1,6,12,12,12,7,4,11,3,5,13,1,7,3,9,0,13,3,7,10,4,11,6,12,7,0,6,3,10,12,2,6,9,4,11,7,6,13,4,3,10,7,6,9,4,13,2,4,12,2,5,9];
            $this->reelStrips['Reels15']=[3,11,5,7,12,5,7,9,3,7,10,6,12,3,1,7,4,12,3,5,11,2,7,9,4,7,1,12,12,12,2,7,11,6,7,10,5,6,13,13,13,7,3,9,3,6,9,4,7,11,4,7,12,4,6,12,5,4,9,7,2,9,7,10,5,6,12,2];

            $this->reelStrips['Reels21']=[4,12,7,11,2,13,7,12,3,10,7,13,4,12,12,12,5,1,14,14,14,6,4,11,2,6,10,7,12,6,13,5,12,6,13,2,12,3,2,13,7,12,2,1,13,13,13,4,12,6,11,4,2,11,4,10];
            $this->reelStrips['Reels22']=[3,13,5,11,13,1,14,14,14,1,13,13,13,1,11,5,13,7,12,4,13,3,12,5,13,3,12,12,12,0,13,3,12,10,13,5,12,3,13,4,6,12,5,13,3,12,5,13,10,6,13,2,11,10,2,13,7,14];
            $this->reelStrips['Reels23']=[4,7,13,3,2,13,5,0,13,13,13,5,6,13,2,3,12,5,3,13,1,14,14,14,1,10,5,11,3,13,2,6,11,7,13,2,12,7,5,12,12,12,5,10,0,5,4,10,1,5,11,7,1,13,5,14,11];
            $this->reelStrips['Reels24']=[3,13,13,13,1,6,12,12,12,7,4,11,3,5,12,1,14,14,14,0,13,3,7,10,4,11,6,12,0,6,13,3,10,12,2,6,13,4,11,7,6,13,4,2,10,7,6,12,4,13,2,4,12,2,5,14];
            $this->reelStrips['Reels25']=[3,13,5,7,13,5,7,11,3,7,10,2,3,1,11,7,4,12,3,5,14,2,7,14,4,7,1,12,12,12,2,7,11,6,3,10,5,6,13,13,13,7,3,14,14,14,7,6,14,4,7,11,4,2,12,4,6,13,5,4,14,2,6,10,5,6,12,2];

            $this->reelStrips['Reels31']=[4,12,12,13,13,12,3,15,15,15,14,2,12,12,12,1,14,14,14,5,1,11,3,12,2,11,7,12,6,13,5,12,6,15,4,11,13,3,15,13,7,12,14,14,13,13,13,12,11,4,14,13,12,14,13,15];
            $this->reelStrips['Reels32']=[12,13,1,11,11,11,1,14,14,14,13,13,13,1,11,7,14,14,12,4,11,12,15,15,15,12,12,12,7,13,3,12,12,6,13,5,12,3,13,4,14,6,12,13,7,12,0,14,14,6,13,2,11,14,2,13,7,14];
            $this->reelStrips['Reels33']=[4,13,14,2,13,14,12,12,13,13,13,15,15,15,0,12,6,14,1,3,13,13,14,14,14,1,11,14,14,3,13,6,12,7,13,2,12,7,14,12,12,12,5,15,0,12,4,15,1,11,11,11,1,13,5,14,15];
            $this->reelStrips['Reels34']=[3,13,13,13,1,11,12,12,12,7,4,0,3,5,12,6,14,14,14,2,13,13,3,7,15,2,0,12,6,14,3,15,15,15,2,1,11,11,11,7,6,14,4,2,15,7,6,12,4,13,2,4,15,2,5,14];
            $this->reelStrips['Reels35']=[13,5,7,14,5,15,15,15,2,3,1,11,11,11,1,4,12,3,5,14,2,7,11,4,7,11,3,5,12,12,12,2,7,11,6,3,15,5,6,13,13,13,7,14,14,14,7,12,6,14,4,12,11,4,2,12,4,6,13,6,4,14,2,6,15,5,12,2];

            $this->reelStrips['Reels41']=[4,2,12,13,13,12,3,15,15,15,14,2,12,12,12,5,14,14,14,6,1,16,16,16,1,15,15,7,12,6,13,5,12,6,15,12,13,3,15,13,7,12,14,14,13,13,13,12,16,4,14,13,12,14,13,15];
            $this->reelStrips['Reels42']=[12,13,1,16,16,16,1,14,14,14,13,13,13,0,16,7,14,14,12,4,16,12,15,15,15,12,12,12,7,13,3,12,5,6,13,5,12,3,13,4,14,6,12,13,7,12,0,14,14,6,13,2,16,14,2,13,7,14];
            $this->reelStrips['Reels43']=[4,13,14,2,13,14,12,12,13,13,13,15,15,15,0,12,6,14,1,3,13,13,14,14,14,1,15,14,14,3,13,6,12,7,13,2,12,7,14,12,12,12,5,15,0,12,4,15,1,16,16,16,1,13,5,14,7];
            $this->reelStrips['Reels44']=[3,13,13,13,1,16,12,12,12,7,4,0,3,5,12,6,14,14,14,2,13,13,3,7,15,2,0,12,6,14,3,15,15,15,2,1,16,16,16,7,6,14,4,2,15,7,6,12,4,13,2,4,15,2,5,14];
            $this->reelStrips['Reels45']=[13,5,7,13,5,15,15,15,2,3,1,16,16,16,1,4,12,3,5,14,2,7,15,4,7,16,3,5,12,12,12,2,7,16,6,3,15,5,6,13,13,13,7,14,14,14,7,12,6,14,4,12,16,4,2,12,4,6,13,6,4,14,2,6,15,5,12,2];

            $this->reelStrips['Reels51']=[11,9,10,8,11,9,10,12,12,11,8,10,8,9,12,1,8,9,10,8,9,11,8,10,12,9,10,8,9,12,11,9,9,1,12,9,11,10,12,9,9,9,10,8,8,8,9,11,11,11,9,12,11,9,12,10];
            $this->reelStrips['Reels52']=[8,10,12,12,12,11,10,12,9,11,12,8,11,1,8,12,9,10,8,8,8,1,10,10,10,9,8,10,9,11,10,12,9,10,10,9,11,8,9,10,11,9,12,11,10,12,0,9,9,9,8,11,11,11,8,10,9,11];
            $this->reelStrips['Reels53']=[10,10,1,12,12,12,8,8,8,11,9,1,11,9,8,1,9,10,10,11,8,9,10,12,1,10,12,11,9,12,11,9,9,9,11,8,1,11,8,9,9,12,10,11,12,9,0,10,9,11,8,10,9,11,10,9,12];
            $this->reelStrips['Reels54']=[12,9,11,12,8,9,12,12,12,9,11,11,12,9,1,12,10,9,1,10,9,8,8,8,1,11,11,11,0,12,9,9,10,10,10,8,11,9,8,10,11,8,9,12,10,10,11,9,9,9,12,11,10,10,8,9];
            $this->reelStrips['Reels55']=[8,10,9,8,11,9,10,11,11,8,10,12,8,10,9,8,11,11,9,8,8,8,11,10,9,12,8,11,10,9,9,9,10,8,11,11,10,10,9,8,11,11,8,10,9,8,11,11,1,9,10,10,9,8,11,9,10,11,8,10,9,8,11,9,10,10,1,9];

            $this->reelStrips['Reels61']=[11,9,10,13,11,9,10,12,12,12,13,10,13,9,12,1,13,9,10,13,9,11,13,10,12,9,10,13,9,12,11,9,9,1,12,9,11,10,12,9,9,9,10,13,13,13,9,11,11,11,9,12,11,9,12,10];
            $this->reelStrips['Reels62']=[13,10,12,12,12,11,10,12,9,11,12,13,11,1,13,12,9,10,13,13,13,1,10,10,10,9,13,10,9,11,12,12,9,10,1,9,11,13,9,10,11,9,12,11,10,12,0,9,9,9,13,11,11,11,13,10,9,11];
            $this->reelStrips['Reels63']=[10,10,1,12,12,12,13,13,13,11,9,0,11,9,13,1,9,10,10,11,13,9,10,12,1,10,12,11,9,12,11,9,9,9,11,13,1,11,13,9,9,12,10,11,12,9,0,10,9,11,13,10,9,11,10,9,9];
            $this->reelStrips['Reels64']=[12,9,11,12,13,9,12,12,12,9,11,11,12,9,0,12,10,9,1,10,9,13,13,13,1,11,11,11,0,12,9,9,10,10,10,13,11,9,13,10,11,13,9,12,10,10,11,9,9,9,12,11,10,10,13,9];
            $this->reelStrips['Reels65']=[13,10,9,13,11,9,10,11,11,13,10,12,13,10,9,13,11,11,9,13,13,13,11,10,9,12,13,11,10,9,9,9,10,13,11,11,10,10,9,13,11,11,13,10,9,13,11,11,1,9,10,10,9,13,11,9,10,11,13,10,9,13,11,9,10,10,1,9];

            $this->reelStrips['Reels71']=[11,12,10,13,11,14,10,12,12,12,13,10,13,14,12,1,13,12,10,13,12,11,13,10,12,14,10,13,12,12,10,14,14,1,12,11,10,12,14,14,14,10,12,13,13,13,12,11,11,11,14,12,11,14,12,10];
            $this->reelStrips['Reels72']=[13,10,12,12,12,13,10,12,14,13,12,13,11,1,13,12,14,10,13,13,13,1,10,10,10,12,13,10,14,13,12,12,13,10,1,14,12,13,14,10,13,14,12,13,10,12,0,14,14,14,13,11,11,11,13,10,14,13];
            $this->reelStrips['Reels73']=[10,10,1,12,12,12,13,13,13,12,12,0,11,12,13,1,12,10,10,12,13,14,10,12,1,10,12,11,14,12,13,14,14,14,12,13,1,12,13,14,14,12,10,11,12,14,0,10,12,11,13,10,12,13,10,12,13];
            $this->reelStrips['Reels74']=[12,13,11,12,13,14,12,12,12,13,11,11,12,14,0,12,10,14,1,10,12,13,13,13,1,11,11,11,0,13,14,14,10,10,10,13,11,12,13,10,12,13,14,12,10,10,12,14,14,14,12,11,10,10,13,14];
            $this->reelStrips['Reels75']=[13,10,12,13,11,12,10,11,11,13,10,12,13,10,12,11,11,11,12,13,13,13,11,11,12,12,13,11,11,12,12,12,10,13,12,11,10,10,12,13,11,11,13,10,12,13,11,11,1,14,10,10,12,11,11,12,10,11,13,10,12,13,11,12,10,10,1,14];

            $this->reelStrips['Reels81']=[11,11,12,13,14,12,12,12,13,15,15,15,13,14,12,1,13,12,15,13,14,11,13,15,15,13,11,15,13,12,12,11,13,14,14,1,13,11,12,13,14,14,14,13,13,13,15,11,13,14,15,13,11,14,13,15];
            $this->reelStrips['Reels82']=[13,13,12,12,12,13,15,12,14,13,12,15,13,11,1,13,12,14,15,13,13,13,1,15,15,15,13,15,14,13,12,12,13,15,1,14,12,13,14,15,13,14,12,13,14,12,0,14,14,14,13,11,11,11,13,15,14,13];
            $this->reelStrips['Reels83']=[15,13,1,12,12,12,13,13,13,12,12,13,0,11,12,13,1,14,13,15,12,13,14,15,12,1,15,14,12,13,14,14,14,12,13,1,12,14,14,13,12,14,0,13,15,15,15,13,11,11,11,12,15,14,13,13,15];
            $this->reelStrips['Reels84']=[12,13,11,12,13,14,12,12,12,13,11,11,13,14,0,13,15,14,1,15,12,13,13,13,1,12,11,11,0,13,14,14,15,15,15,13,11,12,13,15,12,11,14,12,15,15,12,14,14,14,12,11,15,15,13,14];
            $this->reelStrips['Reels85']=[11,14,12,13,11,12,14,11,11,13,14,12,13,14,12,11,11,11,12,13,13,13,11,11,12,12,13,11,11,12,12,12,14,13,11,11,14,15,12,13,11,11,13,14,12,13,11,12,1,14,14,14,13,11,11,12,14,11,13,14,12,13,11,14,13,15,1,14];

            $this->reelStrips['Reels91']=[16,16,13,13,12,12,12,13,15,15,15,13,14,14,1,13,12,12,13,15,16,13,15,16,13,12,15,13,13,15,12,13,14,14,1,13,12,12,13,14,14,14,13,13,13,16,16,16,14,12,12,14,14,13,15,15];
            $this->reelStrips['Reels92']=[13,15,12,12,12,13,15,14,14,13,13,12,12,0,13,12,14,14,13,13,13,1,15,15,15,12,13,15,15,13,12,12,13,15,15,1,14,14,13,15,15,13,12,12,12,13,0,14,14,14,13,16,16,16,13,14,14,13];
            $this->reelStrips['Reels93']=[1,16,13,13,12,12,12,13,13,13,12,12,13,0,12,13,1,14,13,15,15,13,14,14,13,1,12,13,14,12,13,14,14,14,13,1,12,13,14,14,13,12,14,0,13,15,15,15,13,16,16,16,13,15,15,13,13];
            $this->reelStrips['Reels94']=[16,13,15,15,13,14,12,12,12,13,16,16,13,15,0,13,14,14,1,12,12,13,13,13,1,16,16,16,0,13,14,14,15,15,15,13,16,16,13,15,15,13,14,12,15,15,12,14,14,14,13,12,15,15,13,14];
            $this->reelStrips['Reels95']=[13,14,12,13,16,12,14,16,16,13,14,14,13,12,12,16,16,16,12,13,13,13,16,16,12,12,13,16,16,12,12,12,14,13,12,12,14,14,12,13,16,16,13,14,12,13,16,16,1,14,14,14,12,12,16,13,14,14,13,15,15,13,16,16,13,15,1,14];

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
            $this->SymbolGame = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16];            
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
            ];

            $endLen = 6;
            $prs = [];
            $reel = [
                'rp' => []
            ];
            if($winType != 'bonus')
            {
                if($winType == 'win')
                {
                    $sym = rand(2, 12);
                    $cnt = rand(3, 5);
                    for($index = 0; $index < 5; $index++)
                    {
                        $reelStrip = $reelName . ($index+1);
                        if($index < $cnt)
                        {
                            $prs[$index + 1] = $this->GetSymbolPos($this->reelStrips[$reelStrip], $sym, $endLen);
                        }
                        else
                        {
                            $prs[$index + 1] = mt_rand(1, count($this->reelStrips[$reelStrip]) - $endLen);
                        }
                    }
                    
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
                $symb = '1';
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
                        [1,2,0,2,1], [0,2,0,2,0], [2,0,2,0,2], [0,2,2,2,0], [2,0,0,0,2]];
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
                $reels = $this->GetReelStrips('none', $reelName, 'bet');
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
