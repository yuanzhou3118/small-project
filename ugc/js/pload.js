define(['preload', '$'], function() {
	
	var manifest = [
		// p3
		{id: 't1', src: 'p3/t.png'},
		{id: 'th1', src: 'p3/theme1.png'},
		// {id: 'th2', src: 'p3/theme2.png'},
		// {id: 'th3', src: 'p3/theme3.png'},
		{id: 'sn', src: 'p3/sn.png'},
		// p6
		{id: 't2', src: 'p6/t.png'},
		{id: 'gc', src: 'p6/gc.png'},
		{id: 'sp6', src: 'p6/sp.png'},
		// root
		{id: 'logo', src: 'logo.jpg'},
		// p4
		{id: 'sp4', src: 'p4/sp.png'},
		// p3
		{id: 'bg1', src: 'gif/bgt1.gif'},
		{id: 'bg2', src: 'gif/bgt2.gif'},
		{id: 'bg3', src: 'gif/bgt3.gif'},
		{id: 't1s1', src: 'gif/t1s1.gif'},
		// {id: 't2s1', src: 'gif/t2s1.gif'},
		// {id: 't3s1', src: 'gif/t3s1.gif'},
		{id: 't1s2', src: 'gif/t1s2.gif'},
		// {id: 't2s2', src: 'gif/t2s2.gif'},
		// {id: 't3s2', src: 'gif/t3s2.gif'},
		{id: 't1s3', src: 'gif/t1s3.gif'},
		// {id: 't2s3', src: 'gif/t2s3.gif'},
		// {id: 't3s3', src: 'gif/t3s3.gif'},
		// p7
		{id: 't3', src: 'p7/t.png'},
		{id: 'coupon', src: 'p7/s/coupon.png'},
		{id: 'gn', src: 'p7/s/gn.png'},
		{id: 'sorry', src: 'p7/f/sorry.png'},
		{id: 'pa', src: 'p7/f/pa.png'},
	], th = [], bg = [], ts = [[],[],[]], isLoad = {_is: false};
	
	var loader = new createjs.LoadQueue(false, 'assets/');
	loader.on('fileload', handleLoad);
	loader.on('complete', handleComplete);
	loader.loadManifest(manifest);
	
	function handleLoad() {
		var loaded = loader._numItemsLoaded;
		switch (loaded) {
			case 8:
				var t1 = loader.getResult('t1');
				$('.theme .t').append(t1);
				th[0] = loader.getResult('th1');
				$('.theme .th1').append(th[0]);
				th[1] = th[0];
				// th[1] = loader.getResult('th2');
				// $('.theme .th2').append(th[1]);
				th[2] = th[0];
				// th[2] = loader.getResult('th3');
				// $('.theme .th3').append(th[2]);
				var t2 = loader.getResult('t2');
				$('.rank .t').append(t2);
				var gc = loader.getResult('gc');
				$('.rank .gc').append(gc);
				var sp6 = loader.getResult('sp6');
				$('.rank .sp').append(sp6);
				var sn = loader.getResult('sn');
				$('.theme .sn').append(sn);
				var logo = loader.getResult('logo');
				$('.improve .logo').append(logo);
				var sp4 = loader.getResult('sp4');
				$('.improve .spr').append(sp4);
				break;
			case 14:
				bg[0] = loader.getResult('bg1');
				bg[1] = loader.getResult('bg2');
				bg[2] = loader.getResult('bg3');
				ts[0][0] = loader.getResult('t1s1');
				// ts[1][0] = loader.getResult('t2s1');
				// ts[2][0] = loader.getResult('t3s1');
				ts[0][1] = loader.getResult('t1s2');
				// ts[1][1] = loader.getResult('t2s2');
				// ts[2][1] = loader.getResult('t3s2');
				ts[0][2] = loader.getResult('t1s3');
				// ts[1][2] = loader.getResult('t2s3');
				// ts[2][2] = loader.getResult('t3s3');
				ts[1][0] = ts[0][0];
				ts[1][1] = ts[0][1];
				ts[1][2] = ts[0][2];
				ts[2][0] = ts[0][0];
				ts[2][1] = ts[0][1];
				ts[2][2] = ts[0][2];
				isLoad._is = true;
				break;
			default:
				break;
		}
	}
	
	function handleComplete() {
		var t3 = loader.getResult('t3');
		$('.getcoupon .t').append(t3);
		
		var coupon = loader.getResult('coupon');
		$('.success .coupon').append(coupon);
		
		var gn = loader.getResult('gn');
		$('.success .gn').append(gn);
		
		var sorry = loader.getResult('sorry');
		$('.fail .sorry').append(sorry);
		
		var pa = loader.getResult('pa');
		$('.fail .pa').append(pa);
	}
	
	return {
		th: th,
		bg: bg,
		ts: ts,
		isLoad: isLoad
	}
	
});