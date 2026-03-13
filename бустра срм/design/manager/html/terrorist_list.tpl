{$meta_title = 'Списки террористов (импорт)' scope=parent}

{capture name='page_styles'}
    <style>
        .status-badge {
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 10px;
            display: inline-block;
        }

        .status-uploaded {
            background-color: #e2e3e5;
            color: #383d41;
        }

        .status-queued {
            background-color: #cce5ff;
            color: #004085;
        }

        .status-processing {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-done {
            background-color: #d4edda;
            color: #155724;
        }

        .status-error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .row-processing {
            background-color: #fffdf3;
        }

        .row-done {
            background-color: #f6fffa;
        }

        .row-error {
            background-color: #fff5f5;
        }

        .file-name-cell small {
            display: block;
            color: #6c757d;
        }

        .source-tabs .nav-link {
            padding: 0.25rem 0.75rem;
            font-size: 0.9rem;
        }

        .badge-current {
            font-size: 11px;
            padding: 2px 6px;
        }

        .table-sm td {
            vertical-align: middle;
        }
    </style>
{/capture}

<div class="page-wrapper">
    <div class="container-fluid mt-3">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="mb-1">Списки террористов (импорт)</h3>
                <div class="text-muted">
                    Загрузка файлов исходных списков (Росфинмониторинг, ООН, МВК и др.)
                    с последующей обработкой кроном и построением единого реестра.
                </div>
            </div>
        </div>

        {* Навигация по источникам *}
        <div class="card mb-3">
            <div class="card-body py-2">
                <div class="d-flex align-items-center">
                    <div class="mr-3 text-muted small">
                        Источники:
                    </div>
                    <ul class="nav nav-pills source-tabs">
                        {foreach $sources as $src}
                            <li class="nav-item mr-1 mb-1">
                                <a href="#"
                                   class="nav-link {if $src->code == $default_source_code}active{/if}"
                                   data-source-code="{$src->code|escape}">
                                    {$src->name|escape}
                                </a>
                            </li>
                        {/foreach}
                    </ul>
                </div>
            </div>
        </div>

        {* Форма загрузки *}
        <div class="card mb-4">
            <div class="card-header">
                <strong>Загрузить файл списка</strong>
            </div>
            <div class="card-body">
                <form id="uploadForm" enctype="multipart/form-data" method="post" onsubmit="return false;">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="source_code">Источник списка</label>
                            <select id="source_code" name="source_code" class="form-control">
                                {foreach $sources as $src}
                                    <option value="{$src->code|escape}"
                                            {if $src->code == $default_source_code}selected{/if}>
                                        {$src->name|escape} ({$src->code|escape})
                                    </option>
                                {/foreach}
                            </select>
                            <small class="form-text text-muted">
                                Источники задаются в таблице <code>s_terrorist_sources</code>.
                            </small>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="xml_file">Файл списка (XML)</label>
                            <input type="file"
                                   id="xml_file"
                                   name="xml_file"
                                   class="form-control-file"
                                   accept=".xml,.zip">
                            <small class="form-text text-muted">
                                Исходный XML обычно ~60 МБ.
                                Обработка идёт потоково, без загрузки всего файла в память.
                            </small>
                        </div>

                        <div class="form-group col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fa fa-upload"></i> Загрузить
                            </button>
                        </div>
                    </div>
                </form>

                <div id="uploadResult" class="mt-3"></div>

                <div class="alert alert-info mt-3 mb-0">
                    После загрузки файл попадает в очередь для обработки кроном.
                    По завершении обработки создаётся запись, заполняются счётчики записей,
                    и актуальный список помечается как «Текущий».
                </div>
            </div>
        </div>

        {* Таблица загруженных файлов по выбранному источнику *}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Загруженные файлы</strong>
                <span class="text-muted small">
                    Источник: <span id="currentSourceLabel">
                        {foreach $sources as $src}
                            {if $src->code == $default_source_code}
                                {$src->name|escape} ({$src->code|escape})
                            {/if}
                        {/foreach}
                    </span>
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive mb-0">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th style="width:60px;">ID</th>
                            <th>Источник</th>
                            <th style="width:120px;">Тип файла</th>
                            <th>Имя файла</th>
                            <th style="width:130px;">Дата списка</th>
                            <th style="width:120px;">Статус</th>
                            <th style="width:130px;">Строк обработано</th>
                            <th style="width:150px;">Субъектов в файле</th>
                            <th style="width:170px;">Актуальны сейчас</th>
                            <th>Ошибка</th>
                        </tr>
                        </thead>
                        <tbody id="filesBody">
                        <tr>
                            <td colspan="10" class="text-center text-muted">
                                Выберите источник или загрузите первый файл
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {* Таблица клиентов (субъектов) по выбранному файлу *}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Лица из перечня (субъекты) в файле</strong>
                <div class="d-flex align-items-center">
                    <span class="text-muted small mr-3">
                        Файл: <span id="currentFileLabel">не выбран</span>
                    </span>
                    <input type="text"
                           id="subjectsSearch"
                           class="form-control form-control-sm mr-2"
                           placeholder="ФИО / ИНН / СНИЛС">
                    <button type="button" class="btn btn-sm btn-outline-secondary mr-2" id="subjectsSearchBtn">
                        Поиск
                    </button>
                    <span class="text-muted small" id="subjectsPagingInfo">стр. 1 из 1</span>
                    <div class="btn-group btn-group-sm ml-2" role="group">
                        <button type="button" class="btn btn-outline-secondary" id="subjectsPrevBtn">&laquo;</button>
                        <button type="button" class="btn btn-outline-secondary" id="subjectsNextBtn">&raquo;</button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive mb-0">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th style="width:70px;">ID</th>
                            <th>ФИО</th>
                            <th style="width:120px;">Дата рождения</th>
                            <th style="width:130px;">ИНН</th>
                            <th style="width:130px;">СНИЛС</th>
                            <th style="width:130px;">Статус</th>
                            <th style="width:140px;">Актуальность</th>
                            <th style="width:140px;">Впервые</th>
                            <th style="width:140px;">Последний раз</th>
                            <th style="width:130px;">Дата списка</th>
                        </tr>
                        </thead>
                        <tbody id="subjectsBody">
                        <tr>
                            <td colspan="10" class="text-center text-muted">
                                Выберите файл в таблице выше
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {* Актуальный реестр клиентов по всем источникам *}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Актуальный реестр лиц из перечня (is_current = 1)</strong>
                <div class="d-flex align-items-center">
                    <input type="text"
                           id="currentSearch"
                           class="form-control form-control-sm mr-2"
                           placeholder="ФИО / ИНН / СНИЛС">
                    <button type="button" class="btn btn-sm btn-outline-secondary mr-2" id="currentSearchBtn">
                        Поиск
                    </button>
                    <span class="text-muted small" id="currentPagingInfo">стр. 1 из 1</span>
                    <div class="btn-group btn-group-sm ml-2" role="group">
                        <button type="button" class="btn btn-outline-secondary" id="currentPrevBtn">&laquo;</button>
                        <button type="button" class="btn btn-outline-secondary" id="currentNextBtn">&raquo;</button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive mb-0">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th style="width:70px;">ID</th>
                            <th>ФИО</th>
                            <th style="width:120px;">Дата рождения</th>
                            <th style="width:130px;">ИНН</th>
                            <th style="width:130px;">СНИЛС</th>
                            <th style="width:160px;">Источник</th>
                            <th style="width:130px;">Статус</th>
                            <th style="width:140px;">Впервые</th>
                            <th style="width:140px;">Последний раз</th>
                        </tr>
                        </thead>
                        <tbody id="currentSubjectsBody">
                        <tr>
                            <td colspan="9" class="text-center text-muted">
                                Загрузка актуального реестра...
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="report-config"
             data-report-uri="{$report_uri|escape:'html'}"
             data-default-source="{$default_source_code|escape:'html'}"
             style="display:none"></div>
    </div>

    {include file='footer.tpl'}
</div>

{capture name='page_scripts'}
{literal}
    <script>
        $(function () {
            var reportUri     = $('#report-config').data('report-uri');
            var defaultSource = $('#report-config').data('default-source') || '';

            var subjectsState = {
                fileId: null,
                page: 1,
                limit: 50,
                query: ''
            };

            var currentState = {
                page: 1,
                limit: 50,
                query: ''
            };

            function showMessage(type, text) {
                $('#uploadResult').html(
                    '<div class="alert alert-' + type + ' mb-0">' + text + '</div>'
                );
            }

            function escapeHtml(str) {
                if (str === null || str === undefined) return '';
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function getStatusBadge(status) {
                status = (status || '').toLowerCase();
                var cls  = 'status-badge status-uploaded';
                var text = status;

                switch (status) {
                    case 'uploaded':
                        cls  = 'status-badge status-uploaded';
                        text = 'Загружен';
                        break;
                    case 'scheduled':
                        cls  = 'status-badge status-queued';
                        text = 'В очереди';
                        break;
                    case 'in_progress':
                        cls  = 'status-badge status-processing';
                        text = 'Обработка';
                        break;
                    case 'completed':
                        cls  = 'status-badge status-done';
                        text = 'Готово';
                        break;
                    case 'failed':
                        cls  = 'status-badge status-error';
                        text = 'Ошибка';
                        break;
                }

                return '<span class="' + cls + '">' + text + '</span>';
            }

            function getRowClass(status) {
                status = (status || '').toLowerCase();
                if (status === 'in_progress' || status === 'scheduled') {
                    return 'row-processing';
                }
                if (status === 'completed') {
                    return 'row-done';
                }
                if (status === 'failed') {
                    return 'row-error';
                }
                return '';
            }

            function formatSize(size) {
                if (!size) return '-';
                size = parseInt(size, 10) || 0;
                if (size > 1024 * 1024) {
                    return (size / (1024 * 1024)).toFixed(1) + ' МБ';
                }
                if (size > 1024) {
                    return (size / 1024).toFixed(1) + ' КБ';
                }
                return size + ' Б';
            }

            function updateCurrentSourceLabel() {
                var $sel = $('#source_code');
                var text = $sel.find('option:selected').text() || $sel.val() || '—';
                $('#currentSourceLabel').text(text);
            }

            function renderIsCurrent(isCurrent) {
                isCurrent = parseInt(isCurrent || 0, 10);
                if (isCurrent === 1) {
                    return '<span class="badge badge-success">Актуален</span>';
                }
                return '<span class="badge badge-secondary">Не актуален</span>';
            }

            function renderIsTerrorist(isTerrorist) {
                isTerrorist = parseInt(isTerrorist || 0, 10);
                if (isTerrorist === 1) {
                    return '<span class="badge badge-danger">Террорист</span>';
                }
                return '<span class="badge badge-light">Не террорист</span>';
            }

            // Клики по табам источников
            $('.source-tabs').on('click', '.nav-link', function (e) {
                e.preventDefault();
                var code = $(this).data('source-code') || '';
                $('#source_code').val(code);
                $('.source-tabs .nav-link').removeClass('active');
                $(this).addClass('active');
                updateCurrentSourceLabel();
                loadFiles();
            });

            // Загрузка файла (AJAX)
            $('#uploadForm').on('submit', function (e) {
                e.preventDefault();

                var form     = this;
                var formData = new FormData(form);

                var sourceCode = $('#source_code').val();
                if (sourceCode !== undefined) {
                    formData.set('source_code', sourceCode);
                }

                $('#uploadResult').html(
                    '<div class="text-muted"><i class="fa fa-spinner fa-spin"></i> Загрузка файла...</div>'
                );

                $.ajax({
                    url: reportUri + '?action=uploadFile',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    cache: false,
                    dataType: 'json'
                }).done(function (resp) {
                    if (resp && resp.success) {
                        showMessage('success', resp.message || 'Файл успешно загружен');
                        loadFiles();
                    } else {
                        showMessage('danger', (resp && resp.message) || 'Ошибка при загрузке файла');
                    }
                }).fail(function () {
                    showMessage('danger', 'Ошибка сети при загрузке файла');
                });
            });

            // Загрузка списка файлов по источнику
            function loadFiles() {
                var sourceCode = $('#source_code').val();
                updateCurrentSourceLabel();

                $('#filesBody').html(
                    '<tr><td colspan="10" class="text-center text-muted">' +
                    '<i class="fa fa-spinner fa-spin"></i> Загрузка...' +
                    '</td></tr>'
                );

                $.post(reportUri + '?action=loadFiles', {
                    source_code: sourceCode
                }, function (resp) {
                    if (!(resp && resp.success && Array.isArray(resp.rows))) {
                        $('#filesBody').html(
                            '<tr><td colspan="10" class="text-center text-danger">Нет данных по выбранному источнику</td></tr>'
                        );
                        return;
                    }

                    if (!resp.rows.length) {
                        $('#filesBody').html(
                            '<tr><td colspan="10" class="text-center text-muted">Для этого источника файлы ещё не загружались</td></tr>'
                        );
                        return;
                    }

                    var html = '';
                    resp.rows.forEach(function (r) {
                        var subjectsTotal   = parseInt(r.subjects_total || 0, 10);
                        var subjectsCurrent = parseInt(r.subjects_current || 0, 10);

                        var listDate  = r.list_date || '—';
                        var errorText = r.error_message || '';
                        var errorHtml = errorText
                            ? '<small class="text-danger" title="' + escapeHtml(errorText) + '">' +
                            escapeHtml(errorText) +
                            '</small>'
                            : '';

                        var sizeStr = r.file_size ? formatSize(r.file_size) : '-';

                        var rowClass = getRowClass(r.status);

                        var fileLabel = '#' + (r.id || '') + ' — ' + (r.file_name || r.original_filename || '');

                        html += '<tr class="' + rowClass + ' js-file-row" ' +
                            'data-file-id="' + (r.id || '') + '" ' +
                            'data-file-label="' + escapeHtml(fileLabel) + '">' +

                            '<td>' + (r.id || '') + '</td>' +

                            '<td class="file-name-cell">' +
                            '<strong>' + escapeHtml(r.source_name || '') + '</strong>' +
                            '<small class="d-block text-monospace">(' + escapeHtml(r.source_code || '') + ')</small>' +
                            (sizeStr !== '-' ? '<small>Размер: ' + sizeStr + '</small>' : '') +
                            '</td>' +

                            '<td>' + escapeHtml(r.file_type || '') + '</td>' +

                            '<td>' + escapeHtml(r.file_name || r.original_filename || '') + '</td>' +

                            '<td>' + escapeHtml(listDate) + '</td>' +

                            '<td>' + getStatusBadge(r.status) + '</td>' +

                            '<td>' + (r.rows_processed != null ? r.rows_processed : 0) + '</td>' +

                            '<td>' + subjectsTotal + '</td>' +

                            '<td>' + subjectsCurrent + '</td>' +

                            '<td>' + errorHtml + '</td>' +
                            '</tr>';
                    });

                    $('#filesBody').html(html);
                }, 'json').fail(function () {
                    $('#filesBody').html(
                        '<tr><td colspan="10" class="text-center text-danger">Ошибка загрузки списка файлов</td></tr>'
                    );
                });
            }

            // Клик по строке файла -> загрузка клиентов
            $('#filesBody').on('click', '.js-file-row', function () {
                var $tr       = $(this);
                var fileId    = $tr.data('file-id');
                var fileLabel = $tr.data('file-label');

                if (!fileId) {
                    return;
                }

                $('#filesBody tr').removeClass('table-active');
                $tr.addClass('table-active');

                $('#subjectsSearch').val('');
                subjectsState.fileId = fileId;
                subjectsState.page   = 1;
                subjectsState.query  = '';

                loadFileSubjects(fileId, fileLabel);
            });

            // Загрузка клиентов по конкретному файлу (с пагинацией и поиском)
            function loadFileSubjects(fileId, fileLabel, pageOverride) {
                if (fileId) {
                    subjectsState.fileId = fileId;
                    subjectsState.page   = 1;
                }

                if (!subjectsState.fileId) {
                    $('#subjectsBody').html(
                        '<tr><td colspan="10" class="text-center text-muted">Файл не выбран</td></tr>'
                    );
                    return;
                }

                if (typeof pageOverride === 'number') {
                    subjectsState.page = pageOverride;
                }

                $('#currentFileLabel').text(fileLabel || ('#' + subjectsState.fileId));

                var q = $('#subjectsSearch').val() || '';
                subjectsState.query = q;

                $('#subjectsBody').html(
                    '<tr><td colspan="10" class="text-center text-muted">' +
                    '<i class="fa fa-spinner fa-spin"></i> Загрузка клиентов...' +
                    '</td></tr>'
                );

                $.post(reportUri + '?action=loadFileSubjects', {
                    file_id: subjectsState.fileId,
                    page: subjectsState.page,
                    limit: subjectsState.limit,
                    query: subjectsState.query
                }, function (resp) {
                    if (!(resp && resp.success && Array.isArray(resp.rows))) {
                        $('#subjectsBody').html(
                            '<tr><td colspan="10" class="text-center text-danger">Не удалось загрузить клиентов</td></tr>'
                        );
                        return;
                    }

                    var total = parseInt(resp.total || 0, 10);
                    var page  = parseInt(resp.page  || 1, 10);
                    var limit = parseInt(resp.limit || subjectsState.limit, 10);
                    subjectsState.page  = page;
                    subjectsState.limit = limit;

                    var pages = total > 0 ? Math.ceil(total / limit) : 1;
                    $('#subjectsPagingInfo').text('стр. ' + page + ' из ' + pages);

                    if (!resp.rows.length) {
                        $('#subjectsBody').html(
                            '<tr><td colspan="10" class="text-center text-muted">В этом файле нет субъектов</td></tr>'
                        );
                        $('#subjectsPrevBtn').prop('disabled', true);
                        $('#subjectsNextBtn').prop('disabled', true);
                        return;
                    }

                    var html = '';
                    resp.rows.forEach(function (s) {
                        var dob      = s.date_of_birth || '—';
                        var first    = s.first_seen_date || '—';
                        var last     = s.last_seen_date || '—';
                        var listDate = s.list_date || '—';

                        html += '<tr>' +
                            '<td>' + (s.id || '') + '</td>' +
                            '<td>' + escapeHtml(s.full_name || '') + '</td>' +
                            '<td>' + escapeHtml(dob) + '</td>' +
                            '<td>' + escapeHtml(s.inn || '—') + '</td>' +
                            '<td>' + escapeHtml(s.snils || '—') + '</td>' +
                            '<td>' + renderIsTerrorist(s.is_terrorist) + '</td>' +
                            '<td>' + renderIsCurrent(s.is_current) + '</td>' +
                            '<td>' + escapeHtml(first) + '</td>' +
                            '<td>' + escapeHtml(last) + '</td>' +
                            '<td>' + escapeHtml(listDate) + '</td>' +
                            '</tr>';
                    });

                    $('#subjectsBody').html(html);

                    $('#subjectsPrevBtn').prop('disabled', page <= 1);
                    $('#subjectsNextBtn').prop('disabled', page >= pages);
                }, 'json').fail(function () {
                    $('#subjectsBody').html(
                        '<tr><td colspan="10" class="text-center text-danger">Ошибка запроса при загрузке клиентов</td></tr>'
                    );
                });
            }

            // поиск по клиентам в файле
            $('#subjectsSearchBtn').on('click', function () {
                if (!subjectsState.fileId) return;
                subjectsState.page = 1;
                loadFileSubjects(null, null);
            });

            $('#subjectsSearch').on('keypress', function (e) {
                if (e.which === 13) {
                    $('#subjectsSearchBtn').click();
                }
            });

            // пагинация по клиентам в файле
            $('#subjectsPrevBtn').on('click', function () {
                if (subjectsState.page > 1) {
                    loadFileSubjects(null, null, subjectsState.page - 1);
                }
            });

            $('#subjectsNextBtn').on('click', function () {
                loadFileSubjects(null, null, subjectsState.page + 1);
            });

            // Актуальный реестр клиентов (is_current = 1) по всем источникам
            function loadCurrentSubjects(pageOverride) {
                if (typeof pageOverride === 'number') {
                    currentState.page = pageOverride;
                }

                var q = $('#currentSearch').val() || '';
                currentState.query = q;

                $('#currentSubjectsBody').html(
                    '<tr><td colspan="9" class="text-center text-muted">' +
                    '<i class="fa fa-spinner fa-spin"></i> Загрузка актуального реестра...' +
                    '</td></tr>'
                );

                $.post(reportUri + '?action=loadCurrentSubjects', {
                    page: currentState.page,
                    limit: currentState.limit,
                    query: currentState.query
                }, function (resp) {
                    if (!(resp && resp.success && Array.isArray(resp.rows))) {
                        $('#currentSubjectsBody').html(
                            '<tr><td colspan="9" class="text-center text-danger">Не удалось загрузить актуальный реестр</td></tr>'
                        );
                        return;
                    }

                    var total = parseInt(resp.total || 0, 10);
                    var page  = parseInt(resp.page  || 1, 10);
                    var limit = parseInt(resp.limit || currentState.limit, 10);
                    currentState.page  = page;
                    currentState.limit = limit;

                    var pages = total > 0 ? Math.ceil(total / limit) : 1;
                    $('#currentPagingInfo').text('стр. ' + page + ' из ' + pages);

                    if (!resp.rows.length) {
                        $('#currentSubjectsBody').html(
                            '<tr><td colspan="9" class="text-center text-muted">Нет актуальных клиентов</td></tr>'
                        );
                        $('#currentPrevBtn').prop('disabled', true);
                        $('#currentNextBtn').prop('disabled', true);
                        return;
                    }

                    var html = '';
                    resp.rows.forEach(function (s) {
                        var dob   = s.date_of_birth || '—';
                        var first = s.first_seen_date || '—';
                        var last  = s.last_seen_date || '—';

                        html += '<tr>' +
                            '<td>' + (s.id || '') + '</td>' +
                            '<td>' + escapeHtml(s.full_name || '') + '</td>' +
                            '<td>' + escapeHtml(dob) + '</td>' +
                            '<td>' + escapeHtml(s.inn || '—') + '</td>' +
                            '<td>' + escapeHtml(s.snils || '—') + '</td>' +
                            '<td>' +
                            escapeHtml(s.source_name || '') +
                            ' <small class="text-monospace">(' + escapeHtml(s.source_code || '') + ')</small>' +
                            '</td>' +
                            '<td>' + renderIsTerrorist(s.is_terrorist) + ' ' + renderIsCurrent(s.is_current) + '</td>' +
                            '<td>' + escapeHtml(first) + '</td>' +
                            '<td>' + escapeHtml(last) + '</td>' +
                            '</tr>';
                    });

                    $('#currentSubjectsBody').html(html);

                    $('#currentPrevBtn').prop('disabled', page <= 1);
                    $('#currentNextBtn').prop('disabled', page >= pages);
                }, 'json').fail(function () {
                    $('#currentSubjectsBody').html(
                        '<tr><td colspan="9" class="text-center text-danger">Ошибка запроса при загрузке актуального реестра</td></tr>'
                    );
                });
            }

            // поиск по актуальному реестру
            $('#currentSearchBtn').on('click', function () {
                currentState.page = 1;
                loadCurrentSubjects();
            });

            $('#currentSearch').on('keypress', function (e) {
                if (e.which === 13) {
                    $('#currentSearchBtn').click();
                }
            });

            // пагинация по актуальному реестру
            $('#currentPrevBtn').on('click', function () {
                if (currentState.page > 1) {
                    loadCurrentSubjects(currentState.page - 1);
                }
            });

            $('#currentNextBtn').on('click', function () {
                loadCurrentSubjects(currentState.page + 1);
            });

            // Смена источника в select
            $('#source_code').on('change', function () {
                var code = $(this).val();
                $('.source-tabs .nav-link').each(function () {
                    var $link = $(this);
                    if ($link.data('source-code') === code) {
                        $link.addClass('active');
                    } else {
                        $link.removeClass('active');
                    }
                });
                updateCurrentSourceLabel();
                loadFiles();
            });

            // Подгрузка при открытии страницы
            if ($('#source_code').val()) {
                loadFiles();
            }

            // Актуальный реестр грузим всегда
            loadCurrentSubjects();
        });
    </script>
{/literal}
{/capture}

{$smarty.capture.page_styles}
{$smarty.capture.page_scripts}
