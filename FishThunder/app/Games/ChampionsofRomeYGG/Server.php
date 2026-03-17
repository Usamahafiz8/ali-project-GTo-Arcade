<?php 
namespace VanguardLTE\Games\ChampionsofRomeYGG
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
                        $response = file(base_path() . '/app/Games/ChampionsofRomeYGG/translation.txt')[0];                                                                          
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
                        $response = '{"code":0,"data":{"id":"2203301519500100062","org":null,"gameid":0,"data":{"appsrv":"","file":"/init/","gameid":"7358","height":"600","license":"","org":"Demo","root":"","type":"Html","width":"800"}},"fn":"clientinfo","utcts":1648653590613}';
                        break;
                    case 'game':
                        $filename = base_path() . '/app/Games/ChampionsofRomeYGG/game.txt';
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

                        if(in_array($cmd, ['TRAINING_CAMP','DEATH_MATCH_1','DEATH_MATCH_2','DEATH_MATCH_3']))
                        {
                            $postData['slotEvent'] = 'freespin';
                            $postData['freeSpinTypePlayed'] = $cmd;
                            if($cmd == 'TRAINING_CAMP')
                                $freespinsWon = 8;
                            else if($cmd == 'DEATH_MATCH_1')
                                $freespinsWon = 7;
                            else if($cmd == 'DEATH_MATCH_2')
                                $freespinsWon = 6;
                            else if($cmd == 'DEATH_MATCH_3')
                                $freespinsWon = 5;
                            
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $freespinsWon);
                        }

                        //buy bonus
                        if(in_array($cmd, ['BUY_A_BONUS_TRAINING_CAMP','BUY_A_BONUS_DEATH_MATCH_1','BUY_A_BONUS_DEATH_MATCH_2','BUY_A_BONUS_DEATH_MATCH_3']))
                        {
                            $postData['slotEvent'] = 'freespin';
                            $postData['freeSpinTypePlayed'] = $cmd;

                            if($cmd == 'BUY_A_BONUS_TRAINING_CAMP')
                                $freespinsWon = 8;
                            else if($cmd == 'BUY_A_BONUS_DEATH_MATCH_1')
                                $freespinsWon = 7;
                            else if($cmd == 'BUY_A_BONUS_DEATH_MATCH_2')
                                $freespinsWon = 6;
                            else if($cmd == 'BUY_A_BONUS_DEATH_MATCH_3')
                                $freespinsWon = 5;
                            
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $freespinsWon);                            

                            //user bought freespin with 1000 coins
                            $allbet = $betLine * 1000;
                            $slotSettings->SetBalance(-1 * $allbet, $postData['slotEvent']);
                            $bankSum = $allbet / 100 * $slotSettings->GetPercent();
                            $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $bankSum, $postData['slotEvent']);
                            $slotSettings->SetBet($allbet);
                            $slotSettings->UpdateJackpots($allbet);
                            $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreespinCoinWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'CoinWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'Step', 1);
                            $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', 0);
                            $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $nCoins, true);
                        }

                        if( $postData['slotEvent'] != 'freespin' ) 
                        {
                            $slotSettings->SetBalance(-1 * $allbet, $postData['slotEvent']);
                            $bankSum = $allbet / 100 * $slotSettings->GetPercent();
                            $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $bankSum, $postData['slotEvent']);
                            $slotSettings->SetBet($allbet);
                            $slotSettings->UpdateJackpots($allbet);                            
                            
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
            $linesId = $slotSettings->GetPaylines();
            $reelName = '';
            if($postData['slotEvent'] == 'freespin')
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bonus');
                if(isset($postData['reelName']))
                    $reelName = $postData['reelName'];
                else
                {
                    $gladiator = rand(1,3);                    
                    $postData['gladiator'] = $gladiator;
                    if($cmd == 'TRAINING_CAMP' || $cmd == 'BUY_A_BONUS_TRAINING_CAMP')
                        $reelName = 'TrainingReels'.$gladiator;
                    else if($cmd == 'DEATH_MATCH_1' || $cmd == 'BUY_A_BONUS_DEATH_MATCH_1')
                        $reelName = 'Deathmatch1Reels'.$gladiator;
                    else if($cmd == 'DEATH_MATCH_2' || $cmd == 'BUY_A_BONUS_DEATH_MATCH_2')
                        $reelName = 'Deathmatch2Reels'.$gladiator;
                    else if($cmd == 'DEATH_MATCH_3' || $cmd == 'BUY_A_BONUS_DEATH_MATCH_3')
                        $reelName = 'Deathmatch3Reels'.$gladiator;
                    $postData['reelName'] = $reelName;
                }                
            }
            else
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bet');
                $reelName = 'BaseReel';
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
            $spinAcquired = false;             
            $gameWin = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin');

            $minReels = [];
            $minLineWins = [];
            $minTotalWin = -1;
            $minFreespinsWon = 0;            
            $minAddedWilds = [];
            $minBeastPosition = [];
            $minReels0 = [];
            $minScatterCoin = 0;
            $minFeaturesUsed = [];
            $minBeastHit = false;

            $totalWin = 0;
            $treasureWin = 0;
            $freespinsWon = 0;            
            $lineWins = [];            
            $addedWilds = [];
            $reels0 = [];
            $reels = [];
            $beastPosition = [];
            $beastHit = false;

            $scatter = "Scatter";
            $wild = ["Wild"];
            $scatterCoin = 0;
            $featuresUsed = [];

            $bonusMpl = 1;
            if($postData['slotEvent'] == 'freespin')
            {
                $expectedMpl = 1;
                if($postData['slotEvent'] == 'TRAINING_CAMP')
                    $expectedMpl = 1;
                else if($postData['slotEvent'] == 'DEATH_MATCH_1')
                    $expectedMpl = 2;
                else if($postData['slotEvent'] == 'DEATH_MATCH_2')
                    $expectedMpl = 3;
                else if($postData['slotEvent'] == 'DEATH_MATCH_3')
                    $expectedMpl = 4;
                $spinWinLimit = $spinWinLimit / $expectedMpl;
            }

            for( $i = 0; $i <= 300; $i++ ) 
            {
                $addedWildIndices = [];
                $totalWin = 0;
                $treasureWin = 0;
                $freespinsWon = 0;                
                $lineWins = [];                
                $addedWilds = [];
                $featuresUsed = [];
                $beastPosition = [];
                $cWins = array_fill(0, $lines, 0);
                $beastHit = false;
                
                if($this->debug && $postData['slotEvent'] != 'freespin')
                {                 
                    $winType = 'bonus';
                }
                $reels = $slotSettings->GetReelStrips($winType, $reelName);
                $reels0 = $reels;

                if($postData['slotEvent'] == 'freespin')
                {
                    //set wild symbols per gladiator
                    $r = -1;
                    $c = -1;
                    if($postData['gladiator'] == 1)
                    {
                        //red gladiator, wild 2x1 and 1x2
                        $featuresUsed[] = 'swordFeature';                        
                        $random = rand(0, 100);
                        if($random < 40)
                            $r = 0;                            
                        else if($random < 70)
                            $r = 1;
                        else if($random < 85)
                            $r = 2;
                        else
                            $r = 3;                        
                        $c = rand(0, 2);

                        //1x2 wild
                        $addedWilds[] = [
                            'row' => $c,
                            'column' => $r,
                            'icon' => [
                                'iconEnum' => $reels['reel'.($r+1)][$c],
                                'iconType' => 'NORMAL'
                            ]
                        ];
                        $reels['reel'.($r+1)][$c] = $wild[0];
                        $addedWildIndices[] = $r * 3 + $c;

                        $addedWilds[] = [
                            'row' => $c,
                            'column' => $r + 1,
                            'icon' => [
                                'iconEnum' => $reels['reel'.($r+2)][$c],
                                'iconType' => 'NORMAL'
                            ]
                        ];
                        $reels['reel'.($r+2)][$c] = $wild[0];
                        $addedWildIndices[] = ($r+1) * 3 + $c;
                        
                        $availableRow = [];
                        for($w = 0; $w < 5; $w++)
                            if($w != $r && $w != ($r+1))
                                $availableRow[] = $w;
                        
                        $r = $availableRow[rand(0, 2)];
                        $c = rand(0, 1);
                        $addedWilds[] = [
                            'row' => $c,
                            'column' => $r,
                            'icon' => [
                                'iconEnum' => $reels['reel'.($r+1)][$c],
                                'iconType' => 'NORMAL'
                            ]
                        ];
                        $reels['reel'.($r+1)][$c] = $wild[0];
                        $addedWildIndices[] = $r * 3 + $c;

                        $addedWilds[] = [
                            'row' => $c + 1,
                            'column' => $r,
                            'icon' => [
                                'iconEnum' => $reels['reel'.($r+1)][$c+1],
                                'iconType' => 'NORMAL'
                            ]
                        ];
                        $reels['reel'.($r+2)][$c+1] = $wild[0];
                        $addedWildIndices[] = $r * 3 + $c + 1;
                    }
                    else if($postData['gladiator'] == 2)
                    {
                        //green gladiator, wild 2x2
                        $featuresUsed[] = 'maceFeature';
                        $random = rand(0, 100);
                        if($random < 40)
                            $r = 0;                            
                        else if($random < 70)
                            $r = 1;
                        else if($random < 85)
                            $r = 2;
                        else
                            $r = 3;
                        $c = rand(0, 1);

                        $addedWilds[] = [
                            'row' => $c,
                            'column' => $r,
                            'icon' => [
                                'iconEnum' => $reels['reel'.($r+1)][$c],
                                'iconType' => 'NORMAL'
                            ]
                        ];
                        $reels['reel'.($r+1)][$c] = $wild[0];
                        $addedWildIndices[] = ($r) * 3 + $c;

                        $addedWilds[] = [
                            'row' => $c,
                            'column' => $r + 1,
                            'icon' => [
                                'iconEnum' => $reels['reel'.($r+2)][$c],
                                'iconType' => 'NORMAL'
                            ]
                        ];
                        $reels['reel'.($r+2)][$c] = $wild[0];
                        $addedWildIndices[] = ($r+1) * 3 + $c;

                        $addedWilds[] = [
                            'row' => $c + 1,
                            'column' => $r,
                            'icon' => [
                                'iconEnum' => $reels['reel'.($r+1)][$c+1],
                                'iconType' => 'NORMAL'
                            ]
                        ];
                        $reels['reel'.($r+1)][$c+1] = $wild[0];
                        $addedWildIndices[] = ($r) * 3 + $c + 1;
                        $addedWilds[] = [
                            'row' => $c + 1,
                            'column' => $r + 1,
                            'icon' => [
                                'iconEnum' => $reels['reel'.($r+2)][$c+1],
                                'iconType' => 'NORMAL'
                            ]
                        ];
                        $reels['reel'.($r+2)][$c+1] = $wild[0];
                        $addedWildIndices[] = ($r+1) * 3 + $c+1;
                    }
                    else if($postData['gladiator'] == 3)
                    {
                        //blue gladiator, 4 random wilds
                        $featuresUsed[] = 'spearFeature';
                        
                        $count = 0;
                        while($count < 4)
                        {
                            if(rand(0, 100) < 70)
                                $index = rand(0, 9);
                            else
                                $index = rand(10, 14);
                            if(!in_array($index, $addedWildIndices))
                            {
                                $addedWildIndices[] = $index;
                                $count++;
                            }                            
                        }
                        foreach($addedWildIndices as $index)
                        {
                            $r = (int)($index / 3);
                            $c = $index % 3;
                            $addedWilds[] = [
                                'row' => $c,
                                'column' => $r,
                                'icon' => [
                                    'iconEnum' => $reels['reel'.($r+1)][$c],
                                    'iconType' => 'NORMAL'
                                ]
                            ];
                            $reels['reel'.($r+1)][$c] = $wild[0];
                        }
                    }
                    if(rand(0, 100) < 30)
                    {
                        if(rand(0, 100) < 20)
                            $reels['reel'.($r+1)][$c] = 'Beast';
                        else
                            $reels['reel'.(rand(1,5))][rand(0,2)] = 'Beast';
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
                    
                }
                else if($scatterBase >= 3)
                {
                    if($scatterBase == 3)
                        $scatterCoin = 0;//20;
                    else if($scatterBase == 3)
                        $scatterCoin = 0;//300;
                    else if($scatterBase == 3)
                        $scatterCoin = 0;//12000;
                }

                //check beast
                $beastCnt = 0;
                $beastIndex = -1;
                for($r = 1; $r <= 5; $r++)
                {
                    for($c = 0; $c < 3; $c++)
                    {
                        if($reels['reel'.$r][$c] == 'Beast')
                        {
                            $beastCnt++; 
                            $beastIndex = ($r-1)* 3 + $c;
                            $beastPosition = [
                                'column' => $r - 1,
                                'row' => $c,
                                'icon' => [
                                    'iconEnum' => $reels0['reel'.$r][$c],
                                    'iconType' => 'NORMAL'
                                ]
                            ];
                            $featuresUsed[] = 'beastFeature';
                        }
                    }
                }
                if($beastCnt > 1)
                    continue;
                if($postData['slotEvent'] == 'freespin' && in_array($beastIndex, $addedWildIndices))
                {
                    $freespinsWon = 1;                    
                    $beastHit = true;
                }

                $totalWin += $scatterCoin * $betLine;
                $totalWin += $gameWin;

                if($minTotalWin == -1 || ($minTotalWin > $totalWin && $totalWin > 0))
                {
                    $minTotalWin = $totalWin;
                    $minLineWins = $lineWins;
                    $minFreespinsWon = $freespinsWon;
                    $minReels = $reels;
                    $minReels0 = $reels0;
                    $minTreasureWin = $treasureWin;                    
                    $minAddedWilds = $addedWilds;
                    $minScatterCoin = $scatterCoin;
                    $minFeaturesUsed = $featuresUsed;
                    $minBeastPosition = $beastPosition;
                    $minBeastHit = $beastHit;
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
                $reels0 = $minReels0;
                $lineWins = $minLineWins;
                $totalWin = $minTotalWin;
                $freespinsWon = $minFreespinsWon;                    
                $treasureWin = $minTreasureWin;                    
                $addedWilds = $minAddedWilds;
                $scatterCoin = $minScatterCoin;
                $featuresUsed = $minFeaturesUsed;
                $beastPosition = $minBeastPosition;
                $beastHit = $minBeastHit;
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
                'finalBoard' => $slotSettings->GetReelSymbol($reels),
                'featuresUsed' => ['noFeature'],
                'reels' => $slotSettings->GetReelSymbol($reels0),
                'rpos' => [$reels['rp'][0] - 1, $reels['rp'][1] - 1, $reels['rp'][2] - 1, $reels['rp'][3] - 1, $reels['rp'][4] - 1],
                'wonCoins' => $coinWin,
                'wonMoney' => number_format($coinWin * $betLine, 2),
                'wtw' => $lineWins,
                'manualNoWin' => $manualNoWin,
                'scatterWonExtraCoins' => $scatterCoin,
                'scatterWonExtraMoney' => number_format($scatterCoin * $betLine, 2)
            ];

            if($postData['slotEvent'] == 'bet')
            {
                if($scatterBase > 2)
                {
                    //trigger freespin
                    $eventData['freeSpinsAwarded'] = true;
                    $nextCmds = ['TRAINING_CAMP','DEATH_MATCH_1','DEATH_MATCH_2','DEATH_MATCH_3'];
                    $this->gameState = 'Pending';
                    $postData['slotEvent'] = 'freespin';
                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                    $slotSettings->SetGameData($slotSettings->slotId . 'CoinBeforeFreespin', $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin'));
                }
            }
            else
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'FreespinCoinWin', $slotSettings->GetGameData($slotSettings->slotId . 'FreespinCoinWin') + $coinWin);
                $eventData['wildsAwarded'] = $addedWilds;
                
                if(count($beastPosition) > 0)
                    $eventData['beastPosition'] = $beastPosition;
                $eventData['freeSpinTypePlayed'] = $postData['freeSpinTypePlayed'];

                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);
                if($freespinsWon > 0)
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames',  $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') + $freespinsWon);
                if(count($beastPosition) > 0)
                    $eventData['beastHit'] = $beastHit;
                $freespinLeft = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');

                if($postData['freeSpinTypePlayed'] != 'BUY_A_BONUS_TRAINING_CAMP' && $postData['freeSpinTypePlayed'] != 'TRAINING_CAMP')
                {
                    $eventData['deathMatchSpinCoins'] = $coinWin;                    
                    $eventData['deathMatchSpinMoney'] = number_format($coinWin * $betLine);
                    $totalFreeSpinCoins = $slotSettings->GetGameData($slotSettings->slotId . 'FreespinCoinWin');
                    $eventData['deathMatchCoinsGathered'] = $totalFreeSpinCoins;
                    $eventData['deathMatchMoneyGathered'] = number_format($totalFreeSpinCoins * $betLine, 2);
                    $eventData['accC'] = 0;
                    $eventData['accWa'] = 0;
                }

                if($freespinLeft > 0)
                {
                   $needRespin = true;
                   $eventData['freeSpins'] = $freespinLeft;
                }
                else
                {
                    if($postData['freeSpinTypePlayed'] != 'BUY_A_BONUS_TRAINING_CAMP' && $postData['freeSpinTypePlayed'] != 'TRAINING_CAMP')
                    {                        
                        if($totalFreeSpinCoins >= 450)
                        {
                            $eventData['deathMatchThresholdReached'] = true;
                            
                            $multiplier = 1;
                            if($postData['freeSpinTypePlayed'] == 'BUY_A_BONUS_DEATH_MATCH_1' || $postData['freeSpinTypePlayed'] == 'DEATH_MATCH_1')
                                $multiplier = 2;
                            else if($postData['freeSpinTypePlayed'] == 'BUY_A_BONUS_DEATH_MATCH_2' || $postData['freeSpinTypePlayed'] == 'DEATH_MATCH_2')
                                $multiplier = 3;
                            else if($postData['freeSpinTypePlayed'] == 'BUY_A_BONUS_DEATH_MATCH_3' || $postData['freeSpinTypePlayed'] == 'DEATH_MATCH_3')
                                $multiplier = 4;
                            $eventData['deathMatchExtraCoins'] = $multiplier * $slotSettings->GetGameData($slotSettings->slotId . 'FreespinCoinWin');
                            $slotSettings->SetGameData($slotSettings->slotId . 'CoinWin', $slotSettings->GetGameData($slotSettings->slotId . 'CoinBeforeFreespin') + $multiplier * $slotSettings->GetGameData($slotSettings->slotId . 'FreespinCoinWin') );
                            $featuresUsed[] = 'freeSpinMultiplier';                            
                        }
                        else
                        {
                            $eventData['deathMatchThresholdReached'] = false;
                            $consolationCoin = 10 * rand(1, 14);
                            $eventData['consolationDeathMatchExtraCoins'] = $consolationCoin;
                            $eventData['consolationDeathMatchExtraMoney'] = number_format($consolationCoin * $betLine, 2);
                            $featuresUsed[] = 'consolationPrize';
                            $slotSettings->SetGameData($slotSettings->slotId . 'CoinWin', $consolationCoin + $slotSettings->GetGameData($slotSettings->slotId . 'CoinBeforeFreespin'));
                        }
                        $eventData['wonCoins'] = $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin');
                        $eventData['accC'] = $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin');
                        $eventData['wonMoney'] = number_format($slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') * $betLine, 2);
                        $eventData['accWa'] = number_format($slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') * $betLine, 2);
                    }
                    $nextCmds[] = 'C';
                }
                $eventData['featuresUsed'] = $featuresUsed;
            }

            if($needRespin)
            {
                $this->gameState = 'Pending';
            }
            else
            {
                if($totalWin > 0 && $postData['slotEvent'] != 'freespin')
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


