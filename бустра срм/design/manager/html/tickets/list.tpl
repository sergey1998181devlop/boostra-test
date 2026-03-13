{$meta_title='Тикеты' scope=parent}

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
                <h3 class="text-themecolor mb-0 mt-0"><i class="mdi mdi-closed-caption"></i> Тикеты</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Тикеты</li>
                </ol>
            </div>
            
            <div class="col-md-6 col-4 align-self-center">
                <a href="ticket" class="btn float-right hidden-sm-down btn-success js-open-add-modal">
                    <i class="mdi mdi-plus-circle"></i> Добавить
                </a>

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
                        <h4 class="card-title">Тикеты</h4>
                        <div id="basicgrid" class="jsgrid" style="position: relative; width: 100%;">
                            <div class="jsgrid-grid-header jsgrid-header-scrollbar">
                                <table class="jsgrid-table table table-striped table-hover">
                                    <tr class="jsgrid-header-row">
                                        <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'fio_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'fio_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'fio_asc'}<a href="{url page=null sort='fio_desc'}">Номер</a>
                                            {else}<a href="{url page=null sort='fio_asc'}">Номер</a>{/if}
                                        </th>
                                        <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'payment_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'payment_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'manager_asc'}<a href="{url page=null sort='manager_desc'}">Статус</a>
                                            {else}<a href="{url page=null sort='manager_asc'}">Статус</a>{/if}
                                        </th>
                                        <th style="width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'fio_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'fio_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'fio_asc'}<a href="{url page=null sort='fio_desc'}">Дата</a>
                                            {else}<a href="{url page=null sort='fio_asc'}">Дата</a>{/if}
                                        </th>
                                        <th style="width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'fio_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'fio_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'fio_asc'}<a href="{url page=null sort='fio_desc'}">Клиент</a>
                                            {else}<a href="{url page=null sort='fio_asc'}">Клиент</a>{/if}
                                        </th>
                                        <th style="width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'phone_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'phone_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'phone_asc'}<a href="{url page=null sort='phone_desc'}">Телефон</a>
                                            {else}<a href="{url page=null sort='phone_asc'}">Телефон</a>{/if}
                                        </th>
                                        <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'number_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'number_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'subject_asc'}<a href="{url page=null sort='subject_desc'}">Тема</a>
                                            {else}<a href="{url page=null sort='subject_asc'}">Тема</a>{/if}
                                        </th>
                                        <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'payment_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'payment_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'manager_asc'}<a href="{url page=null sort='manager_desc'}">Ответственный</a>
                                            {else}<a href="{url page=null sort='manager_asc'}">Ответственный</a>{/if}
                                        </th>
                                        <th style="width: 60px;" class="jsgrid-header-cell">
                                            Коментарий
                                        </th>
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
                                    {foreach $tickets as $ticket}
                                        <tr class="jsgrid-row" id="main_{$task->id}">
                                            <td style="width: 60px;" class="jsgrid-cell jsgrid-align-right">  
                                                {$ticket->tag}                                               
                                                <a href="ticket/{$ticket->id}" target="_blank">
                                                    {$ticket->id|escape}
                                                </a>
                                            </td>
                                            <td style="width: 60px;" class="jsgrid-cell">
                                                <span class="label" style="background-color:{$statuses[$ticket->status_id]->color}">
                                                    {$statuses[$ticket->status_id]->name|escape}
                                                </span>
                                                {$reasons[$ticket->reason_id]->name|escape}
                                            </td>
                                            <td style="width: 80px;" class="jsgrid-cell jsgrid-align-right">                                                
                                                {$ticket->created|date} {$ticket->created|time}
                                            </td>
                                            <td style="width: 80px;" class="jsgrid-cell jsgrid-align-left">                                                
                                                <a href="ticket/{$ticket->id}" target="_blank">
                                                    {$ticket->client_fio|escape}
                                                </a>
                                                <br />
                                                <small>{$ticket->client_birth}</small>
                                            </td>
                                            <td style="width: 80px;" class="jsgrid-cell">
                                                <strong>{$ticket->client_phone|escape}</strong>
                                                <button class="btn waves-effect waves-light btn-xs btn-info float-right js-mango-call" data-phone="{$task->client_phone}" title="Выполнить звонок"><i class="fas fa-phone-square"></i></button>
                                            </td>
                                            <td style="width: 60px;" class="jsgrid-cell">
                                                {$subjects[$ticket->subject]->name|escape}
                                            </td>
                                            <td style="width: 60px;" class="jsgrid-cell">
                                                {$managers[$ticket->manager_id]->name|escape}
                                            </td>
                                            <td style="width: 60px;" class="jsgrid-cell">
                                                {$ticket->comment}
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