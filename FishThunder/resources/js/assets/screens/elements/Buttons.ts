import * as PIXI from "pixi.js";
import {GameButton} from "./GameButton";

/**
 * module builds game list filter buttons
 */
export class Buttons extends PIXI.Sprite{
	big1:GameButton; //all
	big2:GameButton; //fish button
	big3:GameButton; //slot button
	big4:GameButton; //firelink button
	cont:PIXI.Sprite; //container for filter buttons
	constructor() {
		super();
		//
		this.cont = this.addChild(new PIXI.Sprite());

		this.big1 = this.cont.addChild(new GameButton(1));
		this.big1.x = 0;
		this.big1.y = 0;

		this.big2 = this.cont.addChild(new GameButton(2));
		this.big2.x = 160;
		this.big2.y = 0;

		this.big3 = this.cont.addChild(new GameButton(3));
		this.big3.x = 320;
		this.big3.y = 0;

		this.big4 = this.cont.addChild(new GameButton(4));
		this.big4.x = 480;
		this.big4.y = 0;
	}
}