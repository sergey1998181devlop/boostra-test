
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
                <h3 class="text-themecolor mb-0 mt-0"><i class="mdi mdi-closed-caption"></i> Справочник тегов</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Справочник тегов</li>
                </ol>
            </div>
            
            <div class="col-md-6 col-4 align-self-center">
                <a href="javascript:void(0);" onclick="newTicketTag('newTicketTag')" class="btn float-right hidden-sm-down btn-success js-open-add-modal">
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
                        <div style="position: relative;">
                            <div class="promptBlock" id="newTicketTag" style=""></div>
                        </div>
                        <h4 class="card-title">Теги</h4>
                        <div id="basicgrid" class="jsgrid" style="position: relative; width: 100%;">
                            <div class="jsgrid-grid-header jsgrid-header-scrollbar">
                                <table class="jsgrid-table table table-striped table-hover">
                                    <tr class="jsgrid-header-row">
                                        <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable">
                                            Название тега
                                        </th>
                                        <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable">
                                            Цвет
                                        </th>
                                        <th style="width: 80px;" class="jsgrid-header-cell">
                                        </th>
                                    </tr>
                                </table>
                                <div class="jsgrid-grid-body">
                                    <table class="jsgrid-table table table-striped table-hover" onclick="closeAllOpenBlocksPrompt()">
                                        {foreach $tags as $tag}
                                            <tr class="jsgrid-row">
                                                <td  class="jsgrid-cell jsgrid-align-right">                                                
                                                    {$tag->name}
                                                </td>
                                                <td class="jsgrid-cell jsgrid-align-right">                                                
                                                    <div class="row">
                                                        <div class="col-8">
                                                            <div class="row">
                                                                <div class="col-4">
                                                                    {$tag->color} 
                                                                </div>
                                                                <div class="col-8">
                                                                    <input type="color" value="{$tag->color}" disabled>
                                                                </div>
                                                            <div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="jsgrid-cell jsgrid-align-right" style="text-align:right;">                                                
                                                    <img title="Редактировать тег" onclick="editTicketTag('{$tag->id}')" style="width: 15px; cursor: pointer;" src="/design/manager/assets/images/icon/edit.svg">
                                                </td>
                                            </tr>
                                        {/foreach}
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="js/ticket.js"></script>
</div>
{include file="footer.tpl"}