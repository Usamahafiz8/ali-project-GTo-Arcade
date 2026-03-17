<?php 
namespace VanguardLTE\Games\ValhallaSagaYGG
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
            $positions = [];
            for($i = 0; $i < 5; $i++)
            {
                if($line[$i] != -1)
                {
                    $r = $i;
                    $c = $line[$i];
                    $positions[] = $c * 5 + $r + 1;
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
                        $response = file(base_path() . '/app/Games/ValhallaSagaYGG/translation.txt')[0];                                                                          
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
                        $response = '{"code":0,"data":{"id":"2203301519500100062","org":null,"gameid":0,"data":{"appsrv":"","file":"/init/","gameid":"10194","height":"600","license":"","org":"Demo","root":"","type":"Html","width":"800"}},"fn":"clientinfo","utcts":1648653590613}';
                        break;
                    case 'game':
                        $filename = base_path() . '/app/Games/ValhallaSagaYGG/game.txt';
                        $file = fopen($filename, "r" );
                        $filesize = filesize( $filename );
                        $response = fread( $file, $filesize );
                        fclose( $file );
                        break;
                    case 'restore':
                        $response = '{"code":0,"data":{"size":0,"next":"","data":[],"columns":[],"filterParams":{},"reportGenerationId":null,"header":[],"empty":true},"fn":"restore","utcts":'.time().'}';
                        break;
                    case 'play':                        
                        // $response = '{"code":0,"data":{"buyBal":{"cash":72069.78},"cashRace":{"currency":null,"hasWon":false,"initialPrize":null,"prize":null,"resource":null},"missionState":null,"obj":null,"resultBal":{"cash":72069.78},"wager":{"bets":[{"step":1,"betamount":"60","betcurrency":"EUR","status":"RESULTED","betdata":{"coin":60,"clientParams":{"bonusBuy":true,"linesPlayed":20},"cashBet":60,"cmd":"BUYIN"},"eventdata":{"accC":12,"accWa":"0.60","wonCoins":12,"response":{"cashWin":"51.60","clientData":{"output":[{"bonusInfo":[{"bonusName":"SelectReels","selectedMode":"bonus_buy"},{"bonusName":"BonusFreeSpins","count":"5","freeSpinList":[[{"bonusInfo":[{"bonusName":"FreeSpinsScatters","newScattersPosition":[],"scattersPerPosition":{"1":0,"2":1,"3":1,"4":1,"5":0,"6":0,"7":0,"8":0,"9":1,"10":0,"11":0,"12":0,"13":0,"14":1,"15":0},"scatterSymbol":"11"}],"creditsWon":0,"creditsWonAccumulated":0,"reelLayout":[6,9,5,5,2,7,10,8,7,2,2,7,9,9,8],"type":"spin","winningLines":{"creditsWon":0,"lines":[]}}],[{"bonusInfo":[{"bonusName":"FreeSpinsScatters","newScattersPosition":[9,14],"scattersPerPosition":{"1":0,"2":1,"3":1,"4":1,"5":0,"6":0,"7":0,"8":0,"9":2,"10":0,"11":0,"12":0,"13":0,"14":2,"15":0},"scatterSymbol":"11"}],"creditsWon":0,"creditsWonAccumulated":0,"reelLayout":[8,3,9,9,2,4,9,7,11,8,10,8,5,11,5],"type":"spin","winningLines":{"creditsWon":0,"lines":[]}}],[{"bonusInfo":[{"bonusName":"FreeSpinsScatters","newScattersPosition":[2,7],"scattersPerPosition":{"1":0,"2":2,"3":1,"4":1,"5":0,"6":0,"7":1,"8":0,"9":2,"10":0,"11":0,"12":0,"13":0,"14":2,"15":0},"scatterSymbol":"11"}],"creditsWon":0,"creditsWonAccumulated":0,"reelLayout":[2,11,4,5,3,4,11,2,8,8,8,4,2,2,2],"type":"spin","winningLines":{"creditsWon":0,"lines":[]}}],[{"bonusInfo":[{"bonusName":"FreeSpinsScatters","newScattersPosition":[5],"scattersPerPosition":{"1":0,"2":2,"3":1,"4":1,"5":1,"6":0,"7":1,"8":0,"9":2,"10":0,"11":0,"12":0,"13":0,"14":2,"15":0},"scatterSymbol":"11"}],"creditsWon":0,"creditsWonAccumulated":0,"reelLayout":[9,7,3,5,11,5,6,10,6,10,6,5,2,3,8],"type":"spin","winningLines":{"creditsWon":0,"lines":[]}}],[{"bonusInfo":[{"bonusName":"FreeSpinsScatters","newScattersPosition":[],"scattersPerPosition":{"1":0,"2":2,"3":1,"4":1,"5":1,"6":0,"7":1,"8":0,"9":2,"10":0,"11":0,"12":0,"13":0,"14":2,"15":0},"scatterSymbol":"11"}],"creditsWon":0,"creditsWonAccumulated":0,"reelLayout":[3,1,9,10,8,6,6,7,8,4,10,7,3,4,5],"type":"spin","winningLines":{"creditsWon":0,"lines":[]}}]],"matchPositions":[2,3,4,9,14],"multiplier":"1","creditsWon":51,"prizesPerPosition":{"1":0,"2":10,"3":2,"4":1,"5":2,"6":0,"7":1,"8":0,"9":20,"10":0,"11":0,"12":0,"13":0,"14":15,"15":0},"scatterPrizes":51,"scatterPrizesCredits":51,"scattersPerPosition":{"1":0,"2":2,"3":1,"4":1,"5":1,"6":0,"7":1,"8":0,"9":2,"10":0,"11":0,"12":0,"13":0,"14":2,"15":0},"symbol":"11","symbolAmount":5}],"creditsWon":51.6,"type":"spin","reelLayout":[4,11,11,11,2,8,7,7,11,2,1,10,10,11,8],"winningLines":{"creditsWon":0.0,"lines":[]}}]}},"manualNoWin":false,"nextCmds":"C"},"prizes":null,"prepaid":false}],"prepaid":false,"status":"Pending","timestamp":1669140579,"wagerid":"221122131139277"}},"fn":"play","utcts":1669140579}';
                        // break;
                        $postData['slotEvent'] = 'bet';
                        
                        $betLine = 0;
                        $nCoins = 20;
                        if(isset($postData['coin']))
                            $betLine = $postData['coin'];
                        
                        $allbet = $betLine;
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
                                                        'gameId' => '10194',
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

                        $this->gameState = 'Finished';                        
                        if($allbet > $slotSettings->GetBalance())
                        {
                            return '{"completion":"Unknown","code":1006,"errorCode":"NO_SUFFICIENT_FUNDS","type":"O","rid":"220215083220::e14db45d-39e6-4cee-a076-ebb72ca0a89b","msg":"You do not have sufficient funds for the bet","fn":null,"details":null,"relaunchUrl":null,"timeElapsed":null,"errorType":null,"balanceDifference":null,"suppressed":[]}
                            ';
                        }

                        if($cmd == 'BUYIN')
                        {
                            $postData['coin'] = $postData['coin'] / 60;
                        }

                        if( $postData['slotEvent'] != 'freespin' ) 
                        {
                            $slotSettings->SetBalance(-1 * $allbet, $postData['slotEvent']);
                            $bankSum = $allbet / 100 * $slotSettings->GetPercent();
                            if($cmd == 'BUYIN')
                            {
                                $postData['slotEvent'] = 'freespin';
                            }
                            $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $bankSum, $postData['slotEvent']);
                            $slotSettings->UpdateJackpots($allbet);
                            $slotSettings->SetBet($allbet);
                            $slotSettings->SetGameData($slotSettings->slotId . 'Step', 1);
                            $slotSettings->SetGameData($slotSettings->slotId . 'CoinWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreespinCoinWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'BetCoin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', 0);
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
            $reelName = 'Reels';           

            $lines = count($linesId);
            $nCoins = 20;
            $betLine = 0;
            if(isset($postData['coin']))
                $betLine = $postData['coin'] / 0.2 * 0.01;
            
            $allbet = $postData['amount'];
            
            if($cmd == 'BUYIN')
            {
                $winType = 'bonus';
                $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $nCoins, true);
            }
            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $nCoins);
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
                        
            $spinAcquired = false;             
            $gameWin = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin');

            $minReels = [];
            $minLineWins = [];
            $minTotalWin = -1;
            $minFreespinsWon = 0;
            $minReels0 = [];
            $minWildMultiplier = 1;

            $totalWin = 0;            
            $freespinsWon = 0;            
            $lineWins = [];
            $reels0 = [];
            $reels = [];
            $scatterCnt = 0;
            $scatter = "11";
            $wild = ["1"];
            $wildMultiplier = 1;
            $mpl = 1;

            $availableWildMultipliers = [2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,5,5,5,5,5,10,10];
            shuffle($availableWildMultipliers);
            for( $i = 0; $i <= 300; $i++ ) 
            {
                $totalWin = 0;                
                $freespinsWon = 0;                
                $lineWins = [];
                
                $cWins = array_fill(0, $lines, 0);
                
                if($this->debug && $postData['slotEvent'] != 'freespin')
                {                 
                    $winType = 'bonus';
                }
                $mpl = 1;
                $reels = $slotSettings->GetReelStrips($winType, $reelName);

                if($cmd == 'BUYIN')
                {
                    $reels = $slotSettings->GetNoWinSpin();
                    for($r = 1; $r <= 5; $r++)
                    {
                        for($c = 0; $c < 3; $c++)
                        {
                            if($reels['reel'.$r][$c] == $scatter)
                            {
                                $scatterCnt++;
                            }
                        }
                    }
                    if($scatterCnt < 5)
                    {
                        $leftCnt = 5 - $scatterCnt;
                        while($leftCnt > 0)
                        {
                            $r = rand(1, 5);
                            $c = rand(0, 2);
                            if($reels['reel'.$r][$c] != $scatter)
                            {
                                $reels['reel'.$r][$c] = $scatter;
                                $leftCnt--;
                            }
                        }
                    }
                }

                $reels0 = $reels;
                $wildMultiplier = 1;
                $bonusMpl = 1;
                if(rand(0, 100) < 10 && (in_array($wild[0], $reels['reel1']) || 
                    in_array($wild[0], $reels['reel2']) || 
                    in_array($wild[0], $reels['reel3']) || 
                    in_array($wild[0], $reels['reel4']) || 
                    in_array($wild[0], $reels['reel5']) ))
                {
                    $wildMultiplier = $availableWildMultipliers[rand(0, count($availableWildMultipliers) - 1)];
                }
           
                for( $k = 0; $k < $lines; $k++ )
                {
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
                            $mpl = 1;
                                                                                
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) ) 
                            {
                                $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
                                if(in_array($wild[0], [$s[0], $s[1], $s[2]]))
                                    $bonusMpl = $wildMultiplier;
                                $tmpWin = $slotSettings->Paytable[$csym][3] * $betLine * $mpl * $bonusMpl;
                                $coin = $slotSettings->Paytable[$csym][3] * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $cWins[$k] = $tmpWin;
                                    $emptyLine[0][$p0] = 1;
                                    $emptyLine[1][$p1] = 1;
                                    $emptyLine[2][$p2] = 1;
                                    $winline = [$k + 1, $coin, $this->getConvertedLine([$p0, $p1, $p2, -1, -1]), $tmpWin, $slotSettings->SymbolGame[$j], 3, $bonusMpl];
                                }
                            }
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) ) 
                            {
                                $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
                                if(in_array($wild[0], [$s[0], $s[1], $s[2], $s[3]]))
                                    $bonusMpl = $wildMultiplier;
                                $tmpWin = $slotSettings->Paytable[$csym][4] * $betLine * $mpl * $bonusMpl;
                                $coin = $slotSettings->Paytable[$csym][4] * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $cWins[$k] = $tmpWin;
                                    $emptyLine[0][$p0] = 1;
                                    $emptyLine[1][$p1] = 1;
                                    $emptyLine[2][$p2] = 1;
                                    $emptyLine[3][$p3] = 1;
                                    $winline = [$k + 1, $coin, $this->getConvertedLine([$p0, $p1, $p2, $p3, -1]), $tmpWin, $slotSettings->SymbolGame[$j], 4, $bonusMpl]; //[lineId, coinWon, winPositions]                                                             
                                }
                            }
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) ) 
                            {
                                $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
                                if(in_array($wild[0], [$s[0], $s[1], $s[2], $s[3], $s[4]]))
                                    $bonusMpl = $wildMultiplier;
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
                                    $winline = [$k + 1, $coin, $this->getConvertedLine([$p0, $p1, $p2, $p3, $p4]), $tmpWin, $slotSettings->SymbolGame[$j], 5, $bonusMpl]; //[lineId, coinWon, winPositions]                                                            
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

                $scatterCnt = 0;
                
                for($r = 1; $r <= 5; $r++)
                {
                    for($c = 0; $c < 3; $c++)
                    {
                        if($reels['reel'.$r][$c] == $scatter)
                        {
                            $scatterCnt++;
                        }
                    }
                }

                $freespinsWon = 0;
                if($scatterCnt > 4)
                {
                    $freespinsWon = 5;
                }
                
                $totalWin += $gameWin;
                
                if($minTotalWin == -1 || ($minTotalWin > $totalWin && $totalWin > 0))
                {
                    $minTotalWin = $totalWin;
                    $minLineWins = $lineWins;
                    $minFreespinsWon = $freespinsWon;
                    $minReels = $reels;
                    $minReels0 = $reels0;
                    $minWildMultiplier = $wildMultiplier;
                }

                if($this->debug)
                {
                    $spinAcquired = true;
                    break;
                }                    

                if($cmd == 'BUYIN' && $freespinsWon > 0)
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
                $manualNoWin = true;                
                $reels = $minReels;
                $reels0 = $minReels0;
                $lineWins = $minLineWins;
                $totalWin = $minTotalWin;
                $freespinsWon = $minFreespinsWon;
                $wildMultiplier = $minWildMultiplier;
            }

            $this->lastReels = $reels;           
            
            $coinWin = 0; //coins won
            $winInfo = [];
            if(!empty($lineWins))
            {
                foreach($lineWins as $winline)
                {
                    $coinWin += $winline[1]; //sum up coins
                    $info = [
                        'count' => $winline[5],
                        'creditsWon' => $winline[3],
                        'line' => $winline[0],
                        'matchPositions' => $winline[2],
                        'symbol' => $winline[4]
                    ];
                    if($wildMultiplier > 1)
                        $info['multiplier'] = $winline[6];
                    $winInfo[] = $info;
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
                'wonCoins' => $coinWin,
                'response' => [
                    'cashWin' => number_format($totalWin, 2),
                    'clientData' => [
                        'output' => []
                    ],
                ],
                'manualNoWin' => $manualNoWin,
            ];

            $bonusInfo = [];
            $basicBonusInfo = ['bonusName' => 'SelectReels', 'selectedMode'=>'base_1'];
            if($cmd == 'BUYIN')
                $basicBonusInfo['selectedMode'] = 'bonus_buy';

            $bonusInfo[] = $basicBonusInfo;
            $sticky = [];
            $scatterPos = [];

            if($wildMultiplier > 1)
            {
                $bonusInfo[] = ['bonusName'=>'WildMultipliers', 'wildMultiplier'=>$wildMultiplier, 'wildSymbol'=>'1'];
            }

            if($scatterCnt == 4 && rand(0, 100) < 10 && $totalWin == 0)
            {
                //generate no spin reel
                $finalSpin = $slotSettings->GetNoWinSpin();                
                $bonusInfo[] = [
                    'bonusName' => 'FreeSpinsRespinAnimation',
                    'finalRespinReelLayout' => $slotSettings->GetReelSymbol($finalSpin),
                    'initialReelLayout' => $slotSettings->GetReelSymbol($reels)
                ];                
            }

            if($freespinsWon > 0)
            {
                $sticky = array_fill(0, 15, 0);
                //do 5 freespins
                for($r = 0; $r < 5; $r++)
                    for($c = 0; $c < 3; $c++)
                    {
                        if($reels['reel'.($r+1)][$c] == $scatter)
                        {
                            $pos = $c * 5 + $r;
                            $scatterPos[] = $pos + 1;
                            $sticky[$pos] = 1;
                        }
                    }

                $freespinList = [];
                $prizePositions = [];
                $slotSettings->SetGameData($slotSettings->slotId . 'FreeCoinWin', 0);
                for($i = 0; $i < 5; $i++)
                {
                    [$freespin, $prizePositions] = $this->doSubSpin($slotSettings, $postData, $sticky);
                    $freespinList[] = [$freespin];
                    
                }

                $scatterPrizes = 0;
                $prizesPerPosition = [];
                for($i = 0; $i < 15; $i++)
                {
                    $scatterPrizes += $prizePositions[$i];
                    $prizesPerPosition[($i+1).""] = $prizePositions[$i];
                }

                $freeExtraWin = $slotSettings->GetGameData($slotSettings->slotId . 'FreeCoinWin') * $betLine;
                if($cmd == 'BUYIN')
                {
                    $scatterPrizesCredits = $scatterPrizes * $allbet / 60;
                }
                else
                    $scatterPrizesCredits = $scatterPrizes * $allbet;
                
                $scattersPerPosition = [];
                for($i = 0; $i < 15; $i++)
                {
                    $scattersPerPosition[($i+1).""] = $sticky[$i];
                }

                $freespinInfo = [
                    'bonusName'=>'BonusFreeSpins',
                    'count'=>'5',
                    'freeSpinList' => $freespinList,
                    'matchPositions' => $scatterPos,
                    'multiplier' => '1',
                    'creditsWon'=> $freeExtraWin + $scatterPrizesCredits,
                    'prizesPerPosition' => $prizesPerPosition,
                    'scatterPrizes' => $scatterPrizes,
                    'scatterPrizesCredits' => $scatterPrizesCredits,
                    'scattersPerPosition' => $scattersPerPosition,
                    'symbol'=>'11',
                    'symbolAmount'=> count($scatterPos)
                ];
                $bonusInfo[] = $freespinInfo;

                $totalWin = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin');
                $totalWin += $scatterPrizesCredits;
            }
            
            $output = [
                'bonusInfo' => $bonusInfo,
                'creditsWon' => $totalWin,
                'type' => 'spin',
                'reelLayout' => $slotSettings->GetReelSymbol($reels),
                'winningLines' => [
                    'creditsWon' => $coinWin * $betLine,
                    'lines' => $winInfo
                ]
            ];
            $eventData['response']['clientData']['output'][] = $output;
            $eventData['response']['cashWin'] = number_format($totalWin, 2);
            $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', $totalWin);

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
            $bonusBuy = false;
            if($cmd == 'BUYIN')
            {
                $postData['coin'] = $postData['coin'] * 60;
                $bonusBuy = true;
            }
            $betData = [
                'coin' => $postData['coin'],
                'clientParams' => [
                    'bonusBuy'=> $bonusBuy,
                    'linesPlayed'=>20
                ],
                'cashBet' => (float)$allbet,
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

        function doSubSpin($slotSettings, &$postData, &$sticky)
        {
            $linesId = $slotSettings->GetPaylines();
            $postData['slotEvent'] = 'freespin';
            $reelName = 'FeatureReels';
            $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bonus');            

            $lines = count($linesId);
            $nCoins = 20;
            $betLine = 0;
            if(isset($postData['coin']))
                $betLine = $postData['coin'] / 0.2 * 0.01;
            $allbet = $betLine * 20;
            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $nCoins);
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
            $spinAcquired = false;             
            $gameWin = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin');

            $minReels = [];
            $minLineWins = [];
            $minTotalWin = -1;
            $minReels0 = [];
            $minPrizePerPositions = [];
            $minSticky = [];            
            $minNewScatterPosition = [];

            $lineWins = [];
            $reels0 = [];
            $reels = [];
            $prizePerPositions = array_fill(0, 15, 0);

            $scatter = "11";
            $wild = ["1"];
            $bonusMpl = 1;
            $mpl = 1;

            $prizeMultipliers =[
                '1' => [1,1,1,1,1,1,1,1,1,1,1,1,1,1,2,2,2,2,2,2,2,2,2,2,2,2,3,3,3,3,3,3,5],
                '2' => [10,10,10,10,10,10,10,10,10,10,10,10,10,10, 15,15,15,15,15,15,15,15, 20,20,20,20,20,25],
                '3' => [40,40,40,40,40,40,40,40,40,40,40,40,40,40,40,40,40,40, 50,50,50,50,50,50, 75,75,75, 100],
                '4' => [125,125,125,125,125,125,125,125,125,125,125,125,125,125,125, 150,150,150,150,150,150,150,150, 200,200,200,200, 250],
                '5' => [400,400,400,400,400,400,400,400,400,400,400]
            ];
            
            for( $i = 0; $i <= 300; $i++ ) 
            {
                $totalWin = 0;
                $lineWins = [];
                $newScatterPosition = [];
                
                $tempSticky = $sticky;    

                $cWins = array_fill(0, $lines, 0);
                
                $mpl = 1;
                $reels = $slotSettings->GetReelStrips($winType, $reelName);
                $reels0 = $reels;
           
                for( $k = 0; $k < $lines; $k++ )
                {
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
                            $mpl = 1;
                                                                                
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
                                    $winline = [$k + 1, $coin, $this->getConvertedLine([$p0, $p1, $p2, -1, -1]), $tmpWin, $slotSettings->SymbolGame[$j], 3];
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
                                    $winline = [$k + 1, $coin, $this->getConvertedLine([$p0, $p1, $p2, $p3, -1]), $tmpWin, $slotSettings->SymbolGame[$j], 4]; //[lineId, coinWon, winPositions]                                                             
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
                                    $winline = [$k + 1, $coin, $this->getConvertedLine([$p0, $p1, $p2, $p3, $p4]), $tmpWin, $slotSettings->SymbolGame[$j], 5]; //[lineId, coinWon, winPositions]                                                            
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

                for($r = 0; $r < 5; $r++)
                {
                    for($c = 0; $c < 3; $c++)
                    {
                        if($reels['reel'.($r+1)][$c] == $scatter)
                        {
                            $pos = $c * 5 + $r;
                            $newScatterPosition[] = $pos + 1;
                            $tempSticky[$pos]++;
                        }
                    }
                }

                $totalMul = 0;
                for($k = 0; $k < 15; $k++)
                {
                    if($tempSticky[$k] > 0)
                    {
                        $cnt = $tempSticky[$k];
                        if($cnt > 5)
                            $cnt = 5;
                        $multipliers = $prizeMultipliers[$cnt];
                        $mul = $multipliers[rand(0, count($multipliers) - 1)];
                        $prizePerPositions[$k] = $mul;
                        $totalMul += $mul;
                    }
                }

                $scatterWin = $totalMul * $allbet;
                $totalWin += $gameWin;                
                
                if($minTotalWin == -1 || ($minTotalWin > $totalWin + $scatterWin && $totalWin + $scatterWin > 0))
                {
                    $minTotalWin = $totalWin;
                    $minLineWins = $lineWins;
                    $minReels = $reels;
                    $minReels0 = $reels0;     
                    $minPrizePerPositions = $prizePerPositions;
                    $minSticky = $tempSticky;
                    $minNewScatterPosition = $newScatterPosition;
                }

                if($this->debug)
                {
                    $spinAcquired = true;
                    break;
                }                    

                if($totalWin + $scatterWin <= $spinWinLimit && $winType != 'none' && $totalWin > 0)
                {
                    $spinAcquired = true;
                    break;
                }
                else if( $winType == 'none' && $totalWin == $gameWin ) 
                {
                    break;
                }
            }

            if(!$spinAcquired && $totalWin > $gameWin && $winType != 'none')
            {                
                $reels = $minReels;
                $reels0 = $minReels0;
                $lineWins = $minLineWins;
                $totalWin = $minTotalWin;
                $prizePerPositions = $minPrizePerPositions;
                $tempSticky = $minSticky;
                $newScatterPosition = $minNewScatterPosition;
            }

            $this->lastReels = $reels;
            
            $coinWin = 0; //coins won
            $winInfo = [];
            if(!empty($lineWins))
            {
                foreach($lineWins as $winline)
                {
                    $coinWin += $winline[1]; //sum up coins
                    $winInfo[] = [
                        'count' => $winline[5],
                        'creditsWon' => $winline[3],
                        'line' => $winline[0],
                        'matchPositions' => $winline[2],
                        'symbol' => $winline[4]
                    ];
                }
            }

            $sticky = $tempSticky;
            $slotSettings->SetGameData($slotSettings->slotId . 'CoinWin', $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') + $coinWin);
            
            $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', $totalWin);
            $slotSettings->SetGameData($slotSettings->slotId . 'BetCoin', $betLine);

            //nextCmds
            $scattersPerPosition = [];
            for($i = 0; $i < 15; $i++)
            {
                $scattersPerPosition[($i+1).""] = $sticky[$i];
            }
            
            $bonusInfo = [
                'bonusInfo' => [[
                    'bonusName'=>'FreeSpinsScatters',
                    'newScattersPosition' => $newScatterPosition,
                    'scattersPerPosition' => $scattersPerPosition,
                    'scatterSymbol'=>'11'
                ]],
                'creditsWon'=>$coinWin * $betLine,
                'creditsWonAccumulated'=>$slotSettings->GetGameData($slotSettings->slotId . 'FreeCoinWin') * $betLine,
                'reelLayout' => $slotSettings->GetReelSymbol($reels),
                'type'=>'spin',
                'winningLines' => [
                    'creditsWon'=>$coinWin * $betLine,
                    'lines' => $winInfo
                ]
            ];
            $slotSettings->SetGameData($slotSettings->slotId . 'FreeCoinWin', $slotSettings->GetGameData($slotSettings->slotId . 'FreeCoinWin') + $coinWin);

            return [$bonusInfo, $prizePerPositions];
        }
           
    }

}