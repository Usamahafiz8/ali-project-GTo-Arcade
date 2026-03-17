import * as PIXI from "pixi.js";

export let gamesLoader:PIXI.Loader;
export const isSafari = /^((?!chrome|android).)*safari|iphone|ipad/i.test(navigator.userAgent);

export function convertTimeToStr(secs:number):(string|number)[]
{
	const days = Math.floor(secs / 86400);
	secs -= days * 86400;

	const hours = Math.floor(secs / 3600);
	secs -= hours * 3600;

	const minutes = Math.floor(secs / 60);
	secs -= minutes * 60;

	let hh:string|number = Math.floor(hours);
	hh = hh<10?`0${hh}`:hh;
	let mm:string|number = Math.floor(minutes);
	mm = mm<10?`0${mm}`:mm;
	let ss:string|number = Math.floor(secs);
	ss = ss<10?`0${ss}`:ss;

	return [hh, mm, ss];
}

export function disableButton(element:Element|any)
{
	if(element && element.classList) {
		element.classList.add("grey-color");
	}
}

export function enableButton(element:Element|any)
{
	if(element && element.classList) {
		element.classList.remove("grey-color");
	}
}

export function validatePhoneForE164(phoneNumber: string) {
	const regEx = /^\+[1-9]\d{10,14}$/;		
	return regEx.test(phoneNumber);
};

export function validateEmail(email: string){
	const regEx = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return regEx.test(email);
};