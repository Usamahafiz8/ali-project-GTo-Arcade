<?php 
namespace VanguardLTE\Games\BoilingPotsYGG
{
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;    

    class Server
    {
        public $gameState;
        public $debug = false;
        public $lastReels;        

        function getPositions($csym, $reels, $cnt, $boardHeight)
        {
            $startRow = 7 - $boardHeight;
            $positions = [];
            for($r = 0; $r < $cnt; $r++)
                for($c = $startRow; $c < 7; $c++)
                {
                    if($reels['reel'.($r+1)][$c] == $csym || $reels['reel'.($r+1)][$c] == 'WILD')
                    {
                        $positions[] = ['x' => $r, 'y' => $c - $startRow];
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
                        $response = file(base_path() . '/app/Games/BoilingPotsYGG/translation.txt')[0];                                                                          
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
                        $response = '{"code":0,"data":{"id":"2203301519500100062","org":null,"gameid":0,"data":{"appsrv":"","file":"/init/","gameid":"7358","height":"600","license":"","org":"Demo","root":"","type":"Html","width":"800"}},"fn":"clientinfo","utcts":1648653590613}';
                        break;
                    case 'game':
                        $filename = base_path() . '/app/Games/BoilingPotsYGG/game.txt';
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
                        $nCoins = 20;
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
                            $postData['slotEvent'] = $slotSettings->GetGameData($slotSettings->slotId . 'LastEvent');
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
                            $slotSettings->SaveLogReport($response, $allbet, $reportWin, $postData['slotEvent']);                       
                            break;
                        }
                        if($cmd == 'BUY_BONUS')
                        {
                            $allbet = $postData['amount'];
                            $postData['slotEvent'] = 'buy_freespin';
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
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreespinCoinWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'BetCoin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'CoinBeforeFreespin', 0);
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
            $reelName = '';
            if($postData['slotEvent'] == 'freespin')
            {
                //freespin
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bonus');
                $reelName = 'bonusReels';
            }
            else
            {
                if($postData['slotEvent'] == 'respin')
                {
                    //respin
                    $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bet');
                    $reelName = 'respinReels'.rand(1,4).'_'.rand(1,2);
                }
                else
                {
                    //buy freespin or base spin
                    $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bet');
                    $reelName = 'baseReels'.rand(1,2);
                }
            }

            $nCoins = 20;
            $betLine = 0;
            if(isset($postData['coin']))
                $betLine = $postData['coin'];
            
            $allbet = $betLine * $nCoins;

            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $nCoins, $postData['slotEvent'] == 'buy_freespin');
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
            
            $spinAcquired = false;             
            $gameWin = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin');

            $minReels = [];
            $minLineWins = [];
            $minTotalWin = -1;

            $totalWin = 0;
            $freespinsWon = 0;            
            $lineWins = [];            
            $reels = [];

            $scatter = "SCATTER";
            $scatterCoin = 0;
            $bonusMpl = 1;
            $boardHeight = 3;

            if($postData['slotEvent'] == 'freespin' || $postData['slotEvent'] == 'respin')
            {
                $boardHeight = 7;    
            }
            
            $arrowCnt = 0;
            $respinAwarded = false;
            for( $i = 0; $i <= 300; $i++ ) 
            {
                $totalWin = 0;
                $respinAwarded = false;
                $freespinsWon = 0;                
                $lineWins = [];                
                $cWins = [
                    "SCATTER" => 0,
                    "WILD" => 0,
                    "ARROW" => 0,
                    "HIGH_1" => 0,
                    "HIGH_2" => 0,
                    "HIGH_3" => 0,
                    "HIGH_4" => 0,
                    "HIGH_5" => 0,
                    "LOW_1" => 0,
                    "LOW_2" => 0,
                    "LOW_3" => 0,
                    "LOW_4" => 0,
                    "LOW_5" => 0
                ];
                
                if($this->debug && $postData['slotEvent'] != 'freespin')
                {                 
                    $winType = 'bonus';
                }                                

                $reels = $slotSettings->GetReelStrips($winType, $reelName);
                $mpl = 1;
                $winline = [];
                
                if($postData['slotEvent'] != 'freespin')
                {
                    // if($this->debug && $postData['slotEvent'] != 'respin')
                    // {
                    //     $reels['reel1'][6] = 'ARROW';
                    //     $reels['reel2'][6] = 'ARROW';
                    //     $reels['reel3'][6] = 'ARROW';
                    //     $reels['reel4'][6] = 'ARROW';
                    // }
                    //check arrows
                    if($postData['slotEvent'] != 'respin')
                    {
                        $arrow_rand = rand(0, 100);

                        if($winType != 'win')
                            $arrow_rand = 0;
                        if($arrow_rand < 20)
                        {
                            $boardHeight = 3;
                        }
                        else if($arrow_rand < 75)
                        {
                            $boardHeight = 4;
                        }
                        else if($arrow_rand < 90)
                        {
                            $boardHeight = 5;
                        }
                        else if($arrow_rand < 98)
                        {
                            $boardHeight = 6;
                        }
                        else
                        {
                            $boardHeight = 7;
                        }

                        if($boardHeight > 3)
                        {
                            $c = rand(4, 6);
                            $r = rand(1, 5);
                            while($reels['reel'.$r][$c] == 'SCATTER' || $reels['reel'.$r][$c] == 'WILD')
                            {
                                $c = rand(4, 6);
                                $r = rand(1, 5);
                            }
                            $reels['reel'.$r][$c] = 'ARROW';

                            if($boardHeight > 4)
                            {
                                for($c = 3; $c > 7 - $boardHeight; $c--)
                                {
                                    $r = rand(1, 5);
                                    while($reels['reel'.$r][$c] == 'SCATTER' || $reels['reel'.$r][$c] == 'WILD')
                                    {
                                        $r = rand(1, 5);
                                    }
                                    $reels['reel'.$r][$c] = 'ARROW';
                                }
                            }
                        }
                    }

                    for( $j = 0; $j <count($slotSettings->SymbolGame); $j++ ) 
                    {
                        $mpl = 1;
                        $csym = $slotSettings->SymbolGame[$j];                    
                        $mul1 = $slotSettings->getMultiplier($reels['reel1'], $csym, $boardHeight);
                        $mul2 = $slotSettings->getMultiplier($reels['reel2'], $csym, $boardHeight);
                        $mul3 = $slotSettings->getMultiplier($reels['reel3'], $csym, $boardHeight);
                        $mul4 = $slotSettings->getMultiplier($reels['reel4'], $csym, $boardHeight);
                        $mul5 = $slotSettings->getMultiplier($reels['reel5'], $csym, $boardHeight);

                        if($mul1 > 0 && $mul2 > 0 && $mul3 > 0) //from left to right 3 symbols contained
                        {
                            $mpl = $mul1 * $mul2 * $mul3;
                            $tmpWin = $slotSettings->Paytable[$csym][3] * $betLine * $mpl * $bonusMpl;
                            if($tmpWin > $cWins[$csym])
                            {
                                $cWins[$csym] = $tmpWin;                            
                                $winline = [$j + 1, $slotSettings->Paytable[$csym][3] * $mpl * $bonusMpl, $mpl, $this->getPositions($csym, $reels, 3, $boardHeight), $csym, 3];
                            }
                        }
                        if($mul1 > 0 && $mul2 > 0 && $mul3 > 0 && $mul4 > 0) //from left to right 4 symbols contained
                        {
                            $mpl = $mul1 * $mul2 * $mul3 * $mul4;
                            $tmpWin = $slotSettings->Paytable[$csym][4] * $betLine * $mpl * $bonusMpl;
                            if($tmpWin > $cWins[$csym])
                            {
                                $cWins[$csym] = $tmpWin;
                                $winline = [$j + 1, $slotSettings->Paytable[$csym][4] * $mpl * $bonusMpl, $mpl, $this->getPositions($csym, $reels, 4, $boardHeight), $csym, 4];
                            }
                        }
                        if($mul1 > 0 && $mul2 > 0 && $mul3 > 0 && $mul4 > 0 && $mul5 > 0) //from left to right 5 symbols contained
                        {
                            $mpl = $mul1 * $mul2 * $mul3 * $mul4 * $mul5;
                            $tmpWin = $slotSettings->Paytable[$csym][5] * $betLine * $mpl * $bonusMpl;
                            if($tmpWin > $cWins[$csym])
                            {
                                $cWins[$csym] = $tmpWin;
                                $winline = [$j + 1, $slotSettings->Paytable[$csym][5] * $mpl * $bonusMpl, $mpl, $this->getPositions($csym, $reels, 5, $boardHeight), $csym, 5];
                            }
                        }
                        
                        if($cWins[$csym] > 0 && !empty($winline))
                        {
                            array_push($lineWins, $winline);
                            $totalWin += $cWins[$csym];
                        }
                    }
                }
                else
                {
                    $coin = 0;
                    //set blank to coins
                    for($r = 1; $r <= 5; $r++)
                        for($c = 0; $c < 7; $c++)
                        {
                            if(rand(0, 100) < 70)
                                $reels['reel'.$r][$c] = 'BLANK';
                        }
                    
                    for($r = 1; $r <= 5; $r++)
                    {
                        if($winType == 'none')
                            $reels['reel'.$r][3] = 'BLANK';
                        if($reels['reel'.$r][3] != 'BLANK')
                        {
                            $sym = $reels['reel'.$r][3];
                            
                            if(strpos($sym, "COINS_") !== false)
                            {
                                $mul = 1;
                                if($sym == 'COINS_1')
                                    $mul = 1;
                                else if($sym == 'COINS_2')
                                    $mul = 2;
                                else if($sym == 'COINS_3')
                                    $mul = 3;
                                else if($sym == 'COINS_4')
                                    $mul = 5;
                                else if($sym == 'COINS_5')
                                    $mul = 10;
                                else if($sym == 'COINS_6')
                                    $mul = 25;
                                else if($sym == 'COINS_7')
                                    $mul = 50;
                                $coin = $mul * $nCoins;
                            }
                            else if(strpos($sym, "JACKPOT_R") !== false)
                            {
                                switch($sym)
                                {
                                    case "JACKPOT_R1":
                                        $coin = $nCoins * 100;
                                        break;
                                    case "JACKPOT_R2":
                                        $coin = $nCoins * 200;
                                        break;
                                    case "JACKPOT_R3":
                                        $coin = $nCoins * 500;
                                        break;
                                    case "JACKPOT_R4":
                                        $coin = $nCoins * 1000;
                                        break;
                                    case "JACKPOT_R5":
                                        $coin = $nCoins * 5000;
                                        break;
                                }
                            }   
                            if($coin > 0)
                            {
                                $positions = [];
                                $positions[] = ['x' => $r - 1, 'y' => 3];
                                $winline = [0, $coin, $mpl, $positions, $sym, 1];
                                array_push($lineWins, $winline);
                                $totalWin += $betLine * $coin;
                            }                         
                        }
                    }
                }

                if($postData['slotEvent'] != 'freespin')
                {
                    //check wild respin
                    $arrowCnt = 0;
                    for($r = 1; $r <= 5; $r++)
                    {
                        for($c = 7 - $boardHeight; $c < 7; $c++)
                        {
                            if($reels['reel'.$r][$c] == 'ARROW')
                            {
                                $arrowCnt++;
                            }
                        }
                    }

                    //calc freespin
                    $scatterBase = 0;
                    
                    for($r = 1; $r <= 5; $r++)
                    {
                        for($c = 7 - $boardHeight; $c < 7; $c++)
                        {
                            if($reels['reel'.$r][$c] == $scatter)
                            {
                                $scatterBase++;
                            }
                        }
                    }

                    if($scatterBase > 2 && $winType != 'bonus')
                        continue;
                    if($scatterBase >= 3)
                    {
                        $freespinsWon = 3;
                        if($scatterBase == 3)
                            $scatterCoin = 0;//20;
                        else if($scatterBase == 4)
                            $scatterCoin = 10 * $nCoins;                        
                        else if($scatterBase >= 5)
                            $scatterCoin = 100 * $nCoins;                            
                        
                        if($scatterCoin > 0)
                        {
                            $winline = [0, $scatterCoin, 1, $this->getPositions($scatter, $reels, 5, $boardHeight), $scatter, 5];
                            array_push($lineWins, $winline);
                        }
                    }
                    else
                    {
                        if($arrowCnt >= 4)
                            $respinAwarded = true;
                        if($postData['slotEvent'] == 'respin' && $totalWin > 0)
                            $respinAwarded = true;
                    }
                }

                $totalWin += $gameWin;
                $totalWin += $scatterCoin * $betLine;

                if($minTotalWin == -1 || ($minTotalWin > $totalWin && $totalWin > 0))
                {
                    $minTotalWin = $totalWin;
                    $minLineWins = $lineWins;
                    $minReels = $reels;
                }

                if($this->debug)
                {
                    $spinAcquired = true;
                    break;
                }                    

                if($totalWin <= $spinWinLimit && (($totalWin > 0 && $winType != 'none') || ($winType == 'bonus' && $scatterBase > 2)))
                {
                    $spinAcquired = true;
                    if($postData['slotEvent'] == 'bet' && $totalWin < 0.7 * $spinWinLimit && $winType != 'bonus')
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
                $lineWins = $minLineWins;
                $totalWin = $minTotalWin;           
            }

            $this->lastReels = $reels;           
            
            $coinWin = 0; //coins won
            $prizes = [];
            if(!empty($lineWins))
            {
                foreach($lineWins as $winline)
                {
                    $coinWin += $winline[1]; //sum up coins
                    $prizes[] = [
                        'cash' => number_format($winline[1] * $betLine, 2),
                        'coins' => $winline[1],
                        'oak' => $winline[5],
                        'positions' => $winline[3],
                        'symbol' => $winline[4],
                        'ways' => $winline[2]
                    ];
                }
                if($postData['slotEvent'] == 'freespin')
                    $freespinsWon = 1;
            }

            $slotSettings->SetGameData($slotSettings->slotId . 'CoinWin', $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') + $coinWin);
            $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', $totalWin);
            $slotSettings->SetGameData($slotSettings->slotId . 'BetCoin', $betLine);
            //nextCmds
            $nextCmds = [];
            
            $needRespin = false;
            $reelOffset = 7 - $boardHeight;
            $eventData = [
                'accC' => $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin'),
                'accWa' => number_format($slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') * $betLine, 2),
                'rpos' => [$reels['rp'][0] - 1, $reels['rp'][1] - 1, $reels['rp'][2] - 1, $reels['rp'][3] - 1, $reels['rp'][4] - 1],                
                'manualNoWin' => $manualNoWin,
                'response' => [
                    'cashWin' => number_format($coinWin * $betLine, 2),
                    'clientData' => [
                        'boardHeight' => $boardHeight,
                        'bonusGameSpinAwarded' => false,
                        'excessiveScattersWinInCoins' => '0',
                        'finalBoard' => $this->getFinalReels($reels, $boardHeight),
                        'gameMode' => 'BASE_GAME',
                        'reels' => [
                            'reelSetName' => $reelName,
                            'reelStopPositions' => [$reels['rp'][0] + $reelOffset, $reels['rp'][1] + $reelOffset, $reels['rp'][2] + $reelOffset, $reels['rp'][3] + $reelOffset, $reels['rp'][4] + $reelOffset],
                        ],
                        'reSpinAwarded' => false,
                        'win' => [
                            'accumulatedWonCash' => number_format($slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') * $betLine, 2),
                            'accumulatedWonCoins' => $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin'),
                            'wonCash' => number_format($coinWin * $betLine, 2),
                            'wonCoins' => $coinWin,
                            'prizes' => $prizes
                        ]
                        ],
                        'coinWin' => $coinWin
                ]
            ];

            if($postData['slotEvent'] != 'freespin')
            {
                if($freespinsWon > 0)
                {
                    //trigger freespin
                    $eventData['response']['clientData']['bonusGameSpinAwarded'] = true;
                    $this->gameState = 'Pending';
                    $postData['slotEvent'] = 'freespin';
                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                    $slotSettings->SetGameData($slotSettings->slotId . 'CoinBeforeFreespin', $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin'));
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $freespinsWon);
                    $needRespin = true;
                }
                else
                {
                    if($respinAwarded)
                    {
                        $eventData['response']['clientData']['reSpinAwarded'] = true;
                        if($postData['slotEvent'] == 'respin')
                            $eventData['response']['clientData']['gameMode'] = 'RE_SPIN';
                        else
                            $postData['slotEvent'] = 'respin';
                        $needRespin = true;                        
                    }
                    else
                    {
                        if($postData['slotEvent'] == 'respin')
                        {
                            $eventData['response']['clientData']['reSpinAwarded'] = false;
                            $eventData['response']['clientData']['gameMode'] = 'RE_SPIN';
                        }
                    }
                }
            }
            else
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'FreespinCoinWin', $slotSettings->GetGameData($slotSettings->slotId . 'FreespinCoinWin') + $coinWin);
                $eventData['response']['clientData']['numberOfLivesBefore'] = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);
                if($freespinsWon > 0)
                {
                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames',  3);
                }
                $freespinLeft = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                $eventData['response']['clientData']['gameMode'] = 'BONUS_GAME';
                $eventData['response']['clientData']['numberOfLivesAfter'] = $freespinLeft;
                if($freespinLeft > 0)
                {
                   $needRespin = true;
                }
                else
                {
                    
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
                'nCoins' => 10,
                'cheat' => null,
                'clientData' => null,                
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

        

        function hasArrow($reels, $c)
        {
            if($reels['reel1'][$c] == 'ARROW' || $reels['reel2'][$c] == 'ARROW' || $reels['reel3'][$c] == 'ARROW' || $reels['reel4'][$c] == 'ARROW' || $reels['reel5'][$c] == 'ARROW')
                return true;
            else 
                return false;
        }

        function getFinalReels($reels, $boardHeight)
        {
            $reelSyms = [];
            foreach($reels as $index => $value)
            {
                if(strpos($index, 'reel') !== false)
                {
                    $reel = [];
                    for($c = 7 - $boardHeight; $c < 7; $c++)
                    {
                        $reel[] = $value[$c];
                    }
                    $reelSyms[] = $reel;
                }
            }
            return $reelSyms;
        }
    }

}


