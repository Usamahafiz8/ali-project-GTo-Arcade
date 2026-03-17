import React from 'react';
import { EE } from '../App';
import '../css/bonus.css';
import {PAGE_SIZE_DEFAULT} from "../common/Config";
import $ from 'jquery';
import {gsap, Back} from "gsap";
let SELECTED_CARD = 0;
let CARDS_MONEY = {
	1: 10,
	2: 44,
	3: 100,
	4: 12,
	5: 90
};
export class BonusWin extends React.Component {
	constructor(props) {
		super(props);
		this.getBonusCards = this.getBonusCards.bind(this);
		this.onClose = this.onClose.bind(this);
		EE.addListener("CLOSE_BONUS", this.onClose);
	}

	onClose() {
		this.props.onClose();
	}

	componentDidMount() {
		SELECTED_CARD = 0;
		EE.addListener("RESIZE", this.onResize);
		EE.emit("FORCE_RESIZE");
	}

	onResize(data) {
		const cont = document.getElementsByClassName("modal-window-bonus__scale-cont")[0];
		const sc = Math.min(data.h/PAGE_SIZE_DEFAULT.height, data.w/PAGE_SIZE_DEFAULT.width);
		if(cont) {
			cont.style.transform = `scale(${sc})`;
		}
	}

	getBonusCards() {
		return([1,2,3,4,5].map((item, index) => {
			return(
				<BonusCard card={item} key={index} />
			)
		}));
	}

	render () {
		return (
			<div className="modal-window-bonus">
				<div className="modal-window-bonus__scale-cont">
					<div className="modal-window-bonus__cards">
						{this.getBonusCards()}
					</div>
					<img className="modal-window-bonus__close game-button" onClick={this.onClose} src="/images/fish/popup/close_btn.png" alt=""/>
					<span className="modal-window-bonus__t1 modal-window-bonus__text">{"Note:"}</span>
					<span className="modal-window-bonus__t2 modal-window-bonus__text">{"PICK A FISH TO REVEAL A RANDOM WINning amount, reward will be added when"}</span>
					<span className="modal-window-bonus__t3 modal-window-bonus__text">{"your balance goes below $3. reward will be revoked if you redeem before balance"}</span>
					<span className="modal-window-bonus__t4 modal-window-bonus__text">{"goes to $3"}</span>
					<img className="modal-window-bonus__back" src="/images/fish/popup/bonus_back.png" alt=""/>
				</div>
			</div>
		)
	}
}


class BonusCard extends React.Component {
	constructor(props) {
		super(props);
		this.animationCard = this.animationCard.bind(this);
		EE.addListener('NO_BONUS_CARD_ANIMATION', this.animationCard);
	}

	animationCard(win) {
		$('.bonus__card-wrapper').css('pointer-events', 'none');
		EE.removeListener('NO_BONUS_CARD_ANIMATION', this.animationCard);
		if(SELECTED_CARD===this.props.card) return;
		//
		$("#"+`bonus__card${this.props.card}-money`).text("+"+CARDS_MONEY[this.props.card]);
		gsap.to($("#"+`bonus__card${this.props.card}-body-cont`), {duration: 1.2, rotationY:180, ease:Back.easeOut});
		gsap.to($("#"+`bonus__card${this.props.card}`), {duration: 0.5, scale:1.2, repeat:1, yoyo:true, stagger: 0.1});
		if(win) {
			$("#"+`bonus__card${this.props.card}-win`).css('opacity', 1);
			$("#"+`bonus__card${this.props.card}-lose`).css('opacity', 0);
		} else {
			$("#"+`bonus__card${this.props.card}-money`).css('color', '#999999');
			$("#"+`bonus__card${this.props.card}-win`).css('opacity', 0);
			$("#"+`bonus__card${this.props.card}-lose`).css('opacity', 1);
		}
	}

	render () {
		return (
			<div id={`bonus__card${this.props.card}`} className="bonus__card-wrapper" onClick={()=>{
				if(SELECTED_CARD>0) return;
				this.animationCard(true);
				SELECTED_CARD = this.props.card;
				setTimeout(()=>{
					EE.emit('NO_BONUS_CARD_ANIMATION');
				},Math.random()*3000);
				/*setTimeout(()=>{
					EE.emit('CLOSE_BONUS');
				},7000);*/
			}}>
				<div className="bonus__card-body" id={`bonus__card${this.props.card}-body-cont`}>
					<div className="bonus-card-face bonus-card-front">
						<img id={`bonus__card${this.props.card}-pic`} className="bonus__card" src={`/images/fish/popup/bonus_card${this.props.card}.png`} alt=""/>
					</div>
					<div className="bonus-card-face bonus-card-back">
						<span id={`bonus__card${this.props.card}-money`} className="bonus__card-text">{"+99"}</span>
						<img id={`bonus__card${this.props.card}-win`} className="bonus__card" src="/images/fish/popup/bonus_card.png" alt=""/>
						<img id={`bonus__card${this.props.card}-lose`} className="bonus__card" src="/images/fish/popup/bonus_empty.png" alt=""/>
					</div>
				</div>
			</div>
		)
	}

}