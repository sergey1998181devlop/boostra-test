{* Страница входа пользователя *}

{* Канонический адрес страницы *}
{$canonical="/user/contract" scope=parent}

{$meta_title = "Добро пожаловать в личный кабинет Boostra" scope=parent}

{$login_scripts = true scope=parent}

{$body_class = "gray" scope=parent}

<style>
    .flex-center {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .wait-alert {
        font-size: 1.3rem;
        color: red;
    }

    .order-table {
        text-align: left;
        margin: 20px 0;
        border-spacing: 0;
    }

    .order-table td, .order-table th {
        border: 1px solid;
        padding: 10px;
    }

    button[disabled] {
        cursor: no-drop;
        opacity: .5;
    }

    @media screen and (max-width: 760px) {
        #worksheet colgroup col {
            width: auto;
        }

        [name="amount"] {
            width: 100%;
        }
    }
</style>

<section id="login">
    <div id="worksheet">
        <h3>Добро пожаловать <b>{implode(' ', [$contract_user->firstname, $contract_user->patronymic])} {mb_substr($contract_user->lastname, 0, 1)}.</b></h3>
        <div class="flex-center">
            {if $smarty.session.$session_key}
                <table class="order-table table">
                    <colgroup>
                        <col width="180"/>
                        <col width="180"/>
                        <col width="180"/>
                        <col width="180"/>
                    </colgroup>
                    <thead>
                    <tr>
                        <th>Договор</th>
                        <th>Дата платежа</th>
                        <th>Сумма</th>
                        <th>Действие</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach $smarty.session.$session_key as $loan}
                        <tr>
                            <td>{$loan['НомерЗайма']}</td>
                            <td>{date("d.m.Y", strtotime($loan['ПланДата']))}</td>
                            <td>
                                <input name="contract_number" value="{$loan['НомерЗайма']}" type="hidden" class="contract_number-value"/>
                                {if $loan['IL'] != 0}
                                    {if $settings->il_enabled}
                                        <input name="amount" value="{$loan['IL_DATA']['ОбщийДолг'] - $loan['IL_DATA']['Баланс']}" oninput="this.value = this.value.replace(/[^0-9.]/g, '')" class="amount-value"/>
                                        <input type="hidden" value="{$loan['IL_DATA']['ОбщийДолг'] - $loan['IL_DATA']['Баланс']}" class="amount-value-hidden">
                                    {else}
                                        <div class="about">
                                            <div>Ведутся Технические работы</div>
                                        </div>
                                    {/if}
                                {else}
                                    {assign var="loan_ts" value=$loan['ДатаЗайма']|replace:'T':' '|@strtotime}
                                    {assign var="one_day" value=60*60*24}
                                    {assign var="base_amount" value=($loan['ОстатокОД']|default:0) + ($loan['ОстатокПроцентов']|default:0) + ($loan['ШтрафнойКД']|default:0)}

                                    {if $loan_ts && ($smarty.now - $loan_ts) >= $one_day}
                                        {assign var="amount" value=$base_amount}
                                    {else}
                                        {assign var="amount" value=null}
                                    {/if}
                                    <input name="amount" value="{$amount}" oninput="this.value = this.value.replace(/[^0-9.]/g, '')" class="amount-value"/>
                                    <input type="hidden" value="{$base_amount}" class="amount-value-hidden">
                                {/if}
                            </td>
                            <td>
                                {if $loan['IL'] != 0}
                                    {if $settings->il_enabled}
                                        <button class="send_payment">Оплатить</button>
                                    {/if}
                                {else}
                                    <button class="send_payment">Оплатить</button>
                                {/if}
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            {else}
                <p>У вас нет активный займов!</p>
            {/if}
        </div>
        <p class="wait-alert">Внимание! После совершения платежа баланс может обновляться до 1 часа. Приносим извинения за неудобства.</p>
    </div>
</section>
<script type="text/javascript">
    $(".amount-value").change(function () {
        var inputValue = $(this).parent().find('.amount-value-hidden').val();

        if (+$(this).val() > +inputValue) {
            $(this).val(inputValue);
        }
    })

    $('.send_payment').click(function() {
        $('.alert').remove();
        _that = $(this);
        let amount = _that.parents('tr').find('[name="amount"]').val();
        let contract_number = _that.parents('tr').find('[name="contract_number"]').val();
        $.ajax({
            url: '/user/contract?action=getPaymentLink',
            method: 'POST',
            data: {
                amount: amount,
                contract_number: contract_number
            },
            beforeSend: function () {
                _that.prop('disabled', true);
            },
            success: function (json) {
                if (json['Success']) {
                    window.location.href = json['PaymentURL'];
                }
                if (json['errors']) {
                    let html = '<div class="alert alert-danger">При отправке формы произошла ошибка! <br/><b>' + json['errors'].join('<br/>') + '</b></div>';
                    $('#worksheet').prepend(html);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
                alert(error);
                console.log(error);
            },
        }).done(function () {
            _that.prop('disabled', false);
        });
    });
</script>
