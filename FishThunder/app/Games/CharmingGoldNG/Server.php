<?php 
namespace VanguardLTE\Games\CharmingGoldNG
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
                                if( $lastEvent != 'NULL' && ($lastEvent->serverResponse->bonusQuantities[0]->bonusPlayedCount < $lastEvent->serverResponse->bonusQuantities[0]->bonusTotalCount || $lastEvent->serverResponse->bonusQuantities[1]->bonusPlayedCount < $lastEvent->serverResponse->bonusQuantities[1]->bonusTotalCount)) 
                                {
                                    $result_tmp[] = '{"action":"CheckBrokenGameResponse","data":{"restoredGameId":427},"result":true,"sesId":"10000205144"}';
                                }
                                else
                                {
                                    $result_tmp[] = '{"action":"CheckBrokenGameResponse","result":"true","sesId":"false","data":{"haveBrokenGame":"false"}}';
                                }
                                
                                break;
                            case 'AuthRequest':                                
                                $basicResponse = '';
                                if( $lastEvent != 'NULL' && ($lastEvent->serverResponse->bonusQuantities[0]->bonusPlayedCount < $lastEvent->serverResponse->bonusQuantities[0]->bonusTotalCount || $lastEvent->serverResponse->bonusQuantities[1]->bonusPlayedCount < $lastEvent->serverResponse->bonusQuantities[1]->bonusTotalCount)) 
                                {
                                    $serverResponse = $lastEvent->serverResponse;
                                    $basicResponse = json_encode([
                                        'bonusQuantities' => $serverResponse->bonusQuantities,
                                        'extraWin' => $slotSettings->GetGameData($slotSettings->slotId . 'LastExtraWin'),
                                        'matrix' => $serverResponse->basicMatrix,
                                        'roundId' => 0,
                                        'series' => $serverResponse->series,
                                        'win' => $serverResponse->win,
                                        'win_matrix' => $serverResponse->basicWinsMatrix,
                                    ]);
                                    $restoreData = json_encode([
                                        'bonusResponses' => [
                                            [
                                                'bonusQuantities' => $serverResponse->bonusQuantities,
                                                'extraWin' => $slotSettings->GetGameData($slotSettings->slotId . 'LastExtraWin'),
                                                'matrix' => $serverResponse->matrix,
                                                'roundId' => 0,
                                                'series' => $serverResponse->series,
                                                'win' => $serverResponse->win,
                                                'win_matrix' => $serverResponse->winsMatrix,
                                            ]
                                        ],
                                        'recoveryStackSize' => 1
                                    ]);                                    
                                }
                                else
                                {
                                    $basicResponse = '{"bonusQuantities":[{"bonusMoreCount":0,"bonusPlayedCount":0,"bonusTotalCount":0,"bonusType":0},{"bonusMoreCount":0,"bonusPlayedCount":0,"bonusTotalCount":0,"bonusType":1}],"canGamble":false,"extraWin":0,"matrix":[[7,8,8],[6,3,4],[3,2,1],[4,4,2],[5,3,6]],"nextBonusType":-1,"rolling_matrix":[[0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]],"roundId":0,"series":[],"win":0,"win_matrix":[[-1,-1,-1],[-1,-1,-1],[-1,-1,-1],[-1,-1,-1],[-1,-1,-1]]}';
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

                                $result_tmp[0]='{"action":"AuthResponse","data":{"accountData":{"activeBet":'.$defaultBet.',"activeCoinValue":0.01,"activeLines":1,"availableBets":[1,2,3,4,5,10,15,20,25],"availableCoinValues":[0.01],"availableLines":[1],"balance":'.$slotSettings->GetBalance().',"betMultiplier":20.0,"credits":'.($slotSettings->GetBalance() * 100).',"currency":"","rtp":"95.95","sweepMode":false},"buyItems":[],"gameData":{"basicResponse":'.$basicResponse.',"figures":[{"id":0,"mask":4,"pays":[0,1,6,14,24]},{"id":1,"mask":4,"pays":[0,0,3,10,14]},{"id":2,"mask":4,"pays":[0,0,3,10,14]},{"id":3,"mask":4,"pays":[0,0,2,8,12]},{"id":4,"mask":4,"pays":[0,0,2,8,12]},{"id":5,"mask":4,"pays":[0,0,2,5,10]},{"id":6,"mask":4,"pays":[0,0,2,5,10]},{"id":7,"mask":4,"pays":[0,0,2,5,10]},{"id":8,"mask":4,"pays":[0,0,2,5,10]},{"id":9,"mask":2,"pays":[0,0,0,0,0]},{"id":10,"mask":9,"pays":[0,0,20,40,100]},{"id":11,"mask":89,"pays":[0]},{"id":12,"mask":73,"pays":[0]},{"id":13,"mask":25,"pays":[0]},{"id":14,"mask":25,"pays":[0]},{"id":16,"mask":57,"pays":[200]},{"id":17,"mask":16441,"pays":[600]},{"id":18,"mask":49209,"pays":[1000]},{"id":19,"mask":0,"pays":[0]}],"lines":[],"replay_leaderboard_show_status":false,"replay_status":false, "restoreData":'.$restoreData.'},"gameServerId":"gs-5.122.121-skg-rc-skg-1-512","jackpotsData":{"enabled":false},"offersData":[],"snivyId":"snivy-30.36.44-rc-skg-0"},"result":true,"sesId":"10000022949"}';
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
                                    
                                $minReels = [];
                                $minTotalWin = -1;
                                $minLineWins = [];
                                $minWinsMatrix = [];
                                $minScatterCount = 0;
                                $minHoldLinkCount = 0;
                                $minHoldnWin = 0;
                                $minFreespinWin = 0;
                                $minBurstAppeared = false;
                                $spinAcquired = false;                                
                                $lastHoldnLinkCount = $slotSettings->GetGameData($slotSettings->slotId . 'lastHoldnLinkCount');

                                $wild = [9];
                                $scatter = 10;
                                $burst = 12;
                                $sphere = 14;
                                $burstAppeared = false;
                                for( $i = 0; $i <= 500; $i++ ) 
                                {
                                    $freespinWin = 0;
                                    $burstAppeared = false;
                                    $totalWin = 0;
                                    $burstWin = 0;
                                    if($postData['slotEvent'] != 'bet')
                                    {
                                        $totalWin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin');
                                    }
                                    $holdnWin = 0;
                                    $lineWins = [];
                                    $winsMatrix = [[-1,-1,-1],[-1,-1,-1],[-1,-1,-1],[-1,-1,-1],[-1,-1,-1]];
                                    $cWins = [];
                                    
                                    $reels = $slotSettings->GetReelStrips($winType, $postData['slotEvent']);
                                    
                                    if( $postData['slotEvent'] == $HOLDNLINK_TAG ) 
                                    {                                        
                                        $lastReels = $slotSettings->GetGameData($slotSettings->slotId . 'LastReels');
                                        for($r = 1; $r <= 5; $r++)
                                            for($c = 0; $c < 3; $c++)
                                            {
                                                if($lastReels['reel'.$r][$c] == $sphere)
                                                    $reels['reel'.$r][$c] = $sphere;  
                                                if($lastReels['reel'.$r][$c] == '16')
                                                    $reels['reel'.$r][$c] = '16'; 
                                                if($lastReels['reel'.$r][$c] == '17')
                                                    $reels['reel'.$r][$c] = '17';                                                   
                                            }
                                    }
                                    //check 11 value for holdnLink
                                    for($r = 0; $r < 5; $r++)
                                        for($c = 0; $c < 3; $c++)
                                        {
                                            if($reels['reel' .($r+1)][$c] == $sphere)
                                                $winsMatrix[$r][$c] = rand(1, 8) * 20;
                                            if($reels['reel' .($r+1)][$c] == '16')
                                                $winsMatrix[$r][$c] = 10 * 20;
                                            if($reels['reel' .($r+1)][$c] == '17')
                                                $winsMatrix[$r][$c] = 30 * 20;

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
                                    }
                                    
                                    //check burst win
                                    for($c = 0; $c < 3; $c++)
                                    {
                                        if($reels['reel3'][$c] == $burst)
                                        {
                                            $burstAppeared = true;
                                            break;
                                        }
                                    }

                                    if($burstAppeared)
                                    {
                                        $burstWinPos = [];
                                        
                                        for($r = 0; $r < 5; $r++)
                                            for($c = 0; $c < 3; $c++)
                                            {
                                                if($reels['reel'.($r+1)][$c] == $sphere || $reels['reel'.($r+1)][$c] == '16' || $reels['reel'.($r+1)][$c] == '17')
                                                {
                                                    $burstWinPos[] = [$r, $c];
                                                    $times = $winsMatrix[$r][$c];
                                                    $profit = $times * $betLine;
                                                    $burstWin += $profit;
                                                    $lineWin = ['figureId' => 12, 'lineId' => 255, 'mask' => 4, 'positions' => [[$r, $c]], 'profit' => $profit * 100];
                                                    array_push($lineWins, $lineWin);
                                                }
                                            }
                                        $totalWin += $burstWin;
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
                                    if($scattersCount > 2 && $postData['slotEvent'] == 'bet')
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
                                        $scatterWin = $slotSettings->Paytable['SYM_10'][$scattersCount] * $allbet;
                                        $lineWin = ['figureId' => $scatter, 'lineId' => 255, 'mask' => 2, 'positions' => $scatterPos, 'profit' => $scatterWin * 100];
                                        array_push($lineWins, $lineWin);
                                        $freespinWin = 5;
                                        if($scattersCount == 4)
                                            $freespinWin = 10;
                                        else if($scattersCount == 5)
                                            $freespinWin = 15;
                                    }
                                    else if($scattersCount > 0 && $postData['slotEvent'] == $FREESPIN_TAG)
                                    {
                                        if($scattersCount == 1)
                                            $freespinWin = 1;
                                        else if($scattersCount == 2)
                                            $freespinWin = 2;
                                        else if($scattersCount == 3)
                                            $freespinWin = 5;
                                        else
                                            $freespinWin = 10;
                                            for($r = 0; $r < 5; $r++)
                                        for($c = 0; $c < 3; $c++)
                                        {
                                            if($reels['reel'.($r+1)][$c] == $scatter)
                                            {
                                                $scatterPos[] = [$r, $c];
                                            }
                                        }
                                        $lineWin = ['figureId' => $scatter, 'lineId' => 255, 'mask' => 2, 'positions' => $scatterPos, 'profit' => 0];
                                        array_push($lineWins, $lineWin);
                                    }
                                    $totalWin += $scatterWin;

                                    $holdnlinkCount = 0;
                                    $holdnPos = [];
                                    for($r = 0; $r < 5; $r++)
                                        for($c = 0; $c < 3; $c++)
                                        {
                                            if($reels['reel' .($r+1)][$c] == $sphere || $reels['reel' .($r+1)][$c] == '16' || $reels['reel' .($r+1)][$c] == '17' || $reels['reel' .($r+1)][$c] == $burst)
                                            {
                                                $holdnPos[] = [$r, $c];
                                                $holdnlinkCount++;
                                            }
                                        }
                                    if($holdnlinkCount > 11) 
                                        continue;
                                    if($holdnlinkCount > 5)
                                    {
                                        if($scattersCount > 2) //prevent holdn and freespin together
                                            continue;
                                        if($lastHoldnLinkCount == 0)
                                        {
                                            if($winType != 'bonus')
                                                continue;
                                            $lineWin = ['figureId' => 10, 'lineId' => 255, 'mask' => 2, 'positions' => $holdnPos, 'profit' => 0];
                                            array_push($lineWins, $lineWin);
                                        }
                                        for($r = 0; $r < 5; $r++)
                                            for($c = 0; $c < 3; $c++)
                                            {
                                                $times = $winsMatrix[$r][$c];
                                                if($times != -1)
                                                    $holdnWin += $times * $betLine;
                                            }
                                        if($burstWin > 0) //in case of burst appeared, holdnWin is already added
                                            $holdnWin = 0;
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
                                        $minBurstAppeared = $burstAppeared;
                                        $minFreespinWin = $freespinWin;
                                    }

                                    if($debug)
                                    {
                                        $spinAcquired = true;
                                        break;
                                    }
                                    if($totalWin <= $spinWinLimit && (($totalWin > 0 && $winType != 'none')) || ($winType == 'bonus' && $freespinWin > 0) || ($winType == 'bonus' && $lastHoldnLinkCount == 0 && $holdnlinkCount > 5))
                                    {
                                        $spinAcquired = true;
                                        if($totalWin < 0.5 * $spinWinLimit && $winType != 'bonus')
                                            $spinAcquired = false;
                                        if($spinAcquired)
                                            break;                                        
                                    }  
                                    else if($winType == 'none' && $totalWin == 0 && $holdnlinkCount < 6)
                                    {
                                        break;
                                    }
                                }

                                if(($totalWin > 0 || $scattersCount > 2 || $holdnlinkCount > 5) && !$spinAcquired)
                                {
                                    $lineWins = $minLineWins;
                                    $reels = $minReels;
                                    $totalWin = $minTotalWin;
                                    $scattersCount = $minScatterCount;
                                    $holdnlinkCount = $minHoldLinkCount;
                                    $winsMatrix = $minWinsMatrix;
                                    $holdnWin = $minHoldnWin;
                                    $burstAppeared = $minBurstAppeared;
                                    $freespinWin = $minFreespinWin;
                                }

                                $totalWin -= $holdnWin;
                                if($burstAppeared)
                                    $totalWin += $holdnWin;

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
                                if( $holdnlinkCount > 5 || $postData['slotEvent'] == $HOLDNLINK_TAG)
                                {
                                    if($holdnlinkCount > $lastHoldnLinkCount || $burstAppeared)
                                    {
                                        if($lastHoldnLinkCount == 0)
                                        {
                                            //triggering holdnlink
                                            if( $postData['slotEvent'] == 'bet' ) 
                                                $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $totalWin);
                                        }
                                        $holdnQuantity['bonusMoreCount'] = 3;
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeGamesHoldnLink', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink') + 3);
                                    }
                                    if($burstAppeared)
                                        $holdnlinkCount--;
                                    $slotSettings->SetGameData($slotSettings->slotId . 'lastHoldnLinkCount', $holdnlinkCount);

                                    $holdnQuantity['bonusPlayedCount'] = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink');
                                    $holdnQuantity['bonusTotalCount'] = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGamesHoldnLink');
                                    $nextBonusType = 1;

                                    if($holdnQuantity['bonusPlayedCount'] >= $holdnQuantity['bonusTotalCount'])
                                    {
                                        //holdn ended
                                        for($r = 0; $r < 5; $r++)
                                            for($c = 0; $c < 3; $c++)
                                            {
                                                $times = $winsMatrix[$r][$c];
                                                if($times != -1)
                                                {
                                                    $profit = $betLine * $times * 100;
                                                    $lineWin = ['figureId' => 11, 'lineId' => 255, 'mask' => 4, 'positions' => [$r, $c], 'profit' => $profit];
                                                    array_push($lineWins, $lineWin);
                                                }                                                
                                            }
                                        $totalWin += $holdnWin;
                                        $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin') + $holdnWin);
                                        $slotSettings->SetGameData($slotSettings->slotId . 'lastHoldnLinkCount', 0);
                                    }
                                    $slotSettings->SetGameData($slotSettings->slotId . 'LastReels', $reels);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'WinMatrix', $winsMatrix);                                    
                                }                                

                                if( $freespinWin > 0 )
                                {
                                    if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') > 0 ) 
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

                                $basicReelSym = $reelsym;
                                $basicWinsMatrix = $winsMatrix;
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
                                        'series' => $lineWins,
                                        'win' => $totalWin * 100,
                                        'win_matrix' => $winsMatrix
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
                                        if($burstAppeared)
                                        {
                                            $betData['data']['extraWin'] = ($slotSettings->GetGameData($slotSettings->slotId . 'TotalWin') - $slotSettings->GetGameData($slotSettings->slotId . 'FreeStartWin')) * 100;
                                            $slotSettings->SetGameData($slotSettings->slotId . 'LastExtraWin', $betData['data']['extraWin']);
                                        }
                                        else
                                        {
                                            $betData['data']['extraWin'] = $slotSettings->GetGameData($slotSettings->slotId . 'LastExtraWin');
                                        }                                        
                                    }
                                    $betData['data']['currentBonusType'] = $currentBonusType;
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
                                        'extraWin' => 0,
                                        'matrix' => $reelsym,
                                        'basicMatrix' => $basicReelSym,                                        
                                        'roundId' => 0,
                                        'series' => $lineWins,
                                        'win' => $totalWin * 100,
                                        'winsMatrix' => $winsMatrix,
                                        'basicWinsMatrix' => $basicWinsMatrix,
                                        'totalWin' => $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin'),
                                        'startWin' => $slotSettings->GetGameData($slotSettings->slotId . 'FreeStartWin'),
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
