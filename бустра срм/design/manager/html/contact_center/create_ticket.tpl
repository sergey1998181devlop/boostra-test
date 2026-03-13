{$meta_title='Создание тикета' scope=parent}

{capture name='page_styles'}
    <link href="design/manager/css/create_ticket.css" rel="stylesheet"/>
    <link href="design/manager/assets/plugins/select2/dist/css/select2.min.css" rel="stylesheet"/>
{/capture}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="page-header">
            <div>
                <h3 class="page-title"><i class="mdi mdi-account-card-details mr-2"></i>Создание тикета</h3>
                <ol class="breadcrumb p-0 mb-0 mt-2">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="/tickets">Тикеты</a></li>
                    <li class="breadcrumb-item active">Создание тикета</li>
                </ol>
            </div>

            <div class="col-md-6 col-4 float-right">
                <form action="/tickets/save/" method="POST" id="add-ticket" class="js-order-item-form">
                    <input type="hidden" id="client_id" name="client_id" value="{if $client_info}{$client_info->id}{/if}"/>
                    <input type="hidden" id="order_id" name="order_id" value="{if $client_order}{$client_order->order_id}{/if}"/>
                    <input type="hidden" name="action" value="save"/>
                    <input type="hidden" id="manual_client_fio" name="client_fio" value=""/>
                    <input type="hidden" id="manual_client_phone" name="clientPhone" value=""/>
                    <input type="hidden" id="manual_client_birth" name="clientBirth" value=""/>
                    <input type="hidden" id="manual_client_email" name="clientEmail" value=""/>

                    <button type="submit" class="btn btn-success float-right">Создать тикет</button>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <!-- Основная форма тикета -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Основная информация</h5>
                    </div>

                    <div class="card-body">
                        <!-- Тема обращения -->
                        <div class="form-row">
                            <label for="subject">Тема обращения <span class="text-danger">*</span></label>
                            <div class="form-input">
                                <select id="subject" name="subject" class="form-control" required="required">
                                    <option value="" disabled selected>Выбрать тему</option>
                                    {foreach $subjects as $group}
                                        <optgroup label="{$group.name|escape}">
                                            {foreach $group.children as $subject}
                                                <option value="{$subject.id}">{$subject.name|escape}</option>
                                            {/foreach}
                                        </optgroup>
                                    {/foreach}
                                </select>
                                <div class="invalid-feedback">
                                    Пожалуйста, выберите тему обращения
                                </div>
                            </div>
                        </div>

                        <!-- Приоритет -->
                        <div class="form-row">
                            <label for="priority">Приоритет <span class="text-danger">*</span></label>
                            <div class="form-input">
                                <select name="priority_id" class="form-control" id="priority" required>
                                    <option value="" selected>Не выбрано</option>
                                    {foreach $priorities as $priority}
                                        <option value="{$priority->id}">{$priority->name|escape}</option>
                                    {/foreach}
                                </select>
                                <div class="invalid-feedback">
                                    Пожалуйста, укажите приоритет
                                </div>
                            </div>
                        </div>

                        <!-- Канал коммуникации -->
                        <div class="form-row">
                            <label for="chanel">Канал коммуникации <span class="text-danger">*</span></label>
                            <div class="form-input">
                                <select name="chanel" class="form-control" required="required">
                                    <option value="" disabled selected>Выберите канал</option>
                                    {foreach $channels as $channel}
                                        <option value="{$channel->id}">{$channel->name|escape}</option>
                                    {/foreach}
                                </select>
                                <div class="invalid-feedback">
                                    Пожалуйста, выберите канал коммуникации
                                </div>
                            </div>
                        </div>

                        <!-- Выбор компании -->
                        <div class="form-row">
                            <label for="company">Выбор компании <span class="text-danger">*</span></label>
                            <div class="form-input">
                                <select name="company" class="form-control" required="required">
                                    <option value="" disabled selected>Выберите компанию</option>
                                    {foreach $companies as $company}
                                        <option value="{$company->id}">{$company->name|escape}</option>
                                    {/foreach}
                                </select>
                                <div class="invalid-feedback">
                                    Пожалуйста, выберите компанию
                                </div>
                            </div>
                        </div>

                        <!-- Статус клиента -->
                        <div class="form-row">
                            <label for="client_status">Статус клиента <span class="text-danger">*</span></label>
                            <div class="form-input">
                                <select name="client_status" class="form-control" required="required">
                                    <option value="" disabled selected>Выберите статус</option>
                                    <option value="new">НК (Новый клиент)</option>
                                    <option value="old">ПК (Повторный клиент)</option>
                                </select>
                                <div class="invalid-feedback">
                                    Пожалуйста, выберите статус клиента
                                </div>
                            </div>
                        </div>

                        <!-- Исполнитель -->
                        <div class="form-row">
                            <label for="manager_id">Исполнитель <span class="text-danger">*</span></label>
                            <div class="form-input">
                                <select class="form-control" name="manager_id" id="manager_id" required="required">
                                    <option value="" disabled selected>Выберите исполнителя</option>
                                    {foreach $managers AS $manag}
                                        <option value='{$manag->id}' {if $manag->id==$manager_data->id}selected{/if}>{$manag->name}</option>
                                    {/foreach}
                                </select>
                                <div class="invalid-feedback">
                                    Пожалуйста, выберите исполнителя
                                </div>
                            </div>
                        </div>

                        <!-- Источник жалобы -->
                        <div class="form-row">
                            <label for="source">Источник</label>
                            <div class="form-input">
                                <input type="text" name="source" id="source" class="form-control" placeholder="Источник жалобы">
                            </div>
                        </div>

                        <div class="form-row">
                            <label class="form-check-label" for="is_add_ticket_subject">
                                <input type="checkbox" id="is_add_ticket_subject" name="is_add_ticket_subject"
                                       onclick="$('#additional_subject_id').prop('disabled',!$(this).prop('checked'))"> Продублировать тикет
                            </label>
                            <div class="form-input">
                                <select id="additional_subject_id" name="additional_subject_id" class="form-control" disabled>
                                    <option value="" disabled selected>Выбрать тему</option>
                                    {foreach $subjects as $group}
                                        <optgroup label="{$group.name|escape}">
                                            {foreach $group.children as $subject}
                                                <option value="{$subject.id}">{$subject.name|escape}</option>
                                            {/foreach}
                                        </optgroup>
                                    {/foreach}
                                </select>
                                <div class="invalid-feedback">
                                    Пожалуйста, выберите тему обращения
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <label for="description">Описание <span class="text-danger">*</span></label>
                            <div class="form-input">
                                <textarea
                                        class="form-control"
                                        name="description"
                                        id="description"
                                        rows="6"
                                        required="required"
                                        placeholder="Введите подробное описание проблемы или обращения клиента..."
                                ></textarea>
                                <div class="invalid-feedback">
                                    Пожалуйста, введите описание обращения
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" id="is_repeat" name="is_repeat">
                                <label class="form-check-label" for="is_repeat">Повторное обращение</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Блок для отображения предыдущих тикетов клиента -->
                <div class="card mt-4 {if !$client_tickets}d-none{/if}" id="client-tickets-section">
                    <div class="card-header">
                        <h5 class="mb-0">Предыдущие тикеты клиента</h5>
                    </div>
                    <div>
                        <div class="table-responsive">
                            <table class="data-table" id="client-tickets-table">
                                <thead>
                                <tr>
                                    <th>№</th>
                                    <th>Тема</th>
                                    <th>Ответственный</th>
                                    <th>Статус</th>
                                    <th>Дата</th>
                                </tr>
                                </thead>
                                <tbody>
                                {if $client_tickets}
                                    {foreach $client_tickets as $ticket}
                                        <tr>
                                            <td><a href="/tickets/{$ticket->id}">{$ticket->id}</a></td>
                                            <td>{$ticket->subject_name}</td>
                                            <td>{$ticket->manager_name}</td>
                                            <td>{$ticket->status_name}</td>
                                            <td>{$ticket->created_at}</td>
                                        </tr>
                                    {/foreach}
                                {/if}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Блок ручного поиска клиента (показывается, только если клиент не передан в URL) -->
                <div id="manual-search-block" class="card mb-4 {if $client_info}d-none{/if}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Поиск клиента</h5>
                        <div>
                            <!-- Кнопка "Клиента нет в системе" (показывается по умолчанию) -->
                            <a href="javascript:void(0);" id="no-client-button" class="btn btn-link text-primary" onclick="showManualClientForm()">
                                Клиента нет в системе
                            </a>

                            <!-- Кнопка "Поиск по номеру телефона" (показывается когда открыта форма ручного ввода) -->
                            <a href="javascript:void(0);" id="search-by-phone-button" class="btn btn-link text-primary d-none" onclick="showPhoneSearchForm()">
                                Поиск по номеру телефона
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Поиск по телефону -->
                        <div id="phone-search-form">
                            <div class="form-row">
                                <label for="clientPhone">Номер телефона</label>
                                <div class="form-input d-flex">
                                    <input type="text" id="clientPhone" name="clientPhone" class="form-control"
                                           placeholder="+7 (___) ___-__-__" data-inputmask="'mask': '+7 (999) 999-99-99'">
                                    <button type="button" class="btn btn-info ml-2 search-icon-btn" onclick="searchByPhone()">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <button type="button" class="btn btn-secondary ml-2 search-clear-btn d-none" onclick="cleanSearchBlock()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Форма ручного ввода данных клиента -->
                        <div id="manual-client-form" class="d-none">
                            <div class="form-row">
                                <label for="manualClientFio">ФИО клиента <span class="text-danger">*</span></label>
                                <div class="form-input">
                                    <input type="text" id="manualClientFio" name="manual_client_fio" class="form-control" required>
                                    <div class="invalid-feedback">Пожалуйста, введите ФИО клиента</div>
                                </div>
                            </div>
                            <div class="form-row">
                                <label for="manualClientBirth">Дата рождения</label>
                                <div class="form-input">
                                    <input type="text" id="manualClientBirth" name="manual_client_birth" class="form-control"
                                           placeholder="дд.мм.гггг">
                                </div>
                            </div>
                            <div class="form-row">
                                <label for="manualClientPhone">Телефон <span class="text-danger">*</span></label>
                                <div class="form-input">
                                    <input type="text" id="manualClientPhone" name="manual_client_phone" class="form-control"
                                           placeholder="+7 (___) ___-__-__" required>
                                    <div class="invalid-feedback">Пожалуйста, введите номер телефона клиента</div>
                                </div>
                            </div>
                            <div class="form-row">
                                <label for="manualClientEmail">E-mail</label>
                                <div class="form-input">
                                    <input type="email" id="manualClientEmail" name="manual_client_email" class="form-control"
                                           placeholder="client@mail.com">
                                    <div class="invalid-feedback">Пожалуйста, введите E-mail клиента</div>
                                </div>
                            </div>
                        </div>

                        <!-- Информация о найденном клиенте (скрыта по умолчанию) -->
                        <div id="client-info-block" class="mt-3 d-none">
                            <div class="form-row">
                                <label>ФИО клиента</label>
                                <div class="form-input">
                                    <input type="text" id="clientFioInputNewTicket" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="form-row">
                                <label>Дата рождения</label>
                                <div class="form-input">
                                    <input type="text" id="clientDateBirth" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="text-right mt-2">
                                <button type="button" class="btn btn-success" onclick="confirmClientAttachment()">
                                    <i class="fas fa-check mr-1"></i>Подтвердить привязку
                                </button>
                                <button type="button" class="btn btn-secondary ml-2" onclick="cleanSearchBlock()">
                                    <i class="fas fa-times mr-1"></i>Отмена
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Блок информации о клиенте, введенном вручную -->
                <div id="manual-client-info" class="alert alert-info d-none">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Данные клиента:</strong> <span class="manual-client-name"></span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="changeClient()">
                            <i class="fas fa-pen mr-1"></i>Изменить
                        </button>
                    </div>
                </div>
                
                <!-- Карточка с информацией о клиенте -->
                <div class="card mb-4 {if !$client_info}d-none{/if}" id="client-info-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Клиент</h5>

                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="changeClient()">
                            <i class="fas fa-unlink mr-1"></i>Отвязать
                        </button>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fas fa-user"></i>
                                <span class="ml-2">ФИО:</span>
                                <a href="{if $client_info}/client/{$client_info->id}{else}javascript:void(0){/if}" target="_blank" class="float-right client-fullname">
                                    {if $client_info}{$client_info->lastname} {$client_info->firstname} {$client_info->patronymic}{/if}
                                </a>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-birthday-cake"></i>
                                <span class="ml-2">Дата рождения:</span>
                                <span class="float-right client-birth">{if $client_info}{$client_info->birth}{/if}</span>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-phone"></i>
                                <span class="ml-2">Телефон:</span>
                                <span class="float-right client-phone">
                                    {if $client_info}<a href="tel:{$client_info->phone_mobile|escape}" style="color: white;">{$client_info->phone_mobile|escape}</a>{/if}
                                </span>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-envelope"></i>
                                <span class="ml-2">E-mail:</span>
                                <span class="float-right client-email">
                                    {if $client_info}<a href="mailto:{$client_info->email|escape}" style="color: white;">{$client_info->email|escape}</a>{/if}
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Карточка со списком займов клиента -->
                <div class="card mt-4 d-none" id="client-loans-section">
                    <div class="card-header">
                        <h5 class="mb-0">Займы клиента</h5>
                    </div>
                    <div>
                        <div class="table-responsive">
                            <table class="data-table" id="client-loans-table">
                                <thead>
                                <tr>
                                    <th>№</th>
                                    <th>Сумма</th>
                                    <th>Дата</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                                </thead>
                                <tbody>
                                <!-- Здесь будут отображаться займы клиента -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Карточка с информацией о займе -->
                <div class="card mb-4 {if !isset($client_order)}d-none{/if}" id="loan-info-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Займ</h5>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="detachLoan()">
                            <i class="fas fa-unlink mr-1"></i>Отвязать займ
                        </button>
                    </div>
                    <div class="card-body">
                        {if isset($client_order)}
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-hashtag"></i>
                                    <span class="ml-2">Номер:</span>
                                    <a class="float-right loan-id" href="/order/{$client_order->order_id}"
                                       target="_blank">{$client_order->order_id}</a>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-ruble-sign"></i>
                                    <span class="ml-2">Сумма:</span>
                                    <span class="float-right loan-amount">{$client_order->amount} р.</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-calendar"></i>
                                    <span class="ml-2">Дата:</span>
                                    <span class="float-right loan-date">{$client_order->date|date}</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-calendar-minus"></i>
                                    <span class="ml-2">Статус:</span>
                                    <span class="float-right loan-status">{$client_order->status_1c}</span>
                                </li>
                            </ul>
                        {else}
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-hashtag"></i>
                                    <span class="ml-2">Номер:</span>
                                    <a class="float-right loan-id" href="#" target="_blank"></a>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-ruble-sign"></i>
                                    <span class="ml-2">Сумма:</span>
                                    <span class="float-right loan-amount"></span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-calendar"></i>
                                    <span class="ml-2">Дата:</span>
                                    <span class="float-right loan-date"></span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-calendar-minus"></i>
                                    <span class="ml-2">Статус:</span>
                                    <span class="float-right loan-status"></span>
                                </li>
                            </ul>
                        {/if}

                        <div class="form-group form-check">
                            <input type="checkbox"
                                   class="form-check-input" 
                                   id="remove_from_load"
                                   name="remove_from_load"
                            >
                            <label class="form-check-label" for="remove_from_load">Снять с нагрузки</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{capture name='page_scripts'}
    <script src="design/manager/assets/plugins/inputmask/dist/min/jquery.inputmask.bundle.min.js"></script>
    <script src="design/manager/assets/plugins/select2/dist/js/select2.full.min.js"></script>
    <script src="design/manager/assets/plugins/select2/dist/js/i18n/ru.js"></script>
    <script src="design/manager/js/create_ticket.js?ver=1.2"></script>
{/capture}