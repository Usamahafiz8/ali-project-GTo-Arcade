import * as PIXI from "pixi.js";
import { createGameFrame, SOUND } from "../../Game";
// import { GameItemType } from "../../common/types";

const mask:{x:number,y:number }[] = [{x:74, y:93},		{x:89, y:67},		{x:114, y:51},	{x:362, y:51},		{x:387, y:67},		{x:397, y:92},		{x:397, y:442},		{x:387, y:465},		{x:361, y:483},		{x:115, y:488},		{x:90, y:471},		{x:74, y:446}	];

export class Icon2 extends PIXI.Sprite{
	cont:PIXI.Sprite;
	piccont:PIXI.Sprite;
	id:number = 0;
	url:string;
	SRC:string = "";
	/**
	 * Big game icon
	 * @param data	game data object
	 */
	constructor(data:any) {
		super();

		this.id = data.id!;
		this.SRC = data.src;
		this.url = data.url;
		//
		this.cont = this.addChild(new PIXI.Sprite());
		const fon = this.cont.addChild(PIXI.Sprite.from(this.SRC));
		fon.x = 70;
		fon.y = 45;
		fon.scale.set(0.95);
		//
		this.piccont = this.cont.addChild(new PIXI.Sprite());
		const back = this.cont.addChildAt(new PIXI.Graphics(), 0).beginFill(0x006600, 1).moveTo(mask[0].x,mask[0].y)
		for(let i=1;i<mask.length;i++) {
			back.lineTo(mask[i].x,mask[i].y);
		}
		back.lineTo(mask[0].x,mask[0].y);
		back.endFill();
		//
		this.piccont.mask =	back;		
		//
		const json0 = PIXI.Loader.shared.resources["/images/fish/anim/icon3.json"].spritesheet;
		const array0: any = [];
		if (json0) {
			Object.keys(json0.textures).sort().forEach((key) => {
				array0.push(json0.textures[key]);
			});
		}

		const animate = new PIXI.AnimatedSprite(array0);
		animate.animationSpeed = 0.3;
		animate.loop = true;
		animate.scale.set(0.95);
		this.cont.addChild(animate);
		animate.gotoAndPlay(1);
		//
		//
		let stX = 0;
		const mc = this;
		function tapDown(e:any) {
			stX = mc.position.x - e.data.originalEvent.pageX;
			if(e.data.originalEvent.pageX == undefined)
			{
				stX = mc.position.x - e.data.originalEvent.changedTouches[0].globalX;
			}
		}
		
		var instance = this;
		function tapUp(e:any) {
			var endX = mc.position.x - e.data.originalEvent.pageX;
			if(e.data.originalEvent.pageX == undefined)
			{
				endX = mc.position.x  - e.data.originalEvent.changedTouches[0].globalX;
			}
			if(endX > (stX - 10) && endX < (stX + 10)) {
				console.log(data.id);
				var tempMute = SOUND.MUTE;
				SOUND.muteSounds();
				SOUND.MUTE = tempMute;	
				createGameFrame(instance.url);
			}
		}
		this.on('touchstart', tapDown);
		this.on('pointerdown', tapDown);
		this.on('pointerup', tapUp);
		this.on('touchend', tapUp);
		//
		this.interactive = true;
		this.buttonMode = true;
	}

}