<?php 
namespace VanguardLTE\Games\LuxorRelicsNG
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
                                if( $lastEvent != 'NULL' && $lastEvent->serverResponse->bonusQuantities[0]->bonusPlayedCount < $lastEvent->serverResponse->bonusQuantities[0]->bonusTotalCount) 
                                {
                                    $result_tmp[] = '{"action":"CheckBrokenGameResponse","data":{"restoredGameId":373},"result":true,"sesId":"10000205144"}';
                                }
                                else
                                {
                                    $result_tmp[] = '{"action":"CheckBrokenGameResponse","result":"true","sesId":"false","data":{"haveBrokenGame":"false"}}';
                                }
                                break;
                            case 'AuthRequest':                                
                                $basicResponse = '';
                                if($lastEvent != 'NULL' && $lastEvent->serverResponse->bonusQuantities[0]->bonusPlayedCount < $lastEvent->serverResponse->bonusQuantities[0]->bonusTotalCount) 
                                {
                                    $serverResponse = $lastEvent->serverResponse;
                                    $basicData = [
                                        'bonusQuantities' => $serverResponse->bonusQuantities,
                                        'extraWin' => 0,
                                        'current_spin' => $serverResponse->currentSpin,
                                        'nextBonusType'=> $serverResponse->nextBonusType,
                                        'matrix' => $serverResponse->basicMatrix,
                                        'roundId' => 0,
                                        'packID' => 0,
                                        'magic_cells' => $serverResponse->magic_cells,
                                        'series' => $serverResponse->series,
                                        'win' => $serverResponse->win,
                                    ];
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentSpin',  $serverResponse->currentSpin);
                                    if(count($serverResponse->hnlMatrix) > 0)
                                        $basicData['hnlMatrix'] = $serverResponse->hnlMatrix;
                                    $basicResponse = json_encode($basicData);
                                    $gameStates = json_decode('[[{"bet":1,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":2,"current_spin":10,"denominator":0.01,"magic_cells":[[3,0],[3,1],[3,2],[3,3],[1,0],[4,3],[1,3],[0,0],[0,1],[0,3],[1,1]]},{"bet":3,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":4,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":5,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":10,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":15,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":20,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":25,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":30,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":35,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":40,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":50,"current_spin":4,"denominator":0.01,"magic_cells":[]}]]');

                                    $restoreData = json_encode([
                                        'GameStates' => $gameStates,
                                        'bonusResponses' => [
                                            [
                                                'bonusQuantities' => $serverResponse->bonusQuantities,    
                                                'canGamble' => false,
                                                'currentBonusType' => $serverResponse->currentBonusType,
                                                'extraWin' => $serverResponse->extraWin,
                                                'matrix' => $serverResponse->matrix,
                                                'nextBonusType' => $serverResponse->nextBonusType,
                                                'series' => $serverResponse->series,
                                                'win' => $serverResponse->win,                                            
                                            ]                                            
                                        ],
                                        'current_spin' => $serverResponse->currentSpin,
                                        'matrix' => [],
                                        'magic_cells' => $serverResponse->magic_cells,
                                        'packID' => 0,
                                        'recoveryStackSize' => 1
                                    ]);
                                    // $restoreData = '{"GameStates":[[{"bet":1,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":2,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":3,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":4,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":5,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":10,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":15,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":20,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":30,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":40,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":50,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":100,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":200,"current_spin":0,"denominator":0.01,"magic_cells":[]}]],"bonusResponses":[],"current_spin":0,"magic_cells":[],"matrix":[],"packId":0,"recoveryStackSize":0}';                                    
                                }
                                else
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentSpin',  0);
                                    $basicResponse = '{"bonusQuantities":[{"bonusMoreCount":0,"bonusPlayedCount":0,"bonusTotalCount":0,"bonusType":0}],"canGamble":false,"current_spin":0,"extraWin":0,"magic_cells":[],"matrix":[[1,2,5,5],[9,10,0,9],[3,3,3,3],[13,4,7,10],[8,12,13,13]],"nextBonusType":-1,"packID":9,"rolling_matrix":[[0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]],"roundId":0,"series":[],"win":0}';
                                    $restoreData = '{"GameStates":[[{"bet":1,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":2,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":3,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":4,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":5,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":10,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":15,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":20,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":30,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":40,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":50,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":100,"current_spin":0,"denominator":0.01,"magic_cells":[]},{"bet":200,"current_spin":0,"denominator":0.01,"magic_cells":[]}]],"bonusResponses":[],"current_spin":0,"magic_cells":[],"matrix":[],"packId":9,"recoveryStackSize":0}';
                                }
                               
                                $defaultBet = $slotSettings->GetGameData($slotSettings->slotId . 'BetLine');
                                if($defaultBet == 0)
                                    $defaultBet = 1;                                
                                    
                                $result_tmp[0] = '{"action":"AuthResponse","data":{"accountData":{"activeBet":'.$defaultBet.',"activeCoinValue":0.01,"activeLines":50,"availableBets":[1,2,3,4,5,10],"availableCoinValues":[0.01],"availableLines":[50],"balance":'.$slotSettings->GetBalance().',"betMultiplier":1.0,"credits":'.($slotSettings->GetBalance() * 100).',"currency":"","rtp":"0.00","sweepMode":false},"buyItems":[],"gameData":{"basicResponse":'.$basicResponse.',"figures":[{"id":0,"mask":9,"pays":[0,0,0,0,0]},{"id":1,"mask":16,"pays":[0,0,0,0,0]},{"id":2,"mask":2,"pays":[0,0,0,0,100]},{"id":3,"mask":4,"pays":[0,1,10,50,100]},{"id":4,"mask":4,"pays":[0,0,5,20,50]},{"id":5,"mask":4,"pays":[0,0,5,20,50]},{"id":6,"mask":4,"pays":[0,0,3,10,40]},{"id":7,"mask":4,"pays":[0,0,3,10,40]},{"id":8,"mask":4,"pays":[0,0,3,10,40]},{"id":9,"mask":4,"pays":[0,0,3,10,40]},{"id":10,"mask":4,"pays":[0,0,2,8,25]},{"id":11,"mask":4,"pays":[0,0,2,8,25]},{"id":12,"mask":4,"pays":[0,0,1,5,20]},{"id":13,"mask":4,"pays":[0,0,1,5,20]},{"id":14,"mask":4,"pays":[0,0,1,5,20]},{"id":15,"mask":75,"pays":[0,0,0,0,0]},{"id":16,"mask":91,"pays":[0,0,0,0,0]},{"id":17,"mask":0,"pays":[0,0,0,0,0]},{"id":18,"mask":4,"pays":[1]},{"id":19,"mask":4,"pays":[2]},{"id":20,"mask":4,"pays":[3]},{"id":21,"mask":4,"pays":[4]},{"id":22,"mask":4,"pays":[5]},{"id":23,"mask":4,"pays":[6]},{"id":24,"mask":4,"pays":[7]},{"id":25,"mask":4,"pays":[8]},{"id":26,"mask":4,"pays":[9]},{"id":27,"mask":4,"pays":[20]},{"id":28,"mask":4,"pays":[50]},{"id":29,"mask":4,"pays":[0]},{"id":30,"mask":4,"pays":[0]},{"id":31,"mask":4,"pays":[0]},{"id":32,"mask":4,"pays":[10]},{"id":33,"mask":4,"pays":[30]},{"id":34,"mask":4,"pays":[100]},{"id":35,"mask":49209,"pays":[1000]}],"lines":[{"id":0,"positions":[[0,0],[1,0],[2,0],[3,0],[4,0]]},{"id":1,"positions":[[0,1],[1,1],[2,1],[3,1],[4,1]]},{"id":2,"positions":[[0,2],[1,2],[2,2],[3,2],[4,2]]},{"id":3,"positions":[[0,3],[1,3],[2,3],[3,3],[4,3]]},{"id":4,"positions":[[0,2],[1,1],[2,2],[3,1],[4,2]]},{"id":5,"positions":[[0,1],[1,2],[2,1],[3,2],[4,1]]},{"id":6,"positions":[[0,1],[1,0],[2,0],[3,0],[4,1]]},{"id":7,"positions":[[0,2],[1,3],[2,3],[3,3],[4,2]]},{"id":8,"positions":[[0,3],[1,2],[2,3],[3,2],[4,3]]},{"id":9,"positions":[[0,0],[1,1],[2,0],[3,1],[4,0]]},{"id":10,"positions":[[0,3],[1,3],[2,2],[3,3],[4,3]]},{"id":11,"positions":[[0,0],[1,0],[2,1],[3,0],[4,0]]},{"id":12,"positions":[[0,2],[1,2],[2,2],[3,1],[4,2]]},{"id":13,"positions":[[0,1],[1,1],[2,1],[3,2],[4,1]]},{"id":14,"positions":[[0,0],[1,1],[2,2],[3,1],[4,0]]},{"id":15,"positions":[[0,3],[1,2],[2,1],[3,2],[4,3]]},{"id":16,"positions":[[0,3],[1,0],[2,3],[3,0],[4,3]]},{"id":17,"positions":[[0,0],[1,3],[2,0],[3,3],[4,0]]},{"id":18,"positions":[[0,1],[1,2],[2,3],[3,2],[4,1]]},{"id":19,"positions":[[0,2],[1,1],[2,0],[3,1],[4,2]]},{"id":20,"positions":[[0,0],[1,1],[2,2],[3,3],[4,3]]},{"id":21,"positions":[[0,3],[1,2],[2,1],[3,0],[4,0]]},{"id":22,"positions":[[0,0],[1,0],[2,1],[3,2],[4,3]]},{"id":23,"positions":[[0,3],[1,3],[2,2],[3,1],[4,0]]},{"id":24,"positions":[[0,3],[1,3],[2,2],[3,2],[4,2]]},{"id":25,"positions":[[0,0],[1,0],[2,1],[3,1],[4,1]]},{"id":26,"positions":[[0,2],[1,2],[2,1],[3,1],[4,1]]},{"id":27,"positions":[[0,1],[1,1],[2,2],[3,2],[4,2]]},{"id":28,"positions":[[0,2],[1,3],[2,2],[3,1],[4,0]]},{"id":29,"positions":[[0,1],[1,0],[2,1],[3,2],[4,3]]},{"id":30,"positions":[[0,3],[1,2],[2,1],[3,0],[4,1]]},{"id":31,"positions":[[0,0],[1,1],[2,2],[3,3],[4,2]]},{"id":32,"positions":[[0,3],[1,3],[2,0],[3,3],[4,3]]},{"id":33,"positions":[[0,0],[1,0],[2,3],[3,0],[4,0]]},{"id":34,"positions":[[0,1],[1,1],[2,1],[3,2],[4,3]]},{"id":35,"positions":[[0,2],[1,2],[2,2],[3,1],[4,0]]},{"id":36,"positions":[[0,3],[1,3],[2,1],[3,3],[4,3]]},{"id":37,"positions":[[0,0],[1,0],[2,2],[3,0],[4,0]]},{"id":38,"positions":[[0,0],[1,0],[2,0],[3,1],[4,2]]},{"id":39,"positions":[[0,3],[1,3],[2,3],[3,2],[4,1]]},{"id":40,"positions":[[0,0],[1,0],[2,0],[3,1],[4,0]]},{"id":41,"positions":[[0,3],[1,3],[2,3],[3,2],[4,3]]},{"id":42,"positions":[[0,1],[1,0],[2,3],[3,0],[4,1]]},{"id":43,"positions":[[0,2],[1,3],[2,0],[3,3],[4,2]]},{"id":44,"positions":[[0,2],[1,3],[2,2],[3,1],[4,2]]},{"id":45,"positions":[[0,1],[1,0],[2,1],[3,2],[4,1]]},{"id":46,"positions":[[0,1],[1,2],[2,1],[3,0],[4,1]]},{"id":47,"positions":[[0,2],[1,1],[2,2],[3,3],[4,2]]},{"id":48,"positions":[[0,1],[1,0],[2,2],[3,0],[4,1]]},{"id":49,"positions":[[0,2],[1,3],[2,1],[3,3],[4,2]]}],"replay_leaderboard_show_status":false,"replay_status":false,"restoreData":'.$restoreData.'},"gameServerId":"gs-5.121.59-skg-rc-nv-2-10194","jackpotsData":{"enabled":false},"offersData":[],"snivyId":"snivy-30.35.43-rc-skg-0"},"result":true,"sesId":"20001131720"}';
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
                                $allbet = $betLine * 50;
                                if( !isset($postData['slotEvent']) ) 
                                {
                                    $postData['slotEvent'] = 'bet';
                                }
                                if( $reqId == 'GameStartBonusRequest' ) 
                                {
                                    if($postData['data']['bonusType'] == 0)
                                        $postData['slotEvent'] = $HOLDNLINK_TAG;
                                }
                                else if($reqId == 'HoldnLinkRequest')
                                {
                                    $postData['slotEvent'] = $HOLDNLINK_TAG;
                                }

                                $currentSpin = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentSpin');
                                if($currentSpin == 0 || $currentSpin == 10)
                                {
                                    $currentSpin = 1;
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentSpin', 1);
                                }
                                $magicCells = [];
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
                                    $slotSettings->SetGameData($slotSettings->slotId . 'lastHoldnLinkCount', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGamesHoldnLink', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink', 0);

                                    $currentSpin++;
                                    $magicCells = $slotSettings->GetGameData($slotSettings->slotId . 'MagicCells');
                                    if($magicCells == 0)
                                    {
                                        $magicCells = [];
                                        $slotSettings->SetGameData($slotSettings->slotId . 'MagicCells', []);
                                    }
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
                                $minHoldLinkCount = 0;
                                $minHoldnWin = 0;
                                $minMagicCells = [];
                                $minBasicReels = [];

                                $spinAcquired = false;                                
                                $lastHoldnLinkCount = $slotSettings->GetGameData($slotSettings->slotId . 'lastHoldnLinkCount');
                                $sphere = 0;
                                $jackpot1 = 15;
                                $jackpot2 = 16;
                                $wild = [2];
                                $fireSym = 1;
                                
                                for( $i = 0; $i <= 500; $i++ ) 
                                {
                                    $totalWin = 0;
                                    if($postData['slotEvent'] != 'bet')
                                    {
                                        $totalWin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin');
                                    }
                                    $holdnWin = 0;
                                    $lineWins = [];
                                    $magicCells = $slotSettings->GetGameData($slotSettings->slotId . 'MagicCells');
                                    $winsMatrix = [[17, 17, 17, 17],[17, 17, 17, 17],[17, 17, 17, 17],[17, 17, 17, 17],[17, 17, 17, 17]];
                                    $cWins = array_fill(0, $lines, 0);                                    
                                    
                                    $reels = $slotSettings->GetReelStrips($winType, $postData['slotEvent']);
                                    
                                    $basicReels = $reels;
                                    
                                    if($postData['slotEvent'] == $HOLDNLINK_TAG)
                                    {
                                        $lastWinsMatrix = $slotSettings->GetGameData($slotSettings->slotId . 'WinMatrix');
                                        for($r = 0; $r < 5; $r++)
                                            for($c = 0; $c < 4; $c++)
                                            {
                                                if($lastWinsMatrix[$r][$c] != 17)
                                                {
                                                    if ($lastWinsMatrix[$r][$c] == $jackpot1)
                                                        $reels['reel'.($r+1)][$c] = $jackpot1;
                                                    else if ($lastWinsMatrix[$r][$c] == $jackpot2)
                                                        $reels['reel'.($r+1)][$c] = $jackpot2;
                                                    else
                                                        $reels['reel'.($r+1)][$c] = $sphere;
                                                }
                                            }
                                    }
                                    //check sphere value for holdnLink
                                    for($r = 0; $r < 5; $r++)
                                        for($c = 0; $c < 4; $c++)
                                        {
                                            if($reels['reel' .($r+1)][$c] == $sphere)
                                                $winsMatrix[$r][$c] = 17 + rand(1, 5);
                                            if($reels['reel' .($r+1)][$c] == $jackpot1)
                                                 $winsMatrix[$r][$c] = $jackpot1;
                                            if($reels['reel' .($r+1)][$c] == $jackpot2)
                                                 $winsMatrix[$r][$c] = $jackpot2;

                                        }

                                    if($postData['slotEvent'] == $HOLDNLINK_TAG)
                                    {
                                        $lastWinsMatrix = $slotSettings->GetGameData($slotSettings->slotId . 'WinMatrix');
                                        for($r = 0; $r < 5; $r++)
                                            for($c = 0; $c < 4; $c++)
                                            {
                                                if($lastWinsMatrix[$r][$c] != 17)
                                                {
                                                    $winsMatrix[$r][$c] = $lastWinsMatrix[$r][$c];
                                                }
                                            }
                                    }

                                    if( $postData['slotEvent'] != $HOLDNLINK_TAG ) 
                                    {
                                        //check fire frame
                                        $fireSymCnt = 0;
                                        for($r = 0; $r < 5; $r++)
                                            for($c = 0; $c < 4; $c++)
                                            {
                                                if($reels['reel'.($r+1)][$c] == $fireSym)
                                                {
                                                    $index = $r * 4 + $c;
                                                    if(!in_array($index, $magicCells))
                                                    {
                                                        $magicCells[] = $index;
                                                        $fireSymCnt++;
                                                    }
                                                }
                                            }
                                        if($spinWinLimit < 0 && $fireSymCnt > 0 || $fireSymCnt > 2)
                                            continue;
                                        
                                        if($currentSpin == 10)
                                        {
                                            //convert all fire frames into wild on 10th spin
                                            $positions = [];
                                            foreach($magicCells as $cell)
                                            {
                                                $r = (int)($cell / 4);
                                                $c = $cell % 4;
                                                $reels['reel'.($r+1)][$c] = $wild[0];
                                                $positions[] = [$r, $c];
                                            }
                                            $lineWin = ['figureId' => 1, 'lineId' => 255, 'mask' => 4, 'profit' => 0, 'positions' => $positions];
                                            array_push($lineWins, $lineWin);
                                        }

                                        for( $k = 0; $k < $lines; $k++ ) 
                                        {
                                            $lineWin = [];
                                            for( $j = 0; $j < count($slotSettings->SymbolGame); $j++ ) 
                                            {
                                                $csym = $slotSettings->SymbolGame[$j];
                                                if(!isset($slotSettings->Paytable['SYM_' . $csym]) ) 
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

                                    $holdnlinkCount = 0;
                                    $holdnPos = [];
                                    for($r = 0; $r < 5; $r++)
                                        for($c = 0; $c < 4; $c++)
                                        {
                                            if($reels['reel' .($r+1)][$c] == $sphere || $reels['reel' .($r+1)][$c] == $jackpot1 || $reels['reel' .($r+1)][$c] == $jackpot2)
                                            {
                                                $holdnPos[] = [$r, $c];
                                                $holdnlinkCount++;
                                            }
                                        }
                                    if($holdnlinkCount > 11) 
                                        continue;
                                    if($holdnlinkCount > 2)
                                    {
                                        if($lastHoldnLinkCount == 0)
                                        {
                                            if($winType != 'bonus')
                                                continue;
                                            $lineWin = ['figureId' => 0, 'lineId' => 255, 'mask' => 2, 'positions' => $holdnPos, 'profit' => 0];
                                            array_push($lineWins, $lineWin);

                                            //set random hnl balls
                                            $hnlCount = rand(3,5);
                                            $winsMatrix = [[17, 17, 17, 17],[17, 17, 17, 17],[17, 17, 17, 17],[17, 17, 17, 17],[17, 17, 17, 17]];
                                            $hnlMatrix = [];
                                            while(count($hnlMatrix) < $hnlCount)
                                            {
                                                $value = rand(0, 19);
                                                if(!in_array($value, $hnlMatrix))
                                                {
                                                    $hnlMatrix[] = $value;
                                                    $r = (int)($value / 4);
                                                    $c = $value % 4;
                                                    $winsMatrix[$r][$c] = 17 + rand(1,5);
                                                }
                                            }
                                            $holdnlinkCount = $hnlCount;
                                        }
                                        for($r = 0; $r < 5; $r++)
                                            for($c = 0; $c < 4; $c++)
                                            {
                                                $times = $winsMatrix[$r][$c] - 17;
                                                if($winsMatrix[$r][$c] == $jackpot1)
                                                {
                                                    $times = 10; //mini jackpot
                                                }
                                                else if($winsMatrix[$r][$c] == $jackpot2)
                                                {
                                                    $times = 5; //x5 win
                                                }
                                                
                                                if($times != 0)
                                                    $holdnWin += $times * $allbet;
                                            }
                                    }

                                    $totalWin += $holdnWin;

                                    if($minTotalWin == -1 || ($totalWin > 0 && $totalWin < $minTotalWin))
                                    {
                                        $minTotalWin = $totalWin;
                                        $minReels = $reels;
                                        $minLineWins = $lineWins;
                                        $minHoldLinkCount = $holdnlinkCount;
                                        $minWinsMatrix = $winsMatrix;
                                        $minHoldnWin = $holdnWin;
                                        $minMagicCells = $magicCells;
                                        $minBasicReels = $basicReels;
                                    }

                                    if($debug)
                                    {
                                        $spinAcquired = true;
                                        break;
                                    }
                                    if($totalWin <= $spinWinLimit && (($totalWin > 0 && $winType != 'none') || ($winType == 'bonus' && $lastHoldnLinkCount == 0 && $holdnlinkCount > 2)))
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
                                    $holdnlinkCount = $minHoldLinkCount;
                                    $winsMatrix = $minWinsMatrix;
                                    $holdnWin = $minHoldnWin;
                                    $magicCells = $minMagicCells;
                                    $basicReels = $minBasicReels;
                                }

                                $totalWin -= $holdnWin;

                                $reels = $basicReels;
                                $holdnQuantity = [
                                    'bonusMoreCount' => 0,
                                    'bonusPlayedCount' => 0,
                                    'bonusTotalCount' => 0,
                                    'bonusType' => 0
                                ];

                                $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $totalWin);
                                $nextBonusType = -1;

                                //check holdnlink
                                $triggerHoldn = false;
                                if( $holdnlinkCount > 2)
                                {
                                    if($holdnlinkCount > $lastHoldnLinkCount)
                                    {
                                        if($lastHoldnLinkCount == 0)
                                        {
                                            //triggering holdnlink
                                            $triggerHoldn = true;
                                            $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $totalWin);
                                        }
                                        $holdnQuantity['bonusMoreCount'] = 3;
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeGamesHoldnLink', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink') + 3);
                                    }
                                    $slotSettings->SetGameData($slotSettings->slotId . 'lastHoldnLinkCount', $holdnlinkCount);

                                    $holdnQuantity['bonusPlayedCount'] = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink');
                                    $holdnQuantity['bonusTotalCount'] = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGamesHoldnLink');
                                    $nextBonusType = 0;

                                    if($holdnQuantity['bonusPlayedCount'] >= $holdnQuantity['bonusTotalCount'])
                                    {
                                        //holdn ended
                                        for($r = 0; $r < 5; $r++)
                                            for($c = 0; $c < 4; $c++)
                                            {
                                                $times = $winsMatrix[$r][$c] - 17;
                                                if($times != 0)
                                                {
                                                    $figureId = $winsMatrix[$r][$c];
                                                    if($winsMatrix[$r][$c] == $jackpot1)
                                                    {
                                                        $times = 10;
                                                        $figureId = 32;
                                                    }
                                                    else if($winsMatrix[$r][$c] == $jackpot2)
                                                    {
                                                        $times = 5;
                                                        $figureId = 22;
                                                    }
                                                    $profit = $allbet * $times * 100;
                                                    $lineWin = ['figureId' => $figureId, 'lineId' => 255, 'mask' => 4, 'positions' => [[$c * 5 + $r, 0]], 'profit' => $profit];
                                                    array_push($lineWins, $lineWin);
                                                }
                                            }
                                        $totalWin += $holdnWin;
                                        $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin') + $holdnWin);
                                    }                                    
                                    $slotSettings->SetGameData($slotSettings->slotId . 'WinMatrix', $winsMatrix);                                    
                                }                                

                                $bonusQuantities = [$holdnQuantity];

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
                                    for($c = 0; $c < 4; $c++)
                                        for($r = 0; $r < 5; $r++)                                        
                                        {
                                            $reelsym[] = [$winsMatrix[$r][$c]];
                                        }
                                }
                                $magic_cells = [];
                                for($i = 0; $i < count($magicCells); $i++)
                                {
                                    $index = $magicCells[$i];
                                    $magic_cells[] = [(int)($index / 4), $index % 4];
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
                                        'packID' => 0,
                                        'win' => $totalWin * 100,
                                        'current_spin' => $currentSpin,
                                        'magic_cells' => $magic_cells
                                    ],
                                    'result' => true,
                                    'sesId' => '10000000223'
                                ];
                                $hnlMatrix = [];
                                for($c = 0; $c < 4; $c++)
                                    for($r = 0; $r < 5; $r++)                                        
                                    {
                                        $hnlMatrix[] = [$winsMatrix[$r][$c]];                                            
                                    }
                                if($triggerHoldn)
                                {                                             
                                    $betData['data']['hnlMatrix'] = $hnlMatrix;
                                }
                                    
                                $slotSettings->SetGameData($slotSettings->slotId . 'LastReels', $reels);
                                $currentBonusType = -1;
                                if($postData['slotEvent'] == $HOLDNLINK_TAG)
                                {
                                    $nextBonusType = 0;
                                    $currentBonusType = 0;
                                    if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGamesHoldnLink') <= $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGameHoldnLink'))
                                    {
                                        $nextBonusType = -1;
                                        $betData['data']['win'] = $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin') * 100;
                                    }   
                                    $betData['data']['currentBonusType'] = $currentBonusType;
                                }                               
                                
                                $betData['data']['nextBonusType'] = $nextBonusType;
                                if($nextBonusType != -1)
                                    $totalWin = 0;
                                if( $totalWin > 0) 
                                {
                                    $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), -1 * $totalWin);
                                    $slotSettings->SetBalance($totalWin);
                                    $slotSettings->SetWin($totalWin);                                  
                                }
                                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentSpin', $currentSpin);
                                if($currentSpin == 10)
                                    $magicCells = [];
                                $slotSettings->SetGameData($slotSettings->slotId . 'MagicCells', $magicCells);
                                $spinData = [
                                    'responseEvent' => 'spin',
                                    'responseType' => $postData['slotEvent'],
                                    'serverResponse' => [
                                        'bonusQuantities' => $bonusQuantities,
                                        'extraWin' => 0,
                                        'matrix' => $reelsym,
                                        'basicMatrix' => $basicReelSym,      
                                        'nextBonusType' => $nextBonusType,
                                        'currentBonusType' => $currentBonusType,
                                        'roundId' => 0,
                                        'series' => $lineWins,
                                        'magic_cells' => $magic_cells,
                                        'currentSpin' => $currentSpin,
                                        'win' => $totalWin * 100,
                                        'winsMatrix' => $winsMatrix,
                                        'hnlMatrix' => $hnlMatrix,
                                        'basicWinsMatrix' => $basicWinsMatrix,
                                        'totalWin' => $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin'),
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
