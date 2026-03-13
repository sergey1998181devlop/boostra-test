$(function(){
	// Моб. меню
	$('header .mob_menu_link').click(function(e){
		e.preventDefault()

		if( $(this).hasClass('active') ){
			$(this).removeClass('active')
			$('header .menu').slideUp(200)
		} else{
			$(this).addClass('active')
			$('header .menu').slideDown(300)
		}
    })
})