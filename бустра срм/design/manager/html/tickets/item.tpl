{if $ticket->id}
    {$meta_title="Тикет №`$ticket->id`" scope=parent}
{else}
    {$meta_title="Новый тикет" scope=parent}
{/if}

{capture name='page_scripts'}
    <script src="design/{$settings->theme|escape}/assets/plugins/moment/moment.js"></script>

    <script src="design/{$settings->theme|escape}/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/daterangepicker/daterangepicker.js"></script>

    <script src="design/{$settings->theme|escape}/assets/plugins/inputmask/dist/min/jquery.inputmask.bundle.min.js"></script>
    
    <script src="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/fancybox3/dist/jquery.fancybox.js"></script>
    
    <script>
        function TicketApp()
        {
            var app = this;
            
            var _init = function(){
                _init_phonemask();
                _init_datepicker();
                _open_close_modal();
                _open_manager_modal();
            }
            
            var _init_datepicker = function(){
                $('.js-date-picker').each(function(){
                    $(this).daterangepicker({
                        singleDatePicker: true,
                        showDropdowns: true,
                        locale: {
                            format: 'DD.MM.YYYY'
                        }
                    });
                });
            };
            
            var _init_phonemask = function(){
                $(".js-phone-mask").each(function(){
                    $(this).inputmask("7(999) 999-9999");
                })
            }
            
            var _open_close_modal = function(){
                $(document).on('click', '.js-open-close-modal', function(e){
                    e.preventDefault();
                    
                    $('#modal_close').modal();                
                });                
            }
            
            var _open_manager_modal = function(){
                $(document).on('click', '.js-open-manager-modal', function(e){
                    e.preventDefault();
                    
                    $('#modal_manager').modal();                
                });                
            }
            
            ;(function(){
                _init();
            })();
        }
        $(function(){
            new TicketApp();
        })
    </script>
{/capture}


{capture name='page_styles'}
    <link href="design/{$settings->theme|escape}/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="design/{$settings->theme|escape}/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="design/{$settings->theme|escape}/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">

    <link href="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet" />
    <link href="design/{$settings->theme|escape}/assets/plugins/fancybox3/dist/jquery.fancybox.css" rel="stylesheet" />
    <style>
        .jsgrid-table { margin-bottom:0}
    </style>
{/capture}

<div class="page-wrapper" id="page_wrapper">
    
  
    <!-- ============================================================== -->
    <!-- Container fluid  -->
    <!-- ============================================================== -->
    <div class="container-fluid">
        
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-animation"></i> 
                    {if $ticket->id}Тикет №{$ticket->id}
                    {else}Новый тикет{/if}
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="tickets">Тикеты</a></li>
                    <li class="breadcrumb-item active">
                    {if $ticket->id}Тикет №{$ticket->id}
                        <a href="javascript:void(0);" onclick="openTicketCommentForm();">
                            <i class="mdi mdi-comment-text"></i>
                        </a>
                    {else}Новый тикет{/if}
                        
                    </li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
                
            </div>
        </div>
        
        <ul class="mt-2 nav nav-tabs" role="tablist">
            <li class="nav-item"> 
                <a class="nav-link active" data-toggle="tab" href="#tab_order" role="tab" aria-selected="true">
                    <span class="hidden-sm-up"><i class="ti-home"></i></span> 
                    <span class="hidden-xs-down">Информация</span>
                </a> 
            </li>
            {if $ticket->id}
            {include file='../chatHeader.tpl'}
            <li class="nav-item"> 
                <a class="nav-link" data-toggle="tab" href="#comments" role="tab" aria-selected="true">
                    <span class="hidden-sm-up"><i class="ti-home"></i></span> 
                    <span class="hidden-xs-down">Коментарии</span>
                </a> 
            </li>
            {/if}
        </ul>    
        <div class="tab-content ">
            <div id="comments" class="tab-pane" role="tabpanel">
                <div class="card">
                    <div class="row">
                        <div class="col-12">
                            <div class="card-body">
                                <table class="table">
                                    <tr>
                                        <td>Дата</td>
                                        <td>Сотрудник</td>
                                        <td>Коментарий</td>
                                    </tr>
                                    {foreach $ticketComments AS $ticketComment}
                                    <tr>
                                        <td>{$ticketComment->date_create}</td>
                                        <td>
                                            {$managers[$ticketComment->manager]->name|escape}
                                        </td>
                                        <td>{$ticketComment->comment}</td>
                                    </tr>
                                    {/foreach}
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {$userId = $client}
            {include file='../chat.tpl'}
            
            <div id="tab_order" class="tab-pane active" role="tabpanel">
            
                <div class="row" id="order_wrapper">
                    <div class="col-12">
                        <div class="card card-outline-info">
                            
                            <form action="{url}" method="POST" class="mb-3 js-order-item-form " onclick="closeAllOpenBlocksPrompt()" >
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            
                                            {if $ticket->id}
                                            <div class="col-12">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div>
                                                            <h4 class="badge" style="background:{$statuses[$ticket->status_id]->color}">{$statuses[$ticket->status_id]->name|escape}</h4>
                                                        </div>
                                                        <p>
                                                            Дата создания: {$ticket->created|date} {$ticket->created|time}
                                                        </p>
                                                        <p>
                                                            Ответственный: {$managers[$ticket->manager_id]->name|escape}
                                                        </p>
                                                    </div>
                                                    <div class="col-md-6 text-right">
                                                        {if $ticket->status_id == 4}
                                                        <div>
                                                            {$ticket->close_date|date} {$ticket->close_date|time}
                                                            <br />
                                                            <strong>{$reasons[$ticket->reason_id]->name|escape}</strong>
                                                            <p>
                                                                <i><small>{$ticket->close_comment|nl2br}</small></i>
                                                            </p>
                                                        </div>
                                                        {else}
                                                        <div class="p-1">
                                                            <button type="button" style="width:250px" class="btn btn-warning btn-lg btn-rounded js-open-manager-modal">Перенаправить</button>
                                                        </div>
                                                        <div class="p-1">
                                                            <button type="button" style="width:250px" class="btn btn-success btn-lg btn-rounded js-open-close-modal">Завершить</button>
                                                        </div>
                                                        {/if}
                                                    </div>
                                                </div>
                                            </div>
                                            {/if}
                                            
                                            <div class="col-12">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="col-md-5">
                                                                <label class="control-label">Тема обращения</label>
                                                            </div>
                                                            <div class="col-md-7">
                                                                <select name="subject" class="form-control" {if $ticket->status_id==4}disabled{/if}>
                                                                    <option value="0"></option>
                                                                    {foreach $subjects as $s}
                                                                    <option value="{$s->id}" {if $s->id==$ticket->subject}selected{/if}>{$s->name|escape}</option>
                                                                    {/foreach}
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <div class="form-group row {if in_array('empty_client_fio', (array)$error)}has-danger{/if}">
                                                                <div class="col-md-1">
                                                                    <label class="control-label">Тег</label>
                                                                </div>
                                                                <div class="col-md-8">
                                                                    <select name="tag" class="form-control" {if $ticket->status_id==4}disabled{/if}>
                                                                        <option value=""></option>
                                                                        {foreach $tags as $tag}
                                                                        <option value="{$tag->name}" {if $tag->name==$ticket->tag}selected{/if}>{$tag->name|escape}</option>
                                                                        {/foreach}
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>                                                
                                                <hr />
                                            </div>

                                            {if $error}
                                            <div class="col-md-12">
                                                <ul class="alert alert-danger">
                                                    <li>{$error}</li>
                                                </ul>
                                            </div>
                                            {/if}
                                            
                                            <div class="col-lg-4 col-md-6 col-12">
                                            
                                                <input type="hidden" name="action" value="save" />
                                                <input type="hidden" name="id" value="{$ticket->id}" />
                                                <input type="hidden" name="status_id" value="{$ticket->status_id}" />
                                                <input type="hidden" name="manager_id" value="{$ticket->manager_id}" />
                                                
                                                <div class="row edit-block">
                                                    <div class="col-md-12">
                                                        <div class="form-group row {if in_array('empty_client_fio', (array)$error)}has-danger{/if}">
                                                            <div class="col-md-5">
                                                                <label class="control-label">ФИО клиента</label>
                                                            </div>
                                                            <div class="col-md-7">
                                                                <input oninput="openBlockPrompt('clientFio');" type="text" id="clientFioInput" name="client_fio" value="{$ticket->client_fio}" {if $ticket->status_id==4}disabled{/if} class="form-control" placeholder="" required="true" />
                                                            </div>
                                                        </div>
                                                        <div style="position: relative;">
                                                            <div class="promptBlock" id="clientFioPrompt"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row {if in_array('empty_client_birth', (array)$error)}has-danger{/if}">
                                                            <div class="col-md-5">
                                                                <label class="control-label">Дата рождения</label>
                                                            </div>
                                                            <div class="col-md-7">
                                                                <input oninput="openBlockPrompt('clientBerthDate');" type="text" id="clientBerthDateInput" name="client_birth" value="{if $ticket->client_birth}{$ticket->client_birth|date}{/if}" {if $ticket->status_id==4}disabled{/if} class="form-control js-date-picker" placeholder="" required="true" />
                                                            </div>
                                                        </div>
                                                        <div style="position: relative;">
                                                            <div class="promptBlock" id="clientBerthDatePrompt"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row {if in_array('empty_client_phone', (array)$error)}has-danger{/if}">
                                                            <div class="col-md-5">
                                                                <label class="control-label">Телeфон клиента</label>
                                                            </div>
                                                            <div class="col-md-7">
                                                                <input oninput="openBlockPrompt('clientPhone');" type="text"  id="clientPhoneInput" name="client_phone" value="{$ticket->client_phone|escape}" class="js-phone-mask form-control" placeholder="" required="true" {if $ticket->status_id==4}disabled{/if} />
                                                            </div>
                                                        </div>
                                                        <div style="position: relative;">
                                                            <div class="promptBlock" id="clientPhonePrompt"></div>
                                                        </div>
                                                    </div>
                                                    
                                                </div>
                                            </div>
                                            
                                            <div class="col-lg-4 col-md-6 col-12">
                                            
                                                <div class="row edit-block">
                                                    <div class="col-md-12">
                                                        <div class="form-group row {if in_array('empty_loan_date', (array)$error)}has-danger{/if}">
                                                            <div class="col-md-5">
                                                                <label class="control-label">Дата займа</label>
                                                            </div>
                                                            <div class="col-md-7">
                                                                <input oninput="openBlockPrompt('creditDate');" type="text" id="creditDateInput" name="loan_date" value="{if $ticket->loan_date}{$ticket->loan_date|date}{/if}" class="form-control js-date-picker" placeholder="" required="true" {if $ticket->status_id==4}disabled{/if} />
                                                            </div>
                                                        </div>
                                                        <div style="position: relative;">
                                                            <div class="promptBlock" id="creditDatePrompt"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row {if in_array('empty_loan_summ', (array)$error)}has-danger{/if}">
                                                            <div class="col-md-5">
                                                                <label class="control-label">Сумма займа</label>
                                                            </div>
                                                            <div class="col-md-7">
                                                                <input oninput="openBlockPrompt('creditSumm');" type="text" id="creditSummInput" name="loan_summ" value="{$ticket->loan_summ}" class="form-control" placeholder="" required="true" {if $ticket->status_id==4}disabled{/if} />
                                                            </div>
                                                        </div>
                                                        <div style="position: relative;">
                                                            <div class="promptBlock" id="creditSummPrompt"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row {if in_array('empty_order_number', (array)$error)}has-danger{/if}">
                                                            <div class="col-md-5">
                                                                <label class="control-label">Номер заявки</label>
                                                            </div>
                                                            <div class="col-md-7">
                                                                <input oninput="openBlockPrompt('orderNumber');" type="text" id="orderNumberInput" name="order_number" value="{$ticket->order_number}" class="form-control" placeholder="" {if $ticket->status_id==4}disabled{/if} />
                                                            </div>
                                                        </div>
                                                        <div style="position: relative;">
                                                            <div class="promptBlock" id="orderNumberPrompt"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row {if in_array('empty_contract_number', (array)$error)}has-danger{/if}">
                                                            <div class="col-md-5">
                                                                <label class="control-label">Номер договора</label>
                                                            </div>
                                                            <div class="col-md-7">
                                                                <input oninput="openBlockPrompt('contractNumber');" type="text" id="contractNumberInput" name="contract_number" value="{$ticket->contract_number}" class="form-control" placeholder="" {if $ticket->status_id==4}disabled{/if} />
                                                            </div>
                                                        </div>
                                                        <div style="position: relative;">
                                                            <div class="promptBlock" id="contractNumberPrompt"></div>
                                                        </div>
                                                    </div>
                                                    
                                                </div>
                                            </div>

                                            <script src="js/ticket.js"></script>

                                            <div class="col-lg-4 col-md-6 col-12">
                                                                                            
                                                <div class="row edit-block">
                                                    <div class="col-md-12">
                                                        <div class="form-group row {if in_array('empty_client_fio', (array)$error)}has-danger{/if}">
                                                            <div class="col-md-5">
                                                                <label class="control-label">ФИО принявшего заявку </label>
                                                            </div>
                                                            <div class="col-md-7">
                                                                <input oninput="openBlockPrompt('acceptFio');" id="acceptFioInput" type="text" name="accept_fio" value="{if $ticket->accept_fio}{$ticket->accept_fio|escape}{else}{$manager->name|escape}{/if}" class="form-control" placeholder="" required="true" {if $ticket->status_id==4}disabled{/if} />
                                                            </div>
                                                        </div>
                                                        <div style="position: relative;">
                                                            <div class="promptBlock" id="acceptFioPrompt"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row {if in_array('empty_client_birth', (array)$error)}has-danger{/if}">
                                                            <div class="col-md-5">
                                                                <label class="control-label">Дата поступления обращения</label>
                                                            </div>
                                                            <div class="col-md-7">
                                                                <input oninput="openBlockPrompt('appealDate');" id="appealDateInput" type="text" name="appeal_date" value="{if $ticket->appeal_date}{$ticket->appeal_date|date}{/if}" class="form-control js-date-picker" placeholder="" {if $ticket->status_id==4}disabled{/if} required="true" />
                                                            </div>
                                                        </div>
                                                        <div style="position: relative;">
                                                            <div class="promptBlock" id="appealDatePrompt"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row {if in_array('empty_client_birth', (array)$error)}has-danger{/if}">
                                                            <div class="col-md-5">
                                                                <label class="control-label">Источник</label>
                                                            </div>
                                                            <div class="col-md-7">
                                                                <select name="source" class="form-control" {if $ticket->status_id==4}disabled{/if}>
                                                                    <option value=""></option>                                                                    
                                                                    <option value="Электронная почта" {if $ticket->source=='Электронная почта'}selected{/if}>Электронная почта</option>                                          
                                                                    <option value="Звонок" {if $ticket->source=='Звонок'}selected{/if}>Звонок</option>                                          
                                                                    <option value="Whats`App" {if $ticket->source=='Whats`App'}selected{/if}>Whats`App</option>                                          
                                                                    <option value="Viber" {if $ticket->source=='Viber'}selected{/if}>Viber</option>                                          
                                                                    <option value="Telegram" {if $ticket->source=='Telegram'}selected{/if}>Telegram</option>                                          
                                                                    <option value="SMS" {if $ticket->source=='SMS'}selected{/if}>SMS</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                </div>
                                                
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-actions">
                                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                                    <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                </div>
            </div>

            <div id="tab_logs" class="tab-pane" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        
                        <table class="table table-hover">
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </table>
                        
                    </div>
                </div>
            </div>
			
        </div>
    </div>
    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->
    
    {include file='footer.tpl'}
    
</div>


<div id="modal_close" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            
            <div class="modal-header">
                <h4 class="modal-title">Закрытие тикета</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_close">
                
                    <div class="alert" style="display:none"></div>

                    <input type="hidden" name="action" value="close" />
                    <input type="hidden" name="id" value="{$ticket->id}" />
                    
                    <div class="form-group">
                        <label for="reason_id" class="control-label">Причина закрытия:</label>
                        <select name="reason_id" class="form-control">
                            {foreach $reasons as $r}
                            <option value="{$r->id}">{$r->name|escape}</option>
                            {/foreach}
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="comment" class="control-label">Коментарий:</label>
                        <textarea name="comment" id="comment" class="form-control"></textarea>
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

<div id="modal_add_comment" class="modal fade bd-example-modal-sm show" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" style="display: none; padding-right: 15px;" aria-modal="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            
            <div class="modal-header">
                <h4 class="modal-title">Добавить комментарий</h4>
                <button type="button" class="close" onclick="closeTicetCommentForm();">×</button>
            </div>
            <div class="modal-body">
                <form>
                    
                    <input type="hidden" name="id_ticket" id="id_ticket" value="{$ticket->id}">
                    <input type="hidden" name="manager" id="manager_ticket" value="{$manager->id}">
                    
                    <div class="alert" style="display:none"></div>
                    
                    <div class="form-group">
                        <label for="name" class="control-label text-white">Комментарий:</label>
                        <textarea class="form-control" name="comment" id="comment_ticket"></textarea>
                    </div>
                    <div class="form-action">
                        <button type="button" class="btn btn-danger waves-effect" onclick="closeTicetCommentForm();">Отмена</button>
                        <button type="button" class="btn btn-success waves-effect waves-light" onclick="saveTicketComment();">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="modal_manager" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            
            <div class="modal-header">
                <h4 class="modal-title">Перенаправление тикета</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_close">
                
                    <input type="hidden" name="action" value="manager" />
                    <input type="hidden" name="id" value="{$ticket->id}" />

                    <div class="alert" style="display:none"></div>
                    
                    <div class="form-group">
                        <label for="manager_id" class="control-label">Ответственный:</label>
                        <select name="manager_id" class="form-control">
                            {foreach $managers as $m}
                            <option value="{$m->id}">{$m->name|escape}</option>
                            {/foreach}
                        </select>
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