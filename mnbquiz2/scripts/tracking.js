window.onload = function() {
	// GA code
	(function(i, s, o, g, r, a, m) {
		i['GoogleAnalyticsObject'] = r;
		i[r] = i[r] || function() {
			(i[r].q = i[r].q || []).push(arguments)
		}, i[r].l = 1 * new Date();
		a = s.createElement(o),
			m = s.getElementsByTagName(o)[0];
		a.async = 1;
		a.src = g;
		m.parentNode.insertBefore(a, m)
	})(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');
	ga('create', 'UA-25338682-49', 'auto');
	ga('send', 'pageview');
	// tracking
	$$('.confirm').addEventListener('click', function() {
		tracking('Age-gate-Yes');
	}, false);
	$$('.no').addEventListener('click', function() {
		tracking('Age-gate-No');
	}, false);
	$$('.start').addEventListener('click', function() {
		tracking('Start');
	}, false);
	$$('.next').addEventListener('click', function() {
		var number = +$$('.number').innerHTML;
		switch(number) {
			case 1:
				tracking('Next-2');
				break;
			case 2:
				tracking('Next-3');
				break;
			case 3:
				tracking('Next-4');
				break;
			case 4:
				tracking('Next-5');
				break;
			case 5:
				tracking('Next-6');
				break;
			case 6:
				tracking('Next-7');
				break;
			case 7:
				tracking('Next-8');
				break;
			case 8:
				tracking('Next-9');
				break;
			case 9:
				tracking('Next-10');
				break;
			case 10:
				tracking('Result');
				break;
			default:
				break;
		}
	}, false);
	$$('#result .lottery').addEventListener('click', function() {
		var result = +$$('#result .title img').src.match(/rt\d(.png)/g)[0].match(/\d/g)[0];
		switch(result) {
			case 1:
				tracking('Result-1-Profile');
				break;
			case 2:
				tracking('Result-2-Profile');
				break;
			case 3:
				tracking('Result-3-Profile');
				break;
			case 4:
				tracking('Result-4-Profile');
				break;
			case 5:
				tracking('Result-5-Profile');
				break;
			default:
				break;
		}
	}, false);
	$$('#result .share').addEventListener('click', function() {
		var result = +$$('#result .title img').src.match(/rt\d(.png)/g)[0].match(/\d/g)[0];
		switch(result) {
			case 1:
				tracking('Result-1-Sharing');
				break;
			case 2:
				tracking('Result-2-Sharing');
				break;
			case 3:
				tracking('Result-3-Sharing');
				break;
			case 4:
				tracking('Result-4-Sharing');
				break;
			case 5:
				tracking('Result-5-Sharing');
				break;
			default:
				break;
		}
	}, false);
	$$('.check').addEventListener('click', function() {
		if (this.checked) {
			tracking('Profile-SMS-Code');
		}
	}, false);
	$$('#submit .lottery').addEventListener('click', function() {
		tracking('Profile-LuckyDraw');
	}, false);
	$$('.submit').addEventListener('click', function() {
		tracking('Profile-Submit');
	}, false);
	$$('.getCoupon').addEventListener('click', function() {
		tracking('Profile-Coupon');
	}, false);
	function tracking(action) {
		ga('send', 'event', 'Quiz-2', action);
	}
	function $$(id) {
		return document.querySelector(id);
	}
}