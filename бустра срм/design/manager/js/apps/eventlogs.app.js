function EventsApp()
{
    var app = this;
    
    var _init = function(){
        
        $(document).on('click', '.js-event-add-click', function(e){
            
            var event = $(this).data('event');
            var order = $(this).data('order');
            var user = $(this).data('user');
            var manager = $(this).data('manager');

            app.add_event(event, order, user, manager);
        });

        if ($('.js-event-add-load').length > 0)
        {
            var event = $('.js-event-add-load').data('event');
            var order = $('.js-event-add-load').data('order');
            var user = $('.js-event-add-load').data('user');
            var manager = $('.js-event-add-load').data('manager');

            app.add_event(event, order, user, manager);
            
        }

    };
    
    app.add_event = function(event, order, user, manager){
        $.ajax({
            url: 'ajax/eventlogs.php',
            data: {
                event: event,
                order: order,
                user: user,
                manager: manager,
            }
        });
    };
    
    ;(function(){
        _init();
    })();
}
$(function(){
    new EventsApp()
})