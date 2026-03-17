import * as PIXI from "pixi.js";

export class FlashObject extends PIXI.Sprite{
	cont:PIXI.Sprite;
	constructor() {
		super();
		//
		this.cont = this.addChild(new PIXI.Sprite());
		//
		const json0 = PIXI.Loader.shared.resources["/images/fish/anim/flash.json"].spritesheet;
		const array0:any = [];
		if(json0) {
			Object.keys(json0.textures).sort().forEach((key) => {
				array0.push(json0.textures[key]);
			});
		}

		const animate = new PIXI.AnimatedSprite(array0);
		animate.animationSpeed = 0.5;
		animate.loop = true;
		animate.scale.set(0.9);
		this.cont.addChild(animate);
		animate.gotoAndPlay(1);
	}

}