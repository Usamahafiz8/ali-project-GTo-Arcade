<?php 
namespace VanguardLTE\Games\DragonSpin
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
                                return '{"javascripts":["/games/DragonSpin/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/mainjs/application/capabilitiesdetector.CapabilitiesDetectorBootstrapper.js?resourceversion=5.0.0.11-1656345280&appcode=capabilities-detector","/games/DragonSpin/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/translationjs/bundles/translations/#{locale}/capabilitiesdetector.LocalizationBundle.js?resourceversion=5.0.0.11-1656345280&appcode=capabilities-detector"],"jsons":["/games/DragonSpin/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/translationjson/bundles/translations/#{locale}/capabilitiesdetector.LocalizationBundle.json?resourceversion=5.0.0.11-1656345280&appcode=capabilities-detector"],"main_class":"capabilitiesdetector.CapabilitiesDetectorBootstrapper","supported_locales":["it_IT","ru_RU","el_GR","pl_PL","ro_RO","tr_TR","fr_FR","cs_CZ","hu_HU","ca_ES","de_DE","sk_SK","es_ES","nl_NL","fr_CA","sv_SE","da_DK","bg_BG","hr_HR","fi_FI","en_GB","et_EE","lt_LT","lv_LV","sl_SI","no_NO","pt_PT"],"default_locale":"en"}';
                            break;
                            case "gls-platform":
                                return '{"javascripts":["/games/DragonSpin/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/mainjs/application/glsplatform.GlsPlatform.js?resourceversion=5.0.0.11-1656345280&appcode=gls-platform","/games/DragonSpin/acy-resource.wimobile.casinarena.com/resource-service/metadatajs/bundles/metadata/glsplatform.MetaDataBundle.js?resourceversion=5.0.0.11-1656345280&appcode=gls-platform&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=true&partnercode=nyx_goldennugget&realmoney=false&gamecode=dragonspin&locale=en_EN&webaudio=true","/games/DragonSpin/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/translationjs/bundles/translations/#{locale}/glsplatform.LocalizationsBundle.js?resourceversion=5.0.0.11-1656345280&appcode=gls-platform"],"jsons":["/games/DragonSpin/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/metadatajson/bundles/metadata/glsplatform.MetaDataBundle.json?resourceversion=5.0.0.11-1656345280&appcode=gls-platform&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=true&partnercode=nyx_goldennugget&realmoney=false&gamecode=dragonspin&locale=en_EN&webaudio=true","/games/DragonSpin/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/translationjson/bundles/translations/#{locale}/glsplatform.LocalizationsBundle.json?resourceversion=5.0.0.11-1656345280&appcode=gls-platform"],"main_class":"glsplatform.GlsPlatform","supported_locales":["sl_SI","pl_PL","lt_LT","bg_BG","ru_RU","ro_RO","el_GR","pt_PT","en_GB","sk_SK","cs_CZ","ca_ES","fr_CA","es_ES","lv_LV","hr_HR","it_IT","fi_FI","de_DE","hu_HU","fr_FR","tr_TR","sv_SE","da_DK","no_NO","et_EE","nl_NL"],"default_locale":"en"}';
                                break;
                            case "lean-regular-partner-adapter":
                                return '{"javascripts":["/games/DragonSpin/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/mainjs/application/leanpartneradapter.LeanPartnerAdapter.js?resourceversion=5.0.0.11-1656345280&appcode=lean-regular-partner-adapter","/games/DragonSpin/acy-resource.wimobile.casinarena.com/resource-service/metadatajs/bundles/metadata/leanpartneradapter.MetaDataBundle.js?resourceversion=5.0.0.11-1656345280&appcode=lean-regular-partner-adapter&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=true&partnercode=nyx_goldennugget&realmoney=false&gamecode=dragonspin&locale=en_EN&webaudio=true","/games/DragonSpin/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/translationjs/bundles/translations/#{locale}/leanpartneradapter.LocalizationsBundle.js?resourceversion=5.0.0.11-1656345280&appcode=lean-regular-partner-adapter"],"jsons":["/games/DragonSpin/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/metadatajson/bundles/metadata/leanpartneradapter.MetaDataBundle.json?resourceversion=5.0.0.11-1656345280&appcode=lean-regular-partner-adapter&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=true&partnercode=nyx_goldennugget&realmoney=false&gamecode=dragonspin&locale=en_EN&webaudio=true","/games/DragonSpin/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/translationjson/bundles/translations/#{locale}/leanpartneradapter.LocalizationsBundle.json?resourceversion=5.0.0.11-1656345280&appcode=lean-regular-partner-adapter"],"main_class":"commonadapter.TopboxPartnerAdapter","supported_locales":["it_IT","ru_RU","el_GR","pl_PL","ro_RO","tr_TR","fr_FR","cs_CZ","hu_HU","ca_ES","de_DE","sk_SK","es_ES","nl_NL","fr_CA","sv_SE","da_DK","bg_BG","hr_HR","fi_FI","en_GB","et_EE","lt_LT","lv_LV","sl_SI","no_NO","pt_PT"],"default_locale":"en"}';
                                break;
                            case "dragonspin":
                                return '{
                                    "default_locale": "en",
                                    "inject": [{
                                      "link": {
                                        "rel": "stylesheet",
                                        "href": "content/dragonspin/resources/css/dragon.css?resourceversion=5.0.0.11-1656345280&appcode=dragonspin&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=true&partnercode=nyx_goldennugget&realmoney=false&gamecode=dragonspin&locale=en_EN&webaudio=true"
                                      }
                                    }],
                                    "javascripts": [
                                      "content/dragonspin/app/dragon.min.js?resourceversion=5.0.0.11-1656345280&appcode=dragonspin&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=true&partnercode=nyx_goldennugget&realmoney=false&gamecode=dragonspin&locale=en_EN&webaudio=true",
                                      "content/dragonspin/bundles/metadata/dragon.MetaDataBundle.js?resourceversion=5.0.0.11-1656345280&appcode=dragonspin&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=true&partnercode=nyx_goldennugget&realmoney=false&gamecode=dragonspin&locale=en_EN&webaudio=true",
                                      "content/dragonspin/app/framework.reel.js?resourceversion=5.0.0.11-1656345280&appcode=dragonspin&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=true&partnercode=nyx_goldennugget&realmoney=false&gamecode=dragonspin&locale=en_EN&webaudio=true",
                                      "content/dragonspin/app/framework.lib.js?resourceversion=5.0.0.11-1656345280&appcode=dragonspin&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=true&partnercode=nyx_goldennugget&realmoney=false&gamecode=dragonspin&locale=en_EN&webaudio=true"
                                    ],
                                    "main_class": "dragon.Game",
                                    "orientation": "BOTH",
                                    "publish_events": {"platform":"1"},
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
                                  }';
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
                        $filename = base_path() . '/app/Games/DragonSpin/game.txt';
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
            $gameCount = 1;
            if($postData['slotEvent'] == 'freespin')
            {                
                $featureIndex = $slotSettings->GetGameData($slotSettings->slotId . 'FeatureIndex');
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bonus');
                if($featureIndex == 1 || $featureIndex == 2)
                {
                    $reelName = 'Reels1';  
                    $reelSetIndex = 1; 
                }
                else
                {
                    $reelName = 'Reels2';
                    $reelSetIndex = 3;
                    $gameCount = 3;
                }                
            }
            else
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bet');
                $reelName = 'Reels0';               
                $reelSetIndex = 0; 
            }

            $lines = count($linesId);
            $allbet = (int)((string)$postData->Stake['total']) * 0.01;     
            if($postData['slotEvent'] == 'freespin')                            
                $allbet = $slotSettings->GetGameData($slotSettings->slotId . 'AllBet');  
            $betLine = $allbet / 0.3 * 0.01; 

            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $allbet);
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
            
            $spinAcquired = false;             

            $minAllReels = [];
            $minAllLineWins = [];
            $minAllTotalWin = -1;
            $minAllScatterWins = [];
            $minMsReplacement = '';
            $minFeatureIndex = 0;
            $minFreespinsWon = 0;
            $minWildInfo = '';
            $minNewWildPos = [];
            
            $totalWin = 0;
            $lineWins = [];
            $reels = [];
            
            $wild = [0];
            $scatter = '11';
            $freespinInfo = '';

            $allLineWins = [];
            $allScatterWins = [];
            $allReels = [];
            $allTotalWin = 0;
            $msReplacement = '';
            
            $featureIndex = 0;
            $freespinsWon = 0;
            $wildInfo = '';
            $newWildPos = [];
            for( $i = 0; $i <= 500; $i++ ) 
            {
                $msReplacement = [];
                $newWildPos = [];
                $freespinInfo = '';
                $wildInfo = '';
                $allTotalWin = 0;   
                $freespinsWon = 0;
                if($postData['slotEvent'] == 'freespin')
                {
                    $featureIndex = $slotSettings->GetGameData($slotSettings->slotId . 'FeatureIndex');                    
                }

                if($this->debug && $postData['slotEvent'] != 'freespin')
                {                 
                    $winType = 'bonus';
                }

                //set mystery symbols
                $msSymbolCount = rand(2, 3);                
                $msSymbols = [];
                for($m = 0; $m < $msSymbolCount; $m++)
                {
                    //mystery symbol can be symbols 0~9 (except bonus)
                    $msSymbol = rand(0, 9);
                    while(in_array($msSymbol, $msSymbols))
                    {
                        $msSymbol = rand(0, 9);
                    }
                    $msSymbols[] = $msSymbol;
                }
                $msPerReel = [];
                for($r = 0; $r < 5; $r++)
                {
                    $msSymbol = $msSymbols[rand(0, count($msSymbols) - 1)];
                    $msPerReel[] = $msSymbol;                    
                }

                $msReplacement = '<MSReplacement symbolCount="'.$msSymbolCount.'">'.implode('|', $msPerReel).'</MSReplacement>';
                $bonusAcquired = false;
                for($g = 0; $g < $gameCount; $g++)
                {
                    $scatterWin = '';
                    $totalWin = 0;
                    $lineWins = [];
                    $cWins = array_fill(0, $lines, 0);
                    $lastRp = 0;
                    if($featureIndex == 3 && $g > 0)
                        $lastRp = $allReels[$g - 1]['rp'][2] - 1;
                    $reels = $slotSettings->GetReelStrips($winType, $reelName, $postData['slotEvent'], $g, $lastRp);
                    
                    for($r = 0; $r < 5; $r++)
                        for($c = 0; $c < 3; $c++)
                            if($reels['reel'.($r+1)][$c] == '10')
                                $reels['reel'.($r+1)][$c] = $msPerReel[$r];

                    if($featureIndex == 3)
                    {
                        //make blast reel
                        $reels['rp'][3] = $reels['rp'][2];
                        $reels['rp'][1] = $reels['rp'][2];
                        $reels['reel2'][0] = $reels['reel3'][0];
                        $reels['reel2'][1] = $reels['reel3'][1];
                        $reels['reel2'][2] = $reels['reel3'][2];
                        $reels['reel4'][0] = $reels['reel3'][0];
                        $reels['reel4'][1] = $reels['reel3'][1];
                        $reels['reel4'][2] = $reels['reel3'][2];
                    }

                    $bonusMpl = 1;
                    if($postData['slotEvent'] == 'freespin')
                    {
                        if($featureIndex == 1) //raining wild
                        {
                            $wildCnt = rand(3, 5);
                            $randomWilds = [];
                            for($w = 0; $w < $wildCnt; $w++)
                            {
                                $pos = rand(0, 14);
                                while(in_array($pos, $randomWilds))
                                {
                                    $pos = rand(0, 14);
                                }
                                $randomWilds[] = $pos;

                                $r = $pos % 5;
                                $c = (int)($pos / 5);
                                $reels['reel'.($r+1)][$c] = $wild[0];
                            }                            
                            $wildInfo = '<WildInfo><WildPos>'.implode('|', $randomWilds).'</WildPos></WildInfo>';
                        }
                        else if($featureIndex == 2) //persisting wild
                        {
                            $stickyWild = $slotSettings->GetGameData($slotSettings->slotId . 'StickyWild');
                            $currentFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                            $wildCnt = rand(1,2);
                            if($currentFreespin == 4)
                                $wildCnt = rand(1,3);
                            else if($currentFreespin == 5)
                                $wildCnt = rand(2,5);
                            $newWildPos = [];
                            for($w = 0; $w < $wildCnt; $w++)
                            {
                                $pos = rand(0, 14);
                                while(in_array($pos, $stickyWild))
                                {
                                    $pos = rand(0, 14);
                                }
                                $newWildPos[] = $pos;

                                $r = $pos % 5;
                                $c = (int)($pos / 5);
                                $reels['reel'.($r+1)][$c] = $wild[0];
                            }
                            for($w = 0; $w < count($stickyWild); $w++)
                            {
                                $r = $stickyWild[$w] % 5;
                                $c = (int)($stickyWild[$w] / 5);
                                $reels['reel'.($r+1)][$c] = $wild[0];
                            }
                            $wildInfo = '<WildInfo><OldWildPos>'.implode('|', $stickyWild).'</OldWildPos><NewWildPos>'.implode('|', $newWildPos).'</NewWildPos></WildInfo>';
                        }
                    }
                    
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
                                                                                    
                                if($featureIndex != 3 && ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) ) 
                                {
                                    $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
                                    $tmpWin = $slotSettings->Paytable[$csym][3] * $betLine * $mpl * $bonusMpl;
                                    if( $cWins[$k] < $tmpWin ) 
                                    {
                                        $cWins[$k] = $tmpWin;
                                        $emptyLine[0][$p0] = 1;
                                        $emptyLine[1][$p1] = 1;
                                        $emptyLine[2][$p2] = 1;
                                        $winline = [$k, $tmpWin, $this->getConvertedLine($emptyLine), $slotSettings->awardIndices[$reelSetIndex][$csym][3]]; //[lineId, coinWon, winPositions]
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
                                        $winline = [$k, $tmpWin, $this->getConvertedLine($emptyLine), $slotSettings->awardIndices[$reelSetIndex][$csym][4]]; //[lineId, coinWon, winPositions]                                                             
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
                                        $winline = [$k, $tmpWin, $this->getConvertedLine($emptyLine), $slotSettings->awardIndices[$reelSetIndex][$csym][5]]; //[lineId, coinWon,winPositions]                                                            
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
                    $scatterCount = 0;
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

                    if($scatterCount > 2)
                    {
                        $bonusAcquired = true;
                        $featureIndex = rand(1, 3);  //1: raining wild, 2: persisting wild 3: reel blast
                        $freespinsWon = 5;
                        $freespinInfo = '<FSInfo featureIndex="'.$featureIndex.'" fsWinnings="0" freeSpinsTotal="'.$freespinsWon.'" freeSpinNumber="0" isMaxWin="0" />';
                        $scatterWin = '<ScatterWin winVal="'.($slotSettings->Paytable[$scatter][3] * $betLine * 100).'" awardIndex="30">'.implode('|',$scatterPos).'</ScatterWin>';
                        $totalWin += $slotSettings->Paytable[$scatter][3] * $betLine;                                                
                    }
                        
                    $allScatterWins[$g] = $scatterWin;                    
                    $allLineWins[$g] = $lineWins;
                    $allTotalWin += $totalWin;
                    $allReels[$g] = $reels;
                }

                if($winType != 'bonus' && $bonusAcquired)
                    continue;
                    
                if( $minAllTotalWin == -1 || ($allTotalWin < $minAllTotalWin && $allTotalWin > 0))
                {
                    if($freespinsWon == 0)
                    {
                        $minAllLineWins = $allLineWins;
                        $minAllTotalWin = $allTotalWin;
                        $minAllReels = $allReels;
                        $minAllScatterWins = $allScatterWins;
                        $minMsReplacement = $msReplacement;
                        $minFeatureIndex = $featureIndex;
                        $minFreespinsWon = $freespinsWon;
                        $minWildInfo = $wildInfo;
                        $minNewWildPos = $newWildPos;
                    }                    
                }

                if($this->debug)
                {
                    $spinAcquired = true;
                    break;
                }                    
                if($allTotalWin <= $spinWinLimit && (($allTotalWin > 0 && $winType != 'none') || ($winType == 'bonus' && $freespinsWon > 0)))
                {
                    $spinAcquired = true;
                    if($totalWin < 0.5 * $spinWinLimit && $winType != 'bonus')
                        $spinAcquired = false;
                    if($spinAcquired)
                        break;                                        
                }
                else if( $winType == 'none' && $allTotalWin == 0 ) 
                {
                    $spinAcquired = true;
                    break;
                }
            }            

            $manualNoWin = false;
            if(!$spinAcquired)
            {                
                $manualNoWin = true;
                $allReels = $minAllReels;
                $allTotalWin = $minAllTotalWin;
                $allLineWins = $minAllLineWins;
                $allScatterWins = $minAllScatterWins;
                $msReplacement = $minMsReplacement;
                $featureIndex = $minFeatureIndex;
                $freespinsWon = $minFreespinsWon;
                $wildInfo = $minWildInfo;
                $newWildPos = $minNewWildPos;
            }

            $winSC = 0;
            $bonusAwarded = 'N';
            if($freespinsWon > 0)
            {
                $winSC = 1;
                $bonusAwarded = 'Y';
            }
            
            if($allTotalWin > 0)
            {
                $slotSettings->SetBank($postData['slotEvent'], -1 * $allTotalWin);
                $slotSettings->SetBalance($allTotalWin);
                $slotSettings->SetWin($allTotalWin);
            }

            $slotSettings->SetGameData($slotSettings->slotId . 'TotalGameWin', $slotSettings->GetGameData($slotSettings->slotId . 'TotalGameWin') + $allTotalWin);
            if($postData['slotEvent'] == 'freespin')
                $slotSettings->SetGameData($slotSettings->slotId . 'TotalFreespinWin', $slotSettings->GetGameData($slotSettings->slotId . 'TotalFreespinWin') + $allTotalWin);

            $totalWagerWin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalGameWin');
            $freeSpinWin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalFreespinWin');
            $baseGameWin = $totalWagerWin - $freeSpinWin;
            
            $isFreespin = $postData['slotEvent'] == 'freespin' ? 'Y':'N';            
            $sxe = new SimpleXMLElement('<GameResponse type="Logic"></GameResponse>');
            $header = simplexml_load_string('<Header sessionID="vkWv5HHtCBUMs3Gq1ogvjRBPthnKy49aqkvJ/CKj2EKCnk8ZnfY+aXVrGjaDSipAQKeZRMBeWY2+KgSXm60lcFLhX6a7iZfppr/nfhKgFY4=" ccyCode="GBP" deciSep="." thousandSep="," lang="en_US" gameID="20117" versionID="1_0" fullVersionID="1.0.1" isRecovering="N"/>');
            $accountData = simplexml_load_string('<AccountData><AccountData><CurrencyMultiplier>1</CurrencyMultiplier></AccountData></AccountData>');
            $balance = simplexml_load_string('<Balances><Balance name="CASH_BALANCE" value="'.($slotSettings->GetBalance() * 100).'"/></Balances>');            

            $gameResult = simplexml_load_string('<GameResult stake="'.(string)($allbet * 100).'" stakePerLine="7" paylineCount="30" totalWin="'.($allTotalWin * 100).'" betID="">                    
                    <BGInfo totalWagerWin="'.($totalWagerWin * 100).'" bgWinnings="'.($baseGameWin * 100).'" baseGameSpinsRemaining="0" isMaxWin="0"/>                                        
                </GameResult>');

            $numSpins = $gameCount;
            // if($postData['slotEvent'] == 'freespin')
            //     $numSpins = $featureIndex;
            $reelResults = simplexml_load_string('<ReelResults numSpins="'.$numSpins.'"></ReelResults>');

            for($g = 0; $g < $gameCount; $g++)
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
                if($freespinsWon > 0) //this will happen on gameCount = 1 only
                    $totalWin += $slotSettings->Paytable[$scatter][3] * $betLine;
                $reels = $allReels[$g];
                $reelData = implode(',', $reels['reel1']) . '|' .implode(',', $reels['reel2']) . '|' .implode(',', $reels['reel3']) . '|' .implode(',', $reels['reel4']) . '|' .implode(',', $reels['reel5']);
                $reelSpin = simplexml_load_string('<ReelSpin spinIndex="'.($g).'" reelsetIndex="'.$reelSetIndex.'" winCountPL="'.count($lineWins).'" winCountSC="'.$winSC.'" spinWins="'.($totalWin * 100).'" freeSpin="'.$isFreespin.'" bonusAwarded="'.$bonusAwarded.'" manualNoWin="'.$manualNoWin.'">
                            <ReelStops>'.implode('|',$reels['rp']).'</ReelStops>
                            <ReelInfo>'.$reelData.'</ReelInfo>
                            '.$paylines.$scatterWin.'
                        </ReelSpin>');
                $this->sxml_append($reelResults, $reelSpin);
            }
            $this->sxml_append($gameResult, $reelResults);

            if($msReplacement != '')
            {
                $ms = simplexml_load_string($msReplacement);
                $this->sxml_append($gameResult, $ms);
            }

            if($wildInfo != '')
            {
                $wi = simplexml_load_string($wildInfo);
                $this->sxml_append($gameResult, $wi);
            }

            if($postData['slotEvent'] == 'freespin' && $freespinInfo == '')
            {
                //set freespin info during freespin (not triggering freespin)
                $totalFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                $currentFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');                
                $freespinInfo = '<FSInfo featureIndex = "'.$featureIndex.'" fsWinnings="'.($freeSpinWin * 100).'" freeSpinsTotal="'.$totalFreespin.'" freeSpinNumber="'.$currentFreespin.'" isMaxWin="0"/>';
            }

            if($freespinInfo != '')
            {
                $fs = simplexml_load_string($freespinInfo);
                $this->sxml_append($gameResult, $fs);
            }          

            if($postData['slotEvent'] == 'bet')
            {
                if($freespinsWon > 0) //trigger freespin
                {
                    $bgRecoveryInfo = simplexml_load_string('<BaseGameRecoveryInfo></BaseGameRecoveryInfo>');
                    $this->sxml_append($bgRecoveryInfo, $reelResults);
                    $this->sxml_append($bgRecoveryInfo, $ms);
                    $bgRecoveryInfo = $bgRecoveryInfo->asXML();

                    $slotSettings->SetGameData($slotSettings->slotId . 'bgRecoveryInfo', $bgRecoveryInfo); 
                    $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', 'Freespin');                    
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $freespinsWon);
                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);  
                    $slotSettings->SetGameData($slotSettings->slotId . 'FeatureIndex', $featureIndex);  
                    $slotSettings->SetGameData($slotSettings->slotId . 'AllBet', $allbet);  
                }
            }
            else
            {
                $bgRecoveryInfo = $slotSettings->GetGameData($slotSettings->slotId . 'bgRecoveryInfo'); 
                $bi = simplexml_load_string($bgRecoveryInfo);
                $this->sxml_append($gameResult, $bi);
                if($featureIndex == 2)
                {
                    //save stick wild 
                    for($i = 0; $i < count($newWildPos); $i++)
                        $stickyWild[] = $newWildPos[$i];
                    $slotSettings->SetGameData($slotSettings->slotId . 'StickyWild', $stickyWild);
                }
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


