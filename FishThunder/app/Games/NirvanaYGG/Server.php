<?php 
namespace VanguardLTE\Games\NirvanaYGG
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
                        $response = file(base_path() . '/app/Games/NirvanaYGG/translation.txt')[0];                                                                          
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
                        $response = '{"code":0,"data":{"id":"2203301519500100062","org":null,"gameid":0,"data":{"appsrv":"","file":"/init/","gameid":"7319","height":"600","license":"","org":"Demo","root":"","type":"Html","width":"800"}},"fn":"clientinfo","utcts":1648653590613}';
                        break;
                    case 'game':
                        $filename = base_path() . '/app/Games/NirvanaYGG/game.txt';
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
                            $slotSettings->SaveLogReport($response, $allbet, $reportWin, $postData['slotEvent']);                       
                            break;
                        }
                        else if($cmd == 'TripReel' || $cmd == 'Nudge' || $cmd == 'WildSeed')
                        {
                            $postData['slotEvent'] = 'freespin';
                            $postData['coin'] = $slotSettings->GetGameData($slotSettings->slotId . 'BetLine');
                            $this->miniFreespin = -1;
                            $bets = $this->doFreespin($slotSettings, $cmd, $postData);
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
                            $this->miniFreespin = -1;
                            $this->freespinMode = -1;
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

        function doFreespin($slotSettings, $cmd, $postData)
        {
            $bets = [];  
            $needRespin = true;
            $currentGods = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentGods');
            $currentGods[] = $cmd;
            $slotSettings->SetGameData($slotSettings->slotId . 'CurrentGods', $currentGods);
            $wildSeed = 'WildSeed';
            $tripReel = 'TripReel';
            $nudge = 'Nudge';
            if(in_array($wildSeed, $currentGods) && in_array($tripReel, $currentGods) && in_array($nudge, $currentGods))
                $this->freespinMode = 6;
            else if(in_array($tripReel, $currentGods) && in_array($nudge, $currentGods))
                $this->freespinMode = 5;
            else if(in_array($wildSeed, $currentGods) && in_array($nudge, $currentGods))
                $this->freespinMode = 4;
            else if(in_array($tripReel, $currentGods) && in_array($wildSeed, $currentGods))
                $this->freespinMode = 3;
            else if(in_array($nudge, $currentGods))
                $this->freespinMode = 2;
            else if(in_array($wildSeed, $currentGods))
                $this->freespinMode = 1;
            else if(in_array($tripReel, $currentGods))
                $this->freespinMode = 0;

            while($needRespin)
            {
                $needRespin = $this->doSpin($slotSettings, $postData, $bets, $cmd);
            }
            return $bets;
        }

        function doSpin($slotSettings, &$postData, &$bets, $cmd)
        {
            $linesId = $slotSettings->GetPaylines();

            if($postData['slotEvent'] == 'freespin')
            {
                //nudge: FeatureReelsC
                //wildseed: FeatureReelsB
                //megareel: FeatureReels
                //megareel + wild: FeatureReels2C
                //wild + nudge: FeatureReels2
                //megareel + nudge: FeatureReels2B
                //megareel + nudge + reel: FeatureReels3
                switch($this->freespinMode)
                {
                    case 0:
                        $reelName = "FeatureReels";
                        break;
                    case 1:
                        $reelName = "FeatureReelsB";
                        break;
                    case 2:
                        $reelName = "FeatureReelsC";
                        break;
                    case 3:
                        $reelName = "FeatureReels2C";
                        break;
                    case 4:
                        $reelName = "FeatureReels2";
                        break;
                    case 5:
                        $reelName = "FeatureReels2B";
                        break;
                    case 6:
                        $reelName = "FeatureReels3";
                        break;
                    default:
                        break;
                }
                switch($this->miniFreespin)
                {
                    case 0:
                        $reelName = "FeatureReels";
                        break;
                    case 1:
                        $reelName = "FeatureReelsB";
                        break;
                    case 2:
                        $reelName = "FeatureReelsC";
                        break;
                    default:
                        break;
                }
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bonus');
            }
            else
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bet');
                $reelName = 'Reels';
            }

            $lines = count($linesId);
            $nCoins = 20;
            $betLine = 0;
            if(isset($postData['coin']))
                $betLine = $postData['coin'];            
            
            $allbet = $betLine * $nCoins;
            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $nCoins);
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
            if($this->miniFreespin == 2)
                $winType = 'none';
            $spinAcquired = false;             
            $gameWin = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin');

            $minReels = [];
            $minLineWins = [];
            $minTotalWin = -1;
            $minFreespinsWon = 0;
            $minAddedWilds = [];
            $minReels0 = [];
            $minSpecialWin = '';

            $totalWin = 0;
            $freespinsWon = 0;
            $lineWins = [];
            $specialWin = "";
            $addedWilds = [];
            $reels0 = [];
            $reels = [];

            $scatter = "SYM0";
            $wild = ["SYM1"];

            for( $i = 0; $i <= 300; $i++ ) 
            {
                $miniFreespin = -1;
                $totalWin = 0;
                $freespinsWon = 0;
                $lineWins = [];
                $specialWin = "";
                $addedWilds = [];
                $cWins = array_fill(0, $lines, 0);
                
                if($this->debug && $postData['slotEvent'] != 'freespin' || $slotSettings->GetGameData($slotSettings->slotId . 'Step') == 6)
                {                 
                    $winType = 'bonus';
                }

                $reels = $slotSettings->GetReelStrips($winType, $reelName);
                $reels0 = $reels;

                if($this->freespinMode == 0 || $this->freespinMode == 3 || $this->freespinMode == 5 || $this->freespinMode == 6 || $this->miniFreespin == 0)
                {
                    //set mega reel
                    $reels['rp'][2] = $reels['rp'][1]; 
                    $reels['rp'][3] = $reels['rp'][1];
                    $reels['reel3'][0] = $reels['reel2'][0];
                    $reels['reel3'][1] = $reels['reel2'][1];
                    $reels['reel3'][2] = $reels['reel2'][2];
                    $reels['reel4'][0] = $reels['reel2'][0];
                    $reels['reel4'][1] = $reels['reel2'][1];
                    $reels['reel4'][2] = $reels['reel2'][2];
                }

                if($this->freespinMode == 1 || $this->freespinMode == 3 || $this->freespinMode == 4 || $this->freespinMode == 6 || $this->miniFreespin == 1)
                {                    
                    //place wild symbols
                    $reels0 = $reels;
                    $wildCount = rand(2,4);
                    while($wildCount > 0)
                    {
                        $index = rand(0, 14);
                        $r = intval($index / 3);
                        $c = $index % 3;
                        if($reels['reel'.($r+1)][$c] != $wild[0] && $reels['reel'.($r+1)][$c] != $scatter)
                        {
                            $reels['reel'.($r+1)][$c] = $wild[0];
                            $addedWilds[] = $index;
                            $wildCount--;
                        }
                    }
                }
                
                $bonusMpl = 1;
                
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

                if($scatterBase > 2 && $winType != 'bonus')
                    continue;

                if($scatterBase == 2)
                {
                    // if($spinWinLimit < $allbet * 20 && !$this->debug)
                        // continue;
                    $freespinsWon = 1;
                    $miniFreespin = rand(0,2);
                    // $miniFreespin = 2;
                }
                else if($scatterBase == 3)
                {
                    $freespinsWon = 10;                    
                }
                else if($scatterBase == 4)
                {
                    $freespinsWon = 10;
                    $totalWin += 1000 * $betLine;
                    $lineWins[] = [0, 1000, '000000000000000'];
                }
                else if($scatterBase == 5)
                {
                    $freespinsWon = 10;
                    $totalWin += 5000 * $betLine;
                    $lineWins[] = [0, 5000, '000000000000000'];
                }

                if($minTotalWin == -1 || ($minTotalWin > $totalWin && $totalWin > 0))
                {
                    $minTotalWin = $totalWin;
                    $minLineWins = $lineWins;
                    $minFreespinsWon = $freespinsWon;
                    $minReels = $reels;
                    $minReels0 = $reels0;
                    $minAddedWilds = $addedWilds;
                    $minSpecialWin = $specialWin;
                }

                if($this->debug)
                {
                    $spinAcquired = true;
                    break;
                }                    

                if($totalWin <= $spinWinLimit && $winType != 'none' && $totalWin > 0)
                {
                    $spinAcquired = true;
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
                if($postData['slotEvent'] == "freespin")
                {
                    $manualNoWin = true;                
                    $reels = $minReels;
                    $lineWins = $minLineWins;
                    $totalWin = $minTotalWin;
                    $freespinsWon = $minFreespinsWon;                    
                    $reels0 = $minReels0;
                    $addedWilds = $minAddedWilds;
                    $specialWin = $minSpecialWin;
                }
                else
                {
                    $manualNoWin = true;
                    $reels = $slotSettings->GetNoWinSpin($reelName);
                    $lineWins = [];
                    $totalWin = $gameWin;                    
                    $freespinsWon = 0;                    
                }
            }

            $nudge = 0;
            $nudgeReel = -1;

            if($this->freespinMode == 2 || $this->freespinMode == 4 || $this->freespinMode == 5 || $this->freespinMode == 6 || $this->miniFreespin == 2 )
            {
                $reels0 = $reels;
                //if there is no win and need to activate nudge
                if($totalWin == $gameWin)    
                {
                    $nudgeWin = 0;
                    $spinCount = 0;                    
                    // while($nudgeWin == 0 && $spinCount < 50)
                    {
                        //nudge
                        //up nudge
                        $r = 0;
                        $lineWins = [];
                        while($r < 5)
                        {
                            $reels = $reels0;                    
                            $reel = $slotSettings->GetNudgedReel($reelName, $r, $reels['rp'][$r], -1);
                            if(!empty($reel))
                            {
                                $reels['rp'][$r] = $reels['rp'][$r] - 1;
                                $reels['reel'.($r+1)] = $reel;
                                $nudgeWin = 0;
                                $this->checkWinline($reels, $lines, $slotSettings, $betLine, $lineWins, $nudgeWin);
                                if($nudgeWin > 0 /*&& $totalWin + $gameWon <= $spinWinLimit*/)
                                {
                                    $nudge = -1;
                                    $nudgeReel = $r;
                                    $totalWin += $nudgeWin;
                                    break;
                                }
                            }
                            $r++;
                        }

                        //down nudge
                        if($nudge == 0)
                        {
                            $lineWins = [];
                            //try with down nudge
                            $r = 0;
                            while($r < 5)
                            {
                                $reels = $reels0;                    
                                $reel = $slotSettings->GetNudgedReel($reelName, $r, $reels['rp'], 1);
                                if(!empty($reel))
                                {
                                    $reels['rp'][$r] = $reels['rp'][$r] + 1;
                                    $reels['reel'.($r+1)] = $reel;
                                    $nudgeWin = 0;
                                    $this->checkWinline($reels, $lines, $slotSettings, $betLine, $lineWins, $nudgeWin);
                                    if($nudgeWin > 0 /* && $totalWin + $gameWon <= $spinWinLimit*/)
                                    {
                                        $nudge = 1;
                                        $nudgeReel = $r;
                                        $totalWin += $nudgeWin;
                                        break;
                                    }
                                }
                                $r++;
                            }                        
                        }

                        // if($nudgeWin == 0)
                        // {
                        //     $reels = $slotSettings->GetReelStrips('win', $reelName);
                        //     $reels0 = $reels;
                        // }
                        // $spinCount++;
                    }
                }
            }
            $nudgeCode = 'none';
            if($nudge != 0)
            {
                if($nudge == -1)
                    $nudgeCode = ($nudgeReel+1).'D';
                else 
                    $nudgeCode = ($nudgeReel+1).'U';
            }

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
                'rpos' => [$reels['rp'][0] - 1, $reels['rp'][1] - 1, $reels['rp'][2] - 1, $reels['rp'][3] - 1, $reels['rp'][4] - 1],
                'wonCoins' => $coinWin,
                'wonMoney' => number_format($coinWin * $betLine, 2),
                'wtw' => $lineWins,
                'manualNoWin' => $manualNoWin
            ];
            
            if($postData['slotEvent'] == 'bet')
            {
                if($freespinsWon > 0)
                {
                    //trigger freespin
                    if($miniFreespin > -1)
                    {
                        //mini freespin           
                        $needRespin = true;
                        $this->miniFreespin = $miniFreespin;
                        $eventData['miniFreespins'] = $this->mini[$this->miniFreespin];
                    }
                    else
                    {
                        $nextCmds = ['TripReel','WildSeed','Nudge'];
                        $this->gameState = 'Pending';
                    }
                    
                    $eventData['freeSpins'] = $freespinsWon;
                    $eventData['freeSpinsAwarded'] = $freespinsWon;
                    $postData['slotEvent'] = 'freespin';

                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $freespinsWon);
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

                if($this->miniFreespin > -1)
                {
                    if($freespinsWon == 1)
                    {
                        //new mini freespin awarded again
                        $needRespin = true;
                        $eventData['freeSpinsAwarded'] = 1;
                        $eventData['freeSpins'] = 1;
                    }
                    $eventData['miniFreespins'] = $this->mini[$this->miniFreespin];
                    if($this->miniFreespin == 0)
                    {
                        $eventData['activeGods'] = 1;
                        $eventData['tripReel'] = $reels['rp'][1] - 1;
                    }
                    else if($this->miniFreespin == 1)
                    {
                        $eventData['wildSeed'] = $addedWilds;
                        $eventData['activeGods'] = 2;
                        $eventData['reels0'] = $slotSettings->GetReelSymbol($reels0);
                    }
                    else if($this->miniFreespin == 2)
                    {
                        $eventData['activeGods'] = 4;
                        $eventData['nudge'] = $nudgeCode;
                        $eventData['reels0'] = $slotSettings->GetReelSymbol($reels0);
                        $eventData['rpos'] = [$reels0['rp'][0] - 1, $reels0['rp'][1] - 1, $reels0['rp'][2] - 1, $reels0['rp'][3] - 1, $reels0['rp'][4] - 1];
                    }
                }
                else
                {
                    $currentGods = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentGods');
                    $godIndex = 1;
                    foreach($currentGods as $god)
                    {
                        if($god == 'WildSeed')
                            $eventData['selectedGod'.$godIndex] = 1;
                        else if($god == 'TripReel')
                            $eventData['selectedGod'.$godIndex] = 0;
                        else if($god == 'Nudge')
                            $eventData['selectedGod'.$godIndex] = 2;
                        $godIndex++;
                    }

                    if(in_array('Nudge', $currentGods))
                    {
                        $eventData['nudge'] = $nudgeCode;
                        $eventData['reels0'] = $slotSettings->GetReelSymbol($reels0);
                        $eventData['rpos'] = [$reels0['rp'][0] - 1, $reels0['rp'][1] - 1, $reels0['rp'][2] - 1, $reels0['rp'][3] - 1, $reels0['rp'][4] - 1];
                    }
                    if(in_array('WildSeed', $currentGods))
                    {
                        $eventData['wildSeed'] = $addedWilds;
                        $eventData['reels0'] = $slotSettings->GetReelSymbol($reels0);
                        $eventData['rpos'] = [$reels0['rp'][0] - 1, $reels0['rp'][1] - 1, $reels0['rp'][2] - 1, $reels0['rp'][3] - 1, $reels0['rp'][4] - 1];
                    }
                    if(in_array('TripReel', $currentGods))
                    {
                        $eventData['tripReel'] = $reels['rp'][1] - 1;
                    }
                }

                if($freespinsWon > 1)
                {
                    //reset new freespin
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames',  10);
                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                    $eventData['freeSpinsAwarded'] = 10;
                    $needRespin = false;
                    $this->gameState = 'Pending';

                    //add missing gods to next cmds
                    $gods = ['WildSeed', 'TripReel', 'Nudge'];
                    $currentGods = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentGods');
                    foreach($gods as $god)
                    {
                        if(!in_array($god, $currentGods))
                            $nextCmds[] = $god;
                    }
                }
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

        function checkWinline($reels, $lines, $slotSettings, $betLine, &$lineWins, &$totalWin)
        {
            $scatter = "SYM0";
            $wild = ["SYM1"];
            $linesId = $slotSettings->GetPaylines();
            $lines = count($linesId);
            $cWins = array_fill(0, $lines, 0);
            $bonusMpl = 1;
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
                                $winline = [$k + 1, $coin, $this->getConvertedLine($emptyLine)]; //[lineId, coinWon, winPositions]                                                            
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


