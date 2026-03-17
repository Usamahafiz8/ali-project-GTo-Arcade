import React from 'react';
import { EE, showPopup } from '../App';
import '../css/signphone.css';
import {PAGE_SIZE_DEFAULT} from "../common/Config";
import { sendReferral, sendSMS, verifySMS } from '../server/server';
import { User } from '../server/server';
import { disableButton, validatePhoneForE164 } from '../common/Utils';

const defaultText1 = "Phone Number";
const defaultText2 = "Referral Code";

export class SignPhoneWin extends React.Component {
	constructor(props) {
		super(props);
		this.state = {
			modal: false
		}
		this.onClose = this.onClose.bind(this);
		this.onVerify = this.onVerify.bind(this);
		this.onSubmit = this.onSubmit.bind(this);
		this.onSubmitModal = this.onSubmitModal.bind(this);
		this.onCloseModal = this.onCloseModal.bind(this);
		this.message_id = '';
	}

	componentDidMount() {
		EE.addListener("RESIZE", this.onResize);
		EE.emit("FORCE_RESIZE");
		if(User.phone_ref != '')
		{
			disableButton(document.querySelector('.modal-window-signphone__ok'));
		}
	}

	onResize(data) {
		const cont = document.getElementsByClassName("modal-window-signphone__scale-cont")[0];
		const sc = Math.min(data.h/PAGE_SIZE_DEFAULT.height, data.w/PAGE_SIZE_DEFAULT.width);
		if(cont) {
			cont.style.transform = `scale(${sc})`;
		}
	}

	onSubmit () {
		//submit referral code
		if(User.phone_ref != '')
			return;
		var phone = $('#referral').val();		
		if(!validatePhoneForE164(phone))
		{
			alert('Please input with E.164 format');
			return;
		}
		var data = {
			ref_phone: phone
		};
		sendReferral(data, (response) => {
			var result = JSON.parse(response);
			alert(result.message);
		})
	}

	onVerify () {		
		var message_id = Math.random().toString(36).substring(2);
		this.message_id = message_id;
		console.log('message id: ' + message_id);
		var phone = $('#phone').val();
		if(!this.validatePhoneForE164(phone))
		{
			alert('Please input with E.164 format');
			return;
		}
		
		//send phone number to get sms verification code
		var data = {
			message_id : message_id,
			phone: phone
		}
		sendSMS(data, null);
		this.setState({modal: true});
	}	

	onSubmitModal () {
		//submit sms 6 digits		
		var code = $('#code').val();
		var data = {
			message_id : this.message_id,
			code: code
		}
		verifySMS(data, (response) => {
			var result = JSON.parse(response);
			if(result.status == 'success')
			{
				if(result.data.bonusWon)
				{
					showPopup('Phone registered Successfully. You have won phone signup bonus ' + result.data.amount + ' credits');
				}
				else
				{
					showPopup('Phone registered Successfully.');
				}
			}
			else
			{
				alert(result.message);
			}
		})		
	}

	onBlur1 (e) {
		if(e.target.value==="") e.target.value = defaultText1;
	}
	onBlur2 (e) {
		if(e.target.value==="")	e.target.value = defaultText2;
	}

	onFocus (e) {
		e.target.value = '';
	}

	onInput (e) {
		if (e.target.value.length > 16) {
			e.target.value = e.target.value.slice(0,16);
		}
	}	

	onCloseModal () {
		this.setState({modal: false});
	}

	onClose () {
		this.props.onClose();
	}

	render () {
		return (
			<div className="modal-window-signphone">
					<div className="modal-window-signphone__scale-cont">
						{this.state.modal?
							(<div className="modal-window-signphone__verify-cont">
							<input id='code' className="modal-window-signphone__fields modal-window-signphone__code-modal" type="text" maxLength="6" onInput={this.onInput} placeholder={"------"} />
							<img className="modal-window-signphone__close-modal game-button" onClick={this.onCloseModal} src="/images/fish/popup/close_btn.png" alt=""/>
							<img className="modal-window-signphone__submit-modal game-button" onClick={this.onSubmitModal} src="/images/fish/popup/submit_btn2.png" alt=""/>
							<img className="modal-window-signphone__back-modal" src="/images/fish/popup/verify_back.png" alt=""/>
						</div>):""}
					<input id="phone" className="modal-window-signphone__phone modal-window-signphone__fields" type="text" onInput={this.onInput} placeholder={defaultText1} value={User.phone}/>
					<input id="referral" className="modal-window-signphone__code modal-window-signphone__fields" type="text" onInput={this.onInput} placeholder={defaultText2} value={User.phone_ref}/>
					<img className="modal-window-signphone__close game-button" onClick={this.onClose} src="/images/fish/popup/close_btn.png" alt=""/>
					<img className="modal-window-signphone__verify game-button" onClick={this.onVerify} src="/images/fish/popup/verify_btn2.png" alt=""/>
					<img className="modal-window-signphone__ok game-button" onClick={this.onSubmit} src="/images/fish/popup/submit_btn.png" disabled={User.phone_ref != ''}/>
					<img className="modal-window-signphone__back" src="/images/fish/popup/signphone_back.png" alt=""/>
				</div>
			</div>
		)
	}
}