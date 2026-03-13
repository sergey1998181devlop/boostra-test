{$meta_title='Мои задачи' scope=parent}

{capture name='page_scripts'}
    
    <script src="design/{$settings->theme|escape}/assets/plugins/moment/moment.js"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <!-- Date range Plugin JavaScript -->
    <script src="design/{$settings->theme|escape}/assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/daterangepicker/daterangepicker.js"></script>
    
    <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/cctasks.app.js"></script>
    <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/movements.app.js"></script>
    
{/capture}

{capture name='page_styles'}
    <!-- Date picker plugins css -->
    <link href="design/{$settings->theme|escape}/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    <!-- Daterange picker plugins css -->
    <link href="design/{$settings->theme|escape}/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="design/{$settings->theme|escape}/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">
    
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
                <h3 class="text-themecolor mb-0 mt-0"><i class="mdi mdi-closed-caption"></i> Мои задачи</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Мои задачи</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
                
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
                        <h4 class="card-title">Мои задачи</h4>
                        <div id="basicgrid" class="jsgrid" style="position: relative; width: 100%;">
                            <div class="jsgrid-grid-header jsgrid-header-scrollbar">
                                <table class="jsgrid-table table table-striped table-hover">
                                    <tr class="jsgrid-header-row">
                                        <th style="width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'fio_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'fio_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'fio_asc'}<a href="{url page=null sort='fio_desc'}">ФИО</a>
                                            {else}<a href="{url page=null sort='fio_asc'}">ФИО</a>{/if}
                                        </th>
                                        <th style="width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'phone_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'phone_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'phone_asc'}<a href="{url page=null sort='phone_desc'}">Телефон</a>
                                            {else}<a href="{url page=null sort='phone_asc'}">Телефон</a>{/if}
                                        </th>
                                        <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'number_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'number_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'number_asc'}<a href="{url page=null sort='number_desc'}">Займ</a>
                                            {else}<a href="{url page=null sort='number_asc'}">Займ</a>{/if}
                                        </th>
                                        <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'date_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'date_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'date_asc'}<a href="{url page=null sort='date_desc'}">Дата выдачи</a>
                                            {else}<a href="{url page=null sort='date_asc'}">Дата выдачи</a>{/if}
                                        </th>
                                        <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'summ_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'summ_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'summ_asc'}<a href="{url page=null sort='summ_desc'}">Сумма</a>
                                            {else}<a href="{url page=null sort='summ_asc'}">Сумма</a>{/if}
                                        </th>
                                        <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'payment_date_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'payment_date_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'payment_date_asc'}<a href="{url page=null sort='payment_date_desc'}">Дата возврата</a>
                                            {else}<a href="{url page=null sort='payment_date_asc'}">Дата возврата</a>{/if}
                                        </th>
                                        <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'payment_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'payment_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'payment_asc'}<a href="{url page=null sort='payment_desc'}">Сумма возврата</a>
                                            {else}<a href="{url page=null sort='payment_asc'}">Сумма возврата</a>{/if}
                                        </th>
                                        <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'payment_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'payment_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'payment_asc'}<a href="{url page=null sort='payment_desc'}">Пролонгация</a>
                                            {else}<a href="{url page=null sort='payment_asc'}">Пролонгация</a>{/if}
                                        </th>
                                        <th style="width: 50px;" class="text-right jsgrid-header-cell jsgrid-header-sortable {if $sort == 'status_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'status_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'status_asc'}<a href="{url page=null sort='status_desc'}">Статус</a>
                                            {else}<a href="{url page=null sort='status_asc'}">Статус</a>{/if}
                                        </th>
                                        <th style="width:60px" class="text-right jsgrid-header-cell" ></th>
                                    </tr>
                                    {*}
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
                                    {*}
                                </table>
                            </div>
                            <div class="jsgrid-grid-body">
                                <table class="jsgrid-table table table-striped table-hover ">
                                    <tbody>
                                    {foreach $tasks as $task}
                                        {if $task->cc_status != 2}
                                        <tr class="jsgrid-row" id="main_{$task->id}">
                                            <td style="width: 80px;" class="jsgrid-cell jsgrid-align-right">                                                
                                                <div class="button-toggle-wrapper">
                                                    <button class="js-open-order button-toggle" data-id="{$task->id}" data-uid="{$task->user->UID}" data-site-id="{$task->user->site_id}" data-number="{$task->zaim_number}" type="button" title="Подробнее"></button>
                                                </div>
                                                <a href="client/{$task->user->id}" target="_blank">
                                                    {$task->user->lastname|escape} {$task->user->firstname|escape} {$task->user->patronymic|escape}
                                                </a>
                                            </td>
                                            <td style="width: 80px;" class="jsgrid-cell">
                                                <strong>{$task->user->phone_mobile|escape}</strong>
                                                <button class="btn waves-effect waves-light btn-xs btn-info float-right js-mango-call" data-phone="{$task->user->phone_mobile}" data-user="{$task->user->id}" title="Выполнить звонок">
                                                    <i class="fas fa-phone-square"></i>
                                                </button>
                                            </td>
                                            <td style="width: 60px;" class="jsgrid-cell">
                                                {if $task->order}
                                                <a href="order/{$task->order->order_id}">{$task->zaim_number}</a>
                                                {else}
                                                <div>{$task->zaim_number}</div>
                                                {/if}
                                            </td>
                                            <td style="width: 60px;" class="jsgrid-cell">
                                                {$task->zaim_date|date}
                                                <br />
                                                <button class="js-get-movements btn btn-link btn-xs js-no-peni" data-number="{$task->zaim_number}">Операции</button>
                                            </td>
                                            <td style="width: 60px;" class="jsgrid-cell">
                                                {$task->zaim_summ}
                                            </td>
                                            <td style="width: 60px;" class="jsgrid-cell">
                                                {$task->payment_date|date}
                                            </td>
                                            <td style="width: 60px;" class="jsgrid-cell">
                                                <a class="" href="#" data-toggle="collapse" data-target="#ostatok_{$task->id}">{$task->ostatok_od + $task->ostatok_percents + $ostatok_peni}</a>
                                                <div id="ostatok_{$task->id}" class="collapse">
                                                    <div>Основной долг: <strong>{$task->ostatok_od}</strong></div>
                                                    <div>Проценты: <strong>{$task->ostatok_percents}</strong></div>
                                                    <div>Пени: <strong>{$task->ostatok_peni}</strong></div>
                                                </div>
                                            </td>
                                            <td style="width: 60px;" class="jsgrid-cell">
                                                {if $task->prolongation_amount > 0}
                                                <a class="" href="#" data-toggle="collapse" data-target="#prolongation_{$task->id}">{$task->prolongation_amount}</a>
                                                {/if}
                                                {if $task->last_prolongation == 2}
                                                <span class="label label-danger float-right" title="Количество пролонгаций займа">
                                                {elseif $task->last_prolongation == 1}
                                                <span class="label label-warning float-right" title="Количество пролонгаций займа">
                                                {else}
                                                <span class="label label-primary float-right" title="Количество пролонгаций займа">
                                                {/if}
                                                    <h6 class="m-0">{$task->prolongation_count}</h6>
                                                </span>
                                                <div id="prolongation_{$task->id}" class="collapse">
                                                    {if $task->prolongation_summ_percents > 0}
                                                    <div>Проценты: <strong>{1 * $task->prolongation_summ_percents}</strong></div>
                                                    {/if}
                                                    {if $task->prolongation_summ_insurance > 0}
                                                    <div>Страховка: <strong>{1 * $task->prolongation_summ_insurance}</strong></div>
                                                    {/if}
                                                    {if $task->prolongation_summ_cost > 0}
                                                    <div>Пролонгация: <strong>{1 * $task->prolongation_summ_cost}</strong></div>
                                                    {/if}
                                                    {if $task->prolongation_summ_sms > 0}
                                                    <div>СМС-информ: <strong>{1 * $task->prolongation_summ_sms}</strong></div>
                                                    {/if}
                                                </div>
                                                {if $is_developer}
                                                <br />
                                                <small title="Дата обновления">{$task->last_update|date}</small>
                                                {/if}
                                            </td>
                                            <td style="width: 50px;" class="jsgrid-cell text-right">
                                                <div class="btn-group js-status-{$task->id}">
                                                    {if $task->cc_status == 0}<button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Новая</button>{/if}
                                                    {if $task->cc_status == 1}<button type="button" class="btn btn-warning btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Буфер</button>{/if}
                                                    {if $task->cc_status == 2}<button type="button" class="btn btn-success btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Завершена</button>{/if}
                                                    <div class="dropdown-menu" x-placement="bottom-start">
                                                        {if $task->cc_status != 0}<a class="dropdown-item text-info js-toggle-status" data-status="0" data-task="{$task->id}" href="javascript:void(0)">Новая</a>{/if}
                                                        {if $task->cc_status != 1}<a class="dropdown-item text-warning js-toggle-status" data-status="1" data-task="{$task->id}" href="javascript:void(0)">Буфер</a>{/if}
                                                        {if $task->cc_status != 2}<a class="dropdown-item text-success js-toggle-status" data-status="2" data-task="{$task->id}" href="javascript:void(0)">Завершена</a>{/if}
                                                    </div>
                                                </div>
                                                
                                            </td>
                                            <td style="width:60px;" class="jsgrid-cell text-right">
                                                <button type="button" class="btn btn-sm btn-success js-open-comment-form" title="Добавить комментарий" data-order="{$task->order->order_id}" data-user="{$task->user->id}" data-task="{$task->id}" data-uid="{$task->user->UID}">
                                                    <i class="mdi mdi-comment-text"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-primary js-open-sms-modal" title="Отправить смс" data-user="{$task->user->id}">
                                                    <i class=" far fa-share-square"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr class="order-details" id="changelog_{$task->id}" style="display:none">
                                            <td colspan="9">
                                                <div class="row">
                                                    
                                                    <div class="col-md-6">
                                                        <div class="card">
                                                            <div class="card-body">
                                                                <h4 class="card-title">
                                                                    <span>Комментарии</span>
                                                                    <a href="javascript:void(0);" class="ml-3 js-open-comment-form btn btn-success btn-sm btn-rounded float-right" data-order="{$task->order->order_id}" data-user="{$task->user->id}" data-task="{$task->id}" data-uid="{$task->user->UID}">
                                                                        <i class="mdi mdi-comment-text"></i> Добавить
                                                                    </a>
                                                                </h4>
                                                            </div>
                                                            <div class="js-comments comment-widgets cctasks-comments">
                                                                
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-6">
                                                        <div class="card">
                                                            <form class="js-calc-form">
                                                                
                                                                <input type="hidden" class="js-calc-zaim-summ" value="{$task->zaim_summ}" />
                                                                <input type="hidden" class="js-calc-percent" value="{$task->percent}" />
                                                                <input type="hidden" class="js-calc-ostatok-od" value="{$task->ostatok_od}" />
                                                                <input type="hidden" class="js-calc-ostatok-percents" value="{$task->ostatok_percents}" />
                                                                <input type="hidden" class="js-calc-ostatok-peni" value="{$task->ostatok_peni}" />
                                                                <input type="hidden" class="js-calc-payment-date" value="{$task->payment_date}" />
                                                                <input type="hidden" class="js-calc-allready-added" value="{$task->allready_added}" />

                                                                <input type="hidden" class="js-calc-prolongation-summ-insurance" value="{$task->prolongation_summ_insurance}" />
                                                                <input type="hidden" class="js-calc-prolongation-summ-sms" value="{$task->prolongation_summ_sms}" />
                                                                <input type="hidden" class="js-calc-prolongation-summ-cost" value="{$task->prolongation_summ_cost}" />
                                                                
                                                                
                                                                <div class="card-body">
                                                                    <h4 class="card-title">
                                                                        <span>Калькулятор</span>
                                                                    </h4>
                                                                    <div class="row">
                                                                        <div class="col-6">
                                                                            <div class="input-group mb-3">
                                                                                <input type="text" class="form-control singledate js-calc-input" value="" />
                                                                                <div class="input-group-append">
                                                                                    <span class="input-group-text">
                                                                                        <span class="ti-calendar"></span>
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-6">
                                                                            <button type="submit" class="btn btn-primary js-calc-run">Рассчитать</button>
                                                                        </div>
                                                                        <div class="js-calc-result col-12">
                                                                            
                                                                        </div>
                                                                    </div>                                                                
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                    
                                                </div>
                                            </td>
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
                    <input type="hidden" name="block" value="cctasks" />
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
                                    <div class="form-group">
                                        <label for="name" class="control-label">Выберите шаблон сообщения:</label>
                                        <select name="template_id" class="form-control">
                                            {foreach $sms_templates as $sms_template}
                                            <option value="{$sms_template->id}" title="{$sms_template->template|escape}">{$sms_template->name|escape}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                    <div class="form-action clearfix">
                                        <button type="button" class="btn btn-danger btn-lg float-left waves-effect" data-dismiss="modal">Отменить</button>
                                        <button type="submit" class="btn btn-success btn-lg float-right waves-effect waves-light">Да, отправить</button>
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