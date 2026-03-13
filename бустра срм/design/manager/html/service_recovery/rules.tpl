{$meta_title='Управление правилами восстановления' scope=parent}

{capture name='page_styles'}
<link href="design/manager/assets/plugins/select2/dist/css/select2.min.css" rel="stylesheet"/>
<style>
    :root {
        --dark-bg-primary: #212529;
        --dark-bg-secondary: #2d3338;
        --dark-bg-tertiary: #1a1d20;
        --dark-border-color: #404850;
        --dark-text-primary: #c8c8c8;
        --dark-text-secondary: #e2e2e2;
        --dark-accent-color: #009efb;
    }
    .table-rules { width: 100%; border-collapse: collapse; font-size: 13px; }
    .table-rules th, .table-rules td { vertical-align: middle; white-space: nowrap; border: 1px solid var(--dark-bg-secondary); padding: 10px; }
    .table-rules thead th { background-color: var(--dark-bg-secondary); color: #fff; border-color: var(--dark-border-color); }
    .table-rules tbody td { border-color: var(--dark-bg-secondary); background-color: var(--dark-bg-primary); color: var(--dark-text-primary); }
    .table-rules tbody tr.rule-row:hover td { background-color: rgba(255,255,255,0.05); color: #f0f0f0; }
    .table-rules .toggle-details { color: var(--dark-accent-color); text-decoration: none; cursor: pointer; font-size: 1.2rem; }
    .table-rules .toggle-details .mdi { transition: transform 0.2s ease-in-out; }
    .table-rules .toggle-details.expanded .mdi { transform: rotate(90deg); }
    .badge-success { background-color: #28a745; color: white; } .badge-danger { background-color: #dc3545; color: white; } .badge-warning { background-color: #ffc107; color: black; } .badge-secondary { background-color: #6c757d; color: white; } .badge-info { background-color: #17a2b8; color: white; } .badge-primary { background-color: #007bff; color: white; }
    dark, dark:disabled { background-color: #2a2e33; border-color: var(--dark-border-color); color: var(--dark-text-secondary); }
    dark:focus { background-color: #2a2e33; border-color: var(--dark-accent-color); color: var(--dark-text-secondary); box-shadow: none; }
    .modal-content { background-color: var(--dark-bg-primary); color: var(--dark-text-primary); border: 1px solid var(--dark-border-color); }
    .modal-header, .modal-footer { border-color: var(--dark-border-color); }
    .close { color: #fff; opacity: 0.7; }
    .details-row td { background-color: var(--dark-bg-tertiary) !important; padding: 0 !important; }
    .details-content { padding: 15px; }
    .details-content h6 { color: #fff; border-bottom: 1px solid var(--dark-border-color); padding-bottom: 8px; margin-top: 10px; margin-bottom: 15px; }
    .details-row { display: none; }
    .loader { border: 4px solid var(--dark-border-color); border-top: 4px solid var(--dark-accent-color); border-radius: 50%; width: 30px; height: 30px; animation: spin 1s linear infinite; margin: 20px auto; }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .details-content .table-bordered {
        font-size: 14px;
        border-color: var(--dark-border-color);
    }

    .details-content .table-bordered th,
    .details-content .table-bordered td {
        border-color: var(--dark-border-color);
    }

    /* Основные контейнеры */
    .select2-container--bootstrap4 .select2-selection--single,
    .select2-container--bootstrap4 .select2-selection--multiple {
        background-color: #272c33 !important;
        border: 1px solid #495057 !important;
        border-radius: 0 !important;
        min-height: 38px;
    }

    /* Фокус */
    .select2-container--bootstrap4.select2-container--focus .select2-selection {
        border-color: #009efb !important;
        box-shadow: none !important;
    }

    /* Множественный выбор - основной контейнер */
    .select2-container--bootstrap4 .select2-selection--multiple {
        padding: 2px 3px !important;
    }

    /* Теги в множественном выборе */
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
        background-color: #009efb !important;
        border: 1px solid #009efb !important;
        border-radius: 0 !important;
        color: #fff !important;
        padding: 4px 24px 4px 8px !important;
        margin: 3px 3px 3px 0 !important;
        position: relative;
        display: inline-block;
        line-height: 1.3;
        font-size: 14px;
    }

    /* Кнопка X в теге */
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove {
        color: #fff !important;
        cursor: pointer;
        display: inline-block;
        font-weight: bold;
        position: absolute !important;
        right: 4px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        font-size: 16px;
        line-height: 1;
        padding: 0 4px;
    }

    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #ffc107 !important;
        background-color: transparent !important;
    }

    /* Скрываем placeholder */
    .select2-container--bootstrap4 .select2-selection__placeholder {
        display: none !important;
    }

    /* Поле ввода в множественном выборе */
    .select2-container--bootstrap4 .select2-selection--multiple .select2-search__field {
        background: transparent !important;
        border: 0 !important;
        outline: 0 !important;
        color: #fff !important;
        margin: 3px 0 !important;
        padding: 4px !important;
    }

    /* Выпадающий список */
    .select2-container--bootstrap4 .select2-dropdown {
        background-color: #272c33 !important;
        border: 1px solid #495057 !important;
        border-radius: 0 !important;
        margin-top: -1px;
    }

    /* Опции в выпадающем списке */
    .select2-container--bootstrap4 .select2-results__option {
        padding: 8px 12px !important;
        color: #e9ecef !important;
        background-color: transparent !important;
    }

    /* Выделенная опция */
    .select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected] {
        background-color: #009efb !important;
        color: #fff !important;
    }

    /* Выбранная опция */
    .select2-container--bootstrap4 .select2-results__option[aria-selected="true"] {
        background-color: #495057 !important;
        color: #fff !important;
    }

    /* Отключенная опция */
    .select2-container--bootstrap4 .select2-results__option[aria-disabled="true"] {
        color: #6c757d !important;
        cursor: not-allowed;
        opacity: 0.6;
    }

    /* Поле поиска в выпадающем списке */
    .select2-container--bootstrap4 .select2-search--dropdown .select2-search__field {
        background-color: #212529 !important;
        border: 1px solid #495057 !important;
        border-radius: 0 !important;
        color: #fff !important;
        padding: 6px 12px !important;
    }

    /* Стрелка */
    .select2-container--bootstrap4 .select2-selection__arrow {
        height: 36px;
        right: 10px;
    }

    .select2-container--bootstrap4 .select2-selection__arrow b {
        border-color: #6c757d transparent transparent transparent !important;
        border-width: 5px 4px 0 4px;
    }

    .select2-container .select2-selection--multiple .select2-selection__rendered {
        white-space: normal;
    }
</style>
{/capture}

<div class="page-wrapper">
    <div class="container-fluid">
        {* Заголовок и кнопка *}
        <div class="row page-titles">
            <div class="col-12 col-md-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0"><i class="mdi mdi-cogs"></i><span>Правила восстановления услуг</span></h3>
                <ol class="breadcrumb"><li class="breadcrumb-item"><a href="/">Главная</a></li><li class="breadcrumb-item active">Правила восстановления</li></ol>
            </div>
            <div class="col-12 col-md-4 align-self-center">
                <button id="createRuleBtn" class="btn btn-success float-right d-none d-md-block"><i class="mdi mdi-plus-circle"></i> Создать правило</button>
            </div>
        </div>

        {* Таблица правил *}
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="rules-table" class="table-rules">
                        <thead>
                        <tr>
                            <th style="width: 30px;"></th><th style="width: 50px;">ID</th><th>Название</th>
                            <th style="width: 120px;">Активность</th><th style="width: 120px;">Приоритет</th><th style="width: 320px;">Действия</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $rules as $rule}
                            <tr class="rule-row" data-id="{$rule.id}">
                                <td><a href="#" class="toggle-details" title="Показать/скрыть историю запусков"><i class="mdi mdi-chevron-right"></i></a></td>
                                <td>{$rule.id}</td>
                                <td>{$rule.name|escape}</td>
                                <td>
                                    {if $rule.is_active}<span class="badge badge-success">Да</span>{else}<span class="badge badge-secondary">Нет</span>{/if}
                                </td>
                                <td>{$rule.priority}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary run-btn {if !$rule.is_active}d-none{/if}" data-id="{$rule.id}" title="Запустить правило вручную">
                                        <i class="mdi mdi-play"></i> Запустить
                                    </button>

                                    <button class="btn btn-sm btn-info edit-btn" data-rule='{$rule|json_encode|escape}'>
                                        <i class="mdi mdi-pencil"></i> Редактировать
                                    </button>

                                    <button class="btn btn-sm btn-danger toggle-btn" data-id="{$rule.id}" data-active="{$rule.is_active}">
                                        <i class="mdi mdi-delete"></i> {($rule.is_active) ? 'Деактивировать' : 'Активировать' }
                                    </button>
                                </td>
                            </tr>
                            <tr class="details-row" id="details-{$rule.id}">
                                <td colspan="6">
                                    <div class="details-content">
                                        <h6><i class="mdi mdi-history"></i> История последних запусков</h6>
                                        <div class="run-history-container"></div>
                                    </div>
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{* Модальное окно *}
<div class="modal fade" id="ruleModal" tabindex="-1" role="dialog" aria-labelledby="ruleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ruleModalLabel">Настройки правила</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="ruleForm">
                    <input type="hidden" name="id" id="formRuleId">
                    <div class="row">
                        <div class="col-md-8 form-group"><label for="formName">Название правила</label><input type="text" class="form-control" id="formName" name="name" required></div>
                        <div class="col-md-4 form-group"><label for="formPriority">Приоритет (меньше = важнее)</label><input type="number" class="form-control" id="formPriority" name="priority" value="100" required></div>
                    </div>
                    <div class="form-group"><div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input" id="formIsActive" name="is_active" value="1" checked><label class="custom-control-label" for="formIsActive">Правило активно</label></div></div>
                    <hr style="border-color: #404850;">

                    <h6>Критерии отбора кандидатов</h6>

                    {* --- Новые поля для выбора периода --- *}
                    <div class="form-group">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="dateFilterTypeDays" name="date_filter_type" class="custom-control-input" value="days" checked>
                            <label class="custom-control-label" for="dateFilterTypeDays">Относительный период</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="dateFilterTypePeriod" name="date_filter_type" class="custom-control-input" value="period">
                            <label class="custom-control-label" for="dateFilterTypePeriod">Точный период</label>
                        </div>
                    </div>

                    <div id="daysGroup" class="row">
                        <div class="col-md-12 form-group">
                            <label for="formDaysSinceDisable">Дней с момента отключения (>=)</label>
                            <input type="number" class="form-control" id="formDaysSinceDisable" name="days_since_disable" value="3" required>
                        </div>
                    </div>
                    <div id="periodGroup" class="row" style="display: none;">
                        <div class="col-md-6 form-group">
                            <label for="formDisabledFrom">Отключено с</label>
                            <input type="date" class="form-control" id="formDisabledFrom" name="disabled_from">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="formDisabledTo">Отключено по</label>
                            <input type="date" class="form-control" id="formDisabledTo" name="disabled_to">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group"><label for="formMinLoanAmount">Мин. сумма займа</label><input type="number" step="0.01" class="form-control" id="formMinLoanAmount" name="min_loan_amount" value="1000" min="0"></div>
                        <div class="col-md-6 form-group"><label for="formMaxLoanAmount">Макс. сумма займа</label><input type="number" step="0.01" class="form-control" id="formMaxLoanAmount" name="max_loan_amount" min="1000" value="30000"></div>
                    </div>

                    {* --- Новые поля Select2 --- *}
                    <div class="form-group">
                        <label for="formRepaymentStage">Этап заявки (пусто = все активные)</label>
                        <select id="formRepaymentStage" name="repayment_stage" class="form-control">
                            <option value="">-- Любой активный --</option>
                            {foreach $all_stages as $stage}
                                <option value="{$stage.key}">{$stage.label|escape}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="formServiceKeys">Ключи доп. услуг (пусто = все, релевантные этапу)</label>
                        <select id="formServiceKeys" name="service_keys[]" class="form-control" multiple="multiple">
                            {foreach $all_service_keys as $service}
                                <option value="{$service.key}">{$service.label|escape}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="formManagerIds">Менеджеры, отключившие услугу (пусто = все)</label>
                        <select id="formManagerIds" name="manager_ids[]" class="form-control" multiple="multiple">
                            {foreach $managers as $manager}
                                <option value="{$manager->id}">{$manager->name|escape}</option>
                            {/foreach}
                        </select>
                    </div>

                    <hr style="border-color: #404850;">
                    <h6>Настройки автозапуска</h6>
                    <div class="form-group"><div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input" id="formAutoRunEnabled" name="auto_run_enabled" value="1"><label class="custom-control-label" for="formAutoRunEnabled">Включить автозапуск по расписанию</label></div></div>
                    <div class="form-group" id="cronScheduleGroup" style="display: none;"><label for="formCronSchedule">Расписание CRON</label><input type="text" class="form-control" id="formCronSchedule" name="cron_schedule" placeholder="0 2 * * *"><small class="form-text text-muted">Например, `0 2 * * *` для запуска каждый день в 2 часа ночи.</small></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                <button type="submit" class="btn btn-primary" form="ruleForm">Сохранить</button>
            </div>
        </div>
    </div>
</div>

{capture name='page_scripts'}
    <script src="design/manager/assets/plugins/select2/dist/js/select2.full.min.js"></script>
    <script>
        {literal}
        $(function() {
            const servicesByStage = {/literal}{$services_by_stage_json}{literal};

            // --- Инициализация Select2 ---
            const select2Options = {
                theme: 'bootstrap4',
                width: '100%',
                minimumResultsForSearch: 10,
                language: "ru",
            };

            $('#formRepaymentStage').select2({...select2Options});
            $('#formServiceKeys').select2({...select2Options});
            $('#formManagerIds').select2({...select2Options});

            // --- Управление видимостью полей для фильтрации по дате ---
            $('input[name="date_filter_type"]').on('change', function() {
                if (this.value === 'days') {
                    $('#daysGroup').slideDown();
                    $('#periodGroup').slideUp();
                    $('#formDisabledFrom, #formDisabledTo').prop('disabled', true);
                    $('#formDaysSinceDisable').prop('disabled', false);
                } else {
                    $('#daysGroup').slideUp();
                    $('#periodGroup').slideDown();
                    $('#formDisabledFrom, #formDisabledTo').prop('disabled', false);
                    $('#formDaysSinceDisable').prop('disabled', true);
                }
            });

            // --- Логика связанных списков "Этап -> Услуги" ---
            $('#formRepaymentStage').on('change', function() {
                const selectedStage = $(this).val();
                const $serviceKeysSelect = $('#formServiceKeys');
                const currentServiceKeys = $serviceKeysSelect.val() || [];

                const allowedServices = (selectedStage && servicesByStage[selectedStage]) ? servicesByStage[selectedStage] : null;

                $serviceKeysSelect.find('option').each(function() {
                    const optionValue = $(this).val();

                    const shouldBeDisabled = allowedServices ? !allowedServices.includes(optionValue) : false;

                    $(this).prop('disabled', shouldBeDisabled);
                });
                
                const newAllowedValues = $serviceKeysSelect.find('option:not(:disabled)').map(function() { return $(this).val(); }).get();
                const newSelection = currentServiceKeys.filter(key => newAllowedValues.includes(key));

                $serviceKeysSelect.val(newSelection).trigger('change');
            });

            // --- Хелперы для SweetAlert ---
            const showSuccessAlert = (title, text = '') => Swal.fire({ title, text, icon: 'success'});
            const showErrorAlert = (title, text = '') => Swal.fire({ title, text, icon: 'error'});
            const showConfirmDialog = (title, text, confirmButtonText) => {
                return Swal.fire({
                    title, text, icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: confirmButtonText,
                    cancelButtonText: 'Отмена'
                });
            };

            // --- Открытие модального окна для СОЗДАНИЯ ---
            $('#createRuleBtn').on('click', function() {
                $('#ruleForm')[0].reset();
                $('#formRuleId').val('');
                $('#ruleModalLabel').text('Создание нового правила');

                // Сброс Select2
                $('#formRepaymentStage, #formServiceKeys, #formManagerIds').val(null).trigger('change');

                $('#formIsActive').prop('checked', true);
                $('#formAutoRunEnabled').prop('checked', false).trigger('change');

                // Устанавливаем фильтр по дате по умолчанию
                $('#dateFilterTypeDays').prop('checked', true).trigger('change');

                $('#ruleModal').modal('show');
            });

            // --- Открытие модального окна для РЕДАКТИРОВАНИЯ ---
            $('#rules-table').on('click', '.edit-btn', function() {
                const ruleData = $(this).data('rule');
                $('#ruleForm')[0].reset(); // Сначала сбрасываем форму

                // Заполняем основные поля
                $('#formRuleId').val(ruleData.id);
                $('#formName').val(ruleData.name);
                $('#formPriority').val(ruleData.priority);
                $('#formMinLoanAmount').val(ruleData.min_loan_amount);
                $('#formMaxLoanAmount').val(ruleData.max_loan_amount);
                $('#formCronSchedule').val(ruleData.cron_schedule);
                $('#formIsActive').prop('checked', ruleData.is_active);
                $('#formAutoRunEnabled').prop('checked', ruleData.auto_run_enabled).trigger('change');

                // Заполняем Select2
                $('#formRepaymentStage').val(ruleData.repayment_stage).trigger('change');
                $('#formServiceKeys').val(ruleData.service_keys).trigger('change');
                $('#formManagerIds').val(ruleData.manager_ids).trigger('change');

                // Логика полей даты
                if (ruleData.disabled_from || ruleData.disabled_to) {
                    $('#dateFilterTypePeriod').prop('checked', true).trigger('change');
                    $('#formDisabledFrom').val(ruleData.disabled_from ? ruleData.disabled_from.split(' ')[0] : '');
                    $('#formDisabledTo').val(ruleData.disabled_to ? ruleData.disabled_to.split(' ')[0] : '');
                    $('#formDaysSinceDisable').val(0);
                } else {
                    $('#dateFilterTypeDays').prop('checked', true).trigger('change');
                    $('#formDaysSinceDisable').val(ruleData.days_since_disable);
                    $('#formDisabledFrom').val('');
                    $('#formDisabledTo').val('');
                }

                $('#ruleModalLabel').text('Редактирование правила #' + ruleData.id);
                $('#ruleModal').modal('show');
            });

            // --- Отправка формы ---
            $('#ruleForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                let data = { action: 'save_rule' };
                form.serializeArray().forEach(item => {
                    if (item.name.endsWith('[]')) {
                        let cleanName = item.name.slice(0, -2);
                        if (!data[cleanName]) data[cleanName] = [];
                        data[cleanName].push(item.value);
                    } else {
                        data[item.name] = item.value;
                    }
                });

                data.is_active = $('#formIsActive').is(':checked') ? 1 : 0;
                data.auto_run_enabled = $('#formAutoRunEnabled').is(':checked') ? 1 : 0;

                // Очищаем неактуальные данные по датам
                if (data.date_filter_type === 'days') {
                    data.disabled_from = null;
                    data.disabled_to = null;
                } else {
                    data.days_since_disable = 0;
                }

                $.ajax({
                    url: window.location.href,
                    method: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function(response) {
                        if(response.success){
                            $('#ruleModal').modal('hide');
                            showSuccessAlert('Успешно!', 'Правило сохранено.').then(() => location.reload());
                        } else {
                            showErrorAlert('Ошибка сохранения', response.error);
                        }
                    },
                    error: function(jqXHR) {
                        const errorMsg = jqXHR.responseJSON && jqXHR.responseJSON.error ? jqXHR.responseJSON.error : 'Произошла ошибка сервера.';
                        showErrorAlert('Ошибка', errorMsg);
                    }
                });
            });

            // --- Переключение активности правила ---
            $('#rules-table').on('click', '.toggle-btn', function () {
                const ruleId = $(this).data('id');
                const isActive = $(this).data('active') === 1;
                const actionText = isActive ? 'деактивировать' : 'активировать';
                const buttonText = isActive ? 'Да, деактивировать' : 'Да, активировать';

                showConfirmDialog('Подтвердите действие', `Вы уверены, что хотите ${actionText} правило #${ruleId}?`, buttonText).then((result) => {
                    if (result.value) {
                        $.ajax({
                            url: window.location.href,
                            method: 'POST',
                            data: {action: 'toggle_rule', id: ruleId},
                            dataType: 'json',
                            success: function (response) {
                                if (response.success) {
                                    showSuccessAlert('Выполнено!', `Правило #${ruleId} успешно ${isActive ? 'деактивировано' : 'активировано'}.`).then(() => location.reload());
                                } else {
                                    showErrorAlert('Ошибка', response.error);
                                }
                            },
                            error: function() { showErrorAlert('Критическая ошибка', 'Не удалось выполнить запрос.'); }
                        });
                    }
                });
            });

            // --- Ручной запуск правила ---
            $('#rules-table').on('click', '.run-btn', function() {
                const ruleId = $(this).data('id');
                const ruleName = $(this).closest('tr').find('td:nth-child(3)').text().trim();
                const btn = $(this);

                showConfirmDialog('Подтвердите запуск', `Вы уверены, что хотите вручную запустить правило "${ruleName}" (#${ruleId})?`, 'Да, запустить').then((result) => {
                    if (result.value) {
                        btn.prop('disabled', true).html('<i class="mdi mdi-spin mdi-loading"></i> Запуск...');
                        $.ajax({
                            url: window.location.href,
                            method: 'POST',
                            data: { action: 'run_rule', id: ruleId },
                            dataType: 'json',
                            success: function(response) {
                                if(response.success) {
                                    showSuccessAlert('Процесс успешно завершен!', response.result.message);
                                } else {
                                    showErrorAlert('Ошибка при запуске', response.error);
                                }
                            },
                            error: function() { showErrorAlert('Критическая ошибка', 'Не удалось выполнить запуск правила.'); },
                            complete: function() { btn.prop('disabled', false).html('<i class="mdi mdi-play"></i> Запустить'); }
                        });
                    }
                });
            });

            // --- Загрузка истории запусков (без изменений) ---
            let activeRequests = {};
            function loadRunHistory(ruleId, container) {
                if (activeRequests[ruleId]) { return; }
                activeRequests[ruleId] = true;
                container.html('<div class="loader"></div>');
                $.ajax({
                    url: window.location.href,
                    method: 'POST',
                    data: { action: 'get_rule_runs', id: ruleId },
                    dataType: 'json',
                    success: function(response) {
                        container.empty();
                        if (response.success && response.runs.length > 0) {
                            let table = '<table class="table table-sm table-dark table-bordered"><thead><tr><th>ID</th><th>Время</th><th>Тип</th><th>Статус</th><th>Обработано</th><th>Восстановлено</th></tr></thead><tbody>';
                            response.runs.forEach(function(run) {
                                let statusBadge = run.status === 'completed' ? `<span class="badge badge-success">Завершен</span>` : (run.status === 'failed' ? `<span class="badge badge-danger">Ошибка</span>` : `<span class="badge badge-warning">Выполняется</span>`);
                                let runType = run.run_type === 'auto' ? '<span class="badge badge-info">Авто</span>' : '<span class="badge badge-primary">Ручной</span>';
                                table += `<tr><td>${run.id}</td><td>${run.started_at}</td><td>${runType}</td><td>${statusBadge}</td><td>${run.processed_candidates || 0}</td><td>${run.reenabled_count || 0}</td></tr>`;
                            });
                            table += '</tbody></table>';
                            container.html(table);
                        } else {
                            container.html('<p class="text-muted text-center py-3">История запусков пуста.</p>');
                        }
                    },
                    error: function() { container.html('<p class="text-danger text-center py-3">Не удалось загрузить историю запусков.</p>'); },
                    complete: function() { delete activeRequests[ruleId]; }
                });
            }
            $('#rules-table').on('click', '.toggle-details', function(e) {
                e.preventDefault();
                const link = $(this);
                const ruleId = link.closest('tr').data('id');
                const detailsRow = $('#details-' + ruleId);
                link.toggleClass('expanded');
                if(detailsRow.is(':visible')){
                    detailsRow.hide();
                } else {
                    detailsRow.show();
                    if (link.hasClass('expanded') && detailsRow.find('.run-history-container').is(':empty')) {
                        loadRunHistory(ruleId, detailsRow.find('.run-history-container'));
                    }
                }
            });

        });
        {/literal}
    </script>
{/capture}