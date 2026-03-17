<?php 
namespace VanguardLTE\Games\TripleCashWheel
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
              
            return $_xml->asXML();
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
                                return '{"javascripts":["/games/TripleCashWheel/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/mainjs/application/capabilitiesdetector.CapabilitiesDetectorBootstrapper.js?resourceversion=5.0.0.11-1656345280&appcode=capabilities-detector","/games/TripleCashWheel/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/translationjs/bundles/translations/#{locale}/capabilitiesdetector.LocalizationBundle.js?resourceversion=5.0.0.11-1656345280&appcode=capabilities-detector"],"jsons":["/games/TripleCashWheel/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/translationjson/bundles/translations/#{locale}/capabilitiesdetector.LocalizationBundle.json?resourceversion=5.0.0.11-1656345280&appcode=capabilities-detector"],"main_class":"capabilitiesdetector.CapabilitiesDetectorBootstrapper","supported_locales":["it_IT","ru_RU","el_GR","pl_PL","ro_RO","tr_TR","fr_FR","cs_CZ","hu_HU","ca_ES","de_DE","sk_SK","es_ES","nl_NL","fr_CA","sv_SE","da_DK","bg_BG","hr_HR","fi_FI","en_GB","et_EE","lt_LT","lv_LV","sl_SI","no_NO","pt_PT"],"default_locale":"en"}';
                            break;
                            case "gls-platform":
                                return '{"javascripts":["/games/TripleCashWheel/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/mainjs/application/glsplatform.GlsPlatform.js?resourceversion=5.0.0.11-1656345280&appcode=gls-platform","/games/TripleCashWheel/acy-resource.wimobile.casinarena.com/resource-service/metadatajs/bundles/metadata/glsplatform.MetaDataBundle.js?resourceversion=5.0.0.11-1656345280&appcode=gls-platform&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=true&partnercode=nyx_newdemo&realmoney=false&gamecode=triplecashwheel&locale=en_US&webaudio=true","/games/TripleCashWheel/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/translationjs/bundles/translations/#{locale}/glsplatform.LocalizationsBundle.js?resourceversion=5.0.0.11-1656345280&appcode=gls-platform"],"jsons":["/games/TripleCashWheel/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/metadatajson/bundles/metadata/glsplatform.MetaDataBundle.json?resourceversion=5.0.0.11-1656345280&appcode=gls-platform&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=true&partnercode=nyx_newdemo&realmoney=false&gamecode=triplecashwheel&locale=en_US&webaudio=true","/games/TripleCashWheel/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/translationjson/bundles/translations/#{locale}/glsplatform.LocalizationsBundle.json?resourceversion=5.0.0.11-1656345280&appcode=gls-platform"],"main_class":"glsplatform.GlsPlatform","supported_locales":["sl_SI","pl_PL","lt_LT","bg_BG","ru_RU","ro_RO","el_GR","pt_PT","en_GB","sk_SK","cs_CZ","ca_ES","fr_CA","es_ES","lv_LV","hr_HR","it_IT","fi_FI","de_DE","hu_HU","fr_FR","tr_TR","sv_SE","da_DK","no_NO","et_EE","nl_NL"],"default_locale":"en"}';
                                break;
                            case "lean-regular-partner-adapter":
                                return '{"javascripts":["/games/TripleCashWheel/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/mainjs/application/leanpartneradapter.LeanPartnerAdapter.js?resourceversion=5.0.0.11-1656345280&appcode=lean-regular-partner-adapter","/games/TripleCashWheel/acy-resource.wimobile.casinarena.com/resource-service/metadatajs/bundles/metadata/leanpartneradapter.MetaDataBundle.js?resourceversion=5.0.0.11-1656345280&appcode=lean-regular-partner-adapter&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=true&partnercode=nyx_newdemo&realmoney=false&gamecode=triplecashwheel&locale=en_US&webaudio=true","/games/TripleCashWheel/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/translationjs/bundles/translations/#{locale}/leanpartneradapter.LocalizationsBundle.js?resourceversion=5.0.0.11-1656345280&appcode=lean-regular-partner-adapter"],"jsons":["/games/TripleCashWheel/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/metadatajson/bundles/metadata/leanpartneradapter.MetaDataBundle.json?resourceversion=5.0.0.11-1656345280&appcode=lean-regular-partner-adapter&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=true&partnercode=nyx_newdemo&realmoney=false&gamecode=triplecashwheel&locale=en_US&webaudio=true","/games/TripleCashWheel/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/translationjson/bundles/translations/#{locale}/leanpartneradapter.LocalizationsBundle.json?resourceversion=5.0.0.11-1656345280&appcode=lean-regular-partner-adapter"],"main_class":"commonadapter.PortraitPartnerAdapter","supported_locales":["it_IT","ru_RU","el_GR","pl_PL","ro_RO","tr_TR","fr_FR","cs_CZ","hu_HU","ca_ES","de_DE","sk_SK","es_ES","nl_NL","fr_CA","sv_SE","da_DK","bg_BG","hr_HR","fi_FI","en_GB","et_EE","lt_LT","lv_LV","sl_SI","no_NO","pt_PT"],"default_locale":"en"}';
                                break;
                            case "triplecashwheel":
                                return '{
                                    "orientation": "PORTRAIT_MOBILE",
                                    "default_locale": "en",
                                    "inject": [
                                        {
                                            "link": {
                                                "rel": "stylesheet",
                                                "href": "content/triplecashwheel/app/css/app.css"
                                            }
                                        }
                                    ],
                                    "javascripts": [
                                        "content/triplecashwheel/app/js/game.js?resourceversion=5.0.0.11-1656345280&appcode=triplecashwheel&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=true&partnercode=nyx_newdemo&realmoney=false&gamecode=triplecashwheel&locale=en_US&webaudio=true",
                                        "content/triplecashwheel/app/js/metadatabundle.js?resourceversion=5.0.0.11-1656345280&appcode=triplecashwheel&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=true&partnercode=nyx_newdemo&realmoney=false&gamecode=triplecashwheel&locale=en_US&webaudio=true"
                                    ],
                                    "publish_events": {
                                        "platform":"1"
                                    },
                                    "main_class": "BL.Adapters.SG.Adapter",
                                    "supported_locales": [
                                        "en"
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
                        $filename = base_path() . '/app/Games/TripleCashWheel/game.txt';
                        $file = fopen($filename, "r" );
                        $filesize = filesize( $filename );
                        $response = fread( $file, $filesize );
                        $response = str_replace('BAL_REPLACE', $slotSettings->GetBalance() * 100, $response);
                        fclose( $file );

                        $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', '');
                        $slotSettings->SetGameData($slotSettings->slotId . 'SpinFeature', '-1');
                        $slotSettings->SetGameData($slotSettings->slotId . 'TotalFreespinWin', '0');
                        $slotSettings->SetGameData($slotSettings->slotId . 'OldWildPos', []);
                        break;                        
                    case 'Logic':                        
                        $allbet = (int)((string)$postData->Stake['total']) * 0.01;
                        $postData['slotEvent'] = 'bet';                        
                        $spinStatus = $slotSettings->GetGameData($slotSettings->slotId . 'SpinStatus');
                        if($spinStatus == 'Freespin')
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
                            $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', 0);                            
                            $slotSettings->SetGameData($slotSettings->slotId . 'TotalGameWin', 0);
                        }
                        else
                        {
                            $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);
                        }
                        
                        $response = $this->doSpin($slotSettings, $postData);
                        
                        break;
                    case 'EndGame':
                        $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', '');
                        $slotSettings->SetGameData($slotSettings->slotId . 'SpinFeature', '-1');
                        $slotSettings->SetGameData($slotSettings->slotId . 'TotalFreespinWin', '0');
                        $slotSettings->SetGameData($slotSettings->slotId . 'OldWildPos', []);
                        $sxe = new SimpleXMLElement('<GameResponse type="EndGame"></GameResponse>');
                        $accountData = simplexml_load_string('<AccountData><AccountData><CurrencyMultiplier>1</CurrencyMultiplier></AccountData></AccountData>');
                        $header = simplexml_load_string('<Header sessionID="T0RuuLGLoWQdxR86YwRCpFd/kZ6/qUZHDUsIxT/KRWUuILGsBZXA4GYIpqmFXONl" ccyCode="GBP" deciSep="." thousandSep="," lang="en_US" gameID="20191" versionID="1_0" fullVersionID="unknown" isRecovering="N"/>');
                        $balance = simplexml_load_string('<Balances><Balance name="CASH_BALANCE" value="'.($slotSettings->getBalance() * 100).'"/></Balances>');
                        $this->sxml_append($sxe, $header);
                        $this->sxml_append($sxe, $accountData);
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
            $linesId = $slotSettings->GetPaylines();
            $reelSetIndex = 0;
            if($postData['slotEvent'] == 'freespin')
            {                
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bonus');
                $reelName = 'Reels1';
                $reelSetIndex = 1;
            }
            else
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bet');
                $reelName = 'Reels0';                
            }

            $lines = count($linesId);
            $allbet = (int)((string)$postData->Stake['total']) * 0.01;                                 
            $betLine = $allbet * 0.01; //paytable is 0.4 based      

            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $allbet);
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
            
            $spinAcquired = false;             

            $minAllReels = [];
            $minAllLineWins = [];
            $minAllTotalWin = -1;
            $minAllScatterWins = [];
            
            $totalWin = 0;
            $lineWins = [];
            $reels = [];
            
            $jackpot = '8';
            $wild = ["0"];
            $freespinInfo = '';

            $allLineWins = [];
            $allScatterWins = [];
            $allReels = [];
            $allTotalWin = 0;
            $sevens = [1, 2, 3];
            $bars = [4, 5];
            for( $i = 0; $i <= 500; $i++ ) 
            {
                
                $freespinInfo = '';
                $allTotalWin = 0;                

                if($this->debug && $postData['slotEvent'] != 'freespin')
                {                 
                    $winType = 'bonus';
                }

                for($g = 0; $g < 3; $g++)
                {
                    $scatterWin = '';
                    $totalWin = 0;
                    $lineWins = [];
                    $cWins = array_fill(0, $lines, 0);
                    $reels = $slotSettings->GetReelStrips($winType, $reelName, $postData['slotEvent']);
                    
                    $bonusMpl = 1;
                    if($postData['slotEvent'] == 'freespin')
                        $bonusMpl = 3;
                    
                    for( $k = 0; $k < $lines; $k++ ) 
                    {
                        $mpl = 1;
                        $winline = [];
                        for( $j = 0; $j < count($slotSettings->SymbolGame); $j++ ) 
                        {
                            $csym = $slotSettings->SymbolGame[$j];
                            if( !isset($slotSettings->Paytable[$csym]) ) 
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
                                                                                    
                                if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) ) 
                                {
                                    $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
                                    $tmpWin = $slotSettings->Paytable[$csym][3] * $betLine * $mpl * $bonusMpl;
                                    if( $cWins[$k] < $tmpWin ) 
                                    {
                                        $cWins[$k] = $tmpWin;
                                        $emptyLine[0][$p0] = 1;
                                        $emptyLine[1][$p1] = 1;
                                        $emptyLine[2][$p2] = 1;
                                        $winline = [$k, $tmpWin, $this->getConvertedLine($emptyLine), $slotSettings->awardIndices[$csym][3]]; //[lineId, coinWon, winPositions]
                                    }
                                }
    
                                if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) ) 
                                {
                                    $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
                                    $tmpWin = $slotSettings->Paytable[$csym][4] * $betLine * $mpl * $bonusMpl;
                                    if( $cWins[$k] < $tmpWin ) 
                                    {
                                        $cWins[$k] = $tmpWin;
                                        $emptyLine[0][$p0] = 1;
                                        $emptyLine[1][$p1] = 1;
                                        $emptyLine[2][$p2] = 1;
                                        $emptyLine[3][$p3] = 1;
                                        $winline = [$k, $tmpWin, $this->getConvertedLine($emptyLine), $slotSettings->awardIndices[$csym][4]]; //[lineId, coinWon, winPositions]                                                             
                                    }
                                }
                                
                                if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) ) 
                                {
                                    $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
                                    $tmpWin = $slotSettings->Paytable[$csym][5] * $betLine * $mpl * $bonusMpl;
                                    if( $cWins[$k] < $tmpWin )
                                    {
                                        $cWins[$k] = $tmpWin;
                                        $emptyLine[0][$p0] = 1;
                                        $emptyLine[1][$p1] = 1;
                                        $emptyLine[2][$p2] = 1;
                                        $emptyLine[3][$p3] = 1;
                                        $emptyLine[4][$p4] = 1;
                                        $winline = [$k, $tmpWin, $this->getConvertedLine($emptyLine), $slotSettings->awardIndices[$csym][5]]; //[lineId, coinWon,winPositions]                                                            
                                    }
                                }

                                //mixed sevens
                                if( ( in_array($s[0], $sevens) || in_array($s[0], $wild)) && (in_array($s[1], $sevens) || in_array($s[1], $wild)) && (in_array($s[2], $sevens) || in_array($s[2], $wild)) ) 
                                {
                                    $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
                                    $tmpWin = $slotSettings->Paytable['10'][3] * $betLine * $mpl * $bonusMpl;
                                    if( $cWins[$k] < $tmpWin ) 
                                    {
                                        $cWins[$k] = $tmpWin;
                                        $emptyLine[0][$p0] = 1;
                                        $emptyLine[1][$p1] = 1;
                                        $emptyLine[2][$p2] = 1;
                                        $winline = [$k, $tmpWin, $this->getConvertedLine($emptyLine), $slotSettings->awardIndices['10'][3]]; //[lineId, coinWon, winPositions]
                                    }
                                }
                                if( ( in_array($s[0], $sevens) || in_array($s[0], $wild)) && (in_array($s[1], $sevens) || in_array($s[1], $wild)) && (in_array($s[2], $sevens) || in_array($s[2], $wild)) && (in_array($s[3], $sevens) || in_array($s[3], $wild)) ) 
                                {
                                    $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
                                    $tmpWin = $slotSettings->Paytable['10'][4] * $betLine * $mpl * $bonusMpl;
                                    if( $cWins[$k] < $tmpWin ) 
                                    {
                                        $cWins[$k] = $tmpWin;
                                        $emptyLine[0][$p0] = 1;
                                        $emptyLine[1][$p1] = 1;
                                        $emptyLine[2][$p2] = 1;
                                        $emptyLine[3][$p3] = 1;
                                        $winline = [$k, $tmpWin, $this->getConvertedLine($emptyLine), $slotSettings->awardIndices['10'][4]]; //[lineId, coinWon, winPositions]
                                    }
                                }
                                if( ( in_array($s[0], $sevens) || in_array($s[0], $wild)) && (in_array($s[1], $sevens) || in_array($s[1], $wild)) && (in_array($s[2], $sevens) || in_array($s[2], $wild)) && (in_array($s[3], $sevens) || in_array($s[3], $wild)) && (in_array($s[4], $sevens) || in_array($s[4], $wild)) ) 
                                {
                                    $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
                                    $tmpWin = $slotSettings->Paytable['10'][5] * $betLine * $mpl * $bonusMpl;
                                    if( $cWins[$k] < $tmpWin ) 
                                    {
                                        $cWins[$k] = $tmpWin;
                                        $emptyLine[0][$p0] = 1;
                                        $emptyLine[1][$p1] = 1;
                                        $emptyLine[2][$p2] = 1;
                                        $emptyLine[3][$p3] = 1;
                                        $emptyLine[4][$p4] = 1;
                                        $winline = [$k, $tmpWin, $this->getConvertedLine($emptyLine), $slotSettings->awardIndices['10'][5]]; //[lineId, coinWon, winPositions]
                                    }
                                }

                                //mixed bars
                                if( ( in_array($s[0], $bars) || in_array($s[0], $wild)) && (in_array($s[1], $bars) || in_array($s[1], $wild)) && (in_array($s[2], $bars) || in_array($s[2], $wild)) ) 
                                {
                                    $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
                                    $tmpWin = $slotSettings->Paytable['9'][3] * $betLine * $mpl * $bonusMpl;
                                    if( $cWins[$k] < $tmpWin ) 
                                    {
                                        $cWins[$k] = $tmpWin;
                                        $emptyLine[0][$p0] = 1;
                                        $emptyLine[1][$p1] = 1;
                                        $emptyLine[2][$p2] = 1;
                                        $winline = [$k, $tmpWin, $this->getConvertedLine($emptyLine), $slotSettings->awardIndices['9'][3]]; //[lineId, coinWon, winPositions]
                                    }
                                }
                                if( ( in_array($s[0], $bars) || in_array($s[0], $wild)) && (in_array($s[1], $bars) || in_array($s[1], $wild)) && (in_array($s[2], $bars) || in_array($s[2], $wild)) && (in_array($s[3], $bars) || in_array($s[3], $wild)) ) 
                                {
                                    $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
                                    $tmpWin = $slotSettings->Paytable['9'][4] * $betLine * $mpl * $bonusMpl;
                                    if( $cWins[$k] < $tmpWin ) 
                                    {
                                        $cWins[$k] = $tmpWin;
                                        $emptyLine[0][$p0] = 1;
                                        $emptyLine[1][$p1] = 1;
                                        $emptyLine[2][$p2] = 1;
                                        $emptyLine[3][$p3] = 1;
                                        $winline = [$k, $tmpWin, $this->getConvertedLine($emptyLine), $slotSettings->awardIndices['9'][4]]; //[lineId, coinWon, winPositions]
                                    }
                                }
                                if( ( in_array($s[0], $bars) || in_array($s[0], $wild)) && (in_array($s[1], $bars) || in_array($s[1], $wild)) && (in_array($s[2], $bars) || in_array($s[2], $wild)) && (in_array($s[3], $bars) || in_array($s[3], $wild)) && (in_array($s[4], $bars) || in_array($s[4], $wild)) ) 
                                {
                                    $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
                                    $tmpWin = $slotSettings->Paytable['9'][5] * $betLine * $mpl * $bonusMpl;
                                    if( $cWins[$k] < $tmpWin ) 
                                    {
                                        $cWins[$k] = $tmpWin;
                                        $emptyLine[0][$p0] = 1;
                                        $emptyLine[1][$p1] = 1;
                                        $emptyLine[2][$p2] = 1;
                                        $emptyLine[3][$p3] = 1;
                                        $emptyLine[4][$p4] = 1;
                                        $winline = [$k, $tmpWin, $this->getConvertedLine($emptyLine), $slotSettings->awardIndices['9'][5]]; //[lineId, coinWon, winPositions]
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
                        
                    //calc jackpot symbol
                    $jackpotCnt = 0;    
                    $jackpotPos = [];
                                        
                    for($r = 1; $r <= 5; $r++)
                    {
                        for($c = 0; $c < 3; $c++)
                        {
                            if($reels['reel'.$r][$c] == $jackpot)
                            {
                                $jackpotCnt++;
                                $jackpotPos[] = $c * 5 + $r - 1;
                            }
                        }
                    }
                    if($jackpotCnt > 2)
                    {
                        $jackpotWin = $slotSettings->Paytable[$jackpot][$jackpotCnt] * $allbet;
                        $totalWin += $jackpotWin;
                        $scatterWin = '<ScatterWin winVal="'.($jackpotWin * 100).'" awardIndex="'. $slotSettings->awardIndices[$jackpot][$jackpotCnt].'">'.implode('|', $jackpotPos).'</ScatterWin>';                                                
                    }

                    $allScatterWins[$g] = $scatterWin;                    
                    $allLineWins[$g] = $lineWins;
                    $allTotalWin += $totalWin;
                    $allReels[$g] = $reels;
                }

                if( $minAllTotalWin == -1 || ($allTotalWin < $minAllTotalWin && $allTotalWin > 0))
                {
                    $minAllLineWins = $allLineWins;
                    $minAllTotalWin = $allTotalWin;
                    $minAllReels = $allReels;
                    $minAllScatterWins = $allScatterWins;
                }

                if($this->debug)
                {
                    $spinAcquired = true;
                    break;
                }                    

                if($allTotalWin <= $spinWinLimit && (($allTotalWin > 0 && $winType != 'none')))
                {
                    $spinAcquired = true;
                    if($allTotalWin < 0.5 * $spinWinLimit && $winType != 'bonus')
                        $spinAcquired = false;
                    if($spinAcquired)
                        break;                                        
                }                                          
                else if( $winType == 'none' && $allTotalWin == 0 ) 
                {
                    break;
                }
            }            

            $manualNoWin = false;
            if(!$spinAcquired && $allTotalWin > 0 && $winType != 'none')
            {                
                $manualNoWin = true;
                $allReels = $minAllReels;
                $allTotalWin = $minAllTotalWin;
                $allLineWins = $minAllLineWins;
                $allScatterWins = $minAllScatterWins;
            }

            $winSC = 0;

            $wheelCount = 0;
            $wheelString = '';
            $freespinInfo = '';
            $wheelInfo = '<WheelBonus wheelSpins="0" wheelMultiplier="0"></WheelBonus>';
            $bonusWin = 0;
            $wheelChoices = [4,4,4,4,4,4,4,4,4,4,4,4,4,4,0,1,2,3,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21];
            if($winType == 'bonus')
            {
                //add bonus wheel                
                if(rand(0, 100) < 80)
                    $wheelCount = 1;
                else
                    $wheelCount = 3;

                $multiplier = 1;
                if(rand(0, 100) < 5)
                    $multiplier = 3;

                $bonusWin = 0;
                $freespinPicked = false;

                for($i = 0; $i < $wheelCount; $i++)
                {
                    $index = $wheelChoices[rand(0, count($wheelChoices) - 1)];
                    
                    $feature = $slotSettings->wheelFeature[$index];
                    while(($freespinPicked || $postData['slotEvent'] == 'freespin') && $feature == 'FreeSpin')
                    {
                        $index = rand(0, count($slotSettings->wheelFeature) - 1);
                        $feature = $slotSettings->wheelFeature[$index];
                    }

                    if($feature == 'FreeSpin')
                    {
                        $freespinPicked = true;
                        $freespinInfo = '<FSInfo fsWinnings="0" freeSpinsTotal="10" freeSpinNumber="0"/>';
                        $wheelString .= '<WheelOutcome index="4" freeSpins="10" payout="'.($betLine * 100).'" />';
                        $bonusWin += $betLine * 100;
                    }
                    else
                    {
                        $bonusWin += $betLine * $feature * $multiplier;          
                        $wheelString .= '<WheelOutcome index="'.$index.'" multiplier="'.$feature.'" payout="'.($betLine * $feature * $multiplier * 100).'" />';
                    }
                }
                if($allTotalWin + $bonusWin < $spinWinLimit || $this->debug)
                {
                    $wheelInfo = '<WheelBonus wheelSpins="'.$wheelCount.'" wheelMultiplier="'.$multiplier.'">'.$wheelString.'</WheelBonus>';
                    $allTotalWin += $bonusWin;
                }
                else
                    $wheelInfo = '<WheelBonus wheelSpins="0" wheelMultiplier="0"></WheelBonus>';
            }

            
            
            if($allTotalWin > 0)
            {
                $bankName = $postData['slotEvent'];
                if($bonusWin > 0)
                    $bankName = 'freespin';
                $slotSettings->SetBank($bankName, -1 * $allTotalWin);
                $slotSettings->SetBalance($allTotalWin);
                $slotSettings->SetWin($allTotalWin);
            }

            $slotSettings->SetGameData($slotSettings->slotId . 'TotalGameWin', $slotSettings->GetGameData($slotSettings->slotId . 'TotalGameWin') + $allTotalWin);
            if($postData['slotEvent'] == 'freespin')
                $slotSettings->SetGameData($slotSettings->slotId . 'TotalFreespinWin', $slotSettings->GetGameData($slotSettings->slotId . 'TotalFreespinWin') + $allTotalWin);

            $totalWagerWin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalGameWin');
            $freeSpinWin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalFreespinWin');
            $baseGameWin = $totalWagerWin - $freeSpinWin;
            $bonusAwarded = 'N';            
            $isFreespin = $postData['slotEvent'] == 'freespin' ? 'Y':'N';            
            $sxe = new SimpleXMLElement('<GameResponse type="Logic"></GameResponse>');
            $header = simplexml_load_string('<Header sessionID="vkWv5HHtCBUMs3Gq1ogvjRBPthnKy49aqkvJ/CKj2EKCnk8ZnfY+aXVrGjaDSipAQKeZRMBeWY2+KgSXm60lcFLhX6a7iZfppr/nfhKgFY4=" ccyCode="GBP" deciSep="." thousandSep="," lang="en_US" gameID="20191" versionID="1_0" fullVersionID="1.0.11" isRecovering="N"/>');
            $accountData = simplexml_load_string('<AccountData><AccountData><CurrencyMultiplier>1</CurrencyMultiplier></AccountData></AccountData>');
            $balance = simplexml_load_string('<Balances><Balance name="CASH_BALANCE" value="'.($slotSettings->GetBalance() * 100).'"/></Balances>');
            $gameResult = simplexml_load_string('<GameResult stake="'.(string)$postData->Stake['total'].'" stakePerLine="10" paylineCount="20" totalWin="'.($allTotalWin * 100).'" betID="">                    
                    <BGInfo totalWagerWin="'.($totalWagerWin * 100).'" bgWinnings="'.($baseGameWin * 100).'" baseGameSpinsRemaining="0" isMaxWin="0"/>                    
                </GameResult>');

            $reelResults = simplexml_load_string('<ReelResults numSpins="3"></ReelResults>');

            for($g = 0; $g < 3; $g++)
            {
                $paylines = '';
                $scatterWin = $allScatterWins[$g];
                $lineWins = $allLineWins[$g];
                $totalWin = 0;
                if(!empty($lineWins))                
                {
                    foreach($lineWins as $winline)
                    {
                        $paylines .= '<PaylineWin index="'.$winline[0].'" winVal="'.($winline[1] * 100).'" awardIndex = "'.$winline[3].'" awardTableIndex = "0">'.$winline[2].'</PaylineWin>';
                        $totalWin += $winline[1];
                    }
                }
                $reels = $allReels[$g];
                $reelSpin = simplexml_load_string('<ReelSpin spinIndex="'.($g).'" reelsetIndex="'.$reelSetIndex.'" winCountPL="'.count($lineWins).'" winCountSC="'.$winSC.'" spinWins="'.($totalWin * 100).'" freeSpin="'.$isFreespin.'" bonusAwarded="'.$bonusAwarded.'" manualNoWin="'.$manualNoWin.'">
                            <ReelStops>'.implode('|',$reels['rp']).'</ReelStops>
                            '.$paylines.$scatterWin.'
                        </ReelSpin>');
                $this->sxml_append($reelResults, $reelSpin);
            }
            $this->sxml_append($gameResult, $reelResults);

            $wi = simplexml_load_string($wheelInfo);
            $this->sxml_append($gameResult, $wi);

            if($postData['slotEvent'] == 'freespin' && $freespinInfo == '')
            {
                //set freespin info during freespin (not triggering freespin)
                $totalFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                $currentFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');                
                $freespinInfo = '<FSInfo fsWinnings="'.($freeSpinWin * 100).'" freeSpinsTotal="'.$totalFreespin.'" freeSpinNumber="'.$currentFreespin.'" />';
            }
            if($freespinInfo != '')
            {
                $fs = simplexml_load_string($freespinInfo);
                $this->sxml_append($gameResult, $fs);
            }          

            if($postData['slotEvent'] == 'bet')
            {
                if($freespinInfo != '')
                {
                    // $bgRecoveryInfo = '<BaseGameRecoveryInfo><ReelResults numSpins="1">
                    //         <ReelSpin spinIndex="0" reelsetIndex="0" winCountPL="'.count($lineWins).'" winCountSC="'.$winSC.'" spinWins="'.($totalWin * 100).'" freeSpin="N" bonusAwarded="N" manualNoWin="'.$manualNoWin.'">
                    //             <ReelStops>'.implode('|',$reels['rp']).'</ReelStops>
                    //             '.$paylines.$scatterWin.'
                    //         </ReelSpin>
                    //     </ReelResults></BaseGameRecoveryInfo>';
                    // $slotSettings->SetGameData($slotSettings->slotId . 'bgRecoveryInfo', $bgRecoveryInfo); 
                    $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', 'Freespin');                    
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 10);
                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);          
                }
            }
            else
            {
                // $bgRecoveryInfo = $slotSettings->GetGameData($slotSettings->slotId . 'bgRecoveryInfo'); 
                // $bi = simplexml_load_string($bgRecoveryInfo);
                // $this->sxml_append($gameResult, $bi);
            }            
            
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


