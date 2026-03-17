<?php 
namespace VanguardLTE\Games\LuckyKenoPP
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
                    $postData = $_REQUEST;
                    $balanceInCents = sprintf('%01.2f', $slotSettings->GetBalance());
                    $result_tmp = [];
                    $aid = '';
                    if( $postData['command'] == 'PlaceBet' ) 
                    {
                        $gameData = json_decode($postData['input'], true);
                        $lines = 1;
                        $betline = $gameData['Stake'];
                        if( $lines <= 0 || $betline <= 0.0001 ) 
                        {
                            $response = '{"responseEvent":"error","responseType":"' . $postData['command'] . '","serverResponse":"invalid bet state"}';
                            exit( $response );
                        }
                        if( $slotSettings->GetBalance() < ($lines * $betline) ) 
                        {
                            $response = '{"responseEvent":"error","responseType":"' . $postData['command'] . '","serverResponse":"invalid balance"}';
                            exit( $response );
                        }
                        if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') < $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') && $postData['bet']['bonus'] == 'true' ) 
                        {
                            $response = '{"responseEvent":"error","responseType":"' . $postData['command'] . '","serverResponse":"invalid bonus state"}';
                            exit( $response );
                        }
                    }
                    $aid = (string)$postData['command'];
                    switch( $aid ) 
                    {
                        case 'loadgame':
                            $gameBets = [];
                            foreach( $slotSettings->Bet as $b ) 
                            {
                                if( $b >= 0.1 ) 
                                {
                                    $gameBets[] = $b;
                                }
                            }
                            $result_tmp[0] = '{"Stakes":[' . implode(',', $gameBets) . '],"DefaultStake":' . $gameBets[0] . ',"AutoBetRounds":[5,10,15,25,50],"MaxAutoBetSpins":50,"PlayerTokenId":0,"CurrencyCode":"' . $slotSettings->slotCurrency . ' ","ClientToken":"","BalanceUrl":"GetBalance","EndUrl":"EndGame","NotifyUrl":"Notify","PlaceBetUrl":"PlaceBet","HelpUrl":"","EndFreeGameUrl":"GetFreeRoundResult","MaxMultiplier":0.0,"UkRegulations":false,"GameVersion":1,"GameName":"Lucky Keno","GameCode":"Keno_LuckyKeno","DontShowBalance":false,"DebugMode":false,"IsSuperSpin":false,"Status":{"ErrCode":0,"InitialErrCode":0,"ErrType":0,"UniqueErrorCode":0,"FinancialMode":0,"Balance":' . $balanceInCents . ',"RoundsLeft":0}}';
                            break;
                        case 'GetBalance':
                            $result_tmp[] = '{"Status":{"ErrCode":0,"InitialErrCode":0,"ErrType":0,"UniqueErrorCode":0,"Balance":' . $balanceInCents . ',"RoundsLeft":0}}';
                            break;
                        case 'PlaceBet':
                            $lines = 1;
                            $betline = $gameData['Stake'];
                            $allbet = $betline * $lines;
                            $postData['slotEvent'] = 'bet';
                            if( $postData['slotEvent'] != 'freespin' ) 
                            {
                                if( !isset($postData['slotEvent']) ) 
                                {
                                    $postData['slotEvent'] = 'bet';
                                }
                                $slotSettings->SetBalance(-1 * $allbet, $postData['slotEvent']);
                                $bankSum = $allbet / 100 * $slotSettings->GetPercent();
                                $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $bankSum, $postData['slotEvent']);
                                $slotSettings->UpdateJackpots($allbet);
                                $slotSettings->SetBet($allbet);
                            }
                            $balanceInCents0 = sprintf('%01.2f', $slotSettings->GetBalance());
                            $winTypeTmp = $slotSettings->GetSpinSettings('bet', $allbet);
                            $winType = $winTypeTmp[0];
                            $bank = $winTypeTmp[1];
                            $balls = [];
                            for( $b = 0; $b < 80; $b++ ) 
                            {
                                $balls[] = $b + 1;
                            }
                            $ballSelected = $gameData['Chosen'];

                            $spinType = '';
                            if(rand(0, 100) < 5 && $bank > $allbet * 30 || $debug)
                                $spinType = 'MulWheel';

                            $minMatchNumbers = [];
                            $minDrawnNumbers = [];
                            $minTotalWin = -1;
                            $minMagicWin = -1;
                            $spinAcquired = false;
                            $minMagicNumbers = [];

                            for( $i = 0; $i <= 2000; $i++ ) 
                            {
                                $totalWin = 0;
                                shuffle($balls);
                                $matchNumbers = [];
                                $drawnNumbers = [];
                                $drawnNumbersStr = [];
                                $magicNumbers = [];
                                $magicNumbersStr = [];
                                $winMultiplier = 0;
                                if($winType == 'none')
                                {
                                    for( $a = 0; $a < 20; $a++ ) 
                                    {                                    
                                        $drawnNumbers[] = $balls[$a];
                                    }
                                }
                                else
                                {
                                    $matchingCount = count($ballSelected) - $i % count($ballSelected);
                                    for($a = 0; $a < $matchingCount; $a++)
                                    {
                                        $drawnNumbers[] = $ballSelected[$a];                                        
                                    }
                                    for($a = count($drawnNumbers); $a < 20; $a++)
                                    {
                                        for($b = 0; $b < 80; $b++)
                                        {
                                            if(!in_array($balls[$b], $drawnNumbers))
                                            {
                                                $drawnNumbers[] = $balls[$b];
                                                break;
                                            }
                                        }
                                    }
                                }                                

                                shuffle($balls);
                                $magicNumbers[0] = $balls[0];
                                $magicNumbers[1] = $balls[1];
                                $magicNumbers[2] = $balls[2];

                                if($spinType == 'MulWheel')
                                {
                                    $magicNumbers = [];
                                    while(count($magicNumbers) < 3)
                                    {
                                        $ball = $ballSelected[rand(0, count($ballSelected) - 1)];
                                        if(!in_array($ball, $magicNumbers))
                                        {
                                            $magicNumbers[] = $ball;
                                        }
                                    }

                                    $magicIndices = [];
                                    while(count($magicIndices) < 3)
                                    {
                                        $index = rand(0, 19);
                                        if(!in_array($index, $magicIndices))
                                            $magicIndices[] = $index;
                                    }

                                    for($a = 0; $a < 3; $a++)
                                    {
                                        $drawnNumbers[$magicIndices[$a]] = $magicNumbers[$a];
                                    }
                                }

                                for( $b = 0; $b < count($ballSelected); $b++ ) 
                                {
                                    $curBall = $ballSelected[$b];
                                    if( in_array($curBall, $drawnNumbers) ) 
                                    {
                                        $matchNumbers[] = $curBall;
                                    }
                                }
                                $curPays = $slotSettings->Paytable[count($ballSelected)];
                                $winMultiplier = $curPays[count($matchNumbers)];
                                $totalWin = $betline * $curPays[count($matchNumbers)];
                                $magicMplArr = [
                                    8, 
                                    12, 
                                    32, 
                                    15, 
                                    24, 
                                    4, 
                                    12, 
                                    20, 
                                    100, 
                                    24, 
                                    40, 
                                    8, 
                                    4, 
                                    20, 
                                    15, 
                                    8, 
                                    12, 
                                    32, 
                                    4, 
                                    15
                                ];
                                $magicMpl = 0;
                                $magicWin = 0;
                                $magicMatchCount = 0;
                                for( $k = 0; $k < count($magicNumbers); $k++ ) 
                                {
                                    if( in_array($magicNumbers[$k], $drawnNumbers) ) 
                                    {
                                        $magicMatchCount++;
                                    }
                                }
                                if( $magicMatchCount >= 3 ) 
                                {
                                    shuffle($magicMplArr);
                                    $magicMpl = $magicMplArr[0];
                                    $magicWin = $magicMpl * $betline;
                                }

                                if($minTotalWin == -1 || $minTotalWin > $totalWin)
                                {
                                    $minTotalWin = $totalWin;
                                    $minMatchNumbers = $matchNumbers;
                                    $minDrawnNumbers = $drawnNumbers;
                                    $minMagicNumbers = $magicNumbers;
                                    $minMagicWin = $magicWin;
                                }

                                if( $totalWin + $magicWin <= $bank && $totalWin + $magicWin > 0.7 * $bank && $winType != 'none')
                                {
                                    $spinAcquired = true;
                                    break;
                                }
                                if( $winType == 'none' && ($totalWin + $magicWin) == 0 ) 
                                {
                                    break;
                                }
                            }

                            if(!$spinAcquired && $totalWin + $minMagicWin > 0)
                            {
                                $totalWin = $minTotalWin;
                                $matchNumbers = $minMatchNumbers;
                                $drawnNumbers = $minDrawnNumbers;
                                $magicNumbers = $minMagicNumbers;
                                $magicWin = $minMagicWin;
                            }

                            $IsWin = 'false';
                            if( $totalWin > 0 ) 
                            {
                                $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), -1 * ($totalWin + $magicWin));
                                $slotSettings->SetBalance($totalWin + $magicWin);
                                $slotSettings->SetWin($totalWin + $magicWin);

                                $IsWin = 'true';
                            }
                            $reportWin = $totalWin + $magicWin;
                            $response = '{"responseEvent":"spin","responseType":"' . $postData['slotEvent'] . '","serverResponse":{"slotLines":' . $lines . ',"slotBet":' . $betline . ',"totalFreeGames":0,"currentFreeGames":0,"Balance":' . $slotSettings->GetBalance() . ',"afterBalance":' . $slotSettings->GetBalance() . ',"bonusWin":0,"totalWin":' . ($totalWin + $magicWin) . ',"winLines":[],"Jackpots":[],"reelsSymbols":[]}}';
                            $slotSettings->SaveLogReport($response, $allbet, $reportWin, $postData['slotEvent']);
                            $winstring = '';
                            $slotSettings->SetGameData('LuckyKenoPPTotalWin', $totalWin);
                            $slotSettings->SetGameData('LuckyKenoPPGambleStep', 5);
                            $hist = $slotSettings->GetGameData('LuckyKenoPPCards');
                            $balanceInCents = sprintf('%01.2f', $slotSettings->GetBalance());
                            $numStr = [];
                            for( $i = 0; $i < count($drawnNumbers); $i++ ) 
                            {
                                if( in_array($drawnNumbers[$i], $matchNumbers) ) 
                                {
                                    $numStr[] = '"' . $drawnNumbers[$i] . '":true';
                                }
                                else
                                {
                                    $numStr[] = '"' . $drawnNumbers[$i] . '":false';
                                }
                            }
                            for( $i = 0; $i < count($magicNumbers); $i++ ) 
                            {
                                if( in_array($magicNumbers[$i], $drawnNumbers) ) 
                                {
                                    $magicNumbersStr[] = '"' . $magicNumbers[$i] . '":true';
                                }
                                else
                                {
                                    $magicNumbersStr[] = '"' . $magicNumbers[$i] . '":false';
                                }
                            }
                            $result_tmp[0] = '{"Input":{"SelectedNumbers":[' . implode(',', $ballSelected) . '],"DebugPredefinedNumbers":[],"Stake":' . $allbet . '},"Ticket":{"DrawnNumbers":{' . implode(',', $numStr) . '},"PayTabelId":' . count($ballSelected) . ',"HitCount":' . count($matchNumbers) . ',"TotalWinAmount":' . ($totalWin + $magicWin) . ',"MagicNumbers":{' . implode(',', $magicNumbersStr) . '},"MagicNumbersWinMultiplier":' . $magicMpl . ',"MagicNumbersWinAmount":' . $magicWin . ',"WinAmount":' . $totalWin . ',"IsWin":' . $IsWin . ',"TotalBet":' . $allbet . ',"WinMultiplier":' . $winMultiplier . ',"Id":0,"IsFreeRound":false},"Balance":{"BalanceBefore":' . $balanceInCents0 . ',"BalanceAfter":' . $balanceInCents . ',"TotalWinAmount":' . ($totalWin + $magicWin) . ',"TotalBetAmount":' . $allbet . ',"TotalFreeSpinsWinAmount":0.0,"RoundsLeftBefore":0,"RoundsLeftAfter":0,"Id":0},"Status":{"ErrCode":0,"InitialErrCode":0,"ErrType":0,"UniqueErrorCode":0}}';
                            break;
                    }
                    $response = $result_tmp[0];
                    $slotSettings->SaveGameData();
                    echo $response;
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
