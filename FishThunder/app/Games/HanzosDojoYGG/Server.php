<?php 
namespace VanguardLTE\Games\HanzosDojoYGG
{
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;    

    class Server
    {
        public $gameState;
        public $debug = false;
        public $freespinMode;   //0: mega reel, 1: wild seed, 2: nudge, 3: mega + wild, 4: wild + nudge, 5: mega + nudge, 6: mega + wild + nudge
        public $miniFreespin; //0: mega reel, 1: wild seed, 2: nudge
        public $mini = ['TripReel', 'WildSeed', 'Nudge'];
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
                        $response = file(base_path() . '/app/Games/HanzosDojoYGG/translation.txt')[0];                                                                          
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
                        $response = '{"code":0,"data":{"id":"2203301519500100062","org":null,"gameid":0,"data":{"appsrv":"","file":"/init/","gameid":"7351","height":"600","license":"","org":"Demo","root":"","type":"Html","width":"800"}},"fn":"clientinfo","utcts":1648653590613}';
                        break;
                    case 'game':
                        $filename = base_path() . '/app/Games/HanzosDojoYGG/game.txt';
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
                        $nCoins = 25;
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
                            $slotSettings->SetGameData($slotSettings->slotId . 'CurrentGods', []);
                            $slotSettings->SetGameData($slotSettings->slotId . 'BetLine', $betLine);
                            $slotSettings->SetGameData($slotSettings->slotId . 'Sticky', array_fill(0, 15, 0));
                            $slotSettings->SetGameData($slotSettings->slotId . 'SpinFeature', []);
                            $slotSettings->SetGameData($slotSettings->slotId . 'BoostFeature', []);
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
            $linesId = $slotSettings->GetPaylines();

            if($postData['slotEvent'] == 'freespin' || $postData['slotEvent'] == 'minifreespin')
            {
                $reelName = 'FreespinReels';
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bonus');
            }
            else
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bet');
                $reelName = 'Reels';
            }

            $lines = count($linesId);
            $nCoins = 25;
            $betLine = 0;
            if(isset($postData['coin']))
                $betLine = $postData['coin'];            
            
            $allbet = $betLine * $nCoins;
            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $nCoins);
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
            if($postData['slotEvent'] == 'minifreespin')
            {
                $winType = 'win';
                $spinWinLimit = $slotSettings->GetBank('bonus');
            }
            $spinAcquired = false;             
            $gameWin = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin');

            $minReels = [];
            $minLineWins = [];
            $minTotalWin = -1;
            $minFreespinsWon = 0;
            $minReels0 = [];
            $minNudgeValue = 0;
            $minBoostFeatures = [];
            $minExpandWildFeatures = [];
            $minNewFeatures = [];

            $totalWin = 0;
            $freespinsWon = 0;
            $miniFreespinsWon = 0;
            $lineWins = [];
            $specialWin = "";
            $addedWilds = [];
            $reels0 = [];
            $reels = [];
            $lastSpinFeatures = $slotSettings->GetGameData($slotSettings->slotId . 'SpinFeature');
            $spinFeatures = []; //spin features            
            $expandWildFeatures = [];
            $newFeatures = [];
            
            $scatter = "Freespin";
            $wild = ["Wild"];
            $nudgeValue = 0;
            $nudgeSpin = false;
            for( $i = 0; $i <= 500; $i++ ) 
            {
                $nudgeValue = 0;
                $payBothWay = false;
                $nudgeSpin = false;
                $spinFeatures = [];
                $newFeatures = [];
                $totalWin = 0;
                $freespinsWon = 0;
                $lineWins = [];
                $expandWildFeatures = [];
                $miniFreespinsWon = 0;
                $cWins = array_fill(0, $lines, 0);

                $boostFeatures = $slotSettings->GetGameData($slotSettings->slotId . 'BoostFeature');
                
                if($this->debug && $postData['slotEvent'] != 'freespin' && $postData['slotEvent'] != 'minifreespin')
                {                 
                    $winType = 'bonus';
                }

                $reels = $slotSettings->GetReelStrips($winType, $reelName, $postData['slotEvent']);
                $reels0 = $reels;                
                $bonusMpl = 1;

                if($postData['slotEvent'] == 'minifreespin')
                {
                    foreach($lastSpinFeatures as $spinFeature)
                    {
                        if($spinFeature['name'] == 'LockedCollosalSymbol')
                        {
                            $lockedSym = $spinFeature['lockedSymbol'];
                            for($r = 2; $r <= 4; $r++)   
                                for($c = 0; $c < 3; $c++)
                                {
                                    $reels['reel'.$r][$c] = $lockedSym;
                                    $reels0['reel'.$r][$c] = $lockedSym;
                                }
                            $reels['rp'][1] = 1;
                            $reels['rp'][2] = 1;
                            $reels['rp'][3] = 1;
                        }
                    }

                    $featureFreespin = 0;

                    if($this->debug && $slotSettings->GetGameData($slotSettings->slotId . 'Step') > 2)
                    {
                        $reels['sensei'] = ['Blank', 'Blank', 'Blank'];
                    }

                    for($r = 0; $r < 3; $r++)
                    {
                        if($reels['sensei'][$r] == 'FeatureFreespin')
                            $featureFreespin += 1;
                    }
                    
                    //when hanzo spin, the won boost feature is applied from the winning spin itself, so must be added to boostFeatures
                    if($featureFreespin > 0)
                    {
                        $miniFreespinsWon++;
                        //get 1 boost feature
                        $boostWin = $this->getBoostFeature($boostFeatures, $postData['slotEvent']);
                        switch($boostWin)
                        {
                            case 'CollosalNudge':
                                
                                break;
                            case 'Wild5thReel':
                                $boostFeatures[] = ['name'=>'Wild5thReel'];
                                $newFeatures[] = ['name'=>'Wild5thReel'];
                                break;
                            case 'DoubleMultiplier':
                                $boostFeatures[] = ['name'=>'DoubleMultiplier'];
                                $newFeatures[] = ['name'=>'DoubleMultiplier'];
                                break;
                            case 'PayBothWays':
                                $boostFeatures[] = ['name'=>'PayBothWays'];
                                $newFeatures[] = ['name'=>'PayBothWays'];
                                break;
                        }
                    }                    
                }

                if($postData['slotEvent'] != 'bet')
                    $reels['senseirp'] = [0,0,0];

                foreach($boostFeatures as $boostFeature)
                {
                    switch($boostFeature['name'])
                    {
                        case 'CollosalNudge':
                            $nudgeSpin = true;
                            break;
                        case 'Wild5thReel':
                            $reels['reel5'][0] = $wild[0];
                            $reels['reel5'][1] = $wild[0];
                            $reels['reel5'][2] = $wild[0];
                            $reels0['reel5'][0] = $wild[0];
                            $reels0['reel5'][1] = $wild[0];
                            $reels0['reel5'][2] = $wild[0];
                            break;
                        case 'DoubleMultiplier':
                            $bonusMpl = 2;
                            break;
                        case 'PayBothWays':
                            $payBothWay = true;
                            break;
                    }
                }

                if($nudgeSpin)
                {
                     //check max nudge possibility
                    //'reelChange=0'
                    $symbols = ['High1', 'High2', 'High3', 'High4', 'Low1', 'Low2', 'Low3', 'Low4'];
                    $nudgeSymbol = '';
                    $pattern = [];
                    foreach($symbols as $symbol)                            
                    {
                        if( (in_array($symbol, $reels['reel1']) && in_array($symbol, $reels['reel2'])))
                        {
                            $nudgeSymbol = $symbol;
                            foreach($reels['reel2'] as $sym)
                            {
                                if($sym == $nudgeSymbol)
                                    $pattern[] = 1;
                                else
                                    $pattern[] = 0;
                            }
                            break;
                        }
                        if(($payBothWay && in_array($symbol, $reels['reel4']) && in_array($symbol, $reels['reel5'])))
                        {
                            $nudgeSymbol = $symbol;
                            foreach($reels['reel4'] as $sym)
                            {
                                if($sym == $nudgeSymbol)
                                    $pattern[] = 1;
                                else
                                    $pattern[] = 0;
                            }
                            break;
                        }
                    }

                    $patternCode = implode('', $pattern);                    
                    
                    switch($patternCode)
                    {
                        case '100':
                            $nudgeValue = -2;
                            break;
                        case '110':
                            $nudgeValue = -1;
                            break;
                        case '111':
                            $nudgeValue = 0;
                            break;
                        case '011':
                            $nudgeValue = 1;
                            break;
                        case '001':
                            $nudgeValue = 2;
                            break;
                        default:
                            $nudgeValue = 0;
                            break;
                    }

                    if($nudgeSymbol == '')
                        $nudgeValue = 0;
                    //adjust reel according to nudge value
                    if($nudgeValue != 0)
                    {
                        for($r = 2; $r <= 4; $r++)
                            for($c = 0; $c < 3; $c++)
                                $reels['reel'.$r][$c] = $nudgeSymbol;
                    }
                }

                //check expanding wild on hanzo 
                for($r = 0; $r < 3; $r++)
                {
                    if($reels['sensei'][$r] == 'ExpandingWild')
                    {
                        $reels['reel'.($r+2)][0] = $wild[0];
                        $reels['reel'.($r+2)][1] = $wild[0];
                        $reels['reel'.($r+2)][2] = $wild[0];
                        if(count($expandWildFeatures) == 0)
                        {
                            $expandWildFeatures[] = ['name'=>'ExpandingWilds', 'wildReels' => [$r+1]];
                        }
                        else
                        {
                            $expandWildFeatures[0]['wildReels'][] = $r+1;
                        }
                    }
                }
                
                for( $k = 0; $k < $lines; $k++ ) 
                {
                    $mpl = 1;                    

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

                            $s[0] = $reels['reel1'][$p0];
                            $s[1] = $reels['reel2'][$p1];
                            $s[2] = $reels['reel3'][$p2];
                            $s[3] = $reels['reel4'][$p3];
                            $s[4] = $reels['reel5'][$p4];                            
                                                                                
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) ) 
                            {
                                $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
                                $tmpWin = $slotSettings->Paytable[$csym][3] * $betLine * $mpl * $bonusMpl;
                                $coin = $slotSettings->Paytable[$csym][3] * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $cWins[$k] = $tmpWin;
                                    $emptyLine[0][$p0] = 1;
                                    $emptyLine[1][$p1] = 1;
                                    $emptyLine[2][$p2] = 1;
                                    $winline = [$k + 1, $coin, $this->getConvertedLine($emptyLine)]; //[lineId, coinWon, winPositions]
                                }
                            }
                            if($payBothWay)
                            {
                                //pay both way for 3 symbols
                                if( ($s[4] == $csym || in_array($s[4], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) ) 
                                {
                                    $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
                                    $tmpWin = $slotSettings->Paytable[$csym][3] * $betLine * $mpl * $bonusMpl;
                                    $coin = $slotSettings->Paytable[$csym][3] * $mpl * $bonusMpl;
                                    if( $cWins[$k] < $tmpWin ) 
                                    {
                                        $cWins[$k] = $tmpWin;
                                        $emptyLine[4][$p4] = 1;
                                        $emptyLine[3][$p3] = 1;
                                        $emptyLine[2][$p2] = 1;
                                        $winline = [$k + 1, $coin, $this->getConvertedLine($emptyLine)]; //[lineId, coinWon, winPositions]
                                    }
                                }
                            }


                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) ) 
                            {
                                $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
                                $tmpWin = $slotSettings->Paytable[$csym][4] * $betLine * $mpl * $bonusMpl;
                                $coin = $slotSettings->Paytable[$csym][4] * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $cWins[$k] = $tmpWin;
                                    $emptyLine[0][$p0] = 1;
                                    $emptyLine[1][$p1] = 1;
                                    $emptyLine[2][$p2] = 1;
                                    $emptyLine[3][$p3] = 1;
                                    $winline = [$k + 1, $coin, $this->getConvertedLine($emptyLine)]; //[lineId, coinWon, winPositions]                                                             
                                }
                            }
                            if($payBothWay)
                            {
                                //pay both way for 4 symbols
                                if( ($s[4] == $csym || in_array($s[4], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) ) 
                                {
                                    $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
                                    $tmpWin = $slotSettings->Paytable[$csym][4] * $betLine * $mpl * $bonusMpl;
                                    $coin = $slotSettings->Paytable[$csym][4] * $mpl * $bonusMpl;
                                    if( $cWins[$k] < $tmpWin ) 
                                    {
                                        $cWins[$k] = $tmpWin;
                                        $emptyLine[4][$p4] = 1;
                                        $emptyLine[3][$p3] = 1;
                                        $emptyLine[2][$p2] = 1;
                                        $emptyLine[1][$p1] = 1;
                                        $winline = [$k + 1, $coin, $this->getConvertedLine($emptyLine)]; //[lineId, coinWon, winPositions]                                                             
                                    }
                                }
                            }


                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) ) 
                            {
                                $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
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
                                    $winline = [$k + 1, $coin, $this->getConvertedLine($emptyLine)]; //[lineId, coinWon,winPositions]                                                            
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
                
                $totalWin += $gameWin;

                //calc freespin
                $scatterBase = 0;
                
                for($r = 1; $r <= 5; $r++)
                {
                    for($c = 0; $c < 3; $c++)
                    {
                        if($reels['reel'.$r][$c] == $scatter)
                        {
                            $scatterBase++;
                        }
                    }
                }

                if($scatterBase > 0 && $winType != 'bonus')
                    continue;
                                
                //check hanzo spin in normal spin
                $reveal = 0;                
                if($postData['slotEvent'] == 'bet')
                {
                    //check hanzo spin win in normal spin
                    for($r = 0; $r < 3; $r++)
                    {
                        if($reels['sensei'][$r] == 'Reveal')
                            $reveal++;                    
                    }

                    if($reveal == 3)
                    {
                        //activate hanzo spin when 3 hanzo symbols appeared in rooftop when normal bet
                        if($winType != 'bonus')
                            continue;
                        
                        $spinFeatures[] = ['lockedSymbol'=>$reels['senseiSym'], 'name'=>'LockedCollosalSymbol'];
                        $miniFreespinsWon = 3;
                    }                    
                }

                //check freespin win 
                if($postData['slotEvent'] == 'bet' || $postData['slotEvent'] == 'freespin')
                {
                    if($scatterBase > 0)
                    {
                        if($reveal == 3) //disable 3 hanzo symbol win and freespin win together
                            continue;
                        $freespinsWon = 3 * $scatterBase;
                        $spinFeatures[] = ['name'=>'CollosalReel'];
                    }

                    $featureFreespin = 0;

                    if($this->debug && $slotSettings->GetGameData($slotSettings->slotId . 'Step') > 2)
                    {
                        $reels['sensei'] = ['Blank', 'Blank', 'Blank'];
                    }

                    for($r = 0; $r < 3; $r++)
                    {
                        if($reels['sensei'][$r] == 'FeatureFreespin')
                            $featureFreespin += 1;                        
                    }

                    if($postData['slotEvent'] == 'bet' && $featureFreespin > 0 && $scatterBase == 0) //in normal bet, rooftop freespin symbol without 5x3 freespin symbol cannot trigger freespin
                        continue;
                    
                    
                    //when hanzo spin, the won boost feature is applied from the winning spin itself, so must be added to boostFeatures
                    if($featureFreespin > 0)
                    {
                        //get 1 boost feature
                        $boostWin = $this->getBoostFeature($boostFeatures, $postData['slotEvent']);
                        switch($boostWin)
                        {
                            case 'CollosalNudge':
                                $boostFeatures[] = ['name'=>'CollosalNudge'];
                                $newFeatures[] = ['name'=>'CollosalNudge'];                                
                                break;
                            case 'Wild5thReel':
                                $boostFeatures[] = ['name'=>'Wild5thReel'];
                                $newFeatures[] = ['name'=>'Wild5thReel'];
                                break;
                            case 'DoubleMultiplier':
                                $boostFeatures[] = ['name'=>'DoubleMultiplier'];
                                $newFeatures[] = ['name'=>'DoubleMultiplier'];
                                break;
                            case 'PayBothWays':
                                $boostFeatures[] = ['name'=>'PayBothWays'];
                                $newFeatures[] = ['name'=>'PayBothWays'];
                                break;
                        }
                    }

                    if($postData['slotEvent'] == 'freespin')
                        $freespinsWon += $featureFreespin;                    
                    else
                        $freespinsWon += $featureFreespin * 4;
                }
                if($freespinsWon > 0 && $winType != 'bonus')
                    continue;

                if($minTotalWin == -1 || ($minTotalWin > $totalWin && $totalWin > 0))
                {
                    $minTotalWin = $totalWin;
                    $minLineWins = $lineWins;
                    $minFreespinsWon = $freespinsWon;
                    $minReels = $reels;
                    $minReels0 = $reels0;
                    $minNudgeValue = $nudgeValue;
                    $minExpandWildFeatures = $expandWildFeatures;
                    $minNewFeatures = $newFeatures;
                    $minBoostFeatures = $boostFeatures;
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
                if($postData['slotEvent'] == "freespin" || $postData['slotEvent'] == "minifreespin")
                {
                    $manualNoWin = true;                
                    $reels = $minReels;
                    $lineWins = $minLineWins;
                    $totalWin = $minTotalWin;
                    $freespinsWon = $minFreespinsWon;                    
                    $reels0 = $minReels0;
                    $nudgeValue = $minNudgeValue;
                    $boostFeatures = $minBoostFeatures;
                    $expandWildFeatures = $minExpandWildFeatures;
                    $newFeatures = $minNewFeatures;
                }
                else
                {
                    $manualNoWin = true;
                    $reels = $slotSettings->GetNoWinSpin($reelName);
                    $reels0 = $reels;
                    $lineWins = [];
                    $totalWin = $gameWin;                    
                    $freespinsWon = 0;   
                    $nudgeValue = 0;
                    $boostFeatures = [];              
                }
            }

            $coinWin = 0; //coins won
            
            $activeSymbols = array_fill(0, 15, 0);
            $activeLines = array_fill(0, $lines + 1, 0);
            if(!empty($lineWins))
            {
                foreach($lineWins as $winline)
                {
                    $coinWin += $winline[1]; //sum up coins
                    $activeLines[$winline[0]] = 1;
                    $winsyms = $winline[2];
                    for($c = 0; $c < 15; $c++)
                        if($winsyms[$c] == 1)
                            $activeSymbols[$c] = 1;
                }
            }

            //calculate sticky variables
            $stickyB = $slotSettings->GetGameData($slotSettings->slotId . 'Sticky');
            $stickyA = $stickyB;
            $stickyN = array_fill(0, 15, 0);

            foreach($spinFeatures as $spinFeature)
            {
                if($spinFeature['name'] == 'LockedCollosalSymbol')
                {
                    for($i = 3; $i < 12; $i++)
                        $stickyA[$i] = 1;
                }                
            }

            foreach($lastSpinFeatures as $spinFeature)
            {
                if($spinFeature['name'] == 'LockedCollosalSymbol')
                {
                    for($i = 3; $i < 12; $i++)
                        $stickyA[$i] = 1;
                }
            }

            foreach($boostFeatures as $index => $boostFeature)
            {
                if($boostFeature['name'] == 'Wild5thReel')
                {
                    $stickyA[12] = 1;
                    $stickyA[13] = 1;
                    $stickyA[14] = 1;
                }          
                else if($boostFeature['name'] == 'CollosalNudge')      
                {
                    $boostFeatures[$index]['reelChange'] = $nudgeValue;
                }
            }

            $slotSettings->SetGameData($slotSettings->slotId . 'Sticky', $stickyA);
            for($i = 0; $i < 15; $i++)
            {
                if($stickyB[$i] != $stickyA[$i])
                    $stickyN[$i] = 1;
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
                'finalReels' => $slotSettings->GetReelSymbol($reels),
                'reels' => $slotSettings->GetReelSymbol($reels0),                
                'rpos' => [$reels['rp'][0] - 1, $reels['rp'][1] - 1, $reels['rp'][2] - 1, $reels['rp'][3] - 1, $reels['rp'][4] - 1],
                'wonCoins' => $coinWin,
                'wonMoney' => number_format($coinWin * $betLine, 2),
                'wtw' => $lineWins,
                'manualNoWin' => $manualNoWin,
                'senseiSector' => $reels['sensei'],
                'senseiSectorRpos' => $reels['senseirp'],
                'useSenseiSectorReels' => true,
                'stickyA' => implode('', $stickyA),
                'stickyB' => implode('', $stickyB),
                'stickyN' => implode('', $stickyN),
                'currentFeatures' => [],
                'wonFeatures' => [],
                'wts' => [implode('', $activeLines), implode('', $activeSymbols)]
            ];

            if(count($spinFeatures) > 0)
            {
                $eventData['wonFeatures'] = $spinFeatures;
                $slotSettings->SetGameData($slotSettings->slotId . 'SpinFeature', $spinFeatures);
            }

            if(count($boostFeatures) > 0)
                $eventData['currentFeatures'] = $boostFeatures;            
            if(count($newFeatures) > 0)
                $eventData['wonFeatures'] = array_merge($eventData['wonFeatures'], $newFeatures);
            if(count($expandWildFeatures) > 0)            
                $eventData['currentFeatures'] = $expandWildFeatures;
            
            
            $slotSettings->SetGameData($slotSettings->slotId . 'BoostFeature', $boostFeatures);
            
            if($postData['slotEvent'] == 'bet')
            {
                if($freespinsWon > 0)
                {
                    $eventData['freeSpins'] = $freespinsWon;
                    $eventData['freeSpinsAwarded'] = $freespinsWon;
                    $postData['slotEvent'] = 'freespin';

                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $freespinsWon);
                    
                    $needRespin = true;
                }
                if($miniFreespinsWon > 0)
                {
                    $eventData['freeSpins'] = $miniFreespinsWon;
                    $eventData['freeSpinsAwarded'] = $miniFreespinsWon;
                    $postData['slotEvent'] = 'minifreespin';

                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $miniFreespinsWon);
                    $needRespin = true;
                }

                $eventData['currentMode'] = 'BaseGame';
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

                if($freespinsWon > 0)
                {
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames',  $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') + $freespinsWon);                    
                    $eventData['freeSpinsAwarded'] = $freespinsWon;
                }

                if($miniFreespinsWon > 0)
                {
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames',  $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') + $miniFreespinsWon);                    
                    $eventData['freeSpinsAwarded'] = $miniFreespinsWon;
                }

                if($postData['slotEvent'] == 'freespin')
                {
                    $eventData['currentMode'] = 'Freespins';
                    $eventData['useSenseiSectorReels'] = false;
                }
                else if($postData['slotEvent'] == 'minifreespin')
                    $eventData['currentMode'] = 'MiniFreespins';
            }

            if($needRespin)
            {
                $this->gameState = 'Pending';
            }
            else
            {
                if($totalWin > 0 && empty($nextCmds))
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
                'nCoins' => 25,
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

        function getBoostFeature($boostFeatures, $type)
        {
            $boost = ['CollosalNudge', 'Wild5thReel', 'DoubleMultiplier', 'PayBothWays'];
            $alreadyAdded = [];
            if($type == 'minifreespin')
                $alreadyAdded[] = 'CollosalNudge';
            
            foreach($boostFeatures as $boostFeature)
            {
                if(in_array($boostFeature['name'], $boost))
                {
                    $alreadyAdded[] = $boostFeature['name'];
                }
            }

            $available = [];
            foreach($boost as $name)
            {
                if(!in_array($name, $alreadyAdded))
                    $available[] = $name;
            }

            if(count($available) == 0)
                return '';
            else 
                return $available[rand(0, count($available) - 1)];
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


