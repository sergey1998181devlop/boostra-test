{* Шаблон страницы зарегистрированного пользователя *}
{* Канонический адрес страницы *}
{$canonical="/user" scope=parent}

{$body_class = "gray" scope=parent}

{$add_order_css_js = true scope=parent}
{assign var="currentPage" value="user"}

<div id="userData" data-order-status="{$user->order['status']}" data-order-id="{$user->order['id']}" data-number="{$user->order['1c_id']}" style="display:none;"></div>

{capture name=page_scripts}
    <script src="design/boostra_mini_norm/js/mobile_download_banners/mobile_banners.js?v=1.002" type="text/javascript"></script>
    <script src="design/{$settings->theme|escape}/js/b2p.app.js?v=1.021" type="text/javascript"></script>
    <script src="design/{$settings->theme|escape}/js/user.js?v=1.405" type="text/javascript"></script>
    <script src="design/{$settings->theme|escape}/js/prolongation.app.js?v=1.06" type="text/javascript"></script>
    <script src="design/{$settings->theme|escape}/js/installment_payment_buttons.app.js?v=1.014" type="text/javascript"></script>
    <script src="design/{$settings->theme|escape}/js/user_statuses.js?v=0.001" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/friend_payment.js"></script>
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
                <button type="submit"
                        class="button big {if $config->snow}snow-relative primary{else}green{/if} bg-warning"
                >
                    {if $config->snow}
                        <img class="snow-man" src="design/orange_theme/img/holidays/snow/snow_man.png?v=2"
                             alt="Заявка на заём"/>
                    {/if}
                    Заявка на заём
                </button>
            </form>
        {elseif $quantity_loans_block}
            <div style="color:red;font-size:1.5rem;">
                Вы можете подать новую заявку не ранее чем {$quantity_loans_block|date} {$quantity_loans_block|time}
            </div>
            {*
            <p>
                <a href="partners" target="_blank" class="part-item__link button">Обратитесь к нашим партнерам</a>
            </p>
            *}
        {else}

            {if $user_discount}
                <input type="hidden" name="has_user_discount" value="1"/>
                <div class="discount_subtitle" style=";margin: 30px 0 10px 0;color:#21ca50;">
                    {if $user_discount->percent > 0}
                        Для вас есть акционное предложение: {$user_discount->percent*1}% по займу вместо 0.8%!
                        <br/>
                    {else}
{*                        Для вас доступен беспроцентный заём на {$user_discount->max_period} {$user_discount->max_period|plural:'день':'дней':'дня'}*}
{*                        <br/>*}
{*                        {if !$user_discount->end_date}*}
{*                            <a href="{$config->root_url}/files/docs/zaim_0.pdf" style="font-size:1rem" target="_blank">**}
{*                                Условия акции «ПЕРВЫЙ ЗАЁМ 0%»</a>*}
{*                        {/if}*}
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

    {if !$current_order['status']}
        <p>Спасибо за вашу заявку, она будет обработана в ближайшее время.</p>
    {/if}

    {if in_array($current_order['status'], [15, 16]) && $current_order['status'] !== 8}
        <div class="waits waits-transfer">
            <p>
                Ваша заявка одобрена!
                <br />
                Ожидайте, мы переводим Вам займ на карту.
            </p>
        </div>
    {elseif !in_array($current_order['status'], [8, 9, 10, 11, 13, 14, 17, 16]) && $current_order['1c_status'] == '3.Одобрено'}

        {include file='accept_credit.tpl' user_order=$current_order}

    {elseif in_array($current_order['status'], [8, 9, 14, 16]) || ($current_order['1c_status'] != '6.Закрыт' && in_array($current_order['status'], [10]))}
    {if $current_order['1c_status'] == '5.Выдан'}
        <style>
            .waits-transfer {
                display: none;
            }
        </style>
        {* Показываем баннер/модалку «Оцените наш сервис» только если НЕ бакеты 1–3 *}
        {if empty($debtInDays) || ($debtInDays < 1 && $debtInDays > 3)}
            <!--- Выводим модальное окно обратной связи --->
            {include file='modals/user_feedback_modal.tpl' user_id=$user->id order_id=$current_order['id']}
        {/if}
    {/if}
        {* Ложная ошибка. Просим пользователя ПЕРЕПРИВЯЗАТЬ свою карту на Финлаб *}
        {if $current_order['status'] == 14}
            <form id="confirm-card-form" class="confirm-card-form" data-order_id="{$current_order['id']}" onsubmit="return false;">
                <input type="hidden" name="card_id" value="{$current_order['card_id']}" />
                <input type="hidden" name="organization_id" value="{$current_order['organization_id']}">

                <div class="card-confirm" id="card-confirm">
                    <div class="error-block">
                        <p>Ошибка выдачи!</p>
                        <p>Привяжите карту!</p>
                    </div>

                    <div class="request-error-block" style="display: none">

                    </div>

                    <button class="confirm-button" id="confirm-button">Привязать</button>
                </div>
            </form>
        {/if}
        {* Если хотя бы по одной заявке есть статус STATUS_WAIT_CARD (14) - скрываем все блоки об успешном подписании договора *}
        {* Block hidden when status is 8 to allow cooling-off timer display *}

        {if !$is_admin && !$is_looker}
            <script type="text/javascript">
                $(document).ready(function (){
                    {if $user->loan_history|count == 0}
                        sendMetric('reachGoal', 'dogovor_podpisan_nk');
                    {else}
                        sendMetric('reachGoal', 'dogovor_podpisan_pk');
                    {/if}
                });
            </script>
        {/if}
        <style>
            .modal {
                display: none;
                position: fixed;
                z-index: 1;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                overflow: auto;
                background-color: rgba(0,0,0,0.4);
            }
            .modal-content {
                width: 320px;
                height: 240px;
                background-color: #FFFFFF;
                border-radius: 12px;
                padding: 6px;
                margin: 56px auto 0;
                text-align: center;
                border: 1px solid #888;
                position: relative;
            }

            #jubilee-modal .modal-content {
                height: 200px;
            }

            .btn-modal {
                display: block;
                padding: 5px;
                width: 258px;
                margin-top: 35px;
                margin-left:10px;
                background-color: #0997ff;
                color: #fff;
                text-decoration: none;
                border-radius: 5px;
                font-size: 15px;
            }
            .btn-modal:hover {
                background-color: #0080ff;
                text-decoration: none;
            }
            .close {
                position: absolute;
                top: 10px;
                right: 10px;
                font-size: 28px;
                font-weight: bold;
                color: #aaa;
                cursor: pointer;
            }
            .modal .modal-content h2 {
                font-size: 20px;
                font-weight: 600;
                margin-top: 10px;
            }
            #private .tabs .content .modal-text {
                margin: 18px;
                font-size: 14px;
                line-height: 1.7;
            }
        </style>
        <script type="text/javascript">
            $(document).ready(function() {
                $('#current-year').text(new Date().getFullYear());
                $(".close").click(function() {
                    $("#jubilee-modal").css("display", "none");
                });
                $(".btn-modal").click(function() {
                    ym(45594498, 'reachGoal', 'click_to_telegram_bot');
                });
            });
        </script>

    {elseif in_array($current_order['status'], [13])}

        <div class="waits">
            <p>
                С переводом средств возникла задержка.
                <br />
                Ожидайте, деньги поступят Вам на карту в ближайшее время.
            </p>
        </div>

    {elseif in_array($current_order['status'], [11])}

        <div >
            <p style="color:#d22">
                При переводе произошла ошибка
            </p>
        </div>
        {loan_form cards=$cards}

    {elseif $current_order['status'] == 5}

        <div class="files">
            <p>Некоторые ваши фото не прошли проверку. Для получения займа вам необходимо их заменить!</p>
            <a href="user/upload" class="button medium"> Заменить файлы</a>
        </div>

    {elseif $current_order['status'] == 1}

    {if $view_fake_first_order}
        <div>
            <p style="color:#d22">
                К сожалению Вам отказано.
                <br />Попробуйте отправить заявку повторно,
                <br />так как возможны технические сбои.
            </p>
            <form method="POST" id="repeat_loan_form">

                <input type="hidden" name="service_recurent" value="1" />
                <input type="hidden" name="service_sms" value="1" />
                <input type="hidden" name="service_insurance" value="1" />
                <input type="hidden" name="service_reason" value="0" />
                {if ($user_return_credit_doctor)}
                    <input type="hidden" name="service_doctor" value="0" />
                {else}
                    <input type="hidden" name="service_doctor" value="1" />
                {/if}
                <input type="hidden" name="service_recurent" value="1" />

                <input type="hidden" value="1" name="repeat_first_loan" />
                <input type="hidden" value="{$current_order['id']}" name="order_id" />

                <label class="js-accept-block medium left {if $error=='empty_accept'}error{/if}" >
                    <div class="checkbox">
                        <input class="js-input-accept" type="checkbox" value="1" id="repeat_loan_terms" name="accept" {if $accept}checked="true"{/if} />
                        <span></span>
                    </div>
                    Я ознакомлен и согласен <a href="javascript:void(0);" id="accept_link">со следующим</a>
                    <span class="error">Необходимо согласиться с условиями</span>
                </label>

                <p>
                    <button type="submit" id="repeat_loan_submit" class="button big">
                        Отправить повторно
                    </button>
                </p>
            </form>
        </div>
    {else}
        {*Если это автоодобрение и оно не готово покажем заглушку*}
    {if $current_order['utm_source'] == 'crm_auto_approve' && $user->auto_approve_order->status != 'SUCCESS'}
        <h3 class="text-orange">Определяем возможность выдачи займа, пожалуйста подождите</h3>
    {else}
        {if $current_order.is_new_card_linked}
        <div class="loan-review-status">
            <div class="status-content">
                <div class="status-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm0 18c-4.4 0-8-3.6-8-8s3.6-8 8-8 8 3.6 8 8-3.6 8-8 8zm.5-13H11v6l5.2 3.2.8-1.3-4.5-2.7V7z"/>
                    </svg>
                </div>
                <div class="status-text">Ваша заявка отправлена на повторное рассмотрение. Ожидайте!</div>
            </div>
        </div>
        {else}
            <div class="timer-wrapper">
                <blockquote class="timer__text">Деньги у вас через:</blockquote>
                <div id="countdown-container">
                    <div class="countdown-item" id="minutes-item">
                        <span id="minutes">02</span>
                        <p>минут</p>
                    </div>
                    <div class="countdown-item" id="separator-item">
                        <span id="separator">:</span>
                        <p></p>
                    </div>
                    <div class="countdown-item" id="seconds-item">
                        <span id="seconds">59</span>
                        <p>секунд</p>
                    </div>
                </div>
            </div>
        {/if}

        {if $has_vk}
            {include "vk_group_bot_widget.tpl"}
        {else}
            {include "loan_game.tpl"}
        {/if}
    {/if}
    {/if}
    {/if}

    {if $current_order['1c_status'] == '6.Закрыт' && ($user->file_uploaded || Helpers::isFilesRequired($user))}
        {loan_form cards=$cards}
    {/if}

    {if $reason_block}
        <span class="has-reason-block"></span>
    {if $current_order['status'] == 3}
        <p style="margin:1rem 0" class="warning-credit-text">
            К сожалению по Вашей заявке отказано.
            {if $current_order['official_response']}
                <br/>
                Причина отказа: {$current_order['official_response']}
            {/if}
        </p>
    {if in_array($current_order['reason_id'],[1,5,7,9,12,14,18,19,22,23,28,38]) && !$current_order['payment_refuser']}
        <a type="button" href="javascript:void(0)" order-id="{$current_order['id']}" order-number="{$user->balance->zaim_number}" class="button medium green" id="btn-modal-quick-approval">Узнай причину отказа за 49
            руб.</a>
        <span class="payment-block-error">
                    Не удалось оплатить
                </span>
    {/if}
    {/if}

    {if $reason_block == 999}
        <p style="margin:1rem 0" class="warning-credit-text">Вы не можете оставить заявку {$reason_block}</p>
    {else}
        <p style="margin:1rem 0" class="warning-credit-text">Вы можете повторно обратиться за займом : {$reason_block|date} {$reason_block|time} (мск)</p>
    {/if}

    {elseif $current_order['status'] == 3}
    {if $first_time_visit_after_rejection }
        <span class="first_time_visit_after_rejection"></span>
    {/if}
    {if $repeat_loan_block}
        <p>
            К сожалению по Вашей заявке отказано.
            {if $current_order['official_response']}
                <br/>
                Причина отказа: {$current_order['official_response']}
            {/if}
            <br/>
            {if in_array($current_order['reason_id'],[1,5,7,9,12,14,18,19,22,23,28,38]) && !$current_order['payment_refuser'] && $current_order['reason_id']}
                <a type="button" href="javascript:void(0)" order-id="{$current_order['id']}" order-number="{$user->balance->zaim_number}" class="button medium green" id="btn-modal-quick-approval">Узнай причину отказа за
                    49 руб.</a>
                <span class="payment-block-error">
                    Не удалось оплатить
                </span>
                <br/>
            {/if}
        </p>
        Вы можете повторно обратиться за займом {$repeat_loan_block|date} {$repeat_loan_block|time} (мск)

        {include file='credit_doctor/credit_doctor_allowed.tpl'}
        {include file='credit_doctor/credit_doctor_banner.tpl'}
    {elseif $next_loan_mandatory}
        <div class="clearfix">
            {if $user->file_uploaded || Helpers::isFilesRequired($user)}
                {loan_form cards=$cards}
            {/if}
        </div>
    {else}
    {if $user->fake_order_error == 0}

    {if !in_array($current_order['status'], [11])
        && !$user->file_uploaded
        && !Helpers::isFilesRequired($user)
        && ($quantity_loans_block
        || $redirect)
        && $current_order['1c_status'] != '6.Закрыт'}
        <p class="warning-credit-text">К сожалению по Вашей заявке от {$current_order['date']|date} отказано.</p>
    {/if}

    {if in_array($current_order['reason_id'],[1,5,7,9,12,14,18,19,22,23,28,38]) && !$current_order['payment_refuser']  && $current_order['reason_id']}
        <a type="button" href="javascript:void(0)" order-id="{$current_order['id']}" order-number="{$user->balance->zaim_number}" class="button medium green" id="btn-modal-quick-approval">Узнай причину отказа за 49
            руб.</a>
        <span class="payment-block-error">
                    Не удалось оплатить
                </span>
    {/if}

    {if $user->id != 42863} {* фикс для одного пользователя (просьба Толика) *}
    {if $current_order['official_response']}
        <p class="warning-credit-text">Причина отказа: {$current_order['official_response']}</p>
    {else}
    {if $collapse_rating_banner && $show_rating_banner}
        <p style="margin: 0;">
            <a
                    href="#"
                    onclick="return showRatingBanner();"
                    style="font-size: 1.5rem;
                                                text-decoration: underline;
                                                color: #701ecb;
                                                text-decoration-color: #701ecb;"
            ><b>Почему отказано в займе?</b></a>
        </p>
        {sendMetric}
        {include 'credit_rating/credit_rating.tpl'}
    {/if}
    {/if}
    {/if}
        {include file='credit_doctor/credit_doctor_allowed.tpl'}
        {include file='credit_doctor/credit_doctor_banner.tpl'}

    {/if}
        <div class="clearfix">
            {if $user->file_uploaded || Helpers::isFilesRequired($user)}
                {loan_form cards=$cards}
            {/if}
        </div>
    {/if}
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

{*if !$consultation_send}
    {include file='consultation_form_v2.tpl'}
{/if*}

{*if !$consultation_send}
    {include file='consultation_form.tpl'}
{/if*}
<input type="hidden" class="user-id" data-id="{$user->id}">
<section id="private">
    <input type="hidden" name="is_new_client" value="{$is_new_client}"/>
    <input name="use_b2p" value="{$use_b2p}" type="hidden"/>
    <div>
        <div class="tabs {if $action=='user'}lk{elseif $action=='history'}history{/if}">

            {include file='user_nav.tpl' current='user'}

            <div class="content">

                {if $action == "user"}

                    {if $restricted_mode === 1 && (in_array($due_days, [0])) && $due_days !== 'not'}

                    <div class="container">
                        <div class="row">
                            <div class="col-md-6 row restrict_mode_panel">
                                <div class="col-md-8 restrict_info">
                                    <h2>{$salute_prefix|escape}, <span class="restrict_salute">{$salute|escape}</span>!</h2>
                                    <div class="restrict_alert row">
                                        <div class="col-md-3 hidden-xs">
                                            <img src="design/{$settings->theme|escape}/img/restrict/alert1.png">
                                        </div>
                                        <div class="col-md-7">
                                            Предлагаем Вам воспользоваться <span style="color: #684A2D; text-decoration: underline">уникальным предложением</span> для постоянных клиентов, которые ценят своё время и деньги.
                                        </div>
                                    </div>
                                    <div class="restrict_info_text">Мы подготовили для Вас заём с увеличенной суммой и уверены, что новый заём станет для Вас еще одним шагом к финансовому благополучию и поможет достичь тех целей, к которым Вы стремитесь.</div>
                                    <br>
                                    <span class="restrict_divider"></span><br>
                                    <div class="restrict_alert_text">
                                        Помните, что каждое Ваше решение открывает двери к новым возможностям.
                                    </div>
                                    <div class="restrict_alert row">
                                        <div class="col-md-3 hidden-xs">
                                            <img src="design/{$settings->theme|escape}/img/restrict/alert2.png">
                                        </div>
                                        <div class="col-md-7">
                                            Мы верим в Вас и Вашу способность делать правильные шаги на пути к успеху. Давайте вместе строить Ваше блестящее будущее.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-md-offset-1 hidden-xs restrict_sidebar">
                                    <img src="design/{$settings->theme|escape}/img/restrict/sidebar.png">
                                </div>
                                <div class="clear"></div>
                                <div class="restrict_img_bg hidden-xs" style="background: url('design/{$settings->theme|escape}/img/restrict/bg_img.png')"></div>
                                {foreach $all_orders as $key => $orders_data}
                                    {foreach $orders_data as $order_data}
                                        {if $order_data->balance->zaim_number != null}
                                            {if $order_data->order->additional_service_repayment}
                                                {if ($order_data->balance->ostatok_od + $order_data->balance->ostatok_percents + $order_data->balance->ostatok_peni >= 500)}
                                                    <input type="hidden" name="tv_medical_amount" value="{$vita_med->price}"/>
                                                    <input type="hidden" name="tv_medical" value="1"/>
                                                    <input type="hidden" name="tv_medical_id" value="{$vita_med->id}"/>
                                                    {assign var="amount_value" value=$order_data->balance->ostatok_od + $vita_med->price + $order_data->balance->ostatok_percents + $order_data->balance->ostatok_peni + $order_data->balance->penalty}
                                                {else}
                                                    {assign var="amount_value" value=$order_data->balance->ostatok_od + $order_data->balance->ostatok_percents + $order_data->balance->ostatok_peni + $order_data->balance->penalty}
                                                {/if}
                                            {else}
                                                {assign var="amount_value" value=$order_data->balance->ostatok_od + $order_data->balance->ostatok_percents + $order_data->balance->ostatok_peni + $order_data->balance->penalty}
                                            {/if}
                                            <br>
                                            <div class="restrict_loan_info">
                                                <div class="float_left_block" style="margin-right: 50px;">
                                                    <p>Номер договора</p>
                                                    <h3>{$order_data->balance->zaim_number}</h3>
                                                </div>
                                                <div class="float_left_block">
                                                    <p>Сумма долга</p>
                                                    <h3>{$order_data->balance->ostatok_od + $order_data->balance->ostatok_percents + $order_data->balance->ostatok_peni + $order_data->balance->penalty} руб.</h3>
                                                </div>
                                                <div class="clear"></div>
                                                <div>
                                                    <form method="POST" action="user/payment" class="user_payment_form" style="margin: 0;">
                                                        <div class="action">
                                                            {if $order_data->order->additional_service_repayment}
                                                                {if ($order_data->balance->ostatok_od + $order_data->balance->ostatok_percents + $order_data->balance->ostatok_peni >= 500)}
                                                                    <input type="hidden" name="tv_medical_amount" value="{$vita_med->price}"/>
                                                                    <input type="hidden" name="tv_medical" value="1"/>
                                                                    <input type="hidden" name="tv_medical_id" value="{$vita_med->id}"/>
                                                                {/if}
                                                            {/if}
                                                            <input type="hidden" name="amthash" value="{base64_encode($amount_value)}">
                                                            <input type="hidden" name="number" value="{$order_data->balance->zaim_number}"/>
                                                            <input type="hidden" name="order_id" value="{$order_data->order->order_id}"/>
                                                            <input style="display:none" class="payment_amount"
                                                                   data-order_id="{$order_data->balance->zaim_number}" data-user_id="{$user->id}" type="text"
                                                                   name="amount"
                                                                   value="{$amount_value}"
                                                                   max="{$amount_value}" min="1"/>
                                                            <button class="restrict_button" data-user="{$user->id}"
                                                                    data-event="4" type="submit">Заплатить и взять новый
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        {/if}
                                    {/foreach}
                                {/foreach}
                            </div>
                        </div>
                    </div>
                    {else}
                        {include 'user_current_loan_list.tpl'}
                    {/if}
                {/if}

                {if !$has_active_loans}
                    {include './mobile_banners/link_banner.tpl' banner_img_android="design/boostra_mini_norm/assets/image/banner_rustore_lk_img.png" banner_img_ios="design/boostra_mini_norm/assets/image/banner_ios_lk_img.png" banner_link="https://apimp.boostra.ru/get_app.php?slot=b3"}
                {/if}
                {if $action=="history"}
                    <div class="panel">
                        {*if $current_orders}
                        <div class="list current">
                            <h4>Открытый займ.</h4>
                            <ul class="table">
                                {foreach $current_orders as $order}
                                <li>
                                    <div>
                                        <span class="card master">
                                        </span>
                                    </div>
                                    <div>
                                        Займ на
                                        <strong>{$order->amount*1} {$currency->sign|escape}</strong>
                                    </div>
                                    <div>
                                        Заявка
                                        <a href='order/{$order->url}'>
                                        <strong>{$order->id}</strong>
                                        </a>
                                    </div>
                                    <div>
                                        Дата заявки
                                        <strong>
                                        {$order->date|date}
                                        </strong>
                                    </div>
                                    <div>
                                    <!--
                                        Просрочен на
                                        <strong>2 дня</strong>
                                        -->
                                    </div>

                                </li>
                                {/foreach}
                            </ul>
                        </div>
                        {/if*}
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

<script src="design/{$settings->theme|escape}/js/accept_credit.js?v=1.019" type="text/javascript"></script>

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
