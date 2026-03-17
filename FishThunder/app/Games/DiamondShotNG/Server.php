<?php 
namespace VanguardLTE\Games\DiamondShotNG
{

    use phpDocumentor\Reflection\PseudoTypes\False_;
    use PHPUnit\Framework\Constraint\IsFalse;

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
                                $symbols = [13, 14, 15, 16 ,17, 18];
                                $pickedItems = $slotSettings->GetGameData($slotSettings->slotId . 'PickedItems');
                                if($pickedItems == 0)
                                    $pickedItems = array_fill(0, 20, 0);
                                $rd = rand(0, 65);
                                $index = 0;
                                if($rd < 25)
                                    $index = 0;
                                else if($rd < 35)
                                    $index = 1;
                                else if($rd < 45)
                                    $index = 2;
                                else if($rd < 55)
                                    $index = 3;
                                else if($rd < 62)
                                    $index = 4;
                                else 
                                    $index = 5;
                                
                                $picked = $symbols[$index];
                                $pickedItems[$selectedItem] = $picked;
                                $slotSettings->SetGameData($slotSettings->slotId . 'PickedItems', $pickedItems);

                                $isSelected = false;
                                $selectedSymbol = 0;

                                foreach($symbols as $symbol)                                
                                {
                                    $cnt = 0;
                                    for($i = 0; $i < 20; $i++)    
                                    {                                        
                                        if($pickedItems[$i] == $symbol)
                                        {
                                            $cnt++;
                                            if($cnt == 3)
                                            {
                                                $isSelected = true;
                                                $selectedSymbol = $symbol;
                                                break;
                                            }
                                        }
                                    }
                                    if($isSelected)
                                        break;
                                }

                                $lastPick = 'false';
                                $state = 'PickBonus';
                                $params = '';
                                
                                if($isSelected)
                                {
                                    $lastPick = 'true';
                                    $freespinCnt = 7;
                                    $multiplier = 2;
                                    switch($selectedSymbol)
                                    {
                                        case 13:
                                            $freespinCnt = 5;
                                            $multiplier = 2;
                                            break;
                                        case 14:
                                            $freespinCnt = 7;
                                            $multiplier = 2;
                                            break;
                                        case 15:
                                            $freespinCnt = 10;
                                            $multiplier = 2;
                                            break;
                                        case 16:
                                            $freespinCnt = 11;
                                            $multiplier = 2;
                                            break;
                                        case 17:
                                            $freespinCnt = 15;
                                            $multiplier = 2;
                                            break;
                                        case 18:
                                            $freespinCnt = 20;
                                            $multiplier = 3;
                                            break;
                                    }
                                    $params = ['freeSpinRemain' => $freespinCnt, 'freeSpins' => $freespinCnt, 'freeSpinsTotal' => $freespinCnt, 'multiplier' => $multiplier];
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $freespinCnt);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BonusMpl', $multiplier);
                                }
                                $data = ['action' => 'PickBonusItemResponse', 'result' => 'true', 'sesId' => '10000079423',
                                        'data' => [
                                            'lastPick' => $lastPick,
                                            'params' => $params,
                                            'state' => $state,
                                            'bonusItem' => ['index' => $selectedItem, 'picked' => $lastPick, 'type' => 'IndexedItem', 'value' => $picked]
                                        ]
                                    ];
                                if($isSelected)
                                {
                                    //fill rest fields
                                    $items = [];
                                    for($i = 0; $i < 20; $i++)
                                    {
                                        $picked = 'false';
                                        if($pickedItems[$i] == 0)
                                        {
                                            $pickedItems[$i] = $symbols[rand(0, count($symbols) - 1)];                                            
                                        }
                                        if($pickedItems[$i] == $selectedSymbol)
                                            $picked = 'true';
                                        $items[] = ['index' => $i, 'picked' => $picked, 'type' => 'IndexedItem', 'value' => $pickedItems[$i]];
                                    }
                                    $data['data']['items'] = $items;
                                    $slotSettings->SetGameData($slotSettings->slotId . 'GameState', 'FreeSpins');
                                }
                                $result_tmp[] = json_encode($data);
                                break;
                            case 'CheckBrokenGameRequest':
                                $result_tmp[] = '{"action":"CheckBrokenGameResponse","result":"true","sesId":"false","data":{"haveBrokenGame":"false"}}';
                                break;
                            case 'AuthRequest':                                
                                $lastEvent = $slotSettings->GetHistory();
                                $restoreString = '';
                                if( $lastEvent != 'NULL' ) 
                                {
                                    // $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', $lastEvent->serverResponse->bonusWin);
                                    // $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $lastEvent->serverResponse->totalFreeGames);
                                    // $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $lastEvent->serverResponse->currentFreeGames);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $lastEvent->serverResponse->bonusWin);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BonusSymbol', $lastEvent->serverResponse->BonusSymbol);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeBalance', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeStartWin', 0);
                                    $rp1 = implode(',', $lastEvent->serverResponse->reelsSymbols->rp);
                                    $rp2 = '[' . $lastEvent->serverResponse->reelsSymbols->reel1[0] . ',' . $lastEvent->serverResponse->reelsSymbols->reel2[0] . ',' . $lastEvent->serverResponse->reelsSymbols->reel3[0] . ',' . $lastEvent->serverResponse->reelsSymbols->reel4[0] . ',' . $lastEvent->serverResponse->reelsSymbols->reel5[0] . ']';
                                    $rp2 .= (',[' . $lastEvent->serverResponse->reelsSymbols->reel1[1] . ',' . $lastEvent->serverResponse->reelsSymbols->reel2[1] . ',' . $lastEvent->serverResponse->reelsSymbols->reel3[1] . ',' . $lastEvent->serverResponse->reelsSymbols->reel4[1] . ',' . $lastEvent->serverResponse->reelsSymbols->reel5[1] . ']');
                                    $rp2 .= (',[' . $lastEvent->serverResponse->reelsSymbols->reel1[2] . ',' . $lastEvent->serverResponse->reelsSymbols->reel2[2] . ',' . $lastEvent->serverResponse->reelsSymbols->reel3[2] . ',' . $lastEvent->serverResponse->reelsSymbols->reel4[2] . ',' . $lastEvent->serverResponse->reelsSymbols->reel5[2] . ']');
                                    $bet = $lastEvent->serverResponse->slotBet * 100 * 20;

                                    $gameState = $slotSettings->GetGameData($slotSettings->slotId . 'GameState');
                                    
                                    $defaultBet = $slotSettings->GetGameData($slotSettings->slotId . 'BetLine');
                                    if($defaultBet == 0)
                                        $defaultBet = 1;
                                        
                                    if($gameState === 'PickBonus')
                                    {
                                        $fBonusWin = $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin');
                                        $pickedItems = $slotSettings->GetGameData($slotSettings->slotId . 'PickedItems');
                                        if($pickedItems == 0)
                                            $pickedItems = [];
                                        $items = [];
                                        for($i = 0; $i < count($pickedItems); $i++)
                                        {
                                            if($pickedItems[$i] != 0)
                                                $items[] = ['item' => 'IndexedItem', 'index' => $i, 'value' => $pickedItems[$i], 'picked' => 'false'];
                                        }
                                        $itemString = json_encode($items);
                                        if(count($items) == 0)
                                            $itemString = '""';
                                        $restoreString = ',"restoredGameCode":"216","lastResponse":{"spinResult":{"type":"SpinResult","rows":[' . $rp2 . ']},"totalWin":"' . $fBonusWin . '","state":"PickBonus", "items":'.$itemString.'}';
                                    }
                                    else if($gameState === 'FreeSpins')
                                    {
                                        if( $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') < $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') ) 
                                        {
                                            $fBonusWin = $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin');
                                            $fTotal = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                                            $fCurrent = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                                            $fRemain = $fTotal - $fCurrent;                                        
                                            $restoreString = ',"restoredGameCode":"216","lastResponse":{"spinResult":{"type":"SpinResult","rows":[' . $rp2 . ']},"freeSpinsTotal":"' . $fTotal . '","freeSpinRemain":"' . $fRemain . '","totalBonusWin":"' . $fBonusWin . '","state":"FreeSpins"}';
                                        }
                                    }
                                    if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') == $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') ) 
                                    {
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                                        $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                                    }  
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
                                    $bet = $slotSettings->Bet[0] * 100 * 20;
                                }
                                                              
                                
                                $result_tmp[0] = '{"action":"AuthResponse","result":"true","sesId":"10000063937","data":{"snivy":"proxy v6.12.51 (API v4.23)","supportedFeatures":["Offers","Jackpots","InstantJackpots","SweepStakes"],"sessionId":"10000063937","defaultLines":["0","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","21","22","23","24","25","26","27","28","29"],"bets":["1","2","3","4","5","10","15"],"betMultiplier":"1.0000000","defaultBet":"1","defaultCoinValue":"0.01","coinValues":["0.01"],"gameParameters":{"availableLines":[["1","1","1","1","1"],["0","0","0","0","0"],["2","2","2","2","2"],["0","1","2","1","0"],["2","1","0","1","2"],["1","0","0","0","1"],["1","2","2","2","1"],["0","0","1","2","2"],["2","2","1","0","0"],["1","0","1","0","1"],["1","2","1","2","1"],["0","1","1","1","2"],["2","1","1","1","0"],["1","1","0","1","2"],["1","1","2","1","0"],["0","1","0","1","0"],["2","1","2","1","2"],["0","0","2","0","0"],["2","2","0","2","2"],["1","0","2","0","1"],["1","2","0","2","1"],["0","2","0","2","0"],["2","0","2","0","2"],["0","2","2","2","0"],["2","0","0","0","2"],["0","2","1","2","0"],["2","0","1","0","2"],["1","1","2","1","1"],["1","1","0","1","1"],["0","2","0","1","1"]],"rtp":"92.69","payouts":[{"payout":"200","symbols":["0","0","0"],"type":"basic"},{"payout":"1000","symbols":["0","0","0","0"],"type":"basic"},{"payout":"2500","symbols":["0","0","0","0","0"],"type":"basic"},{"payout":"100","symbols":["1","1","1"],"type":"basic"},{"payout":"500","symbols":["1","1","1","1"],"type":"basic"},{"payout":"1000","symbols":["1","1","1","1","1"],"type":"basic"},{"payout":"50","symbols":["2","2","2"],"type":"basic"},{"payout":"200","symbols":["2","2","2","2"],"type":"basic"},{"payout":"500","symbols":["2","2","2","2","2"],"type":"basic"},{"payout":"25","symbols":["3","3","3"],"type":"basic"},{"payout":"100","symbols":["3","3","3","3"],"type":"basic"},{"payout":"200","symbols":["3","3","3","3","3"],"type":"basic"},{"payout":"10","symbols":["4","4","4"],"type":"basic"},{"payout":"50","symbols":["4","4","4","4"],"type":"basic"},{"payout":"100","symbols":["4","4","4","4","4"],"type":"basic"},{"payout":"5","symbols":["5","5","5"],"type":"basic"},{"payout":"25","symbols":["5","5","5","5"],"type":"basic"},{"payout":"50","symbols":["5","5","5","5","5"],"type":"basic"},{"payout":"5","symbols":["6","6","6"],"type":"basic"},{"payout":"10","symbols":["6","6","6","6"],"type":"basic"},{"payout":"25","symbols":["6","6","6","6","6"],"type":"basic"},{"payout":"5","symbols":["7","7","7"],"type":"basic"},{"payout":"10","symbols":["7","7","7","7"],"type":"basic"},{"payout":"25","symbols":["7","7","7","7","7"],"type":"basic"},{"payout":"1","symbols":["8","8","8"],"type":"scatter"},{"payout":"1","symbols":["9","9","9"],"type":"scatter"},{"payout":"5","symbols":["9","9","9","9"],"type":"scatter"},{"payout":"10","symbols":["9","9","9","9","9"],"type":"scatter"},{"payout":"50","symbols":["9","9","9","9","9","9"],"type":"scatter"},{"payout":"100","symbols":["9","9","9","9","9","9","9"],"type":"scatter"},{"payout":"650","symbols":["9","9","9","9","9","9","9","9"],"type":"scatter"},{"payout":"1000","symbols":["9","9","9","9","9","9","9","9","9"],"type":"scatter"},{"payout":"2","symbols":["10","10","10"],"type":"scatter"},{"payout":"25","symbols":["10","10","10","10"],"type":"scatter"},{"payout":"1000","symbols":["10","10","10","10","10"],"type":"scatter"},{"payout":"10","symbols":["11","11","11"],"type":"basic"},{"payout":"50","symbols":["11","11","11","11"],"type":"basic"},{"payout":"100","symbols":["11","11","11","11","11"],"type":"basic"},{"payout":"2","symbols":["12","12","12"],"type":"basic"},{"payout":"5","symbols":["12","12","12","12"],"type":"basic"},{"payout":"25","symbols":["12","12","12","12","12"],"type":"basic"},{"payout":"5","symbols":["13"],"type":"basic"},{"payout":"2","symbols":["13","13","13"],"type":"basic"},{"payout":"7","symbols":["14"],"type":"basic"},{"payout":"2","symbols":["14","14","14"],"type":"basic"},{"payout":"10","symbols":["15"],"type":"basic"},{"payout":"2","symbols":["15","15","15"],"type":"basic"},{"payout":"11","symbols":["16"],"type":"basic"},{"payout":"2","symbols":["16","16","16"],"type":"basic"},{"payout":"15","symbols":["17"],"type":"basic"},{"payout":"2","symbols":["17","17","17"],"type":"basic"},{"payout":"20","symbols":["18"],"type":"basic"},{"payout":"3","symbols":["18","18","18"],"type":"basic"},{"payout":"5","symbols":["19"],"type":"basic"}],"initialSymbols":[["9","3","4","7","1"],["0","6","8","3","9"],["5","8","5","0","2"]]},"jackpotsEnabled":"true","gameModes":"[]" '.$restoreString.'}}';
                                break;
                            case 'BalanceRequest':
                                $result_tmp[0] = '{"action":"BalanceResponse","result":"true","sesId":"10000373695","data":{"totalAmount":"' . $slotSettings->GetBalance() . '","currency":" "}}';
                                break;
                            case 'FreeSpinRequest':
                            case 'SpinRequest':
                                $postData['slotEvent'] = 'bet';
                                $bonusMpl = 1;
                                $linesId = $slotSettings->GetPaylines();
                                $lines = 30;
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
                                if( $postData['slotEvent'] != 'freespin' ) 
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
                                    $slotSettings->SetGameData($slotSettings->slotId . 'ScatterCount', 0);
                                }
                                else
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);
                                    $bonusMpl = $slotSettings->GetGameData($slotSettings->slotId . 'Mpl');
                                }
                                $balance = sprintf('%01.2f', $slotSettings->GetBalance());
                                $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $lines);
                                $winType = $winTypeTmp[0];
                                $spinWinLimit = $winTypeTmp[1];
                                if( $postData['slotEvent'] == 'freespin' ) 
                                {
                                    $bonusWin0 = $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin');
                                    $freeSpinRemain = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                                    if($freeSpinRemain < 3 && $bonusWin0 <= $allbet * 5)
                                        $winType = 'win';
                                    $bonusMpl = $slotSettings->GetGameData($slotSettings->slotId . 'BonusMpl');
                                }
                                if($debug)
                                {
                                    $winType = 'bonus';
                                }
                                
                                $spinAcquired = false;

                                $minTotalWin = -1;
                                $minLineWins = [];
                                $minScattersCount = 0;
                                $sevens = [1,2,3];
                                $bars = [4,5];
                                $firediamond = '9';
                                for( $i = 0; $i <= 500; $i++ ) 
                                {
                                    $totalWin = 0;
                                    $lineWins = [];
                                    $cWins = array_fill(0, $lines, 0);
                                    $wild = ['0'];
                                    $scatter = '8';
                                    if($postData['slotEvent'] == 'freespin')
                                    {
                                        $cBank = $allbet * 20 - $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin');
                                        $spinsLeft = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                                        if($spinsLeft == 0)
                                            $spinsLeft = 1;
                                        $spinWinLimit = $cBank / $spinsLeft;
                                    }
                                    $reels = $slotSettings->GetReelStrips($winType, $postData['slotEvent']);
                                                                                                            
                                    for( $k = 0; $k < $lines; $k++ ) 
                                    {
                                        $tmpStringWin = '';
                                        for( $j = 0; $j < count($slotSettings->SymbolGame); $j++ ) 
                                        {
                                            $csym = $slotSettings->SymbolGame[$j];
                                            if( $csym == $scatter || $csym == $firediamond || !isset($slotSettings->Paytable['SYM_' . $csym]) ) 
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
                                                        $mpl = 1;
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
                                                        $mpl = 1;
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
                                                        $mpl = 1;
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

                                                //sevens
                                                if( (in_array($s[0], $sevens) || in_array($s[0], $wild)) && (in_array($s[1], $sevens) || in_array($s[1], $wild)) && (in_array($s[2], $sevens)|| in_array($s[2], $wild)) ) 
                                                {
                                                    $mpl = 1;
                                                    $cost = 10;
                                                    $tmpWin = $cost * $betLine * $mpl * $bonusMpl;
                                                    if( $cWins[$k] < $tmpWin ) 
                                                    {
                                                        $cWins[$k] = $tmpWin;
                                                        $tmpStringWin = '{"type":"LineWinAmount","selectedLine":"' . $k . '","amount":"' . $tmpWin . '","wonSymbols":[["0","' . $p0 . '"],["1","' . $p1 . '"],["2","' . $p2 . '"]]}';
                                                    }
                                                }
                                                if( (in_array($s[0], $sevens) || in_array($s[0], $wild)) && (in_array($s[1], $sevens) || in_array($s[1], $wild)) && (in_array($s[2], $sevens)|| in_array($s[2], $wild)) && (in_array($s[3], $sevens)|| in_array($s[3], $wild))) 
                                                {
                                                    $mpl = 1;
                                                    $cost = 50;
                                                    $tmpWin = $cost * $betLine * $mpl * $bonusMpl;
                                                    if( $cWins[$k] < $tmpWin ) 
                                                    {
                                                        $cWins[$k] = $tmpWin;
                                                        $tmpStringWin = '{"type":"LineWinAmount","selectedLine":"' . $k . '","amount":"' . $tmpWin . '","wonSymbols":[["0","' . $p0 . '"],["1","' . $p1 . '"],["2","' . $p2 . '"],["3","' . $p3 . '"]]}';
                                                    }
                                                }
                                                if( (in_array($s[0], $sevens) || in_array($s[0], $wild)) && (in_array($s[1], $sevens) || in_array($s[1], $wild)) && (in_array($s[2], $sevens)|| in_array($s[2], $wild)) && (in_array($s[3], $sevens)|| in_array($s[3], $wild)) && (in_array($s[4], $sevens)|| in_array($s[4], $wild))) 
                                                {
                                                    $mpl = 1;
                                                    $cost = 100;
                                                    $tmpWin = $cost * $betLine * $mpl * $bonusMpl;
                                                    if( $cWins[$k] < $tmpWin ) 
                                                    {
                                                        $cWins[$k] = $tmpWin;
                                                        $tmpStringWin = '{"type":"LineWinAmount","selectedLine":"' . $k . '","amount":"' . $tmpWin . '","wonSymbols":[["0","' . $p0 . '"],["1","' . $p1 . '"],["2","' . $p2 . '"],["3","' . $p3 . '"],["4","' . $p4 . '"]]}';
                                                    }
                                                }

                                                //bars
                                                if( (in_array($s[0], $bars) || in_array($s[0], $wild)) && (in_array($s[1], $bars) || in_array($s[1], $wild)) && (in_array($s[2], $bars)|| in_array($s[2], $wild)) ) 
                                                {
                                                    $mpl = 1;
                                                    $cost = 2;
                                                    $tmpWin = $cost * $betLine * $mpl * $bonusMpl;
                                                    if( $cWins[$k] < $tmpWin ) 
                                                    {
                                                        $cWins[$k] = $tmpWin;
                                                        $tmpStringWin = '{"type":"LineWinAmount","selectedLine":"' . $k . '","amount":"' . $tmpWin . '","wonSymbols":[["0","' . $p0 . '"],["1","' . $p1 . '"],["2","' . $p2 . '"]]}';
                                                    }
                                                }
                                                if( (in_array($s[0], $bars) || in_array($s[0], $wild)) && (in_array($s[1], $bars) || in_array($s[1], $wild)) && (in_array($s[2], $bars)|| in_array($s[2], $wild)) && (in_array($s[3], $bars)|| in_array($s[3], $wild))) 
                                                {
                                                    $mpl = 1;
                                                    $cost = 5;
                                                    $tmpWin = $cost * $betLine * $mpl * $bonusMpl;
                                                    if( $cWins[$k] < $tmpWin ) 
                                                    {
                                                        $cWins[$k] = $tmpWin;
                                                        $tmpStringWin = '{"type":"LineWinAmount","selectedLine":"' . $k . '","amount":"' . $tmpWin . '","wonSymbols":[["0","' . $p0 . '"],["1","' . $p1 . '"],["2","' . $p2 . '"],["3","' . $p3 . '"]]}';
                                                    }
                                                }
                                                if( (in_array($s[0], $bars) || in_array($s[0], $wild)) && (in_array($s[1], $bars) || in_array($s[1], $wild)) && (in_array($s[2], $bars)|| in_array($s[2], $wild)) && (in_array($s[3], $bars)|| in_array($s[3], $wild)) && (in_array($s[4], $bars)|| in_array($s[4], $wild))) 
                                                {
                                                    $mpl = 1;
                                                    $cost = 25;
                                                    $tmpWin = $cost * $betLine * $mpl * $bonusMpl;
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
                                    for( $r = 1; $r <= 5; $r++ ) 
                                    {
                                        for( $p = 0; $p <= 2; $p++ ) 
                                        {
                                            if( $reels['reel' . $r][$p] == $scatter ) 
                                            {
                                                $scattersCount++;
                                                $scattersPos[] = '["' . ($r - 1) . '","' . $p . '"]';
                                            }
                                        }
                                    }                                   
                                    if( $scattersCount >= 3 && $winType != 'bonus')
                                        continue;
                                    $gameState = 'Ready';
                                    if( $scattersCount >= 3 && $slotSettings->slotBonus ) 
                                    {
                                        $scattersWin = $allbet;
                                        $gameState = 'PickBonus';
                                        $scw = '{"type":"Bonus","bonusName":"PickBonus","params":{"fields": "20"},"amount":"' . $slotSettings->FormatFloat($scattersWin) . '","wonSymbols":[' . implode(',', $scattersPos) . ']}';
                                        array_push($lineWins, $scw);
                                    }
                                    $totalWin += $scattersWin;
                                    
                                    $fireCount = 0;
                                    $firePos = [];
                                    $fireWin = 0;
                                    for( $r = 1; $r <= 5; $r++ ) 
                                    {
                                        for( $p = 0; $p <= 2; $p++ ) 
                                        {
                                            if( $reels['reel' . $r][$p] == $firediamond ) 
                                            {
                                                $fireCount++;
                                                $firePos[] = [($r - 1), $p];
                                            }
                                        }
                                    }
                                    if($fireCount > 2)
                                    {
                                        if($fireCount > 9)
                                            $fireCount = 9;
                                        $fireWin = $slotSettings->Paytable['SYM_9'][$fireCount] * $betLine * $bonusMpl;
                                        $data = ['amount' => $fireWin, 'type'=>'WinAmount', 'wonSymbols' => $firePos];
                                        $tmpStringWin = json_encode($data);
                                        array_push($lineWins, $tmpStringWin);
                                    }
                                    $totalWin += $fireWin;
 
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
                                    $totalWin = $minTotalWin;
                                    $lineWins = $minLineWins;
                                    $reels = $minReels;
                                    $scattersCount = $minScattersCount;
                                }
                                                                
                                if( $totalWin > 0 ) 
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
                                    $slotSettings->SetGameData($slotSettings->slotId . 'PickedItems', array_fill(0, 20, 0));
                                    $slotSettings->SetGameData($slotSettings->slotId . 'ScatterCount', $scattersCount);
                                    
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
                                if( $totalWin > 0 ) 
                                {
                                    $winString = ',"slotWin":{"totalWin":"' . $totalWin . '","lineWinAmounts":[' . implode(',', $lineWins) . '],"canGamble":"false"}';
                                }
                                else
                                {
                                    $winString = '';
                                }
                                $response = '{"responseEvent":"spin","responseType":"' . $postData['slotEvent'] . '","serverResponse":{"BonusSymbol":' . $slotSettings->GetGameData($slotSettings->slotId . 'BonusSymbol') . ',"slotLines":' . $lines . ',"slotBet":' . $betLine . ',"totalFreeGames":' . $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') . ',"currentFreeGames":' . $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') . ',"Balance":' . $slotSettings->GetBalance() . ',"afterBalance":' . $slotSettings->GetBalance() . ',"bonusWin":' . $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin') . ',"freeStartWin":' . $slotSettings->GetGameData($slotSettings->slotId . 'FreeStartWin') . ',"totalWin":' . $totalWin . ',"winLines":[],"bonusInfo":[],"Jackpots":' . $jsJack . ',"reelsSymbols":' . $jsSpin . '}}';
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
                            $strLog .= '{"gameName": "'.$game.'", "userID": "'.$userId.'"}';
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
