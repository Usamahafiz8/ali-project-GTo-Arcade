<?php 
namespace VanguardLTE\Games\SmokingHotFruits1x2
{
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;    

    class Server
    {
        public $gameState;
        public $debug = false;
        public $lastReels;            

        function generateWagerId()
        {
            $id = date("ymdHms").round(microtime(true) * 1000) % 1000;
            return $id;
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
                $reqId = $postData['mode'];
                
                switch( $reqId ) 
                {
                    case 'setup':                                
                        $slotSettings->SetGameData($slotSettings->slotId . 'CoinWin', 0);
                        $response = '{
                            "response" : {
                              "config" : {
                                "RTP" : 95.04,
                                "maxCoins" : 60000,
                                "type" : "PAYLINES",
                                "defaultBetVal" : 1.0,
                                "betValues" : [ 0.2, 0.4, 0.6, 0.8, 1.0, 1.2, 1.4, 1.6, 1.8, 2.0, 2.4, 3.0, 4.0, 5.0, 6.0, 7.0, 8.0, 10.0, 12.0, 15.0, 17.0, 20.0, 25.0, 30.0, 40.0, 50.0 ],
                                "autoPlayValues" : [ 5, 10, 20, 50, 99 ],
                                "payScreenThresholds" : [ 20, 50, 120 ],
                                "numRows" : 3,
                                "numReels" : 5,
                                "payDirection" : "LEFT",
                                "coinsPlayed" : 20,
                                "reelSets" : [ {
                                  "reelSetName" : "baseGameReels",
                                  "reels" : [ [ 6, 0, 5, 3, 5, 2, 1, 1, 1, 3, 0, 0, 1, 1, 3, 0, 0, 1, 1, 1, 3, 6, 1, 1, 4, 7, 2, 2, 1, 5, 5, 6, 3, 6, 4, 4, 2, 2, 0, 0, 0, 2, 2, 6, 7, 3, 3, 3, 2, 5, 5, 5, 1, 1, 1, 6, 6, 6, 5, 7, 0, 0, 0, 3, 0, 0, 0, 7, 5, 5, 0, 0, 6, 2, 4, 3, 3, 2, 1, 3, 4, 1, 1, 2, 2, 7, 3, 2, 1, 2, 1, 1, 1, 3, 5, 1, 2, 4, 3, 1, 4, 4, 1, 1, 2, 2, 3, 3, 2, 3, 4, 3, 1, 1, 1, 4, 1, 1, 1, 1, 6, 4, 1, 1, 2, 3, 1, 2 ], [ 6, 2, 3, 2, 4, 3, 5, 0, 0, 3, 2, 2, 4, 4, 1, 5, 5, 2, 2, 3, 3, 4, 4, 6, 1, 1, 0, 1, 1, 2, 2, 2, 7, 6, 5, 6, 2, 2, 1, 4, 4, 4, 5, 5, 2, 3, 2, 2, 4, 4, 2, 7, 0, 0, 0, 6, 6, 6, 5, 3, 3, 3, 0, 3, 0, 0, 0, 7, 5, 5, 0, 7, 6, 0, 0, 3, 3, 2, 1, 3, 0, 1, 1, 2, 2, 3, 3, 1, 3, 2, 4, 5, 1, 3, 5, 1, 2, 4, 3, 1, 2, 4, 3, 4, 2, 3, 4, 3, 2, 3, 4, 4, 1, 3, 3, 3, 2, 3, 3, 0, 5, 4, 0, 4, 2, 3, 2, 3 ], [ 6, 1, 3, 3, 3, 4, 0, 0, 7, 5, 5, 5, 1, 1, 1, 5, 0, 1, 1, 1, 6, 4, 4, 4, 2, 2, 7, 1, 0, 0, 7, 1, 1, 0, 3, 3, 0, 0, 0, 2, 3, 2, 2, 5, 2, 2, 2, 3, 1, 1, 2, 1, 1, 1, 0, 6, 6, 6, 5, 5, 5, 7, 4, 3, 2, 2, 2, 7, 6, 4, 5, 1, 5, 5, 2, 3, 3, 2, 1, 3, 0, 1, 1, 2, 2, 3, 3, 2, 3, 2, 0, 0, 1, 3, 5, 1, 2, 4, 3, 1, 2, 1, 0, 1, 0, 3, 3, 3, 2, 5, 2, 2, 1, 0, 4, 0, 1, 0, 0, 0, 5, 4, 0, 4, 0, 5, 0, 3 ], [ 6, 1, 5, 3, 4, 5, 0, 0, 0, 4, 4, 4, 0, 0, 2, 2, 2, 5, 0, 1, 1, 5, 1, 1, 1, 7, 5, 5, 2, 1, 1, 6, 1, 3, 3, 4, 2, 0, 2, 3, 2, 4, 4, 4, 3, 3, 3, 6, 5, 5, 5, 0, 0, 0, 0, 6, 6, 6, 5, 5, 5, 6, 4, 3, 6, 2, 2, 7, 6, 4, 5, 7, 6, 1, 0, 3, 3, 2, 1, 3, 0, 1, 1, 2, 2, 3, 3, 1, 1, 2, 0, 0, 1, 3, 5, 1, 2, 0, 3, 1, 7, 0, 2, 0, 0, 3, 4, 0, 3, 5, 4, 4, 1, 4, 3, 0, 1, 0, 0, 0, 5, 4, 0, 4, 0, 5, 0, 3 ], [ 6, 0, 4, 0, 0, 7, 0, 0, 0, 3, 1, 1, 1, 0, 2, 2, 1, 2, 0, 0, 2, 1, 1, 3, 3, 5, 2, 2, 2, 1, 1, 3, 4, 4, 2, 0, 5, 5, 2, 2, 0, 1, 1, 4, 0, 4, 2, 2, 4, 4, 2, 2, 1, 1, 0, 6, 6, 6, 5, 7, 5, 5, 5, 3, 6, 5, 6, 7, 5, 6, 6, 2, 1, 1, 1, 3, 3, 2, 7, 3, 0, 1, 1, 2, 2, 3, 3, 2, 3, 2, 0, 3, 0, 7, 0, 1, 4, 3, 3, 1, 2, 0, 2, 0, 1, 0, 4, 0, 3, 0, 4, 4, 1, 0, 1, 1, 0, 0, 1, 0, 6, 4, 0, 4, 0, 5, 0, 3 ] ]
                                } ],
                                "lines" : [ "00000", "11111", "22222", "01210", "21012", "10001", "12221", "00122", "22100", "10121", "12101", "01110", "21112", "01010", "21212", "11011", "11211", "00200", "10101", "12121" ],
                                "paytable" : [ {
                                  "id" : 0,
                                  "payout" : {
                                    "2" : 5,
                                    "3" : 20,
                                    "4" : 50,
                                    "5" : 200
                                  },
                                  "LOW" : true
                                }, {
                                  "id" : 1,
                                  "payout" : {
                                    "3" : 20,
                                    "4" : 50,
                                    "5" : 200
                                  },
                                  "LOW" : true
                                }, {
                                  "id" : 2,
                                  "payout" : {
                                    "3" : 20,
                                    "4" : 50,
                                    "5" : 200
                                  },
                                  "LOW" : true
                                }, {
                                  "id" : 3,
                                  "payout" : {
                                    "3" : 20,
                                    "4" : 50,
                                    "5" : 200
                                  },
                                  "LOW" : true
                                }, {
                                  "id" : 4,
                                  "payout" : {
                                    "3" : 50,
                                    "4" : 200,
                                    "5" : 500
                                  },
                                  "LOW" : true
                                }, {
                                  "id" : 5,
                                  "payout" : {
                                    "3" : 50,
                                    "4" : 200,
                                    "5" : 500
                                  },
                                  "MID" : true
                                }, {
                                  "id" : 6,
                                  "payout" : {
                                    "3" : 100,
                                    "4" : 1000,
                                    "5" : 3000
                                  },
                                  "HIGH" : true
                                }, {
                                  "id" : 7,
                                  "payout" : {
                                    "3" : 200,
                                    "4" : 1000,
                                    "5" : 3000
                                  },
                                  "SCATTER" : true
                                } ]
                              },
                              "bet" : {
                                "stake" : 0.0,
                                "stakePerLine" : 0.0,
                                "balance" : '.$slotSettings->GetBalance().',
                                "addBalance" : 0.0,
                                "lines" : 20,
                                "settle" : false,
                                "buyIn" : false
                              },
                              "results" : {
                                "win" : {
                                  "total" : 0,
                                  "symbolWin" : {
                                    "coins" : 0,
                                    "symbols" : [ ]
                                  },
                                  "scatterWin" : {
                                    "coins" : 0,
                                    "scatters" : [ ]
                                  }
                                },
                                "reels" : [ [ {
                                  "smbID" : 5
                                }, {
                                  "smbID" : 0
                                }, {
                                  "smbID" : 0
                                } ], [ {
                                  "smbID" : 7
                                }, {
                                  "smbID" : 1
                                }, {
                                  "smbID" : 1
                                } ], [ {
                                  "smbID" : 6
                                }, {
                                  "smbID" : 0
                                }, {
                                  "smbID" : 0
                                } ], [ {
                                  "smbID" : 4
                                }, {
                                  "smbID" : 1
                                }, {
                                  "smbID" : 1
                                } ], [ {
                                  "smbID" : 0
                                }, {
                                  "smbID" : 0
                                }, {
                                  "smbID" : 0
                                } ] ]
                              },
                              "state" : {
                                "reelSet" : "baseGameReels",
                                "status" : "NORMAL",
                                "totalWin" : 0,
                                "coinCountTumble" : 0
                              }
                            }
                          }';
                        break;
                    case 'getInfo':
                        $response = '&error_code=0&balance='.$slotSettings->GetBalance().'';
                        break;
                    case 'spin':
                        $postData['slotEvent'] = 'bet';                        
                        
                        $allbet = $postData['stake'] / 100;
                        if( !isset($postData['slotEvent']) ) 
                        {
                            $postData['slotEvent'] = 'bet';
                        }
                        
                        if( $postData['slotEvent'] == 'bet')
                        {
                            $slotSettings->SetBalance(-1 * $allbet, $postData['slotEvent']);
                            $slotSettings->UpdateJackpots($allbet);
                            $slotSettings->SetBet($allbet);                                             
                            
                            $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                        }
                        
                        $ret = $this->doSpin($slotSettings, $postData);
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

        function doSpin($slotSettings, &$postData)
        {            
            if($postData['slotEvent'] == 'freespin')
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'freespin');
            }
            else
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'LastEvent', 'bet');
            }
            
            $nCoins = 100;
            $betLine = 0;            
            
            $betLine = $postData['stake'] / 20 * 0.01;
            $allbet = $postData['stake'] / 100;
            
            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $betLine, $nCoins);
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
            if($this->debug && $postData['slotEvent'] != 'freespin')
            {                 
                $winType = 'bonus';
                $spinWinLimit = 60;
            }     

            $spinAcquired = false;
            $gameWin = $slotSettings->GetGameData($slotSettings->slotId . 'GameWin');

            $minReels = [];
            $minLineWins = [];
            $minTotalWin = -1;

            $totalWin = 0;            
            $lineWins = [];
            $reels = [];

            $scatter = 7;
            $wild = [];
            $bonusMpl = 1;
            $mpl = 1;
            
            $linesId = $slotSettings->GetPaylines();
            $lines = count($linesId);
            
            for( $i = 0; $i <= 600; $i++ )
            {
                $totalWin = 0;
                $lineWins = [];
                $mpl = 1;
                $reels = $slotSettings->GetReelStrips($winType);                

                $cWins = array_fill(0, $lines, 0);
                $bonusMpl = 1;
                
                for( $k = 0; $k < $lines; $k++ )
                {
                    $winline = [];
                    for( $j = 0; $j < count($slotSettings->SymbolGame); $j++ ) 
                    {
                        $csym = $slotSettings->SymbolGame[$j];
                        if( !isset($slotSettings->Paytable[$csym]) ) 
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
                            $mpl = 1;
                            
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) ) 
                            {
                                $tmpWin = $slotSettings->Paytable[$csym][3] * $betLine * $mpl * $bonusMpl;
                                $coin = $slotSettings->Paytable[$csym][3] * $mpl * $bonusMpl;                                
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $cWins[$k] = $tmpWin;                                    
                                    $winline = [$k, $coin, $tmpWin, $csym, 3];
                                }
                            }
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) ) 
                            {
                                $tmpWin = $slotSettings->Paytable[$csym][4] * $betLine * $mpl * $bonusMpl;
                                $coin = $slotSettings->Paytable[$csym][4] * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin ) 
                                {
                                    $cWins[$k] = $tmpWin;                                    
                                    $winline = [$k, $coin, $tmpWin, $csym, 4];
                                }
                            }
                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) ) 
                            {
                                $tmpWin = $slotSettings->Paytable[$csym][5] * $betLine * $mpl * $bonusMpl;
                                $coin = $slotSettings->Paytable[$csym][5] * $mpl * $bonusMpl;
                                if( $cWins[$k] < $tmpWin )
                                {
                                    $cWins[$k] = $tmpWin;                                    
                                    $winline = [$k, $coin, $tmpWin, $csym, 5];
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

                //calc freespin
                $scatterCnt = 0;
                for($r = 0; $r < 5; $r++)
                    for($c = 0; $c < 3; $c++)
                    {
                        if($reels['reel'.($r+1)][$c] == $scatter)
                        {
                            $scatterCnt++;
                        }
                    }
                if( $scatterCnt >= 3 && $winType != 'bonus' && $postData['slotEvent'] == 'bet')
                    continue;
                if ($scatterCnt < 3 && $winType == 'bonus')
                    continue;

                if($scatterCnt >= 3)
                {
                    if($scatterCnt > 5)
                        $scatterCnt = 5;
                    $scatterWin = $slotSettings->Paytable[$scatter][$scatterCnt] * $betLine;
                    $totalWin += $scatterWin;
                    $coin = $slotSettings->Paytable[$scatter][$scatterCnt];
                    $winline = [$k, $coin, $scatterWin, $scatter, $scatterCnt];
                    array_push($lineWins, $winline);
                }

                if($minTotalWin == -1 || ($minTotalWin > $totalWin && $totalWin > 0))
                {
                    $minTotalWin = $totalWin;
                    $minLineWins = $lineWins;
                    $minReels = $reels;
                }

                if($this->debug)
                {
                    $spinAcquired = true;
                    break;
                }

                if($totalWin <= $spinWinLimit && (($totalWin > 0 && $winType != 'none')))
                {
                    $spinAcquired = true;
                    if($totalWin < 0.7 * $spinWinLimit && $winType != 'bonus')
                        $spinAcquired = false;
                    if($spinAcquired)
                        break;
                }
                else if( $winType == 'none' && $totalWin == $gameWin ) 
                {
                    break;
                }
            }

            if(!$spinAcquired && $totalWin > $gameWin && $winType != 'none')
            {                
                $reels = $minReels;
                $lineWins = $minLineWins;
                $totalWin = $minTotalWin;
            }

            if($postData['slotEvent'] == 'bet')
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'BaseWinning', $totalWin);
                $slotSettings->SetGameData($slotSettings->slotId . 'GameWin', $totalWin);
            }
   
            $scatterWin = [
                'scatters' => [],
                'coins' => 0
            ];
            $symbolWin = [
                'symbols' => [],
                'coins' => 0
            ];
            $coinWin = 0;
            if(!empty($lineWins))
            {
                foreach($lineWins as $winline)
                {
                    $coin = $winline[1];
                    $coinWin += $coin;
                    $sym = $winline[3];
                    if($sym == $scatter)
                    {
                        $scatterWin['scatters'][] = [
                            'amt' => $winline[1],
                            'dir' => 'LEFT',
                            'lineID' => $winline[0],
                            'mult' => 1,
                            'num' => $winline[4],
                            'smbID' => $sym
                        ];
                        $scatterWin['coins'] += $coin;
                    }
                    else
                    {
                        $symbolWin['symbols'][] = [
                            'amt' => $winline[1],
                            'dir' => 'LEFT',
                            'lineID' => $winline[0],
                            'mult' => 1,
                            'num' => $winline[4],
                            'smbID' => $sym
                        ];
                        $symbolWin['coins'] += $coin;
                    }
                    
                }
            }

            $slotSettings->SetBalance($totalWin, $postData['slotEvent']);
            $slotSettings->SetWin($totalWin);
            
            $ret = [
                'response' => [
                    'bet' => [
                        'addBalance' => $totalWin,
                        'balance' => $slotSettings->GetBalance(),
                        'buyIn' => false,
                        'lines' => 20,
                        'settle' => true,
                        'stake' => $allbet,
                        'stakePerLine' => $betLine
                    ],
                    'results' => [
                        'reels' => $this->GetReelSymbols($reels),
                        'win' => [
                            'scatterWin' => $scatterWin,
                            'symbolWin' => $symbolWin,
                            'total' => $coinWin
                        ]
                    ],
                    'state' => [
                        'coinCountTumble' => 0,
                        'reelSet' => 'baseGameReels',
                        'status' => 'NORMAL',
                        'totalWin' => $coinWin
                    ]
                ]
            ];

            $slotSettings->SaveLogReport(json_encode($ret), $allbet, $totalWin, $postData['slotEvent']);
            return $ret;
        }

        function GetReelArray($reels)
        {
            $res = [];
            for($r = 0; $r < 6; $r++)
                $res[] = $reels['reel'.($r+1)];
            return $res;
        }

        function GetReelSymbols($reels)
        {
            $symbolArr = [];
            for($r = 0; $r < 5; $r++)
            {
                $values = [];
                for($c = 0; $c < 3; $c++)
                    $values[] = ['smbID' => $reels['reel'.($r+1)][$c]];
                $symbolArr[] = $values;
            }
            return $symbolArr;
        }
    }
}


