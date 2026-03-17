<?php 
namespace VanguardLTE\Games\FourFantasticFishYGG
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
                        $response = file(base_path() . '/app/Games/FourFantasticFishYGG/translation.txt')[0];
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
                        $response = '{"code":0,"data":{"id":"2203301519500100062","org":null,"gameid":0,"data":{"appsrv":"","file":"/init/","gameid":"10235","height":"600","license":"","org":"Demo","root":"","type":"Html","width":"800"}},"fn":"clientinfo","utcts":1648653590613}';
                        break;
                    case 'info':
                        $response = '{"code":0,"data":{"initialized":true,"playerState":{"state":"e30="}},"fn":"info","utcts":1669171450608}';
                        break;                        
                    case 'game':
                        $filename = base_path() . '/app/Games/FourFantasticFishYGG/game.txt';
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

                        if( $postData['slotEvent'] == 'bet' || $cmd == 'BUY_BONUS')
                        {
                            $slotSettings->SetBalance(-1 * $allbet, $postData['slotEvent']);
                            $bankSum = $allbet / 100 * $slotSettings->GetPercent();
                            $slotSettings->UpdateJackpots($allbet);
                            $slotSettings->SetBet($allbet);
                            if($cmd == 'BUY_BONUS')
                                $slotSettings->SetBank('bonus', $bankSum, 'bonus');
                            else
                                $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $bankSum, $postData['slotEvent']);                            
                            
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
            
            $nCoins = 10;
            $betLine = 0;
            if(!isset($postData['coin']))
                $postData['coin'] = $slotSettings->GetGameData($slotSettings->slotId . 'BetCoin');
            
            $betLine = $postData['coin'];
            
            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $nCoins, $cmd == 'BUY_BONUS');
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
            if($this->debug && $postData['slotEvent'] != 'freespin')
            {                 
                $winType = 'bonus';
            }

            $spinAcquired = false;
            $gameWin = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin');

            $minReels = [];
            $minLineWins = [];
            $minTotalWin = -1;
            $minFreespinsWon = 0;
            $minReels0 = [];
            $minScatterPos = [];
            $minSmSpecificFishes = [];

            $totalWin = 0;            
            $freespinsWon = 0;            
            $lineWins = [];
            $reels0 = [];
            $reels = [];
            $smSpecificFishes = [];

            $scatter = 0;
            $wild = [1];
            $bonusMpl = 1;
            $fishSym = 12;
            $mpl = 1;
            $reelName = "base";
            
            $cashFishPrizesInWins = [];
            $cashFishPrizesOutOfWins = [];

            $squid = null;
            for( $i = 0; $i <= 600; $i++ )
            {
                $totalWin = 0;
                $freespinsWon = 0;
                $lineWins = [];
                $cashFishPrizesInWins = [];
                $cashFishPrizesOutOfWins = [];
                $mpl = 1;
                $reels = $slotSettings->GetReelStrips($winType, $reelName);
                $cWins = [
                    "0" => 0,
                    "1" => 0,
                    "2" => 0,
                    "3" => 0,
                    "4" => 0,
                    "5" => 0,
                    "6" => 0,
                    "7" => 0,
                    "8" => 0,
                    "9" => 0,
                    "10" => 0,
                    "11" => 0,
                    "12" => 0
                ];
                $reels0 = $reels;
                $bonusMpl = 1;
                
                //set amount for fish symbols
                for($r = 0; $r < 6; $r++)
                    for($c = 0; $c < 4; $c++)
                    {
                        if($reels['reel'.($r+1)][$c] == $fishSym)
                        {
                            $mult = rand(1, 300);
                            if($r == 5 && rand(0, 100) < 2)
                                $mult = 1000;
                            $amount = $postData['coin'] * $mult;
                            $position = 6 * $c + $r;
                            $fishType = $this->GetFishType($mult);                            
                            $smSpecificFishes[$position] = [
                                'amount' => $amount,
                                'fishType' => $fishType
                            ];
                        }
                    }

                for( $j = 0; $j <count($slotSettings->SymbolGame); $j++ ) 
                {
                    $mpl = 1;
                    $csym = $slotSettings->SymbolGame[$j];                    
                    $mul1 = $slotSettings->getMultiplier($reels['reel1'], $csym, $wild[0]);
                    $mul2 = $slotSettings->getMultiplier($reels['reel2'], $csym, $wild[0]);
                    $mul3 = $slotSettings->getMultiplier($reels['reel3'], $csym, $wild[0]);
                    $mul4 = $slotSettings->getMultiplier($reels['reel4'], $csym, $wild[0]);
                    $mul5 = $slotSettings->getMultiplier($reels['reel5'], $csym, $wild[0]);
                    $mul6 = $slotSettings->getMultiplier($reels['reel6'], $csym, $wild[0]);

                    if($mul1 > 0 && $mul2 > 0 ) //from left to right 2 symbols contained
                    {
                        $mpl = $mul1 * $mul2;
                        $coin = $slotSettings->Paytable[$csym][2] * $mpl * $bonusMpl;
                        $tmpWin = $coin * $betLine;
                        if($tmpWin > $cWins[$csym])
                        {
                            $cWins[$csym] = $tmpWin;                            
                            $winline = [$j + 1, $coin, $this->getPositions($csym, $reels, 2), $tmpWin, $csym, 2, $mpl, $mul1 + $mul2];
                        }
                    }

                    if($mul1 > 0 && $mul2 > 0 && $mul3 > 0) //from left to right 3 symbols contained
                    {
                        $mpl = $mul1 * $mul2 * $mul3;
                        $coin = $slotSettings->Paytable[$csym][3] * $mpl * $bonusMpl;
                        $tmpWin = $coin * $betLine;
                        if($tmpWin > $cWins[$csym])
                        {
                            $cWins[$csym] = $tmpWin;                            
                            $winline = [$j + 1, $coin, $this->getPositions($csym, $reels, 3), $tmpWin, $csym, 3, $mpl, $mul1 + $mul2 + $mul3];
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
                            $winline = [$j + 1, $coin, $this->getPositions($csym, $reels, 4), $tmpWin, $csym, 4, $mpl, $mul1 + $mul2 + $mul3 + $mul4];
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
                            $winline = [$j + 1, $coin, $this->getPositions($csym, $reels, 5), $tmpWin, $csym, 5, $mpl, $mul1 + $mul2 + $mul3 + $mul4 + $mul5];
                        }
                    }

                    if($mul1 > 0 && $mul2 > 0 && $mul3 > 0 && $mul4 > 0 && $mul5 > 0 && $mul6 > 0) //from left to right 5 symbols contained
                    {
                        $mpl = $mul1 * $mul2 * $mul3 * $mul4 * $mul5 * $mul6;
                        $coin = $slotSettings->Paytable[$csym][6] * $mpl * $bonusMpl;
                        $tmpWin = $coin * $betLine;
                        if($tmpWin > $cWins[$csym])
                        {
                            $cWins[$csym] = $tmpWin;
                            $winline = [$j + 1, $coin, $this->getPositions($csym, $reels, 6), $tmpWin, $csym, 6, $mpl, $mul1 + $mul2 + $mul3 + $mul4 + $mul5 + $mul6];
                        }
                    }
                    
                    if($cWins[$csym] > 0 && !empty($winline))
                    {
                        array_push($lineWins, $winline);
                        $totalWin += $cWins[$csym];
                    }
                }

                //process fish symbols                      
                $fishWinLineInfo = $this->GetFishSymWinning($reels, $smSpecificFishes, $slotSettings, $postData);
                $fishWinLine = $fishWinLineInfo[0];
                $cashFishPrizesInWins = $fishWinLineInfo[1];
                $cashFishPrizesOutOfWins = $fishWinLineInfo[2];
                if(count($fishWinLine) > 0)
                {
                    $totalWin += $fishWinLine[3];
                    $lineWins[] = $fishWinLine;
                }

                //calc freespin
                $freespinsWon = 0;
                $scatterPos = [];
                $scatterCnt = 0;
                for($r = 0; $r < 6; $r++)
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
                    $freespinsWon = 3;
                    $winline = [$j + 1, 0, $this->getPositions($scatter, $reels, 6), 0, $scatter, $scatterCnt, 1, $scatterCnt];
                    $lineWins[] = $winline;
                }

                if($minTotalWin == -1 || ($minTotalWin > $totalWin && $totalWin > 0))
                {
                    $minTotalWin = $totalWin;
                    $minLineWins = $lineWins;
                    $minFreespinsWon = $freespinsWon;
                    $minReels = $reels;
                    $minReels0 = $reels0;                         
                    $minScatterPos = $scatterPos;
                    $minSmSpecificFishes = $smSpecificFishes;
                }

                if($this->debug)
                {
                    $spinAcquired = true;
                    break;
                }

                if($winType == 'win' && $totalWin == 0 && rand(0, 100) < 100)
                {
                    $squidData = $this->doSquidSpin($slotSettings, $postData, $reels, $spinWinLimit, $smSpecificFishes);
                    if($squidData != null)
                    {
                        $lineWins = $squidData[0];
                        $squid = $squidData[1];
                        $totalWin = $squidData[2];
                        $spinAcquired = true;
                        break;
                    }
                }

                if($totalWin <= $spinWinLimit && (($totalWin > 0 && $winType != 'none') || ($winType == 'bonus' && $freespinsWon >= 3) ))
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
                $scatterPos = $minScatterPos;
                $smSpecificFishes = $minSmSpecificFishes;
            }

            $this->lastReels = $reels;           
            
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
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreespinLastReels',  $reels);
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreespinScatterPos',  $scatterPos);
                    $postData['slotEvent'] = 'freespin';
                    $this->gameState = 'Pending';  
                    $freespinLeft = $freespinsWon;
                }
            }            

            $partialSpinCalcResults = [];
            $respinWin = 0;
            $respinState = [];

            if($freespinLeft > 0)
            {
                $activeReels = array_fill(0, 6, 0);
                for($i = 0; $i < 6; $i++)
                {
                    if(in_array($i, $scatterPos))
                        $activeReels[$i] = 1;
                }
                
                $slotSettings->SetGameData($slotSettings->slotId . 'SMSpecificFishes', []);
                $reelState = [];
                for($r = 0; $r < 6; $r++)
                {
                    $view = [];
                    for($c = 0; $c < 4; $c++)
                    {                        
                        $view[] = [
                            'p' => ['x' => $r, 'y' => $c],
                            't' => 'Empty',
                            'v' => null
                        ];
                    }
                    $reelState[] = [
                        'isClosed'=>$activeReels[$r] == 0,
                        'isPendingNextSpinOpen' => false,
                        'reelIndex'=>$r,
                        'squidWinsIds' => [],
                        'view' => $view,
                        'winsCurrSpin' => []
                    ];
                }
                $respinState = [
                    'isActive'=> true,
                    'livesLeftAfterSpin' => 3,
                    'reelsState' => [
                        'reels' => $reelState,
                        'squidSwaps' => []
                    ]
                ];
               
                $this->doSubSpin($slotSettings, $postData, $partialSpinCalcResults, $cmd, $activeReels, $respinWin, true);
                while($freespinLeft > 0)
                {
                    $this->doSubSpin($slotSettings, $postData, $partialSpinCalcResults, $cmd, $activeReels, $respinWin);
                    $freespinLeft = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                }
            }
            
            $winsWays = [];
            if(!empty($lineWins))
            {
                foreach($lineWins as $winline)
                {
                    $symbol = $winline[4];                    
                    $coinWin += $winline[1]; //sum up coins
                    $isScatterWin = false;
                    $winAmount = $winline[3];
                    if($symbol == $scatter)
                    {
                        $isScatterWin = true;
                        $winAmount += $respinWin;
                    }
                    $winsWays[] = [
                        'bonusSpinsAwardCount' => 0,
                        'combLen'=> $winline[5],
                        'isScatterWin' => $isScatterWin,
                        'smSpecific' => null,
                        'sym' => $symbol,
                        'symsCount' => $winline[7],
                        'symsLocs' => $winline[2],
                        'waysCombsCount' => $winline[6],
                        'winAmount' => $winAmount,
                        'winAmountsBeforeMult' => [],                        
                    ];
                }
            }            

            $slotSettings->SetGameData($slotSettings->slotId . 'CoinWin', $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') + $coinWin);            
            $slotSettings->SetWin($totalWin);
            $totalWin += $respinWin;
            //nextCmds
            $nextCmds = [];

            $needRespin = false;
            $eventData = [
                'accC' => $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin'),
                'accWa' => number_format($slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') * $betLine, 2),                
                'manualNoWin' => $manualNoWin,
            ];
            
            if(count($partialSpinCalcResults))
            {
                $mainSpin = [
                    'hasWin' => $totalWin > 0,
                    'reelsView' => [
                        'srr' => [
                            'reelsSetChoice' => [
                                'indexes' => [$reels['choice']],
                                'indexesCont' => [0]
                            ],
                            'stops' => $reels['rp']
                        ],
                        'vCalc' => $this->GetReelArray($reels),
                        'vOrig' => $this->GetReelArray($reels),
                    ],
                    'smSpecific' => [
                        'cashFishPrizesInWins' => $cashFishPrizesInWins,
                        'cashFishPrizesOutOfWins' => $cashFishPrizesOutOfWins,
                        'respinState' => null,
                        'squid' => null,
                    ],
                ];
                array_unshift($partialSpinCalcResults, $mainSpin);
            }

            $clientData = [
                'partialSpinCalcResults' => $partialSpinCalcResults,
                'spinCalcRes' => [
                    'hasWin' => $totalWin > 0,
                    'reelsView' => [
                        'srr' => [
                            'reelsSetChoice' => [
                                'indexes' => [$reels['choice']],
                                'indexesCont' => [0]
                            ],
                            'stops' => $reels['rp']
                        ],
                        'vCalc' => $this->GetReelArray($reels),
                        'vOrig' => $this->GetReelArray($reels),
                    ],
                    'smSpecific' => [
                        'cashFishPrizesInWins' => $cashFishPrizesInWins,
                        'cashFishPrizesOutOfWins' => $cashFishPrizesOutOfWins,
                        'respinState' => $respinState,
                        'squid' => $squid,
                    ],
                    'spinReelsRes' => [
                        'reelsSetChoice' => [
                            'indexes' => [$reels['choice']],
                            'indexesCont' => [0]
                        ],
                        'stops' => $reels['rp']
                    ],
                    'winAmount' => $totalWin,
                    'winAmountsBeforeMult' => [],
                    'winsWays' => $winsWays
                ]
            ];
           
            $eventData['response'] = [
                'cashWin' => number_format($slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') * $betLine, 2),
                'clientData' => $clientData,
                'coinWin'=> $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin')
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

        function doSubSpin($slotSettings, &$postData, &$bets, $cmd, &$activeReels, &$respinWin, $emptySpin = false)
        {
            $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'freespin');
            
            $nCoins = 10;
            $betLine = 0;
            if(!isset($postData['coin']))
                $postData['coin'] = $slotSettings->GetGameData($slotSettings->slotId . 'BetCoin');
            
            $betLine = $postData['coin'];            
            
            $winTypeTmp = $slotSettings->GetSpinSettings('freespin', $betLine, $nCoins);
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
            $spinAcquired = false;
            $gameWin = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin');

            $minReels = [];
            $minLineWins = [];
            $minTotalWin = -1;
            $minReels0 = [];
            $minSmSpecificFishes = [];

            $totalWin = 0;            
            $lineWins = [];
            $reels0 = [];
            $reels = [];
            $smSpecificFishes = [];
            $minNewFishAdded = [];
  
            $fishSym = 12;           
            
            $smSpecificFishes = $slotSettings->GetGameData($slotSettings->slotId . 'SMSpecificFishes');
            $newFishAdded = [];
            for( $i = 0; $i <= 600; $i++ )
            {
                $totalWin = 0;
                $lineWins = [];
                $smSpecificFishes = $slotSettings->GetGameData($slotSettings->slotId . 'SMSpecificFishes');
                $newFishAdded = [];

                $reels = [];
                for($r = 0; $r < 6; $r++)
                {
                    $reel = [];
                    for($c = 0; $c < 4; $c++)
                    {
                        $possibility = 0;
                        if($winType == 'win')
                            $possibility = 20;
                        if(rand(0, 100) < $possibility && !$emptySpin)
                            $reel[] = $fishSym;
                        else
                            $reel[] = -9007199254740991;

                        $position = $c * 6 + $r;
                        if(isset($smSpecificFishes[$position]))
                            $reel[$c] = $fishSym;
                    }
                    $reels['reel'.($r+1)] = $reel;
                }
                $reels['choice'] = 0;
                $reels['rp'] = [-1,-1,-1,-1,-1,-1];
    
                $reels0 = $reels;

                //set amount for fish symbols
                for($r = 0; $r < 6; $r++)
                    for($c = 0; $c < 4; $c++)
                    {
                        if($reels['reel'.($r+1)][$c] == $fishSym)
                        {
                            $position = 6 * $c + $r;
                            if(!isset($smSpecificFishes[$position]))
                            {
                                $mult = rand(1, 300);
                                $amount = $postData['coin'] * $mult;
                                
                                $fishType = $this->GetFishType($mult);
                                $smSpecificFishes[$position] = [
                                    'position' => ['x' => $r, 'y' => $c],
                                    'amount' => $amount,
                                    'fishType' => $fishType,
                                    'active' => $activeReels[$r] == 1
                                ];
                                $newFishAdded[$position] = [
                                    'position' => ['x' => $r, 'y' => $c],
                                    'amount' => $amount,
                                    'fishType' => $fishType,
                                    'active' => $activeReels[$r] == 1
                                ];

                                if($activeReels[$r] == 1)
                                    $totalWin += $amount;
                            }
                        }
                    }
      
                if($minTotalWin == -1 || ($minTotalWin > $totalWin && $totalWin > 0))
                {
                    $minTotalWin = $totalWin;
                    $minLineWins = $lineWins;
                    $minReels = $reels;
                    $minReels0 = $reels0;                         
                    $minSmSpecificFishes = $smSpecificFishes;
                    $minNewFishAdded = $newFishAdded;
                }

                if($totalWin <= $spinWinLimit && $winType != 'none' && $totalWin > 0)
                {
                    $spinAcquired = true;
                    if($totalWin < 0.5 * $spinWinLimit && $winType == 'win')
                        $spinAcquired = false;                    
                    if($spinAcquired)
                        break;
                }
                else if( $winType == 'none' && $totalWin == 0 ) 
                {
                    break;
                }
            }

            if(!$spinAcquired && $totalWin > 0 && $winType != 'none')
            {                
                $reels = $minReels;
                $reels0 = $minReels0;
                $lineWins = $minLineWins;
                $totalWin = $minTotalWin;
                $smSpecificFishes = $minSmSpecificFishes;
                $newFishAdded = $minNewFishAdded;
            }

            $this->lastReels = $reels;            

            $respinWin += $totalWin;

            $coinWin = $totalWin / $betLine;
            $slotSettings->SetGameData($slotSettings->slotId . 'GameWin',  $slotSettings->GetGameData($slotSettings->slotId . 'GameWin') + $totalWin);
            $slotSettings->SetGameData($slotSettings->slotId . 'CoinWin', $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') + $coinWin);

            $slotSettings->SetWin($totalWin);
            if(!$emptySpin)
                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);

            if($totalWin > 0)
            {
                //added fish, increase one spin
                $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 3);
            }
            $this->gameState = 'Pending';
            
            
            $freespinLeft = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');

            $reelState = [];
            for($r = 0; $r < 6; $r++)
            {
                $winCurrSpin = [];
                $view = [];
                for($c = 0; $c < 4; $c++)
                {
                    $position = $c * 6 + $r;
                    $t = 'Empty';
                    if(isset($smSpecificFishes[$position]))
                    {
                        $fishInfo = $smSpecificFishes[$position];
                        $winCurrSpin[] = [
                            'cashFishType'=>$fishInfo['fishType'],
                            'pos' => [$fishInfo['position']],
                            'winAmount'=> $fishInfo['amount'],
                            'winAmountsBeforeMult' => [],
                            'isClosedReelNotPaid' => $activeReels[$r] == 0
                        ];
                        $t = 'CashFish';
                    }
                    $view[] = [
                        'p' => [
                            'x' => $r,
                            'y' => $c
                        ],
                        't' => $t,
                        'v' => 0
                    ];
                }
                $state = [
                    'isClosed' => $activeReels[$r] == 0,
                    'isPendingNextSpinOpen' => false,
                    'reelIndex' => $r,
                    'squidWinsIds' => [],
                    'view' => $view,
                    'winsCurrSpin' => $winCurrSpin
                ];
                $reelState[] = $state;
            }

            //remove fishes on closed reel
            for($r = 0; $r < 6; $r++)
                for($c = 0; $c < 4; $c++)
                {
                    $position = $c * 6 + $r;
                    if($activeReels[$r] == 0 && isset($smSpecificFishes[$position]))
                        unset($smSpecificFishes[$position]);
                }
            $slotSettings->SetGameData($slotSettings->slotId . 'SMSpecificFishes', $smSpecificFishes);

            $respinState = [
                'isActive' => true,
                'livesLeftAfterSpin'=> $freespinLeft,
                'reelsState' => [
                    'reels' => $reelState,
                    'squidSwaps' => []
                ]
            ];
            
            $data = [
                'hasWin' => $totalWin > $gameWin,
                'reelsView' => [
                    'srr' => [
                        'reelsSetChoice' => [
                            'indexes' => [$reels['choice']],
                            'indexesCont' => [0]
                        ],
                        'stops' => $reels['rp']
                    ],
                    'vCalc' => $this->GetReelArray($reels),
                    'vOrig' => $this->GetReelArray($reels0),
                ],
                'smSpecific' => [
                    'cashFishPrizesInWins' => [],
                    'cashFishPrizesOutOfWins' => [],
                    'respinState' => $respinState,
                    'squid' => null,
                ],
                'spinReelsRes' => [
                    'reelsSetChoice' => [
                        'indexes' => [$reels['choice']],
                        'indexesCont' => [0]
                    ],
                    'stops' => $reels['rp']
                ],
                'winAmount' => 0,
                'winAmountsBeforeMult' => [],
                'winsWays' => []
            ];

            $bets[] = $data;
            // $slotSettings->SetGameData($slotSettings->slotId . 'Step', $slotSettings->GetGameData($slotSettings->slotId . 'Step') + 1);
        }

        function doSquidSpin($slotSettings, &$postData, &$reels, $spinWinLimit, $smSpecificFishes)
        {            
            $betLine = 0;
            if(!isset($postData['coin']))
                $postData['coin'] = $slotSettings->GetGameData($slotSettings->slotId . 'BetCoin');
            
            $betLine = $postData['coin'];
            
            $spinAcquired = false;
            $totalWin = 0;
            
            $lineWins = [];      

            $wild = [1];
            $bonusMpl = 1;
            $mpl = 1;
            
            $cashFishPrizesInWins = [];
            $cashFishPrizesOutOfWins = [];

            $swapSymbol = "";
            $swapPos = [];
            for( $j = 0; $j <count($slotSettings->SymbolGame); $j++ ) 
            {
                $mpl = 1;
                $csym = $slotSettings->SymbolGame[$j];                    
                $mul1 = $slotSettings->getMultiplier($reels['reel1'], $csym, $wild[0]);
                $mul2 = $slotSettings->getMultiplier($reels['reel2'], $csym, $wild[0]);
                $mul3 = $slotSettings->getMultiplier($reels['reel3'], $csym, $wild[0]);
                $mul4 = $slotSettings->getMultiplier($reels['reel4'], $csym, $wild[0]);
                $mul5 = $slotSettings->getMultiplier($reels['reel5'], $csym, $wild[0]);
                $mul6 = $slotSettings->getMultiplier($reels['reel6'], $csym, $wild[0]);
             
                if($mul1 == 0 && $mul2 > 0 && $mul3 > 0)           
                {
                    $r = -1;
                    $c = -1;
                    if($mul4 > 0)
                    {
                        $c = $this->GetPos($reels['reel4'], $csym);
                        $r = 3;
                    }
                    else if($mul5 > 0)
                    {
                        $c = $this->GetPos($reels['reel5'], $csym);
                        $r = 4;
                    }
                    else if($mul6 > 0)
                    {
                        $c = $this->GetPos($reels['reel6'], $csym);
                        $r = 5;
                    }

                    if($c != -1)
                    {
                        $swapSymbol = $csym;
                        $swapPos[] = [
                            'from' => ['x' => $r, 'y' => $c],
                            'to' => ['x' => 0, 'y' => rand(0, 3)]
                        ];
                        break;
                    }
                }
                else if($mul1 > 0 && $mul2 == 0 && $mul3 > 0) 
                {
                    $r = -1;
                    $c = -1;
                    if($mul4 > 0)
                    {
                        $c = $this->GetPos($reels['reel4'], $csym);
                        $r = 3;
                    }
                    else if($mul5 > 0)
                    {
                        $c = $this->GetPos($reels['reel5'], $csym);
                        $r = 4;
                    }
                    else if($mul6 > 0)
                    {
                        $c = $this->GetPos($reels['reel6'], $csym);
                        $r = 5;
                    }

                    if($c != -1)
                    {
                        $swapSymbol = $csym;
                        $swapPos[] = [
                            'from' => ['x' => $r, 'y' => $c],
                            'to' => ['x' => 1, 'y' => rand(0, 3)]
                        ];
                        break;
                    }
                }
                else if($mul1 > 0 && $mul2 > 0 && $mul3 == 0) 
                {
                    $r = -1;
                    $c = -1;
                    if($mul4 > 0)
                    {
                        $c = $this->GetPos($reels['reel4'], $csym);
                        $r = 3;
                    }
                    else if($mul5 > 0)
                    {
                        $c = $this->GetPos($reels['reel5'], $csym);
                        $r = 4;
                    }
                    else if($mul6 > 0)
                    {
                        $c = $this->GetPos($reels['reel6'], $csym);
                        $r = 5;
                    }

                    if($c != -1)
                    {
                        $swapSymbol = $csym;
                        $swapPos[] = [
                            'from' => ['x' => $r, 'y' => $c],
                            'to' => ['x' => 2, 'y' => rand(0, 3)]
                        ];
                        break;
                    }
                }
            }

            $reels0 = $reels;
            if($swapSymbol != "")
            {
                $from = $swapPos[0]['from'];
                $to = $swapPos[0]['to'];
                $fishSym = 12;
                if($reels['reel'.($to['x'] + 1)][$to['y']] == $fishSym)
                {
                    $r = $to['x'];
                    $c = $to['y'];
                    $position_to = $c * 6 + $r;
                    if(isset($smSpecificFishes[$position_to]))
                    {
                        $data = $smSpecificFishes[$position_to];
                        unset($smSpecificFishes[$position_to]);
                        $r = $from['x'];
                        $c = $from['y'];
                        $position_from = $c * 6 + $r;
                        $smSpecificFishes[$position_from] = $data;
                    }

                }
                $reels['reel'.($from['x'] + 1)][$from['y']] = $reels['reel'.($to['x'] + 1)][$to['y']];
                $reels['reel'.($to['x'] + 1)][$to['y']] = $swapSymbol;
            }
            else
            {
                return null;
            }

            $spinAcquired = false;
            $totalWin = 0;
            $lineWins = [];
            $cashFishPrizesInWins = [];
            $cashFishPrizesOutOfWins = [];
            $mpl = 1;                
            $cWins = [
                "0" => 0,
                "1" => 0,
                "2" => 0,
                "3" => 0,
                "4" => 0,
                "5" => 0,
                "6" => 0,
                "7" => 0,
                "8" => 0,
                "9" => 0,
                "10" => 0,
                "11" => 0,
                "12" => 0
            ];
            $bonusMpl = 1;

            for( $j = 0; $j <count($slotSettings->SymbolGame); $j++ ) 
            {
                $mpl = 1;
                $csym = $slotSettings->SymbolGame[$j];                    
                $mul1 = $slotSettings->getMultiplier($reels['reel1'], $csym, $wild[0]);
                $mul2 = $slotSettings->getMultiplier($reels['reel2'], $csym, $wild[0]);
                $mul3 = $slotSettings->getMultiplier($reels['reel3'], $csym, $wild[0]);
                $mul4 = $slotSettings->getMultiplier($reels['reel4'], $csym, $wild[0]);
                $mul5 = $slotSettings->getMultiplier($reels['reel5'], $csym, $wild[0]);
                $mul6 = $slotSettings->getMultiplier($reels['reel6'], $csym, $wild[0]);

                if($mul1 > 0 && $mul2 > 0 ) //from left to right 2 symbols contained
                {
                    $mpl = $mul1 * $mul2;
                    $coin = $slotSettings->Paytable[$csym][2] * $mpl * $bonusMpl;
                    $tmpWin = $coin * $betLine;
                    if($tmpWin > $cWins[$csym])
                    {
                        $cWins[$csym] = $tmpWin;                            
                        $winline = [$j + 1, $coin, $this->getPositions($csym, $reels, 2), $tmpWin, $csym, 2, $mpl, $mul1 + $mul2];
                    }
                }

                if($mul1 > 0 && $mul2 > 0 && $mul3 > 0) //from left to right 3 symbols contained
                {
                    $mpl = $mul1 * $mul2 * $mul3;
                    $coin = $slotSettings->Paytable[$csym][3] * $mpl * $bonusMpl;
                    $tmpWin = $coin * $betLine;
                    if($tmpWin > $cWins[$csym])
                    {
                        $cWins[$csym] = $tmpWin;                            
                        $winline = [$j + 1, $coin, $this->getPositions($csym, $reels, 3), $tmpWin, $csym, 3, $mpl, $mul1 + $mul2 + $mul3];
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
                        $winline = [$j + 1, $coin, $this->getPositions($csym, $reels, 4), $tmpWin, $csym, 4, $mpl, $mul1 + $mul2 + $mul3 + $mul4];
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
                        $winline = [$j + 1, $coin, $this->getPositions($csym, $reels, 5), $tmpWin, $csym, 5, $mpl, $mul1 + $mul2 + $mul3 + $mul4 + $mul5];
                    }
                }

                if($mul1 > 0 && $mul2 > 0 && $mul3 > 0 && $mul4 > 0 && $mul5 > 0 && $mul6 > 0) //from left to right 5 symbols contained
                {
                    $mpl = $mul1 * $mul2 * $mul3 * $mul4 * $mul5 * $mul6;
                    $coin = $slotSettings->Paytable[$csym][6] * $mpl * $bonusMpl;
                    $tmpWin = $coin * $betLine;
                    if($tmpWin > $cWins[$csym])
                    {
                        $cWins[$csym] = $tmpWin;
                        $winline = [$j + 1, $coin, $this->getPositions($csym, $reels, 6), $tmpWin, $csym, 6, $mpl, $mul1 + $mul2 + $mul3 + $mul4 + $mul5 + $mul6];
                    }
                }
                
                if($cWins[$csym] > 0 && !empty($winline))
                {
                    array_push($lineWins, $winline);
                    $totalWin += $cWins[$csym];
                }
            }

            //process fish symbols                      
            $fishWinLineInfo = $this->GetFishSymWinning($reels, $smSpecificFishes, $slotSettings, $postData);
            $fishWinLine = $fishWinLineInfo[0];
            $cashFishPrizesInWins = $fishWinLineInfo[1];
            $cashFishPrizesOutOfWins = $fishWinLineInfo[2];
            if(count($fishWinLine) > 0)
            {
                $totalWin += $fishWinLine[3];
                $lineWins[] = $fishWinLine;
            }

            if($totalWin <= $spinWinLimit && $totalWin >= 0.5 * $spinWinLimit)
            {
                $spinAcquired = true;                
            }           
            
            if(!$spinAcquired)
            {
                return null;
            }
            
            $respinState = [];
            $winsWays = [];            
            
            $squid = [
                'origSpinCalcRes' => [
                    'hasWin' => false,
                    'reelsView' => [
                        'srr' => [
                            'reelsSetChoice' => [
                                'indexes' => [$reels0['choice']],
                                'indexesCont' => [0]
                            ],
                            'stops' => $reels0['rp']
                        ],
                        'vCalc' => $this->GetReelArray($reels0),
                        'vOrig' => $this->GetReelArray($reels0),
                    ],
                    'smSpecific' => [
                        'cashFishPrizesInWins' => $cashFishPrizesInWins,
                        'cashFishPrizesOutOfWins' => $cashFishPrizesOutOfWins,
                        'respinState' => $respinState,
                        'squid' => null,
                    ],
                    'spinReelsRes' => [
                        'reelsSetChoice' => [
                            'indexes' => [$reels0['choice']],
                            'indexesCont' => [0]
                        ],
                        'stops' => $reels0['rp']
                    ],
                    'winAmount' => 0,
                    'winAmountsBeforeMult' => [],
                    'winsWays' => $winsWays
                ],
                'swaps' => $swapPos
            ];
           
            return [$lineWins, $squid, $totalWin];
        }

        function GetReelArray($reels)
        {
            $res = [];
            for($r = 0; $r < 6; $r++)
                $res[] = $reels['reel'.($r+1)];
            return $res;
        }

        function GetFishType($mult)
        {
            $fishType = 1;
            if($mult <= 5)
                $fishType = 1;
            else if($mult > 5 && $mult < 10)
                $fishType = 2;
            else if($mult >= 10 && $mult < 20)
                $fishType = 3;
            else if($mult >= 20 && $mult < 30)
                $fishType = 4;
            else if($mult >= 40 && $mult < 50)
                $fishType = 5;
            else if($mult >= 50 && $mult < 100)
                $fishType = 6;
            else if($mult >= 100 && $mult < 200)
                $fishType = 7;
            else if($mult >= 200 && $mult < 1000)
                $fishType = 8;
            else if($mult == 1000)
                $fishType = 9;
            else if($mult == 5000)
                $fishType = 10;
            return $fishType;
        }

        function GetFishSymWinning($reels, $smFishes, $slotSettings, $postData)
        {
            $fishSym = 12;
            $mul1 = $slotSettings->getMultiplier($reels['reel1'], $fishSym, 1);
            $mul2 = $slotSettings->getMultiplier($reels['reel2'], $fishSym, 1);
            $mul3 = $slotSettings->getMultiplier($reels['reel3'], $fishSym, 1);
            $mul4 = $slotSettings->getMultiplier($reels['reel4'], $fishSym, 1);
            $mul5 = $slotSettings->getMultiplier($reels['reel5'], $fishSym, 1);
            $mul6 = $slotSettings->getMultiplier($reels['reel6'], $fishSym, 1);

            $winningReel = 0;
            if($mul1 > 0 && $mul2 > 0 && $mul3 > 0)
                $winningReel = 3;
            if($mul1 > 0 && $mul2 > 0 && $mul3 > 0 && $mul4 > 0)
                $winningReel = 4;
            if($mul1 > 0 && $mul2 > 0 && $mul3 > 0 && $mul4 > 0 && $mul5 > 0)
                $winningReel = 5;
            if($mul1 > 0 && $mul2 > 0 && $mul3 > 0 && $mul4 > 0 && $mul5 > 0 && $mul6 > 0)
                $winningReel = 6;
            
            $winline = [];

            $fishPrizesInWins = [];
            $fishPrizesOutWins = [];

            if($winningReel >= 3)
            {
                $win = 0;
                $positions = [];
                $count = 0;

                for($r = 0; $r < $winningReel; $r++)
                    for($c = 0; $c < 4; $c++)
                    {
                        if($reels['reel'.($r+1)][$c] == $fishSym)
                        {
                            $position = 6 * $c + $r;
                            $data = $smFishes[$position];
                            $amount = $data['amount'];
                            $win += $amount;
                            $count++;
                            $positions[] = ['x' => $r, 'y' => $c];

                            $fishInfo = [
                                'cashFishType' => $data['fishType'],
                                'winAmount' => $data['amount'],
                                'pos' => [
                                    ['x' => $r, 'y' => $c]
                                ]
                                ];
                            $fishPrizesInWins[] = $fishInfo;
                        }
                    }

                $winline = [0, $win / $postData['coin'], $positions, $win, $fishSym, $winningReel, 1, $count];
            }
            for($r = $winningReel; $r < 6; $r++)
                for($c = 0; $c < 4; $c++)
                {
                    if($reels['reel'.($r+1)][$c] == $fishSym)
                    {
                        $position = 6 * $c + $r;
                        $data = $smFishes[$position];
                        $amount = $data['amount'];

                        $fishInfo = [
                            'cashFishType' => $data['fishType'],
                            'winAmount' => $data['amount'],
                            'pos' => [
                                ['x' => $r, 'y' => $c]
                            ]
                            ];
                        $fishPrizesOutWins[] = $fishInfo;
                    }
                }
            return [$winline, $fishPrizesInWins, $fishPrizesOutWins];
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


