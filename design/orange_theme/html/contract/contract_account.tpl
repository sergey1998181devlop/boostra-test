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
        <h3>Добро пожаловать <b>{implode(' ', [$user->firstname, $user->patronymic])} {mb_substr($user->lastname, 0, 1)}.</b></h3>
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
                        <tr>
                            <td>{$smarty.session.$session_key['Номер']}</td>
                            <td>{date("d.m.Y", strtotime($smarty.session.$session_key['ПланДата']))}</td>
                            <td>
                                <input name="number" value="{$smarty.section.$session_ley['Номер']}">
                                <input name="amount" value="{$smarty.session.$session_key['ОстатокОД'] + $smarty.session.$session_key['ОстатокПроцентов']}" />
                            </td>
                            <td>
                                <button id="send_payment" onclick="getPaymentLink()">Оплатить</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            {/if}
        </div>
    </div>
</section>
<script type="text/javascript">
    function getPaymentLink() {
        $('.alert').remove();
        let amount = $('[name="amount"]').val();

        $.ajax({
            url: '/user/contract?action=getPaymentLink',
            method: 'POST',
            data: {
                amount: amount,
            },
            beforeSend: function () {
                $('#send_payment').prop('disabled', true);
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
            $('#send_payment').prop('disabled', false);
        });
    }
</script>

