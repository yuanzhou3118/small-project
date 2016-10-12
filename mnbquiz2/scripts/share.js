$(function() {
	var openid, utm_source;
	if (gqs('openid')) {
		openid = gqs('openid');
	} else {
		openid = '';
	}
	if (gqs('utm_source')) {
		utm_source = gqs('utm_source');
	} else {
		utm_source = '';
	}
	$.ajax({
		url: 'api/index.php',
		type: 'POST',
		dataType: 'json',
		data: {
			action: 'create_user',
			openid: openid,
			utm_source: utm_source
		},
		success: function(data) {
			var result = data.result;
			if (result >= 2 && result <= 5) {
				showResult(data.quiz_answer4);
			}
		}
	});
	function showResult(a) {
		var title = new Image(),
			picture = new Image();
		title.src = 'assets/result/rt' + a + '.png';
		picture.src = 'assets/result/r' + a + '.png';
		title.onload = function() {
			$('.title').append(title);
		}
		picture.onload = function() {
			$('.picture').append(picture);
		}
	}
	function gqs(name) {
		var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i"),
			r = window.location.search.substr(1).match(reg);
		if (r != null) return decodeURIComponent(r[2]);
		return null;
	}
});