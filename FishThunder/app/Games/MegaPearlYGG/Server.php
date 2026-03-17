<?php 
namespace VanguardLTE\Games\MegaPearlYGG
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
                        $response = file(base_path() . '/app/Games/MegaPearlYGG/translation.txt')[0];                                                                          
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
                        $response = '{"code":0,"data":{"id":"2203301519500100062","org":null,"gameid":0,"data":{"appsrv":"","file":"/init/","gameid":"10243","height":"600","license":"","org":"Demo","root":"","type":"Html","width":"800"}},"fn":"clientinfo","utcts":1648653590613}';
                        break;
                    case 'info':
                        $response = '{"code":0,"data":{"initialized":true,"playerState":{"state":"e30="}},"fn":"info","utcts":1669171450608}';
                        break;                        
                    case 'game':
                        $filename = base_path() . '/app/Games/MegaPearlYGG/game.txt';
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
                        $nCoins = 40;
                        if(isset($postData['coins']))
                            $nCoins = $postData['coins'];
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
                            $win = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin');
                            $slotSettings->SetBank($slotSettings->GetGameData($slotSettings->slotId . 'LastEvent'), -1 * $win);
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

                        if($cmd == 'freespin' || $cmd == 'pick')
                        {
                            $postData['slotEvent'] = 'freespin';
                        }

                        if( $postData['slotEvent'] != 'freespin' || $cmd == 'buyfeature' ) 
                        {
                            $slotSettings->SetBalance(-1 * $allbet, $postData['slotEvent']);
                            $bankSum = $allbet / 100 * $slotSettings->GetPercent();
                            $slotSettings->UpdateJackpots($allbet);
                            $slotSettings->SetBet($allbet);
                            if($cmd == 'buyfeature')
                                $slotSettings->SetBank('bonus', $bankSum, 'bonus');
                            else
                                $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $bankSum, $postData['slotEvent']);
                            
                            
                            $slotSettings->SetGameData($slotSettings->slotId . 'Step', 1);
                            $slotSettings->SetGameData($slotSettings->slotId . 'CoinWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'BetCoin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'PearlReels', []);
                            $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'GigaScatters', []);
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
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
            $linesId = $slotSettings->GetPaylines();
            if($postData['slotEvent'] == 'freespin')
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'freespin');
            }
            else
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bet');
            }

            $lines = count($linesId);
            $nCoins = 40;
            $betLine = 0;
            if(isset($postData['coin']))
                $betLine = $postData['coin'];
            
            $allbet = $betLine * $nCoins;
            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $nCoins);
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
            if($cmd == 'buyfeature')
                $winType = 'bonus';
            if($this->debug && $postData['slotEvent'] != 'freespin')
            {                 
                $winType = 'bonus';
            }

            if($postData['slotEvent'] == 'freespin')
            {
                $freespinLeft = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                if($slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') < 4 && $freespinLeft == 1)
                {
                    $winType = 'bonus';
                }
            }

            $spinAcquired = false;
            $gameWin = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin');

            $minReels = [];
            $minLineWins = [];
            $minTotalWin = -1;
            $minFreespinsWon = 0;
            $minReels0 = [];
            $minFightTriggered = false;
            $minWildFightCount = 0;

            $totalWin = 0;            
            $freespinsWon = 0;            
            $lineWins = [];
            $reels0 = [];
            $reels = [];
            $wildFightCount = 0;

            $scatter = "S";
            $wild = ["Z"];
            $bonusMpl = 1;
            $mpl = 1;
            $fightTriggered = false;
            $newScatterCnt = 0;
            $pearl_reels = $slotSettings->GetGameData($slotSettings->slotId . 'PearlReels');
            for( $i = 0; $i <= 600; $i++ ) 
            {
                $fightTriggered = false;
                $totalWin = 0;                
                $freespinsWon = 0;
                $wildFightCount = 0;
                $lineWins = [];
                $newScatterCnt = 0;
                $cWins = array_fill(0, $lines, 0);                
                
                $mpl = 1;
                $reels = $slotSettings->GetReelStrips($winType, $postData['slotEvent']);
                $reels0 = $reels;
                
                if($postData['slotEvent'] == 'freespin')
                {
                    if($cmd == 'pick')
                    {
                        for($r = 0; $r < 6; $r++)
                            for($c = 0; $c < 4; $c++)
                            {
                                $reels['reel'.($r+1)][$c] = 'X';
                            }
                    }
                    $pearl_reels = $slotSettings->GetGameData($slotSettings->slotId . 'PearlReels');
                    $totalScatterCnt = 0;
                    for($r = 0; $r < 6; $r++)
                        for($c = 0; $c < 4; $c++) 
                        {
                            if($reels['reel'.($r+1)][$c] == $scatter && $pearl_reels[$r][$c] == -1)
                            {
                                $newScatterCnt++;
                                $totalScatterCnt++;
                            }

                            if($pearl_reels[$r][$c] > -1) //restore pearl symbols from last spin
                            {
                                $reels['reel'.($r+1)][$c] = 'S';
                                $totalScatterCnt++;
                            }
                        }
                    
                    if($totalScatterCnt > 15 && $i < 590)    
                        continue;

                    if($newScatterCnt > 0)
                        $freespinsWon = 3;
                    break;
                }
                
                for( $k = 0; $k < $lines; $k++ )
                {
                    $winline = [];
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
                                if($fightTriggered)
                                    $mpl = 1;
                                if( in_array($s[0], $wild) && in_array($s[1], $wild) && in_array($s[2], $wild) ) 
                                {
                                    $mpl = 0;
                                }
                                $emptyLine = [[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0]];
                                $tmpWin = $slotSettings->Paytable[$csym][3] * $betLine * $mpl * $bonusMpl;
                                $coin = $slotSettings->Paytable[$csym][3] * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $cWins[$k] = $tmpWin;
                                    $emptyLine[0][$p0] = 1;
                                    $emptyLine[1][$p1] = 1;
                                    $emptyLine[2][$p2] = 1;
                                    $winline = [$k, $coin, $tmpWin,[$p0,$p1,$p2,-1,-1,-1], $csym, 3];
                                }
                            }
                            if( ($s[0] == $csym || in_array($s[0], $wild)) &&
                                ($s[1] == $csym || in_array($s[1], $wild)) &&
                                ($s[2] == $csym || in_array($s[2], $wild)) && 
                                ($s[3] == $csym || in_array($s[3], $wild)) ) 
                            {
                                if( in_array($s[0], $wild) && in_array($s[1], $wild) && in_array($s[2], $wild) && in_array($s[3], $wild) ) 
                                {
                                    $mpl = 0;
                                }
                                $emptyLine = [[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0]];
                                $tmpWin = $slotSettings->Paytable[$csym][4] * $betLine * $mpl * $bonusMpl;
                                $coin = $slotSettings->Paytable[$csym][4] * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $cWins[$k] = $tmpWin;
                                    $emptyLine[0][$p0] = 1;
                                    $emptyLine[1][$p1] = 1;
                                    $emptyLine[2][$p2] = 1;
                                    $emptyLine[3][$p3] = 1;
                                    $winline = [$k, $coin, $tmpWin,[$p0,$p1,$p2,$p3,-1,-1], $csym, 4];
                                }
                            }
                            if( ($s[0] == $csym || in_array($s[0], $wild)) &&
                                ($s[1] == $csym || in_array($s[1], $wild)) &&
                                ($s[2] == $csym || in_array($s[2], $wild)) &&
                                ($s[3] == $csym || in_array($s[3], $wild)) &&
                                ($s[4] == $csym || in_array($s[4], $wild)) ) 
                            {
                                if( in_array($s[0], $wild) && in_array($s[1], $wild) && in_array($s[2], $wild) && in_array($s[3], $wild) && in_array($s[4], $wild)  ) 
                                {
                                    $mpl = 0;
                                }
                                $emptyLine = [[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0]];
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
                                    $winline = [$k, $coin, $tmpWin,[$p0,$p1,$p2,$p3,$p4,-1], $csym, 5];
                                }
                            }
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && 
                                ($s[1] == $csym || in_array($s[1], $wild)) && 
                                ($s[2] == $csym || in_array($s[2], $wild)) && 
                                ($s[3] == $csym || in_array($s[3], $wild)) && 
                                ($s[4] == $csym || in_array($s[4], $wild)) && 
                                ($s[5] == $csym || in_array($s[5], $wild)) ) 
                            {
                                if( in_array($s[0], $wild) && in_array($s[1], $wild) && in_array($s[2], $wild) && in_array($s[3], $wild) && in_array($s[4], $wild) && in_array($s[5], $wild)) 
                                {
                                    $mpl = 0;
                                }
                                $emptyLine = [[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0]];
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
                                    $winline = [$k, $coin, $tmpWin,[$p0,$p1,$p2,$p3,$p4,$p5], $csym, 6];
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
                $scatterCnt = 0;
                $scatterPos = [];
                for($r = 0; $r < 6; $r++)            
                    for($c = 0; $c < 4; $c++)
                    {
                        if($reels['reel'.($r+1)][$c] == $scatter)
                        {
                            $scatterCnt++;
                            $scatterPos[] = $c * 6 + $r;
                        }
                    }
                if( $scatterCnt >= 6 && $winType != 'bonus' && $postData['slotEvent'] == 'bet')
                    continue;
                if ($scatterCnt < 6 && $winType == 'bonus')
                    continue;
                if($scatterCnt >= 6 || ($scatterCnt > 0 && $postData['slotEvent'] == 'freespin'))
                {
                    $freespinsWon = 3;
                    $lineWin = [40, 0, 0,$scatterPos, $scatter, $scatterCnt];
                    $lineWins[] = $lineWin;
                }
                
                $totalWin += $gameWin;

                if($minTotalWin == -1 || ($minTotalWin > $totalWin && $totalWin > 0))
                {
                    $minTotalWin = $totalWin;
                    $minLineWins = $lineWins;
                    $minFreespinsWon = $freespinsWon;
                    $minReels = $reels;
                    $minReels0 = $reels0;                         
                    $minFightTriggered = $fightTriggered;
                    $minWildFightCount = $wildFightCount;
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
                $reels0 = $minReels0;
                $lineWins = $minLineWins;
                $totalWin = $minTotalWin;
                $freespinsWon = $minFreespinsWon;
                $wildFightCount = $minWildFightCount;
                $fightTriggered = $minFightTriggered;
            }

            $this->lastReels = $reels;           
            
            $coinWin = 0; //coins won
            $prizes = [];

            if(!empty($lineWins))
            {
                foreach($lineWins as $winline)
                {
                    $symbol = $winline[4];
                    if($symbol == 'S')
                    {
                        $prizes[] = [
                            'symbol' => $winline[4],
                            'line' => $winline[0],
                            'count' => $winline[5],
                            'trigger_value' => 1,
                            'positions' => $winline[3],
                            'behaviour' => 'base',
                            'trigger' => 'respins',
                            'win' => $winline[2] * 100,                            
                        ];
                    }
                    else
                    {
                        $coinWin += $winline[1]; //sum up coins
                        $prizes[] = [
                            'symbol' => $winline[4],
                            'line' => $winline[0],
                            'count' => $winline[5],
                            'positions' => $winline[3],
                            'behaviour' => 'base',
                            'win' => $winline[2] * 100
                        ];
                    }                    
                }
            }

            $slotSettings->SetGameData($slotSettings->slotId . 'CoinWin', $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') + $coinWin);
            if($postData['slotEvent'] != 'freespin')
                $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', $totalWin);            
            
            //nextCmds
            $nextCmds = [];

            $needRespin = false;
            $eventData = [
                'accC' => $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin'),
                'accWa' => number_format($slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') * $betLine, 2),                
                'manualNoWin' => $manualNoWin,
            ];
            
            $behaviour = 'base';
            if($postData['slotEvent'] == 'freespin')
                $behaviour = 'respins';
            $state = [
                'gigablox' => [
                    'reel_layout' => $reels['layout'],
                    'reel_set_idx' => $reels['set_idx'],
                    'reel_idx' => $reels['idx']
                ],
                'gigascatters' => [],
                'gigascatter_history' => [],
                'behaviour' => $behaviour,
                'bets' => [
                    'antebet' => 0,
                    'bet_per_line' => $betLine,
                    'total_bet' => $allbet,
                    'lines' => 40
                ],
                'next_behaviour' => 'base',
                'current_win' => $totalWin * 100,
                'win' => $totalWin * 100,
                'game_complete' => true,
                'reel_display' => [$reels['reel1'], $reels['reel2'], $reels['reel3'], $reels['reel4'], $reels['reel5'], $reels['reel6']],
                'reel_stops' => $reels['rp']
            ];
            
            if(count($prizes) > 0)
                $state['wins'] = $prizes;
            $freespinLeft = 0;
            if($postData['slotEvent'] == 'bet')
            {
                if($freespinsWon > 0)
                {
                    $pearl_reels = [];
                    for($r = 0; $r < 6; $r++)
                    {
                        $pearl_reel = [];
                        for($c = 0; $c < 4; $c++)
                        {
                            if($reels['reel'.($r+1)][$c] == $scatter)
                                $pearl_reel[] = rand(1,5); //multiplier
                            else
                                $pearl_reel[] = -1;
                        }
                        $pearl_reels[] = $pearl_reel;
                    }
                    $gigascatters = [];
                    $gigascatter_history = $this->getGigaScatters($pearl_reels, $gigascatters);
                    $state['gigascatters'] = $gigascatters;
                    $state['gigascatter_history'] = $gigascatter_history;
                    $state['game_complete'] = false;
                    $state['feature_data'] = [[
                        'freespins_played' => 0,
                        'freespins_triggered' => 3,
                        'trigger_value' => 1,
                        'total_freespins' => 3,
                        'trigger' => 'respins',
                        'type' => 'FREESPIN_FEATURE',
                        'complete' => false,
                        'total_win' => 0,
                    ]];
                    $state['next_behaviour'] = 'respins';

                    //trigger freespin
                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                    $slotSettings->SetGameData($slotSettings->slotId . 'PearlReels', $pearl_reels);
                    $slotSettings->SetGameData($slotSettings->slotId . 'GigaScatters', $gigascatters);
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $freespinsWon);
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreespinWinCoin', 0);
                    $postData['slotEvent'] = 'freespin';
                    $this->gameState = 'Pending';
                }                
            }
            else
            {
                $this->gameState = 'Pending';
                if($cmd == 'pick')
                {
                    $state['feature_data'] = [];
                    $state['feature_data'][] = [
                        'freespins_played' => $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame'),
                        'freespins_triggered' => $freespinsWon,
                        'trigger_value' => 1,
                        'total_freespins' => $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames'),
                        'trigger' => 'respins',
                        'type' => 'FREESPIN_FEATURE',
                        'complete' => true,
                        'total_win' => 0,
                    ];
                    
                    $totalMul = 0;
                    $gigascatters = $slotSettings->GetGameData($slotSettings->slotId . 'GigaScatters');
                    foreach($gigascatters as $gigascatter)
                    {
                        $totalMul += $gigascatter['multi'];
                    }
                    $allbet = $slotSettings->GetGameData($slotSettings->slotId . 'BetCoin') * 40;
                    $totalWin = $allbet * $totalMul;
                    $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', $slotSettings->GetGameData($slotSettings->slotId . 'GameWin') + $totalWin);

                    $state['feature_data'][] = [
                        'trigger_value' => 1,
                        'trigger' => 'respin_payout',
                        'type' => 'PICK_FEATURE',
                        'complete' => true,
                        'total_win' => $totalWin * 100,
                        'picks_made' => '0:'.$totalMul.':total_bet_multi'
                    ];
                    $state['current_win'] = $totalWin * 100;
                    $state['win'] = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin') * 100;
                    $state['gigascatters'] = $gigascatters;
                    unset($state['reel_stops']);
                    unset($state['next_behaviour']);                    
                }
                else
                {
                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);
                    $gigascatters = $slotSettings->GetGameData($slotSettings->slotId . 'GigaScatters');
                    if($freespinsWon > 0)
                    {
                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames',  $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + $freespinsWon);
    
                        for($r = 0; $r < 6; $r++)
                        {
                            for($c = 0; $c < 4; $c++)
                            {
                                if($reels['reel'.($r+1)][$c] == $scatter && $pearl_reels[$r][$c] == -1)
                                    $pearl_reels[$r][$c] = rand(1,3); //multiplier                                
                            }
                        }
    
                        $gigascatter_history = $this->getGigaScatters($pearl_reels, $gigascatters);
                        $slotSettings->SetGameData($slotSettings->slotId . 'PearlReels', $pearl_reels);
                        $slotSettings->SetGameData($slotSettings->slotId . 'GigaScatters', $gigascatters);
                        $state['gigascatter_history'] = $gigascatter_history;
                    }
                    $state['gigascatters'] = $gigascatters;                    
                    $freespinLeft = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                    $complete = false;
                    if($freespinLeft == 0)
                        $complete = true;
                    $state['game_complete'] = $complete;
    
                    $state['feature_data'] = [];
                    $state['feature_data'][] = [
                        'freespins_played' => $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame'),
                        'freespins_triggered' => $freespinsWon,
                        'trigger_value' => 1,
                        'total_freespins' => $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames'),
                        'trigger' => 'respins',
                        'type' => 'FREESPIN_FEATURE',
                        'complete' => $complete,
                        'total_win' => 0,
                    ];
                    $state['next_behaviour'] = 'respins';
                    
                    if($freespinLeft == 0)
                    {                    
                        $state['next_behaviour'] = 'base';
                        $state['feature_data'][] = [
                            'trigger_value' => 1,
                            'trigger' => 'respin_payout',
                            'type' => 'PICK_FEATURE',
                            'complete' => false,
                            'total_win' => 0,
                            'picks_made' => ''
                        ];
                    }
                }
            }
            
            $clientData = [
                'settings' => [
                    'slot_settings' => [
                        'default_bet' => 1,
                        'bet_denoms' => [1, 3],
                        'gamble_allowed' => true,
                        'bet_limits' => [
                            'min' => 1,
                            'max' => 9223372036854776000,
                        ]
                        ],
                    'balance' => $slotSettings->GetBalance(),
                    'math_variation' => '96.1;97.0',
                    'currency' => '$;3;,; ;.;2;1',
                    'version' => '1.0.4-1.0.5'
                    ],
                'slot_data' => [
                    'state' => $state
                ],
                'type' => 'play'
            ];

            $json_clientData = json_encode($clientData);
            $eventData['response'] = [
                'cashWin' => '0.00',
                'clientData' => [
                    'state' => base64_encode($json_clientData),
                    // 'state_plain' => $clientData
                ],
                'coinWin'=>'0'
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
                        $nextCmds[] = 'C';
                    }
                }
            }
           
            $prizes = null;            

            if(!empty($nextCmds))
                $eventData['nextCmds'] = implode(',', $nextCmds);

            $betData = [
                'coin' => $betLine,
                'cheat' => null,
                'clientParams' => json_decode($postData['clientParams']),
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

        function checkSquareIncluded($squares, $r, $c)
        {
            $contained = false;
            foreach($squares as $square)
            {
                if($r >= $square['reel'] && $r < $square['reel'] + $square['size'] && $c >= $square['row'] && $c < $square['row'] + $square['size'])
                {
                    $contained = true;
                    break;
                }
            }
            return $contained;
        }

        function getIncludedSquare($squares, $r, $c)
        {
            foreach($squares as $square)
            {
                if($r >= $square['reel'] && $r < $square['reel'] + $square['size'] && $c >= $square['row'] && $c < $square['row'] + $square['size'])
                {
                    return $square;
                }
            }
            return null;
        }

        function isContainedOtherMegaSquare($squares, $r, $c, $size)
        {
            $contained = false;
            foreach($squares as $square)
            {
                if($r >= $square['reel'] && $r + $size <= $square['reel'] + $square['size'] && $c >= $square['row'] && $c + $size <= $square['row'] + $square['size'])
                {
                    $contained = true;
                    break;
                }
            }
            return $contained;
        }

        function getGigaScatters(&$pearl_reels, &$gigascatters)
        {
            $gigascatter_history = [];
            for($size = 4; $size >= 1; $size--)
            {                
                for($r = 0; $r <= 6 - $size; $r++)
                {
                    for($c = 0; $c <= 4 - $size; $c++)
                    {                        
                        $included_squares = [];
                        $pearl_cnt = 0;
                        for($rr = $r; $rr < $r + $size; $rr++)
                            for($cc = $c; $cc < $c + $size; $cc++)
                            {
                                if($pearl_reels[$rr][$cc] > 0 || ($pearl_reels[$rr][$cc] == 0 && $size > 1))
                                {
                                    if($pearl_reels[$rr][$cc] == 0)
                                    {
                                        if(!$this->checkSquareIncluded($included_squares, $rr, $cc))
                                        {
                                            $included_squares[] = $this->getIncludedSquare($gigascatters, $rr, $cc);
                                        }
                                    }
                                    else
                                    {
                                        $included_squares[] = ['size' => 1, 'reel' => $rr, 'row' => $cc, 'multi' => $pearl_reels[$rr][$cc]];
                                    }
                                    $pearl_cnt++;                                
                                }
                            }
                        
                        $target_pearl_cnt = 0;
                        $multi = 0;
                        foreach($included_squares as $square)
                        {
                            $target_pearl_cnt += ($square['size'] * $square['size']);
                            $multi += $square['multi'];
                        }
                        if($pearl_cnt == $target_pearl_cnt && $pearl_cnt == $size * $size) //checking if whole mega scatter is fully contained inside the square
                        {
                            if(!$this->isContainedOtherMegaSquare($gigascatters, $r, $c, $size))
                            {
                                if($size > 1)
                                {
                                    for($rr = 0; $rr < 6; $rr++)
                                        for($cc = 0; $cc < 4; $cc++)
                                            if($pearl_reels[$rr][$cc] > 0)
                                            {
                                                if(!$this->checkSquareIncluded($included_squares, $rr, $cc))
                                                    $included_squares[] = ['size' => 1, 'reel' => $rr, 'row' => $cc, 'multi' => $pearl_reels[$rr][$cc]];
                                            }
                                                
                                    foreach($gigascatters as $square)
                                    {
                                        $included_squares[] = $square;
                                    }
                                    $gigascatter_history[] = $included_squares;
                                }                                
                                
                                //erase elements which included in giga scatters
                                for($rr = $r; $rr < $r + $size; $rr++)
                                    for($cc = $c; $cc < $c + $size; $cc++)
                                        $pearl_reels[$rr][$cc] = 0;
                                if($size > 1)
                                    $multi *= 2;
                                $gigascatters[] = ['size' => $size, 'reel' => $r, 'row' => $c, 'multi' => $multi];
                            }
                        }
                    }
                }
            }

            return $gigascatter_history;
        }
    }

}


