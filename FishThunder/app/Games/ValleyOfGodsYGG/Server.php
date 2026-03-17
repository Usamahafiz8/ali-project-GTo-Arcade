<?php 
namespace VanguardLTE\Games\ValleyOfGodsYGG
{
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;    

    class Server
    {
        public $gameState;
        public $debug = false;
        
        function getConvertedLine($csym, $reel, $len, $block)
        {
            $line = array_fill(0, 25, 0);
            if($len > 0)
            {
                for($r = 0; $r < $len; $r++)
                    for($c = 0; $c < 5; $c++)
                    {
                        if($reel['reel'.($r+1)][$c] == $csym && $block['reel'.($r+1)][$c] == 0)
                            $line[$r * 5 + $c] = 1;
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
                        $response = file(base_path() . '/app/Games/ValleyOfGodsYGG/translation.txt')[0];                                                                          
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
                        $response = '{"code":0,"data":{"id":"2203301519500100062","org":null,"gameid":0,"data":{"appsrv":"","file":"/init/","gameid":"7350","height":"600","license":"","org":"Demo","root":"","type":"Html","width":"800"}},"fn":"clientinfo","utcts":1648653590613}';
                        break;
                    case 'game':
                        $filename = base_path() . '/app/Games/ValleyOfGodsYGG/game.txt';
                        $file = fopen($filename, "r" );
                        $filesize = filesize( $filename );
                        $response = fread( $file, $filesize );
                        fclose( $file );
                        break;
                    case 'restore':
                        $response = '{"code":0,"data":{"size":0,"next":"","data":[],"columns":[],"filterParams":{},"reportGenerationId":null,"header":[],"empty":true},"fn":"restore","utcts":'.time().'}';                        
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
                        $slotSettings->SetGameData($slotSettings->slotId . 'GodMode', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'GodMultiplier', 1);
                        $slotSettings->SetGameData($slotSettings->slotId . 'BlueScarabs', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'GodExtraLife', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'RedScarabs', 0);

                        $slotSettings->SetBalance(-1 * $allbet, $postData['slotEvent']);
                        $bankSum = $allbet / 100 * $slotSettings->GetPercent();
                        $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $bankSum, $postData['slotEvent']);
                        $slotSettings->UpdateJackpots($allbet);
                        $slotSettings->SetBet($allbet);
                        $golden_blocks = [
                            'reel1' => [1,1,0,1,1],
                            'reel2' => [1,0,0,0,1],
                            'reel3' => [0,0,0,0,0],
                            'reel4' => [1,0,0,0,1],
                            'reel5' => [1,1,0,1,1],
                        ];
                        
                        $slotSettings->SetGameData($slotSettings->slotId . 'Blocks', $golden_blocks);
                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeSpin', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', '');

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
            $nCoins = 25;
            $betLine = 0;
            if(isset($postData['coin']))
                $betLine = $postData['coin'];
            $allbet = $betLine * $nCoins;
            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $nCoins);
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
            $golden_blocks = $slotSettings->GetGameData($slotSettings->slotId . 'Blocks');
            if($winType == 'bonus')
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'FreeSpin', 1);
            }

            if($slotSettings->GetGameData($slotSettings->slotId . 'FreeSpin') == 1)
            {
                $postData['slotEvent'] = 'freespin';
                $blocks_count = 0;
                for($r = 0; $r < 5; $r++)
                    for($c = 0; $c < 5; $c++)
                    {
                        if($golden_blocks['reel'.($r+1)][$c] == 1)
                        {
                            $blocks_count++;
                        }
                    }
                if($blocks_count > 0)
                    $winType = 'win';

                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bonus');
            }

            $spinAcquired = false;             
            $gameWin = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin');

            $minReels = [];
            $minLineWins = [];
            $minTotalWin = -1;
            $minOrigReels = []; 

            $totalWin = 0;
            $lineWins = [];
            $origReels = [];
            
            $godMode = $slotSettings->GetGameData($slotSettings->slotId . 'GodMode');
             
            for( $i = 0; $i <= 300; $i++ ) 
            {                
                $totalWin = 0;
                $lineWins = [];                
                $cWins = [
                    "High1" => 0,
                    "High2" => 0,
                    "High3" => 0,
                    "High4" => 0,                    
                    "Low1" => 0,
                    "Low2" => 0,
                    "Low3" => 0,
                    "Low4" => 0,
                    "Low5" => 0,
                    "Low6" => 0,
                ];
                
                if($this->debug /*&& $postData['slotEvent'] != 'freespin'*/)
                {                 
                    $winType = 'bonus';
                }
                $reels = $slotSettings->GetReelStrips();
                
                $origReels = $reels;
                $bonusMpl = 1;
                if($godMode == 1)
                    $bonusMpl = $slotSettings->GetGameData($slotSettings->slotId . 'GodMultiplier');
                
                for( $j = 0; $j <count($slotSettings->SymbolGame); $j++ ) 
                {
                    $mpl = 1;
                    $csym = $slotSettings->SymbolGame[$j];                    
                    $mul1 = $slotSettings->getMultiplier($reels['reel1'], $csym, $golden_blocks['reel1']);
                    $mul2 = $slotSettings->getMultiplier($reels['reel2'], $csym, $golden_blocks['reel2']);
                    $mul3 = $slotSettings->getMultiplier($reels['reel3'], $csym, $golden_blocks['reel3']);
                    $mul4 = $slotSettings->getMultiplier($reels['reel4'], $csym, $golden_blocks['reel4']);
                    $mul5 = $slotSettings->getMultiplier($reels['reel5'], $csym, $golden_blocks['reel5']);

                    if($mul1 > 0 && $mul2 > 0 && $mul3 > 0) //from left to right 3 symbols contained
                    {
                        $mpl = $mul1 * $mul2 * $mul3;
                        $tmpWin = $slotSettings->Paytable[$csym][3] * $betLine * $mpl * $bonusMpl;
                        if($tmpWin > $cWins[$csym])
                        {
                            $cWins[$csym] = $tmpWin;
                            $winline = [$j + 1, $slotSettings->Paytable[$csym][3] * $mpl * $bonusMpl, $this->getConvertedLine($csym, $reels, 3, $golden_blocks), $csym];
                        }
                    }
                    if($mul1 > 0 && $mul2 > 0 && $mul3 > 0 && $mul4 > 0) //from left to right 4 symbols contained
                    {
                        $mpl = $mul1 * $mul2 * $mul3 * $mul4;
                        $tmpWin = $slotSettings->Paytable[$csym][4] * $betLine * $mpl * $bonusMpl;
                        if($tmpWin > $cWins[$csym])
                        {
                            $cWins[$csym] = $tmpWin;
                            $winline = [$j + 1, $slotSettings->Paytable[$csym][4] * $mpl * $bonusMpl, $this->getConvertedLine($csym, $reels, 4, $golden_blocks), $csym];
                        }
                    }
                    if($mul1 > 0 && $mul2 > 0 && $mul3 > 0 && $mul4 > 0 && $mul5 > 0) //from left to right 5 symbols contained
                    {
                        $mpl = $mul1 * $mul2 * $mul3 * $mul4 * $mul5;
                        $tmpWin = $slotSettings->Paytable[$csym][5] * $betLine * $mpl * $bonusMpl;
                        if($tmpWin > $cWins[$csym])
                        {
                            $cWins[$csym] = $tmpWin;
                            $winline = [$j + 1, $slotSettings->Paytable[$csym][5] * $mpl * $bonusMpl, $this->getConvertedLine($csym, $reels, 5, $golden_blocks), $csym];
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
                }
                else
                {
                    $manualNoWin = true;
                    $reels = $slotSettings->GetNoWinSpin($golden_blocks);
                    $lineWins = [];
                    $totalWin = $gameWin;
                }
            }

            //check if wild is included in winning line
            // $activeSymbols = array_fill(0, 15, 0);
            
            $coinWin = 0; //coins won
            $scarabsToBlocks = [];
            $waysToWin = 1;
            $red_scarabs = 0;
            $blue_scarabs = 0;
            $scarabsToExtraLife = [];
            $scarabsToMultiplier = [];
            if(!empty($lineWins))
            {
                //get golden block positions
                $block_positions = [];
                for($r = 0; $r < 5; $r++)
                    for($c = 0; $c < 5; $c++)
                    {
                        if($golden_blocks['reel'.($r+1)][$c] == 1)
                        {
                            $block_positions[] = [$r, $c];
                        }
                    }
                
                foreach($lineWins as $winline)
                {
                    $coinWin += $winline[1]; //sum up coins
                    $symbols = $winline[2];
                    $csym = $winline[3];                    

                    for($i = 0; $i < strlen($symbols); $i++)
                    {
                        if($symbols[$i] == '1')
                        {
                            if(count($block_positions) > 0)
                            {
                                $index = rand(0, count($block_positions) - 1);
                                $pos = $block_positions[$index];
                                $r = $pos[0];
                                $c = $pos[1];
                                $scarab = [
                                    'from' => $i,
                                    'to' => $r * 5 + $c
                                ];   
                                $scarabsToBlocks[] = $scarab;
                                array_splice($block_positions, $index, 1);
                                $golden_blocks['reel'.($r+1)][$c] = 0;
                            }
                            
                            if(count($block_positions) == 0)
                            {
                                if(in_array($csym, $slotSettings->redScarabSyms))
                                {
                                    //red scarabs with cnt
                                    $red_scarabs++;
                                    $scarabsToExtraLife[] = $i;
                                }
                                else
                                {
                                    //blue scarabs with cnt
                                    $blue_scarabs++;
                                    $scarabsToMultiplier[] = $i;
                                }
                            }
                        }
                    }
                }

                for($r = 0; $r < 5; $r++)
                {
                    $non_blocks = 0;
                    for($c = 0; $c < 5; $c++)
                        if($golden_blocks['reel'.($r+1)][$c] == 0)
                            $non_blocks++;
                    $waysToWin *= $non_blocks;
                }

                $slotSettings->SetGameData($slotSettings->slotId . 'Blocks', $golden_blocks);
                if($godMode == 0 && count($block_positions) == 0)
                {
                    //if there is no block left, transit to god mode
                    $slotSettings->SetGameData($slotSettings->slotId . 'GodMode', 1);
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeSpin', 1);
                    $slotSettings->SetGameData($slotSettings->slotId . 'GodMultiplier', 2); //when god mode activates, default multiplier is 2
                    $slotSettings->SetGameData($slotSettings->slotId . 'GodExtraLife', 1); //when god mode activates, default extra life is 1

                    $godMode = 1;
                }
            }
            
            if($godMode == 1)
            {
                //give extra life and multipliers regarding to scarabs
                $extraMultiplier = (int)($blue_scarabs / 5);
                $extraBlueScarabs = $blue_scarabs % 5;
                $slotSettings->SetGameData($slotSettings->slotId . 'GodMultiplier', $slotSettings->GetGameData($slotSettings->slotId . 'GodMultiplier') + $extraMultiplier);
                $slotSettings->SetGameData($slotSettings->slotId . 'BlueScarabs', $slotSettings->GetGameData($slotSettings->slotId . 'BlueScarabs') + $extraBlueScarabs);

                $extraLife = (int)($red_scarabs / 5);
                $extraRedScarabs = $red_scarabs % 5;
                $slotSettings->SetGameData($slotSettings->slotId . 'GodExtraLife', $slotSettings->GetGameData($slotSettings->slotId . 'GodExtraLife') + $extraLife);
                $slotSettings->SetGameData($slotSettings->slotId . 'RedScarabs', $slotSettings->GetGameData($slotSettings->slotId . 'RedScarabs') + $extraRedScarabs);
            }            

            $reels = $origReels;

            $slotSettings->SetGameData($slotSettings->slotId . 'CoinWin', $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') + $coinWin);
            $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', $totalWin);
            $slotSettings->SetGameData($slotSettings->slotId . 'BetCoin', $betLine);
            //nextCmds
            $nextCmds = [];

            $needRespin = false;
            if($coinWin > 0)
                $needRespin = true;
            $eventData = [
                'accC' => $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin'),
                'accWa' => number_format($slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') * $betLine, 2),                
                'block' =>implode($golden_blocks['reel1']).implode($golden_blocks['reel2']).implode($golden_blocks['reel3']).implode($golden_blocks['reel4']).implode($golden_blocks['reel5']),
                'reelSet' => implode(",", $reels['names']),
                'reels' => $slotSettings->GetReelSymbol($reels),
                'rpos' => [$reels['rp'][0] - 1, $reels['rp'][1] - 1, $reels['rp'][2] - 1, $reels['rp'][3] - 1, $reels['rp'][4] - 1],
                'wonCoins' => $coinWin,
                'wonMoney' => number_format($coinWin * $betLine, 2),
                'wtw' => $lineWins,
                'manualNoWin' => $manualNoWin,
                'coinsBeforeMultiplier' => (int)($coinWin / $bonusMpl),
                'extraLife' => '0',
                'extraLifeCollection' => '0',
                'multiplier' => '1',
                'multiplierCollection' => '0',
                'scarabsToBlocks' => [],
                'scarabsToExtraLife' => [],
                'scarabsToMultiplier' => [],                
            ];            

            if($totalWin > 0 && !$needRespin)
            {
                if($godMode == 1 && $slotSettings->GetGameData($slotSettings->slotId . 'GodExtraLife') > 0)
                {
                    //if extra life is available, give one more change
                    $slotSettings->SetGameData($slotSettings->slotId . 'GodExtraLife', $slotSettings->GetGameData($slotSettings->slotId . 'GodExtraLife') - 1);
                    $needRespin = true;
                }
                else
                {
                    $this->gameState = 'Pending';
                    $nextCmds[] = 'C';
                }                
            }

            if($godMode == 1)
            {                    
                $eventData['multiplier'] = $slotSettings->GetGameData($slotSettings->slotId . 'GodMultiplier');
                $eventData['multiplierCollection'] = $slotSettings->GetGameData($slotSettings->slotId . 'BlueScarabs');
                $eventData['extraLife'] = $slotSettings->GetGameData($slotSettings->slotId . 'GodExtraLife');
                $eventData['extraLifeCollection'] = $slotSettings->GetGameData($slotSettings->slotId . 'RedScarabs');
                if(count($scarabsToExtraLife) > 0)
                    $eventData['scarabsToExtraLife'] = $scarabsToExtraLife;
                if(count($scarabsToMultiplier) > 0)
                    $eventData['scarabsToMultiplier'] = $scarabsToMultiplier;
            }

            if($needRespin)
            {
                $eventData['reSpins'] = true;                
                $eventData['scarabsToBlocks'] = $scarabsToBlocks;
                $eventData['waysToWin'] = $waysToWin;                
            }
            else
            {
                if($slotSettings->GetGameData($slotSettings->slotId . 'Step') > 1)
                    $eventData['boardUnlocked'] = true;
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

        function checkWinLine($reels, $slotSettings, $csym)
        {
            $wild = "Wild";
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


