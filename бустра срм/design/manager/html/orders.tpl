{$meta_title='Список заявок' scope=parent}

{capture name='page_scripts'}

    <script src="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/fancybox3/dist/jquery.fancybox.js"></script>

    <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/orders.js?v=1.06"></script>
    <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/order.js?v=2.18"></script>

    <script type="text/javascript">
        // Обработка чекбокса "Показывать тестовые заявки" для верификаторов
        $(document).ready(function() {
            const checkbox = $('#show_test_orders_checkbox');
            if (checkbox.length) {
                checkbox.on('change', function() {
                    const showTest = this.checked ? 1 : 0;
                    document.cookie = 'show_test_orders=' + showTest + '; path=/';
                    window.location.reload();
                });
            }

            $(document).on('click', '.js-autoapprove-filter', function (e) {
                e.preventDefault();

                var $formRow = $('#search_form');

                $formRow.find('input[type="text"]').val('');
                $formRow.find('select').prop('selectedIndex', 0);

                var url = new URL(window.location.href);
                var params = url.searchParams;

                var searchFields = [
                    'order_id',
                    'date',
                    'amount',
                    'fio',
                    'birth',
                    'phone',
                    'region',
                    'status',
                    'reason',
                    'manager_id',
                    'utm',
                    'client_utm_source'
                ];

                searchFields.forEach(function (field) {
                    params.delete('search[' + field + ']');
                });

                [
                    'order_id',
                    'date',
                    'amount',
                    'fio',
                    'birth',
                    'phone',
                    'region',
                    'status',
                    'reason',
                    'manager_id',
                    'client_utm_source'
                ].forEach(function (field) {
                    params.delete(field);
                });

                params.delete('page');

                url.search = params.toString();

                window.location.href = url.toString();
            });
        });
        $(document).on('click', '.js-site-tab-filter', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            var site = $(this).data('site');

            var url = new URL(window.location.href);
            var params = url.searchParams;

            $('#search_form').find('input, select').each(function () {
                var name = $(this).attr('name');
                if (!name) return;

                if (name === 'sort') return;

                if (this.tagName.toLowerCase() === 'select') {
                    $(this).val('');
                } else {
                    $(this).val('');
                }

                params.delete(name);
                params.delete('search[' + name + ']');
            });

            if (site === 'all') {
                params.delete('site_id');
            } else {
                params.set('site_id', site);
            }

            params.delete('page');

            url.search = params.toString();
            window.location.href = url.toString();
        });
    </script>

{/capture}

{capture name='page_styles'}
    <link href="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet" />
    <link href="design/{$settings->theme|escape}/assets/plugins/fancybox3/dist/jquery.fancybox.css" rel="stylesheet" />

    <link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid.min.css" />
    <link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid-theme.min.css" />
    <style>
        .jsgrid-table { margin-bottom:0}

        .btn-outline-wait {
            color: #F4F6F7;
            background-color: transparent;
            border-color: #F4F6F7;
        }

        .btn-wait, .btn-outline-wait:hover, .btn-outline-wait:focus {
            background: #F4F6F7;
            border: 1px solid #F4F6F7;
            color: #333;
        }
        .btn-wait:hover {
            background: #F7F9F9;
            border: 1px solid #F7F9F9;
            color: #444;
        }



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
            <div class="col-md-5 col-6 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    {if $my_orders}
                    <i class="mdi mdi-animation"></i> Мои заявки
                    {else}
                    <i class="mdi mdi-animation"></i> Заявки
                    {/if}
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Заявки</li>
                </ol>
            </div>

            <div class="col-md-2 col-3">
                {if $pagespeed}
                <h6><a href="javascript:void(0);" onclick="$('#pagespeed').slideToggle()">Загрузка: {($pagespeed['end'] - $pagespeed['start'])} c</a></h6>
                <ul id="pagespeed" class="text-white mb-0" style="display:none">
                    {$start_point = $pagespeed|first}
                    {foreach $pagespeed as $pagespeed_key => $pagespeed_point}
                        {if $pagespeed_key != 'start'}
                        <li>{$pagespeed_key}: {($pagespeed_point - $pagespeed['start'])} c</li>
                        {/if}
                    {/foreach}
                </ul>
                {/if}
            </div>
            
            <div class="col-md-5 col-3 align-self-center">
                {if in_array($manager->role, ['verificator', 'edit_verificator'])}
                    {include file='tickets/verificator_statistic.tpl'}
                {/if}
                {if in_array('chief_verificator', $manager->permissions)}
                    {include file='tickets/chief_verificator_statistic.tpl'}
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
                            <h4 class="card-title">Список заявок </h4>
                            <div class="js-filter-status clearfix mb-3">
                                {include file='site_tabs_filter.tpl'}
                                <div class="float-left my-3">
                                    <div class="btn-group" role="group">
                                        <a href="{if $filter_client=='nk'}{url filter_client=null page=null}{else}{url filter_client='nk' page=null}{/if}" class="btn btn-xs {if $filter_client=='nk'}btn-warning{else}btn-outline-warning{/if}">НК</a>
                                        <a href="{if $filter_client=='pk'}{url filter_client=null page=null}{else}{url filter_client='pk' page=null}{/if}" class="btn btn-xs {if $filter_client=='pk'}btn-success{else}btn-outline-success{/if}">ПК</a>
                                    </div>
                                    <div class="btn-group ml-4" role="group">
                                        <a href="{if $filter_loan_type=='PDL'}{url filter_loan_type=null page=null}{else}{url filter_loan_type='PDL' page=null}{/if}" class="btn btn-xs {if $filter_loan_type=='PDL'}btn-primary{else}btn-outline-primary{/if}">PDL</a>
                                        <a href="{if $filter_loan_type=='IL'}{url filter_loan_type=null page=null}{else}{url filter_loan_type='IL' page=null}{/if}" class="btn btn-xs {if $filter_loan_type=='IL'}btn-info{else}btn-outline-info{/if}">IL</a>
                                        <a href="{if $filter_loan_type=='RCL'}{url filter_loan_type=null page=null}{else}{url filter_loan_type='RCL' page=null}{/if}" class="btn btn-xs {if $filter_loan_type=='RCL'}btn-success{else}btn-outline-success{/if}">ВКЛ</a>
                                    </div>
                                    {if $is_verificator_role}
                                        <div class="ml-4 d-inline-block">
                                            <label class="custom-control custom-checkbox mb-0" style="line-height: 30px;">
                                                <input type="checkbox" class="custom-control-input" id="show_test_orders_checkbox" {if $show_test_orders}checked{/if}>
                                                <span class="custom-control-label" style="cursor: pointer;">Показывать тестовые заявки</span>
                                            </label>
                                        </div>
                                    {/if}
                                    <div class="btn-group ml-4 js-organization-filter" role="group">
                                        {foreach $organizations as $org}
                                            {if in_array($org->id, $boostra_organizations_for_filter)}

                                                {assign var=org_site value='boostra'}

                                                {if $org->short_name == 'ООО МКК «Форинт»'}
                                                    {assign var=org_site value='neomani'}
                                                {/if}

                                                {capture assign=org_href}{if $filter_organization_id==$org->id}{url organization_id=null page=null site_id=$org_site}{else}{url organization_id=$org->id page=null site_id=$org_site}{/if}{/capture}

                                                <a href="{$org_href|trim}"
                                                   class="btn btn-xs {if $filter_organization_id==$org->id}btn-info{else}btn-outline-info{/if}">
                                                    {$org->short_name|escape}
                                                </a>
                                            {/if}
                                        {/foreach}
                                    </div>
                                    <div class="btn-group ml-2 js-organization-filter" role="group">
                                        {foreach $organizations as $org}
                                            {if in_array($org->id, $soyaplace_organizations_for_filter)}
                                                <a href="{if $filter_organization_id==$org->id}{url organization_id=null page=null site_id="soyaplace"}{else}{url organization_id=$org->id page=null site_id="soyaplace"}{/if}" class="btn btn-xs {if $filter_organization_id==$org->id}btn-warning{else}btn-outline-warning{/if}">{$org->short_name|escape}</a>
                                            {/if}
                                        {/foreach}
                                    </div>
                                </div>
                                <div class="float-right">
                                    <a href="{if $filter_status=='approve'}{url status=null page=null}{else}{url status='approve' page=null}{/if}" class="btn btn-xs {if $filter_status=='approve'}btn-success{else}btn-outline-success{/if}">Одобрена</a>
                                    <a href="{if $filter_status=='failed_to_issue'}{url status=null page=null}{else}{url status='failed_to_issue' page=null}{/if}" class="btn btn-xs {if $filter_status=='failed_to_issue'}btn-danger{else}btn-outline-danger{/if}">Не удалось выдать</a>
                                    <a href="{if $filter_status==3}{url status=null page=null}{else}{url status=3 page=null}{/if}" class="btn btn-xs {if $filter_status==3}btn-danger{else}btn-outline-danger{/if}">Отказ</a>
                                    <a href="{if $filter_status==5}{url status=null page=null}{else}{url status=5 page=null}{/if}" class="btn btn-xs {if $filter_status==5}btn-primary{else}btn-outline-primary{/if}">На исправлении</a>
                                    <a href="{if $filter_status==6}{url status=null page=null}{else}{url status=6 page=null}{/if}" class="btn btn-xs {if $filter_status==6}btn-info{else}btn-outline-info{/if}">Исправлена</a>
                                    <a href="{if $filter_status==7}{url status=null page=null}{else}{url status=7 page=null}{/if}" class="btn btn-xs {if $filter_status==7}btn-wait{else}btn-outline-wait{/if}">Ожидание</a>
                                    <a href="{if $filter_status==8}{url status=null page=null}{else}{url status=8 page=null}{/if}" class="btn btn-xs {if $filter_status==8}btn-warning{else}btn-outline-warning{/if}">Подписан</a>
                                    <a href="{if $filter_status==8}{url status=null page=null}{else}{url status=18 page=null}{/if}" class="btn btn-xs {if $filter_status==8}btn-warning{else}btn-outline-secondary{/if}">Ожидание ПДН</a>
                                    <a href="{if $filter_status==13}{url status=null page=null}{else}{url status=13 page=null}{/if}" class="btn btn-xs {if $filter_status==13}btn-secondary{else}btn-outline-secondary{/if}">Отложена</a>
                                    <a href="{if $filter_status=='issued'}{url status=null page=null}{else}{url status='issued' page=null}{/if}" class="btn btn-xs {if $filter_status=='issued'}btn-success{else}btn-outline-success{/if}">Выдан</a>
                                    <a href="{if $filter_status=='notreceived'}{url status=null page=null}{else}{url status='notreceived' page=null}{/if}" class="btn btn-xs {if $filter_status=='notreceived'}btn-danger{else}btn-outline-danger{/if}">Не получены</a>
                                    <a href="{if $filter_status=='notbusy'}{url status=null page=null}{else}{url status='notbusy' page=null}{/if}" class="btn btn-xs {if $filter_status=='notbusy'}btn-success{else}btn-outline-success{/if}">Не заняты</a>
                                    <a href="{if $filter_status=='inwork'}{url status=null page=null}{else}{url status='inwork' page=null}{/if}" class="btn btn-xs {if $filter_status=='inwork'}btn-primary{else}btn-outline-primary{/if}">В работе</a>
                                    <a href="{if $filter_utm=='autoapprove'}{url utm=null page=null}{else}{url utm='autoapprove' page=null}{/if}"
                                       class="btn btn-xs js-autoapprove-filter {if $filter_utm=='autoapprove'}btn-success{else}btn-outline-success{/if}">
                                        Автоодобрения
                                    </a>
                                    {if $filter_status}
                                    <input type="hidden" value="{$filter_status}" id="filter_status" />
                                    {/if}
                                </div>    
                            </div>
                            
                        </div>
                        <div id="basicgrid" class="jsgrid" style="position: relative; width: 100%;">
                            <div class="jsgrid-grid-header jsgrid-header-scrollbar">
                                <table class="jsgrid-table table table-striped table-hover">
                                    <tr class="jsgrid-header-row">
                                        <th style="width: 70px;" class="jsgrid-header-cell jsgrid-align-right jsgrid-header-sortable {if $sort == 'order_id_desc'}jsgrid-header-sort jsgrid-header-sort-desc{elseif $sort == 'order_id_asc'}jsgrid-header-sort jsgrid-header-sort-asc{/if}">
                                            {if $sort == 'order_id_asc'}<a href="{url page=null sort='order_id_desc'}">ID</a>
                                            {else}<a href="{url page=null sort='order_id_asc'}">ID</a>{/if}
                                        </th>
                                        <th style="width: 70px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'date_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'date_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'date_asc'}<a href="{url page=null sort='date_desc'}">Дата</a>
                                            {else}<a href="{url page=null sort='date_asc'}">Дата</a>{/if}
                                        </th>
                                        <th style="width: 70px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'amount_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'amount_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'amount_asc'}<a href="{url page=null sort='amount_desc'}">Сумма</a>
                                            {else}<a href="{url page=null sort='amount_asc'}">Сумма</a>{/if}
                                        </th>
                                        <th style="width: 150px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'fio_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'fio_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'fio_asc'}<a href="{url page=null sort='fio_desc'}">ФИО</a>
                                            {else}<a href="{url page=null sort='fio_asc'}">ФИО</a>{/if}
                                        </th>
                                        {if !in_array($manager->role, ['verificator', 'edit_verificator'])}
                                        <th style="width: 70px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'birth_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'birth_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'birth_asc'}<a href="{url page=null sort='birth_desc'}">Д/Р</a>
                                            {else}<a href="{url page=null sort='birth_asc'}">Д/Р</a>{/if}
                                        </th>
                                        {/if}
                                        <th style="width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'phone_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'phone_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'phone_asc'}<a href="{url page=null sort='phone_desc'}">Телефон</a>
                                            {else}<a href="{url page=null sort='phone_asc'}">Телефон</a>{/if}
                                        </th>
                                        <th style="width: 100px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'region_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'region_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'region_asc'}<a href="{url page=null sort='region_desc'}">Регион</a>
                                            {else}<a href="{url page=null sort='region_asc'}">Регион</a>{/if}
                                        </th>
                                        <th style="width: 70px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'status_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'status_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'status_asc'}<a href="{url page=null sort='status_desc'}">Статус</a>
                                            {else}<a href="{url page=null sort='status_asc'}">Статус</a>{/if}
                                        </th>
                                        {if !in_array($manager->role, ['verificator', 'edit_verificator'])}
                                        <th style="width: 70px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'reason_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'reason_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'reason_asc'}<a href="{url page=null sort='reason_desc'}">Статус</a>
                                            {else}<a href="{url page=null sort='reason_asc'}">Причина отказа</a>{/if}
                                        </th>
                                        {/if}
                                        <th style="width: 70px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'manager_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'manager_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'manager_asc'}<a href="{url page=null sort='manager_desc'}">Менеджер</a>
                                            {else}<a href="{url page=null sort='manager_asc'}">Менеджер</a>{/if}
                                        </th>
                                        <th style="width: 70px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'utm_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'utm_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'utm_asc'}<a href="{url page=null sort='utm_desc'}">UTM Заявки</a>
                                            {else}<a href="{url page=null sort='utm_asc'}">UTM Заявки</a>{/if}
                                        </th>
                                        <th style="width: 70px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'client_utm_source_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'client_utm_source_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'client_utm_source_asc'}<a href="{url page=null sort='client_utm_source_desc'}">UTM Клиента</a>
                                            {else}<a href="{url page=null sort='client_utm_source_asc'}">UTM Клиента</a>{/if}
                                        </th>
                                        {if in_array($manager->role, ['verificator', 'edit_verificator','chief_verificator'])}
                                        <th style="width: 100px;" class="jsgrid-header-cell ">
                                            
                                        </th>
                                        {/if}
                                        {if !in_array($manager->role, ['verificator', 'edit_verificator'])}
                                        <th style="width: 100px;" class="jsgrid-header-cell {if $sort == 'scoring_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'scoring_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'scoring_asc'}<a href="javascript:void(0);">Скоринг</a>
                                            {else}<a href="javascript:void(0);">Скоринг</a>{/if}
                                        </th>
                                        {/if}
                                    </tr>
                                    <tr class="jsgrid-filter-row" id="search_form">
                                    
                                        <td style="width: 70px;" class="jsgrid-cell jsgrid-align-right">
                                            <input type="hidden" name="sort" value="{$sort}" />
                                            <input type="text" name="order_id" value="{$search['order_id']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 70px;" class="jsgrid-cell">
                                            <input type="text" name="date" value="{$search['date']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 70px;" class="jsgrid-cell">
                                            <input type="text" name="amount" value="{$search['amount']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 150px;" class="jsgrid-cell">
                                            <input type="text" name="fio" value="{$search['fio']}" class="form-control input-sm">
                                        </td>
                                        {if !in_array($manager->role, ['verificator', 'edit_verificator'])}
                                        <td style="width: 70px;" class="jsgrid-cell">
                                            <input type="text" name="birth" value="{$search['birth']}" class="form-control input-sm">
                                        </td>
                                        {/if}
                                        <td style="width: 80px;" class="jsgrid-cell">
                                            <input type="text" name="phone" value="{$search['phone']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 100px;" class="jsgrid-cell">
                                            <input type="text" name="region" value="{$search['region']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 70px;" class="jsgrid-cell">
                                            <input type="text" name="status" value="{$search['status']}" class="form-control input-sm">
                                        </td>
                                        {if !in_array($manager->role, ['verificator', 'edit_verificator'])}
                                        <td style="width: 70px;" class="jsgrid-cell">
                                            <select name="reason" class="form-control input-sm">
                                                <option value=""></option>
                                                {foreach $reasons as $r}
                                                <option value="{$r->id}" {if $r->id == $search['reason']}selected="true"{/if}>
                                                    {if $manager->role == 'verificator_minus'}
                                                        {$r->client_name|escape}
                                                    {else}
                                                        {$r->admin_name|escape}
                                                    {/if}
                                                </option>
                                                {/foreach}
                                                <option value="is_null">Без причины отказа</option>
                                            </select>
                                        </td>
                                        {/if}
                                        <td style="width: 70px;" class="jsgrid-cell">
                                            <select name="manager_id" class="form-control input-sm">
                                                <option value=""></option>
                                                {foreach $managers as $m}
                                                <option value="{$m->id}" {if $m->id == $search['manager_id']}selected="true"{/if}>{$m->name|escape}</option>
                                                {/foreach}
                                            </select>
                                        </td>
                                        <td style="width: 70px;" class="jsgrid-cell">
                                            <input type="text" name="utm" value="{$search['utm']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 70px;" class="jsgrid-cell">
                                            <input type="text" name="client_utm_source" value="{$search['client_utm_source']}" class="form-control input-sm">
                                        </td>
                                        {if in_array($manager->role, ['verificator', 'edit_verificator','chief_verificator'])}
                                        <td style="width: 100px;" class="jsgrid-cell">
                                        </td>
                                        {/if}
                                        {if !in_array($manager->role, ['verificator', 'edit_verificator'])}
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
                                        <tr class="jsgrid-row js-order-row" {if $order->status == 6}style="border:2px solid green"{/if} data-site_id="{$order->site_id|escape}">
                                            <td style="width: 70px;" class="jsgrid-cell jsgrid-align-right">
                                                <div class="small text-themecolor">{$order->site_id}</div>
                                                <div class="{if $order->utm_source=='cross_order'}text-danger{else}text-success{/if}">{$organizations[$order->organization_id]->short_name|escape}</div>
                                                {if in_array($manager->role, ['verificator', 'edit_verificator'])}
                                                    <div class="js-order-notaccepted" {if $order->manager_id}style="display:none"{/if}>
                                                        <span>{$order->order_id}</span>
                                                    </div>
                                                    <div class="js-order-accepted" {if !$order->manager_id}style="display:none"{/if}>
                                                        <a href="order/{$order->order_id}">{$order->order_id}</a>
                                                    </div>
                                                {else}
                                                <a href="order/{$order->order_id}">{$order->order_id}</a>
                                                {/if}
                                                {if $order->cdoctor_id}
                                                <div>
                                                    <span class="label label-primary">Д {$order->cdoctor_level}</span>
                                                </div>
                                                {/if}
                                                {if $order->autoretry == 2}
                                                <span class="label label-success">АвтоDBrain</span>
                                                {elseif $order->autoretry}
                                                <span class="label label-primary">Автоповтор</span>
                                                {/if} 
                                                {if $order->loan_type == 'IL'}
                                                    <span class="label label-info">IL</span>
                                                {else}
                                                    {if $order->order_data['rcl_loan']}
                                                        <span class="label label-success">ВКЛ</span>
                                                    {/if}
                                                    <span class="label label-primary">PDL</span>
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
                                                {if $manager->role != 'verificator_minus'}
                                                    {if $order->is_user_credit_doctor}<br /><span class="label label-danger">КД</span>{/if}
                                                {/if}
                                                {if $order->b2p}
                                                    <span class="badge badge-info">B2P</span>
                                                {else}
                                                    <span class="badge badge-warning">TIN</span>
                                                {/if}
                                            </td>
                                            <td style="width: 150px;" class="jsgrid-cell">
                                                <a href="client/{$order->user_id}">
                                                {$order->lastname|escape} 
                                                {$order->firstname|escape} 
                                                {$order->patronymic|escape}
                                                </a>
                                                {if $order->first_loan}<span class="label label-primary">Новая</span>
                                                {elseif $order->have_close_credits}<span class="label label-success">ПК</span>
                                                {else}<span class="label label-warning">Повтор</span>{/if}
                                                {if $order->is_discount_way}<span class="label label-warning">Скидка</span>{/if}
                                                
                                                {if $order->is_default_way}<span class="label label-primary">Общие условия</span>{/if}

                                                {*if $order->service_insurance == 0}<span class="label badge-secondary">Без страховки</span>{/if*}
                                                {if $order->utm_source == 'crm_auto_approve'}<span class="label label-light-danger text-center">Авто-одобрение</span>{/if}
                                                {if $order->utm_source == 'divide_order'}
                                                    <span class="label label-danger text-center">Автоодобрение- разделения</span>
                                                {/if}
                                                {if $order->is_main_divide_order}
                                                    <span class="label label-danger text-center">Разделенная - главная</span>
                                                {/if}
                                            </td>
                                            {if !in_array($manager->role, ['verificator', 'edit_verificator'])}
                                            <td style="width: 70px;" class="jsgrid-cell">
                                                {$order->birth|escape}
                                            </td>
                                            {/if}
                                            <td style="width: 80px;" class="jsgrid-cell">
                                                {$order->phone_mobile|escape}
                                                <button class="btn waves-effect waves-light btn-xs btn-info js-mango-call" data-phone="{$order->phone_mobile}" data-order="{$order->order_id}" data-user="{$order->user_id}" title="Выполнить звонок"><i class="fas fa-phone-square"></i></button>
                                                {if empty($order->blockcalls)}
                                                <button
                                                        class="btn waves-effect waves-light btn-xs btn-success js-voximplant-call"
                                                        data-phone="{$order->phone_mobile}">
                                                    <i class="fas fa-phone-square"></i>

                                                </button>
                                                {/if}
                                            </td>
                                            <td style="width: 100px;" class="jsgrid-cell">
                                                {$order->Regregion|escape}
                                            </td>
                                            <td style="width: 70px;" class="jsgrid-cell">
                                                {$order->status_1c}
                                
                                                {if $order->status == 0}<span class="label label-rounded label-inverse">Заполнение</span>{/if}
                                                {if $order->status == 1}<span class="label label-rounded label-info">Новая</span>{/if}
                                                {if $order->status == 2}<span class="label label-rounded label-success">Одобрена</span>{/if}
                                                {if $order->status == 3}<span class="label label-rounded label-danger">Отказ</span>{/if}
                                                {if $order->status == 4}<span class="label label-rounded label-warning">Отказался сам</span>{/if}
                                                {if $order->status == 5}<span class="label label-rounded label-inverse">На исправлении</span>{/if}
                                                {if $order->status == 6}<span class="label label-rounded label-info">Исправлена</span>{/if}
                                                {if $order->status == 7}<span class="label label-rounded label-warning">Ожидание</span>{/if}

                                                {if $order->status == 8}<span class="label label-rounded label-info">Подписан</span>{/if}
                                                {if $order->status == 9}<span class="label label-rounded label-info">Готов к выдаче</span>{/if}
                                                {if $order->status == 10}<span class="label label-rounded label-primary">Выдан</span>{/if}
                                                {if $order->status == 11}<span class="label label-rounded label-danger">Не удалось выдать</span>{/if}
                                                {if $order->status == 12}<span class="label label-rounded label-success">Закрыт</span>{/if}
                                                {if $order->status == 13}<span class="label label-rounded label-warning">Выдача отложена</span>{/if}
                                                {if $order->status == 14}<span class="label label-rounded label-success">Предварительно одобрена</span>{/if}
                                                {if $order->status == 15}<span class="label label-rounded label-warning">Автоподписание</span>{/if}
                                                {if $order->status == 17}<span class="label label-rounded label-success">Охлаждение</span>{/if}
                                                {if $order->status == 18}<span class="label label-rounded label-inverse">Ожидание ПДН</span>{/if}

                                                {if !empty($order->order_data['leadgid_scorista_reject'])}<small class="text-danger">По настройке</small>{/if}

                                                {if $order->processing_time}
                                                    <div class="">
                                                        <small class="text-warning">{($order->processing_time/60)|round} мин</small>
                                                    </div>
                                                {/if}
                                                
                                            </td>
                                            {if !in_array($manager->role, ['verificator', 'edit_verificator'])}
                                            <td style="width: 70px;" class="jsgrid-cell">
                                                <small>
                                                    {if $manager->role == 'verificator_minus'}
                                                        {$reasons[$order->reason_id]->client_name|escape}
                                                    {else}
                                                        {$reasons[$order->reason_id]->admin_name|escape}
                                                    {/if}
                                                </small>
                                            </td>
                                            {/if}
                                            <td style="width: 70px;" class="jsgrid-cell">
                                                <span class="text-warning js-order-manager-{$order->order_id}">{$managers[$order->manager_id]->name}</span>
                                                {if $order->accept_date}<small>{$order->accept_date|date} {$order->accept_date|time}</small>{/if}
                                            </td>
                                            <td style="width: 70px;" class="jsgrid-cell">
                                                {if $order->utm_source == 'Boostra'}<span class="label label-inverse">{$order->utm_source|escape}</span>
                                                {elseif $order->utm_source == 'sms' && $order->webmaster_id=='7777'}<span class="label label-primary">Акция СД</span>
                                                {elseif $order->utm_source == 'sms'}<span class="label label-primary">lvtraff</span>
                                                {elseif $order->utm_source == 'cf'}<span class="label label-warning">ЦФ</span>
                                                {elseif $order->utm_source == 'crm_auto_approve'}<span class="label label-inverse">auto_approve</span>
                                                {elseif $order->utm_source}<span class="label label-warning">{$order->utm_source|escape} </span><br />{$order->webmaster_id}
                                                {/if}
                                                <br />
                                                <strong>{$order->utm_term|escape}</strong>
                                            </td>
                                            <td style="width: 70px;" class="jsgrid-cell">
                                                <span class="label label-inverse">{$order->client_utm_source|escape}</span>
                                            </td>
                                            {if in_array($manager->role, ['verificator', 'edit_verificator','chief_verificator'])}
                                            <td style="width: 100px;" class="jsgrid-cell">
                                                {if !$order->manager_id}
                                                    {if !$order->is_approve_order}
                                                        <button disabled type="button" class="btn btn-rounded btn-danger"> <i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp; Стадия КР</button>
                                                    {else}
                                                        <button type="button" class="btn btn-rounded btn-info js-accept-order-list js-event-add-click" data-event="2" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}"> <i class="fas fa-hospital-symbol"></i>&nbsp;&nbsp; Принять</button>
                                                    {/if}
                                                {/if}
                                            </td>
                                            {/if}
                                            {if !in_array($manager->role, ['verificator', 'edit_verificator'])}
                                            <td style="width: 100px;padding:0" class="jsgrid-cell">
                                                <div style="max-height:140px;padding:5px 0 5px 5px;;overflow-y:auto;overflow-x:hidden">
                                                    {*$order->scorings_result*}
                                                    {foreach $order->scorings as $sc}
                                                        {if $sc->type_name != 'svo' && ($sc->type_name != 'work' || $manager->role != 'verificator_minus')}
                                                        <span 
                                                            data-toggle="tooltip" 
                                                            title="{if $sc->status_name == 'new' || $sc->status_name == 'process'}Выполняется{elseif $sc->string_result}{$sc->string_result} {if $sc->type_name == 'scorista'}{$sc->scorista_ball}{/if}{/if}"
                                                            class="label label-sm {if $sc->status_name == 'new' || $sc->status_name == 'process'}label-warning{elseif $sc->success}label-success{else}label-danger{/if}"
                                                        >
                                                            {$scoring_types[$sc->type_name]->short_title|escape}
                                                        </span>
                                                        {/if}
                                                    {/foreach}
                                                </div>
                                            </td>
                                            {/if}
                                        </tr>
                                    {/if}   
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

                            <div>Показано заявок: <span class="js-count-order-page">{$orders|@count}</span></div>
                            
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
