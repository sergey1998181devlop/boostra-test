{$meta_title='Неполученные займы' scope=parent}

{capture name='page_scripts'}
    <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/orders.js?v=1.06"></script>
    <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/not_received_loans.js"></script>
    <script>
        function download() {
            window.open(
                '{$listingUri}?action=download',
                '_blank'
            );

            return false;
        }
    </script>
{/capture}

{capture name='page_styles'}
    <link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid.min.css" />
    <link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid-theme.min.css" />
    <style>
        .jsgrid-table {
            margin-bottom:0
        }

        @media screen and (min-width: 580px){
            body {
                padding-right: 0px !important;
            }
        }
    </style>
{/capture}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-4 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-sleep"></i>
                    <span id="not-received-loans-span">Неполученные займы</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Неполученные займы</li>
                </ol>
                <div class="col-6 col-md-4 mt-2">
                    <button onclick="return download();" type="button" class="btn btn-success"><i class="ti-save"></i> Выгрузить</button>
                </div>
            </div>
            <div class="col-md-6 col-8 align-right">
                <div class="bg-grey p-2">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <h4>Всего</h4>
                            <h1>{$statistic->totals}</h1>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4>Взято в работу</h4>
                            <h1>{$statistic->in_progress}</h1>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4>Завершено из взятых в работу</h4>
                            <h1>{$statistic->completed}</h1>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4>Конверсия</h4>
                            <h1>{$statistic->conversion} %</h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Неполученные займы</h4>
                        <div id="basicgrid" class="jsgrid" style="position: relative; width: 100%;">
                            <div class="preloader-table" style="display: none; z-index: 9; height: 100%;position: absolute;width: 100%;background:rgba(256,256,256,0.5)">
                                <svg class="circular" viewBox="25 25 50 50">
                                    <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"></circle> </svg>
                            </div>
                            <div class="jsgrid-grid-header jsgrid-grid-body">
                                <table class="jsgrid-table table table-striped table-hover">
                                    {* Headers *}
                                    <tr class="jsgrid-header-row">
                                        <th style="width: 100px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'approve_date_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'approve_date_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'approve_date_asc'}<a href="{url page=null sort='approve_date_desc'}">Дата одобрения</a>
                                            {else}<a href="{url page=null sort='approve_date_asc'}">Дата одобрения</a>{/if}
                                        </th>
                                        <th style="width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'approve_period_date_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'approve_period_date_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'approve_period_date_asc'}<a href="{url page=null sort='approve_period_date_desc'}">Срок действия одобрения</a>
                                            {else}<a href="{url page=null sort='approve_period_date_asc'}">Срок действия одобрения</a>{/if}
                                        </th>
                                        <th style="width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'last_lk_visit_time_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'last_lk_visit_time_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'last_lk_visit_time_asc'}<a href="{url page=null sort='last_lk_visit_time_desc'}">Последний вход в ЛК</a>
                                            {else}<a href="{url page=null sort='last_lk_visit_time_asc'}">Последний вход в ЛК</a>{/if}
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
                                                <a href="{url page=null sort='timezone_desc'}">Время клиента</a>
                                            {else}
                                                <a href="{url page=null sort='timezone_asc'}">Время клиента</a>
                                            {/if}
                                        </th>
                                        <th style="width: 100px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'auto_approve_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'auto_approve_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'auto_approve_asc'}<a href="{url page=null sort='auto_approve_desc'}">Автоодобрение</a>
                                            {else}<a href="{url page=null sort='auto_approve_asc'}">Автоодобрение</a>{/if}
                                        </th>
                                        <th style="width: 100px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'manager_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'not_received_loand_manager_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'not_received_loand_manager_asc'}<a href="{url page=null sort='not_received_loand_manager_desc'}">Ответственный</a>
                                            {else}<a href="{url page=null sort='not_received_loand_manager_asc'}">Ответственный</a>{/if}
                                        </th>
                                        <th style="width: 100px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'last_call_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'last_call_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'last_call_asc'}<a href="{url page=null sort='last_call_desc'}">Время контакта</a>
                                            {else}<a href="{url page=null sort='last_call_asc'}">Время контакта</a>{/if}
                                        </th>
                                        <th style="width: 140px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'call_status_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'call_status_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'call_status_asc'}<a href="{url page=null sort='call_status_desc'}">Статус звонка</a>
                                            {else}<a href="{url page=null sort='call_status_asc'}">Статус звонка</a>{/if}
                                        </th>
                                        <th style="width:110px" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'will_client_receive_loan_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'will_client_receive_loan_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'will_client_receive_loan_asc'}<a href="{url page=null sort='will_client_receive_loan_desc'}">Клиент получит займ</a>
                                            {else}<a href="{url page=null sort='will_client_receive_loan_asc'}">Клиент получит займ</a>{/if}</th>
                                    </tr>

                                    {* Filters *}
                                    <tr class="jsgrid-filter-row" id="search_form">
                                        <td style="width: 100px;" class="jsgrid-cell jsgrid-align-right">
                                            <input type="hidden" name="sort" value="{$sort}" />
                                            <input type="text" name="approve_date" value="{$search['approve_date']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 100px;" class="jsgrid-cell jsgrid-align-right">
                                            <input type="text" name="approve_period_date" value="{$search['approve_period_date']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 80px;" class="jsgrid-cell jsgrid-align-right"></td>
                                        <td style="width: 80px;" class="jsgrid-cell jsgrid-align-right">
                                            <input type="text" name="fio" value="{$search['fio']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 80px;" class="jsgrid-cell">
                                            <input type="text" name="phone" value="{$search['phone']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 80px;" class="jsgrid-cell"></td>
                                        <td style="width: 80px;" class="jsgrid-cell"> </td>
                                        <td style="width: 80px;" class="jsgrid-cell">
                                            <select class="form-control-sm form-control" name="not_received_loan_manager_id">
                                                <option value=""></option>
                                                {foreach $managers as $m}
                                                    <option value="{$m->id}" {if $search['not_received_loan_manager_id'] == $m->id}selected{/if}>{$m->name|escape}</option>
                                                {/foreach}
                                            </select>
                                        </td>
                                        <td style="width: 60px;" class="jsgrid-cell"></td>
                                        <td style="width: 100px;" class="jsgrid-cell"></td>
                                        <td style="width: 100px;" class="jsgrid-cell">
                                            <select class="form-control-sm form-control" name="will_client_receive_loan">
                                                    <option {if empty($search['will_client_receive_loan_search'])}selected{/if} value=""></option>
                                                    {foreach Orders::WILL_CLIENT_RECEIVE_LOAN_MAP as $value => $name}
                                                        <option value="{$value}" {if null !== $search['will_client_receive_loan'] && $value == $search['will_client_receive_loan']}selected{/if}>{$name}</option>
                                                    {/foreach}
                                            </select>
                                        </td>
                                    </tr>
                                    <tbody>

                                    {* Data *}
                                    {foreach $orders as $order}
                                        <tr class="jsgrid-row">
                                            <td style="width: 100px;" class="jsgrid-cell jsgrid-align-right">
                                                {$order->approve_date}
                                            </td>
                                            <td style="width: 80px;" class="jsgrid-cell">
                                                {$order->approve_period_date}
                                            </td>
                                            <td style="width: 80px;" class="jsgrid-cell">
                                                {$order->last_lk_visit_time}
                                            </td>
                                            <td style="width: 120px;" class="jsgrid-cell">
                                                <a href="client/{$order->user_id}">
                                                    {$order->lastname|escape}
                                                    {$order->firstname|escape}
                                                    {$order->patronymic|escape}
                                                </a>
                                            </td>
                                            <td style="width: 100px;" class="jsgrid-cell">
                                                <span class="phone-cell">{$order->phone_mobile|escape}</span>
                                                <button
                                                        class="btn waves-effect waves-light btn-xs btn-success js-voximplant-call {if !$order->not_received_loan_manager_id}js-set-manager{/if}"
                                                        data-order="{$order->order_id}"
                                                        data-phone="{$order->phone_mobile}"
                                                        data-listing_type="not_received_loans"
                                                        title="Выполнить звонок">
                                                    <i class="fas fa-phone-square"></i>
                                                </button>
                                            </td>
                                            <td style="width: 100px;" class="jsgrid-cell">{if $order->timezone !== NULL }{$order->timezone} {else} нет данных{/if}</td>
                                            <td style="width: 150px;" class="js-autoupdate jsgrid-cell stage-cell">
                                                {$order->auto_approve}
                                            </td>
                                            <td style="width: 100px;" class="jsgrid-cell manager{$order->order_id}">
                                                <span class="js-not-received-loan-manager-name">{$managers[$order->not_received_loan_manager_id]->name|escape}</span>
                                            </td>
                                            <td style="width: 100px;" class="jsgrid-cell last-call-cell" data-client-id="{$order->user_id}">
                                                {$last_calls[$order->user_id]->created}
                                            </td>
                                            <td style="width: 140px;" class="jsgrid-cell text-right">
                                                <form class="common_save_field_form" action="/client/{$order->user_id}">
                                                    <select name="call_status" class="form-control field-to-save">
                                                        <option {if null === $order->call_status}selected{/if} hidden></option>

                                                        {foreach Users::CALL_STATUS_MAP as $status_value => $status_name}
                                                            <option value="{$status_value}" {if null !== $order->call_status && $status_value == $order->call_status}selected{/if}>{$status_name}</option>
                                                        {/foreach}
                                                    </select>
                                                </form>
                                            </td>
                                            <td style="width: 100px;" class="jsgrid-cell">
                                                <form class="common_save_order_field_form" action="/order/{$order->order_id}">
                                                    <select name="will_client_receive_loan" class="form-control field-to-save">
                                                        <option {if $order->will_client_receive_loan == null}selected{/if} hidden></option>
                                                        {foreach Orders::WILL_CLIENT_RECEIVE_LOAN_MAP as $will_client_receive_loan_value => $will_client_receive_loan_name}
                                                            <option value="{$will_client_receive_loan_value}"
                                                                    {if $order->will_client_receive_loan !== null && $will_client_receive_loan_value == $order->will_client_receive_loan}selected{/if}>
                                                                {$will_client_receive_loan_name}
                                                            </option>
                                                        {/foreach}
                                                    </select>
                                                </form>
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
            </div>
        </div>
    </div>
    {include file='footer.tpl'}
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
