{$meta_title="Запрос ЦБ #`$request->id`" scope=parent}

{capture name='page_styles'}
    <link href="design/manager/assets/plugins/select2/dist/css/select2.css" rel="stylesheet" type="text/css" />
    <style>
        .text-light-muted {
            color: #a8b2bd;
        }

        /* Скроллбар комментариев */
        .comments-container::-webkit-scrollbar { width: 6px; }
        .comments-container::-webkit-scrollbar-track { background: rgba(255,255,255,0.1); border-radius: 3px; }
        .comments-container::-webkit-scrollbar-thumb { background-color: rgba(255,255,255,0.2); border-radius: 3px; }
        .comments-container::-webkit-scrollbar-thumb:hover { background-color: rgba(255,255,255,0.3); }

        /* Анимация комментариев */
        .comment-item { opacity: 0; transform: translateY(20px); animation: commentFadeIn 0.3s ease forwards; }
        @keyframes commentFadeIn { to { opacity: 1; transform: translateY(0); } }
        .comment-item:hover .comment-text { background-color: rgba(255,255,255,0.1) !important; transition: background-color 0.2s ease; }

        /* Timeline */
        .ticket-timeline { list-style: none; padding: 0; margin: 0; position: relative; }
        .ticket-timeline:before { content: ''; position: absolute; top: 0; width: 2px; background: #e9ecef; left: 15px; }
        .ticket-timeline li { position: relative; margin: 0; padding: 0 0 20px 40px; }
        .ticket-timeline li:not(:last-child)::after { content: ''; position: absolute; left: 15px; top: 20px; bottom: 0; width: 2px; background: #e9ecef; }
        .timeline-icon { width: 20px; height: 20px; border-radius: 50%; position: absolute; left: 6px; top: 0; }
        .timeline-content { background: #1a1f27; border-radius: 4px; padding: 10px 15px; margin-bottom: 0; color: #adb5bd; }
        .timeline-title { font-size: 1rem; color: #fff; margin-bottom: 5px; }
        .timeline-text { font-size: 0.9rem; color: #adb5bd; margin-bottom: 4px; }
        .timeline-date { font-size: 0.85rem; color: #6c757d; margin-top: 4px; }

        /* Select2 */
        .select2-container { width: 400px; }
        .select2-dropdown { z-index: 1052; }
        .select2-container--open { z-index: 1052; }

        /* Кнопки статусов */
        .cb-status-btn { transition: all 0.2s ease; }
        .cb-status-btn:hover { transform: translateY(-1px); box-shadow: 0 2px 8px rgba(0,0,0,0.3); }

        /* Секция комментариев */
        .comment-section-card { border: 1px solid rgba(255,255,255,0.1); }
        .comment-section-card .card-header { border-bottom: 1px solid rgba(255,255,255,0.1); }

        /* Sidebar input стили */
        .sidebar-input { background-color: #2a2f3d !important; border: 1px solid rgba(255,255,255,0.1) !important; color: #ffffff !important; }
        .sidebar-input:focus { border-color: #007bff !important; box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25) !important; }
        .sidebar-input::placeholder { color: #6c757d !important; }
    </style>
{/capture}

<div class="page-wrapper">
    <div class="container-fluid px-4 py-5">

        {* ===== Верхняя закрепленная карточка ===== *}
        <div class="card position-sticky" style="z-index: 10; top: 0;">
            <div class="card-body" style="background-color: #1a1f27">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="d-flex align-items-center mb-1">
                            <h4 class="mb-0 mr-2" style="font-weight: 600;">
                                #{$request->id}
                            </h4>

                            {if $request->request_number}
                                <span class="badge badge-secondary ml-1" style="font-size: 0.85rem;">
                                    {$request->request_number|escape}
                                </span>
                            {/if}
                        </div>

                        <input type="hidden" id="request_id" value="{$request->id}">
                        <input type="hidden" id="status_opr" value="{$request->status_opr}">
                        <input type="hidden" id="status_okk" value="{$request->status_okk}">
                        <input type="hidden" id="status_sent" value="{$request->status_sent}">
                    </div>

                    <div class="col-lg-6 text-lg-right mt-3 mt-lg-0">
                        <button class="btn {if $request->status_sent}btn-success{else}btn-outline-primary{/if} cb-status-btn" data-field="status_sent" {if $request->status_sent}disabled{/if}>
                            <i class="fas fa-paper-plane mr-2"></i>Направлен ответ
                        </button>
                        <button class="btn {if $request->status_okk}btn-success{else}btn-outline-success{/if} cb-status-btn" data-field="status_okk" {if $request->status_okk}disabled{/if}>
                            <i class="fas fa-check mr-2"></i>Обработан ОКК
                        </button>
                        <button class="btn {if $request->status_opr}btn-success{else}btn-outline-success{/if} cb-status-btn" data-field="status_opr" {if $request->status_opr}disabled{/if}>
                            <i class="fas fa-check mr-2"></i>Обработан ОПР
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {* ===== Двухколоночная раскладка ===== *}
        <div class="row">

            {* ===== Левая колонка — 5 секций комментариев ===== *}
            <div class="col-md-8">

                {* ---------- Сообщение из ЛК ЦБ ---------- *}
                {if $request->message_text || $request_files}
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-envelope-open-text mr-2"></i>Запрос из ЛК ЦБ</h5>
                            {if $request_files|@count > 0}
                                <span class="badge badge-info">{$request_files|@count} {if $request_files|@count == 1}файл{else}файлов{/if}</span>
                            {/if}
                        </div>
                        {if $request->message_text}
                            <div class="card-body">
                                <div class="p-3 bg-dark rounded" style="white-space: pre-wrap;">{$request->message_text|escape}</div>
                            </div>
                        {/if}
                        {if $request_files|@count > 0}
                            <div class="card-footer">
                                {foreach $request_files as $file}
                                    <a href="{$file.url|escape}" target="_blank" class="btn btn-sm btn-outline-info mr-2 mb-1">
                                        <i class="fas fa-file-download mr-1"></i>{$file.name|escape}
                                    </a>
                                {/foreach}
                            </div>
                        {/if}
                    </div>
                {/if}

                {* ---------- Описание запроса клиента ---------- *}
                <div class="card mb-4 comment-section-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Описание запроса клиента</h5>
                        <span class="badge badge-primary">{$comments_description|@count|default:0}</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="position-relative">
                                <textarea id="comment-text-description"
                                          class="form-control bg-dark border-0"
                                          rows="3"
                                          placeholder="Опишите суть претензии клиента из запроса..."
                                          style="resize: none;"></textarea>
                                <div class="mt-2 d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Ctrl + Enter для отправки</small>
                                    <button class="btn btn-sm btn-info add-comment-btn" data-section="description">
                                        <i class="fas fa-paper-plane mr-2"></i>Отправить
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="comments-container" data-section="description" style="max-height: 500px; overflow-y: auto;">
                            {if $comments_description|@count > 0}
                                {foreach $comments_description as $comment}
                                    <div class="comment-item mb-3" data-created-at="{$comment->created_at}">
                                        <div class="media">
                                            <div class="mr-3">
                                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white"
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
                                                    </small>
                                                </div>
                                                <div class="comment-text p-3 bg-dark rounded">
                                                    {$comment->text|escape}
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

                {* ---------- Комментарии ОПР ---------- *}
                <div class="card mb-4 comment-section-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Комментарии ОПР</h5>
                        <span class="badge badge-primary">{$comments_opr|@count|default:0}</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="position-relative">
                                <textarea id="comment-text-opr"
                                          class="form-control bg-dark border-0"
                                          rows="3"
                                          placeholder="Опишите взаимодействие с клиентом происходившее до запроса..."
                                          style="resize: none;"></textarea>
                                <div class="mt-2 d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Ctrl + Enter для отправки</small>
                                    <button class="btn btn-sm btn-info add-comment-btn" data-section="opr">
                                        <i class="fas fa-paper-plane mr-2"></i>Отправить
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="comments-container" data-section="opr" style="max-height: 500px; overflow-y: auto;">
                            {if $comments_opr|@count > 0}
                                {foreach $comments_opr as $comment}
                                    <div class="comment-item mb-3" data-created-at="{$comment->created_at}">
                                        <div class="media">
                                            <div class="mr-3">
                                                <div class="rounded-circle bg-info d-flex align-items-center justify-content-center text-white"
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
                                                    </small>
                                                </div>
                                                <div class="comment-text p-3 bg-dark rounded">
                                                    {$comment->text|escape}
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

                {* ---------- Комментарии ОКК ---------- *}
                <div class="card mb-4 comment-section-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Комментарии ОКК</h5>
                        <span class="badge badge-primary">{$comments_okk|@count|default:0}</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="position-relative">
                                <textarea id="comment-text-okk"
                                          class="form-control bg-dark border-0"
                                          rows="3"
                                          placeholder="Опишите недочеты выявленные во взаимодействии с клиентом до запроса..."
                                          style="resize: none;"></textarea>
                                <div class="mt-2 d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Ctrl + Enter для отправки</small>
                                    <button class="btn btn-sm btn-info add-comment-btn" data-section="okk">
                                        <i class="fas fa-paper-plane mr-2"></i>Отправить
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="comments-container" data-section="okk" style="max-height: 500px; overflow-y: auto;">
                            {if $comments_okk|@count > 0}
                                {foreach $comments_okk as $comment}
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
                                                    </small>
                                                </div>
                                                <div class="comment-text p-3 bg-dark rounded">
                                                    {$comment->text|escape}
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

                {* ---------- Мероприятия ---------- *}
                <div class="card mb-4 comment-section-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Мероприятия</h5>
                        <span class="badge badge-primary">{$comments_measures|@count|default:0}</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="position-relative">
                                <textarea id="comment-text-measures"
                                          class="form-control bg-dark border-0"
                                          rows="3"
                                          placeholder="Опишите мероприятия необходимые для предотвращения подобного запроса в дальнейшем..."
                                          style="resize: none;"></textarea>
                                <div class="mt-2 d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Ctrl + Enter для отправки</small>
                                    <button class="btn btn-sm btn-info add-comment-btn" data-section="measures">
                                        <i class="fas fa-paper-plane mr-2"></i>Отправить
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="comments-container" data-section="measures" style="max-height: 500px; overflow-y: auto;">
                            {if $comments_measures|@count > 0}
                                {foreach $comments_measures as $comment}
                                    <div class="comment-item mb-3" data-created-at="{$comment->created_at}">
                                        <div class="media">
                                            <div class="mr-3">
                                                <div class="rounded-circle bg-warning d-flex align-items-center justify-content-center text-white"
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
                                                    </small>
                                                </div>
                                                <div class="comment-text p-3 bg-dark rounded">
                                                    {$comment->text|escape}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {/foreach}
                            {else}
                                <div class="text-center py-2">
                                    <i class="far fa-comments fa-3x mb-3 text-muted"></i>
                                    <p class="text-muted">Мероприятий пока нет</p>
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>

                {* ---------- Комментарии Юристы ---------- *}
                <div class="card mb-4 comment-section-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Комментарии Юристы</h5>
                        <span class="badge badge-primary">{$comments_lawyers|@count|default:0}</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="position-relative">
                                <textarea id="comment-text-lawyers"
                                          class="form-control bg-dark border-0"
                                          rows="3"
                                          placeholder="Опишите суть претензии клиента из запроса..."
                                          style="resize: none;"></textarea>
                                <div class="mt-2 d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Ctrl + Enter для отправки</small>
                                    <button class="btn btn-sm btn-info add-comment-btn" data-section="lawyers">
                                        <i class="fas fa-paper-plane mr-2"></i>Отправить
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="comments-container" data-section="lawyers" style="max-height: 500px; overflow-y: auto;">
                            {if $comments_lawyers|@count > 0}
                                {foreach $comments_lawyers as $comment}
                                    <div class="comment-item mb-3" data-created-at="{$comment->created_at}">
                                        <div class="media">
                                            <div class="mr-3">
                                                <div class="rounded-circle bg-danger d-flex align-items-center justify-content-center text-white"
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
                                                    </small>
                                                </div>
                                                <div class="comment-text p-3 bg-dark rounded">
                                                    {$comment->text|escape}
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

            {* ===== Правая колонка — сайдбар ===== *}
            <div class="col-md-4">

                {* ---------- Клиент ---------- *}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Клиент</h5>
                    </div>
                    <div class="card-body">
                        <input type="hidden" id="client_id" value="{$request->client_id|default:''}">

                        {* === Режим просмотра (клиент привязан) === *}
                        <div id="client-view-mode" {if !$request->client_id}style="display:none;"{/if}>
                            <ul class="list-unstyled mb-3">
                                <li class="mb-2">
                                    <i class="fas fa-user text-muted"></i>
                                    <span class="ml-2 text-muted small">ФИО:</span>
                                    <a href="/client/{$request->client_id}" target="_blank" class="text-info ml-1" id="client-view-fio-link">
                                        {$request->linked_user_fio|escape|default:$request->client_fio|escape}
                                    </a>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-birthday-cake text-muted"></i>
                                    <span class="ml-2 text-muted small">Дата рождения:</span>
                                    <span class="ml-1" id="client-view-birth">{$request->client_birth_date|escape|default:$request->linked_user_birth|escape|default:'—'}</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-envelope text-muted"></i>
                                    <span class="ml-2 text-muted small">E-mail:</span>
                                    <span class="ml-1" id="client-view-email">{$request->client_email|escape|default:$request->linked_user_email|escape|default:'—'}</span>
                                </li>
                            </ul>
                            <button class="btn btn-outline-warning btn-block btn-sm" id="change-client-btn">
                                <i class="fas fa-exchange-alt mr-2"></i>Изменить клиента
                            </button>
                        </div>

                        {* === Режим редактирования (клиент не привязан) === *}
                        <div id="client-edit-mode" {if $request->client_id}style="display:none;"{/if}>
                            <div class="form-group mb-3">
                                <label class="text-muted small mb-1"><i class="fas fa-user mr-1"></i> ФИО</label>
                                <input type="text" id="client-fio-input"
                                       class="form-control form-control-sm sidebar-input"
                                       value="{$request->client_fio|escape}"
                                       placeholder="Укажите ФИО клиента...">
                            </div>

                            <div class="form-group mb-3">
                                <label class="text-muted small mb-1"><i class="fas fa-birthday-cake mr-1"></i> Дата рождения</label>
                                <input type="text" id="client-birth-input"
                                       class="form-control form-control-sm sidebar-input"
                                       value="{$request->client_birth_date|escape}"
                                       placeholder="__.__.____">
                            </div>

                            <div class="form-group mb-3">
                                <label class="text-muted small mb-1"><i class="fas fa-envelope mr-1"></i> E-mail</label>
                                <input type="email" id="client-email-input"
                                       class="form-control form-control-sm sidebar-input"
                                       value="{$request->client_email|escape}"
                                       placeholder="Укажите E-mail клиента из запроса">
                            </div>

                            <button class="btn btn-outline-info btn-block btn-sm" id="search-client-btn">
                                <i class="fas fa-search mr-2"></i>Найти клиента
                            </button>

                            <button class="btn btn-outline-secondary btn-block btn-sm mt-2" id="cancel-client-edit-btn" style="display:none;">
                                <i class="fas fa-times mr-2"></i>Отменить
                            </button>

                            <div id="client-search-result" class="mt-2" style="display: none;">
                                <div class="alert mb-0 py-2" id="client-found-info"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {* ---------- Телефоны клиента ---------- *}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Телефоны клиента</h5>
                    </div>
                    <div class="card-body" id="client-phones-container">
                        {if $request->linked_user_phone}
                            <div class="d-flex align-items-center mb-2 phone-row">
                                <span class="badge badge-success mr-2">Основной</span>
                                <span class="phone-number mr-auto">{$request->linked_user_phone|escape}</span>
                                <button class="btn btn-xs btn-success call mr-1" data-phone="{$request->linked_user_phone|escape}" title="Позвонить"><i class="fas fa-phone"></i></button>
                                <a href="https://t.me/{$request->linked_user_phone|escape}" target="_blank" class="btn btn-xs btn-primary mr-1" title="Telegram"><i class="fab fa-telegram"></i></a>
                                <a href="https://wa.me/{$request->linked_user_phone|escape}" target="_blank" class="btn btn-xs btn-success" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                            </div>
                        {/if}
                        {if $client_phones}
                            {foreach $client_phones as $cp}
                                <div class="d-flex align-items-center mb-2 phone-row">
                                    <span class="badge badge-secondary mr-2">Доп.</span>
                                    <span class="phone-number mr-auto">{$cp->phone|escape}</span>
                                    <button class="btn btn-xs btn-success call mr-1" data-phone="{$cp->phone|escape}" title="Позвонить"><i class="fas fa-phone"></i></button>
                                    <a href="https://t.me/{$cp->phone|escape}" target="_blank" class="btn btn-xs btn-primary mr-1" title="Telegram"><i class="fab fa-telegram"></i></a>
                                    <a href="https://wa.me/{$cp->phone|escape}" target="_blank" class="btn btn-xs btn-success" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                                </div>
                            {/foreach}
                        {/if}
                        {if !$request->linked_user_phone && !$client_phones}
                            <p class="text-muted mb-0" id="no-phones-msg">Телефоны не найдены. Привяжите клиента.</p>
                        {/if}
                    </div>
                </div>

                {* ---------- Займ ---------- *}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Займ</h5>
                    </div>
                    <div class="card-body">
                        <input type="hidden" id="order_id" value="{$request->order_id|default:''}">

                        {* === Режим просмотра (договор привязан) === *}
                        <div id="order-view-mode" {if !$request->order_id}style="display:none;"{/if}>
                            <ul class="list-unstyled mb-3">
                                <li class="mb-2">
                                    <i class="fas fa-hashtag text-muted"></i>
                                    <span class="ml-2 text-muted small">Номер:</span>
                                    <a href="/order/{$request->order_id}" target="_blank" class="text-info ml-1" id="order-view-number-link">
                                        {$request->linked_order_number|escape|default:$request->order_number|escape}
                                    </a>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-ruble-sign text-muted"></i>
                                    <span class="ml-2 text-muted small">Сумма:</span>
                                    <span class="ml-1" id="order-view-amount">{if $request->linked_order_amount}{$request->linked_order_amount} р.{else}—{/if}</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-calendar text-muted"></i>
                                    <span class="ml-2 text-muted small">Дата займа:</span>
                                    <span class="ml-1" id="order-view-date">{if $request->linked_order_date}{$request->linked_order_date|date_format:'%d.%m.%Y'}{else}—{/if}</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-clock text-muted"></i>
                                    <span class="ml-2 text-muted small">Дни просрочки на момент создания:</span>
                                    <span class="ml-1" id="order-view-overdue-at-creation">{$request->linked_order_overdue_days_at_creation|default:'—'}</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-exclamation-circle text-muted"></i>
                                    <span class="ml-2 text-muted small">Дни просрочки:</span>
                                    <span class="ml-1" id="order-view-overdue-days">{$request->linked_order_overdue_days|default:'—'}</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-shield-alt text-muted"></i>
                                    <span class="ml-2 text-muted small">Ответственный коллектор:</span>
                                    <span class="ml-1" id="order-view-manager">
                                        {if $request->linked_order_manager_name}
                                            {$request->linked_order_manager_name|escape}
                                        {else}
                                            Не указан
                                        {/if}
                                    </span>
                                </li>
                            </ul>

                            {* Информация о покупателе (если договор продан) *}
                            <div id="order-buyer-info" {if $request->linked_order_sale_info != 'Договор продан'}style="display:none;"{/if}>
                                <div class="alert alert-warning py-2 mb-3">
                                    <i class="fas fa-exchange-alt mr-1"></i> <strong>Договор продан</strong>
                                </div>
                                <ul class="list-unstyled mb-3">
                                    <li class="mb-2">
                                        <i class="fas fa-user-tie text-muted"></i>
                                        <span class="ml-2 text-muted small">Покупатель:</span>
                                        <span class="ml-1" id="order-view-buyer">{$request->linked_order_buyer|escape|default:'—'}</span>
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-phone text-muted"></i>
                                        <span class="ml-2 text-muted small">Телефон покупателя:</span>
                                        <span class="ml-1" id="order-view-buyer-phone">{$request->linked_order_buyer_phone|escape|default:'—'}</span>
                                    </li>
                                </ul>
                            </div>
                            <button class="btn btn-outline-warning btn-block btn-sm" id="change-order-btn">
                                <i class="fas fa-exchange-alt mr-2"></i>Изменить займ
                            </button>
                        </div>

                        {* === Режим редактирования (договор не привязан) === *}
                        <div id="order-edit-mode" {if $request->order_id}style="display:none;"{/if}>
                            <div class="form-group mb-3">
                                <label class="text-muted small mb-1"><i class="fas fa-hashtag mr-1"></i> Номер</label>
                                <input type="text" id="order-number-input"
                                       class="form-control form-control-sm sidebar-input"
                                       value="{$request->order_number|escape}"
                                       placeholder="Номер займа из запроса ЦБ">
                            </div>

                            <button class="btn btn-outline-info btn-block btn-sm" id="search-order-btn">
                                <i class="fas fa-search mr-2"></i>Найти займ
                            </button>

                            <div id="order-search-result" style="display:none;" class="mt-2">
                                <div id="order-found-info"></div>
                            </div>

                            <button class="btn btn-outline-secondary btn-block btn-sm mt-2" id="cancel-order-edit-btn" style="display:none;">
                                <i class="fas fa-times mr-2"></i>Отменить
                            </button>
                        </div>
                    </div>
                </div>

                {* ---------- Детали ---------- *}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Детали</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            {* Тема *}
                            <li class="mb-2 d-flex align-items-center justify-content-between">
                                <span class="text-nowrap">
                                    <i class="fas fa-tag"></i>
                                    <span class="ml-2">Тема:</span>
                                </span>
                                <select id="subject-select" class="form-control form-control-sm sidebar-input" style="width: 65%; max-width: 400px;">
                                    <option value="">— Выберите —</option>
                                    {foreach $subjects as $subject}
                                        <option value="{$subject->id}" {if $subject->id == $request->subject_id}selected{/if}>
                                            {$subject->name|escape}
                                        </option>
                                    {/foreach}
                                </select>
                            </li>

                            {* Компания *}
                            <li class="mb-2">
                                <i class="fas fa-building"></i>
                                <span class="ml-2">Компания:</span>
                                <span class="float-right">
                                    {if $request->organization_name}
                                        {$request->organization_name|escape}
                                    {else}
                                        Не указана
                                    {/if}
                                </span>
                            </li>

                            {* Создан *}
                            <li class="mb-2">
                                <i class="far fa-clock"></i>
                                <span class="ml-2">Создан:</span>
                                <span class="float-right">
                                    {if $request->created_at}
                                        {$request->created_at|date_format:'%d.%m.%Y %H:%M'}
                                    {else}
                                        &mdash;
                                    {/if}
                                </span>
                            </li>

                            {* Взят в работу *}
                            <li class="mb-2">
                                <i class="far fa-clock"></i>
                                <span class="ml-2">Взят в работу:</span>
                                <span class="float-right">
                                    {if $request->taken_at}
                                        {$request->taken_at|date_format:'%d.%m.%Y %H:%M'}
                                    {else}
                                        &mdash;
                                    {/if}
                                </span>
                            </li>

                            {* Срок ответа *}
                            <li class="mb-2 d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-calendar-check mr-2"></i>Срок ответа:</span>
                                <input type="date" id="response-deadline-input"
                                       class="form-control form-control-sm sidebar-input"
                                       style="width: 55%; max-width: 130px;"
                                       value="{$request->response_deadline|default:''}">
                            </li>

                            {* Чекбокс подтверждения *}
                            <li class="mb-2 d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-handshake mr-2"></i>Взаимодействовал ОПР<br>с клиентом до запроса</span>
                                <div class="btn-group btn-group-sm" id="checkbox-confirmed-group">
                                    <button type="button" class="btn {if $request->opr_contacted_client}btn-success{else}btn-outline-secondary{/if}" data-value="1">Да</button>
                                    <button type="button" class="btn {if !$request->opr_contacted_client}btn-danger{else}btn-outline-secondary{/if}" data-value="0">Нет</button>
                                </div>
                                <input type="hidden" id="checkbox-confirmed" value="{$request->opr_contacted_client}">
                            </li>

                        </ul>
                    </div>
                </div>

                {* ---------- История запроса ---------- *}
                <div class="card mb-4" id="request-history-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">История запроса</h5>
                        <span class="badge badge-primary" id="request-history-count">{$request_history|@count|default:0}</span>
                    </div>
                    <div class="card-body p-4 overflow-auto" style="max-height: 550px">
                        <ul class="ticket-timeline" id="request-history-list">
                            {if $request_history|@count > 0}
                                {foreach $request_history as $index => $event}
                                    <li>
                                        <span class="timeline-icon" style="background-color: {cycle values='#007bff,#28a745,#ffc107,#dc3545,#17a2b8'}"></span>
                                        <div class="timeline-content">
                                            <p class="timeline-title">{if $event->action == 'creation'}Запрос создан{elseif $event->action == 'status_change'}Смена статуса{elseif $event->action == 'subject_change'}Смена темы{elseif $event->action == 'field_update'}Обновление поля{else}{$event->action|escape}{/if}</p>
                                            {if $event->action != 'creation'}<p class="timeline-text">{$event->details|escape}</p>{/if}
                                            <p class="timeline-date">
                                                {if $event->manager_name}{$event->manager_name|escape} &mdash; {/if}
                                                {$event->created_at|date_format:'%d.%m.%Y %H:%M'}
                                            </p>
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

            </div>
        </div>

    </div>
</div>

{include file='footer.tpl'}

{capture name='page_scripts'}
    <script src="design/manager/assets/plugins/select2/dist/js/select2.full.min.js"></script>
    <script src="design/{$settings->theme|escape}/js/cb_request_detail.js"></script>
{/capture}
