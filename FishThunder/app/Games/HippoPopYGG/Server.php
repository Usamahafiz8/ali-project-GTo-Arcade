<?php 
namespace VanguardLTE\Games\HippoPopYGG
{
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;    

    class Server
    {
        public $gameState;
        public $debug = false;

        function getPositions($csym, $reels, $cnt, $direction)
        {
            $positions = [];
            if($direction == 0) //from left
            {
                for($r = 0; $r < $cnt; $r++)
                for($c = 0; $c < count($reels['reel'.($r+1)]); $c++)
                {
                    if($reels['reel'.($r+1)][$c] == $csym || $reels['reel'.($r+1)][$c] == 'WILD')
                    {
                        $positions[] = ['column' => $r, 'row' => $c];
                    }                    
                }
            }
            else
            {
                //from right
                for($r = 4; $r >= 5 - $cnt; $r--)
                for($c = 0; $c < count($reels['reel'.($r+1)]); $c++)
                {
                    if($reels['reel'.($r+1)][$c] == $csym || $reels['reel'.($r+1)][$c] == 'WILD')
                    {
                        $positions[] = ['column' => $r, 'row' => $c];
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
                        $response = file(base_path() . '/app/Games/HippoPopYGG/translation.txt')[0];                                                                          
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
                        $response = '{"code":0,"data":{"id":"2203301519500100062","org":null,"gameid":0,"data":{"appsrv":"","file":"/init/","gameid":"10108","height":"600","license":"","org":"Demo","root":"","type":"Html","width":"800"}},"fn":"clientinfo","utcts":1648653590613}';
                        break;
                    case 'game':
                        $filename = base_path() . '/app/Games/HippoPopYGG/game.txt';
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
                        if($betLine == 0)
                        {
                            $betLine = $slotSettings->GetGameData($slotSettings->slotId . 'BetCoin');
                        }
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

                        $action = 'bet';
                        if(strpos($cmd, "upgrade") !== false)
                        {
                            $postData['slotEvent'] = 'freespin';
                            $action = 'upgrade';
                        }
                        if(strpos($cmd, "gamble") !== false)
                        {
                            $postData['slotEvent'] = 'freespin';
                            $action = 'gamble';
                        }
                        if(strpos($cmd, "fs") !== false)
                        {
                            $postData['slotEvent'] = 'freespin';
                            $action = 'freespin';
                        }
                        if($cmd === 'bonus')
                        {
                            $postData['slotEvent'] = 'freespin';
                            $action = 'bonus';
                        }

                        $this->gameState = 'Finished';
                        if($allbet > $slotSettings->GetBalance())
                        {
                            return '{"completion":"Unknown","code":1006,"errorCode":"NO_SUFFICIENT_FUNDS","type":"O","rid":"220215083220::e14db45d-39e6-4cee-a076-ebb72ca0a89b","msg":"You do not have sufficient funds for the bet","fn":null,"details":null,"relaunchUrl":null,"timeElapsed":null,"errorType":null,"balanceDifference":null,"suppressed":[]}';
                        }

                        if( $postData['slotEvent'] != 'freespin' ) 
                        {
                            $slotSettings->SetBalance(-1 * $allbet, $postData['slotEvent']);
                            $bankSum = $allbet / 100 * $slotSettings->GetPercent();
                            $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $bankSum, $postData['slotEvent']);
                            $slotSettings->UpdateJackpots($allbet);
                            $slotSettings->SetBet($allbet);
                            $slotSettings->SetGameData($slotSettings->slotId . 'BetAmount', $allbet);
                            $slotSettings->SetGameData($slotSettings->slotId . 'Step', 1);
                            $slotSettings->SetGameData($slotSettings->slotId . 'CoinWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreespinCoinWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'BetCoin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'CoinBeforeFreespin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'SpinMultiplier', 1);
                            $slotSettings->SetGameData($slotSettings->slotId . 'SymbolMultiplier', 1);
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'Upgrade', 1);
                            $slotSettings->SetGameData($slotSettings->slotId . 'Gamble', 1);
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreespinAdded', 0);
                        }                                 
                        
                        $bets = [];
                        $needRespin = true;
                        $lastReels = [];
                        $spinMultiplier = $slotSettings->GetGameData($slotSettings->slotId . 'SpinMultiplier');

                        if($action === 'bet')
                        {
                            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $nCoins);
                            if($this->debug && $postData['slotEvent'] != 'freespin')
                            {                 
                                $winTypeTmp[0] = 'bonus';
                                $winTypeTmp[1] = 100;
                            }
                            while($needRespin)
                            {
                                $needRespin = $this->doSpin($slotSettings, $postData, $bets, $cmd, $lastReels, $spinMultiplier, $winTypeTmp);
                            }
                        }
                        else if($action === 'bonus')
                        {
                            $allbet = $postData['amount'];
                            $slotSettings->SetBalance(-1 * $allbet, $postData['slotEvent']);
                            $bankSum = $allbet / 100 * $slotSettings->GetPercent();
                            $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $bankSum, $postData['slotEvent']);
                            $slotSettings->UpdateJackpots($allbet);
                            $slotSettings->SetBet($allbet);
                            $slotSettings->SetGameData($slotSettings->slotId . 'BetAmount', $allbet);
                            $slotSettings->SetGameData($slotSettings->slotId . 'Gamble', 1);
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreeMinReelHeight', 3);
                            $slotSettings->SetGameData($slotSettings->slotId . 'Step', 1);
                            $slotSettings->SetGameData($slotSettings->slotId . 'SpinMultiplier', 3);
                            $slotSettings->SetGameData($slotSettings->slotId . 'CoinWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'SymbolMultiplier', 1);
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreespinCoinWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreespinAdded', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'BetCoin', $betLine);
                            $bet = [
                                'betamount' => $postData['amount'],
                                'betcurrency' => 'EUR',
                                'betdata' => [
                                    'accC' => 0,
                                    'accWa' => 0,
                                    'cashBet' => $postData['amount'],
                                    'cmd' => $cmd,
                                    'cmds' => ['fs1', 'gamble1'],
                                ],
                                'eventdata' => [
                                    'accC' => 0,
                                    'accWa' => 0,
                                    'nextCmds' => 'fs1, gamble1',
                                    'response' => [
                                        'cashWin' => 0,
                                        'clientData' => [],
                                        'coinWin' => '0'
                                    ]
                                    ],
                                'prepaid'=>'False',
                                'prizes'=> null,
                                'status'=>'RESULTED',
                                'step' => $slotSettings->GetGameData($slotSettings->slotId . 'Step')
                            ];
                            $slotSettings->SetGameData($slotSettings->slotId . 'Step', $slotSettings->GetGameData($slotSettings->slotId . 'Step') + 1);
                            $this->gameState = 'Pending';
                            $bets[] = $bet;
                        }
                        else if($action === 'gamble')
                        {
                            $slotSettings->SetGameData($slotSettings->slotId . 'Gamble', $slotSettings->GetGameData($slotSettings->slotId . 'Gamble') + 1);
                            $nextCmds = [];
                            if(rand(0, 100) < 50)
                            {
                                //finish gambling, no luck                                
                                $this->gameState = 'Finished';
                                $bet = [
                                    'betamount' => $postData['amount'],
                                    'betcurrency' => 'EUR',
                                    'betdata' => [
                                        'accC' => 0,
                                        'accWa' => 0,
                                        'cashBet' => $postData['amount'],
                                        'cmd' => $cmd,                                    
                                    ],
                                    'eventdata' => [
                                        'accC' => 0,
                                        'accWa' => 0,
                                        'response' => [
                                            'cashWin' => 0,
                                            'clientData' => [],
                                            'coinWin' => '0'
                                        ]
                                        ],
                                    'prepaid'=>'False',
                                    'prizes'=> null,
                                    'status'=>'RESULTED',
                                    'step' => $slotSettings->GetGameData($slotSettings->slotId . 'Step')
                                ];
                                $slotSettings->SetGameData($slotSettings->slotId . 'Step', $slotSettings->GetGameData($slotSettings->slotId . 'Step') + 1);
                                $bets[] = $bet;
                            }
                            else
                            {
                                $newGamble = $slotSettings->GetGameData($slotSettings->slotId . 'Gamble');
                                if($newGamble < 3)
                                {
                                    $nextCmds = ['gamble'.$newGamble, 'fs'.$newGamble];
                                    $bet = [
                                        'betamount' => $postData['amount'],
                                        'betcurrency' => 'EUR',
                                        'betdata' => [
                                            'accC' => 0,
                                            'accWa' => 0,
                                            'cashBet' => $postData['amount'],
                                            'cmd' => $cmd,                                    
                                        ],
                                        'eventdata' => [
                                            'accC' => 0,
                                            'accWa' => 0,
                                            'response' => [
                                                'cashWin' => 0,
                                                'clientData' => [],
                                                'coinWin' => '0'
                                            ]
                                            ],
                                        'prepaid'=>'False',
                                        'prizes'=> null,
                                        'status'=>'RESULTED',
                                        'step' => $slotSettings->GetGameData($slotSettings->slotId . 'Step')
                                    ];
                                    if(count($nextCmds) > 0)
                                    {
                                        $bet['betdata']['cmds'] = $nextCmds;
                                        $bet['eventdata']['nextCmds'] = implode(',', $nextCmds);
                                    }
                                    $this->gameState = 'Pending';
                                    $slotSettings->SetGameData($slotSettings->slotId . 'Step', $slotSettings->GetGameData($slotSettings->slotId . 'Step') + 1);
                                    $bets[] = $bet;
                                }
                                else
                                {
                                    //force to trigger freespin
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 12);
                                    $spinMultiplier = rand(6,8);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeSpinLevel', 3);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                                    $slotSettings->GetSpinSettings('freespin', $betLine, $nCoins, true);
                                    $freespinLeft = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                                    while($freespinLeft > 0)
                                    {
                                        // $winTypeTmp = ["win", $slotSettings->GetBank('bonus')];
                                        $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $nCoins);
                                        $needRespin = true;
                                        $continuousWin = 0;
                                        while($needRespin)
                                        {
                                            $needRespin = $this->doSpin($slotSettings, $postData, $bets, $cmd, $lastReels, $spinMultiplier, $winTypeTmp);
                                            $continuousWin++;
                                            if($continuousWin > rand(1,2))
                                                $winTypeTmp = ["none", 0];
                                        }                                    
                                        $freespinLeft = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                                    }
                                }
                            }                            
                        }
                        else if($action === 'freespin')
                        {
                            //play freespin
                            $newGamble = $slotSettings->GetGameData($slotSettings->slotId . 'Gamble');
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 5 + 2 * ($newGamble - 1));
                            if($newGamble == 1)
                            {
                                $spinMultiplier = rand(2,4);
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeSpinLevel', 1);
                            }                                
                            else if($newGamble == 2)
                            {
                                $spinMultiplier = rand(4,6);
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeSpinLevel', 2);
                            }
                                
                            $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                            $freespinLeft = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                            $slotSettings->GetSpinSettings('freespin', $betLine, $nCoins, true);
                            while($freespinLeft > 0)
                            {
                                // $winTypeTmp = ["win", $slotSettings->GetBank('bonus')];
                                $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $nCoins);
                                $needRespin = true;
                                $continuousWin = 0;
                                while($needRespin)
                                {
                                    $needRespin = $this->doSpin($slotSettings, $postData, $bets, $cmd, $lastReels, $spinMultiplier, $winTypeTmp);
                                    $continuousWin++;
                                    if($continuousWin > rand(1,2))
                                        $winTypeTmp = ["none", 0];
                                }                                    
                                $freespinLeft = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                            }
                        }
                        else if($action === 'upgrade') //this is freespin winning with spinning, not buying
                        {
                            if($slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') >= 7 || rand(0, 100) < 50)
                            {
                                //run freespin
                                $freespinLeft = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeSpinLevel', 4);
                                while($freespinLeft > 0)
                                {
                                    $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $nCoins);
                                    $needRespin = true;
                                    while($needRespin)
                                    {
                                        $needRespin = $this->doSpin($slotSettings, $postData, $bets, $cmd, $lastReels, $spinMultiplier, $winTypeTmp);
                                    }                                    
                                    $freespinLeft = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                                }
                                $bets[0]['eventdata']['response']['clientData']['upgradeResult'] = 'upgrade'.$slotSettings->GetGameData($slotSettings->slotId . 'Upgrade').'_lost';
                                $bets[0]['betdata']['cmd'] = $cmd;
                            }
                            else
                            {
                                //get more freespin
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') + 2);
                                $slotSettings->SetGameData($slotSettings->slotId . 'Upgrade', $slotSettings->GetGameData($slotSettings->slotId . 'Upgrade') + 1);
                                $upgrade = $slotSettings->GetGameData($slotSettings->slotId . 'Upgrade');
                                $betLine = $slotSettings->GetGameData($slotSettings->slotId . 'BetCoin');
                                $bet = [
                                    'betamount' => '0.00',
                                    'betcurrency' => 'EUR',
                                    'betdata' => [
                                        'accC' => $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin'),
                                        'accWa' => number_format($slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') * $betLine, 2),
                                        'cashBet' => $betLine * 10,
                                        'cmd' => $cmd,
                                        'cmds' => ['upgrade'.$upgrade],
                                    ],
                                    'eventdata' => [
                                        'accC' => $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin'),
                                        'accWa' => number_format($slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') * $betLine, 2),
                                        'nextCmds' => 'upgrade'.$upgrade,
                                        'response' => [
                                            'cashWin' => 0,
                                            'clientData' => [],
                                            'coinWin' => '0'
                                        ]
                                        ],
                                    'prepaid'=>'False',
                                    'prizes'=> null,
                                    'status'=>'RESULTED',
                                    'step' => $slotSettings->GetGameData($slotSettings->slotId . 'Step')
                                ];
                                $this->gameState = 'Pending';
                                $bets[] = $bet;
                                $slotSettings->SetGameData($slotSettings->slotId . 'Step', $slotSettings->GetGameData($slotSettings->slotId . 'Step') + 1);
                            }                            
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

        function doSpin($slotSettings, &$postData, &$bets, $cmd, &$lastReels, &$spinMultiplier, &$winTypeTmp)
        {
            if($postData['slotEvent'] == 'freespin')
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bonus');
            }
            else
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bet');
            }

            $nCoins = 10;
            $betLine = 0;
            if(isset($postData['coin']))
                $betLine = $postData['coin'];
            if($postData['slotEvent'] == 'freespin')
            {
                $betLine = $slotSettings->GetGameData($slotSettings->slotId . 'BetCoin');
            }
            
            $allbet = $betLine * $nCoins;
            
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
            if($spinMultiplier > 9 || $slotSettings->GetGameData($slotSettings->slotId . 'GameWin') > $spinWinLimit)
                $winType = 'none';

            $spinAcquired = false;             
            $gameWin = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin');

            $minReels = [];
            $minLineWins = [];
            $minTotalWin = -1;

            $totalWin = 0;
            $lineWins = [];            
            $reels = [];

            $scatterCoin = 0;
            $boardHeight = 3;

            if($postData['slotEvent'] == 'freespin')
            {
                $boardHeight = $slotSettings->GetGameData($slotSettings->slotId . 'FreeMinReelHeight');
            }
            
            $symbolMultiplier =  $slotSettings->GetGameData($slotSettings->slotId . 'SymbolMultiplier');
            for( $i = 0; $i <= 300; $i++ ) 
            {
                if($i > 270)
                    $winType = 'none';
                $totalWin = 0;
                $lineWins = [];                
                $cWins = [
                    "SuperHigh" => 0,
                    "High1" => 0,
                    "High2" => 0,
                    "High3" => 0,
                    "Mid1" => 0,
                    "Mid2" => 0,
                    "Mid3" => 0,
                    "Low1" => 0,
                    "Low2" => 0,
                    "Low3" => 0,
                    "Low4" => 0,
                ];

                $reels = $slotSettings->GetReelStrips($winType, $lastReels, $boardHeight, $postData['slotEvent']);
                $mpl = 1;
                $winline = [];
                
                for( $j = 0; $j <count($slotSettings->SymbolGame); $j++ ) 
                {
                    $mpl = 1;
                    $csym = $slotSettings->SymbolGame[$j];                    
                    $mul1 = $slotSettings->getMultiplier($reels['reel1'], $csym);
                    $mul2 = $slotSettings->getMultiplier($reels['reel2'], $csym);
                    $mul3 = $slotSettings->getMultiplier($reels['reel3'], $csym);
                    $mul4 = $slotSettings->getMultiplier($reels['reel4'], $csym);
                    $mul5 = $slotSettings->getMultiplier($reels['reel5'], $csym);

                    if($mul1 > 0 && $mul2 > 0 && $mul3 > 0) //from left to right 3 symbols contained
                    {
                        $mpl = $mul1 * $mul2 * $mul3;
                        
                        $coin = $slotSettings->Paytable[$csym][3] * $mpl * $spinMultiplier;
                        if($csym === 'SuperHigh')
                            $coin *= $symbolMultiplier;
                        $winMul = $coin * $betLine;
                        $win = $slotSettings->Paytable[$csym][3] * $mpl * $betLine;
                        if($winMul > $cWins[$csym])
                        {
                            $cWins[$csym] = $winMul;                            
                            $winline = ["left", $coin, $win, $winMul, $this->getPositions($csym, $reels, 3, 0), $csym, 3];
                        }
                    }
                    if($mul3 > 0 && $mul4 > 0 && $mul5 > 0) //from left to right 3 symbols contained
                    {
                        $mpl = $mul3 * $mul4 * $mul5;
                        $coin = $slotSettings->Paytable[$csym][3] * $mpl * $spinMultiplier;
                        if($csym === 'SuperHigh')
                            $coin *= $symbolMultiplier;
                        $winMul = $coin * $betLine;
                        $win = $slotSettings->Paytable[$csym][3] * $mpl * $betLine;
                        if($winMul > $cWins[$csym])
                        {
                            $cWins[$csym] = $winMul;                            
                            $winline = ["right", $coin, $win, $winMul, $this->getPositions($csym, $reels, 3, 1), $csym, 3];
                        }
                    }
                    if($mul1 > 0 && $mul2 > 0 && $mul3 > 0 && $mul4 > 0) //from left to right 4 symbols contained
                    {
                        $mpl = $mul1 * $mul2 * $mul3 * $mul4;
                        $coin = $slotSettings->Paytable[$csym][4] * $mpl * $spinMultiplier;
                        if($csym === 'SuperHigh')
                            $coin *= $symbolMultiplier;
                        $winMul = $coin * $betLine;
                        $win = $slotSettings->Paytable[$csym][4] * $mpl * $betLine;
                        if($winMul > $cWins[$csym])
                        {
                            $cWins[$csym] = $winMul;                            
                            $winline = ["left", $coin, $win, $winMul, $this->getPositions($csym, $reels, 4, 0), $csym, 4];
                        }
                    }
                    if($mul2 > 0 && $mul3 > 0 && $mul4 > 0 && $mul5 > 0) //from right to left 4 symbols contained
                    {
                        $mpl = $mul2 * $mul3 * $mul4 * $mul5;
                        $coin = $slotSettings->Paytable[$csym][4] * $mpl * $spinMultiplier;
                        if($csym === 'SuperHigh')
                            $coin *= $symbolMultiplier;
                        $winMul = $coin * $betLine;
                        $win = $slotSettings->Paytable[$csym][4] * $mpl * $betLine;
                        if($winMul > $cWins[$csym])
                        {
                            $cWins[$csym] = $winMul;                            
                            $winline = ["right", $coin, $win, $winMul, $this->getPositions($csym, $reels, 4, 1), $csym, 4];
                        }
                    }

                    if($mul1 > 0 && $mul2 > 0 && $mul3 > 0 && $mul4 > 0 && $mul5 > 0) //from left to right 5 symbols contained
                    {
                        $mpl = $mul1 * $mul2 * $mul3 * $mul4 * $mul5;
                        $coin = $slotSettings->Paytable[$csym][5] * $mpl * $spinMultiplier;
                        if($csym === 'SuperHigh')
                            $coin *= $symbolMultiplier;
                        $winMul = $coin * $betLine;
                        $win = $slotSettings->Paytable[$csym][5] * $mpl * $betLine;
                        if($winMul > $cWins[$csym])
                        {
                            $cWins[$csym] = $winMul;                            
                            $winline = ["left", $coin, $win, $winMul, $this->getPositions($csym, $reels, 5, 0), $csym, 5];
                        }
                    }                    
                    
                    if($cWins[$csym] > 0 && !empty($winline))
                    {
                        array_push($lineWins, $winline);
                        $totalWin += $cWins[$csym];
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
                $manualNoWin = true;
                $reels = $minReels;
                $lineWins = $minLineWins;
                $totalWin = $minTotalWin;
            }
            
            $coinWin = 0; //coins won
            $prizes = [];
            if(!empty($lineWins))
            {
                foreach($lineWins as $winline)
                {
                    $coinWin += $winline[1]; //sum up coins
                    $prizes[] = [
                        'from' => $winline[0],
                        'oak' => $winline[6],
                        'positions' => $winline[4],
                        'symbol' => $winline[5],
                        'win' => number_format($winline[2], 2),
                        'winMultiplied' => number_format($winline[3], 2),
                    ];
                }
            }

            $slotSettings->SetGameData($slotSettings->slotId . 'CoinWin', $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') + $coinWin);
            $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', $totalWin);
            $slotSettings->SetGameData($slotSettings->slotId . 'BetCoin', $betLine);
            //nextCmds
            $nextCmds = [];
            
            $needRespin = false;
            $level = 0;
            if($postData['slotEvent'] == 'freespin')
                $level = $slotSettings->GetGameData($slotSettings->slotId . 'FreeSpinLevel');
            $eventData = [
                'accC' => $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin'),
                'accWa' => number_format($slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') * $betLine, 2),
                'manualNoWin' => $manualNoWin,
                'response' => [
                    'cashWin' => number_format($coinWin * $betLine, 2),
                    'clientData' => [
                        'board' => $slotSettings->GetReelSymbol($reels),
                        'bonus' => false,
                        'level' => $level,
                        'freeSpins' => null,
                        'multiplier' => $spinMultiplier,
                        'symbolMultiplier' => $symbolMultiplier,
                        'unlocked' => false,
                        'winLines' => $prizes,
                    ],
                    'coinWin' => $coinWin
                ]
            ];

            //process reel win positions
            foreach($lineWins as $winline)
            {
                $positions = $winline[4];
                foreach($positions as $position)
                {
                    $r = $position['column'];
                    $c = $position['row'];
                    $reels['reel'.($r+1)][$c] = -1;
                }
            }
            $freespinAcquired = false;
            if($postData['slotEvent'] != 'freespin')
            {
                //check if base reels height reached to maximum
                $allReachedFullHeight = true;
                for($r = 0; $r < 5; $r++)
                    if(count($reels['reel'.($r+1)]) < 6)
                    {
                        $allReachedFullHeight = false;
                        break;
                    }

                if($allReachedFullHeight)
                {
                    if($totalWin == $gameWin)
                        $freespinAcquired = true;
                    else
                    {
                        //increase base multiplier
                        $spinMultiplier++;
                        $eventData['unlocked'] = true;
                        $winTypeTmp[0] = 'win';
                        if($spinMultiplier > 2 || rand(0, 100) < 60 || $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') * $betLine > 10 * $allbet)
                            $winTypeTmp[0] = 'none';
                    }
                }
                        
                if($freespinAcquired)
                {
                    //trigger freespin                    
                    $this->gameState = 'Pending';
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 5);
                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                    $slotSettings->SetGameData($slotSettings->slotId . 'CoinBeforeFreespin', $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin'));
                    $slotSettings->SetGameData($slotSettings->slotId . 'SpinMultiplier', $spinMultiplier);
                    $slotSettings->SetGameData($slotSettings->slotId . 'Upgrade', 1);
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeMinReelHeight', 3);
                }
                else
                {
                    if($totalWin > $gameWin)
                        $needRespin = true;
                }
            }
            else
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'FreespinCoinWin', $slotSettings->GetGameData($slotSettings->slotId . 'FreespinCoinWin') + $coinWin);
                $isExtra = false;
                if($slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') > 5)
                    $isExtra = true;
                $eventData['response']['clientData']['freeSpins'] = [
                    'current' => $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame'),
                    'extra' => $isExtra,
                    'total' => $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames')
                ];
               
                //get minimum reel height
                $minReelHeight = count($reels['reel1']);
                for($r = 2; $r <= 5; $r++)
                    if($minReelHeight > count($reels['reel'.$r]))
                        $minReelHeight = count($reels['reel'.$r]);
                $slotSettings->SetGameData($slotSettings->slotId . 'FreeMinReelHeight', $minReelHeight);
                if($minReelHeight == 6)
                    $symbolMultiplier = 2;
                else if($minReelHeight == 7)
                    $symbolMultiplier = 3;
                else if($minReelHeight == 8)
                {
                    $symbolMultiplier = 4;
                    if($slotSettings->GetGameData($slotSettings->slotId . 'FreespinAdded') ==  0)
                    {
                        $slotSettings->SetGameData($slotSettings->slotId . 'FreespinAdded', 1);
                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') + 2);
                    }
                }
                    
                $slotSettings->SetGameData($slotSettings->slotId . 'SymbolMultiplier', $symbolMultiplier);
                $eventData['symbolMultiplier'] = $symbolMultiplier;
                $eventData['response']['clientData']['bonus'] = true;
                if($totalWin > $gameWin)
                {
                    $spinMultiplier++;
                    $needRespin = true;
                }
                else
                {
                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);
                }
            }

            if($totalWin > $gameWin)
                $lastReels = $reels;
            else
            {
                $lastReels = [];
            }

            $slotSettings->SetGameData($slotSettings->slotId . 'BetCoin', $betLine);
            if($needRespin)
            {
                $this->gameState = 'Pending';
            }
            else
            {
                if($totalWin > 0)
                {
                    $this->gameState = 'Pending';
                    if(!$freespinAcquired)
                    {
                        $freespinLeft = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                        if($freespinLeft == 0)
                            $nextCmds[] = 'C';
                    }                        
                    else
                        $nextCmds[] = 'upgrade1';
                }                
            }
           
            if(!empty($nextCmds))
                $eventData['nextCmds'] = implode(',', $nextCmds);

            if($postData['slotEvent'] == 'freespin')
                $cmd = '';
            else
                $cmd = 'BASE_SPIN';
            $betData = [
                'coin' => $betLine,
                'cheat' => null,
                'clientData' => null,                
                'variant' => null,
                'cmd' => $cmd
            ];
            if($freespinAcquired)
                $betData['cmds'] = ['upgrade1'];

            $bet = [
                'step' => $slotSettings->GetGameData($slotSettings->slotId . 'Step'),
                'betamount' => $allbet,
                'betcurrency' => 'EUR',                
                'status' => 'RESULTED',
                'betdata'=> $betData,
                'eventdata' => $eventData,
                'prizes' => null,
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


