<?php 
namespace VanguardLTE\Games\QuickHitSunDragon
{
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;
    use SimpleXMLElement;

    class Server
    {
        public $gameState;
        public $debug = false;
        function getConvertedLine($csym, $reel, $len, $block)
        {
            $res = [];
            $wild = '0';
            if($len > 0)
            {
                for($r = 0; $r < $len; $r++)
                    for($c = 0; $c < 6; $c++)
                    {
                        if(($reel['reel'.($r+1)][$c] == $csym || $reel['reel'.($r+1)][$c] == $wild) && $block['reel'.($r+1)][$c] == 0)
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
                                return '{"javascripts":["/games/QuickHitSunDragon/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/mainjs/application/capabilitiesdetector.CapabilitiesDetectorBootstrapper.js?resourceversion=5.0.0.11-1656345280&appcode=capabilities-detector","/games/QuickHitSunDragon/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/translationjs/bundles/translations/#{locale}/capabilitiesdetector.LocalizationBundle.js?resourceversion=5.0.0.11-1656345280&appcode=capabilities-detector"],"jsons":["/games/QuickHitSunDragon/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/translationjson/bundles/translations/#{locale}/capabilitiesdetector.LocalizationBundle.json?resourceversion=5.0.0.11-1656345280&appcode=capabilities-detector"],"main_class":"capabilitiesdetector.CapabilitiesDetectorBootstrapper","supported_locales":["it_IT","ru_RU","el_GR","pl_PL","ro_RO","tr_TR","fr_FR","cs_CZ","hu_HU","ca_ES","de_DE","sk_SK","es_ES","nl_NL","fr_CA","sv_SE","da_DK","bg_BG","hr_HR","fi_FI","en_GB","et_EE","lt_LT","lv_LV","sl_SI","no_NO","pt_PT"],"default_locale":"en"}';
                            break;
                            case "gls-platform":
                                return '{"javascripts":["/games/QuickHitSunDragon/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/mainjs/application/glsplatform.GlsPlatform.js?resourceversion=5.0.0.11-1656345280&appcode=gls-platform","/games/QuickHitSunDragon/acy-resource.wimobile.casinarena.com/resource-service/metadatajs/bundles/metadata/glsplatform.MetaDataBundle.js?resourceversion=5.0.0.11-1656345280&appcode=gls-platform&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=quickhitsundragon&locale=en_US&webaudio=true","/games/QuickHitSunDragon/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/translationjs/bundles/translations/#{locale}/glsplatform.LocalizationsBundle.js?resourceversion=5.0.0.11-1656345280&appcode=gls-platform"],"jsons":["/games/QuickHitSunDragon/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/metadatajson/bundles/metadata/glsplatform.MetaDataBundle.json?resourceversion=5.0.0.11-1656345280&appcode=gls-platform&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=quickhitsundragon&locale=en_US&webaudio=true","/games/QuickHitSunDragon/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/translationjson/bundles/translations/#{locale}/glsplatform.LocalizationsBundle.json?resourceversion=5.0.0.11-1656345280&appcode=gls-platform"],"main_class":"glsplatform.GlsPlatform","supported_locales":["sl_SI","pl_PL","lt_LT","bg_BG","ru_RU","ro_RO","el_GR","pt_PT","en_GB","sk_SK","cs_CZ","ca_ES","fr_CA","es_ES","lv_LV","hr_HR","it_IT","fi_FI","de_DE","hu_HU","fr_FR","tr_TR","sv_SE","da_DK","no_NO","et_EE","nl_NL"],"default_locale":"en"}';
                                break;
                            case "lean-regular-partner-adapter":
                                return '{"javascripts":["/games/QuickHitSunDragon/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/mainjs/application/leanpartneradapter.LeanPartnerAdapter.js?resourceversion=5.0.0.11-1656345280&appcode=lean-regular-partner-adapter","/games/QuickHitSunDragon/acy-resource.wimobile.casinarena.com/resource-service/metadatajs/bundles/metadata/leanpartneradapter.MetaDataBundle.js?resourceversion=5.0.0.11-1656345280&appcode=lean-regular-partner-adapter&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=quickhitsundragon&locale=en_US&webaudio=true","/games/QuickHitSunDragon/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/translationjs/bundles/translations/#{locale}/leanpartneradapter.LocalizationsBundle.js?resourceversion=5.0.0.11-1656345280&appcode=lean-regular-partner-adapter"],"jsons":["/games/QuickHitSunDragon/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/metadatajson/bundles/metadata/leanpartneradapter.MetaDataBundle.json?resourceversion=5.0.0.11-1656345280&appcode=lean-regular-partner-adapter&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=quickhitsundragon&locale=en_US&webaudio=true","/games/QuickHitSunDragon/d1gpiy04es6c1f.cloudfront.net/5.0.0.11-1656345280/resource-service/translationjson/bundles/translations/#{locale}/leanpartneradapter.LocalizationsBundle.json?resourceversion=5.0.0.11-1656345280&appcode=lean-regular-partner-adapter"],"main_class":"commonadapter.TopboxPartnerAdapter","supported_locales":["it_IT","ru_RU","el_GR","pl_PL","ro_RO","tr_TR","fr_FR","cs_CZ","hu_HU","ca_ES","de_DE","sk_SK","es_ES","nl_NL","fr_CA","sv_SE","da_DK","bg_BG","hr_HR","fi_FI","en_GB","et_EE","lt_LT","lv_LV","sl_SI","no_NO","pt_PT"],"default_locale":"en"}';
                                break;
                            case "quickhitsundragon":
                                return '{
                                    "orientation": "BOTH",
                                    "default_locale": "en",
                                    "inject": [
                                        {
                                            "link": {
                                                "rel": "stylesheet",
                                                "href": "content/quickhitsundragon/css/app.css"
                                            }
                                        }
                                    ],
                                    "javascripts": [
                                        "content/quickhitsundragon/js/bootstrapper.js?resourceversion=5.0.0.11-1656345280&appcode=quickhitsundragon&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=quickhitsundragon&locale=en_US&webaudio=true"
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
                        $filename = base_path() . '/app/Games/QuickHitSunDragon/game.txt';
                        $file = fopen($filename, "r" );
                        $filesize = filesize( $filename );
                        $response = fread( $file, $filesize );
                        $response = str_replace('BAL_REPLACE', $slotSettings->GetBalance() * 100, $response);
                        fclose( $file );               
                        
                        $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', '');                        
                        $slotSettings->SetGameData($slotSettings->slotId . 'TotalFreespinWin', '0');
                        

                        $golden_blocks = [
                            'reel1' => [1,1,1,0,0,0],
                            'reel2' => [1,1,1,0,0,0],
                            'reel3' => [1,1,1,0,0,0],
                            'reel4' => [1,1,1,0,0,0],
                            'reel5' => [1,1,1,0,0,0],
                        ];
                        
                        $slotSettings->SetGameData($slotSettings->slotId . 'Blocks', $golden_blocks);
                        $slotSettings->SetGameData($slotSettings->slotId . 'StandardBlocks', $golden_blocks);
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

            $golden_blocks = $slotSettings->GetGameData($slotSettings->slotId . 'Blocks');
            $allbet = (int)((string)$postData->Stake['total']) * 0.01;                                 
            $betLine = $allbet / 0.5 * 0.01; //paytable is 0.4 based      

            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $allbet);
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
            
            $spinAcquired = false;             
            $gameWin = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin');

            $minReels = [];
            $minLineWins = [];
            $minTotalWin = -1;
            $minFreespinsWon = 0;

            $totalWin = 0;
            $freespinsWon = 0;
            $lineWins = [];
            $reels = [];
            
            $scatter = '13';
            $jackpot = '12';
            $freespinInfo = '';
            $scatterWin = '';            
            $scatterCount = 0;

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

                if($postData['slotEvent'] == 'freespin')
                {
                    $golden_blocks = $slotSettings->GetGameData($slotSettings->slotId . 'FreespinBlocks');
                }
                else
                {
                    if(rand(0, 100) < 15)
                    {
                        $spinFeature = rand(0, count($slotSettings->fsPos) - 1);
                        $block_indices = explode('|', $slotSettings->fsPos[$spinFeature]['skip']);
                        $golden_blocks = [
                            'reel1' => [0,0,0,0,0,0],
                            'reel2' => [0,0,0,0,0,0],
                            'reel3' => [0,0,0,0,0,0],
                            'reel4' => [0,0,0,0,0,0],
                            'reel5' => [0,0,0,0,0,0],
                        ];
                        for($k = 0; $k < count($block_indices); $k++)
                        {
                            $r = $block_indices[$k] % 5;
                            $c = (int)($block_indices[$k] / 5);
                            $golden_blocks['reel'.($r+1)][$c] = 1;
                        }
                    }
                    else
                    {
                        $golden_blocks = $slotSettings->GetGameData($slotSettings->slotId . 'StandardBlocks');                        
                    }
                    $slotSettings->SetGameData($slotSettings->slotId . 'Blocks', $golden_blocks);
                }

                $reels0 = $reels;
                $bonusMpl = 1;                
                
                for( $j = 0; $j < count($slotSettings->SymbolGame); $j++ ) 
                {
                    $mpl = 1;
                    $csym = $slotSettings->SymbolGame[$j];                    
                    $mul1 = $slotSettings->getMultiplier($reels['reel1'], $csym, $golden_blocks['reel1']);
                    $mul2 = $slotSettings->getMultiplier($reels['reel2'], $csym, $golden_blocks['reel2']);
                    $mul3 = $slotSettings->getMultiplier($reels['reel3'], $csym, $golden_blocks['reel3']);
                    $mul4 = $slotSettings->getMultiplier($reels['reel4'], $csym, $golden_blocks['reel4']);
                    $mul5 = $slotSettings->getMultiplier($reels['reel5'], $csym, $golden_blocks['reel5']);

                    if($mul1 > 0 && $mul2 > 0 && $mul3 > 0) //from left to right 3 symbols contained
                    {
                        $mpl = $mul1 * $mul2 * $mul3;
                        $tmpWin = $slotSettings->Paytable[$csym][3] * $betLine * $mpl * $bonusMpl;
                        if($tmpWin > $cWins[$csym])
                        {
                            $cWins[$csym] = $tmpWin;
                            $winline = [$j + 1, $tmpWin, $this->getConvertedLine($csym, $reels, 3, $golden_blocks), $mpl, $slotSettings->awardIndices[$csym][3]];
                        }
                    }
                    if($mul1 > 0 && $mul2 > 0 && $mul3 > 0 && $mul4 > 0) //from left to right 4 symbols contained
                    {
                        $mpl = $mul1 * $mul2 * $mul3 * $mul4;
                        $tmpWin = $slotSettings->Paytable[$csym][4] * $betLine * $mpl * $bonusMpl;
                        if($tmpWin > $cWins[$csym])
                        {
                            $cWins[$csym] = $tmpWin;
                            $winline = [$j + 1, $tmpWin, $this->getConvertedLine($csym, $reels, 4, $golden_blocks), $mpl, $slotSettings->awardIndices[$csym][4]];
                        }
                    }
                    if($mul1 > 0 && $mul2 > 0 && $mul3 > 0 && $mul4 > 0 && $mul5 > 0) //from left to right 5 symbols contained
                    {
                        $mpl = $mul1 * $mul2 * $mul3 * $mul4 * $mul5;
                        $tmpWin = $slotSettings->Paytable[$csym][5] * $betLine * $mpl * $bonusMpl;
                        if($tmpWin > $cWins[$csym])
                        {
                            $cWins[$csym] = $tmpWin;
                            $winline = [$j + 1, $tmpWin, $this->getConvertedLine($csym, $reels, 5, $golden_blocks), $mpl, $slotSettings->awardIndices[$csym][5]];
                        }
                    }
                    
                    if($cWins[$csym] > 0 && !empty($winline))
                    {
                        array_push($lineWins, $winline);
                        $totalWin += $cWins[$csym];
                    }
                }
                
                // $totalWin += $gameWin;

                //calc jackpot symbol
                $jackpotCnt = 0;                
                $jackpotPos = [];
                //calc freespin
                $scatterCount = 0;
                $scatterPos = [];
                
                for($r = 1; $r <= 5; $r++)
                {
                    for($c = 0; $c < 6; $c++)
                    {
                        if($reels0['reel'.$r][$c] == $scatter && $golden_blocks['reel'.$r][$c] == 0)
                        {
                            $scatterCount++;
                            $scatterPos[] = $c * 5 + $r - 1;
                        }
                        if($reels0['reel'.$r][$c] == $jackpot && $golden_blocks['reel'.$r][$c] == 0)
                        {
                            $jackpotCnt++;
                            $jackpotPos[] = $c * 5 + $r - 1;
                        }
                    }
                }

                if($jackpotCnt > 9)
                    continue;
                if($jackpotCnt > 2)
                {
                    $jackpotWin = $slotSettings->Paytable[$jackpot][$jackpotCnt] * $allbet;
                    $totalWin += $jackpotWin;
                    $scatterWin = '<ScatterWin winVal="'.($jackpotWin * 100).'" awardIndex="'.$slotSettings->awardIndices['12'][$jackpotCnt].'">'.implode('|', $jackpotPos).'</ScatterWin>';
                }

                if($scatterCount > 2 && $winType != 'bonus')
                    continue;

                if($minTotalWin == -1 || ($minTotalWin > $totalWin && $totalWin > 0))
                {
                    $minTotalWin = $totalWin;
                    $minLineWins = $lineWins;
                    $minFreespinsWon = $freespinsWon;
                    $minReels = $reels;
                }

                if($this->debug)
                {
                    $spinAcquired = true;
                    break;
                }                    
                if($totalWin <= $spinWinLimit && (($totalWin > 0 && $winType != 'none') || ($winType == 'bonus' && $scatterCount > 2)))
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
            if(!$spinAcquired && $totalWin > $gameWin && $winType != 'none')
            {                
                // if($postData['slotEvent'] == "freespin")
                {
                    $manualNoWin = true;
                    $reels = $minReels;
                    $lineWins = $minLineWins;
                    $totalWin = $minTotalWin;
                    $freespinsWon = $minFreespinsWon;                    
                    $scatterWin = '';
                }
                // else
                // {
                //     $manualNoWin = true;
                //     $reels = $slotSettings->GetNoWinSpin($reelName);
                //     $lineWins = [];
                //     $totalWin = $gameWin;                    
                //     $freespinsWon = 0;   
                // }
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

            $winSC = 0;
            
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
                $freespinsWon = 8;
                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                
                if($postData['slotEvent'] != 'freespin')
                {
                    $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', 'Freespin');
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $freespinsWon);
                    $spinFeature = rand(0, count($slotSettings->fsPos) - 1);
                    $slotSettings->SetGameData($slotSettings->slotId . 'SpinFeature', $spinFeature);
                    $freespinInfo = '<FSInfo fsWinnings="0" freeSpinsTotal="8" freeSpinNumber="0" extraSpinsAwarded="0" patternGroup="'.$slotSettings->fsPos[$spinFeature]['patternGroup'].'" patternIndex="'.$slotSettings->fsPos[$spinFeature]['patternIndex'].'" isMaxWin="0" />';                    

                    $block_indices = explode('|', $slotSettings->fsPos[$spinFeature]['skip']);
                    $freespin_blocks = [
                        'reel1' => [0,0,0,0,0,0],
                        'reel2' => [0,0,0,0,0,0],
                        'reel3' => [0,0,0,0,0,0],
                        'reel4' => [0,0,0,0,0,0],
                        'reel5' => [0,0,0,0,0,0],
                    ];
                    for($i = 0; $i < count($block_indices); $i++)
                    {
                        $r = $block_indices[$i] % 5;
                        $c = (int)($block_indices[$i] / 5);
                        $freespin_blocks['reel'.($r+1)][$c] = 1;
                    }
                    $slotSettings->SetGameData($slotSettings->slotId . 'FreespinBlocks', $freespin_blocks);
                    $slotSettings->SetGameData($slotSettings->slotId . 'Blocks', $golden_blocks);

                    $bgRecoveryInfo = '<BaseGameRecoveryInfo><ReelResults numSpins="1">
                            <ReelSpin spinIndex="0" reelsetIndex="0" winCountPL="'.count($lineWins).'" winCountSC="'.$winSC.'" spinWins="'.($totalWin * 100).'" freeSpin="N" bonusAwarded="N" manualNoWin="'.$manualNoWin.'">
                                <ReelStops>'.implode('|',$reels['rp']).'</ReelStops>
                                '.$paylines.$scatterWin.'
                            </ReelSpin>
                        </ReelResults></BaseGameRecoveryInfo>';
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
            $header = simplexml_load_string('<Header sessionID="yJfeC7tY+Tb0ft3INwSHqrpjrQJyMqScxIjNRQXPjvy7VdCpaLlft5VEFrB67aGxFluFacUmZejYWig/YJQfk++lR8iszDPfBUlQ5bFIWow=" ccyCode="en_US" deciSep="." thousandSep="," lang="en_US" gameID="20292" versionID="1_0" fullVersionID="1.0.8" isRecovering="N"/>');
            $accountData = simplexml_load_string('<AccountData><AccountData><CurrencyMultiplier>1</CurrencyMultiplier></AccountData></AccountData>');
            $balance = simplexml_load_string('<Balances><Balance name="CASH_BALANCE" value="'.($slotSettings->GetBalance() * 100).'"/></Balances>');

            $golden_blocks = $slotSettings->GetGameData($slotSettings->slotId . 'Blocks');
            $gameResult = simplexml_load_string('<GameResult stake="'.(string)$postData->Stake['total'].'" totalWin="'.($totalWin * 100).'" betID="">
                    <ReelResults numSpins="1">
                        <ReelSpin spinIndex="0" reelsetIndex="'.$reelSetIndex.'" anywayWins="'.count($lineWins).'" scatterWinCount="'.$winSC.'" totalSpinWin="'.($totalWin * 100).'" freeSpin="'.$isFreespin.'" bonusAwarded="'.$bonusAwarded.'" manualNoWin="'.$manualNoWin.'">
                            <ReelStops>'.implode('|',$reels['rp']).'</ReelStops>
                            '.$paylines.$scatterWin.'
                        </ReelSpin>
                    </ReelResults>
                    <BGInfo totalWagerWin="'.($totalWagerWin * 100).'" bgWinnings="'.($baseGameWin * 100).'" baseGameSpinsRemaining="0" isMaxWin="0"/>
                    <BGShapeInfo positionsToSkip="'.$this->getBlockedPositions($golden_blocks).'" />
                </GameResult>');            

            if($postData['slotEvent'] == 'freespin')
            {
                //set freespin info during freespin (not triggering freespin)
                $totalFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                $currentFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');                
                $freespinInfo = '<FSInfo fsWinnings="'.($freeSpinWin * 100).'" freeSpinsTotal="'.$totalFreespin.'" freeSpinNumber="'.$currentFreespin.'" />';

                $bgRecoveryInfo = $slotSettings->GetGameData($slotSettings->slotId . 'bgRecoveryInfo'); 
                $bi = simplexml_load_string($bgRecoveryInfo);
                $this->sxml_append($gameResult, $bi);

                $fsShape = simplexml_load_string('<FSShapeInfo positionsToSkip="'.$slotSettings->fsPos[$slotSettings->GetGameData($slotSettings->slotId . 'SpinFeature')]['skip'].'" />');
                $this->sxml_append($gameResult, $fsShape);
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


