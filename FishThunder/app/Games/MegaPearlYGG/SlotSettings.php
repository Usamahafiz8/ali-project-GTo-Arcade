<?php 
namespace VanguardLTE\Games\MegaPearlYGG
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
        public $highSymbols = null;
        public $reelLayout = null;
        
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

            $this->reelStrips['reel2_0'] = ["B","B","Q","Q","B","B","J","J","B","B","D","D","E","E","C","C","J","J","B","B","Q","Q","A","A","Q","Q","E","E","Q","Q","C","C","D","D","J","J","D","D","C","C","K","K","A","A","C","C","B","B","K","K","C","C","A","A","K","K","B","B","J","J","K","K","A","A","K","K","C","C","J","J","E","E","Q","Q","K","K","D","D","B","B","K","K","J","J","A","A","B","B","E","E","A","A","Q","Q","B","B","J","J","D","D","A","A","E","E","Q","Q","K","K","D","D","C","C","D","D","E","E","C","C","J","J","S","S","D","D","K","K","A","A","D","D","C","C","E","E","A","A","Q","Q","C","C","B","B","K","K","E","E","Q","Q","J","J","D","D","J","J","Z","Z","Q","Q","E","E","A","A","E","E"];
            $this->reelStrips['reel2_N'] = ["D","D","K","K","B","B","K","K","J","J","D","D","B","B","C","C","Q","Q","D","D","J","J","D","D","C","C","D","D","J","J","Q","Q","J","J","E","E","B","B","A","A","K","K","B","B","J","J","C","C","B","B","D","D","A","A","E","E","C","C","J","J","B","B","E","E","Q","Q","J","J","E","E","J","J","D","D","K","K","E","E","B","B","D","D","C","C","Z","Z","A","A","C","C","K","K","E","E","Q","Q","B","B","K","K","A","A","Q","Q","K","K","A","A","Q","Q","A","A","B","B","A","A","Q","Q","C","C","E","E","A","A","J","J","D","D","E","E","K","K","D","D","E","E","J","J","Q","Q","A","A","E","E","B","B","S","S","C","C","K","K","C","C","Q","Q","A","A","K","K","C","C","Q","Q"];
            $this->reelStrips['reel3_0'] = ["C","C","C","A","A","A","K","K","K","J","J","J","C","C","C","A","A","A","J","J","J","D","D","D","C","C","C","Q","Q","Q","E","E","E","S","S","S","A","A","A","K","K","K","B","B","B","E","E","E","K","K","K","A","A","A","J","J","J","K","K","K","Q","Q","Q","C","C","C","K","K","K","E","E","E","K","K","K","A","A","A","E","E","E","D","D","D","Q","Q","Q","A","A","A","E","E","E","C","C","C","D","D","D","J","J","J","B","B","B","K","K","K","A","A","A","E","E","E","Q","Q","Q","K","K","K","Q","Q","Q","C","C","C","E","E","E","J","J","J","K","K","K","C","C","C","E","E","E","C","C","C","D","D","D","J","J","J","B","B","B","Q","Q","Q","J","J","J","B","B","B","K","K","K","D","D","D","J","J","J","D","D","D","J","J","J","Z","Z","Z","Q","Q","Q","B","B","B","E","E","E","B","B","B","A","A","A","J","J","J","D","D","D","C","C","C","B","B","B","Q","Q","Q","B","B","B","A","A","A","Q","Q","Q","A","A","A","D","D","D","E","E","E","B","B","B","D","D","D","B","B","B","C","C","C","Q","Q","Q","D","D","D"];
            $this->reelStrips['reel3_N'] = ["C","C","C","B","B","B","Q","Q","Q","J","J","J","E","E","E","B","B","B","S","S","S","E","E","E","A","A","A","C","C","C","E","E","E","J","J","J","K","K","K","D","D","D","Q","Q","Q","C","C","C","B","B","B","Q","Q","Q","J","J","J","Q","Q","Q","C","C","C","A","A","A","D","D","D","K","K","K","B","B","B","D","D","D","C","C","C","K","K","K","D","D","D","J","J","J","B","B","B","J","J","J","K","K","K","B","B","B","Q","Q","Q","E","E","E","J","J","J","A","A","A","D","D","D","K","K","K","C","C","C","E","E","E","K","K","K","Q","Q","Q","A","A","A","D","D","D","E","E","E","Z","Z","Z","E","E","E","C","C","C","E","E","E","B","B","B","J","J","J","D","D","D","C","C","C","B","B","B","A","A","A","B","B","B","J","J","J","C","C","C","J","J","J","D","D","D","A","A","A","C","C","C","A","A","A","K","K","K","Q","Q","Q","A","A","A","Q","Q","Q","A","A","A","E","E","E","D","D","D","B","B","B","A","A","A","Q","Q","Q","K","K","K","E","E","E","K","K","K","Q","Q","Q","J","J","J","D","D","D","K","K","K"];
            $this->reelStrips['reel4_0'] = ["Q","Q","Q","Q","J","J","J","J","K","K","K","K","A","A","A","A","K","K","K","K","C","C","C","C","J","J","J","J","Q","Q","Q","Q","C","C","C","C","K","K","K","K","C","C","C","C","J","J","J","J","D","D","D","D","E","E","E","E","B","B","B","B","K","K","K","K","B","B","B","B","E","E","E","E","A","A","A","A","Z","Z","Z","Z","C","C","C","C","J","J","J","J","B","B","B","B","A","A","A","A","J","J","J","J","E","E","E","E","Q","Q","Q","Q","E","E","E","E","K","K","K","K","A","A","A","A","K","K","K","K","Q","Q","Q","Q","B","B","B","B","C","C","C","C","D","D","D","D","C","C","C","C","D","D","D","D","Q","Q","Q","Q","D","D","D","D","Q","Q","Q","Q","J","J","J","J","D","D","D","D","Q","Q","Q","Q","A","A","A","A","J","J","J","J","C","C","C","C","K","K","K","K","D","D","D","D","A","A","A","A","E","E","E","E","J","J","J","J","Q","Q","Q","Q","D","D","D","D","K","K","K","K","B","B","B","B","S","S","S","S","E","E","E","E","D","D","D","D","J","J","J","J","C","C","C","C","B","B","B","B","A","A","A","A","J","J","J","J","B","B","B","B","A","A","A","A","E","E","E","E","C","C","C","C","K","K","K","K","Q","Q","Q","Q","E","E","E","E","D","D","D","D","K","K","K","K","B","B","B","B","D","D","D","D","A","A","A","A","B","B","B","B","E","E","E","E","Q","Q","Q","Q","C","C","C","C","E","E","E","E","B","B","B","B","A","A","A","A"];
            $this->reelStrips['reel4_N'] = ["B","B","B","B","C","C","C","C","J","J","J","J","A","A","A","A","C","C","C","C","B","B","B","B","A","A","A","A","E","E","E","E","K","K","K","K","J","J","J","J","A","A","A","A","J","J","J","J","A","A","A","A","Q","Q","Q","Q","J","J","J","J","C","C","C","C","Q","Q","Q","Q","D","D","D","D","E","E","E","E","Q","Q","Q","Q","K","K","K","K","B","B","B","B","C","C","C","C","Q","Q","Q","Q","D","D","D","D","C","C","C","C","Q","Q","Q","Q","J","J","J","J","E","E","E","E","K","K","K","K","S","S","S","S","K","K","K","K","J","J","J","J","C","C","C","C","B","B","B","B","K","K","K","K","Q","Q","Q","Q","E","E","E","E","B","B","B","B","A","A","A","A","Q","Q","Q","Q","A","A","A","A","J","J","J","J","D","D","D","D","E","E","E","E","K","K","K","K","C","C","C","C","K","K","K","K","E","E","E","E","K","K","K","K","Q","Q","Q","Q","C","C","C","C","J","J","J","J","Z","Z","Z","Z","Q","Q","Q","Q","D","D","D","D","B","B","B","B","J","J","J","J","D","D","D","D","A","A","A","A","E","E","E","E","D","D","D","D","A","A","A","A","J","J","J","J","E","E","E","E","C","C","C","C","K","K","K","K","A","A","A","A","Q","Q","Q","Q","B","B","B","B","D","D","D","D","K","K","K","K","D","D","D","D","E","E","E","E","B","B","B","B","C","C","C","C","B","B","B","B","D","D","D","D","A","A","A","A","B","B","B","B","D","D","D","D","E","E","E","E"];
            $this->reelStrips['reels'] = [["Q","J","B","J","J","J","J","D","D","D","D","A","B","D","E","Q","Q","Q","Q","J","Q","A","J","J","J","J","E","Z","D","D","D","D","K","K","K","K","Q","Q","Q","Q","C","A","A","A","A","E","E","E","E","A","C","C","C","C","E","E","E","E","J","J","J","J","S","S","A","A","A","A","E","E","E","E","A","A","A","A","K","C","C","C","C","Z","J","J","J","J","Q","Q","Q","Q","K","K","K","K","Q","Q","Q","Q","K","S","D","B","B","B","B","A","A","A","A","J","J","J","J","Z","A","A","A","A","Q","K","K","K","K","D","D","D","D","K","K","K","K","A","A","A","A","K","E","E","E","E","D","D","D","D","B","B","B","B","Q","Q","Q","Q","C","Q","Q","Q","Q","J","J","J","J","K","K","K","K","J","K","K","K","K"],["D","D","D","D","J","C","Z","A","A","A","A","C","Z","C","C","C","C","S","K","S","S","Q","Q","Q","Q","K","K","K","K","C","C","C","C","J","J","J","J","E","E","E","E","C","C","C","C","Q","Q","Q","Q","E","K","K","K","K","Q","Q","Q","Q","K","K","K","K","J","K","K","K","K","E","A","A","A","A","E","E","E","E","K","K","K","K","Z","B","B","B","B","Q","J","J","J","J","C","J","E","E","E","E","A","K","K","K","K","D","D","D","D","A","A","A","A","D","D","D","D","Q","Q","Q","Q","K","D","A","A","A","A","K","Q","Q","Q","Q","J","E","J","J","J","J","A","A","A","A","J","J","J","J","E","E","E","E","J","J","J","J","B","K","K","K","K","A","J","J","J","J","E","E","E","E","J","J","J","J","K","Q"],["Q","J","Q","B","C","A","J","A","A","A","A","Z","K","K","K","K","D","D","D","D","Z","Q","Q","Q","Q","B","Q","Q","Q","Q","C","C","C","C","K","E","E","E","E","A","A","A","A","Q","Q","Q","Q","E","E","E","E","J","J","J","J","K","K","K","K","E","E","E","E","D","B","B","B","B","A","B","B","B","B","D","A","D","D","D","D","Q","Q","Q","Q","D","D","D","D","A","A","A","A","Q","A","J","J","J","J","D","D","D","D","Q","Q","Q","Q","K","K","K","K","Q","A","A","A","A","K","K","K","K","Q","Q","Q","Q","E","J","J","J","J","Q","Q","Q","Q","S","S","A","A","A","A","J","J","J","J","A","A","A","A","D","A","A","A","A","J","J","J","J","B","B","B","B","K","D","D","D","D","B","K","K","K","K","Z","S"],["A","A","A","A","B","B","B","B","S","B","Q","K","C","C","C","C","J","J","J","J","A","E","E","E","E","C","C","C","C","E","A","A","A","A","C","Q","Q","Q","Q","K","K","K","K","Q","Q","Q","Q","J","J","J","J","E","E","E","E","Q","Q","Q","Q","Z","K","K","K","K","J","J","J","J","K","K","K","K","Q","J","J","J","J","K","K","K","K","D","D","D","D","E","C","J","E","E","E","E","J","J","J","J","S","S","J","A","A","A","A","D","E","D","D","D","D","A","A","A","A","J","K","E","E","E","E","Z","C","J","K","K","K","K","D","D","D","D","J","J","J","J","K","K","K","K","Q","Q","Q","Q","C","C","C","C","K","K","K","K","Q","Q","Q","Q","A","Z","K","E","E","E","E","J","J","J","J","A","A","A","A","K"],["D","D","D","D","B","D","D","D","D","B","K","J","J","J","J","Q","J","J","J","J","K","K","K","K","Z","K","Q","Q","Q","Q","E","K","K","K","K","A","A","A","A","E","E","E","E","A","S","A","A","A","A","Q","Q","Q","Q","J","J","J","J","D","D","D","D","Q","Q","Q","Q","E","E","E","E","D","D","D","D","A","A","A","A","C","C","C","C","A","A","A","A","Q","Z","J","Q","Q","Q","Q","D","B","B","B","B","J","Q","Q","Q","Q","A","Z","K","K","K","K","D","Q","B","A","A","A","A","E","E","E","E","Q","Q","Q","Q","J","J","J","J","K","K","K","K","Q","K","K","K","K","B","B","B","B","A","D","D","D","D","A","A","A","A","S","S","C","B","B","B","B","J","J","J","J","Q","Q","Q","Q","A","A","A","A","D","A"],["D","D","D","D","B","C","C","C","C","A","A","A","A","B","B","B","B","D","D","D","D","K","K","K","K","J","J","J","J","E","E","E","E","C","E","A","A","A","A","D","E","E","E","E","J","J","J","J","E","A","A","A","A","Q","K","S","J","J","J","J","Z","J","J","J","J","K","K","K","K","E","E","E","E","Q","C","C","C","C","A","A","A","A","K","J","J","J","J","Q","Q","Q","Q","K","J","A","A","A","A","Q","Q","Q","Q","C","J","A","C","C","C","C","E","E","E","E","Q","Q","Q","Q","K","K","K","K","E","E","E","E","K","K","K","K","Z","Q","Q","Q","Q","J","K","K","K","K","D","D","D","D","J","J","J","J","C","K","K","K","K","E","J","J","J","J","Q","Q","Q","Q","Z","J","A","K","S","S","K","K","K","K"]];
            
            $this->Paytable['A'] = [0,0,0,5,10,20,50];       
            $this->Paytable['K'] = [0,0,0,5,10,20,50];
            $this->Paytable['Q'] = [0,0,0,5,10,20,50];
            $this->Paytable['J'] = [0,0,0,5,10,20,50];
            $this->Paytable['B'] = [0,0,0,15,40,80,200];
            $this->Paytable['C'] = [0,0,0,15,36,72,180];
            $this->Paytable['D'] = [0,0,0,10,20,40,100];
            $this->Paytable['E'] = [0,0,0,10,20,40,100];
            $this->Paytable['S'] = [0,0,0,0,0,0,0,0];
            $this->Paytable['X'] = [0,0,0,0,0,0,0];
            $this->Paytable['Z'] = [0,0,0,20,60,120,300]; //wild

            $this->reelLayout = [
                [
                    [
                        'layout' => [1,1,1,1,2,-1],
                        'overrides' => [
                            "4"=>"2_N",
                            "5"=>"2_N"
                        ]
                    ],
                    [
                        'layout' => [1,1,1,2,-1,1],
                        'overrides' => [
                            "3"=>"2_N",
                            "4"=>"2_N"
                        ]
                    ],
                    [
                        'layout' => [1,1,2,-1,1,1],
                        'overrides' => [
                            "2"=>"2_N",
                            "3"=>"2_N"
                        ]
                    ],
                    [
                        'layout' => [1,2,-1,1,1,1],
                        'overrides' => [
                            "1"=>"2_N",
                            "2"=>"2_N"
                        ]
                    ],
                    [
                        'layout' => [2,-1,1,1,1,1],
                        'overrides' => [
                            "0"=>"2_0",
                            "1"=>"2_0"
                        ]
                    ]
                ],
                [
                    [
                        'layout' => [1,1,2,-1,2,-1],
                        'overrides' => [
                            "2"=>"2_N",
                            "3"=>"2_N",
                            "4"=>"2_N",
                            "5"=>"2_N"
                        ]
                    ],
                    [
                        'layout' => [1,2,-1,1,2,-1],
                        'overrides' => [
                            "1"=>"2_N",
                            "2"=>"2_N",
                            "4"=>"2_N",
                            "5"=>"2_N"
                        ]
                    ],
                    [
                        'layout' => [2,-1,1,1,2,-1],
                        'overrides' => [
                            "0"=>"2_0",
                            "1"=>"2_0",
                            "4"=>"2_N",
                            "5"=>"2_N"
                        ]
                    ],
                    [
                        'layout' => [1,2,-1,2,-1,1],
                        'overrides' => [
                            "1"=>"2_N",
                            "2"=>"2_N",
                            "3"=>"2_N",
                            "4"=>"2_N"
                        ]
                    ],
                    [
                        'layout' => [2,-1,1,2,-1,1],
                        'overrides' => [
                            "0"=>"2_0",
                            "1"=>"2_0",
                            "3"=>"2_N",
                            "4"=>"2_N"
                        ]
                    ],
                    [
                        'layout' => [2,-1,2,-1,1,1],
                        'overrides' => [
                            "0"=>"2_0",
                            "1"=>"2_0",
                            "2"=>"2_N",
                            "3"=>"2_N"
                        ]
                    ],
                ],
                [
                    [
                        'layout' => [2,-1,2,-1,2,-1],
                        'overrides' => [
                            "0"=>"2_0",
                            "1"=>"2_0",
                            "2"=>"2_N",
                            "3"=>"2_N",
                            "4"=>"2_N",
                            "5"=>"2_N"
                        ]
                    ],
                ],
                [
                    [
                        'layout' => [1,1,1,3,-1,-1],
                        'overrides' => [                            
                            "3"=>"3_N",
                            "4"=>"3_N",
                            "5"=>"3_N"
                        ]
                    ],
                    [
                        'layout' => [1,1,3,-1,-1,1],
                        'overrides' => [                            
                            "2"=>"3_N",
                            "3"=>"3_N",
                            "4"=>"3_N"
                        ]
                    ],
                    [
                        'layout' => [1,3,-1,-1,1,1],
                        'overrides' => [                            
                            "1"=>"3_N",
                            "2"=>"3_N",
                            "3"=>"3_N"
                        ]
                    ],
                    [
                        'layout' => [3,-1,-1,1,1,1],
                        'overrides' => [                            
                            "0"=>"3_0",
                            "1"=>"3_0",
                            "2"=>"3_0"
                        ]
                    ],
                ],
                [
                    [
                        'layout' => [1,2,-1,3,-1,-1],
                        'overrides' => [                            
                            "1"=>"2_N",
                            "2"=>"2_N",
                            "3"=>"3_N",
                            "4"=>"3_N",
                            "5"=>"3_N"
                        ]
                    ],
                    [
                        'layout' => [2,-1,1,3,-1,-1],
                        'overrides' => [                            
                            "0"=>"2_0",
                            "1"=>"2_0",
                            "3"=>"3_N",
                            "4"=>"3_N",
                            "5"=>"3_N"
                        ]
                    ],
                    [
                        'layout' => [2,-1,3,-1,-1,1],
                        'overrides' => [                            
                            "0"=>"2_0",
                            "1"=>"2_0",
                            "2"=>"3_N",
                            "3"=>"3_N",
                            "4"=>"3_N"
                        ]
                    ],
                    [
                        'layout' => [3,-1,-1,2,-1,1],
                        'overrides' => [                            
                            "0"=>"2_0",
                            "1"=>"2_0",
                            "2"=>"3_0",
                            "3"=>"2_N",
                            "4"=>"2_N"
                        ]
                    ],
                    [
                        'layout' => [3,-1,-1,1,2,-1],
                        'overrides' => [                            
                            "0"=>"3_0",
                            "1"=>"3_0",
                            "2"=>"3_0",
                            "4"=>"2_N",
                            "5"=>"2_N"
                        ]
                    ],
                    [
                        'layout' => [1,3,-1,-1,2,-1],
                        'overrides' => [                            
                            "1"=>"3_N",
                            "2"=>"3_N",
                            "3"=>"3_N",
                            "4"=>"2_N",
                            "5"=>"2_N"
                        ]
                    ],
                ],
                [
                    [
                        'layout' => [3,-1,-1,3,-1,-1],
                        'overrides' => [                            
                            "0"=>"3_0",
                            "1"=>"3_0",
                            "2"=>"3_0",
                            "3"=>"3_N",
                            "4"=>"3_N",
                            "5"=>"3_N"
                        ]
                    ],
                ],
                [
                    [
                        'layout' => [1,1,4,-1,-1,-1],
                        'overrides' => [                            
                            "2"=>"4_N",
                            "3"=>"4_N",
                            "4"=>"4_N",
                            "5"=>"4_N"
                        ]
                    ],
                    [
                        'layout' => [1,4,-1,-1,-1,1],
                        'overrides' => [                            
                            "1"=>"4_N",
                            "2"=>"4_N",
                            "3"=>"4_N",
                            "4"=>"4_N"
                        ]
                    ],
                    [
                        'layout' => [4,-1,-1,-1,1,1],
                        'overrides' => [                            
                            "0"=>"4_0",
                            "1"=>"4_0",
                            "2"=>"4_0",
                            "3"=>"4_0"
                        ]
                    ],
                ],
                [
                    [
                        'layout' => [2,-1,4,-1,-1,-1],
                        'overrides' => [                            
                            "0"=>"2_0",
                            "1"=>"2_0",
                            "2"=>"4_N",
                            "3"=>"4_N",
                            "4"=>"4_N",
                            "5"=>"4_N"
                        ]
                    ],
                    [
                        'layout' => [4,-1,-1,-1,2,-1],
                        'overrides' => [                            
                            "0"=>"4_0",
                            "1"=>"4_0",
                            "2"=>"4_0",
                            "3"=>"4_0",
                            "4"=>"2_N",
                            "5"=>"2_N"
                        ]
                    ],
                ]
            ];
            
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
            $this->SymbolGame = ["A","Q","B","C","D","E","X","K","J","Z","S"];

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
            //     if($winLimit < 0)
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
        public function GetRandomScatterPos($rp, $endCnt, $sb)
        {
            $rpResult = [];
            for( $i = 0; $i < count($rp)-$endCnt; $i++ ) 
            {
                if( $rp[$i] == $sb ) 
                {
                    if( isset($rp[$i + 1]) && isset($rp[$i + 2]) ) 
                    {
                        array_push($rpResult, $i);
                    }
                    if( isset($rp[$i - 1]) && isset($rp[$i + 1]) ) 
                    {
                        array_push($rpResult, $i - 1);
                    }
                    if( isset($rp[$i + 3]) && isset($rp[$i + 2]) ) 
                    {
                        array_push($rpResult, $i + 1);
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
            $availableConfigs = [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,2,2,2,2,2,2,2,2,2,2,2,3,3,3,3,3,3,3,3,4,4,4,4,4,4,4,5,5,5,5,5,6,6,6,6,7,7];
            shuffle($availableConfigs);
            $reelset_id = $availableConfigs[0];
            $reel_idx = rand(0, count($this->reelLayout[$reelset_id]) - 1);
            $reel_layout = $this->reelLayout[$reelset_id][$reel_idx];

            $layout = $reel_layout['layout'];
            $overrides = $reel_layout['overrides'];          

            $reels = [];   
            $rp = [];

            $scatterCnt = 0;
            $syms = ["A","Q","B","C","D","E","X","K","J","Z"];
            $winSym = $syms[rand(0, 9)];
            foreach($layout as $key => $value)
            {
                $reel = [];
                $pos = -1;
                $reelstrip = [];
                if($value == 1)
                {
                    $reelstrip = $this->reelStrips['reels'][$key];
                    $cnt = count($reelstrip) - 5;
                    
                    if($winType == 'bonus' && $scatterCnt < 6)
                        $pos = $this->GetRandomScatterPos($reelstrip, 10, "S");
                    else
                        $pos = rand(0, $cnt);
                    
                
                    $reel = [$reelstrip[$pos], $reelstrip[$pos+1], $reelstrip[$pos+2], $reelstrip[$pos+3]];
                }
                else if($value == -1)
                {
                    $pos = $rp[$key - 1];
                    $reel = $reels['reel'.($key)];
                }
                else
                {
                    $reelname = 'reel'.$overrides[$key];
                    $reelstrip = $this->reelStrips[$reelname];
                    $cnt = count($reelstrip) - 5;
                    
                    if($winType == 'bonus' && $scatterCnt < 6)
                        $pos = $this->GetRandomScatterPos($reelstrip, 10, "S");
                    else
                    {
                        if($winType == 'win')
                        {
                            $pos = $this->GetSymbolPos($reelstrip, $winSym, 5);
                        }                            
                        else
                            $pos = rand(0, $cnt);
                    }
                        
                    
                    $reel = [$reelstrip[$pos], $reelstrip[$pos+1], $reelstrip[$pos+2], $reelstrip[$pos+3]];
                }
                
                for($c = 0; $c < 4; $c++)
                    if($reel[$c] == 'S')
                        $scatterCnt++;
                $reels['reel'.($key+1)] = $reel;
                $rp[] = $pos;
            }

            if($slotEvent == 'freespin')
            {
                for($r = 0; $r < 6; $r++)
                    for($c = 0; $c < 4; $c++)
                        if($reels['reel'.($r+1)][$c] !== 'S')
                            $reels['reel'.($r+1)][$c] = 'X';
            }
            $reels['rp'] = $rp;
            $reels['set_idx'] = $reelset_id;
            $reels['idx'] = $reel_idx;
            $reels['layout'] = $layout;
            return $reels;
        }
       
        public function GetPaylines()
        {
            $linesId = [[0,0,0,0,0,0],[1,1,1,1,1,1],[2,2,2,2,2,2],[3,3,3,3,3,3],[1,2,3,3,2,1],[2,1,0,0,1,2],[0,1,2,2,1,0],[3,2,1,1,2,3],[1,0,0,0,0,1],[2,3,3,3,3,2],[0,1,1,1,1,0],[3,2,2,2,2,3],[1,2,2,2,2,1],[2,1,1,1,1,2],[1,0,1,2,3,2],[2,3,2,1,0,1],[0,0,1,2,3,3],[3,3,2,1,0,0],[0,1,0,1,0,1],[3,2,3,2,3,2],[1,2,3,2,1,0],[2,1,0,1,2,3],[0,1,2,3,2,1],[3,2,1,0,1,2],[1,0,0,1,2,3],[2,3,3,2,1,0],[0,1,1,2,2,3],[3,2,2,1,1,0],[0,1,2,3,2,2],[3,2,1,0,1,1],[1,2,3,2,1,1],[2,1,0,1,2,2],[1,0,1,0,1,0],[2,3,2,3,2,3],[1,1,2,3,2,1],[2,2,1,0,1,2],[0,1,0,1,2,3],[3,2,3,2,1,0],[1,2,1,0,1,0],[2,1,2,3,2,3]];
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

            $scatter = "SCATTER";
            $wild = ["WILD"];
            while($isWin)
            {               
                $reels = $this->GetReelStrips('none', $reelName);
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
                            $p5 = $linesId[$k][5];

                            $s[0] = $reels['reel1'][$p0];
                            $s[1] = $reels['reel2'][$p1];
                            $s[2] = $reels['reel3'][$p2];
                            $s[3] = $reels['reel4'][$p3];
                            $s[4] = $reels['reel5'][$p4];
                            $s[5] = $reels['reel6'][$p5];                            
                                                                                
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
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) && ($s[5] == $csym || in_array($s[5], $wild)) ) 
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
                    for($r = 1; $r <= 5; $r++)
                        for($c = 0; $c < 3; $c++)
                        {
                            if($reels['reel'.$r][$c] == $scatter)
                            {
                                $scatterCnt++;
                            }
                        }
                    if($scatterCnt < 3)
                        $isWin = false;
                }
            }
            return $reels;
        }
    }

}
