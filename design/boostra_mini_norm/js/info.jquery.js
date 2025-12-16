$(document).ready(function () {
	$('#questions .title').click(function () {
		var el = $(this).parent();
		if (el.hasClass("active")) {
			return;
		}
		$('#questions .active .answer').animate({height: "0"}, 100);
		$('#questions .active').removeClass('active');
		el.addClass('active');
		el.find(".answer").css('height', 'auto');
		var h = el.find(".answer").height();
		el.find(".answer").css('height', '0');
		el.find(".answer").animate({height: h + 25 + "px"}, 300);
	});
});