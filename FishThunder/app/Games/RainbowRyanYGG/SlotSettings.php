<?php 
namespace VanguardLTE\Games\RainbowRyanYGG
{

    use VanguardLTE\Lib\BasicSlotSettings;
    use VanguardLTE\Lib\JackpotHandler;

    class SlotSettings extends BasicSlotSettings
    {
        public $bonusRate = 30;
        public $winRate = 60;
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
        public $SymbolGameNoScatter = null;
        public $SymbolGameR = null;        
        public $SymbolGameB = null;
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
        public $reelNames = null;
        public $featureReelNames = null;
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

            $this->Paytable['High1'] = [0, 0, 30, 75, 150, 200, 250];
            $this->Paytable['High2'] = [0, 0, 25, 50, 100, 150, 200];
            $this->Paytable['High3'] = [0, 0, 20, 40, 80, 100, 150];
            $this->Paytable['High4'] = [0, 0, 15, 30, 60, 90, 100];
            $this->Paytable['Low1'] = [0, 0, 0, 12, 25, 50, 70];
            $this->Paytable['Low2'] = [0, 0, 0, 12, 25, 50, 70];
            $this->Paytable['Low3'] = [0, 0, 0, 10, 20, 40, 50];
            $this->Paytable['Low4'] = [0, 0, 0, 10, 20, 40, 50];
            
            
            $this->reelNames = ["Reels", "BaseReelsLeft", "BaseReelsRight", "BaseReelsHighSync"];
            $this->featureReelNames = ["FeatureReels", "FeatureReelsHighSync"];
            $this->reelStrips = [];
            $this->reelStrips['BaseReelsHighSync1']=["High3","Low4","Low4","High4","High3","Low3","Low3","High4","Freespin","Low1","Low1","High1","High4","Low2","Low2","High2","Low3","High1","High1","Low3","Low1","Low4","Low4","High2","Low2","Low4","Low4","High1","Low2","Low2","Low2","High3","Low3","Low4","Low4","High2","High3","Low4","Low4","Low1","High2","Low2","Low2","Low3","Low4","High2","High2","Low4","Low1","Low3","Low3","High3","High2","Low1","Low1","High4","Low4","Low1","Low3","Low3","High3","High3","Low2","High2","Low1","Low1","High4","High2","Low4","Low4","High1","Low2","Low1","Low4","Low4","High3","High2","Low2","Low2","High4","High3","Low3","Low3","High4","Low4","Low1","High3","Low2","Low3","Low3","High3","High1","Low4","Low4","High2","Low1","Low3","Low3","High4","High3","Low4","Low4","High1","Low3","Low2","Low2","High1","High2","Low1","Low1","High3","High2","Low1","Low1","High1","Low2","Low2","High4","Low3","Low4","Low4","High2","Low1","High3","High4","Low3","Low4","Low2","High4","Low3","Low2","Low4","High1","Low1","Low2","High3","Low4","High1","Low2","Low1","High4","Low3","Low3","Low2","High4","High4","Low4","Low1","Low1","High4","Low3","Low4","High3","Low1","Low3","High4","Low4","Low3","Low2","High3","Low1","Low1","High1","Low3","Low4","High4","Low1","Low1","Low2","High4","Low4","Low3","Low2","High3","Low1","Low4","Low4","High2","Low3","Low2","Low2","High4","Low1","Low1","Low3","Low3","Low4","Low4","Low3","High2","Low4","High4","Low2","Low3","High2","Low2","Low3","Low3","High4","Low2","Low2","Low3","High3","Low4","Low3","Low3","High1","High2","Low4","High3","Low2","High1","Low4","High4","Low1","Low3","Low2","Low2","Low4","High4","Low3","High1","High3","Low4","Low4","High4","High3","Low3","Low3","High4","Freespin","Low1","Low1","High1","High4","Low2","Low2","High2","Low3","High2","High1","Low3","Low1","Low4","Low4","High2","Low2","Low4","Low4","High1","Low2","Low2","Low2","High3","Low3","Low4","Low4","High2","High3","Low4","Low4","Low1","High2","Low2","Low2","Low3","Low4","High3","High2","Low4","Low1","Low3","Low3","High3","High2","Low1","Low1","High4","Low4","Low1","Low3","Low3","High4","High3","Low2","High2","Low1","Low1","High4","High2","Low4","Low4","High1","Low2","Low1","Low4","Low4","High3","High2","Low2","Low2","High4","High3","Low3","Low3","High4","Low4","Low1","High3","Low2","Low3","Low3","High3","High1","Low4","Low4","High2","Low1","Low3","Low3","High4","High3","Low4","Low4","High1","Low3","Low2","Low2","High1","High2","Low1","Low1","High3","High2","Low1","Low1","High1","Low2","Low2","High4","Low3","Low4","Low4","High2","Low1","High3","High4","Low3","Low4","Low2","High4","Low3","Low2","Low4","High1","Low1","Low2","High3","Low4","High1","Low2","Low1","High4","Low3","Low3","Low2","High3","High4","Low4","Low1","Low1","High4","Low3","Low4","High3","Low1","Low3","High4","Low4","Low3","Low2","High3","Low1","Low1","High1","Low3","Low4","High4","Low1","Low1","Low2","High4","Low4","Low3","Low2","High3","Low1","Low4","Low4","High2","Low3","Low2","Low2","High4","Low1","Low1","Low3","Low3","Low4","Low4","Low3","High2","Low4","High4","Low2","Low3","High2","Low2","Low3","Low3","High4","Low2","Low2","Low3","High3","Low4","Low3","Low3","High1","High2","Low4","High3","Low2","High1","Low4","High4","Low1","Low3","Low2","Low2","Low4","High4","Low3","High1"];
            $this->reelStrips['BaseReelsHighSync2']=["High3","Low4","Low4","High4","High3","Low3","Low3","High4","Freespin","Low1","Low1","High1","High4","Low2","Low2","High2","Low3","High1","High1","Low3","Low1","Low4","Low4","High2","Low2","Low4","Low4","High1","Low2","Low2","Low2","High3","Low3","Low4","Low4","High2","High3","Low4","Low4","Low1","High2","Low2","Low2","Low3","Low4","High2","High2","Low4","Low1","Low3","Low3","High3","High2","Low1","Low1","High4","Low4","Low1","Low3","Low3","High3","High3","Low2","High2","Low1","Low1","High4","High2","Low4","Low4","High1","Low2","Low1","Low4","Low4","High3","High2","Low2","Low2","High4","High3","Low3","Low3","High4","Low4","Low1","High3","Low2","Low3","Low3","High3","High1","Low4","Low4","High2","Low1","Low3","Low3","High4","High3","Low4","Low4","High1","Low3","Low2","Low2","High1","High2","Low1","Low1","High3","High2","Low1","Low1","High1","Low2","Low2","High4","Low3","Low4","Low4","High2","Low1","High3","High4","Low3","Low4","Low2","High4","Low3","Low2","Low4","High1","Low1","Low2","High3","Low4","High1","Low2","Low1","High4","Low3","Low3","Low2","High4","High4","Low4","Low1","Low1","High4","Low3","Low4","High3","Low1","Low3","High4","Low4","Low3","Low2","High3","Low1","Low1","High1","Low3","Low4","High4","Low1","Low1","Low2","High4","Low4","Low3","Low2","High3","Low1","Low4","Low4","High2","Low3","Low2","Low2","High4","Low1","Low1","Low3","Low3","Wild","Low4","Low4","Low3","High2","Low4","High4","Low2","Low3","High2","Low2","Low3","Low3","High4","Low2","Low2","Low3","High3","Low4","Low3","Low3","High1","High2","Low4","High3","Low2","High1","Low4","High4","Low1","Low3","Wild","Low2","Low2","Low4","High4","Low3","High1","High3","Low4","Low4","High4","High3","Low3","Low3","High4","Freespin","Low1","Low1","High1","High4","Low2","Low2","High2","Low3","High2","High1","Low3","Low1","Low4","Low4","High2","Low2","Low4","Low4","High1","Low2","Low2","Low2","High3","Low3","Low4","Low4","High2","High3","Low4","Low4","Low1","High2","Low2","Low2","Low3","Low4","High3","High2","Low4","Low1","Low3","Low3","High3","High2","Low1","Low1","High4","Low4","Low1","Low3","Low3","High4","High3","Low2","High2","Low1","Low1","High4","High2","Low4","Low4","High1","Low2","Low1","Low4","Low4","High3","High2","Low2","Low2","High4","High3","Low3","Low3","High4","Low4","Low1","High3","Low2","Low3","Low3","High3","High1","Low4","Low4","High2","Low1","Low3","Low3","High4","High3","Low4","Low4","High1","Low3","Low2","Low2","High1","High2","Low1","Low1","High3","High2","Low1","Low1","High1","Low2","Low2","High4","Low3","Low4","Low4","High2","Low1","High3","High4","Low3","Low4","Low2","High4","Low3","Low2","Low4","High1","Low1","Low2","High3","Low4","High1","Low2","Low1","High4","Low3","Low3","Low2","High3","High4","Low4","Low1","Low1","High4","Low3","Low4","High3","Low1","Low3","High4","Low4","Low3","Low2","High3","Low1","Low1","High1","Low3","Low4","High4","Low1","Low1","Low2","High4","Low4","Low3","Low2","High3","Low1","Low4","Low4","High2","Low3","Low2","Low2","High4","Low1","Low1","Low3","Low3","Wild","Low4","Low4","Low3","High2","Low4","High4","Low2","Low3","High2","Low2","Low3","Low3","High4","Low2","Low2","Low3","High3","Low4","Low3","Low3","High1","High2","Low4","High3","Low2","High1","Low4","High4","Low1","Low3","Wild","Low2","Low2","Low4","High4","Low3","High1"];
            $this->reelStrips['BaseReelsHighSync3']=["High3","Low4","Low4","High4","High3","Low3","Low3","High4","Freespin","Low1","Low1","High1","High4","Low2","Low2","High2","Low3","High1","High1","Low3","Low1","Low4","Low4","High2","Low2","Low4","Low4","High1","Low2","Low2","Low2","High3","Low3","Low4","Low4","High2","High3","Low4","Low4","Low1","High2","Low2","Low2","Low3","Low4","High2","High2","Low4","Low1","Low3","Low3","High3","High2","Low1","Low1","High4","Low4","Low1","Low3","Low3","High3","High3","Low2","High2","Low1","Low1","High4","High2","Low4","Low4","High1","Low2","Low1","Low4","Low4","High3","High2","Low2","Low2","High4","High3","Low3","Low3","High4","Low4","Low1","High3","Low2","Low3","Low3","High3","High1","Low4","Low4","High2","Low1","Low3","Low3","High4","High3","Low4","Low4","High1","Low3","Low2","Low2","High1","High2","Low1","Low1","High3","High2","Low1","Low1","High1","Low2","Low2","High4","Low3","Low4","Low4","High2","Low1","High3","High4","Low3","Low4","Low2","High4","Low3","Low2","Low4","High1","Low1","Low2","High3","Low4","High1","Low2","Low1","High4","Low3","Low3","Low2","High4","High4","Low4","Low1","Low1","High4","Low3","Low4","High3","Low1","Low3","High4","Low4","Low3","Low2","High3","Low1","Low1","High1","Low3","Low4","High4","Low1","Low1","Low2","High4","Low4","Low3","Low2","High3","Low1","Low4","Low4","High2","Low3","Low2","Low2","High4","Low1","Low1","Low3","Low3","Wild","Low4","Low4","Low3","High2","Low4","High4","Low2","Low3","High2","Low2","Low3","Low3","High4","Low2","Low2","Low3","High3","Low4","Low3","Low3","High1","High2","Low4","High3","Low2","High1","Low4","High4","Low1","Low3","Wild","Low2","Low2","Low4","High4","Low3","High1","High3","Low4","Low4","High4","High3","Low3","Low3","High4","Freespin","Low1","Low1","High1","High4","Low2","Low2","High2","Low3","High2","High1","Low3","Low1","Low4","Low4","High2","Low2","Low4","Low4","High1","Low2","Low2","Low2","High3","Low3","Low4","Low4","High2","High3","Low4","Low4","Low1","High2","Low2","Low2","Low3","Low4","High3","High2","Low4","Low1","Low3","Low3","High3","High2","Low1","Low1","High4","Low4","Low1","Low3","Low3","High4","High3","Low2","High2","Low1","Low1","High4","High2","Low4","Low4","High1","Low2","Low1","Low4","Low4","High3","High2","Low2","Low2","High4","High3","Low3","Low3","High4","Low4","Low1","High3","Low2","Low3","Low3","High3","High1","Low4","Low4","High2","Low1","Low3","Low3","High4","High3","Low4","Low4","High1","Low3","Low2","Low2","High1","High2","Low1","Low1","High3","High2","Low1","Low1","High1","Low2","Low2","High4","Low3","Low4","Low4","High2","Low1","High3","High4","Low3","Low4","Low2","High4","Low3","Low2","Low4","High1","Low1","Low2","High3","Low4","High1","Low2","Low1","High4","Low3","Low3","Low2","High3","High4","Low4","Low1","Low1","High4","Low3","Low4","High3","Low1","Low3","High4","Low4","Low3","Low2","High3","Low1","Low1","High1","Low3","Low4","High4","Low1","Low1","Low2","High4","Low4","Low3","Low2","High3","Low1","Low4","Low4","High2","Low3","Low2","Low2","High4","Low1","Low1","Low3","Low3","Wild","Low4","Low4","Low3","High2","Low4","High4","Low2","Low3","High2","Low2","Low3","Low3","High4","Low2","Low2","Low3","High3","Low4","Low3","Low3","High1","High2","Low4","High3","Low2","High1","Low4","High4","Low1","Low3","Wild","Low2","Low2","Low4","High4","Low3","High1"];
            $this->reelStrips['BaseReelsHighSync4']=["High3","Low4","Low4","High4","High3","Low3","Low3","High4","Freespin","Low1","Low1","High1","High4","Low2","Low2","High2","Low3","High1","High1","Low3","Low1","Low4","Low4","High2","Low2","Low4","Low4","High1","Low2","Low2","Low2","High3","Low3","Low4","Low4","High2","High3","Low4","Low4","Low1","High2","Low2","Low2","Low3","Low4","High2","High2","Low4","Low1","Low3","Low3","High3","High2","Low1","Low1","High4","Low4","Low1","Low3","Low3","High3","High3","Low2","High2","Low1","Low1","High4","High2","Low4","Low4","High1","Low2","Low1","Low4","Low4","High3","High2","Low2","Low2","High4","High3","Low3","Low3","High4","Low4","Low1","High3","Low2","Low3","Low3","High3","High1","Low4","Low4","High2","Low1","Low3","Low3","High4","High3","Low4","Low4","High1","Low3","Low2","Low2","High1","High2","Low1","Low1","High3","High2","Low1","Low1","High1","Low2","Low2","High4","Low3","Low4","Low4","High2","Low1","High3","High4","Low3","Low4","Low2","High4","Low3","Low2","Low4","High1","Low1","Low2","High3","Low4","High1","Low2","Low1","High4","Low3","Low3","Low2","High4","High4","Low4","Low1","Low1","High4","Low3","Low4","High3","Low1","Low3","High4","Low4","Low3","Low2","High3","Low1","Low1","High1","Low3","Low4","High4","Low1","Low1","Low2","High4","Low4","Low3","Low2","High3","Low1","Low4","Low4","High2","Low3","Low2","Low2","High4","Low1","Low1","Low3","Low3","Wild","Low4","Low4","Low3","High2","Low4","High4","Low2","Low3","High2","Low2","Low3","Low3","High4","Low2","Low2","Low3","High3","Low4","Low3","Low3","High1","High2","Low4","High3","Low2","High1","Low4","High4","Low1","Low3","Wild","Low2","Low2","Low4","High4","Low3","High1","High3","Low4","Low4","High4","High3","Low3","Low3","High4","Freespin","Low1","Low1","High1","High4","Low2","Low2","High2","Low3","High2","High1","Low3","Low1","Low4","Low4","High2","Low2","Low4","Low4","High1","Low2","Low2","Low2","High3","Low3","Low4","Low4","High2","High3","Low4","Low4","Low1","High2","Low2","Low2","Low3","Low4","High3","High2","Low4","Low1","Low3","Low3","High3","High2","Low1","Low1","High4","Low4","Low1","Low3","Low3","High4","High3","Low2","High2","Low1","Low1","High4","High2","Low4","Low4","High1","Low2","Low1","Low4","Low4","High3","High2","Low2","Low2","High4","High3","Low3","Low3","High4","Low4","Low1","High3","Low2","Low3","Low3","High3","High1","Low4","Low4","High2","Low1","Low3","Low3","High4","High3","Low4","Low4","High1","Low3","Low2","Low2","High1","High2","Low1","Low1","High3","High2","Low1","Low1","High1","Low2","Low2","High4","Low3","Low4","Low4","High2","Low1","High3","High4","Low3","Low4","Low2","High4","Low3","Low2","Low4","High1","Low1","Low2","High3","Low4","High1","Low2","Low1","High4","Low3","Low3","Low2","High3","High4","Low4","Low1","Low1","High4","Low3","Low4","High3","Low1","Low3","High4","Low4","Low3","Low2","High3","Low1","Low1","High1","Low3","Low4","High4","Low1","Low1","Low2","High4","Low4","Low3","Low2","High3","Low1","Low4","Low4","High2","Low3","Low2","Low2","High4","Low1","Low1","Low3","Low3","Wild","Low4","Low4","Low3","High2","Low4","High4","Low2","Low3","High2","Low2","Low3","Low3","High4","Low2","Low2","Low3","High3","Low4","Low3","Low3","High1","High2","Low4","High3","Low2","High1","Low4","High4","Low1","Low3","Wild","Low2","Low2","Low4","High4","Low3","High1"];
            $this->reelStrips['BaseReelsHighSync5']=["High3","Low4","Low4","High4","High3","Low3","Low3","High4","Freespin","Low1","Low1","High1","High4","Low2","Low2","High2","Low3","High1","High1","Low3","Low1","Low4","Low4","High2","Low2","Low4","Low4","High1","Low2","Low2","Low2","High3","Low3","Low4","Low4","High2","High3","Low4","Low4","Low1","High2","Low2","Low2","Low3","Low4","High2","High2","Low4","Low1","Low3","Low3","High3","High2","Low1","Low1","High4","Low4","Low1","Low3","Low3","High3","High3","Low2","High2","Low1","Low1","High4","High2","Low4","Low4","High1","Low2","Low1","Low4","Low4","High3","High2","Low2","Low2","High4","High3","Low3","Low3","High4","Low4","Low1","High3","Low2","Low3","Low3","High3","High1","Low4","Low4","High2","Low1","Low3","Low3","High4","High3","Low4","Low4","High1","Low3","Low2","Low2","High1","High2","Low1","Low1","High3","High2","Low1","Low1","High1","Low2","Low2","High4","Low3","Low4","Low4","High2","Low1","High3","High4","Low3","Low4","Low2","High4","Low3","Low2","Low4","High1","Low1","Low2","High3","Low4","High1","Low2","Low1","High4","Low3","Low3","Low2","High4","High4","Low4","Low1","Low1","High4","Low3","Low4","High3","Low1","Low3","High4","Low4","Low3","Low2","High3","Low1","Low1","High1","Low3","Low4","High4","Low1","Low1","Low2","High4","Low4","Low3","Low2","High3","Low1","Low4","Low4","High2","Low3","Low2","Low2","High4","Low1","Low1","Low3","Low3","Wild","Low4","Low4","Low3","High2","Low4","High4","Low2","Low3","High2","Low2","Low3","Low3","High4","Low2","Low2","Low3","High3","Low4","Low3","Low3","High1","High2","Low4","High3","Low2","High1","Low4","High4","Low1","Low3","Wild","Low2","Low2","Low4","High4","Low3","High1","High3","Low4","Low4","High4","High3","Low3","Low3","High4","Freespin","Low1","Low1","High1","High4","Low2","Low2","High2","Low3","High2","High1","Low3","Low1","Low4","Low4","High2","Low2","Low4","Low4","High1","Low2","Low2","Low2","High3","Low3","Low4","Low4","High2","High3","Low4","Low4","Low1","High2","Low2","Low2","Low3","Low4","High3","High2","Low4","Low1","Low3","Low3","High3","High2","Low1","Low1","High4","Low4","Low1","Low3","Low3","High4","High3","Low2","High2","Low1","Low1","High4","High2","Low4","Low4","High1","Low2","Low1","Low4","Low4","High3","High2","Low2","Low2","High4","High3","Low3","Low3","High4","Low4","Low1","High3","Low2","Low3","Low3","High3","High1","Low4","Low4","High2","Low1","Low3","Low3","High4","High3","Low4","Low4","High1","Low3","Low2","Low2","High1","High2","Low1","Low1","High3","High2","Low1","Low1","High1","Low2","Low2","High4","Low3","Low4","Low4","High2","Low1","High3","High4","Low3","Low4","Low2","High4","Low3","Low2","Low4","High1","Low1","Low2","High3","Low4","High1","Low2","Low1","High4","Low3","Low3","Low2","High3","High4","Low4","Low1","Low1","High4","Low3","Low4","High3","Low1","Low3","High4","Low4","Low3","Low2","High3","Low1","Low1","High1","Low3","Low4","High4","Low1","Low1","Low2","High4","Low4","Low3","Low2","High3","Low1","Low4","Low4","High2","Low3","Low2","Low2","High4","Low1","Low1","Low3","Low3","Wild","Low4","Low4","Low3","High2","Low4","High4","Low2","Low3","High2","Low2","Low3","Low3","High4","Low2","Low2","Low3","High3","Low4","Low3","Low3","High1","High2","Low4","High3","Low2","High1","Low4","High4","Low1","Low3","Wild","Low2","Low2","Low4","High4","Low3","High1"];
            $this->reelStrips['BaseReelsHighSync6']=["High3","Low4","Low4","High4","High3","Low3","Low3","High4","Freespin","Low1","Low1","High1","High4","Low2","Low2","High2","Low3","High1","High1","Low3","Low1","Low4","Low4","High2","Low2","Low4","Low4","High1","Low2","Low2","Low2","High3","Low3","Low4","Low4","High2","High3","Low4","Low4","Low1","High2","Low2","Low2","Low3","Low4","High2","High2","Low4","Low1","Low3","Low3","High3","High2","Low1","Low1","High4","Low4","Low1","Low3","Low3","High3","High3","Low2","High2","Low1","Low1","High4","High2","Low4","Low4","High1","Low2","Low1","Low4","Low4","High3","High2","Low2","Low2","High4","High3","Low3","Low3","High4","Low4","Low1","High3","Low2","Low3","Low3","High3","High1","Low4","Low4","High2","Low1","Low3","Low3","High4","High3","Low4","Low4","High1","Low3","Low2","Low2","High1","High2","Low1","Low1","High3","High2","Low1","Low1","High1","Low2","Low2","High4","Low3","Low4","Low4","High2","Low1","High3","High4","Low3","Low4","Low2","High4","Low3","Low2","Low4","High1","Low1","Low2","High3","Low4","High1","Low2","Low1","High4","Low3","Low3","Low2","High4","High4","Low4","Low1","Low1","High4","Low3","Low4","High3","Low1","Low3","High4","Low4","Low3","Low2","High3","Low1","Low1","High1","Low3","Low4","High4","Low1","Low1","Low2","High4","Low4","Low3","Low2","High3","Low1","Low4","Low4","High2","Low3","Low2","Low2","High4","Low1","Low1","Low3","Low3","Wild","Low4","Low4","Low3","High2","Low4","High4","Low2","Low3","High2","Low2","Low3","Low3","High4","Low2","Low2","Low3","High3","Low4","Low3","Low3","High1","High2","Low4","High3","Low2","High1","Low4","High4","Low1","Low3","Wild","Low2","Low2","Low4","High4","Low3","High1","High3","Low4","Low4","High4","High3","Low3","Low3","High4","Freespin","Low1","Low1","High1","High4","Low2","Low2","High2","Low3","High2","High1","Low3","Low1","Low4","Low4","High2","Low2","Low4","Low4","High1","Low2","Low2","Low2","High3","Low3","Low4","Low4","High2","High3","Low4","Low4","Low1","High2","Low2","Low2","Low3","Low4","High3","High2","Low4","Low1","Low3","Low3","High3","High2","Low1","Low1","High4","Low4","Low1","Low3","Low3","High4","High3","Low2","High2","Low1","Low1","High4","High2","Low4","Low4","High1","Low2","Low1","Low4","Low4","High3","High2","Low2","Low2","High4","High3","Low3","Low3","High4","Low4","Low1","High3","Low2","Low3","Low3","High3","High1","Low4","Low4","High2","Low1","Low3","Low3","High4","High3","Low4","Low4","High1","Low3","Low2","Low2","High1","High2","Low1","Low1","High3","High2","Low1","Low1","High1","Low2","Low2","High4","Low3","Low4","Low4","High2","Low1","High3","High4","Low3","Low4","Low2","High4","Low3","Low2","Low4","High1","Low1","Low2","High3","Low4","High1","Low2","Low1","High4","Low3","Low3","Low2","High3","High4","Low4","Low1","Low1","High4","Low3","Low4","High3","Low1","Low3","High4","Low4","Low3","Low2","High3","Low1","Low1","High1","Low3","Low4","High4","Low1","Low1","Low2","High4","Low4","Low3","Low2","High3","Low1","Low4","Low4","High2","Low3","Low2","Low2","High4","Low1","Low1","Low3","Low3","Wild","Low4","Low4","Low3","High2","Low4","High4","Low2","Low3","High2","Low2","Low3","Low3","High4","Low2","Low2","Low3","High3","Low4","Low3","Low3","High1","High2","Low4","High3","Low2","High1","Low4","High4","Low1","Low3","Wild","Low2","Low2","Low4","High4","Low3","High1"];
            $this->reelStrips['BaseReelsLeft1']=["High2","Low4","Low4","High4","Low4","Low3","Low2","Low2","High1","Freespin","Low4","Low3","Low3","Low2","Low1","High4","Low3","Low3","Low3","Low2","Low1","Low3","Low3","High3","Low1","Low1","Low3","Low3","Low3","Freespin","Low1","Low1","High1","High1","High3","High3","Low3","Low1","Low1","Low1","High1","High1","High2","High3","Low1","Low1","Low1","Freespin","Low3","High3","High3","High1","High1","Low3","Low3","Low1","Low1","Low1","High3","Low3","High3","Freespin","Low1","Low1","High1","High1","High3","High3","Low1","Low1","Low3","Low3","Low3","High3","High1","High3","Low3","Low3","Low1","Low1","High3","High3","High1","High1","Low3","Low3","Low1","Low3","Low3","High3","High3","Low3","High1","High1","Low1","Low3","High3","High3","Low3","High1","High1","Low3","Low3","Low1","Low3","Low3","Low1","High3","High3","Low3","Low1","Low1","High3","Low3","Low3","Low1","Low1","High3","Low3","Low3","High3","High3","Low1","Low3","Low1","Low1","Low3","Low3","High2","Low4","Low4","High4","Low4","Low3","Low2","Low2","High1","Freespin","Low4","Low3","Low2","Low1","High4","Low3","Low3","Low3","Low2","Low1","Low3","Low3","High3","Low1","Low1","Low1","Low3","Low3","Low3","Low1","Low1","High1","High1","Low3","Low3","High3","Low1","Low1","Low1","High1","High1","High2","Low1","Low1","Low1","High1","Low3","Low3","High3","High3","High1","High1","Low3","Low3","Low1","Low1","Low1","High1","High1","High3","Low3","High3","Low1","Low1","High1","High3","High3","Low1","Low3","Low3","Low3","High3","High1","Low3","Low3","High3","Low1","Low1","High3","High3","Low3","Low3","Low1","Low3","Low3","High3","High3","Low3","High1","High1","Low1","Low1","Low3","High3","High3","Low3","High1","High1","Low3","Low3","High3","High3","Low1","Low3","Low3","Low1","High3","High3","Low3","Low1","Low1","High3","Low3","Low3","Low1","Low1","High3","Low3","Low3","High3","High3","Low1","Low3","Low1","Low1","Low3","Low3","Low1","Low3"];
            $this->reelStrips['BaseReelsLeft2']=["Low1","Low1","Low2","Low2","Low2","Freespin","High3","High4","High4","Low3","Low1","Low4","Low4","Low2","Wild","High1","Low3","Low1","Low4","Low4","Low2","Low2","Low4","Low4","High2","Low3","Low3","Low1","High4","High4","High4","Low4","Low4","Low4","Low3","High4","High4","Low4","Low2","Low2","Low4","Wild","High4","High4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","Low4","High1","High2","Low2","Low2","Low4","Low4","Low2","Low2","Freespin","High2","High2","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Low2","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High4","High4","Low4","High2","High2","High2","Low2","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","Low2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low2","Low4","Low4","Low2","Low2","Low2","Freespin","High3","High4","High4","Low4","Low4","Low4","Low2","Low4","Low4","Low2","Low2","High2","High4","High4","High4","Low4","Low4","Low4","High4","High4","Freespin","Low4","Low2","Low2","Low4","Low4","Low2","Low2","High4","High4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","Low4","High2","Low2","Low4","Low4","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Wild","Low2","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High2","High2","High4","High4","Low4","Low2","Low2","High2","High2","High2","Low2","Low4","Low4","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","High2","High2","Low4","Low2","Low2","High4","Wild","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low4","Low4","Low2","Low2","High2","High4","Low4","Low4","Low2","Low2"];
            $this->reelStrips['BaseReelsLeft3']=["High2","Low4","Low4","High4","Wild","Low4","Low3","Low2","Low2","High1","Freespin","Low4","Low3","Low3","Low2","Low1","High4","Low3","Low3","Low3","Low2","Wild","Low1","Low3","Low3","High3","Low1","Low1","Low3","Low3","Low3","Freespin","Low1","Low1","High1","High1","High3","High3","Low3","Low1","Low1","Low1","High1","High1","High2","High3","Low1","Low1","Low1","Freespin","Low3","High3","High3","High1","High1","Low3","Low3","Low1","Low1","Low1","High3","Low3","High3","Freespin","Low1","Low1","High1","High1","High3","High3","Low1","Low1","Low3","Low3","Low3","High3","High1","Wild","High3","Low3","Low3","Low1","Low1","High3","High3","High1","High1","Low3","Low3","Low1","Low3","Low3","High3","High3","Low3","High1","High1","Low1","Low3","High3","High3","Low3","High1","High1","Low3","Low3","Low1","Low3","Low3","Low1","High3","High3","Low3","Low1","Low1","High3","Low3","Low3","Low1","Low1","High3","Low3","Low3","High3","High3","Low1","Low3","Low1","Low1","Low3","Low3","High2","Low4","Low4","High4","Low4","Low3","Low2","Low2","High1","Freespin","Low4","Low3","Low2","Wild","Low1","High4","Low3","Low3","Low3","Low2","Low1","Low3","Low3","Wild","High3","Low1","Low1","Low1","Low3","Low3","Low3","Low1","Low1","High1","High1","Low3","Low3","High3","Low1","Low1","Low1","High1","High1","High2","Low1","Low1","Low1","High1","Low3","Low3","High3","High3","High1","High1","Low3","Low3","Low1","Low1","Low1","High1","High1","High3","Low3","High3","Low1","Low1","High1","High3","High3","Low1","Low3","Low3","Low3","High3","High1","Low3","Low3","High3","Low1","Low1","High3","High3","Low3","Low3","Low1","Low3","Low3","High3","High3","Low3","High1","High1","Low1","Low1","Low3","High3","High3","Low3","High1","High1","Low3","Low3","High3","High3","Low1","Low3","Low3","Low1","High3","High3","Low3","Low1","Low1","High3","Low3","Low3","Low1","Low1","High3","Low3","Low3","High3","High3","Low1","Low3","Low1","Low1","Low3","Low3","Low1","Low3"];
            $this->reelStrips['BaseReelsLeft4']=["Low1","Low1","Low2","Low2","Low2","Freespin","High3","High4","High4","Low3","Low1","Low4","Low4","Low2","Wild","High1","Low3","Low1","Low4","Low4","Low2","Low2","Low4","Low4","High2","Low3","Low3","Low1","High4","High4","High4","Low4","Low4","Low4","Low3","High4","High4","Low4","Low2","Low2","Low4","Wild","High4","High4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","Low4","High1","High2","Low2","Low2","Low4","Low4","Low2","Low2","Freespin","High2","High2","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Low2","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High4","High4","Low4","High2","High2","High2","Low2","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","Low2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low2","Low4","Low4","Low2","Low2","Low2","Freespin","High3","High4","High4","Low4","Low4","Low4","Low2","Low4","Low4","Low2","Low2","High2","High4","High4","High4","Low4","Low4","Low4","High4","High4","Freespin","Low4","Low2","Low2","Low4","Low4","Low2","Low2","High4","High4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","Low4","High2","Low2","Low4","Low4","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Wild","Low2","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High2","High2","High4","High4","Low4","Low2","Low2","High2","High2","High2","Low2","Low4","Low4","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","High2","High2","Low4","Low2","Low2","High4","Wild","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low4","Low4","Low2","Low2","High2","High4","Low4","Low4","Low2","Low2"];
            $this->reelStrips['BaseReelsLeft5']=["High2","Low4","Low4","High4","Wild","Low4","Low3","Low2","Low2","High1","Freespin","Low4","Low3","Low3","Low2","Low1","High4","Low3","Low3","Low3","Low2","Wild","Low1","Low3","Low3","High3","Low1","Low1","Low3","Low3","Low3","Freespin","Low1","Low1","High1","High1","High3","High3","Low3","Low1","Low1","Low1","High1","High1","High2","High3","Low1","Low1","Low1","Freespin","Low3","High3","High3","High1","High1","Low3","Low3","Low1","Low1","Low1","High3","Low3","High3","Freespin","Low1","Low1","High1","High1","High3","High3","Low1","Low1","Low3","Low3","Low3","High3","High1","Wild","High3","Low3","Low3","Low1","Low1","High3","High3","High1","High1","Low3","Low3","Low1","Low3","Low3","High3","High3","Low3","High1","High1","Low1","Low3","High3","High3","Low3","High1","High1","Low3","Low3","Low1","Low3","Low3","Low1","High3","High3","Low3","Low1","Low1","High3","Low3","Low3","Low1","Low1","High3","Low3","Low3","High3","High3","Low1","Low3","Low1","Low1","Low3","Low3","High2","Low4","Low4","High4","Low4","Low3","Low2","Low2","High1","Freespin","Low4","Low3","Low2","Wild","Low1","High4","Low3","Low3","Low3","Low2","Low1","Low3","Low3","Wild","High3","Low1","Low1","Low1","Low3","Low3","Low3","Low1","Low1","High1","High1","Low3","Low3","High3","Low1","Low1","Low1","High1","High1","High2","Low1","Low1","Low1","High1","Low3","Low3","High3","High3","High1","High1","Low3","Low3","Low1","Low1","Low1","High1","High1","High3","Low3","High3","Low1","Low1","High1","High3","High3","Low1","Low3","Low3","Low3","High3","High1","Low3","Low3","High3","Low1","Low1","High3","High3","Low3","Low3","Low1","Low3","Low3","High3","High3","Low3","High1","High1","Low1","Low1","Low3","High3","High3","Low3","High1","High1","Low3","Low3","High3","High3","Low1","Low3","Low3","Low1","High3","High3","Low3","Low1","Low1","High3","Low3","Low3","Low1","Low1","High3","Low3","Low3","High3","High3","Low1","Low3","Low1","Low1","Low3","Low3","Low1","Low3"];
            $this->reelStrips['BaseReelsLeft6']=["Low1","Low1","Low2","Low2","Low2","Freespin","High3","High4","High4","Low3","Low1","Low4","Low4","Low2","Wild","High1","Low3","Low1","Low4","Low4","Low2","Low2","Low4","Low4","High2","Low3","Low3","Low1","High4","High4","High4","Low4","Low4","Low4","Low3","High4","High4","Low4","Low2","Low2","Low4","Wild","High4","High4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","Low4","High1","High2","Low2","Low2","Low4","Low4","Low2","Low2","Freespin","High2","High2","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Low2","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High4","High4","Low4","High2","High2","High2","Low2","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","Low2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low2","Low4","Low4","Low2","Low2","Low2","Freespin","High3","High4","High4","Low4","Low4","Low4","Low2","Low4","Low4","Low2","Low2","High2","High4","High4","High4","Low4","Low4","Low4","High4","High4","Freespin","Low4","Low2","Low2","Low4","Low4","Low2","Low2","High4","High4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","Low4","High2","Low2","Low4","Low4","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Wild","Low2","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High2","High2","High4","High4","Low4","Low2","Low2","High2","High2","High2","Low2","Low4","Low4","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","High2","High2","Low4","Low2","Low2","High4","Wild","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low4","Low4","Low2","Low2","High2","High4","Low4","Low4","Low2","Low2"];
            $this->reelStrips['BaseReelsRight1']=["Low1","Low1","Low2","Low2","Low2","Freespin","High3","High4","High4","Low3","Low1","Low4","Low4","Low2","High1","Low3","Low1","Low4","Low4","Low2","Low2","Low4","Low4","High2","Low3","Low3","Low1","High4","High4","High4","Low4","Low4","Low4","Low3","High4","High4","Low4","Low2","Low2","Low4","High4","High4","Low2","Low2","Freespin","Low4","Low4","High4","High4","Low1","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","Low4","High1","High2","Low2","Low2","Low4","Low4","Low2","Low2","Freespin","High2","High2","High4","High4","High4","Low4","Low3","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Low3","Low1","Low1","Low3","High2","High2","Low4","Low2","Low2","High4","High4","Low4","High2","High2","High2","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","Low2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low2","Low4","Low4","Low2","Low2","Low2","Freespin","High3","High4","High4","Low4","Low4","Low4","Low2","Low4","Low4","Low2","Low2","High2","High4","High4","High4","Low4","Low4","Low4","High4","High4","Freespin","Low4","Low2","Low2","Low4","Low4","Low2","Low2","High4","High4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","Low4","High2","Low2","Low4","Low4","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Low2","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High2","High2","High4","High4","Low4","Low2","Low2","High2","High2","High2","Low2","Low4","Low4","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low4","Low4","Low2","Low4","Low4","Low2","Low2","High2","High4","Low4","Low4","Low2","Low2"];
            $this->reelStrips['BaseReelsRight2']=["High2","Low4","Low4","High4","Wild","Low1","Low3","Low1","Low1","High1","Low1","Low3","Low3","Low1","Low1","High4","Low3","Low3","Low3","Low2","Wild","Low1","Low3","Low3","High3","Low1","Low1","Low3","Low3","Low3","Freespin","Low1","Low1","High1","High1","High3","High3","Low3","Low1","Low1","Low1","High1","High1","High2","High3","Low1","Low1","Low1","Freespin","Low3","High3","High3","High1","High1","Low3","Low3","Low1","Low1","Low1","High3","Low3","High3","Freespin","Low1","Low1","High1","High1","High3","High3","Low1","Low1","Low3","Low3","Low3","High3","High1","Wild","High3","Low3","Low3","Low1","Low1","High3","High3","High1","High1","Low3","Low3","Low1","Low3","Low3","High3","High3","Low3","High1","High1","Low1","Low3","High3","High3","Low3","High1","High1","Low3","Low3","Low1","Low3","Low3","Low1","High3","High3","Low3","Low1","Low1","High3","Low3","Low3","Low1","Low1","High3","Low3","Low3","High3","High3","Low1","Low3","Low1","Low1","Low3","Low3","High2","Low4","Low4","High4","Low4","Low3","Low2","Low2","High1","Freespin","Low1","Low3","Low2","Low1","Low1","High4","Low3","Low3","Low3","Low2","Low1","Low1","Low3","Low3","Low1","High3","Low1","Low1","Low1","Low3","Low3","Low3","Low1","Low1","High1","High1","Low3","Low3","High3","Low1","Low1","Low1","High1","High1","High2","Low1","Low1","Low1","High1","Low3","Low3","High3","High3","High1","High1","Low3","Low3","Low1","Low1","Low1","High1","High1","High3","Low3","High3","Low1","Low1","High1","High3","High3","Low1","Low3","Low3","Low3","High3","High1","Low3","Low3","High3","Low1","Low1","High3","High3","Low3","Low3","Wild","Low3","Low3","High3","High3","Low3","High1","High1","Low1","Low1","Low3","High3","High3","Low3","High1","High1","Low3","Low3","High3","High3","Low1","Low3","Low3","Low1","High3","High3","Low3","Low1","Low1","High3","Low3","Low3","Low1","Low1","High3","Low3","Low3","High3","High3","Low1","Low3","Low1","Low1","Low3","Low3","Low1","Low3"];
            $this->reelStrips['BaseReelsRight3']=["Low1","Low1","Low2","Low2","Low2","Freespin","High3","High4","High4","Low3","Low1","Low4","Low4","Low2","Wild","High1","Low3","Low1","Low4","Low4","Low2","Low2","Low4","Low4","High2","Low3","Low3","Low1","High4","High4","High4","Low4","Low4","Low4","Low3","High4","High4","Low4","Low2","Low2","Low4","Wild","High4","High4","Low2","Low2","Freespin","Low4","Low4","High4","High4","Low1","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","Low4","High1","High2","Low2","Low2","Low4","Low4","Low2","Low2","Freespin","High2","High2","High4","High4","High4","Low4","Low3","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Low3","Low1","Low1","Low3","High2","High2","Low4","Low2","Low2","High4","High4","Low4","High2","High2","High2","Wild","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","Low2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low2","Low4","Low4","Low2","Low2","Low2","Freespin","High3","High4","High4","Low4","Low4","Low4","Low2","Low4","Low4","Low2","Low2","High2","High4","High4","High4","Low4","Low4","Low4","High4","High4","Freespin","Low4","Low2","Low2","Low4","Low4","Low2","Low2","High4","High4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","Low4","High2","Low2","Low4","Low4","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Wild","Low2","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High2","High2","High4","High4","Low4","Low2","Low2","High2","High2","High2","Low2","Low4","Low4","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","High2","High2","Low4","Low2","Low2","High4","Wild","Low4","Low4","Low2","Low2","Low4","Low4","Low2","Low4","Low4","Low2","Low2","High2","High4","Low4","Low4","Low2","Low2"];
            $this->reelStrips['BaseReelsRight4']=["High2","Low4","Low4","High4","Wild","Low1","Low3","Low1","Low1","High1","Low1","Low3","Low3","Low1","Low1","High4","Low3","Low3","Low3","Low2","Wild","Low1","Low3","Low3","High3","Low1","Low1","Low3","Low3","Low3","Freespin","Low1","Low1","High1","High1","High3","High3","Low3","Low1","Low1","Low1","High1","High1","High2","High3","Low1","Low1","Low1","Freespin","Low3","High3","High3","High1","High1","Low3","Low3","Low1","Low1","Low1","High3","Low3","High3","Freespin","Low1","Low1","High1","High1","High3","High3","Low1","Low1","Low3","Low3","Low3","High3","High1","Wild","High3","Low3","Low3","Low1","Low1","High3","High3","High1","High1","Low3","Low3","Low1","Low3","Low3","High3","High3","Low3","High1","High1","Low1","Low3","High3","High3","Low3","High1","High1","Low3","Low3","Low1","Low3","Low3","Low1","High3","High3","Low3","Low1","Low1","High3","Low3","Low3","Low1","Low1","High3","Low3","Low3","High3","High3","Low1","Low3","Low1","Low1","Low3","Low3","High2","Low4","Low4","High4","Low4","Low3","Low2","Low2","High1","Freespin","Low1","Low3","Low2","Low1","Low1","High4","Low3","Low3","Low3","Low2","Low1","Low1","Low3","Low3","Low1","High3","Low1","Low1","Low1","Low3","Low3","Low3","Low1","Low1","High1","High1","Low3","Low3","High3","Low1","Low1","Low1","High1","High1","High2","Low1","Low1","Low1","High1","Low3","Low3","High3","High3","High1","High1","Low3","Low3","Low1","Low1","Low1","High1","High1","High3","Low3","High3","Low1","Low1","High1","High3","High3","Low1","Low3","Low3","Low3","High3","High1","Low3","Low3","High3","Low1","Low1","High3","High3","Low3","Low3","Wild","Low3","Low3","High3","High3","Low3","High1","High1","Low1","Low1","Low3","High3","High3","Low3","High1","High1","Low3","Low3","High3","High3","Low1","Low3","Low3","Low1","High3","High3","Low3","Low1","Low1","High3","Low3","Low3","Low1","Low1","High3","Low3","Low3","High3","High3","Low1","Low3","Low1","Low1","Low3","Low3","Low1","Low3"];
            $this->reelStrips['BaseReelsRight5']=["Low1","Low1","Low2","Low2","Low2","Freespin","High3","High4","High4","Low3","Low1","Low4","Low4","Low2","Wild","High1","Low3","Low1","Low4","Low4","Low2","Low2","Low4","Low4","High2","Low3","Low3","Low1","High4","High4","High4","Low4","Low4","Low4","Low3","High4","High4","Low4","Low2","Low2","Low4","Wild","High4","High4","Low2","Low2","Freespin","Low4","Low4","High4","High4","Low1","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","Low4","High1","High2","Low2","Low2","Low4","Low4","Low2","Low2","Freespin","High2","High2","High4","High4","High4","Low4","Low3","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Low3","Low1","Low1","Low3","High2","High2","Low4","Low2","Low2","High4","High4","Low4","High2","High2","High2","Wild","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","Low2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low2","Low4","Low4","Low2","Low2","Low2","Freespin","High3","High4","High4","Low4","Low4","Low4","Low2","Low4","Low4","Low2","Low2","High2","High4","High4","High4","Low4","Low4","Low4","High4","High4","Freespin","Low4","Low2","Low2","Low4","Low4","Low2","Low2","High4","High4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","Low4","High2","Low2","Low4","Low4","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Wild","Low2","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High2","High2","High4","High4","Low4","Low2","Low2","High2","High2","High2","Low2","Low4","Low4","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","High2","High2","Low4","Low2","Low2","High4","Wild","Low4","Low4","Low2","Low2","Low4","Low4","Low2","Low4","Low4","Low2","Low2","High2","High4","Low4","Low4","Low2","Low2"];
            $this->reelStrips['BaseReelsRight6']=["High2","Low4","Low4","High4","Wild","Low1","Low3","Low1","Low1","High1","Low1","Low3","Low3","Low1","Low1","High4","Low3","Low3","Low3","Low2","Wild","Low1","Low3","Low3","High3","Low1","Low1","Low3","Low3","Low3","Freespin","Low1","Low1","High1","High1","High3","High3","Low3","Low1","Low1","Low1","High1","High1","High2","High3","Low1","Low1","Low1","Freespin","Low3","High3","High3","High1","High1","Low3","Low3","Low1","Low1","Low1","High3","Low3","High3","Freespin","Low1","Low1","High1","High1","High3","High3","Low1","Low1","Low3","Low3","Low3","High3","High1","Wild","High3","Low3","Low3","Low1","Low1","High3","High3","High1","High1","Low3","Low3","Low1","Low3","Low3","High3","High3","Low3","High1","High1","Low1","Low3","High3","High3","Low3","High1","High1","Low3","Low3","Low1","Low3","Low3","Low1","High3","High3","Low3","Low1","Low1","High3","Low3","Low3","Low1","Low1","High3","Low3","Low3","High3","High3","Low1","Low3","Low1","Low1","Low3","Low3","High2","Low4","Low4","High4","Low4","Low3","Low2","Low2","High1","Freespin","Low1","Low3","Low2","Low1","Low1","High4","Low3","Low3","Low3","Low2","Low1","Low1","Low3","Low3","Low1","High3","Low1","Low1","Low1","Low3","Low3","Low3","Low1","Low1","High1","High1","Low3","Low3","High3","Low1","Low1","Low1","High1","High1","High2","Low1","Low1","Low1","High1","Low3","Low3","High3","High3","High1","High1","Low3","Low3","Low1","Low1","Low1","High1","High1","High3","Low3","High3","Low1","Low1","High1","High3","High3","Low1","Low3","Low3","Low3","High3","High1","Low3","Low3","High3","Low1","Low1","High3","High3","Low3","Low3","Wild","Low3","Low3","High3","High3","Low3","High1","High1","Low1","Low1","Low3","High3","High3","Low3","High1","High1","Low3","Low3","High3","High3","Low1","Low3","Low3","Low1","High3","High3","Low3","Low1","Low1","High3","Low3","Low3","Low1","Low1","High3","Low3","Low3","High3","High3","Low1","Low3","Low1","Low1","Low3","Low3","Low1","Low3"];
            $this->reelStrips['FeatureReels1']=["High2","Low4","Low4","High4","Low4","Low3","Low2","Low2","High1","Low4","Low3","Low3","Low2","Low1","Low3","Low3","Low3","Low2","Low1","Low3","Low3","High3","Low1","Low1","Low3","Low3","Low3","Low1","Low1","High1","High1","High3","High3","Low3","Low1","Low1","Low1","High1","High1","High3","Low1","Low1","Low3","High3","High3","High1","High1","Low3","Low3","Low1","Low1","High3","Low3","High3","Low1","Low1","High1","High1","High3","High3","Low1","Low1","Low3","Low3","High3","High1","High3","Low3","Low3","Low1","Low1","High3","High3","High1","High1","Low3","Low3","Low1","Low1","Low3","Low3","High3","High3","Low3","High1","High1","Low1","Low3","High3","High3","Low3","High1","High1","Low3","Low3","Low1","Low3","Low3","Low1","High3","High3","Low3","Low1","Low1","High3","Low3","Low3","Low1","Low1","High3","Low3","Low3","High3","High3","Low1","Low3","Low3","Low1","Low1","Low3","Low3","Low4","Low4","Low4","Low3","Low2","Low2","High1","Low4","Low3","Low3","Low2","Low1","Low3","Low3","Low2","Low1","Low3","Low3","High3","Low1","Low1","Low3","Low3","Low1","Low1","Low3","Low3","High1","High1","High3","High3","Low3","Low1","Low1","Low1","High1","High1","High3","Low1","Low1","Low3","High3","High3","Low1","Low1","High1","High1","Low1","Low1","Low3","Low3","Low1","Low1","High3","Low3","High3","Low1","Low1","High1","High1","High3","High3","Low1","Low1","Low3","Low3","High3","High1","High3","Low3","Low3","Low1","Low1","High3","High3","High1","High1","Low3","Low3","Low1","Low1","Low3","Low3","High3","High3","Low3","High1","High1","Low1","Low3","High3","High3","Low3","High1","High1","Low3","Low3","Low1","Low3","Low3","Low1","High3","High3","Low3","Low1","Low1","High3","Low3","Low3","Low1","Low1","High3","Low3","Low3","High3","High3","Low1","Low3","Low3","Low1","Low1","Low3","Low3","Low4","Low4","Low3","Low2","Low2","High1","Low4","Low3","Low3","Low2","Low1","Low3","Low3","Low2","Low1","Low3","Low3","High3","Low1","Low1","Low3","Low3","Low1","Low1","Low3","Low3","High1","High1","High3","High3","Low3","Low1","Low1","High1","High1","High3","Low1","Low1","Low3","High3","High3","High1","High1","Low3","Low3","Low1","Low1","High3","Low3","High3","Low1","Low1","High1","High1","High3","High3","Low1","Low1","Low3","Low3","High3","High1","High3","Low3","Low3","Low1","Low1","Low3","Low3","High3","High3","High1","High1","Low3","Low3","Low1","Low1","Low3","Low3","High3","High3","Low3","High1","High1","Low1","Low3","High3","High3","Low3","High1","High1","Low3","Low3","Low1","Low3","Low3","Low1","High3","High3","Low3","Low1","Low1","High3","Low3","Low3","Low1","Low1","High3","Low3","Low3","High3","High3","Low1","Low3","Low3","Low1","Low1","Low3","Low3","Low4","Low4","Low3","Low2","Low2","High1","Low4","Low3","Low3","Low2","Low1","Low3","Low3","Low2","Low1","Low3","Low3","High3","Low1","Low1","Low3","Low3","Low1","Low1","Low3","Low3","High3","High3","Low3","Low1","Low1","Low4","Low4","Low3","Low3","High1","High1","Low1","Low1","High3","High3","Low3","Low1","Low1","High1","High1","High3","Low1","Low1","Low3","High3","High3","Low1","Low1","High1","High1","Low1","Low1","Low3","Low3","Low1","Low1","High3","Low3","High3","Low1","Low1","High1","High1","High3","High3","Low1","Low1","Low3","Low3","High3","High1","High3","Low3","Low3","Low1","Low1","Low3","Low3","High3","High3","High1","High1","Low3","Low3","Low1","Low1","Low3","Low3","High3","High3","Low3","High1","High1","Low1","Low3","High3","High3","Low3","High1","High1","Low3","Low3","Low1","Low3","Low3","Low1","High3","High3","Low3","Low1","Low1","High3","Low3","Low3","Low1","Low1","High3","Low3","Low3","High3","High3","Low1","Low3","Low3","Low1","Low1","Low3","Low3","High3","High3","Low3","Low1","Low1","Low3","Low3"];
            $this->reelStrips['FeatureReels2']=["High4","High4","Low4","Low4","Low2","Low2","Low2","High3","Low4","Low4","Low2","Wild","Low2","Low1","Low4","Low4","Low2","Low2","Low4","Low4","High2","High4","Low2","Low2","Low4","High4","High4","Low4","Low4","Low4","Low3","High4","High4","Low4","Low2","Low2","Low4","High4","High4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","High1","High2","Wild","Low2","Low4","Low4","High2","High2","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Low2","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High2","High4","High4","Low4","High2","High2","Low2","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High4","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Wild","Low2","Low1","Low4","Low4","Low2","Low2","Low4","Low4","High2","High4","Low4","Low4","Low2","Low2","Low4","High4","High4","Low4","Low4","Low4","Low4","Low3","High4","High4","Low4","Low2","Low2","Low4","High4","High4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","High2","Wild","Low2","Low4","Low4","High2","High2","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Low2","Low4","Low4","Low2","High2","High2","Low4","Low3","Low3","Low2","Low2","High2","High4","High4","Low4","High2","High2","Low2","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low3","Low3","Low2","Low2","Low4","Low4","High4","High4","Low4","Low4","Low2","Low2","Low2","Low2","Low4","Low4","Low2","Wild","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High2","High4","Low4","Low4","Low4","Low4","Low2","Low2","Low4","High4","High4","Low4","Low4","High4","High4","Low4","Low2","Low2","Low4","High4","High4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low4","Low4","Low2","Low2","Low4","Low4","Low4","Low4","High2","Wild","Low2","Low4","Low4","High2","High2","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Low2","Low4","Low4","Low3","Low3","Low2","High2","High2","Low4","Low2","Low2","High2","High4","High4","Low4","High2","High2","Low2","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High4","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Wild","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High2","High4","Low2","Low2","Low4","High4","High4","Low4","Low4","High4","High4","High4","High4","Low4","Low2","Low2","Low4","High4","High4","Low4","Low4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","High2","Wild","Low2","Low4","Low4","High2","High2","Low4","Low4","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Low2","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High2","High4","High4","Low4","Low2","Low2","Low2","High2","High2","Low2","Low1","Low1","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low2","Low4","Low4","High4","High4","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High4","High4","Low4","Low4","Low3","Low3","High4","High4","High4","High4","Low4","Low4","Low2","Low2","Low2","Low2","High3","Low4","Low4","Low2","Wild","Low2","Low1","Low1","Low4","Low4","Low2","Low2","Low4","Low4","High2","High4","Low2","Low2","Low4","Low3","Low3","Low1","Low1","High4","High4","Low1","Low1","Low3","Low3","Low4","Low4","Low4","Low4","Low3","Low1","Low1","High4","High4","Low4","Low2","Low2","Low1","Low1","High4","High4","Low2","Low2","Low3","Low3","Low4","Low4","High4","High4","Low4","High2","High2","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","High1","High2","Wild","Low2","Low4","Low4","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low1","Low1","Low4","Low4","Low2","Low2","High4","High2","Low2","Low1","Low1","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High2","High4","High4","Low4","High2","High2","Low2","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High4","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Wild","Low2","Low1","Low4","Low4","Low2","Low2","Low4","Low4","High2","High4","Low4","Low4","Low2","Low2","Low4","High4","High4","Low4","Low4","Low4","Low4","Low3","High4","High4","Low4","Low2","Low2","Low4","High4","High4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","High2","Wild","Low2","Low4","Low4","High2","High2","High4","High4","High4","High4","Low4","Low2","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Low2","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High2","High4","High4","Low4","High2","High2","Low2","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low1","Low1","Low4","Low4","High4","High4","Low2","High2","High2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High4","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Wild","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High2","High4","Low4","Low4","Low2","Low2","Low4","High4","High4","Low4","Low4","High4","High4","Low4","Low2","Low2","Low4","High4","High4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low4","Low4","Low2","Low2","Low4","Low4","High2","Wild","Low2","Low4","Low4","High2","High2","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Low2","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High2","High4","High4","Low4","High2","High2","High2","Low2","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High4","High4","Low4","Low4","Low2","Low2","Low2","Low2","Low4","Low4","Low2","Wild","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High2","High4","Low2","Low2","Low4","High4","High4","Low4","Low4","Low3","Low3","High4","High4","Low4","Low2","Low2","Low4","High4","High4","Low4","Low4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","High2","Wild","Low2","Low4","Low4","High2","High2","Low4","Low4","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Low2","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High2","High4","High4","Low4","Low2","Low2","Low2","High2","High2","Low2","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High4","High4","Low4","Low4"];
            $this->reelStrips['FeatureReels3']=["Low4","Low4","High4","Low4","Low3","Low2","Low2","High1","Low4","Low3","Low3","Low2","Low1","Low3","Low3","Low3","Wild","Low2","Low1","Low3","Low3","High3","Wild","Low1","Low1","Low3","Low3","Low3","Low3","Low1","Low1","High1","High1","High3","High3","Low3","Low1","Low1","Low1","Low1","High1","High1","High3","Low1","Low1","Low3","High3","High3","High3","High1","High1","High1","Low3","Low3","Low1","Low1","High3","Low3","High3","Low1","Low1","High1","High1","High3","High3","Low1","Low1","Low3","Low3","High3","High1","High3","Low3","Low3","High2","Low1","Low1","High3","High3","High3","High3","High1","High1","Low3","Low3","Low1","Low1","Low3","Low3","High3","High3","Low3","High1","High1","Low1","Low3","High3","High3","Low3","High1","High1","Low3","Low3","Low1","Low3","Low3","Low1","High3","High3","Low3","Low1","Low1","High3","Low3","Low3","Low1","Low1","High3","Low3","Low3","Low3","Low3","High3","High3","Low1","Low3","Low3","Low1","Low1","Low3","Low3","Low4","Low4","Low4","Low3","Low2","Low2","High1","Low4","Low3","Low3","Low2","Low1","Low3","Low3","Wild","Low2","Low1","Low3","Low3","High3","Wild","Low1","Low1","Low3","Low3","Low1","Low1","High2","Low3","Low3","High1","High1","High3","High3","Low3","High2","Low1","Low1","Low1","Low1","High1","High1","High3","Low1","Low1","Low3","High3","High3","Low1","Low1","High1","High1","Low1","Low1","Low3","Low3","Low1","Low1","High3","Low3","High3","Low1","Low1","High1","High1","High3","High3","Low1","Low1","Low3","Low3","High3","High1","High3","Low3","Low3","High2","Low1","Low1","High3","High3","High1","High1","Low3","Low3","Low1","Low1","Low3","Low3","High3","High3","Low3","High1","High1","High1","High1","Low1","Low3","High3","High3","Low3","High1","High1","Low3","Low3","Low1","Low3","Low3","Low1","High3","High3","Low3","Low1","Low1","High3","Low3","Low3","Low1","Low1","High3","Low3","Low3","High3","High3","Low1","Low3","Low3","Low1","Low1","Low3","Low3","Low4","Low4","Low3","Low2","Low2","High1","Low4","Low3","Low3","Low2","Low1","Low3","Low3","Wild","Low2","Low1","Low3","Low3","High3","Wild","Low1","Low1","Low3","Low3","Low1","Low1","Low3","Low3","High1","High1","High3","High3","Low3","Low1","Low1","High1","High1","High3","Low1","Low1","Low3","High3","High3","High1","High1","Low3","Low3","Low1","Low1","High3","Low3","High3","Low1","Low1","High1","High1","High3","High3","Low1","Low1","Low3","Low3","High3","High1","High3","Low3","Low3","Low1","Low1","Low3","Low3","High3","High3","High1","High1","Low3","Low3","Low1","Low1","Low3","Low3","High3","High3","Low3","High1","High1","Low1","Low3","High3","High3","Low3","High1","High1","Low3","Low3","Low1","Low3","Low3","Low1","High3","High3","Low3","Low1","Low1","High3","Low3","Low3","Low1","Low1","High3","Low3","Low3","High3","High3","Low1","Low3","Low3","Low1","Low1","Low3","Low3","Low4","Low4","Low3","Low2","Low2","High1","Low4","Low3","Low3","Low2","Low1","Low3","Low3","Wild","Low2","Low1","Low3","Low3","High3","Wild","Low1","Low1","Low3","Low3","Low1","Low1","Low3","Low3","High3","High3","Low3","Low1","Low1","Low4","Low4","Low3","Low3","High1","High1","Low1","Low1","High3","High3","Low3","Low1","Low1","High1","High1","High3","Low1","Low1","Low3","High3","High3","Low1","Low1","High1","High1","Low1","Low1","Low3","Low3","Low1","Low1","High3","Low3","High3","Low1","Low1","High1","High1","High3","High3","Low1","Low1","Low3","Low3","High3","High1","High3","Low3","Low3","Low1","Low1","Low3","Low3","High3","High3","High1","High1","Low3","Low3","Low1","Low1","Low3","Low3","High3","High3","Low3","High1","High1","Low1","Low3","High3","High3","Low3","High1","High1","Low3","Low3","Low1","Low3","Low3","Low1","High3","High3","Low3","Low1","Low1","High3","Low3","Low3","Low1","Low1","High3","Low3","Low3","High3","High3","Low1","Low3","Low3","Low1","Low1","Low3","Low3","High3","High3","Low3","Low1","Low1","Low3","Low3"];
            $this->reelStrips['FeatureReels4']=["High4","High4","Low4","Low4","Low2","Low2","Low2","High3","Low4","Low4","Low2","Wild","Low2","Low1","Low4","Low4","Low2","Low2","Low4","Low4","High2","High4","Low2","Low2","Low4","High4","High4","Low4","Low4","Low4","Low3","High4","High4","Low4","Low2","Low2","Low4","High4","High4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","High1","High2","Wild","Low2","Low4","Low4","High2","High2","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Low2","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High2","High4","High4","Low4","High2","High2","Low2","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High4","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Wild","Low2","Low1","Low4","Low4","Low2","Low2","Low4","Low4","High2","High4","Low4","Low4","Low2","Low2","Low4","High4","High4","Low4","Low4","Low4","Low4","Low3","High4","High4","Low4","Low2","Low2","Low4","High4","High4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","High2","Wild","Low2","Low4","Low4","High2","High2","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Low2","Low4","Low4","Low2","High2","High2","Low4","Low3","Low3","Low2","Low2","High2","High4","High4","Low4","High2","High2","Low2","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low3","Low3","Low2","Low2","Low4","Low4","High4","High4","Low4","Low4","Low2","Low2","Low2","Low2","Low4","Low4","Low2","Wild","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High2","High4","Low4","Low4","Low4","Low4","Low2","Low2","Low4","High4","High4","Low4","Low4","High4","High4","Low4","Low2","Low2","Low4","High4","High4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low4","Low4","Low2","Low2","Low4","Low4","Low4","Low4","High2","Wild","Low2","Low4","Low4","High2","High2","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Low2","Low4","Low4","Low3","Low3","Low2","High2","High2","Low4","Low2","Low2","High2","High4","High4","Low4","High2","High2","Low2","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High4","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Wild","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High2","High4","Low2","Low2","Low4","High4","High4","Low4","Low4","High4","High4","High4","High4","Low4","Low2","Low2","Low4","High4","High4","Low4","Low4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","High2","Wild","Low2","Low4","Low4","High2","High2","Low4","Low4","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Low2","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High2","High4","High4","Low4","Low2","Low2","Low2","High2","High2","Low2","Low1","Low1","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low2","Low4","Low4","High4","High4","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High4","High4","Low4","Low4","Low3","Low3","High4","High4","High4","High4","Low4","Low4","Low2","Low2","Low2","Low2","High3","Low4","Low4","Low2","Wild","Low2","Low1","Low1","Low4","Low4","Low2","Low2","Low4","Low4","High2","High4","Low2","Low2","Low4","Low3","Low3","Low1","Low1","High4","High4","Low1","Low1","Low3","Low3","Low4","Low4","Low4","Low4","Low3","Low1","Low1","High4","High4","Low4","Low2","Low2","Low1","Low1","High4","High4","Low2","Low2","Low3","Low3","Low4","Low4","High4","High4","Low4","High2","High2","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","High1","High2","Wild","Low2","Low4","Low4","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low1","Low1","Low4","Low4","Low2","Low2","High4","High2","Low2","Low1","Low1","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High2","High4","High4","Low4","High2","High2","Low2","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High4","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Wild","Low2","Low1","Low4","Low4","Low2","Low2","Low4","Low4","High2","High4","Low4","Low4","Low2","Low2","Low4","High4","High4","Low4","Low4","Low4","Low4","Low3","High4","High4","Low4","Low2","Low2","Low4","High4","High4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","High2","Wild","Low2","Low4","Low4","High2","High2","High4","High4","High4","High4","Low4","Low2","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Low2","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High2","High4","High4","Low4","High2","High2","Low2","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low1","Low1","Low4","Low4","High4","High4","Low2","High2","High2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High4","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Wild","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High2","High4","Low4","Low4","Low2","Low2","Low4","High4","High4","Low4","Low4","High4","High4","Low4","Low2","Low2","Low4","High4","High4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low4","Low4","Low2","Low2","Low4","Low4","High2","Wild","Low2","Low4","Low4","High2","High2","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Low2","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High2","High4","High4","Low4","High2","High2","High2","Low2","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High4","High4","Low4","Low4","Low2","Low2","Low2","Low2","Low4","Low4","Low2","Wild","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High2","High4","Low2","Low2","Low4","High4","High4","Low4","Low4","Low3","Low3","High4","High4","Low4","Low2","Low2","Low4","High4","High4","Low4","Low4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","High2","Wild","Low2","Low4","Low4","High2","High2","Low4","Low4","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Low2","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High2","High4","High4","Low4","Low2","Low2","Low2","High2","High2","Low2","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High4","High4","Low4","Low4"];
            $this->reelStrips['FeatureReels5']=["Low4","Low4","High4","Low4","Low3","Low2","Low2","High1","Low4","Low3","Low3","Low2","Low1","Low3","Low3","Low3","Wild","Low2","Low1","Low3","Low3","High3","Wild","Low1","Low1","Low3","Low3","Low3","Low3","Low1","Low1","High1","High1","High3","High3","Low3","Low1","Low1","Low1","Low1","High1","High1","High3","Low1","Low1","Low3","High3","High3","High3","High1","High1","High1","Low3","Low3","Low1","Low1","High3","Low3","High3","Low1","Low1","High1","High1","High3","High3","Low1","Low1","Low3","Low3","High3","High1","High3","Low3","Low3","High2","Low1","Low1","High3","High3","High3","High3","High1","High1","Low3","Low3","Low1","Low1","Low3","Low3","High3","High3","Low3","High1","High1","Low1","Low3","High3","High3","Low3","High1","High1","Low3","Low3","Low1","Low3","Low3","Low1","High3","High3","Low3","Low1","Low1","High3","Low3","Low3","Low1","Low1","High3","Low3","Low3","Low3","Low3","High3","High3","Low1","Low3","Low3","Low1","Low1","Low3","Low3","Low4","Low4","Low4","Low3","Low2","Low2","High1","Low4","Low3","Low3","Low2","Low1","Low3","Low3","Wild","Low2","Low1","Low3","Low3","High3","Wild","Low1","Low1","Low3","Low3","Low1","Low1","High2","Low3","Low3","High1","High1","High3","High3","Low3","High2","Low1","Low1","Low1","Low1","High1","High1","High3","Low1","Low1","Low3","High3","High3","Low1","Low1","High1","High1","Low1","Low1","Low3","Low3","Low1","Low1","High3","Low3","High3","Low1","Low1","High1","High1","High3","High3","Low1","Low1","Low3","Low3","High3","High1","High3","Low3","Low3","High2","Low1","Low1","High3","High3","High1","High1","Low3","Low3","Low1","Low1","Low3","Low3","High3","High3","Low3","High1","High1","High1","High1","Low1","Low3","High3","High3","Low3","High1","High1","Low3","Low3","Low1","Low3","Low3","Low1","High3","High3","Low3","Low1","Low1","High3","Low3","Low3","Low1","Low1","High3","Low3","Low3","High3","High3","Low1","Low3","Low3","Low1","Low1","Low3","Low3","Low4","Low4","Low3","Low2","Low2","High1","Low4","Low3","Low3","Low2","Low1","Low3","Low3","Wild","Low2","Low1","Low3","Low3","High3","Wild","Low1","Low1","Low3","Low3","Low1","Low1","Low3","Low3","High1","High1","High3","High3","Low3","Low1","Low1","High1","High1","High3","Low1","Low1","Low3","High3","High3","High1","High1","Low3","Low3","Low1","Low1","High3","Low3","High3","Low1","Low1","High1","High1","High3","High3","Low1","Low1","Low3","Low3","High3","High1","High3","Low3","Low3","Low1","Low1","Low3","Low3","High3","High3","High1","High1","Low3","Low3","Low1","Low1","Low3","Low3","High3","High3","Low3","High1","High1","Low1","Low3","High3","High3","Low3","High1","High1","Low3","Low3","Low1","Low3","Low3","Low1","High3","High3","Low3","Low1","Low1","High3","Low3","Low3","Low1","Low1","High3","Low3","Low3","High3","High3","Low1","Low3","Low3","Low1","Low1","Low3","Low3","Low4","Low4","Low3","Low2","Low2","High1","Low4","Low3","Low3","Low2","Low1","Low3","Low3","Wild","Low2","Low1","Low3","Low3","High3","Wild","Low1","Low1","Low3","Low3","Low1","Low1","Low3","Low3","High3","High3","Low3","Low1","Low1","Low4","Low4","Low3","Low3","High1","High1","Low1","Low1","High3","High3","Low3","Low1","Low1","High1","High1","High3","Low1","Low1","Low3","High3","High3","Low1","Low1","High1","High1","Low1","Low1","Low3","Low3","Low1","Low1","High3","Low3","High3","Low1","Low1","High1","High1","High3","High3","Low1","Low1","Low3","Low3","High3","High1","High3","Low3","Low3","Low1","Low1","Low3","Low3","High3","High3","High1","High1","Low3","Low3","Low1","Low1","Low3","Low3","High3","High3","Low3","High1","High1","Low1","Low3","High3","High3","Low3","High1","High1","Low3","Low3","Low1","Low3","Low3","Low1","High3","High3","Low3","Low1","Low1","High3","Low3","Low3","Low1","Low1","High3","Low3","Low3","High3","High3","Low1","Low3","Low3","Low1","Low1","Low3","Low3","High3","High3","Low3","Low1","Low1","Low3","Low3"];
            $this->reelStrips['FeatureReels6']=["High4","High4","Low4","Low4","Low2","Low2","Low2","High3","Low4","Low4","Low2","Wild","Low2","Low1","Low4","Low4","Low2","Low2","Low4","Low4","High2","High4","Low2","Low2","Low4","High4","High4","Low4","Low4","Low4","Low3","High4","High4","Low4","Low2","Low2","Low4","High4","High4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","High1","High2","Wild","Low2","Low4","Low4","High2","High2","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Low2","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High2","High4","High4","Low4","High2","High2","Low2","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High4","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Wild","Low2","Low1","Low4","Low4","Low2","Low2","Low4","Low4","High2","High4","Low4","Low4","Low2","Low2","Low4","High4","High4","Low4","Low4","Low4","Low4","Low3","High4","High4","Low4","Low2","Low2","Low4","High4","High4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","High2","Wild","Low2","Low4","Low4","High2","High2","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Low2","Low4","Low4","Low2","High2","High2","Low4","Low3","Low3","Low2","Low2","High2","High4","High4","Low4","High2","High2","Low2","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low3","Low3","Low2","Low2","Low4","Low4","High4","High4","Low4","Low4","Low2","Low2","Low2","Low2","Low4","Low4","Low2","Wild","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High2","High4","Low4","Low4","Low4","Low4","Low2","Low2","Low4","High4","High4","Low4","Low4","High4","High4","Low4","Low2","Low2","Low4","High4","High4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low4","Low4","Low2","Low2","Low4","Low4","Low4","Low4","High2","Wild","Low2","Low4","Low4","High2","High2","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Low2","Low4","Low4","Low3","Low3","Low2","High2","High2","Low4","Low2","Low2","High2","High4","High4","Low4","High2","High2","Low2","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High4","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Wild","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High2","High4","Low2","Low2","Low4","High4","High4","Low4","Low4","High4","High4","High4","High4","Low4","Low2","Low2","Low4","High4","High4","Low4","Low4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","High2","Wild","Low2","Low4","Low4","High2","High2","Low4","Low4","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Low2","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High2","High4","High4","Low4","Low2","Low2","Low2","High2","High2","Low2","Low1","Low1","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low2","Low4","Low4","High4","High4","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High4","High4","Low4","Low4","Low3","Low3","High4","High4","High4","High4","Low4","Low4","Low2","Low2","Low2","Low2","High3","Low4","Low4","Low2","Wild","Low2","Low1","Low1","Low4","Low4","Low2","Low2","Low4","Low4","High2","High4","Low2","Low2","Low4","Low3","Low3","Low1","Low1","High4","High4","Low1","Low1","Low3","Low3","Low4","Low4","Low4","Low4","Low3","Low1","Low1","High4","High4","Low4","Low2","Low2","Low1","Low1","High4","High4","Low2","Low2","Low3","Low3","Low4","Low4","High4","High4","Low4","High2","High2","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","High1","High2","Wild","Low2","Low4","Low4","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low1","Low1","Low4","Low4","Low2","Low2","High4","High2","Low2","Low1","Low1","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High2","High4","High4","Low4","High2","High2","Low2","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High4","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Wild","Low2","Low1","Low4","Low4","Low2","Low2","Low4","Low4","High2","High4","Low4","Low4","Low2","Low2","Low4","High4","High4","Low4","Low4","Low4","Low4","Low3","High4","High4","Low4","Low2","Low2","Low4","High4","High4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","High2","Wild","Low2","Low4","Low4","High2","High2","High4","High4","High4","High4","Low4","Low2","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Low2","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High2","High4","High4","Low4","High2","High2","Low2","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low1","Low1","Low4","Low4","High4","High4","Low2","High2","High2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High4","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Wild","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High2","High4","Low4","Low4","Low2","Low2","Low4","High4","High4","Low4","Low4","High4","High4","Low4","Low2","Low2","Low4","High4","High4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low4","Low4","Low2","Low2","Low4","Low4","High2","Wild","Low2","Low4","Low4","High2","High2","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Low2","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High2","High4","High4","Low4","High2","High2","High2","Low2","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High4","High4","Low4","Low4","Low2","Low2","Low2","Low2","Low4","Low4","Low2","Wild","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High2","High4","Low2","Low2","Low4","High4","High4","Low4","Low4","Low3","Low3","High4","High4","Low4","Low2","Low2","Low4","High4","High4","Low4","Low4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","High2","Wild","Low2","Low4","Low4","High2","High2","Low4","Low4","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Low2","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High2","High4","High4","Low4","Low2","Low2","Low2","High2","High2","Low2","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low2","Low4","Low4","High4","High4","Low4","Low4"];
            $this->reelStrips['FeatureReelsHighSync1']=["High3","Low4","Low4","High4","High3","Low3","Low3","High4","Low1","Low1","High1","High4","Low2","Low2","High2","Low3","High1","High1","Low3","Low1","Low4","Low4","High2","Low2","Low4","Low4","High1","Low2","Low2","Low2","High3","Low3","Low4","Low4","High2","High3","Low4","Low4","Low1","High2","Low2","Low2","Low3","Low4","High2","High2","Low4","Low1","Low3","Low3","High3","High2","Low1","Low1","High4","Low4","Low1","Low3","Low3","High3","High3","Low2","High2","Low1","Low1","High4","High2","Low4","Low4","High1","Low2","Low1","Low4","Low4","High3","High2","Low2","Low2","High4","High3","Low3","Low3","High4","Low4","Low1","High3","Low2","Low3","Low3","High3","High1","Low4","Low4","High2","Low1","Low3","Low3","High4","High3","Low4","Low4","High1","Low3","Low2","Low2","High1","High2","Low1","Low1","High3","High2","Low1","Low1","High1","Low2","Low2","High4","Low3","Low4","Low4","High2","Low1","High3","High4","Low3","Low4","Low2","High4","Low3","Low2","Low4","High1","Low1","Low2","High3","Low4","High1","Low2","Low1","High4","Low3","Low3","Low2","High4","High4","Low4","Low1","Low1","High4","Low3","Low4","High3","Low1","Low3","High4","Low4","Low3","Low2","High3","Low1","Low1","High1","Low3","Low4","High4","Low1","Low1","Low2","High4","Low4","Low3","Low2","High3","Low1","Low4","Low4","High2","Low3","Low2","Low2","High4","Low1","Low1","Low3","Low3","Low4","Low4","Low3","High2","Low4","High4","Low2","Low3","High2","Low2","Low3","Low3","High4","Low2","Low2","Low3","High3","Low4","Low3","Low3","High1","High2","Low4","High3","Low2","High1","Low4","High4","Low1","Low3","Low2","Low2","Low4","High4","Low3","High1","High3","Low4","Low4","High4","High3","Low3","Low3","High4","Low1","Low1","High1","High4","Low2","Low2","High2","Low3","High2","High1","Low3","Low1","Low4","Low4","High2","Low2","Low4","Low4","High1","Low2","Low2","Low2","High3","Low3","Low4","Low4","High2","High3","Low4","Low4","Low1","High2","Low2","Low2","Low3","Low4","High3","High2","Low4","Low1","Low3","Low3","High3","High2","Low1","Low1","High4","Low4","Low1","Low3","Low3","High4","High3","Low2","High2","Low1","Low1","High4","High2","Low4","Low4","High1","Low2","Low1","Low4","Low4","High3","High2","Low2","Low2","High4","High3","Low3","Low3","High4","Low4","Low1","High3","Low2","Low3","Low3","High3","High1","Low4","Low4","High2","Low1","Low3","Low3","High4","High3","Low4","Low4","High1","Low3","Low2","Low2","High1","High2","Low1","Low1","High3","High2","Low1","Low1","High1","Low2","Low2","High4","Low3","Low4","Low4","High2","Low1","High3","High4","Low3","Low4","Low2","High4","Low3","Low2","Low4","High1","Low1","Low2","High3","Low4","High1","Low2","Low1","High4","Low3","Low3","Low2","High3","High4","Low4","Low1","Low1","High4","Low3","Low4","High3","Low1","Low3","High4","Low4","Low3","Low2","High3","Low1","Low1","High1","Low3","Low4","High4","Low1","Low1","Low2","High4","Low4","Low3","Low2","High3","Low1","Low4","Low4","High2","Low3","Low2","Low2","High4","Low1","Low1","Low3","Low3","Low4","Low4","Low3","High2","Low4","High4","Low2","Low3","High2","Low2","Low3","Low3","High4","Low2","Low2","Low3","High3","Low4","Low3","Low3","High1","High2","Low4","High3","Low2","High1","Low4","High4","Low1","Low3","Low2","Low2","Low4","High4","Low3","High1"];
            $this->reelStrips['FeatureReelsHighSync2']=["High3","Low4","Low4","High4","High3","Low3","Low3","High4","Low1","Low1","High1","High4","Low2","Low2","High2","Low3","High1","High1","Low3","Low1","Low4","Low4","High2","Low2","Low4","Low4","High1","Low2","Low2","Low2","High3","Low3","Low4","Low4","High2","High3","Low4","Low4","Low1","High2","Low2","Low2","Low3","Low4","High2","High2","Low4","Low1","Low3","Low3","High3","High2","Low1","Low1","High4","Low4","Low1","Low3","Low3","High3","High3","Low2","High2","Low1","Low1","High4","High2","Low4","Low4","High1","Low2","Low1","Low4","Low4","High3","High2","Low2","Low2","High4","High3","Low3","Low3","High4","Low4","Low1","High3","Low2","Low3","Low3","High3","High1","Low4","Low4","High2","Low1","Low3","Low3","High4","High3","Low4","Low4","High1","Low3","Low2","Low2","High1","High2","Low1","Low1","High3","High2","Low1","Low1","High1","Low2","Low2","High4","Low3","Low4","Low4","High2","Low1","High3","High4","Low3","Low4","Low2","High4","Low3","Low2","Low4","High1","Low1","Low2","High3","Low4","High1","Low2","Low1","High4","Low3","Low3","Low2","High4","High4","Low4","Low1","Low1","High4","Low3","Low4","High3","Low1","Low3","High4","Low4","Low3","Low2","High3","Low1","Low1","High1","Low3","Low4","High4","Low1","Low1","Low2","High4","Low4","Low3","Low2","High3","Low1","Low4","Low4","High2","Low3","Low2","Low2","High4","Low1","Low1","Low3","Low3","Wild","Low4","Low4","Low3","High2","Low4","High4","Low2","Low3","High2","Low2","Low3","Low3","High4","Low2","Low2","Low3","High3","Low4","Low3","Low3","High1","High2","Low4","High3","Low2","High1","Low4","High4","Low1","Low3","Wild","Low2","Low2","Low4","High4","Low3","High1","High3","Low4","Low4","High4","High3","Low3","Low3","High4","Low1","Low1","High1","High4","Low2","Low2","High2","Low3","High2","High1","Low3","Low1","Low4","Low4","High2","Low2","Low4","Low4","High1","Low2","Low2","Low2","High3","Low3","Low4","Low4","High2","High3","Low4","Low4","Low1","High2","Low2","Low2","Low3","Low4","High3","High2","Low4","Low1","Low3","Low3","High3","High2","Low1","Low1","High4","Low4","Low1","Low3","Low3","High4","High3","Low2","High2","Low1","Low1","High4","High2","Low4","Low4","High1","Low2","Low1","Low4","Low4","High3","High2","Low2","Low2","High4","High3","Low3","Low3","High4","Low4","Low1","High3","Low2","Low3","Low3","High3","High1","Low4","Low4","High2","Low1","Low3","Low3","High4","High3","Low4","Low4","High1","Low3","Low2","Low2","High1","High2","Low1","Low1","High3","High2","Low1","Low1","High1","Low2","Low2","High4","Low3","Low4","Low4","High2","Low1","High3","High4","Low3","Low4","Low2","High4","Low3","Low2","Low4","High1","Low1","Low2","High3","Low4","High1","Low2","Low1","High4","Low3","Low3","Low2","High3","High4","Low4","Low1","Low1","High4","Low3","Low4","High3","Low1","Low3","High4","Low4","Low3","Low2","High3","Low1","Low1","High1","Low3","Low4","High4","Low1","Low1","Low2","High4","Low4","Low3","Low2","High3","Low1","Low4","Low4","High2","Low3","Low2","Low2","High4","Low1","Low1","Low3","Low3","Wild","Low4","Low4","Low3","High2","Low4","High4","Low2","Low3","High2","Low2","Low3","Low3","High4","Low2","Low2","Low3","High3","Low4","Low3","Low3","High1","High2","Low4","High3","Low2","High1","Low4","High4","Low1","Low3","Wild","Low2","Low2","Low4","High4","Low3","High1"];
            $this->reelStrips['FeatureReelsHighSync3']=["High3","Low4","Low4","High4","High3","Low3","Low3","High4","Low1","Low1","High1","High4","Low2","Low2","High2","Low3","High1","High1","Low3","Low1","Low4","Low4","High2","Low2","Low4","Low4","High1","Low2","Low2","Low2","High3","Low3","Low4","Low4","High2","High3","Low4","Low4","Low1","High2","Low2","Low2","Low3","Low4","High2","High2","Low4","Low1","Low3","Low3","High3","High2","Low1","Low1","High4","Low4","Low1","Low3","Low3","High3","High3","Low2","High2","Low1","Low1","High4","High2","Low4","Low4","High1","Low2","Low1","Low4","Low4","High3","High2","Low2","Low2","High4","High3","Low3","Low3","High4","Low4","Low1","High3","Low2","Low3","Low3","High3","High1","Low4","Low4","High2","Low1","Low3","Low3","High4","High3","Low4","Low4","High1","Low3","Low2","Low2","High1","High2","Low1","Low1","High3","High2","Low1","Low1","High1","Low2","Low2","High4","Low3","Low4","Low4","High2","Low1","High3","High4","Low3","Low4","Low2","High4","Low3","Low2","Low4","High1","Low1","Low2","High3","Low4","High1","Low2","Low1","High4","Low3","Low3","Low2","High4","High4","Low4","Low1","Low1","High4","Low3","Low4","High3","Low1","Low3","High4","Low4","Low3","Low2","High3","Low1","Low1","High1","Low3","Low4","High4","Low1","Low1","Low2","High4","Low4","Low3","Low2","High3","Low1","Low4","Low4","High2","Low3","Low2","Low2","High4","Low1","Low1","Low3","Low3","Wild","Low4","Low4","Low3","High2","Low4","High4","Low2","Low3","High2","Low2","Low3","Low3","High4","Low2","Low2","Low3","High3","Low4","Low3","Low3","High1","High2","Low4","High3","Low2","High1","Low4","High4","Low1","Low3","Wild","Low2","Low2","Low4","High4","Low3","High1","High3","Low4","Low4","High4","High3","Low3","Low3","High4","Low1","Low1","High1","High4","Low2","Low2","High2","Low3","High2","High1","Low3","Low1","Low4","Low4","High2","Low2","Low4","Low4","High1","Low2","Low2","Low2","High3","Low3","Low4","Low4","High2","High3","Low4","Low4","Low1","High2","Low2","Low2","Low3","Low4","High3","High2","Low4","Low1","Low3","Low3","High3","High2","Low1","Low1","High4","Low4","Low1","Low3","Low3","High4","High3","Low2","High2","Low1","Low1","High4","High2","Low4","Low4","High1","Low2","Low1","Low4","Low4","High3","High2","Low2","Low2","High4","High3","Low3","Low3","High4","Low4","Low1","High3","Low2","Low3","Low3","High3","High1","Low4","Low4","High2","Low1","Low3","Low3","High4","High3","Low4","Low4","High1","Low3","Low2","Low2","High1","High2","Low1","Low1","High3","High2","Low1","Low1","High1","Low2","Low2","High4","Low3","Low4","Low4","High2","Low1","High3","High4","Low3","Low4","Low2","High4","Low3","Low2","Low4","High1","Low1","Low2","High3","Low4","High1","Low2","Low1","High4","Low3","Low3","Low2","High3","High4","Low4","Low1","Low1","High4","Low3","Low4","High3","Low1","Low3","High4","Low4","Low3","Low2","High3","Low1","Low1","High1","Low3","Low4","High4","Low1","Low1","Low2","High4","Low4","Low3","Low2","High3","Low1","Low4","Low4","High2","Low3","Low2","Low2","High4","Low1","Low1","Low3","Low3","Wild","Low4","Low4","Low3","High2","Low4","High4","Low2","Low3","High2","Low2","Low3","Low3","High4","Low2","Low2","Low3","High3","Low4","Low3","Low3","High1","High2","Low4","High3","Low2","High1","Low4","High4","Low1","Low3","Wild","Low2","Low2","Low4","High4","Low3","High1"];
            $this->reelStrips['FeatureReelsHighSync4']=["High3","Low4","Low4","High4","High3","Low3","Low3","High4","Low1","Low1","High1","High4","Low2","Low2","High2","Low3","High1","High1","Low3","Low1","Low4","Low4","High2","Low2","Low4","Low4","High1","Low2","Low2","Low2","High3","Low3","Low4","Low4","High2","High3","Low4","Low4","Low1","High2","Low2","Low2","Low3","Low4","High2","High2","Low4","Low1","Low3","Low3","High3","High2","Low1","Low1","High4","Low4","Low1","Low3","Low3","High3","High3","Low2","High2","Low1","Low1","High4","High2","Low4","Low4","High1","Low2","Low1","Low4","Low4","High3","High2","Low2","Low2","High4","High3","Low3","Low3","High4","Low4","Low1","High3","Low2","Low3","Low3","High3","High1","Low4","Low4","High2","Low1","Low3","Low3","High4","High3","Low4","Low4","High1","Low3","Low2","Low2","High1","High2","Low1","Low1","High3","High2","Low1","Low1","High1","Low2","Low2","High4","Low3","Low4","Low4","High2","Low1","High3","High4","Low3","Low4","Low2","High4","Low3","Low2","Low4","High1","Low1","Low2","High3","Low4","High1","Low2","Low1","High4","Low3","Low3","Low2","High4","High4","Low4","Low1","Low1","High4","Low3","Low4","High3","Low1","Low3","High4","Low4","Low3","Low2","High3","Low1","Low1","High1","Low3","Low4","High4","Low1","Low1","Low2","High4","Low4","Low3","Low2","High3","Low1","Low4","Low4","High2","Low3","Low2","Low2","High4","Low1","Low1","Low3","Low3","Wild","Low4","Low4","Low3","High2","Low4","High4","Low2","Low3","High2","Low2","Low3","Low3","High4","Low2","Low2","Low3","High3","Low4","Low3","Low3","High1","High2","Low4","High3","Low2","High1","Low4","High4","Low1","Low3","Wild","Low2","Low2","Low4","High4","Low3","High1","High3","Low4","Low4","High4","High3","Low3","Low3","High4","Low1","Low1","High1","High4","Low2","Low2","High2","Low3","High2","High1","Low3","Low1","Low4","Low4","High2","Low2","Low4","Low4","High1","Low2","Low2","Low2","High3","Low3","Low4","Low4","High2","High3","Low4","Low4","Low1","High2","Low2","Low2","Low3","Low4","High3","High2","Low4","Low1","Low3","Low3","High3","High2","Low1","Low1","High4","Low4","Low1","Low3","Low3","High4","High3","Low2","High2","Low1","Low1","High4","High2","Low4","Low4","High1","Low2","Low1","Low4","Low4","High3","High2","Low2","Low2","High4","High3","Low3","Low3","High4","Low4","Low1","High3","Low2","Low3","Low3","High3","High1","Low4","Low4","High2","Low1","Low3","Low3","High4","High3","Low4","Low4","High1","Low3","Low2","Low2","High1","High2","Low1","Low1","High3","High2","Low1","Low1","High1","Low2","Low2","High4","Low3","Low4","Low4","High2","Low1","High3","High4","Low3","Low4","Low2","High4","Low3","Low2","Low4","High1","Low1","Low2","High3","Low4","High1","Low2","Low1","High4","Low3","Low3","Low2","High3","High4","Low4","Low1","Low1","High4","Low3","Low4","High3","Low1","Low3","High4","Low4","Low3","Low2","High3","Low1","Low1","High1","Low3","Low4","High4","Low1","Low1","Low2","High4","Low4","Low3","Low2","High3","Low1","Low4","Low4","High2","Low3","Low2","Low2","High4","Low1","Low1","Low3","Low3","Wild","Low4","Low4","Low3","High2","Low4","High4","Low2","Low3","High2","Low2","Low3","Low3","High4","Low2","Low2","Low3","High3","Low4","Low3","Low3","High1","High2","Low4","High3","Low2","High1","Low4","High4","Low1","Low3","Wild","Low2","Low2","Low4","High4","Low3","High1"];
            $this->reelStrips['FeatureReelsHighSync5']=["High3","Low4","Low4","High4","High3","Low3","Low3","High4","Low1","Low1","High1","High4","Low2","Low2","High2","Low3","High1","High1","Low3","Low1","Low4","Low4","High2","Low2","Low4","Low4","High1","Low2","Low2","Low2","High3","Low3","Low4","Low4","High2","High3","Low4","Low4","Low1","High2","Low2","Low2","Low3","Low4","High2","High2","Low4","Low1","Low3","Low3","High3","High2","Low1","Low1","High4","Low4","Low1","Low3","Low3","High3","High3","Low2","High2","Low1","Low1","High4","High2","Low4","Low4","High1","Low2","Low1","Low4","Low4","High3","High2","Low2","Low2","High4","High3","Low3","Low3","High4","Low4","Low1","High3","Low2","Low3","Low3","High3","High1","Low4","Low4","High2","Low1","Low3","Low3","High4","High3","Low4","Low4","High1","Low3","Low2","Low2","High1","High2","Low1","Low1","High3","High2","Low1","Low1","High1","Low2","Low2","High4","Low3","Low4","Low4","High2","Low1","High3","High4","Low3","Low4","Low2","High4","Low3","Low2","Low4","High1","Low1","Low2","High3","Low4","High1","Low2","Low1","High4","Low3","Low3","Low2","High4","High4","Low4","Low1","Low1","High4","Low3","Low4","High3","Low1","Low3","High4","Low4","Low3","Low2","High3","Low1","Low1","High1","Low3","Low4","High4","Low1","Low1","Low2","High4","Low4","Low3","Low2","High3","Low1","Low4","Low4","High2","Low3","Low2","Low2","High4","Low1","Low1","Low3","Low3","Wild","Low4","Low4","Low3","High2","Low4","High4","Low2","Low3","High2","Low2","Low3","Low3","High4","Low2","Low2","Low3","High3","Low4","Low3","Low3","High1","High2","Low4","High3","Low2","High1","Low4","High4","Low1","Low3","Wild","Low2","Low2","Low4","High4","Low3","High1","High3","Low4","Low4","High4","High3","Low3","Low3","High4","Low1","Low1","High1","High4","Low2","Low2","High2","Low3","High2","High1","Low3","Low1","Low4","Low4","High2","Low2","Low4","Low4","High1","Low2","Low2","Low2","High3","Low3","Low4","Low4","High2","High3","Low4","Low4","Low1","High2","Low2","Low2","Low3","Low4","High3","High2","Low4","Low1","Low3","Low3","High3","High2","Low1","Low1","High4","Low4","Low1","Low3","Low3","High4","High3","Low2","High2","Low1","Low1","High4","High2","Low4","Low4","High1","Low2","Low1","Low4","Low4","High3","High2","Low2","Low2","High4","High3","Low3","Low3","High4","Low4","Low1","High3","Low2","Low3","Low3","High3","High1","Low4","Low4","High2","Low1","Low3","Low3","High4","High3","Low4","Low4","High1","Low3","Low2","Low2","High1","High2","Low1","Low1","High3","High2","Low1","Low1","High1","Low2","Low2","High4","Low3","Low4","Low4","High2","Low1","High3","High4","Low3","Low4","Low2","High4","Low3","Low2","Low4","High1","Low1","Low2","High3","Low4","High1","Low2","Low1","High4","Low3","Low3","Low2","High3","High4","Low4","Low1","Low1","High4","Low3","Low4","High3","Low1","Low3","High4","Low4","Low3","Low2","High3","Low1","Low1","High1","Low3","Low4","High4","Low1","Low1","Low2","High4","Low4","Low3","Low2","High3","Low1","Low4","Low4","High2","Low3","Low2","Low2","High4","Low1","Low1","Low3","Low3","Wild","Low4","Low4","Low3","High2","Low4","High4","Low2","Low3","High2","Low2","Low3","Low3","High4","Low2","Low2","Low3","High3","Low4","Low3","Low3","High1","High2","Low4","High3","Low2","High1","Low4","High4","Low1","Low3","Wild","Low2","Low2","Low4","High4","Low3","High1"];
            $this->reelStrips['FeatureReelsHighSync6']=["High3","Low4","Low4","High4","High3","Low3","Low3","High4","Low1","Low1","High1","High4","Low2","Low2","High2","Low3","High1","High1","Low3","Low1","Low4","Low4","High2","Low2","Low4","Low4","High1","Low2","Low2","Low2","High3","Low3","Low4","Low4","High2","High3","Low4","Low4","Low1","High2","Low2","Low2","Low3","Low4","High2","High2","Low4","Low1","Low3","Low3","High3","High2","Low1","Low1","High4","Low4","Low1","Low3","Low3","High3","High3","Low2","High2","Low1","Low1","High4","High2","Low4","Low4","High1","Low2","Low1","Low4","Low4","High3","High2","Low2","Low2","High4","High3","Low3","Low3","High4","Low4","Low1","High3","Low2","Low3","Low3","High3","High1","Low4","Low4","High2","Low1","Low3","Low3","High4","High3","Low4","Low4","High1","Low3","Low2","Low2","High1","High2","Low1","Low1","High3","High2","Low1","Low1","High1","Low2","Low2","High4","Low3","Low4","Low4","High2","Low1","High3","High4","Low3","Low4","Low2","High4","Low3","Low2","Low4","High1","Low1","Low2","High3","Low4","High1","Low2","Low1","High4","Low3","Low3","Low2","High4","High4","Low4","Low1","Low1","High4","Low3","Low4","High3","Low1","Low3","High4","Low4","Low3","Low2","High3","Low1","Low1","High1","Low3","Low4","High4","Low1","Low1","Low2","High4","Low4","Low3","Low2","High3","Low1","Low4","Low4","High2","Low3","Low2","Low2","High4","Low1","Low1","Low3","Low3","Wild","Low4","Low4","Low3","High2","Low4","High4","Low2","Low3","High2","Low2","Low3","Low3","High4","Low2","Low2","Low3","High3","Low4","Low3","Low3","High1","High2","Low4","High3","Low2","High1","Low4","High4","Low1","Low3","Wild","Low2","Low2","Low4","High4","Low3","High1","High3","Low4","Low4","High4","High3","Low3","Low3","High4","Low1","Low1","High1","High4","Low2","Low2","High2","Low3","High2","High1","Low3","Low1","Low4","Low4","High2","Low2","Low4","Low4","High1","Low2","Low2","Low2","High3","Low3","Low4","Low4","High2","High3","Low4","Low4","Low1","High2","Low2","Low2","Low3","Low4","High3","High2","Low4","Low1","Low3","Low3","High3","High2","Low1","Low1","High4","Low4","Low1","Low3","Low3","High4","High3","Low2","High2","Low1","Low1","High4","High2","Low4","Low4","High1","Low2","Low1","Low4","Low4","High3","High2","Low2","Low2","High4","High3","Low3","Low3","High4","Low4","Low1","High3","Low2","Low3","Low3","High3","High1","Low4","Low4","High2","Low1","Low3","Low3","High4","High3","Low4","Low4","High1","Low3","Low2","Low2","High1","High2","Low1","Low1","High3","High2","Low1","Low1","High1","Low2","Low2","High4","Low3","Low4","Low4","High2","Low1","High3","High4","Low3","Low4","Low2","High4","Low3","Low2","Low4","High1","Low1","Low2","High3","Low4","High1","Low2","Low1","High4","Low3","Low3","Low2","High3","High4","Low4","Low1","Low1","High4","Low3","Low4","High3","Low1","Low3","High4","Low4","Low3","Low2","High3","Low1","Low1","High1","Low3","Low4","High4","Low1","Low1","Low2","High4","Low4","Low3","Low2","High3","Low1","Low4","Low4","High2","Low3","Low2","Low2","High4","Low1","Low1","Low3","Low3","Wild","Low4","Low4","Low3","High2","Low4","High4","Low2","Low3","High2","Low2","Low3","Low3","High4","Low2","Low2","Low3","High3","Low4","Low3","Low3","High1","High2","Low4","High3","Low2","High1","Low4","High4","Low1","Low3","Wild","Low2","Low2","Low4","High4","Low3","High1"];
            $this->reelStrips['Reels1']=["High2","Low4","Low4","High4","Low4","Low3","Low2","Low2","High1","Freespin","Low4","Low3","Low3","Low2","Low1","High4","Low3","Low3","Low3","Low2","Low1","Low3","Low3","High3","Low1","Low1","Low3","Low3","Low3","Freespin","Low1","Low1","High1","High1","High3","High3","Low3","Low1","Low1","Low1","High1","High1","High2","High3","Low1","Low1","Low1","Freespin","Low3","High3","High3","High1","High1","Low3","Low3","Low1","Low1","Low1","High3","Low3","High3","Freespin","Low1","Low1","High1","High1","High3","High3","Low1","Low1","Low3","Low3","Low3","High3","High1","High3","Low3","Low3","Low1","Low1","High3","High3","High1","High1","Low3","Low3","Low1","Low3","Low3","High3","High3","Low3","High1","High1","Low1","Low3","High3","High3","Low3","High1","High1","Low3","Low3","Low1","Low3","Low3","Low1","High3","High3","Low3","Low1","Low1","High3","Low3","Low3","Low1","Low1","High3","Low3","Low3","High3","High3","Low1","Low3","Low1","Low1","Low3","Low3","High2","Low4","Low4","High4","Low4","Low3","Low2","Low2","High1","Freespin","Low4","Low3","Low2","Low1","High4","Low3","Low3","Low3","Low2","Low1","Low3","Low3","High3","Low1","Low1","Low1","Low3","Low3","Low3","Low1","Low1","High1","High1","Low3","Low3","High3","Low1","Low1","Low1","High1","High1","High2","Low1","Low1","Low1","High1","Low3","Low3","High3","High3","High1","High1","Low3","Low3","Low1","Low1","Low1","High1","High1","High3","Low3","High3","Low1","Low1","High1","High3","High3","Low1","Low3","Low3","Low3","High3","High1","Low3","Low3","High3","Low1","Low1","High3","High3","Low3","Low3","Low1","Low3","Low3","High3","High3","Low3","High1","High1","Low1","Low1","Low3","High3","High3","Low3","High1","High1","Low3","Low3","High3","High3","Low1","Low3","Low3","Low1","High3","High3","Low3","Low1","Low1","High3","Low3","Low3","Low1","Low1","High3","Low3","Low3","High3","High3","Low1","Low3","Low1","Low1","Low3","Low3","Low1","Low3"];
            $this->reelStrips['Reels2']=["Low1","Low1","Low2","Low2","Low2","Freespin","High3","High4","High4","Low3","Low1","Low4","Low4","Low2","Wild","High1","Low3","Low1","Low4","Low4","Low2","Low2","Low4","Low4","High2","Low3","Low3","Low1","High4","High4","High4","Low4","Low4","Low4","Low3","High4","High4","Low4","Low2","Low2","Low4","Wild","High4","High4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","Low4","High1","High2","Low2","Low2","Low4","Low4","Low2","Low2","Freespin","High2","High2","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Low2","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High4","High4","Low4","High2","High2","High2","Low2","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","Low2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low2","Low4","Low4","Low2","Low2","Low2","Freespin","High3","High4","High4","Low4","Low4","Low4","Low2","Low4","Low4","Low2","Low2","High2","High4","High4","High4","Low4","Low4","Low4","High4","High4","Freespin","Low4","Low2","Low2","Low4","Low4","Low2","Low2","High4","High4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","Low4","High2","Low2","Low4","Low4","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Wild","Low2","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High2","High2","High4","High4","Low4","Low2","Low2","High2","High2","High2","Low2","Low4","Low4","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","High2","High2","Low4","Low2","Low2","High4","Wild","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low4","Low4","Low2","Low2"];
            $this->reelStrips['Reels3']=["High2","Low4","Low4","High4","Wild","Low4","Low3","Low2","Low2","High1","Freespin","Low4","Low3","Low3","Low2","Low1","High4","Low3","Low3","Low3","Low2","Wild","Low1","Low3","Low3","High3","Low1","Low1","Low3","Low3","Low3","Freespin","Low1","Low1","High1","High1","High3","High3","Low3","Low1","Low1","Low1","High1","High1","High2","High3","Low1","Low1","Low1","Freespin","Low3","High3","High3","High1","High1","Low3","Low3","Low1","Low1","Low1","High3","Low3","High3","Freespin","Low1","Low1","High1","High1","High3","High3","Low1","Low1","Low3","Low3","Low3","High3","High1","Wild","High3","Low3","Low3","Low1","Low1","High3","High3","High1","High1","Low3","Low3","Low1","Low3","Low3","High3","High3","Low3","High1","High1","Low1","Low3","High3","High3","Low3","High1","High1","Low3","Low3","Low1","Low3","Low3","Low1","High3","High3","Low3","Low1","Low1","High3","Low3","Low3","Low1","Low1","High3","Low3","Low3","High3","High3","Low1","Low3","Low1","Low1","Low3","Low3","High2","Low4","Low4","High4","Low4","Low3","Low2","Low2","High1","Freespin","Low4","Low3","Wild","Low2","Low1","High4","Low3","Low3","Low3","Low2","Low1","Low3","Low3","Wild","High3","Low1","Low1","Low1","Low3","Low3","Low3","Low1","Low1","High1","High1","Low3","Low3","High3","Low1","Low1","Low1","High1","High1","High2","Low1","Low1","Low1","High1","Low3","Low3","High3","High3","High1","High1","Low3","Low3","Low1","Low1","Low1","High1","High1","High3","Low3","High3","Low1","Low1","High1","High3","High3","Low1","Low3","Low3","Low3","High3","High1","Low3","Low3","High3","Low1","Low1","High3","High3","Low3","Low3","Low1","Low3","Low3","High3","High3","Low3","High1","High1","Low1","Low1","Low3","High3","High3","Low3","High1","High1","Low3","Low3","High3","High3","Low1","Low3","Low3","Low1","High3","High3","Low3","Low1","Low1","High3","Low3","Low3","Low1","Low1","High3","Low3","Low3","High3","High3","Low1","Low3","Low1","Low1","Low3","Low3","Low1","Low3"];
            $this->reelStrips['Reels4']=["Low1","Low1","Low2","Low2","Low2","Freespin","High3","High4","High4","Low3","Low1","Low4","Low4","Low2","Wild","High1","Low3","Low1","Low4","Low4","Low2","Low2","Low4","Low4","High2","Low3","Low3","Low1","High4","High4","High4","Low4","Low4","Low4","Low3","High4","High4","Low4","Low2","Low2","Low4","Wild","High4","High4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","Low4","High1","High2","Low2","Low2","Low4","Low4","Low2","Low2","Freespin","High2","High2","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Low2","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High4","High4","Low4","High2","High2","High2","Low2","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","Low2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low2","Low4","Low4","Low2","Low2","Low2","Freespin","High3","High4","High4","Low4","Low4","Low4","Low2","Low4","Low4","Low2","Low2","High2","High4","High4","High4","Low4","Low4","Low4","High4","High4","Freespin","Low4","Low2","Low2","Low4","Low4","Low2","Low2","High4","High4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","Low4","High2","Low2","Low4","Low4","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Wild","Low2","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High2","High2","High4","High4","Low4","Low2","Low2","High2","High2","High2","Low2","Low4","Low4","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","High2","High2","Low4","Low2","Low2","High4","Wild","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low4","Low4","Low2","Low2"];
            $this->reelStrips['Reels5']=["High2","Low4","Low4","High4","Wild","Low4","Low3","Low2","Low2","High1","Freespin","Low4","Low3","Low3","Low2","Low1","High4","Low3","Low3","Low3","Low2","Wild","Low1","Low3","Low3","High3","Low1","Low1","Low3","Low3","Low3","Freespin","Low1","Low1","High1","High1","High3","High3","Low3","Low1","Low1","Low1","High1","High1","High2","High3","Low1","Low1","Low1","Freespin","Low3","High3","High3","High1","High1","Low3","Low3","Low1","Low1","Low1","High3","Low3","High3","Freespin","Low1","Low1","High1","High1","High3","High3","Low1","Low1","Low3","Low3","Low3","High3","High1","Wild","High3","Low3","Low3","Low1","Low1","High3","High3","High1","High1","Low3","Low3","Low1","Low3","Low3","High3","High3","Low3","High1","High1","Low1","Low3","High3","High3","Low3","High1","High1","Low3","Low3","Low1","Low3","Low3","Low1","High3","High3","Low3","Low1","Low1","High3","Low3","Low3","Low1","Low1","High3","Low3","Low3","High3","High3","Low1","Low3","Low1","Low1","Low3","Low3","High2","Low4","Low4","High4","Low4","Low3","Low2","Low2","High1","Freespin","Low4","Low3","Wild","Low2","Low1","High4","Low3","Low3","Low3","Low2","Low1","Low3","Low3","Wild","High3","Low1","Low1","Low1","Low3","Low3","Low3","Low1","Low1","High1","High1","Low3","Low3","High3","Low1","Low1","Low1","High1","High1","High2","Low1","Low1","Low1","High1","Low3","Low3","High3","High3","High1","High1","Low3","Low3","Low1","Low1","Low1","High1","High1","High3","Low3","High3","Low1","Low1","High1","High3","High3","Low1","Low3","Low3","Low3","High3","High1","Low3","Low3","High3","Low1","Low1","High3","High3","Low3","Low3","Low1","Low3","Low3","High3","High3","Low3","High1","High1","Low1","Low1","Low3","High3","High3","Low3","High1","High1","Low3","Low3","High3","High3","Low1","Low3","Low3","Low1","High3","High3","Low3","Low1","Low1","High3","Low3","Low3","Low1","Low1","High3","Low3","Low3","High3","High3","Low1","Low3","Low1","Low1","Low3","Low3","Low1","Low3"];
            $this->reelStrips['Reels6']=["Low1","Low1","Low2","Low2","Low2","Freespin","High3","High4","High4","Low3","Low1","Low4","Low4","Low2","Wild","High1","Low3","Low1","Low4","Low4","Low2","Low2","Low4","Low4","High2","Low3","Low3","Low1","High4","High4","High4","Low4","Low4","Low4","Low3","High4","High4","Low4","Low2","Low2","Low4","Wild","High4","High4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","Low4","High1","High2","Low2","Low2","Low4","Low4","Low2","Low2","Freespin","High2","High2","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Low2","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High4","High4","Low4","High2","High2","High2","Low2","Low4","Low4","High4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","Low2","High2","High2","Low4","Low2","Low2","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low2","Low4","Low4","Low2","Low2","Low2","Freespin","High3","High4","High4","Low4","Low4","Low4","Low2","Low4","Low4","Low2","Low2","High2","High4","High4","High4","Low4","Low4","Low4","High4","High4","Freespin","Low4","Low2","Low2","Low4","Low4","Low2","Low2","High4","High4","Low2","Low2","Low4","Low4","High4","High4","Low4","High2","High2","Low4","High4","High4","Low2","Low2","Low4","Low4","Low4","High2","Low2","Low4","Low4","High4","High4","High4","Low4","Low2","High4","Low4","High2","High2","Low4","Low4","Low2","Low2","High4","High2","Wild","Low2","Low4","Low4","Low2","High2","High2","Low4","Low2","Low2","High2","High2","High4","High4","Low4","Low2","Low2","High2","High2","High2","Low2","Low4","Low4","High4","Low4","Low4","Low2","Low2","Low2","Low4","Low4","High4","High4","Low2","High2","High2","Low4","Low2","Low2","High4","Wild","Low4","Low4","Low2","Low2","Low2","Low4","Low4","Low2","Low4","Low4","Low2","Low2"];

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
            $this->SymbolGame = ["High1","High2","High3","High4","Low1","Low2","Low3","Low4"];
            $this->HighSymbol = ["High1","High2","High3","High4"];
            
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
        
        public function GetSpinSettings($garantType = 'bet', $bet, $lines)
        {
            $this->AllBet = $bet * $lines;
            return $this->SpinSettings($garantType, $this->AllBet);
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
            //     $this->isBonusStart = true;
            //     $garantType = 'bonus';
            //     $winLimit = $this->GetBank($garantType);
            //     if($winLimit >= $this->AllBet * 20)
            //     {
            //         $return = ['bonus', $winLimit];
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
        public function GetRandomScatterPos($rp)
        {
            $rpResult = [];
            for( $i = 0; $i < count($rp); $i++ ) 
            {
                if( $rp[$i] == 'Freespin' ) 
                {
                    if( isset($rp[$i + 1]) && isset($rp[$i - 1]) ) 
                    {
                        array_push($rpResult, $i);
                    }
                    if( isset($rp[$i - 1]) && isset($rp[$i - 2]) ) 
                    {
                        array_push($rpResult, $i - 1);
                    }
                    if( isset($rp[$i + 1]) && isset($rp[$i + 2]) ) 
                    {
                        array_push($rpResult, $i + 1);
                    }
                }
            }
            shuffle($rpResult);
            if( !isset($rpResult[0]) ) 
            {
                $rpResult[0] = rand(2, count($rp) - 3);
            }
            return $rpResult[0];
        }
        public function GetGambleSettings()
        {
            $spinWin = rand(1, $this->WinGamble);
            return $spinWin;
        }
        public function GetReelStrips($winType, $reelName)
        {
            $arrReels = [
                $reelName.'1', 
                $reelName.'2', 
                $reelName.'3', 
                $reelName.'4', 
                $reelName.'5',                 
                $reelName.'6'
            ];
            
            $prs = [];
            if($winType != 'bonus')
            {
                if($winType == 'win')
                {
                    $sym = $this->SymbolGame[rand(0, 7)];
                    foreach($arrReels as $index => $reelStrip ) 
                    {
                        if( is_array($this->reelStrips) && count($this->reelStrips[$reelStrip]) > 0 ) 
                        {
                            $prs[$index + 1] = $this->GetSymbolPos($this->reelStrips[$reelStrip], $sym, 4);
                        }
                    }
                }
                else
                {
                    foreach( $arrReels as $index => $reelStrip ) 
                    {
                        if( is_array($this->reelStrips) && count($this->reelStrips[$reelStrip]) > 0 ) 
                        {
                            $prs[$index + 1] = mt_rand(1, count($this->reelStrips[$reelStrip]) - 3);
                        }
                    }
                }                
            }
            else
            {
                $reelsId = [];
                foreach($arrReels as $index => $reelStrip ) 
                {
                    if( is_array($this->reelStrips[$reelStrip]) && count($this->reelStrips[$reelStrip]) > 0 ) 
                    {
                        $prs[$index + 1] = $this->GetRandomScatterPos($this->reelStrips[$reelStrip]);
                        $reelsId[] = $index + 1;
                    }
                }

                $random = rand(0, 100);
                $scattersCnt = 3;
                if($random < 80)
                    $scattersCnt = 3;
                else if($random < 85)
                    $scattersCnt = 4;
                else if($random < 90)
                    $scattersCnt = 5;
                else
                    $scattersCnt = 6;
                
                shuffle($reelsId);
                for( $i = 0; $i < count($reelsId); $i++ ) 
                {
                    if( $i < $scattersCnt ) 
                    {
                        $prs[$reelsId[$i]] = $this->GetRandomScatterPos($this->reelStrips[$reelName.$reelsId[$i]]);
                    }
                    else
                    {
                        $prs[$reelsId[$i]] = rand(1, count($this->reelStrips[$reelName.$reelsId[$i]]) - 4);
                    }
                }
            }
            
            $reel = [
                'rp' => []
            ];
            
            foreach( $prs as $index => $value ) 
            {
                $key = $this->reelStrips[$reelName.$index];
                $key[-1] = $key[count($key) - 1];
                $reel['reel' . $index][0] = $key[$value - 1];
                $reel['reel' . $index][1] = $key[$value];
                $reel['reel' . $index][2] = $key[$value + 1];
                $reel['reel' . $index][3] = $key[$value + 2];
                $reel['rp'][] = $value;
            }
            return $reel;
        }

        public function GetBets()
        {
            return ["1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "15", "20"];
        }

        public function GetPaylines()
        {
            $linesId = [[1,1,1,1,1],[0,0,0,0,0],[2,2,2,2,2],[0,1,2,1,0],[2,1,0,1,2],[1,1,1,1,1],[0,0,0,0,0],[2,2,2,2,2],[0,1,2,1,0],[2,1,0,1,2]];            
            return $linesId;
        }

        public function GetReelSymbol($reels)
        {
            return [
                [ $reels['reel1'][0], $reels['reel1'][1], $reels['reel1'][2], $reels['reel1'][3]],
                [ $reels['reel2'][0], $reels['reel2'][1], $reels['reel2'][2], $reels['reel2'][3]],
                [ $reels['reel3'][0], $reels['reel3'][1], $reels['reel3'][2], $reels['reel3'][3]],
                [ $reels['reel4'][0], $reels['reel4'][1], $reels['reel4'][2], $reels['reel4'][3]],
                [ $reels['reel5'][0], $reels['reel5'][1], $reels['reel5'][2], $reels['reel5'][3]],
                [ $reels['reel6'][0], $reels['reel6'][1], $reels['reel6'][2], $reels['reel6'][3]]
            ];
        }

        public function GetNoWinSpin($slotEvent)
        {
            $isWin = true;            
            
            $symbolGame = $this->SymbolGame;
            $scatter = "Freespin";                     
            while($isWin)
            {
                if($slotEvent == 'bet' || $slotEvent == '')
                    $reelName = $this->reelNames[rand(0, count($this->reelNames) - 1)];
                else
                    $reelName = $this->featureReelNames[rand(0, count($this->featureReelNames) - 1)];
                $reels = $this->GetReelStrips('none', $reelName);
                $win = 0;
                
                for( $k = 0; $k < count($symbolGame); $k++ ) 
                {
                    $csym = $symbolGame[$k];
                    $wild = "Wild";                    
                    if( $csym == $scatter || !isset($this->Paytable[$csym]))
                    {

                    }
                    else
                    {
                        if( (in_array($csym,$reels['reel1']) || in_array($wild, $reels['reel1'])) &&
                            (in_array($csym,$reels['reel2']) || in_array($wild, $reels['reel2']))  && in_array($csym, $this->HighSymbol))
                        {
                            //2 symbols match
                            $win += $this->Paytable[$csym][2];
                        }
                        if( (in_array($csym,$reels['reel1']) || in_array($wild, $reels['reel1'])) &&
                            (in_array($csym,$reels['reel2']) || in_array($wild, $reels['reel2'])) &&
                            (in_array($csym,$reels['reel3']) || in_array($wild, $reels['reel3'])) ) 
                        {
                            //3 symbols match
                            $win += $this->Paytable[$csym][3];                            
                        }
                        if( (in_array($csym,$reels['reel1']) || in_array($wild, $reels['reel1'])) &&
                            (in_array($csym,$reels['reel2']) || in_array($wild, $reels['reel2'])) &&
                            (in_array($csym,$reels['reel3']) || in_array($wild, $reels['reel3'])) &&
                            (in_array($csym,$reels['reel4']) || in_array($wild, $reels['reel4'])) )
                        {
                            //4 symbols match
                            $win += $this->Paytable[$csym][4];                            
                        }
                        if( (in_array($csym,$reels['reel1']) || in_array($wild, $reels['reel1'])) &&
                        (in_array($csym,$reels['reel2']) || in_array($wild, $reels['reel2'])) &&
                        (in_array($csym,$reels['reel3']) || in_array($wild, $reels['reel3'])) &&
                        (in_array($csym,$reels['reel4']) || in_array($wild, $reels['reel4'])) &&
                        (in_array($csym,$reels['reel5']) || in_array($wild, $reels['reel5']))) 
                        {
                            //5 symbols match
                            $win += $this->Paytable[$csym][5];
                        }
                        if( (in_array($csym,$reels['reel1']) || in_array($wild, $reels['reel1'])) &&
                        (in_array($csym,$reels['reel2']) || in_array($wild, $reels['reel2'])) &&
                        (in_array($csym,$reels['reel3']) || in_array($wild, $reels['reel3'])) &&
                        (in_array($csym,$reels['reel4']) || in_array($wild, $reels['reel4'])) &&
                        (in_array($csym,$reels['reel5']) || in_array($wild, $reels['reel5'])) &&
                        (in_array($csym,$reels['reel6']) || in_array($wild, $reels['reel6']))) 
                        {
                            //6 symbols match
                            $win += $this->Paytable[$csym][6];
                        }
                        
                    }
                }

                if($win == 0)
                {
                    //calc scatter syms
                    $scatterC = 0;                    
                    for( $r = 1; $r <= 5; $r++ ) 
                    {
                        for( $p = 0; $p <= 3; $p++ ) 
                        {
                            if( $reels['reel' . $r][$p] == $scatter )
                            {
                                $scatterC++;
                            }                            
                        }
                    }    
                    if($scatterC < 3)
                        $isWin = false;
                }
            }
            return $reels;
        }         
    }

}
