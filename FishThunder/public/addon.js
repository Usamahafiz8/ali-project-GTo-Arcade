function AlignVertical() {
	setInterval(function () {

		try {
			var canv = document.getElementsByTagName('canvas')[0];
			var hc = canv.style['height'].split('px')[0];
			var hw = window.innerHeight;
			var wc = canv.style['width'].split('px')[0];
			var ww = window.innerWidth;
			canv.style['position'] = 'fixed';
			canv.style['top'] = ((hw - hc) / 2) + 'px';
			canv.style['left'] = ((ww - wc) / 2) + 'px';

		} catch (e) { }

	}, 1000);
}

(function () {

	var exitBtn = document.createElement('div');
	exitBtn.style.content = "url('/frontend/Default/img/btn_home.png')";
	exitBtn.style['right'] = '5px';
	exitBtn.style['top'] = '5px';
	exitBtn.style['width'] = '50px';
	exitBtn.style['height'] = '50px';
	exitBtn.style['position'] = 'fixed';
	exitBtn.style['z-index'] = '1000';
	exitBtn.style['text-align'] = 'center';
	exitBtn.style['padding-top'] = '0px';
	exitBtn.style['cursor'] = 'pointer';

	exitBtn.addEventListener('click', function () {
		var data = JSON.stringify({
			event: 'backToHub'
		});
		window.parent.parent.postMessage(data, "*");
	});

	setTimeout(function () {
		document.body.appendChild(exitBtn);
		// if (device.desktop() || device.tablet()) 
		{
			AlignVertical();
		}
	}, 2000);
}
)();