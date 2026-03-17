<?php 
namespace VanguardLTE\Games\ShiningPrincessNG
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
                    $debug = false;
                    try
                    {
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
                        $postData = json_decode(trim(file_get_contents('php://input')), true);
                        $result_tmp = [];
                        if( isset($postData['gameData']) ) 
                        {
                            $postData = $postData['gameData'];
                            $reqId = $postData['cmd'];
                            if( !isset($postData['cmd']) ) 
                            {
                                $response = '{"responseEvent":"error","responseType":"","serverResponse":"incorrect action"}';
                                exit( $response );
                            }
                        }
                        else
                        {
                            $reqId = $postData['action'];
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
                        switch( $reqId ) 
                        {
                            case 'InitRequest':
                                $result_tmp[0] = '{"action":"InitResponce","result":true,"sesId":"a40e5dc15a83a70f288e421fbcfc6de8","data":{"id":16183084}}';
                                exit( $result_tmp[0] );
                                break;
                            case 'EventsRequest':
                                $result_tmp[0] = '{"action":"EventsResponce","result":true,"sesId":"a40e5dc15a83a70f288e421fbcfc6de8","data":[]}';
                                exit( $result_tmp[0] );
                                break;
                            case 'APIVersionRequest':
                                $result_tmp[] = '{"action":"APIVersionResponse","result":true,"sesId":false,"data":{"router":"v3.12","transportConfig":{"reconnectTimeout":500000000000}}}';
                                break;                            
                            case 'CheckBrokenGameRequest':
                                $result_tmp[] = '{"action":"CheckBrokenGameResponse","result":"true","sesId":"false","data":{"haveBrokenGame":"false"}}';
                                break;                            
                            case 'AuthRequest':
                                // $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', 0);
                                // $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeBalance', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeStartWin', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'BonusSymbol', -1);                                
                                $slotSettings->SetGameData($slotSettings->slotId . 'LanternSymbols', []);
                                $lastEvent = $slotSettings->GetHistory();
                                if( $lastEvent != 'NULL' ) 
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', $lastEvent->serverResponse->bonusWin);
                                    // $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $lastEvent->serverResponse->totalFreeGames);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $lastEvent->serverResponse->currentFreeGames);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $lastEvent->serverResponse->bonusWin);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BonusSymbol', $lastEvent->serverResponse->BonusSymbol);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeBalance', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeStartWin', 0);
                                    $rp2 = '[' . $lastEvent->serverResponse->reelsSymbols->reel1[0] . ',' . $lastEvent->serverResponse->reelsSymbols->reel2[0] . ',' . $lastEvent->serverResponse->reelsSymbols->reel3[0] . ',' . $lastEvent->serverResponse->reelsSymbols->reel4[0] . ',' . $lastEvent->serverResponse->reelsSymbols->reel5[0] . ']';
                                    $rp2 .= (',[' . $lastEvent->serverResponse->reelsSymbols->reel1[1] . ',' . $lastEvent->serverResponse->reelsSymbols->reel2[1] . ',' . $lastEvent->serverResponse->reelsSymbols->reel3[1] . ',' . $lastEvent->serverResponse->reelsSymbols->reel4[1] . ',' . $lastEvent->serverResponse->reelsSymbols->reel5[1] . ']');
                                    $rp2 .= (',[' . $lastEvent->serverResponse->reelsSymbols->reel1[2] . ',' . $lastEvent->serverResponse->reelsSymbols->reel2[2] . ',' . $lastEvent->serverResponse->reelsSymbols->reel3[2] . ',' . $lastEvent->serverResponse->reelsSymbols->reel4[2] . ',' . $lastEvent->serverResponse->reelsSymbols->reel5[2] . ']');
                                    $bet = $lastEvent->serverResponse->slotBet * 100 * 20;
                                }
                                else
                                {                                    
                                    $rp_1 = rand(0, count($slotSettings->reelStrip1) - 3);
                                    $rp_2 = rand(0, count($slotSettings->reelStrip2) - 3);
                                    $rp_3 = rand(0, count($slotSettings->reelStrip3) - 3);
                                    $rp_4 = rand(0, count($slotSettings->reelStrip4) - 3);
                                    $rp_5 = rand(0, count($slotSettings->reelStrip5) - 3);
                                    $rr1 = $slotSettings->reelStrip1[$rp_1];
                                    $rr2 = $slotSettings->reelStrip2[$rp_2];
                                    $rr3 = $slotSettings->reelStrip3[$rp_3];
                                    $rr4 = $slotSettings->reelStrip4[$rp_4];
                                    $rr5 = $slotSettings->reelStrip5[$rp_5];
                                    $rp2 = '[' . $rr1 . ',' . $rr2 . ',' . $rr3 . ',' . $rr4 . ',' . $rr5 . ']';
                                    $rr1 = $slotSettings->reelStrip1[$rp_1 + 1];
                                    $rr2 = $slotSettings->reelStrip2[$rp_2 + 1];
                                    $rr3 = $slotSettings->reelStrip3[$rp_3 + 1];
                                    $rr3 = $slotSettings->reelStrip4[$rp_4 + 1];
                                    $rr3 = $slotSettings->reelStrip5[$rp_5 + 1];
                                    $rp2 .= (',[' . $rr1 . ',' . $rr2 . ',' . $rr3 . ',' . $rr4 . ',' . $rr5 . ']');
                                    $rr1 = $slotSettings->reelStrip1[$rp_1 + 2];
                                    $rr2 = $slotSettings->reelStrip2[$rp_2 + 2];
                                    $rr3 = $slotSettings->reelStrip3[$rp_3 + 2];
                                    $rr3 = $slotSettings->reelStrip4[$rp_4 + 2];
                                    $rr3 = $slotSettings->reelStrip5[$rp_5 + 2];
                                    $rp2 .= (',[' . $rr1 . ',' . $rr2 . ',' . $rr3 . ',' . $rr4 . ',' . $rr5 . ']');
                                    $bet = $slotSettings->Bet[0] * 100 * 20;
                                    $slotSettings->SetGameData($slotSettings->slotId . 'LanternSymbols', []);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BonusTotalPickCount', 0);
                                }
                                if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') == $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') ) 
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                                }
                                $restoreString = '';
                                $defaultBet = $slotSettings->GetGameData($slotSettings->slotId . 'BetLine');
                                if($defaultBet == 0)
                                    $defaultBet = 1;
                                    
                                $gameState = $slotSettings->GetGameData($slotSettings->slotId . 'GameState');
                                /*
                                if($gameState == 'ReSpins')
                                {
                                    if( $slotSettings->GetGameData($slotSettings->slotId . 'RespinGames') <= $slotSettings->GetGameData($slotSettings->slotId . 'CurrentRespin') ) 
                                    {
                                        if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') > $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') )                                        
                                        {
                                            $fBonusWin = $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin');
                                            $fTotal = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                                            $fCurrent = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                                            $fRemain = $fTotal - $fCurrent;
                                        
                                            $restoreString = ',"restoredGameCode":"171","lastResponse":{"spinResult":{"type":"SpinResult","rows":[' . $rp2 . ']},"freeSpinsTotal":"' . $fTotal . '","freeSpinRemain":"' . $fRemain . '","totalBonusWin":"' . $fBonusWin . '","state":"FreeSpins"}';
                                        }
                                    }
                                    else
                                    {
                                        $fBonusWin = $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin');
                                        $reSpinRemain = $slotSettings->GetGameData($slotSettings->slotId . 'RespinGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentRespin');
                                        $reSpinsTotal = $slotSettings->GetGameData($slotSettings->slotId . 'RespinGames');
                                        $restoreString = ',"restoredGameCode":"171","lastResponse":{"spinResult":{"type":"SpinResult","rows":[' . $rp2 . ']},"reSpinTotal":"' . $reSpinsTotal . '","reSpinRemain":"' . $reSpinRemain . '","totalBonusWin":"' . $fBonusWin . '","state":"ReSpins"}';
                                    }
                                }
                                else*/
                                // if($gameState == 'FreeSpins')
                                {
                                    if( $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') < $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') ) 
                                    {
                                        $fBonusWin = $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin');
                                        $fTotal = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                                        $fCurrent = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                                        $fRemain = $fTotal - $fCurrent;
                                        
                                        $restoreString = ',"restoredGameCode":"171","lastResponse":{"spinResult":{"type":"SpinResult","rows":[' . $rp2 . ']},"freeSpinsTotal":"' . $fTotal . '","freeSpinRemain":"' . $fRemain . '","totalBonusWin":"' . $fBonusWin . '","state":"FreeSpins"}';
                                    }
                                }
                                $result_tmp[0] = '{"action":"AuthResponse","result":"true","sesId":"10000153653","data":{"snivy":"proxy v6.12.51 (API v4.23)","supportedFeatures":["Offers","Jackpots","InstantJackpots","SweepStakes"],"sessionId":"10000153653","defaultLines":["0","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","21","22","23","24","25","26","27","28","29","30","31","32","33","34","35","36","37","38","39"],"bets":["1","2","3","4","5","10"],"betMultiplier":"1.0000000","defaultBet":"'.$defaultBet.'","defaultCoinValue":"0.01","coinValues":["0.01"],"gameParameters":{"availableLines":[["0","0","0","0","0"],["0","0","0","0","1"],["1","1","1","1","0"],["1","1","1","1","1"],["2","2","2","2","2"],["3","3","3","3","3"],["3","2","1","2","3"],["2","1","0","1","2"],["1","2","3","2","1"],["0","1","2","1","0"],["1","1","1","1","2"],["2","2","2","2","1"],["2","2","2","2","3"],["3","3","3","3","2"],["2","3","3","3","3"],["3","2","2","2","2"],["1","2","2","2","2"],["2","1","1","1","1"],["0","1","1","1","1"],["1","0","0","0","0"],["0","0","0","1","2"],["0","0","1","0","0"],["1","1","0","1","1"],["1","1","1","2","3"],["2","2","2","1","0"],["3","3","3","2","1"],["1","2","3","3","3"],["0","1","2","2","2"],["3","2","1","1","1"],["2","1","0","0","0"],["1","1","2","1","1"],["2","2","1","2","2"],["2","2","3","2","2"],["3","3","2","3","3"],["3","2","2","2","3"],["2","3","3","3","2"],["2","1","1","1","2"],["1","2","2","2","1"],["1","0","0","0","1"],["0","1","1","1","0"]],"rtp":"94.13","payouts":[{"payout":"2","symbols":["0","0"],"type":"basic"},{"payout":"20","symbols":["0","0","0"],"type":"basic"},{"payout":"50","symbols":["0","0","0","0"],"type":"basic"},{"payout":"150","symbols":["0","0","0","0","0"],"type":"basic"},{"payout":"16","symbols":["1","1","1"],"type":"basic"},{"payout":"42","symbols":["1","1","1","1"],"type":"basic"},{"payout":"100","symbols":["1","1","1","1","1"],"type":"basic"},{"payout":"16","symbols":["2","2","2"],"type":"basic"},{"payout":"42","symbols":["2","2","2","2"],"type":"basic"},{"payout":"100","symbols":["2","2","2","2","2"],"type":"basic"},{"payout":"10","symbols":["3","3","3"],"type":"basic"},{"payout":"40","symbols":["3","3","3","3"],"type":"basic"},{"payout":"80","symbols":["3","3","3","3","3"],"type":"basic"},{"payout":"10","symbols":["4","4","4"],"type":"basic"},{"payout":"40","symbols":["4","4","4","4"],"type":"basic"},{"payout":"80","symbols":["4","4","4","4","4"],"type":"basic"},{"payout":"6","symbols":["5","5","5"],"type":"basic"},{"payout":"30","symbols":["5","5","5","5"],"type":"basic"},{"payout":"60","symbols":["5","5","5","5","5"],"type":"basic"},{"payout":"6","symbols":["6","6","6"],"type":"basic"},{"payout":"30","symbols":["6","6","6","6"],"type":"basic"},{"payout":"60","symbols":["6","6","6","6","6"],"type":"basic"},{"payout":"6","symbols":["7","7","7"],"type":"basic"},{"payout":"30","symbols":["7","7","7","7"],"type":"basic"},{"payout":"60","symbols":["7","7","7","7","7"],"type":"basic"},{"payout":"4","symbols":["8","8","8"],"type":"basic"},{"payout":"20","symbols":["8","8","8","8"],"type":"basic"},{"payout":"50","symbols":["8","8","8","8","8"],"type":"basic"},{"payout":"4","symbols":["9","9","9"],"type":"basic"},{"payout":"20","symbols":["9","9","9","9"],"type":"basic"},{"payout":"50","symbols":["9","9","9","9","9"],"type":"basic"},{"payout":"4","symbols":["10","10","10"],"type":"basic"},{"payout":"20","symbols":["10","10","10","10"],"type":"basic"},{"payout":"50","symbols":["10","10","10","10","10"],"type":"basic"}],"initialSymbols":[["5","11","7","1","1"],["11","6","4","1","6"],["10","10","5","9","8"],["6","4","11","4","3"]]},"jackpotsEnabled":"true","gameModes":"[]" '.$restoreString.'}}';
                                break;
                            case 'BalanceRequest':
                                $result_tmp[] = '{"action":"BalanceResponse","result":"true","sesId":"10000214325","data":{"entries":"0.00","totalAmount":"' . $slotSettings->GetBalance() . '","currency":" "}}';
                                break;
                            case 'PickBonusItemRequest':                               
                                if(rand(0, 100) < 10)
                                {
                                    //pick
                                    $type = 'Picks';
                                    $value = rand(1,2);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BonusTotalPickCount', $slotSettings->GetGameData($slotSettings->slotId . 'BonusTotalPickCount') + $value);
                                }
                                else
                                {
                                    //freespin
                                    $type = 'FreeSpins';
                                    $value = rand(1,4);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') + $value);
                                }
                                
                                $state = 'PickBonus';
                                $lastPick = 'false';
                                $slotSettings->SetGameData($slotSettings->slotId . 'BonusPickCount', $slotSettings->GetGameData($slotSettings->slotId . 'BonusPickCount') + 1);
                                $params = [];
                                $leftPick = $slotSettings->GetGameData($slotSettings->slotId . 'BonusTotalPickCount') - $slotSettings->GetGameData($slotSettings->slotId . 'BonusPickCount');
                                if($leftPick == 0)
                                {
                                    $lastPick = 'true';
                                    $type = 'FreeSpins';
                                    $total = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                                    $params = [
                                        'freeSpins' => $total,
                                        'freeSpinRemain' => $total,
                                        'freeSpinsTotal' => $total] ;
                                    $state = 'FreeSpins';
                                }
                                $res = [
                                    'action' => 'PickBonusItemResponse',
                                    'result' => 'true',
                                    'sesId' => "10000214325",
                                    'data' => [
                                        'lastPick' => $lastPick,
                                        'state' => $state,
                                        'params' => $params,
                                        'items' => [],
                                        'bonusItem' => [
                                            'index' => $postData['data']['index'],
                                            'value' => $value,
                                            'picked' => 'true',
                                            'type' => $type
                                        ]
                                    ]
                                ];

                                $result_tmp[] = json_encode($res);
                                break;
                            case 'RespinRequest':
                            case 'FreeSpinRequest':
                            case 'FreeRespinRequest':
                            case 'SpinRequest':
                                $postData['slotEvent'] = 'bet';
                                $bonusMpl = 1;
                                $linesId = $slotSettings->GetPayLines();                                
                                $lines = 40;
                                $betLine = $postData['data']['coin'] * $postData['data']['bet'];                                
                                $slotSettings->SetGameData($slotSettings->slotId . 'BetLine', $postData['data']['bet']);
                                $allbet = $betLine * $lines;
                                
                                if( !isset($postData['slotEvent']) ) 
                                {
                                    $postData['slotEvent'] = 'bet';
                                }

                                if( $reqId == 'FreeSpinRequest' )
                                {
                                    $postData['slotEvent'] = 'freespin';
                                }
                                else if( $reqId == 'RespinRequest' || $reqId == 'FreeRespinRequest' )
                                {
                                    $postData['slotEvent'] = 'respin';
                                }
                                if( $postData['slotEvent'] == 'bet' ) 
                                {
                                    $slotSettings->SetBalance(-1 * $allbet, $postData['slotEvent']);
                                    $bankSum = $allbet / 100 * $slotSettings->GetPercent();
                                    $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $bankSum, $postData['slotEvent']);
                                    $slotSettings->UpdateJackpots($allbet);
                                    $slotSettings->SetBet($allbet);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'RespinGames', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentRespin', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BonusSymbol', -1);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeBalance', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeStartWin', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'WildsArr', []);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'GameState', 'Ready');
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreespinMultiplier', 1);
                                }
                                else
                                {
                                    if($postData['slotEvent'] == 'freespin')
                                        $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);
                                    else if($postData['slotEvent'] == 'respin')
                                        $slotSettings->SetGameData($slotSettings->slotId . 'CurrentRespin', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentRespin') + 1);
                                }
                                $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $lines);
                                $winType = $winTypeTmp[0];
                                $spinWinLimit = $winTypeTmp[1];
                                
                                if($debug && $postData['slotEvent'] != 'freespin')
                                    $winType = 'bonus';
                                $spinAcquired = false;

                                $minLineWins = [];
                                $minTotalWin = -1;
                                $minReels = [];

                                $spinAcquired = false;
                                $wild = ['12'];
                                $scatter = '11';
                                $respinSym = '0';

                                for( $i = 0; $i <= 300; $i++ ) 
                                {
                                    $totalWin = 0;
                                    $lineWins = [];
                                    $cWins = array_fill(0, 50, 0);
                                                                  
                                    $reels = $slotSettings->GetReelStrips($winType, $postData['slotEvent']);
                                    // if($winType == 'win' && $spinWinLimit > $allbet * 2 && rand(0, 100) < 100)
                                    // {
                                    //     $reels['reel1'] = [0,0,0,0];
                                    // }
                                    if($postData['slotEvent'] == 'respin')
                                    {
                                        $lastReels = $slotSettings->GetGameData($slotSettings->slotId . 'LastReels');
                                        for($r = 0; $r < 5; $r++)
                                        {
                                            for($c = 0; $c <= 3; $c++)
                                            {
                                                if($lastReels['reel'.($r+1)][$c] == $respinSym || $lastReels['reel'.($r+1)][$c] == $wild[0])
                                                {
                                                    $reels['reel'.($r+1)][$c] = $lastReels['reel'.($r+1)][$c];
                                                }
                                            }
                                        }
                                    }

                                    for( $k = 0; $k < count($linesId); $k++ ) 
                                    {
                                        $tmpStringWin = '';
                                        for( $j = 0; $j < count($slotSettings->SymbolGame); $j++ )
                                        {
                                            $csym = $slotSettings->SymbolGame[$j];
                                            if( $csym == $scatter || !isset($slotSettings->Paytable['SYM_' . $csym]) ) 
                                            {
                                            }
                                            else
                                            {
                                                $s = [];
                                                $s[0] = $reels['reel1'][$linesId[$k][0]];
                                                $s[1] = $reels['reel2'][$linesId[$k][1]];
                                                $s[2] = $reels['reel3'][$linesId[$k][2]];
                                                $s[3] = $reels['reel4'][$linesId[$k][3]];
                                                $s[4] = $reels['reel5'][$linesId[$k][4]];
                                                $p0 = $linesId[$k][0];
                                                $p1 = $linesId[$k][1];
                                                $p2 = $linesId[$k][2];
                                                $p3 = $linesId[$k][3];
                                                $p4 = $linesId[$k][4];
                                                if( $s[0] == $csym || in_array($s[0], $wild) ) 
                                                {
                                                    $mpl = 1;
                                                    $tmpWin = $slotSettings->Paytable['SYM_' . $csym][1] * $betLine * $mpl * $bonusMpl;
                                                    if( $cWins[$k] < $tmpWin ) 
                                                    {
                                                        $cWins[$k] = $tmpWin;
                                                        $tmpStringWin = '{"type":"LineWinAmount","selectedLine":"' . $k . '","amount":"' . $tmpWin . '","wonSymbols":[["0","' . $p0 . '"]]}';
                                                    }
                                                }
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
                                                        $tmpStringWin = '{"type":"LineWinAmount","selectedLine":"' . $k . '","amount":"' . $tmpWin . '","wonSymbols":[["0","' . $p0 . '"],["1","' . $p1 . '"]]}';
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
                                                        $tmpStringWin = '{"type":"LineWinAmount","selectedLine":"' . $k . '","amount":"' . $tmpWin . '","wonSymbols":[["0","' . $p0 . '"],["1","' . $p1 . '"],["2","' . $p2 . '"]]}';
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
                                                        $tmpStringWin = '{"type":"LineWinAmount","selectedLine":"' . $k . '","amount":"' . $tmpWin . '","wonSymbols":[["0","' . $p0 . '"],["1","' . $p1 . '"],["2","' . $p2 . '"],["3","' . $p3 . '"]]}';
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
                                                        $tmpStringWin = '{"type":"LineWinAmount","selectedLine":"' . $k . '","amount":"' . $tmpWin . '","wonSymbols":[["0","' . $p0 . '"],["1","' . $p1 . '"],["2","' . $p2 . '"],["3","' . $p3 . '"],["4","' . $p4 . '"]]}';
                                                    }
                                                }
                                            }
                                        }
                                        if( $cWins[$k] > 0 && $tmpStringWin != '' ) 
                                        {
                                            array_push($lineWins, $tmpStringWin);
                                            $totalWin += $cWins[$k];
                                        }
                                    }
                                    
                                    
                                    $scattersWin = 0;
                                    
                                    $scattersPos = [];
                                    $scattersCount = 0;
                                    $respinPos = [];
                                    $respinCount = 0;
                                    for( $r = 1; $r <= 5; $r++ ) 
                                    {
                                        for( $p = 0; $p <= 3; $p++ ) 
                                        {
                                            if( $reels['reel' . $r][$p] == $scatter ) 
                                            {
                                                $scattersCount++;
                                                $scattersPos[] = [($r - 1), $p];
                                            }
                                            else if( $reels['reel' . $r][$p] == $respinSym && $r == 1 ) 
                                            {
                                                $respinCount++;
                                                $respinPos[] = [($r - 1), $p];
                                            }       
                                        }
                                    }
                                    if($scattersCount >= 3 && $winType != 'bonus')
                                        continue;
                                    if($scattersCount >= 3 && $respinCount > 3)
                                    {
                                        continue;
                                    }
                                    
                                    $scattersWin = $slotSettings->Paytable['SYM_' . $scatter][$scattersCount] * $betLine;
                                    $gameState = 'Ready';
                                    if( $scattersCount >= 3 )
                                    {
                                        $scw = [
                                            'amount' => number_format($scattersWin, 2),
                                            'bonusName' => 'FreeSpins',
                                            'params' => [
                                                'freeSpins'=>'12'
                                            ],
                                            'type' => 'Bonus',
                                            'wonSymbols' => $scattersPos
                                        ];
                                        $gameState = 'FreeSpins';
                                        array_push($lineWins, json_encode($scw));
                                    }

                                    if($respinCount > 3 && $postData['slotEvent'] != 'respin')
                                    {
                                        $rw = [
                                            'bonusName' => 'ReSpins',
                                            'params' => [
                                                'reSpins'=>'2'
                                            ],
                                            'type' => 'Bonus',
                                            'wonSymbols' => $respinPos
                                        ];
                                        $gameState = 'ReSpins';
                                        array_push($lineWins, json_encode($rw));
                                    }
                                    
                                    $totalWin += $scattersWin;
                                    
                                    if($minTotalWin == -1 || ($totalWin > 0 && $totalWin < $minTotalWin))
                                    {
                                        $minLineWins = $lineWins;
                                        $minReels = $reels;
                                        $minTotalWin = $totalWin;
                                        $minScattersCount = $scattersCount;
                                    }
                                    
                                    if($debug)
                                    {
                                        $spinAcquired = true;
                                        break;
                                    }                                        

                                    if($totalWin <= $spinWinLimit && (($totalWin > 0 && $winType != 'none') || ($winType == 'bonus' && $scattersCount >= 3)))
                                    {
                                        $spinAcquired = true;
                                        // if($totalWin < 0.1 * $spinWinLimit && $winType != 'bonus')
                                        //     $spinAcquired = false;
                                        if($spinAcquired)
                                            break;                                        
                                    } 
                                    else if($winType == 'none' && $totalWin == 0)
                                    {
                                        break;
                                    }                                  
                                }
                                
                                if($totalWin > 0 && !$spinAcquired)
                                {
                                    $lineWins = $minLineWins;
                                    $reels = $minReels;
                                    $totalWin = $minTotalWin;
                                    $scattersCount = $minScattersCount;
                                }

                                if( $totalWin > 0 && !$debug ) 
                                {
                                    $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), -1 * $totalWin);
                                    $slotSettings->SetBalance($totalWin);
                                    $slotSettings->SetWin($totalWin);
                                }
                                
                                $reportWin = $totalWin;
                                if( $postData['slotEvent'] == 'freespin' ) 
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin') + $totalWin);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin') + $totalWin);
                                }
                                else if($postData['slotEvent'] == 'respin')
                                {
                                    
                                }
                                else
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $totalWin);
                                }

                                if( $scattersCount >= 3 ) 
                                {
                                    if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') > 0 ) 
                                    {
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') + $slotSettings->slotFreeCount);
                                    }
                                    else
                                    {
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeStartWin', $totalWin);                                        
                                        $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', $totalWin);
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 12);
                                        $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                                    }
                                }
                                if($respinCount > 3 && $postData['slotEvent'] != 'respin')
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'RespinGames', 2);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentRespin', 0);
                                    if($postData['slotEvent'] == 'bet')
                                        $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', $totalWin);                                    
                                }

                                $jsSpin = '' . json_encode($reels) . '';
                                $jsJack = '' . json_encode($slotSettings->Jackpots) . '';
                                
                                if( count($lineWins) > 0 ) 
                                {
                                    $winString = ',"slotWin":{"totalWin":"' . $totalWin . '","lineWinAmounts":[' . implode(',', $lineWins) . '],"canGamble":"false"}';
                                }
                                else
                                {
                                    $winString = '';
                                }
                                $response = '{"responseEvent":"spin","responseType":"' . $postData['slotEvent'] . '","serverResponse":{"BonusSymbol":' . $slotSettings->GetGameData($slotSettings->slotId . 'BonusSymbol') . ',"slotLines":' . $lines . ',"slotBet":' . $betLine . ',"totalFreeGames":' . $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') . ',"currentFreeGames":' . $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') . ',"Balance":' . $slotSettings->GetBalance() . ',"afterBalance":' . $slotSettings->GetBalance() . ',"bonusWin":' . $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin') . ',"freeStartWin":' . $slotSettings->GetGameData($slotSettings->slotId . 'FreeStartWin') . ',"totalWin":' . $totalWin . ',"winLines":[],"bonusInfo":[],"Jackpots":' . $jsJack . ',"reelsSymbols":' . $jsSpin . '}}';
                                $symb = '["' . $reels['reel1'][0] . '","' . $reels['reel2'][0] . '","' . $reels['reel3'][0] . '","' . $reels['reel4'][0] . '","' . $reels['reel5'][0] . '"],["' . $reels['reel1'][1] . '","' . $reels['reel2'][1] . '","' . $reels['reel3'][1] . '","' . $reels['reel4'][1] . '","' . $reels['reel5'][1] . '"],["' . $reels['reel1'][2] . '","' . $reels['reel2'][2] . '","' . $reels['reel3'][2] . '","' . $reels['reel4'][2] . '","' . $reels['reel5'][2] . '"],["' . $reels['reel1'][3] . '","' . $reels['reel2'][3] . '","' . $reels['reel3'][3] . '","' . $reels['reel4'][3] . '","' . $reels['reel5'][3] . '"]';
                                
                                if($postData['slotEvent'] == 'freespin' || $postData['slotEvent'] == 'respin')
                                    $allbet = 0;
                                $slotSettings->SaveLogReport($response, $allbet, $reportWin, $postData['slotEvent']);
                                $slotSettings->SetGameData($slotSettings->slotId . 'LastReels', $reels);
                                if( $postData['slotEvent'] == 'freespin' ) 
                                {
                                    $bonusWin0 = $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin');
                                    $freeSpinRemain = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                                    $freeSpinsTotal = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                                    $gameState = 'FreeSpins';
                                    $gameParameters = '';
                                    if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') <= $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') && $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') > 0 ) 
                                    {
                                        $gameState = 'Ready';
                                        $gameParameters = '"gameParameters":{"initialSymbols":[' . $slotSettings->GetGameData($slotSettings->slotId . 'initialSymbols') . ']},';
                                    }
                                    $result_tmp[] = '{"action":"FreeSpinResponse","result":"true","sesId":"10000228087","data":{'.$gameParameters.'"state":"' . $gameState . '"' . $winString . ',"spinResult":{"type":"SpinResult","rows":[' . $symb . ']}, "totalBonusWin":"' . $slotSettings->FormatFloat($bonusWin0) . '","freeSpinRemain":"' . $freeSpinRemain . '","freeSpinsTotal":"' . $freeSpinsTotal . '"}}';
                                }
                                else if($postData['slotEvent'] == 'respin')
                                {
                                    $bonusWin0 = $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin');
                                    $reSpinRemain = $slotSettings->GetGameData($slotSettings->slotId . 'RespinGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentRespin');
                                    $reSpinsTotal = $slotSettings->GetGameData($slotSettings->slotId . 'RespinGames');
                                    $gameState = 'ReSpins';
                                    $action = 'RespinResponse';
                                    $freespinStr = "";
                                    if($reqId == 'FreeRespinRequest')
                                    {
                                        $gameState = 'FreeRespins';
                                        $action = 'FreeRespinResponse';
                                    }                                    
                                        
                                    if( $reSpinRemain <= 0 ) 
                                    {
                                        $reSpinRemain = 0;
                                        if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') <= $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') ) 
                                            $gameState = 'Ready';
                                        else
                                        {
                                            $gameState = 'FreeSpins';
                                            $freeSpinRemain = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                                            $freeSpinsTotal = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                                            $freespinStr = ',"freeSpinRemain":"' . $freeSpinRemain . '","freeSpinsTotal":"' . $freeSpinsTotal . '"';
                                        }
                                            
                                    }
                                    if($reSpinRemain > 0)
                                    {
                                        $winString = '';
                                    }
                                    $result_tmp[] = '{"action":"'.$action.'","result":"true","sesId":"10000228087","data":{"state":"' . $gameState . '"' . $winString . ',"spinResult":{"type":"SpinResult","rows":[' . $symb . ']}, "totalBonusWin":"' . $slotSettings->FormatFloat($bonusWin0) . '","reSpinRemain":"' . $reSpinRemain . '","reSpinTotal":"' . $reSpinsTotal . '"' . $freespinStr . '}}';
                                }
                                else
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'initialSymbols', $symb);
                                    $result_tmp[] = '{"action":"SpinResponse","result":"true","sesId":"10000373695","data":{"spinResult":{"type":"SpinResult","rows":[' . $symb . ']}' . $winString . ',"state":"' . $gameState . '"}}';
                                }
                                $slotSettings->SetGameData($slotSettings->slotId . 'GameState', $gameState);
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
