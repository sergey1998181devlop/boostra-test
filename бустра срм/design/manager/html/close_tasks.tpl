{$meta_title='Возврат лояльных' scope=parent}

{capture name='page_scripts'}
    
    <script src="design/{$settings->theme|escape}/assets/plugins/moment/moment.js"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <!-- Date range Plugin JavaScript -->
    <script src="design/{$settings->theme|escape}/assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/daterangepicker/daterangepicker.js"></script>
    
    <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/close_tasks.app.js"></script>
    <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/movements.app.js"></script>
    
    <script>
        $(function(){
            $(document).on('click', '.js-distribute-open', function(e){
                e.preventDefault();
                
                $('.js-distribute-contract').remove();
                $('.js-contract-row').each(function(){
                    $('#form_distribute').append('<input type="hidden" name="contracts[]" class="js-distribute-contract" value="'+$(this).data('contract')+'" />');
                });
                
                $('.js-select-type').val('all');
                
                $('#modal_distribute').modal();
            });
            
            $(document).on('submit', '#form_distribute', function(e){
                e.preventDefault();
                
                var $form = $(this);
                
                if ($form.hasClass('loading'))
                    return false;
                
    console.log(location.hash)             
                var _hash = location.hash.replace('#', '?');
                $.ajax({
                    url: '/ccprolongations'+_hash,
                    data: $form.serialize(),
                    type: 'POST',
                    beforeSend: function(){
                        $form.addClass('loading');
                    },
                    success: function(resp){
                        if (resp.success)
                        {
                            $('#modal_distribute').modal('hide');            
                            
                            Swal.fire({
                                timer: 5000,
                                title: 'Договора распределены.',
                                type: 'success',
                            });
                            location.reload();
                        }
                        else
                        {
                            Swal.fire({
                                text: resp.error,
                                type: 'error',
                            });
                            
                        }
                        $form.removeClass('loading');
                    }
                })
            })
            
        })
    </script>
    
{/capture}

{capture name='page_styles'}
    <!-- Date picker plugins css -->
    <link href="design/{$settings->theme|escape}/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    <!-- Daterange picker plugins css -->
    <link href="design/{$settings->theme|escape}/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="design/{$settings->theme|escape}/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">
    
    <link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid.min.css" />
    <link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid-theme.min.css" />
    <link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/css-chart/css-chart.css" />
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
            <div class="col-md-4 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0"><i class="mdi mdi-closed-caption"></i> Возврат лояльных</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Возврат лояльных</li>
                </ol>
            </div>
            <div class="col-md-2">
                <div class="p-2"> 
                </div>
            </div>
            <div class="col-md-6 col-4 align-self-center">
                
                {if $statistic->total_amount > 0}
                <div class="row bg-grey">
                    <div class="col-md-6 text-center">
                        <h3 class="pt-1">
                            <i class="fas fa-id-card-alt"></i>
                            <span>Портфель: {$statistic->total_amount|round} P</span>
                        </h3>
                    </div>
                    <div class="col-md-6 text-center">
                        <h3 class="pt-1">
                            <i class=" far fa-money-bill-alt"></i>
                            <span>Собрано: {$statistic->total_paid|round} P</span>
                            <span class="label label-info">
                                <h4 class="mb-0">
                                    {if $statistic->total_amount > 0}
                                        {($statistic->total_paid / $statistic->total_amount * 100)|round}%
                                    {else}
                                        0%
                                    {/if}
                                </h4>
                            </span>
                        </h3>
                    </div>
                    <div class="col-md-12">
                        <hr class="m-0" />
                    </div>
                    <div class="col-md-4">
                        <div class="card m-0  bg-grey">
                            <div class="card-body p-0 pt-2">
                                <div class="row">
                                    <div class="col-4">
                                        <div data-label="{($statistic->inwork/$statistic->total*100)|round}%" class="css-bar css-bar-sm mb-0 css-bar-success css-bar-{($statistic->inwork/$statistic->total*10)|round*10}"></div>
                                    </div>
                                    <div class="col-8">
                                        <h5 class="card-title mb-1">Обработано</h5>
                                        <h6 class="text-white">
                                            {$statistic->inwork} / {$statistic->total}    
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card m-0 bg-grey">
                            <div class="card-body p-0 pt-2">
                                <div class="row">
                                    {if $manager->role == 'contact_center'}
                                        {$real_percents = ($statistic->prolongation/$settings->cc_pr_prolongation_plan*100)|round}
                                        {$round_percents = ($statistic->prolongation/$settings->cc_pr_prolongation_plan*10)|round*10}                                        
                                        {$cc_pr_prolongation_plan = $settings->cc_pr_prolongation_plan}
                                    {else}
                                        {$real_percents = ($statistic->prolongation/$statistic->total*100)|round}
                                        {$round_percents = ($statistic->prolongation/$statistic->total*10)|round*10}                                                                            
                                        {$cc_pr_prolongation_plan = $statistic->total}
                                    {/if}
                                    <div class="col-4">
                                        <div data-label="{$real_percents}%" class="css-bar css-bar-sm mb-0 css-bar-success css-bar-{$round_percents}"></div>
                                    </div>
                                    <div class="col-8">
                                        <h5 class="card-title mb-1">Пролонгации</h5>
                                        <h6 class="text-white">
                                            {$statistic->prolongation} / {$cc_pr_prolongation_plan}
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card m-0 bg-grey">
                            <div class="card-body p-0 pt-2">
                                <div class="row">
                                    {if $manager->role == 'contact_center'}
                                        {$real_percents = ($statistic->closed/$settings->cc_pr_close_plan*100)|round}
                                        {if ($statistic->closed/$settings->cc_pr_close_plan*10)|round*10 > 100}
                                            {$round_percents = 100}
                                        {else}
                                            {$round_percents = ($statistic->closed/$settings->cc_pr_close_plan*10)|round*10}                                        
                                        {/if}
                                        {$cc_pr_close_plan = $settings->cc_pr_close_plan}
                                    {else}
                                        {$real_percents = ($statistic->closed/$statistic->total*100)|round}
                                        {$round_percents = ($statistic->closed/$statistic->total*10)|round*10}                                                                            
                                        {$cc_pr_close_plan = $statistic->total}
                                    {/if}
                                    <div class="col-4">
                                        <div data-label="{$real_percents}%" class="css-bar css-bar-sm mb-0 css-bar-success css-bar-{$round_percents}"></div>
                                    </div>
                                    <div class="col-8">
                                        <h5 class="card-title mb-1">Закрытия</h5>
                                        <h6 class="text-white">
                                            {$statistic->closed} / {$cc_pr_close_plan}
                                        </h6>
                                    </div>
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
                            <h4 class="card-title float-left">
                                Возврат лояльных
                                {if $filter_type=='pk'}(ПК)
                                {elseif $filter_type=='nk'}(НК)
                                {/if}
                            </h4>
                            <div class="float-right js-filter-status">
                                <a href="{if $filter_type=='pk'}{url type=null page=null}{else}{url type='pk' page=null}{/if}" class="btn btn-xs {if $filter_type=='pk'}btn-success{else}btn-outline-success{/if}">ПК</a>
                                <a href="{if $filter_type=='nk'}{url type=null page=null}{else}{url type='nk' page=null}{/if}" class="btn btn-xs {if $filter_type=='nk'}btn-info{else}btn-outline-info{/if}">НК</a>
                                {if $filter_type}
                                <input type="hidden" value="{$filter_type}" id="type" />
                                {/if}
                            </div>
                        </div>
                        
                        {if !$close_tasks|count}
                        <div class="alert alert-danger">
                            <h3 class="text-danger">Нет Активных заданий</h3>
                        </div>
                        {/if}
                        
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
                                        <th style="width: 40px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'timezone_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'timezone_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'timezone_asc'}<a href="{url page=null sort='timezone_desc'}">Час. пояс</a>
                                            {else}<a href="{url page=null sort='timezone_asc'}">Час. пояс</a>{/if}
                                        </th>
                                        <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'number_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'number_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'number_asc'}<a href="{url page=null sort='number_desc'}">Займ</a>
                                            {else}<a href="{url page=null sort='number_asc'}">Займ</a>{/if}
                                        </th>
                                        <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'date_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'date_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'date_asc'}<a href="{url page=null sort='date_desc'}">Дата закрытия</a>
                                            {else}<a href="{url page=null sort='date_asc'}">Дата закрытия</a>{/if}
                                        </th>
                                        <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'date_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'date_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'date_asc'}<a href="{url page=null sort='date_desc'}">Опции</a>
                                            {else}<a href="{url page=null sort='date_asc'}">Опции</a>{/if}
                                        </th>
                                        {*}
                                        <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'manager_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'manager_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'manager_asc'}<a href="{url page=null sort='manager_desc'}">Ответственный</a>
                                            {else}<a href="{url page=null sort='manager_asc'}">Ответственный</a>{/if}
                                        </th>
                                        {*}
                                        <th style="width: 50px;" class="text-right jsgrid-header-cell jsgrid-header-sortable {if $sort == 'status_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'status_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'status_asc'}<a href="{url page=null sort='status_desc'}">Статус</a>
                                            {else}<a href="{url page=null sort='status_asc'}">Статус</a>{/if}
                                        </th>
                                        <th style="width:60px" class="text-right jsgrid-header-cell" ></th>
                                    </tr>
                                    
                                    <tr class="jsgrid-filter-row" id="search_form">
                                    
                                        <td style="width: 80px;" class="jsgrid-cell jsgrid-align-right">
                                            <input type="hidden" name="sort" value="{$sort}" />
                                            <input type="text" name="fio" value="{$search['fio']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 80px;" class="jsgrid-cell jsgrid-align-right">
                                            <input type="text" name="phone" value="{$search['phone']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 40px;" class="jsgrid-cell jsgrid-align-right">

                                        </td>
                                        <td style="width: 60px;" class="jsgrid-cell jsgrid-align-right">
                                            <input type="text" name="number" value="{$search['number']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 60px;" class="jsgrid-cell jsgrid-align-right">
                                        </td>
                                        <td style="width: 60px;" class="jsgrid-cell">
                                        
                                        </td>
                                        {*}
                                        <td style="width: 60px;" class="jsgrid-cell">
                                            <select name="manager" class="form-control input-sm">
                                                <option value=""></option>
                                                {foreach $managers as $m}
                                                {if $m->role == 'contact_center'}
                                                <option value="{$m->id}" {if $m->id == $search['manager']}selected="true"{/if}>{$m->name|escape}</option>
                                                {/if}
                                                {/foreach}
                                            </select>
                                        </td>
                                        {*}
                                        <td style="width: 50px;" class="jsgrid-cell">
                                            <select name="status" class="form-control input-sm">
                                                <option value=""></option>
                                                {foreach $pr_statuses as $ts_id => $ts}
                                                <option value="{$ts_id}" {if $ts_id === $search['status']}selected="true"{/if}>{$ts|escape}</option>
                                                {/foreach}
                                            </select>
                                        </td>
                                        <td style="width: 60px;" class="jsgrid-cell">
                                        </td>
                                    </tr>
                                    
                                </table>
                            </div>
                            <div class="jsgrid-grid-body">
                                <table class="jsgrid-table table table-striped table-hover ">
                                    <tbody>
                                    {foreach $close_tasks as $task}
                                        <tr class="jsgrid-row" id="main_{$task->id}">
                                            <td style="width: 80px;" class="jsgrid-cell jsgrid-align-right">                                                
                                                {*}
                                                <div class="button-toggle-wrapper">
                                                    <button class="js-open-order button-toggle" data-id="{$task->id}" data-uid="{$task->user->UID}" data-number="{$task->balance->zaim_number}" type="button" title="Подробнее"></button>
                                                </div>
                                                {*}
                                                <a href="client/{$task->user->id}" target="_blank">
                                                    {$task->user->lastname|escape}
                                                    {$task->user->firstname|escape}
                                                    {$task->user->patronymic|escape}
                                                </a>
                                            </td>
                                            <td style="width: 80px;" class="jsgrid-cell">
                                                <strong>{$task->user->phone_mobile|escape}</strong>
                                                <button class="btn waves-effect waves-light btn-xs btn-info float-right js-mango-call" data-phone="{$task->user->phone_mobile}" data-user="{$task->user->id}" title="Выполнить звонок">
                                                    <i class="fas fa-phone-square"></i>
                                                </button>
                                            </td>
                                            <td style="width: 40px;" class="jsgrid-cell">
                                                <div>{$task->timezone} ч</div>
                                            </td>
                                            <td style="width: 60px;" class="jsgrid-cell">
                                                {if $task->order_id}
                                                <a href="order/{$task->order_id}">{$task->number}</a>
                                                {else}
                                                <div>{$task->number}</div>
                                                {/if}
                                            </td>
                                            <td style="width: 60px;" class="jsgrid-cell">
                                                {$task->close_date|date}
                                                <br />
                                                {*}
                                                <button class="js-get-movements btn btn-link btn-xs js-no-peni" data-number="{$task->number}">Операции</button>
                                                {*}
                                            </td>
                                            <td style="width: 60px;" class="jsgrid-cell">
                                                {if $task->offer}
                                                    <small>
                                                    {if $task->offer->type == 'percents'}Снижение процентной ставки по кредиту
                                                    {elseif $task->offer->type == 'amount'}Увеличенная сумма займа
                                                    {elseif $task->offer->type == 'insure'}Снижение страховки 
                                                    {/if}
                                                    <br />
                                                    <span class="label label-success">{$task->offer->value|escape}</span>
                                                    </small>
                                                {else}
                                                <button type="button" class="btn btn-info btn-xs js-open-offer-modal" data-task="{$task->id}">Создать опцию</button>
                                                {/if}
                                                {*$managers[$task->manager_id]->name|escape*}
                                            </td>
                                            <td style="width: 50px;" class="jsgrid-cell text-right">
                                                <div class="btn-group js-status-{$task->id}">
                                                    {if $task->status == 0}<button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Новая</button>{/if}
                                                    {if $task->status == 1}<button type="button" class="btn btn-warning btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Буфер</button>{/if}
                                                    {if $task->status == 2}<button type="button" class="btn btn-success btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Завершена</button>{/if}
                                                    <div class="dropdown-menu" x-placement="bottom-start">
                                                        {if $task->status != 0}<a class="dropdown-item text-info js-toggle-status" data-status="0" data-task="{$task->id}" href="javascript:void(0)">Новая</a>{/if}
                                                        {if $task->status != 1}<a class="dropdown-item text-warning js-toggle-status" data-status="1" data-task="{$task->id}" href="javascript:void(0)">Буфер</a>{/if}
                                                        {if $task->status != 2}<a class="dropdown-item text-success js-toggle-status" data-status="2" data-task="{$task->id}" href="javascript:void(0)">Завершена</a>{/if}
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="width:60px;" class="jsgrid-cell text-right">
                                                <button type="button" class="btn btn-sm btn-success js-open-comment-form" title="Добавить комментарий" data-order="{$task->order_id}" data-user="{$task->user->id}" data-task="{$task->id}" data-uid="{$task->user->UID}">
                                                    <i class="mdi mdi-comment-text"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-primary js-open-sms-modal" title="Отправить смс" data-user="{$task->user->id}">
                                                    <i class=" far fa-share-square"></i>
                                                </button>
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

<div class="">
    
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

    <div id="modal_perspective" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                
                <div class="modal-header">
                    <h4 class="modal-title">Изменить статус на "Перспектива"</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="form_perspective" action="order/{$order->order_id}">
                        
                        <input type="hidden" name="task_id" value="" />
                        <input type="hidden" name="action" value="add_perspective" />
                        
                        <div class="alert" style="display:none"></div>
                        
                        <div class="form-group">
                            <label for="name" class="control-label text-white">Когда обещает:</label>
                            <input type="text" name="perspective_date" class="form-control js-perspective" value="" />
                        </div>
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

    <div id="modal_recall" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                
                <div class="modal-header">
                    <h4 class="modal-title">Изменить статус на "Перезвонить"</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="form_recall" action="order/{$order->order_id}">
                        
                        <input type="hidden" name="task_id" value="" />
                        <input type="hidden" name="action" value="add_recall" />
                        
                        <div class="alert" style="display:none"></div>
                        
                        <div class="form-group">
                            <label for="name" class="control-label text-white">Время перезвона:</label>
                            <input type="text" name="recall_date" class="form-control js-recall" value="" />
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

    <div id="modal_add_offer" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                
                <div class="modal-header">
                    <h4 class="modal-title">Добавить опцию</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="form_add_offer">
                        
                        <input type="hidden" name="task_id" value="" />
                        <input type="hidden" name="action" value="add_offer" />
                        
                        <div class="alert" style="display:none"></div>
                        
                        <div class="form-group">
                            <div class="row">
                                <label for="name" class="col-md-6 control-label text-white">Время действия:</label>
                                <div class="col-md-6">
                                    <input type="text" name="end_date" class="form-control js-recall" value="" />
                                </div>
                            </div>
                        </div>
                        <div class="form-group ">
                            <div class="row">
                                <label for="name" class="col-md-6 control-label text-white">Опция:</label>
                                <div class="col-md-6">
                                    <select class="form-control  js-offer-type-types" name="type">
                                        <option value="" selected=""></option>
                                        <option value="percents">Снижение процентной ставки по кредиту</option>
                                        <option value="amount">Увеличенная сумма займа</option>
                                        <option value="insure">Снижение страховки</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="name" class="col-md-6 control-label text-white">Размер скидки:</label>
                            <div class="col-md-6">
                            <select class="form-control js-offer-type-values" name="value">
                                <option value="" selected=""></option>
                                {foreach $offer_types as $offer_type_name => $offer_type_values}
                                    {foreach $offer_type_values as $offer_type_value}
                                    <option value="{$offer_type_value}" class="js-offer-type-{$offer_type_name}">{$offer_type_value}</option>
                                    {/foreach}
                                {/foreach}
                            </select>
                        </div>
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

</div>