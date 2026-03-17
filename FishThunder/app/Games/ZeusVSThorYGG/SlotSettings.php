<?php 
namespace VanguardLTE\Games\ZeusVSThorYGG
{

    use VanguardLTE\Lib\BasicSlotSettings;
    use VanguardLTE\Lib\JackpotHandler;

    class SlotSettings extends BasicSlotSettings
    {
        public $winRate = 60;
        public $playerId = null;
        public $splitScreen = null;
        public $reelStrips = null;        
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
        public $highSymbol = null;
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

            $this->highSymbol = ['RW', 'BW', 'RG', 'BG', 'RD', 'BD'];
            $this->Paytable['RS'] = [0, 0, 0, 0, 0, 0];
            $this->Paytable['BS'] = [0, 0, 0, 0, 0, 0];
            $this->Paytable['RW'] = [0, 0, 3, 30, 70, 100];
            $this->Paytable['BW'] = [0, 0, 3, 30, 70, 100];
            $this->Paytable['RG'] = [0, 0, 2, 15, 30, 70];
            $this->Paytable['BG'] = [0, 0, 2, 15, 30, 70];
            $this->Paytable['RD'] = [0, 0, 2, 10, 20, 50];
            $this->Paytable['BD'] = [0, 0, 2, 10, 20, 50];
            $this->Paytable['RB'] = [0, 0, 0, 8, 15, 40];
            $this->Paytable['BB'] = [0, 0, 0, 8, 15, 40];
            $this->Paytable['RH'] = [0, 0, 0, 8, 15, 25];
            $this->Paytable['BH'] = [0, 0, 0, 8, 15, 25];
            $this->Paytable['RA'] = [0, 0, 0, 4, 8, 15];
            $this->Paytable['BA'] = [0, 0, 0, 4, 8, 15];
            $this->Paytable['RK'] = [0, 0, 0, 2, 5, 12];
            $this->Paytable['BK'] = [0, 0, 0, 2, 5, 12];
            $this->Paytable['RQ'] = [0, 0, 0, 2, 5, 12];
            $this->Paytable['BQ'] = [0, 0, 0, 2, 5, 12];
            
            $this->reelStrips = [];
            $this->reelStrips['Base9Reels1']=["BB","BG","RD","BQ","BB","RK","BQ","BA","RH","BA","RK","RD","RH","BG"];
            $this->reelStrips['Base9Reels2']=["BD","RQ","BH","RA","RG","RQ","BD","RB","BK","RB","RG","RA","BK","BH"];
            $this->reelStrips['Base9Reels3']=["RK","RB","BK","RQ","BA","BD","BK","RA","RD","RH","RQ","BH","RK","BB","BG","RG","BQ","BQ","RW","RW","RW","RW"];
            $this->reelStrips['Base9Reels4']=["RD","RK","RD","BQ","BA","RH","RH","BQ","RK","BA","BG","BG","BB","BQ","BA","RH","RK","BB","RW","RW","RW","RW"];
            $this->reelStrips['Base9Reels5']=["RA","BD","RA","BH","BD","RQ","BH","RB","BK","RA","BK","BK","RB","RG","RG","RQ","BH","RQ","RW","RW","RW","RW"];
            $this->reelStrips['Fs7Reels1']=["RW","RW","RW","RW","M","BK","RQ","RD","M","RB","BH","RK","M","BA","RQ","RH","M","RB","RA","BH","M","RK","BQ","RH","M","BB","RD","RQ","M","RG","BD","BB","M","BK","BQ","RD","M","RB","BH","BK","M","BA","BQ","RH","M","BB","RA","BH","M","RK","BQ","BH","M","BB","BD","RQ"];
            $this->reelStrips['Fs7Reels2']=["M","RK","RG","BQ","RW","RW","RW","RW","M","RA","RB","BG","M","RD","RH","RK","M","BB","RA","RQ","M","RK","RB","RG","M","BA","RK","RQ","M","BK","RG","BQ","M","BB","RD","BG","M","BA","RB","BG","M","BG","RH","BK","M","BD","RA","BQ","M","RK","BD","BG","M","BA","BK","RQ"];
            $this->reelStrips['Fs7Reels3']=["M","RA","RH","RG","M","RD","RA","BH","RW","RW","RW","RW","M","BG","RB","RH","M","RK","RA","RG","M","RK","RD","BA","M","RH","RG","RQ","M","BA","BH","RG","M","RD","BA","BH","M","RB","BD","BQ","M","RS","BB","RH","M","BK","RA","BG","M","RK","BS","BA","M","BH","BG","RQ"];
            $this->reelStrips['Fs7Reels4']=["BD","RQ","RQ","RQ","RK","BB","BH","RG","RG","RG","RA","RA","RQ","BD","BD","BH","RA","BB","RA","RA","RB","RB","BB","RH","RH","RG","RQ","RQ","RK","RH","RH","BH","RG","RQ","RQ","RD","RB","RB","RH","BG","BG","RK","RA","RA","RH","RH","RW","RW","RW","RW","RH","BB","BB","RQ","RQ","RK","RK","RK","BB","BH","BH","BH","RA","BB"];
            $this->reelStrips['Fs7Reels5']=["BA","BA","BK","BQ","RK","RA","RG","BK","BK","BK","BK","BQ","BQ","BQ","RS","BA","BA","RA","RH","BA","RA","BA","BK","BK","BK","BQ","BA","RH","RD","BK","RH","BQ","BQ","BA","BA","RD","BK","RQ","BA","BA","BA","RD","RD","RA","BK","BK","RQ","RQ","RK","RK","BK","RA","RA","RH","RB","RA","RK","RH","BK","BQ","BS","BQ","BA","RQ"];
            $this->reelStrips['Base3Reels1']=["RA","RA","BK","BQ","RG","RD","RB","BS","RG","RD","RB","RK","RK","RK","RQ","RA","RA","RD","RG","RD","RB","RS","RG","RD","RB","RD","RD","RA","RA","RA","RG","RD","RB","BS","RG","RD","RB","RG","RD","RB","BS","RG","RD","RB","RB","RB","RH","RH","RH","RK","RW","RW","RW","RW","BQ","BQ","BQ","RG","RD","RB","BS","RG","RD","RB","RG","RA","RH","RG","RD","RB","BS","RG","RD","RB","RG","RD","RB","BS","RG","RD","RB","RG","RD","RB","RS","RG","RD","RB","RQ","BA","RQ","RQ","RQ","BQ","BQ","RB","RB","RB","RG","RD","RB","BS","RG","RD","RB","RH","RH"];
            $this->reelStrips['Base3Reels2']=["RK","RK","RK","RH","RH","RH","RH","BD","BD","RQ","RQ","RQ","RQ","RQ","RQ","RA","RA","RH","RH","RQ","RA","RK","RK","RK","RB","RB","RH","RK","RA","BB","RA","RA","RB","RB","BB","BB","BB","RH","RH","RH","BG","BG","RH","RH","RH","BB","RH","RH","RH","RH","RH","RH","RH","RH","RH","RA","RA","RA","RA","RA","RA","RA","RH","RB","RB","RB"];
            $this->reelStrips['Base3Reels3']=["BG","BD","BB","RS","BG","BD","BB","BG","BD","BB","BS","BG","BD","BB","BG","BD","BB","BS","BG","BD","BB","RA","RA","RQ","RQ","RQ","BG","BD","BB","BS","BG","BD","BB","RA","RK","RA","RA","RA","BG","BD","BB","BS","BG","BD","BB","BB","BG","RQ","RQ","RQ","BD","RK","RK","RH","BB","BB","BG","BD","BB","BS","BG","BD","BB","BG","BD","BB","BS","BG","BD","BB","BB","BB","BG","BD","BB","BS","BG","BD","BB","BG","BD","BB","BS","BG","BD","BB","RH","RH","BB","BB","BG","BD","BB","BS","BG","BD","BB","BB","BB","BB","RK","RK","RK","RH","RH","BD","RQ"];
            $this->reelStrips['Base3Reels4']=["RH","RH","RH","BD","RH","RH","BG","BB","BG","BD","BH","BD","BG","BB","BG","BB","BG","BG","BD","BB","BB","BB","BB","BB","BB","BD","BB","BG","BG","BG","BG","BD","BD","BD","RA","BH","BD","RK","RK","RK","BH","BB","RW","RW","RW","RW","BB","BG","BB","RQ","RQ","RQ","RK","BD","RA","RA","BD","BD","RK","BD","RQ","BD","BB","BB","BB","RH","RA","RA","BA","BA","BK","BK","RD","RA","RA","RK","RQ","RQ","RK","RK","RK","RK","BH","BH","BH","BA","BA","BA","RQ","RQ","RQ","RQ","RQ","RG","BK","BK","BK","BK","BQ","BQ","BQ","BQ","BA","BA","BA","BA","BQ"];
            $this->reelStrips['Base3Reels5']=["RD","RQ","RQ","RH","RH","RA","RG","RD","RB","BS","RG","RD","RB","RG","RD","RB","BS","RG","RD","RB","RQ","RG","RD","RB","RS","RG","RD","RB","RK","RG","RG","RD","RB","BS","RG","RD","RB","RG","RD","RB","BS","RG","RD","RB","RB","RB","RA","RK","RK","RQ","RG","RD","RB","BS","RG","RD","RB","RG","RD","RB","BS","RG","RD","RB","RB","RH","RA","RA","RB","RH","RA","RK","RD","RA","RA","RK","RB","RB","RK","RK","RK","RK","RD","RD","RQ","RQ","RQ","RG","RG","RD","RB","BS","RG","RD","RB","RA","RA","RA","RK","RG"];
            $this->reelStrips['Base6Reels1']=["BG","BG","BG","BG","RD","RA","BH","BB","BG","RQ","RQ","RQ","RQ","RG","BH","BH","RA","RK","RQ","BA","RB","RB","BG","BG","RG","RK","RK","RK","RG","RH","RH","BH","RG","RQ","RQ","RD","RB","RB","BG","BG","BG","RD","RD","RA","BG","BG","RD","RD","RD","RK","RH","BB","RG","RB","RB","RK","RK","RK","BB","BD","BD","BD","RB","RB"];
            $this->reelStrips['Base6Reels2']=["RW","RW","RW","RW","BH","RK","BD","BH","BH","BH","RG","RG","BD","BD","BH","BH","BG","BG","RQ","BA","RB","RB","BB","BB","BB","BH","RK","RK","RK","RH","RH","BH","RB","BD","BD","RD","RG","BB","RB","RG","RA","RD","BD","RA","BG","BG","BB","BB","BB","RQ","RQ","BG","BG","RQ","RQ","RK","RK","RK","BH","BG","BD","BD","RB","RB"];
            $this->reelStrips['Base6Reels3']=["BB","BB","BD","BG","RG","RK","RG","RH","RH","RH","BD","BG","BD","BD","BH","BH","BB","BB","RK","RB","RB","BA","BB","BB","BG","BG","BG","RB","RB","RH","RH","BH","BH","BD","BD","RD","RG","BB","RB","RG","RA","RH","BD","RA","BG","BG","RB","RG","RG","RG","BH","BG","BG","RB","RB","RB","RK","RB","BH","BD","BD","BD","BB","RQ"];
            $this->reelStrips['Base6Reels4']=["BB","RH","BB","RK","RB","RA","BH","BB","RG","RG","RA","RA","RA","BD","BH","BH","RA","BB","RA","RG","RD","RD","RB","RH","RH","BH","RB","RB","RD","RH","RH","BH","RG","RQ","RQ","RD","RB","RB","RH","BG","BG","RK","RA","RA","RH","RH","RK","RK","RK","RK","RH","BB","BB","RQ","RQ","RK","RK","RK","BB","BH","BH","BH","RA","RA"];
            $this->reelStrips['Base6Reels5']=["BA","BA","BA","BA","RD","RA","RG","BK","BK","BK","RQ","RQ","RQ","RQ","BA","BA","BK","RA","RH","BK","RA","BA","BQ","BQ","BQ","BA","BK","RH","RG","BK","RH","BQ","BQ","BA","BA","RD","BK","RQ","RG","BQ","BQ","RD","RD","RA","BA","BA","BQ","BQ","RG","BK","BA","RA","RA","RH","RB","RA","RK","RH","RG","BQ","BQ","BQ","RG","RQ"];
            $this->reelStrips['Base11Reels1']=["RA","RA","RK","RQ","BG","BA","BA","RK","RK","RK","RK","RQ","RQ","RQ","RQ","RA","RA","BA","BD","RA","BA","RA","RK","RK","RK","RQ","RA","BH","RK","RK","BB","RQ","RQ","RA","RA","RK","RK","BQ","RA","RA","RA","RK","BK","RK","RK","RK","BK","BK","BK","BK","RK","BA","BA","BK","BK","BA","BK","BH","RK","RQ","RQ","RQ","RA","BQ"];
            $this->reelStrips['Base11Reels2']=["BK","BK","BK","BK","BH","BA","RH","BH","BH","BH","BA","BA","BA","RD","BH","RH","BA","RB","BA","BA","BB","RB","RB","BH","BH","BG","BK","BK","RB","BH","BH","RH","BD","BQ","BQ","BD","RB","RB","BH","RG","RG","BB","RH","BH","BH","RH","RB","RB","BB","RB","BH","RB","RB","BG","BH","RH","BH","BD","RB","BH","RH","BH","BA","BA"];
            $this->reelStrips['Base11Reels3']=["BG","BG","BG","BG","RH","BK","BD","BD","BD","RH","RD","RG","RD","RD","RH","RH","RB","RB","BK","BB","BB","BB","RB","RB","BG","BG","BG","RB","RB","BH","BB","RH","RH","RD","RD","BD","BD","BD","BB","RH","BA","BB","RD","BA","RG","RG","BD","BD","BD","BD","RH","RG","RG","BB","BB","BB","BK","BB","RH","RD","RD","RD","RB","BQ"];
            $this->reelStrips['Base11Reels4']=["BW","BW","BW","BW","BK","BK","BG","RH","RH","RH","RG","RD","RD","RD","RH","RH","RG","RG","BQ","RA","BB","BB","RB","RB","RB","RH","BK","BK","BK","BH","BH","RH","BB","RD","RD","BD","BG","RB","BB","BQ","BA","BD","RD","BA","RG","RG","RB","RB","RB","BQ","BQ","RG","RG","BQ","BQ","BK","BK","BK","RH","RG","RD","RD","BQ","BQ","BK","BK","BG","RH","RH","RH","RG","RD","RD","RD","RH","RH","RG","RG","BQ","RA","BB","BB","RB","RB","RB","RH","BK","BK","BK","BH","BH","RH","BB","RD","RD","BD","BG","RB","BB","BQ","BA","BD","RD","BA","RG","RG","RB"];
            $this->reelStrips['Base11Reels5']=["BW","BW","BW","BW","BQ","BQ","BA","BA","BA","BA","RD","RD","BB","BB","RH","BA","BA","BK","BQ","RA","BB","BB","BD","BD","RG","RG","BK","BK","BK","BH","BH","RH","RH","BQ","BQ","BD","BB","BB","RG","RG","RG","BQ","BQ","BA","BD","BD","BK","BK","BK","BK","BH","RB","RB","BD","BD","BK","BK","BK","BG","BB","BB","RD","BQ","BQ","BD","BD","BH","BH","BQ","BQ","BQ","BQ","BH","BH","RH","BA","BA","BK","BQ","RA","BB","BB","RB","RH","BG","RG","BK","BK","BK","BH","BH","BG","RH","BH","BH","BD","BB","BB","RG","RG","RG","BH","BH","BA","BG","RG","BK"];
            $this->reelStrips['Base0Reels1']=["BD","BD","BD","BD","RB","RA","RA","RA","RG","RG","RH","RB","RQ","RQ","BH","BH","RA","RK","RB","BA","RB","RB","BG","BG","BG","RG","RK","RK","RK","BG","RG","BH","BH","RQ","RQ","RG","BG","RB","BG","BG","RQ","RG","RQ","RA","BG","BG","RD","BH","RK","RK","RG","BB","BB","RQ","RQ","RK","RK","RK","BB","BD","BD","BD","BB","RQ"];
            $this->reelStrips['Base0Reels2']=["RD","RD","BG","BG","RH","RD","RD","RH","RQ","RH","BD","BD","RG","RB","BH","BH","BB","RK","RQ","BA","RH","RH","BB","BB","BB","RK","RK","RK","RK","RH","RH","RD","RH","BD","BD","RD","RH","RD","BG","BG","BG","RQ","RQ","RD","RA","BG","BB","RD","RD","RH","BH","BG","BG","RQ","RQ","RK","RK","RK","BH","BD","BD","BD","RQ","RQ"];
            $this->reelStrips['Base0Reels3']=["RW","RW","RW","RW","BG","BD","BB","RB","RG","RG","BH","RD","BD","RD","BH","RG","BB","RG","RG","RB","RG","BA","RA","BB","RA","RA","BD","RB","RB","RH","RH","RA","BH","RD","BD","RD","RB","RB","BG","RD","BG","RB","RB","RA","RD","RD","RG","RG","RG","RG","BH","RH","RH","RB","RB","RA","RK","RH","BH","BD","RH","RD","BB","RQ","RD","BD","RG","RB","RG","RG","BB","RD","BD","RD","BH","BH","BB","RG","RG","RB","RG","BA","RA","BB","RA","RA","BD","RB","RB","RH","RH","RA","BH","RD","BD","RD","RB","RB","BG","BG","BG","RB","RB","RA","RD","RD","RG"];
            $this->reelStrips['Base0Reels4']=["BB","RH","BB","RK","RQ","RA","BH","RG","BH","BD","RA","RA","RA","RG","BB","BH","RA","RG","RA","RA","RB","RB","BB","RH","RH","RG","RK","BH","RK","RH","RH","RG","BH","RQ","RQ","RD","RB","RB","RH","BG","BG","BH","RA","RA","RH","RH","RK","RK","RK","RK","RH","BB","BB","RQ","RQ","RK","RK","RK","BB","RG","BH","BH","RA","RA"];
            $this->reelStrips['Base0Reels5']=["RW","RW","RW","RW","RH","RA","BQ","BQ","BQ","RG","BK","BK","BK","BK","RB","RB","BA","RA","RG","BA","RA","BA","BA","BA","BA","RB","RG","RH","RD","RH","RH","BK","BK","RB","BQ","RD","BK","RQ","RD","BA","BA","BA","RD","RA","BK","RB","BK","BQ","RG","BA","BK","RA","RA","RH","RB","RA","RK","RH","BK","BQ","RG","BQ","BA","RQ","RH","RA","RG","BQ","BQ","BQ","BK","RG","BK","BK","RB","RB","BA","RA","RG","BA","RA","BA","RG","BA","BA","RB","RG","RH","RD","RH","RH","BK","BK","RB","BQ","RD","BK","RQ","RD","BA","BA","BA","RD","RA","BK","RB","RG"];
            $this->reelStrips['Base12Reels1']=["BW","BW","BW","BW","BQ","BQ","BB","BA","BA","BA","BA","BD","BD","BD","BB","BB","RQ","BA","BG","RQ","BA","RA","BB","BB","BB","BB","BG","BH","BD","BH","BH","BG","BA","BB","BD","BD","BB","BQ","BG","RK","RQ","BD","BD","BA","BH","BB","BG","RQ","RQ","BA","BG","BA","BA","BH","BG","BA","BK","BH","BG","BB","BB","BB","BH","BQ"];
            $this->reelStrips['Base12Reels2']=["RB","BH","RB","BK","BG","BA","RH","RH","BG","RD","BA","BA","BA","BG","RB","RH","BA","BG","BA","RH","BB","BB","RB","BH","BH","BG","BK","BG","BK","BH","BH","RH","BG","BQ","BQ","BD","BB","BB","BH","RG","RG","BK","BA","BA","BH","BH","BK","BK","BK","BK","BH","RB","RB","BQ","BQ","BK","BK","BK","RB","RH","RH","RH","BA","BA"];
            $this->reelStrips['Base12Reels3']=["RB","RB","RG","BD","BG","RD","BG","BB","BG","BG","RB","BD","RD","BD","RH","RH","RB","BG","BG","BB","BG","RA","RA","RB","RA","RA","RD","BB","BB","BH","BH","RA","RH","BD","RD","BD","BB","BB","RG","RG","RG","BB","BB","RA","BD","BD","BG","BG","BG","BG","RH","BH","BH","BB","BB","BA","BK","BH","RH","RD","BH","BD","RB","BQ"];
            $this->reelStrips['Base12Reels4']=["BQ","BQ","BQ","BQ","BB","BA","BG","BG","BG","BG","BQ","BA","BK","BK","BQ","BQ","BA","BK","BB","BK","BB","BB","BQ","BA","BH","BG","BK","BA","BK","BG","BG","BK","BK","BQ","BQ","BG","BG","BB","BA","BA","BA","BD","BQ","BA","BQ","BQ","BK","BA","BK","BK","BG","BA","BA","BQ","BQ","BK","BK","BK","BA","BQ","BQ","BQ","BK","BQ"];
            $this->reelStrips['Base12Reels5']=["BD","BD","BK","BK","BH","BD","BD","BH","BQ","BH","BQ","BK","BK","BB","BQ","BQ","BK","BK","BA","BQ","BH","BH","BA","BA","BA","BQ","BK","BK","BK","BH","BH","BD","BH","BK","BK","BD","BH","BD","BA","BQ","BG","BQ","BQ","BD","BA","BA","BK","BD","BD","BH","BQ","BA","BA","BQ","BQ","BK","BK","BK","BQ","BA","BA","BA","BQ","BQ"];
            $this->reelStrips['Base18Reels1']=["RA","BH","BH","RA","BK","RQ","RA","BK","BD","BD","RQ","RB","RG","BH","RB","RG","BK","RQ","BW","BW","BW","BW"];
            $this->reelStrips['Base18Reels2']=["BQ","RH","BA","BA","BB","BG","BB","BQ","RD","RH","RD","RH","RK","RK","BA","RK","BG","BQ","BW","BW","BW","BW"];
            $this->reelStrips['Base18Reels3']=["BQ","BG","RB","BA","RQ","BQ","RK","RH","RG","RA","BK","BK","RD","RQ","BH","RK","BD","BB","BW","BW","BW","BW"];
            $this->reelStrips['Base18Reels4']=["BK","RB","RG","RB","BK","BD","BH","BH","BD","RG","RA","RQ","RQ","RA"];
            $this->reelStrips['Base18Reels5']=["RD","BG","BG","BA","BQ","BA","RH","BB","BQ","RK","BB","RD","RK","RH"];
            $this->reelStrips['Base15Reels1']=["RB","RQ","BH","BD","RA","RA","RK","RK","RB","BK","BG","BB","BA","BB","BD","BH","RA","RQ"];
            $this->reelStrips['Base15Reels2']=["RH","RD","RG","BH","RA","RK","RQ","RG","BG","RG","BB","RG","BG","RG","RD","RQ","RH","RK"];
            $this->reelStrips['Base15Reels3']=["BH","BA","BK","BD","BB","BA","BK","BD","RB","BA","BK","BD","RB","BK","BQ","BD","BA","BG"];
            $this->reelStrips['Base15Reels4']=["BD","BH","BA","BB","BD","BH","BA","BB","BK","BH","BA","BB","BQ","BK","BG","BA","RQ","BD"];
            $this->reelStrips['Base15Reels5']=["BH","RG","BB","BB","BA","BG","RK","BK","RG","BG","RG","RQ","BH","BG","RB","BB","RG","BD"];
            $this->reelStrips['Fs1Reels1']=["M","RG","BD","RB","M","BK","RQ","RD","M","RB","BS","RK","M","BA","RQ","RH","M","RB","RA","BH","M","RK","BQ","RH","M","RS","RD","RQ","M","RG","BD","BB","M","BK","BQ","RD","M","RB","BH","BK","M","BA","BQ","RH","M","BB","RA","BH","M","RK","BQ","BH","M","BB","BD","RQ"];
            $this->reelStrips['Fs1Reels2']=["M","RK","RG","BQ","RW","RW","RW","RW","M","RA","BB","BH","M","BD","BH","RK","M","BD","BA","RQ","M","RK","RB","BG","M","BB","RK","RQ","M","BK","RG","BQ","M","BB","RD","BG","M","BA","RB","BG","M","BD","RH","BK","M","BD","RA","BQ","M","RK","BB","BG","M","BA","BK","RQ","M","BK","BB","BG","M","BA","BK","BQ","M","RK","BB","BG","M","BA","BK","RQ","M","BK","BB","BG","M","BA","BK","RQ","M","BK","BB","BG","M","BA","BK","BQ","M","RK","BB","BG","M","BA","BK","RQ","M","BK","BB","BG","M","BA","BK","RQ"];
            $this->reelStrips['Fs1Reels3']=["M","RA","RH","RG","M","RD","RA","BH","M","RB","RD","RQ","M","BG","RB","RH","M","RK","RA","RG","M","RK","RD","BA","M","RH","RG","RQ","M","BA","BH","RG","M","RD","BA","BS","M","RB","BD","BQ","M","BG","BB","RH","M","BK","RA","BG","M","RK","RS","BA","M","BH","BG","RQ"];
            $this->reelStrips['Fs1Reels4']=["RQ","RQ","RQ","RQ","RK","RA","BH","RG","RG","RG","RA","RA","RQ","BD","BH","BH","RA","BB","RA","RA","RB","RB","BB","RH","RH","RG","RQ","RQ","RK","RH","RH","BH","RG","RQ","RQ","RD","RB","RB","RH","BG","BG","RK","RA","RA","RH","RH","RK","RK","RK","RK","RH","BB","BB","RQ","RQ","RK","RK","RK","BB","BH","BH","BH","RA","RA"];
            $this->reelStrips['Fs1Reels5']=["BA","BA","BK","BQ","RH","RA","RG","BK","BK","BK","BK","BQ","BQ","BQ","BQ","BA","BA","RA","RH","BA","RA","BA","BK","BK","BK","BQ","BA","RH","RD","BK","RH","BQ","BQ","BA","BA","RD","BK","RQ","BA","BA","BA","RD","RD","RA","BK","BK","RG","RG","RG","RG","BK","RA","RA","RH","RB","RA","RK","RH","BK","BQ","BQ","BQ","BA","RQ"];
            $this->reelStrips['Fs15Reels1']=["RA","RA","RK","RQ","BK","BA","BG","RK","RK","RK","RK","RQ","RQ","RQ","BS","RA","RA","BA","BH","RA","BA","RA","RK","RK","RK","RQ","RA","BH","BD","RK","BH","RQ","RQ","RA","RA","BD","RK","BQ","RA","RA","RA","BD","BD","BA","RK","RK","BQ","BQ","BK","BK","RK","BA","BA","BH","BB","BA","BK","BH","RK","RQ","RS","RQ","RA","BQ"];
            $this->reelStrips['Fs15Reels2']=["RD","BQ","BQ","BQ","BK","RB","RH","BG","BG","BG","BA","BA","BQ","RD","RD","RH","BA","RB","BA","BA","BB","BB","RB","BH","BH","BG","BQ","BQ","BK","BH","BH","RH","BG","BQ","BQ","BD","BB","BB","BH","RG","RG","BK","BA","BA","BH","BH","BW","BW","BW","BW","BH","RB","RB","BQ","BQ","BK","BK","BK","RB","RH","RH","RH","BA","RB"];
            $this->reelStrips['Fs15Reels3']=["M","BA","BH","BG","M","BD","BA","RH","BW","BW","BW","BW","M","RG","BB","BH","M","BK","BA","BG","M","BK","BD","RA","M","BH","BG","BQ","M","RA","RH","BG","M","BD","RA","RH","M","BB","RD","RQ","M","BS","RB","BH","M","RK","BA","RG","M","BK","RS","RA","M","RH","RG","BQ"];
            $this->reelStrips['Fs15Reels4']=["M","BK","BG","RQ","BW","BW","BW","BW","M","BA","BB","RG","M","BD","BH","BK","M","RB","BA","BQ","M","BK","BB","BG","M","RA","BK","BQ","M","RK","BG","RQ","M","RB","BD","RG","M","RA","BB","RG","M","RG","BH","RK","M","RD","BA","RQ","M","BK","RD","RG","M","RA","RK","BQ"];
            $this->reelStrips['Fs15Reels5']=["BW","BW","BW","BW","M","RK","BQ","BD","M","BB","RH","BK","M","RA","BQ","BH","M","BB","BA","RH","M","BK","RQ","BH","M","RB","BD","BQ","M","BG","RD","RB","M","RK","RQ","BD","M","BB","RH","RK","M","RA","RQ","BH","M","RB","BA","RH","M","BK","RQ","RH","M","RB","RD","BQ"];
            $this->reelStrips['Fs4Reels1']=["M","RG","BD","RB","M","BK","RQ","RD","M","RB","BS","RK","M","BA","RQ","RH","M","RB","RA","BH","M","RK","BQ","RH","M","RS","RD","RQ","M","RG","BD","BB","M","BK","BQ","RD","M","RB","BH","BK","M","BA","BQ","RH","M","BB","RA","BH","M","RK","BQ","BH","M","BB","BD","RQ"];
            $this->reelStrips['Fs4Reels2']=["M","RK","RG","BQ","M","RB","RD","RG","M","RA","RB","BH","M","RD","RH","RK","M","BD","RA","RQ","M","RK","RB","RG","M","BB","RK","RQ","M","BK","RG","BQ","M","BB","RD","BG","M","BA","RB","BG","M","BD","RH","BK","M","BD","RA","BQ","M","RK","BB","BG","M","BA","BK","RQ"];
            $this->reelStrips['Fs4Reels3']=["M","RA","RH","RG","M","RD","RA","BH","M","RB","RD","RQ","M","BG","RB","RH","M","RK","RA","RG","M","RK","RD","BA","M","RH","RG","RQ","M","BA","BH","RG","M","RD","BA","BS","M","RB","BD","BQ","M","BG","BB","RH","M","BK","RA","BG","M","RK","RS","BA","M","BH","BG","RQ"];
            $this->reelStrips['Fs4Reels4']=["RQ","RQ","RQ","RQ","RK","RA","BH","RG","RG","RG","RA","RA","RQ","BD","BH","BH","RA","BB","RA","RA","RB","RB","BB","RH","RH","RG","RQ","RQ","RK","RH","RH","BH","RG","RQ","RQ","RD","RB","RB","RH","BG","BG","RK","RA","RA","RH","RH","RK","RK","RK","RK","RH","BB","BB","RQ","RQ","RK","RK","RK","BB","BH","BH","BH","RA","RA"];
            $this->reelStrips['Fs4Reels5']=["BA","BA","BK","BQ","RH","RA","RG","BK","BK","BK","BK","BQ","BQ","BQ","BQ","BA","BA","RA","RH","BA","RA","BA","BK","BK","BK","BQ","BA","RH","RD","BK","RH","BQ","BQ","BA","BA","RD","BK","RQ","BA","BA","BA","RD","RD","RA","BK","BK","RW","RW","RW","RW","BK","RA","RA","RH","RB","RA","RK","RH","BK","BQ","BQ","BQ","BA","RQ","BA","BA","BK","BQ","RH","RA","BA","BK","BK","BK","BK","BQ","BQ","BQ","BQ","BA","BA","BA","BK","BQ","RH","RA","BA","BK","BK","BK","BK","BQ","BQ","BQ","BQ","BA","BQ","RH","RA","BA","BK","BK","BK","BK","BQ","BQ","BQ"];
            $this->reelStrips['Fs12Reels1']=["RA","RA","RK","RQ","BH","BA","BG","RK","RK","RK","RK","RQ","RQ","RQ","RQ","RA","RA","BA","BH","RA","BA","RA","RK","RK","RK","RQ","RA","BH","BD","RK","BH","RQ","RQ","RA","RA","BD","RK","BQ","RA","RA","RA","BD","BD","BA","RK","RK","BW","BW","BW","BW","RK","BA","BA","BH","BB","BA","BK","BH","RK","RQ","RQ","RQ","RA","BQ","RA","RA","RK","RQ","BH","BA","RA","RK","RK","RK","RK","RQ","RQ","RQ","RQ","RA","RA","RA","RK","RQ","BH","BA","RA","RK","RK","RK","RK","RQ","RQ","RQ","RQ","RA","RQ","BH","BA","RA","RK","RK","RK","RK","RQ","RQ","RQ"];
            $this->reelStrips['Fs12Reels2']=["BQ","BQ","BQ","BQ","BK","BA","RH","BG","BG","BG","BA","BA","BQ","RD","RH","RH","BA","RB","BA","BA","BB","BB","RB","BH","BH","BG","BQ","BQ","BK","BH","BH","RH","BG","BQ","BQ","BD","BB","BB","BH","RG","RG","BK","BA","BA","BH","BH","BK","BK","BK","BK","BH","RB","RB","BQ","BQ","BK","BK","BK","RB","RH","RH","RH","BA","BA"];
            $this->reelStrips['Fs12Reels3']=["M","BA","BH","BG","M","BD","BA","RH","M","BB","BD","BQ","M","RG","BB","BH","M","BK","BA","BG","M","BK","BD","RA","M","BH","BG","BQ","M","RA","RH","BG","M","BD","RA","RS","M","BB","RD","RQ","M","RG","RB","BH","M","RK","BA","RG","M","BK","BS","RA","M","RH","RG","BQ"];
            $this->reelStrips['Fs12Reels4']=["M","BK","BG","RQ","M","BB","BD","BG","M","BA","BB","RH","M","BD","BH","BK","M","RD","BA","BQ","M","BK","BB","BG","M","RB","BK","BQ","M","RK","BG","RQ","M","RB","BD","RG","M","RA","BB","RG","M","RD","BH","RK","M","RD","BA","RQ","M","BK","RB","RG","M","RA","RK","BQ"];
            $this->reelStrips['Fs12Reels5']=["M","BG","RD","BB","M","RK","BQ","BD","M","BB","RS","BK","M","RA","BQ","BH","M","BB","BA","RH","M","BK","RQ","BH","M","BS","BD","BQ","M","BG","RD","RB","M","RK","RQ","BD","M","BB","RH","RK","M","RA","RQ","BH","M","RB","BA","RH","M","BK","RQ","RH","M","RB","RD","BQ"];
            $this->reelStrips['Fs16Reels1']=["RS","RK","RK","RA","BS","BG","BD","BB","RS","BB","RQ","RK","BS","BD","RQ","RQ","RS","RQ","BG","BG","BS","RK","BB","RQ"];
            $this->reelStrips['Fs16Reels2']=["RG","BA","BG","RD","RD","BK","BK","BK","RB","RB","BA","BA","RG","RD","RB","BQ","RD","BQ","RG","BD","RB","RG","BG","BG"];
            $this->reelStrips['Fs16Reels3']=["RS","RG","RD","RQ","RS","RG","RA","RH","RS","BA","BA","RK","BS","RA","RK","RA","BS","BK","BA","RQ","BS","RH","RH","RB"];
            $this->reelStrips['Fs16Reels4']=["BG","BG","RG","RA","BD","BD","RK","RK","BB","BB","RA","RA","BD","BD","RH","RH","BB","RQ","RQ","RD","BB","RB","RG","RG"];
            $this->reelStrips['Fs16Reels5']=["RS","BK","BK","BA","BS","BK","BA","BH","RS","BH","BQ","BK","BS","BA","BQ","BQ","RS","BQ","BQ","BQ","BS","BK","BK","BQ"];
            $this->reelStrips['Fs13Reels1']=["RA","RA","RK","RQ","BK","BA","BG","RK","RK","RK","RK","RQ","RQ","RQ","BS","RA","RA","BA","BH","RA","BA","RA","RK","RK","RK","RQ","RA","BH","BD","RK","BH","RQ","RQ","RA","RA","BD","RK","BQ","RA","RA","RA","BD","BD","BA","RK","RK","BW","BW","BW","BW","RK","BA","BA","BH","BB","BA","BK","BH","RK","RQ","RS","RQ","RA","BQ"];
            $this->reelStrips['Fs13Reels2']=["RD","BQ","BQ","BQ","BK","RB","RH","BG","BG","BG","BA","BA","BQ","RD","RD","RH","BA","RB","BA","BA","BB","BB","RB","BH","BH","BG","BQ","BQ","BK","BH","BH","RH","BG","BQ","BQ","BD","BB","BB","BH","RG","RG","BK","BA","BA","BH","BH","BW","BW","BW","BW","BH","RB","RB","BQ","BQ","BK","BK","BK","RB","RH","RH","RH","BA","RB"];
            $this->reelStrips['Fs13Reels3']=["M","BA","BH","BG","M","BD","BA","RH","M","BD","BA","RH","M","RG","BB","BH","M","BK","BA","BG","M","BK","BD","RA","M","BH","BG","BQ","M","RA","RH","BG","M","BD","RA","RH","M","BB","RD","RQ","M","BS","RB","BH","M","RK","BA","RG","M","BK","RS","RA","M","RH","RG","BQ"];
            $this->reelStrips['Fs13Reels4']=["M","BK","BG","RQ","BW","BW","BW","BW","M","BA","BB","RG","M","BD","BH","BK","M","RB","BA","BQ","M","BK","BB","BG","M","RA","BK","BQ","M","RK","BG","RQ","M","RB","BD","RG","M","RA","BB","RG","M","RG","BH","RK","M","RD","BA","RQ","M","BK","RD","RG","M","RA","RK","BQ"];
            $this->reelStrips['Fs13Reels5']=["BW","BW","BW","BW","M","RK","BQ","BD","M","BB","RH","BK","M","RA","BQ","BH","M","BB","BA","RH","M","BK","RQ","BH","M","RB","BD","BQ","M","BG","RD","RB","M","RK","RQ","BD","M","BB","RH","RK","M","RA","RQ","BH","M","RB","BA","RH","M","BK","RQ","RH","M","RB","RD","BQ"];
            $this->reelStrips['Base10Reels1']=["BW","BW","BW","BW","BH","BA","RQ","RQ","RQ","BG","RK","RK","RK","RK","BB","BB","RA","BA","BG","RA","BA","RA","RA","RA","RA","BB","BG","BH","BD","BH","BH","RK","RK","BB","RQ","BD","RK","BQ","BD","RA","RA","RA","BD","BA","RK","BB","RK","RQ","BG","RA","RK","BA","BA","BH","BB","BA","BK","BH","RK","RQ","BG","RQ","RA","BQ","BH","BA","BG","RQ","RQ","RQ","RK","BG","RK","RK","BB","BB","RA","BA","BG","RA","BA","RA","BG","RA","RA","BB","BG","BH","BD","BH","BH","RK","RK","BB","RQ","BD","RK","BQ","BD","RA","RA","RA","BD","BA","RK","BB","BG"];
            $this->reelStrips['Base10Reels2']=["RB","BH","RB","BK","BQ","BA","RH","BG","RH","RD","BA","BA","BA","BG","RB","RH","BA","BG","BA","BA","BB","BB","RB","BH","BH","BG","BK","RH","BK","BH","BH","BG","RH","BQ","BQ","BD","BB","BB","BH","RG","RG","RH","BA","BA","BH","BH","BK","BK","BK","BK","BH","RB","RB","BQ","BQ","BK","BK","BK","RB","BG","RH","RH","BA","BA"];
            $this->reelStrips['Base10Reels3']=["BW","BW","BW","BW","RG","RD","RB","BB","BG","BG","RH","BD","RD","BD","RH","BG","RB","BG","BG","BB","BG","RA","BA","RB","BA","BA","RD","BB","BB","BH","BH","BA","RH","BD","RD","BD","BB","BB","RG","BD","RG","BB","BB","BA","BD","BD","BG","BG","BG","BG","RH","BH","BH","BB","BB","BA","BK","BH","RH","RD","BH","BD","RB","BQ","BD","RD","BG","BB","BG","BG","RB","BD","RD","BD","RH","RH","RB","BG","BG","BB","BG","RA","BA","RB","BA","BA","RD","BB","BB","BH","BH","BA","RH","BD","RD","BD","BB","BB","RG","RG","RG","BB","BB","BA","BD","BD","BG"];
            $this->reelStrips['Base10Reels4']=["BD","BD","RG","RG","BH","BD","BD","BH","BQ","BH","RD","RD","BG","BB","RH","RH","RB","BK","BQ","RA","BH","BH","RB","RB","RB","BK","BK","BK","BK","BH","BH","BD","BH","RD","RD","BD","BH","BD","RG","RG","RG","BQ","BQ","BD","BA","RG","RB","BD","BD","BH","RH","RG","RG","BQ","BQ","BK","BK","BK","RH","RD","RD","RD","BQ","BQ"];
            $this->reelStrips['Base10Reels5']=["RD","RD","RD","RD","BB","BA","BA","BA","BG","BG","BH","BB","BQ","BQ","RH","RH","BA","BK","BB","RA","BB","BB","RG","RG","RG","BG","BK","BK","BK","RG","BG","RH","RH","BQ","BQ","BG","RG","BB","RG","RG","BQ","BG","BQ","BA","RG","RG","BD","RH","BK","BK","BG","RB","RB","BQ","BQ","BK","BK","BK","RB","RD","RD","RD","RB","BQ"];
            $this->reelStrips['Base2Reels1']=["RD","RD","RK","RK","RH","RD","RD","RH","RQ","RH","RQ","RK","RK","RB","RQ","RQ","RK","RK","RA","RQ","RH","RH","RA","RA","RA","RQ","RK","RK","RK","RH","RH","RD","RH","RK","RK","RD","RH","RD","RA","RQ","RG","RQ","RQ","RD","RA","RA","RK","RD","RD","RH","RQ","RA","RA","RQ","RQ","RK","RK","RK","RQ","RA","RA","RA","RQ","RQ"];
            $this->reelStrips['Base2Reels2']=["RQ","RQ","RQ","RQ","RB","RA","RG","RG","RG","RG","RQ","RA","RK","RK","RQ","RQ","RA","RK","RB","RK","RB","RB","RQ","RA","RH","RG","RK","RA","RK","RG","RG","RK","RK","RQ","RQ","RG","RG","RB","RA","RA","RA","RD","RQ","RA","RQ","RQ","RK","RA","RK","RK","RG","RA","RA","RQ","RQ","RK","RK","RK","RA","RQ","RQ","RQ","RK","RQ"];
            $this->reelStrips['Base2Reels3']=["BB","BB","BG","RD","RG","BD","RG","RB","RG","RG","BB","RD","BD","RD","BH","BH","BB","RG","RG","RB","RG","BA","BA","BB","BA","BA","BD","RB","RB","RH","RH","BA","BH","RD","BD","RD","RB","RB","BG","BG","BG","RB","RB","BA","RD","RD","RG","RG","RG","RG","BH","RH","RH","RB","RB","RA","RK","RH","BH","BD","RH","RD","BB","RQ"];
            $this->reelStrips['Base2Reels4']=["BB","RH","BB","RK","RG","RA","BH","BH","RG","BD","RA","RA","RA","RG","BB","BH","RA","RG","RA","BH","RB","RB","BB","RH","RH","RG","RK","RG","RK","RH","RH","BH","RG","RQ","RQ","RD","RB","RB","RH","BG","BG","RK","RA","RA","RH","RH","RK","RK","RK","RK","RH","BB","BB","RQ","RQ","RK","RK","RK","BB","BH","BH","BH","RA","RA"];
            $this->reelStrips['Base2Reels5']=["RW","RW","RW","RW","RQ","RQ","RB","RA","RA","RA","RA","RD","RD","RD","RB","RB","BQ","RA","RG","BQ","RA","BA","RB","RB","RB","RB","RG","RH","RD","RH","RH","RG","RA","RB","RD","RD","RB","RQ","RG","BK","BQ","RD","RD","RA","RH","RB","RG","BQ","BQ","RA","RG","RA","RA","RH","RG","RA","RK","RH","RG","RB","RB","RB","RH","RQ"];
            $this->reelStrips['Fs10Reels1']=["RA","RA","RK","RQ","BH","BA","BG","RK","RK","RK","RK","RQ","RQ","RQ","RQ","RA","RA","BA","BH","RA","BA","RA","RK","RK","RK","RQ","RA","BH","BD","RK","BH","RQ","RQ","RA","RA","BD","RK","BQ","RA","RA","RA","BD","BD","BA","RK","RK","BG","BG","BG","BG","RK","BA","BA","BH","BB","BA","BK","BH","RK","RQ","RQ","RQ","RA","BQ"];
            $this->reelStrips['Fs10Reels2']=["BQ","BQ","BQ","BQ","BK","BA","RH","BG","BG","BG","BA","BA","BQ","RD","RH","RH","BA","RB","BA","BA","BB","BB","RB","BH","BH","BG","BQ","BQ","BK","BH","BH","RH","BG","BQ","BQ","BD","BB","BB","BH","RG","RG","BK","BA","BA","BH","BH","BK","BK","BK","BK","BH","RB","RB","BQ","BQ","BK","BK","BK","RB","RH","RH","RH","BA","BA"];
            $this->reelStrips['Fs10Reels3']=["M","BA","BW","BW","BW","BW","BA","RH","M","BB","BD","BQ","M","RG","BB","BH","M","BK","BA","BG","M","BK","BD","RA","M","BH","BG","BQ","M","RA","RH","BG","M","BD","RA","RS","M","BB","RD","RQ","M","RG","RB","BH","M","RK","BA","RG","M","BK","BS","RA","M","RH","RG","BQ","M","RH","RG","RQ","M","RH","RG","RQ","M","RH","RG","RQ","M","RH","RG","RQ","M","RH","RG","RQ","M","RH","RG","RQ","M","RH","RG","RQ","M","RA","RH","BG","M","BD","RA","RS","M","BB","RD","RQ","M","RG","RB","BH","M","RK","BA","RG"];
            $this->reelStrips['Fs10Reels4']=["M","BK","BG","RQ","M","BB","BD","BG","M","BA","BB","RH","M","BD","BH","BK","M","RD","BA","BQ","M","BK","BB","BG","M","RB","BK","BQ","M","RK","BG","RQ","M","RB","BD","RG","M","RA","BB","RG","M","RD","BH","RK","M","RD","BA","RQ","M","BK","RB","RG","M","RA","RK","BQ"];
            $this->reelStrips['Fs10Reels5']=["M","BG","RD","BB","M","RK","BQ","BD","M","BB","RS","BK","M","RA","BQ","BH","M","BB","BA","RH","M","BK","RQ","BH","M","BS","BD","BQ","M","BG","RD","RB","M","RK","RQ","BD","M","BB","RH","RK","M","RA","RQ","BH","M","RB","BA","RH","M","BK","RQ","RH","M","RB","RD","BQ"];
            $this->reelStrips['Base19Reels1']=["BA","RD","BA","RH","RD","BQ","RH","BB","RK","BA","RK","RK","BB","BG","BG","BQ","RH","BQ","BW","BW","BW","BW"];
            $this->reelStrips['Base19Reels2']=["BD","BK","BD","RQ","RA","BH","BH","RQ","BK","RA","RG","RG","RB","RQ","RA","BH","BK","RB","BW","BW","BW","BW"];
            $this->reelStrips['Base19Reels3']=["BK","BB","RK","BQ","RA","RD","RK","BA","BD","BH","BQ","RH","BK","RB","RG","BG","RQ","RQ","BW","BW","BW","BW"];
            $this->reelStrips['Base19Reels4']=["RD","BQ","RH","BA","BG","BQ","RD","BB","RK","BB","BG","BA","RK","RH"];
            $this->reelStrips['Base19Reels5']=["RB","RG","BD","RQ","RB","BK","RQ","RA","BH","RA","BK","BD","BH","RG"];
            $this->reelStrips['Fs6Reels1']=["RW","RW","RW","RW","M","BK","RQ","RD","M","RB","BH","RK","M","BA","RQ","RH","M","RB","RA","BH","M","RK","BQ","RH","M","BB","RD","RQ","M","RG","BD","BB","M","BK","BQ","RD","M","RB","BH","BK","M","BA","BQ","RH","M","BB","RA","BH","M","RK","BQ","BH","M","BB","BD","RQ"];
            $this->reelStrips['Fs6Reels2']=["M","RK","RG","BQ","RW","RW","RW","RW","M","RA","RB","BG","M","RD","RH","RK","M","BB","RA","RQ","M","RK","RB","RG","M","BA","RK","RQ","M","BK","RG","BQ","M","BB","RD","BG","M","BA","RB","BG","M","BG","RH","BK","M","BD","RA","BQ","M","RK","BD","BG","M","BA","BK","RQ"];
            $this->reelStrips['Fs6Reels3']=["M","RA","RH","RG","M","RD","RA","BH","RW","RW","RW","RW","M","BG","RB","RH","M","RK","RA","RG","M","RK","RD","BA","M","RH","RG","RQ","M","BA","BH","RG","M","RD","BA","BH","M","RB","BD","BQ","M","RS","BB","RH","M","BK","RA","BG","M","RK","BS","BA","M","BH","BG","RQ"];
            $this->reelStrips['Fs6Reels4']=["BD","RQ","RQ","RQ","RK","BB","BH","RG","RG","RG","RA","RA","RQ","BD","BD","BH","RA","BB","RA","RA","RB","RB","BB","RH","RH","RG","RQ","RQ","RK","RH","RH","BH","RG","RQ","RQ","RD","RB","RB","RH","BG","BG","RK","RA","RA","RH","RH","RQ","RQ","RK","RK","RH","BB","BB","RQ","RQ","RK","RK","RK","BB","BH","BH","BH","RA","BB"];
            $this->reelStrips['Fs6Reels5']=["BA","BA","BK","BQ","RK","RA","RG","BK","BK","BK","BK","BQ","BQ","BQ","RS","BA","BA","RA","RH","BA","RA","BA","BK","BK","BK","BQ","BA","RH","RD","BK","RH","BQ","BQ","BA","BA","RD","BK","RQ","BA","BA","BA","RD","RD","RA","BK","BK","RW","RW","RW","RW","BK","RA","RA","RH","RB","RA","RK","RH","BK","BQ","BS","BQ","BA","RQ"];
            $this->reelStrips['Base5Reels1']=["RH","BG","RB","RB","RA","RG","BK","RK","BG","RG","BG","BQ","RH","RG","BB","RB","BG","RD"];
            $this->reelStrips['Base5Reels2']=["RD","RH","RA","RB","RD","RH","RA","RB","RK","RH","RA","RB","RQ","RK","RG","RA","BQ","RD"];
            $this->reelStrips['Base5Reels3']=["RH","RA","RK","RD","RB","RA","RK","RD","BB","RA","RK","RD","BB","RK","RQ","RD","RA","RG"];
            $this->reelStrips['Base5Reels4']=["BH","BD","BG","RH","BA","BK","BQ","BG","RG","BG","RB","BG","RG","BG","BD","BQ","BH","BK"];
            $this->reelStrips['Base5Reels5']=["BB","BQ","RH","RD","BA","BA","BK","BK","BB","RK","RG","RB","RA","RB","RD","RH","BA","BQ"];
            $this->reelStrips['Fs3Reels1']=["M","RG","BD","RB","M","BK","RQ","RD","M","RB","BS","RK","M","BA","RQ","RH","M","RB","RA","BH","M","RK","BQ","RH","M","RS","RD","RQ","M","RG","BD","BB","M","BK","BQ","RD","M","RB","BH","BK","M","BA","BQ","RH","M","BB","RA","BH","M","RK","BQ","BH","M","BB","BD","RQ"];
            $this->reelStrips['Fs3Reels2']=["M","RK","RG","BQ","M","RB","RD","RG","M","RA","RB","BH","M","RD","RH","RK","M","BD","RA","RQ","M","RK","RB","RG","M","BB","RK","RQ","M","BK","RG","BQ","M","BB","RD","BG","M","BA","RB","BG","M","BD","RH","BK","M","BD","RA","BQ","M","RK","BB","BG","M","BA","BK","RQ"];
            $this->reelStrips['Fs3Reels3']=["M","RA","RH","RG","M","RD","RA","BH","M","RB","RD","RQ","M","BG","RB","RH","M","RK","RA","RG","M","RK","RD","BA","M","RH","RG","RQ","M","BA","BH","RG","M","RD","BA","BS","M","RB","BD","BQ","M","BG","BB","RH","M","BK","RA","BG","M","RK","RS","BA","M","BH","BG","RQ"];
            $this->reelStrips['Fs3Reels4']=["RQ","RQ","RQ","RQ","RK","RA","BH","RW","RW","RW","RW","RA","RQ","BD","BH","BH","RA","BB","BB","BB","RB","RB","BB","BH","BH","RG","RQ","RQ","RK","RH","RH","BH","RG","RQ","RQ","RD","RB","RB","RH","BG","BG","RK","RA","RA","RH","RH","RK","RK","RK","RK","RH","BB","BB","RQ","RQ","BG","BG","BG","BB","BH","BH","BH","RA","RA","RH","BG","BG","RK","RA","RA","RH","RH","BH","BH","BH","BH","RH","BB","BB","RQ","RQ","RK","RK","RK","BB","BH","BH","BH","RA","RA","BB","BH","BH","BH","BB","BB","BB","RQ","RQ","BH","BB","BB","BB","BH","BH","BH","BB"];
            $this->reelStrips['Fs3Reels5']=["BA","BA","BK","BQ","RH","RA","RG","BK","BK","BK","BK","BQ","BQ","BQ","BQ","BA","BA","RA","RH","BA","RA","BA","BK","BK","BK","BQ","BA","RH","RD","BK","RH","BQ","BQ","BA","BA","RD","BK","RQ","BA","BA","BA","RD","RD","RA","BK","BK","RG","RG","RG","RG","BK","RA","RA","RH","RB","RA","RK","RH","BK","BQ","BQ","BQ","BA","RQ"];
            $this->reelStrips['Base16Reels1']=["RA","RA","RA","RA","BD","BA","BG","RK","RK","RK","BQ","BQ","BQ","BQ","RA","RA","RK","BA","BH","RK","BA","RA","RQ","RQ","RQ","RA","RK","BH","BG","RK","BH","RQ","RQ","RA","RA","BD","RK","BQ","BG","RQ","RQ","BD","BD","BA","RA","RA","RQ","RQ","BG","RK","RA","BA","BA","BH","BB","BA","BK","BH","BG","RQ","RQ","RQ","BG","BQ"];
            $this->reelStrips['Base16Reels2']=["RB","BH","RB","BK","BB","BA","RH","RB","BG","BG","BA","BA","BA","RD","RH","RH","BA","RB","BA","BG","BD","BD","BB","BH","BH","RH","BB","BB","BD","BH","BH","RH","BG","BQ","BQ","BD","BB","BB","BH","RG","RG","BK","BA","BA","BH","BH","BK","BK","BK","BK","BH","RB","RB","BQ","BQ","BK","BK","BK","RB","RH","RH","RH","BA","BA"];
            $this->reelStrips['Base16Reels3']=["RB","RB","RD","RG","BG","BK","BG","BH","BH","BH","RD","RG","RD","RD","RH","RH","RB","RB","BK","BB","BB","RA","RB","RB","RG","RG","RG","BB","BB","BH","BH","RH","RH","RD","RD","BD","BG","RB","BB","BG","BA","BH","RD","BA","RG","RG","BB","BG","BG","BG","RH","RG","RG","BB","BB","BB","BK","BB","RH","RD","RD","RD","RB","BQ"];
            $this->reelStrips['Base16Reels4']=["BW","BW","BW","BW","RH","BK","RD","RH","RH","RH","BG","BG","RD","RD","RH","RH","RG","RG","BQ","RA","BB","BB","RB","RB","RB","RH","BK","BK","BK","BH","BH","RH","BB","RD","RD","BD","BG","RB","BB","BG","BA","BD","RD","BA","RG","RG","RB","RB","RB","BQ","BQ","RG","RG","BQ","BQ","BK","BK","BK","RH","RG","RD","RD","BB","BB"];
            $this->reelStrips['Base16Reels5']=["RG","RG","RG","RG","BD","BA","RH","RB","RG","BQ","BQ","BQ","BQ","BG","RH","RH","BA","BK","BQ","RA","BB","BB","RG","RG","BG","BK","BK","BK","BG","BH","BH","RH","BG","BQ","BQ","BD","BB","BB","RG","RG","RG","BD","BD","BA","RG","RG","BD","BD","BD","BK","BH","RB","BG","BB","BB","BK","BK","BK","RB","RD","RD","RD","BB","BB"];
            $this->reelStrips['Fs0Reels1']=["M","RG","BD","RB","M","BK","RQ","RD","M","RB","BS","RK","M","BA","RQ","RH","M","RB","RA","BH","M","RK","RS","RH","M","BQ","RW","RW","RW","RW","BD","BB","M","BK","BQ","RD","M","RB","BH","BK","M","BA","BQ","RH","M","BB","RA","BH","M","RK","BQ","BH","M","BB","BD","RQ","M","BK","BQ","BH","M","BB","BD","RQ","M","RK","BQ","BH","M","BK","BQ","BH","M","BB","BD","RQ","M","RK","BQ","BH","M","BK","BQ","BH","M","BB","BD","RQ","M","RK","BQ","BH","M","BK","BQ","BH","M","BB","BD","RQ","M","RK","BQ","BH"];
            $this->reelStrips['Fs0Reels2']=["M","RK","RG","BQ","M","RB","RD","RG","M","RA","RB","BH","M","RD","RH","RK","M","BD","RA","RQ","M","RK","RB","RG","M","BB","RK","RQ","M","BK","RG","BQ","M","BB","RD","BG","M","BA","RB","BG","M","BD","RH","BK","M","BD","RA","BQ","M","RK","BB","BG","M","BA","BK","RQ"];
            $this->reelStrips['Fs0Reels3']=["M","RA","RH","RG","M","RD","RA","BH","M","RB","RD","RQ","M","BG","RB","RH","M","RK","RA","RG","M","RK","RD","BA","M","RH","RG","RQ","M","BA","BH","RG","M","RD","BA","BS","M","RB","BD","BQ","M","BG","BB","RH","M","BK","RA","BG","M","RK","RS","BA","M","BH","BG","RQ"];
            $this->reelStrips['Fs0Reels4']=["RQ","RQ","RQ","RQ","RK","RA","BH","RG","RG","RG","RA","RA","RQ","BD","BH","BH","RA","BB","RA","RA","RB","RB","BB","RH","RH","RG","RQ","RQ","RK","RH","RH","BH","RG","RQ","RQ","RD","RB","RB","RH","BG","BG","RK","RA","RA","RH","RH","RK","RK","RK","RK","RH","BB","BB","RQ","RQ","RK","RK","RK","BB","BH","BH","BH","RA","RA"];
            $this->reelStrips['Fs0Reels5']=["BA","BA","BK","BQ","RH","RA","RG","BK","BK","BK","BK","BQ","BQ","BQ","BQ","BA","BA","RA","RH","BA","RA","BA","BK","BK","BK","BQ","BA","RH","RD","BK","RH","BQ","BQ","BA","BA","RD","BK","RQ","BA","BA","BA","RD","RD","RA","BK","BK","RG","RG","RG","RG","BK","RA","RA","RH","RB","RA","RK","RH","BK","BQ","BQ","BQ","BA","RQ"];
            $this->reelStrips['Base8Reels1']=["BD","RG","RG","RA","RQ","RA","BH","RB","RQ","BK","RB","BD","BK","BH"];
            $this->reelStrips['Base8Reels2']=["RK","BB","BG","BB","RK","RD","RH","RH","RD","BG","BA","BQ","BQ","BA"];
            $this->reelStrips['Base8Reels3']=["RQ","RG","BB","RA","BQ","RQ","BK","BH","BG","BA","RK","RK","BD","BQ","RH","BK","RD","RB","RW","RW","RW","RW"];
            $this->reelStrips['Base8Reels4']=["RQ","BH","RA","RA","RB","RG","RB","RQ","BD","BH","BD","BH","BK","BK","RA","BK","RG","RQ","RW","RW","RW","RW"];
            $this->reelStrips['Base8Reels5']=["BA","RH","RH","BA","RK","BQ","BA","RK","RD","RD","BQ","BB","BG","RH","BB","BG","RK","BQ","RW","RW","RW","RW"];
            $this->reelStrips['Base13Reels1']=["BD","BQ","BQ","BH","BH","BA","BG","BD","BB","RS","BG","BD","BB","BG","BD","BB","RS","BG","BD","BB","BQ","BG","BD","BB","BS","BG","BD","BB","BK","BG","BG","BD","BB","RS","BG","BD","BB","BG","BD","BB","RS","BG","BD","BB","BB","BB","BA","BK","BK","BQ","BG","BD","BB","RS","BG","BD","BB","BG","BD","BB","RS","BG","BD","BB","BB","BH","BA","BA","BB","BH","BA","BK","BD","BA","BA","BK","BB","BB","BK","BK","BK","BK","BD","BD","BQ","BQ","BQ","BG","BG","BD","BB","RS","BG","BD","BB","BA","BA","BA","BK","BG"];
            $this->reelStrips['Base13Reels2']=["BH","BH","BH","RD","BH","BH","RG","RB","RG","RD","RH","RD","RG","RB","RG","RB","RG","RG","RD","RB","RB","RB","RB","RB","RB","RD","RB","RG","RG","RG","RG","RD","RD","RD","BA","RH","RD","BK","BK","BK","RH","RB","BW","BW","BW","BW","RB","RG","RB","BQ","BQ","BQ","BK","RD","BA","BA","RD","RD","BK","RD","BQ","RD","RB","RB","RB","BH","BA","BA","RA","RA","RK","RK","BD","BA","BA","BK","BQ","BQ","BK","BK","BK","BK","RH","RH","RH","RA","RA","RA","BQ","BQ","BQ","BQ","BQ","BG","RK","RK","RK","RK","RQ","RQ","RQ","RQ","RA","RA","RA","RA","RQ"];
            $this->reelStrips['Base13Reels3']=["RG","RD","RB","BS","RG","RD","RB","RG","RD","RB","RS","RG","RD","RB","RG","RD","RB","RS","RG","RD","RB","BA","BA","BQ","BQ","BQ","RG","RD","RB","RS","RG","RD","RB","BA","BK","BA","BA","BA","RG","RD","RB","RS","RG","RD","RB","RB","RG","BQ","BQ","BQ","RD","BK","BK","BH","RB","RB","RG","RD","RB","RS","RG","RD","RB","RG","RD","RB","RS","RG","RD","RB","RB","RB","RG","RD","RB","RS","RG","RD","RB","RG","RD","RB","RS","RG","RD","RB","BH","BH","RB","RB","RG","RD","RB","RS","RG","RD","RB","RB","RB","RB","BK","BK","BK","BH","BH","RD","BQ"];
            $this->reelStrips['Base13Reels4']=["BK","BK","BK","BH","BH","BH","BH","RD","RD","BQ","BQ","BQ","BQ","BQ","BQ","BA","BA","BH","BH","BQ","BA","BK","BK","BK","BB","BB","BH","BK","BA","RB","BA","BA","BB","BB","RB","RB","RB","BH","BH","BH","RG","RG","BH","BH","BH","RB","BH","BH","BH","BH","BH","BH","BH","BH","BH","BA","BA","BA","BA","BA","BA","BA","BH","BB","BB","BB"];
            $this->reelStrips['Base13Reels5']=["BA","BA","RK","RQ","BG","BD","BB","RS","BG","BD","BB","BK","BK","BK","BQ","BA","BA","BD","BG","BD","BB","BS","BG","BD","BB","BD","BD","BA","BA","BA","BG","BD","BB","RS","BG","BD","BB","BG","BD","BB","RS","BG","BD","BB","BB","BB","BH","BH","BH","BK","BW","BW","BW","BW","RQ","RQ","RQ","BG","BD","BB","RS","BG","BD","BB","BG","BA","BH","BG","BD","BB","RS","BG","BD","BB","BG","BD","BB","RS","BG","BD","BB","BG","BD","BB","BS","BG","BD","BB","BQ","RA","BQ","BQ","BQ","RQ","RQ","BB","BB","BB","BG","BD","BB","RS","BG","BD","BB","BH","BH"];
            $this->reelStrips['Fs8Reels1']=["RA","RA","RK","RQ","BH","BA","BG","RK","RK","RK","RK","RQ","RQ","RQ","RQ","RA","RA","BA","BH","RA","BA","RA","RK","RK","RK","RQ","RA","BH","BD","RK","BH","RQ","RQ","RA","RA","BD","RK","BQ","RA","RA","RA","BD","BD","BA","RK","RK","BG","BG","BG","BG","RK","BA","BA","BH","BB","BA","BK","BH","RK","RQ","RQ","RQ","RA","BQ"];
            $this->reelStrips['Fs8Reels2']=["BQ","BQ","BQ","BQ","BK","BA","RH","BG","BG","BG","BA","BA","BQ","RD","RH","RH","BA","RB","BA","BA","BB","BB","RB","BH","BH","BG","BQ","BQ","BK","BH","BH","RH","BG","BQ","BQ","BD","BB","BB","BH","RG","RG","BK","BA","BA","BH","BH","BK","BK","BK","BK","BH","RB","RB","BQ","BQ","BK","BK","BK","RB","RH","RH","RH","BA","BA"];
            $this->reelStrips['Fs8Reels3']=["M","BA","BH","BG","M","BD","BA","RH","M","BB","BD","BQ","M","RG","BB","BH","M","BK","BA","BG","M","BK","BD","RA","M","BH","BG","BQ","M","RA","RH","BG","M","BD","RA","RS","M","BB","RD","RQ","M","RG","RB","BH","M","RK","BA","RG","M","BK","BS","RA","M","RH","RG","BQ"];
            $this->reelStrips['Fs8Reels4']=["M","BK","BG","RQ","M","BB","BD","BG","M","BA","BB","RH","M","BD","BH","BK","M","RD","BA","BQ","M","BK","BB","BG","M","RB","BK","BQ","M","RK","BG","RQ","M","RB","BD","RG","M","RA","BB","RG","M","RD","BH","RK","M","RD","BA","RQ","M","BK","RB","RG","M","RA","RK","BQ"];
            $this->reelStrips['Fs8Reels5']=["M","BG","RD","BB","M","RK","BQ","BD","M","BB","RS","BK","M","RA","BQ","BH","M","BB","BA","RH","M","BK","BS","BH","M","RQ","BW","BW","BW","BW","RD","RB","M","RK","RQ","BD","M","BB","RH","RK","M","RA","RQ","BH","M","RB","BA","RH","M","BK","RQ","RH","M","RB","RD","BQ","M","RK","RQ","RH","M","RB","RD","BQ","M","BK","RQ","RH","M","RK","RQ","RH","M","RB","RD","BQ","M","BK","RQ","RH","M","RK","RQ","RH","M","RB","RD","BQ","M","BK","RQ","RH","M","RK","RQ","RH","M","RB","RD","BQ","M","BK","RQ","RH"];
            $this->reelStrips['Fs2Reels1']=["M","RG","BD","RB","M","BK","RQ","RD","M","RB","BS","RK","M","BA","RQ","RH","M","RB","RA","BH","M","RK","BQ","RH","M","RS","RD","RQ","M","RG","BD","BB","M","BK","BQ","RD","M","RB","BH","BK","M","BA","BQ","RH","M","BB","RA","BH","M","RK","BQ","BH","M","BB","BD","RQ"];
            $this->reelStrips['Fs2Reels2']=["M","RK","RG","BQ","M","RB","RD","RG","M","RA","RB","BH","M","RD","RH","RK","M","BD","RA","RQ","M","RK","RB","RG","M","BB","RK","RQ","M","BK","RG","BQ","M","BB","RD","BG","M","BA","RB","BG","M","BD","RH","BK","M","BD","RA","BQ","M","RK","BB","BG","M","BA","BK","RQ"];
            $this->reelStrips['Fs2Reels3']=["M","RA","RW","RW","RW","RW","RA","BH","M","RB","RD","RQ","M","BG","RB","RH","M","RK","RA","RG","M","RK","RD","BA","M","RH","RG","RQ","M","BA","BH","RG","M","RD","BA","BS","M","RB","BD","BQ","M","BG","BB","RH","M","BK","RA","BG","M","RK","RS","BA","M","BH","BG","RQ","M","BH","BG","BQ","M","BH","BG","BQ","M","BH","BG","BQ","M","BH","BG","BQ","M","BH","BG","BQ","M","BH","BG","BQ","M","BH","BG","BQ","M","BA","BH","RG","M","RD","BA","BS","M","RB","BD","BQ","M","BG","BB","RH","M","BK","RA","BG"];
            $this->reelStrips['Fs2Reels4']=["RQ","RQ","RQ","RQ","RK","RA","BH","RG","RG","RG","RA","RA","RQ","BD","BH","BH","RA","BB","RA","RA","RB","RB","BB","RH","RH","RG","RQ","RQ","RK","RH","RH","BH","RG","RQ","RQ","RD","RB","RB","RH","BG","BG","RK","RA","RA","RH","RH","RK","RK","RK","RK","RH","BB","BB","RQ","RQ","RK","RK","RK","BB","BH","BH","BH","RA","RA"];
            $this->reelStrips['Fs2Reels5']=["BA","BA","BK","BQ","RH","RA","RG","BK","BK","BK","BK","BQ","BQ","BQ","BQ","BA","BA","RA","RH","BA","RA","BA","BK","BK","BK","BQ","BA","RH","RD","BK","RH","BQ","BQ","BA","BA","RD","BK","RQ","BA","BA","BA","RD","RD","RA","BK","BK","RG","RG","RG","RG","BK","RA","RA","RH","RB","RA","RK","RH","BK","BQ","BQ","BQ","BA","RQ"];
            $this->reelStrips['Base1Reels1']=["RW","RW","RW","RW","RQ","RQ","RA","RA","RA","RA","BD","BD","RB","RB","BH","RA","RA","RK","RQ","BA","RB","RB","RD","RD","BG","BG","RK","RK","RK","RH","RH","BH","BH","RQ","RQ","RD","RB","RB","BG","BG","BG","RQ","RQ","RA","RD","RD","RK","RK","RK","RK","RH","BB","BB","RD","RD","RK","RK","RK","RG","RB","RB","BD","RQ","RQ","RD","RD","RH","RH","RQ","RQ","RQ","RQ","RH","RH","BH","RA","RA","RK","RQ","BA","RB","RB","BB","BH","RG","BG","RK","RK","RK","RH","RH","RG","BH","RH","RH","RD","RB","RB","BG","BG","BG","RH","RH","RA","RG","BG","RK"];
            $this->reelStrips['Base1Reels2']=["RW","RW","RW","RW","RK","RK","RG","BH","BH","BH","BG","BD","BD","BD","BH","BH","BG","BG","RQ","BA","RB","RB","BB","BB","BB","BH","RK","RK","RK","RH","RH","BH","RB","BD","BD","RD","RG","BB","RB","RQ","RA","RD","BD","RA","BG","BG","BB","BB","BB","RQ","RQ","BG","BG","RQ","RQ","RK","RK","RK","BH","BG","BD","BD","RQ","RQ","RK","RK","RG","BH","BH","BH","BG","BD","BD","BD","BH","BH","BG","BG","RQ","BA","RB","RB","BB","BB","BB","BH","RK","RK","RK","RH","RH","BH","RB","BD","BD","RD","RG","BB","RB","RQ","RA","RD","BD","RA","BG","BG","BB"];
            $this->reelStrips['Base1Reels3']=["RG","RG","RG","RG","BH","RK","RD","RD","RD","BH","BD","BG","BD","BD","BH","BH","BB","BB","RK","RB","RB","RB","BB","BB","RG","RG","RG","BB","BB","RH","RB","BH","BH","BD","BD","RD","RD","RD","RB","BH","RA","RB","BD","RA","BG","BG","RD","RD","RD","RD","BH","BG","BG","RB","RB","RB","RK","RB","BH","BD","BD","BD","BB","RQ"];
            $this->reelStrips['Base1Reels4']=["RK","RK","RK","RK","RH","RA","BH","RH","RH","RH","RA","RA","RA","BD","RH","BH","RA","BB","RA","RA","RB","BB","BB","RH","RH","RG","RK","RK","BB","RH","RH","BH","RD","RQ","RQ","RD","BB","BB","RH","BG","BG","RB","BH","RH","RH","BH","BB","BB","RB","BB","RH","BB","BB","RG","RH","BH","RH","RD","BB","RH","BH","RH","RA","RA"];
            $this->reelStrips['Base1Reels5']=["BA","BA","BK","BQ","RG","RA","RA","BK","BK","BK","BK","BQ","BQ","BQ","BQ","BA","BA","RA","RD","BA","RA","BA","BK","BK","BK","BQ","BA","RH","BK","BK","RB","BQ","BQ","BA","BA","BK","BK","RQ","BA","BA","BA","BK","RK","BK","BK","BK","RK","RK","RK","RK","BK","RA","RA","RK","RK","RA","RK","RH","BK","BQ","BQ","BQ","BA","RQ"];
            $this->reelStrips['Fs11Reels1']=["RA","RA","RK","RQ","BH","BA","BG","RK","RK","RK","RK","RQ","RQ","RQ","RQ","RA","RA","BA","BH","RA","BA","RA","RK","RK","RK","RQ","RA","BH","BD","RK","BH","RQ","RQ","RA","RA","BD","RK","BQ","RA","RA","RA","BD","BD","BA","RK","RK","BG","BG","BG","BG","RK","BA","BA","BH","BB","BA","BK","BH","RK","RQ","RQ","RQ","RA","BQ"];
            $this->reelStrips['Fs11Reels2']=["BQ","BQ","BQ","BQ","BK","BA","RH","BW","BW","BW","BW","BA","BQ","RD","RH","RH","BA","RB","RB","RB","BB","BB","RB","RH","RH","BG","BQ","BQ","BK","BH","BH","RH","BG","BQ","BQ","BD","BB","BB","BH","RG","RG","BK","BA","BA","BH","BH","BK","BK","BK","BK","BH","RB","RB","BQ","BQ","RG","RG","RG","RB","RH","RH","RH","BA","BA","BH","RG","RG","BK","BA","BA","BH","BH","RH","RH","RH","RH","BH","RB","RB","BQ","BQ","BK","BK","BK","RB","RH","RH","RH","BA","BA","RB","RH","RH","RH","RB","RB","RB","BQ","BQ","RH","RB","RB","RB","RH","RH","RH","RB"];
            $this->reelStrips['Fs11Reels3']=["M","BA","BH","BG","M","BD","BA","RH","M","BB","BD","BQ","M","RG","BB","BH","M","BK","BA","BG","M","BK","BD","RA","M","BH","BG","BQ","M","RA","RH","BG","M","BD","RA","RS","M","BB","RD","RQ","M","RG","RB","BH","M","RK","BA","RG","M","BK","BS","RA","M","RH","RG","BQ"];
            $this->reelStrips['Fs11Reels4']=["M","BK","BG","RQ","M","BB","BD","BG","M","BA","BB","RH","M","BD","BH","BK","M","RD","BA","BQ","M","BK","BB","BG","M","RB","BK","BQ","M","RK","BG","RQ","M","RB","BD","RG","M","RA","BB","RG","M","RD","BH","RK","M","RD","BA","RQ","M","BK","RB","RG","M","RA","RK","BQ"];
            $this->reelStrips['Fs11Reels5']=["M","BG","RD","BB","M","RK","BQ","BD","M","BB","RS","BK","M","RA","BQ","BH","M","BB","BA","RH","M","BK","RQ","BH","M","BS","BD","BQ","M","BG","RD","RB","M","RK","RQ","BD","M","BB","RH","RK","M","RA","RQ","BH","M","RB","BA","RH","M","BK","RQ","RH","M","RB","RD","BQ"];
            $this->reelStrips['Fs5Reels1']=["RW","RW","RW","RW","M","BK","RQ","RD","M","RB","BH","RK","M","BA","RQ","RH","M","RB","RA","BH","M","RK","BQ","RH","M","BB","RD","RQ","M","RG","BD","BB","M","BK","BQ","RD","M","RB","BH","BK","M","BA","BQ","RH","M","BB","RA","BH","M","RK","BQ","BH","M","BB","BD","RQ"];
            $this->reelStrips['Fs5Reels2']=["M","RK","RG","BQ","RW","RW","RW","RW","M","RA","RB","BG","M","RD","RH","RK","M","BB","RA","RQ","M","RK","RB","RG","M","BA","RK","RQ","M","BK","RG","BQ","M","BB","RD","BG","M","BA","RB","BG","M","BG","RH","BK","M","BD","RA","BQ","M","RK","BD","BG","M","BA","BK","RQ"];
            $this->reelStrips['Fs5Reels3']=["M","RA","RH","RG","M","RD","RA","BH","M","RD","RA","BH","M","BG","RB","RH","M","RK","RA","RG","M","RK","RD","BA","M","RH","RG","RQ","M","BA","BH","RG","M","RD","BA","BH","M","RB","BD","BQ","M","RS","BB","RH","M","BK","RA","BG","M","RK","BS","BA","M","BH","BG","RQ"];
            $this->reelStrips['Fs5Reels4']=["BD","RQ","RQ","RQ","RK","BB","BH","RG","RG","RG","RA","RA","RQ","BD","BD","BH","RA","BB","RA","RA","RB","RB","BB","RH","RH","RG","RQ","RQ","RK","RH","RH","BH","RG","RQ","RQ","RD","RB","RB","RH","BG","BG","RK","RA","RA","RH","RH","RW","RW","RW","RW","RH","BB","BB","RQ","RQ","RK","RK","RK","BB","BH","BH","BH","RA","BB"];
            $this->reelStrips['Fs5Reels5']=["BA","BA","BK","BQ","RK","RA","RG","BK","BK","BK","BK","BQ","BQ","BQ","RS","BA","BA","RA","RH","BA","RA","BA","BK","BK","BK","BQ","BA","RH","RD","BK","RH","BQ","BQ","BA","BA","RD","BK","RQ","BA","BA","BA","RD","RD","RA","BK","BK","RW","RW","RW","RW","BK","RA","RA","RH","RB","RA","RK","RH","BK","BQ","BS","BQ","BA","RQ"];
            $this->reelStrips['Fs14Reels1']=["RA","RA","RK","RQ","BK","BA","BG","RK","RK","RK","RK","RQ","RQ","RQ","BS","RA","RA","BA","BH","RA","BA","RA","RK","RK","RK","RQ","RA","BH","BD","RK","BH","RQ","RQ","RA","RA","BD","RK","BQ","RA","RA","RA","BD","BD","BA","RK","RK","BW","BW","BW","BW","RK","BA","BA","BH","BB","BA","BK","BH","RK","RQ","RS","RQ","RA","BQ"];
            $this->reelStrips['Fs14Reels2']=["RD","BQ","BQ","BQ","BK","RB","RH","BG","BG","BG","BA","BA","BQ","RD","RD","RH","BA","RB","BA","BA","BB","BB","RB","BH","BH","BG","BQ","BQ","BK","BH","BH","RH","BG","BQ","BQ","BD","BB","BB","BH","RG","RG","BK","BA","BA","BH","BH","BQ","BQ","BK","BK","BH","RB","RB","BQ","BQ","BK","BK","BK","RB","RH","RH","RH","BA","RB"];
            $this->reelStrips['Fs14Reels3']=["M","BA","BH","BG","M","BD","BA","RH","BW","BW","BW","BW","M","RG","BB","BH","M","BK","BA","BG","M","BK","BD","RA","M","BH","BG","BQ","M","RA","RH","BG","M","BD","RA","RH","M","BB","RD","RQ","M","BS","RB","BH","M","RK","BA","RG","M","BK","RS","RA","M","RH","RG","BQ"];
            $this->reelStrips['Fs14Reels4']=["M","BK","BG","RQ","BW","BW","BW","BW","M","BA","BB","RG","M","BD","BH","BK","M","RB","BA","BQ","M","BK","BB","BG","M","RA","BK","BQ","M","RK","BG","RQ","M","RB","BD","RG","M","RA","BB","RG","M","RG","BH","RK","M","RD","BA","RQ","M","BK","RD","RG","M","RA","RK","BQ"];
            $this->reelStrips['Fs14Reels5']=["BW","BW","BW","BW","M","RK","BQ","BD","M","BB","RH","BK","M","RA","BQ","BH","M","BB","BA","RH","M","BK","RQ","BH","M","RB","BD","BQ","M","BG","RD","RB","M","RK","RQ","BD","M","BB","RH","RK","M","RA","RQ","BH","M","RB","BA","RH","M","BK","RQ","RH","M","RB","RD","BQ"];
            $this->reelStrips['Base7Reels1']=["BH","RG","RK","RQ","RG","RG","RQ","RK","RG","RH","RG","RQ","BD","RB","BG","RG","RK","RQ","BG","RK","RQ","RB","RK","RH","RK","BD","RA","BH","RQ","RD","BG","BA","RB","RQ","RG","RG","RQ","RG","RA","RD","BG","RK","RH","BG","RB","RQ","RD","BB","RD","RK","RQ","RA","RQ","RD","RG","RQ","RG","BH","RA","BA","RQ","RK","RG","RK","BH","RQ","BH","RH","RH","RW","RW","RW","RW","RQ","RB","RK","BB","RA","BD","RQ","RG","RK","RK","BG","RB","RD","RG","RK","RQ","BG","RA","BG","RQ","RG","RQ","RQ","RQ","BG","RB","BB","BH","RK","RK","RB","RG","BG","RQ"];
            $this->reelStrips['Base7Reels2']=["RK","RQ","BG","RD","BG","BD","RK","RK","RD","RQ","RQ","RK","BH","RK","BB","BD","BH","RB","BH","BG","RA","BH","BH","RD","RG","RB","BG","RK","BD","BH","RH","RA","BB","BG","BB","BD","RA","BB","RB","BD","RQ","BB","RK","RB","BD","BA","BD","BD","RD","BH","RK","RB","BH","BB","RB","RK","BD","RB","BB","BG","RW","RW","RW","RW","RQ","BH","RK","RQ","RB","RG","RK","RQ","BD","BG","BB","BH","BH","BH","BG","RG","BB","BD","RH","BB","BA","RA","RG","BH","RH","BG","BD","BB","RK","BH","BG","RQ","BB","BH","BG","BD","BG","RQ","RH","RK","BG","BD","RQ"];
            $this->reelStrips['Base7Reels3']=["BD","BB","BG","RB","BH","BD","RB","BH","RD","BH","BG","RG","BA","BG","BG","BH","BH","BG","RA","BB","RA","RG","BD","RB","RK","BG","BD","BH","BG","BA","BD","RW","RW","RW","RW","BB","BG","BB","RK","BB","BH","RB","BH","RH","BH","RH","BB","RK","BH","BD","BH","RA","RH","RB","BH","BD","RB","BB","RG","BD","BG","RG","RG","BH","BD","BH","RB","RQ","RB","BD","BD","BH","RB","BH","BD","BD","BB","BD","RH","RK","RB","BG","BB","BB","RH","BD","RB","BG","RD","RA","RK","BG","RB","BH","BH","BH","BH","RH","BB","BG","BB","RG","BB","BB","RG","BG","BB"];
            $this->reelStrips['Base7Reels4']=["RK","RG","RB","RA","BD","RB","RA","RK","BG","RK","RK","RA","RA","RK","RG","RA","RK","RG","RK","RA","BH","RH","RK","RB","RA","RH","RA","RG","RA","RH","RK","RQ","RA","RA","RH","RH","RQ","RA","RH","BB","BB","RK","BB","RH","RB","BH","BD","BH","RK","BB","BG","BH","BG","RG","BB","RQ","RQ","RG","RD","RA","RK","RQ","BH","RH","RQ","RA","RD","RG","RK","BH","BH","RK","RB","RH","BB","RA","RA","RA","RK","RG","RA","RK","RH","BH","RB","BG","RH","RB","BH","RW","RW","RW","RW","RH","RH","RH","BH","BB","RB","RG","RA","BH","RK","RH","RA","RG","RK"];
            $this->reelStrips['Base7Reels5']=["BA","BA","BA","RH","BK","RG","BK","BQ","BA","BK","BQ","BQ","RA","RA","RA","RG","RK","BA","BA","RA","RD","BK","RA","BQ","BQ","RD","BK","BQ","BA","BA","RD","BK","BK","BK","RH","RH","RA","RQ","BK","BA","RH","BA","BK","BK","RD","RG","BQ","RD","BQ","BQ","RQ","RG","BA","BQ","BA","BK","BQ","BK","RA","RA","BA","BK","RQ","BK","RH","BQ","BK","RH","RH","BK","BK","RB","BK","BK","BK","RG","BQ","RD","BA","BQ","RH","RH","RA","BK","RH","BA","BA","BQ","BK","BK","BA","RD","RA","BA","RW","RW","RW","RW","RA","BQ","RD","RG","BA","BA","BA","RG","BQ"];            
            $this->reelStrips['Base14Reels1']=["RB","BG","BH","BD","RA","RA","RK","RK","RB","BK","BD","BB","BA","BG","BD","RB","RA","RQ"];
            $this->reelStrips['Base14Reels2']=["RH","BG","RG","BH","RA","RK","RQ","RG","BK","RB","BD","RG","BA","BG","RD","RQ","RH","RK"];
            $this->reelStrips['Base14Reels3']=["BH","BA","BK","BQ","BH","BA","BK","BQ","BH","BA","BK","BQ","BH","BK","BQ","BB","BA","RG"];
            $this->reelStrips['Base14Reels4']=["BQ","BK","BA","BH","BQ","BK","BA","BH","BQ","BK","BA","BH","BQ","BK","BA","BA","RB","BB"];
            $this->reelStrips['Base14Reels5']=["BH","BG","BQ","BD","BA","BG","RK","BD","BK","BG","RA","BQ","RH","BG","RB","BD","RG","BB"];
            $this->reelStrips['Fs9Reels1']=["RA","RA","RK","RQ","BH","BA","BG","RK","RK","RK","RK","RQ","RQ","RQ","RQ","RA","RA","BA","BH","RA","BA","RA","RK","RK","RK","RQ","RA","BH","BD","RK","BH","RQ","RQ","RA","RA","BD","RK","BQ","RA","RA","RA","BD","BD","BA","RK","RK","BG","BG","BG","BG","RK","BA","BA","BH","BB","BA","BK","BH","RK","RQ","RQ","RQ","RA","BQ"];
            $this->reelStrips['Fs9Reels2']=["BQ","BQ","BQ","BQ","BK","BA","RH","BG","BG","BG","BA","BA","BQ","RD","RH","RH","BA","RB","BA","BA","BB","BB","RB","BH","BH","BG","BQ","BQ","BK","BH","BH","RH","BG","BQ","BQ","BD","BB","BB","BH","RG","RG","BK","BA","BA","BH","BH","BK","BK","BK","BK","BH","RB","RB","BQ","BQ","BK","BK","BK","RB","RH","RH","RH","BA","BA"];
            $this->reelStrips['Fs9Reels3']=["M","BA","BH","BG","M","BD","BA","RH","M","BB","BD","BQ","M","RG","BB","BH","M","BK","BA","BG","M","BK","BD","RA","M","BH","BG","BQ","M","RA","RH","BG","M","BD","RA","RS","M","BB","RD","RQ","M","RG","RB","BH","M","RK","BA","RG","M","BK","BS","RA","M","RH","RG","BQ"];
            $this->reelStrips['Fs9Reels4']=["M","BK","BG","RQ","BW","BW","BW","BW","M","BA","RB","RH","M","RD","RH","BK","M","RD","RA","BQ","M","BK","BB","RG","M","RB","BK","BQ","M","RK","BG","RQ","M","RB","BD","RG","M","RA","BB","RG","M","RD","BH","RK","M","RD","BA","RQ","M","BK","RB","RG","M","RA","RK","BQ","M","RK","RB","RG","M","RA","RK","RQ","M","BK","RB","RG","M","RA","RK","BQ","M","RK","RB","RG","M","RA","RK","BQ","M","RK","RB","RG","M","RA","RK","RQ","M","BK","RB","RG","M","RA","RK","BQ","M","RK","RB","RG","M","RA","RK","BQ"];
            $this->reelStrips['Fs9Reels5']=["M","BG","RD","BB","M","RK","BQ","BD","M","BB","RS","BK","M","RA","BQ","BH","M","BB","BA","RH","M","BK","RQ","BH","M","BS","BD","BQ","M","BG","RD","RB","M","RK","RQ","BD","M","BB","RH","RK","M","RA","RQ","BH","M","RB","BA","RH","M","BK","RQ","RH","M","RB","RD","BQ"];
            $this->reelStrips['Base4Reels1']=["RH","RG","RQ","RD","RA","RG","BK","RD","RK","RG","BA","RQ","BH","RG","BB","RD","BG","RB"];
            $this->reelStrips['Base4Reels2']=["RQ","RK","RA","RH","RQ","RK","RA","RH","RQ","RK","RA","RH","RQ","RK","RA","RA","BB","RB"];
            $this->reelStrips['Base4Reels3']=["RH","RA","RK","RQ","RH","RA","RK","RQ","RH","RA","RK","RQ","RH","RK","RQ","RB","RA","BG"];
            $this->reelStrips['Base4Reels4']=["BH","RG","BG","RH","BA","BK","BQ","BG","RK","BB","RD","BG","RA","RG","BD","BQ","BH","BK"];
            $this->reelStrips['Base4Reels5']=["BB","RG","RH","RD","BA","BA","BK","BK","BB","RK","RD","RB","RA","RG","RD","BB","BA","BQ"];
            $this->reelStrips['Base17Reels1']=["RA","RA","RA","BH","RK","BG","RK","RQ","RA","RK","RQ","RQ","BA","BA","BA","BG","BK","RA","RA","BA","BD","RK","BA","RQ","RQ","BD","RK","RQ","RA","RA","BD","RK","RK","RK","BH","BH","BA","BQ","RK","RA","BH","RA","RK","RK","BD","BG","RQ","BD","RQ","RQ","BQ","BG","RA","RQ","RA","RK","RQ","RK","BA","BA","RA","RK","BQ","RK","BH","RQ","RK","BH","BH","RK","RK","BB","RK","RK","RK","BG","RQ","BD","RA","RQ","BH","BH","BA","RK","BH","RA","RA","RQ","RK","RK","RA","BD","BA","RA","BW","BW","BW","BW","BA","RQ","BD","BG","RA","RA","RA","BG","RQ"];
            $this->reelStrips['Base17Reels2']=["BK","BG","BB","BA","RD","BB","BA","BK","RG","BK","BK","BA","BA","BK","BG","BA","BK","BG","BK","BA","RH","BH","BK","BB","BA","BH","BA","BG","BA","BH","BK","BQ","BA","BA","BH","BH","BQ","BA","BH","RB","RB","BK","RB","BH","BB","RH","RD","RH","BK","RB","RG","RH","RG","BG","RB","BQ","BQ","BG","BD","BA","BK","BQ","RH","BH","BQ","BA","BD","BG","BK","RH","RH","BK","BB","BH","RB","BA","BA","BA","BK","BG","BA","BK","BH","RH","BB","RG","BH","BB","RH","BW","BW","BW","BW","BH","BH","BH","RH","RB","BB","BG","BA","RH","BK","BH","BA","BG","BK"];
            $this->reelStrips['Base17Reels3']=["RD","RB","RG","BB","RH","RD","BB","RH","BD","RH","RG","BG","RA","RG","RG","RH","RH","RG","BA","RB","BA","BG","RD","BB","BK","RG","RD","RH","RG","RA","RD","BW","BW","BW","BW","RB","RG","RB","BK","RB","RH","BB","RH","BH","RH","BH","RB","BK","RH","RD","RH","BA","BH","BB","RH","RD","BB","RB","BG","RD","RG","BG","BG","RH","RD","RH","BB","BQ","BB","RD","RD","RH","BB","RH","RD","RD","RB","RD","BH","BK","BB","RG","RB","RB","BH","RD","BB","RG","BD","BA","BK","RG","BB","RH","RH","RH","RH","BH","RB","RG","RB","BG","RB","RB","BG","RG","RB"];
            $this->reelStrips['Base17Reels4']=["BK","BQ","RG","BD","RG","RD","BK","BK","BD","BQ","BQ","BK","RH","BK","RB","RD","RH","BB","RH","RG","BA","RH","RH","BD","BG","BB","RG","BK","RD","RH","BH","BA","RB","RG","RB","RD","BA","RB","BB","RD","BQ","RB","BK","BB","RD","RA","RD","RD","BD","RH","BK","BB","RH","RB","BB","BK","RD","BB","RB","RG","BW","BW","BW","BW","BQ","RH","BK","BQ","BB","BG","BK","BQ","RD","RG","RB","RH","RH","RH","RG","BG","RB","RD","BH","RB","RA","BA","BG","RH","BH","RG","RD","RB","BK","RH","RG","BQ","RB","RH","RG","RD","RG","BQ","BH","BK","RG","RD","BQ"];
            $this->reelStrips['Base17Reels5']=["RH","BG","BK","BQ","BG","BG","BQ","BK","BG","BH","BG","BQ","RD","BB","RG","BG","BK","BQ","RG","BK","BQ","BB","BK","BH","BK","RD","BA","RH","BQ","BD","RG","RA","BB","BQ","BG","BG","BQ","BG","BA","BD","RG","BK","BH","RG","BB","BQ","BD","RB","BD","BK","BQ","BA","BQ","BD","BG","BQ","BG","RH","BA","RA","BQ","BK","BG","BK","RH","BQ","RH","BH","BH","BW","BW","BW","BW","BQ","BB","BK","RB","BA","RD","BQ","BG","BK","BK","RG","BB","BD","BG","BK","BQ","RG","BA","RG","BQ","BG","BQ","BQ","BQ","RG","BB","RB","RH","BK","BK","BB","BG","RG","BQ"];

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
            $this->SymbolGame = ["RS","RW","RG","RD","RB","RH","RA","RK","RQ","BS","BW","BG","BD","BB","BH","BA","BK","BQ"];
            $this->SymbolGameNoScatter = ["RW","RG","RD","RB","RH","RA","RK","RQ","BW","BG","BD","BB","BH","BA","BK","BQ"];
            $this->SymbolGameR = ["RW","RG","RD","RB","RH","RA","RK","RQ"];            
            $this->SymbolGameB = ["BW","BG","BD","BB","BH","BA","BK","BQ"];
            
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
            //     $gameBet = $sum / $prc * 100;
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
            //     if($winLimit >= $this->AllBet * 50)
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
            for( $i = 0; $i < count($rp) - 3; $i++ ) 
            {
                if( $rp[$i] == 'BS' || $rp[$i] == 'RS' ) 
                {
                    if( isset($rp[$i + 1]) && isset($rp[$i - 1]) ) 
                    {
                        array_push($rpResult, $i);
                    }
                    if( isset($rp[$i - 1]) && isset($rp[$i - 2]) ) 
                    {
                        array_push($rpResult, $i - 1);
                    }
                    if( isset($rp[$i + 1]) && isset($rp[$i + 3]) ) 
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

        public function GetGuaranteedReelStrips($reelName)
        {
            $temp = file(base_path() . '/app/Games/ZeusVSThorYGG/guaranteed_reels.txt');
            $arr = json_decode($temp);
            $randIndex = rand(0, count($arr[$reelName]));
            $rpos = $arr[$reelName][$randIndex];
            $reels = [];
            $reels['rp'] = $rpos;
            for($i = 0; $i < 6; $i++)
            {
                $reels['reel'.($i+1)] = array_slice($this->reelStrips[$reelName.($i+1)], $rpos[$i] - 1, 4);
            }
            return $reels;
        }

        public function GetReelStrips($winType, $reelName, $reelIndex)
        {
            $reelName = $reelName . $reelIndex . 'Reels';
            // $reelName = "Base13Reels";
            $arrReels = [
                $reelName.'1', 
                $reelName.'2', 
                $reelName.'3', 
                $reelName.'4', 
                $reelName.'5',                 
            ];
            
            $prs = [];
            if($winType != 'bonus')
            {
                if($winType == 'win')
                {
                    $sym = $this->SymbolGameNoScatter[rand(0, 14)];
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

                $scattersCnt = rand(3, count($reelsId));
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

            if(strpos($reelName, "Fs") !== false)
            {
                //this means feature reel
                $commonSymbol = '';
                if($reelIndex < 8)
                {
                    $symbolCnt = count($this->SymbolGameR);
                    $commonSymbol = $this->SymbolGameR[rand(0, $symbolCnt - 1)];
                }
                else
                {
                    $symbolCnt = count($this->SymbolGameB);
                    $commonSymbol = $this->SymbolGameB[rand(0, $symbolCnt - 1)];
                }
                
                $minCnt = 3;
                if(in_array($commonSymbol, $this->highSymbol))
                {
                    $minCnt = 2;
                }

                $commonSymbolCnt = rand($minCnt, 5);
                if(in_array($commonSymbol, $this->SymbolGameR))
                {
                    $placedCnt = 0;
                    //for red symbols
                    for($r = 1; $r <= 5; $r++)
                    {
                        for($c = 0; $c < 4; $c++)
                        {
                            if($reel['reel'.$r][$c] == 'M')
                            {
                                if($placedCnt < $commonSymbolCnt)
                                {
                                    $reel['reel'.$r][$c] = $commonSymbol;
                                    $placedCnt++;
                                }
                                else
                                {
                                    $reel['reel'.$r][$c] = $this->SymbolGameNoScatter[rand(0, $symbolCnt - 1)];
                                }
                            }
                        }
                    }
                }
                else
                {
                    //for blue symbols
                    $placedCnt = 0;
                    //for red symbols
                    for($r = 5; $r >= 1; $r--)
                    {
                        for($c = 0; $c < 4; $c++)
                        {
                            if($reel['reel'.$r][$c] == 'M')
                            {
                                if($placedCnt < $commonSymbolCnt)
                                {
                                    $reel['reel'.$r][$c] = $commonSymbol;
                                    $placedCnt++;
                                }
                                else
                                {
                                    $reel['reel'.$r][$c] = $this->SymbolGameNoScatter[rand(0, $symbolCnt - 1)];
                                }
                            }
                        }
                    }
                }
                
            }
            return $reel;
        }

        public function GetNoWin()
        {
            $response = '{"code":0,"data":{"wager":{"wagerid":"2112150359110100013","timestamp":1639540751240,"status":"Finished","bets":[{"step":1,"betamount":"10.00","betcurrency":"USD","wonamount":"0.00","status":"RESULTED","betdata":{"lines":"1111111111","coin":"1.00","nCoins":1,"cheat":null,"clientData":null,"initialBalance":"10656.00"},"eventdata":{"accC":0,"accWa":"0.00","reelSet":"Reels","reels":[["GRAPES","LEMON","LEMON"],["CHERRY","CHERRY","CHERRY"],["LEMON","LEMON","LEMON"],["BELL","LEMON","LEMON"],["BELL","BELL","SEVEN"]],"rpos":[38,60,3,20,54],"wonCoins":0,"wts":["00000000000","000000000000000"],"wtw":[]},"prizes":null,"prepaid":false}],"prepaid":false},"buyBal":{"cash":"10646.00"},"resultBal":{"cash":"10646.00"},"obj":null,"cashRace":{"hasWon":false,"prize":null,"initialPrize":null,"currency":null,"resource":null},"missionState":null},"fn":"play","utcts":'.time().'}';                             
            return $response;
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
                [ $reels['reel5'][0], $reels['reel5'][1], $reels['reel5'][2], $reels['reel5'][3]]
            ];
        }

        public function GetNoWinSpin($color, $slotEvent)
        {
            $isWin = true;            
            
            $symbolGame = $this->SymbolGame;
            $scatterR = "RS"; 
            $scatterB = "BS";
            $reelName = "Base";
            $reelIndex = rand(0, 19);   
            while($isWin)
            {
                
                $reels = $this->GetReelStrips('none', $reelName, $reelIndex);
                $win = 0;
                
                for( $k = 0; $k < count($symbolGame); $k++ ) 
                {
                    $csym = $symbolGame[$k];
                    $wild = "RW";
                    if(in_array($csym, $this->SymbolGameB))
                        $wild = "BW";
                    if( $csym == $scatterR || $csym == $scatterB || !isset($this->Paytable[$csym]))
                    {

                    }
                    else
                    {
                        if( (in_array($csym,$reels['reel1']) || in_array($wild, $reels['reel1'])) &&
                            (in_array($csym,$reels['reel2']) || in_array($wild, $reels['reel2'])) ) 
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
                        
                        if( (in_array($csym,$reels['reel5']) || in_array($wild, $reels['reel5'])) &&
                        (in_array($csym,$reels['reel4']) || in_array($wild, $reels['reel4'])) ) 
                        {
                            //2 symbols match
                            $win += $this->Paytable[$csym][2];                            
                        }
                        if( (in_array($csym,$reels['reel5']) || in_array($wild, $reels['reel5'])) &&
                            (in_array($csym,$reels['reel4']) || in_array($wild, $reels['reel4'])) &&
                            (in_array($csym,$reels['reel3']) || in_array($wild, $reels['reel3']))) 
                        {
                            //3 symbols match
                            $win += $this->Paytable[$csym][3];                            
                        }
                        if( (in_array($csym,$reels['reel5']) || in_array($wild, $reels['reel5'])) &&
                            (in_array($csym,$reels['reel4']) || in_array($wild, $reels['reel4'])) &&
                            (in_array($csym,$reels['reel3']) || in_array($wild, $reels['reel3'])) &&
                            (in_array($csym,$reels['reel2']) || in_array($wild, $reels['reel2']))) 
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
                    }
                }

                if($win == 0)
                {
                    //calc scatter syms
                    $scatterB = 0;
                    $scatterR = 0;
                    for( $r = 1; $r <= 5; $r++ ) 
                    {
                        for( $p = 0; $p <= 3; $p++ ) 
                        {
                            if( $reels['reel' . $r][$p] == "BS" )
                            {
                                $scatterB++;
                            }
                            else if( $reels['reel' . $r][$p] == "RS" )
                            {
                                $scatterR++;
                            }
                        }
                    }    
                    if($scatterB + $scatterR < 3)
                        $isWin = false;
                }
            }
            return $reels;
        }         
    }

}
