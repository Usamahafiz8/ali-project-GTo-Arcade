@extends('frontend.games.layouts.app')
@section('content')
    <iframe id="mainframe" style="overflow:hidden;height:100%;width:100%;position:absolute;top:0px;left:0px;right:0px;bottom:0px" ></iframe>
    @include ('frontend.games.partials.popups')
@endsection
@section('scripts')
    <script type="text/javascript">
        window.localStorage.setItem('game_orientation', 'portrait');
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
                try
                {
                    JSBridge.sendMessageToNative("portrait");    
                }catch(e){}
                try
                {
                    window.webkit.messageHandlers.swiftJSBridge.postMessage("portrait");
                }catch(e){}
            }
            else 
            {
                // false for not mobile device
                isMobile = false;
            }
            var url;
            url = '/games/TripleCashWheel/acy-resource.wimobile.casinarena.com/resource-service/game.html';
            $('#mainframe').attr('src', url);
        });
    </script>
@endsection