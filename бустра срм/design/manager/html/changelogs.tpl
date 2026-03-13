{$meta_title='Лог изменений' scope=parent}

{capture name='page_scripts'}
    
    <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/changelogs.js"></script>
    

{/capture}

{capture name='page_styles'}
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
                <h3 class="text-themecolor mb-0 mt-0"><i class="mdi mdi-book-open-page-variant"></i> Логи изменений</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Логи</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
                <div class="dropdown float-right mr-2 hidden-sm-down">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> January 2019 </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton"> 
                        <a class="dropdown-item" href="#">February 2019</a> 
                        <a class="dropdown-item" href="#">March 2019</a> 
                        <a class="dropdown-item" href="#">April 2019</a> 
                    </div>
                </div>
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
                        <h4 class="card-title">Логи</h4>
                        <div id="basicgrid" class="jsgrid" style="position: relative; width: 100%;">
                            <div class="jsgrid-grid-header jsgrid-header-scrollbar">
                                <table class="jsgrid-table table table-striped table-hover">
                                    <tr class="jsgrid-header-row">
                                        <th style="width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'date_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'date_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'date_asc'}<a href="{url page=null sort='date_desc'}">Дата</a>
                                            {else}<a href="{url page=null sort='date_asc'}">Дата</a>{/if}
                                        </th>
                                        <th style="width: 120px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'type_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'type_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'type_asc'}<a href="{url page=null sort='type_desc'}">Тип операции</a>
                                            {else}<a href="{url page=null sort='type_asc'}">Тип операции</a>{/if}
                                        </th>
                                        <th style="width: 100px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'manager_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'manager_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'manager_asc'}<a href="{url page=null sort='manager_desc'}">Менеджер</a>
                                            {else}<a href="{url page=null sort='manager_asc'}">Менеджер</a>{/if}
                                        </th>
                                        <th style="width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'order_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'order_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'order_asc'}<a href="{url page=null sort='order_desc'}">№ заказа</a>
                                            {else}<a href="{url page=null sort='order_asc'}">№ заказа</a>{/if}
                                        </th>
                                        <th style="width: 120px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'user_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'user_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'user_asc'}<a href="{url page=null sort='user_desc'}">Клиент</a>
                                            {else}<a href="{url page=null sort='user_asc'}">Клиент</a>{/if}
                                        </th>
                                    </tr>
                                    <tr class="jsgrid-filter-row" id="search_form">
                                    
                                        <td style="width: 80px;" class="jsgrid-cell jsgrid-align-right">
                                            <input type="hidden" name="sort" value="{$sort}" />
                                            <input type="text" name="date" value="{$search['date']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 120px;" class="jsgrid-cell">
                                            <select name="type" class="form-control input-sm">
                                                <option value=""></option>
                                                {foreach $types as $t_key => $t_name}
                                                <option value="{$t_key}" {if $t_key == $search['type']}selected="true"{/if}>{$t_name|escape}</option>
                                                {/foreach}
                                            </select>
                                        </td>
                                        <td style="width: 100px;" class="jsgrid-cell">
                                            <select name="manager" class="form-control input-sm">
                                                <option value=""></option>
                                                {foreach $managers as $m}
                                                <option value="{$m->id}" {if $m->id == $search['manager']}selected="true"{/if}>{$m->name|escape}</option>
                                                {/foreach}
                                            </select>
                                        </td>
                                        <td style="width: 80px;" class="jsgrid-cell">
                                            <input type="text" name="order" value="{$search['order']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 120px;" class="jsgrid-cell">
                                            <input type="text" name="user" value="{$search['user']}" class="form-control input-sm">
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="jsgrid-grid-body">
                                <table class="jsgrid-table table table-striped table-hover ">
                                    <tbody>
                                    {foreach $changelogs as $changelog}
                                        <tr class="jsgrid-row">
                                            <td style="width: 80px;" class="jsgrid-cell jsgrid-align-right">                                                
                                                <div class="button-toggle-wrapper">
                                                    <button class="js-open-order button-toggle" data-id="{$changelog->id}" type="button" title="Подробнее"></button>
                                                </div>
                                                <span>{$changelog->created|date}</span>
                                                {$changelog->created|time}
                                            </td>
                                            <td style="width: 120px;" class="jsgrid-cell">
                                                {if $types[$changelog->type]}{$types[$changelog->type]}
                                                {else}{$changelog->type|escape}{/if}
                                            </td>
                                            <td style="width: 100px;" class="jsgrid-cell">
                                                <a href="manager/{$changelog->manager->id}">{$changelog->manager->name|escape}</a>
                                            </td>
                                            <td style="width: 80px;" class="jsgrid-cell">
                                                <a href="order/{$changelog->order_id}">{$changelog->order_id}</a>
                                            </td>
                                            <td style="width: 120px;" class="jsgrid-cell">
                                                <a href="client/{$changelog->user->id}">
                                                    {$changelog->user->lastname|escape} 
                                                    {$changelog->user->firstname|escape} 
                                                    {$changelog->user->patronymic|escape}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr class="order-details" id="changelog_{$changelog->id}" style="display:none">
                                            <td colspan="5">
                                                <div class="row">
                                                    <table class="table">
                                                        <tr>
                                                            <th>Параметр</th>
                                                            <th>Старое значение</th>
                                                            <th>Новое значение</th>
                                                        </tr>
                                                        {if is_array($changelog->old_values)}
                                                            {foreach $changelog->old_values as $field => $old_value}
                                                                <tr>
                                                                    <td>{$field}</td>
                                                                    <td>
                                                                        {if $field == 'status' && is_numeric($old_value)}
                                                                            {$order_statuses[$changelog->old_values[$field]]}
                                                                        {else}
                                                                            {$old_value|escape}
                                                                        {/if}
                                                                    </td>
                                                                    <td>
                                                                        {if $field == 'status'}
                                                                            {$order_statuses[$changelog->new_values[$field]]}
                                                                        {else}
                                                                            {$changelog->new_values[$field]|escape}
                                                                        {/if}
                                                                    </td>
                                                                </tr>
                                                            {/foreach}
                                                        {else}
                                                            <tr>
                                                                <td>{$field}</td>
                                                                <td>
                                                                    {$changelog->old_values}
                                                                </td>
                                                                <td>
                                                                    {$changelog->new_values}
                                                                </td>
                                                            </tr>
                                                        {/if}
                                                    </table>

                                                </div>
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