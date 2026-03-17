$(document).ready(function() {
	$('.overlay').click(function () {
		$(this).fadeOut();
		$('.popup').fadeOut();
		$("body").css("overflow", "auto")
	});

	// $('.hint-overlay').click(function () {
	// 	$(this).fadeOut();
	// });

	$('.popup__btn--footer').click(function () {
		$('.overlay').fadeOut();
		$('.popup').fadeOut();
		$("body").css("overflow", "auto");
	});

	$('.hint__btn').click(function () {
		event.preventDefault();
		$('.hint-overlay').fadeOut();
		$('.hint').hide();
	});

	$('.open').click(function() {
		event.preventDefault();
		$('.overlay').fadeIn();
		$('.popup').fadeIn();
		// $("body").css("overflow", "hidden")
	});

	$.fn.datepicker;

	$(".profile__field-input--date").datepicker({
		startView: 2
	});
	
	$(".history__field-input").datepicker({
		startView: 2
	});

	$('.popup__menu').on('click', '.popup__link:not(.active)', function (event) {
		event.preventDefault();
		$(this) 
			.addClass('active').siblings().removeClass('active')
			.closest('.popup__body').find('.popup__cont').removeClass('active')
			.eq($(this).index()).addClass('active');
	});

	$('.table__help--front').click(function() {
		$('.hint-overlay').fadeIn();
		$('.hint--front').show();
	});

	$('.table__help--back').click(function() {
		$('.hint-overlay').fadeIn();
		$('.hint--back').show();
	});

	$('.history__name').click(function() {
		$(this).closest('.history__item').find('.history__cont').slideToggle();
		$(this).closest('.history__item').toggleClass('active');
		$(this).parent().siblings('.history__item').removeClass('active');
		$(this).parent().siblings().children().next().slideUp();
	});;

	$('.deposit__box').click(function() {
		$(this).closest('.deposit__item').find('.deposit__cont').slideToggle();
		$(this).closest('.deposit__item').toggleClass('active');
		$(this).parent().siblings('.deposit__item').removeClass('active');
		$(this).parent().siblings().children().next().slideUp();
	});

	$('.deposit__form').card({
		container: '.card__wrap',
		formSelectors: {
				numberInput:'input[name="number"]',
				cvcInput:'input[name="cvc"]',
				expiryInput:'input[name="expiry"]',
				nameInput : 'input[name = "first_name"], input[name = "last_name"]' 
		}
	});
	// const swiper = new Swiper('.line', {
	// 	slidesPerView: 'auto',
	// 	spaceBetween: 30,
    //     autoplay: {
    //       delay: 0,
    //       disableOnInteraction: false,
    //     },
	// 	speed: 5000,
	// });
	$('.line__inner').liMarquee();
});

const swiper = new Swiper('.slider', {
	loop: true,
	autoplay: {
		delay: 5500,
		disableOnInteraction: false,
	},
	speed: 700,
	pagination: {
		el: '.swiper-pagination',
		clickable: true,
	  },
	
	  navigation: {
		nextEl: '.slider__next',
		prevEl: '.slider__prev',
	  },
	
});
