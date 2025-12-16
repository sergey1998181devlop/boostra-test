function B2pApp()
{
    var app = this;
    
    _init_add_card = function(){
        $(document).on('click', '.js-b2p-add-card', function(e){
            e.preventDefault();

            if ($(document).find('.js-need-verify').not(':checked').length > 0) {
                $(document).find('#not_checked_info').show();
                return;
            }

            $(document).find('.docs_wrapper').hide();
            $(document).find('#not_checked_info').hide();

            var $this = $(this);

            // Получаем значение согласия на рекуррентные платежи
            var recurring_consent = 1; // по умолчанию согласие
            const recurrentCheckbox = $(document).find('#recurrent');
            if (recurrentCheckbox.length > 0) {
                recurring_consent = recurrentCheckbox.is(':checked') ? 1 : 0;
            }

            $.ajax({
                url: '/ajax/b2p_payment.php',
                data: {
                    action: 'attach_card',
                    recurring_consent: recurring_consent
                },
                success: function(resp){
                    if (!!resp.link)
                    {
                        location.href = resp.link;
                        return true;
                    }
                    else
                    {
                        e.preventDefault();
                    }
                }
            })
        })
    };
    
    ;(function(){
        _init_add_card();
    })();
}
$(function(){
    new B2pApp();
})