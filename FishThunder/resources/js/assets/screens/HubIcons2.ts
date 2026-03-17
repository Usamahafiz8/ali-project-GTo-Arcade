import * as PIXI from "pixi.js";
import {SELECTED_PART, UPDATE_BIG_BUTTONS} from "../../Game";
import {gsap} from "gsap";
import {EE} from "../../App";
import { Icon2 } from "../icons/Icon2";
import { getGames } from "../../server/server";
import _ from "lodash";

let max_left:number = 0;
let width_mask:number = 1520;

/**
 * game list module
 */
export class HubIcons2 extends PIXI.Sprite{
	cont:PIXI.Sprite;
	total_width_cont:number = 0;
	headcont:PIXI.Sprite;
	msk:PIXI.Graphics;
	Games: any;
	/**
	 * game list module
	 */
	constructor() {
		super();
		//
		this.updateHub = this.updateHub.bind(this);
		this.onResize = this.onResize.bind(this);
		EE.addListener("RESIZE", this.onResize);
		//
		this.headcont = this.addChild(new PIXI.Sprite());
		this.headcont.scale.set(1.35)

		this.cont = this.headcont.addChild(new PIXI.Sprite());
		this.cont.interactive = true; //need for touch scroll
		(this.cont as any).dragging = false; //need for touch scroll
		this.cont
			.on('mousedown', this.onDragStart)
			.on('touchstart', this.onTouchStart)
			// events for drag end
			.on('mouseup', this.onDragEnd)
			.on('mouseupoutside', this.onDragEnd)
			.on('touchend', this.onDragEnd)
			.on('touchendoutside', this.onDragEnd)
			// events for drag move
			.on('mousemove', this.onDragMove)
			.on('touchmove', this.onTouchMove);

		//this.cont.mask = this.headcont.addChild(new PIXI.Graphics()).beginFill(0x0000ff, 1).drawRect(0,-10,1520,720).endFill();
		this.msk = this.headcont.addChild(new PIXI.Graphics()).beginFill(0x0000ff, 1).drawRect(0,-10,1520,530).endFill();
		this.cont.mask = this.msk;
		EE.addListener(UPDATE_BIG_BUTTONS, this.updateHub);
		
		getGames((e:any)=>{
			this.Games = JSON.parse(e);
			this.updateHub();
		})
	}

	/**
	 * arrangement of module elements depending on the screen size
	 * @param data	object {w, h, scale}
	 */
	onResize(data:any) {
		this.cont.x = 0;
		this.msk.width = (data.w/data.scale);// - 400;
		//
		let hght = (data.h/data.scale);
		let yy = (hght - 1080)/2;
		if(yy<0) yy = 0;
		this.headcont.y = yy;
	}

	/**
	 * start drag game container
	 * @param e
	 */
	onDragStart(e:any)
	{
		(this as any).data = e.data;
		(this as any).dragging = true;
		(this as any).offset = { x: this.position.x - e.data.originalEvent.pageX, y: this.y - e.data.originalEvent.pageY };
	}

	/**
	 * start touch drag game container
	 * @param e
	 */
	onTouchStart(e:any)
	{
		(this as any).data = e.data;
		(this as any).dragging = true;
		const startcoord = (this as any).data.getLocalPosition(this.parent);
		(this as any).offset = { x: this.position.x - startcoord.x, y: 0 };
	}

	/**
	 * finish drag
	 */
	onDragEnd()
	{
		(this as any).dragging = false;
		(this as any).data = null;
		//
		const curx = this.position.x;
		let endx = curx - curx % 90;
		if(curx>0) endx = 0;
		if(endx<max_left) endx = max_left - max_left % 90;
		gsap.to(this.position, {
			duration: 0.5,
			x: endx
		});
	}

	/**
	 * drag move
	 * @param e
	 */
	onDragMove(e:any)
	{
		if ((this as any).dragging)
		{
			this.position.x = e.data.originalEvent.pageX + (this as any).offset.x;
			this.position.y = 0;
		}
	}

	/**
	 * touch move
	 */
	onTouchMove()
	{
		if ((this as any).dragging)
		{
			const newPosition = (this as any).data.getLocalPosition(this.parent);
			this.position.x = newPosition.x + (this as any).offset.x;
			this.position.y = 0;
		}
	}

	/**
	 * build game list
	 */
	async updateHub() {
		gsap.killTweensOf(this.cont);
		await new Promise((resolve) => {
			gsap.to(this.cont, {
				duration: 0.5,
				alpha: 0,
				onComplete: ()=>{
					resolve(null);
				}
			});
		});
		this.cont.removeChildren();
		this.cont.x = 0;
		//
		gsap.to(this.cont, {
			duration: 0.3,
			alpha: 1
		});
		//
		var fishes = _.concat(this.Games.fishes);
		var casinos = _.concat(this.Games.casinos);
		var slots = _.concat(this.Games.slots);
		let gamearray = [];
		switch (SELECTED_PART) {
			case 1:
				while(fishes.length != 0 || casinos.length != 0 || slots.length != 0)
				{
					if(fishes.length > 0)
					{
						gamearray.push(fishes[0]);
						fishes.shift();
					}
					if(casinos.length > 0)
					{
						gamearray.push(casinos[0]);						
						casinos.shift();
					}
					for(var i = 0; i < 6; i++)
					{
						if(slots.length > 0)
						{
							gamearray.push(slots[0]);						
							slots.shift();
						}	
					}
					// gamearray = _.concat(this.Games.fishes, this.Games.casinos, this.Games.slots);
				}
				break;
			case 2:
				gamearray = slots;
				break;
			case 3:
				gamearray = fishes;
				break;
			case 4:
				gamearray = casinos;
				break;
		}
		//
		const xx_plus = 390;
		let xx = 0;
		let yy = 0;
		for (const game of gamearray) {
			let item;
			yy = 20;
			item = new Icon2(game);
			item.x = xx;
			item.y = yy;
			xx+=xx_plus;
			this.cont.addChild(item);
		}
		this.cont.hitArea = new PIXI.Rectangle(0, 0, xx, 530);
		this.total_width_cont = xx;
		if(xx > width_mask) {
			max_left = (xx - width_mask + 390)*-1;
		}
	}


}