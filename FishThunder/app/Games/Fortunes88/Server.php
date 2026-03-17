<?php 
namespace VanguardLTE\Games\Fortunes88
{
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;
    use SimpleXMLElement;

    class Server
    {
        public $gameState;
        public $debug = false;
        function getConvertedLine($csym, $reel, $len)
        {
            $res = [];
            $wild = '0';
            if($len > 0)
            {
                for($r = 0; $r < $len; $r++)
                    for($c = 0; $c < 3; $c++)
                    {
                        if(($reel['reel'.($r+1)][$c] == $csym || $reel['reel'.($r+1)][$c] == $wild))
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
                                return '{"javascripts":["/games/Fortunes88/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/mainjs/application/capabilitiesdetector.CapabilitiesDetectorBootstrapper.js?resourceversion=5.0.0.11-1663077573&appcode=capabilities-detector","/games/Fortunes88/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/translationjs/bundles/translations/#{locale}/capabilitiesdetector.LocalizationBundle.js?resourceversion=5.0.0.11-1663077573&appcode=capabilities-detector"],"jsons":["/games/Fortunes88/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/translationjson/bundles/translations/#{locale}/capabilitiesdetector.LocalizationBundle.json?resourceversion=5.0.0.11-1663077573&appcode=capabilities-detector"],"main_class":"capabilitiesdetector.CapabilitiesDetectorBootstrapper","supported_locales":["it_IT","ru_RU","el_GR","pl_PL","ro_RO","tr_TR","fr_FR","cs_CZ","hu_HU","ca_ES","de_DE","sk_SK","es_ES","nl_NL","fr_CA","sv_SE","da_DK","bg_BG","hr_HR","fi_FI","en_GB","et_EE","lt_LT","lv_LV","sl_SI","no_NO","pt_PT"],"default_locale":"en"}';
                            break;
                            case "gls-platform":
                                return '{"javascripts":["/games/Fortunes88/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/mainjs/application/glsplatform.GlsPlatform.js?resourceversion=5.0.0.11-1663077573&appcode=gls-platform","/games/Fortunes88/acy-resource.wimobile.casinarena.com/resource-service/metadatajs/bundles/metadata/glsplatform.MetaDataBundle.js?resourceversion=5.0.0.11-1663077573&appcode=gls-platform&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=eightyeightfortunes&locale=en_US&webaudio=true","/games/Fortunes88/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/translationjs/bundles/translations/#{locale}/glsplatform.LocalizationsBundle.js?resourceversion=5.0.0.11-1663077573&appcode=gls-platform"],"jsons":["/games/Fortunes88/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/metadatajson/bundles/metadata/glsplatform.MetaDataBundle.json?resourceversion=5.0.0.11-1663077573&appcode=gls-platform&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=eightyeightfortunes&locale=en_US&webaudio=true","/games/Fortunes88/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/translationjson/bundles/translations/#{locale}/glsplatform.LocalizationsBundle.json?resourceversion=5.0.0.11-1663077573&appcode=gls-platform"],"main_class":"glsplatform.GlsPlatform","supported_locales":["sl_SI","pl_PL","lt_LT","bg_BG","ru_RU","ro_RO","el_GR","pt_PT","en_GB","sk_SK","cs_CZ","ca_ES","fr_CA","es_ES","lv_LV","hr_HR","it_IT","fi_FI","de_DE","hu_HU","fr_FR","tr_TR","sv_SE","da_DK","no_NO","et_EE","nl_NL"],"default_locale":"en"}';
                                break;
                            case "lean-regular-partner-adapter":
                                return '{"javascripts":["/games/Fortunes88/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/mainjs/application/leanpartneradapter.LeanPartnerAdapter.js?resourceversion=5.0.0.11-1663077573&appcode=lean-regular-partner-adapter","/games/Fortunes88/acy-resource.wimobile.casinarena.com/resource-service/metadatajs/bundles/metadata/leanpartneradapter.MetaDataBundle.js?resourceversion=5.0.0.11-1663077573&appcode=lean-regular-partner-adapter&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=eightyeightfortunes&locale=en_US&webaudio=true","/games/Fortunes88/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/translationjs/bundles/translations/#{locale}/leanpartneradapter.LocalizationsBundle.js?resourceversion=5.0.0.11-1663077573&appcode=lean-regular-partner-adapter"],"jsons":["/games/Fortunes88/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/metadatajson/bundles/metadata/leanpartneradapter.MetaDataBundle.json?resourceversion=5.0.0.11-1663077573&appcode=lean-regular-partner-adapter&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=eightyeightfortunes&locale=en_US&webaudio=true","/games/Fortunes88/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/translationjson/bundles/translations/#{locale}/leanpartneradapter.LocalizationsBundle.json?resourceversion=5.0.0.11-1663077573&appcode=lean-regular-partner-adapter"],"main_class":"commonadapter.LandscapePartnerAdapter","supported_locales":["it_IT","ru_RU","el_GR","pl_PL","ro_RO","tr_TR","fr_FR","cs_CZ","hu_HU","ca_ES","de_DE","sk_SK","es_ES","nl_NL","fr_CA","sv_SE","da_DK","bg_BG","hr_HR","fi_FI","en_GB","et_EE","lt_LT","lv_LV","sl_SI","no_NO","pt_PT"],"default_locale":"en"}';
                                break;
                            case "eightyeightfortunes":
                                return '{
                                    "default_locale": "en",
                                    "inject": [
                                        {
                                            "script": {
                                                "data-main": "content/eightyeightfortunes/lib/require/require_cfg.js?resourceversion=5.0.0.11-1663077573&appcode=eightyeightfortunes&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=eightyeightfortunes&locale=en_US&webaudio=true",
                                                "src": "content/eightyeightfortunes/lib/require/require.js",
                                                "type": "text/javascript"
                                            }
                                        },
                                        {
                                            "link": {
                                                "rel": "stylesheet",
                                                "href": "content/eightyeightfortunes/resources/css/help.css"
                                            }
                                        },
                                        {
                                            "link": {
                                                "rel": "stylesheet",
                                                "href": "content/eightyeightfortunes/resources/css/pays.css"
                                            }
                                        },
                                        {
                                            "link": {
                                                "rel": "stylesheet",
                                                "href": "content/eightyeightfortunes/resources/css/game.css"
                                            }
                                        }
                                    ],
                                    "javascripts": [
                                        "content/eightyeightfortunes/app/eightyeightfortunes.Game.js?resourceversion=5.0.0.11-1663077573&appcode=eightyeightfortunes&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=eightyeightfortunes&locale=en_US&webaudio=true",
                                        "content/eightyeightfortunes/bundles/metadata/eightyeightfortunes.MetaDataBundle.js?resourceversion=5.0.0.11-1663077573&appcode=eightyeightfortunes&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=eightyeightfortunes&locale=en_US&webaudio=true"
                                    ],
                                    "main_class": "eightyeightfortunes.Game",
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
                                    ],
                                    "publish_events": {
                                       "platform": "1"
                                    },
                                    "orientation": "LANDSCAPE"
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
                        $filename = base_path() . '/app/Games/Fortunes88/game.txt';
                        $file = fopen($filename, "r" );
                        $filesize = filesize( $filename );
                        $response = fread( $file, $filesize );
                        $response = str_replace('BAL_REPLACE', $slotSettings->GetBalance() * 100, $response);
                        fclose( $file );               
                        
                        $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', '');                        
                        $slotSettings->SetGameData($slotSettings->slotId . 'TotalFreespinWin', '0');

                        break;                        
                    case 'Logic':                        
                        $allbet = (int)$postData->SpinInfo['creditBet'] * (int)$postData->SpinInfo['betMultiplier'] * 0.01;
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
                        $header = simplexml_load_string('<Header sessionID="T0RuuLGLoWQdxR86YwRCpFd/kZ6/qUZHDUsIxT/KRWUuILGsBZXA4GYIpqmFXONl" ccyCode="GBP" deciSep="." thousandSep="," lang="en_US" gameID="20089" versionID="1_0" fullVersionID="unknown" isRecovering="N"/>');
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
            $reelSetIndex = 0;
            $creditBet = (int)$postData->SpinInfo['creditBet'];
            $betMultiplier = (int)$postData->SpinInfo['betMultiplier'];
            $postData->Stake['total'] = $creditBet * $betMultiplier;
            $allbet = $creditBet * $betMultiplier * 0.01;
            
            if($postData['slotEvent'] == 'freespin')
            {                
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bonus');
                $reelSetIndex = 5;
                if($creditBet == 18)
                    $reelSetIndex = 6;
                else if($creditBet == 38)
                    $reelSetIndex = 7;
                else if($creditBet == 68)
                    $reelSetIndex = 8;
                else if($creditBet == 88)
                    $reelSetIndex = 9;
            }
            else
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bet');
                $reelSetIndex = 0;
                if($creditBet == 18)
                    $reelSetIndex = 1;
                else if($creditBet == 38)
                    $reelSetIndex = 2;
                else if($creditBet == 68)
                    $reelSetIndex = 3;
                else if($creditBet == 88)
                    $reelSetIndex = 4;
            }
            $reelName = 'Reels'.$reelSetIndex;
            $betLine = 0.01 * $betMultiplier;

            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $allbet);
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];           
            
            $spinAcquired = false;             
            $gameWin = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin');

            $minReels = [];
            $minLineWins = [];
            $minTotalWin = -1;
            $minFreespinsWon = 0;
            $minFeature = '';
            $minJackpotCnt = 0;

            $totalWin = 0;
            $freespinsWon = 0;
            $lineWins = [];
            $reels = [];
            
            $scatter = '1';
            $jackpotSym = '0';
            $freespinInfo = '';
            $scatterWin = '';            
            $scatterCount = 0;
            $feature = '';

            for( $i = 0; $i <= 500; $i++ ) 
            {
                $scatterWin = '';
                $freespinInfo = '';
                $totalWin = 0;
                $freespinsWon = 0;
                $lineWins = [];
                $cWins = array_fill(0, count($slotSettings->SymbolGame), 0);
                
                if($this->debug && $postData['slotEvent'] != 'freespin')
                {                 
                    $winType = 'bonus';
                }

                $reels = $slotSettings->GetReelStrips($winType, $reelName, $postData['slotEvent']);
                $reels0 = $reels;
                $bonusMpl = 1;                
                
                for( $j = 0; $j < count($slotSettings->SymbolGame); $j++ ) 
                {
                    $mpl = 1;
                    $csym = $slotSettings->SymbolGame[$j];       
                    if($csym == $scatter)             
                        continue;
                    $mul1 = $slotSettings->getMultiplier($reels['reel1'], $csym);
                    $mul2 = $slotSettings->getMultiplier($reels['reel2'], $csym);
                    $mul3 = $slotSettings->getMultiplier($reels['reel3'], $csym);
                    $mul4 = $slotSettings->getMultiplier($reels['reel4'], $csym);
                    $mul5 = $slotSettings->getMultiplier($reels['reel5'], $csym);

                    if($mul1 > 0 && $mul2 > 0 && $mul3 > 0) //from left to right 3 symbols contained
                    {
                        $mpl = $mul1 * $mul2 * $mul3;
                        $tmpWin = $slotSettings->Paytable[$csym][3] * $betLine * $mpl * $bonusMpl;
                        if($tmpWin > $cWins[$csym])
                        {
                            $cWins[$csym] = $tmpWin;
                            $winline = [$j + 1, $tmpWin, $this->getConvertedLine($csym, $reels, 3), $mpl, $slotSettings->awardIndices[$csym][3]];
                        }
                    }
                    if($mul1 > 0 && $mul2 > 0 && $mul3 > 0 && $mul4 > 0) //from left to right 4 symbols contained
                    {
                        $mpl = $mul1 * $mul2 * $mul3 * $mul4;
                        $tmpWin = $slotSettings->Paytable[$csym][4] * $betLine * $mpl * $bonusMpl;
                        if($tmpWin > $cWins[$csym])
                        {
                            $cWins[$csym] = $tmpWin;
                            $winline = [$j + 1, $tmpWin, $this->getConvertedLine($csym, $reels, 4), $mpl, $slotSettings->awardIndices[$csym][4]];
                        }
                    }
                    if($mul1 > 0 && $mul2 > 0 && $mul3 > 0 && $mul4 > 0 && $mul5 > 0) //from left to right 5 symbols contained
                    {
                        $mpl = $mul1 * $mul2 * $mul3 * $mul4 * $mul5;
                        $tmpWin = $slotSettings->Paytable[$csym][5] * $betLine * $mpl * $bonusMpl;
                        if($tmpWin > $cWins[$csym])
                        {
                            $cWins[$csym] = $tmpWin;
                            $winline = [$j + 1, $tmpWin, $this->getConvertedLine($csym, $reels, 5), $mpl, $slotSettings->awardIndices[$csym][5]];
                        }
                    }
                    
                    if($cWins[$csym] > 0 && !empty($winline))
                    {
                        array_push($lineWins, $winline);
                        $totalWin += $cWins[$csym];
                    }
                }
                
                $scatterCount = 0;
                $s1 = $slotSettings->getMultiplier($reels['reel1'], $scatter);
                $s2 = $slotSettings->getMultiplier($reels['reel2'], $scatter);
                $s3 = $slotSettings->getMultiplier($reels['reel3'], $scatter);
                $s4 = $slotSettings->getMultiplier($reels['reel4'], $scatter);
                $s5 = $slotSettings->getMultiplier($reels['reel5'], $scatter);
                if($s1 > 0 && $s2 && $s3 > 0)
                    $scatterCount = 3;
                if($s1 > 0 && $s2 && $s3 > 0 && $s4 > 0)
                    $scatterCount = 4;
                if($s1 > 0 && $s2 && $s3 > 0 && $s4 > 0 && $s5 > 0)
                    $scatterCount = 5;
                if($scatterCount > 2 && $winType != 'bonus')
                    continue;

                $scattersWin = 0;
                if($scatterCount > 2)
                {
                    if($winType != 'bonus')
                        continue;
                    $scattersWin = $slotSettings->Paytable[$scatter][$scatterCount] * $creditBet / 8 * $betLine;
                }

                $totalWin += $scattersWin;
                //calc jackpot symbol
                $jackpotCnt = 0;                
                $jackpotPos = [];
                
                for($r = 0; $r < 5; $r++)
                    for($c = 0; $c < 3; $c++)
                    {
                        if($reels['reel'.($r+1)][$c] == $jackpotSym)
                        {
                            $jackpotCnt++;
                            $jackpotPos[] = $c * 5 + $r;
                        }
                    }
               
                if($jackpotCnt > 0)
                {
                    $scatterWin = '<ScatterWin winIndex="0" winVal="0" awardIndex="'.$slotSettings->awardIndices[$jackpotSym][$jackpotCnt].'">'.implode('|', $jackpotPos).'</ScatterWin>';
                    if($scatterCount < 3 && $winType == 'win')
                        if($creditBet > 8)
                        {
                            $miniJackpotWin = 2 * $betMultiplier;
                            $minorJackpotWin = 3.75 * $betMultiplier;
                            $jackpotIndex = -1;
                            $jackpotWin = 0;
                            if(rand(0, 100) < 20 && $creditBet > 18)
                            {
                                if($totalWin + $minorJackpotWin < $spinWinLimit)
                                {
                                    $jackpotWin = $minorJackpotWin;
                                    $jackpotIndex = 1;
                                }
                            }
                            else
                            {
                                if($totalWin + $miniJackpotWin < $spinWinLimit)
                                {
                                    $jackpotWin = $miniJackpotWin;                                
                                    $jackpotIndex = 0;
                                }
                            }
                            if($jackpotIndex != -1)
                            {
                                $totalWin += $jackpotWin;
                                $jackpotIndices = [0, 1, 2, 3];
                                $picks = [];
                                $picks[] = $jackpotIndex;
                                $picks[] = $jackpotIndex;
                                foreach($jackpotIndices as $index)
                                {
                                    if(!in_array($index, $picks))
                                    {
                                        $cnt = rand(0, 2);
                                        for($j = 0; $j < $cnt; $j++)
                                            $picks[] = $index;
                                    }
                                }                                                        
                                shuffle($picks);
                                $picks[] = $jackpotIndex;
                                $feature = '<Feature index="2" name="BG_FuBat_Jackpot"><data pickLength="'.count($picks).'" jackpotWin="'.($jackpotWin * 100).'" jackpotType="'.$jackpotIndex.'">'.implode('|', $picks).'</data></Feature>';
                            }
                        }
                }

                if($minTotalWin == -1 || ($minTotalWin > $totalWin && $totalWin > 0))
                {
                    $minTotalWin = $totalWin;
                    $minLineWins = $lineWins;
                    $minFreespinsWon = $freespinsWon;
                    $minReels = $reels;
                    $minFeature = $feature;
                    $minJackpotCnt = $jackpotCnt;
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
                    $spinAcquired = true;
                    break;
                }
            }

            $manualNoWin = false;
            if(!$spinAcquired )
            {                
                $manualNoWin = true;
                $reels = $minReels;
                $lineWins = $minLineWins;
                $totalWin = $minTotalWin;
                $freespinsWon = $minFreespinsWon;                    
                $scatterWin = '';
                $feature = $minFeature;
                $jackpotCnt = $minJackpotCnt;
            }

            $coinWin = 0; //coins won
            $paylines = '';
            if(!empty($lineWins))
            {
                $index = 0;
                foreach($lineWins as $winline)
                {
                    $coinWin += $winline[1]; //sum up coins
                    $paylines .= '<AnywayWin winIndex="'.$index.'" winVal="'.($winline[1] * 100).'" ways="'.$winline[3].'" awardIndex="'.$winline[4].'">'.$winline[2].'</AnywayWin>';
                    $index ++;
                }
            }

            $winSC = $jackpotCnt;
            
            if($totalWin > 0)
            {
                $slotSettings->SetBank($postData['slotEvent'], -1 * $totalWin);
                $slotSettings->SetBalance($totalWin);
                $slotSettings->SetWin($totalWin);
            }

            $slotSettings->SetGameData($slotSettings->slotId . 'TotalGameWin', $slotSettings->GetGameData($slotSettings->slotId . 'TotalGameWin') + $totalWin);
            if($postData['slotEvent'] == 'freespin')
                $slotSettings->SetGameData($slotSettings->slotId . 'TotalFreespinWin', $slotSettings->GetGameData($slotSettings->slotId . 'TotalFreespinWin') + $totalWin);

            if($scatterCount > 2)
            {
                $freespinsWon = 10;
                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                
                if($postData['slotEvent'] != 'freespin')
                {
                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalFreespinWin', $slotSettings->GetGameData($slotSettings->slotId . 'TotalFreespinWin') + $totalWin);
                    $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', 'Freespin');
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $freespinsWon);
                    $feature = '<Feature index="1" name="FreeGame"><data totalFreeSpinsWin="'.($totalWin * 100).'" remainingFreeSpins="'.$freespinsWon.'" extraFreeSpinsAwarded="0" freeSpinTriggerWin="'.($totalWin * 100).'" lastFreeSpin="N" /></Feature>';
                  
                    $bgRecoveryInfo = '<BaseGameRecoveryInfo><GameResult stake="'.$creditBet * $betMultiplier.'" creditBet="'.$creditBet.'" betMultiplier="'.$betMultiplier.'" waysCount="243" totalWin="'.($totalWin * 100).'" betID=""><ReelResults numSpins="1">
                            <ReelSpin spinIndex="0" reelsetIndex="0" winCountPL="'.count($lineWins).'" winCountSC="'.$winSC.'" spinWins="'.($totalWin * 100).'" freeSpin="N" bonusAwarded="N" manualNoWin="'.$manualNoWin.'">
                                <ReelStops>'.implode('|',$reels['rp']).'</ReelStops>
                                '.$paylines.$scatterWin.'
                            </ReelSpin>
                        </ReelResults></GameResult></BaseGameRecoveryInfo>';
                    $bgRecoveryInfo = trim(preg_replace('/\s+/', ' ', $bgRecoveryInfo));
                    $bgRecoveryInfo = str_replace('> <', '><', $bgRecoveryInfo);
                    $slotSettings->SetGameData($slotSettings->slotId . 'bgRecoveryInfo', $bgRecoveryInfo); 
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
            $header = simplexml_load_string('<Header sessionID="yJfeC7tY+Tb0ft3INwSHqrpjrQJyMqScxIjNRQXPjvy7VdCpaLlft5VEFrB67aGxFluFacUmZejYWig/YJQfk++lR8iszDPfBUlQ5bFIWow=" ccyCode="en_US" deciSep="." thousandSep="," lang="en_US" gameID="20077" versionID="1_0" fullVersionID="1.0.19" isRecovering="N"/>');
            $accountData = simplexml_load_string('<AccountData><AccountData><CurrencyMultiplier>1</CurrencyMultiplier></AccountData></AccountData>');
            $balance = simplexml_load_string('<Balances><Balance name="CASH_BALANCE" value="'.($slotSettings->GetBalance() * 100).'"/></Balances>');
            $reelData = json_encode($reels);
            $gameResultStr = '<GameResult stake="'.$creditBet * $betMultiplier.'" creditBet="'.$creditBet.'" betMultiplier="'.$betMultiplier.'" waysCount="243" totalWin="'.($totalWin * 100).'" betID="">
                    <ReelResults numSpins="1">
                        <ReelSpin reelsetIndex="'.$reelSetIndex.'" anywayWinCount="'.count($lineWins).'" scatterWinCount="'.$winSC.'" totalWayWin="'.($totalWagerWin * 100).'" totalScatterWin="0" totalSpinWin="'.($totalWin * 100).'" freeSpin="'.$isFreespin.'" bonusAwarded="'.$bonusAwarded.'" manualNoWin="'.$manualNoWin.'">
                            <ReelStops>'.implode('|',$reels['rp']).'</ReelStops>
                            '.$paylines.$scatterWin.'
                        </ReelSpin>
                    </ReelResults>
                    <GameWinInfo totalWagerWin="'.($totalWagerWin * 100).'" totalBaseGameWin="'.($baseGameWin * 100).'" totalFreeSpinsWin="'.($freeSpinWin * 100).'" maxWinValue="25000000" isMaxWin="N"/>
                    <GameRtpInfo targetedRtpValue="96.00"/>
                </GameResult>';
            $gameResultStr = trim(preg_replace('/\s+/', ' ', $gameResultStr));
            $gameResultStr = str_replace('> <', '><', $gameResultStr);
            $gameResult = simplexml_load_string($gameResultStr);

            if($postData['slotEvent'] == 'freespin')
            {
                //set freespin info during freespin (not triggering freespin)
                $totalFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                $currentFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');                
                $remainingFreespin = $totalFreespin - $currentFreespin;
                $bgRecoveryInfo = $slotSettings->GetGameData($slotSettings->slotId . 'bgRecoveryInfo'); 
                $bi = simplexml_load_string($bgRecoveryInfo);
                $this->sxml_append($gameResult, $bi);
                $lastFreespin = 'N';
                if($remainingFreespin == 0)
                    $lastFreespin = 'Y';
                $feature = '<Feature index="1" name="FreeGame"><data totalFreeSpinsWin="'.($slotSettings->GetGameData($slotSettings->slotId . 'TotalFreespinWin') * 100).'" remainingFreeSpins="'.$remainingFreespin.'" extraFreeSpinsAwarded="'.$freespinsWon.'" freeSpinTriggerWin="0" lastFreeSpin="'.$lastFreespin.'" /></Feature>';
            }

            if($feature != '')
            {
                $ft = simplexml_load_string($feature);
                $this->sxml_append($gameResult, $ft);
            }

            if($freespinInfo != '')
            {
                $fs = simplexml_load_string($freespinInfo);
                $this->sxml_append($gameResult, $fs);
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
    }

}


