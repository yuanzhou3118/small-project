define(['wx', '$'], function(wx) {
	
	$(function() {
		
		var shareData = {
			title: '这是标题这是标题这是标题',
			desc: '这是描述这是描述这是描述这是描述这是描述这是描述',
			link: window.location.origin + window.location.pathname + 'share.html?openid=' + getQueryString('openid'),
			imgUrl: 'http://ww2.sinaimg.cn/mw690/ae28eeb5jw8eeu8cudl8vj20hs0hs0t6.jpg'
		}
		
		$.ajax({
			url: 'http://www.dangdaimingshi.com/ddmswechat/get_signature.php?signurl=' + encodeURIComponent(window.location.href),
			type: 'GET',
			success: function(data) {
				var jdata = $.parseJSON(data);
				if (jdata.appId) {
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
		
		wx.ready(function() {
			oMS(shareData);
		});
		
		function oMS(obj) {
			wx.onMenuShareTimeline({
				title: obj.desc,
				link: obj.link,
				imgUrl: obj.imgUrl,
				success: function() {
					console.log('onMenuShareTimeline');
					replaceImage();
				}
			});
			wx.onMenuShareAppMessage({
				title: obj.title,
				desc: obj.desc,
				link: obj.link,
				imgUrl: obj.imgUrl,
				success: function() {
					console.log('onMenuShareAppMessage');
					replaceImage();
				}
			});
		}
		
		function replaceImage() {
			if ($('.theme').css('display') == 'none') {
				// pr.onload = function() {
					var pr = new Image();
					pr.src = 'assets/p4/pr.png';
					$('.spr').children().remove();
					$('.spr').append(pr);
					$('.share_float').hide();
				// }
			}
		}
		
		function getQueryString(name) {
			var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i"),
				r = window.location.search.substr(1).match(reg);
			if (r != null) return decodeURIComponent(r[2]);
			return null;
		}
	});
	
});