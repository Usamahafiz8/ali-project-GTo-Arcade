<?php 
namespace VanguardLTE\Games\BeautyAndBeastYGG
{
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;    

    class Server
    {
        public $debug = false;
        
        public $gameState;
        /**
         * game mode
         * 0: general spin non coin mode
         * 1: general spin boost mode
         * 2: general spin nudge mode
         * 3: general spin nudge & boost mode
         * 4: freespin chef; TripReel
         * 5: freespin butler; Nudge
         * 6: freespin maid; Wild Seed
         */
        public $gameMode = 0;
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
                        $response = file(base_path() . '/app/Games/BeautyAndBeastYGG/translation.txt')[0];
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
                        $response = '{"code":0,"data":{"id":"","org":null,"gameid":0,"data":{"appsrv":"","file":"/init/","gameid":"7333","height":"600","license":"","org":"Demo","root":"","type":"Html","width":"800"}},"fn":"clientinfo","utcts":'.time().'}';
                        break;
                    case 'game':
                        $filename = base_path() . '/app/Games/BeautyAndBeastYGG/game.txt';
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
                        $bets = [];
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
                        else if($cmd == 'Nudge' || $cmd == 'TripReel' || $cmd == 'WildSeed')
                        {
                            $postData['slotEvent'] = 'freespin';
                            if($cmd == 'TripReel')
                                $this->gameMode = 4;
                            else if($cmd == 'Nudge')
                                $this->gameMode = 5;
                            else if($cmd == 'WildSeed')
                                $this->gameMode = 6;
                            $wagerid = $postData['wagerid'];
                            $totalWin = 0;
                            $linesId = $slotSettings->GetPaylines();
                            $lines = count($linesId);
                            $betLine = $slotSettings->GetGameData($slotSettings->slotId . 'BetLine');
                            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $lines);
                            $spinCount = 9;
                            $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bonus');
                            while($spinCount > 0)
                            {
                                $spinCount--;
                                if($cmd == 'Nudge')
                                    $this->doNudgeSubSpin($totalWin, $bets, $slotSettings, $spinCount);
                                else
                                    $this->doSubSpin($totalWin, $bets, $slotSettings, $postData, 1, $spinCount);
                            }

                            $this->gameState = "Pending";
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
                            break;
                        }
                        
                        $allbet = $postData['amount'];
                        
                        if($allbet > $slotSettings->GetBalance())
                        {
                            return '{"completion":"Unknown","code":1006,"errorCode":"NO_SUFFICIENT_FUNDS","type":"O","rid":"220215083220::e14db45d-39e6-4cee-a076-ebb72ca0a89b","msg":"You do not have sufficient funds for the bet","fn":null,"details":null,"relaunchUrl":null,"timeElapsed":null,"errorType":null,"balanceDifference":null,"suppressed":[]}
                            ';
                        }
                        $slotSettings->SetGameData($slotSettings->slotId . 'BetAmount', $allbet);
                        $slotSettings->UpdateJackpots($allbet);
                        $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bet');
                        $slotSettings->SetGameData($slotSettings->slotId . 'InitialBalance', $slotSettings->GetBalance());
                        $slotSettings->SetBalance(-1 * $allbet, $postData['slotEvent']);
                        $bankSum = $allbet / 100 * $slotSettings->GetPercent();
                        $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $bankSum, $postData['slotEvent']);
                        $slotSettings->SetBet($allbet);
                        
                        $slotSettings->SetGameData($slotSettings->slotId . 'SpinStep', 1);
                        $slotSettings->SetGameData($slotSettings->slotId . 'GameWon', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'GameCoinWon', 0);

                        $this->gameMode = 0; //general spin
                        
                        $this->gameState = "Finished";
                        $bonusMpl = 1;
                        $win = 0;
                        $spinRand = rand(1, 200);

                        $betLine = 0;
                        if(isset($postData['coin']))
                        {
                            $betLine = $postData['coin'];
                            $slotSettings->SetGameData($slotSettings->slotId . 'Coin', $betLine);
                        }
                        if($spinRand == 1)
                        {
                            $this->gameMode = 4;
                            $this->doSubSpin($win, $bets, $slotSettings, $postData, 1, 0);
                            unset($bets[0]['eventdata']['freeSpins']);
                        }
                        else if($spinRand == 2)
                        {
                            $this->gameMode = 6;
                            $this->doSubSpin($win, $bets, $slotSettings, $postData, 1, 0);
                            unset($bets[0]['eventdata']['freeSpins']);
                        }
                        else if($spinRand == 3)
                        {
                            $this->gameMode = 5;
                            $this->doNudgeSubSpin($win, $bets, $slotSettings, 0);
                            unset($bets[0]['eventdata']['freeSpins']);
                        }
                        else
                            $this->doSpin($slotSettings, $postData, $bets, $bonusMpl);
                        
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

        function doSpin($slotSettings, $postData, &$bets, $bonusMpl)
        {
            $linesId = $slotSettings->GetPaylines();
            $lines = count($linesId);
            $betLine = 0;
            if(isset($postData['coin']))
                $betLine = $postData['coin'];
            $amount = $postData['amount'];
            $slotEvent = 'bet';
            if($betLine * $lines == $amount)
            {
                $this->gameMode = 0;
                $slotEvent = 'bet';
            }
            if($betLine * ($lines + 5) == $amount)
            {
                $slotEvent = 'boost';
                $this->gameMode = 1;
            }
            if($betLine * ($lines + 15) == $amount)
            {
                $slotEvent = 'nudge';
                $this->gameMode = 2;
            }
            if($betLine * ($lines + 30) == $amount)
            {
                $slotEvent = 'boost_nudge';
                $this->gameMode = 3;
            }

            $allbet = $amount;
            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $lines);
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
            $symbolString = $slotSettings->SymbolString;
            $gameWon = $slotSettings->GetGameData($slotSettings->slotId . 'GameWon');            

            for( $i = 0; $i <= 500; $i++ )
            {
                $totalWin = 0;
                $coinWin = 0;
                $lineWins = [];
                $symbolWin = array_fill(0, count($slotSettings->SymbolGame), 0);                              
                
                $cWins = array_fill(0, 20, 0);
                $wild = [1]; //there is no wild symbol in this game                            
                $scatter = 0; //scatter symbol
                
                $reels = $slotSettings->GetReelStrips($winType, $slotEvent);

                if($this->debug)
                {
                    //freespin test
                    $reels['rp'] = [5,5,23,5,4];
                    $reels['reel1'] = [9,1,6,8,5];
                    $reels['reel2'] = [0,6,9,7,1];
                    $reels['reel3'] = [0,3,6,4,4];
                    $reels['reel4'] = [3,8,6,1,4];
                    $reels['reel5'] = [0,6,5,9,1];
                    $winType = 'bonus';
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
                                                                        
                            if(($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild))) 
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
                                    $winline = [$k + 1, $slotSettings->Paytable['SYM_' . $csym][3], $this->getConvertedLine($emptyLine), $tmpWin]; //[lineId, coinWon, winSymPositions, cash]
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
                                    $winline = [$k + 1, $slotSettings->Paytable['SYM_' . $csym][4], $this->getConvertedLine($emptyLine), $tmpWin]; //[lineId, coinWon, winSymPositions, cash]                                                             
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
                $scatterCount = 0;
                
                for($r = 0; $r < 5; $r++)
                {
                    for($c = 0; $c < 3; $c++)
                    {
                        if($reels['reel'.($r+1)][$c] == $scatter)
                        {
                            $scatterCount++;                            
                        }
                    }
                }
                $freespinAwarded = false;
                if($scatterCount > 2 || ($scatterCount > 1 && ($slotEvent == 'boost' || $slotEvent == 'boost_nudge')))
                {
                    $freespinAwarded = true;
                }
                if($freespinAwarded && $winType != 'bonus')
                    continue;
                
                if($scatterCount == 4)
                    $coinWin = 1000;
                else if($scatterCount == 5)
                    $coinWin = 5000;
                $totalWin += $coinWin * $betLine;

                $spinAcquired = false;
                if($this->debug)
                {
                    $spinAcquired = true;
                    break;
                }

                if($totalWin <= $spinWinLimit && (($totalWin > 0 && $winType != 'none') || ($winType == 'bonus' && $freespinAwarded)))
                {
                    $spinAcquired = true;
                    if($totalWin < 0.5 * $spinWinLimit && $winType != 'bonus')
                        $spinAcquired = false;
                    if($spinAcquired)
                        break;                                        
                }                                    
                else if( $winType == 'none' && $totalWin == $gameWon) 
                {
                    break;
                }        
            }

            if(!$spinAcquired && $totalWin > 0 || ($freespinAwarded && $winType != 'bonus' ))
            {
                $reels = $slotSettings->GetNoWinSpin($slotEvent);
                $lineWins = [];
                $totalWin = $gameWon;
                $coinWin = 0;          
                $scatterCount = 0;  
            }

            //calculate scatter symbols
            $scatterCount = 0;
            $activeLines = array_fill(0, $lines, 0);
            $activeSymbols = array_fill(0, 15, 0);
            for($r = 0; $r < 5; $r++)
            {
                for($c = 0; $c < 3; $c++)
                {
                    if($reels['reel'.($r+1)][$c] == $scatter)
                    {
                        $scatterCount++;
                        $activeSymbols[$r * 3 + $c] = 1;
                    }
                }
            }

            $freespinAwarded = false;
            if($scatterCount > 2 || ($scatterCount > 1 && ($slotEvent == 'boost' || $slotEvent == 'boost_nudge')))
            {
                $freespinAwarded = true;
            }
            
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
            $slotSettings->SetGameData($slotSettings->slotId . 'GameWon', number_format($totalWin,2));
            //create bet info
            $first_bet = [
                'betAmount' => $allbet,
                'betCurrency' => 'USD',
                'betdata' => [
                    'cheat' => null,
                    'clientData' => null,
                    'coin' => $betLine,
                    'initialBalance' => $slotSettings->GetGameData($slotSettings->slotId . 'InitialBalance'),                    
                    'nCoins' => 1
                ],
                'eventdata' => [
                    'accC' => $slotSettings->GetGameData($slotSettings->slotId . 'GameCoinWon'), //this is accumlated coin win for whole game
                    'accWa' => $slotSettings->GetGameData($slotSettings->slotId . 'GameWon'), //this is accumulated cash win for whole game,
                    'bonusWon' => 0,
                    'reels' => $slotSettings->GetReelSymbol($reels),
                    'reelSet' => 'Reels',
                    'rpos' => [$reels['rp'][0] - 1, $reels['rp'][1] - 1, $reels['rp'][2] - 1, $reels['rp'][3] - 1, $reels['rp'][4] - 1],
                    'wonCoins' => $coinWin, //coin win for spin
                    'wtw' => []
                ],
                'prepaid' => false,
                'prizes' => null,
                'status' => 'RESULTED',
                'wonamount' => 0
            ];

            if($this->gameMode == 1)
                $first_bet['eventdata']['reelSet'] = 'ReelsSuperbet1';
            else if($this->gameMode == 2)
                $first_bet['eventdata']['reelSet'] = 'ReelsSuperbet2';
            else if($this->gameMode == 3)
                $first_bet['eventdata']['reelSet'] = 'ReelsSuperbet3';

            $first_bet['step'] = $slotSettings->GetGameData($slotSettings->slotId . 'SpinStep');
            if($totalWin > $gameWon + 1e-5)
            {
                $this->gameState = 'Pending';
                $first_bet['eventdata']['wtw'] = $lineWins;
                $first_bet['eventdata']['nextCmds'] = 'C';
            }

            if($freespinAwarded)
            {
                $this->gameState = 'Pending';
                $first_bet['eventdata']['nextCmds'] = 'TripReel,WildSeed,Nudge';
                $first_bet['eventdata']['freeSpinsAwarded'] = 10;
                $wts = [implode('', $activeLines), implode('', $activeSymbols)];
                $first_bet['eventdata']['wts'] = $wts;
                $first_bet['eventdata']['wtw'] = $lineWins;
                $slotSettings->SetGameData($slotSettings->slotId . 'Coin', $betLine);
            }

            $bets[] = $first_bet;
            $slotSettings->SetGameData($slotSettings->slotId . 'SpinStep', $slotSettings->GetGameData($slotSettings->slotId . 'SpinStep') + 1);            
        }
        
        function doSubSpin(&$lastWin, &$bets, $slotSettings, $postData, $bonusMpl, $spinIndex)
        {
            $linesId = $slotSettings->GetPaylines();
            $lines = count($linesId);       
            $betLine = $slotSettings->GetGameData($slotSettings->slotId . 'Coin');
            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $lines);
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
            // $spinWinLimit = $slotSettings->GetBank('bonus') + $slotSettings->GetBank('');
            $allbet = $betLine * count($slotSettings->GetPaylines());
            $gameWon = $slotSettings->GetGameData($slotSettings->slotId . 'GameWon');
            $slotEvent = '';
            if($this->gameMode == 4)
                $slotEvent = 'freespin_chef';
            else if($this->gameMode == 5)
                $slotEvent = 'freespin_butler';
            else if($this->gameMode == 6)
                $slotEvent = 'freespin_maid';            
                 
            $originalReels = [];
            $minReels = [];
            $minTotalWin = -1;
            $minLineWins = [];
            $minWildSeeds = [];
            $minOriginalReels = [];
            for( $i = 0; $i <= 500; $i++ )
            {
                $nudges = [];
                $wildSeed = [];
                $totalWin = 0;
                $coinWin = 0;
                $lineWins = [];
                
                // if($postData['slotEvent'] == 'freespin')
                // {
                //     $cBank = $allbet * 20 - $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin');
                //     $spinsLeft = $spinIndex;
                //     if($spinsLeft == 0)
                //         $spinsLeft = 1;
                //     $spinWinLimit = $cBank / $spinsLeft;
                // }
                $symbolWin = array_fill(0, count($slotSettings->SymbolGame), 0);
                
                $cWins = array_fill(0, 20, 0);
                $wild = [1];      
                $scatter = 0; //scatter symbol
                $reels = $slotSettings->GetReelStrips($winType, $slotEvent);
                $originalReels = $reels;
                if($this->gameMode == 4)
                {
                    //trip reel, chef
                    $reels['reel3'] = $reels['reel2'];
                    $reels['reel4'] = $reels['reel2'];
                    $reels['rp'][2] = $reels['rp'][1];
                    $reels['rp'][3] = $reels['rp'][1];                    
                }
                else if($this->gameMode == 5)
                {
                    //nudge, butler

                }
                else if($this->gameMode == 6)
                {
                    //wild seed, maid
                    $wilds = [];
                    for($r = 0; $r < 5; $r++)
                        for($c = 0; $c < 3; $c++)
                        {
                            if($reels['reel'.($r+1)][$c] == 1)
                                $wilds[] = $r * 3 + $c;
                        }
                    //add 2 more wilds
                    $seedCount = rand(2,3);
                    while($seedCount > 0)
                    {
                        $seedPosition = rand(0, 14);
                        if(!in_array($seedPosition, $wilds))
                        {
                            $r = intval($seedPosition / 3);
                            $c = $seedPosition % 3;
                            $reels['reel'.($r+1)][$c] = 1;
                            $seedCount--;
                            $wildSeed[] = $seedPosition;
                        }                        
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
                                    $winline = [$k + 1, $slotSettings->Paytable['SYM_' . $csym][3], $this->getConvertedLine($emptyLine), $tmpWin]; //[lineId, coinWon, winSymPositions, cash]
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
                                    $winline = [$k + 1, $slotSettings->Paytable['SYM_' . $csym][4], $this->getConvertedLine($emptyLine), $tmpWin]; //[lineId, coinWon, winSymPositions, cash]                                                             
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

                if($minTotalWin == -1 || $minTotalWin > $totalWin)
                {
                    $minTotalWin = $totalWin;
                    $minReels = $reels;
                    $minLineWins = $lineWins;
                    if($this->gameMode == 6)
                        $minWildSeeds = $wildSeed;
                    $minOriginalReels = $originalReels;
                }

                $spinAcquired = false;
                if($this->debug)
                {
                    $spinAcquired = true;
                    break;
                }

                if($totalWin > 0 && $totalWin <= $spinWinLimit && $winType != 'none')
                {
                    $spinAcquired = true;
                    if($totalWin < 0.4 * $spinWinLimit && $winType != 'bonus')
                        $spinAcquired = false;
                    if($spinAcquired)
                        break;                        
                }                                    
                else if( $winType == 'none' ) 
                {
                    break;
                }
            }

            if(!$spinAcquired && $totalWin > $gameWon)
            {
                $totalWin = $minTotalWin;
                $lineWins = $minLineWins;
                $reels = $minReels;
                if($this->gameMode == 6)
                {
                    //wild seed, maid
                    $wildSeed = $minWildSeeds;
                }
                $originalReels = $minOriginalReels;                
            }
            
            if($totalWin > $lastWin + 1e-5)
            {
                //able to do more spin                
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
            
            $bet = [
                'betAmount' => 0,
                'betCurrency' => 'USD',
                'betdata' => [
                    'cheat' => null,
                    'clientData' => null,
                    'coin' => $betLine,
                    'initialBalance' => $slotSettings->GetGameData($slotSettings->slotId . 'InitialBalance'),                    
                    'nCoins' => 1
                ],
                'eventdata' => [
                    'accC' =>  $slotSettings->GetGameData($slotSettings->slotId . 'GameCoinWon'),
                    'accWa' =>  $slotSettings->GetGameData($slotSettings->slotId . 'GameWon'),                             
                    'reels' => $slotSettings->GetReelSymbol($reels),  
                    'rpos' => [$reels['rp'][0] - 1, $reels['rp'][1] - 1, $reels['rp'][2] - 1, $reels['rp'][3] - 1, $reels['rp'][4] - 1],
                    'wonCoins' => $coinWin,
                    'wonMoney' => number_format($coinWin * $betLine, 2),
                    'freeSpins' => $spinIndex
                ],
                'prepaid' => false,
                'prizes' => null,
                'status' => 'RESULTED',
                'step' => $slotSettings->GetGameData($slotSettings->slotId . 'SpinStep'),
                'wonamount' => 0
            ];            

            if($this->gameMode == 4)
            {
                //trip reel
                $bet['eventdata']['reelSet'] = 'FeatureReels';
                $bet['eventdata']['activeGods'] = 1;
                $bet['eventdata']['tripReel'] = $reels['rp'][1];
            }
            else if($this->gameMode == 5)
            {
                $bet['eventdata']['reelSet'] = 'FeatureReelsC';
                $bet['eventdata']['activeGods'] = 4;
            }
            else if($this->gameMode == 6)
            {
                //wild seed
                $bet['eventdata']['reelSet'] = 'FeatureReelsB';
                $bet['eventdata']['activeGods'] = 2;
                $bet['eventdata']['reels0'] = $slotSettings->GetReelSymbol($originalReels);
                $bet['eventdata']['wildSeed'] = $wildSeed;
            }
            
            $wts = [implode('', $activeLines), implode('', $activeSymbols)];
            $bet['eventdata']['wts'] = $wts;
            $bet['eventdata']['wtw'] = $lineWins;            
            $slotSettings->SetGameData($slotSettings->slotId . 'Coin', $betLine);
            $bet['eventdata']['accWa'] = $slotSettings->GetGameData($slotSettings->slotId . 'GameWon');
            $bet['eventdata']['doubleAmount'] = $slotSettings->GetGameData($slotSettings->slotId . 'GameWon');
            $bet['eventdata']['doubleCount'] = 1;
            
            if($spinIndex == 0 && $totalWin > 0)
            {
                $bet['eventdata']['nextCmds'] = 'C';
            }

            $bets[] = $bet;            
            $slotSettings->SetGameData($slotSettings->slotId . 'SpinStep', $slotSettings->GetGameData($slotSettings->slotId . 'SpinStep') + 1);            
        }

        function checkWinline(&$reels, $lines, $slotSettings, $betLine, &$lineWins, &$totalWin, $winTypeTmp)
        {
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
            $linesId = $slotSettings->GetPaylines();
            $wild = [1];
            $bonusMpl = 1;
            $cWins = array_fill(0, 20, 0);
            $spinAcquired = false;
            
            $maxWin = 0;
            $maxLineWins = [];
            $maxReels = [];
            for($i = 0; $i < 300; $i++)
            {
                $win = 0;
                $reels = $slotSettings->GetReelStrips($winType, 'freespin_butler');
                $_lineWins = [];
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
                                    $cWins[$k] = $tmpWin;

                                    $emptyLine[0][$p0] = 1;
                                    $emptyLine[1][$p1] = 1;
                                    $emptyLine[2][$p2] = 1;
                                    $winline = [$k + 1, $slotSettings->Paytable['SYM_' . $csym][3], $this->getConvertedLine($emptyLine), $tmpWin]; //[lineId, coinWon, winSymPositions, cash]
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
                                    $winline = [$k + 1, $slotSettings->Paytable['SYM_' . $csym][4], $this->getConvertedLine($emptyLine), $tmpWin]; //[lineId, coinWon, winSymPositions, cash]                                                             
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
                                    $winline = [$k + 1, $slotSettings->Paytable['SYM_' . $csym][5], $this->getConvertedLine($emptyLine), $tmpWin]; //[lineId, coinWon, winSymPositions, cash]                                                            
                                }
                            }                                        
                        }
                    }
                    if( $cWins[$k] > 0 && !empty($winline))
                    {
                        array_push($_lineWins, $winline);            
                        $win += $cWins[$k];        
                    }
                }
                if($win > $maxWin)
                {
                    $maxWin = $win;
                    $maxLineWins = $_lineWins;
                    $maxReels = $reels;
                }
                if($win >= $spinWinLimit * 0.5)
                {
                    $lineWins = $_lineWins;
                    $totalWin += $win;
                    $spinAcquired = true;
                    break;
                }
            }

            if(!$spinAcquired)
            {
                $totalWin = $maxWin;
                $lineWins = $maxLineWins;
                $reels = $maxReels;
            }   
        }

        function doNudgeSubSpin(&$lastWin, &$bets, $slotSettings, $spinIndex)
        {
            $linesId = $slotSettings->GetPaylines();
            $lines = count($linesId);            
            $betLine = $slotSettings->GetGameData($slotSettings->slotId . 'Coin');
            $winTypeTmp = $slotSettings->GetSpinSettings('freespin', $betLine, $lines);

            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
            $gameWon = $slotSettings->GetGameData($slotSettings->slotId . 'GameWon');

            $slotEvent = 'freespin_butler';
            $originalReels = [];                        
            $totalWin = 0;
            
            $lineWins = [];                       
            $reels = [];         
            // $this->checkWinline($reels, $lines, $slotSettings, $betLine, $lineWins, $totalWin, $winTypeTmp);            
            $minLineWins = [];
            $minReels = [];
            $minTotalWin = -1;

            $wild = [1];
            $bonusMpl = 1;

            $spinAcquired = false;
            for($i = 0; $i < 300; $i++)
            {
                $totalWin = 0;
                $reels = $slotSettings->GetReelStrips($winType, $slotEvent);                
                $cWins = array_fill(0, 20, 0);
                $lineWins = [];
                $spinAcquired = false;
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
                                    $cWins[$k] = $tmpWin;

                                    $emptyLine[0][$p0] = 1;
                                    $emptyLine[1][$p1] = 1;
                                    $emptyLine[2][$p2] = 1;
                                    $winline = [$k + 1, $slotSettings->Paytable['SYM_' . $csym][3], $this->getConvertedLine($emptyLine), $tmpWin]; //[lineId, coinWon, winSymPositions, cash]
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
                                    $winline = [$k + 1, $slotSettings->Paytable['SYM_' . $csym][4], $this->getConvertedLine($emptyLine), $tmpWin]; //[lineId, coinWon, winSymPositions, cash]                                                             
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

                if($minTotalWin == -1 || $minTotalWin > $totalWin)
                {
                    $minTotalWin = $totalWin;
                    $minReels = $reels;
                    $minLineWins = $lineWins;
                }

                if($totalWin <= $spinWinLimit && ($totalWin > 0 && $winType != 'none'))
                {
                    $spinAcquired = true;
                    if($totalWin < 0.5 * $spinWinLimit && $winType != 'bonus')
                        $spinAcquired = false;
                    if($spinAcquired)
                        break;
                }
            }

            if(!$spinAcquired)
            {
                $totalWin = $minTotalWin;
                $reels = $minReels;
                $lineWins = $minLineWins;
            }

            $nudge = 0;
            $nudgeReel = -1;
            $originalReels = $reels;
            if($totalWin > $gameWon)
            {
                //suppose current reel as nudged reel
                if(rand(0, 100) < 50)
                    $nudge = -1;
                else
                    $nudge = 1;
                $nudgeReel = rand(0, 2);
                $originalReels['reel'.($nudgeReel+1)] = $slotSettings->GetNudgedReel($nudgeReel, $reels['rp'][$nudgeReel], -$nudge);
                $originalReels['rp'][$nudgeReel] -= $nudge;
            }

           
            // if($totalWin == 0 && $winType == 'win')
            // {
            //     //try with up nudge
            //     $r = 0;
            //     $lineWins = [];
            //     while($r < 5)
            //     {
            //         $reels = $originalReels;                    
            //         $reel = $slotSettings->GetNudgedReel($r, $reels['rp'][$r], -1);
            //         if(!empty($reel))
            //         {
            //             $reels['rp'][$r] = $reels['rp'][$r] - 1;
            //             $reels['reel'.($r+1)] = $reel;
            //             $totalWin = 0;
            //             $this->checkWinline($reels, $lines, $slotSettings, $betLine, $lineWins, $totalWin, $winTypeTmp);
            //             if($totalWin > 0 /*&& $totalWin + $gameWon <= $spinWinLimit*/)
            //             {
            //                 $nudge = -1;
            //                 $nudgeReel = $r;
            //                 break;
            //             }
            //         }
            //         $r++;
            //     }
            //     if($nudge == 0)
            //     {
            //         $lineWins = [];
            //         //try with down nudge
            //         $r = 0;
            //         while($r < 5)
            //         {
            //             $reels = $originalReels;                    
            //             $reel = $slotSettings->GetNudgedReel($r, $reels['rp'], 1);
            //             if(!empty($reel))
            //             {
            //                 $reels['rp'][$r] = $reels['rp'][$r] + 1;
            //                 $reels['reel'.($r+1)] = $reel;
            //                 $totalWin = 0;
            //                 $this->checkWinline($reels, $lines, $slotSettings, $betLine, $lineWins, $totalWin, $winTypeTmp);
            //                 if($totalWin > 0 /* && $totalWin + $gameWon <= $spinWinLimit*/)
            //                 {
            //                     $nudge = 1;
            //                     $nudgeReel = $r;
            //                     break;
            //                 }
            //             }
            //             $r++;
            //         }
            //         if($nudge == 0)
            //         {
            //             $totalWin = 0;
            //             $lineWins = [];
            //         }
            //     }
            // }
            // else if( $totalWin > 0 && /*($totalWin+$gameWon) <= $spinWinLimit &&*/ $winType == 'win' ) 
            // {
                
            // }
            // else 
            // {
            //     if($totalWin > 0)
            //     {
            //         $reels = $slotSettings->GetNoWinSpin($slotEvent);
            //         $originalReels = $reels;
            //         $lineWins = [];
            //         $totalWin = 0;
            //     }
            // }

            // $totalWin += $gameWon;
            
            $lastWin = $totalWin;

            $nudgeCode = '';
            if($nudge != 0)
            {
                if($nudge == -1)
                    $nudgeCode = ($nudgeReel+1).'D';
                else 
                    $nudgeCode = ($nudgeReel+1).'U';
            }

            $activeLines = array_fill(0, $lines, 0);
            $activeSymbols = array_fill(0, 15, 0);
            $coinWin = 0;

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
                    'reels' => $slotSettings->GetReelSymbol($reels),
                    'reels0' => $slotSettings->GetReelSymbol($originalReels),
                    // 'rpos' => [$reels['rp'][0] - 1, $reels['rp'][1] - 1, $reels['rp'][2] - 1, $reels['rp'][3] - 1, $reels['rp'][4] - 1],
                    'rpos' => [$originalReels['rp'][0] - 1, $originalReels['rp'][1] - 1, $originalReels['rp'][2] - 1, $originalReels['rp'][3] - 1, $originalReels['rp'][4] - 1],
                    'wonCoins' => $coinWin,
                    'wonMoney' => number_format($coinWin * $betLine, 2),
                    'freeSpins' => $spinIndex
                ],
                'prepaid' => false,
                'prizes' => null,
                'status' => 'RESULTED',
                'step' => $slotSettings->GetGameData($slotSettings->slotId . 'SpinStep'),
                'wonamount' => 0
            ];            

            $bet['eventdata']['reelSet'] = 'FeatureReelsC';
            $bet['eventdata']['activeGods'] = 4;
            
            $wts = [implode('', $activeLines), implode('', $activeSymbols)];
            $bet['eventdata']['wts'] = $wts;
            $bet['eventdata']['wtw'] = $lineWins;
            $slotSettings->SetGameData($slotSettings->slotId . 'GameCoinWon', $slotSettings->GetGameData($slotSettings->slotId . 'GameCoinWon') + $coinWin);
            $slotSettings->SetGameData($slotSettings->slotId . 'GameWon', $totalWin);
            $slotSettings->SetGameData($slotSettings->slotId . 'Coin', $betLine);
            $bet['eventdata']['accWa'] = $slotSettings->GetGameData($slotSettings->slotId . 'GameWon');
            $bet['eventdata']['doubleAmount'] = $slotSettings->GetGameData($slotSettings->slotId . 'GameWon');
            $bet['eventdata']['doubleCount'] = 1;
            if($nudgeCode != '')
                $bet['eventdata']['nudge'] = $nudgeCode;
            
            if($spinIndex == 0 && $totalWin > 0)
            {
                $bet['eventdata']['nextCmds'] = 'C';
            }

            $bets[] = $bet;            
            $slotSettings->SetGameData($slotSettings->slotId . 'SpinStep', $slotSettings->GetGameData($slotSettings->slotId . 'SpinStep') + 1);            
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


