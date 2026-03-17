import React from 'react';
import { EE } from '../App';
import '../css/cashback.css';
import {PAGE_SIZE_DEFAULT} from "../common/Config";

export class CashbackWin extends React.Component {
	constructor(props) {
		super(props);
		this.onClose = this.onClose.bind(this);
	}

	componentDidMount() {
		EE.addListener("RESIZE", this.onResize);
		EE.emit("FORCE_RESIZE");
	}

	onResize(data) {
		const cont = document.getElementsByClassName("modal-window-cashback__scale-cont")[0];
		const sc = Math.min(data.h/PAGE_SIZE_DEFAULT.height, data.w/PAGE_SIZE_DEFAULT.width);
		if(cont) {
			cont.style.transform = `scale(${sc})`;
		}
	}

	onClose() {
		this.props.onClose();
	}

	render () {
		return (
			<div className="modal-window-cashback">
				<div className="modal-window-cashback__scale-cont">
					<span className="modal-window-cashback__prize">{this.props.amount}</span>
					<span className="modal-window-cashback__prize-shadow">{this.props.amount}</span>
					<img className="modal-window-cashback__close game-button" onClick={this.onClose} src="/images/fish/popup/close_btn.png" alt=""/>
					<img className="modal-window-cashback__back" src="/images/fish/popup/cashback_back.png" alt=""/>
				</div>
			</div>
		)
	}
}