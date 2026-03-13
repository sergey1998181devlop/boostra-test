{$meta_title="Тикет #`$ticket->id`" scope=parent}

{assign var='statusClass' value=''}

{capture name='page_styles'}
    <link href="design/manager/assets/plugins/select2/dist/css/select2.css" rel="stylesheet" type="text/css" />
    <style>
        /* Основные стили */
        body {
            background-color: #1a1f2d;
            color: #ffffff;
        }

        /* История звонков */
        .call-item {
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: .25rem;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .call-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        /* Аудио плеер */
        .audio-player audio {
            width: 100%;
            margin-top: 1rem;
        }

        /* Вспомогательные классы */
        .text-light-muted {
            color: #a8b2bd;
        }

        .comments-container::-webkit-scrollbar {
            width: 6px;
        }

        .comments-container::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
        }

        .comments-container::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }

        .comments-container::-webkit-scrollbar-thumb:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }

        /* Анимация появления новых комментариев */
        .comment-item {
            opacity: 0;
            transform: translateY(20px);
            animation: commentFadeIn 0.3s ease forwards;
        }

        @keyframes commentFadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .comment-item:hover .comment-text {
            background-color: rgba(255, 255, 255, 0.1) !important;
            transition: background-color 0.2s ease;
        }

        .ticket-timeline {
            list-style: none;
            padding: 0;
            margin: 0;
            position: relative;
        }
        
        .ticket-timeline:before {
            content: '';
            position: absolute;
            top: 0;
            width: 2px;
            background: #e9ecef;
            left: 15px;
        }

        .ticket-timeline li {
            position: relative;
            margin: 0;
            padding: 0 0 20px 40px;
        }

        .ticket-timeline li:not(:last-child)::after {
            content: '';
            position: absolute;
            left: 15px;
            top: 20px;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }
        
        .timeline-icon {
            width: 20px;
            height: 20px;
            background-color: #007bff;
            border-radius: 50%;
            position: absolute;
            left: 6px;
            top: 0;
        }
        
        .timeline-content {
            background: #1a1f27;
            border-radius: 4px;
            padding: 10px 15px;
            margin-bottom: 0;
            color: #adb5bd;
        }
        
        .timeline-title {
            font-size: 1rem;
            color: #fff;
            margin-bottom: 5px;
        }
        
        .timeline-date {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 4px;
        }

        .select2-container {
            width: 400px;
        }
        
        .select2-dropdown {
            z-index: 1052;
        }
        .select2-container--open {
            z-index: 1052;
        }

        /* Стили для обозначения дублей */
        .badge-duplicate {
            background-color: #f8d7da;
            color: #721c24;
            padding: 0.3rem 0.6rem;
            border-radius: 0.25rem;
        }

        .badge-main-ticket {
            background-color: #d4edda;
            color: #155724;
            padding: 0.3rem 0.6rem;
            border-radius: 0.25rem;
        }

        .ticket-link {
            color: #0066cc;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .ticket-link:hover {
            color: #004499;
            text-decoration: underline;
        }

        .duplicate-tickets-table {
            width: 100%;
            border-collapse: collapse;
        }

        .duplicate-tickets-table th,
        .duplicate-tickets-table td {
            padding: 0.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .duplicate-tickets-table thead th {
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
            font-weight: 600;
        }

        #btn-disputed-complaint {
            margin-left: 5px;
            background-color: #f39c12;
            border-color: #e67e22;
            color: #fff;
        }

        #btn-disputed-complaint:hover {
            background-color: #e67e22;
            border-color: #d68910;
        }

        #btn-disputed-complaint:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Стили для кнопки возврата */
        #btn-return-to-new {
            margin-left: 5px;
        }

        /* Выделение спорных жалоб в статусе */
        .badge-disputed {
            background-color: #FF6B6B !important;
            color: #fff !important;
        }

        /* Подсветка комментария при фокусе для спорной жалобы */
        .comment-required-highlight {
            border: 2px solid #f39c12 !important;
            box-shadow: 0 0 10px rgba(243, 156, 18, 0.3) !important;
        }

        /* Анимация мигания для привлечения внимания */
        @keyframes blink {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .blink-animation {
            animation: blink 1s ease-in-out 3;
        }
    </style>
{/capture}

<div class="page-wrapper">
    <div class="container-fluid px-4 py-5">
        <div class="card position-sticky">
            <div class="card-body" style="background-color: #1a1f27">
                <div class="row align-items-center">
                    <div class="col-lg-4">
                        <div class="d-flex align-items-center mb-1">
                            <h4 class="mb-0 mr-2" style="font-weight: 600;">
                                {$ticket->ticket_subject|escape} #{$ticket->id}
                            </h4>

                            <span class="badge" style="background-color: {$ticket->status_color}; color:#fff; font-weight: 500;">
                                {$ticket->status_name|escape}
                            </span>

                            {if $ticket->is_duplicate == 1}
                                <span class="badge badge-duplicate ml-2">Дубль</span>
                            {elseif $ticket->duplicates_count > 0}
                                <span class="badge badge-main-ticket ml-2">Основной ({$ticket->duplicates_count} дубл.)</span>
                            {elseif $agreement_copies_count > 0}
                                <span class="badge badge-warning ml-2">Есть ДД ({$agreement_copies_count} шт.)</span>
                            {/if}
                        </div>

                        <input type="hidden" id="ticket_id" value="{$ticket->id}">
                        <input type="hidden" id="ticket_channel_id" value="{$ticket->chanel_id}">
                        <input type="hidden" id="ticket_status_id" value="{$ticket->status_id}">

                        {if $ticket->accepted_at}
                            <div class="ticket-timer mt-2">
                                <i class="far fa-clock"></i> В работе:
                                {if $ticket->status_id == 5}
                                    {assign var="closedTime" value=$working_time.closed_time}
                                    {assign var="openStart" value=$working_time.open_start}
                                    <span id="work-timer" class="text-warning"
                                          data-closed-time="{$closedTime}"
                                          data-open-start="{$openStart}">
                                        00:00:00
                                    </span>
                                {else}
                                    {assign var="totalTime" value=$working_time.closed_time}
                                    {if $working_time.open_start}
                                        {assign var="totalTime" value=$totalTime + (time() - $working_time.open_start)}
                                    {/if}
                                    {assign var="hours" value=floor($totalTime / 3600)}
                                    {assign var="minutes" value=floor(($totalTime % 3600) / 60)}
                                    {assign var="seconds" value=$totalTime % 60}
                                    <span class="text-warning">
                                        {if $hours > 0}{$hours} ч {/if}
                                        {if $minutes > 0}{$minutes} мин {/if}
                                        {if $hours == 0 && $minutes == 0}{$seconds} сек{/if}
                                    </span>
                                {/if}
                            </div>
                        {/if}

                    </div>

                    <div class="col-lg-8 text-lg-right mt-3 mt-lg-0">
                        {if $ticket->is_duplicate != 1}
                            {* Чекбокс "Достигнуты договоренности" *}
                            {if !$ticket->data.has_agreement}
                                <div class="form-check form-check-inline">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           id="agreement-checkbox"
                                           data-ticket-id="{$ticket->id}">
                                    <label class="form-check-label" for="agreement-checkbox">
                                        <i class="fas fa-handshake mr-1"></i>
                                        Достигнуты договоренности
                                    </label>
                                </div>
                            {/if}

                            {if !in_array($ticket->status_id, [1, 7])}
                                <button 
                                        class="btn btn-primary ticket-action" 
                                        data-action="request_details" 
                                        data-ticket-id="{$ticket->id}"
                                        {if $ticket->data.request_details_used}style="display: none"{/if}
                                >
                                    <i class="fas fa-hand-paper mr-2"></i>Статус КО
                                </button>
                            {/if}

                            {* Кнопка "Спорная жалоба" *}
                            {if $ticket->is_duplicate != 1 && in_array($ticket->chanel_id, [1, 2, 3]) && $ticket->status_id != 9}
                                <button type="button"
                                        id="btn-disputed-complaint"
                                        class="btn btn-warning"
                                        data-ticket-id="{$ticket->id}">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>Спорная жалоба
                                </button>
                            {/if}

                            {* Кнопка возврата в статус "Новый" *}
                            {if $ticket->status_id == 9 && in_array($manager->id, [381, 272, 388])}
                                <button type="button"
                                        id="btn-return-to-new"
                                        class="btn btn-success"
                                        data-ticket-id="{$ticket->id}">
                                    <i class="fas fa-undo mr-2"></i>Вернуть в работу
                                </button>
                            {/if}
                        {/if}
                        <div class="btn-group" role="group">
                            {* Кнопки доступны только для основного тикета или если тикет не является дублем *}
                            {if $ticket->is_duplicate != 1 && $ticket->status_id != 8}
                                {if $ticket->status_id == 1}
                                    <button class="btn btn-primary ticket-action" data-action="accept" data-ticket-id="{$ticket->id}">
                                        <i class="fas fa-check mr-2"></i>Принять
                                    </button>
                                {elseif $ticket->status_id == 10}
                                    {* Кнопки для статуса "Достигнуты договоренности" *}
                                    <button class="btn btn-warning ticket-action" data-action="pause" data-ticket-id="{$ticket->id}">
                                        <i class="fas fa-pause-circle mr-2"></i>Ожидание
                                    </button>
                                    <button class="btn btn-info open-agreement-modal" 
                                            data-mode="reschedule"
                                            data-ticket-id="{$ticket->id}"
                                            data-source-ticket-id="{$ticket->data.source_ticket_id}"
                                            data-current-date="{$ticket->data.agreement_date}"
                                            data-current-note="{$ticket->data.agreement_note}">
                                        <i class="fas fa-calendar-alt mr-2"></i>Перенести на другую дату
                                    </button>
                                {elseif $ticket->status_id == 5}
                                    <button class="btn btn-warning ticket-action" data-action="pause" data-ticket-id="{$ticket->id}">
                                        <i class="fas fa-pause-circle mr-2"></i>Остановить работу
                                    </button>
                                {elseif $ticket->status_id == 3 && $ticket->accepted_at}
                                    <button class="btn btn-info ticket-action" data-action="unpause" data-ticket-id="{$ticket->id}">
                                        <i class="fas fa-play-circle mr-2"></i>Возобновить работу
                                    </button>
                                {/if}

                                {if !in_array($ticket->status_id, [2, 4]) && !$ticket->closed_at}
                                    <button class="btn btn-success ticket-action" data-action="resolve" data-ticket-id="{$ticket->id}">
                                        <i class="fas fa-check-circle mr-2"></i>Урегулирован
                                    </button>
                                    <button class="btn btn-danger ticket-action" data-action="unresolve" data-ticket-id="{$ticket->id}">
                                        <i class="fas fa-times-circle mr-2"></i>Не урегулирован
                                    </button>
                                {else}
                                    {if in_array($manager->role, ['admin','developer', 'opr', 'ts_operator'])}
                                        <button class="btn btn-info ticket-action" data-action="unpause" data-ticket-id="{$ticket->id}">
                                            <i class="fas fa-play-circle mr-2"></i>Возобновить работу
                                        </button>
                                        {if $ticket->status_id == 2}
                                            <button class="btn btn-success ticket-action" data-action="resolve" data-ticket-id="{$ticket->id}">
                                                <i class="fas fa-check-circle mr-2"></i>Урегулирован
                                            </button>
                                        {elseif $ticket->status_id == 4}
                                            <button class="btn btn-danger ticket-action" data-action="unresolve" data-ticket-id="{$ticket->id}">
                                                <i class="fas fa-times-circle mr-2"></i>Не урегулирован
                                            </button>
                                        {/if}
                                    {/if}
                                {/if}
                            {else}
                                {* Если это дубль, показываем сообщение и ссылку на основной тикет *}
                                <div class="alert alert-warning mb-0">
                                    Это дублирующийся тикет. Управление статусами доступно только в
                                    <a href="/tickets/{$main_ticket->id}" class="font-weight-bold">основном тикете #{$main_ticket->id}</a>
                                </div>
                            {/if}
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left column -->
            <div class="col-md-8">
                {* Информационный блок для копии договоренности *}
                {if $ticket->data.agreement_copy && $ticket->data.source_ticket_id}
                    <div class="alert alert-info mb-4">
                        <h5 class="alert-heading">
                            <i class="fas fa-handshake mr-2"></i>Напоминание по договоренностям
                        </h5>
                        <p class="mb-2">
                            Это копия тикета, созданная для напоминания о договоренностях с клиентом.
                        </p>
                        <hr>
                        <p class="mb-1">
                            <strong>Дата договоренности:</strong> {$ticket->data.agreement_date|date_format:'%d.%m.%Y'}
                        </p>
                        <p class="mb-1">
                            <strong>Суть договоренности:</strong> {$ticket->data.agreement_note|escape}
                        </p>
                        <p class="mb-0">
                            <a href="/tickets/{$ticket->data.source_ticket_id}" class="btn btn-sm btn-outline-primary mt-2" target="_blank">
                                <i class="fas fa-external-link-alt mr-1"></i>Перейти к исходному тикету #{$ticket->data.source_ticket_id}
                            </a>
                        </p>
                    </div>
                {/if}

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Обращение</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">{$ticket->description|make_urls_clickable}</p>
                    </div>
                </div>

                {* Если тикет имеет дубли и не является дублем, показываем информацию о дублях *}
                {if $ticket->duplicates_count > 0 && $ticket->is_duplicate == 0}
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Дублирующиеся тикеты</h5>
                            <span class="badge badge-primary">{$duplicate_tickets|@count|default:0}</span>
                        </div>
                        <div class="card-body overflow-auto" style="max-height: 400px">
                            <div class="table-responsive">
                                <table class="duplicate-tickets-table">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Дата создания</th>
                                        <th>Статус</th>
                                        <th>Исполнитель</th>
                                        <th>Действия</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {foreach $duplicate_tickets as $dup_ticket}
                                        <tr>
                                            <td>#{$dup_ticket->id}</td>
                                            <td>{$dup_ticket->created_at|date_format:'%d.%m.%Y %H:%M'}</td>
                                            <td>{$dup_ticket->status_name}</td>
                                            <td>{$dup_ticket->manager_name}</td>
                                            <td>
                                                <a href="/tickets/{$dup_ticket->id}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye mr-1"></i>Просмотр
                                                </a>
                                            </td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                {/if}

                {* Если у тикета есть копии ДД, показываем их *}
                {if !empty($agreement_copies)}
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Копии договоренностей</h5>
                            <span class="badge badge-warning">{$agreement_copies|@count}</span>
                        </div>
                        <div class="card-body overflow-auto" style="max-height: 400px">
                            <div class="table-responsive">
                                <table class="duplicate-tickets-table">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Дата создания</th>
                                        <th>Дата договоренности</th>
                                        <th>Статус</th>
                                        <th>Действия</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {foreach $agreement_copies as $ag_copy}
                                        <tr>
                                            <td>#{$ag_copy->id}</td>
                                            <td>{$ag_copy->created_at|date_format:'%d.%m.%Y %H:%M'}</td>
                                            <td>{$ag_copy->agreement_date|date_format:'%d.%m.%Y'}</td>
                                            <td>{$ag_copy->status_name}</td>
                                            <td>
                                                <a href="/tickets/{$ag_copy->id}" class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-eye mr-1"></i>Просмотр
                                                </a>
                                            </td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                {/if}

                <!-- Комментарии -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Комментарии</h5>
                        <span class="badge badge-primary">{$comments|@count|default:0}</span>
                    </div>
                    <div class="card-body">
                        <!-- Форма добавления комментария -->
                        <div class="mb-4" id="comment-form" data-ticket-id="{$ticket->id}">
                            <div class="position-relative">
                                <textarea id="comment-text"
                                          class="form-control bg-dark border-0"
                                          rows="3"
                                          placeholder="Напишите комментарий..."
                                          style="resize: none;"
                                ></textarea>
                                <div class="mt-2 d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Ctrl + Enter для отправки</small>
                                    <button id="send-comment"
                                            class="btn btn-primary btn-sm"
                                    >
                                        <i class="fas fa-paper-plane mr-2"></i>Отправить
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Список комментариев -->
                        <div id="comments" class="comments-container" style="max-height: 500px; overflow-y: auto;">
                            {if $comments|@count > 0}
                                {foreach $comments as $comment}
                                    <div class="comment-item mb-3" data-created-at="{$comment->created_at}">
                                        <div class="media">
                                            <div class="mr-3">
                                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white"
                                                     style="width: 40px; height: 40px;">
                                                    {$comment->manager_name|mb_substr:0:1|upper}
                                                </div>
                                            </div>
                                            <div class="media-body">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="font-weight-bold">{$comment->manager_name|escape}</span>
                                                    <small class="text-muted">
                                                        <i class="far fa-clock mr-1"></i>
                                                        {$comment->created_at|date_format:'%d.%m.%Y %H:%M'}
                                                        {if isset($comment->ticket_id) && $comment->ticket_id != $ticket->id}
                                                            <a href="/tickets/{$comment->ticket_id}" target="_blank" class="ml-2 badge badge-info">
                                                                Из тикета #{$comment->ticket_id}
                                                            </a>
                                                        {/if}
                                                    </small>
                                                </div>
                                                <div class="p-3 bg-dark rounded">
                                                    {$comment->text}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {/foreach}
                            {else}
                                <div class="text-center py-2">
                                    <i class="far fa-comments fa-3x mb-3 text-muted"></i>
                                    <p class="text-muted">Комментариев пока нет</p>
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>
                
                <!-- Комментарии из 1С и CRM -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Комментарии из заявки</h5>
                        <span class="badge badge-primary">{$comments_from_order|@count|default:0}</span>
                    </div>
                    <div class="card-body">
                        <!-- Список комментариев -->
                        <div id="comments" class="comments-container" style="max-height: 500px; overflow-y: auto;">
                            {if $comments_from_order|@count > 0}
                                {foreach $comments_from_order as $comment}
                                    <div class="comment-item mb-3">
                                        <div class="media">
                                            <div class="mr-3">
                                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white"
                                                     style="width: 40px; height: 40px;">
                                                    {$comment->manager_name|mb_substr:0:1|upper}
                                                </div>
                                            </div>
                                            <div class="media-body">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span>{$comment->manager_name|escape}</span>
                                                    <small class="text-muted">
                                                        <i class="far fa-clock mr-1"></i>
                                                        {$comment->created|date_format:'%d.%m.%Y %H:%M'}
                                                    </small>
                                                </div>
                                                <div class="p-3 bg-dark rounded">
                                                    {$comment->text|make_urls_clickable}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {/foreach}
                            {else}
                                <div class="text-center py-2">
                                    <i class="far fa-comments fa-3x mb-3 text-muted"></i>
                                    <p class="text-muted">Комментариев пока нет</p>
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Client -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Клиент</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fas fa-user"></i>
                                <span class="ml-2">ФИО:</span>
                                {if $ticket->client_id}
                                    <a href="client/{$ticket->client_id}" target="_blank" class="float-right">
                                        {$ticket->client_full_name|escape}
                                    </a>
                                {else}
                                    <span class="float-right">{$ticket->data.fio|escape}</span>
                                {/if}
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-birthday-cake"></i>
                                <span class="ml-2">Дата рождения:</span>
                                <span class="float-right">{($ticket->client_birth) ? $ticket->client_birth : $ticket->data.birth|escape}</span>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-envelope"></i>
                                <span class="ml-2">E-mail:</span>
                                <span class="float-right">
                                    {if $ticket->client_email}
                                        <a href="mailto:{$ticket->client_email|escape}" style="color: white;">{$ticket->client_email|escape}</a>
                                    {else}
                                        Не указан
                                    {/if}
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Телефоны клиента</h5>
                    </div>
                    <div class="card-body">
                        {if $ticket->client_id}
                            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center mb-3">
                                <div class="flex-grow-1">
                                    <i class="fas fa-phone text-muted"></i>
                                    <span class="ml-2">Основной телефон:</span>
                                    <span class="ml-2 font-weight-medium">{$ticket->client_phone|escape}</span>
                                </div>
                                
                                <button class="btn btn-outline-primary mt-2 mt-lg-0 call mr-2"
                                        data-phone="{$ticket->client_phone|escape}"
                                        data-toggle="tooltip"
                                        title="Позвонить клиенту через Vox">
                                    <i class="fas fa-phone mr-2"></i>Позвонить
                                </button>
                                <a href="https://t.me/{$ticket->client_phone|format_phone}"
                                   class="btn btn-info mt-2 mt-lg-0 mr-2"
                                   data-toggle="tooltip"
                                   title="Написать в Telegram"
                                   target="_blank"
                                >
                                    <i class="mdi mdi-telegram"></i>
                                </a>
                                
                                <a href="https://wa.me/{$ticket->client_phone|format_phone}"
                                   class="btn btn-success mt-2 mt-lg-0 mr-2"
                                   data-toggle="tooltip"
                                   title="Написать в WhatsApp"
                                   target="_blank"
                                >
                                    <i class="mdi mdi-whatsapp"></i>
                                </a>
                            </div>
                        {/if}

                        {if $ticket->data.phone}
                            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center">
                                <div class="flex-grow-1">
                                    <i class="fas fa-phone text-muted"></i>
                                    <span class="ml-2">Телефон из тикета:</span>
                                    <span class="ml-2 font-weight-medium">{$ticket->data.phone|escape}</span>
                                </div>
                                <button class="btn btn-outline-primary mt-2 mt-lg-0 call mr-2"
                                        data-phone="{$ticket->data.phone|escape}"
                                        data-toggle="tooltip"
                                        title="Позвонить клиенту через Vox">
                                    <i class="fas fa-phone mr-2"></i>Позвонить
                                </button>

                                <a href="https://t.me/{$ticket->data.phone|format_phone}"
                                   class="btn btn-info mt-2 mt-lg-0 mr-2"
                                   data-toggle="tooltip"
                                   title="Написать в Telegram"
                                   target="_blank"
                                >
                                    <i class="mdi mdi-telegram"></i>
                                </a>

                                <a href="https://api.whatsapp.com/send?phone={$ticket->data.phone|format_phone}"
                                   class="btn btn-success mt-2 mt-lg-0 mr-2"
                                   data-toggle="tooltip"
                                   title="Написать в WhatsApp"
                                   target="_blank"
                                >
                                    <i class="mdi mdi-whatsapp"></i>
                                </a>
                            </div>
                        {/if}
                    </div>
                </div>


                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Займ</h5>
                    </div>
                    <div class="card-body">
                        {if isset($order)}
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-hashtag"></i>
                                    <span class="ml-2">Номер:</span>
                                    <a class="float-right" href="/order/{$ticket->order_id}"
                                       target="_blank">{$order->number}</a>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-ruble-sign"></i>
                                    <span class="ml-2">Сумма:</span>
                                    <span class="float-right">{$order->amount} р.</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-calendar"></i>
                                    <span class="ml-2">Дата:</span>
                                    <span class="float-right">{$order->date|date}</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-calendar-minus"></i>
                                    <span class="ml-2">Дни просрочки на момент создания:</span>
                                    <span class="float-right">{if isset($ticket->data.overdue_days)}{$ticket->data.overdue_days}{else}нет информации{/if}</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-calendar-minus"></i>
                                    <span class="ml-2">Дни просрочки:</span>
                                    <span class="float-right">{if isset($order->days_overdue)}{$order->days_overdue}{else}нет информации{/if}</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-shield-alt"></i>
                                    <span class="ml-2">Ответственный коллектор:</span>
                                    
                                    <span class="float-right">{if $ticket->responsible_person_name} {$ticket->responsible_person_name} {else} Не указан {/if}</span>
                                </li>
                            </ul>
                        {else}
                            <p>Займ не привязан</p>
                        {/if}
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Детали</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fas fa-tag"></i>
                                <span class="ml-2">Тема:</span>
                                <span class="float-right d-flex align-items-center">
                                    <span id="current-subject">{$ticket->subject|escape}</span>
                                    {if in_array($manager->role, ['admin', 'developer', 'opr', 'ts_operator']) && $ticket->is_duplicate != 1}
                                        <button class="btn btn-sm btn-outline-primary ml-2" id="change-subject-btn">
                                            <i class="fas fa-exchange-alt"></i>
                                        </button>
                                        <div style="display: none;" class="ml-2" id="subject-select-container">
                                            <select id="subject-select" class="form-control"></select>
                                        </div>
                                    {/if}
                                </span>
                            </li>

                            <li class="mb-2">
                                <i class="fas fa-exclamation-circle"></i>
                                <span class="ml-2">Приоритет:</span>
                                <span class="float-right d-flex align-items-center">
                                    <span id="current-priority">{$ticket->priority_name|escape}</span>
                                    {if $ticket->status_id == 3}
                                        <button class="btn btn-sm btn-outline-primary ml-2" id="change-priority-btn">
                                            <i class="fas fa-exchange-alt"></i>
                                        </button>
                                        <div style="display: none;" class="ml-2" id="priority-select-container">
                                            <select id="priority-select" class="form-control">
                                                {foreach $priorities as $p}
                                                    <option value="{$p->id}" {if $p->id == $ticket->priority_id}selected{/if}>{$p->name|escape}</option>
                                                {/foreach}
                                            </select>
                                        </div>
                                    {/if}
                                </span>
                            </li>

                            {* Отображение информации о дублировании *}
                            {if $ticket->is_duplicate == 1}
                                <li class="mb-2">
                                    <i class="fas fa-copy text-warning"></i>
                                    <span class="ml-2">Статус дублирования:</span>
                                    <span class="float-right">
                                        <span class="badge badge-warning">Дубль</span>
                                    </span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-link"></i>
                                    <span class="ml-2">Основной тикет:</span>
                                    <span class="float-right">
                                        <a href="/tickets/{$main_ticket->id}" class="btn btn-sm btn-outline-primary">
                                            #{$main_ticket->id}
                                        </a>
                                    </span>
                                </li>
                            {elseif $ticket->duplicates_count > 0}
                                <li class="mb-2">
                                    <i class="fas fa-copy text-info"></i>
                                    <span class="ml-2">Статус дублирования:</span>
                                    <span class="float-right">
                                        <span class="badge badge-info">Основной тикет</span>
                                    </span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-copy"></i>
                                    <span class="ml-2">Количество дублей:</span>
                                    <span class="float-right">
                                        <span class="badge badge-primary">{$ticket->duplicates_count}</span>
                                    </span>
                                </li>
                            {/if}

                            <li class="mb-2">
                                <i class="fas fa-comments"></i>
                                    {if $ticket->direction_id > 0}
                                        <span class="ml-2">Направление:</span>
                                        <span class="float-right">{$ticket->direction_name|escape}</span>
                                    {else}
                                        <span class="ml-2">Канал коммуникации:</span>
                                        <span class="float-right">{$ticket->channel_name|escape}</span>
                                    {/if}
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-building"></i>
                                <span class="ml-2">Компания:</span>
                                <span class="float-right">{($ticket->company_name) ? $ticket->company_name : 'Не найден'}</span>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-building"></i>
                                <span class="ml-2">Источник:</span>
                                <span class="float-right">{(($ticket->data.source) ? $ticket->data.source : 'Не указан')|make_urls_clickable}</span>
                            </li>
                            <li class="mb-2">
                                <i class="far fa-clock"></i>
                                <span class="ml-2">Создан:</span>
                                <span class="float-right">{$ticket->created_at|date_format:'%d.%m.%Y %H:%M'}</span>
                            </li>
                            <li class="mb-2">
                                <i class="far fa-clock"></i>
                                <span class="ml-2">Взять в работу:</span>
                                <span class="float-right">{$ticket->accepted_at|date_format:'%d.%m.%Y %H:%M'}</span>
                            </li>
                            {if $ticket->closed_at}
                                <li class="mb-2">
                                    <i class="far fa-clock"></i>
                                    <span class="ml-2">Закрыто:</span>
                                    <span class="float-right">{$ticket->closed_at|date_format:'%d.%m.%Y %H:%M'}</span>
                                </li>
                            {/if}
                            <li class="mb-2">
                                <i class="fas fa-user-plus"></i>
                                <span class="ml-2">Инициатор:</span>
                                <span class="float-right">{$initiator->name}</span>
                            </li>
                            
                            <li class="mb-2">
                                <i class="fas fa-eye"></i>
                                <span class="ml-2">Исполнитель:</span>
                                <span class="float-right d-flex align-items-center">
                                    <span id="current-manager">{$ticket->manager_name}</span>
                                    {if in_array($manager->role, ['admin', 'developer', 'opr', 'ts_operator']) && $ticket->is_duplicate != 1}
                                        <button class="btn btn-sm btn-outline-primary ml-2" id="change-manager-btn">
                                        <i class="fas fa-exchange-alt"></i>
                                    </button>
                                    <div style="display: none;" class="ml-2" id="manager-select-container">
                                        <select id="manager-select" class="form-control"></select>
                                    </div>
                                    {/if}
                                </span>
                            </li>


                            {if isset($ticket->responsible_group_name) && $ticket->responsible_group_name || isset($ticket->responsible_person_name) && $ticket->responsible_person_name}
                                <li class="mb-2">
                                    <i class="fas fa-user"></i>
                                    <span class="ml-2">Ответственный по договору:</span>
                                    <span class="float-right d-flex align-items-center">
                                        <span>{$ticket->responsible_group_name|default:''} {$ticket->responsible_person_name|default:''}</span>
                                    </span>
                                </li>
                            {/if}
                            
                            {if in_array($ticket->status_id, [2,4])}
                                <li class="mb-2">
                                    <i class="fas fa-comment"></i>
                                    <span class="ml-2">Обратная связь от клиента:</span>
                                    <span class="float-right d-flex align-items-center">
                                        <span>{($ticket->feedback_received) ? 'Получена' : 'Не получена'}</span>
                                    </span>
                                </li>
                            {/if}

                            {* Информация о договоренностях для копий тикетов *}
                            {if $ticket->data.agreement_copy}
                                <li class="mb-2">
                                    <i class="fas fa-handshake text-warning"></i>
                                    <span class="ml-2">Договоренность на дату:</span>
                                    <span class="float-right">{$ticket->data.agreement_date|escape}</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-sticky-note text-warning"></i>
                                    <span class="ml-2">Суть договоренностей:</span>
                                    <span class="float-right">{$ticket->data.agreement_note|escape}</span>
                                </li>
                                {if $ticket->data.source_ticket_id}
                                    <li class="mb-2">
                                        <i class="fas fa-link text-warning"></i>
                                        <span class="ml-2">Исходный тикет:</span>
                                        <span class="float-right">
                                            <a href="/tickets/{$ticket->data.source_ticket_id}" class="btn btn-sm btn-outline-primary">
                                                #{$ticket->data.source_ticket_id}
                                            </a>
                                        </span>
                                    </li>
                                {/if}
                            {/if}
                        </ul>
                    </div>
                </div>

                <!-- История звонков -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">История звонков</h5>
                        <span class="badge badge-primary">{$call_history|@count|default:0}</span>
                    </div>
                    <div class="card-body overflow-auto" style="max-height: 550px">
                        {if isset($call_history) && $call_history|@count > 0}
                            {foreach $call_history as $call}
                                {assign var="callResult" value=$call->text|json_decode:1}
                                <div class="call-item" id="call-{$call->id}">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="d-flex align-items-center mb-2 text-primary">
                                                <i class="fas fa-phone mr-2"></i>
                                                <span class="font-weight-bold mr-3">
                                                    Входящий
                                                </span>
                                                <span class="text-light-muted">
                                                    <i class="far fa-clock mr-1"></i>
                                                    {$call->created|date_format:"%d.%m.%Y %H:%M"}
                                                </span>
                                            </div>
                                            <div class="call-details">
                                                <div class="mb-1">
                                                    <i class="fas fa-sitemap mr-2"></i>
                                                    Стадия: {$callResult.stage}
                                                </div>
                                                <div class="mb-1">
                                                    <i class="fas fa-tag mr-2"></i>
                                                    Тег: {$callResult.operator_tag}
                                                </div>
                                                <div class="mb-1">
                                                    <i class="fas fa-bars mr-2"></i>
                                                    Выбрал меню: {$callResult.tag}
                                                </div>
                                                <div>
                                                    <i class="fas fa-user mr-2"></i>
                                                    Оператор:
                                                    {if $callResult.handled_by === 'aviar'}
                                                        Aviar
                                                    {elseif $callResult.handled_by === 'operator'}
                                                        {$callResult.operator_name}
                                                    {/if}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-right">
                                            {if $callResult.record_url}
                                                <div class="btn-group">
                                                    <button class="btn btn-outline-primary btn-sm play-record"
                                                            data-call-id="{$call->id}"
                                                            data-toggle="tooltip"
                                                            title="Прослушать запись">
                                                        <i class="fas fa-play mr-1"></i> Прослушать
                                                    </button>
                                                    <a href="{$callResult.record_url}"
                                                       class="btn btn-outline-secondary btn-sm"
                                                       download
                                                       data-toggle="tooltip"
                                                       title="Скачать запись">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                </div>
                                            {/if}
                                        </div>
                                    </div>

                                    {if $callResult.record_url}
                                        <div class="audio-player d-none mt-3" id="player_{$call->id}">
                                            <div class="progress mb-2" style="height: 5px;">
                                                <div class="progress-bar bg-info" role="progressbar"
                                                     style="width: 0%"></div>
                                            </div>
                                            <audio controls class="w-100" preload="none">
                                                <source src="{$callResult.record_url}" type="audio/mpeg">
                                                Ваш браузер не поддерживает аудио элемент.
                                            </audio>
                                        </div>
                                    {/if}
                                </div>
                            {/foreach}
                        {else}
                            <div class="text-center py-4 text-light-muted">
                                <i class="fas fa-phone-slash fa-3x mb-3"></i>
                                <p class="mb-0">История звонков пуста</p>
                            </div>
                        {/if}
                    </div>
                </div>

                <!-- Timeline: История тикета -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">История тикета</h5>
                    </div>
                    <div class="card-body p-4 overflow-auto" style="max-height: 550px">
                        <ul class="ticket-timeline">
                            {if $ticket_history|@count > 0}
                                {foreach $ticket_history as $index => $history}
                                    <li>
                                        <!-- Кружок (цвет можно варьировать) -->
                                        <div class="timeline-icon"
                                             style="background-color: {if $index mod 5 == 0}#ffb822
                                            {elseif $index mod 5 == 1}#0abb87
                                            {elseif $index mod 5 == 2}#fd397a
                                            {elseif $index mod 5 == 3}#36a3f7
                                            {else}#5d78ff{/if};">
                                        </div>

                                        <div class="timeline-content">
                                            <div class="timeline-title">
                                                {if $history->field_name == 'creation'}
                                                    Тикет создан
                                                {elseif $history->field_name == 'manager_id'}
                                                    Смена исполнителя
                                                {elseif $history->field_name == 'status_id'}
                                                    Обновление статуса
                                                {elseif $history->field_name == 'workload_exclusion'}
                                                    Тикет снять с нагрузки
                                                {elseif $history->field_name == 'subject_id'}
                                                    Смена темы
                                                {elseif $history->field_name == 'duplicates'}
                                                    Дублирующиеся тикеты
                                                {elseif $history->field_name == 'priority_id'}
                                                    Смена приоритета
                                                {elseif $history->field_name == 'agreement'}
                                                    Договоренность
                                                {elseif $history->field_name == 'agreement_copy'}
                                                    Копия договоренности
                                                {elseif $history->field_name == 'agreement_completed'}
                                                    Договоренность выполнена
                                                {elseif $history->field_name == 'agreement_rescheduled'}
                                                    Перенос договоренности
                                                {else}
                                                    {$history->field_name|escape}
                                                {/if}
                                            </div>

                                            {if $history->field_name == 'manager_id'}
                                                Изменено с
                                                <strong>{$managers[$history->old_value]}</strong>
                                                на
                                                <strong>{$managers[$history->new_value]}</strong>.
                                            {elseif $history->field_name == 'creation'}
                                            {elseif $history->field_name == 'status_id'}
                                                Изменено с
                                                <strong>{$statuses[$history->old_value]}</strong>
                                                на
                                                <strong>{$statuses[$history->new_value]}</strong>.
                                            {elseif $history->field_name == 'workload_exclusion'}
                                                <strong>{$history->comment}</strong>
                                            {elseif $history->field_name == 'subject_id'}
                                                Изменено с
                                                <strong>{$subjects[$history->old_value]}</strong>
                                                на
                                                <strong>{$subjects[$history->new_value]}</strong>.
                                            {elseif $history->field_name == 'duplicates'}
                                                <strong>{$history->comment}</strong>
                                            {elseif $history->field_name == 'priority_id'}
                                                Изменено с
                                                <strong>{$priorities_map[$history->old_value]}</strong>
                                                на
                                                <strong>{$priorities_map[$history->new_value]}</strong>.
                                            {else}
                                                {if $history->comment}
                                                    {$history->comment|escape}
                                                {else}
                                                    Изменено с
                                                    <strong>{$history->old_value}</strong>
                                                    на
                                                    <strong>{$history->new_value}</strong>.
                                                {/if}
                                            {/if}

                                            {if $history->field_name != 'duplicates'}
                                                <div>
                                                    Кем: <strong>{$managers[$history->changed_by]}</strong>
                                                </div>
                                            {/if}

                                            <div class="timeline-date">
                                                {$history->changed_at|date_format:'%d.%m.%Y %H:%M'}
                                            </div>
                                        </div>
                                    </li>
                                {/foreach}
                            {else}
                                <li>
                                    <div class="timeline-icon" style="background-color: #adb5bd;"></div>
                                    <div class="timeline-content">
                                        <p class="timeline-title mb-1">История отсутствует</p>
                                        <p class="timeline-date mb-0">Нет записей в истории</p>
                                    </div>
                                </li>
                            {/if}
                        </ul>
                    </div>
                </div>

                <!-- Attachments -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Привязанные файлы</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            {if isset($data.attached_files) && $data.attached_files|@count > 0}
                                {foreach $data.attached_files as $file}
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <span>{$file.name}</span><br>
                                        </div>
                                        <div>
                                            <a href="{$file.url}" target="_blank"
                                               class="btn btn-sm btn-outline-primary">Скачать</a>
                                        </div>
                                    </li>
                                {/foreach}
                            {else}
                                <div class="text-center py-4 text-light-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3"></i>
                                    <p class="mb-0">Нет прикрепленных файлов</p>
                                </div>
                            {/if}
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="feedbackModal" tabindex="-1" role="dialog" aria-labelledby="feedbackModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="background-color: #1a1f27; color: #ffffff;">
            <div class="modal-header">
                <h5 class="modal-title" id="feedbackModalLabel">Обратная связь от клиента</h5>
            </div>
            <div class="modal-body">
                <p>В процессе проработки тикета, была ли получена обратная связь от клиента?</p>
                <input type="hidden" id="feedback-ticket-id" value="">
                <input type="hidden" id="feedback-action" value="">
            {if $ticket->client_id}
                <div id="resolvedNotificationBlock" style="display: none; margin-top: 20px; padding: 15px; background-color: #2a2f37; border-radius: 5px; border-left: 4px solid #28a745;">
                    <h6 style="color: #28a745; margin-bottom: 10px;">
                        <i class="fas fa-bell mr-2"></i>Отправить пуш-уведомление клиенту?
                    </h6>
                    <p style="margin-bottom: 0; font-size: 14px;">
                        Если у вас остались дополнительные вопросы, позвоните нам +74951804205
                    </p>
                    <div style="margin-top: 10px;">
                        <label style="display: flex; align-items: center; cursor: pointer;">
                            <input type="checkbox" id="notifyUserCheckbox" style="margin-right: 8px;" >
                            <span>Отправить уведомление</span>
                        </label>
                    </div>
                </div>
            {/if}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" onclick="handleFeedbackButtonClick(1)">
                    <i class="fas fa-check-circle mr-2"></i>Получена
                </button>
                <button type="button" class="btn btn-danger" onclick="handleFeedbackButtonClick(0)">
                    <i class="fas fa-times-circle mr-2"></i>Не получена
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="pauseNotificationModal" tabindex="-1" role="dialog" aria-labelledby="pauseNotificationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="background-color: #1a1f27; color: #ffffff;">
            <div class="modal-header">
                <h5 class="modal-title" id="pauseNotificationModalLabel">Отправить пуш-уведомление клиенту?</h5>
            </div>
            <div class="modal-body">
                <p>С просьбой перезвонить в ОПР для решения проблемы</p>
                <input type="hidden" id="pause-ticket-id" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" onclick="handlePauseNotificationClick(1)">
                    <i class="fas fa-check-circle mr-2"></i>Да
                </button>
                <button type="button" class="btn btn-danger" onclick="handlePauseNotificationClick(0)">
                    <i class="fas fa-times-circle mr-2"></i>Нет
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="agreementModal" tabindex="-1" role="dialog" aria-labelledby="agreementModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="background-color: #1a1f27; color: #ffffff;">
            <div class="modal-header">
                <h5 class="modal-title" id="agreementModalLabel">Достигнуты договоренности</h5>
            </div>
            <div class="modal-body">
                <input type="hidden" id="agreement-mode" value="create">
                <input type="hidden" id="agreement-ticket-id" value="">
                <input type="hidden" id="agreement-source-ticket-id" value="">

                <div id="current-agreement-block" class="form-group" style="display: none;">
                    <label>Текущая договоренность:</label>
                    <p class="text-muted" id="current-agreement-info"></p>
                </div>
                
                <div class="form-group">
                    <label for="agreement-date" id="agreement-date-label">Дата повторного контакта</label>
                    <input type="date" id="agreement-date" class="form-control bg-dark border-0" />
                    <small class="text-muted" id="agreement-date-hint">В указанную дату в 07:00 МСК будет создана копия тикета</small>
                </div>
                
                <div class="form-group mt-3">
                    <label for="agreement-note" id="agreement-note-label">Суть договоренностей</label>
                    <textarea id="agreement-note" class="form-control bg-dark border-0" rows="4" placeholder="Опишите договоренности..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button id="agreement-apply" type="button" class="btn btn-success">
                    <i class="fas fa-check mr-2"></i><span id="agreement-apply-text">Применить</span>
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
            </div>
        </div>
    </div>
</div>

{include file='footer.tpl'}

{capture name='page_scripts'}
    <script src="design/manager/assets/plugins/select2/dist/js/select2.min.js"></script>
    <script src="design/manager/js/ticket_detail.js?v=1.09"></script>
{/capture}