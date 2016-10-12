define(['./fn'], function(fn) {
	
	var Quiz = new fn.Quiz(),
		openid;
	
	console.log('version: ' + Quiz.version);
	
	if (Quiz.gqs('unionid')) {
		openid = Quiz.gqs('unionid');
	}
	
	var $lottery = $('#result .title'),
		$picture = $('#result .picture');
	// index
	$('.detail').on('click', function() {
		$('.detail_float').show();
	});
	$('.d_close').on('click', function() {
		$('.detail_float').hide();
	});
	// age
	$('.confirm').on('click', function() {
		$('#age').hide();
	});
	// result
	$('#result .lottery').on('click', function() {
		$('#submit').show();
		$('#result').hide();
	});
	$('.share').on('click', function() {
		$('.share_float').show();
	});
	$('.share_float').on('click', function() {
		$(this).hide();
	});
	// submit
	var phoneReg = /^1[34578]\d{9}$/;
	
	$('.captcha')[0].oninput = function() {
		if (this.value != '') {
			this.style.backgroundImage = 'none';
		} else {
			this.style.backgroundImage = 'url(assets/submit/captcha.png)';
		}
	}
	var $send = $('.send'),
		count = 60,
		isCheck,
		mobile,
		captcha,
		name,
		address;
	// captcha
	$('.check').on('click', function() {
		mobile = $('.mobile').val();
		if (!phoneReg.test(mobile)) {
			alert('请填写正确的手机号码');
			$(this)[0].checked = false;
		} else {
			isCheck = $(this)[0].checked;
			if (isCheck) {
				count = 60;
				$(this).attr('disabled', 'disabled');
				Quiz.ajax({
					action: 'get_captcha',
					openid: openid,
					mobile: mobile
				}, function(data) {
					if (data.result == 1) {
						countDown();
					} else {
						alert('Error');
					}
				});
			}
		}
	});
	// info
	$('#info .submit').on('click', function() {
		name = $('.name').val();
		address = $('.address').val();
		if (name == '') {
			alert('请填写姓名');
		}
		if (address == '') {
			alert('请填写地址');
		}
		if (name != '' && address != '') {
			Quiz.ajax({
				action: 'update_user',
				openid: openid,
				user_name: name,
				address: address
			}, function(data) {
				var result = data.result;
				$('#hint').show();
				$('#info').hide();
				if (result == 4) {
					Quiz.fail();
				} else if (result == 1) {
					Quiz.success();
				} else {
					alert('Error');
				}
			});
		}
	});
	// functions
	function countDown() {
		if (count == 0) {
			$send.text('获取验证码');
			$('.check').removeAttr('disabled');
			$('.check')[0].checked = false;
			return;
		}
		$send.text(count + '秒后获取');
		count--;
		var timer = setTimeout(countDown, 1000);
	}
	
});