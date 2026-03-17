<?php 
namespace VanguardLTE\Games\WildMantraYGG
{
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;    

    class Server
    {
        public $debug = false;
        
        public $gameState;
        public $gameMode = 0; //0: general spin, 1: wild respin, 2: free spin
        public $reelStatus;
        public $wildCount;
        public $fixedReel;
        public $triggeringReel;
        public $freespinWildReel;
        public $fullStackReels = [];
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
                        
                        $response = file(base_path() . '/app/Games/WildMantraYGG/translation.txt')[0];                                                                          
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
                        $response = '{"code":0,"data":{"id":"","org":null,"gameid":0,"data":{"appsrv":"","file":"/init/","gameid":"7363","height":"600","license":"","org":"Demo","root":"","type":"Html","width":"800"}},"fn":"clientinfo","utcts":'.time().'}';
                        break;
                    case 'game':
                        $filename = base_path() . '/app/Games/WildMantraYGG/game.txt';
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
                            $slotSettings->SetBank($slotSettings->GetGameData($slotSettings->slotId . 'LastEvent'), -1 * $won);                                    
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
                                                    'nCoins' => 25,
                                                    'restoredAccumulatedWonCoin' => 0,
                                                    'variant' => null,
                                                    'lines' => '11111111111111111111'
                                                ],
                                                'eventdata' => ['exactWon' => $won],
                                                'prizes' => [
                                                    [
                                                        'descr' => 'Cash out',
                                                        'gameId' => '7363',
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

                        $this->gameMode = 0;
                        $betLine = 0;
                        if(isset($postData['coin']))
                            $betLine = $postData['coin'];
                        $allbet = $betLine * $lines;

                        //check balance
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
                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                        $this->fullStackReels = [];
                        $this->gameMode = 0; //general spin                
                        
                        $this->gameState = "Finished";
                        $bonusMpl = 1;
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
                        {
                            $slotSettings->SaveLogReport($response, $allbet, $reportWin, $postData['slotEvent']);        
                        }
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
            $slotEvent = 'bet';
            if($this->debug)
            {
                $winType = 'bonus';
            }
            for( $i = 0; $i <= 300; $i++ )
            {
                $totalWin = 0;
                $coinWin = 0;
                $lineWins = [];
                $symbolWin = array_fill(0, count($slotSettings->SymbolGame), 0);                
                
                $cWins = array_fill(0, 25, 0);
                $wild = [0];
                $scatter = 1; //scatter symbol
                
                $reels = $slotSettings->GetReelStrips($winType, $slotEvent);

                if($this->debug)
                {
                    //respin test                    
                    // $reels['rp'] = [5,4,5,5,5];
                    // $reels['reel1'] = [8,3,8,7,8];
                    // $reels['reel2'] = [0,3,9,8,9];
                    // $reels['reel3'] = [2,9,7,9,7];
                    // $reels['reel4'] = [6,5,7,8,9];
                    // $reels['reel5'] = [9,7,8,5,8];

                    //freespin test
                    // $reels['rp'] = [50,21,5,24,5];
                    // $reels['reel1'] = [1,8,7,8,3];
                    // $reels['reel2'] = [1,5,7,6,8];
                    // $reels['reel3'] = [2,9,7,9,7];
                    // $reels['reel4'] = [1,4,6,7,9];
                    // $reels['reel5'] = [9,7,8,5,8];
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
                                $emptyLine = [[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0]];
                                $mpl = 1;                                                        
                                $tmpWin = $slotSettings->Paytable['SYM_' . $csym][3] * $betLine * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $symbolWin[$csym] += ($tmpWin - $cWins[$k]);
                                    $cWins[$k] = $tmpWin;

                                    $emptyLine[0][$p0] = 1;
                                    $emptyLine[1][$p1] = 1;
                                    $emptyLine[2][$p2] = 1;
                                    $winline = [$k + 1, $slotSettings->Paytable['SYM_' . $csym][3], $this->getConvertedLine($emptyLine), $tmpWin]; //[lineId, coinWon, winSymPositions, cash]
                                }
                            }
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) ) 
                            {
                                $emptyLine = [[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0]];
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
                                    $winline = [$k + 1, $slotSettings->Paytable['SYM_' . $csym][4], $this->getConvertedLine($emptyLine), $tmpWin]; //[lineId, coinWon, winSymPositions, cash]                                                             
                                }
                            }
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) ) 
                            {
                                $mpl = 1;
                                $emptyLine = [[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0]];
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
                                    $winline = [$k + 1, $slotSettings->Paytable['SYM_' . $csym][5], $this->getConvertedLine($emptyLine), $tmpWin]; //[lineId, coinWon, winSymPositions, cash]                                                            
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

                 //calculate scatter symbols
                $scatterCount = 0;
                for($r = 0; $r < 5; $r++)
                {
                    for($c = 0; $c < 4; $c++)
                    {
                        if($reels['reel'.($r+1)][$c] == $scatter)
                        {
                            $scatterCount++;
                        }
                    }
                }
                if($scatterCount >= 3 && $winType != 'bonus')
                    continue;
                if($scatterCount == 3)
                    $coinWin = 50;
                else if($scatterCount == 4)
                    $coinWin = 500;
                else if($scatterCount ==5 )
                    $coinWin = 25000;

                $totalWin += $coinWin * $betLine;
                $totalWin += $gameWon;

                $spinAcquired = false;
                if($this->debug)
                {
                    $spinAcquired = true;
                    break;
                }

                if($totalWin <= $spinWinLimit && (($totalWin > 0 && $winType != 'none') || ($winType == 'bonus' && $scatterCount >= 3)))
                {
                    $spinAcquired = true;
                    // if($totalWin < 0.5 * $spinWinLimit && $winType != 'bonus')
                    //     $spinAcquired = false;
                    if($spinAcquired)
                        break;                                        
                }                                         
                else if( $winType == 'none' && $totalWin == $gameWon) 
                {
                    break;
                }
            }

            if(!$spinAcquired && $totalWin > $gameWon)
            {
                $reels = $slotSettings->GetNoWinSpin($slotEvent);
                $lineWins = [];
                $totalWin = $gameWon;
                $coinWin = 0;
                $scatterCount = 0;
            }

            $wildPositions = array_fill(0, 5, -1);            
            $this->reelStatus = array_fill(0, 5, 'SPINNING');
            $this->wildCount = 0;
            for($r = 0; $r < 5; $r++)
            {
                for($c = 0; $c < 4; $c++)
                {
                    if(in_array($reels['reel'.($r+1)][$c], $wild))
                    {
                        $wildPositions[$r * 4 + $c] = 1;
                        $this->wildCount++;
                        $this->reelStatus[$r] = 'LOCKED';
                        $this->fixedReel = $r;
                        $this->freespinWildReel = [
                            'rp' => $reels['rp'][$r],
                            'reel' => $reels['reel'.($r+1)]
                        ];
                    }
                }
            }
            $needRespin = false;
            $extraFreepsin = 0;
            $nudge = [];
            if($scatterCount > 2)
            {
                $this->gameMode = 2;
                if($this->wildCount == 0)
                {
                    //set next spin to have at least 1 wild reel
                    $this->fixedReel = rand(0, 4);
                    $this->reelStatus[$this->fixedReel] = 'LOCKED';
                    $this->freespinWildReel = $slotSettings->GetFreespinWildReel($this->fixedReel);
                    for($c = 0; $c < 4; $c++)
                        if($this->freespinWildReel['reel'][$c] == 0)
                            $this->wildCount++;
                    if($this->wildCount == 4)
                        $extraFreepsin = 1;
                    $direction = 'UP';
                    if($this->freespinWildReel['rp'] - 1 > 0)
                        $direction = 'DOWN';
                    $nudge = ['affectedReel' => $this->fixedReel, 'direction' => $direction, 'finalRpos' => $this->freespinWildReel['rp'] - 1];
                }
                $needRespin = true;
            }           

            if($this->wildCount > 0 && $this->gameMode == 0)
            {
                $this->gameMode = 1;
                $needRespin = true;
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
            $slotSettings->SetGameData($slotSettings->slotId . 'GameWon', $totalWin,2);
            //create bet info
            $first_bet = [
                'betAmount' => $allbet,
                'betCurrency' => 'USD',
                'betdata' => [
                    'cheat' => null,
                    'clientData' => null,
                    'coin' => $betLine,
                    'initialBalance' => $slotSettings->GetGameData($slotSettings->slotId . 'InitialBalance'),                    
                    'nCoins' => 25
                ],
                'eventdata' => [
                    'accC' => $slotSettings->GetGameData($slotSettings->slotId . 'GameCoinWon'), //this is accumlated coin win for whole game
                    'accWa' => number_format($slotSettings->GetGameData($slotSettings->slotId . 'GameWon'), 2), //this is accumulated cash win for whole game,
                    'bonusWon' => 0,
                    'finalBoard' => $slotSettings->GetReelSymbol($reels),
                    'reels' => $slotSettings->GetReelSymbol($reels),
                    'reelSet' => 'BaseGameReels',                    
                    'rpos' => [$reels['rp'][0] - 1, $reels['rp'][1] - 1, $reels['rp'][2] - 1, $reels['rp'][3] - 1, $reels['rp'][4] - 1],                    
                    'wonCoins' => $coinWin, //coin win for spin
                    'wonMoney' => number_format($coinWin * $betLine,2), //won money for spin
                    'wtw' => []
                ],
                'prepaid' => false,
                'prizes' => null,
                'status' => 'RESULTED',                
                'wonamount' => 0,
                'step' => $slotSettings->GetGameData($slotSettings->slotId . 'SpinStep')
            ];                        
            if($totalWin > $gameWon + 1e-5)
            {
                $this->gameState = 'Pending';                
                $first_bet['eventdata']['wtw'] = $lineWins;  
                if(!$needRespin)              
                    $first_bet['eventdata']['nextCmds'] = 'C';
            } 

            if($this->gameMode == 1)            
            {
                //wild respin
                $first_bet['eventdata']['finalReelsPositions'] = [-1, -1, -1, -1, -1];
                $first_bet['eventdata']['reelsStatus'] = $this->reelStatus;
                $first_bet['eventdata']['respinAwarded'] = true;
                $this->triggeringReel = -1;
            }
            else if($this->gameMode == 2)
            {
                $first_bet['eventdata']['finalReelsPositions'] = [-1, -1, -1, -1, -1];
                $first_bet['eventdata']['finalReelsPositions'][$this->fixedReel] = $this->freespinWildReel['rp'] - 1;
                $first_bet['eventdata']['reelsStatus'] = $this->reelStatus;
                $first_bet['eventdata']['freeSpinsAwarded'] = 10;
                $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 10 + $extraFreepsin);
                $first_bet['eventdata']['initialNudges'] = [];
                if(!empty($nudge))
                    $first_bet['eventdata']['initialNudges'][] = $nudge;
            }

            $bets[] = $first_bet;
            $slotSettings->SetGameData($slotSettings->slotId . 'SpinStep', $slotSettings->GetGameData($slotSettings->slotId . 'SpinStep') + 1);
            $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bet');
            
            while($needRespin)
            {
                $needRespin = $this->doSubSpin($totalWin, $bets, $reels, $slotSettings, $postData, $bonusMpl, $spinIndex);
            }            
        }
        
        function doSubSpin(&$lastWin, &$bets, &$lastReels, $slotSettings, $postData, $bonusMpl, $spinIndex)
        {
            $slotEvent = '';
            if($this->gameMode == 1)
            {
                $slotEvent = 'respin';
                $postData['slotEvent'] = 'freespin';
            }
            else if($this->gameMode == 2)
            {
                $slotEvent = 'freespin';    
                $postData['slotEvent'] = 'freespin';
            }
            $linesId = $slotSettings->GetPaylines();
            $lines = count($linesId);
            $betLine = $postData['coin'];
            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $lines);
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];

            $allbet = $betLine * count($slotSettings->GetPaylines());
            $gameWon = $slotSettings->GetGameData($slotSettings->slotId . 'GameWon');
            
            $origianlReels = [];
            $tempReelStatus = $this->reelStatus;
            $spinAcquired = false;

            $minOriginalReel = [];
            $minReels = [];
            $minWin = -1;
            $minNudges = null;
            $minLineWins = [];
            $minTriggeringReel = -1;
            $minReelStatus = [];

            for( $i = 0; $i <= 300; $i++ )
            {
                $this->reelStatus = $tempReelStatus;
                $nudges = [];
                $totalWin = 0;
                $coinWin = 0;
                $lineWins = [];
                $triggeringReel = -1;
                $symbolWin = array_fill(0, count($slotSettings->SymbolGame), 0);
                
                $cWins = array_fill(0, 25, 0);
                $wild = [0]; //there is no wild symbol in this game                            
                $scatter = 1; //scatter symbol
                
                $reels = $slotSettings->GetReelStrips($winType, $slotEvent);

                if($this->debug && $slotSettings->GetGameData($slotSettings->slotId . 'SpinStep') < 4)
                {
                    //wild respin test
                    $reels['rp'] = [5,4,4,5,5];
                    $reels['reel1'] = [8,3,8,7,8];
                    $reels['reel2'] = [0,3,9,8,9];
                    $reels['reel3'] = [0,2,9,7,9];
                    $reels['reel4'] = [6,5,7,8,9];
                    $reels['reel5'] = [9,7,8,5,8];
                }
                $wilds = 0;

                //keep fixed reels
                for($r = 0; $r < 5; $r++)
                {
                    if($this->reelStatus[$r] == 'LOCKED')
                    {
                        $reels['reel'.($r+1)] = $lastReels['reel'.($r+1)];
                        $reels['rp'][$r] = $lastReels['rp'][$r];
                    }                        
                }

                if($this->gameMode == 2 && !empty($this->freespinWildReel))
                {
                    //in free spin, freespinWildReel is not empty when there was no locked reel, this will be called only once after main spin
                    $reels['reel'.($this->fixedReel+1)] = $this->freespinWildReel['reel'];
                    $reels['rp'][$this->fixedReel] = $this->freespinWildReel['rp'];
                    $this->freespinWildReel = [];
                }

                $origianlReels = $reels;   
                for($r = 0; $r < 5; $r++)                 
                    for($c = 0; $c < 4; $c++)
                    {
                        if($reels['reel'.($r+1)][$c] == 0)
                        {
                            $wilds++;
                            $this->reelStatus[$r] = 'LOCKED';
                            if($r != $this->fixedReel)
                                $triggeringReel = $r;
                        }
                    }
                if($wilds > $this->wildCount && $this->triggeringReel != $triggeringReel)
                {
                    //nudge creation
                    $nudge = [
                        'triggeringReel' => $triggeringReel,
                        'effects' => []
                    ];
                    $this->wildCount = $wilds;
                    //new wild symbol appeared, generate nudge
                    $wildIndex = -1;
                    $symIndex = -1;
                    
                    //check if fixed reels wild is not fully stacked
                    for($c = 0; $c < 4; $c++)
                    {
                        if($reels['reel'.($this->fixedReel+1)][$c] == 0)
                            $wildIndex = $c;
                        else 
                            $symIndex = $c;
                        if($symIndex != -1)
                            break;
                    }
                    
                    if($symIndex == -1) //reel is fully stacked with wild
                    {
                        for($r = 0; $r < 5; $r++)
                        {
                            if($this->reelStatus[$r] == 'LOCKED' && $r != $this->fixedReel && in_array($r, $this->fullStackReels))
                                $this->fixedReel = $r;
                        }
                    }
                    else
                    {
                        if($symIndex < $wildIndex)
                        {
                            //this case cannot be existed
                            $reels['rp'][$this->fixedReel] += 1;
                            $nudge['effects'][] = [
                                'affectedReel' => $this->fixedReel,
                                'direction' => 'UP',
                                'fianlRPos' => $reels['rp'][$this->fixedReel],
                            ];
                            $reels['reel'.($this->fixedReel+1)][$wildIndex-1] = 0;                            
                        }
                        else 
                        {
                            $reels['rp'][$this->fixedReel] -= 1;
                            $nudge['effects'][] = [
                                'affectedReel' => $this->fixedReel,
                                'direction' => 'DOWN',
                                'fianlRPos' => $reels['rp'][$this->fixedReel],
                            ];
                            // $reels['reel'.($this->fixedReel+1)][$wildIndex+1] = 0;
                            for($c = 3; $c > 0; $c--)
                                $reels['reel'.($this->fixedReel+1)][$c] = $reels['reel'.($this->fixedReel+1)][$c - 1];
                            $reels['reel'.($this->fixedReel+1)][0] = 0;
                        }
                        $this->wildCount++;
                        $nudges[] = $nudge;
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
                                $emptyLine = [[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0]];
                                $mpl = 1;                                                        
                                $tmpWin = $slotSettings->Paytable['SYM_' . $csym][3] * $betLine * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $symbolWin[$csym] += ($tmpWin - $cWins[$k]);
                                    $cWins[$k] = $tmpWin;

                                    $emptyLine[0][$p0] = 1;
                                    $emptyLine[1][$p1] = 1;
                                    $emptyLine[2][$p2] = 1;
                                    $winline = [$k + 1, $slotSettings->Paytable['SYM_' . $csym][3], $this->getConvertedLine($emptyLine), $tmpWin]; //[lineId, coinWon, winSymPositions, cash]
                                }
                            }
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) ) 
                            {
                                $emptyLine = [[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0]];
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
                                    $winline = [$k + 1, $slotSettings->Paytable['SYM_' . $csym][4], $this->getConvertedLine($emptyLine), $tmpWin]; //[lineId, coinWon, winSymPositions, cash]                                                             
                                }
                            }
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) ) 
                            {
                                $mpl = 1;
                                $emptyLine = [[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0]];
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
                                    $winline = [$k + 1, $slotSettings->Paytable['SYM_' . $csym][5], $this->getConvertedLine($emptyLine), $tmpWin]; //[lineId, coinWon, winSymPositions, cash]                                                            
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

                if($minWin == -1 || $minWin > $totalWin)
                {
                    $minOriginalReel = $origianlReels;
                    $minReels = $reels;
                    $minWin = $totalWin;
                    $minNudges = $nudges;
                    $minLineWins = $lineWins;
                    $minTriggeringReel = $triggeringReel;
                    $minReelStatus = $this->reelStatus;
                }

                if($this->debug)
                {
                    $spinAcquired = true;
                    break;
                }

                if($totalWin <= $spinWinLimit && $winType != 'none')
                {
                    $spinAcquired = true;
                    break;
                }                                    
                else if( $winType == 'none' ) 
                {
                    break;
                }
            }

            if($totalWin > $gameWon && !$spinAcquired)
            {
                $origianlReels = $minOriginalReel;
                $reels = $minReels;
                $totalWin = $minWin;
                $nudges = $minNudges;
                $lineWins = $minLineWins;
                $triggeringReel = $minTriggeringReel;
                $this->reelStatus = $minReelStatus;
            }

            for($r = 1; $r <= 5; $r++)
            {
                $wildCnt = 0;
                for($c = 0; $c < 4; $c++)
                {
                    if($reels['reel'.$r][$c] == 0)
                        $wildCnt++;
                }
                if($wildCnt == 4)
                    $this->fullStackReels[] = $r - 1;
            }

            if($totalWin > $lastWin + 1e-5)
            {
                //able to do more spin                
                $lastWin = $totalWin;
            }
            $needRespin = false;
            if($this->gameMode == 1)
            {
                //wild respin mode
                if($triggeringReel != $this->triggeringReel)
                {
                    $needRespin = true;
                    $this->triggeringReel = $triggeringReel;
                }
            }
            else
            {
                $freeGames = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                if($slotSettings->GetGameData($slotSettings->slotId . 'SpinStep') <= $freeGames)   
                    $needRespin = true;
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
            $totalWin = $slotSettings->GetGameData($slotSettings->slotId . 'GameCoinWon') * $betLine;
            $slotSettings->SetGameData($slotSettings->slotId . 'GameWon', $totalWin);
            $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bonus');
                        
            $bet = [
                'betAmount' => 0,
                'betCurrency' => 'USD',
                'betdata' => [
                    'cheat' => null,
                    'clientData' => null,
                    'coin' => $betLine,
                    'initialBalance' => $slotSettings->GetGameData($slotSettings->slotId . 'InitialBalance'),                    
                    'nCoins' => 25
                ],
                'eventdata' => [
                    'accC' =>  $slotSettings->GetGameData($slotSettings->slotId . 'GameCoinWon'),
                    'accWa' =>  $slotSettings->GetGameData($slotSettings->slotId . 'GameWon'),                             
                    'finalBoard' => $slotSettings->GetReelSymbol($reels),  
                    'reels' => $slotSettings->GetReelSymbol($origianlReels),                    
                    'finalReelsPositions' => [$reels['rp'][0] - 1, $reels['rp'][1] - 1, $reels['rp'][2] - 1, $reels['rp'][3] - 1, $reels['rp'][4] - 1],
                    'rpos' => [$reels['rp'][0] - 1, $reels['rp'][1] - 1, $reels['rp'][2] - 1, $reels['rp'][3] - 1, $reels['rp'][4] - 1],
                    'wonCoins' => $coinWin,
                    'wonMoney' => number_format($coinWin * $betLine, 2)
                ],
                'prepaid' => false,
                'prizes' => null,
                'status' => 'RESULTED',
                'step' => $slotSettings->GetGameData($slotSettings->slotId . 'SpinStep'),
                'wonamount' => 0                
            ];            

            if($this->gameMode == 0)
                $bet['eventdata']['reelSet'] = 'BaseGameReels';
            else if($this->gameMode == 1)
            {
                $bet['eventdata']['reelSet'] = 'RespinReels';
                $bet['eventdata']['reelsStatus'] = $this->reelStatus;
            }
            else if($this->gameMode == 2)
            {
                $bet['eventdata']['reelSet'] = 'FreeSpinsReels';
                $bet['eventdata']['reelsStatus'] = $this->reelStatus;
            }

            $lastReels = $reels;
            if($needRespin)
            {   
                if($this->gameMode == 1)             
                {
                    $bet['eventdata']['nudges'] = $nudges;
                    $bet['eventdata']['respinsAwarded'] = true;
                }
                else
                {
                    if(count($nudges) > 0)  
                        $bet['eventdata']['nudges'] = $nudges;
                }
                $bet['eventdata']['wtw'] = $lineWins;
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

        
           
    }
}


