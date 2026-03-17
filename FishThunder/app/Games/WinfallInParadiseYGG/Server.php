<?php 
namespace VanguardLTE\Games\WinfallInParadiseYGG
{
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;    

    class Server
    {
        public $gameState;
        public $debug = false;
        public $lastReels;        
        
        function getPositions($csym, $reels, $cnt)
        {
            $positions = [];
            $wild = 1;
            for($r = 0; $r < $cnt; $r++)
            {
                for($c = 0; $c < 4; $c++)
                {
                    if($reels['reel'.($r+1)][$c] == $csym || $reels['reel'.($r+1)][$c] == $wild)
                    {
                        $positions[] = ['x' => $r, 'y' => $c];
                    }
                }
            }
            return $positions;
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
                        $response = file(base_path() . '/app/Games/WinfallInParadiseYGG/translation.txt')[0];
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
                            $slotSettings->SetGameData($slotSettings->slotId . 'WheelPossibility', 5);
                            $slotSettings->SetGameData($slotSettings->slotId . 'WinItAmount', 20);
                        break;                                
                    case 'clientinfo':
                        $response = '{"code":0,"data":{"id":"2203301519500100062","org":null,"gameid":0,"data":{"appsrv":"","file":"/init/","gameid":"10180","height":"600","license":"","org":"Demo","root":"","type":"Html","width":"800"}},"fn":"clientinfo","utcts":1648653590613}';
                        break;
                    case 'info':
                        $response = '{"code":0,"data":{"initialized":true,"playerState":{"state":"{\"States\":{}}"}},"fn":"info","utcts":1670246824095}';
                        break;                        
                    case 'game':
                        $filename = base_path() . '/app/Games/WinfallInParadiseYGG/game.txt';
                        $file = fopen($filename, "r" );
                        $filesize = filesize( $filename );
                        $response = fread( $file, $filesize );
                        fclose( $file );
                        break;
                    case 'restore':
                        $response = '{"code":0,"data":{"size":0,"next":"","data":[],"columns":[],"filterParams":{},"reportGenerationId":null,"header":[],"empty":true},"fn":"restore","utcts":'.time().'}';
                        break;
                    case 'play':                        
                        $postData['slotEvent'] = 'bet';                        
                        $betLine = 0;
                        $nCoins = 10;
                        if(!isset($postData['coin']))
                            $postData['coin'] = $slotSettings->GetGameData($slotSettings->slotId . 'BetCoin');
                        if(isset($postData['coin']))
                            $betLine = $postData['coin'];
                        $allbet = 0;
                        if(isset($postData['amount']))
                            $allbet = $postData['amount'];
                        else
                            $allbet = $slotSettings->GetGameData($slotSettings->slotId . 'BetAmount');
                        if( !isset($postData['slotEvent']) ) 
                        {
                            $postData['slotEvent'] = 'bet';
                        }

                        $cmd ='';
                        if (isset($postData['cmd']))
                        {
                            $cmd = $postData['cmd'];                            
                        }

                        if($cmd == 'Finished')
                        {
                            $win = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin');
                            $status = 'Finished';
                            $nextCmds = [];
                            if($win > 0)
                            {
                                $status = 'Pending';
                                $nextCmds[] = 'C';
                            }

                            $eventData = [
                                'accC' => $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin'),
                                'accWa' => number_format($win, 2),
                                'initialPlayerState' => ['state' => '{"States":{"EUR100":{"WheelYesChance":'.$slotSettings->GetGameData($slotSettings->slotId . 'WheelPossibility').',"WinItAgainMultiplier":'.($betLine * $nCoins * 20).'}}}'],
                                'playerState' => ['state' => '{"States":{"EUR100":{"WheelYesChance":'.$slotSettings->GetGameData($slotSettings->slotId . 'WheelPossibility').',"WinItAgainMultiplier":'.($betLine * $nCoins * 20).'}}}'],
                                'response' => [
                                    'cashWin' => 0,
                                    'clientData' => ['Finished' => 0],
                                    'coinWin' => '0'
                                ]
                            ];
                            if(count($nextCmds) > 0)
                                $eventData['nextCmds'] = $nextCmds;
                
                            $lastBalance = $slotSettings->GetBalance();
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
                                    'resultBal' => ['cash' =>  $lastBalance],
                                    'wager' => [
                                        'bets' => [
                                            [
                                                'step' => $slotSettings->GetGameData($slotSettings->slotId . 'Step'),
                                                'betamount' => 0,
                                                'betcurrency' => 'EUR',
                                                'wonamount' => number_format($win, 2),
                                                'status' => 'RESULTED',
                                                'betdata'=> [
                                                    'accC' => $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin'),
                                                    'accWA' => number_format($win, 2),
                                                    'cmd' => 'Finished',
                                                ],
                                                'eventdata' => $eventData,                                                
                                                'prepaid' => false
                                            ]
                                        ],
                                        'prepaid' => false,
                                        'status' => $status,
                                        'timestamp' => time(),
                                        'wagerid' => $postData['wagerid']
                                    ]
                                    ],
                                    'fn' => $reqId,
                                    'utcts' => time()
                                ];  
                            
                            $response = json_encode($ret);
                            break;
                        }


                        if($cmd == 'C')
                        {
                            $win = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin');
                            $slotSettings->SetBank($slotSettings->GetGameData($slotSettings->slotId . 'LastEvent'), -1 * $win);
                            $lastBalance = $slotSettings->GetBalance();
                            $slotSettings->SetBalance($win);
                            
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
                            $slotSettings->SaveLogReport($response, $allbet, $reportWin, $slotSettings->GetGameData($slotSettings->slotId . 'LastEvent'));                       
                            break;
                        }
                        
                        $this->gameState = 'Finished';                        
                        if($allbet > $slotSettings->GetBalance())
                        {
                            return '{"completion":"Unknown","code":1006,"errorCode":"NO_SUFFICIENT_FUNDS","type":"O","rid":"220215083220::e14db45d-39e6-4cee-a076-ebb72ca0a89b","msg":"You do not have sufficient funds for the bet","fn":null,"details":null,"relaunchUrl":null,"timeElapsed":null,"errorType":null,"balanceDifference":null,"suppressed":[]}
                            ';
                        }

                        if( $postData['slotEvent'] == 'bet' || $cmd == 'BONUS_BUY')
                        {
                            $slotSettings->SetBalance(-1 * $allbet, $postData['slotEvent']);
                            $slotSettings->UpdateJackpots($allbet);
                            $slotSettings->SetBet($allbet);                                                     
                            
                            $slotSettings->SetGameData($slotSettings->slotId . 'Step', 1);
                            $slotSettings->SetGameData($slotSettings->slotId . 'CoinWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'BetCoin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'BaseWinning', 0);
                            if($cmd == 'BONUS_BUY')
                            {
                                $slotSettings->SetGameData($slotSettings->slotId . 'WheelPossibility', 100);
                                $slotSettings->SetGameData($slotSettings->slotId . 'WinItAmount', 400);
                            }
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
                            $ret['data']['resultBal']['cash'] = $slotSettings->GetBalance();
                        }
                        $response = json_encode($ret);
                        if($allbet > 0)
                        {
                            $slotSettings->SetGameData($slotSettings->slotId . 'BetAmount', $allbet);
                            $slotSettings->SetGameData($slotSettings->slotId . 'BetCoin', $betLine);
                        }
                            
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
            if($postData['slotEvent'] == 'freespin')
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'freespin');
            }
            else
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bet');
            }
            
            $nCoins = 10;
            $betLine = 0;
            if(!isset($postData['coin']))
                $postData['coin'] = $slotSettings->GetGameData($slotSettings->slotId . 'BetCoin');
            
            $betLine = $postData['coin'];
            $allbet = $nCoins * $betLine;
            
            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $nCoins, $cmd == 'BONUS_BUY');
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];

            if($this->debug && $postData['slotEvent'] != 'freespin')
            {                 
                $winType = 'bonus';
                $spinWinLimit = 10;
            }

            $spinAcquired = false;
            $gameWin = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin');

            $minReels = [];
            $minLineWins = [];
            $minTotalWin = -1;
            $minFreespinsWon = 0;
            $minReels0 = [];
            $minScatterPos = [];
            $minWheelYesNo = [];
            $minWaveModifier = [];
            $minWindModifier = [];
            $minLastWheelPossibility = 0;

            $totalWin = 0;            
            $freespinsWon = 0;            
            $lineWins = [];
            $reels0 = [];
            $reels = [];
            $waveModifier = [];
            $windModifier = [];
            $wheelYesNo = [];

            $scatter = 11;
            $lava = 12;
            $wild = [10];
            $bonusMpl = 1;
            $mpl = 1;
            
            $linesId = $slotSettings->GetPaylines();
            $lines = count($linesId);
            $lastWheelPossibility = $slotSettings->GetGameData($slotSettings->slotId . 'WheelPossibility');
            if($cmd == 'BONUS_BUY')
            {
                $freespinsWon = 7;
                $spinAcquired = true;
            }
            else
            {
                for( $i = 0; $i <= 600; $i++ )
                {
                    $totalWin = 0;
                    $feature = 0;
                    $freespinsWon = 0;
                    $lineWins = [];
                    $waveModifier = [];
                    $windModifier = [];
                    $wheelYesNo = [];
                    $mpl = 1;
                    $reels = $slotSettings->GetReelStrips($winType);                               
                    $reels0 = $reels;

                    $cWins = array_fill(0, $lines, 0);
                    $bonusMpl = 1;
                    
                    //check lava symbol
                    $lastWheelPossibility = $slotSettings->GetGameData($slotSettings->slotId . 'WheelPossibility');
                    
                    for($c = 0; $c < 4; $c++)
                    {
                        if($reels['reel5'][$c] == $lava)
                        {
                            $lastWheelPossibility += 5;
                            $wheelYesNo = ['wave' => false, 'wind' => false, 'winItAgain' => false, 'yesChance' => $lastWheelPossibility];
                            if(rand(0, 100) <= $lastWheelPossibility || $this->debug)
                            {
                                $feature = rand(1,2);    
                            }
                            break;       
                        }
                    }

                    if($feature == 1) //wave
                    {
                        $wheelYesNo = ['wave' => true, 'wind' => false, 'winItAgain' => false, 'yesChance' => 5];
                        $wildCnt = rand(2,3);
                        $row = rand(0, 3);
                        $upgradedSymbolCoords = [];
                        for($k = 0; $k < $wildCnt; $k++)
                        {
                            $reels['reel'.($k+1)][$row] = $wild[0];
                            $upgradedSymbolCoords[] = ['reel' => $k, 'row' => $row];
                        }
                        $wildCnt = rand(1, 3);
                        while($wildCnt > 0)
                        {
                            $position = rand(0, 19);
                            $r = $position % 5;
                            $c = (int)($position / 5);
                            if($reels['reel'.($r+1)][$c] != $wild[0])
                            {
                                $reels['reel'.($r+1)][$c] = $wild[0];
                                $wildCnt--;
                                $upgradedSymbolCoords[] = ['reel' => $r, 'row' => $c];
                            }
                        }
                        $waveModifier = [
                            'stopWindow' => ['reelList' => $this->GetReelSymbols($reels)],
                            'upgradedSymbolCoords' => $upgradedSymbolCoords,
                        ];
                    }
                    else if($feature == 2) //wind
                    {
                        $wheelYesNo = ['wave' => false, 'wind' => true, 'winItAgain' => false, 'yesChance' => 5];
                        $target_sym = rand(2, 6);
                        $upgradedSymbolCoords = [];
                        for($r = 0; $r < 5; $r++)
                            for($c = 0; $c < 4; $c++)
                            {
                                if($reels['reel'.($r+1)][$c] < $target_sym || $reels['reel'.($r+1)][$c] == $lava)
                                {
                                    $reels['reel'.($r+1)][$c] = $target_sym;
                                    $upgradedSymbolCoords[] = ['reel' => $r, 'row' => $c];
                                }
                            }

                        //add 2 more wilds
                        $wildCnt = 2;
                        while($wildCnt > 0)
                        {
                            $position = rand(0, 19);
                            $r = $position % 5;
                            $c = (int)($position / 5);
                            if($reels['reel'.($r+1)][$c] != $wild[0])
                            {
                                $reels['reel'.($r+1)][$c] = $wild[0];
                                $wildCnt--;
                                $upgradedSymbolCoords[] = ['reel' => $r, 'row' => $c];
                            }
                        }

                        $windModifier = [
                            'stopWindow' => ['reelList' => $this->GetReelSymbols($reels), 'reels' => 5],
                            'upgradedSymbolCoords' => $upgradedSymbolCoords,
                            'upgradeSymbol' => $target_sym
                        ];                    
                    }
                    else if($feature == 3) //win it again
                    {
                        $wheelYesNo = ['wave' => false, 'wind' => true, 'winItAgain' => true];
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
                                    $tmpWin = $slotSettings->Paytable[$csym][3] * $betLine * $mpl * $bonusMpl;
                                    $coin = $slotSettings->Paytable[$csym][3] * $mpl * $bonusMpl;                                
                                    if( $cWins[$k] < $tmpWin ) 
                                    {
                                        $positions = [];
                                        $positions[] = ['reel' => 0, 'row' => $p0];
                                        $positions[] = ['reel' => 1, 'row' => $p1];
                                        $positions[] = ['reel' => 2, 'row' => $p2];
                                        $symbols = [
                                            'ids' => [$s[0], $s[1], $s[2]],
                                            'value' => $csym
                                        ];

                                        $cWins[$k] = $tmpWin;                                    
                                        $winline = [$k + 1, $coin, $tmpWin, $symbols, $positions, 3];
                                    }
                                }
                                if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) ) 
                                {
                                    $tmpWin = $slotSettings->Paytable[$csym][4] * $betLine * $mpl * $bonusMpl;
                                    $coin = $slotSettings->Paytable[$csym][4] * $mpl * $bonusMpl;
                                    if( $cWins[$k] < $tmpWin ) 
                                    {
                                        $positions = [];
                                        $positions[] = ['reel' => 0, 'row' => $p0];
                                        $positions[] = ['reel' => 1, 'row' => $p1];
                                        $positions[] = ['reel' => 2, 'row' => $p2];
                                        $positions[] = ['reel' => 3, 'row' => $p3];
                                        $symbols = [
                                            'ids' => [$s[0], $s[1], $s[2], $s[3]],
                                            'value' => $csym
                                        ];

                                        $cWins[$k] = $tmpWin;                                    
                                        $winline = [$k + 1, $coin, $tmpWin, $symbols, $positions, 4];
                                    }
                                }
                                if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) ) 
                                {
                                    $tmpWin = $slotSettings->Paytable[$csym][5] * $betLine * $mpl * $bonusMpl;
                                    $coin = $slotSettings->Paytable[$csym][5] * $mpl * $bonusMpl;
                                    if( $cWins[$k] < $tmpWin )
                                    {
                                        $positions = [];
                                        $positions[] = ['reel' => 0, 'row' => $p0];
                                        $positions[] = ['reel' => 1, 'row' => $p1];
                                        $positions[] = ['reel' => 2, 'row' => $p2];
                                        $positions[] = ['reel' => 3, 'row' => $p3];
                                        $positions[] = ['reel' => 4, 'row' => $p4];
                                        $symbols = [
                                            'ids' => [$s[0], $s[1], $s[2], $s[3], $s[4]],
                                            'value' => $csym
                                        ];

                                        $cWins[$k] = $tmpWin;                                    
                                        $winline = [$k + 1, $coin, $tmpWin, $symbols, $positions, 5];
                                    }
                                }
                            }
                        }

                        if( $cWins[$k] > 0 && !empty($winline))
                        {
                            array_push($lineWins, $winline);
                            $totalWin += $cWins[$k];
                        }
                    }

                    //calc freespin
                    $freespinsWon = 0;
                    $scatterPos = [];
                    $scatterCnt = 0;
                    for($r = 0; $r < 5; $r++)
                        for($c = 0; $c < 4; $c++)
                        {
                            if($reels['reel'.($r+1)][$c] == $scatter)
                            {
                                $scatterCnt++;
                                $scatterPos[] = $r;
                            }
                        }
                    if( $scatterCnt >= 3 && $winType != 'bonus' && $postData['slotEvent'] == 'bet')
                        continue;
                    if ($scatterCnt < 3 && $winType == 'bonus')
                        continue;

                    if($scatterCnt >= 3)
                    {
                        $freespinsWon = 7;                    
                    }

                    if($minTotalWin == -1 || ($minTotalWin > $totalWin && $totalWin > 0))
                    {
                        $minTotalWin = $totalWin;
                        $minLineWins = $lineWins;
                        $minFreespinsWon = $freespinsWon;
                        $minReels = $reels;
                        $minReels0 = $reels0;
                        $minScatterPos = $scatterPos;
                        $minWheelYesNo = $wheelYesNo;
                        $minWaveModifier = $waveModifier;
                        $minWindModifier = $windModifier;
                        $minLastWheelPossibility = $lastWheelPossibility;
                    }

                    if($this->debug)
                    {
                        $spinAcquired = true;
                        break;
                    }

                    if($totalWin <= $spinWinLimit && (($totalWin > 0 && $winType != 'none') || ($winType == 'bonus' && $freespinsWon >= 3) ))
                    {
                        $spinAcquired = true;
                        if($totalWin < 0.7 * $spinWinLimit && $winType != 'bonus')
                            $spinAcquired = false;
                        if($spinAcquired)
                            break;
                    }
                    else if( $winType == 'none' && $totalWin == $gameWin ) 
                    {
                        break;
                    }
                }
            }

            $manualNoWin = false;
            if(!$spinAcquired && $totalWin > $gameWin && $winType != 'none')
            {                
                $manualNoWin = true;                
                $reels = $minReels;
                $reels0 = $minReels0;
                $lineWins = $minLineWins;
                $totalWin = $minTotalWin;
                $freespinsWon = $minFreespinsWon;
                $scatterPos = $minScatterPos;
                $wheelYesNo = $minWheelYesNo;
                $waveModifier = $minWaveModifier;
                $windModifier = $minWindModifier;
                $lastWheelPossibility = $minLastWheelPossibility;
            }

            $slotSettings->SetGameData($slotSettings->slotId . 'WheelPossibility', $lastWheelPossibility);
            if( (isset($wheelYesNo['wind']) && $wheelYesNo['wind'] == true) || (isset($wheelYesNo['wave']) && $wheelYesNo['wave'] == true) || (isset($wheelYesNo['winItAgain']) && $wheelYesNo['winItAgain'] == true))
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'WheelPossibility', 5);
            }
            
            $coinWin = 0; //coins won            
            $freespinLeft = 0;

            if($postData['slotEvent'] == 'bet')
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'BaseWinning', $totalWin);
                $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', $totalWin);
                if($freespinsWon > 0)
                {
                    //trigger freespin
                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreespinWinCoin', 0);                    
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames',  $freespinsWon);
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreespinGuaranteedWin',  $allbet);
                    $postData['slotEvent'] = 'freespin';
                    $this->gameState = 'Pending';  
                    $freespinLeft = $freespinsWon;
                }
            }
            
            //check win it amount
            $winitIncreased = false;
            if($totalWin > $slotSettings->GetGameData($slotSettings->slotId . 'WinItAmount'))
            {
                $winitIncreased = true;
                $slotSettings->SetGameData($slotSettings->slotId . 'WinItAmount', $totalWin);
            }
            $winItAmount = $slotSettings->GetGameData($slotSettings->slotId . 'WinItAmount');

            $FreeSpins = [];
            $freespinWin = 0;

            if($freespinLeft > 0)
            {
                $freeSpins = [];
                while($freespinLeft > 0)
                {
                    $this->doSubSpin($slotSettings, $postData, $freeSpins, $freespinWin);
                    $freespinLeft = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                }
                $FreeSpins = [
                    'freeSpins' => $freeSpins,
                    'startingCashWinAmount' => (int)($totalWin * 100),
                    'startingMinimumWin' => $allbet * 100,
                    'startingNumberOfSpins'=> $freespinsWon,
                    'totalCashWinAmount'=> ($freespinWin + $totalWin) * 100,
                    'totalNumberOfSpins' => $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames')
                ];
            }

            $winningLines = [];
            if(!empty($lineWins))
            {
                foreach($lineWins as $winline)
                {
                    $winningLines[] = [
                        'occurrences' => $winline[5],
                        'positions' => $winline[4],
                        'symbol' => $winline[3],
                        'winAmount'=> (int)($winline[2] * 100),
                        'winningLine' => $winline[0]
                    ];
                }
            }            

            $slotSettings->SetGameData($slotSettings->slotId . 'CoinWin', $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') + $coinWin);            
            $slotSettings->SetWin($totalWin);
            //nextCmds

            $needRespin = false;
            $eventData = [
                'accC' => $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin'),
                'accWa' => number_format($slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') * $betLine, 2),                
                'manualNoWin' => $manualNoWin,
            ];
            $clientData = [];
            if($cmd != 'BONUS_BUY')
            {
                $BaseGame = [
                    'Spin' => [
                        'spinEvent' => [
                            'lines' => 20,
                            'reelSetName' => 'Base',
                            'resultsPanel' => [
                                'reelList' => $this->GetReelSymbols($reels0),
                                'reels' => '5',
                            ],
                            'totalWinnings' => (int)(($totalWin + $freespinWin) * 100)
                        ],
                        'winningLinesEvent' => [
                            'totalLineWinnings'=> (int)($totalWin * 100),
                            'winningLines' => $winningLines
                        ]
                        ],
                    'WinItAgainEvaluation' => [
                        'amountNow' => (int)($winItAmount * 100),
                        'hasIncreased' => $winitIncreased
                    ]
                ];
                
                if(count($wheelYesNo) > 0)
                    $BaseGame['WheelYesNo'] = $wheelYesNo;
    
                if(count($waveModifier) > 0)
                {
                    $waveModifier['winAmount'] = $totalWin * 100;
                    $waveModifier['winningLines'] = $winningLines;
                    $BaseGame['WaveModifier'] = $waveModifier;
                }
                if(count($windModifier) > 0)
                {
                    $windModifier['winAmount'] = (int)($totalWin * 100);
                    $windModifier['winningLines'] = [
                                                    'totalLineWinnings' => (int)($totalWin * 100),
                                                    'winningLines' => $winningLines
                                                    ];
                    $BaseGame['WindModifier'] = $windModifier;
                }
    
                $clientData['BaseGame'] = $BaseGame;
            }

            if(count($FreeSpins) > 0)
                $clientData['FreeSpins'] = $FreeSpins;
           
            $eventData['response'] = [                
                'cashWin' => number_format($slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') * $betLine, 2),
                'clientData' => $clientData,
                'coinWin'=> $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin')
            ];
            $eventData['initialPlayerState'] = [
                'state' => '{"States":{"EUR100":{"WheelYesChance":'.$lastWheelPossibility.',"WinItAgainMultiplier":400}}}'
            ];
            $eventData['playerState'] = [
                'state' => '{"States":{"EUR100":{"WheelYesChance":'.$slotSettings->GetGameData($slotSettings->slotId . 'WheelPossibility').',"WinItAgainMultiplier":400}}}'
            ];

            if($needRespin)
            {
                $this->gameState = 'Pending';
            }
            else
            {
                if($totalWin > 0)
                {
                    $this->gameState = 'Pending';
                    if($freespinLeft == 0)
                    {
                        // $nextCmds[] = 'C';
                    }
                }
            }
           
            $prizes = null;            

            // if(!empty($nextCmds))
                $eventData['nextCmds'] = 'Finished,Checkpoint';

            $betData = [
                'coin' => $betLine,
                'cheat' => null,                
                'variant' => null
            ];
            if($cmd != '')
                $betData['cmd'] = $cmd;            

            $bet = [
                'step' => $slotSettings->GetGameData($slotSettings->slotId . 'Step'),
                'betamount' => $postData['amount'],
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

        function doSubSpin($slotSettings, &$postData, &$bets, &$freespinWin)
        {
            $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'freespin');
            $nCoins = 10;
            $betLine = 0;
            if(!isset($postData['coin']))
                $postData['coin'] = $slotSettings->GetGameData($slotSettings->slotId . 'BetCoin');
            
            $betLine = $postData['coin'];
            
            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $nCoins);
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
            $freespinLeft = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
            $spinWinLimit = $spinWinLimit / $freespinLeft;
            if($this->debug)
            {                 
                $winType = 'win';
                $spinWinLimit = 10;
            }
            $spinAcquired = false;
            $gameWin = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin');

            $minReels = [];
            $minLineWins = [];
            $minTotalWin = -1;
            $minFreespinsWon = 0;
            $minReels0 = [];
            $minScatterPos = [];
            $minWheelYesNo = [];
            $minWaveModifier = [];
            $minWindModifier = [];
            $minLastWheelPossibility = 0;

            $totalWin = 0;
            $freespinsWon = 0;            
            $lineWins = [];
            $reels0 = [];
            $reels = [];
            $waveModifier = [];
            $windModifier = [];

            $scatter = 11;
            $wild = [10];
            $lava = 12;
            $bonusMpl = 1;
            $mpl = 1;
            
            $linesId = $slotSettings->GetPaylines();
            $lines = count($linesId);
            $guaranteeWin = $slotSettings->GetGameData($slotSettings->slotId . 'FreespinGuaranteedWin');
            for( $i = 0; $i <= 600; $i++ )
            {
                $totalWin = 0;
                $feature = 0;
                $freespinsWon = 0;
                $lineWins = [];
                $waveModifier = [];
                $windModifier = [];
                $wheelYesNo = [];
                $mpl = 1;
                $reels = $slotSettings->GetReelStrips($winType);
                $cWins = array_fill(0, $lines, 0);
                $bonusMpl = 1;
               
                $reels0 = $reels;
                //check lava symbol
                $lastWheelPossibility = $slotSettings->GetGameData($slotSettings->slotId . 'WheelPossibility');
                
                for($c = 0; $c < 4; $c++)
                {
                    if($reels['reel5'][$c] == $lava)
                    {
                        $lastWheelPossibility += 5;
                        $wheelYesNo = ['wave' => false, 'wind' => false, 'winItAgain' => false, 'yesChance' => $lastWheelPossibility];
                        if(rand(0, 100) <= $lastWheelPossibility)
                        {
                            $feature = rand(1,2);    
                            if($freespinLeft == 6)
                                $feature = 1;
                            else if($freespinLeft == 5)
                                $feature = 2;
                        }
                        break;       
                    }
                }

                if($feature == 1) //wave
                {
                    $wheelYesNo = ['wave' => true, 'wind' => false, 'winItAgain' => false, 'yesChance' => 5];
                    $wildCnt = rand(2,3);
                    $row = rand(0, 3);
                    $upgradedSymbolCoords = [];
                    for($k = 0; $k < $wildCnt; $k++)
                    {
                        $reels['reel'.($k+1)][$row] = $wild[0];
                        $upgradedSymbolCoords[] = ['reel' => $k, 'row' => $row];
                    }
                    $wildCnt = rand(1, 3);
                    while($wildCnt > 0)
                    {
                        $position = rand(0, 19);
                        $r = $position % 5;
                        $c = (int)($position / 5);
                        if($reels['reel'.($r+1)][$c] != $wild[0])
                        {
                            $reels['reel'.($r+1)][$c] = $wild[0];
                            $wildCnt--;
                            $upgradedSymbolCoords[] = ['reel' => $r, 'row' => $c];
                        }
                    }
                    $waveModifier = [
                        'stopWindow' => ['reelList' => $this->GetReelSymbols($reels)],
                        'upgradedSymbolCoords' => $upgradedSymbolCoords,
                    ];
                }
                else if($feature == 2) //wind
                {
                    $wheelYesNo = ['wave' => false, 'wind' => true, 'winItAgain' => false, 'yesChance' => 5];
                    $target_sym = rand(2, 6);
                    $upgradedSymbolCoords = [];
                    for($r = 0; $r < 5; $r++)
                        for($c = 0; $c < 4; $c++)
                        {
                            if($reels['reel'.($r+1)][$c] < $target_sym || $reels['reel'.($r+1)][$c] == $lava)
                            {
                                $reels['reel'.($r+1)][$c] = $target_sym;
                                $upgradedSymbolCoords[] = ['reel' => $r, 'row' => $c];
                            }
                        }

                    //add 2 more wilds
                    $wildCnt = 2;
                    while($wildCnt > 0)
                    {
                        $position = rand(0, 19);
                        $r = $position % 5;
                        $c = (int)($position / 5);
                        if($reels['reel'.($r+1)][$c] != $wild[0])
                        {
                            $reels['reel'.($r+1)][$c] = $wild[0];
                            $wildCnt--;
                            $upgradedSymbolCoords[] = ['reel' => $r, 'row' => $c];
                        }
                    }

                    $windModifier = [
                        'stopWindow' => ['reelList' => $this->GetReelSymbols($reels), 'reels' => 5],
                        'upgradedSymbolCoords' => $upgradedSymbolCoords,
                        'upgradeSymbol' => $target_sym
                    ];                    
                }
                else if($feature == 3) //win it again
                {
                    $wheelYesNo = ['wave' => false, 'wind' => true, 'winItAgain' => true];
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
                                $tmpWin = $slotSettings->Paytable[$csym][3] * $betLine * $mpl * $bonusMpl;
                                $coin = $slotSettings->Paytable[$csym][3] * $mpl * $bonusMpl;                                
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $positions = [];
                                    $positions[] = ['reel' => 0, 'row' => $p0];
                                    $positions[] = ['reel' => 1, 'row' => $p1];
                                    $positions[] = ['reel' => 2, 'row' => $p2];
                                    $symbols = [
                                        'ids' => [$s[0], $s[1], $s[2]],
                                        'value' => $csym
                                    ];

                                    $cWins[$k] = $tmpWin;                                    
                                    $winline = [$k + 1, $coin, $tmpWin, $symbols, $positions, 3];
                                }
                            }
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) ) 
                            {
                                $tmpWin = $slotSettings->Paytable[$csym][4] * $betLine * $mpl * $bonusMpl;
                                $coin = $slotSettings->Paytable[$csym][4] * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $positions = [];
                                    $positions[] = ['reel' => 0, 'row' => $p0];
                                    $positions[] = ['reel' => 1, 'row' => $p1];
                                    $positions[] = ['reel' => 2, 'row' => $p2];
                                    $positions[] = ['reel' => 3, 'row' => $p3];
                                    $symbols = [
                                        'ids' => [$s[0], $s[1], $s[2], $s[3]],
                                        'value' => $csym
                                    ];

                                    $cWins[$k] = $tmpWin;                                    
                                    $winline = [$k + 1, $coin, $tmpWin, $symbols, $positions, 4];
                                }
                            }
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) ) 
                            {
                                $tmpWin = $slotSettings->Paytable[$csym][5] * $betLine * $mpl * $bonusMpl;
                                $coin = $slotSettings->Paytable[$csym][5] * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin )
                                {
                                    $positions = [];
                                    $positions[] = ['reel' => 0, 'row' => $p0];
                                    $positions[] = ['reel' => 1, 'row' => $p1];
                                    $positions[] = ['reel' => 2, 'row' => $p2];
                                    $positions[] = ['reel' => 3, 'row' => $p3];
                                    $positions[] = ['reel' => 4, 'row' => $p4];
                                    $symbols = [
                                        'ids' => [$s[0], $s[1], $s[2], $s[3], $s[4]],
                                        'value' => $csym
                                    ];

                                    $cWins[$k] = $tmpWin;                                    
                                    $winline = [$k + 1, $coin, $tmpWin, $symbols, $positions, 5];
                                }
                            }
                        }
                    }

                    if( $cWins[$k] > 0 && !empty($winline))
                    {
                        array_push($lineWins, $winline);
                        $totalWin += $cWins[$k];
                    }
                }

                //calc freespin
                $freespinsWon = 0;
                $scatterPos = [];
                $scatterCnt = 0;
                for($r = 0; $r < 5; $r++)
                    for($c = 0; $c < 4; $c++)
                    {
                        if($reels['reel'.($r+1)][$c] == $scatter)
                        {
                            $scatterCnt++;
                            $scatterPos[] = $r;
                        }
                    }
                if( $scatterCnt >= 3 && $winType != 'bonus' && $postData['slotEvent'] == 'bet')
                    continue;
                if ($scatterCnt < 3 && $winType == 'bonus')
                    continue;

                if($scatterCnt >= 3 )
                {
                    if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') < 35)
                        $freespinsWon = 7;
                    else
                        $totalWin += $betLine * $nCoins * 10;
                }

                if($minTotalWin == -1 || ($minTotalWin > $totalWin && $totalWin > 0))
                {
                    $minTotalWin = $totalWin;
                    $minLineWins = $lineWins;
                    $minFreespinsWon = $freespinsWon;
                    $minReels = $reels;
                    $minReels0 = $reels0;
                    $minScatterPos = $scatterPos;
                    $minWheelYesNo = $wheelYesNo;
                    $minWaveModifier = $waveModifier;
                    $minWindModifier = $windModifier;
                    $minLastWheelPossibility = $lastWheelPossibility;
                }

                if($totalWin <= $spinWinLimit && (($totalWin > 0 && $winType != 'none') || ($winType == 'bonus' && $freespinsWon >= 3) ))
                {
                    $spinAcquired = true;                    
                    break;
                }
                else if( $winType == 'none' && $totalWin == $gameWin ) 
                {
                    break;
                }
            }

            if(!$spinAcquired && $totalWin > $gameWin && $winType != 'none')
            {                
                $reels = $minReels;
                $reels0 = $minReels0;
                $lineWins = $minLineWins;
                $totalWin = $minTotalWin;
                $freespinsWon = $minFreespinsWon;
                $scatterPos = $minScatterPos;
                $wheelYesNo = $minWheelYesNo;
                $waveModifier = $minWaveModifier;
                $windModifier = $minWindModifier;
                $lastWheelPossibility = $minLastWheelPossibility;
            }
    
            
            $freespinLeft = 0;
            $slotSettings->SetGameData($slotSettings->slotId . 'WheelPossibility', $lastWheelPossibility);
            if( (isset($wheelYesNo['wind']) && $wheelYesNo['wind'] == true) || (isset($wheelYesNo['wave']) && $wheelYesNo['wave'] == true) || (isset($wheelYesNo['winItAgain']) && $wheelYesNo['winItAgain'] == true))
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'WheelPossibility', 5);
            }

            $winningLines = [];
            $coinWin = 0; //coins won
            if(!empty($lineWins))
            {
                foreach($lineWins as $winline)
                {
                    $winningLines[] = [
                        'occurrences' => $winline[5],
                        'positions' => $winline[4],
                        'symbol' => $winline[3],
                        'winAmount'=> $winline[2] * 100,
                        'winningLine' => $winline[0]
                    ];
                }
            }

            $realWin = $totalWin;
            if($totalWin < $guaranteeWin)
                $totalWin = $guaranteeWin;
            $guaranteeWinIncreased = false;
            if($totalWin > $guaranteeWin)
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'FreespinGuaranteedWin', $totalWin);
                $guaranteeWinIncreased = true;
                $guaranteeWin = $totalWin;
            }

            //check win it amount
            $winitIncreased = false;
            if($totalWin > $slotSettings->GetGameData($slotSettings->slotId . 'WinItAmount'))
            {
                $winitIncreased = true;
                $slotSettings->SetGameData($slotSettings->slotId . 'WinItAmount', $totalWin);
            }
            $winItAmount = $slotSettings->GetGameData($slotSettings->slotId . 'WinItAmount');

            $freespinWin += $totalWin;
            $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', $slotSettings->GetGameData($slotSettings->slotId . 'GameWin') + $totalWin);
            $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);

            $retrigger = [];
            if($freespinsWon > 0)
            {
                $retrigger = [
                    'retriggerBonusSpins' => $freespinsWon,
                    'retriggerBonusWin' => 0
                ];
                $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') + $freespinsWon);
            }   

            $freespinLeft = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');

            $slotSettings->SetGameData($slotSettings->slotId . 'CoinWin', $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') + $coinWin);            
            $slotSettings->SetWin($totalWin);
            
            $bet = [
                'MinimumWinEvaluation' => ['amountNow' => $guaranteeWin * 100, 'hasIncreased' => $guaranteeWinIncreased],
                'MinimumWinModifier' => ['winAmount' => $guaranteeWin * 100],
                'Spin' => [
                    'spinEvent' => [
                        'lines' => 20,
                        'resultsPanel' => [
                            'reelList' => $this->GetReelSymbols($reels0),
                            'reels' => '5',
                        ],
                        'totalWinnings' => (int)($realWin * 100)
                    ],
                    'winningLinesEvent' => [
                        'totalLineWinnings'=> (int)($realWin * 100),
                        'winningLines' => $winningLines
                    ]
                ],
                'SpinsRemaining' => $freespinLeft,
                'WinAmount' => (int)($totalWin * 100),
                'WinAmountSoFar'=> (int)($slotSettings->GetGameData($slotSettings->slotId . 'GameWin') * 100),
                'WinItAgainEvaluation' => [
                    'amountNow' => (int)($winItAmount * 100),
                    'hasIncreased' => $winitIncreased
                ]
            ];

            if(count($retrigger) > 0)
                $bet['Retrigger'] = $retrigger;

            if(count($wheelYesNo) > 0)
                $bet['WheelYesNo'] = $wheelYesNo;

            if(count($waveModifier) > 0)
            {
                $waveModifier['winAmount'] = $realWin * 100;
                $waveModifier['winningLines'] = $winningLines;
                $bet['WaveModifier'] = $waveModifier;
            }
            if(count($windModifier) > 0)
            {
                $windModifier['winAmount'] = (int)($realWin * 100);
                $windModifier['winningLines'] = [
                                                'totalLineWinnings' => (int)($realWin * 100),
                                                'winningLines' => $winningLines
                                                ];
                $bet['WindModifier'] = $windModifier;
            }
            $bets[] = $bet;
        }

        function GetReelArray($reels)
        {
            $res = [];
            for($r = 0; $r < 6; $r++)
                $res[] = $reels['reel'.($r+1)];
            return $res;
        }

        function GetReelSymbols($reels)
        {
            $symbolArr = [];
            for($r = 0; $r < 5; $r++)
            {
                $values = [];
                for($c = 0; $c < 4; $c++)
                    $values[] = ['value' => $reels['reel'.($r+1)][$c]];
                $symbolArr[] = ['symbols' => $values];
            }
            return $symbolArr;
        }

        function GetPos($reel, $sym)
        {
            for($c = 0; $c < 4; $c++)
            {
                if($reel[$c] == $sym || $reel[$c] == 1)
                {
                    return $c;
                }
            }
            return -1;
        }
    }

}


