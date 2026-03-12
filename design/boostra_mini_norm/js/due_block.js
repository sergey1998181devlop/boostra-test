$(document).ready(function () {
    $.magnificPopup.open({
        items: {
            src: '#due_block'
        },
        type: 'inline',
        showCloseBtn: false,
        closeOnBgClick: false,
        enableEscapeKey: false,
        callbacks: {
            open: function() {
                setTimeout(function() {
                    $('#due_block .modal_title .close-modal').show();
                }, 5000)
            },
        }
    });


    $('body').on('click', '#due_prolongation_start', function() {
        ym(45594498,'reachGoal','banner_collection_pay')
        $.magnificPopup.close();
        $('.user_payment_form #button_1').click();
    })

    $('body').on('click', '#due_close_start', function() {
        ym(45594498,'reachGoal','banner_collection_pay')
        $.magnificPopup.close();
        console.log($('.full_payment_button[data-order_id="'+$(this).parents('#due_block').attr('data-order_id')+'"]'))
        $('.full_payment_button[data-order_id="'+$(this).parents('#due_block').attr('data-order_id')+'"]').click();
    })
})