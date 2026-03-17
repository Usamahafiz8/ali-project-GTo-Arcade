import * as PIXI from "pixi.js";
import {gsap} from "gsap";
import {ButtonItem} from "../gui/ButtonItem";
import { EE, showPopup } from "../../App";
import _ from "lodash";
import { BonusWheelData, spinWheel } from "../../server/server";

export class BonusWheel extends PIXI.Sprite{
	close: PIXI.Sprite = new PIXI.Sprite();
	black: PIXI.Graphics = new PIXI.Graphics();
	cont: PIXI.Sprite = new PIXI.Sprite();
	conttitle: PIXI.Sprite = new PIXI.Sprite();
	wheel: PIXI.Sprite = new PIXI.Sprite();
	buttonStart: PIXI.Sprite = new PIXI.Sprite();

	HIDE_BONUS:any = null;

	constructor(hideBonus:any) {
		super();
		//
		this.HIDE_BONUS = hideBonus;
		this.onResize = this.onResize.bind(this);
		this.removed = this.removed.bind(this);
		this.build = this.build.bind(this);
		this.build();
		this.blockSpin = !BonusWheelData.active;
	}

	async build() {
		//
		this.addChild(this.black);
		this.cont = this.addChild(new PIXI.Sprite());
		this.conttitle = this.addChild(new PIXI.Sprite());

		const back = this.cont.addChild(new PIXI.Sprite(PIXI.Texture.from("/images/fish/bonus/back1.png")));
		back.x = 270;
		back.y = -210;

		const textback = this.conttitle.addChild(new PIXI.Sprite(PIXI.Texture.from("/images/fish/bonus/title1.png")));
		textback.x = -545;

		this.wheel = this.cont.addChild(new BonusWheelItem());
		this.wheel.x = 367;
		this.wheel.y = 367;

		this.close = this.conttitle.addChild(new ButtonItem("/images/fish/popup/close_btn.png", ()=>{
			if(this.HIDE_BONUS) this.HIDE_BONUS();
		}));
		this.close.x = 600;
		this.close.y = 20;

		const ind = this.cont.addChild(new PIXI.Sprite(PIXI.Texture.from("/images/fish/bonus/circle_button.png")));
		ind.x = 270;
		ind.y = -265;
		const block_spin = this.cont.addChild(new PIXI.Sprite(PIXI.Texture.from("/images/fish/bonus/go_wheel_grey.png")));
		block_spin.x = 262;
		block_spin.y = 333;
		//
		this.buttonStart = this.cont.addChild(new ButtonItem("/images/fish/bonus/go_wheel.png", ()=>{
			this.blockSpin = true;
			spinWheel({}, (response: string) => {
				var result = JSON.parse(response);
				if(result.status == 'success')
				{
					(this.wheel as any).onSpin(result.stop_position, ()=>{
						setTimeout(() => {
							showPopup('You have won wheel bonus of ' + result.win_amount + " credits");
						}, 500);						
					});		
				}
				else
				{
					showPopup(result.message);
				}
			});
		}));
		this.buttonStart.x = 262;
		this.buttonStart.y = 333;

		EE.addListener("RESIZE", this.onResize);
		this.on('removed ', this.removed);
		//
		EE.emit('FORCE_RESIZE');
	}

	/**
	 * Block Spin button (true) or unblock (false)
	 * Usage: this.blockSpin = false;
	 * @param val
	 */
	set blockSpin(val:boolean) {
		this.buttonStart.visible = !val;
	}

	removed() {
		EE.removeListener("RESIZE", this.onResize);
		this.cont.removeChildren();
	}

	onResize(data:any) {
		this.black.clear();
		this.black.beginFill(0x000000, 0.7).drawRect(0,0,(data.w/data.scale),(data.h/data.scale)).endFill();
		this.conttitle.x = (data.w/data.scale)/2;
		this.conttitle.y = 50;
		this.cont.x = (data.w/data.scale)/2 - 385;
		this.cont.y = (data.h/data.scale) - 550;
	}

}

class BonusWheelItem extends PIXI.Sprite{
	cont:PIXI.Sprite;
	wheel:PIXI.Sprite;
	texts:PIXI.Sprite;
	data_wheel:any;

	constructor() {
		super();
		//TODO Important! this is data from server! available_prizes - thirty winning prizes. position_prize - the serial number of the winning position. 15 is $5
		const data_wheel={"available_prizes": BonusWheelData.available_prizes, "position_prize": 0};
		data_wheel.position_prize = Math.ceil(Math.random()*30) + 1; //TODO !!! Random prize. Remove this line if needed

		//
		this.data_wheel = data_wheel;

		const style = new PIXI.TextStyle({
			breakWords: true,
			fontFamily: "Roboto",
			fontSize: "50px",
			align: "center",
			wordWrap: true,
			wordWrapWidth: 0,
			fontWeight: "900",
			fill: "#ffffff",
			lineHeight: 50,
			miterLimit: 0,
			stroke: "#ffffff",
			strokeThickness: 2,
			whiteSpace: "normal",
		});
		//
		this.cont = this.addChild(new PIXI.Sprite());
		this.cont.anchor.set(0.5);

		this.wheel = this.cont.addChild(new PIXI.Sprite(PIXI.Texture.from("/images/fish/bonus/circle_blank.png")));
		this.wheel.x = -585;
		this.wheel.y = -585;

		this.texts = this.cont.addChild(new PIXI.Sprite());
		this.texts.x = 0;
		this.texts.y = 0;
		this.texts.angle = 4;

		const available_prizes = data_wheel.available_prizes;
		for(let i=0;i<available_prizes.length;i++) {
			const tx1 = this.texts.addChild(new PIXI.Text(`${available_prizes[i]}`, style));
			tx1.pivot.set(0, 520);
			tx1.angle = this.getAngle(i);
		}


		//this.onSelectWheelAnimate = this.onSelectWheelAnimate.bind(this);
		this.removed = this.removed.bind(this);
		this.onSpin = this.onSpin.bind(this);

		//EE.addListener("TICKER", this.onSelectWheelAnimate);
		this.on('removed ', this.removed);
	}

	/**
	 * Get angle
	 * @param num	1-30
	 */
	getAngle(num:number) {
		return (360/30 * num);
	}

	/**
	 * Get num on angle
	 * @param ang	0-359
	 */
	getNumOnAngle(ang:number) {
		return Math.ceil(ang/(360/30));
	}

	onSpin(position_prize: number, onComplete:any) {		
		const maxRound = 3;
		const startAngle = this.cont.angle = 0;//this.cont.angle;
		const curNum = this.getNumOnAngle(startAngle);
		let idleStopNum = position_prize + 1;

		let idleRotation = startAngle - (360 * maxRound);
		let idleTim = maxRound;
		idleTim += (idleStopNum - curNum) / 10;
		idleRotation -= (this.getAngle(idleStopNum) - startAngle);
		console.log(startAngle, idleRotation)
		gsap.to(this.cont, { duration: idleTim, angle:idleRotation + 4, ease: "sine.out", onComplete:onComplete, onCompleteParams:[] } );
	}

	removed() {
		this.cont.removeChildren();
	}

}