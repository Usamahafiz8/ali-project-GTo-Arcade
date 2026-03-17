<?php 
namespace VanguardLTE\Games\CoinPusherPirateKing
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
                
                $postData = $request->all();
                $gameData = $postData['gameData'];
                $msgid = $gameData['msgid'];
                $reportWin = 0;
                $result_tmp = [];
                switch( $msgid ) 
                {
                    case 100: //login
                        $result_tmp[] = json_encode(['msgid' => 101, 'data' => ['status_code' => 0]]);
                        $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', '');
                        break;
                    case 102:
                        $data = $this->doInit($slotSettings);
                        $result_tmp[] = json_encode(['msgid' => 103, 'data' => $data]);
                        break;
                    case 104:
                        $data = [
                            'status_code' => 0,
                            'type' => 1,
                            'lucky_draw_config' => null,
                            'pool_list' => [
                                    [
                                        'id' => 0,
                                        'name' => '',
                                        'current_cent' => [
                                            'low' => 0,
                                            'high' => 0,
                                            'unsigned' => true
                                        ],
                                        'enable' => false,
                                        'max_cent' => null
                                    ],
                                    [
                                        'id' => 1,
                                        'name' => '',
                                        'current_cent' => [
                                            'low' => 0,
                                            'high' => 0,
                                            'unsigned' => true
                                        ],
                                        'enable' => false,
                                        'max_cent' => null
                                    ],
                                    [
                                        'id' => 2,
                                        'name' => '',
                                        'current_cent' => [
                                            'low' => 0,
                                            'high' => 0,
                                            'unsigned' => true
                                        ],
                                        'enable' => false,
                                        'max_cent' => null
                                    ],
                                    [
                                        'id' => 3,
                                        'name' => '',
                                        'current_cent' => [
                                            'low' => 0,
                                            'high' => 0,
                                            'unsigned' => true
                                        ],
                                        'enable' => false,
                                        'max_cent' => null
                                    ]
                                ]
                            ];
                            $result_tmp[] = json_encode(['msgid' => 202, 'data' => $data]);
                            $result_tmp[] = ':::'.json_encode(['msgid' => 105, 'data' => ['status_code' => 0]]);
                        break;
                    case 106: //bet request
                        $postData = $gameData;
                        $postData['slotEvent'] = 'bet';
                        $response = null;
                        $spinStatus = $slotSettings->GetGameData($slotSettings->slotId . 'SpinStatus');
                        if($spinStatus === 'Freespin')
                        {
                            $postData['slotEvent'] = 'freespin';
                        }
                        
                        $multiCnt = count($postData['multiBet']);
                        $bet = $slotSettings->bets[$postData['bet_index']] / 10;
                        
                        $allbet = $bet * $multiCnt;
                        $slotSettings->SetBalance(-1 * $allbet, $postData['slotEvent']);
                        $bankSum = $allbet / 100 * $slotSettings->GetPercent();
                        $slotSettings->SetBet($allbet);
                        $slotSettings->UpdateJackpots($allbet);

                        if( $postData['slotEvent'] != 'freespin' ) 
                        {                            
                            $slotSettings->SetGameData($slotSettings->slotId . 'TotalGameWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                        }
                        else
                        {
                            
                        }
                        
                        $data = $this->doSpin($slotSettings, $postData);
                        $result_tmp[] = json_encode(['msgid' => 107, 'data' => $data]);                        
                        break;    
                    case 112:
                        $result_tmp[] = json_encode(['msgid' => 113, 'data' => ['status_code' => 0]]);
                        break;                
                    default:
                        break;
                }
                
                $slotSettings->SaveGameData();
                $slotSettings->SaveGameDataStatic();
                DB::commit();          
                $response = implode('------', $result_tmp);
                return ':::' .$response;                     
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

        function doInit($slotSettings)
        {
            //generate coin data per bet index
            $count = count($slotSettings->bets);
            $pusherData = [];
            for($i = 0; $i < $count; $i++)
            {
                $pusherData[] = [-1, -1, -1, -1, -1, -1, 4, 3, 4, -1, -1, 7, 5, 7, -1, -1, -1, -1, -1, -1, -1, 8, 5, 8, -1, -1, 8, 7, 8, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1];
            }

            $pusherDataStr = json_encode($pusherData);
            $slotSettings->SetGameData($slotSettings->slotId . 'PusherData', $pusherDataStr);

            $data = [
                'status_code' => 0,
                'bet_5_arr' => $slotSettings->bets,
                'line_5_arr' => [1],
                'rate_arr' => [10],
                'rate_default_index' => 0,
                'language_list' => [],
                'language_default_index' => null,
                'player_cent' => [
                    'low' => $this->getCreditNumber($slotSettings->GetBalance()),
                    'high' => 0,
                    'unsigned' => true
                ],
                'last_rng' => [],
                'recover_data' => null,
                'bet_config_list' => [['bet_arr' => $slotSettings->bets]],
                'last_bs_result' => null,
                'member_info' => null,
                'local_jp_list' => [],
                'ps_reward_rules' => null,
                'is_free_game' => false,
                'free_game_info' => null,
                'has_lobby_logged' => false,
                'accounting_unit' => 100,
                'module_config' => [],
                'bet_pays' => [],
                'max_bets' => [],
                'extra_datas' => [
                        [
                            'data' => $pusherData[0]
                        ]
                    ],
                    'last_fs_result_list' => [],
                    'common_datas' => [],
                    'village_infor' => null
                ];
            return $data;
        }
        
        function doSpin($slotSettings, $postData)
        {
            $reelSetIndex = 0;
            if($postData['slotEvent'] == 'freespin')
            {                
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bonus');
            }
            else
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bet');
            }

            $multiCnt = count($postData['multiBet']);
            $bet = $slotSettings->bets[$postData['bet_index']] / 10;
            $allbet = $bet * $multiCnt;

            $spinAcquired = false;             

            $minTotalWin = -1;
            $minFreespinsWon = 0;

            $totalWin = 0;
            $totalMultiplier = 0;
            $freespinsWon = 0;
            $win_multipliers = [];
            $bonus_hit_data = [];
            $feature = -1;
            
            if($this->debug && $postData['slotEvent'] != 'freespin')
            {                 
                $winType = 'bonus';
            }

            $coinPusherData = json_decode($slotSettings->GetGameData($slotSettings->slotId . 'PusherData'), true);
            $activePusherData = $postData['position'];            
            $payTable = [
                0 => 30,
                1 => 25,
                2 => 20,
                3 => 15,
                4 => 10,
                5 => 8,
                6 => 5,
                7 => 3,
                8 => 2
            ];

            //check big syms
            $bigSymCnt = 0;
            for($r = 0; $r < 8; $r++)
                for($c = 0; $c < 5; $c++)
                {
                    if($activePusherData[$r * 5 + $c] < 7 && $activePusherData[$r * 5 + $c] > -1)
                        $bigSymCnt++;
                }

            //fill some big symbols if big symbols are smaller than 3
            if($bigSymCnt < 3)
            {
                while($bigSymCnt < 3)
                {
                    $r = rand(0, 5);
                    $c = rand(0, 5);
                    if($activePusherData[$r * 5 + $c] == -1)
                    {
                        $activePusherData[$r * 5 + $c] = rand(0, 7);
                        $bigSymCnt++;
                    }
                }
            }

            for($coin = 0; $coin < $multiCnt; $coin++)
            {
                $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $bet);
                $winType = $winTypeTmp[0];
                $spinWinLimit = $winTypeTmp[1];

                // $winType = 'win';
                // $spinWinLimit = 10;

                $coinMultiplier = 0;
                if($winType == 'win' || $winType == 'bonus')
                {    
                    if($winType == 'bonus' && $postData['slotEvent'] != 'freespin')
                    {
                        $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', 'Freespin');
                        $slotSettings->SetGameData($slotSettings->slotId . 'TotalFreespinWin', 0);
                        $feature = rand(2, 6); //2: pusher, 3: coin spray, 4: flip, 5: wall, 6: area

                        $pendingMultiplier = 0; //sum of all coins
                        $smallCoinMultiplier = 0; //sum of all small coins
                        for($i = 0; $i < count($activePusherData); $i++)
                        {
                            if($activePusherData[$i] != -1)
                            {
                                $pendingMultiplier += $payTable[$activePusherData[$i]];                                
                            }
                                
                            else
                            {
                                $pendingMultiplier += 1;
                                $smallCoinMultiplier += 1;
                            }                                
                        }

                        if($feature == 4)
                        {
                            if($spinWinLimit * 10 / $bet < $pendingMultiplier)
                                $feature = 3;
                        }
                        
                        $slotSettings->SetGameData($slotSettings->slotId . 'Feature', $feature);
    
                        switch($feature)
                        {
                            case 2:
                                $winLimit = $spinWinLimit * 10;
                                for($i = 39; $i >=0; $i--)                                
                                {
                                    $tmpWin = 0;
                                    $multiplier = 0;
                                    if($activePusherData[$i] != -1)
                                        $multiplier = $payTable[$activePusherData[$i]];                                        
                                    else
                                        $multiplier = 1;

                                    $tmpWin = $multiplier * $bet;
                                    if($tmpWin < $winLimit && $tmpWin > 0)
                                    {
                                        $winLimit -= $tmpWin;
                                        $coinMultiplier += $multiplier;
                                        $activePusherData[$i] = -1;
                                    }

                                    if($winLimit <= 0)
                                        break;
                                }

                                $bonus_hit_data[] = ['type' => 2, 'time' => 0, 'win' => $coinMultiplier];
                                $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', '');
                                break;
                            case 3:
                                $coinMultiplier = (int)($spinWinLimit * 10 / $bet);
                                $bonus_hit_data[] = ['type' => 3, 'time' => 0, 'win' => $coinMultiplier];
                                $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', '');
                                break;
                            case 4:
                                $coinMultiplier = $pendingMultiplier;                       
                                $bonus_hit_data[] = ['type' => 4, 'time' => 0, 'win' => $coinMultiplier];                                        
                                $activePusherData = array_fill(0, 40, -1);
                                $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', '');
                                break;
                            case 5:
                                $bonus_hit_data[] = ['type' => 5, 'time' => 0, 'win' => 0];
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', rand(7, 15));
                                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                                break;
                            case 6:
                                $bonus_hit_data[] = ['type' => 6, 'time' => 0, 'win' => 0];
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', rand(3, 4));
                                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                                break;
                        }
                        $totalMultiplier += $coinMultiplier;
                        $totalWin += $coinMultiplier * $bet;
                    }
                    else
                    {
                        $winLimit = $spinWinLimit;
                        if($winLimit / $bet > 35)
                        {
                            $coinMultiplier = (int)($spinWinLimit / $bet);
                            $bonus_hit_data[] = ['type' => 3, 'time' => 0, 'win' => $coinMultiplier];
                        }
                        else
                        {
                            if($postData['slotEvent'] == 'freespin' && $slotSettings->GetGameData($slotSettings->slotId . 'Feature') == 6)
                                $winLimit /= 2;
                            for($i = 39; $i >=0; $i--)                                
                            {
                                $tmpWin = 0;
                                $multiplier = 0;
                                if($activePusherData[$i] != -1)
                                {
                                    $sym_r = (int)($i / 5);
                                    if($sym_r == 7)
                                        $multiplier = $payTable[$activePusherData[$i]];                                        
                                }
                                else
                                    $multiplier = 1;
    
                                $tmpWin = $multiplier * $bet;
                                if($tmpWin <= $winLimit && $tmpWin > 0)
                                {
                                    $winLimit -= $tmpWin;
                                    $coinMultiplier += $multiplier;
                                    $activePusherData[$i] = -1;
                                }
    
                                if($winLimit <= 0)
                                    break;
                            }                            
                        }

                        if($postData['slotEvent'] == 'freespin' && $slotSettings->GetGameData($slotSettings->slotId . 'Feature') == 6)
                        {
                            //double feature
                            $coinMultiplier *= 2;
                        }
                        $totalMultiplier += $coinMultiplier;
                        $totalWin += $coinMultiplier * $bet;
                        

                        // //check big win symbols
                        // $bigSyms = [];
                        // for($i = 0; $i < count($activePusherData); $i++)
                        // {
                        //     if($activePusherData[$i] != -1)
                        //     {
                        //         $bigSyms[$i] = $activePusherData[$i];
                        //     }
                        // }
    
                        // asort($bigSyms);
                        // $winningBigSymIndices = [];
                        // $keys = array_keys($bigSyms);
                        
                        // for($i = 0; $i < count($bigSyms); $i++)
                        // {
                        //     $key = $keys[$i];
                        //     $value = $bigSyms[$key];
                        //     $multiplier = $payTable[$value];
                        //     $tmpWin = $bet * $multiplier;
    
                        //     $sym_r = (int)($key / 5);
    
                        //     if($sym_r == 7 && $spinWinLimit >= $tmpWin) //if big symbol is last row
                        //     {
                        //         $winningBigSymIndices[] = $bigSyms[$key];
                        //         $spinWinLimit -= $tmpWin;
                        //         $totalWin += $tmpWin;
                        //         $totalMultiplier += $multiplier;
                        //         $coinMultiplier += $multiplier;
                        //         $activePusherData[$key] = -1;
                        //     }
                        // }
    
                        // $smallWinCnt = 0;
                        // if($spinWinLimit > 0)
                        // {
                        //     //fill up win limit with small coin wins
                        //     $smallWinCnt = (int)($spinWinLimit / $bet);
                        //     $totalWin += $smallWinCnt * $bet;
                        //     $totalMultiplier += $smallWinCnt;
                        //     $coinMultiplier += $smallWinCnt;
                        // }
    
                        if($postData['slotEvent'] == 'freespin')
                        {
                            $feature = $slotSettings->GetGameData($slotSettings->slotId . 'Feature');
                            $bonus_hit_data[] = ['type' => $feature, 'time' => 0, 'win' => $coinMultiplier];
                        }
                    }
                }
                else
                {
                }

                if($postData['slotEvent'] == 'freespin')
                {
                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);
                    $totalFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                    $currentFreespin = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                    if($totalFreespin <= $currentFreespin || $slotSettings->user->spin_bank <= 0.1)
                    {
                        $slotSettings->SetGameData($slotSettings->slotId . 'SpinStatus', '');
                        $postData['slotEvent'] = 'bet';
                    }
                }

                $win_multipliers[] = $coinMultiplier;
            }

            $coinPusherData[$postData['bet_index']] = $activePusherData;

            $lastBalance = $slotSettings->GetBalance();
            if($totalWin > 0)
            {
                $slotSettings->SetWin($totalWin);
                $slotSettings->SetBalance($totalWin);
                // if($postData['slotEvent'] != 'freespin')
                    // $slotSettings->SetBalance($totalWin);
                // else
                // {
                //     //mint freespin total win is considered at last
                //     $slotSettings->SetGameData($slotSettings->slotId . 'TotalFreespinWin', $slotSettings->GetGameData($slotSettings->slotId . 'TotalFreespinWin') + $totalWin);
                // }
            }

            $special_pos = [];
            for($i = 0; $i < count($coinPusherData); $i++)
            {
                $data = $coinPusherData[$i];                
                $special_pos[] = [
                    'data' => $data
                ];
            }

            $response = [
                "msgid" => 107,
                "status_code" => 0,
                "result" => [
                  "module_id" => "BS",
                  "credit" => [
                    "low" => (int)($totalWin * 10),
                    "high" => 0,
                    "unsigned" => true
                  ],
                  "rng" => [],
                  "win_line_group" => [],
                  "multiplier_alone" => null,
                  "mulitplier_pattern" => [],
                  "random_syb_pattern" => [],
                  "bonus_multiplier" => null,
                  "win_bonus_group" => [],
                  "be_locked_pattern" => [],
                  "position_pay" => [],
                  "reel_stack_pay" => [],
                  "golden_wild_flag" => [],
                  "pay_of_scatter" => [],
                  "capture_award" => null,
                  "win_line_multiple" => null,
                  "mystery" => null,
                  "jp" => null,
                  "overlap" => [],
                  "pay_of_pos" => [],
                  "golden_icon" => [],
                  "exp_wild" => [],
                  "pre_exp_wild" => [],
                  "trigger_respin_times" => null,
                  "push_wild" => [],
                  "typed_wild" => null,
                  "sub_result" => [],
                  "icon_accumulate" => null,
                  "scatter_type" => [],
                  "pre_scatter_type" => [],
                  "full_pay" => null,
                  "block_reel_index" => null,
                  "trigger_super_scatter" => [],
                  "strip_index" => null,
                  "cascade_result" => [],
                  "random_bonus_times" => null,
                  "bonus_multiplier_list" => [],
                  "bonus_multiplier_index" => null,
                  "col_cascade_count" => [],
                  "external_multiplier" => null,
                  "pre_no_win_acc" => null,
                  "no_win_acc" => null,
                  "respin_types" => [],
                  "respin_costs" => [],
                  "jackpot_rng" => null,
                  "jackpot_type" => null,
                  "capture_award_list" => [],
                  "capture_award_index" => null,
                  "golden_scatter_flag" => [],
                  "full_symbol" => null,
                  "pay_of_star" => [],
                  "collect_counter" => null,
                  "cur_collect_counter" => null,
                  "upgrade_id" => [],
                  "change_symbol_id" => null,
                  "full_symbol_pattern" => [],
                  "avg_bet" => null,
                  "trigger_bonus_total_bet" => null,
                  "respin_reels" => [],
                  "cent_in_ask" => [],
                  "next_strip_index" => null,
                  "bonus_bet_list" => [],
                  "last_player_opt_index" => null,
                  "cur_star_counts" => [],
                  "total_star_times" => [],
                  "bonus_star_times" => [],
                  "cur_random_prize" => [],
                  "pool_info" => null,
                  "crush_syb_pattern" => [],
                  "bonus_symbol_pos" => null,
                  "arcade_mario_bar" => null,
                  "race_game_data" => null,
                  "coin_pusher_data" => [
                    "special_pos" => $special_pos,
                    "wins" => $win_multipliers,
                    "total_win" => $totalMultiplier,
                    "bonus_hit_data" => $bonus_hit_data
                  ],
                  "arcade_monopoly" => null,
                  "player_data" => null,
                  "village_infor" => null
                ],
                "player_cent" => [
                  "low" => $this->getCreditNumber($lastBalance),
                  "high" => 0,
                  "unsigned" => true
                ],
                "next_module" => "BS",
                "cur_module_play_times" => 1,
                "cur_module_total_times" => 1,
                "member_info" => null
            ];
            
            if($postData['slotEvent'] != 'freespin')
            {
                
            }
            else
            {
               
            }            
            
            $slotSettings->SaveLogReport(json_encode($response), $allbet, $totalWin, $postData['slotEvent']);
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

        function getCreditNumber($value)
        {
            return intval(number_format($value * 100, 0, '.', '')); 
        }
    }
}


?>