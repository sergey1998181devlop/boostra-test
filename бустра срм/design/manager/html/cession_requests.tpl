{assign var="meta_title" value="Цессия"}

{capture name='page_styles'}
    <link href="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet"
          type="text/css"/>
    <link href="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="design/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">
    <script>
        window.enumOptions = {
            execution_status: {$enumValues.execution_status|@json_encode nofilter},
            importance: {$enumValues.importance|@json_encode nofilter},
            cedent: {$enumValues.cedent|@json_encode nofilter},
            contract_form: {$enumValues.contract_form|@json_encode nofilter},
            counterparty: {$enumValues.counterparty|@json_encode nofilter}
        };
    </script>
{/capture}

{capture name='page_scripts'}
    <script src="design/manager/assets/plugins/moment/moment.js"></script>
    <script src="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script src="design/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
{literal}
    <script>
        $(function () {
            const currentRange = $('.daterange').val();

            let startDate, endDate;

            if (currentRange) {
                const dates = currentRange.split(' - ');
                if (dates.length === 2) {
                    startDate = moment(dates[0], 'DD.MM.YYYY');
                    endDate = moment(dates[1], 'DD.MM.YYYY');
                }
            }

            if (!startDate || !endDate) {
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('daterange') || $('.daterange').val()) {
                    startDate = moment().subtract(1, 'days');
                    endDate = moment();
                }
            }

            if (startDate && endDate) {
                $('.daterange').daterangepicker({
                    autoApply: true,
                    locale: {format: 'DD.MM.YYYY'},
                    startDate: startDate,
                    endDate: endDate
                });
            } else {
                $('.daterange').daterangepicker({
                    autoApply: true,
                    locale: {format: 'DD.MM.YYYY'},
                    autoUpdateInput: false
                });

                $('.daterange').val('');
            }

            $('.daterange').on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('DD.MM.YYYY') + ' - ' + picker.endDate.format('DD.MM.YYYY'));
                updateFilters();
            });

            function updateFilters() {
                const params = {};
                $('.filter').each(function () {
                    const name = $(this).attr('name');
                    const value = $(this).val().trim();
                    if (value) params[name] = value;
                });

                const queryString = new URLSearchParams(params).toString();
                window.location.href = '/cession_requests' + (queryString ? '?' + queryString : '');
            }

            $('.filter').on('change', updateFilters);

            let delayTimer;
            $('.filter').on('keyup', function () {
                clearTimeout(delayTimer);
                delayTimer = setTimeout(updateFilters, 500);
            });
        });

        function resetFilters() {
            $('.daterange').val('');

            window.location.href = '/cession_requests';
            return false;
        }

        function download() {
            const params = {};
            $('.filter').each(function () {
                const name = $(this).attr('name');
                const value = $(this).val();
                if (value) params[name] = value;
            });

            const query = new URLSearchParams(params).toString();
            const url = '/ajax/cession_requests.php?action=download&' + query;

            window.location.href = url;
            return false;
        }

        $(document).on('click', '.edit-icon', function () {
            const $el = $(this);
            const id = $el.data('id');
            const field = $el.data('field');
            const value = $el.data('value');

            $('#edit-id').val(id);
            $('#edit-field').val(field);

            let inputHtml = '';

            const enumFields = ['execution_status', 'importance', 'cedent', 'contract_form', 'counterparty'];

            if (enumFields.includes(field)) {
                const labelMap = {
                    execution_status: 'Статус исполнения',
                    importance: 'Важность',
                    cedent: 'Цедент',
                    contract_form: 'Форма договора',
                    counterparty: 'Контрагент'
                };

                const options = window.enumOptions?.[field] ?? [];

                inputHtml = `<label>${labelMap[field]}</label><select class="form-control" name="value">`;
                options.forEach(opt => {
                    inputHtml += `<option value="${opt}"${opt === value ? ' selected' : ''}>${opt}</option>`;
                });
                inputHtml += `</select>`;
            } else if (field === 'comments') {
                inputHtml = `<label>Комментарий</label><textarea class="form-control" name="value">${value}</textarea>`;
            } else if (field === 'transfer_date') {
                inputHtml = `<label>Дата передачи</label><input type="text" class="form-control datepicker" name="value" value="${value}">`;
            } else {
                inputHtml = `<label>${$el.closest('td').prev('th').text()}</label><input type="text" class="form-control" name="value" value="${value}">`;
            }

            $('#edit-input-wrapper').html(inputHtml);

            if (field === 'transfer_date') {
                $('.datepicker').datepicker({
                    format: 'yyyy-mm-dd',
                    autoclose: true,
                    todayHighlight: true
                });
            }

            $('#editModal').modal('show');

            if (field === 'execution_status' && value === 'Новый') {
                $('#editModal').off('change.confirmation').on('change.confirmation', 'select[name="value"]', function () {
                    const newVal = $(this).val();
                    if (newVal === 'Отозвано') {
                        $('#editModal').modal('hide');
                        setTimeout(() => {
                            showOtozvanConfirmModal();
                        }, 300);
                    }
                });
            }
        });

        function showOtozvanConfirmModal() {
            const html = `
        <div class="modal fade" id="otozvanConfirmModal" tabindex="-1" role="dialog" aria-labelledby="otozvanConfirmLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header"><h5 class="modal-title">Подтверждение</h5></div>
              <div class="modal-body">Вы уверены, что клиента отзываем?</div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Нет</button>
                <button type="button" class="btn btn-primary" id="confirm-otozvan">Да, отправить письмо</button>
              </div>
            </div>
          </div>
        </div>
    `;

            $('body').append(html);
            $('#otozvanConfirmModal').modal('show');

            $('#confirm-otozvan').on('click', function () {
                $('#otozvanConfirmModal').modal('hide');
                $('#otozvanConfirmModal').remove();
                $('#editFieldForm').submit();

                $('<input>').attr({
                    type: 'hidden',
                    name: 'send_email',
                    value: '1'
                }).appendTo('#editForm');
            });

            $('#otozvanConfirmModal').on('hidden.bs.modal', function () {
                $(this).remove();
            });
        }

        $('#editFieldForm').on('submit', function (e) {
            e.preventDefault();
            const formData = $(this).serialize();

            $.post('/ajax/cession_requests.php?action=update_row_value', formData, function (response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Ошибка при сохранении');
                }
            }, 'json');
        });

        $('#addCessionForm').on('submit', function (e) {
            e.preventDefault();

            const formData = $(this).serialize();

            $.post('/ajax/cession_requests.php?action=add_manual_request', formData, function (response) {
                if (response.success) {
                    $('#addModal').modal('hide');
                    location.reload();
                } else {
                    alert(response.error || 'Ошибка при добавлении');
                }
            }, 'json');
        });

        $(document).on('click', '.delete-row', function () {
            const id = $(this).data('id');

            if (!confirm('Вы уверены, что хотите удалить эту запись?')) {
                return;
            }

            $.post('/ajax/cession_requests.php?action=delete_manual_request', {id: id}, function (response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.error || 'Ошибка при удалении');
                }
            }, 'json');
        });

        $(document).ready(function () {
            function createTopScrollbar() {
                const tableWrapper = $('.table-responsive');

                if (tableWrapper.length === 0) return;

                const topScrollbarHTML = `
            <div class="top-scrollbar-wrapper" id="topScrollbar">
                <div class="top-scrollbar-content" id="topScrollbarContent"></div>
            </div>
        `;

                tableWrapper.before(topScrollbarHTML);

                const topScrollbar = $('#topScrollbar');
                const topScrollbarContent = $('#topScrollbarContent');
                const mainTable = tableWrapper;

                function updateScrollbarWidth() {
                    const scrollWidth = mainTable[0].scrollWidth;
                    topScrollbarContent.css('width', scrollWidth + 'px');
                }

                updateScrollbarWidth();

                topScrollbar.on('scroll', function () {
                    if (!$(this).data('syncing')) {
                        mainTable.data('syncing', true);
                        mainTable.scrollLeft($(this).scrollLeft());
                        mainTable.data('syncing', false);
                    }
                });

                mainTable.on('scroll', function () {
                    if (!$(this).data('syncing')) {
                        topScrollbar.data('syncing', true);
                        topScrollbar.scrollLeft($(this).scrollLeft());
                        topScrollbar.data('syncing', false);
                    }
                });

                $(window).on('resize', updateScrollbarWidth);

                const observer = new MutationObserver(updateScrollbarWidth);
                observer.observe(tableWrapper[0], {
                    childList: true,
                    subtree: true,
                    attributes: true
                });
            }

            createTopScrollbar();
        });

        $('#bulkRevokeBtn').on('click', function (e) {
            e.preventDefault();

            const idsToRevoke = [];

            $('.execution-checkbox:checked').each(function () {
                const status = $(this).data('status');
                const id = $(this).data('id');
                if (status === 'Новый') {
                    idsToRevoke.push(id);
                }
            });

            if (idsToRevoke.length === 0) {
                alert('Нет заявок со статусом "Новый" для отзыва.');
                return;
            }

            window.bulkRevokeIds = idsToRevoke;

            $('#bulkRevokeModal').modal('show');
        });

        $('#confirmBulkRevoke').on('click', function () {
            const ids = window.bulkRevokeIds || [];

            $('#bulkRevokeModal').modal('hide');

            $.post('/ajax/cession_requests.php?action=update_row_value', {
                ids: ids,
                field: 'execution_status',
                value: 'Отозвано',
                bulk: 1
            }, function (response) {
                console.log(response);
                if (response.success) {
                    window.location.reload();
                } else {
                    alert(response.error || 'Ошибка при массовом обновлении');
                }
            }, 'json');
        });
    </script>
{/literal}
{/capture}


<style>
    .table thead th, .table th {
        border: 2px solid;
        font-size: 12px;
        min-width: 150px;
    }

    .table td, .table th {
        font-size: 12px;
        white-space: normal;
        word-wrap: break-word;
    }

    thead.position-sticky {
        top: 0;
        background-color: #272c33;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    #addModal .form-group {
        margin-bottom: 15px;
    }

    #addModal .form-control {
        font-size: 13px;
    }

    #addModal label {
        font-weight: 500;
    }

    #addModal .modal-body {
        padding: 20px 25px;
    }

    #addModal .modal-footer {
        padding: 15px 25px;
    }

    #addModal .modal-header {
        padding: 15px 25px;
    }

    .top-scrollbar-wrapper {
        width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        margin-bottom: 7px;
        border-radius: 4px;
        height: 20px;
    }

    .top-scrollbar-content {
        height: 1px;
    }

    .resizable-handle {
        position: absolute;
        top: 0;
        right: 0;
        width: 5px;
        height: 100%;
        cursor: col-resize;
        background: transparent;
        border-right: 2px solid transparent;
        z-index: 1;
    }

    .resizable-handle:hover {
        border-right-color: #007bff;
    }

    .resizable-handle.resizing {
        border-right-color: #007bff;
        background: rgba(0, 123, 255, 0.1);
    }

    .table.resizing {
        user-select: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
    }

    .table {
        table-layout: fixed !important;
        width: max-content !important;
    }

    .table thead th, .table th {
        border: 2px solid;
        font-size: 12px;
        min-width: 150px;
        position: relative;
        user-select: none;
    }

</style>

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-file-chart"></i> <span>{$meta_title}</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">{$meta_title}</li>
                </ol>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Цессия за период
                    {if $smarty.get.daterange}
                        {$smarty.get.daterange}
                    {else}
                        {assign var="now_ts" value=$smarty.now}
                        {assign var="today" value=$now_ts|date_format:"%d.%m.%Y"}
                        {assign var="yesterday_ts" value=$now_ts-86400}
                        {assign var="yesterday" value=$yesterday_ts|date_format:"%d.%m.%Y"}
                        {$yesterday} - {$today}
                    {/if}
                </h4>

                <form>
                    <div class="row">
                        <div class="col-12 col-md-4">
                            <div class="input-group mb-3">
                                <input type="text" name="daterange" class="form-control daterange filter"
                                       value="{$smarty.get.daterange|escape}">
                                <div class="input-group-append">
                                    <span class="input-group-text"><span class="ti-calendar"></span></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <button type="submit" class="btn btn-info">
                                <i class="ti-filter"></i> Отфильтровать
                            </button>
                            <button onclick="return download();" type="button" class="btn btn-success">
                                <i class="ti-save"></i> Выгрузить
                            </button>
                            <button onclick="return resetFilters();" type="button" class="btn btn-warning">
                                <i class="ti-reload"></i> Сбросить
                            </button>
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addModal">
                                <i class="ti-plus"></i> Добавить
                            </button>
                            <button id="bulkRevokeBtn" class="btn btn-success">
                                Отозвать
                            </button>
                        </div>
                    </div>

                    <div class="row mt-2 mb-4">
                        <div class="col-12 col-md-2 py-1">
                            <label>ФИО + Дата рождения</label>
                            <input type="text" name="full_name_with_birth" class="form-control form-control-sm filter"
                                   value="{$smarty.get.full_name_with_birth|escape}">
                        </div>
                        <div class="col-12 col-md-2 py-1">
                            <label>Номер договора</label>
                            <input type="text" name="contract_number" class="form-control form-control-sm filter"
                                   value="{$smarty.get.contract_number|escape}">
                        </div>
                        <div class="col-12 col-md-2 py-1">
                            <label>Номер ШКД</label>
                            <input type="text" name="shkd_number" class="form-control form-control-sm filter"
                                   value="{$smarty.get.shkd_number|escape}">
                        </div>
                        <div class="col-12 col-md-2 py-1">
                            <label>Дата договора</label>
                            <input type="text" name="contract_date" class="form-control form-control-sm filter"
                                   value="{$smarty.get.contract_date|escape}">
                        </div>
                        <div class="col-12 col-md-2 py-1">
                            <label>Дата заявки</label>
                            <input type="text" name="request_date" class="form-control form-control-sm filter"
                                   value="{$smarty.get.request_date|escape}">
                        </div>
                        <div class="col-12 col-md-2 py-1">
                            <label>Email</label>
                            <input type="text" name="email" class="form-control form-control-sm filter"
                                   value="{$smarty.get.email|escape}">
                        </div>

                        <div class="col-12 col-md-2 py-1">
                            <label>Форма договора</label>
                            <select name="contract_form" class="form-control form-control-sm filter">
                                <option value="">Все</option>
                                {foreach from=$enumValues.contract_form item=item}
                                    <option value="{$item}"
                                            {if $smarty.get.contract_form == $item}selected{/if}>{$item}</option>
                                {/foreach}
                            </select>
                        </div>

                        <div class="col-12 col-md-2 py-1">
                            <label>Цедент</label>
                            <select name="cedent" class="form-control form-control-sm filter">
                                <option value="">Все</option>
                                {foreach from=$enumValues.cedent item=item}
                                    <option value="{$item}"
                                            {if $smarty.get.cedent == $item}selected{/if}>{$item}</option>
                                {/foreach}
                            </select>
                        </div>

                        <div class="col-12 col-md-2 py-1">
                            <label>Контрагент</label>
                            <select name="counterparty" class="form-control form-control-sm filter">
                                <option value="">Все</option>
                                {foreach from=$enumValues.counterparty item=item}
                                    <option value="{$item}"
                                            {if $smarty.get.counterparty == $item}selected{/if}>{$item}</option>
                                {/foreach}
                            </select>
                        </div>

                        <div class="col-12 col-md-2 py-1">
                            <label>Дата передачи</label>
                            <input type="text" name="transfer_date" class="form-control form-control-sm filter"
                                   value="{$smarty.get.transfer_date|escape}">
                        </div>

                        <div class="col-12 col-md-2 py-1">
                            <label>Важность</label>
                            <select name="importance" class="form-control form-control-sm filter">
                                <option value="">Все</option>
                                {foreach from=$enumValues.importance item=item}
                                    <option value="{$item}"
                                            {if $smarty.get.importance == $item}selected{/if}>{$item}</option>
                                {/foreach}
                            </select>
                        </div>

                        <div class="col-12 col-md-2 py-1">
                            <label>Статус исполнения</label>
                            <select name="execution_status" class="form-control form-control-sm filter">
                                <option value="">Все</option>
                                {foreach from=$enumValues.execution_status item=item}
                                    <option value="{$item}"
                                            {if $smarty.get.execution_status == $item}selected{/if}>{$item}</option>
                                {/foreach}
                            </select>
                        </div>

                        <div class="col-12 col-md-2 py-1">
                            <label>Замена клиента</label>
                            <input type="text" name="client_replace_status" class="form-control form-control-sm filter"
                                   value="{$smarty.get.client_replace_status|escape}">
                        </div>
                        <div class="col-12 col-md-2 py-1">
                            <label>Комментарий</label>
                            <input type="text" name="comments" class="form-control form-control-sm filter"
                                   value="{$smarty.get.comments|escape}">
                        </div>
                        <div class="col-12 col-md-2 py-1">
                            <label>Доп. действия</label>
                            <input type="text" name="extra_actions" class="form-control form-control-sm filter"
                                   value="{$smarty.get.extra_actions|escape}">
                        </div>
                    </div>

                </form>

                <div class="table-responsive">
                    <table id="cession-table" class="table table-bordered table-hover">
                        <colgroup>
                            <col style="width: 60px;">
                            <col style="width: 140px;">
                            <col style="width: 260px;">
                            <col style="width: 160px;">
                            <col style="width: 160px;">
                            <col style="width: 140px;">
                            <col style="width: 140px;">
                            <col style="width: 140px;">
                            <col style="width: 160px;">
                            <col style="width: 140px;">
                            <col style="width: 140px;">
                            <col style="width: 160px;">
                            <col style="width: 180px;">
                            <col style="width: 180px;">
                            <col style="width: 160px;">
                            <col style="width: 220px;">
                            <col style="width: 80px;">
                        </colgroup>
                        <thead class="position-sticky">
                        <tr>
                            <th class="resizable" data-column="id">ID</th>
                            <th class="resizable" data-column="request_date">Дата заявки</th>
                            <th class="resizable" data-column="full_name_with_birth">ФИО + Дата рождения</th>
                            <th class="resizable" data-column="contract_number">Номер договора</th>
                            <th class="resizable" data-column="shkd_number">Номер ШКД</th>
                            <th class="resizable" data-column="contract_date">Дата договора</th>
                            <th class="resizable" data-column="contract_form">Форма договора</th>
                            <th class="resizable" data-column="cedent">Цедент</th>
                            <th class="resizable" data-column="counterparty">Контрагент</th>
                            <th class="resizable" data-column="transfer_date">Дата передачи</th>
                            <th class="resizable" data-column="importance">Важность</th>
                            <th class="resizable" data-column="execution_status">Статус исполнения</th>
                            <th class="resizable" data-column="comments">Комментарий</th>
                            <th class="resizable" data-column="extra_actions">Доп. действия</th>
                            <th class="resizable" data-column="client_replace_status">Замена клиента</th>
                            <th class="resizable" data-column="email">Email</th>
                            <th>Действие</th>
                        </tr>

                        </thead>
                        <tbody>
                        {if $requests}
                            {foreach $requests as $r}
                                <tr>
                                    <td>{$r->id}</td>
                                    <td>{$r->request_date}</td>
                                    <td>{$r->full_name_with_birth|escape}</td>
                                    <td>{$r->contract_number|escape}</td>
                                    <td>{$r->shkd_number|escape}</td>
                                    <td>{$r->contract_date}</td>
                                    <td>
                                        {$r->contract_form|escape}
                                        <i class="ti-pencil-alt text-info ml-1 edit-icon"
                                           style="cursor:pointer;"
                                           data-id="{$r->id}"
                                           data-field="contract_form"
                                           data-value="{$r->contract_form|escape:'html'}"></i>
                                    </td>
                                    <td>
                                        {$r->cedent|escape}
                                        <i class="ti-pencil-alt text-info ml-1 edit-icon"
                                           style="cursor:pointer;"
                                           data-id="{$r->id}"
                                           data-field="cedent"
                                           data-value="{$r->cedent|escape:'html'}"></i>
                                    </td>
                                    <td>
                                        {$r->counterparty|escape}
                                        <i class="ti-pencil-alt text-info ml-1 edit-icon"
                                           style="cursor:pointer;"
                                           data-id="{$r->id}"
                                           data-field="counterparty"
                                           data-value="{$r->counterparty|escape:'html'}"></i>
                                    </td>
                                    <td>
                                        {$r->transfer_date}
                                        <i class="ti-pencil-alt text-info ml-1 edit-icon"
                                           style="cursor:pointer;"
                                           data-id="{$r->id}"
                                           data-field="transfer_date"
                                           data-value="{$r->transfer_date|escape:'html'}"></i>
                                    </td>
                                    <td>
                                        {$r->importance|escape}
                                        <i class="ti-pencil-alt text-info ml-1 edit-icon"
                                           style="cursor:pointer;"
                                           data-id="{$r->id}"
                                           data-field="importance"
                                           data-value="{$r->importance|escape:'html'}"></i>
                                    </td>
                                    <td>
                                        <span style="display: inline-block;">{$r->execution_status|escape}</span>
                                        <i class="ti-pencil-alt text-info ml-2 edit-icon"
                                           style="cursor:pointer; display:inline-block;"
                                           data-id="{$r->id}"
                                           data-field="execution_status"
                                           data-value="{$r->execution_status|escape:'html'}"></i>
                                        {if $r->execution_status == 'Новый'}
                                            <input type="checkbox"
                                                   class="ml-2 execution-checkbox"
                                                   data-id="{$r->id}"
                                                   data-status="Новый"
                                                   style="transform: scale(1.2); margin-left: 8px;">
                                        {/if}
                                    </td>
                                    <td>
                                        {$r->comments|escape}
                                        <i class="ti-pencil-alt text-info ml-1 edit-icon"
                                           style="cursor:pointer;"
                                           data-id="{$r->id}"
                                           data-field="comments"
                                           data-value="{$r->comments|escape:'html'}"></i>
                                    </td>
                                    <td>
                                        {$r->extra_actions|escape}
                                        <i class="ti-pencil-alt text-info ml-1 edit-icon"
                                           style="cursor:pointer;"
                                           data-id="{$r->id}"
                                           data-field="extra_actions"
                                           data-value="{$r->extra_actions|escape:'html'}"></i>
                                    </td>
                                    <td>
                                        {$r->client_replace_status|escape}
                                        <i class="ti-pencil-alt text-info ml-1 edit-icon"
                                           style="cursor:pointer;"
                                           data-id="{$r->id}"
                                           data-field="client_replace_status"
                                           data-value="{$r->client_replace_status|escape:'html'}"></i>
                                    </td>
                                    <td>
                                        {$r->email|escape}
                                        {if $r->source == 'manual'}
                                            <i class="ti-pencil-alt text-info ml-1 edit-icon"
                                               style="cursor:pointer;"
                                               data-id="{$r->id}"
                                               data-field="email"
                                               data-value="{$r->email|escape:'html'}"></i>
                                        {/if}
                                    </td>
                                    <td>
                                        {if $r->source == 'manual'}
                                            <button class="btn btn-sm btn-danger delete-row" data-id="{$r->id}">
                                                <i class="ti-trash"></i>
                                            </button>
                                        {else}
                                            —
                                        {/if}
                                    </td>
                                </tr>
                            {/foreach}
                        {else}
                            <tr>
                                <td colspan="16" class="text-center text-danger">Данные не найдены</td>
                            </tr>
                        {/if}
                        </tbody>
                    </table>
                </div>

                {if $pages_count > 1}
                    <div class="card-footer">
                        <div class="jsgrid-pager d-flex justify-content-end align-items-center">
                            {section name=pages loop=$pages_count start=1}
                                {$p = $smarty.section.pages.index + 1}
                                {if ($p == $current_page)}
                                    <span class="jsgrid-pager-page jsgrid-pager-current-page">{$p}</span>
                                {else}
                                    <span class="jsgrid-pager-page"><a href="cession_requests?page={$p}">{$p}</a></span>
                                {/if}
                            {/section}
                            &nbsp;&nbsp; {$current_page} из {$pages_count}
                        </div>
                    </div>
                {/if}
                <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <form id="editFieldForm">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Редактирование</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="id" id="edit-id">
                                    <input type="hidden" name="field" id="edit-field">
                                    <div class="form-group" id="edit-input-wrapper"></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                                    <button type="submit" class="btn btn-primary">Сохранить</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <form id="addCessionForm">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Добавить новую заявку</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>

                                <div class="modal-body">
                                    <div class="row">
                                        <!-- LEFT COLUMN -->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Дата заявки *</label>
                                                <input type="date" class="form-control" name="request_date" required>
                                            </div>
                                            <div class="form-group">
                                                <label>ФИО + Дата рождения *</label>
                                                <input type="text" class="form-control" name="full_name_with_birth"
                                                       placeholder="Например: Иванов Иван Иванович 01.01.1980"
                                                       required>
                                            </div>
                                            <div class="form-group">
                                                <label for="contract_number">Номер договора *</label>
                                                <input type="text" name="contract_number" class="form-control"
                                                       placeholder="Например: A25-1234567" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="shkd_number">Номер ШКД *</label>
                                                <input type="text" name="shkd_number" class="form-control"
                                                       placeholder="Например: AA25-1234567" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Дата договора займа *</label>
                                                <input type="date" class="form-control" name="contract_date" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Email *</label>
                                                <input type="email" class="form-control" name="email" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Форма договора</label>
                                                <select class="form-control" name="contract_form">
                                                    {foreach from=$enumValues.contract_form item=item}
                                                        <option value="{$item}"
                                                                {if $item == 'Займ'}selected{/if}>{$item}</option>
                                                    {/foreach}
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Цедент</label>
                                                <select class="form-control" name="cedent">
                                                    {foreach from=$enumValues.cedent item=item}
                                                        <option value="{$item}"
                                                                {if $item == 'Алфавит'}selected{/if}>{$item}</option>
                                                    {/foreach}
                                                </select>
                                            </div>
                                        </div>

                                        <!-- RIGHT COLUMN -->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Контрагент</label>
                                                <select name="counterparty" class="form-control">
                                                    {foreach from=$enumValues.counterparty item=item}
                                                        <option value="{$item}">{$item}</option>
                                                    {/foreach}
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Дата передачи</label>
                                                <input type="date" class="form-control" name="transfer_date">
                                            </div>
                                            <div class="form-group">
                                                <label>Важность</label>
                                                <select class="form-control" name="importance">
                                                    {foreach from=$enumValues.importance item=item}
                                                        <option value="{$item}"
                                                                {if $item == 'Не срочно'}selected{/if}>{$item}</option>
                                                    {/foreach}
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Статус исполнения</label>
                                                <select class="form-control" name="execution_status">
                                                    {foreach from=$enumValues.execution_status item=item}
                                                        <option value="{$item}"
                                                                {if $item == 'Новый'}selected{/if}>{$item}</option>
                                                    {/foreach}
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Комментарий</label>
                                                <textarea class="form-control" name="comments" rows="2"></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label>Доп. действия</label>
                                                <textarea class="form-control" name="extra_actions" rows="2"></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label>Замена клиента</label>
                                                <input type="text" class="form-control" name="client_replace_status">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-success">Добавить</button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal fade" id="bulkRevokeModal" tabindex="-1" role="dialog"
                     aria-labelledby="bulkRevokeModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Подтверждение</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                Вы уверены, что хотите <b>отозвать выбранные заявки</b>? Письма будут отправлены на
                                указанные email.
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                                <button type="button" class="btn btn-success" id="confirmBulkRevoke">Да, отозвать
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>