import React from 'react';
import { EE } from '../App';
import '../css/freeplay.css';
import {PAGE_SIZE_DEFAULT} from "../common/Config";
import { signUp } from '../server/server';
import { validateEmail } from '../common/Utils';

export class FreePlayWin extends React.Component {
	constructor(props) {
		super(props);
		this.onClose = this.onClose.bind(this);
		this.onCloseAlert = this.onCloseAlert.bind(this);
		this.onShowAlert = this.onShowAlert.bind(this);
		this.onSubmit = this.onSubmit.bind(this);
		this.state = {
			TEXT_ALERT: ""
		};
	}

	componentDidMount() {
		EE.addListener("RESIZE", this.onResize);
		EE.emit("FORCE_RESIZE");
	}

	onResize(data) {
		const cont = document.getElementsByClassName("modal-window-fp__scale-cont")[0];
		const cont_alert = document.getElementsByClassName("modal-window-freeplay-alert-cont")[0];
		const sc = Math.min(data.h/PAGE_SIZE_DEFAULT.height, data.w/PAGE_SIZE_DEFAULT.width);
		if(cont) {
			cont.style.transform = `scale(${sc})`;
		}
		if(cont_alert) {
			cont_alert.style.transform = `scale(${sc})`;
		}
	}

	onSubmit () {
		var token = document.getElementById('root').getAttribute('token');
		var username = $('.modal-window-fp__name')[0].value;
		var password = $('.modal-window-fp__pass')[0].value;
		var phone = $('.modal-window-fp__phone')[0].value;
		var email = $('.modal-window-fp__email')[0].value;  
		if(username == '')
		{
			this.onShowAlert("Please input username");
			return;
		}
		if(password == '')
		{
			this.onShowAlert("Please input password");
			return;
		}	
		if(!validateEmail(email))
		{
			this.onShowAlert("Please input valid email address");
			return;
		}
		var data = {
			username: username,
			password: password,
			phone: phone,
			email: email,
			_token: token
		}		
		signUp(data, (e) => {
			var result = JSON.parse(e);
			this.onShowAlert(result.message);			
		});			
	}

	onClose () {
		EE.emit('SHOW_LOGIN');
	}

	onShowAlert(message)
	{
		this.setState({TEXT_ALERT: message});
		document.querySelector(".modal-window-freeplay-alert").style.display = "flex";
	}

	onCloseAlert () {
		document.querySelector(".modal-window-freeplay-alert").style.display = "none";
	}

	render () {
		return (
			<div className="modal-window-fp">
				<div className="modal-window-freeplay-alert">
					<div className="modal-window-freeplay-alert-cont">
						<span className="modal-window-freeplay-alert__text">{this.state.TEXT_ALERT}</span>

						<img className="modal-window-freeplay__btn game-button modal-window-freeplay-alert__ok" onClick={this.onCloseAlert} src="/images/fish/popup/ok_btn.png" alt=""/>
						<img className="modal-window-freeplay-alert__back" src="/images/fish/popup/message_back.png" alt=""/>
					</div>
				</div>
				<div className="modal-window-fp__scale-cont">
					<input className="modal-window-fp__name modal-window-fp__fields" type="text" onInput={this.onInput}/>
					<input className="modal-window-fp__pass modal-window-fp__fields" type="password" onInput={this.onInput}/>
					<input className="modal-window-fp__phone modal-window-fp__fields" type="text" onInput={this.onInput}/>
					<input className="modal-window-fp__email modal-window-fp__fields" type="text" onInput={this.onInput}/>
					<img className="modal-window-fp__close game-button" onClick={this.onClose} src="/images/fish/popup/close_btn.png" alt=""/>
					<img className="modal-window-fp__submit game-button" onClick={this.onSubmit} src="/images/fish/popup/submit_btn3.png" alt=""/>
					<img className="modal-window-fp__back" src="/images/fish/popup/fp_back.png" alt=""/>
				</div>
			</div>
		)
	}
}