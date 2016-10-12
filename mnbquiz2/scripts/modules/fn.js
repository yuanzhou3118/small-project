define(['fastclick'], function() {
	
	var cjs = createjs || {},
		loader = new cjs.LoadQueue(false, 'assets/answer/'),
		stage = new cjs.Stage('view');
	cjs.Touch.enable(stage);
	cjs.Ticker.addEventListener('tick', stage);
	cjs.Ticker.timingMode = cjs.Ticker.RAF;
	
	var Quiz = function(argus) {
		this._argus =  $.extend({}, Quiz.default, argus);
	}, p = Quiz.prototype;
	
	Quiz.default = {
		url: 'api/index.php',
		type: 'POST',
		dataType: 'json'
	}
	
	p.version = '0.4.4';
	
	p.gqs = function(name) {
		var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i"),
			r = window.location.search.substr(1).match(reg);
		if (r != null) return decodeURIComponent(r[2]);
		return null;
	}
	
	p.ajax = function(datas, fn) {
		var _argus = this._argus;
		$.ajax({
			url: _argus.url,
			type: _argus.type,
			dataType: _argus.dataType,
			data: datas,
			success: function(data) {
				fn && fn(data);
			}
		});
	}
	
	p.compact = function(arr) {
		var newArr = arr.filter(function(index) {
			return index;
		});
		return newArr;
	}
	
	p.cookie = function(name) {
		var kv = document.cookie.split('; '),
			len = kv.length;
		var key, value;
		for (var i = 0; i < len; i++) {
			key = kv[i].split('=')[0];
			if (key == name) {
				value = kv[i].split('=')[1];
				return value;
			}
		}
	}
	
	p.bitmap = function(id) {
		var result = loader.getResult(id),
			bitmap = new cjs.Bitmap(result);
		return bitmap;
	}
	
	p.radio = function(arr, fn) {
		var i = 0,
			len = arr.length;
		for (;i < len; i++) {
			arr[i].save = i;
			arr[i].on('click', function() {
				fn && fn(this.save);
			});
		}
	}
	
	p.check = function(arr, fn_true, fn_false, amount) {
		var count = 0,
			i = 0,
			len = arr.length;
		for (; i < len; i++) {
			arr[i].save = i;
			arr[i].clickTime = 0; // 选中奇数，未选中偶数
			arr[i].on('click', function() {
				this.clickTime++;
				if (amount) {
					if (count < amount) {
						if (this.checked) {
							this.clickTime = 0;
						} else {
							this.clickTime = 1;
						}
					}
					if (count == amount) {
						this.clickTime = 0;
						if (!this.checked) {
							count = amount + 1;
						}
					}
				}
				if (this.clickTime % 2 == 0) {
					this.checked = false;
					count--;
					fn_false && fn_false(this.save, count);
				} else {
					this.checked = true;
					count++;
					fn_true && fn_true(this.save, count);
				}
				console.log('clickTime: ' + this.clickTime + '\nchecked: ' + this.checked + '\ncount: ' + count);
			});
		}
	}
	
	p.twoCheck = function(arr_choose, arr_cancle, fn_true, fn, amount) {
		var count = 0, curPos = [false, false, false], choose_pos = null,
			i = 0, len_choose = arr_choose.length,
			j = 0, len_cancle = arr_cancle.length;
		var s = this;
		for (; i < len_choose; i++) {
			arr_choose[i].save = i;  // 选中 1，未选中 0
			arr_choose[i].on('click', function() {
				if (count < amount) {
					if (this.checked) {
						if (this.clickTime >= 1) { // 已经被选中
							this.clickTime++;
							count--;
						}
					} else { // 第一次选中
						this.clickTime = 1;
					}
				}
				if (count == amount) {
					this.clickTime = 0;
					count = amount + 1;
				}
				if (this.clickTime == 0) {
					this.checked = false;
					count--;
				} else {
					this.checked = true;
					count++;
					if (curPos.indexOf(false) != -1) {
						if (this.clickTime == 1) {
							choose_pos = curPos.indexOf(false);
							if (curPos.indexOf(this.save) == -1) {
								curPos[choose_pos] = this.save;
								fn_true(this.save, choose_pos);
							}
						}
					}
				}
				console.log('clickTime: ' + this.clickTime + '\nchecked: ' + this.checked + '\ncount: ' + count + '\nchoose_pso: ' + choose_pos + '\ncurPos: ' + curPos);
			});
		}
		for (; j < len_cancle; j++) {
			arr_cancle[j].save = j;
			arr_cancle[j].on('click', function() {
				if (curPos[this.save] !== false) {
					choose_pos = this.save;
					var choose = curPos[this.save];
					arr_choose[choose].checked = false;
					arr_choose[choose].clickTime = 0;
					fn(choose);
					curPos[this.save] = false;
					count = s.compact(curPos).length;
					console.log('clickTime: ' + arr_choose[choose].clickTime + '\nchecked: ' + arr_choose[choose].checked + '\ncount: ' + count + '\nchoose_pso: ' + this.save + '\ncurPos: ' + curPos);
				}
			});
		}
	}
	
	p.success = function() {
		$('.success').show();
		$('#submit').hide();
	}
	
	p.fail = function() {
		$('.fail').show();
		$('#submit').hide();
	}
	
	p.played = function() {
		$('.played').show();
		$('#submit').hide();
	}
	
	return {
		Quiz: Quiz,
		loader: loader,
		stage: stage
	}
	
});