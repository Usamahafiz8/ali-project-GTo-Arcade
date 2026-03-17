<?php 
namespace VanguardLTE\Games\DeepSeaMagic
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
                                return '{"javascripts":["/games/DeepSeaMagic/d1gpiy04es6c1f.cloudfront.net/5.0.0.14-1669806083/resource-service/mainjs/application/capabilitiesdetector.CapabilitiesDetectorBootstrapper.js?resourceversion=5.0.0.14-1669806083&appcode=capabilities-detector","/games/DeepSeaMagic/d1gpiy04es6c1f.cloudfront.net/5.0.0.14-1669806083/resource-service/translationjs/bundles/translations/#{locale}/capabilitiesdetector.LocalizationBundle.js?resourceversion=5.0.0.14-1669806083&appcode=capabilities-detector"],"jsons":["/games/DeepSeaMagic/d1gpiy04es6c1f.cloudfront.net/5.0.0.14-1669806083/resource-service/translationjson/bundles/translations/#{locale}/capabilitiesdetector.LocalizationBundle.json?resourceversion=5.0.0.14-1669806083&appcode=capabilities-detector"],"main_class":"capabilitiesdetector.CapabilitiesDetectorBootstrapper","supported_locales":["it_IT","ru_RU","el_GR","pl_PL","ro_RO","tr_TR","fr_FR","cs_CZ","hu_HU","ca_ES","de_DE","sk_SK","es_ES","nl_NL","fr_CA","sv_SE","da_DK","bg_BG","hr_HR","fi_FI","en_GB","et_EE","lt_LT","lv_LV","sl_SI","no_NO","pt_PT"],"default_locale":"en"}';
                            break;
                            case "gls-platform":
                                return '{"javascripts":["/games/DeepSeaMagic/d1gpiy04es6c1f.cloudfront.net/5.0.0.14-1669806083/resource-service/mainjs/application/glsplatform.GlsPlatform.js?resourceversion=5.0.0.14-1669806083&appcode=gls-platform","/games/DeepSeaMagic/acy-resource.wimobile.casinarena.com/resource-service/metadatajs/bundles/metadata/glsplatform.MetaDataBundle.js?resourceversion=5.0.0.14-1669806083&appcode=gls-platform&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=true&partnercode=mockpartner&realmoney=false&gamecode=dropandlockdeepseamagic&locale=en_US&webaudio=true","/games/DeepSeaMagic/d1gpiy04es6c1f.cloudfront.net/5.0.0.14-1669806083/resource-service/translationjs/bundles/translations/#{locale}/glsplatform.LocalizationsBundle.js?resourceversion=5.0.0.14-1669806083&appcode=gls-platform"],"jsons":["/games/DeepSeaMagic/d1gpiy04es6c1f.cloudfront.net/5.0.0.14-1669806083/resource-service/metadatajson/bundles/metadata/glsplatform.MetaDataBundle.json?resourceversion=5.0.0.14-1669806083&appcode=gls-platform&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=true&partnercode=mockpartner&realmoney=false&gamecode=dropandlockdeepseamagic&locale=en_US&webaudio=true","/games/DeepSeaMagic/d1gpiy04es6c1f.cloudfront.net/5.0.0.14-1669806083/resource-service/translationjson/bundles/translations/#{locale}/glsplatform.LocalizationsBundle.json?resourceversion=5.0.0.14-1669806083&appcode=gls-platform"],"main_class":"glsplatform.GlsPlatform","supported_locales":["it_IT","ru_RU","el_GR","pl_PL","ro_RO","tr_TR","fr_FR","cs_CZ","hu_HU","ca_ES","de_DE","sk_SK","es_ES","nl_NL","fr_CA","sv_SE","da_DK","bg_BG","hr_HR","fi_FI","en_GB","et_EE","lt_LT","lv_LV","sl_SI","no_NO","pt_PT"],"default_locale":"en"}';
                                break;
                            case "lean-regular-partner-adapter":
                                return '{"javascripts":["/games/DeepSeaMagic/d1gpiy04es6c1f.cloudfront.net/5.0.0.14-1669806083/resource-service/mainjs/application/leanpartneradapter.LeanPartnerAdapter.js?resourceversion=5.0.0.14-1669806083&appcode=lean-regular-partner-adapter","/games/DeepSeaMagic/acy-resource.wimobile.casinarena.com/resource-service/metadatajs/bundles/metadata/leanpartneradapter.MetaDataBundle.js?resourceversion=5.0.0.14-1669806083&appcode=lean-regular-partner-adapter&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=true&partnercode=mockpartner&realmoney=false&gamecode=dropandlockdeepseamagic&locale=en_US&webaudio=true","/games/DeepSeaMagic/d1gpiy04es6c1f.cloudfront.net/5.0.0.14-1669806083/resource-service/translationjs/bundles/translations/#{locale}/leanpartneradapter.LocalizationsBundle.js?resourceversion=5.0.0.14-1669806083&appcode=lean-regular-partner-adapter"],"jsons":["/games/DeepSeaMagic/d1gpiy04es6c1f.cloudfront.net/5.0.0.14-1669806083/resource-service/metadatajson/bundles/metadata/leanpartneradapter.MetaDataBundle.json?resourceversion=5.0.0.14-1669806083&appcode=lean-regular-partner-adapter&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=true&partnercode=mockpartner&realmoney=false&gamecode=dropandlockdeepseamagic&locale=en_US&webaudio=true","/games/DeepSeaMagic/d1gpiy04es6c1f.cloudfront.net/5.0.0.14-1669806083/resource-service/translationjson/bundles/translations/#{locale}/leanpartneradapter.LocalizationsBundle.json?resourceversion=5.0.0.14-1669806083&appcode=lean-regular-partner-adapter"],"main_class":"commonadapter.TopboxPartnerAdapter","supported_locales":["it_IT","ru_RU","el_GR","pl_PL","ro_RO","tr_TR","fr_FR","cs_CZ","hu_HU","ca_ES","de_DE","sk_SK","es_ES","nl_NL","fr_CA","sv_SE","da_DK","bg_BG","hr_HR","fi_FI","en_GB","et_EE","lt_LT","lv_LV","sl_SI","no_NO","pt_PT"],"default_locale":"en"}';
                                break;
                            case "dropandlockdeepseamagic":
                                return '{
                                    "orientation": "BOTH",
                                    "default_locale": "en",
                                    "inject": [
                                        {
                                            "link": {
                                                "rel": "stylesheet",
                                                "href": "content/dropandlockdeepseamagic/css/app.css"
                                            }
                                        }
                                    ],
                                    "javascripts": [
                                        "content/dropandlockdeepseamagic/js/metadatabundle.js?resourceversion=5.0.0.14-1669806083&appcode=dropandlockdeepseamagic&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=true&partnercode=mockpartner&realmoney=false&gamecode=dropandlockdeepseamagic&locale=en_US&webaudio=true"
                                    ],
                                    "publish_events": {
                                        "platform": "1"
                                    },
                                    "main_class": "SG.EntryPoint",
                                    "supported_locales": [
                                        "en"
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
                        $filename = base_path() . '/app/Games/DeepSeaMagic/game.txt';
                        $file = fopen($filename, "r" );
                        $filesize = filesize( $filename );
                        $response = fread( $file, $filesize );
                        $response = str_replace('BAL_REPLACE', $slotSettings->GetBalance() * 100, $response);
                        fclose( $file );               
                        
                        $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', '');                        
                        $slotSettings->SetGameData($slotSettings->slotId . 'TotalFreespinWin', '0');
                        $slotSettings->SetGameData($slotSettings->slotId . 'TotalDropLockWin', '0');

                        break;                        
                    case 'Logic':                        
                        $allbet = $postData->Stake['total'] * 0.01;
                        $postData['slotEvent'] = 'bet';                        
                        $spinStatus = $slotSettings->GetGameData($slotSettings->slotId . 'SpinStatus');
                        if($spinStatus == 'Freespin')
                        {
                            $postData['slotEvent'] = 'freespin';
                        }
                        if($spinStatus == 'DropLock')
                        {
                            $postData['slotEvent'] = 'droplock';
                        }
                        
                        if( $postData['slotEvent'] == 'bet' ) 
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
                        else if( $postData['slotEvent'] == 'freespin' ) 
                        {
                            $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);
                        }
                        else if( $postData['slotEvent'] == 'droplock' ) 
                        {
                            $slotSettings->SetGameData($slotSettings->slotId . 'CurrentDropLock', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentDropLock') + 1);
                        }
                        $response = $this->doSpin($slotSettings, $postData);
                        
                        break;
                    case 'EndGame':
                        $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', '');
                        $slotSettings->SetGameData($slotSettings->slotId . 'SpinFeature', '-1');
                        $slotSettings->SetGameData($slotSettings->slotId . 'TotalFreespinWin', '0');
                        $slotSettings->SetGameData($slotSettings->slotId . 'TotalDropLockWin', '0');
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
            $allbet = $postData->Stake['total'] * 0.01;
            if($postData['slotEvent'] == 'freespin')
                $reelSetIndex = 1;
            
            $reelName = 'Reels'.$reelSetIndex;

            $linesId = $slotSettings->GetPaylines();
            $lines = count($linesId);    
            $betLine = $allbet / 0.25 * 0.01;

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
            $minDlspinWin = 0;
            $minFeature = '';
            $minBonusSyms = [];
            $minMatrix = [];

            $totalWin = 0;
            $freespinsWon = 0;
            $lineWins = [];
            $reels = [];
            
            $scatter = 10;
            $freespinInfo = '';
            $scatterWin = '';
            $bonusSyms = [];
            $scatterCount = 0;
            $dlSpinWin = 0;
            $feature = '';
            $wild = [0];
            $sphere = 11;
            $availableCash = [0.4,0.4,0.4,0.4,0.4,0.4,0.4,0.4,0.4, 0.6,0.6,0.6,0.6,0.6,0.6,0.6, 1,1,1,1,1,1,1, 2,2,2,2,2,2, 3,3,3,3,3, 5,5,5,5, 10,10,10, 25];
            $paytable = $slotSettings->Paytable;
            $matrix = []; //droplock matrix
            for( $i = 0; $i <= 500; $i++ ) 
            {
                $scatterWin = '';
                $freespinInfo = '';
                $bonusSyms = [];
                $totalWin = 0;
                $freespinsWon = 0;
                $scatterCount = 0;
                $dlSpinWin = 0;
                $scatterPos = [];
                $lineWins = [];
                $matrix = [];
                $cWins = array_fill(0, $lines, 0);

                $reels = $slotSettings->GetReelStrips($winType, $reelName, $postData['slotEvent']);

                if($postData['slotEvent'] == 'droplock')
                {
                    //restore last sphere values
                    $lastDropLock = $slotSettings->GetGameData($slotSettings->slotId . 'DropLockSyms');
                    for($r = 0; $r < 5; $r++)
                        for($c = 0; $c < 3; $c++)
                        {
                            if($lastDropLock[$r][$c] != -1)
                                $reels['reel'.($r+1)][$c] = 11;
                        }

                    //determine sphere values
                    for($c = 0; $c < 3; $c++)
                    for($r = 0; $r < 5; $r++)
                    {
                        if($reels['reel'.($r+1)][$c] == $sphere)
                        {
                            $times = $availableCash[rand(0, count($availableCash) - 1)];
                            $cash = $allbet * $times;
                            if($lastDropLock[$r][$c] == -1)
                                $bonusSyms[] = $cash * 100;
                            else
                                $bonusSyms[] = $lastDropLock[$r][$c];
                        }
                        else
                        {
                            $bonusSyms[] = -1;
                        }
                    }
                }
                else
                {
                    //determine sphere values
                    for($c = 0; $c < 3; $c++)
                    for($r = 0; $r < 5; $r++)
                    {
                        if($reels['reel'.($r+1)][$c] == $sphere)
                        {
                            $times = $availableCash[rand(0, count($availableCash) - 1)];
                            $cash = $allbet * $times;
                            $bonusSyms[] = $cash * 100;
                        }
                        else
                        {
                            $bonusSyms[] = -1;
                        }
                    }
                }
                
                $bonusMpl = 1;
                if($postData['slotEvent'] == 'freespin')
                    $bonusMpl = $slotSettings->GetGameData($slotSettings->slotId . 'FreespinMultiplier');
                
                if($postData['slotEvent'] == 'droplock')
                {                    
                    for($r = 0; $r < 5; $r++)
                    {
                        $row = [];
                        for($c = 0; $c < 3; $c++)
                        {
                            $pos = $c * 5 + $r;
                            $row[] = $bonusSyms[$pos];
                        }
                        $matrix[] = $row;
                    }

                    //push down the balls
                    $this->pushDownMatrix($matrix);
                    
                    $droplockwin = 0;
                    for($c = 0; $c < 3; $c++)
                    {
                        $isFull = true;
                        $rowWin = 0;
                        for($r = 0; $r < 5; $r++)
                        {
                            if($matrix[$r][$c] != -1)
                                $rowWin += $matrix[$r][$c] / 100;
                            else
                            {
                                $isFull = false;
                                break;
                            }
                        }
                        if($isFull)
                        {
                            $droplockwin += $rowWin;                            
                            for($r = 0; $r < 5; $r++)
                                $matrix[$r][$c] = -1;
                        }
                    }

                    if($droplockwin > 0)
                        $dlSpinWin = 3;
                    $totalWin += $droplockwin;
                }
                else
                {
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
                                                                                    
                                if(($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) ) 
                                {
                                    $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
                                    $tmpWin = $paytable[$csym][3] * $betLine * $mpl * $bonusMpl;
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
                                    $tmpWin = $paytable[$csym][4] * $betLine * $mpl * $bonusMpl;
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
                                    $tmpWin = $paytable[$csym][5] * $betLine * $mpl * $bonusMpl;
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
                        $scattersWin = $allbet * 5;
                        $scatterWin = '<ScatterWin winVal="'.($scattersWin * 100).'" awardIndex="28">'.implode('|', $scatterPos).'</ScatterWin>';
                    }

                    for($c = 0; $c < 3; $c++)
                    {
                        $ballInRow = 0;
                        for($r = 0; $r < 5; $r++)
                        {
                            if($reels['reel'.($r+1)][$c] == $sphere)
                                $ballInRow++;
                        }
                        if($ballInRow > 2)
                            $dlSpinWin = 3;
                    }
                    if($dlSpinWin > 0 && $winType != 'bonus')
                        continue;

                    if($scatterCount > 2 && $dlSpinWin > 0)
                        continue;

                    $totalWin += $scattersWin;
                }
                
                if($minTotalWin == -1 && $scatterCount < 3 || ($minTotalWin > $totalWin && $totalWin > 0))
                {
                    $minTotalWin = $totalWin;
                    $minLineWins = $lineWins;
                    $minFreespinsWon = $freespinsWon;
                    $minReels = $reels;
                    $minFeature = $feature;
                    $minBonusSyms = $bonusSyms;
                    $minDlspinWin = $dlSpinWin;
                    $minMatrix = $matrix;
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
                $feature = $minFeature;
                $bonusSyms = $minBonusSyms;
                $dlSpinWin = $minDlspinWin;
                $matrix = $minMatrix;
            }

            $coinWin = 0; //coins won
            $paylines = '';
            if(!empty($lineWins))
            {
                foreach($lineWins as $winline)
                {
                    $coinWin += $winline[1]; //sum up coins
                    $paylines .= '<PaylineWin index="'.$winline[0].'" winVal="'.($winline[1] * 100).'" awardIndex = "'.$winline[3].'" awardTableIndex = "0">'.$winline[2].'</PaylineWin>';
                }
            }
            
            $winSC = 0;
            $bonusAwarded = 'N';
            $scWin = 0;
            if($scatterWin != '')
            {
                $winSC = 1;
                $bonusAwarded = 'Y';
                $scWin = $allbet * 5;
            }

            if($totalWin > 0)
            {
                $slotSettings->SetBank($postData['slotEvent'], -1 * $totalWin);
                $slotSettings->SetBalance($totalWin);
                $slotSettings->SetWin($totalWin);
            }

            $slotSettings->SetGameData($slotSettings->slotId . 'TotalGameWin', $slotSettings->GetGameData($slotSettings->slotId . 'TotalGameWin') + $totalWin);
            if($postData['slotEvent'] == 'freespin')
                $slotSettings->SetGameData($slotSettings->slotId . 'TotalFreespinWin', $slotSettings->GetGameData($slotSettings->slotId . 'TotalFreespinWin') + $totalWin);
            if($postData['slotEvent'] == 'droplock')
                $slotSettings->SetGameData($slotSettings->slotId . 'TotalDropLockWin', $slotSettings->GetGameData($slotSettings->slotId . 'TotalDropLockWin') + $totalWin);

            $freespinsWon = 0;
            if($scatterCount > 2)
            {
                $freespinsWon = 6;
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
            $reelData = implode(',', $reels['reel1']) . '|' .implode(',', $reels['reel2']) . '|' .implode(',', $reels['reel3']) . '|' .implode(',', $reels['reel4']) . '|' .implode(',', $reels['reel5']);
            $gameResultStr = '';
            if($postData['slotEvent'] == 'droplock')
            {
                $gameResultStr = '<GameResult stake="'.(string)($allbet * 100).'" stakePerLine="'.($allbet * 100 / 50).'" paylineCount="'.$lines.'" totalWin="'.($totalWin * 100).'" betID="">                    
                    <ReelResults numSpins="1">
                    <ReelSpin reelSetIndices="'.implode('|', $reels['ri']).'" spinWins="'.($totalWin * 100).'">
                            <ReelStops>'.implode('|',$reels['rp']).'</ReelStops>
                            <ReelInfo>'.$reelData.'</ReelInfo>
                            '.$paylines.$scatterWin.'
                        </ReelSpin>
                    </ReelResults>
                    <BGInfo totalWagerWin="'.($totalWagerWin * 100).'" bgWinnings="'.($baseGameWin * 100).'" isMaxWin="0"/>
                </GameResult>';
            }
            else
            {
                $gameResultStr = '<GameResult stake="'.(string)($allbet * 100).'" stakePerLine="'.($allbet * 100 / 50).'" paylineCount="'.$lines.'" totalWin="'.($totalWin * 100).'" betID="">                    
                    <ReelResults numSpins="1">
                        <ReelSpin spinIndex="0" reelsetIndex="'.$reelSetIndex.'" winCountPL="'.count($lineWins).'" winCountSC="'.$winSC.'" spinWins="'.(($totalWin-$scWin) * 100).'" freeSpin="'.$isFreespin.'" bonusAwarded="'.$bonusAwarded.'" manualNoWin="'.$manualNoWin.'">
                            <ReelStops>'.implode('|',$reels['rp']).'</ReelStops>
                            <ReelInfo>'.$reelData.'</ReelInfo>
                            '.$paylines.$scatterWin.'
                        </ReelSpin>
                    </ReelResults>
                    <BGInfo totalWagerWin="'.($totalWagerWin * 100).'" bgWinnings="'.($baseGameWin * 100).'" isMaxWin="0"/>
                </GameResult>';
            }
            
            $gameResultStr = trim(preg_replace('/\s+/', ' ', $gameResultStr));
            $gameResultStr = str_replace('> <', '><', $gameResultStr);
            $gameResult = simplexml_load_string($gameResultStr);

            $bonusSymValues = '';
            for($i = 0; $i < count($bonusSyms); $i++)
            {
                if($bonusSyms[$i] != -1)
                {
                    $bonusSymValues = implode('|', $bonusSyms);
                    break;
                }
            }            

            $freespininfo = '';
            $droplockinfo = '';
            if($postData['slotEvent'] == 'bet')
            {
                if($freespinsWon > 0)
                {
                    //triggering freespin
                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                    $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', 'Freespin');
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $freespinsWon);
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreespinMultiplier', $scatterCount);
                    $freespininfo = '<FSInfo fsWinnings="0" freeSpinsTotal="'.$freespinsWon.'" freeSpinNumber="0" />';
                  
                    $bgRecoveryInfo = '<BaseGameRecoveryInfo>
                        <ReelResults numSpins="1">
                            <ReelSpin spinIndex="0" reelsetIndex="0" winCountPL="'.count($lineWins).'" winCountSC="'.$winSC.'" spinWins="'.(($totalWin-$scWin) * 100).'" freeSpin="N" bonusAwarded="N" manualNoWin="'.$manualNoWin.'">
                                <ReelStops>'.implode('|',$reels['rp']).'</ReelStops>
                                '.$paylines.$scatterWin.'
                            </ReelSpin>
                        </ReelResults></BaseGameRecoveryInfo>';
                    $bgRecoveryInfo = trim(preg_replace('/\s+/', ' ', $bgRecoveryInfo));
                    $bgRecoveryInfo = str_replace('> <', '><', $bgRecoveryInfo);
                    $slotSettings->SetGameData($slotSettings->slotId . 'bgRecoveryInfo', $bgRecoveryInfo); 
                }
                //drop lock
                if($dlSpinWin > 0)
                {
                    $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', 'DropLock');
                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentDropLock', 0);
                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalDropLock', 3);

                    //push down bonusSyms                    
                    $matrix = [];
                    for($r = 0; $r < 5; $r++)
                    {
                        $row = [];
                        for($c = 0; $c < 3; $c++)
                        {
                            $pos = $c * 5 + $r;
                            $row[] = $bonusSyms[$pos];
                        }
                        $matrix[] = $row;
                    }

                    $this->pushDownMatrix($matrix);                    

                    $slotSettings->SetGameData($slotSettings->slotId . 'DropLockSyms', $matrix);
                    $slotSettings->SetGameData($slotSettings->slotId . 'PrevDlrRemaining', 3);
                    $droplockinfo = '<DLInfo dlWinnings="0" dlRemaining="3" />';

                    $bgRecoveryInfo = '<BaseGameRecoveryInfo>
                        <ReelResults numSpins="1">
                            <ReelSpin spinIndex="0" reelsetIndex="0" winCountPL="'.count($lineWins).'" winCountSC="'.$winSC.'" spinWins="'.($totalWin * 100).'" freeSpin="N" bonusAwarded="N" manualNoWin="'.$manualNoWin.'">
                                <ReelStops>'.implode('|',$reels['rp']).'</ReelStops>
                                '.$paylines.$scatterWin.'
                            </ReelSpin>
                        </ReelResults>'.$bonusSymValues.'</BaseGameRecoveryInfo>';
                    $bgRecoveryInfo = trim(preg_replace('/\s+/', ' ', $bgRecoveryInfo));
                    $bgRecoveryInfo = str_replace('> <', '><', $bgRecoveryInfo);
                    $slotSettings->SetGameData($slotSettings->slotId . 'bgRecoveryInfo', $bgRecoveryInfo); 
                }
                if($bonusSymValues != '')
                {
                    $bonusSymValues = '<BonusSymValues>'.$bonusSymValues.'</BonusSymValues>';
                    $bsv = simplexml_load_string($bonusSymValues);
                    $this->sxml_append($gameResult, $bsv);
                }
            }
            else if($postData['slotEvent'] == 'freespin')
            {
                //set freespin info during freespin (not triggering freespin)
                if($freespinsWon > 0)
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') + $freespinsWon);
                $totalFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                $currentFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');                
                $bgRecoveryInfo = $slotSettings->GetGameData($slotSettings->slotId . 'bgRecoveryInfo'); 
                $bi = simplexml_load_string($bgRecoveryInfo);
                $this->sxml_append($gameResult, $bi);
        
                $freespininfo = '<FSInfo fsWinnings="'.($freeSpinWin * 100).'" freeSpinsTotal="'.$totalFreespin.'" freeSpinNumber="'.$currentFreespin.'" isMaxWin="0" extraSpinsAwarded="'.$freespinsWon.'" />';

                $multiplier = $slotSettings->GetGameData($slotSettings->slotId . 'FreespinMultiplier');
                $multiplierInfo = '<MultiplierInfo currentMultiplier="'.$multiplier.'" multList="'.$multiplier.'" />';
                $mi = simplexml_load_string($multiplierInfo);
                $this->sxml_append($gameResult, $mi);

                //drop lock
                if($dlSpinWin > 0)
                {
                    $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', 'DropLock');
                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentDropLock', 0);
                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalDropLock', 3);

                    //push down bonusSyms                    
                    $matrix = [];
                    for($r = 0; $r < 5; $r++)
                    {
                        $row = [];
                        for($c = 0; $c < 3; $c++)
                        {
                            $pos = $c * 5 + $r;
                            $row[] = $bonusSyms[$pos];
                        }
                        $matrix[] = $row;
                    }

                    $this->pushDownMatrix($matrix);                    

                    $slotSettings->SetGameData($slotSettings->slotId . 'DropLockSyms', $matrix);
                    $slotSettings->SetGameData($slotSettings->slotId . 'PrevDlrRemaining', 3);
                    $droplockinfo = '<DLInfo dlWinnings="0" dlRemaining="3" />';

                    $bgRecoveryInfo = '<BaseGameRecoveryInfo>
                        <ReelResults numSpins="1">
                            <ReelSpin spinIndex="0" reelsetIndex="0" winCountPL="'.count($lineWins).'" winCountSC="'.$winSC.'" spinWins="'.($totalWin * 100).'" freeSpin="N" bonusAwarded="N" manualNoWin="'.$manualNoWin.'">
                                <ReelStops>'.implode('|',$reels['rp']).'</ReelStops>
                                '.$paylines.$scatterWin.'
                            </ReelSpin>
                        </ReelResults>'.$bonusSymValues.'</BaseGameRecoveryInfo>';
                    $bgRecoveryInfo = trim(preg_replace('/\s+/', ' ', $bgRecoveryInfo));
                    $bgRecoveryInfo = str_replace('> <', '><', $bgRecoveryInfo);
                    $slotSettings->SetGameData($slotSettings->slotId . 'bgRecoveryInfo', $bgRecoveryInfo); 
                }
                if($bonusSymValues != '')
                {
                    $bonusSymValues = '<BonusSymValues>'.$bonusSymValues.'</BonusSymValues>';
                    $bsv = simplexml_load_string($bonusSymValues);
                    $this->sxml_append($gameResult, $bsv);
                }
            }
            else if($postData['slotEvent'] == 'droplock')
            {
                //push down matrix
                $this->pushDownMatrix($matrix);                

                $rowEvent = 0;                
                if($dlSpinWin > 0)
                {
                    $rowEvent = 1;
                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalDropLock', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentDropLock') + 3);
                }

                $totalDropSpins = $slotSettings->GetGameData($slotSettings->slotId . 'TotalDropLock');
                $currentDropSpins = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentDropLock');
                $leftDropSpins = $totalDropSpins - $currentDropSpins;

                if($leftDropSpins == 0)
                {
                    $totalFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                    $currentFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                    if($totalFreespin > $currentFreespin)
                    {
                        $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', 'Freespin'); //restore freespin if freespin is left
                        $freespininfo = '<FSInfo fsWinnings="'.($freeSpinWin * 100).'" freeSpinsTotal="'.$totalFreespin.'" freeSpinNumber="'.$currentFreespin.'" isMaxWin="0" extraSpinsAwarded="0" />';
                    }
                }

                $lastMatrix = $slotSettings->GetGameData($slotSettings->slotId . 'DropLockSyms');
                $newMatrix = [];
                for($r = 0; $r < 5; $r++)
                    {
                        $row = [];
                        for($c = 0; $c < 3; $c++)
                        {
                            $pos = $c * 5 + $r;
                            $row[] = $bonusSyms[$pos];
                        }
                        $newMatrix[] = $row;
                    }

                $prevBalls = [];
                $newBalls = [];
                $allBalls = [];
                for($c = 0; $c < 3; $c++)
                for($r = 0; $r < 5; $r++)
                {
                    $prevBalls[] = $lastMatrix[$r][$c];
                    if($lastMatrix[$r][$c] == -1 && $newMatrix[$r][$c] != -1)
                        $newBalls[] = $newMatrix[$r][$c];
                    else
                        $newBalls[] = -1;
                }

                //allBalls is for pushed matrix for new matrix
                $this->pushDownMatrix($newMatrix);
                for($c = 0; $c < 3; $c++)
                for($r = 0; $r < 5; $r++)
                {
                    $allBalls[] = $newMatrix[$r][$c];
                }

                $dalcStr = '<DropAndLocksymbols prevBonusVals="'.implode('|', $prevBalls).'" newBonusVals="'.implode('|', $newBalls).'" allBonusVals="'.implode('|', $allBalls).'" rowEvents="'.$rowEvent.'" />';
                $dalc = simplexml_load_string($dalcStr);
                $this->sxml_append($gameResult, $dalc);
                $totalDlWin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalDropLockWin');
                $prevDlrRemaining = $slotSettings->GetGameData($slotSettings->slotId . 'PrevDlrRemaining');
                $droplockinfo = '<DLInfo dlWinnings="'.($totalDlWin * 100).'" dlRemaining="'.$leftDropSpins.'" prevDLRemaining="'.$prevDlrRemaining.'" isMaxWin="0" totalDropAndLocks="'.$totalDropSpins.'" />';
                $slotSettings->SetGameData($slotSettings->slotId . 'PrevDlrRemaining', $leftDropSpins);
                $slotSettings->SetGameData($slotSettings->slotId . 'DropLockSyms', $matrix); //save pushed and processed full row matrix
            }           

            if($freespininfo != '')
            {
                $fi = simplexml_load_string($freespininfo);
                $this->sxml_append($gameResult, $fi);
            }
            if($droplockinfo != '')
            {
                $di = simplexml_load_string($droplockinfo);
                $this->sxml_append($gameResult, $di);
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
            
            if($postData['slotEvent'] != 'bet')
                $allbet = 0;
            $slotSettings->SaveLogReport($response, $allbet, $totalWin, $postData['slotEvent']);                  
            return $response;
        }

        public function pushDownMatrix(&$matrix)
        {
            for($r = 0; $r < 5; $r++)
            {
                for($c = 2; $c >= 0; $c--)
                {
                    if($matrix[$r][$c] != -1)
                    {
                        for($cc = 2; $cc >= $c; $cc--)
                        {
                            if($matrix[$r][$cc] == -1)
                                break;
                        }
                        if($cc > $c)
                        {
                            //cc is the lowest value that is not ball
                            $matrix[$r][$cc] = $matrix[$r][$c];
                            $matrix[$r][$c] = -1;
                        }
                    }
                }
            }
        }

    }

}


