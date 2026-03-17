@extends('frontend.games.layouts.app')
@section('content')
    <iframe id="mainframe" style="overflow:hidden;height:100%;width:100%;position:absolute;top:0px;left:0px;right:0px;bottom:0px" ></iframe>
    @include ('frontend.games.partials.popups')
@endsection
@section('scripts')
    <script type="text/javascript">
        if( !sessionStorage.getItem('sessionId') ){
            sessionStorage.setItem('sessionId', parseInt(Math.random() * 1000000));
        }
        $(document).ready(function() {
            window.localStorage.setItem('game_orientation', 'portrait');
            $('.home-btn').css('display', 'none');
            var isMobile;
            if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)){
                // true for mobile device                
                isMobile = true;
            }
            else 
            {
                // false for not mobile device
                isMobile = false;
            }
            var url;
            url = '/games/CoinPusherPirateKing/index.html?access_token=(*--)dd12f3635bc9599ca58012601d07197c&lang=eng&ccy=NON&sm=00&subid=0&fullscr=1&lc=en-US&pm=1&ns=0&anal=10&lb=1&stf=1';
            $('#mainframe').attr('src', url);
        });
    </script>
@endsection