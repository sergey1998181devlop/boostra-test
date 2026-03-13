
{$meta_title='Список заявок по рассылке' scope=parent}

{capture name='page_scripts'}

    <script src="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/fancybox3/dist/jquery.fancybox.js"></script>

    <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/orders.js?v=1.06"></script>
    <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/order.js?v=2.18"></script>


{/capture}

{capture name='page_styles'}
    <link href="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet" />
    <link href="design/{$settings->theme|escape}/assets/plugins/fancybox3/dist/jquery.fancybox.css" rel="stylesheet" />

    <link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid.min.css" />
    <link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid-theme.min.css" />
    <style>
        .jsgrid-table { margin-bottom:0}
    </style>
{/capture}

<div class="page-wrapper">
    <!-- ============================================================== -->
    <!-- Container fluid  -->
    <!-- ============================================================== -->
    <div class="container-fluid">
        <!-- ============================================================== -->
        <!-- Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    {if $my_orders}
                        <i class="mdi mdi-animation"></i> Мои заявки
                    {else}
                        <i class="mdi mdi-animation"></i> Заявки по рассылке
                    {/if}
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Заявки по рассылке</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
                {if in_array($manager->role, ['verificator', 'edit_verificator'])}
                    {$pk_percent = ($daily_pk/$settings->verificator_daily_plan_pk*100)|round}
                    {if $pk_percent < 40}{$pk_color='danger'}
                    {elseif $pk_percent < 65}{$pk_color='primary'}
                    {elseif $pk_percent < 85}{$pk_color='info'}
                {else}{$pk_color='success'}{/if}
                {$nk_percent = ($daily_nk/$settings->verificator_daily_plan_nk*100)|round}
                {if $nk_percent < 40}{$nk_color='danger'}
                {elseif $nk_percent < 65}{$nk_color='primary'}
                {elseif $nk_percent < 85}{$nk_color='info'}
            {else}{$nk_color='success'}{/if}

            <div class="row">
                <div class="col-md-6">
                    <div class="card m-0 bg-grey">
                        <div class="card-body">
                            <h5 class="card-title mb-1">Новые клиенты</h5>
                            <div class="row">
                                <div class="col-4">
                                    <strong class="text-{$nk_color}">{$nk_percent}%</strong>
                                </div>
                                <div class="col-8">
                                    <h3 class="font-light mb-0 text-right">
                                        {$daily_nk}/{$settings->verificator_daily_plan_nk}
                                    </h3>
                                </div>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-{$nk_color}" role="progressbar" style="width: {$nk_percent}%; height: 6px;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card m-0 bg-grey">
                        <div class="card-body">
                            <h5 class="card-title mb-1">Повторные клиенты</h5>
                            <div class="row">
                                <div class="col-4">
                                    <strong class="text-{$pk_color}">{$pk_percent}%</strong>
                                </div>
                                <div class="col-8">
                                    <h3 class="font-light mb-0 text-right">
                                        {$daily_pk}/{$settings->verificator_daily_plan_pk}
                                    </h3>
                                </div>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-{$pk_color}" role="progressbar" style="width: {$pk_percent}%; height: 6px;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        {/if}
    </div>
</div>
<!-- ============================================================== -->
<!-- End Bread crumb and right sidebar toggle -->
<!-- ============================================================== -->
<!-- ============================================================== -->
<!-- Start Page Content -->
<!-- ============================================================== -->
<div class="row">
    <div class="col-12">
        <!-- Column -->
        <div class="card">
            <div class="card-body">
                <div class="clearfix">
                    <h4 class="card-title float-left">Список заявок </h4>
                    <div class="float-right js-filter-status">
                        <a href="{if $search['status']==1}{url value=null search=null}{else}{url value=1 search='status'}{/if}" class="btn btn-xs {if $search['status']==1}btn-success{else}btn-outline-info{/if}">Новая</a>
                        <a href="{if $search['status']==2}{url value=null search=null}{else}{url value=2 search='status'}{/if}" class="btn btn-xs {if $search['status']==2}btn-success{else}btn-outline-success{/if}">Одобрена</a>
                        <a href="{if $search['status']==3}{url value=null search=null}{else}{url value=3 search='status'}{/if}" class="btn btn-xs {if $search['status']==3}btn-danger{else}btn-outline-danger{/if}">Отказ</a>
                        <a href="{if $search['status']==5}{url value=null search=null}{else}{url value=5 search='status'}{/if}" class="btn btn-xs {if $search['status']==5}btn-primary{else}btn-outline-primary{/if}">На исправлении</a>
                        <a href="{if $search['status']==6}{url value=null search=null}{else}{url value=6 search='status'}{/if}" class="btn btn-xs {if $search['status']==6}btn-info{else}btn-outline-info{/if}">Исправлена</a>
                        <a href="{if $search['status']==7}{url value=null search=null}{else}{url value=7 search='status'}{/if}" class="btn btn-xs {if $search['status']==7}btn-warning{else}btn-outline-warning{/if}">Ожидание</a>
                        <a href="{if $search['status']==8}{url value=null search=null}{else}{url value=8 search='status'}{/if}" class="btn btn-xs {if $search['status']==8}btn-success{else}btn-outline-success{/if}">Выдан</a>
                        <a href="{if $search['status']==9}{url value=null search=null}{else}{url value=9 search='status'}{/if}" class="btn btn-xs {if $search['status']==9}btn-danger{else}btn-outline-danger{/if}">Не получены</a>
                        <a href="{if $search['status']==10}{url value=null search=null}{else}{url value=10 search='status'}{/if}" class="btn btn-xs {if $search['status']==10}btn-success{else}btn-outline-success{/if}">Не заняты</a>
                        <a href="{if $search['status']==11}{url value=null search=null}{else}{url value=11 search='status'}{/if}" class="btn btn-xs {if $search['status']==11}btn-primary{else}btn-outline-primary{/if}">В работе</a>
                    </div>
                </div>
                <div id="basicgrid" class="jsgrid" style="position: relative; width: 100%;">
                    <div class="jsgrid-grid-header jsgrid-header-scrollbar" style="">
                        <table class="jsgrid-table table table-striped table-hover">
                            <tr class="jsgrid-header-row">
                                <th style="width: 70px;" class="jsgrid-header-cell jsgrid-align-right jsgrid-header-sortable {if $sort == 'order_id_desc'}jsgrid-header-sort jsgrid-header-sort-desc{elseif $sort == 'order_id_asc'}jsgrid-header-sort jsgrid-header-sort-asc{/if}">
                                    {if $sort == 'order_id_asc'}<a href="{url sort='order_id_desc'}">ID</a>
                                    {else}<a href="{url sort='order_id_asc'}">ID</a>{/if}
                                </th>
                                <th style="width: 70px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'date_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'date_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                    {if $sort == 'date_asc'}<a href="{url sort='date_desc'}">Дата</a>
                                    {else}<a href="{url sort='date_asc'}">Дата</a>{/if}
                                </th>
                                <th style="width: 70px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'amount_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'amount_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                    {if $sort == 'amount_asc'}<a href="{url sort='amount_desc'}">Сумма</a>
                                    {else}<a href="{url sort='amount_asc'}">Сумма</a>{/if}
                                </th>
                                <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'period_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'period_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                    {if $sort == 'period_asc'}<a href="{url sort='period_desc'}">Срок</a>
                                    {else}<a href="{url sort='period_asc'}">Срок</a>{/if}
                                </th>
                                <th style="width: 150px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'fio_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'fio_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                    {if $sort == 'fio_asc'}<a href="{url sort='fio_desc'}">ФИО</a>
                                    {else}<a href="{url sort='fio_asc'}">ФИО</a>{/if}
                                </th>
                                {if !in_array($manager->role, ['verificator', 'edit_verificator'])}
                                    <th style="width: 70px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'birth_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'birth_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                        {if $sort == 'birth_asc'}<a href="{url sort='birth_desc'}">Д/Р</a>
                                        {else}<a href="{url sort='birth_asc'}">Д/Р</a>{/if}
                                    </th>
                                {/if}
                                <th style="width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'phone_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'phone_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                    {if $sort == 'phone_asc'}<a href="{url sort='phone_desc'}">Телефон</a>
                                    {else}<a href="{url sort='phone_asc'}">Телефон</a>{/if}
                                </th>
                                <th style="width: 100px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'region_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'region_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                    {if $sort == 'region_asc'}<a href="{url sort='region_desc'}">Регион</a>
                                    {else}<a href="{url sort='region_asc'}">Регион</a>{/if}
                                </th>
                                <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'status_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'status_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                    {if $sort == 'status_asc'}<a href="{url sort='status_desc'}">Статус</a>
                                    {else}<a href="{url sort='status_asc'}">Статус</a>{/if}
                                </th>
                                <th style="width: 70px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'manager_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'manager_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                    {if $sort == 'manager_asc'}<a href="{url sort='manager_desc'}">Менеджер</a>
                                    {else}<a href="{url sort='manager_asc'}">Менеджер</a>{/if}
                                </th>
                                <th style="width: 70px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'utm_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'utm_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                    {if $sort == 'utm_asc'}<a href="{url sort='utm_desc'}">UTM</a>
                                    {else}<a href="{url sort='utm_asc'}">UTM</a>{/if}
                                </th>
                                {if $manager->role == 'verificator_cc'}
                                    <th style="width: 100px;" class="jsgrid-header-cell ">

                                    </th>
                                {/if}

                            </tr>
                            <tr class="jsgrid-filter-row" id="search_form">

                                <td style="width: 70px;" class="jsgrid-cell jsgrid-align-right">
                                    <form method="GET" action="{url}">
                                        <input name='search' value='order_id' type='hidden'>
                                        <input type="text" name="value" value="{$search['order_id']}" class="form-control input-sm">
                                    </form>
                                </td>
                                <td style="width: 70px;" class="jsgrid-cell">
                                    <form method="GET" action="{url}">
                                        <input name='search' value='date' type='hidden'>
                                        <input type="text" name="value" value="{$search['date']}" class="form-control input-sm">
                                    </form>
                                </td>
                                <td style="width: 70px;" class="jsgrid-cell">
                                    <form method="GET" action="{url}">
                                        <input name='search' value='amount' type='hidden'>
                                        <input type="text" name="value" value="{$search['amount']}" class="form-control input-sm">
                                    </form>
                                </td>
                                <td style="width: 60px;" class="jsgrid-cell">
                                    <form method="GET" action="{url}">
                                        <input name='search' value='period' type='hidden'>
                                        <input type="text" name="value" value="{$search['period']}" class="form-control input-sm">
                                    </form>
                                </td>
                                <td style="width: 150px;" class="jsgrid-cell">
                                    <form method="GET" action="{url}">
                                        <input name='search' value='fio' type='hidden'>
                                        <input type="text" name="value" value="{$search['fio']}" class="form-control input-sm">
                                    </form>
                                </td>
                                {if !in_array($manager->role, ['verificator', 'edit_verificator'])}
                                    <td style="width: 70px;" class="jsgrid-cell">
                                        <form method="GET" action="{url}">
                                            <input name='search' value='birth' type='hidden'>
                                            <input type="text" name="value" value="{$search['birth']}" class="form-control input-sm">
                                        </form>
                                    </td>
                                {/if}
                                <td style="width: 80px;" class="jsgrid-cell">
                                    <form method="GET" action="{url}">
                                        <input name='search' value='phone' type='hidden'>
                                        <input type="text" name="value" value="{$search['phone']}" class="form-control input-sm">
                                    </form>
                                </td>
                                <td style="width: 100px;" class="jsgrid-cell">
                                    <form method="GET" action="{url}">
                                        <input name='search' value='region' type='hidden'>
                                        <input type="text" name="value" value="{$search['region']}" class="form-control input-sm">
                                    </form>
                                </td>
                                <td style="width: 60px;" class="jsgrid-cell">
                                    <form method="GET" action="{url}">
                                        <input name='search' value='status' type='hidden'>
                                        <select name="value" class="form-control input-sm" onchange="javascript:this.form.submit()">
                                            <option value=""></option>
                                            {foreach $statuses as $k => $v}
                                                <option value="{$k}" {if $k == $search['status']}selected="true"{/if}>{$v|escape}</option>
                                            {/foreach}
                                        </select>
                                    </form>
                                </td>
                                <td style="width: 70px;" class="jsgrid-cell">
                                    <form method="GET" action="{url}">
                                        <input name='search' value='manager_id' type='hidden'>
                                        <select name="value" class="form-control input-sm"  id="manager_id" onchange="javascript:this.form.submit()">
                                            <option value=""></option>
                                            {foreach $managers as $m}
                                                <option value="{$m->id}" {if $m->id == $search['manager_id']}selected="true"{/if}>{$m->name|escape}</option>
                                            {/foreach}
                                        </select>
                                    </form>
                                </td>
                                <td style="width: 70px;" class="jsgrid-cell">
                                    <form method="GET" action="{url}">
                                        <input name='search' value='utm' type='hidden'>
                                        <input type="text" name="value" value="{$search['utm']}" class="form-control input-sm">
                                    </form>
                                </td>
                                {if $manager->role == 'verificator_cc'}
                                    <td style="width: 100px;" class="jsgrid-cell">
                                    </td>
                                {/if}

                            </tr>
                        </table>
                    </div>
                    <div class="jsgrid-grid-body">
                        <table class="jsgrid-table table table-striped table-hover">
                            <tbody>
                                {foreach $orders as $order}
                                    {if in_array('all_orders', $manager->permissions) || $order->status_1c != '7.Технический отказ'}
                                        <tr class="jsgrid-row js-order-row" {if $order->status == 6}style="border:2px solid green"{/if}>
                                            <td style="width: 70px;" class="jsgrid-cell jsgrid-align-right">
                                                {if in_array($manager->role, ['verificator', 'edit_verificator'])}
                                                    <div class="js-order-notaccepted" {if $order->manager_id}style="display:none"{/if}>
                                                        <span>{$order->id}</span>
                                                    </div>
                                                    <div class="js-order-accepted" {if !$order->manager_id}style="display:none"{/if}>
                                                        <div class="button-toggle-wrapper">
                                                            <button class="button-toggle js-open-order" data-id="{$order->id}" type="button" title="Подробнее"></button>
                                                        </div>
                                                        <a href="order/{$order->id}">{$order->id}</a>
                                                    </div>
                                                {else}
                                                    <div class="button-toggle-wrapper">
                                                        <button class="button-toggle js-open-order" data-id="{$order->id}" type="button" title="Подробнее"></button>
                                                    </div>
                                                    <a href="order/{$order->id}">{$order->id}</a>
                                                {/if}
                                                {if $order->cdoctor_id}
                                                    <div>
                                                        <span class="label label-primary">Д {$order->cdoctor_level}</span>
                                                    </div>
                                                {/if}
                                            </td>
                                            <td style="width: 70px;" class="jsgrid-cell">
                                                {$order->date|date}
                                                {$order->date|time}
                                            </td>
                                            <td style="width: 70px;" class="jsgrid-cell">
                                                {$order->amount}
                                                {if $order->percent > 0}<span class="badge badge-success">{$order->percent*1} %</span>
                                                {else}<span class="badge badge-danger">{$order->percent*1} %</span>{/if}
                                            </td>
                                            <td style="width: 60px;" class="jsgrid-cell">
                                                {$order->period} {$order->period|plural:'день':'дней':'дня'}
                                            </td>
                                            <td style="width: 150px;" class="jsgrid-cell">
                                                <a href="client/{$order->user_id}">
                                                    {$order->lastname|escape}
                                                    {$order->firstname|escape}
                                                    {$order->patronymic|escape}
                                                </a>
                                                {if !in_array($manager->role, ['verificator', 'edit_verificator'])}
                                                    {if $order->first_loan}<span class="label label-primary">Новая</span>
                                                    {elseif $order->have_close_credits}<span class="label label-success">ПК</span>
                                                    {else}<span class="label label-warning">Повтор</span>{/if}
                                                    {if $order->is_user_credit_doctor == 1}<span class="label label-danger">КД</span>{/if}
                                                {/if}
                                            </td>
                                            {if !in_array($manager->role, ['verificator', 'edit_verificator'])}
                                                <td style="width: 70px;" class="jsgrid-cell">
                                                    {$order->birth}
                                                </td>
                                            {/if}
                                            <td style="width: 80px;" class="jsgrid-cell">
                                                {$order->phone_mobile|escape}
                                                <button class="btn waves-effect waves-light btn-xs btn-info js-mango-call" data-phone="{$order->phone_mobile}" data-order="{$order->id}" data-user="{$order->user_id}" title="Выполнить звонок"><i class="fas fa-phone-square"></i></button>
                                            </td>
                                            <td style="width: 100px;" class="jsgrid-cell">
                                                {$order->Regregion|escape}
                                            </td>
                                            <td style="width: 60px;" class="jsgrid-cell">
                                                {foreach $statuses AS $k => $v}
                                                    {if $order->status == $k}<span class="label label-rounded {$styles[$k]}">{$v}</span>{/if}
                                                {/foreach}
                                            </td>
                                            <td style="width: 70px;" class="jsgrid-cell">
                                                <span class="text-warning js-order-manager-{$order->id}">{$managers[$order->manager_id]->name}</span>
                                                {if $order->accept_date}<small>{$order->accept_date|date} {$order->accept_date|time}</small>{/if}
                                            </td>
                                            <td style="width: 70px;" class="jsgrid-cell">
                                                {if $order->utm_source == 'Boostra'}<span class="label label-inverse">
                                                    {elseif $order->utm_source == 'sms'}<span class="label label-primary">
                                                        {elseif $order->utm_source}<span class="label label-warning">{/if}
                                                            {$order->utm_source}
                                                            {if $order->utm_source}</span>{/if}
                                                            {$order->webmaster_id}
                                                        </td>
                                                        {if $manager->role == 'verificator_cc'}
                                                            <td style="width: 100px;" class="jsgrid-cell">
                                                                {if !$order->manager_id}
                                                                    <button type="button" class="btn btn-rounded btn-info js-accept-order-list js-event-add-click" data-event="2" data-manager="{$manager->id}" data-order="{$order->id}" data-user="{$order->user_id}"> <i class="fas fa-hospital-symbol"></i>&nbsp;&nbsp; Принять</button>
                                                                {/if}
                                                            </td>
                                                        {/if}

                                                        </tr>
                                                    {/if}
                                                {/foreach}
                                                </tbody>
                                                </table>
                                                </div>
                                                {include file='tickets/pagination.tpl'}
                                                </div>
                                                </div>
                                                </div>
                                                <!-- Column -->
                                                </div>
                                                </div>
                                                <!-- ============================================================== -->
                                                <!-- End PAge Content -->
                                                <!-- ============================================================== -->
                                                </div>
                                                {include file='footer.tpl'}
                                                </div>
