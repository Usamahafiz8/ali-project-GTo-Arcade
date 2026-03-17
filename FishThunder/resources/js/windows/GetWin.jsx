import React from 'react';
import { EE } from '../App';
import '../css/get.css';
import {PAGE_SIZE_DEFAULT} from "../common/Config";

export class GetWin extends React.Component {
	constructor(props) {
		super(props);
		this.onClose = this.onClose.bind(this);
		this.onGet = this.onGet.bind(this);
		this.onVerify = this.onVerify.bind(this);

	}

	componentDidMount() {
		EE.addListener("RESIZE", this.onResize);
		EE.emit("FORCE_RESIZE");
	}

	onResize(data) {
		const cont = document.getElementsByClassName("modal-window-get__scale-cont")[0];
		const sc = Math.min(data.h/PAGE_SIZE_DEFAULT.height, data.w/PAGE_SIZE_DEFAULT.width);
		if(cont) {
			cont.style.transform = `scale(${sc})`;
		}
	}

	onGet () {
		console.log('go get!')
	}

	onVerify () {
		console.log('go verify!')
	}

	onFocus (e) {
		e.target.value = '';
	}

	onClose () {
		this.props.onClose();
	}

	render () {
		return (
			<div className="modal-window-get">
				<div className="modal-window-get__scale-cont">
					<input className="modal-window-get__phone modal-window-get__fields" type="text" onFocus={this.onFocus} onInput={this.onInput} placeholder={"Phone"}/>
					<input className="modal-window-get__code modal-window-get__fields" type="text" onFocus={this.onFocus} onInput={this.onInput} placeholder={"Code"}/>
					<img className="modal-window-get__close game-button" onClick={this.onClose} src="/images/fish/popup/close_btn.png" alt=""/>
					<img className="modal-window-get__get game-button" onClick={this.onGet} src="/images/fish/popup/get_btn.png" alt=""/>
					<img className="modal-window-get__ver game-button" onClick={this.onVerify} src="/images/fish/popup/verify_btn.png" alt=""/>
					<img className="modal-window-get__back" src="/images/fish/popup/get_back.png" alt=""/>
				</div>
			</div>
		)
	}
}