$(document).ready(function(){
	$('a.popup').magnificPopup({
		type: 'inline'
	});

	$('.mfp').click(function(e) {
		if ($(e.target).closest(".box").length) {
			return;
		}
		var magnificPopup = $.magnificPopup.instance; 
		magnificPopup.close();
	});
});