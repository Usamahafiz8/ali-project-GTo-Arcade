<?php 
namespace VanguardLTE\Games\GoldenChief
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
                                return '{"javascripts":["/games/GoldenChief/d1gpiy04es6c1f.cloudfront.net/5.0.0.14-1669806083/resource-service/mainjs/application/capabilitiesdetector.CapabilitiesDetectorBootstrapper.js?resourceversion=5.0.0.14-1669806083&appcode=capabilities-detector","/games/GoldenChief/d1gpiy04es6c1f.cloudfront.net/5.0.0.14-1669806083/resource-service/translationjs/bundles/translations/#{locale}/capabilitiesdetector.LocalizationBundle.js?resourceversion=5.0.0.14-1669806083&appcode=capabilities-detector"],"jsons":["/games/GoldenChief/d1gpiy04es6c1f.cloudfront.net/5.0.0.14-1669806083/resource-service/translationjson/bundles/translations/#{locale}/capabilitiesdetector.LocalizationBundle.json?resourceversion=5.0.0.14-1669806083&appcode=capabilities-detector"],"main_class":"capabilitiesdetector.CapabilitiesDetectorBootstrapper","supported_locales":["it_IT","ru_RU","el_GR","pl_PL","ro_RO","tr_TR","fr_FR","cs_CZ","hu_HU","ca_ES","de_DE","sk_SK","es_ES","nl_NL","fr_CA","sv_SE","da_DK","bg_BG","hr_HR","fi_FI","en_GB","et_EE","lt_LT","lv_LV","sl_SI","no_NO","pt_PT"],"default_locale":"en"}';
                            break;
                            case "gls-platform":
                                return '{"javascripts":["/games/GoldenChief/d1gpiy04es6c1f.cloudfront.net/5.0.0.14-1669806083/resource-service/mainjs/application/glsplatform.GlsPlatform.js?resourceversion=5.0.0.14-1669806083&appcode=gls-platform","/games/GoldenChief/acy-resource.wimobile.casinarena.com/resource-service/metadatajs/bundles/metadata/glsplatform.MetaDataBundle.js?resourceversion=5.0.0.14-1669806083&appcode=gls-platform&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=nyx_goldennugget&realmoney=false&gamecode=goldenchief&locale=en_US&webaudio=true","/games/GoldenChief/d1gpiy04es6c1f.cloudfront.net/5.0.0.14-1669806083/resource-service/translationjs/bundles/translations/#{locale}/glsplatform.LocalizationsBundle.js?resourceversion=5.0.0.14-1669806083&appcode=gls-platform"],"jsons":["/games/GoldenChief/d1gpiy04es6c1f.cloudfront.net/5.0.0.14-1669806083/resource-service/metadatajson/bundles/metadata/glsplatform.MetaDataBundle.json?resourceversion=5.0.0.14-1669806083&appcode=gls-platform&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=nyx_goldennugget&realmoney=false&gamecode=goldenchief&locale=en_US&webaudio=true","/games/GoldenChief/d1gpiy04es6c1f.cloudfront.net/5.0.0.14-1669806083/resource-service/translationjson/bundles/translations/#{locale}/glsplatform.LocalizationsBundle.json?resourceversion=5.0.0.14-1669806083&appcode=gls-platform"],"main_class":"glsplatform.GlsPlatform","supported_locales":["it_IT","ru_RU","el_GR","pl_PL","ro_RO","tr_TR","fr_FR","cs_CZ","hu_HU","ca_ES","de_DE","sk_SK","es_ES","nl_NL","fr_CA","sv_SE","da_DK","bg_BG","hr_HR","fi_FI","en_GB","et_EE","lt_LT","lv_LV","sl_SI","no_NO","pt_PT"],"default_locale":"en"}';
                                break;
                            case "lean-regular-partner-adapter":
                                return '{"javascripts":["/games/GoldenChief/d1gpiy04es6c1f.cloudfront.net/5.0.0.14-1669806083/resource-service/mainjs/application/leanpartneradapter.LeanPartnerAdapter.js?resourceversion=5.0.0.14-1669806083&appcode=lean-regular-partner-adapter","/games/GoldenChief/acy-resource.wimobile.casinarena.com/resource-service/metadatajs/bundles/metadata/leanpartneradapter.MetaDataBundle.js?resourceversion=5.0.0.14-1669806083&appcode=lean-regular-partner-adapter&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=nyx_goldennugget&realmoney=false&gamecode=goldenchief&locale=en_US&webaudio=true","/games/GoldenChief/d1gpiy04es6c1f.cloudfront.net/5.0.0.14-1669806083/resource-service/translationjs/bundles/translations/#{locale}/leanpartneradapter.LocalizationsBundle.js?resourceversion=5.0.0.14-1669806083&appcode=lean-regular-partner-adapter"],"jsons":["/games/GoldenChief/d1gpiy04es6c1f.cloudfront.net/5.0.0.14-1669806083/resource-service/metadatajson/bundles/metadata/leanpartneradapter.MetaDataBundle.json?resourceversion=5.0.0.14-1669806083&appcode=lean-regular-partner-adapter&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=nyx_goldennugget&realmoney=false&gamecode=goldenchief&locale=en_US&webaudio=true","/games/GoldenChief/d1gpiy04es6c1f.cloudfront.net/5.0.0.14-1669806083/resource-service/translationjson/bundles/translations/#{locale}/leanpartneradapter.LocalizationsBundle.json?resourceversion=5.0.0.14-1669806083&appcode=lean-regular-partner-adapter"],"main_class":"commonadapter.PortraitPartnerAdapter","supported_locales":["it_IT","ru_RU","el_GR","pl_PL","ro_RO","tr_TR","fr_FR","cs_CZ","hu_HU","ca_ES","de_DE","sk_SK","es_ES","nl_NL","fr_CA","sv_SE","da_DK","bg_BG","hr_HR","fi_FI","en_GB","et_EE","lt_LT","lv_LV","sl_SI","no_NO","pt_PT"],"default_locale":"en"}';
                                break;
                            case "goldenchief":
                                return '{
                                    "orientation": "PORTRAIT_MOBILE",
                                    "default_locale": "en",
                                    "inject": [
                                        {
                                            "script": {
                                                "data-main": "content/goldenchief/lib/require/require_cfg.js?resourceversion=5.0.0.14-1669806083&appcode=goldenchief&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=nyx_goldennugget&realmoney=false&gamecode=goldenchief&locale=en_US&webaudio=true",
                                                "src": "content/goldenchief/lib/require/require.js",
                                                "type": "text/javascript"
                                            }
                                        },
                                        {
                                            "link": {
                                                "rel": "stylesheet",
                                                "href": "content/goldenchief/resources/css/game.css"
                                            }
                                        }
                                    ],
                                    "javascripts": [
                                        "content/goldenchief/resources/js/goldenchief.game.js?resourceversion=5.0.0.14-1669806083&appcode=goldenchief&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=nyx_goldennugget&realmoney=false&gamecode=goldenchief&locale=en_US&webaudio=true",
                                        "content/goldenchief/resources/js/goldenchief.metadatabundle.js?resourceversion=5.0.0.14-1669806083&appcode=goldenchief&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=nyx_goldennugget&realmoney=false&gamecode=goldenchief&locale=en_US&webaudio=true"
                                    ],
                                    "publish_events": {"platform":"1"},
                                    "main_class": "goldenchief.Game",
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
                                }';
                                break;
                        }
                        
                    }
                }
                $postData = simplexml_load_string($request->getContent());                
                $reqType = (string)$postData['type'];
                
                switch( $reqType ) 
                {
                    case 'Init':         
                        $filename = base_path() . '/app/Games/GoldenChief/game.txt';
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
                        if(isset($postData->Gamble))
                        {
                            $collect = (int)((string)$postData->Gamble['collect']);
                            $response = $this->doGamble($slotSettings, $collect);
                            break;
                        }
                        $allbet = 0;
                        if(isset($postData->Stake))
                        {
                            $allbet = (int)((string)$postData->Stake['total']) * 0.01;
                            $slotSettings->SetGameData($slotSettings->slotId . 'AllBet', $allbet);
                        }
                        $postData['slotEvent'] = 'bet';                        
                        $spinStatus = $slotSettings->GetGameData($slotSettings->slotId . 'SpinStatus');
                        if($spinStatus == 'Freespin')
                        {
                            $postData['slotEvent'] = 'freespin';
                        }
                        else if($spinStatus == 'BigBet')
                        {
                            $postData['slotEvent'] = 'bigbet';
                        }
                        
                        if( $postData['slotEvent'] != 'freespin' && $postData['slotEvent'] != 'bigbet') 
                        {
                            $slotSettings->SetBalance(-1 * $allbet, $postData['slotEvent']);
                            $bankSum = $allbet / 100 * $slotSettings->GetPercent();
                            $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $bankSum, $postData['slotEvent']);
                            $slotSettings->UpdateJackpots($allbet);
                            $slotSettings->SetBet($allbet);
                            $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'TotalGameWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'PreviousFsCount', -1);
                            $slotSettings->SetGameData($slotSettings->slotId . 'StickyWild', []);
                        }
                        else
                        {
                            if($spinStatus == 'Freespin')
                                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);
                            else if($spinStatus == 'BigBet')
                                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentBigBetGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentBigBetGame') + 1);
                        }
                        
                        $response = $this->doSpin($slotSettings, $postData);                        
                        break;
                    case 'EndGame':
                        $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', '');
                        $slotSettings->SetGameData($slotSettings->slotId . 'TotalFreespinWin', '0');
                        $slotSettings->SetGameData($slotSettings->slotId . 'FeatureIndex', 0); 
                        $slotSettings->SetGameData($slotSettings->slotId . 'StickyWild', []);
                        $sxe = new SimpleXMLElement('<GameResponse type="EndGame"></GameResponse>');
                        $header = simplexml_load_string('<Header sessionID="T0RuuLGLoWQdxR86YwRCpFd/kZ6/qUZHDUsIxT/KRWUuILGsBZXA4GYIpqmFXONl" ccyCode="GBP" deciSep="." thousandSep="," lang="en_US" gameID="20125" versionID="1_0" fullVersionID="unknown" isRecovering="N"/>');
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
            $isBigBet = (int)((string)$postData->Stake['isBigBet']);
            if($isBigBet)            
            {
                $spinStatus = $slotSettings->GetGameData($slotSettings->slotId . 'SpinStatus');
                if($spinStatus == '')
                {
                    //this means triggering of bigbet
                    $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', 'BigBet');                    
                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalBigBetGame', 5);
                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentBigBetGame', 1);                    
                }                
            }
            
            $reelSetIndex = 0;
            $gameCount = 1;
            $allbet = (int)((string)$postData->Stake['total']) * 0.01;
            if($postData['slotEvent'] == 'freespin')
            $allbet = $slotSettings->GetGameData($slotSettings->slotId . 'AllBet');  
            $betLine = $allbet / 0.1 * 0.01; 
            $linesId = $slotSettings->GetPaylines($allbet);

            if($postData['slotEvent'] == 'freespin')
            {                
                $featureIndex = $slotSettings->GetGameData($slotSettings->slotId . 'FeatureIndex');
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bonus');
                $reelSetIndex = rand(5, 14);
            }
            else if($isBigBet)
            {
                $betLine = 0.01;
                if($allbet == 50)
                {
                    //50 big bet
                    $reelSetIndex = 4;
                }
                else if($allbet == 30)
                {
                    //30 big bet
                    $reelSetIndex = 3;
                }
                else if($allbet == 20)
                {
                    //20 big bet
                    $reelSetIndex = 2;
                }
            }
            else
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bet');                         
                $reelSetIndex = 0; 
            }
            $reelName = 'Reels'.$reelSetIndex;
            $lines = count($linesId);

            $betIndex = 0;
            if($allbet < 2)
                $betIndex = 1;
            if($isBigBet == 1)
                $betIndex = 2;
            $slotEvent = '';
            if($postData['slotEvent'] == 'freespin')
                $slotEvent = 'freespin';
            $winTypeTmp = $slotSettings->GetSpinSettings($slotEvent, $allbet);
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
            if($this->debug && $postData['slotEvent'] != 'freespin')
            {
                $winType = 'bonus';
                // $winType = 'win';
                $spinWinLimit = 100;
            }
            $spinAcquired = false;             

            $minAllReels = [];
            $minAllLineWins = [];
            $minAllTotalWin = -1;
            $minAllScatterWins = [];
            $minAllScatterCnt = [];
            $minStickyWild = [];
            $minFeatureIndex = 0;            
            $minFreespinsWon = 0;
            $minWildInfo = '';
            $minTotemWin = '';
            $minCanyonBonus = '';
            $minHeldWild = '';
            $minSymbolUpgrade = '';
            
            $totalWin = 0;
            $lineWins = [];
            $reels = [];
            
            $wild = [8, 9];
            $scatter = 10;
            $scatters = [10, 11, 12];
            $freespinInfo = '';

            $allLineWins = [];
            $allScatterWins = [];
            $allScatterCount = [];
            $allReels = [];
            $allTotalWin = 0;
            
            $totemWin = '';
            $featureIndex = 0;
            $freespinsWon = 0;
            $wildInfo = '';
            $heldWild = '';
            $symbolUpgrade = '';
            $bonusMpl = 1;
            $paytable = $slotSettings->Paytable[$betIndex];            
            $stickyWild = [];

            for( $i = 0; $i <= 500; $i++ ) 
            {
                $allTotalWin = 0;   
                $freespinInfo = '';
                $wildInfo = '';
                $heldWild = '';
                $symbolUpgrade = '';                
                $totemWin = '';
                $canyonBonus = '';
                $stickyWild = $slotSettings->GetGameData($slotSettings->slotId . 'StickyWild');

                if($postData['slotEvent'] == 'freespin')
                {
                    $featureIndex = $slotSettings->GetGameData($slotSettings->slotId . 'FeatureIndex');                    
                }

                for($g = 0; $g < $gameCount; $g++)
                {
                    $scatterWin = '';
                    $totalWin = 0;
                    $lineWins = [];
                    $cWins = array_fill(0, $lines, 0);                 
                    $reels = $slotSettings->GetReelStrips($winType, $reelName);

                    //check wild expansion for low possibility
                    if($isBigBet)
                    {
                        if(count($stickyWild) > 0 && $allbet > 20)
                            for($r = 0; $r < 5; $r++)
                            {
                                if(in_array($r, $stickyWild))
                                {
                                    for($c = 0; $c < 4; $c++)
                                        $reels['reel'.($r+1)][$c] = $wild[1];
                                }
                            }

                        if(rand(0, 100) < 20 && $allbet == 50)
                        {
                            $replaceSymbol = rand(1,7);
                            $replaces = [];
                            for($r = 0; $r < 5; $r++)
                                for($c = 0; $c < 4; $c++)
                                {
                                    if($reels['reel'.($r+1)][$c] < $replaceSymbol)
                                    {
                                        $replaces[] = $c * 5 + $r;
                                        $reels['reel'.($r+1)][$c] = $replaceSymbol;
                                    }
                                }
                            if(count($replaces) > 0)
                            {
                                $symbolUpgrade = '<SymbolUpgrade replacementSymbol="'.$replaceSymbol.'" positions="'.implode('|', $replaces).'" />';
                            }
                        }
                    }
                    
                    $originalWildPositions = [];
                    $wildReels = [];
                    for($r = 0; $r < 5; $r++)
                    {
                        for($c = 0; $c < 4; $c++)
                        {
                            if($reels['reel'.($r+1)][$c] == $wild[1])
                            {
                                $position = $c * 5 + $r;
                                if(!in_array($r, $stickyWild))
                                {
                                    $originalWildPositions[] = $position;
                                    if(!in_array($r, $wildReels))
                                        $wildReels[] = $r;       
                                }                                                                
                            }
                        }                   
                    }

                    foreach($wildReels as $wr)
                    {
                        for($c = 0; $c < 4; $c++)
                            $reels['reel'.($wr+1)][$c] = $wild[1];
                    }

                    if(count($wildReels) > 0)
                    {
                        $wildInfo = '<WildExpansion originalWildPositions="'.implode('|', $originalWildPositions).'" wildReels="'.implode('|', $wildReels).'" />';
                        if($isBigBet && $allbet > 20)
                        {
                            foreach($wildReels as $wr)
                            {
                                if(!in_array($wr, $stickyWild))
                                    $stickyWild[] = $wr;
                            }
                        }
                    }

                    if(count($stickyWild) > 0)
                    {
                        $heldWild = '<HeldWildPositions heldWilds="'.implode('|', $stickyWild).'" />';
                    }
                    
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
                                                                                    
                                if($featureIndex != 3 && ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) ) 
                                {
                                    $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
                                    $tmpWin = $paytable[$csym][3] * $betLine * $mpl * $bonusMpl;
                                    if( $cWins[$k] < $tmpWin ) 
                                    {
                                        $cWins[$k] = $tmpWin;
                                        $emptyLine[0][$p0] = 1;
                                        $emptyLine[1][$p1] = 1;
                                        $emptyLine[2][$p2] = 1;
                                        $winline = [$k, $tmpWin, $this->getConvertedLine($emptyLine), $slotSettings->awardIndices[$betIndex][$csym][3]]; //[lineId, coinWon, winPositions]
                                    }
                                }
    
                                if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) ) 
                                {
                                    $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
                                    $tmpWin = $paytable[$csym][4] * $betLine * $mpl * $bonusMpl;
                                    if( $cWins[$k] < $tmpWin ) 
                                    {
                                        $cWins[$k] = $tmpWin;
                                        $emptyLine[0][$p0] = 1;
                                        $emptyLine[1][$p1] = 1;
                                        $emptyLine[2][$p2] = 1;
                                        $emptyLine[3][$p3] = 1;
                                        $winline = [$k, $tmpWin, $this->getConvertedLine($emptyLine), $slotSettings->awardIndices[$betIndex][$csym][4]]; //[lineId, coinWon, winPositions]                                                             
                                    }
                                }
                                
                                if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) ) 
                                {
                                    $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
                                    $tmpWin = $paytable[$csym][5] * $betLine * $mpl * $bonusMpl;
                                    if( $cWins[$k] < $tmpWin )
                                    {
                                        $cWins[$k] = $tmpWin;
                                        $emptyLine[0][$p0] = 1;
                                        $emptyLine[1][$p1] = 1;
                                        $emptyLine[2][$p2] = 1;
                                        $emptyLine[3][$p3] = 1;
                                        $emptyLine[4][$p4] = 1;
                                        $winline = [$k, $tmpWin, $this->getConvertedLine($emptyLine), $slotSettings->awardIndices[$betIndex][$csym][5]]; //[lineId, coinWon,winPositions]                                                            
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
                    $scatterPos = [];
                    for($r = 1; $r <= 5; $r++)
                    {
                        for($c = 0; $c < 4; $c++)
                        {
                            if(in_array($reels['reel'.$r][$c], $scatters))
                            {
                                $scatterCount++;
                                $scatterPos[] = $c * 5 + $r - 1;
                                break;
                            }                            
                        }
                    }

                    if($winType != 'bonus' && $scatterCount > 2)
                        continue;

                    if($scatterCount > 2)
                    {
                        if($scatterCount > 5)
                            $scatterCount = 5;
                        $featureIndex = rand(1, 3);  //1: freespin, 2: totem, 3: cash canyon                        
                        $scatterWin = '<ScatterWin winVal="'.($paytable[$scatter][$scatterCount] * $betLine * 100).'" awardIndex="0">'.implode('|',$scatterPos).'</ScatterWin>';
                        $totalWin += $paytable[$scatter][$scatterCount] * $betLine;

                        if($featureIndex == 2) // totem
                        {
                            $totemWins = [[4,2,3], [5,6], [7,8], [9,10], [11,12], [13,14], [15,18], [20,25], [30,35], [40,50], [60,75], [100,150], [200,250], [300,400]];
                            $gameMode = 1;
                            $numLives = 0;
                            if($scatterCount > 3 || $isBigBet)
                            {
                                $totemWins = [[50,60,70], [80,90], [100,110], [120,130], [140,150], [160,180], [200,250], [300,350], [400,500], [600,650], [750,875], [1000,1500], [1750,2000], [2125,2250]];
                                $gameMode = 5;
                                $numLives = 1;
                            }
                            $pickIndex = rand(2, count($totemWins) - 1);
                            $winIndex = rand(0, 1);
                            $times = $totemWins[$pickIndex][$winIndex];
                            $win = $allbet * $times;
                            if($isBigBet)
                                $win = 0.2 * $times;
                            $totalWin += $win;
                            $steps = [];
                            $deadPosition = rand(2, $pickIndex - 1);
                            for($p = 0; $p < $pickIndex; $p++)
                            {
                                $steps[] = rand(0,1);
                                if($numLives > 0 && $p == $deadPosition)
                                {
                                    $steps[] = -2;
                                }
                            }
                            
                            $steps[] = $winIndex;
                            $steps[] = 2;                            
                            
                            $totemWin = '<TotemBonus gameMode="'.$gameMode.'" totemType="0" numLives="'.$numLives.'" winAmount="'.($win* 100).'" steps="'.implode('|', $steps).'" />';
                        }
                        else if($featureIndex == 3)
                        {
                            $cashCanyons = [0,1,2,3,4,5,6,7,8,10,12,15,20,25,30,35,40,60,75,100,125,150,200,350,500];
                            $curPos = 0;
                            $steps = [];
                            $condition = true;
                            $canyonId = 1;
                            if($isBigBet)
                            {
                                $winLimit = ($spinWinLimit - $totalWin) / 0.2;
                                $canyonId = 5;
                                $cashCanyons = [0,4,8,12,16,20,24,28,32,40,50,60,70,80,90,100,120,150,200,250,300,400,500,800,1000];
                            }
                            else
                                $winLimit = ($spinWinLimit - $totalWin) / $allbet;

                            while($condition)
                            {
                                $step_limit = 6;
                                if(count($cashCanyons) - $curPos < 6)
                                    $step_limit = count($cashCanyons) - $curPos;
                                $step = rand(1, $step_limit);
                                $curPos += $step;
                                $steps[] = $step;

                                $condition = ($curPos < count($cashCanyons) - 1) && $cashCanyons[$curPos] <= $winLimit;
                            }

                            array_pop($steps);
                            $position = 0;
                            for($p = 0; $p < count($steps); $p++ )
                            {
                                $position += $steps[$p];
                            }
                            $steps[] = -2;
                            $times = $cashCanyons[$position];
                            $win = $times * $allbet;
                            if($isBigBet)
                                $win = $times * 0.2;
                            $totalWin += $win;
                            $canyonBonus = '<CanyonBonus canyonID="'.$canyonId.'" winAmount="'.($win*100).'" steps="'.implode('|', $steps).'" />';
                        }
                    }
                        
                    $allScatterWins[$g] = $scatterWin;                    
                    $allLineWins[$g] = $lineWins;
                    $allTotalWin += $totalWin;
                    $allReels[$g] = $reels;
                    $allScatterCount[$g] = $scatterCount;
                }

                if( $minAllTotalWin == -1 || ($allTotalWin < $minAllTotalWin && $allTotalWin > 0))
                {
                    $minAllLineWins = $allLineWins;
                    $minAllTotalWin = $allTotalWin;
                    $minAllReels = $allReels;
                    $minAllScatterWins = $allScatterWins;
                    $minFeatureIndex = $featureIndex;
                    $minFreespinsWon = $freespinsWon;
                    $minWildInfo = $wildInfo;
                    $minAllScatterCnt = $allScatterCount;
                    $minTotemWin = $totemWin;
                    $minCanyonBonus = $canyonBonus;
                    $minHeldWild = $heldWild;
                    $minSymbolUpgrade = $symbolUpgrade;
                    $minStickyWild = $stickyWild;
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
                    break;
                }
            }            

            $manualNoWin = false;
            if(!$spinAcquired && ($allTotalWin > 0 || $scatterCount > 2))
            {                
                $manualNoWin = true;
                $allReels = $minAllReels;
                $allTotalWin = $minAllTotalWin;
                $allLineWins = $minAllLineWins;
                $allScatterWins = $minAllScatterWins;
                $featureIndex = $minFeatureIndex;
                $freespinsWon = $minFreespinsWon;
                $wildInfo = $minWildInfo;
                $allScatterCount = $minAllScatterCnt;
                $totemWin = $minTotemWin;
                $canyonBonus = $minCanyonBonus;
                $heldWild = $minHeldWild;
                $symbolUpgrade = $minSymbolUpgrade;
                $stickyWild = $minStickyWild;
            }

            $winSC = 0;
            $bonusAwarded = 'N';
            if($featureIndex > 0)
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
            $cheifWin = 0;
            $activePaylineCount = 20;
            
            $isFreespin = $postData['slotEvent'] == 'freespin' ? 'Y':'N';            
            $sxe = new SimpleXMLElement('<GameResponse type="Logic"></GameResponse>');
            $header = simplexml_load_string('<Header sessionID="vkWv5HHtCBUMs3Gq1ogvjRBPthnKy49aqkvJ/CKj2EKCnk8ZnfY+aXVrGjaDSipAQKeZRMBeWY2+KgSXm60lcFLhX6a7iZfppr/nfhKgFY4=" ccyCode="GBP" deciSep="." thousandSep="," lang="en_US" gameID="20125" versionID="1_0" fullVersionID="1.0.1" isRecovering="N"/>');
            $accountData = simplexml_load_string('<AccountData><AccountData><CurrencyMultiplier>1</CurrencyMultiplier></AccountData></AccountData>');
            $balance = simplexml_load_string('<Balances><Balance name="CASH_BALANCE" value="'.($slotSettings->GetBalance() * 100).'"/></Balances>');

            $baseGameSpinsRemaining = 0;
            if($isBigBet)
            {
                $totalBigBetGame = $slotSettings->GetGameData($slotSettings->slotId . 'TotalBigBetGame');
                $currentBigBetGame = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentBigBetGame');
                $baseGameSpinsRemaining = $totalBigBetGame - $currentBigBetGame;
            }
            $gameResultStr = '<GameResult stake="'.(string)($allbet * 100).'" stakePerLine="'.($allbet * 100 / 20).'" paylineCount="30" totalWin="'.($allTotalWin * 100).'" betID="">                    
                            <BGInfo totalWagerWin="'.($totalWagerWin * 100).'" bgWinnings="'.($baseGameWin * 100).'" baseGameSpinsRemaining="'.$baseGameSpinsRemaining.'" isMaxWin="0" isBigBet="'.$isBigBet.'" cheifWin="'.$cheifWin.'"/>
                            <PaylineCountInfo normalPaylineCount="20" bonusPaylineCount="100" activePaylineCount="'.$activePaylineCount.'"/>
                        </GameResult>';
            $gameResultStr = trim(preg_replace('/\s+/', ' ', $gameResultStr));
            $gameResultStr = str_replace('> <', '><', $gameResultStr);
            $gameResult = simplexml_load_string($gameResultStr);
            $numSpins = $gameCount;
            
            $reelResults = simplexml_load_string('<ReelResults numSpins="'.$numSpins.'"></ReelResults>');
            $bonusWheel = '';
            $gambleInfo = '';
            $scatterCnt = 0;
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
                $scatterCnt = $allScatterCount[$g];
                
                if($scatterCnt > 2) 
                {
                    $totalWin += $paytable[$scatter][$scatterCnt] * $betLine;                    
                }
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

            if($scatterCnt > 2)
            {
                if($featureIndex == 1) // freespin
                {
                    $position = 1;
                    $freespinCnt = 5;
                    $pickIndex = 1;
                    if($scatterCnt == 4)
                    {
                        $pickIndex = 2;
                        $freespinCnt = 10;
                    }
                    else if($scatterCnt == 5)
                    {
                        $pickIndex = 3;
                        $freespinCnt = 15;
                    }
                        
                    $slotSettings->SetGameData($slotSettings->slotId . 'PickIndex', $pickIndex);
                    $gambleInfo = '<GambleInfo previousFSCount="-1" currentFSCount="'.$freespinCnt.'" />';
                    $slotSettings->SetGameData($slotSettings->slotId . 'PreviousFsCount', $freespinCnt);
                }
                else if($featureIndex == 2) // totem
                {
                    $position = 6;                    
                }
                else if($featureIndex == 3)
                {
                    $position = 9;
                }

                $bonusWheel = '<BonusWheel stopPosition="'.$position.'"/>';                
            }

            if($bonusWheel != '')
            {
                if($bonusWheel != '')
                {
                    $bw = simplexml_load_string($bonusWheel);
                    $this->sxml_append($gameResult, $bw);
                }
                
                if($gambleInfo != '')
                {
                    $gi = simplexml_load_string($gambleInfo);
                    $this->sxml_append($gameResult, $gi);
                }

                if($totemWin != '')
                {
                    $tw = simplexml_load_string($totemWin);
                    $this->sxml_append($gameResult, $tw);
                }

                if($canyonBonus != '')
                {
                    $cb = simplexml_load_string($canyonBonus);
                    $this->sxml_append($gameResult, $cb);
                }
            }            

            if($wildInfo != '')
            {
                $wi = simplexml_load_string($wildInfo);
                $this->sxml_append($gameResult, $wi);
            }

            if($heldWild != '')
            {
                $hw = simplexml_load_string($heldWild);
                $this->sxml_append($gameResult, $hw);
            }
            $slotSettings->SetGameData($slotSettings->slotId . 'StickyWild', $stickyWild);

            if($symbolUpgrade != '')
            {
                $sw = simplexml_load_string($symbolUpgrade);
                $this->sxml_append($gameResult, $sw);
            }

            if($postData['slotEvent'] == 'freespin' && $freespinInfo == '')
            {
                //set freespin info during freespin (not triggering freespin)
                $totalFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                $currentFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');                
                $freespinInfo = '<FSInfo featureIndex = "'.$featureIndex.'" fsWinnings="'.($freeSpinWin * 100).'" freeSpinsTotal="'.$totalFreespin.'" freeSpinNumber="'.$currentFreespin.'" isMaxWin="0"/>';
            }

            if($postData['slotEvent'] == 'bet')
            {
                if($featureIndex == 1) //trigger freespin
                {
                    $bgRecoveryInfo = simplexml_load_string('<BaseGameRecoveryInfo></BaseGameRecoveryInfo>');
                    $this->sxml_append($bgRecoveryInfo, $reelResults);
                    $bgRecoveryInfo = $bgRecoveryInfo->asXML();

                    $slotSettings->SetGameData($slotSettings->slotId . 'bgRecoveryInfo', $bgRecoveryInfo); 
                    $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', 'Freespin');
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $freespinsWon);
                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);  
                    $slotSettings->SetGameData($slotSettings->slotId . 'FeatureIndex', $featureIndex);  
                    $slotSettings->SetGameData($slotSettings->slotId . 'AllBet', $allbet);  
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreespinWin', 0);                    
                }
            }
            else if($postData['slotEvent'] == 'freespin')
            {                
                $freespinsTotal = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                $currentFreeGame = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                if($freespinsTotal - $currentFreeGame == 0)
                {
                    if($baseGameSpinsRemaining > 0)
                        $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', 'BigBet');

                }
                $freespinInfo = '<FSInfo fsWinnings="'.($freeSpinWin * 100).'" freeSpinsTotal="'.$freespinsTotal.'" freeSpinNumber="'.$currentFreeGame.'" freespinsAwarded="'.$freespinsWon.'" isMaxWin="0" />';
                if($freespinInfo != '')
                {
                    $fs = simplexml_load_string($freespinInfo);
                    $this->sxml_append($gameResult, $fs);
                }
                $bgRecoveryInfo = $slotSettings->GetGameData($slotSettings->slotId . 'bgRecoveryInfo'); 
                $bi = simplexml_load_string($bgRecoveryInfo);
                $this->sxml_append($gameResult, $bi);
            }
            
            $this->sxml_append($sxe, $header);
            $this->sxml_append($sxe, $accountData);
            $this->sxml_append($sxe, $balance);
            $this->sxml_append($sxe, $gameResult);
            $response = $sxe->asXML();

            if($postData['slotEvent'] == 'freespin' || $postData['slotEvent'] == 'bigbet')
                $allbet = 0;
            $slotSettings->SaveLogReport($response, $allbet, $totalWin, $postData['slotEvent']);        
            return $response;
        }

        function doGamble($slotSettings, $collect)
        {
            $arr = [0,5,10,15,20,25,30];            
            $pickIndex = $slotSettings->GetGameData($slotSettings->slotId . 'PickIndex');            
            $spinArr = [$pickIndex - 1, $pickIndex];
            if($pickIndex < count($arr) - 1)
                $spinArr[] = $pickIndex + 1;
            
            if($pickIndex + 1)
            $pickIndex = $spinArr[rand(0,2)];            
            $slotSettings->SetGameData($slotSettings->slotId . 'PickIndex', $pickIndex);       
            $prevFsCount = $slotSettings->GetGameData($slotSettings->slotId . 'PreviousFsCount');
            if($collect == 0)
            {
                $newSpin = $arr[$pickIndex];
                
                $gambleFinished = 0;

                if($newSpin == 0)
                    $gambleFinished = 1;
            }
            else
            {
                $newSpin = $prevFsCount;
                $gambleFinished = 1;                
                $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', 'Freespin');

                $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $newSpin);
                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);  
            }
            
            $gambleInfo = '<GambleInfo previousFSCount="'.$prevFsCount.'" currentFSCount="'.$newSpin.'" gambleFinished="'.$gambleFinished.'"/>';

            $allbet = $slotSettings->GetGameData($slotSettings->slotId . 'AllBet');

            $sxe = new SimpleXMLElement('<GameResponse type="Logic"></GameResponse>');
            $header = simplexml_load_string('<Header sessionID="vkWv5HHtCBUMs3Gq1ogvjRBPthnKy49aqkvJ/CKj2EKCnk8ZnfY+aXVrGjaDSipAQKeZRMBeWY2+KgSXm60lcFLhX6a7iZfppr/nfhKgFY4=" ccyCode="GBP" deciSep="." thousandSep="," lang="en_US" gameID="20125" versionID="1_0" fullVersionID="1.0.1" isRecovering="N"/>');
            $accountData = simplexml_load_string('<AccountData><AccountData><CurrencyMultiplier>1</CurrencyMultiplier></AccountData></AccountData>');

            $this->sxml_append($sxe, $header);
            $this->sxml_append($sxe, $accountData);
            
            $gameResultStr = '<GameResult stake="'.(string)($allbet * 100).'" stakePerLine="'.($allbet * 100 / 20).'" paylineCount="0" totalWin="0" betID=""></GameResult>';
            $gameResultStr = trim(preg_replace('/\s+/', ' ', $gameResultStr));
            $gameResultStr = str_replace('> <', '><', $gameResultStr);
            $gameResult = simplexml_load_string($gameResultStr);

            $bgRecoveryInfo = $slotSettings->GetGameData($slotSettings->slotId . 'bgRecoveryInfo'); 
            $bi = simplexml_load_string($bgRecoveryInfo);
            $this->sxml_append($gameResult, $bi);
            $gi = simplexml_load_string($gambleInfo);
            $this->sxml_append($gameResult, $gi);

            $this->sxml_append($sxe, $gameResult);
            $response = $sxe->asXml();
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


