{$meta_title = 'Отчёт оплат по р/с' scope=parent}

{capture name='page_scripts'}
    <script src="design/manager/assets/plugins/moment/moment.js"></script>
    <script src="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <!-- Date range Plugin JavaScript -->
    <script src="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script src="design/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    <script>
        $(function () {
            $('.daterange').daterangepicker({
                autoApply: true,
                locale: {
                    format: 'DD.MM.YYYY'
                },
                default: ''
            });

            function updateFilters() {
                const paramsObj = {
                    user: $('input.user').val() || '',
                    status: $('select.status').val() || '',
                };

                const dateRange = $('input[name="daterange"]').val();
                if (dateRange) {
                    paramsObj.daterange = dateRange;
                }

                const params = new URLSearchParams(paramsObj).toString();
                window.open('{$reportUri}?' + params, '_self');
            }

            $('select.filter').on('change', () => {
                updateFilters();
            });

            let delayTimer;
            $('input.user, input.status').on('keyup', function() {
                clearTimeout(delayTimer);
                delayTimer = setTimeout(() => {
                    updateFilters();
                }, 500);
            });
        });

        function download() {
            const params = {
                daterange: $('input[name="daterange"]').val(),
                user: $('input[name="user"]').val(),
                status: $('select[name="status"]').val()
            };

            const queryString = new URLSearchParams(params).toString();

            const url = '{$reportUri}?action=download&' + queryString;

            // Выполняем Ajax-запрос
            $.ajax({
                url: url,
                method: 'GET',
                xhrFields: {
                    responseType: 'blob'
                },
                success: function (data, textStatus, jqXHR) {
                    const contentType = jqXHR.getResponseHeader('Content-Type');
                    if (contentType && contentType.includes('application/json')) {
                        const response = JSON.parse(data);
                        if (response.status === 'error') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Ошибка',
                                text: response.message
                            });
                            return;
                        }
                    }
    
                    const blob = new Blob([data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                    const downloadUrl = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = downloadUrl;
                    a.download = 'payments_rs_report_' + new Date().toISOString().slice(0, 10) + '.xlsx';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(downloadUrl);
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ошибка',
                        text: 'Произошла ошибка при выполнении запроса'
                    });
                }
            });

            return false;
        }
    </script>
    <script src="design/manager/js/payments-rs-report.js"></script>
{/capture}

{capture name='page_styles'}
    <link href="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet"
          type="text/css"/>
    <!-- Daterange picker plugins css -->
    <link href="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="design/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">
{/capture}

<style>
    tr.small td {
        padding: 0.25rem;
    }

    .table thead th, .table th {
        border: 2px solid;
        font-size: 12px;
        min-width: 150px;
    }

    .table thead td, .table td {
        font-size: 12px;
    }

    thead.position-sticky {
        top: 0;
        background-color: #272c33;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .table td, .table th {
        white-space: normal;
        word-wrap: break-word;
    }
</style>

<div class="page-wrapper" data-report-uri="{$reportUri}">
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
                        <h4 class="card-title">{$meta_title} за
                            период {if $date_from}{$date_from} - {$date_to}{/if}</h4>

                        <form>
                            <div class="row">
                                <div class="col-12 col-md-4">
                                    <div class="input-group mb-3">
                                        <input type="text" name="daterange" class="form-control daterange"
                                               value="{if $date_from && $date_to}{$date_from} - {$date_to}{/if}">
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <span class="ti-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <button type="submit" class="btn btn-info">Отфильтровать</button>

                                    <button onclick="return download();" type="button" class="btn btn-success">
                                        <i class="ti-save"></i> Выгрузить
                                    </button>

                                    <button onclick="return window.open('{$reportUri}','_self');" type="button" class="btn btn-warning">
                                        <i class="ti-save"></i> Сбросить
                                    </button>
                                </div>
                            </div>

                            <div class="row mt-2 mb-4">
                                {foreach from=$filterConfigurations item=filter}
                                    <div class="col-12 col-md-2 py-1">
                                        <label for="{$filter.name}">{$filter.label}</label>
                                        {if $filter.type == 'select'}
                                            <select id="{$filter.name}" name="{$filter.name}" class="form-control form-control-sm filter {$filter.name}">
                                                <option selected value="">Все</option>
                                                {foreach from=$filter.options key=key item=value}
                                                    <option value="{$key}" {if $filter.value == $key}selected{/if}>
                                                        {$value}
                                                    </option>
                                                {/foreach}
                                            </select>
                                        {else}
                                            <input type="text" id="{$filter.name}" name="{$filter.name}" class="form-control form-control-sm filter {$filter.name}" value="{$filter.value}">
                                        {/if}
                                    </div>
                                {/foreach}
                            </div>
                        </form>

                        <div id="result" class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="position-sticky">
                                <tr>
                                    {assign var="queryString" value=""}
                                    {foreach from=$smarty.get key=key item=value}
                                        {if $key != 'sort' && $key != 'order'}
                                            {assign var="queryString" value="$queryString&$key=$value"}
                                        {/if}
                                    {/foreach}

                                    {foreach from=$reportHeaders item=header}
                                        <th>
                                            {if $header.sort_key}
                                                {if $smarty.get.sort == $header.sort_key && $smarty.get.order == 'desc'}
                                                    <a href="{$reportUri}?sort={$header.sort_key}&order=asc{$queryString}">
                                                        {$header.label} <i class="fa fa-sort-down"></i>
                                                    </a>
                                                {elseif $smarty.get.sort == $header.sort_key && $smarty.get.order == 'asc'}
                                                    <a href="{$reportUri}?sort={$header.sort_key}&order=desc{$queryString}">
                                                        {$header.label} <i class="fa fa-sort-up"></i>
                                                    </a>
                                                {else}
                                                    <a href="{$reportUri}?sort={$header.sort_key}&order=desc{$queryString}">
                                                        {$header.label} <i class="fa fa-sort"></i>
                                                    </a>
                                                {/if}
                                            {else}
                                                {$header.label}
                                            {/if}
                                        </th>
                                    {/foreach}
                                    <th>Действия</th>
                                </tr>
                                </thead>
                                <tbody>
                                {if $reportRows}
                                    {foreach $reportRows as $reportRow}
                                        <tr data-id="{$reportRow.id}">
                                            <td>
                                                {if $can_see_client_url}
                                                    <a href="/client/{$reportRow.user_id}" target="_blank">{$reportRow.lastname} {$reportRow.firstname} {$reportRow.patronymic}</a>
                                                {else}
                                                    {$reportRow.lastname} {$reportRow.firstname} {$reportRow.patronymic}
                                                {/if}
                                            </td>
                                            <td>
                                                {if $can_see_client_url}
                                                    <a href="/client/{$reportRow.user_id}" target="_blank">    {$reportRow.user_id}</a>
                                                {else}
                                                    {$reportRow.user_id}
                                                {/if}
                                            </td>
                                            <td>
                                                {if $can_see_client_url}
                                                    <a href="/order/{$reportRow.order_id}" target="_blank">{$reportRow.contract_number}</a>
                                                {else}
                                                    {$reportRow.contract_number}
                                                {/if}
                                            </td>
                                            <td>
                                                {if $reportRow.file}
                                                    <a href="{$reportRow.file}" class="open-file-modal" data-file="{$reportRow.file}">
                                                        {$reportRow.name}
                                                    </a>
                                                {else}
                                                    Не загружено
                                                {/if}
                                            </td>
                                            <td>
                                                {if $reportRow.status == 'new'}
                                                    <span class="badge badge-warning">Новый</span>
                                                {elseif $reportRow.status == 'approved'}
                                                    <span class="badge badge-success">Одобрено</span>
                                                {elseif $reportRow.status == 'cancelled'}
                                                    <span class="badge badge-danger">Отклонено</span>
                                                {/if}
                                            </td>
                                            <td>{$reportRow.created_at|date_format:'%d.%m.%Y %H:%M'}</td>
                                            <td>{$reportRow.source}</td>
                                            <td class="status-cell">
                                                <div class="d-flex align-reportRows-center">
                                                    {if in_array($reportRow.status, ['new', 'cancelled'])}
                                                        <button class="btn btn-success btn-sm mr-2" onclick="updateStatus({$reportRow.id}, 'approved', this); return false;" title="Одобрить">
                                                            <i class="fa fa-check"></i>
                                                        </button>
                                                    {/if}
                                                    {if in_array($reportRow.status, ['new', 'approved'])}
                                                        <button class="btn btn-danger btn-sm" onclick="updateStatus({$reportRow.id}, 'cancelled', this); return false;" title="Отклонить">
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                    {/if}
                                                </div>
                                            </td>
                                        </tr>
                                    {/foreach}
                                {else}
                                    <tr>
                                        <td colspan="{count($reportHeaders) + 1}" class="text-center text-danger">Данные не найдены</td>
                                    </tr>
                                {/if}
                                </tbody>
                            </table>
                            
                            {* Пагинация *}
                            {if $total_pages_num > 1}
                                <div class="jsgrid-pager-container">
                                    <div class="jsgrid-pager">
                                        {if $current_page_num > 1}
                                            <span class="jsgrid-pager-nav-button">
                                                <a href="{$reportUri}?page=1{$queryString}">Первая</a>
                                            </span>
                                            <span class="jsgrid-pager-nav-button">
                                                <a href="{$reportUri}?page={$current_page_num - 1}{$queryString}">Предыдущая</a>
                                            </span>
                                        {/if}

                                        {section name=pages loop=$total_pages_num}
                                            {$p = $smarty.section.pages.index + 1}
                                            {if ($p == $current_page_num)}
                                                <span class="jsgrid-pager-page jsgrid-pager-current-page">{$p}</span>
                                            {elseif $p >= ($current_page_num - 2) && $p <= ($current_page_num + 2)}
                                                <span class="jsgrid-pager-page">
                                                    <a href="{$reportUri}?page={$p}{$queryString}">{$p}</a>
                                                </span>
                                            {/if}
                                        {/section}

                                        {if $current_page_num < $total_pages_num}
                                            <span class="jsgrid-pager-nav-button">
                                                <a href="{$reportUri}?page={$current_page_num + 1}{$queryString}">Следующая</a>
                                            </span>
                                            <span class="jsgrid-pager-nav-button">
                                                <a href="{$reportUri}?page={$total_pages_num}{$queryString}">Последняя</a>
                                            </span>
                                        {/if}
                                        &nbsp;&nbsp; {$current_page_num} из {$total_pages_num}
                                    </div>
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {include file='footer.tpl'}
</div>

<!-- Modal для выбора причины отклонения -->
<div class="modal fade" id="rejectReasonModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Выберите причину отклонения</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="rejectReason">Причина отклонения:</label>
                    <select class="form-control" id="rejectReason">
                        <option value="">Выберите причину...</option>
                        {foreach from=$rejectReasonOptions key=value item=label}
                            <option value="{$value}">{$label}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-danger" id="confirmRejectBtn" disabled>Отклонить</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for file preview -->
<div class="modal fade" id="fileModal" tabindex="-1" role="dialog" aria-labelledby="fileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Файл</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Контейнер для изображения -->
                <div id="imageContainer" style="display: none; text-align: center;">
                    <img id="previewImage" src="" style="max-width: 100%; max-height: 500px;" />
                </div>
                <!-- iframe для других типов файлов -->
                <iframe id="fileFrame" src="" width="100%" height="500px" frameborder="0" style="display: none;"></iframe>
            </div>
            <div class="modal-footer">
                <a id="downloadLink" href="" class="btn btn-primary" download>Скачать файл</a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
