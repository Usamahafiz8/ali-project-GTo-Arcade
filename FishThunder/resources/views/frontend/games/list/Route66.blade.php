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
            try
            {
                JSBridge.sendMessageToNative("portrait");    
            }catch(e){}
            try
            {
                window.webkit.messageHandlers.swiftJSBridge.postMessage("portrait");
            }catch(e){}
            var url = '/games/Route66/web-mobile/index.html?_token={!! csrf_token() !!}';
            
            $('#mainframe').attr('src', url);
            $('.home-btn').css('display', 'none');
        });
        
    </script>
@endsection