{$meta_title='Обращение' scope=parent}
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
                <h3 class="text-themecolor mb-0 mt-0"><i class="mdi mdi-closed-caption"></i>{$meta_title} №{$appeal->Id}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">{$meta_title}</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
                {if !$appeal->TicketId}
                        <div class="row" style=" padding-bottom: 10px;">
                            <div class="col-md-6 col-8 align-self-center">
                                
                            </div>
                            <div class="col-md-6 col-4 align-self-center">
                                <a href="javascript:void(0);" onclick="task.openBlock('addNewTicketForAppeal');" class="btn float-right hidden-sm-down btn-success js-open-add-modal">
                                    <i class="mdi mdi-plus-circle"></i> Создать тикет
                                </a>
                            </div>
                        </div>
                {/if}
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{$meta_title}</h4>
                        <div id="basicgrid" class="jsgrid" style="position: relative; overflow-x: auto; width: auto; white-space: normal; padding-bottom: 15px;">
                            <div class="jsgrid-grid-header jsgrid-header-scrollbar">
                                <table class="jsgrid-table table table-striped table-hover">
                                    <tr class="jsgrid-header-row">
                                        <td style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'date_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'date_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'date_asc'}<a href="{url page=null sort='date_desc'}">Дата обращения</a>
                                            {else}<a href="{url page=null sort='date_asc'}"> Дата обращения</a>{/if}
                                        </td>
                                        <td class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'them_asc'} jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'them_desc'} jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'them_asc'}<a href="{url page=null sort='them_desc'}">Тема обращения</a>
                                            {else}<a href="{url page=null sort='them_asc'}"> Тема обращения</a>{/if}
                                        </td>
                                        <td class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'text_asc'} jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'text_desc'} jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'text_asc'}<a href="{url page=null sort='text_desc'}">Текст обращения</a>
                                            {else}<a href="{url page=null sort='text_asc'}"> Текст обращения</a>{/if}
                                        </td>
                                        <td class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'telephone_asc'} jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'telephone_desc'} jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'telephone_asc'}<a href="{url page=null sort='telephone_desc'}">Телефон отправителя</a>
                                            {else}<a href="{url page=null sort='telephone_asc'}"> Телефон отправителя</a>{/if}
                                        </td>
                                        <td class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'email_from_asc'} jsgrid-header-sort jsgrid-header-sort-asc {elseif $sort == 'email_from_desc'} jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'email_from_asc'}<a href="{url page=null sort='email_from_desc'}">Email отправителя</a>
                                            {else}<a href="{url page=null sort='email_from_asc'}"> Email отправителя</a>{/if}
                                        </td>
                                        <td class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'email_to_asc'}jsgrid-header-sort jsgrid-header-sort-asc {elseif $sort == 'email_to_desc'} jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'email_to_asc'}<a href="{url page=null sort='email_to_desc'}">Email получателя</a>
                                            {else}<a href="{url page=null sort='email_to_asc'}"> Email получателя</a>{/if}
                                        </td>
                                    </tr>
                                        <tr class="jsgrid-row" id="main_{$appeal->Id}">   
                                            <td  class="jsgrid-cell">
                                                {$appeal->AppealDate}
                                            </td>
                                            <td class="jsgrid-cell">
                                                {$appeal->Them}
                                            </td>
                                            <td class="jsgrid-cell">
                                                {$appeal->Text}
                                            </td>
                                            <td class="jsgrid-cell">
                                                {$appeal->Phone}
                                            </td>
                                            <td class="jsgrid-cell">
                                                {$appeal->Email}
                                            </td>
                                            <td class="jsgrid-cell">
                                                {$appeal->ToEmail}
                                            </td>
                                        </tr>
                                </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
             </div>
        </div>
        {include file='footer.tpl'}
    </div>

<div id="addNewTicketForAppeal" class="modalBlock" style="left: 10%; right: 10%; bottom: 10px;">
    <div>
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Создать новый тикет</h4>
                <button type="button" class="close" onclick="task.closeBlock('addNewTicketForAppeal');">×</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            {include file="promptBlockTasks.tpl"}
                                            <div class="col-lg-4 col-md-6 col-12">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label class="control-label">ФИО ответственного</label>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <select id="executManagerIdNewTiketForAppeal" class="form-control">
                                                            {foreach $managers AS $val}
                                                            <option value="{$val->id}" {if $val->id==$curentManager->id}selected{/if}>{$val->name}</option>
                                                            {/foreach}
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label class="control-label">Тема обращения</label>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <select id="themNewTiketForAppeal" class="form-control">
                                                            {foreach $subjects AS $val}
                                                            <option value="{$val->id}" {if $appeal->Them==$val->name}selected{/if}>{$val->name}</option>
                                                            {/foreach}
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label class="control-label">Источник</label>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <select id="inputChanelNewTiketForAppeal" class="form-control">                                
                                                            <option value="Электронная почта" selected>Электронная почта</option>                                          
                                                            <option value="Звонок">Звонок</option>                                          
                                                            <option value="Whats`App">Whats`App</option>                                          
                                                            <option value="Viber">Viber</option>                                          
                                                            <option value="Telegram">Telegram</option>                                          
                                                            <option value="SMS">SMS</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label class="control-label">Дата поступления обращения</label>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <input id="dateCreateNewTiketForAppeal" value="{$appeal->AppealDate}" class="form-control"/>
                                                    </div>
                                                </div>
                                                <input type="hidden" id="managerIdNewTiketForAppeal" value="{$curentManager->id}"/>
                                                <input type="hidden" id="appealNumberNewTiketForAppeal" value="{$appeal->Id}"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div  class="col-md-12">
                                        <label class="control-label">Комметарий</label>
                                    </div>
                                    <div  class="col-md-12">
                                        <textarea id="commentNewTiketForAppeal" class="form-control"></textarea>
                                    </div>
                                </div>
                </div>
                <div class="form-action" id="action">
                    <button type="button" class="btn btn-danger waves-effect" onclick="task.closeBlock('addNewTicketForAppeal');">Отмена</button>
                    <button type="button" class="btn btn-success waves-effect waves-light" onclick="task.saveNewTicketForAppeal();task.closeBlock('action');">Сохранить</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var appeals = new ViewImage();
    appeals.alias = 'appeals';
</script>