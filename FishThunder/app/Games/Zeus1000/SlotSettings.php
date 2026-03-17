<?php 
namespace VanguardLTE\Games\Zeus1000
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

            // Jack 0
            // Queen 1
            // King 2
            // Ace 3
            // Vase 4
            // Helmet 5
            // Chariot 6
            // Pegasus 7
            // Throne 8
            // Zeus 9
            // FrownyZeus 10
            // Wild 11
            // Bonus1 12
            // Bonus2 13
            // Wonus 14
            // VOID 15

            $this->Paytable['0'] = [0, 0, 0, 3, 7, 15];
            $this->Paytable['1'] = [0, 0, 0, 3, 7, 15];
            $this->Paytable['2'] = [0, 0, 0, 5, 10, 20];
            $this->Paytable['3'] = [0, 0, 0, 5, 10, 20];
            $this->Paytable['4'] = [0, 0, 0, 5, 10, 25];
            $this->Paytable['5'] = [0, 0, 0, 5, 15, 30];
            $this->Paytable['6'] = [0, 0, 0, 10, 20, 40];
            $this->Paytable['7'] = [0, 0, 0, 10, 25, 50];
            $this->Paytable['8'] = [0, 0, 0, 10, 25, 40];
            $this->Paytable['9'] = [0, 0, 0, 15, 40, 100];
            $this->Paytable['10'] = [0, 0, 0, 15, 40, 100];
            $this->Paytable['11'] = [0, 0, 0, 15, 40, 100];
            $this->Paytable['12'] = [0, 0, 0, 0, 0, 0];
            $this->Paytable['13'] = [0, 0, 0, 0, 0, 0];
            
            $this->awardIndices = [
                '0' => ['3' => 0,'4' => 6,'5' => 14],
                '1' => ['3' => 1,'4' => 7,'5' => 15],
                '2' => ['3' => 2,'4' => 8,'5' => 20],
                '3' => ['3' => 3,'4' => 9,'5' => 21],
                '4' => ['3' => 4,'4' => 10,'5' => 23],
                '5' => ['3' => 5,'4' => 16,'5' => 26],
                '6' => ['3' => 11,'4' => 22,'5' => 27],
                '7' => ['3' => 12,'4' => 24,'5' => 31],
                '8' => ['3' => 13,'4' => 25,'5' => 32],
                '9' => ['3' => 17,'4' => 28, '5' => 33],
                '10' => ['3' => 18,'4' => 29,'5' => 34],
                '11' => ['3' => 19,'4' => 30,'5' => 35],
                '12' => ['3' => 36,'4' => 37,'5' => 38, '6'=> 39],
            ];
            
            $this->reelStrips = [];
            
            $this->reelStrips['Reels01']=[9,9,9,9,9,3,3,4,6,6,2,2,12,13,0,3,3,8,8,5,7,1,1,4,4,3,7,6,11,11,11,11,11,2,4,4,4,5,5,7,2,2,8,8,3,7,7,1,1,3,3,4,8,5,7,1,2,2,4,3,7,6,2,2,2,4,8,5,1,2,4,4,3,9,9,9,9,9,3,3,4,5,6,2,1,12,13,0,2,3,4,8,7,7,1,2,8,4,4,7,6,1,3,3,0,0,0,4,4,7,7,1,2,8,4,3,7,0,2,3,4,8,5,7,1,2,8,8,3,7,6,1,2,9,9,9,9,3,3,4,5,6,2,1,12,13,0,2,3,4,8,5,7,1,2,8,4,3,3,1,1,1,7,7,2,8,8,3,7,6,1,6,8,3,3,7,2,2,5,3];
            $this->reelStrips['Reels02']=[8,1,2,7,4,5,0,1,1,6,5,3,1,9,9,9,9,0,1,6,4,3,2,5,7,3,0,7,4,4,3,0,1,8,4,3,0,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,1,2,7,4,5,0,1,1,6,5,3,1,4,5,6,5,3,1,9,9,9,9,0,1,6,4,3,2,5,7,3,0,7,4,4,3,0,1,8,0,7,4,4,3,0,1,8,4,0,0,7,4,4,3,0,1,8,4,7,3,0,7,3,0,2,7,4,5,0,1,1,6,5,3,1,9,9,9,9,0,1,6,4,3,2,5,7,3,0];
            $this->reelStrips['Reels03']=[9,9,9,9,9,3,3,4,6,6,2,2,12,13,0,3,3,8,8,5,7,1,1,4,4,3,7,6,11,11,11,11,11,2,4,4,4,5,5,7,2,2,8,8,3,7,7,1,1,3,3,4,8,5,7,1,2,2,4,3,7,6,2,2,2,4,8,5,1,2,4,4,3,9,9,9,9,9,3,3,4,5,6,2,1,12,13,0,2,3,4,8,7,7,1,2,8,4,4,7,6,1,3,3,0,0,0,4,4,7,7,1,2,8,4,3,7,0,2,3,4,8,5,7,1,2,8,8,3,7,6,1,2,9,9,9,9,3,3,4,5,6,2,1,12,13,0,2,3,4,8,5,7,1,2,8,4,3,3,1,1,1,7,7,2,8,8,3,7,6,1,6,8,3,3,7,2,2,5,3];
            $this->reelStrips['Reels04']=[8,1,2,7,4,5,0,1,1,6,5,3,1,9,9,9,9,0,1,6,4,3,2,5,7,3,0,7,4,4,3,0,1,8,4,3,0,11,11,11,11,11,11,11,11,11,11,11,11,11,11,1,2,7,4,5,0,1,1,6,5,3,1,4,5,0,1,1,6,5,3,1,9,9,9,9,0,1,6,4,3,2,5,7,3,0,7,4,4,3,0,1,8,0,7,4,4,3,0,1,8,4,0,0,7,4,4,3,0,1,8,4,7,4,4,3,0,7,7,4,5,0,1,1,6,5,3,1,9,9,9,9,0,1,6,4,3,2,5,7,3,0];
            $this->reelStrips['Reels05']=[9,9,9,9,9,3,3,4,6,6,2,2,12,13,0,3,3,8,8,5,7,1,1,4,4,3,7,6,11,11,11,11,11,11,4,4,4,5,5,7,2,2,8,8,3,7,7,1,1,3,3,4,8,5,7,1,2,2,4,3,7,6,2,2,2,4,8,5,1,2,4,4,3,9,9,9,9,9,3,3,4,5,6,2,1,12,13,0,2,3,4,8,7,7,1,2,8,4,4,7,6,1,3,3,0,0,0,4,4,7,7,1,2,8,4,3,7,0,2,3,4,8,5,7,1,2,8,8,3,7,6,1,2,9,9,9,9,3,3,4,5,6,2,1,12,13,0,2,3,4,8,5,7,1,2,8,4,3,3,1,1,1,7,7,2,8,8,3,7,6,1,6,8,3,3,7,2,2,5,3];

            $this->reelStrips['Reels11']=[6,6,6,6,6,6,4,4,4,4,4,4,9,9,9,9,9,9,9,9,9,9,2,2,2,2,5,5,5,5,6,6,6,6,6,6,12,13,5,5,5,5,4,4,4,4,4,4,2,2,2,2,3,3,3,3,0,0,0,0,9,9,9,9,9,9,9,9,9,9,9,9,6,6,6,6,6,6,4,4,4,4,4,4,5,5,5,5,1,1,1,1,0,0,0,0,9,9,9,9,9,9,9,9,9,9,9,9,7,7,7,7,7,7,8,8,8,8,8,8,0,0,0,0,2,2,2,2,3,3,3,3,5,5,5,5,5,5,9,9,9,9,9,9,5,5,5,5,5,5,1,1,1,1,1,1,0,0,0,0,2,2,2,2,1,1,1,1,7,7,7,7,7,7,8,8,8,8,8,8,6,6,6,6,6,6,9,9,9,9,9,9,9,9,9,9,9,9,4,4,4,4,4,4,9,9,9,9,9,9,6,6,6,6,6,6,12,13,5,5,5,5,9,9,9,9,9,9,2,2,2,2,3,3,3,3,0,0,0,0,9,9,9,9,9,9,9,9,9,9,9,9,6,6,6,6,6,6,4,4,4,4,4,4,5,5,5,5,9,9,9,9,9,9,9,9,9,9,9,9,5,5,5,5,4,4,4,4,7,7,7,7,7,7,8,8,8,8,8,8,0,0,0,0,9,9,9,9,9,9,3,3,3,3,5,5,5,5,9,9,9,9,9,9,5,5,5,5,5,5,9,9,9,9,9,9,0,0,0,0,2,2,2,2,1,1,1,1,7,7,7,7,7,7,8,8,8,8,8,8];
            $this->reelStrips['Reels12']=[5,5,5,5,5,5,0,0,0,6,6,6,6,2,2,2,1,1,1,1,3,3,3,3,2,2,2,2,6,6,6,6,0,0,0,0,2,2,2,2,5,5,5,5,1,1,1,1,2,2,2,2,1,1,1,1,3,3,3,3,5,5,5,5,4,4,4,4,3,3,3,3,2,2,2,2,1,3,3,3,4,4,4,4,1,1,1,1,3,3,3,3,2,2,2,2,9,9,9,9,9,9,9,9,0,0,0,0,8,8,8,8,2,2,2,2,7,7,7,7,3,3,3,3,2,2,2,2,3,3,3,3,7,7,7,7,7,7,5,5,5,5,5,5,8,8,8,8,8,8,5,5,5,5,5,5,3,3,3,3,2,2,2,2,1,1,1,1,8,8,8,8,8,8,2,2,2,2,2,3,3,3,3,3,5,5,5,5,4,4,4,4,2,2,2,2,1,1,1,1,3,3,3,3,6,6,6,6,9,9,9,9,4,4,4,4,0,0,0,0,5,5,5,5,1,1,1,1,2,2,2,2,1,1,1,1,0,0,0,0,5,5,5,5,4,4,4,4,9,9,9,9,2,2,2,2,1,1,1,1,4,4,4,4,1,1,1,1,3,3,3,3,2,2,2,2,8,8,8,8,5,5,5,5,4,4,4,4,7,7,7,7,2,2,2,2,8,8,8,8,3,3,3,9,9,2,2,2,3,3,3,3,7,7,7,7,7,7,5,5,5,5,5,5,7,7,7,7,7,7,5,5,5,5,5,5,3,3,3,3,2,2,2,9,9,1,1,1,8,8,8,8,8,8,7,7,7,7,7,7];
            $this->reelStrips['Reels13']=[6,6,6,6,6,6,4,4,4,4,4,4,9,9,9,9,9,9,9,9,9,3,3,3,3,5,5,5,5,5,6,6,6,6,6,6,5,5,5,5,5,5,4,4,4,4,4,4,2,2,2,2,3,3,3,3,0,0,0,0,9,9,9,9,9,9,9,9,9,9,9,9,6,6,6,6,6,6,4,4,4,4,4,4,5,5,5,5,1,1,1,1,0,0,0,0,9,9,9,9,9,9,9,9,9,9,9,9,7,7,7,7,7,7,8,8,8,8,8,8,0,0,0,0,2,2,2,2,3,3,3,3,5,5,5,5,5,5,9,9,9,9,9,9,5,5,5,5,5,5,1,1,1,1,1,1,0,0,0,0,2,2,2,2,1,1,1,1,7,7,7,7,7,7,8,8,8,8,8,8,6,6,6,6,6,6,9,9,9,9,9,9,9,9,9,9,9,9,4,4,4,4,4,4,9,9,9,9,9,9,6,6,6,6,6,6,12,13,5,5,5,5,9,9,9,9,9,9,2,2,2,2,3,3,3,12,13,0,0,0,9,9,9,9,9,9,9,9,9,9,9,9,6,6,6,6,6,6,4,4,4,4,4,4,5,5,5,5,9,9,9,9,9,9,9,9,9,9,9,9,5,5,5,5,4,4,4,4,7,7,7,7,7,7,8,8,8,8,8,8,0,0,0,0,9,9,9,9,9,9,3,3,3,3,5,5,5,5,9,9,9,9,9,9,5,5,5,5,5,5,9,9,9,9,9,9,0,0,0,0,2,2,2,2,1,1,1,1,7,7,7,7,7,7,8,8,8,8,8,8];
            $this->reelStrips['Reels14']=[5,5,5,5,5,5,0,0,0,6,6,6,6,2,2,2,1,1,1,1,3,3,3,3,2,2,2,2,6,6,6,6,0,0,0,0,2,2,2,2,5,5,5,5,1,1,1,1,2,2,2,2,1,1,1,1,3,3,3,3,5,5,5,5,4,4,4,4,3,3,3,3,2,2,2,2,1,3,3,3,4,4,4,4,1,1,1,1,3,3,3,3,2,2,2,2,9,9,9,9,9,9,9,9,9,9,9,9,8,8,8,8,2,2,2,2,7,7,7,7,3,3,3,3,2,2,2,2,3,3,3,3,7,7,7,7,7,7,5,5,5,5,5,5,8,8,8,8,8,8,5,5,5,5,5,5,3,3,3,3,2,2,2,2,1,1,1,1,8,8,8,8,8,8,2,2,2,2,2,9,9,9,9,5,5,5,5,5,4,4,4,4,2,2,2,2,1,1,1,1,3,3,3,3,6,6,6,6,9,9,9,9,4,4,4,4,0,0,0,0,5,5,5,5,1,1,1,1,9,9,9,9,1,1,1,1,0,0,0,0,5,5,5,5,4,4,4,4,3,3,3,3,9,9,9,9,1,1,1,1,4,4,4,4,1,1,1,1,9,9,9,9,2,2,2,2,8,8,8,8,5,5,5,9,9,4,4,4,7,7,7,7,2,2,2,2,8,8,8,8,3,3,3,3,2,2,2,2,3,3,3,3,7,7,7,7,7,7,5,5,5,5,5,5,7,7,7,7,7,7,5,5,5,5,9,9,3,3,3,3,2,2,2,2,1,1,1,1,8,8,8,8,8,8,7,7,7,7,7,7];
            $this->reelStrips['Reels15']=[6,6,6,6,6,12,13,4,4,4,4,4,9,9,9,9,9,9,9,9,9,9,9,9,5,5,5,5,5,5,6,6,6,6,6,6,12,13,5,5,5,5,4,4,4,4,4,4,2,2,2,2,3,3,3,3,0,0,0,0,9,9,9,9,9,9,9,9,9,9,9,9,6,6,6,6,6,6,4,4,4,4,4,4,5,5,5,5,1,1,1,1,0,0,0,0,9,9,9,9,9,9,9,9,0,0,0,0,7,7,7,7,7,7,8,8,8,8,8,8,0,0,0,0,2,2,2,2,3,3,3,3,5,5,5,5,5,5,9,9,9,9,9,9,5,5,5,5,5,5,9,9,9,9,9,9,0,0,0,0,2,2,2,2,1,1,1,1,7,7,7,7,7,7,8,8,8,8,8,8,6,6,6,6,6,6,9,9,9,9,4,4,6,6,6,6,6,6,4,4,4,4,4,4,5,5,5,5,5,5,6,6,6,6,6,6,9,9,5,5,5,5,4,4,4,4,4,4,2,2,2,2,3,3,3,3,0,0,0,0,9,9,9,9,9,9,9,9,9,9,9,9,6,6,6,6,6,6,4,4,4,4,4,4,5,5,5,5,1,1,1,1,9,9,9,9,6,6,6,6,6,6,4,4,4,4,4,4,7,7,7,7,7,7,8,8,8,8,8,8,0,0,0,0,2,2,2,2,3,3,3,3,5,5,5,5,5,5,9,9,9,9,9,9,5,5,5,5,5,5,9,9,9,9,9,9,0,0,0,0,2,2,2,2,1,1,1,1,7,7,7,7,7,7,8,8,8,8,8,8];

            $this->reelStrips['Reels21']=[9,9,9,9,9,3,3,4,5,6,2,1,0,2,3,4,8,5,7,1,2,8,4,3,7,6,1,2,3,3,8,8,8,4,4,8,5,7,1,2,8,4,3,7,6,1,2,11,11,11,11,5,7,1,2,8,4,3,7,6,1,2,3,4,8,5,1,2,3,4,3,9,9,9,9,9,9,9,4,5,6,2,1,0,2,3,4,8,5,7,1,2,8,4,3,7,6,1,2,3,0,2,3,4,8,5,7,1,2,8,4,3,7,0,2,3,4,8,5,7,1,2,8,4,3,7,6,1,2,9,9,9,9,3,3,4,5,6,2,1,0,2,3,4,8,5,7,1,2,8,4,3,7,6,1,5,7,1,2,8,4,3,7,6,1,6,8,4,3,7,2,4,5,3];
            $this->reelStrips['Reels22']=[8,1,2,7,4,5,0,1,1,6,5,3,1,9,9,9,9,0,1,6,4,3,2,5,7,3,0,7,4,4,3,0,1,8,4,3,0,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,1,4,5,0,1,1,6,5,3,1,4,5,0,1,1,6,5,3,1,9,9,9,9,0,1,6,4,3,2,5,7,3,0,7,4,4,3,0,1,8,0,7,4,4,3,0,1,8,4,0,0,7,4,4,3,0,1,8,4,7,4,4,3,0,7,4,4,3,0,2,7,4,5,0,1,1,6,5,3,1,9,9,9,9,0,1,6,4,3,2,5,7,3,0];
            $this->reelStrips['Reels23']=[9,9,9,9,9,3,3,4,5,6,2,1,0,2,3,4,8,5,7,1,2,8,4,3,7,6,1,2,3,11,11,11,11,11,4,8,5,7,1,2,8,4,3,7,6,1,2,3,3,4,8,5,7,1,2,8,4,3,7,6,1,2,3,4,8,5,1,2,3,4,3,9,9,9,9,9,3,3,4,5,6,2,1,0,2,3,4,8,5,7,1,2,8,4,3,7,6,1,2,3,0,2,3,4,8,5,7,1,2,8,4,3,7,0,2,3,4,8,5,7,1,2,8,4,3,7,6,1,2,9,9,9,9,3,3,4,5,6,2,1,0,2,3,4,8,5,7,1,2,8,4,3,7,6,1,5,7,1,2,8,4,3,7,6,1,6,8,4,3,7,2,4,5,3];
            $this->reelStrips['Reels24']=[8,1,2,7,4,5,0,1,1,6,5,3,1,9,9,9,9,0,1,6,4,3,2,5,7,3,0,7,4,4,3,0,1,8,4,3,0,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,1,1,1,2,7,4,5,0,1,1,6,5,3,1,4,5,0,1,1,6,5,3,1,9,9,9,9,0,1,6,4,3,2,5,7,3,0,7,4,4,3,0,1,8,0,7,4,4,3,0,1,8,4,0,0,7,4,4,3,0,1,8,4,7,4,4,3,0,7,4,4,3,0,2,7,4,5,0,1,1,6,5,3,1,9,9,9,9,0,1,6,4,3,2,5,7,3,0];
            $this->reelStrips['Reels25']=[9,9,9,9,9,3,3,4,5,6,2,1,0,2,3,4,8,5,7,1,2,8,4,3,7,6,1,2,3,11,11,11,11,11,4,8,5,7,1,2,8,4,3,7,6,1,2,3,3,4,8,5,7,1,2,8,4,3,7,6,1,2,3,4,8,5,1,2,3,4,3,9,9,9,9,9,3,3,4,5,6,2,1,0,2,3,4,8,5,7,1,2,8,4,3,7,6,1,2,3,0,2,3,4,8,5,7,1,2,8,4,3,7,0,2,3,4,8,5,7,1,2,8,4,3,7,6,1,2,9,9,9,9,3,3,4,5,6,2,1,0,2,3,4,8,5,7,1,2,8,4,3,7,6,1,5,7,1,2,8,4,3,7,6,1,6,8,4,3,7,2,4,5,3];

            $this->reelStrips['Reels31']=[6,6,6,6,6,6,4,4,4,4,4,4,9,9,9,9,9,9,9,9,9,9,9,9,5,5,5,5,5,5,6,6,6,6,6,6,5,5,5,5,9,9,4,4,4,4,2,2,2,2,3,3,3,3,0,0,0,0,9,9,9,9,9,9,9,9,9,9,9,9,6,6,6,6,6,6,4,4,4,4,4,4,9,9,9,9,1,1,1,1,0,0,0,0,9,9,9,9,9,9,9,9,0,0,0,0,7,7,7,7,7,7,8,8,8,8,8,8,0,0,0,0,2,2,2,2,3,3,3,3,5,5,5,5,5,5,9,9,9,9,9,9,5,5,5,5,5,5,1,1,1,1,1,1,0,0,0,0,9,9,2,2,1,1,1,1,7,7,7,7,7,7,8,8,8,8,8,8,6,6,6,6,6,6,9,9,9,9,9,9,9,9,9,9,9,9,4,4,4,4,4,4,9,9,9,9,9,9,6,6,6,6,6,6,5,5,5,5,9,9,9,9,9,9,2,2,2,2,3,3,3,3,0,0,0,0,9,9,9,9,9,9,9,9,9,9,0,0,0,6,6,6,6,6,4,4,4,4,4,4,5,5,5,5,9,9,9,9,9,9,9,9,9,1,1,1,1,5,5,5,4,4,4,4,7,7,7,7,7,7,8,8,8,8,8,8,0,0,0,0,9,9,9,9,9,9,3,3,3,3,5,5,5,5,9,9,9,9,9,9,5,5,5,5,5,5,9,9,9,9,9,9,0,0,0,0,2,2,2,2,1,1,1,1,7,7,7,7,7,9,9,8,8,8,8,8];
            $this->reelStrips['Reels32']=[5,5,5,5,5,5,0,0,0,6,6,6,6,2,2,2,1,1,1,1,3,3,3,3,2,2,2,2,6,6,6,6,0,0,0,0,2,2,2,2,5,5,5,5,1,1,1,9,9,2,2,2,1,1,1,1,3,3,3,3,5,5,5,5,4,4,4,4,9,9,9,9,2,2,2,2,1,3,3,3,4,4,4,4,1,1,1,1,3,3,3,3,2,2,2,2,9,9,9,9,9,9,9,9,0,0,0,0,8,8,8,8,2,2,2,2,7,7,7,7,3,3,3,3,9,9,9,9,3,3,3,3,7,7,7,7,7,7,5,5,5,5,5,5,8,8,8,8,8,8,5,5,5,5,5,5,3,3,3,3,9,9,9,9,1,1,1,1,8,8,8,8,8,8,2,2,2,2,2,3,3,3,3,9,9,5,5,5,4,4,4,4,2,2,2,2,9,9,9,9,9,9,9,9,6,6,6,6,0,0,0,0,4,4,4,4,9,9,9,9,5,5,5,5,1,1,1,1,2,2,2,2,1,1,1,1,0,0,0,0,9,9,9,9,4,4,4,4,3,3,3,3,2,2,2,2,9,9,9,9,4,4,4,4,1,1,1,1,3,3,3,3,2,2,2,2,8,8,8,8,5,5,5,5,4,4,4,4,7,7,7,7,2,2,2,2,8,8,8,8,3,3,3,3,2,2,2,2,3,3,3,3,7,7,7,7,7,7,5,5,5,5,5,5,9,9,9,9,9,9,5,5,5,5,5,5,3,3,3,3,2,2,2,2,1,1,1,1,8,8,8,8,8,8,7,7,7,7,7,7];
            $this->reelStrips['Reels33']=[6,6,6,6,6,6,4,4,4,4,4,4,9,9,9,9,9,9,9,9,9,9,9,9,5,5,5,5,5,5,6,6,6,6,6,6,5,5,5,5,4,4,4,4,4,4,2,2,2,2,3,3,3,3,0,0,0,0,9,9,9,9,9,9,9,9,9,9,9,9,6,6,6,6,6,6,4,4,4,4,4,4,5,5,5,5,1,1,1,1,0,0,0,0,9,9,9,9,9,9,9,9,9,9,9,9,7,7,7,7,7,7,8,8,8,8,8,8,0,0,0,0,2,2,2,2,3,3,3,3,5,5,5,5,5,5,9,9,9,9,9,9,5,5,5,5,5,5,1,1,1,1,1,1,0,0,0,0,2,2,2,2,1,1,1,1,7,7,7,7,7,7,8,8,8,8,8,8,6,6,6,6,6,6,9,9,9,9,9,9,9,9,9,9,9,9,4,4,4,4,4,4,9,9,9,9,9,9,6,6,6,6,6,6,5,5,5,5,9,9,9,9,9,9,2,2,2,2,3,3,3,3,0,0,0,0,9,9,9,9,9,9,9,9,9,9,6,6,6,6,6,6,6,4,4,4,4,4,4,4,5,5,5,5,9,9,9,9,9,9,9,9,9,9,9,9,5,5,5,5,4,4,4,4,7,7,7,7,7,7,8,8,8,8,8,8,0,0,0,0,9,9,9,9,9,9,3,3,3,3,5,5,5,5,9,9,9,9,9,9,5,5,5,5,5,5,9,9,9,9,9,9,0,0,0,0,2,2,2,2,1,1,1,1,7,7,7,7,7,7,8,8,8,8,8,8];
            $this->reelStrips['Reels34']=[5,5,5,5,5,5,0,0,0,6,6,6,6,2,2,2,1,1,1,1,3,3,3,3,2,2,2,2,6,6,6,6,0,0,0,0,2,2,2,2,5,5,5,5,1,1,1,1,2,2,2,2,1,1,1,1,3,3,3,3,5,5,5,5,4,4,4,4,3,3,3,3,2,2,2,2,1,3,3,3,4,4,4,4,1,1,1,1,3,3,3,3,2,2,2,2,9,9,9,9,9,9,9,9,9,9,9,9,8,8,8,8,2,2,2,2,7,7,7,7,3,3,3,3,2,2,2,2,3,3,3,3,7,7,7,7,7,7,5,5,5,5,5,5,8,8,8,8,8,8,5,5,5,5,5,9,9,3,3,3,2,2,2,2,1,1,1,1,8,8,8,8,8,8,2,2,2,2,2,3,3,3,3,9,9,5,5,5,4,4,4,4,2,2,2,2,9,9,9,9,3,3,3,3,6,6,6,6,0,0,0,0,4,4,4,4,0,0,0,0,5,5,5,5,9,9,9,9,2,2,2,2,1,1,1,1,0,0,0,0,5,5,5,5,4,4,4,4,9,9,9,9,2,2,2,2,1,1,1,1,4,4,4,9,9,1,1,1,3,3,3,3,2,2,2,2,8,8,8,8,5,5,5,5,4,4,4,4,7,7,7,7,2,2,2,2,8,8,8,8,3,3,3,3,2,2,2,2,3,3,3,3,7,7,7,7,7,7,5,5,5,5,5,5,7,7,7,7,7,7,5,5,5,5,5,5,3,3,3,3,2,2,2,2,1,1,1,1,8,8,8,8,8,8,7,7,7,7,7,7];
            $this->reelStrips['Reels35']=[6,6,6,6,6,6,4,4,4,4,4,4,9,9,9,9,9,9,9,9,9,9,9,9,5,5,5,5,5,5,6,6,6,6,6,6,5,5,5,5,4,4,4,4,4,4,2,2,2,2,3,3,3,3,0,0,0,0,9,9,9,9,9,9,9,9,9,9,9,9,6,6,6,6,6,6,4,4,4,4,4,4,5,5,5,5,1,1,1,1,0,0,0,0,9,9,9,9,9,9,9,9,9,9,9,9,7,7,7,7,7,7,8,8,8,8,8,8,0,0,0,0,2,2,2,2,3,3,3,3,5,5,5,5,5,5,9,9,9,9,9,9,5,5,5,5,5,5,9,9,9,9,9,9,0,0,0,0,2,2,2,2,1,1,1,1,7,7,7,7,7,7,8,8,8,8,8,8,6,6,6,6,6,6,4,4,4,4,4,4,6,6,6,6,6,6,4,4,4,4,4,4,5,5,5,5,5,5,6,6,6,6,6,6,5,5,5,5,4,4,4,4,4,4,2,2,2,2,3,3,3,3,0,0,0,0,9,9,9,9,9,9,9,9,9,9,9,9,6,6,6,6,6,6,4,4,4,4,4,4,5,5,5,5,1,1,1,1,0,0,0,0,6,6,6,6,6,6,4,4,4,4,4,4,7,7,7,7,7,7,8,8,8,8,8,8,0,0,0,0,2,2,2,2,3,3,3,3,5,5,5,5,5,5,9,9,9,9,9,9,5,5,5,5,5,5,9,9,9,9,9,9,0,0,0,0,2,2,2,2,1,1,1,1,7,7,7,7,7,7,8,8,8,8,8,8];

            $this->reelStrips['Reels41']=[4,4,2,6,4,4,2,6,2,7,7,7,5,5,8,8,3,3,3,6,5,0,2,2,3,6,6,1,1,1,1,1,7,7,2,2,4,5,8,8,4,7,6,2,2,10,10,10,10,6,6,7,7,0,0,3,3,1,10,10,10,10,10,6,6,5,3,7,8,5,7,0,6,1,5,5,4,4,6,5,8,2,3,3,6,6,4,4,10,10,10,10,10,10,8,8,3,0,0,0,4,6,1,6,5,8,8,7,7,5,5,5,10,10,10,10,3,3,3,1,6,4,4,10,10,10,10,10,10,5,2,3,4,3,3,6,1,2,2,7,5,1,1,5,7,1,6,2,3,4,4,5,7,0,0,7,7,6,2,0,4,4,3,3,5,5,0,0,0,8,0,7,5,5,1,5,5,7,7,1];
            $this->reelStrips['Reels42']=[10,10,10,10,4,4,5,6,0,2,3,3,7,7,8,8,2,2,3,3,10,10,0,1,6,5,10,10,2,0,4,5,10,10,10,10,10,10,8,8,10,10,10,10,2,7,6,10,10,10,10,7,5,0,0,3,3,6,6,1,1,1,1,10,10,10,10,5,6,5,7,0,6,2,2,4,4,4,0,0,8,10,10,10,6,6,8,7,10,10,10,10,8,8,0,7,7,0,6,6,1,1,6,6,1,1,0,10,10,10,10,10,1,7,7,3,3,3,3,6,6,4,4,10,10,10,10,6,6,5,2,3,4,4,10,10,8,8,0,7,7,0,10,10,7,6,6,2,3,4,6,10,10,10,3,7,7,6,2,0,4,4,8,8,5,5,0,0,0,10,10,7,8,10,10,10,10,7,8,7];
            $this->reelStrips['Reels43']=[10,10,10,10,4,4,5,6,0,2,3,3,7,7,8,8,2,2,3,3,10,10,10,10,2,1,2,6,10,10,2,3,3,4,5,5,0,1,1,2,3,8,8,4,7,6,10,10,10,10,2,7,5,0,0,1,1,2,3,10,10,10,10,6,6,10,10,10,10,5,7,0,6,6,5,5,4,4,0,0,8,2,10,10,1,1,4,4,10,10,10,10,5,5,8,8,3,0,0,0,4,10,10,10,2,8,8,6,5,7,7,1,1,1,1,10,10,10,10,6,6,4,4,10,10,10,10,6,6,1,1,5,5,3,3,6,6,2,10,10,10,6,5,7,10,10,10,10,3,4,4,2,2,7,5,7,7,6,5,5,0,0,3,3,2,2,0,0,0,10,10,10,1,2,3,4,1,5,5,5];
            $this->reelStrips['Reels44']=[10,10,10,10,4,4,5,6,0,2,3,3,7,7,8,8,2,2,3,3,10,10,10,6,5,6,4,6,3,0,0,0,7,7,2,2,4,5,8,8,4,7,6,2,2,5,6,10,10,10,10,7,5,8,8,3,3,6,6,1,1,1,1,6,6,5,3,7,8,8,7,7,6,1,1,1,1,4,0,0,8,2,3,3,6,6,4,4,0,0,3,3,7,4,5,8,3,7,7,5,0,0,0,0,4,6,6,2,2,8,8,6,5,7,7,3,3,3,3,6,6,4,4,10,10,10,10,6,6,5,2,3,4,3,3,10,10,0,1,1,2,2,6,5,7,7,3,10,10,10,5,5,0,0,2,0,4,4,2,0,4,4,3,3,5,5,0,0,0,8,8,4,8,4,8,7,8,7,8,7];
            $this->reelStrips['Reels45']=[10,10,10,10,4,4,5,6,0,2,3,3,7,7,8,8,2,2,3,3,5,0,2,2,3,6,6,5,4,2,2,6,6,5,5,4,4,0,0,8,2,3,3,6,6,4,4,10,10,10,10,7,5,0,0,3,3,6,6,10,10,10,10,6,6,5,3,7,8,5,7,0,6,6,5,5,4,4,0,0,8,2,3,3,6,6,4,4,10,10,10,10,5,5,8,8,3,0,0,0,4,6,6,2,2,8,8,7,7,7,7,1,1,1,1,3,3,3,3,6,6,4,4,2,2,2,2,6,6,7,7,3,4,3,3,6,6,10,10,10,10,8,0,7,7,6,6,2,3,4,4,4,5,3,2,3,0,6,6,5,4,4,3,3,5,5,0,0,0,10,10,10,10,5,5,5,5,5,5,5,3,3,2,6,3,3,2,6,2,7,6,5,5,5,8,8,3,3,3,0,0,0,2,2,3,6,6,6,5,5,2,5,7,7,1,2,4,5,8,8,4,7,6,2,2,5,6,6,5,2,1,7,5,0,0,3,3,6,1,10,10,10,10,6,6,5,3,7,8,5,7,0,6,6,5,5,4,4,0,0,8,2,3,3,6,6,4,4,10,10,10,10,5,5,8,8,3,0,0,0,4,6,6,2,2,8,8,7,7,7,7,1,1,1,1,3,3,3,3,6,6,4,4,10,10,10,10,6,6,5,2,3,4,3,3,6,6,2,2,7,5,8,0,7,7,6,6,2,3,4,4,10,10,10,10,7,7,6,6,5,4,4,3,3,5,5,0,0,0,5,5,5,5,5,5,5,5,5,5,5];

            $this->reelStrips['Reels51']=[6,6,6,6,6,6,4,4,4,4,4,4,10,10,10,10,10,10,10,10,10,10,10,10,5,5,5,5,5,5,6,6,6,6,6,6,10,10,10,10,5,5,4,4,4,4,4,4,2,2,2,2,3,3,3,3,0,0,0,0,10,10,10,10,3,3,3,3,10,10,10,10,6,6,6,6,6,6,4,4,4,4,4,4,5,5,5,5,10,10,10,10,0,0,0,0,6,6,6,6,6,6,4,4,4,4,4,4,7,7,7,7,7,7,7,7,7,7,7,7,0,0,0,0,2,2,2,2,3,3,3,3,8,8,8,8,8,8,10,10,10,10,10,10,7,7,7,7,7,7,10,10,10,10,10,10,0,0,0,0,2,2,2,2,1,1,1,1,8,8,8,8,8,8,8,8,8,8,8,8];
            $this->reelStrips['Reels52']=[10,10,10,10,10,10,5,5,5,5,5,5,2,2,2,2,1,1,1,1,10,10,10,10,10,10,10,10,6,6,6,6,0,0,0,0,8,8,8,8,5,5,5,5,1,1,1,1,2,2,2,2,2,2,2,2,10,10,10,10,10,10,10,10,4,4,4,4,3,3,3,3,2,2,2,2,10,10,10,10,4,4,4,4,1,1,1,1,0,0,0,0,2,2,2,2,10,10,10,10,5,5,5,5,4,4,4,4,7,7,7,7,2,2,2,2,10,10,10,10,0,0,0,0,2,2,2,2,3,3,3,3,10,10,10,10,10,10,10,10,10,10,10,10,7,7,7,7,7,7,10,10,10,10,10,10,0,0,0,0,2,2,2,2,1,1,1,1,7,7,7,7,7,7,7,7,7,7,7,7];
            $this->reelStrips['Reels53']=[6,6,6,6,6,6,4,4,4,4,4,4,10,10,10,10,10,10,10,10,10,10,10,10,6,6,6,6,6,6,5,5,5,5,5,5,8,8,8,8,5,5,5,5,1,1,1,1,10,10,10,10,5,5,5,5,8,8,8,8,8,8,3,3,3,3,3,3,6,6,6,6,2,2,2,2,10,10,10,10,4,4,4,4,1,1,1,1,0,0,0,0,2,2,2,2,10,10,10,10,5,5,5,5,4,4,4,4,7,7,7,7,10,10,10,10,6,6,6,6,0,0,0,0,2,2,2,2,3,3,3,3,10,10,10,10,10,10,4,4,4,4,3,3,3,3,3,3,1,1,1,1,1,1,10,10,0,0,0,0,10,10,10,10,6,6,6,6,8,8,8,8,8,8,8,8,8,8,8,8];
            $this->reelStrips['Reels54']=[5,5,5,5,5,5,5,5,5,5,5,5,10,10,10,10,10,10,10,10,10,10,10,10,6,6,6,6,6,6,5,5,5,5,5,5,10,10,10,10,5,5,5,5,1,1,1,1,2,2,2,2,2,2,2,2,2,2,2,2,10,10,10,10,4,4,4,4,3,3,3,3,2,2,2,2,10,10,10,10,4,4,4,4,1,1,1,1,0,0,0,0,2,2,2,2,10,10,10,10,5,5,5,5,4,4,4,4,7,7,7,7,2,2,2,2,10,10,10,10,0,0,0,0,2,2,2,2,3,3,3,3,8,8,8,8,8,8,10,10,10,10,10,10,7,7,7,7,7,7,7,7,7,7,7,7,0,0,0,0,2,2,2,2,1,1,1,1,8,8,8,8,8,8,8,8,8,8,8,8];
            $this->reelStrips['Reels55']=[6,6,6,6,6,6,5,5,5,5,5,5,10,10,10,10,10,10,10,10,10,10,10,10,6,6,6,6,6,6,5,5,5,5,5,5,10,10,10,10,5,5,5,5,1,1,1,1,10,10,2,2,2,2,2,2,2,2,2,2,6,6,6,6,6,6,5,5,5,5,5,5,3,3,3,3,3,3,3,3,3,3,3,3,1,1,1,1,0,0,0,0,2,2,2,2,10,10,10,10,5,5,5,5,4,4,4,4,7,7,7,7,2,2,2,2,10,10,10,10,0,0,0,0,2,2,2,2,3,3,3,3,8,8,8,8,8,8,8,8,8,8,8,8,7,7,7,7,7,7,7,7,7,7,7,7,0,0,0,0,2,2,2,2,1,1,1,1,8,8,8,8,8,8,8,8,8,8,8,8];

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
            $this->SymbolGame = [0,1,2,3,4,5,6,7,8,9,10,11];
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

        public function GetFrawnyZeusPos($rp, $endCnt)
        {
            $rpResult = [];
            for( $i = 0; $i < count($rp); $i++ ) 
            {
                if( $rp[$i] == 10 ) 
                {
                    if( isset($rp[$i]) && isset($rp[$i + $endCnt - 1]) ) 
                    {
                        if($rp[$i] == 10 && $rp[$i+1] == 10 && $rp[$i+2] == 10 && $rp[$i+3] == 10)
                            array_push($rpResult, $i);
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

        public function GetAddedZeusReelStrips($reelName, $slotIndex, $availableFrownyFillSizes, $availableFrownyFillPositions, $frownyZeusWin)
        {
            $arrReels = [
                $reelName.'1', 
                $reelName.'2', 
                $reelName.'3',
                $reelName.'4',
                $reelName.'5',
            ];

            $endLen = 5;
            if($slotIndex == 1)
                $endLen = 15;
            $prs = [];
            $reel = [
                'rp' => []
            ];

            $reelStrips = [];
            foreach( $arrReels as $index => $reelStrip ) 
            {
                $reelStrips[$reelStrip] = $this->reelStrips[$reelStrip];
            }

            //input frowny zeus symbols
            if($reelName == 'Reels4')
            {
                for($i = 1; $i < 5; $i++)
                {
                    $startPos = [];
                    $size = $availableFrownyFillSizes[$i];
                    $step = $availableFrownyFillPositions[$i];
                    for($z = 0; $z < $frownyZeusWin; $z++)
                        $startPos[] = 21 + $step * $z;

                    foreach($startPos as $index => $pos)
                    {
                        array_splice($reelStrips[$reelName.($i+1)], $pos, 0, array_fill(0, $size, 10));
                    }
                }
            }
            else
            {
                for($i = 0; $i < 5; $i++)
                {
                    $startPos = [];
                    $size = $availableFrownyFillSizes[$i+5];
                    $step = $availableFrownyFillPositions[$i+5];
                    for($z = 0; $z < $frownyZeusWin; $z++)
                        $startPos[] = 21 + $step * $z;

                    foreach($startPos as $index => $pos)
                    {
                        array_splice($reelStrips[$reelName.($i+1)], $pos, 0, array_fill(0, $size, 10));
                    }
                }
            }

            foreach( $arrReels as $index => $reelStrip ) 
            {
                if( is_array($reelStrips) && count($reelStrips[$reelStrip]) > 0 ) 
                {
                    if($reelName == 'Reels4' && $index == 0)
                    {
                        $prs[$index + 1] = $this->GetFrawnyZeusPos($reelStrips[$reelStrip], 5);
                    }
                    else
                        $prs[$index + 1] = mt_rand(1, count($reelStrips[$reelStrip]) - $endLen);
                }
            }

            if($slotIndex == 0)
            {
                foreach( $prs as $index => $value )
                {
                    $key = $reelStrips[$reelName.$index];
                    $key[-1] = $key[count($key) - 1];
                    $reel['reel' . $index][0] = $key[$value];
                    $reel['reel' . $index][1] = $key[$value + 1];
                    $reel['reel' . $index][2] = $key[$value + 2];
                    $reel['reel' . $index][3] = $key[$value + 3];
                    $reel['rp'][] = $value + 1;
                }
            }
            else
            {
                foreach( $prs as $index => $value )
                {
                    $key = $reelStrips[$reelName.$index];
                    $key[-1] = $key[count($key) - 1];
                    $reel['reel' . $index][0] = $key[$value];
                    $reel['reel' . $index][1] = $key[$value + 1];
                    $reel['reel' . $index][2] = $key[$value + 2];
                    $reel['reel' . $index][3] = $key[$value + 3];
                    $reel['reel' . $index][4] = $key[$value + 4];
                    $reel['reel' . $index][5] = $key[$value + 5];
                    $reel['reel' . $index][6] = $key[$value + 6];
                    $reel['reel' . $index][7] = $key[$value + 7];
                    $reel['reel' . $index][8] = $key[$value + 8];
                    $reel['reel' . $index][9] = $key[$value + 9];
                    $reel['reel' . $index][10] = $key[$value + 10];
                    $reel['reel' . $index][11] = $key[$value + 11];
                    $reel['rp'][] = $value + 1;
                }
            }
            
            return $reel;
        }
        
        public function GetReelStrips($winType, $reelName, $slotIndex, $earlyScatter = 0)        
        {
            $arrReels = [
                $reelName.'1', 
                $reelName.'2', 
                $reelName.'3',
                $reelName.'4',
                $reelName.'5',
            ];

            $endLen = 4;
            if($slotIndex == 1)
                $endLen = 15;
            $prs = [];
            $reel = [
                'rp' => []
            ];
            if($winType != 'bonus')
            {
                if($winType == 'win')
                {
                    $sym = rand(4, 11);

                    foreach( $arrReels as $index => $reelStrip ) 
                    {
                        if( is_array($this->reelStrips) && count($this->reelStrips[$reelStrip]) > 0 ) 
                        {
                            if($reelName == 'Reels4' && $index == 0)
                            {
                                $prs[$index + 1] = $this->GetFrawnyZeusPos($this->reelStrips[$reelStrip], 5);
                            }
                            else
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
                            if($reelName == 'Reels4' && $index == 0)
                            {
                                $prs[$index + 1] = $this->GetFrawnyZeusPos($this->reelStrips[$reelStrip], 5);
                            }
                            else
                                $prs[$index + 1] = mt_rand(1, count($this->reelStrips[$reelStrip]) - $endLen);
                        }
                    }
                }                
            }
            else
            {
                $symb = 12;
                $reelsId = [1,2,3,4,5];
                $scatterCnt = 3 - $earlyScatter;
                for( $i = 0; $i < count($reelsId); $i++ ) 
                {                    
                    if( ($i == 0 || $i == 2 || $i == 4) && $scatterCnt > 0) 
                    {
                        $prs[$reelsId[$i]] = $this->GetRandomScatterPos($this->reelStrips[$reelName.$reelsId[$i]], $endLen, $symb);
                        $scatterCnt--;
                    }
                    else
                    {
                        $prs[$reelsId[$i]] = rand(1, count($this->reelStrips[$reelName.$reelsId[$i]]) - $endLen);
                    }
                }          
            }

            if($slotIndex == 0)
            {
                foreach( $prs as $index => $value )
                {
                    $key = $this->reelStrips[$reelName.$index];
                    $key[-1] = $key[count($key) - 1];
                    $reel['reel' . $index][0] = $key[$value];
                    $reel['reel' . $index][1] = $key[$value + 1];
                    $reel['reel' . $index][2] = $key[$value + 2];
                    $reel['reel' . $index][3] = $key[$value + 3];
                    $reel['rp'][] = $value + 1;
                }
            }
            else
            {
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
                    $reel['reel' . $index][7] = $key[$value + 7];
                    $reel['reel' . $index][8] = $key[$value + 8];
                    $reel['reel' . $index][9] = $key[$value + 9];
                    $reel['reel' . $index][10] = $key[$value + 10];
                    $reel['reel' . $index][11] = $key[$value + 11];
                    $reel['rp'][] = $value + 1;
                }
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

        public function GetPaylineIndex1()
        {
            return [1,5,9,13,17,21,25,29,33,37,41,45,49,53,57,61,65,69,73,77,81,85,89,93,97];
        }

        public function GetPaylineIndex2()
        {
            return [2,3,4,6,7,8,10,11,12,14,15,16,18,19,20,22,23,24,26,27,28,30,31,32,34,35,36,38,39,40,42,43,44,46,47,48,50,51,52,54,55,56,58,59,60,62,63,64,66,67,68,70,71,72,74,75,76,78,79,80,82,83,84,86,87,88,90,91,92,94,95,96,98,99,100];
        }

        public function GetPaylines1($lines)
        {
            $part = $lines / 20;
            $paylines = [[0,0,0,0,0],[3,3,3,3,3],[1,1,1,1,1],[2,2,2,2,2],[0,1,2,1,0],[3,2,1,2,3],[2,1,0,1,2],[1,2,3,2,1],[0,1,0,1,0],[3,2,3,2,3],[1,0,1,0,1],[2,3,2,3,2],[1,2,1,2,1],[2,1,2,1,2],[0,1,1,1,0],[3,2,2,2,3],[1,0,0,0,1],[2,3,3,3,2],[1,2,2,2,1],[2,1,1,1,2],[0,0,1,0,0],[3,3,2,3,3],[1,1,0,1,1],[2,2,3,2,2],[1,1,2,1,1]];;
            $cnt = count($paylines) / 5 * $part;
            $linesId = array_slice($paylines, 0, $cnt);
            return $linesId;
        }

        public function GetPaylines2($lines)
        {
            $part = $lines / 20;
            $paylines = [[0,0,0,0,0],[1,1,1,1,1],[2,2,2,2,2],[3,3,3,3,3],[4,4,4,4,4],[5,5,5,5,5],[6,6,6,6,6],[7,7,7,7,7],[8,8,8,8,8],[9,9,9,9,9],[10,10,10,10,10],[11,11,11,11,11],[0,1,2,1,0],[1,2,3,2,1],[2,3,4,3,2],[3,4,5,4,3],[4,5,6,5,4],[5,6,7,6,5],[6,7,8,7,6],[7,8,9,8,7],[8,9,10,9,8],[9,10,11,10,9],[11,10,9,10,11],[10,9,8,9,10],[9,8,7,8,9],[8,7,6,7,8],[7,6,5,6,7],[6,5,4,5,6],[5,4,3,4,5],[4,3,2,3,4],[3,2,1,2,3],[2,1,0,1,2],[0,1,0,1,0],[1,2,1,2,1],[2,3,2,3,2],[3,4,3,4,3],[4,5,4,5,4],[5,6,5,6,5],[6,7,6,7,6],[7,8,7,8,7],[8,9,8,9,8],[9,10,9,10,9],[10,11,10,11,10],[11,10,11,10,11],[10,9,10,9,10],[9,8,9,8,9],[8,7,8,7,8],[7,6,7,6,7],[6,5,6,5,6],[5,4,5,4,5],[4,3,4,3,4],[3,2,3,2,3],[2,1,2,1,2],[1,0,1,0,1],[0,1,1,1,0],[1,2,2,2,1],[2,3,3,3,2],[3,4,4,4,3],[4,5,5,5,4],[5,6,6,6,5],[6,7,7,7,6],[7,8,8,8,7],[8,9,9,9,8],[9,10,10,10,9],[10,11,11,11,10],[11,10,10,10,11],[10,9,9,9,10],[9,8,8,8,9],[8,7,7,7,8],[7,6,6,6,7],[6,5,5,5,6],[5,4,4,4,5],[4,3,3,3,4],[3,2,2,2,3],[2,1,1,1,2]];
            $cnt = count($paylines) / 5 * $part;
            $linesId = array_slice($paylines, 0, $cnt);
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
