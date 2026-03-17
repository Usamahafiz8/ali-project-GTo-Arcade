<?php 
namespace VanguardLTE\Games\SquidSlotNG
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
                                if( $lastEvent != 'NULL' && $lastEvent->serverResponse->bonusQuantities[0]->bonusPlayedCount < $lastEvent->serverResponse->bonusQuantities[0]->bonusTotalCount) 
                                {
                                    $result_tmp[] = '{"action":"CheckBrokenGameResponse","data":{"restoredGameId":494},"result":true,"sesId":"10000205144"}';
                                }
                                else
                                {
                                    $result_tmp[] = '{"action":"CheckBrokenGameResponse","result":"true","sesId":"false","data":{"haveBrokenGame":"false"}}';
                                }
                                break;
                            case 'AuthRequest':                                
                                $basicResponse = '';
                                if($lastEvent != 'NULL' && $lastEvent->serverResponse->bonusQuantities[0]->bonusPlayedCount < $lastEvent->serverResponse->bonusQuantities[0]->bonusTotalCount) 
                                {                                    
                                    $serverResponse = $lastEvent->serverResponse;
                                    $basicData = [
                                        'bonusQuantities' => $serverResponse->bonusQuantities,
                                        'extraWin' => $serverResponse->extraWin,
                                        'nextBonusType'=> $serverResponse->nextBonusType,
                                        'matrix' => $serverResponse->basicMatrix,
                                        'winsMatrix' => $serverResponse->basicWinsMatrix,
                                        'roundId' => 0,
                                        'series' => $serverResponse->series,
                                        'win' => $serverResponse->win,
                                    ];
                                    $basicResponse = json_encode($basicData);
                                    if($lastEvent->serverResponse->bonusQuantities[0]->bonusPlayedCount > 0)
                                    {
                                        $restoreData = [
                                            'bonusResponses' => [
                                                [
                                                    'bonusQuantities' => $serverResponse->bonusQuantities,    
                                                    'canGamble' => false,
                                                    'currentBonusType' => $serverResponse->nextBonusType,
                                                    'extraWin' => $serverResponse->extraWin,
                                                    'matrix' => $serverResponse->matrix,
                                                    'winsMatrix' => $serverResponse->winsMatrix,
                                                    'nextBonusType' => $serverResponse->nextBonusType,
                                                    'series' => $serverResponse->series,
                                                    'win' => $serverResponse->win,                                            
                                                ]                                            
                                            ],
                                            'recoveryStackSize' => 1
                                        ];
                                        $restoreData = json_encode($restoreData);
                                    }
                                    else
                                    {
                                        $restoreData = '{"bonusResponses":[],"recoveryStackSize":0}';
                                    }
                                }
                                else
                                {
                                    $basicResponse = '{"bonusQuantities":[{"bonusMoreCount":0,"bonusPlayedCount":0,"bonusTotalCount":0,"bonusType":0}],"canGamble":false,"extraWin":0,"matrix":[[0,1,2],[3,9,9],[6,7,4]],"nextBonusType":-1,"rolling_matrix":[[0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0,0,0]],"roundId":0,"series":[],"win":0,"winsMatrix":[[-1,-1,3],[30,2,4],[-1,-1,-1]]}';
                                    $restoreData = '{"bonusResponses":[],"recoveryStackSize":0}';
                                }
                               
                                $defaultBet = $slotSettings->GetGameData($slotSettings->slotId . 'BetLine');
                                if($defaultBet == 0)
                                    $defaultBet = 1;                                
                                    
                                $result_tmp[0] = '{"action":"AuthResponse","data":{"accountData":{"activeBet":'.$defaultBet.',"activeCoinValue":0.01,"activeLines":5,"availableBets":[1,2,3,4,5,10,15,20,30,40,50,100],"availableCoinValues":[0.01],"availableLines":[5],"balance":'.$slotSettings->GetBalance().',"betMultiplier":1.0,"credits":'.($slotSettings->GetBalance() * 100).',"currency":"","rtp":"96.03","sweepMode":false},"buyItems":[],"gameData":{"basicResponse":'.$basicResponse.',"figures":[{"id":0,"mask":4,"pays":[0,0,750]},{"id":1,"mask":4,"pays":[0,0,200]},{"id":2,"mask":4,"pays":[0,0,60]},{"id":3,"mask":4,"pays":[0,0,40]},{"id":4,"mask":4,"pays":[0,0,40]},{"id":5,"mask":4,"pays":[0,0,40]},{"id":6,"mask":4,"pays":[0,0,40]},{"id":7,"mask":4,"pays":[0,0,5]},{"id":8,"mask":25,"pays":[0,0,0]},{"id":9,"mask":196633,"pays":[0]},{"id":10,"mask":25,"pays":[0]},{"id":11,"mask":57,"pays":[10]},{"id":12,"mask":16441,"pays":[30]},{"id":13,"mask":49209,"pays":[456]},{"id":14,"mask":196889,"pays":[0]},{"id":100,"mask":0,"pays":[0]}],"lines":[{"id":0,"positions":[[0,1],[1,1],[2,1]]},{"id":1,"positions":[[0,0],[1,0],[2,0]]},{"id":2,"positions":[[0,2],[1,2],[2,2]]},{"id":3,"positions":[[0,0],[1,1],[2,2]]},{"id":4,"positions":[[0,2],[1,1],[2,0]]}],"replay_leaderboard_show_status":false,"replay_status":false,"restoreData":'.$restoreData.'},"gameServerId":"gs-5.121.59-skg-rc-nv-2-10194","jackpotsData":{"enabled":false},"offersData":[],"snivyId":"snivy-30.35.43-rc-skg-0"},"result":true,"sesId":"20001295797"}';
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
                                $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', 0);
                                break;
                            case 'GameStartBonusRequest':
                            case 'GameStartBasicRequest':
                                $postData['slotEvent'] = 'bet';
                                $bonusMpl = 1;
                                $linesId = $slotSettings->GetPaylines();                                
                                $lines = count($linesId);
                                $betLine = $postData['data']['activeCoinValue'] * $postData['data']['activeBet'];

                                $slotSettings->SetGameData($slotSettings->slotId . 'BetLine', $postData['data']['activeBet']);
                                $slotSettings->SetGameData($slotSettings->slotId . 'activeCoinValue', $postData['data']['activeCoinValue']);
                                $allbet = $betLine * 5;
                                if( !isset($postData['slotEvent']) ) 
                                {
                                    $postData['slotEvent'] = 'bet';
                                }
                                if( $reqId == 'GameStartBonusRequest' ) 
                                {
                                    if($postData['data']['bonusType'] == 0)
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
                                    $slotSettings->SetGameData($slotSettings->slotId . 'lastHoldnLinkCount', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGamesHoldnLink', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', 0);
                                }                                
                                else if($postData['slotEvent'] == $HOLDNLINK_TAG)
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink') + 1);
                                    $bonusMpl = 1;
                                }
                                
                                $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $allbet);                                
                                $winType = $winTypeTmp[0];
                                $spinWinLimit = $winTypeTmp[1];
                                // if($debug && $postData['slotEvent'] == 'bet')
                                //     $winType = "bonus";                               
                                    
                                $minReels = [];
                                $minTotalWin = -1;
                                $minLineWins = [];
                                $minWinsMatrix = [];
                                $minHoldLinkCount = 0;
                                $minHoldnWin = 0;
                                $minBasicReels = [];
                                $minTriggeredHoldn = false;

                                $spinAcquired = false;                                
                                $lastHoldnLinkCount = $slotSettings->GetGameData($slotSettings->slotId . 'lastHoldnLinkCount');
                                $sphere = 9;
                                $jackpot1 = 11; //mini
                                $jackpot2 = 12; //minor
                                $spheres = [$sphere, $jackpot1, $jackpot2];
                                
                                $wild = [];
                                $scatter = 9;
                                $triggeredHoldn = false;
                                
                                for( $i = 0; $i <= 300; $i++ ) 
                                {
                                    $totalWin = 0;
                                    if($postData['slotEvent'] != 'bet')
                                    {
                                        $totalWin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin');
                                    }
                                    $holdnWin = 0;
                                    $lineWins = [];
                                    $winsMatrix = [[-1, -1, -1],[-1, -1, -1],[-1, -1, -1]];
                                    $cWins = array_fill(0, $lines, 0);                                    
                                    
                                    $reels = $slotSettings->GetReelStrips($winType, $postData['slotEvent']);
                                    if($debug)
                                    {
                                        $reels['reel1'] = [3,3,3];
                                        $reels['reel2'] = [3,3,3];
                                        $reels['reel3'] = [3,3,3];
                                    }
                                    if($winType == 'bonus')
                                    {
                                        $reels['reel2'] = [$sphere, $sphere, $sphere];
                                    }
                                    $basicReels = $reels;

                                    
                                    if($postData['slotEvent'] == $HOLDNLINK_TAG)
                                    {
                                        $lastWinsMatrix = $slotSettings->GetGameData($slotSettings->slotId . 'WinMatrix');
                                        $lastReels = $slotSettings->GetGameData($slotSettings->slotId . 'LastReels');
                                        for($c = 0; $c < 3; $c++)
                                        {
                                            $reels['reel2'][$c] = $lastReels['reel2'][$c];
                                        }
                                    }
                                    //check sphere value for holdnLink
                                    for($r = 0; $r < 3; $r++)
                                        for($c = 0; $c < 3; $c++)
                                        {
                                            if($reels['reel' .($r+1)][$c] == $sphere)
                                                $winsMatrix[$r][$c] = rand(1, 5);
                                            if($reels['reel' .($r+1)][$c] == $jackpot1)
                                                $winsMatrix[$r][$c] = 10;
                                            if($reels['reel' .($r+1)][$c] == $jackpot2)
                                                $winsMatrix[$r][$c] = 30;

                                        }

                                    if($postData['slotEvent'] == $HOLDNLINK_TAG)
                                    {
                                        $lastWinsMatrix = $slotSettings->GetGameData($slotSettings->slotId . 'WinMatrix');
                                        for($c = 0; $c < 3; $c++)
                                        {
                                            if($lastWinsMatrix[1][$c] != -1)
                                            {
                                                $winsMatrix[1][$c] = $lastWinsMatrix[1][$c];
                                            }
                                        }
                                    }

                                    if( $postData['slotEvent'] != $HOLDNLINK_TAG ) 
                                    {
                                        for( $k = 0; $k < $lines; $k++ ) 
                                        {
                                            $lineWin = [];
                                            for( $j = 0; $j < count($slotSettings->SymbolGame); $j++ ) 
                                            {
                                                $csym = $slotSettings->SymbolGame[$j];
                                                if( $csym == $scatter || !isset($slotSettings->Paytable['SYM_' . $csym]) ) 
                                                {
                                                }
                                                else
                                                {
                                                    $s = [];
                                                    $p0 = $linesId[$k][0];
                                                    $p1 = $linesId[$k][1];
                                                    $p2 = $linesId[$k][2];
                                                    $s[0] = $reels['reel1'][$p0];
                                                    $s[1] = $reels['reel2'][$p1];
                                                    $s[2] = $reels['reel3'][$p2];                                                    
                                                   
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
                                            }
                                            if( $cWins[$k] > 0 && count($lineWin) > 0 ) 
                                            {
                                                array_push($lineWins, $lineWin);
                                                $totalWin += $cWins[$k];
                                            }
                                        }
                                    }

                                    //check multiplier 2 
                                    $firstSym = $reels['reel1'][0];
                                    $sameCnt = 0;
                                    for($r = 0; $r < 3; $r++)
                                        for($c = 0; $c < 3; $c++)
                                            if($reels['reel'.($r+1)][$c] == $firstSym)
                                                $sameCnt++;
                                    if($sameCnt == 9)
                                    {
                                        $totalWin *= 2;
                                        foreach($lineWins as &$lineWin)
                                        {
                                            $lineWin['profit'] *= 2;
                                        }
                                    }                                        

                                    $holdnlinkCount = 0;
                                    $holdnPos = [];
                                    for($r = 0; $r < 3; $r++)
                                        for($c = 0; $c < 3; $c++)
                                        {
                                            if($r != 1)
                                            {
                                                if($reels['reel' .($r+1)][$c] == $sphere || $reels['reel' .($r+1)][$c] == $jackpot1 || $reels['reel' .($r+1)][$c] == $jackpot2)
                                                {
                                                    $holdnPos[] = [$r, $c];
                                                    $holdnlinkCount++;
                                                }
                                            }
                                        }
                                    if($holdnlinkCount > 4) 
                                        continue;

                                    if(in_array($reels['reel2'][0], $spheres) && in_array($reels['reel2'][1], $spheres) && in_array($reels['reel2'][2], $spheres) && $postData['slotEvent'] != $HOLDNLINK_TAG)
                                    {
                                        $lineWin = ['figureId' => 0, 'lineId' => 255, 'mask' => 2, 'positions' => [[1,0], [1,1], [1,2]], 'profit' => 0];
                                        array_push($lineWins, $lineWin);
                                        $totalWin += $winsMatrix[1][0] * $allbet;
                                        $totalWin += $winsMatrix[1][1] * $allbet;
                                        $totalWin += $winsMatrix[1][2] * $allbet;
                                        $triggeredHoldn = true;
                                    }

                                    if($holdnlinkCount > 0)
                                    {                                        
                                        for($r = 0; $r < 3; $r++)
                                            for($c = 0; $c < 3; $c++)
                                            {
                                                $times = $winsMatrix[$r][$c];
                                                if($times != -1)
                                                {
                                                    $holdnWin += $times * $allbet;
                                                }                                                
                                            }
                                    }

                                    $totalWin += $holdnWin;

                                    if($minTotalWin == -1 || ($totalWin > 0 && $totalWin < $minTotalWin))
                                    {
                                        $minTotalWin = $totalWin;
                                        $minReels = $reels;
                                        $minLineWins = $lineWins;
                                        $minHoldLinkCount = $holdnlinkCount;
                                        $minWinsMatrix = $winsMatrix;
                                        $minHoldnWin = $holdnWin;
                                        $minBasicReels = $basicReels;
                                        $minTriggeredHoldn = $triggeredHoldn;
                                    }

                                    if($debug)
                                    {
                                        $spinAcquired = true;
                                        break;
                                    }
                                    if($totalWin <= $spinWinLimit && (($totalWin > 0 && $winType != 'none') || ($winType == 'bonus' && $triggeredHoldn)))
                                    {
                                        $spinAcquired = true;
                                        if($totalWin < 0.5 * $spinWinLimit && $winType != 'bonus')
                                            $spinAcquired = false;
                                        if($spinAcquired)
                                            break;                                        
                                    }   
                                    else if($winType == 'none' && $totalWin == 0 && $holdnlinkCount < 6)
                                    {
                                        $spinAcquired = true;
                                        break;
                                    }
                                }

                                if(!$spinAcquired)
                                {
                                    $lineWins = $minLineWins;
                                    $reels = $minReels;
                                    $totalWin = $minTotalWin;
                                    $holdnlinkCount = $minHoldLinkCount;
                                    $winsMatrix = $minWinsMatrix;
                                    $holdnWin = $minHoldnWin;
                                    $basicReels = $minBasicReels;
                                    $triggeredHoldn = $minTriggeredHoldn;
                                }

                                $totalWin -= $holdnWin;

                                $holdnQuantity = [
                                    'bonusMoreCount' => 0,
                                    'bonusPlayedCount' => 0,
                                    'bonusTotalCount' => 0,
                                    'bonusType' => 0
                                ];

                                $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $totalWin);
                                $nextBonusType = -1;

                                //check holdnlink
                                if($triggeredHoldn)
                                {
                                    $holdnQuantity['bonusMoreCount'] = 4;
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGamesHoldnLink', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink') + 4);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeStartWin', $totalWin);
                                    $holdnQuantity['bonusPlayedCount'] = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink');
                                    $holdnQuantity['bonusTotalCount'] = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGamesHoldnLink');
                                    $slotSettings->SetGameData($slotSettings->slotId . 'WinMatrix', $winsMatrix);
                                    $nextBonusType = 0;
                                }

                                if( $postData['slotEvent'] == $HOLDNLINK_TAG)
                                {                                    
                                    if($holdnlinkCount > 0)
                                    {
                                        //everytime holdn got, add win
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeGamesHoldnLink', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink') + 4);
                                        for($r = 0; $r < 3; $r++)
                                            for($c = 0; $c < 3; $c++)
                                            {
                                                $times = $winsMatrix[$r][$c];
                                                if($times != -1)
                                                {
                                                    $figureId = $reels['reel'.($r+1)][$c];                                                    
                                                    $profit = $allbet * $times * 100;
                                                    $lineWin = ['figureId' => $figureId, 'lineId' => 255, 'mask' => 4, 'positions' => [[$r, $c]], 'profit' => $profit];
                                                    array_push($lineWins, $lineWin);
                                                }
                                            }
                                        $slotSettings->SetWin($holdnWin);
                                        $totalWin += $holdnWin;
                                        $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $totalWin);
                                        $holdnQuantity['bonusMoreCount'] = 4;
                                    }
                                    $holdnQuantity['bonusPlayedCount'] = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink');
                                    $holdnQuantity['bonusTotalCount'] = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGamesHoldnLink');
                                    $nextBonusType = 0;

                                    if($holdnQuantity['bonusPlayedCount'] >= $holdnQuantity['bonusTotalCount'])
                                    {
                                        //holdn ended
                                        for($r = 0; $r < 3; $r++)
                                            for($c = 0; $c < 3; $c++)
                                            {
                                                $times = $winsMatrix[$r][$c];
                                                if($times != -1)
                                                {
                                                    $figureId = $reels['reel'.($r+1)][$c];                                                    
                                                    $profit = $allbet * $times * 100;
                                                    $lineWin = ['figureId' => $figureId, 'lineId' => 255, 'mask' => 4, 'positions' => [[$c * 5 + $r, 0]], 'profit' => $profit];
                                                    array_push($lineWins, $lineWin);
                                                }
                                            }
                                        $totalWin += $holdnWin;                                        
                                        $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $totalWin);
                                    }                                    
                                    $slotSettings->SetGameData($slotSettings->slotId . 'WinMatrix', $winsMatrix);
                                }                                

                                $bonusQuantities = [$holdnQuantity];

                                $action = 'GameStartBasicResponse';
                                if($reqId == 'GameStartBasicRequest')
                                    $action = 'GameStartBasicResponse';
                                else if($reqId == 'GameStartBonusRequest')
                                    $action = 'GameStartBonusResponse';
                                $reelsym = [$reels['reel1'], $reels['reel2'], $reels['reel3']];

                                $basicReelSym = $reelsym;
                                $basicWinsMatrix = $winsMatrix;
                                if($postData['slotEvent'] == $HOLDNLINK_TAG)
                                {                                    
                                    for($c = 0; $c < 3; $c++)
                                        for($r = 0; $r < 3; $r++)                                        
                                        {
                                            if($winsMatrix[$r][$c] == -1)
                                                $reelsym[$r][$c] = 100;                                            
                                        }
                                }
                               
                                $betData = [
                                    'action' => $action,
                                    'data' => [
                                        'bonusQuantities' => $bonusQuantities,
                                        'canGamble' => false,
                                        'extraWin' => 0,
                                        'matrix' => $reelsym,
                                        'roundId' => 0,
                                        'series' => $lineWins,
                                        'win' => $totalWin * 100,
                                        'winsMatrix' => $winsMatrix,
                                    ],
                                    'result' => true,
                                    'sesId' => '10000000223'
                                ];

                                if($postData['slotEvent'] == $HOLDNLINK_TAG)
                                {
                                    if($holdnlinkCount > 0)
                                    {
                                        $betData['data']['extraWin'] = $holdnWin * 100;
                                        $betData['data']['win'] = ($totalWin - $holdnWin) * 100;
                                        $slotSettings->SetGameData($slotSettings->slotId . 'LastExtraWin', $betData['data']['extraWin']);
                                    }                                    
                                    else
                                    {
                                        $betData['data']['extraWin'] = 0;
                                    }
                                }
                                    
                                $currentBonusType = -1;
                                if($postData['slotEvent'] == $HOLDNLINK_TAG)
                                {
                                    $nextBonusType = 0;
                                    $currentBonusType = 0;
                                    if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGamesHoldnLink') <= $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink'))
                                    {
                                        $nextBonusType = -1;
                                        $betData['data']['win'] = $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin') * 100;
                                        $betData['data']['extraWin'] = 0;
                                    }   
                                    $betData['data']['currentBonusType'] = $currentBonusType;
                                }                               
                                
                                $betData['data']['nextBonusType'] = $nextBonusType;
                                if($nextBonusType != -1)
                                    $totalWin = 0;
                                if( $totalWin > 0) 
                                {
                                    $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), -1 * $totalWin);
                                    $slotSettings->SetBalance($totalWin);                                    
                                    if($currentBonusType == -1 && $nextBonusType == -1)
                                        $slotSettings->SetWin($totalWin); //set win for normal spin win
                                }
                                $slotSettings->SetGameData($slotSettings->slotId . 'LastReels', $reels);
                                $spinData = [
                                    'responseEvent' => 'spin',
                                    'responseType' => $postData['slotEvent'],
                                    'serverResponse' => [
                                        'bonusQuantities' => $bonusQuantities,
                                        'extraWin' => $betData['data']['extraWin'],
                                        'matrix' => $reelsym,
                                        'basicMatrix' => $basicReelSym,      
                                        'nextBonusType' => $nextBonusType,
                                        'currentBonusType' => $currentBonusType,
                                        'roundId' => 0,
                                        'series' => $lineWins,
                                        'win' => $betData['data']['win'],
                                        'winsMatrix' => $winsMatrix,
                                        'basicWinsMatrix' => $basicWinsMatrix,
                                        'totalWin' => $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin'),
                                        'reels' => $reels
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
