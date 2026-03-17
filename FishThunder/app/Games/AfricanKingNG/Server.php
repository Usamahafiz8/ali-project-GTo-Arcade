<?php 
namespace VanguardLTE\Games\AfricanKingNG
{

    use Illuminate\Support\Facades\DB;

    set_time_limit(5);
    class Server
    {
        private $debug = false;
        public function get($request, $game)
        {
            try
            {
                $userId = \Auth::id();
                DB::beginTransaction();
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
                    if( $slotSettings->GetBalance() < ($postData['data']['coin'] * $postData['data']['bet'] * 30) && $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') <= 0 ) 
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
                        $item = $postData['data']['index'];
                        $Items = $slotSettings->GetGameData($slotSettings->slotId . 'Items');
                        $BonusState = $slotSettings->GetGameData($slotSettings->slotId . 'BonusState');
                        $Picks = $slotSettings->GetGameData($slotSettings->slotId . 'Picks');
                        $SelectedItems = $slotSettings->GetGameData($slotSettings->slotId . 'SelectedItems');
                        if( $BonusState != 2 ) 
                        {
                            exit();
                        }
                        $SelectedItems[] = $item;
                        $Picks--;
                        $pickOptions = [0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,2,2,2,2,2,2,2,2,2,3,3];
                        shuffle($pickOptions);
                        $freespinWon = $pickOptions[0];
                        if($freespinWon == 0)
                            $freespinWon = 5;
                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') + $freespinWon);
                        if( $Picks == 0 ) 
                        {
                            $lastPick = 'true';
                            $state = 'FreeSpins';
                            $bonusItem = '{"type":"IndexedItem","index":"' . $item . '","value":"1' . $pickOptions[0] . '","picked":"true"}';
                            $Items[$item] = $bonusItem;
                            $params = '"params":{"freeSpins":"' . $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') . '","multiplier":"1","freeSpinRemain":"' . $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') . '","freeSpinsTotal":"' . $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') . '"}';
                            $items = [];
                            for( $i = 0; $i < 25; $i++ ) 
                            {
                                if(in_array($i, $SelectedItems))
                                {
                                    $items[] = $Items[$i];
                                }
                                else
                                {
                                    $items[] = '{"type":"IndexedItem","index":"' . $i . '","value":"1' . rand(0, 3) . '","picked":"false"}';
                                }
                            }
                            $allItems = ',"items":[' . implode(',', $items) . ']';
                            $slotSettings->SetGameData($slotSettings->slotId . 'GameState', 'FreeSpins');
                        }
                        else
                        {
                            $lastPick = 'false';
                            $allItems = '';
                            $state = 'PickBonus';
                            $bonusItem = '{"type":"IndexedItem","index":"' . $item . '","value":"1' . $pickOptions[0] . '","picked":"false"}';
                            $params = '"params":{"freeSpins":"' . $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') . '"}';
                            $Items[$item] = $bonusItem;
                        }
                        $result_tmp[] = '{"action":"PickBonusItemResponse","result":"true","sesId":"10000608346","data":{"lastPick":"' . $lastPick . '","bonusItem":' . $bonusItem . '' . $allItems . ',"state":"' . $state . '",' . $params . '}}';
                        $slotSettings->SetGameData($slotSettings->slotId . 'Picks', $Picks);
                        $slotSettings->SetGameData($slotSettings->slotId . 'Items', $Items);
                        $slotSettings->SetGameData($slotSettings->slotId . 'BonusState', $BonusState);
                        $slotSettings->SetGameData($slotSettings->slotId . 'SelectedItems', $SelectedItems);
                        break;
                    case 'CheckBrokenGameRequest':
                        $result_tmp[] = '{"action":"CheckBrokenGameResponse","result":"true","sesId":"false","data":{"haveBrokenGame":"false"}}';
                        break;
                    case 'AuthRequest':
                       
                                                                                            
                        $lastEvent = $slotSettings->GetHistory();
                        if( $lastEvent != 'NULL' ) 
                        {
                        //     $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', $lastEvent->serverResponse->bonusWin);
                        //     $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $lastEvent->serverResponse->totalFreeGames);
                        //     $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $lastEvent->serverResponse->currentFreeGames);
                        //     $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $lastEvent->serverResponse->bonusWin);
                        //     $slotSettings->SetGameData($slotSettings->slotId . 'BonusSymbol', $lastEvent->serverResponse->BonusSymbol);
                            // $slotSettings->SetGameData($slotSettings->slotId . 'FreeBalance', 0);
                            // $slotSettings->SetGameData($slotSettings->slotId . 'FreeStartWin', 0);
                            $rp1 = implode(',', $lastEvent->serverResponse->reelsSymbols->rp);
                            $rp2 = '[' . $lastEvent->serverResponse->reelsSymbols->reel1[0] . ',' . $lastEvent->serverResponse->reelsSymbols->reel2[0] . ',' . $lastEvent->serverResponse->reelsSymbols->reel3[0] . ',' . $lastEvent->serverResponse->reelsSymbols->reel4[0] . ',' . $lastEvent->serverResponse->reelsSymbols->reel5[0] . ']';
                            $rp2 .= (',[' . $lastEvent->serverResponse->reelsSymbols->reel1[1] . ',' . $lastEvent->serverResponse->reelsSymbols->reel2[1] . ',' . $lastEvent->serverResponse->reelsSymbols->reel3[1] . ',' . $lastEvent->serverResponse->reelsSymbols->reel4[1] . ',' . $lastEvent->serverResponse->reelsSymbols->reel5[1] . ']');
                            $rp2 .= (',[' . $lastEvent->serverResponse->reelsSymbols->reel1[2] . ',' . $lastEvent->serverResponse->reelsSymbols->reel2[2] . ',' . $lastEvent->serverResponse->reelsSymbols->reel3[2] . ',' . $lastEvent->serverResponse->reelsSymbols->reel4[2] . ',' . $lastEvent->serverResponse->reelsSymbols->reel5[2] . ']');
                            $bet = $lastEvent->serverResponse->slotBet * 100 * 20;
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
                        if($gameState === 'PickBonus')
                        {
                            $fBonusWin = $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin');
                            $Items = $slotSettings->GetGameData($slotSettings->slotId . 'Items');
                            $itemString = '['.implode(',', $Items).']';
                            if(count($Items) == 0)
                                $itemString = '""';
                            $restoreString = ',"restoredGameCode":"254","lastResponse":{"spinResult":{"type":"SpinResult","rows":[' . $rp2 . ']},"totalWin":"' . $fBonusWin . '","state":"PickBonus", "items":'.$itemString.',"params":{"pickedItems":"'.$slotSettings->GetGameData($slotSettings->slotId . 'TotalPicks').'"},"freeSpins":"'.$slotSettings->GetGameData($slotSettings->slotId . 'FreeGames').'"}';
                        }
                        else if($gameState === 'FreeSpins' || $gameState === 'ReSpins')
                        {
                            if( $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') < $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') ) 
                            {
                                $fBonusWin = $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin');
                                $fTotal = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                                $fCurrent = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                                $fRemain = $fTotal - $fCurrent;
                                $restoreString = ',"restoredGameCode":"254","lastResponse":{"spinResult":{"type":"SpinResult","rows":[' . $rp2 . ']},"freeSpinsTotal":"' . $fTotal . '","freeSpinRemain":"' . $fRemain . '","totalBonusWin":"' . $fBonusWin . '","state":"FreeSpins"}';
                            }
                        }
                        $result_tmp[0] = '{"action":"AuthResponse","result":"true","sesId":"10000296198","data":{"snivy":"proxy v6.10.48 (API v4.23)","supportedFeatures":["Offers","Jackpots","InstantJackpots","SweepStakes"],"sessionId":"10000296198","defaultLines":["0","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19"],"bets":["1","2","3","4","5","10","15"],"betMultiplier":"1.5000000","defaultBet":"'.$defaultBet.'","defaultCoinValue":"0.01","coinValues":["0.01"],"gameParameters":{"availableLines":[["1","1","1","1","1"],["0","0","0","0","0"],["2","2","2","2","2"],["0","1","2","1","0"],["2","1","0","1","2"],["1","0","1","2","1"],["1","2","1","0","1"],["0","0","1","2","2"],["2","2","1","0","0"],["0","1","0","1","0"],["2","1","2","1","2"],["1","0","0","0","1"],["1","2","2","2","1"],["0","1","1","1","0"],["2","1","1","1","2"],["1","1","0","1","1"],["1","1","2","1","1"],["0","2","0","2","0"],["2","0","2","0","2"],["2","0","1","0","2"]],"rtp":"0.00","payouts":[{"payout":"15","symbols":["0","0"],"type":"basic"},{"payout":"100","symbols":["0","0","0"],"type":"basic"},{"payout":"1000","symbols":["0","0","0","0"],"type":"basic"},{"payout":"3000","symbols":["0","0","0","0","0"],"type":"basic"},{"payout":"5","symbols":["2","2"],"type":"basic"},{"payout":"50","symbols":["2","2","2"],"type":"basic"},{"payout":"100","symbols":["2","2","2","2"],"type":"basic"},{"payout":"1000","symbols":["2","2","2","2","2"],"type":"basic"},{"payout":"20","symbols":["3","3","3"],"type":"basic"},{"payout":"50","symbols":["3","3","3","3"],"type":"basic"},{"payout":"500","symbols":["3","3","3","3","3"],"type":"basic"},{"payout":"10","symbols":["4","4","4"],"type":"basic"},{"payout":"30","symbols":["4","4","4","4"],"type":"basic"},{"payout":"400","symbols":["4","4","4","4","4"],"type":"basic"},{"payout":"10","symbols":["5","5","5"],"type":"basic"},{"payout":"30","symbols":["5","5","5","5"],"type":"basic"},{"payout":"400","symbols":["5","5","5","5","5"],"type":"basic"},{"payout":"5","symbols":["6","6","6"],"type":"basic"},{"payout":"25","symbols":["6","6","6","6"],"type":"basic"},{"payout":"200","symbols":["6","6","6","6","6"],"type":"basic"},{"payout":"5","symbols":["7","7","7"],"type":"basic"},{"payout":"20","symbols":["7","7","7","7"],"type":"basic"},{"payout":"100","symbols":["7","7","7","7","7"],"type":"basic"},{"payout":"5","symbols":["8","8","8"],"type":"basic"},{"payout":"20","symbols":["8","8","8","8"],"type":"basic"},{"payout":"100","symbols":["8","8","8","8","8"],"type":"basic"},{"payout":"2","symbols":["9","9"],"type":"scatter"},{"payout":"5","symbols":["9","9","9"],"type":"scatter"},{"payout":"20","symbols":["9","9","9","9"],"type":"scatter"},{"payout":"125","symbols":["9","9","9","9","9"],"type":"scatter"},{"payout":"5","symbols":["10"],"type":"basic"},{"payout":"10","symbols":["10","10"],"type":"basic"},{"payout":"30","symbols":["10","10","10"],"type":"basic"},{"payout":"1","symbols":["11"],"type":"basic"},{"payout":"2","symbols":["11","11"],"type":"basic"},{"payout":"3","symbols":["11","11","11"],"type":"basic"},{"payout":"4","symbols":["11","11","11","11"],"type":"basic"},{"payout":"5","symbols":["11","11","11","11","11"],"type":"basic"},{"payout":"6","symbols":["11","11","11","11","11","11"],"type":"basic"},{"payout":"7","symbols":["11","11","11","11","11","11","11"],"type":"basic"},{"payout":"8","symbols":["11","11","11","11","11","11","11","11"],"type":"basic"},{"payout":"9","symbols":["11","11","11","11","11","11","11","11","11"],"type":"basic"},{"payout":"10","symbols":["11","11","11","11","11","11","11","11","11","11"],"type":"basic"},{"payout":"2","symbols":["12"],"type":"basic"},{"payout":"4","symbols":["12","12"],"type":"basic"},{"payout":"6","symbols":["12","12","12"],"type":"basic"},{"payout":"8","symbols":["12","12","12","12"],"type":"basic"},{"payout":"10","symbols":["12","12","12","12","12"],"type":"basic"},{"payout":"12","symbols":["12","12","12","12","12","12"],"type":"basic"},{"payout":"14","symbols":["12","12","12","12","12","12","12"],"type":"basic"},{"payout":"16","symbols":["12","12","12","12","12","12","12","12"],"type":"basic"},{"payout":"3","symbols":["13"],"type":"basic"},{"payout":"6","symbols":["13","13"],"type":"basic"},{"payout":"9","symbols":["13","13","13"],"type":"basic"},{"payout":"12","symbols":["13","13","13","13"],"type":"basic"}],"initialSymbols":[["6","2","4","5","4"],["7","8","0","7","2"],["8","6","7","8","6"]]},"jackpotsEnabled":"true","gameModes":"[]" '.$restoreString.'}}';
                        break;
                    case 'BalanceRequest':
                        $result_tmp[] = '{"action":"BalanceResponse","result":"true","sesId":"10000214325","data":{"entries":"0.00","totalAmount":"' . $slotSettings->GetBalance() . '","currency":" "}}';
                        break;
                    case 'RespinRequest':
                    case 'FreeSpinRequest':
                    case 'SpinRequest':
                        $postData['slotEvent'] = 'bet';
                        $bonusMpl = 1;
                        $linesId = [];
                        $linesId[0] = [
                            2, 
                            2, 
                            2, 
                            2, 
                            2
                        ];
                        $linesId[1] = [
                            1, 
                            1, 
                            1, 
                            1, 
                            1
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
                        $linesId[5] = [
                            2, 
                            1, 
                            2, 
                            3, 
                            2
                        ];
                        $linesId[6] = [
                            2, 
                            3, 
                            2, 
                            1, 
                            2
                        ];
                        $linesId[7] = [
                            1, 
                            1, 
                            2, 
                            3, 
                            3
                        ];
                        $linesId[8] = [
                            3, 
                            3, 
                            2, 
                            1, 
                            1
                        ];
                        $linesId[9] = [
                            1, 
                            2, 
                            1, 
                            2, 
                            1
                        ];
                        $linesId[10] = [
                            3, 
                            2, 
                            3, 
                            2, 
                            3
                        ];
                        $linesId[11] = [
                            2, 
                            1, 
                            1, 
                            1, 
                            2
                        ];
                        $linesId[12] = [
                            1, 
                            3, 
                            3, 
                            3, 
                            1
                        ];
                        $linesId[13] = [
                            1, 
                            2, 
                            2, 
                            2, 
                            1
                        ];
                        $linesId[14] = [
                            3, 
                            2, 
                            2, 
                            2, 
                            3
                        ];
                        $linesId[15] = [
                            2, 
                            2, 
                            1, 
                            2, 
                            2
                        ];
                        $linesId[16] = [
                            2, 
                            2, 
                            3, 
                            2, 
                            2
                        ];
                        $linesId[17] = [
                            1, 
                            3, 
                            1, 
                            3, 
                            1
                        ];
                        $linesId[18] = [
                            3, 
                            1, 
                            3, 
                            1, 
                            3
                        ];
                        $linesId[19] = [
                            3, 
                            1, 
                            2, 
                            1, 
                            3
                        ];
                        $lines = 30;
                        $betLine = $postData['data']['coin'] * $postData['data']['bet'];
                        $slotSettings->SetGameData($slotSettings->slotId . 'BetLine', $postData['data']['bet']);
                        $allbet = $betLine * $lines;
                        if( !isset($postData['slotEvent']) ) 
                        {
                            $postData['slotEvent'] = 'bet';
                        }
                        if( $reqId == 'FreeSpinRequest' || $reqId == 'RespinRequest' ) 
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
                            $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'BonusSymbol', -1);
                            $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreeBalance', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreeStartWin', 0);
                        }
                        else
                        {
                            $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);
                            $bonusMpl = $slotSettings->slotFreeMpl;
                        }
                        $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $lines);
                        $winType = $winTypeTmp[0];
                        $spinWinLimit = $winTypeTmp[1];
                        if($this->debug && $postData['slotEvent'] != 'freespin')
                        {
                            $winType = 'bonus';
                        }
                            
                        $minLineWins = [];
                        $minTotalWin = -1;
                        $minReels = [];
                        $spinAcquired = false;

                        for( $i = 0; $i <= 500; $i++ ) 
                        {
                            $totalWin = 0;
                            $lineWins = [];
                            $cWins = array_fill(0, count($linesId), 0);
                            $wild = ['0', '1'];
                            if($postData['slotEvent'] == 'freespin')
                            {
                                $cBank = $allbet * 20 - $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin');
                                $spinsLeft = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') -  $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                                if($spinsLeft == 0)
                                    $spinsLeft = 1;
                                $spinWinLimit = $cBank / $spinsLeft;
                            }
                            $scatter = '9';
                            $reels = $slotSettings->GetReelStrips($winType, $postData['slotEvent']);                            
                            if( $reqId == 'FreeSpinRequest' ) 
                            {
                                $indices = [1,1,2,2,2,3,3,3,3,3,3,3,3,3,3,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5];
                                $rreel = $indices[rand(0, count($indices) - 1)];
                                $reels['reel' . $rreel][0] = 1;
                                $reels['reel' . $rreel][1] = 1;
                                $reels['reel' . $rreel][2] = 1;
                            }
                            else if($reqId == 'RespinRequest')
                            {
                                $lastReels = $slotSettings->GetGameData($slotSettings->slotId . 'LastReels');
                                for($r = 1; $r <= 5; $r++)
                                {
                                    if(in_array($scatter, $lastReels['reel'.$r]))
                                    {
                                        $reels['reel'.$r] = $lastReels['reel'.$r];
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
                                            if( in_array($s[0], $wild) || in_array($s[1], $wild) ) 
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
                                            if( in_array($s[0], $wild) || in_array($s[1], $wild) || in_array($s[2], $wild) ) 
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
                                           
                                            if( in_array($s[0], $wild) || in_array($s[1], $wild) || in_array($s[2], $wild) || in_array($s[3], $wild) ) 
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
                                            if( in_array($s[0], $wild) || in_array($s[1], $wild) || in_array($s[2], $wild) || in_array($s[3], $wild) || in_array($s[4], $wild) ) 
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
                            $scattersWinB = 0;
                            $scattersPos = [];
                            $scattersStr = '';
                            $scattersCount = 0;
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
                            if($scattersCount > 5)
                                continue;
                            if($scattersCount > 2 && $winType != 'bonus')
                                continue;
                            $scattersWin = $slotSettings->Paytable['SYM_' . $scatter][$scattersCount] * $betLine * $bonusMpl;
                            $gameState = 'Ready';
                            
                            if($scattersCount == 2)
                            {
                                if($postData['slotEvent'] != 'freespin')
                                {
                                    $gameState = 'ReSpins';
                                    $scw = '{"type":"Bonus","bonusName":"ReSpins","params":{"reSpins":"1"},"wonSymbols":[' . implode(',', $scattersPos) . ']}';
                                    array_push($lineWins, $scw);
                                }                                
                                $scw = '{"type":"WinAmount","amount":"' . $slotSettings->FormatFloat($scattersWin) . '","wonSymbols":[' . implode(',', $scattersPos) . ']}';
                                array_push($lineWins, $scw);
                            }
                            else if( $scattersCount >= 3) 
                            {
                                $gameState = 'PickBonus';
                                $scw = '{"type":"Bonus","bonusName":"PickBonus","params":{"fields":"25","freeSpins":"8"},"amount":"' . $slotSettings->FormatFloat($scattersWin) . '","wonSymbols":[' . implode(',', $scattersPos) . ']}';
                                array_push($lineWins, $scw);
                            }
                            $totalWin += ($scattersWin + $scattersWinB);
                            if($minTotalWin == -1 || ($totalWin > 0 && $totalWin < $minTotalWin))
                            {
                                $minLineWins = $lineWins;
                                $minReels = $reels;
                                $minTotalWin = $totalWin;
                                $minScattersCount = $scattersCount;
                            }

                            if($this->debug)
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
                        if($scattersCount == 2)
                        {
                            $slotSettings->SetGameData($slotSettings->slotId . 'LastReels', $reels);
                            $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', $totalWin);
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
                                $slotSettings->SetGameData($slotSettings->slotId . 'Picks', $scattersCount);
                                $slotSettings->SetGameData($slotSettings->slotId . 'TotalPicks', $scattersCount);
                                $slotSettings->SetGameData($slotSettings->slotId . 'BonusState', 2);
                                $slotSettings->SetGameData($slotSettings->slotId . 'SelectedItems', []);
                                $slotSettings->SetGameData($slotSettings->slotId . 'Items', []);
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
                        $slotSettings->SaveLogReport($response, $allbet, $reportWin, $postData['slotEvent']);
                        if( $postData['slotEvent'] == 'freespin' ) 
                        {
                            $bonusWin0 = $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin');
                            if($reqId == 'FreeSpinRequest')
                            {                                
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
                                $result_tmp[] = '{"action":"RespinResponse","result":"true","sesId":"10000228087","data":{"reSpinRemain":"0","reSpinTotal":"1", "state":"' . $gameState . '"' . $winString . ',"spinResult":{"type":"SpinResult","rows":[' . $symb . ']},"totalBonusWin":"' . $slotSettings->FormatFloat($bonusWin0) . '"}}';
                            }
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
                DB::commit();
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
            
        }
    }

}
