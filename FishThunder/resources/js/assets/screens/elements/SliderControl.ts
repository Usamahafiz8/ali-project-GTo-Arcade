import * as PIXI from "pixi.js";
import { EE } from "../../../App";
import {ButtonItem} from "../../gui/ButtonItem";
import {MAX_COUNT_COLUMN} from "../../../common/Config";

export class SliderControl extends PIXI.Sprite{
	cont:PIXI.Sprite;
	contdots:PIXI.Sprite;
	arrow_left:PIXI.Sprite;
	arrow_right:PIXI.Sprite;
	current:number = 0;
	all:number = 0;

	constructor() {
		super();
		//
		this.cont = this.addChild(new PIXI.Sprite());
		this.contdots = this.cont.addChild(new PIXI.Sprite());
		//this.contdots.y = 72;
		this.buildPanel = this.buildPanel.bind(this);
		this.updateStateDots = this.updateStateDots.bind(this);
		this.update = this.update.bind(this);

		const toRight = ()=>{
			if(this.current===(this.all-1)) return;
			this.current++;
			this.updateStateDots();
		};
		const toLeft = ()=>{
			if(this.current===0) return;
			this.current--;
			this.updateStateDots();
		};


		//this.back_l.alpha = this.back_c.alpha = this.back_r.alpha = 0;
		this.arrow_left = this.cont.addChild(new ButtonItem("/images/fish/arrow.png", toLeft));
		this.arrow_left.y = -20;
		this.arrow_left.x = -215;

		this.arrow_right = this.cont.addChild(new ButtonItem("/images/fish/arrow.png", toRight));
		this.arrow_right.scale.x = -1;
		this.arrow_right.y = -20;
		this.arrow_right.x = 235;
		this.buildPanel(1);
		//
		this.x = 323;
		this.y = 55;
		//
		EE.addListener('UPDATE_CONTROL', this.update);
		EE.addListener('SLIDER_R', toRight);
		EE.addListener('SLIDER_L', toLeft);
	}

	update(obj:any) {
		let pg = 1;
		if(obj && obj.totalcolumn) {
			pg = Math.ceil(obj.totalcolumn / MAX_COUNT_COLUMN);
		}
		//console.log(obj.totalcolumn,  MAX_COUNT_COLUMN, pg)
		this.current = 0;
		this.buildPanel(pg);
	}

	updateStateDots() {
		for(let i=0;i<this.contdots.children.length;i++) {
			const dot = this.contdots.children[i] as any;
			dot.setActive(false);
			if(i===this.current) dot.setActive(true);
		}
		EE.emit('SLIDER_ACTION', {page: this.current});
	}

	buildPanel(count:number) {
		this.all = count;
		this.contdots.removeChildren();
		const alldotslen = count*23;
		const space = Math.min((280 - alldotslen)/(count - 1), 20);
		for(let i=0;i<count;i++) {
			const dot = this.contdots.addChild(new DotForSlider(i));
			dot.x = i*(28 + space);
			if(i===this.current) dot.setActive(true);
		}
		this.contdots.x = -((28 + space)*(count-1))/2-10;
	}

}

class DotForSlider extends PIXI.Sprite{
	cont:PIXI.Sprite;
	active:boolean = false;
	active_dot:PIXI.Sprite;
	id:number;

	constructor(id:number) {
		super();
		//
		this.id = id;
		this.cont = this.addChild(new PIXI.Sprite());
		this.setActive = this.setActive.bind(this);

		this.cont.addChild(new PIXI.Sprite(PIXI.Texture.from("/images/fish/dot1.png")));
		this.active_dot = this.cont.addChild(new PIXI.Sprite(PIXI.Texture.from("/images/fish/dot2.png")));
		this.active_dot.visible = false;
		this.active_dot.y = 4;
	}

	setActive(act:boolean) {
		this.active = this.active_dot.visible = act;
	}

}