{$meta_title='КД Доступность ступеней обучения' scope=parent}

{capture name='page_styles'}
    <link href="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="design/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">
{/capture}

<style>
    tr.small td {
        padding: 0.25rem;
    }
    .table thead th, .table th {
        border: 1px solid;
        font-size: 10px;
    }
    thead.position-sticky {
        top: 0;
        background-color: #272c33;
    }
</style>

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-file-chart"></i>
                    <span>КД Доступность ступеней обучения</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">КД Доступность ступеней обучения</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <form id="report_form">
                            <div class="row mb-3">
                                <div class="col-6 col-md-4">
                                    <button type="button" onclick="loadData();" class="btn btn-info">Сформировать</button>
                                    <button onclick="return download();" type="button" class="btn btn-success"><i class="ti-save"></i> Выгрузить</button>
                                </div>
                            </div>
                        </form>
                        <div id="result" class="">
                            <table class="table table-bordered table-hover">
                                <tbody>
                                {if $result}
                                    <tr style="font-weight: bold;">
                                        {foreach $result as $row}
                                                <td>Доступна {$row->level}</td>
                                        {/foreach}
                                        <td>Итого</td>
                                        <td>КД ВСЕГО</td>
                                    </tr>
                                    <tr>
                                        {foreach $result as $row}
                                                <td>{$row->user_count}</td>
                                        {/foreach}
                                        <td>{$total}</td>
                                        <td>{$totalSales}</td>
                                    </tr>
                                {else}
                                    <tr>
                                        <td colspan="14" class="text-danger text-center">Данные не найдены</td>
                                    </tr>
                                {/if}
                                </tbody>
                            </table>
                        </div>
                        <strong class=""></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {include file='footer.tpl'}
</div>

{capture name='page_scripts'}

    <script src="design/manager/assets/plugins/moment/moment.js"></script>
    <script>
        function loadData(){
            $('.preloader').show();
            let filter_data = $('#report_form').serialize();
            $("#result").load('{$smarty.server.REQUEST_URI}?ajax=1&' + filter_data + ' #result table', function (response, status, xhr) {
                $('.preloader').hide();
                if (status == "error") {
                    alert('Произошла ошибка сервера подробности в консоли');
                    console.error('error load text: ' + xhr.status + " " + xhr.statusText);
                }
            });
        }

        function download() {
            let filter_data = $('#report_form').serialize();
            window.open(
                '{$smarty.server.REQUEST_URI}?action=download&' + filter_data,
                '_blank' // <- This is what makes it open in a new window.
            );
        }
    </script>
{/capture}