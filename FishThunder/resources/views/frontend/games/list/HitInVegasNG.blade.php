@extends('frontend.games.layouts.app')
@section('content')
    <iframe id="mainframe" style="overflow:hidden;height:100%;width:100%;position:absolute;left: 50%;transform: translate(-50%, 0%);" ></iframe>
    @include ('frontend.games.partials.popups')
@endsection
@section('scripts')
    <script type="text/javascript">        
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
        var qstr = '?launcher=true&sfx_500236410=1616417992679&commonVersion=(build%2088)&game=277&userId=003403186056&wshost=&quality=high&lang=en&noframe=yes';
        if(isMobile)
            qstr = '?launcher=true&sfx_500236410=1616417992679&commonVersion=(build%2088)&game=277&userId=003403186056&wshost=&quality=hd&lang=en&noframe=yes';
        $(document).ready(function() {
            
            
            var url;
            if(isMobile)
                url = '/games/HitInVegasNG/index.html';
            else
                url = '/games/HitInVegasNG/index.html';
            url += qstr;
            $('#mainframe').attr('src', url);
            var height = parseInt($('#mainframe').height());
            var width = parseInt(height * 16 / 9);         
            $('#mainframe').css('width', width + 'px');
            $('#mainframe').css('height', height + 'px');            
            console.log("width: " + width + " height: " + height);            
        }); 

        if (!sessionStorage.getItem('sessionId')) {
            sessionStorage.setItem('sessionId', parseInt(Math.random() * 1000000));
        }

        var uparts = document.location.href.split("?");
       
        var exitUrl = '';
        if (document.location.href.split("api_exit=")[1] != undefined) {
            exitUrl = document.location.href.split("api_exit=")[1].split("&")[0];
        }
        addEventListener('message', function (ev) {

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
@endsection