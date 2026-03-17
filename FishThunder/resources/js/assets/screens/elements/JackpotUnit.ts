import * as PIXI from "pixi.js";

export class JackpotUnit extends PIXI.Sprite{
	cont:PIXI.Sprite;
	txtmoney: PIXI.Text;
	constructor(type:number) {
		super();
		//
		this.cont = this.addChild(new PIXI.Sprite());
		this.cont.addChild(new PIXI.Sprite(PIXI.Texture.from("/images/fish/money_back.png")));
		//
		this.cont.addChild(new PIXI.Sprite(PIXI.Texture.from(`/images/fish/money${type}.png`)));
		//
		const styletextmoney = new PIXI.TextStyle({
			fontFamily: "Roboto",
			fontSize: "35px",
			fill: "0xFFE395",
			fontWeight: "800",
			miterLimit: 2,
			align: "right",
			strokeThickness: 2
		});
		//
		this.txtmoney = this.cont.addChild(new PIXI.Text("", styletextmoney));
		this.txtmoney.x = 140;
		this.txtmoney.y = 8;
		var txtmoney = this.txtmoney;
		switch (type) {
			case 1:
				txtmoney.text = "";
				txtmoney.x = 150 - txtmoney.width;
				txtmoney.style.fill= [
					"#FFFFFF",
					"#FEBF37"
				];
				break;
			case 2:
				txtmoney.text = "";
				txtmoney.x = 150 - txtmoney.width;
				txtmoney.style.fill= [
					"#FFFFFF",
					"#21DBF9"
				];
				break;
			case 3:
				txtmoney.text = "";
				txtmoney.x = 150 - txtmoney.width;
				txtmoney.style.fill= [
					"#FFFFFF",
					"#27FE56"
				];
				break;
			case 4:
				txtmoney.text = "";
				txtmoney.x = 150 - txtmoney.width;
				txtmoney.style.fill= [
					"#FFFFFF",
					"#F77879"
				];
				break;
		}
	}
}