{$meta_title='Список заявок' scope=parent}

{capture name='page_scripts'}
    
    <script src="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/fancybox3/dist/jquery.fancybox.js"></script>
    
    <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/individual_orders.js?v=1.03"></script>    

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
                    <i class="mdi mdi-animation"></i> Заявки
                    {/if}
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Заявки</li>
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
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <div class="clearfix">
                            <h4 class="card-title float-left">Список заявок </h4>
                            {*
                            <div class="float-right js-filter-status">
                                <a href="{if $filter_status=='approve'}{url status=null page=null}{else}{url status='approve' page=null}{/if}" class="btn btn-xs {if $filter_status=='approve'}btn-success{else}btn-outline-success{/if}">Одобрена</a>
                                <a href="{if $filter_status==3}{url status=null page=null}{else}{url status=3 page=null}{/if}" class="btn btn-xs {if $filter_status==3}btn-danger{else}btn-outline-danger{/if}">Отказ</a>
                                <a href="{if $filter_status==5}{url status=null page=null}{else}{url status=5 page=null}{/if}" class="btn btn-xs {if $filter_status==5}btn-primary{else}btn-outline-primary{/if}">На исправлении</a>
                                <a href="{if $filter_status==6}{url status=null page=null}{else}{url status=6 page=null}{/if}" class="btn btn-xs {if $filter_status==6}btn-info{else}btn-outline-info{/if}">Исправлена</a>
                                <a href="{if $filter_status==7}{url status=null page=null}{else}{url status=7 page=null}{/if}" class="btn btn-xs {if $filter_status==7}btn-warning{else}btn-outline-warning{/if}">Ожидание</a>
                                <a href="{if $filter_status=='issued'}{url status=null page=null}{else}{url status='issued' page=null}{/if}" class="btn btn-xs {if $filter_status=='issued'}btn-success{else}btn-outline-success{/if}">Выдан</a>
                                <a href="{if $filter_status=='notreceived'}{url status=null page=null}{else}{url status='notreceived' page=null}{/if}" class="btn btn-xs {if $filter_status=='notreceived'}btn-danger{else}btn-outline-danger{/if}">Не получены</a>
                                <a href="{if $filter_status=='notbusy'}{url status=null page=null}{else}{url status='notbusy' page=null}{/if}" class="btn btn-xs {if $filter_status=='notbusy'}btn-success{else}btn-outline-success{/if}">Не заняты</a>
                                <a href="{if $filter_status=='inwork'}{url status=null page=null}{else}{url status='inwork' page=null}{/if}" class="btn btn-xs {if $filter_status=='inwork'}btn-primary{else}btn-outline-primary{/if}">В работе</a>
                                {if $filter_status}
                                <input type="hidden" value="{$filter_status}" id="filter_status" />
                                {/if}
                            </div>
                            *}
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
                                        <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'period_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'period_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'period_asc'}<a href="{url page=null sort='period_desc'}">Срок</a>
                                            {else}<a href="{url page=null sort='period_asc'}">Срок</a>{/if}
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
                                        <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'status_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'status_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'status_asc'}<a href="{url page=null sort='status_desc'}">Статус</a>
                                            {else}<a href="{url page=null sort='status_asc'}">Статус</a>{/if}
                                        </th>
                                        <th style="width: 70px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'manager_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'manager_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'manager_asc'}<a href="{url page=null sort='manager_desc'}">Менеджер</a>
                                            {else}<a href="{url page=null sort='manager_asc'}">Менеджер</a>{/if}
                                        </th>
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
                                        <td style="width: 60px;" class="jsgrid-cell">
                                            <input type="text" name="period" value="{$search['period']}" class="form-control input-sm">
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
                                        <td style="width: 60px;" class="jsgrid-cell">
                                            <input type="text" name="status" value="{$search['status']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 70px;" class="jsgrid-cell">
                                            <select name="manager_id" class="form-control input-sm">
                                                <option value=""></option>
                                                {foreach $managers as $m}
                                                <option value="{$m->id}" {if $m->id == $search['manager_id']}selected="true"{/if}>{$m->name|escape}</option>
                                                {/foreach}
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="jsgrid-grid-body">
                                <table class="jsgrid-table table table-striped table-hover">
                                    <tbody>
                                    {foreach $individual_orders as $individual_order}
                                        {$order = $individual_order->order}
                                        <tr class="jsgrid-row js-order-row" {if $order->status == 6}style="border:2px solid green"{/if}>
                                            <td style="width: 70px;" class="jsgrid-cell jsgrid-align-right">
                                                <a href="individual_order/{$order->order_id}">{$order->order_id}</a>
                                                {if $individual_order->paid}
                                                <div>
                                                    <span class="label label-success">Оплачен</span>
                                                </div>
                                                {/if}
                                            </td>
                                            <td style="width: 70px;" class="jsgrid-cell">
                                                {$order->date|date} 
                                                {$order->date|time}
                                            </td>
                                            <td style="width: 70px;" class="jsgrid-cell">
                                                {$order->amount} 
                                            </td>
                                            <td style="width: 60px;" class="jsgrid-cell">
                                                {$order->period} {$order->period|plural:'день':'дней':'дня'}
                                            </td>
                                            <td style="width: 150px;" class="jsgrid-cell">
                                                <a href="client/{$order->user_id}">
                                                {$order->lastname} 
                                                {$order->firstname} 
                                                {$order->patronymic}
                                                </a>
                                                {if !in_array($manager->role, ['verificator', 'edit_verificator'])}
                                                {if $order->first_loan}<span class="label label-primary">Новая</span>
                                                {elseif $order->have_close_credits}<span class="label label-success">ПК</span>
                                                {else}<span class="label label-warning">Повтор</span>{/if}
                                                {/if}
                                                {if $order->is_user_credit_doctor == 1}<span class="label label-danger">КД</span>{/if}
                                            </td>
                                            {if !in_array($manager->role, ['verificator', 'edit_verificator'])}
                                            <td style="width: 70px;" class="jsgrid-cell">
                                                {$order->birth}
                                            </td>
                                            {/if}
                                            <td style="width: 80px;" class="jsgrid-cell">
                                                {$order->phone_mobile}
                                                <button class="btn waves-effect waves-light btn-xs btn-info js-mango-call" data-phone="{$order->phone_mobile}" data-order="{$order->order_id}" data-user="{$order->user_id}" title="Выполнить звонок"><i class="fas fa-phone-square"></i></button>
                                            </td>
                                            <td style="width: 100px;" class="jsgrid-cell">
                                                {$order->Regregion}
                                            </td>
                                            <td style="width: 60px;" class="jsgrid-cell">
                                                {if $individual_order->status == 1}<span class="label label-rounded label-primary">Новая</span>{/if}
                                                {if $individual_order->status == 2}<span class="label label-rounded label-success">Одобрена</span>{/if}
                                                {if $individual_order->status == 3}<span class="label label-rounded label-danger">Закрыта</span>{/if}
                                                {if $individual_order->status == 4}<span class="label label-rounded label-info">В работе</span>{/if}
                                                {if $individual_order->status == 9}<span class="label label-rounded label-warning">Оплачена</span>{/if}
                                            
                                            </td>
                                            <td style="width: 70px;" class="jsgrid-cell">
                                                <span class="text-warning js-order-manager-{$individual_order->order_id}">{$managers[$individual_order->manager_id]->name}</span>
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