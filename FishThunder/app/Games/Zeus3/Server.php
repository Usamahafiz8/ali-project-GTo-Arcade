<?php 
namespace VanguardLTE\Games\Zeus3
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
            for($r = 0; $r < 6; $r++)
                for($c = 0; $c < 7; $c++)
                {
                    if($line[$r][$c] == 1)
                    {
                        $res[] = $c * 6 + $r;
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
                                return '{"javascripts":["/games/Zeus3/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/mainjs/application/capabilitiesdetector.CapabilitiesDetectorBootstrapper.js?resourceversion=5.0.0.11-1663077573&appcode=capabilities-detector","/games/Zeus3/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/translationjs/bundles/translations/#{locale}/capabilitiesdetector.LocalizationBundle.js?resourceversion=5.0.0.11-1663077573&appcode=capabilities-detector"],"jsons":["/games/Zeus3/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/translationjson/bundles/translations/#{locale}/capabilitiesdetector.LocalizationBundle.json?resourceversion=5.0.0.11-1663077573&appcode=capabilities-detector"],"main_class":"capabilitiesdetector.CapabilitiesDetectorBootstrapper","supported_locales":["it_IT","ru_RU","el_GR","pl_PL","ro_RO","tr_TR","fr_FR","cs_CZ","hu_HU","ca_ES","de_DE","sk_SK","es_ES","nl_NL","fr_CA","sv_SE","da_DK","bg_BG","hr_HR","fi_FI","en_GB","et_EE","lt_LT","lv_LV","sl_SI","no_NO","pt_PT"],"default_locale":"en"}';
                            break;
                            case "gls-platform":
                                return '{"javascripts":["/games/Zeus3/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/mainjs/application/glsplatform.GlsPlatform.js?resourceversion=5.0.0.11-1663077573&appcode=gls-platform","/games/Zeus3/acy-resource.wimobile.casinarena.com/resource-service/metadatajs/bundles/metadata/glsplatform.MetaDataBundle.js?resourceversion=5.0.0.11-1663077573&appcode=gls-platform&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=zeus3_prt&locale=en_US&webaudio=true","/games/Zeus3/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/translationjs/bundles/translations/#{locale}/glsplatform.LocalizationsBundle.js?resourceversion=5.0.0.11-1663077573&appcode=gls-platform"],"jsons":["/games/Zeus3/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/metadatajson/bundles/metadata/glsplatform.MetaDataBundle.json?resourceversion=5.0.0.11-1663077573&appcode=gls-platform&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=zeus3_prt&locale=en_US&webaudio=true","/games/Zeus3/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/translationjson/bundles/translations/#{locale}/glsplatform.LocalizationsBundle.json?resourceversion=5.0.0.11-1663077573&appcode=gls-platform"],"main_class":"glsplatform.GlsPlatform","supported_locales":["sl_SI","pl_PL","lt_LT","bg_BG","ru_RU","ro_RO","el_GR","pt_PT","en_GB","sk_SK","cs_CZ","ca_ES","fr_CA","es_ES","lv_LV","hr_HR","it_IT","fi_FI","de_DE","hu_HU","fr_FR","tr_TR","sv_SE","da_DK","no_NO","et_EE","nl_NL"],"default_locale":"en"}';
                                break;
                            case "lean-regular-partner-adapter":
                                return '{"javascripts":["/games/Zeus3/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/mainjs/application/leanpartneradapter.LeanPartnerAdapter.js?resourceversion=5.0.0.11-1663077573&appcode=lean-regular-partner-adapter","/games/Zeus3/acy-resource.wimobile.casinarena.com/resource-service/metadatajs/bundles/metadata/leanpartneradapter.MetaDataBundle.js?resourceversion=5.0.0.11-1663077573&appcode=lean-regular-partner-adapter&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=zeus3_prt&locale=en_US&webaudio=true","/games/Zeus3/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/translationjs/bundles/translations/#{locale}/leanpartneradapter.LocalizationsBundle.js?resourceversion=5.0.0.11-1663077573&appcode=lean-regular-partner-adapter"],"jsons":["/games/Zeus3/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/metadatajson/bundles/metadata/leanpartneradapter.MetaDataBundle.json?resourceversion=5.0.0.11-1663077573&appcode=lean-regular-partner-adapter&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=zeus3_prt&locale=en_US&webaudio=true","/games/Zeus3/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/translationjson/bundles/translations/#{locale}/leanpartneradapter.LocalizationsBundle.json?resourceversion=5.0.0.11-1663077573&appcode=lean-regular-partner-adapter"],"main_class":"commonadapter.LandscapePartnerAdapter","supported_locales":["it_IT","ru_RU","el_GR","pl_PL","ro_RO","tr_TR","fr_FR","cs_CZ","hu_HU","ca_ES","de_DE","sk_SK","es_ES","nl_NL","fr_CA","sv_SE","da_DK","bg_BG","hr_HR","fi_FI","en_GB","et_EE","lt_LT","lv_LV","sl_SI","no_NO","pt_PT"],"default_locale":"en"}';
                                break;
                            case "zeus3_prt":
                                return '{
                                    "default_locale": "en",
                                    "inject": [
                                        {
                                            "script": {
                                                "data-main": "content/zeus3_prt/lib/require/require_cfg.js?resourceversion=5.0.0.11-1663077573&appcode=zeus3_prt&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=zeus3_prt&locale=en_US&webaudio=true",
                                                "src": "content/zeus3_prt/lib/require/require.js",
                                                "type": "text/javascript"
                                            }
                                        },
                                        {
                                            "link": {
                                                "rel": "stylesheet",
                                                "href": "content/zeus3_prt/resources/css/game.css"
                                            }
                                        }
                                    ],
                                    "javascripts": [
                                        "content/zeus3_prt/resources/js/zeus3_prt.game.js?resourceversion=5.0.0.11-1663077573&appcode=zeus3_prt&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=zeus3_prt&locale=en_US&webaudio=true",
                                        "content/zeus3_prt/resources/js/zeus3_prt.metadatabundle.js?resourceversion=5.0.0.11-1663077573&appcode=zeus3_prt&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=zeus3_prt&locale=en_US&webaudio=true"
                                    ],
                                    "main_class": "zeus3_prt.Game",
                                    "publish_events": {"platform":"1"},
                                    "orientation": "LANDSCAPE",
                                    "supported_locales": [
                                        "bg",
                                        "ca",
                                        "cs",
                                        "da",
                                        "de",
                                        "el",
                                        "en",
                                        "es",
                                        "et",
                                        "fi",
                                        "fr",
                                        "hr",
                                        "hu",
                                        "it",
                                        "lt",
                                        "lv",
                                        "nl",
                                        "no",
                                        "pl",
                                        "pt",
                                        "ro",
                                        "ru",
                                        "sk",
                                        "sl",
                                        "sv",
                                        "tr"
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
                        $filename = base_path() . '/app/Games/Zeus3/game.txt';
                        $file = fopen($filename, "r" );
                        $filesize = filesize( $filename );
                        $response = fread( $file, $filesize );
                        $response = str_replace('BAL_REPLACE', $slotSettings->GetBalance() * 100, $response);
                        fclose( $file );

                        $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', '');
                        $slotSettings->SetGameData($slotSettings->slotId . 'TotalFreespinWin', '0');
                        $slotSettings->SetGameData($slotSettings->slotId . 'StickyWild', []);
                        $slotSettings->SetGameData($slotSettings->slotId . 'FeatureIndex', 0); 
                        break;                        
                    case 'Logic':                        
                        $allbet = (int)$postData->WagerInfo['totalStake'] * 0.01;
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
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'TotalFreespinWin', 0);
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
            $linesId = $slotSettings->GetPaylines();
            $reelSetIndex = 0;
            if($postData['slotEvent'] == 'freespin')
            {                
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bonus');
                $reelSetIndex = 1;
                $linesId = $slotSettings->GetFreespinPaylines();
            }
            else
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bet');
            }
            $reelName = 'Reels'.$reelSetIndex;
            $lines = count($linesId);
            $allbet = (int)$postData->WagerInfo['totalStake'] * 0.01;
            $betLine = $allbet / 0.4 * 0.01; //paytable is 0.4 based      

            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $allbet);
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
            if($postData['slotEvent'] == 'freespin')
            {
                if($winType == 'bonus')
                    $winType = 'win';
            }
            
            $spinAcquired = false;             
            $gameWin = 0;//$slotSettings->GetGameData($slotSettings->slotId . 'GameWin');

            $minReels = [];
            $minLineWins = [];
            $minTotalWin = -1;
            $minFreespinsWon = 0;
            $minWildInfo = [];
            $minWinningReels = [];
            $minFreespinInfo = '';

            $totalWin = 0;
            $freespinsWon = 0;
            $lineWins = [];
            $reels = [];
            
            $scatter = 12;            
            $wild = [13];
            $zeus = [9, 10, 11];
            $freespinInfo = '';
            $scatterWin = '';            
            $scatterCount = 0;
            $wildInfo = [];
            $newWildPos = [];
            $winningReels = [];
            $freespinTriggerWin = 0;
            
            for( $i = 0; $i <= 300; $i++ ) 
            {
                $scatterWin = '';
                $freespinInfo = '';
                $totalWin = 0;
                $freespinsWon = 0;
                $lineWins = [];
                $winningReels = [];
                $cWins = array_fill(0, $lines, 0);
                $newWildPos = [];
                $wildInfo = [];
                
                if($this->debug && $postData['slotEvent'] != 'freespin')
                {                 
                    $winType = 'bonus';
                }

                $reels = $slotSettings->GetReelStrips($winType, $reelName, $postData['slotEvent']);
                $reels0 = $reels;

                //check zeus symbol
                if($postData['slotEvent'] == 'freespin')
                {
                    $totalFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                    $currentFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');                
                    $leftSpin = $totalFreespin - $currentFreespin;
                    if($leftSpin == 0)
                        $leftSpin = 1;
                    if($winType == 'win')
                        $spinWinLimit = ($allbet * 20 - $slotSettings->GetGameData($slotSettings->slotId . 'TotalGameWin')) / $leftSpin;

                    for($r = 0; $r < 6; $r++)
                    {
                        $isZeusExist = false;
                        $zeusPosition = -1;
                        $reelSize = 7 - $r;
                        for($c = 0; $c < 7; $c++)
                        {
                            if(in_array($reels['reel'.($r+1)][$c], $zeus))
                            {
                                $zeusPosition = $c;
                                $isZeusExist = true;
                                break;
                            }
                        }

                        if($isZeusExist)
                        {
                            $move = 0;
                            if($zeusPosition != 0)
                            {
                                $move = -$zeusPosition;
                            }
                            else
                            {
                                $nonZeusPosition = 0;
                                for($c = 0; $c < $reelSize; $c++)
                                {
                                    if(!in_array($reels['reel'.($r+1)][$c], $zeus))
                                    {
                                        $nonZeusPosition = $c;
                                        break;
                                    }
                                }
                                $move = $reelSize - $nonZeusPosition;
                                if($move == $reelSize)
                                    $move = 0;
                            }
                            for($c = 0; $c < 7; $c++)
                                if($reels['reel'.($r+1)][$c] != -1)
                                    $reels['reel'.($r+1)][$c] = $wild[0];
                            $wildInfo[] = [$r, $move];
                        }
                    }
                }
                else
                {
                    for($r = 0; $r < 6; $r++)
                    {
                        $isAllZeus = true;
                        for($c = 0; $c < 7; $c++)
                        {
                            if(!in_array($reels['reel'.($r+1)][$c], $zeus) && $reels['reel'.($r+1)][$c] != -1)
                            {
                                $isAllZeus = false;
                                break;
                            }
                        }
                        if($isAllZeus)
                        {
                            for($c = 0; $c < 7; $c++)
                                if($reels['reel'.($r+1)][$c] != -1)
                                    $reels['reel'.($r+1)][$c] = $wild[0];
                            $wildInfo[] = [$r, 0];
                        }
                    }
                }

                //unify zeus symbols
                for($r = 0; $r < 6; $r++)
                {
                    for($c = 0; $c < 7; $c++)
                    {
                        if(in_array($reels['reel'.($r+1)][$c], $zeus) && $reels['reel'.($r+1)][$c] != -1)
                        {
                            $reels['reel'.($r+1)][$c] = 14;
                        }
                    }
                }

                $bonusMpl = 1;                
                
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
                           
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) ) 
                            {
                                $emptyLine = [[0,0,0,0,0,0,0],[0,0,0,0,0,0,0],[0,0,0,0,0,0,0],[0,0,0,0,0,0,0],[0,0,0,0,0,0,0], [0,0,0,0,0,0,0]];
                                $tmpWin = $slotSettings->Paytable[$csym][3] * $betLine * $mpl * $bonusMpl;
                                $coin = $slotSettings->Paytable[$csym][3] * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $cWins[$k] = $tmpWin;
                                    $emptyLine[0][$p0] = 1;
                                    $emptyLine[1][$p1] = 1;
                                    $emptyLine[2][$p2] = 1;
                                    $winline = [$k, $coin, $this->getConvertedLine($emptyLine), $tmpWin, $slotSettings->awardIndices[$csym][3]]; //[lineId, coinWon, winPositions]
                                }
                                if(!in_array(0, $winningReels))
                                    $winningReels[] = 0;
                                if(!in_array(1, $winningReels))
                                    $winningReels[] = 1;
                                if(!in_array(2, $winningReels))
                                    $winningReels[] = 2;
                            }

                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) ) 
                            {
                                $emptyLine = [[0,0,0,0,0,0,0],[0,0,0,0,0,0,0],[0,0,0,0,0,0,0],[0,0,0,0,0,0,0],[0,0,0,0,0,0,0], [0,0,0,0,0,0,0]];
                                $tmpWin = $slotSettings->Paytable[$csym][4] * $betLine * $mpl * $bonusMpl;
                                $coin = $slotSettings->Paytable[$csym][4] * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $cWins[$k] = $tmpWin;
                                    $emptyLine[0][$p0] = 1;
                                    $emptyLine[1][$p1] = 1;
                                    $emptyLine[2][$p2] = 1;
                                    $emptyLine[3][$p3] = 1;
                                    $winline = [$k, $coin, $this->getConvertedLine($emptyLine), $tmpWin, $slotSettings->awardIndices[$csym][4]]; //[lineId, coinWon, winPositions]                                                             
                                }
                                if(!in_array(0, $winningReels))
                                    $winningReels[] = 0;
                                if(!in_array(1, $winningReels))
                                    $winningReels[] = 1;
                                if(!in_array(2, $winningReels))
                                    $winningReels[] = 2;
                                if(!in_array(3, $winningReels))
                                    $winningReels[] = 3;
                            }
                            
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) ) 
                            {
                                $emptyLine = [[0,0,0,0,0,0,0],[0,0,0,0,0,0,0],[0,0,0,0,0,0,0],[0,0,0,0,0,0,0],[0,0,0,0,0,0,0], [0,0,0,0,0,0,0]];
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
                                    $winline = [$k, $coin, $this->getConvertedLine($emptyLine), $tmpWin, $slotSettings->awardIndices[$csym][5]]; //[lineId, coinWon,winPositions]                                                            
                                }
                                if(!in_array(0, $winningReels))
                                    $winningReels[] = 0;
                                if(!in_array(1, $winningReels))
                                    $winningReels[] = 1;
                                if(!in_array(2, $winningReels))
                                    $winningReels[] = 2;
                                if(!in_array(3, $winningReels))
                                    $winningReels[] = 3;
                                if(!in_array(4, $winningReels))
                                    $winningReels[] = 4;
                            }

                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) && ($s[5] == $csym || in_array($s[5], $wild)) ) 
                            {
                                $emptyLine = [[0,0,0,0,0,0,0],[0,0,0,0,0,0,0],[0,0,0,0,0,0,0],[0,0,0,0,0,0,0],[0,0,0,0,0,0,0], [0,0,0,0,0,0,0]];
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
                                    $emptyLine[5][$p5] = 1;
                                    $winline = [$k, $coin, $this->getConvertedLine($emptyLine), $tmpWin, $slotSettings->awardIndices[$csym][6]]; //[lineId, coinWon,winPositions]                                                            
                                }
                                if(!in_array(0, $winningReels))
                                    $winningReels[] = 0;
                                if(!in_array(1, $winningReels))
                                    $winningReels[] = 1;
                                if(!in_array(2, $winningReels))
                                    $winningReels[] = 2;
                                if(!in_array(3, $winningReels))
                                    $winningReels[] = 3;
                                if(!in_array(4, $winningReels))
                                    $winningReels[] = 4;
                                if(!in_array(5, $winningReels))
                                    $winningReels[] = 5;
                            }
                        }
                    }

                    if( $cWins[$k] > 0 && !empty($winline))
                    {
                        array_push($lineWins, $winline);
                        $totalWin += $cWins[$k];
                    }
                }
                
                // $totalWin += $gameWin;

                //calc freespin
                $scatterCount = 0;
                $scatterPos = [];
                
                for($r = 1; $r <= 6; $r++)
                {
                    for($c = 0; $c < 7; $c++)
                    {
                        if($reels0['reel'.$r][$c] == $scatter)
                        {
                            $scatterCount++;
                            $scatterPos[] = $c * 6 + $r - 1;
                        }
                    }
                }

                if($scatterCount > 2 && $winType != 'bonus')
                    continue;
                if($scatterCount > 4)
                    continue;
                if($scatterCount > 2)
                {
                    $freespinsWon = 10;
                    $freespinTriggerWin = 0;
                    if($scatterCount == 4)
                    {
                        $freespinsWon = 25;
                        $freespinTriggerWin = $allbet * 10;
                    }
                    else if($scatterCount == 5)
                    {
                        $freespinsWon = 50;
                        $freespinTriggerWin = $allbet * 50;
                    }
                    $totalWin += $freespinTriggerWin;
                    $scatterWin = '<ScatterWin winVal="0" awardIndex="'.$slotSettings->awardIndices[$scatter][$scatterCount].'">'.implode('|', $scatterPos).'</ScatterWin>';
                    $freespinInfo = '<Feature index="1" name="FreeSpins"><data freeSpinTriggerWin="'.$freespinTriggerWin.'" totalFreeSpinsTriggered="'.$freespinsWon.'" /></Feature>';
                }

                if($minTotalWin == -1 && $scatterCount < 3 || ($minTotalWin > $totalWin && $totalWin > 0))
                {
                    $minTotalWin = $totalWin;
                    $minLineWins = $lineWins;
                    $minFreespinsWon = $freespinsWon;
                    $minReels = $reels;
                    $minWildInfo = $wildInfo;
                    $minWinningReels = $winningReels;
                    $minFreespinInfo = $freespinInfo;
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
                $reels = $minReels;
                $lineWins = $minLineWins;
                $totalWin = $minTotalWin;
                $freespinsWon = $minFreespinsWon;                    
                $scatterWin = '';
                $wildInfo = $minWildInfo;
                $winningReels = $minWinningReels;
                $freespinInfo = $minFreespinInfo;
            }

            $coinWin = 0; //coins won
            $paylines = '';
            if(!empty($lineWins))
            {
                foreach($lineWins as $winline)
                {
                    $coinWin += $winline[1]; //sum up coins
                    $paylines .= '<PaylineWin index="'.$winline[0].'" winVal="'.($winline[3] * 100).'" awardIndex="'.$winline[4].'" awardTableIndex="0">'.$winline[2].'</PaylineWin>';
                }
            }

            $winSC = 0;
            if($scatterCount > 2)
                $winSC = 1;
            
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
            $gameResult = simplexml_load_string('<GameResult stake="'.(int)$postData->WagerInfo['totalStake'].'" stakePerLine="1" paylineCount="192" totalWin="'.($totalWin * 100).'" betID="">
                    <ReelResults numSpins="1">
                        <ReelSpin spinIndex="0" reelsetIndex="'.$reelSetIndex.'" winCountPL="'.count($lineWins).'" winCountSC="'.$winSC.'" spinWins="'.($totalWin * 100).'" freeSpin="'.$isFreespin.'" bonusAwarded="'.$bonusAwarded.'" manualNoWin="'.$manualNoWin.'">
                            <ReelStops>'.implode('|',$reels['rp']).'</ReelStops>
                            '.$paylines.$scatterWin.'
                        </ReelSpin>
                    </ReelResults>
                    <GameWinInfo totalWagerWin="'.($totalWagerWin * 100).'" totalBGWin="'.($baseGameWin * 100).'" totalFSWin="'.($freeSpinWin * 100).'" maxWinValue="25000000" isMaxWin="N"/>                    
                </GameResult>');
            
            if($postData['slotEvent'] == 'freespin' && $freespinInfo == '')
            {
                //set freespin info during freespin (not triggering freespin)
                $totalFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                $currentFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');                
                $leftSpin = $totalFreespin - $currentFreespin;
                $lastFreeSpin = 'N';                
                if($leftSpin == 0)
                    $lastFreeSpin = 'Y';
                $freespinInfo = '<Feature index="1" name="FreeSpins"><data totalFreeSpinsWin="'.($freeSpinWin * 100).'" totalFreeSpinsAwarded="'.$totalFreespin.'" freeSpinsReTriggered="'.$freespinsWon.'" freeSpinReTriggerWin="'.($freespinTriggerWin * 100).'" remainingFreeSpins="'.$leftSpin.'" lastFreeSpin="'.$lastFreeSpin.'" /></Feature>';
            }
            if($freespinInfo != '')
            {
                $fs = simplexml_load_string($freespinInfo);
                $this->sxml_append($gameResult, $fs);
            }


            if(count($wildInfo) > 0)
            {
                $wildInfoStr = [];
                foreach($wildInfo as $wi)
                {
                    $animated = 0;
                    if(in_array($wi[0], $winningReels))
                        $animated = 1;
                    $wi[] = $animated;
                    $wildInfoStr[] = implode('|', $wi);
                }
                $wildInfoStr = implode(',', $wildInfoStr);
                
                $wi = simplexml_load_string('<WildInfo>'.$wildInfoStr.'</WildInfo>');
                $this->sxml_append($gameResult, $wi);
            }
            $reelData = '<ReelData reel1="'.implode(',',$reels['reel1']).'" reel2="'.implode(',',$reels['reel2']).'" reel3="'.implode(',',$reels['reel3']).'" reel4="'.implode(',',$reels['reel4']).'" reel5="'.implode(',',$reels['reel5']).'" reel6="'.implode(',',$reels['reel6']).'"></ReelData>';
            $rd = simplexml_load_string($reelData);
            $this->sxml_append($gameResult, $rd);

            if($postData['slotEvent'] == 'bet')
            {
                if($scatterWin != '')
                {
                    $bgRecoveryInfo = '<BaseGameRecoveryInfo><ReelResults numSpins="1">
                            <ReelSpin spinIndex="0" reelsetIndex="0" winCountPL="'.count($lineWins).'" winCountSC="'.$winSC.'" spinWins="'.($totalWin * 100).'" freeSpin="N" bonusAwarded="N" manualNoWin="'.$manualNoWin.'">
                                <ReelStops>'.implode('|',$reels['rp']).'</ReelStops>
                                '.$paylines.$scatterWin.'
                            </ReelSpin>
                        </ReelResults>'.$freespinInfo.'</BaseGameRecoveryInfo>';
                    $slotSettings->SetGameData($slotSettings->slotId . 'bgRecoveryInfo', $bgRecoveryInfo); 
                    $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', 'Freespin');                    
                    $slotSettings->SetGameData($slotSettings->slotId . 'SpinFeature', '-1');
                }
            }
            else
            {
                $bgRecoveryInfo = $slotSettings->GetGameData($slotSettings->slotId . 'bgRecoveryInfo'); 
                $bi = simplexml_load_string($bgRecoveryInfo);
                $this->sxml_append($gameResult, $bi);
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


