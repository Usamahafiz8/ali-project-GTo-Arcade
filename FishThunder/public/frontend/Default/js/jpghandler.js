


function init()
{    
    setInterval(() => {
        var t = (new Date()).getTime();
        $.ajax({
            url: '/jpstv.json?page=game&r=' + t,
            type: "GET",
            data: {},
            dataType: 'json',
            success: function (response) {             
                if(response.status == "logout")
				{
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
					
					if(!isMobile)
						window.location.href = '/logout';
					else
					{
                        if(window.vuplex)
                        {
                            window.vuplex.postMessage('logout');
                        }
                        try
                        {
                            JSBridge.sendMessageToNative("logout");    
                        }catch(e){}
                        try
                        {
                            window.webkit.messageHandlers.swiftJSBridge.postMessage("logout");
                        }catch(e){}
                    }
					return;
				}
            },
            error: function () {
            }
            
        });
    }, 10000);
}

function showBonusPopup(name, win)
{
    var ele = $('.notification_bonus');
    $('.notification_bonus').css('display', 'block');    
    var width = ele.width;
    var height = width * 731 / 1154;
    $('.notification_bonus').height(height);

    $('.bonus_wintype').attr('src', '/frontend/Default/img/' + name + '.png');
    $('.bonus_balance').html(win.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }));    
}

init();