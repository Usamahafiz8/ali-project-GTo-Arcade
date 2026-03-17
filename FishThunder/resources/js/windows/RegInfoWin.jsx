import React from 'react';
import {EE, showPopup} from '../App';
import '../css/reginfo.css';
import {PAGE_SIZE_DEFAULT} from "../common/Config";

export class RegInfoWin extends React.Component {
	constructor(props) {
		super(props);
		this.onClose = this.onClose.bind(this);
	}

	componentDidMount() {
		EE.addListener("RESIZE", this.onResize);
		EE.emit("FORCE_RESIZE");
	}

	onResize(data) {
		const cont = document.getElementsByClassName("modal-window-reginfo__scale-cont")[0];
		const sc = Math.min(data.h/PAGE_SIZE_DEFAULT.height, data.w/PAGE_SIZE_DEFAULT.width);
		if(cont) {
			cont.style.transform = `scale(${sc})`;
		}
	}

	onClose() {
		//TODO test popup
		// showPopup("Closing popup");
		this.props.onClose();
	}

	render () {
		return (
			<div className="modal-window-reginfo">
				<div className="modal-window-reginfo__scale-cont">
					<img className="modal-window-reginfo__close game-button" onClick={this.onClose} src="/images/fish/popup/close_btn.png" alt=""/>
					<img className="modal-window-reginfo__coins" src="/images/fish/popup/coins.png" alt=""/>
					<img className="modal-window-reginfo__back" src="/images/fish/popup/reginfo_back.png" alt=""/>
				</div>
			</div>
		)
	}
}