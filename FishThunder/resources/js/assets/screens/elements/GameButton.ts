import * as PIXI from "pixi.js";
import {SELECTED_PART, updateSelectButton, UPDATE_BIG_BUTTONS} from "../../../Game";
import {EE} from "../../../App";

/**
 * game list filter button
 */
export class GameButton extends PIXI.Sprite{
	cont:PIXI.Sprite;
	state1:PIXI.Sprite;
	state2:PIXI.Sprite;
	state1text:PIXI.Sprite;
	state2text:PIXI.Sprite;
	pic:PIXI.Sprite;
	backpic:PIXI.Sprite;

	/**
	 * id type button. 1-all 2-fish, 3-clot, 4-firelink
	 */
	ID:number;

	/**
	 * game list filter button
	 * @param idd	1-all 2-fish, 3-slot, 4-firelink
	 */
	constructor(idd:number) {
		super();
		//
		this.ID = idd;
		this.updateState = this.updateState.bind(this);
		this.tapDown = this.tapDown.bind(this);
		this.tapUp = this.tapUp.bind(this);
		this.clickThis = this.clickThis.bind(this);

		//button animation
		this.cont = this.addChild(new PIXI.Sprite());
		this.pic = this.cont.addChild(new PIXI.Sprite());

		this.state1 = this.pic.addChild(new PIXI.Sprite());

		let xx = 25;
		if(idd===2) xx = 15;
		if(idd===3) xx = 20;
		this.state1text = this.state1.addChild(new PIXI.Sprite(PIXI.Texture.from(`/images/fish/menu${idd-1}-1.png`)));
		this.state1text.x = xx;
		this.state1text.y = 110;
		this.state2 = this.pic.addChild(new PIXI.Sprite());

		this.state2text = this.state2.addChild(new PIXI.Sprite(PIXI.Texture.from(`/images/fish/menu${idd-1}-2.png`)));
		this.state2text.x = xx;
		this.state2text.y = 110;
		this.state2.visible = false;
		this.backpic = this.pic.addChild(new PIXI.Sprite(PIXI.Texture.from(`/images/fish/menu${idd-1}_back.png`)));
		this.backpic.x = 0;
		//
		const json01 = PIXI.Loader.shared.resources["/images/fish/anim/glow_ic.json"].spritesheet;
		const array01:any = [];
		if(json01) {
			Object.keys(json01.textures).sort().forEach((key) => {
				array01.push(json01.textures[key]);
			});
		}

		const animate5 = new PIXI.AnimatedSprite(array01);
		animate5.animationSpeed = 0.3;
		animate5.loop = true;
		animate5.y = 0;
		animate5.x = 0;
		this.pic.addChild(animate5);
		animate5.gotoAndPlay(1);
		//
		const json0 = PIXI.Loader.shared.resources["/images/fish/anim/glow2.json"].spritesheet;
		const array0:any = [];
		if(json0) {
			Object.keys(json0.textures).sort().forEach((key) => {
				array0.push(json0.textures[key]);
			});
		}

		const animate = new PIXI.AnimatedSprite(array0);
		animate.animationSpeed = 0.3;
		animate.loop = true;
		animate.y = -60;
		animate.x = -52;
		this.state2.addChild(animate);
		animate.gotoAndPlay(1);
		//
		const json1 = PIXI.Loader.shared.resources[`/images/fish/anim/part${idd-1}.json`].spritesheet;
		const array1:any = [];
		if(json1) {
			Object.keys(json1.textures).sort().forEach((key) => {
				array1.push(json1.textures[key]);
			});
		}

		const animate1 = new PIXI.AnimatedSprite(array1);
		animate1.animationSpeed = 0.3;
		animate1.loop = true;
		animate1.y = 0;
		animate1.x = 0;
		if(idd===1) {
			animate1.y = 0;
			animate1.x = 10;
		}
		if(idd===2) {
			animate1.y = 10;
		}
		if(idd===3) {
			animate1.y = -10;
		}
		if(idd===4) {
			animate1.y = 9;
			animate1.x = 18;
		}
		this.pic.addChild(animate1);
		animate1.gotoAndPlay(1);
		//
		this.on('pointerdown', this.tapDown);
		this.on('pointerup', this.tapUp);
		this.on('pointerupoutside', this.tapUp);
		this.on('pointercancel', this.tapUp);

		this.on('click', this.clickThis);
		this.on('tap', this.clickThis);
		//
		EE.addListener(UPDATE_BIG_BUTTONS, this.updateState);
		this.updateState();
		//
		this.interactive = true;
		this.buttonMode = true;

	}

	/**
	 * mouse down
	 */
	tapDown() {
		if(this.ID===SELECTED_PART) return;
		this.pic.x = 8;
		this.pic.y = 8;
		this.pic.scale.set(0.9);
	}

	/**
	 * mouse up
	 */
	tapUp() {
		if(this.ID===SELECTED_PART) return;
		this.pic.x = 0;
		this.pic.y = 0;
		this.pic.scale.set(1);
	}

	/**
	 * click button
	 */
	clickThis() {
		if(this.ID===SELECTED_PART) return;
		this.tapUp();
		updateSelectButton(this.ID);
		this.updateState();
	}

	/**
	 * updates the state of the button (select/unselect)
	 */
	updateState() {
		this.state2.visible = (this.ID===SELECTED_PART);
	}
}