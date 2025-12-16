$(document).ready(function () {

    // Common function for AJAX requests
    function doAJAXServiceRefuse( params ){
        
        params.url     = 'ajax/RefuseFromService.php';
        params.spinner = params.spinner || function () {
            params.button.children('.button-preloader').toggleClass('--hide');
        };
        
        doAJAX( params );
    }

    // Prepare documents action handler
    function prepareDocs( button ){
        doAJAXServiceRefuse({
            data: {
                action: 'prepare_docs',
                service: button.parent().attr('service'),
                loan_number: button.parent().attr('loan_number'),
                amount: button.parent().attr('amount')
            },
            button: button,
            successCallback: function( response, ajax ){
                ajax.button.next().toggleClass('--hide');
                ajax.button.replaceWith( '<p class="ajax-message--success">' + response.message + '</p>');
            },
        });
    }

    // Send ASP action handler
    function sendASP( button ){
         doAJAXServiceRefuse({
            data: {
                action: 'send_asp',
                service: button.parent().attr('service'),
                loan_number: button.parent().attr('loan_number'),
                amount: button.parent().attr('amount')
            },
            button: button,
            successCallback: function( response, ajax ){
                let wrapper = ajax.button.parent();
                    wrapper.html('<p class="--inline-block">Код из СМС: </p><input type="text" class="action_input-asp_code"/><img src="design/boostra_mini_norm/img/preloader.gif" class="button-preloader --hide" style="vertical-align: sub;">');
                wrapper.children('.action_input-asp_code').on('input', function(){
                    if( $(this).val().length === 4 ){
                        $(this).attr('disabled', 'disabled');
                        confirmASP( $(this) );
                    }
                });

                // @todo Add countdown timer
                setTimeout(function(){
                    ajax.button
                        .toggleClass('--pale')
                        .removeAttr('disabled');
                }, 60000 );
            },
        });
    }

    // Confirm ASP action handler
    function confirmASP( input ){
         doAJAXServiceRefuse({
            data: {
                action: 'confirm_asp',
                asp_code: input.val(),
                service: input.parent().attr('service'),
                loan_number: input.parent().attr('loan_number'),
                amount: input.parent().attr('amount')
            },
            button: input,
            spinner: function(){ input.next().toggleClass('--hide'); },
            successCallback: function( response, ajax ){
                let controlWrapper = ajax.button.parent(),
                    countdown = 4;
                controlWrapper
                    .siblings('.button-exit--with_text')
                        .children('p').text('Закрыть окно');
                controlWrapper
                    .html('<p class="ajax-message--success">' + response.message + '<br><span class="countdown-message">Окно закроется через <span class="timer">5</span> секунд.</span></p>')
                    .siblings('div.wrapper-control')
                        .children().attr('disabled','disabled');
                let countdownInterval = setInterval(function () {
                    controlWrapper.find('.timer').text(countdown--);
                }, 1000 );
                setTimeout(function(){
                    clearInterval( countdownInterval );
                    controlWrapper.find('.countdown-message').remove();
                    $('.action-close').trigger('click');
                }, 5000 );
                
            },
            errorCallback: function (response, ajax) {
               ajax.button.parent().html('<p class="ajax-message--error">Произошла ошибка: ' + response.message + '</p>');
            }
        });
    }

    /** ACTIONS **/

    // Opens modal window
    $('.action-open_service_refuse_modal').on( 'click', function( event ){

        let service = $(event.target).attr('service'),
            loanNumber = $(event.target).children('.input-select_service_refuse_modal__loan-number').val(),
            modalName = 'extra_service-modal--' + service,
            modalWindow = $('#' + modalName + '[data-loan=' + loanNumber + ']' );
        
        modalWindow
            .fadeIn(100)
            .on( 'click', '.action-close', function (){
                $('body').css('overflow', 'hidden');
                $('body').css('overflow', 'scroll');
                modalWindow.fadeOut(100);
            });
    });

    // Prepare documents
    $('.action-prepare_docs').on( 'click', function () {
        
        // Не позволяем пользователю вернуть 100%, так как они массово начали возвращать
        // Мне кажется руководство тыкает пальцем в небо
        if( + $(this).parent().attr('amount') === 100 ){
            alert('Для отказа от услуги просим Вас направить заявление об отказе от услуги на электронную почту info@boostra.ru');
            
            return;
        }
        
        prepareDocs( $(this) );
    });

    // Send ASP
    $('.action-continue').on('click', function(){
        let wrapper = $(this).parent();
        wrapper.html('<button class="button --inline-block action-send_asp --white"><p>Отправить СМС-код</p><img src="design/boostra_mini_norm/img/preloader.gif" class="button-preloader --hide"></button>');
        wrapper.children('.action-send_asp').on('click', function(){
            sendASP( $(this) );
        });
    });
});