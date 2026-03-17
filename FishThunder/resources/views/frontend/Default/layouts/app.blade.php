<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<title>{{ settings('app_name') }}</title>
    <!-- <meta name="apple-mobile-web-app-capable" content="yes" /> -->
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<meta name="description" content="{{ settings('app_name') }}">
	<!-- <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" /> -->
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1.0, maximum-scale=1.0" />
	<link rel="stylesheet" href="/css/bootstrap.min.css">	    
    <!-- Style css -->
    
    <script src="/frontend/Default/js/jquery-3.5.1.min.js"></script>
    <script src="/frontend/Default/js/PreventZoom.js"></script>
    <script src="/frontend/Default/js/fullMobile.js"></script>
    <script src="/back/bower_components/moment/min/moment.min.js"></script>
    <script src="/back/bower_components/moment/min/moment-timezone-with-data-1970-2030.min.js"></script>
    <script src="/frontend/Default/js/bootstrap/bootstrap.min.js"></script>
    <script src="/js/jquery.fullscreen-min.js"></script>
    <link rel="stylesheet" type="text/css" href="/frontend/Default/css/main.css" />
    <link rel="stylesheet" type="text/css" href="/frontend/Default/css/style-mobile.css" />
</head>

<body class="@yield('add-body-class')" style="background-color: black">

<script type="text/javascript">
    window.localStorage.setItem('game_orientation', 'landscape');
	var is_games_page =@if(isset($is_game_page)) true @else false @endif;
	var terms_and_conditions = @if(Auth::check() && auth()->user()->shop && auth()->user()->shop->rules_terms_and_conditions && !auth()->user()->agreed) true @else false @endif;
</script>

<style>    
html {
    -ms-touch-action: none;
}

body,
canvas,
div {
    display: block;
    outline: none;
    -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
    user-select: none;
    -moz-user-select: none;
    -webkit-user-select: none;
    -ms-user-select: none;
    -khtml-user-select: none;
    -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
}
.bgvideo {
    position: fixed;
    right: 0;
    bottom: 0;
    object-fit: cover;
    width: 100%;
    height: 100%;
    z-index: -9999;
}
</style>

<!-- MAIN -->
<main class="main">
    <video preload="metadata" autoplay playsinline loop muted class="bgvideo" id="bgvideo" style="display: block;">
        <source src="/images/fish/back.mp4" type="video/mp4">
    </video>
    @yield('content')
    <!-- Start Fullscreen and orientation code -->   
</main>
<div id="orientation"></div>
<div id="mask" class="mask" style="opacity: 0;z-index: -999;">
    <div style="z-index: 1001; top: 8vh; left: 50vw; transform: translate(-50%, -50%); position: fixed;">
        <div id="mask_close" style="width:50vw; font-size:22px; font-weight:bold; color: #EEE8AA; display: none;"></div>
    </div>
    <div id="swipe"></div>
</div>
<div id="bar" class="absvh"></div>


<!-- /.MAIN -->
@yield('footer')

<script type="text/javascript">
    $(function() {
		moment.tz.setDefault("{{ config('app.timezone') }}");
	});
    addEventListener('message', function(e)
    {
        try{
            var data = JSON.parse(e.data);            
            if(data.event == 'Logout')
            {
                console.log("logout event from mobile ");
                try
                {
                    JSBridge.sendMessageToNative("logout");    
                }catch(e){}
                try
                {
                    window.webkit.messageHandlers.swiftJSBridge.postMessage("logout")
                }catch(e){}
            }
            else if(data.event == 'Log')
            {
                console.log(JSON.stringify(data.value));
                try
                {
                    JSBridge.sendMessageToNative(JSON.stringify(data.value));
                }catch(e){}
                try
                {
                    window.webkit.messageHandlers.swiftJSBridge.postMessage(JSON.stringify(data.value));
                }catch(e){}
            }
        }
        catch(e){}
    });
</script>

@yield('scripts')

</body>
</html>
