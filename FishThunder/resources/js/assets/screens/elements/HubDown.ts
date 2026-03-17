import * as PIXI from "pixi.js";
import { EE } from "../../../App";
import { FlashObject } from "./FlashObject";
import {SliderControl} from "./SliderControl";
import {Buttons} from "./Buttons";
import { convertTimeToStr, isSafari } from "../../../common/Utils";
import { BonusWheelData } from "../../../server/server";

export class HubDown extends PIXI.Sprite{
	cont:PIXI.Sprite = new PIXI.Sprite();
	user:UserBlock;
	boosters:PIXI.Sprite = new PIXI.Sprite();
	// rewards:PIXI.Sprite = new PIXI.Sprite();
	wheel:PIXI.Sprite = new PIXI.Sprite();
	back_l:PIXI.Sprite = new PIXI.Sprite();
	back_ll:PIXI.Sprite = new PIXI.Sprite();
	back_r:PIXI.Sprite = new PIXI.Sprite();
	back_rr:PIXI.Sprite = new PIXI.Sprite();
	dback:PIXI.Sprite = new PIXI.Sprite();
	flashL:PIXI.Sprite = new PIXI.Sprite();
	flashR:PIXI.Sprite = new PIXI.Sprite();
	buttons:PIXI.Sprite = new PIXI.Sprite();

	constructor() {
		super();
		//
		this.cont = this.addChild(new PIXI.Sprite());
		this.dback = this.cont.addChild(new PIXI.Sprite(PIXI.Texture.from("/images/fish/down_c.png")));
		this.back_l = this.cont.addChild(new PIXI.Sprite(PIXI.Texture.from("/images/fish/down_l.png")));
		this.back_ll = this.cont.addChild(new PIXI.Sprite(PIXI.Texture.from("/images/fish/down_ll.png")));
		this.back_r = this.cont.addChild(new PIXI.Sprite(PIXI.Texture.from("/images/fish/down_r.png")));
		this.back_rr = this.cont.addChild(new PIXI.Sprite(PIXI.Texture.from("/images/fish/down_rr.png")));


		this.dback.addChild(new SliderControl());
		//
		this.flashR = this.back_rr.addChild(new FlashObject());
		this.flashR.x = -30;
		this.flashR.y = -26;
		this.flashR.scale.x=0.89;

		this.flashL = this.back_ll.addChild(new FlashObject());
		//this.flashL.anchor.x = 0.5;
		this.flashL.x = -40;
		this.flashL.y = -26;
		this.flashL.scale.x=0.9;
		//this.flashL.x = -640;
		//
		this.user = this.cont.addChild(new UserBlock());
		this.user.x = 205;

		this.wheel = this.cont.addChild(new WheelButton());
		this.wheel.x = 480;

		this.buttons = this.cont.addChild(new Buttons());
		this.buttons.x = 0;

		this.onResize = this.onResize.bind(this);
		EE.addListener("RESIZE", this.onResize);
		EE.emit('FORCE_RESIZE');
	}

	onResize(data:any) {
		this.user.y = (data.h/data.scale) - 150;
		this.wheel.y = (data.h/data.scale) - 169;
		this.dback.x = (data.w/data.scale)/2 - 331;
		this.dback.y = (data.h/data.scale) - 125;
		this.back_l.y = (data.h/data.scale) - 125;
		this.back_r.y = (data.h/data.scale) - 125;
		this.back_l.x = 0;
		this.back_r.x = (data.w/data.scale) - 104;
		this.back_ll.width = this.back_rr.width = ((data.w/data.scale) - 208 - 662)/2;
		this.back_ll.y = this.back_rr.y = (data.h/data.scale) - 79;
		this.back_ll.x = 104;
		this.back_rr.x = this.back_r.x - this.back_rr.width;
		//this.flashL.width = this.flashR.width = this.back_ll.width;
		this.buttons.x = this.back_rr.x - 10;
		this.buttons.y = this.back_rr.y - 90;
		//
		let ds = data.scale;
		if(ds<1) ds=1;
		this.wheel.x = (data.w/data.scale)/2 - 650;
		this.user.x = (data.w/data.scale)/2 - 870;
	}

}

class UserBlock extends PIXI.Sprite{
	cont:PIXI.Sprite;
	animate:PIXI.AnimatedSprite;
	moneyuser: PIXI.Text;
	constructor() {
		super();
		const styletext = new PIXI.TextStyle({
			fontFamily: "Patua One",
			fontSize: "22px",
			fill: [
				"#ffffff",
				"#FCD13D",
				],
			dropShadow: true,
			dropShadowBlur: 1,
			dropShadowColor: "#A76520",
			dropShadowDistance: 3,
			align: "center",

		});

		const styletext2 = new PIXI.TextStyle({
			fontFamily: "Roboto",
			fontSize: "28px",
			fill: [
				"#ffffff",
				"#37D8FC",
			],
			dropShadow: true,
			dropShadowBlur: 2,
			dropShadowColor: "#000000",
			dropShadowDistance: 2,
			align: "center",
			fontWeight: "600"
		});
		//
		this.cont = this.addChild(new PIXI.Sprite());
		//
		const json0 = PIXI.Loader.shared.resources["/images/fish/anim/glow3.json"].spritesheet;
		const array0:any = [];
		if(json0) {
			Object.keys(json0.textures).sort().forEach((key) => {
				array0.push(json0.textures[key]);
			});
		}

		this.animate = new PIXI.AnimatedSprite(array0);
		this.animate.animationSpeed = 0.3;
		this.animate.loop = true;
		this.animate.y = -52;
		this.animate.x = -14;
		this.animate.scale.set(0.9);
		this.cont.addChild(this.animate);
		this.animate.gotoAndPlay(1);
		//
		this.cont.addChild(new PIXI.Sprite(PIXI.Texture.from("/images/fish/user.png")));
		//
		var username:string = document.getElementById('root')?.getAttribute('username')!;
		const nameuser = this.cont.addChild(new PIXI.Text(username, styletext));
		nameuser.x = 90 - (nameuser.width/2);
		nameuser.y = 77;

		var balance:string = document.getElementById('root')?.getAttribute('balance')!;
		this.moneyuser = this.cont.addChild(new PIXI.Text(balance, styletext2));
		this.moneyuser.x = 95 - (this.moneyuser.width/2);
		this.moneyuser.y = 102;
		//
		this.cont.interactive = true;
		this.cont.buttonMode = true;
		this.cont.on('pointerdown', ()=>{
			EE.emit('SHOW_INFO');
		})
	}

}
class WheelButton extends PIXI.Sprite{
	cont:PIXI.Sprite;
	tim:any = 0;
	constructor() {
		super();
		//
		const styletext = new PIXI.TextStyle({
			fontFamily: "Roboto",
			fontSize: "26px",
			fontWeight: "bold",
			fill: [
				"#ECFCFF",
				"#2ED6FC",
			],
			dropShadow: true,
			dropShadowBlur: 1,
			dropShadowColor: "#000000",
			dropShadowDistance: 2,
			align: "center",
		});
		//
		this.cont = this.addChild(new PIXI.Sprite());
		//
		const json0 = PIXI.Loader.shared.resources["/images/fish/anim/wheel.json"].spritesheet;
		const array0:any = [];
		if(json0) {
			Object.keys(json0.textures).sort().forEach((key) => {
				array0.push(json0.textures[key]);
			});
		}

		const animate = new PIXI.AnimatedSprite(array0);
		animate.animationSpeed = 0.3;
		animate.loop = true;
		this.cont.addChild(animate);
		animate.gotoAndPlay(1);
		//
		const time = this.cont.addChild(new PIXI.Text("00:00:00", styletext));
		time.x = 140 - (time.width/2);
		time.y = 137;
		//
		
		this.tim = setInterval(()=>{
			var next_bonus_time = BonusWheelData.bonus_time;
			if(next_bonus_time != '')
			{
				var bonus_time = Date.parse(next_bonus_time);
				if(isSafari)
				{
					bonus_time = new Date(next_bonus_time.replace(/-/g, '/')).getTime();
				}
				var now = new Date(new Date().toLocaleString('en', {timeZone: 'America/Bogota'}));
				var sec = (bonus_time - now.getTime()) / 1000;
				if(sec < 0)
					sec = 0;
				const timtext = convertTimeToStr(sec);
				time.text =`${timtext[0]}:${timtext[1]}:${timtext[2]}`;
				time.x = 140 - (time.width/2);
			}
		}, 1000);
		//
		this.cont.interactive = true;
		this.cont.buttonMode = true;
		this.cont.on('pointerdown', ()=>{
			EE.emit('SHOW_WHEEL');
		})
	}

	componentWillUnmount() {
		clearInterval(this.tim);
	}

}