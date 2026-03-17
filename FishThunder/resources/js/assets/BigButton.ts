import * as PIXI from "pixi.js";
import {SELECTED_PART} from "../Game";

/**
 * game list filter button
 */
export class BigButton extends PIXI.Sprite{
	cont:PIXI.Sprite;
	yellow_back:PIXI.AnimatedSprite;
	yellow_ball:PIXI.Sprite;
	blue_ball:PIXI.Sprite;
	text_blue:PIXI.Sprite;
	text_yellow:PIXI.Sprite;
	pic:PIXI.Sprite;
	//timeline:PIXI.AnimatedSprite;
	/**
	 * id type button. 1-fish, 2-clot, 3-firelink
	 */
	ID:number;

	/**
	 * game list filter button
	 * @param idd	1-fish, 2-clot, 3-firelink
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

		this.pic.addChild(new PIXI.Sprite(PIXI.Texture.from("/images/buttons/back_blue.png")));

		//this.yellow_back = this.pic.addChild(new PIXI.Sprite(PIXI.Texture.from("/images/buttons/back_yellow.png")));
		//this.yellow_back.visible = false;

		const json0 = PIXI.Loader.shared.resources['images/buttons/anim1.json'].spritesheet;
		const array0:any = [];
		if(json0) {
			Object.keys(json0.textures).sort().forEach((key) => {
				array0.push(json0.textures[key]);
			});
		}

		this.yellow_back = new PIXI.AnimatedSprite(array0);
		this.yellow_back.animationSpeed = 0.5;
		this.yellow_back.loop = true;
		this.pic.addChild(this.yellow_back);
		this.yellow_back.visible = false;


		this.yellow_ball = this.cont.addChild(new PIXI.Sprite(PIXI.Texture.from("/images/buttons/round_yellow.png")));
		this.yellow_ball.visible = false;
		this.yellow_ball.x = 230;
		this.yellow_ball.y = 8;

		this.blue_ball = this.cont.addChild(new PIXI.Sprite(PIXI.Texture.from("/images/buttons/round_blue.png")));
		this.blue_ball.visible = false;
		this.blue_ball.x = 230;
		this.blue_ball.y = 8;

		this.text_blue = this.cont.addChild(new PIXI.Sprite(PIXI.Texture.from(`/images/buttons/b${idd}text_b.png`)));
		this.text_blue.visible = false;
		this.text_blue.x = 33;
		this.text_blue.y = 35;

		this.text_yellow = this.cont.addChild(new PIXI.Sprite(PIXI.Texture.from(`/images/buttons/b${idd}text_y.png`)));
		this.text_yellow.visible = false;
		this.text_yellow.x = 33;
		this.text_yellow.y = 35;

		const logo = this.cont.addChild(new PIXI.Sprite(PIXI.Texture.from(`/images/buttons/b${idd}logo.png`)));
		logo.x = 230;
		logo.y = 8;

		this.on('pointerdown', this.tapDown);
		this.on('pointerup', this.tapUp);
		this.on('pointerupoutside', this.tapUp);
		this.on('pointercancel', this.tapUp);

		this.on('click', this.clickThis);
		this.on('tap', this.clickThis);
		//
		//
		this.interactive = true;
		this.buttonMode = true;

	}

	/**
	 * mouse down
	 */
	tapDown() {
		if(this.ID===SELECTED_PART) return;
		this.pic.y = 8;
		this.pic.scale.set(0.9);
	}

	/**
	 * mouse up
	 */
	tapUp() {
		if(this.ID===SELECTED_PART) return;
		this.pic.y = 0;
		this.pic.scale.set(1);
	}

	/**
	 * click button
	 */
	clickThis() {
		if(this.ID===SELECTED_PART) return;
		this.tapUp();
		this.updateState();
	}

	/**
	 * updates the state of the button (animated/not animated)
	 */
	updateState() {
		if(this.ID!==SELECTED_PART) {
			this.blue_ball.visible = true;
			this.yellow_ball.visible = false;
			this.yellow_back.visible = false;
			this.yellow_back.stop();
			this.text_blue.visible = true;
			this.text_yellow.visible = false;
		} else {
			this.yellow_back.play();
			this.yellow_back.visible = true;
			this.blue_ball.visible = false;
			this.yellow_ball.visible = true;
			this.text_blue.visible = false;
			this.text_yellow.visible = true;
		}
	}



}