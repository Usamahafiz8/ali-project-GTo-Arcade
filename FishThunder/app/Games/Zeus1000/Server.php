<?php 
namespace VanguardLTE\Games\Zeus1000
{
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;
    use SimpleXMLElement;

    class Server
    {
        public $debug = false;
        function getConvertedLine($line, $cols)
        {
            $res = [];
            for($r = 0; $r < 5; $r++)
                for($c = 0; $c < $cols; $c++)
                {
                    if($line[$r][$c] == 1)
                    {
                        $res[] = $c * 5 + $r;
                    }
                }
            $res = implode('|', $res);
            return $res;
        }

        function generateWagerId()
        {
            $id = date("ymdHms").round(microtime(true) * 1000) % 1000;
            return $id;
        }
        
        function arrayToXml($array, $rootElement = null, $xml = null) 
        {
            $_xml = $xml;
              
            // If there is no Root Element then insert root
            if ($_xml === null) {
                $_xml = new SimpleXMLElement($rootElement !== null ? $rootElement : '<root/>');
            }
              
            // Visit all key value pair
            foreach ($array as $k => $v) {
                  
                // If there is nested array then
                if (is_array($v)) { 
                    // Call function for nested array
                    $this->arrayToXml($v, $k, $_xml->addChild($k));
                }
                      
                else {
                      
                    // Simply add child element. 
                    $_xml->addChild($k, $v);
                }
            }
              
            return $_xml;
        }

        function sxml_append(SimpleXMLElement $to, SimpleXMLElement $from) {
            $toDom = dom_import_simplexml($to);
            $fromDom = dom_import_simplexml($from);
            $toDom->appendChild($toDom->ownerDocument->importNode($fromDom, true));
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
                
                if(isset($request->cmd))
                {
                    if($request->cmd == 'manifest')
                    {
                        switch($request->appcode)
                        {
                            case "capabilities-detector":
                                return '{"javascripts":["/games/Zeus1000/d3bneyhc3uesob.cloudfront.net/5.0.0.18-1663603863/resource-service/mainjs/application/capabilitiesdetector.CapabilitiesDetectorBootstrapper.js?resourceversion=5.0.0.18-1663603863&appcode=capabilities-detector","/games/Zeus1000/d3bneyhc3uesob.cloudfront.net/5.0.0.18-1663603863/resource-service/translationjs/bundles/translations/#{locale}/capabilitiesdetector.LocalizationBundle.js?resourceversion=5.0.0.18-1663603863&appcode=capabilities-detector"],"jsons":["/games/Zeus1000/d3bneyhc3uesob.cloudfront.net/5.0.0.18-1663603863/resource-service/translationjson/bundles/translations/#{locale}/capabilitiesdetector.LocalizationBundle.json?resourceversion=5.0.0.18-1663603863&appcode=capabilities-detector"],"main_class":"capabilitiesdetector.CapabilitiesDetectorBootstrapper","supported_locales":["it_IT","ru_RU","el_GR","pl_PL","ro_RO","tr_TR","fr_FR","cs_CZ","hu_HU","ca_ES","de_DE","sk_SK","es_ES","nl_NL","fr_CA","sv_SE","da_DK","bg_BG","hr_HR","fi_FI","en_GB","et_EE","lt_LT","lv_LV","sl_SI","no_NO","pt_PT"],"default_locale":"en"}';
                            break;
                            case "gls-platform":
                                return '{"javascripts":["/games/Zeus1000/d3bneyhc3uesob.cloudfront.net/5.0.0.18-1663603863/resource-service/mainjs/application/glsplatform.GlsPlatform.js?resourceversion=5.0.0.18-1663603863&appcode=gls-platform","/games/Zeus1000/lon-pt-mob.wi-gameserver.com/resource-service/metadatajs/bundles/metadata/glsplatform.MetaDataBundle.js?resourceversion=5.0.0.18-1663603863&appcode=gls-platform&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=zeus1000_gls&locale=en_US&webaudio=true","/games/Zeus1000/d3bneyhc3uesob.cloudfront.net/5.0.0.18-1663603863/resource-service/translationjs/bundles/translations/#{locale}/glsplatform.LocalizationsBundle.js?resourceversion=5.0.0.18-1663603863&appcode=gls-platform"],"jsons":["/games/Zeus1000/d3bneyhc3uesob.cloudfront.net/5.0.0.18-1663603863/resource-service/metadatajson/bundles/metadata/glsplatform.MetaDataBundle.json?resourceversion=5.0.0.18-1663603863&appcode=gls-platform&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=zeus1000_gls&locale=en_US&webaudio=true","/games/Zeus1000/d3bneyhc3uesob.cloudfront.net/5.0.0.18-1663603863/resource-service/translationjson/bundles/translations/#{locale}/glsplatform.LocalizationsBundle.json?resourceversion=5.0.0.18-1663603863&appcode=gls-platform"],"main_class":"glsplatform.GlsPlatform","supported_locales":["it_IT","ru_RU","el_GR","pl_PL","ro_RO","tr_TR","fr_FR","cs_CZ","hu_HU","ca_ES","de_DE","sk_SK","es_ES","nl_NL","fr_CA","sv_SE","da_DK","bg_BG","hr_HR","fi_FI","en_GB","et_EE","lt_LT","lv_LV","sl_SI","no_NO","pt_PT"],"default_locale":"en"}';
                                break;
                            case "lean-regular-partner-adapter":
                                return '{"javascripts":["/games/Zeus1000/d3bneyhc3uesob.cloudfront.net/5.0.0.18-1663603863/resource-service/mainjs/application/leanpartneradapter.LeanPartnerAdapter.js?resourceversion=5.0.0.18-1663603863&appcode=lean-regular-partner-adapter","/games/Zeus1000/lon-pt-mob.wi-gameserver.com/resource-service/metadatajs/bundles/metadata/leanpartneradapter.MetaDataBundle.js?resourceversion=5.0.0.18-1663603863&appcode=lean-regular-partner-adapter&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=zeus1000_gls&locale=en_US&webaudio=true","/games/Zeus1000/d3bneyhc3uesob.cloudfront.net/5.0.0.18-1663603863/resource-service/translationjs/bundles/translations/#{locale}/leanpartneradapter.LocalizationsBundle.js?resourceversion=5.0.0.18-1663603863&appcode=lean-regular-partner-adapter"],"jsons":["/games/Zeus1000/d3bneyhc3uesob.cloudfront.net/5.0.0.18-1663603863/resource-service/metadatajson/bundles/metadata/leanpartneradapter.MetaDataBundle.json?resourceversion=5.0.0.18-1663603863&appcode=lean-regular-partner-adapter&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=zeus1000_gls&locale=en_US&webaudio=true","/games/Zeus1000/d3bneyhc3uesob.cloudfront.net/5.0.0.18-1663603863/resource-service/translationjson/bundles/translations/#{locale}/leanpartneradapter.LocalizationsBundle.json?resourceversion=5.0.0.18-1663603863&appcode=lean-regular-partner-adapter"],"main_class":"leanpartneradapter.LeanPartnerAdapter","supported_locales":["it_IT","ru_RU","el_GR","pl_PL","ro_RO","tr_TR","fr_FR","cs_CZ","hu_HU","ca_ES","de_DE","sk_SK","es_ES","nl_NL","fr_CA","sv_SE","da_DK","bg_BG","hr_HR","fi_FI","en_GB","et_EE","lt_LT","lv_LV","sl_SI","no_NO","pt_PT"],"default_locale":"en"}';
                                break;
                            case "zeus1000_gls":
                                return '{
                                    "default_locale": "en",
                                    "javascripts": [
                                        "/games/Zeus1000/d3bneyhc3uesob.cloudfront.net/5.0.0.18-1663603863/resource-service/content/zeus1000_gls/js/zeus1000_gls.game.js?resourceversion=5.0.0.18-1663603863&appcode=zeus1000_gls",
                                        "content/zeus1000_gls/js/zeus1000_gls.MetaDataBundle.js?resourceversion=5.0.0.18-1663603863&appcode=zeus1000_gls&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=zeus1000_gls&locale=en_US&webaudio=true"
                                    ],
                                    "main_class": "zeus1000_gls.Game",
                                    "supported_locales": [
                                        "hr",
                                        "ro",
                                        "ca",
                                        "tr",
                                        "no",
                                        "hu",
                                        "lv",
                                        "lt",
                                        "de",
                                        "fi",
                                        "bg",
                                        "fr",
                                        "sv",
                                        "sl",
                                        "sk",
                                        "da",
                                        "it",
                                        "cs",
                                        "el",
                                        "pt",
                                        "pl",
                                        "en",
                                        "ru",
                                        "et",
                                        "es",
                                        "nl"
                                    ]
                                
                                
                                }
                                ';
                                break;
                        }
                        
                    }
                }
                $postData = simplexml_load_string($request->getContent());                
                $reqType = (string)$postData['type'];
                $reportWin = 0;
                
                switch( $reqType ) 
                {
                    case 'Init':         
                        $filename = base_path() . '/app/Games/Zeus1000/game.txt';
                        $file = fopen($filename, "r" );
                        $filesize = filesize( $filename );
                        $response = fread( $file, $filesize );
                        $response = str_replace('BAL_REPLACE', $slotSettings->GetBalance() * 100, $response);
                        fclose( $file );

                        $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', '');
                        $slotSettings->SetGameData($slotSettings->slotId . 'TotalFreespinWin', '0');
                        $slotSettings->SetGameData($slotSettings->slotId . 'FeatureIndex', 0); 
                        break;                        
                    case 'Logic':                        
                        $allbet = (int)$postData->Stake['total'] * 0.01;
                        $postData['slotEvent'] = 'bet';                        
                        $spinStatus = $slotSettings->GetGameData($slotSettings->slotId . 'SpinStatus');
                        if($spinStatus == 'Freespin')
                        {
                            $postData['slotEvent'] = 'freespin';
                            $totalFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                            $currentFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');                
                            $leftSpin = $totalFreespin - $currentFreespin;
                            if($leftSpin == 0)
                            {
                                $gameResultStr = $slotSettings->GetGameData($slotSettings->slotId . 'BGRecovery');
                                $sxe = new SimpleXMLElement('<GameResponse type="EndGame"></GameResponse>');
                                $header = simplexml_load_string('<Header sessionID="T0RuuLGLoWQdxR86YwRCpFd/kZ6/qUZHDUsIxT/KRWUuILGsBZXA4GYIpqmFXONl" ccyCode="GBP" deciSep="." thousandSep="," lang="en_US" gameID="20117" versionID="1_0" fullVersionID="unknown" isRecovering="N"/>');
                                $balance = simplexml_load_string('<Balances><Balance name="CASH_BALANCE" value="'.($slotSettings->getBalance() * 100).'"/></Balances>');
                                $gameResult = simplexml_load_string($gameResultStr);
                                $totalWagerWin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalGameWin');
                                $freespinTriggerWin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalFreespinWin');              
                                $fi = simplexml_load_string('<Feature index="0"><FS_Info totalGameWins="'.($totalWagerWin*100).'" winTopUp="0" entryWin="'.($freespinTriggerWin * 100).'" fsPlayed="Y" /></Feature>');
                                $this->sxml_append($gameResult, $fi);
                                $this->sxml_append($sxe, $header);
                                $this->sxml_append($sxe, $balance);
                                $this->sxml_append($sxe, $gameResult);
                                $response = $sxe->asXML();
                                $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', '');
                                $slotSettings->SetGameData($slotSettings->slotId . 'BGRecovery', '');
                                break;
                            }
                            
                        }
                        
                        if( $postData['slotEvent'] != 'freespin' ) 
                        {
                            $slotSettings->SetBalance(-1 * $allbet, $postData['slotEvent']);
                            $bankSum = $allbet / 100 * $slotSettings->GetPercent();
                            $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $bankSum, $postData['slotEvent']);
                            $slotSettings->UpdateJackpots($allbet);
                            $slotSettings->SetBet($allbet);
                            $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', 0);                            
                            $slotSettings->SetGameData($slotSettings->slotId . 'TotalGameWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'TotalFreespinWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'FrawnyZeusPlayed', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', '');
                        }
                        else
                        {
                            $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);
                        }
                        
                        $response = $this->doSpin($slotSettings, $postData);
                        
                        break;
                    case 'EndGame':
                        $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', '');
                        $slotSettings->SetGameData($slotSettings->slotId . 'TotalFreespinWin', '0');
                        $slotSettings->SetGameData($slotSettings->slotId . 'FeatureIndex', 0); 
                        $slotSettings->SetGameData($slotSettings->slotId . 'StickyWild', []);
                        $sxe = new SimpleXMLElement('<GameResponse type="EndGame"></GameResponse>');
                        $header = simplexml_load_string('<Header sessionID="T0RuuLGLoWQdxR86YwRCpFd/kZ6/qUZHDUsIxT/KRWUuILGsBZXA4GYIpqmFXONl" ccyCode="GBP" deciSep="." thousandSep="," lang="en_US" gameID="20117" versionID="1_0" fullVersionID="unknown" isRecovering="N"/>');
                        $balance = simplexml_load_string('<Balances><Balance name="CASH_BALANCE" value="'.($slotSettings->getBalance() * 100).'"/></Balances>');
                        $this->sxml_append($sxe, $header);
                        $this->sxml_append($sxe, $balance);
                        $response = $sxe->asXML();
                        break;
                    default:
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

        function doSpin($slotSettings, $postData)
        {
            $betLines = (int)$postData->PaylineCount['count'];
            $linesId1 = $slotSettings->GetPaylines1($betLines);
            $linesId2 = $slotSettings->GetPaylines2($betLines);
            $reelSetIndex = 0;
            $allbet = (int)$postData->Stake['total'] * 0.01;
            $betLine = $allbet / 0.5 * 0.01; //paytable is 0.4 based      
            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $allbet);
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];

            if($postData['slotEvent'] == 'freespin')
            {                
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bonus');
                $reelSetIndex = 2;
                $totalFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                $currentFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');                
                $leftSpin = $totalFreespin - $currentFreespin;
                if(rand(0, 20) < 2 || ($leftSpin < 3 && $slotSettings->GetGameData($slotSettings->slotId . 'FrawnyZeusPlayed') == 0))
                {
                    $reelSetIndex = 4;
                    $slotSettings->SetGameData($slotSettings->slotId . 'FrawnyZeusPlayed', 1);                    
                }
                $totalWagerWin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalGameWin');
                if($leftSpin < 3 && $totalWagerWin < $allbet * 10)
                    $winType = 'win';
                if($leftSpin == 0)
                    $leftSpin = 1;                
                if($winType == 'win')
                    $spinWinLimit = ($allbet * 20 - $totalWagerWin) / $leftSpin;
            }
            else
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bet');
            }
            $reelName1 = 'Reels'.$reelSetIndex;
            $reelName2 = 'Reels'.($reelSetIndex+1);
            
            $spinAcquired = false;             
            $gameWin = 0;//$slotSettings->GetGameData($slotSettings->slotId . 'GameWin');

            $minReels0 = [];
            $minReels1 = [];
            $minlineWins1 = [];
            $minlineWins2 = [];
            $minTotalWin1 = -1;
            $minTotalWin2 = -1;
            $minFullWild = [];
            $minFreespinsWon = 0;
            $minFreespinInfo = '';
            $minFrownyZeusInfo = '';

            $totalWin = 0;
            $freespinsWon = 0;
            $lineWins1 = [];
            $lineWins2 = [];
            
            $scatter = 12;            
            $wild = [11];
            $freespinInfo = '';
            $scatterWin1 = '';
            $scatterWin2 = '';
            $scatterCount = 0;
            $wildInfo = [];
            $freespinTriggerWin = 0;
            $frownyZeusWin = 0;
            $frownyZeusInfo = '';
            $paylineIndex1 = $slotSettings->GetPaylineIndex1();
            $paylineIndex2 = $slotSettings->GetPaylineIndex2();
            $availableFrownyFillPositions = [0, 14, 14, 15, 15, 26, 26, 26, 24, 24];
            $availableFrownyFillSizes = [0, 6, 6, 11, 11, 14, 14, 14, 12, 12];
            
            for( $i = 0; $i <= 300; $i++ ) 
            {
                $scatterWin1 = '';
                $scatterWin2 = '';
                $freespinInfo = '';
                $frownyZeusInfo = '';
                $totalWin1 = 0;
                $totalWin2 = 0;
                $freespinsWon = 0;
                $lineWins1 = [];
                $lineWins2 = [];
                $fullWild = [];
                $frownyZeusWin = 0;
                
                if($this->debug && $postData['slotEvent'] != 'freespin')
                {                 
                    $winType = 'bonus';
                }
                
                if($postData['slotEvent'] == 'freespin' && $reelSetIndex == 4)
                {
                    $frownyRand = rand(0, 100);
                    if($frownyRand < 60)
                        $frownyZeusWin = 4;
                    else if($frownyRand < 80)
                        $frownyZeusWin = 5;
                    else if($frownyRand < 90)
                        $frownyZeusWin = 6;
                    else if($frownyRand < 98)
                        $frownyZeusWin = 7;
                    else
                        $frownyZeusWin = 8;

                    $frownyZeusInfo = '<FrownyZeusInfo symNbr="'.($frownyZeusWin * 100).'" insertionsCount="'.$frownyZeusWin.'">';
                    for($r = 1; $r < 10; $r++)
                    {
                        $startPos = [];
                        $step = $availableFrownyFillPositions[$r];
                        for($z = 0; $z < $frownyZeusWin; $z++)
                            $startPos[] = 21 + $step * $z;
                        $insertionInfo = '<ReelInsertionInfo reelId="'.$r.'" insertSize="'.$availableFrownyFillSizes[$r].'" insertStartPos="'.implode('|', $startPos).'" />';
                        $frownyZeusInfo .= $insertionInfo;
                    }
                    $frownyZeusInfo .= '</FrownyZeusInfo>';
                    $reels0 = $slotSettings->GetAddedZeusReelStrips($reelName1, 0, $availableFrownyFillSizes, $availableFrownyFillPositions, $frownyZeusWin);
                }
                else
                {
                    $reels0 = $slotSettings->GetReelStrips($winType, $reelName1, 0);
                }
                
                
                //check full wild
                for($r = 0; $r < 5; $r++)
                {
                    $isFullReel = true;
                    for($c = 0; $c < 4; $c++)
                    {
                        if($reels0['reel'.($r+1)][$c] != $wild[0])
                        {
                            $isFullReel = false;
                            break;
                        }
                    }
                    if($isFullReel)
                        $fullWild[] = $r;
                }
                
                $bonusMpl = 1;                
                $lines = count($linesId1);
                $cWins = array_fill(0, $lines, 0);
                for( $k = 0; $k < $lines; $k++ ) 
                {
                    $mpl = 1;
                    $winline = [];
                    for( $j = 0; $j < count($slotSettings->SymbolGame); $j++ ) 
                    {
                        $csym = $slotSettings->SymbolGame[$j];
                        if( $csym == $scatter || !isset($slotSettings->Paytable[$csym]) ) 
                        {
                        }
                        else
                        {
                            $s = [];
                            $p0 = $linesId1[$k][0];
                            $p1 = $linesId1[$k][1];
                            $p2 = $linesId1[$k][2];
                            $p3 = $linesId1[$k][3];
                            $p4 = $linesId1[$k][4];

                            $s[0] = $reels0['reel1'][$p0];
                            $s[1] = $reels0['reel2'][$p1];
                            $s[2] = $reels0['reel3'][$p2];
                            $s[3] = $reels0['reel4'][$p3];
                            $s[4] = $reels0['reel5'][$p4];                            
                           
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) ) 
                            {
                                $emptyLine = [[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0], [0,0,0,0]];
                                $tmpWin = $slotSettings->Paytable[$csym][3] * $betLine * $mpl * $bonusMpl;
                                $coin = $slotSettings->Paytable[$csym][3] * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $cWins[$k] = $tmpWin;
                                    $emptyLine[0][$p0] = 1;
                                    $emptyLine[1][$p1] = 1;
                                    $emptyLine[2][$p2] = 1;
                                    $winline = [$paylineIndex1[$k] - 1, $coin, $this->getConvertedLine($emptyLine, 4), $tmpWin, $slotSettings->awardIndices[$csym][3]]; //[lineId, coinWon, winPositions]
                                }
                            }

                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) ) 
                            {
                                $emptyLine = [[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0], [0,0,0,0]];
                                $tmpWin = $slotSettings->Paytable[$csym][4] * $betLine * $mpl * $bonusMpl;
                                $coin = $slotSettings->Paytable[$csym][4] * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $cWins[$k] = $tmpWin;
                                    $emptyLine[0][$p0] = 1;
                                    $emptyLine[1][$p1] = 1;
                                    $emptyLine[2][$p2] = 1;
                                    $emptyLine[3][$p3] = 1;
                                    $winline = [$paylineIndex1[$k] - 1, $coin, $this->getConvertedLine($emptyLine, 4), $tmpWin, $slotSettings->awardIndices[$csym][4]]; //[lineId, coinWon, winPositions]                                                             
                                }
                            }
                            
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) ) 
                            {
                                $emptyLine = [[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0], [0,0,0,0]];
                                $tmpWin = $slotSettings->Paytable[$csym][5] * $betLine * $mpl * $bonusMpl;
                                $coin = $slotSettings->Paytable[$csym][5] * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin )
                                {
                                    $cWins[$k] = $tmpWin;
                                    $emptyLine[0][$p0] = 1;
                                    $emptyLine[1][$p1] = 1;
                                    $emptyLine[2][$p2] = 1;
                                    $emptyLine[3][$p3] = 1;
                                    $emptyLine[4][$p4] = 1;
                                    $winline = [$paylineIndex1[$k] - 1, $coin, $this->getConvertedLine($emptyLine, 4), $tmpWin, $slotSettings->awardIndices[$csym][5]]; //[lineId, coinWon,winPositions]                                                            
                                }
                            }
                        }
                    }

                    if( $cWins[$k] > 0 && !empty($winline))
                    {
                        array_push($lineWins1, $winline);
                        $totalWin1 += $cWins[$k];
                    }
                }
                
                //calc freespin
                $scatterCount1 = 0;
                $scatterPos1 = [];                
                for($r = 1; $r <= 5; $r++)
                {
                    for($c = 0; $c < 4; $c++)
                    {
                        if($reels0['reel'.$r][$c] == $scatter)
                        {
                            $scatterCount1++;
                            $scatterPos1[] = $c * 5 + $r - 1;
                        }
                    }
                }

                if($postData['slotEvent'] == 'freespin' && $reelSetIndex == 4)
                {
                    $reels1 = $slotSettings->GetAddedZeusReelStrips($reelName2, 1, $availableFrownyFillSizes, $availableFrownyFillPositions, $frownyZeusWin);
                }
                else
                {
                    // if($winType == 'bonus')
                    //     $reels1 = $slotSettings->GetReelStrips("none", $reelName2, 1);
                    // else
                    $reels1 = $slotSettings->GetReelStrips($winType, $reelName2, 1, $scatterCount1);
                    if($reels1['reel1'][0] == 13 || $reels1['reel3'][0] == 13 || $reels1['reel5'][0] == 13)
                        continue;
                }

                if(count($fullWild) > 0)
                {
                    foreach($fullWild as $r)
                    {
                        $reels1['reel'.($r+1)] = array_fill(0, 12, $wild[0]);
                    }
                }
                
                
                $lines = count($linesId2);
                $cWins = array_fill(0, $lines, 0);
                for( $k = 0; $k < $lines; $k++ ) 
                {
                    $mpl = 1;
                    $winline = [];
                    for( $j = 0; $j < count($slotSettings->SymbolGame); $j++ ) 
                    {
                        $csym = $slotSettings->SymbolGame[$j];
                        if( $csym == $scatter || !isset($slotSettings->Paytable[$csym]) ) 
                        {
                        }
                        else
                        {
                            $s = [];
                            $p0 = $linesId2[$k][0];
                            $p1 = $linesId2[$k][1];
                            $p2 = $linesId2[$k][2];
                            $p3 = $linesId2[$k][3];
                            $p4 = $linesId2[$k][4];

                            $s[0] = $reels1['reel1'][$p0];
                            $s[1] = $reels1['reel2'][$p1];
                            $s[2] = $reels1['reel3'][$p2];
                            $s[3] = $reels1['reel4'][$p3];
                            $s[4] = $reels1['reel5'][$p4];                            
                           
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) ) 
                            {
                                $emptyLine = [[0,0,0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0], [0,0,0,0,0,0,0,0,0,0,0,0]];
                                $tmpWin = $slotSettings->Paytable[$csym][3] * $betLine * $mpl * $bonusMpl;
                                $coin = $slotSettings->Paytable[$csym][3] * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $cWins[$k] = $tmpWin;
                                    $emptyLine[0][$p0] = 1;
                                    $emptyLine[1][$p1] = 1;
                                    $emptyLine[2][$p2] = 1;
                                    $winline = [$paylineIndex2[$k] - 1, $coin, $this->getConvertedLine($emptyLine, 12), $tmpWin, $slotSettings->awardIndices[$csym][3]]; //[lineId, coinWon, winPositions]
                                }
                            }

                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) ) 
                            {
                                $emptyLine = [[0,0,0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0], [0,0,0,0,0,0,0,0,0,0,0,0]];
                                $tmpWin = $slotSettings->Paytable[$csym][4] * $betLine * $mpl * $bonusMpl;
                                $coin = $slotSettings->Paytable[$csym][4] * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $cWins[$k] = $tmpWin;
                                    $emptyLine[0][$p0] = 1;
                                    $emptyLine[1][$p1] = 1;
                                    $emptyLine[2][$p2] = 1;
                                    $emptyLine[3][$p3] = 1;
                                    $winline = [$paylineIndex2[$k] - 1, $coin, $this->getConvertedLine($emptyLine, 12), $tmpWin, $slotSettings->awardIndices[$csym][4]]; //[lineId, coinWon, winPositions]                                                             
                                }
                            }
                            
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) ) 
                            {
                                $emptyLine = [[0,0,0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0], [0,0,0,0,0,0,0,0,0,0,0,0]];
                                $tmpWin = $slotSettings->Paytable[$csym][5] * $betLine * $mpl * $bonusMpl;
                                $coin = $slotSettings->Paytable[$csym][5] * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin )
                                {
                                    $cWins[$k] = $tmpWin;
                                    $emptyLine[0][$p0] = 1;
                                    $emptyLine[1][$p1] = 1;
                                    $emptyLine[2][$p2] = 1;
                                    $emptyLine[3][$p3] = 1;
                                    $emptyLine[4][$p4] = 1;
                                    $winline = [$paylineIndex2[$k] - 1, $coin, $this->getConvertedLine($emptyLine, 12), $tmpWin, $slotSettings->awardIndices[$csym][5]]; //[lineId, coinWon,winPositions]                                                            
                                }
                            }
                        }
                    }

                    if( $cWins[$k] > 0 && !empty($winline))
                    {
                        array_push($lineWins2, $winline);
                        $totalWin2 += $cWins[$k];
                    }
                }
                

                
                
                $scatterPos2 = [];                
                $scatterCount2 = 0;
                for($r = 1; $r <= 5; $r++)
                {
                    for($c = 0; $c < 12; $c++)
                    {
                        if($reels1['reel'.$r][$c] == $scatter)
                        {
                            $scatterCount2++;
                            $scatterPos2[] = $c * 5 + $r - 1;
                        }
                    }
                }
                
                $scatterCount = $scatterCount1 + $scatterCount2;
                if($scatterCount > 2 && $winType != 'bonus')
                    continue;
                
                if($scatterCount > 2)
                {                    
                    $freespinsWon = 10;
                    $freespinTriggerWin = 0;
                    if($scatterCount == 3)
                    {
                        $freespinsWon = 10;
                        $freespinTriggerWin = $allbet * 2;
                    }
                    else if($scatterCount == 4)
                    {
                        $freespinsWon = 15;
                        $freespinTriggerWin = $allbet * 10;
                    }
                    else if($scatterCount == 5)
                    {
                        $freespinsWon = 20;
                        $freespinTriggerWin = $allbet * 20;
                    }
                    else if($scatterCount == 6)
                    {
                        $freespinsWon = 30;
                        $freespinTriggerWin = $allbet * 25;
                    }
                    if($scatterCount2 > 0)
                        $totalWin2 += $freespinTriggerWin;
                    else
                        $totalWin1 += $freespinTriggerWin;
                    $scatterWin1 = '<ScatterWin winVal="0" awardIndex="'.$slotSettings->awardIndices[$scatter][$scatterCount].'">'.implode('|', $scatterPos1).'</ScatterWin>';
                    $scatterWin2 = '<ScatterWin winVal="'.($freespinTriggerWin * 100).'" awardIndex="'.$slotSettings->awardIndices[$scatter][$scatterCount].'">'.implode('|', $scatterPos2).'</ScatterWin>';
                    $freespinInfo = '<Feature index="0"><FS_Info fsAwarded="'.$freespinsWon.'"/></Feature>';
                }
                $totalWin = $totalWin1 + $totalWin2 + $frownyZeusWin;
                if($minTotalWin1 == -1 && $minTotalWin2 == -1 && $scatterCount < 3 || ($minTotalWin1 + $minTotalWin2 > $totalWin1 + $totalWin2 && $totalWin1 + $totalWin2 > 0))
                {
                    $minTotalWin1 = $totalWin1;
                    $minTotalWin2 = $totalWin2;
                    $minlineWins1 = $lineWins1;
                    $minlineWins2 = $lineWins2;
                    $minFullWild = $fullWild;
                    $minFreespinsWon = $freespinsWon;
                    $minReels0 = $reels0;
                    $minReels1 = $reels1;
                    $minWildInfo = $wildInfo;
                    $minFreespinInfo = $freespinInfo;
                    $minFrownyZeusInfo = $frownyZeusInfo;
                }

                if($this->debug)
                {
                    $spinAcquired = true;
                    break;
                }                    

                if($totalWin <= $spinWinLimit && (($totalWin > 0 && $winType != 'none') || ($winType == 'bonus' && $freespinsWon > 0)))
                {
                    $spinAcquired = true;
                    if($totalWin < 0.5 * $spinWinLimit && $winType != 'bonus')
                        $spinAcquired = false;
                    if($spinAcquired)
                        break;                                        
                }           

                else if( $winType == 'none' && $totalWin == $gameWin ) 
                {
                    break;
                }
            }

            $manualNoWin = false;
            if(!$spinAcquired && $totalWin > $gameWin && $winType != 'none' || ($winType != 'bonus' && $scatterCount > 2))
            {                
                $manualNoWin = true;
                $reels0 = $minReels0;
                $reels1 = $minReels1;
                $lineWins1 = $minlineWins1;
                $lineWins2 = $minlineWins2;
                $totalWin1 = $minTotalWin1;
                $totalWin2 = $minTotalWin2;
                $fullWild = $minFullWild;
                $freespinsWon = $minFreespinsWon;                    
                $scatterWin1 = '';
                $scatterWin2 = '';
                $wildInfo = $minWildInfo;
                $freespinInfo = $minFreespinInfo;
                $frownyZeusInfo = $minFrownyZeusInfo;
            }

            $paylines1 = '';
            if(!empty($lineWins1))
            {
                foreach($lineWins1 as $winline)
                {
                    $paylines1 .= '<PaylineWin index="'.$winline[0].'" winVal="'.($winline[3] * 100).'" awardIndex="'.$winline[4].'" awardTableIndex="0">'.$winline[2].'</PaylineWin>';
                }
            }

            $paylines2 = '';
            if(!empty($lineWins2))
            {
                foreach($lineWins2 as $winline)
                {
                    $paylines2 .= '<PaylineWin index="'.$winline[0].'" winVal="'.($winline[3] * 100).'" awardIndex="'.$winline[4].'" awardTableIndex="0">'.$winline[2].'</PaylineWin>';
                }
            }

            $winSC1 = 0;
            if($scatterCount1 > 0)
                $winSC1 = 1;
            $winSC2 = 0;
            if($scatterCount2 > 0)
                $winSC2 = 1;
            
            if($totalWin > 0)
            {
                $slotSettings->SetBank($postData['slotEvent'], -1 * $totalWin);
                $slotSettings->SetBalance($totalWin);
                $slotSettings->SetWin($totalWin);
            }

            $slotSettings->SetGameData($slotSettings->slotId . 'TotalGameWin', $slotSettings->GetGameData($slotSettings->slotId . 'TotalGameWin') + $totalWin);
            if($postData['slotEvent'] == 'freespin')
                $slotSettings->SetGameData($slotSettings->slotId . 'TotalFreespinWin', $slotSettings->GetGameData($slotSettings->slotId . 'TotalFreespinWin') + $totalWin);

            if($freespinsWon > 0)
            {
                if($postData['slotEvent'] != 'freespin')
                {
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $freespinsWon);
                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalFreespinWin', $freespinTriggerWin);
                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                    $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', 'Freespin');
                }
                else
                {
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') + $freespinsWon);
                }
            }
            
            $totalWagerWin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalGameWin');
            $freeSpinWin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalFreespinWin');
            $baseGameWin = $totalWagerWin - $freeSpinWin;
            $bonusAwarded = $scatterCount > 2 ? 'Y':'N';            
            $isFreespin = $postData['slotEvent'] == 'freespin' ? 'Y':'N';            
            $sxe = new SimpleXMLElement('<GameResponse type="Logic"></GameResponse>');
            $header = simplexml_load_string('<Header sessionID="vkWv5HHtCBUMs3Gq1ogvjRBPthnKy49aqkvJ/CKj2EKCnk8ZnfY+aXVrGjaDSipAQKeZRMBeWY2+KgSXm60lcFLhX6a7iZfppr/nfhKgFY4=" ccyCode="GBP" deciSep="." thousandSep="," lang="en_US" gameID="20089" versionID="1_0" fullVersionID="1.0.3" isRecovering="N"/>');
            $accountData = simplexml_load_string('<AccountData><AccountData><CurrencyMultiplier>1</CurrencyMultiplier></AccountData></AccountData>');
            $balance = simplexml_load_string('<Balances><Balance name="CASH_BALANCE" value="'.($slotSettings->GetBalance() * 100).'"/></Balances>');
            $gameResultStr = '<GameResult stake="'.(int)$postData->Stake['total'].'" stakePerLine="1" paylineCount="100" totalWin="'.($totalWin * 100).'" betID="">
                        <ReelResults numSpins="1">
                            <ReelSpin slotIndex="0" reelsetIndex="'.$reelSetIndex.'" winCountPL="'.count($lineWins1).'" winCountSC="'.$winSC1.'" spinWins="'.($totalWin1 * 100).'" freeSpin="'.$isFreespin.'" bonusAwarded="'.$bonusAwarded.'" manualNoWin="'.$manualNoWin.'">
                                <ReelStops>'.implode('|',$reels0['rp']).'</ReelStops>
                                '.$paylines1.$scatterWin1.'
                            </ReelSpin>
                            <ReelSpin slotIndex="1" reelsetIndex="'.($reelSetIndex+1).'" winCountPL="'.count($lineWins2).'" winCountSC="'.$winSC2.'" spinWins="'.($totalWin2 * 100).'" freeSpin="'.$isFreespin.'" bonusAwarded="'.$bonusAwarded.'">
                                <ReelStops>'.implode('|',$reels1['rp']).'</ReelStops>
                                '.$paylines2.$scatterWin2.'
                            </ReelSpin>
                        </ReelResults>
                        <MaxWin_Info maxWinValue="25000000" maxWin="false" cappedWins="0" />
                    </GameResult>';
            $gameResult = simplexml_load_string($gameResultStr);
            if($freespinsWon > 0 && $postData['slotEvent'] != 'freespin')
            {
                //save recovery info when triggering freespin
                $slotSettings->SetGameData($slotSettings->slotId . 'BGRecovery', $gameResultStr);
            }
             
            if($postData['slotEvent'] == 'freespin' && $freespinInfo == '')
            {
                //set freespin info during freespin (not triggering freespin)
                $totalFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                $currentFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');  
                $freespinTriggerWin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalFreespinWin');              
                $leftSpin = $totalFreespin - $currentFreespin;
                $freespinInfo = '<Feature index="0"><FS_Info totalSpinsWon="'.$totalFreespin.'" currentSpin="'.$currentFreespin.'" currentFSWins="'.($freeSpinWin * 100).'" totalGameWins="'.($totalWagerWin * 100).'" fsAwarded="0" winTopUp="0" entryWin="'.($freespinTriggerWin * 100).'"/></Feature>';
                if($leftSpin == 0)
                {
                    // $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', '');
                    // $freespinInfo = '<Feature index="0"><FS_Info totalGameWins="'.($totalWagerWin*100).'" winTopUp="0" entryWin="'.($freespinTriggerWin * 100).'" fsPlayed="Y" /></Feature>';
                }                    
            }

            if($freespinInfo != '')
            {
                $fs = simplexml_load_string($freespinInfo);
                $this->sxml_append($gameResult, $fs);
            }

            if($frownyZeusInfo != '')
            {
                $fz = simplexml_load_string($frownyZeusInfo);
                $this->sxml_append($gameResult, $fz);
            }

            if(count($fullWild) > 0)
            {
                $fw = simplexml_load_string('<WildTransfer_info reels="'.implode("|", $fullWild).'" />');
                $this->sxml_append($gameResult, $fw);
            }
        
            $reelData = '<ReelData reel1="'.implode(',',$reels0['reel1']).'" reel2="'.implode(',',$reels0['reel2']).'" reel3="'.implode(',',$reels0['reel3']).'" reel4="'.implode(',',$reels0['reel4']).'" reel5="'.implode(',',$reels0['reel5']).'"></ReelData>';
            $rd = simplexml_load_string($reelData);
            $this->sxml_append($gameResult, $rd);
            $this->sxml_append($sxe, $header);
            $this->sxml_append($sxe, $accountData);
            $this->sxml_append($sxe, $balance);
            $this->sxml_append($sxe, $gameResult);
            
            $response = $sxe->asXML();

            if($postData['slotEvent'] == 'freespin')
                $allbet = 0;
            $slotSettings->SaveLogReport($response, $allbet, $totalWin, $postData['slotEvent']);        
            return $response;
        }
        
        function getActiveSymbols($reels, $sym, $line)
        {
            $rows = 5;
            $cols = count($reels['reel1']);
                        
            $active = array_fill(0, $rows * $cols, 0);
            for($r = 0; $r < $rows; $r++)
                for($c = 0; $c < $cols; $c++)
                {
                    if($reels['reel'.($r+1)][$c] == $sym && $c == $line)
                        $active[$r * $cols + $c] = 1;
                }
            
            return implode("", $active);
        }

        function getMultiplier($reel, $sym, $wild)
        {
            $multiplier = 0;
            for($c = 0; $c < 4; $c++)
                if($reel[$c] == $sym || $reel[$c] == $wild)
                    $multiplier++;

            return $multiplier > 0 ? $multiplier : 1;
        }
    }

}


