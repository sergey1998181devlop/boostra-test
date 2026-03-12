{* Шаблон страницы зарегистрированного пользователя *}

{* Канонический адрес страницы *}
{$canonical="/user" scope=parent}

{$body_class = "gray" scope=parent}

{$add_order_css_js = true scope=parent}

{capture name=page_scripts}
{/capture}


<style>
    #private .tabs .content > * {
        vertical-align:top;
    }
    .toggle_reason {
        color:#d33;
    }
    .loanhistory {
        width:1000px;
        padding:0;
        margin: 20px 0 0 0;
    }
    .loanhistory li {
        display:flex;
        width:100%;
        border-bottom: 1px solid #ccc;
        padding:10px;
    }
    .loanhistory li:hover {
        background:#efefef;
    }
    .loanhistory li > div {
        display:inline-block;
        vertical-align:middle;
    }
    .loanhistory li div.loan_number {
        width:30%;
        text-align:left;
    }
    .loanhistory li div.loan_status {
        width:20%;
        text-align:left;
    }
    .loanhistory li div.loan_date {
        width:38%;
        text-align:left;
    }
    .loanhistory li div.loan_reason {
        width:100%;
        display:none;
        text-align:left;
        padding: 5px;
        box-sizing: border-box;
    }
    .loanhistory li div.loan_card {
        width:38%;
        text-align:left;
    }
    @media (max-width:860px)
    {
        .loanhistory {
            width:500px;
        }
        .loanhistory li{
            flex-wrap: wrap;
        }
    }
    @media (max-width:540px)
    {
        .loanhistory {
            width:100%;
        }
        .loanhistory li div.loan_number {
            width:100%;
            text-align:left;
        }
        .loanhistory li div.loan_date {
            width:100%;
            text-align:left;
        }
        .loanhistory li div.loan_status {
            width:100%;
            text-align:left;
        }
        .loanhistory li div.loan_reason {
            width:100%;
            text-align:left;
        }
    }
</style>

<section id="private">


    <div>
        <div class="tabs ">

            {include file='user_nav.tpl' current='user'}

            <div class="content">
                <div class="panel">

                    {foreach $result_orders as $order}
                        {assign var="schedule_payments" value=$order['Платежи']}
                        <h1 style="text-align:left">Плановый график платежей {$order['contract']->number}</h1>
                        <div class="list">
                            <ul class="loanhistory">
                                <li>
                                    <div class="loan_number">
                                        Дата оплаты
                                    </div>
                                    <div class="loan_date">
                                        Сумма процентов в платеже
                                    </div>
                                    <div class="loan_date">
                                        Сумма тела займа в платеже
                                    </div>
                                    <div class="loan_date">
                                        Сумма платежа
                                    </div>
                                    <div class="loan_status">
                                        Остаток после оплаты
                                    </div>
                                </li>
                                {foreach $schedule_payments['Платежи'] as $payment}
                                    <li>
                                        <div class="loan_number">
                                            {$payment['ДатаПо']|date}
                                        </div>
                                        <div class="loan_date">
                                            {$payment['СуммаПроцентов']}
                                        </div>
                                        <div class="loan_date">
                                            {$payment['СуммаОД']}
                                        </div>
                                        <div class="loan_date">
                                            {$payment['Сумма']}
                                        </div>
                                        <div class="loan_status">
                                            {$payment['ОстатокОД']}
                                        </div>
                                    </li>
                                {/foreach}
                            </ul>
                        </div>
                    {/foreach}
                </div>


            </div>
        </div>
    </div>
</section>
