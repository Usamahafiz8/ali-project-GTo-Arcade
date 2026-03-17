<?php 
namespace VanguardLTE\Games\GoldenMonkeyYGG
{
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;    

    class Server
    {
        public $gameState;
        public $debug = false;
        public $lastReels;        

        function getPositions($csym, $reels, $cnt, $direction)
        {
            $positions = array_fill(0, 15, 0);

            if($direction == 0)
            {
                for($r = 0; $r < $cnt; $r++)
                for($c = 0; $c < 3; $c++)
                {
                    if($reels['reel'.($r+1)][$c] == $csym || $reels['reel'.($r+1)][$c] == 'SYM1')
                    {
                        $positions[$r * 3 + $c] = 1;
                    }                    
                }
            }
            else
            {
                for($r = 4; $r > 4 - $cnt; $r--)
                for($c = 0; $c < 3; $c++)
                {
                    if($reels['reel'.($r+1)][$c] == $csym || $reels['reel'.($r+1)][$c] == 'SYM1')
                    {
                        $positions[$r * 3 + $c] = 1;
                    }                    
                }
            }
            
            return implode("",$positions);
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
                        $response = file(base_path() . '/app/Games/GoldenMonkeyYGG/translation.txt')[0];                                                                          
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
                        break;                                
                    case 'clientinfo':
                        $response = '{"code":0,"data":{"id":"2203301519500100062","org":null,"gameid":0,"data":{"appsrv":"","file":"/init/","gameid":"7325","height":"600","license":"","org":"Demo","root":"","type":"Html","width":"800"}},"fn":"clientinfo","utcts":1648653590613}';
                        break;
                    case 'game':
                        $filename = base_path() . '/app/Games/GoldenMonkeyYGG/game.txt';
                        $file = fopen($filename, "r" );
                        $filesize = filesize( $filename );
                        $response = fread( $file, $filesize );
                        fclose( $file );
                        break;
                    case 'restore':
                        $response = '{"code":0,"data":{"size":0,"next":"","data":[],"columns":[],"filterParams":{},"reportGenerationId":null,"header":[],"empty":true},"fn":"restore","utcts":'.time().'}';
                        $slotSettings->SetGameData($slotSettings->slotId . 'WildInfo', []);
                        $slotSettings->SetGameData($slotSettings->slotId . 'FreespinWildInfo', []);
                        $slotSettings->SetGameData($slotSettings->slotId . 'BaseSticky', array_fill(0, 15, 0));
                        break;
                    case 'info':
                        $response = '{"code":0,"data":{"nextwilds":{"nextWilds":{},"amount":"0.00"}},"fn":"info","utcts":1667738748964}';
                        break;
                    case 'play':
                        $postData['slotEvent'] = 'bet';
                        
                        $betLine = 0;
                        $nCoins = 25;
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
                            //collect current supermeter and end jokerizer mode
                            $curCoinWin = $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin');
                            $curBetCoin = $slotSettings->GetGameData($slotSettings->slotId . 'BetCoin');
                            $win = $curCoinWin * $curBetCoin;
                            $slotSettings->SetBank($slotSettings->GetGameData($slotSettings->slotId . 'LastEvent'), -1 * $win);
                            $postData['slotEvent'] = $slotSettings->GetGameData($slotSettings->slotId . 'LastEvent');
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
                                                'betcurrency' => 'EUR',
                                                'wonamount' => number_format($win, 2),
                                                'status' => 'RESULTED',
                                                'betdata'=> [
                                                    'doubleA' => number_format($win, 2),
                                                    'doubleN' => 1,
                                                    'cheat' => null,
                                                    'cmd' => 'C',
                                                    'coin' => $slotSettings->GetGameData($slotSettings->slotId . 'BetCoin'),
                                                    'nCoins' => 25,
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
                                                        'woncurrency' => 'EUR'
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
                            if($slotSettings->GetGameData($slotSettings->slotId . 'LastEvent') === 'bonus')
                                $postData['slotEvent'] = 'freespin';
                            $slotSettings->SaveLogReport($response, $allbet, $reportWin, $postData['slotEvent']);                       
                            break;
                        }
                        if($cmd == 1 || $cmd == 2 || $cmd == 3)
                        {
                            $allbet = $postData['amount'];
                            $postData['slotEvent'] = 'freespin';
                            $postData['freespinMode'] = $cmd;
                            $freespinsWon = 0;
                            if($cmd == 1)
                            {
                                //incremental wild
                                $freespinsWon = 6;
                            }
                            else if($cmd == 2)
                            {
                                //stacked wild
                                $freespinsWon = 8;
                            }
                            else if($cmd == 3)
                            {
                                //starting wild
                                $freespinsWon = 10;
                            }
                            $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $freespinsWon);
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
                            $slotSettings->SetGameData($slotSettings->slotId . 'CoinBeforeFreespin', 0);
                        }                                 
                        
                        $bets = [];
                        $needRespin = true;
                        while($needRespin)
                        {
                            $needRespin = $this->doSpin($slotSettings, $postData, $bets, $cmd);
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
            $nCoins = 25;
            $betLine = 0;
            if(isset($postData['coin']))
                $betLine = $postData['coin'];
            $allbet = $betLine * $nCoins;

            if($postData['slotEvent'] == 'freespin')
            {
                //freespin
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bonus');
                $reelName = 'FeatureReelsV' . $postData['freespinMode'];
                $betLine = $slotSettings->GetGameData($slotSettings->slotId . 'BetCoin');
                $allbet = $slotSettings->GetGameData($slotSettings->slotId . 'BetAmount');
            }
            else
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bet');
                $reelName = 'Reels';
            }
            
            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $nCoins);
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
            if($postData['slotEvent'] == 'freespin' && $slotSettings->GetGameData($slotSettings->slotId . 'Step') < 4)
            {
                $winType = 'win';
                $spinWinLimit = $slotSettings->GetBank('bonus');
            }

            if($postData['slotEvent'] == 'freespin' && $postData['freespinMode'] == 1)
                $winType = 'win';
            $spinAcquired = false;             
            $gameWin = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin');

            $minReels = [];
            $minLineWins = [];
            $minTotalWin = -1;
            $minAddedWilds = [];
            $minMonkey = [];
            $minReels0 = [];
            $minChest = [];
            $minFreespinsWon = 0;

            $totalWin = 0;
            $freespinsWon = 0;            
            $lineWins = [];            
            $reels = [];
            $reels0 = [];
            $monkey = [];
            $chest = [];            
            
            $scatterCoin = 0;
            $bonusMpl = 1;
            $scatter = "SYM0";
            $addedWilds = [];

            $thisWilds = $addedWilds;
            for( $i = 0; $i <= 300; $i++ ) 
            {
                if($postData['slotEvent'] == 'freespin')
                    $addedWilds = $slotSettings->GetGameData($slotSettings->slotId . 'FreespinWildInfo');
                else
                    $addedWilds = $slotSettings->GetGameData($slotSettings->slotId . 'WildInfo');
                $monkey = [];
                $chest = [];
                $totalWin = 0;
                $freespinsWon = 0;                
                $lineWins = [];
                $cWins = [
                    "SYM0" => 0,
                    "SYM1" => 0,
                    "SYM2" => 0,
                    "SYM3" => 0,
                    "SYM4" => 0,
                    "SYM5" => 0,
                    "SYM6" => 0,
                    "SYM7" => 0,
                    "SYM8" => 0,
                    "SYM9" => 0,
                    "SYM10" => 0,
                    "SYM11" => 0,
                    "SYM12" => 0
                ];
                
                if($this->debug && $postData['slotEvent'] != 'freespin')
                {                 
                    $winType = 'bonus';
                }

                $reels = $slotSettings->GetReelStrips($winType, $reelName);
                $mpl = 1;
                $winline = [];

                $wildCount = 0;
                    for($r = 0; $r < 5; $r++)
                        for($c = 0; $c < 3; $c++)
                            if($reels['reel'.($r+1)][$c] == 'SYM1')
                                $wildCount++;
           
                $scatterCnt = 0;
                for($r = 1; $r <= 5; $r++)
                {
                    for($c = 0; $c < 3; $c++)
                    {
                        if($reels['reel'.$r][$c] == $scatter)
                        {
                            $scatterCnt++;
                        }
                    }
                }                
                
                //add remaining wilds
                foreach($addedWilds as $key => $value)
                {
                    $r = (int)($key / 3);
                    $c = $key % 3;
                    $reels['reel'.($r+1)][$c] = "SYM1";
                }
                $reels0 = $reels;
                
                if( (count($addedWilds) + $wildCount == 0 && rand(0, 100) < 10) && $scatterCnt == 0 || //insert monkey action with low possibility
                    ($postData['slotEvent'] == 'freespin' && $postData['freespinMode'] == 1)) 
                {
                    $monkeyR = rand(0, 4);
                    $monkeyC = rand(0, 2);
                    if($postData['slotEvent'] == 'freespin')
                    {
                        if($postData['freespinMode'] == 1)
                        {
                            $availablePos = [];                                
                            for($c = 0; $c < 3; $c++)
                            {
                                if($reels['reel2'][$c] != 'SYM1')
                                    $availablePos[] = 1 * 3 + $c;
                                if($reels['reel4'][$c] != 'SYM1')
                                    $availablePos[] = 3 * 3 + $c;
                            }
                            if(count($availablePos) > 0)
                            {
                                $pos = $availablePos[rand(0, count($availablePos) - 1)];
                                $monkeyR = (int)($pos / 3);
                                $monkeyC = $pos % 3;
                            }                                
                        }
                        else if($postData['freespinMode'] == 2)
                        {
                            //insert 3rd reel stacked wilds by random
                            $randval = rand(0, 100);
                            if( $randval < 60)
                            {
                                if($randval < 10)
                                {
                                    $reels['reel3'][0] = 'SYM1';
                                    $reels['reel3'][1] = 'SYM1';
                                    $reels['reel3'][2] = 'SYM1';
                                }
                                else if($randval < 40)
                                {
                                    $reels['reel3'][0] = 'SYM1';
                                    $reels['reel3'][1] = 'SYM1';                                        
                                }
                                else
                                {
                                    $reels['reel3'][1] = 'SYM1';
                                    $reels['reel3'][2] = 'SYM1';
                                }
                            }
                        }
                        else if($postData['freespinMode'] == 3)
                        {
                            
                        }
                    }

                    if($postData['slotEvent'] != 'freespin')
                        $monkeyR = 4;
                    if($monkeyR == 1 || $monkeyR == 3)
                    {
                        //sticky wild                            
                        $monkey['type'] = 'sw';
                        if($postData['slotEvent'] == 'freespin')
                            $monkey['type'] = 'fs1';
                        $monkey['position'] = $monkeyR * 3 + $monkeyC;
                        $reels['reel'.($monkeyR+1)][$monkeyC] = 'SYM1';                            
                    }
                    else if($monkeyR == 2)
                    {
                        //count down wild
                        $monkey['type'] = 'cd';
                        $monkey['position'] = $monkeyR * 3 + $monkeyC;
                        $reels['reel'.($monkeyR+1)][$monkeyC] = 'SYM1';
                    }
                    else if($monkeyR == 4)
                    {
                        //chest
                        $monkey['type'] = 'tc';
                        $monkey['position'] = $monkeyR * 3 + $monkeyC;
                        $reels['reel'.($monkeyR+1)][$monkeyC] = 'SYM2';
                    }                  
                }
                

                //check new wilds
                for($r = 0; $r < 5; $r++)
                for($c = 0; $c < 3; $c++)
                {
                    if($reels['reel'.($r+1)][$c] == "SYM1")
                    {
                        $pos = $r * 3 + $c;
                        if(!array_key_exists($pos, $addedWilds))
                        {
                            if($r == 1 || $r == 3)
                            {
                                //sticky wild
                                $addedWilds[$pos] = -1;
                            }
                            else if($r == 2)
                            {
                                //count down wild
                                $addedWilds[$pos] = 3;
                                if($postData['slotEvent'] == 'freespin' && $postData['freespinMode'] == 2)
                                    $addedWilds[$pos] = -2;
                            }
                        }                            
                    }
                }

                if($postData['slotEvent'] == 'freespin' && $postData['freespinMode'] == 3)
                {
                    //in starting wild freepspin mode, fix (2,1) position as wild
                    $reels['reel3'][1] = 'SYM1';
                }
                
                for( $j = 0; $j <count($slotSettings->SymbolGame); $j++ ) 
                {
                    $mpl = 1;
                    $csym = $slotSettings->SymbolGame[$j];                    
                    $mul1 = $slotSettings->getMultiplier($reels['reel1'], $csym);
                    $mul2 = $slotSettings->getMultiplier($reels['reel2'], $csym);
                    $mul3 = $slotSettings->getMultiplier($reels['reel3'], $csym);
                    $mul4 = $slotSettings->getMultiplier($reels['reel4'], $csym);
                    $mul5 = $slotSettings->getMultiplier($reels['reel5'], $csym);

                    if($mul1 > 0 && $mul2 > 0 && $mul3 > 0) //from left to right 3 symbols contained
                    {
                        $mpl = $mul1 * $mul2 * $mul3;
                        $coin = $slotSettings->Paytable[$csym][3] * $mpl * $bonusMpl;
                        $tmpWin = $coin * $betLine;
                        if($tmpWin > $cWins[$csym])
                        {
                            $cWins[$csym] = $tmpWin;                            
                            $winline = [$j + 1, $coin, $this->getPositions($csym, $reels, 3, 0), $tmpWin, $csym, 3];
                        }
                    }
                    else if($mul5 > 0 && $mul4 > 0 && $mul3 > 0) //from left to right 3 symbols contained
                    {
                        $mpl = $mul5 * $mul4 * $mul3;
                        $coin = $slotSettings->Paytable[$csym][3] * $mpl * $bonusMpl;
                        $tmpWin = $coin * $betLine;
                        if($tmpWin > $cWins[$csym])
                        {
                            $cWins[$csym] = $tmpWin;                            
                            $winline = [$j + 1, $coin, $this->getPositions($csym, $reels, 3, 1), $tmpWin, $csym, 3];
                        }
                    }

                    if($mul1 > 0 && $mul2 > 0 && $mul3 > 0 && $mul4 > 0) //from left to right 4 symbols contained
                    {
                        $mpl = $mul1 * $mul2 * $mul3 * $mul4;
                        $coin = $slotSettings->Paytable[$csym][4] * $mpl * $bonusMpl;
                        $tmpWin = $coin * $betLine;
                        if($tmpWin > $cWins[$csym])
                        {
                            $cWins[$csym] = $tmpWin;
                            $winline = [$j + 1, $coin, $this->getPositions($csym, $reels, 4, 0), $tmpWin, $csym, 4];
                        }
                    }
                    else if($mul5 > 0 && $mul4 > 0 && $mul3 > 0 && $mul2 > 0) //from left to right 4 symbols contained
                    {
                        $mpl = $mul5 * $mul4 * $mul3 * $mul2;
                        $coin = $slotSettings->Paytable[$csym][4] * $mpl * $bonusMpl;
                        $tmpWin = $coin * $betLine;
                        if($tmpWin > $cWins[$csym])
                        {
                            $cWins[$csym] = $tmpWin;
                            $winline = [$j + 1, $coin, $this->getPositions($csym, $reels, 4, 1), $tmpWin, $csym, 4];
                        }
                    }
                    if($mul1 > 0 && $mul2 > 0 && $mul3 > 0 && $mul4 > 0 && $mul5 > 0) //from left to right 5 symbols contained
                    {
                        $mpl = $mul1 * $mul2 * $mul3 * $mul4 * $mul5;
                        $coin = $slotSettings->Paytable[$csym][5] * $mpl * $bonusMpl;
                        $tmpWin = $coin * $betLine;
                        if($tmpWin > $cWins[$csym])
                        {
                            $cWins[$csym] = $tmpWin;
                            $winline = [$j + 1, $coin, $this->getPositions($csym, $reels, 5, 0), $tmpWin, $csym, 5];
                        }
                    }
                    
                    if($cWins[$csym] > 0 && !empty($winline))
                    {
                        array_push($lineWins, $winline);
                        $totalWin += $cWins[$csym];
                    }
                }

                //check chest win
                for($c = 0; $c < 3; $c++)
                {
                    if($reels['reel5'][$c] == 'SYM2')
                    {
                        //coin or freespin
                        if(rand(0, 100) < 0)
                        {
                            $chest_coin = 10 * rand(5, 20);
                            $totalWin += $chest_coin * $betLine;
                            $winline = [2, $chest_coin, '000000000000000'];
                            array_push($lineWins, $winline);
                            $chest['data'] = 'coins:' . $chest_coin;
                        }
                        else
                        {
                            
                            if($slotSettings->GetBank('bonus') > $allbet * 20 && $scatterCnt < 3)
                            {
                                if($postData['slotEvent'] != 'freespin')
                                {
                                    $freespinsWon = 6;
                                }
                                else
                                {
                                    $freespinsWon = 2;
                                }
                            }
                            else
                            {
                                $chest_coin = 10 * rand(5, 10);
                                $totalWin += $chest_coin * $betLine;
                                $winline = [2, $chest_coin, '000000000000000'];
                                array_push($lineWins, $winline);
                                $chest['data'] = 'coins:' . $chest_coin;
                            }
                        }
                    }
                }

                $scatterBase = 0;
                if($postData['slotEvent'] != 'freespin')
                {
                    //calc freespin
                    for($r = 1; $r <= 5; $r++)
                    {
                        for($c = 0; $c < 3; $c++)
                        {
                            if($reels['reel'.$r][$c] == $scatter)
                            {
                                $scatterBase++;
                            }
                        }
                    }

                    if($scatterBase > 2 && $winType != 'bonus')
                        continue;
                    if($scatterBase >= 3)
                    {
                        $freespinsWon = 6;
                        if($scatterBase == 3)
                        {
                            $scatterCoin = 0;//20;                            
                        }
                        else if($scatterBase == 4)
                        {
                            $scatterCoin = 1000;
                        }
                        else if($scatterBase >= 5)
                        {
                            $scatterCoin = 5000;
                        }
                        
                        if($scatterCoin > 0)
                        {
                            $winline = [0, $scatterCoin, $this->getPositions($scatter, $reels, 5, 0), $scatterCoin * $betLine, $scatter, 5];
                            array_push($lineWins, $winline);
                        }
                    }
                }

                $totalWin += $gameWin;
                $totalWin += $scatterCoin * $betLine;

                if($minTotalWin == -1 && $scatterBase == 0 || ($minTotalWin > $totalWin && $totalWin > 0))
                {
                    $minTotalWin = $totalWin;
                    $minLineWins = $lineWins;
                    $minReels = $reels;
                    $minAddedWilds = $addedWilds;
                    $minMonkey = $monkey;
                    $minReels0 = $reels0;
                    $minChest = $chest;
                    $minFreespinsWon = $freespinsWon;
                }

                if($this->debug)
                {
                    $spinAcquired = true;
                    break;
                }                    

                if($totalWin <= $spinWinLimit && (($totalWin > 0 && $winType != 'none') || ($winType == 'bonus' && $freespinsWon > 0)))
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
                $addedWilds = $minAddedWilds;
                $monkey = $minMonkey;
                $reels0 = $minReels0;
                $chest = $minChest;
                $freespinsWon = $minFreespinsWon;
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

            //determine next wilds
            $nextWilds = [];
            foreach($addedWilds as $key => $value)
            {
                if($value == -1)
                {
                    //enable sticky wild on winning spin only
                    if($coinWin > 0 || $postData['slotEvent'] == 'freespin')
                    {
                        if($postData['slotEvent'] == 'freespin' && $postData['freespinMode'] == 2 && ($pos == 6 || $pos == 7 || $pos == 8))
                        {
                            //in stacked wild freespin mode, the stacked wilds in the 3rd reel is not permanent, so remove from next wild
                        }
                        else
                            $nextWilds[$key] = $value;
                    }
                }
                else
                {
                    //leave count down wild if count is bigger than 0
                    if($value > 0)
                    {
                        $nextWilds[$key] = $value - 1;
                    }
                }
            }
            if($postData['slotEvent'] == 'freespin')
                $slotSettings->SetGameData($slotSettings->slotId . 'FreespinWildInfo', $nextWilds);
            else
                $slotSettings->SetGameData($slotSettings->slotId . 'WildInfo', $nextWilds);
            $slotSettings->SetGameData($slotSettings->slotId . 'CoinWin', $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') + $coinWin);
            $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', $totalWin);
            $slotSettings->SetGameData($slotSettings->slotId . 'BetCoin', $betLine);
            //nextCmds
            $nextCmds = [];
            
            $needRespin = false;
            $eventData = [
                'accC' => $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin'),
                'accWa' => number_format($slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') * $betLine, 2),                
                'reelSet' => $reelName,
                'reels' => $slotSettings->GetReelSymbol($reels),
                'rpos' => [$reels['rp'][0] - 1, $reels['rp'][1] - 1, $reels['rp'][2] - 1, $reels['rp'][3] - 1, $reels['rp'][4] - 1],
                'wonCoins' => $coinWin,
                'wtw' => $lineWins,
                'manualNoWin' => $manualNoWin,
            ];

            if($postData['slotEvent'] != 'freespin')
            {
                $eventData['addedWilds'] = $addedWilds;
                $eventData['nextWilds'] = $nextWilds;
                $eventData['thisWilds'] = $thisWilds;
            }
            else
            {
                $eventData['fsAddedWilds'] = $addedWilds;
                $eventData['fsNextWilds'] = $nextWilds;
                $eventData['fsThisWilds'] = $thisWilds;
            }

            if(count($thisWilds) > 0 || count($addedWilds) > 0 ||
                ($postData['slotEvent'] == 'freespin' && $postData['freespinMode'] == 3))
            {
                $baseStickyB = $slotSettings->GetGameData($slotSettings->slotId . 'BaseSticky');
                $baseStickyA = array_fill(0, 15, 0);
                $baseStickyN = array_fill(0, 15, 0);
                foreach($nextWilds as $key => $value)
                {
                    $baseStickyA[$key] = 1;
                }
                for($i = 0; $i < 15; $i++)
                if($baseStickyB[$i] == 0 && $baseStickyA[$i] == 1)
                    $baseStickyN[$i] = 1;

                if($postData['slotEvent'] == 'freespin' && $postData['freespinMode'] == 3)
                {
                    //starting wild freespin wild position
                    $baseStickyA[7] = 1;
                    $baseStickyN[7] = 0;
                }

                if($postData['slotEvent'] != 'freespin')
                {
                    $eventData['baseStickyB'] = implode("", $baseStickyB);
                    $eventData['baseStickyA'] = implode("", $baseStickyA);
                    $eventData['baseStickyN'] = implode("", $baseStickyN);
                }
                else
                {
                    $eventData['stickyB'] = implode("", $baseStickyB);
                    $eventData['stickyA'] = implode("", $baseStickyA);
                    $eventData['stickyN'] = implode("", $baseStickyN);
                }
                
                $slotSettings->SetGameData($slotSettings->slotId . 'BaseSticky', $baseStickyA);
            }

            if(count($monkey) > 0)
            {
                $eventData['monkey'] = $monkey['position'];
                $eventData['monkeyType'] = $monkey['type'];
                $eventData['reels0'] = $slotSettings->GetReelSymbol($reels0);
            }

            if(count($chest) > 0)
            {
                $eventData['chest'] = $chest['data'];
            }

            if($postData['slotEvent'] != 'freespin')
            {
                if($freespinsWon > 0)
                {
                    //trigger freespin
                    $this->gameState = 'Pending';
                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                    $slotSettings->SetGameData($slotSettings->slotId . 'CoinBeforeFreespin', $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin'));
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $freespinsWon);
                    $nextCmds = [1,2,3];
                }
                else
                {
                    
                }
            }
            else
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'FreespinCoinWin', $slotSettings->GetGameData($slotSettings->slotId . 'FreespinCoinWin') + $coinWin);
                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);
                if($freespinsWon > 0)
                {
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames',  $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') + $freespinsWon);                    
                }
                $freespinLeft = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                $eventData['freeSpins'] = $freespinLeft;
                if($freespinLeft > 0)
                {
                   $needRespin = true;                   
                }
                else
                {
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreespinWildInfo', []);
                    $slotSettings->SetGameData($slotSettings->slotId . 'BaseSticky', array_fill(0, 15, 0));
                }
            }

            if($freespinsWon > 0)
                $eventData['freeSpinsAwarded'] = $freespinsWon;

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
            }
           
            $prizes = null;            

            if(!empty($nextCmds))
                $eventData['nextCmds'] = implode(',', $nextCmds);

            $betData = [
                'coin' => $betLine,
                'nCoins' => 25,
                'cheat' => null,
                'clientData' => null,                
                'variant' => null
            ];
            if($cmd != '')
                $betData['cmd'] = $cmd;            

            $bet = [
                'step' => $slotSettings->GetGameData($slotSettings->slotId . 'Step'),
                'betamount' => $allbet,
                'betcurrency' => 'EUR',                
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

        

        function hasArrow($reels, $c)
        {
            if($reels['reel1'][$c] == 'ARROW' || $reels['reel2'][$c] == 'ARROW' || $reels['reel3'][$c] == 'ARROW' || $reels['reel4'][$c] == 'ARROW' || $reels['reel5'][$c] == 'ARROW')
                return true;
            else 
                return false;
        }
    }

}


