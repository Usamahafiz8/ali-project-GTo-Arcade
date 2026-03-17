<?php 
namespace VanguardLTE\Games\EagleGoldNG
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
                        $debug =true;
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
                                if( $lastEvent != 'NULL'&& ($lastEvent->serverResponse->bonusQuantities[0]->bonusPlayedCount < $lastEvent->serverResponse->bonusQuantities[0]->bonusTotalCount || $lastEvent->serverResponse->bonusQuantities[1]->bonusPlayedCount < $lastEvent->serverResponse->bonusQuantities[1]->bonusTotalCount) ) 
                                {
                                    $result_tmp[] = '{"action":"CheckBrokenGameResponse","data":{"restoredGameId":432},"result":true,"sesId":"10000205144"}';
                                }
                                else
                                {
                                    $result_tmp[] = '{"action":"CheckBrokenGameResponse","result":"true","sesId":"false","data":{"haveBrokenGame":"false"}}';
                                }
                                break;
                            case 'AuthRequest':                                
                                $basicResponse = '';
                                if( $lastEvent != 'NULL'&& ($lastEvent->serverResponse->bonusQuantities[0]->bonusPlayedCount < $lastEvent->serverResponse->bonusQuantities[0]->bonusTotalCount || $lastEvent->serverResponse->bonusQuantities[1]->bonusPlayedCount < $lastEvent->serverResponse->bonusQuantities[1]->bonusTotalCount) ) 
                                {
                                    $serverResponse = $lastEvent->serverResponse;
                                    $basicResponse = json_encode([
                                        'bonusQuantities' => $serverResponse->bonusQuantities,
                                        'extraWin' => $serverResponse->extraWin,
                                        'matrix' => $serverResponse->basicMatrix,
                                        'roundId' => 0,
                                        'series' => $serverResponse->series,
                                        'win' => $serverResponse->win,
                                        'winsMatrix' => $serverResponse->basicWinsMatrix,
                                    ]);
                                    $restoreData = json_encode([
                                        'bonusResponses' => [
                                            [
                                                'bonusQuantities' => $serverResponse->bonusQuantities,
                                                'extraWin' => $serverResponse->extraWin,
                                                'matrix' => $serverResponse->matrix,
                                                'roundId' => 0,
                                                'series' => $serverResponse->series,
                                                'win' => $serverResponse->win,
                                                'winsMatrix' => $serverResponse->winsMatrix,
                                            ]
                                        ],
                                        'recoveryStackSize' => 1
                                    ]);
                                    // $basicResponse = '{"bonusQuantities":[{"bonusMoreCount":0,"bonusPlayedCount":0,"bonusTotalCount":0,"bonusType":0},{"bonusMoreCount":0,"bonusPlayedCount":0,"bonusTotalCount":0,"bonusType":1}],"canGamble":false,"extraWin":0,"matrix":[[8,3,7],[6,8,7],[3,7,9],[5,6,1],[1,3,8]],"nextBonusType":-1,"rolling_matrix":[[0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]],"roundId":0,"series":[],"win":0,"winsMatrix":[[-1,-1,-1],[-1,-1,-1],[-1,-1,-1],[-1,-1,-1],[-1,-1,-1]]}';
                                    // $restoreData = '{"bonusResponses":[],"recoveryStackSize":0}';
                                    // $restoreData = '{"bonusResponses": ['.$lastEvent->serverResponse->basicResponse.'], "recoveryStackSize":1}';
                                    // $basicResponse = '{"bonusQuantities":[{"bonusMoreCount":0,"bonusPlayedCount":0,"bonusTotalCount":0,"bonusType":0},{"bonusMoreCount":0,"bonusPlayedCount":0,"bonusTotalCount":0,"bonusType":1}],"canGamble":false,"extraWin":0,"matrix":[[8,3,7],[6,8,7],[3,7,9],[5,6,1],[1,3,8]],"nextBonusType":-1,"rolling_matrix":[[0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]],"roundId":0,"series":[],"win":0,"winsMatrix":[[-1,-1,-1],[-1,-1,-1],[-1,-1,-1],[-1,-1,-1],[-1,-1,-1]]}';
                                }
                                else
                                {
                                    $basicResponse = '{"bonusQuantities":[{"bonusMoreCount":0,"bonusPlayedCount":0,"bonusTotalCount":0,"bonusType":0},{"bonusMoreCount":0,"bonusPlayedCount":0,"bonusTotalCount":0,"bonusType":1}],"canGamble":false,"extraWin":0,"matrix":[[8,3,7],[6,8,7],[3,7,9],[5,6,1],[1,3,8]],"nextBonusType":-1,"rolling_matrix":[[0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]],"roundId":0,"series":[],"win":0,"winsMatrix":[[-1,-1,-1],[-1,-1,-1],[-1,-1,-1],[-1,-1,-1],[-1,-1,-1]]}';
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

                                $result_tmp[0] = '{"action":"AuthResponse","data":{"accountData":{"activeBet":'.$defaultBet.',"activeCoinValue":0.01,"activeLines":25,"availableBets":[1,2,3,4,5,10,15,20,25],"availableCoinValues":[0.01],"availableLines":[25],"balance":'.$slotSettings->GetBalance().',"betMultiplier":0.8,"credits":'.($slotSettings->GetBalance() * 100).',"currency":"","rtp":"95.78","sweepMode":false},"buyItems":[],"gameData":{"basicResponse":'.$basicResponse.',"figures":[{"id":0,"mask":2,"pays":[0,0,25,100,200]},{"id":1,"mask":4,"pays":[0,0,25,100,200]},{"id":2,"mask":4,"pays":[0,0,10,50,100]},{"id":3,"mask":4,"pays":[0,0,10,50,100]},{"id":4,"mask":4,"pays":[0,0,10,50,100]},{"id":5,"mask":4,"pays":[0,0,4,10,30]},{"id":6,"mask":4,"pays":[0,0,4,10,30]},{"id":7,"mask":4,"pays":[0,0,4,10,30]},{"id":8,"mask":4,"pays":[0,0,4,10,30]},{"id":9,"mask":9,"pays":[0,0,2,2,2,2,2,2,2,2,2,2,2,2,2]},{"id":10,"mask":25,"pays":[0]},{"id":11,"mask":196633,"pays":[0]},{"id":12,"mask":25,"pays":[0]},{"id":13,"mask":57,"pays":[10]},{"id":14,"mask":16441,"pays":[30]},{"id":15,"mask":49209,"pays":[1000]}],"lines":[{"id":0,"positions":[[0,1],[1,1],[2,1],[3,1],[4,1]]},{"id":1,"positions":[[0,0],[1,0],[2,0],[3,0],[4,0]]},{"id":2,"positions":[[0,2],[1,2],[2,2],[3,2],[4,2]]},{"id":3,"positions":[[0,0],[1,1],[2,2],[3,1],[4,0]]},{"id":4,"positions":[[0,2],[1,1],[2,0],[3,1],[4,2]]},{"id":5,"positions":[[0,1],[1,2],[2,2],[3,2],[4,1]]},{"id":6,"positions":[[0,1],[1,0],[2,0],[3,0],[4,1]]},{"id":7,"positions":[[0,2],[1,2],[2,1],[3,0],[4,0]]},{"id":8,"positions":[[0,0],[1,0],[2,1],[3,2],[4,2]]},{"id":9,"positions":[[0,2],[1,1],[2,1],[3,1],[4,0]]},{"id":10,"positions":[[0,0],[1,1],[2,1],[3,1],[4,2]]},{"id":11,"positions":[[0,1],[1,2],[2,1],[3,2],[4,1]]},{"id":12,"positions":[[0,1],[1,0],[2,1],[3,0],[4,1]]},{"id":13,"positions":[[0,2],[1,2],[2,1],[3,2],[4,2]]},{"id":14,"positions":[[0,0],[1,0],[2,1],[3,0],[4,0]]},{"id":15,"positions":[[0,0],[1,1],[2,1],[3,1],[4,0]]},{"id":16,"positions":[[0,2],[1,1],[2,1],[3,1],[4,2]]},{"id":17,"positions":[[0,0],[1,2],[2,0],[3,2],[4,0]]},{"id":18,"positions":[[0,2],[1,0],[2,2],[3,0],[4,2]]},{"id":19,"positions":[[0,2],[1,0],[2,1],[3,2],[4,0]]},{"id":20,"positions":[[0,0],[1,2],[2,1],[3,0],[4,2]]},{"id":21,"positions":[[0,2],[1,1],[2,2],[3,1],[4,2]]},{"id":22,"positions":[[0,0],[1,1],[2,0],[3,1],[4,0]]},{"id":23,"positions":[[0,1],[1,1],[2,0],[3,1],[4,1]]},{"id":24,"positions":[[0,1],[1,1],[2,2],[3,1],[4,1]]}],"replay_leaderboard_show_status":false,"replay_status":false,"restoreData":'.$restoreData.'},"gameServerId":"gs-5.121.120-skg-rc-skg-1-504","jackpotsData":{"enabled":false},"offersData":[],"snivyId":"snivy-30.35.43-rc-skg-0"},"result":true,"sesId":"10000205144"}';
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
                                $linesId = $slotSettings->GetPaylines();                                
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
                                $spinAcquired = false;                                
                                $lastHoldnLinkCount = $slotSettings->GetGameData($slotSettings->slotId . 'lastHoldnLinkCount');
                                for( $i = 0; $i <= 500; $i++ ) 
                                {
                                    $totalWin = 0;
                                    $holdnWin = 0;
                                    $lineWins = [];
                                    $winsMatrix = [[-1,-1,-1],[-1,-1,-1],[-1,-1,-1],[-1,-1,-1],[-1,-1,-1]];
                                    $cWins = array_fill(0, $lines, 0);
                                    $wild = ['0'];
                                    $scatter = 9;
                                    $reels = $slotSettings->GetReelStrips($winType, $postData['slotEvent']);
                                    if($postData['slotEvent'] == 'bet' && rand(0, 100) < 10 && $winType == 'win')
                                    {
                                        $bigSym = rand(0, 9);                                        
                                        $reels['reel2'][0] = $bigSym;
                                        $reels['reel2'][1] = $bigSym;
                                        $reels['reel2'][2] = $bigSym;
                                        $reels['reel3'][0] = $bigSym;
                                        $reels['reel3'][1] = $bigSym;
                                        $reels['reel3'][2] = $bigSym;
                                        $reels['reel4'][0] = $bigSym;
                                        $reels['reel4'][1] = $bigSym;
                                        $reels['reel4'][2] = $bigSym;
                                        $firstSym = rand(0, 8);
                                        $reels['reel1'][0] = $firstSym;
                                        $reels['reel1'][1] = $firstSym;
                                        $reels['reel1'][2] = $firstSym;
                                        $winsMatrix[0] = [$firstSym, $firstSym, $firstSym];
                                        $winsMatrix[1] = [$bigSym, $bigSym, $bigSym];
                                        $winsMatrix[2] = [$bigSym, $bigSym, $bigSym];
                                        $winsMatrix[3] = [$bigSym, $bigSym, $bigSym];
                                    }
                                    if( $postData['slotEvent'] == $HOLDNLINK_TAG ) 
                                    {
                                        $lastReels = $slotSettings->GetGameData($slotSettings->slotId . 'LastReels');
											file_put_contents('C:\inetpub\GameLogs\GameInternal.log', "\r\n --- Trigger here lastreels:".json_encode($lastReels)." ---",FILE_APPEND);
                               
                                        for($r = 1; $r <= 5; $r++)
                                            for($c = 0; $c < 3; $c++)
                                            {
                                                if($lastReels['reel'.$r][$c] == '11')
                                                    $reels['reel'.$r][$c] = '11';  
                                                if($lastReels['reel'.$r][$c] == '13')
                                                    $reels['reel'.$r][$c] = '13'; 
                                                if($lastReels['reel'.$r][$c] == '14')
                                                    $reels['reel'.$r][$c] = '14';                                                   
                                            }
                                    }
                                    //check 11 value for holdnLink
                                    for($r = 0; $r < 5; $r++)
                                        for($c = 0; $c < 3; $c++)
                                        {
                                            if($reels['reel' .($r+1)][$c] == '11')
                                                $winsMatrix[$r][$c] = rand(1, 8);
                                            if($reels['reel' .($r+1)][$c] == '13')
                                                $winsMatrix[$r][$c] = 10;
                                            if($reels['reel' .($r+1)][$c] == '14')
                                                $winsMatrix[$r][$c] = 30;

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
                                    
                                    if($postData['slotEvent'] == $FREESPIN_TAG)
                                    {
                                        $winsMatrix[1] = array_fill(0, 3, $reels['reel2'][0]);
                                        $winsMatrix[2] = array_fill(0, 3, $reels['reel2'][0]);
                                        $winsMatrix[3] = array_fill(0, 3, $reels['reel2'][0]);
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
                                                    $p3 = $linesId[$k][3];
                                                    $p4 = $linesId[$k][4];
                                                    $s[0] = $reels['reel1'][$p0];
                                                    $s[1] = $reels['reel2'][$p1];
                                                    $s[2] = $reels['reel3'][$p2];
                                                    $s[3] = $reels['reel4'][$p3];
                                                    $s[4] = $reels['reel5'][$p4];
                                                    
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
                                            }
                                            if( $cWins[$k] > 0 && count($lineWin) > 0 ) 
                                            {
                                                array_push($lineWins, $lineWin);
                                                $totalWin += $cWins[$k];
                                            }
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
                                        $scatterWin = $allbet * 2;
                                        $lineWin = ['figureId' => $scatter, 'lineId' => 255, 'mask' => 2, 'positions' => $scatterPos, 'profit' => $scatterWin * 100];
                                        array_push($lineWins, $lineWin);
                                    }
                                    $totalWin += $scatterWin;

                                    $holdnlinkCount = 0;
                                    $holdnPos = [];
                                    for($r = 0; $r < 5; $r++)
                                        for($c = 0; $c < 3; $c++)
                                        {
                                            if($reels['reel' .($r+1)][$c] == '11' || $reels['reel' .($r+1)][$c] == '13' || $reels['reel' .($r+1)][$c] == '14')
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
                                    }

                                    if($debug)
                                    {
                                        $spinAcquired = true;
                                        break;
                                    }
                                    if($lastHoldnLinkCount == 0 && $winType == 'bonus' && $holdnlinkCount > 5)
                                        $spinWinLimit = $spinWinLimit * 10;
                                    if($totalWin <= $spinWinLimit && (($totalWin > 0 && $winType != 'none') || ($winType == 'bonus' && $scattersCount > 2) || ($winType == 'bonus' && $lastHoldnLinkCount == 0 && $holdnlinkCount > 5)))
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

                                if($totalWin > 0 && !$spinAcquired)
                                {
                                    $lineWins = $minLineWins;
                                    $reels = $minReels;
                                    $totalWin = $minTotalWin;
                                    $scattersCount = $minScatterCount;
                                    $holdnlinkCount = $minHoldLinkCount;
                                    $winsMatrix = $minWinsMatrix;
                                    $holdnWin = $minHoldnWin;
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

                                if( $postData['slotEvent'] == $FREESPIN_TAG) 
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin') + $totalWin);
                                }
                                else
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $totalWin);
                                }
                                $nextBonusType = -1;

                                //check holdnlink
                                if( $holdnlinkCount > 5)
                                {
                                    if($holdnlinkCount > $lastHoldnLinkCount)
                                    {
                                        if($lastHoldnLinkCount == 0)
                                        {
											file_put_contents('C:\inetpub\GameLogs\GameInternal.log', "\r\n ---Triggers HoldNWin: ---",FILE_APPEND);
											//triggering holdnlink
                                            $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $totalWin);
                                        }
                                        $holdnQuantity['bonusMoreCount'] = 3;
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeGamesHoldnLink', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink') + 3);
										file_put_contents('C:\inetpub\GameLogs\GameInternal.log', "\r\n --- Trigger HoldNWin end: ---",FILE_APPEND);
                                    }
                                    $slotSettings->SetGameData($slotSettings->slotId . 'lastHoldnLinkCount', $holdnlinkCount);

                                    $holdnQuantity['bonusPlayedCount'] = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink');
                                    $holdnQuantity['bonusTotalCount'] = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGamesHoldnLink');
                                    $nextBonusType = 1;

                                    if($holdnQuantity['bonusPlayedCount'] >= $holdnQuantity['bonusTotalCount'])
                                    {file_put_contents('C:\inetpub\GameLogs\GameInternal.log', "\r\n --- HoldNWin ended: ---",FILE_APPEND);
                                        //holdn ended
                                        for($r = 0; $r < 5; $r++)
                                            for($c = 0; $c < 3; $c++)
                                            {
                                                $times = $winsMatrix[$r][$c];
                                                if($times != -1)
                                                {
                                                    $profit = $allbet * $times * 100;
                                                    $lineWin = ['figureId' => 11, 'lineId' => 255, 'mask' => 4, 'positions' => [$r, $c], 'profit' => $profit];
                                                    array_push($lineWins, $lineWin);
                                                }                                                
                                            }
                                        $totalWin += $holdnWin;
                                        $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin') + $holdnWin);
                                    
											file_put_contents('C:\inetpub\GameLogs\GameInternal.log', "\r\n --- HoldNWin ended end: ---",FILE_APPEND);
									}
                                    $slotSettings->SetGameData($slotSettings->slotId . 'LastReels', $reels);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'WinMatrix', $winsMatrix);                                    
                                }                                

                                if( $scattersCount >= 3 ) 
                                {
                                    if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') > 0 ) 
                                    {
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') + 3);
                                        $freespinQuantity['bonusMoreCount'] = 3;
                                    }
                                    else
                                    {
                                        //triggering freespin
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeStartWin', $totalWin);
                                        $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $totalWin);
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $slotSettings->slotFreeCount);
                                        $freespinQuantity['bonusMoreCount'] = $slotSettings->slotFreeCount;
                                        file_put_contents('C:\inetpub\GameLogs\GameInternal.log', "\r\n setfree: ".$slotSettings->slotFreeCount,FILE_APPEND);     
									 $nextBonusType = 0;
                                    }                                    
                                }                                

                                $freespinQuantity['bonusTotalCount'] = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                                $freespinQuantity['bonusPlayedCount'] = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');                                                                                      
                                	 file_put_contents('C:\inetpub\GameLogs\GameInternal.log', "\r\n getfree: ".$freespinQuantity['bonusTotalCount'],FILE_APPEND);     
									
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
file_put_contents('C:\inetpub\GameLogs\GameInternal.log', "\r\n ---BuildHoldNWin: ---",FILE_APPEND);							
                                    $reelsym = [];
                                    $tmpWinMatrix = [];
                                    for($c = 0; $c < 3; $c++)
                                        for($r = 0; $r < 5; $r++)                                        
                                        {
                                            $reelsym[] = [$reels['reel'.($r+1)][$c]];
                                            $tmpWinMatrix[] = [$winsMatrix[$r][$c]];
                                        }

                                    $winsMatrix = $tmpWinMatrix;             

		file_put_contents('C:\inetpub\GameLogs\GameInternal.log', "\r\n ---BuildHoldNWin end: ---",FILE_APPEND);									
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
                                        'winsMatrix' => $winsMatrix
                                    ],
                                    'result' => true,
                                    'sesId' => '10000000223'
                                ];

                                if($postData['slotEvent'] == $HOLDNLINK_TAG)
                                {
										file_put_contents('C:\inetpub\GameLogs\GameInternal.log', "\r\n ---More HoldNWin: ---",FILE_APPEND);
                                    $nextBonusType = 1;
                                    $currentBonusType = 1;
                                    if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGamesHoldnLink') <= $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink'))
                                    {
                                        $nextBonusType = -1;
                                        $betData['data']['win'] = $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin') * 100;
                                    }   
                                    $betData['data']['currentBonusType'] = $currentBonusType;
											file_put_contents('C:\inetpub\GameLogs\GameInternal.log', "\r\n ---More HoldNWin end: ---",FILE_APPEND);
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
                                        'series' => $lineWins,
                                        'win' => $betData['data']['win'],
                                        'winsMatrix' => $winsMatrix,
                                        'basicWinsMatrix' => $basicWinsMatrix,
                                        'totalWin' => $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin'),
                                        'reels' => $reels
                                    ]];
									
									 file_put_contents('C:\inetpub\GameLogs\GameInternal.log', "\r\n basicWinsMatrix: ".json_encode($basicWinsMatrix),FILE_APPEND);     
									 file_put_contents('C:\inetpub\GameLogs\GameInternal.log', "\r\n winsmatrix: ".json_encode($winsMatrix),FILE_APPEND);     
									 file_put_contents('C:\inetpub\GameLogs\GameInternal.log', "\r\n reels: ".json_encode($reels),FILE_APPEND);     
                                $response = json_encode($spinData);
									 file_put_contents('C:\inetpub\GameLogs\GameInternal.log', "\r\n spindata: ".json_encode($response),FILE_APPEND);  
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
