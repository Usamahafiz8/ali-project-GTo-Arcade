<?php 
namespace VanguardLTE\Games\BeautyAndBeastYGG
{
    class GameReel
    {
        public $reelsStrip = [
            'reelStrip1' => [], 
            'reelStrip2' => [], 
            'reelStrip3' => [], 
            'reelStrip4' => [], 
            'reelStrip5' => [], 
            'reelStrip6' => []
        ];
        public $reelsStripBonus = [
            'reelStripBonus1' => [], 
            'reelStripBonus2' => [], 
            'reelStripBonus3' => [], 
            'reelStripBonus4' => [], 
            'reelStripBonus5' => [], 
            'reelStripBonus6' => []
        ];
        public $reelsStripBonusB = [
            'reelStripBonusB1' => [], 
            'reelStripBonusB2' => [], 
            'reelStripBonusB3' => [], 
            'reelStripBonusB4' => [], 
            'reelStripBonusB5' => [], 
            'reelStripBonusB6' => []
        ];
        public $reelsStripBonusC = [
            'reelStripBonusC1' => [], 
            'reelStripBonusC2' => [], 
            'reelStripBonusC3' => [], 
            'reelStripBonusC4' => [], 
            'reelStripBonusC5' => [], 
            'reelStripBonusC6' => []
        ];
        public $reelsStripSuperbet1 = [
            'reelStripSuperbet11' => [], 
            'reelStripSuperbet12' => [], 
            'reelStripSuperbet13' => [], 
            'reelStripSuperbet14' => [], 
            'reelStripSuperbet15' => [], 
            'reelStripSuperbet16' => []
        ];
        public $reelsStripSuperbet2 = [
            'reelStripSuperbet21' => [], 
            'reelStripSuperbet22' => [], 
            'reelStripSuperbet23' => [], 
            'reelStripSuperbet24' => [], 
            'reelStripSuperbet25' => [], 
            'reelStripSuperbet26' => []
        ];
        public $reelsStripSuperbet3 = [
            'reelStripSuperbet31' => [], 
            'reelStripSuperbet32' => [], 
            'reelStripSuperbet33' => [], 
            'reelStripSuperbet34' => [], 
            'reelStripSuperbet35' => [], 
            'reelStripSuperbet36' => []
        ];
        public function __construct()
        {
            $temp = file(base_path() . '/app/Games/BeautyAndBeastYGG/reels.txt');
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
                if( isset($this->reelsStripBonus[$str[0]]) ) 
                {
                    $data = explode(',', $str[1]);
                    foreach( $data as $elem ) 
                    {
                        $elem = trim($elem);
                        if( $elem != '' ) 
                        {
                            $this->reelsStripBonus[$str[0]][] = $elem;
                        }
                    }
                }
                if( isset($this->reelsStripBonusB[$str[0]]) ) 
                {
                    $data = explode(',', $str[1]);
                    foreach( $data as $elem ) 
                    {
                        $elem = trim($elem);
                        if( $elem != '' ) 
                        {
                            $this->reelsStripBonusB[$str[0]][] = $elem;
                        }
                    }
                }
                if( isset($this->reelsStripBonusC[$str[0]]) ) 
                {
                    $data = explode(',', $str[1]);
                    foreach( $data as $elem ) 
                    {
                        $elem = trim($elem);
                        if( $elem != '' ) 
                        {
                            $this->reelsStripBonusC[$str[0]][] = $elem;
                        }
                    }
                }
                if( isset($this->reelsStripSuperbet1[$str[0]]) ) 
                {
                    $data = explode(',', $str[1]);
                    foreach( $data as $elem ) 
                    {
                        $elem = trim($elem);
                        if( $elem != '' ) 
                        {
                            $this->reelsStripSuperbet1[$str[0]][] = $elem;
                        }
                    }
                }
                if( isset($this->reelsStripSuperbet2[$str[0]]) ) 
                {
                    $data = explode(',', $str[1]);
                    foreach( $data as $elem ) 
                    {
                        $elem = trim($elem);
                        if( $elem != '' ) 
                        {
                            $this->reelsStripSuperbet2[$str[0]][] = $elem;
                        }
                    }
                }
                if( isset($this->reelsStripSuperbet3[$str[0]]) ) 
                {
                    $data = explode(',', $str[1]);
                    foreach( $data as $elem ) 
                    {
                        $elem = trim($elem);
                        if( $elem != '' ) 
                        {
                            $this->reelsStripSuperbet3[$str[0]][] = $elem;
                        }
                    }
                }
            }
        }
    }

}
