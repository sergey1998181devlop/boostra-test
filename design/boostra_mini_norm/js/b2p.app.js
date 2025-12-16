/**
 * Функция для генерации и открытия ссылки СБП
 * @param {Event} event
 */
function generateAndOpenSbpLink(event) {
    const sbpBankModal = document.querySelector('#sbp_banks_modal');
    const bankIdInput = document.querySelector('#bank_id');

    if (sbpBankModal) {

        // Если не выбран банк
        if (!bankIdInput.value) {
            const selectedCardId = document.querySelector('#selected_card_id');
            const selectedCardType = document.querySelector('#selected_card_type');

            // Если нет выбранного счета, показываем выбор банка
            if (!selectedCardId || !selectedCardType || selectedCardType.value !== 'sbp' || !selectedCardId.value || selectedCardId.value === '0') {
                event.preventDefault();
                openChooseSbpBankModal();
            }
        }
        return;
    }

    event.preventDefault();
    event.stopPropagation();

    // Показываем индикатор загрузки на кнопке
    var button = event.target;
    var originalText = button.innerHTML;
    button.innerHTML = 'Генерируем ссылку...';
    button.disabled = true;

    $.ajax({
        url: 'ajax/b2p_payment.php',
        method: 'GET',
        data: {
            action: 'attach_sbp_registration'
        },
        success: function (response) {
            if (response.success && response.link) {
                // Открываем ссылку СБП в новом окне
                window.open(response.link, '_blank');
            } else {
                // Показываем ошибку
                console.log('SBP link generation failed:', response);
                alert(response.error || 'Возникла ошибка при генерации ссылки СБП. Попробуйте еще раз или обратитесь в поддержку.');
            }
        },
        error: function() {
            alert('Произошла ошибка при генерации ссылки СБП. Попробуйте позже.');
        },
        complete: function() {
            // Возвращаем кнопке первоначальный вид
            button.innerHTML = originalText;
            button.disabled = false;
        }
    });
}

function B2pApp()
{
    var app = this;
    
    _init_add_card = function(){
        $(document).on('click', '.js-b2p-add-card', function(e){
            localStorage.openCardModal = false
            e.preventDefault();

            if ($(document).find('.js-need-verify').not(':checked').length > 0) {
                $(document).find('#not_checked_info').show();
                console.log($(document).find('.js-need-verify').not(':checked').length);
                return
            }

            // Получаем значение согласия на рекуррентные платежи
            let recurringConsent = 1; // по умолчанию согласие
            const recurrentCheckbox = $(document).find('#recurrent');
            if (recurrentCheckbox.length > 0) {
                recurringConsent = recurrentCheckbox.is(':checked') ? 1 : 0;
            }

            $(document).find('.docs_wrapper').hide();
            $(document).find('#not_checked_info').hide();
            var $this = $(this);
            var organization_id = $(this).data('organization_id') || 1;
            $(this).hide();

            $('.security-text').hide();
            $('.add_card__title').hide();
            /* $('.top_menu__logo').hide(); */
            $.ajax({
                url: '/ajax/b2p_payment.php',
                data: {
                    action: 'attach_card',
                    organization_id: organization_id,
                    recurring_consent: recurringConsent
                },
                success: function(resp){
                    if (!!resp.link)
                    {
                        const iframe = document.getElementById('add_card_frame');
                        $('#add_card_frame').attr('src', resp.link);
                        iframe.style.display = 'block';
                        iframe.addEventListener('load', function () {
                            try {
                                const currentURL = iframe.contentWindow.location.href;
                                console.log('Текущий URL дочернего фрейма:', currentURL);
                                location.reload();
                            } catch (e) {
                                console.error('Не удалось получить URL дочернего фрейма:', e);
                            }
                        });
                        //location.href = resp.link;
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