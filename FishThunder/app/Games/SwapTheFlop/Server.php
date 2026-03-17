<?php 
namespace VanguardLTE\Games\SwapTheFlop
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

        function getCardForResponse($card)
        {
            $suit = $card[0];
            $rank = $card[1];
            if($rank == 14)
                $rank = 1;
            return ['id' => $suit * 13 + $rank, 'suit' => $suit, 'value' => $rank];
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

        function sortCards($cards, $result)
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
                    for($i = 0; $i < count($cards); $i++)
                    {
                        $card = $cards[$i];
                        if( ($card[1] > $result[2] - 5 && $card[1] <= $result[2]) || ($result[2] == 5 && $card[1] == 14))
                            $usingCard[] = 1;
                        else
                            $usingCard[] = 0;
                    }
                    break;
                case 8: //straight flush
                case 9: //loyal flush
                    for($i = 0; $i < count($cards); $i++)
                    {
                        $card = $cards[$i];
                        if(( ($card[1] > $result[2] - 5  && $card[1] <= $result[2]) || ($result[2] == 5 && $card[1] == 14)) && ($card[0] == $result[3]))
                            $usingCard[] = 1;
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

            $sortCard = [];
            $notWinCard = [];
            for($i = 0; $i < count($usingCard); $i++)
            {
                if($usingCard[$i] == 1)
                {
                    $sortCard[] = $this->getCardForResponse($cards[$i]);
                }
                else
                {
                    $notWinCard[] = $cards[$i];
                }
            }

            usort($notWinCard, function($a, $b){
                return -($a[1] - $b[1]);
            });
            for($i = 0; $i < count($notWinCard); $i++)
                $sortCard[] = $this->getCardForResponse($notWinCard[$i]);

            while(count($sortCard) > 5)
                array_pop($sortCard);
            return $sortCard;
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
                
                $postData = json_decode($request->getContent());
                $reqType = $postData[0]->method;
                
                switch( $reqType ) 
                {
                    case "initializeGame":
                        $filename = base_path() . '/app/Games/SwapTheFlop/game.txt';
                        $file = fopen($filename, "r" );
                        $filesize = filesize( $filename );
                        $response = fread( $file, $filesize );
                        $response = str_replace('BAL_REPLACE', $slotSettings->GetBalance() * 100, $response);
                        fclose( $file );
                        $slotSettings->SetGameData($slotSettings->slotId . 'Step', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'BonusBet', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'PlayerBet', 0);
                        $slotSettings->SetGameData($slotSettings->slotId . 'PlayerCards', []);
                        $slotSettings->SetGameData($slotSettings->slotId . 'CommunityCards', []);                        
                        break;                        
                    case 'openPlay':              
                        $bonusBet = $postData[1]->params->data->bounsBet;
                        $playerBet = $postData[1]->params->data->playerBet;
                        $anteBet = $playerBet * 0.25;
                        $normalBet = $playerBet - $anteBet;
                        $allbet = $bonusBet + $playerBet;

                        $playerCards = [];
                        $communityCards = [];
                        $dealerCards = [];
                        $totalWin = 0;                        
                        $playerCards = [];
                        $winTypeTmp = $slotSettings->GetSpinSettings('bet', $allbet);
                        $winType = $winTypeTmp[0];
                        $winLimit = $winTypeTmp[1];
                        $spinAcquired = false;
                        $aceHunterBonus = 0;
                        $doubleBonus = 0;
                        $anteWin = 0;

                        $minPlayerCards = [];
                        $minDealerCards = [];
                        $minCommunityCards = [];
                        $minPlayerResult = [];
                        $minDealerResult = [];
                        $minTotalWin = -1;
                        $minAnteWin = 0;
                        $minaceHunterBonus = 0;
                        $mindoubleBonus = 0;
                        $qualified = false;
                        $minqualified = false;
                        
                        for($i = 0; $i < 500; $i++){
                            $playerCards = [];
                            $communityCards = [];
                            $playerCards[] = [rand(0, 3), rand(2, 14)];
                            $playerCards[] = [rand(0, 3), rand(2, 14)];
                            $aceHunterBonus = 0;
                            $doubleBonus = 0;
                            $totalWin = 0;
                            $anteWin = 0;
                            $qualified = false;

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

                            if($slotSettings->CompareHand($playerResult, $dealerResult) == 1)
                            {
                                //check if dealer is qualified
                                if($dealerResult[1] > 1 || ($dealerResult[1] == 1 && $dealerResult[2] >= 11) )
                                    $qualified = true;

                                $anteMultiplier = 1;
                                if($playerResult[1] == 5) //flush
                                    $anteMultiplier = 4;
                                else if($playerResult[1] == 6) //full house
                                    $anteMultiplier = 5;
                                else if($playerResult[1] == 7) //four of a kind
                                    $anteMultiplier = 10;
                                else if($playerResult[1] == 8) //straight flush
                                    $anteMultiplier = 100;
                                else if($playerResult[1] == 9) //royal flush
                                    $anteMultiplier = 200;

                                if($qualified)
                                    $totalWin = $playerBet + $anteBet * $anteMultiplier + $normalBet;
                                else
                                    $totalWin = $playerBet + $anteBet * $anteMultiplier;

                                $anteWin = $anteBet * $anteMultiplier;
                            }
                            else if($slotSettings->CompareHand($playerResult, $dealerResult) == 0)
                            {
                                continue;
                            }

                            //check bonus win                                
                            if($playerCards[0][1] == 14 || $playerCards[1][1] == 14) //AA
                            {
                                $aceHunterBonus = $bonusBet * 3;
                                if($playerCards[0][1] == 14 && $playerCards[1][1] == 14) 
                                {
                                    $aceHunterBonus = $bonusBet * 12.5;
                                    $doubleBonus = $bonusBet * 17.5;
                                }                                
                            }                                    
                            else if($playerCards[0][1] == 13 && $playerCards[1][1] == 13) //KK
                            {
                                $doubleBonus = $bonusBet * 20;
                            }
                            else if($playerCards[0][1] == 12 && $playerCards[1][1] == 12) //QQ
                            {
                                $doubleBonus = $bonusBet * 15;
                            }
                            else if($playerCards[0][1] == 11 && $playerCards[1][1] == 11) //JJ
                            {
                                $doubleBonus = $bonusBet * 10;
                            }
                            else if($playerCards[0][1] == $playerCards[1][1]) // 22~TT
                            {
                                $doubleBonus = $bonusBet * 5;
                            }

                            $totalWin += ($doubleBonus + $aceHunterBonus);

                            if($minTotalWin == -1 || ($minTotalWin > $totalWin && $totalWin > 0))
                            {
                                $minPlayerCards = $playerCards;
                                $minDealerCards = $dealerCards;
                                $minCommunityCards = $communityCards;
                                $minPlayerResult = $playerResult;
                                $minDealerResult = $dealerResult;
                                $minAnteWin = $anteWin;
                                $minTotalWin = $totalWin;
                                $minaceHunterBonus = $aceHunterBonus;
                                $mindoubleBonus = $doubleBonus;
                                $minqualified = $qualified;
                            }
                            
                            if($totalWin < $winLimit && $winType != 'none' && $totalWin > 0)
                            {
                                $spinAcquired = true;
                                break;
                            }
                            else if( $winType == 'none' && $totalWin == 0) 
                            {
                                break;
                            }
                        }

                        if(!$spinAcquired)
                        {
                            $playerCards = $minPlayerCards;
                            $dealerCards = $minDealerCards;
                            $communityCards = $minCommunityCards;
                            $playerResult = $minPlayerResult;
                            $dealerResult = $minDealerResult;
                            $anteWin = $minAnteWin;
                            $totalWin = $minTotalWin;
                            $aceHunterBonus = $minaceHunterBonus;
                            $doubleBonus = $mindoubleBonus;
                            $qualified = $minqualified;
                        }

                        $slotSettings->SetGameData($slotSettings->slotId . 'CommunityCards', $communityCards);
                        $slotSettings->SetGameData($slotSettings->slotId . 'PlayerCards', $playerCards);
                        $slotSettings->SetGameData($slotSettings->slotId . 'DealerCards', $dealerCards);
                        $slotSettings->SetGameData($slotSettings->slotId . 'PlayerResult', $playerResult);
                        $slotSettings->SetGameData($slotSettings->slotId . 'DealerResult', $dealerResult);
                        $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $totalWin);
                        $slotSettings->SetGameData($slotSettings->slotId . 'AnteWin', $anteWin);
                        $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', $aceHunterBonus + $doubleBonus);
                        $slotSettings->SetGameData($slotSettings->slotId . 'PlayerBet', $playerBet);
                        $slotSettings->SetGameData($slotSettings->slotId . 'BonusBet', $bonusBet);
                        $slotSettings->SetGameData($slotSettings->slotId . 'DealerQualified', $qualified);                        
                        
                        $oldBalance = $slotSettings->GetBalance();
                        if($allbet > 0)
                        {
                            $slotSettings->SetBalance(-1 * $allbet, '');
                            $bankSum = $allbet / 100 * $slotSettings->GetPercent();
                            $slotSettings->SetBank('', $bankSum, '');
                            $slotSettings->UpdateJackpots($allbet);
                            $slotSettings->SetBet($allbet);
                        }

                        $flopCards = [];
                        $flopCards[] = $this->getCardForResponse($communityCards[0]);
                        $flopCards[] = $this->getCardForResponse($communityCards[1]);
                        $flopCards[] = $this->getCardForResponse($communityCards[2]);
                        $heroCards = [];
                        $heroCards[] = $this->getCardForResponse($playerCards[0]);
                        $heroCards[] = $this->getCardForResponse($playerCards[1]);

                        $ret = [
                            ['balance'=>$oldBalance, 'casinoData' => [], 'freeBalance'=>'0', 'method'=>'openPlay', 'playId'=>'24304556', 'sessionId'=>'3bfe8240-aacb-4499-9d31-ca163e375793'],
                            ['method'=>'saveData', 'sessionId'=>'3bfe8240-aacb-4499-9d31-ca163e375793'],
                            ['bet'=>$playerBet, 'method'=>'placeBet', 'playId'=>'24304556', 'sessionId'=>'3bfe8240-aacb-4499-9d31-ca163e375793'],
                            ['bet'=>$bonusBet, 'method'=>'placeBet', 'playId'=>'24304556', 'sessionId'=>'3bfe8240-aacb-4499-9d31-ca163e375793'],
                            [
                                'balance' => $slotSettings->GetBalance(), 'casinoData' => [], 'freeBalance'=>'0', 'freePlaysData' => [], 'method'=>'getBetResult',
                                'params' => [
                                    [
                                        'bonusInfo' => [
                                            [
                                                'bonusName'=>'Deal',
                                                'flopCards' => $flopCards,
                                                'handBetDistribution' => [$anteBet, $normalBet],
                                                'playerCards' => $heroCards,
                                                'totalHandBet'=> $playerBet
                                            ]
                                        ],
                                        'creditsWon'=>'0',
                                        'debug' => ['generatedSequence'=>'15413823150529'],
                                        'jackpotsInfo' => [],
                                        'type'=>'spin'
                                    ],
                                    [
                                        'bonusInfo' => [
                                            ['bet'=> $bonusBet / 2, 'bonusName'=>'AceHunterSideGame', 'creditsWon'=> $aceHunterBonus, 'sideBetWon'=> $aceHunterBonus > 0 ? true : false],
                                            ['bet'=> $bonusBet / 2, 'bonusName'=>'DoubleTroubleSideGame', 'creditsWon'=> $doubleBonus, 'sideBetWon'=> $doubleBonus > 0 ? true : false],
                                        ],
                                        'creditsWon'=> ($aceHunterBonus + $doubleBonus),
                                        'debug' => ['generatedSequence'=>'15413823150529'],
                                        'type'=>'sidegame'
                                    ]
                                ],
                                'playId'=>'24304556', 'playingFreePlay'=> false, 'sessionId'=>'3bfe8240-aacb-4499-9d31-ca163e375793'
                            ]
                        ];
                        $response = json_encode($ret);                       
                        break;
                    case 'placeBet':
                        //check if swap or play
                        if($postData[0]->params->params->actions[0]->action == 'play')
                        {
                            $communityCards = $slotSettings->GetGameData($slotSettings->slotId . 'CommunityCards');
                            $playerCards = $slotSettings->GetGameData($slotSettings->slotId . 'PlayerCards');
                            $dealerCards = $slotSettings->GetGameData($slotSettings->slotId . 'DealerCards');
                            $playerResult = $slotSettings->GetGameData($slotSettings->slotId . 'PlayerResult');
                            $dealerResult = $slotSettings->GetGameData($slotSettings->slotId . 'DealerResult');
                            $totalWin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin');
                            $anteWin = $slotSettings->GetGameData($slotSettings->slotId . 'AnteWin');
                            $bonusWin = $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin');
                            $playerBet = $slotSettings->GetGameData($slotSettings->slotId . 'PlayerBet');
                            $bonusBet = $slotSettings->GetGameData($slotSettings->slotId . 'BonusBet');
                            $qualified = $slotSettings->GetGameData($slotSettings->slotId . 'DealerQualified');
                            $allbet = $playerBet + $bonusBet;
                            $playWin = $totalWin - $bonusWin;
                            $anteBet = $playerBet * 0.25;
                            $normalBet = $playerBet - $anteBet;

                            if($totalWin > 0)
                            {
                                $slotSettings->SetBank('', -1 * $totalWin);
                                $slotSettings->SetBalance($totalWin); 
                                $slotSettings->SetWin($totalWin);
                            }
                        
                            $dealer = [];
                            $dealer[] = $this->getCardForResponse($dealerCards[0]);
                            $dealer[] = $this->getCardForResponse($dealerCards[1]);
                            $heroCards = [];
                            $heroCards[] = $this->getCardForResponse($playerCards[0]);
                            $heroCards[] = $this->getCardForResponse($playerCards[1]);
                            $flopCards = [];
                            $flopCards[] = $this->getCardForResponse($communityCards[0]);
                            $flopCards[] = $this->getCardForResponse($communityCards[1]);
                            $flopCards[] = $this->getCardForResponse($communityCards[2]);
                            $turn = [];
                            $turn[] = $this->getCardForResponse($communityCards[3]);
                            $river = [];
                            $river[] = $this->getCardForResponse($communityCards[4]);

                            $playerTotalCards = array_merge($playerCards, $communityCards);
                            $dealerTotalCards = array_merge($dealerCards, $communityCards);

                            $bestHandPlayer = $this->sortCards($playerTotalCards, $playerResult);
                            $bestHandDealer = $this->sortCards($dealerTotalCards, $dealerResult);

                            $handResult = $slotSettings->CompareHand($playerResult, $dealerResult);
                            $handStatus = '';
                            if($handResult == 1)
                                $handStatus = 'win';
                            else if($handResult == -1)
                                $handStatus = 'lost';
                            
                            $ret = [
                                ['bet'=>'0', 'method'=>'placeBet', 'playId'=>'24304556', 'sessionId'=>'3bfe8240-aacb-4499-9d31-ca163e375793'],
                                [
                                    'balance' => $slotSettings->GetBalance(), 'casinoData' => [], 'freeBalance'=>'0', 'freePlaysData' => [], 'method'=>'getBetResult',
                                    'params' => [
                                        [
                                            'bonusInfo' => [
                                                [
                                                    'betsWin' => [
                                                        'ante' => $anteWin,
                                                        'bet' => $playWin - $anteWin
                                                    ],
                                                    'bonusName'=>'SwapOrPlay',
                                                    'communityCards' => [
                                                        'flop' => $flopCards,
                                                        'turn' => $turn,
                                                        'river' => $river
                                                    ],
                                                    'creditsWon' => $playWin,
                                                    'dealerQualifies' => $qualified,
                                                    'dealerStats' => [
                                                        'best_game_id'=>$slotSettings->GetHandName($dealerResult[1]),
                                                        'hand' => $dealer,
                                                        'best_possible_hand' => $bestHandDealer
                                                    ],
                                                    'handStatus' => $handStatus,
                                                    'initialBets' => [
                                                        'ante' => $anteBet,
                                                        'bet' => $normalBet
                                                    ],
                                                    'playerStats' => [
                                                        'best_game_id'=>$slotSettings->GetHandName($playerResult[1]),
                                                        'hand' => $heroCards,
                                                        'best_possible_hand' => $bestHandPlayer
                                                    ],
                                                    'swapped' => false
                                                ]
                                            ],
                                            'creditsWon'=>$playWin,
                                            'debug' => ['generatedSequence'=>'15413823150529'],
                                            'jackpotsInfo' => [],
                                            'type'=>'spin'
                                        ],                                        
                                    ],
                                    'playId'=>'24304556', 'playingFreePlay'=> false, 'sessionId'=>'3bfe8240-aacb-4499-9d31-ca163e375793'
                                ]
                            ];
                            $response = json_encode($ret);
                            $slotSettings->SaveLogReport($response, $allbet, $totalWin, '');
                        }
                        else if($postData[0]->params->params->actions[0]->action == 'swap')
                        {
                            //generate flop again with player cards fixed
                            $oldcommunityCards = $slotSettings->GetGameData($slotSettings->slotId . 'CommunityCards');
                            $playerCards = $slotSettings->GetGameData($slotSettings->slotId . 'PlayerCards');
                            $playerBet = $slotSettings->GetGameData($slotSettings->slotId . 'PlayerBet');                            
                            $bonusBet = $slotSettings->GetGameData($slotSettings->slotId . 'BonusBet');  
                            $bonusWin = $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin');

                            $allbet = $playerBet + $bonusBet;                            
                            $anteBet = $playerBet * 0.25;
                            $normalBet = $playerBet - $anteBet;

                            $communityCards = [];
                            $dealerCards = [];
                            $totalWin = 0;                        
                            $winTypeTmp = $slotSettings->GetSpinSettings('bet', $allbet);
                            $winType = $winTypeTmp[0];
                            $winLimit = $winTypeTmp[1];
                            $spinAcquired = false;
                            $anteWin = 0;
    
                            $minDealerCards = [];
                            $minCommunityCards = [];
                            $minPlayerResult = [];
                            $minDealerResult = [];
                            $minTotalWin = -1;
                            $minAnteWin = 0;
                            $qualified = false;
                            $minqualified = false;
                            
                            for($i = 0; $i < 500; $i++){
                                $communityCards = [];                                
                                $aceHunterBonus = 0;
                                $doubleBonus = 0;
                                $totalWin = 0;
                                $anteWin = 0;
                                $qualified = false;
    
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
    
                                if($slotSettings->CompareHand($playerResult, $dealerResult) == 1)
                                {
                                    //check if dealer is qualified
                                    if($dealerResult[1] > 1 || ($dealerResult[1] == 1 && $dealerResult[2] >= 11) )
                                        $qualified = true;
    
                                    $anteMultiplier = 1;
                                    if($playerResult[1] == 5) //flush
                                        $anteMultiplier = 4;
                                    else if($playerResult[1] == 6) //full house
                                        $anteMultiplier = 5;
                                    else if($playerResult[1] == 7) //four of a kind
                                        $anteMultiplier = 10;
                                    else if($playerResult[1] == 8) //straight flush
                                        $anteMultiplier = 100;
                                    else if($playerResult[1] == 9) //royal flush
                                        $anteMultiplier = 200;
    
                                    if($qualified)
                                        $totalWin = $playerBet + $anteBet * $anteMultiplier + $normalBet;
                                    else
                                        $totalWin = $playerBet + $anteBet * $anteMultiplier;
    
                                    $anteWin = $anteBet * $anteMultiplier;
                                }
                                else if($slotSettings->CompareHand($playerResult, $dealerResult) == 0)
                                {
                                    continue;
                                }
    
                                $totalWin += $bonusWin;
    
                                if($minTotalWin == -1 || ($minTotalWin > $totalWin && $totalWin > 0))
                                {
                                    $minPlayerCards = $playerCards;
                                    $minDealerCards = $dealerCards;
                                    $minCommunityCards = $communityCards;
                                    $minPlayerResult = $playerResult;
                                    $minDealerResult = $dealerResult;
                                    $minAnteWin = $anteWin;
                                    $minTotalWin = $totalWin;
                                    $minqualified = $qualified;
                                }
                                
                                if($totalWin < $winLimit && $winType != 'none' && $totalWin > 0)
                                {
                                    $spinAcquired = true;
                                    break;
                                }
                                else if( $winType == 'none' && $totalWin == 0) 
                                {
                                    break;
                                }
                            }
    
                            if(!$spinAcquired)
                            {
                                $playerCards = $minPlayerCards;
                                $dealerCards = $minDealerCards;
                                $communityCards = $minCommunityCards;
                                $playerResult = $minPlayerResult;
                                $dealerResult = $minDealerResult;
                                $anteWin = $minAnteWin;
                                $totalWin = $minTotalWin;
                                $qualified = $minqualified;
                            }

                            if($totalWin > 0)
                            {
                                $slotSettings->SetBank('', -1 * $totalWin);
                                $slotSettings->SetBalance($totalWin); 
                                $slotSettings->SetWin($totalWin);
                            }
                        
                            $dealer = [];
                            $dealer[] = $this->getCardForResponse($dealerCards[0]);
                            $dealer[] = $this->getCardForResponse($dealerCards[1]);
                            $heroCards = [];
                            $heroCards[] = $this->getCardForResponse($playerCards[0]);
                            $heroCards[] = $this->getCardForResponse($playerCards[1]);
                            $flopCards = [];
                            $flopCards[] = $this->getCardForResponse($communityCards[0]);
                            $flopCards[] = $this->getCardForResponse($communityCards[1]);
                            $flopCards[] = $this->getCardForResponse($communityCards[2]);
                            $oldFlops = [];
                            $oldFlops[] = $this->getCardForResponse($oldcommunityCards[0]);
                            $oldFlops[] = $this->getCardForResponse($oldcommunityCards[1]);
                            $oldFlops[] = $this->getCardForResponse($oldcommunityCards[2]);
                            $turn = [];
                            $turn[] = $this->getCardForResponse($communityCards[3]);
                            $river = [];
                            $river[] = $this->getCardForResponse($communityCards[4]);

                            $playerTotalCards = array_merge($playerCards, $communityCards);
                            $dealerTotalCards = array_merge($dealerCards, $communityCards);

                            $bestHandPlayer = $this->sortCards($playerTotalCards, $playerResult);
                            $bestHandDealer = $this->sortCards($dealerTotalCards, $dealerResult);

                            $handResult = $slotSettings->CompareHand($playerResult, $dealerResult);
                            $handStatus = '';
                            if($handResult == 1)
                                $handStatus = 'win';
                            else if($handResult == -1)
                                $handStatus = 'lost';

                            $playWin = $totalWin - $bonusWin;
                            $ret = [
                                ['bet'=>'0', 'method'=>'placeBet', 'playId'=>'24304556', 'sessionId'=>'3bfe8240-aacb-4499-9d31-ca163e375793'],
                                [
                                    'balance' => $slotSettings->GetBalance(), 'casinoData' => [], 'freeBalance'=>'0', 'freePlaysData' => [], 'method'=>'getBetResult',
                                    'params' => [
                                        [
                                            'bonusInfo' => [
                                                [
                                                    'betsWin' => [
                                                        'ante' => $anteWin,
                                                        'bet' => $playWin - $anteWin
                                                    ],
                                                    'bonusName'=>'SwapOrPlay',
                                                    'communityCards' => [
                                                        'flop' => $flopCards,
                                                        'turn' => $turn,
                                                        'river' => $river
                                                    ],
                                                    'creditsWon' => $playWin,
                                                    'dealerQualifies' => $qualified,
                                                    'dealerStats' => [
                                                        'best_game_id'=>$slotSettings->GetHandName($dealerResult[1]),
                                                        'hand' => $dealer,
                                                        'best_possible_hand' => $bestHandDealer
                                                    ],
                                                    'handStatus' => $handStatus,
                                                    'initialBets' => [
                                                        'ante' => $anteBet,
                                                        'bet' => $normalBet
                                                    ],
                                                    'playerStats' => [
                                                        'best_game_id'=>$slotSettings->GetHandName($playerResult[1]),
                                                        'hand' => $heroCards,
                                                        'best_possible_hand' => $bestHandPlayer
                                                    ],
                                                    'swapped' => true
                                                ]
                                            ],
                                            'creditsWon'=>$playWin,
                                            'debug' => ['generatedSequence'=>'15413823150529'],
                                            'jackpotsInfo' => [],
                                            'type'=>'spin'
                                        ],                                        
                                    ],
                                    'playId'=>'24304556', 'playingFreePlay'=> false, 'sessionId'=>'3bfe8240-aacb-4499-9d31-ca163e375793'
                                ]
                            ];
                            $response = json_encode($ret);
                            $slotSettings->SaveLogReport($response, $allbet, $totalWin, '');
                        }

                        break;
                    case 'settlePlay':
                        $ret = [
                            ['balance'=>$slotSettings->GetBalance(), 'casinoData' => [], 'freeBalance'=>'0', 'method'=>'openPlay', 'playId'=>'24304556', 'sessionId'=>'3bfe8240-aacb-4499-9d31-ca163e375793'],
                            ['casinoData' => [], 'freePlaysData' => [], 'method'=>'closePlay', 'sessionId'=>'3bfe8240-aacb-4499-9d31-ca163e375793']
                        ];
                        $response = json_encode($ret);
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


