{$meta_title='Отчет времени обработки заявок' scope=parent}

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

                        {include file='html_blocks/pagination.tpl'}

                        <div id="result" class="">
                            <table class="table table-bordered table-hover">
                                <thead class="position-sticky">
                                <form>
                                    <div class="row">
                                        <div class="col-12 col-md-4">
                                            <div class="input-group mb-3">
                                                <input type="text" name="daterange" class="form-control daterange" value="{if $from && $to}{$from}-{$to}{/if}">
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
                                <tr>
                                    <th><input type="text" class="form-control" name="order_id" value="{$order_id}" /></th>
                                    <th><input type="text" class="form-control" name="date" value="{$date}" /></th>
                                    <th>
                                        <select class="form-control" name="have_close_credits">
                                            <option value=""></option>
                                            <option value="1" {if $have_close_credits === '1'} selected="selected"{/if}>пк</option>
                                            <option value="0" {if $have_close_credits === '0'} selected="selected"{/if}>нк</option>
                                        </select>
                                    <th></th>
                                    <th>
                                        <select class="form-control" name="manager_id">
                                            <option value=""></option>
                                            {foreach $managers as $managerId => $managerName}
                                                <option value="{$managerId}" {if $managerId === $manager_id} selected="selected"{/if}>{$managerName}</option>
                                            {/foreach}
                                        </select>
                                    </th>
                                    <th>
                                        <select class="form-control" name="status_id">
                                            <option value=""></option>
                                            {foreach $orderStatuses as $statusId => $statusName}
                                                <option value="{$statusId}" {if strval($statusId) === $status_id} selected="selected"{/if}>{$statusName}</option>
                                            {/foreach}
                                        </select>
                                    </th>
                                    <th colspan="4"></th>
                                </tr>
                                </form>
                                <tr>
                                    <th>Заявка</th>
                                    <th>Дата размещения</th>
                                    <th style="width: 90px;">НК/ПК</th>
                                    <th>ФИО</th>
                                    <th>Менеджер</th>
                                    <th>Статус (текущий)</th>
                                    <th>Дата взятия менеджером</th>
                                    <th>Статус(первый)</th>
                                    <th>Дата первого статуса</th>
                                    <th>Скорость обработки (сек)</th>
                                </tr>
                                </thead>
                                <tbody>
                                {if $items}
                                    {foreach $items as $item}
                                        <tr>
                                            <td><a href="/order/{$item->order_id}">{$item->order_id}</a></td>
                                            <td>{$item->date}</td>
                                            <td>{if $item->have_close_credits === '1'}пк{else}нк{/if}</td>
                                            <td>{$item->fio}</td>
                                            <td>{$managers[$item->manager_id]}</td>
                                            <td>{$orderStatuses[$item->status]}</td>
                                            <td>{$item->date_set_manager}</td>
                                            <td>{$orderStatuses[$item->first_status]}</td>
                                            <td>{$item->date_set_first_status}</td>
                                            <td>{$item->speedManager}</td>
                                        </tr>
                                    {/foreach}
                                {else}
                                    <tr>
                                        <td colspan="10" class="text-danger text-center">Данные не найдены</td>
                                    </tr>
                                {/if}
                                </tbody>
                            </table>
                        </div>
                        {include file='html_blocks/pagination.tpl'}
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
        const order_id = $('input[name="order_id"]').val();
        const date = $('input[name="date"]').val();
        const have_close_credits = $('select[name="have_close_credits"]').val();
        const manager_id = $('select[name="manager_id"]').val();
        const status_id = $('select[name="status_id"]').val();
        const query = (new URLSearchParams({
            daterange: dateRange,
            order_id: order_id,
            date: date,
            have_close_credits: have_close_credits,
            manager_id: manager_id,
            status_id: status_id
        })).toString();
        window.open(
            '{$verificationsUri}?action=download&' + query,
            '_blank'
        );
        return false;
    }
</script>