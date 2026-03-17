<?php 
namespace VanguardLTE\Games\JungleBooksYGG
{
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;    

    class Server
    {
        public $gameState;
        public $debug = false;
        public $lastReels;        
        
        function getConvertedLine($line)
        {
            $str = json_encode($line);
            $res = str_replace(['[', ']', ','], ['','',''], $str);
            return $res;
        }

        function generateWagerId()
        {
            $id = date("ymdHms").round(microtime(true) * 1000) % 1000;
            return $id;
        }

        public function get($request, $game)
        {
            try
            {
                DB::beginTransaction();
                $userId = Auth::id();
                if( $userId == null ) 
                {
                    $response = '{"responseEvent":"error","responseType":"","serverResponse":"invalid login"}';
                    exit( $response );
                }
                $slotSettings = new SlotSettings($game, $userId);
                if( !$slotSettings->is_active() ) 
                {
                    $response = '{"responseEvent":"error","responseType":"","serverResponse":"Game is disabled"}';
                    exit( $response );
                }
                
                $postData = $request->all();                        
                $reqId = $postData['fn'];
                $reportWin = 0;
                
                switch( $reqId ) 
                {
                    case 'translations':                                
                        $slotSettings->SetGameData($slotSettings->slotId . 'CoinWin', 0);
                        $response = file(base_path() . '/app/Games/JungleBooksYGG/translation.txt')[0];                                                                          
                        break;
                    case 'authenticate':                                
                        $response = json_encode([
                            "code" => 0,
                            "data" => [
                                "balance" => [
                                    "cash" => $slotSettings->GetBalance()
                                ],
                                "auxiliaryData" => [
                                    "sessionId" => "session",
                                    "ticketId" => "ticket",
                                    "funWalletStatus" => null,
                                    "sessionBalance" => null,
                                    "participation" => null,
                                    "prizes" => null,
                                    "popupMessage" => null
                                    ],
                                "org" => "Demo",
                                "country" => null,
                                "currency" => "EUR",
                                "nativeId" => "",
                                "language" => "en",
                                "userid" => "",
                                "sessid" => $request->sessionId,
                                "nativeSessid" => $request->sessionId,
                                "token" => "",                                   
                                "userProps" => [
                                    "game" => [ "rate" => "1.0"],
                                    "id" => ["nativeid" => ""]
                                    ]
                                ],
                                "fn" => $reqId,
                                "utcts" => time()
                            ]);
                        $slotSettings->SetGameData($slotSettings->slotId . 'CurrentHost', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'CurrentGuest', 5);
                        $slotSettings->SetGameData($slotSettings->slotId . 'HostReaminingSpin', rand(10, 20));
                        $slotSettings->SetGameData($slotSettings->slotId . 'GuestRemainingSpin', 0);
                        break;                                
                    case 'clientinfo':
                        $response = '{"code":0,"data":{"id":"2203301519500100062","org":null,"gameid":0,"data":{"appsrv":"","file":"/init/","gameid":"7337","height":"600","license":"","org":"Demo","root":"","type":"Html","width":"800"}},"fn":"clientinfo","utcts":1648653590613}';
                        break;
                    case 'game':
                        $filename = base_path() . '/app/Games/JungleBooksYGG/game.txt';
                        $file = fopen($filename, "r" );
                        $filesize = filesize( $filename );
                        $response = fread( $file, $filesize );
                        fclose( $file );
                        break;
                    case 'restore':
                        $response = '{"code":0,"data":{"size":0,"next":"","data":[],"columns":[],"filterParams":{},"reportGenerationId":null,"header":[],"empty":true},"fn":"restore","utcts":'.time().'}';
                        break;
                    case 'info':
                        $response = '{"code":0,"data":{"realm":{"currentHost":0,"betAmount":0,"currentGuest":5,"remainingSpinInMode":0,"currentGuestFeature":0,"paylineSet":"Paylines","coin":"0.04"}},"fn":"info","utcts":'.time().'}';
                        break;
                    case 'play':
                        $postData['slotEvent'] = 'bet';
                        
                        $betLine = 0;
                        $nCoins = 50;
                        if(isset($postData['coin']))
                            $betLine = $postData['coin'];
                        
                        $allbet = $betLine * $nCoins;
                        if( !isset($postData['slotEvent']) ) 
                        {
                            $postData['slotEvent'] = 'bet';
                        }

                        $cmd ='';
                        if (isset($postData['cmd']))
                        {
                            $cmd = $postData['cmd'];                            
                        }

                        if($cmd == 'C')
                        {
                            $curCoinWin = $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin');
                            $curBetCoin = $slotSettings->GetGameData($slotSettings->slotId . 'BetCoin');
                            $win = $curCoinWin * $curBetCoin;
                            $slotSettings->SetBank('', -1 * $win);
                            $lastBalance = $slotSettings->GetBalance();
                            $slotSettings->SetBalance($win);
                            $slotSettings->SetWin($win);
                            $reportWin = $win;
                            $ret = [
                                'code' => 0,
                                'data' => [
                                    'buyBal' => ['cash' => $lastBalance],
                                    'cashRace' => [
                                        'currency' => null,
                                        'hasWon' => false,
                                        'initialPrize' => null,
                                        'prize' => null,
                                        'resource' => null
                                    ],
                                    'missionState' => null,
                                    'obj' => null,
                                    'resultBal' => ['cash' =>  $slotSettings->GetBalance()],
                                    'wager' => [
                                        'bets' => [
                                            [
                                                'step' => $slotSettings->GetGameData($slotSettings->slotId . 'Step'),
                                                'betamount' => 0,
                                                'betcurrency' => 'USD',
                                                'wonamount' => number_format($win, 2),
                                                'status' => 'RESULTED',
                                                'betdata'=> [
                                                    'doubleA' => number_format($win, 2),
                                                    'doubleN' => 1,
                                                    'cheat' => null,
                                                    'cmd' => 'C',
                                                    'coin' => $slotSettings->GetGameData($slotSettings->slotId . 'BetCoin'),
                                                    'nCoins' => 1,
                                                    'restoredAccumulatedWonCoin' => $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin'),
                                                    'variant' => null,
                                                    'lines' => '11111111111111111111'
                                                ],
                                                'eventdata' => [],
                                                'prizes' => [
                                                    [
                                                        'descr' => 'Cash out',
                                                        'gameId' => '8302',
                                                        'netamount' => $win,
                                                        'prizeid' => '111',
                                                        'type' => 'FIXED',
                                                        'wonamount' => $win,
                                                        'wonAspect' => 'CASH',
                                                        'woncurrency' => 'USD'
                                                    ]
                                                ],
                                                'prepaid' => false
                                            ]
                                        ],
                                        'prepaid' => false,
                                        'status' => 'Finished',
                                        'timestamp' => time(),
                                        'wagerid' => $postData['wagerid']
                                    ]
                                ],
                                'fn' => $reqId,
                                'utcts' => time()
                            ];                                    
                            
                            $response = json_encode($ret);
                            $allbet = $slotSettings->GetGameData($slotSettings->slotId . 'BetAmount');                            
                            $slotSettings->SaveLogReport($response, $allbet, $reportWin, '');                       
                            break;
                        }

                        $this->gameState = 'Finished';                        
                        if($allbet > $slotSettings->GetBalance())
                        {
                            return '{"completion":"Unknown","code":1006,"errorCode":"NO_SUFFICIENT_FUNDS","type":"O","rid":"220215083220::e14db45d-39e6-4cee-a076-ebb72ca0a89b","msg":"You do not have sufficient funds for the bet","fn":null,"details":null,"relaunchUrl":null,"timeElapsed":null,"errorType":null,"balanceDifference":null,"suppressed":[]}
                            ';
                        }

                        if( $postData['slotEvent'] != 'freespin' ) 
                        {
                            $slotSettings->SetBalance(-1 * $allbet, $postData['slotEvent']);
                            $bankSum = $allbet / 100 * $slotSettings->GetPercent();
                            $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $bankSum, $postData['slotEvent']);
                            $slotSettings->UpdateJackpots($allbet);
                            $slotSettings->SetBet($allbet);
                            $slotSettings->SetGameData($slotSettings->slotId . 'Step', 1);
                            $slotSettings->SetGameData($slotSettings->slotId . 'CoinWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreespinCoinWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'BetCoin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'TreasureChest', []);
                        }                                 
                        
                        $bets = [];
                        $needRespin = true;

                        while($needRespin)
                        {
                            $needRespin = $this->doSpin($slotSettings, $postData, $bets, $cmd);
                        }                        
                        $slotSettings->SetGameData($slotSettings->slotId . 'HostReaminingSpin', $slotSettings->GetGameData($slotSettings->slotId . 'HostReaminingSpin') - 1);
                        if($slotSettings->GetGameData($slotSettings->slotId . 'HostReaminingSpin') < 0)
                        {
                            $slotSettings->SetGameData($slotSettings->slotId . 'HostReaminingSpin', 0);
                        }

                        $wagerid = '';
                        if(isset($postData['wagerid']))
                            $wagerid = $postData['wagerid'];
                        else
                            $wagerid = $this->generateWagerId();
                        $ret = [
                            'code' => 0,
                            'data' => [
                                'buyBal' => ['cash' => $slotSettings->GetBalance()],
                                'cashRace' => [
                                    'currency' => null,
                                    'hasWon' => false,
                                    'initialPrize' => null,
                                    'prize' => null,
                                    'resource' => null
                                ],
                                'missionState' => null,
                                'obj' => null,
                                'resultBal' => ['cash' => $slotSettings->GetBalance()],
                                'wager' => [
                                    'bets' => $bets,
                                    'prepaid' => false,
                                    'status' => $this->gameState,
                                    'timestamp' => time(),
                                    'wagerid' => $wagerid
                                ]
                            ],
                            'fn' => $reqId,
                            'utcts' => time()
                        ];
                        if($this->gameState == 'Finished')
                        {
                            //jokerizer finished with winning more than 500 coin
                            $curCoinWin = $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin');
                            $curBetCoin = $slotSettings->GetGameData($slotSettings->slotId . 'BetCoin');
                            $ret['data']['resultBal']['cash'] = $slotSettings->GetBalance();
                        }
                        $response = json_encode($ret);
                        $slotSettings->SetGameData($slotSettings->slotId . 'BetAmount', $allbet);
                        if($this->gameState == 'Finished')
                            $slotSettings->SaveLogReport($response, $allbet, $reportWin, $postData['slotEvent']);        
                        break;
                    default:
                        break;
                }
                
                $slotSettings->SaveGameData();
                $slotSettings->SaveGameDataStatic();
                DB::commit();          
                return $response;                     
            }
            catch( \Exception $e ) 
            {
                if( isset($slotSettings) ) 
                {
                    $slotSettings->InternalErrorSilent($e);
                }
                else
                {
                    $strLog = '';
                    $strLog .= "\n";
                    $strLog .= ('{"responseEvent":"error","responseType":"' . $e . '","serverResponse":"InternalError","request":' . json_encode($_REQUEST) . ',"requestRaw":' . file_get_contents('php://input') . '}');
                    $strLog .= "\n";
                    $strLog .= ' ############################################### ';
                    $strLog .= "\n";
                    $slg = '';
                    if( file_exists(storage_path('logs/') . 'GameInternal.log') ) 
                    {
                        $slg = file_get_contents(storage_path('logs/') . 'GameInternal.log');
                    }
                    file_put_contents(storage_path('logs/') . 'GameInternal.log', $slg . $strLog);
                }
            }
        }

        function doSpin($slotSettings, &$postData, &$bets, $cmd)
        {
            $reelName = '';
            $currentHost = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentHost');
            $currentGuest = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentGuest');
            $paylineSet = "Paylines";
            
            switch($currentHost)
            {
                case 0: //boy
                    $reelName = 'BoyClassicSetReelA';
                    $paylineSet = "Paylines";
                    break;
                case 1: //bear
                    $bearReels = ['NormalStackedSetA', 'NormalStackedSetB'];
                    $reelName = $bearReels[rand(0,1)];
                    $paylineSet = "Paylines";
                    break;
                case 2: //tiger
                    $tigerReels = ['TigerClassicSetReelA', 'TigerStackedNormalSymbolA', 'TigerStackedNormalSymbolB', 'TigerStackedSimpleA', 'TigerStackedSimpleB', 'TigerStackedWildSymbolA', 'TigerStackedWildSymbolB'];
                    $reelName = $tigerReels[rand(0, count($tigerReels) - 1)];
                    $paylineSet = "Paylines";
                    break;
                case 3: //snake
                    $paylineSet = "Paylines3";
                    $reelName = 'SnakeClassicSetReelA';
                    break;
                case 4: //panther
                    $paylineSet = "Paylines4";
                    $reelName = 'PantherClassicSetReelA';
                    break;
                case 5:
                    break;
            }
            $linesId = $slotSettings->GetPaylines($paylineSet);
            $lines = count($linesId);
            $nCoins = 50;
            $betLine = 0;
            if(isset($postData['coin']))
                $betLine = $postData['coin'];

            $guestDisable = false;
            if($betLine != $slotSettings->GetGameData($slotSettings->slotId . 'BetLine', $betLine))
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'GuestRemainingSpin', 0);
                $guestDisable = true;
            }
            $slotSettings->SetGameData($slotSettings->slotId . 'BetLine', $betLine);
            
            $allbet = $betLine * $nCoins;
            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $nCoins);
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];

            if($slotSettings->GetGameData($slotSettings->slotId . 'HostReaminingSpin') == 0 || $slotSettings->GetGameData($slotSettings->slotId . 'GuestRemainingSpin') == 0)
                $winType = 'none';
            $spinAcquired = false;
            $gameWin = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin');

            $minReels = [];
            $minLineWins = [];
            $minFeatures = [];
            $minReels0 = [];
            $minTreasureChest = [];
            $minTotalWin = -1;
            $minFreespinsWon = 0;

            $totalWin = 0;            
            $freespinsWon = 0;
            $lineWins = [];
            $reels = [];
            $reels0 = [];
            $wild = ["Wild"];
            $bonusMpl = 1;
            $mpl = 1;
            $features = [];

            //set host features
            $activeFeatures = [];
            $activeColumns = [1,1,0,1,1];
            switch($currentHost)
            {
                case 0: //boy                    
                    $activeFeatures[] = 0; //sync reel
                    $activeFeatures[] = 1; //wild spread
                    break;
                case 1: //bear                    
                    $activeFeatures[] = 2; //honeycomb
                    $activeFeatures[] = 3; //stacked symbol                    
                    break;
                case 2: //tiger
                    $activeFeatures[] = 4; //sticky respin
                    $activeFeatures[] = 5; //stacked wilds
                    break;
                case 3: //snake
                    $activeFeatures[] = 6; //both ways
                    $activeFeatures[] = 7; //3x multiplier wild
                    $activeColumns = [1,0,0,0,1];
                    break;
                case 4: //panther
                    $activeFeatures[] = 8; //superhigh is wild
                    $activeFeatures[] = 9; //x2 x3 x5 random multiplier on win
                    $activeColumns = [0,1,1,1,0];
                    break;
            }

            //set guest feature
            $guestRemainingSpin = $slotSettings->GetGameData($slotSettings->slotId . 'GuestRemainingSpin');
            if($guestRemainingSpin > 0)
            {
                $activeFeatures[] = $slotSettings->GetGameData($slotSettings->slotId . 'GuestFeature');
            }
            
            for( $i = 0; $i <= 300; $i++ ) 
            {
                $totalWin = 0;                
                $freespinsWon = 0;
                $lineWins = [];
                $features = [];
                $treasureChest = [];
                
                $cWins = array_fill(0, $lines * 2, 0);
                
                if($this->debug && $postData['slotEvent'] != 'freespin')
                {                 
                    $winType = 'bonus';
                }
                $mpl = 1;
                $reels = $slotSettings->GetReelStrips($winType, $reelName);
                
                if(in_array(0, $activeFeatures))
                {
                    //sync reels
                    if($currentHost == 3 || $currentHost == 4)
                    {
                        //snake
                        $reels['rp'][2] = $reels['rp'][1];
                        $reels['rp'][3] = $reels['rp'][1];                                                
                        $reels['reel3'] = $reels['reel2'];
                        $reels['reel4'] = $reels['reel2'];
                        $reels['map'][2] = $reels['map'][1];
                        $reels['map'][3] = $reels['map'][1];
                        $features[] = ['synchedReels' => ['reelsToSync' => [1,2,3]]];
                    }                    
                    else
                    {
                        $reels['rp'][1] = $reels['rp'][0];
                        $reels['rp'][4] = $reels['rp'][3];
                        $reels['reel2'] = $reels['reel1'];
                        $reels['reel5'] = $reels['reel4'];
                        $reels['map'][1] = $reels['map'][0];
                        $reels['map'][4] = $reels['map'][3];
                        $features[] = ['synchedReels' => ['reelsToSync' => [0,1,3,4]]];
                    }
                    
                }
                if($postData['slotEvent'] == 'respin')
                {
                    $stickyA = $slotSettings->GetGameData($slotSettings->slotId . 'StickyA');
                    for($r = 0; $r < 5; $r++)
                        for($c = 0; $c < 4; $c++)
                        {
                            if($stickyA[$r * 4 + $c] == 1)
                            {
                                $reels['reel'.($r+1)][$c] = $this->lastReels['reel'.($r+1)][$c];
                            }                                
                        }
                }
                $reels0 = $reels;

                if(in_array(1, $activeFeatures))
                {
                    //spread wilds
                    $positions = [];
                    $addedPos = [];
                    for($r = 0; $r < 5; $r++)
                    {
                        for($c = $activeColumns[$r]; $c < 4; $c++)
                        {
                            if($reels0['reel'.($r+1)][$c] == 'Wild')
                            {
                                $wildPos = $r * 4 + $c;
                                $newR = rand(0,4);
                                $newC = rand(1,3);
                                $index = $newR * 4 + $newC;
                                while($reels0['reel'.($newR+1)][$newC] == 'Wild' && !in_array($index, $addedPos))
                                {
                                    $newR = rand(0,4);
                                    $newC = rand(1,3);
                                }
                                $reels['reel'.($newR+1)][$newC] = 'Wild';
                                $addedPos[] = $index;
                                $positions[] = ['from' => $wildPos, 'to' => [$index]];
                            }
                        }
                    }

                    if(count($positions) > 0)
                    {
                        $icons = [];
                        for($c = 0; $c < 4; $c++)
                        {
                            $arr = [];
                            for($r = 1; $r <= 5; $r++)
                                $arr[] = $slotSettings->SymbolIndex[$reels['reel'.$r][$c]];
                            $icons[] = $arr;                                
                        }
                        $features[] = ['spreadingWilds' => ['icons' => $icons, 'positions' => $positions]];
                    }
                }

                if(in_array(2, $activeFeatures)) //honey comb
                {
                    if($postData['slotEvent'] != 'respin' && $winType == 'win')
                    {
                        if(rand(0, 100) < 20)
                        {
                            //get tallest reel
                            $tallestReel = -1;
                            for($r = 0; $r < 5; $r++)
                                if($activeColumns[$r] == 0)
                                {
                                    $tallestReel = $r;
                                    break;
                                }
                            $c = rand(0,3);
                            $treasureChest = ['positions' => [$tallestReel * 4 + $c]];
                            $reels['reel'.($tallestReel+1)][$c] = 'Bonus';
                        }
                    }
                    else
                    {
                        $treasureChest = $slotSettings->GetGameData($slotSettings->slotId . 'TreasureChest');
                    }                    
                }

                if(in_array(3, $activeFeatures)) //stacked symbols
                {
                    $stackedSymbols = [
                        "Bonus" => 0,
                        "SuperHigh" => 0,
                        "Wild" => 0,
                        "High1" => 0,
                        "High2" => 0,
                        "High3" => 0,
                        "High4" => 0,       
                        "High5" => 0,
                        "Low1" => 0,
                        "Low2" => 0,
                        "Low3" => 0,
                        "Low4" => 0,
                    ];
                    for($r = 0; $r < 5; $r++)
                    {
                        $tmpStack = [
                        "Bonus" => 0,
                        "SuperHigh" => 0,
                        "Wild" => 0,
                        "High1" => 0,
                        "High2" => 0,
                        "High3" => 0,
                        "High4" => 0,       
                        "High5" => 0,
                        "Low1" => 0,
                        "Low2" => 0,
                        "Low3" => 0,
                        "Low4" => 0,
                        ];
                        for($c = $activeColumns[$r]; $c < 4; $c++)
                        {
                            $tmpStack[$reels['reel'.($r+1)][$c]]++;
                            if($tmpStack[$reels['reel'.($r+1)][$c]] > $stackedSymbols[$reels['reel'.($r+1)][$c]])
                                $stackedSymbols[$reels['reel'.($r+1)][$c]] = $tmpStack[$reels['reel'.($r+1)][$c]];
                        }
                    }
                    $maxCount = 0;
                    $maxSymbol = '';
                    foreach($stackedSymbols as $key => $value)
                    {
                        if($maxCount == 0 || $maxCount < $value)
                        {
                            $maxCount = $value;
                            $maxSymbol = $key;
                        }
                    }
                    $features[] = ['stackedSymbol' => $slotSettings->SymbolIndex[$maxSymbol]];
                }

                if(in_array(4, $activeFeatures))
                {
                    //sticky respin
                    $features[] = ['stickyRespin' => 'active'];
                }

                if(in_array(5, $activeFeatures))
                {
                    //stacked wilds
                    $features[] = ['stackedWilds' => 'active'];
                }

                if(in_array(6, $activeFeatures))
                {
                    //both way winning
                    $features[] = ['winBothWays' => 'active'];
                }
                if(in_array(7, $activeFeatures))
                {
                    //wild 3x multiplier
                    $features[] = ['multiplier3ForWild' => 'active'];
                }
                if(in_array(8, $activeFeatures))
                {
                    //superhigh symbol as wild
                    $positions = [];
                    for($r = 0; $r < 5; $r++)
                        for($c = 0; $c < 4; $c++)
                        {
                            if($reels['reel'.($r+1)][$c] == 'SuperHigh')
                            {
                                $reels['reel'.($r+1)][$c] = 'Wild';
                                $positions[] = $r * 4 + $c;
                            }
                        }
                    
                    if(count($positions) > 0)
                    {
                        for($c = 0; $c < 4; $c++)
                        {
                            $arr = [];
                            for($r = 1; $r <= 5; $r++)
                                $arr[] = $slotSettings->SymbolIndex[$reels['reel'.$r][$c]];
                            $icons[] = $arr;                                
                        }
                        $features[] = ['topPayingSymbolIsWild' => ['icons' => $icons, 'positions' => $positions]];
                    }
                }
                if(in_array(9, $activeFeatures))
                {
                    $rand = rand(0, 95);
                    if($rand < 20)
                        $bonusMpl = 1;
                    else if($rand < 65)
                        $bonusMpl = 2;
                    else if($rand < 90)
                        $bonusMpl = 3;
                    else 
                        $bonusMpl = 5;
                    $features[] = ['wholeRewardMultiplier' => $bonusMpl];
                }
                
                for( $k = 0; $k < $lines; $k++ )
                {
                    $winline = [];
                    for( $j = 0; $j < count($slotSettings->SymbolGame); $j++ ) 
                    {
                        $csym = $slotSettings->SymbolGame[$j];
                        if( !isset($slotSettings->Paytable[$csym]) ) 
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
                            $mpl = 1;
                            
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) ) 
                            {
                                if($currentHost == 3 && in_array($wild[0], [$s[0], $s[1], $s[2]]))
                                    $mpl = 3;
                                $tmpWin = $slotSettings->Paytable[$csym][3] * $betLine * $mpl * $bonusMpl;
                                $coin = $slotSettings->Paytable[$csym][3] * $mpl * $bonusMpl;                                
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $symbols = [pow(2, 3 - $p0), pow(2, 3 - $p1), pow(2, 3 - $p2),0,0];
                                    $cWins[$k] = $tmpWin;                                    
                                    $winline = [$k + 1, $coin, implode('', $symbols), $tmpWin, [$p0, $p1, $p2, -1, -1]];
                                }
                            }
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) ) 
                            {
                                if($currentHost == 3 && in_array($wild[0], [$s[0], $s[1], $s[2], $s[3]]))
                                    $mpl = 3;
                                $tmpWin = $slotSettings->Paytable[$csym][4] * $betLine * $mpl * $bonusMpl;
                                $coin = $slotSettings->Paytable[$csym][4] * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $symbols = [pow(2, 3 - $p0), pow(2, 3 - $p1), pow(2, 3 - $p2), pow(2, 3 - $p3),0];
                                    $cWins[$k] = $tmpWin;                                    
                                    $winline = [$k + 1, $coin, implode('', $symbols), $tmpWin, [$p0, $p1, $p2, $p3, -1]];
                                }
                            }
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) ) 
                            {
                                if($currentHost == 3 && in_array($wild[0], [$s[0], $s[1], $s[2], $s[3], $s[4]]))
                                    $mpl = 3;
                                $tmpWin = $slotSettings->Paytable[$csym][5] * $betLine * $mpl * $bonusMpl;
                                $coin = $slotSettings->Paytable[$csym][5] * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin )
                                {
                                    $cWins[$k] = $tmpWin;
                                    $symbols = [pow(2, 3 - $p0), pow(2, 3 - $p1), pow(2, 3 - $p2), pow(2, 3 - $p3), pow(2, 3 - $p4)];                                    
                                    $winline = [$k + 1, $coin, implode('', $symbols), $tmpWin, [$p0, $p1, $p2, $p3, $p4]];
                                }
                            }

                            if($currentHost == 3)
                            {
                                //in case of snake, make both win 
                                if( ($s[4] == $csym || in_array($s[4], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) ) 
                                {
                                    if($currentHost == 3 && in_array($wild[0], [$s[2], $s[3], $s[4]]))
                                        $mpl = 3;
                                    $tmpWin = $slotSettings->Paytable[$csym][3] * $betLine * $mpl * $bonusMpl;
                                    $coin = $slotSettings->Paytable[$csym][3] * $mpl * $bonusMpl;                                
                                    if( $cWins[$k + $lines] < $tmpWin ) 
                                    {
                                        $symbols = [0,0,pow(2, 3 - $p2), pow(2, 3 - $p3), pow(2, 3 - $p4)];
                                        $cWins[$k + $lines] = $tmpWin;                                    
                                        $winline = [$k + $lines + 1, $coin, implode('', $symbols), $tmpWin, [-1, -1, $p2, $p3, $p4]];
                                    }
                                }
                                if( ($s[4] == $csym || in_array($s[4], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) ) 
                                {
                                    if($currentHost == 3 && in_array($wild[0], [$s[1], $s[2], $s[3], $s[4]]))
                                        $mpl = 3;
                                    $tmpWin = $slotSettings->Paytable[$csym][4] * $betLine * $mpl * $bonusMpl;
                                    $coin = $slotSettings->Paytable[$csym][4] * $mpl * $bonusMpl;
                                    if( $cWins[$k + $lines] < $tmpWin ) 
                                    {
                                        $symbols = [0,pow(2, 3 - $p1), pow(2, 3 - $p2), pow(2, 3 - $p3), pow(2, 3 - $p4)];
                                        $cWins[$k + $lines] = $tmpWin;                                    
                                        $winline = [$k + $lines + 1, $coin, implode('', $symbols), $tmpWin, [-1, $p1, $p2, $p3, $p4]];
                                    }
                                }
                            }
                        }
                    }

                    if( $cWins[$k] > 0 && !empty($winline))
                    {
                        array_push($lineWins, $winline);
                        $totalWin += $cWins[$k];
                    }
                    if( $cWins[$k + $lines] > 0 && !empty($winline))
                    {
                        array_push($lineWins, $winline);
                        $totalWin += $cWins[$k + $lines];
                    }
                }

                if($postData['slotEvent'] != 'respin')
                {
                    if(isset($treasureChest['positions']))
                    {
                        $coin = rand(1, 10) * 50;
                        $totalWin += $coin * $betLine;
                        $treasureChest['rewards'] = [$coin];
                    }
                }
                
                $totalWin += $gameWin;
                
                if($minTotalWin == -1 || ($minTotalWin > $totalWin && $totalWin > 0))
                {
                    $minTotalWin = $totalWin;
                    $minLineWins = $lineWins;
                    $minFreespinsWon = $freespinsWon;
                    $minReels = $reels;
                    $minReels0 = $reels0;
                    $minFeatures = $features;
                    $minTreasureChest = $treasureChest;
                }

                if($this->debug)
                {
                    $spinAcquired = true;
                    break;
                }                    

                if($totalWin <= $spinWinLimit && (($totalWin > 0 && $winType != 'none')))
                {
                    $spinAcquired = true;
                    if($totalWin < 0.5 * $spinWinLimit && $winType != 'bonus')
                        $spinAcquired = false;
                    if($spinAcquired)
                        break;                                        
                }                                     
                else if( $winType == 'none' && $totalWin == $gameWin ) 
                {
                    break;
                }
            }

            $manualNoWin = false;
            if(!$spinAcquired && $totalWin > $gameWin && $winType != 'none')
            {                
                $manualNoWin = true;                
                $reels = $minReels;
                $lineWins = $minLineWins;
                $totalWin = $minTotalWin;
                $freespinsWon = $minFreespinsWon;
                $features = $minFeatures;
                $reels0 = $minReels0;
                $treasureChest = $minTreasureChest;
            }

            $respin = 0;
            $stickyB = array_fill(0, 20, 0);
            $stickyA = array_fill(0, 20, 0);
            $stickyN = array_fill(0, 20, 0);
            if(in_array(4, $activeFeatures))
            {
                //give one more sticky respin if winning spin
                if(count($lineWins) > 0 || count($treasureChest) > 0)
                {
                    if($postData['slotEvent'] != 'respin')
                    {
                        $respin = 1;
                        foreach($lineWins as $lineWin)
                        {
                            $winSym = $lineWin[4];
                            for($r = 0; $r < 5; $r++)
                            {
                                if($winSym[$r] > -1)
                                {
                                    $c = $winSym[$r];
                                    $stickyA[4 * $r + $c] = 1;
                                    $stickyN[4 * $r + $c] = 1;
                                }
                            }
                        }

                        if(isset($treasureChest['positions']) > 0)
                        {
                            $position = $treasureChest['positions'][0];
                            $stickyA[$position] = 1;
                            $stickyN[$position] = 1;
                        }
                    }
                    else
                    {
                        $respin = 0;
                        $stickyA = $slotSettings->GetGameData($slotSettings->slotId . 'StickyA');
                        $stickyB = $stickyA;
                        $stickyN = array_fill(0, 20, 0);                        
                    }
                    $slotSettings->SetGameData($slotSettings->slotId . 'StickyA', $stickyA);
                    $slotSettings->SetGameData($slotSettings->slotId . 'StickyB', $stickyB);
                    $slotSettings->SetGameData($slotSettings->slotId . 'StickyN', $stickyN);
                }
            }

            if(isset($treasureChest['positions'])) //set treasure bonus
            {
                if($respin > 0)
                {
                    $slotSettings->SetGameData($slotSettings->slotId . 'TreasureChest', $treasureChest);
                    $features[] = ['treasureChestPositions' => $treasureChest['positions']];
                }
                else
                {
                    $features[] = ['treasureChestPositions' => $treasureChest['positions']];
                    $features[] = ['treasureChest' => $treasureChest];
                }
            }

            $this->lastReels = $reels;           
            
            $coinWin = 0; //coins won
            if(!empty($lineWins))
            {
                foreach($lineWins as $winline)
                {
                    $coinWin += $winline[1]; //sum up coins                     
                }
            }
            if(isset($treasureChest['positions']) && $respin == 0)
            {
                $coinWin += $treasureChest['rewards'][0];
            }

            $slotSettings->SetGameData($slotSettings->slotId . 'CoinWin', $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') + $coinWin);
            $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', $totalWin);
            $slotSettings->SetGameData($slotSettings->slotId . 'BetCoin', $betLine);
            //nextCmds
            $nextCmds = [];
            $needRespin = false;
            $gameModeCurrent = [
                'betAmount' => $allbet * 1e6,
                'coin' => $postData['coin'],
                'currentGuest' => $currentGuest,
                'currentGuestFeature' => '-1',
                'currentHost' => $currentHost,
                'paylineSet' => $paylineSet,
                'remainingSpinInMode' => '0'
            ];
            $gameModeNext = [
                'betAmount' => $allbet * 1e6,
                'coin' => $postData['coin'],
                'currentGuest' => 5,
                'currentGuestFeature' => '-1',
                'currentHost' => $currentHost,
                'paylineSet' => $paylineSet,
                'remainingSpinInMode' => '0'
            ];
            $guestRemainingSpin = $slotSettings->GetGameData($slotSettings->slotId . 'GuestRemainingSpin');
            if($guestRemainingSpin > 0)
            {
                $gameModeCurrent['currentGuestFeature'] = $slotSettings->GetGameData($slotSettings->slotId . 'GuestFeature');
                $gameModeCurrent['remainingSpinInMode'] = $guestRemainingSpin;
                if($respin == 0)
                    $gameModeNext['remainingSpinInMode'] = $guestRemainingSpin - 1 >= 0 ? $guestRemainingSpin - 1 : 0;
                
                if($guestRemainingSpin > 1)
                {
                    $gameModeNext['currentGuestFeature'] = $slotSettings->GetGameData($slotSettings->slotId . 'GuestFeature');
                    $gameModeNext['currentGuest'] = $currentGuest;
                }
                if($postData['slotEvent'] != 'respin')
                    $slotSettings->SetGameData($slotSettings->slotId . 'GuestRemainingSpin', $slotSettings->GetGameData($slotSettings->slotId . 'GuestRemainingSpin') - 1);
            }
            else
            {
                $gameModeNext['currentGuest'] = 5;
                $gameModeNext['currentGuestFeature'] = -1;

                if(rand(0, 100) < 10 && $slotSettings->GetGameData($slotSettings->slotId . 'HostReaminingSpin') > 3 && !$guestDisable)
                {
                    //put guest feature for small possibility
                    $newGuest = 0;
                    while($newGuest == $currentHost)
                        $newGuest = rand(0, 3);
                    $slotSettings->SetGameData($slotSettings->slotId . 'GuestRemainingSpin', rand(4, 7));
                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentGuest', $newGuest);

                    switch($newGuest)
                    {
                        case 0:
                            $gameModeNext['currentGuestFeature'] = 0; //sync reels
                            $slotSettings->SetGameData($slotSettings->slotId . 'GuestFeature', 0);
                            break;
                        case 1:
                            $gameModeNext['currentGuestFeature'] = 0; //honey comb
                            $slotSettings->SetGameData($slotSettings->slotId . 'GuestFeature', 2);
                            break;
                        case 2:
                            $gameModeNext['currentGuestFeature'] = 0; //sticky respin
                            $slotSettings->SetGameData($slotSettings->slotId . 'GuestFeature', 4);
                            break;
                        case 3:
                            $gameModeNext['currentGuestFeature'] = 0; //both ways
                            $slotSettings->SetGameData($slotSettings->slotId . 'GuestFeature', 6);
                            break;
                        case 4:
                            $gameModeNext['currentGuestFeature'] = 0; //superhigh is wild
                            $slotSettings->SetGameData($slotSettings->slotId . 'GuestFeature', 8);
                            break;
                    } 
                }
            }

            if($slotSettings->GetGameData($slotSettings->slotId . 'HostReaminingSpin') == 0)
            {
                $availableNext = [];
                for($i = 0; $i < 5; $i++)
                    if($i != $currentHost)
                    {
                        if($i != 2)
                            $availableNext[] = $i;
                        else
                        {
                            if(rand(0, 100) < 10)
                                $availableNext[] = $i;
                        }
                    }
                
                $gameModeNext['currentGuest'] = $currentHost;
                $newHost = $availableNext[rand(0, count($availableNext) - 1)];
                
                switch($currentHost)
                {
                    case 0:
                        $gameModeNext['currentGuestFeature'] = 0; //sync reels
                        $slotSettings->SetGameData($slotSettings->slotId . 'GuestFeature', 0);
                        break;
                    case 1:
                        $gameModeNext['currentGuestFeature'] = 0; //honey comb
                        $slotSettings->SetGameData($slotSettings->slotId . 'GuestFeature', 2);
                        break;
                    case 2:
                        $gameModeNext['currentGuestFeature'] = 0; //sticky respin
                        $slotSettings->SetGameData($slotSettings->slotId . 'GuestFeature', 4);
                        break;
                    case 3:
                        $gameModeNext['currentGuestFeature'] = 0; //both ways
                        $slotSettings->SetGameData($slotSettings->slotId . 'GuestFeature', 6);
                        break;
                    case 4:
                        $gameModeNext['currentGuestFeature'] = 0; //superhigh is wild
                        $slotSettings->SetGameData($slotSettings->slotId . 'GuestFeature', 8);
                        break;
                }                
                
                $gameModeNext['currentHost'] = $newHost;                
                $gameModeNext['remainingSpinInMode'] = 2;    
                $newPaylineSet = 'Paylines';
                switch($newHost)
                {
                    case 3: //snake
                        $newPaylineSet = "Paylines3";
                        break;
                    case 4: //panther
                        $newPaylineSet = "Paylines4";
                        break;
                }
                $gameModeNext['paylineSet'] = $newPaylineSet;
                $slotSettings->SetGameData($slotSettings->slotId . 'GuestRemainingSpin', rand(4, 7));
                $slotSettings->SetGameData($slotSettings->slotId . 'HostReaminingSpin', rand(10, 20));
                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentHost', $newHost);
                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentGuest', $currentHost);
            }

            $eventData = [
                'accC' => $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin'),
                'accWa' => number_format($slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') * $betLine, 2),                
                'reelSet' => $reelName,
                'reelMap' => $reels['map'],
                'features' => $features,
                'reels' => $slotSettings->GetReelSymbol($reels0),
                'rpos' => [$reels['rp'][0] - 1, $reels['rp'][1] - 1, $reels['rp'][2] - 1, $reels['rp'][3] - 1, $reels['rp'][4] - 1],
                'reelSet' => [],
                'wonCoins' => $coinWin,
                'wonMoney' => number_format($coinWin * $betLine, 2),
                'wtw' => $lineWins,
                'reelSet' => implode(',', $reels['set']),
                'gameModeCurrent' => $gameModeCurrent,
                'paylineSet' => $paylineSet,
                'manualNoWin' => $manualNoWin,
            ];

            if($postData['slotEvent'] == 'bet')
            {
                if($respin > 0)
                {
                    //trigger freespin
                    $postData['slotEvent'] = 'respin';
                    $eventData['stickyA'] = implode('', $stickyA);
                    $eventData['stickyB'] = implode('', $stickyB);
                    $eventData['stickyN'] = implode('', $stickyN);
                    $needRespin = true;
                    $eventData['reSpins'] = true;
                }
            }
            else
            {
                $eventData['stickyA'] = implode('', $stickyA);
                $eventData['stickyB'] = implode('', $stickyB);
                $eventData['stickyN'] = implode('', $stickyN);
            }

            if($needRespin)
            {
                $this->gameState = 'Pending';
            }
            else
            {
                if($totalWin > 0)
                {
                    $this->gameState = 'Pending';
                    $nextCmds[] = 'C';
                }
                $eventData['gameModeNext'] = $gameModeNext;
            }
           
            $prizes = null;            

            if(!empty($nextCmds))
                $eventData['nextCmds'] = implode(',', $nextCmds);

            $betData = [
                'coin' => $betLine,
                'nCoins' => 10,
                'cheat' => null,
                'clientData' => null,                
                'variant' => null
            ];
            if($cmd != '')
                $betData['cmd'] = $cmd;            

            $bet = [
                'step' => $slotSettings->GetGameData($slotSettings->slotId . 'Step'),
                'betamount' => $allbet,
                'betcurrency' => 'USD',                
                'status' => 'RESULTED',
                'betdata'=> $betData,
                'eventdata' => $eventData,
                'prizes' => $prizes,
                'prepaid' => false,
            ];
            if($this->gameState == 'Finished')
                $bet['wonamount'] = number_format($slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') * $betLine, 2);
            $bets[] = $bet;
            $slotSettings->SetGameData($slotSettings->slotId . 'Step', $slotSettings->GetGameData($slotSettings->slotId . 'Step') + 1);
            return $needRespin;
        }

        function getActiveSymbols($reels, $sym, $line)
        {
            $rows = 5;
            $cols = count($reels['reel1']);
                        
            $active = array_fill(0, $rows * $cols, 0);
            for($r = 0; $r < $rows; $r++)
                for($c = 0; $c < $cols; $c++)
                {
                    if($reels['reel'.($r+1)][$c] == $sym && $c == $line)
                        $active[$r * $cols + $c] = 1;
                }
            
            return implode("", $active);
        }

        function getMaxHighSymbol($reels, $slotSettings)
        {
            $sym = '';
            $linesId = $slotSettings->GetPaylines();
            $lines = count($linesId);
            $scatter = "Scatter";
            $wild = ["Wild"];
            
            $maxWin = 0;
            foreach($slotSettings->highSymbols as $highSym)
            {
                for($r = 0; $r < 5; $r++)
                    for($c = 0; $c < 2; $c++)
                    {
                        if($reels['reel'.($r+1)][$c] == $highSym)
                        {
                            $reels['reel'.($r+1)][$c] = 'Wild';                            
                        }
                    }
                
                $win = 0;
                for( $k = 0; $k < $lines; $k++ ) 
                {
                    for( $j = 0; $j < count($slotSettings->SymbolGame); $j++ ) 
                    {
                        $csym = $slotSettings->SymbolGame[$j];
                        if( $csym == $scatter || !isset($slotSettings->Paytable[$csym]) ) 
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
                            $tmpWin = 0;
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) ) 
                            {
                                $tmpWin = $slotSettings->Paytable[$csym][3];                                
                            }
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) ) 
                            {
                                $tmpWin = $slotSettings->Paytable[$csym][4];                                
                            }
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) ) 
                            {
                                $tmpWin = $slotSettings->Paytable[$csym][5];                                
                            }
                            $win += $tmpWin;
                        }
                    }                    
                }
                if($maxWin < $win)
                {
                    $maxWin = $win;
                    $sym = $highSym;
                }
            }
            return $sym;
        }        
    }

}


