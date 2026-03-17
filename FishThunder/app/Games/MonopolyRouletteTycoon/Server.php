<?php 
namespace VanguardLTE\Games\MonopolyRouletteTycoon
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

        function getBetPositions($name, $slotSettings)
        {
            $pos = '';            
            foreach($slotSettings->Paytable as $key => $value)
            {
                if(strpos($name, $key) !== false)
                {
                    $pos = str_replace($key . '_', '', $name);
                    $pos = explode('_', $pos);
                    if($key == 'OUTSIDE_LOW')
                    {
                        return [[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18], $value];
                    }
                    else if($key == 'OUTSIDE_HIGH')
                    {
                        return [[19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36], $value];
                    }
                    else if($key == 'OUTSIDE_EVEN')
                    {
                        return [[2,4,6,8,10,12,14,16,18,20,22,24,26,28,30,32,34,36], $value];
                    }
                    else if($key == 'OUTSIDE_ODD')
                    {
                        return [[1,3,5,7,9,11,13,15,17,19,21,23,25,27,29,31,33,35], $value];
                    }
                    else if($key == 'OUTSIDE_RED')
                    {
                        return [[1,3,5,7,9,12,14,16,18,19,21,23,25,27,30,32,34,36], $value];
                    }
                    else if($key == 'OUTSIDE_BLACK')
                    {
                        return [[2,4,6,8,10,11,13,15,17,20,22,24,26,28,29,31,33,35], $value];
                    }
                    else if($key == 'PICK')
                    {
                        return [[], $value];
                    }
                    else if($key == 'SPLIT' || $key == 'STREET' || $key == 'CORNER')
                    {
                        return [$pos, $value];
                    }
                    else if($key == 'SIX' || $key == 'DOZEN')
                    {
                        $res = [];
                        for($i = $pos[0]; $i <= $pos[1]; $i++)
                            $res[] = $i;
                        return [$res, $value];
                    }
                    else if($name == 'COLUMN_1_34')
                    {
                        return [[1,4,7,10,13,16,19,22,25,28,31,34], $value];
                    }
                    else if($name == 'COLUMN_2_35')
                    {
                        return [[2,5,8,11,14,17,20,23,26,29,32,35], $value];
                    }
                    else if($name == 'COLUMN_3_36')
                    {
                        return [[3,6,9,12,15,18,21,24,27,30,33,36], $value];
                    }
                    break;
                }
            }            
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
                                return '{"javascripts":["/games/MonopolyRouletteTycoon/d1ay6055gq02dh.cloudfront.net/5.0.0.14-1654689740/resource-service/mainjs/application/capabilitiesdetector.CapabilitiesDetectorBootstrapper.js?resourceversion=5.0.0.14-1654689740&appcode=capabilities-detector","/games/MonopolyRouletteTycoon/d1ay6055gq02dh.cloudfront.net/5.0.0.14-1654689740/resource-service/translationjs/bundles/translations/#{locale}/capabilitiesdetector.LocalizationBundle.js?resourceversion=5.0.0.14-1654689740&appcode=capabilities-detector"],"jsons":["/games/MonopolyRouletteTycoon/d1ay6055gq02dh.cloudfront.net/5.0.0.14-1654689740/resource-service/translationjson/bundles/translations/#{locale}/capabilitiesdetector.LocalizationBundle.json?resourceversion=5.0.0.14-1654689740&appcode=capabilities-detector"],"main_class":"capabilitiesdetector.CapabilitiesDetectorBootstrapper","supported_locales":["it_IT","ru_RU","el_GR","pl_PL","ro_RO","tr_TR","fr_FR","cs_CZ","hu_HU","ca_ES","de_DE","sk_SK","es_ES","nl_NL","fr_CA","sv_SE","da_DK","bg_BG","hr_HR","fi_FI","en_GB","et_EE","lt_LT","lv_LV","sl_SI","no_NO","pt_PT"],"default_locale":"en"}';
                            break;
                            case "gls-platform":
                                return '{"javascripts":["/games/MonopolyRouletteTycoon/d1ay6055gq02dh.cloudfront.net/5.0.0.14-1654689740/resource-service/mainjs/application/glsplatform.GlsPlatform.js?resourceversion=5.0.0.14-1654689740&appcode=gls-platform","/games/MonopolyRouletteTycoon/dnk-resource.wimobile.casinarena.com/resource-service/metadatajs/bundles/metadata/glsplatform.MetaDataBundle.js?resourceversion=5.0.0.14-1654689740&appcode=gls-platform&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=monopolyroulette&locale=en_US&webaudio=true","/games/MonopolyRouletteTycoon/d1ay6055gq02dh.cloudfront.net/5.0.0.14-1654689740/resource-service/translationjs/bundles/translations/#{locale}/glsplatform.LocalizationsBundle.js?resourceversion=5.0.0.14-1654689740&appcode=gls-platform"],"jsons":["/games/MonopolyRouletteTycoon/d1ay6055gq02dh.cloudfront.net/5.0.0.14-1654689740/resource-service/metadatajson/bundles/metadata/glsplatform.MetaDataBundle.json?resourceversion=5.0.0.14-1654689740&appcode=gls-platform&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=monopolyroulette&locale=en_US&webaudio=true","/games/MonopolyRouletteTycoon/d1ay6055gq02dh.cloudfront.net/5.0.0.14-1654689740/resource-service/translationjson/bundles/translations/#{locale}/glsplatform.LocalizationsBundle.json?resourceversion=5.0.0.14-1654689740&appcode=gls-platform"],"main_class":"glsplatform.GlsPlatform","supported_locales":["it_IT","ru_RU","el_GR","pl_PL","ro_RO","tr_TR","fr_FR","cs_CZ","hu_HU","ca_ES","de_DE","sk_SK","es_ES","nl_NL","fr_CA","sv_SE","da_DK","bg_BG","hr_HR","fi_FI","en_GB","et_EE","lt_LT","lv_LV","sl_SI","no_NO","pt_PT"],"default_locale":"en"}';
                                break;
                            case "lean-regular-partner-adapter":
                                return '{"javascripts":["/games/MonopolyRouletteTycoon/d1ay6055gq02dh.cloudfront.net/5.0.0.14-1654689740/resource-service/mainjs/application/leanpartneradapter.LeanPartnerAdapter.js?resourceversion=5.0.0.14-1654689740&appcode=lean-regular-partner-adapter","/games/MonopolyRouletteTycoon/dnk-resource.wimobile.casinarena.com/resource-service/metadatajs/bundles/metadata/leanpartneradapter.MetaDataBundle.js?resourceversion=5.0.0.14-1654689740&appcode=lean-regular-partner-adapter&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=monopolyroulette&locale=en_US&webaudio=true","/games/MonopolyRouletteTycoon/d1ay6055gq02dh.cloudfront.net/5.0.0.14-1654689740/resource-service/translationjs/bundles/translations/#{locale}/leanpartneradapter.LocalizationsBundle.js?resourceversion=5.0.0.14-1654689740&appcode=lean-regular-partner-adapter"],"jsons":["/games/MonopolyRouletteTycoon/d1ay6055gq02dh.cloudfront.net/5.0.0.14-1654689740/resource-service/metadatajson/bundles/metadata/leanpartneradapter.MetaDataBundle.json?resourceversion=5.0.0.14-1654689740&appcode=lean-regular-partner-adapter&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=monopolyroulette&locale=en_US&webaudio=true","/games/MonopolyRouletteTycoon/d1ay6055gq02dh.cloudfront.net/5.0.0.14-1654689740/resource-service/translationjson/bundles/translations/#{locale}/leanpartneradapter.LocalizationsBundle.json?resourceversion=5.0.0.14-1654689740&appcode=lean-regular-partner-adapter"],"main_class":"leanpartneradapter.LeanPartnerAdapter","supported_locales":["it_IT","ru_RU","el_GR","pl_PL","ro_RO","tr_TR","fr_FR","cs_CZ","hu_HU","ca_ES","de_DE","sk_SK","es_ES","nl_NL","fr_CA","sv_SE","da_DK","bg_BG","hr_HR","fi_FI","en_GB","et_EE","lt_LT","lv_LV","sl_SI","no_NO","pt_PT"],"default_locale":"en"}';
                                break;
                            case "monopolyroulette":
                                return '{
                                    "default_locale": "en",
                                    "inject": [
                                        {
                                            "script": {
                                                "data-main": "content/monopolyroulette/lib/require/require_cfg.js?resourceversion=5.0.0.14-1654689740&appcode=monopolyroulette&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=monopolyroulette&locale=en_US&webaudio=true",
                                                "src": "content/monopolyroulette/lib/require/require.js",
                                                "type": "text/javascript"
                                            }
                                        },
                                        {
                                            "link": {
                                                "rel": "stylesheet",
                                                "href": "content/monopolyroulette/resources/css/game.css"
                                            }
                                        },
                                        {
                                            "link": {
                                                "rel": "stylesheet",
                                                "href": "content/monopolyroulette/resources/css/help.css"
                                            }
                                        }
                                    ],
                                    "javascripts": [
                                        "content/monopolyroulette/app/monopolyroulette.Game.js?resourceversion=5.0.0.14-1654689740&appcode=monopolyroulette&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=monopolyroulette&locale=en_US&webaudio=true",
                                        "content/monopolyroulette/bundles/metadata/monopolyroulette.MetaDataBundle.js?resourceversion=5.0.0.14-1654689740&appcode=monopolyroulette&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=mockpartner&realmoney=false&gamecode=monopolyroulette&locale=en_US&webaudio=true"
                                    ],
                                    "main_class": "monopolyroulette.Game",
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
                                    }
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
                        $filename = base_path() . '/app/Games/MonopolyRouletteTycoon/game.txt';
                        $file = fopen($filename, "r" );
                        $filesize = filesize( $filename );
                        $response = fread( $file, $filesize );
                        $response = str_replace('BAL_REPLACE', $slotSettings->GetBalance() * 100, $response);
                        fclose( $file );
                        $slotSettings->SetGameData($slotSettings->slotId . 'AllBet', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'PowerSpin', -1);
                        $slotSettings->SetGameData($slotSettings->slotId . 'BaseWinning', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'TotalWinning', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'RollsWinning', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'History', []);
                        $slotSettings->SetGameData($slotSettings->slotId . 'MonopolyBetPosition', []);
                        $slotSettings->SetGameData($slotSettings->slotId . 'MonopolyWageredProps', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'MonopolyTotalBet', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'MonopolyRollsRemaining', -1);
                        $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', '');
                        $slotSettings->SetGameData($slotSettings->slotId . 'MonopolyPosition', 0);
                        break;                        
                    case 'Logic':                        
                        $playState = $postData->Play;
                        $allbet = (int)$playState['stake'] * 0.01;
                        $betState = $playState->BetState;
                        $powerSpin = $slotSettings->GetGameData($slotSettings->slotId . 'PowerSpin');
                        $spinStatus = $slotSettings->GetGameData($slotSettings->slotId . 'SpinStatus');
                        $monopolyFeaturePositions = ['GO', 'INCOME TAX', 'COMMUNITY CHEST', 'CHANCE', 'JUST VISITING JAIL', 'FREE PARKING', 'GO TO JAIL', 'LUXURY TAX'];
                        $monopolySteps = [
                            'GO',//
                            'MEDITERRANEAN AVENUE',
                            'COMMUNITY CHEST',//
                            'BALTIC AVENUE',  
                            'INCOME TAX', //
                            'READING RAILROAD',
                            'ORIENTAL AVENUE',
                            'CHANCE',//
                            'VERMONT AVENUE',
                            'CONNECTICUT AVENUE',
                            'JUST VISITING JAIL', //
                            'ST. CHARLES PLACE',
                            'ELECTRIC COMPANY',
                            'STATES AVENUE',
                            'VIRGINIA AVENUE',
                            'PENNSYLVANIA RAILROAD',
                            'ST. JAMES PLACE',
                            'COMMUNITY CHEST', //
                            'TENNESSEE AVENUE',
                            'NEW YORK AVENUE',
                            'FREE PARKING', //
                            'KENTUCKY AVENUE',
                            'CHANCE', //
                            'INDIANA AVENUE',
                            'ILLINOIS AVENUE',
                            'B. & O. RAILROAD',
                            'ATLANTIC AVENUE',
                            'VENTNOR AVENUE',
                            'WATER WORKS',
                            'MARVIN GARDENS',
                            'GO TO JAIL', //
                            'PACIFIC AVENUE',
                            'NORTH CAROLINA AVENUE',
                            'COMMUNITY CHEST',
                            'PENNSYLVANIA AVENUE',
                            'SHORT LINE',
                            'CHANCE', //
                            'PARK PLACE',
                            'LUXURY TAX', //
                            'BOARDWALK'
                        ];

                        if($spinStatus == 'MonopolyFreespin')
                            $postData['slotEvent'] = 'freespin';
                        $isMonopolyTriggered = false;
                        $history = $slotSettings->GetGameData($slotSettings->slotId . 'History');
                        $dice0 = 0;
                        $dice1 = 0;
                        $bonusDataStr = '';
                        $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $allbet);
                        $winType = $winTypeTmp[0];
                        $winLimit = $winTypeTmp[1];
                        if($postData['slotEvent'] != 'freespin')
                        {
                            if(isset($playState->MonopolyBetState))
                            {
                                $monopolyBetState = $playState->MonopolyBetState;                            
                                $powerSpin = 4;
                                $betPerSpace = (string)$monopolyBetState['betPerSpace'];
                                $monopolyBetPosition = explode('|', $betPerSpace);
                                $slotSettings->SetGameData($slotSettings->slotId . 'MonopolyBetPosition', $monopolyBetPosition);
                                $monopolyTotalBet = (int)$monopolyBetState['totalBet'];
                                $numWageredProps = (int)$monopolyBetState['numWageredProps'];

                                $slotSettings->SetGameData($slotSettings->slotId . 'BaseWinning', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'MonopolyWageredProps', $numWageredProps);
                                $slotSettings->SetGameData($slotSettings->slotId . 'MonopolyTotalBet', $monopolyTotalBet);
                            }

                            $monopolyBetPosition = [];
                            if($powerSpin >= 0)
                            {
                                $monopolyBetPosition = $slotSettings->GetGameData($slotSettings->slotId . 'MonopolyBetPosition');
                                $powerSpin--;
                                $slotSettings->SetGameData($slotSettings->slotId . 'PowerSpin', $powerSpin);
                            }

                            $num_bets = (int)$betState['num_bets'];
                            $input_bets = [];
                            foreach($betState->Bet as $bet)
                            {
                                $name = (string)$bet['name'];
                                $stake = (int)$bet['stake'];
                                $pos = $this->getBetPositions($name, $slotSettings);
                                if(count($pos[0]) == 0)
                                {
                                    //this is PICK, check seln value
                                    $pos[0][] = (int)$bet['seln'];
                                }
                                $input_bets[] = ['name' => $name, 'stake' => $stake, 'sel' => $pos[0], 'multiplier' => $pos[1], 'winnings' => 0];
                            }
                            
                            $totalWin = 0;

                            if($allbet > 0)
                            {
                                $slotSettings->SetBalance(-1 * $allbet, $postData['slotEvent']);
                                $bankSum = $allbet / 100 * $slotSettings->GetPercent();
                                $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $bankSum, $postData['slotEvent']);
                                $slotSettings->UpdateJackpots($allbet);
                                $slotSettings->SetBet($allbet);
                            }

                            $ball = 0;
                            $red = [1,3,5,7,9,12,14,16,18,19,21,23,25,27,30,32,34,36];
                            $bets = $input_bets;

                            $minBets = [];
                            $minTotalWin = -1;
                            $minBall = 0;
                            $spinAcquired = false;
                            for($i = 0; $i < 500; $i++)
                            {
                                $spinAcquired = false;
                                $ball = rand(0, 36);
                                $totalWin = 0;
                                $bets = $input_bets;
                                foreach($bets as &$bet)
                                {
                                    $sel = $bet['sel'];
                                    if(in_array($ball, $sel))
                                    {
                                        $stake = $bet['stake'];
                                        $multiplier = $bet['multiplier'];
                                        $totalWin += $stake * 0.01 * $multiplier;
                                        $bet['winnings'] = $stake * $multiplier;
                                    }
                                }

                                if($minTotalWin == -1 || $minTotalWin > $totalWin)
                                {
                                    $minTotalWin = $totalWin;
                                    $minBets = $bets;
                                    $minBall = $ball;
                                }

                                if($totalWin <= $winLimit)
                                {
                                    $spinAcquired = false;
                                    break;
                                }                                    
                            }

                            if(!$spinAcquired)
                            {
                                $totalWin = $minTotalWin;
                                $bets = $minBets;
                                $ball = $minBall;
                            }

                            if($totalWin > 0)
                            {
                                $slotSettings->SetBank('', -1 * $totalWin);
                                $slotSettings->SetBalance($totalWin);
                                $slotSettings->SetWin($totalWin);
                            }

                            $history[] = $ball;                        
                            if(count($history) > 45)
                                array_shift($history);

                            //check if monopoly bonus can be triggered
                            if($ball == 0)
                            {
                                $slotSettings->SetGameData($slotSettings->slotId . 'MonopolyBetPosition', []);
                                $slotSettings->SetGameData($slotSettings->slotId . 'MonopolyWageredProps', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'MonopolyTotalBet', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'MonopolyRollsRemaining', -1);
                                $slotSettings->SetGameData($slotSettings->slotId . 'PowerSpin', -1);
                                $powerSpin = -1;
                            }
                            else if($powerSpin >= 0 && in_array($ball, $red))
                            {
                                $slotSettings->SetGameData($slotSettings->slotId . 'PowerSpin', -1);
                                $slotSettings->SetGameData($slotSettings->slotId . 'MonopolyRollsRemaining', 3);
                                $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', 'MonopolyFreespin');
                                
                                if($powerSpin == 3)
                                    $slotSettings->SetGameData($slotSettings->slotId . 'MonopolyMultiplier', 1);
                                else if($powerSpin == 2)
                                    $slotSettings->SetGameData($slotSettings->slotId . 'MonopolyMultiplier', 2);
                                else if($powerSpin == 1)
                                    $slotSettings->SetGameData($slotSettings->slotId . 'MonopolyMultiplier', 3);
                                else if($powerSpin == 0)
                                    $slotSettings->SetGameData($slotSettings->slotId . 'MonopolyMultiplier', 5);   
                                $slotSettings->SetGameData($slotSettings->slotId . 'LapMultiplier', 1);   
                                $slotSettings->SetGameData($slotSettings->slotId . 'GoTime', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'GetOutOfJail', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeGame', 0);
                                $isMonopolyTriggered = true;

                                $slotSettings->SetGameData($slotSettings->slotId . 'MonopolyPosition', 0);
                                $bonusDataStr = '<BoardBonusData>
                                            <RollResult dice1="0" dice2="0" destIndex="0" prevIndex="0" currentSubBonusGameId="0" win="'.($totalWin * 100).'" NumbOfBoardBonusPlayed="0"  isGoCrossed="0" IsGetOutOfJailCardAvailable="0" IsCCBonus="0" IsChanceBonus="0" previousBoardCount="0" />
                                            <SubBonusData></SubBonusData>
                                        </BoardBonusData>';
                            }

                            $slotSettings->SetGameData($slotSettings->slotId . 'BaseWinning', $totalWin);
                            $slotSettings->SetGameData($slotSettings->slotId . 'TotalWinning', $totalWin);
                        }
                        else
                        {
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'FreeGame') + 1);
                            $monopolyPosition = $slotSettings->GetGameData($slotSettings->slotId . 'MonopolyPosition');
                            $monopolyMultiplier = $slotSettings->GetGameData($slotSettings->slotId . 'MonopolyMultiplier');                            
                            $monopolyBetPosition = $slotSettings->GetGameData($slotSettings->slotId . 'MonopolyBetPosition');
                            $lapMultiplier = $slotSettings->GetGameData($slotSettings->slotId . 'LapMultiplier');
                            $slotSettings->SetGameData($slotSettings->slotId . 'MonopolyRollsRemaining', $slotSettings->GetGameData($slotSettings->slotId . 'MonopolyRollsRemaining') - 1);
                            $totalWin = 0;        
                            $propertyMultiplier = 0;
                            $spinAcquired = false;
                            $propertyData = '';
                            $ccData = '';
                            $chanceData = '';
                            $destPosition = 0;
                            $getOutOfJail = $slotSettings->GetGameData($slotSettings->slotId . 'GetOutOfJail');

                            $minDice0 = 0;
                            $minDice1 = 0;
                            $minTotalWin = -1;
                            $minNewPosition = -1;
                            $minpropertyData = '';
                            $minccData = '';
                            $minchanceData = '';
                            $minGetOutOfJail = -1;
                            $minDestPosition = 0;

                            $communityOptions = ['1' => 'incometax','2'=>'consultancyfee','3'=>'saleofstock', '4'=>'secondprize', '6'=>'getoutofjail', '7'=>'gotojail'];
                            $chanceOptions = ['0' => 'advance', '1' => 'advance', '2'=>'advance', '3'=> 'goback', '4'=> 'advance', '5'=>'getoutofjail', '6'=>'gotojail'];

                            $monopolyTotalBet = $slotSettings->GetGameData($slotSettings->slotId . 'MonopolyTotalBet');
                            for($i = 0; $i < 500; $i++)
                            {
                                $getOutOfJail = $slotSettings->GetGameData($slotSettings->slotId . 'GetOutOfJail');
                                $dice0 = rand(1, 6);
                                $dice1 = rand(1, 6);
                                // $dice0 = 3;
                                // $dice1 = 4;
                                $propertyMultiplier = 0;
                                $newPosition = ($monopolyPosition + $dice0 + $dice1) % count($monopolySteps);
                                $destPosition = $newPosition;
                                if(in_array($monopolySteps[$newPosition], $monopolyFeaturePositions))
                                {
                                    $feature = $monopolySteps[$newPosition];
                                    if($feature == 'COMMUNITY CHEST')
                                    {
                                        $options = array_keys($communityOptions);
                                        $option = $options[rand(0, count($options) - 1)];
                                        switch($option)
                                        {
                                            case '1': //income tax
                                                $totalWin = $monopolyTotalBet * 0.01;
                                                $ccData = '<CCData CCWinnings="'.$monopolyTotalBet.'" CCSteps="0" CCSubBonusIndex="1" />';
                                                break;
                                            case '2': //consultancy fee
                                                $totalWin = $monopolyTotalBet * 2 * 0.01;
                                                $ccData = '<CCData CCWinnings="'.($monopolyTotalBet * 2).'" CCSteps="0" CCSubBonusIndex="2" />';
                                                break;
                                            case '3': ///sale of stock
                                                $totalWin = $monopolyTotalBet * 2 * 0.01;
                                                $ccData = '<CCData CCWinnings="'.($monopolyTotalBet * 2).'" CCSteps="0" CCSubBonusIndex="3" />';
                                                break;
                                            case '4': //second prize
                                                $totalWin = $monopolyTotalBet * 0.01;
                                                $ccData = '<CCData CCWinnings="'.($monopolyTotalBet).'" CCSteps="0" CCSubBonusIndex="4" />';
                                                break;
                                            case '6': //get out of jail
                                                $getOutOfJail++;
                                                $ccData = '<CCData CCWinnings="0" CCSteps="0" CCSubBonusIndex="6" />';
                                                break;
                                            case '7': //go to jail
                                                if($getOutOfJail > 0)
                                                {
                                                    $slotSettings->SetGameData($slotSettings->slotId . 'GetOutOfJail', 0);
                                                    $step = 10 - $newPosition;
                                                    if($step < 0)
                                                        $step = count($monopolySteps) + $step;
                                                    
                                                    $ccData = '<CCData CCWinnings="0" CCSteps="'.$step.'" CCSubBonusIndex="7" />';
                                                }
                                                else
                                                {
                                                    $slotSettings->SetGameData($slotSettings->slotId . 'MonopolyRollsRemaining', 0);
                                                    $ccData = '<CCData CCWinnings="0" CCSteps="0" CCSubBonusIndex="7" />';
                                                }
                                                
                                                break;
                                        }
                                    }
                                    else if($feature == 'CHANCE')
                                    {
                                        $options = array_keys($chanceOptions);
                                        $option = $options[rand(0, count($options) - 1)];
                                        switch($option)
                                        {
                                            case '0': //advance to GO, 0
                                            case '1': //advance to BoardWalk, 39
                                            case '2': //advance to St Charles 11
                                            case '4': //advance to ILLINOIS AVENUE 24                                            
                                                if($option == '0')
                                                    $jumpPosition = 0;
                                                else if($option == '1')
                                                    $jumpPosition = 39;
                                                else if($option == '2')
                                                    $jumpPosition = 11;
                                                else if($option == '4')
                                                    $jumpPosition = 24;
                                                $step = $jumpPosition - $newPosition;
                                                if($step < 0)
                                                    $step = count($monopolySteps) + $step;
                                                
                                                $newPosition = $jumpPosition;
                                                $chanceData = '<ChanceData ChanceWinnings="0" ChanceSteps="'.$step.'" ChanceSubBonusIndex="'.$option.'" />';
                                                break;
                                            case '3': //goback:
                                                $chanceData = '<ChanceData ChanceWinnings="0" ChanceSteps="-3" ChanceSubBonusIndex="3" />';
                                                $newPosition -= 3;                                                
                                                break;
                                           
                                            case '5': //getoutofjail:
                                                $chanceData = '<ChanceData ChanceWinnings="0" ChanceSteps="0" ChanceSubBonusIndex="5" />';
                                                $getOutOfJail++;
                                                break;
                                            case '6': //gotojail
                                                if($getOutOfJail > 0)
                                                {
                                                    $slotSettings->SetGameData($slotSettings->slotId . 'GetOutOfJail', 0);
                                                    $step = 10 - $newPosition;
                                                    if($step < 0)
                                                        $step = count($monopolySteps) + $step;
                                                    $chanceData = '<ChanceData ChanceWinnings="0" ChanceSteps="'.$step.'" ChanceSubBonusIndex="6" />';
                                                }
                                                else
                                                {
                                                    $slotSettings->SetGameData($slotSettings->slotId . 'MonopolyRollsRemaining', 0);
                                                    $chanceData = '<ChanceData ChanceWinnings="0" ChanceSteps="'.(-$dice0 - $dice1).'" ChanceSubBonusIndex="6" />';
                                                }
                                                break;
                                        }
                                    }
                                    $feature = $monopolySteps[$newPosition];
                                    switch($feature)
                                    {                                        
                                        case 'GO':                                            
                                            break;                                        
                                        case 'INCOME TAX':                  
                                            if($slotSettings->GetGameData($slotSettings->slotId . 'FreeGame') == 1)
                                                $totalWin = $monopolyTotalBet * 0.01;
                                            else
                                                $slotSettings->SetGameData($slotSettings->slotId . 'MonopolyRollsRemaining', 0);    
                                            break;
                                        case 'JUST VISITING JAIL':
                                        case 'FREE PARKING':
                                            break;
                                        case 'GO TO JAIL':
                                        case 'LUXURY TAX':
                                            $slotSettings->SetGameData($slotSettings->slotId . 'MonopolyRollsRemaining', 0);
                                            break;
                                    }
                                }
                                
                                if($monopolyBetPosition[$newPosition] > 0)
                                {
                                    $name = $monopolySteps[$newPosition];
                                    $propertyMultiplier = $slotSettings->monopolyTable[$name];
                                    $totalWin = $monopolyBetPosition[$newPosition] * $propertyMultiplier * $monopolyMultiplier * $lapMultiplier * 0.01;

                                    $propertyData = '<PropertyData PropertyWinnings="'.($totalWin * 100).'" PropertyMultiplier="'.($propertyMultiplier*100).'" LapMultiplier="'.$lapMultiplier.'" RouletteMultiplier="'.$monopolyMultiplier.'" />';
                                }                                

                                if($minTotalWin == -1 || ($minTotalWin > $totalWin && $totalWin > 0))
                                {
                                    $minTotalWin = $totalWin;
                                    $minDice0 = $dice0;
                                    $minDice1 = $dice1;
                                    $minNewPosition = $newPosition;
                                    $minDestPosition = $destPosition;
                                    $minpropertyData = $propertyData;
                                    $minccData = $ccData;
                                    $minGetOutOfJail = $getOutOfJail;
                                    $minchanceData = $chanceData;
                                }

                                if($this->debug)
                                {
                                    $spinAcquired = true;
                                    break;
                                }   
                                
                                if($totalWin <= $winLimit && (($totalWin > 0 && $winType != 'none')))
                                {
                                    $spinAcquired = true;
                                    if($totalWin < 0.5 * $winLimit && $winType != 'bonus')
                                        $spinAcquired = false;
                                    if($spinAcquired)
                                        break;                                        
                                }  
                                else if( $winType == 'none' && $totalWin == 0) 
                                {
                                    $spinAcquired = true;
                                    break;
                                }
                            }

                            if(!$spinAcquired)
                            {
                                $totalWin = $minTotalWin;
                                $dice0 = $minDice0;
                                $dice1 = $minDice1;
                                $newPosition = $minNewPosition;
                                $propertyData = $minpropertyData;
                                $ccData = $minccData;
                                $getOutOfJail = $minGetOutOfJail;
                                $chanceData = $minchanceData;
                                $destPosition = $minDestPosition;
                            }

                            $slotSettings->SetGameData($slotSettings->slotId . 'GetOutOfJail', $getOutOfJail);
                            $isGoCrossed = 0;
                            if($newPosition < $monopolyPosition || $newPosition == 0) //check if passed GO
                            {
                                $isGoCrossed = 1;
                                $slotSettings->SetGameData($slotSettings->slotId . 'MonopolyRollsRemaining', $slotSettings->GetGameData($slotSettings->slotId . 'MonopolyRollsRemaining') + 3);
                                $lapMultiplier++;
                                $slotSettings->SetGameData($slotSettings->slotId . 'LapMultiplier', $lapMultiplier);
                                $slotSettings->SetGameData($slotSettings->slotId . 'GoTime', $slotSettings->GetGameData($slotSettings->slotId . 'GoTime') + 1);
                                if($slotSettings->GetGameData($slotSettings->slotId . 'GoTime') >= 4)
                                    $slotSettings->SetGameData($slotSettings->slotId . 'MonopolyRollsRemaining', 0);
                            }

                            $slotSettings->SetGameData($slotSettings->slotId . 'MonopolyPosition', $newPosition);

                            if($totalWin > 0)
                            {
                                $slotSettings->SetGameData($slotSettings->slotId . 'TotalWinning', $slotSettings->GetGameData($slotSettings->slotId . 'TotalWinning') + $totalWin);
                                $slotSettings->SetGameData($slotSettings->slotId . 'RollsWinning', $slotSettings->GetGameData($slotSettings->slotId . 'RollsWinning') + $totalWin);

                                $slotSettings->SetBank('', -1 * $totalWin);
                                $slotSettings->SetBalance($totalWin); 
                                $slotSettings->SetWin($totalWin);
                            }

                            $isCCBonus = $ccData == '' ? 0 : 1;
                            $isChanceBonus = $chanceData == '' ? 0 : 1;
                            $bonusDataStr = '<BoardBonusData>
                                            <RollResult dice1="'.$dice0.'" dice2="'.$dice1.'" destIndex="'.$destPosition.'" prevIndex="'.$monopolyPosition.'" currentSubBonusGameId="0" win="'.($totalWin * 100).'" NumbOfBoardBonusPlayed="'.$slotSettings->GetGameData($slotSettings->slotId . 'FreeGame').'"  isGoCrossed="'.$isGoCrossed.'" IsGetOutOfJailCardAvailable="'.($getOutOfJail > 0 ? 1 : 0).'" IsCCBonus="'.$isCCBonus.'" IsChanceBonus="'.$isChanceBonus.'" previousBoardCount="0" />
                                            <SubBonusData>
                                                '.$propertyData.$ccData.$chanceData.'
                                            </SubBonusData>
                                        </BoardBonusData>';
                        }
                        
                        $totalWin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalWinning');
                        $slotSettings->SetGameData($slotSettings->slotId . 'History', $history);

                        $sxe = new SimpleXMLElement('<GameResponse type="Logic"></GameResponse>');
                        $header = simplexml_load_string('	<Header sessionID="LBS2KXtZGdKpmDxNI33/AWjJjFGFlxVQgWWWM3jfghxe3rsg+fntmoALK3j4j8C4GyBcF84O/aA0CMNNUrJeKcJWJqVHxu8KrkTvRlu3eyM=" ccyCode="en_US" deciSep="." thousandSep="," lang="en_GB" gameID="20112" versionID="1_0" fullVersionID="1.0.54" isRecovering="N"/>');
                        $accountData = simplexml_load_string('<AccountData><AccountData><CurrencyMultiplier>1</CurrencyMultiplier></AccountData></AccountData>');
                        $balance = simplexml_load_string('<Balances><Balance name="CASH_BALANCE" value="'.($slotSettings->GetBalance() * 100).'"/></Balances>');
                        $gameResult = simplexml_load_string('<GameResult stake="'.($allbet * 100).'" stakePerLine="0" paylineCount="0" totalWin="'.($totalWin * 100).'" betID=""></GameResult>');

                        if($postData['slotEvent'] != 'freespin')
                        {
                            $playStr = '<Play stake="'.($allbet * 100).'" win="'.($totalWin * 100).'">
                                        <BetState drawn="'.$ball.'" total_bets="'.$num_bets.'">';
                            foreach($bets as &$bet)
                            {
                                $playStr .= '<Bet name="'.$bet['name'].'" stake="'.$bet['stake'].'" winnings="'.$bet['winnings'].'" seln="'.implode(',', $bet['sel']).'" />';
                            }

                            $monopolyTotalBet = $slotSettings->GetGameData($slotSettings->slotId . 'MonopolyTotalBet');
                            $numWageredProps = $slotSettings->GetGameData($slotSettings->slotId . 'MonopolyWageredProps');
                            $monopolyBetPosition = $slotSettings->GetGameData($slotSettings->slotId . 'MonopolyBetPosition');

                            $playStr .= '</BetState>';
                            $playStr .= '<MonopolyBetState totalBet="'.$monopolyTotalBet.'" numWageredProps="'.$numWageredProps.'" betPerSpace="'.implode('|', $monopolyBetPosition).'" powerSpinsRemaining="'.$powerSpin.'" isMonopolyBetFlusedOnFirstSpin="0"/>
                                    </Play>';

                            if($isMonopolyTriggered)
                                $slotSettings->SetGameData($slotSettings->slotId . 'PlayData', $playStr);
                        }
                        else
                        {
                            $playStr = $slotSettings->GetGameData($slotSettings->slotId . 'PlayData');                            
                        }
                        
                        $play = simplexml_load_string($playStr);
                        $stats = simplexml_load_string('<Stats>
                                    <History name="Bet_Draw" value="'.implode('|', $history).'"/>
                                </Stats>');

                        $rollsRemaining = $slotSettings->GetGameData($slotSettings->slotId . 'MonopolyRollsRemaining');
                        $rollsWinning = $slotSettings->GetGameData($slotSettings->slotId . 'RollsWinning') * 100;
                        $baseWinning = $slotSettings->GetGameData($slotSettings->slotId . 'BaseWinning') * 100;

                        $rbInfo = simplexml_load_string('<RBInfo RollsRemaining="'.$rollsRemaining.'" RollWinningSoFar="'.$rollsWinning.'" BaseWinningSoFar="'.$baseWinning.'" isMaxWin="N"/>');
                        
                        $this->sxml_append($gameResult, $play);
                        $this->sxml_append($gameResult, $stats);
                        $this->sxml_append($gameResult, $rbInfo);
                        if($bonusDataStr != '')
                        {
                            $bonusData = simplexml_load_string($bonusDataStr);
                            $this->sxml_append($gameResult, $bonusData);
                        }
                        $this->sxml_append($sxe, $header);
                        $this->sxml_append($sxe, $accountData);
                        $this->sxml_append($sxe, $balance);
                        $this->sxml_append($sxe, $gameResult);
                        $response = $sxe->asXML();
                        if($postData['slotEvent'] == 'freespin')
                            $allbet = 0;
                        $slotSettings->SaveLogReport($response, $allbet, $totalWin, $postData['slotEvent']);

                        if($rollsRemaining == 0)
                        {
                            $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', '');
                            $slotSettings->SetGameData($slotSettings->slotId . 'MonopolyRollsRemaining', -1);
                            $slotSettings->SetGameData($slotSettings->slotId . 'BaseWinning', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'RollsWinning', 0);
                        }
                        break;
                    case 'EndGame':
                        $slotSettings->SetGameData($slotSettings->slotId . 'AllBet', 0);

                        $sxe = new SimpleXMLElement('<GameResponse type="EndGame"></GameResponse>');
                        $header = simplexml_load_string('<Header sessionID="T0RuuLGLoWQdxR86YwRCpFd/kZ6/qUZHDUsIxT/KRWUuILGsBZXA4GYIpqmFXONl" ccyCode="GBP" deciSep="." thousandSep="," lang="en_US" gameID="20112" versionID="1_0" fullVersionID="unknown" isRecovering="N"/>');
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
    }

}


