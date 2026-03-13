/**
 * Универсальный плагин обратного таймера
 */
(function($) {
    $.fn.timerOut = function(options) {
        options = $.extend({}, $.fn.timerOut.defaults, options || {});
        return $(this).each(function() {
            let _this = this,
                second = options.second,
                interval = setInterval(updateTimer, 1000);

            if (typeof(options.onStart) == 'function') {
                options.onStart.call(_this);
            }

            $(_this).html("<div class='timerOut'></div>");

            function updateTimer() {
                second--;
                $(_this).find('.timerOut').text(second);
                if (second === 0) {
                    clearInterval(interval);
                    $(_this).find('.timerOut').remove();

                    if (typeof(options.onComplete) == 'function') {
                        options.onComplete.call(_this);
                    }
                }
            }
        });
    };
    $.fn.timerOut.defaults = {
        second: 30,  // how long it should take to count between the target numbers
        onComplete: null,  // callback method for when the element finishes updating
        onStart: null,
    };
})(jQuery);

/**
 * Универсальная проверка смс кода с функцией обратного вызова
 * @param phone
 * @param code
 * @param _callBack
 */
function validateSMSCode (phone, code, _callBack) {
    $.ajax({
        url: 'ajax/sms.php?action=check',
        data: {
            code: code,
            phone: phone,
        },
        success: function (resp) {
            if(resp.soap_fault) {
                alert(resp.error);
            } else {
                _callBack(resp);
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
            alert(error);
            console.log(error);
        },
    });
};

/**
 * Авторизация по Ajax, с помощью телефона и смс кода
 * @param data
 * @param _callBack
 */
function sendLogin(data, _callBack = null)
{
    $.ajax({
        url: 'user/login',
        data: data,
        method: 'POST',
        success: function (resp) {
            if (typeof(_callBack) == 'function') {
                _callBack(resp);
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
            alert(error);
            console.log(error);
        },
    });
}

/**
 * Валидация телефона в модальном окне
 */
function validatePhone() {
    sendMetric('reachGoal','new_form_aprove');
    $.ajax({
        url: 'ajax/loginCodeByCall.php',
        data: $("#modal_phone input, #modal_phone textarea").serialize(),
        method: 'POST',
        beforeSend: function () {
          $("#modal_phone").addClass('loading');
          $("#modal_phone button").prop('disabled', true);
          $('input[name="code"]').remove();
        },
        success: function (resp) {
            if (resp.error) {
                if (resp.soap_fault) {
                    alert(resp.error);
                } else if (resp.error === 'sms_time') {
                    $(".timerOutWrapper").timerOut({
                        second: resp['time_left']
                    });
                    $("#modal_phone button").prop('disabled', false).text('Отправить повторно');
                } else if(resp.error_type === 'user_not_find') {
                    $("#main_page_form").submit();
                }
            } else {
                $('#modal_input_sms_code').remove();
                $("#modal_phone .modal-content").append("<div id='modal_input_sms_code' class='input-inline input-control'><input name='code' value='' /><small>Мы вам уже звоним. Внимательно прослушайте и введите код, продиктованный голосовым роботом</small></div>");
                $('input[name="code"]').inputmask({
                    mask: "9999",
                    oncomplete: function () {
                        validateSMSCode($("#modal_phone [name='phone']").val(), $("#modal_phone [name='code']").val(), function (resp) {
                            $('input[name="code"]').closest('div').removeClass('has-error');
                            if (resp.success) {
                                $("#modal_phone").addClass('loading');
                                    let login_data = {
                                        key: $('input[name="code"]').val(),
                                        real_phone: $("#modal_phone input[name='phone']").val(),
                                        login: 1,
                                        ajax: 1,
                                    }
                                    sendLogin(login_data, function (resp_login) {
                                        if (resp_login.redirect) {
                                            window.location.href = resp_login.redirect;
                                        }
                                        $("#modal_phone").remove('loading');
                                    });
                            } else {
                                $('input[name="code"]').closest('div').addClass('has-error');
                            }
                        });
                    }
                });

                $(".timerOutWrapper").timerOut({
                    onComplete: function () {
                        $("#modal_phone button").prop('disabled', false).text('Отправить повторно');
                    }
                });

                if(resp['developer_code']) {
                    $('input[name="code"]').val(resp['developer_code']);
                }
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
            alert(error);
            console.log(error);
        },
    }).done(function () {
        $("#modal_phone").removeClass('loading');
    });
}

$(document).ready(function () {
   $(document).on('click', '.toggle-password', function () {
       let elemClass = $(this).hasClass('active') ? 'bi-eye-slash' : 'bi-eye',
           type = $(this).hasClass('active') ? 'password' : 'text';

       $(this)
           .toggleClass('active')
           .removeClass('bi-eye-slash bi-eye')
           .addClass(elemClass);

       $(this)
           .closest('.form-control')
           .find('input')
           .attr('type', type);
   });

    document.querySelectorAll('.partner_title').forEach(function(element) {
        element.addEventListener('click', function() {
            var modalId = this.getAttribute('data-modal');
            document.getElementById(modalId).style.display = 'block';
        });
    });

    document.querySelectorAll('.close').forEach(function(element) {
        element.addEventListener('click', function() {
            var modalId = this.getAttribute('data-modal');
            document.getElementById(modalId).style.display = 'none';
        });
    });

    window.addEventListener('click', function(event) {
        if (event.target.className === 'modal' && event.target.id !== 'sbp_banks_modal') {
            event.target.style.display = 'none';
        }
    });

    $('.js-bki-consent-checkbox').on('change', function() {
        var consent = $(this).is(':checked') ? 1 : 0;
        $.ajax({
            url: 'ajax/user.php?action=bki_consent',
            method: 'POST',
            data: { consent: consent },
            dataType: 'json',
            success: function(response) {
                if (!response.success) {
                    console.error('Failed to save BKI consent:', response.error);
                }
            },
            error: function() {
                console.error('AJAX error while saving BKI consent.');
            }
        });
    });

});

$(document).on('click', '[data-modal_mf]', function (e) {
    e.preventDefault();
    let idModal = $(this).data('modal_mf');

    $.magnificPopup.open({
        items: {
            src: '#' + idModal
        },
        type: 'inline',
        showCloseBtn: true
    });
});

Object.defineProperty(String.prototype, 'capitalize', {
    value: function () {
        return this.charAt(0).toUpperCase() + this.slice(1);
    },
    enumerable: false
});

// Для возможности отключать показ окошка с помощью на определённых страницах
if (typeof window.inactivityPopupEnabled === 'undefined') {
    window.inactivityPopupEnabled = true;
}
// Function to initialize the inactivity popup
function initializeInactivityPopup() {
    let inactivityLimit = 120000; // 2 minutes
    let inactivityTimer;

    // Function to reset the inactivity timer
    function resetTimer() {
        clearTimeout(inactivityTimer);
        inactivityTimer = setTimeout(showInactivityModal, inactivityLimit);
    }

    function showInactivityModal() {
        if (!window.inactivityPopupEnabled)
            return;

        $.magnificPopup.open({
            items: {
                src: '#inactivity-modal',
                type: 'inline'
            },
            closeBtnInside: true,
            closeOnBgClick: true,
        });
    }

    $(document).on('click', '#close-popup', function () {
        $.magnificPopup.close();
    });

    // Reset the timer when user interacts with the page
    $(document).on('mousemove keypress click scroll', resetTimer);

    // Start the timer when the page loads
    resetTimer();
}

// Initialize the inactivity popup on the account page and on the register page
if (window.location.pathname === '/account' || window.location.pathname === '/register') {
    initializeInactivityPopup();
}

$(document).ready(function() {
    $('.accordion-title').click(function() {
        var $accordionItem = $(this).parent();
        var $accordionContent = $(this).next('.accordion-content');

        // Если секция уже открыта
        if ($accordionItem.hasClass('active')) {
            $accordionItem.removeClass('active');
            $accordionContent.css('max-height', 0);
            $accordionContent.css('padding', '0 15px');
        } else {
            // Закрываем все другие открытые секции
            $('.accordion-item.active').removeClass('active').children('.accordion-content').css('max-height', 0).css('padding', '0 15px');

            // Открываем выбранную секцию
            $accordionItem.addClass('active');
            $accordionContent.css('max-height', $accordionContent.prop('scrollHeight') + 'px');
            $accordionContent.css('padding', '10px 15px');
          
            const yandexGoalId = $accordionItem.data('yandex_goal_id');
            
            if (yandexGoalId) {
                sendMetric('reachGoal', yandexGoalId);
            }
        }
    });
});