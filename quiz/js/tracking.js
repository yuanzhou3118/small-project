function tracking(cat, action) {
	ga('send', 'event', cat, action);
}
$('.start')[0].addEventListener('click', function() {
	tracking('Quiz', 'Testing');
});
$('#q-1')[0].addEventListener('click', function() {
	tracking('Quiz', 'QA-1');
});
$('#q-2')[0].addEventListener('click', function() {
	tracking('Quiz', 'QA-2');
});
$('#q-3')[0].addEventListener('click', function() {
	tracking('Quiz', 'QA-3');
});
$('#q-4')[0].addEventListener('click', function() {
	tracking('Quiz', 'QA-4');
});
$('#q-5')[0].addEventListener('click', function() {
	tracking('Quiz', 'QA-5');
});
$('#q-6')[0].addEventListener('click', function() {
	tracking('Quiz', 'QA-6');
});
$('.se')[0].addEventListener('click', function() {
	tracking('Quiz', 'Sharing');
});
$('.sh')[0].addEventListener('click', function() {
	tracking('Quiz', 'LuckyDraw');
});
$('.getCode')[0].addEventListener('click', function() {
	tracking('Quiz', 'SMS-Code');
});
$('.submit-tel')[0].addEventListener('click', function() {
	tracking('Quiz', 'PN-Submit');
});
$('.submit-info')[0].addEventListener('click', function() {
	tracking('Quiz', 'Profile-Submit');
});
$('.again')[0].addEventListener('click', function() {
	tracking('Quiz', 'Play-Again');
});