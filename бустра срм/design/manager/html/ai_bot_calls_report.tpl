{$meta_title='Отчёт по звонкам ИИ' scope=parent}

{capture name='page_scripts'}
    <script src="design/manager/assets/plugins/moment/moment.js"></script>
    <script src="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script src="design/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    <script>
        function showFullTranscript(transcript) {
            document.getElementById('transcriptModalBody').innerHTML = transcript.replace(/\n/g, '<br>');
            $('#transcriptModal').modal('show');
        }

        function download() {
            const params = {
                action: 'download',
                daterange: $('input[name="daterange"]').val(),
                phone_mobile: $('#phone_mobile').val() || '',
                client_fio: $('#client_fio').val() || '',
                tag: $('#tag').val() || '',
                bot_action: $('#bot_action').val() || '',
                transferred_to_operator: $('#transferred_to_operator').val() || '',
            };

            const queryString = new URLSearchParams(params).toString();
            window.location.href = '{$reportUri}?' + queryString;
        }

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
                    phone_mobile: $('#phone_mobile').val() || '',
                    client_fio: $('#client_fio').val() || '',
                    tag: $('#tag').val() || '',
                    bot_action: $('#bot_action').val() || '',
                    transferred_to_operator: $('#transferred_to_operator').val() || '',
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
            $('#phone_mobile, #client_fio').on('keyup', function() {
                clearTimeout(delayTimer);
                delayTimer = setTimeout(() => {
                    updateFilters();
                }, 500);
            });
        });
    </script>

{/capture}

{capture name='page_styles'}
    <link href="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet">
    <link href="design/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">
{/capture}

<style>
    .table-responsive {
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
                        <h4 class="card-title">{$meta_title} за период {if $date_from}{$date_from} - {$date_to}{/if}</h4>

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
                                        {if $key neq 'sort' && $value neq ''}
                                            {assign var="queryString" value=$queryString|cat:"&"|cat:$key|cat:"="|cat:$value}
                                        {/if}
                                    {/foreach}

                                    <th>
                                        {if $smarty.get.sort == "date_time_asc"}
                                            {assign var="newSort" value="date_time_desc"}
                                            {assign var="sortIcon" value='<i class="ti-arrow-up"></i>'}
                                        {elseif $smarty.get.sort == "date_time_desc"}
                                            {assign var="newSort" value=""}
                                            {assign var="sortIcon" value='<i class="ti-arrow-down"></i>'}
                                        {else}
                                            {assign var="newSort" value="date_time_asc"}
                                            {assign var="sortIcon" value=''}
                                        {/if}
                                        <a href="{$reportUri}?sort={$newSort}{if $queryString}{$queryString}{/if}">
                                            Дата/Время {$sortIcon}
                                        </a>
                                    </th>
                                    <th>
                                        {if $smarty.get.sort == "phone_mobile_asc"}
                                            {assign var="newSort" value="phone_mobile_desc"}
                                            {assign var="sortIcon" value='<i class="ti-arrow-up"></i>'}
                                        {elseif $smarty.get.sort == "phone_mobile_desc"}
                                            {assign var="newSort" value=""}
                                            {assign var="sortIcon" value='<i class="ti-arrow-down"></i>'}
                                        {else}
                                            {assign var="newSort" value="phone_mobile_asc"}
                                            {assign var="sortIcon" value=''}
                                        {/if}
                                        <a href="{$reportUri}?sort={$newSort}{if $queryString}{$queryString}{/if}">
                                            Номер телефона {$sortIcon}
                                        </a>
                                    </th>
                                    <th>
                                        {if $smarty.get.sort == "duration_asc"}
                                            {assign var="newSort" value="duration_desc"}
                                            {assign var="sortIcon" value='<i class="ti-arrow-up"></i>'}
                                        {elseif $smarty.get.sort == "duration_desc"}
                                            {assign var="newSort" value=""}
                                            {assign var="sortIcon" value='<i class="ti-arrow-down"></i>'}
                                        {else}
                                            {assign var="newSort" value="duration_asc"}
                                            {assign var="sortIcon" value=''}
                                        {/if}
                                        <a href="{$reportUri}?sort={$newSort}{if $queryString}{$queryString}{/if}">
                                            Длительность {$sortIcon}
                                        </a>
                                    </th>
                                    <th>
                                        {if $smarty.get.sort == "client_fio_asc"}
                                            {assign var="newSort" value="client_fio_desc"}
                                            {assign var="sortIcon" value='<i class="ti-arrow-up"></i>'}
                                        {elseif $smarty.get.sort == "client_fio_desc"}
                                            {assign var="newSort" value=""}
                                            {assign var="sortIcon" value='<i class="ti-arrow-down"></i>'}
                                        {else}
                                            {assign var="newSort" value="client_fio_asc"}
                                            {assign var="sortIcon" value=''}
                                        {/if}
                                        <a href="{$reportUri}?sort={$newSort}{if $queryString}{$queryString}{/if}">
                                            ФИО клиента {$sortIcon}
                                        </a>
                                    </th>
                                    <th>
                                        {if $smarty.get.sort == "tag_asc"}
                                            {assign var="newSort" value="tag_desc"}
                                            {assign var="sortIcon" value='<i class="ti-arrow-up"></i>'}
                                        {elseif $smarty.get.sort == "tag_desc"}
                                            {assign var="newSort" value=""}
                                            {assign var="sortIcon" value='<i class="ti-arrow-down"></i>'}
                                        {else}
                                            {assign var="newSort" value="tag_asc"}
                                            {assign var="sortIcon" value=''}
                                        {/if}
                                        <a href="{$reportUri}?sort={$newSort}{if $queryString}{$queryString}{/if}">
                                            Тег {$sortIcon}
                                        </a>
                                    </th>
                                    <th>Транскрипция звонка</th>
                                    <th>Запись звонка</th>
                                    <th>Действия</th>
                                    <th>
                                        {if $smarty.get.sort == "assessment_asc"}
                                            {assign var="newSort" value="assessment_desc"}
                                            {assign var="sortIcon" value='<i class="ti-arrow-up"></i>'}
                                        {elseif $smarty.get.sort == "assessment_desc"}
                                            {assign var="newSort" value=""}
                                            {assign var="sortIcon" value='<i class="ti-arrow-down"></i>'}
                                        {else}
                                            {assign var="newSort" value="assessment_asc"}
                                            {assign var="sortIcon" value=''}
                                        {/if}
                                        <a href="{$reportUri}?sort={$newSort}{if $queryString}{$queryString}{/if}">
                                            Оценка клиента {$sortIcon}
                                        </a>
                                    </th>
                                    <th>
                                        {if $smarty.get.sort == "transferred_to_operator_asc"}
                                            {assign var="newSort" value="transferred_to_operator_desc"}
                                            {assign var="sortIcon" value='<i class="ti-arrow-up"></i>'}
                                        {elseif $smarty.get.sort == "transferred_to_operator_desc"}
                                            {assign var="newSort" value=""}
                                            {assign var="sortIcon" value='<i class="ti-arrow-down"></i>'}
                                        {else}
                                            {assign var="newSort" value="transferred_to_operator_asc"}
                                            {assign var="sortIcon" value=''}
                                        {/if}
                                        <a href="{$reportUri}?sort={$newSort}{if $queryString}{$queryString}{/if}">
                                            Перевод на оператора {$sortIcon}
                                        </a>
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                {if $items}
                                    {foreach $items as $item}
                                        <tr>
                                            <td>{$item.date_time}</td>
                                            <td>{$item.phone_mobile}</td>
                                            <td>{$item.duration}</td>
                                            <td>
                                                {if $item.client_id}
                                                    <a href="/client/{$item.client_id}">{$item.client_fio}</a>
                                                {else}
                                                    {$item.client_fio}
                                                {/if}
                                            </td>
                                            <td>{$item.tag}</td>
                                            <td>
                                                <div class="limited-text" onclick="showFullTranscript('{$item.full_transcript|escape:'html'}')" 
                                                     title="Кликните для просмотра полной транскрипции">
                                                    {$item.transcript}
                                                </div>
                                            </td>
                                            <td>
                                                {if $item.call_record}
                                                    <audio controls style="width: 200px; height: 30px;">
                                                        <source src="{$item.call_record}" type="audio/mpeg">
                                                        Ваш браузер не поддерживает воспроизведение аудио.
                                                    </audio>
                                                {else}
                                                    <span class="text-muted">Нет записи</span>
                                                {/if}
                                            </td>
                                            <td>{$item.actions}</td>
                                            <td>{$item.assessment}</td>
                                            <td>{$item.transferred_to_operator}</td>
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

<div class="modal fade" id="transcriptModal" tabindex="-1" role="dialog" aria-labelledby="transcriptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transcriptModalLabel">Полная транскрипция звонка</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="transcriptModalBody" style="max-height: 50vh; overflow-y: auto;">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

