import React from 'react';
import { EE } from '../App';
import '../css/mail.css';
import {PAGE_SIZE_DEFAULT} from "../common/Config";

const messages = [
	
];

let SELECTED_MESSAGE = 0;

export class MailWin extends React.Component {
	constructor(props) {
		super(props);
		this.onClose = this.onClose.bind(this);
		this.getMessages = this.getMessages.bind(this);
		this.selectMsg = this.selectMsg.bind(this);
		EE.addListener('SELECT_MSG', this.selectMsg);
		this.state = {
			id_message: 0
		}
	}

	selectMsg() {
		this.setState({
			id_message: SELECTED_MESSAGE
		})
	}

	componentDidMount() {
		EE.addListener("RESIZE", this.onResize);
		EE.emit("FORCE_RESIZE");
	}

	onResize(data) {
		const cont = document.getElementsByClassName("modal-window-msg__scale-cont")[0];
		const sc = Math.min(data.h/PAGE_SIZE_DEFAULT.height, data.w/PAGE_SIZE_DEFAULT.width);
		if(cont) {
			cont.style.transform = `scale(${sc})`;
		}
	}

	getMessages() {
		return(messages.map((item, index) => {
			return(
				<MessageItem msg={item} key={index} />
			)
		}));
	}

	onClose () {
		this.props.onClose();
	}

	render () {
		return (
			<div className="modal-window-msg">
				<div className="modal-window-msg__scale-cont">
					{messages.length === 0 ?
						<img className="modal-window-nomsg" src="/images/fish/popup/no_msg.png" alt=""/>
						:
						<div>
							<div className="modal-window-msg__items">
								{this.getMessages()}
							</div>
							{this.state.id_message>0 ?
							<div className="modal-window-msg__body">
								<span className="mail_body-from0">{"From:"}</span>
								<span className="mail_body-from">{messages[this.state.id_message-1].from}</span>
								<span className="mail_body-text">{messages[this.state.id_message-1].text}</span>
							</div> : ""}
						</div>

					}
					<img className="modal-window-mail__close game-button" onClick={this.onClose} src="/images/fish/popup/close_btn.png" alt=""/>
					<img className="modal-window-msg__back" src="/images/fish/popup/mess_back.png" alt=""/>
				</div>
			</div>
		)
	}
}

class MessageItem extends React.Component {
	constructor(props) {
		super(props);
		this.resetSelectMsg = this.resetSelectMsg.bind(this);
		EE.addListener('RESET_SELECT_MSG', this.resetSelectMsg);
		this.state = {
			select: false
		}
	}

	resetSelectMsg() {
		this.setState({select: false});
	}

	render () {
		let txt = this.props.msg.text;
		if(txt.length>55) txt = String(this.props.msg.text).substr(0, 55)+"...";
		return (
			<div className="mail_item-wrapper" onClick={()=>{
				if(SELECTED_MESSAGE===this.props.msg.id) return;
				EE.emit('RESET_SELECT_MSG');
				this.setState({select: true});
				SELECTED_MESSAGE = this.props.msg.id;
				EE.emit('SELECT_MSG');
			}}>
				{this.state.select ?
				<img className="mail_item_select" src="/images/fish/popup/msg_select.png" alt=""/> : ""}
				<span className="mail_item-date">{this.props.msg.date}</span>
				<span className="mail_item-from">{this.props.msg.from}</span>
				<span className="mail_item-text">{txt}</span>
			</div>
		)
	}

}