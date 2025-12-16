$(document).ready(function(){

    $("input[name='pay[summ]']").inputmask({
        "mask": "99999",
        clearIncomplete: true
    });

	$("#private form").submit(function (event) {
        event.preventDefault();
		$.magnificPopup.open({
			items: {
				src: '#check' 
			},
			type: 'inline'
		});
	});
});