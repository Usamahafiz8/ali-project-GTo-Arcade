import './App.css';
import React from 'react';
import TopWindows from "./TopWindows";
import * as PIXI from "pixi.js";
import { listImages } from './common/Config';
import EventEmitter from "eventemitter3";
import { setup } from './Game';

/**
 * global Event Emitter
 */
export let EE:EventEmitter = new EventEmitter();
///
export let imagesLoader:PIXI.Loader;
export function showPopup(txt:string) {
	/*"Congratulations, you have completed more than 8 gems! Once time is over you'll receive a reward."*/
	EE.emit('SHOW_MESSAGE', txt);
}
export function showCashbackPopup(amount:number) {
	EE.emit('SHOW_CASHBACK', amount);
}


class App extends React.Component {
	isSetup: any;
	componentDidMount() {
		const WebFont = require('webfontloader');
		WebFont.load({
			google: {
				families: ['Patua One']
			}
		});
		const preloaderbase = document.getElementsByClassName("preloader-game");
		//
		imagesLoader = PIXI.Loader.shared;
		imagesLoader.add(listImages);
		imagesLoader.onProgress.add(() => {
			/*if(preloader[0]) {
				(preloader[0] as any).style.setProperty("filter", `grayscale(${100 - loader.progress}%)`);
			}*/
		});
		imagesLoader.onError.add((e) => {
			console.log('ERROR LOAD! ', e);
		});
		imagesLoader.onComplete.add(() => {
			//PIXI.utils.clearTextureCache();
			if(preloaderbase[0]) {
				(preloaderbase[0] as any).style.setProperty("opacity", "0");
				setTimeout(()=>{
					(preloaderbase[0] as any).style.setProperty("display", "none");
					(preloaderbase[0] as any).parentNode.removeChild((preloaderbase[0] as any));

				}, 1000);
			}
			EE.emit('CLEAR_TOP_WINDOWS');
			//setup(); //else activate it for open hub
			//test popups::::
			//EE.emit('SHOW_USER'); //user info popup
			//EE.emit('SHOW_LUCKY'); //congratulations popup
			//EE.emit('SHOW_SETTINGS'); //user settings popup
			//EE.emit('SHOW_MAIL'); //messages popup
			//EE.emit('SHOW_EDITINFO'); //user edit info popup
			//EE.emit('SHOW_GET'); //get 500 popup
			//EE.emit('SHOW_REG'); //user registration popup
			//EE.emit('SHOW_INFO'); //user info2 popup
			//EE.emit('SHOW_BONUS'); //bonus cards popup
			//EE.emit('SHOW_NPASS'); //new password popup
			var type:string = document.getElementById('root')?.getAttribute('type')!;
			if(type == 'hub')
			{
				if(!this.isSetup)
				{
					setup();
					this.isSetup = true;
				}
			}
			else
			{
				EE.emit('SHOW_LOGIN');
				document.getElementById('AppGame')?.style.setProperty("display", "none");
			}
		});
		imagesLoader.load();
		//
		EE.once('GO_GAME', ()=>{
			EE.emit('CLEAR_TOP_WINDOWS');
			setup();
		})
	}

	render () {
		return (
			<div>
				<TopWindows/>
				<div id="AppGame"/>
			</div>
		)
	}
}

export default App;
