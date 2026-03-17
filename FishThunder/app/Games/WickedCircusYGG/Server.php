<?php 
namespace VanguardLTE\Games\WickedCircusYGG
{
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;    

    class Server
    {
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
            $debug = false;
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
                        $slotSettings->SetGameData($slotSettings->slotId . 'SupermeterCoin', 0);
                        $response = file(base_path() . '/app/Games/WickedCircusYGG/translation.txt')[0];                                                                          
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
                                "currency" => "USD",
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
                        $response = '{"code":0,"data":{"id":"","org":null,"gameid":0,"data":{"appsrv":"","file":"/init/","gameid":"7321","height":"600","license":"","org":"Demo","root":"","type":"Html","width":"800"}},"fn":"clientinfo","utcts":'.time().'}';
                        break;
                    case 'game':
                        $filename = base_path() . '/app/Games/WickedCircusYGG/game.txt';
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
                        $bonusMpl = 1;
                        $linesId = $slotSettings->GetPaylines();                                
                        $lines = 10;
                        $betLine = 0;
                        if(isset($postData['coin']))
                            $betLine = $postData['coin'];
                        $allbet = $betLine * $lines;
                        if( !isset($postData['slotEvent']) ) 
                        {
                            $postData['slotEvent'] = 'bet';
                        }
                        $cmd ='';
                        if (isset($postData['cmd']))
                        {
                            $postData['slotEvent'] = 'freespin';
                            $cmd = $postData['cmd'];
                            $slotSettings->SetGameData($slotSettings->slotId . 'Step', $slotSettings->GetGameData($slotSettings->slotId . 'Step') + 1);
                        }

                        if($cmd == 'LS')
                        {
                            //process for <load supermeter> command without generating new spin                                    
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
                                    'resultBal' => ['cash' =>  $slotSettings->GetBalance()],
                                    'wager' => [
                                        'bets' => [
                                            [
                                                'step' => 2,
                                                'betamount' => 0,
                                                'betcurrency' => 'USD',
                                                'wonamount' => 0,
                                                'status' => 'RESULTED',
                                                'betdata'=> [
                                                    'cheat' => null,
                                                    'cmd' => 'LS',
                                                    'coin' => $slotSettings->GetGameData($slotSettings->slotId . 'BetCoin'),
                                                    'nCoins' => (int)$slotSettings->GetGameData($slotSettings->slotId . 'BetCoin'),
                                                    'restoredAccumulatedWonCoin' => $slotSettings->GetGameData($slotSettings->slotId . 'SupermeterCoin'),
                                                    'variant' => null,
                                                    'lines' => '1111111111'
                                                ],
                                                'eventdata' => [
                                                    'nextCmds' => 'C,S',
                                                    'supermeter' => $slotSettings->GetGameData($slotSettings->slotId . 'SupermeterCoin')
                                                ],
                                                'prizes' => null,
                                                'prepaid' => false
                                            ]
                                        ],
                                        'prepaid' => false,
                                        'status' => 'Pending',
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
                        else if($cmd == 'C')
                        {
                            //collect current supermeter and end jokerizer mode
                            $curSupermeterCoin = $slotSettings->GetGameData($slotSettings->slotId . 'SupermeterCoin');
                            $curBetCoin = $slotSettings->GetGameData($slotSettings->slotId . 'BetCoin');
                            $win = $curSupermeterCoin * $curBetCoin;
                            
                            $slotSettings->SetBank('', -1 * $win);                            

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
                                                'wonamount' => $win,
                                                'status' => 'RESULTED',
                                                'betdata'=> [
                                                    'doubleA' => $win,
                                                    'doubleN' => 1,
                                                    'cheat' => null,
                                                    'cmd' => 'C',
                                                    'coin' => $slotSettings->GetGameData($slotSettings->slotId . 'BetCoin'),
                                                    'nCoins' => 1,
                                                    'restoredAccumulatedWonCoin' => $slotSettings->GetGameData($slotSettings->slotId . 'SupermeterCoin'),
                                                    'variant' => null,
                                                    'lines' => '1111111111'
                                                ],
                                                'eventdata' => [],
                                                'prizes' => [
                                                    [
                                                        'descr' => 'Cash out',
                                                        'gameId' => '7321',
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
                            $slotSettings->SaveLogReport($response, $allbet, $reportWin, '');
                            break;
                        }

                        //rest cases are ''(general spin), 'H'(hold), 'S'(supermeter spin)
                        //check balance
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
                            $slotSettings->SetGameData($slotSettings->slotId . 'SupermeterCoin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'BetCoin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'LastScatterCount', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'MysteryCoinWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', 0);
                        }
                        else
                        {
                            $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);                            
                        }
                        
                        $winTypeTmp = $slotSettings->GetSpinSettings('bet', $betLine, $lines);
                        $winType = $winTypeTmp[0];
                        $spinWinLimit = $winTypeTmp[1];
                        $gameWin = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin');
                        $spinAcquired = false;
                        $scattersCount = 0;

                        $minLineWins = [];
                        $minReels = [];
                        $minScattersCount = 0;
                        $minScattersCoinWin = 0;
                        $minTotalWin = -1;

                        for( $i = 0; $i <= 500; $i++ ) 
                        {
                            $scattersCount = 0;
                            $totalWin = 0;
                            $lineWins = [];
                            $cWins = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
                            $wild = []; //there is no wild symbol in this game
                            $scatter = 0; //joker symbol
                            $slotEvent = 'bet';
                            if($cmd == 'S')
                                $slotEvent = 'normal_supermeter';
                            else if($cmd == 'H')
                                $slotEvent = 'hold_supermeter';
                            $reels = $slotSettings->GetReelStrips($winType, $slotEvent);

                            if($debug)
                            {
                                //reel test
                                if($postData['slotEvent'] != 'freespin')
                                {
                                    $reels['rp'] = [33, 1, 53, 41, 29];
                                    $reels['reel1'] = [6, 6, 0];
                                    $reels['reel2'] = [6, 6, 6];
                                    $reels['reel3'] = [6, 3, 3];
                                    $reels['reel4'] = [6, 6, 6];
                                }
                                else
                                {
                                    $reels['reel1'][0] = $scatter;
                                    $reels['reel2'][0] = $scatter;
                                }
                            }

                            if($cmd == 'H')
                            {
                                //hold status, restore last reels, attention! once H(hold), cannot S(spin) then 
                                $lastReels = $slotSettings->GetGameData($slotSettings->slotId . 'Reels');
                                $scattersLastReelPos = $slotSettings->GetGameData($slotSettings->slotId . 'JokerizerReelPos');
                                for($s = 0; $s < 5; $s++)
                                {
                                    if($scattersLastReelPos[$s] == 1)
                                    {
                                        $reels['rp'][$s] = $lastReels['rp'][$s];
                                        $reels['reel'.($s+1)] = $lastReels['reel'.($s+1)];
                                    }
                                }
                            }
                            
                            
                            for( $k = 0; $k < $lines; $k++ ) 
                            {
                                $winline = [];
                                for( $j = 0; $j < count($slotSettings->SymbolGame); $j++ ) 
                                {
                                    $csym = $slotSettings->SymbolGame[$j];
                                    if( $csym == $scatter || !isset($slotSettings->Paytable['SYM_' . $csym]) ) 
                                    {
                                    }
                                    else
                                    {
                                        $s = [];
                                        $s[0] = $reels['reel1'][$linesId[$k][0]-1];
                                        $s[1] = $reels['reel2'][$linesId[$k][1]-1];
                                        $s[2] = $reels['reel3'][$linesId[$k][2]-1];
                                        $s[3] = $reels['reel4'][$linesId[$k][3]-1];
                                        $s[4] = $reels['reel5'][$linesId[$k][4]-1];
                                        $p0 = $linesId[$k][0]-1;
                                        $p1 = $linesId[$k][1]-1;
                                        $p2 = $linesId[$k][2]-1;
                                        $p3 = $linesId[$k][3]-1;
                                        $p4 = $linesId[$k][4]-1;                     
                                        if($k < 5)
                                        {                                                    
                                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) ) 
                                            {
                                                $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
                                                $mpl = 1;                                                        
                                                $tmpWin = $slotSettings->Paytable['SYM_' . $csym][3] * $betLine * $mpl * $bonusMpl;
                                                if( $cWins[$k] < $tmpWin ) 
                                                {
                                                    $cWins[$k] = $tmpWin;
                                                    $emptyLine[0][$p0] = 1;
                                                    $emptyLine[1][$p1] = 1;
                                                    $emptyLine[2][$p2] = 1;
                                                    $winline = [$k + 1, $slotSettings->Paytable['SYM_' . $csym][3], $this->getConvertedLine($emptyLine)]; //[lineId, coinWon, winPositions]
                                                }
                                            }
                                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) ) 
                                            {
                                                $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
                                                $mpl = 1;                                                        
                                                $tmpWin = $slotSettings->Paytable['SYM_' . $csym][4] * $betLine * $mpl * $bonusMpl;
                                                if( $cWins[$k] < $tmpWin ) 
                                                {
                                                    $cWins[$k] = $tmpWin;
                                                    $emptyLine[0][$p0] = 1;
                                                    $emptyLine[1][$p1] = 1;
                                                    $emptyLine[2][$p2] = 1;
                                                    $emptyLine[3][$p3] = 1;
                                                    $winline = [$k + 1, $slotSettings->Paytable['SYM_' . $csym][4], $this->getConvertedLine($emptyLine)]; //[lineId, coinWon, winPositions]                                                             
                                                }
                                            }
                                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) ) 
                                            {
                                                $mpl = 1;
                                                $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
                                                $tmpWin = $slotSettings->Paytable['SYM_' . $csym][5] * $betLine * $mpl * $bonusMpl;
                                                if( $cWins[$k] < $tmpWin ) 
                                                {
                                                    $cWins[$k] = $tmpWin;
                                                    $emptyLine[0][$p0] = 1;
                                                    $emptyLine[1][$p1] = 1;
                                                    $emptyLine[2][$p2] = 1;
                                                    $emptyLine[3][$p3] = 1;
                                                    $emptyLine[4][$p4] = 1;
                                                    $winline = [$k + 1, $slotSettings->Paytable['SYM_' . $csym][5], $this->getConvertedLine($emptyLine)]; //[lineId, coinWon, winPositions]                                                            
                                                }
                                            }
                                        }
                                        else
                                        {
                                            if(($cWins[$k-5] == 0) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) ) 
                                            {
                                                $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
                                                $mpl = 1;                                                        
                                                $tmpWin = $slotSettings->Paytable['SYM_' . $csym][3] * $betLine * $mpl * $bonusMpl;
                                                if( $cWins[$k] < $tmpWin ) 
                                                {
                                                    $cWins[$k] = $tmpWin;
                                                    $emptyLine[2][$p2] = 1;
                                                    $emptyLine[3][$p3] = 1;
                                                    $emptyLine[4][$p4] = 1;
                                                    
                                                    $winline = [$k + 1, $slotSettings->Paytable['SYM_' . $csym][3], $this->getConvertedLine($emptyLine)]; //[lineId, coinWon, winPositions]
                                                }
                                            }
                                            if(($cWins[$k-5] == 0) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) ) 
                                            {
                                                $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
                                                $mpl = 1;                                                        
                                                $tmpWin = $slotSettings->Paytable['SYM_' . $csym][4] * $betLine * $mpl * $bonusMpl;
                                                if( $cWins[$k] < $tmpWin ) 
                                                {
                                                    $cWins[$k] = $tmpWin;
                                                    $emptyLine[1][$p1] = 1;
                                                    $emptyLine[2][$p2] = 1;
                                                    $emptyLine[3][$p3] = 1;
                                                    $emptyLine[4][$p4] = 1;
                                                    $winline = [$k + 1, $slotSettings->Paytable['SYM_' . $csym][4], $this->getConvertedLine($emptyLine)]; //[lineId, coinWon, winPositions]                                                             
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
                            }

                            $scattersReelPos = [0,0,0,0,0];                                    
                            for( $r = 1; $r <= 5; $r++ ) 
                            {
                                for( $p = 0; $p <= 2; $p++ ) 
                                {
                                    if( $reels['reel' . $r][$p] == $scatter ) 
                                    {
                                        $scattersCount++;
                                        $scattersReelPos[$r-1] = 1; 
                                    }
                                }
                            }
                            
                            $scattersCoinWin = 0;
                            if($scattersCount > 1 && $winType != 'bonus')
                                continue;
                            if($scattersCount > 0)
                            {                                
                                if($scattersCount ==  $slotSettings->GetGameData($slotSettings->slotId . 'LastScatterCount'))                            
                                {
                                    $scattersCoinWin = $slotSettings->GetGameData($slotSettings->slotId . 'MysteryCoinWin');
                                }
                                else
                                {
                                    if($scattersCount == 3)
                                        $scattersCoinWin = 20 * rand(25, 50);
                                    else if($scattersCount == 4)
                                        $scattersCoinWin = 20 * rand(40, 150);
                                    else if($scattersCount >= 5)
                                        $scattersCoinWin = 20 * rand(100, 300);
                                    else if($scattersCount == 2 && $postData['slotEvent'] == 'freespin')
                                        $scattersCoinWin = 20 * rand(2, 20);
                                    
                                }
                            }
                            $scattersWin = $scattersCoinWin * $betLine;
                            $gameState = 'Finished';
                            $totalWin += $scattersWin;                                                   

                            if($debug)
                            {
                                $spinAcquired = true;
                                break;
                            }

                            if($minTotalWin == -1 || ($totalWin > 0 && $totalWin < $minTotalWin))
                            {
                                $minLineWins = $lineWins;
                                $minReels = $reels;
                                $minScattersCount = $scattersCount;
                                $minScattersCoinWin = $scattersCoinWin;
                                $minTotalWin = $totalWin;
                            }

                            if($totalWin <= $spinWinLimit && $winType != 'none' && $totalWin > 0)
                            {
                                $spinAcquired = true;
                                if($totalWin < 0.5 * $spinWinLimit)
                                    $spinAcquired = false;
                                break;
                            }                                    
                            else if( $winType == 'none' && $totalWin == 0) 
                            {
                                break;
                            }
                        }

                        $manualWin = false;
                        if(!$spinAcquired && $totalWin > 0)
                        {
                            $lineWins = $minLineWins;
                            $reels = $minReels;
                            $scattersCount = $minScattersCount;
                            $scattersCoinWin = $minScattersCoinWin;
                            $totalWin = $minTotalWin;
                            $manualWin = true;
                        }

                        if($totalWin > 0)
                        {
                            $slotSettings->SetWin($totalWin);
                        }

                        $supermeter = 0; //coins won
                        $activeLines = [0,0,0,0,0,0,0,0,0,0,0];
                        $activeSymbols = [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
                        if(!empty($lineWins))
                        {
                            foreach($lineWins as $winline)
                            {
                                $supermeter += $winline[1]; //sum up coins 
                                $activeLines[$winline[0]] = 1;
                                $winsyms = $winline[2];
                                for($c = 0; $c < 15; $c++)
                                    if($winsyms[$c] == 1)
                                        $activeSymbols[$c] = 1;
                            }
                            $slotSettings->SetGameData($slotSettings->slotId . 'SupermeterCoin', $slotSettings->GetGameData($slotSettings->slotId . 'SupermeterCoin') + $supermeter);
                            $slotSettings->SetGameData($slotSettings->slotId . 'BetCoin', $betLine);
                        }
                        if($scattersCoinWin > 0)
                        {
                            for($r = 0; $r< 5; $r++)
                                for($c = 0; $c < 3; $c++)
                                {
                                    if($reels['reel'.($r+1)][$c] == 0)
                                        $activeSymbols[$r * 3 + $c] = 1;                                        
                                }
                        }

                        if($scattersCoinWin > 0)
                        {
                            $slotSettings->SetGameData($slotSettings->slotId . 'LastScatterCount', $scattersCount);
                            $slotSettings->SetGameData($slotSettings->slotId . 'MysteryCoinWin', $scattersCoinWin);
                        }

                        //nextCmds
                        $nextCmds = [];
                        $eventData = [];

                        $stickyA = '';
                        $stickyB = '';
                        $stickyN = '';
                        
                        $winPredict = ($slotSettings->GetGameData($slotSettings->slotId . 'SupermeterCoin') + $slotSettings->GetGameData($slotSettings->slotId . 'MysteryCoinWin')) * $betLine;
                        $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', $winPredict);

                        //removed comsumed coin
                        if($cmd == 'S' || $cmd == 'H')
                        {
                            $useCoin = 20;
                            if($cmd == 'H')
                                $useCoin = 60;
                            $slotSettings->SetGameData($slotSettings->slotId . 'SupermeterCoin', $slotSettings->GetGameData($slotSettings->slotId . 'SupermeterCoin') - $useCoin);
                        }

                        if($scattersCoinWin > 500 || $scattersCount == 5)
                        {
                            //take mystery win and finish jokerizer mode if mystery win is larger than 500 or joker count is 5
                            $slotSettings->SetGameData($slotSettings->slotId . 'SupermeterCoin', $slotSettings->GetGameData($slotSettings->slotId . 'SupermeterCoin') + $slotSettings->GetGameData($slotSettings->slotId . 'MysteryCoinWin'));
                            $eventData['mystery'] = $slotSettings->GetGameData($slotSettings->slotId . 'MysteryCoinWin');
                            $gameState = 'Finished';
                        }
                        else
                        {
                            if($postData['slotEvent'] != 'freespin')
                            {
                                if($supermeter > 0)
                                {
                                    $nextCmds = ['LS','C'];
                                    //toggle freespin
                                    $slotSettings->SetGameData($slotSettings->slotId . 'Step', 1);                                
                                    $gameState = 'Pending';
                                }
                            }
                            else if($postData['slotEvent'] == 'freespin')
                            {
                                $gameState = 'Pending';
                                $slotSettings->SetGameData($slotSettings->slotId . 'Reels', $reels);
                                if($scattersCount > 1)
                                {
                                    $scattersLastReelPos = $slotSettings->GetGameData($slotSettings->slotId . 'JokerizerReelPos');
                                    for($s = 0; $s < 5; $s++)
                                    {
                                        $stickyB .= ($scattersLastReelPos[$s].$scattersLastReelPos[$s].$scattersLastReelPos[$s]);
                                        $stickyA .= ($scattersReelPos[$s].$scattersReelPos[$s].$scattersReelPos[$s]);
                                        $d = $scattersLastReelPos[$s] == $scattersReelPos[$s] ? 0 : 1;
                                        $stickyN .= ($d.$d.$d);
                                    }
                                }                               
                                
                                if($cmd == 'S')
                                {
                                    $totalSupermeterCoin =  $slotSettings->GetGameData($slotSettings->slotId . 'SupermeterCoin');
                                                                 
                                    //check mystery win
                                    if($totalSupermeterCoin >= 60 && $scattersCount > 1)
                                    {
                                        $nextCmds = ['M', 'H'];                                        
                                    }
                                    else if($totalSupermeterCoin >= 20)
                                    {
                                        $nextCmds = ['C', 'S'];                                                                              
                                    }
                                    else
                                    {
                                        $slotSettings->SetGameData($slotSettings->slotId . 'SupermeterCoin', $slotSettings->GetGameData($slotSettings->slotId . 'SupermeterCoin') + $slotSettings->GetGameData($slotSettings->slotId . 'MysteryCoinWin'));
                                        $eventData['mystery'] = $slotSettings->GetGameData($slotSettings->slotId . 'MysteryCoinWin');                                        
                                        $slotSettings->SetGameData($slotSettings->slotId . 'LastScatterCount', 0);
                                        $slotSettings->SetGameData($slotSettings->slotId . 'MysteryCoinWin', 0);
                                        if($slotSettings->GetGameData($slotSettings->slotId . 'SupermeterCoin') >= 20)
                                        {
                                            $nextCmds = ['C', 'S'];
                                        }
                                        else
                                        {
                                            $nextCmds = [];
                                            $gameState = 'Finished';
                                        }
                                    }                                    
                                }
                                else if($cmd == 'H')
                                {
                                    $totalSupermeterCoin =  $slotSettings->GetGameData($slotSettings->slotId . 'SupermeterCoin');    
                                    if($totalSupermeterCoin >= 60)
                                    {
                                        $nextCmds[] = 'M';
                                        $nextCmds[] = 'H';
                                    }
                                    else
                                    {
                                        $nextCmds = ['M'];
                                        $eventData['mystery'] = $slotSettings->GetGameData($slotSettings->slotId . 'MysteryCoinWin');
                                        // if($totalSupermeterCoin < 20)
                                        {
                                            $slotSettings->SetGameData($slotSettings->slotId . 'SupermeterCoin', $slotSettings->GetGameData($slotSettings->slotId . 'MysteryCoinWin'));
                                            $slotSettings->SetGameData($slotSettings->slotId . 'MysteryCoinWin', 0);
                                            if($slotSettings->GetGameData($slotSettings->slotId . 'SupermeterCoin') == 0)
                                            {
                                                $nextCmds = [];
                                                $gameState = 'Finished';
                                            }
                                            else
                                            {
                                                $nextCmds = ['C', 'S'];
                                            }
                                        }
                                    }
                                }
                                else if($cmd == 'M')
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'SupermeterCoin', $slotSettings->GetGameData($slotSettings->slotId . 'SupermeterCoin') + $slotSettings->GetGameData($slotSettings->slotId . 'MysteryCoinWin'));
                                    $eventData['mystery'] = $slotSettings->GetGameData($slotSettings->slotId . 'MysteryCoinWin');
                                    if( $slotSettings->GetGameData($slotSettings->slotId . 'SupermeterCoin') >= 20)
                                    {
                                        $nextCmds[] = 'C';
                                        $nextCmds[] = 'S';
                                    }
                                    else
                                    {
                                        $nextCmds[] = 'C';
                                    }
                                    $slotSettings->SetGameData($slotSettings->slotId . 'LastScatterCount', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'MysteryCoinWin', 0);
                                }
                            }
                        }

                        $slotSettings->SetGameData($slotSettings->slotId . 'JokerizerReelPos', $scattersReelPos);
                        $step = $slotSettings->GetGameData($slotSettings->slotId . 'Step');                                
                        
                        $reelSym = $slotSettings->GetReelSymbol($reels);
                        $slotSettings->SetGameData($slotSettings->slotId . 'ReelsSym', $reelSym);

                        $wts = [implode('', $activeLines), implode('', $activeSymbols)];
                        $eventData['accC'] = $supermeter;
                        $eventData['accWa'] = $supermeter * $betLine;
                        $eventData['reels'] = $reelSym;
                        $reelset = 'Reels';
                        if($cmd == 'S')
                            $reelset = 'FeatureReels';
                        else if($cmd == 'H')
                            $reelset = 'FeatureReelsB';

                        $eventData['reelSet'] = $reelset;
                        $eventData['rpos'] = [$reels['rp'][0] - 1, $reels['rp'][1] - 1, $reels['rp'][2] - 1, $reels['rp'][3] - 1, $reels['rp'][4] - 1];
                        $eventData['wonCoins'] = $supermeter;
                        $eventData['wts'] = $wts;
                        $eventData['wtw'] = $lineWins;
                        $eventData['manualWin'] = $manualWin;

                        $buyBalance = $slotSettings->GetBalance();
                        $prizes = null;
                        if($gameState == 'Finished')
                        {
                            //process finished game, this happens when mystery coin exceeds 500 or supermeter is lower than 20 when S, or lower than 60 when H
                            $leftSupermeter = $slotSettings->GetGameData($slotSettings->slotId . 'SupermeterCoin');
                            $win = $leftSupermeter * $betLine;
                            $slotSettings->SetBalance($win, ''); 
                            $slotSettings->SetBank('', -1 * $win);
                            $reportWin = $win;
                            if($win > 0)
                            {
                                $prizes = [[
                                        'descr' => 'Cash out',
                                        'gameId' => '7321',
                                        'netamount' => $win,
                                        'prizeid' => '111',
                                        'type' => 'FIXED',
                                        'wonamount' => $win,
                                        'wonAspect' => 'CASH',
                                        'woncurrency' => 'USD'
                                    ]];
                            }
                        }

                        $resBalance = $slotSettings->GetBalance();
                        $slotSettings->SetGameData($slotSettings->slotId . 'WTS', $wts);                                
                        if(!empty($nextCmds))
                            $eventData['nextCmds'] = implode(',', $nextCmds);

                        if($scattersCount > 2 || ($scattersCount == 2 && $postData['slotEvent'] == 'freespin'))
                        {
                            // if(in_array('M', $nextCmds) && in_array('H', $nextCmds))
                            {
                                $eventData['stickyA'] = $stickyA;
                                $eventData['stickyB'] = $stickyB;
                                $eventData['stickyN'] = $stickyN;
                                $slotSettings->SetGameData($slotSettings->slotId . 'StickyA', $stickyA);
                                $slotSettings->SetGameData($slotSettings->slotId . 'StickyB', $stickyB);
                                $slotSettings->SetGameData($slotSettings->slotId . 'stickyN', $stickyN);
                            }                                    
                            $slotSettings->SetGameData($slotSettings->slotId . 'MysteryWin', $scattersCoinWin);
                        }

                        if($postData['slotEvent'] == 'freespin')
                            $eventData['supermeter'] = $slotSettings->GetGameData($slotSettings->slotId . 'SupermeterCoin');

                        $betData = [
                            'lines' => '1111111111',
                            'coin' => $betLine,
                            'nCoins' => (int)$betLine,
                            'cheat' => null,
                            'clientData' => null,
                            'initialBalance' => $buyBalance,
                            'variant' => null
                        ];
                        if($cmd != '')
                            $betData['cmd'] = $cmd;
                        //doubleA, doubleN, restoredAccumulatedWonCoin

                        $bets = [
                            'step' => $step,
                            'betamount' => $allbet,
                            'betcurrency' => 'USD',
                            'wonamount' => $resBalance - $buyBalance,
                            'status' => 'RESULTED',
                            'betdata'=> $betData,
                            'eventdata' => $eventData,
                            'prizes' => $prizes,
                            'prepaid' => false,                                    
                        ];
                        $wagerid = '';
                        if(isset($postData['wagerid']))
                            $wagerid = $postData['wagerid'];
                        else
                            $wagerid = $this->generateWagerId();
                        $ret = [
                            'code' => 0,
                            'data' => [
                                'buyBal' => ['cash' => $buyBalance],
                                'cashRace' => [
                                    'currency' => null,
                                    'hasWon' => false,
                                    'initialPrize' => null,
                                    'prize' => null,
                                    'resource' => null
                                ],
                                'missionState' => null,
                                'obj' => null,
                                'resultBal' => ['cash' => $resBalance],
                                'wager' => [
                                    'bets' => [
                                        $bets
                                    ],
                                    'prepaid' => false,
                                    'status' => $gameState,
                                    'timestamp' => time(),
                                    'wagerid' => $wagerid
                                ]
                            ],
                            'fn' => $reqId,
                            'utcts' => time()
                        ];
                        $response = json_encode($ret);
                        $slotSettings->SetGameData($slotSettings->slotId . 'BetAmount', $allbet);
                        if($gameState == 'Finished')
                            $slotSettings->SaveLogReport($response, $allbet, $reportWin, '');        
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
           
    }
}


