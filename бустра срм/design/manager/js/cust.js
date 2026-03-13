$(document).ready(function(){
	$("input[name='from']").datepicker({dateFormat: "yy-mm-dd"});
	$("input[name='to']").datepicker({dateFormat: "yy-mm-dd"});

	// запретить закрытие dropdown по клику внутри
	$('.dropdown-click-over .dropdown-menu').click(function (e) {
		e.stopPropagation();
	});
});

function updateSettings(key, value) {
	$.ajax({
		url: "ajax/settings.php?action=update",
		data: {
			key,value
		},
		dataType: 'json',
		method : 'POST',
		beforeSend: function () {
			$('.preloader').show();
		},
		success: function(){
			$('.preloader').hide();
		}
	});
}
