<?php 
namespace VanguardLTE\Games\RainbowRyanYGG
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
                        $response = file(base_path() . '/app/Games/RainbowRyanYGG/translation.txt')[0];                                                                          
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
                        $response = '{"code":0,"data":{"id":"","org":null,"gameid":0,"data":{"appsrv":"","file":"/init/","gameid":"7336","height":"600","license":"","org":"Demo","root":"","type":"Html","width":"800"}},"fn":"clientinfo","utcts":'.time().'}';
                        break;
                    case 'game':
                        $filename = base_path() . '/app/Games/RainbowRyanYGG/game.txt';
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
                        $betLine = 0;
                        $nCoins = 50;
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
                            //collect current supermeter and end jokerizer mode
                            $curCoinWin = $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin');
                            $curBetCoin = $slotSettings->GetGameData($slotSettings->slotId . 'BetCoin');
                            $win = $curCoinWin * $curBetCoin;
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
                                                'betcurrency' => 'USD',
                                                'wonamount' => number_format($win, 2),
                                                'status' => 'RESULTED',
                                                'betdata'=> [
                                                    'doubleA' => number_format($win, 2),
                                                    'doubleN' => 1,
                                                    'cheat' => null,
                                                    'cmd' => 'C',
                                                    'coin' => $slotSettings->GetGameData($slotSettings->slotId . 'BetCoin'),
                                                    'nCoins' => 50,
                                                    'restoredAccumulatedWonCoin' => $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin'),
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
                            $slotSettings->SetGameData($slotSettings->slotId . 'LastSyncedReelCount', 0);
                        }
                        else
                        {
                            $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);                            
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
            if($postData['slotEvent'] == 'freespin')
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bonus');
            else
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bet');

            $nCoins = 50;
            $betLine = 0;
            if(isset($postData['coin']))
                $betLine = $postData['coin'];
            $allbet = $betLine * $nCoins;
            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $nCoins);
            $winType = $winTypeTmp[0];

            if($this->debug && $postData['slotEvent'] != 'freespin')
                $winType = 'bonus';

            $spinWinLimit = $winTypeTmp[1];
            if($postData['slotEvent'] == 'freespin')
            {
                $freespinLeft = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                if($freespinLeft > 1)
                    $spinWinLimit /= 10;
            }
                
            $spinAcquired = false;            

            $symbolGame = $slotSettings->SymbolGame;            
            $reelName = '';            
            $mpl = 1;
            
            $gameWin = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin');
            
            $minTotalWin = -1;
            $minLineWins = [];
            $minReels = [];
            $minFreespinWon = 0;
            $minUsedReels = [];
            $minSyncReelsCount = 0;
            $cntTick = rand(0, 100);
            for( $i = 0; $i <= 300; $i++ ) 
            {
                $usedReels = [0,1,2,3,4,5];
                $syncReelsCount = 1;
                $totalWin = 0;
                $lineWins = [];
                $cWins = ["High1" => 0, "High2" => 0, "High3" => 0, "High4" => 0, "Low1" => 0, "Low2" => 0, "Low3" => 0, "Low4" => 0];                
                $scatter = "Freespin";
                if($postData['slotEvent'] == 'bet')
                    $reelName = $slotSettings->reelNames[rand(0, count($slotSettings->reelNames) - 1)];
                else
                    $reelName = $slotSettings->featureReelNames[rand(0, count($slotSettings->featureReelNames) - 1)];
                $reels = $slotSettings->GetReelStrips($winType, $reelName);  

                //sync reel probability
                $tick = rand(0, 100);
                if(($tick < 10 && $postData['slotEvent'] == 'bet') || $postData['slotEvent'] == 'freespin')
                {
                    //sync reels                    
                    if($cntTick < 80)
                        $syncReelsCount = 2;
                    else if($cntTick < 99)
                        $syncReelsCount = 3;
                    else    
                        $syncReelsCount = 4;

                    $lastSyncReelsCount = $slotSettings->GetGameData($slotSettings->slotId . 'LastSyncedReelCount');
                    if($syncReelsCount < $lastSyncReelsCount)
                        $syncReelsCount = $lastSyncReelsCount;
                    
                    $baseSyncReel = rand(0, 5);
                    $syncedReels = [];
                    $syncedReels[] = $baseSyncReel;
                    while(count($syncedReels) < $syncReelsCount)
                    {
                        $t = rand(0, 5);
                        if(!in_array($t, $syncedReels))
                            $syncedReels[] = $t;
                    }

                    for($z = 0; $z < 6; $z++)
                        if(in_array($z, $syncedReels))
                        {
                            if($z != $baseSyncReel)
                            {
                                $usedReels[$z] = $baseSyncReel;
                                $reels['rp'][$z] = $reels['rp'][$baseSyncReel];
                                $reels['reel'.($z+1)] = $reels['reel'.($baseSyncReel+1)];
                            }
                        }                    
                }

                if($this->debug)
                {
                    //reel test                    
                }
                for( $k = 0; $k < count($symbolGame); $k++ ) 
                {
                    $mpl = 1;                    
                    $csym = $symbolGame[$k];
                    $wild = "Wild";
                    if( $csym == $scatter || !isset($slotSettings->Paytable[$csym]))
                    {
                        
                    }
                    else
                    {
                        $winline = [];                        
                        if( (in_array($csym,$reels['reel1']) || in_array($wild, $reels['reel1'])) &&
                            (in_array($csym,$reels['reel2']) || in_array($wild, $reels['reel2'])) && in_array($csym, $slotSettings->HighSymbol) ) 
                        {
                            //2 symbols match
                            $bonusMpl = 1;
                            $bonusMpl *= $this->getMultiplier($reels['reel1'], $csym, $wild);
                            $bonusMpl *= $this->getMultiplier($reels['reel2'], $csym, $wild);
                            
                            $tmpWin = $slotSettings->Paytable[$csym][2] * $betLine * $mpl * $bonusMpl;
                            if($tmpWin > $cWins[$csym])
                            {
                                $cWins[$csym] = $tmpWin;
                            $winline = [$k, $slotSettings->Paytable[$csym][2] * $mpl * $bonusMpl,  $this->getActiveSymbols($reels, $csym, $wild, 2), /*$tmpWin, $slotSettings->Paytable[$csym][2] * $bonusMpl, $slotSettings->Paytable[$csym][2] * $betLine * $bonusMpl, $mpl*/];
                            }
                        }
                        if( (in_array($csym,$reels['reel1']) || in_array($wild, $reels['reel1'])) &&
                            (in_array($csym,$reels['reel2']) || in_array($wild, $reels['reel2'])) &&
                            (in_array($csym,$reels['reel3']) || in_array($wild, $reels['reel3'])) ) 
                        {
                            //3 symbols match
                            $bonusMpl = 1;
                            $bonusMpl *= $this->getMultiplier($reels['reel1'], $csym, $wild);
                            $bonusMpl *= $this->getMultiplier($reels['reel2'], $csym, $wild);
                            $bonusMpl *= $this->getMultiplier($reels['reel3'], $csym, $wild);
                            $tmpWin = $slotSettings->Paytable[$csym][3] * $betLine * $mpl * $bonusMpl;
                            if($tmpWin > $cWins[$csym])
                            {
                                $cWins[$csym] = $tmpWin;
                            $winline = [$k, $slotSettings->Paytable[$csym][3] * $mpl * $bonusMpl, $this->getActiveSymbols($reels, $csym, $wild, 3),/* $tmpWin, $slotSettings->Paytable[$csym][3] * $bonusMpl,$slotSettings->Paytable[$csym][3] * $betLine * $bonusMpl, $mpl*/];
                            }
                        }
                        if( (in_array($csym,$reels['reel1']) || in_array($wild, $reels['reel1'])) &&
                            (in_array($csym,$reels['reel2']) || in_array($wild, $reels['reel2'])) &&
                            (in_array($csym,$reels['reel3']) || in_array($wild, $reels['reel3'])) &&
                            (in_array($csym,$reels['reel4']) || in_array($wild, $reels['reel4'])) )
                        {
                            //4 symbols match
                            $bonusMpl = 1;
                            $bonusMpl *= $this->getMultiplier($reels['reel1'], $csym, $wild);
                            $bonusMpl *= $this->getMultiplier($reels['reel2'], $csym, $wild);
                            $bonusMpl *= $this->getMultiplier($reels['reel3'], $csym, $wild);
                            $bonusMpl *= $this->getMultiplier($reels['reel4'], $csym, $wild);
                            $tmpWin = $slotSettings->Paytable[$csym][4] * $betLine * $mpl * $bonusMpl;
                            if($tmpWin > $cWins[$csym])
                            {
                                $cWins[$csym] = $tmpWin;
                            $winline = [$k, $slotSettings->Paytable[$csym][4] * $mpl * $bonusMpl, $this->getActiveSymbols($reels, $csym, $wild, 4), /*$tmpWin, $slotSettings->Paytable[$csym][4] * $bonusMpl, $slotSettings->Paytable[$csym][4] * $betLine * $bonusMpl, $mpl*/]; 
                            }
                        }
                        if( (in_array($csym,$reels['reel1']) || in_array($wild, $reels['reel1'])) &&
                        (in_array($csym,$reels['reel2']) || in_array($wild, $reels['reel2'])) &&
                        (in_array($csym,$reels['reel3']) || in_array($wild, $reels['reel3'])) &&
                        (in_array($csym,$reels['reel4']) || in_array($wild, $reels['reel4'])) &&
                        (in_array($csym,$reels['reel5']) || in_array($wild, $reels['reel5']))) 
                        {
                            //5 symbols match
                            $bonusMpl = 1;
                            $bonusMpl *= $this->getMultiplier($reels['reel1'], $csym, $wild);
                            $bonusMpl *= $this->getMultiplier($reels['reel2'], $csym, $wild);
                            $bonusMpl *= $this->getMultiplier($reels['reel3'], $csym, $wild);
                            $bonusMpl *= $this->getMultiplier($reels['reel4'], $csym, $wild);
                            $bonusMpl *= $this->getMultiplier($reels['reel5'], $csym, $wild);
                            $tmpWin = $slotSettings->Paytable[$csym][5] * $betLine * $mpl * $bonusMpl;
                            if($tmpWin > $cWins[$csym])
                            {
                                $cWins[$csym] = $tmpWin;
                            $winline = [$k, $slotSettings->Paytable[$csym][5] * $mpl * $bonusMpl, $this->getActiveSymbols($reels, $csym, $wild, 5), /*$tmpWin, $slotSettings->Paytable[$csym][5] * $bonusMpl, $slotSettings->Paytable[$csym][5] * $betLine * $bonusMpl, $mpl*/];
                            }
                        }    
                        if( (in_array($csym,$reels['reel1']) || in_array($wild, $reels['reel1'])) &&
                        (in_array($csym,$reels['reel2']) || in_array($wild, $reels['reel2'])) &&
                        (in_array($csym,$reels['reel3']) || in_array($wild, $reels['reel3'])) &&
                        (in_array($csym,$reels['reel4']) || in_array($wild, $reels['reel4'])) &&
                        (in_array($csym,$reels['reel5']) || in_array($wild, $reels['reel5'])) &&
                        (in_array($csym,$reels['reel6']) || in_array($wild, $reels['reel6'])) ) 
                        {
                            //5 symbols match
                            $bonusMpl = 1;
                            $bonusMpl *= $this->getMultiplier($reels['reel1'], $csym, $wild);
                            $bonusMpl *= $this->getMultiplier($reels['reel2'], $csym, $wild);
                            $bonusMpl *= $this->getMultiplier($reels['reel3'], $csym, $wild);
                            $bonusMpl *= $this->getMultiplier($reels['reel4'], $csym, $wild);
                            $bonusMpl *= $this->getMultiplier($reels['reel5'], $csym, $wild);
                            $bonusMpl *= $this->getMultiplier($reels['reel6'], $csym, $wild);
                            $tmpWin = $slotSettings->Paytable[$csym][5] * $betLine * $mpl * $bonusMpl;
                            if($tmpWin > $cWins[$csym])
                            {
                                $cWins[$csym] = $tmpWin;
                            $winline = [$k, $slotSettings->Paytable[$csym][5] * $mpl * $bonusMpl, $this->getActiveSymbols($reels, $csym, $wild, 6), /*$tmpWin, $slotSettings->Paytable[$csym][5] * $bonusMpl, $slotSettings->Paytable[$csym][5] * $betLine * $bonusMpl, $mpl*/];
                            }
                        }                    

                        if( $cWins[$csym] > 0 && !empty($winline))
                        {
                            array_push($lineWins, $winline);
                            $totalWin += $cWins[$csym];
                        }
                    }
                }

                $freespinsWon = 0;
                $scatterCount = 0;
                
                for( $r = 1; $r <= 6; $r++ ) 
                {
                    for( $p = 0; $p <= 3; $p++ ) 
                    {
                        if( $reels['reel' . $r][$p] == $scatter )
                        {
                            $scatterCount++;
                        }                      
                    }                
                }
                if($scatterCount >= 3 && $winType != 'bonus')
                    continue;
                if($scatterCount == 3)
                    $freespinsWon = 7;
                else if($scatterCount == 4)
                    $freespinsWon = 10;
                else if($scatterCount == 5)
                    $freespinsWon = 15;
                else if($scatterCount == 6)
                    $freespinsWon = 20;
                
                $totalWin += $gameWin;

                if($this->debug)
                {
                    $spinAcquired = true;
                    break;
                }
                
                if($totalWin < $minTotalWin || $minTotalWin == -1)
                {
                    $minTotalWin == $totalWin;
                    $minLineWins = $lineWins;
                    $minReels = $reels;
                    $minFreespinWon = $freespinsWon;   
                    $minUsedReels = $usedReels;
                    $minSyncReelsCount = $syncReelsCount;                 
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
            if(!$spinAcquired && $totalWin > 0)
            {
                if($postData['slotEvent'] == 'bet')
                {
                    $reels = $slotSettings->GetNoWinSpin($cmd, $postData['slotEvent']);
                    $manualNoWin = true;
                    $lineWins = [];
                    $totalWin = $gameWin;
                    $scatterCount = 0;
                    $freespinsWon = 0;
                }
                else
                {
                    //freespin
                    $reels = $minReels;
                    $lineWins = $minLineWins;
                    $totalWin = $minTotalWin;
                    $freespinsWon = $minFreespinWon;
                    $usedReels = $minUsedReels;
                    $syncReelsCount = $minSyncReelsCount;
                }
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
                'reelSet' => $reelName,
                'reels' => $slotSettings->GetReelSymbol($reels),
                'rpos' => [$reels['rp'][0] - 1, $reels['rp'][1] - 1, $reels['rp'][2] - 1, $reels['rp'][3] - 1, $reels['rp'][4] - 1, $reels['rp'][5] - 1],
                'usedReels' => $usedReels,
                'syncReelsCount' => $syncReelsCount,
                'wonCoins' => $coinWin,
                // 'wonMoney' => number_format($coinWin * $betLine, 2),
                'wtw' => $lineWins,
                'manualNoWin' => $manualNoWin
            ];

            if($postData['slotEvent'] == 'freespin')
            {
                //save last sync reel count
                $slotSettings->SetGameData($slotSettings->slotId . 'LastSyncedReelCount', $syncReelsCount);
            }

            if($postData['slotEvent'] == 'bet')
            {
                if($freespinsWon > 0)
                {
                    //triggers free spin
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $freespinsWon);
                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                    $postData['slotEvent'] = 'freespin';      
                    $eventData['freeSpins'] = $freespinsWon;
                    $eventData['freeSpinsAwarded'] = $freespinsWon;
                    $needRespin = true;
                }
            }
            else
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);
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
                if($totalWin > 0 || $postData['slotEvent'] == 'freespin')
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

        function getActiveSymbols($reels, $sym, $wild, $lines)
        {
            $rows = 5;
            $cols = 4;
                        
            $active = array_fill(0, $rows * $cols, 0);
            for($r = 0; $r < $lines; $r++)
                for($c = 0; $c < $cols; $c++)
                {
                    if($reels['reel'.($r+1)][$c] == $sym || $reels['reel'.($r+1)][$c] == $wild)
                        $active[$r * $cols + $c] = 1;
                }
            
            return implode("", $active);
        }

        function getMultiplier($reel, $sym, $wild)
        {
            $multiplier = 0;
            for($c = 0; $c < 4; $c++)
                if($reel[$c] == $sym || $reel[$c] == $wild)
                    $multiplier++;

            return $multiplier > 0 ? $multiplier : 1;
        }
    }

}


