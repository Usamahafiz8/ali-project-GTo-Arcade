import * as PIXI from "pixi.js";
import {ButtonItem} from "../../gui/ButtonItem";
import {EE} from "../../../App";
import {BROWSER_SCALE, BROWSER_WIDTH} from "../../../responsiveModule";
import {gsap} from "gsap";
import {SOUND} from "../../../Game";


export class HubRight extends PIXI.Sprite{
	cont:PIXI.Sprite;

	constructor() {
		super();
		//
		this.cont = this.addChild(new PIXI.Sprite());
		this.cont.addChild(new PIXI.Sprite(PIXI.Texture.from("/images/fish/right_back.png")));
		this.x = (BROWSER_WIDTH/BROWSER_SCALE);
		this.cont.x = -210;
		//this.cont.x = -155;
		this.cont.scale.set(1.35);
		const buttonSnd = this.cont.addChild(new ButtonItem("/images/fish/right_menu2.png", ()=>{
			buttonSnd.visible = false;
			buttonSndOff.visible = true;
			SOUND.muteSounds();
		}));
		buttonSnd.x = 54;
		buttonSnd.y = 184;
		const buttonSndOff = this.cont.addChild(new ButtonItem("/images/fish/right_menu3.png", ()=>{
			buttonSnd.visible = true;
			buttonSndOff.visible = false;
			SOUND.unmuteSounds();
		}));
		buttonSndOff.visible = false;
		buttonSndOff.x = 54;
		buttonSndOff.y = 184;

		const buttonLetter = this.cont.addChild(new ButtonItem("/images/fish/right_menu1.png", ()=>{
			EE.emit('SHOW_MAIL');
		}));
		buttonLetter.x = 54;
		buttonLetter.y = 53;

		const buttonExit = this.cont.addChild(new ButtonItem("/images/fish/right_menu4.png", ()=>{
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
				var data = JSON.stringify({event: 'Logout', value: "logout"});
				window.postMessage(data, "*");
				window.location.href = '/logout';				
			}
		}));
		buttonExit.x = 54;
		buttonExit.y = 308;
		//
		const op = this.cont.addChild(new ButtonItem("/images/fish/right_open.png", ()=>{
			cl.visible = true;
			op.visible = false;
			gsap.killTweensOf(this.cont);
			gsap.to(this.cont, 0.3, {x: -210});
		}));
		op.x = 10;
		op.y = 193;
		op.visible = false;
		//
		const cl = this.cont.addChild(new ButtonItem("/images/fish/right_close.png", ()=>{
			cl.visible = false;
			op.visible = true;
			gsap.killTweensOf(this.cont);
			gsap.to(this.cont, 0.3, {x: -60});
		}));
		cl.x = 13;
		cl.y = 191;

		this.onResize = this.onResize.bind(this);


		EE.addListener("RESIZE", this.onResize);
		EE.emit("FORCE_RESIZE");
	}

	onResize(data:any) {
		this.x = (BROWSER_WIDTH/BROWSER_SCALE);
		this.cont.y = (data.h/data.scale)/2 - 340;
	}

}