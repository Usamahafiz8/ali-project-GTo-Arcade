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
            if(window.vuplex)
            {
                window.vuplex.postMessage('landscape');
            }
            else
            {
                window.addEventListener('vuplexready', () => {
                    window.vuplex.postMessage('landscape');
                })
            }
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
            
            url = '/games/{{ $game->name }}/www.goldentreasure.mobi/game/index.html?gameId=1028&uid=712&token=O4cEwky45sXlfoZkJUnjQaais236&cdn=games/BuffaloThunder/cdn.goldentreasure.mobi&musicSwitch=true&lang=en&domainName=www.goldenfortune.game&type=1&gl=1028,1128,1228';
            $('#mainframe').attr('src', url);
        });
    </script>
@endsection