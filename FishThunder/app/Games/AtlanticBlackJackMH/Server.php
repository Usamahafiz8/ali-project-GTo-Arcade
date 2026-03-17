<?php 

namespace VanguardLTE\Games\AtlanticBlackJackMH
{

    use Illuminate\Support\Facades\DB;
    use SimpleXMLElement;

    set_time_limit(5);
    class Server
    {
        private function deepClone($array)
        {
            $ret = [];
            foreach ($array as $key => $item) {
                $ret[$key] = $this->deepClone($item);
            }
            return $ret;
        }

        private function getActions($hand, $dealer)
        {
            $actions = [];
            $dealer_cards = $dealer['cards'];
            $card = $hand['cards'];
            if($dealer_cards[1][1] == 11 && !isset($dealer['no_insurance'])) //if dealer show card is ace
            {
                $actions[] = 'insurance';
                return $actions;
            }
            $total = $this->getTotal($card);
            $val1 = $card[0][1];
            $val2 = $card[1][1];
            if($total < 21)
            {
                $actions[] = 'hit';
                if($hand['doubled'] == false)
                    $actions[] = 'double';
                $actions[] = 'stand';                
            }
            if($val1 == $val2 && count($card) == 2)
            {
                $actions[] = 'split';
            }
            return $actions;
        }

        public function getNextHand($players, &$position, &$curhandPosition)
        {
            $isNextAvailable = false;
            $positions = array_keys($players);
            $curKey = -1;
            for($i = 0; $i < count($positions); $i++)
            {
                if($positions[$i] == $position)
                {
                    $curKey = $i;
                    break;
                }
            }

            $hands = $players[$position]['hands'];
            if(count($hands) > $curhandPosition + 1)
            {
                for($i = $curhandPosition + 1; $i < count($hands); $i++)
                {
                    $hand = $hands[$i];
                    if($hand['result'] != 'busted' || $hand['result'] != 'blackjack')
                    {
                        $isNextAvailable = true;
                        $curhandPosition = $i;
                        break;
                    }
                }
            }

            if($isNextAvailable) //if next hand is found in the current player
                return $isNextAvailable;
            
            if($curKey == count($positions) - 1) //if current position is last player position
                return false;

            $searchKey = $curKey + 1;
            while($searchKey < count($positions)) //iterate players array from next of current position
            {
                $searchPosition = $positions[$searchKey];
                $player = $players[$searchPosition];
                $hands = $player['hands'];
                for($i = 0; $i < count($hands); $i++)
                {
                    $hand = $hands[$i];
                    if($hand['result'] != 'busted' && $hand['result'] != 'blackjack')
                    {
                        $isNextAvailable = true;
                        $position = $searchPosition;
                        $curhandPosition = $i;
                        break;
                    }
                }
                if($isNextAvailable)
                    break;
                $searchKey++;
            }

            return $isNextAvailable;
        }

        function sxml_append(SimpleXMLElement $to, SimpleXMLElement $from) {
            $toDom = dom_import_simplexml($to);
            $fromDom = dom_import_simplexml($from);
            $toDom->appendChild($toDom->ownerDocument->importNode($fromDom, true));
        }
        
        function getTotal($cards)
        {
            $total = 0;
            foreach($cards as $card)
            {
                if($card[1] > 11)
                {
                    $total += 10;
                }
                else
                    $total += $card[1];                
            }
            if($total > 21)
            {
                foreach($cards as $card)
                {
                    if($card[1] == 11)
                        $total -= 10;
                    if($total <= 21)
                        break;
                }
            }
            return $total;
        }

        function getPlayersString($players, $dealer, $balance, $curPlayerPos, $curPlayerHand)
        {
            $playersStr = '<players>';
            foreach($players as $player)
            {
                $position = $player['position'];
                $playersStr .= '<player position="'.$position.'" pbet="0" pwin="0.0">';
                $handIndex = 0;
                foreach($player['hands'] as $hand)
                {
                    $cards = $hand['cards'];
                    $bet = $hand['bet'];
                    $total = $this->getTotal($cards);
                    $resultStr = '';
                    $doubledStr = '';
                    if($hand['result'] != '')
                        $resultStr = 'result="'.$hand['result'].'"';
                    if($hand['doubled'] != false)
                        $doubledStr = 'doubled="true"';
                    $insuranceStr = '';
                    if(isset($hand['insurance']))
                        $insuranceStr = 'insurance="'.$hand['insurance'].'"';
                    $playersStr .= '<hand hand="'.$handIndex.'" bet="'.$bet.'" win="'.$hand['win'].'" total="'.$total.'" '.$resultStr.' '.$doubledStr.' '.$insuranceStr.'>';
                    foreach($cards as $card)
                    {
                        $playersStr .= '<card suit="'.$card[0].'" value="'.$card[1].'" />';
                    }
                    $playersStr .= '</hand>';
                    $handIndex++;
                }
                
                $playersStr .= '</player>';
            }
            $playersStr .= '</players>';
            
            $blackjackstatus = simplexml_load_string('<blackjackstatus win="0.0" chips="'.$balance.'" promochips="0.0" missedwin="0.0"><game class="blackjack" vers="1" tournament="0" instance="47"><type mode="G" id="" /></game></blackjackstatus>');
            if($curPlayerPos != -1)
            {
                $curPlayer = $players[$curPlayerPos];
                $curHand = $curPlayer['hands'][$curPlayerHand];

                $actionsStr = '<actions position="'.$curPlayer['position'].'" hand="'.$curPlayerHand.'">';
                $actions = $this->getActions($curHand, $dealer);
                foreach($actions as $action)
                {
                    $actionsStr .= '<action type="'.$action.'" />';
                }
                $actionsStr .= '</actions>';
            }
            else
            {
                //this means game end and next action is deal
                $keys = array_keys($players);
                $actionsStr = '<actions position="'.$keys[0].'" hand="0"><action type="deal"/></actions>';
            }
            $actionsXML = simplexml_load_string($actionsStr);

            $dealerResult = '';
            if($dealer['result'] != '')
                $dealerResult = 'result="'.$dealer['result'].'"';
            $dealerStr = '<dealer total="'.$this->getTotal($dealer['cards']).'" '.$dealerResult.'><hand>';
            foreach($dealer['cards'] as $card)
            {
                $dealerStr .= '<card suit="'.$card[0].'" value="'.$card[1].'" />';
            }
            $dealerStr .= '</hand></dealer>';

            $dealerXML = simplexml_load_string($dealerStr);
            $playersXML = simplexml_load_string($playersStr);
            $this->sxml_append($blackjackstatus, $actionsXML);
            $this->sxml_append($blackjackstatus, $dealerXML);
            $this->sxml_append($blackjackstatus, $playersXML);
            $xml = $blackjackstatus->asXML();
            return $xml;
        }

        public function generateCard()
        {
            $card = [rand(1, 4), rand(2, 14)];
            return $card;
        }

        public function get($request, $game)
        {
            try
            {
                $debug = false;
                DB::beginTransaction();
                $userId = \Auth::id();
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
                $postData = $request->all();
                $xmlRequest = simplexml_load_string($postData['xmlRequest']);
                $cmd = $xmlRequest->getName();
                $result_tmp = [];                
                
                switch( $cmd) 
                {
                    case 'login':
                        $ret = [
                            'creationTime'=> time(),
                            'expiryTime' => time() + 600 * 1000,
                            'securityToken' => 'demo0000023681-1fadyeqtardzz3dpw3zj7hggg-',
                            'serverTime' => time(),
                            'ttl' => '600000',
                            'xmlResponse'=>'<?xml version="1.0" encoding="UTF-8"?>
                            <loginresponse realmoney="demo" machineid="" accountNo="" ml="0" popupmessage="0" resetpassword="0" show_games="" hide_games="" feature_games="" trending_games="" currency="" />'
                        ];
                        $response = json_encode($ret);
                        break;
                    case 'startgame':
                        $ret = [
                            'securityToken' => 'demo0000023681-1fadyeqtardzz3dpw3zj7hggg-',
                            'xmlResponse'=>'<?xml version="1.0" encoding="UTF-8"?>
                            <blackjackstatus win="0.0" chips="'.$slotSettings->GetBalance().'" promochips="0.0" missedwin="0.0"><game class="blackjack" vers="1" tournament="0" instance="47"><type mode="G" id="" /></game><actions position="1" hand="0"><action type="deal" /></actions><dealer total="0"><hand /></dealer><players /></blackjackstatus>'
                        ];
                        $response = json_encode($ret);
                        $slotSettings->SetGameData($slotSettings->slotId . 'Players', []);
                        $slotSettings->SetGameData($slotSettings->slotId . 'Dealer', []);
                        $slotSettings->SetGameData($slotSettings->slotId . 'Allbet', 0);
                        break;
                    case 'blackjackoperation':
                        $operation = (string)$xmlRequest['action'];
                        $players = $slotSettings->GetGameData($slotSettings->slotId . 'Players');
                        $dealer = $slotSettings->GetGameData($slotSettings->slotId . 'Dealer');
                        switch($operation)
                        {
                            case 'deal': //deal means start a game
                                $allbet = 0;
                                $dealer = [];     
                                $dealer['cards'] = [];
                                $dealer['result'] = '';
                                $dealer['cards'][]= [0, 0];
                                $dealer['cards'][] = $this->generateCard();
                                if($debug)
                                    $dealer['cards'][1] = [rand(1,4), 11];
                                $firstPlayerPos = -1;
                                foreach($xmlRequest->bets->children() as $key => $value)
                                {
                                    $position = (string)$value['position'];
                                    if($firstPlayerPos == -1)
                                        $firstPlayerPos = $position;
                                    $amount = (string)$value['amount'];
                                    $allbet += $amount;
                                    $cards = [];
                                    $cards[] = $this->generateCard();
                                    $cards[] = $this->generateCard();                                    

                                    $players[$position] = [
                                        'position' => $position,
                                        'hands' => []];
                                    $result = '';
                                    $total = $this->getTotal($cards);
                                    if($total == 21)
                                        $result = 'blackjack';
                                    else if($total > 21)
                                        $result = 'busted';
                                    $players[$position]['hands'][0] = ['bet' => $amount, 'cards' => $cards, 'result' => $result, 'win' => 0, 'doubled' => false];
                                }
                                
                                $slotSettings->SetBalance(-1 * $allbet, '');
                                $bankSum = $allbet / 100 * $slotSettings->GetPercent();
                                $slotSettings->SetBank('', $bankSum, '');
                                $slotSettings->UpdateJackpots($allbet);                                
                                $slotSettings->SetBet($allbet);
                                
                                //save player info
                                $slotSettings->SetGameData($slotSettings->slotId . 'Allbet', $allbet);
                                $slotSettings->SetGameData($slotSettings->slotId . 'Players', $players);
                                $slotSettings->SetGameData($slotSettings->slotId . 'Dealer', $dealer);

                                $firstHand = 0;
                                if($players[$firstPlayerPos]['hands'][$firstHand]['result'] == 'blackjack')
                                    $this->getNextHand($players, $firstPlayerPos, $firstHand);
                                $xml = $this->getPlayersString($players, $dealer, $slotSettings->GetBalance(), $firstPlayerPos, $firstHand);
                                $ret = ['securityToken'=>'demo0000024206-cb03wisr1vf21fgt2m5buyo23-', 'xmlResponse' => $xml];
                                $response = json_encode($ret);
                                break;
                            case 'hit':
                            case 'double':
                            case 'stand':
                                $position = (string)$xmlRequest['position'];
                                $handPointer = (string)$xmlRequest['hand'];
                                $hand = &$players[$position]['hands'][$handPointer];
                                $isNextAvailable = true;
                                if($operation == 'double')
                                {
                                    //double bet amount
                                    $newBet = (int)$hand['bet'];
                                    $hand['bet'] += $newBet;
                                    $hand['doubled'] = true;
                                    $slotSettings->SetBalance(-1 * $newBet, '');
                                    $bankSum = $newBet / 100 * $slotSettings->GetPercent();
                                    $slotSettings->SetBank('', $bankSum, '');
                                    $slotSettings->UpdateJackpots($newBet);       
                                    $slotSettings->SetBet($newBet);

                                    $slotSettings->SetGameData($slotSettings->slotId . 'Allbet', $slotSettings->GetGameData($slotSettings->slotId . 'Allbet') + $newBet);
                                }

                                if($operation != 'stand')
                                {
                                    //deal new card
                                    $hand['cards'][] = $this->generateCard();
                                    $total = $this->getTotal($hand['cards']);
                                    if($total >= 21)
                                    {
                                        if($total > 21)
                                            $hand['result'] = 'busted';
                                        else
                                            $hand['result'] = 'blackjack';
                                        $isNextAvailable = $this->getNextHand($players, $position, $handPointer);
                                    }
                                }
                                else
                                {
                                    $isNextAvailable = $this->getNextHand($players, $position, $handPointer);
                                }
                                

                                $slotSettings->SetGameData($slotSettings->slotId . 'Players', $players);
                                
                                if($isNextAvailable)
                                {
                                    $xml = $this->getPlayersString($players, $dealer, $slotSettings->GetBalance(), $position, $handPointer);
                                    $ret = ['securityToken'=>'demo0000024206-cb03wisr1vf21fgt2m5buyo23-', 'xmlResponse' => $xml];
                                }
                                else
                                {
                                    //game end
                                    $winTypeTmp = $slotSettings->GetSpinSettings('bet', $slotSettings->GetGameData($slotSettings->slotId . 'Allbet'), 1);
                                    $winType = $winTypeTmp[0];
                                    $winLimit = $winTypeTmp[1];

                                    $minTotalWin = -1;
                                    $minPlayers = [];
                                    $minDealer = [];
                                    $spinAcquired = false;
                                    for($i = 0; $i < 500; $i++)
                                    {
                                        $spinAcquired = false;
                                        $dealer = $slotSettings->GetGameData($slotSettings->slotId . 'Dealer');
                                        $players = $slotSettings->GetGameData($slotSettings->slotId . 'Players');
                                        if(isset($dealer['no_insurance']))
                                            $dealer['cards'][0] = [rand(1, 4), rand(1, 9)];
                                        else
                                            $dealer['cards'][0] = $this->generateCard(); //insert card to hidden card
                                        
                                        $dealer_total = $this->getTotal($dealer['cards']);
                                        $dealer_result = '';
                                        if($dealer_total == 21) //if 21 with 2 cards then blackjack
                                            $dealer_result = 'blackjack';
                                        while($dealer_total < 17)
                                        {
                                            $dealer['cards'][] = $this->generateCard();
                                            $dealer_total = $this->getTotal($dealer['cards']);
                                        }
                                        
                                        if($dealer_total > 21)
                                            $dealer_result = 'busted';
                                        
                                        $dealer['result'] = $dealer_result;

                                        $totalWin = 0;
                                        foreach($players as &$player)
                                        {
                                            foreach($player['hands'] as &$hand)
                                            {
                                                if($dealer_result == 'blackjack')
                                                {
                                                    if($hand['result'] == 'blackjack')
                                                    {
                                                        $hand['result'] = 'push';
                                                        $hand['win'] = $hand['bet'];
                                                    }                                                    
                                                }
                                                else if($dealer_result == 'busted')
                                                {
                                                    if($hand['result'] == 'blackjack')
                                                    {
                                                        $hand['win'] = $hand['bet'] * 2.5;
                                                    }
                                                    else if($hand['result'] != 'busted')
                                                    {
                                                        $hand['win'] = $hand['bet'] * 2;
                                                        $hand['result'] = 'win';
                                                    }                                                    
                                                }
                                                else
                                                {
                                                    //dealer not busted
                                                    if($hand['result'] != 'busted')                                                   
                                                    {
                                                        //user not busted
                                                        $hand_total = $this->getTotal($hand['cards']);
                                                        if($dealer_total < $hand_total)
                                                        {
                                                            //player win
                                                            $hand['result'] = 'win';
                                                            if($hand['result'] == 'blackjack')
                                                                $hand['win'] = $hand['bet'] * 2.5;
                                                            else
                                                                $hand['win'] = $hand['bet'] * 2;
                                                        }
                                                        else if($dealer_total == $hand_total)
                                                        {
                                                            //push win
                                                            $hand['result'] = 'push';
                                                            $hand['win'] = $hand['bet'];
                                                        }
                                                    }                                                    
                                                }
                                                $totalWin += $hand['win'];
                                            }
                                        }

                                        if($minTotalWin == -1 || $totalWin < $minTotalWin)
                                        {
                                            $minTotalWin = $totalWin;
                                            $minPlayers = $players;
                                            $minDealer = $dealer;                                            
                                        }

                                        if($totalWin < $winLimit)
                                        {
                                            $spinAcquired = true;
                                            break;
                                        }
                                    }

                                    if(!$spinAcquired)
                                    {
                                        $totalWin = $minTotalWin;
                                        $players = $minPlayers;
                                        $dealer = $minDealer; 
                                    }

                                    if($totalWin > 0)
                                    {
                                        $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), -1 * $totalWin);
                                        $slotSettings->SetBalance($totalWin);
                                        $slotSettings->SetWin($totalWin);
                                    }

                                    $xml = $this->getPlayersString($players, $dealer, $slotSettings->GetBalance(), -1, 0); //game end
                                    $ret = ['securityToken'=>'demo0000024206-cb03wisr1vf21fgt2m5buyo23-', 'xmlResponse' => $xml];
                                    
                                    $allbet = $slotSettings->GetGameData($slotSettings->slotId . 'Allbet');
                                    $slotSettings->SaveLogReport($xml, $allbet, $totalWin, '');
                                }
                                
                                $response = json_encode($ret);
                                break;
                            case 'split':
                                $position = (string)$xmlRequest['position'];
                                $handPointer = (string)$xmlRequest['hand'];
                                $hand = &$players[$position]['hands'][$handPointer];
                                $cards = &$hand['cards']; //split cards into two hands
                                $cards[1] = $this->generateCard();
                                $result = '';
                                $total = $this->getTotal($cards);
                                if($total == 21)
                                    $result = 'blackjack';
                                else if($total > 21)
                                    $result = 'busted';
                                $hand['result'] = $result;
                                
                                $new_cards = [];
                                $new_cards[0] = $cards[0];
                                $new_cards[] = $this->generateCard();
                                $total = $this->getTotal($new_cards);
                                if($total == 21)
                                    $result = 'blackjack';
                                else if($total > 21)
                                    $result = 'busted';
                                $newHand = ['bet' => $hand['bet'], 'cards' => $new_cards, 'result' => $result, 'win' => 0, 'doubled' => false];
                                $newBet = $hand['bet'];
                                $slotSettings->SetBalance(-1 * $newBet, '');
                                $bankSum = $newBet / 100 * $slotSettings->GetPercent();
                                $slotSettings->SetBank('', $bankSum, '');
                                $slotSettings->UpdateJackpots($newBet);       
                                $slotSettings->SetBet($newBet);

                                $slotSettings->SetGameData($slotSettings->slotId . 'Allbet', $slotSettings->GetGameData($slotSettings->slotId . 'Allbet') + $newBet);

                                //insert new hand next to the current hand pointer
                                $players[$position]['hands'][] = $newHand;
                                $slotSettings->SetGameData($slotSettings->slotId . 'Players', $players);
                                $xml = $this->getPlayersString($players, $dealer, $slotSettings->GetBalance(), $position, $handPointer);
                                $ret = ['securityToken'=>'demo0000024206-cb03wisr1vf21fgt2m5buyo23-', 'xmlResponse' => $xml];
                                $response = json_encode($ret);
                                break;
                            case 'insurance':
                                $insuranceBet = 0;
                                foreach($xmlRequest->bets->children() as $key => $value)
                                {
                                    $position = (string)$value['position'];
                                    $players[$position]['hands'][0]['insurance'] = $players[$position]['hands'][0]['bet'] / 2;                                    
                                    $insuranceBet += $players[$position]['hands'][0]['insurance'];
                                }
                                if($insuranceBet > 0)
                                {
                                    $slotSettings->SetBalance(-1 * $insuranceBet, '');
                                    $bankSum = $insuranceBet / 100 * $slotSettings->GetPercent();
                                    $slotSettings->SetBank('', $bankSum, '');
                                    $slotSettings->UpdateJackpots($insuranceBet);       
                                    $slotSettings->SetBet($insuranceBet);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'Allbet', $slotSettings->GetGameData($slotSettings->slotId . 'Allbet') + $insuranceBet);
                                }

                                if(rand(0, 100) < 10)
                                {
                                    //make dealer as blackjack
                                    $dealer['cards'][0] = [rand(1, 4), rand(12, 14)];
                                    $dealer['result'] = 'blackjack';
                                    $totalWin = 0;
                                    foreach($players as &$player)
                                    {
                                        foreach($player['hands'] as &$hand)
                                        {
                                            if(isset($hand['insurance']))
                                            {
                                                $hand['result'] = 'win';
                                                $hand['win'] = $hand['bet'] + $hand['insurance'];
                                                $totalWin += $hand['win'];
                                            }
                                        }
                                    }
                                    if($totalWin > 0)
                                    {
                                        $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), -1 * $totalWin);
                                        $slotSettings->SetBalance($totalWin);
                                        $slotSettings->SetWin($totalWin);
                                    }
                                    $xml = $this->getPlayersString($players, $dealer, $slotSettings->GetBalance(), -1, 0); //game end
                                    $ret = ['securityToken'=>'demo0000024206-cb03wisr1vf21fgt2m5buyo23-', 'xmlResponse' => $xml];
                                    
                                    $allbet = $slotSettings->GetGameData($slotSettings->slotId . 'Allbet');
                                    $slotSettings->SaveLogReport($xml, $allbet, $totalWin, '');
                                }
                                else
                                {
                                    //if dealer not blackjack, start normal round
                                    $dealer['no_insurance'] = true;
                                    $slotSettings->SetGameData($slotSettings->slotId . 'Dealer', $dealer);
                                    $keys = array_keys($players);
                                    $firstPlayerPos = $keys[0];
                                    $firstHand = 0;
                                    if($players[$firstPlayerPos]['hands'][$firstHand]['result'] == 'blackjack')
                                        $this->getNextHand($players, $firstPlayerPos, $firstHand);
                                    $xml = $this->getPlayersString($players, $dealer, $slotSettings->GetBalance(), $firstPlayerPos, $firstHand);
                                    $ret = ['securityToken'=>'demo0000024206-cb03wisr1vf21fgt2m5buyo23-', 'xmlResponse' => $xml];                                    
                                }

                                $slotSettings->SetGameData($slotSettings->slotId . 'Players', $players);
                                $response = json_encode($ret);
                                break;
                        }
                        break;
                }
                DB::commit();
                $slotSettings->SaveGameData();
                $slotSettings->SaveGameDataStatic();
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
                    $strLog .= "\nplayerId: " . $userId . " gameName: " . $game;
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
