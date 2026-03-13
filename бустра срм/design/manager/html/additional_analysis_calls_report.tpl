{$meta_title='Отчёт по анализу звонков ИИ c доп. услугами' scope=parent}

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
        })

        function download() {
            const params = {
                daterange: $('input[name="daterange"]').val(),
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
                    a.download = 'additional_analysis_calls_report_' + new Date().toISOString().slice(0, 10) + '.xlsx';
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

        function openContentModal(element, columnTitle) {
            document.getElementById('contentModalLabel').innerText = columnTitle;
            document.getElementById('contentModalBody').innerText = element.textContent || element.innerText;
            $('#contentModal').modal('show');
        }
    </script>
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

                                    <button onclick="return window.open('{$reportUri}','_self');"
                                            type="button" class="btn btn-warning">
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
                                                {if $filter.name != 'type_report'}
                                                    <option selected value="">Все</option>
                                                {/if}
                                                {if isset($filter.option_value_field)}
                                                    {foreach from=$filter.options item=option}
                                                        {assign var="optionValue" value=$option[$filter.option_value_field]}
                                                        {assign var="optionLabel" value=$option[$filter.option_label_field]}
                                                        {if $optionValue && $optionLabel}
                                                            <option value="{$optionValue}" {if $filter.value == $optionValue}selected{/if}>
                                                                {$optionLabel}
                                                            </option>
                                                        {/if}
                                                    {/foreach}
                                                {else}
                                                    {foreach from=$filter.options key=key item=value}
                                                        <option value="{$key}" {if $filter.value == $key}selected{/if}>
                                                            {$value}
                                                        </option>
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
                                                    {assign var="sortIcon" value=''}
                                                {/if}
                                                <a href="{$reportUri}?sort={$newSort}{$queryString}">
                                                    {$header.label} {$sortIcon}
                                                </a>
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
                                                {if $header.key == 'record'}
                                                    <td>
                                                        <audio controls src="{$row[$header.key]|escape}"></audio>
                                                    </td>
                                                {else}
                                                    <td>
                                                        <div class="limited-text" onclick="openContentModal(this, '{$header.label}')"
                                                            title="Кликните для отображения полного текста">
                                                            {$row[$header.key]|escape}
                                                        </div>
                                                    </td>
                                                {/if}
                                            {/foreach}
                                        </tr>
                                    {/foreach}
                                {else}
                                    <tr>
                                        <td colspan="{$reportHeaders|@count}" class="text-danger text-center">
                                            Данные не найдены
                                        </td>
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

<!-- Модальное окно -->
<div class="modal fade" id="contentModal" tabindex="-1" role="dialog" aria-labelledby="contentModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contentModalLabel">Содержимое</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="contentModalBody" style="max-height: 50vh; overflow-y: auto;color: #fff;">
                <!-- Здесь будет полный текст -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
