var isOpenid2 = getQueryString('openid');
if (isOpenid2 == 'o6M7ojuyyLBKcIa7P85i8xdSYr6U') {
	$('.x').show();
}

$(function() {
	
	var DISTANCE = 500,
		check_choice,
		user_occasion,
		user_frequency,
		phoneReg = /^1[34578]\d{9}$/,
		codeReg = /\d{4}/;

	function Animate(obj, ques) {
		this.obj = obj;
		this.ques = ques;
	}
	
	var dots = $('.dot').children('li');
	
	var shareMessage = {
		title: '发现你的欧洲杯主场派对最夯装备',
		decs0: '作为行走的荷尔蒙，TA最适合西西里的美丽派对了！',
		decs1: '圣丹尼法式锋潮派对独属于最具艺术气质的TA！',
		decs2: '颜值与实力并存的日耳曼战车主题派对是TA的真爱！',
		decs3: 'TA最适合在斗牛士风情派对上与热情狂野的西班牙人共舞！',
		link: window.location.origin + window.location.pathname
	}
	
	var infors = $('.l input'),
		openid = getQueryString('openid');
		
	if (openid == null) {
		openid = '';
	}
	
	// setTimeout(function() {
		$.ajax({
			url: 'http://www.dangdaimingshi.com/ddmswechat/get_signature.php?signurl='+encodeURIComponent(window.location.href),
			type: 'GET',
			success: function(data){
				var jdata = $.parseJSON(data);
				if(jdata.appId){
					var conObj = {
						// debug: true,
						appId : jdata.appId,
						timestamp : jdata.timestamp,
						nonceStr : jdata.nonceStr,
						signature : jdata.signature,
						jsApiList : ['onMenuShareTimeline','onMenuShareAppMessage']
					}
					wx.config(conObj);
				}
			}
		});
		wx.ready(function(){
			wx.onMenuShareTimeline({
				title: '欧洲杯主场派对即刻开嗨，赶快和我一起寻找最夯装备！',
				link: shareMessage.link + 'qrcode.html',
				imgUrl: shareMessage.link + 'img/share-ico.jpg'
			});
			wx.onMenuShareAppMessage({
				title: '发现你的欧洲杯主场派对最夯装备',
				desc: '欧洲杯主场派对即刻开嗨，赶快和我一起寻找最夯装备！',
				link: shareMessage.link + 'qrcode.html',
				imgUrl: shareMessage.link + 'img/share-ico.jpg'
			});
		});
	// }, 3000);
	
	Animate.prototype.outAnswer = function() {
		var o = this.obj,
			q = this.ques;
		
		for (var i = 0; i < 4; i++) {
			o[i].save = i;
			o[i].onclick = function() {
				
				if (q < 6) {
					for (var k = 0; k < 6; k++) {
						dots.eq(k).removeClass('active');
						dots.eq(k).css('background', 'rgba(255, 255, 255, .3)');
					}
					dots.eq(q).addClass('active');
				}
				
				if (q == 4) {
					check_choice = this.save;
					$('.share').attr('href', '#share' + check_choice);
					chooseShare(check_choice);
				} else if (q == 3) {
					user_occasion = this.save + 1;
				} else if (q == 5) {
					user_frequency = this.save + 1;
				}
				
				if (this.save % 2 == 0) {
					if (q < 6) {
						move('.q' + q + this.save).delay('0.5s').end(function() {
							move('#q-' + (q + 1)).set('opacity', 1).end(function() {
								$('#q-' + q).css('display', 'none');
							});
						});
					} else {
						move('.q' + q + this.save).delay('0.5s').end(function() {
							$('#q-' + q).css('display', 'none');
							checkChoice(check_choice);
							addCompagain();
							$('.r').show();
						});
					}
					move('.q' + q).translate(DISTANCE, 0).delay('0.5s').end();
				} else {
					if (q < 6) {
						move('.q' + q + this.save).translate(-DISTANCE, 0).delay('0.5s').end(function() {
							move('#q-' + (q + 1)).set('opacity', 1).end(function() {
								$('#q-' + q).css('display', 'none');
							});
						});
					} else {
						move('.q' + q + this.save).translate(-DISTANCE, 0).delay('0.5s').end(function() {
							$('#q-' + q).css('display', 'none');
							checkChoice(check_choice);
							addCompagain();
							$('.r').show();
						});
					}
					move('.q' + q).translate(-DISTANCE, 0).delay('0.5s').end();
				}
				for (var j = 0; j < 4; j++) {
					if (j % 2 == 0) {
						move('.q' + q + j).translate(DISTANCE, 0).duration(600).end();
					} else {
						move('.q' + q + j).translate(-DISTANCE, 0).duration(600).end();
					}
				}
			}
		}
	}

	var q1 = new Animate($('#q-1 .answer li'), 1);
	q1.outAnswer();

	var q2 = new Animate($('#q-2 .answer li'), 2);
	q2.outAnswer();

	var q3 = new Animate($('#q-3 .answer li'), 3);
	q3.outAnswer();

	var q4 = new Animate($('#q-4 .answer li'), 4);
	q4.outAnswer();

	var q5 = new Animate($('#q-5 .answer li'), 5);
	q5.outAnswer();

	var q6 = new Animate($('#q-6 .answer li'), 6);
	q6.outAnswer();

	$('.submit-tel').on('click', isChance);
	
	function isChance() {
		if (!phoneReg.test(infors[0].value)) {
			alert('手机号码不正确');
		} else if (!codeReg.test(infors[1].value)) {
			alert('验证码错误');
		} else {
			$.ajax({
				url: 'api/index.php',
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'add_user',
					user_occasion: user_occasion,
					user_frequency: user_frequency,
					mobile: infors[0].value,
					captcha: infors[1].value,
					openid: openid
				},
				success: function(done) {
					var result = done.result;
					if (result == 1 || result == 5) {
						$('.w').hide();
						$('.c').show();
					} else if (result == 2) {
						$('.w').hide();
						$('.c form').hide();
						$('.c img').css('margin-top', '2rem');
						$('.c').show();
					} else if (result == 3) {
						$('.w').hide();
						$('.f').show();
					} else {
						alert('验证码不对');
					}
				},
				error: function() {
					alert('Error');
				}
			});
		}
	}

	function checkChoice(c) {
		switch (c) {
			case 0:
				$('.r1').show();
				break;
			case 1:
				$('.r2').show();
				break;
			case 2:
				$('.r3').show();
				break;
			case 3:
				$('.r4').show();
				break;
			default:
				break;
		}
	}
	
	function addCompagain() {
		$.ajax({
			url: 'api/index.php',
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'add_campaign',
				user_occasion: user_occasion,
				user_frequency: user_frequency,
				openid: openid
			}
		})
	}
	
	function wxshare(des, hash) {
		// wx.ready(function(){
			wx.onMenuShareTimeline({
				title: des,
				link: shareMessage.link + 'share.html#share' + hash,
				imgUrl: shareMessage.link + 'img/share-ico.jpg'
			});
			wx.onMenuShareAppMessage({
				title: shareMessage.title,
				desc: des,
				link: shareMessage.link + 'share.html#share' + hash,
				imgUrl: shareMessage.link + 'img/share-ico.jpg'
			});
		// });
	}
	
	function chooseShare(num) {
		switch (num) {
			case 0:
				wxshare(shareMessage.decs0, num);
				break;
			case 1:
				wxshare(shareMessage.decs1, num);
				break;
			case 2:
				wxshare(shareMessage.decs2, num);
				break;
			case 3:
				wxshare(shareMessage.decs3, num);
				break;
			default:
				break;
		}
	}
	
});