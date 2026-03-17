<?php 
namespace VanguardLTE\Games\CrabAvengers
{
    include('CheckReels.php');
    class Server
    {
        public function get($request, $game)
        {
            if (\str_contains($request->getRequestUri(),"gamehoutai"))
            {
                if (\str_contains($request->getRequestUri(),"fishRoomTypeInfo"))
                {
                    return '{"Code": 20000,"Message": "登录成功","Data": {"roomTypeInfo": {"money": 103700,"limit": [{"limitBalance": 0,"min": 50,"max": 2000,"coin_rate": 100},{"limitBalance": 10000,"min": 500,"max": 5000,"coin_rate": 100},{"limitBalance": 10000,"min": 1000,"max": 10000,"coin_rate": 100}]},"fishRoomMod": 1}}';
                }else
                {
                    return '{"Code": 20000,"Message": "登录成功","Data": {"res": [{"gameid": 1004,"servid": 1}]}}';
                }
            }else if (\str_contains($request->getRequestUri(),"timeDiff"))
            {
                return '{"data": [],"message": "入队成功","code": 1000}';
            }else if (\str_contains($request->getRequestUri(),"ShowConfig"))
            {
                return '{"code": 20000,"status": 200,"data": {"coin_type": "USD","max_length": 9,"is_separation": 0,"is_point": 1,"ctl_area": 1},"msg": "回成功"}';
            }else if (\str_contains($request->getRequestUri(),"NmMsgdropRate"))
            {
                return '{"data": [],"message": "入队成功","code": 1000}';
            }

            \DB::beginTransaction();
            $userId = \Auth::id();
            if( $userId == null ) 
            {
                $response = '{"responseEvent":"error","responseType":"","serverResponse":"invalid login"}';
                exit( $response );
            }
            $slotSettings = new SlotSettings($game, $userId);
            $_obf_0D221D1040101E0C18152D38350A220B2431190A3E1822 = json_decode(trim(file_get_contents('php://input')), true);
            $credit = round(sprintf('%01.2f', $slotSettings->GetBalance()) * 100);
            $win = round(sprintf('%01.2f', $slotSettings->GetCashableBalance()) * 100);
            $_obf_0D0C042906245B03073E5C11081A210E351540320D2B01 = [];
            $_obf_0D1725391C1C0A3529182B263529401F0E1322380B1A32 = '';
            $_obf_0D1725391C1C0A3529182B263529401F0E1322380B1A32 = (string)$_obf_0D221D1040101E0C18152D38350A220B2431190A3E1822['action'];
            switch( $_obf_0D1725391C1C0A3529182B263529401F0E1322380B1A32 ) 
            {
                case 'Init1':
                case 'Init2':
                case 'Act61':
                case 'Ping':
                case 'Act58':
                case 'getBalance':
                    $_obf_0D34145C302B1D0101103437210F3D3C2D1C3836051D11 = $slotSettings->Bet;
                    $_obf_0D030E023B24273D231A270A343212362F2140160E0832 = [];
                    $_obf_0D030E023B24273D231A270A343212362F2140160E0832[] = '' . ($slotSettings->CurrentDenom * 100) . '';
                    foreach( $slotSettings->Denominations as $b ) 
                    {
                        $_obf_0D030E023B24273D231A270A343212362F2140160E0832[] = '' . ($b * 100) . '';
                    }
                    $_obf_0D0C042906245B03073E5C11081A210E351540320D2B01[0] = '{"action":"' . $_obf_0D1725391C1C0A3529182B263529401F0E1322380B1A32 . '","nickName":"' . $slotSettings->username . '","currency":"' . $slotSettings->slotCurrency . '","Credit":' . $credit . ',"Win":'.$win.'}';
                    //$_obf_0D0C042906245B03073E5C11081A210E351540320D2B01[0] = '{"action":"' . $_obf_0D1725391C1C0A3529182B263529401F0E1322380B1A32 . '","nickName":"' . $slotSettings->username . '","currency":"' . $slotSettings->slotCurrency . '","Credit":' . $credit .'}';
                    break;
                case 'Act41':
                    $_obf_0D34145C302B1D0101103437210F3D3C2D1C3836051D11 = $slotSettings->Bet;
                    $_obf_0D030E023B24273D231A270A343212362F2140160E0832 = [];
                    $_obf_0D030E023B24273D231A270A343212362F2140160E0832[] = '' . ($slotSettings->CurrentDenom * 100) . '';
                    foreach( $slotSettings->Denominations as $b ) 
                    {
                        $_obf_0D030E023B24273D231A270A343212362F2140160E0832[] = '' . ($b * 100) . '';
                    }
                    $_obf_0D0C042906245B03073E5C11081A210E351540320D2B01[0] = '{"action":"' . $_obf_0D1725391C1C0A3529182B263529401F0E1322380B1A32 . '","nickName":"' . $slotSettings->username . '","currency":"' . $slotSettings->slotCurrency . '","Credit":' . $credit . ',"Win":'.$win.'}';
                    //$_obf_0D0C042906245B03073E5C11081A210E351540320D2B01[0] = '{"action":"' . $_obf_0D1725391C1C0A3529182B263529401F0E1322380B1A32 . '","nickName":"' . $slotSettings->username . '","currency":"' . $slotSettings->slotCurrency . '","Credit":' . $credit .'}';
                    break;
                case 'Act18':
                    $_obf_0D12350C2F3F39371E1E3F2A1E0736130C0D0E19071E32 = [];
                    $_obf_0D12350C2F3F39371E1E3F2A1E0736130C0D0E19071E32[1] = 2;
                    $_obf_0D12350C2F3F39371E1E3F2A1E0736130C0D0E19071E32[2] = 0;
                    $_obf_0D12350C2F3F39371E1E3F2A1E0736130C0D0E19071E32[3] = 3;
                    $_obf_0D12350C2F3F39371E1E3F2A1E0736130C0D0E19071E32[4] = 4;
                    $_obf_0D12350C2F3F39371E1E3F2A1E0736130C0D0E19071E32[5] = 5;
                    $_obf_0D12350C2F3F39371E1E3F2A1E0736130C0D0E19071E32[6] = 6;
                    $_obf_0D12350C2F3F39371E1E3F2A1E0736130C0D0E19071E32[7] = 7;
                    $_obf_0D12350C2F3F39371E1E3F2A1E0736130C0D0E19071E32[8] = 8;
                    $_obf_0D12350C2F3F39371E1E3F2A1E0736130C0D0E19071E32[9] = 9;
                    $_obf_0D12350C2F3F39371E1E3F2A1E0736130C0D0E19071E32[10] = 10;
                    $_obf_0D12350C2F3F39371E1E3F2A1E0736130C0D0E19071E32[11] = 12;
                    $_obf_0D12350C2F3F39371E1E3F2A1E0736130C0D0E19071E32[12] = 15;
                    $_obf_0D12350C2F3F39371E1E3F2A1E0736130C0D0E19071E32[13] = 18;
                    $_obf_0D12350C2F3F39371E1E3F2A1E0736130C0D0E19071E32[14] = 20;
                    $_obf_0D12350C2F3F39371E1E3F2A1E0736130C0D0E19071E32[15] = 22;
                    $_obf_0D12350C2F3F39371E1E3F2A1E0736130C0D0E19071E32[16] = 30;
                    $_obf_0D12350C2F3F39371E1E3F2A1E0736130C0D0E19071E32[17] = 30;
                    $_obf_0D12350C2F3F39371E1E3F2A1E0736130C0D0E19071E32[18] = 30;
                    $_obf_0D12350C2F3F39371E1E3F2A1E0736130C0D0E19071E32[19] = 40;
                    $_obf_0D12350C2F3F39371E1E3F2A1E0736130C0D0E19071E32[20] = 50;
                    $_obf_0D12350C2F3F39371E1E3F2A1E0736130C0D0E19071E32[21] = 50;
                    $_obf_0D12350C2F3F39371E1E3F2A1E0736130C0D0E19071E32[22] = 50;
                    $_obf_0D12350C2F3F39371E1E3F2A1E0736130C0D0E19071E32[23] = 100;
                    $_obf_0D12350C2F3F39371E1E3F2A1E0736130C0D0E19071E32[24] = 100;
                    $_obf_0D12350C2F3F39371E1E3F2A1E0736130C0D0E19071E32[25] = 150;
                    if( isset($_obf_0D221D1040101E0C18152D38350A220B2431190A3E1822['reqDat']) ) 
                    {
                        $_obf_0D1725391C1C0A3529182B263529401F0E1322380B1A32 = 'Act19';
                        $hits = $_obf_0D221D1040101E0C18152D38350A220B2431190A3E1822['reqDat']['hits'];
                        $lose = false;
                        for( $i = 0; $i < 2000; $i++ ) 
                        {
                            $allbet = 0;
                            $totalWin = 0;
                            $bank = $slotSettings->GetBank((isset($_obf_0D221D1040101E0C18152D38350A220B2431190A3E1822['slotEvent']) ? $_obf_0D221D1040101E0C18152D38350A220B2431190A3E1822['slotEvent'] : ''));
                            foreach( $hits as $key => $hit ) 
                            {
                                $fishType = $hit['fishType'];
                                $bet = $hit['bet'];
                                $_obf_0D362832171714063F2F145B3E251F362B012122193D32 = 0;
                                if( !isset($_obf_0D12350C2F3F39371E1E3F2A1E0736130C0D0E19071E32[$fishType]) ) 
                                {
                                    $_obf_0D362832171714063F2F145B3E251F362B012122193D32 = $_obf_0D221D1040101E0C18152D38350A220B2431190A3E1822['reqDat']['hits'][$key]['win'];
                                }
                                else
                                {
                                    $_obf_0D362832171714063F2F145B3E251F362B012122193D32 = $_obf_0D12350C2F3F39371E1E3F2A1E0736130C0D0E19071E32[$fishType] * $bet;
                                }
                                if( $_obf_0D362832171714063F2F145B3E251F362B012122193D32 != $_obf_0D221D1040101E0C18152D38350A220B2431190A3E1822['reqDat']['hits'][$key] ) 
                                {
                                    $_obf_0D362832171714063F2F145B3E251F362B012122193D32 = $_obf_0D221D1040101E0C18152D38350A220B2431190A3E1822['reqDat']['hits'][$key]['win'];
                                }
                                if( $lose ) 
                                {
                                    $_obf_0D221D1040101E0C18152D38350A220B2431190A3E1822['reqDat']['hits'][$key]['win'] = 0;
                                    $_obf_0D362832171714063F2F145B3E251F362B012122193D32 = 0;
                                }
                                $totalWin += $_obf_0D362832171714063F2F145B3E251F362B012122193D32;
                                $allbet += $bet;
                            }
                            if( $totalWin <= $bank ) 
                            {
                                break;
                            }
                            if( $i > 100 ) 
                            {
                                $lose = true;
                            }
                        }
                        $allbet = $allbet * 0.01;
                        $totalWin = $totalWin * 0.01;
                        if( $allbet < 0.0001 || $slotSettings->GetBalance() < $allbet ) 
                        {
                            $response = '{"responseEvent":"error","responseType":"bet","serverResponse":"invalid bet state"}';
                            exit( $response );
                        }
                        $_obf_0D1A310E2B25282C1A01072A06330C1A173E3437092622 = $allbet / 100 * $slotSettings->GetPercent();
                        $slotSettings->SetBank((isset($_obf_0D221D1040101E0C18152D38350A220B2431190A3E1822['slotEvent']) ? $_obf_0D221D1040101E0C18152D38350A220B2431190A3E1822['slotEvent'] : ''), $_obf_0D1A310E2B25282C1A01072A06330C1A173E3437092622, 'bet');
                        //$slotSettings->UpdateJackpots($allbet);
                        $slotSettings->SetBalance(-1 * $allbet, 'bet');
                        if( $totalWin > 0 ) 
                        {
                            $slotSettings->SetBank((isset($_obf_0D221D1040101E0C18152D38350A220B2431190A3E1822['slotEvent']) ? $_obf_0D221D1040101E0C18152D38350A220B2431190A3E1822['slotEvent'] : ''), -1 * $totalWin);
                            $slotSettings->SetBalance($totalWin);
                        }
                        $_obf_0D300C2F21350336261622142A322E0C270C0A1F2F0422 = '{"dealerCard":"","gambleState":"","totalWin":' . $totalWin . ',"afterBalance":' . $credit . ',"Balance":' . $credit . '}';
                        $response = '{"responseEvent":"gambleResult","serverResponse":' . $_obf_0D300C2F21350336261622142A322E0C270C0A1F2F0422 . '}';
                        $slotSettings->SaveLogReport($response, $allbet, 1, $totalWin, 'bet');
                    }
                    //$credit = floor(sprintf('%01.2f', $slotSettings->GetBalance()));
                    $_obf_0D0C042906245B03073E5C11081A210E351540320D2B01[0] = '{"action":"' . $_obf_0D1725391C1C0A3529182B263529401F0E1322380B1A32 . '","hits":' . json_encode($_obf_0D221D1040101E0C18152D38350A220B2431190A3E1822) . ',"nickName":"' . $slotSettings->username . '","currency":"' . $slotSettings->slotCurrency . '","Credit":' . $credit . '}';
                    break;
            }
            $response = $_obf_0D0C042906245B03073E5C11081A210E351540320D2B01[0];
            $slotSettings->SaveGameData();
            \DB::commit();
            return ':::' . $response;
        }
    }

}
