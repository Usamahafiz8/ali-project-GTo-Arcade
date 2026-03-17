import React from 'react';
import { EE } from '../App';
import '../css/info.css';
import {PAGE_SIZE_DEFAULT} from "../common/Config";
import { deleteUser, User } from '../server/server';

export class InfoWin extends React.Component {
	constructor(props) {
		super(props);
		this.onClose = this.onClose.bind(this);
		this.onDelete = this.onDelete.bind(this);
		this.closeDelAccount = this.closeDelAccount.bind(this);
		this.state = {
			delete_sure: false
		};
	}

	componentDidMount() {
		EE.addListener("RESIZE", this.onResize);
		EE.emit("FORCE_RESIZE");
	}

	onResize(data) {
		const cont = document.getElementsByClassName("modal-window-info__scale-cont")[0];
		const sc = Math.min(data.h/PAGE_SIZE_DEFAULT.height, data.w/PAGE_SIZE_DEFAULT.width);
		if(cont) {
			cont.style.transform = `scale(${sc})`;
		}
	}

	onDelete () {
		this.setState({delete_sure: true});
	}

	goDelAccount () {		
		deleteUser((data) => {
			data = JSON.parse(data);
			if(data.result == 'success')
			{
				var isMobile;
				if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
					// true for mobile device                
					isMobile = true;
				}
				else {
					// false for not mobile device
					isMobile = false;
				}
				if (!isMobile)
					window.location.href = '/logout';
				else {
					var data = JSON.stringify({ event: 'Logout', value: "logout" });
					window.postMessage(data, "*");
					window.location.href = '/logout';
				}
			}
		});
	}

	closeDelAccount () {
		this.setState({delete_sure: false});
	}

	onClose () {
		this.props.onClose();
	}

	render () {
		return (
			<div className="modal-window-info">
				<div className="modal-window-info__scale-cont">
					{this.state.delete_sure?
						<div className="modal-window-info__popup-cont">
							<img className="modal-window-info_text" src="/images/fish/popup/really_delete.png" alt=""/>
							<img className="modal-window-info__yes modal-window-info-popup__btn game-button" onClick={this.goDelAccount} src="/images/fish/popup/yes_btn.png" alt=""/>
							<img className="modal-window-info__no modal-window-info-popup__btn game-button" onClick={this.closeDelAccount} src="/images/fish/popup/no_btn.png" alt=""/>
							<img className="modal-window-info__popup-back" src="/images/fish/popup/message_back.png" alt=""/>
						</div>:""}
					<span className="modal-window-info__user modal-window-info__text">{document.getElementById('root').getAttribute('username')}</span>
					<span className="modal-window-info__pass modal-window-info__text">{"****************"}</span>
					<span className="modal-window-info__phone modal-window-info__text">{User.phone}</span>
					<img className="modal-window-info__close game-button" onClick={this.onClose} src="/images/fish/popup/close_btn.png" alt=""/>

					<img className="modal-window-info__del modal-window-info__btn game-button" onClick={this.onDelete} src="/images/fish/popup/delete_btn.png" alt=""/>
					<img className="modal-window-info__done modal-window-info__btn game-button" onClick={this.props.goChangePass} src="/images/fish/popup/change_btn.png" alt=""/>
					{/* <img className="modal-window-info__change modal-window-info__btn game-button" onClick={this.props.goAddPhone} src="/images/fish/popup/add_btn.png" alt=""/> */}
					<img className="modal-window-info__back" src="/images/fish/popup/info_back.png" alt=""/>
				</div>
			</div>
		)
	}
}