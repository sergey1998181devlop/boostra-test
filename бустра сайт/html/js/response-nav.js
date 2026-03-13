var speed = 150;

//open menu
$(".response-open a").on('click', function(){
    var panel = $(this).attr('href');
    $(panel).animate({
        'right':'0'
    }, speed);
    return false;
});

// close menu
$('#response-nav .close').on('click', function () {
     responseNavClose($(this).attr('href'));
     return false;
});

// close menu on click link
$('.navmenu a').on('click', function () {
    responseNavClose($(this).closest('#response-nav'));
});

// Close menu outside the area
$(document).mouseup(function (e) {
    var panel = $('#response-nav');
    if(e.target != panel[0] && !panel.has(e.target).length){
        responseNavClose(panel);
    }
});

function responseNavClose (panel) {
    $(panel).animate({
        'right':'-260px'
    }, speed);
}