<?php 
namespace VanguardLTE\Games\AladdinsChestNG
{

set_time_limit(5);
    class Server
    {
        public function get($request, $game)
        {
            function get_($request, $game)
            {
                \DB::transaction(function() use ($request, $game)
                {
                    try
                    {
                        $debug = false;
                        $FREESPIN_TAG = "freespin";
                        $HOLDNLINK_TAG = "holdnlink";
                        $userId = \Auth::id();
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
                        $postData = json_decode(trim(file_get_contents('php://input')), true)['gameData'];
                        $result_tmp = [];
                        $reqId = $postData['action'];
                        if( !isset($postData['action']) ) 
                        {
                            $response = '{"responseEvent":"error","responseType":"","serverResponse":"incorrect action"}';
                            exit( $response );
                        }
                        if( $reqId == 'SpinRequest' ) 
                        {
                            if( $postData['data']['coin'] <= 0 || $postData['data']['bet'] <= 0 ) 
                            {
                                $response = '{"responseEvent":"error","responseType":"","serverResponse":"invalid bet state"}';
                                exit( $response );
                            }
                            if( $slotSettings->GetBalance() < ($postData['data']['coin'] * $postData['data']['bet'] * 10) && $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') <= 0 ) 
                            {
                                $response = '{"responseEvent":"error","responseType":"","serverResponse":"invalid balance"}';
                                exit( $response );
                            }
                        }
                        $lastEvent = $slotSettings->GetHistory();
                        switch( $reqId ) 
                        {
                            case 'APIVersionRequest':
                                $result_tmp[] = '{"action":"APIVersionResponse","result":true,"sesId":false,"data":{"router":"v3.12","transportConfig":{"reconnectTimeout":500000000000}}}';
                                break;
                            case 'PingRequest':
                                $result_tmp[] = '{"action": "PingResponse", "result": "true", "sesId": "false", "data": ""}';
                                break;                            
                            case 'CheckBrokenGameRequest':
                                if( $lastEvent != 'NULL' && ($lastEvent->serverResponse->bonusQuantities[0]->bonusPlayedCount < $lastEvent->serverResponse->bonusQuantities[0]->bonusTotalCount || $lastEvent->serverResponse->bonusQuantities[1]->bonusPlayedCount < $lastEvent->serverResponse->bonusQuantities[1]->bonusTotalCount) ) 
                                {
                                    $result_tmp[] = '{"action":"CheckBrokenGameResponse","data":{"restoredGameId":458},"result":true,"sesId":"10000205144"}';
                                }
                                else
                                {
                                    $result_tmp[] = '{"action":"CheckBrokenGameResponse","result":"true","sesId":"false","data":{"haveBrokenGame":"false"}}';
                                }
                                break;
                            case 'AuthRequest':     
                                $basicResponse = '';
                                $slotSettings->SetGameData($slotSettings->slotId . 'KeysLeft', 6);
                                if( $lastEvent != 'NULL' && ($lastEvent->serverResponse->bonusQuantities[0]->bonusPlayedCount < $lastEvent->serverResponse->bonusQuantities[0]->bonusTotalCount || $lastEvent->serverResponse->bonusQuantities[1]->bonusPlayedCount < $lastEvent->serverResponse->bonusQuantities[1]->bonusTotalCount) ) 
                                {
                                    $serverResponse = $lastEvent->serverResponse;
                                        
                                    $responseData = [
                                        'bonusQuantities' => $serverResponse->bonusQuantities,
                                        'extraWin' => $serverResponse->extraWin,
                                        'keysLeft' => $slotSettings->GetGameData($slotSettings->slotId . 'KeysLeft'),
                                        'matrix' => $serverResponse->basicMatrix,
                                        'middleCollectorMultiplier' => $slotSettings->GetGameData($slotSettings->slotId . 'MiddleCollectMultiplier'),
                                        'nextBonusType'=> $serverResponse->nextBonusType,
                                        'roundId' => 0,
                                        'series' => $serverResponse->series,
                                        'win' => $serverResponse->win,
                                        'winsMatrix' => $serverResponse->basicWinsMatrix,                                        
                                    ];
                                    if($responseData['middleCollectorMultiplier'] == 0)
                                        unset($responseData['middleCollectorMultiplier']);
                                    $basicResponse = json_encode($responseData);

                                    if(isset($serverResponse->initialWinsMatrix))
                                    {
                                        $restoreData = json_encode([
                                            'bonusResponses' => [
                                                [
                                                    'bonusQuantities' => $serverResponse->bonusQuantities,
                                                    'extraWin' => $slotSettings->GetGameData($slotSettings->slotId . 'LastExtraWin'),
                                                    'initialMatrix' => $serverResponse->matrix,
                                                    'initialWinsMatrix' => $serverResponse->initialWinsMatrix,
                                                    'matrix' => $serverResponse->matrix,
                                                    'roundId' => 0,
                                                    'nextBonusType'=> $serverResponse->nextBonusType,
                                                    'currentBonusType'=> $serverResponse->currentBonusType,
                                                    'series' => $serverResponse->series,
                                                    'win' => $serverResponse->win,
                                                    'winsMatrix' => $serverResponse->winsMatrix,
                                                    'spinLog' => $serverResponse->spinLog
                                                ]
                                            ],
                                            'recoveryStackSize' => 1
                                        ]);
                                    }
                                    else
                                        $restoreData = '{"bonusResponses":[],"recoveryStackSize":0}';
                                    
                                }
                                else
                                {
                                    $basicResponse = '{"bonusQuantities":[{"bonusMoreCount":0,"bonusPlayedCount":0,"bonusTotalCount":0,"bonusType":0},{"bonusMoreCount":0,"bonusPlayedCount":0,"bonusTotalCount":0,"bonusType":1}],"canGamble":false,"extraWin":0,"keysLeft":6,"matrix":[[5,2,3],[10,3,4],[6,6,13],[9,9,9],[4,1,1]],"nextBonusType":-1,"rollingMatrix":[[9,5,11,9,1,1],[6,4,4,4,11,1,9,8,12,7],[4,10,5,8,7,5,6,8,7,2,11,15,10,5],[6,4,4,10,10,10,3,9,8,10,7,1,1,1,9,1,1,1],[9,9,4,11,3,10,5,8,3,7,6,2,8,7,1,11,2,10,9,8,11,7]],"roundId":0,"series":[],"win":0,"winsMatrix":[[-1,-1,-1],[-1,-1,-1],[-1,-1,-1],[-1,-1,-1],[-1,-1,-1]],"winsRollingMatrix":[[-1,-1,-1,-1,-1,-1],[-1,-1,-1,-1,-1,-1,-1,-1,-1,-1],[-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1],[-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1],[-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1]]}';
                                    $restoreData = '{"bonusResponses":[],"recoveryStackSize":0}';
                                }
                                if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') == $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') ) 
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                                }
                                $defaultBet = $slotSettings->GetGameData($slotSettings->slotId . 'BetLine');
                                if($defaultBet == 0)
                                    $defaultBet = 1;                                

                                $result_tmp[0] = '{"action":"AuthResponse","data":{"accountData":{"activeBet":'.$defaultBet.',"activeCoinValue":0.01,"activeLines":1,"availableBets":[1,2,3,4,5,10,15,20,25],"availableCoinValues":[0.01],"availableLines":[1],"balance":'.$slotSettings->GetBalance().',"betMultiplier":20.0,"credits":'.($slotSettings->GetBalance() * 100).',"currency":"","rtp":"96.42","sweepMode":false},"buyItems":[],"gameData":{"basicResponse":'.$basicResponse.',"figures":[{"id":0,"mask":2,"pays":[0,0,0,0,0]},{"id":1,"mask":4,"pays":[0,0,12,14,16]},{"id":2,"mask":4,"pays":[0,0,10,12,14]},{"id":3,"mask":4,"pays":[0,0,8,10,12]},{"id":4,"mask":4,"pays":[0,0,6,8,10]},{"id":5,"mask":4,"pays":[0,0,4,6,8]},{"id":6,"mask":4,"pays":[0,0,2,4,6]},{"id":7,"mask":4,"pays":[0,0,2,4,6]},{"id":8,"mask":4,"pays":[0,0,2,4,6]},{"id":9,"mask":4,"pays":[0,0,2,4,6]},{"id":10,"mask":4,"pays":[0,0,2,4,6]},{"id":11,"mask":4,"pays":[0,0,2,4,6]},{"id":12,"mask":9,"pays":[0,0,0,0,0]},{"id":13,"mask":64,"pays":[0,0,0,0,0]},{"id":14,"mask":64,"pays":[0,0,0,0,0]},{"id":15,"mask":64,"pays":[0,0,0,0,0]},{"id":16,"mask":25,"pays":[0]},{"id":17,"mask":65561,"pays":[0]},{"id":18,"mask":131353,"pays":[0]},{"id":19,"mask":27,"pays":[0]},{"id":20,"mask":4121,"pays":[0]},{"id":21,"mask":57,"pays":[0]},{"id":22,"mask":16441,"pays":[0]},{"id":23,"mask":32825,"pays":[0]},{"id":24,"mask":49209,"pays":[0]},{"id":25,"mask":57,"pays":[10]},{"id":26,"mask":16441,"pays":[30]},{"id":27,"mask":32825,"pays":[500]},{"id":28,"mask":49209,"pays":[1000]},{"id":100,"mask":0,"pays":[0]}],"keysStates":[{"bet":1,"denominator":0.01,"keysLeft":6},{"bet":2,"denominator":0.01,"keysLeft":6},{"bet":3,"denominator":0.01,"keysLeft":6},{"bet":4,"denominator":0.01,"keysLeft":6},{"bet":5,"denominator":0.01,"keysLeft":6},{"bet":10,"denominator":0.01,"keysLeft":6},{"bet":15,"denominator":0.01,"keysLeft":6},{"bet":20,"denominator":0.01,"keysLeft":6},{"bet":30,"denominator":0.01,"keysLeft":6},{"bet":40,"denominator":0.01,"keysLeft":6},{"bet":50,"denominator":0.01,"keysLeft":6},{"bet":100,"denominator":0.01,"keysLeft":6},{"bet":200,"denominator":0.01,"keysLeft":6},{"bet":300,"denominator":0.01,"keysLeft":6},{"bet":400,"denominator":0.01,"keysLeft":6},{"bet":500,"denominator":0.01,"keysLeft":6}],"lines":[],"replay_leaderboard_show_status":false,"replay_status":false, "restoreData":'.$restoreData.'},"gameServerId":"gs-5.122.121-skg-rc-skg-1-512","jackpotsData":{"enabled":false},"offersData":[],"snivyId":"snivy-30.36.44-rc-skg-0"},"result":true,"sesId":"10000086604"}';
                                break;
                            case 'BalanceRequest':
                                $result_tmp[0] = '{"action":"BalanceResponse","result":"true","sesId":"10000373695","data":{"totalAmount":"' . $slotSettings->GetBalance() . '","currency":" "}}';
                                break;
                            case 'TakeWinRequest':
                                $totalBonusWin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin');
                                $activeCoin = $slotSettings->GetGameData($slotSettings->slotId . 'activeCoinValue');
                                $balance = $slotSettings->GetBalance();
                                $result_tmp[] = '{"action":"BalanceResponse","data":{"activeCoinValue":'.$activeCoin.',"balance":'.$balance.',"credits":'.($balance * 100).'},"result":true,"sesId":"10000000223"}';
                                $result_tmp[] = ':::{"action":"TakeWinResponse","data":{"winnerPaid":'.($totalBonusWin * 100).'},"result":true,"sesId":"10000000223"}';                                
                                break;
                            case 'GameStartBonusRequest':
                            case 'GameStartBasicRequest':                                
                                $postData['slotEvent'] = 'bet';
                                $bonusMpl = 1;
                                $linesId = $slotSettings->Ways243ToLine();
                                $lines = count($linesId);
                                $betLine = $postData['data']['activeCoinValue'] * $postData['data']['activeBet'];

                                $lastBetLine = $slotSettings->GetGameData($slotSettings->slotId . 'BetLine');
                                if($postData['data']['activeBet'] != $lastBetLine)
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'KeysLeft', 6);
                                }
                                $slotSettings->SetGameData($slotSettings->slotId . 'BetLine', $postData['data']['activeBet']);
                                $slotSettings->SetGameData($slotSettings->slotId . 'activeCoinValue', $postData['data']['activeCoinValue']);
                                $allbet = $betLine * 20;
                                if( !isset($postData['slotEvent']) ) 
                                {
                                    $postData['slotEvent'] = 'bet';
                                }
                                if( $reqId == 'GameStartBonusRequest' ) 
                                {
                                    if($postData['data']['bonusType'] == 0)
                                        $postData['slotEvent'] = $FREESPIN_TAG;
                                    else
                                        $postData['slotEvent'] = $HOLDNLINK_TAG;
                                }
                                else if($reqId == 'HoldnLinkRequest')
                                {
                                    $postData['slotEvent'] = $HOLDNLINK_TAG;
                                }
                                if( $postData['slotEvent'] == 'bet' ) 
                                {
                                    $slotSettings->SetBalance(-1 * $allbet, $postData['slotEvent']);
                                    $bankSum = $allbet / 100 * $slotSettings->GetPercent();
                                    $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $bankSum, $postData['slotEvent']);
                                    $slotSettings->UpdateJackpots($allbet);
                                    $slotSettings->SetBet($allbet);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'Mpl', 1);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeStartWin', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BonusSymbol', -1);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeBalance', 0);                                    
                                    $slotSettings->SetGameData($slotSettings->slotId . 'lastHoldnLinkCount', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGamesHoldnLink', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'LastExtraWin', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'HoldnPlusCount', 3);
                                    if($slotSettings->GetGameData($slotSettings->slotId . 'KeysLeft') < 1)
                                        $slotSettings->SetGameData($slotSettings->slotId . 'KeysLeft', 6);
                                }
                                else if($postData['slotEvent'] == $FREESPIN_TAG)
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);
                                    $bonusMpl = $slotSettings->GetGameData($slotSettings->slotId . 'Mpl');
                                }
                                else if($postData['slotEvent'] == $HOLDNLINK_TAG)
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink') + 1);
                                    $bonusMpl = 1;
                                }
                                
                                $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $allbet);
                                $winType = $winTypeTmp[0];
                                $spinWinLimit = $winTypeTmp[1];
                                if($debug && $postData['slotEvent'] == 'bet')
                                    $winType = "bonus";
                                
                                if($postData['slotEvent'] == $FREESPIN_TAG)
                                {
                                    $winType = 'win';
                                    if($spinWinLimit == 0)
                                        $spinWinLimit = 1;
                                }

                                $keysLeft = $slotSettings->GetGameData($slotSettings->slotId . 'KeysLeft');
                                if($keysLeft == 0)
                                    $slotSettings->SetGameData($slotSettings->slotId . 'KeysLeft', 6);
                                    
                                $minReels = [];
                                $minTotalWin = -1;
                                $minLineWins = [];
                                $minWinsMatrix = [];
                                $minInitialWinsMatrix = [];
                                $minSpinLog = [];
                                $minScatterCount = 0;
                                $minHoldLinkCount = 0;
                                $minHoldnWin = 0;
                                $minKeysLeft = 0;
                                $minfreespinWin = 0;
                                $minChest = [];

                                $spinAcquired = false;
                                $lastHoldnLinkCount = $slotSettings->GetGameData($slotSettings->slotId . 'lastHoldnLinkCount');
                                $freespinWin = 0;

                                $wild = [0];
                                $scatter = 12;
                                $chestSym = 18;
                                $multiplierSym = 19;
                                $plusSym = 20;
                                $sphere = 17;
                                $diamonds = [21, 22, 23, 24];
                                $holdnSyms = [17, 18, 19, 20, 21, 22, 23, 24];
                                $keys = [13, 14, 15];
                                $availableSphereValues = [8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,12,12,12,12,12,12,12,12,12,14,14,14,14,14,14,14,14,14,20,20,20,20,20,20,24,24,24,24,24,25,25,25,25,25,25,25,30,30,30,30,60,60,60,75,80,90];
                                $spinLog = [];
                                $keyGot = false;
                                for( $i = 0; $i <= 500; $i++ ) 
                                {
                                    $totalWin = 0;
                                    $freespinWin = 0;
                                    $spinLog = [];
                                    $chest = [];
                                    $multiplier = [];
                                    $holdnlinkCount = 0;
                                    $keysLeft = $slotSettings->GetGameData($slotSettings->slotId . 'KeysLeft');
                                    if($postData['slotEvent'] != 'bet')
                                    {
                                        $totalWin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin');
                                    }
                                    $holdnWin = 0;
                                    $lineWins = [];
                                    $winsMatrix = [[-1,-1,-1],[-1,-1,-1],[-1,-1,-1],[-1,-1,-1],[-1,-1,-1]];
                                    $initialWinsMatrix = [];
                                    $initialMatrix = [];
                                    $cWins = [];
                                    
                                    $reels = $slotSettings->GetReelStrips($winType, $postData['slotEvent']);             
                                    if($postData['slotEvent'] == 'bet')
                                    {
                                        //check key reel
                                        if($reels['reel3'][0] == $keys[0] && $reels['reel3'][1] == $keys[1] && $reels['reel3'][2] == $keys[2])
                                        {
                                            $keyGot = true;
                                            if($keysLeft < 2 && $winType != 'bonus')
                                            {
                                                $reels['reel3'][0] = 4;
                                                $reels['reel3'][1] = 5;
                                                $reels['reel3'][2] = 6;
                                                $keyGot = false;
                                            }
                                            else
                                                $keysLeft--;       
                                        }
                                    }

                                    if( $postData['slotEvent'] === $HOLDNLINK_TAG ) 
                                    {
                                        $lastReels = $slotSettings->GetGameData($slotSettings->slotId . 'LastReels');
                                        if(is_array($lastReels) && count($lastReels) > 0)
                                        {
                                            for($r = 1; $r <= 5; $r++)
                                            for($c = 0; $c < 3; $c++)
                                            {
                                                if($lastReels['reel'.$r][$c] == $sphere)
                                                    $reels['reel'.$r][$c] = $sphere;
                                                if(in_array($lastReels['reel'.$r][$c], $diamonds))
                                                    $reels['reel'.$r][$c] = $lastReels['reel'.$r][$c];
                                            }
                                        }                                        

                                        $probability = 5;
                                        if(rand(0, 100) < $probability) //give holdnk spin count plus symbol with low probability
                                        {
                                            $r = rand(1, 5);
                                            $c = rand(0, 2);
                                            $count = 0;
                                            while(($reels['reel'.$r][$c] == $sphere || in_array($reels['reel'.$r][$c], $diamonds)) && $count < 30)
                                            {
                                                $r = rand(1, 5);
                                                $c = rand(0, 2);
                                                $count++;
                                            }
                                            if($count < 30)
                                                $reels['reel'.$r][$c] = $plusSym;
                                        }

                                        if(rand(0, 100) < $probability) //give holdnk chest symbol with low probability
                                        {
                                            $r = rand(1, 5);
                                            $c = rand(0, 2);
                                            $count = 0;
                                            while(($reels['reel'.$r][$c] == $sphere || in_array($reels['reel'.$r][$c], $diamonds)) && $count < 30)
                                            {
                                                $r = rand(1, 5);
                                                $c = rand(0, 2);
                                                $count++;
                                            }
                                            if($count < 30)
                                            {
                                                $reels['reel'.$r][$c] = $chestSym;
                                                $chest = [$r - 1, $c];
                                            }
                                        }

                                        if(rand(0, 100) < $probability) //give holdnk multiplier symbol with low probability
                                        {
                                            $r = rand(1, 5);
                                            $c = rand(0, 2);
                                            $count = 0;
                                            while(($reels['reel'.$r][$c] == $sphere || in_array($reels['reel'.$r][$c], $diamonds)) && $count < 30)
                                            {
                                                $r = rand(1, 5);
                                                $c = rand(0, 2);
                                                $count++;
                                            }
                                            if($count < 30)
                                            {                                                                                
                                                $reels['reel'.$r][$c] = $multiplierSym;                                                
                                                $multiplier = [[$r - 1, $c], rand(2, 3)];
                                                $winsMatrix[$r-1][$c] = $multiplier[1];
                                            }
                                        }
                                        
                                        if(rand(0, 100) < 30)
                                        {
                                            $miniCnt = 0;
                                            $minorCnt = 0;
                                            $majorCnt = 0;
                                            $megaCnt = 0;
                                            for($r = 0; $r < 5; $r++)
                                                for($c = 0; $c < 3; $c++)
                                                {
                                                    if($reels['reel'.($r+1)][$c] == $diamonds[0])
                                                        $miniCnt++;
                                                    else if($reels['reel'.($r+1)][$c] == $diamonds[1])
                                                        $minorCnt++;
                                                    else if($reels['reel'.($r+1)][$c] == $diamonds[2])
                                                        $majorCnt++;
                                                    else if($reels['reel'.($r+1)][$c] == $diamonds[3])
                                                        $megaCnt++;
                                                }

                                            $r = rand(1, 5);
                                            $c = rand(0, 2);
                                            $count = 0;
                                            while(($reels['reel'.$r][$c] == $sphere || in_array($reels['reel'.$r][$c], $diamonds)) && $count < 30)
                                            {
                                                $r = rand(1, 5);
                                                $c = rand(0, 2);
                                                $count++;
                                            }
                                            if($count < 10)
                                            {
                                                $a = 0;
                                                if($miniCnt > 0)
                                                    $a = 1;
                                                if($minorCnt >= 2)
                                                    $a = 2;
                                                if($majorCnt >= 3)
                                                    $a = 3;                

                                                $reels['reel'.$r][$c] = $diamonds[rand($a,3)];                                                
                                            }
                                        }
                                    }
                                    //check sphere value for holdnLink
                                    for($r = 0; $r < 5; $r++)
                                        for($c = 0; $c < 3; $c++)
                                        {
                                            if($reels['reel' .($r+1)][$c] == $sphere)
                                                $winsMatrix[$r][$c] = $availableSphereValues[rand(0, count($availableSphereValues) - 1)];
                                        }
                                    if($postData['slotEvent'] == $HOLDNLINK_TAG)
                                    {
                                        $lastWinsMatrix = $slotSettings->GetGameData($slotSettings->slotId . 'WinMatrix');
                                        for($r = 0; $r < 5; $r++)
                                            for($c = 0; $c < 3; $c++)
                                            {
                                                if($lastWinsMatrix[$r][$c] != -1)
                                                {
                                                    $winsMatrix[$r][$c] = $lastWinsMatrix[$r][$c];
                                                }
                                            }
                                    }                                                                      

                                    if( $postData['slotEvent'] != $HOLDNLINK_TAG ) 
                                    {       
                                        for( $j = 0; $j < count($slotSettings->SymbolGame); $j++ ) 
                                        {                                            
                                            $csym = $slotSettings->SymbolGame[$j];                                                
                                            if( $csym == $scatter || !isset($slotSettings->Paytable['SYM_' . $csym]) ) 
                                            {
                                            }
                                            else
                                            {
                                                $wscc = 0;
                                                $cl = 0;
                                                for( $swc = 1; $swc <= 5; $swc++ ) 
                                                {
                                                    $isNext = false;
                                                    if( $reels['reel' . $swc][0] == $csym || in_array($reels['reel' . $swc][0], $wild) ) 
                                                    {
                                                        $isNext = true;
                                                    }
                                                    if( $reels['reel' . $swc][1] == $csym || in_array($reels['reel' . $swc][1], $wild) ) 
                                                    {
                                                        $isNext = true;
                                                    }
                                                    if( $reels['reel' . $swc][2] == $csym || in_array($reels['reel' . $swc][2], $wild) ) 
                                                    {
                                                        $isNext = true;
                                                    }
                                                    if( $isNext ) 
                                                    {
                                                        $wscc++;
                                                        if( $wscc == 3 || $wscc == 2 ) 
                                                        {
                                                            $cl = 0;
                                                        }
                                                        if( $wscc == 4 ) 
                                                        {
                                                            $cl = 1;
                                                        }
                                                        if( $wscc == 5 ) 
                                                        {
                                                            $cl = 2;
                                                        }
                                                    }
                                                    else
                                                    {
                                                        break;
                                                    }
                                                }
                                                for( $k = 0; $k < count($linesId[$cl]); $k++ ) 
                                                {
                                                    $lineWin = [];
                                                    $cWins[$k] = 0;
                                                    $s = [];
                                                    $p0 = $linesId[$cl][$k][0] - 1;
                                                    $p1 = $linesId[$cl][$k][1] - 1;
                                                    $p2 = $linesId[$cl][$k][2] - 1;
                                                    $p3 = $linesId[$cl][$k][3] - 1;
                                                    $p4 = $linesId[$cl][$k][4] - 1;
                                                    $s[0] = $reels['reel1'][$p0];
                                                    $s[1] = $reels['reel2'][$p1];
                                                    $s[2] = $reels['reel3'][$p2];
                                                    $s[3] = $p3 != -1 ? $reels['reel4'][$p3] : -1;
                                                    $s[4] = $p4 != -1 ? $reels['reel5'][$p4] : -1;
                                                    
                                                    if($wscc == 2)
                                                    {
                                                        if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) ) 
                                                        {
                                                            $mpl = 1;
                                                            if( in_array($s[0], $wild) && in_array($s[1], $wild) ) 
                                                            {
                                                                $mpl = 0;
                                                            }
                                                            else if( in_array($s[0], $wild) || in_array($s[1], $wild) ) 
                                                            {
                                                                $mpl = $slotSettings->slotWildMpl;
                                                            }
                                                            $tmpWin = $slotSettings->Paytable['SYM_' . $csym][2] * $betLine * $mpl * $bonusMpl;
                                                            if( $cWins[$k] < $tmpWin ) 
                                                            {
                                                                $cWins[$k] = $tmpWin;
                                                                $lineWin = ['figureId' => $csym, 'lineId' => $k, 'mask' => 0, 'positions' => [[0, $p0], [1, $p1]], 'profit' => $tmpWin * 100];
                                                            }
                                                        }
                                                    }
                                                    else if($wscc == 3)
                                                    {
                                                        if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) ) 
                                                        {
                                                            $mpl = 1;
                                                            if( in_array($s[0], $wild) && in_array($s[1], $wild) && in_array($s[2], $wild) ) 
                                                            {
                                                                $mpl = 0;
                                                            }
                                                            else if( in_array($s[0], $wild) || in_array($s[1], $wild) || in_array($s[2], $wild) ) 
                                                            {
                                                                $mpl = $slotSettings->slotWildMpl;
                                                            }
                                                            $tmpWin = $slotSettings->Paytable['SYM_' . $csym][3] * $betLine * $mpl * $bonusMpl;
                                                            if( $cWins[$k] < $tmpWin ) 
                                                            {
                                                                $cWins[$k] = $tmpWin;
                                                                $lineWin = ['figureId' => $csym, 'lineId' => $k, 'mask' => 0, 'positions' => [[0, $p0], [1, $p1], [2, $p2]], 'profit' => $tmpWin * 100];
                                                            }
                                                        }
                                                    }
                                                    if($wscc == 4)
                                                    {
                                                        if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) ) 
                                                        {
                                                            $mpl = 1;
                                                            if( in_array($s[0], $wild) && in_array($s[1], $wild) && in_array($s[2], $wild) && in_array($s[3], $wild) ) 
                                                            {
                                                                $mpl = 0;
                                                            }
                                                            else if( in_array($s[0], $wild) || in_array($s[1], $wild) || in_array($s[2], $wild) || in_array($s[3], $wild) ) 
                                                            {
                                                                $mpl = $slotSettings->slotWildMpl;
                                                            }
                                                            $tmpWin = $slotSettings->Paytable['SYM_' . $csym][4] * $betLine * $mpl * $bonusMpl;
                                                            if( $cWins[$k] < $tmpWin ) 
                                                            {
                                                                $cWins[$k] = $tmpWin;
                                                                $lineWin = ['figureId' => $csym, 'lineId' => $k, 'mask' => 0, 'positions' => [[0, $p0], [1, $p1], [2, $p2], [3, $p3]], 'profit' => $tmpWin * 100];
                                                            }
                                                        }
                                                    }
                                                    else if($wscc == 5)
                                                    {
                                                        if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) ) 
                                                        {
                                                            $mpl = 1;
                                                            if( in_array($s[0], $wild) && in_array($s[1], $wild) && in_array($s[2], $wild) && in_array($s[3], $wild) && in_array($s[4], $wild) ) 
                                                            {
                                                                $mpl = 0;
                                                            }
                                                            else if( in_array($s[0], $wild) || in_array($s[1], $wild) || in_array($s[2], $wild) || in_array($s[3], $wild) || in_array($s[4], $wild) ) 
                                                            {
                                                                $mpl = $slotSettings->slotWildMpl;
                                                            }
                                                            $tmpWin = $slotSettings->Paytable['SYM_' . $csym][5] * $betLine * $mpl * $bonusMpl;
                                                            if( $cWins[$k] < $tmpWin ) 
                                                            {
                                                                $cWins[$k] = $tmpWin;
                                                                $lineWin = ['figureId' => $csym, 'lineId' => $k, 'mask' => 0, 'positions' => [[0, $p0], [1, $p1], [2, $p2], [3, $p3], [4, $p4]], 'profit' => $tmpWin * 100];
                                                            }
                                                        }
                                                    }
                                                    if( $cWins[$k] > 0 && count($lineWin) > 0 ) 
                                                    {
                                                        array_push($lineWins, $lineWin);
                                                        $totalWin += $cWins[$k];
                                                    }
                                                }                                                
                                            }
                                        }

                                        $tempHoldnCount = 0;
                                        for($r = 0; $r < 5; $r++)
                                        for($c = 0; $c < 3; $c++)
                                        {
                                            if(in_array($reels['reel' .($r+1)][$c], $holdnSyms))
                                            {
                                                $holdnPos[] = [$r, $c];
                                                $tempHoldnCount++;
                                            }
                                        }
                                        //add sphere win in the freespin
                                        if($tempHoldnCount < $keysLeft)
                                        {
                                            for($r = 0; $r < 5; $r++)
                                            for($c = 0; $c < 3; $c++)
                                            {
                                                if($reels['reel' .($r+1)][$c] == $sphere && $winsMatrix[$r][$c] > 0)
                                                {
                                                    if($postData['slotEvent'] == $FREESPIN_TAG)
                                                        $winsMatrix[$r][$c] *= 3;
                                                    $times = $winsMatrix[$r][$c];
                                                    $profit = $times * $betLine;
                                                    $lineWin = ['figureId' => 17, 'lineId' => 255, 'mask' => 4, 'positions' => [[$r, $c]], 'profit' => $profit * 100];
                                                    array_push($lineWins, $lineWin);
                                                    $totalWin += $profit;
                                                }                                                
                                            }
                                        }
                                    }
                                    else
                                    {
                                        for($r = 0; $r < 5; $r++)
                                        for($c = 0; $c < 3; $c++)
                                        {
                                            if($reels['reel'.($r+1)][$c] == $plusSym)
                                            {
                                                $log = ['amount' => 0, 'dst_pos' => [$c * 5 + $r, 0], 'src_pos' => [$c * 5 + $r, 0]];
                                                array_push($spinLog, $log);
                                                $freespinWin = 1;
                                            }
                                        }

                                        $initialWinsMatrix = $winsMatrix;
                                        if(count($multiplier) > 0)
                                        {
                                            $multiplierPos = $multiplier[0];
                                            $times = $multiplier[1];
                                            for($r = 0; $r < 5; $r++)
                                            for($c = 0; $c < 3; $c++)
                                            {
                                                if($reels['reel'.($r+1)][$c] == $sphere)
                                                {
                                                    $amount = $winsMatrix[$r][$c] * $betLine * ($times - 1) / $postData['data']['activeBet'];
                                                    $winsMatrix[$r][$c] *= $times;
                                                    $log = ['amount' => (int)($amount * 100), 'src_pos' => [$multiplierPos[1] * 5 + $multiplierPos[0], 0], 'dst_pos' => [$c * 5 + $r, 0]];
                                                    array_push($spinLog, $log);
                                                }
                                            }
                                            $log = ['amount' => 0, 'src_pos' => [$multiplierPos[1] * 5 + $multiplierPos[0], 0], 'dst_pos' => [$multiplierPos[1] * 5 + $multiplierPos[0], 0]];
                                            array_push($spinLog, $log);                                                    
                                            $winsMatrix[$multiplierPos[0]][$multiplierPos[1]] = -1;
                                        }

                                        if(count($chest) > 0)
                                        {
                                            $chestSum = 0;
                                            for($r = 0; $r < 5; $r++)
                                            for($c = 0; $c < 3; $c++)
                                            {
                                                if($reels['reel'.($r+1)][$c] == $sphere)
                                                {
                                                    $chestSum += $winsMatrix[$r][$c];
                                                    $profit = $winsMatrix[$r][$c] * $betLine;
                                                    $log = ['amount' => $winsMatrix[$r][$c], 'dst_pos' => [$chest[1] * 5 + $chest[0], 0], 'src_pos' => [$c * 5 + $r, 0]];
                                                    array_push($spinLog, $log);
                                                    $winsMatrix[$r][$c] = -1;
                                                }
                                            }
                                            $winsMatrix[$chest[0]][$chest[1]] = $chestSum;
                                        }
                                    }

                                    $scattersCount = 0;
                                    $scatterPos = [];
                                    for($r = 0; $r < 5; $r++)
                                        for($c = 0; $c < 3; $c++)
                                        {
                                            if($reels['reel'.($r+1)][$c] == $scatter)
                                            {                                                
                                                $scattersCount++;
                                            }
                                        }
                                    $scatterWin = 0;
                                    if($scattersCount > 2)
                                    {
                                        if($winType != 'bonus')
                                            continue;
                                        for($r = 0; $r < 5; $r++)
                                        for($c = 0; $c < 3; $c++)
                                        {
                                            if($reels['reel'.($r+1)][$c] == $scatter)
                                            {
                                                $winsMatrix[$r][$c] = $scatter;
                                                $scatterPos[] = [$r, $c];                                                
                                            }
                                        }
                                        $freespinWin = 10;
                                        if($scattersCount == 4)
                                            $freespinWin = 15;
                                        else if($scattersCount == 5)
                                            $freespinWin = 20;
                                        $scatterWin = $slotSettings->Paytable['SYM_'.$scatter][$scattersCount] * $allbet;
                                        $lineWin = ['figureId' => $scatter, 'lineId' => 255, 'mask' => 2, 'positions' => $scatterPos, 'profit' => $scatterWin * 100];
                                        array_push($lineWins, $lineWin);
                                    }
                                    $totalWin += $scatterWin;
                                    
                                    $holdnPos = [];
                                    for($r = 0; $r < 5; $r++)
                                        for($c = 0; $c < 3; $c++)
                                        {
                                            if(in_array($reels['reel' .($r+1)][$c], $holdnSyms))
                                            {
                                                $holdnPos[] = [$r, $c];
                                                $holdnlinkCount++;
                                            }
                                        }
                                    
                                    if($holdnlinkCount >= $keysLeft || $postData['slotEvent'] == $HOLDNLINK_TAG)
                                    {
                                        if($scattersCount > 2 && $postData['slotEvent'] == 'bet') //prevent holdn and freespin together
                                            continue;
                                        if($lastHoldnLinkCount == 0)
                                        {
                                            $lineWin = ['figureId' => 16, 'lineId' => 255, 'mask' => 2, 'positions' => $holdnPos, 'profit' => 0];
                                            array_push($lineWins, $lineWin);
                                        }

                                        for($r = 0; $r < 5; $r++)
                                            for($c = 0; $c < 3; $c++)
                                            {
                                                $times = $winsMatrix[$r][$c];
                                                if($times != -1)
                                                    $holdnWin += $times * $betLine;
                                            }
                                        
                                        //check jackpot win
                                        $miniCnt = 0;
                                        $minorCnt = 0;
                                        $majorCnt = 0;
                                        $megaCnt = 0;
                                        for($r = 0; $r < 5; $r++)
                                            for($c = 0; $c < 3; $c++)
                                            {
                                                if($reels['reel'.($r+1)][$c] == $diamonds[0])
                                                    $miniCnt++;
                                                else if($reels['reel'.($r+1)][$c] == $diamonds[1])
                                                    $minorCnt++;
                                                else if($reels['reel'.($r+1)][$c] == $diamonds[2])
                                                    $majorCnt++;
                                                else if($reels['reel'.($r+1)][$c] == $diamonds[3])
                                                    $megaCnt++;
                                            }
                                        if($megaCnt >= 5)
                                        {
                                            $holdnWin += (int)($megaCnt / 5) * 1000 * $allbet;
                                        }
                                        if($majorCnt >= 3)
                                        {
                                            $holdnWin += (int)($majorCnt / 3) * 500 * $allbet;
                                        }
                                        if($minorCnt >= 2)
                                        {
                                            $holdnWin += (int)($minorCnt / 2) * 30 * $allbet;
                                        }
                                        if($miniCnt > 0)
                                        {
                                            $holdnWin += $miniCnt * 10 * $allbet;
                                        }
                                    }

                                    $totalWin += $holdnWin;
                                    if($minTotalWin == -1 && !$keyGot || ($totalWin > 0 && $totalWin < $minTotalWin) && !$keyGot)
                                    {
                                        $minTotalWin = $totalWin;
                                        $minReels = $reels;
                                        $minLineWins = $lineWins;
                                        $minScatterCount = $scattersCount;
                                        $minHoldLinkCount = $holdnlinkCount;
                                        $minWinsMatrix = $winsMatrix;
                                        $minHoldnWin = $holdnWin;
                                        $minKeysLeft = $keysLeft;
                                        $minfreespinWin = $freespinWin;
                                        $minSpinLog = $spinLog;
                                        $minInitialWinsMatrix = $initialWinsMatrix;
                                        $minChest = $chest;
                                    }

                                    if($debug)
                                    {
                                        $spinAcquired = true;
                                        break;
                                    }
                                    if($totalWin < $spinWinLimit && $winType != 'none' && $totalWin > 0)
                                    {
                                        $spinAcquired = true;
                                        if($postData['slotEvent'] == 'bet' && $totalWin < 0.7 * $spinWinLimit)
                                            $spinAcquired = false;
                                        if($spinAcquired)
                                            break;                                        
                                    }
                                    else if($winType == 'none' && $totalWin == 0 && $holdnlinkCount < 6)
                                    {
                                        break;
                                    }
                                }

                                if($totalWin > 0 && !$spinAcquired)
                                {
                                    $lineWins = $minLineWins;
                                    $reels = $minReels;
                                    $totalWin = $minTotalWin;
                                    $scattersCount = $minScatterCount;
                                    $holdnlinkCount = $minHoldLinkCount;
                                    $winsMatrix = $minWinsMatrix;
                                    $holdnWin = $minHoldnWin;
                                    $keysLeft = $minKeysLeft;
                                    $freespinWin = $minfreespinWin;
                                    $spinLog = $minSpinLog;
                                    $initialWinsMatrix = $minInitialWinsMatrix;
                                    $chest = $minChest;
                                }

                                
                                $totalWin -= $holdnWin;

                                $freespinQuantity = [
                                    'bonusMoreCount' => 0,
                                    'bonusPlayedCount' => 0,
                                    'bonusTotalCount' => 0,
                                    'bonusType' => 0
                                ];
                                $holdnQuantity = [
                                    'bonusMoreCount' => 0,
                                    'bonusPlayedCount' => 0,
                                    'bonusTotalCount' => 0,
                                    'bonusType' => 1
                                ];

                                $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $totalWin);
                                $nextBonusType = -1;

                                $slotSettings->SetGameData($slotSettings->slotId . 'KeysLeft', $keysLeft);
                                //check holdnlink
                                $triggerHoldn = false;
                                if( $holdnlinkCount >= $keysLeft || $postData['slotEvent'] == $HOLDNLINK_TAG)
                                {                                    
                                    if($holdnlinkCount > $lastHoldnLinkCount)
                                    {
                                        if($freespinWin > 0)
                                        {
                                            $slotSettings->SetGameData($slotSettings->slotId . 'HoldnPlusCount', $slotSettings->GetGameData($slotSettings->slotId . 'HoldnPlusCount') + $freespinWin);
                                        }
                                        $moreHoldn = $slotSettings->GetGameData($slotSettings->slotId . 'HoldnPlusCount');
                                        if($lastHoldnLinkCount == 0)
                                        {
                                            //triggering holdnlink
                                            if( $postData['slotEvent'] == 'bet' ) 
                                            {
                                                $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $totalWin);
                                                $triggerHoldn = true;
                                            }
                                        }
                                        $holdnQuantity['bonusMoreCount'] = $moreHoldn;
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeGamesHoldnLink', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink') + $moreHoldn);
                                    }
                                    
                                    //remove speical symbols from holdn count
                                    for($r = 0; $r < 5; $r++)
                                        for($c = 0; $c < 3; $c++)
                                        {
                                            if($reels['reel'.($r+1)][$c] == $plusSym || $reels['reel'.($r+1)][$c] == $multiplierSym || $reels['reel'.($r+1)][$c] == $chestSym ) //these symbols are disappeared after current spin
                                                $holdnlinkCount--;
                                        }

                                    $slotSettings->SetGameData($slotSettings->slotId . 'lastHoldnLinkCount', $holdnlinkCount);

                                    $holdnQuantity['bonusPlayedCount'] = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink');
                                    $holdnQuantity['bonusTotalCount'] = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGamesHoldnLink');
                                    $nextBonusType = 1;

                                    if($holdnQuantity['bonusPlayedCount'] >= $holdnQuantity['bonusTotalCount'])
                                    {
                                        //holdn ended
                                        $minorCnt = 0;
                                        $majorCnt = 0;
                                        $megaCnt = 0;
                                        for($r = 0; $r < 5; $r++)
                                            for($c = 0; $c < 3; $c++)
                                            {
                                                $times = $winsMatrix[$r][$c];
                                                if($times != -1)
                                                {
                                                    $profit = $betLine * $times * 100;
                                                    $lineWin = ['figureId' => 17, 'lineId' => 255, 'mask' => 6, 'positions' => [[$c * 5 + $r, 0]], 'profit' => $profit];
                                                    array_push($lineWins, $lineWin);
                                                }

                                                if($reels['reel'.($r+1)][$c] == $diamonds[0])
                                                {
                                                    $profit = $allbet * 10 * 100;
                                                    $lineWin = ['figureId' => 21, 'lineId' => 255, 'mask' => 6, 'positions' => [[$c * 5 + $r, 0]], 'profit' => $profit];
                                                    array_push($lineWins, $lineWin);
                                                }                                                    
                                                else if($reels['reel'.($r+1)][$c] == $diamonds[1])
                                                {
                                                    $minorCnt++;
                                                    if($minorCnt >= 2)
                                                    {
                                                        $profit = $allbet * 30 * 100;
                                                        $lineWin = ['figureId' => 22, 'lineId' => 255, 'mask' => 6, 'positions' => [[$c * 5 + $r, 0]], 'profit' => $profit];
                                                        array_push($lineWins, $lineWin);
                                                        $minorCnt = 0;
                                                    }
                                                }                                                    
                                                else if($reels['reel'.($r+1)][$c] == $diamonds[2])
                                                {
                                                    $majorCnt++;
                                                    if($majorCnt >= 3)
                                                    {
                                                        $profit = $allbet * 500 * 100;
                                                        $lineWin = ['figureId' => 22, 'lineId' => 255, 'mask' => 6, 'positions' => [[$c * 5 + $r, 0]], 'profit' => $profit];
                                                        array_push($lineWins, $lineWin);
                                                        $majorCnt = 0;
                                                    }
                                                }                                                    
                                                else if($reels['reel'.($r+1)][$c] == $diamonds[3])
                                                {
                                                    $megaCnt++;
                                                    if($megaCnt >= 5)
                                                    {
                                                        $profit = $allbet * 1000 * 100;
                                                        $lineWin = ['figureId' => 22, 'lineId' => 255, 'mask' => 6, 'positions' => [[$c * 5 + $r, 0]], 'profit' => $profit];
                                                        array_push($lineWins, $lineWin);
                                                        $megaCnt = 0;
                                                    }
                                                }
                                            }
                                            
                                        $totalWin += $holdnWin;
                                        $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin') + $holdnWin);
                                        $slotSettings->SetGameData($slotSettings->slotId . 'lastHoldnLinkCount', 0);
                                        $slotSettings->SetGameData($slotSettings->slotId . 'HoldnPlusCount', 3);                                        
                                    }                                   
                                    
                                    $slotSettings->SetGameData($slotSettings->slotId . 'WinMatrix', $winsMatrix);
                                }

                                if( $freespinWin > 0 && $postData['slotEvent'] != $HOLDNLINK_TAG) 
                                {
                                    if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') > 0 && $postData['slotEvent'] == $FREESPIN_TAG) 
                                    {
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') + $freespinWin);
                                        $freespinQuantity['bonusMoreCount'] = $freespinWin;
                                    }
                                    else
                                    {
                                        //triggering freespin
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeStartWin', $totalWin);
                                        $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $totalWin);
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $freespinWin);
                                        $freespinQuantity['bonusMoreCount'] = $freespinWin;
                                        $nextBonusType = 0;
                                    }
                                }

                                $freespinQuantity['bonusTotalCount'] = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                                $freespinQuantity['bonusPlayedCount'] = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');                                                                                      
                                
                                $bonusQuantities = [$freespinQuantity, $holdnQuantity];

                                $action = 'GameStartBasicResponse';
                                if($reqId == 'GameStartBasicRequest')
                                    $action = 'GameStartBasicResponse';
                                else if($reqId == 'GameStartBonusRequest')
                                    $action = 'GameStartBonusResponse';
                                $reelsym = [$reels['reel1'], $reels['reel2'], $reels['reel3'], $reels['reel4'], $reels['reel5']];
                              

                                $basicWinsMatrix = $winsMatrix;
                                $basicMatrix = $reelsym;

                                if($postData['slotEvent'] == $HOLDNLINK_TAG)
                                {                                    
                                    $reelsym = [];
                                    $tmpWinMatrix = [];
                                    for($c = 0; $c < 3; $c++)
                                        for($r = 0; $r < 5; $r++)                                        
                                        {
                                            $reelsym[] = [$reels['reel'.($r+1)][$c]];
                                            $tmpWinMatrix[] = [$winsMatrix[$r][$c]];
                                        }

                                    $winsMatrix = $tmpWinMatrix;                                            
                                }

                                
                                $betData = [
                                    'action' => $action,
                                    'data' => [
                                        'bonusQuantities' => $bonusQuantities,
                                        'canGamble' => false,
                                        'extraWin' => 0,
                                        'matrix' => $reelsym,
                                        'roundId' => 0,
                                        'keysLeft'=> $keysLeft,
                                        'series' => $lineWins,
                                        'win' => $totalWin * 100,
                                        'winsMatrix' => $winsMatrix
                                    ],
                                    'result' => true,
                                    'sesId' => '10000000223'
                                ];

                                if(count($initialWinsMatrix) > 0)
                                {
                                    $tmpWinMatrix = [];
                                    for($c = 0; $c < 3; $c++)
                                        for($r = 0; $r < 5; $r++) 
                                        {
                                            $tmpWinMatrix[] = [$initialWinsMatrix[$r][$c]];
                                        }

                                    $betData['data']['initialWinsMatrix'] = $tmpWinMatrix;
                                }

                                if($triggerHoldn)
                                {
                                    //all spheres into center chest                                    
                                    $value = 0;
                                    for($r = 0; $r < 5; $r++)
                                        for($c = 0; $c < 3; $c++)
                                        {
                                            if($winsMatrix[$r][$c] > 0)
                                            {
                                                $value += $winsMatrix[$r][$c];
                                                $winsMatrix[$r][$c] = -1;
                                            }
                                        }

                                    $winsMatrix[2][1] = $value;
                                    $reels['reel3'][1] = $sphere;
                                    $slotSettings->SetGameData($slotSettings->slotId . 'lastHoldnLinkCount', 1); //set holdn count as 1
                                    $slotSettings->SetGameData($slotSettings->slotId . 'WinMatrix', $winsMatrix);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'MiddleCollectMultiplier', $value);         
                                    $slotSettings->SetGameData($slotSettings->slotId . 'KeysLeft', 6);
                                }
                                else
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'MiddleCollectMultiplier', 0);
                                }

                                if(count($chest) > 0)
                                {
                                    //set chest position as sphere with total sphere sum
                                    $reels['reel'.($chest[0] + 1)][$chest[1]] = $sphere;
                                    for($r = 0; $r < 5; $r++)
                                        for($c = 0; $c < 2; $c++)
                                        {
                                            if($chest[0] != $r && $c != $chest[1])
                                                $reels['reel'.($r+1)][$c] = 0;
                                        }
                                            
                                }

                                $slotSettings->SetGameData($slotSettings->slotId . 'LastReels', $reels);

                                if($postData['slotEvent'] == $HOLDNLINK_TAG)
                                {
                                    $nextBonusType = 1;
                                    $currentBonusType = 1;
                                    if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGamesHoldnLink') <= $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink'))
                                    {
                                        if($slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') > $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame'))
                                        {
                                            $nextBonusType = 0;
                                            $betData['data']['extraWin'] = ($slotSettings->GetGameData($slotSettings->slotId . 'TotalWin') - $slotSettings->GetGameData($slotSettings->slotId . 'FreeStartWin')) * 100;
                                            $betData['data']['win'] = $slotSettings->GetGameData($slotSettings->slotId . 'FreeStartWin') * 100;
                                        }
                                        else
                                        {
                                            $nextBonusType = -1;
                                            $betData['data']['win'] = $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin') * 100;
                                            $betData['data']['extraWin'] = 0;
                                        }                                            
                                    }
                                    else
                                    {
                                        $betData['data']['win'] = $slotSettings->GetGameData($slotSettings->slotId . 'FreeStartWin') * 100;
                                        $betData['data']['extraWin'] = ($slotSettings->GetGameData($slotSettings->slotId . 'TotalWin') - $slotSettings->GetGameData($slotSettings->slotId . 'FreeStartWin')) * 100;
                                        $betData['data']['series'] = [];
                                    }
                                    $betData['data']['currentBonusType'] = $currentBonusType;
                                    $betData['data']['spinLog'] = $spinLog;
                                }
                                else if($postData['slotEvent'] == $FREESPIN_TAG)
                                {
                                    $nextBonusType = 0;
                                    $currentBonusType = 0;
                                    if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') <= $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame'))
                                    {
                                        $nextBonusType = -1;
                                        $betData['data']['win'] = $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin') * 100;
                                        $betData['data']['extraWin'] = 0;
                                        $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                                    }
                                    else
                                    {
                                        $betData['data']['win'] = $slotSettings->GetGameData($slotSettings->slotId . 'FreeStartWin') * 100;
                                        $betData['data']['extraWin'] = ($slotSettings->GetGameData($slotSettings->slotId . 'TotalWin') - $slotSettings->GetGameData($slotSettings->slotId . 'FreeStartWin')) * 100;
                                    }
                                    $betData['data']['currentBonusType'] = $currentBonusType;
                                    unset($betData['data']['roundId']);
                                }
                                
                                $betData['data']['nextBonusType'] = $nextBonusType;

                                if($nextBonusType >= 0)
                                {
                                    $totalWin = 0; //make total win temporarily to not update balance
                                }

                                if($totalWin > 0) 
                                {
                                    $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), -1 * $totalWin);
                                    $slotSettings->SetBalance($totalWin);
                                    $slotSettings->SetWin($totalWin);                         
                                }
                                
                                $spinData = [
                                    'responseEvent' => 'spin',
                                    'responseType' => $postData['slotEvent'],
                                    'serverResponse' => [
                                        'bonusQuantities' => $bonusQuantities,
                                        'extraWin' => $betData['data']['extraWin'],
                                        'matrix' => $reelsym,
                                        'roundId' => 0,
                                        'keysLeft'=> $keysLeft,
                                        'series' => $lineWins,
                                        'win' => $totalWin * 100,
                                        'winsMatrix' => $winsMatrix,
                                        'basicWinsMatrix' => $basicWinsMatrix,
                                        'basicMatrix' => $basicMatrix,
                                        'nextBonusType' => $nextBonusType,                                        
                                        'totalWin' => $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin'),
                                        'startWin' => $slotSettings->GetGameData($slotSettings->slotId . 'FreeStartWin'),
                                        'reels' => $reels
                                    ]];
                                if(isset($betData['data']['initialWinsMatrix']))
                                    $spinData['serverResponse']['initialWinsMatrix'] = $betData['data']['initialWinsMatrix'];
                                if(isset($betData['data']['spinLog']))
                                    $spinData['serverResponse']['spinLog'] = $betData['data']['spinLog']; 
                                if(isset($betData['data']['currentBonusType']))
                                    $spinData['serverResponse']['currentBonusType'] = $betData['data']['currentBonusType'];
                                $response = json_encode($spinData);
                                if($postData['slotEvent'] == $FREESPIN_TAG || $postData['slotEvent'] == $HOLDNLINK_TAG)
                                    $allbet = 0;
                                $slotSettings->SaveLogReport($response, $allbet, $totalWin, $postData['slotEvent']);
                                $result_tmp[] = json_encode($betData);
                                break;
                        }
                        $response = implode('------', $result_tmp);
                        $slotSettings->SaveGameData();
                        $slotSettings->SaveGameDataStatic();
                        echo ':::' . $response;
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
                }, 5);
            }
            get_($request, $game);
        }
    }

}
