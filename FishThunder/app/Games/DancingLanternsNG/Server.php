<?php 
namespace VanguardLTE\Games\DancingLanternsNG
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
                                if($slotSettings->GetGameData($slotSettings->slotId . 'BonusTotalPickCount') > $slotSettings->GetGameData($slotSettings->slotId . 'BonusPickCount'))
                                {
                                    $totalPick = $slotSettings->GetGameData($slotSettings->slotId . 'BonusTotalPickCount');
                                    $picked = $slotSettings->GetGameData($slotSettings->slotId . 'BonusPickCount');
                                    $freeSpinWin = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                                    $restoreString = ',"restoredGameCode":"291","lastResponse":{"params":{"totalPicks":"'.$totalPick.'","picked":"'.$picked.'","freeSpinWin":"'.$freeSpinWin.'"},"state":"PickBonus","spinResult":{"type":"SpinResult","rows":[["10","8","7","5","8"],["10","8","7","1","8"],["10","7","7","4","8"],["1","9","1","4","8"]]},"LanternSymbol":[{"wonSymbols":[["0","3"],["2","3"],["1","4"]],"bet":"20","coinValue":"0.01"}],"LanternExtraSymbol":"","type":"Bonus"}';
                                }
                                else if( $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') < $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') ) 
                                {
                                    $fBonusWin = $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin');
                                    $fTotal = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                                    $fCurrent = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                                    $fRemain = $fTotal - $fCurrent;
                                    
                                    $restoreString = ',"restoredGameCode":"291","lastResponse":{"spinResult":{"type":"SpinResult","rows":[' . $rp2 . ']},"freeSpinsTotal":"' . $fTotal . '","freeSpinRemain":"' . $fRemain . '","totalBonusWin":"' . $fBonusWin . '","state":"FreeSpins"}';
                                }
                                
                                $result_tmp[0] = '{"action":"AuthResponse","result":"true","sesId":"10000034253","data":{"snivy":"proxy v11.16.82 (API v4.16)","bets":["1","2","3","4","5","10","15","20","25"],"coinValues":["0.01"],"betMultiplier":"0.4000000","defaultCoinValue":"0.01","defaultBet":"20","jackpotsEnabled":"false","defaultLines":["0","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","21","22","23","24","25","26","27","28","29","30","31","32","33","34","35","36","37","38","39","40","41","42","43","44","45","46","47","48","49"],"supportedFeatures":["Offers","Jackpots","InstantJackpots","SweepStakes","PaidJackpots"],"sessionId":"10000034253","gameParameters":{"availableLines":[["1","1","1","1","1"],["2","2","2","2","2"],["0","0","0","0","0"],["3","3","3","3","3"],["1","2","3","2","1"],["2","1","0","1","2"],["1","0","0","0","1"],["2","3","3","3","2"],["0","1","2","3","3"],["3","2","1","0","0"],["1","1","2","1","1"],["2","2","1","2","2"],["0","0","1","0","0"],["3","3","2","3","3"],["1","1","2","2","1"],["2","2","1","1","2"],["0","0","0","1","0"],["3","3","3","2","3"],["0","1","2","1","0"],["3","2","1","2","3"],["0","0","1","1","0"],["3","3","2","2","3"],["0","1","0","1","0"],["3","2","3","2","3"],["1","2","2","1","1"],["2","1","1","2","2"],["1","1","0","1","1"],["2","2","3","2","2"],["1","0","1","0","1"],["2","3","2","3","2"],["0","1","1","1","0"],["3","2","2","2","3"],["1","2","1","0","1"],["2","1","2","3","2"],["3","3","2","1","0"],["0","0","1","2","3"],["0","1","1","0","0"],["3","2","2","3","3"],["0","0","2","0","0"],["3","3","1","3","3"],["0","1","0","0","0"],["3","2","3","3","3"],["1","2","2","2","1"],["2","1","1","1","2"],["1","2","1","2","1"],["2","1","2","1","2"],["1","1","3","1","1"],["2","2","0","2","2"],["1","0","1","2","1"],["2","3","2","1","2"]],"rtp":"0.00","initialSymbols":[["0","1","2","3","4"],["5","6","7","8","9"],["10","11","7","4","8"],["1","9","1","4","8"]],"payouts":[{"payout":"2","symbols":["100","100"],"type":"basic"},{"payout":"4","symbols":["100","100","100"],"type":"basic"},{"payout":"20","symbols":["100","100","100","100"],"type":"basic"},{"payout":"120","symbols":["100","100","100","100","100"],"type":"basic"},{"payout":"2","symbols":["102","102"],"type":"basic"},{"payout":"6","symbols":["102","102","102"],"type":"basic"},{"payout":"20","symbols":["102","102","102","102"],"type":"basic"},{"payout":"120","symbols":["102","102","102","102","102"],"type":"basic"},{"payout":"4","symbols":["103","103","103"],"type":"basic"},{"payout":"12","symbols":["103","103","103","103"],"type":"basic"},{"payout":"40","symbols":["103","103","103","103","103"],"type":"basic"},{"payout":"4","symbols":["104","104","104"],"type":"basic"},{"payout":"12","symbols":["104","104","104","104"],"type":"basic"},{"payout":"40","symbols":["104","104","104","104","104"],"type":"basic"},{"payout":"4","symbols":["105","105","105"],"type":"basic"},{"payout":"8","symbols":["105","105","105","105"],"type":"basic"},{"payout":"30","symbols":["105","105","105","105","105"],"type":"basic"},{"payout":"4","symbols":["106","106","106"],"type":"basic"},{"payout":"8","symbols":["106","106","106","106"],"type":"basic"},{"payout":"30","symbols":["106","106","106","106","106"],"type":"basic"},{"payout":"4","symbols":["107","107","107"],"type":"basic"},{"payout":"8","symbols":["107","107","107","107"],"type":"basic"},{"payout":"30","symbols":["107","107","107","107","107"],"type":"basic"},{"payout":"2","symbols":["108","108","108"],"type":"basic"},{"payout":"6","symbols":["108","108","108","108"],"type":"basic"},{"payout":"14","symbols":["108","108","108","108","108"],"type":"basic"},{"payout":"2","symbols":["109","109","109"],"type":"basic"},{"payout":"6","symbols":["109","109","109","109"],"type":"basic"},{"payout":"14","symbols":["109","109","109","109","109"],"type":"basic"},{"payout":"2","symbols":["110","110","110"],"type":"basic"},{"payout":"6","symbols":["110","110","110","110"],"type":"basic"},{"payout":"14","symbols":["110","110","110","110","110"],"type":"basic"},{"payout":"2","symbols":["200","200"],"type":"basic"},{"payout":"4","symbols":["200","200","200"],"type":"basic"},{"payout":"20","symbols":["200","200","200","200"],"type":"basic"},{"payout":"100","symbols":["200","200","200","200","200"],"type":"basic"},{"payout":"2","symbols":["202","202"],"type":"basic"},{"payout":"6","symbols":["202","202","202"],"type":"basic"},{"payout":"20","symbols":["202","202","202","202"],"type":"basic"},{"payout":"100","symbols":["202","202","202","202","202"],"type":"basic"},{"payout":"4","symbols":["203","203","203"],"type":"basic"},{"payout":"12","symbols":["203","203","203","203"],"type":"basic"},{"payout":"40","symbols":["203","203","203","203","203"],"type":"basic"},{"payout":"4","symbols":["204","204","204"],"type":"basic"},{"payout":"12","symbols":["204","204","204","204"],"type":"basic"},{"payout":"40","symbols":["204","204","204","204","204"],"type":"basic"},{"payout":"4","symbols":["205","205","205"],"type":"basic"},{"payout":"8","symbols":["205","205","205","205"],"type":"basic"},{"payout":"30","symbols":["205","205","205","205","205"],"type":"basic"},{"payout":"4","symbols":["206","206","206"],"type":"basic"},{"payout":"8","symbols":["206","206","206","206"],"type":"basic"},{"payout":"30","symbols":["206","206","206","206","206"],"type":"basic"},{"payout":"4","symbols":["207","207","207"],"type":"basic"},{"payout":"8","symbols":["207","207","207","207"],"type":"basic"},{"payout":"30","symbols":["207","207","207","207","207"],"type":"basic"},{"payout":"6","symbols":["208","208","208","208"],"type":"basic"},{"payout":"14","symbols":["208","208","208","208","208"],"type":"basic"},{"payout":"6","symbols":["209","209","209","209"],"type":"basic"},{"payout":"14","symbols":["209","209","209","209","209"],"type":"basic"},{"payout":"6","symbols":["210","210","210","210"],"type":"basic"},{"payout":"14","symbols":["210","210","210","210","210"],"type":"basic"}],"params":""},"gameModes":"" '.$restoreString.'}}';
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
                            case 'FreeSpinRequest':
                            case 'SpinRequest':
                                $postData['slotEvent'] = 'bet';
                                $bonusMpl = 1;
                                $linesId = $slotSettings->GetPayLines();                                
                                $lines = 20;
                                $betLine = $postData['data']['coin'] * $postData['data']['bet'];
                                $slotSettings->SetGameData($slotSettings->slotId . 'BetLine', $postData['data']['bet']);
                                $allbet = $betLine * $lines;
                                if($postData['data']['extraBet'] == 1)
                                    $allbet *= 2;
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
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BonusSymbol', -1);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeBalance', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeStartWin', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'WildsArr', []);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'GameState', 'Spin');                        
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreespinMultiplier', 1);
                                }
                                else
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);
                                    $bonusMpl = $slotSettings->GetGameData($slotSettings->slotId . 'FreespinMultiplier');
                                }
                                $balance = sprintf('%01.2f', $slotSettings->GetBalance());
                                $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $lines);
                                $winType = $winTypeTmp[0];
                                $spinWinLimit = $winTypeTmp[1];
                                if($debug && $postData['slotEvent'] != 'freespin')
                                    $winType = 'bonus';
                                $spinAcquired = false;

                                $minLineWins = [];
                                $minTotalWin = -1;
                                $minReels = [];
                                $minReels0 = [];
                                $minLanternSymbols = [];
                                $minLanternExtraSymbols = [];

                                $spinAcquired = false;
                                $wild = ['0'];
                                $scatter = '1';

                                for( $i = 0; $i <= 300; $i++ ) 
                                {
                                    $totalWin = 0;
                                    $lineWins = [];
                                    $cWins = array_fill(0, 50, 0);
                                    $lanternExtraSymbols = [];
                                                                  
                                    $reels = $slotSettings->GetReelStrips($winType, $postData['slotEvent']);
                                    $reels0 = $reels;
                                    $lanternSymbols = $slotSettings->GetGameData($slotSettings->slotId . 'LanternSymbols');
                                    if($lanternSymbols == 0)
                                        $lanternSymbols = [];

                                    for($l = 0; $l < count($lanternSymbols); $l++)
                                        $lanternSymbols[$l][1]--;
                                    if(count($lanternSymbols) > 0)
                                    {
                                        for($l = count($lanternSymbols) - 1; $l >= 0; $l--)
                                        {
                                            if($lanternSymbols[$l][1] < 0)
                                            array_splice($lanternSymbols, $l, 1);
                                        }                                        
                                    }
                                    
                                    //generate new lanterns
                                    if(count($lanternSymbols) == 0)
                                    {
                                        for($l = 0; $l < 5; $l++)
                                        {
                                            $c = 4;
                                            if ($postData['data']['extraBet'] == 1)
                                                $c = rand(1, 3);
                                            if(rand(0, 100) < 10)
                                            {
                                                $isExists = false;
                                                foreach($lanternSymbols as $symbol)
                                                {
                                                    if($symbol[0] == $l && $symbol[1] == $c)
                                                    {
                                                        $isExists = true;
                                                        break;
                                                    }
                                                }
                                                if(!$isExists)
                                                    $lanternSymbols[] = [$l, $c];
                                            }
                                        }
                                    }

                                    //replace lantern symbols with wild
                                    foreach($lanternSymbols as $symbol)
                                    {
                                        if($symbol[1] >= 0 && $symbol[1] < 4)
                                        {
                                            if($reels0['reel'.($symbol[0]+1)][$symbol[1]] == '2')
                                            {
                                                $lanternExtraSymbols[] = $symbol;
                                                for($r = $symbol[0]-1; $r <= $symbol[0]+1; $r++)
                                                    for($c = $symbol[1]-1; $c <= $symbol[1]+1; $c++)
                                                    { 
                                                        if(isset($reels0['reel'.($r+1)]))
                                                            if(isset($reels0['reel'.($r+1)][$c]))
                                                                $reels['reel'.($r+1)][$c] = $wild[0];
                                                    }
                                            }
                                            $reels['reel'.($symbol[0]+1)][$symbol[1]] = $wild[0];
                                        }
                                    }

                                    for( $k = 0; $k < 50; $k++ ) 
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
                                    for( $r = 1; $r <= 5; $r++ ) 
                                    {
                                        $isScat = false;
                                        for( $p = 0; $p <= 3; $p++ ) 
                                        {
                                            if($isScat)
                                            {
                                                break;
                                            }
                                            if( $reels0['reel' . $r][$p] == $scatter ) 
                                            {
                                                $scattersCount++;
                                                $scattersPos[] = [($r - 1), $p];
                                                $isScat = true;                                                
                                            }                                            
                                        }
                                    }
                                    if( $scattersCount >= 3 && $winType != 'bonus')
                                        continue;
                                    $scattersWin = $slotSettings->Paytable['SYM_' . $scatter][$scattersCount] * $betLine;
                                    $gameState = 'Ready';                                  
                                    if( $scattersCount >= 3 )
                                    {
                                        $scw = [
                                            'amount' => number_format($scattersWin, 2),
                                            'bonusName' => 'PickBonus',
                                            'params' => [
                                                'fields' => '20'
                                            ],
                                            'type' => 'Bonus',
                                            'wonSymbols' => $scattersPos
                                        ];
                                        $gameState = 'PickBonus';
                                        array_push($lineWins, json_encode($scw));
                                    }
                                    
                                    $totalWin += $scattersWin;
                                    
                                    if($minTotalWin == -1 || ($totalWin > 0 && $totalWin < $minTotalWin))
                                    {
                                        $minLineWins = $lineWins;
                                        $minReels = $reels;
                                        $minReels0 = $reels0;
                                        $minTotalWin = $totalWin;
                                        $minScattersCount = $scattersCount;
                                        $minLanternSymbols = $lanternSymbols;
                                        $minLanternExtraSymbols = $lanternExtraSymbols;
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
                                
                                if($totalWin > 0 && !$spinAcquired)
                                {
                                    $lineWins = $minLineWins;
                                    $reels = $minReels;
                                    $totalWin = $minTotalWin;
                                    $scattersCount = $minScattersCount;
                                    $lanternSymbols = $minLanternSymbols;
                                    $lanternExtraSymbols = $minLanternExtraSymbols;
                                    $reels0 = $minReels0;
                                }

                                // if(count($lanternExtraSymbols) > 0)
                                // {
                                //     if($postData['slotEvent'] == 'freespin')
                                //         $ls = '{"type": "Bonus", "bonusName": "LanternFreeSpinSymbol", "amount": "0.00", "wonSymbols": '.json_encode($lanternExtraSymbols).'}';
                                //     else
                                //         $ls = '{"type": "Bonus", "bonusName": "LanternExtraSymbol", "amount": "0.00", "wonSymbols": '.json_encode($lanternExtraSymbols).'}';
                                //     array_push($lineWins, $ls);
                                // }

                                if(count($lanternSymbols) > 0)
                                {
                                    $tmpLanternSymbols = [];
                                    // foreach($lanternSymbols as $symbol) 
                                    // {
                                    //     $isExist = false;
                                    //     foreach($lanternExtraSymbols as $ext)
                                    //     {
                                    //         if($ext[0] == $symbol[0] && $ext[1] == $symbol[1])
                                    //         {
                                    //             $isExist = true;
                                    //             break;
                                    //         }
                                    //     }
                                    //     if(!$isExist)
                                    //         $tmpLanternSymbols[] = $symbol;
                                    // }
                                    if($postData['slotEvent'] == 'freespin')
                                        $ls = '{"type":"Bonus","bonusName":"LanternFreeSpinSymbol","amount":"0.00","wonSymbols":'.json_encode($lanternSymbols).'}';
                                    else
                                        $ls = '{"type":"Bonus","bonusName":"LanternSymbol","amount":"0.00","wonSymbols":'.json_encode($lanternSymbols).'}';
                                    array_push($lineWins, $ls);
                                }

                                
                                $slotSettings->SetGameData($slotSettings->slotId . 'LanternSymbols', $lanternSymbols);

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
                                else
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $totalWin);
                                }
                                if( $scattersCount >= 3 ) 
                                {
                                    // if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') > 0 ) 
                                    // {
                                    //     $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') + $slotSettings->slotFreeCount);
                                    // }
                                    // else
                                    {
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeStartWin', $totalWin);
                                        $pickCount = 5;
                                        if($scattersCount == 4)
                                            $pickCount = 10;
                                        else if($scattersCount == 5)
                                            $pickCount = 20;
                                        $slotSettings->SetGameData($slotSettings->slotId . 'BonusTotalPickCount', $pickCount);
                                        $slotSettings->SetGameData($slotSettings->slotId . 'BonusPickCount', 0);
                                        $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', $totalWin);
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                                        $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
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
                                    $winString = ',"slotWin":{"totalWin":"' . $totalWin . '","lineWinAmounts":[],"canGamble":"false"}';
                                }
                                $response = '{"responseEvent":"spin","responseType":"' . $postData['slotEvent'] . '","serverResponse":{"BonusSymbol":' . $slotSettings->GetGameData($slotSettings->slotId . 'BonusSymbol') . ',"slotLines":' . $lines . ',"slotBet":' . $betLine . ',"totalFreeGames":' . $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') . ',"currentFreeGames":' . $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') . ',"Balance":' . $slotSettings->GetBalance() . ',"afterBalance":' . $slotSettings->GetBalance() . ',"bonusWin":' . $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin') . ',"freeStartWin":' . $slotSettings->GetGameData($slotSettings->slotId . 'FreeStartWin') . ',"totalWin":' . $totalWin . ',"winLines":[],"bonusInfo":[],"Jackpots":' . $jsJack . ',"reelsSymbols":' . $jsSpin . '}}';
                                $symb1 = '["' . $reels['reel1'][0] . '","' . $reels['reel2'][0] . '","' . $reels['reel3'][0] . '","' . $reels['reel4'][0] . '","' . $reels['reel5'][0] . '"],["' . $reels['reel1'][1] . '","' . $reels['reel2'][1] . '","' . $reels['reel3'][1] . '","' . $reels['reel4'][1] . '","' . $reels['reel5'][1] . '"],["' . $reels['reel1'][2] . '","' . $reels['reel2'][2] . '","' . $reels['reel3'][2] . '","' . $reels['reel4'][2] . '","' . $reels['reel5'][2] . '"],["' . $reels['reel1'][3] . '","' . $reels['reel2'][3] . '","' . $reels['reel3'][3] . '","' . $reels['reel4'][3] . '","' . $reels['reel5'][3] . '"]';
                                $symb = '["' . $reels0['reel1'][0] . '","' . $reels0['reel2'][0] . '","' . $reels0['reel3'][0] . '","' . $reels0['reel4'][0] . '","' . $reels0['reel5'][0] . '"],["' . $reels0['reel1'][1] . '","' . $reels0['reel2'][1] . '","' . $reels0['reel3'][1] . '","' . $reels0['reel4'][1] . '","' . $reels0['reel5'][1] . '"],["' . $reels0['reel1'][2] . '","' . $reels0['reel2'][2] . '","' . $reels0['reel3'][2] . '","' . $reels0['reel4'][2] . '","' . $reels0['reel5'][2] . '"],["' . $reels0['reel1'][3] . '","' . $reels0['reel2'][3] . '","' . $reels0['reel3'][3] . '","' . $reels0['reel4'][3] . '","' . $reels0['reel5'][3] . '"]';
                                
                                if($postData['slotEvent'] == 'freespin')
                                    $allbet = 0;
                                $slotSettings->SaveLogReport($response, $allbet, $reportWin, $postData['slotEvent']);
                                $slotSettings->SetGameData($slotSettings->slotId . 'Reels', $reels);
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
                                    $result_tmp[] = '{"action":"FreeSpinResponse","result":"true","sesId":"10000228087","data":{"state":"' . $gameState . '"' . $winString . ',"spinResult":{"type":"SpinResult","rows":[' . $symb . ']},  "spinResultStage2":{"type":"SpinResult","rows":[' . $symb1 . ']}, "totalBonusWin":"' . $slotSettings->FormatFloat($bonusWin0) . '","freeSpinRemain":"' . $freeSpinRemain . '","freeSpinsTotal":"' . $freeSpinsTotal . '"}}';
                                }
                                else
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'initialSymbols', $symb);
                                    $result_tmp[] = '{"action":"SpinResponse","result":"true","sesId":"10000373695","data":{"spinResult":{"type":"SpinResult","rows":[' . $symb . ']}, "spinResultStage2":{"type":"SpinResult","rows":[' . $symb1 . ']} ' . $winString . ',"state":"' . $gameState . '"}}';
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
