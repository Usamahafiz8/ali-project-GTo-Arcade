<!DOCTYPE html>
<html>

<head lang="en">    
    <script src="/frontend/Default/js/jquery-3.5.1.min.js"></script>
    <link rel="stylesheet" type="text/css" href="/frontend/Default/css/main.css" />
    <script type="text/javascript">
        if (!sessionStorage.getItem('sessionId')) {
            sessionStorage.setItem('sessionId', parseInt(Math.random() * 1000000));
        }
        $(document).ready(function() {            
            try
            {
                JSBridge.sendMessageToNative("portrait");    
            }catch(e){}
            try
            {
                window.webkit.messageHandlers.swiftJSBridge.postMessage("portrait");
            }catch(e){}
            var url = '/games/UltraBlazingFireLink/index.html';
            $('#mainframe').attr('src', url);
        });

        var exitUrl = '';
        if (document.location.href.split("api_exit=")[1] != undefined) {
            exitUrl = document.location.href.split("api_exit=")[1].split("&")[0];
        }
    
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
                document.location.href = exitUrl;
            }

        });
    </script>
</head>

<body style="overflow: hidden; background:black">
    <iframe id="mainframe" style="overflow:hidden;height:100%;width:100%;position:absolute;top:0px;left:0px;right:0px;bottom:0px"></iframe>
    <div class="home-btn" style="right: 10px; left: auto;">

    </div>
    <script>
        $('.home-btn').on('click', function(){
            if(window.vuplex)
            {
                window.vuplex.postMessage('exitToHub');
                window.location.href = "/empty";
                return;
            }
            window.postMessage('CloseGame', "*");
        })
    </script>
</body>


</html>