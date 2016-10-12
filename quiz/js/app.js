var image = new Image();
image.src = 'img/p/bg.jpg';
image.onload = function() {
	$('.mask').hide();
}

var isOpenid1 = getQueryString('openid');
if (isOpenid1 == 'o6M7ojuyyLBKcIa7P85i8xdSYr6U') {
	$('.x').show();
}

$(function() {
	
	FastClick.attach(document.body);
	
	var count = 60,
		phoneReg = /^1[34578]\d{9}$/,
		hash;
	
	$('.confirm').on('click', function() {
		$('.table').hide();
	});
	
	$('.start').on('click', function() {
		$('.q').show();
		$('.tip1').show();
	});
	
	var infors = $('input');
	for (var i = 0, len = infors.length; i < len; i++) {
		infors[i].oninput = function() {
			if (this.value.length) {
				this.parentNode.style.backgroundImage = 'none';
			} else {
				this.parentNode.style.backgroundImage = 'url(img/w/' + this.parentNode.className + '.png)';
			}
		}
	}
	
	var openid = getQueryString('openid');
	
	if (openid == null) {
		openid = '';
	}
	
	$('.sh').on('click', function() {
		$('.l').show();
	});
	
	$('.se').on('click', function() {
		$('.share_float').show();
	});
	
	$('.share_float').on('click', function() {
		$(this).hide();
	})
	
	$('.getCode').on('click', getCode);
	
	$('.submit-info').on('click', submitInfor);
	
	$('.again').on('click', function() {
		window.location.reload();
	});
	
	$('.iwant span').on('click', function() {
		$('.x').show();
	});
	
	var url = 'api/index.php';
	var content = '<span style="position:absolute;top:0;right:0;width:2.02rem;height:0.9rem;z-index:9;background:transparent;"></span>';
	
	function getCode() {
		count = 60;
		$('.code').append(content);
		if (!phoneReg.test(infors[0].value)) {
			alert('手机号码不正确');
		} else {
			$.ajax({
				url: url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'get_captcha',
					mobile: infors[0].value,
					openid: openid
				},
				success: function(done) {
					var result = done.result;
					if (result == 1) {
						alert('发送成功');
						countDown();
					} else {
						alert('发送验证码失败');
					}
				},
				error: function() {
					alert('Error');
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
	
	function submitInfor() {
		$.ajax({
			url: url,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'update_user',
				mobile: infors[0].value,
				user_name: infors[2].value,
				address: infors[3].value,
				openid: openid
			},
			success: function(done) {
				var result = done.result;
				if (result == 1) {
					$('.c').hide();
					$('.s').show();
				} else if (result == 2) {
					alert('提交失败');
				} else {
					alert('失败');
				}
			},
			error: function() {
				alert('Error');
			}
		});
	}
	
});

function getQueryString(name) {
	var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
	var r = window.location.search.substr(1).match(reg);
	if (r != null) return unescape(r[2]);
	return null;
}