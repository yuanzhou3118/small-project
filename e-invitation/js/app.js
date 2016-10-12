var loadFiles = {
	files: ['img/p1/confirm.png', 'img/p1/start.png', 'img/p1/bg.jpg', 'img/p1/tip.png'],
	handleComplete: function() {
		$('.mask').hide();
	}
}
var queue = new createjs.LoadQueue(false);
queue.on('complete', loadFiles.handleComplete);
queue.loadManifest(loadFiles.files);

$(function() {
	
	FastClick.attach(document.body);
	
	var phoneReg = /^1[34578]\d{9}$/,
		codeReg = /\d{4}/,
		count = 60;
		
	var return_city;
	
	$('.confirm').on('click', function(){
		$('.table').hide();
	});
	
	var infors = $('#form')[0].elements;
	for (var i = 0, len = infors.length; i < len; i++) {
		infors[i].oninput = function() {
			if (this.value.length) {
				this.parentNode.style.backgroundImage = 'none';
			} else {
				this.parentNode.style.backgroundImage = 'url(img/p2/' + this.parentNode.className + '.png)';
			}
		}
	}
	
	$('.getCode').on('click', getCode);
	
	$('.submit').on('click', submit);
	
	$('.location').on('click', function() {
		$('.map')[0].style.display = 'table';
	});
	
	$('.close').on('click', function() {
		$('.map').hide();
	});
	
	var openid = getQueryString('openid'); // wcy1ecdnxmbobopwvrds5me38p8wy9or
	
	if (openid == null) {
		openid = '';
	}
	
	function getCode() {
		count = 60;
		if (!phoneReg.test(infors[2].value)) {
			alert('手机号码不正确');
		} else {
			$.ajax({
				url: 'api/index.php',
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'get_captcha',
					mobile: infors[2].value,
					openid: openid
				},
				success: function(done) {
					var result = done.result;
					if (result == 1) {
						alert('发送成功');
						$('.code').append('<span style="position:absolute;top:0;right:0;width:2.02rem;height:0.9rem;z-index:9;background:transparent;"></span>');
						countDown();
					} else {
						alert('发送验证码失败');
					}
				}
			});
		}
	}
	
	function countDown() {
		$('.getCode').text(count + 's后重发');
		count--;
		if (count == -1 ) {
			$('.getCode').text('获取验证码');
			$('.code span').eq(1).remove();
			return;
		}
		setTimeout(countDown, 1000);
	}
	
	function submit() {
		var msg = {
			name: infors[1].value,
			mobile: infors[2].value,
			captcha: infors[3].value,
		}
		if (!phoneReg.test(msg.mobile)) {
			alert('手机号码不正确');
		} else if (!codeReg.test(msg.captcha)) {
			alert('验证码错误');
		} else {
			$.ajax({
				url: 'api/index.php',
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'add_user',
					user_name: msg.name,
					mobile: msg.mobile,
					captcha: msg.captcha,
					openid: openid
				},
				success: function(done) {
					var result = done.result,
						auth_code = done.auth_code;
					if (result == 1 || result == 3) {
						console.log(auth_code);
						$('.success').show();
					} else if (result == 2) {
						alert('已参加过');
					} else if (result == 4) {
						alert('验证码错误');
					} else if (result == 6) {
						alert('未中奖');
					} else {
						console.log('Failed.');
					}
				}
			})
		}
	}
	
	function getQueryString(name) {
		var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
		var r = window.location.search.substr(1).match(reg);
		if (r != null) return unescape(r[2]);
		return null;
	}
});