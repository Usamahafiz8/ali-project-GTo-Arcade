<?php 
namespace VanguardLTE\Games\MichaelJackson
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
                                return '{"javascripts":["/games/MichaelJackson/d3bneyhc3uesob.cloudfront.net/5.0.0.13-1655212767/resource-service/mainjs/application/capabilitiesdetector.CapabilitiesDetectorBootstrapper.js?resourceversion=5.0.0.13-1655212767&appcode=capabilities-detector","/games/MichaelJackson/d3bneyhc3uesob.cloudfront.net/5.0.0.13-1655212767/resource-service/translationjs/bundles/translations/en_GB/capabilitiesdetector.LocalizationBundle.js?resourceversion=5.0.0.13-1655212767&appcode=capabilities-detector"],"jsons":["/games/MichaelJackson/d3bneyhc3uesob.cloudfront.net/5.0.0.13-1655212767/resource-service/translationjson/bundles/translations/#{locale}/capabilitiesdetector.LocalizationBundle.json?resourceversion=5.0.0.13-1655212767&appcode=capabilities-detector"],"main_class":"capabilitiesdetector.CapabilitiesDetectorBootstrapper","supported_locales":["it_IT","ru_RU","el_GR","pl_PL","ro_RO","tr_TR","fr_FR","cs_CZ","hu_HU","ca_ES","de_DE","sk_SK","es_ES","nl_NL","fr_CA","sv_SE","da_DK","bg_BG","hr_HR","fi_FI","en_GB","et_EE","lt_LT","lv_LV","sl_SI","no_NO","pt_PT"],"default_locale":"en"}';
                            break;
                            case "gls-platform":
                                return '{"javascripts":["/games/MichaelJackson/d3bneyhc3uesob.cloudfront.net/5.0.0.13-1655212767/resource-service/mainjs/application/glsplatform.GlsPlatform.js?resourceversion=5.0.0.13-1655212767&appcode=gls-platform","/games/MichaelJackson/lon-pt-mob.wi-gameserver.com/resource-service/metadatajs/bundles/metadata/glsplatform.MetaDataBundle.js?resourceversion=5.0.0.13-1655212767&appcode=gls-platform&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=mjkingofpop&locale=en_US&webaudio=true","/games/MichaelJackson/d3bneyhc3uesob.cloudfront.net/5.0.0.13-1655212767/resource-service/translationjs/bundles/translations/#{locale}/glsplatform.LocalizationsBundle.js?resourceversion=5.0.0.13-1655212767&appcode=gls-platform"],"jsons":["/games/MichaelJackson/d3bneyhc3uesob.cloudfront.net/5.0.0.13-1655212767/resource-service/metadatajson/bundles/metadata/glsplatform.MetaDataBundle.json?resourceversion=5.0.0.13-1655212767&appcode=gls-platform&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=mjkingofpop&locale=en_US&webaudio=true","/games/MichaelJackson/d3bneyhc3uesob.cloudfront.net/5.0.0.13-1655212767/resource-service/translationjson/bundles/translations/#{locale}/glsplatform.LocalizationsBundle.json?resourceversion=5.0.0.13-1655212767&appcode=gls-platform"],"main_class":"glsplatform.GlsPlatform","supported_locales":["it_IT","ru_RU","el_GR","pl_PL","ro_RO","tr_TR","fr_FR","cs_CZ","hu_HU","ca_ES","de_DE","sk_SK","es_ES","nl_NL","fr_CA","sv_SE","da_DK","bg_BG","hr_HR","fi_FI","en_GB","et_EE","lt_LT","lv_LV","sl_SI","no_NO","pt_PT"],"default_locale":"en"}';
                                break;
                            case "lean-regular-partner-adapter":
                                return '{"javascripts":["/games/MichaelJackson/d3bneyhc3uesob.cloudfront.net/5.0.0.13-1655212767/resource-service/mainjs/application/leanpartneradapter.LeanPartnerAdapter.js?resourceversion=5.0.0.13-1655212767&appcode=lean-regular-partner-adapter","/games/MichaelJackson/lon-pt-mob.wi-gameserver.com/resource-service/metadatajs/bundles/metadata/leanpartneradapter.MetaDataBundle.js?resourceversion=5.0.0.13-1655212767&appcode=lean-regular-partner-adapter&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=mjkingofpop&locale=en_US&webaudio=true","/games/MichaelJackson/d3bneyhc3uesob.cloudfront.net/5.0.0.13-1655212767/resource-service/translationjs/bundles/translations/#{locale}/leanpartneradapter.LocalizationsBundle.js?resourceversion=5.0.0.13-1655212767&appcode=lean-regular-partner-adapter"],"jsons":["/games/MichaelJackson/d3bneyhc3uesob.cloudfront.net/5.0.0.13-1655212767/resource-service/metadatajson/bundles/metadata/leanpartneradapter.MetaDataBundle.json?resourceversion=5.0.0.13-1655212767&appcode=lean-regular-partner-adapter&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=mjkingofpop&locale=en_US&webaudio=true","/games/MichaelJackson/d3bneyhc3uesob.cloudfront.net/5.0.0.13-1655212767/resource-service/translationjson/bundles/translations/#{locale}/leanpartneradapter.LocalizationsBundle.json?resourceversion=5.0.0.13-1655212767&appcode=lean-regular-partner-adapter"],"main_class":"leanpartneradapter.LeanPartnerAdapter","supported_locales":["it_IT","ru_RU","el_GR","pl_PL","ro_RO","tr_TR","fr_FR","cs_CZ","hu_HU","ca_ES","de_DE","sk_SK","es_ES","nl_NL","fr_CA","sv_SE","da_DK","bg_BG","hr_HR","fi_FI","en_GB","et_EE","lt_LT","lv_LV","sl_SI","no_NO","pt_PT"],"default_locale":"en"}';
                                break;
                            case "mjkingofpop":
                                return '{
                                    "default_locale": "en",
                                    "inject": [{
                                      "link": {
                                        "rel": "stylesheet",
                                        "href": "content/mjkingofpop/resources/css/game.css"
                                      }
                                    }],
                                    "javascripts": [
                                      "content/mjkingofpop/app/mjkingofpop.Game.js?resourceversion=5.0.0.13-1655212767&appcode=mjkingofpop&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=mjkingofpop&locale=en_US&webaudio=true",
                                      "content/mjkingofpop/bundles/metadata/mjkingofpop.MetaDataBundle.js?resourceversion=5.0.0.13-1655212767&appcode=mjkingofpop&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=mjkingofpop&locale=en_US&webaudio=true"
                                    ],
                                    "main_class": "mjkop.Game",
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
                        $filename = base_path() . '/app/Games/MichaelJackson/game.txt';
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
                            $slotSettings->SetBet($allbet);
                            $slotSettings->UpdateJackpots($allbet);
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
            $linesId = $slotSettings->GetPaylines();
            $freespinFeatureIndex = -1;
            $reelSetIndex = 0;
            if($postData['slotEvent'] == 'freespin')
            {                
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bonus');
            }
            else
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bet');
                $reelName = 'Reels0';                
            }

            $lines = count($linesId);
            $allbet = (int)((string)$postData->Stake['total']) * 0.01;                                 
            $betLine = $allbet / 0.4 * 0.01; //paytable is 0.4 based      

            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $allbet);
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];               
           
            $spinAcquired = false;             
            $gameWin = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin');

            $minReels = [];
            $minLineWins = [];
            $minTotalWin = -1;
            $minFreespinsWon = 0;
            $minFreespinType = -1;
            $minNewWildPos = [];
            $minWildInfo = '';

            $totalWin = 0;
            $freespinsWon = 0;
            $lineWins = [];
            $reels = [];
            
            $scatter = "11";
            $jackpot = '12';
            $wild = ["10"];
            $mysteryFeature = '';
            $freespinInfo = '';
            $scatterWin = '';            
            $scatterCount = 0;
            $wildInfo = '';
            $newWildPos = [];

            $oldWildPos = $slotSettings->GetGameData($slotSettings->slotId . 'OldWildPos');
            $wheelChoiceCnt = count($slotSettings->wheelChoices);
            for( $i = 0; $i <= 500; $i++ ) 
            {
                $mysteryFeature = '';
                $scatterWin = '';
                $freespinInfo = '';
                $totalWin = 0;
                $freespinsWon = 0;
                $lineWins = [];
                $cWins = array_fill(0, $lines, 0);
                $newWildPos = [];
                $wildInfo = '';
                
                if($this->debug && $postData['slotEvent'] != 'freespin')
                {                 
                    $winType = 'bonus';
                }

                if($postData['slotEvent'] == 'freespin')
                {
                    //0: credit win, 1: credit win with multiplier, 2: , 3: smooth criminal feature
                    $freespinFeatureIndex = $slotSettings->GetGameData($slotSettings->slotId . 'SpinFeature');
                    
                    if($freespinFeatureIndex == -1) //triggered freespin but not determined mode
                    {
                        $reelSetIndex = 1;
                        $featureIndex = 1;
                        $minFeatureIndex = -1;
                        $multiplier = 0;
                        $minMultiplier = 0;
                        $bonusTrackIndices = [1, 6, 10, 14];
                        $multipliers = [2,3,5,10];
                        for($j = 0; $j < 100; $j++)
                        {
                            shuffle($slotSettings->wheelChoices);
                            $freespinType = $slotSettings->wheelChoices[0];
                            $lineWins = [];
                            switch($slotSettings->wheelFeature[$freespinType])
                            {
                                case 'BeatIt':
                                    $featureIndex = 2;
                                    break;
                                case 'SmoothCriminal':
                                    $featureIndex = 3;
                                    break;
                                default:
                                    $featureIndex = 1;
                                    break;
                            }

                            $slotSettings->SetGameData($slotSettings->slotId . 'FreespinType', $freespinType);
                            if($featureIndex == 1)
                            {                                
                                $win = 0;
                                if(in_array($freespinType, $bonusTrackIndices))
                                {
                                    //this is bonus track, set multiplier
                                    $multiplier = $multipliers[rand(0, count($multipliers) - 1)];
                                    $winline = [0, $slotSettings->wheelFeature[$freespinType] * $multiplier, '0', $betLine * $slotSettings->wheelFeature[$freespinType] * $multiplier];
                                    $win = $betLine * $slotSettings->wheelFeature[$freespinType] * $multiplier;
                                }
                                else
                                {
                                    $multiplier = 0;
                                    $featureIndex = 0;
                                    $winline = [0, $slotSettings->wheelFeature[$freespinType], '0', $betLine * $slotSettings->wheelFeature[$freespinType]];
                                    $win = $betLine * $slotSettings->wheelFeature[$freespinType];
                                }
                                
                                $totalWin = $win;
                                $lineWins[] = $winline;
                                
                                if($win < $spinWinLimit)
                                {
                                    $spinAcquired = true;
                                    break;
                                }
                                if($minTotalWin == -1 || $minTotalWin > $win)
                                {
                                    $minTotalWin = $win;
                                    $minLineWins = $lineWins;
                                    $minFreespinType = $freespinType;
                                    $minFeatureIndex = $featureIndex;
                                    $minMultiplier = $multiplier;
                                }
                            }
                            else
                            {
                                //get smoothcriminal or beatit freespin
                                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                                if($featureIndex == 2)
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 10);
                                }
                                else if($featureIndex == 3)
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 5);
                                }
                                $spinAcquired = true;
                            }                            
                        }

                        if(!$spinAcquired)
                        {
                            $totalWin = $minTotalWin;
                            $lineWins = $minLineWins;
                            $freespinType = $minFreespinType;
                            $featureIndex = $minFeatureIndex;
                            $multiplier = $minMultiplier;
                        }

                        //freespin protocol : freespin win -> freespin determine -> $freespins
                        //in freespin determine step, break without spinning once determined
                        $slotSettings->SetGameData($slotSettings->slotId . 'SpinFeature', $featureIndex);
                        $spinAcquired = true;
                        $freespinInfo = '<FSInfo fsWinnings="0" freeSpinsTotal="0" freeSpinNumber="0" triggeredFeatureIndex="'.$featureIndex.'" multiplier="'.$multiplier.'" />';
                        $reels = ['rp' => [$freespinType, 0]];
                        break;
                    }
                    else
                    {
                        $reelName = 'Reels' . $freespinFeatureIndex;
                        $reelSetIndex = $freespinFeatureIndex;
                    }                    
                }

                $reels = $slotSettings->GetReelStrips($winType, $reelName, $postData['slotEvent']);
                $reels0 = $reels;
                if($postData['slotEvent'] == 'freespin')
                {
                    $totalFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                    $currentFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');                
                    
                    if($freespinFeatureIndex == 2) //BeatIt
                    {
                        for($r = 0; $r < 5; $r++)
                        {
                            for($c = 0; $c < 3; $c++)
                            {
                                if($reels['reel'.($r+1)][$c] == '10')
                                {
                                    $index = $c * 5 + $r;
                                    if(!in_array($index, $oldWildPos))
                                        $newWildPos[] = $index;
                                }
                            }
                        }
                        for($w = 0; $w < count($oldWildPos); $w++)
                        {
                            $r = $oldWildPos[$w] % 5;
                            $c = (int)($oldWildPos[$w] / 5);
                            $reels['reel'.($r+1)][$c] = '10';
                        }

                        $oi = '';
                        $ni = '';
                        if(count($oldWildPos) > 0)
                            $oi = '<OldWildPos>'.implode('|', $oldWildPos).'</OldWildPos>';
                        if(count($newWildPos) > 0)
                            $ni = '<NewWildPos>'.implode('|', $newWildPos).'</NewWildPos>';
                        $wildInfo = '<WildInfo>'.$oi.$ni.'</WildInfo>';
                    }
                    else  //SmoothCriminal
                    {
                        $currentFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                        switch($currentFreespin)
                        {
                            case 1:
                                $reels['reel5'][0] = $wild[0];
                                $reels['reel5'][1] = $wild[0];
                                $reels['reel5'][2] = $wild[0];
                                $wildInfo = '<WildInfo><WildReels>4</WildReels></WildInfo>';                                
                                break;
                            case 2:
                                $reels['reel5'][0] = $wild[0];
                                $reels['reel5'][1] = $wild[0];
                                $reels['reel5'][2] = $wild[0];
                                $reels['reel4'][0] = $wild[0];
                                $reels['reel4'][1] = $wild[0];
                                $reels['reel4'][2] = $wild[0];
                                $wildInfo = '<WildInfo><WildReels>3|4</WildReels></WildInfo>';
                                break;
                            case 3:
                                $reels['reel3'][0] = $wild[0];
                                $reels['reel3'][1] = $wild[0];
                                $reels['reel3'][2] = $wild[0];
                                $reels['reel4'][0] = $wild[0];
                                $reels['reel4'][1] = $wild[0];
                                $reels['reel4'][2] = $wild[0];
                                $wildInfo = '<WildInfo><WildReels>2|3</WildReels></WildInfo>';
                                break;
                            case 4:
                                $reels['reel3'][0] = $wild[0];
                                $reels['reel3'][1] = $wild[0];
                                $reels['reel3'][2] = $wild[0];
                                $reels['reel2'][0] = $wild[0];
                                $reels['reel2'][1] = $wild[0];
                                $reels['reel2'][2] = $wild[0];
                                $wildInfo = '<WildInfo><WildReels>1|2</WildReels></WildInfo>';
                                break;
                            case 5:
                                $reels['reel1'][0] = $wild[0];
                                $reels['reel1'][1] = $wild[0];
                                $reels['reel1'][2] = $wild[0];
                                $reels['reel2'][0] = $wild[0];
                                $reels['reel2'][1] = $wild[0];
                                $reels['reel2'][2] = $wild[0];
                                $wildInfo = '<WildInfo><WildReels>0|1</WildReels></WildInfo>';
                                break;
                        }
                    }
                }

                if(rand(0, 100) < 5 && $winType == 'win')
                {                    
                    if(rand(0, 100) < 50)
                    {
                        //moonwalk wild
                        $moonwalk = [];
                        $moonwalkIndices = [];
                        $cnt = rand(2, 5);
                        
                        while(count($moonwalk) < $cnt)
                        {
                            $r = rand(1,4);
                            $c = rand(0,2);
                            if(!in_array($r * 3 + $c, $moonwalk))
                                $moonwalk[] = $r * 3 + $c;
                        }

                        for($r = 0; $r < 5; $r++)
                        {
                            for($c = 0; $c < 3; $c++)
                            {
                                if(in_array($r * 3 + $c, $moonwalk))
                                {
                                    $reels['reel'.($r+1)][$c] = '10';
                                    $moonwalkIndices[] = $c * 5 + $r;
                                }
                            }
                        }
                        $mysteryFeature = '<MysteryInfo featureIdx="1"><wildPos>'.implode('|', $moonwalkIndices).'</wildPos></MysteryInfo>';
                    }
                    else
                    {
                        //stacked wild
                        $arr = [0,1,2,3,4];
                    
                        $wildReels = [];
                        $wildReels[] = $arr[rand(0, 4)];
                        while(count($wildReels) < 2)
                        {
                            $pick = rand(0,4);
                            if(!in_array($pick, $wildReels))
                                $wildReels[] = $pick;
                        }
                        for($r = 0; $r < 5; $r++)
                        {
                            if(in_array($r, $wildReels))
                            {
                                $reels['reel'.($r+1)][0] = '10';
                                $reels['reel'.($r+1)][1] = '10';
                                $reels['reel'.($r+1)][2] = '10';
                            }
                        }
                        $mysteryFeature = '<MysteryInfo featureIdx="2"><wildReels>'.implode('|', $wildReels).'</wildReels></MysteryInfo>';
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
                        if( $csym == $scatter || $csym == $jackpot || !isset($slotSettings->Paytable[$csym]) ) 
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
                                $coin = $slotSettings->Paytable[$csym][3] * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $cWins[$k] = $tmpWin;
                                    $emptyLine[0][$p0] = 1;
                                    $emptyLine[1][$p1] = 1;
                                    $emptyLine[2][$p2] = 1;
                                    $winline = [$k, $coin, $this->getConvertedLine($emptyLine), $tmpWin]; //[lineId, coinWon, winPositions]
                                }
                            }

                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) ) 
                            {
                                $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
                                $tmpWin = $slotSettings->Paytable[$csym][4] * $betLine * $mpl * $bonusMpl;
                                $coin = $slotSettings->Paytable[$csym][4] * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $cWins[$k] = $tmpWin;
                                    $emptyLine[0][$p0] = 1;
                                    $emptyLine[1][$p1] = 1;
                                    $emptyLine[2][$p2] = 1;
                                    $emptyLine[3][$p3] = 1;
                                    $winline = [$k, $coin, $this->getConvertedLine($emptyLine), $tmpWin]; //[lineId, coinWon, winPositions]                                                             
                                }
                            }
                            
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) ) 
                            {
                                $emptyLine = [[0,0,0],[0,0,0],[0,0,0],[0,0,0],[0,0,0]];
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
                                    $winline = [$k, $coin, $this->getConvertedLine($emptyLine), $tmpWin]; //[lineId, coinWon,winPositions]                                                            
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
                
                // $totalWin += $gameWin;

                //calc jackpot symbol
                $jackpotCnt = 0;                
                $jackpotPos = [];
                //calc freespin
                $scatterCount = 0;
                $scatterPos = [];
                
                for($r = 1; $r <= 5; $r++)
                {
                    for($c = 0; $c < 3; $c++)
                    {
                        if($reels0['reel'.$r][$c] == $scatter)
                        {
                            $scatterCount++;
                            $scatterPos[] = $c * 5 + $r - 1;
                        }
                        if($reels0['reel'.$r][$c] == $jackpot)
                        {
                            $jackpotCnt++;
                            $jackpotPos[] = $c * 5 + $r - 1;
                        }
                    }
                }

                if($jackpotCnt > 5)
                    continue;
                if($jackpotCnt > 1)
                {
                    $jackpotWin = $slotSettings->Paytable[$jackpot][$jackpotCnt] * $allbet;
                    $totalWin += $jackpotWin;
                    $scatterWin = '<ScatterWin winVal="'.($jackpotWin * 100).'" awardIndex="34">'.implode('|', $jackpotPos).'</ScatterWin>';
                }

                if($scatterCount > 2 && $winType != 'bonus')
                    continue;

                if($scatterCount > 2)
                {
                    $scatterWin = '<ScatterWin winVal="0" awardIndex="37">'.implode('|', $scatterPos).'</ScatterWin>';
                    $freespinInfo = '<FSInfo fsWinnings="0" triggeredFeatureIndex="1"/>';
                }

                if(($minTotalWin == -1 || $minTotalWin > $totalWin) && $totalWin > 0)
                {
                    $minTotalWin = $totalWin;
                    $minLineWins = $lineWins;
                    $minFreespinsWon = $freespinsWon;
                    $minReels = $reels;
                    $minNewWildPos = $newWildPos;
                    $minWildInfo = $wildInfo;
                }

                if($this->debug)
                {
                    $spinAcquired = true;
                    break;
                }                    

                if($totalWin <= $spinWinLimit && (($winType != 'none' && $totalWin > 0) || ($winType == 'bonus' && $scatterCount > 2)))
                {
                    $spinAcquired = true;
                    if($postData['slotEvent'] == 'bet' && $totalWin < 0.7 * $spinWinLimit && $winType != 'bonus')
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
                $manualNoWin = true;
                $reels = $minReels;
                $lineWins = $minLineWins;
                $totalWin = $minTotalWin;
                $freespinsWon = $minFreespinsWon;                    
                $mysteryFeature = '';
                $scatterWin = '';
                $newWildPos = $minNewWildPos;
                $wildInfo = $minWildInfo;
            }

            $coinWin = 0; //coins won
            $paylines = '';
            if(!empty($lineWins))
            {
                foreach($lineWins as $winline)
                {
                    $coinWin += $winline[1]; //sum up coins
                    $paylines .= '<PaylineWin index="'.$winline[0].'" winVal="'.($winline[3] * 100).'">'.$winline[2].'</PaylineWin>';
                }
            }

            $winSC = 0;
            if($scatterCount > 2)
                $winSC = 1;

            
            if($totalWin > 0)
            {
                $slotSettings->SetBank($postData['slotEvent'], -1 * $totalWin);
                $slotSettings->SetWin($totalWin);
                $slotSettings->SetBalance($totalWin);
            }

            $slotSettings->SetGameData($slotSettings->slotId . 'TotalGameWin', $slotSettings->GetGameData($slotSettings->slotId . 'TotalGameWin') + $totalWin);
            if($postData['slotEvent'] == 'freespin')
                $slotSettings->SetGameData($slotSettings->slotId . 'TotalFreespinWin', $slotSettings->GetGameData($slotSettings->slotId . 'TotalFreespinWin') + $totalWin);

            $totalWagerWin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalGameWin');
            $freeSpinWin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalFreespinWin');
            $baseGameWin = $totalWagerWin - $freeSpinWin;
            $bonusAwarded = $scatterCount > 2 ? 'Y':'N';            
            $isFreespin = $postData['slotEvent'] == 'freespin' ? 'Y':'N';            
            $sxe = new SimpleXMLElement('<GameResponse type="Logic"></GameResponse>');
            $header = simplexml_load_string('<Header sessionID="vkWv5HHtCBUMs3Gq1ogvjRBPthnKy49aqkvJ/CKj2EKCnk8ZnfY+aXVrGjaDSipAQKeZRMBeWY2+KgSXm60lcFLhX6a7iZfppr/nfhKgFY4=" ccyCode="GBP" deciSep="." thousandSep="," lang="en_US" gameID="20089" versionID="1_0" fullVersionID="1.0.3" isRecovering="N"/>');
            $accountData = simplexml_load_string('<AccountData><AccountData><CurrencyMultiplier>1</CurrencyMultiplier></AccountData></AccountData>');
            $balance = simplexml_load_string('<Balances><Balance name="CASH_BALANCE" value="'.($slotSettings->GetBalance() * 100).'"/></Balances>');
            $gameResult = simplexml_load_string('<GameResult stake="'.(string)$postData->Stake['total'].'" stakePerLine="5" paylineCount="25" totalWin="'.($totalWin * 100).'" betID="">
                    <ReelResults numSpins="1">
                        <ReelSpin spinIndex="0" reelsetIndex="'.$reelSetIndex.'" winCountPL="'.count($lineWins).'" winCountSC="'.$winSC.'" spinWins="'.($totalWin * 100).'" freeSpin="'.$isFreespin.'" bonusAwarded="'.$bonusAwarded.'" manualNoWin="'.$manualNoWin.'">
                            <ReelStops>'.implode('|',$reels['rp']).'</ReelStops>
                            '.$paylines.$scatterWin.'
                        </ReelSpin>
                    </ReelResults>
                    <BGInfo totalWagerWin="'.($totalWagerWin * 100).'" bgWinnings="'.($baseGameWin * 100).'" baseGameSpinsRemaining="0" isMaxWin="0"/>                    
                </GameResult>');
            if($mysteryFeature != '')
            {
                $mystery = simplexml_load_string($mysteryFeature);
                $this->sxml_append($gameResult, $mystery);
            }

            if($postData['slotEvent'] == 'freespin' && $freespinInfo == '')
            {
                //set freespin info during freespin (not triggering freespin)
                $totalFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                $currentFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                $freespinInfo = '<FSInfo fsWinnings="'.($freeSpinWin * 100).'" freeSpinsTotal="'.$totalFreespin.'" freeSpinNumber="'.$currentFreespin.'" />';

                if(count($newWildPos) > 0)
                {
                    for($w = 0; $w < count($newWildPos); $w++)
                        $oldWildPos[] = $newWildPos[$w];
                    $slotSettings->SetGameData($slotSettings->slotId . 'OldWildPos', $oldWildPos);
                }
            }
            if($freespinInfo != '')
            {
                $fs = simplexml_load_string($freespinInfo);
                $this->sxml_append($gameResult, $fs);
            }
            if($wildInfo != '')
            {
                $wi = simplexml_load_string($wildInfo);
                $this->sxml_append($gameResult, $wi);
            }

            if($postData['slotEvent'] == 'bet')
            {
                if($scatterWin != '')
                {
                    $bgRecoveryInfo = '<BaseGameRecoveryInfo><ReelResults numSpins="1">
                            <ReelSpin spinIndex="0" reelsetIndex="0" winCountPL="'.count($lineWins).'" winCountSC="'.$winSC.'" spinWins="'.($totalWin * 100).'" freeSpin="N" bonusAwarded="N" manualNoWin="'.$manualNoWin.'">
                                <ReelStops>'.implode('|',$reels['rp']).'</ReelStops>
                                '.$paylines.$scatterWin.'
                            </ReelSpin>
                        </ReelResults></BaseGameRecoveryInfo>';
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


