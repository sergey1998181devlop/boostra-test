{$meta_title='Статистика операторов Vox' scope=parent}

{capture name='page_scripts'}
    <script src="design/manager/assets/plugins/moment/moment.js"></script>
    <script src="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script src="design/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    <script>
        $(function () {
            $('.daterange').daterangepicker({
                autoApply: true,
                locale: { format: 'DD.MM.YYYY' },
                default: ''
            });

            function updateFilters() {
                const paramsObj = {
                    operator: $('select.operator').val() || '',
                    queue: $('select.queue').val() || ''
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
        })

        function download() {
            const params = {
                daterange: $('input[name="daterange"]').val(),
                operator: $('select.operator').val() || '',
                queue: $('select.queue').val() || ''
            };

            const queryString = new URLSearchParams(params).toString();
            const url = '{$reportUri}?action=download&' + queryString;

            $.ajax({
                url: url,
                method: 'GET',
                xhrFields: { responseType: 'blob' },
                success: function (data, textStatus, jqXHR) {
                    const contentType = jqXHR.getResponseHeader('Content-Type');
                    if (contentType && contentType.includes('application/json')) {
                        const response = JSON.parse(data);
                        if (response.status === 'error') {
                            Swal.fire({ icon: 'error', title: 'Ошибка', text: response.message });
                            return;
                        }
                    }

                    const blob = new Blob([data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                    const downloadUrl = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = downloadUrl;
                    a.download = 'vox_calls_report_' + new Date().toISOString().slice(0, 10) + '.xlsx';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(downloadUrl);
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Ошибка', text: 'Произошла ошибка при выполнении запроса' });
                }
            });

            return false;
        }

        let syncInProgress = false;

        function syncData() {
            if (syncInProgress) {
                Swal.fire({ icon: 'info', title: 'Подождите', text: 'Импорт уже выполняется, дождитесь завершения' });
                return false;
            }

            const dateRange = $('input[name="daterange"]').val();
            if (!dateRange) {
                Swal.fire({ icon: 'warning', title: 'Внимание', text: 'Выберите период для импорта' });
                return false;
            }

            Swal.fire({
                icon: 'question',
                title: 'Импорт из Vox',
                text: 'Запустить импорт звонков за период ' + dateRange + '? Это может занять некоторое время.',
                showCancelButton: true,
                confirmButtonText: 'Запустить',
                cancelButtonText: 'Отмена',
            }).then(function (result) {
                if (!result.value) return;

                syncInProgress = true;
                const $btn = $('#syncBtn');
                const originalHtml = $btn.html();
                $btn.prop('disabled', true).html('<i class="ti-reload fa-spin"></i> Импорт...');

                const params = {
                    action: 'sync',
                    daterange: dateRange
                };

                $.ajax({
                    url: '{$reportUri}?' + new URLSearchParams(params).toString(),
                    method: 'GET',
                    success: function (resp) {
                        syncInProgress = false;
                        $btn.prop('disabled', false).html(originalHtml);

                        if (resp && resp.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Импорт завершён',
                                text: resp.message,
                            }).then(function () {
                                location.reload();
                            });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Ошибка', text: (resp && resp.message) || 'Неизвестная ошибка' });
                        }
                    },
                    error: function () {
                        syncInProgress = false;
                        $btn.prop('disabled', false).html(originalHtml);
                        Swal.fire({ icon: 'error', title: 'Ошибка', text: 'Произошла ошибка при выполнении запроса' });
                    }
                });
            });

            return false;
        }

        let currentDetailsParams = {};

        function openDetails(voxUserId, assessment, title) {
            const params = {
                action: 'details',
                daterange: $('input[name="daterange"]').val(),
                vox_user_id: voxUserId,
            };

            if (assessment !== null && assessment !== undefined && assessment !== '') {
                params.assessment = assessment;
            }
            currentDetailsParams = params;

            $('#detailsModalLabel').text(title || 'Детализация');
            $('#detailsModalBody').html('<div class="text-center">Загрузка...</div>');
            $('#downloadDetailsBtn').hide();
            $('#detailsModal').modal('show');

            $.ajax({
                url: '{$reportUri}?' + new URLSearchParams(params).toString(),
                method: 'GET',
                success: function (resp) {
                    if (!resp || resp.status !== 'success') {
                        $('#detailsModalBody').html('<div class="text-danger">Ошибка загрузки данных</div>');
                        return;
                    }

                    const items = resp.items || [];
                    let html = '';
                    html += '<div class="table-responsive">';
                    html += '<table class="table table-bordered table-hover">';
                    html += '<thead><tr>';
                    html += '<th>Дата и время</th>';
                    html += '<th>Номер телефона</th>';
                    html += '<th>Очередь</th>';
                    html += '<th>ФИО оператора</th>';
                    html += '<th>Тэг</th>';
                    html += '<th>Оценка</th>';
                    html += '<th>Аудио</th>';
                    html += '</tr></thead><tbody>';

                    if (!items.length) {
                        html += '<tr><td colspan="6" class="text-center text-danger">Данные не найдены</td></tr>';
                    } else {
                        for (const it of items) {
                            const record = (it.record_url || '').toString();
                            const audioCell = record ? ('<audio controls src="' + $('<div/>').text(record).html() + '"></audio>') : '';
                            const phoneCell = it.user_id ? ('<a href="client/' + it.user_id + '">' + it.phone + '</a>') : $('<div/>').text(it.phone || '').html();

                            html += '<tr>';
                            html += '<td>' + $('<div/>').text(it.datetime || '').html() + '</td>';
                            html += '<td>' + phoneCell + '</td>';
                            html += '<td>' + $('<div/>').text(it.queue || '').html() + '</td>';
                            html += '<td>' + $('<div/>').text(it.operator || '').html() + '</td>';
                            html += '<td>' + $('<div/>').text(it.tags || '').html() + '</td>';
                            html += '<td>' + $('<div/>').text(it.assessment || '').html() + '</td>';
                            html += '<td>' + audioCell + '</td>';
                            html += '</tr>';
                        }
                    }

                    html += '</tbody></table></div>';

                    if (resp.limit && items.length >= resp.limit) {
                        html += '<div class="text-warning">Показаны первые ' + resp.limit + ' записей. Уточните фильтры.</div>';
                    }

                    $('#detailsModalBody').html(html);
                    $('#downloadDetailsBtn').show();
                },
                error: function () {
                    $('#detailsModalBody').html('<div class="text-danger">Произошла ошибка при выполнении запроса</div>');
                }
            });

            return false;
        }

        function downloadDetails() {
            if (!currentDetailsParams) {
                return false;
            }

            const params = Object.assign({}, currentDetailsParams, {
                download_details: 1
            });
            window.location.href = '{$reportUri}?' + new URLSearchParams(params).toString();
            return false;
        }
    </script>
{/capture}

{capture name='page_styles'}
    <link href="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css"/>
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

    .limited-text {
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 5;
        -webkit-box-orient: vertical;
        cursor: pointer;
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
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{$meta_title} за период {if $date_from}{$date_from} - {$date_to}{/if}</h4>

                        <form>
                            <div class="row">
                                <div class="col-12 col-md-4">
                                    <div class="input-group mb-3">
                                        <input type="text" name="daterange" class="form-control daterange" value="{if $date_from && $date_to}{$date_from} - {$date_to}{/if}">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><span class="ti-calendar"></span></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <button type="submit" class="btn btn-info">Отфильтровать</button>
                                    <button onclick="return download();" type="button" class="btn btn-success">
                                        <i class="ti-save"></i> Выгрузить
                                    </button>
                                    <button onclick="return syncData();" type="button" class="btn btn-primary" id="syncBtn">
                                        <i class="ti-reload"></i> Импорт Vox
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
                                                {if isset($filter.option_value_field)}
                                                    <option selected value="">Все</option>
                                                    {foreach from=$filter.options item=option}
                                                        {assign var="optionValue" value=$option[$filter.option_value_field]}
                                                        {assign var="optionLabel" value=$option[$filter.option_label_field]}
                                                        {if $optionValue && $optionLabel}
                                                            <option value="{$optionValue}" {if $filter.value == $optionValue}selected{/if}>{$optionLabel}</option>
                                                        {/if}
                                                    {/foreach}
                                                {else}
                                                    {foreach from=$filter.options key=key item=value}
                                                        <option value="{$key}" {if $filter.value == $key}selected{/if}>{$value}</option>
                                                    {/foreach}
                                                {/if}
                                            </select>
                                        {elseif $filter.type == 'text'}
                                            <input id="{$filter.name}" name="{$filter.name}" value="{$filter.value}" class="form-control form-control-sm filter {$filter.name}" placeholder="{$filter.placeholder}">
                                        {/if}
                                    </div>
                                {/foreach}
                            </div>
                        </form>

                        {include file='html_blocks/pagination.tpl'}

                        <div id="result" class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="position-sticky">
                                <tr>
                                    {assign var="queryString" value=""}
                                    {foreach from=$smarty.get key=key item=value}
                                        {if $key neq 'sort'}
                                            {assign var="queryString" value=$queryString|cat:"&"|cat:$key|cat:"="|cat:$value}
                                        {/if}
                                    {/foreach}

                                    {foreach from=$reportHeaders item=header}
                                        <th>
                                            {if isset($header.sort_key)}
                                                {if $smarty.get.sort == "{$header.sort_key}_asc"}
                                                    {assign var="newSort" value="{$header.sort_key}_desc"}
                                                    {assign var="sortIcon" value='<i class="ti-arrow-up"></i>'}
                                                {elseif $smarty.get.sort == "{$header.sort_key}_desc"}
                                                    {assign var="newSort" value=""}
                                                    {assign var="sortIcon" value='<i class="ti-arrow-down"></i>'}
                                                {else}
                                                    {assign var="newSort" value="{$header.sort_key}_asc"}
                                                    {assign var="sortIcon" value='' }
                                                {/if}
                                                <a href="{$reportUri}?sort={$newSort}{$queryString}">{$header.label} {$sortIcon}</a>
                                            {else}
                                                {$header.label}
                                            {/if}
                                        </th>
                                    {/foreach}
                                </tr>
                                </thead>
                                <tbody>
                                {if $reportRows|@count}
                                    {foreach from=$reportRows item=row}
                                        <tr>
                                            {foreach from=$reportHeaders item=header}
                                                {if in_array($header.key, ['assessment_1','assessment_2','assessment_3','assessment_4','assessment_5'])}
                                                    <td>
                                                        {assign var="r" value=$header.key|replace:'assessment_':''}
                                                        {if $row[$header.key] > 0}
                                                            <a href="#" onclick="return openDetails('{$row.vox_user_id|escape}', '{$r|escape}', 'Детализация: {$row.operator|escape} / {$header.label|escape}');">
                                                                {$row[$header.key]|escape}
                                                            </a>
                                                        {else}
                                                            {$row[$header.key]|escape}
                                                        {/if}
                                                    </td>
                                                {elseif $header.key == 'total_rated'}
                                                    <td>
                                                        {if $row[$header.key] > 0}
                                                            <a href="#" onclick="return openDetails('{$row.vox_user_id|escape}', 'rated', 'Детализация: {$row.operator|escape} / Всего оценок');">
                                                                {$row[$header.key]|escape}
                                                            </a>
                                                        {else}
                                                            {$row[$header.key]|escape}
                                                        {/if}
                                                    </td>
                                                {elseif $header.key == 'total'}
                                                    <td>
                                                        {if $row[$header.key] > 0}
                                                            <a href="#" onclick="return openDetails('{$row.vox_user_id|escape}', '', 'Детализация: {$row.operator|escape} / Все звонки');">
                                                                {$row[$header.key]|escape}
                                                            </a>
                                                        {else}
                                                            {$row[$header.key]|escape}
                                                        {/if}
                                                    </td>
                                                {else}
                                                    <td>{$row[$header.key]|escape}</td>
                                                {/if}
                                            {/foreach}
                                        </tr>
                                    {/foreach}
                                {else}
                                    <tr>
                                        <td colspan="{$reportHeaders|@count}" class="text-danger text-center">Данные не найдены</td>
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

<div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 80%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">Детализация</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detailsModalBody" style="max-height: 85vh; overflow-y: auto;">
            </div>
            <div class="modal-footer">
                <button type="button" id="downloadDetailsBtn" class="btn btn-success" onclick="return downloadDetails();" style="display:none;">
                    <i class="ti-save"></i> Выгрузить
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
