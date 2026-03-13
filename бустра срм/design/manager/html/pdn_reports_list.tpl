{$meta_title='Отчеты по портфелю' scope=parent}

{capture name='page_styles'}
    <style>
    .table td {
        text-align:center!important;
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
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-file-chart"></i> 
                    <span>Отчеты по портфелю</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Отчеты по портфелю</li>
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
                        <h4 class="card-title">Отчеты по портфелю</h4>
                        <table class="table table-hover">
                            
                            <tr>
                                <th>Дата формирования</th>
                                <th class="text-center">
                                    Активные займы
                                </th>
                                <th class="text-center">
                                    Выданные займы за квартал
                                </th>
                            </tr>
                            
                            {foreach $reports as $item}
                            <tr>
                                <td>
                                    <strong>{$item['date']}</strong>
                                </td>
                                <td>
                                    <strong>
                                    {if !empty($item['pdn_remains'])}
                                        <a href="{$smarty.server.REQUEST_URI}?action=download&file_id={$item['pdn_remains']['id']}">Скачать</a>
                                    {/if}
                                    </strong>
                                </td>
                                <td>
                                    <strong>
                                    {if !empty($item['pdn_quarterly'])}
                                        <a href="{$smarty.server.REQUEST_URI}?action=download&file_id={$item['pdn_quarterly']['id']}">Скачать</a>
                                    {/if}
                                    </strong>
                                </td>
                            </tr>
                            {/foreach}
                            
                        </table>
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