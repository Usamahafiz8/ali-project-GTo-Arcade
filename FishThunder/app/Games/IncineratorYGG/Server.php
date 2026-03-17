<?php 
namespace VanguardLTE\Games\IncineratorYGG
{
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;    

    class Server
    {
        public $debug = false;
        public $consec_win = 0;
        public $gameState;
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
                        
                        $response = file(base_path() . '/app/Games/IncineratorYGG/translation.txt')[0];                                                                          
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
                        $response = '{"code":0,"data":{"id":"","org":null,"gameid":0,"data":{"appsrv":"","file":"/init/","gameid":"7320","height":"600","license":"","org":"Demo","root":"","type":"Html","width":"800"}},"fn":"clientinfo","utcts":'.time().'}';
                        break;
                    case 'game':
                        $filename = base_path() . '/app/Games/IncineratorYGG/game.txt';
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
                        $lines = count($linesId);
                        
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
                            //collect cash step
                            $won = $slotSettings->GetGameData($slotSettings->slotId . 'GameWon');
                            $coin = $slotSettings->GetGameData($slotSettings->slotId . 'Coin');
                            $allbet = $coin * $lines;

                            $buyBalance = $slotSettings->GetBalance();                            
                            $slotSettings->SetBank($slotSettings->GetGameData($slotSettings->slotId . 'LastBet'), -1 * $won);                                    
                            $slotSettings->SetBalance($won);
                            $slotSettings->SetWin($won);


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
                                    'resultBal' => ['cash' =>  $slotSettings->GetBalance()],
                                    'wager' => [
                                        'bets' => [
                                            [
                                                'step' => $postData['step'] + 1,
                                                'betamount' => 0,
                                                'betcurrency' => 'USD',
                                                'wonamount' => $won,
                                                'status' => 'RESULTED',
                                                'betdata'=> [
                                                    'doubleA' => $won,
                                                    'doubleN' => 1,
                                                    'cheat' => null,
                                                    'cmd' => 'C',
                                                    'coin' => $slotSettings->GetGameData($slotSettings->slotId . 'Coin'),
                                                    'nCoins' => 1,
                                                    'restoredAccumulatedWonCoin' => 0,
                                                    'variant' => null,
                                                    'lines' => '11111111111111111111'
                                                ],
                                                'eventdata' => ['exactWon' => $won],
                                                'prizes' => [
                                                    [
                                                        'descr' => 'Cash out',
                                                        'gameId' => '7339',
                                                        'netamount' => $won,
                                                        'prizedata' => null,
                                                        'prizeid' => '111',
                                                        'type' => 'FIXED',
                                                        'wonamount' => $won,
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
                            $slotSettings->SaveLogReport($response, $allbet, $won, $postData['slotEvent']);   
                            break;
                        }

                        $betLine = 0;
                        if(isset($postData['coin']))
                            $betLine = $postData['coin'];
                        $allbet = $betLine * $lines;
                        if($allbet > $slotSettings->GetBalance())
                        {
                            return '{"completion":"Unknown","code":1006,"errorCode":"NO_SUFFICIENT_FUNDS","type":"O","rid":"220215083220::e14db45d-39e6-4cee-a076-ebb72ca0a89b","msg":"You do not have sufficient funds for the bet","fn":null,"details":null,"relaunchUrl":null,"timeElapsed":null,"errorType":null,"balanceDifference":null,"suppressed":[]}
                            ';
                        }
                        
                        $slotSettings->UpdateJackpots($allbet);
                        $slotSettings->SetGameData($slotSettings->slotId . 'InitialBalance', $slotSettings->GetBalance());
                        $slotSettings->SetBalance(-1 * $allbet, $postData['slotEvent']);
                        $bankSum = $allbet / 100 * $slotSettings->GetPercent();
                        $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $bankSum, $postData['slotEvent']);
                        $slotSettings->SetBet($allbet);

                        $bets = [];
                        $slotSettings->SetGameData($slotSettings->slotId . 'SpinStep', 1);
                        $slotSettings->SetGameData($slotSettings->slotId . 'GameWon', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'GameCoinWon', 0);

                        $this->gameMode = 0; //general spin                
                        
                        $this->gameState = "Finished";
                        $bonusMpl = 1;
                        $slotSettings->SetGameData($slotSettings->slotId . 'LastBet', 'bet');
                        $this->doSpin($slotSettings, $postData, $bets, $bonusMpl, 0);
                        
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

        function doSpin($slotSettings, $postData, &$bets, $bonusMpl, $spinIndex)
        {
            //if $spinIndex is lower than 0, it it is fake spin            
            $linesId = $slotSettings->GetPaylines();
            $lines = count($linesId);
            $betLine = 0;
            if(isset($postData['coin']))
                $betLine = $postData['coin'];
            $allbet = $betLine * $lines;

            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $lines);
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
            $symbolString = $slotSettings->SymbolString;
            $gameWon = $slotSettings->GetGameData($slotSettings->slotId . 'GameWon');
            $spinAcquired = false;
            for( $i = 0; $i <= 300; $i++ )
            {
                $totalWin = 0;
                $coinWin = 0;
                $lineWins = [];
                $symbolWin = array_fill(0, count($slotSettings->SymbolGame), 0);                
                $stickyB = array_fill(0, 15, 0);
                $stickyA = array_fill(0, 15, 0);                            
                $stickyN = array_fill(0, 15, 0);                
                $cWins = array_fill(0, 25, 0);
                $wild = [0]; //there is no wild symbol in this game                            
                $reels = $slotSettings->GetReelStrips($winType, $postData['slotEvent']);

                if($this->debug)
                {
                    //reel test
                    if($postData['slotEvent'] != 'freespin')
                    {
                        $reels['reel1'] = [5,2,2];
                        $reels['reel2'] = [4,2,2];
                        $reels['reel3'] = [2,2,5];                        
                        $reels['reel4'] = [6,6,4];
                        $reels['reel5'] = [6,1,1];                        
                    }
                }
                
                for( $k = 0; $k < $lines; $k++ ) 
                {
                    $winline = [];
                    for( $j = 0; $j < count($slotSettings->SymbolGame); $j++ ) 
                    {
                        $csym = $slotSettings->SymbolGame[$j];
                        if( !isset($slotSettings->Paytable['SYM_' . $csym]) ) 
                        {
                        }
                        else
                        {
                            $s = [];
                            $s[0] = $reels['reel1'][$linesId[$k][0]];
                            $s[1] = $reels['reel2'][$linesId[$k][1]];
                            $s[2] = $reels['reel3'][$linesId[$k][2]];
                            $s[3] = $reels['reel4'][$linesId[$k][3]];
                            $s[4] = $reels['reel5'][$linesId[$k][4]];
                            $p0 = $linesId[$k][0];
                            $p1 = $linesId[$k][1];
                            $p2 = $linesId[$k][2];
                            $p3 = $linesId[$k][3];
                            $p4 = $linesId[$k][4];                     
                                                                        
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) ) 
                            {
                                $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
                                $mpl = 1;                                                        
                                $tmpWin = $slotSettings->Paytable['SYM_' . $csym][3] * $betLine * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $symbolWin[$csym] += ($tmpWin - $cWins[$k]);
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
                                    $symbolWin[$csym] += ($tmpWin - $cWins[$k]);
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
                                    $symbolWin[$csym] += ($tmpWin - $cWins[$k]);
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
                    }
                    if( $cWins[$k] > 0 && !empty($winline))
                    {
                        array_push($lineWins, $winline);
                        $totalWin += $cWins[$k];
                    }
                }
              
                $totalWin += $gameWon;

                
                if($this->debug)
                    break;

                if($totalWin <= $spinWinLimit && $winType != 'none' && $totalWin > 0)
                {
                    $spinAcquired = true;
                    break;
                }                                    
                else if( $winType == 'none' && $totalWin == $gameWon) 
                {
                    break;
                }               
            }

            if(!$spinAcquired && $totalWin > 0)
            {
                $reels = $slotSettings->GetNoWinSpin($postData['slotEvent']);
                $lineWins = [];
                $totalWin = 0;
            }        

            $activeLines = array_fill(0, $lines, 0);
            $activeSymbols = array_fill(0, 15, 0);
            if(!empty($lineWins))
            {
                foreach($lineWins as $winline)
                {
                    $activeLines[$winline[0]] = 1;
                    $winsyms = $winline[2];
                    for($c = 0; $c < 15; $c++)
                        if($winsyms[$c] == 1)
                            $activeSymbols[$c] = 1;

                    $coinWin += $winline[1];
                }
            }
            $slotSettings->SetGameData($slotSettings->slotId . 'GameCoinWon', $slotSettings->GetGameData($slotSettings->slotId . 'GameCoinWon') + $coinWin);
            $slotSettings->SetGameData($slotSettings->slotId . 'GameWon', $slotSettings->GetGameData($slotSettings->slotId . 'GameWon') + $totalWin);
            //create bet info
            $first_bet = [
                'betAmount' => $allbet,
                'betCurrency' => 'USD',
                'betdata' => [
                    'cheat' => null,
                    'clientData' => null,
                    'coin' => $betLine,
                    'initialBalance' => $slotSettings->GetGameData($slotSettings->slotId . 'InitialBalance'),
                    'lines' => '11111111111111111111',
                    'nCoins' => 1
                ],
                'eventdata' => [
                    'accC' => $slotSettings->GetGameData($slotSettings->slotId . 'GameCoinWon'),
                    'accWa' => $slotSettings->GetGameData($slotSettings->slotId . 'GameWon'),                    
                    'wonCoins' => $coinWin,
                    'reels' => $this->getReelSymbol($symbolString, $reels),                    
                    'reelSet' => 'Reels',                    
                    'rpos' => null,
                    'wonCoins' => 0,
                    'wts' => ['00000000000000000000000000', '000000000000000'],
                    'wtw' => []
                ],
                'prepaid' => false,
                'prizes' => null,
                'status' => 'RESULTED',                
                'wonamount' => 0
            ];

            $needRespin = false;
            $this->consec_win = 0;
            $reels_left = $this->getReelCollapsed($symbolString, $reels, $activeSymbols);
            for($r = 0; $r < 5; $r++)
                for($c = 0; $c < 3; $c++)
                {
                    if($reels_left[$r][$c] != '')
                        $stickyA[$r * 3 + $c] = 1;
                }
            if($totalWin > $gameWon + 1e-5)
            {
                $this->gameState = 'Pending';
                $needRespin = true;
                $first_bet['eventdata']['freeSpins'] = 1;
                $first_bet['eventdata']['freeSpinsAwarded'] = 1;
                $first_bet['eventdata']['stickyA'] = implode('', $stickyA);
                $wts = [implode('', $activeLines), implode('', $activeSymbols)];
                $first_bet['eventdata']['wts'] = $wts;
                $first_bet['eventdata']['wtw'] = $lineWins;
                $first_bet['step'] = $slotSettings->GetGameData($slotSettings->slotId . 'SpinStep');
                $first_bet['eventdata']['reels1'] = $reels_left;
                $this->consec_win++;
            }
             

            $bets[] = $first_bet;
            $slotSettings->SetGameData($slotSettings->slotId . 'SpinStep', $slotSettings->GetGameData($slotSettings->slotId . 'SpinStep') + 1);
            
            if($winTypeTmp[0] == 'bonus')
                $winTypeTmp[1] *= 10;
            while($needRespin)
            {
                $needRespin = $this->doSubSpin($totalWin, $bets, $reels_left, $winTypeTmp, $slotSettings, $postData, $bonusMpl, $spinIndex);
            }            
        }
        
        function doSubSpin(&$lastWin, &$bets, &$lastReels, $winTypeTmp, $slotSettings, $postData, $bonusMpl, $spinIndex)
        {
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
            $stickyB = array_fill(0, 15, 0);
            $stickyA = array_fill(0, 15, 0);                            
            $stickyN = array_fill(0, 15, 0);
            $betLine = $postData['coin'];
            $allbet = $betLine * count($slotSettings->GetPaylines());
            $gameWon = $slotSettings->GetGameData($slotSettings->slotId . 'GameWon');
            $wildPattern = false;
            if($this->consec_win == 3)
            {
                //if win 3 times consecutively, enable wild pattern feature
                $this->consec_win = 0;
                $wildPattern = true;
            }
            
            $linesId = $slotSettings->GetPaylines();
            $lines = count($linesId);
            $spinAcquired = false;

            $minTotalWin = -1;
            $minLineWins = [];
            $minReels = [];
            $minOriginalReels = [];
            $postData['slotEvent'] = 'freespin';
            $slotSettings->SetGameData($slotSettings->slotId . 'LastBet', 'freespin');
            for( $i = 0; $i <= 1000; $i++ ) 
            {
                $totalWin = 0;
                $coinWin = 0;
                $lineWins = [];                               
                
                $cWins = array_fill(0, 20, 0);
                $wild = [0]; 
                $reels = $slotSettings->GetReelStrips($winType, $postData['slotEvent']);
                if($this->debug && $gameWon < 7)
                {
                    $reels['reel1'] = [6,3,4];
                    $reels['reel2'] = [6,1,5];
                    $reels['reel3'] = [6,2,1];  
                }
                $originalReel = $reels;
                //keep last reel remained symbols
                $wilds = $slotSettings->GetWildPattern(0);
                for($r = 0; $r < 5; $r++)
                {
                    for($c = 0; $c < 3; $c++)
                    {
                        if($lastReels[$r][$c] != '')
                        {
                            $reels['reel'.($r+1)][$c] = $slotSettings->SymbolIndex[$lastReels[$r][$c]];                            
                        }
                    }
                }
                
                for($r = 0; $r < 5; $r++)
                {
                    for($c = 0; $c < 3; $c++)
                    {
                        // if($lastReels[$r][$c] != '')
                        {
                            if($wildPattern && $wilds[$r * 3 + $c] == 1)
                            {
                                $reels['reel'.($r+1)][$c] = 0;
                            }
                        }
                    }
                }
                
                for( $k = 0; $k < $lines; $k++ )
                {
                    for( $j = 0; $j < count($slotSettings->SymbolGame); $j++ ) 
                    {
                        $csym = $slotSettings->SymbolGame[$j];
                        if( !isset($slotSettings->Paytable['SYM_' . $csym]) ) 
                        {
                        }
                        else
                        {
                            $s = [];
                            $s[0] = $reels['reel1'][$linesId[$k][0]];
                            $s[1] = $reels['reel2'][$linesId[$k][1]];
                            $s[2] = $reels['reel3'][$linesId[$k][2]];
                            $s[3] = $reels['reel4'][$linesId[$k][3]];
                            $s[4] = $reels['reel5'][$linesId[$k][4]];
                            $p0 = $linesId[$k][0];
                            $p1 = $linesId[$k][1];
                            $p2 = $linesId[$k][2];
                            $p3 = $linesId[$k][3];
                            $p4 = $linesId[$k][4];                     
                                                                        
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
                    }
                    if( $cWins[$k] > 0 && !empty($winline))
                    {
                        array_push($lineWins, $winline);
                        $totalWin += $cWins[$k];
                    }
                }

                $totalWin += $gameWon;          
                if($totalWin < $minTotalWin || $minTotalWin == -1)
                {
                    $minTotalWin == $totalWin;
                    $minLineWins = $lineWins;
                    $minReels = $reels;
                    $minOriginalReels = $originalReel;
                }     

                if($this->debug)
                    break;
                
                if($totalWin <= $spinWinLimit && (($totalWin > 0 && $winType != 'none') ))
                {
                    $spinAcquired = true;
                    if($totalWin < 0.5 * $spinWinLimit && $winType != 'bonus')
                        $spinAcquired = false;
                    if($spinAcquired)
                        break;
                }                                    
                else if( $winType == 'none' && $totalWin == 0) 
                {
                    break;
                }
            }

            if(!$spinAcquired && $totalWin > $gameWon)
            {
                $totalWin = $minTotalWin;
                $lineWins = $minLineWins;
                $reels = $minReels;
                $originalReel = $minOriginalReels;
            }
            
            $needRespin = false;
            if($totalWin > $lastWin + 1e-5)
            {
                //able to do more spin
                $needRespin = true;
                $lastWin = $totalWin;
            }            

            $activeLines = array_fill(0, $lines, 0);
            $activeSymbols = array_fill(0, 15, 0);
            if(!empty($lineWins))
            {
                foreach($lineWins as $winline)
                {
                    $activeLines[$winline[0]] = 1;
                    $winsyms = $winline[2];
                    for($c = 0; $c < 15; $c++)
                        if($winsyms[$c] == 1)
                            $activeSymbols[$c] = 1;

                    $coinWin += $winline[1];
                }
            }

            $slotSettings->SetGameData($slotSettings->slotId . 'GameCoinWon', $slotSettings->GetGameData($slotSettings->slotId . 'GameCoinWon') + $coinWin);
            $slotSettings->SetGameData($slotSettings->slotId . 'GameWon', $totalWin);
            $symbolString = $slotSettings->SymbolString;
            $lastReels = $this->getReelCollapsed($symbolString, $reels, $activeSymbols);
            for($r = 0; $r < 5; $r++)
                for($c = 0; $c < 3; $c++)
                {
                    if($lastReels[$r][$c] != '')
                        $stickyA[$r * 3 + $c] = 1;
                }
            $bet = [
                'betAmount' => 0,
                'betCurrency' => 'USD',
                'betdata' => [
                    'cheat' => null,
                    'clientData' => null,
                    'coin' => $betLine,
                    'initialBalance' => $slotSettings->GetGameData($slotSettings->slotId . 'InitialBalance'),
                    'lines' => '11111111111111111111',
                    'nCoins' => 1
                ],
                'eventdata' => [
                    'accC' =>  $slotSettings->GetGameData($slotSettings->slotId . 'GameCoinWon'),
                    'accWa' =>  $slotSettings->GetGameData($slotSettings->slotId . 'GameWon'),                             
                    'reels' => $this->getReelSymbol($symbolString, $reels),                    
                    'reelSet' => 'FeatureReels',                    
                    'rpos' => null,
                    'wonCoins' => $coinWin                    
                ],
                'prepaid' => false,
                'prizes' => null,
                'status' => 'RESULTED',
                'step' => $slotSettings->GetGameData($slotSettings->slotId . 'SpinStep'),
                'wonamount' => 0
            ];
            
            if($wildPattern)
            {
                $addedWilds = [];
                for($r = 0; $r < 15; $r++)
                    if($wilds[$r] == 1)
                        $addedWilds[] = $r;
                $bet['eventdata']['addedWilds'] = $addedWilds;
                $bet['eventdata']['reels0'] = $this->getReelSymbol($symbolString, $originalReel);
            }
            if($needRespin)
            {                
                $wts = [implode('', $activeLines), implode('', $activeSymbols)];
                $bet['eventdata']['wts'] = $wts;
                $bet['eventdata']['wtw'] = $lineWins;
                $bet['eventdata']['freeSpins'] = 1;
                $bet['eventdata']['freeSpinsAwarded'] = 1;
                $bet['eventdata']['reels1'] = $lastReels;
                $bet['eventdata']['stickyA'] = implode('', $stickyA);
                $this->consec_win++;
            }
            else
            {
                //save game coin win
                $slotSettings->SetGameData($slotSettings->slotId . 'GameCoinWon', $slotSettings->GetGameData($slotSettings->slotId . 'GameCoinWon') + $coinWin);
                $slotSettings->SetGameData($slotSettings->slotId . 'GameWon', $totalWin);
                $slotSettings->SetGameData($slotSettings->slotId . 'Coin', $betLine);

                $bet['eventdata']['nextCmds'] = 'C';
                $bet['eventdata']['accWa'] = $slotSettings->GetGameData($slotSettings->slotId . 'GameWon');
                $bet['eventdata']['doubleAmount'] = $slotSettings->GetGameData($slotSettings->slotId . 'GameWon');
                $bet['eventdata']['doubleCount'] = 1;                
                $bet['eventdata']['wts'] = ['000000000000000000000', '000000000000000'];
                $bet['eventdata']['wtw'] = [];                
            }

            $bets[] = $bet;            
            $slotSettings->SetGameData($slotSettings->slotId . 'SpinStep', $slotSettings->GetGameData($slotSettings->slotId . 'SpinStep') + 1);
            return $needRespin;
        }
        
        function getReelSymbol($symbolString, $reels)
        {   
            return [
                [ $symbolString[$reels['reel1'][0]], $symbolString[$reels['reel1'][1]], $symbolString[$reels['reel1'][2]] ],
                [ $symbolString[$reels['reel2'][0]], $symbolString[$reels['reel2'][1]], $symbolString[$reels['reel2'][2]] ],
                [ $symbolString[$reels['reel3'][0]], $symbolString[$reels['reel3'][1]], $symbolString[$reels['reel3'][2]] ],
                [ $symbolString[$reels['reel4'][0]], $symbolString[$reels['reel4'][1]], $symbolString[$reels['reel4'][2]] ],
                [ $symbolString[$reels['reel5'][0]], $symbolString[$reels['reel5'][1]], $symbolString[$reels['reel5'][2]] ]
            ];
        }

        function getReelCollapsed($symbolString, $reels, $activeSymbols)
        {
            $reels_left = [];
            for($r = 0; $r < 5; $r++)
            {
                $row = [];
                for($c = 0; $c < 3; $c++)
                {
                    $b = $symbolString[$reels['reel'.($r+1)][$c]];
                    if ($activeSymbols[$r * 3 + $c] == 1)
                    {
                        $b = '';
                    }
                    $row[] = $b;
                }
                $reels_left[] = $row;
            }
            for($r = 0; $r < 5; $r++)
            {
                for($c = 0; $c< 3; $c++)
                {
                    if($reels_left[$r][$c] == '')
                    {
                        array_splice($reels_left[$r], $c, 1);
                        array_splice($reels_left[$r], 0, 0, '');
                    }
                }
            }
            return $reels_left;
        }
           
    }
}


