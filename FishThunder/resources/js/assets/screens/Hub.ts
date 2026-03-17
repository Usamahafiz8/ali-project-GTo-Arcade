import * as PIXI from "pixi.js";
import { EE, showCashbackPopup, showPopup } from "../../App";
import { pageStatus, updatePageStatus } from "../../Game";
import { BonusWheelData, User } from "../../server/server";
// import { Buttons } from "./elements/Buttons";
import { HubDown } from "./elements/HubDown";
import {HubTop} from "./elements/HubTop";

export class Hub extends PIXI.Sprite{
	cont: PIXI.Sprite = new PIXI.Sprite();
	down: HubDown;
	// buttons:PIXI.Sprite = new PIXI.Sprite();
	hubTop: HubTop;
	isFirstOpen: Boolean;
	constructor() {
		super();
		//
		updatePageStatus(0);
		this.isFirstOpen = true;
		this.cont = this.addChild(new PIXI.Sprite());
		this.hubTop = this.cont.addChild(new HubTop());
		this.down = this.cont.addChild(new HubDown());
		// this.buttons = this.cont.addChild(new Buttons());
		// this.buttons.x = -100;

		this.onResize = this.onResize.bind(this);
		EE.addListener("RESIZE", this.onResize);
		this.readUserInfo();
		setInterval(() => { this.readUserInfo(); }, 5000);
		// TTL = 0;
		
		// var hubInstance = this;
		// addEventListener('message', function (e) {
		// 	try {
		// 		var data = JSON.parse(e.data);

		// 		if (data.event == 'backToHub') {					
		// 			this.window.location.reload();
		// 			// updatePageStatus(0);
		// 			// hubInstance.readUserInfo();
		// 			// hubInstance.down.user.moneyuser.text = "";
		// 			// document.getElementById("game-frame")!.remove();
		// 			// document.getElementById("game-background")!.remove();
		// 			// if(!SOUND.MUTE)
		// 			// 	SOUND.unmuteSounds();
		// 			// window.localStorage.setItem('game_orientation', 'landscape');

		// 			// try
		// 			// {
		// 			// 	// @ts-ignore
		// 			// 	JSBridge.sendMessageToNative("landscape");    
		// 			// }catch(e){}
		// 			// try
		// 			// {
		// 			// 	// @ts-ignore
		// 			// 	window.webkit.messageHandlers.swiftJSBridge.postMessage("landscape");
		// 			// }catch(e){}
		// 		}
		// 	}
		// 	catch (e) { }
		// })
	}

	onResize(_data:any) {
		// this.buttons.y = (_data.h/_data.scale)/2 - 240;		
	}

	readUserInfo() {
		var hubInstance = this;
		var t = (new Date()).getTime();
		
		if (pageStatus == 0) 
		{
			fetch('/jpstv.json?page=hub&r=' + t, { method: 'GET' })
				.then(response => response.json())
				.then(response => {
					if (response.status == "logout") {
						var isMobile;
						if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
							// true for mobile device                
							isMobile = true;
						}
						else {
							// false for not mobile device
							isMobile = false;
						}

						if (!isMobile)
							window.location.href = '/logout';
						else {
							var data = JSON.stringify({ event: 'Logout', value: "logout" });
							window.postMessage(data, "*");
							window.location.href = '/logout';
						}
						return;
					}
					if(hubInstance.isFirstOpen)
					{
						hubInstance.isFirstOpen = false;
						if(response['phone'] == '')
							EE.emit('SHOW_REGINFO'); //reginfo popup
						User.phone = response['phone'];
						User.phone_ref = response['phone_ref'];
					}
					hubInstance.down.user.moneyuser.text = response['balance'];
					var content = response.content;				
					this.hubTop.grand.txtmoney.text = content[3]['jackpot'];  
					this.hubTop.major.txtmoney.text = content[2]['jackpot'];  
					this.hubTop.minor.txtmoney.text = content[1]['jackpot'];  
					this.hubTop.mini.txtmoney.text = content[0]['jackpot'];  	

					if (response.won_bonuses) {
						for (var i = 0; i < response.won_bonuses.length; i++) {
							var won_bonus = response.won_bonuses[i];
							var description = won_bonus.description;
							var id = won_bonus.id;
							var last_bonus = window.localStorage.getItem('bonus' + id);
							if (last_bonus != won_bonus.date) {
								window.localStorage.setItem('bonus' + id, won_bonus.date);
								if(description == 'Cashback')
								{
									showCashbackPopup(won_bonus.amount);
								}
								else
								{
									var message = "You have won " + description + " of " + won_bonus.amount + " credits";
									showPopup(message);
								}
								break;
							}
						}
					}

					//set wheel bonus values
					if(response.wheel)
					{
						BonusWheelData.available_prizes = response.wheel.values;
						BonusWheelData.bonus_time = response.wheel.bonus_time;
						BonusWheelData.active = response.wheel.active
					}
				});
		}

	}

}