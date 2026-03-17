$(function(){
	$('.menuBtn').on('click',function(){
		if($('html').is('.navOpen')){
			$('html').removeClass('navOpen');
			$('.mWrap:visible').liMarquee('start',100);
		}else{
			$('.mWrap').liMarquee('stop');
			setTimeout(function(){
				$('html').addClass('navOpen');
			},100);
		}
		return false;
	});
	
	$('a',$('.navWrap')).on('click',function(){
		$('html').removeClass('navOpen');
		$('.mWrap').liMarquee('start',100);
	});
	
	
	var pathMas = window.location.pathname.split('/');
	var href = pathMas[pathMas.length-1]
	if(href === ""){
		href = 'index.html';
	}
	
	var curLink = $('a[href="'+href+'"]');

	curLink.addClass('cur');

	if('ontouchstart' in window){
		$('html').addClass('touch');
	}else{
		$('html').addClass('no-touch');
	}
});