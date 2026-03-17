<?php 
namespace VanguardLTE\Games\BUCKSNG
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
                        $MYSTERY_TAG = "mystery";
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
                                if( $lastEvent != 'NULL' && ($lastEvent->serverResponse->bonusQuantities[0]->bonusPlayedCount < $lastEvent->serverResponse->bonusQuantities[0]->bonusTotalCount || $lastEvent->serverResponse->bonusQuantities[1]->bonusPlayedCount < $lastEvent->serverResponse->bonusQuantities[1]->bonusTotalCount)) 
                                {
                                    $result_tmp[] = '{"action":"CheckBrokenGameResponse","data":{"restoredGameId":327},"result":true,"sesId":"10000205144"}';
                                }
                                else
                                {
                                    $result_tmp[] = '{"action":"CheckBrokenGameResponse","result":"true","sesId":"false","data":{"haveBrokenGame":"false"}}';
                                }
                                break;
                            case 'AuthRequest':                                
                                                                
                                if( $lastEvent != 'NULL' && ($lastEvent->serverResponse->bonusQuantities[0]->bonusPlayedCount < $lastEvent->serverResponse->bonusQuantities[0]->bonusTotalCount || $lastEvent->serverResponse->bonusQuantities[1]->bonusPlayedCount < $lastEvent->serverResponse->bonusQuantities[1]->bonusTotalCount)) 
                                {
                                    $serverResponse = $lastEvent->serverResponse;
                                    $matrixData = json_encode($serverResponse->basicMatrix);
                                    
                                    $restoreData = [
                                        'bonusType'=> $serverResponse->nextBonusType,
                                        'extraGamesPlayed' => $serverResponse->bonusQuantities[0]->bonusPlayedCount,
                                        'extraGamesTotal' => $serverResponse->bonusQuantities[0]->bonusTotalCount,
                                        'extraWin' => $serverResponse->extraWin,
                                        'matrix' => $serverResponse->matrix,
                                        'win' => $serverResponse->win
                                    ];
                                    if($serverResponse->bonusQuantities[1]->bonusPlayedCount < $serverResponse->bonusQuantities[1]->bonusTotalCount)
                                    {
                                        $restoreData['holdNlinkGamesPlayed'] = $serverResponse->bonusQuantities[1]->bonusPlayedCount;
                                        $restoreData['holdNlinkGamesTotal'] = $serverResponse->bonusQuantities[1]->bonusTotalCount;

                                    }
                                    $restoreData = ',"restoreData":'.json_encode($restoreData);
                                }
                                else
                                {
                                    $matrixData = '[[12,4,4,12],[8,8,5,9],[5,0,11,7],[0,12,4,7],[3,3,3,0]]';
                                    $restoreData = '';
                                }
                                if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') == $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') ) 
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                                }
                                
                                $defaultBet = $slotSettings->GetGameData($slotSettings->slotId . 'BetLine');
                                if($defaultBet == 0)
                                    $defaultBet = 1;                                
                                
                                $result_tmp[0]='{"action":"AuthResponse","data":{"accountData":{"activeBet":'.$defaultBet.',"activeCoinValue":0.01,"activeLines":1,"availableBets":[1,2,3,4,5,10,15,20,30,40],"availableCoinValues":[0.01],"availableLines":[1],"balance":'.$slotSettings->GetBalance().',"betMultiplier":25.0,"credits":'.($slotSettings->GetBalance() * 100).',"currency":"","rtp":"0.00","sweepMode":false},"buyItems":[],"gameData":{"figures":[{"id":0,"mask":9,"pays":[0,0,25,50,75]},{"id":1,"mask":25,"pays":[0,0,0,0,0]},{"id":2,"mask":2,"pays":[0,0,0,0,0]},{"id":3,"mask":4,"pays":[0,3,10,30,100]},{"id":4,"mask":4,"pays":[0,2,5,15,40]},{"id":5,"mask":4,"pays":[0,2,5,15,40]},{"id":6,"mask":4,"pays":[0,0,4,10,30]},{"id":7,"mask":4,"pays":[0,0,4,10,30]},{"id":8,"mask":4,"pays":[0,0,3,6,25]},{"id":9,"mask":4,"pays":[0,0,3,6,25]},{"id":10,"mask":4,"pays":[0,0,3,6,25]},{"id":11,"mask":4,"pays":[0,0,3,6,25]},{"id":12,"mask":4,"pays":[0,0,3,6,25]},{"id":14,"mask":8,"pays":[12500,12500,12500,12500,12500]},{"id":15,"mask":8,"pays":[2500,2500,2500,2500,2500]},{"id":16,"mask":8,"pays":[1000,1000,1000,1000,1000]},{"id":17,"mask":8,"pays":[500,500,500,500,500]},{"id":18,"mask":8,"pays":[125,125,125,125,125]},{"id":19,"mask":8,"pays":[150,150,150,150,150]},{"id":20,"mask":8,"pays":[200,200,200,200,200]},{"id":21,"mask":8,"pays":[250,250,250,250,250]},{"id":22,"mask":8,"pays":[375,375,375,375,375]},{"id":23,"mask":8,"pays":[500,500,500,500,500]},{"id":24,"mask":8,"pays":[625,625,625,625,625]},{"id":25,"mask":8,"pays":[1250,1250,1250,1250,1250]},{"id":26,"mask":8,"pays":[50000,2,50000,50000,50000]},{"id":27,"mask":8,"pays":[50000,1,50000,50000,50000]},{"id":28,"mask":8,"pays":[12500,2,12500,12500,12500]},{"id":29,"mask":8,"pays":[12500,1,12500,12500,12500]},{"id":30,"mask":8,"pays":[2500,2,2500,2500,2500]},{"id":31,"mask":8,"pays":[2500,1,2500,2500,2500]},{"id":32,"mask":8,"pays":[0,2,2,2,2]},{"id":33,"mask":8,"pays":[0,1,1,1,1]},{"id":34,"mask":8,"pays":[25,25,25,25,25]},{"id":35,"mask":8,"pays":[20,20,20,20,20]},{"id":36,"mask":8,"pays":[15,15,15,15,15]},{"id":37,"mask":8,"pays":[10,10,10,10,10]},{"id":38,"mask":8,"pays":[0,0,0,0,0]}],"lines":[],"matrix":'.$matrixData.$restoreData.'},"gameServerId":"gs-5.121.59-skg-rc-nv-2-10194","jackpotsData":{"enabled":false},"offersData":[],"snivyId":"snivy-30.35.43-rc-skg-0"},"result":true,"sesId":"20001583936"}';
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
                                $linesId = $slotSettings->Ways1024ToLine();
                                $lines = count($linesId);
                                $betLine = $postData['data']['activeCoinValue'] * $postData['data']['activeBet'];
                                $slotSettings->SetGameData($slotSettings->slotId . 'BetLine', $postData['data']['activeBet']);
                                $slotSettings->SetGameData($slotSettings->slotId . 'activeCoinValue', $postData['data']['activeCoinValue']);
                                $mightyCoins = ['134' => 1, '135' => 0.8, '136' => 0.6, '137' => 0.4, '138' => 0, '119' => 6, '120' => 8, '121' => 10, '122' => 15, '123' => 20, '124' => 25, '125' => 50];
                                $allbet = $betLine * 25;
                                if( !isset($postData['slotEvent']) ) 
                                {
                                    $postData['slotEvent'] = 'bet';
                                }
                                if( $reqId == 'GameStartBonusRequest' ) 
                                {
                                    if($postData['data']['bonusType'] == 0)
                                    {
                                        if($slotSettings->GetGameData($slotSettings->slotId . 'RespinCount'))
                                        {
                                            $slotSettings->SetGameData($slotSettings->slotId . 'RespinCount', 0);
                                            $position = $postData['data']['selectedIndex'];
                                            $holdNlinkTotal = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGamesHoldnLink');                                            
                                            
                                            $moreCount = rand(1,3);
                                            $availableCounts[] = $moreCount;
                                            $availableCounts[] = rand(1,3);
                                            $availableCounts[] = rand(1,3);
                                            $slotSettings->SetGameData($slotSettings->slotId . 'FreeGamesHoldnLink', $holdNlinkTotal + $moreCount);
                                            $betData = [
                                                'action' => 'GameStartBonusResponse',
                                                'data' => [
                                                    'bonusType' => 4,
                                                    'extraGamesTotal' => $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames'),
                                                    'holdNlinkGamesTotal' => $holdNlinkTotal + $moreCount,
                                                    'canGamble' => false,
                                                    'extraWin' => 0,
                                                    'matrix' => [[$position]],
                                                    'picks' => $availableCounts,
                                                    'nextBonusType' => 1,
                                                    'roundId' => 0,
                                                    'series' => [],
                                                    'win' => $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin') * 100,
                                                ],
                                                'result' => true,
                                                'sesId' => '10000000223'
                                            ];
                                            $result_tmp[] = json_encode($betData);
                                            break;
                                        }
                                        else
                                            $postData['slotEvent'] = $FREESPIN_TAG;
                                    }                                        
                                    else if($postData['data']['bonusType'] == 1)
                                        $postData['slotEvent'] = $HOLDNLINK_TAG;
                                    else if($postData['data']['bonusType'] == 2)
                                        $postData['slotEvent'] = $MYSTERY_TAG;                                    
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
                                    $slotSettings->SetGameData($slotSettings->slotId . 'RespinCount', 0);

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
                                // if($postData['slotEvent'] == $FREESPIN_TAG && ($slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') < 4))
                                // {
                                //     $winType = 'win';
                                //     $spinWinLimit = $slotSettings->GetBank('bonus');
                                // }
                                    
                                $minReels = [];
                                $minTotalWin = -1;
                                $minLineWins = [];
                                $minWinsMatrix = [];
                                $minScatterCount = 0;
                                $minHoldLinkCount = 0;
                                $minHoldnWin = 0;
                                $minMysteryCount = 0;
                                $spinAcquired = false;                                
                                $lastHoldnLinkCount = $slotSettings->GetGameData($slotSettings->slotId . 'lastHoldnLinkCount');

                                $wild = [2];
                                $scatter = 0;
                                $mightyValues = [134, 134, 134, 134, 134, 134, 135, 135, 135, 135, 135, 135, 136, 136, 136, 136, 136, 136, 136, 136, 137, 137, 137, 137, 137, 137, 137, 137, 137, 125, 124, 124, 123, 123, 122, 122, 121, 121, 120, 120, 119, 119, 119 /*138*/];                            
                                $mystery_replacer = 0; //this only works when mystery pick
                                $mystery_matrix = [];
                                
                                for( $i = 0; $i <= 500; $i++ ) 
                                {
                                    $totalWin = 0;
                                    $mysteryCount = 0;
                                    $holdnlinkCount = 0;
                                    $scattersCount = 0;
                                    if($postData['slotEvent'] != 'bet')
                                    {
                                        $totalWin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin');
                                    }
                                    $holdnWin = 0;
                                    $lineWins = [];
                                    $winsMatrix = [[-1,-1,-1,-1],[-1,-1,-1,-1],[-1,-1,-1,-1],[-1,-1,-1,-1],[-1,-1,-1,-1]];
                                    $cWins = [];

                                    if($postData['slotEvent'] == $MYSTERY_TAG)
                                    {
                                        $mystery_replacer = rand(19, 25);

                                        $mystery_matrix = [$mystery_replacer-2, $mystery_replacer-1, $mystery_replacer, $mystery_replacer+1, $mystery_replacer+2];
                                        $mystery_replacer_pos = 2;
                                        $lineWin = ['figureId' => $mystery_replacer, 'lineId' => 255, 'mask' => 4, 'positions' => [[0, $mystery_replacer_pos]], 'profit' => 0];
                                        array_push($lineWins, $lineWin);
                                        $lastReels = $slotSettings->GetGameData($slotSettings->slotId . 'LastReels');
                                        $lastWinsMatrix = $slotSettings->GetGameData($slotSettings->slotId . 'WinMatrix');
                                        for($r = 0; $r < 5; $r++)
                                            for($c = 0; $c < 4; $c++)
                                                if($lastReels['reel'.($r+1)][$c] == 138)
                                                {
                                                    $lastReels['reel'.($r+1)][$c] = 100 + $mystery_replacer;                                                    
                                                    $lastWinsMatrix[$r][$c] = $mightyCoins[$lastReels['reel'.($r+1)][$c]];
                                                }
                                        $slotSettings->SetGameData($slotSettings->slotId . 'LastReels', $lastReels);
                                        $slotSettings->SetGameData($slotSettings->slotId . 'WinMatrix', $lastWinsMatrix);
                                        break;
                                    }
                                    
                                    $reels = $slotSettings->GetReelStrips($winType, $postData['slotEvent']);
                                    
                                    for($r = 0; $r < 5; $r++)
                                        for($c = 0; $c < 4; $c++)
                                        {
                                            if($reels['reel'.($r+1)][$c] == 1)
                                            {
                                                $reels['reel'.($r+1)][$c] = $mightyValues[rand(0, count($mightyValues) - 1)];
                                            }
                                        }

                                    if($postData['slotEvent'] != $HOLDNLINK_TAG && rand(0, 100) < 5) //set mystery with low probability
                                    {
                                        $reels['reel'.rand(1,5)][rand(0,3)] = 138;
                                    }
                                    
                                    if( $postData['slotEvent'] == $HOLDNLINK_TAG ) 
                                    {
                                        $lastReels = $slotSettings->GetGameData($slotSettings->slotId . 'LastReels');
                                        for($r = 1; $r <= 5; $r++)
                                            for($c = 0; $c < 4; $c++)
                                            {
                                                if($lastReels['reel'.$r][$c] >= 119)
                                                    $reels['reel'.$r][$c] = $lastReels['reel'.$r][$c];
                                            }
                                    }
                                    //check 11 value for holdnLink
                                    for($r = 0; $r < 5; $r++)
                                        for($c = 0; $c < 4; $c++)
                                        {
                                            if($reels['reel' .($r+1)][$c] >= 119)
                                                $winsMatrix[$r][$c] = $mightyCoins[$reels['reel' .($r+1)][$c]];
                                        }
                                    if($postData['slotEvent'] == $HOLDNLINK_TAG)
                                    {
                                        $lastWinsMatrix = $slotSettings->GetGameData($slotSettings->slotId . 'WinMatrix');
                                        for($r = 0; $r < 5; $r++)
                                            for($c = 0; $c < 4; $c++)
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
                                                        if($wscc == 2)
                                                        {
                                                            $cl = 0;
                                                        }
                                                        if( $wscc == 3 ) 
                                                        {
                                                            $cl = 1;
                                                        }
                                                        if( $wscc == 4 ) 
                                                        {
                                                            $cl = 2;
                                                        }
                                                        if( $wscc == 5 ) 
                                                        {
                                                            $cl = 3;
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
                                                    $s[2] = $p2 != -1 ? $reels['reel3'][$p2] : -1;
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
                                                                $lineWin = ['figureId' => $csym, 'lineId' => 255, 'mask' => 0, 'positions' => [[0, $p0], [1, $p1]], 'profit' => $tmpWin * 100];
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
                                                                $lineWin = ['figureId' => $csym, 'lineId' => 255, 'mask' => 0, 'positions' => [[0, $p0], [1, $p1], [2, $p2]], 'profit' => $tmpWin * 100];
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
                                                                $lineWin = ['figureId' => $csym, 'lineId' => 255, 'mask' => 0, 'positions' => [[0, $p0], [1, $p1], [2, $p2], [3, $p3]], 'profit' => $tmpWin * 100];
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
                                                                $lineWin = ['figureId' => $csym, 'lineId' => 255, 'mask' => 0, 'positions' => [[0, $p0], [1, $p1], [2, $p2], [3, $p3], [4, $p4]], 'profit' => $tmpWin * 100];
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
                                    }
                                    
                                    
                                    $scatterPos = [];
                                    for($r = 0; $r < 5; $r++)
                                        for($c = 0; $c < 4; $c++)
                                        {
                                            if($reels['reel'.($r+1)][$c] == $scatter)
                                            {                                                
                                                $scattersCount++;
                                                $scatterPos[] = [$r, $c];
                                            }
                                        }
                                    $scatterWin = 0;
                                    if($scattersCount > 2)
                                    {
                                        if($winType != 'bonus')
                                            continue;
                                        
                                        $scatterWin = $slotSettings->Paytable['SYM_'.$scatter][$scattersCount] * $betLine;
                                        $lineWin = ['figureId' => $scatter, 'lineId' => 255, 'mask' => 2, 'positions' => $scatterPos, 'profit' => $scatterWin * 100];
                                        array_push($lineWins, $lineWin);
                                    }
                                    $totalWin += $scatterWin;

                                    $holdnPos = [];
                                    for($r = 0; $r < 5; $r++)
                                        for($c = 0; $c < 4; $c++)
                                        {
                                            if($reels['reel' .($r+1)][$c] >= 119)
                                            {
                                                $holdnPos[] = [$r, $c];
                                                $holdnlinkCount++;
                                                if($reels['reel' .($r+1)][$c] == 138)
                                                    $mysteryCount++;
                                            }
                                        }
                                    
                                    if($holdnlinkCount > 4)
                                    {
                                        if($scattersCount > 2) //prevent holdn and freespin together
                                            continue;
                                        if($lastHoldnLinkCount == 0)
                                        {
                                            if($winType != 'bonus')
                                                continue;
                                            $mask = 2;
                                            if($mysteryCount > 0)
                                                $mask = 6;
                                            $lineWin = ['figureId' => 1, 'lineId' => 255, 'mask' => $mask, 'positions' => $holdnPos, 'profit' => 0];
                                            array_push($lineWins, $lineWin);
                                        }
                                        for($r = 0; $r < 5; $r++)
                                            for($c = 0; $c < 4; $c++)
                                            {
                                                $times = $winsMatrix[$r][$c];
                                                if($times != -1)
                                                    $holdnWin += $times * $allbet;                                                
                                            }
                                    }

                                    $totalWin += $holdnWin;

                                    if($minTotalWin == -1 || ($totalWin > 0 && $totalWin < $minTotalWin))
                                    {
                                        $minTotalWin = $totalWin;
                                        $minReels = $reels;
                                        $minLineWins = $lineWins;
                                        $minScatterCount = $scattersCount;
                                        $minHoldLinkCount = $holdnlinkCount;
                                        $minWinsMatrix = $winsMatrix;
                                        $minHoldnWin = $holdnWin;
                                        $minMysteryCount = $mysteryCount;
                                    }

                                    if($holdnlinkCount > 15)
                                        continue;
                                    if($debug)
                                    {
                                        $spinAcquired = true;
                                        break;
                                    }
                                    if($totalWin <= $spinWinLimit && (($totalWin > 0 && $winType != 'none')))
                                    {
                                        $spinAcquired = true;
                                        if($postData['slotEvent'] == 'bet' && $totalWin < 0.7 * $spinWinLimit && $winType != 'bonus')
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
                                    $mysteryCount = $minMysteryCount;
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
                                
                                //check holdnlink
                                $doRespin = false;
                                if($holdnlinkCount > 4)
                                {
                                    $nextBonusType = 1;
                                    if($lastHoldnLinkCount == 0)
                                    {
                                        //triggering holdnlink
                                        if( $postData['slotEvent'] == 'bet' ) 
                                            $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $totalWin);
                                        $holdnQuantity['bonusMoreCount'] = 6;
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeGamesHoldnLink', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink') + $holdnQuantity['bonusMoreCount']);

                                        if($mysteryCount > 0)
                                        {
                                            //first activate mystery before mighty 
                                            $nextBonusType = 2;
                                        }
                                    }
                                    $slotSettings->SetGameData($slotSettings->slotId . 'lastHoldnLinkCount', $holdnlinkCount);
                                    $holdnQuantity['bonusPlayedCount'] = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink');
                                    $holdnQuantity['bonusTotalCount'] = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGamesHoldnLink');
                                    

                                    if($holdnQuantity['bonusPlayedCount'] >= $holdnQuantity['bonusTotalCount'])
                                    {
                                        //holdn ended
                                        if(rand(0, 100) < 10)
                                            $doRespin = true;
                                        if($doRespin)
                                        {
                                            $slotSettings->SetGameData($slotSettings->slotId . 'RespinCount', 1);
                                        }
                                        else
                                        {
                                            for($r = 0; $r < 5; $r++)
                                            for($c = 0; $c < 4; $c++)
                                            {
                                                $times = $winsMatrix[$r][$c];
                                                if($times != -1)
                                                {
                                                    $profit = $allbet * $times * 100;
                                                    $lineWin = ['figureId' => $reels['reel'.($r+1)][$c] - 100, 'lineId' => 255, 'mask' => 0, 'positions' => [[$c * 5 + $r, 0]], 'profit' => $profit];
                                                    array_push($lineWins, $lineWin);
                                                }
                                            }
                                            $totalWin += $holdnWin;
                                            $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $totalWin);
                                            $slotSettings->SetGameData($slotSettings->slotId . 'lastHoldnLinkCount', 0);
                                        }
                                        
                                    }
                                    $slotSettings->SetGameData($slotSettings->slotId . 'LastReels', $reels);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'WinMatrix', $winsMatrix);                                    
                                }                                

                                if( $scattersCount >= 3 ) 
                                {
                                    if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') > 0 ) 
                                    {
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') + 8);
                                        $freespinQuantity['bonusMoreCount'] = 8;
                                    }
                                    else
                                    {
                                        //triggering freespin
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeStartWin', $totalWin);
                                        $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $totalWin);
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $slotSettings->slotFreeCount);
                                        $freespinQuantity['bonusMoreCount'] = $slotSettings->slotFreeCount;
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
                                $reelsym = [];
                                if($postData['slotEvent'] !== $MYSTERY_TAG)
                                    $reelsym = [$reels['reel1'], $reels['reel2'], $reels['reel3'], $reels['reel4'], $reels['reel5']];

                                $basicReelSym = $reelsym;
                                $basicWinsMatrix = $winsMatrix;
                                if($postData['slotEvent'] == $HOLDNLINK_TAG)
                                {
                                    $reelsym = [];
                                    $tmpWinMatrix = [];
                                    for($c = 0; $c < 4; $c++)
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
                                        'extraGamesPlayed' => $freespinQuantity['bonusPlayedCount'],
                                        'extraGamesTotal' => $freespinQuantity['bonusTotalCount'],
                                        'canGamble' => false,
                                        'extraWin' => 0,
                                        'matrix' => $reelsym,
                                        'roundId' => 0,
                                        'series' => $lineWins,
                                        'win' => $totalWin * 100,
                                    ],
                                    'result' => true,
                                    'sesId' => '10000000223'
                                ];

                                if($postData['slotEvent'] == $HOLDNLINK_TAG)
                                {
                                    $nextBonusType = 1;
                                    $currentBonusType = 1;
                                    if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGamesHoldnLink') <= $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink'))
                                    {
                                        if(!$doRespin)
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
                                            $nextBonusType = 4;
                                            $lineWin = ['figureId' => 200, 'lineId' => 255, 'mask' => 2, 'positions' => [], 'profit' => 0];
                                            array_push($lineWins, $lineWin);
                                        }                                        
                                    }
                                    else
                                    {
                                        $betData['data']['win'] = $slotSettings->GetGameData($slotSettings->slotId . 'FreeStartWin') * 100;
                                        $betData['data']['extraWin'] = ($slotSettings->GetGameData($slotSettings->slotId . 'TotalWin') - $slotSettings->GetGameData($slotSettings->slotId . 'FreeStartWin')) * 100;                                        
                                    }
                                    $betData['data']['bonusType'] = $currentBonusType;
                                }
                                else if($postData['slotEvent'] == $FREESPIN_TAG)
                                {
                                    $currentBonusType = 0;
                                    if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') <= $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame'))
                                    {
                                        $nextBonusType = -1;
                                        $betData['data']['win'] = $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin') * 100;
                                        $betData['data']['extraWin'] = 0;
                                    }
                                    else
                                    {
                                        $nextBonusType = 0;
                                        if($holdnQuantity['bonusPlayedCount'] < $holdnQuantity['bonusTotalCount'])
                                            $nextBonusType = 1;
                                        $betData['data']['win'] = $slotSettings->GetGameData($slotSettings->slotId . 'FreeStartWin') * 100;
                                        $betData['data']['extraWin'] = ($slotSettings->GetGameData($slotSettings->slotId . 'TotalWin') - $slotSettings->GetGameData($slotSettings->slotId . 'FreeStartWin')) * 100;
                                    }
                                    $betData['data']['bonusType'] = $currentBonusType;
                                    unset($betData['data']['roundId']);
                                }
                                else if($postData['slotEvent'] == $MYSTERY_TAG)
                                {
                                    $nextBonusType = 1;
                                    $currentBonusType = 2;
                                    $betData['data']['bonusType'] = $currentBonusType;
                                    $betData['data']['mysteryReplacer'] = $mystery_replacer;
                                    $betData['data']['matrix'] = [$mystery_matrix];
                                    $betData['data']['holdNlinkGamesTotal'] = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGamesHoldnLink');
                                    $slotSettings->SetGameData($slotSettings->slotId . 'MysteryReplacer', $mystery_replacer);
                                }
                                
                                $betData['data']['nextBonusType'] = $nextBonusType;
                                if($holdnQuantity['bonusTotalCount'] > 0 )
                                    $betData['data']['holdNlinkGamesTotal'] = $holdnQuantity['bonusTotalCount'];

                                if($nextBonusType >= 0)
                                {
                                    $totalWin = 0; //make total win temporarily to not update balance
                                }

                                if( $totalWin > 0) 
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
                                        'basicMatrix' => $basicReelSym,                                        
                                        'roundId' => 0,
                                        'nextBonusType' => $betData['data']['nextBonusType'],
                                        'series' => $lineWins,
                                        'win' => $betData['data']['win'],
                                        'winsMatrix' => $winsMatrix,
                                        'basicWinsMatrix' => $basicWinsMatrix,
                                        'totalWin' => $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin'),
                                        'startWin' => $slotSettings->GetGameData($slotSettings->slotId . 'FreeStartWin'),
                                    ]];
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
