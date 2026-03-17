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
            url = '/games/SmokingHotFruits1x2/game/smokinghotfruits20/index.html?acc_id=5%7C1x2test%40USD&language=en&lang=en&gameID=8115&gameName=Smoking+Hot+Fruits+20&gameType=OPENSLOTS&gameVersion=115&playMode=real&path=/games/SmokingHotFruits1x2/&site=2746&installID=29&proLeague=PREM&proLeagueName=null&jurisdiction=uk&realitycheck_uk_elapsed=0&realitycheck_uk_limit=600&realitycheck_uk_proceed=&realitycheck_uk_exit=&realitycheck_uk_history=&realitycheck_uk_autospin=&rc_info=null&ukgc_link=null&desktop_launch=true&isQuickFire=null&clientName=NYX_DEV&folderName=smokinghotfruits20&channel=desktop&pathCDN=/games/SmokingHotFruits1x2/&geolocation=null&confType=null&keepAliveInterval=null&keepAliveURL=null&lobbyurl=&wsPath=test&balanceBeforeSpin=null&force=null&forceMobile=null&NYX_GCM_ENV=null&NYX_GCM_PARAMS=null&elapsed_session_time=null&dev=null&terminal=false&referrer=null&slot_framework=';
            $('#mainframe').attr('src', url);
        });
    </script>
@endsection