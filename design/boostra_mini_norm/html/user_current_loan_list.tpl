<div class="panel">
    {if $restricted_mode === 1 && !$friend_restricted_mode && (in_array($due_days, [1,2])) && $due_days !== 'not'}
        <div class="restrict_alert row" style="width: 400px;">
            <div class="col-md-2 hidden-xs">
                <img src="design/{$settings->theme|escape}/img/restrict/alert1.png">
            </div>
            <div class="col-md-8">
                Мы подготовили для Вас заём с увеличенной суммой.
                Предлагаем Вам воспользоваться <span style="color: #684A2D; text-decoration: underline">уникальным предложением</span> для постоянных клиентов, которые ценят своё время и деньги.
                <b>Спешите, осталось всего 13 предодобренных займов!</b>
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
                                                    data-event="4" type="submit">Погасить и воспользоваться предложением
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
    {/if}
    <style type="text/css">
        .get_prolongation_modal {
            height: 54px;
        }

        .user_info {
            display: flex;
            gap: 5%;
            flex-direction: column;
        }

        .send_complaint {
            background-color: #888585;
        }

        .send_complaint:hover {
            background-color: #575252;
        }

        #company_form {
            border: 2px dashed #2c2b39;
            display: block;
            padding: 10px;
            border-radius: 10px;
            margin: 15px 0;
            background: #fcc512;
            max-width: 480px;
            text-align: center;
        }

        #private .tabs .content #company_form p {
            margin: 0;
            font-size: initial;
        }

        #company_form a {
            display: block;
            background: #2c2b39;
            color: white;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 10px;
        }

        @media screen and (max-width: 768px) {
            #private .tabs .content #company_form p {
                font-size: 12px;
            }
        }
        .logout_hint{
            border: 1px solid red;
            color: red;
            width: fit-content;
            padding: 10px;
            border-radius: 10px;
            margin-right: 10px;
        }

        .logout_hint a{
            color: blue;
            text-decoration: underline;
        }
        .carousel-container-banners {
            width: 100%;
            max-width: 800px;
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            box-sizing: border-box;
            /*для промо*/
            padding: 0 !important;
            height: 290px !important;
        }

        .user_info-banner{
            display: flex;
            flex-direction: column;
            margin-bottom: 10px;
        }
        .user_info-banner > h1:only-child {
            font-size: clamp(24px, 21.6px + 0.65vw, 32px);
            margin-top: 0;
        }
        .carousel-container-banners .owl-carousel {
            height: 100%;
        }
        .carousel-container-banners .item {
            text-align: center;
            box-sizing: border-box;
            display: flex !important;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;
        }

        .carousel-container-banners .owl-stage {
            display: flex;
            flex-wrap: nowrap;
            transition: transform 0.5s ease-in-out;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }

        .carousel-container-banners  .owl-nav {
            position: absolute;
            top: 45%;
            width: 100%;
            display: flex;
            justify-content: space-between;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .carousel-container-banners .owl-nav button {
            pointer-events: all;
            background: lightgray !important;
            color: #fff;
            border: none;
            width: 50px;
            height: 50px;
            font-size: 20px;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .carousel-container-banners  .owl-carousel .owl-item img {
            width: 100%;
            height: 100%;
        }
        .carousel-container-banners  .owl-stage-outer {
            height: 100%;
        }
        .carousel-container-banners {
            height: 250px;
        }
        .carousel-container-banners .additional_service__banner___text{
            padding: 0;
            justify-content: center;
        }
        .carousel-container-banners .additional_service__pay__credit__banner___details {
            margin: 0;
        }
        .carousel-container-banners .additional_service__banner___text .btn  {
            font-size: 18px;
        }
        .carousel-container-banners .about_promotion_div {
            margin: 0;
        }
        @media (max-width: 600px) {
            .carousel-container-banners .owl-carousel .item {
                padding: 5px;
            }
            .carousel-container-banners {
                height: 230px;
            }
            .carousel-container-banners .owl-nav button {
                width: 35px;
                height: 35px;
            }
        }

    </style>
    <style>
        .banner img {
            width: auto !important;
            height: auto !important;
        }
        .item .banner-container .banner-content .banner-logo {
            margin: 0 !important;
            padding: 10px 0 0 0 !important;
            line-height: unset !important;
        }
         .item .banner-container .banner-content .banner-text {
            font-size: 1rem !important;
            font-weight: 500;
            margin: 20px 0 0 0 !important;
            padding: 0;
        }

    </style>
    <style>
        .item .banner-container .banner-content .banner-logo {
            margin: 0 !important;
            padding: 10px 0 !important;
            line-height: unset !important;
        }
        .item .banner-container .banner-content .banner-text {
            font-size: 1rem !important;
            font-weight: 500;
            margin: 20px 0 0 0 !important;
            padding: 0;
        }

        .banner-button p {
            margin: 0 !important;
            padding: 0 !important;
        }

        .banner-button .promocode {
            font-size: 1.5rem;
            font-weight: 700;
            color: #000;
        }

        .banner-button .icon, .banner-button .thumbs-up {
            position: absolute;
            right: 20px;
            top: 40%;
        }

        .banner-link {
            color: #000;
            text-decoration: none;
            font-size: 1rem;
            display: block;
            margin: 5px 0;
            font-weight: 500;
        }

        .banner-2-img-1 {
            position: absolute;
            right: 0;
            bottom: 0;
        }

        @media (max-width: 1200px) {
            .banner {
                width: 90%;
            }
        }

        @media (max-width: 768px) {
            .banner {
                background: none;
            }

            .banner-content {
                padding: 15px;
                width: 100%;
            }

            .banner-medium-text {
                font-size: 1.6rem;
            }

            .banner-button {
                padding: 10px 60px;
            }

            .banner-button p:first-child {
                font-size: 1.4rem;
            }

            .item .banner-container .banner-content .banner-logo {
                text-align: center !important;
                padding: 0 !important;
            }

            .item .banner-container .banner-content .banner-logo img {
                width: 80% !important;
                display: inline !important;
            }

            .banner-link {
                font-size: 1.3rem;
            }
        }

        @media (max-width: 480px) {
            .banner-link {
                font-size: 1.2rem;
            }

            .banner-content {
                padding: 10px;
            }

            .banner-medium-text {
                font-size: 1.2rem;
            }

            .banner-button {
                padding 8px 40px;
            }

            .banner-button p:first-child {
                font-size: 1.1rem;
            }

            .banner-button .promocode {
                font-size: 1.2rem;
            }

            .banner-button .icon {
                right: 10px;
            }
        }
    </style>

    <style>
        .any-faq {
            display: inline-block;
            padding: 12px 24px;
            font-size: 0.9rem;
            color: #dc3545;
            background-color: white;
            border: 2px solid #dc3545;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .any-faq:hover {
            background-color: #dc3545;
            color: white;
            text-decoration: none;
        }

        .salute-row {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .salute-row h1 {
            margin: 0;
            display: inline-block;
            font-size: 1.8rem;
        }

        .promo-badge{
            padding:.7rem 1.17rem;
            background:#fff;
            border:2px solid #0997FF;
            color:#0997FF;
            border-radius:999px;
            font-weight:600;
            font-size:.95rem;
            cursor:pointer;
            line-height:1;
            transition:.15s ease-in-out;
        }
        .promo-badge:hover{ background:#E9F5FF; }


        .promo-modal .promo-card{
            width:100%;
            max-width:420px;
            min-width:260px;
            margin:0 auto;
            padding:28px 24px;
            text-align:center;
            background:#fff;
            border-radius:20px;
            box-shadow:0 8px 24px rgba(0,0,0,.12);
        }
        .promo-modal .promo-title{
            font-size:28px;
            font-weight:800;
            margin:4px 0 14px;
        }
        .promo-modal p{
            margin:0 0 18px;
            font-size:16px;
            line-height:1.45;
            color:#2E2E2E;
        }

        .promo-modal .back-btn{
            display:inline-block;
            padding:10px 18px;
            background:#fff;
            border:2px solid #0997FF;
            color:#0997FF;
            border-radius:999px;
            font-weight:600;
            cursor:pointer;
            transition:.15s;
        }
        .promo-modal .back-btn:hover{ background:#E9F5FF; }
    </style>

    <div class="user_info">
        <div class="user_info-banner">
            <h1>{$salute|escape}</h1>
            {if !$friend_restricted_mode}
                {if $view_partner_href}
                    <div class="item">
                        {include
                        file='partials/partner_link_banner.tpl'
                        margin='1rem 0 16px 0'
                        metric_id='11'
                        background_link=$background_href
                        client_suffix=$client_suffix
                        partner_link=$partner_href
                        ab_key=$ab_key}
                    </div>
                {elseif $partner_href_expired}
                    <div class="item">
                        {include
                        file='partials/partner_link_banner.tpl'
                        margin='1rem 0 16px 0'
                        metric_id='10'
                        client_suffix=$client_suffix
                        partner_link=$partner_href_expired
                        ab_key=$ab_key}
                    </div>
                {/if}
            {/if}
        </div>
        <input type="hidden" value="{$full_payment_amount_done}" id="full_payment_amount_done">
    </div>

        {if $cross_orders && $cross_orders_up}
            {foreach $cross_orders as $cross_order}
                {view_order current_order=$cross_order}
            {/foreach}                                
        {/if}                        

    {if $restricted_mode_logout_hint === 1}
        <div class="logout_hint">
            <span>Личный кабинет работает в ограниченном режиме.</span>
            <br>
            <span>Для взятия нового займа нажмите </span>
            <a href="/user/logout">Перезайти</a>
        </div>
    {/if}

    {if $restricted_mode !== 1}
        {if ($user->gender == 'female') && ($smarty.now|date_format:"%Y%m%d" < '20240311') }{* Поздравление с 8 марта*}
            {include 'block/8march.tpl'}
        {/if}

        {assign var="date_finish" value="2024-01-09 0:00:00"}
        {if ($smarty.now < strtotime($date_finish))}
            <style type="text/css">
                #info_banner {
                    color: #664d03;
                    background-color: #fff3cd;
                    border-color: #ffecb5;
                    max-width: 685px;
                    padding: 1rem 1rem;
                    margin-bottom: 1rem;
                    border: 1px solid transparent;
                    border-radius: 0.25rem;
                    margin-bottom: 3rem;
                }
            </style>
            <div id="info_banner">
                Уважаемые Клиенты, просим учитывать, что при осуществлении оплаты Договора займа по <strong>РЕКВИЗИТАМ</strong> с 29.12.2023 г. по 08.01.2024 г., денежные средства будут проведены в счет оплаты задолженности не ранее 09.01.2024 г.
            </div>
        {/if}

        {if !$collapse_rating_banner}
            {include 'credit_rating/credit_rating.tpl'}
        {/if}

        {if $indulgensia}
            <h3 class="green" style="line-height:1.2;font-weight:normal;margin-bottom:1rem">
                Прощаем вам все начисления и проценты по займу, верните ровно столько, сколько брали!
                <br />
                Акция действительна только 3 дня с 17 по 19 ноября включительно.
                <br />
                Оплатите через кнопку "Оплатить другую сумму", позвоните на <a href="tel:88003333073">88003333073</a>

                и мы закроем ваш долг!
            </h3>
        {/if}

    {if Helpers::isFilesRequired($user) && !$user->file_uploaded && (!$user->balance->zaim_number || $user->balance->zaim_number=='Нет открытых договоров')}
        <div class="files">
            <p>
                Прикрепите фотографии с лицом и паспортом для подтверждения
            </p>
            <a href="user/upload" class="button medium btn-600 btn-fsize-14 btn-line-h-24"> Добавить</a>
        </div>
    {/if}

    {/if}

    {if $restricted_mode_logout_hint !== 1}
        {if $all_orders}
            {include 'user_current_divide_orders.tpl' divide_order=$all_orders last_order=null exitpool_completed=true}
        {/if}

        {if $divide_order}
            {include 'user_current_divide_orders.tpl'}
        {else}
            {include 'user_current_loan.tpl'}
        {/if}
    {/if}


    {if $restricted_mode !== 1}

        {if $user->cdoctor_pdf}
            <div class="cdoctor-file" style="background:url(design/{$settings->theme|escape}/img/cdoctor.svg) right center no-repeat">
                <div class="cdoctor-file-left">
                    <div class="cdoctor-file-title">Зачем ждать, когда можно действовать?</div>
                    <div class="cdoctor-file-info">Мы уже получили Вашу кредитную историю и принимаем решение. Узнайте больше.</div>
                    <a href="{$user->cdoctor_pdf}" target="_blank" class="button medium">Узнать кредитную историю</a>
                </div>
                <div class="cdoctor-file-left">
                </div>
            </div>
        {/if}

    {if $show_company_form && $config->available_company_btn_form}
        <div id="company_form">
            <a href="{$config->root_url}/company_form">Займ для ИП и ООО</a>
            <p>(для индивидуальных предпринимателей и юридических лиц)</p>
        </div>
    {/if}

    {if !in_array($user->order['status'], [8, 9, 10, 11, 13]) && $user->order['1c_status'] == '3.Одобрено'}
        {include 'promocode.tpl'}
        {assign var="last_digit" value=$user->order['id']|substr:-1}
        {assign var="search_pattern" value="i:`$last_digit`;"}
        {if strpos($settings->promo_mobile_banner_for_orders_end, $search_pattern) !== false
        && count($all_orders->orders) < 2}

            {include './mobile_banners/link_banner.tpl'
            banner_img_android="design/boostra_mini_norm/assets/image/promocode_android.png"
            banner_img_ios="design/boostra_mini_norm/assets/image/promocode_ios.png"
            banner_link="https://apimp.boostra.ru/get_app.php?slot=b_promo&user_id={$user->id}"}
        {/if}
    {/if}

        {if $autoapprove_card_reassign || $is_need_choose_card}
            <p class="autoapprove_card_security">
                <span class="autoapprove_card_security__title">Важно! Мы повысили уровень безопасности Ваших персональных данных.</span>
                <br>Для дальнейшего совершения операций с денежными средствами необходимо перепривязать Вашу карту.
                <br>Добавьте дебетовую (обычную) карту. На неё мы продолжим зачислять Вам деньги.
                <br>При необходимости Вы можете добавить более одной карты и выбирать любую из них для зачисления/списания средств.
            </p>
        {/if}

{*        {if $is_user_order_taken}*}
{*            {if $settings->banner_url_a && $settings->banner_url_b}*}
{*                {if round(($settings->banner_clicks_a/($settings->banner_clicks_a + $settings->banner_clicks_b))*100) >= rand(1,100)}*}
{*                    <a class="likezaim_banner" href="{$settings->banner_url_a}" target="_blank">*}
{*                        <img src="/design/boostra_mini_norm/img/banners/likezaim.png" alt="likezaim" />*}
{*                    </a>*}
{*                {else}*}
{*                    <a class="likezaim_banner" href="{$settings->banner_url_b}" target="_blank">*}
{*                        <img src="/design/boostra_mini_norm/img/banners/marketplace.png" alt="marketplace" />*}
{*                    </a>*}
{*                {/if}*}
{*            {/if}*}
{*        {/if}*}

        {if $likezaim && $likezaim->link}
            <a class="likezaim_banner" href="{$likezaim->link}" target="_blank">
                <img class="likezaim_banner" src="/design/boostra_mini_norm/img/banners/likezaim.png" alt="likezaim classic"/>
            </a>
        {/if}

    {include file='cards_list.tpl'}

    <div style="padding-top: 15px;">
        <a href="/user/faq" class="any-faq">Остались вопросы?</a>
    </div>
    <div style="padding-top: 15px;">
        <div class="rs-payment-button-wrapper"
             style="position: relative; display: inline-block;">
            <button class="payment_button button button-inverse btn-600 btn-fsize-14 btn-line-h-24" type="button" id="openRsModal"
                    >Я уже оплатил заём полностью
            </button>

            {if $payment_rs_data && $payment_rs_data->status == 'approved' && $payment_rs_data->is_recent}
                <div class="payment-notification success">
                    <div class="payment-notification-header">
                        <span class="payment-notification-icon">✅</span>
                        <strong class="payment-notification-title">Платёж успешно принят</strong>
                    </div>
                    <p class="payment-notification-message">Ваш чек загружен и платёж подтверждён.</p>
                    <div class="payment-notification-content info">
                        <strong>Обращаем внимание:</strong><br>
                        – Если оплата произведена на полную сумму задолженности, договор будет закрыт в течение
                        трёх рабочих дней.<br>
                        – Если по истечении этого срока статус договора не обновился, пожалуйста, обратитесь
                        через форму обратной связи или в чат поддержки.
                    </div>
                </div>
            {elseif $payment_rs_data && $payment_rs_data->status == 'cancelled' && $payment_rs_data->is_recent}
                <div class="payment-notification error">
                    <div class="payment-notification-header">
                        <span class="payment-notification-icon">❌</span>
                        <strong class="payment-notification-title">Платёж не подтверждён</strong>
                    </div>
                    <div class="payment-notification-content">
                        {if $payment_rs_data->reject_reason == 'insufficient_funds'}
                            <strong>Причина:</strong>
                            Недостаточно средств для закрытия договора
                            <br>
                            <br>
                            <strong>Что делать:</strong>
                            Необходимо внести полную сумму для оплаты.
                        {elseif $payment_rs_data->reject_reason == 'wrong_requisites'}
                            <strong>Причина:</strong>
                            Оплата произошла по некорректным реквизитам
                            <br>
                            <br>
                            <strong>Что делать:</strong>
                            Оплата поступила по некорректным реквизитам.
                            <br>
                            <br>
                            Пожалуйста, опишите ситуацию на нашу эл. почту и приложите свои реквизиты для возврата платежа.
                            <br>
                            Затем внесите оплату по корректным реквизитам.
                        {elseif $payment_rs_data->reject_reason == 'wrong_photo'}
                            <strong>Причина:</strong>
                            Приложили иное фото
                            <br>
                            <br>
                            <strong>Что делать:</strong>
                            Необходимо приложить скриншот чека.
                        {elseif $payment_rs_data->reject_reason == 'duplicate_receipt'}
                            <strong>Причина:</strong>
                            Дубль чека
                            <br>
                            <br>
                            <strong>Что делать:</strong>
                            Этот чек уже был загружен ранее.
                            Приложите актуальный чек.
                        {else}
                            <strong>Что делать:</strong>
                            К сожалению, чек не был принят.
                            <br>
                            Пожалуйста, проверьте данные и при необходимости повторно загрузите чек или свяжитесь с поддержкой.
                        {/if}
                    </div>
                </div>
            {/if}
        </div>
    </div>
        {if $has_active_loans}
            {include './mobile_banners/link_banner.tpl' banner_img_android="design/boostra_mini_norm/assets/image/banner_rustore_lk_img.png" banner_img_ios="design/boostra_mini_norm/assets/image/banner_ios_lk_img.png" banner_link="https://apimp.boostra.ru/get_app.php?slot=b3"}
            <div class="download_banners_wrapper" style="padding: 30px 0 10px">
                <h2 style="margin-bottom: 20px;">Где безопасно скачать или обновить приложение Boostra</h2>
                <h3 style="margin-bottom: 20px;">Способ 1. В популярных магазинах приложений</h3>

                <div class="app_block">
                    <a href="https://redirect.appmetrica.yandex.com/serve/749593578275145650">
                        <img style="width:220px" src="design/{$settings->theme|escape}/img/nashstore_icon.png" alt="nashstore_icon"/>
                    </a>
                    <a href="https://redirect.appmetrica.yandex.com/serve/821651145620505260">
                        <img style="width:220px" src="design/{$settings->theme|escape}/img/rustore_icon.png" alt="rustore_icon"/>
                    </a>
                    {* <a href="https://redirect.appmetrica.yandex.com/serve/533644391370206393">
                        <img style="width:220px" src="design/{$settings->theme|escape}/img/appstore_icon.png" alt="appstore_icon"/>
                    </a> *}
                </div>

                <h3 style="margin-bottom: 20px;">Способ 2. Скачайте и установите наше приложение, напрямую по ссылке ниже</h3>
                <a class="button button-inverse btn-600 btn-fsize-14 btn-line-h-24" target="_blank" href="https://redirect.appmetrica.yandex.com/serve/749593560105358068">Скачать для Android</a>
            </div>
        {/if}
        {*
        {if ($banners_count > 0 && !$payCredit) ||  ($userGift->got_gift && !$payCredit)}
            <div class="partner-banners">
                <h2>Вам {if $banners_count == 1}доступно ПО от нашего партнёра{else}доступны ПО от наших партнёров{/if}</h2>
                {if $has_credit_doctor || ($userGift->got_gift && !$payCredit)}
                    {include file='credit_doctor_banner.tpl'}
                {/if}
            </div>
        {/if}
        *}

        {if $user->balance->zaim_number == 'Нет открытых договоров' && (!$user->order || $user->order['status'] == 3 || $user->order['status'] == 2)}
            <div class="remove_account_block">
                <a href="javascript:void(0);" data-modal_mf="confirm_remove_account">Удалить личный кабинет</a>
            </div>
            {include 'modals/modal_asp_contract_deleted_user_cabinet.tpl'}
        {/if}

    {/if}
    <input type="hidden" id="show-modal-asp" value="{$show_asp_modal}">
    <input type="hidden" id="input-is-admin" value="{$is_admin}">
    <input type="hidden" id="input-is-looker" value="{$is_looker}">
    <div id = 'div-show-asp-modal' style="display: none">
        <style>
            @font-face{
                font-family: 'Circe';
                font-weight: 400;

                src: url('../fonts/Circe.woff') format('woff'), url('../fonts/Circe.ttf');
            }
            .arbitration-modal-content {
              width: 30%;
              margin: 0 auto;
              padding: 20px;
              position: relative;
              background: #FFFFFF;
              box-shadow: 0 4px 10px 2px rgba(129, 129, 129, 0.25);
              border-radius: 15px;
              min-width: 200px;
              max-width: 400px;
            }
            .arbitration-modal-content [name="sms_asp"] {
              max-width: 100px;
            }
            #asp_sms {
                width: 30%;
                margin: 0 auto;
                padding: 20px;
                position: relative;
                background: #FFFFFF;
                box-shadow: 0px 4px 10px 2px rgba(129, 129, 129, 0.25);
                border-radius: 15px;
                min-width: 200px;
            }
            .arbitration-agreement-modal-header{
              display: flex;
              justify-content: end;
            }
            .additional-agreement-modal-header{
                display: flex;
                justify-content: space-between;
                margin-bottom: 20px;
            }
            .arbitration-agreement-modal-header>h3,
            .additional-agreement-modal-header>h3 {
                font-family: 'Circe';
                font-style: normal;
                font-weight: 700;
                font-size: 20px;
                line-height: 120%;
                color: #2E2E2E;
            }
            .arbitration-agreement-modal-input-div,
            .additional-agreement-modal-input-div{
                margin-bottom: 20px;
            }
            .arbitration-agreement-modal-input-div>input,
            .additional-agreement-modal-input-div>input {
                -webkit-appearance:checkbox;
                color: #0997FF;
                border: 1px solid #0997FF;
                border-radius: 2px;
            }
            .arbitration-agreement-modal-input-div>span,
            .additional-agreement-modal-input-div>span{
                font-family: 'Circe';
                font-style: normal;
                font-weight: 400;
                font-size: 15px;
                line-height: 120%;
                color: #2E2E2E;
            }
            .arbitration-agreement-modal-header>a,
            .additional-agreement-modal-header>a{
                text-decoration: none !important;
                color: #0997FF;
                font-size: 16px;
            }
            #asp_sms [name="sms_asp"] {
                max-width: 100px;
            }
            .asp-sign-accept{
                background: #0997FF;
                border-radius: 232px !important;
                font-family: 'Circe';
                font-style: normal;
                font-weight: 700;
                font-size: 15px;
                line-height: 100%;
                color: #FFFFFF;
                border: 1px solid transparent;
            }
            .sms-asp-send-button:hover,.asp-sign-accept:hover {
                background: #FFFFFF;
                box-shadow: 0px 4px 10px 2px rgba(129, 129, 129, 0.25);
                border-radius: 15px;
                color: #0997FF;
                border: 1px solid #0997FF;
            }
            .arbitration-agreement-modal .error,
            .additional-agreement-modal .error{
                color: red !important;
            }
            .text-error{
                font-family: 'Circe';
                font-style: normal;
                font-weight: 700;
                font-size: 15px;
                line-height: 100%;
            }

            #removeCardModal {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 30%;
                min-width: 230px;
                height: 200px;
                background: white;
                display: flex;
                justify-content: center;
                align-items: center;
                flex-direction: column;
            }
            .removeCardModal-close {
                position: absolute;
                top: 10px;
                right: 50px;
                font-size: 20px;
                cursor: pointer;
            }
            #removeCardModal-buttons {
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 20px;
                margin-top: 20px;
            }
            .arbitration-agreement-modal-input-div>.error,
            .additional-agreement-modal-input-div>.error{
                display: inline !important;
            }
            .sms-asp-send-button{
                background: #0997FF;
                border-radius: 5px !important;
                font-family: 'Circe';
                font-style: normal;
                font-weight: 700;
                font-size: 15px;
                line-height: 100%;
                color: #FFFFFF;;
            }
            .wrapper_sms_code{
                display: flex;
                align-items: flex-end;
                gap: 5px;
                flex-wrap: wrap;
            }
            @media screen and (max-width: 520px) {
                .sms-asp-code-error {
                    margin-top: 15px;
                }
            }

            @media screen and (max-width: 540px) {
                .likezaim_banner {
                    width: 100%;
                }
                .likezaim_banner>img {
                    width: 100%;
                }
            }

            .card-confirm {
                width: 350px;
                padding: 20px;
                background: #ffffff;
                border-radius: 12px;
            }

            .card-confirm .error-block, .card-confirm .request-error-block {
                border-radius: 12px;
                padding: 10px;
                margin-bottom: 20px;
                font-size: 14px;
                text-align: center;
            }

            .card-confirm .error-block {
                border: 1px solid #3EE13E;
                color: #3EE13E;
            }

            .card-confirm .request-error-block {
                border: 1px solid #a43540;
                color: #842029;
            }

            .card-confirm .error-block p {
                margin: 0 !important;
                padding: 0 0 10px 0 !important;
            }

            .card-confirm .card-details-block {
                margin-bottom: 20px;
            }

            .card-confirm .card-details-block label {
                display: block;
                margin-bottom: 5px;
                font-size: 14px;
                color: #333333;
            }

            .card-confirm .card-details-block input {
                padding: 10px;
                font-size: 14px;
                border: 1px solid #cccccc;
                border-radius: 8px;
                background-color: #f9f9f9;
                margin-bottom: 15px;
                box-sizing: border-box;
                cursor: not-allowed;
            }

            .card-confirm .card-details-block .expiration-date {
                max-width: 100px !important;
            }

            .card-confirm .card-details-block input:read-only {
                background-color: #f0f0f0;
            }

            .card-confirm .confirm-button {
                padding: 15px 30px;
                font-size: 16px;
                color: white;
                background-color: #00BB00;
                border: none;
                border-radius: 18px;
                cursor: pointer;
                box-shadow: 0 6px 12px rgba(0, 187, 0, 0.4);
                transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            }

            .card-confirm .confirm-button:hover {
                transform: translateY(-2px); /* Slight lift effect on hover */
                box-shadow: 0 8px 16px rgba(0, 187, 0, 0.6); /* Stronger shadow on hover */
            }

            #bki_question_modal {
                width: 100%;
                max-width: 400px;
                background: #fff;
                padding: 25px 30px;
                border-radius: 18px;
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
                font-family: 'Circe', sans-serif;
                position: relative;
                margin: 0 auto !important;
                left: 0 !important;
                right: 0 !important;
            }

            #bki_question_modal h3 {
                font-size: 20px;
                margin-bottom: 18px;
                color: #2E2E2E;
                text-align: center;
            }

            #bki_question_modal label {
                display: block;
                margin-top: 12px;
                font-size: 15px;
                font-weight: 500;
            }

            #bki_question_modal select,
            #bki_question_modal input[type="file"] {
                width: 100%;
                margin-top: 6px;
                padding: 8px 12px;
                font-size: 14px;
                border: 1px solid #ccc;
                border-radius: 10px;
                background: #f9f9f9;
            }

            #bki_question_modal .button.green {
                /* background-color: #00BB00; */
                color: white;
                font-size: 16px;
                font-weight: 600;
                padding: 12px 28px;
                border: none;
                border-radius: 24px;
                margin-top: 20px;
                /* box-shadow: 0 4px 12px rgba(0, 187, 0, 0.3); */
                transition: background-color 0.3s ease;
            }

            #bki_question_modal .button.green:hover {
                /* background-color: #00a000; */
            }

            .text-center {
                text-align: center;
            }
            .loader {
                margin-top: 15px;
            }
            .spinner {
                border: 4px solid rgba(0, 0, 0, 0.1);
                border-radius: 50%;
                border-top: 4px solid #3498db;
                width: 30px;
                height: 30px;
                animation: spin 1s linear infinite;
                margin: 0 auto;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(-360deg); }
            }

            .rs-payment-button-wrapper {
                overflow: visible !important;
                position: relative;
                z-index: 1;
            }
        </style>

        {if $restricted_mode !== 1}
            <div id="asp_sms" style="display:none;" class="sms-asp-modal">
                <div class="additional-agreement-modal">
                    <div class="additional-agreement-modal-header">
                        <h3>Давайте будем общаться чаще</h3>
                        <a style="display: none;" onclick="$.magnificPopup.close();" class="close-modal" href="javascript:void();"><small>X</small></a>
                    </div>
                    <div class="additional-agreement-modal-input-div">
                        <input type="checkbox" value="1" name="accept_asp_1" required >
                        <span>Принимаю соглашение на <a href="files/asp/Agreement_to_different_Frequency_Interactions.pdf" style="text-decoration: none;font-weight: bolder" target="_blank"><u>иную частоту взаимодействия</u> </a></span>
                    </div>
                    <button class="button medium asp-sign-accept" onclick="asp_app.click_asp_accept('asp_sms')">Принять</button>
                    <div class="wrapper_sms_code" style="display: none;">
                        <div class="button sms-asp-send-button" onclick="!asp_app.validate_accept('asp_sms') || asp_app.send_sms('asp_sms');">Получить код</div>
                        <input type="text" name="sms_asp_1" disabled />
                        <div class="sms-asp-code-error" style="display: none;"></div>
                    </div>
                </div>
            </div>
        {/if}

        <div id="modal_connect" class="mfp-hide modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Карта добавлена</h5>
                </div>

                <div class="modal-body">
                    {if isset($user->order) && $user->order.status|intval === 2}
                        <p>Хотите привязать новую карту к текущей заявке? После привязки заявка будет проверена повторно.</p>
                    {else}
                        <p>Если в настоящий момент у Вас есть заявка на рассмотрении, то для получения займа на новую карту Вам необходимо обратиться на горячую линию
                            <a class="tel" href="tel:88003333073">8 800 333 30 73</a> или в чат на нашем сайте.</p>
                    {/if}
                </div>

                <div class="modal-footer">
                    <button class="button button-inverse" onclick="$.magnificPopup.close()">Нет</button>

                    {if isset($user->order) && $user->order.status|intval === 2}
                        <button class="button attach-new-card" data-order-id="{$last_order.id}">
                            Привязать к заявке
                        </button>
                    {/if}
                </div>
            </div>
        </div>

        {*        <input type="hidden" data-id = "{$user->id}" class="user-id">*}
{*                <div id="asp_sms">*}
{*                    <a style="display: none;" onclick="$.magnificPopup.close();" class="close-modal" href="javascript:void();"><small>пропустить</small></a>*}
{*                    <h5>Может будем общаться чаще?<br/>*}
{*                        Повысить уровень доверия и привлекательности в компании.</h5>*}
{*                    <label class="big left">*}
{*                        <div class="checkbox">*}
{*                            <input type="checkbox" value="1" name="accept_asp" required />*}
{*                            <span></span>*}
{*                        </div> Я ознакомлен и согласен со <a style="margin-left: 5px;" href="/files/docs/asp_zaim.pdf" target="_blank">следующим</a>*}
{*                    </label>*}
{*                    <div class="button medium asp-sign-accept" onclick="asp_app.click_asp_accept();">Подписать</div>*}
{*                    <div class="wrapper_sms_code" style="display: none;">*}
{*                        <div class="button sms-asp-send-button" onclick="!asp_app.validate_accept() || asp_app.send_sms();">Получить код</div>*}
{*                        <input type="text" name="sms_asp" disabled />*}
{*                        <div class="sms-asp-code-error" style="display: none;"></div>*}
{*                    </div>*}
{*                </div>*}

        {if $restricted_mode !== 1}
        <div id="arbitr" style="display:none;" class="arbitration-modal-content sms-asp-modal">
            <div class="arbitration-agreement-modal">
                <div class="arbitration-agreement-modal-header">
                    <a onclick="$.magnificPopup.close();" class="close-modal" href="javascript:void(0)"><small>X</small></a>
                </div>
                <div class="arbitration-agreement-modal-input-div">
                    <input type="checkbox" value="1" style="width: auto;" name="accept_asp_2" required >
                    <input type="hidden" name="order_id" value="{$user->order['id']}">
                    <input type="hidden" name="user_id" value="{$user->id}">
                    <span>Принимаю арбитражное <u><a href="user/docs?action=arbitration_agreement&order_id={$user->order['id']}" style="font-weight: bolder" target="_blank"></u>
                        соглашение,</a></span> <br>
                    <span><u><a href="user/docs?action=asp_agreement&order_id={$user->order['id']}" style="font-weight: bolder" target="_blank"></u>
                        соглашение</a> об использовании аналога собственноручной подписи (АСП)</span>
                    <br>
                    <span><u><a href="user/docs?action=offer_arbitration&order_id={$user->order['id']}" style="font-weight: bolder" target="_blank">
                        и соглашение о подписании молчанием
                    </a></u></span>
                </div>
                <button class="button medium asp-sign-accept" onclick="asp_app.click_asp_accept('arbitr')">Принять</button>
                <div class="wrapper_sms_code" style="display: none;">
                    <div class="button sms-asp-send-button" onclick="!asp_app.validate_accept('arbitr') || asp_app.send_sms('arbitr');">Получить код</div>
                    <input type="text" name="sms_asp_2" disabled />
                    <div class="sms-asp-code-error" style="display: none;"></div>
                </div>
            </div>
        </div>
        {/if}

        {include 'modals/rs_payment_modal.tpl'}

        <div id="bki_question_modal" class="mfp-hide white-popup-modal wrapper_border-green" style="max-width: 500px;">
            <h3>Бюро кредитных историй</h3>
            <p style="font-size: 14px; color: #666;">
                Если вы обнаружили неверную информацию в отчёте БКИ, прикрепите скриншот с примерами и опишите проблему.
            </p>
            <form id="bki_form" action="/ajax/UploadBkiQuestionHandler.php" method="POST" enctype="multipart/form-data" onsubmit="return false;">
                <input type="hidden" name="order_id" value="{$last_order.id}">
                <input type="hidden" name="user_id" value="{$user->id}">
                <div style="margin-bottom: 15px;">
                    <label>Прикрепите скриншоты:</label>
                    <input
                            type="file"
                            name="bki_files[]"
                            id="bki_files_input"
                            multiple
                            accept="image/png,image/jpeg,image/jpg"
                            style="width: 100%;"
                    />
                    <div id="bki_files_preview" style="
                        margin-top: 10px;
                        display: flex;
                        gap: 10px;
                        overflow-x: auto;
                        padding: 5px 0;">
                    </div>
                </div>
                <label for="bki_contract_number">Номер договора:</label>
                <select name="contract_number" id="bki_contract_number">
                    {foreach $all_orders as $orders_data}
                        {foreach $orders_data as $order_data}
                            {if $order_data->balance->zaim_number != null}
                                <option value="{$order_data->balance->zaim_number}">
                                    {$order_data->balance->zaim_number}
                                </option>
                            {/if}
                        {/foreach}
                    {/foreach}
                </select>

                <label style="margin-top: 10px;">Описание проблемы:</label>
                <textarea name="problem_description" rows="4" style="width: 100%; padding: 8px 12px; font-size: 14px; border: 1px solid #ccc; border-radius: 10px; background: #f9f9f9;"></textarea>

                <div id="bki_form_error" style="display: none; font-size: 14px; color: red; margin-top: 10px;"></div>

                <div class="text-center" style="margin-top: 15px;">
                    <button type="submit" class="button green">Отправить</button>
                </div>
            </form>
            <div id="bki_form_success_message" style="display: none; font-size: 14px; color: #333; margin-top: 15px;">
                <p>До 12-ти рабочих дней бюро кредитных историй примет обновление.</p>
                <p>Если в течение данного срока информация в Вашем отчете не обновится, просим сообщить нам об этом.</p>

                <div style="text-align: center; margin-top: 20px;">
                    <button onclick="$.magnificPopup.close();" class="button">Закрыть</button>
                </div>
            </div>
        </div>

        <script type="text/javascript">
            $bannerButton = $('.banner .banner-button');

            if ($bannerButton.length) {
                $bannerButton.on('click', function(e) {
                    var $self = $(this);

                    if ($self.hasClass('get-access')) {
                        $('.full_payment_button[data-order_id="'+$self.attr('data-order_id')+'"]').click();
                    } else {
                        var banner = e.currentTarget.closest('.banner');
                        var copyText = banner.querySelector('.promocode');

                        navigator.clipboard.writeText(copyText.innerText);

                        var thumbsUp = banner.querySelector('.thumbs-up');
                        thumbsUp.classList.remove('d-none');
                        var icon = banner.querySelector('.icon');
                        icon.classList.add('d-none');

                        setTimeout(function() {
                            thumbsUp.classList.add('d-none');
                            icon.classList.remove('d-none');
                        }, 2000);
                    }
                });
            }

            if ($('#full_payment_amount_done').val() === 'true') {
                var orderId = {$userGift->order_id|default:0}
                $.ajax({
                    url: 'ajax/generate_fd_key.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        user_id: '{$user->id}',
                        order_id: orderId,
                        full_payment_amount_done: true
                    },
                    beforeSend: function() {
                        $("body").addClass('is_loading');
                    },
                    success: function(response) {
                        $("body").addClass('is_loading');
                        if (response.success) {
                            window.open(response.login_url, '_blank');
                            location.reload();
                        } else {
                            alert(response.message || 'Не удалось сгенерировать ключ');
                        }
                    },
                    error: function() {
                        alert('Ошибка при запросе на сервер');
                    },
                    complete: function() {
                        $("body").removeClass('is_loading');
                    }
                });
            }

            $(document).ready(function () {
                let value = $('#show-modal-asp').val();
                let admin = $('#input-is-admin').val();
                let looker = $('#input-is-looker').val();
                if (value && value != 0 && looker == 0 && admin == 0) {
                    $('#asp_sms').css('display', 'block');
                    $.magnificPopup.open({
                        items: {
                            src: '#asp_sms'
                        },
                        type: 'inline',
                        showCloseBtn: false,
                        modal: true,
                    });
                }

                asp_app.init_mask();
                asp_app.init_skip_button_timer();
                $('.sms-asp-modal a, .sms-asp-modal .button').on('click', function () {
                    asp_app.skip_button_second = 10;
                });
            });

            $('.close-modal').click(function () {
                $('#show-modal-asp').val(0);
            });

            const default_sms_delay_seconds = 30;
            const ASP_SMS_ERROR = 'Вы ввели неверный код.';
            const user_phone = '{$user->phone_mobile}';

            // Обработка модальных окон с АСП подписью
            let asp_app = {
                timer_second: 0,
                asp_timer: null,
                skip_button_second: 30,
                skip_button_timer: null,
                skip_button_elements: $('#asp_sms .close-modal, #arbitr .close-modal'),
                accept_field: null,
                code_field: null,
                order_id: null,

                init_skip_button_timer: function () {
                    this.skip_button_timer = setInterval(function () {
                        if (this.skip_button_second === 0) {
                            this.skip_button_elements.show();
                            clearInterval(this.skip_button_timer);
                        }
                        this.skip_button_second--;
                    }.bind(this), 1000);
                },

                validate_accept: function (modalId) {
                    this.accept_field = $('#' + modalId + ' [name^="accept_asp"]');
                    let accept_val = this.accept_field.is(':checked');
                    let error_msg_field = $('#' + modalId + ' .additional-agreement-modal-input-div>span, #' + modalId + ' .arbitration-agreement-modal-input-div>span');

                    $('#' + modalId + ' .text-error').remove();
                    error_msg_field.removeClass('error');
                    if (accept_val) {
                        $('#' + modalId + ' label').removeClass('error');
                    } else {
                        error_msg_field.addClass('error').after('<p class="text-error">Для продолжения необходимо Ваше согласие</p>');
                    }

                    return !error_msg_field.hasClass('error');
                },

                click_asp_accept: function (modalId) {
                    if (this.validate_accept(modalId)) {
                        $('#' + modalId + ' .asp-sign-accept').hide();
                        $('#' + modalId + ' .wrapper_sms_code').show();
                    }
                },

                // выключение таймера и снятие блокировок
                delete_timer: function (modalId) {
                    clearInterval(this.asp_timer);
                    this.asp_timer = null; // сбрасываем таймер
                    $('#' + modalId + ' .sms-asp-send-button').removeClass('disabled').text('Отправить ещё раз');
                    $('#' + modalId + ' [name^="sms_asp"]').val('').prop('disabled', true);
                    $('#' + modalId + ' .sms-asp-code-error').hide();
                },

                // функция таймера отправки смс
                init_timer: function (modalId, seconds) {
                    this.timer_second = seconds;
                    $('#' + modalId + ' .sms-asp-send-button').addClass('disabled');
                    $('#' + modalId + ' [name^="sms_asp"]').prop('disabled', false);

                    this.asp_timer = setInterval(function () {
                        if (this.timer_second === 0) {
                            this.delete_timer(modalId);
                        } else {
                            $('#' + modalId + ' .sms-asp-send-button').text(this.timer_second);
                        }
                        this.timer_second--;
                    }.bind(this), 1000);
                },

                // отправка СМС
                send_sms: function (modalId) {
                    // Если таймер уже запущен, не запускаем новый
                    if (this.asp_timer) {
                        return;
                    }

                    this.init_timer(modalId, default_sms_delay_seconds);
                    $.ajax({
                        url: 'ajax/sms.php',
                        data: {
                            phone: user_phone,
                            action: 'send',
                            flag: 'АСП',
                        },
                        success: function (resp) {
                            if (resp.error) {
                                if (resp.error === 'sms_time')
                                    this.init_timer(modalId, resp.time_left);
                                else
                                    console.log(resp);
                            } else {
                                if (resp.mode === 'developer') {
                                    $('#' + modalId + ' [name^="sms_asp"]').prop('disabled', false).val(resp.developer_code);
                                    this.validate_sms_code(modalId);
                                } else {
                                    console.log('response: ', resp);
                                }
                            }
                        }.bind(this)
                    });
                },

                // маска ввода для СМС
                init_mask: function () {
                    $('[name^="sms_asp"]').inputmask({
                        mask: "9999",
                        oncomplete: function () {
                            let modalId = $(this).closest('.sms-asp-modal').attr('id');
                            asp_app.validate_sms_code(modalId);
                        }
                    });
                },

                // проверка СМС
                validate_sms_code: function (modalId) {
                    let smsCode = $('#' + modalId + ' [name^="sms_asp"]').val();

                    let data = {
                        phone: user_phone,
                        action: 'asp_sms',
                        code: smsCode,
                    }

                    if (modalId === 'arbitr') {
                        data.action = 'check_arbitration_agreement'
                        data.order_id = $('#' + modalId + ' [name="order_id"]').val()
                    }

                    $.ajax({
                        url: 'ajax/sms.php',
                        data,
                        success: function (resp) {
                            if (resp.validate_sms !== 0) {
                                $.magnificPopup.close();
                            } else {
                                $('#' + modalId + ' .sms-asp-code-error').show().text(resp.soap_fault ? resp.error : ASP_SMS_ERROR);
                            }
                        }
                    });
                }
            }

            $(document).on('click', '#openBkiModal', function () {
                $.magnificPopup.open({
                    items: {
                        src: '#bki_question_modal'
                    },
                    type: 'inline'
                });
            });

            let bkiAcceptedFiles = [];

            $('#bki_files_input').on('change', function () {
                const preview = $('#bki_files_preview');
                const inputFiles = this.files;

                $.each(inputFiles, function (i, file) {
                    if (!file.type.match('image.*')) return;

                    if (bkiAcceptedFiles.find(f => f.name === file.name && f.size === file.size)) return;

                    bkiAcceptedFiles.push(file);

                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const wrapper = $('<div>').css({
                            'position': 'relative',
                            'flex': '0 0 auto',
                            'text-align': 'center'
                        });

                        const removeBtn = $('<div>x</div>').css({
                            'position': 'absolute',
                            'top': '-8px',
                            'right': '-8px',
                            'cursor': 'pointer',
                            'font-size': '14px',
                            'color': '#a00',
                            'background': '#fff',
                            'border': '1px solid #ccc',
                            'border-radius': '50%',
                            'width': '20px',
                            'height': '20px',
                            'line-height': '18px',
                            'text-align': 'center'
                        });

                        removeBtn.on('click', function () {
                            bkiAcceptedFiles = bkiAcceptedFiles.filter(f => !(f.name === file.name && f.size === file.size));
                            wrapper.remove();
                        });

                        const img = $('<img>')
                            .attr('src', e.target.result)
                            .css({
                                'max-width': '100px',
                                'max-height': '100px',
                                'border': '1px solid #ccc',
                                'padding': '2px',
                                'background': '#f9f9f9'
                            });

                        const label = $('<div>').text(file.name).css({
                            'font-size': '12px',
                            'margin-top': '5px',
                            'max-width': '100px',
                            'overflow': 'hidden',
                            'text-overflow': 'ellipsis',
                            'white-space': 'nowrap'
                        });

                        wrapper.append(removeBtn).append(img).append(label);
                        preview.append(wrapper);
                    };

                    reader.readAsDataURL(file);
                });

                $(this).val('');
            });

            $('#bki_form').on('submit', function (e) {
                e.preventDefault();

                // Custom validation before sending AJAX
                const description = $('textarea[name="problem_description"]').val().trim();
                const contractNumber = $('select[name="contract_number"]').val();
                const fileInput = document.getElementById('bki_files_input');
                const hasFiles = bkiAcceptedFiles.length > 0;

                $('#bki_form_error').hide();

                if (!hasFiles) {
                    $('#bki_form_error').text('Пожалуйста, прикрепите хотя бы один файл.').show();
                    return;
                }

                if (!contractNumber) {
                    $('#bki_form_error').text('Пожалуйста, выберите номер договора.').show();
                    return;
                }

                if (!description) {
                    $('#bki_form_error').text('Пожалуйста, заполните описание проблемы.').show();
                    return;
                }

                // Prepare formData for AJAX
                const formData = new FormData();
                formData.append('contract_number', $('select[name="contract_number"]').val());
                formData.append('problem_description', description);
                formData.append('order_id', $('input[name="order_id"]').val());
                formData.append('user_id', $('input[name="user_id"]').val());

                bkiAcceptedFiles.forEach(file => {
                    formData.append('bki_files[]', file);
                });

                $.ajax({
                    url: 'ajax/UploadBkiQuestionHandler.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        const $form = $('#bki_form');
                        $('#bki_form_error').hide();
                        $form[0].reset();
                        $('#bki_files_preview').empty();
                        bkiAcceptedFiles = [];
                        $form.hide();
                        $('#bki_form_success_message').show();
                    },
                    error: function (xhr) {
                        let message = '❌ Ошибка при отправке.';

                        try {
                            const res = JSON.parse(xhr.responseText);
                            if (res.error) {
                                message = '❌ ' + res.error;
                            }
                        } catch (e) {}

                        $('#bki_form_error').text(message).show();
                        $('#bki_form_success').hide();
                    }
                });
            });

        </script>
    </div>
    {if !$blocked_adv_sms && !$friend_restricted_mode}
        <div class="footer__blocked_adv_sms blocked_adv_sms">
            <button type="button" class="button btn-sm button-inverse btn-600 btn-fsize-14 btn-line-h-24">
                Отписаться от рекламных смс
            </button>
        </div>
    {/if}

    <div style="padding-top: 15px;" hidden>
        <a href="javascript:void(0);" class="any-faq" id="openBkiModal">Бюро кредитных историй</a>
    </div>

</div>

<input type="hidden" id="show_payment_options_modal" value="{$show_payment_options_modal|default:false}">

<div id="payment-options-info" class="mfp-hide white-popup-modal" style="max-width: 800px; padding: 30px;">
    <div style="position: relative; margin-bottom: 20px;">
        <button onclick="$.magnificPopup.close();"
                style="
        position: absolute;
        top: -15px;
        right: -15px;
        width: 36px;
        height: 36px;
        background: #fff;
        color: #000;
        font-size: 20px;
        font-weight: bold;
        line-height: 36px;
        border-radius: 50%;
        border: 1px solid #ccc;
        box-shadow: 0 2px 5px rgba(0,0,0,0.15);
        cursor: pointer;
        text-align: center;
        padding: 0;
    ">×
        </button>
    </div>

    <h2 style="margin-top: 0;">Мгновенные оплаты</h2>
    <p>
        Оплата через личный кабинет осуществляется мгновенно, что позволяет сразу же погасить текущий займ.
        <br>
        После оплаты в зависимости от условий компании, у вас может появиться возможность оформить новый займ.
    </p>

    <h2>Оплата по реквизитам</h2>
    <p>
        При оплате по банковским реквизитам возможны дополнительные расходы: комиссия вашего банка может достигать до
        10%.
        <br>
        Дата зачисления платежа считается днем поступления средств на счет Займодавца, что может занимать до 3 рабочих
        дней в зависимости от банка.
        <br>
        Учтите, что в этот период начисляются проценты за каждый день задержки.
        <a href="/user/faq?action=user_section&section_id=12&q=92" style="color: #007bff;">Актуальные реквизиты</a>
    </p>
    <p style="color: red; font-style: italic;">
        *Обратите внимание: задержки с банковским переводом и несоблюдение сроков оплаты могут быть зафиксированы в базе
        данных БКИ и негативно повлиять на вашу кредитную историю.
    </p>

    <h2>Оплата через Почту России</h2>
    <p>
        Этот способ занимает до 5 рабочих дней, при этом комиссия отсутствует.
        <br>
        Дата зачисления платежа считается днем поступления средств на счет Займодавца, что может занимать до 5 дней в
        зависимости от банка.
        <br>
        Учтите, что в этот период начисляются проценты за каждый день задержки.
    </p>
    <p style="color: red; font-style: italic;">
        *Обратите внимание: задержки с почтовым переводом и несоблюдение сроков оплаты могут быть зафиксированы в базе
        данных БКИ и негативно повлиять на вашу кредитную историю.
    </p>
</div>

<div id="ajax_prolongation__content"></div>
<script src="design/{$settings->theme}/js/files_data.app.js?v=1.73" type="text/javascript"></script>
<script>

    </script>
    <script src="design/{$settings->theme}/js/add_card.js?v=1.014" type="text/javascript"></script>

{capture_array key="footer_page_scripts"}
{literal}
    <script type="text/javascript">
        $(".blocked_adv_sms button").on('click', function () {
            const result = confirm('Вы уверены, что хотите отписаться от рекламных смс?');
            if (result) {
                $.ajax({
                    url: 'ajax/user.php?action=blocked_adv_sms',
                    success: function () {
                        $("#blocked_adv_sms").remove();
                        window.location.reload();
                    }
                });
            }
        });

        $(".partner-href").on('click', function (event) {
            event.preventDefault();
            let url = $(this).attr('href');
            $.ajax({
                url: 'ajax/user.php?action=add_statistic_partner_href',
                success: function () {
                    window.location = url;
                }
            });
        });

        $('.modal-remove_card').on('click', function(event) {
            event.preventDefault();
            $('.action-remove_card').attr('data-button-card-id',$(this).attr('data-button-card-id'))
            $.magnificPopup.open({
                items: {
                    src: '#removeCardModal',
                    type: 'inline'
                }
            });
        });

        $('.modal-choose_card').on('click', function(event) {
            event.preventDefault();
            $('.action-choose_card').attr('data-button-card-id',$(this).attr('data-button-card-id'))
            $('.action-choose_card').attr('data-button-order-id',$(this).attr('data-button-order-id'))
            $('.action-choose_card').attr('data-button-action', $(this).attr('data-button-action'))

            $.magnificPopup.open({
                items: {
                    src: '#chooseCardModal',
                    type: 'inline'
                }
            });
        });

        // Удаление карты
        remove_card = function (card_id) {
            $.ajax({
                url: 'ajax/remove_card.php',
                data: { card_id: card_id, },
                method: 'POST',
                success: function (resp) {
                    if (resp.result && resp.result == 'success') {
                        $.magnificPopup.close()
                        document.querySelector('[data-card-id="' + card_id + '"]').remove(); // Удаляем карту
                        $('li[data-card-id-deleting="'+card_id+'"]').remove();
                        $('.card-list-for-order li:first input[type="radio"]').prop('checked', true);
                        if(document.querySelectorAll('[data-card-id]').length < 2) { // Если одна карта, убираем у неё кнопку удаления
                            $('.modal-remove_card').remove();
                        }else{
                            enableButtons( $('.modal-remove_card') ); // Иначе включаем все кнопки
                        }
                        alert("Карта успешно удалена из ЛК");
                        if (typeof window.syncUserSelectedCard === 'function') {
                            window.syncUserSelectedCard();
                        }
                    } else {
                        if (resp.error == 'card_blocked') {
                            alert("Удаление карты заблокировано. В настоящее время она используется для совершения для операций");
                            enableButtons( $('.modal-remove_card') ); // Иначе включаем все кнопки
                        }
                        if (resp.error == 'first_card_blocked') {
                            alert("Удаление единственной карты невозможно");
                            enableButtons( $('.modal-remove_card') ); // Иначе включаем все кнопки
                        }
                    }
                }
            });
        };

        // Выбор карты
        choose_card = function (card_id, order_id, action = 'choose_card') {
            let data = {
              order_id: order_id,
            }

            if (action === 'choose_sbp') {
              data.sbp_account_id = card_id
              data.action = 'choose_sbp'
            } else {
              data.card_id = card_id
              data.action = 'choose_card'
            }

            $.ajax({
                url: '/ajax/choose_card.php',
                data: data,
                method: 'POST',
                success: function (resp) {
                    if (resp && resp.error) {
                        alert(resp.error ? resp.error : "Не удалось выбрать карту");
                    }

                    location.reload();
                }
            });
        };

        $('.attach-new-card').on('click', function() {
            const cardId = localStorage.getItem('newCardId');
            const orderId = $(this).data('order-id');

            if (!cardId || !orderId) {
                console.error('Не удалось привязать карту: отсутствует cardId или orderId');
                return;
            }

            choose_card(cardId, orderId);
            $.magnificPopup.close();
        });

        $('.action-remove_card').on('click', function(event){
            event.preventDefault();
            let button = $(event.target),
                card_id = button.attr('data-button-card-id');

            disableButtons( $('.action-remove_card') );
            remove_card(card_id, button);
        });

        $('.action-choose_card').on('click', function (event) {
            event.preventDefault();
            const button = $(event.target);
            const card_id = button.attr('data-button-card-id');
            const order_id = button.attr('data-button-order-id');
            const action = button.attr('data-button-action');

            disableButtons($('.action-choose_card'));
            choose_card(card_id, order_id, action);
        });

        function disableButtons( elems ){
            elems.attr('disabled', 'disabled');
            elems.css('cursor', 'not-allowed');
        }

        function enableButtons( elems ){
            elems.removeAttr('disabled');
            elems.css('cursor', 'pointer');
        }

        let nowHour = new Date().getHours();
        let today = new Date().getDay();

        var isOrganic = ['Boostra', '', 'direct1', 'direct_seo', 'direct', 'direct3'].includes(userUtmSource.trim());
        let isBetween8and19 = (nowHour >= 8 && nowHour <= 18);

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

        $(".get_prolongation_modal").on('click', function () {

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

            $("#ajax_prolongation__content").load('/ajax/loan.php?action=get_prolongation', {
                order_id,
                number,
                user_id,
                tv_medical_tariff_id
            }, function (response, status, xhr) {
                if (status === "error") {
                    alert('Произошла ошибка сервера подробности в консоли');
                    console.error('error load text: ' + xhr.status + " " + xhr.statusText);
                    return;
                }

                $("body").removeClass('is_loading');
                initialize();

                let shouldShow = shouldShowElements(userUtmSource, nowHour, today);
                console.log('tv_medical_tariff_id:', tv_medical_tariff_id);

                toggleVisibility('checkboxBlock', false);

                if (overdue > 8) {
                    toggleVisibility('checkboxBlock', true);
                    toggleVisibility('vitaMedContainer', true);
                    toggleVisibility('conciergeServiceContainer', false);
                } else {
                    toggleVisibility('vitaMedContainer', false);
                    toggleVisibility('conciergeServiceContainer', false);
                }

                $.ajax({
                    url: '/ajax/loan.php',
                    method: 'GET',
                    data: { action: 'prolongation_amount' },
                    dataType: 'json',
                    success: function (data) {
                        if (data.success) {
                            $('#prolongation_amount').text(data.amount);
                            $("#prolongation_confirm_form [name='amount']").val(data.amount);
                            $('.payment_button[data-number="' + number + '"] .payment_button__amount, #amount_text').text(data.amount);
                        }
                    },
                    complete: function () {
                        if ($('#collectionPromo').length <= 0) {
                            setTimeout(() => {
                                $(".js-prolongation-open-modal[data-order_id='" + order_id + "']").trigger('click');
                            }, 300);
                        }
                    }
                });
            });

        });
        $(document).ready(initialize);

        if ((new URLSearchParams(window.location.search)).get("is_prolongation") === "1") {
            $(".get_prolongation_modal:first").trigger('click');
        }

        $(document).ready(function () {
            const urlParams = new URLSearchParams(window.location.search);
            const isPaymentOptions = urlParams.has('payment-options');

            if (isPaymentOptions) {
                $.magnificPopup.open({
                    items: {
                        src: '#payment-options-info'
                    },
                    type: 'inline'
                });
            }
        });


        // Логируем открытие страницы только для тех юзеров, кто видит колесо
        if (document.querySelectorAll('.wheel-open').length) {
            $.post('/ajax/promo_logger.php', {
                action: 'page_open',
                user_id: +(document.querySelector('input[name="user_id"]')?.value ?? document.querySelector('.payment_amount')?.dataset.user_id ?? 0)
            });
        }

        $(document)
            .off('click', '#openPromo')
            .on('click', '#openPromo', function () {
                $.magnificPopup.open({
                    items: { src: '#promo_modal' },
                    type: 'inline',
                    modal: true,
                    closeBtnInside: false
                });
                $.post('/ajax/promo_logger.php', {
                    action: 'open_promo_modal',
                    user_id: +(document.querySelector('input[name="user_id"]')?.value ?? document.querySelector('.payment_amount')?.dataset.user_id ?? 0)
                });
            })
            .off('click', '#closePromo')
            .on('click', '#closePromo', function () {
                $.magnificPopup.close();
            });

    </script>
{/literal}
{/capture_array}

{assign var=hasIL value=false}

{foreach from=$all_orders->orders|default:[] item=order}
    {if ($order->balance->loan_type|default:''|lower) == 'il'}
        {assign var=hasIL value=true}
        {break}
    {/if}
{/foreach}

{if $hasIL}
    <br>
    <span> ⃰ - сумма с учетом комиссий и дополнительного ПО</span>
{/if}
<div>
    <div style="display: none">
        <div id="accepted_first_order_divide"  class="white-popup-modal wrapper_border-green mfp-hide">
            <div>
                <h4>
                    Не забудьте вернуться завтра за второй частью займа!
                </h4>
                <button class="green button" onclick="$.magnificPopup.close()">Хорошо</button>
            </div>
        </div>
    </div>
    <style>
        #accepted_first_order_divide {
            margin: auto;
        }

        #accepted_first_order_divide > div:first-child {
            display: flex;
            flex-flow: column;
            gap: 40px;
            padding: 30px;
            margin: auto;
            text-align: center;
        }

        @media screen and (min-width: 769px) {
            #accepted_first_order_divide > div:first-child {
                gap: 120px;
            }
        }
        .grace-main-div{
            width:500px;
            border-radius:15px;
            display:inline-block !important;
            padding: 20px;
            box-shadow: 0 1px 15px rgba(0,0,0,0.3), 0 1px 2px rgba(0,0,0,0.24);
            margin: 20px 0;
        }
        .grace-container-div>h1 {
            color: #2E2E2E;
            font-family: Circe;
            font-size: 20px;
            font-style: normal;
            font-weight: 700;
            line-height: 120%;
            margin: 10px 0 22px;
        }
        .grace-container-div>h4{
            color: #2E2E2E;
            font-family: Circe;
            font-size: 15px;
            font-style: normal;
            font-weight: 400;
            line-height: 120%;
            margin-top: 10px;
        }
        .new-price{
            color: #FF7F09;
            font-family: Circe;
            font-size: 18px;
            font-style: normal;
            font-weight: 700;
            line-height: 120%;
        }

        .old-price{
            color: #CECECE;
            font-family: Circe;
            font-size: 18px;
            font-style: normal;
            font-weight: 400;
            line-height: 120%;
            text-decoration-line: line-through;
        }
        .pay-grace {
            border-radius: 5px;
            border: 2px solid #0997FF !important;
            background: #0997FF !important;
            padding: 10px 30px;
            margin-top: 30px;
            margin-bottom:10px;
            color: #FFF !important;
            font-size: 15px;
            font-style: normal;
            font-weight: 700;
            line-height: 100%;
        }
        .pay-grace:hover {
            background: #0997FF !important;
        }
        .get-reference{
            border-radius: 5px;
            border: 2px solid #0997FF;
            padding: 10px 30px;
            background: white;
            color: #0997FF;
            font-size: 15px;
            font-style: normal;
            font-weight: 700;
            line-height: 100%;
        }
        .get-reference{
            background: white !important;
        }
        .form-pay {
            margin: 0 !important;
        }
        #blocked_adv_sms {
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</div>

<!-- Акция коллекшна -->
{if $restricted_mode === 1 && $collectionPromo === true}
    <link href="design/{$settings->theme}/css/collectionPromo.css" rel="stylesheet" type="text/css" >

    <div id="collectionPromo">
        <div class="modal_title">
            {$collectionPromoTitle}
            <a style="display:none;" onclick="$.magnificPopup.close();" class="close-modal" href="javascript:void();"><small>X</small></a>
            <div style="clear: both"></div>
        </div>

        <div class="modal-body">
            <h3 class="no_bold">
                Получите персональную скидку
                {if $user->balance->discount_date}
                    до {date('d.m.Y', strtotime($user->balance->discount_date) - 86400)}
                {/if}
            </h3>
            <br>

            <h3 class="no_bold">{$collectionPromoSubTitle}</h3>
            <h3 class="no_bold old_amount"><del>{$collectionPromoOldAmount} &#8381;</del></h3>
            <h3 class="new_amount">{$collectionPromoNewAmount} &#8381;</h3>
            <br>
            <h3 class="no_bold">{$collectionPromoMessage}</h3>
            <br>
            <button id="collection_promo_pay_button" class="restrict_button" data-user="{$user->id}"
                    data-event="4" type="button">Оплатить
            </button>

            {if $collectionPromoDoc}
                <br><br>
                <a href="https://boostra.ru/files/docs/akvarius/{$collectionPromoDoc}.pdf" target="_blank">Правила акции</a>
            {/if}
        </div>
    </div>

    {foreach $additional_scripts as $script}
        <script src="design/{$settings->theme}/js/promo/additional/{$script}.js"></script>
    {/foreach}
    <script src="design/{$settings->theme}/js/promo/collectionPromo.js"></script>
{/if}
<script src="design/{$settings->theme}/js/rs_payment.js?v=1.0"></script>
