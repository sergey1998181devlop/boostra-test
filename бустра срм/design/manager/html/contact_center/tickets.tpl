{$meta_title='Тикеты' scope=parent}

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

        .overdue-ticket {
            border-left: 4px solid #ED6A5A;
        }
        
        .has-comments-after-closing {
            border-left: 4px solid #01c0c8;
        }

        .agreement-ticket {
            border: 2px solid #FFA500;
            border-left: 4px solid #FFA500;
            background-color: rgba(255, 165, 0, 0.05)!important;
        }
        
        .agreement-ticket:hover {
            background-color: rgba(255, 165, 0, 0.1);
        }
        
        tr.highlighted-ticket {
            background-color: #ffbc34 !important;
            transition: background-color 0.3s ease !important;
            box-shadow: 0 0 6px rgba(255, 193, 7, 0.7);
        }
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
                <a href="tickets/create" class="btn hidden-sm-down btn-success js-open-add-modal mr-2">
                    <i class="mdi mdi-plus-circle"></i> Добавить
                </a>

                <form id="filter-form">
                    <button type="submit" class="btn btn-info mr-2">Отфильтровать</button>
                    <a href="#" class="btn btn-primary download-btn">
                        <i class="fas fa-file-excel"></i> Экспорт в Excel
                    </a>
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
                            <table id="tickets" class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th scope="col" class="resizable" data-column="id">
                                        <a href="#" class="sortable {if $sort == 'id'}asc{elseif $sort == '-id'}desc{/if}" data-sort="{($sort=='id') ? '-id' : 'id'}">
                                            #
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="client">
                                        <a href="#" class="sortable {if $sort == 'client'}asc{elseif $sort == '-client'}desc{/if}" data-sort="{($sort=='client') ? '-client' : 'client'}">
                                            Клиент
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="chanel">
                                        <a href="#" class="sortable {if $sort == 'chanel'}asc{elseif $sort == '-chanel'}desc{/if}" data-sort="{($sort=='chanel') ? '-chanel' : 'chanel'}">
                                            Канал
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="date">
                                        <a href="#" class="sortable {if $sort == 'date'}asc{elseif $sort == '-date'}desc{/if}" data-sort="{($sort=='date') ? '-date' : 'date'}">
                                            Дата
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="accepted_at">
                                        <a href="#" class="sortable {if $sort == 'accepted_at'}asc{elseif $sort == '-accepted_at'}desc{/if}" data-sort="{($sort=='accepted_at') ? '-accepted_at' : 'accepted_at'}">
                                            Принято в работу
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="parent_subject">
                                        <a href="#" class="sortable {if $sort == 'parent_subject'}asc{elseif $sort == '-parent_subject'}desc{/if}" data-sort="{($sort=='parent_subject') ? '-parent_subject' : 'parent_subject'}">
                                            Тип обращения
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="subject">
                                        <a href="#" class="sortable {if $sort == 'subject'}asc{elseif $sort == '-subject'}desc{/if}" data-sort="{($sort=='subject') ? '-subject' : 'subject'}">
                                            Тема
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="status">
                                        <a href="#" class="sortable {if $sort == 'status'}asc{elseif $sort == '-status'}desc{/if}" data-sort="{($sort=='status') ? '-status' : 'status'}">
                                            Статус проработки
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="priority">
                                        <a href="#" class="sortable {if $sort == 'priority'}asc{elseif $sort == '-priority'}desc{/if}" data-sort="{($sort=='priority') ? '-priority' : 'priority'}">
                                            Приоритет
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="is_repeat">
                                        <a href="#" class="sortable {if $sort == 'repeat'}asc{elseif $sort == '-repeat'}desc{/if}" data-sort="{($sort=='repeat') ? '-repeat' : 'repeat'}">
                                            Статус обращения
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="company">
                                        <a href="#" class="sortable {if $sort == 'company'}asc{elseif $sort == '-company'}desc{/if}" data-sort="{($sort=='company') ? '-company' : 'company'}">
                                            Компания
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="phone">
                                        Телефон
                                    </th>
                                    <th scope="col" class="resizable" data-column="phone">
                                        Регион
                                    </th>
                                    <th scope="col" class="resizable" data-column="overdue_days">
                                        Дни просрочки
                                    </th>
                                    <th scope="col" class="resizable" data-column="manager">
                                        <a href="#" class="sortable {if $sort == 'manager'}asc{elseif $sort == '-manager'}desc{/if}" data-sort="{($sort=='manager') ? '-manager' : 'manager'}">
                                            Исполнитель
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="initiator">
                                        <a href="#" class="sortable {if $sort == 'initiator'}asc{elseif $sort == '-initiator'}desc{/if}" data-sort="{($sort=='initiator') ? '-initiator' : 'initiator'}">
                                            Инициатор
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="responsible_person_name">
                                        <a href="#" class="sortable {if $sort == 'responsible_person_name'}asc{elseif $sort == '-responsible_person_name'}desc{/if}" data-sort="{($sort=='responsible_person_name') ? '-responsible_person_name' : 'responsible_person_name'}">
                                            Ответственный по договору
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="responsible_group_name">
                                        <a href="#" class="sortable {if $sort == 'responsible_group_name'}asc{elseif $sort == '-group'}desc{/if}" data-sort="{($sort=='responsible_group_name') ? '-responsible_group_name' : 'responsible_group_name'}">
                                            Группа
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="description">Описание</th>
                                    <th scope="col" class="resizable" data-column="comment">Результат отработки</th>
                                </tr>
                                <tr id="filter-row">
                                    <th class>
                                        <input type="hidden" name="sort" value="{$sort|escape}">
                                        <input type="text" name="id" value="{$filters['id']}"
                                               class="form-control input-sm">
                                    </th>
                                    <th>
                                        <input type="text" name="client_name" value="{$filters['client_name']}"
                                               class="form-control input-sm">
                                    </th>
                                    <th>
                                        <select name="chanel_id" class="form-control">
                                            <option value="">Выберите канал</option>
                                            {foreach $channels as $channel}
                                                <option value="{$channel->id}" {if $channel->id == $filters['chanel_id']}selected{/if}>{$channel->name|escape}</option>
                                            {/foreach}
                                        </select>
                                    </th>
                                    <th>
                                        <div class="input-group">
                                            <input type="text" name="date_range" id="date_range" class="form-control"
                                                   placeholder="Выберите период" value="{$filters['date_range']|escape}"
                                                   autocomplete="off">
                                            <div class="input-group-append">
                                                    <span class="input-group-text">
                                                        <i class="far fa-calendar"></i>
                                                    </span>
                                            </div>
                                        </div>
                                    </th>
                                    <th>
                                        <div class="input-group">
                                            <input type="text" name="accepted_date_range" id="accepted_date_range" class="form-control"
                                                   placeholder="Выберите период" value="{$filters['accepted_date_range']|escape}"
                                                   autocomplete="off">
                                            <div class="input-group-append">
                                                    <span class="input-group-text">
                                                        <i class="far fa-calendar"></i>
                                                    </span>
                                            </div>
                                        </div>
                                    </th>
                                    <th>
                                        <select name="subject_parent_id" class="form-control">
                                            <option value="">Выберите тип обращения</option>
                                            {foreach $subjects.main as $key => $subject}
                                                <option value="{$key}" {if $key == $filters['subject_parent_id']}selected{/if}>{$subject|escape}</option>
                                            {/foreach}
                                        </select>
                                    </th>
                                    <th>
                                        <select name="subject_id" class="form-control">
                                            <option value="">Выберите тему</option>
                                            {foreach $subjects.child as $key => $subject}
                                                <option value="{$key}" {if $key == $filters['subject_parent_id']}selected{/if}>{$subject|escape}</option>
                                            {/foreach}
                                        </select>
                                    </th>
                                    <th>
                                        <select name="status_id" class="form-control">
                                            <option value="">Выберите статус</option>
                                            {foreach $statuses as $status}
                                                <option value="{$status->id}" {if $status->id == $filters['status_id']}selected{/if}>{$status->name|escape}</option>
                                            {/foreach}
                                        </select>
                                    </th>
                                    <th>
                                        <select name="priority_id" class="form-control">
                                            <option value="">Выберите приоритет</option>
                                            {foreach $priorities as $priority}
                                                <option value="{$priority->id}" {if $priority->id == $filters['priority_id']}selected{/if}>{$priority->name|escape}</option>
                                            {/foreach}
                                        </select>
                                    </th>
                                    <th></th>
                                    <th>
                                        <select name="company_id" class="form-control">
                                            <option value="">Выберите компанию</option>
                                            {foreach $companies as $company}
                                                <option value="{$company->id}" {if $company->id == $filters['company_id']}selected{/if}>{$company->name|escape}</option>
                                            {/foreach}
                                        </select>
                                    </th>
                                    <th>
                                        <input type="text" name="phone" value="{$filters['phone']}"
                                               class="form-control input-sm">
                                    </th>
                                    <th></th>
                                    <th>
                                        <select id="overdue_range" name="overdue_range" class="form-control">
                                            <option value=""></option>
                                        </select>
                                    </th>
                                    <th>
                                        <select name="manager_id" id="manager_id" class="form-control select2-manager">
                                            <option value="">Выберите менеджера</option>
                                            <option value='{$manager_data->id}' {if $manager_data->id == $filters['manager_id']}selected{/if}>{$manager_data->name_1c|escape}</option>
                                            {foreach $managers AS $manag}
                                                <option value='{$manag->id}' {if $manag->id == $filters['manager_id']}selected{/if}>{$manag->name|escape}</option>
                                            {/foreach}
                                        </select>
                                    </th>
                                    <th>
                                        <select name="initiator_id" id="initiator_id" class="form-control select2-initiator">
                                            <option value="">Выберите инициатора</option>
                                            <option value='{$initiator_data->id}' {if $initiator_data->id == $filters['initiator_id']}selected{/if}>{$initiator_data->name_1c|escape}</option>
                                            {foreach $initiators AS $initiator}
                                                <option value='{$initiator->id}' {if $initiator->id == $filters['initiator_id']}selected{/if}>{$initiator->name|escape}</option>
                                            {/foreach}
                                        </select>
                                    </th>
                                    <th>
                                        <select name="responsible_person_name" id="responsible_person_name" class="form-control select2-responsible">
                                            <option value="">Выберите ответственного</option>
                                            {foreach $responsible_persons as $uid => $name}
                                                <option value="{$name|escape}" {if $name == $filters['responsible_person_name']}selected{/if}>
                                                    {$name|escape}
                                                </option>
                                            {/foreach}
                                        </select>
                                    </th>

                                    <th>
                                        <select name="responsible_group_name" class="form-control">
                                            <option value="">Выберите группу</option>
                                            {foreach $responsible_groups as $uid => $name}
                                                <option value="{$name|escape}" {if $name == $filters['responsible_group_name']}selected{/if}>
                                                    {$name|escape}
                                                </option>
                                            {/foreach}
                                        </select>
                                    </th>
                                    <th>
                                        <input type="text" name="description" value="{$filters['description']}"
                                               class="form-control input-sm">
                                    </th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach $items as $ticket}
                                    <tr class="
                                        {if $ticket->is_overdue}overdue-ticket{/if}
                                        {if $ticket->data.has_comments_after_closing}has-comments-after-closing{/if}
                                        {if $ticket->data.agreement_copy}agreement-ticket{/if}
                                        {if (isset($highlight_ticket_id) && $highlight_ticket_id == $ticket->id) || (!empty($ticket->is_highlighted) && $ticket->is_highlighted == 1)} highlighted-ticket{/if}
                                        ">
                                        <td>
                                            <a href="tickets/{$ticket->id}" target="_blank">
                                                {$ticket->id|escape}
                                            </a>
                                        </td>
                                        <td>
                                            {if $ticket->client_id}
                                                <a href="client/{$ticket->client_id}" target="_blank">
                                                    {$ticket->client_name|escape}
                                                </a>
                                            {else}
                                                {$ticket->client_name|escape}
                                            {/if}
                                        </td>
                                        <td>{$ticket->chanel_name|escape}</td>
                                        <td>{$ticket->created_at|date_format:'%d.%m.%Y %H:%M'}</td>
                                        <td>{if $ticket->accepted_at}{$ticket->accepted_at|date_format:'%d.%m.%Y %H:%M'}{/if}</td>
                                        <td>{$ticket->subject_parent_name|escape}</td>
                                        <td>{$ticket->ticket_subject|escape}</td>
                                        <td><span class="badge badge-pill"
                                                  style="background-color: {$ticket->status_color}">{$ticket->status_name}</span>
                                        </td>
                                        <td><span class="badge badge-pill"
                                                  style="background-color: {$ticket->priority_color}"
                                                  title="{$ticket->priority_name}">{$ticket->priority_name}</span></td>
                                        <td>
                                            {if $ticket->is_repeat}
                                                <span class="badge badge-pill badge-danger">Повторное</span>
                                            {else}
                                                <span class="badge badge-pill badge-primary">Первичное</span>
                                            {/if}
                                        </td>
                                        <td>{$ticket->company_name|escape}</td>
                                        <td>{$ticket->client_phone|replace:'false':''|escape}</td>
                                        <td>{$ticket->client_region|escape}</td>
                                        <td>{if isset($ticket->data.overdue_days)}{$ticket->data.overdue_days}{else}нет информации{/if}</td>
                                        <td>{$ticket->name_manager|escape}</td>
                                        <td>{$ticket->name_initiator|escape}</td>
                                        <td>{$ticket->responsible_person_name|escape}</td>
                                        <td>{$ticket->responsible_group_name|escape}</td>
                                        <td class="text-clamp">{$ticket->description|escape|make_urls_clickable}</td>
                                        <td>{$ticket->final_comment|default:$ticket->last_comment|escape|make_urls_clickable}</td>
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


{capture name='page_scripts'}
    <script src="design/manager/assets/plugins/moment/moment.js"></script>
    <script src="design/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    <script type="text/javascript" src="design/{$settings->theme|escape}/js/table-manager.js"></script>
    <script src="design/manager/assets/plugins/select2/dist/js/select2.full.min.js"></script>

    <script>
        $(function() {
            new TableManager({
                tableId: 'tickets',
                filterFormId: 'filter-form',
                storageKey: 'columnWidthsInTicketsPage',
                url: 'tickets',
                autoUpdateInterval: 120000, // Авто-обновление каждые 2 минуты
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

            const table = document.getElementById('tickets');
            const dummy = topScroll.querySelector('.dummy');
            dummy.style.width = table.scrollWidth + 'px';
        })();

        {literal}
        $(document).ready(function() {
            // Инициализация Select2 для менеджеров
            $('.select2-manager').select2({
                placeholder: "Выберите или найдите менеджера",
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function() {
                        return "Менеджер не найден";
                    },
                    searching: function() {
                        return "Поиск...";
                    },
                    inputTooShort: function(args) {
                        return "Введите хотя бы " + args.minimum + " символа для поиска";
                    }
                },
                minimumInputLength: 0,
                matcher: function(params, data) {
                    // Если нет поискового запроса, возвращаем все элементы
                    if ($.trim(params.term) === '') {
                        return data;
                    }

                    // Если у элемента нет текста, пропускаем
                    if (typeof data.text === 'undefined') {
                        return null;
                    }

                    // Поиск по ФИО (регистронезависимый)
                    var searchTerm = params.term.toLowerCase();
                    var dataText = data.text.toLowerCase();

                    // Разбиваем поисковый запрос на слова
                    var searchWords = searchTerm.split(/\s+/);

                    // Проверяем, что все слова из поиска присутствуют в имени
                    var allWordsFound = searchWords.every(function(word) {
                        return dataText.indexOf(word) > -1;
                    });

                    if (allWordsFound) {
                        return data;
                    }

                    return null;
                }
            });

            // Инициализация Select2 для инициаторов
            $('.select2-initiator').select2({
                placeholder: "Выберите или найдите инициатора",
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function() {
                        return "Инициатор не найден";
                    },
                    searching: function() {
                        return "Поиск...";
                    },
                    inputTooShort: function(args) {
                        return "Введите хотя бы " + args.minimum + " символа для поиска";
                    }
                },
                minimumInputLength: 0,
                matcher: function(params, data) {
                    // Если нет поискового запроса, возвращаем все элементы
                    if ($.trim(params.term) === '') {
                        return data;
                    }

                    // Если у элемента нет текста, пропускаем
                    if (typeof data.text === 'undefined') {
                        return null;
                    }

                    // Поиск по ФИО (регистронезависимый)
                    var searchTerm = params.term.toLowerCase();
                    var dataText = data.text.toLowerCase();

                    // Разбиваем поисковый запрос на слова
                    var searchWords = searchTerm.split(/\s+/);

                    // Проверяем, что все слова из поиска присутствуют в имени
                    var allWordsFound = searchWords.every(function(word) {
                        return dataText.indexOf(word) > -1;
                    });

                    if (allWordsFound) {
                        return data;
                    }

                    return null;
                }
            });

            // Инициализация Select2 для ответственных лиц
            $('.select2-responsible').select2({
                placeholder: "Выберите или найдите ответственного",
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function() {
                        return "Ответственный не найден";
                    },
                    searching: function() {
                        return "Поиск...";
                    },
                    inputTooShort: function(args) {
                        return "Введите хотя бы " + args.minimum + " символа для поиска";
                    }
                },
                minimumInputLength: 0,
                matcher: function(params, data) {
                    // Если нет поискового запроса, возвращаем все элементы
                    if ($.trim(params.term) === '') {
                        return data;
                    }

                    // Если у элемента нет текста, пропускаем
                    if (typeof data.text === 'undefined') {
                        return null;
                    }

                    // Поиск по ФИО (регистронезависимый)
                    var searchTerm = params.term.toLowerCase();
                    var dataText = data.text.toLowerCase();

                    // Разбиваем поисковый запрос на слова
                    var searchWords = searchTerm.split(/\s+/);

                    // Проверяем, что все слова из поиска присутствуют в имени
                    var allWordsFound = searchWords.every(function(word) {
                        return dataText.indexOf(word) > -1;
                    });

                    if (allWordsFound) {
                        return data;
                    }

                    return null;
                }
            });

            // Select2 для просрочки
            const overdueOptions = [
                { id: "1", text: "До 8 дней включительно" },
                { id: "2", text: "С 9 по 30 день включительно" },
                { id: "3", text: "С 31 дня и далее" }
            ];

            // Инициализируем Select2 для просрочки
            $('#overdue_range').select2({
                placeholder: "Введите или выберите срок",
                allowClear: true,
                data: overdueOptions,
                tags: true,
                width: '150px',
                createTag: function(params) {
                    var term = $.trim(params.term);

                    if (term === '') {
                        return null;
                    }

                    // Проверяем, соответствует ли введенное значение формату диапазона
                    if (/^-?\d+\s*-\s*-?\d+$/.test(term)) {
                        return {
                            id: term,
                            text: term + ' дней',
                            newTag: true
                        };
                    }

                    return null;
                }
            });

            // Устанавливаем значение для overdue_range, если оно есть в фильтрах
            {/literal}
            {if $filters['overdue_range']}
            $('#overdue_range').val('{$filters['overdue_range']}').trigger('change');
            {else}
            $('#overdue_range').val(null).trigger('change');
            {/if}
            {literal}

            // Инициализация daterangepicker для фильтра по дате
            $('#date_range').daterangepicker({
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

            // Обработчики для корректного отображения выбранных дат и обновления таблицы
            $('#date_range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD.MM.YYYY') + ' - ' + picker.endDate.format('DD.MM.YYYY'));
                $('#filter-form').submit();
            });

            $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $('#filter-form').submit();
            });

            $('#accepted_date_range').daterangepicker({
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

            $('#accepted_date_range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD.MM.YYYY') + ' - ' + picker.endDate.format('DD.MM.YYYY'));
                $('#filter-form').submit();
            });

            $('#accepted_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $('#filter-form').submit();
            });

        });
        {/literal}
    </script>
{/capture}