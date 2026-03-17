<?php 
namespace VanguardLTE\Games\DarkJokeRizesYGG
{
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;    

    class Server
    {
        public $gameState;
        public $debug = false;
        public $isJokerizerFreespin = false;
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
                        $slotSettings->SetGameData($slotSettings->slotId . 'SupermeterCoin', 0);
                        $response = file(base_path() . '/app/Games/DarkJokeRizesYGG/translation.txt')[0];                                                                          
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
                        $response = '{"code":0,"data":{"id":"","org":null,"gameid":0,"data":{"appsrv":"","file":"/init/","gameid":"7310","height":"600","license":"","org":"Demo","root":"","type":"Html","width":"800"}},"fn":"clientinfo","utcts":'.time().'}';
                        break;
                    case 'game':
                        $filename = base_path() . '/app/Games/DarkJokeRizesYGG/game.txt';
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
                                                'step' => $slotSettings->GetGameData($slotSettings->slotId . 'Step'),
                                                'betamount' => 0,
                                                'betcurrency' => 'USD',
                                                'wonamount' => 0,
                                                'status' => 'RESULTED',
                                                'betdata'=> [
                                                    'cheat' => null,
                                                    'cmd' => 'LS',
                                                    'coin' => $slotSettings->GetGameData($slotSettings->slotId . 'BetCoin'),
                                                    'nCoins' => 1,
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
                            $slotSettings->SetGameData($slotSettings->slotId . 'Step', $slotSettings->GetGameData($slotSettings->slotId . 'Step') + 1);
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
                                                        'gameId' => '7310',
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

                        //rest cases are ''(general spin), 'S'(supermeter spin)
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
                            $slotSettings->SetGameData($slotSettings->slotId . 'MysteryWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'LastSticky', array_fill(0, 15, 0));
                            $slotSettings->SetGameData($slotSettings->slotId . 'LastScatterCount', 0);
                        }
                        else
                        {
                            $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);                            
                        }
                        
                        $this->gameState = 'Finished';
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

                        $buyBalance = $slotSettings->GetBalance();

                        $win = 0;
                        if($this->gameState == 'Finished')
                        {
                            //for jokerizer big win
                            $win = $slotSettings->GetGameData($slotSettings->slotId . 'SupermeterCoin') * $betLine;
                            if(!$this->debug)
                            {
                                $slotSettings->SetBalance($win, 'bonus');
                                $slotSettings->SetBank('', -1 * $win);
                            }
                        }
                        $resBalance = $buyBalance + $win;
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
        function doSpin($slotSettings, $postData, &$bets, $cmd)
        {
            $linesId = $slotSettings->GetPaylines();                                
            $lines = count($linesId);
            $betLine = 0;
            if(isset($postData['coin']))
                $betLine = $postData['coin'];
            $allbet = $betLine * $lines;
            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $lines);
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
            
            $spinAcquired = false;
            $stickyA = array_fill(0, 15, 0);            
            $stickyB = array_fill(0, 15, 0);
            if($postData['slotEvent'] == 'freespin' && $slotSettings->GetGameData($slotSettings->slotId . 'LastSticky') != 0)
            {
                $stickyB = $slotSettings->GetGameData($slotSettings->slotId . 'LastSticky');
            }
            $stickyN = array_fill(0, 15, 0);
            $minLineWins = [];
            $minReels = [];
            $minScattersCount = 0;
            $minScattersCoinWin = 0;
            $minTotalWin = -1;
            for( $i = 0; $i <= 500; $i++ ) 
            {
                $totalWin = 0;
                $lineWins = [];
                $cWins = array_fill(0, $lines, 0);
                $wild = []; //there is no wild symbol in this game
                $scatter = 0; //joker symbol
                $slotEvent = 'bet';
                if($cmd == 'S')
                    $slotEvent = 'normal_supermeter';
                else if($cmd == 'B')
                    $slotEvent = 'big_supermeter';

                // if($this->debug)
                // {
                // $winType = 'bonus';
                // $spinWinLimit = 1000;
                // }
                $reels = $slotSettings->GetReelStrips($winType, $slotEvent);
                if($this->isJokerizerFreespin)
                {
                    //fix joker reels from last reels
                    $lastScatterReelPos = $slotSettings->GetGameData($slotSettings->slotId . 'JokerReel');
                    for($r = 0; $r< 5; $r++)
                    {
                        if($lastScatterReelPos[$r] == 1)
                        {
                            $reels['reel'.($r+1)] = $this->lastReels['reel'.($r+1)];
                            $reels['rp'][$r] = $this->lastReels['rp'][$r];
                        }
                    }
                }
                if($cmd == 'B')
                {
                    $reels['reel3'][1] = 0;
                    $stickyB[7] = 1;
                }

                if($this->debug)
                {
                    // //reel test
                    if($postData['slotEvent'] != 'freespin')
                    {
                        $reels['rp'] = [59,3,7,60,43];
                        $reels['reel1'] = [1,1,3];
                        $reels['reel2'] = [1,6,3];
                        $reels['reel3'] = [1,6,6];
                        $reels['reel4'] = [1,6,4];
                        $reels['reel5'] = [1,1,1];
                    }
                    else
                    {
                        // $reels['reel1'][0] = 0;
                        // $reels['reel2'][0] = 0;
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
                            if($k < 5)
                            {                                                    
                                if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) ) 
                                {
                                    $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
                                    $mpl = 1;                                                        
                                    $tmpWin = $slotSettings->Paytable['SYM_' . $csym][3] * $betLine * $mpl;
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
                                    $tmpWin = $slotSettings->Paytable['SYM_' . $csym][4] * $betLine * $mpl;
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
                                    $tmpWin = $slotSettings->Paytable['SYM_' . $csym][5] * $betLine * $mpl;
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
                                    $tmpWin = $slotSettings->Paytable['SYM_' . $csym][3] * $betLine * $mpl;
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
                                    $tmpWin = $slotSettings->Paytable['SYM_' . $csym][4] * $betLine * $mpl;
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

                $scattersCoinWin = 0;                                    
                $scattersReelPos = [0,0,0,0,0];
                $scattersCount = 0;
                
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
                
                $slotSettings->SetGameData($slotSettings->slotId . 'JokerReel', $scattersReelPos);

                $scattersWin = 0;
                
                if($scattersCount == 3)
                    $scattersCoinWin = 20 * rand(50, 100);
                else if($scattersCount == 4)
                    $scattersCoinWin = 20 * rand(150, 200);
                else if($scattersCount >= 5)
                    $scattersCoinWin = 6000;
                else if($scattersCount == 2 && $postData['slotEvent'] == 'freespin')
                    $scattersCoinWin = 20 * rand(1, 10);
                if($scattersCoinWin > 0)
                {
                    $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
                    for($r = 0; $r< 5; $r++)
                        for($c = 0; $c < 3; $c++)
                        {
                            if($reels['reel'.($r+1)][$c] == 0)
                                $emptyLine[$r][$c] = 1;
                        }
                    $winline = [0, $scattersCoinWin, $this->getConvertedLine($emptyLine)];
                    array_push($lineWins, $winline);
                }

                $scattersWin = $scattersCoinWin * $betLine;
                
                $this->gameState = 'Finished';

                $totalWin += ($scattersWin);

                if($this->debug)
                {
                    $spinAcquired = true;
                    break;
                }                    

                $gameWin = $slotSettings->GetGameData($slotSettings->slotId . 'SupermeterCoin') * $betLine;
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
                    if($totalWin + $gameWin < 0.5 * $spinWinLimit)
                        $spinAcquired = false;
                    if($spinAcquired)
                        break;     
                }
                else if( $winType == 'none' && $totalWin == 0) 
                {
                    break;
                }
            }

            if(!$spinAcquired && $totalWin > 0)
            {
                $lineWins = $minLineWins;
                $reels = $minReels;
                $scattersCount = $minScattersCount;
                $scattersCoinWin = $minScattersCoinWin;
                $totalWin = $minTotalWin;
            }     

            $this->lastReels = $reels;
            for($r = 0; $r < 5; $r++)
                for($c = 0; $c < 3; $c++)
                {
                    if($reels['reel'.($r+1)][$c] == 0)
                    {
                        $stickyA[$r * 3] = 1;
                        $stickyA[$r * 3+1] = 1;
                        $stickyA[$r * 3+2] = 1;
                        
                        if($stickyB[$r * 3] != $stickyA[$r * 3])
                            $stickyN[$r * 3] = 1;
                        if($stickyB[$r * 3 + 1] != $stickyA[$r * 3 + 1])
                            $stickyN[$r * 3 + 1] = 1;
                        if($stickyB[$r * 3 + 2] != $stickyA[$r * 3 + 2])
                            $stickyN[$r * 3 + 2] = 1;
                    }
                }
            
            if($totalWin > 0)
                $slotSettings->SetWin($totalWin);
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
            }

            $slotSettings->SetGameData($slotSettings->slotId . 'SupermeterCoin', $slotSettings->GetGameData($slotSettings->slotId . 'SupermeterCoin') + $supermeter);
            $slotSettings->SetGameData($slotSettings->slotId . 'BetCoin', $betLine);
            //nextCmds
            $nextCmds = [];
            $wts = [implode('', $activeLines), implode('', $activeSymbols)];
            $eventData = [
                'accC' => $supermeter,
                'accWa' => $supermeter * $betLine,
                'reels' => $slotSettings->GetReelSymbol($reels),
                'rpos' => [$reels['rp'][0] - 1, $reels['rp'][1] - 1, $reels['rp'][2] - 1, $reels['rp'][3] - 1, $reels['rp'][4] - 1],
                'wonCoins' => $supermeter,
                'wts' => $wts,
                'wtw' => $lineWins
            ];            
            $needRespin = false;
            $prizes = null;
            
            if($cmd == 'S' || $cmd == 'B')
            {
                //minus 20 supermeter coins
                if(!$this->isJokerizerFreespin)
                {
                    $useCoin = 20;
                    if($cmd == 'B')
                        $useCoin = 200;
                    $slotSettings->SetGameData($slotSettings->slotId . 'SupermeterCoin', $slotSettings->GetGameData($slotSettings->slotId . 'SupermeterCoin') - $useCoin);
                }
            }

            if($scattersCoinWin > 500)
            {
                //take mystery win and finish jokerizer mode                
                $eventData['stickyA'] = implode('', $stickyA);
                $eventData['stickyB'] = implode('', $stickyB);
                $eventData['stickyN'] = implode('', $stickyN);
                $this->gameState = 'Finished';
                
                $prizes = [[
                    'descr' => 'supermeter',
                    'gameId' => '7310',
                    'netamount' => number_format($slotSettings->GetGameData($slotSettings->slotId . 'SupermeterCoin') * $betLine, 2),
                    'prizedata' => null,
                    'prizeid' => 111,
                    'type' => 'FIXED',
                    'wonamount' => number_format($slotSettings->GetGameData($slotSettings->slotId . 'SupermeterCoin') * $betLine, 2),
                    'wonAspect' => 'CASH',
                    'woncurrency' => 'USD'
                ]];
            }
            else
            {
                if($postData['slotEvent'] != 'freespin')
                {
                    if($supermeter > 0 )
                    {
                        $nextCmds = ['LS','C'];
                        //toggle jokerize mode                
                        $slotSettings->SetGameData($slotSettings->slotId . 'Reels', $reels);
                        $this->gameState = 'Pending';
                    }
                }
                else if($postData['slotEvent'] == 'freespin')
                {                    
                    $this->gameState = 'Pending';
                    if($cmd == 'S' || $cmd == 'B')
                    {
                        $totalSupermeterCoin =  $slotSettings->GetGameData($slotSettings->slotId . 'SupermeterCoin');

                        if($scattersCount > 1 && $slotSettings->GetGameData($slotSettings->slotId . 'LastScatterCount' ) == 0 && rand(0, 100) < 10)
                        {
                            $eventData['stickyA'] = implode('', $stickyA);
                            $eventData['stickyB'] = implode('', $stickyB);
                            $eventData['stickyN'] = implode('', $stickyN);
                            $slotSettings->SetGameData($slotSettings->slotId . 'LastSticky', $stickyA);
                            $slotSettings->SetGameData($slotSettings->slotId . 'LastScatterCount', $scattersCount);
                            //if joker symbol is more than 2 in jokerizer mode, activate freespin                            
                            $needRespin = true;
                            $eventData['freeSpins'] = 1;
                            $eventData['freeSpinsAwarded'] = 1;
                            $this->isJokerizerFreespin = true;
                        }
                        else
                        {
                            if($scattersCount > 1)
                            {
                                $eventData['stickyA'] = implode('', $stickyA);
                                $eventData['stickyB'] = implode('', $stickyB);
                                $eventData['stickyN'] = implode('', $stickyN);
                            }
                            $slotSettings->SetGameData($slotSettings->slotId . 'LastScatterCount', 0);
                            $this->isJokerizerFreespin = false;                            
                            if($totalSupermeterCoin >= 20)
                                $nextCmds[] = 'S';
                            if($totalSupermeterCoin >= 200)
                                $nextCmds[] = 'B';
                            if($totalSupermeterCoin == 0)
                            {
                                $this->gameState = 'Finished';
                                $nextCmds = [];
                            }
                            else    
                                $nextCmds[] = 'C';
                        }                    
                    }
                }
            }
            

            $slotSettings->SetGameData($slotSettings->slotId . 'WTS', $wts);                                
            if(!empty($nextCmds))
                $eventData['nextCmds'] = implode(',', $nextCmds);

            if($postData['slotEvent'] == 'freespin' || $this->gameState == 'Finished')
                $eventData['supermeter'] = $slotSettings->GetGameData($slotSettings->slotId . 'SupermeterCoin');

            $betData = [
                'lines' => '1111111111',
                'coin' => $betLine,
                'nCoins' => 1,
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
                $bet['wonamount'] = number_format($slotSettings->GetGameData($slotSettings->slotId . 'SupermeterCoin') * $betLine, 2);
            $bets[] = $bet;
            $slotSettings->SetGameData($slotSettings->slotId . 'Step', $slotSettings->GetGameData($slotSettings->slotId . 'Step') + 1);
            return $needRespin;
        }
    }
}


