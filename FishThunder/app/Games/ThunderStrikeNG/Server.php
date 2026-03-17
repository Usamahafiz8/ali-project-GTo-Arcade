<?php 
namespace VanguardLTE\Games\ThunderStrikeNG
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
                            $restoreString = ',"restoredGameCode":"256","lastResponse":{"spinResult":{"type":"SpinResult","rows":[' . $syms . ']},"freeSpinsTotal":"' . $fTotal . '","freeSpinRemain":"' . $fRemain . '","totalBonusWin":"' . $fBonusWin . '","state":"FreeSpins","expandingSymbols":["1"]}';
                        }
                        $result_tmp[0] = '{"action":"AuthResponse","result":"true","sesId":"10000175411","data":{"snivy":"proxy v6.12.51 (API v4.23)","supportedFeatures":["Offers","Jackpots","InstantJackpots","SweepStakes"],"sessionId":"10000175411","defaultLines":["0","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","21","22","23","24","25","26","27","28","29","30","31","32","33","34","35","36","37","38","39","40","41","42","43","44","45","46","47","48","49","50","51","52","53","54","55","56","57","58","59","60","61","62","63","64","65","66","67","68","69","70","71","72","73","74","75","76","77","78","79","80","81","82","83","84","85","86","87","88","89","90","91","92","93","94","95"],"bets":["1","2","3","4","5","7","10","15","20","25"],"betMultiplier":"0.2083333","defaultBet":"'.$defaultBet.'","defaultCoinValue":"0.01","coinValues":["0.01"],"gameParameters":{"availableLines":[["0","0","0","0","0","0"],["0","0","0","0","1","1"],["0","0","0","1","1","1"],["0","0","0","1","2","2"],["0","0","1","1","1","1"],["0","0","1","1","2","2"],["0","0","1","2","2","2"],["0","0","1","2","3","3"],["0","0","2","2","2","2"],["0","0","2","2","3","3"],["0","0","2","3","3","3"],["0","0","2","3","4","4"],["0","0","3","3","3","3"],["0","0","3","3","4","4"],["0","0","3","4","4","4"],["0","0","3","4","5","5"],["0","1","0","0","0","0"],["0","1","0","0","1","1"],["0","1","0","1","1","1"],["0","1","0","1","2","2"],["0","1","1","1","1","1"],["0","1","1","1","2","2"],["0","1","1","2","2","2"],["0","1","1","2","3","3"],["0","1","2","2","2","2"],["0","1","2","2","3","3"],["0","1","2","3","3","3"],["0","1","2","3","4","4"],["0","1","3","3","3","3"],["0","1","3","3","4","4"],["0","1","3","4","4","4"],["0","1","3","4","5","5"],["0","2","0","0","0","0"],["0","2","0","0","1","1"],["0","2","0","1","1","1"],["0","2","0","1","2","2"],["0","2","1","1","1","1"],["0","2","1","1","2","2"],["0","2","1","2","2","2"],["0","2","1","2","3","3"],["0","2","2","2","2","2"],["0","2","2","2","3","3"],["0","2","2","3","3","3"],["0","2","2","3","4","4"],["0","2","3","3","3","3"],["0","2","3","3","4","4"],["0","2","3","4","4","4"],["0","2","3","4","5","5"],["1","0","0","0","0","0"],["1","0","0","0","1","1"],["1","0","0","1","1","1"],["1","0","0","1","2","2"],["1","0","1","1","1","1"],["1","0","1","1","2","2"],["1","0","1","2","2","2"],["1","0","1","2","3","3"],["1","0","2","2","2","2"],["1","0","2","2","3","3"],["1","0","2","3","3","3"],["1","0","2","3","4","4"],["1","0","3","3","3","3"],["1","0","3","3","4","4"],["1","0","3","4","4","4"],["1","0","3","4","5","5"],["1","1","0","0","0","0"],["1","1","0","0","1","1"],["1","1","0","1","1","1"],["1","1","0","1","2","2"],["1","1","1","1","1","1"],["1","1","1","1","2","2"],["1","1","1","2","2","2"],["1","1","1","2","3","3"],["1","1","2","2","2","2"],["1","1","2","2","3","3"],["1","1","2","3","3","3"],["1","1","2","3","4","4"],["1","1","3","3","3","3"],["1","1","3","3","4","4"],["1","1","3","4","4","4"],["1","1","3","4","5","5"],["1","2","0","0","0","0"],["1","2","0","0","1","1"],["1","2","0","1","1","1"],["1","2","0","1","2","2"],["1","2","1","1","1","1"],["1","2","1","1","2","2"],["1","2","1","2","2","2"],["1","2","1","2","3","3"],["1","2","2","2","2","2"],["1","2","2","2","3","3"],["1","2","2","3","3","3"],["1","2","2","3","4","4"],["1","2","3","3","3","3"],["1","2","3","3","4","4"],["1","2","3","4","4","4"],["1","2","3","4","5","5"],["0","0","0","0","0","1"],["0","0","0","0","1","2"],["0","0","0","1","1","2"],["0","0","0","1","2","3"],["0","0","1","1","1","2"],["0","0","1","1","2","3"],["0","0","1","2","2","3"],["0","0","1","2","3","4"],["0","0","2","2","2","3"],["0","0","2","2","3","4"],["0","0","2","3","3","4"],["0","0","2","3","4","5"],["0","0","3","3","3","4"],["0","0","3","3","4","5"],["0","0","3","4","4","5"],["0","0","3","4","5","6"],["0","1","0","0","0","1"],["0","1","0","0","1","2"],["0","1","0","1","1","2"],["0","1","0","1","2","3"],["0","1","1","1","1","2"],["0","1","1","1","2","3"],["0","1","1","2","2","3"],["0","1","1","2","3","4"],["0","1","2","2","2","3"],["0","1","2","2","3","4"],["0","1","2","3","3","4"],["0","1","2","3","4","5"],["0","1","3","3","3","4"],["0","1","3","3","4","5"],["0","1","3","4","4","5"],["0","1","3","4","5","6"],["0","2","0","0","0","1"],["0","2","0","0","1","2"],["0","2","0","1","1","2"],["0","2","0","1","2","3"],["0","2","1","1","1","2"],["0","2","1","1","2","3"],["0","2","1","2","2","3"],["0","2","1","2","3","4"],["0","2","2","2","2","3"],["0","2","2","2","3","4"],["0","2","2","3","3","4"],["0","2","2","3","4","5"],["0","2","3","3","3","4"],["0","2","3","3","4","5"],["0","2","3","4","4","5"],["0","2","3","4","5","6"],["1","0","0","0","0","1"],["1","0","0","0","1","2"],["1","0","0","1","1","2"],["1","0","0","1","2","3"],["1","0","1","1","1","2"],["1","0","1","1","2","3"],["1","0","1","2","2","3"],["1","0","1","2","3","4"],["1","0","2","2","2","3"],["1","0","2","2","3","4"],["1","0","2","3","3","4"],["1","0","2","3","4","5"],["1","0","3","3","3","4"],["1","0","3","3","4","5"],["1","0","3","4","4","5"],["1","0","3","4","5","6"],["1","1","0","0","0","1"],["1","1","0","0","1","2"],["1","1","0","1","1","2"],["1","1","0","1","2","3"],["1","1","1","1","1","2"],["1","1","1","1","2","3"],["1","1","1","2","2","3"],["1","1","1","2","3","4"],["1","1","2","2","2","3"],["1","1","2","2","3","4"],["1","1","2","3","3","4"],["1","1","2","3","4","5"],["1","1","3","3","3","4"],["1","1","3","3","4","5"],["1","1","3","4","4","5"],["1","1","3","4","5","6"],["1","2","0","0","0","1"],["1","2","0","0","1","2"],["1","2","0","1","1","2"],["1","2","0","1","2","3"],["1","2","1","1","1","2"],["1","2","1","1","2","3"],["1","2","1","2","2","3"],["1","2","1","2","3","4"],["1","2","2","2","2","3"],["1","2","2","2","3","4"],["1","2","2","3","3","4"],["1","2","2","3","4","5"],["1","2","3","3","3","4"],["1","2","3","3","4","5"],["1","2","3","4","4","5"],["1","2","3","4","5","6"]],"rtp":"0.00","payouts":[{"payout":"10","symbols":["1","1","1","1"],"type":"scatter"},{"payout":"50","symbols":["1","1","1","1","1"],"type":"scatter"},{"payout":"3","symbols":["2","2","2"],"type":"basic"},{"payout":"8","symbols":["2","2","2","2"],"type":"basic"},{"payout":"50","symbols":["2","2","2","2","2"],"type":"basic"},{"payout":"400","symbols":["2","2","2","2","2","2"],"type":"basic"},{"payout":"3","symbols":["3","3","3"],"type":"basic"},{"payout":"8","symbols":["3","3","3","3"],"type":"basic"},{"payout":"50","symbols":["3","3","3","3","3"],"type":"basic"},{"payout":"400","symbols":["3","3","3","3","3","3"],"type":"basic"},{"payout":"2","symbols":["4","4","4"],"type":"basic"},{"payout":"6","symbols":["4","4","4","4"],"type":"basic"},{"payout":"40","symbols":["4","4","4","4","4"],"type":"basic"},{"payout":"300","symbols":["4","4","4","4","4","4"],"type":"basic"},{"payout":"2","symbols":["5","5","5"],"type":"basic"},{"payout":"5","symbols":["5","5","5","5"],"type":"basic"},{"payout":"30","symbols":["5","5","5","5","5"],"type":"basic"},{"payout":"250","symbols":["5","5","5","5","5","5"],"type":"basic"},{"payout":"1","symbols":["6","6","6"],"type":"basic"},{"payout":"4","symbols":["6","6","6","6"],"type":"basic"},{"payout":"25","symbols":["6","6","6","6","6"],"type":"basic"},{"payout":"200","symbols":["6","6","6","6","6","6"],"type":"basic"},{"payout":"1","symbols":["7","7","7"],"type":"basic"},{"payout":"3","symbols":["7","7","7","7"],"type":"basic"},{"payout":"20","symbols":["7","7","7","7","7"],"type":"basic"},{"payout":"150","symbols":["7","7","7","7","7","7"],"type":"basic"},{"payout":"1","symbols":["8","8","8"],"type":"basic"},{"payout":"3","symbols":["8","8","8","8"],"type":"basic"},{"payout":"20","symbols":["8","8","8","8","8"],"type":"basic"},{"payout":"100","symbols":["8","8","8","8","8","8"],"type":"basic"},{"payout":"1","symbols":["9","9","9"],"type":"basic"},{"payout":"2","symbols":["9","9","9","9"],"type":"basic"},{"payout":"10","symbols":["9","9","9","9","9"],"type":"basic"},{"payout":"70","symbols":["9","9","9","9","9","9"],"type":"basic"},{"payout":"1","symbols":["10","10","10"],"type":"basic"},{"payout":"2","symbols":["10","10","10","10"],"type":"basic"},{"payout":"8","symbols":["10","10","10","10","10"],"type":"basic"},{"payout":"60","symbols":["10","10","10","10","10","10"],"type":"basic"},{"payout":"1","symbols":["11","11","11"],"type":"basic"},{"payout":"2","symbols":["11","11","11","11"],"type":"basic"},{"payout":"8","symbols":["11","11","11","11","11"],"type":"basic"},{"payout":"50","symbols":["11","11","11","11","11","11"],"type":"basic"},{"payout":"1","symbols":["12","12","12"],"type":"basic"},{"payout":"2","symbols":["12","12","12","12"],"type":"basic"},{"payout":"8","symbols":["12","12","12","12","12"],"type":"basic"},{"payout":"40","symbols":["12","12","12","12","12","12"],"type":"basic"}],"initialSymbols":[["0","1","2","3","4","5"],["6","7","8","9","10","11"],["-1","12","11","12","5","5"],["-1","-1","11","12","5","5"],["-1","-1","-1","12","5","5"],["-1","-1","-1","-1","5","5"],["-1","-1","-1","-1","-1","5"]]},"jackpotsEnabled":"true","gameModes":"[]" '.$restoreString.'}}';
                        break;
                    case 'BalanceRequest':
                        $result_tmp[] = '{"action":"BalanceResponse","result":"true","sesId":"10000214325","data":{"entries":"0.00","totalAmount":"' . $slotSettings->GetBalance() . '","currency":" "}}';
                        break;
                    case 'FreeSpinRequest':
                    case 'SpinRequest':                        
                        $postData['slotEvent'] = 'bet';
                        $lineCount = count($postData['data']['lines']);
                        $bonusMpl = 1;
                        $linesId = $slotSettings->GetPaylines();
                        $lines = 20;
                        $betLine = $postData['data']['coin'] * $postData['data']['bet'];
                        $slotSettings->SetGameData($slotSettings->slotId . 'BetLine', $postData['data']['bet']);
                        $allbet = $betLine * $lines;
                        if($lineCount == 192)
                            $allbet = $allbet * 2;
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
                        $minReels0 = [];
                        $minfreespinWin = 0;
                        $spinAcquired = false;
                        $wild = ['0', '3'];
                        $scatter = '1';
                        for( $i = 0; $i <= 500; $i++ ) 
                        {
                            $freespinWin = 0;
                            $totalWin = 0;
                            $lineWins = [];
                            $cWins = array_fill(0, $lineCount, 0);                            
                            $reels = $slotSettings->GetReelStrips($winType, $postData['slotEvent']);                            
                            $reels0 = $reels;
                            if($postData['slotEvent'] == 'freespin')
                            {
                                for($r = 0; $r < 6; $r++)
                                {
                                    $isWildReel = false;
                                    for($c = 0; $c < 7; $c++)
                                    {
                                        if($reels['reel'.($r+1)][$c] == '2')
                                        {
                                            $isWildReel = true;
                                            break;
                                        }
                                    }
                                    if($isWildReel)
                                    {
                                        for($c = 0; $c < 7; $c++)
                                        {
                                            $reels['reel'.($r+1)][$c] = '3';
                                        }
                                    }
                                }
                            }
                            else
                            {
                                for($r = 0; $r < 6; $r++)
                                {
                                    $isWildReel = true;
                                    for($c = 0; $c < 7; $c++)
                                    {
                                        if($reels['reel'.($r+1)][$c] != '2' && $reels['reel'.($r+1)][$c] != '-1')
                                        {
                                            $isWildReel = false;
                                            break;
                                        }
                                    }
                                    if($isWildReel)
                                    {
                                        for($c = 0; $c < 7; $c++)
                                        {
                                            if($reels['reel'.($r+1)][$c] == '2' )
                                            {
                                                $reels['reel'.($r+1)][$c] = '3';
                                            }
                                        }
                                    }
                                }
                            }
                            
                            for( $k = 0; $k < $lineCount; $k++ ) 
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
                                        $p5 = $linesId[$k][5];
                                        $s[0] = $reels['reel1'][$p0];
                                        $s[1] = $reels['reel2'][$p1];
                                        $s[2] = $reels['reel3'][$p2];
                                        $s[3] = $reels['reel4'][$p3];
                                        $s[4] = $reels['reel5'][$p4];
                                        $s[5] = $reels['reel6'][$p5];

                                        if($postData['slotEvent'] != 'freespin')
                                        {
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
                                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) && ($s[5] == $csym || in_array($s[5], $wild)))
                                            {
                                                $mpl = 1;
                                                if( in_array($s[0], $wild) && in_array($s[1], $wild) && in_array($s[2], $wild) && in_array($s[3], $wild) && in_array($s[4], $wild) && in_array($s[5], $wild)) 
                                                {
                                                    $mpl = 0;
                                                }
                                                else if( in_array($s[0], $wild) || in_array($s[1], $wild) || in_array($s[2], $wild) || in_array($s[3], $wild) || in_array($s[4], $wild) || in_array($s[5], $wild)  ) 
                                                {
                                                    $mpl = $slotSettings->slotWildMpl;
                                                }
                                                $tmpWin = $slotSettings->Paytable['SYM_' . $csym][6] * $betLine * $mpl * $bonusMpl;
                                                if( $cWins[$k] < $tmpWin ) 
                                                {
                                                    $cWins[$k] = $tmpWin;
                                                    $tmpStringWin = '{"type":"LineWinAmount","selectedLine":"' . $k . '","amount":"' . $tmpWin . '","wonSymbols":[["0","' . $p0 . '"],["1","' . $p1 . '"],["2","' . $p2 . '"],["3","' . $p3 . '"],["4","' . $p4 . '"],["5","' . $p5 . '"]]}';
                                                }
                                            }
                                        }
                                        else
                                        {
                                            if( ($s[5] == $csym || in_array($s[5], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) ) 
                                            {
                                                $mpl = 1;
                                                if( in_array($s[5], $wild) && in_array($s[4], $wild) ) 
                                                {
                                                    $mpl = 0;
                                                }
                                                else if( in_array($s[5], $wild) || in_array($s[4], $wild) ) 
                                                {
                                                    $mpl = $slotSettings->slotWildMpl;
                                                }
                                                $tmpWin = $slotSettings->Paytable['SYM_' . $csym][2] * $betLine * $mpl * $bonusMpl;
                                                if( $cWins[$k] < $tmpWin ) 
                                                {
                                                    $cWins[$k] = $tmpWin;
                                                    $tmpStringWin = '{"type":"LineWinAmount","selectedLine":"' . $k . '","amount":"' . $tmpWin . '","wonSymbols":[["4","' . $p4 . '"],["5","' . $p5 . '"]]}';
                                                }
                                            }
                                            if( ($s[5] == $csym || in_array($s[5], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) ) 
                                            {
                                                $mpl = 1;
                                                if( in_array($s[5], $wild) && in_array($s[4], $wild) && in_array($s[3], $wild) ) 
                                                {
                                                    $mpl = 0;
                                                }
                                                else if( in_array($s[5], $wild) || in_array($s[4], $wild) || in_array($s[3], $wild) ) 
                                                {
                                                    $mpl = $slotSettings->slotWildMpl;
                                                }
                                                $tmpWin = $slotSettings->Paytable['SYM_' . $csym][3] * $betLine * $mpl * $bonusMpl;
                                                if( $cWins[$k] < $tmpWin ) 
                                                {
                                                    $cWins[$k] = $tmpWin;
                                                    $tmpStringWin = '{"type":"LineWinAmount","selectedLine":"' . $k . '","amount":"' . $tmpWin . '","wonSymbols":[["3","' . $p3 . '"],["4","' . $p4 . '"],["5","' . $p5 . '"]]}';
                                                }
                                            }
                                            if( ($s[5] == $csym || in_array($s[5], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) ) 
                                            {
                                                $mpl = 1;
                                                if( in_array($s[5], $wild) && in_array($s[4], $wild) && in_array($s[3], $wild) && in_array($s[2], $wild) ) 
                                                {
                                                    $mpl = 0;
                                                }
                                                else if( in_array($s[5], $wild) || in_array($s[4], $wild) || in_array($s[3], $wild) || in_array($s[2], $wild) ) 
                                                {
                                                    $mpl = $slotSettings->slotWildMpl;
                                                }
                                                $tmpWin = $slotSettings->Paytable['SYM_' . $csym][4] * $betLine * $mpl * $bonusMpl;
                                                if( $cWins[$k] < $tmpWin ) 
                                                {
                                                    $cWins[$k] = $tmpWin;
                                                    $tmpStringWin = '{"type":"LineWinAmount","selectedLine":"' . $k . '","amount":"' . $tmpWin . '","wonSymbols":[["2","' . $p2 . '"],["3","' . $p3 . '"],["4","' . $p4 . '"],["5","' . $p5 . '"]]}';
                                                }
                                            }
                                            if( ($s[5] == $csym || in_array($s[5], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) ) 
                                            {
                                                $mpl = 1;
                                                if( in_array($s[5], $wild) && in_array($s[4], $wild) && in_array($s[3], $wild) && in_array($s[2], $wild) && in_array($s[1], $wild) ) 
                                                {
                                                    $mpl = 0;
                                                }
                                                else if( in_array($s[5], $wild) || in_array($s[4], $wild) || in_array($s[3], $wild) || in_array($s[2], $wild) || in_array($s[1], $wild) ) 
                                                {
                                                    $mpl = $slotSettings->slotWildMpl;
                                                }
                                                $tmpWin = $slotSettings->Paytable['SYM_' . $csym][5] * $betLine * $mpl * $bonusMpl;
                                                if( $cWins[$k] < $tmpWin ) 
                                                {
                                                    $cWins[$k] = $tmpWin;
                                                    $tmpStringWin = '{"type":"LineWinAmount","selectedLine":"' . $k . '","amount":"' . $tmpWin . '","wonSymbols":[["1","' . $p1 . '"],["2","' . $p2 . '"],["3","' . $p3 . '"],["4","' . $p4 . '"],["5","' . $p5 . '"]]}';
                                                }
                                            }
                                            if( ($s[5] == $csym || in_array($s[5], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[0] == $csym || in_array($s[0], $wild)))
                                            {
                                                $mpl = 1;
                                                if( in_array($s[5], $wild) && in_array($s[4], $wild) && in_array($s[3], $wild) && in_array($s[2], $wild) && in_array($s[1], $wild) && in_array($s[0], $wild)) 
                                                {
                                                    $mpl = 0;
                                                }
                                                else if( in_array($s[5], $wild) || in_array($s[4], $wild) || in_array($s[3], $wild) || in_array($s[2], $wild) || in_array($s[1], $wild) || in_array($s[0], $wild)  ) 
                                                {
                                                    $mpl = $slotSettings->slotWildMpl;
                                                }
                                                $tmpWin = $slotSettings->Paytable['SYM_' . $csym][6] * $betLine * $mpl * $bonusMpl;
                                                if( $cWins[$k] < $tmpWin ) 
                                                {
                                                    $cWins[$k] = $tmpWin;
                                                    $tmpStringWin = '{"type":"LineWinAmount","selectedLine":"' . $k . '","amount":"' . $tmpWin . '","wonSymbols":[["0","' . $p0 . '"],["1","' . $p1 . '"],["2","' . $p2 . '"],["3","' . $p3 . '"],["4","' . $p4 . '"],["5","' . $p5 . '"]]}';
                                                }
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
                            $scattersCount = 0;
                            for( $r = 1; $r <= 5; $r++ ) 
                            {
                                for( $p = 0; $p <= 6; $p++ ) 
                                {
                                    if( $reels['reel' . $r][$p] == $scatter ) 
                                    {
                                        $scattersCount++;
                                        $scattersPos[] = [($r - 1), $p];
                                    }
                                }
                            }
                            if($scattersCount >= 3 && $winType != 'bonus')
                                continue;
                            $scattersWin = $slotSettings->Paytable['SYM_' . $scatter][$scattersCount] * $betLine * $lines * $bonusMpl;
                            $gameState = 'Ready';
                            
                            if( $scattersCount >= 3 ) 
                            {
                                if($scattersCount > 5)
                                    $scattersCount = 5;
                                $freespinWin = 10;
                                if($scattersCount == 4)
                                    $freespinWin = 25;
                                else if($scattersCount == 5)
                                    $freespinWin = 50;
                                $scw = [
                                    'amount' => number_format($scattersWin, 2),
                                    'bonusName' => 'FreeSpins',
                                    'params' => ['freeSpins'=> $freespinWin],
                                    'type' => 'Bonus',
                                    'wonSymbols' => $scattersPos
                                ];
                                $scw = json_encode($scw);
                                array_push($lineWins, $scw);
                            }                            

                            $totalWin += ($scattersWin + $scattersWinB);
                            
                            if($minTotalWin == -1 || ($totalWin > 0 && $totalWin < $minTotalWin))
                            {
                                $minLineWins = $lineWins;
                                $minReels = $reels;
                                $minReels0 = $reels0;
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
                                break;
                            }
                        }

                        if($totalWin > 0 && !$spinAcquired)
                        {
                            $lineWins = $minLineWins;
                            $reels = $minReels;
                            $reels0 = $minReels0;
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
                        $syms = [];
                        for($c = 0; $c < 7; $c++)
                        {
                            $row = [];
                            for($r = 0; $r < 6; $r++)
                            {
                                $row[] = $reels['reel'.($r+1)][$c];
                            }
                            $syms[] = $row;
                        }
                        $symb = json_encode($syms);
                        $syms = [];
                        for($c = 0; $c < 7; $c++)
                        {
                            $row = [];
                            for($r = 0; $r < 6; $r++)
                            {
                                $row[] = $reels0['reel'.($r+1)][$c];
                            }
                            $syms[] = $row;
                        }
                        $symb0 = json_encode($syms);
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
                                $gameParameters = '"gameParameters":{"initialSymbols":[["8","9","12","12","10"],["1","9","7","7","9"],["-1","-1","11","3","12"],["-1","-1","5","4","7"]]},';
                                $winString0 = implode(',', $lineWins);
                                $winString = ',"slotWin":{"lineWinAmounts":[' . $winString0 . '],"totalWin":"' . $slotSettings->FormatFloat($totalWin) . '","canGamble":"false"}';
                            }
                            $result_tmp[] = '{"action":"FreeSpinResponse","result":"true","sesId":"10000228087","data":{' . $gameParameters . '"state":"' . $gameState . '"' . $winString . ',"spinResult":{"type":"SpinResult","rows":' . $symb0 . '},"spinResultStage2":{"type":"SpinResult","rows":' . $symb . '},"totalBonusWin":"' . $slotSettings->FormatFloat($bonusWin0) . '","freeSpinRemain":"' . $freeSpinRemain . '","freeSpinsTotal":"' . $freeSpinsTotal . '"}}';
                        }
                        else
                        {
                            $slotSettings->SetGameData($slotSettings->slotId . 'initialSymbols', $symb);
                            $result_tmp[] = '{"action":"SpinResponse","result":"true","sesId":"10000217909","data":{"state":"' . $gameState . '"' . $winString . ',"spinResult":{"type":"SpinResult","rows":' . $symb0 . '},"spinResultStage2":{"type":"SpinResult","rows":' . $symb . '}}}';
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
