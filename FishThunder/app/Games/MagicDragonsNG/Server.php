<?php 
namespace VanguardLTE\Games\MagicDragonsNG
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
                            if( $slotSettings->GetBalance() < ($postData['data']['coin'] * $postData['data']['bet'] * 40) && $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') <= 0 ) 
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
                            case 'PickBonusItemRequest':
                                $item = $postData['data']['index'] - 1;
                                $wheelArr = [];
                                $wheelArr[] = ["2", "3", "5"];
                                $wheelArr[] = ["3", "5", "8"];
                                $wheelArr[] = ["5", "8", "10"];
                                $wheelArr[] = ["8", "10", "15"];
                                $wheelArr[] = ["10", "15", "30"];
                                
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeGameType', $item);
                                if($item == 5)
                                {
                                    $item = rand(0, 4);
                                }
                                
                                $wheelArr0 = [20, 15, 10, 8, 5];                                
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', (int)$wheelArr0[$item]);
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeGameMultipliers', $wheelArr[$item]);
                                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);                                
                                $slotSettings->SetGameData($slotSettings->slotId . 'GameState', 'FreeSpins');                                
                                $result_tmp[] = '{"action":"PickBonusItemResponse","result":"true","sesId":"10000075130","data":{"state":"FreeSpins","lastPick":"true","bonusItem":{"type":"BonusItem","index":"'.$item.'","value":"0","picked":"true"},"items":[{"type":"BonusItem","index":"'.$item.'","value":"0","picked":"true"}],"params":{"freeSpins":"'.$wheelArr0[$item].'","freeSpinsTotal":"'.$wheelArr0[$item].'","multipliers":'.json_encode($wheelArr[$item]).'}}}';
                                break;
                            case 'CheckBrokenGameRequest':
                                $result_tmp[] = '{"action":"CheckBrokenGameResponse","result":"true","sesId":"false","data":{"haveBrokenGame":"false"}}';
                                break;
                            case 'AuthRequest':                                
                                $lastEvent = $slotSettings->GetHistory();
                                if( $lastEvent != 'NULL' ) 
                                {
                                    // $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', $lastEvent->serverResponse->bonusWin);
                                    // $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $lastEvent->serverResponse->totalFreeGames);
                                    // $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $lastEvent->serverResponse->currentFreeGames);
                                    // $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $lastEvent->serverResponse->bonusWin);
                                    // $slotSettings->SetGameData($slotSettings->slotId . 'BonusSymbol', $lastEvent->serverResponse->BonusSymbol);
                                    // $slotSettings->SetGameData($slotSettings->slotId . 'FreeBalance', 0);
                                    // $slotSettings->SetGameData($slotSettings->slotId . 'FreeStartWin', 0);
                                    $rp1 = implode(',', $lastEvent->serverResponse->reelsSymbols->rp);
                                    $rp2 = '[' . $lastEvent->serverResponse->reelsSymbols->reel1[0] . ',' . $lastEvent->serverResponse->reelsSymbols->reel2[0] . ',' . $lastEvent->serverResponse->reelsSymbols->reel3[0] . ',' . $lastEvent->serverResponse->reelsSymbols->reel4[0] . ',' . $lastEvent->serverResponse->reelsSymbols->reel5[0] . ']';
                                    $rp2 .= (',[' . $lastEvent->serverResponse->reelsSymbols->reel1[1] . ',' . $lastEvent->serverResponse->reelsSymbols->reel2[1] . ',' . $lastEvent->serverResponse->reelsSymbols->reel3[1] . ',' . $lastEvent->serverResponse->reelsSymbols->reel4[1] . ',' . $lastEvent->serverResponse->reelsSymbols->reel5[1] . ']');
                                    $rp2 .= (',[' . $lastEvent->serverResponse->reelsSymbols->reel1[2] . ',' . $lastEvent->serverResponse->reelsSymbols->reel2[2] . ',' . $lastEvent->serverResponse->reelsSymbols->reel3[2] . ',' . $lastEvent->serverResponse->reelsSymbols->reel4[2] . ',' . $lastEvent->serverResponse->reelsSymbols->reel5[2] . ']');
                                    $bet = $lastEvent->serverResponse->slotBet * 100 * 40;
                                }
                                else
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeBalance', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeStartWin', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BonusSymbol', -1);
                                    $rp1 = implode(',', [
                                        rand(0, count($slotSettings->reelStrip1) - 3), 
                                        rand(0, count($slotSettings->reelStrip2) - 3), 
                                        rand(0, count($slotSettings->reelStrip3) - 3)
                                    ]);
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
                                    $bet = $slotSettings->Bet[0] * 100 * 40;
                                }
                                $restoreString = '';
                                $defaultBet = $slotSettings->GetGameData($slotSettings->slotId . 'BetLine');
                                if($defaultBet == 0)
                                    $defaultBet = 1;
                                $gameState = $slotSettings->GetGameData($slotSettings->slotId . 'GameState');
                                if($gameState === 'PickBonus')
                                {
                                    $fBonusWin = $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin');
                                    $restoreString = ',"restoredGameCode":"189","lastResponse":{"spinResult":{"type":"SpinResult","rows":[' . $rp2 . ']},"params":{"pickedItems":"6","freeSpinsSeries":"1"},"expandingSymbol":"-1","state":"PickBonus","lastPick":"false","totalBonusWin":"'.$fBonusWin.'"}';
                                }
                                else if($gameState === 'FreeSpins')
                                {
                                    if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') == $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') ) 
                                    {
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                                        $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                                        $slotSettings->SetGameData($slotSettings->slotId . 'GameState', 'Ready');
                                    }
                                    else if( $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') < $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') ) 
                                    {
                                        $fBonusWin = $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin');
                                        $fTotal = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                                        $fCurrent = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                                        $fRemain = $fTotal - $fCurrent;
                                        $restoreString = ',"restoredGameCode":"189","lastResponse":{"spinResult":{"type":"SpinResult","rows":[' . $rp2 . ']},"freeSpinsTotal":"' . $fTotal . '","freeSpinRemain":"' . $fRemain . '","totalBonusWin":"' . $fBonusWin . '","state":"FreeSpins","expandingSymbols":["1"]}';
                                    }
                                }                                
                                $result_tmp[0] = '{"action":"AuthResponse","result":"true","sesId":"10000010891","data":{"snivy":"proxy v6.10.48 (API v4.23)","supportedFeatures":["Offers","Jackpots","InstantJackpots","SweepStakes"],"sessionId":"10000010891","defaultLines":["0"],"bets":'.json_encode($slotSettings->GetBets()).',"betMultiplier":"25.0000000","defaultBet":"'.$defaultBet.'","defaultCoinValue":"0.01","coinValues":["0.01"],"gameParameters":{"availableLines":[["0","0","0","0","0"],["1","1","1","1","1"],["2","2","2","2","2"]],"rtp":"94.35","payouts":[{"payout":"50","symbols":["1","1","1"],"type":"basic"},{"payout":"100","symbols":["1","1","1","1"],"type":"basic"},{"payout":"800","symbols":["1","1","1","1","1"],"type":"basic"},{"payout":"35","symbols":["2","2","2"],"type":"basic"},{"payout":"100","symbols":["2","2","2","2"],"type":"basic"},{"payout":"800","symbols":["2","2","2","2","2"],"type":"basic"},{"payout":"30","symbols":["3","3","3"],"type":"basic"},{"payout":"100","symbols":["3","3","3","3"],"type":"basic"},{"payout":"800","symbols":["3","3","3","3","3"],"type":"basic"},{"payout":"20","symbols":["4","4","4"],"type":"basic"},{"payout":"50","symbols":["4","4","4","4"],"type":"basic"},{"payout":"300","symbols":["4","4","4","4","4"],"type":"basic"},{"payout":"15","symbols":["5","5","5"],"type":"basic"},{"payout":"35","symbols":["5","5","5","5"],"type":"basic"},{"payout":"300","symbols":["5","5","5","5","5"],"type":"basic"},{"payout":"10","symbols":["6","6","6"],"type":"basic"},{"payout":"30","symbols":["6","6","6","6"],"type":"basic"},{"payout":"200","symbols":["6","6","6","6","6"],"type":"basic"},{"payout":"10","symbols":["7","7","7"],"type":"basic"},{"payout":"20","symbols":["7","7","7","7"],"type":"basic"},{"payout":"200","symbols":["7","7","7","7","7"],"type":"basic"},{"payout":"10","symbols":["8","8","8"],"type":"basic"},{"payout":"15","symbols":["8","8","8","8"],"type":"basic"},{"payout":"100","symbols":["8","8","8","8","8"],"type":"basic"},{"payout":"10","symbols":["9","9","9"],"type":"basic"},{"payout":"15","symbols":["9","9","9","9"],"type":"basic"},{"payout":"100","symbols":["9","9","9","9","9"],"type":"basic"},{"payout":"5","symbols":["10","10","10"],"type":"basic"},{"payout":"15","symbols":["10","10","10","10"],"type":"basic"},{"payout":"100","symbols":["10","10","10","10","10"],"type":"basic"},{"payout":"5","symbols":["11","11","11"],"type":"basic"},{"payout":"10","symbols":["11","11","11","11"],"type":"basic"},{"payout":"100","symbols":["11","11","11","11","11"],"type":"basic"},{"payout":"5","symbols":["12","12","12"],"type":"basic"},{"payout":"10","symbols":["12","12","12","12"],"type":"basic"},{"payout":"50","symbols":["12","12","12","12","12"],"type":"basic"}],"initialSymbols":[["8","6","2","11","8"],["11","8","8","9","6"],["1","1","12","0","3"]]},"jackpotsEnabled":"true","gameModes":"[]" '.$restoreString.'}}';
                                break;
                            case 'BalanceRequest':
                                $result_tmp[] = '{"action":"BalanceResponse","result":"true","sesId":"10000214325","data":{"entries":"0.00","totalAmount":"' . $slotSettings->GetBalance() . '","currency":" "}}';
                                break;
                            case 'FreeSpinRequest':
                            case 'SpinRequest':
                                $postData['slotEvent'] = 'bet';
                                $bonusMpl = 1;
                                $linesId = $slotSettings->Ways243ToLine();
                                $lines = 25;
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
                                    $linesId = $slotSettings->Ways243ToLineBonus();
                                }
                                if( $postData['slotEvent'] != 'freespin' ) 
                                {
                                    $slotSettings->SetBalance(-1 * $allbet, $postData['slotEvent']);
                                    $bankSum = $allbet / 100 * $slotSettings->GetPercent();
                                    $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $bankSum, $postData['slotEvent']);
                                    $slotSettings->UpdateJackpots($allbet);
                                    $slotSettings->SetBet($allbet);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BonusSymbol', -1);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeBalance', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeStartWin', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGameMultipliers', null);
                                }
                                else
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);
                                    $multipliers = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGameMultipliers');
                                    $bonusMpl = $multipliers[rand(0, count($multipliers) - 1)];
                                }
                                
                                $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $lines);
                                $winType = $winTypeTmp[0];
                                $spinWinLimit = $winTypeTmp[1];
                                if($debug)
                                {
                                    if($postData['slotEvent'] != 'freespin')
                                        $winType = 'bonus';
                                }
                                $slotEvent = $postData['slotEvent'];
                                $wild = ['0'];
                                $scatter = '12';
                                $white_dragon = '13';
                                $red_dragon = '14';
                                $black_dragon = '15';
                                $blue_dragon = '16';
                                $fire_dragon = '17';
                                $diamond_dragon = '18';
                                $spinAcquired = false;

                                $minLineWins = [];
                                $minTotalWin = -1;
                                $minReels = [];
                                $minScattersCount = 0;

                                for( $i = 0; $i <= 300; $i++ ) 
                                {
                                    $totalWin = 0;
                                    $lineWins = [];
                                    $cWins = [];
                                    $reels = $slotSettings->GetReelStrips($winType, $slotEvent);
                                    
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
                                                // bonus adjust
                                                if($slotEvent == "freespin")
                                                {
                                                    if( $reels['reel' . $swc][3] == $csym || in_array($reels['reel' . $swc][3], $wild) ) 
                                                    {
                                                        $isNext = true;
                                                    }
                                                }
                                                if( $isNext ) 
                                                {
                                                    $wscc++;                                                    
                                                    if( $wscc == 3 ) 
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
                                                $tmpStringWin = '';
                                                $cWins[$k] = 0;
                                                $s = [];
                                                $s[0] = -1;
                                                $s[1] = -1;
                                                $s[2] = -1;
                                                $s[3] = -1;
                                                $s[4] = -1;
                                                $p0 = 0;
                                                $p1 = 0;
                                                $p2 = 0;
                                                $p3 = 0;
                                                $p4 = 0;
                                                
                                                if( $wscc == 3 ) 
                                                {
                                                    $s[0] = $reels['reel1'][$linesId[$cl][$k][0] - 1];
                                                    $s[1] = $reels['reel2'][$linesId[$cl][$k][1] - 1];
                                                    $s[2] = $reels['reel3'][$linesId[$cl][$k][2] - 1];
                                                    $s[3] = -1;
                                                    $s[4] = -1;
                                                    $p0 = $linesId[$cl][$k][0] - 1;
                                                    $p1 = $linesId[$cl][$k][1] - 1;
                                                    $p2 = $linesId[$cl][$k][2] - 1;
                                                    $p3 = 0;
                                                    $p4 = 0;
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
                                                }
                                                if( $wscc == 4 ) 
                                                {
                                                    $s[0] = $reels['reel1'][$linesId[$cl][$k][0] - 1];
                                                    $s[1] = $reels['reel2'][$linesId[$cl][$k][1] - 1];
                                                    $s[2] = $reels['reel3'][$linesId[$cl][$k][2] - 1];
                                                    $s[3] = $reels['reel4'][$linesId[$cl][$k][3] - 1];
                                                    $s[4] = -1;
                                                    $p0 = $linesId[$cl][$k][0] - 1;
                                                    $p1 = $linesId[$cl][$k][1] - 1;
                                                    $p2 = $linesId[$cl][$k][2] - 1;
                                                    $p3 = $linesId[$cl][$k][3] - 1;
                                                    $p4 = 0;
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
                                                }
                                                if( $wscc == 5 ) 
                                                {
                                                    $s[0] = $reels['reel1'][$linesId[$cl][$k][0] - 1];
                                                    $s[1] = $reels['reel2'][$linesId[$cl][$k][1] - 1];
                                                    $s[2] = $reels['reel3'][$linesId[$cl][$k][2] - 1];
                                                    $s[3] = $reels['reel4'][$linesId[$cl][$k][3] - 1];
                                                    $s[4] = $reels['reel5'][$linesId[$cl][$k][4] - 1];
                                                    $p0 = $linesId[$cl][$k][0] - 1;
                                                    $p1 = $linesId[$cl][$k][1] - 1;
                                                    $p2 = $linesId[$cl][$k][2] - 1;
                                                    $p3 = $linesId[$cl][$k][3] - 1;
                                                    $p4 = $linesId[$cl][$k][4] - 1;
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
                                                if( $cWins[$k] > 0 && $tmpStringWin != '' )
                                                {
                                                    array_push($lineWins, $tmpStringWin);
                                                    $totalWin += $cWins[$k];
                                                }
                                            }
                                        }
                                    }
                                    $scattersWin = 0;                                    
                                    $scattersPos = [];
                                    $scattersStr = '';
                                    $scattersCount = 0;
                                    $bSym = $slotSettings->GetGameData($slotSettings->slotId . 'BonusSymbol');
                                    $bSymCnt = 0;

                                    $lineCount = 3;
                                    if($slotEvent == "freespin")
                                        $lineCount = 4;
                                    for( $r = 1; $r <= 5; $r++ ) 
                                    {
                                        $isScat = false;
                                        for( $p = 0; $p < $lineCount; $p++ ) 
                                        {
                                            if($isScat)
                                                break;
                                            if( $reels['reel' . $r][$p] == $scatter ) 
                                            {
                                                $scattersCount++;
                                                $scattersPos[] = '["' . ($r - 1) . '","' . $p . '"]';
                                                $isScat = true;
                                            }
                                        }
                                    }

                                    if($scattersCount >= 3 && $winType != 'bonus')
                                        continue;
                                    $scattersWin = $slotSettings->Paytable['SYM_' . $scatter][$scattersCount] * $betLine * $bonusMpl;
                                    $gameState = 'Ready';
                                    if( $scattersCount >= 3 && $slotSettings->slotBonus ) 
                                    {
                                        $gameState = 'PickBonus';
                                        $scw = '{"type":"Bonus","bonusName":"FreeSpinSerie","params":{"winFreeSpinSeries":"1"},"amount":"' . $slotSettings->FormatFloat($scattersWin) . '","wonSymbols":[' . implode(',', $scattersPos) . ']}';
                                        array_push($lineWins, $scw);
                                    }
                                    $totalWin += $scattersWin;

                                    if($totalWin > 0)
                                    {
                                        if( $minTotalWin == -1 || $totalWin < $minTotalWin )
                                        {
                                            $minLineWins = $lineWins;
                                            $minTotalWin = $totalWin;
                                            $minReels = $reels;
                                            $minScattersCount = $scattersCount;
                                        }
                                    }
                                    
                                    if($debug)
                                    {
                                        $spinAcquired = true;
                                        break;
                                    }

                                    if($totalWin <= $spinWinLimit && (($totalWin > 0 && $winType != 'none') || ($winType == 'bonus' && $scattersCount >= 3)))
                                    {
                                        $spinAcquired = true;
                                        if($totalWin < 0.5 * $spinWinLimit && $winType != 'bonus')
                                            $spinAcquired = false;
                                        if($spinAcquired)
                                            break;                                        
                                    }  
                                    else if($winType == 'none' && $totalWin == 0)
                                    {
                                        break;
                                    }
                                }

                                if($totalWin > 0 && $spinAcquired == false)
                                {
                                    if($winType != 'none')
                                    {
                                        $lineWins = $minLineWins;
                                        $totalWin = $minTotalWin;
                                        $reels = $minReels;
                                        $scattersCount = $minScattersCount;
                                    }
                                    else
                                    {
                                        $lineWins = [];
                                        $totalWin = 0;
                                        $reels = $slotSettings->GetNoWinSpin($postData['slotEvent']);
                                        $scattersCount = 0;
                                        $gameState = 'Ready';
                                    }                                    
                                }

                                if( $totalWin > 0) 
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

                                    //replace wild with translated wild according to dragon selection
                                    $item = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGameType');
                                    $wild_translated = 0;
                                    switch($item)
                                    {
                                        case 0:
                                            $wild_translated = $white_dragon;
                                            break;
                                        case 1:
                                            $wild_translated = $red_dragon;
                                            break;
                                        case 2:
                                            $wild_translated = $black_dragon;
                                            break;
                                        case 3:
                                            $wild_translated = $blue_dragon;
                                            break;
                                        case 4:
                                            $wild_translated = $fire_dragon;
                                            break;
                                        case 5:
                                            $wild_translated = $diamond_dragon;
                                            break;
                                        default:
                                            break;
                                    }
                                    for( $r = 1; $r <= 5; $r++ ) 
                                    {
                                        for( $p = 0; $p < $lineCount; $p++ ) 
                                        {
                                            if( $reels['reel' . $r][$p] == '0' ) 
                                            {
                                                $reels['reel' . $r][$p] = $wild_translated;
                                            }
                                        }
                                    }
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
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $slotSettings->slotFreeCount);
                                    }
                                }
                                
                                $jsSpin = '' . json_encode($reels) . '';
                                $jsJack = '' . json_encode($slotSettings->Jackpots) . '';
                                if( $totalWin > 0 || $winType == 'bonus' ) 
                                {
                                    $winString0 = implode(',', $lineWins);
                                    $winString = ',"slotWin":{"lineWinAmounts":[' . $winString0 . '],"totalWin":"' . $slotSettings->FormatFloat($totalWin) . '","canGamble":"false"}';
                                }
                                else
                                {
                                    $winString = '';
                                }
                                $response = '{"responseEvent":"spin","responseType":"' . $postData['slotEvent'] . '","serverResponse":{"BonusSymbol":' . $slotSettings->GetGameData($slotSettings->slotId . 'BonusSymbol') . ',"slotLines":' . $lines . ',"slotBet":' . $betLine . ',"totalFreeGames":' . $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') . ',"currentFreeGames":' . $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') . ',"Balance":' . $slotSettings->GetBalance() . ',"afterBalance":' . $slotSettings->GetBalance() . ',"bonusWin":' . $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin') . ',"freeStartWin":' . $slotSettings->GetGameData($slotSettings->slotId . 'FreeStartWin') . ',"totalWin":' . $totalWin . ',"winLines":[],"bonusInfo":[],"Jackpots":' . $jsJack . ',"reelsSymbols":' . $jsSpin . '}}';
                                //bonus adjust
                                if($slotEvent == "freespin")
                                    $symb = '["' . $reels['reel1'][0] . '","' . $reels['reel2'][0] . '","' . $reels['reel3'][0] . '","' . $reels['reel4'][0] . '","' . $reels['reel5'][0] . '"],["' . $reels['reel1'][1] . '","' . $reels['reel2'][1] . '","' . $reels['reel3'][1] . '","' . $reels['reel4'][1] . '","' . $reels['reel5'][1] . '"],["' . $reels['reel1'][2] . '","' . $reels['reel2'][2] . '","' . $reels['reel3'][2] . '","' . $reels['reel4'][2] . '","' . $reels['reel5'][2] . '"]' . ',["' . $reels['reel1'][3] . '","' . $reels['reel2'][3] . '","' . $reels['reel3'][3] . '","' . $reels['reel4'][3] . '","' . $reels['reel5'][3] . '"]';
                                else
                                    $symb = '["' . $reels['reel1'][0] . '","' . $reels['reel2'][0] . '","' . $reels['reel3'][0] . '","' . $reels['reel4'][0] . '","' . $reels['reel5'][0] . '"],["' . $reels['reel1'][1] . '","' . $reels['reel2'][1] . '","' . $reels['reel3'][1] . '","' . $reels['reel4'][1] . '","' . $reels['reel5'][1] . '"],["' . $reels['reel1'][2] . '","' . $reels['reel2'][2] . '","' . $reels['reel3'][2] . '","' . $reels['reel4'][2] . '","' . $reels['reel5'][2] . '"]';
                                if($postData['slotEvent'] == 'freespin')
                                    $allbet = 0;
                                $slotSettings->SaveLogReport($response, $allbet, $reportWin, $postData['slotEvent']);
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
                                    $result_tmp[] = '{"action":"FreeSpinResponse","result":"true","sesId":"10000228087","data":{' . $gameParameters . '"state":"' . $gameState . '"' . $winString . ',"spinResult":{"type":"SpinResult","rows":[' . $symb . ']},"totalBonusWin":"' . $slotSettings->FormatFloat($bonusWin0) . '","freeSpinRemain":"' . $freeSpinRemain . '","freeSpinsTotal":"' . $freeSpinsTotal . '"}}';
                                }
                                else
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'initialSymbols', $symb);
                                    $result_tmp[] = '{"action":"SpinResponse","result":"true","sesId":"10000217909","data":{"state":"' . $gameState . '"' . $winString . ',"spinResult":{"type":"SpinResult","rows":[' . $symb . ']}}}';
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
