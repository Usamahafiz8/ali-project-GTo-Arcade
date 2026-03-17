var isScrolling = 0;
var checkScrollInterval = null;
var scrollToResetTimeOut = null;
var isiOS13Up = Boolean(navigator.userAgent.match(/OS 1[3-9](_\d)* like Mac OS X/i));
var mob = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

window.addEventListener("orientationchange", changeOrientation, false);
window.addEventListener("scroll", SetIsScrolling(1));
window.addEventListener("load", (e) => {
    document.addEventListener('touchmove', function(e){
        var t = window.document.getElementById("bar"),
        h = window.innerHeight;
        if(t)
        {
            if (t.clientHeight === h )
            {
                e.preventDefault();
            }
        }
    });

    window.document.getElementById("mask").addEventListener("touchend", scrolling);
    var orient = document.getElementById("orientation");
    if(orient != null)
        orient.style.setProperty("display", "none");
    screenFix();
    if(isChrome())
    {
        !mob ? window.addEventListener('mousedown', requestChromeFullScreen) : window.addEventListener('touchend', requestChromeFullScreen);
    }
});

function scrolling(){
    if(checkScrollInterval)
    {
        clearInterval(checkScrollInterval);
        checkScrollInterval = null;
    }
    if(scrollToResetTimeOut)
    {
        clearInterval(scrollToResetTimeOut);
        scrollToResetTimeOut =null;        
    }
    scrollToResetTimeOut = setTimeout(scrollToReset, 500);    
}

function changeOrientation()
{
    screenFix();
}

window.onresize = function(){
    screenFix();
}

function isSafari()
{
    return /Safari/.test(navigator.userAgent) && !/Chrome/.test(navigator.userAgent) && !/CriOS/.test(navigator.userAgent) && !/FxiOS/.test(navigator.userAgent) && mob
}

function isChrome()
{
    return /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
}

function screenFix()
{
    if (isSafari())
    {
        SafariSwipe();
    }    

    if (!mob) return;            
    var orient = document.getElementById("orientation");
    if(orient == null)
        return;
    setTimeout(() => {
        var orientation = (screen.orientation || {}).type || screen.mozOrientation || screen.msOrientation;            
        var game_orientation = window.localStorage.getItem('game_orientation');
        if (orientation === "portrait-secondary" || orientation === "portrait-primary") {
            if(game_orientation != 'portrait')
            {
                orient.style.setProperty("visibility", "visible");   
                orient.style.display = "inline";
                return;
            }        
            else
            {
                orient.style.display = "none";
                orient.style.setProperty("visibility", "hidden");        
            }
        }
        else
        {
            if(game_orientation == 'portrait')
            {
                orient.style.setProperty("visibility", "visible");   
                orient.style.display = "inline";
                return;
            }        
            else
            {
                orient.style.display = "none";
                orient.style.setProperty("visibility", "hidden");        
            }
        }
    }, 300);    
}

function SafariSwipe()
{
    var t = window.document.getElementById("bar"),
        e = window.innerHeight;
    if(t)
    {
        if (t.clientHeight === e )
        {
            hideSwipe();
        }else if (window.document.getElementById("swipe").style.visibility != "visible")
        {
            document.getElementsByTagName("body")[0].style.overflow = "auto";
            showSwipe();
        }  
    }    
}
function GetExitSwipeMsg(a) {
    return "en" == a ? "If swiping does not work, please tap here. \u274e" : "es" == a ? "Si no se puede arrastrar para arriba, por favor toque aqu\u00ed. \u274e" : ""
};

function onClickCloseMask()
{
    hideSwipe();
}

function SetIsScrolling (t) {
    isScrolling = t
}

function showCloseMask()
{
    window.document.getElementById("mask_close").style.display = "block"
}

function checkScroll()
{
    if(isScrolling)
    {
        isScrolling = 0;
        scrollToReset();
    }        
}

function scrollToReset()
{
    window.scrollTo(0, 0);
}

function showSwipe()
{
    var maskClose = window.document.getElementById("mask_close");
    if (maskClose) {
        var closeTxt = GetExitSwipeMsg('en');
        maskClose.textContent = closeTxt, maskClose.onclick || (maskClose.onclick = onClickCloseMask), setTimeout(showCloseMask, 3)
    }
    var mask = window.document.getElementById("mask");
    mask && (mask.style.display = "inline", mask.style.visibility = "visible");
    mask.style.zIndex = 1000;
    mask.style.opacity = 100;
    var i = window.document.getElementById("swipe");
    i.style.visibility = 'visible';
    window.scrollTo(0, 0);
    if(isiOS13Up)
    {
        if(checkScrollInterval)
        {
            clearInterval(checkScrollInterval);
            checkScrollInterval = null;                
        }
        checkScrollInterval = setInterval(checkScroll, 500);
    }
    // i.removeEventListener('touchmove',checkScroll,false);
    // i.addEventListener('touchmove',checkScroll, {passive: false});
}

function hideSwipe ()
{
    var i = window.document.getElementById("swipe");
    i.removeEventListener('touchmove',checkScroll,false);
    var e = window.document.getElementById("mask");
    e && (e.style.display = "none");
    var n = window.document.getElementById("swipe");
    n && (n.style.visibility = "hidden")
    var i = window.document.getElementById("mask_close");
    i.style.display = "none", console.warn("[Mask] HIDE !!")        

    if (window.scrollX > 0 || window.scrollY > 0)
    {
        window.scrollTo(0,0);
        document.getElementsByTagName("body")[0].style.overflow = "hidden";
    }
}

function requestChromeFullScreen()
{
    var el = window.top.document.documentElement,
    rfs = el.requestFullscreen
      || el.webkitRequestFullScreen
      || el.mozRequestFullScreen
      || el.msRequestFullscreen;

    rfs.call(el);
}