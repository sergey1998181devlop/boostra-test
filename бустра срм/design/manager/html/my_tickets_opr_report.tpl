{$meta_title='Отчёт обращений клиентов для ОПР' scope=parent}

{capture name='page_scripts'}
    <script src="design/manager/assets/plugins/moment/moment.js"></script>
    <script src="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <!-- Date range Plugin JavaScript -->
    <script src="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script src="design/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    <script>
        // функция для экспорта
        function download() {
            const params = {
                ticket_id: $('input.ticket_id').val() || '',
                created_at: $('input.created_at').val() || '',
                closed_at: $('input.closed_at').val() || '',
                company: $('select.company').val() || '',
                client: $('select.client').val() || '',
                subject: $('select.subject').val() || '',
                parent_subject: $('select.parent_subject').val() || '',
                status: $('select.status').val() || '',
                priority: $('select.priority').val() || '',
                manager: $('select.manager').val() || '',
                initiator: $('select.initiator').val() || '',
                channel: $('select.channel').val() || '',
            };

            // Добавляем данные о выбранном шаблоне, если он применен
            const selectedTemplateId = $('#template-select').val();
            if (selectedTemplateId) {
                params.template_id = selectedTemplateId;
                
                // Получаем данные шаблона из глобальной переменной или из DOM
                const templateData = window.currentAppliedTemplate;
                if (templateData && templateData.fields) {
                    params.fields = templateData.fields;
                }
            }

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
                    
                    // Формируем имя файла с учетом шаблона
                    let filename = 'my_tickets_report';
                    if (selectedTemplateId) {
                        const templateName = $('#template-select option:selected').text();
                        if (templateName && templateName !== 'Выберите шаблон') {
                            const cleanName = templateName
                                .replace(/\s+/g, '_')
                                .replace(/[^a-zA-Z0-9а-яА-Я_-]/g, '')
                                .replace(/_+/g, '_')
                                .replace(/^_|_$/g, '');
                            
                            if (cleanName) {
                                filename += '_' + cleanName;
                            }
                        }
                    }
                    filename += '_' + new Date().toISOString().slice(0, 10) + '.xlsx';
                    
                    a.download = filename;
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
        }

        $(function () {
            $('.datepicker').datepicker({
                format: 'dd.mm.yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'ru'
            });

            // Инициализация daterangepicker для основных фильтров
            $('.daterange-filter').daterangepicker({
                autoApply: true,
                autoUpdateInput: false,
                locale: {
                    format: 'DD.MM.YYYY',
                    separator: ' - ',
                    applyLabel: 'Применить',
                    cancelLabel: 'Отмена',
                    fromLabel: 'От',
                    toLabel: 'До',
                    customRangeLabel: 'Выбрать период',
                    weekLabel: 'Н',
                    daysOfWeek: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
                    monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
                        'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
                },
                opens: 'left'
            });

            $('.daterange-filter').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD.MM.YYYY') + ' - ' + picker.endDate.format('DD.MM.YYYY'));
                updateFilters();
            });

            $('.daterange-filter').on('cancel.daterangepicker', function() {
                $(this).val('');
                updateFilters();
            });

            function updateFilters() {
                const paramsObj = {
                    ticket_id: $('input.ticket_id').val() || '',
                    created_at: $('input.created_at').val() || '',
                    closed_at: $('input.closed_at').val() || '',
                    company: $('select.company').val() || '',
                    client: $('select.client').val() || '',
                    subject: $('select.subject').val() || '',
                    parent_subject: $('select.parent_subject').val() || '',
                    status: $('select.status').val() || '',
                    priority: $('select.priority').val() || '',
                    manager: $('select.manager').val() || '',
                    initiator: $('select.initiator').val() || '',
                    channel: $('select.channel').val() || '',
                };

                // Добавляем ID примененного шаблона если есть
                if (window.currentAppliedTemplateId) {
                    paramsObj.template_id = window.currentAppliedTemplateId;
                }

                const params = new URLSearchParams(paramsObj).toString();
                window.open('{$reportUri}?' + params, '_self');
            }

            $('select.filter').on('change', () => {
                updateFilters();
            });

            let delayTimer;
            $('input.ticket_id').on('keyup', function() {
                clearTimeout(delayTimer);
                delayTimer = setTimeout(() => {
                    updateFilters();
                }, 500);
            });

            $('input.datepicker').on('changeDate', function() {
                updateFilters();
            });

            function openContentModal(element, columnTitle) {
                document.getElementById('contentModalLabel').innerText = columnTitle;
                document.getElementById('contentModalBody').innerText = element.textContent || element.innerText;
                $('#contentModal').modal('show');
            }

            // === ШАБЛОНЫ ===
            
            // Глобальная переменная для хранения данных примененного шаблона
            window.currentAppliedTemplate = null;
            window.currentAppliedTemplateId = null;
            
            // Восстанавливаем выбранный шаблон при загрузке страницы
            {if $template_id}
                window.currentAppliedTemplateId = {$template_id};
                $('#template-select').val({$template_id});
                updateTemplateButtons();
            {/if}
            
            let currentTemplateId = null;
            let isEditMode = false;

            // Управление состоянием кнопок
            function updateTemplateButtons() {
                const selectedTemplate = $('#template-select').val();
                const hasSelection = selectedTemplate !== '';
                
                $('#edit-template-btn, #delete-template-btn, #apply-template-btn').prop('disabled', !hasSelection);
            }

            // Обработчик изменения выбора шаблона
            $('#template-select').on('change', function() {
                updateTemplateButtons();
                
                // Очищаем данные примененного шаблона при изменении выбора
                if (!$(this).val()) {
                    window.currentAppliedTemplate = null;
                    window.currentAppliedTemplateId = null;
                }
            });

            // Создать новый шаблон
            $('#create-template-btn').on('click', function() {
                isEditMode = false;
                currentTemplateId = null;
                
                $('#templateModalLabel').text('Создать шаблон');
                $('#template-id').val('');
                $('#templateForm')[0].reset();
                
                // Заполняем текущими значениями фильтров
                fillTemplateFormWithCurrentFilters();
                
                $('#templateModal').modal('show');
            });

            // Редактировать шаблон
            $('#edit-template-btn').on('click', function() {
                const templateId = $('#template-select').val();
                if (!templateId) return;
                
                isEditMode = true;
                currentTemplateId = templateId;
                
                $('#templateModalLabel').text('Редактировать шаблон');
                
                // Загружаем данные шаблона
                $.get(window.location.pathname, {
                    action: 'getTemplate',
                    id: templateId
                })
                .done(function(response) {
                    if (response.success) {
                        fillTemplateForm(response.template);
                        $('#templateModal').modal('show');
                    } else {
                        showNotification('error', response.message || 'Ошибка загрузки шаблона');
                    }
                })
                .fail(function() {
                    showNotification('error', 'Ошибка при загрузке данных шаблона');
                });
            });

            // Удалить шаблон
            $('#delete-template-btn').on('click', function() {
                const templateId = $('#template-select').val();
                if (!templateId) return;
                
                const templateName = $('#template-select option:selected').text();
                
                if (confirm('Вы уверены, что хотите удалить шаблон "' + templateName + '"?')) {
                    $.post(window.location.pathname, {
                        action: 'deleteTemplate',
                        id: templateId
                    })
                    .done(function(response) {
                        if (response.success) {
                            showNotification('success', response.message);
                            $('#template-select option[value="' + templateId + '"]').remove();
                            $('#template-select').val('').trigger('change');
                        } else {
                            showNotification('error', response.message || 'Ошибка удаления шаблона');
                        }
                    })
                    .fail(function() {
                        showNotification('error', 'Ошибка при удалении шаблона');
                    });
                }
            });

            // Применить шаблон
            $('#apply-template-btn').on('click', function() {
                const templateId = $('#template-select').val();
                if (!templateId) return;
                
                $.get(window.location.pathname, {
                    action: 'getTemplate',
                    id: templateId
                })
                .done(function(response) {
                    if (response.success) {
                        // Сохраняем ID примененного шаблона
                        window.currentAppliedTemplateId = templateId;
                        applyTemplateToFilters(response.template.data);
                    } else {
                        showNotification('error', response.message || 'Ошибка применения шаблона');
                    }
                })
                .fail(function() {
                    showNotification('error', 'Ошибка при применении шаблона');
                });
            });

            // Сохранить шаблон
            $('#save-template-btn').on('click', function() {
                const formData = collectTemplateFormData();
                
                if (!formData.name.trim()) {
                    showNotification('error', 'Введите название шаблона');
                    return;
                }
                
                const action = isEditMode ? 'updateTemplate' : 'createTemplate';
                const postData = {
                    action: action,
                    name: formData.name,
                    data: formData.data
                };
                
                if (isEditMode) {
                    postData.id = currentTemplateId;
                }
                
                $.post(window.location.pathname, postData)
                .done(function(response) {
                    if (response.success) {
                        showNotification('success', response.message);
                        $('#templateModal').modal('hide');
                        
                        if (!isEditMode) {
                            // Добавляем новый шаблон в список
                            const option = $('<option></option>')
                                .attr('value', response.template_id)
                                .text(formData.name);
                            $('#template-select').append(option);
                        } else {
                            // Обновляем название в списке
                            $('#template-select option[value="' + currentTemplateId + '"]').text(formData.name);
                        }
                    } else {
                        showNotification('error', response.message || 'Ошибка сохранения шаблона');
                    }
                })
                .fail(function() {
                    showNotification('error', 'Ошибка при сохранении шаблона');
                });
            });

            // Заполнить форму шаблона текущими фильтрами
            function fillTemplateFormWithCurrentFilters() {
                $('#template-created-at').val($('input.created_at').val() || '');
                $('#template-closed-at').val($('input.closed_at').val() || '');
                $('#template-company').val($('select.company').val() || '');
                $('#template-client').val($('select.client').val() || '');
                $('#template-subject').val($('select.subject').val() || '');
                $('#template-parent_subject').val($('select.parent_subject').val() || '');
                $('#template-status').val($('select.status').val() || '');
                $('#template-priority').val($('select.priority').val() || '');
                $('#template-manager').val($('select.manager').val() || '');
                $('#template-initiator').val($('select.initiator').val() || '');
                $('#template-channel').val($('select.channel').val() || '');
                
                // Отмечаем все поля для экспорта по умолчанию
                $('input[name="fields[]"]').prop('checked', true);
            }

            // Заполнить форму данными шаблона
            function fillTemplateForm(template) {
                $('#template-id').val(template.id);
                $('#template-name').val(template.name);
                
                const data = template.data;
                if (data.filters) {
                    $('#template-created-at').val(data.filters.created_at || '');
                    $('#template-closed-at').val(data.filters.closed_at || '');
                    $('#template-company').val(data.filters.company || '');
                    $('#template-client').val(data.filters.client || '');
                    $('#template-subject').val(data.filters.subject || '');
                    $('#template-parent_subject').val(data.filters.parent_subject || '');
                    $('#template-status').val(data.filters.status || '');
                    $('#template-priority').val(data.filters.priority || '');
                    $('#template-manager').val(data.filters.manager || '');
                    $('#template-initiator').val(data.filters.initiator || '');
                    $('#template-channel').val(data.filters.channel || '');
                }
                
                // Отмечаем поля для экспорта
                $('input[name="fields[]"]').prop('checked', false);
                if (data.fields && Array.isArray(data.fields)) {
                    data.fields.forEach(function(field) {
                        $('input[name="fields[]"][value="' + field + '"]').prop('checked', true);
                    });
                }
            }

            // Применить шаблон к фильтрам
            function applyTemplateToFilters(data) {
                // Сохраняем данные примененного шаблона в глобальной переменной для экспорта
                window.currentAppliedTemplate = data;
                
                if (data.filters) {
                    $('input.created_at').val(data.filters.created_at || '');
                    $('input.closed_at').val(data.filters.closed_at || '');
                    $('select.company').val(data.filters.company || '');
                    $('select.client').val(data.filters.client || '');
                    $('select.subject').val(data.filters.subject || '');
                    $('select.parent_subject').val(data.filters.parent_subject || '');
                    $('select.status').val(data.filters.status || '');
                    $('select.priority').val(data.filters.priority || '');
                    $('select.manager').val(data.filters.manager || '');
                    $('select.initiator').val(data.filters.initiator || '');
                    $('select.channel').val(data.filters.channel || '');
                    
                    // Применяем фильтры
                    updateFilters();
                }
            }

            // Собрать данные формы шаблона
            function collectTemplateFormData() {
                const filters = {
                    created_at: $('#template-created-at').val(),
                    closed_at: $('#template-closed-at').val(),
                    company: $('#template-company').val(),
                    client: $('#template-client').val(),
                    subject: $('#template-subject').val(),
                    parent_subject: $('#template-parent_subject').val(),
                    status: $('#template-status').val(),
                    priority: $('#template-priority').val(),
                    manager: $('#template-manager').val(),
                    initiator: $('#template-initiator').val(),
                    channel: $('#template-channel').val()
                };
                
                const fields = [];
                $('input[name="fields[]"]:checked').each(function() {
                    fields.push($(this).val());
                });
                
                return {
                    name: $('#template-name').val(),
                    data: {
                        filters: filters,
                        fields: fields
                    }
                };
            }

            // Показать уведомление
            function showNotification(type, message) {
                // Используем существующую систему уведомлений или создаем простую
                if (typeof toastr !== 'undefined') {
                    toastr[type](message);
                } else {
                    alert(message);
                }
            }

            // Инициализация при загрузке страницы
            $(document).ready(function() {
                updateTemplateButtons();
            });
        })

        $('#templateModal').on('shown.bs.modal', function() {
            $('.daterange-filter').daterangepicker({
                autoApply: true,
                autoUpdateInput: false,
                locale: {
                    format: 'DD.MM.YYYY',
                    separator: ' - ',
                    applyLabel: 'Применить',
                    cancelLabel: 'Отмена',
                    fromLabel: 'От',
                    toLabel: 'До',
                    customRangeLabel: 'Выбрать период',
                    weekLabel: 'Н',
                    daysOfWeek: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
                    monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
                        'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
                },
                opens: 'left'
            });
            
            $('.daterange-filter').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD.MM.YYYY') + ' - ' + picker.endDate.format('DD.MM.YYYY'));
            });
            
            $('.daterange-filter').on('cancel.daterangepicker', function() {
                $(this).val('');
            });
        });
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
                        <!-- Блок управления шаблонами -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card border-info">
                                    <div class="card-header d-flex justify-content-between align-items-center bg-info text-white">
                                        <h5 class="mb-0"><i class="mdi mdi-content-save-settings"></i> Шаблоны отчета</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <div class="form-group mb-0">
                                                    <label for="template-select">Выберите шаблон:</label>
                                                    <select id="template-select" class="form-control">
                                                        <option value="">-- Выберите шаблон --</option>
                                                        {foreach $templates as $template}
                                                            <option value="{$template->id}">
                                                                {$template->name}
                                                            </option>
                                                        {/foreach}
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label>&nbsp;</label>
                                                <div class="btn-group d-block" role="group">
                                                    <button type="button" class="btn btn-success" id="create-template-btn">
                                                        <i class="mdi mdi-plus"></i> Создать
                                                    </button>
                                                    <button type="button" class="btn btn-primary" id="edit-template-btn" disabled>
                                                        <i class="mdi mdi-pencil"></i> Изменить
                                                    </button>
                                                    <button type="button" class="btn btn-danger" id="delete-template-btn" disabled>
                                                        <i class="mdi mdi-delete"></i> Удалить
                                                    </button>
                                                    <button type="button" class="btn btn-info" id="apply-template-btn" disabled>
                                                        <i class="mdi mdi-check"></i> Применить
                                                    </button>
                                                    <button onclick="return download();" type="button" class="btn btn-success">
                                                        <i class="ti-save"></i> Выгрузить
                                                    </button>
                                                    <button onclick="return window.open('{$reportUri}','_self');" type="button" class="btn btn-warning">
                                                        <i class="ti-reload"></i> Сбросить
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2 mb-4">
                            {foreach from=$filterConfigurations item=filter}
                                <div class="col-12 col-md-2 py-1">
                                    <label for="{$filter.name}">{$filter.label}</label>
                                    {if $filter.type == 'select'}
                                        <select id="{$filter.name}" name="{$filter.name}" class="form-control form-control-sm filter {$filter.name}">
                                            <option selected value="">Все</option>
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
                                    {elseif $filter.type == 'date'}
                                        <input id="{$filter.name}" name="{$filter.name}" value="{$filter.value}" class="form-control form-control-sm filter {$filter.name} datepicker" placeholder="{$filter.placeholder}" autocomplete="off">
                                    {elseif $filter.type == 'daterange'}
                                        <input id="{$filter.name}" name="{$filter.name}" value="{$filter.value}" class="form-control form-control-sm filter {$filter.name} daterange-filter" placeholder="{$filter.placeholder}" autocomplete="off">
                                    {/if}
                                </div>
                            {/foreach}
                        </div>

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
                                                {if $header.key == 'client_name'}
                                                    <td>
                                                        {if $can_see_client_url}
                                                            <a href="/client/{$row.client_id|escape}">{$row.client_name|escape}</a>
                                                        {else}
                                                            {$row.client_name|escape}
                                                        {/if}
                                                    </td>
                                                {elseif $header.key == 'order_id'}
                                                    <td>
                                                        {if $can_see_client_url}
                                                            <a href="/order/{$row.order_id|escape}">{$row.order_id|escape}</a>
                                                        {else}
                                                            {$row.order_id|escape}
                                                        {/if}
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

<!-- Модальное окно для создания/редактирования шаблона -->
<div class="modal fade" id="templateModal" tabindex="-1" role="dialog" aria-labelledby="templateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="templateModalLabel">Создать шаблон</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="templateForm">
                    <input type="hidden" id="template-id" name="id" value="">
                    
                    <!-- Основные настройки -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="template-name">Название шаблона <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="template-name" name="name" required>
                            </div>
                        </div>
                    </div>

                    <!-- Фильтры -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="mdi mdi-filter"></i> Настройки фильтров</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="template-created-at">Дата создания</label>
                                        <input type="text" class="form-control daterange-filter" id="template-created-at" name="filters[created_at]">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="template-closed-at">Дата закрытия</label>
                                        <input type="text" class="form-control daterange-filter" id="template-closed-at" name="filters[closed_at]">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="template-company">Компания</label>
                                        <select class="form-control" id="template-company" name="filters[company]">
                                            <option value="">Все компании</option>
                                            {foreach $templateFilterConfigurations.company.options as $option}
                                                <option value="{$option.id}">{$option.name}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="template-client">Клиент</label>
                                        <select class="form-control" id="template-client" name="filters[client]">
                                            <option value="">Все клиенты</option>
                                            {foreach $templateFilterConfigurations.client.options as $option}
                                                <option value="{$option.id}">{$option.name}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="template-subject">Тема</label>
                                        <select class="form-control" id="template-subject" name="filters[subject]">
                                            <option value="">Все темы</option>
                                            {foreach $templateFilterConfigurations.subject.options as $option}
                                                <option value="{$option.id}">{$option.name}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="template-parent_subject">Тип обращения</label>
                                        <select class="form-control" id="template-parent_subject" name="filters[parent_subject]">
                                            <option value="">Все типы обращений</option>
                                            {foreach $templateFilterConfigurations.parent_subject.options as $option}
                                                <option value="{$option.id}">{$option.name}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="template-status">Статус</label>
                                        <select class="form-control" id="template-status" name="filters[status]">
                                            <option value="">Все статусы</option>
                                            {foreach $templateFilterConfigurations.status.options as $option}
                                                <option value="{$option.id}">{$option.name}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="template-priority">Приоритет</label>
                                        <select class="form-control" id="template-priority" name="filters[priority]">
                                            <option value="">Все приоритеты</option>
                                            {foreach $templateFilterConfigurations.priority.options as $option}
                                                <option value="{$option.id}">{$option.name}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="template-manager">Менеджер</label>
                                        <select class="form-control" id="template-manager" name="filters[manager]">
                                            <option value="">Все менеджеры</option>
                                            {foreach $templateFilterConfigurations.manager.options as $option}
                                                <option value="{$option.id}">{$option.name}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="template-initiator">Инициатор</label>
                                        <select class="form-control" id="template-initiator" name="filters[initiator]">
                                            <option value="">Все инициаторы</option>
                                            {foreach $templateFilterConfigurations.initiator.options as $option}
                                                <option value="{$option.id}">{$option.name}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="template-channel">Канал поступления</label>
                                        <select class="form-control" id="template-channel" name="filters[channel]">
                                            <option value="">Все каналы</option>
                                            {foreach $templateFilterConfigurations.channel.options as $option}
                                                <option value="{$option.id}">{$option.name}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Поля для экспорта -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="mdi mdi-table-column"></i> Поля для экспорта</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                {foreach $availableFields as $field}
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   id="field-{$field.key}" name="fields[]" value="{$field.key}">
                                            <label class="form-check-label" for="field-{$field.key}">
                                                {$field.label}
                                            </label>
                                        </div>
                                    </div>
                                {/foreach}
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="save-template-btn">Сохранить</button>
            </div>
        </div>
    </div>
</div>
