{$canonical="/pay" scope=parent}
<script src="design/{$settings->theme|escape}/js/prolongation.app.js?v=1.18" type="text/javascript"></script>

<style>
    .main-div {
        width: 100%;
        min-width: 300px;
        background-image: url("design/boostra_mini_norm/img/background.jpg");
        background-repeat: no-repeat;
        background-size: 100% 100%;
        height: 450px;
        display: flex;
        justify-content: space-between;
        flex-direction: column;
        filter: brightness(100%);
        color: #fff;
        font-weight: 700;
        margin-bottom: 20px;
    }
    .main-div-txt {
        max-width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        gap: 20px;
        margin-top: 60px;
    }

    .main-div-txt > h3 {
        font-size: 3.5vw;
        text-align: center;
    }

    .main-div-txt > p {
        font-weight: 600;
        line-height: 1.3;
        margin-bottom: 0.5rem;
        font-size: 20px;
        text-align: center;
    }

    .pay-div {
        width: 60%;
        min-width: 200px;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        gap: 20px;
    }

    .pay-div-input {

        width: 60%;
        border: 1px solid;
        border-radius: 5px;
        text-align: center;
        padding: 20px;
        box-sizing: border-box;
    }

    .pay-div > button {
        text-align: center;
        padding: 15px;
        border: 1px solid #d7d7d7;
        width: 100%;
        background: #cba57f;

    }

    .parent-div {
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        margin: 20px 0 20px 0;
    }
    .main-div-txt-right{
        text-align: right;
        padding-right: 27px;
        font-size: 15px;
    }
    .main-div-txt-grace{
        text-align: center;
        font-size: 15px;
        margin: 20px 0;
    }
    @media (max-width: 768px) {
        .main-div {
            min-height: 200px;
            height: unset;

        }

        .main-div-txt > h3 {
            font-size: 5vw;
        }

        .main-div-txt > p {
            font-size: 15px;
        }
        .main-div-txt-right{
            font-size: 10px;
        }
    }

</style>
{$total = $user->balance->ostatok_od + $user->balance->ostatok_percents + $user->balance->ostatok_peni +$user->balance->penalty}
<div class="parent-div">
    {if $user->balance->zaim_number == 'Нет открытых договоров' || empty($user->balance->zaim_number) || $total <= 0}
    <div class="main-div">
        <div class="main-div-txt">
            <h3>Нет задолженности по договору</h3>
        </div>
    </div>
    {elseif ($user->balance->last_prolongation == 2 && $user->balance->prolongation_count > 5) && $smsData->type == 'sms-prolongation'}
    <div class="main-div">
        <div class="main-div-txt">
            <h4> Уважаемый клиент, Вы использовали лимит пролонгаций по данному займу.
                <br />
                Для формирования позитивной кредитной истории срочно погасите заем!
            </h4>
        </div>
    </div>
    {else}
    <div class="main-div">
        <div class="main-div-txt">
            <h3>Уважаемый (ая) {$user->lastname} {$user->firstname} {$user->patronymic}</h3>
            <p> Ваша задолженность по договору
                <strong>№ {$user->balance->zaim_number}</strong>
                на текущий момент составляет:
            </p>
            <h3>{$total} руб.</h3>
            {if  $smsData->type == 'sms-prolongation'}
                <div>
                    Для продления займа внесите
                    {$order_data->balance->ostatok_percents
                    + $order_data->balance->ostatok_peni
                    + $order_data->balance->calc_percents
                    + ($order_data->order->additional_service_multipolis|intval * $order_data->multipolis_amount)
                    + ($order_data->order->additional_service_tv_med|intval * $tv_medical_price)
                    }
                    &nbsp;руб
                </div>
            {/if}
        </div>
        <div class="main-div-txt-grace">
            <p>
                Оплата со скидкой доступна в личном кабинете
            </p>
        </div>
        <div class="main-div-txt-right">
            <p>
                *Для обновления суммы задолженности
            </p>
            <p>
                перезагрузите страницу через 10 минут
            </p>
        </div>
    </div>
    {/if}

    {if $smsData->type == 'sms-payment' || $smsData->type == ''}
    <div class="pay-div form form-control">
        <input type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '')" value="{$total}"
               class="pay-div-input">
        <input type="hidden" value="{$user->balance->zaim_number}" id="zaim-id">
        <input type="hidden" value="{$user->id}" id="user-id">
        <input type="hidden" value="{$order_data->id}" id="order-id">
        <input type="hidden" value="{$total}" class="total-value">
        <button class="pay_button pay_button_sum">Оплатить задолженность</button>
    </div>
    {elseif $smsData->type == 'sms-prolongation' && $user->balance->last_prolongation != 2 && $user->balance->prolongation_count <= 5}
    <div class="pay-div form form-control">
    <button id="button_{$counter}" class="payment_button  big get_prolongation_modal js-save-click pay_button prolongation_button"
            data-order_id="{$order_data->id}"
            data-user="{$user->id}"
            data-event="1"
            type="button"
            data-number="{$user->balance->zaim_number}">
        Минимальный платеж
        <input type="hidden" id="number_{$counter}" value="{$user->balance->zaim_number}">
        {/if}
    </div>
    <button style="display: none!important;" class="js-prolongation-open-modal js-save-click" data-order_id="{$order_data->id}" data-user="{$user->id}" data-event="1" type="button" data-number="{$user->balance->zaim_number}"></button>
</div>
<div id="ajax_prolongation__content"></div>
<script>
    $(".pay-div-input").change(function () {
        var inputValue = $('.total-value').val();

        if (+($(this).val()) > +inputValue) {
            $(this).val(inputValue);
        }
    })
    $('.pay_button_sum').click(function () {
        let number = $('#zaim-id').val()
        let orderId = $('#order-id').val()
        let userId = $('#user-id').val()
        let action = 'get_payment_link'
        let amount = $('.pay-div-input').val()
        $.ajax({
            url: 'ajax/b2p_payment.php',
            data: {
                number: number,
                action: action,
                amount: amount,
                order_id: orderId,
                user_id: userId
            },
            beforeSend: function () {
                $('.main-div').addClass('loading');
            },
            success: function (resp) {
                if (resp.payment_link)
                    location.href = resp.payment_link;
                else
                    alert(resp.error);
            }
        })
    })
    let nowHour = new Date().getHours();
    let today = new Date().getDay();
    let userUtmSource = "{$user->utm_source|escape:'javascript'}";
    var isOrganic = ['Boostra', '', 'direct1', 'direct_seo', 'direct', 'direct3'].includes(userUtmSource.trim());
    let isBetween8and19 = (nowHour >= 8 && nowHour <= 18);
    let crmAutoApprove = "{$user->order['utm_source']}" === 'crm_auto_approve';

    let shouldCheck = !isOrganic || (isOrganic && !isBetween8and19) || crmAutoApprove;

    function shouldShowElements(utmSource, hour, day) {
        var isOrganic = ['Boostra', '', 'direct1', 'direct_seo', 'direct', 'direct3'].includes(utmSource.trim());
        let isOutsideRestrictedHours = (hour >= 10 && hour < 17);
        let isWeekday = (day !== 0 && day !== 6);

        return isOrganic && isOutsideRestrictedHours && isWeekday;
    }

    function toggleVisibility(elementId, shouldShow) {
        let element = document.getElementById(elementId);
        if (element) {
            element.style.display = shouldShow ? 'block' : 'none';
        }
    }

    function setCheckboxState(checkboxId, shouldCheck) {
        let checkbox = document.getElementById(checkboxId);
        if (checkbox) {
            if (shouldCheck) {
                checkbox.setAttribute("checked", "checked");
            } else {
                checkbox.removeAttribute("checked");
            }
        }
    }
    $(document).on('click',".get_prolongation_modal",function() {
        $("body").addClass('is_loading');
        let order_id = $(this).data('order_id'),
            number = $(this).data('number'),
            tv_medical_tariff_id = 0,
            user_id = $(this).data('user'),
            counter = $(this),
            $button = counter.find('input[type=hidden]').val();
        let tv_medical_radio = document.querySelector("#tv_medical__wrapper input[name='tv_medical_id']:checked");
        if (tv_medical_radio) {
            tv_medical_tariff_id = $(tv_medical_radio).val();
        }

        $("#ajax_prolongation__content").load('ajax/loan.php?action=get_prolongation', {
            order_id,
            number,
            user_id,
            tv_medical_tariff_id
        }, function (response, status, xhr) {
            if (status === "error") {
                alert('Произошла ошибка сервера подробности в консоли');
                console.error('error load text: ' + xhr.status + " " + xhr.statusText);
            } else {
                $("body").removeClass('is_loading');
                $(".js-prolongation-open-modal[data-order_id='" + order_id + "']").trigger('click');
                initialize();
                let shouldShow = shouldShowElements(userUtmSource, nowHour, today);

                /*if (shouldShow && isFirstOrder == 1) {
                    toggleVisibility('checkboxBlock', true);
                } else {
                    toggleVisibility('checkboxBlock', false);
                }*/

                toggleVisibility('checkboxBlock', false)

                prolongationRefreshAmount($button);
            }
        });
    });
    $(document).on('click',".prolongation_button",function() {
        localStorage.prolongation_link = true
    })
    $(document).ready(initialize);
</script>

