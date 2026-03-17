<?php 
namespace VanguardLTE\Games\OlveraStreet
{
    set_time_limit(5);
    include('CheckReels.php');
    class Server
    {
        public function get($request, $game)
        {
            \DB::beginTransaction();
            $userId = \Auth::id();
            $debug = false;
            if( $userId == null ) 
            {
                $response = '{"responseEvent":"error","responseType":"","serverResponse":"invalid login"}';
                exit( $response );
            }
            $slotSettings = new SlotSettings($game, $userId);
            try
            {
                $reelIndex = [0,9,10,11,12,13,14,15,16,17];
                $fireBallScore = array(18 => 50, 19 => 75, 20 => 100, 21 => 125, 22 => 150, 23 => 200, 24 => 250, 25 => 375, 26 => 500, 27 => 625, 28 => 750, 29 => 1000, 30 => 2500, 31 => 5000);
                $balance = sprintf('%01.2f', $slotSettings->GetBalance()) * 100;
                $cashable_Balance = sprintf('%01.2f', $slotSettings->GetCashableBalance()) * 100;
                $type = $request->all()[0]['type'];
                $body = $request->all()[1]['body'];
                $returnResponse = array();
                switch($type)
                {
                    case "cashable":
                        $bodyResponse = '{"cashable":'.$cashable_Balance.'}';
                        $response = '{"main": 200,"type": 107,"body":'.$bodyResponse.'}';
                        array_push($returnResponse,$response);
                        break;
                    case "jackpot":
                        $bodyResponse = '{"jackpot":['.$slotSettings->jpgs[0]->balance.','.$slotSettings->jpgs[1]->balance.','.$slotSettings->jpgs[2]->balance.','.$slotSettings->jpgs[3]->balance.'], "shop_id":"'.$slotSettings->shop_id.'"}';
                        $response = '{"main": 200,"type": 101,"body":'.$bodyResponse.'}';
                        array_push($returnResponse,$response);
                        break;
                    case "login":
                        $bodyResponse = '{"wFaceID":1,"cbGender":1,"dwCustomID":0,"dwUserID":10335,"dwGameID":10335,"dwSpreaderID":0,"dwExperience":0,"szAccounts":"","szNickName":"","szAliPayAcccount":"","szBinkID":"","szDynamicPass":"01B2C320CB0C4F1F9F639383B79ED853","szMobile":"","lUserScore":'.$balance.',"lUserInsure":'.$cashable_Balance.',"cbInsureEnabled":0,"cbIsAgent":0,"cbMoorMachine":0,"TodayAlmsCount":0,"dwLockServerID":0,"dwKindID":0,"dwAgentID":2231}';
                        $response = '{"main": 1,"type": 100,"body":'.$bodyResponse.'}';
                        array_push($returnResponse,$response);
                        break;
                    case "transScore":
                        $transScore = intval($body);
                        $transScore = $transScore / 100;
                        if ($slotSettings->GetCashableBalance()>= $transScore) 
                        {
                            $user = \Auth::user();
                            $transaction = new \VanguardLTE\Transaction();
                            $transaction->user_id = \Auth::id();
                            $transaction->system = 'PURCHASE';
                            $transaction->type = 'add';
                            $transaction->summ = abs($transScore);
                            $transaction->shop_id = $user->shop_id;
                            $transaction->save();
                            $user->update([
                                'balance' => $user->balance + $transScore, 
                                'cashable_balance' => $user->cashable_balance - $transScore
                            ]);
                            $bodyResponse = '{"bTransition":1}';
                            $response = '{"main": 200,"type": 208,"body":'.$bodyResponse.'}';
                            array_push($returnResponse,$response);
                            break;   
                        }
                        break;
                    case "subLogin":
                        $bodyResponse = '{"dwGameID":10335,"dwUserID":10335,"wFaceID":1,"dwCustomID":0,"szNickName":"","szFaceAddress":"","cbGender":1,"cbMemberOrder":0,"wTableID":65535,"wChairID":65535,"cbUserStatus":1,"lScore":'.$balance.',"dwWinCount":7,"dwLostCount":14,"dwDrawCount":0,"dwFleeCount":0}';                               
                        $response = '{"main": 3,"type": 100,"body":'.$bodyResponse.'}';
                        array_push($returnResponse,$response);

                        $slotSettings->SetGameData('betIndex',0);
                        $slotSettings->SetGameData('freeSpinTotalTimes',0);
                        $slotSettings->SetGameData('freeSpinTimes',0);
                        $slotSettings->SetGameData('freeSpinTotalWinScore',0);
                        $slotSettings->SetGameData('totalGuess',0);
                        $slotSettings->SetGameData('gameType','normal'); 
                        $slotSettings->SetGameData('topLimit',4);
                        $fireLinkTable = [[0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0]];
                        $slotSettings->SetGameData('fireLinkTable',\json_encode($fireLinkTable));
                        break;
                    case "playData":
                        $bet = 1;
                        $line = 50;
                        $betPctIndex = 0;
                        $betPct = 1;
                        $bounsId = 0;
                        $betIndex = $slotSettings->GetGameData('betIndex');
                        $bet = $betIndex + 1;
                        $totalBet = $bet * 50;
                        $bodyResponse = '{"Credit": '.$cashable_Balance.',"BetIndex": '.$betIndex.',"Bet":'.$bet.',"Line":'.$line.',"BetPctIndex":'.$betPctIndex.',"BetPct":'.$betPct.',"TotalBet":'.$totalBet.',"BonusId":'.$bounsId.'}';
                        $response = '{"main":200, "type": 203,"body":'.$bodyResponse.'}'; 
                        array_push($returnResponse,$response);
                        break;
                    case "betChange":
                        $betIndex = $slotSettings->GetGameData('betIndex');
                        if ($body == 1)
                        {
                            if ($betIndex < 4)
                            {
                                $betIndex++;
                            }
                        }else
                        {
                            if ($betIndex >0)
                            {
                                $betIndex--;
                            }
                        }
                        $line = 0;
                        $betPctIndex = 0;
                        $betPct = 0;
                        $totalBet = 50;
                        switch($betIndex)
                        {
                            case 0:
                                $totalBet = 50;
                                break;
                            case 1:
                                $totalBet = 100;
                                break;
                            case 2:
                                $totalBet = 150;
                                break;
                            case 3:
                                $totalBet = 250;
                                break;
                            case 4:
                                $totalBet = 500;
                                break;
                            default:
                                $totalBet = 50;
                                break;
                        }
                        $bounsId = 0;
                        $slotSettings->SetGameData('betIndex', $betIndex);
                        $bodyResponse = '{"Credit": '.$cashable_Balance.',"BetIndex": '.$betIndex.',"Bet":'.($totalBet/50).',"Line":'.$line.',"BetPctIndex":'.$betPctIndex.',"BetPct":'.$betPct.',"TotalBet":'.$totalBet.',"BonusId":'.$bounsId.'}';
                        $response = '{"main":200,"type": 203,"body":'.$bodyResponse.'}'; //SUB_S_PLAYER_DATA:203
                        array_push($returnResponse,$response);
                        break;
                    case "spin":
                        
                        $linesId = [];
                        $linesId[0] = [1, 1, 1, 1, 1];
                        $linesId[1] = [2, 2, 2, 2, 2];
                        $linesId[2] = [0, 0, 0, 0, 0];
                        $linesId[3] = [3, 3, 3, 3, 3];
                        $linesId[4] = [1, 2, 3, 2, 1];
                        $linesId[5] = [2, 1, 0, 1, 2];
                        $linesId[6] = [0, 1, 2, 1, 0];
                        $linesId[7] = [3, 2, 1, 2, 3];
                        $linesId[8] = [2, 3, 2, 3, 2];
                        $linesId[9] = [1, 0, 1, 0, 1];
                        $linesId[10] = [1, 1, 2, 3, 3];
                        $linesId[11] = [2, 2, 1, 0, 0];
                        $linesId[12] = [3, 2, 2, 2, 3];
                        $linesId[13] = [0, 1, 1, 1, 0];
                        $linesId[14] = [1, 2, 1, 0, 1];
                        $linesId[15] = [2, 1, 2, 3, 2];
                        $linesId[16] = [1, 0, 0, 1, 2];
                        $linesId[17] = [2, 3, 3, 2, 1];
                        $linesId[18] = [1, 2, 2, 2, 1];
                        $linesId[19] = [2, 1, 1, 1, 2];
                        $linesId[20] = [2, 2, 3, 2, 1];
                        $linesId[21] = [1, 1, 0, 1, 2];
                        $linesId[22] = [0, 1, 0, 1, 0];
                        $linesId[23] = [3, 2, 3, 2, 3];
                        $linesId[24] = [0, 0, 1, 0, 0];
                        $linesId[25] = [3, 3, 2, 3, 3];
                        $linesId[26] = [1, 1, 2, 1, 1];
                        $linesId[27] = [2, 2, 1, 2, 2];
                        $linesId[28] = [0, 0, 1, 2, 2];
                        $linesId[29] = [3, 3, 2, 1, 1];
                        $linesId[30] = [1, 2, 1, 2, 1];
                        $linesId[31] = [2, 1, 2, 1, 2];
                        $linesId[32] = [2, 3, 2, 1, 2];
                        $linesId[33] = [1, 0, 1, 2, 1];
                        $linesId[34] = [1, 0, 0, 0, 1];
                        $linesId[35] = [2, 3, 3, 3, 2];
                        $linesId[36] = [1, 1, 1, 2, 3];
                        $linesId[37] = [2, 2, 2, 1, 0];
                        $linesId[38] = [0, 1, 2, 3, 2];
                        $linesId[39] = [3, 2, 1, 0, 1];
                        $linesId[40] = [1, 2, 3, 3, 3];
                        $linesId[41] = [2, 1, 0, 0, 0];
                        $linesId[42] = [0, 0, 0, 1, 2];
                        $linesId[43] = [3, 3, 3, 2, 1];
                        $linesId[44] = [3, 2, 2, 1, 0];
                        $linesId[45] = [0, 1, 1, 2, 3];
                        $linesId[46] = [1, 2, 2, 3, 3];
                        $linesId[47] = [2, 1, 1, 0, 0];
                        $linesId[48] = [0, 1, 0, 1, 2];
                        $linesId[49] = [3, 2, 3, 2, 1];
    
                        $curWinScore = 0;
                        $jackpotId = -1;
                        $jackpotScore = 0;
                        $winAllId = 0;
                        $winAllScore = 0;
                        $freeSpinTotalTimes = 0;
                        $freeSpinTimes = 0;
                        $freeSpinAddTimes = 0;
                        $freeSpinPlusOdds = 0;
                        $freeGameMode = 0;
                        $isAddFreeGame = 0;
                        $reelSymbols = [[0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0]];
                        $scatterLst = [-1,-1,-1,-1,-1];
                        $bounsId = 0;  
                        $freeSpinTotalTimes = 0;
                        $freeSpinTimes = 0;
                        $freeSpinTotalWinScore = 0;
                        $jackpotVal = 0;
                        $totalGuess = 0;
                        $topLimit = $slotSettings->GetGameData('topLimit');
                        $gameType = $slotSettings->GetGameData('gameType');
                        $fireLinkTable = \json_decode($slotSettings->GetGameData('fireLinkTable'));
                        switch($gameType)
                        {
                            case 'normal':
                                $bounsId = 0;
                                break;
                            case 'freespin':
                                $bounsId = 1;
                                break;
                            case 'bonus':
                                $bounsId = 2;
                                break;
                            default: 
                                $bounsId = 0;
                        }                        

                        $betIndex = $slotSettings->GetGameData('betIndex');
                        switch($betIndex)
                        {
                            case 0:
                                $totalBet = 50;
                                break;
                            case 1:
                                $totalBet = 100;
                                break;
                            case 2:
                                $totalBet = 150;
                                break;
                            case 3:
                                $totalBet = 250;
                                break;
                            case 4:
                                $totalBet = 500;
                                break;
                            default:
                                $totalBet = 50;
                                break;
                        }
                        $bet = $totalBet/ 50;
                        $logBet = 0;
                        if ($gameType == 'normal')
                        {
                            $slotSettings->SetBalance(-$totalBet * 0.01,'bet');
                            $bankSum = $totalBet * 0.01 / 100 * $slotSettings->GetPercent();
                            $slotSettings->SetBank('',$bankSum, 'bet');
                            $slotSettings->UpdateJackpots($totalBet * 0.01);
                            $slotSettings->SetBet($totalBet * 0.01);
                            $logBet = $totalBet;
                        }
                        $winTypeTmp = $slotSettings->GetSpinSettings($gameType,$totalBet * 0.01);
                        if($debug && $gameType == 'normal')
                            $winTypeTmp[0] = 'freespin';
                        $winTypeTmp[1] = $winTypeTmp[1] * 100;
                        if ($gameType == 'freespin' || $gameType == 'bonus')
                        {
                            $freeSpinTotalTimes = $slotSettings->GetGameData('freeSpinTotalTimes');
                            $freeSpinTimes = $slotSettings->GetGameData('freeSpinTimes');
                            $freeSpinTotalWinScore = $slotSettings->GetGameData('freeSpinTotalWinScore');
                            $totalGuess = $winTypeTmp[1];
                        }
                        $winLine = [];
                        $cWinsTotal = [];
                        for($i = 0; $i <= 2000 ; $i++)
                        {
                            $scatterLst = [-1,-1,-1,-1,-1];
                            $totalWin = 0;
                            $cWins = [];
                            if ($gameType == 'normal' || $gameType == 'freespin')
                            {
                                $cWins = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 
                                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0 ];
                            }else
                            {
                                $cWins = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 
                                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 
                                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 
                                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
                            }
                            $wild = ['0'];
                            $scatter = '2';

                            switch($gameType)
                            {
                                case "normal":
                                    if ($winTypeTmp[0] == "none" || $winTypeTmp[0] == "win")
                                    {
                                        $reels = []; 
                                        while(true)
                                        {
                                            $reels = $slotSettings->GetReelStrips('none',0); 
                                            $fsCnt = 0;
                                            $fbCnt = 0;
                                            for($m = 1; $m < 6; $m++)
                                            {
                                                for($n = 0; $n < 4; $n++)
                                                {
                                                    if ($reels['reel'.$m][$n] == '2')
                                                    {
                                                        $fsCnt++;
                                                        $scatterLst[$m-1] = $n;
                                                        break;
                                                    }
                                                }
                                            }
                                            
                                            for($m = 1; $m < 6; $m++)
                                            {
                                                for($n = 0; $n < 4; $n++)
                                                {
                                                    if (\intval($reels['reel'.$m][$n])  >= 18)
                                                    {
                                                        $fbCnt++;
                                                        break;
                                                    }
                                                }
                                            }
                                            if ($fsCnt <= 2 && $fbCnt <= 3 )
                                            {
                                                break;
                                            }
                                        }
                                    }else if ($winTypeTmp[0] == "freespin")
                                    {
                                        $reels = $slotSettings->GetReelStrips('freespin',0); 
                                        $slotSettings->SetGameData('totalGuess',$winTypeTmp[1]);
                                    }else if ($winTypeTmp[0] == "bonus")
                                    {
                                        $reels = $slotSettings->GetReelStrips('bonus',0); 
                                        $slotSettings->SetGameData('totalGuess',$winTypeTmp[1]);
                                    }
                                    break;
                                case "freespin":
                                    $reels = $slotSettings->GetReelStrips('none',1); 
                                    break;
                                case "bonus":
                                    $reels = $slotSettings->GetReelStrips('none',2); 
                                    for($r = 1; $r <= 5; $r++)
                                    for($c = 0; $c < $topLimit; $c++)
                                    {
                                        if($reels['reel'.$r][$c] >= 18)
                                            $reels['reel'.$r][$c] = $reelIndex[rand(0,count($reelIndex) - 1)];
                                    }
                                    break;
                            }
                            
                            if ($gameType == 'normal')
                            {
                                for($m = 1; $m < 6; $m++)
                                {
                                    for($n = 0; $n < 4; $n++)
                                    {
                                        if (\intval($reels['reel'.$m][$n])  >= 18)
                                        {
                                            $fireBallPercent = \rand(0,100);
                                            if ($fireBallPercent >= 0 && $fireBallPercent < 90)
                                            {
                                                $reels['reel'.$m][$n] = strval(\rand(18,23));
                                            }else if ($fireBallPercent >= 90 && $fireBallPercent < 95)
                                            {
                                                $reels['reel'.$m][$n] = strval(\rand(23,25));
                                            }else if ($fireBallPercent >= 95 && $fireBallPercent < 98)
                                            {
                                                $reels['reel'.$m][$n] = strval(\rand(25,27));
                                            }else if ($fireBallPercent >= 98 && $fireBallPercent < 100)
                                            {
                                                $reels['reel'.$m][$n] = strval(\rand(27,31));
                                            }
                                        }
                                    }
                                }
                            }else 
                            {
                                $currentTotalWin = 0;
                                $isMega = false;
                                $isMajor = false;
                                $isMinor = false;
                                $isMini = false;
                                for($m = 1; $m < 6; $m++)
                                {
                                    $n = 7;
                                    if ($gameType == 'freespin')
                                    {
                                        $n = 3;
                                    }
                                    for(; $n >= 0; $n--)
                                    {
                                        if (\intval($reels['reel'.$m][$n])  >= 18)
                                        {
                                            if ($gameType == 'freespin')
                                            {
                                                $currentTotalWin = $freeSpinTotalWinScore;
                                                $reels['reel'.$m][$n] = $reelIndex[\rand(0,\count($reelIndex) - 1)];
                                            }else 
                                            {
                                                for($a = 0; $a < 5;$a++)
                                                {
                                                    for($b = 7; $b >= $topLimit; $b--)
                                                    {
                                                        if(\intval($fireLinkTable[$a][$b]) >= 18)
                                                        {
                                                            $currentTotalWin += \intval($totalBet/50) * $fireBallScore[\intval($fireLinkTable[$a][$b])];
                                                        }else if (\intval($fireLinkTable[$a][$b]) == 6)
                                                        {
                                                            $currentTotalWin += $slotSettings->jpgs[3]->balance * 100;
                                                            $isMega = true;
                                                        }else if (\intval($fireLinkTable[$a][$b]) == 5)
                                                        {
                                                            $currentTotalWin += $slotSettings->jpgs[2]->balance* 100;
                                                            $isMajor = true;
                                                            
                                                        }else if (\intval($fireLinkTable[$a][$b]) == 4)
                                                        {
                                                            $currentTotalWin += $slotSettings->jpgs[1]->balance* 100;
                                                            $isMinor = true;                                                            
                                                        }else if (\intval($fireLinkTable[$a][$b]) == 3)
                                                        {
                                                            $currentTotalWin += $slotSettings->jpgs[0]->balance* 100;
                                                            $isMini = true; 
                                                        }
                                                    }
                                                    for($b = 7; $b >= 0; $b--)
                                                    {
                                                        if (\intval($fireLinkTable[$a][$b]) == 3)
                                                        {
                                                            $isMega = true;
                                                        }else if (\intval($fireLinkTable[$a][$b]) == 4)
                                                        {
                                                            $isMajor = true;   
                                                        }else if (\intval($fireLinkTable[$a][$b]) == 5)
                                                        {
                                                            $isMinor = true;                                                            
                                                        }else if (\intval($fireLinkTable[$a][$b]) == 6)
                                                        {
                                                            $isMini = true; 
                                                        }
                                                    }
                                                }
                                            }
                                            if ($currentTotalWin < $totalGuess && $gameType != 'freespin')
                                            {
                                                $fireBallPercent = \rand(0,100);
                                                if ($fireBallPercent >= 0 && $fireBallPercent < 90)
                                                {
                                                    $reels['reel'.$m][$n] = strval(\rand(18,23));
                                                }else if ($fireBallPercent >= 90 && $fireBallPercent < 95)
                                                {
                                                    $reels['reel'.$m][$n] = strval(\rand(23,25));
                                                }else if ($fireBallPercent >= 95 && $fireBallPercent < 98)
                                                {
                                                    $reels['reel'.$m][$n] = strval(\rand(25,27));
                                                }else if ($fireBallPercent >= 98 && $fireBallPercent < 100)
                                                {
                                                    $reels['reel'.$m][$n] = strval(\rand(27,31));
                                                }
                                                if ($gameType == 'bonus')
                                                {
                                                    // if ($slotSettings->jpgs[3]->balance >= $slotSettings->jpgs[3]->pay_sum && !$isMega && !$isMajor && !$isMinor && !$isMini)
                                                    // {
                                                    //     $reels['reel'.$m][$n] = '6';
                                                    //     $isMega = true;
                                                    // }else if ($slotSettings->jpgs[2]->balance >= $slotSettings->jpgs[2]->pay_sum && !$isMega && !$isMajor && !$isMinor && !$isMini)
                                                    // {
                                                    //     $reels['reel'.$m][$n] = '5';
                                                    //     $isMajor = true;
                                                    // }else if ($slotSettings->jpgs[1]->balance >= $slotSettings->jpgs[1]->pay_sum && !$isMega && !$isMajor && !$isMinor && !$isMini)
                                                    // {
                                                    //     $reels['reel'.$m][$n] = '4';
                                                    //     $isMinor = true;
                                                    // }else if ($slotSettings->jpgs[0]->balance >= $slotSettings->jpgs[0]->pay_sum && !$isMega && !$isMajor && !$isMinor && !$isMini)
                                                    // {
                                                    //     $reels['reel'.$m][$n] = '3';
                                                    //     $isMini = true;
                                                    // }    
                                                }                                                
                                            }
                                            else 
                                            {
                                                $reels['reel'.$m][$n] = $reelIndex[\rand(0,\count($reelIndex) - 1)];
                                            }
                                        }
                                    }
                                }
                            }

                            if ($gameType == "normal")
                            {
                                $fsCnt = 0;
                                $fbCnt = 0;
                                for($m = 1; $m < 6; $m++)
                                {
                                    for($n = 0; $n < 4; $n++)
                                    {
                                        if ($reels['reel'.$m][$n] == '2')
                                        {
                                            $fsCnt++;
                                            $scatterLst[$m-1] = $n;
                                            break;
                                        }
                                    }
                                }
                                
                                for($m = 1; $m < 6; $m++)
                                {
                                    for($n = 0; $n < 4; $n++)
                                    {
                                        if (\intval($reels['reel'.$m][$n])  >= 18)
                                        {
                                            $fbCnt++;
                                            break;
                                        }
                                    }
                                }
                                
    
                                if ($fsCnt >= 3) 
                                {
                                    $bounsId = 1;
                                    for ($m = 0; $m < 5; $m++)
                                    {
                                        for($n = 0; $n < 4; $n++)
                                        {
                                            $reelSymbols[$m][4 + $n] = $reels['reel'.strval($m + 1)][$n];
                                        }
                                    }
                                    break;
                                }

                                if ($fbCnt >= 4) 
                                {
                                    for ($m = 0; $m < 5; $m++)
                                    {
                                        for($n = 0; $n < 4; $n++)
                                        {
                                            $reelSymbols[$m][$n] = $reelIndex[\rand(0,\count($reelIndex) - 1)]; 
                                        }
                                        for($n = 0; $n < 4; $n++)
                                        {
                                            $reelSymbols[$m][4 + $n] = $reels['reel'.strval($m + 1)][$n];
                                        }
                                    }
                                    $gameType = 'bonus';
                                }
                            }else if ($gameType == "freespin") 
                            {
                                $fsCnt = 0;
                                for($m = 1; $m < 6; $m++)
                                {
                                    for($n = 0; $n < 4; $n++)
                                    {
                                        if ($reels['reel'.$m][$n] == '2')
                                        {
                                            $fsCnt++;
                                            $scatterLst[$m-1] = $n;
                                            break;
                                        }
                                    }
                                }

                                if ($fsCnt >= 3) 
                                {
                                    $freeSpinTotalTimes += 10;
                                    $freeSpinAddTimes = 10;
                                }
                            }
                            
                            if ($gameType == "normal" || $gameType == "freespin")   
                            {
                                $step = 1;
                                $totalWin = 0;
                                $winLine = [];
                                for($l = 0; $l < $step; $l++)
                                {
                                    for($j = 0;$j < 4; $j++ )
                                    {
                                        $csym = $reels['reel1'][$j + (2* $l)];
                                        if (\intval($csym) >= 18)
                                        {
                                            continue;
                                        }
                                        for($k = 0 ; $k < 50; $k++ )
                                        {
                                            $s = [];
                                            $s[0] = $reels['reel1'][$linesId[$k][0]+ (2* $l)];
                                            $s[1] = $reels['reel2'][$linesId[$k][1]+ (2* $l)];
                                            $s[2] = $reels['reel3'][$linesId[$k][2]+ (2* $l)];
                                            $s[3] = $reels['reel4'][$linesId[$k][3]+ (2* $l)];
                                            $s[4] = $reels['reel5'][$linesId[$k][4]+ (2* $l)];
                                            $tempWin = 0;
                                            $tempWinLine = [];
                                            if (($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) )
                                            {
                                                $tempWinLine = [$linesId[$k][0]+ (2* $l),$linesId[$k][1]+ (2* $l),$linesId[$k][2]+ (2* $l),-1,-1];
                                                $tempWin = $slotSettings->Paytable['SYM_'.$csym][3];
                                                if (($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild))&& ($s[3] == $csym || in_array($s[3], $wild)))
                                                {
                                                    $tempWinLine = [$linesId[$k][0]+ (2* $l),$linesId[$k][1]+ (2* $l),$linesId[$k][2]+ (2* $l),$linesId[$k][3]+ (2* $l),-1];
                                                    $tempWin = $slotSettings->Paytable['SYM_'.$csym][4];
                                                    if (($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild))&& ($s[3] == $csym || in_array($s[3], $wild))&& ($s[4] == $csym || in_array($s[4], $wild)))
                                                    {
                                                        $tempWin = $slotSettings->Paytable['SYM_'.$csym][5];
                                                        $tempWinLine = [$linesId[$k][0]+ (2* $l),$linesId[$k][1]+ (2* $l),$linesId[$k][2]+ (2* $l),$linesId[$k][3]+ (2* $l),$linesId[$k][4]+ (2* $l)];
                                                    }
                                                }   
                                            }
                                            if ($tempWinLine != [])
                                            {
                                                \array_push($winLine,$tempWinLine);
                                            } 
                                            if ($tempWin > 0)
                                            {
                                                if ($bounsId == 1)
                                                {
                                                    $tempWin *=2;
                                                }
                                                $cWins[$k + 50 * $l] = $tempWin;
                                                $totalWin += $tempWin  * $bet ;
                                            }
                                        }
                                    }
                                }                               
                                if ($gameType == "freespin" || $i > 1900 || ($i >= 1000 && $totalWin == 0) || ($totalWin <= $winTypeTmp[1] && $totalWin > 0.7 * $winTypeTmp[1]) || ( $winTypeTmp[1] == 0 && $totalWin == 0))
                                {
                                    $curWinScore = $totalWin;
                                    $winAllScore = $totalWin;
                                    $freeGameMode = 0;
                                    
                                    for ($m = 0; $m < 5; $m++)
                                    {
                                        $n = 0;
                                        $index = 0;
                                        for(; $n < 4; $n++)
                                        {
                                            $reelSymbols[$m][4 + $n] = $reels['reel'.strval($m + 1)][$index];
                                            $index++;
                                        }
                                    }
                                    $cWinsTotal = $cWins;
                                    break;
                                }
                                
                            }else if ($gameType == "bonus")
                            {
                                $isResetFireBallCnt = false;
                                $topLimit = $slotSettings->GetGameData('topLimit');
                                $fireLinkTable = \json_decode($slotSettings->GetGameData('fireLinkTable'));
                                $fireBallCnt1  = 0;
                                for($a = 0; $a < 5;$a++)
                                {
                                    for($b = 7; $b >= $topLimit; $b--)
                                    {
                                        if ($fireLinkTable[$a][$b] >= 18 || $fireLinkTable[$a][$b] == 3 ||$fireLinkTable[$a][$b] == 4 || $fireLinkTable[$a][$b] == 5 || $fireLinkTable[$a][$b] == 6)
                                        {
                                            $fireBallCnt1++;
                                        }
                                    }
                                }
                                if ($bounsId == 0)
                                {
                                    for($a = 0;$a <= 4;$a++)
                                    {
                                        for($b = 0; $b < 4; $b++)
                                        {
                                            if ((intval($reels['reel'.($a + 1)][$b]) >= 18 || intval($reels['reel'.($a + 1)][$b]) == 3 || intval($reels['reel'.($a + 1)][$b]) == 4 || intval($reels['reel'.($a + 1)][$b]) == 5 || intval($reels['reel'.($a + 1)][$b]) == 6 ) && $fireLinkTable[$a][$b + 4] == 0)
                                            {
                                                //$fireBallValue = ($betIndex + 1) * (50 + (intval($reels['reel'.$a][$b])-18) *25);
                                                $fireLinkTable[$a][$b + 4] = $reels['reel'.($a + 1)][$b];
                                            }
                                        }
                                    }
                                }else 
                                {
                                    for($a = 0;$a <= 4;$a++)
                                    {
                                        for($b = 7; $b >= 0; $b--)
                                        {
                                            if ((intval($reels['reel'.($a + 1)][$b]) >= 18 || intval($reels['reel'.($a + 1)][$b]) == 3 || intval($reels['reel'.($a + 1)][$b]) == 4 || intval($reels['reel'.($a + 1)][$b]) == 5 || intval($reels['reel'.($a + 1)][$b]) == 6 ) && $fireLinkTable[$a][$b] == 0)
                                            {
                                                $fireLinkTable[$a][$b] = $reels['reel'.($a + 1)][$b];
                                            }
                                        }
                                    }
                                }
                                
                                $fireBallCnt2  = 0;
                                for($a = 0; $a < 5;$a++)
                                {
                                    for($b = 7; $b >= $topLimit; $b--)
                                    {
                                        if ($fireLinkTable[$a][$b] >= 18 || $fireLinkTable[$a][$b] == 3 || $fireLinkTable[$a][$b] == 4 || $fireLinkTable[$a][$b] == 5 || $fireLinkTable[$a][$b] == 6)
                                        {
                                            $fireBallCnt2++;
                                        }
                                    }
                                }

                                switch($fireBallCnt2)
                                {
                                    case $fireBallCnt2 >= 20:
                                        $topLimit = 0;
                                        break;
                                    case $fireBallCnt2 >= 16:
                                        $topLimit = 1;
                                        break;
                                    case $fireBallCnt2 >=12:
                                        $topLimit = 2;
                                        break;
                                    case $fireBallCnt2 >= 8:
                                        $topLimit = 3;
                                        break;
                                }
                                $slotSettings->SetGameData('topLimit',$topLimit);
                                
                                for ($m = 0; $m < 5; $m++)
                                {
                                    $n = 0;
                                    $index = 0;
                                    if($bounsId == 2) 
                                    {
                                        $n = -4;
                                    }
                                    for(; $n < 4; $n++)
                                    {
                                        $reelSymbols[$m][4 + $n] = $reels['reel'.strval($m + 1)][$index];
                                        $index++;
                                    }
                                }
                                if ($fireBallCnt2 > $fireBallCnt1)
                                {
                                    $bounsId = 2;
                                    $freeSpinTotalTimes = 3;
                                    $freeSpinTimes = 0;
                                }
                                $slotSettings->SetGameData('fireLinkTable',\json_encode($fireLinkTable));
                                break;
                            }
                        }
                            
                        $isLastFreeSpin = ""; 
                        if ($gameType == "freespin") 
                        {
                            $freeSpinTotalWinScore += $curWinScore;
                            $freeSpinTimes++;
                            if ($freeSpinAddTimes > 0)
                            {
                                $isAddFreeGame = 1;
                            }
                            if ($freeSpinTotalTimes == $freeSpinTimes)
                            {
                                $bounsId = 0;
                                $isLastFreeSpin = '{"iBonusID":'.$bounsId.',"iFreeSpinTotalTimes":'.$freeSpinTotalTimes.',"iFreeSpinTimes":'.$freeSpinTimes.',"iFreeSpinAddTimes":'.$freeSpinAddTimes.',"iFreeSpinAgainTimes":0,"iFreeSpinPlusOdd":0,"dFSTotalWinScore":'.$freeSpinTotalWinScore.'}';
                                $gameType = "normal";
                                $slotSettings->SetGameData('freeSpinTotalTimes',0);
                                $slotSettings->SetGameData('freeSpinTimes',0);
                                $slotSettings->SetGameData('freeSpinTotalWinScore',0);
                                $slotSettings->SetGameData('totalGuess',0);
                            }
                            $bodyResponse = '{"fCredit":'.$cashable_Balance.',"iBetIndex":'.$betIndex.',"iBet":'.$bet.',"iTotalLine":150,"iBetLine":50,"iBetPctIndex":0,"iBetPct":1,"fTotalBet":'.$totalBet.',"iBonusId":'.$bounsId.',"iWinType":0,"iAwardCount":0,"fCurWinScore":'.$curWinScore.',"iJackpotId":'.$jackpotId.',"iJackpotScore":'.$jackpotScore.',"iWinAllId":'.$winAllId.',"fWinAllScore":'.$winAllScore.',"iFreeSpinTotalTimes":'.$freeSpinTotalTimes.',"iFreeSpinTimes":'.$freeSpinTimes.',"iFreeSpinAddTimes":'.$freeSpinAddTimes.',"iFreeSpinPlusOdd":'.$freeSpinPlusOdds.',"iNextGameSpecialOdds":1,"iFreeGameMode":'.$freeGameMode.',"iFreeGameAgainTime":0,"fFreeSpinTotalWinScore":'.$freeSpinTotalWinScore.',"bChangeAward":false,"ary2_Reels":'.\json_encode($reelSymbols).',"iIsAddFreeGame":'.$isAddFreeGame.',"list_PrizeInfos":[],"list_ScatterInfos":[]}';
                            $slotSettings->SetGameData('freeSpinTotalTimes',$freeSpinTotalTimes);
                            $slotSettings->SetGameData('freeSpinTotalWinScore',$freeSpinTotalWinScore);
                            $slotSettings->SetGameData('freeSpinTimes',$freeSpinTimes);
                            $slotSettings->SetGameData('gameType',$gameType);
                            $slotSettings->SetBank('bonus', -$curWinScore * 0.01);
                            $slotSettings->SetWin($curWinScore * 0.01);
                        }else if ($gameType == "normal")
                        {
                            $slotSettings->SetBank('normal', -$totalWin * 0.01);
                            $slotSettings->SetWin($totalWin * 0.01);
                            $bodyResponse = '{"fCredit":'.$cashable_Balance.',"iBetIndex":'.$betIndex.',"iBet":'.$bet.',"iTotalLine":50,"iBetLine":50,"iBetPctIndex":0,"iBetPct":1,"fTotalBet":'.$totalBet.',"iBonusId":'.$bounsId.',"iWinType":0,"iAwardCount":0,"fCurWinScore":'.$curWinScore.',"iJackpotId":'.$jackpotId.',"iJackpotScore":'.$jackpotScore.',"iWinAllId":'.$winAllId.',"fWinAllScore":'.$winAllScore.',"iFreeSpinTotalTimes":'.$freeSpinTotalTimes.',"iFreeSpinTimes":'.$freeSpinTimes.',"iFreeSpinAddTimes":'.$freeSpinAddTimes.',"iFreeSpinPlusOdd":'.$freeSpinPlusOdds.',"iNextGameSpecialOdds":1,"iFreeGameMode":'.$freeGameMode.',"iFreeGameAgainTime":0,"fFreeSpinTotalWinScore":'.$freeSpinTotalWinScore.',"bChangeAward":false,"ary2_Reels":'.\json_encode($reelSymbols).',"iIsAddFreeGame":'.$isAddFreeGame.',"list_PrizeInfos":[],"list_ScatterInfos":[]}';
                        }else if ($gameType == "bonus")
                        {
                            $freeSpinTotalWinScore = 0;
                            if ($freeSpinTotalTimes == 0)
                            {
                                $user = auth()->user();
                                $bounsId = 0;
                                $jackpotResponse = '';
                                for($a = 0; $a < 5;$a++)
                                {
                                    for($b = 7; $b >= $topLimit; $b--)
                                    {
                                        if(\intval($fireLinkTable[$a][$b]) >= 18)
                                        {
                                            $freeSpinTotalWinScore += \intval($totalBet/50) * $fireBallScore[\intval($fireLinkTable[$a][$b])];
                                        }
                                        else if (\intval($fireLinkTable[$a][$b]) == 6)
                                        {
                                            $freeSpinTotalWinScore += $slotSettings->jpgs[3]->balance* 100;
                                            $jackpotVal = $slotSettings->jpgs[3]->balance* 100;

                                            \VanguardLTE\StatGame::create([
                                                'user_id' =>$user->id , 
                                                'balance' => $slotSettings->GetBalance(), 
                                                'cashable_balance' => $slotSettings->GetCashableBalance(),
                                                'bet' => 0, 
                                                'win' => $slotSettings->jpgs[3]->balance, 
                                                'game' => 'community_prize_firelink', 
                                                'percent' => 0, 
                                                'percent_jps' => 0, 
                                                'percent_jpg' => 0, 
                                                'profit' => 0, 
                                                'shop_id' => $user->shop_id,
                                                'reels' => 'prize1'
                                            ]);

                                            $jackpotResponse = '{"pid": 10335,"jp_id": 3,"jpScore":'.($slotSettings->jpgs[3]->balance).'}'; ///////////
                                            $slotSettings->jpgs[3]->balance = $slotSettings->jpgs[3]->start_balance;
                                            $slotSettings->jpgs[3]->pay_sum = rand($slotSettings->jpgs[3]->start_balance*1.2 * 100,$slotSettings->jpgs[3]->start_balance *2 * 100)/100 ;
                                            $slotSettings->jpgs[3]->save();
                                        }else if (\intval($fireLinkTable[$a][$b]) == 5)
                                        {
                                            $freeSpinTotalWinScore += $slotSettings->jpgs[2]->balance* 100;
                                            $jackpotVal = $slotSettings->jpgs[2]->balance* 100;

                                            \VanguardLTE\StatGame::create([
                                                'user_id' =>$user->id , 
                                                'balance' => $slotSettings->GetBalance(), 
                                                'cashable_balance' => $slotSettings->GetCashableBalance(),
                                                'bet' => 0, 
                                                'win' => $slotSettings->jpgs[2]->balance, 
                                                'game' => 'community_prize_firelink', 
                                                'percent' => 0, 
                                                'percent_jps' => 0, 
                                                'percent_jpg' => 0, 
                                                'profit' => 0, 
                                                'shop_id' => $user->shop_id,
                                                'reels' => 'prize2'
                                            ]);

                                            $jackpotResponse = '{"pid": 10335,"jp_id": 2,"jpScore":'.($slotSettings->jpgs[2]->balance).'}'; ///////////
                                            $slotSettings->jpgs[2]->balance = $slotSettings->jpgs[2]->start_balance;
                                            $slotSettings->jpgs[2]->pay_sum = rand($slotSettings->jpgs[2]->start_balance*1.2 * 100,$slotSettings->jpgs[2]->start_balance *2 * 100)/100 ;
                                            $slotSettings->jpgs[2]->save();
                                        }else if (\intval($fireLinkTable[$a][$b]) == 4)
                                        {
                                            $freeSpinTotalWinScore += $slotSettings->jpgs[1]->balance* 100;
                                            $jackpotVal = $slotSettings->jpgs[1]->balance* 100;

                                            \VanguardLTE\StatGame::create([
                                                'user_id' =>$user->id , 
                                                'balance' => $slotSettings->GetBalance(), 
                                                'cashable_balance' => $slotSettings->GetCashableBalance(),
                                                'bet' => 0, 
                                                'win' => $slotSettings->jpgs[1]->balance, 
                                                'game' => 'community_prize_firelink', 
                                                'percent' => 0, 
                                                'percent_jps' => 0, 
                                                'percent_jpg' => 0, 
                                                'profit' => 0, 
                                                'shop_id' => $user->shop_id,
                                                'reels' => 'prize3'
                                            ]);

                                            $jackpotResponse = '{"pid": 10335,"jp_id": 1,"jpScore":'.($slotSettings->jpgs[1]->balance).'}'; ///////////
                                            $slotSettings->jpgs[1]->balance = $slotSettings->jpgs[1]->start_balance;
                                            $slotSettings->jpgs[1]->pay_sum = rand($slotSettings->jpgs[1]->start_balance*1.2 * 100,$slotSettings->jpgs[1]->start_balance *2 * 100)/100 ;
                                            $slotSettings->jpgs[1]->save();
                                        }else if (\intval($fireLinkTable[$a][$b]) == 3)
                                        {
                                            $freeSpinTotalWinScore += $slotSettings->jpgs[0]->balance* 100;
                                            $jackpotVal = $slotSettings->jpgs[0]->balance* 100;

                                            \VanguardLTE\StatGame::create([
                                                'user_id' =>$user->id , 
                                                'balance' => $slotSettings->GetBalance(), 
                                                'cashable_balance' => $slotSettings->GetCashableBalance(),
                                                'bet' => 0, 
                                                'win' => $slotSettings->jpgs[0]->balance, 
                                                'game' => 'community_prize_firelink', 
                                                'percent' => 0, 
                                                'percent_jps' => 0, 
                                                'percent_jpg' => 0, 
                                                'profit' => 0, 
                                                'shop_id' => $user->shop_id,
                                                'reels' => 'prize4'
                                            ]);
                                            
                                            $jackpotResponse = '{"pid": 10335,"jp_id": 0,"jpScore":'.($slotSettings->jpgs[0]->balance).'}'; ///////////
                                            $slotSettings->jpgs[0]->balance = $slotSettings->jpgs[0]->start_balance;
                                            $slotSettings->jpgs[0]->pay_sum = rand($slotSettings->jpgs[0]->start_balance*1.2 * 100,$slotSettings->jpgs[0]->start_balance *2 * 100)/100 ;
                                            $slotSettings->jpgs[0]->save();
                                        }
                                    }
                                }
                                if ($jackpotResponse != "")
                                {
                                    $jackpotTemp = '{"main":200,"type":209,"body":'.$jackpotResponse.'}';
                                    array_push($returnResponse,$jackpotTemp);
                                }
                                $gameType = 'normal';
                                $totalWin = $freeSpinTotalWinScore;
                                $freeSpinTotalWinScore -= $jackpotVal;
                                $fireLinkTable = [[0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0]];
                                $slotSettings->SetGameData('fireLinkTable',\json_encode($fireLinkTable));
                                $slotSettings->SetGameData('topLimit',4);
                                $slotSettings->SetBank('bonus', -($totalWin - $jackpotVal) * 0.01);
                                $slotSettings->SetWin(($totalWin - $jackpotVal) * 0.01);
                            }
                            $bodyResponse = '{"fCredit":'.$cashable_Balance.',"iBetIndex":'.$betIndex.',"iBet":'.$bet.',"iTotalLine":150,"iBetLine":50,"iBetPctIndex":0,"iBetPct":1,"fTotalBet":'.$totalBet.',"iBonusId":'.$bounsId.',"iWinType":0,"iAwardCount":0,"fCurWinScore":'.$curWinScore.',"iJackpotId":'.$jackpotId.',"iJackpotScore":'.$jackpotScore.',"iWinAllId":'.$winAllId.',"fWinAllScore":'.$winAllScore.',"iFreeSpinTotalTimes":'.$freeSpinTotalTimes.',"iFreeSpinTimes":'.$freeSpinTimes.',"iFreeSpinAddTimes":'.$freeSpinAddTimes.',"iFreeSpinPlusOdd":'.$freeSpinPlusOdds.',"iNextGameSpecialOdds":1,"iFreeGameMode":'.$freeGameMode.',"iFreeGameAgainTime":0,"fFreeSpinTotalWinScore":'.$freeSpinTotalWinScore.',"bChangeAward":false,"ary2_Reels":'.\json_encode($reelSymbols).',"iIsAddFreeGame":'.$isAddFreeGame.',"list_PrizeInfos":[],"list_ScatterInfos":[]}';
                            $freeSpinTotalTimes--;
                            $slotSettings->SetGameData('freeSpinTotalTimes',$freeSpinTotalTimes);
                            $slotSettings->SetGameData('freeSpinTimes',$freeSpinTimes);
                            $slotSettings->SetGameData('gameType',$gameType);
                        }
                        $response = '{"main":200,"type": 204,"body":'.$bodyResponse.'}'; //SUB_S_PLAYER_DATA:204
                        array_push($returnResponse,$response);
                        if ($totalWin > 0) 
                        {
                            $index = 0;
                            for($i = 0; $i < \count($cWinsTotal); $i++)
                            {
                                if ($cWinsTotal[$i] > 0)
                                {
                                    $k = 0;
                                    for(; $k < 5; $k++)
                                    {
                                        if ($winLine[$index][$k] != -1)
                                        {
                                            $k++;
                                            break;
                                        }
                                    }
                                    $payLine = $i;
                                    $bodyResponse = '{"id":'.($index + 100).',"line":'.$payLine.',"dir": 0,"num": '.$k.',"point": '.$cWinsTotal[$i].',"reward":'.$cWinsTotal[$i].',"lineFrom":'.\json_encode($winLine[$index]).'}';
                                    $response = '{"main": 200, "type": 205,"body":'.$bodyResponse.'}';
                                    array_push($returnResponse,$response);    
                                    $index++;
                                    // if ($index == 1)
                                    // {
                                    //     $response = '{"main":200,"type": 207,"body":""}';
                                    //     array_push($returnResponse,$response);    
                                    // }
                                }
                            }
                        }
                        if ($bounsId == 1 && $scatterLst != [-1,-1,-1,-1,-1]) 
                        {
                            $bodyResponse = '{"id":1,"line":1,"dir": 30,"num": 30,"point": 30,"reward":30,"lineFrom":'.\json_encode($scatterLst).'}';
                            $response = '{"main": 200, "type": 206,"body":'.$bodyResponse.'}';
                            array_push($returnResponse,$response);
                        }
                        if ($isLastFreeSpin != '')
                        {
                            //array_push($returnResponse,'{"main": 200,"type": 109,"body":'.$isLastFreeSpin.'}');
                        }
                        $response = '{"main":200,"type": 207,"body":""}';
                        array_push($returnResponse,$response);
                        $bodyResponse = '{"jackpot":['.$slotSettings->jpgs[0]->balance.','.$slotSettings->jpgs[1]->balance.','.$slotSettings->jpgs[2]->balance.','.$slotSettings->jpgs[3]->balance.'], "shop_id":"'.$slotSettings->shop_id.'"}';
                        $response = '{"main": 200,"type": 101,"body":'.$bodyResponse.'}';
                        array_push($returnResponse,$response);
                        $slotSettings->SetBalance($totalWin * 0.01,'bet');

                        $slotSettings->SaveLogReport(json_encode($reelSymbols), $logBet / 100, $totalWin / 100, $gameType);
                        //check community prize
                        // $prizeType = -1;
                        // $prizeBalance = 0;
                        // if ($slotSettings->jpgsMain[2]->balance >= $slotSettings->jpgsMain[2]->pay_sum)
                        // {
                        //     $prizeType = 2;
                        //     $prizeBalance = $slotSettings->jpgsMain[2]->balance;
                        //     $slotSettings->user->prize_type = $prizeType;
                        //     $slotSettings->user->prize_balance = $prizeBalance;
                        //     $slotSettings->user->save();
                        //     $slotSettings->jpgsMain[2]->balance = $slotSettings->jpgsMain[2]->start_balance;
                        //     $slotSettings->jpgsMain[2]->pay_sum = rand($slotSettings->jpgsMain[2]->start_balance*1.2 * 100,$slotSettings->jpgsMain[2]->start_balance *2 * 100)/100 ;
                        //     $slotSettings->jpgsMain[2]->save();
                        // } else if ($slotSettings->jpgsMain[3]->balance >= $slotSettings->jpgsMain[3]->pay_sum)
                        // {
                        //     $prizeType = 4;
                        //     $prizeBalance = $slotSettings->jpgsMain[3]->balance;
                        //     $slotSettings->user->prize_type = $prizeType;
                        //     $slotSettings->user->prize_balance = $prizeBalance;
                        //     $slotSettings->user->save();
                        //     $slotSettings->jpgsMain[3]->balance = $slotSettings->jpgsMain[3]->start_balance;
                        //     $slotSettings->jpgsMain[3]->pay_sum = rand($slotSettings->jpgsMain[3]->start_balance*1.2 * 100,$slotSettings->jpgsMain[3]->start_balance *2 * 100)/100 ;
                        //     $slotSettings->jpgsMain[3]->save();
                        // } else if ($slotSettings->jpgsMain[0]->balance >= $slotSettings->jpgsMain[0]->pay_sum || $slotSettings->jpgsMain[1]->balance >= $slotSettings->jpgsMain[1]->pay_sum)
                        // {
                        //     $jpgIndex = 0;
                        //     $prizeType = 1;
                        //     if ($slotSettings->jpgsMain[1]->balance >= $slotSettings->jpgsMain[1]->pay_sum)
                        //     {
                        //         $jpgIndex = 1;
                        //         $prizeType = 3;
                        //     }
                        //     $jsonStr = json_encode(\DB::select("SELECT NOW();")[0]);
                        //     $jsonStr = str_replace('{"NOW()":"','',$jsonStr);
                        //     $jsonStr = str_replace('"}','',$jsonStr);
                        //     $nowDate = new \DateTime($jsonStr);
                        //     $beforeTime = new \DateTime($jsonStr);
                        //     $beforeTime = $beforeTime->sub(new \DateInterval('P0Y0M0DT0H0M15S'));
                        //     $gameLogs = \VanguardLTE\StatGame::where('date_time','<=',$nowDate->format('Y-m-d H:i:s'))->where('date_time','>=',$beforeTime->format('Y-m-d H:i:s'))->groupBy('user_id')->get();
                        //     $userIdLst = array();
                        //     foreach($gameLogs as $gameLog)
                        //     {
                        //         if (!in_array($gameLog->user_id,$userIdLst))
                        //         {
                        //             array_push($userIdLst,$gameLog->user_id);
                        //         }   
                        //     }
                        //     if (\count($userIdLst) >= 1)
                        //     {
                        //         //$prizeType = $jpgIndex + 1;
                        //         $prizeBalance = $slotSettings->jpgsMain[$jpgIndex]->balance / \count($userIdLst);
                        //         foreach($userIdLst as $userId)
                        //         {
                        //             $tempUser = \VanguardLTE\User::where('id',$userId)->get();
                        //             if (\count($tempUser) >= 1)
                        //             {
                        //                 $tempUser[0]->prize_type = $prizeType;
                        //                 $tempUser[0]->prize_balance = $prizeBalance;
                        //                 $tempUser[0]->save();
                        //             }
                        //         }
                        //         $slotSettings->jpgsMain[$jpgIndex]->balance = $slotSettings->jpgsMain[$jpgIndex]->start_balance;
                        //         $slotSettings->jpgsMain[$jpgIndex]->pay_sum = rand($slotSettings->jpgsMain[$jpgIndex]->start_balance*1.2 * 100,$slotSettings->jpgsMain[$jpgIndex]->start_balance *2 * 100)/100 ;
                        //         $slotSettings->jpgsMain[$jpgIndex]->save();
                        //     }
                        // }
                        // if ($slotSettings->user->prize_type != -1 && $slotSettings->user->prize_balance != 0 && $gameType == 'normal')
                        // {
                        //     $slotSettings->SetBalance($slotSettings->user->prize_balance,'bet');
                        //     $bodyResponse = '{"reelIndex":'.$slotSettings->user->prize_type.',"win":'.$slotSettings->user->prize_balance.',"entry":'.($slotSettings->GetBalance() * 100).',"winning":'.($slotSettings->GetCashableBalance()  * 100).'}';
                        //     $response = '{"main": 200,"type": 998,"body":'.$bodyResponse.'}';
                        //     array_push($returnResponse,$response);

                        //     if (\Auth::user()->prize_type == 0)
                        //     {
                        //         $prizeType = "prize1";
                        //     }else if(\Auth::user()->prize_type == 1)
                        //     {
                        //         $prizeType = "prize2";
                        //     }else if (\Auth::user()->prize_type == 2)
                        //     {
                        //         $prizeType = "prize3";
                        //     }else if (\Auth::user()->prize_type == 3)
                        //     {
                        //         $prizeType = "prize4";
                        //     }
                        //     $prizeBalance = $slotSettings->user->prize_balance;
                            
                        //     \VanguardLTE\StatGame::create([
                        //         'user_id' =>\Auth::user()->id , 
                        //         'balance' => $slotSettings->GetBalance(), 
                        //         'cashable_balance' => $slotSettings->GetCashableBalance(),
                        //         'bet' => 0, 
                        //         'win' => $prizeBalance,
                        //         'game' => 'community_prize', 
                        //         'percent' => 0, 
                        //         'percent_jps' => 0, 
                        //         'percent_jpg' => 0, 
                        //         'profit' => 0, 
                        //         'shop_id' => intval(\Auth::user()->shop_id),
                        //         'reels' => $prizeType
                        //     ]);

                        //     $slotSettings->user->prize_type = -1;
                        //     $slotSettings->user->prize_balance = 0;
                        //     $slotSettings->user->save();
                        // }
                        //
                        break;
                    case "wait_bonus": //프리스핀 시작 초기화
                        $gameType = "freespin";
                        $slotSettings->SetGameData('gameType',$gameType);
                        $betIndex = $slotSettings->GetGameData('betIndex');
                        switch($betIndex)
                        {
                            case 0:
                                $totalBet = 50;
                                break;
                            case 1:
                                $totalBet = 100;
                                break;
                            case 2:
                                $totalBet = 150;
                                break;
                            case 3:
                                $totalBet = 250;
                                break;
                            case 4:
                                $totalBet = 500;
                                break;
                            default:
                                $totalBet = 50;
                                break;
                        }
                        $bet = $totalBet/ 50;
                        $jackpotId = -1;
                        $jackpotScore = 0;
                        $winAllId = 0;
                        $winAllScore = 0;
                        $freeSpinTotalTimes = 10;
                        $freeSpinTimes = 0;
                        $freeSpinTotalWinScore = 0;
                        $slotSettings->SetGameData('freeSpinTotalTimes',$freeSpinTotalTimes);
                        $slotSettings->SetGameData('freeSpinTimes',$freeSpinTimes);
                        $slotSettings->SetGameData('freeSpinTotalWinScore',$freeSpinTotalWinScore);
                        // $slotSettings->SetGameData('totalGuess',\rand(20*$totalBet,30 * $totalBet));
                        $freeSpinAddTimes = 0;
                        $freeSpinPlusOdds = 0;
                        $freeGameMode = 0;
                        $bodyResponse = '{"iBetIndex":'.$betIndex.',"iBet":'.$bet.',"iLines":150,"iBetPct":1,"iFreeSpinTotalTimes":'.$freeSpinTotalTimes.',"iFreeSpinTimes":'.$freeSpinTimes.',"iFSAddTimes":'.$freeSpinAddTimes.',"iNextGameSpecialOdds":2,"iFreeSpinPlusOdd":'.$freeSpinPlusOdds.',"fTotalBet":'.$totalBet.',"fFreeSpinTotalWinScore":'.$freeSpinTotalWinScore.'}';
                        $response = '{"main":200,"type": 211,"body":'.$bodyResponse.'}';
                        array_push($returnResponse,$response);
                        break;
                    case "wait_special":
                        $bounsId = 2;
                        $curWinScore = 0;
                        $betIndex = $slotSettings->GetGameData('betIndex');
                        $bet = $betIndex + 1;
                        $totalBet = $bet * 50;
                        $jackpotId = -1;
                        $jackpotScore = 0;
                        $winAllId = 0;
                        $winAllScore = 0;
                        $freeSpinAddTimes = 0;
                        $freeSpinPlusOdds = 0;
                        $freeGameMode = 1;
                        $freeSpinTotalWinScore = 0;
                        $gameType = "bonus";
                        $slotSettings->SetGameData('gameType',$gameType);
                        // $slotSettings->SetGameData('totalGuess',\rand(20*$totalBet,30 * $totalBet));
                        $bodyResponse = '{"iBetIndex":'.$betIndex.',"iBet":'.$bet.',"iLines":50,"iBetPct":1,"iFreeSpinTotalTimes":3,"iFreeSpinTimes": 0,"iFSAddTimes":'.$freeSpinAddTimes.',"iNextGameSpecialOdds":2,"iFreeSpinPlusOdd":'.$freeSpinPlusOdds.',"fTotalBet":'.$totalBet.',"fFreeSpinTotalWinScore":'.$freeSpinTotalWinScore.'}';
                        $response = '{"main":200,"type": 212,"body":'.$bodyResponse.'}';
                        array_push($returnResponse,$response);
                        break;
                }
            }catch (\Exception $e)
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
            $slotSettings->SaveGameData();
            \DB::commit();
            return \json_encode($returnResponse);
        }
    }
}
