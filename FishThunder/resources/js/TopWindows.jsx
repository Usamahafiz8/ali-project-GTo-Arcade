import React from 'react';
import {EE} from "./App";
import {LoginWin} from "./windows/LoginWin";
import {RegWin} from "./windows/RegWin";
import {NewPassWin} from "./windows/NewPassWin";
import {InfoWin} from "./windows/InfoWin";
import {BonusWin} from "./windows/BonusWin";
import {LuckyWin} from "./windows/LuckyWin";
import {MailWin} from "./windows/MailWin";
import {SettingsWin} from "./windows/SettingsWin";
import {EditInfoTextWin} from "./windows/EditInfoTextWin";
import {GetWin} from "./windows/GetWin";
import {UserWin} from "./windows/UserWin";
import {SignPhoneWin} from "./windows/SignPhoneWin";
import {RegInfoWin} from "./windows/RegInfoWin";
import {MessageWin} from "./windows/MessageWin";
import {FreePlayWin} from "./windows/FreePlayWin";
import {CashbackWin} from "./windows/CashbackWin";

let PAGE_LOGIN = "PAGE_LOGIN";
let PAGE_INFO = "PAGE_INFO";
let PAGE_REG = "PAGE_REG";
let PAGE_FREE_PLAY = "PAGE_FREE_PLAY";
let PAGE_NPASS = "PAGE_NPASS";
let PAGE_BONUS = "PAGE_BONUS";
let SHOW_LUCKY = "SHOW_LUCKY";
let PAGE_CASHBACK = "PAGE_CASHBACK";
let PAGE_MAIL = "PAGE_MAIL";
let PAGE_SETTINGS = "PAGE_SETTINGS";
let PAGE_EDITINFO = "PAGE_EDITINFO";
let PAGE_GET = "PAGE_GET";
let PAGE_USER = "PAGE_USER";
let PAGE_SIGNPHONE = "PAGE_SIGNPHONE";
let PAGE_REGINFO = "PAGE_REGINFO";
let PAGE_MESSAGE = "PAGE_MESSAGE";

class TopWindows extends React.Component {
	constructor(props) {
		super(props);
		this.onCloseAll = this.onCloseAll.bind(this);
		this.goOpenChangePass = this.goOpenChangePass.bind(this);
		this.goAddPhone = this.goAddPhone.bind(this);
		this.state = {
			CURRENT_PAGE: "",
			data: "",
		}
		EE.addListener('CLEAR_TOP_WINDOWS', this.onCloseAll);
	}

	onCloseAll() {
		this.setState({CURRENT_PAGE: ""});
	}

	componentDidMount() {
		EE.addListener('SHOW_LUCKY', ()=>{
			this.setState({CURRENT_PAGE: SHOW_LUCKY});
		});
		EE.addListener('SHOW_CASHBACK', (amount)=>{
			this.setState({CURRENT_PAGE: PAGE_CASHBACK, data: amount});
		});
		EE.addListener('SHOW_MAIL', ()=>{
			this.setState({CURRENT_PAGE: PAGE_MAIL});
		});
		EE.addListener('SHOW_SETTINGS', ()=>{
			this.setState({CURRENT_PAGE: PAGE_SETTINGS});
		});
		EE.addListener('SHOW_EDITINFO', ()=>{
			this.setState({CURRENT_PAGE: PAGE_EDITINFO});
		});
		EE.addListener('SHOW_GET', ()=>{
			this.setState({CURRENT_PAGE: PAGE_GET});
		});
		EE.addListener('SHOW_USER', ()=>{
			this.setState({CURRENT_PAGE: PAGE_USER});
		});
		EE.addListener('SHOW_LOGIN', ()=>{
			this.setState({CURRENT_PAGE: PAGE_LOGIN});
		});
		EE.addListener('SHOW_INFO', ()=>{
			this.setState({CURRENT_PAGE: PAGE_INFO});
		});
		EE.addListener('SHOW_REG', ()=>{
			this.setState({CURRENT_PAGE: PAGE_REG});
		});
		EE.addListener('SHOW_BONUS', ()=>{
			this.setState({CURRENT_PAGE: PAGE_BONUS});
		});
		EE.addListener('SHOW_SIGNPHONE', ()=>{
			this.setState({CURRENT_PAGE: PAGE_SIGNPHONE});
		});
		EE.addListener('SHOW_REGINFO', ()=>{
			this.setState({CURRENT_PAGE: PAGE_REGINFO});
		});
		EE.addListener('SHOW_FREE_PLAY', ()=>{
			this.setState({CURRENT_PAGE: PAGE_FREE_PLAY});
		});
		EE.addListener('SHOW_MESSAGE', (txt)=>{
			this.setState({CURRENT_PAGE: PAGE_MESSAGE, data: txt});
		});
		EE.addListener('SHOW_NPASS', this.goOpenChangePass);
	}

	goOpenChangePass () {
		this.setState({CURRENT_PAGE: PAGE_NPASS});
	}

	goAddPhone () {
		this.setState({CURRENT_PAGE: PAGE_SIGNPHONE});
	}

	render () {
		return (
			<div className="modal-windows-cont">
				{(this.state.CURRENT_PAGE === PAGE_USER && <UserWin onClose={this.onCloseAll}/> )}
				{(this.state.CURRENT_PAGE === PAGE_GET && <GetWin onClose={this.onCloseAll}/> )}
				{(this.state.CURRENT_PAGE === PAGE_EDITINFO && <EditInfoTextWin onClose={this.onCloseAll}/> )}
				{(this.state.CURRENT_PAGE === PAGE_SETTINGS && <SettingsWin onClose={this.onCloseAll}/> )}
				{(this.state.CURRENT_PAGE === PAGE_MAIL && <MailWin onClose={this.onCloseAll}/> )}
				{(this.state.CURRENT_PAGE === PAGE_INFO && <InfoWin onClose={this.onCloseAll} goChangePass={this.goOpenChangePass} goAddPhone={this.goAddPhone}/> )}
				{(this.state.CURRENT_PAGE === PAGE_NPASS && <NewPassWin onClose={this.onCloseAll}/> )}
				{(this.state.CURRENT_PAGE === PAGE_BONUS && <BonusWin onClose={this.onCloseAll}/> )}
				{(this.state.CURRENT_PAGE === PAGE_REG && <RegWin onClose={this.onCloseAll}/> )}
				{(this.state.CURRENT_PAGE === PAGE_FREE_PLAY && <FreePlayWin onClose={this.onCloseAll}/> )}
				{(this.state.CURRENT_PAGE === PAGE_LOGIN && <LoginWin onClose={this.onCloseAll}/> )}
				{(this.state.CURRENT_PAGE === SHOW_LUCKY && <LuckyWin onClose={this.onCloseAll}/> )}
				{(this.state.CURRENT_PAGE === PAGE_CASHBACK && <CashbackWin amount={this.state.data} onClose={this.onCloseAll}/> )}
				{(this.state.CURRENT_PAGE === PAGE_SIGNPHONE && <SignPhoneWin onClose={this.onCloseAll}/> )}
				{(this.state.CURRENT_PAGE === PAGE_REGINFO && <RegInfoWin onClose={this.onCloseAll}/> )}
				{(this.state.CURRENT_PAGE === PAGE_MESSAGE && <MessageWin text={this.state.data} onClose={this.onCloseAll}/> )}
			</div>
		)
	}
}

export default TopWindows;
