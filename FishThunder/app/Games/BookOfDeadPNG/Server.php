<?php 
namespace VanguardLTE\Games\BookOfDeadPNG
{
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;
    use SimpleXMLElement;

    class Server
    {
        public $gameState;
        public $debug = false;

        function getConvertedLine($line)
        {
            $res = [];
            for($r = 0; $r < 5; $r++)
                for($c = 0; $c < 3; $c++)
                {
                    if($line[$r][$c] == 1)
                    {
                        $res[] = $c * 5 + $r;
                    }
                }
            $res = implode('|', $res);
            return $res;
        }

        function getBlockedPositions($block)
        {
            $res = [];
            for($r = 0; $r < 5; $r++)
                for($c = 0; $c < 5; $c++)
                    if($block['reel'.($r+1)][$c] == 1)
                        $res[] = $c * 5 + $r;
            $res = implode('|', $res);
            return $res;
        }

        function generateWagerId()
        {
            $id = date("ymdHms").round(microtime(true) * 1000) % 1000;
            return $id;
        }

        public function get($request, $game)
        {
            try
            {
                DB::beginTransaction();
                $userId = Auth::id();
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
                $getData = $_GET;
                $tempData = explode("\n", file_get_contents('php://input'));
                $response = "";
                if( trim($tempData[0]) == 'd=1' ) 
                {
                    $response = "d=103 \"uiExKAN80Re\"";
                }
                else if( trim($tempData[0]) == 'd=2' ) 
                {
                    $response = "d=103 \"uiExKAN80Re!195634\"\r\n 101 195634 \"DEMO\" \"\" \"\" \"user195634\" \"\"\r\n127 \"2022-12-13T13:52:24Z\"";
                }
                else if( trim($tempData[0]) == 'd=3' )
                {
                    $balanceInCents = $slotSettings->GetBalance() * 100;
                    $response = "d=104 1\r\n54 9 1 2 3 4 5 10 20 50 100 1\r\n57 \"<custom><RTP Value=\"96\" /></custom>\"\r\n60 96 0 0\r\n52 ".$balanceInCents." 0 0\r\n83 0\r\n91 196524 \"5HYKG2_PVwnMq-TynJSVglqiwz4-7P-pu_gxZ3SiB3bUeGAwuZuXzh4OD43QFq6Lm-Suf4HNv5y7p-6NozjeB_2bGMjmfAatYXxa7qH2wo_gj1HX1BDmx_uP2rrAW5y6o1fOSk6yiWaofhw2VVwMYIyZUzNN1C9YwF-iWZdgkhKgk6_BmulySF84qBUszmEgAqk9foD0RvS6BAV7jNKFWJUT7zc.\"\r\n109";
                }

                $reqType = '';
                if( isset($tempData[2]) && trim($tempData[2]) == '0' ) 
                {
                    $response = 'd=';
                }
                else if( isset($tempData[2]) ) 
                {
                    $gameData = explode(' ', $tempData[2]);
                    if($gameData[0] == 1)
                    {
                        $reqType = 'spin';
                    }
                    if($gameData[0] == 2)
                    {
                        //freespin process
                        if($gameData[1] == 6)
                        {
                            $reqType = 'dofreespin';
                        }                        
                    }
                    else if($gameData[0] == 4)
                    {
                        if($gameData[1] == 0)
                            $reqType = 'collect';
                        else
                        {
                            $reqType = 'gamble';
                        }
                    }
                    else if($gameData[0] == 7)
                    {
                        $reqType = 'freespin';
                    }
                    
                }
                if( isset($getData['command']) && $getData['command'] == 'Configuration_v2' ) 
                {
                    $reqType = 'configuration';                    
                }

                switch($reqType)
                {
                    case 'configuration':
                        $response = '{"hasMysteryJackpot":false,"multipleFreegamesPerGamesession":false,"hasGuaranteedJackpot":false,"jackpots":null,"disableSwipeToFullscreenPortraitIos":false,"disableSwipeToFullscreenLandscapeIos":false,"disableSwipeToFullscreenIos":false,"swipeToHideIosBlacklist":"{15}","defaultHyperSpin":false,"disableHyperSpin":true,"disableVideoActivationScreen":false,"alwaysShowDecimals":false,"useExternalBalanceOnly":false,"disableScrollToFullscreenMessage":false,"bundleMode":0,"disableInGameModals":false,"disableInGameFreeGamesModals":false,"disableFastplay":false,"unsupportedDeviceMessage":"This game is currently not supported by your device.","jackpotNotifications":true,"bgColor":"green","hideExit":false,"hideHelp":false,"hideHistory":false,"hideFastplay":false,"hideLobby":false,"hideSound":false,"hideAutoAdjustBet":false,"hideSpaceBarToSpin":false,"disableHistory":false,"disableHelp":false,"disableSound":false,"enforceRoundTime":false,"spinLimitEnabled":false,"spinLimitInterval":0,"spinLimitExpectedSpinTime":0,"spinLimitMinCooloffTime":0,"spinLimitMaxCooloffTime":0,"spinLimitFixedCooloffTime":0,"spinLimitDisplayCooloff":0,"spinLimitDisplayIdleTimeCountdown":0,"spinLimitIdleTimeCountdownThreshold":0,"spinLimitRoundIdleTimeEnabled":false,"spinLimitRoundIdleTimeTimerThreshold":3000,"regularSpinTime":2500,"minQuickRoundTime":-1,"autoPlayResume":false,"disableSpacebarToSpin":false,"resourceLevel":-1,"videoLevel":"-1","fps":0,"matchId":"","betMaxMode":0,"betMaxSpin":false,"playForRealDelay":-1,"renderer":"","disableExitInRound":false,"cId":"","defaultFastPlay":false,"defaultSpacebarToSpin":true,"defaultSound":true,"disableFastplayQuestion":false,"disableVideo":"0","requiredPlatformFeatureSupport":"StencilBuffer,EnforceHardwareAcceleration","customDeviceBlockRegex":"","debug":false,"debugAlert":false,"fullScreenMode":true,"defaultAutoAdjustBet":true,"defaultAutoSpins":"50","limits":"","autoSpins":"10,20,50,75,100","cashierUrl":"","lobbyUrl":"","mobileGameHistoryUrl":"/CasinoHistoryMobile","gameModules":"{\"bundleconfig\":{\"script\":\"\",\"resource\":\"resources/games/videoslot/bookofdead/config_${CHANNEL}.json\"}, \"featurepreview\":{\"script\":\"\",\"resource\":\"resources/games/videoslot/bookofdead/featurepreview_bundle.json\"}, \"game\":{\"script\":\"\",\"resource\":\"resources/games/videoslot/bookofdead/game_bundle.json\"}, \"ui\":{\"script\":\"games/videoslot/bookofdead/ui/desktop/bookofdead_viewfactory.js\",\"resource\":\"resources/games/videoslot/bookofdead/ui_${CHANNEL}_bundle.json\"}, \"mysteryjackpot\": {\"script\":\"\", \"resource\":\"resources/games/videoslot/bookofdead/mysteryjackpot_bundle.json\"}}","useBrowserStorage":0,"showWinUpTo":true,"isSocial":false,"availableModules":[],"uiVersion":"","gameURL":"games/videoslot/bookofdead/bookofdead_main.js","playForRealUrl":"","desktopGameHistoryUrl":"/CasinoHistory","hasInGameJackpots":false,"hasFreeInGameJackpots":false,"enforceShowGameName":false,"disableMobileBlurHandling":false,"integrationErrorCodes":"{\"IDS_IERR_UNKNOWN\":\"Internal error\",\"IDS_IERR_UNKNOWNUSER\":\"User unknown\",\"IDS_IERR_INTERNAL\":\"Internal error\",\"IDS_IERR_INVALIDCURRENCY\":\"Unknown currency\",\"IDS_IERR_WRONGUSERNAMEPASSWORD\":\"Unable to authenticate user\",\"IDS_IERR_ACCOUNTLOCKED\":\"Account is locked\",\"IDS_IERR_ACCOUNTDISABLED\":\"Account is disabled\",\"IDS_IERR_NOTENOUGHMONEY\":\"There is not enough funds on the account\",\"IDS_IERR_MAXCONCURRENTCALLS\":\"The system is currently under heavy load. Please try again later\",\"IDS_IERR_SPENDINGBUDGETEXCEEDED\":\"Your spending budget has been reached.\",\"IDS_IERR_SESSIONEXPIRED\":\"Session has expired. Please restart the game\",\"IDS_IERR_TIMEBUDGETEXCEEDED\":\"Your time budget has been reached\",\"IDS_IERR_SERVICEUNAVAILABLE\":\"The system is temporarily unavailable. Please try again later\",\"IDS_IERR_INVALIDIPLOCATION\":\"You are logging in from a restricted location. Your login has been denied.\",\"IDS_IERR_USERISUNDERAGE\":\"You are blocked from playing these games due to being underage. If you have any questions please contact your customer support\",\"IDS_IERR_SESSIONLIMITEXCEEDED\":\"Your session limit has been reached. Please exit the game and start a new game session to continue playing.\"}","autoplayReset":false,"autoplayLimits":false,"settings":"&settings=%3croot%3e%3csettings%3e%3cDenominations%3e%3cdenom+Value%3d%220.01%22+%2f%3e%3cdenom+Value%3d%220.02%22+%2f%3e%3cdenom+Value%3d%220.03%22+%2f%3e%3cdenom+Value%3d%220.04%22+%2f%3e%3cdenom+Value%3d%220.05%22+%2f%3e%3cdenom+Value%3d%220.1%22+%2f%3e%3cdenom+Value%3d%220.2%22+%2f%3e%3cdenom+Value%3d%220.5%22+%2f%3e%3cdenom+Value%3d%221%22+%2f%3e%3c%2fDenominations%3e%3c%2fsettings%3e%3c%2froot%3e","resourceRoot":"/games/BookOfDeadPNG/8.0.1-bookofdead.403/","showSplash":true,"showPoweredBy":true,"historyFilterGame":false,"loaderMessage":"","loaderMinShowDuration":0,"realityCheck":"","hasJackpots":false,"helpUrl":"/casino/gamehelp?pid=2&gameid=310&lang=en_US&brand=&jurisdiction=&context=&channel=desktop","showClientVersionInHelp":false,"showFFGamesVersionInHelp":false,"disableAutoplay":false,"enforceAutoplayStopAtFreeSpins":false,"enforceAutoplayStopAtBonus":false,"betLossPresentation":false,"waterMark":false,"displayClock":false,"disableSpinOnPaytable":false,"useServerTime":false,"rCmga":0,"minRoundTime":-1,"detailedFreegameMessage":false,"minSpinningTime":"","creditDisplay":0,"pingIncreaseInterval":0,"minPingTime":0,"maxPingTime":null,"baccaratHistory":7,"gameRoundBalanceCheck":false,"quickStopEnabled":true,"neverGamble":false,"autoHold":false,"denom":"20","brand":"common","defaultLimit":1,"freeGameEndLogout":false,"lines":10,"mjDemoText":"","mjsupportmessage":"","mjcongratulations":";","mjprizes":",,,","mjnames":"Mini,Minor,Major,Grand","freeSpinLimit":0,"rtpMin":"96.21","rtpMax":"96.21","rtpBest":"","observedMaxWin":"7529","observedMaxWinProbability":"0.0000000100"}';
                        break;
                    case 'collect':
                        $totalWin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalGameWin');
                        $coinWin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalCoinWin');
                        $slotSettings->SetBalance($totalWin);                        
                        $lastEvent = $slotSettings->GetGameData($slotSettings->slotId . 'LastEvent');
                        $allbet = $slotSettings->GetGameData($slotSettings->slotId . 'AllBet');                        
                        $balanceInCents = $slotSettings->GetBalance() * 100;                        
                        $response = "d=5 ".$coinWin."\r\n6 ".($totalWin * 100)."\r\n52 ".$balanceInCents." 0 0";
                        $slotSettings->SaveLogReport($response, 0, $totalWin, $lastEvent);
                        break;
                    case 'gamble':
                        $cards_suits = [
                            '4' => [0,1,2,3,4,5,6,7,8,9,10,11,12], //clover
                            '5' => [13,14,15,16,17,18,19,20,21,22,23,24,25], //diamond
                            '6' => [26,27,28,29,30,31,32,33,34,35,36,37,38], //spade
                            '7' => [39,40,41,42,43,44,45,46,47,48,49,50,51] //heart
                        ];
                        $cards_color = [
                            '1' => [13,14,15,16,17,18,19,20,21,22,23,24,25,39,40,41,42,43,44,45,46,47,48,49,50,51], //red
                            '3' => [0,1,2,3,4,5,6,7,8,9,10,11,12,26,27,28,29,30,31,32,33,34,35,36,37,38] //black
                        ];

                        $totalWin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalGameWin');
                        $coinWin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalCoinWin');
                        $isGambleWin = 0;
                        if($coinWin == 2500 || $slotSettings->GetGameData($slotSettings->slotId . 'GambleUp') == 5 )
                        {
                            $isGambleWin = 0;
                            $slotSettings->SetGameData($slotSettings->slotId . 'TotalGameWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'TotalCoinWin', 0);                            
                        }
                        else if($totalWin * 2 <= $slotSettings->user->spin_bank && rand(0, 100) < 50)
                        {
                            $isGambleWin = 1;
                            $totalWin *= 2;
                            $coinWin *= 2;
                            $slotSettings->SetGameData($slotSettings->slotId . 'GambleUp', $slotSettings->GetGameData($slotSettings->slotId . 'GambleUp') + 1);
                            $slotSettings->SetGameData($slotSettings->slotId . 'TotalGameWin', $totalWin);
                            $slotSettings->SetGameData($slotSettings->slotId . 'TotalCoinWin', $coinWin);
                        }

                        $card = '';
                        $card_set = [];
                        if($gameData[1] == 1 || $gameData[1] == 3)
                            $card_set = $cards_color;
                        else
                            $card_set = $cards_suits;

                        $set_keys = array_keys($card_set);
                        if($isGambleWin == 0)
                        {
                            $random_set = $set_keys[rand(0, count($set_keys) - 1)];
                            while($random_set == $gameData[1])
                            {
                                $random_set = $set_keys[rand(0, count($set_keys) - 1)];
                            }
                            $cnt = count($card_set[$random_set]);
                            $card = $card_set[$random_set][rand(0, $cnt - 1)];

                            $balanceInCents = $slotSettings->GetBalance() * 100;
                            $response = "d=4 1 0 0 ".$card." 0\r\n6 0\r\n52 ".$balanceInCents." 0 0";
                        }
                        else
                        {
                            $random_set = $set_keys[rand(0, count($set_keys) - 1)];
                            while($random_set != $gameData[1])
                            {
                                $random_set = $set_keys[rand(0, count($set_keys) - 1)];
                            }
                            $cnt = count($card_set[$random_set]);
                            $card = $card_set[$random_set][rand(0, $cnt - 1)];
                            $response = "d=4 1 ".$gameData[2]." ".$coinWin." ".$card." 0";
                        }
                        
                        break;
                    case 'spin':
                        $nCoins = $gameData[1];
                        $lines = $gameData[2];
                        $coinValue = $gameData[3] / 100;

                        $allbet = $coinValue * $nCoins * $lines;
                        $postData['slotEvent'] = 'bet';
                        
                        if( $postData['slotEvent'] == 'bet' ) 
                        {
                            $slotSettings->SetBalance(-1 * $allbet, $postData['slotEvent']);
                            $slotSettings->UpdateJackpots($allbet);
                            $slotSettings->SetBet($allbet);
                            $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'AllBet', $allbet);
                            $slotSettings->SetGameData($slotSettings->slotId . 'TotalGameWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'BonusSymbol', -1);
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'TotalCoinWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'GambleUp', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bet');
                        }                                              
                        $response = $this->doSpin($slotSettings, $gameData, $postData);                        
                        // $response = "d=1 1 10 20 9 3 2 0 3 9 4 5 6 6 7 1 2 6 9 1 0 1 1 5 4 6 0 2 4 0 4\r\n2 0 0 9 3 0\r\n2 2 0 20\r\n2 2 1 10\r\n2 2 6 7\r\n2 5 1\r\n";
                        break;
                    
                    case 'dofreespin':
                        // $response = "d=2 6\r\n3 0 20 400 10 1\r\n1 1 10 20 4 3 5 2 6 0 1 2 7 7 5 0 0 2 1 1 0 6 0 8 5 0 5 6 9 3 4\r\n2 0 0 7 2 0\r\n2 2 0 50\r\n2 5 0\r\n3 0 70 1400 9 1\r\n1 1 10 20 4 3 5 3 0 1 1 4 9 0 2 6 4 2 7 0 0 6 0 2 2 5 1 5 5 3 4\r\n3 0 70 1400 8 1\r\n1 1 10 20 6 7 5 0 2 3 4 0 5 2 3 4 7 6 0 1 0 2 2 6 0 6 2 1 0 4 1\r\n2 0 0 7 2 0\r\n2 2 0 50\r\n2 5 0\r\n3 0 120 2400 7 1\r\n1 1 10 20 0 1 7 3 1 2 0 5 2 1 3 4 4 8 0 0 0 6 3 2 7 4 6 4 1 1 6\r\n3 0 120 2400 6 1\r\n1 1 10 20 1 7 3 0 1 4 6 4 0 9 5 3 0 6 4 0 0 0 6 9 5 1 5 0 4 8 1\r\n3 0 120 2400 5 1\r\n1 1 10 20 5 0 6 8 2 6 9 1 6 9 5 3 1 5 0 0 2 2 6 3 0 30 8 6 2 0 5 3 3 4 0 4 4 0 4 8 4\r\n3 0 155 3100 4 1\r\n1 1 10 20 6 7 5 3 9 6 0 7 1 1 2 3 4 5 1 1 3 0 7 3 1 40 3 6 2 1 5 4 5 2 1 5 2 2 2 0 5 0 4 4 7 0\r\n2 0 0 7 2 0\r\n2 2 0 50\r\n2 5 0\r\n3 0 255 5100 3 1\r\n1 1 10 20 6 7 5 5 2 3 1 9 0 3 6 1 3 0 2 0 0 2 2 4 9 3 6 1 2 4 1\r\n3 0 255 5100 2 1\r\n1 1 10 20 6 7 5 2 3 9 5 0 7 0 8 5 4 5 1 1 4 2 5 2 1 5 6 7 3 1 40 8 5 2 1 5 9 7 2 1 5 2 2 5 6 7 1 5 3 7 0\r\n2 0 0 7 2 0\r\n2 2 0 50\r\n2 5 0\r\n3 0 360 7200 1 1\r\n1 1 10 20 1 0 4 5 2 4 1 8 2 6 5 3 3 1 7 0 0 6 2 0 1 6 5 3 2 7 2\r\n3 1 360 7200 0 1\r\n";
                        $postData['slotEvent'] = 'freespin';
                        $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'freespin');
                        $curCoinWin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalCoinWin');
                        $curWin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalGameWin');
                        $freespinLeft = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame'); 
                        $response = "d=2 6\r\n3 0 ".$curCoinWin." ".($curWin * 100)." ".$freespinLeft." 1\r\n";
                        while($freespinLeft > 0)
                        {
                            $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);
                            $response .= $this->doSpin($slotSettings, $gameData, $postData);
                            $freespinLeft = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame'); 
                            $response .= "\r\n";
                        }                        
                        
                        break;
                    case 'freespin':
                        $response = "d=83 ".$gameData[1];
                        break;
                }                
                $slotSettings->SaveGameData();
                $slotSettings->SaveGameDataStatic();
                DB::commit();          
                return $response;                     
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

        function doSpin($slotSettings, $gameData, $postData)
        {
            if($postData['slotEvent'] == 'bet')
            {               
                $nCoins = $gameData[1];
                $lines = $gameData[2];
                $coinValue = $gameData[3] / 100;    
            }
            else
            {
                $nCoins = $slotSettings->GetGameData($slotSettings->slotId . 'nCoins');
                $lines = $slotSettings->GetGameData($slotSettings->slotId . 'lines');
                $coinValue = $slotSettings->GetGameData($slotSettings->slotId . 'coinValue');
            }            

            $allbet = $coinValue * $nCoins * $lines;

            $linesId = $slotSettings->GetPaylines($lines);
            $betLine = $nCoins * $coinValue;

            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $allbet);
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
            
            $spinAcquired = false;             
            $gameWin = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin');            

            if($this->debug && $postData['slotEvent'] != 'freespin')
            {                 
                $winType = 'bonus';
            }

            $minReels = [];
            $minLineWins = [];
            $minTotalWin = -1;
            $minFreespinsWon = 0;
            $minScatterCount = 0;
            $minBonusCount = 0;            

            $totalWin = 0;
            $freespinsWon = 0;
            $lineWins = [];
            $reels = [];
            
            $scatter = 9;
            $scatterCount = 0;
            $bonusCount = 0;
            $wild = [9];
            $paytable = $slotSettings->Paytable;
            for( $i = 0; $i <= 500; $i++ ) 
            {
                $totalWin = 0;
                $freespinsWon = 0;
                $scatterCount = 0;
                $bonusCount = 0;
                $scatterPos = [];
                $lineWins = [];
                $cWins = array_fill(0, $lines, 0);
                
                $reels = $slotSettings->GetReelStrips($winType, $lines, $slotSettings->GetGameData($slotSettings->slotId . 'BonusSymbol'));
                $bonusMpl = 1;
                
                for( $k = 0; $k < $lines; $k++ ) 
                {
                    $mpl = 1;
                    $winline = [];
                    for( $j = 0; $j < count($slotSettings->SymbolGame); $j++ ) 
                    {
                        $csym = $slotSettings->SymbolGame[$j];
                        if( !isset($paytable[$csym]) ) 
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

                            if(($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild))) 
                            {
                                $wildCnt = 0;
                                if($s[0] == $wild[0])   $wildCnt++;
                                if($s[1] == $wild[0])   $wildCnt++;
                                $coin = $paytable[$csym][2] * $nCoins;
                                $tmpWin = $paytable[$csym][2] * $betLine * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $cWins[$k] = $tmpWin;                                    
                                    $winline = [$k, $csym, 2, $wildCnt, $tmpWin, $coin];
                                }
                            }
                                                                                
                            if(($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) ) 
                            {
                                $wildCnt = 0;
                                if($s[0] == $wild[0])   $wildCnt++;
                                if($s[1] == $wild[0])   $wildCnt++;
                                if($s[2] == $wild[0])   $wildCnt++;
                                $coin = $paytable[$csym][3] * $nCoins;
                                $tmpWin = $paytable[$csym][3] * $betLine * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $cWins[$k] = $tmpWin;                                    
                                    $winline = [$k, $csym, 3, $wildCnt, $tmpWin, $coin];
                                }
                            }

                            if(($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild))  && ($s[3] == $csym || in_array($s[3], $wild)) ) 
                            {
                                $wildCnt = 0;
                                if($s[0] == $wild[0])   $wildCnt++;
                                if($s[1] == $wild[0])   $wildCnt++;
                                if($s[2] == $wild[0])   $wildCnt++;
                                if($s[3] == $wild[0])   $wildCnt++;
                                $coin = $paytable[$csym][4] * $nCoins;
                                $tmpWin = $paytable[$csym][4] * $betLine * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $cWins[$k] = $tmpWin;                                    
                                    $winline = [$k, $csym, 4, $wildCnt, $tmpWin, $coin];
                                }
                            }

                            if(($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[4] == $csym || in_array($s[4], $wild))) 
                            {
                                $wildCnt = 0;
                                if($s[0] == $wild[0])   $wildCnt++;
                                if($s[1] == $wild[0])   $wildCnt++;
                                if($s[2] == $wild[0])   $wildCnt++;
                                if($s[3] == $wild[0])   $wildCnt++;
                                if($s[4] == $wild[0])   $wildCnt++;
                                $coin = $paytable[$csym][5] * $nCoins;
                                $tmpWin = $paytable[$csym][5] * $betLine * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $cWins[$k] = $tmpWin;                                    
                                    $winline = [$k, $csym, 5, $wildCnt, $tmpWin, $coin];
                                }
                            }                            
                        }
                    }

                    if( $cWins[$k] > 0 && !empty($winline))
                    {
                        array_push($lineWins, $winline);
                        $totalWin += $cWins[$k];
                    }
                }

                //calculate bonus symbol count                
                for($r = 1; $r <= 5; $r++)
                {
                    for($c = 0; $c < 3; $c++)
                    {
                        if($reels['reel'.$r][$c] == $scatter)
                        {
                            $scatterCount++;
                            $scatterPos[] = $c * 5 + $r - 1;
                        }                            
                    }
                }

                if($winType != 'bonus' && $scatterCount > 2)
                    continue;

                $scattersWin = 0;
                if($scatterCount > 2)
                {
                    $freespinsWon = 10;
                    $coin = $paytable[$scatter][$scatterCount] * 10;
                    $scattersWin = $coin * $betLine;                    
                }

                $totalWin += $scattersWin;

                if($postData['slotEvent'] == 'freespin')
                {
                    //check bonus sym
                    $bonusSym = $slotSettings->GetGameData($slotSettings->slotId . 'BonusSymbol');
                    for($r = 0; $r < 5; $r++)
                        for($c = 0; $c < 3; $c++)
                        {
                            if($reels['reel'.($r+1)][$c] == $bonusSym)
                                $bonusCount++;
                        }
                    if ($bonusCount > 5)
                        $bonusCount = 5;
                    $bonusCoin = $slotSettings->Paytable[$bonusSym][$bonusCount] * 10;
                    $bonusWin = $bonusCoin * $betLine;                    
                    $totalWin += $bonusWin;
                }


                if($minTotalWin == -1 && $scatterCount < 3 || ($minTotalWin > $totalWin && $totalWin > 0))
                {
                    $minTotalWin = $totalWin;
                    $minLineWins = $lineWins;
                    $minFreespinsWon = $freespinsWon;
                    $minReels = $reels;
                    $minScatterCount = $scatterCount;
                    $minBonusCount = $bonusCount;
                }

                if($this->debug)
                {
                    $spinAcquired = true;
                    break;
                }                    

                if($totalWin <= $spinWinLimit && (($totalWin > 0 && $winType != 'none') || ($winType == 'bonus' && $freespinsWon > 0)))
                {
                    $spinAcquired = true;
                    if($totalWin < 0.4 * $spinWinLimit && $winType != 'bonus')
                        $spinAcquired = false;
                    if($spinAcquired)
                        break;                                        
                }                                          
                else if( $winType == 'none' && $totalWin == $gameWin ) 
                {
                    break;
                }
            }

            if(!$spinAcquired && $totalWin > $gameWin && $winType != 'none' || ($winType != 'bonus' && $scatterCount > 2))
            {                
                $reels = $minReels;
                $lineWins = $minLineWins;
                $totalWin = $minTotalWin;
                $freespinsWon = $minFreespinsWon;
                $scatterCount = $minScatterCount;
                $bonusCount = $minBonusCount;
            }

            $responseCmds = [];

            $responseCmds[] = $nCoins; //coin count
            $responseCmds[] = $lines; //lines
            $responseCmds[] = $coinValue * 100; //coin value

            for($r = 0; $r < 5; $r++)
                for($c = 0; $c < 3; $c++)
                    $responseCmds[] = $reels['reel'.($r+1)][$c]; //insert reels

            if($postData['slotEvent'] == 'bet')
            {
                $responseCmds[] = $freespinsWon > 0 ? 1 : 0;
            }
            else
            {
                $responseCmds[] = 1;
            }
            

            $winlineCnt = count($lineWins);
            $responseCmds[] = $winlineCnt;

            $coins = 0;
            $bonusSym = '';
            if($postData['slotEvent'] == 'freespin')
                $bonusSym = $slotSettings->GetGameData($slotSettings->slotId . 'BonusSymbol');

            if($winlineCnt > 0)
            {
                foreach($lineWins as $lineWin)
                {
                    $responseCmds[] = $lineWin[0];
                    $responseCmds[] = $lineWin[1];
                    $responseCmds[] = $lineWin[2];
                    $responseCmds[] = $lineWin[3];
                    $responseCmds[] = $lineWin[5];                 
                    $coins += $lineWin[5];
                }
            }

            if($totalWin > 0)
            {
                $slotSettings->SetWin($totalWin);
            }
            
            for($r = 0; $r < 5; $r++)
            {
                //insert adjacent symbols for every reel for top and below
                $responseCmds[] = rand(0, 8);
                $responseCmds[] = rand(0, 8);
            }

            $balanceInCents = $slotSettings->GetBalance() * 100;           

            if($postData['slotEvent'] == 'bet')
            {         
                $response = "d=1 " . implode(' ', $responseCmds);       
                if($freespinsWon > 0)
                {
                    $scatterCoin = $slotSettings->Paytable[$scatter][$scatterCount] * 10;
                    $coins += $scatterCoin;
                    //trigger freespin
                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                    $slotSettings->SetGameData($slotSettings->slotId . 'CoinBeforeFreespin', $coins);
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $freespinsWon);

                    $slotSettings->SetGameData($slotSettings->slotId . 'nCoins', $nCoins);
                    $slotSettings->SetGameData($slotSettings->slotId . 'lines', $lines);
                    $slotSettings->SetGameData($slotSettings->slotId . 'coinValue', $coinValue);
    
                    $bonusSym = rand(0, 8);
                    $slotSettings->SetGameData($slotSettings->slotId . 'BonusSymbol', $bonusSym);
                    $response .= "\r\n2 0 0 9 ".$scatterCount." 0\r\n2 2 0 ".$scatterCoin."\r\n2 2 1 ".$freespinsWon."\r\n2 2 6 ".$bonusSym."\r\n2 5 1\r\n";            
                }
                else
                {
                    if($totalWin > 0)
                    {
                        $response .= "\r\n3 1 ".$coins." ".($totalWin * 100)." 0 1";
                    }
                    else
                        $response .= "\r\n3 1 0 0 0 1\r\n6 0\r\n52 ".$balanceInCents." 0 0";
                }
                $slotSettings->SetGameData($slotSettings->slotId . 'TotalGameWin', $totalWin);
                $slotSettings->SetGameData($slotSettings->slotId . 'TotalCoinWin', $coins);
            }
            else
            {
                $response = "1 " . implode(' ', $responseCmds);
                if($freespinsWon > 0)
                {
                    $scatterCoin = $slotSettings->Paytable[$scatter][$scatterCount] * 10;
                    $coins += $scatterCoin;
                    //add freespin
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') + $freespinsWon);                    
                    $response .= "\r\n2 0 0 9 ".$scatterCount." 0\r\n2 2 0 ".$scatterCoin."\r\n2 2 1 ".$freespinsWon."\r\n2 5 0";            
                }
                if($bonusCount > 0 && $slotSettings->Paytable[$bonusSym][$bonusCount] > 0)
                {
                    $bonusCoin = $slotSettings->Paytable[$bonusSym][$bonusCount] * 10;
                    $coins += $bonusCoin;
                    $response .= "\r\n2 0 0 ".$bonusSym." ".$bonusCount." 0\r\n2 2 0 ".$bonusCoin."\r\n2 5 0";
                }
                
                $slotSettings->SetGameData($slotSettings->slotId . 'TotalGameWin', $slotSettings->GetGameData($slotSettings->slotId . 'TotalGameWin') + $totalWin);
                $slotSettings->SetGameData($slotSettings->slotId . 'TotalCoinWin', $slotSettings->GetGameData($slotSettings->slotId . 'TotalCoinWin') + $coins);

                $totalCoinWin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalCoinWin');
                $totalGameWin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalGameWin');
                $freespinLeft = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame'); 
                $canGamble = 0;
                if($freespinLeft == 0)
                    $canGamble = 1;
                $response .= "\r\n3 ".$canGamble." ".$totalCoinWin." ".($totalGameWin * 100)." ".$freespinLeft." 1";
            }
            
            if($postData['slotEvent'] == 'bet')
            {
                $slotSettings->SaveLogReport($response, $allbet, $postData['slotEvent']);    
            }            
            
            return $response;
        }
    }
}


