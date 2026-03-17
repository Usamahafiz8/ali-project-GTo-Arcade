import React from 'react';
import { EE } from '../App';
import '../css/user.css';
import {PAGE_SIZE_DEFAULT} from "../common/Config";

export class UserWin extends React.Component {
	constructor(props) {
		super(props);
		this.onClose = this.onClose.bind(this);
		this.onSetMobile = this.onSetMobile.bind(this);
		this.onPassEdit = this.onPassEdit.bind(this);
		this.onEdit = this.onEdit.bind(this);

	}

	componentDidMount() {
		EE.addListener("RESIZE", this.onResize);
		EE.emit("FORCE_RESIZE");
	}

	onResize(data) {
		const cont = document.getElementsByClassName("modal-window-user__scale-cont")[0];
		const sc = Math.min(data.h/PAGE_SIZE_DEFAULT.height, data.w/PAGE_SIZE_DEFAULT.width);
		if(cont) {
			cont.style.transform = `scale(${sc})`;
		}
	}

	onPassEdit () {
		console.log('on pass edit!')
	}

	onSetMobile () {
		console.log('on Set Mobile!')
	}

	onHelp () {
		console.log('on help!')
	}

	onEdit () {
		console.log('on edit!')
	}

	onClose () {
		this.props.onClose();
	}

	onFocus (e) {
		e.target.value = '';
	}

	onInput (e) {
		if (e.target.value.length > 16) {
			e.target.value = e.target.value.slice(0,16);
		}
	}

	render () {
		return (
			<div className="modal-window-user">
				<div className="modal-window-user__scale-cont">
					<img className="modal-window-user__ava game-button" src="/images/fish/ava.png" alt=""/>
					<input className="modal-window-user__pass1 modal-window-user__fields" type="text" onFocus={this.onFocus} onInput={this.onInput} value={"**********"}/>
					<input className="modal-window-user__pass2 modal-window-user__fields" type="text" onFocus={this.onFocus} onInput={this.onInput} value={"**********"}/>
					<img className="modal-window-user__close game-button" onClick={this.onClose} src="/images/fish/popup/close_btn.png" alt=""/>
					<img className="modal-window-user__btn1 game-button" onClick={this.onEdit} src="/images/fish/popup/edit_btn.png" alt=""/>
					<img className="modal-window-user__btn4 game-button" onClick={this.onHelp} src="/images/fish/popup/help_btn.png" alt=""/>
					<img className="modal-window-user__btn2 game-button" onClick={this.onSetMobile} src="/images/fish/popup/set_mobile_btn.png" alt=""/>
					<img className="modal-window-user__btn3 game-button" onClick={this.onPassEdit} src="/images/fish/popup/pass_edit_btn.png" alt=""/>
					<img className="modal-window-user__back" src="/images/fish/popup/user_back.png" alt=""/>
				</div>
			</div>
		)
	}
}