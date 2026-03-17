<?php 
namespace VanguardLTE\Games\ZeusVSThorYGG
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
                        $response = file(base_path() . '/app/Games/ZeusVSThorYGG/translation.txt')[0];                                                                          
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
                        $response = '{"code":0,"data":{"id":"","org":null,"gameid":0,"data":{"appsrv":"","file":"/init/","gameid":"1014","height":"600","license":"","org":"Demo","root":"","type":"Html","width":"800"}},"fn":"clientinfo","utcts":'.time().'}';
                        break;
                    case 'game':
                        $filename = base_path() . '/app/Games/ZeusVSThorYGG/game.txt';
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
                        $linesId = $slotSettings->GetPaylines();                                
                        $lines = count($linesId);
                        $betLine = 0;
                        if(isset($postData['coin']))
                            $betLine = $postData['coin'];
                        $allbet = $betLine * $lines;
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
                                                'wonamount' => $win,
                                                'status' => 'RESULTED',
                                                'betdata'=> [
                                                    'doubleA' => $win,
                                                    'doubleN' => 1,
                                                    'cheat' => null,
                                                    'cmd' => 'C',
                                                    'coin' => $slotSettings->GetGameData($slotSettings->slotId . 'BetCoin'),
                                                    'nCoins' => 10,
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
                        if(strpos($cmd, "BASE") !== false)
                        {
                            $postData['slotEvent'] = "bet";
                            $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bet');
                        }
                        else
                        {
                            $postData['slotEvent'] = "freespin";                            
                            $this->gameState = 'Pending';
                            $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bonus');
                        }

                        if(isset($postData['coin']))
                        {
                            $betLine = $postData['coin'];
                            $allbet = $betLine * $lines;
                            //check balance
                            if($allbet > $slotSettings->GetBalance())
                            {
                                return '{"completion":"Unknown","code":1006,"errorCode":"NO_SUFFICIENT_FUNDS","type":"O","rid":"220215083220::e14db45d-39e6-4cee-a076-ebb72ca0a89b","msg":"You do not have sufficient funds for the bet","fn":null,"details":null,"relaunchUrl":null,"timeElapsed":null,"errorType":null,"balanceDifference":null,"suppressed":[]}
                                ';
                            }
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
                        }
                        else
                        {
                            $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);                            
                        }                        
                        
                        $bets = [];                        
                        // $needRespin = true;
                        // while($needRespin)
                        // {
                        //     $needRespin = $this->doSpin($slotSettings, $postData, $bets, $cmd);
                        // }
                
                        $this->doSpin($slotSettings, $postData, $bets, $cmd);

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
                        $slotSettings->SetGameData($slotSettings->slotId . 'BetAmount', $allbet);
                        
                        $response = json_encode($ret);
                        if($this->gameState == 'Finished')
                        {
                            //jokerizer finished with winning more than 500 coin
                            $curCoinWin = $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin');
                            $curBetCoin = $slotSettings->GetGameData($slotSettings->slotId . 'BetCoin');
                            $ret['data']['resultBal']['cash'] = $slotSettings->GetBalance();
                            $slotSettings->SaveLogReport($response, $allbet, $reportWin, $postData['slotEvent']);
                        }
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

        function doSpin($slotSettings, $postData, &$bets, $cmd)
        {
            $lines = 10;
            $betLine = 0;
            if(isset($postData['coin']))
                $betLine = $postData['coin'];
            $allbet = $betLine * $lines;
            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $lines);
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
            
            $spinAcquired = false;            

            $symbolGame = $slotSettings->SymbolGame;            
            $reelIndex = 0;
            $reelName = 'Base';
            $color = strpos($cmd, 'RED') !== false ? 'RED' : 'BLUE';
            $mpl = 1;
            $limit = 200;
            if($postData['slotEvent'] == 'freespin')
            {
                $limit = 1000;
                $winType = 'win';
            }

            $minTotalWin = -1;
            $minLineWins = [];
            $minReels = [];
            $minFreespinWon = 0;
            $gameWin = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin');
            for( $i = 0; $i <= $limit; $i++ ) 
            {
                $totalWin = 0;
                $lineWins = [];
                $cWins = ["RS" => 0,"BS" => 0,"RW" => 0,"BW" => 0,"RG" => 0,"BG" => 0,"RD" => 0,"BD" => 0,"RB" => 0,"BB" => 0,"RH" => 0,"BH" => 0,"RA" => 0,"BA" => 0,"RK" => 0,"BK" => 0,"RQ" => 0,"BQ" => 0];
                
                $scatterR = "RS"; 
                $scatterB = "BS";                
                if($postData['slotEvent'] == 'bet')
                {
                    $reelIndex = rand(0, 19);
                    $reelName = 'Base';
                }
                else
                {
                    $reelIndex = rand(0, 16);
                    $reelName = 'Fs';
                }

                if($postData['slotEvent'] == 'freespin')
                {
                    $spinsLeft = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') -  $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                    if($spinsLeft == 0)
                        $spinsLeft = 1;
                    $spinWinLimit = $spinWinLimit / $spinsLeft;
                }

                if($this->debug)
                {
                    //reel test
                    $winType = 'bonus';
                    $reelName = 'Base';
                    $reelIndex = 13;
                    // if($postData['slotEvent'] != 'freespin')
                    // {
                    //     // $reels['reel1'][0] = 'BS';
                    //     // $reels['reel3'][0] = 'BS';
                    //     // $reels['reel5'][0] = 'BS';
                    //     $reels['reel1'] = ["RG","RH","RH","BH"];
                    //     $reels['reel2'] = ["BD","RD","RG","BB"];
                    //     $reels['reel3'] = ["RG","RA","RH","BD"];
                    //     $reels['reel4'] = ["BH","RG","RQ","RQ"];
                    //     $reels['reel5'] = ["BK","BK","BK","RQ"];
                    // }
                    // else
                    // {
                    //     $reels['reel1'] = ["RA","RA","BD","BD"];
                    //     $reels['reel2'] = ["BA","BH","BH","BQ"];
                    //     $reels['reel3'] = ["RA","BK","BH","BG"];
                    //     $reels['reel4'] = ["BQ","BH","RK","BG"];
                    //     $reels['reel5'] = ["RQ","RA","RQ","BH"];                        
                    // }
                }
                if($winType == 'bonus')
                {
                    $reelName = 'Base';
                    $reelIndex = rand(0, 1) == 0 ? 13 : 3;
                }
                $reels = $slotSettings->GetReelStrips($winType, $reelName, $reelIndex);
                
                for( $k = 0; $k < count($symbolGame); $k++ ) 
                {
                    $mpl = 1;
                    if($postData['slotEvent'] == 'freespin')
                    {
                        $mpl = $slotSettings->GetGameData($slotSettings->slotId . 'FreeSpinMultiplier');
                    }
                    $csym = $symbolGame[$k];
                    $wild = "RW";
                    if(in_array($csym, $slotSettings->SymbolGameB))
                        $wild = "BW";

                    if( $csym == $scatterR || $csym == $scatterB || !isset($slotSettings->Paytable[$csym]))
                    {
                        
                    }
                    else
                    {
                        $winline = [];
                        if(in_array($csym, $slotSettings->SymbolGameR))
                        {
                            if( (in_array($csym,$reels['reel1']) || in_array($wild, $reels['reel1'])) &&
                                (in_array($csym,$reels['reel2']) || in_array($wild, $reels['reel2'])) ) 
                            {
                                //2 symbols match
                                if($postData['slotEvent'] == 'freespin')
                                {
                                    if($color == 'RED')
                                        $mpl = $slotSettings->GetGameData($slotSettings->slotId . 'FreeSpinMultiplier') + 1;
                                }
                                else
                                {
                                    if($color == 'RED')
                                        $mpl = 2;
                                }

                                $bonusMpl = 1;
                                $bonusMpl *= $this->getMultiplier($reels['reel1'], $csym, $wild);
                                $bonusMpl *= $this->getMultiplier($reels['reel2'], $csym, $wild);
                                
                                $tmpWin = $slotSettings->Paytable[$csym][2] * $betLine * $mpl * $bonusMpl;
                                if($tmpWin > $cWins[$csym])
                                {
                                    $cWins[$csym] = $tmpWin;
                                    $winline = [$k, $slotSettings->Paytable[$csym][2] * $mpl * $bonusMpl,  $this->getActiveSymbols($reels, $csym, $wild, 'RED', 2), $tmpWin, $slotSettings->Paytable[$csym][2] * $bonusMpl, $slotSettings->Paytable[$csym][2] * $betLine * $bonusMpl, $mpl];
                                }
                            }
                            if( (in_array($csym,$reels['reel1']) || in_array($wild, $reels['reel1'])) &&
                                (in_array($csym,$reels['reel2']) || in_array($wild, $reels['reel2'])) &&
                                (in_array($csym,$reels['reel3']) || in_array($wild, $reels['reel3'])) ) 
                            {
                                //3 symbols match
                                if($postData['slotEvent'] == 'freespin')
                                {
                                    if($color == 'RED')
                                        $mpl = $slotSettings->GetGameData($slotSettings->slotId . 'FreeSpinMultiplier') + 1;
                                }
                                else
                                {
                                    if($color == 'RED')
                                        $mpl = 2;
                                }
                                $bonusMpl = 1;
                                $bonusMpl *= $this->getMultiplier($reels['reel1'], $csym, $wild);
                                $bonusMpl *= $this->getMultiplier($reels['reel2'], $csym, $wild);
                                $bonusMpl *= $this->getMultiplier($reels['reel3'], $csym, $wild);
                                $tmpWin = $slotSettings->Paytable[$csym][3] * $betLine * $mpl * $bonusMpl;
                                if($tmpWin > $cWins[$csym])
                                {
                                    $cWins[$csym] = $tmpWin;
                                    $winline = [$k, $slotSettings->Paytable[$csym][3] * $mpl * $bonusMpl, $this->getActiveSymbols($reels, $csym, $wild, 'RED', 3), $tmpWin, $slotSettings->Paytable[$csym][3] * $bonusMpl,$slotSettings->Paytable[$csym][3] * $betLine * $bonusMpl, $mpl];
                                }
                            }
                            if( (in_array($csym,$reels['reel1']) || in_array($wild, $reels['reel1'])) &&
                                (in_array($csym,$reels['reel2']) || in_array($wild, $reels['reel2'])) &&
                                (in_array($csym,$reels['reel3']) || in_array($wild, $reels['reel3'])) &&
                                (in_array($csym,$reels['reel4']) || in_array($wild, $reels['reel4'])) )
                            {
                                //4 symbols match
                                if($postData['slotEvent'] == 'freespin')
                                {
                                    if($color == 'RED')
                                        $mpl = $slotSettings->GetGameData($slotSettings->slotId . 'FreeSpinMultiplier') + 1;
                                }
                                else
                                {
                                    if($color == 'RED')
                                        $mpl = 2;
                                }
                                $bonusMpl = 1;
                                $bonusMpl *= $this->getMultiplier($reels['reel1'], $csym, $wild);
                                $bonusMpl *= $this->getMultiplier($reels['reel2'], $csym, $wild);
                                $bonusMpl *= $this->getMultiplier($reels['reel3'], $csym, $wild);
                                $bonusMpl *= $this->getMultiplier($reels['reel4'], $csym, $wild);
                                $tmpWin = $slotSettings->Paytable[$csym][4] * $betLine * $mpl * $bonusMpl;
                                if($tmpWin > $cWins[$csym])
                                {
                                    $cWins[$csym] = $tmpWin;
                                    $winline = [$k, $slotSettings->Paytable[$csym][4] * $mpl * $bonusMpl, $this->getActiveSymbols($reels, $csym, $wild, 'RED', 4), $tmpWin, $slotSettings->Paytable[$csym][4] * $bonusMpl, $slotSettings->Paytable[$csym][4] * $betLine * $bonusMpl, $mpl]; 
                                }
                            }
                            if( (in_array($csym,$reels['reel1']) || in_array($wild, $reels['reel1'])) &&
                            (in_array($csym,$reels['reel2']) || in_array($wild, $reels['reel2'])) &&
                            (in_array($csym,$reels['reel3']) || in_array($wild, $reels['reel3'])) &&
                            (in_array($csym,$reels['reel4']) || in_array($wild, $reels['reel4'])) &&
                            (in_array($csym,$reels['reel5']) || in_array($wild, $reels['reel5']))) 
                            {
                                //5 symbols match
                                if($postData['slotEvent'] == 'freespin')
                                {
                                    if($color == 'RED')
                                        $mpl = $slotSettings->GetGameData($slotSettings->slotId . 'FreeSpinMultiplier') + 1;
                                }
                                else
                                {
                                    if($color == 'RED')
                                        $mpl = 2;
                                }
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
                                    $winline = [$k, $slotSettings->Paytable[$csym][5] * $mpl * $bonusMpl, $this->getActiveSymbols($reels, $csym, $wild, 'RED', 5), $tmpWin, $slotSettings->Paytable[$csym][5] * $bonusMpl, $slotSettings->Paytable[$csym][5] * $betLine * $bonusMpl, $mpl];
                                }
                            }                            
                        }
                        else
                        {
                            $mpl = 1;
                            if( (in_array($csym,$reels['reel5']) || in_array($wild, $reels['reel5'])) &&
                            (in_array($csym,$reels['reel4']) || in_array($wild, $reels['reel4'])) ) 
                            {
                                //2 symbols match
                                if($postData['slotEvent'] == 'freespin')
                                {
                                    if($color == 'BLUE')
                                        $mpl = $slotSettings->GetGameData($slotSettings->slotId . 'FreeSpinMultiplier') + 1;
                                }
                                else
                                {
                                    if($color == 'BLUE')
                                        $mpl = 2;
                                }
                                $bonusMpl = 1;                                
                                $bonusMpl *= $this->getMultiplier($reels['reel4'], $csym, $wild);
                                $bonusMpl *= $this->getMultiplier($reels['reel5'], $csym, $wild);
                                $tmpWin = $slotSettings->Paytable[$csym][2] * $betLine * $mpl * $bonusMpl;
                                if($tmpWin > $cWins[$csym])
                                {
                                    $cWins[$csym] = $tmpWin;
                                    $winline = [$k, $slotSettings->Paytable[$csym][2] * $mpl * $bonusMpl, $this->getActiveSymbols($reels, $csym, $wild, 'BLUE', 2), $tmpWin, $slotSettings->Paytable[$csym][2] * $bonusMpl, $slotSettings->Paytable[$csym][2] * $betLine * $bonusMpl, $mpl]; 
                                }
                            }
                            if( (in_array($csym,$reels['reel5']) || in_array($wild, $reels['reel5'])) &&
                                (in_array($csym,$reels['reel4']) || in_array($wild, $reels['reel4'])) &&
                                (in_array($csym,$reels['reel3']) || in_array($wild, $reels['reel3']))) 
                            {
                                //3 symbols match
                                if($postData['slotEvent'] == 'freespin')
                                {
                                    if($color == 'BLUE')
                                        $mpl = $slotSettings->GetGameData($slotSettings->slotId . 'FreeSpinMultiplier') + 1;
                                }
                                else
                                {
                                    if($color == 'BLUE')
                                        $mpl = 2;
                                }
                                $bonusMpl = 1;                                
                                $bonusMpl *= $this->getMultiplier($reels['reel3'], $csym, $wild);
                                $bonusMpl *= $this->getMultiplier($reels['reel4'], $csym, $wild);
                                $bonusMpl *= $this->getMultiplier($reels['reel5'], $csym, $wild);
                                $tmpWin = $slotSettings->Paytable[$csym][3] * $betLine * $mpl * $bonusMpl;
                                if($tmpWin > $cWins[$csym])
                                {
                                    $cWins[$csym] = $tmpWin;
                                    $winline = [$k, $slotSettings->Paytable[$csym][3] * $mpl * $bonusMpl, $this->getActiveSymbols($reels, $csym, $wild, 'BLUE', 3), $tmpWin, $slotSettings->Paytable[$csym][3] * $bonusMpl, $slotSettings->Paytable[$csym][3] * $betLine * $bonusMpl, $mpl]; 
                                }
                            }
                            if( (in_array($csym,$reels['reel5']) || in_array($wild, $reels['reel5'])) &&
                                (in_array($csym,$reels['reel4']) || in_array($wild, $reels['reel4'])) &&
                                (in_array($csym,$reels['reel3']) || in_array($wild, $reels['reel3'])) &&
                                (in_array($csym,$reels['reel2']) || in_array($wild, $reels['reel2']))) 
                            {
                                //4 symbols match
                                if($postData['slotEvent'] == 'freespin')
                                {
                                    if($color == 'BLUE')
                                        $mpl = $slotSettings->GetGameData($slotSettings->slotId . 'FreeSpinMultiplier') + 1;
                                }
                                else
                                {
                                    if($color == 'BLUE')
                                        $mpl = 2;
                                }
                                $bonusMpl = 1;                                
                                $bonusMpl *= $this->getMultiplier($reels['reel2'], $csym, $wild);
                                $bonusMpl *= $this->getMultiplier($reels['reel3'], $csym, $wild);
                                $bonusMpl *= $this->getMultiplier($reels['reel4'], $csym, $wild);
                                $bonusMpl *= $this->getMultiplier($reels['reel5'], $csym, $wild);
                                $tmpWin = $slotSettings->Paytable[$csym][4] * $betLine * $mpl * $bonusMpl;
                                if($tmpWin > $cWins[$csym])
                                {
                                    $cWins[$csym] = $tmpWin;
                                    $winline = [$k, $slotSettings->Paytable[$csym][4] * $mpl * $bonusMpl, $this->getActiveSymbols($reels, $csym, $wild, 'BLUE', 4), $tmpWin,$slotSettings->Paytable[$csym][4] * $bonusMpl, $slotSettings->Paytable[$csym][4] * $betLine * $bonusMpl, $mpl];
                                }
                            }
                            if( (in_array($csym,$reels['reel1']) || in_array($wild, $reels['reel1'])) &&
                                (in_array($csym,$reels['reel2']) || in_array($wild, $reels['reel2'])) &&
                                (in_array($csym,$reels['reel3']) || in_array($wild, $reels['reel3'])) &&
                                (in_array($csym,$reels['reel4']) || in_array($wild, $reels['reel4'])) &&
                                (in_array($csym,$reels['reel5']) || in_array($wild, $reels['reel5']))) 
                            {
                                //5 symbols match
                                if($postData['slotEvent'] == 'freespin')
                                {
                                    if($color == 'BLUE')
                                        $mpl = $slotSettings->GetGameData($slotSettings->slotId . 'FreeSpinMultiplier') + 1;
                                }
                                else
                                {
                                    if($color == 'BLUE')
                                        $mpl = 2;
                                }
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
                                    $winline = [$k, $slotSettings->Paytable[$csym][5] * $mpl * $bonusMpl, $this->getActiveSymbols($reels, $csym, $wild, 'BLUE', 5), $tmpWin, $slotSettings->Paytable[$csym][5] * $bonusMpl, $slotSettings->Paytable[$csym][5] * $betLine * $bonusMpl, $mpl];
                                }
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
                $scatterR = 0;
                $scatterB = 0;
                
                for( $r = 1; $r <= 5; $r++ ) 
                {
                    for( $p = 0; $p <= 3; $p++ ) 
                    {
                        if( $reels['reel' . $r][$p] == "BS" )
                        {
                            $scatterB++;
                        }
                        else if( $reels['reel' . $r][$p] == "RS" )
                        {
                            $scatterR++;
                        }
                    }
                }
                if($color == 'RED')
                {
                    if($scatterR == 3 || ($scatterR == 2 && $scatterB == 1))
                        $freespinsWon = 10;
                    else if($scatterB == 3 || ($scatterB == 2 && $scatterR == 1))
                        $freespinsWon = 7;
                }
                else
                {
                    if($scatterB == 3 || ($scatterB == 2 && $scatterR == 1))
                        $freespinsWon = 10;
                    else if($scatterR == 3 || ($scatterR == 2 && $scatterB == 1))
                        $freespinsWon = 7;
                }
                if($freespinsWon > 0 && $winType != 'bonus')
                    continue;
                if($postData['slotEvent'] == 'freespin')
                {
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') + $freespinsWon);
                }
                
                $totalWin += $gameWin;

                if($totalWin < $minTotalWin || $minTotalWin == -1)
                {
                    $minTotalWin == $totalWin;
                    $minLineWins = $lineWins;
                    $minReels = $reels;
                    $minFreespinWon = $freespinsWon;                    
                }

                if($totalWin <= $spinWinLimit && (($totalWin > 0 && $winType != 'none') || ($winType == 'bonus' && $freespinsWon > 0)))
                {
                    $spinAcquired = true;
                    if($totalWin < 0.5 * $spinWinLimit && $winType != 'bonus' && $postData['slotEvent'] == 'bet')
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
            if($postData['slotEvent'] == 'freespin')
            {
                if(!$spinAcquired)
                {
                    //set minimum win because in freespin must win
                    $totalWin = $minTotalWin;
                    $reels = $minReels;
                    $lineWins = $minLineWins;
                    $freespinsWon = $minFreespinWon; 
                }
            }
            else
            {
                if(!$spinAcquired && $totalWin > 0)
                {
                    $reels = $slotSettings->GetNoWinSpin($cmd, $postData['slotEvent']);
                    $manualNoWin = true;
                    $lineWins = [];
                    $totalWin = $gameWin;
                    $freespinsWon = 0;
                }            
            }

            $this->lastReels = $reels;           
            
            $coinWin = 0; //coins won            
            $mpl = 1;
            if($postData['slotEvent'] == 'freespin')
                $mpl = $slotSettings->GetGameData($slotSettings->slotId . 'FreeSpinMultiplier');
            if(!empty($lineWins))
            {
                foreach($lineWins as $winline)
                {
                    $coinWin += $winline[1]; //sum up coins 
                    if($winline[6] > $mpl)
                        $mpl = $winline[6];
                }
            }
            if($postData['slotEvent'] == 'freespin')
                $slotSettings->SetGameData($slotSettings->slotId . 'FreeSpinMultiplier', $mpl);
                
            $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', $totalWin);
            $slotSettings->SetGameData($slotSettings->slotId . 'CoinWin', $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') + $coinWin);
            $slotSettings->SetGameData($slotSettings->slotId . 'BetCoin', $betLine);
            //nextCmds
            $nextCmds = [];
            if($totalWin > 0)
            {
                $this->gameState = 'Pending';
                $nextCmds[] = 'C';
            }
            
            $eventData = [
                'accC' => $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin'),
                'accWa' => number_format($slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') * $betLine, 2),
                'finalBoard' => $slotSettings->GetReelSymbol($reels),
                'reelSet' => $reelName,
                'reels' => $slotSettings->GetReelSymbol($reels),
                'rpos' => [$reels['rp'][0] - 1, $reels['rp'][1] - 1, $reels['rp'][2] - 1, $reels['rp'][3] - 1, $reels['rp'][4] - 1],
                'wonCoins' => $coinWin,
                'wonMoney' => number_format($coinWin * $betLine, 2),                
                'wtw' => $lineWins,
                'manualNoWin' => $manualNoWin
            ];  

            if($freespinsWon > 0 && $postData['slotEvent'] == 'bet')
            {
                //triggers free spin
                $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $freespinsWon);
                $slotSettings->SetGameData($slotSettings->slotId . 'FreeSpinMultiplier', 1);
                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                                
                $nextCmds = ["RED_1_".$freespinsWon."_".$freespinsWon,"BLUE_1_".$freespinsWon."_".$freespinsWon];
            }

            if($postData['slotEvent'] == 'bet')
            {
                $eventData['baseGameState'] = [
                    'currentMultiplier' => $mpl,
                    'numberOfFreeSpinsWon' => $freespinsWon,
                    'spinColor' => $cmd == 'BASE_GAME_BLUE' ? 'BLUE' : 'RED'
                ];
            }
            else
            {
                $spinsleft = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') -  $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                $totalFreespins = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                $eventData['freeSpinState'] = [
                    'currentMultiplier' => $mpl,
                    'respinsTriggered' => $freespinsWon,
                    'spinColor' => strpos($cmd, 'BLUE') !== false ? 'BLUE' : 'RED',
                    'spinsLeft' => $spinsleft,
                    'totalFreeSpinsCount' => $totalFreespins
                ];
                if($spinsleft <= 0)
                {
                    //freespin ended
                    $nextCmds = ['C'];
                }
                else
                {
                    $nextCmds = ['RED_'.$mpl.'_'.$spinsleft.'_'.$totalFreespins, 'BLUE_'.$mpl.'_'.$spinsleft.'_'.$totalFreespins];
                }
            }

            $needRespin = false;
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

        function getActiveSymbols($reels, $sym, $wild, $color, $lines)
        {
            $rows = 5;
            $cols = 4;
                        
            $active = array_fill(0, $rows * $cols, 0);
            if($color == 'RED')
            {
                $rows = $lines;
                for($r = 0; $r < $lines; $r++)
                for($c = 0; $c < $cols; $c++)
                {
                    if($reels['reel'.($r+1)][$c] == $sym || $reels['reel'.($r+1)][$c] == $wild)
                        $active[$r * $cols + $c] = 1;
                }
            }
            else if($color == 'BLUE')
            {
                for($r = $rows - 1; $r >= $rows - $lines; $r--)
                    for($c = 0; $c < $cols; $c++)
                    {
                        if($reels['reel'.($r+1)][$c] == $sym || $reels['reel'.($r+1)][$c] == $wild)
                            $active[$r * $cols + $c] = 1;
                    }
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


