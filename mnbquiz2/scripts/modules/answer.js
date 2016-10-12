define(['./fn', './data', 'wx'], function(fn, data, wx) {
	
	var cjs = createjs || {},
		Quiz = new fn.Quiz(),
		loader = fn.loader,
		stage = fn.stage,
		w = stage.canvas.width,
		h = stage.canvas.height,
		openid,
		utm_source,
		utm_source_share,
		shareUrl = 'http://lftapl.dangdaimingshi.com/mnbquiz2/';
	
	if (Quiz.gqs('utm_source')) {
		utm_source = utm_source_share = Quiz.gqs('utm_source');
		if (utm_source_share.indexOf('share') != -1) {
			var end = utm_source_share.indexOf('share') - 1;
			utm_source_share = utm_source_share.substring(0, end);
		}
		document.cookie = 'utm_source=' + utm_source;
	} else {
		utm_source = Quiz.cookie('utm_source');
		window.location.href += ('&utm_source=' + utm_source);
	}
	
	if (Quiz.gqs('unionid')) {
		openid = Quiz.gqs('unionid');
	} else {
		// auth to get openid
		// var time = new Date().getTime(),
		// 	unixtime = Math.floor(time / 1000),
		// 	key = 'gyp2016',
		// 	a = '5spzSmcI0h',
		// 	token = hex_md5(key + unixtime);
		// window.location.href = 'http://mnbq1pro.gypserver.com/wxAPI/?mark=getwxoauth&token=' + token + '&unixtime=' + unixtime + '&a=' + a + '&rurl=' + encodeURIComponent(window.location.origin + window.location.pathname) + '&scope=snsapi_userinfo';
		location.href = 'login.php?rwurl=' + encodeURIComponent('http://lftapl.dangdaimingshi.com/mnbquiz2/index.html');
	}
	
	loader.on('fileload', handleFileload);
	loader.on('complete', handleComplete);
	loader.loadManifest(data.questions);
	
	var shareData = {
		title: '破译你的派对人格',
		desc1: '气氛主导，我是控场一流的派对领袖',
		desc2: '魅力超群，我是火力全开的派对玩家',
		desc3: '左右逢源，我是人见人爱的派对小蜜蜂',
		desc4: '行走的荷尔蒙，我是性感的派对捕猎手',
		desc5: '魅力隐形，我是低调闷骚的派对旁观者',
		afterLink: shareUrl + 'share.html?openid=' + openid + '&utm_source=' + utm_source_share + '_share',
		beforeLink: shareUrl + 'index.html?utm_source=' + utm_source_share + '_share',
		imgUrl: shareUrl + 'assets/share.jpg'
	}
	
	var coupon_50_href = 'http://www.wemart.cn/v2/weimao/index.html?shopId=shop000201603099411#gc/2636/6',
		coupon_25_href = 'http://www.wemart.cn/v2/weimao/index.html?shopId=shop000201603099411#gc/2637/6';
	
	var createUserResult,
		createUserQuizId,
		questoinFourAnswer,
		questionFiveAnswer,
		questionNo = 1;
	
	var assets = {q1:[], q2:[], q2c:[], q3:[], q3c:[], q4:[], q5:[], q5c:[], q6:[], q7:[], q7b: [], q7c:[], q8:[], q8t:[], q8c:[], q9:[], q9c:[], q10:[]},
		result = {q1:[], q2:[], q3:[], q4:[], q5:[], q6:[], q7:[], q8:[], q9:[], q10:[]};
	
	var $number = $('#answer .number'),
		$next = $('#answer .next'),
		$noSubmit = $('#answer .noSubmit');
	// next question
	$next.on('click', function() {
		// console.info('question.No: ' + questionNo);
		result['q' + questionNo] = Quiz.compact(result['q' + questionNo]);
		if (!result['q' + questionNo].length && questionNo != 8) {
			alert('请至少选择一个选项');
		} else if (result.q8.length != 3 && questionNo == 8) {
			alert('请选择三个选项');
		} else {
			var _num = +$number.text();
			if (questionNo < 10) {
				$next.addClass('next-gray');
			} else {
				$next.addClass('next-check-gray');
			}
			if (questionNo == 5 && result.q5.indexOf(2) == -1) {
				_num = 11;
			} else {
				questionNo < 10 ? questionNo++ : questionNo = 10;
			}
			$noSubmit.show();
			switch(_num) {
				case 1:
					Quiz.ajax({action:'save_quiz',openid:openid,quiz_id:1,quiz_answer:result.q1[0]}, function(data) {
						if (data.result == 1) {
							afterSubmit();
							stage.addChild(assets.q2_t, assets.q2[0], assets.q2[1], assets.q2[2], assets.q2[3], assets.q2[4], assets.q2[5], assets.q2[6]);
						}
					});
					break;
				case 2:
					Quiz.ajax({action:'save_quiz',openid:openid,quiz_id:2,quiz_answer:result.q2.join('|')}, function(data) {
						if (data.result == 1) {
							afterSubmit();
							stage.addChild(assets.q3_t, assets.q3[0], assets.q3[1], assets.q3[2], assets.q3[3], assets.q3[4], assets.q3[5], assets.q3[6]);
						}
					});
					break;
				case 3:
					Quiz.ajax({action:'save_quiz',openid:openid,quiz_id:3,quiz_answer:result.q3.join('|')}, function(data) {
						if (data.result == 1) {
							afterSubmit();
							stage.addChild(assets.q4_t, assets.q4[0], assets.q4[1], assets.q4[2], assets.q4[3], assets.q4[4], assets.q4[5], assets.q4[6]);
						}
					});
					break;
				case 4:
					questoinFourAnswer = result.q4[0];
					onMenuShare(shareData, questoinFourAnswer);
					getResult(result.q4[0]);
					getGift(result.q4[0]);
					Quiz.ajax({action:'save_quiz',openid:openid,quiz_id:4,quiz_answer:result.q4[0]}, function(data) {
						if (data.result == 1) {
							afterSubmit();
							stage.addChild(assets.q5_t, assets.q5[0], assets.q5[1], assets.q5[2], assets.q5[3], assets.q5[4], assets.q5[5], assets.q5[6]);
						}
					});
					break;
				case 5:
					Quiz.ajax({action:'save_quiz',openid:openid,quiz_id:5,quiz_answer:result.q5.join('|')}, function(data) {
						if (data.result == 1) {
							afterSubmit();
							stage.addChild(assets.q6_t, assets.q6[0], assets.q6[1], assets.q6[2], assets.q6[3], assets.q6[4], assets.q6[5]);
						}
					});
					break;
				case 6:
					Quiz.ajax({action:'save_quiz',openid:openid,quiz_id:6,quiz_answer:result.q6[0]}, function(data) {
						if (data.result == 1) {
							afterSubmit();
							stage.addChild(assets.q7_t, assets.q7b[0], assets.q7b[1],assets.q7b[2], assets.q7[0], assets.q7[1], assets.q7[2], assets.q7[3], assets.q7[4], assets.q7[5], assets.q7[6], assets.q7[7], assets.q7[8], assets.q7[9], assets.q7[10]);
						}
					});
					break;
				case 7:
					Quiz.ajax({action:'save_quiz',openid:openid,quiz_id:7,quiz_answer:result.q7.join('|')}, function(data) {
						if (data.result == 1) {
							afterSubmit();
							stage.addChild(assets.q8_t, assets.q8t[0], assets.q8t[1], assets.q8t[2], assets.q8[0], assets.q8[1], assets.q8[2], assets.q8[3], assets.q8[4], assets.q8[5], assets.q8[6], assets.q8[7], assets.q8[8], assets.q8[9], assets.q8[10], assets.q8[11], assets.q8[12]);
						}
					});
					break;
				case 8:
					Quiz.ajax({action:'save_quiz',openid:openid,quiz_id:8,quiz_answer:result.q8.join('|')}, function(data) {
						if (data.result == 1) {
							afterSubmit();
							stage.addChild(assets.q9_t, assets.q9[0], assets.q9[1], assets.q9[2], assets.q9[3], assets.q9[4], assets.q9[5], assets.q9[6], assets.q9[7], assets.q9[8]);
						}
					});
					break;
				case 9:
					Quiz.ajax({action:'save_quiz',openid:openid,quiz_id:9,quiz_answer:result.q9.join('|')}, function(data) {
						if (data.result == 1) {
							afterSubmit();
							stage.addChild(assets.q10_t, assets.time, assets.money, assets.blackBg, assets.progress, assets.choose, assets.handUp, assets.handDwon);
							$next.addClass('next-check');
							$('.black-bg').show();
						}
					});
					break;
				case 10:
					Quiz.ajax({action:'save_quiz',openid:openid,quiz_id:10,quiz_answer:result.q10[0]}, function(data) {
						if (data.result == 1) {
							$next.removeClass('next-check-gray');
							$('#result').show();
							$('#answer').hide();
						}
					});
					break;
				case 11:
					Quiz.ajax({action:'save_quiz',openid:openid,quiz_id:5,quiz_answer:result.q5.join('|')}, function(data) {
						if (data.result == 1) {
							questionNo = 8;
							afterSubmit();
							stage.addChild(assets.q8_t, assets.q8t[0], assets.q8t[1], assets.q8t[2], assets.q8[0], assets.q8[1], assets.q8[2], assets.q8[3], assets.q8[4], assets.q8[5], assets.q8[6], assets.q8[7], assets.q8[8], assets.q8[9], assets.q8[10], assets.q8[11], assets.q8[12]);
						}
					});
				default:
					break;
			}
		}
	});
	// lottery
	var codeReg = /[0-9]{4}/;
	$('#submit .lottery').on('click', function() {
		console.log('type: ' + questoinFourAnswer);
		captcha = $('.captcha').val();
		if(!codeReg.test(captcha)) {
			alert('请填写正确的验证码');
		} else {
			Quiz.ajax({
				action: 'check_user',
				openid: openid,
				mobile: $('.mobile').val(),
				captcha: captcha,
				type: questoinFourAnswer
			}, function(data) {
				var result = data.result;
				if (result == 3) {
					$('#hint').show();
					$('.getCoupon').attr('href', coupon_25_href);
					Quiz.fail();
				} else if (result == 2) {
					$('#hint').show();
					Quiz.played();
				} else if (result == 1) {
					if (questoinFourAnswer != 4) {
						$('#info').show();
						$('#submit').hide();
					} else {
						$('#hint .coupon').addClass('coupon_50');
						$('#hint').show();
						$('.getCoupon').attr('href', coupon_50_href);
						Quiz.fail();
					}
				} else if (result == 4) {
					alert('验证码错误');
				}
			});
		}
	});
	
	$(function() {
		// wechat jssdk config
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
			wx.onMenuShareTimeline({
				title: shareData.title,
				link: shareData.beforeLink,
				imgUrl: shareData.imgUrl
			});
			wx.onMenuShareAppMessage({
				title: shareData.title,
				desc: shareData.title,
				link: shareData.beforeLink,
				imgUrl: shareData.imgUrl
			});
		});
	});
	
	function handleFileload(event) {
		var _itemLoaded = loader._numItemsLoaded,
			_items = loader._numItems;
		var percent = parseInt((_itemLoaded / _items).toFixed(2) * 100);
		$('.percent').text(percent);
		$('.loading .inner').css('width', percent + '%');
		switch(_itemLoaded) {
			case 8:
				q1();
				break;
			case 16:
				q2();
				break;
			case 24:
				q3();
				break;
			case 30:
				q4();
				break;
			case 31:
				waiter();
				break;
			case 39:
				q5();
				break;
			case 46:
				q6();
				break;
			case 58:
				q7();
				break;
			case 88:
				q8();
				break;
			case 98:
				q9();
				break;
			case 105:
				q10();
				break;
			default:
				break;
		}
	}
	
	// create_user
	function handleComplete() {
		Quiz.ajax({
			action: 'create_user',
			openid: openid,
			utm_source: utm_source
		}, function(data) {
			createUserResult = data.result;
			createUserQuizId = data.quiz_id;
			if (createUserResult == 1) {
				if (createUserQuizId != null) {
					questionNo = createUserQuizId + 1;
					$('.number').text(questionNo);
					questionShow(questionNo);
				}
			}
			if (createUserResult == 2) {
				questoinFourAnswer = data.quiz_answer4;
				onMenuShare(shareData, questoinFourAnswer);
				getResult(questoinFourAnswer);
				getGift(questoinFourAnswer);
				if (createUserQuizId != 10) {
					questionNo = createUserQuizId + 1;
					$('.number').text(questionNo);
					questionShow(questionNo);
				}
			}
			if (createUserResult == 5) {
				questoinFourAnswer = data.quiz_answer4;
				questionFiveAnswer = data.quiz_answer5.split('|');
				if (questionFiveAnswer.indexOf('2') == -1) { // 没选干邑
					questionNo = 8;
				} else { // 选了干邑
					questionNo = 6;
				}
				$('.number').text(questionNo);
				questionShow(questionNo);
				onMenuShare(shareData, questoinFourAnswer);
				getResult(questoinFourAnswer);
				getGift(questoinFourAnswer);
			}
		});
		// loading
		$('.loading').hide();
		// index
		$('.start').on('click', function() {
			if (createUserResult) {
				$('.warn2').removeClass('warn2-index');
				$('.detail').hide();
				switch(createUserResult) {
					case 1:
					case 5:
						$('#answer').show();
						$('#index').hide();
						break;
					case 2:
						if (createUserQuizId == 10) {
							$('#result').show();
							$('#index').hide();
						} else {
							$('#answer').show();
							$('#index').hide();
						}
						break;
					case 3:
						$('.played').show();
						$('#hint').show();
						$('#index').hide();
						break;
					case 4:
						$('#info').show();
						$('#index').hide();
						break;
					default:
						break;
				}
			}
		});
	}
	
	function afterSubmit() {
		$number.text(questionNo);
		showWaiter(questionNo);
		$noSubmit.hide();
		$next.removeClass('next-gray');
		stage.removeAllChildren();
	}
	
	function q1() {
		assets.isChoose = Quiz.bitmap('isChoose');
		
		assets.q1_t = Quiz.bitmap('q1_t').set(data.position.title);
		
		assets.q1[0] = Quiz.bitmap('q1_1');
		
		var q1_a_width = assets.q1[0].image.width;
		
		assets.q1[0].set({
			x: w - q1_a_width >> 1,
			y: data.position.q1.a[0]
		});
		
		assets.q1[1] = Quiz.bitmap('q1_2');
		assets.q1[1].set({
			x: w - q1_a_width >> 1,
			y: data.position.q1.a[1]
		});
		
		assets.q1[2] = Quiz.bitmap('q1_3');
		assets.q1[2].set({
			x: w - q1_a_width >> 1,
			y: data.position.q1.a[2]
		});
		
		assets.q1[3] = Quiz.bitmap('q1_4');
		assets.q1[3].set({
			x: w - q1_a_width >> 1,
			y: data.position.q1.a[3]
		});
		
		assets.q1[4] = Quiz.bitmap('q1_5');
		assets.q1[4].set({
			x: w - q1_a_width >> 1,
			y: data.position.q1.a[4]
		});
		
		assets.q1[5] = Quiz.bitmap('q1_6');
		assets.q1[5].set({
			x: w - q1_a_width >> 1,
			y: data.position.q1.a[5]
		});
		
		stage.addChild(assets.q1_t, assets.q1[0], assets.q1[1], assets.q1[2], assets.q1[3], assets.q1[4], assets.q1[5]);
		
		Quiz.radio(assets.q1, function(index) {
			result.q1[0] = index + 1;
			console.info('question1: ' + result.q1[0]);
			assets.isChoose.set(data.position.q1.c[index]);
			stage.addChild(assets.isChoose);
		});
	}
	
	function q2() {
		assets.q2_t = Quiz.bitmap('q2_t').set(data.position.title);
		assets.q2[0] = Quiz.bitmap('q2_1').set(data.position.q2.a[0]);
		assets.q2[1] = Quiz.bitmap('q2_2').set(data.position.q2.a[1]);
		assets.q2[2] = Quiz.bitmap('q2_3').set(data.position.q2.a[2]);
		assets.q2[3] = Quiz.bitmap('q2_4').set(data.position.q2.a[3]);
		assets.q2[4] = Quiz.bitmap('q2_5').set(data.position.q2.a[4]);
		assets.q2[5] = Quiz.bitmap('q2_6').set(data.position.q2.a[5]);
		assets.q2[6] = Quiz.bitmap('q2_7').set(data.position.q2.a[6]);
		// clone choose icon
		assets.q2c[0] = assets.isChoose.clone().set(data.position.q2.c[0]);
		assets.q2c[1] = assets.isChoose.clone().set(data.position.q2.c[1]);
		assets.q2c[2] = assets.isChoose.clone().set(data.position.q2.c[2]);
		assets.q2c[3] = assets.isChoose.clone().set(data.position.q2.c[3]);
		assets.q2c[4] = assets.isChoose.clone().set(data.position.q2.c[4]);
		assets.q2c[5] = assets.isChoose.clone().set(data.position.q2.c[5]);
		assets.q2c[6] = assets.isChoose.clone().set(data.position.q2.c[6]);
		
		Quiz.check(assets.q2, function(index) {
			// true
			result.q2.push(index + 1);
			console.info('question2: ' + result.q2);
			stage.addChild(assets.q2c[index]);
		}, function(index) {
			// false
			result.q2[result.q2.indexOf(index + 1)] = false;
			console.info('question2: ' + result.q2);
			stage.removeChild(assets.q2c[index]);
		});
	}
	
	function q3() {
		assets.q3_t = Quiz.bitmap('q3_t').set(data.position.title);
		assets.q3[0] = Quiz.bitmap('q3_1').set(data.position.q3.a[0]);
		assets.q3[1] = Quiz.bitmap('q3_2').set(data.position.q3.a[1]);
		assets.q3[2] = Quiz.bitmap('q3_3').set(data.position.q3.a[2]);
		assets.q3[3] = Quiz.bitmap('q3_4').set(data.position.q3.a[3]);
		assets.q3[4] = Quiz.bitmap('q3_5').set(data.position.q3.a[4]);
		assets.q3[5] = Quiz.bitmap('q3_6').set(data.position.q3.a[5]);
		assets.q3[6] = Quiz.bitmap('q3_7').set(data.position.q3.a[6]);
		// choose icon
		assets.q3c[0] = assets.isChoose.clone().set(data.position.q3.c[0]);
		assets.q3c[1] = assets.isChoose.clone().set(data.position.q3.c[1]);
		assets.q3c[2] = assets.isChoose.clone().set(data.position.q3.c[2]);
		assets.q3c[3] = assets.isChoose.clone().set(data.position.q3.c[3]);
		assets.q3c[4] = assets.isChoose.clone().set(data.position.q3.c[4]);
		assets.q3c[5] = assets.isChoose.clone().set(data.position.q3.c[5]);
		assets.q3c[6] = assets.isChoose.clone().set(data.position.q3.c[6]);
		
		Quiz.check(assets.q3, function(index) {
			// true
			result.q3.push(index + 1);
			console.info('question3: ' + result.q3);
			stage.addChild(assets.q3c[index]);
		}, function(index) {
			// false
			result.q3[result.q3.indexOf(index + 1)] = false;
			console.info('question3: ' + result.q3);
			stage.removeChild(assets.q3c[index]);
		}, 2);
	}
	
	function q4() {
		assets.q4_t = Quiz.bitmap('q4_t').set(data.position.title);
		assets.q4[0] = Quiz.bitmap('q4_1').set(data.position.q4.a[0]);
		assets.q4[1] = Quiz.bitmap('q4_2').set(data.position.q4.a[1]);
		assets.q4[2] = Quiz.bitmap('q4_3').set(data.position.q4.a[2]);
		assets.q4[3] = Quiz.bitmap('q4_4').set(data.position.q4.a[3]);
		assets.q4[4] = Quiz.bitmap('q4_5').set(data.position.q4.a[4]);
		
		Quiz.radio(assets.q4, function(index) {
			result.q4[0] = index + 1;
			console.info('question4: ' + result.q4[0]);
			assets.isChoose.set(data.position.q4.c[index]);
			stage.addChild(assets.isChoose);
		});
	}
	
	function waiter() {
		assets.waiter = new Image();
		assets.waiter.src = 'assets/answer/waiter.png';
		assets.waiter.onload = function() {
			$('.waiter').append(assets.waiter);
		}
	}
	
	function q5() {
		assets.q5_t = Quiz.bitmap('q5_t').set(data.position.title);
		assets.q5[0] = Quiz.bitmap('q5_1').set(data.position.q5.a[0]);
		assets.q5[1] = Quiz.bitmap('q5_2').set(data.position.q5.a[1]);
		assets.q5[2] = Quiz.bitmap('q5_3').set(data.position.q5.a[2]);
		assets.q5[3] = Quiz.bitmap('q5_4').set(data.position.q5.a[3]);
		assets.q5[4] = Quiz.bitmap('q5_5').set(data.position.q5.a[4]);
		assets.q5[5] = Quiz.bitmap('q5_6').set(data.position.q5.a[5]);
		assets.q5[6] = Quiz.bitmap('q5_7').set(data.position.q5.a[6]);
		// choose icon
		assets.q5c[0] = assets.isChoose.clone().set(data.position.q5.c[0]);
		assets.q5c[1] = assets.isChoose.clone().set(data.position.q5.c[1]);
		assets.q5c[2] = assets.isChoose.clone().set(data.position.q5.c[2]);
		assets.q5c[3] = assets.isChoose.clone().set(data.position.q5.c[3]);
		assets.q5c[4] = assets.isChoose.clone().set(data.position.q5.c[4]);
		assets.q5c[5] = assets.isChoose.clone().set(data.position.q5.c[5]);
		assets.q5c[6] = assets.isChoose.clone().set(data.position.q5.c[6]);
		
		Quiz.check(assets.q5, function(index) {
			// true
			result.q5.push(index + 1);
			console.info('question5: ' + result.q5);
			stage.addChild(assets.q5c[index]);
		}, function(index) {
			// false
			result.q5[result.q5.indexOf(index + 1)] = false;
			console.info('question5: ' + result.q5);
			stage.removeChild(assets.q5c[index]);
		});
	}
	
	function q6() {
		assets.q6_t = Quiz.bitmap('q6_t').set(data.position.title);
		
		assets.q6[0] = Quiz.bitmap('q6_1');
		
		var q1_a_width = assets.q6[0].image.width;
		
		assets.q6[0].set({
			x: w - q1_a_width >> 1,
			y: data.position.q6.a[0]
		});
		
		assets.q6[1] = Quiz.bitmap('q6_2');
		assets.q6[1].set({
			x: w - q1_a_width >> 1,
			y: data.position.q6.a[1]
		});
		
		assets.q6[2] = Quiz.bitmap('q6_3');
		assets.q6[2].set({
			x: w - q1_a_width >> 1,
			y: data.position.q6.a[2]
		});
		
		assets.q6[3] = Quiz.bitmap('q6_4');
		assets.q6[3].set({
			x: w - q1_a_width >> 1,
			y: data.position.q6.a[3]
		});
		
		assets.q6[4] = Quiz.bitmap('q6_5');
		assets.q6[4].set({
			x: w - q1_a_width >> 1,
			y: data.position.q6.a[4]
		});
		
		assets.q6[5] = Quiz.bitmap('q6_6');
		assets.q6[5].set({
			x: w - q1_a_width >> 1,
			y: data.position.q6.a[5]
		});
		
		Quiz.radio(assets.q6, function(index) {
			result.q6[0] = index + 1;
			console.info('question6: ' + result.q6[0]);
			assets.isChoose.set(data.position.q6.c[index]);
			stage.addChild(assets.isChoose);
		});
	}
	
	function q7() {
		assets.q7_t = Quiz.bitmap('q7_t').set(data.position.title);
		assets.bottom = Quiz.bitmap('bottom');
		assets.q7b[0] = assets.bottom.clone().set(data.position.q7.b[0]);
		assets.q7b[1] = assets.bottom.clone().set(data.position.q7.b[1]);
		assets.q7b[2] = assets.bottom.clone().set(data.position.q7.b[2]);
		assets.q7[0] = Quiz.bitmap('q7_1').set(data.position.q7.a[0]);
		assets.q7[1] = Quiz.bitmap('q7_2').set(data.position.q7.a[1]);
		assets.q7[2] = Quiz.bitmap('q7_3').set(data.position.q7.a[2]);
		assets.q7[3] = Quiz.bitmap('q7_4').set(data.position.q7.a[3]);
		assets.q7[4] = Quiz.bitmap('q7_5').set(data.position.q7.a[4]);
		assets.q7[5] = Quiz.bitmap('q7_6').set(data.position.q7.a[5]);
		assets.q7[6] = Quiz.bitmap('q7_7').set(data.position.q7.a[6]);
		assets.q7[7] = Quiz.bitmap('q7_8').set(data.position.q7.a[7]);
		assets.q7[8] = Quiz.bitmap('q7_9').set(data.position.q7.a[8]);
		assets.q7[9] = Quiz.bitmap('q7_10').set(data.position.q7.a[9]);
		// choose icon
		assets.q7c[0] = assets.isChoose.clone().set(data.position.q7.c[0]);
		assets.q7c[1] = assets.isChoose.clone().set(data.position.q7.c[1]);
		assets.q7c[2] = assets.isChoose.clone().set(data.position.q7.c[2]);
		assets.q7c[3] = assets.isChoose.clone().set(data.position.q7.c[3]);
		assets.q7c[4] = assets.isChoose.clone().set(data.position.q7.c[4]);
		assets.q7c[5] = assets.isChoose.clone().set(data.position.q7.c[5]);
		assets.q7c[6] = assets.isChoose.clone().set(data.position.q7.c[6]);
		assets.q7c[7] = assets.isChoose.clone().set(data.position.q7.c[7]);
		assets.q7c[8] = assets.isChoose.clone().set(data.position.q7.c[8]);
		assets.q7c[9] = assets.isChoose.clone().set(data.position.q7.c[9]);
		
		Quiz.check(assets.q7, function(index) {
			// true
			result.q7.push(index + 1);
			console.info('question7: ' + result.q7);
			stage.addChild(assets.q7c[index]);
		}, function(index) {
			// false
			result.q7[result.q7.indexOf(index + 1)] = false;
			console.info('question7: ' + result.q7);
			stage.removeChild(assets.q7c[index]);
		});
	}
	
	function q8() {
		assets.q8_t = Quiz.bitmap('q8_t').set(data.position.title);
		// top
		assets.q8t[0] = Quiz.bitmap('top1').set(data.position.q8.t[0]);
		assets.q8t[1] = Quiz.bitmap('top2').set(data.position.q8.t[1]);
		assets.q8t[2] = Quiz.bitmap('top3').set(data.position.q8.t[2]);
		// choose
		assets.q8[0] = Quiz.bitmap('q8_1').set(data.position.q8.a[0]);
		assets.q8[1] = Quiz.bitmap('q8_2').set(data.position.q8.a[1]);
		assets.q8[2] = Quiz.bitmap('q8_3').set(data.position.q8.a[2]);
		assets.q8[3] = Quiz.bitmap('q8_4').set(data.position.q8.a[3]);
		assets.q8[4] = Quiz.bitmap('q8_5').set(data.position.q8.a[4]);
		assets.q8[5] = Quiz.bitmap('q8_6').set(data.position.q8.a[5]);
		assets.q8[6] = Quiz.bitmap('q8_7').set(data.position.q8.a[6]);
		assets.q8[7] = Quiz.bitmap('q8_8').set(data.position.q8.a[7]);
		assets.q8[8] = Quiz.bitmap('q8_9').set(data.position.q8.a[8]);
		assets.q8[9] = Quiz.bitmap('q8_10').set(data.position.q8.a[9]);
		assets.q8[10] = Quiz.bitmap('q8_11').set(data.position.q8.a[10]);
		assets.q8[11] = Quiz.bitmap('q8_12').set(data.position.q8.a[11]);
		assets.q8[12] = Quiz.bitmap('q8_13').set(data.position.q8.a[12]);
		// choosen
		assets.q8c[0] = Quiz.bitmap('q8_c1');
		assets.q8c[1] = Quiz.bitmap('q8_c2');
		assets.q8c[2] = Quiz.bitmap('q8_c3');
		assets.q8c[3] = Quiz.bitmap('q8_c4');
		assets.q8c[4] = Quiz.bitmap('q8_c5');
		assets.q8c[5] = Quiz.bitmap('q8_c6');
		assets.q8c[6] = Quiz.bitmap('q8_c7');
		assets.q8c[7] = Quiz.bitmap('q8_c8');
		assets.q8c[8] = Quiz.bitmap('q8_c9');
		assets.q8c[9] = Quiz.bitmap('q8_c10');
		assets.q8c[10] = Quiz.bitmap('q8_c11');
		assets.q8c[11] = Quiz.bitmap('q8_c12');
		assets.q8c[12] = Quiz.bitmap('q8_c13');
		
		Quiz.twoCheck(assets.q8, assets.q8t, function(index, choose_pos) {
			// true
			if (result.q8.indexOf(index + 1) == -1 && Quiz.compact(result.q8).length < 3) {
				result.q8.push(index + 1);
			}
			console.info('question8: ' + result.q8);
			assets.q8c[index].set(data.position.q8.c[choose_pos]);
			stage.addChild(assets.q8c[index]);
		}, function(index) {
			result.q8[result.q8.indexOf(index + 1)] = false;
			console.info('question8: ' + result.q8);
			stage.removeChild(assets.q8c[index]);
		}, 3);
	}
	
	function q9() {
		assets.q9_t = Quiz.bitmap('q9_t').set(data.position.title);
		assets.q9[0] = Quiz.bitmap('q9_1').set(data.position.q9.a[0]);
		assets.q9[1] = Quiz.bitmap('q9_2').set(data.position.q9.a[1]);
		assets.q9[2] = Quiz.bitmap('q9_3').set(data.position.q9.a[2]);
		assets.q9[3] = Quiz.bitmap('q9_4').set(data.position.q9.a[3]);
		assets.q9[4] = Quiz.bitmap('q9_5').set(data.position.q9.a[4]);
		assets.q9[5] = Quiz.bitmap('q9_6').set(data.position.q9.a[5]);
		assets.q9[6] = Quiz.bitmap('q9_7').set(data.position.q9.a[6]);
		assets.q9[7] = Quiz.bitmap('q9_8').set(data.position.q9.a[7]);
		assets.q9[8] = Quiz.bitmap('q9_9').set(data.position.q9.a[8]);
		// choose icon
		assets.q9c[0] = assets.isChoose.clone().set(data.position.q9.c[0]);
		assets.q9c[1] = assets.isChoose.clone().set(data.position.q9.c[1]);
		assets.q9c[2] = assets.isChoose.clone().set(data.position.q9.c[2]);
		assets.q9c[3] = assets.isChoose.clone().set(data.position.q9.c[3]);
		assets.q9c[4] = assets.isChoose.clone().set(data.position.q9.c[4]);
		assets.q9c[5] = assets.isChoose.clone().set(data.position.q9.c[5]);
		assets.q9c[6] = assets.isChoose.clone().set(data.position.q9.c[6]);
		assets.q9c[7] = assets.isChoose.clone().set(data.position.q9.c[7]);
		assets.q9c[8] = assets.isChoose.clone().set(data.position.q9.c[8]);
		
		Quiz.check(assets.q9, function(index) {
			// true
			result.q9.push(index + 1);
			console.info('question9: ' + result.q9);
			stage.addChild(assets.q9c[index]);
		}, function(index) {
			// false
			result.q9[result.q9.indexOf(index + 1)] = false;
			console.info('question9: ' + result.q9);
			stage.removeChild(assets.q9c[index]);
		});
	}
	
	function q10() {
		assets.q10_t = Quiz.bitmap('q10_t').set(data.position.title);
		// progress
		assets.progress = Quiz.bitmap('progress').set(data.position.q10.progress);
		// time
		assets.time = Quiz.bitmap('time').set(data.position.q10.time);
		// money
		assets.money = Quiz.bitmap('money').set(data.position.q10.money);
		// hands up and down
		assets.handUp = Quiz.bitmap('hand_up').set(data.position.q10.hand_up);
		assets.handDwon = Quiz.bitmap('hand_down').set(data.position.q10.hand_down);
		// bg
		assets.blackBg = new cjs.Shape();
		assets.blackBg.graphics.beginFill('rgba(0,0,0,.8)').drawRect(0, 0, w, h);
		// choose
		assets.choose = Quiz.bitmap('c').set(data.position.q10.choose);
		
		createjs.Tween.get(assets.handUp, {loop: true}).to({y: 340}, 800, createjs.Ease.quartInOut);
		createjs.Tween.get(assets.handDwon, {loop: true}).to({y: 458}, 800, createjs.Ease.quartInOut);
		
		var extraHeight = assets.choose.image.height >> 1,
			left = data.position.q10.choose.x;
		
		var isDown = false;
		
		result.q10[0] = 4;
		
		stage.on('stagemousedown', function(event) {
			if ($number.text() == '10') {
				isDown = true;
			}
		});
		stage.on('stagemousemove', function(event) {
			if (isDown) {
				var top = event.stageY;
				if (top >= 256 && top <= 786) {
					$('.black-bg').hide();
					stage.removeChild(assets.blackBg, assets.handUp, assets.handDwon);
					assets.choose.setTransform(left, top - extraHeight);
					if (top <= data.position.q10.sure[0]) {
						assets.choose.setTransform(left, 256 - extraHeight);
						result.q10[0] = 1;
						console.info('question10: ' + result.q10);
					}
					if (top >= data.position.q10.sure[1]&& top <= data.position.q10.sure[2]) {
						assets.choose.setTransform(left, data.position.q10.sure[1] + 12.5 - extraHeight);
						result.q10[0] = 2;
						console.info('question10: ' + result.q10);
					}
					if (top >= data.position.q10.sure[3]&& top <= data.position.q10.sure[4]) {
						assets.choose.setTransform(left, data.position.q10.sure[3] + 12.5 - extraHeight);
						result.q10[0] = 3;
						console.info('question10: ' + result.q10);
					}
					if (top >= data.position.q10.sure[5]&& top <= data.position.q10.sure[6]) {
						assets.choose.setTransform(left, data.position.q10.sure[5] + 12.5 - extraHeight);
						result.q10[0] = 4;
						console.info('question10: ' + result.q10);
					}
					if (top >= data.position.q10.sure[7]&& top <= data.position.q10.sure[8]) {
						assets.choose.setTransform(left, data.position.q10.sure[7] + 12.5 - extraHeight);
						result.q10[0] = 5;
						console.info('question10: ' + result.q10);
					}
					if (top >= data.position.q10.sure[9]&& top <= data.position.q10.sure[10]) {
						assets.choose.setTransform(left, data.position.q10.sure[9] + 12.5 - extraHeight);
						result.q10[0] = 6;
						console.info('question10: ' + result.q10);
					}
					if (top >= data.position.q10.sure[11]&& top <= data.position.q10.sure[12]) {
						assets.choose.setTransform(left, data.position.q10.sure[11] + 12.5 - extraHeight);
						result.q10[0] = 7;
						console.info('question10: ' + result.q10);
					}
					if (top >= data.position.q10.sure[13]&& top <= data.position.q10.sure[14]) {
						assets.choose.setTransform(left, data.position.q10.sure[13] + 12.5 - extraHeight);
						result.q10[0] = 8;
						console.info('question10: ' + result.q10);
					}
					if (top >= data.position.q10.sure[15]&& top <= data.position.q10.sure[16]) {
						assets.choose.setTransform(left, data.position.q10.sure[15] + 12.5 - extraHeight);
						result.q10[0] = 9;
						console.info('question10: ' + result.q10);
					}
					if (top >= data.position.q10.sure[17]) {
						assets.choose.setTransform(left, 786 - extraHeight);
						result.q10[0] = 10;
						console.info('question10: ' + result.q10);
					}
				}
			}
		});
		stage.on('stagemouseup', function(event) {
			isDown = false;
		});
	}
	
	function getResult(answer) {
		var title = new Image(),
			picture = new Image();
		title.src = data.result['r' + answer].title;
		picture.src = data.result['r' + answer].picture;
		title.onload = function() {
			$('#result .title').append(title);
		}
		picture.onload = function() {
			$('#result .picture').append(picture);
		}
	}
	
	function getGift(answer) {
		if (answer != 4) {
			var title = new Image(),
				picture = new Image();
			title.src = data.info['gift' + answer].title;
			picture.src = data.info['gift' + answer].picture;
			title.onload = function() {
				$('#info .title').append(title);
			}
			picture.onload = function() {
				$('#info .picture').append(picture);
			}
		}
	}
	
	function questionShow(number) {
		showWaiter(number);
		stage.removeAllChildren();
		switch(number) {
			case 1:
				stage.addChild(assets.q1_t, assets.q1[0], assets.q1[1], assets.q1[2], assets.q1[3], assets.q1[4], assets.q1[5]);
				break;
			case 2:
				stage.addChild(assets.q2_t, assets.q2[0], assets.q2[1], assets.q2[2], assets.q2[3], assets.q2[4], assets.q2[5], assets.q2[6]);
				break;
			case 3:
				stage.addChild(assets.q3_t, assets.q3[0], assets.q3[1], assets.q3[2], assets.q3[3], assets.q3[4], assets.q3[5], assets.q3[6]);
				break;
			case 4:
				stage.addChild(assets.q4_t, assets.q4[0], assets.q4[1], assets.q4[2], assets.q4[3], assets.q4[4], assets.q4[5], assets.q4[6]);
				break;
			case 5:
				stage.addChild(assets.q5_t, assets.q5[0], assets.q5[1], assets.q5[2], assets.q5[3], assets.q5[4], assets.q5[5], assets.q5[6]);
				break;
			case 6:
				stage.addChild(assets.q6_t, assets.q6[0], assets.q6[1], assets.q6[2], assets.q6[3], assets.q6[4], assets.q6[5]);
				break;
			case 7:
				stage.addChild(assets.q7_t, assets.q7b[0], assets.q7b[1],assets.q7b[2], assets.q7[0], assets.q7[1], assets.q7[2], assets.q7[3], assets.q7[4], assets.q7[5], assets.q7[6], assets.q7[7], assets.q7[8], assets.q7[9], assets.q7[10]);
				break;
			case 8:
				stage.addChild(assets.q8_t, assets.q8t[0], assets.q8t[1], assets.q8t[2], assets.q8[0], assets.q8[1], assets.q8[2], assets.q8[3], assets.q8[4], assets.q8[5], assets.q8[6], assets.q8[7], assets.q8[8], assets.q8[9], assets.q8[10], assets.q8[11], assets.q8[12]);
				break;
			case 9:
				stage.addChild(assets.q9_t, assets.q9[0], assets.q9[1], assets.q9[2], assets.q9[3], assets.q9[4], assets.q9[5], assets.q9[6], assets.q9[7], assets.q9[8]);
				break;
			case 10:
				stage.addChild(assets.q10_t, assets.time, assets.money, assets.blackBg, assets.progress, assets.choose, assets.handUp, assets.handDwon);
				$('.black-bg').show();
				break;
			default:
				break;
		}
	}
	
	function showWaiter(num) {
		if (num == 5 || num == 7) {
			$('.waiter').show();
		} else {
			$('.waiter').hide();
		}
	}
	
	function onMenuShare(obj, key) {
		wx.onMenuShareTimeline({
			title: obj['desc' + key],
			link: obj.afterLink,
			imgUrl: obj.imgUrl
		});
		wx.onMenuShareAppMessage({
			title: obj.title,
			desc: obj['desc' + key],
			link: obj.afterLink,
			imgUrl: obj.imgUrl
		});
	}
});