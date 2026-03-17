import React from 'react';
import { EE } from '../App';
import '../css/editinfotext.css';
import {PAGE_SIZE_DEFAULT} from "../common/Config";

export class EditInfoTextWin extends React.Component {
	constructor(props) {
		super(props);
		this.onClose = this.onClose.bind(this);
		this.onSave = this.onSave.bind(this);
		this.onEdit = this.onEdit.bind(this);

	}

	componentDidMount() {
		EE.addListener("RESIZE", this.onResize);
		EE.emit("FORCE_RESIZE");
	}

	onResize(data) {
		const cont = document.getElementsByClassName("modal-window-edittext__scale-cont")[0];
		const sc = Math.min(data.h/PAGE_SIZE_DEFAULT.height, data.w/PAGE_SIZE_DEFAULT.width);
		if(cont) {
			cont.style.transform = `scale(${sc})`;
		}
	}

	onSave () {
		console.log('on save!')
	}

	onEdit () {
		console.log('on edit!')
	}

	onClose () {
		this.props.onClose();
	}

	render () {
		return (
			<div className="modal-window-edittext">
				<div className="modal-window-edittext__scale-cont">
					<img className="modal-window-edittext__ava game-button" src="/images/fish/ava.png" alt=""/>
					<textarea className="modal-window-edittext__comment modal-window-edittext__fields" type="text" defaultValue={"Information about user. Can to be edit"}/>
					<img className="modal-window-edittext__btn1 game-button" onClick={this.onEdit} src="/images/fish/popup/edit2_btn.png" alt=""/>
					<img className="modal-window-edittext__btn2 game-button" onClick={this.onSave} src="/images/fish/popup/save_btn.png" alt=""/>
					<img className="modal-window-edittext__btn3 game-button" onClick={this.onClose} src="/images/fish/popup/cancel_btn.png" alt=""/>
					<img className="modal-window-edittext__back" src="/images/fish/popup/about_back.png" alt=""/>
				</div>
			</div>
		)
	}
}