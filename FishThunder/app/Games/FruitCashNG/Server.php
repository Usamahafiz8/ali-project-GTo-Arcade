<?php 
namespace VanguardLTE\Games\FruitCashNG
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
                            case 'PickBonusItemRequest':
                                $selectedItem = (int)$postData['data']['index'];
                                if( $slotSettings->GetGameData($slotSettings->slotId . 'PickMode') == 1 ) 
                                {
                                    $ClLOmJbJVbjmyHqNurOwcCUCaAiO4 = rand(4, 9);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $ClLOmJbJVbjmyHqNurOwcCUCaAiO4);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                                    $allItems = [
                                        '{"type":"FreeSpins","index":"1","value":"' . rand(4, 9) . '","picked":"false"}', 
                                        '{"type":"FreeSpins","index":"2","value":"' . rand(4, 9) . '","picked":"false"}', 
                                        '{"type":"FreeSpins","index":"3","value":"' . rand(4, 9) . '","picked":"false"}', 
                                        '{"type":"FreeSpins","index":"4","value":"' . rand(4, 9) . '","picked":"false"}', 
                                        '{"type":"FreeSpins","index":"5","value":"' . rand(4, 9) . '","picked":"false"}', 
                                        '{"type":"FreeSpins","index":"6","value":"' . rand(4, 9) . '","picked":"false"}'
                                    ];
                                    $allItems[$selectedItem - 1] = '{"type":"FreeSpins","index":"' . $selectedItem . '","value":"' . $ClLOmJbJVbjmyHqNurOwcCUCaAiO4 . '","picked":"true"}';
                                    $slotSettings->SetGameData($slotSettings->slotId . 'PickMode', 2);
                                    $result_tmp[] = '{"action":"PickBonusItemResponse","result":"true","sesId":"10000085299","data":{"params":{"pickItems":"6"},"items":[' . implode(',', $allItems) . '],"bonusItem":' . $allItems[$selectedItem - 1] . ',"lastPick":"true","canGamble":"false","params":"","state":"PickMultyplier"}}';
                                }
                                else if( $slotSettings->GetGameData($slotSettings->slotId . 'PickMode') == 2 ) 
                                {
                                    $cmp = rand(3, 8);
                                    $allItems = [
                                        '{"type":"Multiplier","index":"1","value":"' . rand(3, 8) . '","picked":"false"}', 
                                        '{"type":"Multiplier","index":"2","value":"' . rand(3, 8) . '","picked":"false"}', 
                                        '{"type":"Multiplier","index":"3","value":"' . rand(3, 8) . '","picked":"false"}', 
                                        '{"type":"Multiplier","index":"4","value":"' . rand(3, 8) . '","picked":"false"}', 
                                        '{"type":"Multiplier","index":"5","value":"' . rand(3, 8) . '","picked":"false"}', 
                                        '{"type":"Multiplier","index":"6","value":"' . rand(3, 8) . '","picked":"false"}'
                                    ];
                                    $allItems[$selectedItem - 1] = '{"type":"Multiplier","index":"' . $selectedItem . '","value":"' . $cmp . '","picked":"true"}';
                                    $slotSettings->SetGameData($slotSettings->slotId . 'Mpl', $cmp);
                                    $result_tmp[] = '{"action":"PickBonusItemResponse","result":"true","sesId":"10000085299","data":{"params":{"freeSpins":"' . $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') . '","multiplier":"' . $cmp . '"},"items":[' . implode(',', $allItems) . '],"bonusItem":' . $allItems[$selectedItem - 1] . ',"lastPick":"true","canGamble":"false","params":"","state":"FreeSpins"}}';
                                    $slotSettings->SetGameData($slotSettings->slotId . 'PickMode', 3);
                                }
                                break;
                            case 'CheckBrokenGameRequest':
                                $result_tmp[] = '{"action":"CheckBrokenGameResponse","result":"true","sesId":"false","data":{"haveBrokenGame":"false"}}';
                                break;
                            case 'AuthRequest':
                                $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeBalance', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeStartWin', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'BonusSymbol', -1);
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeGamesHoldnLink', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'lastHoldnLinkCount', 0);
                                $lastEvent = $slotSettings->GetHistory();
                                if( $lastEvent != 'NULL' ) 
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', $lastEvent->serverResponse->bonusWin);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $lastEvent->serverResponse->totalFreeGames);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $lastEvent->serverResponse->currentFreeGames);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGamesHoldnLink', $lastEvent->serverResponse->totalFreeGamesHoldn);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink', $lastEvent->serverResponse->currentFreeGamesHoldn);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'lastHoldnLinkCount', $lastEvent->serverResponse->lastHoldnCount);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $lastEvent->serverResponse->bonusWin);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BonusSymbol', $lastEvent->serverResponse->BonusSymbol);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeBalance', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeStartWin', 0);
                                    $rp1 = implode(',', $lastEvent->serverResponse->reelsSymbols->rp);
                                    $rp2 = '[' . $lastEvent->serverResponse->reelsSymbols->reel1[0] . ',' . $lastEvent->serverResponse->reelsSymbols->reel2[0] . ',' . $lastEvent->serverResponse->reelsSymbols->reel3[0] . ',' . $lastEvent->serverResponse->reelsSymbols->reel4[0] . ',' . $lastEvent->serverResponse->reelsSymbols->reel5[0] . ']';
                                    $rp2 .= (',[' . $lastEvent->serverResponse->reelsSymbols->reel1[1] . ',' . $lastEvent->serverResponse->reelsSymbols->reel2[1] . ',' . $lastEvent->serverResponse->reelsSymbols->reel3[1] . ',' . $lastEvent->serverResponse->reelsSymbols->reel4[1] . ',' . $lastEvent->serverResponse->reelsSymbols->reel5[1] . ']');
                                    $rp2 .= (',[' . $lastEvent->serverResponse->reelsSymbols->reel1[2] . ',' . $lastEvent->serverResponse->reelsSymbols->reel2[2] . ',' . $lastEvent->serverResponse->reelsSymbols->reel3[2] . ',' . $lastEvent->serverResponse->reelsSymbols->reel4[2] . ',' . $lastEvent->serverResponse->reelsSymbols->reel5[2] . ']');
                                    $bet = $lastEvent->serverResponse->slotBet * 100 * 20;
                                }
                                else
                                {
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
                                    $bet = $slotSettings->Bet[0] * 100 * 20;
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
                                if( $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') < $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') ) 
                                {
                                    $fBonusWin = $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin');
                                    $fTotal = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                                    $fCurrent = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                                    $fRemain = $fTotal - $fCurrent;
                                    $restoreString = ',"restoredGameCode":"297","lastResponse":{"spinResult":{"type":"SpinResult","rows":[' . $rp2 . ']},"freeSpinsTotal":"' . $fTotal . '","freeSpinRemain":"' . $fRemain . '","totalBonusWin":"' . $fBonusWin . '","state":"FreeSpins","expandingSymbols":["1"]}';
                                }
                                else if( $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink') < $slotSettings->GetGameData($slotSettings->slotId . 'FreeGamesHoldnLink') ) 
                                {
                                    $fBonusWin = $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin');
                                    $fTotal = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGamesHoldnLink');
                                    $fCurrent = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink');
                                    $fRemain = $fTotal - $fCurrent;
                                    $restoreString = ',"restoredGameCode":"297","lastResponse":{"spinResult":{"type":"SpinResult","rows":[' . $rp2 . ']},"freeSpinsTotal":"' . $fTotal . '","freeSpinRemain":"' . $fRemain . '","totalBonusWin":"0","state":"HoldnLink","expandingSymbols":["1"]}';
                                }
                                $result_tmp[0] = '{"action":"AuthResponse","result":"true","sesId":"10000378151","data":{"snivy":"proxy v11.16.82 (API v4.16)","bets":["4","5","10","20","30","40","50","100","150"],"coinValues":["0.01"],"betMultiplier":"1.0000000","defaultCoinValue":"0.01","defaultBet":"'.$defaultBet.'","jackpotsEnabled":"false","defaultLines":["0","1","2","3","4"],"supportedFeatures":["Offers","Jackpots","InstantJackpots","SweepStakes","PaidJackpots"],"sessionId":"10000378151","gameParameters":{"availableLines":[["0","0","0","0","0"],["1","1","1","1","1"],["2","2","2","2","2"],["0","1","2","1","0"],["2","1","0","1","2"]],"rtp":"0.00","initialSymbols":[["0","7","10","7","3"],["9","9","5","200","9"],["5","2","9","102","5"]],"payouts":[{"payout":"2","symbols":["0","0","0"],"type":"scatter"},{"payout":"10","symbols":["0","0","0","0"],"type":"scatter"},{"payout":"100","symbols":["0","0","0","0","0"],"type":"scatter"},{"payout":"1500","symbols":["1","1","1","1","1","1","1","1","1","1","1","1","1","1","1","1"],"type":"scatter"},{"payout":"1","symbols":["1","1","1","1","1","1","1","1","1","1","1","1","1","1","1","1","1"],"type":"scatter"},{"payout":"3","symbols":["3","3"],"type":"basic"},{"payout":"10","symbols":["3","3","3"],"type":"basic"},{"payout":"50","symbols":["3","3","3","3"],"type":"basic"},{"payout":"250","symbols":["3","3","3","3","3"],"type":"basic"},{"payout":"2","symbols":["4","4"],"type":"basic"},{"payout":"5","symbols":["4","4","4"],"type":"basic"},{"payout":"30","symbols":["4","4","4","4"],"type":"basic"},{"payout":"150","symbols":["4","4","4","4","4"],"type":"basic"},{"payout":"2","symbols":["5","5"],"type":"basic"},{"payout":"5","symbols":["5","5","5"],"type":"basic"},{"payout":"25","symbols":["5","5","5","5"],"type":"basic"},{"payout":"100","symbols":["5","5","5","5","5"],"type":"basic"},{"payout":"1","symbols":["6","6"],"type":"basic"},{"payout":"5","symbols":["6","6","6"],"type":"basic"},{"payout":"25","symbols":["6","6","6","6"],"type":"basic"},{"payout":"100","symbols":["6","6","6","6","6"],"type":"basic"},{"payout":"2","symbols":["7","7","7"],"type":"basic"},{"payout":"20","symbols":["7","7","7","7"],"type":"basic"},{"payout":"75","symbols":["7","7","7","7","7"],"type":"basic"},{"payout":"2","symbols":["8","8","8"],"type":"basic"},{"payout":"10","symbols":["8","8","8","8"],"type":"basic"},{"payout":"50","symbols":["8","8","8","8","8"],"type":"basic"},{"payout":"2","symbols":["9","9","9"],"type":"basic"},{"payout":"10","symbols":["9","9","9","9"],"type":"basic"},{"payout":"50","symbols":["9","9","9","9","9"],"type":"basic"},{"payout":"2","symbols":["10","10","10"],"type":"basic"},{"payout":"10","symbols":["10","10","10","10"],"type":"basic"},{"payout":"50","symbols":["10","10","10","10","10"],"type":"basic"},{"payout":"2","symbols":["11","11","11"],"type":"basic"},{"payout":"10","symbols":["11","11","11","11"],"type":"basic"},{"payout":"50","symbols":["11","11","11","11","11"],"type":"basic"},{"payout":"30","symbols":["12"],"type":"basic"},{"payout":"150","symbols":["13"],"type":"basic"},{"payout":"300","symbols":["14"],"type":"basic"},{"payout":"1","symbols":["15"],"type":"basic"},{"payout":"2","symbols":["16"],"type":"basic"},{"payout":"3","symbols":["17"],"type":"basic"},{"payout":"4","symbols":["18"],"type":"basic"},{"payout":"5","symbols":["19"],"type":"basic"},{"payout":"10","symbols":["20"],"type":"basic"},{"payout":"15","symbols":["21"],"type":"basic"},{"payout":"20","symbols":["22"],"type":"basic"},{"payout":"50","symbols":["23"],"type":"basic"},{"payout":"100","symbols":["24"],"type":"basic"}],"params":""},"gameModes":[] '.$restoreString.'}}';                                
                                break;
                            case 'BalanceRequest':
                                $result_tmp[0] = '{"action":"BalanceResponse","result":"true","sesId":"10000373695","data":{"totalAmount":"' . $slotSettings->GetBalance() . '","currency":" "}}';
                                break;
                            case 'HoldnLinkRequest':
                            case 'FreeSpinRequest':
                            case 'SpinRequest':
                                $postData['slotEvent'] = 'bet';
                                $bonusMpl = 1;
                                $linesId = [];
                                $linesId[0] = [
                                    1, 
                                    1, 
                                    1, 
                                    1, 
                                    1
                                ];
                                $linesId[1] = [
                                    2, 
                                    2, 
                                    2, 
                                    2, 
                                    2
                                ];                                
                                $linesId[2] = [
                                    3, 
                                    3, 
                                    3, 
                                    3, 
                                    3
                                ];
                                $linesId[3] = [
                                    1, 
                                    2, 
                                    3, 
                                    2, 
                                    1
                                ];
                                $linesId[4] = [
                                    3, 
                                    2, 
                                    1, 
                                    2, 
                                    3
                                ];
                                
                                $lines = 5;
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
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BonusSymbol', -1);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeBalance', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeStartWin', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'lastHoldnLinkCount', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGamesHoldnLink', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink', 0);

                                }
                                else if($postData['slotEvent'] == 'freespin')
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);
                                    $bonusMpl = $slotSettings->GetGameData($slotSettings->slotId . 'Mpl');
                                }
                                else if($postData['slotEvent'] == $HOLDNLINK_TAG)
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink') + 1);
                                    $bonusMpl = 1;
                                }
                                
                                $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $lines);                                
                                $winType = $winTypeTmp[0];
                                $spinWinLimit = $winTypeTmp[1];
                                if($debug && $postData['slotEvent'] == 'bet')
                                    $winType = "bonus";
                                                                    
                                $minReels = [];
                                $minTotalWin = -1;
                                $minLineWins = [];
                                $minHoldLinkWin = 0;
                                $minScatterCount = 0;
                                $minHoldLinkCount = 0;
                                $spinAcquired = false;

                                for( $i = 0; $i <= 500; $i++ ) 
                                {
                                    $totalWin = 0;
                                    $lineWins = [];
                                    $cWins = array_fill(0, $lines, 0);
                                    $wild = ['2'];
                                    $scatter = '0';
                                    $reels = $slotSettings->GetReelStrips($winType, $postData['slotEvent']);
                                    //win test
                                
                                    if( $postData['slotEvent'] != $HOLDNLINK_TAG ) 
                                    {
                                        for( $k = 0; $k < $lines; $k++ ) 
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
                                                    $s[0] = $reels['reel1'][$linesId[$k][0] - 1];
                                                    $s[1] = $reels['reel2'][$linesId[$k][1] - 1];
                                                    $s[2] = $reels['reel3'][$linesId[$k][2] - 1];
                                                    $s[3] = $reels['reel4'][$linesId[$k][3] - 1];
                                                    $s[4] = $reels['reel5'][$linesId[$k][4] - 1];
                                                    $p0 = $linesId[$k][0] - 1;
                                                    $p1 = $linesId[$k][1] - 1;
                                                    $p2 = $linesId[$k][2] - 1;
                                                    $p3 = $linesId[$k][3] - 1;
                                                    $p4 = $linesId[$k][4] - 1;
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
                                    }

                                    $scattersWin = 0;                                    
                                    $scattersPos = [];                                    
                                    $scattersCount = 0;

                                    //check bonus symbol
                                    $bSym = $slotSettings->GetGameData($slotSettings->slotId . 'BonusSymbol');
                                    $bSymCnt = 0;
                                    for( $r = 1; $r <= 5; $r++ ) 
                                    {
                                        $isScat = false;
                                        for( $p = 0; $p <= 2; $p++ ) 
                                        {
                                            if( $reels['reel' . $r][$p] == $scatter ) 
                                            {
                                                $scattersCount++;
                                                $scattersPos[] = '["' . ($r - 1) . '","' . $p . '"]';
                                                $isScat = true;
                                            }
                                        }
                                    }
                                    for( $r = 1; $r <= 5; $r++ ) 
                                    {
                                        $isScat = false;
                                        for( $p = 0; $p <= 2; $p++ ) 
                                        {
                                            if( $reels['reel' . $r][$p] == $bSym ) 
                                            {
                                                $bSymCnt++;
                                                break;
                                            }
                                        }
                                    }
                                    if( $scattersCount >= 3 && $winType != 'bonus')
                                        continue;
                                    if( $postData['slotEvent'] == $HOLDNLINK_TAG ) 
                                        $scattersCount = 0;

                                    $scattersCount = $scattersCount > 5 ? 5 : $scattersCount;
                                    $scattersWin = $slotSettings->Paytable['SYM_' . $scatter][$scattersCount] * $betLine * $bonusMpl;
                                    $gameState = 'Ready';
                                    if( $scattersCount >= 3 && $slotSettings->slotBonus ) 
                                    {
                                        $gameState = 'FreeSpins';
                                        $freespinCnt = 6;
                                        if($postData['slotEvent'] == 'freespin')
                                            $freespinCnt = 3;
                                        $scw = '{"type":"Bonus","bonusName":"FreeSpins","params":{"freeSpins": "'.$freespinCnt.'"},"amount":"' . $slotSettings->FormatFloat($scattersWin) . '","wonSymbols":[' . implode(',', $scattersPos) . ']}';
                                        array_push($lineWins, $scw);
                                    }
                                    $totalWin += $scattersWin;                                    

                                    //check holdnlink 
                                    $holdnlinkWin = 0;
                                    $holdnlinkCount = 0;
                                    $holdnlinkPos = [];

                                    //if current spin is holdnlink, keep last holdn positions
                                    if( $postData['slotEvent'] == $HOLDNLINK_TAG ) 
                                    {
                                        $lastReel = $slotSettings->GetGameData($slotSettings->slotId . 'LastReel');
                                        for( $r = 1; $r <= 5; $r++ ) 
                                        {
                                            for( $p = 0; $p <= 2; $p++ ) 
                                            {
                                                if( intval($lastReel['reel' . $r][$p]) > 100 ) 
                                                {
                                                    $reels['reel' . $r][$p] = $lastReel['reel' . $r][$p];                                  
                                                }
                                            }
                                        }
                                    }

                                    for( $r = 1; $r <= 5; $r++ ) 
                                    {
                                        for( $p = 0; $p <= 2; $p++ ) 
                                        {
                                            if( intval($reels['reel' . $r][$p]) > 100 ) 
                                            {
                                                $holdnlinkCount++;
                                                $holdnlinkWin += (intval($reels['reel' . $r][$p]) - 100) * $allbet;            
                                                $holdnlinkPos[] = '["' . ($r - 1) . '","' . $p . '"]';                                    
                                            }
                                        }
                                    }                                    

                                    $lastHoldnLinkCount = $slotSettings->GetGameData($slotSettings->slotId . 'lastHoldnLinkCount');                                    

                                    if($holdnlinkCount > 5 )
                                    {
                                        if($scattersCount >= 3)
                                            continue;
                                        //holdnlinkecount must be greater than 5 to fire
                                        if($lastHoldnLinkCount == 0)
                                        {                                            
                                            //first holdnlink event fire
                                            $gameState = 'HoldnLink';
                                            $holdnLine = '{"type":"Bonus","bonusName":"HoldnLink","params":{"freeSpins": "'.$slotSettings->slotHoldnLinkCount.'"},"amount":"0.00","wonSymbols":[' . implode(',', $holdnlinkPos) . ']}';
                                            array_push($lineWins, $holdnLine);
                                        }                                        
                                    }     
                                    else
                                    {
                                        $holdnlinkWin = 0;
                                    }
                                    
                                    $totalWin += $holdnlinkWin;

                                    if($minTotalWin == -1 || ($totalWin > 0 && $totalWin < $minTotalWin))
                                    {
                                        $minTotalWin = $totalWin;
                                        $minReels = $reels;
                                        $minLineWins = $lineWins;
                                        $minHoldLinkWin = $holdnlinkWin;
                                        $minScatterCount = $scattersCount;
                                        $minHoldLinkCount = $holdnlinkCount;
                                    }

                                    if($debug)
                                    {
                                        $spinAcquired = true;
                                        break;
                                    }
                                    if($totalWin <= $spinWinLimit && (($totalWin > 0 && $winType != 'none') || ($winType == 'bonus' && $scattersCount >= 3) || ($winType == 'bonus' && $lastHoldnLinkCount == 0 && $holdnlinkCount > 5)))
                                    {
                                        $spinAcquired = true;
                                        if($totalWin < 0.5 * $spinWinLimit && $winType != 'bonus')
                                            $spinAcquired = false;
                                        if($spinAcquired)
                                            break;                                        
                                    }
                                    else if($winType == 'none' && $totalWin == 0 && $holdnlinkCount < 4)
                                    {
                                        break;
                                    }
                                }

                                if($totalWin > 0 && !$spinAcquired)
                                {
                                    $lineWins = $minLineWins;
                                    $reels = $minReels;
                                    $totalWin = $minTotalWin;
                                    $holdnlinkWin = $minHoldLinkWin;
                                    $scattersCount = $minScatterCount;
                                    $holdnlinkCount = $minHoldLinkCount;
                                }
                                
                                if($holdnlinkCount > 5 && $lastHoldnLinkCount == 0)
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGamesHoldnLink', $slotSettings->slotHoldnLinkCount);                                    
                                    $totalWin -= $holdnlinkWin;
                                }

                                $slotSettings->SetGameData($slotSettings->slotId . 'LastReel', $reels);
                                if($postData['slotEvent'] == $HOLDNLINK_TAG)
                                {
                                    $totalWin -= $holdnlinkWin;
                                    //check holdnlink spin is ended
                                    if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGamesHoldnLink') <= $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink') && $slotSettings->GetGameData($slotSettings->slotId . 'FreeGamesHoldnLink') > 0 ) 
                                    {
                                        $holdLinearPos = [];
                                        $linearIndex = 0;

                                        for( $p = 0; $p <= 2; $p++ )                                                                         
                                        {
                                            for( $r = 1; $r <= 5; $r++ )     
                                            {
                                                if( intval($reels['reel' . $r][$p]) > 100 ) 
                                                {
                                                    $holdLinearPos[] = '["' . $linearIndex . '","0"]';                                                                                    
                                                }
                                                $linearIndex++;
                                            }
                                        }

                                        $holdnLine = '{"type":"WinAmount","amount":"'.$holdnlinkWin.'","wonSymbols":[' . implode(',', $holdLinearPos) . ']}';
                                        array_push($lineWins, $holdnLine);
                                        $lastFreespinRemain = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                                        if($lastFreespinRemain > 0) //check if last spin was bonus free spin
                                        {                                            
                                            $bonusLine = '{"type":"Bonus","bonusName":"FreeSpins","params":{"freeSpins": "'.$lastFreespinRemain.'"},"amount":"0.00","wonSymbols":""}';
                                            array_push($lineWins, $bonusLine);
                                        }
                                        $totalWin += $holdnlinkWin;
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
                                }
                                else
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $totalWin);
                                }

                                if( $scattersCount >= 3 ) 
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'PickMode', 1);
                                    if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') > 0 ) 
                                    {
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') + 3);
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
                                if( $totalWin > 0 || ($holdnlinkCount > 5 && $lastHoldnLinkCount == 0)) 
                                {
                                    $winString = ',"slotWin":{"totalWin":"' . $totalWin . '","lineWinAmounts":[' . implode(',', $lineWins) . '],"canGamble":"false"}';
                                }
                                else
                                {
                                    $winString = ',"slotWin":{"totalWin":"0.00","canGamble":"false"}';
                                }

                                $response = '{"responseEvent":"spin","responseType":"' . $postData['slotEvent'] . '","serverResponse":{"BonusSymbol":' . $slotSettings->GetGameData($slotSettings->slotId . 'BonusSymbol') . ',"slotLines":' . $lines . ',"slotBet":' . $betLine . ',"totalFreeGames":' . $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') . ',"currentFreeGames":' . $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') . ',"totalFreeGamesHoldn":'.$slotSettings->GetGameData($slotSettings->slotId . 'FreeGamesHoldnLink').',"currentFreeGamesHoldn" : '.$slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink').',"lastHoldnCount": '.$slotSettings->GetGameData($slotSettings->slotId . 'lastHoldnLinkCount') .',"Balance":' . $slotSettings->GetBalance() . ',"afterBalance":' . $slotSettings->GetBalance() . ',"bonusWin":' . $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin') . ',"freeStartWin":' . $slotSettings->GetGameData($slotSettings->slotId . 'FreeStartWin') . ',"totalWin":' . $totalWin . ',"winLines":[],"bonusInfo":[],"Jackpots":' . $jsJack . ',"reelsSymbols":' . $jsSpin . '}}';
                                if($postData['slotEvent'] == 'freespin')
                                    $allbet = 0;
                                $slotSettings->SaveLogReport($response, $allbet, $reportWin, $postData['slotEvent']);

                                $symb = "";
                                if( $postData['slotEvent'] != $HOLDNLINK_TAG ) 
                                {
                                    $symb = '["' . $reels['reel1'][0] . '","' . $reels['reel2'][0] . '","' . $reels['reel3'][0] . '","' . $reels['reel4'][0] . '","' . $reels['reel5'][0] . '"],["' . $reels['reel1'][1] . '","' . $reels['reel2'][1] . '","' . $reels['reel3'][1] . '","' . $reels['reel4'][1] . '","' . $reels['reel5'][1] . '"],["' . $reels['reel1'][2] . '","' . $reels['reel2'][2] . '","' . $reels['reel3'][2] . '","' . $reels['reel4'][2] . '","' . $reels['reel5'][2] . '"]';
                                }
                                else
                                {
                                    $symb = '["' . $reels['reel1'][0] . '","' . $reels['reel2'][0] . '","' . $reels['reel3'][0] . '","' . $reels['reel4'][0] . '","' . $reels['reel5'][0] . '","' . $reels['reel1'][1] . '","' . $reels['reel2'][1] . '","' . $reels['reel3'][1] . '","' . $reels['reel4'][1] . '","' . $reels['reel5'][1] . '","' . $reels['reel1'][2] . '","' . $reels['reel2'][2] . '","' . $reels['reel3'][2] . '","' . $reels['reel4'][2] . '","' . $reels['reel5'][2] . '"]';
                                }

                                if( $postData['slotEvent'] == 'freespin' ) 
                                {
                                    $bonusWin0 = $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin');
                                    $freeSpinRemain = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                                    $freeSpinsTotal = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                                    if($gameState != 'HoldnLink')
                                        $gameState = 'FreeSpins';
                                    else
                                        $slotSettings->SetGameData($slotSettings->slotId . 'lastHoldnLinkCount', $holdnlinkCount);
                                    $gameParameters = '';
                                    if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') <= $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') && $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') > 0 ) 
                                    {
                                        $gameState = 'Ready';
                                        $gameParameters = '"gameParameters":{"initialSymbols":[' . $slotSettings->GetGameData($slotSettings->slotId . 'initialSymbols') . ']},';
                                    }

                                    $result_tmp[] = '{"action":"FreeSpinResponse","result":"true","sesId":"10000228087","data":{' . $gameParameters . '"state":"' . $gameState . '"' . $winString . ',"spinResult":{"type":"SpinResult","rows":[' . $symb . ']},"totalBonusWin":"' . $slotSettings->FormatFloat($bonusWin0) . '","freeSpinRemain":"' . $freeSpinRemain . '","freeSpinsTotal":"' . $freeSpinsTotal . '"}}';
                                }
                                else if( $postData['slotEvent'] == $HOLDNLINK_TAG ) 
                                {
                                    $freeSpinRemain = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGamesHoldnLink') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink');
                                    $freeSpinsTotal = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGamesHoldnLink');
                                    $gameState = 'HoldnLink';
                                    $gameParameters = '';
                                    if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGamesHoldnLink') <= $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink') && $slotSettings->GetGameData($slotSettings->slotId . 'FreeGamesHoldnLink') > 0 ) 
                                    {
                                        if($slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') > 0)
                                        {
                                            $freeSpinRemain = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                                            $freeSpinsTotal = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                                            $gameState = 'FreeSpins';
                                            $gameParameters = '"gameParameters":{"initialSymbols":[' . $slotSettings->GetGameData($slotSettings->slotId . 'initialSymbols') . ']},';    
                                        }
                                        else
                                        {
                                            $gameState = 'Ready';
                                            $gameParameters = '"gameParameters":{"initialSymbols":[' . $slotSettings->GetGameData($slotSettings->slotId . 'initialSymbolsHoldnLink') . ']},';    
                                        }                                        
                                    }
                                    $result_tmp[] = '{"action":"HoldnLinkResponse","result":"true","sesId":"10000228087","data":{' . $gameParameters . '"state":"' . $gameState . '"' . $winString . ',"spinResult":{"type":"SpinResult","rows":[' . $symb . ']},"totalBonusWin":"'.$holdnlinkWin.'","freeSpinRemain":"' . $freeSpinRemain . '","freeSpinsTotal":"' . $freeSpinsTotal . '"}}';
                                }
                                else
                                {
                                    //save initial symbols
                                    if($holdnlinkCount > 5 && $lastHoldnLinkCount == 0)
                                    {
                                        $slotSettings->SetGameData($slotSettings->slotId . 'initialSymbolsHoldnLink', $symb);
                                        $slotSettings->SetGameData($slotSettings->slotId . 'lastHoldnLinkCount', $holdnlinkCount);
                                    }
                                    else
                                    {
                                        $slotSettings->SetGameData($slotSettings->slotId . 'initialSymbols', $symb);                                        
                                    }   
                                    $result_tmp[] = '{"action":"SpinResponse","result":"true","sesId":"10000373695","data":{"spinResult":{"type":"SpinResult","rows":[' . $symb . ']}' . $winString . ',"state":"' . $gameState . '"}}';                                 
                                }
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
