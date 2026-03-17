<?php 
namespace VanguardLTE\Games\SuperCashDropYGG
{
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;    

    class Server
    {
        public $gameState;
        public $debug = false;
        public $lastReels;        
        
        function getPositions($line, $height, $cnt)
        {
            $positions = [];
            for($r = 0; $r < $cnt; $r++)
            {
                $c = $line[$r];
                $pos = 6 * $c + $r;
                $positions[] = $pos;
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
                        $response = file(base_path() . '/app/Games/SuperCashDropYGG/translation.txt')[0];                                                                          
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
                        $response = '{"code":0,"data":{"id":"2203301519500100062","org":null,"gameid":0,"data":{"appsrv":"","file":"/init/","gameid":"10214","height":"600","license":"","org":"Demo","root":"","type":"Html","width":"800"}},"fn":"clientinfo","utcts":1648653590613}';
                        break;
                    case 'info':
                        $response = '{"code":0,"data":{"initialized":true,"playerState":{"state":"e30="}},"fn":"info","utcts":1669171450608}';
                        break;                        
                    case 'game':
                        $filename = base_path() . '/app/Games/SuperCashDropYGG/game.txt';
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
                        $nCoins = 1;
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

                        if($cmd == 'ReSpin')
                        {
                            $postData['slotEvent'] = 'respin';
                        }
                        if($cmd == 'FreeSpin' || $cmd == 'ReSpinInFreeSpin')
                        {
                            $postData['slotEvent'] = 'freespin';
                        }

                        if( $postData['slotEvent'] == 'bet' || $cmd == 'BUY_BONUS')
                        {
                            $slotSettings->SetBalance(-1 * $allbet, $postData['slotEvent']);
                            $slotSettings->UpdateJackpots($allbet);
                            $slotSettings->SetBet($allbet);
                            if($cmd == 'BUY_BONUS')
                            {

                            }
                            
                            $slotSettings->SetGameData($slotSettings->slotId . 'Step', 1);
                            $slotSettings->SetGameData($slotSettings->slotId . 'CoinWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'BetCoin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'TotalReSpins', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'CurrentRespin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'BaseWinning', 0);
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
                            if($cmd == 'BUY_BONUS')
                                $betLine = $betLine / 75;
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
            
            $nCoins = 100;
            $betLine = 0;

            if(!isset($postData['coin']))
                $postData['coin'] = $slotSettings->GetGameData($slotSettings->slotId . 'BetCoin');
            if($cmd == 'BUY_BONUS')
            {
                $postData['coin'] = $postData['coin'] / 75;
                $slotSettings->GetSpinSettings('freespin', $postData['coin'], 1, true);
            }
                
            $betLine = $postData['coin'] * 0.01;
            
            $allbet = $betLine * $nCoins;
            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $nCoins);
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
            if($cmd == 'BUY_BONUS')
                $winType = 'bonus';
            if($this->debug && $postData['slotEvent'] != 'freespin')
            {                 
                $winType = 'bonus';
            }

            if($postData['slotEvent'] == 'freespin')
            {
                $spinWinLimit /= 10;
                // if($slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') < 4 && $freespinLeft == 1)
                // {
                //     $winType = 'bonus';
                // }
            }

            $spinAcquired = false;
            $gameWin = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin');

            $minReels = [];
            $minLineWins = [];
            $minTotalWin = -1;
            $minFreespinsWon = 0;
            $minReels0 = [];
            $minWildMultipliers = [];
            $minIsLockedWild = false;
            $minScatterPos = [];

            $totalWin = 0;            
            $freespinsWon = 0;            
            $lineWins = [];
            $reels0 = [];
            $reels = [];
            $isLockedWild = false;

            $scatter = 9;
            $wild = [0];
            $bonusMpl = 1;
            $mpl = 1;
            $reelName = "Reels";
            if($postData['slotEvent'] == 'freespin')
            {
                $reelName = 'FeatureReels';
            }
            $poss = rand(0, 100);
            $boardHeight = 4;
            if($poss < 5)
            {
                $boardHeight = 5;
                if($poss < 3)
                    $boardHeight = 6;
            }
            if($postData['slotEvent'] == 'freespin')
                $boardHeight = 6;

            $respinRows = [];
            $gcv = null;
            if($cmd == 'ReSpin' || $cmd == 'ReSpinInFreeSpin')
            {
                $lastRspinReels = $slotSettings->GetGameData($slotSettings->slotId . 'RespinReels');
                $boardHeight = $lastRspinReels['height'];
                for($r = 0; $r < 6; $r++)
                    if($lastRspinReels['reel'.($r+1)][0] == $wild[0])
                        $respinRows[] = $r;
                $gcv = $lastRspinReels['gcv'];
            }

            for( $i = 0; $i <= 600; $i++ )
            {
                $totalWin = 0;
                $freespinsWon = 0;
                $lineWins = [];
                $mpl = 1;
                $reels = $slotSettings->GetReelStrips($winType, $reelName, $boardHeight, $gcv);
                
                $isLockedWild = false;
                $linesId = $slotSettings->GetPaylines($reels['height']);
                $lines = count($linesId);
                $cWins = array_fill(0, $lines, 0);        
                if($cmd == 'ReSpin' || $cmd == 'ReSpinInFreeSpin')
                {
                    $lastRspinReels = $slotSettings->GetGameData($slotSettings->slotId . 'RespinReels');
                    for($r = 0; $r < 6; $r++)
                        if($lastRspinReels['reel'.($r+1)][0] == $wild[0])
                        {
                            $reels['reel'.($r+1)] = array_fill(0, $boardHeight, 0);
                        }
                }        
                $reels0 = $reels;

                //check wild for super stretch
                $wildMultipliers = [];
                $lastWildMultiplierCnt = 0;
                if($cmd == 'ReSpin' || $cmd == 'ReSpinInFreeSpin')
                {
                    $wildMultipliers = $slotSettings->GetGameData($slotSettings->slotId . 'WildMultipliers');
                    $lastWildMultiplierCnt = count($wildMultipliers);
                }
                for($r = 0; $r < 6; $r++)
                {
                    $multiplier = 0;
                    for($c = 0; $c < $reels['height']; $c++)
                    {
                        if($reels['reel'.($r+1)][$c] == $wild[0])
                        {
                            $multiplier++;                            
                        }
                    }
                    if($multiplier > 0 && !in_array($r, $respinRows))
                    {
                        $reels['reel'.($r+1)] = array_fill(0, $boardHeight, 0);
                        $wildMultipliers[] = [
                            'r' => $r,
                            'ri' => 0,
                            'wc' => $multiplier,
                            'x' => $multiplier
                        ];
                    }
                }

                $scatterCnt = 0;                
                for($r = 0; $r < 6; $r++)            
                    for($c = 0; $c < $boardHeight; $c++)
                    {
                        if($reels['reel'.($r+1)][$c] == $scatter)
                        {
                            $scatterCnt++;
                            $scatterPos[] = $c * 6 + $r;
                        }
                    }

                if(count($wildMultipliers) > $lastWildMultiplierCnt && (rand(0, 100) < 10 || $postData['slotEvent'] == 'freespin')) //trigger sticky wild with low possibility
                {
                    if($scatterCnt < 5)
                        $isLockedWild = true;
                }

                $bonusMpl = 1;
                
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
                                $mpl = 1;
                                if( in_array($s[0], $wild) || in_array($s[1], $wild) || in_array($s[2], $wild) ) 
                                {
                                    $mpl = $this->GetWildMultiplier($wildMultipliers, 3);
                                }
                                $emptyLine = [[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0]];                                
                                $coin = $slotSettings->Paytable[$csym][3] * $mpl * $bonusMpl;
                                $tmpWin = $coin * $betLine;
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $cWins[$k] = $tmpWin;
                                    $emptyLine[0][$p0] = 1;
                                    $emptyLine[1][$p1] = 1;
                                    $emptyLine[2][$p2] = 1;
                                    $winline = [$k+1, $coin, $tmpWin, $this->getPositions([$p0,$p1,$p2,-1,-1,-1], $reels['height'], 3), $csym, 3, $mpl];
                                }
                            }
                            if( ($s[0] == $csym || in_array($s[0], $wild)) &&
                                ($s[1] == $csym || in_array($s[1], $wild)) &&
                                ($s[2] == $csym || in_array($s[2], $wild)) && 
                                ($s[3] == $csym || in_array($s[3], $wild)) ) 
                            {
                                $mpl = 1;
                                if( in_array($s[0], $wild) || in_array($s[1], $wild) || in_array($s[2], $wild) || in_array($s[3], $wild) ) 
                                {
                                    $mpl = $this->GetWildMultiplier($wildMultipliers, 4);
                                }
                                $emptyLine = [[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0]];                                
                                $coin = $slotSettings->Paytable[$csym][4] * $mpl * $bonusMpl;
                                $tmpWin = $coin * $betLine;
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $cWins[$k] = $tmpWin;
                                    $emptyLine[0][$p0] = 1;
                                    $emptyLine[1][$p1] = 1;
                                    $emptyLine[2][$p2] = 1;
                                    $emptyLine[3][$p3] = 1;
                                    $winline = [$k+1, $coin, $tmpWin, $this->getPositions([$p0,$p1,$p2,$p3,-1,-1], $reels['height'], 4), $csym, 4, $mpl];
                                }
                            }
                            if( ($s[0] == $csym || in_array($s[0], $wild)) &&
                                ($s[1] == $csym || in_array($s[1], $wild)) &&
                                ($s[2] == $csym || in_array($s[2], $wild)) &&
                                ($s[3] == $csym || in_array($s[3], $wild)) &&
                                ($s[4] == $csym || in_array($s[4], $wild)) ) 
                            {
                                $mpl = 1;
                                if( in_array($s[0], $wild) || in_array($s[1], $wild) || in_array($s[2], $wild) || in_array($s[3], $wild) || in_array($s[4], $wild)  ) 
                                {
                                    $mpl = $this->GetWildMultiplier($wildMultipliers, 5);
                                }
                                $emptyLine = [[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0]];                                
                                $coin = $slotSettings->Paytable[$csym][5] * $mpl * $bonusMpl;
                                $tmpWin = $coin * $betLine;
                                if( $cWins[$k] < $tmpWin )
                                {
                                    $cWins[$k] = $tmpWin;
                                    $emptyLine[0][$p0] = 1;
                                    $emptyLine[1][$p1] = 1;
                                    $emptyLine[2][$p2] = 1;
                                    $emptyLine[3][$p3] = 1;
                                    $emptyLine[4][$p4] = 1;
                                    $winline = [$k+1, $coin, $tmpWin, $this->getPositions([$p0,$p1,$p2,$p3,$p4,-1], $reels['height'], 5), $csym, 5, $mpl];
                                }
                            }
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && 
                                ($s[1] == $csym || in_array($s[1], $wild)) && 
                                ($s[2] == $csym || in_array($s[2], $wild)) && 
                                ($s[3] == $csym || in_array($s[3], $wild)) && 
                                ($s[4] == $csym || in_array($s[4], $wild)) && 
                                ($s[5] == $csym || in_array($s[5], $wild)) ) 
                            {
                                $mpl = 1;
                                if( in_array($s[0], $wild) || in_array($s[1], $wild) || in_array($s[2], $wild) || in_array($s[3], $wild) || in_array($s[4], $wild) || in_array($s[5], $wild)) 
                                {
                                    $mpl = $this->GetWildMultiplier($wildMultipliers, 6);
                                }
                                $emptyLine = [[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0],[0,0,0,0,0,0]];
                                $coin = $slotSettings->Paytable[$csym][6] * $mpl * $bonusMpl;
                                $tmpWin = $coin * $betLine;
                                if( $cWins[$k] < $tmpWin )
                                {
                                    $cWins[$k] = $tmpWin;
                                    $emptyLine[0][$p0] = 1;
                                    $emptyLine[1][$p1] = 1;
                                    $emptyLine[2][$p2] = 1;
                                    $emptyLine[3][$p3] = 1;
                                    $emptyLine[4][$p4] = 1;
                                    $emptyLine[5][$p5] = 1;
                                    $winline = [$k+1, $coin, $tmpWin,$this->getPositions([$p0,$p1,$p2,$p3,$p4,$p5], $reels['height'], 6), $csym, 6, $mpl];
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
                
                if( $scatterCnt >= 5 && $winType != 'bonus' && $postData['slotEvent'] == 'bet')
                    continue;
                if ($scatterCnt < 6 && $winType == 'bonus')
                    continue;

                if($scatterCnt >= 5)
                {
                    $freespinsWon = $scatterCnt;
                }

                if($minTotalWin == -1 || ($minTotalWin > $totalWin && $totalWin > 0))
                {
                    $minTotalWin = $totalWin;
                    $minLineWins = $lineWins;
                    $minFreespinsWon = $freespinsWon;
                    $minReels = $reels;
                    $minReels0 = $reels0;                         
                    $minWildMultipliers = $wildMultipliers;
                    $minIsLockedWild = $isLockedWild;
                    $minScatterPos = $scatterPos;
                }

                if($this->debug)
                {
                    $spinAcquired = true;
                    break;
                }                    

                if($totalWin <= $spinWinLimit && (($totalWin > 0 && $winType != 'none') || ($winType == 'bonus' && $scatterCnt >= 5)))
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
                $wildMultipliers = $minWildMultipliers;
                $isLockedWild = $minIsLockedWild;
                $scatterPos = $minScatterPos;
            }

            $this->lastReels = $reels;           
            
            $coinWin = 0; //coins won
            $prizes = [];

            if(!empty($lineWins))
            {
                foreach($lineWins as $winline)
                {
                    $symbol = $winline[4];                    
                    $coinWin += $winline[1]; //sum up coins
                    $prizes[] = [
                        'id' => $symbol.':'.$winline[5],
                        'li' => $winline[0],
                        'm' => 'LtoR',                        
                        'sp' => $winline[3],
                        't'=>'line',
                        'w' => $winline[2],
                        'wx' => $winline[6],
                        'x' => 1,
                    ];
                }
            }

            $slotSettings->SetGameData($slotSettings->slotId . 'CoinWin', $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') + $coinWin);
            if($postData['slotEvent'] == 'bet')
                $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', $totalWin);
            else
                $slotSettings->SetGameData($slotSettings->slotId . 'GameWin',  $slotSettings->GetGameData($slotSettings->slotId . 'GameWin') + $totalWin);
            
            //nextCmds
            $nextCmds = [];

            $needRespin = false;
            $eventData = [
                'accC' => $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin'),
                'accWa' => number_format($slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') * $betLine, 2),                
                'manualNoWin' => $manualNoWin,
            ];            
  
            $freespinLeft = 0;

            $rs = [];

            $lastSpinType = 'Spin';
            $nextSpinType = 'Spin';            
            
            $fin = true;
            $fs = [];
            
            if($postData['slotEvent'] == 'bet')
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'BaseWinning', $totalWin);
                if($freespinsWon > 0)
                {
                    //trigger freespin
                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreespinWinCoin', 0);                    
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames',  $freespinsWon);
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreespinLastReels',  $reels);
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreespinScatterPos',  $scatterPos);
                    $postData['slotEvent'] = 'freespin';
                    $this->gameState = 'Pending';
                    $fin = false;

                    $fs = [
                        'aw' => 0,
                        'cp' => 0,
                        'id' => 'FreeSpin',
                        'sa' => $freespinsWon,
                        'sr' => $freespinsWon,
                        't' => [
                            'o' => $scatterPos,
                            's' => $freespinsWon,
                            'v' => [$reels['reel1'], $reels['reel2'], $reels['reel3'], $reels['reel4'], $reels['reel5'], $reels['reel6']]
                        ]
                    ];
                    $nextSpinType = 'FreeSpin';
                    $freespinLeft = $freespinsWon;
                    $nextCmds = ['FreeSpin'];
                }
            }
            else if($postData['slotEvent'] == 'freespin')
            {
                $fin = true;
                $this->gameState = 'Pending';
                if($cmd != 'ReSpinInFreeSpin')
                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);
                if($freespinsWon > 0)
                {
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames',  $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + $freespinsWon);
                }

                $totalFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                $freespinLeft = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');

                $lastReels = $slotSettings->GetGameData($slotSettings->slotId . 'FreespinLastReels');
                $fs = [
                    'aw' => 0,
                    'cp' => $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame'),
                    'id' => 'FreeSpin',
                    'sa' => $totalFreespin,
                    'sr' => $freespinLeft,
                    't' => [
                        'o' => $slotSettings->GetGameData($slotSettings->slotId . 'FreespinScatterPos'),
                        's' => $freespinsWon,
                        'v' => [$lastReels['reel1'], $lastReels['reel2'], $lastReels['reel3'], $lastReels['reel4'], $lastReels['reel5'], $lastReels['reel6']]
                    ]
                ];
                $nextSpinType = 'FreeSpin';
                $lastSpinType = 'FreeSpin';
                if($freespinLeft > 0)
                    $nextCmds[] = 'FreeSpin';                
                else if($freespinLeft == 0)
                {
                    $nextSpinType = 'Spin';
                    $nextCmds[] = 'C';
                }
            }

            if($cmd == 'ReSpin' || $cmd == 'ReSpinInFreeSpin')
            {
                $lastRspinReels = $slotSettings->GetGameData($slotSettings->slotId . 'RespinReels');
                if($isLockedWild)
                {
                    //trigger respin again
                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalReSpins', $slotSettings->GetGameData($slotSettings->slotId . 'TotalReSpins') + 1);        
                    $slotSettings->SetGameData($slotSettings->slotId . 'RespinReels', $reels);            
                }
                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentRespin', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentRespin') + 1);

                $leftRespin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalReSpins') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentRespin');
                $lastSpinType = $cmd;
                $id = 'ReSpin';
                if($postData['slotEvent'] == 'freespin')
                    $id = 'ReSpinInFreeSpin';
                
                $rs = [
                    'aw' => 0,
                    'cp' => $slotSettings->GetGameData($slotSettings->slotId . 'CurrentRespin'),
                    'id' => $id,
                    'sa' => $slotSettings->GetGameData($slotSettings->slotId . 'TotalReSpins'),
                    'sr' => $leftRespin,
                    't' => [
                        'o' => [],
                        's' => 0,
                        'v' => [$lastRspinReels['reel1'], $lastRspinReels['reel2'], $lastRspinReels['reel3'], $lastRspinReels['reel4'], $lastRspinReels['reel5'], $lastRspinReels['reel6']]
                    ]
                ];
                if($leftRespin == 0)
                {
                    if($postData['slotEvent'] == 'freespin')
                        $nextSpinType = 'FreeSpin';
                }
                else
                {
                    $nextSpinType = 'ReSpin';
                    if($postData['slotEvent'] == 'freespin')
                        $nextSpinType = 'ReSpinInFreeSpin';
                }
            }
            else
            {
                //trigger respin
                if($isLockedWild)
                {
                    $id = 'ReSpin';
                    if($postData['slotEvent'] == 'freespin')
                        $id = 'ReSpinInFreeSpin';
                    $rs = [
                        'aw' => 0,
                        'cp' => 0,
                        'id' => $id,
                        'sa' => 1,
                        'sr' => 1,
                        't' => [
                            'o' => [],
                            's' => 0,
                            'v' => [$reels['reel1'], $reels['reel2'], $reels['reel3'], $reels['reel4'], $reels['reel5'], $reels['reel6']]
                        ]
                    ];

                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalReSpins', 1);
                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentRespin', 0);

                    $slotSettings->SetGameData($slotSettings->slotId . 'RespinReels', $reels);
                    $slotSettings->SetGameData($slotSettings->slotId . 'WildMultipliers', $wildMultipliers);
                    $this->gameState = 'Pending';
                    $nextSpinType = 'ReSpin';
                    if($postData['slotEvent'] == 'freespin')
                        $nextSpinType = 'ReSpinInFreeSpin';
                    $nextCmds = [$nextSpinType];
                }
            }
            
            if($freespinLeft)
                $fin = false;
            
            $clientData = [
                'a' => $lastSpinType,
                'aa' => [$nextSpinType],
                'br' => false,
                'bw' => $slotSettings->GetGameData($slotSettings->slotId . 'BaseWinning'),
                'cw' => $totalWin,
                'fin' => $fin,
                'fv' => [$reels['reel1'], $reels['reel2'], $reels['reel3'], $reels['reel4'], $reels['reel5'], $reels['reel6']],
                'gcv' => $reels['gcv'],
                'ilw' => $isLockedWild,
                'iv' => [$reels0['reel1'], $reels0['reel2'], $reels0['reel3'], $reels0['reel4'], $reels0['reel5'], $reels0['reel6']],
                'rl' => '6x'.$reels['height'],
                'ts' => $postData['coin'],
                'tw' => $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') * $betLine,
                'v' => '1.0.0',
                'w' => $prizes,
                'wr' => $wildMultipliers,
            ];

            if(count($rs) > 0)
            {
                if($postData['slotEvent'] == 'freespin')
                    $clientData['rsfs'] = $rs;
                else
                    $clientData['rs'] = $rs;
            }
                
            if(count($fs) > 0)
                $clientData['fs'] = $fs;

            $eventData['response'] = [
                'cashWin' => '0.00',
                'clientData' => $clientData,
                'coinWin'=>'0'
            ];
            // $eventData['playerState'] = $clientData;
            // $eventData['initialPlayerState'] = $clientData;

            if($needRespin)
            {
                $this->gameState = 'Pending';
            }
            else
            {
                if($totalWin > 0)
                {
                    $this->gameState = 'Pending';
                    if($freespinLeft == 0 && !$isLockedWild)
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

        function GetWildMultiplier($wildMultipliers, $cnt)
        {
            $multiplier = 0;
            for($i = 0; $i < $cnt; $i++)
            {
                if(isset($wildMultipliers[$i]))
                {
                    $multiplier += $wildMultipliers[$i]['x'];
                }
            }
            if($multiplier == 0)
                $multiplier = 1;
            return $multiplier;
        }
    }

}


