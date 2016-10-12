(function() {
	var stage, w, h,
		queue1, queue2, queue3,
		loadFirstFiles, files, barFiles,
		bg1, spark, btn1, age, mask, yes, no,
		bg2, bars, bar1, bar2, bar3, bar4, mark, container, bds, bd1, bd2, bd3, bd4;
		
	var utm_source = getQueryString('utm_source');
	
	if (getQueryString('utm_medium')) {
		var user_from = +getQueryString('utm_medium').split('_')[1];
		if (user_from == 0) {
			user_from = 1;
		} else {
			user_from = 2;
		}
	} else {
		user_from = 1;
	}
		
	var pos = [
		{ x: 80, y: 410 },
		{ x: 480, y: 272 },
		{ x: 80, y: 706 },
		{ x: 500, y: 570 }
	];

	loadFirstFiles = [
		{id: 'a', src: 'img/s/bg.jpg'},
		{id: 'b', src: 'img/s/spark.png'},
		{id: 'c', src: 'img/s/iwantgo.png'},
		{id: 'd', src: 'img/age/age.png'},
		{id: 'e', src: 'img/logo.png'}
	];

	files = [
		{id: 'a', src: 'img/b/bg.jpg'},
		{id: 'b', src: 'img/b/bar1.png'},
		{id: 'c', src: 'img/b/bar2.png'},
		{id: 'd', src: 'img/b/bar3.png'},
		{id: 'e', src: 'img/b/bar4.png'},
		{id: 'f', src: 'img/b/mark.png'}
	];

	barFiles = [
		{id: 'a', src: 'img/d/0.jpg'},
		{id: 'b', src: 'img/d/1.jpg'},
		{id: 'c', src: 'img/d/2.jpg'},
		{id: 'd', src: 'img/d/3.jpg'},
		{id: 'e', src: 'img/d/more.png'}
	];

	stage = new createjs.Stage('view');
	w = stage.canvas.width;
	h = stage.canvas.height;

	createjs.Touch.enable(stage);
	stage.enableMouseOver();

	queue1 = new createjs.LoadQueue(false);
	
	queue1.on('complete', handleLoad);
	queue1.loadManifest(loadFirstFiles);

	queue2 = new createjs.LoadQueue(false);
	queue2.on('complete', handleComplete);

	queue3 = new createjs.LoadQueue(false);
	queue3.on('complete', handleDetail);

	createjs.Ticker.timingMode = createjs.Ticker.RAF;

	function handleLoad() {
		$('body').css('background', '#201718');
		var image1 = queue1.getResult('a');
		bg1 = new createjs.Bitmap(image1);
		
		mask = new createjs.Shape().set({alpha:0.85});
		mask.graphics.beginFill('#000').drawRect(0, 0, w, h);
		
		var image2 = queue1.getResult('b');
		spark = new createjs.Bitmap(image2).set({x:375, y:0});
		
		var image3 = queue1.getResult('c');
		btn1 = new createjs.Bitmap(image3);
		btn1.x = w - btn1.image.width >> 1;
		btn1.y = 1010;
		
		var image4 = queue1.getResult('d');
		age = new createjs.Bitmap(image4).set({x:112, y:370});
		
		var image5 = queue1.getResult('e');
		logo = new createjs.Bitmap(image5);
		logo.x = w - logo.image.width >> 1;
		logo.y = 40;
		
		yes = new createjs.Shape().set({x:428, y:728, alpha:0.01});
		yes.graphics.beginFill('#fff').drawRect(0, 0, 148, 108);
		
		no = new createjs.Shape().set({x:176, y:728, alpha:0.01});
		no.graphics.beginFill('#fff').drawRect(0, 0, 148, 108);
		
		yes.on('click', function() {
			createjs.Tween.get(spark,{loop:true}).to({alpha:0},500).wait(500).to({alpha:1},400).wait(700);
			stage.removeChild(mask, age, yes, no);
			queue3.loadManifest(barFiles);
			tracking('Cuba', 'Age-Gate-Yes');
		});
		
		no.on('click', function() {
			window.location.replace('http://www.we-responsible.com/');
			tracking('Cuba', 'Age-Gate-No');
		});
		
		$.ajax({
			url: 'api/index.php',
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'check_auth'
			},
			success: function(done) {
				var result = done.result,
					url = done.auth_url;
				if (result == 2) {window.location.href = url;}
				else if (result == 1) {
					stage.addChild(bg1, spark, btn1, mask, age, logo, yes, no);
					createjs.Ticker.addEventListener('tick', stage);
				} else {
					window.location.reload();
				}
			}
		});
		
		queue2.loadManifest(files);
	}

	function handleComplete() {
		btn1.on('click', handleMove);
		
		var image1 = queue2.getResult('a');
		bg2 = new createjs.Bitmap(image1);
		
		var image6 = queue2.getResult('f');
		mark = new createjs.Bitmap(image6);
		
		var image2 = queue2.getResult('b');
		bar1 = new createjs.Bitmap(image2).set({x:-650, y:260});
		
		var image3 = queue2.getResult('c');
		bar2 = new createjs.Bitmap(image3).set({x:760, y:145});
		
		var image4 = queue2.getResult('d');
		bar3 = new createjs.Bitmap(image4).set({x:-610, y:580});
		
		var image5 = queue2.getResult('e');
		bar4 = new createjs.Bitmap(image5).set({x:770, y:370});
		
		container = new createjs.Container();
		container.addChild(bar3, bar4, bar1, bar2);
	}

	function handleDetail() {
		bars = [bar1, bar2, bar3, bar4];
		
		var image1 = queue3.getResult('a');
		bd1 = new createjs.Bitmap(image1);
		
		var image2 = queue3.getResult('b');
		bd2 = new createjs.Bitmap(image2);
		
		var image3 = queue3.getResult('c');
		bd3 = new createjs.Bitmap(image3);
		
		var image4 = queue3.getResult('d');
		bd4 = new createjs.Bitmap(image4);
		
		var image5 = queue3.getResult('e');
		more = new createjs.Bitmap(image5);
		more.x = w - more.image.width >> 1;
		more.y = 1030;
		
		bds = [bd1, bd2, bd3, bd4];
		
		var index;
		for (var i = 0; i < 4; i++) {
			bars[i].save = i;
			bars[i].on('click', function() {
				index = this.save;
				stage.removeChild(mark);
				mark.set(pos[index]);
				stage.addChild(mark);
				setTimeout(function() {
					stage.addChild(bds[index], more);
					for (var j = 0; j < 4; j++) {
						$('#intro' + j).hide();
					}
					$('#intro' + index).show();
					stage.removeChild(container, mask);
				}, 600);
			});
		}
		// tracking code start
		bars[0].on('click', function() {
			tracking('Cuba', 'Go-Cocktail');
		});
		bars[1].on('click', function() {
			tracking('Cuba', 'Go-Amber');
		});
		bars[2].on('click', function() {
			tracking('Cuba', 'Go-Lounge');
		});
		bars[3].on('click', function() {
			tracking('Cuba', 'Go-At');
		});
		// tracking code end
		more.on('click', function() {
			switch (index) {
				case 0:
					tracking('Cuba', 'More-Cocktail');
					break;
				case 1:
					tracking('Cuba', 'More-Amber');
					break;
				case 2:
					tracking('Cuba', 'More-Lounge');
					break;
				case 3:
					tracking('Cuba', 'More-At');
					break;
				default:
					break;
			}
			$('.container').hide();
			document.title = '玩转四场夏日古巴风情派对，一起嗨Fun一夏！';
			$('.share').show();
		});
	}

	function handleMove() {
		submit();
		stage.addChild(bg2, container);
		stage.removeChild(btn1);
		createjs.Tween.get(bar1).to({x:-150},600,createjs.Ease.quintInOut);
		createjs.Tween.get(bar2).to({x:340},600,createjs.Ease.quintInOut);
		createjs.Tween.get(bar3).wait(500).to({x:-110},600,createjs.Ease.quintInOut);
		createjs.Tween.get(bar4).wait(500).to({x:370},600,createjs.Ease.quintInOut);
		tracking('Cuba', 'Go');
	}
	
	function submit() {
		$.ajax({
			url: 'api/index.php',
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'add_user',
				user_from: user_from
			},
			success: function(done) {
				var result = done.result;
				if (result == 1) console.log('Success.');
				else console.log('Failed.');
			}
		});
	}
	
	function getQueryString(name) {
		var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
		var r = window.location.search.substr(1).match(reg);
		if (r != null) return unescape(r[2]);
		return null;
	}
	
	function tracking(cat, action) {
		ga('send', 'event', cat, action);
	}
})();