<!DOCTYPE html>
<html>

<head lang="en">
    <script src="/frontend/Default/js/jquery-3.5.1.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1.0, maximum-scale=1.0" />
    <link rel="stylesheet" type="text/css" href="/frontend/Default/css/main.css" />
    <link rel="stylesheet" type="text/css" href="/frontend/Default/css/notification.css" />
    <!-- PixiJS must be imported before @pixi/sound -->
    <script src="https://unpkg.com/pixi.js/dist/browser/pixi.min.js"></script>

    <!-- found here, if not using CDN "./node_modules/@pixi/sound/dist/pixi-sound.js" -->
    <script src="https://unpkg.com/@pixi/sound/dist/pixi-sound.js"></script>
    <script src="/frontend/Default/js/PreventZoom.js"></script>
    <script src="/frontend/Default/js/fullMobile.js"></script>
    <script src="/js/jquery.fullscreen-min.js"></script>
    <link rel="stylesheet" type="text/css" href="/frontend/Default/css/style-mobile.css" />
</head>

<body style="margin:0px; background-color:black">
    @yield('content')
    @include('frontend.Default.partials.bonus_popup')
    @yield('scripts')
    <div class="home-btn" style="display: {{$game->category_temp == 2 ? 'none' : 'block'}}"></div>
    <div id="mask" class="mask" style="opacity: 0;z-index: -999;">
        <div style="z-index: 1001; top: 8vh; left: 50vw; transform: translate(-50%, -50%); position: fixed;">
            <div id="mask_close" style="width:50vw; font-size:22px; font-weight:bold; color: #EEE8AA; display: none;"></div>
        </div>
        <div id="swipe"></div>
    </div>
    <div id="bar" class="absvh"></div>
    <script>
        addEventListener('message', function(ev) {
            if (ev.data == 'CloseGame') {
                var isFramed = false;
                try {
                    isFramed = window != window.top || document != top.document || self.location != top.location;
                } catch (e) {
                    isFramed = true;
                }

                if (isFramed) {
                    window.parent.postMessage('CloseGame', "*");
                }
                document.location.href = "/";
            }
        });

        $('.home-btn').on('click', function() {
            window.location.href = "/";
        });
    </script>
    <script src="/frontend/Default/js/jpghandler.js"></script>
</body>

</html>