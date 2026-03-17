<?php 
namespace VanguardLTE\Games\PenguinCityYGG
{
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;    

    class Server
    {
        public $gameState;
        public $debug = false;
        
        function getConvertedLine($csym, $reel, $wild, $len)
        {
            $line = array_fill(0, 15, 0);
            if($len > 0)
            {
                for($r = 0; $r < $len; $r++)
                    for($c = 0; $c < 3; $c++)
                    {
                        if($reel['reel'.($r+1)][$c] == $csym || in_array($reel['reel'.($r+1)][$c], $wild))
                            $line[$r * 3 + $c] = 1;
                    }
            }
            else
            {
                for($r = 4; $r >= 5 + $len; $r--)
                    for($c = 0; $c < 3; $c++)
                    {
                        if($reel['reel'.($r+1)][$c] == $csym || in_array($reel['reel'.($r+1)][$c], $wild))
                            $line[$r * 3 + $c] = 1;
                    }
            }
            return implode("", $line);
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
                        $response = file(base_path() . '/app/Games/PenguinCityYGG/translation.txt')[0];                                                                          
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
                        $response = '{"code":0,"data":{"id":"2203301519500100062","org":null,"gameid":0,"data":{"appsrv":"","file":"/init/","gameid":"7350","height":"600","license":"","org":"Demo","root":"","type":"Html","width":"800"}},"fn":"clientinfo","utcts":1648653590613}';
                        break;
                    case 'game':
                        $filename = base_path() . '/app/Games/PenguinCityYGG/game.txt';
                        $file = fopen($filename, "r" );
                        $filesize = filesize( $filename );
                        $response = fread( $file, $filesize );
                        fclose( $file );
                        break;
                    case 'restore':
                        $response = '{"code":0,"data":{"size":0,"next":"","data":[],"columns":[],"filterParams":{},"reportGenerationId":null,"header":[],"empty":true},"fn":"restore","utcts":'.time().'}';
                        $slotSettings->SetGameData($slotSettings->slotId . 'EscapeMode', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'LastWild', []);
                        $slotSettings->SetGameData($slotSettings->slotId . 'LastWildCount', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'LastEmperorShift', 0);
                        break;
                    case 'info':
                        $response = '{"code":0,"data":{"gameStatus":{"feature":null,"emperorShift":0,"coinValue":"0.00","preStickyWilds":[],"postStickyWilds":[],"previousEmperorShift":0}},"fn":"info","utcts":1649295870414}';
                        break;
                    case 'play':
                        $postData['slotEvent'] = 'bet';
                        $bonusMpl = 1;
                        $betLine = 0;
                        $nCoins = 25;
                        if(isset($postData['coin']))
                            $betLine = $postData['coin'];
                        $allbet = $betLine * $nCoins;
                        if( !isset($postData['slotEvent']) )
                        {
                            $postData['slotEvent'] = 'bet';
                        }
                        if($slotSettings->GetGameData($slotSettings->slotId . 'EscapeMode') > 0)
                            $postData['slotEvent'] = 'freespin';
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
                                                    'nCoins' => 1,
                                                    'restoredAccumulatedWonCoin' => $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin'),
                                                    'variant' => null,
                                                    'lines' => '1111111111'
                                                ],
                                                'eventdata' => [],
                                                'prizes' => [
                                                    [
                                                        'descr' => 'Cash out',
                                                        'gameId' => '8308',
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
                            return '{"completion":"Unknown","code":1006,"errorCode":"NO_SUFFICIENT_FUNDS","type":"O","rid":"220215083220::e14db45d-39e6-4cee-a076-ebb72ca0a89b","msg":"You do not have sufficient funds for the bet","fn":null,"details":null,"relaunchUrl":null,"timeElapsed":null,"errorType":null,"balanceDifference":null,"suppressed":[]}';
                        }

                        $slotSettings->SetGameData($slotSettings->slotId . 'CoinWin', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'BetCoin', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'Step', 1);
                        $slotSettings->SetBalance(-1 * $allbet, $postData['slotEvent']);
                        $bankSum = $allbet / 100 * $slotSettings->GetPercent();
                        $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $bankSum, $postData['slotEvent']);
                        $slotSettings->UpdateJackpots($allbet);
                        $slotSettings->SetBet($allbet);
                        
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
            {
                if($slotSettings->GetGameData($slotSettings->slotId . 'EscapeMode') == 1)
                    $reelName = 'PenguinReels';
                else 
                    $reelName = 'EmperorReels';
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bonus');                
            }
            else
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bet');
                $reelName = 'Reels';
            }

            $nCoins = 25;
            $betLine = 0;
            if(isset($postData['coin']))
                $betLine = $postData['coin'];
            $allbet = $betLine * $nCoins;
            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $nCoins);
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];            

            $spinAcquired = false;             
            $gameWin = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin');

            $minReels = [];
            $minLineWins = [];
            $minTotalWin = -1;            
            $minOrigReels = [];
            $minPenguinAction = [];
            $minIsEmperorAppeared = false;

            $totalWin = 0;
            $lineWins = [];
            $origReels = [];

            $wild = "Wild";
            $emperor_wild = "EMPEROR_WILD";
            $wilds = [$wild, $emperor_wild];
            $penguinAction = [];

            $needPenguinEscape = rand(0, 100) < 70 ? true : false;
            
            $isEmperorAppeared = false;

            for( $i = 0; $i <= 300; $i++ ) 
            {
                $isEmperorAppeared = false;
                $totalWin = 0;
                $lineWins = [];
                $penguinAction = [];
                $cWins = [
                    "High1" => 0,
                    "High2" => 0,
                    "High3" => 0,
                    "High4" => 0,
                    "High5" => 0,
                    "Low1" => 0,
                    "Low2" => 0,
                    "Low3" => 0,
                    "Low4" => 0                    
                ];
                
                if($this->debug /*&& $postData['slotEvent'] != 'freespin'*/)
                {                 
                    $winType = 'bonus';
                }
                $reels = $slotSettings->GetReelStrips($winType, $reelName);
                if($this->debug && $postData['slotEvent'] != 'freespin')
                {
                    $reels['reel1'] = ["Low1","Low1","Low4"];
                    $reels['reel2'] = ["Low2","High3","Wild"];
                    $reels['reel3'] = ["Low3","Low2","Low2"];
                    $reels['reel4'] = ["High5","High5","Low2"];
                    $reels['reel5'] = ["Low2","Low2","High1"];
                    $reels['rp'] = [82,81,35,141,54];
                }
                $origReels = $reels;
                if($postData['slotEvent'] == 'freespin')
                {
                    $lastWild = $slotSettings->GetGameData($slotSettings->slotId . 'LastWild');
                    
                    //keep wild cards during freespin
                    for($r = 0; $r < 5; $r++)
                    {
                        for ($c = 0; $c < 3; $c++)
                        {
                            $index = $r * 3 + $c;
                            if(in_array($index, $lastWild))
                            {
                                $reels['reel'.($r+1)][$c] = $wild;
                            }
                            if($reels['reel'.($r+1)][$c] == $emperor_wild)
                            {
                                $isEmperorAppeared = true;
                            }
                        }
                    }

                    //keep emperor wild shifted 
                    if($slotSettings->GetGameData($slotSettings->slotId . 'LastEmperorShift') > 0)
                    {
                        $emperorShift = $slotSettings->GetGameData($slotSettings->slotId . 'LastEmperorShift') - 1;
                        switch($emperorShift)
                        {
                            case 4:
                                $reels['reel3'][0] = $emperor_wild;
                                $reels['reel3'][1] = $emperor_wild;
                                break;
                            case 3:
                                $reels['reel3'][0] = $emperor_wild;
                                $reels['reel3'][1] = $emperor_wild;
                                $reels['reel3'][2] = $emperor_wild;
                                break;                                
                            case 2:
                                $reels['reel3'][1] = $emperor_wild;
                                $reels['reel3'][2] = $emperor_wild;
                                break;
                            case 1:
                                $reels['reel3'][2] = $emperor_wild;                                
                                break;
                        }
                        if($emperorShift > 0)
                            $isEmperorAppeared = true;
                    }
                }
                
                $bonusMpl = 1;                
                $isPenguinEscapped = false;
                
                for( $j = 0; $j <count($slotSettings->NonWildSymbol); $j++ ) 
                {
                    $mpl = 1;
                    $csym = $slotSettings->NonWildSymbol[$j];                    
                    $mul1 = $slotSettings->getMultiplier($reels['reel1'], $csym, $wilds);
                    $mul2 = $slotSettings->getMultiplier($reels['reel2'], $csym, $wilds);
                    $mul3 = $slotSettings->getMultiplier($reels['reel3'], $csym, $wilds);
                    $mul4 = $slotSettings->getMultiplier($reels['reel4'], $csym, $wilds);
                    $mul5 = $slotSettings->getMultiplier($reels['reel5'], $csym, $wilds);

                    if($postData['slotEvent'] == 'freespin' && $needPenguinEscape && !$isPenguinEscapped && !$isEmperorAppeared)
                    {
                        //penguin escape action, penguin escape position only possible on reel2,3,4
                        $diffSym = "";
                        
                        $changeRow = -1;
                        $changeCol = -1;
                        $multipliers = [$mul1, $mul2, $mul3, $mul4, $mul5];
                        for($m = 1; $m < 4; $m++)
                        {
                            switch($m)
                            {
                                case 1:
                                    if( ($multipliers[$m] < 3 && $mul1 > 0 && $mul3 > 0) || ($multipliers[$m] < 3 && $mul5 > 0 && $mul4 > 0 && $mul3 > 0))
                                    {
                                        $diffSym = "";
                                        for($c = 0; $c < 3; $c++)
                                        {
                                            if($reels['reel'.($m+1)][$c] != $csym && !in_array($reels['reel'.($m+1)][$c], $wilds))
                                            {
                                                if(!$this->checkWinLine($reels, $slotSettings, $reels['reel'.($m+1)][$c]))
                                                {
                                                    $diffSym = $reels['reel'.($m+1)][$c];
                                                    break;
                                                }
                                            }
                                        }
                                        if($diffSym != "")
                                        {
                                            $multipliers[$m]++;
                                            $changeRow = $m;
                                            $changeCol = $c;
                                        }
                                    }
                                    break;
                                case 2:
                                    if( ($multipliers[$m] < 3 && $mul1 > 0 && $mul2 > 0) || ($multipliers[$m] < 3 && $mul4 > 0 && $mul5 > 0))
                                    {
                                        $diffSym = "";
                                        for($c = 0; $c < 3; $c++)
                                        {
                                            if($reels['reel'.($m+1)][$c] != $csym && !in_array($reels['reel'.($m+1)][$c], $wilds))
                                            {
                                                if(!$this->checkWinLine($reels, $slotSettings, $reels['reel'.($m+1)][$c]))
                                                {
                                                    $diffSym = $reels['reel'.($m+1)][$c];
                                                    break;
                                                }
                                            }
                                        }
                                        if($diffSym != "")
                                        {
                                            $multipliers[$m]++;
                                            $changeRow = $m;
                                            $changeCol = $c;
                                        }
                                    }
                                    break;
                                case 3:
                                    if( ($multipliers[$m] < 3 && $mul5 > 0 && $mul3 > 0) || ($multipliers[$m] < 3 && $mul1 > 0 && $mul2 > 0 && $mul3 > 0))
                                    {
                                        $diffSym = "";
                                        for($c = 0; $c < 3; $c++)
                                        {
                                            if($reels['reel'.($m+1)][$c] != $csym && !in_array($reels['reel'.($m+1)][$c], $wilds))
                                            {
                                                if(!$this->checkWinLine($reels, $slotSettings, $reels['reel'.($m+1)][$c]))
                                                {
                                                    $diffSym = $reels['reel'.($m+1)][$c];
                                                    break;
                                                }
                                            }
                                        }
                                        if($diffSym != "")
                                        {
                                            $multipliers[$m]++;
                                            $changeRow = $m;
                                            $changeCol = $c;
                                        }
                                    }
                                    break;                                
                            }
                            if(($diffSym != "" && rand(0, 100) < 30) && $winType == 'win')
                            {
                                $penguinAction["iconPositionToChange"] = $changeRow * 3 + $changeCol;
                                $penguinAction["newIcon"] = $csym;
                                $reels['reel'.($changeRow + 1)][$changeCol] = $csym;
                                if(rand(0, 100) < 10)
                                {
                                    //avoid wild symbols on reel2,3 or reel 3,4 simultaneously
                                    $possible = true;
                                    switch($changeRow)
                                    {
                                        case 1:
                                        case 3:
                                            if( in_array($wild, $reels['reel3']))
                                                $possible = false;
                                            break;
                                        case 2:
                                            if( in_array($wild, $reels['reel2']) || in_array($wild, $reels['reel4']))
                                                $possible = false;
                                            break;                                                                                
                                    }
                                    if($possible)
                                    {
                                        $reels['reel'.($changeRow + 1)][$changeCol] = $wild;
                                        $penguinAction["newIcon"] = $wild;
                                    }
                                }
                                $isPenguinEscapped = true;

                                $mul1 = $multipliers[0];
                                $mul2 = $multipliers[1];
                                $mul3 = $multipliers[2];
                                $mul4 = $multipliers[3];
                                $mul5 = $multipliers[4];
                                break;
                            }                                
                        }
                    }

                    if($mul1 > 0 && $mul2 > 0 && $mul3 > 0) //from left to right 3 symbols contained
                    {
                        $mpl = $mul1 * $mul2 * $mul3;
                        $tmpWin = $slotSettings->Paytable[$csym][3] * $betLine * $mpl * $bonusMpl;
                        if($tmpWin > $cWins[$csym])
                        {
                            $cWins[$csym] = $tmpWin;
                            $winline = [$j + 1, $slotSettings->Paytable[$csym][3] * $mpl, $this->getConvertedLine($csym, $reels, $wilds, 3)];                        
                        }
                    }
                    if($mul1 > 0 && $mul2 > 0 && $mul3 > 0 && $mul4 > 0) //from left to right 4 symbols contained
                    {
                        $mpl = $mul1 * $mul2 * $mul3 * $mul4;
                        $tmpWin = $slotSettings->Paytable[$csym][4] * $betLine * $mpl * $bonusMpl;
                        if($tmpWin > $cWins[$csym])
                        {
                            $cWins[$csym] = $tmpWin;
                            $winline = [$j + 1, $slotSettings->Paytable[$csym][4] * $mpl, $this->getConvertedLine($csym, $reels, $wilds, 4)];                        
                        }
                    }
                    if($mul1 > 0 && $mul2 > 0 && $mul3 > 0 && $mul4 > 0 && $mul5 > 0) //from left to right 5 symbols contained
                    {
                        $mpl = $mul1 * $mul2 * $mul3 * $mul4 * $mul5;
                        $tmpWin = $slotSettings->Paytable[$csym][5] * $betLine * $mpl * $bonusMpl;
                        if($tmpWin > $cWins[$csym])
                        {
                            $cWins[$csym] = $tmpWin;
                            $winline = [$j + 1, $slotSettings->Paytable[$csym][5] * $mpl, $this->getConvertedLine($csym, $reels, $wilds, 5)];                        
                        }
                    }
                    if($mul1 == 0 && $mul2 > 0 && $mul3 > 0 && $mul4 > 0 && $mul5 > 0) //from right to left 4 symbols contained
                    {
                        $mpl = $mul2 * $mul3 * $mul4 * $mul5;
                        $tmpWin = $slotSettings->Paytable[$csym][4] * $betLine * $mpl * $bonusMpl;
                        if($tmpWin > $cWins[$csym])
                        {
                            $cWins[$csym] = $tmpWin;
                            $winline = [$j + 1, $slotSettings->Paytable[$csym][4] * $mpl, $this->getConvertedLine($csym, $reels, $wilds, -4)];
                        }
                    }
                    else if(($mul1 == 0 || $mul2 == 0) && $mul3 > 0 && $mul4 > 0 && $mul5 > 0) //from right to left 3 symbols contained
                    {
                        $mpl = $mul3 * $mul4 * $mul5;
                        $tmpWin = $slotSettings->Paytable[$csym][3] * $betLine * $mpl * $bonusMpl;
                        if($tmpWin > $cWins[$csym])
                        {
                            $cWins[$csym] = $tmpWin;
                            $winline = [$j + 1, $slotSettings->Paytable[$csym][3] * $mpl, $this->getConvertedLine($csym, $reels, $wilds, -3)];
                        }
                    }
                    
                    if($cWins[$csym] > 0 && !empty($winline))
                    {
                        array_push($lineWins, $winline);
                        $totalWin += $cWins[$csym];
                    }
                }
                
                $totalWin += $gameWin;         
                
                if($minTotalWin == -1 || ($minTotalWin > $totalWin && $totalWin > 0))
                {
                    $minTotalWin = $totalWin;
                    $minLineWins = $lineWins;                    
                    $minReels = $reels;
                    $minOrigReels = $origReels;
                    $minPenguinAction = $penguinAction;
                    $minIsEmperorAppeared = $isEmperorAppeared;
                }

                if($this->debug)
                {
                    $spinAcquired = true;
                    break;
                }                    

                if($totalWin <= $spinWinLimit && (($totalWin > 0 && $winType != 'none')))
                {
                    $spinAcquired = true;
                    if($totalWin < 0.5 * $spinWinLimit && $winType != 'bonus')
                        $spinAcquired = false;
                    if($spinAcquired)
                        break;                                        
                }           
                                
                else if( $winType == 'none' && $totalWin == $gameWin) 
                {
                    break;
                }
            }

            $manualNoWin = false;
            if(!$spinAcquired && $totalWin > $gameWin && $winType != 'none')
            {                
                if($postData['slotEvent'] == "freespin")
                {
                    $manualNoWin = true;           
                    $reels = $minReels;
                    $lineWins = $minLineWins;
                    $totalWin = $minTotalWin;
                    $origReels = $minOrigReels;
                    $penguinAction = $minPenguinAction;
                    $isEmperorAppeared = $minIsEmperorAppeared;
                }
                else
                {
                    $manualNoWin = true;
                    $reels = $slotSettings->GetNoWinSpin($postData['slotEvent']);
                    $lineWins = [];
                    $totalWin = $gameWin;
                }
            }

            //check if wild is included in winning line
            $activeSymbols = array_fill(0, 15, 0);
            
            $coinWin = 0; //coins won
            if(!empty($lineWins))
            {
                foreach($lineWins as $winline)
                {
                    $coinWin += $winline[1]; //sum up coins
                    for($i = 0; $i < 15; $i++)                        
                        $activeSymbols[$i] = $activeSymbols[$i] != 1 ? $winline[2][$i] : 1;
                }
            }

            $wildCount = 0;
            $wildPos = [];
            if($totalWin > 0)
            {
                for($r = 1; $r <= 5; $r++)
                    for($c = 0; $c < 3; $c++)
                    {
                        if($reels['reel'.$r][$c] == $wild && $activeSymbols[($r-1) * 3 + $c] == 1)
                        {
                            $wildCount++;
                            $wildPos[] = ($r - 1) * 3 + $c;
                        }
                    }    
            }

            $reels = $origReels;

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
                'rpos' => [$reels['rp'][0] - 1, $reels['rp'][1] - 1, $reels['rp'][2] - 1, $reels['rp'][3] - 1, $reels['rp'][4] - 1],
                'wonCoins' => $coinWin,
                'wonMoney' => number_format($coinWin * $betLine, 2),
                'wtw' => $lineWins,
                'manualNoWin' => $manualNoWin
            ];

            $eventData['emperorShift'] = 0;
            $eventData['newWildCount'] = 0;
            $eventData['penguinMode'] = false;
            $eventData['previousEmperorShift'] = 0;
            $eventData['postStickyWilds'] = [];
            $eventData['preStickyWilds'] = $slotSettings->GetGameData($slotSettings->slotId . 'LastWild');

            if($postData['slotEvent'] == 'bet')
            {
                if($wildCount > 0)
                {
                    $eventData['penguinModeEntered'] = true;
                    $eventData['penguinMode'] = true;
                    $eventData['postStickyWilds'] = $wildPos;
                    $eventData['newWildCount'] = $wildCount - $slotSettings->GetGameData($slotSettings->slotId . 'LastWildCount');
                    $slotSettings->SetGameData($slotSettings->slotId . 'EscapeMode', 1);
                }
            }
            else
            {
                $eventData['penguinMode'] = true;
                $eventData['penguinAction'] = false;
                $eventData['postStickyWilds'] = $wildPos;
                $eventData['newWildCount'] = $wildCount - $slotSettings->GetGameData($slotSettings->slotId . 'LastWildCount');

                if($totalWin == 0)
                {
                    $eventData['penguinMode'] = false;
                    $slotSettings->SetGameData($slotSettings->slotId . 'EscapeMode', 0);
                }
                else
                {
                    if(!empty($penguinAction))
                    {
                        $eventData['penguinAction'] = $penguinAction;
                    }
                }

                if($slotSettings->GetGameData($slotSettings->slotId . 'LastEmperorShift') == 0)
                {
                    if($isEmperorAppeared)
                    {
                        $slotSettings->SetGameData($slotSettings->slotId . 'EscapeMode', 2);
                        $emp = [0,0,0];
                        if($reels['reel3'][0] == $emperor_wild)
                            $emp[0] = 1;
                        if($reels['reel3'][1] == $emperor_wild)
                            $emp[1] = 1;
                        if($reels['reel3'][2] == $emperor_wild)
                            $emp[2] = 1;
                        $emp_code = implode("", $emp);
                        $emperorShift = 0;
                        if($emp_code == "100")
                            $emperorShift = 5;
                        if($emp_code == "110")
                            $emperorShift = 4;
                        if($emp_code == "111")
                            $emperorShift = 3;
                        if($emp_code == "011")
                            $emperorShift = 2;
                        if($emp_code == "001")
                            $emperorShift = 1;
                        
                        if($emperorShift == 1)
                            $slotSettings->SetGameData($slotSettings->slotId . 'EscapeMode', 1);
                        
                        $eventData['emperorShift'] = $emperorShift;
                        $slotSettings->SetGameData($slotSettings->slotId . 'LastEmperorShift', $emperorShift);                        
                    }
                }
                else
                {
                    $lastEmperorShift = $slotSettings->GetGameData($slotSettings->slotId . 'LastEmperorShift');
                    $emperorShift = $lastEmperorShift - 1;
                    $eventData['previousEmperorShift'] = $lastEmperorShift;
                    if($emperorShift == 1)
                        $slotSettings->SetGameData($slotSettings->slotId . 'EscapeMode', 1);
                    $slotSettings->SetGameData($slotSettings->slotId . 'LastEmperorShift', $emperorShift);
                    $eventData['emperorShift'] = $emperorShift;

                    if($emperorShift == 0)
                    {
                        //check if emperor wild is got immediately after 
                        if($isEmperorAppeared)
                        {
                            $slotSettings->SetGameData($slotSettings->slotId . 'EscapeMode', 2);
                            $emp = [0,0,0];
                            if($reels['reel3'][0] == $emperor_wild)
                                $emp[0] = 1;
                            if($reels['reel3'][1] == $emperor_wild)
                                $emp[1] = 1;
                            if($reels['reel3'][2] == $emperor_wild)
                                $emp[2] = 1;
                            $emp_code = implode("", $emp);
                            $emperorShift = 0;
                            if($emp_code == "100")
                                $emperorShift = 5;
                            if($emp_code == "110")
                                $emperorShift = 4;
                            if($emp_code == "111")
                                $emperorShift = 3;
                            if($emp_code == "011")
                                $emperorShift = 2;
                            if($emp_code == "001")
                                $emperorShift = 1;
                            
                            if($emperorShift == 1)
                                $slotSettings->SetGameData($slotSettings->slotId . 'EscapeMode', 1);
                            
                            $eventData['emperorShift'] = $emperorShift;
                            $slotSettings->SetGameData($slotSettings->slotId . 'LastEmperorShift', $emperorShift);                        
                        }
                    }
                }
            }

            if($totalWin > 0)
            {
                $this->gameState = 'Pending';
                $nextCmds[] = 'C';
            }
           
            $prizes = null;            

            $slotSettings->SetGameData($slotSettings->slotId . 'LastWildCount', $wildCount);
            $slotSettings->SetGameData($slotSettings->slotId . 'LastWild', $wildPos);

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

        function checkWinLine($reels, $slotSettings, $csym)
        {
            $wilds = ["Wild", "EMPEROR_WILD"];
            
            $mpl = 1;
            $win = 0;
                
            $mul1 = $slotSettings->getMultiplier($reels['reel1'], $csym, $wilds);
            $mul2 = $slotSettings->getMultiplier($reels['reel2'], $csym, $wilds);
            $mul3 = $slotSettings->getMultiplier($reels['reel3'], $csym, $wilds);
            $mul4 = $slotSettings->getMultiplier($reels['reel4'], $csym, $wilds);
            $mul5 = $slotSettings->getMultiplier($reels['reel5'], $csym, $wilds);

            if($mul1 > 0 && $mul2 > 0 && $mul3 > 0) //from left to right 3 symbols contained
            {
                $mpl = $mul1 * $mul2 * $mul3;
                $win = $slotSettings->Paytable[$csym][3] * $mpl;
            }
            if($mul1 > 0 && $mul2 > 0 && $mul3 > 0 && $mul4 > 0) //from left to right 4 symbols contained
            {
                $mpl = $mul1 * $mul2 * $mul3 * $mul4;
                $win = $slotSettings->Paytable[$csym][4] * $mpl;               
            }
            if($mul1 > 0 && $mul2 > 0 && $mul3 > 0 && $mul4 > 0 && $mul5 > 0) //from left to right 5 symbols contained
            {
                $mpl = $mul1 * $mul2 * $mul3 * $mul4 * $mul5;
                $win = $slotSettings->Paytable[$csym][5] * $mpl;
            }
            if($mul1 == 0 && $mul2 > 0 && $mul3 > 0 && $mul4 > 0 && $mul5 > 0) //from right to left 4 symbols contained
            {
                $mpl = $mul2 * $mul3 * $mul4 * $mul5;
                $win = $slotSettings->Paytable[$csym][4] * $mpl;                
            }
            else if(($mul1 == 0 || $mul2 == 0) && $mul3 > 0 && $mul4 > 0 && $mul5 > 0) //from right to left 3 symbols contained
            {
                $mpl = $mul3 * $mul4 * $mul5;
                $win = $slotSettings->Paytable[$csym][3] * $mpl;               
            }
            
            return $win > 0;
        }
    }

}


