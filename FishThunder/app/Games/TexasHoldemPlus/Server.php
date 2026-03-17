<?php 
namespace VanguardLTE\Games\TexasHoldemPlus
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

        function getCard($cards)
        {
            $result = [];
            foreach($cards as $card)
            {
                $suit = $card[0];
                $rank = $card[1];
                $suitStr = '';
                switch($suit)
                {
                    case 0:
                        $suitStr = 'S';
                        break;
                    case 1:
                        $suitStr = 'H';                    
                        break;
                    case 2:
                        $suitStr = 'D';
                        break;
                    case 3:
                        $suitStr = 'C';
                        break;
                }
                $rankStr = '';
                if($rank == 14)
                    $rankStr = 'A';
                else if($rank == 11)
                    $rankStr = 'J';
                else if($rank == 12)
                    $rankStr = 'Q';
                else if($rank == 13)
                    $rankStr = 'K';
                else if($rank == 10)
                    $rankStr = 'T';
                else
                    $rankStr = (string)$rank;
                $result[] = ($rankStr.$suitStr);
            }
            return $result;
        }

        function getUsedCards($cards, $result)
        {
            $usingCard = [];
            switch($result[1])
            {
                case 0: //high card
                case 1: //one pair
                case 3: //three of a kind
                case 7: //four of a kind
                    for($i = 0; $i < count($cards); $i++)
                    {
                        $card = $cards[$i];
                        if($card[1] == $result[2])
                            $usingCard[] = 1;
                        else
                            $usingCard[] = 0;
                    }
                    break;
                case 2: //two pair
                case 6: //full house
                    for($i = 0; $i < count($cards); $i++)
                    {
                        $card = $cards[$i];
                        if($card[1] == $result[2] || $card[1] == $result[3])
                            $usingCard[] = 1;
                        else
                            $usingCard[] = 0;
                    }
                    break;
                case 4: //straight
                    $contained = [];
                    for($i = 0; $i < count($cards); $i++)
                    {
                        $card = $cards[$i];
                        if( (($card[1] > $result[2] - 5  && $card[1] <= $result[2]) || ($result[2] == 5 && $card[1] == 14)) && !in_array($card[1], $contained))
                        {
                            $usingCard[] = 1;
                            $contained[] = $card[1];
                        }
                        else
                            $usingCard[] = 0;
                    }
                    break;
                case 8: //straight flush
                case 9: //loyal flush
                    $contained = [];
                    for($i = 0; $i < count($cards); $i++)
                    {
                        $card = $cards[$i];
                        if( ((($card[1] > $result[2] - 5  && $card[1] <= $result[2]) || ($result[2] == 5 && $card[1] == 14)) && ($card[0] == $result[3])) && !in_array($card[1], $contained))
                        {
                            $usingCard[] = 1;
                            $contained[] = $card[1];
                        }
                        else
                            $usingCard[] = 0;
                    }
                    break;    
                case 5: //flush
                    for($i = 0; $i < count($cards); $i++)
                    {
                        $card = $cards[$i];
                        if($card[0] == $result[3])
                            $usingCard[] = 1;
                        else
                            $usingCard[] = 0;
                    }
                    break;
            }
            return $usingCard;
        }

        function generateDeck($pickedCards)
        {
            $pickedCards = $this->getCard($pickedCards);
            $availableDeck = [];
            for($s = 0; $s < 4; $s++)
                for($r = 2; $r <= 14; $r++)
                {
                    $card = $this->getCard([[$s, $r]])[0];
                    if(!in_array($card, $pickedCards))
                    {
                        $availableDeck[] = [$s, $r];
                    }
                }
            return $availableDeck;
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
                                return '{"javascripts":["/games/TexasHoldemPlus/d1ay6055gq02dh.cloudfront.net/5.0.0.11-1658237375/resource-service/mainjs/application/capabilitiesdetector.CapabilitiesDetectorBootstrapper.js?resourceversion=5.0.0.11-1658237375&appcode=capabilities-detector","/games/TexasHoldemPlus/d1ay6055gq02dh.cloudfront.net/5.0.0.11-1658237375/resource-service/translationjs/bundles/translations/#{locale}/capabilitiesdetector.LocalizationBundle.js?resourceversion=5.0.0.11-1658237375&appcode=capabilities-detector"],"jsons":["/games/TexasHoldemPlus/d1ay6055gq02dh.cloudfront.net/5.0.0.11-1658237375/resource-service/translationjson/bundles/translations/#{locale}/capabilitiesdetector.LocalizationBundle.json?resourceversion=5.0.0.11-1658237375&appcode=capabilities-detector"],"main_class":"capabilitiesdetector.CapabilitiesDetectorBootstrapper","supported_locales":["it_IT","ru_RU","el_GR","pl_PL","ro_RO","tr_TR","fr_FR","cs_CZ","hu_HU","ca_ES","de_DE","sk_SK","es_ES","nl_NL","fr_CA","sv_SE","da_DK","bg_BG","hr_HR","fi_FI","en_GB","et_EE","lt_LT","lv_LV","sl_SI","no_NO","pt_PT"],"default_locale":"en"}';
                            break;
                            case "gls-platform":
                                return '{"javascripts":["/games/TexasHoldemPlus/d1ay6055gq02dh.cloudfront.net/5.0.0.11-1658237375/resource-service/mainjs/application/glsplatform.GlsPlatform.js?resourceversion=5.0.0.11-1658237375&appcode=gls-platform","/games/TexasHoldemPlus/dnk-resource.wimobile.casinarena.com/resource-service/metadatajs/bundles/metadata/glsplatform.MetaDataBundle.js?resourceversion=5.0.0.11-1658237375&appcode=gls-platform&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=nyx_newdemo&realmoney=false&gamecode=texasholdemplus&locale=en_US&webaudio=true","/games/TexasHoldemPlus/d1ay6055gq02dh.cloudfront.net/5.0.0.11-1658237375/resource-service/translationjs/bundles/translations/#{locale}/glsplatform.LocalizationsBundle.js?resourceversion=5.0.0.11-1658237375&appcode=gls-platform"],"jsons":["/games/TexasHoldemPlus/d1ay6055gq02dh.cloudfront.net/5.0.0.11-1658237375/resource-service/metadatajson/bundles/metadata/glsplatform.MetaDataBundle.json?resourceversion=5.0.0.11-1658237375&appcode=gls-platform&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=nyx_newdemo&realmoney=false&gamecode=texasholdemplus&locale=en_US&webaudio=true","/games/TexasHoldemPlus/d1ay6055gq02dh.cloudfront.net/5.0.0.11-1658237375/resource-service/translationjson/bundles/translations/#{locale}/glsplatform.LocalizationsBundle.json?resourceversion=5.0.0.11-1658237375&appcode=gls-platform"],"main_class":"glsplatform.GlsPlatform","supported_locales":["sl_SI","pl_PL","lt_LT","bg_BG","ru_RU","ro_RO","el_GR","pt_PT","en_GB","sk_SK","cs_CZ","ca_ES","fr_CA","es_ES","lv_LV","hr_HR","it_IT","fi_FI","de_DE","hu_HU","fr_FR","tr_TR","sv_SE","da_DK","no_NO","et_EE","nl_NL"],"default_locale":"en"}';
                                break;
                            case "lean-regular-partner-adapter":
                                return '{"javascripts":["/games/TexasHoldemPlus/d1ay6055gq02dh.cloudfront.net/5.0.0.11-1658237375/resource-service/mainjs/application/leanpartneradapter.LeanPartnerAdapter.js?resourceversion=5.0.0.11-1658237375&appcode=lean-regular-partner-adapter","/games/TexasHoldemPlus/dnk-resource.wimobile.casinarena.com/resource-service/metadatajs/bundles/metadata/leanpartneradapter.MetaDataBundle.js?resourceversion=5.0.0.11-1658237375&appcode=lean-regular-partner-adapter&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=nyx_newdemo&realmoney=false&gamecode=texasholdemplus&locale=en_US&webaudio=true","/games/TexasHoldemPlus/d1ay6055gq02dh.cloudfront.net/5.0.0.11-1658237375/resource-service/translationjs/bundles/translations/#{locale}/leanpartneradapter.LocalizationsBundle.js?resourceversion=5.0.0.11-1658237375&appcode=lean-regular-partner-adapter"],"jsons":["/games/TexasHoldemPlus/d1ay6055gq02dh.cloudfront.net/5.0.0.11-1658237375/resource-service/metadatajson/bundles/metadata/leanpartneradapter.MetaDataBundle.json?resourceversion=5.0.0.11-1658237375&appcode=lean-regular-partner-adapter&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=nyx_newdemo&realmoney=false&gamecode=texasholdemplus&locale=en_US&webaudio=true","/games/TexasHoldemPlus/d1ay6055gq02dh.cloudfront.net/5.0.0.11-1658237375/resource-service/translationjson/bundles/translations/#{locale}/leanpartneradapter.LocalizationsBundle.json?resourceversion=5.0.0.11-1658237375&appcode=lean-regular-partner-adapter"],"main_class":"commonadapter.TopboxPartnerAdapter","supported_locales":["it_IT","ru_RU","el_GR","pl_PL","ro_RO","tr_TR","fr_FR","cs_CZ","hu_HU","ca_ES","de_DE","sk_SK","es_ES","nl_NL","fr_CA","sv_SE","da_DK","bg_BG","hr_HR","fi_FI","en_GB","et_EE","lt_LT","lv_LV","sl_SI","no_NO","pt_PT"],"default_locale":"en"}';
                                break;
                            case "texasholdemplus":
                                return '{
                                    "orientation": "BOTH",
                                    "default_locale": "en",
                                    "inject": [
                                        {
                                            "link": {
                                                "rel": "stylesheet",
                                                "href": "content/texasholdemplus/css/app.css"
                                            }
                                        }
                                    ],
                                    "javascripts": [
                                        "content/texasholdemplus/js/bootstrapper.js?resourceversion=5.0.0.11-1658237375&appcode=texasholdemplus&gaffingenabled=false&demoenabled=false&debugenabled=false&touchdevice=false&partnercode=nyx_newdemo&realmoney=false&gamecode=texasholdemplus&locale=en_US&webaudio=true"
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
                        $filename = base_path() . '/app/Games/TexasHoldemPlus/game.txt';
                        $file = fopen($filename, "r" );
                        $filesize = filesize( $filename );
                        $response = fread( $file, $filesize );
                        $response = str_replace('BAL_REPLACE', $slotSettings->GetBalance() * 100, $response);
                        fclose( $file );
                        $slotSettings->SetGameData($slotSettings->slotId . 'AllBet', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'FlopBet', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'TurnBet', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'RiverBet', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'Step', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'PlayerCards', []);
                        $slotSettings->SetGameData($slotSettings->slotId . 'CommunityCards', []);                        
                        break;                        
                    case 'Logic':                        
                        $playerState = $postData->PlayerState;
                        $betType = (int)$playerState['type'];
                        $anteBet = (int)$playerState->Player['anteBet'] * 0.01;
                        $bonusBet = (int)$playerState->Player['bonusBet'] * 0.01;
                        $allbet = 0;
                        $step = $slotSettings->GetGameData($slotSettings->slotId . 'Step');
                        $actions = [];
                        $playerCards = $slotSettings->GetGameData($slotSettings->slotId . 'PlayerCards');
                        $communityCards = $slotSettings->GetGameData($slotSettings->slotId . 'CommunityCards');
                        $dealerCards = [];
                        $previousAction = '';
                        $winning = '';
                        $playerInfo = '';
                        $dealerInfo = '';
                        $endGame = 0;
                        $totalWin = 0;

                        if($betType == 0) //ante bet
                        {
                            $allbet = $anteBet + $bonusBet;
                            $slotSettings->SetGameData($slotSettings->slotId . 'AllBet', $allbet);
                            $actions = ['FOLD', 'CALL'];
                            $playerCards = [];
                            $winTypeTmp = $slotSettings->GetSpinSettings('bet', $allbet);
                            $winLimit = $winTypeTmp[1];
                            $spinAcquired = false;
                            $bonusWin = 0;
                            $bonusRank = 0;

                            $minPlayerCards = [];
                            $minDealerCards = [];
                            $minCommunityCards = [];
                            $minPlayerResult = [];
                            $minDealerResult = [];
                            $minBonusWin = 0;
                            $minBonusRank = 0;
                            
                            for($i = 0; $i < 500; $i++){
                                $playerCards = [];
                                $communityCards = [];
                                $playerCards[] = [rand(0, 3), rand(2, 14)];
                                $playerCards[] = [rand(0, 3), rand(2, 14)];

                                $deck = $this->generateDeck($playerCards);
                                shuffle($deck);
                                $dealerCards = [];
                                $dealerCards[] = $deck[0];
                                $dealerCards[] = $deck[1];
                                $communityCards[] = $deck[2];
                                $communityCards[] = $deck[3];
                                $communityCards[] = $deck[4];
                                $communityCards[] = $deck[5];
                                $communityCards[] = $deck[6];
                                
                                $playerTotalCards = array_merge($playerCards, $communityCards);
                                $dealerTotalCards = array_merge($dealerCards, $communityCards);
                                
                                $playerResult = $slotSettings->GetCombination($playerTotalCards);
                                $dealerResult = $slotSettings->GetCombination($dealerTotalCards);

                                $playBet = $anteBet * 5;
                                if($playerResult[1] > $dealerResult[1] || $playerResult[1] == $dealerResult[1] && $playerResult[2] > $dealerResult[2] || $playerResult[1] == $dealerResult[1] && $playerResult[2] == $dealerResult[2] && $playerResult[3] > $dealerResult[3])
                                {
                                    $anteWin = 0;
                                    if($playerResult[1] < 4)
                                        $anteWin = $anteBet;
                                    else
                                        $anteWin = $anteBet * 2;

                                    $totalWin = $playBet * 2 + $anteWin;
                                }
                                else if($playerResult[1] == $dealerResult[1] && $playerResult[2] == $dealerResult[2] && $playerResult[3] == $dealerResult[3])
                                {
                                    //same rank
                                    $anteWin = $anteBet;
                                    $totalWin = $playBet + $anteWin;
                                }

                                //check bonus win
                                $bonusWin = 0;
                                $bonusRank = 0;
                                if($playerCards[0][1] == 14 && $playerCards[1][1] == 14) //AA
                                {
                                    $bonusWin = $bonusBet * 31;
                                    $bonusRank = 7;
                                }
                                else if(($playerCards[0][1] == 14 && $playerCards[1][1] == 13) || ($playerCards[0][1] == 13 && $playerCards[1][1] == 14)) //AK
                                {
                                    if($playerCards[0][0] == $playerCards[1][0]) 
                                    {
                                        //AK suited
                                        $bonusWin = $bonusBet * 26;
                                        $bonusRank = 6;
                                    }
                                    else
                                    {
                                        //AK unsuited
                                        $bonusWin = $bonusBet * 16;
                                        $bonusRank = 4;
                                    }
                                }
                                else if(($playerCards[0][1] == 14 && $playerCards[1][1] >= 11) || ($playerCards[0][1] >= 11 && $playerCards[1][1] == 14)) //AJ or AQ
                                {
                                    if($playerCards[0][0] == $playerCards[1][0]) 
                                    {
                                        //AJ or AQ suited
                                        $bonusWin = $bonusBet * 21;
                                        $bonusRank = 5;
                                    }
                                    else
                                    {
                                        //AJ or AQ unsuited
                                        $bonusWin = $bonusBet * 6;
                                        $bonusRank = 2;
                                    }
                                }
                                else if($playerCards[0][1] == $playerCards[1][1]) //pair of cards
                                {
                                    if($playerCards[0][1] > 10)
                                    {
                                        //Pair of Jacks to Kings
                                        $bonusWin = $bonusBet * 11;
                                        $bonusRank = 3;
                                    }
                                    else
                                    {
                                        //Pair of 2 to 10
                                        $bonusWin = $bonusBet * 4;
                                        $bonusRank = 1;
                                    }
                                }

                                $totalWin += $bonusWin;

                                if($winLimit > $totalWin || $this->debug)
                                {
                                    $spinAcquired = true;
                                    break;
                                }
                                else
                                {
                                    $minPlayerCards = $playerCards;
                                    $minDealerCards = $dealerCards;
                                    $minCommunityCards = $communityCards;
                                    $minPlayerResult = $playerResult;
                                    $minDealerResult = $dealerResult;
                                    $minBonusWin = $bonusWin;
                                    $minBonusRank = $bonusRank;
                                }
                            }

                            if(!$spinAcquired)
                            {
                                $playerCards = $minPlayerCards;
                                $dealerCards = $minDealerCards;
                                $communityCards = $minCommunityCards;
                                $playerResult = $minPlayerResult;
                                $dealerResult = $minDealerResult;
                                $bonusWin = $minBonusWin;
                                $bonusRank = $minBonusRank;
                            }

                            $slotSettings->SetGameData($slotSettings->slotId . 'CommunityCards', $communityCards);
                            $slotSettings->SetGameData($slotSettings->slotId . 'PlayerCards', $playerCards);
                            $slotSettings->SetGameData($slotSettings->slotId . 'DealerCards', $dealerCards);
                            $slotSettings->SetGameData($slotSettings->slotId . 'PlayerResult', $playerResult);
                            $slotSettings->SetGameData($slotSettings->slotId . 'DealerResult', $dealerResult);
                            $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', $bonusWin);
                            $slotSettings->SetGameData($slotSettings->slotId . 'BonusRank', $bonusRank);

                        }
                        else if($betType == 3) //flop
                        {
                            $allbet = $anteBet * 2;
                            $actions = ['FOLD', 'CHECK', 'BET'];                            
                            $communityCards = array_slice($communityCards, 0, 3);                            
                            $slotSettings->SetGameData($slotSettings->slotId . 'AllBet', $slotSettings->GetGameData($slotSettings->slotId . 'AllBet') + $allbet);
                            $slotSettings->SetGameData($slotSettings->slotId . 'FlopBet', $allbet);
                            $previousAction = 'previousAction="Bet"';
                        } 
                        else if($betType == 2) //postflop check
                        {
                            $actions = ['FOLD', 'CHECK', 'BET'];
                            if($step == 2) //turn
                            {
                                $slotSettings->SetGameData($slotSettings->slotId . 'TurnBet', 0);
                                $communityCards = array_slice($communityCards, 0, 4);
                            }
                            else if($step == 3) //river
                            {
                                $actions = ['ENDGAME'];
                                $slotSettings->SetGameData($slotSettings->slotId . 'RiverBet', 0);        
                                
                                //endgame
                                $endGame = 1;
                            }
                            $previousAction = 'previousAction="Check"';
                            
                        }
                        else if($betType == 4) //post flop bet
                        {
                            $actions = ['FOLD', 'CHECK', 'BET'];
                            $allbet = $anteBet;
                            $slotSettings->SetGameData($slotSettings->slotId . 'AllBet', $slotSettings->GetGameData($slotSettings->slotId . 'AllBet') + $allbet);
                            $step = $slotSettings->GetGameData($slotSettings->slotId . 'Step');
                            if($step == 2) //turn
                            {
                                $slotSettings->SetGameData($slotSettings->slotId . 'TurnBet', $allbet);
                                $communityCards = array_slice($communityCards, 0, 4);
                            }
                            else if($step == 3) //river
                            {
                                $actions = ['ENDGAME'];
                                $slotSettings->SetGameData($slotSettings->slotId . 'RiverBet', $allbet);

                                //endgame
                                $endGame = 1;                                
                            }
                            $previousAction = 'previousAction="Bet"';
                        }
                        else if($betType == 1) //fold
                        {
                            $actions = ['ENDGAME'];
                            $endGame = 2;
                            $previousAction = 'previousAction="Fold"';
                        }

                        if($allbet > 0)
                        {
                            $slotSettings->SetBalance(-1 * $allbet, $postData['slotEvent']);
                            $bankSum = $allbet / 100 * $slotSettings->GetPercent();
                            $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $bankSum, $postData['slotEvent']);
                            $slotSettings->UpdateJackpots($allbet);
                            $slotSettings->SetBet($allbet);
                        }

                        if($endGame > 0)
                        {
                            $playerResult = $slotSettings->GetGameData($slotSettings->slotId . 'PlayerResult');
                            $dealerResult = $slotSettings->GetGameData($slotSettings->slotId . 'DealerResult');
                            $dealerCards = $slotSettings->GetGameData($slotSettings->slotId . 'DealerCards');
                            $playerTotalCards = array_merge($playerCards, $communityCards);         
                            $usedCards = $this->getUsedCards($playerTotalCards, $playerResult);
                            $rank = $playerResult[1];
                            if($rank > 4)
                                $rank++;
                            $playerInfo = 'rank="'.$rank.'" cardsUsed="'.implode('|', $usedCards).'"';

                            $dealerTotalCards = array_merge($dealerCards, $communityCards);
                            $usedCards = $this->getUsedCards($dealerTotalCards, $dealerResult);
                            $rank = $dealerResult[1];
                            if($rank > 4)
                                $rank++;
                            $dealerInfo = 'rank="'.$rank.'" usedCards="'.implode('|', $usedCards).'"';

                            $bonusWin = $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin');
                            $bonusStr = 'bonusWin="0"';
                            if($bonusWin > 0)
                            {
                                $bonusStr = 'bonusWin="'.($bonusWin * 100).'"';
                                $playerInfo .= (' bonusRank="'.$slotSettings->GetGameData($slotSettings->slotId . 'BonusRank').'"');
                            }

                            $totalWin = $bonusWin;
                            if($endGame == 1)
                            {                                
                                $flopBet = $slotSettings->GetGameData($slotSettings->slotId . 'FlopBet');
                                $turnBet = $slotSettings->GetGameData($slotSettings->slotId . 'TurnBet');
                                $riverBet = $slotSettings->GetGameData($slotSettings->slotId . 'RiverBet');
                                $playBet = $flopBet + $turnBet + $riverBet;

                                if($playerResult[1] > $dealerResult[1] || $playerResult[1] == $dealerResult[1] && $playerResult[2] > $dealerResult[2] || $playerResult[1] == $dealerResult[1] && $playerResult[2] == $dealerResult[2] && $playerResult[3] > $dealerResult[3])
                                {
                                    $anteWin = 0;
                                    if($playerResult[1] < 4)
                                        $anteWin = $anteBet;
                                    else
                                        $anteWin = $anteBet * 2;

                                    $totalWin += $playBet * 2 + $anteWin;

                                    $winning = '<Winning player="1" dealer="0" tie="0" push="0" totalWin="'.($totalWin * 100).'" anteWin="'.($anteWin * 100).'" playWin="'.($playBet * 2 * 100).'" '.$bonusStr.' />';
                                }
                                else if($playerResult[1] == $dealerResult[1] && $playerResult[2] == $dealerResult[2] && $playerResult[3] == $dealerResult[3])
                                {
                                    //same rank
                                    $anteWin = $anteBet;
                                    $totalWin += ($playBet + $anteWin);

                                    $winning = '<Winning player="0" dealer="0" tie="1" push="1" totalWin="'.($totalWin * 100).'" anteWin="'.($anteWin * 100).'" playWin="'.(($playBet) * 100).'" '.$bonusStr.' />';
                                }
                                else
                                {
                                    //player lose
                                    $winning = '<Winning player="0" dealer="1" tie="0" push="0" totalWin="'.($totalWin * 100).'" anteWin="0" playWin="0" '.$bonusStr.' />';
                                }
                            }
                            else if($endGame == 2)
                            {
                                //ending with fold
                                $winning = '<Winning player="0" dealer="0" tie="0" push="0" totalWin="'.($totalWin * 100).'" anteWin="0" playWin="0" '.$bonusStr.' />';
                            }    
                            if($totalWin > 0)
                            {
                                $slotSettings->SetBank('', -1 * $totalWin);
                                $slotSettings->SetBalance($totalWin);
                                $slotSettings->SetWin($totalWin);
                            }                        
                        }
                        
                        $slotSettings->SetGameData($slotSettings->slotId . 'Step', $slotSettings->GetGameData($slotSettings->slotId . 'Step') + 1);                        
                        
                        $flopBet = $slotSettings->GetGameData($slotSettings->slotId . 'FlopBet');
                        $turnBet = $slotSettings->GetGameData($slotSettings->slotId . 'TurnBet');
                        $riverBet = $slotSettings->GetGameData($slotSettings->slotId . 'RiverBet');
                        $playBet = $flopBet + $turnBet + $riverBet;
                        $allbet = $slotSettings->GetGameData($slotSettings->slotId . 'AllBet');

                        $sxe = new SimpleXMLElement('<GameResponse type="Logic"></GameResponse>');
                        $header = simplexml_load_string('	<Header sessionID="LBS2KXtZGdKpmDxNI33/AWjJjFGFlxVQgWWWM3jfghxe3rsg+fntmoALK3j4j8C4GyBcF84O/aA0CMNNUrJeKcJWJqVHxu8KrkTvRlu3eyM=" ccyCode="en_GB" deciSep="." thousandSep="," lang="en_GB" gameID="20376" versionID="1_0" fullVersionID="1.0.1" isRecovering="N" readyForEndGame="N"/>');
                        $accountData = simplexml_load_string('<AccountData><AccountData><CurrencyMultiplier>1</CurrencyMultiplier></AccountData></AccountData>');
                        $balance = simplexml_load_string('<Balances><Balance name="CASH_BALANCE" value="'.($slotSettings->GetBalance() * 100).'"/></Balances>');
                        $gameResult = simplexml_load_string('<GameResult stake="'.($allbet * 100).'" stakePerLine="0" paylineCount="0" totalWin="'.($totalWin * 100).'" betID=""></GameResult>');
                        $gameStatus = simplexml_load_string('<GameStatus state="Play" actions="'.implode('|', $actions).'" '.$previousAction.'>
                                        <PlayerState numOfPlayers="1" totalWin="'.$totalWin.'">
                                            <Player cards="'.implode('|',$this->getCard($playerCards)).'" '.$playerInfo.' >
                                                <Stakes anteBet="'.($anteBet * 100).'" playBet="'.($playBet * 100).'" flopBet="'.($flopBet * 100).'" turnBet="'.($turnBet * 100).'" riverBet="'.($riverBet * 100).'" bonusBet="'.($bonusBet * 100).'"/>
                                                '.$winning.'
                                            </Player>
                                        </PlayerState>
                                    </GameStatus>');

                        if(count($communityCards) > 0)
                        {
                            $communityCardsResult = simplexml_load_string('<CommunityCards number="'.count($communityCards).'" cards="'.implode('|',$this->getCard($communityCards)).'" />');
                            $this->sxml_append($gameStatus, $communityCardsResult);
                        }
                        if(count($dealerCards) > 0)
                        {
                            $dealerCardsResult = simplexml_load_string('<Dealer cards="'.implode('|',$this->getCard($dealerCards)).'" '.$dealerInfo.' />');
                            $this->sxml_append($gameStatus, $dealerCardsResult);
                        }
                        $this->sxml_append($gameResult, $gameStatus);
                        $this->sxml_append($sxe, $header);
                        $this->sxml_append($sxe, $accountData);
                        $this->sxml_append($sxe, $balance);
                        $this->sxml_append($sxe, $gameResult);
                        $response = $sxe->asXML();
                        if($endGame > 0)
                            $slotSettings->SaveLogReport($response, $allbet, $totalWin, '');
                        break;
                    case 'EndGame':
                        $slotSettings->SetGameData($slotSettings->slotId . 'AllBet', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'FlopBet', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'TurnBet', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'RiverBet', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'Step', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'PlayerCards', []);
                        $slotSettings->SetGameData($slotSettings->slotId . 'CommunityCards', []);
                        $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'BonusRank', 0);

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
    }

}


