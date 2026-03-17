import React from 'react';
import { EE } from '../App';
import '../css/settings.css';
import {PAGE_SIZE_DEFAULT} from "../common/Config";

export class SettingsWin extends React.Component {
	constructor(props) {
		super(props);
		this.onClose = this.onClose.bind(this);

	}

	componentDidMount() {
		EE.addListener("RESIZE", this.onResize);
		EE.emit("FORCE_RESIZE");
	}

	onResize(data) {
		const cont = document.getElementsByClassName("modal-window-set__scale-cont")[0];
		const sc = Math.min(data.h/PAGE_SIZE_DEFAULT.height, data.w/PAGE_SIZE_DEFAULT.width);
		if(cont) {
			cont.style.transform = `scale(${sc})`;
		}
	}

	onClose () {
		this.props.onClose();
	}

	render () {
		return (
			<div className="modal-window-set">
				<div className="modal-window-set__scale-cont">
					<img className="modal-window-set__close game-button" onClick={this.onClose} src="/images/fish/popup/close_btn.png" alt=""/>
					<img className="modal-window-set__btn1 game-button" onClick={this.onRegistration} src="/images/fish/popup/sounds_btn.png" alt=""/>
					<img className="modal-window-set__btn2 game-button" onClick={this.onRegistration} src="/images/fish/popup/terms_btn.png" alt=""/>
					<img className="modal-window-set__btn3 game-button" onClick={this.onRegistration} src="/images/fish/popup/about_btn.png" alt=""/>
					<img className="modal-window-set__back" src="/images/fish/popup/sett_back.png" alt=""/>
				</div>
			</div>
		)
	}
}