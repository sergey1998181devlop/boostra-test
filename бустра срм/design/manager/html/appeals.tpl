{$meta_title='Обращения' scope=parent}
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
                <h3 class="text-themecolor mb-0 mt-0"><i class="mdi mdi-closed-caption"></i>{$meta_title}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">{$meta_title}</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">                
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
                                        <td style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'id_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'id_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'id_asc'}<a href="{url page=null sort='id_desc'}">Номер обращения</a>
                                            {else}<a href="{url page=null sort='id_asc'}">Номер обращения</a>{/if}
                                        </td>
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
                                        <td class="jsgrid-header-cell">
                                            № тикета
                                        </td>
                                    </tr>
                                    {foreach $appeals AS $appeal}
                                        <tr class="jsgrid-row" id="main_{$appeal->Id}">                                              
                                            <td  class="jsgrid-cell jsgrid-align-right">
                                                № <a href="appeals/{$appeal->Id}">{$appeal->Id}</a>
                                            </td>
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
                                            <td class="jsgrid-cell">
                                                {if $appeal->TicketId}
                                                    <a href="/ccmytickets/{$appeal->TicketId}">Тикет №{$appeal->TicketId}</a>
                                                {/if}
                                            </td>
                                        </tr>
                                    {/foreach}
                                </table>
                                {include file="tickets/pagination.tpl"}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
             </div>
        </div>
        {include file='footer.tpl'}
    </div>
    
<script>
    var appeals = new ViewImage();
    appeals.alias = 'appeals';
</script>
    