{if $user_balance->inn == '9726022441'} 
<div style="margin:20px 0">Передан по Договору уступки права требования от ООО МКК "Акадо" ИНН 9726022441</div>
{/if}

{if $user_balance->loan_type=='IL'}
    {if $settings->il_enabled}
        {include file='installment/user_loan_balance_il.tpl'}
    {/if}
{else}
<div class="split">
    <ul class="split__list">
        {*
        <li>
            <div>Остаток Основного долга</div>
            <div>{$user_balance->ostatok_od}</div>
        </li>
        <li>
            <div>Остаток Процентов</div>
            <div>{$user_balance->ostatok_percents}</div>
        </li>
        {if $user_balance->ostatok_peni}
            <li>
                <div>Остаток Пени</div>
                <div>{$user_balance->ostatok_peni}</div>
            </li>
        {/if}
        {if $user_balance->penalty}
            <li>
                <div>Дополнительные услуги</div>
                <div>{$user_balance->penalty*1}</div>
            </li>
        {/if}
        *}

        {if !$settings->hide_order_information || $user_data['show_order_information']}
            {assign var="remaining_debt_total" value=($user_balance->ostatok_od + $user_balance->ostatok_percents + $user_balance->ostatok_peni + $user_balance->penalty)}
        {else}
            {assign var="zaim_diff" value=(time() - strtotime($user_balance->zaim_date))}

            {if $zaim_diff > 60*60*72}
                {assign var="remaining_debt_total" value=($user_balance->ostatok_od + $user_balance->ostatok_percents + $user_balance->ostatok_peni + $user_balance->penalty)}
            {else}
                {assign var="min_credit" value=($user_balance->p2pcredits_amount < $user_balance->ostatok_od) ? $user_balance->p2pcredits_amount : $user_balance->ostatok_od}

                {assign var="remaining_debt_total" value=($min_credit + $user_balance->ostatok_percents + $user_balance->ostatok_peni)}
            {/if}
        {/if}

        <li class="split__list-item">
            {if isset($remaining_debt_total)}
                {if $order_data->order->is_sum_hidden_after_prolongation && !$user_data['show_order_information']}
                    <p>Информация обновляется. Пожалуйста, проверьте позже.</p>
                {else}
                    <div class="split__list-text">
                        <a href="javascript:void(0);" class="js-open-details-{$user_balance->zaim_number}">Общий остаток задолженности</a>
                    </div>
                    <div class="split__list-text">
                        {$remaining_debt_total}
                    </div>
                {/if}
            {/if}
        </li>
{*        <li>*}
{*            <div>Проценты по займу</div>*}
{*            <div>{($user_balance->ostatok_percents)}</div>*}
{*        </li>*}
        <li class="split__list-item">
            <div class="split__list-text">Дата планового платежа</div>
            <div class="split__list-text">{$user_balance->payment_date|date}</div>
        </li>
{*        https://tracker.yandex.ru/BOOSTRARU-883*}

{*        {if $user_balance->last_prolongation == 1 && $user_balance->prolongation_count <= 5}*}
{*            <li>*}
{*                <div>Итого на {$user_balance->payment_date|date}</div>*}
{*                <div>{$user_balance->prolongation_amount}</div>*}
{*            </li>*}
{*        {/if}*}
    </ul>
</div>
    {if $order_data->balance->sale_info!='Договор продан' && $order_data->balance->zaim_number!='Нет открытых договоров' && $order_data->balance->zaim_number != 'Ошибка. Обратитесь в офис'}
        {assign var="payment_date" value=$order_data->balance->payment_date}
        {assign var="utc_payment_date" value=$payment_date|strtotime}
        {assign var="utc_now" value=$smarty.now|date_format:"%Y-%m-%d 00:00:00"|strtotime}
        {if $utc_now > $utc_payment_date}
            <div>
                <p class="text-red">Ваш заём просрочен</p>
            </div>
        {/if}
    {/if}
{/if}
<style>

</style>
    {if isset($orderData->balance->sum_with_grace)}
        <input class="grace-value" {if $orderData->balance->sum_with_grace} value="true" {/if} type="hidden">
        <div class="grace-main-div" id="grace-div">
            <div class="grace-container-div">
                <h1>Вам доступна оплата со скидкой для закрытия займа</h1>
                <h4>Остаток задолженности с учетом скидки <span
                            class="new-price">{str_replace(',', '', number_format($orderData->balance->sum_od_with_grace + $orderData->balance->sum_percent_with_grace, 2))}</span>
                    <span class="old-price">{($orderData->balance->ostatok_od+$orderData->balance->ostatok_percents+$orderData->balance->ostatok_peni+$orderData->balance->penalty)}</span>
                </h4>
                <div style="display: flex; align-items: center; gap: 30px; margin: 30px 0 0 0;">
                    <form method="POST" action="user/payment" class="user_payment_form form-pay">
                        <input type="hidden" name="number" value="{{$orderData->balance->zaim_number}}"/>
                        <input type="hidden" name="grace_payment" value="true"/>
                        <input type="hidden" name="order_id" value="{$orderData->order->order_id}"/>
                        <input type="hidden" class="payment_amount" data-order_id="{$orderData->order->order_id}"
                            data-user_id="{$orderData->balance->user_id}" name="amount"
                            value="{str_replace(',', '', number_format($orderData->balance->sum_od_with_grace + $orderData->balance->sum_percent_with_grace, 2))}"
                            max="{str_replace(',', '', number_format($orderData->balance->sum_od_with_grace + $orderData->balance->sum_percent_with_grace, 2))}"
                            min="1">
                        <button data-order_id="{$orderData->balance->zaim_number}" class="pay-grace payment_button button button-inverse btn-600 js-save-click btn-fsize-14 full_payment_button" data-event="5">
                            Оплатить со скидкой
                        </button>
                    </form>
                    <div class="notice">
                        *оплата по скидке происходит только по кнопке "Оплатить со скидкой"
                    </div>
                </div>
            </div>
        </div>
    {/if}



<style>

    .pt-20 { padding-top:20px }
    .details-modal {
        background: #fff;
        max-width: 420px;
        border-radius: 40px;
        margin: 0 auto;
        padding: 60px 30px;
        position: relative;
    }
    .details-modal .details_modal_step1 { display:block }
    .details-modal .details_modal_step2 { display:none }
    .details-modal.loaded .details_modal_step1 { display:none}
    .details-modal.loaded .details_modal_step2 { display:block }
    .details-modal.loading .details_modal_step1:before {
        content:'';
        display:block;
        position:absolute;
        width: 100%;
        height: 100%;
        opacity: 0.8;
        background: #fff;
        top: 0;
        left: 0;
        border-radius: 40px;
        background: #fff url(/design/boostra_mini_norm/img/preloader.gif) center no-repeat;
    }
</style>

<div style="display:none">
    <div class="details-modal" id="details_modal_{$user_balance->zaim_number}">
        <div class="details_modal_step1">
            <h5 class="text-center">Получить расшифровку задолженности по займу {$user_balance->zaim_number}?</h5>
            <div class="text-center pt-20">
                <button type="button" class="button medium button-inverse" onclick="$.magnificPopup.close();">Нет, позже</button>
                <button type="button" class="button medium js-approve-details">Да, получить</button>
            </div>
        </div>
        <div class="details_modal_step2"> 
            <h5 class="text-center">
                Расшифровка по займу {$user_balance->zaim_number} 
                <br />будет доступна в разделе документы 
                <br />через некоторое время.
            </h5>
            <div class="text-center pt-20">
                <button type="button" class="button medium" onclick="$.magnificPopup.close();">Понятно</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(function(){
        $('.js-open-details-{$user_balance->zaim_number}').click(function(){
            $.magnificPopup.open({
                items: { src: '#details_modal_{$user_balance->zaim_number}' },
                type: 'inline',
                showCloseBtn: false,
                modal:true,
            });

        });      
        $('#details_modal_{$user_balance->zaim_number} .js-approve-details').click(function(){
            $('#details_modal_{$user_balance->zaim_number}').addClass('loading');
            setTimeout(function(){
                $('#details_modal_{$user_balance->zaim_number}').removeClass('loading').addClass('loaded');
            }, 5000)
        });

    })


    var graceValue = $(".grace-value").val()

    if (graceValue) {
        localStorage.graceValue = true
    }

    $(document).on('click','.pay-grace',function (){
        localStorage.graceButton = true;
    })



</script>
