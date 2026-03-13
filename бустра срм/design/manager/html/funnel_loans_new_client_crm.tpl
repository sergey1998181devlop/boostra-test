{$meta_title='Воронка займы Новый клиент из CRM' scope=parent}

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

{function name=generateTotals}
    {if $totals}
        <tr class="small bg-inverse text-danger">
            <td style="vertical-align: middle;" rowspan="2" colspan="{$fields_name|count - $totals['items']|count}"><b>Всего:</b></td>
            {foreach $totals['items'] as $item}
                <td class="text-danger"><b>{$item}</b></td>
            {/foreach}
        </tr>
        <tr class="small bg-inverse text-warning">
            {foreach $totals['cv'] as $item}
                <td>{$item}</td>
            {/foreach}
        </tr>
    {/if}
{/function}

{function name=generateValues}
        <tr>
            <td rowspan="{$values|count}" style="vertical-align: middle">{$date}</td>
            {foreach $values['items'] as $value}
                <td>{$value}</td>
            {/foreach}
        </tr>
        <tr class="bg-dark">
            {foreach $values['cv'] as $value}
                <td>{$value}</td>
            {/foreach}
        </tr>
{/function}

{function name=generateRow}
    {foreach $array as $date => $values}
        {if $values['items']}
            {generateValues array=$values date=$date}
        {else}
            {foreach $values as $utm_source => $utm_source_values}
                {if $utm_source_values['items']}
                    <tr>
                        {if $utm_source_values@first}
                            <td rowspan="{$utm_source_values|count * $values|count}" style="vertical-align: middle">{$date}</td>
                        {/if}
                        <td rowspan="{$utm_source_values|count}" style="vertical-align: middle">{$utm_source}</td>
                        {foreach $utm_source_values['items'] as $value}
                            <td>{$value}</td>
                        {/foreach}
                    </tr>
                    <tr>
                        {foreach $utm_source_values['cv'] as $value}
                            <td class="bg-dark">{$value}</td>
                        {/foreach}
                    </tr>
                {else}
                    {foreach $utm_source_values as $webmaster => $webmaster_value}
                        <tr>
                            {if $webmaster_value@first}
                                <td rowspan="{$values|count * $utm_source_values|count * $webmaster_value|count}" style="vertical-align: middle">{$date}</td>
                            {/if}

                            {if $webmaster_value@first}
                                <td rowspan="{$utm_source_values|count * $webmaster_value|count}" style="vertical-align: middle">{$utm_source}</td>
                            {/if}

                            <td rowspan="{$webmaster_value|count}" style="vertical-align: middle">{$webmaster}</td>

                            {foreach $webmaster_value['items'] as $value}
                                <td>{$value}</td>
                            {/foreach}
                        </tr>
                        <tr>
                            {foreach $webmaster_value['cv'] as $value}
                                <td class="bg-dark">{$value}</td>
                            {/foreach}
                        </tr>
                    {/foreach}
                {/if}
            {/foreach}
        {/if}
    {/foreach}
{/function}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-file-chart"></i>
                    <span>Воронка займы Новый клиент из CRM</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Воронка займы Новый клиент из CRM</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            Отчет за период {if $date_from}{$date_from|date} - {$date_to|date}{/if}
                            {if $filter_source}
                                {foreach $filter_source as $fs}
                                    {$fs}{if !$fs@last}, {/if}
                                {/foreach}
                            {/if}
                            <small class="animate-flashing text-danger"> (Разбивка по всем источникам нагружает систему! Из списка выберите несколько или "ВСЕ".)</small>
                        </h4>
                        <p class="text-warning">Данные корректны с 13.07.2023</p>
                        <form id="report_form">
                            <div class="row">
                                <div class="col-6 col-md-2">
                                    <div class="input-group mb-3">
                                        <input type="text" name="date_range" class="form-control daterange" value="{if $from && $to}{$from}-{$to}{/if}">
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <span class="ti-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-2">
                                    <div class="mb-3">
                                        <select class="form-control" name="filter_group_by">
                                            <option value="day" {if !$filter_client}selected{/if}>По дням</option>
                                            <option value="month" {if $filter_client == 'nk'}selected{/if}>По месяцам</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6 col-md-2">
                                    <div class="mb-3">
                                        <div class="btn-group btn-block">
                                            <button class="btn btn-block btn-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="false" aria-expanded="true" data-flip="false">
                                                Источники
                                            </button>
                                            <div class="dropdown-menu p-2" >
                                                {foreach $sources as $source}
                                                    <div class="form-group">
                                                        <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                                            <input name="filter_utm_source[]" type="checkbox" class="custom-control-input" id="filter_source_{$source@index}" value="{$source}"  />
                                                            <label class="custom-control-label" for="filter_source_{$source@index}">
                                                                {$source}
                                                            </label>
                                                        </div>
                                                    </div>
                                                {/foreach}
                                                <div class="form-group">
                                                    <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                                        <input name="filter_utm_source[]" type="checkbox" class="custom-control-input" id="filter_source_all" value="all"  />
                                                        <label class="custom-control-label" for="filter_source_all">
                                                            ВСЕ
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-2">
                                    <div class="mb-3">
                                        <div class="btn-group btn-block dropdown-click-over">
                                            <button disabled class="btn btn-block btn-secondary dropdown-toggle" id="dropdown-button-webmaster_id" type="button" data-toggle="dropdown" aria-haspopup="false" aria-expanded="true" data-flip="false">
                                                Выбор web-id
                                            </button>
                                            <div class="dropdown-menu p-2" id="dropdown-webmaster_id"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-auto">
                                    <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                        <input name="filter_user_registered" type="checkbox" class="custom-control-input" id="filter_user_registered" value="1"  />
                                        <label class="custom-control-label" for="filter_user_registered">
                                            Отвалы
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-auto">
                                    <button type="button" onclick="loadData();" class="btn btn-info">Сформировать</button>
                                </div>
                                <div class="col-md-auto">
                                    <button onclick="download();" type="button" class="btn btn-success"><i class="ti-save"></i> Выгрузить</button>
                                </div>
                            </div>

                        </form>
                        <div id="result" class="">
                            <table class="table table-bordered table-hover">
                                {if $results}
                                    <thead class="position-sticky">
                                        <tr>
                                            {foreach $fields_name as $field_name}
                                                <th>{$field_name}</th>
                                            {/foreach}
                                        </tr>
                                        {generateTotals totals=$totals}
                                    </thead>
                                {/if}
                                <tbody>
                                    {if $results}
                                        {generateRow array=$results}
                                        {generateTotals totals=$totals}
                                    {else}
                                        <tr>
                                            <td colspan="{$fields_name|count}" class="text-danger text-center">Данные не найдены</td>
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
    <script src="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script src="design/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    <script>
        $(function(){
            $('.daterange').daterangepicker({
                autoApply: true,
                locale: {
                    format: 'YYYY.MM.DD'
                },
                default:''
            });
        });

        // подгрузка webmaster_id
        $('[name="filter_utm_source[]"]').on('change', function (e) {
            let $_input_utm_source_all = $('#filter_source_all'),
                $_button = $('#dropdown-button-webmaster_id'),
                $_utm_source_text = $("#utm_source_selected"),
                $_checked_input = $('[name="filter_utm_source[]"]:checked'),
                is_all_checked = $_input_utm_source_all.prop('checked'),
                total_utm_source = $_checked_input.length;

            if (!is_all_checked && total_utm_source === 1) {
                $_button.prop('disabled', false);
                $("#report_form").addClass('data-loading');
                $("#dropdown-webmaster_id").load('/web_master_report?ajax=1&action=getWebmasterIds&utm_source=' + $_checked_input.val(), function () {
                    $("#report_form").removeClass('data-loading');
                });
                $_utm_source_text.text($_checked_input.val());
            } else {
                $_utm_source_text.empty();
                $_button.prop('disabled', true);
                $("#dropdown-webmaster_id").empty();
            }
        });

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
                '{$smarty.server.REQUEST_URI}?ajax=1&download=1&' + filter_data,
                '_blank' // <- This is what makes it open in a new window.
            );
        }
    </script>
{/capture}
