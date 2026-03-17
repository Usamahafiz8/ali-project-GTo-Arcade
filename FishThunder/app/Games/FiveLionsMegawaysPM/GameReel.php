<?php 
namespace VanguardLTE\Games\FiveLionsMegawaysPM
{
    class GameReel
    {
        public $reelsStrip = [
            'reelStrip0_0' => [],
            'reelStrip0_1' => [],
            'reelStrip0_2' => [],
            'reelStrip0_3' => [],
            'reelStrip0_4' => [],
            'reelStrip0_5' => [],

            'reelStrip1_0' => [],
            'reelStrip1_1' => [],
            'reelStrip1_2' => [],
            'reelStrip1_3' => [],
            'reelStrip1_4' => [],
            'reelStrip1_5' => [],

            'reelStrip2_0' => [],
            'reelStrip2_1' => [],
            'reelStrip2_2' => [],
            'reelStrip2_3' => [],
            'reelStrip2_4' => [],
            'reelStrip2_5' => [],

            'reelStrip3_0' => [],
            'reelStrip3_1' => [],
            'reelStrip3_2' => [],
            'reelStrip3_3' => [],
            'reelStrip3_4' => [],
            'reelStrip3_5' => [],

            'reelStrip4_0' => [],
            'reelStrip4_1' => [],
            'reelStrip4_2' => [],
            'reelStrip4_3' => [],
            'reelStrip4_4' => [],
            'reelStrip4_5' => [],

            'reelStrip5_0' => [],
            'reelStrip5_1' => [],
            'reelStrip5_2' => [],
            'reelStrip5_3' => [],
            'reelStrip5_4' => [],
            'reelStrip5_5' => [],

            'reelStrip6_0' => [],
            'reelStrip6_1' => [],
            'reelStrip6_2' => [],
            'reelStrip6_3' => [],
            'reelStrip6_4' => [],
            'reelStrip6_5' => [],

            'reelStrip7_0' => [],
            'reelStrip7_1' => [],
            'reelStrip7_2' => [],
            'reelStrip7_3' => [],
            'reelStrip7_4' => [],
            'reelStrip7_5' => [],

            'reelStrip8_0' => [],
            'reelStrip8_1' => [],
            'reelStrip8_2' => [],
            'reelStrip8_3' => [],
            'reelStrip8_4' => [],
            'reelStrip8_5' => [],
        ];
        
        public function __construct()
        {
            $temp = file(base_path() . '/app/Games/FiveLionsMegawaysPM/reels.txt');
            foreach( $temp as $str ) 
            {
                $str = explode('=', $str);
                if( isset($this->reelsStrip[$str[0]]) ) 
                {
                    $data = explode(',', $str[1]);
                    foreach( $data as $elem ) 
                    {
                        $elem = trim($elem);
                        if( $elem != '' ) 
                        {
                            $this->reelsStrip[$str[0]][] = $elem;
                        }
                    }
                }
            }
        }
    }

}
