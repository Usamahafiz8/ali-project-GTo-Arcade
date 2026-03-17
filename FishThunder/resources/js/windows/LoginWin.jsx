import React from 'react';
import { EE } from '../App';
import '../css/login.css';
import $ from 'jquery';
import {PAGE_SIZE_DEFAULT} from "../common/Config";
import { post } from '../server/server';

const logoimages = [
	"/images/fish/login/logo/1.png",
	"/images/fish/login/logo/2.png",
	"/images/fish/login/logo/3.png",
	"/images/fish/login/logo/4.png",
	"/images/fish/login/logo/5.png",
	"/images/fish/login/logo/6.png",
	"/images/fish/login/logo/7.png",
	"/images/fish/login/logo/8.png",
	"/images/fish/login/logo/9.png",
	"/images/fish/login/logo/10.png",
	"/images/fish/login/logo/11.png",
	"/images/fish/login/logo/12.png",
	"/images/fish/login/logo/13.png",
];

const logoimagesback = [
	"/images/fish/login/back/1.png",
	"/images/fish/login/back/2.png",
	"/images/fish/login/back/3.png",
	"/images/fish/login/back/4.png",
	"/images/fish/login/back/5.png",
	"/images/fish/login/back/6.png",
	"/images/fish/login/back/7.png",
	"/images/fish/login/back/8.png",
	"/images/fish/login/back/9.png",
	"/images/fish/login/back/10.png",
	"/images/fish/login/back/11.png",
	"/images/fish/login/back/12.png",
	"/images/fish/login/back/13.png",
	"/images/fish/login/back/14.png",
];

export class LoginWin extends React.Component {
	int = 0;
	i = 0;
	b = 0;
	constructor() {
		super();
		this.animateLogo = this.animateLogo.bind(this);
	}

	componentWillUnmount() {
		clearInterval(this.int);
	}

	componentDidMount() {
		EE.addListener("RESIZE", this.onResize);
		EE.emit("FORCE_RESIZE");
		this.int = setInterval(this.animateLogo, 50);

		var remember = window.localStorage.getItem('is_remember');
		var mem_username = '';
		var mem_password = '';
		if(remember == undefined)
		{
			window.localStorage.setItem('is_remember', 1);			
		}
		else
		{
			if(remember == 1)
			{
				mem_username = window.localStorage.getItem('username');
				mem_password = window.localStorage.getItem('password');
				if(mem_username == undefined)
					mem_username = '';
				if(mem_password == undefined)
					mem_password = '';
				$('#username').val(mem_username);
				$('#password').val(mem_password);
			}
			else
			{
				var check = $('.modal-window-login__check')[0];
				check.style.opacity = 0;
			}
		}
	}

	animateLogo() {
		const img = document.getElementById("logo-login");
		const imgb = document.getElementById("back-login");
		if(img) {
			img.src = logoimages[this.i];
			imgb.src = logoimagesback[this.b];
			this.i++;
			this.b++;
			if (logoimages.length === this.i) {
				this.i = 0;
			}
			if (logoimagesback.length === this.b) {
				this.b = 0;
			}
		}
	}

	onResize(data) {
		const cont = document.getElementsByClassName("modal-window-login__scale-cont")[0];
		const sc = Math.min(data.h/PAGE_SIZE_DEFAULT.height, data.w/PAGE_SIZE_DEFAULT.width);
		if(cont) {
			cont.style.transform = `scale(${sc})`;
		}
	}

	goLogin() {
		const id = $('.modal-window-login__text-id')[0];
		const pass = $('.modal-window-login__text-pass')[0];
		console.log(id.value, pass.value);

		var remember = window.localStorage.getItem('is_remember');
		if(remember)
		{
			window.localStorage.setItem('username', id.value);
			window.localStorage.setItem('password', pass.value);
		}
		var token = document.getElementById('root').getAttribute('token');
		post('/login', {username: id.value, password: pass.value, _token: token});
	}

	onInput(e) {
		if (e.target.value.length > 30) {
			e.target.value = e.target.value.slice(0,30);
		}
	}

	checkRem(e) {
		e.target.style.transition = "0.5s";
		e.target.style.opacity = (e.target.style.opacity==="0"?"1":"0");
		if(e.target.style.opacity == 1)
			window.localStorage.setItem('is_remember', 1);
		else
			window.localStorage.setItem('is_remember', 0);
	}

	goRegistration() {
		EE.emit('SHOW_FREE_PLAY');
	}

	render () {
		return (
			<div className="modal-window-login">
				<div className="modal-window-login__scale-cont">
					<div className="modal-window-login__logo">
						<img className="modal-window-login__logo1" src="" id={"logo-login"} alt=""/>
						<img className="modal-window-login__logo2" src="/images/fish/login/logo_text.png" alt=""/>
					</div>
					<div className="modal-window-login__down">
						<input id='username' className="modal-window-login__text-id modal-window-login__fields" type="text" onInput={this.onInput}/>
						<input id='password' className="modal-window-login__text-pass modal-window-login__fields" type="password" onInput={this.onInput}/>
						<img className="modal-window-login__check" onClick={this.checkRem} src="/images/fish/dot2.png" alt=""/>
						<img className="modal-window-login__login modal-window-login__btn game-button" onClick={this.goLogin} src="/images/fish/login/login_btn.png" alt=""/>
						<img className="modal-window-login__fplay modal-window-login__btn game-button" onClick={this.goRegistration} src="/images/fish/login/freeplay_btn.png" alt=""/>
						<img className="modal-window-login__back" src="" id={"back-login"}  alt=""/>
					</div>
				</div>
			</div>
		)
	}
}