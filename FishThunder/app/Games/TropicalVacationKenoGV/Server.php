<?php 
namespace VanguardLTE\Games\TropicalVacationKenoGV
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
                    $postData0 = json_decode(trim(file_get_contents('php://input')), true);
                    $postData = [];
                    $postData['command'] = $postData0['gameData'][0];
                    $balanceInCents = $slotSettings->GetBalance() * 100;
                    $result_tmp = [];
                    $aid = '';
                    if( $postData['command'] == 'draw' ) 
                    {
                        $postData['command'] = 'bet';
                    }
                    if( $postData['command'] == 'bet' ) 
                    {
                        $lines = 1;
                        $betline = $postData0['gameData'][1]['bet'];
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
                    }
                    $aid = (string)$postData['command'];
                    switch( $aid ) 
                    {
                        case 'setup':
                            $result_tmp[] = '40';
                            $result_tmp[] = '40/game';
                            $slotSettings->SetGameData('SpinStatus', '');
                            break;
                        case 'open':
                            $gameBets = $slotSettings->Bet;
                            $denoms = [];
                            $denoms[] = '' . ($slotSettings->CurrentDenom * 100) . '';
                            foreach( $gameBets as &$b ) 
                            {
                                $b = '' . ($b * 100) . '';
                            }
                            $balanceInCents = $slotSettings->GetBalance() * 100;
                            $result_tmp[] = '42/game,["open",{"bet":{"denoms":[5,10,25,50,100],"bets":[1,2,3,4,5,6,7,8,9,10]},"paytable":[[null],[0],[0,0],[0,0,1,25],[0,0,1,2,22],[0,0,0,3,18,210],[0,0,0,1,8,28,350],[0,0,0,1,2,8,60,400],[0,0,0,0,3,10,50,400,1000],[0,0,0,0,2,5,12,75,500,1500],[0,0,0,0,1,2,8,47,150,1000,2500]],"rows":8,"columns":10,"picks":10,"rtp":0.9402,"recall":{"id":"624ba6d6371967da71005ee6","date":"2022-04-05T02:17:58.542Z","bet":50,"win":50,"balance":50000,"actions":[{"event":"draw","cost":50,"request":{"selected":[42,43,52,53,62,63,72,73,54,64],"denom":5,"bet":10},"response":{"credits":50000,"_cost":50,"_close":true,"_win":50,"bonus":{"trigger_win":10,"turtles":[37,22,20,6]},"bonuses":false,"win":50,"catches":[53,42,62,63],"picks":[42,43,52,53,62,63,72,73,54,64],"draw":[53,67,47,25,1,45,42,36,51,7,62,19,41,15,17,50,22,63,55,32]}}]},"feature":{"total_objects":12,"turtles":4,"multipliers":[2,2,3,3,4,4,5,5,6,6,7,8,9,10]},"credits":'.$balanceInCents.'}]';
                            break;
                        case 'feature':
                            $actionFeature = $postData0['gameData'][1]['action'];
                            $FeatureStep = $slotSettings->GetGameData('TropicalVacationKenoGVFeatureStep');
                            $totalPicked = $slotSettings->GetGameData('TropicalVacationKenoGVtotalPicked');
                            $pickedObjectsIndexes = $slotSettings->GetGameData('TropicalVacationKenoGVpickedObjectsIndexes');
                            $pickedMultipliers = $slotSettings->GetGameData('TropicalVacationKenoGVpickedMultipliers');
                            $pickedObject = $slotSettings->GetGameData('PickedObjects');
                            if( $actionFeature == 'start' ) 
                            {
                                $FeatureMultipliers = [
                                    2, 
                                    2, 
                                    3, 
                                    3, 
                                    4, 
                                    4, 
                                    5, 
                                    5, 
                                    6, 
                                    6, 
                                    7, 
                                    8, 
                                    9, 
                                    10
                                ];
                                shuffle($FeatureMultipliers);
                                $picks = $slotSettings->GetGameData('TropicalVacationKenoGVpickObjects');
                                $result_tmp[0] = '42/game,["feature",{"win":0,"picks":'.$picks.',"multipliers":[' . implode(',', $FeatureMultipliers) . '],"name":"tikibar","action":"start","_cost":0,"credits":' . $balanceInCents . '}]';
                            }
                            else if( $actionFeature == 'choosePick' ) 
                            {
                                $FeatureMultipliers = $slotSettings->GetGameData('TropicalVacationKenoGVFeatureMultipliers');                                

                                $pickIndex = $postData0['gameData'][1]['index'];
                                $pickMultiplier = $FeatureMultipliers[$pickIndex];
                                $pickedObjectsIndexes[] = $pickIndex;
                                $totalPicked++;
                                $pickedMultipliers[] = $pickMultiplier;

                                $pickedObject[$pickIndex] = $pickMultiplier;

                                if( !is_array($pickedMultipliers) || !is_array($FeatureMultipliers) || !is_array($pickedObjectsIndexes) ) 
                                {
                                    $response = '{"responseEvent":"error","responseType":"error","serverResponse":"invalid feature state"}';
                                    exit( $response );
                                }
                                $result_tmp[0] = '42/game,["feature",{"multiplier":' . $pickMultiplier . ',"pickedMultipliers":[' . implode(',', $pickedMultipliers) . '],"pickIndexes":[' . implode(',', $pickedObjectsIndexes) . '],"pickedObjectsIndexes":[' . implode(',', $pickedObjectsIndexes) . '],"pickedObjectsMultipliers":[' . implode(',', $pickedMultipliers) . '],"index":' . $pickIndex . ',"totalPicked":' . $totalPicked . ',"picks":'. $slotSettings->GetGameData('TropicalVacationKenoGVpickObjects') .',"name":"tikibar","action":"choosePick","_cost":0,"credits":' . $balanceInCents . '}]';
                            }
                            else if( $actionFeature == 'finishPick' ) 
                            {
                                $FeatureMultipliers = $slotSettings->GetGameData('TropicalVacationKenoGVFeatureMultipliers');
                                if( !is_array($FeatureMultipliers) ) 
                                {
                                    $response = '{"responseEvent":"error","responseType":"error","serverResponse":"invalid feature state"}';
                                    exit( $response );
                                }
                                $result_tmp[0] = '42/game,["feature",{"multipliers":[' . implode(',', $slotSettings->GetGameData('TropicalVacationKenoGVFeatureMultipliers')) . '],"_close":true,"pickedMultipliers":[' . implode(',', $pickedMultipliers) . '],"pickIndexes":[' . implode(',', $pickedObjectsIndexes) . '],"pickedObjectsIndexes":[' . implode(',', $pickedObjectsIndexes) . '],"pickedObjectsMultipliers":[' . implode(',', $pickedMultipliers) . '],"name":"tikibar","action":"finishPick","_cost":0,"credits":' . $balanceInCents . '}]';
                                
                                $slotSettings->SetGameData('RemovedIndices', []);
                            }
                            $slotSettings->SetGameData('TropicalVacationKenoGVFeatureMultipliers', $FeatureMultipliers);
                            $slotSettings->SetGameData('TropicalVacationKenoGVFeatureStep', $FeatureStep);
                            $slotSettings->SetGameData('TropicalVacationKenoGVtotalPicked', $totalPicked);
                            $slotSettings->SetGameData('TropicalVacationKenoGVpickedObjectsIndexes', $pickedObjectsIndexes);
                            $slotSettings->SetGameData('TropicalVacationKenoGVpickedMultipliers', $pickedMultipliers);
                            $slotSettings->SetGameData('PickedObjects', $pickedObject);
                            break;
                        case 'bet':
                            $postData['slotEvent'] = 'bet';
                            if($slotSettings->GetGameData('SpinStatus') == 'freespin')
                                $postData['slotEvent'] = 'freespin';
                            $lines = 1;
                            $betline = $postData0['gameData'][1]['bet'] *  $postData0['gameData'][1]['denom'] / 100;
                            $allbet = $betline * $lines;
                            
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
                            else
                            {
                                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);
                            }

                            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $allbet);
                            $winType = $winTypeTmp[0];
                            $spinWinLimit = $winTypeTmp[1];
                            if($debug && $postData['slotEvent'] != 'freespin')
                                $winType = 'bonus';
                            if($winType == 'bonus' && $postData['slotEvent'] == 'freespin')
                                $winType = 'win';
                                
                            $balls = [];
                            for( $b = 0; $b < 80; $b++ ) 
                            {
                                $balls[] = $b + 1;
                            }
                            $ballSelected = $postData0['gameData'][1]['selected'];                            

                            $mintikiSpot = [];
                            $minavailableMultiplierIndices = [];
                            $minmultiHit = [];
                            $minturtlesCnt = rand(0, 4);
                            $minturtlesCurCnt = 0;
                            $minturtlesArr = [];
                            $minmatchNumbers = [];
                            $mindrawnNumbers = [];
                            $minremovedIndices = [];
                            $minTotalWin = -1;
                            $minmpl = 1;

                            $mpl = 1;
                            $spinAcquired = false;

                            for( $i = 0; $i <= 2000; $i++ ) 
                            {
                                $mpl = 1;
                                $totalWin = 0;
                                shuffle($balls);

                                $matchNumbers = [];
                                $drawnNumbers = [];
                                $turtlesCurCnt = 0;
                                $turtlesArr = [];
                                $multiHit = [];
                                $removedIndices = [];
                                $tikiSpot = [];
                                $availableMultiplierIndices = [];

                                $ballSelectedIndices = [];
                                $turtlesCnt = 4;

                                if($winType != 'none')
                                {
                                    if(count($ballSelected) > 4)
                                    {
                                        $targetMatchNumber = count($ballSelected) - $i % count($ballSelected);
                                        while(count($ballSelectedIndices) < $targetMatchNumber)
                                        {
                                            $index = rand(0, count($ballSelected) - 1);
                                            if(!in_array($index, $ballSelectedIndices))
                                                $ballSelectedIndices[] = $index;
                                        }
                                    }
                                }                                

                                if($winType == 'none')
                                {
                                    for( $a = 0; $a < 20; $a++ ) 
                                    {
                                        $drawnNumbers[] = $balls[$a];    
                                    }
                                }
                                else
                                {
                                    for($a = 0; $a < count($ballSelectedIndices); $a++)
                                        $drawnNumbers[] = $ballSelected[$ballSelectedIndices[$a]];
                                    
                                    for($a = count($ballSelectedIndices); $a < 20; $a++)
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

                                if($postData['slotEvent'] == 'freespin')
                                {
                                    //set multipliers
                                    $removedIndices = $slotSettings->GetGameData('RemovedIndices');
                                    $pickedIndices = $slotSettings->GetGameData('TropicalVacationKenoGVpickedObjectsIndexes');
                                    $pickedObjects = $slotSettings->GetGameData('PickedObjects');

                                    $mpl = 0;
                                    foreach($pickedIndices as $value)
                                    {
                                        if(!in_array($value, $removedIndices))
                                        {
                                            $spot = rand(1, 80);
                                            if(rand(0, 100) < 20)
                                                $spot = $drawnNumbers[rand(0, count($drawnNumbers) - 1)];
                                            while(in_array($spot, $tikiSpot))
                                            {
                                                $spot = rand(1, 80);
                                            }
                                            $tikiSpot[] = $spot;
                                            $availableMultiplierIndices[$spot] = $value;
                                        }
                                    }

                                    foreach($removedIndices as $value)
                                    {
                                        $mpl += $pickedObjects[$value];
                                    }

                                    if($mpl == 0)
                                        $mpl = 1;
                                }

                                if($mpl == 1)
                                    $mpl = 0;
                                for( $b = 0; $b < count($drawnNumbers); $b++ ) 
                                {
                                    $curBall = $drawnNumbers[$b];
                                    if( in_array($curBall, $ballSelected) ) 
                                    {
                                        $matchNumbers[] = $curBall;
                                    }
                                }

                                if($postData['slotEvent'] == 'freespin')
                                {
                                    foreach($drawnNumbers as $curBall)
                                    {
                                        if(in_array($curBall, $tikiSpot))
                                        {
                                            $objectIndex = $availableMultiplierIndices[$curBall];
                                            $multiHit[] = $objectIndex;
                                            $mpl += $pickedObjects[$objectIndex];
                                            $removedIndices[] = $objectIndex;
                                        }
                                    }
                                }

                                if($mpl == 0)
                                    $mpl = 1;

                                $curPays = $slotSettings->Paytable[count($ballSelected)];
                                $totalWin = $betline * $curPays[count($matchNumbers)] * $mpl;

                                shuffle($balls);
                                $turtleIndices = [];
                                if($winType == 'bonus')
                                {
                                    $matchedturtlesCnt = rand(3, 4);
                                    while(count($turtleIndices) < $matchedturtlesCnt)
                                    {
                                        $index = rand(0, count($matchNumbers) - 1);
                                        if(!in_array($index, $turtleIndices))
                                            $turtleIndices[] = $index;
                                    }                                    
                                }
    
                                for( $t = 0; $t < $turtlesCnt; $t++ ) 
                                {
                                    if($winType == 'bonus')
                                    {
                                        if($t < count($turtleIndices))
                                            $turtlesArr[] = $matchNumbers[$turtleIndices[$t]];
                                        else
                                        {
                                            $pos = rand(1, 80);
                                            while(in_array($pos, $turtlesArr))
                                            {
                                                $pos = rand(1, 80);
                                            }
                                            $turtlesArr[] = $pos;
                                        }
                                    }
                                    else
                                        $turtlesArr[] = $balls[$t];
                                    
                                    if( in_array($turtlesArr[$t], $matchNumbers) ) 
                                    {
                                        $turtlesCurCnt++;
                                    }
                                }
                                if($turtlesCurCnt >= 3 && $postData['slotEvent'] == 'freespin')
                                    continue;

                                if($minTotalWin == -1 || ($totalWin < $minTotalWin && $totalWin > 0))
                                {
                                    $mintikiSpot = $tikiSpot;
                                    $minavailableMultiplierIndices = $availableMultiplierIndices;
                                    $minmultiHit = $multiHit;
                                    $minturtlesCnt = $turtlesCnt;
                                    $minturtlesCurCnt = $turtlesCurCnt;
                                    $minturtlesArr = $turtlesArr;
                                    $minmatchNumbers = $matchNumbers;
                                    $mindrawnNumbers = $drawnNumbers;
                                    $minTotalWin = $totalWin;
                                    $minremovedIndices = $removedIndices;
                                    $minmpl = $mpl;
                                }
                                if($debug)
                                {
                                    $spinAcquired = true;
                                    break;
                                }

                                if($totalWin <= $spinWinLimit && (($totalWin > 0 && $winType != 'none') || ($winType == 'bonus' && $turtlesCurCnt >= 3)))
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

                            if(!$spinAcquired && $totalWin > 0 && $winType != 'none')
                            {
                                $tikiSpot = $mintikiSpot;
                                $availableMultiplierIndices = $minavailableMultiplierIndices;
                                $multiHit = $minmultiHit;
                                $turtlesCnt = $minturtlesCnt;
                                $turtlesCurCnt = $minturtlesCurCnt;
                                $turtlesArr = $minturtlesArr;
                                $matchNumbers = $minmatchNumbers;
                                $drawnNumbers = $mindrawnNumbers;
                                $totalWin = $minTotalWin;
                                $removedIndices = $minremovedIndices;
                                $mpl = $minmpl;
                            }

                            if( $totalWin > 0 )
                            {
                                $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), -1 * $totalWin);
                                $slotSettings->SetBalance($totalWin);
                                $slotSettings->SetWin($totalWin);
                            }

                            $reportWin = $totalWin;
                            $response = '{"responseEvent":"spin","responseType":"' . $postData['slotEvent'] . '","serverResponse":{"slotLines":' . $lines . ',"slotBet":' . $betline . ',"totalFreeGames":0,"currentFreeGames":0,"Balance":' . $slotSettings->GetBalance() . ',"afterBalance":' . $slotSettings->GetBalance() . ',"bonusWin":0,"totalWin":' . $totalWin . ',"winLines":[],"Jackpots":[],"reelsSymbols":[]}}';
                            $slotSettings->SaveLogReport($response, $allbet, $reportWin, $postData['slotEvent']);
                            $winstring = '';
                            $slotSettings->SetGameData('TropicalVacationKenoGVTotalWin', $totalWin);
                            $slotSettings->SetGameData('TropicalVacationKenoGVGambleStep', 5);
                            $hist = $slotSettings->GetGameData('TropicalVacationKenoGVCards');
                            $balanceInCents = $slotSettings->GetBalance() * 100;

                            $bonuses = false;
                            
                            if( $turtlesCurCnt >= 3 ) 
                            {
                                //trigger bonus
                                $bonuses = ["tikibar"];
                                $slotSettings->SetGameData('TropicalVacationKenoGVpickedObjectsIndexes', []);
                                $slotSettings->SetGameData('TropicalVacationKenoGVpickedMultipliers', []);
                                $slotSettings->SetGameData('PickedObjects', []);
                                $slotSettings->SetGameData('TropicalVacationKenoGVtotalPicked', 0);
                                
                                if($turtlesCurCnt == 3)
                                    $slotSettings->SetGameData('TropicalVacationKenoGVpickObjects', 4);
                                else
                                    $slotSettings->SetGameData('TropicalVacationKenoGVpickObjects', 5);

                                $slotSettings->SetGameData('SpinStatus', 'freespin');
                                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);  
                                $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', 0);  
                            }

                            $draw = [
                                'draw' => $drawnNumbers,
                                'picks' => $ballSelected,
                                'catches' => $matchNumbers,
                                'win' => $totalWin * 100,
                                'bonuses' => $bonuses,
                                'bonus' => [
                                    'turtles' => $turtlesArr,
                                    'trigger_win' => 0,
                                ],
                                '_close'=> true,
                                '_cost' => $allbet,
                                'credits' => $balanceInCents,
                            ];
                            if($totalWin > 0)
                            {
                                if($postData['slotEvent'] == 'freespin')
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin') + $totalWin);  
                                }
                                $draw['_win'] = $totalWin * 100;
                            }
                            if($postData['slotEvent'] == 'freespin')
                            {
                                $multipliers = [];
                                $lastRemovedIndices = $slotSettings->GetGameData('RemovedIndices');
                                $slotSettings->SetGameData('RemovedIndices', $removedIndices);
                                $indexes = [];
                                $pickedObject = $slotSettings->GetGameData('PickedObjects');

                                $pickedIndices = $slotSettings->GetGameData('TropicalVacationKenoGVpickedObjectsIndexes');
                                $pickedObjects = $slotSettings->GetGameData('PickedObjects');
                                foreach($pickedIndices as $index)
                                {
                                    if(!in_array($index, $lastRemovedIndices))
                                    {
                                        $indexes[] = $index;
                                        $multipliers[] = $pickedObjects[$index];
                                    }
                                }
                               
                                unset($draw['bonuses']);
                                $draw['bonus'] = ['turtles' => []];
                                $draw['indexes'] = $indexes;
                                $draw['multipliers'] = $multipliers;
                                $draw['multHit'] = $multiHit;
                                $draw['multiplier'] = $mpl;
                                $draw['numBonusGames'] = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                                $draw['tikiObjectsSpots'] = ['spot' => $tikiSpot];
                                $draw['pickedObjectsIndexes'] = $pickedIndices;
                                $draw['pickedObjectsMultipliers'] = $slotSettings->GetGameData('TropicalVacationKenoGVpickedMultipliers');
                                $draw['bonusTotalWin'] = $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin');
                                if(count($availableMultiplierIndices) == 0)
                                {
                                    $draw['barClosed'] = ['multSpot'=>rand(1,80)];
                                    $draw['endBonus'] = true;
                                    $slotSettings->SetGameData('SpinStatus', '');
                                }
                            }

                            $ret = [];
                            $ret[] = 'draw';
                            $ret[] = $draw;

                            $result_tmp[] = '42/game,' . json_encode($ret);
                            break;
                    }
                    $response = implode('------:::', $result_tmp);
                    $slotSettings->SaveGameData();
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
