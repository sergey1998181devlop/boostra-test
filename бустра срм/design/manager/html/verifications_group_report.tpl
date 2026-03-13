{$meta_title='Время обработки заявок верификаторами' scope=parent}

{capture name='page_scripts'}
    <script src="design/manager/assets/plugins/moment/moment.js"></script>

    <script src="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <!-- Date range Plugin JavaScript -->
    <script src="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script src="design/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    <script>
        $(function(){
            $('.daterange').daterangepicker({
                autoApply: true,
                locale: {
                    format: 'DD.MM.YYYY'
                },
                default:''
            });
        })
    </script>
{/capture}

{capture name='page_styles'}
    <link href="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    <!-- Daterange picker plugins css -->
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
                    <span>{$meta_title}</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">{$meta_title}</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{$meta_title} за период {if $date_from}{$date_from|date} - {$date_to|date}{/if}</h4>
                        <div id="result" class="">
                            <form>
                                <div class="row">
                                    <div class="col-12 col-md-4">
                                        <div class="input-group mb-3">
                                            <input type="text" name="daterange" class="form-control daterange" value="{if $from && $to}{$from}-{$to}{/if}" />
                                            <div class="input-group-append">
                                                <span class="input-group-text">
                                                    <span class="ti-calendar"></span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <button type="submit" class="btn btn-info">Сформировать</button>

                                        <button onclick="return download();" type="button" class="btn btn-success">
                                            <i class="ti-save"></i> Выгрузить
                                        </button>
                                    </div>

                                </div>
                            </form>
                            <table class="table table-bordered table-hover">
                                <thead class="position-sticky">
                                <tr>
                                    <th>Менеджер</th>
                                    <th>Количество заявок</th>
                                    <th>Средняя скорость обработки менеджером (сек)</th>
                                </tr>
                                </thead>
                                <tbody>
                                {if $managersAvgSpeed}
                                    {foreach $managersAvgSpeed as $managerAvgSpeed}
                                        <tr>
                                            <td>
                                                {if $can_see_manager_url}
                                                    <a href="/manager/{$managerAvgSpeed['managerId']}" target="_blank">
                                                        {$managerAvgSpeed['name_1c']}
                                                    </a>
                                                {else}
                                                    {$managerAvgSpeed['name_1c']}
                                                {/if}
                                            </td>
                                            <td class="text-right">
                                                <a
                                                    href="/verifications_report?daterange={$from}-{$to}&manager_id={$managerAvgSpeed['managerId']}"
                                                    title="Открыть список всех заявок менеджера"
                                                    target="_blank"
                                                >
                                                    {$managerAvgSpeed['count']}
                                                </a>
                                            </td>
                                            <td class="text-right">{$managerAvgSpeed['avg']}</td>
                                        </tr>
                                    {/foreach}
                                {else}
                                    <tr>
                                        <td colspan="3" class="text-danger text-center">Данные не найдены</td>
                                    </tr>
                                {/if}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {include file='footer.tpl'}
</div>

<script>
    function download() {
        const dateRange = $('input[name="daterange"]').val();
        const query = (new URLSearchParams({
            daterange: dateRange,
        })).toString();
        window.open(
            '{$verificationsGroupUri}?action=download&' + query,
            '_blank'
        );
        return false;
    }
</script>