$(document).ready( function() {
	$("input:not(:checkbox), textarea, select", ".plup").each(function(i, v) {
		var placeholder = $(v).attr('placeholder');
		$(v).attr('placeholder', '');
		$(v).parent().append('<span class="floating-label">' + placeholder + '</span>');
	});

	$(".plup input").each(function(i, v) {
		adding(v);
	});

	$(".plup input").keyup(function() {
		adding(this);
	});

	function adding(elem) {
		if (elem.value) {
			$(elem).addClass('sup');
			return;
		}
		$(elem).removeClass('sup');
	}
});