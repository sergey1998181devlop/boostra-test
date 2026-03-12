{* Экран подписания кросс-ордера НК *}

{$cross_docs_list = [
    ["verify" => 1, "title" => "Заявлением о предоставлении микрозайма", "url" => "{$config->root_url}/user/docs?action=micro_zaim&organization_id={$cross_organization_id}&loan_amount={$cross_order_amount}"],
    ["verify" => 1, "title" => "Общие условия договора займа", "url" => "{$config->root_url}/files/docs/lord/accept_documents/obschie-usloviya.pdf"],
    ["verify" => 1, "title" => "Правила предоставления займов", "url" => "{$config->root_url}/files/docs/lord/accept_documents/pravila-predostavleniya.pdf"],
    ["verify" => 1, "title" => "Положение АСП", "url" => "{$config->root_url}/files/docs/lord/register_user_docs/polozhenie-asp.pdf"],
    ["verify" => 0, "title" => "Соглашение о регулярных (рекуррентных) платежах", "url" => "{$config->root_url}/user/docs?action=soglasie_recurrent&organization_id={$cross_organization_id}"],
    ["verify" => 1, "title" => "", "url" => "<a href=\"{$config->root_url}/user/docs?action=offer_agreement&order_id={$order->id}&cross_organization_id={$cross_organization_id}\" target=\"_blank\">Соглашение об акцепте оферты</a>, <a href=\"{$config->root_url}/user/docs?action=asp_agreement&order_id={$order->id}&cross_organization_id={$cross_organization_id}\" target=\"_blank\">Соглашение об АСП</a>, <a href=\"{$config->root_url}/user/docs?action=arbitration_agreement&order_id={$order->id}&cross_organization_id={$cross_organization_id}\" target=\"_blank\">Арб.соглашение</a>, <a href=\"{$config->root_url}/user/docs?action=offer_arbitration_cessionary&order_id={$order->id}&cross_organization_id={$cross_organization_id}\" target=\"_blank\">Оферта</a>"],
    ["verify" => 1, "title" => "Договором об условиях предоставления Акционерное общество «Сургутнефтегазбанк» услуги по переводу денежных средств с использованием реквизитов банковской карты с помощью Интернет-ресурса ООО «Бест2пей» (Публичная оферта)'", "url" => "{$config->root_url}/files/docs/Договор_об_условиях_предоставления_Акционерное_общество_«Сургутнефтегазбанк».pdf"],
    ["verify" => 1, "title" => "Индивидуальными условиями договором займа", "url" => "{$cross_order_individual_url}"]
]}


<div id="cross_order_nk_sign" style="display: none;">
    <div class="cross-order-sign__container">

        <img class="cross-order-sign__logo" src="design/{$settings->theme|escape}/img/svg/logo.svg" alt="boostra logo" />

        <img class="cross-order-sign__icon" src="design/{$settings->theme|escape}/img/svg/confetti.svg" alt="Поздравляем!" />

        <div class="cross-order-sign__approved_notice">
            <h2>Отлично! Осталось подписать документы</h2>
            <p>Для получения дополнительных <span id="cross_sign_amount">0</span> рублей<br>на срок 21 день</p>
        </div>

        <div class="cross-order-sign__list_wrapper">

            <div class="cross-order-sign__list_item">
                <label for="cross_order_agree_all">
                    <input type="checkbox" value="1" id="cross_order_agree_all" name="cross_order_agree_all" />
                    <div>
                        <span>Я подписываю указанные ниже документы аналогом собственноручной подписи и согласен с указанными в перечне условиями и документами</span>
                    </div>
                </label>
            </div>

            <div class="cross-order-sign__list_item cross-order-sign__list_item--button">
                <button disabled type="button" id="cross_order_sign_button">
                    Подписать и получить <span id="cross_sign_button_amount">0</span> ₽
                </button>
            </div>

            <div class="documents-spoiler">
                <div class="documents-spoiler__header js-cross-order-spoiler-toggle">
                    <span>Я согласен <span class="documents-spoiler__underlined">со всеми условиями</span></span>
                    <span class="documents-spoiler__arrow">
                        <img src="design/{$settings->theme|escape}/img/svg/up_arrow.svg" alt="arrow" />
                    </span>
                </div>
                <div class="documents-spoiler__content" id="cross_order_spoiler_content">
                    {foreach $cross_docs_list as $key => $doc}
                        <div class="cross-order-sign__list_item">
                            <label for="cross_order_doc_{$key}">
                                <input type="checkbox" 
                                       value="{$doc.verify}" 
                                       id="cross_order_doc_{$key}" 
                                       name="cross_order_doc_{$key}" 
                                       class="cross-order-doc-checkbox {if $doc.verify}js-need-verify{/if}" 
                                       data-verify="{$doc.verify}" />
                                <div>
                                    {if $doc.title}
                                        <a target="_blank" href="{$doc.url}">{$doc.title}</a>
                                    {else}
                                        <span class="cross-order-docs-inline">{$doc.url nofilter}</span>
                                    {/if}
                                </div>
                            </label>
                        </div>
                    {/foreach}

                    {if $isOrganic}
                        <div class="cross-order-sign__list_item cross-order-sign__list_item--extra-service">
                            {include file="credit_doctor/credit_doctor_checkbox.tpl" idkey="cross_{$order->id}"}
                            <input type="hidden" id="credit_doctor_hiddencross_{$order->id}" value="0" />
                        </div>
                        <div class="cross-order-sign__list_item cross-order-sign__list_item--extra-service">
                            {include file="tv_medical/tv_medical_checkbox.tpl" idkey="cross_{$order->id}"}
                            <input type="hidden" id="tv_medical_hiddencross_{$order->id}" value="0" />
                        </div>
                    {/if}
                </div>
            </div>

            <button type="button" id="cross_order_decline_button" class="cross-order-sign__decline-button">
                Получить позже
            </button>
        </div>

        <input type="hidden" id="cross_order_parent_id" value="" />
        <input type="hidden" id="cross_order_amount_value" value="" />
    </div>
</div>

<div class="mfp-hide">
    <div class="cross-order-sign__sms-block" id="cross_order_sms_block">
        <div class="cross-order-sign__sms-actions">
            <span class="info">На Ваш телефон <span id="cross_order_phone"></span> было отправлено СМС-сообщение с кодом для подтверждения.</span>
            <div id="cross_order_sms">
                <div>
                    <input type="text"
                           inputmode="numeric"
                           autofocus
                           autocomplete="one-time-code"
                           id="cross_order_sms_code"
                           maxlength="4"
                           placeholder="Код из СМС"
                           class="cross-order-sign__input"
                           aria-required="true" />
                    <span class="cross-order-sign__error" id="cross_order_sms_error"></span>
                </div>
                <div class="cross-order-sign__sms-resend" id="cross_order_sms_resend"></div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="design/{$settings->theme}/css/cross_order_nk_sign.css?v=1.06">

<script type="text/javascript">
{literal}
var CrossOrderNKSign = {
    parentOrderId: null,
    crossAmount: 0,
    userPhone: '',

    show: function(parentOrderId, crossAmount, userPhone) {
        this.parentOrderId = parentOrderId;
        this.crossAmount = crossAmount;
        this.userPhone = userPhone || '';
        $('#cross_sign_amount').text(this.formatNumber(crossAmount));
        $('#cross_sign_button_amount').text(this.formatNumber(crossAmount));
        $('#cross_order_parent_id').val(parentOrderId);
        $('#cross_order_amount_value').val(crossAmount);
        $('#cross_order_phone').text(this.formatPhone(userPhone));

        $("input[name='credit_doctor_check']").prop('checked', false).trigger('change');
        $("input[name='tv_medical_check']").prop('checked', false).trigger('change');

        $('#cross_order_nk_sign').fadeIn(300);

        if (typeof sendMetric !== 'undefined') {
            sendMetric('reachGoal', 'cross_order_nk_sign_show');
        }
    },

    hide: function() {
        $('#cross_order_nk_sign').fadeOut(300);
    },

    formatNumber: function(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
    },

    formatPhone: function(phone) {
        if (!phone) return '';
        var cleaned = phone.replace(/\D/g, '');
        if (cleaned.length === 11) {
            return '+' + cleaned[0] + ' (' + cleaned.substring(1, 4) + ') ' +
                   cleaned.substring(4, 7) + '-' + cleaned.substring(7, 9) + '-' + cleaned.substring(9);
        }
        return phone;
    }
};

function cross_order_open_sms_popup() {
    cross_order_send_sms();
}

cross_order_send_sms = function() {
    const _phone = CrossOrderNKSign.userPhone;

    $.ajax({
        url: 'ajax/sms.php',
        data: {
            action: 'send',
            phone: _phone,
            flag: 'autoconfirm',
        },
        success: function(resp) {
            if (!!resp.error) {
                if (resp.error === 'sms_time') {
                    CrossOrderNKSign.hide();
                    $.magnificPopup.open({
                        items: { src: '#cross_order_sms_block' },
                        type: 'inline',
                        showCloseBtn: false,
                        modal: true,
                    });
                    cross_order_set_timer(resp.time_left);
                } else {
                    console.log(resp);
                }
            } else {
                CrossOrderNKSign.hide();
                $.magnificPopup.open({
                    items: { src: '#cross_order_sms_block' },
                    type: 'inline',
                    showCloseBtn: false,
                    modal: true,
                });
                cross_order_set_timer(resp.time_left);
                initWebOTPOnPage({ autoSubmit: true });
                sendMetric('reachGoal', 'sms_sent');

                if (resp.developer_code) {
                    $('#cross_order_sms_code').val(resp.developer_code);
                }
            }
        },
        error: function() {
            alert('Ошибка отправки СМС. Попробуйте позже.');
        }
    });
};
cross_order_check_sms = function() {
    const _data = {
        action: 'check_autoconfirm_cross',
        phone: CrossOrderNKSign.userPhone,
        code: $('#cross_order_sms_code').val(),
        parent_order_id: CrossOrderNKSign.parentOrderId,
        cross_amount: CrossOrderNKSign.crossAmount,
        is_user_credit_doctor: $('#credit_doctor_hiddencross_{/literal}{$order->id}{literal}').val() || '0',
        is_tv_medical: $('#tv_medical_hiddencross_{/literal}{$order->id}{literal}').val() || '0'
    };

    $.ajax({
        url: 'ajax/sms.php',
        data: _data,
        beforeSend: function() {
            $('#cross_order_sms_block').addClass('loading');
        },
        success: function(resp) {
            $('#cross_order_sms_block').removeClass('loading');

            if (resp.success) {
                $('#cross_order_sms_block').removeClass('error');
                $('#cross_order_sms_error').html('');

                try { $.magnificPopup.close(); } catch(e) {}

                CrossOrderNKSign.hide();

                $('#score-info').fadeIn(200);
                if ($('#sbp-bank-selection-wrapper').length > 0) {
                    $('#sbp-bank-selection-wrapper').fadeIn(200);
                } else {
                    $('#card-add-section').removeClass('is-hidden').fadeIn(200);
                }
            } else {
                $('#cross_order_sms_block').removeClass('loading');
                $('#cross_order_sms_error').html(resp.error || 'Код не совпадает');
                $('#cross_order_sms_block').addClass('error');
            }
        },
        error: function() {
            $('#cross_order_sms_block').removeClass('loading');
            alert('Ошибка проверки кода. Попробуйте позже.');
        }
    });
};

let cross_order_sms_timer;
cross_order_set_timer = function(_seconds) {
    clearInterval(cross_order_sms_timer);
    cross_order_sms_timer = setInterval(function() {
        _seconds--;
        if (_seconds > 0) {
            var _str = '<span>Повторно отправить код можно через ' + _seconds + 'сек</span>';
            $('#cross_order_sms_resend').addClass('inactive').html(_str).show();
        } else {
            $('#cross_order_sms_resend').removeClass('inactive')
                .html('<a class="js-cross-order-resend-sms" href="#">Отправить код еще раз</a>').show();
            clearInterval(cross_order_sms_timer);
        }
    }, 1000);
};

function cross_order_decline() {
    if (typeof sendMetric !== 'undefined') {
        sendMetric('reachGoal', 'cross_order_nk_sign_decline');
    }
    CrossOrderNKSign.hide();

    // Автоматически отмечаем все обязательные чекбоксы
    if (typeof $ !== 'undefined') {
        $('.js-need-verify').prop('checked', true);
        $('#not_checked_info').hide();
    }

    $('#score-info').fadeIn(200);
    if ($('#sbp-bank-selection-wrapper').length > 0) {
        $('#sbp-bank-selection-wrapper').fadeIn(200);
    } else {
        $('#card-add-section').removeClass('is-hidden').fadeIn(200);
    }
}

function validateCrossOrderCheckboxes() {

    const $requiredCheckboxes = $('.cross-order-doc-checkbox[data-verify="1"]');
    const $checkedRequired = $requiredCheckboxes.filter(':checked');
    const allRequiredChecked = $requiredCheckboxes.length === $checkedRequired.length;

    const $allCheckboxes = $('.cross-order-doc-checkbox');
    const allChecked = $allCheckboxes.length === $allCheckboxes.filter(':checked').length;
    
    $('#cross_order_agree_all').prop('checked', allChecked);

    $('#cross_order_sign_button').prop('disabled', !allRequiredChecked);
}

$('#cross_order_agree_all').on('change', function() {
    $('.cross-order-doc-checkbox').prop('checked', this.checked);
    $("input[name='credit_doctor_check'], input[name='tv_medical_check']").prop('checked', this.checked).trigger('change');
    validateCrossOrderCheckboxes();
});

$('.cross-order-doc-checkbox').on('change', function() {
    validateCrossOrderCheckboxes();
});

$('#cross_order_sign_button').on('click', function() {
    if (!$(this).prop('disabled')) {
        cross_order_open_sms_popup();
    }
});

$('#cross_order_decline_button').on('click', function() {
    cross_order_decline();
});

$(document).on('input', '#cross_order_sms_code', function() {
    const _v = $(this).val();
    if (_v.length === 4) {
        cross_order_check_sms();
    }
});

$(document).on('click', '.js-cross-order-resend-sms', function(e) {
    e.preventDefault();
    cross_order_send_sms();
    return false;
});

$(document).on('click', '.js-cross-order-spoiler-toggle', function() {
    const content = document.getElementById('cross_order_spoiler_content');
    const arrow = document.querySelector('.documents-spoiler__arrow');

    if (content && content.classList.contains('open')) {
        content.classList.remove('open');
        if (arrow) arrow.classList.remove('open');
    } else if (content) {
        content.classList.add('open');
        if (arrow) arrow.classList.add('open');
    }
});
{/literal}
</script>

{if !$credit_doctor_js_loaded}
    <script src="design/{$settings->theme}/js/creditdoctor_modal.app.js?v=1.00" type="text/javascript"></script>
    {$credit_doctor_js_loaded = true scope=parent}
{/if}

{if !$credit_doctor_popup_loaded}
    {include file="credit_doctor/credit_doctor_popup.tpl"}
    {$credit_doctor_popup_loaded = true scope=parent}
{/if}
