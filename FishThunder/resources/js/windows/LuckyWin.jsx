import React from 'react';
import { EE } from '../App';
import '../css/lucky.css';
import {PAGE_SIZE_DEFAULT} from "../common/Config";

export class LuckyWin extends React.Component {
	constructor(props) {
		super(props);
		this.onClose = this.onClose.bind(this);
	}

	componentDidMount() {
		EE.addListener("RESIZE", this.onResize);
		EE.emit("FORCE_RESIZE");
	}

	onResize(data) {
		const cont = document.getElementsByClassName("modal-window-lucky__scale-cont")[0];
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
			<div className="modal-window-lucky">
				<div className="modal-window-lucky__scale-cont">
					<span className="modal-window-lucky__t1 modal-window-lucky__text">{"YOU WON GRAND COMMUNITY"}</span>
					<span className="modal-window-lucky__t2 modal-window-lucky__text">{"PRIZE JACKPOT"}</span>
					<span className="modal-window-lucky__prize">{"299.00"}</span>
					<span className="modal-window-lucky__prize-shadow">{"299.00"}</span>
					<img className="modal-window-lucky__close game-button" onClick={this.onClose} src="/images/fish/popup/close_btn.png" alt=""/>
					<img className="modal-window-lucky__back" src="/images/fish/popup/cong_back.png" alt=""/>
				</div>
			</div>
		)
	}
}