require.config({
	baseUrl: 'scripts/',
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
	}
});
require(['require', 'modules/app', 'modules/answer'], function(require) {
	
	$(function() {
		var FastClick = require('fastclick');
		FastClick.attach(document.body);
	});
	
});