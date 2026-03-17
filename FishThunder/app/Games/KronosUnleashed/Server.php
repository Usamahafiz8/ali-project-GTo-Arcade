<?php 
namespace VanguardLTE\Games\KronosUnleashed
{
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;
    use SimpleXMLElement;

    class Server
    {
        public $gameState;
        public $debug = false;
        function getConvertedLine($line, $rowCnt)
        {
            $res = [];
            for($r = 0; $r < 5; $r++)
                for($c = 0; $c < $rowCnt; $c++)
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
                                return '{"javascripts":["/games/KronosUnleashed/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/mainjs/application/capabilitiesdetector.CapabilitiesDetectorBootstrapper.js?resourceversion=5.0.0.11-1663077573&appcode=capabilities-detector","/games/KronosUnleashed/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/translationjs/bundles/translations/#{locale}/capabilitiesdetector.LocalizationBundle.js?resourceversion=5.0.0.11-1663077573&appcode=capabilities-detector"],"jsons":["/games/KronosUnleashed/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/translationjson/bundles/translations/#{locale}/capabilitiesdetector.LocalizationBundle.json?resourceversion=5.0.0.11-1663077573&appcode=capabilities-detector"],"main_class":"capabilitiesdetector.CapabilitiesDetectorBootstrapper","supported_locales":["it_IT","ru_RU","el_GR","pl_PL","ro_RO","tr_TR","fr_FR","cs_CZ","hu_HU","ca_ES","de_DE","sk_SK","es_ES","nl_NL","fr_CA","sv_SE","da_DK","bg_BG","hr_HR","fi_FI","en_GB","et_EE","lt_LT","lv_LV","sl_SI","no_NO","pt_PT"],"default_locale":"en"}';
                            break;
                            case "gls-platform":
                                return '{"javascripts":["/games/KronosUnleashed/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/mainjs/application/glsplatform.GlsPlatform.js?resourceversion=5.0.0.11-1663077573&appcode=gls-platform","/games/KronosUnleashed/acy-resource.wimobile.casinarena.com/resource-service/metadatajs/bundles/metadata/glsplatform.MetaDataBundle.js?resourceversion=5.0.0.11-1663077573&appcode=gls-platform&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=kronosunleashed&locale=en_US&webaudio=true","/games/KronosUnleashed/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/translationjs/bundles/translations/#{locale}/glsplatform.LocalizationsBundle.js?resourceversion=5.0.0.11-1663077573&appcode=gls-platform"],"jsons":["/games/KronosUnleashed/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/metadatajson/bundles/metadata/glsplatform.MetaDataBundle.json?resourceversion=5.0.0.11-1663077573&appcode=gls-platform&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=kronosunleashed&locale=en_US&webaudio=true","/games/KronosUnleashed/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/translationjson/bundles/translations/#{locale}/glsplatform.LocalizationsBundle.json?resourceversion=5.0.0.11-1663077573&appcode=gls-platform"],"main_class":"glsplatform.GlsPlatform","supported_locales":["sl_SI","pl_PL","lt_LT","bg_BG","ru_RU","ro_RO","el_GR","pt_PT","en_GB","sk_SK","cs_CZ","ca_ES","fr_CA","es_ES","lv_LV","hr_HR","it_IT","fi_FI","de_DE","hu_HU","fr_FR","tr_TR","sv_SE","da_DK","no_NO","et_EE","nl_NL"],"default_locale":"en"}';
                                break;
                            case "lean-regular-partner-adapter":
                                return '{"javascripts":["/games/KronosUnleashed/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/mainjs/application/leanpartneradapter.LeanPartnerAdapter.js?resourceversion=5.0.0.11-1663077573&appcode=lean-regular-partner-adapter","/games/KronosUnleashed/acy-resource.wimobile.casinarena.com/resource-service/metadatajs/bundles/metadata/leanpartneradapter.MetaDataBundle.js?resourceversion=5.0.0.11-1663077573&appcode=lean-regular-partner-adapter&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=kronosunleashed&locale=en_US&webaudio=true","/games/KronosUnleashed/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/translationjs/bundles/translations/#{locale}/leanpartneradapter.LocalizationsBundle.js?resourceversion=5.0.0.11-1663077573&appcode=lean-regular-partner-adapter"],"jsons":["/games/KronosUnleashed/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/metadatajson/bundles/metadata/leanpartneradapter.MetaDataBundle.json?resourceversion=5.0.0.11-1663077573&appcode=lean-regular-partner-adapter&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=kronosunleashed&locale=en_US&webaudio=true","/games/KronosUnleashed/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1663077573/resource-service/translationjson/bundles/translations/#{locale}/leanpartneradapter.LocalizationsBundle.json?resourceversion=5.0.0.11-1663077573&appcode=lean-regular-partner-adapter"],"main_class":"commonadapter.PortraitPartnerAdapter","supported_locales":["it_IT","ru_RU","el_GR","pl_PL","ro_RO","tr_TR","fr_FR","cs_CZ","hu_HU","ca_ES","de_DE","sk_SK","es_ES","nl_NL","fr_CA","sv_SE","da_DK","bg_BG","hr_HR","fi_FI","en_GB","et_EE","lt_LT","lv_LV","sl_SI","no_NO","pt_PT"],"default_locale":"en"}';
                                break;
                            case "kronosunleashed":
                                return '{
                                    "orientation": "PORTRAIT_MOBILE",
                                    "default_locale": "en",
                                    "inject": [
                                        {
                                            "link": {
                                                "rel": "stylesheet",
                                                "href": "content/kronosunleashed/app/css/app.css"
                                            }
                                        }
                                    ],
                                    "javascripts": [
                                        "content/kronosunleashed/app/js/game.js?resourceversion=5.0.0.11-1663077573&appcode=kronosunleashed&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=kronosunleashed&locale=en_US&webaudio=true",
                                        "content/kronosunleashed/app/js/metadatabundle.js?resourceversion=5.0.0.11-1663077573&appcode=kronosunleashed&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=kronosunleashed&locale=en_US&webaudio=true"
                                    ],
                                    "publish_events": {
                                        "platform":"1"
                                    },
                                    "main_class": "BL.Adapters.SG.Adapter",
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
                        $filename = base_path() . '/app/Games/KronosUnleashed/game.txt';
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
                        $allbet = (int)$postData->Stake['total'] * 0.01;
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
            }
            else
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bet');
            }
            $reelName = 'Reels'.$reelSetIndex;
            $lines = count($linesId);
            $allbet = (int)$postData->Stake['total'] * 0.01;
            $betLine = $allbet / 0.2 * 0.01; //paytable is 0.2 based      

            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $allbet);
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
            
            if($postData['slotEvent'] == 'freespin')
            {
                $totalFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                $currentFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');                
                $leftSpin = $totalFreespin - $currentFreespin;
                if($leftSpin > 0)
                {
                    $spinWinLimit /= $leftSpin;
                }
            }
            $spinAcquired = false;             
            $gameWin = 0;//$slotSettings->GetGameData($slotSettings->slotId . 'GameWin');

            $minReels = [];
            $minLineWins = [];
            $minTotalWin = -1;
            $minFreespinsWon = 0;
            $minFreespinInfo = '';
            $minRespins = [];
            $minJackpotWins = [];

            $totalWin = 0;
            $freespinsWon = 0;
            $lineWins = [];
            $reels = [];
            
            $scatter = 0;
            $wild = [10];
            $freespinInfo = '';
            $scatterWin = '';            
            $scatterCount = 0;
            $freespinTriggerWin = 0;
            $respins = [];
            $jackpotWins = [];
            
            for( $i = 0; $i <= 300; $i++ ) 
            {
                $respins = [];
                $rowCnt = 8;
                $kronosSym = 9;
                $scatterWin = '';
                $freespinInfo = '';
                $totalWin = 0;
                $freespinsWon = 0;
                $lineWins = [];                
                
                if($this->debug && $postData['slotEvent'] != 'freespin')
                {                 
                    $winType = 'bonus';
                }

                $reels = $slotSettings->GetReelStrips($winType, $reelName, $postData['slotEvent']);

                //check if first reel is full of kronos
                $kronosCnt = 0;
                for($c = 0; $c < $rowCnt; $c++)
                {
                    if($reels['reel1'][$c] == $kronosSym)
                        $kronosCnt++;
                }
                
                if($kronosCnt >= 6)
                {
                    if($winType != 'bonus')
                        continue;
                    //insert kronos respins
                    for($c = 0; $c < $rowCnt; $c++)
                    {
                        $reels['reel1'][$c] = $kronosSym;
                    }
                    for($r = 2; $r <= 5; $r++)
                        for($c = 0; $c < $rowCnt; $c++)
                        {
                                $reels['reel'.$r][$c] = -1;
                        }
                    $jackpotAppeared = false;
                    $respinCnt = 3;
                    
                    while($respinCnt > 0)
                    {
                        $respin = [];
                        $respinCnt--;
                        $newAppeared = false;
                        $specialAppeared = false;
                        
                        for($r = 2; $r <= 5; $r++)
                            for($c = 0; $c < $rowCnt; $c++)
                            {
                                if($reels['reel'.$r][$c] == -1)
                                {
                                    $randSym = -1;
                                    $rand = rand(0, 300);
                                    if($rand < 6)
                                        $randSym = $kronosSym;
                                    else if($rand == 6)
                                        $randSym = 2;
                                    else if($rand == 7)
                                        $randSym = 3;
                                    else if($rand == 8)
                                        $randSym = 4;
                                    else if($rand == 9)
                                        $randSym = 5;
                                    
                                    if(count($respins) == 1)
                                        $randSym = 5;
                                    if(count($respins) > 12)
                                        $randSym = -1;

                                    $reels['reel'.$r][$c] = $randSym;
                                    if($randSym != -1)
                                    {
                                        if($randSym == $kronosSym)
                                            $respin[] = '1,'.($c * 5 + $r - 1);
                                        else
                                        {
                                            if(!$specialAppeared)
                                            {                                                
                                                $specialAppeared = true;

                                                if($randSym == 5)
                                                {
                                                    //jackpot bonus
                                                    if(!$jackpotAppeared)
                                                    {
                                                        $respin[] = $randSym.','.($c * 5 + $r - 1);
                                                        $jackpotAppeared = true;

                                                        if(rand(0, 100) < 10)
                                                        {
                                                            $totalWin += $allbet * 35;
                                                            $jackpotWins[] = '3,9';                                                        
                                                        }
                                                        else
                                                        {
                                                            $totalWin += $allbet * 15;
                                                            $jackpotWins[] = '4,9';
                                                        }                                                        
                                                    }
                                                    else
                                                    {
                                                        $respin[] = '1,'.($c * 5 + $r - 1);
                                                        $reels['reel'.$r][$c] = $kronosSym;
                                                    }
                                                }
                                                else if($randSym == 2)
                                                {
                                                    //expand surrounding
                                                    for($sr = $r - 1; $sr <= $r + 1; $sr++)
                                                        for($sc = $c -1; $sc <= $c + 1; $sc++)
                                                        {
                                                            if(isset($reels['reel'.$sr][$sc]))
                                                            {
                                                                if($reels['reel'.$sr][$sc] == -1)
                                                                    $reels['reel'.$sr][$sc] = $kronosSym;
                                                            }
                                                        }
                                                    $respin[] = $randSym.','.($c * 5 + $r - 1);
                                                }
                                                else if($randSym == 3)
                                                {
                                                    if($kronosSym == 12)
                                                    {
                                                        $respin[] = '1,'.($c * 5 + $r - 1);
                                                    }
                                                    else
                                                    {
                                                        //kronos pay increase
                                                        if($kronosSym == 9)
                                                        {
                                                            for($sr = 1; $sr <= 5; $sr++)
                                                                for($sc = 0; $sc < $rowCnt; $sc++)
                                                                {
                                                                    if($reels['reel'.$sr][$sc] == $kronosSym)
                                                                        $reels['reel'.$sr][$sc] = 11;
                                                                }
                                                            $kronosSym = 11;
                                                        }
                                                        else if($kronosSym == 11)
                                                        {
                                                            for($sr = 1; $sr <= 5; $sr++)
                                                                for($sc = 0; $sc < $rowCnt; $sc++)
                                                                {
                                                                    if($reels['reel'.$sr][$sc] == $kronosSym)
                                                                        $reels['reel'.$sr][$sc] = 12;
                                                                }
                                                            $kronosSym = 12;
                                                        }
                                                        $respin[] = $randSym.','.($c * 5 + $r - 1);
                                                    }
                                                }
                                                else if($randSym == 4)
                                                {
                                                    //extra row
                                                    for($sr = 1; $sr <= 5; $sr++)
                                                        $reels['reel'.$sr][$rowCnt] = $kronosSym;
                                                    $rowCnt++;   
                                                    $respin[] = $randSym.','.($c * 5 + $r - 1);                                                 
                                                }
                                            }
                                            else
                                            {
                                                $respin[] = '1,'.($c * 5 + $r - 1);
                                                $reels['reel'.$r][$c] = $kronosSym;
                                            }                                            
                                        }
                                        $newAppeared = true;                                        
                                    }                                    
                                }
                            }
                        if($newAppeared)
                        {
                            $respinCnt++;                            
                        }
                            
                        $respins[] = $respin;
                    }

                    //change 2,3,4 special syms to kronos
                    for($r = 1; $r <= 5; $r++)
                        for($c = 0; $c < $rowCnt; $c++)
                        {
                            if($reels['reel'.$r][$c] == 2 || $reels['reel'.$r][$c] == 3 || $reels['reel'.$r][$c] == 4)
                                $reels['reel'.$r][$c] = $kronosSym;
                        }
                }
                
                $bonusMpl = 1;                
                if($rowCnt == 8)
                    $lines = 60;
                else if($rowCnt == 9)
                    $lines = 70;
                else if($rowCnt == 10)
                    $lines = 80;
                else if($rowCnt == 11)
                    $lines = 90;
                else if($rowCnt == 12)
                    $lines = 100;
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
                                $emptyLine = [array_fill(0, $rowCnt, 0),array_fill(0, $rowCnt, 0),array_fill(0, $rowCnt, 0),array_fill(0, $rowCnt, 0),array_fill(0, $rowCnt, 0)];
                                $tmpWin = $slotSettings->Paytable[$csym][3] * $betLine * $mpl * $bonusMpl;
                                $coin = $slotSettings->Paytable[$csym][3] * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $cWins[$k] = $tmpWin;
                                    $emptyLine[0][$p0] = 1;
                                    $emptyLine[1][$p1] = 1;
                                    $emptyLine[2][$p2] = 1;
                                    $winline = [$k, $coin, $this->getConvertedLine($emptyLine, $rowCnt), $tmpWin, $slotSettings->awardIndices[$csym][3]]; //[lineId, coinWon, winPositions]
                                }                              
                            }
                            if( ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) ) 
                            {
                                $emptyLine = [array_fill(0, $rowCnt, 0),array_fill(0, $rowCnt, 0),array_fill(0, $rowCnt, 0),array_fill(0, $rowCnt, 0),array_fill(0, $rowCnt, 0)];
                                $tmpWin = $slotSettings->Paytable[$csym][3] * $betLine * $mpl * $bonusMpl;
                                $coin = $slotSettings->Paytable[$csym][3] * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $cWins[$k] = $tmpWin;
                                    $emptyLine[2][$p2] = 1;
                                    $emptyLine[3][$p3] = 1;
                                    $emptyLine[4][$p4] = 1;
                                    $winline = [$k, $coin, $this->getConvertedLine($emptyLine, $rowCnt), $tmpWin, $slotSettings->awardIndices[$csym][3]]; //[lineId, coinWon, winPositions]
                                }                              
                            }

                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) ) 
                            {
                                $emptyLine = [array_fill(0, $rowCnt, 0),array_fill(0, $rowCnt, 0),array_fill(0, $rowCnt, 0),array_fill(0, $rowCnt, 0),array_fill(0, $rowCnt, 0)];
                                $tmpWin = $slotSettings->Paytable[$csym][4] * $betLine * $mpl * $bonusMpl;
                                $coin = $slotSettings->Paytable[$csym][4] * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $cWins[$k] = $tmpWin;
                                    $emptyLine[0][$p0] = 1;
                                    $emptyLine[1][$p1] = 1;
                                    $emptyLine[2][$p2] = 1;
                                    $emptyLine[3][$p3] = 1;
                                    $winline = [$k, $coin, $this->getConvertedLine($emptyLine, $rowCnt), $tmpWin, $slotSettings->awardIndices[$csym][4]]; //[lineId, coinWon, winPositions]                                                             
                                }                               
                            }

                            if( ($s[4] == $csym || in_array($s[4], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) ) 
                            {
                                $emptyLine = [array_fill(0, $rowCnt, 0),array_fill(0, $rowCnt, 0),array_fill(0, $rowCnt, 0),array_fill(0, $rowCnt, 0),array_fill(0, $rowCnt, 0)];
                                $tmpWin = $slotSettings->Paytable[$csym][4] * $betLine * $mpl * $bonusMpl;
                                $coin = $slotSettings->Paytable[$csym][4] * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $cWins[$k] = $tmpWin;
                                    $emptyLine[4][$p4] = 1;
                                    $emptyLine[1][$p1] = 1;
                                    $emptyLine[2][$p2] = 1;
                                    $emptyLine[3][$p3] = 1;
                                    $winline = [$k, $coin, $this->getConvertedLine($emptyLine, $rowCnt), $tmpWin, $slotSettings->awardIndices[$csym][4]]; //[lineId, coinWon, winPositions]                                                             
                                }                               
                            }
                            
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) ) 
                            {
                                $emptyLine = [array_fill(0, $rowCnt, 0),array_fill(0, $rowCnt, 0),array_fill(0, $rowCnt, 0),array_fill(0, $rowCnt, 0),array_fill(0, $rowCnt, 0)];
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
                                    $winline = [$k, $coin, $this->getConvertedLine($emptyLine, $rowCnt), $tmpWin, $slotSettings->awardIndices[$csym][5]]; //[lineId, coinWon,winPositions]                                                            
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
                
                if($kronosCnt >= 6 && $totalWin < $allbet) //lightning respin guarantee
                    $totalWin = $allbet;

                //calc freespin
                $scatterCount = 0;
                $scatterPos = [];
                
                $freespinsWon = 0;
                $scatterReelPositions = [
                    'reel1' => ['pos' => []],
                    'reel2' => ['pos' => []],
                    'reel3' => ['pos' => []],
                    'reel4' => ['pos' => []],
                    'reel5' => ['pos' => []],
                ];

                for($r = 1; $r <= 5; $r++)
                {
                    $min = -1;
                    $max = -1;
                    $scatterAppear = false;
                    for($c = 0; $c < $rowCnt; $c++)
                    {
                        if($reels['reel'.$r][$c] == $scatter)
                        {
                            $scatterAppear = true;
                            $scatterPos[] = $c * 5 + $r - 1;
                            if($min == -1 || $min > $c)
                                $min = $c;
                            if($max == -1 || $max < $c)
                                $max = $c;
                            $scatterReelPositions['reel'.$r]['pos'][] = $c;
                        }
                    }
                    if($scatterAppear)
                        $scatterCount++;

                    if($min != -1)
                    {
                        $scatterReelPositions['reel'.$r]['min'] = $min;
                        $scatterReelPositions['reel'.$r]['max'] = $max;
                    }
                }

                //calculate groups
                if($scatterCount > 2)
                    for($r = 1; $r <= 5; $r++)
                    {
                        if(count($scatterReelPositions['reel'.$r]['pos']) > 0)
                        {
                            $maxN = $r;
                            for($n = $r + 1; $n <= 5; $n++)
                            {
                                //check same row
                                $hasSameRow = false;
                                if(count($scatterReelPositions['reel'.$n]['pos']) > 0)
                                {
                                    foreach($scatterReelPositions['reel'.$n]['pos'] as $pos)
                                    {
                                        if(in_array($pos, $scatterReelPositions['reel'.($n-1)]['pos']))
                                        {
                                            $hasSameRow = true;
                                            break;
                                        }
                                    }
                                }
                                else
                                    $hasSameRow = false;
                                
                                if(!$hasSameRow)
                                    break;
                                $maxN = $n;
                            }

                            //crate group with max n
                            $min = -1;
                            $max = -1;
                            for($g = $r; $g <= $maxN; $g++)
                            {
                                if($min == -1 || $min > $scatterReelPositions['reel'.$g]['min'])
                                    $min = $scatterReelPositions['reel'.$g]['min'];
                                if($max == -1 || $min < $scatterReelPositions['reel'.$g]['max'])
                                    $max = $scatterReelPositions['reel'.$g]['max'];
                            }
                            $freespinsWon += ($max - $min + 1) * ($maxN - $r + 1) * 2;
                            $r = $maxN;
                        }
                    }

                if($freespinsWon > 0 && $winType != 'bonus')
                    continue;

                if($freespinsWon > 0)
                {
                    $scatterWin = '<ScatterWin winVal="0" awardIndex="0"></ScatterWin>';                    
                    $freespinInfo = '<FSInfo fsWinnings="0" freeSpinsTotal="'.$freespinsWon.'" freeSpinNumber="0" isMaxWin="0" fsGuarantee="1000" />';
                }

                if($minTotalWin == -1 && $scatterCount < 3 || ($minTotalWin > $totalWin && $totalWin > 0))
                {
                    $minTotalWin = $totalWin;
                    $minLineWins = $lineWins;
                    $minFreespinsWon = $freespinsWon;
                    $minReels = $reels;
                    $minFreespinInfo = $freespinInfo;
                    $minRespins = $respins;
                    $minJackpotWins = $jackpotWins;
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
                $freespinInfo = $minFreespinInfo;
                $respins = $minRespins;
                $jackpotWins = $minJackpotWins;
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
            if(count($respins) > 0)
                $postData['slotEvent'] = 'respin';
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
            $header = simplexml_load_string('<Header sessionID="vkWv5HHtCBUMs3Gq1ogvjRBPthnKy49aqkvJ/CKj2EKCnk8ZnfY+aXVrGjaDSipAQKeZRMBeWY2+KgSXm60lcFLhX6a7iZfppr/nfhKgFY4=" ccyCode="GBP" deciSep="." thousandSep="," lang="en_US" gameID="20208" versionID="1_0" fullVersionID="1.0.3" isRecovering="N"/>');
            $accountData = simplexml_load_string('<AccountData><AccountData><CurrencyMultiplier>1</CurrencyMultiplier></AccountData></AccountData>');
            $balance = simplexml_load_string('<Balances><Balance name="CASH_BALANCE" value="'.($slotSettings->GetBalance() * 100).'"/></Balances>');
            $gameResult = simplexml_load_string('<GameResult stake="'.(int)$postData->Stake['total'].'" stakePerLine="3" paylineCount="'.$lines.'" totalWin="'.($totalWin * 100).'" betID="">
                    <ReelResults numSpins="1">
                        <ReelSpin spinIndex="0" reelsetIndex="'.$reelSetIndex.'" winCountPL="'.count($lineWins).'" winCountSC="'.$winSC.'" spinWins="'.($totalWin * 100).'" freeSpin="'.$isFreespin.'" bonusAwarded="'.$bonusAwarded.'" manualNoWin="'.$manualNoWin.'">
                            <ReelStops>'.implode('|',$reels['rp']).'</ReelStops>
                            '.$paylines.$scatterWin.'
                        </ReelSpin>
                    </ReelResults>
                    <BGInfo totalWagerWin="'.($totalWagerWin * 100).'" bgWinnings="'.($baseGameWin * 100).'" baseGameSpinsRemaining="0" isBigBet="0" isMaxWin="0"/>                    
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
                $freespinInfo = '<FSInfo fsWinnings="1340" freeSpinsTotal="'.$totalFreespin.'" freeSpinNumber="'.$currentFreespin.'" isMaxWin="0" fsGuarantee="0" freeSpinsAwarded="0" />';
            }
            if($freespinInfo != '')
            {
                $fs = simplexml_load_string($freespinInfo);
                $this->sxml_append($gameResult, $fs);
            }
            if(count($respins) > 0)
            {
                $rs = '<Respins jackpots="'.implode(';', $jackpotWins).'">';
                foreach($respins as $index => $respin)
                {
                    $rs .= '<Respin index="'.$index.'">'.implode(';', $respin).'</Respin>';
                }
                $rs .= '</Respins>';
                $rs = simplexml_load_string($rs);
                $this->sxml_append($gameResult, $rs);
            }

     
            $reelData = '<ReelData reel1="'.implode(',',$reels['reel1']).'" reel2="'.implode(',',$reels['reel2']).'" reel3="'.implode(',',$reels['reel3']).'" reel4="'.implode(',',$reels['reel4']).'" reel5="'.implode(',',$reels['reel5']).'"></ReelData>';
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
            else if($postData['slotEvent'] == 'freespin')
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


