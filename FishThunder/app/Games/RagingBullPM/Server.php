<?php 
namespace VanguardLTE\Games\RagingBullPM
{
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;
    use SimpleXMLElement;

    class Server
    {
        public $gameState;
        public $debug = false;
        function getConvertedLine($positions)
        {
            $res = [];
            foreach($positions as $r => $c)
            {
                $res[] = $c * 5 + $r;
            }
            $res = implode('~', $res);
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

                $reqType = $postData['action'];
                $objRes = [];
                $LASTSPIN = $slotSettings->GetHistory();
                switch( $reqType ) 
                {
                    case 'doInit':         
                        $objRes = $this->doInit($slotSettings, $postData, $LASTSPIN);
                        break;
                    case 'doSpin':
                        $objRes = $this->doSpin($slotSettings, $postData);
                        break;
                    case 'doCollect':
                        $objRes = $this->doCollect($slotSettings, $postData);
                        break;
                    case 'doFSOption':
                        $objRes = $this->doFSOption($slotSettings, $postData, $LASTSPIN);
                        break;
                    case 'update':
                        $objRes = [
                            'balance_bonus' => '0.00',
                            'balance' => $slotSettings->GetBalance(),
                            'balance_cash' => $slotSettings->GetBalance(),
                            'stime' => floor(microtime(true) * 1000),
                        ];
                        break;
                    default:
                        break;
                }
                
                $slotSettings->SaveGameData();
                DB::commit();          
                return $this->toResponse($objRes);                    
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

        function doInit($slotSettings, $postData, $LASTSPIN)
        {
            $balance = $slotSettings->GetBalance();
            $res = [
                'def_s' => '4,3,7,7,4,11,5,6,7,11,10,5,9,8,8',
                'balance' => $balance,
                'cfgs' => '1',
                'ver' => '2',
                'index' => $postData['index'],
                'balance_cash' => $balance,
                'reel_set_size' => '8',
                'def_sb' => '7,11,8,10,6',
                'def_sa' => '6,3,5,8,11',
                'bonusInit' => '[{bgid:0,bgt:33,bg_i:"15,150,2000",bg_i_mask:"wp,wp,wp"},{bgid:2,bgt:18,bg_i:"2000,150,15",bg_i_mask:"pw,pw,pw"}]',
                'balance_bonus' => '0.00',
                'wrlm_sets' => '2~0~2,3,5~1~3,5,8~2~5,8,10~3~8,10,15~4~10,15,30~5~15,30,40',
                'na' => 's',
                'scatters' => '1~0,0,0,0,0~0,0,0,0,0~1,1,1,1,1',
                'fs_aw' => 't;n',
                'gmb' => '0,0,0',
                'rt' => 'd',
                'base_aw' => 't;n',
                'stime' => floor(microtime(true) * 1000),
                'sa' => '6,3,5,8,11',
                'sb' => '7,11,8,10,6',
                'sc' => '0.01,0.02,0.03,0.04,0.05,0.10,0.20,0.30',
                'defc' => '0.10',
                'sh' => '3',
                'wilds' => '2~0,0,0,0,0~1,1,1,1,1',
                'bonuses' => '0',
                'fsbonus' => '',
                'c' => '0.01',
                'sver' => '5',
                'n_reel_set' => '0',
                'counter' => ((int)$postData['counter'] + 1),
                'paytable' => '0,0,0,0,0;0,0,0,0,0;0,0,0,0,0;126,45,15,2,0;81,30,12,0,0;81,30,12,0,0;63,24,9,0,0;63,24,9,0,0;36,12,4,0,0;36,12,4,0,0;27,9,3,0,0;27,9,3,0,0;18,6,2,0,0;18,6,2,0,0',
                'l' => '18',
                'rtp' => '96.5,96.46,96.44,96.41,96.38,96.37,95.94',
                'reel_set0' => '9,8,10,5,10,13,5,7,13,8,12,5,4,7,7,3,4,9,9,8,5,5,12,3,3,6,6,4,4,11,11,13,4,8,8,13,3,9,7,11,13,7,6,11,7,12,9,13,13,3,8,6,12,13,4,11,7,8,11,10,10,3,6,12,10,12,5,6,11,10,6,10,13,9,10,7,12,8,12,11,9,11,8,9,13,12,5,9,6,10,4~4,6,9,9,1,11,11,13,2,7,8,8,12,6,10,2,3,13,7,11,9,3,9,12,8,6,5,5,3,7,11,13,8,1,5,8,9,4,6,10,4,4,13,13,1,5,10,10,3,6,9,11,1,10,6,4,12,2,6,6,4,9,13,8,1,3,10,12,12,11,5,3,12,13,2,4,12,7,7,10,13,9,2,11,5,5,9,8,1,6,7,11,2,10,12,10,8,1,7,4,13,12,7,8~4,12,9,13,11,6,2,7,4,4,5,8,3,12,9,9,1,8,11,8,10,11,2,12,4,3,10,11,12,5,1,8,6,4,13,11,11,2,6,8,3,7,10,5,5,3,10,7,6,6,4,1,3,6,7,10,9,13,1,12,5,12,12,2,8,8,5,12,11,9,2,6,7,13,13,4,6,2,7,13,12,3,6,7,7,10,10,8,1,5,10,8,7,13,9,9,13,11,1,9,9,10,11~13,6,13,7,7,10,6,1,8,9,9,6,10,2,12,13,10,10,8,11,2,12,6,5,13,7,2,5,9,5,4,1,11,11,11,12,12,4,7,4,6,8,1,9,13,13,12,6,3,10,5,10,7,3,8,4,9,3,4,6,6,12,1,8,8,12,7,3,10,10,9,11,2,12,10,5,9,2,13,11,8,11,11,2,13,9,5,5,3,12,9,8,11,3,7,4,4,13,6,7,13,8,12,4,5,7,11~13,8,10,13,5,13,5,5,9,11,10,6,13,6,10,10,11,9,9,13,13,11,6,3,8,10,7,7,5,3,12,7,10,4,12,12,11,8,11,13,12,4,6,8,11,6,6,3,8,12,7,13,4,6,9,3,12,4,4,9,12,11,11,7,8,8,10,5,4,12,3,13,7,11,6,9,8,7,3,9,10,5,9,7,4,12,10,9,8',
                's' => '4,3,7,7,4,11,5,6,7,11,10,5,9,8,8',
                'reel_set2' => '9,8,10,5,10,13,5,7,7,13,8,12,12,5,4,7,3,4,9,8,5,12,3,6,4,11,13,4,8,13,3,9,7,11,13,7,6,11,7,12,9,13,3,8,8,6,12,13,13,4,11,7,8,11,10,3,6,12,10,12,5,5,6,6,11,10,6,10,13,9,10,7,12,8,12,11,9,11,8,9,13,12,5,9,6,10,4~4,6,9,1,11,11,13,2,7,8,12,6,10,2,3,13,7,11,9,3,9,12,8,6,6,5,5,3,3,7,11,13,8,1,5,8,9,4,6,10,4,13,13,1,5,10,3,6,9,9,11,1,10,6,4,12,2,6,6,4,9,13,8,1,3,10,12,12,11,5,3,12,13,13,13,2,4,12,7,7,10,13,9,2,11,5,5,9,8,1,6,7,11,2,10,12,10,8,1,7,4,13,12,7,8~4,12,9,13,11,6,2,7,4,5,8,3,12,9,1,8,11,8,10,11,2,12,4,3,10,11,12,5,1,8,6,4,13,11,11,2,6,8,3,7,10,5,5,3,10,10,7,6,4,1,3,6,7,10,9,13,1,12,5,12,2,8,5,12,11,9,2,6,7,13,13,4,6,6,2,7,13,12,3,6,7,10,8,1,5,10,8,7,7,13,9,9,13,11,1,9,9,10,11~13,6,13,7,10,6,1,8,9,6,10,2,12,13,10,8,11,2,12,6,5,13,7,2,5,9,5,4,1,11,12,12,4,7,4,6,8,8,1,9,13,13,6,3,10,5,10,7,3,8,4,9,3,4,6,12,1,8,12,7,7,3,10,10,9,9,11,2,12,10,5,9,2,13,11,8,11,2,13,9,5,3,12,9,8,11,3,7,4,4,13,6,7,13,8,12,4,5,7,11~13,8,10,13,5,13,5,5,9,11,10,6,13,6,10,11,9,13,11,11,6,3,8,10,10,7,5,3,12,7,10,4,4,12,11,8,11,13,12,4,6,8,11,6,6,6,3,8,12,7,7,13,4,6,9,3,12,4,9,9,12,11,7,8,10,5,4,12,3,13,7,11,6,9,8,7,3,9,10,5,9,7,4,12,10,9,8',
                't' => '243',
                'reel_set1' => '9,8,10,5,10,13,5,7,7,13,8,12,12,5,4,7,3,4,9,8,5,12,3,6,4,11,13,4,8,13,3,9,7,11,13,7,6,11,7,12,9,13,3,8,8,6,12,13,13,4,11,7,8,11,10,3,6,12,10,12,5,5,6,6,11,10,6,10,13,9,10,7,12,8,12,11,9,11,8,9,13,12,5,9,6,10,4~4,6,9,1,11,11,13,2,7,8,12,6,10,2,3,13,7,11,9,3,9,12,8,6,6,5,5,3,3,7,11,13,8,1,5,8,9,4,6,10,4,13,13,1,5,10,3,6,9,9,11,1,10,6,4,12,2,6,6,4,9,13,8,1,3,10,12,12,11,5,3,12,13,13,13,2,4,12,7,7,10,13,9,2,11,5,5,9,8,1,6,7,11,2,10,12,10,8,1,7,4,13,12,7,8~4,12,9,13,11,6,2,7,4,5,8,3,12,9,1,8,11,8,10,11,2,12,4,3,10,11,12,5,1,8,6,4,13,11,11,2,6,8,3,7,10,5,5,3,10,10,7,6,4,1,3,6,7,10,9,13,1,12,5,12,2,8,5,12,11,9,2,6,7,13,13,4,6,6,2,7,13,12,3,6,7,10,8,1,5,10,8,7,7,13,9,9,13,11,1,9,9,10,11~13,6,13,7,10,6,1,8,9,6,10,2,12,13,10,8,11,2,12,6,5,13,7,2,5,9,5,4,1,11,12,12,4,7,4,6,8,8,1,9,13,13,6,3,10,5,10,7,3,8,4,9,3,4,6,12,1,8,12,7,7,3,10,10,9,9,11,2,12,10,5,9,2,13,11,8,11,2,13,9,5,3,12,9,8,11,3,7,4,4,13,6,7,13,8,12,4,5,7,11~13,8,10,13,5,13,5,5,9,11,10,6,13,6,10,11,9,13,11,11,6,3,8,10,10,7,5,3,12,7,10,4,4,12,11,8,11,13,12,4,6,8,11,6,6,6,3,8,12,7,7,13,4,6,9,3,12,4,9,9,12,11,7,8,10,5,4,12,3,13,7,11,6,9,8,7,3,9,10,5,9,7,4,12,10,9,8',
                'reel_set4' => '9,8,10,5,10,13,5,7,7,13,8,12,12,5,4,7,3,4,9,8,5,12,3,6,4,11,13,4,8,13,3,9,7,11,13,7,6,11,7,12,9,13,3,8,8,6,12,13,13,4,11,7,8,11,10,3,6,12,10,12,5,5,6,6,11,10,6,10,13,9,10,7,12,8,12,11,9,11,8,9,13,12,5,9,6,10,4~4,6,9,1,11,11,13,2,7,8,12,6,10,2,3,13,7,11,9,3,9,12,8,6,6,5,5,3,3,7,11,13,8,1,5,8,9,4,6,10,4,13,13,1,5,10,3,6,9,9,11,1,10,6,4,12,2,6,6,4,9,13,8,1,3,10,12,12,11,5,3,12,13,13,2,4,12,7,7,10,13,9,2,11,5,5,9,8,1,6,7,11,2,10,12,10,8,1,7,4,13,12,7,8~4,12,9,13,11,6,2,7,4,5,8,3,12,9,1,8,11,8,10,11,2,12,4,3,10,11,12,5,1,8,6,4,13,11,11,2,6,8,3,7,10,5,5,3,10,10,7,6,4,1,3,6,7,10,9,13,1,12,5,12,2,8,5,12,11,9,2,6,7,13,13,4,6,6,2,7,13,12,3,6,7,10,8,1,5,10,8,7,7,13,9,9,13,11,1,9,9,10,11~13,6,13,7,10,6,1,8,9,6,10,2,12,13,10,8,11,2,12,6,5,13,7,2,5,9,5,4,1,11,12,12,4,7,4,6,8,8,1,9,13,13,6,3,10,5,10,7,3,8,4,9,3,4,6,12,1,8,12,7,7,3,10,10,9,9,11,2,12,10,5,9,2,13,11,8,11,2,13,9,5,3,12,9,8,11,3,7,4,4,13,6,7,13,8,12,4,5,7,11~13,8,10,13,5,13,5,5,9,11,10,6,13,6,10,11,9,13,11,11,6,3,8,10,10,7,5,3,12,7,10,4,4,12,11,8,11,13,12,4,6,8,11,6,6,3,8,12,7,7,13,4,6,9,3,12,4,9,9,12,11,7,8,10,5,4,12,3,13,7,11,6,9,8,7,3,9,10,5,9,7,4,12,10,9,8',
                'reel_set3' => '9,8,10,5,10,13,5,7,7,13,8,12,12,5,4,7,3,4,9,8,5,12,3,6,4,11,13,4,8,13,3,9,7,11,13,7,6,11,7,12,9,13,3,8,8,6,12,13,13,4,11,7,8,11,10,3,6,12,10,12,5,5,6,6,11,10,6,10,13,9,10,7,12,8,12,11,9,11,8,9,13,12,5,9,6,10,4~4,6,9,1,11,11,13,2,7,8,12,6,10,2,3,13,7,11,9,3,9,12,8,6,6,5,5,3,3,7,11,13,8,1,5,8,9,4,6,10,4,13,13,1,5,10,3,6,9,9,11,1,10,6,4,12,2,6,6,4,9,13,8,1,3,10,12,12,11,5,3,12,13,13,13,2,4,12,7,7,10,13,9,2,11,5,5,9,8,1,6,7,11,2,10,12,10,8,1,7,4,13,12,7,8~4,12,9,13,11,6,2,7,4,5,8,3,12,9,1,8,11,8,10,11,2,12,4,3,10,11,12,5,1,8,6,4,13,11,11,2,6,8,3,7,10,5,5,3,10,10,7,6,4,1,3,6,7,10,9,13,1,12,5,12,2,8,5,12,11,9,2,6,7,13,13,4,6,6,2,7,13,12,3,6,7,10,8,1,5,10,8,7,7,13,9,9,13,11,1,9,9,10,11~13,6,13,7,10,6,1,8,9,6,10,2,12,13,10,8,11,2,12,6,5,13,7,2,5,9,5,4,1,11,12,12,4,7,4,6,8,8,1,9,13,13,6,3,10,5,10,7,3,8,4,9,3,4,6,12,1,8,12,7,7,3,10,10,9,9,11,2,12,10,5,9,2,13,11,8,11,2,13,9,5,3,12,9,8,11,3,7,4,4,13,6,7,13,8,12,4,5,7,11~13,8,10,13,5,13,5,5,9,11,10,6,13,6,10,11,9,13,11,11,6,3,8,10,10,7,5,3,12,7,10,4,4,12,11,8,11,13,12,4,6,8,11,6,6,6,3,8,12,7,7,13,4,6,9,3,12,4,9,9,12,11,7,8,10,5,4,12,3,13,7,11,6,9,8,7,3,9,10,5,9,7,4,12,10,9,8',
                'reel_set6' => '9,8,10,5,10,13,5,7,7,13,8,12,12,5,4,7,3,4,9,8,5,12,3,6,4,11,13,4,8,13,3,9,7,11,13,7,6,11,7,12,9,13,3,8,8,6,12,13,13,4,11,7,8,11,10,3,6,12,10,12,5,5,6,6,11,10,6,10,13,9,10,7,12,8,12,11,9,11,8,9,13,12,5,9,6,10,4~4,6,9,1,11,11,13,2,7,8,12,6,10,2,3,13,7,11,9,3,9,12,8,6,6,5,5,3,3,7,11,13,8,1,5,8,9,4,6,10,4,13,13,1,5,10,3,6,9,9,11,1,10,6,4,12,2,6,6,4,9,13,8,1,3,10,12,12,11,5,3,12,13,13,2,4,12,7,7,10,13,9,2,11,5,5,9,8,1,6,7,11,2,10,12,10,8,1,7,4,13,12,7,8~4,12,9,13,11,6,2,7,4,5,8,3,12,9,1,8,11,8,10,11,2,12,4,3,10,11,12,5,1,8,6,4,13,11,11,2,6,8,3,7,10,5,5,3,10,10,7,6,4,1,3,6,7,10,9,13,1,12,5,12,2,8,5,12,11,9,2,6,7,13,13,4,6,6,2,7,13,12,3,6,7,10,8,1,5,10,8,7,7,13,9,9,13,11,1,9,9,10,11~13,6,13,7,10,6,1,8,9,6,10,2,12,13,10,8,11,2,12,6,5,13,7,2,5,9,5,4,1,11,12,12,4,7,4,6,8,8,1,9,13,13,6,3,10,5,10,7,3,8,4,9,3,4,6,12,1,8,12,7,7,3,10,10,9,9,11,2,12,10,5,9,2,13,11,8,11,2,13,9,5,3,12,9,8,11,3,7,4,4,13,6,7,13,8,12,4,5,7,11~13,8,10,13,5,13,5,5,9,11,10,6,13,6,10,11,9,13,11,11,6,3,8,10,10,7,5,3,12,7,10,4,4,12,11,8,11,13,12,4,6,8,11,6,6,3,8,12,7,7,13,4,6,9,3,12,4,9,9,12,11,7,8,10,5,4,12,3,13,7,11,6,9,8,7,3,9,10,5,9,7,4,12,10,9,8',
                'reel_set5' => '9,8,10,5,10,13,5,7,7,13,8,12,12,5,4,7,3,4,9,8,5,12,3,6,4,11,13,4,8,13,3,9,7,11,13,7,6,11,7,12,9,13,3,8,8,6,12,13,13,4,11,7,8,11,10,3,6,12,10,12,5,5,6,6,11,10,6,10,13,9,10,7,12,8,12,11,9,11,8,9,13,12,5,9,6,10,4~4,6,9,1,11,11,13,2,7,8,12,6,10,2,3,13,7,11,9,3,9,12,8,6,6,5,5,3,3,7,11,13,8,1,5,8,9,4,6,10,4,13,13,1,5,10,3,6,9,9,11,1,10,6,4,12,2,6,6,4,9,13,8,1,3,10,12,12,11,5,3,12,13,13,2,4,12,7,7,10,13,9,2,11,5,5,9,8,1,6,7,11,2,10,12,10,8,1,7,4,13,12,7,8~4,12,9,13,11,6,2,7,4,5,8,3,12,9,1,8,11,8,10,11,2,12,4,3,10,11,12,5,1,8,6,4,13,11,11,2,6,8,3,7,10,5,5,3,10,10,7,6,4,1,3,6,7,10,9,13,1,12,5,12,2,8,5,12,11,9,2,6,7,13,13,4,6,6,2,7,13,12,3,6,7,10,8,1,5,10,8,7,7,13,9,9,13,11,1,9,9,10,11~13,6,13,7,10,6,1,8,9,6,10,2,12,13,10,8,11,2,12,6,5,13,7,2,5,9,5,4,1,11,12,12,4,7,4,6,8,8,1,9,13,13,6,3,10,5,10,7,3,8,4,9,3,4,6,12,1,8,12,7,7,3,10,10,9,9,11,2,12,10,5,9,2,13,11,8,11,2,13,9,5,3,12,9,8,11,3,7,4,4,13,6,7,13,8,12,4,5,7,11~13,8,10,13,5,13,5,5,9,11,10,6,13,6,10,11,9,13,11,11,6,3,8,10,10,7,5,3,12,7,10,4,4,12,11,8,11,13,12,4,6,8,11,6,6,3,8,12,7,7,13,4,6,9,3,12,4,9,9,12,11,7,8,10,5,4,12,3,13,7,11,6,9,8,7,3,9,10,5,9,7,4,12,10,9,8',
                'reel_set7' => '9,8,10,5,10,13,5,7,7,13,8,12,12,5,4,7,3,4,9,8,5,12,3,6,4,11,13,4,8,13,3,9,7,11,13,7,6,11,7,12,9,13,3,8,8,6,12,13,13,4,11,7,8,11,10,3,6,12,10,12,5,5,6,6,11,10,6,10,13,9,10,7,12,8,12,11,9,11,8,9,13,12,5,9,6,10,4~4,6,9,1,11,11,13,2,7,8,12,6,10,2,3,13,7,11,9,3,9,12,8,6,6,5,5,3,3,7,11,13,8,1,5,8,9,4,6,10,4,13,13,1,5,10,3,6,9,9,11,1,10,6,4,12,2,6,6,4,9,13,8,1,3,10,12,12,11,5,3,12,13,13,2,4,12,7,7,10,13,9,2,11,5,5,9,8,1,6,7,11,2,10,12,10,8,1,7,4,13,12,7,8~4,12,9,13,11,6,2,7,4,5,8,3,12,9,1,8,11,8,10,11,2,12,4,3,10,11,12,5,1,8,6,4,13,11,11,2,6,8,3,7,10,5,5,3,10,10,7,6,4,1,3,6,7,10,9,13,1,12,5,12,2,8,5,12,11,9,2,6,7,13,13,4,6,6,2,7,13,12,3,6,7,10,8,1,5,10,8,7,7,13,9,9,13,11,1,9,9,10,11~13,6,13,7,10,6,1,8,9,6,10,2,12,13,10,8,11,2,12,6,5,13,7,2,5,9,5,4,1,11,12,12,4,7,4,6,8,8,1,9,13,13,6,3,10,5,10,7,3,8,4,9,3,4,6,12,1,8,12,7,7,3,10,10,9,9,11,2,12,10,5,9,2,13,11,8,11,2,13,9,5,3,12,9,8,11,3,7,4,4,13,6,7,13,8,12,4,5,7,11~13,8,10,13,5,13,5,5,9,11,10,6,13,6,10,11,9,13,11,11,6,3,8,10,10,7,5,3,12,7,10,4,4,12,11,8,11,13,12,4,6,8,11,6,6,3,8,12,7,7,13,4,6,9,3,12,4,9,9,12,11,7,8,10,5,4,12,3,13,7,11,6,9,8,7,3,9,10,5,9,7,4,12,10,9,8',
                'awt' => 'rsf'
            ];

            if($LASTSPIN != null)
            {
                $arrLastSpin = (array)$LASTSPIN;
                foreach (array_keys($arrLastSpin) as $key) {
                    if (isset($res[$key])) unset($res[$key]);
                }
                $res = $res + $arrLastSpin;
            }
           
            return $res;
        }

        function doSpin($slotSettings, $postData)
        {
            $reelSetIndex = 0;
            $postData['slotEvent'] = 'bet';

            $fsmax = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') ?? 0;
            $fs = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') ?? 0;
            $fsopt = $slotSettings->GetGameData($slotSettings->slotId . 'FSOpt') ?? [];
            
            if ($fsmax > $fs) 
            {
                $postData['slotEvent'] = 'freespin';
            }

            $reelName = 'reels'.$reelSetIndex;

            $allbet = $postData['c'] * 18;
            if($postData['slotEvent'] == 'freespin')
            {                
                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);
            }
            else
            {
                $bankSum = $allbet / 100 * $slotSettings->GetPercent();
                $slotSettings->SetBank(($postData['slotEvent'] ?? ''), $bankSum, $postData['slotEvent']);
                $slotSettings->SetBet($allbet);
                $slotSettings->SetBalance(-$allbet, $postData['slotEvent']);
                $slotSettings->UpdateJackpots($allbet);
                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                $slotSettings->SetGameData($slotSettings->slotId . 'TotalFreeSpinWin', 0);
                $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', 0);
            }
            
            $betLine = $postData['c']; 

            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $allbet);
            $winType = $winTypeTmp[0];
            $spinWinLimit = $winTypeTmp[1];
            
            $spinAcquired = false;             

            $minReels = [];
            $minLineWins = [];
            $minTotalWin = -1;
            $minFreespinsWon = 0;
            $minJackpotCnt = 0;
            $minScatterCount = 0;
            $minBonusMpl = 1;
            $totalWin = 0;
            $lineWins = [];
            $reels = [];
            
            $wild = [2];
            $scatter = '1';
            $linesId = $slotSettings->Ways243ToLine();
            $bonusMpl = 1;
            for( $i = 0; $i <= 500; $i++ ) 
            {
                $totalWin = 0;
                $freespinsWon = 0;
                $lineWins = [];
                $cWins = array_fill(0, count($slotSettings->SymbolGame), 0);
                
                if($this->debug && $postData['slotEvent'] != 'freespin')
                {                 
                    $winType = 'bonus';
                }

                $bonusMpl = 1;
                if($postData['slotEvent'] == 'freespin')
                {
                    $fsOpt = $slotSettings->GetGameData($slotSettings->slotId . 'FSOpt');
                    $multipliers = $fsOpt['multipliers'];
                    $bonusMpl = $multipliers[rand(0, count($multipliers) - 1)];                    
                }

                $reels = $slotSettings->GetReelStrips($winType, $reelName);                
                
                for( $j = 0; $j < count($slotSettings->SymbolGame); $j++ ) 
                {                                            
                    $csym = $slotSettings->SymbolGame[$j];                                                
                    if( $csym == $scatter || !isset($slotSettings->Paytable['SYM_' . $csym]) ) 
                    {
                    }
                    else
                    {
                        $wscc = 0;
                        $cl = 0;
                        for( $swc = 1; $swc <= 5; $swc++ ) 
                        {
                            $isNext = false;
                            if( $reels['reel' . $swc][0] == $csym || in_array($reels['reel' . $swc][0], $wild) ) 
                            {
                                $isNext = true;
                            }
                            if( $reels['reel' . $swc][1] == $csym || in_array($reels['reel' . $swc][1], $wild) ) 
                            {
                                $isNext = true;
                            }
                            if( $reels['reel' . $swc][2] == $csym || in_array($reels['reel' . $swc][2], $wild) ) 
                            {
                                $isNext = true;
                            }
                            if( $isNext ) 
                            {
                                $wscc++;
                                if( $wscc == 3 || $wscc == 2 ) 
                                {
                                    $cl = 0;
                                }
                                if( $wscc == 4 ) 
                                {
                                    $cl = 1;
                                }
                                if( $wscc == 5 ) 
                                {
                                    $cl = 2;
                                }
                            }
                            else
                            {
                                break;
                            }
                        }
                        for( $k = 0; $k < count($linesId[$cl]); $k++ ) 
                        {
                            $lineWin = [];
                            $cWins[$k] = 0;
                            $s = [];
                            $p0 = $linesId[$cl][$k][0] - 1;
                            $p1 = $linesId[$cl][$k][1] - 1;
                            $p2 = $linesId[$cl][$k][2] - 1;
                            $p3 = $linesId[$cl][$k][3] - 1;
                            $p4 = $linesId[$cl][$k][4] - 1;
                            $s[0] = $reels['reel1'][$p0];
                            $s[1] = $reels['reel2'][$p1];
                            $s[2] = $reels['reel3'][$p2];
                            $s[3] = $p3 != -1 ? $reels['reel4'][$p3] : -1;
                            $s[4] = $p4 != -1 ? $reels['reel5'][$p4] : -1;
                            
                            if($wscc == 2)
                            {
                                if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) ) 
                                {
                                    $mpl = 1;
                                    if( in_array($s[0], $wild) && in_array($s[1], $wild) ) 
                                    {
                                        $mpl = 0;
                                    }
                                    else if( in_array($s[0], $wild) || in_array($s[1], $wild) ) 
                                    {
                                        $mpl = $bonusMpl;
                                    }
                                    $tmpWin = $slotSettings->Paytable['SYM_' . $csym][2] * $betLine * $mpl;
                                    if( $cWins[$k] < $tmpWin ) 
                                    {
                                        $cWins[$k] = $tmpWin;
                                        $lineWin = [0, $tmpWin, $this->getConvertedLine([$p0, $p1])];
                                    }
                                }
                            }
                            else if($wscc == 3)
                            {
                                if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) ) 
                                {
                                    $mpl = 1;
                                    if( in_array($s[0], $wild) && in_array($s[1], $wild) && in_array($s[2], $wild) ) 
                                    {
                                        $mpl = 0;
                                    }
                                    else if( in_array($s[0], $wild) || in_array($s[1], $wild) || in_array($s[2], $wild) ) 
                                    {
                                        $mpl = $bonusMpl;
                                    }
                                    $tmpWin = $slotSettings->Paytable['SYM_' . $csym][3] * $betLine * $mpl;
                                    if( $cWins[$k] < $tmpWin ) 
                                    {
                                        $cWins[$k] = $tmpWin;
                                        $lineWin = [0, $tmpWin, $this->getConvertedLine([$p0, $p1, $p2])];
                                    }
                                }
                            }
                            if($wscc == 4)
                            {
                                if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) ) 
                                {
                                    $mpl = 1;
                                    if( in_array($s[0], $wild) && in_array($s[1], $wild) && in_array($s[2], $wild) && in_array($s[3], $wild) ) 
                                    {
                                        $mpl = 0;
                                    }
                                    else if( in_array($s[0], $wild) || in_array($s[1], $wild) || in_array($s[2], $wild) || in_array($s[3], $wild) ) 
                                    {
                                        $mpl = $bonusMpl;
                                    }
                                    $tmpWin = $slotSettings->Paytable['SYM_' . $csym][4] * $betLine * $mpl;
                                    if( $cWins[$k] < $tmpWin ) 
                                    {
                                        $cWins[$k] = $tmpWin;
                                        $lineWin = [0, $tmpWin, $this->getConvertedLine([$p0, $p1, $p2, $p3])];
                                    }
                                }
                            }
                            else if($wscc == 5)
                            {
                                if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) ) 
                                {
                                    $mpl = 1;
                                    if( in_array($s[0], $wild) && in_array($s[1], $wild) && in_array($s[2], $wild) && in_array($s[3], $wild) && in_array($s[4], $wild) ) 
                                    {
                                        $mpl = 0;
                                    }
                                    else if( in_array($s[0], $wild) || in_array($s[1], $wild) || in_array($s[2], $wild) || in_array($s[3], $wild) || in_array($s[4], $wild) ) 
                                    {
                                        $mpl = $bonusMpl;
                                    }
                                    $tmpWin = $slotSettings->Paytable['SYM_' . $csym][5] * $betLine * $mpl;
                                    if( $cWins[$k] < $tmpWin ) 
                                    {
                                        $cWins[$k] = $tmpWin;
                                        $lineWin = [0, $tmpWin, $this->getConvertedLine([$p0, $p1, $p2, $p3, $p4])];
                                    }
                                }
                            }
                            if( $cWins[$k] > 0 && count($lineWin) > 0 ) 
                            {
                                array_push($lineWins, $lineWin);
                                $totalWin += $cWins[$k];
                            }
                        }                                                
                    }
                }
                
                $scatterCount = 0;
                for($r = 0; $r < 5; $r++)                
                    for($c = 0; $c < 3; $c++)
                        if($reels['reel'.($r+1)][$c] == $scatter)
                            $scatterCount++;
                if($scatterCount > 2 && $winType != 'bonus')
                    continue;

                $scattersWin = 0;
                if($scatterCount > 2)
                {
                    if($winType != 'bonus')
                        continue;
                    // $scattersWin = $slotSettings->Paytable[$scatter][$scatterCount] * $betLine;
                }

                $totalWin += $scattersWin;
                //calc jackpot symbol
                $jackpotCnt = 0;                
                $jackpotPos = [];
                
                // for($r = 0; $r < 5; $r++)
                //     for($c = 0; $c < 3; $c++)
                //     {
                //         if($reels['reel'.($r+1)][$c] == $jackpotSym)
                //         {
                //             $jackpotCnt++;
                //             $jackpotPos[] = $c * 5 + $r;
                //         }
                //     }

                if($minTotalWin == -1 && $scatterCount < 3 || ($minTotalWin > $totalWin && $totalWin > 0))
                {
                    $minTotalWin = $totalWin;
                    $minLineWins = $lineWins;
                    $minFreespinsWon = $freespinsWon;
                    $minReels = $reels;
                    $minJackpotCnt = $jackpotCnt;
                    $minScatterCount = $scatterCount;
                    $minBonusMpl = $bonusMpl;
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
                else if( $winType == 'none' && $totalWin == 0 ) 
                {
                    break;
                }
            }

            if(!$spinAcquired && ($minTotalWin > 0 || $scatterCount > 2))
            {                
                $totalWin = $minTotalWin;
                $lineWins = $minLineWins;
                $freespinsWon = $minFreespinsWon;
                $reels = $minReels;
                $scatterCount = $minScatterCount;
                $bonusMpl = $minBonusMpl;
            }

            $jackpotReels = [];
            $jackpotWin = 0;
            if($scatterCount < 3)
            {
                if($winType == 'win' && $totalWin + $allbet * 15 < $spinWinLimit && rand(0, 100) < 5)
                {
                    $jackpotWin = $allbet * 15;
                    $totalWin += $jackpotWin;
                    while(count($jackpotReels) < 3)
                    {
                        $jr = rand(0, 4);
                        if(!in_array($jr, $jackpotReels))
                            $jackpotReels[] = $jr;
                    }
                }
            }

            $oldBalance = $slotSettings->GetBalance();
            if($totalWin > 0)
            {
                $slotSettings->SetBank($postData['slotEvent'], -1 * $totalWin);
                $slotSettings->SetBalance($totalWin);
                $slotSettings->SetWin($totalWin);
            }

            $syms = [];
            for($c = 0; $c < 3; $c++)
                for($r = 0; $r < 5; $r++)
                    $syms[] = $reels['reel'.($r+1)][$c];

            $res = [
                'tw' => $totalWin,
                'balance' => $oldBalance,
                'index' =>  $postData['index'],
                'balance_cash' => $oldBalance,
                'balance_bonus' => '0.00',
                'na' => 's', //next action
                'aw' => '1',
                'stime' => floor(microtime(true) * 1000),
                'sa' => '3,7,5,1,5',
                'sb' => '6,4,8,7,7',
                'sh' => '3',
                'c' => $allbet,
                'sver' => '5',
                'n_reel_set' => $reelSetIndex,
                'counter' => ((int)$postData['counter'] + 1),
                's' => implode(',', $syms),
                'w' => $totalWin - $jackpotWin,
                'awt' => 'rsf'
            ];

            if($jackpotWin > 0)
            {
                $res['bgid'] = 0;
                $res['coef'] = $allbet;
                $res['rw'] = $jackpotWin;
                $res['bgt'] = 33;
                $res['bw'] = 1;
                $res['wp'] = 15;
                $res['end'] = 1;
                $res['gri'] = implode(',', $jackpotReels);
            }
            if($totalWin > 0)
            {
                foreach($lineWins as $index => $lineWin)
                {
                    $res['l'.$index] = implode('~', $lineWin);
                }
                if($postData['slotEvent'] != 'freespin')
                    $res['na'] = 'c';
            }

            if($scatterCount > 2)
            {
                if($postData['slotEvent'] == 'freespin')
                {

                }
                else
                {
                    //trigger freespin
                    $str_fs_opt = $this->stringifyFSOptions($slotSettings->fsOpts);
                    $res['fs_opt'] = $str_fs_opt;
                    $res['fs_opt_mask'] = 'fs,m,ts,rm';
                    $res['na'] = 'fso';
                }
            }
      

            $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin') + $totalWin);
            if($postData['slotEvent'] == 'freespin')
            {
                $slotSettings->SetGameData($slotSettings->slotId . 'TotalFreeSpinWin', $slotSettings->GetGameData($slotSettings->slotId . 'TotalFreeSpinWin') + $totalWin);
                $res['balance'] = $slotSettings->GetGameData($slotSettings->slotId . 'FSStartBalance');
                $res['balance_cash'] = $slotSettings->GetGameData($slotSettings->slotId . 'FSStartBalance');
                $res['tw'] = $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin');
                $res['fsmul'] = 1;
                $res['fsmax'] = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                $res['fswin'] = $slotSettings->GetGameData($slotSettings->slotId . 'TotalFreeSpinWin');                
                $res['fs'] = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1;
                $res['fsres'] = $slotSettings->GetGameData($slotSettings->slotId . 'TotalFreeSpinWin');
                $res['fsopt_i'] = $slotSettings->GetGameData($slotSettings->slotId . 'FSOptIndex');

                //check wild symbols
                $wildSyms = [];
                for($r = 0; $r < 5; $r++)
                    for($c = 0; $c < 3; $c++)
                    {
                        if($reels['reel'.($r+1)][$c] == $wild[0])
                            $wildSyms[] = $c * 5 + $r;
                    }
                $res['wrlm_res'] = '2~'.$bonusMpl.'~'.implode(',', $wildSyms);

                $fsOpt = $slotSettings->GetGameData($slotSettings->slotId . 'FSOpt');
                $multipliers = $fsOpt['multipliers'];
                for($i = 0; $i < count($multipliers); $i++)
                    if($multipliers[$i] == $bonusMpl)
                        break;
                $res['wrlm_cs'] = '2~'.($i+1);

                if($slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') >= $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames'))
                {
                    unset($res['fs']);
                    unset($res['fsmul']);
                    unset($res['fsmax']);
                    unset($res['fswin']);
                    unset($res['fsres']);
                    $res['fs_total'] = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                    $res['fswin_total'] = $slotSettings->GetGameData($slotSettings->slotId . 'TotalFreeSpinWin');
                    $res['fsmul_total'] = 1;
                    $res['fsc_win_total'] = $slotSettings->GetGameData($slotSettings->slotId . 'TotalFreeSpinWin');
                    $res['fsres_total'] = $slotSettings->GetGameData($slotSettings->slotId . 'TotalFreeSpinWin');
                    $res['fsc_res_total'] = $slotSettings->GetGameData($slotSettings->slotId . 'TotalFreeSpinWin');
                    $res['fsc_mul_total'] = 1;
                    $res['fsc_total'] = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                    $res['na'] = 'c';
                }
                $allbet = 0;
            }

            $slotSettings->SaveLogReport(json_encode($res), $allbet, $totalWin, $postData['slotEvent']);        
            return $res;
        }

        public function doCollect($slotSettings, $postData) {
            $BALANCE = $slotSettings->GetBalance();
    
            $objRes = [
                'action' => 'doCollect',    
                'balance' => $BALANCE,
                'index' => $postData['index'],
                'balance_cash' => $BALANCE,
                'balance_bonus' => '0.00',
                'na' => 's',
                'stime' => floor(microtime(true) * 1000),
                'sver' => '5',
                'counter' => ((int)$postData['counter'] + 1),
            ];
            
            return $objRes;
        }

        public function doFSOption($slotSettings, $postData, $LASTSPIN) {
            $BALANCE = $LASTSPIN->balance;
    
            /* freespin option */
            $fsopt_i = $postData['ind'];
    
            $fs_opts = $slotSettings->fsOpts;
    
            /* mystery option determine by random, exclude last option */
            $rand_spin_opt = random_int(0, count($fs_opts) - 2);
            $rand_multipliers_opt = random_int(0, count($fs_opts) - 2);
    
            $fs_opts[6]['spin_count'] = $fs_opts[$rand_spin_opt]['spin_count'];
            $fs_opts[6]['multipliers'] = $fs_opts[$rand_multipliers_opt]['multipliers'];
    
            $str_fs_opt = $this->stringifyFSOptions($fs_opts);
    
            $fsmax = $fs_opts[$fsopt_i]['spin_count'];
            $fs = 1;
    
            $objRes = [
                'action' => 'doFSOption',    
                'fsmul' => '1',
                'fs_opt_mask' => 'fs,m,ts,rm',
                'balance' => $BALANCE,
                'fsmax' => $fsmax,
                'index' => $postData['index'],
                'balance_cash' => $BALANCE,
                'balance_bonus' => '0',
                'na' => 's',
                'fswin' => '0',
                'stime' => floor(microtime(true) * 1000),
                'fs' => $fs,
                'fs_opt' => $str_fs_opt,
                'fsres' => '0',
                'sver' => '5',
                'counter' => ((int)$postData['counter'] + 1),
                'fsopt_i' => $fsopt_i,
            ];
    
            $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $fsmax);
            $slotSettings->SetGameData($slotSettings->slotId . 'TotalFreeSpinWin', 0);
            $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', 0);
            $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
            $slotSettings->SetGameData($slotSettings->slotId . 'FSOpt', $fs_opts[$fsopt_i]);
            $slotSettings->SetGameData($slotSettings->slotId . 'FSOptIndex', $fsopt_i);
            $slotSettings->SetGameData($slotSettings->slotId . 'FSStartBalance', $BALANCE);            
    
            $_GameLog = json_encode(array_merge($objRes, ['start_with' => $LASTSPIN]));
            $slotSettings->SaveLogReport($_GameLog, 0, 0, 'freespin');
    
            return $objRes;
        }
        
        public function stringifyFSOptions($fs_opts) {
            $fs_opts = array_map(function($opt) {
                $multipliers = implode(";", $opt['multipliers']);
                return "${opt['spin_count']},${opt['val1']},${opt['val2']},${multipliers}";
            }, $fs_opts);
    
            $res = implode("~", $fs_opts);
            return $res;
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

        public function toResponse($obj) {
            $response = '';
            foreach ($obj as $key => $value) {
                if ($value !== null) {
                    $response = "{$response}&{$key}={$value}";
                }
            }
    
            /* remove double quotes around key for javascript */
            $response = preg_replace('/"(\w+)":/i', '\1:', $response);
            return trim($response, "&");
        }
    }

}


