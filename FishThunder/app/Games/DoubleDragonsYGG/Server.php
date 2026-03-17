<?php 
namespace VanguardLTE\Games\DoubleDragonsYGG
{
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;    

    class Server
    {
        public $gameState;
        public $debug = false;
        private $winInRow = 0;
        private $gameMode = 0; //0: normal, 1: double dragons, 2: red dragon freespin, 3: blue dragon freespin    
        private $lastReel = [];
        
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
                        $response = file(base_path() . '/app/Games/DoubleDragonsYGG/translation.txt')[0];                                                                          
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
                        $response = '{"code":0,"data":{"id":"2203301519500100062","org":null,"gameid":0,"data":{"appsrv":"","file":"/init/","gameid":"7329","height":"600","license":"","org":"Demo","root":"","type":"Html","width":"800"}},"fn":"clientinfo","utcts":1648653590613}';
                        break;
                    case 'game':
                        $filename = base_path() . '/app/Games/DoubleDragonsYGG/game.txt';
                        $file = fopen($filename, "r" );
                        $filesize = filesize( $filename );
                        $response = fread( $file, $filesize );
                        fclose( $file );
                        break;
                    case 'restore':
                        $response = '{"code":0,"data":{"size":0,"next":"","data":[],"columns":[],"filterParams":{},"reportGenerationId":null,"header":[],"empty":true},"fn":"restore","utcts":'.time().'}';                        
                        break;
                    case 'info':
                        // $slotSettings->SetGameData($slotSettings->slotId . 'BlueHead', 8);
                        // $slotSettings->SetGameData($slotSettings->slotId . 'RedHead', 8);
                        $response = '{"code":0,"data":{"dragonheads":{"totalblue":'.$slotSettings->GetGameData($slotSettings->slotId . 'BlueHead').',"totalred":'.$slotSettings->GetGameData($slotSettings->slotId . 'RedHead').'}},"fn":"info","utcts":1656646429759}';
                        
                        $slotSettings->SetGameData($slotSettings->slotId . 'CollectionTotalBet', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'CollectionTotalCnt', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'DDTrigger', 0);
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
                            if($slotSettings->GetGameData($slotSettings->slotId . 'LastEvent') == 'bonus')
                                $postData['slotEvent'] = 'freespin';
                            $slotSettings->SaveLogReport($response, $allbet, $reportWin, $postData['slotEvent']);                       
                            break;
                        }
                        $this->gameState = 'Finished';                        
                        if($allbet > $slotSettings->GetBalance())
                        {
                            return '{"completion":"Unknown","code":1006,"errorCode":"NO_SUFFICIENT_FUNDS","type":"O","rid":"220215083220::e14db45d-39e6-4cee-a076-ebb72ca0a89b","msg":"You do not have sufficient funds for the bet","fn":null,"details":null,"relaunchUrl":null,"timeElapsed":null,"errorType":null,"balanceDifference":null,"suppressed":[]}';
                        }

                        $slotSettings->SetGameData($slotSettings->slotId . 'CollectionTotalBet', $slotSettings->GetGameData($slotSettings->slotId . 'CollectionTotalBet') + $allbet);
                        $slotSettings->SetGameData($slotSettings->slotId . 'CollectionTotalCnt', $slotSettings->GetGameData($slotSettings->slotId . 'CollectionTotalCnt') + 1);

                        $slotSettings->SetGameData($slotSettings->slotId . 'CoinWin', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'BetCoin', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'Step', 1);
                        $this->lastReel = [];

                        $slotSettings->SetBalance(-1 * $allbet, $postData['slotEvent']);
                        $bankSum = $allbet / 100 * $slotSettings->GetPercent();
                        $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $bankSum, $postData['slotEvent']);
                        $slotSettings->UpdateJackpots($allbet);          
                        $slotSettings->SetBet($allbet);              
                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeSpin', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', '');
                        $slotSettings->SetGameData($slotSettings->slotId . 'ForceDoubleDragon', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'DDMode', 0);
                        $bets = [];
                        $needRespin = true;

                        if(rand(0, 100) < 15 && $slotSettings->GetBank('bonus') > $allbet * 20)
                            $slotSettings->SetGameData($slotSettings->slotId . 'ForceDoubleDragon', 1);
                        $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $nCoins);
                        if($winTypeTmp[0] == 'bonus')
                        {
                            $slotSettings->SetGameData($slotSettings->slotId . 'DDMode', 1);
                        }
                        while($needRespin)
                        {                            
                            $needRespin = $this->doSpin($slotSettings, $postData, $bets, $cmd, $winTypeTmp);
                            $ddmode = $slotSettings->GetGameData($slotSettings->slotId . 'DDMode');                            
                            if($ddmode == 1)
                                $winTypeTmp = $slotSettings->GetSpinSettings("doubledragon", $betLine, $nCoins);
                            else
                            {
                                $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $nCoins);
                                if($winTypeTmp[0] == 'bonus')
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'DDMode', 1);
                                }
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

        function doSpin($slotSettings, &$postData, &$bets, $cmd, $winTypeTmp)
        {
            $nCoins = 25;
            $betLine = 0;
            if(isset($postData['coin']))
                $betLine = $postData['coin'];
            $allbet = $betLine * $nCoins;
            if($postData['slotEvent'] == 'freespin')
            {
                $betLine = $slotSettings->GetGameData($slotSettings->slotId . 'FSCoin');
                $allbet = $betLine * $nCoins;
            }            
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
            $ddTriggerCnt = 4;           

            $reelName = 'BaseReel';
            $reelSet = 'ReelsConfig0';
            if($postData['slotEvent'] == 'freespin')
            {
                $reelName = 'FeatureReel';
                $reelSet = 'FeatureReelsConfig0';
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bonus');

                $leftFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');                
            }
            else
            {
                if($slotSettings->GetGameData($slotSettings->slotId . 'ForceDoubleDragon') == 1 && $slotSettings->GetGameData($slotSettings->slotId . 'Step') < 6)
                {
                    $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bonus');
                }
            }

            $spinAcquired = false;             
            $gameWin = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin');
            $wild = ['WILD_ICEHEAD', 'WILD_ICE', 'WILD_FIREHEAD', 'WILD_FIRE'];
            $linesId = $slotSettings->GetPaylines();
            $lines = count($linesId);
            $ddRedReel = $slotSettings->GetGameData($slotSettings->slotId . 'DD_Red_Reel');
            $ddBlueReel = $slotSettings->GetGameData($slotSettings->slotId . 'DD_Blue_Reel');

            $minReels = [];
            $minLineWins = [];
            $minTotalWin = -1;
            $minDeltaWin = 0;
            $minOrigReels = [];
            $minddFeature = [];
            $minddRedCnt = 0;
            $minddBlueCnt = 0;
            $minredHeadCnt = 0;
            $minblueHeadCnt = 0;
            $mininsertedRed = [];
            $mininsertedBlue = [];
            $mintrigger = false;

            $totalWin = 0;
            $lineWins = [];
            $origReels = [];
            $ddFeature = [];
            $ddRedCnt = 0;
            $ddBlueCnt = 0;
            $redHeadCnt = 0;
            $blueHeadCnt = 0;
            $insertedRed = [];
            $insertedBlue = [];
            $trigger = false;

            for( $i = 0; $i <= 200; $i++ ) 
            {                
                $trigger = false;
                $stickyA = array_fill(0, 15, 0);
                $totalWin = 0;
                $lineWins = [];                
                $cWins = array_fill(0, $lines, 0);
                $insertedRed = [];
                $insertedBlue = [];
                $bonusMpl = 1;
                

                if($this->debug && $slotSettings->GetGameData($slotSettings->slotId . 'Step') < 2 /*&& $postData['slotEvent'] != 'freespin'*/)
                {                 
                    $winType = 'bonus';

                    // test double dragon
                    // $reels['reel1'] = ['Low1', 'Low1', 'Low2'];
                    // $reels['reel2'] = ['Low1', 'Low3', 'Low4'];
                    // $reels['reel3'] = ['Low1', 'Low3', 'Low2'];
                    // $reels['reel4'] = ['High4', 'High1', 'High4'];
                    // $reels['reel5'] = ['High4', 'Low4', 'Low3'];
                    // $reels['rp'] = [486, 1146, 257, 260, 424];

                    //test red head
                    // $reels['reel1'] = ['High3', 'Low1', 'Low2'];
                    // $reels['reel2'] = ['Low3', 'WILD_FIREHEAD', 'WILD_FIRE'];
                    // $reels['reel3'] = ['WILD_ICE', 'WILD_ICE', 'WILD_ICE'];
                    // $reels['reel4'] = ['Low3', 'High3', 'Low3'];
                    // $reels['reel5'] = ['Low1', 'Low2', 'High3'];
                    // $reels['rp'] = [207, 1087, 88, 202, 1020];

                    //test blue head
                    // $reels['reel1'] = ['Low1', 'Low4', 'High2'];
                    // $reels['reel2'] = ['WILD_ICEHEAD', 'WILD_ICE', 'WILD_ICE'];
                    // $reels['reel3'] = ['Low1', 'Low4', 'High4'];
                    // $reels['reel4'] = ['High4', 'High1', 'High4'];
                    // $reels['reel5'] = ['Low2', 'High3', 'Low2'];
                    // $reels['rp'] = [3655, 1122, 933, 1029, 595];
                }               
                $reels = $slotSettings->GetReelStrips($winType, $reelName, $this->lastReel);

                for($r = 0; $r < 5; $r++)
                    for($c = 0; $c < 3; $c++)
                    {
                        if(isset($this->lastReel[$r][$c]) && $this->lastReel[$r][$c] != '')
                            $reels['reel'.($r+1)][$c] = $this->lastReel[$r][$c];
                    }                
                $origReels = $reels;
                if($this->gameMode == 1)
                {
                    //use double dragon feature
                    $ddRedCnt = $slotSettings->GetGameData($slotSettings->slotId . 'DD_Red_Count');
                    $ddBlueCnt = $slotSettings->GetGameData($slotSettings->slotId . 'DD_Blue_Count');
                    
                    
                    if( $slotSettings->GetGameData($slotSettings->slotId . 'DDTrigger') == 1)
                    {
                        $ddFeature = ['red' => [], 'blue' => []];                         
                        $ddFeature['red'][] = [(string)($ddRedReel) => $ddRedCnt];
                        $ddFeature['blue'][] = [(string)($ddBlueReel) => $ddBlueCnt];                        
                        $trigger = true;
                    }
                    for($c = 2; $c >=0; $c--)
                    {
                        if(!in_array($reels['reel'.($ddBlueReel+1)][$c], $wild))
                        {
                            if($ddBlueCnt > 0)
                            {
                                $reels['reel'.($ddBlueReel+1)][$c] = 'WILD_ICE';
                                $ddBlueCnt--;
                            }
                        }
                        if(!in_array($reels['reel'.($ddRedReel+1)][$c], $wild))
                        {
                            if($ddRedCnt > 0)
                            {
                                $reels['reel'.($ddRedReel+1)][$c] = 'WILD_FIRE';
                                $ddRedCnt--;
                            }
                        }
                    }
                    if(!$trigger)
                        $origReels = $reels;
                    
                }
                else if($this->gameMode == 2)
                {
                    //double dragon in red freespin
                    $ddTriggerCnt = 2;

                    $ddRedCnt = $slotSettings->GetGameData($slotSettings->slotId . 'DD_Red_Count');
                    $ddBlueCnt = $slotSettings->GetGameData($slotSettings->slotId . 'DD_Blue_Count');
                    
                    $trigger = false;
                    if( $slotSettings->GetGameData($slotSettings->slotId . 'DDTrigger') == 1)
                    {
                        $ddFeature = ['red' => [], 'blue' => []];                         
                        $ddFeature['red'][] = [(string)($ddRedReel) => $ddRedCnt];
                        $ddFeature['red'][] = [(string)($ddBlueReel) => $ddBlueCnt];                        
                        $trigger = true;
                    }

                    if($trigger || $slotSettings->GetGameData($slotSettings->slotId . 'DDTrigger') == 2)
                    {
                        for($c = 2; $c >=0; $c--)
                        {
                            if(!in_array($reels['reel'.($ddBlueReel+1)][$c], $wild))
                            {
                                if($ddBlueCnt > 0)
                                {
                                    $reels['reel'.($ddBlueReel+1)][$c] = 'WILD_FIRE';
                                    $ddBlueCnt--;
                                }
                            }
                            if(!in_array($reels['reel'.($ddRedReel+1)][$c], $wild))
                            {
                                if($ddRedCnt > 0)
                                {
                                    $reels['reel'.($ddRedReel+1)][$c] = 'WILD_FIRE';
                                    $ddRedCnt--;
                                }
                            }
                        }
                        if(!$trigger)
                            $origReels = $reels;
                    }
                }
                else if($this->gameMode == 3)
                {
                    //double dragon in blue freespin
                    $ddTriggerCnt = 2;

                    $ddRedCnt = $slotSettings->GetGameData($slotSettings->slotId . 'DD_Red_Count');
                    $ddBlueCnt = $slotSettings->GetGameData($slotSettings->slotId . 'DD_Blue_Count');
                    
                    $trigger = false;
                    if( $slotSettings->GetGameData($slotSettings->slotId . 'DDTrigger') == 1)
                    {
                        $ddFeature = ['red' => [], 'blue' => []];                         
                        $ddFeature['blue'][] = [(string)($ddRedReel) => $ddRedCnt];
                        $ddFeature['blue'][] = [(string)($ddBlueReel) => $ddBlueCnt];                        
                        $trigger = true;
                    }
                    if($trigger || $slotSettings->GetGameData($slotSettings->slotId . 'DDTrigger') == 2)
                    {
                        for($c = 2; $c >=0; $c--)
                        {
                            if(!in_array($reels['reel'.($ddBlueReel+1)][$c], $wild))
                            {
                                if($ddBlueCnt > 0)
                                {
                                    $reels['reel'.($ddBlueReel+1)][$c] = 'WILD_ICE';
                                    $ddBlueCnt--;
                                }
                            }
                            if(!in_array($reels['reel'.($ddRedReel+1)][$c], $wild))
                            {
                                if($ddRedCnt > 0)
                                {
                                    $reels['reel'.($ddRedReel+1)][$c] = 'WILD_ICE';
                                    $ddRedCnt--;
                                }
                            }
                        }
                        if(!$trigger)
                            $origReels = $reels;
                    }
                }
                

                //check dragon head
                $redHeadCnt = 0;
                $blueHeadCnt = 0;
                $redIndex = [];
                $blueIndex = [];
                for($r = 0; $r < 5; $r++)
                    for($c = 0; $c < 3; $c++)
                    {
                        if($reels['reel'.($r+1)][$c] == 'WILD_FIREHEAD')
                        {
                            $redHeadCnt++;
                            $reels['reel'.($r+1)][$c] = 'WILD_FIRE';
                            $redIndex[] = $r * 3 + $c;
                        }
                        if($reels['reel'.($r+1)][$c] == 'WILD_ICEHEAD')
                        {
                            $blueHeadCnt++;
                            $reels['reel'.($r+1)][$c] = 'WILD_ICE';
                            $blueIndex[] = $r * 3 + $c;
                        }
                    } 

                if($redHeadCnt > 0 && $slotSettings->GetGameData($slotSettings->slotId . 'RedHead') > 7 && $winType != 'bonus')
                    continue;
                if($blueHeadCnt > 0 && $slotSettings->GetGameData($slotSettings->slotId . 'BlueHead') > 7 && $winType != 'bonus')
                    continue;
                if($redHeadCnt > 0)    
                {
                    for($w = 0; $w < count($redIndex); $w++)
                    {
                        $insertedRed[$redIndex[$w]] = [];
                        $randomWildCnt = rand(2,3);
                        while($randomWildCnt > 0)
                        {
                            $r = rand(0, 4);
                            $c = rand(0, 2);
                            if(!in_array($reels['reel'.($r+1)][$c], ['WILD_ICE', 'WILD_FIRE'])){
                                $reels['reel'.($r+1)][$c] = 'WILD_FIRE';
                                $randomWildCnt--;
                                $insertedRed[$redIndex[$w]][] = $r * 3 + $c;
                            }                            
                        }
                    }
                }

                if($blueHeadCnt > 0)    
                {
                    for($w = 0; $w < count($blueIndex); $w++)
                    {
                        $insertedBlue[$blueIndex[$w]] = 2;
                        $bonusMpl *= 2;
                    }
                }

                for( $k = 0; $k < $lines; $k++ ) 
                {
                    $mpl = 1;                    

                    $winline = [];
                    for( $j = 0; $j < count($slotSettings->SymbolGame); $j++ ) 
                    {
                        $csym = $slotSettings->SymbolGame[$j];
                        if( !isset($slotSettings->Paytable[$csym]) ) 
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
                
                $delta = abs($spinWinLimit - $totalWin);
                if($minTotalWin == -1 || ($minDeltaWin > $delta && $totalWin > 0))
                {
                    $minDeltaWin = $delta;
                    $minTotalWin = $totalWin;
                    $minLineWins = $lineWins;                    
                    $minReels = $reels;
                    $minOrigReels = $origReels;     
                    
                    $minddFeature = $ddFeature;
                    $minddRedCnt = $ddRedCnt;
                    $minddBlueCnt = $ddBlueCnt;
                    $minredHeadCnt = $redHeadCnt;
                    $minblueHeadCnt = $blueHeadCnt;
                    $mininsertedRed = $insertedRed;
                    $mininsertedBlue = $insertedBlue;
                    $mintrigger = $trigger;
                }

                if($this->debug && $totalWin > $gameWin)
                {
                    $spinAcquired = true;
                    break;
                }                    

                if($totalWin <= $spinWinLimit && (($totalWin > 0 && $winType != 'none') 
                || ($winType == 'bonus' && $redHeadCnt > 0 && $slotSettings->GetGameData($slotSettings->slotId . 'RedHead') > 7) 
                || ($winType == 'bonus' && $blueHeadCnt > 0 && $slotSettings->GetGameData($slotSettings->slotId . 'BlueHead') > 7)))
                {
                    $spinAcquired = true;
                    if($totalWin < 0.5 * $spinWinLimit)
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
                $manualNoWin = true;           
                $reels = $minReels;
                $lineWins = $minLineWins;
                $totalWin = $minTotalWin;
                $origReels = $minOrigReels;

                $ddFeature = $minddFeature;
                $ddRedCnt = $minddRedCnt;
                $ddBlueCnt = $minddBlueCnt;
                $redHeadCnt = $minredHeadCnt;
                $blueHeadCnt = $minblueHeadCnt;
                $insertedRed = $mininsertedRed;
                $insertedBlue = $mininsertedBlue;
                $trigger = $mintrigger;
            }

            $slotSettings->SetWin($totalWin - $gameWin);
            if($trigger) //double dragon triggered
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'DDTrigger', 2);
            }

            //check if wild is included in winning line
            // $activeSymbols = array_fill(0, 15, 0);
            
            $coinWin = 0; //coins won
            
            $activeSymbols = array_fill(0, 15, 0);
            if(!empty($lineWins))
            {
                foreach($lineWins as $winline)
                {
                    $winsyms = $winline[2];
                    for($c = 0; $c < 15; $c++)
                        if($winsyms[$c] == 1)
                            $activeSymbols[$c] = 1;

                    $coinWin += $winline[1]; //sum up coins
                }
            }
            
            if($postData['slotEvent'] == 'freespin')
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'FSWin', $slotSettings->GetGameData($slotSettings->slotId . 'FSWin') + $coinWin * $betLine);
            }
            $slotSettings->SetGameData($slotSettings->slotId . 'CoinWin', $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') + $coinWin);
            $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', $totalWin);
            $slotSettings->SetGameData($slotSettings->slotId . 'BetCoin', $betLine);
            //nextCmds
            $nextCmds = [];

            $needRespin = false;

            if($this->gameMode >= 1)
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'DD_Red_Count', $ddRedCnt);
                $slotSettings->SetGameData($slotSettings->slotId . 'DD_Blue_Count', $ddBlueCnt);
            }

            if($coinWin > 0)
            {
                $needRespin = true;

                if($slotSettings->GetGameData($slotSettings->slotId . 'DDTrigger') != 2)
                    $this->winInRow++;
                if($this->winInRow >= $ddTriggerCnt && $slotSettings->GetGameData($slotSettings->slotId . 'DDTrigger') == 0)
                {
                    $slotSettings->SetGameData($slotSettings->slotId . 'DDTrigger', 1);
                    
                    if($postData['slotEvent'] != 'freespin')//trigger double dragon from normal spin
                        $this->gameMode = 1;
                    else
                        $slotSettings->SetGameData($slotSettings->slotId . 'FSDD', 1);

                    $red_reel = rand(0, 4);
                    $blue_reel = rand(0, 4);
                    while($red_reel == $blue_reel || abs($red_reel - $blue_reel) < 2)
                    {
                        $blue_reel = rand(0, 4);
                    }
                    $redCnt = rand(7, 24);
                    $blueCnt = rand(7, 24);
                    $slotSettings->SetGameData($slotSettings->slotId . 'DD_Red_Count', $redCnt);
                    $slotSettings->SetGameData($slotSettings->slotId . 'DD_Red_Reel', $red_reel);
                    $slotSettings->SetGameData($slotSettings->slotId . 'DD_Blue_Count', $blueCnt);
                    $slotSettings->SetGameData($slotSettings->slotId . 'DD_Blue_Reel', $blue_reel);                                       
                }

                $reels_left = $this->getReelCollapsed($reels, $activeSymbols);
                for($r = 0; $r < 5; $r++)
                    for($c = 0; $c < 3; $c++)
                    {
                        if($reels_left[$r][$c] != '')
                            $stickyA[$r * 3 + $c] = 1;
                    }
                $this->lastReel = $reels_left;
            }
            else
            {
                $this->winInRow = 0;                
                $this->lastReel = [];    
                $ddRedCnt = 0;
                $ddBlueCnt = 0;
                $slotSettings->SetGameData($slotSettings->slotId . 'DD_Red_Count', $ddRedCnt);   
                $slotSettings->SetGameData($slotSettings->slotId . 'DD_Blue_Count', $ddBlueCnt);         
            }

            $eventData = [
                'accC' => $slotSettings->GetGameData($slotSettings->slotId . 'CoinWin'),
                'accWa' => number_format($slotSettings->GetGameData($slotSettings->slotId . 'CoinWin') * $betLine, 2),                
                'reels' => $slotSettings->GetReelSymbol($origReels),
                'reelSet' => $reelSet,
                'rpos' => [$reels['rp'][0], $reels['rp'][1], $reels['rp'][2], $reels['rp'][3], $reels['rp'][4]],
                'wonCoins' => $coinWin,
                'wonMoney' => number_format($coinWin * $betLine, 2),
                'wtw' => $lineWins,
                'manualNoWin' => $manualNoWin,
                'blueHeadsBefore' => $slotSettings->GetGameData($slotSettings->slotId . 'BlueHead'),
                'blueHeadsAfter' => $slotSettings->GetGameData($slotSettings->slotId . 'BlueHead') + $blueHeadCnt,
                'redHeadsBefore' => $slotSettings->GetGameData($slotSettings->slotId . 'RedHead'),
                'redHeadsAfter' =>  $slotSettings->GetGameData($slotSettings->slotId . 'RedHead') + $redHeadCnt,
            ];

            $slotSettings->SetGameData($slotSettings->slotId . 'BlueHead', $slotSettings->GetGameData($slotSettings->slotId . 'BlueHead') + $blueHeadCnt);
            $slotSettings->SetGameData($slotSettings->slotId . 'RedHead', $slotSettings->GetGameData($slotSettings->slotId . 'RedHead') + $redHeadCnt);

            if($this->gameMode < 2)
            {
                if($slotSettings->GetGameData($slotSettings->slotId . 'RedHead') >= 9) //red freespin
                {
                    $collection_total_bet = $slotSettings->GetGameData($slotSettings->slotId . 'CollectionTotalBet');
                    $collection_total_cnt = $slotSettings->GetGameData($slotSettings->slotId . 'CollectionTotalCnt');
                    $averageCoin = (float)(number_format($collection_total_bet / $collection_total_cnt / 25, 2));

                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 5);
                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                    $slotSettings->SetGameData($slotSettings->slotId . 'RedHead', $slotSettings->GetGameData($slotSettings->slotId . 'RedHead') - 9);
                    $slotSettings->SetGameData($slotSettings->slotId . 'FSCoin', $averageCoin);
                    $slotSettings->SetGameData($slotSettings->slotId . 'FSWin', 0);
                    $slotSettings->SetGameData($slotSettings->slotId . 'FSDD', 0);
                    $slotSettings->SetGameData($slotSettings->slotId . 'DDTrigger', 0);
                    $eventData['freeSpins'] = 5;
                    $eventData['freeSpinsAwarded'] = 5;
                    $eventData['freeSpinsFeature'] = 'RED';
                    $eventData['fsCoinValue'] = $averageCoin;
                    $this->gameMode = 2;
                    $needRespin = true;
                    $postData['slotEvent'] = 'freespin';
                    $this->winInRow = 0;
                }
                else if($slotSettings->GetGameData($slotSettings->slotId . 'BlueHead') >= 9) //blue freespin
                {
                    $collection_total_bet = $slotSettings->GetGameData($slotSettings->slotId . 'CollectionTotalBet');
                    $collection_total_cnt = $slotSettings->GetGameData($slotSettings->slotId . 'CollectionTotalCnt');
                    $averageCoin = (float)(number_format($collection_total_bet / $collection_total_cnt / 25, 2));

                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 5);
                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                    $slotSettings->SetGameData($slotSettings->slotId . 'BlueHead', $slotSettings->GetGameData($slotSettings->slotId . 'BlueHead') - 9);
                    $slotSettings->SetGameData($slotSettings->slotId . 'FSCoin', $averageCoin);
                    $slotSettings->SetGameData($slotSettings->slotId . 'FSWin', 0);
                    $slotSettings->SetGameData($slotSettings->slotId . 'FSDD', 0);
                    $slotSettings->SetGameData($slotSettings->slotId . 'DDTrigger', 0);
                    $eventData['freeSpins'] = 5;
                    $eventData['freeSpinsAwarded'] = 5;
                    $eventData['freeSpinsFeature'] = 'BLUE';
                    $eventData['fsCoinValue'] = $averageCoin;
                    $this->gameMode = 3;
                    $needRespin = true;
                    $postData['slotEvent'] = 'freespin';
                    $this->winInRow = 0;
                }
            }

            if(count($insertedRed) > 0)
                $eventData['red'] = $insertedRed;
            if(count($insertedBlue) > 0)
                $eventData['blue'] = $insertedBlue;

            if($slotSettings->GetGameData($slotSettings->slotId . 'DDTrigger') == 2)
            {
                if(count($ddFeature) > 0)
                    $eventData['ddFeature'] = $ddFeature;

                $unusedWilds = [];
                if($ddRedCnt > 0)
                    $unusedWilds[$ddRedReel] = $ddRedCnt;
                if($ddBlueCnt > 0)
                    $unusedWilds[$ddBlueReel] = $ddBlueCnt;
                if(count($unusedWilds) > 0)
                    $eventData['unusedWilds'] = $unusedWilds;  
                if($ddRedCnt == 0 && $ddBlueCnt == 0)
                {
                    $slotSettings->SetGameData($slotSettings->slotId . 'DDTrigger', 0);
                    $this->winInRow = 0;
                }                
            }

            if($trigger || $slotSettings->GetGameData($slotSettings->slotId . 'DDTrigger') == 2)
                $eventData['reels0'] = $slotSettings->GetReelSymbol($reels);

            if($needRespin)
            {
                $eventData['reSpins'] = true;                
                $eventData['stickyA'] = implode('', $stickyA);
                $eventData['reels0'] = $slotSettings->GetReelSymbol($reels);
                if($coinWin > 0)
                    $eventData['reels1'] = $reels_left;
            }
            else
            {
                //check if freepsin
                if($postData['slotEvent'] == 'freespin')
                {
                    $leftFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                    if($leftFreespin > 0)
                    {
                        $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);
                        $needRespin = true; //continue if there is more freespin left
                        $eventData['freeSpins'] = $leftFreespin;
                    }
                }    
            }

            if($totalWin > 0 && !$needRespin)
            {
                $this->gameState = 'Pending';
                $nextCmds[] = 'C';             
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

        function getReelCollapsed($reels, $activeSymbols)
        {
            $reels_left = [];
            for($r = 0; $r < 5; $r++)
            {
                $row = [];
                for($c = 0; $c < 3; $c++)
                {
                    $b = $reels['reel'.($r+1)][$c];
                    if ($activeSymbols[$r * 3 + $c] == 1)
                    {
                        $b = '';
                    }
                    $row[] = $b;
                }
                $reels_left[] = $row;
            }
            for($r = 0; $r < 5; $r++)
            {
                for($c = 0; $c< 3; $c++)
                {
                    if($reels_left[$r][$c] == '')
                    {
                        array_splice($reels_left[$r], $c, 1);
                        array_splice($reels_left[$r], 0, 0, '');
                    }
                }
            }
            return $reels_left;
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


