<?php 
namespace VanguardLTE\Games\FishHunterGhostPGD
{
    include('CheckReels.php');
    class Server
    {
        public function get($request, $game)
        {
            if (\str_contains($request->getRequestUri(),"lobby"))
            {
                if (\str_contains($request->getRequestUri(),"drtcmd"))
                {
                    return "eyJkYXRhIjp7Imtpb3NrX2lkIjoiNzIzNjIyMiIsInBpbl9pZCI6IjMyMjY0MDkiLCJpc19ndWVzdCI6ZmFsc2UsImRlZmF1bHRfcGFzc3dvcmQiOmZhbHNlLCJtYWNoaW5lX2lkIjoiMzE3MiIsImRldmljZV9pZCI6IjEyODE3NTUxIn0sInJlc3VsdCI6MH0=";
                }else if (\str_contains($request->getRequestUri(),"login"))
                {
                    return "eyJhdXRvX2lkIjoiMTI4MTc1ODAiLCJpbnZpdGVfY29kZSI6IkZQSENCWFFMV1cifQ==";
                }else if (\str_contains($request->getRequestUri(),"auth"))
                {
                    $shop = \VanguardLTE\Shop::where("id",\auth()->user()->shop_id)->get();
                    //$shopNumber = $shop[0]->shop_num;
                    return \base64_encode('{"ark_id":"'.auth()->user()->username.'","ark_token":"'.auth()->user()->shop_id.'"}');
                }else if (\str_contains($request->getRequestUri(),"command3=game"))
                {
                    $base64Content = $request->getContent();
                    $jsonStr = \base64_decode($base64Content);
                    $jsonInfo = \json_decode($jsonStr);
                    $arkData = \base64_decode($jsonInfo->ark_data);
                    $arkJson = \json_decode($arkData);
                    $output = "";
                    switch($arkJson->cmd_id)
                    {
                        case "kiosk":
                            switch($arkJson->cmd_name)
                            {
                                case "KioskUrl":
                                    $output = "eyJjbWRfc24iOiIxNjMwNjgxMzAxMDAwIiwiY21kX2RhdGEiOnsic3RhdHVzIjowLCJ1cmwiOiI1Mi42MC44MS4xNjI6NjA3MSIsInN1cmwiOiJmaXNoMDEyLmdvbGRlbmRyYWdvbmNpdHkuY29tOjUwNzEifX0=";
                                    break;
                            }
                            break;
                    }
                    return $output;
                }
                else if (\str_contains($request->getRequestUri(),"command2=command"))
                {
                    $base64Content = $request->getContent();
                    $jsonStr = \base64_decode($base64Content);
                    $jsonInfo = \json_decode($jsonStr);
                    $arkData = \base64_decode($jsonInfo->ark_data);
                    $arkJson = \json_decode($arkData);
                    $output = "";
                    switch ($arkJson->cmd_id)
                    {
                        case "table":
                            $output = "eyJjbWRfc24iOiIxNjMwNjU1MzEyMDAwIiwiY21kX2RhdGEiOnsic3RhdHVzIjowLCJ1cmwiOiIzLjk2LjM4LjIzMzo2MDAxIiwic3VybCI6ImxvYmJ5MDA2LmdvbGRlbmRyYWdvbmNpdHkuY29tOjUwMDEifX0=";
                            break;
                        case "lobby":
                            switch($arkJson->cmd_name)
                            {
                                case "playerFlow":
                                    $output = "eyJjbWRfc24iOiIxNjMwNjU1MzE1MDAyIiwiY21kX2RhdGEiOm51bGx9";
                                    break;
                                case "getUserProperty":
                                    $output = "eyJjbWRfc24iOiIxNjMwNjU1MzIwMDAxIiwiY21kX2RhdGEiOnsiZGF0YSI6eyJsYXN0X25hbWUiOiJPZ2JlaCIsInB1cmNoYXNlX3JldmVydCI6ZmFsc2UsIndpbm5pbmdzIjoxMDA1LjAsImRyaXZlcnNfbGljZW5zZSI6IiIsImxhc3RfcHVyY2hhc2VfaW50ZXJuZXRfYWZ0ZXIiOjAsImNsaWVudF9wdXJjaGFzZV9zZXJpYWxfaWQiOjYsImtpb3NrX2lkIjoiNzIzNjIyMiIsImZpcnN0X25hbWUiOiJWaWN0b3IiLCJsYXN0X3B1cmNoYXNlIjoiMjAyMS0wOC0yNlQwMjo0NzoxMC4zMzcwMDAiLCJhcmtfaWQiOiIxMjgxNzU1MSIsIm1vYmlsZV9wYXNzd29yZF91cGRhdGVfdGltZSI6IjIwMjEtMDgtMjZUMDI6NTE6NTQuOTk2MDAwIiwiY3JlYXRlX2xvY2FsX3RpbWUiOiIyMDIxLTA4LTI1VDIyOjQ3OjAzLjY3MTAwMCIsImZpcnN0X3B1cmNoYXNlX3RpbWUiOiIyMDIxLTA4LTI1VDIyOjQ3OjEwLjMzNTAwMCIsImxhc3RfcHVyY2hhc2VfYW1vdW50IjoyMDAsImpwX3RvdGFsX2JldF90ZW1wIjo5NzAsImVkaXRvciI6InN5c3RlbSIsIm1haWwiOiIiLCJtb2JpbGVfcGFzc3dvcmRfdXBkYXRlX2VkaXRvciI6IjMyMjY0MDkiLCJsYXN0X2xvZ2luX21hY2hpbmVJRCI6IjMxNzIiLCJlbmFibGUiOnRydWUsImNvbXBzX2ZsYWciOiIyMDIxLTA4LTI2VDAyOjQ3OjEwLjMzNzAwMCIsImxhc3RfcHVyY2hhc2VfY29tcHMiOjE1LCJwaG9uZSI6IiIsImJpcnRoZGF5IjoiMjAyMS0wOC0yNSIsIkNvbXBzSW4iOjAuMCwiZW50cmllcyI6MjEyMjAuMCwibGFzdF9jb21wc190eXBlIjoyLCJleGNoYW5nZV9tb25leSI6MC4wLCJnZW5kZXIiOiIxIiwibGFzdF9lbnRlcl90aGVtZUlEIjoiMTQ4MDAxIiwicGFzc3dvcmQiOiI5NmU3OTIxODk2NWViNzJjOTJhNTQ5ZGQ1YTMzMDExMiIsImNyZWF0ZV90aW1lIjoiMjAyMS0wOC0yNlQwMjo0NzowMy42NzEwMDAiLCJwaW5faWQiOiIzMjI2NDA5IiwiaW50ZXJuZXRfdGltZSI6ODkxMDAuMCwiZGVmYXVsdF9wYXNzd29yZCI6ZmFsc2V9LCJyZXN1bHQiOjB9fQ==";
                                    break;
                                case "getLobbyInfo":
                                    $output = "eyJjbWRfc24iOiIxNjMwNjU1MzIwMDAyIiwiY21kX2RhdGEiOnsiZGF0YSI6eyJzY29yZV9ib3giOnRydWUsImRvbmF0ZSI6ZmFsc2UsIm5hbWUiOiJDQVBJVEFMIiwicHJldmlld19tb2RlIjowLCJtb2JpbGUiOnRydWUsImFsbG93X3N0YXRlIjoiTkMiLCJzaHV0dGVyX3NraWxsIjpmYWxzZSwibW9kZSI6MCwic2tpbGxfZ2FtZV9yYXRlIjowLjksInNraWxsX2dhbWVfZW5hYmxlIjp0cnVlLCJpbnRlcm5ldF90aW1lIjp0cnVlLCJzaHV0dGVyIjpmYWxzZSwibWFjaGluZV9pZCI6IjMxNzIiLCJwcmV2aWV3X3NlY29uZHMiOjB9LCJyZXN1bHQiOjB9fQ==";
                                    break;
                                case "getKioskGameSetting":
                                    $output = "eyJjbWRfc24iOiIxNjMwNjU1MzIwMDAzIiwiY21kX2RhdGEiOnsiZGF0YSI6eyJkaXNhYmxlX2dhbWVfdHlwZV9saXN0IjpbIjMiXSwibG9ja19nYW1lX2xpc3QiOltdLCJkaXNhYmxlX2dhbWVfbGlzdCI6WyIxMDIwMDEiLCIxMDMwMDEiXX0sInJlc3VsdCI6MH19";
                                    break;
                                case "getUserCellphoneVerify":
                                    $output = "eyJjbWRfc24iOiIxNjMwNjU1MzIwMDA0IiwiY21kX2RhdGEiOnsiZGF0YSI6eyJlcnJvcl9tc2ciOiJQaG9uZSBOdW1iZXIgUmVnaXN0cmF0aW9uIEJvbnVzIGhhcyBlbmRlZC4ifSwicmVzdWx0IjotMjh9fQ==";
                                    break;
                                case "getSmsSetting":
                                    $output = "eyJjbWRfc24iOiIxNjMwNjU1MzIwMDA1IiwiY21kX2RhdGEiOnsiZGF0YSI6eyJpc19zaG93IjpmYWxzZSwiZXJyb3JfbXNnIjoiU21zIHN0YXRlIGlzIGRpc2FibGVkLiIsImlzX3Nob3dfcG9wdXAiOmZhbHNlfSwicmVzdWx0IjotMzJ9fQ==";
                                    break;
                                case "getCommonEventInfo":
                                    $output = "eyJjbWRfc24iOiIxNjMwNjU1MzIwMDAxIiwiY21kX2RhdGEiOnsiZGF0YSI6eyJsYXN0X25hbWUiOiJPZ2JlaCIsInB1cmNoYXNlX3JldmVydCI6ZmFsc2UsIndpbm5pbmdzIjoxMDA1LjAsImRyaXZlcnNfbGljZW5zZSI6IiIsImxhc3RfcHVyY2hhc2VfaW50ZXJuZXRfYWZ0ZXIiOjAsImNsaWVudF9wdXJjaGFzZV9zZXJpYWxfaWQiOjYsImtpb3NrX2lkIjoiNzIzNjIyMiIsImZpcnN0X25hbWUiOiJWaWN0b3IiLCJsYXN0X3B1cmNoYXNlIjoiMjAyMS0wOC0yNlQwMjo0NzoxMC4zMzcwMDAiLCJhcmtfaWQiOiIxMjgxNzU1MSIsIm1vYmlsZV9wYXNzd29yZF91cGRhdGVfdGltZSI6IjIwMjEtMDgtMjZUMDI6NTE6NTQuOTk2MDAwIiwiY3JlYXRlX2xvY2FsX3RpbWUiOiIyMDIxLTA4LTI1VDIyOjQ3OjAzLjY3MTAwMCIsImZpcnN0X3B1cmNoYXNlX3RpbWUiOiIyMDIxLTA4LTI1VDIyOjQ3OjEwLjMzNTAwMCIsImxhc3RfcHVyY2hhc2VfYW1vdW50IjoyMDAsImpwX3RvdGFsX2JldF90ZW1wIjo5NzAsImVkaXRvciI6InN5c3RlbSIsIm1haWwiOiIiLCJtb2JpbGVfcGFzc3dvcmRfdXBkYXRlX2VkaXRvciI6IjMyMjY0MDkiLCJsYXN0X2xvZ2luX21hY2hpbmVJRCI6IjMxNzIiLCJlbmFibGUiOnRydWUsImNvbXBzX2ZsYWciOiIyMDIxLTA4LTI2VDAyOjQ3OjEwLjMzNzAwMCIsImxhc3RfcHVyY2hhc2VfY29tcHMiOjE1LCJwaG9uZSI6IiIsImJpcnRoZGF5IjoiMjAyMS0wOC0yNSIsIkNvbXBzSW4iOjAuMCwiZW50cmllcyI6MjEyMjAuMCwibGFzdF9jb21wc190eXBlIjoyLCJleGNoYW5nZV9tb25leSI6MC4wLCJnZW5kZXIiOiIxIiwibGFzdF9lbnRlcl90aGVtZUlEIjoiMTQ4MDAxIiwicGFzc3dvcmQiOiI5NmU3OTIxODk2NWViNzJjOTJhNTQ5ZGQ1YTMzMDExMiIsImNyZWF0ZV90aW1lIjoiMjAyMS0wOC0yNlQwMjo0NzowMy42NzEwMDAiLCJwaW5faWQiOiIzMjI2NDA5IiwiaW50ZXJuZXRfdGltZSI6ODkxMDAuMCwiZGVmYXVsdF9wYXNzd29yZCI6ZmFsc2V9LCJyZXN1bHQiOjB9fQ==";
                                    break;
                                case "getClientPopUpAdv":
                                    $output = "eyJjbWRfc24iOiIxNjMwNjU1MzIyMDAwIiwiY21kX2RhdGEiOnsiZGF0YSI6eyJwb3BfdXBfdmVyc2lvbiI6IjEwNDEuMCIsInBvcF91cF9pbmZvIjpbeyJidXR0b25fYWN0aW9uIjoiQ1VTVE9NX0lEPVJhbmtpbmdCb251cyIsImJ1dHRvbl90ZXh0IjoiR08iLCJwcmlvcml0eSI6MCwibW9kZSI6WzBdLCJzdGFydFRpbWUiOiIyMDIxLTA4LTEyVDA0OjAwOjAwIiwiYnVuZGxlX25hbWUiOiJQb3BVcF9UcmVhTWFwUmFua0JvbnVzUGx1czA4MTJfbmMiLCJlbmRUaW1lIjoiMjAyMS0xMi0zMVQwNDowMDowMCJ9LHsiYnV0dG9uX2FjdGlvbiI6IkNVU1RPTV9JRD1PY2VhbkhlYXJ0IiwiYnV0dG9uX3RleHQiOiJHTyIsInByaW9yaXR5IjowLCJtb2RlIjpbMF0sInN0YXJ0VGltZSI6IjIwMjEtMDktMDJUMDQ6MDA6MDAiLCJidW5kbGVfbmFtZSI6IlBvcFVwX09jZWFuSGVhcnRQbHVzMDgxOV9uYyIsImVuZFRpbWUiOiIyMDIxLTA5LTA1VDAzOjU5OjU5In0seyJidXR0b25fYWN0aW9uIjoiQ1VTVE9NX0lEPVRyZWFzdXJlTWFwIiwiYnV0dG9uX3RleHQiOiJHTyIsInByaW9yaXR5IjoyLCJtb2RlIjpbMF0sInN0YXJ0VGltZSI6IjIwMjAtMDktMjJUMDQ6MDA6MDAiLCJidW5kbGVfbmFtZSI6IlBvcFVwX1RyZWFzdU1hcCIsImVuZFRpbWUiOiIyMDIxLTEyLTMxVDA0OjAwOjAwIn1dfSwicmVzdWx0IjowfX0=";
                                    break;
                                case "getClientBannerAdv":
                                    $output = "eyJjbWRfc24iOiIxNjMwNjU1MzIyMDAxIiwiY21kX2RhdGEiOnsiZGF0YSI6eyJiYW5uZXJfdmVyc2lvbiI6Ijk3NC4wIiwiYmFubmVyX2luZm8iOlt7ImJ1dHRvbl9hY3Rpb24iOiJDVVNUT01fSUQ9T2NlYW5IZWFydCIsImJ1dHRvbl90ZXh0IjoiR08iLCJwcmlvcml0eSI6MSwibW9kZSI6WzBdLCJzdGFydFRpbWUiOiIyMDIwLTEyLTMwVDA1OjAwOjAwIiwiYnVuZGxlX25hbWUiOiIyMDIwX0Jhbm5lcl9PY2VhbkhlYXJ0MSIsImVuZFRpbWUiOiIyMDIxLTEyLTMxVDA1OjAwOjAwIn0seyJidXR0b25fYWN0aW9uIjoiQ1VTVE9NX0lEPVJhbmtpbmdCb251cyIsImJ1dHRvbl90ZXh0IjoiR08iLCJwcmlvcml0eSI6MiwibW9kZSI6WzBdLCJzdGFydFRpbWUiOiIyMDIxLTA3LTI5VDA0OjAwOjAwIiwiYnVuZGxlX25hbWUiOiIyMDIwX0Jhbm5lcl9UcmVhTWFwUmFua0JvbnVzTmV3IiwiZW5kVGltZSI6IjIwMjEtMTItMzFUMDQ6MDA6MDAifV19LCJyZXN1bHQiOjB9fQ==";
                                    break;
                                case "getExtraCompsResult":
                                    $output = "eyJjbWRfc24iOiIxNjM4NTAyMTcxMDA3IiwiY21kX2RhdGEiOnsiZGF0YSI6W10sInJlc3VsdCI6MH19";
                                    break;
                                case "GetPuzzleQuestInfo":
                                    $output = "eyJjbWRfc24iOiIxNjM4NTAyMTcyMDAxIiwiY21kX2RhdGEiOnsiOTAwMDA1Ijp7ImRhdGEiOnsiRW5hYmxlIjp0cnVlLCJCZWdpblRpbWVVVEMiOjE1OTc3MjMyMDAsIkVuZFRpbWVVVEMiOjE2NDA5MjMyMDAsIk5vd1RpbWVVVEMiOjE2Mzg1MDIxNzIsIlF1ZXN0TGlzdCI6W10sIlVzZXJRdWVzdERhdGEiOltdLCJQdXp6bGVQbGF5ZXJJRCI6IjIxNjI1NTQzIn0sInJlc3VsdCI6MH19fQ==";
                                    break;
                                case "getPopupBillBoard":
                                    $output = "eyJjbWRfc24iOiIxNjM4NTAyMTc5MDAwIiwiY21kX2RhdGEiOnsiZGF0YSI6W3siVGl0bGVJbmZvIjpbeyJUYWdJRCI6InJ1bGVzIiwiVGFnU2hvdyI6ZmFsc2UsIlRhZ05hbWUiOiJSVUxFUyIsIlRhZ1R5cGUiOiJpbWFnZSJ9XSwiVGl0bGVJRCI6InJ1bGVzIiwiVGl0bGVOYW1lIjoiUlVMRVMifV0sInJlc3VsdCI6MH19";
                                    break;
                                case "getPopupContent":
                                    $output = "eyJjbWRfc24iOiIxNjM4NTAyMTc5MDAxIiwiY21kX2RhdGEiOnsiZGF0YSI6W3siQnVuZGxlTmFtZSI6IlJlZ3VsYXRpb25fdGVtcGxhdGUucG5nIiwiQnV0dG9uVGV4dCI6IlRXTyIsIkJ1dHRvbkFjdGlvbiI6IlJVTEUiLCJQcmlvcml0eSI6LTk5OTksIkJ1bmRsZVZlcnNpb24iOiIyMTUwLjAiLCJQb3B1cElEIjoiUmVndWxhdGlvbiIsIlN0YXJ0VGltZSI6IjIwMjEtMTEtMDFUMDQ6MDA6MDAiLCJFbmRUaW1lIjoiMjAyOC0xMi0zMVQwNDowMDowMCJ9XSwicmVzdWx0IjowfX0=";
                                    break;
                                
                            }   
                    }
                    return $output;
                } 
                else 
                {
                    return "e345f6ff3f2805fd944d35be0bd4aa8e";
                }
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
            $win = 0;
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
                    $_obf_0D0C042906245B03073E5C11081A210E351540320D2B01[0] = '{"action":"' . $_obf_0D1725391C1C0A3529182B263529401F0E1322380B1A32 . '","nickName":"' . $slotSettings->username . '","currency":" ","Credit":' . $credit . ',"Win":'.$win.'}';
                    //$_obf_0D0C042906245B03073E5C11081A210E351540320D2B01[0] = '{"action":"' . $_obf_0D1725391C1C0A3529182B263529401F0E1322380B1A32 . '","nickName":"' . $slotSettings->username . '","currency":" ","Credit":' . $credit .'}';
                    break;
                case 'Act41':
                    $_obf_0D34145C302B1D0101103437210F3D3C2D1C3836051D11 = $slotSettings->Bet;
                    $_obf_0D030E023B24273D231A270A343212362F2140160E0832 = [];
                    $_obf_0D030E023B24273D231A270A343212362F2140160E0832[] = '' . ($slotSettings->CurrentDenom * 100) . '';
                    foreach( $slotSettings->Denominations as $b ) 
                    {
                        $_obf_0D030E023B24273D231A270A343212362F2140160E0832[] = '' . ($b * 100) . '';
                    }
                    $_obf_0D0C042906245B03073E5C11081A210E351540320D2B01[0] = '{"action":"' . $_obf_0D1725391C1C0A3529182B263529401F0E1322380B1A32 . '","nickName":"' . $slotSettings->username . '","currency":" ","Credit":' . $credit . ',"Win":'.$win.'}';
                    //$_obf_0D0C042906245B03073E5C11081A210E351540320D2B01[0] = '{"action":"' . $_obf_0D1725391C1C0A3529182B263529401F0E1322380B1A32 . '","nickName":"' . $slotSettings->username . '","currency":" ","Credit":' . $credit .'}';
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
                    $_obf_0D0C042906245B03073E5C11081A210E351540320D2B01[0] = '{"action":"' . $_obf_0D1725391C1C0A3529182B263529401F0E1322380B1A32 . '","hits":' . json_encode($_obf_0D221D1040101E0C18152D38350A220B2431190A3E1822) . ',"nickName":"' . $slotSettings->username . '","currency":" ","Credit":' . $credit . '}';
                    break;
            }
            $response = $_obf_0D0C042906245B03073E5C11081A210E351540320D2B01[0];
            $slotSettings->SaveGameData();
            \DB::commit();
            return ':::' . $response;
        }
    }

}
