{$meta_title='Запросы ЦБ' scope=parent}

{capture name='page_styles'}
    <link href="design/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">
    <link href="design/manager/assets/plugins/select2/dist/css/select2.min.css" rel="stylesheet"/>
    <style>
        @font-face {
            font-family: 'Font Awesome 5 Free';
            font-style: normal;
            font-weight: 400;
            src: url("/design/manager/scss/icons/font-awesome/webfonts/fa-regular-400.eot");
            src: url("/design/manager/scss/icons/font-awesome/webfonts/fa-regular-400.eot?#iefix") format("embedded-opentype"), url("/design/manager/scss/icons/font-awesome/webfonts/fa-regular-400.woff2") format("woff2"), url("/design/manager/scss/icons/font-awesome/webfonts/fa-regular-400.woff") format("woff"), url("/design/manager/scss/icons/font-awesome/webfonts/fa-regular-400.ttf") format("truetype"), url("/design/manager/scss/icons/font-awesome/webfonts/fa-regular-400.svg#fontawesome") format("svg");
        }

        th {
            border: 1px solid rgba(0, 0, 0, 0.4) !important;
        }

        thead tr:first-of-type {
            background-color: #1a1f27;
        }

        thead tr:nth-of-type(2) {
            background-color: #383f48;
        }

        .sortable::after {
            position: relative;
        }

        .sortable::after {
            font-family: 'Font Awesome 5 Free';
            content: '\f0dc';
            font-weight: 900;
            margin-left: 5px;
        }

        .asc::after {
            content: '\f0de';
            font-family: 'Font Awesome 5 Free';

        }

        .desc::after {
            content: '\f0dd';
            font-family: 'Font Awesome 5 Free';
        }

        th a, td a {
            color: inherit;
            font-weight: 500;
            text-decoration: none;
        }

        th a:hover, td a:hover {
            color: #ababab;
            text-decoration: none;
        }

        .scrollbar-top {
            overflow-x: auto;
            overflow-y: hidden;
        }
        .scrollbar-top .dummy {
            height: 10px;
        }
        .scrollbar-top::-webkit-scrollbar,
        .table-responsive::-webkit-scrollbar {
            height: 10px;
        }
        .scrollbar-top::-webkit-scrollbar-track,
        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        .scrollbar-top::-webkit-scrollbar-thumb,
        .table-responsive::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        .scrollbar-top::-webkit-scrollbar-thumb:hover,
        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        .scrollbar-top,
        .table-responsive {
            scrollbar-width: thin;
            scrollbar-color: #888 #f1f1f1;
        }

        .limited-text {
            max-width: 250px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            cursor: pointer;
        }

        #cb-requests th, #cb-requests td { white-space: nowrap; }
        [data-column="id"] { min-width: 50px; }
        [data-column="request_number"] { min-width: 120px; }
        [data-column="organization"] { min-width: 140px; }
        [data-column="received_at"] { min-width: 130px; }
        [data-column="client_fio"] { min-width: 160px; }
        [data-column="client_birth_date"] { min-width: 110px; }
        [data-column="files"] { min-width: 60px; }
        [data-column="phone"] { min-width: 120px; }
        [data-column="subject"] { min-width: 130px; }
        [data-column="status"] { min-width: 90px; }
        [data-column="request_after_opr"] { min-width: 180px; }
        [data-column="response_deadline"] { min-width: 120px; }
        [data-column="comment_opr"] { min-width: 180px; }
        [data-column="comment_okk"] { min-width: 180px; }
        [data-column="measures"] { min-width: 180px; }
        [data-column="comment_lawyers"] { min-width: 180px; }
    </style>
{/capture}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-tdemecolor mb-0 mt-0">{$meta_title}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">{$meta_title}</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 d-flex align-self-center align-content-end justify-content-end">
                <form id="filter-form">
                    <button type="submit" class="btn btn-info mr-2">Отфильтровать</button>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="scrollbar-top" id="scrollbar-top">
                            <div class="dummy"></div>
                        </div>
                        <div class="table-responsive" id="table-container">
                            <table id="cb-requests" class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th scope="col" class="resizable" data-column="id">
                                        <a href="#" class="sortable {if $sort == 'id'}asc{elseif $sort == '-id'}desc{/if}" data-sort="{($sort=='id') ? '-id' : 'id'}">
                                            #
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="request_number">
                                        <a href="#" class="sortable {if $sort == 'request_number'}asc{elseif $sort == '-request_number'}desc{/if}" data-sort="{($sort=='request_number') ? '-request_number' : 'request_number'}">
                                            Номер запроса
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="organization">
                                        <a href="#" class="sortable {if $sort == 'organization'}asc{elseif $sort == '-organization'}desc{/if}" data-sort="{($sort=='organization') ? '-organization' : 'organization'}">
                                            Юридическое лицо
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="received_at">
                                        <a href="#" class="sortable {if $sort == 'received_at'}asc{elseif $sort == '-received_at'}desc{/if}" data-sort="{($sort=='received_at') ? '-received_at' : 'received_at'}">
                                            Дата поступления
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="client_fio">
                                        <a href="#" class="sortable {if $sort == 'client_fio'}asc{elseif $sort == '-client_fio'}desc{/if}" data-sort="{($sort=='client_fio') ? '-client_fio' : 'client_fio'}">
                                            ФИО
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="client_birth_date">
                                        Дата рождения
                                    </th>
                                    <th scope="col" class="resizable" data-column="files">
                                        Файлы
                                    </th>
                                    <th scope="col" class="resizable" data-column="phone">
                                        Телефон
                                    </th>
                                    <th scope="col" class="resizable" data-column="subject">
                                        <a href="#" class="sortable {if $sort == 'subject'}asc{elseif $sort == '-subject'}desc{/if}" data-sort="{($sort=='subject') ? '-subject' : 'subject'}">
                                            Тема
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="status">
                                        Статус
                                    </th>
                                    <th scope="col" class="resizable" data-column="request_after_opr">
                                        Запрос после проработки ОПР
                                    </th>
                                    <th scope="col" class="resizable" data-column="response_deadline">
                                        <a href="#" class="sortable {if $sort == 'response_deadline'}asc{elseif $sort == '-response_deadline'}desc{/if}" data-sort="{($sort=='response_deadline') ? '-response_deadline' : 'response_deadline'}">
                                            Срок ответа
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="comment_opr">
                                        Комментарий ОПР
                                    </th>
                                    <th scope="col" class="resizable" data-column="comment_okk">
                                        Комментарий ОКК
                                    </th>
                                    <th scope="col" class="resizable" data-column="measures">
                                        Мероприятия
                                    </th>
                                    <th scope="col" class="resizable" data-column="comment_lawyers">
                                        Комментарий юристы
                                    </th>
                                </tr>
                                <tr id="filter-row">
                                    <th>
                                        <input type="hidden" name="sort" value="{$sort|escape}">
                                        <input type="text" name="id" value="{$filters['id']}"
                                               class="form-control input-sm">
                                    </th>
                                    <th>
                                        <input type="text" name="request_number" value="{$filters['request_number']}"
                                               class="form-control input-sm">
                                    </th>
                                    <th>
                                        <select name="organization_id" class="form-control">
                                            <option value="">Выберите юр. лицо</option>
                                            {foreach $legal_entities as $entity}
                                                <option value="{$entity->id}" {if $entity->id == $filters['organization_id']}selected{/if}>{$entity->name|escape}</option>
                                            {/foreach}
                                        </select>
                                    </th>
                                    <th>
                                        <div class="input-group">
                                            <input type="text" name="received_date_range" id="received_date_range" class="form-control"
                                                   placeholder="Выберите период" value="{$filters['received_date_range']|escape}"
                                                   autocomplete="off">
                                            <div class="input-group-append">
                                                    <span class="input-group-text">
                                                        <i class="far fa-calendar"></i>
                                                    </span>
                                            </div>
                                        </div>
                                    </th>
                                    <th>
                                        <input type="text" name="client_fio" value="{$filters['client_fio']}"
                                               class="form-control input-sm">
                                    </th>
                                    <th>
                                        <input type="text" name="client_birth_date" value="{$filters['client_birth_date']|escape}"
                                               class="form-control input-sm" placeholder="дд.мм.гггг">
                                    </th>
                                    <th></th>
                                    <th>
                                        <input type="text" name="client_phone" value="{$filters['client_phone']}"
                                               class="form-control input-sm">
                                    </th>
                                    <th>
                                        <select name="subject_id" class="form-control">
                                            <option value="">Выберите тему</option>
                                            {foreach $subjects as $subject}
                                                <option value="{$subject->id}" {if $subject->id == $filters['subject_id']}selected{/if}>{$subject->name|escape}</option>
                                            {/foreach}
                                        </select>
                                    </th>
                                    <th>
                                        <select name="status" class="form-control">
                                            <option value="">Выберите статус</option>
                                            <option value="new" {if $filters['status'] == 'new'}selected{/if}>Новый</option>
                                            <option value="opr" {if $filters['status'] == 'opr'}selected{/if}>Обработан ОПР</option>
                                            <option value="okk" {if $filters['status'] == 'okk'}selected{/if}>Обработан ОКК</option>
                                            <option value="sent" {if $filters['status'] == 'sent'}selected{/if}>Направлен ответ</option>
                                        </select>
                                    </th>
                                    <th></th>
                                    <th>
                                        <div class="input-group">
                                            <input type="text" name="deadline_range" id="deadline_range" class="form-control"
                                                   placeholder="Выберите период" value="{$filters['deadline_range']|escape}"
                                                   autocomplete="off">
                                            <div class="input-group-append">
                                                    <span class="input-group-text">
                                                        <i class="far fa-calendar"></i>
                                                    </span>
                                            </div>
                                        </div>
                                    </th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach $items as $item}
                                    <tr>
                                        <td><a href="cb-requests/{$item.id}" target="_blank">{$item.id}</a></td>
                                        <td>{$item.request_number|escape}</td>
                                        <td>{$item.organization_name|escape}</td>
                                        <td>{if $item.received_at}{$item.received_at|date_format:'%d.%m.%Y'}{/if}</td>
                                        <td>{$item.client_fio|escape}</td>
                                        <td>{if $item.client_birth_date}{$item.client_birth_date|date_format:'%d.%m.%Y'}{/if}</td>
                                        <td class="text-center">
                                            {if $item.files|@count > 0}
                                                <a href="javascript:void(0);" data-files="{$item.files|@json_encode|escape:'html'}" onclick="openFilesModal(this)" title="Показать файлы">
                                                    <i class="fas fa-paperclip text-info"></i>
                                                </a>
                                            {/if}
                                        </td>
                                        <td>{$item.client_phone|escape}</td>
                                        <td>{$item.subject_name|escape}</td>
                                        <td>
                                            {if $item.status_opr}<span class="badge badge-success">ОПР</span>{/if}
                                            {if $item.status_okk}<span class="badge badge-info">ОКК</span>{/if}
                                            {if $item.status_sent}<span class="badge badge-primary">Ответ</span>{/if}
                                            {if !$item.status_opr && !$item.status_okk && !$item.status_sent}<span class="badge badge-warning">Новый</span>{/if}
                                        </td>
                                        <td><div class="limited-text" onclick="openTextModal(this, 'Запрос после проработки ОПР')">{$item.request_after_opr|escape}</div></td>
                                        <td>{if $item.response_deadline}{$item.response_deadline|date_format:'%d.%m.%Y'}{/if}</td>
                                        <td><div class="limited-text" onclick="openTextModal(this, 'Комментарий ОПР')">{$item.comment_opr|escape}</div></td>
                                        <td><div class="limited-text" onclick="openTextModal(this, 'Комментарий ОКК')">{$item.comment_okk|escape}</div></td>
                                        <td><div class="limited-text" onclick="openTextModal(this, 'Мероприятия')">{$item.measures|escape}</div></td>
                                        <td><div class="limited-text" onclick="openTextModal(this, 'Комментарий юристы')">{$item.comment_lawyers|escape}</div></td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                        </div>

                        {include file='html_blocks/table_pagination.tpl'}
                    </div>
                </div>
            </div>
        </div>
    </div>
    {include file='footer.tpl'}
</div>


<!-- Модальное окно для полного текста -->
<div class="modal fade" id="textModal" tabindex="-1" role="dialog" aria-labelledby="textModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textModalLabel">Содержимое</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="textModalBody" style="max-height: 50vh; overflow-y: auto; white-space: pre-wrap; color: #fff;"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="filesModal" tabindex="-1" role="dialog" aria-labelledby="filesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filesModalLabel">Прикрепленные файлы</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="filesModalBody" style="max-height: 50vh; overflow-y: auto;"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

{capture name='page_scripts'}
    <script src="design/manager/assets/plugins/moment/moment.js"></script>
    <script src="design/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    <script type="text/javascript" src="design/{$settings->theme|escape}/js/table-manager.js"></script>
    <script src="design/manager/assets/plugins/select2/dist/js/select2.full.min.js"></script>

    <script>
        $(function() {
            new TableManager({
                tableId: 'cb-requests',
                filterFormId: 'filter-form',
                storageKey: 'columnWidthsInCbRequestsPage',
                url: 'cb-requests',
            });
        });

        (function() {
            const topScroll = document.getElementById('scrollbar-top');
            const tableContainer = document.getElementById('table-container');

            topScroll.addEventListener('scroll', function() {
                tableContainer.scrollLeft = topScroll.scrollLeft;
            });

            tableContainer.addEventListener('scroll', function() {
                topScroll.scrollLeft = tableContainer.scrollLeft;
            });

            const table = document.getElementById('cb-requests');
            const dummy = topScroll.querySelector('.dummy');
            dummy.style.width = table.scrollWidth + 'px';
        })();

        function openTextModal(element, title) {
            var text = element.textContent || element.innerText;
            if (!text.trim()) return;
            document.getElementById('textModalLabel').innerText = title;
            document.getElementById('textModalBody').innerText = text;
            $('#textModal').modal('show');
        }

        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function openFilesModal(trigger) {
            var raw = trigger ? trigger.getAttribute('data-files') : '';
            if (!raw) {
                return;
            }

            var files = [];
            try {
                files = JSON.parse(raw);
            } catch (e) {
                return;
            }
            if (!Array.isArray(files) || files.length === 0) {
                return;
            }

            var html = '<ul class="list-group">';
            files.forEach(function(file, index) {
                var name = escapeHtml(file && file.name ? file.name : ('Файл ' + (index + 1)));
                var url = file && file.url ? String(file.url) : '';

                if (url) {
                    html += '<li class="list-group-item d-flex justify-content-between align-items-center">';
                    html += '<span class="text-break mr-2">' + name + '</span>';
                    html += '<a href="' + escapeHtml(url) + '" target="_blank" class="btn btn-sm btn-outline-info">Скачать</a>';
                    html += '</li>';
                } else {
                    html += '<li class="list-group-item d-flex justify-content-between align-items-center text-muted">';
                    html += '<span class="text-break mr-2">' + name + '</span>';
                    html += '<span class="badge badge-secondary">Недоступен</span>';
                    html += '</li>';
                }
            });
            html += '</ul>';

            document.getElementById('filesModalBody').innerHTML = html;
            $('#filesModal').modal('show');
        }

        {literal}
        $(document).ready(function() {
            // Инициализация daterangepicker для фильтра по дате поступления
            $('#received_date_range').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    format: 'DD.MM.YYYY',
                    separator: ' - ',
                    applyLabel: 'Применить',
                    cancelLabel: 'Очистить',
                    fromLabel: 'С',
                    toLabel: 'По',
                    customRangeLabel: 'Выбрать период',
                    weekLabel: 'Нед',
                    daysOfWeek: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
                    monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
                        'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
                    firstDay: 1
                }
            });

            $('#received_date_range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD.MM.YYYY') + ' - ' + picker.endDate.format('DD.MM.YYYY'));
                $('#filter-form').submit();
            });

            $('#received_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $('#filter-form').submit();
            });

            // Инициализация daterangepicker для фильтра по сроку ответа
            $('#deadline_range').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    format: 'DD.MM.YYYY',
                    separator: ' - ',
                    applyLabel: 'Применить',
                    cancelLabel: 'Очистить',
                    fromLabel: 'С',
                    toLabel: 'По',
                    customRangeLabel: 'Выбрать период',
                    weekLabel: 'Нед',
                    daysOfWeek: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
                    monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
                        'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
                    firstDay: 1
                }
            });

            $('#deadline_range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD.MM.YYYY') + ' - ' + picker.endDate.format('DD.MM.YYYY'));
                $('#filter-form').submit();
            });

            $('#deadline_range').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $('#filter-form').submit();
            });
        });
        {/literal}
    </script>
{/capture}
