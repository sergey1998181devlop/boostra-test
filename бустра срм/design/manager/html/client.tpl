{$meta_title="`$client->lastname` `$client->firstname` `$client->patronymic`" scope=parent}

{assign 'stopfactorsImportant' [
"Негатив по ФССП",
"Высокая доля просрочек в КИ за последние 2 года",
"Глубокая просрочка в КИ за последние 2 года",
"Глубокая просрочка по последним займам в КИ",
"Негативы последних займов в КИ",
"Высокая просрочек в КИ за последние 2 года",
"Высокая вероятность дефолта по КИ",
"Высокий риск банкротства в течении 2х месяцев",
"Банкротство в КИ",
"Подозрение на фрод",
"Черный список скористы",
"Несовпадение ФИО с данными официальных источников",
"Реквизиты паспорта не уникальны",
"Большое количество разных телефонов в заявках на текущий паспорт",
"Регион проживания не совпадает с регионом телефона и регистрации",
"Регион повышенного риска (Белгородская обл)",
"Регион военных действий",
"Регион вблизи военных действий",
"Беженцы с территорий боевых действий",
"Сомнительная серия паспорта",
"Регион повышенного риска (Дальний Восток)",
"Высокая долговая нагрузка по КИ",
"Дополнительная оценка первого займа",
"Высокая доля просрочек в КИ за последние 2 года"
]}
{assign var="clientPhone" value=$order->phone_mobile|default:$client->phone_mobile}
{capture name='page_scripts'}

    <script src="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/fancybox3/dist/jquery.fancybox.js"></script>

    <script src="design/manager/assets/plugins/inputmask/dist/min/jquery.inputmask.bundle.min.js"></script>
    <script src="design/manager/assets/plugins/moment/moment.js"></script>
    <script src="design/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/order.js?v=2.18"></script>
    <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/check_images.js"></script>
    <script type="module" src="design/{$settings->theme|escape}/js/apps/client.js?v=1.5"></script>
    <script src="design/{$settings->theme|escape}/js/apps/terrorist-matches.js?v=1"></script>
    <script src="design/{$settings->theme|escape}/js/apps/movements.app.js"></script>
    <script type="module" src="design/{$settings->theme|escape}/js/apps/collection.js?v=1.021"></script>
    <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/promocodes.js?v=1.2"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $('input[name=passport_serial]').inputmask("99 99 999999");
        $('input[name=subdivision_code]').inputmask("999-999");

        window.app = window.app || {literal}{}{/literal};
        window.app.client_config = {
            client_id: {$client->id}
        };
    </script>
    
{/capture}

{capture name='page_styles'}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet" />
    <link href="design/{$settings->theme|escape}/assets/plugins/fancybox3/dist/jquery.fancybox.css" rel="stylesheet" />
    <link href="design/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">
    <style>
        .text-black {
            padding: 5px;
            color: #ff0000;
            background: black;
        }

        td.transcript-cell {
            max-width: 400px;
            word-break: break-word;
            white-space: normal;
        }
        .transcript-wrapper {
            max-width: 100%;
            overflow-x: hidden;
        }
        .transcript-preview {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            max-height: 3.6em;
            white-space: nowrap;
            word-break: break-word;
        }
        .transcript-preview.collapsed {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: normal;
            word-break: break-word;
            max-height: 4.5em;
        }
        .transcript-preview.expanded {
            white-space: normal;
            max-height: none;
            overflow: visible;
        }

        .ticket-status-highlight {
            display: flex;
            flex-direction: row;
            background: rgba(255, 28, 28, 0.23);
            border-radius: 4px;
            padding: 4px 6px;
            box-shadow: 0 0 0 1px rgba(255, 28, 28, 0.74);
        }

        .ticket-buttons__btn {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @media (max-width: 964px) {
            .ticket-status-highlight {
                flex-direction: column;
            }
        }

        @media (max-width: 764px) {
            .ticket-status-highlight {
                flex-direction: row;
            }
        }

        .block-calls-section .form-control {
            max-width: 200px;
        }

        .sidebar-buttons .block-calls-section .form-control {
            max-width: 260px;
            width: 100%;
            margin-left: auto;
            margin-right: auto;
        }

        .sidebar-buttons .form-control,
        .sidebar-buttons .btn {
            max-width: 260px;
            width: 100%;
            margin-left: auto;
            margin-right: 0;
        }

        .sidebar-buttons .sidebar-note {
            max-width: 260px;
            width: 100%;
            margin-left: auto;
            margin-right: 0;
        }

        .client-tickets-scrollbar-top {
            overflow-x: auto;
            overflow-y: hidden;
        }
        .client-tickets-scrollbar-top .dummy {
            height: 10px;
        }
        .client-tickets-scrollbar-top::-webkit-scrollbar,
        #client-tickets-table-container::-webkit-scrollbar {
            height: 10px;
        }
        .client-tickets-scrollbar-top::-webkit-scrollbar-track,
        #client-tickets-table-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        .client-tickets-scrollbar-top::-webkit-scrollbar-thumb,
        #client-tickets-table-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        .client-tickets-scrollbar-top::-webkit-scrollbar-thumb:hover,
        #client-tickets-table-container::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        .client-tickets-scrollbar-top,
        #client-tickets-table-container {
            scrollbar-width: thin;
            scrollbar-color: #888 #f1f1f1;
        }
        #client-tickets-filter-row th {
            padding: 4px;
        }
        #client-tickets-filter-row .form-control {
            min-width: 100px;
            font-size: 12px;
            padding: 2px 6px;
            height: 30px;
        }
        #client-tickets-filter-row .input-group {
            flex-wrap: nowrap;
            min-width: 180px;
        }
        #client-tickets-filter-row .input-group-text {
            padding: 2px 6px;
            font-size: 12px;
        }
    </style>
{/capture}

<div class="page-wrapper">
    <!-- ============================================================== -->
    <!-- Container fluid  -->
    <!-- ============================================================== -->
    <div class="container-fluid">

        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0"><i class="mdi mdi-account-card-details"></i> {$client->lastname|escape} {$client->firstname|escape} {$client->patronymic|escape}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="clients">Клиенты</a></li>
                    <li class="breadcrumb-item active">Карточка клиента</li>
                </ol>
            </div>
            <div class="col-md-3 col-6 align-self-center">
                {if $looker_link && $manager->role != 'verificator_minus'}
                    <a href="{$looker_link}" class="btn btn-info" target="_blank">
                        <i class="fas fa-user"></i><span>Смотреть ЛК</span>
                    </a>
                {/if}

                <button class="btn btn-primary" data-target="#generatePromoCodeModal" data-toggle="modal">Выдать промокод</button>
            </div>
            <div class="col-md-3 col-6 align-self-center">
                {if $manager->role != 'verificator_minus'}
                    <h3 class="text-white"><i>Клиент с {$client->site_id}</i></h3>
                {/if}
            </div>
        </div>

        <ul class="mt-2 nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#info" role="tab" aria-selected="true">
                    <span class="hidden-sm-up"><i class="ti-home"></i></span>
                    <span class="hidden-xs-down">Персональная информация</span>
                </a>
            </li>
            {if $manager->role != 'verificator_minus'}
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#scorings" role="tab" aria-selected="true">
                        <span class="hidden-sm-up"><i class="ti-bookmark-alt"></i></span>
                        <span class="hidden-xs-down">Скоринги</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#history" role="tab" aria-selected="true">
                        <span class="hidden-sm-up"><i class="ti-bookmark-alt"></i></span>
                        <span class="hidden-xs-down">Кредитная история</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#comments" role="tab" aria-selected="true">
                        <span class="hidden-sm-up"><i class=" ti-comments "></i></span>
                        <span class="hidden-xs-down">Комментарии</span>
                    </a>
                </li>
            {/if}
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#calls" role="tab" aria-selected="true">
                    <span class="hidden-sm-up"><i class="ti-mobile "></i></span>
                    <span class="hidden-xs-down">История звонков</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#exitpools" role="tab" aria-selected="true">
                    <span class="hidden-sm-up"><i class="ti-notepad "></i></span>
                    <span class="hidden-xs-down">Опросы</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link js-event-add-click" data-toggle="tab" href="#sms_list" role="tab" aria-selected="true">
                    <span class="hidden-sm-up"><i class="ti-chat"></i></span>
                    <span class="hidden-xs-down">СМС клиенту</span>
                </a>
                <div style="clear: both;"></div>
            </li>
            <li class="nav-item">
                <a class="nav-link js-event-add-click" data-toggle="tab" href="#duplicates" role="tab" aria-selected="true">
                    <span class="hidden-sm-up"><i class="ti-bookmark-alt"></i></span>
                    <span class="hidden-xs-down">Совпадения</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tickets" role="tab" aria-selected="false">
                    <span class="hidden-sm-up"><i class="ti-list"></i></span>
                    <span class="hidden-xs-down">Тикеты</span>
                </a>
            </li>
        </ul>

        <div class="tab-content ">
            <div id="info" class="tab-pane active" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        <!-- Column -->
                        <div class="card">

                            {if !empty($user_data) && !empty($user_data['gray_list'])}
                                <h4 class="text-danger" style="padding-left: 20px; height: 0; line-height: 30px;">Подозрение в мошенничестве</h4>
                            {/if}

                            {if $is_short_flow}
                                <h4 class="text-success" style="padding-left: 20px; height: 0; line-height: 30px;">
                                    Короткая регистрация
                                    {if !$is_short_flow_data_confirm}<span class="text-danger"> - Клиент не уверен в распознанных данных</span>{/if}
                                </h4>
                            {/if}

                            {if $is_samara_office && $manager->role != 'verificator_minus'}
                                <h4 class="text-danger" style="padding-left: 20px; height: 0; line-height: 30px;">Заявка из офиса (Потенциально ЦБ)</h4>
                            {/if}

                            {if $inn_not_found}
                                <h4 class="text-danger" style="padding-left: 20px; height: 0; line-height: 30px;">ИНН не найден - необходимо скорректировать данные и перезапустить акси</h4>
                            {/if}

                            <div class="card-body">
                                <div class="form-body">

                                    <!-- Персональные данные -->

                                    <div class="row align-items-start mb-2">
                                        <div class="col-md-8">
                                            <h3 class="card-title mb-0">
                                                <a href="javascript:void(0);" class="js-edit-form"
                                                   data-form="personal_data_form">
                                                    <span>Персональная информация</span>
                                                    <div class="row mt-2">
                                                        {if $is_esia}
                                                            <div class="col-auto">
                                                                <img src="/design/{$settings->theme}/assets/img/esia_logo.png"
                                                                     height="42"/>
                                                            </div>
                                                        {/if}
                                                        {if $is_tid}
                                                            <div class="col-auto">
                                                                <img src="/design/{$settings->theme}/assets/img/t_bank_logo.svg"
                                                                     height="42"/>
                                                            </div>
                                                        {/if}
                                                    </div>
                                                </a>
                                            </h3>
                                        </div>
                                        <div class="col-md-4 text-right">
                                            {if $active_ticket}
                                                {include file="html_blocks/client_active_ticket.tpl" active_ticket=$active_ticket}
                                            {/if}
                                            {if $scorings_efrsb->success === 0}
                                                <small style="color: red">
                                                    Банкротство: {$scorings_efrsb->string_result}
                                                    с {$scorings_efrsb->bankruptcy_date}
                                                </small>
                                                <br>
                                            {/if}
                                            <small>{$client->UID}</small>
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="row">
                                        <div class="col-md-8">
                                            <form action="{url}" class="mb-3 js-order-item-form"
                                                  id="personal_data_form">
                                                <input type="hidden" name="action" value="personal"/>
                                                <input type="hidden" name="user_id" value="{$client->id}"/>
                                                <input type="hidden" data-manager="{$manager->id}">

                                                <div class="row view-block {if $personal_error}hide{/if}">
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <label class="control-label col-md-4">ФИО:</label>
                                                            <div class="col-md-8">
                                                                <p class="form-control-static">{$client->lastname|escape} {$client->firstname|escape} {$client->patronymic|escape}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <label class="control-label col-md-4">Номер
                                                                телефона:</label>
                                                            <div class="col-md-8">
                                                                <p class="form-control-static">{$client->phone_mobile|escape}
                                                                    <button class="btn waves-effect waves-light btn-xs btn-info js-mango-call"
                                                                            data-phone="{$client->phone_mobile}"
                                                                            data-user="{$client->id}"
                                                                            title="Выполнить звонок"><i
                                                                                class="fas fa-phone-square"></i>
                                                                    </button>
                                                                    <button type="button"
                                                                            class="waves-effect waves-light btn btn-xs btn-primary js-open-sms-modal"
                                                                            title="Отправить смс"
                                                                            data-user="{$client->id}">
                                                                        <i class=" far fa-share-square"></i>
                                                                    </button>
                                                                    {if empty(blockcalls)}
                                                                        <button
                                                                                class="btn waves-effect waves-light btn-xs btn-success js-voximplant-call"
                                                                                data-phone="{$client->phone_mobile|escape}">
                                                                            <i class="fas fa-phone-square"></i>

                                                                        </button>
                                                                    {/if}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    {if $manager->role != 'verificator_minus'}
                                                        {foreach $additional_phones as $phone}
                                                            <div class="col-md-12">
                                                                <div class="form-group row">
                                                                    <label class="control-label col-md-4">Найденный
                                                                        номер:</label>
                                                                    <div class="col-md-8">
                                                                        <p class="form-control-static">{$phone->phone|escape}
                                                                            <button class="btn waves-effect waves-light btn-xs btn-info js-mango-call"
                                                                                    data-phone="{$phone->phone}"
                                                                                    data-user="{$client->id}"
                                                                                    title="Выполнить звонок"><i
                                                                                        class="fas fa-phone-square"></i>
                                                                            </button>
                                                                            <button type="button"
                                                                                    class="waves-effect waves-light btn btn-xs btn-primary js-open-sms-modal"
                                                                                    title="Отправить смс"
                                                                                    data-user="{$client->id}">
                                                                                <i class=" far fa-share-square"></i>
                                                                            </button>
                                                                            {if empty($blockcalls)}
                                                                                <button
                                                                                        class="btn waves-effect waves-light btn-xs btn-success js-voximplant-call"
                                                                                        data-phone="{$phone->phone|escape}">
                                                                                    <i class="fas fa-phone-square"></i>

                                                                                </button>
                                                                            {/if}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        {/foreach}
                                                    {/if}
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <label class="control-label col-md-4">Почта:</label>
                                                            <div class="col-md-8">
                                                                <p class="form-control-static">{$client->email|escape}
                                                                    {if $vk_user_id }<a
                                                                        href="https://vk.com/id{$vk_user_id}"
                                                                        target="_blank" class="label label-info">
                                                                            ВК</a>{/if}
                                                                    {if !empty($user_data)}
                                                                        {if !empty($user_data['whatsapp_phone'])}<a
                                                                            href="https://wa.me/{$user_data['whatsapp_phone']}"
                                                                            target="_blank" class="label label-success">
                                                                                WhatsApp</a>{/if}
                                                                        {if !empty($user_data['viber_phone'])}<a
                                                                            href="viber://chat?number={$user_data['viber_phone']}"
                                                                            target="_blank" class="label label-primary">
                                                                                Viber</a>{/if}
                                                                        {if !empty($user_data['skype_login'])}<a
                                                                            href="skype:{$user_data['skype_login']}"
                                                                            target="_blank" class="label label-info">
                                                                                Skype</a>{/if}
                                                                    {/if}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    {if $manager->role != 'verificator_minus'}
                                                        {foreach $additionalEmails as $email}
                                                            <div class="col-md-12">
                                                                <div class="form-group row">
                                                                    <label class="control-label col-md-4">Найденный
                                                                        email:</label>
                                                                    <div class="col-md-8">
                                                                        <p class="form-control-static">{$email->email|escape}</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        {/foreach}
                                                    {/if}
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <label class="control-label col-md-4">Пол:</label>
                                                            <div class="col-md-8">
                                                                <p class="form-control-static">
                                                                    {if $client->gender == 'male'}Мужской
                                                                    {elseif $client->gender == 'female'}Женский
                                                                    {else}Не указан{/if}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <label class="control-label col-md-4">Дата и место
                                                                рождения:</label>
                                                            <div class="col-md-8">
                                                                <p class="form-control-static">
                                                                    {$client->birth|escape}
                                                                    , {$client->birth_place|escape}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <label class="control-label col-md-4">Образование:</label>
                                                            <div class="col-md-8">
                                                                <p class="form-control-static">
                                                                    {$education_name}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    {if $manager->role != 'verificator_minus'}
                                                        <div class="col-md-12">
                                                            <div class="form-group row">
                                                                <label class="control-label col-md-4">Отмена страховки
                                                                    при пролонгации:</label>
                                                                <div class="col-md-8">
                                                                    <p class="form-control-static">
                                                                        {if $client->choose_insure}
                                                                            Да
                                                                        {else}
                                                                            Нет
                                                                        {/if}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group row">
                                                                <label class="control-label col-md-4">Кредитный
                                                                    рейтинг:</label>
                                                                <div class="col-md-8">
                                                                    <p class="form-control-static"
                                                                       style="color: {if $has_pay_credit_rating } #31b131 {else} #f62d51 {/if};">
                                                                        {if $has_pay_credit_rating}
                                                                            Оплачен
                                                                        {else}
                                                                            Не оплачен
                                                                        {/if}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    {/if}
                                                </div>

                                                <div class="row edit-block {if !$personal_error}hide{/if}">
                                                    {if $personal_error}
                                                        <div class="col-md-12">
                                                            <ul class="alert alert-danger">
                                                                {if in_array('empty_lastname', (array)$personal_error)}
                                                                    <li>Укажите Фамилию!</li>
                                                                {/if}
                                                                {if in_array('empty_firstname', (array)$personal_error)}
                                                                    <li>Укажите Имя!</li>
                                                                {/if}
                                                                {if in_array('empty_patronymic', (array)$personal_error)}
                                                                    <li>Укажите Отчество!</li>
                                                                {/if}
                                                                {if in_array('empty_gender', (array)$personal_error)}
                                                                    <li>Укажите Пол!</li>
                                                                {/if}
                                                                {if in_array('empty_birth', (array)$personal_error)}
                                                                    <li>Укажите Дату рождения!</li>
                                                                {/if}
                                                                {if in_array('empty_birth_place', (array)$personal_error)}
                                                                    <li>Укажите Место рождения!</li>
                                                                {/if}

                                                                {if in_array('empty_phone', (array)$personal_error)}
                                                                    <li>Укажите Номер телефона!</li>
                                                                {/if}
                                                            </ul>
                                                        </div>
                                                    {/if}
                                                    <div class="col-md-4">
                                                        <div class="form-group {if in_array('empty_lastname', (array)$personal_error)}has-danger{/if}">
                                                            <label class="control-label">Фамилия</label>
                                                            <input type="text" name="lastname"
                                                                   value="{$client->lastname|escape}"
                                                                   class="form-control" data-cyrillic="fio"
                                                                   placeholder="Фамилия" required="true"/>
                                                            {if in_array('empty_lastname', (array)$personal_error)}
                                                                <small class="form-control-feedback">Укажите
                                                                    Фамилию!</small>
                                                            {/if}
                                                            {if in_array('symbols_lastname', (array)$personal_error)}
                                                                <small class="form-control-feedback">Допускается ввод
                                                                    только русских букв</small>
                                                            {/if}
                                                            {include file='client_log.tpl' log=$client_log['lastname']}
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group {if in_array('empty_firstname', (array)$personal_error)}has-danger{/if}">
                                                            <label class="control-label">Имя</label>
                                                            <input type="text" name="firstname"
                                                                   value="{$client->firstname|escape}"
                                                                   class="form-control" data-cyrillic="fio"
                                                                   placeholder="Имя" required="true"/>
                                                            {if in_array('empty_firstname', (array)$personal_error)}
                                                                <small class="form-control-feedback">Укажите
                                                                    Имя!</small>
                                                            {/if}
                                                            {if in_array('symbols_firstname', (array)$personal_error)}
                                                                <small class="form-control-feedback">Допускается ввод
                                                                    только русских букв</small>
                                                            {/if}
                                                            {include file='client_log.tpl' log=$client_log['firstname']}
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group {if in_array('empty_patronymic', (array)$personal_error)}has-danger{/if}">
                                                            <label class="control-label">Отчество</label>
                                                            <input type="text" name="patronymic"
                                                                   value="{$client->patronymic|escape}"
                                                                   class="form-control" data-cyrillic="fio"
                                                                   placeholder="Отчество" required="true"/>
                                                            {if in_array('empty_patronymic', (array)$personal_error)}
                                                                <small class="form-control-feedback">Укажите
                                                                    Отчество!</small>
                                                            {/if}
                                                            {if in_array('symbols_patronymic', (array)$personal_error)}
                                                                <small class="form-control-feedback">Допускается ввод
                                                                    только русских букв</small>
                                                            {/if}
                                                            {include file='client_log.tpl' log=$client_log['patronymic']}
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-group {if in_array('empty_gender', (array)$personal_error)}has-danger{/if}">
                                                            <label class="control-label">Пол</label>
                                                            <select class="form-control custom-select" name="gender">
                                                                <option value="male"
                                                                        {if $client->gender == 'male'}selected="true"{/if}>
                                                                    Мужской
                                                                </option>
                                                                <option value="female"
                                                                        {if $client->gender == 'female'}selected="true"{/if}>
                                                                    Женский
                                                                </option>
                                                            </select>
                                                            {if in_array('empty_gender', (array)$personal_error)}
                                                                <small class="form-control-feedback">Укажите
                                                                    Пол!</small>
                                                            {/if}
                                                            {include file='client_log.tpl' log=$client_log['gender']}
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-group {if in_array('empty_birth', (array)$personal_error)}has-danger{/if}">
                                                            <label class="control-label">Дата рождения</label>
                                                            <input type="text" class="form-control" name="birth"
                                                                   value="{$client->birth|escape}"
                                                                   placeholder="dd.mm.yyyy" required="true"/>
                                                            {if in_array('empty_birth', (array)$personal_error)}
                                                                <small class="form-control-feedback">Укажите Дату
                                                                    рождения!</small>
                                                            {/if}
                                                            {include file='client_log.tpl' log=$client_log['birth']}
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group {if in_array('empty_birth_place', (array)$personal_error)}has-danger{/if}">
                                                            <label class="control-label">Место рождения</label>
                                                            <input type="text" class="form-control"
                                                                   data-cyrillic="with-numbers" name="birth_place"
                                                                   value="{$client->birth_place|escape}" placeholder=""
                                                                   required="true"/>
                                                            {if in_array('empty_birth_place', (array)$personal_error)}
                                                                <small class="form-control-feedback">Укажите Место
                                                                    рождения!</small>
                                                            {/if}
                                                            {if in_array('symbols_birth_place', (array)$personal_error)}
                                                                <small class="form-control-feedback">Допускается ввод
                                                                    только русских букв</small>
                                                            {/if}
                                                            {include file='client_log.tpl' log=$client_log['birth_place']}
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group ">
                                                            <label class="control-label">Почта</label>
                                                            <input type="text" class="form-control" name="email"
                                                                   value="{$client->email|escape}" placeholder=""/>
                                                            {include file='client_log.tpl' log=$client_log['email']}
                                                        </div>
                                                    </div>
                                                    {if $manager->role != 'verificator_minus'}
                                                        <div class="col-md-4">
                                                            <div class="form-group ">
                                                                <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                                                    <input type="checkbox" class="custom-control-input"
                                                                           id="choose_insure" name="choose_insure"
                                                                           value="1"
                                                                           {if $client->choose_insure}checked="true"{/if}>
                                                                    <label class="custom-control-label"
                                                                           for="choose_insure">Отмена страховки при
                                                                        пролонгации</label>
                                                                    {include file='client_log.tpl' log=$client_log['choose_insure']}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    {/if}
                                                    {if in_array($manager->role, ['admin','developer', 'contact_center_plus', 'boss_cc', 'contact_center', 'yurist', 'opr', 'ts_operator'])}
                                                        <div class="col-md-4">
                                                            <div class="form-group {if in_array('empty_phone', (array)$personal_error)}has-danger{/if}">
                                                                <label class="control-label">Номер телефона</label>
                                                                <input type="text" class="form-control"
                                                                       name="phone_mobile"
                                                                       value="{$client->phone_mobile|escape}"
                                                                       placeholder="" required="true"/>
                                                                {if in_array('empty_phone', (array)$personal_error)}
                                                                    <small class="form-control-feedback">Укажите Номер
                                                                        телефона!</small>
                                                                {/if}
                                                                {include file='client_log.tpl' log=$client_log['phone_mobile']}
                                                            </div>
                                                        </div>
                                                    {else}
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="control-label">Номер телефона</label>
                                                                <input type="text" class="form-control"
                                                                       value="{$client->phone_mobile|escape}" readonly/>
                                                                {include file='client_log.tpl' log=$client_log['phone_mobile']}
                                                            </div>
                                                        </div>
                                                    {/if}
                                                    {if in_array($manager->role, ['developer', 'admin', 'contact_center_plus', 'opr', 'ts_operator'])}
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="control-label">УИД клиента</label>
                                                                <input type="text" class="form-control" name="uid"
                                                                       value="{$client->UID|escape}" placeholder=""/>
                                                                {include file='client_log.tpl' log=$client_log['UID']}
                                                            </div>
                                                        </div>
                                                    {else}
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="control-label">УИД клиента</label>
                                                                <input type="text" class="form-control" name="uid"
                                                                       value="{$client->UID|escape}" readonly/>
                                                                {include file='client_log.tpl' log=$client_log['UID']}
                                                            </div>
                                                        </div>
                                                    {/if}
                                                    {if !empty($additional_phones) && $manager->role != 'verificator_minus'}
                                                        <div class="col-md-7">
                                                            <div class="form-group">
                                                                <label class="control-label">Найденные номера:</label>
                                                                {foreach $additional_phones as $phone}
                                                                    <div class="row mb-2 js-additionalphone-row">
                                                                        <div class="col-7">
                                                                            <input name="additional_phones[]"
                                                                                   type="text" class="form-control"
                                                                                   value="{$phone->phone|escape}"
                                                                                   readonly/>
                                                                        </div>
                                                                        <div class="col-5">
                                                                            <button class="btn btn-danger js-remove-additionalphone">
                                                                                <i class="fas fas fa-times-circle"></i>
                                                                                Удалить
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                {/foreach}
                                                            </div>
                                                        </div>
                                                    {/if}
                                                    {if !empty($additionalEmails)}
                                                        <div class="col-md-7">
                                                            <div class="form-group">
                                                                <label class="control-label">Найденные почты:</label>

                                                                {assign var=has_unsynced_emails value=false}
                                                                {foreach $additionalEmails as $email}
                                                                    {if empty($email->synced_at)}{assign var=has_unsynced_emails value=true}{/if}
                                                                    <div class="row mb-2 js-additionalemail-row"
                                                                         data-email="{$email->email|escape}"
                                                                         data-synced="{if empty($email->synced_at)}0{else}1{/if}">
                                                                        <div class="col-7">
                                                                            <input name="additionalEmails[]" type="text"
                                                                                   class="form-control"
                                                                                   value="{$email->email|escape}"
                                                                                   readonly/>
                                                                        </div>
                                                                        <div class="col-5">
                                                                            {if empty($email->synced_at)}
                                                                                <span class="badge badge-warning js-email-sync-badge">Не синхронизирован</span>
                                                                            {else}
                                                                                <span class="badge badge-success js-email-sync-badge">Синхронизирован</span>
                                                                            {/if}
                                                                            <button class="btn btn-danger js-remove-additionalemail">
                                                                                <i class="fas fas fa-times-circle"></i>
                                                                                Удалить
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                {/foreach}

                                                                {if $has_unsynced_emails}
                                                                    <button type="button"
                                                                            class="btn btn-warning mt-2 js-sync-user-emails"
                                                                            data-user-id="{$client->id}">
                                                                        <i class="fa fa-refresh"></i> Отправить
                                                                        несинхронизированные email в 1С
                                                                    </button>
                                                                {/if}
                                                            </div>
                                                        </div>
                                                    {/if}

                                                    <div class="col-md-12">
                                                        <div class="form-actions">
                                                            <button type="submit"
                                                                    class="btn btn-success js-save-no-agreement"><i
                                                                        class="fa fa-check"></i> Сохранить
                                                            </button>
                                                            {if in_array($manager->role, ['developer', 'chief_verificator', 'ts_operator']) && !$has_approved_orders}
                                                                <button type="submit"
                                                                        class="btn btn-primary js-save-agreement"><i
                                                                            class="fa fa-file"></i> Подписать доп.
                                                                    соглашение
                                                                </button>
                                                            {/if}
                                                            <button type="button"
                                                                    class="btn btn-inverse js-cancel-edit">Отмена
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <!-- Боковая панель с кнопками -->
                                        <div class="col-md-4">
                                            <div class="sidebar-buttons">
                                                <!-- Кнопки ЧС -->
                                                <div class="blacklist-user mb-3">
                                                    {if !$blacklist}
                                                        <button data-user="{$client->id}" data-state="1"
                                                                class="btn btn-block btn-danger js-blacklist-user">
                                                            Добавить в ЧС
                                                        </button>
                                                    {else}
                                                        <span class="text-black alert d-block mb-2 sidebar-note">Клиент в ЧС!</span>
                                                        <button data-user="{$client->id}" data-state="0"
                                                                class="btn btn-block btn-primary js-blacklist-user">
                                                            Убрать из ЧС
                                                        </button>
                                                    {/if}
                                                </div>
                                                <!-- Создать тикет -->
                                                {if $manager->role != 'verificator_minus'}
                                                    <div class="create-ticket mb-3">
                                                        <a href="/tickets/create?client_id={$client->id}"
                                                           target="_blank" class="btn btn-block btn-success">
                                                            <i class="fas fa-plus"></i> Создать тикет
                                                        </a>
                                                    </div>
                                                {/if}
                                                <!-- Блокировка звонков клиенту -->
                                                <div class="block-calls-section mb-3 text-right">
                                                    {if empty($blockcalls)}
                                                        <input type="number" class="form-control mb-2 days-count"
                                                               placeholder="Кол-во дней блокировки" min="1">
                                                        <button data-user="{$client->id}"
                                                                class="btn btn-block btn-danger js-client-blacklist-call-user"
                                                                value='1' data-manager="{$manager->id}">
                                                            Блокировать звонки клиенту
                                                        </button>
                                                    {else}
                                                        <div class="alert alert-dark mb-2 p-2 text-center sidebar-note">
                                                            <small>Звонки клиенту заблокированы</small>
                                                        </div>
                                                        <button data-user="{$client->id}"
                                                                class="btn btn-block btn-secondary js-client-blacklist-call-user"
                                                                value='0' data-manager="{$manager->id}">
                                                            Разблокировать звонки
                                                        </button>
                                                    {/if}
                                                </div>

                                                <hr class="my-3">

                                                <!-- SMS кнопки -->
                                                {if in_array($manager->role, ['developer', 'ts_operator'])}
                                                    <button class="btn btn-block btn-info btn-sm mb-2 js-send-sms-lk send-data-to-modal"
                                                            data-type="sms-lk" data-manager="{$manager->id}"
                                                            data-target="#smsModal" data-toggle="modal" type="button">
                                                        СМС-ЛК
                                                    </button>
                                                {/if}
                                                <button class="btn btn-block btn-info btn-sm mb-2 js-send-sms-prolongation send-data-to-modal"
                                                        data-type="sms-prolongation" data-manager="{$manager->id}"
                                                        data-target="#smsModal" data-toggle="modal" type="button">
                                                    СМС-пролонгация
                                                </button>
                                                <button class="btn btn-block btn-info btn-sm mb-2 js-send-sms-payment send-data-to-modal"
                                                        data-type="sms-payment" data-manager="{$manager->id}"
                                                        data-target="#smsModal" data-toggle="modal" type="button">
                                                    СМС-оплата
                                                </button>
                                                <button type="button" data-type="payment" data-target="#paymentModal"
                                                        data-toggle="modal"
                                                        class="btn btn-block btn-success btn-sm mb-2 js-sen-payment send-data-to-modal">
                                                    Клиент оплатил по реквизитам
                                                </button>

                                                <hr class="my-3">

                                                <!-- Управление клиентом -->
                                                <div class="mb-2">
                                                    {if !empty($user_data) && !empty($user_data['gray_list'])}
                                                        <button data-user="{$client->id}" data-action="graylist"
                                                                class="btn btn-block btn-primary btn-sm js-graylist-user">
                                                            Убрать подозрение в мошенничестве
                                                        </button>
                                                    {else}
                                                        <button data-user="{$client->id}" data-action="graylist"
                                                                class="btn btn-block btn-outline-danger btn-sm js-graylist-user">
                                                            Подозрение в мошенничестве
                                                        </button>
                                                    {/if}
                                                </div>

                                                <div class="mb-2">
                                                    {if !empty($user_data) && !empty($user_data['show_order_information'])}
                                                        <button data-user="{$client->id}"
                                                                data-action="toggle_user_data_field"
                                                                data-field="show_order_information"
                                                                class="btn btn-block btn-primary btn-sm js-toggle_user_data_field-user">
                                                            Отключить принудительный показ информации (сумма займа) в ЛК
                                                        </button>
                                                    {else}
                                                        <button data-user="{$client->id}"
                                                                data-action="toggle_user_data_field"
                                                                data-field="show_order_information"
                                                                class="btn btn-block btn-outline-secondary btn-sm js-toggle_user_data_field-user">
                                                            Показать информацию (сумма займа) в ЛК
                                                        </button>
                                                    {/if}
                                                </div>

                                                <div class="mb-2">
                                                    {if !empty($user_data) && !empty($user_data['whitelist_dop'])}
                                                        <button data-user="{$client->id}"
                                                                data-action="toggle_user_data_field"
                                                                data-field="whitelist_dop"
                                                                class="btn btn-block btn-primary btn-sm js-toggle_user_data_field-user">
                                                            Удалить из белого списка ДОП
                                                        </button>
                                                    {else}
                                                        <button data-user="{$client->id}"
                                                                data-action="toggle_user_data_field"
                                                                data-field="whitelist_dop"
                                                                class="btn btn-block btn-outline-secondary btn-sm js-toggle_user_data_field-user">
                                                            Белый список ДОП
                                                        </button>
                                                    {/if}
                                                </div>

                                                <!-- Исходящие звонки -->
                                                <div class="mb-2">
                                                    {if $calls_disabled}
                                                        <small class="text-muted d-block mb-1">Исх. звонки откл.
                                                            до {$calls_disabled->date_end|date_format:"%d.%m.%Y"}</small>
                                                        <button data-user="{$client->id}"
                                                                class="btn btn-block btn-success btn-sm js-enable-calls"
                                                                type="button">
                                                            Включить исх. звонки
                                                        </button>
                                                    {else}
                                                        <div class="dropdown">
                                                            <button data-user="{$client->id}"
                                                                    class="btn btn-block btn-outline-danger btn-sm dropdown-toggle js-disable-calls-btn"
                                                                    type="button" id="disableCallsDropdown"
                                                                    data-toggle="dropdown" aria-haspopup="true"
                                                                    aria-expanded="false">
                                                                Откл. исх. звонки
                                                            </button>
                                                            <div class="dropdown-menu"
                                                                 aria-labelledby="disableCallsDropdown">
                                                                {section name=days start=1 loop=6}
                                                                    <a class="dropdown-item js-disable-calls" href="#"
                                                                       data-user="{$client->id}"
                                                                       data-days="{$smarty.section.days.index}">На {$smarty.section.days.index} {if $smarty.section.days.index == 1}день{elseif $smarty.section.days.index < 5}дня{else}дней{/if}</a>
                                                                {/section}
                                                            </div>
                                                        </div>
                                                    {/if}
                                                </div>

                                                {if in_array($manager->role, ['developer', 'ts_operator'])}
                                                    <div class="mb-2">
                                                        {if !empty($user_data) && !empty($user_data['test_user'])}
                                                            <button type="button" data-user="{$client->id}"
                                                                    data-action="test_user"
                                                                    class="btn btn-block btn-success btn-sm js-test-loan">
                                                                Не тестовый пользователь
                                                            </button>
                                                        {else}
                                                            <button type="button" data-user="{$client->id}"
                                                                    data-action="test_user"
                                                                    class="btn btn-block btn-warning btn-sm js-test-loan">
                                                                Тестовый пользователь
                                                            </button>
                                                        {/if}
                                                    </div>
                                                {/if}

                                                {if !empty($user_data) && !empty($user_data['terrorist_status']) && $user_data['terrorist_status'] == 1}
                                                    <div class="alert alert-danger p-2 mt-2">
                                                        <small>
                                                            <i class="fa fa-exclamation-triangle"></i> Клиент в террор.
                                                            списках
                                                            {if !empty($user_data['terrorist_scoring_id'])}
                                                                <button type="button"
                                                                        class="btn btn-xs btn-outline-light ml-1 js-terrorist-details"
                                                                        data-scoring-id="{$user_data['terrorist_scoring_id']}"
                                                                        style="padding: 1px 5px; font-size: 10px;">
                                                                    Детали
                                                                </button>
                                                            {/if}
                                                        </small>
                                                    </div>
                                                {/if}
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /Персональные данные -->

                                    <hr>

                                    {if in_array($manager->role, ['developer', 'admin', 'contact_center', 'boss_cc', 'opr', 'ts_operator'])}
                                        <div class="row mt-5">
                                            <p class="col-md">Списание ЗО на оплате через рекуррентный платеж</p>
                                            <div>
                                                <div class="onoffswitch">
                                                    <input type="checkbox" name="client_recurring_payment_so"
                                                           class="onoffswitch-checkbox"
                                                           id="client_recurring_payment_so"
                                                            {if $client_data['client_recurring_payment_so'] === '0'} value="0" {else} value="1" checked {/if}
                                                    />
                                                    <label class="onoffswitch-label"
                                                           for="client_recurring_payment_so">
                                                        <span class="onoffswitch-inner"></span>
                                                        <span class="onoffswitch-switch"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    {/if}

                                    {if in_array('client_cession_info', $manager->permissions)}
                                        <div class="row mt-5">
                                            <p class="col-md">Выводить уведомление о цессии в ЛК</p>
                                            <div>
                                                <div class="onoffswitch">
                                                    <input type="checkbox" name="show_cession_info"
                                                           class="onoffswitch-checkbox"
                                                           id="show_cession_info"
                                                           data-user="{$client->id}"
                                                           {if !isset($client_data['show_cession_info']) || $client_data['show_cession_info'] == 1}checked{/if}
                                                    />
                                                    <label class="onoffswitch-label"
                                                           for="show_cession_info">
                                                        <span class="onoffswitch-inner"></span>
                                                        <span class="onoffswitch-switch"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-5">
                                            <p class="col-md">Выводить уведомление о передаче агентам в ЛК</p>
                                            <div>
                                                <div class="onoffswitch">
                                                    <input type="checkbox" name="show_agent_info"
                                                           class="onoffswitch-checkbox"
                                                           id="show_agent_info"
                                                           data-user="{$client->id}"
                                                           {if !isset($client_data['show_agent_info']) || $client_data['show_agent_info'] == 1}checked{/if}
                                                    />
                                                    <label class="onoffswitch-label"
                                                           for="show_agent_info">
                                                        <span class="onoffswitch-inner"></span>
                                                        <span class="onoffswitch-switch"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    {/if}

                                    <!-- Паспортные данные -->
                                    <form action="{url}" class="mt-5 mb-3 js-order-item-form" id="passport_data_form">

                                        <input type="hidden" name="action" value="passport" />
                                        <input type="hidden" name="user_id" value="{$client->id}" />

                                        <h3 class="box-title">
                                            <a href="javascript:void(0);" class="js-edit-form">
                                                <span>Паспортные данные</span>
                                            </a>
                                        </h3>
                                        <hr>

                                        <div class="row view-block {if $passport_error}hide{/if}">
                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <label class="control-label col-md-4">Паспорт:</label>
                                                    <div class="col-md-8">
                                                        <p class="form-control-static">{$client->passport_serial|escape}, от {$client->passport_date|escape}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <label class="control-label col-md-4">Код подразделения:</label>
                                                    <div class="col-md-8">
                                                        <p class="form-control-static">{$client->subdivision_code|escape}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <label class="control-label col-md-4">Кем выдан:</label>
                                                    <div class="col-md-8">
                                                        <p class="form-control-static">{$client->passport_issued|escape}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row edit-block {if !$passport_error}hide{/if}">
                                            {if $passport_error}
                                                <div class="col-md-12">
                                                    <ul class="alert alert-danger">
                                                        {if $passport_error[$client->id]}
                                                            <li>
                                                                Клиент с такими паспортными данными уже зарегистрирован!<br/>
                                                                <a href="http://manager.boostra.ru/client/{$passport_error[$client->id]}">
                                                                    manager.boostra.ru/client/{$passport_error[$client->id]}
                                                                </a>
                                                            </li>
                                                        {/if}
                                                        {if in_array('empty_passport_serial', (array)$passport_error)}<li>Укажите серию и номер паспорта!</li>{/if}
                                                        {if in_array('empty_passport_date', (array)$passport_error)}<li>Укажите дату выдачи паспорта!</li>{/if}
                                                        {if in_array('empty_subdivision_code', (array)$passport_error)}<li>Укажите код подразделения выдавшего паспорт!</li>{/if}
                                                        {if in_array('empty_passport_issued', (array)$passport_error)}<li>Укажите кем выдан паспорт!</li>{/if}
                                                    </ul>
                                                </div>
                                            {/if}
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_passport_serial', (array)$passport_error)}has-danger{/if}">
                                                    <label class="control-label">Серия и номер паспорта</label>
                                                    <input type="text" class="form-control" name="passport_serial" value="{$client->passport_serial|escape}" placeholder="" required="true" />
                                                    {if in_array('empty_passport_serial', (array)$passport_error)}<small class="form-control-feedback">Укажите серию и номер паспорта!</small>{/if}
                                                    {include file='client_log.tpl' log=$client_log['passport_serial']}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_passport_date', (array)$passport_error)}has-danger{/if}">
                                                    <label class="control-label">Дата выдачи</label>
                                                    <input type="text" class="form-control" name="passport_date" value="{$client->passport_date|escape}" placeholder="" required="true" />
                                                    {if in_array('empty_passport_date', (array)$passport_error)}<small class="form-control-feedback">Укажите дату выдачи паспорта!</small>{/if}
                                                    {include file='client_log.tpl' log=$client_log['passport_date']}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_subdivision_code', (array)$passport_error)}has-danger{/if}">
                                                    <label class="control-label">Код подразделения</label>
                                                    <input type="text" class="form-control" name="subdivision_code" value="{$client->subdivision_code|escape}" placeholder="" required="true" />
                                                    {if in_array('empty_subdivision_code', (array)$passport_error)}<small class="form-control-feedback">Укажите код подразделения выдавшего паспорт!</small>{/if}
                                                    {include file='client_log.tpl' log=$client_log['subdivision_code']}
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group {if in_array('empty_passport_issued', (array)$passport_error)}has-danger{/if}">
                                                    <label class="control-label">Кем выдан</label>
                                                    <input type="text" class="form-control" data-cyrillic="with-numbers" name="passport_issued" value="{$client->passport_issued|escape}" placeholder="" required="true" />
                                                    {if in_array('empty_passport_issued', (array)$passport_error)}<small class="form-control-feedback">Укажите кем выдан паспорт!</small>{/if}
                                                    {if in_array('symbols_passport_issued', (array)$passport_error)}
                                                        <small class="form-control-feedback">Допускается ввод только русских букв</small>
                                                    {/if}
                                                    {include file='client_log.tpl' log=$client_log['passport_issued']}
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-actions">
                                                    <button type="submit" class="btn btn-success js-save-no-agreement"> <i class="fa fa-check"></i> Сохранить</button>
                                                    {if in_array($manager->role, ['developer', 'chief_verificator', 'ts_operator']) && !$has_approved_orders}
                                                        <button type="submit" class="btn btn-primary js-save-agreement"> <i class="fa fa-file"></i> Подписать доп. соглашение</button>
                                                    {/if}
                                                    <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    <!-- /Паспортные данные -->

                                    <!-- Прописка -->
                                    <form action="{url}" class="js-order-item-form mb-3" id="reg_address_form">

                                        <input type="hidden" name="action" value="reg_address" />
                                        <input type="hidden" name="user_id" value="{$client->id}" />

                                        <h3 class="box-title">
                                            <a href="javascript:void(0);" class="js-edit-form">
                                                <span>Адрес прописки</span>
                                            </a>
                                        </h3>
                                        <hr>

                                        <div class="row view-block {if $regaddress_error}hide{/if}">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <p class="form-control-static">
                                                        {$client->Regindex|escape},
                                                        {$client->Regregion|escape},
                                                        {$client->Regcity|escape},
                                                        {$client->Regstreet|escape},
                                                        д.{$client->Reghousing|escape},
                                                        {if $client->Regbuilding|escape}стр. {$client->Regbuilding|escape},{/if}
                                                        {if $client->Regroom|escape}кв.{$client->Regroom|escape}{/if}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row edit-block {if !$regaddress_error}hide{/if}">
                                            {if $regaddress_error}
                                                <div class="col-md-12">
                                                    <ul class="alert alert-danger">
                                                        {if in_array('empty_regregion', (array)$regaddress_error)}<li>Укажите область!</li>{/if}
                                                        {if in_array('empty_regcity', (array)$regaddress_error)}<li>Укажите город!</li>{/if}
                                                        {if in_array('empty_regstreet', (array)$regaddress_error)}<li>Укажите улицу!</li>{/if}
                                                        {if in_array('empty_reghousing', (array)$regaddress_error)}<li>Укажите дом!</li>{/if}
                                                    </ul>
                                                </div>
                                            {/if}
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_regregion', (array)$regaddress_error)}has-danger{/if}">
                                                    <label class="control-label">Область</label>
                                                    <input type="text" class="form-control" data-cyrillic="with-numbers" name="Regregion" value="{$client->Regregion|escape}" placeholder="" required="true" />
                                                    {if in_array('empty_regregion', (array)$regaddress_error)}<small class="form-control-feedback">Укажите область!</small>{/if}
                                                    {if in_array('symbols_regregion', (array)$regaddress_error)}
                                                        <small class="form-control-feedback">Допускается ввод только русских букв</small>
                                                    {/if}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_regcity', (array)$regaddress_error)}has-danger{/if}">
                                                    <label class="control-label">Город</label>
                                                    <input type="text" class="form-control" data-cyrillic="with-numbers" name="Regcity" value="{$client->Regcity|escape}" placeholder="" required="true" />
                                                    {if in_array('empty_regcity', (array)$regaddress_error)}<small class="form-control-feedback">Укажите город!</small>{/if}
                                                    {if in_array('symbols_regcity', (array)$regaddress_error)}
                                                        <small class="form-control-feedback">Допускается ввод только русских букв</small>
                                                    {/if}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_regstreet', (array)$regaddress_error)}has-danger{/if}">
                                                    <label class="control-label">Улица</label>
                                                    <input type="text" class="form-control" data-cyrillic="with-numbers" name="Regstreet" value="{$client->Regstreet|escape}" placeholder="" />
                                                    {if in_array('empty_regstreet', (array)$regaddress_error)}<small class="form-control-feedback">Укажите улицу!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_reghousing', (array)$regaddress_error)}has-danger{/if}">
                                                    <label class="control-label">Дом</label>
                                                    <input type="text" class="form-control" data-cyrillic="with-numbers" name="Reghousing" value="{$client->Reghousing|escape}" placeholder="" required="true" />
                                                    {if in_array('empty_reghousing', (array)$regaddress_error)}<small class="form-control-feedback">Укажите дом!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">Строение</label>
                                                    <input type="text" class="form-control" name="Regbuilding" value="{$client->Regbuilding|escape}" placeholder="" />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">Квартира</label>
                                                    <input type="text" class="form-control" name="Regroom" value="{$client->Regroom|escape}" placeholder="" required="true" />
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-actions">
                                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                                    <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    <!-- /Прописка -->

                                    <!-- Адрес проживания -->
                                    <form action="{url}" class="js-order-item-form mb-3" id="faktaddress_form">

                                        <input type="hidden" name="action" value="fakt_address" />
                                        <input type="hidden" name="user_id" value="{$client->id}" />

                                        <h3 class="box-title">
                                            <a {if empty($pdn_results)}href="javascript:void(0);" class="js-edit-form"{/if}>
                                                <span>Адрес проживания</span>
                                            </a>
                                        </h3>
                                        <hr>

                                        <div class="row view-block {if $faktaddress_error}hide{/if}">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    {if empty($pdn_results)}
                                                        <p class="form-control-static">
                                                            {$client->Faktindex|escape},
                                                            {$client->Faktregion|escape},
                                                            {$client->Faktcity|escape},
                                                            {$client->Faktstreet|escape},
                                                            д.{$client->Fakthousing|escape}
                                                            {if $client->Faktbuilding|escape}, стр. {$client->Faktbuilding|escape}{/if}
                                                            {if $client->Faktroom|escape}, кв.{$client->Faktroom|escape}{/if}
                                                        </p>
                                                    {else}
                                                        {foreach $pdn_results as $pdn_result}
                                                            <p class="form-control-static">
                                                                Заявка № {$pdn_result->order_id}:
                                                                {$pdn_result->fakt_address->address_index|escape},
                                                                {$pdn_result->fakt_address->region|escape},
                                                                {$pdn_result->fakt_address->city|escape},
                                                                {$pdn_result->fakt_address->street|escape},
                                                                д.{$pdn_result->fakt_address->housing|escape}{if $pdn_result->fakt_address->building|escape}, стр. {$pdn_result->fakt_address->building|escape}{/if}{if $pdn_result->fakt_address->room|escape}, кв.{$pdn_result->fakt_address->room|escape}{/if}
                                                            </p>
                                                        {/foreach}
                                                    {/if}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row edit-block {if !$faktaddress_error}hide{/if}">
                                            {if $faktaddress_error}
                                                <div class="col-md-12">
                                                    <ul class="alert alert-danger">
                                                        {if in_array('empty_faktregion', (array)$faktaddress_error)}<li>Укажите область!</li>{/if}
                                                        {if in_array('empty_faktcity', (array)$faktaddress_error)}<li>Укажите город!</li>{/if}
                                                        {if in_array('empty_faktstreet', (array)$faktaddress_error)}<li>Укажите улицу!</li>{/if}
                                                        {if in_array('empty_fakthousing', (array)$faktaddress_error)}<li>Укажите дом!</li>{/if}
                                                    </ul>
                                                </div>
                                            {/if}
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_faktregion', (array)$faktaddress_error)}has-danger{/if}">
                                                    <label class="control-label">Область</label>
                                                    <input type="text" class="form-control" data-cyrillic="with-numbers" name="Faktregion" value="{$client->Faktregion|escape}" placeholder="" required="true" />
                                                    {if in_array('empty_faktregion', (array)$faktaddress_error)}<small class="form-control-feedback">Укажите область!</small>{/if}
                                                    {if in_array('symbols_faktregion', (array)$faktaddress_error)}
                                                        <small class="form-control-feedback">Допускается ввод только русских букв</small>
                                                    {/if}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_faktcity', (array)$faktaddress_error)}has-danger{/if}">
                                                    <label class="control-label">Город</label>
                                                    <input type="text" class="form-control" data-cyrillic="with-numbers" name="Faktcity" value="{$client->Faktcity|escape}" placeholder="" required="true" />
                                                    {if in_array('empty_faktcity', (array)$faktaddress_error)}<small class="form-control-feedback">Укажите город!</small>{/if}
                                                    {if in_array('symbols_faktcity', (array)$faktaddress_error)}
                                                        <small class="form-control-feedback">Допускается ввод только русских букв</small>
                                                    {/if}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_faktstreet', (array)$faktaddress_error)}has-danger{/if}">
                                                    <label class="control-label">Улица</label>
                                                    <input type="text" class="form-control" data-cyrillic="with-numbers" name="Faktstreet" value="{$client->Faktstreet|escape}" placeholder=""/>
                                                    {if in_array('empty_faktstreet', (array)$faktaddress_error)}<small class="form-control-feedback">Укажите улицу!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_fakthousing', (array)$faktaddress_error)}has-danger{/if}">
                                                    <label class="control-label">Дом</label>
                                                    <input type="text" class="form-control" name="Fakthousing" data-cyrillic="with-numbers" value="{$client->Fakthousing|escape}" placeholder="" required="true" />
                                                    {if in_array('empty_fakthousing', (array)$faktaddress_error)}<small class="form-control-feedback">Укажите дом!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">Строение</label>
                                                    <input type="text" class="form-control" name="Faktbuilding" value="{$client->Faktbuilding|escape}" placeholder="" />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">Квартира</label>
                                                    <input type="text" class="form-control" name="Faktroom" value="{$client->Faktroom|escape}" placeholder="" required="true" />
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-actions">
                                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                                    <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    <!-- /Адрес проживания -->

                                    {if $manager->role != 'verificator_minus'}
                                    <!-- Контактные лица -->
                                    <form action="{url}" class="js-order-item-form mb-3" id="contact_persons_form">

                                        <input type="hidden" name="action" value="contacts" />
                                        <input type="hidden" name="user_id" value="{$client->id}" />

                                        <div class="row">
                                                <div class="col-md-9">
                                                    <h3 class="box-title">
                                                        <a href="javascript:void(0);" class="js-edit-form">
                                                            <span>Контактные лица</span>
                                                        </a>
                                                    </h3>
                                                </div>
                                                <div class="col-md-3 d-flex justify-content-end">
                                                {if count($contactpersons) > 2}
                                                    <button class="btn btn-primary toggle-contacts" type="button" data-toggle="collapse" data-target="#contactsCollapse" aria-expanded="false" aria-controls="contactsCollapse">
                                                        Показать всех
                                                    </button>
                                                {/if}
                                                </div>
                                        </div>
                                        
                                        <hr>

                                        <div class="row view-block {if $contacts_error}hide{/if}">
                                            {foreach $contactpersons as $key => $contactperson}
                                                {if $key < 2}
                                                <div class="col-md-12">
                                                    <div class="form-group row {if in_array('empty_fakthousing', (array)$contacts_error)}has-danger{/if}">
                                                        <div class="col-md-8">
                                                            <p class="form-control-static">
                                                                <strong>
                                                                    {$contactperson->name|escape}
                                                                    ({$contactperson->relation|escape})
                                                                    {$contactperson->phone|escape}
                                                                </strong>
                                                                {if $contactperson->phone}
                                                                    <button class="btn waves-effect waves-light btn-xs btn-info js-mango-call" data-phone="{$contactperson->phone|escape}" data-user="{$client->id}" title="Выполнить звонок"><i class="fas fa-phone-square"></i></button>
                                                                    <button
                                                                            class="btn waves-effect waves-light btn-xs btn-success js-voximplant-call"
                                                                            data-phone="{$contactperson->phone|escape}">
                                                                        <i class="fas fa-phone-square"></i>

                                                                    </button>

                                                                {/if}
                                                            </p>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <i>{$contactperson->comment}</i>
                                                        </div>
                                                    </div>
                                                </div>
                                                {/if}
                                            {/foreach}

                                            {if count($contactpersons) > 2}
                                                
                                                <div class="collapse col-md-12" id="contactsCollapse">
                                                    <div class="row">
                                                    {foreach $contactpersons as $key => $contactperson}
                                                        {if $key >= 2}
                                                            <div class="col-md-12">
                                                                <div class="form-group row {if in_array('empty_fakthousing', (array)$contacts_error)}has-danger{/if}">
                                                                    <div class="col-md-8">
                                                                        <p class="form-control-static">
                                                                            <strong>
                                                                                {$contactperson->name|escape}
                                                                                ({$contactperson->relation|escape})
                                                                                {$contactperson->phone|escape}
                                                                            </strong>
                                                                            {if $contactperson->phone}
                                                                                <button class="btn waves-effect waves-light btn-xs btn-info js-mango-call" data-phone="{$contactperson->phone|escape}" data-user="{$client->id}" title="Выполнить звонок"><i class="fas fa-phone-square"></i></button>
                                                                                <button
                                                                                        class="btn waves-effect waves-light btn-xs btn-success js-voximplant-call"
                                                                                        data-phone="{$contactperson->phone|escape}">
                                                                                    <i class="fas fa-phone-square"></i>
            
                                                                                </button>
            
                                                                            {/if}
                                                                        </p>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <i>{$contactperson->comment}</i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        {/if}
                                                    {/foreach}
                                                    </div>
                                                </div>
                                            {/if}
                                        </div>

                                        <div class="row edit-block {if !$contacts_error}hide{/if}">
                                            {if $contacts_error}
                                                <div class="col-md-12">
                                                    <ul class="alert alert-danger">
                                                        {if in_array('empty_contact_person_name', (array)$contacts_error)}<li>Укажите ФИО контакного лица!</li>{/if}
                                                        {if in_array('empty_contact_person_phone', (array)$contacts_error)}<li>Укажите тел. контакного лица!</li>{/if}
                                                        {if in_array('empty_contact_person_relation', (array)$contacts_error)}<li>Укажите кем приходится контакное лицо!</li>{/if}
                                                        {if in_array('empty_contact_person2_name', (array)$contacts_error)}<li>Укажите ФИО контакного лица 2!</li>{/if}
                                                        {if in_array('empty_contact_person2_phone', (array)$contacts_error)}<li>Укажите тел. контакного лица 2!</li>{/if}
                                                        {if in_array('empty_contact_person2_relation', (array)$contacts_error)}<li>Укажите кем приходится контакное лицо 2!</li>{/if}
                                                        {if in_array('empty_contact_person3_name', (array)$contacts_error)}<li>Укажите ФИО контакного лица 3!</li>{/if}
                                                        {if in_array('empty_contact_person3_phone', (array)$contacts_error)}<li>Укажите тел. контакного лица 3!</li>{/if}
                                                        {if in_array('empty_contact_person3_relation', (array)$contacts_error)}<li>Укажите кем приходится контакное лицо 3!</li>{/if}
                                                    </ul>
                                                </div>
                                            {/if}

                                            <div class="col-12" id="contactperson_edit_block">

                                                {foreach $contactpersons as $contactperson}
                                                    <div class="row">
                                                        <input type="hidden" name="contact_person_id[]" value="{$contactperson->id}" />
                                                        <div class="col-md-4">
                                                            <div class="form-group {if in_array('empty_contact_person_name', (array)$contacts_error)}has-danger{/if}">
                                                                <label class="control-label">ФИО контакного лица</label>
                                                                <input type="text" class="form-control" name="contact_person_name[]" value="{$contactperson->name|escape}" placeholder="" required="true" />
                                                                {if in_array('empty_contact_person_name', (array)$contacts_error)}<small class="form-control-feedback">Укажите ФИО контакного лица!</small>{/if}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group {if in_array('empty_contact_person_phone', (array)$contacts_error)}has-danger{/if}">
                                                                <label class="control-label">Тел. контакного лица</label>
                                                                <input type="text" class="form-control" name="contact_person_phone[]" value="{$contactperson->phone|escape}" placeholder="" required="true" />
                                                                {if in_array('empty_contact_person_phone', (array)$contacts_error)}<small class="form-control-feedback">Укажите тел. контакного лица!</small>{/if}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group {if in_array('empty_contact_person_relation', (array)$contacts_error)}has-danger{/if}">
                                                                <label class="control-label">Кем приходится</label>
                                                                <select class="form-control custom-select" name="contact_person_relation[]">
                                                                    <option value="" {if $contactperson->relation == ''}selected=""{/if}>Выберите значение</option>
                                                                    <option value="мать/отец" {if $contactperson->relation == 'мать/отец'}selected=""{/if}>мать/отец</option>
                                                                    <option value="муж/жена" {if $contactperson->relation == 'муж/жена'}selected=""{/if}>муж/жена</option>
                                                                    <option value="сын/дочь" {if $contactperson->relation == 'сын/дочь'}selected=""{/if}>сын/дочь</option>
                                                                    <option value="коллега" {if $contactperson->relation == 'коллега'}selected=""{/if}>коллега</option>
                                                                    <option value="друг/сосед" {if $contactperson->relation == 'друг/сосед'}selected=""{/if}>друг/сосед</option>
                                                                    <option value="иной родственник" {if $contactperson->relation == 'иной родственник'}selected=""{/if}>иной родственник</option>
                                                                </select>
                                                                {if in_array('empty_contact_person_relation', (array)$contacts_error)}<small class="form-control-feedback">Укажите кем приходится контакное лицо!</small>{/if}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12 mb-5">
                                                            <div class="form-group {if in_array('empty_contact_person_comment', (array)$contacts_error)}has-danger{/if}">
                                                                <label class="control-label">Комментарий</label>
                                                                <input type="text" class="form-control" name="contact_person_comment[]" value="{$contactperson->comment}" placeholder=""  />
                                                                {if in_array('empty_contact_person_comment', (array)$contacts_error)}<small class="form-control-feedback">Укажите тел. контакного лица!</small>{/if}
                                                            </div>
                                                        </div>
                                                    </div>
                                                {/foreach}

                                                <div class="row js-contactperson-block" id="new_contactperson">
                                                    <input type="hidden" name="contact_person_id[]" value="" />
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label class="control-label">ФИО контакного лица</label>
                                                            <input type="text" class="form-control" name="contact_person_name[]" value="" placeholder="" required="true" />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label class="control-label">Тел. контакного лица</label>
                                                            <input type="text" class="form-control" name="contact_person_phone[]" value="" placeholder="" required="true" />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label class="control-label">Кем приходится</label>
                                                            <select class="form-control custom-select" name="contact_person_relation[]">
                                                                <option value="">Выберите значение</option>
                                                                <option value="мать/отец">мать/отец</option>
                                                                <option value="муж/жена">муж/жена</option>
                                                                <option value="сын/дочь">сын/дочь</option>
                                                                <option value="коллега">коллега</option>
                                                                <option value="друг/сосед">друг/сосед</option>
                                                                <option value="иной родственник">иной родственник</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12 mb-5">
                                                        <div class="form-group">
                                                            <label class="control-label">Комментарий</label>
                                                            <div class="row">
                                                                <div class="col-10">
                                                                    <input type="text" class="form-control" name="contact_person_comment[]" value="" placeholder=""  />
                                                                </div>
                                                                <div class="col-2 ">
                                                                    <label class="control-label">&nbsp; </label>
                                                                    <button class="btn btn-danger js-remove-contactperson"><i class="fas fas fa-times-circle"></i> Удалить</button>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-actions">
                                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                                    <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                                    <button type="submit" class="btn btn-rounded btn-outline-success js-add-contactperson float-right"><i class="fa fa-plus-circle"></i> Добавить</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    <!-- /Контактные лица -->
                                    {/if}

                                    <!-- Данные о работе -->
                                    <form action="{url}" class="js-order-item-form mb-3" id="work_data_form">

                                        <input type="hidden" name="action" value="workdata" />
                                        <input type="hidden" name="user_id" value="{$client->id}" />

                                        <h3 class="box-title">
                                            <a {if empty($pdn_results)}href="javascript:void(0);" class="js-edit-form"{/if}>
                                                <span>Данные о работе</span>
                                            </a>
                                        </h3>
                                        <hr>

                                        <div class="row view-block {if $workdata_error}hide{/if}">
                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <label class="control-label col-md-4">Сфера деятельности:</label>
                                                    <div class="col-md-8">
                                                        <p class="form-control-static">
                                                            {$client->work_scope|escape}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            {if $client->profession}
                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-4">Должность:</label>
                                                        <div class="col-md-8">
                                                            <p class="form-control-static">
                                                                {$client->profession|escape}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/if}
                                            {if $client->work_phone}
                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-4">Рабочий телефон:</label>
                                                        <div class="col-md-8">
                                                            <p class="form-control-static">
                                                                {$client->work_phone|escape}
                                                                <button class="btn waves-effect waves-light btn-xs btn-info js-mango-call" data-phone="{$client->work_phone|escape}" data-user="{$client->id}" title="Выполнить звонок"><i class="fas fa-phone-square"></i></button>
                                                                {if empty($blockcalls)}
                                                                <button
                                                                        class="btn waves-effect waves-light btn-xs btn-success js-voximplant-call"
                                                                        data-phone="{$client->work_phone|escape}">
                                                                    <i class="fas fa-phone-square"></i>

                                                                </button>
                                                                {/if}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/if}
                                            {if $client->workplace}
                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-4">Название организации:</label>
                                                        <div class="col-md-8">
                                                            <p class="form-control-static">
                                                                {$client->workplace|escape}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/if}
                                            {if $client->workdirector_name}
                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-4">ФИО руководителя:</label>
                                                        <div class="col-md-8">
                                                            <p class="form-control-static">
                                                                {$client->workdirector_name|escape}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/if}
                                            {if $manager->role != 'verificator_minus'}
                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <label class="control-label col-md-4">Доход:</label>
                                                    <div class="col-md-8">
                                                        {if empty($pdn_results)}
                                                            <p class="form-control-static">
                                                                {$client->income_base|escape}
                                                            </p>
                                                        {else}
                                                            {foreach $pdn_results as $pdn_result}
                                                                <p class="form-control-static">
                                                                    Заявка № {$pdn_result->order_id}: {$pdn_result->income_base|escape}
                                                                </p>
                                                            {/foreach}
                                                        {/if}
                                                    </div>
                                                </div>
                                            </div>
                                            {/if}
                                        </div>

                                        <div class="row edit-block {if !$workdata_error}hide{/if}">
                                            {if $workdata_error}
                                                <div class="col-md-12">
                                                    <ul class="alert alert-danger">
                                                        {if in_array('empty_work_scope', (array)$workdata_error)}<li>Укажите сферу деятельности!</li>{/if}
                                                        {if in_array('empty_income_base', (array)$workdata_error)}<li>Укажите доход!</li>{/if}
                                                    </ul>
                                                </div>
                                            {/if}
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_income_base', (array)$workdata_error)}has-danger{/if}">
                                                    <label class="control-label">Сфера деятельности</label>
                                                    <input type="text" class="form-control" name="work_scope" value="{$client->work_scope|escape}" placeholder="" required="true" />
                                                    {if in_array('empty_income_base', (array)$workdata_error)}<small class="form-control-feedback">Укажите сферу деятельности!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">Должность</label>
                                                    <input type="text" class="form-control" name="profession" value="{$client->profession|escape}" placeholder="" />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">Рабочий телефон</label>
                                                    <input type="text" class="form-control" name="work_phone" value="{$client->work_phone|escape}" placeholder="" />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">Название организации</label>
                                                    <input type="text" class="form-control" name="workplace" value="{$client->workplace|escape}" placeholder="" />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">ФИО руководителя</label>
                                                    <input type="text" class="form-control" name="workdirector_name" value="{$client->workdirector_name|escape}" placeholder="" />
                                                </div>
                                            </div>
                                            {if $manager->role != 'verificator_minus'}
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_income_base', (array)$workdata_error)}has-danger{/if}">
                                                    <label class="control-label">Доход</label>
                                                    <input type="text" class="form-control" name="income_base" value="{$client->income_base|escape}" placeholder="" required="true" />
                                                    {if in_array('empty_income_base', (array)$workdata_error)}<small class="form-control-feedback">Укажите доход!</small>{/if}
                                                </div>
                                            </div>
                                            {/if}
                                            <div class="col-md-12">
                                                <div class="form-actions">
                                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                                    <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    <!-- /Данные о работе -->

                                    <!-- Рабочий адрес -->
                                    <form action="{url}" class="js-order-item-form mb-3" id="work_address_form">

                                        <input type="hidden" name="action" value="work_address" />
                                        <input type="hidden" name="user_id" value="{$client->id}" />

                                        <h3 class="box-title">
                                            <a href="javascript:void(0);" class="js-edit-form">
                                                <span>Адрес Организации</span>
                                            </a>
                                        </h3>
                                        <hr>

                                        <div class="row view-block {if $workaddress_error}hide{/if}">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <p class="form-control-static">
                                                        {if $client->Workregion}
                                                            <strong>
                                                                {if $client->Workregion}{$client->Workregion|escape},{/if}
                                                                {if $client->Workcity}{$client->Workcity|escape},{/if}
                                                                {if $client->Workstreet}{$client->Workstreet|escape},{/if}
                                                                {if $client->Workhousing}д.{$client->Workhousing|escape},{/if}
                                                                {if $client->Workbuilding}стр. {$client->Workbuilding|escape},{/if}
                                                                {if $client->Workroom}оф.{$client->Workroom|escape}{/if}
                                                            </strong>
                                                        {elseif $client->work_address}
                                                            Адрес 1С: <strong>{$client->work_address|escape}</strong>
                                                        {/if}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row edit-block {if !$workaddress_error}hide{/if}">
                                            {if $workaddress_error}
                                                <div class="col-md-12">
                                                    <ul class="alert alert-danger">
                                                        {if in_array('empty_workregion', (array)$workaddress_error)}<li>Укажите область!</li>{/if}
                                                        {if in_array('empty_workcity', (array)$workaddress_error)}<li>Укажите город!</li>{/if}
                                                        {if in_array('empty_workstreet', (array)$workaddress_error)}<li>Укажите улицу!</li>{/if}
                                                        {if in_array('empty_workhousing', (array)$workaddress_error)}<li>Укажите дом!</li>{/if}
                                                    </ul>
                                                </div>
                                            {/if}
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_workregion', (array)$workaddress_error)}has-danger{/if}">
                                                    <label class="control-label">Область</label>
                                                    <input type="text" class="form-control" name="Workregion" value="{$client->Workregion|escape}" placeholder="" required="true" />
                                                    {if in_array('empty_workregion', (array)$workaddress_error)}<small class="form-control-feedback">Укажите область!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_workcity', (array)$workaddress_error)}has-danger{/if}">
                                                    <label class="control-label">Город</label>
                                                    <input type="text" class="form-control" name="Workcity" value="{$client->Workcity|escape}" placeholder="" required="true" />
                                                    {if in_array('empty_workcity', (array)$workaddress_error)}<small class="form-control-feedback">Укажите город!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_workstreet', (array)$workaddress_error)}has-danger{/if}">
                                                    <label class="control-label">Улица</label>
                                                    <input type="text" class="form-control" name="Workstreet" value="{$client->Workstreet|escape}" placeholder="" />
                                                    {if in_array('empty_workstreet', (array)$workaddress_error)}<small class="form-control-feedback">Укажите улицу!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_workhousing', (array)$workaddress_error)}has-danger{/if}">
                                                    <label class="control-label">Дом</label>
                                                    <input type="text" class="form-control" name="Workhousing" value="{$client->Workhousing|escape}" placeholder="" required="true" />
                                                    {if in_array('empty_workhousing', (array)$workaddress_error)}<small class="form-control-feedback">Укажите дом!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">Строение</label>
                                                    <input type="text" class="form-control" name="Workbuilding" value="{$client->Workbuilding|escape}" placeholder="" />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">Офис</label>
                                                    <input type="text" class="form-control" name="Workroom" value="{$client->Workroom|escape}" placeholder="" required="true" />
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-actions">
                                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                                    <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    <!-- /Рабочий адрес -->

                                    {if $manager->role != 'verificator_minus'}
                                    <form action="{url}" class="js-order-item-form mb-3" method="POST" id="socials_form">

                                        <input type="hidden" name="action" value="socials" />
                                        <input type="hidden" name="user_id" value="{$client->id}" />

                                        <h3 class="box-title">
                                            <a href="javascript:void(0);" class="js-edit-form">
                                                <span>Ссылки на профили в соц. сетях</span>
                                            </a>
                                        </h3>
                                        <hr>

                                        <div class="row view-block {if $socials_error}hide{/if}">
                                            {if $client->social_fb}
                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-4">Facebook:</label>
                                                        <div class="col-md-8">
                                                            <p class="form-control-static">
                                                                {$client->social_fb|escape}
                                                                <a href="{$client->social_fb|escape}" target="_blank" title="Открыть соцсеть"><i class="fas fa-external-link-square-alt"></i></a>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/if}
                                            {if $client->social_inst}
                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-4">Instagram:</label>
                                                        <div class="col-md-8">
                                                            <p class="form-control-static">
                                                                {$client->social_inst|escape}
                                                                <a href="{$client->social_inst|escape}" target="_blank" title="Открыть соцсеть"><i class="fas fa-external-link-square-alt"></i></a>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/if}
                                            {if $client->social_vk}
                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-4">В Контакте:</label>
                                                        <div class="col-md-8">
                                                            <p class="form-control-static">
                                                                {$client->social_vk|escape}
                                                                <a href="{$client->social_vk|escape}" target="_blank" title="Открыть соцсеть"><i class="fas fa-external-link-square-alt"></i></a>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/if}
                                            {if $client->social_ok}
                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-4">Одноклассники:</label>
                                                        <div class="col-md-8">
                                                            <p class="form-control-static">
                                                                {$client->social_ok|escape}
                                                                <a href="{$client->social_ok|escape}" target="_blank" title="Открыть соцсеть"><i class="fas fa-external-link-square-alt"></i></a>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/if}
                                        </div>

                                        <div class="row edit-block {if !$socials_error}hide{/if}">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Facebook</label>
                                                    <input type="text" class="form-control" name="social_fb" value="{$client->social_fb|escape}" placeholder="" />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Instagram</label>
                                                    <input type="text" class="form-control" name="social_inst" value="{$client->social_inst|escape}" placeholder="" />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">В Контакте</label>
                                                    <input type="text" class="form-control" name="social_vk" value="{$client->social_vk|escape}" placeholder="" />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Одноклассники</label>
                                                    <input type="text" class="form-control" name="social_ok" value="{$client->social_ok|escape}" placeholder="" />
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-actions">
                                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                                    <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    {/if}

                                    <!-- -->
                                    <form action="{url}" class="col-md-6 js-order-item-form mb-3 {if !$is_post}js-check-images{/if}" id="images_form" data-user="{$client->id}">

                                        <input type="hidden" name="action" value="images" />
                                        <input type="hidden" name="user_id" value="{$client->id}" />

                                        <h3 class="box-title">
                                            <span>Фотографии</span>
                                            {if !$is_post}
                                                <div class="spinner-border spinner-border-sm m-2 text-info float-right" role="status"></div>
                                            {/if}
                                        </h3>
                                        <hr>

                                        <div class="row view-block {if $socials_error}hide{/if}" >

                                            <ul class="col-md-12 list-inline order-images-list ">
                                                {foreach $files as $file}

                                                    {if $file->visible == 1}

                                                        {if $file->status == 0}
                                                            {$item_class="border-warning"}
                                                            {$ribbon_class="ribbon-warning"}
                                                            {$ribbon_icon="fas fa-clock"}
                                                        {elseif $file->status == 1}
                                                            {$item_class="border-info"}
                                                            {$ribbon_class="ribbon-info"}
                                                            {$ribbon_icon="fas fa-question"}
                                                        {elseif $file->status == 2}
                                                            {$item_class="border-success border border-bg"}
                                                            {$ribbon_class="ribbon-success"}
                                                            {$ribbon_icon="fa fa-check-circle"}
                                                        {elseif $file->status == 3}
                                                            {$item_class="border-danger border"}
                                                            {$ribbon_class="ribbon-danger"}
                                                            {$ribbon_icon="fas fa-times-circle"}
                                                        {/if}
                                                        <li class="ribbon-wrapper border {$item_class} js-image-item" id="file_{$file->id}" data-id="{$file->id}" style="
                                                    height: 151px;
                                                ">
                                                            {*}<div class="ribbon ribbon-sm ribbon-corner {$ribbon_class}"><i class="{$ribbon_icon}"></i></div>{*}
                                                            <a class="js-open-popup-image image-popup-fit-width" data-fancybox="user_image" href="{$front_url}/files/users/{$file->name}">
                                                                <img src="{$file->name|resize:100:100}" loading="lazy" alt="" class="img-responsive js-image-thumb" style="max-width:100px;max-height:100px;" />
                                                            </a>
                                                            <div class="label-exists js-label-exists"></div>



                                                            <div class="overlay-buttons">
                                                                <a href="javascript:void(0);" data-id="{$file->id}" class="js-image-accept btn btn-xs {if $file->status == 2}btn-success{else}btn-outline-success{/if}">
                                                                    <i class="fas fa-check"></i>
                                                                </a>
                                                                <a href="javascript:void(0);" data-id="{$file->id}" class="js-image-reject btn btn-xs {if $file->status == 3}btn-danger{else}btn-outline-danger{/if}">
                                                                    <i class="fas fa-times"></i>
                                                                </a>
                                                            </div>

                                                            <form method="POST" action="{$config->front_url}/ajax/upload_joxi.php">
                                                                <select id="type" name="type" class="label label-primary image-label" placeholder="type..." style="width: 78px;top: 91px;bottom: 1px;padding-left: 0px;padding-right: 0px;left: 1px;height: 19px;border-bottom-width: 0px;padding-bottom: 0px;">

                                                                    <option {if $file->type == 'face1'}selected{/if} value="face1">Профиль</option>
                                                                    <option {if $file->type == 'face2'}selected{/if} value="face2">Анфас</option>
                                                                    <option {if $file->type == 'passport'}selected{/if} value="passport">Документ</option>
                                                                    <option {if $file->type == 'passport1'}selected{/if} value="passport1">Паспорт</option>
                                                                    <option {if $file->type == 'passport2'}selected{/if} value="passport2">Прописка</option>
                                                                    <option {if $file->type == 'passport3'}selected{/if} value="passport3">Брак</option>
                                                                    <option {if $file->type == 'passport4'}selected{/if} value="passport4">Карта</option>
                                                                    <option {if $file->type == 'selfi'}selected{/if} value="selfi">Селфи</option>
                                                                </select>

                                                                <input type="hidden" name="action" value="update">
                                                                <input type="hidden" name="user_id" value="{$client->id}">
                                                                <input type="hidden" name="file_id" value="{$file->id}">

                                                                <input type="hidden" name="token" value="123ighdfgys_dfgd_1">

                                                                <input type="submit" value="Ок" class="label label-primary image-label" style="
                                                                            right: 0px;
                                                                            padding-left: 0px;
                                                                            padding-right: 0px;
                                                                            border-right-width: 0px;
                                                                            left: 82px;
                                                                            width: 27px;
                                                                            top: 91px;
                                                                            bottom: 1px;
                                                                            height: 19px;
                                                                            border-bottom-width: 0px;

                                                                        ">
                                                            </form>
                                                        </li>
                                                    {/if}
                                                {/foreach}
                                            </ul>

                                            <br>
                                            <br>

                                            <h4 class="box-title">
                                                <span>Для внутреннего использования</span>
                                                {if !$is_post}
                                                    <div class="spinner-border spinner-border-sm m-2 text-info float-right" role="status"></div>
                                                {/if}
                                            </h4>
                                            <hr>
                                            <ul class="col-md-12 list-inline order-images-list ">
                                                {foreach $files as $file}

                                                    {if $file->visible == 0}

                                                        {if $file->status == 0}
                                                            {$item_class="border-warning"}
                                                            {$ribbon_class="ribbon-warning"}
                                                            {$ribbon_icon="fas fa-clock"}
                                                        {elseif $file->status == 1}
                                                            {$item_class="border-info"}
                                                            {$ribbon_class="ribbon-info"}
                                                            {$ribbon_icon="fas fa-question"}
                                                        {elseif $file->status == 2}
                                                            {$item_class="border-success border border-bg"}
                                                            {$ribbon_class="ribbon-success"}
                                                            {$ribbon_icon="fa fa-check-circle"}
                                                        {elseif $file->status == 3}
                                                            {$item_class="border-danger border"}
                                                            {$ribbon_class="ribbon-danger"}
                                                            {$ribbon_icon="fas fa-times-circle"}
                                                        {/if}
                                                        <li class="ribbon-wrapper border {$item_class} js-image-item" id="file_{$file->id}" data-id="{$file->id}" style="
                                                    height: 151px;
                                                ">
                                                            {*}<div class="ribbon ribbon-sm ribbon-corner {$ribbon_class}"><i class="{$ribbon_icon}"></i></div>{*}
                                                            <a class="js-open-popup-image image-popup-fit-width" data-fancybox="user_image" href="{$front_url}/files/users/{$file->name}">
                                                                <img src="{$file->name|resize:100:100}" loading="lazy" alt="" class="img-responsive js-image-thumb" style="max-width:100px;max-height:100px;" />
                                                            </a>
                                                            <div class="label-exists js-label-exists"></div>



                                                            <div class="overlay-buttons">
                                                                <a href="javascript:void(0);" data-id="{$file->id}" class="js-image-accept btn btn-xs {if $file->status == 2}btn-success{else}btn-outline-success{/if}">
                                                                    <i class="fas fa-check"></i>
                                                                </a>
                                                                <a href="javascript:void(0);" data-id="{$file->id}" class="js-image-reject btn btn-xs {if $file->status == 3}btn-danger{else}btn-outline-danger{/if}">
                                                                    <i class="fas fa-times"></i>
                                                                </a>
                                                            </div>

                                                            <form method="POST" action="{$config->front_url}/ajax/upload_joxi.php">
                                                                <select id="type" name="type" class="label label-primary image-label" placeholder="type..." style="width: 78px;top: 91px;bottom: 1px;padding-left: 0px;padding-right: 0px;left: 1px;height: 19px;border-bottom-width: 0px;padding-bottom: 0px;">

                                                                    <option {if $file->type == 'face1'}selected{/if} value="face1">Профиль</option>
                                                                    <option {if $file->type == 'face2'}selected{/if} value="face2">Анфас</option>
                                                                    <option {if $file->type == 'passport'}selected{/if} value="passport">Документ</option>
                                                                    <option {if $file->type == 'passport1'}selected{/if} value="passport1">Паспорт</option>
                                                                    <option {if $file->type == 'passport2'}selected{/if} value="passport2">Прописка</option>
                                                                    <option {if $file->type == 'passport3'}selected{/if} value="passport3">Брак</option>
                                                                    <option {if $file->type == 'passport4'}selected{/if} value="passport4">Карта</option>
                                                                    <option {if $file->type == 'selfi'}selected{/if} value="selfi">Селфи</option>
                                                                </select>

                                                                <input type="hidden" name="action" value="update">
                                                                <input type="hidden" name="user_id" value="{$client->id}">
                                                                <input type="hidden" name="file_id" value="{$file->id}">

                                                                <input type="hidden" name="token" value="123ighdfgys_dfgd_1">

                                                                <input type="submit" value="Ок" class="label label-primary image-label" style="
                                                                            right: 0px;
                                                                            padding-left: 0px;
                                                                            padding-right: 0px;
                                                                            border-right-width: 0px;
                                                                            left: 82px;
                                                                            width: 27px;
                                                                            top: 91px;
                                                                            bottom: 1px;
                                                                            height: 19px;
                                                                            border-bottom-width: 0px;

                                                                        ">
                                                            </form>
                                                        </li>
                                                    {/if}
                                                {/foreach}
                                            </ul>


                                        </div>

                                        <div class="row edit-block {if !$images_error}hide{/if}">
                                            {foreach $files as $file}
                                                <div class="col-md-4 col-lg-3 col-xlg-3">
                                                    <div class="card card-body">
                                                        <div class="row">
                                                            <div class="col-md-6 col-lg-4 text-center">
                                                                <a class="js-open-popup-image image-popup-fit-width" href="{$front_url}/files/users/{$file->name}">
                                                                    <img src="{$front_url}/files/users/{$file->name}" alt="" class="img-responsive" />
                                                                </a>
                                                            </div>
                                                            <div class="col-md-6 col-lg-8">
                                                                <div class="form-group">
                                                                    <label class="control-label">Статус</label>
                                                                    <select id="status_{$file->id}" class="form-control custom-select js-file-status" name="status[{$file->id}]">
                                                                        <option value="0" {if $file->status == 0}selected="true"{/if}>Новый</option>
                                                                        <option value="1" {if $file->status == 1}selected="true"{/if}>На проверке</option>
                                                                        <option value="2" {if $file->status == 2}selected="true"{/if}>Принят</option>
                                                                        <option value="3" {if $file->status == 3}selected="true"{/if}>Отклонен</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/foreach}
                                            <div class="col-md-12">
                                                <div class="form-actions">
                                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                                    <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>


                                    <form method="POST" action="{$config->front_url}/ajax/upload_joxi.php" class="row">
                                        <br>


                                        <input type="text" id="file_url" name="file_url" class="form-control col-md-2" placeholder="Вставьте в поле ссылку Joxi" style="
                                        margin: 0px 10px 10px 20px;
                                    "><br><br>


                                        <select id="type" name="type" class="form-control col-md-2" placeholder="type...">
                                            <option selected>выберите тип...</option>

                                            <option value="face1">Профиль</option>
                                            <option value="face2">Анфас</option>
                                            <option value="passport">Документ</option>
                                            <option value="passport1">Паспорт</option>
                                            <option value="passport2">Прописка</option>
                                            <option value="passport3">Брак</option>
                                            <option value="passport4">Карта</option>
                                            <option value="selfi">Селфи</option>
                                        </select><br><br>

                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="user_id" value="{$client->id}">
                                        <input type="hidden" name="token" value="123ighdfgys_dfgd_1">

                                        <input type="submit" value="Добавить" class="btn btn-large btn-primary col-md-1" style="
                                        height: 36px;
                                        left: 10px;
                                        width: 226px;
                                    ">
                                    </form>
                                </div>
                                <div class="row mt-4 d-flex">
                                    <div class="col-xs-12 col-md-3 align-self-end">
                                        {if !$client->blocked }
                                            <button data-user="{$client->id}" data-state="1" type="button" class="btn btn-large btn-danger js-block-user">Удалить/Заблокировать ЛК</button>
                                        {else}
                                            <div class="text-danger">ЛК удален/заблокирован!</div>
                                            <button data-user="{$client->id}" data-state="0" type="button" class="btn btn-large btn-primary js-block-user mt-2">Вернуть/Разблокировать ЛК</button>
                                        {/if}

                                        {if $stop_list}
                                            <button style="margin-top: 10px" data-user="{$client->id}" class="btn btn-large btn-danger js-stoplist-user">Удалить из стоп-листа</button>
                                        {/if}
                                    </div>
                                    <div>
                                        Статус по звонку
                                        <select id="callStatus" name="call_status" class="form-control js-save-client-select">
                                            <option {if null === $client->call_status}selected{/if} hidden>выберите статус...</option>

                                            {foreach Users::CALL_STATUS_MAP as $status_value => $status_name}
                                                <option value="{$status_value}" {if $status_value == $client->call_status }selected{/if}>{$status_name}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                    <div>
                                        Клиент продолжит оформлять
                                        <select id="continueOder" name="continue_order" class="form-control js-save-client-select">
                                            <option {if null === $client->continue_order}selected{/if} hidden>выберите вариант...</option>

                                            {foreach Users::CONTINUE_ORDER_MAP as $continue_order_value => $continue_order_name}
                                                <option value="{$continue_order_value}" {if $continue_order_value == $client->call_status }selected{/if}>{$continue_order_name}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        {if $client_data['disable_approved_order_calls']}
                                            <button id="toggleAutoCall" class="btn btn-success" data-user-id="{$client->id}">Включить звонки по одобренным заявкам</button>
                                        {else}
                                            <button id="toggleAutoCall" class="btn btn-danger" data-user-id="{$client->id}">Отключить звонки по одобренным заявкам</button>
                                        {/if}
                                    </div>
                                </div>

                                {if $manager->role != 'verificator_minus'}
                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <button class="btn btn-danger"
                                                data-target="#leaveComplaint" data-toggle="modal" type="button">
                                            Оставить жалобу
                                        </button>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <button id="toggle_show_docs" data-user-id="{$client->id}" class="btn btn-primary">
                                            {if empty($user_data['show_extra_docs']) || $user_data['show_extra_docs'] == 0}
                                                Показать доп. документы
                                            {else}
                                                Скрыть доп. документы
                                            {/if}
                                        </button>
                                    </div>
                                </div>
                                {/if}

                                {if $client_data['is_rejected_nk'] && $manager->role != 'verificator_minus'}
                                    <div class="row mt-4">
                                        <div class="col-md-12">
                                            <button id="unlockRejectedNk" class="btn btn-danger" type="button" data-user-id="{$client->id}">
                                                Разблокировать проданного отказного НК (Bonon)
                                            </button>
                                        </div>
                                    </div>
                                {/if}

                                <div class="my-2">
                                    <div class="js-order-item-form mb-3">
                                        <p>
                                            <a data-toggle="collapse" data-target="#collapseHistoryUserCabinet" href="#collapseHistoryUserCabinet" aria-expanded="false" aria-controls="collapseHistoryUserCabinet">
                                                <span>Просмотреть историю действий с ЛК</span>
                                            </a>
                                        </p>
                                        <div class="collapse" id="collapseHistoryUserCabinet">
                                            <table class="table table-sm bg-dark">
                                                <thead>
                                                    <tr>
                                                        <th>Дата</th>
                                                        <th>Тип операции</th>
                                                        <th>Ответственный</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {if $history_deleted_user_cabinet}
                                                        {foreach $history_deleted_user_cabinet as $history_values}
                                                            <tr>
                                                                {foreach $history_values as $history_value}
                                                                    <td>{$history_value}</td>
                                                                {/foreach}
                                                            </tr>
                                                        {/foreach}
                                                    {else}
                                                        <tr>
                                                            <td colspan="3">
                                                                <h3 class="text-warning">Данные не найдены</h3>
                                                            </td>
                                                        </tr>
                                                    {/if}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <h4 class="box-title mt-3">
                                    <span>Оставить коментарий</span>

                                </h4>

                                <form method="POST" id="form_add_comment_client_page" action="order/{$order->order_id}">

                                    <input type="hidden" name="order_id" value="{$order->order_id}" />
                                    <input type="hidden" name="user_id" value="{$client->id}" />
                                    <input type="hidden" name="block" value="missing" />
                                    <input type="hidden" name="action" value="add_comment" />
                                    <div class="alert" style="display:none"></div>

                                    <div class="form-group">
                                        <textarea class="form-control" name="text"></textarea>
                                    </div>
                                    <div class="form-action">
                                        <button type="submit" class="btn btn-success waves-effect waves-light">Добавить коментарий</button>
                                    </div>
                                </form>


                            </div>

                        </div>
                        <!-- Column -->
                    </div>
                </div>
            </div>

            {if $manager->role != 'verificator_minus'}
            <div id="scorings" class="tab-pane" role="tabpanel">
                <div data-order="{$order->order_id}" class="js-scorings-block {if $need_update_scorings}js-need-update{/if}" >
                    <h3 class="box-title mt-5">
                        <span>Скоринг тесты</span>
                    </h3>
                    <hr>
                    <div class="row" id="scorings">
                        <div class="col-md-12">
                            <table class="table">
                                <tr>
                                    <th>Тип</th>
                                    <th>Дата</th>
                                    <th>Статус</th>
                                    <th>Результат</th>
                                    <th></th>
                                    {*}<th></th>{*}
                                </tr>

                                {foreach $scorings as $scoring}
                                    {if $scoring->type->name != 'svo' && ($scoring->type->name != 'work' || $manager->role != 'verificator_minus')}
                                    <tr>
                                        <td>
                                            {if in_array($scoring->type->name, ['fssp', 'axilink', 'scorista', 'juicescore'])}
                                                <a href="#" data-toggle="collapse" data-target="#scoring_{$scoring->id}">{$scoring->type->title}</a>
                                            {else}
                                                {$scoring->type->title}
                                            {/if}
                                        </td>
                                        <td>
                                            {if $scoring->type->name == 'scorista'}
                                                {if $scoring->status_name == 'completed'}
                                                    {$scoring->end_date|date} {$scoring->end_date|time}
                                                {elseif $scoring->status_name == 'stopped'}
                                                    {if $scoring->end_date}
                                                        {$scoring->end_date|date} {$scoring->end_date|time}
                                                    {else}
                                                        {$scoring->start_date|date} {$scoring->start_date|time}
                                                    {/if}
                                                {elseif $scoring->status_name == 'error'}
                                                    {$scoring->start_date|date} {$scoring->start_date|time}
                                                {/if}
                                            {else}
                                                {if $scoring->created}
                                                    {$scoring->created|date} {$scoring->created|time}
                                                {/if}
                                            {/if}
                                        </td>
                                        <td>
                                            {if !$scoring}
                                                <span class="label label-warning">Не проводился</span>
                                            {elseif $scoring->status_name == 'new'}
                                                <span class="label label-info" title="Скоринг находится в очереди на выполнение">Ожидание</span>
                                            {elseif $scoring->status_name == 'import'}
                                                <span class="label label-info" title="Скоринг импортируется из 1C">Импорт</span>
                                            {elseif $scoring->status_name == 'process'}
                                                <span class="label label-primary">Выполняется</span>
                                            {elseif $scoring->status_name == 'error'}
                                                <span class="label label-danger">Ошибка</span>
                                            {elseif $scoring->status_name == 'completed'}
                                                <span class="label label-success">Завершен</span>
                                            {/if}

                                        </td>
                                        <td>
                                            {if $scoring->status_name == 'completed'}
                                                {if $scoring->success}<span class="label label-success">Пройден</span>
                                                {else}<span class="label label-danger">Не пройден</span>{/if}
                                            {/if}
                                        </td>
                                        <td>
                                            {if $scoring->type->name == 'scorista'}
                                                {if $scoring->status_name == 'completed'}
                                                    {if $scoring->success}
                                                        <span class="label label-success">{$scoring->scorista_ball}</span>
                                                    {else}
                                                        <span class="label label-danger">{$scoring->scorista_ball}</span>
                                                    {/if}

                                                    {if $scoring->body->decision->decisionName == 'Отказ'}
                                                        <span class="label label-danger">{$scoring->body->decision->decisionName}</span>
                                                    {elseif $scoring->body->decision->decisionName}
                                                        <span class="label label-info">{$scoring->body->decision->decisionName}</span>
                                                    {/if}

                                                    {*if $scoring->type->params['scorebal_nocall'] <= $scoring->scorista_ball}
                                                        <p class="p-0 m-0 text-success"><small>Можно не звонить клиенту</small></p>
                                                    {/if*}
                                                {else}
                                                    <small>{$scoring->string_result}</small>
                                                {/if}
                                            {elseif $scoring->type->name == 'juicescore'}
                                                {if $scoring->body}
                                                    {if $scoring->success}
                                                        <span class="label label-success">{$scoring->body['AntiFraud score']}</span>
                                                    {else}
                                                        <span class="label label-danger">{$scoring->body['AntiFraud score']}</span>
                                                    {/if}
                                                {/if}
                                            {else}
                                                <small>{$scoring->string_result|escape}</small>
                                            {/if}

                                        </td>
                                        {*}
                                        <td>
                                            {if $scoring->status_name == 'new' || $scoring->status_name == 'process'}
                                            <a class="float-right btn btn-xs btn-outline-primary btn-rounded ">Выполняется</a>
                                            {else}
                                            <a class="float-right btn btn-xs btn-outline-success btn-rounded js-run-scorings" href="javascript:void(0);" data-order="{$order->order_id}" data-type="{$scoring_type->name}">Запустить</a>
                                            {/if}

                                        </td>
                                        {*}
                                    </tr>

                                    {if $scoring->type->name == 'fssp'}
                                        <tr class="collapse" id="scoring_{$scoring->id}">
                                            <td colspan="5">
                                                {if $scoring->body && $scoring->body->result[0]->result|count > 0}
                                                    <ul>
                                                        {foreach $scoring->body->result as $key => $value}
                                                            <li>
                                                                <ul>
                                                                    {foreach $value->result as $kk =>  $item}
                                                                        <li>
                                                                            <p>{$item->name}</p>
                                                                            <p>{$item->exe_production}</p>
                                                                            <p>{$item->details}</p>
                                                                            <p>{$item->subject}</p>
                                                                            <p>{$item->department}</p>
                                                                            <p>{$item->bailiff}</p>
                                                                            <p>{$item->ip_end}</p>
                                                                        </li>
                                                                    {/foreach}
                                                                </ul>
                                                            </li>
                                                        {/foreach}
                                                    </ul>
                                                {else}
                                                    Производства не найдены
                                                {/if}
                                            </td>
                                        </tr>
                                    {/if}


                                    {if $scoring->type->name == 'scorista'}
                                        <tr class="collapse" id="scoring_{$scoring->id}">
                                            <td colspan="6">
                                                {if $scoring->status_name == 'error'}
                                                    <pre class="text-white">{$scoring->body|var_dump}</pre>
                                                {elseif $scoring->status_name == 'completed'}
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <p class="text-info m-0">Рекомендуемое решение: {$scoring->body->decision->decisionName}</p>
                                                            <p class="text-info m-0">Рекомендуемая сумма: {if $scoring->body->additional->decisionSum}{$scoring->body->additional->decisionSum}{else}Нет{/if}</p>
                                                            <p class="text-info">Рекомендуемый период: {if $scoring->body->additional->decisionPeriod}{$scoring->body->additional->decisionPeriod}{else}Нет{/if}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            {if $scoring->body->additional->decisionMessage}
                                                                <p class="box bg-primary m-0">{$scoring->body->additional->decisionMessage}</p>
                                                            {/if}
                                                        </div>
                                                    </div>

                                                    <ul>
                                                        {if $scoring->body}
                                                            {foreach $scoring->body as $key => $value}
                                                                <li>
                                                                    {$key}
                                                                    <ul>
                                                                        {foreach $value as $kk =>  $item}
                                                                            {if $item->description}
                                                                                <li>
                                                                                    {if is_object($item->result)}
                                                                                        {$kk}<br />
                                                                                        {foreach $item->result as $k => $v}
                                                                                            {$k}: {$v}<br />
                                                                                        {/foreach}
                                                                                    {else}
                                                                                        {if $item->result > 0}
                                                                                            <span
                                                                                            {if in_array($item->description, $stopfactorsImportant)}
                                                                                                class="text-danger"
                                                                                            {else}
                                                                                                class="text-info"
                                                                                            {/if}
                                                                                            >
                                                                                        {/if}
                                                                                        <strong>{$item->description}</strong>:
                                                                                        {if is_null($item->result)}-
                                                                                        {else}
                                                                                            {$item->result}
                                                                                        {/if}
                                                                                        {if $item->result > 0}
                                                                                            </span>
                                                                                        {/if}
                                                                                    {/if}
                                                                                </li>
                                                                            {/if}
                                                                        {/foreach}
                                                                    </ul>
                                                                </li>
                                                            {/foreach}
                                                        {/if}
                                                    </ul>
                                                {/if}
                                            </td>
                                        </tr>
                                    {/if}

                                    {if $scoring->type->name == 'juicescore'}
                                        <tr class="collapse" id="scoring_{$scoring->id}">
                                            <td colspan="6">

                                                <ul>
                                                    {if $scoring->body}
                                                        {foreach $scoring->body as $key => $item}
                                                            {if $key == 'Predictors'}
                                                                <li>
                                                                    <p>{$key}</p>
                                                                    <ul>
                                                                        {foreach $item as $pkey => $pitem}
                                                                            <li>{$pkey}: {$pitem}</li>
                                                                        {/foreach}
                                                                    </ul>
                                                                </li>
                                                            {elseif is_object($item)}
                                                                <li><span class="label-danger">{$scoring->string_result}</span></li>
                                                            {else}
                                                                <li>{$key}: {$item}</li>
                                                            {/if}
                                                        {/foreach}
                                                    {/if}
                                                </ul>
                                            </td>
                                        </tr>
                                    {/if}

                                    {if $scoring->type->name == 'axilink'}
                                        <tr class="collapse" id="scoring_{$scoring->id}">
                                            <td colspan="6">
                                                {if $scoring->status_name == 'error'}
                                                    <pre class="text-white">{$scoring->string_result}</pre>
                                                {elseif $scoring->status_name == 'completed'}
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <p class="text-info m-0">Рекомендуемое решение: {if !empty($scoring->body->name)}{$scoring->body->name}{else}Нет{/if}</p>
                                                            <p class="text-info">Рекомендуемая сумма: {if !empty($scoring->body->sum)}{$scoring->body->sum}{else}Нет{/if}</p>
                                                            <p class="text-info">Рекомендуемый период: {if !empty($scoring->body->limit_period)}{$scoring->body->limit_period}{else}Нет{/if}</p>
                                                            <p class="text-info">Балл: {$scoring->scorista_ball}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            {if $scoring->body->message}
                                                                <p class="box bg-primary m-0">{$scoring->body->message}</p>
                                                            {/if}
                                                        </div>
                                                    </div>
                                                {/if}
                                            </td>
                                        </tr>
                                    {/if}
                                    {/if}
                                {/foreach}
                            </table>

                        </div>
                    </div>
                </div>
            </div>
            {/if}
            {if $manager->role != 'verificator_minus'}
                <div id="comments" class="tab-pane" role="tabpanel">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    {if $blacklist}
                                        <h3>ЧС</h3>
                                        <table class="table">
                                            <tr>
                                                <th>Дата</th>
                                                <th>Ответственный</th>
                                                <th>Комментарий</th>
                                            </tr>
                                            {if !is_array($blacklist)}
                                                {$blacklist = [$blacklist]}
                                            {/if}
                                            {foreach $blacklist as $record}
                                                {if $record}
                                                    <tr class="text-danger">
                                                        <td>{$record->created_date|date} {$record->created_date|time}</td>
                                                        <td>{$record->name_1c|escape}</td>
                                                        <td>{$record->comment|nl2br}</td>
                                                    </tr>
                                                {/if}
                                            {/foreach}
                                        </table>
                                    {/if}

                                    {if !$comments && !$comments_1c && !$blacklist}
                                        <h4>Нет комментариев</h4>
                                    {/if}

                                    {if $comments}
                                        <h4>Комментарии CRM</h4>
                                        <table class="table" id="table__comments">
                                            <tr>
                                                <th>Дата</th>
                                                <th>Заявка</th>
                                                <th>Менеджер</th>
                                                <th>Блок</th>
                                                <th>Комментарий</th>
                                            </tr>
                                            {foreach $comments as $comment}
                                                {if $comment->block === 'incomingCall'}
                                                    <tr>
                                                        <td>{$comment->created|date} {$comment->created|time}</td>
                                                        <td>
                                                            <a href="order/{$comment->order_id}">{$comment->order_id}</a>
                                                        </td>
                                                        <td>{$managers[$comment->manager_id]->name|escape}</td>
                                                        <td>{$comment_blocks[$comment->block]}</td>
                                                        <td>
                                                            {assign var="callResult" value=$comment->text|json_decode:1}

                                                            Тег: {$callResult.operator_tag} <br>
                                                            Выбрал меню: {$callResult.tag} <br>
                                                            Стадия: {$callResult.stage} <br>
                                                            Клиент в черном списке: {$callResult.blacklisted} <br>

                                                            Кто обработал звонок:
                                                            {if $callResult.handled_by === 'aviar'}
                                                                Aviar <br>
                                                            {elseif $callResult.handled_by === 'operator'}
                                                                {$callResult.operator_name} <br>
                                                                Оценка клиента: {$callResult.assessment} <br>
                                                            {elseif $callResult.handled_by === 'missed'}
                                                                Пропущен <br>
                                                            {/if}
                                                            Запись звонка: <audio controls src="{$callResult.record_url}" style="margin-top: 5px">
                                                                Ваш браузер не поддерживает воспроизведение аудио. Вот ссылка на запись: <a href="{$callResult.record_url}">Скачать</a>.
                                                            </audio>
                                                        </td>
                                                    </tr>
                                                {elseif $comment->block === 'fromtechIncomingCall'}
                                                    <tr>
                                                        <td>{$comment->created|date} {$comment->created|time}</td>
                                                        <td>
                                                            <a href="order/{$comment->order_id}">{$comment->order_id}</a>
                                                        </td>
                                                        <td>{$managers[$comment->manager_id]->name|escape}</td>
                                                        <td>{$comment_blocks[$comment->block]}</td>
                                                        <td class="transcript-cell">
                                                            {assign var="callResult" value=$comment->text|json_decode:1}

                                                            <strong>Звонок от Fromtech AI</strong><br>

                                                            {if $callResult.call_transcript}
                                                                <strong>Транскрипция:</strong><br>
                                                                <div class="alert alert-info mb-2 transcript-wrapper">
                                                                    <div class="transcript-preview collapsed">
                                                                        {$callResult.call_transcript|nl2br}
                                                                    </div>
                                                                    <a href="javascript:void(0);" class="toggle-transcript btn btn-sm btn-link mt-1">Показать полностью</a>
                                                                </div>
                                                            {/if}

                                                            <strong>Длительность:</strong> {$callResult.duration} сек.
                                                            {if $callResult.bot_duration}
                                                                (бот: {$callResult.bot_duration} сек.)<br>
                                                            {/if}

                                                            <strong>Статус клиента:</strong>
                                                            {if $callResult.client == 'known'}
                                                                <span class="badge badge-success">Известный</span>
                                                            {else}
                                                                <span class="badge badge-warning">Неизвестный</span>
                                                            {/if}
                                                            <br>

                                                            {if $callResult.call_record}
                                                                <strong>Запись звонка:</strong><br>
                                                                <audio controls src="{$callResult.call_record}" style="margin-top: 5px">
                                                                    Ваш браузер не поддерживает воспроизведение аудио. Вот ссылка на запись: <a href="{$callResult.call_record}">Скачать</a>.
                                                                </audio>
                                                            {/if}

                                                            {if $callResult.call_log}
                                                                <br><a href="{$callResult.call_log}" target="_blank" class="btn btn-sm btn-info mt-2">Лог звонка</a>
                                                            {/if}

                                                            {if $callResult.dialog_log}
                                                                <a href="{$callResult.dialog_log}" target="_blank" class="btn btn-sm btn-info mt-2">Лог диалога</a>
                                                            {/if}

                                                            {assign var=sto_label value=($callResult.switch_to_operator === true || $callResult.switch_to_operator|lower == 'true') ? 'Да' : 'Нет'}
                                                            <div class="mt-2"><strong>Перевод на оператора:</strong> {$sto_label}</div>
                                                        </td>
                                                    </tr>
                                                {else}
                                                    <tr>
                                                        <td>{$comment->created|date} {$comment->created|time}</td>
                                                        <td>
                                                            <a href="order/{$comment->order_id}">{$comment->order_id}</a>
                                                        </td>
                                                        <td>{$managers[$comment->manager_id]->name|escape}</td>
                                                        <td>{$comment_blocks[$comment->block]}</td>
                                                        <td>{$comment->text|make_urls_clickable|nl2br}</td>
                                                    </tr>
                                                {/if}
                                            {/foreach}
                                        </table>
                                    {else}
                                        <h4>Комментарии CRM</h4>
                                        <table class="table d-none" id="table__comments">
                                            <tr>
                                                <th>Дата</th>
                                                <th>Заявка</th>
                                                <th>Менеджер</th>
                                                <th>Блок</th>
                                                <th>Комментарий</th>
                                            </tr>
                                        </table>
                                    {/if}
                                    {if $comments_1c}
                                        <h3>Комментарии из 1С</h3>
                                        <table class="table">
                                            <tr>
                                                <th>Дата</th>
                                                <th>Блок</th>
                                                <th>Комментарий</th>
                                            </tr>
                                            {foreach $comments_1c as $comment}
                                                <tr>
                                                    <td style="box-shadow: inset 5px 0px 0px 0px rgb{$comment->color}">{$comment->created|date} {$comment->created|time}</td>
                                                    <td>{$comment->block|escape}</td>
                                                    <td>{$comment->text|make_urls_clickable|nl2br}</td>
                                                </tr>
                                            {/foreach}
                                        </table>
                                    {/if}

                                    {if !$comments && !$comments_1c}
                                        <h4>Нет комментариев</h4>
                                    {/if}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="history" class="tab-pane" role="tabpanel">
                    <div class="row">
                        <div class="col-12">
                            <ul class="nav nav-pills mt-4 mb-4">
                                <li class=" nav-item"> <a href="#navpills-orders" class="nav-link active" data-toggle="tab" aria-expanded="false">Заявки</a> </li>
                                <li class="nav-item"> <a href="#navpills-loans" class="nav-link" data-toggle="tab" aria-expanded="false">Кредиты</a> </li>
                            </ul>
                            <div class="tab-content br-n pn">
                                <div id="navpills-orders" class="tab-pane active">
                                    <div class="card">
                                        <div class="card-body">
                                            <table class="table">
                                                <tr>
                                                    <th>Номер</th>
                                                    <th>Номер 1С</th>
                                                    <th>Дата</th>
                                                    <th class="text-center">Сумма</th>
                                                    <th class="text-center">Период</th>
                                                    <th class="text-right">Статус 1С</th>
                                                </tr>
                                                {foreach $orders as $order}
                                                    <tr>
                                                        <td>
                                                            <a href="order/{$order->order_id}" target="_blank">{$order->order_id}</a>
                                                        </td>
                                                        <td>
                                                            {$order->id_1c}
                                                        </td>
                                                        <td>{$order->date|date} {$order->date|time}</td>
                                                        <td class="text-center">{$order->amount}</td>
                                                        <td class="text-center">{$order->period}</td>
                                                        <td class="text-right">{$order->status_1c}</td>
                                                    </tr>
                                                {/foreach}
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div id="navpills-loans" class="tab-pane">
                                    <div class="card">
                                        <div class="card-body">
                                            {if $client->loan_history|count > 0}
                                                <table class="table">
                                                    <tr>
                                                        <th>Договор</th>
                                                        <th>Дата</th>
                                                        <th>Подписано соглашение</th>
                                                        <th>Просрочка</th>
                                                        <th>Ответственный</th>
                                                        <th class="text-right">Статус</th>
                                                        <th class="text-center">Сумма</th>
                                                        <th class="text-center">Остаток ОД</th>
                                                        <th class="text-right">Остаток процентов</th>
                                                        <th>&nbsp;</th>
                                                    </tr>
                                                    {foreach $client->loan_history as $loan_history_item}
                                                        <tr>
                                                            <td>
                                                                {$loan_history_item->number}
                                                            </td>
                                                            <td>
                                                                {$loan_history_item->date|date}
                                                            </td>
                                                            <td>
{*                                                                <div class="onoffswitch">*}
{*                                                                    <input type="checkbox" name="" class="onoffswitch-checkbox input-asp" value="1" id="loan_{$loan_history_item->number}" {if !empty($loan_history_item->asp)}checked{/if} {if empty($loan_history_item->asp)}readonly{/if}>*}
{*                                                                    <label class="onoffswitch-label" for="loan_{$loan_history_item->number}">*}
{*                                                                        <span class="onoffswitch-inner"></span>*}
{*                                                                        <span class="onoffswitch-switch"></span>*}
{*                                                                    </label>*}
{*                                                                </div>*}
                                                                <span class="mr-1 span-asp-{$loan_history_item->number}">{if !empty($loan_history_item->asp) } Активно  {else} Неактивно{/if}</span>
                                                                {if !empty($loan_history_item->asp)}<button class="btn btn-danger off-asp-button" data-toggle="modal" data-target="#off-asp" value="{$loan_history_item->number}">Выкл </button>{/if}
                                                            </td>
                                                            <td>{$loan_history_item->days_overdue}</td>
                                                            <td>{$loan_history_item->responsible_collector}</td>
                                                            <td class="text-right">
                                                                {if $loan_history_item->loan_percents_summ > 0 || $loan_history_item->loan_body_summ > 0}
                                                                    <span class="label label-success">Активный</span>
                                                                {else}
                                                                    <span class="label label-danger">Закрыт</span>
                                                                {/if}
                                                            </td>
                                                            <td class="text-center">{$loan_history_item->amount}</td>
                                                            <td class="text-center">{$loan_history_item->loan_body_summ}</td>
                                                            <td class="text-right">{$loan_history_item->loan_percents_summ}</td>
                                                            <td>
                                                                <button type="button" class="btn btn-xs btn-info js-get-movements" data-number="{$loan_history_item->number}">Операции</button>
                                                            </td>
                                                        </tr>
                                                    {/foreach}
                                                </table>
                                            {else}
                                                <h4>Нет кредитов</h4>
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                            </div>




                        </div>
                    </div>
                </div>
            {/if}

            <div id="calls" class="tab-pane" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

                                {if $calls}
                                    <table class="table">
                                        <tr>
                                            <th>Дата</th>
                                            <th>Направление</th>
                                            <th>Продолжительность</th>
                                            <th></th>
                                        </tr>
                                        {foreach $calls as $call}
                                            <tr>
                                                <td>
                                                    {$call->date|date}
                                                    {$call->date|time}
                                                </td>
                                                <td>
                                                    {if $call->from_number == $client->phone_mobile}
                                                        Исходящий
                                                    {else}
                                                        Входящий
                                                    {/if}

                                                    {if $call->record_file}
                                                        <button class="btn text-info" onclick="window.open('{$config->root_url}/files/calls/{$call->record_file}', 'Запись звонка', 'width=600,height=400')">
                                                            <i class="fas fa-play-circle"></i>
                                                        </button>
                                                    {/if}
                                                </td>
                                                <td>
                                                    {$call->duration}c
                                                </td>
                                                <td>


                                                </td>
                                            </tr>
                                        {/foreach}
                                    </table>
                                {else}
                                    <h3>Звонки не найдены</h3>
                                {/if}

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {include 'sms_list.tpl'}

            
            <div id="exitpools" class="tab-pane" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        {if $questions}
                            <div class="card">
                                <div class="card-body">
                                    <table class="table table-stripped">
                                        {foreach $questions as $question}
                                            <tr>
                                                <td>{$question->question}</td>
                                                <td>{$question->response}</td>
                                            </tr>
                                        {/foreach}
                                    </table>
                                </div>
                            </div>
                        {/if}

                        {if $payment_exitpools}
                            <div class="card">
                                <div class="card-body">
                                    <h3>Причина просрочки</h3>
                                    <table class="table table-stripped">
                                        {foreach $payment_exitpools as $pe}
                                            <tr>
                                                <td>{$pe->created}</td>
                                                <td>{$pe->response}</td>
                                            </tr>
                                        {/foreach}
                                    </table>
                                </div>
                            </div>
                        {/if}

                        {if !$questions && !$payment_exitpools}
                            <h3>Опросы не найдены</h3>
                        {/if}
                    </div>
                </div>
            </div>

            <div id="duplicates" class="tab-pane" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        <div class="tab-content br-n pn">
                            <div id="navpills-orders" class="tab-pane active">
                                <div class="card">
                                    <div class="card-body">
                                        {if $userDuplicates}
                                            <table class="table">
                                                <tr>
                                                    <th>Профиль</th>
                                                    <th>Совпадение</th>
                                                </tr>
                                                {foreach $userDuplicates as $userId => $duplicates}
                                                    {foreach $duplicates as $duplicate}
                                                        <tr>
                                                            <td><a href="/client/{$userId}">{$userId}</a></td>
                                                            <td>{$duplicate}</td>
                                                        </tr>
                                                    {/foreach}
                                                {/foreach}
                                            </table>
                                        {/if}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="tickets" class="tab-pane" role="tabpanel">
                <div id="tab_tickets_container">
                    <div class="text-center p-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Загрузка тикетов...</span>
                        </div>
                        <p>Загрузка тикетов...</p>
                    </div>
                </div>
            </div>
            {$userId = $client->id}

        </div>
    </div>
    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->


</div>

<div id="modal_blacklist" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog"
     aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Добавления в ЧС</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="add_blacklist" action="{url}">
                    <input type="hidden" name="action" value="blacklist"/>

                    <div class="btn-group btn-block mb-2">
                        <div class="mb-3">
                            <select required class="form-control" name="reason">
                                <option value="">Причина добавления в ЧС</option>
                                {foreach $blacklistReasons as $item}
                                    <option value="{$item}">{$item}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="control-label text-white">Комментарий</label>
                        <input type="text" name="comment" class="form-control js-perspective" value=""/>
                    </div>
                    <div class="form-action">
                        <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success waves-effect waves-light">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="loan_operations" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loan_operations_title">Операции по договору</h5>
                <button type="button" class="btn-close btn" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times text-white"></i>
                </button>
            </div>
            <div class="modal-body">
            </div>
        </div>
    </div>
</div>

<div id="modal_send_sms" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Отправить смс-сообщение? <span class="text-themecolor">{$client->site_id}</span></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">


                <div class="card">
                    <div class="card-body">

                        <div class="tab-content tabcontent-border p-3" id="myTabContent">
                            <div role="tabpanel" class="tab-pane fade active show" id="waiting_reason" aria-labelledby="home-tab">
                                <form class="js-sms-form">
                                    <input type="hidden" name="user_id" value="" />
                                    <input type="hidden" name="action" value="send_sms" />
                                    <input type="hidden" name="type" value="sms" />
                                    <div class="form-group">
                                        <label for="name" class="control-label">Выберите шаблон сообщения:</label>
                                        <select name="template_id" class="form-control">
                                            {foreach $sms_templates as $sms_template}
                                                <option value="{$sms_template->id}" title="{$sms_template->template|escape}">{$sms_template->name|escape}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                    <div class="form-action clearfix">
                                        <div class="row">
                                            <div class="col-3">
                                                <button type="button" class="btn btn-danger float-left waves-effect" data-dismiss="modal">Отменить</button>
                                            </div>
                                            <div class="col-9 text-right">
                                                <button type="button" class="mr-1 btn btn-info waves-effect waves-light js-send-sms">СМС</button>

                                                <button type="button" class="mr-1 btn btn-primary waves-effect waves-light js-send-viber">Viber</button>

                                                {*  <button type="button" class="btn btn-success waves-effect waves-light js-send-whatsapp">Whatsapp</button> *}
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="paymentModal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" style="text-align: center; font-size: 18px; color: white">
                <form method="POST" id="add_payment" action="{url}">
                    <input type="hidden" name="action" value="add_payment">
                    <div class="col-md-6">
                        <label>Договор</label>
                        <select name="orderId" class="form-control mb-2" id="orderSelect" required>
                            {foreach $orders as $o}
                                <option value="{$o->order_id}">{$o->order_id}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="paymentDate">Дата платежа <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="paymentDate" name="payment_date" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="margin: auto">
                    <button type="submit"  class="btn btn btn-success add-payment">Выбрать</button>
                    <button type="button" class="btn btn btn-danger" data-dismiss="modal">Отмена</button>
                </div>
            </form>
        </div>

    </div>
</div>

<div class="modal fade" id="smsModal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" style="text-align: center; font-size: 18px; color: white">
                <select class="form-control mb-2" id="orderSelect">
                    <label for="phoneInput">Договор</label>
                    {foreach $orders as $o}
                        {if $o->status == '10'}
                            <option value="{$o->order_id}">{$o->order_id}</option>
                        {/if}
                    {/foreach}
                </select>
                <div class="modal-body send-sms-modal-div">
                    <label for="phoneInput">Напишите номер телефона </label>
                    <input  value="{$client->phone_mobile}" class="form form-control p-2 sms-phone" maxlength="11" id = 'phoneInput'>
                </div>
            </div>
            <div class="modal-footer" style="margin: auto">
                <button type="button"  class="btn btn btn-success send-sms">Отправить</button>
                <button type="button" class="btn btn btn-danger" data-dismiss="modal">Отмена</button>
            </div>
        </div>

    </div>
</div>
<div class="modal fade" id="leaveComplaint" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body" style="text-align: center; font-size: 18px; color: white">
                <select class="form-control mb-2" id="selectSubject">
                    <option value="" disabled selected>Выберите тематику жалобы</option>
                    <option value="borrower">Заемщик</option>
                    <option value="ministry">МВД</option>
                    <option value="prosecutor">Прокуратура</option>
                    <option value="roskomnadzor">Роскомнадзор</option>
                    <option value="rospotrebnadzor">Роспотребнадзор</option>
                    <option value="sro">СРО</option>
                    <option value="third_person">Третье лицо </option>
                    <option value="fssp">ФССП</option>
                    <option value="central_bank">Центробанк</option>
                    <option value="hotline">Горячая линия</option>
                    <option value="email">Почта</option>
                    <option value="third-face">Жалоба взаимод. 3 лица</option>
                    <option value="bomber">Жалоба на бомбер</option>
                    <option value="threats">Жалобы угрозы (угрожали физ. расправой/оскорбления)</option>
                    <option value="robot">Жалобы робот</option>
                    <option value="sms">Жалобы на смс</option>
                    <option value="add_service">Жалобы на доп. услуги</option>
                </select>
                <select name="" id="complaint-order-number" class="form-control">
                    {foreach $orders as $order}
                        <option value="{$order->order_id}">{$order->order_id}</option>
                    {/foreach}
                </select>
                <label for="complaint-comment" class="mt-2">Комментарии</label>
                <textarea  id="complaint-comment" class="form-control" cols="30" rows="10"></textarea>
            </div>
            <div class="modal-footer" style="margin: auto">
                <button type="button"  class="btn btn btn-success send-complaint">Отправить</button>
                <button type="button" class="btn btn btn-danger" data-dismiss="modal">Отмена</button>
            </div>
        </div>

    </div>
</div>
{*<div id="smsModal" class="modal fade bd-example-modal-sm show" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">*}
{*    <div class="modal-dialog modal-md">*}
{*        <div class="modal-content">*}
{*            <div class="modal-header">*}
{*                <button type="button" class="close" data-dismiss="modal">&times;</button>*}
{*            </div>*}
{*            <div class="modal-body" style="text-align: center; font-size: 18px; color: white">*}
{*                <select class="form-control mb-2">*}
{*                    {foreach $orders as $o}*}
{*                        {if $o->status == 10}*}
{*                            <option value="{$o->id}">{$o->id}</option>*}
{*                        {/if}*}
{*                    {/foreach}*}
{*                </select>*}
{*            </div>*}
{*            <div class="modal-footer" style="margin: auto">*}
{*                <button type="button"  class="btn btn btn-success off-asp-modal-button"  value="{$loan_history_item->number}">Да</button>*}
{*                <button type="button" class="btn btn btn-danger" data-dismiss="modal">Нет</button>*}
{*            </div>*}
{*        </div>*}

{*    </div>*}
{*</div>*}
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>

<script type="text/javascript">
    $(document).ready(function () {
        $("#blocked_adv_sms").on('click', function () {
            let user_id = $(this).data('id'),
                btn = $(this),
                blocked = 1;

            if (!$(this).hasClass('btn-danger')) {
                blocked = 0;
            }

            const newClass = blocked ? 'btn-warning' : 'btn-danger';
            const newText = blocked ? 'Разблокировать рекламные смс' : 'Заблокировать рекламные смс';

            btn.removeClass('btn-warning').removeClass('btn-danger');

            $.ajax({
                url: '/ajax/users.php?action=blocked_adv_sms',
                type: 'POST',
                data: {
                    user_id,
                    blocked,
                },
                beforeSend: function () {
                    $('.preloader').show();
                },
                success: function (result) {
                    if (result.success) {
                        btn.addClass(newClass).text(newText);
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
                    alert(error);
                    console.log(error);
                },
            }).done(function () {
                $(".preloader").hide();
            });
        })

        $("#toggle_show_docs").on('click', function () {
            let btn = $(this);
            let user_id = btn.data('user-id');
            let new_value = btn.text().trim() === 'Показать доп. документы' ? 1 : 0;

            $.ajax({
                url: '/ajax/users.php?action=toggle_show_docs',
                type: 'POST',
                data: {
                    user_id: user_id,
                    new_value: new_value
                },
                beforeSend: function () {
                    $('.preloader').show();
                },
                success: function (result) {
                    if (result.success) {
                        btn.text(new_value ? 'Скрыть доп. документы' : 'Показать доп. документы');
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
                    alert(error);
                    console.log(error);
                },
                complete: function () {
                    $(".preloader").hide();
                }
            });
        });

        $(document).on('change', '#vsev_debt_notification_disabled', function (e) {
            const userId = `{$client->id}`;

            $.ajax({
                url: 'client/' + userId,
                type: 'POST',
                data: {
                    user_id: userId,
                    action: 'vsev_debt_notification_disabled',
                    vsev_debt_notification_disabled: +e.target.checked
                },
                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            timer: 5000,
                            title: 'Успешно',
                            type: 'success',
                        });
                    } else {
                        Swal.fire({
                            timer: 5000,
                            title: 'Ошибка!',
                            text: translateMessage(response.error),
                            type: 'error',
                        });
                    }
                },
                error: function () {
                    alert('Ошибка при отправке запроса.');
                }
            });
        });

        // toggle clients button
        $('#contactsCollapse').on('shown.bs.collapse', function () {
            $('.toggle-contacts').text('Свернуть');
        });
        $('#contactsCollapse').on('hidden.bs.collapse', function () {
            $('.toggle-contacts').text('Показать всех');
        });
                                                   
    })
</script>

<script>
    $('.off-asp-button').click(function () {
        $('.off-asp-modal-button').val($(this).val())
    })

    $('.off-asp-modal-button').click(function (){
        let value =$(this).val()
        $.ajax({
            type: 'GET',
            data: { data: value},
            url:'ajax/delete_asp_zaim.php',
            success:(resp) => {
                location.reload()
            }
        })
    })
    $(document).on('click','.send-data-to-modal',function(){
        let type = $(this).data('type')
        let manager = $(this).data('manager')
        $('.send-sms').attr('data-type', type)
        $('.send-sms').attr('data-manager', manager)
    })
    $('.sms-phone').on('input', function(){
        let phone = {$clientPhone}
        if (phone !== $(this).val()) {
            $('.send-sms').prop('disabled', true);
            if ($('.send-sms-modal-div .modal-radio-button').length === 0) {
                $('.send-sms-modal-div').append(`
                <div class='modal-radio-button mt-2'>
                    <label for="">Номер клиента?</label>
                    <span>Да</span><input type="radio" name="client-phone" class="mr-2 client-phone" value='true'>
                    <span>Нет</span><input type="radio" name="client-phone" class="client-phone" value='false'>
                </div>
            `);
            }
        } else {
            $('.modal-radio-button').remove();
            $('.send-sms').prop('disabled', false);
        }
    })
    $(document).on('change', '.client-phone', function () {
        let value = $(this).val();
        if (value === 'true') {
            $('.send-sms').prop('disabled', false);
            $('.modal-whose-input').remove();
        } else {
            $('.send-sms').prop('disabled', true);
            if ($('.send-sms-modal-div .modal-input').length === 0) {
                $('.send-sms-modal-div').append(`
                <div class='modal-whose-input mt-2'>
                    <input type="text" name="client-phone-new" class="mr-2 modal-whose-number form form-control" placeholder="Чей номер?" maxlength="64">
                </div>
            `);
                    }
                }
            });

    $(document).on('input', '.modal-whose-number', function() {
        if ($(this).val().length !== 0) {
            $('.send-sms').prop('disabled', false);
        }else{
            $('.send-sms').prop('disabled', true);
        }
    })

    $('.btn-modal-send-sms').click(function() {
        $('.send-sms').attr('data-type',$(this).data('type'))
        $('.send-sms').attr('data-order',$(this).data('order'))
        $('.send-sms').attr('data-manager',$(this).data('manager'))
    })
    $(document).ready(function(){
        $('.js-stoplist-user').click(function (e) {
            e.preventDefault();
            var userId = $(this).data('user');

            $.ajax({
                url: 'ajax/clear_stop_list.php',
                type: 'POST',
                data: {
                    'action': 'stoplist',
                    'user_id': userId
                },
                success: function (resp) {
                    if (!!resp.error) {
                        Swal.fire({
                            timer: 5000,
                            title: 'Ошибка!',
                            text: resp.error,
                            type: 'error',
                        });
                    } else {
                        Swal.fire({
                            timer: 5000,
                            title: 'Успешно',
                            text: resp.success,
                            type: 'success',
                        });
                        location.reload();
                    }
                }
            });
        });
        
        $('#toggleAutoCall').click(function () {
            var userId = $(this).data('user-id');
            $.ajax({
                type: 'POST',
                data: { user_id: userId, 'action': 'toggle_approved_order_call_disabling' },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        if (response.disable) {
                            $('#toggleAutoCall')
                                .removeClass('btn-danger')
                                .addClass('btn-success')
                                .text('Включить звонки по одобренным заявкам');
                        } else {
                            $('#toggleAutoCall')
                                .removeClass('btn-success')
                                .addClass('btn-danger')
                                .text('Отключить звонки по одобренным заявкам');
                        }
                    } else {
                        alert('Ошибка');
                    }
                },
                error: function () {
                    alert('Ошибка при отправке запроса.');
                }
            });
        });

        $('#unlockRejectedNk').click(function () {
            var userId = $(this).data('user-id');
            $.ajax({
                type: 'POST',
                data: { user_id: userId, 'action': 'unlock_rejected_nk' },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        $('#unlockRejectedNk').remove();
                        Swal.fire({
                            timer: 5000,
                            title: 'Успешно',
                            text: resp.success,
                            type: 'success',
                        });
                    } else {
                        alert('Ошибка');
                    }
                },
                error: function () {
                    alert('Ошибка.');
                }
            });
        });

        $('#complaint-order-number').select2({
            width: '100%',
            placeholder: "Выберите заявку",
            allowClear: true,
            maximumSelectionLength: 1,
        }).val(null).trigger('change');


        $('.send-complaint').click(function () {
            var userId = {$client->id};
            let subject = $('#selectSubject').val();
            let number = $('#complaint-order-number').val()
            let comment = $('#complaint-comment').val()
            $.ajax({
                type: 'POST',
                data: { user_id: userId,subject:subject, number:number,comment:comment,action:'leave_complaint'},
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            timer: 5000,
                            title: 'Успешно',
                            text: translateMessage(response.message),
                            type: 'success',
                        }).then(() => {
                            $('#selectSubject').val('');
                            $('#complaint-order-number').val(null).trigger('change');
                            $('#complaint-comment').val('');
                            $('#leaveComplaint').modal('hide');
                        });
                    } else {
                        Swal.fire({
                            timer: 5000,
                            title: 'Ошибка!',
                            text: translateMessage(response.message),
                            type: 'error',
                        });
                    }
                },
                error: function () {
                    alert('Ошибка при отправке запроса.');
                }
            });
        });

        function translateMessage(message) {
            const translations = {
                'Not all fields are filled in.': 'Не все поля заполнены.',
                'The loan does not belong to the client.': 'Заявка не принадлежит клиенту.',
                'Complaint successfully sent to 1C.': 'Жалоба успешно отправлена в 1С.',
                'Error sending request.': 'Ошибка при отправке запроса.'
            };

            return translations[message] || message;
        }

        $(document).on('change', '#client_recurring_payment_so', function (e) {
            const userId = `{$client->id}`;

            $.ajax({
                url: 'client/' + userId,
                type: 'POST',
                data: {
                    user_id: userId,
                    action: 'client_recurring_payment_so',
                    client_recurring_payment_so: +e.target.checked
                },
                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            timer: 5000,
                            title: 'Успешно',
                            type: 'success',
                        });
                    } else {
                        Swal.fire({
                            timer: 5000,
                            title: 'Ошибка!',
                            text: translateMessage(response.error),
                            type: 'error',
                        });
                    }
                },
                error: function () {
                    alert('Ошибка при отправке запроса.');
                }
            });
        });

        $(document).on('click', '.toggle-transcript', function () {
            let $preview = $(this).siblings('.transcript-preview');

            if ($preview.hasClass('collapsed')) {
                $preview.removeClass('collapsed').addClass('expanded');
                $(this).text('Скрыть');
            } else {
                $preview.removeClass('expanded').addClass('collapsed');
                $(this).text('Показать полностью');
            }
        });
    });

</script>
{if in_array($manager->role, ['developer', 'chief_verificator', 'ts_operator']) && !$has_approved_orders}
    <div id="modal_agreement_saved" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title">Готово</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">

                    <div class="card">
                        <div class="card-body">

                            <div class="tab-content tabcontent-border p-3" id="myTabContent">
                                <div role="tabpanel" class="tab-pane fade active show" id="waiting_reason" aria-labelledby="home-tab">
                                    <div>Клиенту отправлено доп. соглашение в ЛК</div>
                                    <div class="form-action clearfix">
                                        <div class="row">
                                            <div class="col-12 text-right">
                                                <button type="button" class="btn btn-success float-right waves-effect" data-dismiss="modal">Ок</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/if}

{include file='html_blocks/issue_promocode_modal.tpl' clientId=$client->id haveCloseCredits=($client->loan_history|count > 0)}
