{* Страница входа пользователя *}

{* Канонический адрес страницы *}
{$canonical="/account/login" scope=parent}

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
</style>

<section id="login">
    <div id="worksheet">
        <h3>Добро пожаловать <b>{$fio}</b></h3>
        <div class="flex-center">
            {if !empty($user.loans)}
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
                        <th>Дата</th>
                        <th>Сумма</th>
                        <th>Действие</th>
                    </tr>
                    </thead>
                    <tbody>
                        {foreach $user.loans as $key_loan => $loan}
                            <tr id="row_{$key_loan}">
                                <td>{$loan.loan_type} {$loan.loan_id}</td>
                                <td>{date("d.m.Y", $loan.loan_date_added)}</td>
                                <td>
                                    <input
                                            onchange="changeAmount(this)"
                                            max="{$loan.loan_amount}"
                                            value="{$loan.loan_amount}" />
                                </td>
                                <td>
                                    <button id="get_payment_link_{$key_loan}" onclick="getPaymentLink({$key_loan})">Оплатить</button>
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            {/if}
        </div>
	</div>
</section>
<script type="text/javascript">
    function changeAmount(elem) {
        let max = parseFloat($(elem).attr('max')),
            val = parseFloat($(elem).val());

        if (max < val) {
            $(elem).val(max);
        }
    }

    function getPaymentLink(key_loan) {
        $('.alert').remove();
        let input = $('#row_' + key_loan + ' input');

        $.ajax({
            url: '{$url_get_payment_link}',
            method: 'POST',
            data: {
                amount: input.val(),
                key_loan: key_loan,
            },
            beforeSend: function () {
                $('#get_payment_link_' + key_loan).prop('disabled', true);
            },
            success: function (json) {
                if (json['Success']) {
                    window.location.href = json['PaymentURL'];
                }

                if (json['error']) {
                    let html = '<div class="alert alert-danger">' + json['error'] + '</b></div>';
                    $('#worksheet').prepend(html);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
                alert(error);
                console.log(error);
            },
        }).done(function () {
            $('#get_payment_link_' + key_loan).prop('disabled', false);
        });
    }
</script>

