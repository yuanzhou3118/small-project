define(['$', 'swiper', 'fastclick'], function() {
	
	var count = 60,
		$gc = $('.getCode'),
		$cs = $('.code span');
	
	var Lib = function(argus) {
		this._argus = $.extend({}, Lib.defaults, argus);
	}, p = Lib.prototype;
	
	Lib.defaults = {
		url: 'api/index.php',
		type: 'POST'
	}
	
	p.getQueryString = function(name) {
		var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i"),
			r = window.location.search.substr(1).match(reg);
		if (r != null) return decodeURIComponent(r[2]);
		return null;
	}

	p.submitCB = function(datas, fn) {
		var _argus = this._argus;
		$.ajax({
			url: _argus.url,
			type: _argus.type,
			dataType: 'json',
			data: datas,
			success: function(done) {
				fn && fn(done);
			},
			error: function(fail) {
				console.log('Error');
			}
		});
	}
	
	p.rank = function(rank, arr, isYou) {
		if (rank.data) {
			var data = rank.data,
				top = data.length;
			for (var i = 0; i < top; i++) {
				if (data[i].head_url == isYou) {
					arr[i].head_url.parent().addClass('you');
				}
				arr[i].head_url.append('<img src="' + data[i].head_url + '">');
				arr[i].nickname.text(data[i].nickname);
				arr[i].score.text(data[i].score);
			}
		}
	}
	
	p.countDown = function() {
		$gc.text(count + 's后重发');
		count--;
		if (count == -1 ) {
			$gc.text('获取验证码');
			$cs.eq(1).remove();
			return;
		}
		setTimeout(p.countDown, 1000);
	}
	
	p.getCode = function(datas) {
		var _this = this,
			_argus = this._argus;
		count = 60;
		$.ajax({
			url: _argus.url,
			type: _argus.type,
			dataType: 'json',
			data: datas,
			success: function(done) {
				var result = done.result;
				alert('发送成功');
				_this.countDown();
			},
			error: function(fail) {
				console.log('Error');
			}
		});
	}
	
	return {
		Lib: Lib
	}
	
});