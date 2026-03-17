import initRenderer from "./initRenderer";
import * as PIXI from "pixi.js";
import {gsap} from "gsap";
import PixiPlugin from "gsap/src/PixiPlugin";
import { Hub } from "./assets/screens/Hub";
import {HubIcons2} from "./assets/screens/HubIcons2";
import {EE} from "./App";
import {HubRight} from "./assets/screens/elements/HubRight";
import Sound from "./sounds/Sound";
import { BonusWheel } from "./assets/bonus/BonusWheel";

/**
 * main stage
 */
export let stage:PIXI.Container;
//head application container
let maincontainer:PIXI.Container = new PIXI.Container();
//container for some top windows (bonus)
let topcontainer:PIXI.Container = new PIXI.Container();
/**
 * main renderer
 */
export let RENDERER:PIXI.Renderer;
export let SOUND:Sound;
/**
 * selected section of games
 */
export let SELECTED_PART:number=1;

export let pageStatus: Number;
export function updatePageStatus(status: Number)
{
	pageStatus = status;
}

/**
 * event name to update the state of large filter buttons for games
 */
export let UPDATE_BIG_BUTTONS:string="UPDATE_BIG_BUTTONS";
/**
 * click on the games section button
 * @param idd	1-fish, 2-clot, 3-firelink
 */
export function updateSelectButton(idd:number) {
	SELECTED_PART = idd;
	EE.emit(UPDATE_BIG_BUTTONS);
}

/**
 * entry point function
 */
export async function setup() {
	//load config hson
	//create renderer
	RENDERER = initRenderer();
	//create stage
	stage = new PIXI.Container();
	stage.addChild(maincontainer);
	stage.addChild(topcontainer);
	//add pixi support
	gsap.registerPlugin(PixiPlugin);
	PixiPlugin.registerPIXI(PIXI);
	//get money and add game
	createHub();
	EE.addListener('SHOW_WHEEL', showBonusWheel);
	//
	let ticker = PIXI.Ticker.shared;
	ticker.autoStart = false;
	ticker.stop();
	function animate(time:any) {
		ticker.update(time);
		RENDERER.render(stage);
		requestAnimationFrame(animate);
		//EE.emit('UPDATE');
	}
	animate(performance.now());
}

function createHub() {
	SOUND = new Sound();
	maincontainer.addChild(new Hub());
	const games = maincontainer.addChild(new HubIcons2());
	games.y = 150;
	maincontainer.addChild(new HubRight());
}

function showBonusWheel() {
	maincontainer.visible = false;
	topcontainer.removeChildren();
	topcontainer.addChild(new BonusWheel(hideBonus));
}

function hideBonus() {
	maincontainer.visible = true;
	topcontainer.removeChildren();
}

export function createGameFrame(url: string)
{
	window.location.href = url;
	// var backDiv = document.createElement('div');
	// document.body.appendChild(backDiv);
	// backDiv.style['width'] = '100%';
	// backDiv.style['height'] = '100%';
	// backDiv.style['position'] = 'absolute';
	// backDiv.style['background'] = '#000';
	// backDiv.style['top'] = '0';
	// backDiv.style['left'] = '0';
	// backDiv.setAttribute('id', 'game-background');

	// var iframe = document.createElement('iframe');
	// document.body.appendChild(iframe);		
	// iframe.src = url;

	// // var width = window.innerWidth;
	// // var height = window.innerHeight;
	// var canv_width, canv_height, canv_top, canv_left;

	// // if(width / height < 1.4) //for z fold 
	// // {
	// // 	canv_width = width + 'px';
	// // 	canv_height = (width * 9 / 16) + 'px';
	// // 	canv_top = ((height - width * 9 / 16) / 2) + 'px';
	// // 	canv_left = '0px';			
	// // }
	// // else
	// {
	// 	canv_width = '100%';
	// 	canv_height = '100%';
	// 	canv_top = '0px';
	// 	canv_left = '0px';
	// }

	// iframe.style['width'] = canv_width;
	// iframe.style['height'] = canv_height;
	// iframe.style['position'] = 'absolute';
	// iframe.style['top'] = canv_top;
	// iframe.style['left'] = canv_left;
	// iframe.style['border'] = 'none';
	// iframe.setAttribute('id', 'game-frame');
	// updatePageStatus(1);
}