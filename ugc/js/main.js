require.config({
	baseUrl: 'js/lib/',
	paths: {
		fastclick: 'fastclick.min',
		preload: 'preloadjs.min',
		swiper: 'idangerous.swiper.min',
		wx: 'jweixin-1.0.0',
		$: 'zepto.min',
	}
});
require(['require', '../lib', 'shake', '../pload', '../share'], function(require, L, S, P) {
	var UGC = new L.Lib(), hu, nn, openid;
	// console.log(S);
	$(function() {
		var FastClick = require('fastclick');
		FastClick.attach(document.body);
		hu = UGC.getQueryString('headUrl');
		nn = UGC.getQueryString('nickname');
		openid = UGC.getQueryString('openid');
		if (hu == null) hu = '';
		if (nn == null) nn = '';
		if (openid == null) openid = '';
		// console.log(hu, nn, openid);
		$('.skeleton').append('<img src="' + hu + '">');
		$('.nickname').text(nn);
		
		var headUrls = $('.list .td2'),
			nicknames = $('.list .td3 span'),
			scores = $('.list .td4');
			
		var users = [
			{head_url: headUrls.eq(0), nickname: nicknames.eq(0), score: scores.eq(0)},
			{head_url: headUrls.eq(1), nickname: nicknames.eq(1), score: scores.eq(1)},
			{head_url: headUrls.eq(2), nickname: nicknames.eq(2), score: scores.eq(2)},
			{head_url: headUrls.eq(3), nickname: nicknames.eq(3), score: scores.eq(3)},
			{head_url: headUrls.eq(4), nickname: nicknames.eq(4), score: scores.eq(4)}
		];
		
		UGC.submitCB({
			action: 'score_list',
			openid: openid
		}, function(rank) {
			UGC.rank(rank, users, hu);
		});
		
	});
	// variables
	var st, timer, wt = 1, r = 1, phoneReg = /^1[34578]\d{9}$/, codeReg = /\d{4}/, content = '<span style="position:absolute;top:0;right:0;width:2.02rem;height:0.9rem;z-index:9;background:transparent;"></span>';
	// p1
	$('.confirm').on('click', function() {
		$('.ageTip').hide();
	});
	
	$('.cp').on('click', function() {
		$('.start').hide();
	});
	
	$('.checkRank').on('click', function() {
		$('.container').hide();
	});
	// p2
	$('.fp')[0].addEventListener('touchstart', function(event) {
		event.preventDefault();
		st = new Date();
		// console.log(st);
		timer = setTimeout(function() {
			$('.verify').hide();
			$('.sn').on('click', showImprove);
			UGC.submitCB({
				action: 'create_user',
				head_url: hu,
				nickname: nn,
				openid: openid
			}, function(done) {
				var result = done.result;
				if (result == 0) {
					alert('Error');
				} else {
					$('.improve .score').text(done.score);
				}
			});
		}, 600);
		return false;
	}, false);
	
	$('.fp')[0].addEventListener('touchend', function() {
		// console.log(new Date() - st);
		if ((new Date() - st) < 600) {
			clearTimeout(timer);
		}
	}, false);
	// p3
	var mySwiper = new Swiper('.swiper-container', {
		loop: false,
		onSlideChangeEnd: function(swiper) {
			wt = swiper.activeIndex + 1;
			switch (wt) {
				case 1:
					$('.left').addClass('op');
					$('.right').removeClass('op');
					break;
				case 2:
					$('.left').removeClass('op');
					$('.right').removeClass('op');
					break;
				case 3:
					$('.left').removeClass('op');
					$('.right').addClass('op');
					break;
				default:
					break;
			}
		}
	});
	
	$('.left').on('click', function() {
		mySwiper.swipePrev();
	});
	
	$('.right').on('click', function() {
		mySwiper.swipeNext();
	});
	
	function showImprove() {
		if(P.isLoad._is) {
			$('.theme').hide();
			$('.improve .text').append(P.th[wt-1]);
			$('.improve .inner').css('backgroundImage', 'url(' + P.bg[wt-1].src + ')');
			$('.improve .scene').append(P.ts[wt-1][0]);
			setTimeout(function() {
				$('.shake_tip').hide();
			}, 1200);
		}
	}
	
	$('.shake_tip').click(function() {
		$(this).hide();
	});
	// p4
	var myShakeEvent = new S({
		threshold: 8,
		timeout: 600
	});
	myShakeEvent.start();
	window.addEventListener('shake', shakeEventDidOccur, false);
	function shakeEventDidOccur() {
		r = parseInt(Math.random() * 3) + 1;
		$('.improve .scene').children('img').remove();
		$('.improve .scene').append(P.ts[wt-1][r-1]);
	}
	
	$('.spr').on('click', function() {
		if ($(this).children().attr('src').match(/(sp\.png)/g)) {
			$('.share_float').show();
			window.removeEventListener('shake', shakeEventDidOccur, false);
			myShakeEvent.stop();
			UGC.submitCB({
				action: 'add_topic',
				topic_id: wt + '-' + r,
				openid: openid
			});
		} else {
			$('.improve').hide();
			UGC.submitCB({
				action: 'score_list',
				openid: openid
			}, function(rank) {
				UGC.rank(rank, users, hu);
			});
		}
	});
	
	$('.share_float').on('click', function() {
		$(this).hide();
	});
	// p6
	$('.gc').on('click', function() {
		$('.rank').hide();
		UGC.submitCB({
			action: 'mobile_status',
			openid: openid
		}, function(done) {
			var result = done.result;
			if (result == 1) {
				$('.submit').show();
			} else if (result == 2) {
				$('.fail').show();
			}
		});
	});
	
	$('.sp').on('click', function() {
		$('.share_float').show();
	});
	// p7
	var elements = $('#submit-tel input'), i = 0;
	for(; i < 2; i++) {
		elements[i].oninput = function() {
			var parent = this.parentNode;
			if(this.value.length) {
				parent.style.backgroundImage = 'none';
			} else {
				parent.style.backgroundImage = 'url(assets/p7/' + parent.className + '.png)';
			}
		}
	}
	
	$('.getCode').on('click', function() {
		if (phoneReg.test(elements[0].value)) {
			$('.code').append(content);
			UGC.getCode({
				action: 'get_captcha',
				mobile: elements[0].value,
				openid: openid
			});
		} else {
			alert('请填写正确的手机号码');
		}
	});
	
	$('.submit-tel').on('click', function() {
		if (codeReg.test(elements[1].value)) {
			UGC.submitCB({
				action: 'add_user',
				mobile: elements[0].value,
				captcha: elements[1].value,
				openid: openid
			}, function(done) {
				var result = done.result;
				$('.submit').hide();
				if (result == 1) {
					// $('.gn_link').attr('href', done.href);
					$('.success').show();
				} else if (result == 3 || result == 2) {
					$('.fail').show();
				}
			});
		} else {
			alert('请填写正确的验证码');
		}
	});
	
	$('.fail .pa').on('click', function() {
		window.location.reload();
	});	
});