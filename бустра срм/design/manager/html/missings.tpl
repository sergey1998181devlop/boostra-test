{$meta_title='Отвалы клиентов' scope=parent}

{capture name='page_scripts'}

    <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/clients.js?v=1.000"></script>
    <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/missings.js?v=1.002"></script>
{/capture}

{capture name='page_styles'}
    <link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid.min.css" />
    <link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid-theme.min.css" />
    <style>
        .jsgrid-table { margin-bottom:0}
    </style>
{/capture}

<style>
    @media screen and (min-width: 580px){
        body {
            padding-right: 0px !important;
        }
    }
</style>

<div class="page-wrapper">
    <!-- ============================================================== -->
    <!-- Container fluid  -->
    <!-- ============================================================== -->
    <div class="container-fluid">
        <!-- ============================================================== -->
        <!-- Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <div class="row page-titles">
            <div class="col-md-12 col-4 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-sleep"></i>
                    <span>Отвалы клиентов</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Отвалы</li>
                </ol>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Start Page Content -->
        <!-- ============================================================== -->
        <div class="row">
{*            <div class="col-md-12">*}
{*                <div class="card">*}
{*                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap">*}
{*                        <!-- Блок "Не обработано" -->*}
{*                        <div class="text-center mx-3">*}
{*                            <h5 class="card-title font-weight-bold">Не обработано</h5>*}
{*                            <h2 class="text-danger font-weight-bold">{$statistic->unhandled}</h2>*}
{*                        </div>*}

{*                        <!-- Блок "Всего" -->*}
{*                        <div class="text-center mx-3">*}
{*                            <h5 class="card-title font-weight-bold">*}
{*                                Всего*}
{*                                <i class="mdi mdi-information ml-1"*}
{*                                   data-toggle="tooltip"*}
{*                                   data-placement="top"*}
{*                                   title="За сегодня"></i>*}
{*                            </h5>*}
{*                            <h2 class="text-primary font-weight-bold">{$statistic->totals}</h2>*}
{*                        </div>*}

{*                        <!-- Блок "Взято в работу" -->*}
{*                        <div class="text-center mx-3">*}
{*                            <h5 class="card-title font-weight-bold">*}
{*                                Взято в работу*}
{*                                <i class="mdi mdi-information ml-1"*}
{*                                   data-toggle="tooltip"*}
{*                                   data-placement="top"*}
{*                                   title="За сегодня и вчера"></i>*}
{*                            </h5>*}
{*                            <h2 class="text-warning font-weight-bold">{$statistic->in_progress}</h2>*}
{*                        </div>*}

{*                        <!-- Блок "Завершено из взятых в работу" -->*}
{*                        <div class="text-center mx-3">*}
{*                            <h5 class="card-title font-weight-bold">Завершено из взятых в работу</h5>*}
{*                            <h2 class="text-success font-weight-bold">{$statistic->completed}</h2>*}
{*                        </div>*}

{*                        <!-- Блок "Конверсия" -->*}
{*                        <div class="text-center mx-3">*}
{*                            <h5 class="card-title font-weight-bold">Конверсия</h5>*}
{*                            <h2 class="text-info font-weight-bold">*}
{*                                {if $statistic->totals > 0 && $statistic->in_progress > 0}*}
{*                                    {($statistic->completed / $statistic->in_progress * 100)|round}*}
{*                                {else}*}
{*                                    0*}
{*                                {/if} %*}
{*                            </h2>*}
{*                        </div>*}
{*                    </div>*}
{*                </div>*}
{*            </div>*}


            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Отвалы клиентов</h4>
                        <div id="basicgrid" class="jsgrid" style="position: relative; width: 100%;">
                            <div class="preloader-table" style="display: none; z-index: 9; height: 100%;position: absolute;width: 100%;background:rgba(256,256,256,0.5)">
                                <svg class="circular" viewBox="25 25 50 50">
                                    <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"></circle> </svg>
                            </div>
                            <div class="jsgrid-grid-header jsgrid-grid-body">
                                <table class="jsgrid-table table table-striped table-hover">
                                    <tr class="jsgrid-header-row">
                                        <th style="width: 100px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'id_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'id_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'id_asc'}<a href="{url page=null sort='id_desc'}">ID</a>
                                            {else}<a href="{url page=null sort='id_asc'}">ID</a>{/if}
                                        </th>
                                        <th style="width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'date_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'date_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'date_asc'}<a href="{url page=null sort='date_desc'}">Регистрация</a>
                                            {else}<a href="{url page=null sort='date_asc'}">Регистрация</a>{/if}
                                        </th>
                                        <th style="width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'date_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'date_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'date_asc'}<a href="{url page=null sort='date_desc'}">Посл. действие</a>
                                            {else}<a href="{url page=null sort='date_asc'}">Посл. действие</a>{/if}
                                        </th>
                                        <th style="width: 120px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'fio_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'fio_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'fio_asc'}<a href="{url page=null sort='fio_desc'}">ФИО</a>
                                            {else}<a href="{url page=null sort='fio_asc'}">ФИО</a>{/if}
                                        </th>
                                        <th style="width: 100px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'phone_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'phone_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'phone_asc'}<a href="{url page=null sort='phone_desc'}">Телефон</a>
                                            {else}<a href="{url page=null sort='phone_asc'}">Телефон</a>{/if}
                                        </th>
                                        <th style="width: 100px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'timezone_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'timezone_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'timezone_asc'}
                                                <a href="{url page=null sort='timezone_desc'}">Время</a>
                                            {else}
                                                <a href="{url page=null sort='timezone_asc'}">Время</a>
                                            {/if}
                                        </th>
                                        <th style="width:100px" class="jsgrid-header-cell ">
                                            <a href="javascript:void(0);">Тип платформы</a>
                                        </th>
                                        <th style="width:100px" class="jsgrid-header-cell ">
                                            <a href="javascript:void(0);">Пред. орг.</a>
                                        </th>
                                        <th style="width:100px" class="jsgrid-header-cell ">
                                            <a href="javascript:void(0);">Новая орг.</a>
                                        </th>
                                        <th style="width:150px" class="jsgrid-header-cell ">
                                            <a href="javascript:void(0);">Этапы</a>
                                        </th>
                                        <th style="width:60px;" class="jsgrid-header-cell "></th>
                                        <th style="width: 100px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'manager_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'manager_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'manager_asc'}<a href="{url page=null sort='manager_desc'}">Ответственный</a>
                                            {else}<a href="{url page=null sort='manager_asc'}">Ответственный</a>{/if}
                                        </th>
                                        <th style="width: 100px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'last_call_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'last_call_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'last_call_asc'}<a href="{url page=null sort='last_call_desc'}">Время контакта</a>
                                            {else}<a href="{url page=null sort='last_call_asc'}">Время контакта</a>{/if}
                                        </th>
                                        <th style="width: 140px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'call_status_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'call_status_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'call_status_asc'}<a href="{url page=null sort='call_status_desc'}">Статус по звонку</a>
                                            {else}<a href="{url page=null sort='call_status_asc'}">Статус по звонку</a>{/if}
                                        </th>
                                        <th style="width: 140px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'stage_in_contact_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'stage_in_contact_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'stage_in_contact_asc'}<a href="{url page=null sort='stage_in_contact_desc'}">Этап во время контакта</a>
                                            {else}<a href="{url page=null sort='stage_in_contact_asc'}">Этап во время контакта</a>{/if}
                                        </th>
                                        <th style="width: 100px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'continue_order_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'continue_order_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'continue_order_asc'}<a href="{url page=null sort='continue_order_desc'}">Клиент продолжит оформлять</a>
                                            {else}<a href="{url page=null sort='continue_order_asc'}">Клиент продолжит оформлять</a>{/if}
                                        </th>
                                        <th style="width: 100px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'status_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'status_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'status_asc'}<a href="{url page=null sort='status_desc'}">Прозвон роботом</a>
                                            {else}<a href="{url page=null sort='status_asc'}">Прозвон роботом</a>{/if}
                                        </th>
                                        <th style="width:110px" class="jsgrid-header-cell ">
                                            <a href="javascript:void(0);">Комментарий</a>
                                        </th>
                                        <th style="width:110px" class="jsgrid-header-cell ">
                                            <a href="javascript:void(0);">Последний тег</a>
                                        </th>
                                        <th style="width: 160px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'phone_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'phone_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'user_id_asc'}<a href="{url page=null sort='user_id_desc'}">Отвалы</a>
                                            {else}<a href="{url page=null sort='user_id_asc'}">Отвалы</a>{/if}
                                        </th>
                                    </tr>

                                    <tr class="jsgrid-filter-row" id="search_form">
                                        <td style="width: 100px;" class="jsgrid-cell jsgrid-align-right">
                                            <input type="hidden" name="sort" value="{$sort}" />
                                            <input type="text" name="user_id" value="{$search['user_id']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 80px;" class="jsgrid-cell jsgrid-align-right">
                                            <input type="text" name="created" value="{$search['created']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 80px;" class="jsgrid-cell jsgrid-align-right">
                                            <input type="text" name="created" value="{$search['created']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 120px;" class="jsgrid-cell jsgrid-align-right">
                                            <input type="text" name="fio" value="{$search['fio']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 100px;" class="jsgrid-cell">
                                            <input type="text" name="phone" value="{$search['phone']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 100px;" class="jsgrid-cell">
                                            <input type="text" name="timezone" value="{$search['timezone']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 100px;" class="jsgrid-cell">
                                            <select class="form-control-sm form-control" name="utm_term">
                                                <option value="">Выберите тип платформы</option>
                                                <option value="">Веб-сайт</option>
                                                <option value="app_android">Android</option>
                                            </select>
                                        </td>
                                        <td style="width: 100px;" class="jsgrid-cell"></td>
                                        <td style="width: 100px;" class="jsgrid-cell"></td>
                                        <td style="width: 150px;" class="jsgrid-cell">
                                            <select class="form-control-sm form-control js-stages-multi" name="stages" multiple size="6">
                                                <option value="2" {if isset($search.stages) && in_array(2, $search.stages)}selected{/if}>Перс. инфо</option>
                                                <option value="3" {if isset($search.stages) && in_array(3, $search.stages)}selected{/if}>Адрес</option>
                                                <option value="4" {if isset($search.stages) && in_array(4, $search.stages)}selected{/if}>Одобрение</option>
                                                <option value="5" {if isset($search.stages) && in_array(5, $search.stages)}selected{/if}>Карта</option>
                                                <option value="6" {if isset($search.stages) && in_array(6, $search.stages)}selected{/if}>Файлы</option>
                                                <option value="7" {if isset($search.stages) && in_array(7, $search.stages)}selected{/if}>Доп. инфо</option>
                                            </select>
                                        </td>

                                        <td style="width: 60px;" class="jsgrid-cell"></td>
                                        <td style="width: 100px;" class="jsgrid-cell">
                                            <select class="form-control-sm form-control" name="missing_manager_id">
                                                <option value="">Выберите менеджера</option>
                                                <option value="unhandled">Не обработано</option>
                                                {foreach $managers as $m}
                                                <option value="{$m->id}" {if $search['missing_manager_id'] == $m->id}selected{/if}>{$m->name|escape}</option>
                                                {/foreach}
                                            </select>
                                        </td>
                                        <td style="width: 100px;" class="jsgrid-cell">
                                            <input type="text" name="last_call" value="{$search['last_call']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 140px;" class="jsgrid-cell">
                                            <select class="form-control-sm form-control" name="call_status_search">
                                                <option {if empty($search['call_status_search'])}selected{/if} value=""></option>
                                                {foreach Users::CALL_STATUS_MAP as $status_value => $status_name}
                                                    <option value="{$status_value}" {if null !== $search['call_status_search'] && $status_value == $search['call_status_search']}selected{/if}>{$status_name}</option>
                                                {/foreach}
                                            </select>
                                        </td>
                                        <td style="width: 140px;" class="jsgrid-cell">
                                            <select class="form-control-sm form-control" name="stage_in_contact_search">
                                                <option {if empty($search['stage_in_contact_search'])}selected{/if} value=""></option>
                                                {foreach Users::CLIENT_STAGE_NAMES_MAP as $stage_in_contact_value => $stage_in_contact_name}
                                                    <option value="{$stage_in_contact_value}" {if null !== $search['stage_in_contact_search'] && $stage_in_contact_value == $search['stage_in_contact_search']}selected{/if}>{$stage_in_contact_name}</option>
                                                {/foreach}
                                            </select>
                                        </td>
                                        <td style="width: 100px;" class="jsgrid-cell">
                                            <select class="form-control-sm form-control" name="continue_order_search">
                                                <option {if empty($search['continue_order_search'])}selected{/if} value=""></option>
                                                {foreach Users::CONTINUE_ORDER_MAP as $continue_order_value => $continue_order_name}
                                                    <option value="{$continue_order_value}" {if null !== $search['continue_order_search'] && $continue_order_value ==  $search['continue_order_search'] }selected{/if}>{$continue_order_name}</option>
                                                {/foreach}
                                            </select>
                                        </td>
                                        <td style="width: 100px;" class="jsgrid-cell">
                                            <select class="form-control-sm form-control" name="status_search">
                                                <option {if empty($search['status_search'])}selected{/if} value=""></option>
                                                {foreach Users::CALL_ROBOT_MAP as $call_robot_value => $call_robot_name}
                                                    <option value="{$call_robot_value}" {if null !== $search['status_search'] && $call_robot_value ==  $search['status_search'] }selected{/if}>{$call_robot_name}</option>
                                                {/foreach}
                                            </select>
                                        </td>
                                        <td style="width: 110px;" class="jsgrid-cell"></td>
                                        <td style="width: 160px;" class="jsgrid-cell"></td>
                                        <td style="width: 160px;" class="jsgrid-cell"></td>
                                    </tr>
                                    <tbody>
                                    {foreach $clients as $client}
                                        <tr class="jsgrid-row">
                                            <td style="width: 100px;" class="jsgrid-cell jsgrid-align-right">
                                                <a href="client/{$client->id}">{$client->id}</a>

                                            </td>
                                            <td style="width: 80px;" class="jsgrid-cell">
                                                <span>{$client->created|date}</span>
                                                {$client->created|time}
                                            </td>
                                            <td style="width: 80px;" class="jsgrid-cell">
                                                <span>{$client->last_stage_date|date}</span>
                                                {$client->last_stage_date|time}
                                            </td>
                                            <td style="width: 120px;" class="jsgrid-cell">
                                                {$client->lastname|escape}
                                                {$client->firstname|escape}
                                                {$client->patronymic|escape}
                                            </td>
                                            <td style="width: 100px;" class="jsgrid-cell">
                                                <span class="phone-cell">{$client->phone_mobile|escape}</span>
                                                <button class="btn waves-effect waves-light btn-xs btn-info {if !$is_developer}js-mango-call{/if} {if !$client->missing_manager_id}js-set-manager{/if}" data-phone="{$client->phone_mobile}" data-user="{$client->id}" title="Выполнить звонок"><i class="fas fa-phone-square"></i></button>
                                                <button
                                                        class="btn waves-effect waves-light btn-xs btn-success js-voximplant-call"
                                                        data-listing_type="missings"
                                                        data-phone="{$client->phone_mobile}"
                                                        data-user="{$client->id}">
                                                    <i class="fas fa-phone-square"></i>

                                                </button>
                                            </td>
                                            <td style="width: 100px;" class="jsgrid-cell">{if $client->timezone !== NULL }{$client->timezone} {else} нет данных{/if}</td>
                                            <td style="width: 100px;" class="jsgrid-cell">{if $client->utm_term === 'app_android' } Android {else} Веб-сайт {/if}</td>
                                            <td style="width: 100px;" class="jsgrid-cell">
                                                {if isset($org_switch_data[$client->id])}
                                                    {$org_switch_data[$client->id]->old_org_name|escape}
                                                {/if}
                                            </td>
                                            <td style="width: 100px;" class="jsgrid-cell">
                                                {if isset($org_switch_data[$client->id])}
                                                    {$org_switch_data[$client->id]->new_org_name|escape}
                                                {/if}
                                            </td>
                                            <td style="width: 150px;" class="js-autoupdate jsgrid-cell stage-cell" data-client-id="{$client->id}">
                                                <span class="label label-success">Регистрация</span>
                                                <span class="label {if $client->personal_data_added}label-success{else}label-inverse{/if}">Перс. инфо</span>
                                                <span class="label {if $client->address_data_added}label-success{else}label-inverse{/if}">Адрес</span>
                                                <span class="label {if $client->accept_data_added}label-success{else}label-inverse{/if}">Одобрение</span>
                                                <span class="label {if $client->card_added}label-success{else}label-inverse{/if}">Карта</span>
                                                <span class="label {if $client->files_added}label-success{else}label-inverse{/if}">Файлы</span>
                                                <span class="label {if $client->additional_data_added}label-success{else}label-inverse{/if}">Доп. инфо</span>
                                                {if $client->stage_sms_sended}
                                                <span class="label label-primary" title="СМС сообщение отправлено">СМС</span>
                                                {/if}
                                            </td>
                                            <td style="width: 60px;" class="jsgrid-cell text-right">
                                                <button type="button" class="btn btn-sm btn-success js-open-comment-form" title="Добавить комментарий" data-user="{$client->id}" data-uid="{$client->uid}">
                                                    <i class="mdi mdi-comment-text"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-primary js-open-sms-modal"
                                                        title="Отправить смс"
                                                        data-user="{$client->id}"
                                                >
                                                    <i class=" far fa-share-square"></i>
                                                </button>
                                            </td>
                                            <td style="width: 100px;" class="jsgrid-cell manager{$client->id}">
                                                <span class="js-missing-manager-name">{$managers[$client->missing_manager_id]->name|escape}</span>
                                                <br />
                                                <div class="ended{$client->id}">
                                                {if $client->missing_status}
                                                    <span class="label label-primary">Завершена</span>
                                                {/if}
                                                </div>
                                            </td>
                                            <td style="width: 100px;" class="jsgrid-cell last-call-cell" data-client-id="{$client->id}">
                                                {$last_calls[$client->id]->created}
                                            </td>
                                            <td style="width: 140px;" class="jsgrid-cell text-right">
                                                <form class="common_save_field_form" action="/client/{$client->id}">
                                                    <select name="call_status" class="form-control field-to-save">
                                                        <option {if null === $client->call_status}selected{/if} hidden></option>

                                                        {foreach Users::CALL_STATUS_MAP as $status_value => $status_name}
                                                            <option value="{$status_value}" {if null !== $client->call_status && $status_value == $client->call_status}selected{/if}>{$status_name}</option>
                                                        {/foreach}
                                                    </select>
                                                </form>
                                            </td>
                                            <td style="width: 100px;" class="jsgrid-cell">
                                                <form class="common_save_field_form" action="/client/{$client->id}">
                                                    <select name="stage_in_contact" class="form-control field-to-save">
                                                        <option {if null === $client->stage_in_contact}selected{/if} hidden></option>

                                                        {foreach Users::CLIENT_STAGE_NAMES_MAP as $stage_in_contact_value => $stage_in_contact_name}
                                                            <option value="{$stage_in_contact_value}" {if null !== $client->stage_in_contact && $stage_in_contact_value == $client->stage_in_contact }selected{/if}>{$stage_in_contact_name}</option>
                                                        {/foreach}
                                                    </select>
                                                </form>
                                            </td>
                                            <td style="width: 100px;" class="jsgrid-cell text-right">
                                                <form class="common_save_field_form" action="/client/{$client->id}">
                                                    <select name="continue_order" class="form-control field-to-save">
                                                        <option {if null === $client->continue_order}selected{/if} hidden></option>

                                                        {foreach Users::CONTINUE_ORDER_MAP as $continue_order_value => $continue_order_name}
                                                            <option value="{$continue_order_value}" {if null !== $client->continue_order && $continue_order_value == $client->continue_order }selected{/if}>{$continue_order_name}</option>
                                                        {/foreach}
                                                    </select>
                                                </form>
                                            </td>
                                            <td style="width: 140px;" class="jsgrid-cell text-right">
                                                <form class="common_save_field_form" action="/client/{$client->id}">
                                                    <select name="call_robot" class="form-control field-to-save">
                                                        <option {if null === $client->status}selected{/if} hidden></option>

                                                        {foreach Users::CALL_ROBOT_MAP as $status_value => $status_name}
                                                            <option value="{$status_value}" {if null !== $client->status && $status_value == $client->status}selected{/if}>{$status_name}</option>
                                                        {/foreach}
                                                    </select>
                                                </form>
                                            </td>
                                            <td style="width: 110px;" class="jsgrid-cell">
                                                <div>
                                                    <div>{$client->last_comment}</div>
                                                    <form method="POST" id="form_add_comment" action="order/{$order->order_id}">

                                                        <input type="hidden" name="order_id" value="{$order->order_id}" />
                                                        <input type="hidden" name="user_id" value="{$client->id}" />
                                                        <input type="hidden" name="block" value="missing" />
                                                        <input type="hidden" name="action" value="add_comment" />
                                                        <div class="alert" style="display:none"></div>

                                                        <div class="form-group">
                                                            <textarea class="form-control" name="text"></textarea>
                                                        </div>
                                                        <div class="form-action">
                                                            <button type="submit" class="btn btn-success waves-effect waves-light">Сохранить</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </td>
                                            <td style="width: 100px;" class="jsgrid-cell">

                                            </td>
                                            <td style="width: 160px;" class="jsgrid-cell">
                                                {if $client->dump}
                                                <div>
                                                    {$managers[$client->dump->manager_id]->name}
                                                </div>
                                                <div>
                                                    {$client->dump->callDate}
                                                </div>
                                                <div>
                                                {if $client->dump->record_file}
                                                    <audio style="width: 80%; " src="{$config->back_url}/files/calls/{$client->dump->record_file}" controls>
                                                {else}
                                                    Вызов не осуществлен
                                                {/if}
                                                </div>
                                                {/if}
                                            </td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                            </div>

                            {if $total_pages_num>1}

                            {* Количество выводимых ссылок на страницы *}
                        	{$visible_pages = 11}
                        	{* По умолчанию начинаем вывод со страницы 1 *}
                        	{$page_from = 1}

                        	{* Если выбранная пользователем страница дальше середины "окна" - начинаем вывод уже не с первой *}
                        	{if $current_page_num > floor($visible_pages/2)}
                        		{$page_from = max(1, $current_page_num-floor($visible_pages/2)-1)}
                        	{/if}

                        	{* Если выбранная пользователем страница близка к концу навигации - начинаем с "конца-окно" *}
                        	{if $current_page_num > $total_pages_num-ceil($visible_pages/2)}
                        		{$page_from = max(1, $total_pages_num-$visible_pages-1)}
                        	{/if}

                        	{* До какой страницы выводить - выводим всё окно, но не более ощего количества страниц *}
                        	{$page_to = min($page_from+$visible_pages, $total_pages_num-1)}

                            <div class="jsgrid-pager-container" style="">
                                <div class="jsgrid-pager">
                                    Страницы:

                                    {if $current_page_num == 2}
                                    <span class="jsgrid-pager-nav-button "><a href="{url page=null}">Пред.</a></span>
                                    {elseif $current_page_num > 2}
                                    <span class="jsgrid-pager-nav-button "><a href="{url page=$current_page_num-1}">Пред.</a></span>
                                    {/if}

                                    <span class="jsgrid-pager-page {if $current_page_num==1}jsgrid-pager-current-page{/if}">
                                        {if $current_page_num==1}1{else}<a href="{url page=null}">1</a>{/if}
                                    </span>
                                   	{section name=pages loop=$page_to start=$page_from}
                                		{* Номер текущей выводимой страницы *}
                                		{$p = $smarty.section.pages.index+1}
                                		{* Для крайних страниц "окна" выводим троеточие, если окно не возле границы навигации *}
                                		{if ($p == $page_from + 1 && $p != 2) || ($p == $page_to && $p != $total_pages_num-1)}
                                		<span class="jsgrid-pager-page {if $p==$current_page_num}jsgrid-pager-current-page{/if}">
                                            <a href="{url page=$p}">...</a>
                                        </span>
                                		{else}
                                		<span class="jsgrid-pager-page {if $p==$current_page_num}jsgrid-pager-current-page{/if}">
                                            {if $p==$current_page_num}{$p}{else}<a href="{url page=$p}">{$p}</a>{/if}
                                        </span>
                                		{/if}
                                	{/section}
                                    <span class="jsgrid-pager-page {if $current_page_num==$total_pages_num}jsgrid-pager-current-page{/if}">
                                        {if $current_page_num==$total_pages_num}{$total_pages_num}{else}<a href="{url page=$total_pages_num}">{$total_pages_num}</a>{/if}
                                    </span>

                                    {if $current_page_num<$total_pages_num}
                                    <span class="jsgrid-pager-nav-button"><a href="{url page=$current_page_num+1}">След.</a></span>
                                    {/if}
                                    &nbsp;&nbsp; {$current_page_num} из {$total_pages_num}
                                </div>
                            </div>
                            {/if}

                            <div class="jsgrid-load-shader" style="display: none; position: absolute; inset: 0px; z-index: 10;">
                            </div>
                            <div class="jsgrid-load-panel" style="display: none; position: absolute; top: 50%; left: 50%; z-index: 1000;">
                                Идет загрузка...
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Column -->
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End PAge Content -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- footer -->
    <!-- ============================================================== -->
    {include file='footer.tpl'}
    <!-- ============================================================== -->
    <!-- End footer -->
    <!-- ============================================================== -->
</div>

<div id="modal_add_comment" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Добавить комментарий</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_add_comment" action="order/{$order->order_id}">

                    <input type="hidden" name="order_id" value="" />
                    <input type="hidden" name="user_id" value="" />
                    <input type="hidden" name="block" value="missing" />
                    <input type="hidden" name="action" value="add_comment" />

                    <input type="hidden" name="task_id" value="" />
                    <input type="hidden" name="uid" value="" />

                    <div class="alert" style="display:none"></div>

                    <div class="form-group">
                        <label for="name" class="control-label text-white">Комментарий:</label>
                        <textarea class="form-control" name="text"></textarea>
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


<div id="modal_send_sms" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Отправить смс-сообщение?</h4>
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

                                            {*  <button type="button" class="btn btn-success waves-effect waves-light js-send-whatsapp">Whatsapp</button>  *}
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
