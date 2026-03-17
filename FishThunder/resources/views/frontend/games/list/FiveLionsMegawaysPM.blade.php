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
            url = '/games/FiveLionsMegawaysPM/index.html?jackpotid=0&gname=5%20Lions%20Megaways&extGame=1&ext=0&cb_target=exist_tab&symbol=vswayslions&jurisdictionID=99&lobbyUrl=js://window.close();&minilobby=false&mgckey=AUTHTOKEN@{{isset($userId)?$userId:0}}~stylename@sparkle~SESSION@8ffd2c8e-0ba2-4730-9f94-44f1c318b483&tabName=&userID={{$userId ?? 0}}&styleName={{$styleName ?? ""}}&replayUrl={{$replayUrl ?? ""}}';
            $('#mainframe').attr('src', url);
        });
    </script>
@endsection