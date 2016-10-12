({
    baseUrl: "./",
    paths: {
		$: 'lib/zepto.min',
		wx: 'lib/jweixin-1.0.0',
		cjs: 'lib/createjs.min',
		fastclick: 'lib/fastclick.min',
		md5: 'lib/md5'
	},
	shim: {
		'modules/app': ['$'],
		'modules/fn': ['$', 'cjs'],
		'modules/answer': ['$', 'cjs', 'md5']
	},
    name: "modules/main",
    out: "app.min.js",
    optimize: "uglify"
})