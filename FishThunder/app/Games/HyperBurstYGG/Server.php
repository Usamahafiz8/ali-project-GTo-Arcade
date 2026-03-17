<?php 
namespace VanguardLTE\Games\HyperBurstYGG
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
                        $response = file(base_path() . '/app/Games/HyperBurstYGG/translation.txt')[0];                                                                          
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
                        $response = '{"code":0,"data":{"id":"2203301519500100062","org":null,"gameid":0,"data":{"appsrv":"","file":"/init/","gameid":"7392","height":"600","license":"","org":"Demo","root":"","type":"Html","width":"800"}},"fn":"clientinfo","utcts":1648653590613}';
                        break;
                    case 'game':
                        $filename = base_path() . '/app/Games/HyperBurstYGG/game.txt';
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
                            $curCoinWin = $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin');
                            $curBetCoin = $slotSettings->GetGameData($slotSettings->slotId . 'BetCoin');
                            $win = $curCoinWin * $curBetCoin;
                            $slotSettings->SetBank('bet', -1 * $win);
                            $slotSettings->SetWin($win);

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
                            if($slotSettings->GetGameData($slotSettings->slotId . 'LastEvent') === 'bonus')
                                $postData['slotEvent'] = 'freespin';
                            $slotSettings->SaveLogReport($response, $allbet, $reportWin, $postData['slotEvent']);                       
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
                            $slotSettings->SetGameData($slotSettings->slotId . 'BetCoin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'ConsecutiveWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'WinMultiplier', 1);
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
            $linesId = $slotSettings->GetPaylines();

            $lines = count($linesId);
            $nCoins = 25;
            $betLine = 0;
            if(isset($postData['coin']))
                $betLine = $postData['coin'];
            
            $allbet = $betLine * $nCoins;
            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $nCoins);
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
            
            $spinAcquired = false;
            $gameWin = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin');
            if($postData['slotEvent'] == 'freespin')
            {
                $spinWinLimit -= $gameWin;
                if($spinWinLimit < 0)
                {
                    $winType = 'none';
                    $spinWinLimit = 0;
                }
            }

                
            $minReels = [];
            $minLineWins = [];
            $minTotalWin = -1;
            $minFreespinsWon = 0;
            $minReels0 = [];

            $totalWin = 0;            
            $freespinsWon = 0;            
            $lineWins = [];
            $reels0 = [];
            $reels = [];

            $wild = ["WILD"];
            $bonusMpl = 1;
            $mpl = 1;
            $consecutiveWins = $slotSettings->GetGameData($slotSettings->slotId . 'ConsecutiveWin');

            for( $i = 0; $i <= 600; $i++ ) 
            {                
                $totalWin = 0;                
                $freespinsWon = 0;
                $lineWins = [];
                
                $cWins = array_fill(0, $lines, 0);
                
                if($this->debug && $postData['slotEvent'] != 'freespin')
                {                 
                    $winType = 'bonus';
                }
                $mpl = 1;
                $reels = $slotSettings->GetReelStrips($consecutiveWins);
                $reels0 = $reels;
                
                if($postData['slotEvent'] == 'freespin')
                {
                    $mpl = $slotSettings->GetGameData($slotSettings->slotId . 'WinMultiplier');
                }
                for( $k = 0; $k < $lines; $k++ )
                {
                    $winline = [];
                    for( $j = 0; $j < count($slotSettings->SymbolGame); $j++ ) 
                    {
                        $csym = $slotSettings->SymbolGame[$j];
                       
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
                                                                            
                        if( ($s[0] == $csym || in_array($s[0], $wild)) &&
                            ($s[1] == $csym || in_array($s[1], $wild)) &&
                            ($s[2] == $csym || in_array($s[2], $wild)) ) 
                        {
                            $emptyLine = [[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0]];
                            $tmpWin = $slotSettings->Paytable[$csym][3] * $betLine * $mpl * $bonusMpl;
                            $coin = $slotSettings->Paytable[$csym][3] * $mpl * $bonusMpl;
                            if( $cWins[$k] < $tmpWin ) 
                            {
                                $cWins[$k] = $tmpWin;
                                $emptyLine[0][$p0] = 1;
                                $emptyLine[1][$p1] = 1;
                                $emptyLine[2][$p2] = 1;
                                $winline = [$k + 1, $coin, $this->getConvertedLine($emptyLine)]; //[lineId, coinWon, winPositions]
                            }
                        }
                        if( ($s[0] == $csym || in_array($s[0], $wild)) &&
                            ($s[1] == $csym || in_array($s[1], $wild)) &&
                            ($s[2] == $csym || in_array($s[2], $wild)) && 
                            ($s[3] == $csym || in_array($s[3], $wild)) ) 
                        {
                            $emptyLine = [[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0]];
                            $tmpWin = $slotSettings->Paytable[$csym][4] * $betLine * $mpl * $bonusMpl;
                            $coin = $slotSettings->Paytable[$csym][4] * $mpl * $bonusMpl;
                            if( $cWins[$k] < $tmpWin ) 
                            {
                                $cWins[$k] = $tmpWin;
                                $emptyLine[0][$p0] = 1;
                                $emptyLine[1][$p1] = 1;
                                $emptyLine[2][$p2] = 1;
                                $emptyLine[3][$p3] = 1;
                                $winline = [$k + 1, $coin, $this->getConvertedLine($emptyLine)]; //[lineId, coinWon, winPositions]                                                             
                            }
                        }
                        if( ($s[0] == $csym || in_array($s[0], $wild)) &&
                            ($s[1] == $csym || in_array($s[1], $wild)) &&
                            ($s[2] == $csym || in_array($s[2], $wild)) &&
                            ($s[3] == $csym || in_array($s[3], $wild)) &&
                            ($s[4] == $csym || in_array($s[4], $wild)) ) 
                        {
                            $emptyLine = [[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0]];
                            $tmpWin = $slotSettings->Paytable[$csym][5] * $betLine * $mpl * $bonusMpl;
                            $coin = $slotSettings->Paytable[$csym][5] * $mpl * $bonusMpl;
                            if( $cWins[$k] < $tmpWin )
                            {
                                $cWins[$k] = $tmpWin;
                                $emptyLine[0][$p0] = 1;
                                $emptyLine[1][$p1] = 1;
                                $emptyLine[2][$p2] = 1;
                                $emptyLine[3][$p3] = 1;
                                $emptyLine[4][$p4] = 1;
                                $winline = [$k + 1, $coin, $this->getConvertedLine($emptyLine)]; //[lineId, coinWon, winPositions]                                                            
                            }
                        }
                        if( ($s[0] == $csym || in_array($s[0], $wild)) && 
                            ($s[1] == $csym || in_array($s[1], $wild)) && 
                            ($s[2] == $csym || in_array($s[2], $wild)) && 
                            ($s[3] == $csym || in_array($s[3], $wild)) && 
                            ($s[4] == $csym || in_array($s[4], $wild)) && 
                            ($s[5] == $csym || in_array($s[5], $wild)) ) 
                        {
                            $emptyLine = [[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0]];
                            $tmpWin = $slotSettings->Paytable[$csym][6] * $betLine * $mpl * $bonusMpl;
                            $coin = $slotSettings->Paytable[$csym][6] * $mpl * $bonusMpl;
                            if( $cWins[$k] < $tmpWin )
                            {
                                $cWins[$k] = $tmpWin;
                                $emptyLine[0][$p0] = 1;
                                $emptyLine[1][$p1] = 1;
                                $emptyLine[2][$p2] = 1;
                                $emptyLine[3][$p3] = 1;
                                $emptyLine[4][$p4] = 1;
                                $emptyLine[5][$p5] = 1;
                                $winline = [$k + 1, $coin, $this->getConvertedLine($emptyLine)]; //[lineId, coinWon, winPositions]                                                            
                            }
                        }
                    }

                    if( $cWins[$k] > 0 && !empty($winline))
                    {
                        array_push($lineWins, $winline);
                        $totalWin += $cWins[$k];
                    }
                }

                $totalWin += $gameWin;

                if($totalWin > $gameWin)
                {
                    $freespinsWon = 1;
                }

                if($minTotalWin == -1 || ($minTotalWin > $totalWin && $totalWin > 0))
                {
                    $minTotalWin = $totalWin;
                    $minLineWins = $lineWins;
                    $minFreespinsWon = $freespinsWon;
                    $minReels = $reels;
                    $minReels0 = $reels0;
                }

                if($this->debug)
                {
                    $spinAcquired = true;
                    break;
                }                    

                if($totalWin <= $spinWinLimit && (($totalWin > 0 && $winType != 'none')))
                {
                    $spinAcquired = true;
                    if($totalWin < 0.3 * $spinWinLimit && $winType != 'bonus')
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
            if(!$spinAcquired && $totalWin > $gameWin)
            {                
                // if($postData['slotEvent'] == "freespin")
                {
                    $manualNoWin = true;                
                    $reels = $minReels;
                    $reels0 = $minReels0;
                    $lineWins = $minLineWins;
                    $totalWin = $minTotalWin;
                    $freespinsWon = $minFreespinsWon;
                }
                // else
                // {
                //     $manualNoWin = true;
                //     $reels = $slotSettings->GetNoWinSpin($consecutiveWins);
                //     $lineWins = [];
                //     $totalWin = $gameWin;
                //     $freespinsWon = 0;
                // }
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

            $slotSettings->SetGameData($slotSettings->slotId . 'CoinWin', $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') + $coinWin);
            $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', $totalWin);
            $slotSettings->SetGameData($slotSettings->slotId . 'BetCoin', $betLine);
            //nextCmds
            $nextCmds = [];

            $needRespin = false;
            $eventData = [
                'accC' => $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin'),
                'accWa' => number_format($slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') * $betLine, 2),                
                'finalBoard' => $slotSettings->GetReelSymbol($reels),
                'reels' => $slotSettings->GetReelSymbol($reels0),
                'rpos' => [$reels['rp'][0] - 1, $reels['rp'][1] - 1, $reels['rp'][2] - 1, $reels['rp'][3] - 1, $reels['rp'][4] - 1, $reels['rp'][5] - 1],
                'wonCoins' => $coinWin,
                'wonMoney' => number_format($coinWin * $betLine, 2),
                'wtw' => $lineWins,
                'reelsStripesNames' => $reels['stripNames'],
                'manualNoWin' => $manualNoWin,
                'multiplier' => 1
            ];

            if($postData['slotEvent'] == 'bet')
            {
                if($freespinsWon > 0)
                {
                    //trigger freespin
                    $eventData['freeSpinsAwarded'] = $freespinsWon;                    
                    $eventData['freeSpins'] = $freespinsWon;
                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $freespinsWon);                    
                    $slotSettings->SetGameData($slotSettings->slotId . 'WinMultiplier', 1);
                    $slotSettings->SetGameData($slotSettings->slotId . 'ConsecutiveWin', $slotSettings->GetGameData($slotSettings->slotId . 'ConsecutiveWin') + 1);
                    $this->gameState = 'Pending';
                    $postData['slotEvent'] = 'freespin';
                    
                    $eventData['reSpins'] = true;
                    $eventData['consecutiveWins'] = $slotSettings->GetGameData($slotSettings->slotId . 'ConsecutiveWin');
                    $needRespin = true;
                }
            }
            else
            {
                //save multipliers for freespin session
                $consecutiveWins = $slotSettings->GetGameData($slotSettings->slotId . 'ConsecutiveWin');
                $multiplier = $slotSettings->GetGameData($slotSettings->slotId . 'WinMultiplier');
                if($consecutiveWins == 3)
                    $multiplier = 2;
                else if($consecutiveWins == 4)
                    $multiplier = 3;
                else if($consecutiveWins == 5)
                    $multiplier = 5;
                else if($consecutiveWins == 6)
                    $multiplier = 10;

                if($freespinsWon > 0)
                {
                    $slotSettings->SetGameData($slotSettings->slotId . 'ConsecutiveWin', $slotSettings->GetGameData($slotSettings->slotId . 'ConsecutiveWin') + 1);                    
                }
                $slotSettings->SetGameData($slotSettings->slotId . 'WinMultiplier', $multiplier);
                
                $eventData['multiplier'] = $multiplier;
                $eventData['consecutiveWins'] = $slotSettings->GetGameData($slotSettings->slotId . 'ConsecutiveWin');
                $eventData['colossalReel'] = $reels['colossalReel'];
                $eventData['colossalReelSymbols'] = $reels['colossalReelSymbols'];

                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);
                if($freespinsWon > 0)
                {
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames',  $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') + $freespinsWon);
                    $eventData['freeSpinsAwarded'] = $freespinsWon;
                    $eventData['reSpins'] = true;
                }

                $freespinLeft = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');

                if($freespinLeft > 0)
                {
                   $needRespin = true;
                   $eventData['freeSpins'] = $freespinLeft;
                }                
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
            }
           
            $prizes = null;            

            if(!empty($nextCmds))
                $eventData['nextCmds'] = implode(',', $nextCmds);

            $betData = [
                'coin' => $betLine,
                'nCoins' => 50,
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
    }

}


