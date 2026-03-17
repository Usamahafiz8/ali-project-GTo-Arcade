import * as PIXI from "pixi.js";
import {JackpotUnit} from "./JackpotUnit";
import {EE} from "../../../App";
import { PAGE_SIZE_DEFAULT } from "../../../common/Config";
import {FlashObject} from "./FlashObject";


export class HubTop extends PIXI.Sprite{
	cont:PIXI.Sprite;
	unitcont:PIXI.Sprite;
	back:PIXI.Sprite = new PIXI.Sprite();
	grand: JackpotUnit;
	major: JackpotUnit;
	minor: JackpotUnit;
	mini: JackpotUnit;
	constructor() {
		super();
		//
		this.cont = this.addChild(new PIXI.Sprite());
		this.back = this.cont.addChild(new TopBack());

		this.unitcont = this.cont.addChild(new PIXI.Sprite());

		this.grand = this.unitcont.addChild(new JackpotUnit(1));
		this.grand.x = 20;
		this.grand.y = 20;
		this.major = this.unitcont.addChild(new JackpotUnit(2));
		this.major.x = 355;
		this.major.y = 20;
		this.minor = this.unitcont.addChild(new JackpotUnit(3));
		this.minor.x = 1247;
		this.minor.y = 20;
		this.mini = this.unitcont.addChild(new JackpotUnit(4));
		this.mini.x = 1581;
		this.mini.y = 20;

		this.onResize = this.onResize.bind(this);
		

		EE.addListener("RESIZE", this.onResize);
		EE.emit('FORCE_RESIZE');
	}

	onResize(data:any) {
		const spaceX = (data.w/data.scale) - PAGE_SIZE_DEFAULT.width;
		//this.back.width = (data.w/data.scale);
		//this.frame_ex1.width = this.frame_ex2.width = spaceX/2;
		//this.frame_ex2.x = (data.w/data.scale) - spaceX/2;
		this.unitcont.x = spaceX/2;
	}

}

class TopBack extends PIXI.Sprite{
	contL:PIXI.Sprite = new PIXI.Sprite();
	contR:PIXI.Sprite = new PIXI.Sprite();
	contC:PIXI.Sprite = new PIXI.Sprite();
	flashL:PIXI.Sprite = new PIXI.Sprite();
	flashR:PIXI.Sprite = new PIXI.Sprite();
	constructor() {
		super();
		this.contC = this.addChild(new PIXI.Sprite(PIXI.Texture.from("/images/fish/top_c.png")));
		this.contL = this.addChild(new PIXI.Sprite(PIXI.Texture.from("/images/fish/top_l.png")));
		this.contR = this.addChild(new PIXI.Sprite(PIXI.Texture.from("/images/fish/top_r.png")));


		const logo = this.contC.addChild(new Logo());
		logo.x = 100;
		logo.y = -110;


		this.flashR = this.contR.addChild(new FlashObject());
		this.flashR.scale.x = 1.01;
		this.flashR.x = -50;
		this.flashR.y = 70;
		//
		this.flashL = this.contL.addChild(new FlashObject());
		this.flashL.anchor.x = 0.5;
		this.flashL.scale.x = 1.05;
		this.flashL.x = -10;
		this.flashL.y = 70;

		this.onResize = this.onResize.bind(this);
		//
		EE.addListener("RESIZE", this.onResize);
		EE.emit('FORCE_RESIZE');
	}

	onResize(_data:any) {
		this.contC.x = (_data.w/_data.scale)/2 - 292;
		this.contL.x = 0;
		this.contR.x = (_data.w/_data.scale)/2 + 292;
		this.contL.width = this.contR.width = ((_data.w/_data.scale)-584)/2;
		/*const end_r = this.contR.addChild(new PIXI.Sprite(PIXI.Texture.from("/images/screens/elements/bg_up_multi_end.png")));
		end_r.x = len - 180;
		const end_l = this.contL.addChild(new PIXI.Sprite(PIXI.Texture.from("/images/screens/elements/bg_up_multi_end.png")));
		end_l.x = len - 180;*/

	}
}
export class Frame extends PIXI.Sprite{
	cont:PIXI.Sprite;
	animate:PIXI.AnimatedSprite;
	constructor() {
		super();
		//
		this.cont = this.addChild(new PIXI.Sprite());
		//
		this.play = this.play.bind(this);
		//
		const json0 = PIXI.Loader.shared.resources["/images/anim/frame_up.json"].spritesheet;
		const array0:any = [];
		if(json0) {
			Object.keys(json0.textures).sort().forEach((key) => {
				array0.push(json0.textures[key]);
			});
		}

		this.animate = new PIXI.AnimatedSprite(array0);
		this.animate.animationSpeed = 0.5;
		this.animate.loop = true;
		this.cont.addChild(this.animate);
		this.animate.gotoAndPlay(1);
	}

	play() {
		this.animate.gotoAndPlay(1);
	}

}

export class Logo extends PIXI.Sprite{
	cont:PIXI.Sprite;
	constructor() {
		super();
		//
		this.cont = this.addChild(new PIXI.Sprite());
		//
		const json0 = PIXI.Loader.shared.resources["/images/fish/anim/logo.json"].spritesheet;
		const array0:any = [];
		if(json0) {
			Object.keys(json0.textures).sort().forEach((key) => {
				array0.push(json0.textures[key]);
			});
		}

		const animate = new PIXI.AnimatedSprite(array0);
		animate.animationSpeed = 0.2;
		animate.loop = true;
		this.cont.addChild(animate);
		animate.gotoAndPlay(1);
	}

}