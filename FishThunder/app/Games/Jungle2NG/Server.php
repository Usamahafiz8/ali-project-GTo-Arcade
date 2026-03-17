<?php 
namespace VanguardLTE\Games\Jungle2NG
{

    use Illuminate\Support\Facades\DB;

    set_time_limit(5);
    class Server
    {
        public $debug = false;
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
                        $bonusSymbol = $postData['data']['index'];
                        $slotSettings->SetGameData($slotSettings->slotId . 'BonusSymbol', $bonusSymbol);
                        $selectedItem = (int)$postData['data']['index'];
                        $ClLOmJbJVbjmyHqNurOwcCUCaAiO4 = rand(2, 11);
                        $allItems = [
                            '{"type":"BonusItem","index":"1","value":"' . rand(2, 11) . '","picked":"false"}', 
                            '{"type":"BonusItem","index":"2","value":"' . rand(2, 11) . '","picked":"false"}', 
                            '{"type":"BonusItem","index":"3","value":"' . rand(2, 11) . '","picked":"false"}', 
                            '{"type":"BonusItem","index":"4","value":"' . rand(2, 11) . '","picked":"false"}', 
                            '{"type":"BonusItem","index":"5","value":"' . rand(2, 11) . '","picked":"false"}', 
                            '{"type":"BonusItem","index":"6","value":"' . rand(2, 11) . '","picked":"false"}', 
                            '{"type":"BonusItem","index":"7","value":"' . rand(2, 11) . '","picked":"false"}', 
                            '{"type":"BonusItem","index":"8","value":"' . rand(2, 11) . '","picked":"false"}', 
                            '{"type":"BonusItem","index":"9","value":"' . rand(2, 11) . '","picked":"false"}', 
                            '{"type":"BonusItem","index":"10","value":"' . rand(2, 11) . '","picked":"false"}', 
                            '{"type":"BonusItem","index":"11","value":"' . rand(2, 11) . '","picked":"false"}'
                        ];
                        $allItems[$selectedItem - 1] = '{"type":"BonusItem","index":"' . $selectedItem . '","value":"' . $ClLOmJbJVbjmyHqNurOwcCUCaAiO4 . '","picked":"true"}';
                        $slotSettings->SetGameData($slotSettings->slotId . 'SpecialSym', $ClLOmJbJVbjmyHqNurOwcCUCaAiO4);
                        $result_tmp[] = '{"action":"PickBonusItemResponse","result":"true","sesId":"10000097032","data":{"items":[' . implode(',', $allItems) . '],"state":"FreeSpin","lastPick":"true","canGamble":"false","params":{"freeSpins":"'.$slotSettings->GetGameData($slotSettings->slotId . 'FreeGames').'"},"bonusItem":' . $allItems[$selectedItem - 1] . '}}';
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
                        $lastEvent = $slotSettings->GetHistory();
                        if( $lastEvent != 'NULL' ) 
                        {
                            $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', $lastEvent->serverResponse->bonusWin);
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $lastEvent->serverResponse->totalFreeGames);
                            $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $lastEvent->serverResponse->currentFreeGames);
                            $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $lastEvent->serverResponse->bonusWin);
                            $slotSettings->SetGameData($slotSettings->slotId . 'BonusSymbol', $lastEvent->serverResponse->BonusSymbol);
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreeBalance', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreeStartWin', 0);
                            
                            $reels = $lastEvent->serverResponse->reelsSymbols;
                            $syms = '["' . $reels->reel1[0] . '","' . $reels->reel2[0] . '","' . $reels->reel3[0] . '","' . $reels->reel4[0] . '","' . $reels->reel5[0] . '"],["' . $reels->reel1[1] . '","' . $reels->reel2[1] . '","' . $reels->reel3[1] . '","' . $reels->reel4[1] . '","' . $reels->reel5[1] . '"],["' . $reels->reel1[2] . '","' . $reels->reel2[2] . '","' . $reels->reel3[2] . '","' . $reels->reel4[2] . '","' . $reels->reel5[2] . '"],["' . $reels->reel1[3] . '","' . $reels->reel2[3] . '","' . $reels->reel3[3] . '","' . $reels->reel4[3] . '","' . $reels->reel5[3] . '"]';
                        }
                        else
                        {
                            $syms = '["4","4","7","8","2"],["10","9","9","5","4"],["-1","-1","10","2","8"],["-1","-1","3","11","11"]';
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
                            $restoreString = ',"restoredGameCode":"235","lastResponse":{"spinResult":{"type":"SpinResult","rows":[' . $syms . ']},"freeSpinsTotal":"' . $fTotal . '","freeSpinRemain":"' . $fRemain . '","totalBonusWin":"' . $fBonusWin . '","state":"FreeSpins","expandingSymbols":["1"]}';
                        }
                        $result_tmp[0] = '{"action":"AuthResponse","result":"true","sesId":"10000260512","data":{"snivy":"proxy v6.10.48 (API v4.23)","supportedFeatures":["Offers","Jackpots","InstantJackpots","SweepStakes"],"sessionId":"10000260512","defaultLines":["0","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","21","22","23","24","25","26","27","28","29","30","31","32","33","34","35","36","37","38","39","40","41","42","43","44","45","46","47","48","49","50","51","52","53","54","55","56","57","58","59"],"bets":["1","2","3","4","5","6","10","15"],"betMultiplier":"0.5000000","defaultBet":"1","defaultCoinValue":"0.01","coinValues":["0.01"],"gameParameters":{"availableLines":[["0","0","0","0","0"],["0","0","0","0","1"],["0","0","0","1","0"],["0","0","0","1","2"],["0","0","1","0","0"],["0","0","1","0","1"],["0","0","1","1","1"],["0","0","1","2","1"],["0","0","2","1","0"],["0","0","2","2","2"],["0","0","2","3","2"],["0","0","2","3","3"],["0","0","3","2","3"],["0","0","3","2","1"],["0","0","3","3","3"],["0","1","0","0","0"],["0","1","0","1","0"],["0","1","0","1","2"],["0","1","1","0","0"],["0","1","1","0","1"],["0","1","1","1","2"],["0","1","1","2","3"],["0","1","2","1","0"],["0","1","2","2","1"],["0","1","2","3","2"],["0","1","2","3","3"],["0","1","3","2","1"],["0","1","3","2","3"],["0","1","3","3","2"],["0","1","3","3","3"],["1","0","0","0","0"],["1","0","0","0","1"],["1","0","0","1","0"],["1","0","0","1","2"],["1","0","1","0","0"],["1","0","1","0","1"],["1","0","1","1","2"],["1","0","1","2","3"],["1","0","2","1","0"],["1","0","2","2","1"],["1","0","2","3","2"],["1","0","2","3","3"],["1","0","3","2","1"],["1","0","3","2","3"],["1","0","3","3","3"],["1","1","0","0","0"],["1","1","0","1","0"],["1","1","0","1","2"],["1","1","1","2","3"],["1","1","1","1","1"],["1","1","1","0","1"],["1","1","1","0","0"],["1","1","2","1","2"],["1","1","2","2","2"],["1","1","2","3","2"],["1","1","2","3","3"],["1","1","3","2","3"],["1","1","3","2","1"],["1","1","3","3","2"],["1","1","3","3","3"]],"rtp":"0.00","payouts":[{"payout":"30","symbols":["0","0","0"],"type":"basic"},{"payout":"100","symbols":["0","0","0","0"],"type":"basic"},{"payout":"200","symbols":["0","0","0","0","0"],"type":"basic"},{"payout":"30","symbols":["1","1","1"],"type":"basic"},{"payout":"100","symbols":["1","1","1","1"],"type":"basic"},{"payout":"200","symbols":["1","1","1","1","1"],"type":"basic"},{"payout":"20","symbols":["2","2","2"],"type":"basic"},{"payout":"50","symbols":["2","2","2","2"],"type":"basic"},{"payout":"150","symbols":["2","2","2","2","2"],"type":"basic"},{"payout":"20","symbols":["3","3","3"],"type":"basic"},{"payout":"50","symbols":["3","3","3","3"],"type":"basic"},{"payout":"150","symbols":["3","3","3","3","3"],"type":"basic"},{"payout":"10","symbols":["4","4","4"],"type":"basic"},{"payout":"25","symbols":["4","4","4","4"],"type":"basic"},{"payout":"100","symbols":["4","4","4","4","4"],"type":"basic"},{"payout":"10","symbols":["5","5","5"],"type":"basic"},{"payout":"25","symbols":["5","5","5","5"],"type":"basic"},{"payout":"100","symbols":["5","5","5","5","5"],"type":"basic"},{"payout":"5","symbols":["6","6","6"],"type":"basic"},{"payout":"20","symbols":["6","6","6","6"],"type":"basic"},{"payout":"100","symbols":["6","6","6","6","6"],"type":"basic"},{"payout":"5","symbols":["7","7","7"],"type":"basic"},{"payout":"20","symbols":["7","7","7","7"],"type":"basic"},{"payout":"100","symbols":["7","7","7","7","7"],"type":"basic"},{"payout":"2","symbols":["8","8","8"],"type":"basic"},{"payout":"10","symbols":["8","8","8","8"],"type":"basic"},{"payout":"50","symbols":["8","8","8","8","8"],"type":"basic"},{"payout":"2","symbols":["9","9","9"],"type":"basic"},{"payout":"10","symbols":["9","9","9","9"],"type":"basic"},{"payout":"50","symbols":["9","9","9","9","9"],"type":"basic"},{"payout":"2","symbols":["10","10","10"],"type":"basic"},{"payout":"10","symbols":["10","10","10","10"],"type":"basic"},{"payout":"50","symbols":["10","10","10","10","10"],"type":"basic"},{"payout":"2","symbols":["11","11","11"],"type":"basic"},{"payout":"10","symbols":["11","11","11","11"],"type":"basic"},{"payout":"50","symbols":["11","11","11","11","11"],"type":"basic"}],"initialSymbols":['.$syms.']},"jackpotsEnabled":"true","gameModes":"[]" '.$restoreString.'}}';
                        break;
                    case 'BalanceRequest':
                        $result_tmp[] = '{"action":"BalanceResponse","result":"true","sesId":"10000214325","data":{"entries":"0.00","totalAmount":"' . $slotSettings->GetBalance() . '","currency":" "}}';
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
                        $balance = sprintf('%01.2f', $slotSettings->GetBalance());
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
                        $minfreespinWin = 0;
                        $minScattersCount = 0;
                        
                        $spinAcquired = false;
                        $wild = ['0'];
                        $scatter = '12';
                        $hasFreespinSyms = false;
                        for( $i = 0; $i <= 500; $i++ ) 
                        {
                            $hasFreespinSyms = false;
                            $freespinWin = 0;
                            $totalWin = 0;
                            $lineWins = [];
                            $cWins = array_fill(0, 60, 0);
                            
                            $reels = $slotSettings->GetReelStrips($winType, $postData['slotEvent']);
                            
                            // $reels['reel1'][0] = 11;
                            // $reels['reel1'][1] = 11;
                            // $reels['reel2'][0] = 11;
                            // $reels['reel3'][2] = 0;

                            if( $postData['slotEvent'] == 'freespin' ) 
                            {
                                $specSym = $slotSettings->GetGameData($slotSettings->slotId . 'SpecialSym');
                                $reels['reel1'][0] = $specSym;
                                $reels['reel1'][1] = $specSym;
                                $reels['reel2'][0] = $specSym;
                                $reels['reel2'][1] = $specSym;
                            }
                            for( $k = 0; $k < 60; $k++ ) 
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
                            $scattersWinB = 0;
                            $scattersPos = [];
                            $scattersStr = '';
                            $scattersCount = 0;
                            $bSym = $slotSettings->GetGameData($slotSettings->slotId . 'BonusSymbol');
                            $bSymCnt = 0;
                            for( $r = 1; $r <= 5; $r++ ) 
                            {
                                $isScat = false;
                                for( $p = 0; $p <= 3; $p++ ) 
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
                                for( $p = 0; $p <= 4; $p++ ) 
                                {
                                    if( $reels['reel' . $r][$p] == $bSym ) 
                                    {
                                        $bSymCnt++;
                                        break;
                                    }
                                }
                            }
                            
                            $scattersWin = $slotSettings->Paytable['SYM_' . $scatter][$scattersCount] * $betLine * $lines * $bonusMpl;
                            $gameState = 'Ready';

                            //check freespin mode
                            $controllerSyms = [];
                            $controllerSyms[] = $reels['reel1'][0];
                            $controllerSyms[] = $reels['reel1'][1];
                            $controllerSyms[] = $reels['reel2'][0];
                            $controllerSyms[] = $reels['reel2'][1];
                            $targetSym = '0';
                            foreach($controllerSyms as $sym)
                            {
                                if($sym != '0')
                                {
                                    $targetSym = $sym;
                                    break;
                                }
                            }

                            if( $scattersCount >= 3 ) 
                            {
                                $hasFreespinSyms = true;
                                $freespinWin = 5;
                                if( ($controllerSyms[0] == $targetSym || $controllerSyms[0] == '0') &&
                                    ($controllerSyms[1] == $targetSym || $controllerSyms[1] == '0') &&
                                    ($controllerSyms[2] == $targetSym || $controllerSyms[2] == '0') &&
                                    ($controllerSyms[3] == $targetSym || $controllerSyms[3] == '0'))
                                {
                                    //method 1
                                    // if($targetSym == '0' || $targetSym == '1')
                                    //     continue;
                                    $freespinWin = 20;
                                }

                                $gameState = 'PickBonus';
                                $scw = '{"type":"Bonus","bonusName":"FreeSpins","params":{"freeSpins":"'.$freespinWin.'"},"amount":"' . $slotSettings->FormatFloat($scattersWin) . '","wonSymbols":[' . implode(',', $scattersPos) . ']},{"wonSymbols":"","type":"Bonus","bonusName":"PickBonus","params":{"pickItems":"11"}}';
                                array_push($lineWins, $scw);
                            }
                            else
                            {
                                if($postData['slotEvent'] != 'freespin')
                                {
                                    if( ($controllerSyms[0] == $targetSym || $controllerSyms[0] == '0') &&
                                    ($controllerSyms[1] == $targetSym || $controllerSyms[1] == '0') &&
                                    ($controllerSyms[2] == $targetSym || $controllerSyms[2] == '0') &&
                                    ($controllerSyms[3] == $targetSym || $controllerSyms[3] == '0'))
                                    {
                                        $hasFreespinSyms = true;
                                        if($winType == 'bonus')
                                        {
                                            //method 3
                                            $freespinWin = 5;
                                            $gameState = 'FreeSpins';
                                            $scw = '{"type":"Bonus","bonusName":"FreeSpins","params":{"freeSpins":"'.$freespinWin.'"},"wonSymbols":""}';
                                            $slotSettings->SetGameData($slotSettings->slotId . 'SpecialSym', $targetSym);
                                            array_push($lineWins, $scw);
                                        }                                        
                                    }
                                }
                            }

                            $totalWin += ($scattersWin + $scattersWinB);
                            
                            if($minTotalWin == -1 && !$hasFreespinSyms || ($totalWin > 0 && $totalWin < $minTotalWin))
                            {
                                $minLineWins = $lineWins;
                                $minReels = $reels;
                                $minTotalWin = $totalWin;
                                $minScattersCount = $scattersCount;
                                $minfreespinWin = $freespinWin;
                            }

                            if($this->debug)
                            {
                                $spinAcquired = true;
                                break;
                            }
                            if($totalWin <= $spinWinLimit && (($totalWin > 0 && $winType != 'none') || ($winType == 'bonus' && $freespinWin > 0)))
                            {
                                $spinAcquired = true;
                                if($totalWin < 0.5 * $spinWinLimit && $winType != 'bonus')
                                    $spinAcquired = false;
                                if($spinAcquired)
                                    break;                                        
                            }  
                            else if($winType == 'none' && $totalWin == 0)
                            {
                                $spinAcquired = true;
                                break;
                            }
                        }

                        if(empty($reels) || !$spinAcquired || ($hasFreespinSyms && $winType != 'bonus'))
                        {
                            $lineWins = $minLineWins;
                            $reels = $minReels;
                            $totalWin = $minTotalWin;
                            $scattersCount = $minScattersCount;
                            $freespinWin = $minfreespinWin;
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
                        if( $freespinWin >= 1 ) 
                        {
                            if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') > 0 ) 
                            {
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') + $freespinWin);
                            }
                            else
                            {
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeStartWin', $totalWin);
                                $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', $totalWin);
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $freespinWin);
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
                        $symb = '["' . $reels['reel1'][0] . '","' . $reels['reel2'][0] . '","' . $reels['reel3'][0] . '","' . $reels['reel4'][0] . '","' . $reels['reel5'][0] . '"],["' . $reels['reel1'][1] . '","' . $reels['reel2'][1] . '","' . $reels['reel3'][1] . '","' . $reels['reel4'][1] . '","' . $reels['reel5'][1] . '"],["' . $reels['reel1'][2] . '","' . $reels['reel2'][2] . '","' . $reels['reel3'][2] . '","' . $reels['reel4'][2] . '","' . $reels['reel5'][2] . '"],["' . $reels['reel1'][3] . '","' . $reels['reel2'][3] . '","' . $reels['reel3'][3] . '","' . $reels['reel4'][3] . '","' . $reels['reel5'][3] . '"]';
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
                                $gameParameters = '"gameParameters":{"initialSymbols":[["8","9","12","12","10"],["1","9","7","7","9"],["-1","-1","11","3","12"],["-1","-1","5","4","7"]]},';
                                $winString0 = implode(',', $lineWins);
                                $winString = ',"slotWin":{"lineWinAmounts":[' . $winString0 . '],"totalWin":"' . $slotSettings->FormatFloat($totalWin) . '","canGamble":"false"}';
                            }
                            $result_tmp[] = '{"action":"FreeSpinResponse","result":"true","sesId":"10000228087","data":{' . $gameParameters . '"state":"' . $gameState . '"' . $winString . ',"spinResult":{"type":"SpinResult","rows":[' . $symb . ']},"totalBonusWin":"' . $slotSettings->FormatFloat($bonusWin0) . '","freeSpinRemain":"' . $freeSpinRemain . '","freeSpinsTotal":"' . $freeSpinsTotal . '"}}';
                        }
                        else
                        {
                            $slotSettings->SetGameData($slotSettings->slotId . 'initialSymbols', $symb);
                            $result_tmp[] = '{"action":"SpinResponse","result":"true","sesId":"10000217909","data":{"state":"' . $gameState . '"' . $winString . ',"spinResult":{"type":"SpinResult","rows":[' . $symb . ']}}}';
                        }
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
