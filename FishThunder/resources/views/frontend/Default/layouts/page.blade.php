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
    <script src="/frontend/Default/js/bootstrap/bootstrap.min.js"></script>
    <script src="/js/jquery.fullscreen-min.js"></script>
</head>

<body class="@yield('add-body-class')" style="padding-left: 100px; padding-right: 100px;">

<script type="text/javascript">
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
#need-fullscreen-popup {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1000;
    width: 100%;
    height: 100%;
    background-color: black;
    display: flex;
    flex-direction: column;
    align-content: center;
    justify-content: center;
}
.fullscreen-popup-img {
    align-self: center;
    max-width: 225px;
    width: 100%;
    max-height: 184px;
    user-select: none;
}
.fullscreen-popup-text {
    user-select: none;
    color: white;
    font-size: 30px;
    text-align: center;
}

</style>

<!-- MAIN -->
<main class="main">
    @yield('content')
    <!-- Start Fullscreen and orientation code -->   
</main>

<!-- /.MAIN -->
@yield('footer')

@yield('scripts')

</body>
</html>
