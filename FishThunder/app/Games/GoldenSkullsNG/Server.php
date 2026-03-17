<?php 
namespace VanguardLTE\Games\GoldenSkullsNG
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
                                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $lastEvent->serverResponse->bonusWin);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BonusSymbol', $lastEvent->serverResponse->BonusSymbol);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeBalance', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeStartWin', 0);
                                    $rp2 = '[' . $lastEvent->serverResponse->reelsSymbols->reel1[0] . ',' . $lastEvent->serverResponse->reelsSymbols->reel2[0] . ',' . $lastEvent->serverResponse->reelsSymbols->reel3[0] . ',' . $lastEvent->serverResponse->reelsSymbols->reel4[0] . ',' . $lastEvent->serverResponse->reelsSymbols->reel5[0] . ']';
                                    $rp2 .= (',[' . $lastEvent->serverResponse->reelsSymbols->reel1[1] . ',' . $lastEvent->serverResponse->reelsSymbols->reel2[1] . ',' . $lastEvent->serverResponse->reelsSymbols->reel3[1] . ',' . $lastEvent->serverResponse->reelsSymbols->reel4[1] . ',' . $lastEvent->serverResponse->reelsSymbols->reel5[1] . ']');
                                    $rp2 .= (',[' . $lastEvent->serverResponse->reelsSymbols->reel1[2] . ',' . $lastEvent->serverResponse->reelsSymbols->reel2[2] . ',' . $lastEvent->serverResponse->reelsSymbols->reel3[2] . ',' . $lastEvent->serverResponse->reelsSymbols->reel4[2] . ',' . $lastEvent->serverResponse->reelsSymbols->reel5[2] . ']');
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
                                $gameState = $slotSettings->GetGameData($slotSettings->slotId . 'GameState');
                                $restoreString = '';
                                $defaultBet = $slotSettings->GetGameData($slotSettings->slotId . 'BetLine');
                                if($defaultBet == 0)
                                $defaultBet = 1;                            
                                $rougeWin =  $slotSettings->GetGameData($slotSettings->slotId . 'RougeWin');  

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
                                            $items[] = ['item' => 'BonusItem', 'index' => $i, 'value' => $pickedItems[$i], 'picked' => 'false'];
                                    }
                                    $itemString = json_encode($items);
                                    if(count($items) == 0)
                                        $itemString = '""';
                                    $restoreString = ',"restoredGameCode":"176","lastResponse":{"spinResult":{"type":"SpinResult","rows":[' . $rp2 . ']},"totalWin":"' . $fBonusWin . '","state":"PickBonus", "items":'.$itemString.'}';
                                }
                                else if($gameState === 'FreeSpins')
                                {
                                    if( $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') < $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') ) 
                                    {
                                        $rogueSyms = $slotSettings->GetGameData($slotSettings->slotId . 'RogueSyms');
                                        $rogueMask = 0;
                                        for($r = 0; $r < 5; $r++)
                                        {
                                            if($rogueSyms[$r] == 1)
                                            {
                                                $rogueMask += pow(2, 4 - $r);
                                            }
                                        }                                        

                                        $fBonusWin = $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin');
                                        $fTotal = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                                        $fCurrent = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                                        $fRemain = $fTotal - $fCurrent;                                        
                                        $restoreString = ',"restoredGameCode":"176","lastResponse":{"rougeMask":"'.$rogueMask.'","rougeWin":"'.$rougeWin.'", "spinResult":{"type":"SpinResult","rows":[' . $rp2 . ']},"freeSpinsTotal":"' . $fTotal . '","freeSpinRemain":"' . $fRemain . '","totalBonusWin":"' . $fBonusWin . '","state":"FreeSpins"}';
                                    } 
                                }
                                $result_tmp[0] = '{"action":"AuthResponse","result":"true","sesId":"10000103715","data":{"snivy":"proxy v6.12.51 (API v4.23)","supportedFeatures":["Offers","Jackpots","InstantJackpots","SweepStakes"],"sessionId":"10000103715","defaultLines":["0","1","2","3","4","5","6","7","8","9"],"bets":["1","2","3","4","5","10","15","20","30","40","50"],"betMultiplier":"1.0000000","defaultBet":"'.$defaultBet.'","defaultCoinValue":"0.01","coinValues":["0.01"],"gameParameters":{"availableLines":[["1","1","1","1","1"],["0","0","0","0","0"],["2","2","2","2","2"],["0","1","2","1","0"],["2","1","0","1","2"],["1","2","2","2","1"],["1","0","0","0","1"],["2","2","1","0","0"],["0","0","1","2","2"],["2","1","1","1","0"]],"rtp":"95.27","payouts":[{"payout":"25","symbols":["0","0","0"],"type":"basic"},{"payout":"125","symbols":["0","0","0","0"],"type":"basic"},{"payout":"1000","symbols":["0","0","0","0","0"],"type":"basic"},{"payout":"25","symbols":["1","1","1"],"type":"basic"},{"payout":"125","symbols":["1","1","1","1"],"type":"basic"},{"payout":"1000","symbols":["1","1","1","1","1"],"type":"basic"},{"payout":"20","symbols":["2","2","2"],"type":"basic"},{"payout":"100","symbols":["2","2","2","2"],"type":"basic"},{"payout":"500","symbols":["2","2","2","2","2"],"type":"basic"},{"payout":"15","symbols":["3","3","3"],"type":"basic"},{"payout":"75","symbols":["3","3","3","3"],"type":"basic"},{"payout":"250","symbols":["3","3","3","3","3"],"type":"basic"},{"payout":"15","symbols":["4","4","4"],"type":"basic"},{"payout":"75","symbols":["4","4","4","4"],"type":"basic"},{"payout":"250","symbols":["4","4","4","4","4"],"type":"basic"},{"payout":"10","symbols":["5","5","5"],"type":"basic"},{"payout":"50","symbols":["5","5","5","5"],"type":"basic"},{"payout":"125","symbols":["5","5","5","5","5"],"type":"basic"},{"payout":"10","symbols":["6","6","6"],"type":"basic"},{"payout":"50","symbols":["6","6","6","6"],"type":"basic"},{"payout":"125","symbols":["6","6","6","6","6"],"type":"basic"},{"payout":"5","symbols":["7","7","7"],"type":"basic"},{"payout":"25","symbols":["7","7","7","7"],"type":"basic"},{"payout":"100","symbols":["7","7","7","7","7"],"type":"basic"},{"payout":"5","symbols":["8","8","8"],"type":"basic"},{"payout":"25","symbols":["8","8","8","8"],"type":"basic"},{"payout":"100","symbols":["8","8","8","8","8"],"type":"basic"},{"payout":"5","symbols":["9","9","9"],"type":"basic"},{"payout":"25","symbols":["9","9","9","9"],"type":"basic"},{"payout":"100","symbols":["9","9","9","9","9"],"type":"basic"},{"payout":"5","symbols":["10","10","10"],"type":"basic"},{"payout":"25","symbols":["10","10","10","10"],"type":"basic"},{"payout":"100","symbols":["10","10","10","10","10"],"type":"basic"},{"payout":"20","symbols":["12","12"],"type":"basic"},{"payout":"250","symbols":["12","12","12"],"type":"basic"},{"payout":"2500","symbols":["12","12","12","12"],"type":"basic"},{"payout":"10000","symbols":["12","12","12","12","12"],"type":"basic"}],"initialSymbols":[["0","1","2","3","4"],["5","6","7","8","9"],["10","11","12","13","14"]]},"jackpotsEnabled":"true","gameModes":"[]" '.$restoreString.'}}';
                                break;
                            case 'BalanceRequest':
                                $result_tmp[0] = '{"action":"BalanceResponse","result":"true","sesId":"10000373695","data":{"totalAmount":"' . $slotSettings->GetBalance() . '","currency":" "}}';
                                break;
                            case 'PickBonusItemRequest':
                                $selectedItem = (int)$postData['data']['index'];
                                $symbols = [1,1,1,2,3];
                                $pickedItems = $slotSettings->GetGameData($slotSettings->slotId . 'PickedItems');
                                $pickedCount = $slotSettings->GetGameData($slotSettings->slotId . 'PickedCount');
                                $pickedCount++;
                                $slotSettings->SetGameData($slotSettings->slotId . 'PickedCount', $pickedCount);
                                if($pickedItems == 0)
                                    $pickedItems = array_fill(0, 18, 0);
                                $rd = rand(0, 60);
                                $index = 0;
                                if($rd < 25)
                                    $index = 0;
                                else if($rd < 35)
                                    $index = 1;
                                else if($rd < 45)
                                    $index = 2;
                                else if($rd < 55)
                                    $index = 3;
                                else 
                                    $index = 4;                                
                                $picked = $symbols[$index];
                                $pickedItems[$selectedItem] = $picked;
                                $slotSettings->SetGameData($slotSettings->slotId . 'PickedItems', $pickedItems);

                                $lastPick = 'false';
                                $state = 'PickBonus';
                                $freespinCnt = 0;
                                for($i = 0; $i < count($pickedItems); $i++)
                                {
                                    if($pickedItems[$i] != 0)
                                        $freespinCnt += $pickedItems[$i];
                                }
                                
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') + $picked);
                                $params = ['freeSpins' => $freespinCnt, 'freeSpinsTotal' => $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames')];
                                if($pickedCount == 6)
                                {
                                    $lastPick = 'true';
                                    $state = 'FreeSpins';
                                }
                                $data = ['action' => 'PickBonusItemResponse', 'result' => 'true', 'sesId' => '10000079423',
                                        'data' => [
                                            'lastPick' => $lastPick,
                                            'params' => $params,
                                            'state' => $state,
                                            'bonusItem' => ['index' => $selectedItem, 'picked' => 'true', 'type' => 'BonusItem', 'value' => $picked]
                                        ]
                                    ];
                                $slotSettings->SetGameData($slotSettings->slotId . 'RougeWin', $freespinCnt);
                                if($pickedCount == 6)
                                {
                                    //fill rest fields
                                    $items = [];
                                    for($i = 0; $i < count($pickedItems); $i++)
                                    {
                                        $picked = 'false';
                                        if($pickedItems[$i] == 0)
                                        {
                                            $pickedItems[$i] = $symbols[rand(0, count($symbols) - 1)];
                                        }
                                        else
                                            $picked = 'true';
                                        $items[] = ['index' => $i, 'picked' => $picked, 'type' => 'BonusItem', 'value' => $pickedItems[$i]];
                                    }
                                    $data['data']['items'] = $items;
                                    $slotSettings->SetGameData($slotSettings->slotId . 'GameState', $state);                                    
                                }
                                $result_tmp[] = json_encode($data);
                                break;
                            case 'FreeSpinRequest':
                            case 'SpinRequest':
                                $postData['slotEvent'] = 'bet';
                                $bonusMpl = 1;
                                $linesId = $slotSettings->GetPaylines();
                                $lines = 10;
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
                                }
                                if($debug && $postData['slotEvent'] != 'freespin')
                                {
                                    $winType = 'bonus';
                                }
                                
                                $spinAcquired = false;

                                $minTotalWin = -1;
                                $minLineWins = [];
                                $minScattersCount = 0;
                                $minRogueSyms = array_fill(0, 5, 0);
                                $wild = [12,13,14,15,16,17];
                                $scatter = '11';
                                $freespinCnt = 0;
                                $rogueSyms = array_fill(0, 5, 0);
                                for( $i = 0; $i <= 500; $i++ ) 
                                {
                                    $totalWin = 0;
                                    $lineWins = [];
                                    $cWins = array_fill(0, $lines, 0);
                                    $rogueSyms = $slotSettings->GetGameData($slotSettings->slotId . 'RogueSyms');
                                    $reels = $slotSettings->GetReelStrips($winType, $postData['slotEvent']);

                                    if($postData['slotEvent'] == 'freespin')
                                    {
                                        $rnd = rand(0, 100);
                                        $possibility = 10;
                                        if($slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') > 35)
                                            $possibility = 5;
                                        if($slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') > 50)
                                            $possibility = 0;
                                        if($slotSettings->GetGameData($slotSettings->slotId . 'TeqhillaAppear') < 50)
                                            $possibility = 0;
                                        if($rnd < $possibility)
                                            $reels['reel1'][rand(0,2)] = 13;
                                        $rnd = rand(0, 100);
                                        if($rnd < $possibility)
                                            $reels['reel2'][rand(0,2)] = 14;
                                        $rnd = rand(0, 100);
                                        if($rnd < $possibility)
                                            $reels['reel3'][rand(0,2)] = 15;
                                        $rnd = rand(0, 100);
                                        if($rnd < $possibility)
                                            $reels['reel4'][rand(0,2)] = 16;
                                        $rnd = rand(0, 100);
                                        if($rnd < $possibility)
                                            $reels['reel5'][rand(0,2)] = 17;
                                    }
                                                                                                            
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

                                    $scattersWin = 0;
                                    $gameState = 'Ready';
                                    if($scattersCount >= 3 && $winType != 'bonus')
                                        continue;
                                    if( $scattersCount >= 3 && $slotSettings->slotBonus ) 
                                    {
                                        if($scattersCount > 5)
                                            $scattersCount = 5;
                                        $freespinCnt = 12;
                                        if($scattersCount == 4)
                                            $freespinCnt = 15;
                                        else if($scattersCount == 5)
                                            $freespinCnt = 20;
                                        $gameState = 'FreeSpins';
                                        $scattersWin = $slotSettings->Paytable['SYM_' . $scatter][$scattersCount] * $betLine * $bonusMpl;
                                        $scw = '{"type":"Bonus","bonusName":"FreeSpins","params":{"freeSpins": "'.$freespinCnt.'"},"amount":"' . $slotSettings->FormatFloat($scattersWin) . '","wonSymbols":[' . implode(',', $scattersPos) . ']}';
                                        array_push($lineWins, $scw);                                        
                                    }

                                    if($postData['slotEvent'] == 'freespin')
                                    {
                                        //check rogue symbols      
                                        $mask = 0;
                                        $rogueSymPos = [];
                                        for($r = 0; $r < 5; $r++)
                                            for($c = 0; $c < 3; $c++)
                                            {
                                                if($reels['reel'.($r+1)][$c] > 12)
                                                {
                                                    if($rogueSyms[$r] != 1)
                                                    {
                                                        $rogueSyms[$r] = 1;
                                                        $rogueSymPos[] = [$r, $c];
                                                        $mask += pow(2, 4 - $r);
                                                    }
                                                }
                                            }
                                        if($mask > 0)
                                        {
                                            $rw = [
                                                'bonusName' => 'RougeSymbol', 'params' => ['symbolMask'=>$mask], 'type'=>'Bonus', 'wonSymbols' => $rogueSymPos
                                            ];
                                            array_push($lineWins, json_encode($rw));
                                        }                                        
                                    }
                                    
                                    $totalWin += $scattersWin;
 
                                    if($minTotalWin == -1 || ($totalWin > 0 && $totalWin < $minTotalWin))
                                    {
                                        $minLineWins = $lineWins;
                                        $minReels = $reels;
                                        $minTotalWin = $totalWin;
                                        $minScattersCount = $scattersCount;
                                        $minRogueSyms = $rogueSyms;
                                    }

                                    if($debug)
                                    {
                                        $spinAcquired = true;
                                        break;
                                    }

                                    if($totalWin <= $spinWinLimit && (($totalWin > 0 && $winType != 'none') || ($winType == 'bonus' && $freespinCnt > 0)))
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
                                    $rogueSyms = $minRogueSyms;
                                }
                                                                
                                if( $totalWin > 0 && !$debug) 
                                {
                                    $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), -1 * $totalWin);
                                    $slotSettings->SetBalance($totalWin);
                                    $slotSettings->SetWin($totalWin);
                                }
                                $reportWin = $totalWin;
                                $rogueMask = 0; 
                                if( $postData['slotEvent'] == 'freespin' ) 
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin') + $totalWin);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin') + $totalWin);
                                    $gameState = 'FreeSpins';
                                    for($r = 0; $r < 5; $r++)
                                    {
                                        if($rogueSyms[$r] == 1)
                                        {
                                            $rogueMask += pow(2, 4 - $r);
                                        }
                                    }
                                    $slotSettings->SetGameData($slotSettings->slotId . 'RogueSyms', $rogueSyms);
                                    if($rogueMask == 31 && $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') <= $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame'))
                                    {
                                        $pw = ['bonusName' => 'PickBonus', 'params' => ['pickItems'=>'18'], 'type'=>'Bonus'];
                                        array_push($lineWins, json_encode($pw));
                                        $gameState = 'PickBonus';
                                        $slotSettings->SetGameData($slotSettings->slotId . 'PickedItems', array_fill(0, 18, 0));
                                        $slotSettings->SetGameData($slotSettings->slotId . 'PickedCount', 0);
                                        $slotSettings->SetGameData($slotSettings->slotId . 'RogueSyms', array_fill(0,5,0));
                                    }
                                }
                                else
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $totalWin);
                                }

                                if( $scattersCount >= 3 ) 
                                {                                    
                                    $slotSettings->SetGameData($slotSettings->slotId . 'ScatterCount', $scattersCount);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'TeqhillaAppear', rand(0, 100));
                                    
                                    if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') > 0 ) 
                                    {
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') + $freespinCnt);
                                    }
                                    else
                                    {
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeStartWin', $totalWin);
                                        $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', $totalWin);
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $freespinCnt);
                                        $slotSettings->SetGameData($slotSettings->slotId . 'RogueSyms', array_fill(0,5,0));
                                    }
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
                                $symb = '["' . $reels['reel1'][0] . '","' . $reels['reel2'][0] . '","' . $reels['reel3'][0] . '","' . $reels['reel4'][0] . '","' . $reels['reel5'][0] . '"],["' . $reels['reel1'][1] . '","' . $reels['reel2'][1] . '","' . $reels['reel3'][1] . '","' . $reels['reel4'][1] . '","' . $reels['reel5'][1] . '"],["' . $reels['reel1'][2] . '","' . $reels['reel2'][2] . '","' . $reels['reel3'][2] . '","' . $reels['reel4'][2] . '","' . $reels['reel5'][2] . '"]';
                                if($postData['slotEvent'] == 'freespin')
                                    $allbet = 0;
                                $slotSettings->SaveLogReport($response, $allbet, $reportWin, $postData['slotEvent']);
                                if( $postData['slotEvent'] == 'freespin' ) 
                                {
                                    $bonusWin0 = $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin');
                                    $freeSpinRemain = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                                    $freeSpinsTotal = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                                    
                                    $gameParameters = '';
                                    
                                    if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') <= $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') && $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') > 0 ) 
                                    {
                                        //check teqhilla
                                        if ($rogueMask < 31)                                        
                                        {
                                            $gameState = 'Ready';
                                            $gameParameters = '"gameParameters":{"initialSymbols":[' . $slotSettings->GetGameData($slotSettings->slotId . 'initialSymbols') . ']},';
                                        }                                                                               
                                    }
                                    
                                    $result_tmp[] = '{"action":"FreeSpinResponse","result":"true","sesId":"10000228087","data":{' . $gameParameters . '"state":"' . $gameState . '"' . $winString . ',"spinResult":{"type":"SpinResult","rows":[' . $symb . ']},"totalBonusWin":"' . $slotSettings->FormatFloat($bonusWin0) . '","freeSpinRemain":"' . $freeSpinRemain . '","freeSpinsTotal":"' . $freeSpinsTotal . '", "rougeMask":"'.$rogueMask.'"}}';
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
