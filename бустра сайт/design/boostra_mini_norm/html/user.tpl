{* Шаблон страницы зарегистрированного пользователя *}
{* Канонический адрес страницы *}
{$canonical="/user" scope=parent}

{$body_class = "gray" scope=parent}

{$add_order_css_js = true scope=parent}
{assign var="currentPage" value="user"}

<div id="userData" data-order-status="{$user->order['status']}" data-order-id="{$user->order['id']}" data-number="{$user->order['1c_id']}" data-1c-status="{$user->order['1c_status']}" style="display:none;"></div>

{capture name=page_scripts}
    <script src="design/boostra_mini_norm/js/mobile_download_banners/mobile_banners.js?v=1.002" type="text/javascript"></script>
    <script src="design/{$settings->theme|escape}/js/b2p.app.js?v=1.022" type="text/javascript"></script>
    <script src="design/{$settings->theme|escape}/js/user.js?v=1.414" type="text/javascript"></script>
    <script src="design/{$settings->theme|escape}/js/prolongation.app.js?v=1.18" type="text/javascript"></script>
    <script src="design/{$settings->theme|escape}/js/installment_payment_buttons.app.js?v=1.019" type="text/javascript"></script>
    <script src="design/{$settings->theme|escape}/js/user_statuses.js?v=0.002" type="text/javascript"></script>
{/capture}
<script type="text/javascript">
    let userUtmSource = "{$user->utm_source|escape:'javascript'}";
    let overdue = "{$overdue|escape:'javascript'}";
    let userId = "{$user->id|escape:'javascript'}"
    let crmAutoApprove = "{!empty(order_for_choosing_card) && $order_for_choosing_card['utm_source'] === 'crm_auto_approve'}";
    let isFirstOrder = "{$is_first_order|escape:'javascript'}"
    var isOrganic = "{$isOrganic|escape:'javascript'}"
</script>
{if $config->snow}
    <link rel="stylesheet" type="text/css" href="design/orange_theme/css/holidays/snow.css?v=1.36"/>
    {include file='design/orange_theme/html/holidays/snow.tpl'}
{/if}

{if !($user_return_credit_doctor)}
    {* Если последняя заявка клиента была автоодобрена, тогда активируем чекбокс КД *}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let shouldCheckboxBeChecked = {json_encode($is_last_order_auto_approved)};
            if (shouldCheckboxBeChecked) {
                document.getElementById('credit_doctor_check').checked = true;
            }
        });
    </script>
{/if}
{literal}
    <style>
        img{
            max-width: 100%;
        }
        .clear {
            clear: both;
        }
        .restrict_mode_panel {
            background: #f4f4f4;
            border: 1px solid #D0D0D0;
            border-radius: 10px;
            padding: 20px;
        }
        .restrict_salute {
            color: #0997FF;
            text-decoration: underline;
        }

        .restrict_sidebar img {
            width: 100%;
        }
        .restrict_alert {
            background: #FDDAB9;
            border-radius: 10px;
            position: relative;
            padding: 15px;
            margin: 15px 0;
            font-size: 11px;
        }
        .restrict_alert img {
            width: 100px;
            position: absolute;
            top: -29px;
            left: -32px;
        }
        .restrict_info h2 {
            font-size: 25px;
        }
        .restrict_info_text {
            font-size: 11px;
            margin: 0;
            padding: 0;
            position: relative;
        }
        .restrict_divider {
            clear: both;
            display: block;
            border-bottom: 1px solid #000;
            position: relative;
        }
        .restrict_img_bg {
            width: 100%;
            height: 150px;
            background-size: cover !important;
            border-radius: 10px;
            background-position-y: 237px !important;
            position: relative;
        }
        .restrict_alert_text {
            position: relative;
            font-weight: bold;
            font-size: 14px;
        }
        .float_left_block {
            float: left;
        }
        .float_left_block p {
            margin: 0 !important;
            padding: 0 !important;
            font-size: 12px !important;
        }
        .float_left_block h3 {
            color: #0997FF !important;
        }
        .restrict_button {
            background: #0997FF;
            border-radius: 5px;
            width: 100%;
            margin: 15px 0px 0 0;
            box-shadow: none;
        }
        .prolongation-notification-main{
            width: clamp(300px, 50%, 800px);
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            margin: 32px 0;
        }
        .prolongation-notification{
            width: 95%;
            /* border: 1px solid; */
            box-shadow: 0px 4px 20px 0px #02113B1A;
            padding: 5px 20px;
            border-radius: 20px;

        }
        .prolongation-notification>p,.prolongation-notification-details>div>p{
            font-size: 14px !important;
            color: #6F7985;
            font-weight: 500;
            margin: 10px 0 !important;
        }
        /* .prolongation-notification-details>div>p {
            text-indent: 30px;
        } */
        .prolongation-not-available{
            color:#038AEE;
            text-decoration: underline;
            cursor: pointer;
        }
        .prolongation-notification-details{
            display: none;
            width: 95%;
            box-shadow: 0px 4px 20px 0px #02113B1A;
            border-top: none;
            padding: clamp(24px, 22.2px + 0.488vw, 30px) clamp(20px, 18.5px + 0.407vw, 25px) 42px;
            margin-bottom: 5px;
            margin-top: 14px;
            border-radius: 22px;
            color: #6F7985;
            font-weight: 500;
            font-size: 14px;
        }
        .prolongation-notification-show {
            display: flex;
            justify-content: center;
            flex-direction: column;
            align-items: flex-start;
        }
        .payment_button-change-color {
            /* background: black !important;
            color: white !important; */
        }
        .btn-close-prolongation-notification{
            /* background: white !important;
            color: black;
            border: 1px solid; */
            margin: 15px 0 0;
        }

        button.pay-full {
            padding: 12px 51px;
        }
        @media (max-width: 767px) {
            .row {
                width: auto !important;
            }
            .prolongation-notification-main {
                width: 90%;
                margin: 10px auto;
            }
        }
        @media (min-width: 1700px) {
            .restrict_alert, .restrict_info_text, .float_left_block {
                font-size: 20px !important;
            }
        }
        @media (min-width: 2400px) {
            .restrict_alert, .restrict_info_text, .float_left_block {
                font-size: 30px !important;
            }
        }
    </style>
{/literal}

{function name=sendMetric}
    {if !$is_developer}
        <script>
            $(document).ready(function () {
                sendMetric('reachGoal', 'new_cr_reject_link_viewed');
            });
        </script>
    {/if}
{/function}

{function name=loan_form}
    {if $restricted_mode !== 1}
        {if $redirect}
            <form method="POST" action="{$redirect['url']}" id="newlk_form" data-user="{$user->id}">
                <input type="hidden" name="data" value="{$redirect['data']}"/>
                <input type="hidden" name="signature" value="{$redirect['signature']}"/>
                <button type="submit" class="button big green bg-warning">
                    Заявка на заём
                </button>
            </form>
        {elseif $quantity_loans_block}
            <div class="view_order">
                {include file='order_statuses/quantity_loans_block.tpl'}
            </div>
        {else}

            {if $user_discount}
                <input type="hidden" name="has_user_discount" value="1"/>
                <div class="discount_subtitle" style=";margin: 30px 0 10px 0;color:#21ca50;">
                    {if $user_discount->percent > 0}
                        Для вас есть акционное предложение: {$user_discount->percent*1}% по займу вместо 0.8%!
                        <br/>
                    {/if}
                    {if $user_discount->end_date}
                        Срок действия акции: до {$user_discount->end_date|date}
                        <br/>
                        (необходимо оформить заявку и получить деньги в течение этого периода)
                    {/if}
                </div>
            {/if}
            {if $user->maratorium_valid}
                <p class="warning-credit-text">Вы можете подать новую заявку не ранее
                    чем {$user->maratorium_date|date} {$user->maratorium_date|time}</p>
            {/if}
            {include file="user_get_zaim_form.tpl"}
        {/if}
    {/if}
{/function}

{if $notification_type}
    {include file='modals/ticket_notification_modal.tpl' user_id=$user->id type=$notification_type}
{/if}

{function name='view_order'}
<div class="view_order">
    {if $current_order._flags.is_empty}
        <p>Спасибо за вашу заявку, она будет обработана в ближайшее время.</p>

    {elseif $current_order._flags.is_waiting_transfer}
        {include file='order_statuses/waiting_transfer.tpl'}

    {elseif $current_order._flags.is_show_accept_credit}
        {include file='accept_credit.tpl' user_order=$current_order}

    {elseif $current_order._flags.is_show_issued_block}
        {include file='order_statuses/issued.tpl' order=$current_order}

    {elseif $current_order._flags.is_transfer_delay}
        {include file='order_statuses/transfer_delay.tpl'}

    {elseif $current_order._flags.is_not_issued}
        {include file='order_statuses/not_issued.tpl'}

    {elseif $current_order._flags.is_photo_error}
        {include file='order_statuses/photo_error.tpl'}

    {elseif $current_order._flags.is_new || $current_order._flags.is_crm_waiting || $current_order._flags.is_corrected}
        {include file='order_statuses/pending.tpl' order=$current_order}

    {elseif $current_order._flags.is_cooling_off}
        {include file='order_statuses/cooling_off.tpl' order=$current_order}
    {/if}

    {if ($current_order._flags.has_reason_block || $current_order._flags.is_rejected)
    && !$current_order._flags.is_hidden_reject_reason
    && !$can_show_new_order_form}
        {include file='order_statuses/rejected.tpl' order=$current_order}
    {/if}
</div>
    <div class="hidden">
        <div id="quick-approval-modal">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header modal-header-prolongation">
                        <div class="title-wrap">
                            <h5 class="modal-title text-center" id="modalTitle">Вы приобрели услугу</h5>
                        </div>
                        <a type="button" id="closeButtonModal"
                           class="btn-close btn-close-modal  btn-close-prolongation-x" data-bs-dismiss="modal"
                           aria-label="Close">X</a>
                    </div>
                    <div class="modal-body">
                        <p>Файл с подробным описанием причины отказа разместили во вкладке Документы</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/function}

<input type="hidden" class="user-id" data-id="{$user->id}">
<section id="private">
    <input type="hidden" name="is_new_client" value="{$is_new_client}"/>
    <input name="use_b2p" value="{$use_b2p}" type="hidden"/>
    <div>
        <div class="tabs {if $action=='user'}lk{elseif $action=='history'}history{/if}">

            {include file='user_nav.tpl' current='user'}

            <div class="content">

                {if $action == "user"}
                    {if $is_virtual_card_enabled}
                        {include file='virtual_card.tpl'}
                    {else}
                        {include file='user_tab.tpl'}
                    {/if}
                {/if}

                {if !$has_active_loans}
                    {include './mobile_banners/link_banner.tpl' banner_img_android="design/boostra_mini_norm/assets/image/banner_rustore_lk_img.png" banner_img_ios="design/boostra_mini_norm/assets/image/banner_ios_lk_img.png" banner_link="https://apimp.boostra.ru/get_app.php?slot=b3"}
                {/if}
                {if $action=="history"}
                    <div class="panel">

                        {if $orders}
                            <div class="list">
                                <!--h4>Прочие займы  <span>.</span></h4-->
                                <ul class="table">
                                    {foreach $orders as $order}
                                        {if $order->status != 4}
                                            <li>
                                                <div>
									<span class="card visa">

									</span>
                                                </div>
                                                <div>
                                                    Заём на
                                                    <strong>{$order->amount*1} {$currency->sign|escape}</strong>
                                                </div>
                                                <div>
                                                    Заявка
                                                    <a href='order/{$order->url}'>
                                                        <strong>{$order->id}</strong>
                                                    </a>
                                                    / {$order->id_1c}
                                                </div>
                                                <div>
                                                    Дата заявки
                                                    <strong>
                                                        {$order->date|date}
                                                        {$order->date|time}
                                                    </strong>
                                                </div>
                                                <div>
                                                    {$order->status_1c}
                                                    {*}
                                                                                        {if $order->paid == 1}оплачен,{/if}
                                                                                        {if $order->status == 0}
                                                                                        ждет обработки
                                                                                        {elseif $order->status == 1}в обработке
                                                                                        {elseif $order->status == 3}погашен
                                                                                        {/if}
                                                    {*}
                                                    {*
                                                        Просрочен на
                                                        <strong>4 дня</strong>
                                                        *}
                                                </div>
                                                <div>
                                                    {*
                                                    Дата погашения
                                                    <strong>10.02.2017</strong>
                                                    *}
                                                </div>
                                            </li>
                                        {/if}
                                    {/foreach}
                                </ul>
                            </div>
                        {/if}
                    </div>
                {/if}{* action = history *}

                {if $action=="success"}

                    {$meta_title="Оплата успешно принята"}
                    <div class="panel">
                        <h1>Оплата успешно принята</h1>
                        <div class="about">
                            <p>Вы будете перенаправлены в свой Личный кабинет через несколько секунд.</p>
                        </div>
                    </div>
                {/if}

                {if $action=="error"}
                    <div class="panel">
                        <h1>Карта не привязана</h1>
                        <div class="about">
                            <p>Попробуйте заново или привяжите другую карту</p>
                        </div>
                    </div>
                {/if}

            </div>
        </div>
    </div>
</section>
<div style="display:none">

        <div id="accept_order" class="accept_credit_modal white-popup mfp-hide">
            {* Добавляем скролл в модальное окно и изменяем его размер *}

            <div id="not_checked_info" style="display:none">
                <strong style="color:#f11">Вы должны согласиться с условиями</strong>
            </div>
            <p>Я согласен со всеми условиями:</p>

            <div>
                <label class="spec_size">
                    <div class="checkbox"
                         style="border-width: 1px;width: 16px !important;height: 16px !important;margin-top: 5px;">
                        <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="0" id="agreed_1"
                               name="agreed_1" />
                        <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                    </div>
                </label>
                Настоящим подтверждаю, что полностью ознакомлен и согласен с
                <a href="http://www.boostra.ru/files/docs/obschie-usloviya.pdf" target="_blank">Общими условиями
                    договора потребительского микрозайма</a>
            </div>
            <div>
                <label class="spec_size">
                    <div class="checkbox"
                         style="border-width: 1px;width: 16px !important;height: 16px !important;margin-top: 5px;">
                        <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="0" id="agreed_4"
                               name="agreed_4" />
                        <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                    </div>
                </label>
                Настоящим подтверждаю, что полностью ознакомлен и согласен с
                <a href="http://www.boostra.ru/files/docs/pravila-predostavleniya.pdf"
                   target="_blank">
                    Правилами предоставления займов ООО МКК "Аквариус"
                </a>
            </div>
            <div>
                <label class="spec_size">
                    <div class="checkbox"
                         style="border-width: 1px;width: 16px !important;height: 16px !important;margin-top: 5px;">
                        <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="0" id="agreed_3"
                               name="agreed_3" />
                        <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                    </div>
                </label>
                Настоящим подтверждаю, что полностью ознакомлен и согласен с
                <a href="https://www.boostra.ru/files/docs/informatsiyaobusloviyahpredostavleniyaispolzovaniyaivozvrata.pdf"
                   target="_blank">
                    Правилами обслуживания и пользования услугами ООО МКК "Аквариус"
                </a>
            </div>
            {if $pdn_doc > 50}
                <div>
                    <label class="spec_size">
                        <div class="checkbox"
                             style="border-width: 1px;width: 16px !important;height: 16px !important;">
                            <input class="js-agreeed-asp js-need-verify-modal" type="checkbox"
                                   value="0" id="agreed_10"
                                   name="agreed_10" />
                            <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                        </div>
                    </label>
                    Настоящим подтверждаю, что полностью ознакомлен и согласен с
                    <a href="user/docs?action=pdn_excessed" target="_blank">
                        Уведомлением о повышенном риске невыполнения кредитных обязательств
                    </a>
                </div>
            {/if}
            <div>
                <label class="spec_size">
                    <div class="checkbox"
                         style="border-width: 1px;width: 16px !important;height: 16px !important;margin-top: 5px;">
                        <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="0" id="agreed_3"
                               name="agreed_3" />
                        <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                    </div>
                </label>
                Настоящим подтверждаю, что полностью ознакомлен и согласен с
                <a href="user/docs?action=micro_zaim" target="_blank" class="micro-zaim-doc-js">Заявлением
                    о предоставлении микрозайма</a>
                <script defer>
                    $('a.micro-zaim-doc-js').mousedown(function (e) {
                        e.preventDefault();
                        let loanAmount = $('#calculator .total').text();
                        if (!loanAmount) {
                            loanAmount = $('#approve_max_amount').text();
                        }
                        if (!loanAmount) {
                            loanAmount = $('#amountToCard').text();
                        }
                        if (!loanAmount && $('.cross_order_accept')) {
                            const text = $("#full-loan-info").text();
                            loanAmount = text.match(/\d[\d\s.]*\d/g)?.[0].replace(/\s/g, '');
                            window.open($(this).attr('href') + '&loan_amount=' + loanAmount, '_blank');
                            return false;
                        }
                        let is_user_credit_doctor = $('#credit_doctor_check').is(':checked') ? 1 : 0;
                        let newUrl = $(this).attr('href') + '&loan_amount=' + loanAmount + '&credit_doctor=' + is_user_credit_doctor;
                        window.open(newUrl, '_blank');
                    })
                </script>
            </div>
            <div>
                <label class="spec_size">
                    <div class="checkbox"
                         style="border-width: 1px;width: 16px !important;height: 16px !important;margin-top: 5px;">
                        <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="0" id="agreed_5"
                               name="agreed_5" />
                        <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                    </div>
                </label>
                Настоящим подтверждаю, что полностью ознакомлен и согласен с
                <a href="http://www.boostra.ru/files/docs/politikakonfidentsialnosti.pdf" target="_blank">
                    Политикой конфиденциальности ООО МКК "Аквариус"
                </a>
            </div>
            <div>
                <label class="spec_size">
                    <div class="checkbox"
                         style="border-width: 1px;width: 16px !important;height: 16px !important;margin-top: 5px;">
                        <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="0" id="agreed_9"
                               name="agreed_9"/>
                        <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                    </div>
                </label>
                Настоящим выражаю свое <a
                        href="http://www.boostra.ru/files/docs/soglasie-klienta-na-poluchenie-informatsii-iz-byuro-kreditnyh-istorij.pdf"
                        target="_blank">согласие</a> на запрос кредитного отчета в бюро кредитных историй

            </div>
            {include file="credit_doctor/credit_doctor_checkbox.tpl"}
            <div>
                <label class="spec_size">
                    <div class="checkbox"
                         style="border-width: 1px;width: 16px !important;height: 16px !important;margin-top: 5px;">
                        <input class="js-service-recurent" type="checkbox" value="0" id="service_recurent_check"
                        />
                        <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                    </div>
                </label>
                Настоящим подтверждаю, что полностью ознакомлен и согласен с <a
                        class="block_1"
                        href="http://www.boostra.ru/files/docs/soglashenie-o-regulyarnyh-rekurentnyh-platezhah.pdf"
                        target="_blank">Соглашением о применении регулярных (рекуррентных) платежах</a>.

            </div>
            <div>
                <label class="spec_size">
                    <div class="checkbox"
                         style="border-width: 1px;width: 16px !important;height: 16px !important;margin-top: 5px;">
                        <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="0" id="agreed_8"
                               name="agreed_8" />
                        <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                    </div>
                </label>
                Настоящим подтверждаю, что полностью ознакомлен и согласен с
                <a href="http://www.boostra.ru/files/docs/Договор_об_условиях_предоставления_Акционерное_общество_«Сургутнефтегазбанк».pdf"
                   target="_blank">
                    Договором об условиях предоставления Акционерное общество «Сургутнефтегазбанк» услуги по переводу
                    денежных средств с использованием реквизитов банковской карты с помощью Интернет-ресурса ООО
                    «Бест2пей» (Публичная оферта)
                </a>
            </div>
            <div>
                <label class="spec_size">
                    <div class="checkbox"
                         style="border-width: 1px;width: 16px !important;height: 16px !important;">
                        <input class="js-agreeed-asp" type="checkbox" value="0"
                               id="agreed_9"
                               name="agreed_9" />
                        <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                    </div>
                </label>
                Настоящим подтверждаю, что полностью ознакомлен и согласен с подключением ПО «ВитаМед» стоимостью 600 рублей
            </div>
            <div>
                <label class="spec_size">
                    <div class="checkbox"
                         style="border-width: 1px;width: 16px !important;height: 16px !important;margin-top: 5px;">
                        <input class="js-service-doctor js-need-verify-modal" type="checkbox" value="0"
                               id="service_doctor_check" name="service_doctor"/>
                        <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                    </div>
                </label>

                Настоящим подтверждаю, что полностью ознакомлен и согласен с
                <a class="contract_approve_file"
                   href="{$config->root_url}/files/contracts/{$user->order['approved_file']}"
                   target="_blank">Договором</a>
            </div>
            <button title="%title%" type="button" class="mfp-close" style="color:green;font-size:20px;">ОК</button>

        </div>

    <div id="autodebit">
        <form id="autodebit_form">

            <div class="alert-block">
                <div class="alert"></div>
                <button type="button" class="js-close-autodebit button button-inverse medium">Продолжить</button>
            </div>

            <div id="detach_block">
                <h1>Вы желаете отменить автоплатеж с карты <span class="autodebit_card_number"></span> ?</h1>
            </div>

            <div id="attach_block">
                <h1>Вы желаете подключить автоплатежи с карты <span class="autodebit_card_number"></span> ?</h1>
                <p>Нажимая "Подтвердить" я соглашаюсь и принимаю <a
                            href="http://boostra.ru/files/docs/soglashenie-o-regulyarnyh-rekurrentnyh-platezhah-mkk-ooo-bustra.pdf"
                            target="_blank">следующее соглашение</a></p>
            </div>

            <input type="hidden" name="card_attach" value=""/>
            <input type="hidden" name="card_detach" value=""/>
            <input type="hidden" name="card_type" value=""/>

            <div class="actions">
                <button type="button" class="js-close-autodebit button button-inverse medium">Отменить</button>
                <button type="submit" class="button medium">Подтвердить</button>
            </div>
        </form>
    </div>

    {include file="credit_doctor/credit_doctor_popup.tpl"}
    {include file="star_oracle/star_oracle_popup.tpl"}
</div>
<script src="design/{$settings->theme}/js/creditdoctor_modal.app.js?v=1.03" type="text/javascript"></script>
{if $user->skip_credit_rating === 'PAY'}
    <div id="modal_result_pay_credit_rating">
        <a onclick="$.magnificPopup.close();" class="close-modal" href="javascript:void();">
            <img src="design/{$settings->theme}/img/modal_icons/close_modal.png" width="17"/>
        </a>
        <div class="text-center">
            <img src="design/{$settings->theme}/img/modal_icons/icon_success_pay_cr.svg" width="120"/>
            <h2>Поздравляем!</h2>
            <p><b>Теперь вероятность одобрения займа намного выше!</b></p>
            <p>Персональный балл кредитного рейтинга и рекомендации по его повышению появятся в личном кабинете</p>
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function () {
            if (localStorage.getItem('new_user_pay_credit_rating')) {
                $.magnificPopup.open({
                    items: {
                        src: '#modal_result_pay_credit_rating'
                    },
                    type: 'inline',
                    showCloseBtn: true,
                    modal: true,
                });
                localStorage.removeItem('new_user_pay_credit_rating');
            }
        });

    </script>
{/if}

{if !$is_developer}
<script>
    {if $comeback_url}
    if(window.history) {
        window.history.pushState({ catchHistory: true }, '', window.location.href)
        window.history.pushState({ catchHistory: true }, '', window.location.href)
        window.addEventListener('popstate', (e) => {
            if(e.state?.catchHistory) {
                e.preventDefault();
            {if $partner_href_expired}
                sendMetric('reachGoal', 'decline_monitoring_5')
            {elseif $view_partner_href}
                sendMetric('reachGoal', 'decline_monitoring_4')
            {/if}
                invokeShopview('bonon-comeback{$client_suffix}', '{$comeback_url}')
                window.location = '{$comeback_url}';
            }
        })
    }
    {/if}
    window.complaint_partner_href = '{$complaint_partner_href->href}'
    window.addEventListener('sessionready', function (e) {
        $('#juicescore_useragent').val(navigator.userAgent)

        if (typeof FingerprintID !== 'undefined' && FingerprintID)
            $('#finkarta_fp').val(FingerprintID);
    })
</script>
{/if}

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const prolongationButton = document.querySelector('.get_prolongation_modal')
        var  isRestrictedMode = {$restricted_mode};
        if (prolongationButton && isRestrictedMode === 1){
            prolongationButton.click()
        }
    });

    $('.prolongation-not-available').click(function (){
        $('.prolongation-notification-details').toggleClass('prolongation-notification-show')
        $('.pay-full').toggleClass('button-inverse')
    })

    $('.btn-close-prolongation-notification').click(function (){
        $('.prolongation-notification-details').toggleClass('prolongation-notification-show')
        $('.pay-full').toggleClass('button-inverse')
    })
</script>


{if $restricted_mode !== 1 && $due_days != 'not' && $due_days != 0}
    <div id="due_block" data-order_id="{$user->balance->zaim_number}">
        <div class="modal_title">
            Задолженность по договору {$user->balance->zaim_number}
            <a onclick="$.magnificPopup.close(); ym(45594498, 'reachGoal', 'banner_collection_close_banner');" class="close-modal" href="javascript:void(0);">
                <small>X</small>
            </a>
        </div>
        {if !!$smarty.cookies.error}
            <h3 style="color:#d22;font-size:1.1rem;padding:0.5rem 1rem;display:block">
                {$smarty.cookies.error}
            </h3>
        {/if}
        {if $due_days > 1 && $prolongation_amount <= 0 && $saler_info['sale_info'] != 'Договор продан'}
            <div>
                Вы допустили просрочку по займу. Оплатите долг прямо сейчас.
            </div>
            <br>
            <div style="text-align: center">
                {if $due_days >= 0 && $due_days <= 8}
                    <button type="button" id="due_close_start" class="button medium" style="margin-bottom: 10px">Оплатить и взять новый</button>
                {/if}
                <button type="button" id="due_close_start" class="button medium">Оплатить</button>
            </div>
        {else}
            {if $due_days >= 1 && $due_days <= 30 && !$prolongation_available}
                <div>
                    Вы допустили просрочку по займу. Оплатите долг прямо сейчас.
                </div>
                <br>
                <div style="text-align: center">
                    {if $due_days >= 0 && $due_days <= 8}
                        <button type="button" id="due_close_start" class="button medium" style="margin-bottom: 10px">Оплатить и взять новый</button>
                    {/if}
                    <button type="button" id="due_close_start" class="button medium">Оплатить</button>
                </div>
            {else}
                {if $due_days <= 0}
                    {if $prolongation_available && $prolongation_amount > 0}
                        <div>
                            {if !empty($prolongation_text)}
                                {$prolongation_text}
                            {else}
                                Приближается срок погашения займа, но уже сейчас вы можете воспользоваться услугой «Пролонгация»
                            {/if}
                        </div>
                        <br>
                        <div style="text-align: center">
                            {if $due_days >= 0 && $due_days <= 8}
                                <button type="button" id="due_close_start" class="button medium" style="margin-bottom: 10px">Оплатить и взять новый</button>
                            {/if}
                            <button type="button" id="due_prolongation_start" class="button medium">Оформить пролонгацию</button>
                        </div>
                    {else}
                        <div>
                            Приближается срок погашения займа, но уже сейчас вы можете оплатить долг.
                        </div>
                        <br>
                        <div style="text-align: center">
                            {if $due_days >= 0 && $due_days <= 8}
                                <button type="button" id="due_close_start" class="button medium" style="margin-bottom: 10px">Оплатить и взять новый</button>
                            {/if}
                            <button type="button" id="due_close_start" class="button medium">Оплатить</button>
                        </div>
                    {/if}
                {/if}
                {if $due_days >= 1 && $due_days <= 9}
                    {if $prolongation_available && $prolongation_amount > 0}
                        <div>
                            Вы допустили просрочку займа. Воспользуйтесь услугой «Пролонгация» или оплатите заем прямо сейчас
                        </div>
                        <br>
                        <div style="text-align: center">
                            {if $due_days >= 0 && $due_days <= 8}
                                <button type="button" id="due_close_start" class="button medium" style="margin-bottom: 10px">Оплатить и взять новый</button>
                            {/if}
                            <button type="button" id="due_prolongation_start" class="button medium">Оформить пролонгацию</button>
                        </div>
                    {else}
                        <div>
                            Вы допустили просрочку займа. Оплатите заем прямо сейчас
                        </div>
                        <br>
                        <div style="text-align: center">
                            {if $due_days >= 0 && $due_days <= 8}
                                <button type="button" id="due_close_start" class="button medium" style="margin-bottom: 10px">Оплатить и взять новый</button>
                            {/if}
                            <button type="button" id="due_close_start" class="button medium">Оплатить</button>
                        </div>
                    {/if}
                {/if}
                {if $due_days >= 10 && $due_days <= 30}
                    {if $prolongation_available && $prolongation_amount > 0}
                        <div>
                            Вы допустили просрочку займа. Воспользуйтесь услугой «Пролонгация» или оплатите заем прямо сейчас
                        </div>
                        <br>
                        <div style="text-align: center">
                            <button type="button" id="due_prolongation_start" class="button medium">Оформить пролонгацию</button>
                            <button type="button" id="due_close_start" class="button medium">Погасить</button>
                        </div>
                    {else}
                        <div>
                            Вы допустили просрочку займа. Оплатите заем прямо сейчас
                        </div>
                        <br>
                        <div style="text-align: center">
                            <button type="button" id="due_close_start" class="button medium">Погасить</button>
                        </div>
                    {/if}
                {/if}
                {if $due_days > 90}
                    {if $saler_info['sale_info'] == 'Договор продан'}
                        <div>
                            Ваше дело передано в коллекторское агентство {$saler_info['name']}. Свяжитесь с агентством по номеру {$saler_info['phone_number']}
                        </div>
                    {else}
                        {if $saler_info['name'] == ''}
                            <div>
                                Вы допустили просрочку по займу. Оплатите долг прямо сейчас.
                            </div>
                            <br>
                            <div style="text-align: center">
                                <button type="button" id="due_close_start" class="button medium">Погасить</button>
                            </div>
                        {else}
                            <div>
                                Ваше дело передано в коллекторское агентство {$saler_info['name']}. Свяжитесь с агентством по номеру {$saler_info['phone_number']} или оплатите заем прямо сейчас.
                            </div>
                            <br>
                            <div style="text-align: center">
                                <button type="button" id="due_close_start" class="button medium">Оплатить</button>
                            </div>
                        {/if}
                    {/if}
                {/if}
            {/if}
        {/if}
    </div>
    <script src="design/{$settings->theme|escape}/js/due_block.js?v=1.003" type="text/javascript"></script>
{/if}

<script src="design/{$settings->theme}/js/sbp.js?v=1.010"></script>
<script src="design/{$settings->theme|escape}/js/accept_credit.js?v=1.026" type="text/javascript"></script>

{if $restricted_mode === 1 && (in_array($due_days, [0,1,2])) && $due_days !== 'not'}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-grid-only@1.0.0/bootstrap.min.css">
{/if}

{if $auto_approve_seconds_task}
    {include 'auto_approve_timer.tpl'}
{/if}
{*if ($view_partner_href || $partner_href_expired) && empty($disable_partner_href_autoredirect)*}
{if $view_partner_href && empty($disable_partner_href_autoredirect)}
    <script>
        let nextOrderDate = '{$reason_block|date} {$reason_block|time}';
        if (!localStorage.nextOrderDate || localStorage.nextOrderDate != nextOrderDate)
        {
            localStorage.nextOrderDate = nextOrderDate;
            localStorage.partnerHrefRedirects = 0;
        }

        if (Number(localStorage.partnerHrefRedirects) < 3)
        {
            localStorage.partnerHrefRedirects = Number(localStorage.partnerHrefRedirects) + 1;
            setTimeout(function () {
            {if $partner_href_expired && false}
                sendMetric('reachGoal', 'decline_monitoring_7')
                window.location.href = '{$partner_href_expired}';
            {else}
                sendMetric('reachGoal', 'decline_monitoring_8')
                invokeShopview('bonon-shop-window{$client_suffix}', '{$partner_href}')
                window.location.href = '{$partner_href}';
            {/if}
            }, 15000);
        }
    </script>
{/if}

<script src="/js/centrifugo/centrifuge.min.js"></script>
<script type="text/javascript">
    const centrifuge = new Centrifuge("{$config->CENTRIFUGO['socket_url']}/connection/websocket", {
        token: "{$centrifugo_jwt_token}"
    });

    centrifuge.on('connect', function (ctx) {
        console.log("connected", ctx);
    });

    centrifuge.on('disconnect', function (ctx) {
        console.log("disconnected", ctx);
    });

    centrifuge.connect();

    const subscription = centrifuge.newSubscription('check_auto_approve.{$user->id}');

    // Обработка события успешной подписки
    subscription.on('subscribed', function (ctx) {
        console.log('Subscribed to channel:', ctx.channel);
    });

    // Обработка события отписки
    subscription.on('unsubscribed', function (ctx) {
        console.log('Unsubscribed from channel:', ctx.channel);
    });

    // Обработка входящих сообщений
    subscription.on('publication', function (ctx) {
        console.log('Received message:', ctx.data);
        if (ctx.data.result) {
            document.getElementById('auto-approve-timer').remove();
            location.reload();
        }
    });

    // Подписываемся на канал
    subscription.subscribe();
</script>

<script type="text/javascript">
    var juicyLabConfig = {
        completeButton: "#repeat_loan_submit",
        apiKey: "{$juiceScoreToken}",
    };

    {literal}
    let sessionIdExist = (document.cookie.match(/(?:^|;\s*)juicescore_session_id=([^;]*)/) || [])[1];
    if (sessionIdExist) {
        window.addEventListener('load', function () {
            $('#juicescore_session_id').val($.cookie('juicescore_session_id'));
        });
    }
    else {
        var s = document.createElement('script');
        s.type = 'text/javascript';
        s.async = true;
        s.src = "https://score.juicyscore.com/static/js.js";
        var x = document.getElementsByTagName('head')[0];
        x.appendChild(s);

        window.addEventListener('load', function () {
            juicyScoreApi.getSessionId()
                .then(function (sessionId) {
                    $.cookie('juicescore_session_id', sessionId, {expires: 1, path: '/'});
                    $('#juicescore_session_id').val(sessionId);
                });
        });
    }
    {/literal}
</script>
{if $reason_block && $user->order['utm_source'] == 'sravni'}
    <script type="text/javascript">
        $(document).ready(function() {
            // Перенаправление с задержкой 5 секунд
            setTimeout(function() {
                window.location.href = 'https://goto.startracking.ru/api/v1/redirect?offer_id=2359&aff_id=12269&landing_id=11094&aff_sub2=1';
            }, 5000);
        });
    </script>
{/if}
<noscript>
    <img style="display:none;" src="https://score.juicyscore.com/savedata/?isJs=0"/>
</noscript>
