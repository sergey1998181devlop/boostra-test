<div id="tab_order" class="tab-pane active" role="tabpanel">

    <div class="row" id="order_wrapper">
        <div class="col-lg-12">
            <div class="card card-outline-info">

                <div class="card-body">

                    {if !empty($user_data) && !empty($user_data['gray_list'])}
                        <h4 class="text-danger">Подозрение в мошенничестве</h4>
                    {/if}
                    {* Террористические списки *}
                    {if !empty($user_data) && !empty($user_data['terrorist_status']) && $user_data['terrorist_status'] == 1}
                        <div class="col-md-12 mb-3">
                                        <span class="label label-danger" style="display:block; padding: 10px 12px;">
                                            <h4 class="mb-1" style="margin:0;">
                                                Клиент найден в террористических списках
                                                {if !empty($user_data['terrorist_scoring_id'])}
                                                    <button type="button"
                                                            class="btn btn-xs btn-outline-light ml-2 js-terrorist-details"
                                                            data-scoring-id="{$user_data['terrorist_scoring_id']}"
                                                            style="padding: 2px 8px; font-size: 11px;"
                                                    >
                                                        <i class="fa fa-search"></i> Детали
                                                    </button>

                                                {/if}
                                            </h4>
                                            <small style="opacity:.9;">
                                                Требуется ручная проверка совпадений (ФИО/ДР/ИНН/СНИЛС) по перечням.
                                            </small>
                                        </span>
                        </div>
                    {/if}


                    {if !empty($region_ip_mismatch)}
                        <h4 class="text-danger">Несовпадение по ip региона</h4>
                    {/if}

                    {if !empty($user_data) && !empty($user_data['old_phones'])}
                        <h4 class="text-danger">Клиент сменил номер телефона, старый телефон:
                            {$user_data['old_phones']|json_decode|first}
                        </h4>
                    {/if}

                    {if $is_order_from_akvarius && $manager->role != 'verificator_minus'}
                        <h4 class="text-warning">Заявка из Аквариуса</h4>
                    {/if}

                    {if $is_short_flow}
                        <h4 class="text-success">
                            Короткая регистрация
                            {if !$is_short_flow_data_confirm}<span class="text-danger"> - Клиент не уверен в распознанных данных</span>{/if}
                        </h4>
                    {/if}

                    {if $has_autoconfirm_sms}
                        <h4 class="text-info">Клиент подписал Автовыдачу</h4>
                    {/if}

                    {if $is_autoconfirm}
                        <h4 class="text-success">Автовыдача</h4>
                    {/if}

                    {if $order_data['self_employee_order'] == 1}
                    <h4 class="text-warning">Целевой займ самозанятого</h4>
                    {/if}

                    {if $order_data['hyper_c_approve_amount']}
                        <h4 class="text-success">Hyper-C автоодобрение</h4>
                    {/if}

                    {if $is_samara_office && $manager->role != 'verificator_minus'}
                        <h4 class="text-danger">Заявка из офиса (Потенциально ЦБ)</h4>
                    {/if}

                    {if $inn_not_found}
                        <h4 class="text-danger">ИНН не найден - необходимо скорректировать данные и перезапустить акси</h4>
                    {/if}

                    {if $sbp_accounts}
                        <h4 class="text-success">Привязан счет СБП</h4>
                    {/if}

                    {if $order_data['is_order_decision_with_hyper_c']}
                        <h4 class="text-success">Решение принято с учетом Hyper-C</h4>
                    {elseif $has_hyper_c_scoring}
                        <h4 class="text-success">Заявка идёт через Hyper-C</h4>
                    {/if}

                    <div class="form-body">
                        <div class="row pt-3 js-order-head" data-order="{$order->order_id}">

                            <div class="js-comments-block-order col-md-12">{display_comments block='order'}</div>

                            {if !$skip_credit_rating && !$accept_reject_orders}
                                <div class="col-md-12 mb-3 animate-flashing">
                                                    <span class="label label-danger">
                                                        <h4>Заявка на стадии предложения услуги "Кредитный рейтинг". Обработка заявки заморожена на время принятия решения клиентом</h4>
                                                    </span>
                                </div>
                            {/if}

                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="control-label col-md-6">Номер заявки:</label>
                                            <div class="col-md-6">
                                                <p class="form-control-static"><strong>{$order->order_id}</strong></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="control-label col-md-6">Номер заявки 1C:</label>
                                            <div class="col-md-6">
                                                <p class="form-control-static">{$order->id_1c}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="control-label col-md-6">Дата и время подачи заявки:</label>
                                            <div class="col-md-6">
                                                {$order->date|date} {$order->date|time}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="control-label col-md-6">Статус заявки:</label>
                                            <div class="col-md-6">
                                                <p class="form-control-static">
                                                    {$order->status_1c}
                                                    {if !empty($order->id_1c)}
                                                        <button
                                                                type="button"
                                                                class="btn btn-xs btn-outline-primary ml-2 sync-order-status-btn"
                                                                data-order-id="{$order->order_id}"
                                                                title="Синхронизировать статус с 1С"
                                                                style="padding: 2px 8px; font-size: 11px;"
                                                        >
                                                            <i class="fa fa-refresh"></i> Обновить
                                                        </button>
                                                    {/if}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="control-label col-md-6">Дата и время принятия заявки:</label>
                                            <div class="col-md-6">
                                                <p class="form-control-static">
                                                    {if $order->accept_date}
                                                        {$order->accept_date|date} {$order->accept_date|time}
                                                    {/if}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="control-label col-md-6">Статус заявки CRM:</label>
                                            <div class="col-md-6">
                                                <p class="form-control-static mb-0">
                                                    {if $order->status == 0}<span class="label label-rounded label-inverse">Заполнение</span>
                                                    {elseif $order->status == 1}<span class="label label-rounded label-info">Новая</span>
                                                    {elseif $order->status == 2}<span class="label label-rounded label-success">Одобрена</span>
                                                    {elseif $order->status == 3}<span class="label label-rounded label-danger">Отказ</span>
                                                {if $order->reason}<div><small class="text-danger">{if $manager->role=='verificator_minus'}{$order->reason->client_name|escape}{else}{$order->reason->admin_name|escape}{/if}</small></div>{/if}
                                                {elseif $order->status == 4}<span class="label label-rounded label-warning">Отказался сам</span>
                                                {elseif $order->status == 5}<span class="label label-rounded label-inverse">На исправлении</span>
                                                {elseif $order->status == 6}<span class="label label-rounded label-info">Исправлена</span>
                                                {elseif $order->status == 7}<span class="label label-rounded label-warning">Ожидание</span>
                                                {elseif $order->status == 8}<span class="label label-rounded label-primary">Подписан</span>
                                                {elseif $order->status == 9}<span class="label label-rounded label-primary">Готов к выдаче</span>
                                                {elseif $order->status == 10}<span class="label label-rounded label-success">Выдан</span>
                                                {elseif $order->status == 11}<span class="label label-rounded label-danger">Не удалось выдать</span>
                                                {elseif $order->status == 13}<span class="label label-rounded label-warning">Выдача отложена</span>
                                                {elseif $order->status == 14}<span class="label label-rounded label-success">Предварительно одобрена</span>
                                                {elseif $order->status == 15}<span class="label label-rounded label-warning">Автоподписание</span>
                                                {elseif $order->status == 17}<span class="label label-rounded label-success">Охлаждение</span>
                                                {elseif $order->status == 18}<span class="label label-rounded label-inverse">Ожидание ПДН</span>
                                                {/if}
                                                {if !empty($order_data['leadgid_scorista_reject'])}<small class="text-secondary">Принудительный отказ по настройке</small>{/if}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="control-label col-md-6">Источник:</label>
                                            <div class="col-md-6">
                                                <p class="form-control-static">
                                                    {if $order->utm_source == 'Boostra'}<span class="label label-inverse">{$order->utm_source|escape}</span>
                                                    {elseif $order->utm_source == 'sms' && $order->webmaster_id=='7777'}<span class="label label-primary">Акция СД</span>
                                                    {elseif $order->utm_source == 'sms'}<span class="label label-primary">lvtraff</span>
                                                    {elseif $order->utm_source == 'cf'}<span class="label label-danger">ЦФ</span>
                                                    {elseif $order->utm_source}<span class="label label-warning">{$order->utm_source|escape} </span><br />{$order->webmaster_id}
                                                    {/if}
                                                    <br />
                                                    <strong>{$order->utm_term|escape}</strong>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="control-label col-md-6">УИД заявки</label>
                                            <div class="col-md-6">
                                                <p class="form-control-static"><small>{$order->order_uid|escape}</small></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="control-label col-md-6">IP при регистрации</label>
                                            <div class="col-md-6">
                                                <p class="form-control-static"><small>{$order->user->reg_ip|escape}</small></p>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="control-label col-md-6">IP при входе</label>
                                            <div class="col-md-6">
                                                <p class="form-control-static"><small>{$order->user->last_ip|escape}</small></p>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="control-label col-md-6">IP при подаче заявки</label>
                                            <div class="col-md-6">
                                                <p class="form-control-static"><small>{$order->ip|escape}</small></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        {if !empty($order->pdn_nkbi_loan)}
                                            <div class="form-group row">
                                                <label class="control-label col-md-6">ПДН</label>
                                                <div class="col-md-6">
                                                    <p class="form-control-static">
                                                        <strong class="text-warning">
                                                            {$order->pdn_nkbi_loan|round:1} %
                                                        </strong>
                                                    </p>
                                                </div>
                                            </div>
                                        {/if}
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="control-label col-md-6">Причина займа:</label>
                                            <div class="col-md-6">
                                                <p class="form-control-static">{if isset($order_data['loan_purpose']) && $order_data['loan_purpose'] !== ''}{$order_data['loan_purpose']|escape}{else}—{/if}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group row">
                                        </div>
                                    </div>

                                    {if in_array($order->status, [1, 5, 6, 7]) }
                                        <div class="col-md-6 pr-0 pl-0 js-order-status-block" {if !$order->manager_id || ($manager->id != $order->manager_id && !in_array($manager->role, ['admin', 'developer', 'opr', 'ts_operator']))}style="display:none"{/if}>
                                            <button type="button" class="btn btn-rounded btn-success js-approve-order js-event-add-click" data-event="4" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}"><i class="mdi mdi-checkbox-marked-circle-outline"></i>&nbsp;Одобрить</button>
                                            <button type="button" class="btn btn-rounded btn-danger js-reject-order js-event-add-click ml-2" data-event="4" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}"><i class="mdi mdi-alert-circle-outline"></i>&nbsp;Отказать</button>
                                            {if $order->status != 7}
                                                <button type="button" class="btn btn-rounded btn-warning js-waiting-order ml-2" data-order="{$order->order_id}"><i class="mdi mdi-clock-fast"></i>&nbsp;Ожидание</button>
                                            {/if}

                                            {if $has_pay_credit_rating && !$has_last_scorista_scoring}
                                                <p class="warning-text" style="display: none;">
                                                    <b>У клиента подключен КР, необходимо провести проверку скористы!</b>
                                                </p>
                                            {/if}

                                            {if !$order_divide && $manager->role != 'verificator_minus'}
                                                <div class="col-md-12 pl-md-0 my-3">
                                                    <button type="button" class="btn btn-outline-warning" data-toggle="modal" data-target="#modal_divide__order">
                                                        Разделить займ <span class="mdi mdi-animation"></span>
                                                    </button>
                                                </div>
                                                <div id="modal_divide__order" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                                                    <div class="modal-dialog modal-md">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h4 class="modal-title"><u>Какую сумму одобрить
                                                                        клиенту в первом займе?</u></h4>
                                                                <button type="button" class="close"
                                                                        data-dismiss="modal" aria-hidden="true">×
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p class="text-muted">
                                                                    Напоминаем: После разделения займа, на
                                                                    следующий день система отправит автоодобрение
                                                                    клиенту с недостающей суммой изначальной заявки
                                                                </p>
                                                                <form method="POST" id="divide_form">
                                                                    <input type="hidden" name="order_id" value="{$order->order_id}" />
                                                                    <input type="hidden" name="action" value="divide_order" />

                                                                    <div class="form-group">
                                                                        <input type="text" class="ion_slider__input" data-step="100" name="amount" value="9000" data-min="1000" data-max="9900" />
                                                                    </div>
                                                                </form>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-danger"
                                                                        data-dismiss="modal">Отмена
                                                                </button>
                                                                <button onclick="divideOrder()" type="button" class="btn btn-success">
                                                                    Подтвердить
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/if}
                                        </div>
                                        <input type="hidden" value="{$has_pay_credit_rating}" name="has_pay_credit_rating" />
                                        <input type="hidden" value="{$has_last_scorista_scoring}" name="has_last_scorista_scoring" />
                                        <input type="hidden" value="{$skip_credit_rating}" name="skip_credit_rating" />
                                        <input type="hidden" value="{$accept_reject_orders}" name="accept_reject_orders" />
                                        <input type="hidden" value="{$is_approve_order}" name="is_approve_order" />
                                        <input type="hidden" value="{$has_hyper_c_scoring}" name="has_hyper_c_scoring" />

                                        <div class="col-md-6 js-order-accept-block" {if $order->manager_id}style="display:none"{/if}>
                                            <button type="button" class="btn btn-lg btn-rounded btn-info js-accept-order js-event-add-click" data-event="2" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}"> <i class="fas fa-hospital-symbol"></i>&nbsp;&nbsp; Принять</button>
                                        </div>
                                    {/if}

                                    {if $user_offer}
                                        <div class="col-md-12">
                                            <div class="alert alert-danger">
                                                <h3>У клиента есть спецпредложение!</h3>
                                                {if $user_offer->type == 'percents'}Сниженная процентная ставка по кредиту до {$user_offer->value}%{/if}
                                                {if $user_offer->type == 'amount'}Повышенная сумма займа на {$user_offer->value}руб{/if}
                                                {if $user_offer->type == 'insure'}Сниженная ставка на страхование до {$user_offer->value}%{/if}
                                            </div>
                                        </div>
                                    {/if}

                                </div>
                            </div>
                            <div class="col-md-3">
                                <input type="hidden" data-manager="{$manager->id}">
                                <a href="/tickets/create?client_id={$order->user_id}&order_id={$order->order_id}" target="_blank" class="btn btn-success mb-3">Создать тикет</a>
                                {if in_array($manager->role, ['admin', 'developer', 'chief_verificator', 'verificator', 'edit_verificator', 'opr', 'ts_operator'])}
                                    <select class="form-control mb-2 js-order-manager {if in_array($manager->role, ['verificator', 'edit_verificator'])}js-need-comment{/if}" data-order="{$order->order_id}">
                                        <option value="0"></option>
                                        {foreach $managers as $m}
                                            <option value="{$m->id}" {if $m->id == $order->manager_id}selected{/if}>{$m->name|escape}</option>
                                        {/foreach}
                                    </select>
                                {else}

                                    <p>Менеджер: {$managers[$order->manager_id]->name|escape}</p>
                                {/if}

                                <div class="pb-2 js-maratorium-block">
                                    {if $order->maratorium_valid}
                                        <strong class="text-warning">Мораторий до {$order->maratorium_date|date} {$order->maratorium_date|time}</strong>
                                        <br />
                                        <small class="text-warning">{$maratoriums[$order->maratorium_id]->name}</small>
                                    {else}
                                        <button class="btn btn-block btn-warning " type="button" id="open_maratorium_modal">Добавить мораторий</button>
                                    {/if}
                                </div>

                                <div class="js-load-balance-block load-balance-block loading mb-2" data-order="{$order->order_id}" data-user="{$order->user_id}" data-pay="{if $order->pay_result}{$order->pay_result|escape}{/if}">
                                    <div class="js-load-balance-inner"></div>
                                </div>

                                {if $order->percent != 1}
                                    <h3 class="d-block p-2 label label-primary text-center mt-2">Участвует в акции: {1 * $order->percent}%</h3>
                                {/if}
                                {if in_array($manager->role, ['developer', 'boss_cc', 'chief_verificator', 'ts_operator']) || $manager->id==14}
                                    <div class="row">
                                        <div class="col-6">
                                            <button class="btn-block btn btn-primary js-resend-approve" data-order="{$order->order_id}">Повторить одобрение</button>
                                        </div>
                                        <div class="col-6">
                                            <button class="btn-block btn btn-danger js-set-tehotkaz" data-order="{$order->order_id}">Техотказ</button>
                                        </div>
                                    </div>
                                {/if}
                                <div class="row mt-2">
                                    <div class="col-6">
                                        {if empty($order_data['payment_deferment'])}
                                            <button class="btn-block btn btn-info" data-order="{$order->order_id}" data-target="#paymentDeferment" data-toggle="modal">Отсрочка за ФД</button>
                                        {else}
                                            <button class="btn-block btn btn-info" disabled>Ранее делалась отсрочка</button>
                                        {/if}
                                    </div>
                                    <div class="col-6">
                                        {if in_array($manager->role, ['developer', 'ts_operator'])}
                                            <button class="btn-block btn btn-info js-send-sms-lk send-sms" data-type="sms-lk"  data-order="{$order->order_id}" data-manager="{$manager->id}" >СМС-ЛК</button>
                                        {/if}
                                        <button class="btn-block btn btn-info btn-modal-send-sms" data-type="sms-prolongation" data-order="{$order->order_id}" data-manager="{$manager->id}" data-target="#sms-modal"  data-toggle="modal">СМС-пролонгация</button>
                                        <button class="btn-block btn btn-info btn-modal-send-sms" data-type="sms-payment" data-order="{$order->order_id}" data-manager="{$manager->id}" data-target="#sms-modal"  data-toggle="modal">СМС-оплата</button>
                                        {*                                                    <button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal">Open Modal</button>*}
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <button class="btn-block btn btn-primary" data-target="#generatePromoCodeModal" data-toggle="modal">Выдать промокод</button>
                                    </div>
                                </div>
                                {if $manager->role != 'verificator_minus'}
                                    <div class="row mt-2">
                                        <div class="col-12">
                                            <button class="btn-block btn btn-info btn-modal-disable_check_reports_for_loan"
                                                    data-order="{$order->order_id}"
                                                    data-manager="{$manager->id}"
                                                    data-target="#disable_check_reports_for_loan-modal"
                                                    data-toggle="modal">
                                                {if empty($order_data['disable_check_reports_for_loan'])}Отключить {else}Включить {/if}проверку ССП и КИ отчетов</button>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-12 js-robot-calls-block">
                                            {if $robot_calls_disabled}
                                                {if $robot_calls_disabled_until}<p class="small text-muted mb-1">
                                                    Отключено до {$robot_calls_disabled_until}</p>{/if}
                                                <button type="button"
                                                        class="btn-block btn btn-outline-success js-order-enable-robot-calls"
                                                        data-order-id="{$order->order_id}"
                                                        title="Включить исходящие звонки робота">
                                                    <i class="fas fa-phone"></i> Включить звонки робота
                                                </button>
                                            {else}
                                                <button type="button"
                                                        class="btn-block btn btn-outline-danger js-order-disable-robot-calls"
                                                        data-order-id="{$order->order_id}"
                                                        data-target="#disable_robot_calls_modal" data-toggle="modal"
                                                        title="Добавить номера клиента в DNC-лист Voximplant">
                                                    <i class="fas fa-phone-slash"></i> Отключить звонки робота
                                                </button>
                                            {/if}
                                        </div>
                                    </div>
                                {/if}
                            </div>
                        </div>

                        <div data-order="{$order->order_id}" class="" >
                            <h3 class="box-title mt-5">
                                <a href="javascript:void(0);" data-toggle="collapse" data-target="#sent_sms">
                                    <span>Смс сообщения:</span>
                                </a>
                                <a class="float-right btn btn-sm btn-outline-primary btn-rounded js-open-sms-modal" data-user="{$order->user_id}">Отправить</a>
                                {if $order->accept_try>4}<a class="float-right btn btn-sm btn-outline-warning btn-rounded js-unblock-asp-modal m-r-10"  data-toggle="modal" data-target="#unblock-asp-modal" id="unblock-asp-btn">Разблокировать АСП</a>{/if}
                            </h3>
                            <hr>
                            <div class="row {if !$open_scorings}collapse{/if}" id="sent_sms">
                                {if $sms_messages}
                                    <table class="table">
                                        <tr>
                                            <th>Дата</th>
                                            <th>Сообщение</th>
                                            <th>Статус</th>
                                        </tr>
                                        {foreach $sms_messages as $sm}
                                            <tr>
                                                <td>{$sm->created|date} {$sm->created|time}</td>
                                                <td>{$sm->message}</td>
                                                <td>{$sm->send_status}</td>
                                            </tr>
                                        {/foreach}
                                    </table>
                                {else}
                                    <div class="col-12">
                                        <h4>Нет отправленных сообщений</h4>
                                    </div>
                                {/if}
                            </div>
                        </div>

                        {include file="order/scorings_block.tpl" open_scorings=1}



                        <div class="row">

                            <!-- Сумма и период заявки -->
                            <form action="{url}" class="col-md-6 js-order-item-form mb-3" id="period_amount_form">

                                <input type="hidden" name="action" value="amount" />
                                <input type="hidden" name="order_id" value="{$order->order_id}" />
                                <input type="hidden" name="user_id" value="{$order->user_id}" />

                                <h3 class="card-title">
                                    {if $is_developer || (in_array($order->status, [1, 5, 6, 7]) && ($manager->id == $order->manager_id)) || (in_array($order->status, [1, 5, 6, 2, 7, 3, 10, 11]) && (in_array($manager->role, ['admin', 'developer', 'chief_verificator','edit_verificator', 'contact_center_plus', 'opr', 'ts_operator'])) && !$order_divide)}
                                        <a href="javascript:void(0);" id="open_edit_amount_modal" class="js-edit-form js-event-add-click" data-event="9" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}">
                                            <span>Сумма и срок заявки</span>
                                        </a>
                                    {else}
                                        <span>Сумма и срок заявки</span>
                                    {/if}
                                    <a href="javascript:void(0);" class="ml-3 js-open-comment-form" data-block="amount">
                                        <i class="mdi mdi-comment-text"></i>
                                    </a>
                                </h3>
                                <hr>

                                <div class="row view-block {if $amount_error}hide{/if}">

                                    <div class="js-comments-block-amount">{display_comments block='amount'}</div>

                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <label class="control-label col-md-4">Сумма:</label>
                                            <div class="col-md-8">
                                                <p class="form-control-static">
                                                    <strong>{$order->amount} руб</strong>
                                                    {if $order->loan_type == 'IL'}
                                                        <span class="label label-info">IL</span>
                                                    {else}
                                                        {if $order_data['rcl_loan']}
                                                            <span class="label label-success">ВКЛ</span>
                                                        {/if}
                                                        <span class="label label-primary">PDL</span>
                                                    {/if}

                                                    {if $axi_amount}
                                                        <label class="label label-primary">
                                                            <span title="Акси: рекомендуемая сумма">Акси одобрил: {$axi_amount} руб</span>
                                                        </label>
                                                    {/if}

                                                    {if $order_data['increase_amount_for_nnu']}
                                                        <label class="label label-success">
                                                            <span title="NNU: рекомендуемая сумма">NNU: {$order_data['increase_amount_for_nnu']} руб</span>
                                                        </label>
                                                    {/if}
                                                <span class="js-scor_amount-block">
                                                    <i class="fa fa-spinner fa-spin"></i>
                                                </span>
                                                    {if $axi_amount && $manager->role != 'verificator_minus'}
                                                        <br /><small><i class="text-primary" title="Аксиоматика">Аксиоматика: {$axi_amount} руб</i></small>
                                                    {/if}
                                                <span class="js-installment_scor_amount-block">
                                                </span>
                                                    {if $order_divide && $manager->role != 'verificator_minus'}
                                                        <span class="badge badge-warning">Внимание, займ разделен!</span>
                                                    {/if}

                                                <span class="js-scor_message-block">
                                                </span>

                                                    {if !empty($order_data['fake_scorista_amount']) && $manager->role != 'verificator_minus'}
                                                        <br /><small><i class="text-primary" title="Акси">Акси: {$order_data['fake_scorista_amount']}</i></small>
                                                    {/if}

                                                <span class="js-installment_scor_message-block">
                                                </span>

                                                    <span class="js-scor_amount_increased-block">
                                                    </span>
                                                    {if $dbrain_statistic->decision == 'Одобрение'}
                                                        <br /><span class="text-success">Dbrain решение: {$dbrain_statistic->reason}</span>
                                                    {/if}

                                                    {if $order_data['hyper_c_approve_amount']}
                                                        <label class="label label-primary">
                                                            <span title="Hyper-C: рекомендуемая сумма">Hyper-C одобрил: {$order_data['hyper_c_approve_amount']} руб</span>
                                                        </label>
                                                    {/if}

                                                    {if $order_data['scorista_source'] && $order_data['scorista_source'] == 'org_switch_parent'}
                                                        <small><i class="text-primary">Скориста импортирована из другой заявки клиента</i></small>
                                                    {/if}

                                                    {if $order_data['rcl_max_amount']}
                                                        <br /><small><i class="text-success" title="Сумма лимита ВКЛ">Сумма лимита ВКЛ: {$order_data['rcl_max_amount']} руб</i></small>
                                                    {/if}
                                                    {if $nbki_score && ($nbki_score > $min_nbki_score || $link_order_scorista) }
                                                        <br /><small><i class="text-primary" title="NBKI credit limit">NBKI credit limit = 30 000 руб.</i></small>
                                                        <br /><small><i class="text-primary" title="Рекомендованная сумма">Рекомендовано выдать заемщику <strong>{$order->amount} руб.</strong></i></small>
                                                    {/if}
                                                    {if $link_order_scorista}
                                                        <br /><small><i class="text-success" title="Одобренная Скориста">Найдена одобренная Скориста:<br /> <a href="{$link_order_scorista}" target="_blank" class="text-primary"><strong>перейти к заявке</strong></a></i></small>
                                                    {/if}

                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <label class="control-label col-md-4">Срок:</label>
                                            <div class="col-md-8">
                                                <p class="form-control-static">
                                                    {if $order->loan_type == 'IL'}
                                                        <strong>{$order->period/7} {($order->period/7)|plural:'неделя':'недель':'недели'}</strong>
                                                    {else}
                                                        <strong>{$order->period} {$order->period|plural:'день':'дней':'дня'}</strong>
                                                    {/if}
                                                    <span class="js-scor_period-block">
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    {if $self_employee_document}
                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <label class="control-label col-md-4">Подтверждение целевого займа:</label>
                                                    <div class="col-md-8">
                                                        <p class="form-control-static">
                                                            <a href="{$config->root_url}/ajax/download_self_employee_document.php?order_id={$order->order_id}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                <i class="fa fa-download"></i> {$self_employee_document.name|escape}
                                                            </a>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                    {/if}
                                    {if $order->organization_id != 17}
                                        {if $manager->role != 'verificator_minus'}
                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <label class="control-label col-md-4">Финансовый доктор:</label>
                                                    <div class="col-md-8">
                                                        <p class="form-control-static">
                                                            {if $order->is_user_credit_doctor}
                                                                <strong>Проставлен </strong><span class="label label-danger">{$credit_doctor_price} руб КД</span>
                                                            {else}
                                                                <strong>Не проставлен</strong>
                                                            {/if}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        {/if}
                                        <div class="col-md-12">
                                            <div class="form-group row">
                                                <label class="control-label col-md-4">Телемедицина:</label>
                                                <div class="col-md-8">
                                                    <p class="form-control-static">
                                                        {if isset($tv_medical_price)}
                                                            <strong>Проставлен </strong><span class="label label-danger">{$tv_medical_price} руб</span>
                                                        {else}
                                                            <strong>Не проставлен</strong>
                                                        {/if}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    {else}
                                        <div class="col-md-12">
                                            <div class="form-group row">
                                                <label class="control-label col-md-4">Безопасная сделка:</label>
                                                <div class="col-md-8">
                                                    <p class="form-control-static">
                                                        {if isset($safe_deal_price)}
                                                            <strong>Проставлен </strong><span class="label label-danger">{$safe_deal_price} руб БС</span>
                                                        {else}
                                                            <strong>Не проставлен</strong>
                                                        {/if}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    {/if}

                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <label class="control-label col-md-4">Карта:
                                                {if $order->card_type == 'card'}<span class="label label-danger">Карта</span>
                                                {elseif $order->card_type == 'sbp' && $order->card_id == 0 && $selected_bank}<span class="label label-info">Банк</span>
                                                {elseif $order->card_type == 'sbp'}<span class="label label-success">СБП</span>
                                                {elseif $order->card_type == 'virt'}<span class="label label-primary">Вирта</span>
                                                {/if}
                                            </label>
                                            <div class="col-md-8">
                                                <p class="form-control-static">
                                                    {if $order->card_type == 'sbp' && $order->card_id == 0 && $selected_bank}
                                                        <span class="label label-info">Выбран банк для выплаты по СБП: {$selected_bank->title}</span>
                                                    {elseif $order->card_type == 'sbp'}
                                                        {foreach $sbp_accounts as $sbp_account}
                                                            {if $order->card_id == $sbp_account->id}
                                                                СБП счет: {$sbp_account->title}
                                                                {break}
                                                            {/if}
                                                        {/foreach}
                                                    {else}
                                                        {foreach $card_list as $card}
                                                            {if $card->id == $order->card_id}
                                                                <strong>{$card->pan} </strong> {$card->expdate}
                                                                ({$organizations[$card->organization_id]->name|escape})
                                                                {if $card->deleted}<small class="text-danger">(Удалена)</small>{/if}
                                                                {break}
                                                            {/if}
                                                        {/foreach}
                                                    {/if}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="row edit-block {if !$amount_error}hide{/if}">
                                    {if $amount_error}
                                        <div class="col-md-12">
                                            <ul class="alert alert-danger">
                                                {if in_array('empty_amount', (array)$amount_error)}<li>Укажите сумму заявки!</li>
                                                {elseif in_array('amount_limit', (array)$amount_error)}<li>Превышена сумма займа! (30000)</li>
                                                {elseif in_array('empty_period', (array)$amount_error)}<li>Укажите срок заявки!</li>
                                                {elseif in_array('empty_card', (array)$amount_error)}  <li>Не выбрана карта!</li>
                                                {elseif in_array('timeout', (array)$amount_error)}  <li>Смена данных не возможна. Ожидайте, Выдача находится в таймауте!</li>
                                                {elseif in_array('signed_contract_amount', (array)$amount_error)}  <li>Не допускается изменение суммы после подписания контракта!</li>
                                                {elseif is_array($amount_error)}
                                                    {foreach $amount_error as $amount_error_item}<li>{$amount_error_item|escape}</li>{/foreach}
                                                {else}<li>{$amount_error|escape}</li>{/if}
                                            </ul>
                                        </div>
                                    {/if}
                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <label class="col-6 control-label">Тип займа:</label>
                                            <div class="col-6">
                                                <select name="loan_type" class="form-control js-loan-type-select">
                                                    <option value="PDL" {if $order->loan_type=='PDL'}selected{/if}>PDL</option>
                                                    <option value="IL"  {if $order->loan_type=='IL'}selected{/if} {if !$installment_scor_amount}style="display:none"{/if} class="js-installment-option">IL</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group row {if in_array('empty_amount', (array)$amount_error)}has-danger{/if}">
                                            <label class="col-6 control-label">Сумма:</label>
                                            <div class="col-6">
                                                <input type="text" name="amount" value="{$order->amount|escape}" class="form-control js-order-summ" placeholder="Сумма заявки" required="true" {if $order->status == 2 && !$is_developer} readonly {/if} />
                                                {if in_array('empty_amount', (array)$amount_error)}<small class="form-control-feedback">Укажите сумму заявки!</small>{/if}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="row form-group {if in_array('empty_period', (array)$amount_error)}has-danger{/if}">
                                            <label class="col-6 control-label">Срок:</label>
                                            <div class="col-6">
                                                <select name="period" class="form-control js-periods-select">
                                                    {if $order->utm_source == 'cross_order'}{assign var=max_pdl_period value=21}{else}{assign var=max_pdl_period value=16}{/if}
                                                    {section name=pdl_periods start=5 loop=$max_pdl_period+1 step=1}
                                                        <option class="js-pdl-periods" value="{$smarty.section.pdl_periods.index}" {if $order->loan_type=='IL'}style="display:none"{/if} {if $order->period==$smarty.section.pdl_periods.index}selected{/if}>
                                                            {$smarty.section.pdl_periods.index} {$smarty.section.loan_periods.index|plural:'день':'дней':'дня'}
                                                        </option>
                                                    {/section}
                                                    {section name=il_periods start=84 loop=(168+14) step=14}
                                                        <option class="js-il-periods" value="{$smarty.section.il_periods.index}" {if $order->loan_type=='PDL'}style="display:none"{/if} {if $order->period==$smarty.section.il_periods.index}selected{/if}>
                                                            {$smarty.section.il_periods.index/7} {($smarty.section.loan_periods.index/7)|plural:'неделя':'недель':'недели'}
                                                        </option>
                                                    {/section}
                                                </select>
                                                {if in_array('empty_period', (array)$amount_error)}<small class="form-control-feedback">Укажите срок заявки!</small>{/if}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="row form-group {if in_array('empty_period', (array)$amount_error)}has-danger{/if}">
                                            <label class="col-6 control-label">Карта:</label>
                                            <div class="col-6">
                                                {if in_array('change_card', $manager->permissions)}
                                                    <select class="form-control {if in_array($manager->role, ['verificator', 'edit_verificator', 'contact_center_plus'])}js-need-comment-card{/if} js-order-card-type" name="card_type">
                                                        <option value="card" {if $order->card_type=='card'}selected{/if}>Карта</option>
                                                        <option value="bank" {if $order->card_type == 'sbp' && $order->card_id == 0 && ($selected_bank || $default_selected_bank)}selected{/if}>Банк</option>
                                                        <option value="sbp" {if $order->card_type=='sbp' && $order->card_id != 0}selected{/if}>СБП</option>
                                                        <option value="virt" {if $order->card_type=='virt'}selected{/if}>Вирта</option>
                                                    </select>
                                                    <select class="form-control  {if in_array($manager->role, ['verificator', 'edit_verificator', 'contact_center_plus'])}js-need-comment-card{/if} js-order-card" name="card_id">
                                                        <option value="0" {if !$order->card_id}selected="true"{/if}>Не выбран</option>
                                                        {foreach $card_list as $card}
                                                            {if !$card->deleted && !$card->deleted_by_client}
                                                                <option  data-type="card"
                                                                         value="{$card->id}"
                                                                         {if !empty($card->Status) && $card->Status != 'A'}disabled="true"{/if}
                                                                        {if $card->id == $order->card_id}selected="true"{/if}>
                                                                    Карта                                                                                 {$card->pan} {$card->expdate}
                                                                    ({$organizations[$card->organization_id]->name|escape})
                                                                </option>
                                                            {/if}
                                                        {/foreach}
                                                        {if $sbp_accounts}
                                                            {foreach $sbp_accounts as $sbp_account}
                                                                <option data-type="sbp"
                                                                        value="{$sbp_account->id}"
                                                                        {if $sbp_account->deleted}disabled="true"{/if}
                                                                        {if $sbp_account->id == $order->card_id}selected="true"{/if}>
                                                                    СБП {$sbp_account->title}
                                                                </option>
                                                            {/foreach}
                                                        {/if}
                                                        {if $selected_bank || $default_selected_bank}
                                                            <option data-type="bank"
                                                                    value="{if $selected_bank}{$selected_bank->id}{else}{$default_selected_bank->id}{/if}"
                                                                    {if $order->card_type == 'sbp' && $order->card_id == 0 && ($selected_bank || $default_selected_bank)}selected="true"{/if}>
                                                                Банк {if $selected_bank}{$selected_bank->title}{else}{$default_selected_bank->title}{/if}
                                                            </option>
                                                        {/if}
                                                    </select>
                                                {else}
                                                    <input type="text" style="display:none" value="{$order->card_id}" name="card_id" />
                                                    <p class="form-control-static">
                                                        {foreach $card_list as $card}
                                                            {if $card->CardId == $order->card_id}
                                                                <strong>{$card->Pan} </strong> {$card->ExpDate} ({$organizations[$card->organization_id]->name|escape})
                                                                <a href="javascript:void(0);" class="btn btn-link ml-2 js-hold-card" data-user="{$order->user_id}" data-card="{$card->CardId}" data-rebill="{$card->RebillId}" title="Проверить карту">
                                                                    <i class="fas fa-h-square"></i>
                                                                </a>
                                                            {/if}
                                                        {/foreach}
                                                    </p>
                                                {/if}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-actions">
                                            <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                            <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                        </div>
                                    </div>
                                </div>

                            </form>
                            <!-- /Сумма и период заявки -->

                            {if $manager->role != 'verificator_minus'}
                                <div class="col-md-6">
                                    <form action="{url}" class=" js-order-item-form mb-3" id="services_form">

                                        <input type="hidden" name="action" value="services" />
                                        <input type="hidden" name="order_id" value="{$order->order_id}" />
                                        <input type="hidden" name="user_id" value="{$order->user_id}" />

                                        <h3 class="card-title">
                                            {if (in_array($order->status, [1, 5, 6, 7]) && ($manager->id == $order->manager_id)) || (in_array($order->status, [1, 5, 6, 2, 7, 3, 10]) && in_array($manager->role, ['admin', 'developer', 'chief_verificator', 'opr', 'ts_operator']))}
                                                <a href="javascript:void(0);" class="js-edit-form js-event-add-click" data-event="10" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}">
                                                    <span>Сервисные услуги</span>
                                                </a>
                                            {else}
                                                <span>Сервисные услуги</span>
                                            {/if}
                                            <a href="javascript:void(0);" class="ml-3 js-open-comment-form" data-block="services">
                                                <i class="mdi mdi-comment-text"></i>
                                            </a>
                                        </h3>
                                        <hr>

                                        <div class="row view-block {if $services_error}hide{/if}">

                                            <div class="js-comments-block-services">{display_comments block='services'}</div>

                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <label class="control-label col-md-6">Смс информирование:</label>
                                                    <div class="col-md-6">
                                                        <p class="form-control-static">
                                                            <strong>{if $order->service_sms}Да{else}Нет{/if}</strong>
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="control-label col-md-6">Страхование:</label>
                                                    <div class="col-md-6">
                                                        <p class="form-control-static">
                                                            <strong>{if $order->service_insurance}Да{else}Нет{/if}</strong>
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="control-label col-md-6">Кредитный рейтинг:</label>
                                                    <div class="col-md-6">
                                                        <p class="form-control-static">
                                                            <strong style="color: {if $has_pay_credit_rating } #31b131 {else} #f62d51 {/if};">
                                                                {if $has_pay_credit_rating}
                                                                    Куплен
                                                                {else}
                                                                    Не куплен
                                                                {/if}
                                                            </strong>
                                                        </p>
                                                    </div>
                                                </div>
                                                {if in_array($manager->role, ['developer', 'admin','contact_center','yurist','contact_center_plus','boss_cc', 'opr', 'ts_operator'])}
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-6">Пролонгация</label>
                                                        <div class="col-md-6">
                                                            <div class="onoffswitch toggle-prolongation-switch">
                                                                <input type="hidden" id="managerID"
                                                                       value="{$manager->id}">
                                                                <input type="hidden" id="orderID"
                                                                       value="{$order->order_id}">
                                                                <input type="checkbox" name="prolongation"
                                                                       class="onoffswitch-checkbox"
                                                                       id="prolongation"
                                                                        {if $order_data['prolongation']} value="1" checked {else} value="0" {/if}/>
                                                                <label class="onoffswitch-label"
                                                                       for="prolongation">
                                                                    <span class="onoffswitch-inner"></span>
                                                                    <span class="onoffswitch-switch"></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-6">Доп. услуги при
                                                            пролонгации Вита-мед {if $order->organization_id == 7}Акадо{else} Аквариус/Бустра{/if}</label>
                                                        <div class="col-md-6">
                                                            <div class="onoffswitch additional-services-switch">
                                                                <input type="hidden" id="order-id"
                                                                       value="{$order->order_id}">
                                                                <input type="hidden" id="manager-id"
                                                                       data-manager="{$manager->id}">
                                                                <input type="checkbox" name="additional_service_tv_med"
                                                                       class="onoffswitch-checkbox"
                                                                       id="additional-services-tv_med" {if !$order_data['additional_service_tv_med']} value="1"  checked {else} value="0" {/if}/>
                                                                <label class="onoffswitch-label"
                                                                       for="additional-services-tv_med">
                                                                    <span class="onoffswitch-inner"></span>
                                                                    <span class="onoffswitch-switch"></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-6">Доп. услуги при
                                                            пролонгации Консьерж сервис {if $order->organization_id == 7}Акадо{else} Аквариус/Бустра{/if}</label>
                                                        <div class="col-md-6">
                                                            <div class="onoffswitch additional-services-switch">
                                                                <input type="hidden" id="order-id"
                                                                       value="{$order->order_id}">
                                                                <input type="hidden" id="manager-id"
                                                                       data-manager="{$manager->id}">
                                                                <input type="checkbox" name="additional_service_multipolis"
                                                                       class="onoffswitch-checkbox"
                                                                       id="additional-services-multipolis" {if !$order_data['additional_service_multipolis']} value="1"  checked {else} value="0" {/if}/>
                                                                <label class="onoffswitch-label"
                                                                       for="additional-services-multipolis">
                                                                    <span class="onoffswitch-inner"></span>
                                                                    <span class="onoffswitch-switch"></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-6">Витамед на закрытии</label>
                                                        <div class="col-md-6">
                                                            <div class="onoffswitch additional-services-switch">
                                                                <input type="hidden" class="order-id"
                                                                       value="{$order->order_id}">
                                                                <input type="hidden" class="manager-id"
                                                                       data-manager="{$manager->id}">
                                                                <input type="checkbox"
                                                                       name="additional_service_repayment"
                                                                       class="onoffswitch-checkbox"
                                                                       id="additional-service-repayment" {if !$order_data['additional_service_repayment']} value="1"  checked {else} value="0" {/if}>
                                                                <label class="onoffswitch-label"
                                                                       for="additional-service-repayment">
                                                                    <span class="onoffswitch-inner"></span>
                                                                    <span class="onoffswitch-switch"></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-6">50% Витамед на закрытии</label>
                                                        <div class="col-md-6">
                                                            <div class="onoffswitch additional-services-switch">
                                                                <input type="hidden" class="order-id"
                                                                       value="{$order->order_id}">
                                                                <input type="hidden" class="manager-id"
                                                                       data-manager="{$manager->id}">
                                                                <input type="checkbox"
                                                                       name="half_additional_service_repayment"
                                                                       class="onoffswitch-checkbox"
                                                                       id="additional-service-half-repayment" {if !$order_data['half_additional_service_repayment']} value="1"  checked {else} value="0" {/if}>
                                                                <label class="onoffswitch-label"
                                                                       for="additional-service-half-repayment">
                                                                    <span class="onoffswitch-inner"></span>
                                                                    <span class="onoffswitch-switch"></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-6">Витамед на частичном закрытии </label>
                                                        <div class="col-md-6">
                                                            <div class="onoffswitch additional-services-switch">
                                                                <input type="hidden" class="order-id"
                                                                       value="{$order->order_id}">
                                                                <input type="hidden" class="manager-id"
                                                                       data-manager="{$manager->id}">
                                                                <input type="checkbox"
                                                                       name="additional_service_partial_repayment"
                                                                       class="onoffswitch-checkbox"
                                                                       id="additional-service-partial-repayment" {if !$order_data['additional_service_partial_repayment']} value="1"  checked {else} value="0" {/if}>
                                                                <label class="onoffswitch-label"
                                                                       for="additional-service-partial-repayment">
                                                                    <span class="onoffswitch-inner"></span>
                                                                    <span class="onoffswitch-switch"></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-6">50% Витамед на частичном закрытии </label>
                                                        <div class="col-md-6">
                                                            <div class="onoffswitch additional-services-switch">
                                                                <input type="hidden" class="order-id"
                                                                       value="{$order->order_id}">
                                                                <input type="hidden" class="manager-id"
                                                                       data-manager="{$manager->id}">
                                                                <input type="checkbox"
                                                                       name="half_additional_service_partial_repayment"
                                                                       class="onoffswitch-checkbox"
                                                                       id="additional-service-half-partial-repayment" {if !$order_data['half_additional_service_partial_repayment']} value="1"  checked {else} value="0" {/if}>
                                                                <label class="onoffswitch-label"
                                                                       for="additional-service-half-partial-repayment">
                                                                    <span class="onoffswitch-inner"></span>
                                                                    <span class="onoffswitch-switch"></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label class="control-label col-md-6">ЗО на частичном закрытии </label>
                                                        <div class="col-md-6">
                                                            <div class="onoffswitch additional-services-switch">
                                                                <input type="hidden" class="order-id"
                                                                       value="{$order->order_id}">
                                                                <input type="hidden" class="manager-id"
                                                                       data-manager="{$manager->id}">
                                                                <input type="checkbox"
                                                                       name="additional_service_so_partial_repayment"
                                                                       class="onoffswitch-checkbox"
                                                                       id="additional-service-so-partial-repayment" {if !$order_data['additional_service_so_partial_repayment']} value="1"  checked {else} value="0" {/if}>
                                                                <label class="onoffswitch-label"
                                                                       for="additional-service-so-partial-repayment">
                                                                    <span class="onoffswitch-inner"></span>
                                                                    <span class="onoffswitch-switch"></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-6">50% ЗО на частичном закрытии </label>
                                                        <div class="col-md-6">
                                                            <div class="onoffswitch additional-services-switch">
                                                                <input type="hidden" class="order-id"
                                                                       value="{$order->order_id}">
                                                                <input type="hidden" class="manager-id"
                                                                       data-manager="{$manager->id}">
                                                                <input type="checkbox"
                                                                       name="half_additional_service_so_partial_repayment"
                                                                       class="onoffswitch-checkbox"
                                                                       id="half-additional-service-so-partial-repayment" {if !$order_data['half_additional_service_so_partial_repayment']} value="1"  checked {else} value="0" {/if}>
                                                                <label class="onoffswitch-label"
                                                                       for="half-additional-service-so-partial-repayment">
                                                                    <span class="onoffswitch-inner"></span>
                                                                    <span class="onoffswitch-switch"></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label class="control-label col-md-6">ЗО на закрытии </label>
                                                        <div class="col-md-6">
                                                            <div class="onoffswitch additional-services-switch">
                                                                <input type="hidden" class="order-id"
                                                                       value="{$order->order_id}">
                                                                <input type="hidden" class="manager-id"
                                                                       data-manager="{$manager->id}">
                                                                <input type="checkbox"
                                                                       name="additional_service_so_repayment"
                                                                       class="onoffswitch-checkbox"
                                                                       id="additional-service-so-repayment" {if !$order_data['additional_service_so_repayment']} value="1"  checked {else} value="0" {/if}>
                                                                <label class="onoffswitch-label"
                                                                       for="additional-service-so-repayment">
                                                                    <span class="onoffswitch-inner"></span>
                                                                    <span class="onoffswitch-switch"></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-6">50% ЗО на закрытии </label>
                                                        <div class="col-md-6">
                                                            <div class="onoffswitch additional-services-switch">
                                                                <input type="hidden" class="order-id"
                                                                       value="{$order->order_id}">
                                                                <input type="hidden" class="manager-id"
                                                                       data-manager="{$manager->id}">
                                                                <input type="checkbox"
                                                                       name="half_additional_service_so_repayment"
                                                                       class="onoffswitch-checkbox"
                                                                       id="half-additional-service-so-repayment" {if !$order_data['half_additional_service_so_repayment']} value="1"  checked {else} value="0" {/if}>
                                                                <label class="onoffswitch-label"
                                                                       for="half-additional-service-so-repayment">
                                                                    <span class="onoffswitch-inner"></span>
                                                                    <span class="onoffswitch-switch"></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                {/if}
                                                {if $order->status_1c == "5.Выдан"}
                                                    <input type="hidden" value="{$order->deleteKD}" id = "delete-kd-click">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-6">Отключение ШКД:</label>
                                                        <div class="col-md-6">
                                                            {*                                                            <p class="form-control-static" >*}
                                                            {*                                                                <input type="radio"  value="{$order->user_id}" id="delete-kd" data-order = "{$order->order_id}" {if $order->deleteKD} checked {/if}>*}
                                                            {*                                                            </p>*}
                                                            <div class="onoffswitch delete-kd-switch">
                                                                <input type="hidden" id = "order-id" value="{$order->order_id}">
                                                                <input type="hidden" id = "user-id" data-user="{$user->id}">
                                                                <input type="checkbox" name="delete-kd" class="onoffswitch-checkbox" id="delete-kd" {if $order->deleteKD} value="1"  checked {else} value="0" {/if}/>
                                                                <label class="onoffswitch-label" for="delete-kd">
                                                                    <span class="onoffswitch-inner"></span>
                                                                    <span class="onoffswitch-switch"></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                {/if}

                                                <div class="form-group row">
                                                    <label
                                                            class="control-label col-md-6"
                                                            data-toggle="tooltip"
                                                            data-placement="left"
                                                            title="Кнопка доступна только для новых и одобренных заявок"
                                                    >
                                                        Финансовый доктор до выдачи:
                                                    </label>
                                                    <div class="col-md-6">
                                                        <div class="onoffswitch toggle-kd-switch">
                                                            <input
                                                                    type="checkbox"
                                                                    name="toggle-kd"
                                                                    class="onoffswitch-checkbox"
                                                                    id="toggle-kd"
                                                                    data-order-id="{$order->order_id}"
                                                                    data-user-id="{$order->user_id}"
                                                                    data-manager-id="{$manager->id}"
                                                                    {if !$order_data['disable_additional_service_on_issue']} checked {/if}
                                                                    {if !in_array($order->status_1c, ['Новая', '1.Рассматривается', '3.Одобрено'])} disabled="disabled" {/if}
                                                            />
                                                            <label class="onoffswitch-label" for="toggle-kd">
                                                                <span class="onoffswitch-inner"></span>
                                                                <span class="onoffswitch-switch"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row edit-block {if !$services_error}hide{/if}">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" name="service_sms" id="service_sms" value="1" {if $order->service_sms}checked="true"{/if} />
                                                        <label class="custom-control-label" for="service_sms">Смс информирование</label>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" name="service_reason" id="service_reason" value="1" {if $order->service_reason}checked="true"{/if} />
                                                        <label class="custom-control-label" for="service_reason">Причина отказа</label>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" name="service_insurance" id="service_insurance" value="1" {if $order->service_insurance}checked="true"{/if} />
                                                        <label class="custom-control-label" for="service_insurance">Страхование</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-actions">
                                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                                    <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                                </div>
                                            </div>
                                        </div>


                                    </form>

                                    <form action="{url}" class="js-order-item-form mb-3" id="autodebit_form">

                                        <input type="hidden" name="action" value="change_autodebit_param" />
                                        <input type="hidden" name="order_id" value="{$order->order_id}" />
                                        <input type="hidden" name="user_id" value="{$order->user_id}" />

                                        <h3 class="card-title">
                                            {if in_array('autodebit', $manager->permissions)}
                                                <a href="javascript:void(0);" class="js-edit-form js-event-add-click" data-event="10" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}">
                                                    <span>Автосписание</span>
                                                </a>
                                            {else}
                                                <span>Автосписание</span>
                                            {/if}
                                            <a href="javascript:void(0);" class="ml-3 js-open-comment-form" data-block="autodebit">
                                                <i class="mdi mdi-comment-text"></i>
                                            </a>
                                        </h3>
                                        <hr>

                                        <div class="row view-block {if $autodebit_error}hide{/if}">

                                            <div class="js-comments-block-autodebit">{display_comments block='autodebit'}</div>

                                            <div class="col-md-12">
                                                {foreach $autodebit_cards as $card}
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-6">
                                                            {if $card->deleted_by_client}
                                                                <span class="text-danger">
                                                                    {$card->pan} ({$organizations[$card->organization_id]->name|escape})
                                                                    <br /><small class="text-danger">карта удалена {$card->deleted_by_client_date|date_format:'%d.%m.%Y %H:%M:%S'}</small>
                                                                </span>
                                                            {else}
                                                                {$card->pan} ({$organizations[$card->organization_id]->name|escape})
                                                                <br /><small class="text-success">карта добавлена {$card->created|date_format:'%d.%m.%Y %H:%M:%S'}</small>
                                                            {/if}
                                                        </label>
                                                        <div class="col-md-3">
                                                            <p class="form-control-static">
                                                                <strong>{if $card->autodebit}Активно{else}Выключено{/if}</strong>
                                                            </p>
                                                        </div>
                                                    </div>
                                                {/foreach}
                                            </div>

                                            <div class="col-md-12">
                                                {if $sbp_accounts}
                                                    {foreach $sbp_accounts as $sbp_account}
                                                        <div class="form-group row">
                                                            <label class="control-label col-md-6">СБП
                                                                <br/><small class="text-success">счёт {$sbp_account->title} добавлен {$sbp_account->created_at|date_format:'%d.%m.%Y %H:%M:%S'}</small>
                                                            </label>

                                                            <div class="col-md-3">
                                                                <p class="form-control-static">
                                                                    <strong>{if $sbp_account->autodebit}Активно{else}Выключено{/if}</strong>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    {/foreach}
                                                {/if}
                                            </div>
                                        </div>

                                        <div class="row edit-block {if !$autodebit_error}hide{/if}">
                                            <div class="col-md-12">
                                                {foreach $autodebit_cards as $card}
                                                    <div class="form-group" data-card-data = '{$card->id}'>
                                                        <div class="row">
                                                            <label class="col-md-3" for="autodebit_{$card->id}">
                                                                {if $card->deleted_by_client}<span class="text-danger">{$card->pan}</span>
                                                                {else}{$card->pan}{/if}
                                                            </label>
                                                            <div class="col-md-3">
                                                                <button type="submit"
                                                                        class="btn btn-danger remove-card"
                                                                        data-button-card-id="{$card->id}"
                                                                        data-user-id= "{$order->user_id}"
                                                                        data-manager="{$manager->id}"
                                                                >Удалить
                                                                </button>
                                                            </div>
                                                            <select name="card_autodebit_data[{$card->id}]" class="col-md-6 form-control">
                                                                <option value="0" {if !$card->autodebit}selected{/if}>Выключено</option>
                                                                <option value="1" {if $card->autodebit}selected{/if}>Активно</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                {/foreach}

                                                {if $sbp_accounts}
                                                    {foreach $sbp_accounts as $sbp_account}
                                                        <div class="form-group" data-sbp-account-data = '{$sbp_account->token}'>
                                                            <div class="row">
                                                                <label class="col-md-3" for="autodebit_sbp_{$sbp_account->token}">
                                                                    {if $sbp_account->deleted}<span class="text-danger">СБП</span>
                                                                    {else}СБП{/if}
                                                                </label>
                                                                <div class="col-md-3">
                                                                    <button type="submit"
                                                                            class="btn btn-danger remove-sbp-account"
                                                                            data-sbp-token="{$sbp_account->token}"
                                                                            data-user-id= "{$order->user_id}"
                                                                            data-manager="{$manager->id}"
                                                                    >Удалить
                                                                    </button>
                                                                </div>

                                                                <select name="sbp_autodebit_data[{$sbp_account->id}]" class="col-md-6 form-control">
                                                                    <option value="0" {if !$sbp_account->autodebit}selected{/if}>Выключено</option>
                                                                    <option value="1" {if $sbp_account->autodebit}selected{/if}>Активно</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    {/foreach}
                                                {/if}
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-actions">
                                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                                    <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                                </div>
                                            </div>
                                        </div>


                                    </form>
                                </div>
                            {/if}

                        </div>

                        <div class="row">
                            <div class="col-md-6">

                                <!-- Персональные данные -->
                                <form action="{url}" class="mb-3 js-order-item-form " id="personal_data_form">

                                    <input type="hidden" name="action" value="personal" />
                                    <input type="hidden" name="order_id" value="{$order->order_id}" />
                                    <input type="hidden" name="user_id" value="{$order->user_id}" />

                                    <h3 class="card-title">
                                        {if (in_array($order->status, [1, 5, 6, 7]) && ($manager->id == $order->manager_id)) || (in_array($order->status, [1, 5, 6, 2, 7, 3, 10]) && in_array($manager->role, ['admin', 'developer', 'chief_verificator', 'opr', 'ts_operator']))}
                                            <a href="javascript:void(0);" class="js-edit-form js-event-add-click"  data-event="11" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}">
                                                <span>Персональная информация</span>
                                            </a>
                                        {else}
                                            <span>Персональная информация</span>
                                        {/if}
                                        <a href="javascript:void(0);" class="ml-3 js-open-comment-form" data-block="personal">
                                            <i class="mdi mdi-comment-text"></i>
                                        </a>
                                    </h3>
                                    </h3>
                                    <hr>

                                    <div class="row view-block {if $personal_error}hide{/if}">
                                        <div class="js-comments-block-personal">{display_comments block='personal'}</div>
                                        <div class="col-md-12">
                                            <div class="form-group row">
                                                <label class="control-label col-md-4">ФИО:</label>
                                                <div class="col-md-8">
                                                    <p class="form-control-static">
                                                        <strong>
                                                            {$order->lastname|escape} {$order->firstname|escape} {$order->patronymic|escape},
                                                            {if $order->birth}{$order->birth|escape}{/if}
                                                        </strong>
                                                        <a href="client/{$order->user_id}" target="_blank"><i class="far fa-user"></i></a>
                                                        {if $order->first_loan}<span class="label label-primary">Новая</span>
                                                        {elseif $order->have_close_credits}<span class="label label-success">ПК</span>
                                                        {else}<span class="label label-warning">Повтор</span>{/if}
                                                        {if $manager->role != 'verificator_minus'}
                                                            {if $order->is_user_credit_doctor == 1}<span class="label label-danger">КД</span>{/if}
                                                            {if $vk_user_id }<a href="https://vk.com/id{$vk_user_id}" target="_blank" class="label label-info">ВК</a>{/if}
                                                        {/if}

                                                        {if !empty($user_data)}
                                                            {if !empty($user_data['whatsapp_phone'])}<a href="https://wa.me/{$user_data['whatsapp_phone']}" target="_blank" class="label label-success">WhatsApp</a>{/if}
                                                            {if !empty($user_data['viber_phone'])}<a href="viber://chat?number={$user_data['viber_phone']}" target="_blank" class="label label-primary">Viber</a>{/if}
                                                            {if !empty($user_data['skype_login'])}<a href="skype:{$user_data['skype_login']}" target="_blank" class="label label-info">Skype</a>{/if}
                                                        {/if}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group row">
                                                <label class="control-label col-md-4">Телефон:</label>
                                                <div class="col-md-8">
                                                    <p class="form-control-static">
                                                        <strong class="user-phone-mobile">{$order->phone_mobile|escape}</strong>
                                                        <button class="btn waves-effect waves-light btn-xs btn-info js-mango-call js-event-add-click" data-phone="{$order->phone_mobile}" data-event="22" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}" title="Выполнить звонок"><i class="fas fa-phone-square"></i></button>
                                                        <button type="button" class="btn btn-sm btn-primary js-open-sms-modal" title="Отправить смс" data-user="{$order->user_id}">
                                                            <i class=" far fa-share-square"></i>
                                                        </button>
                                                        {if empty($blockCalls)}
                                                            <button
                                                                    class="btn waves-effect waves-light btn-xs btn-success js-voximplant-call"
                                                                    data-phone="{$order->phone_mobile|escape}">
                                                                <i class="fas fa-phone-square"></i>

                                                            </button>
                                                        {/if}
                                                        <br>
                                                        {if empty($order_data['idx_check']) || $order_data['idx_check'] == 'unchecked'}
                                                            <small class="text-secondary">Проверка IDX на принадлежность телефона клиента не проводилась</small>
                                                        {elseif $order_data['idx_check'] == 'success'}
                                                            <small class="text-success">По результату проверки IDX телефон принадлежит клиенту</small>
                                                        {else}
                                                            <small class="text-danger">По результату проверки IDX телефон не принадлежит клиенту</small>
                                                        {/if}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        {if !in_array($manager->role, ['verificator_minus'])}
                                            {foreach $additional_phones as $phone}
                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-4">Найденный номер:</label>
                                                        <div class="col-md-8">
                                                            <p class="form-control-static">{$phone->phone|escape}
                                                                <button class="btn waves-effect waves-light btn-xs btn-info js-mango-call" data-phone="{$phone->phone}" data-user="{$client->id}" title="Выполнить звонок"><i class="fas fa-phone-square"></i></button>
                                                                <button type="button" class="waves-effect waves-light btn btn-xs btn-primary js-open-sms-modal" title="Отправить смс" data-user="{$client->id}">
                                                                    <i class=" far fa-share-square"></i>
                                                                </button>
                                                                {if empty($blockCalls)}
                                                                    <button
                                                                            class="btn waves-effect waves-light btn-xs btn-success js-voximplant-call"
                                                                            data-phone="{$phone->phone|escape}">
                                                                        <i class="fas fa-phone-square"></i>

                                                                    </button>
                                                                {/if}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/foreach}
                                        {/if}
                                        <div class="col-md-12">
                                            <div class="form-group row">
                                                <label class="control-label col-md-4">Почта:</label>
                                                <div class="col-md-8">
                                                    <p class="form-control-static">
                                                        <strong>{$order->email|escape}</strong>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        {if !in_array($manager->role, ['verificator_minus'])}
                                            {foreach $additionalEmails as $email}
                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-4">Найденный email:</label>
                                                        <div class="col-md-8">
                                                            <p class="form-control-static">{$email->email|escape}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/foreach}
                                        {/if}
                                        <div class="col-md-12">
                                            <div class="form-group row">
                                                <label class="control-label col-md-4">Пол:</label>
                                                <div class="col-md-8">
                                                    <p class="form-control-static">
                                                        <strong>
                                                            {if $order->gender == 'male'}Мужской
                                                            {elseif $order->gender == 'female'}Женский
                                                            {else}Не указан{/if}
                                                        </strong>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group row">
                                                <label class="control-label col-md-4">Дата и место рождения:</label>
                                                <div class="col-md-8">
                                                    <p class="form-control-static">
                                                        <strong>
                                                            {if $order->birth}{$order->birth|escape}, {/if}
                                                            <small class="label label-primary">{$order->age}</small>
                                                            {$order->birth_place|escape}
                                                        </strong>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group row">
                                                <label class="control-label col-md-4">Образование:</label>
                                                <div class="col-md-8">
                                                    <p class="form-control-static">
                                                        <strong>
                                                            {$education_name}
                                                        </strong>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row edit-block {if !$personal_error}hide{/if}">
                                        {if $personal_error}
                                            <div class="col-md-12">
                                                <ul class="alert alert-danger">
                                                    {if in_array('empty_lastname', (array)$personal_error)}<li>Укажите Фамилию!</li>{/if}
                                                    {if in_array('empty_firstname', (array)$personal_error)}<li>Укажите Имя!</li>{/if}
                                                    {if in_array('empty_patronymic', (array)$personal_error)}<li>Укажите Отчество!</li>{/if}
                                                    {if in_array('empty_gender', (array)$personal_error)}<li>Укажите Пол!</li>{/if}
                                                    {if in_array('empty_birth', (array)$personal_error)}<li>Укажите Дату рождения!</li>{/if}
                                                    {if in_array('empty_birth_place', (array)$personal_error)}<li>Укажите Место рождения!</li>{/if}
                                                </ul>
                                            </div>
                                        {/if}
                                        <div class="col-md-4">
                                            <div class="form-group {if in_array('empty_lastname', (array)$personal_error)}has-danger{/if}">
                                                <label class="control-label">Фамилия</label>
                                                <input type="text" name="lastname" value="{$order->lastname|escape}" class="form-control" data-cyrillic="fio" placeholder="Фамилия" required="true" />
                                                {if in_array('empty_lastname', (array)$personal_error)}<small class="form-control-feedback">Укажите Фамилию!</small>{/if}
                                                {if in_array('symbols_lastname', (array)$personal_error)}<small class="form-control-feedback">Допускается ввод только русских букв</small>{/if}
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group {if in_array('empty_firstname', (array)$personal_error)}has-danger{/if}">
                                                <label class="control-label">Имя</label>
                                                <input type="text" name="firstname" value="{$order->firstname|escape}" class="form-control" data-cyrillic="fio" placeholder="Имя" required="true" />
                                                {if in_array('empty_firstname', (array)$personal_error)}<small class="form-control-feedback">Укажите Имя!</small>{/if}
                                                {if in_array('symbols_firstname', (array)$personal_error)}<small class="form-control-feedback">Допускается ввод только русских букв</small>{/if}
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group {if in_array('empty_patronymic', (array)$personal_error)}has-danger{/if}">
                                                <label class="control-label">Отчество</label>
                                                <input type="text" name="patronymic" value="{$order->patronymic|escape}" class="form-control" data-cyrillic="fio" placeholder="Отчество" />
                                                {if in_array('empty_patronymic', (array)$personal_error)}<small class="form-control-feedback">Укажите Отчество!</small>{/if}
                                                {if in_array('symbols_patronymic', (array)$personal_error)}<small class="form-control-feedback">Допускается ввод только русских букв</small>{/if}
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group {if in_array('empty_gender', (array)$personal_error)}has-danger{/if}">
                                                <label class="control-label">Пол</label>
                                                <select class="form-control custom-select" name="gender">
                                                    <option value="male" {if $order->gender == 'male'}selected="true"{/if}>Мужской</option>
                                                    <option value="female" {if $order->gender == 'female'}selected="true"{/if}>Женский</option>
                                                </select>
                                                {if in_array('empty_gender', (array)$personal_error)}<small class="form-control-feedback">Укажите Пол!</small>{/if}
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group {if in_array('empty_birth', (array)$personal_error)}has-danger{/if}">
                                                <label class="control-label">ДP</label>
                                                <input type="text" class="form-control js-mask-input" data-mask="99.99.9999" name="birth" value="{if $order->birth}{$order->birth|escape}{/if}" placeholder="dd.mm.yyyy" required="true" />
                                                {if in_array('empty_birth', (array)$personal_error)}<small class="form-control-feedback">Укажите Дату рождения!</small>{/if}
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group {if in_array('empty_birth_place', (array)$personal_error)}has-danger{/if}">
                                                <label class="control-label">Место рождения</label>
                                                <input type="text" class="form-control" data-cyrillic="with-numbers" name="birth_place" value="{$order->birth_place|escape}" placeholder="" />
                                                {if in_array('empty_birth_place', (array)$personal_error)}<small class="form-control-feedback">Укажите Место рождения!</small>{/if}
                                                {if in_array('symbols_birth_place', (array)$personal_error)}<small class="form-control-feedback">Допускается ввод только русских букв</small>{/if}
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group ">
                                                <label class="control-label">Почта</label>
                                                <input type="text" class="form-control" name="email" value="{$order->email|escape}" placeholder="" />
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-actions">
                                                <button type="submit" class="btn btn-success js-save-no-agreement"> <i class="fa fa-check"></i> Сохранить</button>
                                                {if in_array($manager->role, ['developer', 'chief_verificator', 'ts_operator']) && !$has_approved_orders}
                                                    <button type="submit" class="btn btn-primary js-save-agreement"> <i class="fa fa-file"></i> Подписать доп. соглашение</button>
                                                {/if}
                                                <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <!-- /Персональные данные -->

                                <!-- Паспортные данные -->
                                <form action="{url}" class="mb-3 js-order-item-form" id="passport_data_form">

                                    <input type="hidden" name="action" value="passport" />
                                    <input type="hidden" name="order_id" value="{$order->order_id}" />
                                    <input type="hidden" name="user_id" value="{$order->user_id}" />

                                    <h3 class="box-title">
                                        {if (in_array($order->status, [1, 5, 6, 7]) && ($manager->id == $order->manager_id)) || (in_array($order->status, [1, 5, 6, 2, 7, 3, 10]) && in_array($manager->role, ['admin', 'developer', 'chief_verificator', 'opr', 'ts_operator']))}
                                            <a href="javascript:void(0);" class="js-edit-form js-event-add-click"  data-event="12" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}">
                                                <span>Паспортные данные</span>
                                            </a>
                                        {else}
                                            <span>Паспортные данные</span>
                                        {/if}
                                        <a href="javascript:void(0);" class="ml-3 js-open-comment-form" data-block="passport">
                                            <i class="mdi mdi-comment-text"></i>
                                        </a>
                                    </h3>
                                    <hr>

                                    <div class="row view-block {if $passport_error}hide{/if}">
                                        <div class="js-comments-block-passport">{display_comments block='passport'}</div>
                                        <div class="col-md-12">
                                            <div class="form-group row">
                                                <label class="control-label col-md-4">Паспорт:</label>
                                                <div class="col-md-8">
                                                    <p class="form-control-static"><strong>{$order->passport_serial|escape}{if $order->passport_date}, от {$order->passport_date|escape}{/if}</strong></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group row">
                                                <label class="control-label col-md-4">Код подразделения:</label>
                                                <div class="col-md-8">
                                                    <p class="form-control-static"><strong>{$order->subdivision_code|escape}</strong></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group row">
                                                <label class="control-label col-md-4">Кем выдан:</label>
                                                <div class="col-md-8">
                                                    <p class="form-control-static"><strong>{$order->passport_issued|escape}</strong></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row edit-block {if !$passport_error}hide{/if}">
                                        {if $passport_error}
                                            <div class="col-md-12">
                                                <ul class="alert alert-danger">
                                                    {if $passport_error[$order->user_id]}
                                                        <li>
                                                            Клиент с такими паспортными данными уже зарегистрирован!<br/>
                                                            <a href="http://manager.boostra.ru/client/{$passport_error[$order->user_id]}">
                                                                manager.boostra.ru/client/{$passport_error[$order->user_id]}
                                                            </a>
                                                        </li>
                                                    {/if}
                                                    {if in_array('empty_passport_serial', (array)$passport_error)}<li>Укажите серию и номер паспорта!</li>{/if}
                                                    {if in_array('empty_passport_date', (array)$passport_error)}<li>Укажите дату выдачи паспорта!</li>{/if}
                                                    {if in_array('empty_subdivision_code', (array)$passport_error)}<li>Укажите код подразделения выдавшего паспорт!</li>{/if}
                                                    {if in_array('empty_passport_issued', (array)$passport_error)}<li>Укажите кем выдан паспорт!</li>{/if}
                                                </ul>
                                            </div>
                                        {/if}
                                        <div class="col-md-4">
                                            <div class="form-group {if in_array('empty_passport_serial', (array)$passport_error)}has-danger{/if}">
                                                <label class="control-label">Серия и номер паспорта</label>
                                                <input type="text" class="form-control js-mask-input" data-mask="99 99 999999" name="passport_serial" value="{$order->passport_serial|escape}" placeholder="" required="true"  />
                                                {if in_array('empty_passport_serial', (array)$passport_error)}<small class="form-control-feedback">Укажите серию и номер паспорта!</small>{/if}
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group {if in_array('empty_passport_date', (array)$passport_error)}has-danger{/if}">
                                                <label class="control-label">Дата выдачи</label>
                                                <input type="text" class="form-control js-mask-input" data-mask="99.99.9999" name="passport_date" value="{if $order->passport_date}{$order->passport_date|escape}{/if}" placeholder="" required="true" />
                                                {if in_array('empty_passport_date', (array)$passport_error)}<small class="form-control-feedback">Укажите дату выдачи паспорта!</small>{/if}
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group {if in_array('empty_subdivision_code', (array)$passport_error)}has-danger{/if}">
                                                <label class="control-label">Код подразделения</label>
                                                <input type="text" class="form-control js-mask-input" name="subdivision_code" data-mask="999-999" value="{$order->subdivision_code|escape}" placeholder="" required="true" />
                                                {if in_array('empty_subdivision_code', (array)$passport_error)}<small class="form-control-feedback">Укажите код подразделения выдавшего паспорт!</small>{/if}
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group {if in_array('empty_passport_issued', (array)$passport_error)}has-danger{/if}">
                                                <label class="control-label">Кем выдан</label>
                                                <input type="text" class="form-control" data-cyrillic="with-numbers" name="passport_issued" value="{$order->passport_issued|escape}" placeholder="" required="true" />
                                                {if in_array('empty_passport_issued', (array)$passport_error)}<small class="form-control-feedback">Укажите кем выдан паспорт!</small>{/if}
                                                {if in_array('symbols_passport_issued', (array)$passport_error)}<small class="form-control-feedback">Допускается ввод только русских букв</small>{/if}
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-actions">
                                                <button type="submit" class="btn btn-success js-save-no-agreement"> <i class="fa fa-check"></i> Сохранить</button>
                                                {if in_array($manager->role, ['developer', 'chief_verificator', 'ts_operator']) && !$has_approved_orders}
                                                    <button type="submit" class="btn btn-primary js-save-agreement"> <i class="fa fa-file"></i> Подписать доп. соглашение</button>
                                                {/if}
                                                <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <!-- /Паспортные данные -->

                                <!-- Прописка -->
                                <form action="{url}" class="js-order-item-form mb-3" id="reg_address_form">

                                    <input type="hidden" name="action" value="reg_address" />
                                    <input type="hidden" name="order_id" value="{$order->order_id}" />
                                    <input type="hidden" name="user_id" value="{$order->user_id}" />

                                    <h3 class="box-title">
                                        {if (in_array($order->status, [1, 5, 6, 7]) && ($manager->id == $order->manager_id)) || (in_array($order->status, [1, 5, 6, 2, 7, 3, 10]) && in_array($manager->role, ['admin', 'developer', 'chief_verificator', 'opr', 'ts_operator']))}
                                            <a href="javascript:void(0);" class="js-edit-form js-event-add-click"  data-event="13" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}">
                                                <span>Адрес прописки</span>
                                            </a>
                                        {else}
                                            <span>Адрес прописки</span>
                                        {/if}
                                        <a href="javascript:void(0);" class="ml-3 js-open-comment-form" data-block="regaddress">
                                            <i class="mdi mdi-comment-text"></i>
                                        </a>
                                    </h3>
                                    <hr>

                                    <div class="row view-block {if $regaddress_error}hide{/if}">
                                        <div class="js-comments-block-regaddress">{display_comments block='regaddress'}</div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <p class="form-control-static">
                                                    <strong>
                                                        {$order->Regindex|escape},
                                                        {$order->Regregion|escape} {$order->Regregion_shorttype|escape},
                                                        {$order->Regcity_shorttype|escape} {$order->Regcity|escape},
                                                        {$order->Regstreet_shorttype|escape} {$order->Regstreet|escape},
                                                        д.{$order->Reghousing|escape},
                                                        {if $order->Regbuilding}стр. {$order->Regbuilding|escape},{/if}
                                                        {if $order->Regroom}кв.{$order->Regroom|escape}{/if}
                                                    </strong>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class=" js-dadata-address row edit-block {if !$regaddress_error}hide{/if}">
                                        {if $regaddress_error}
                                            <div class="col-md-12">
                                                <ul class="alert alert-danger">
                                                    {if in_array('empty_regregion', (array)$regaddress_error)}<li>Укажите область!</li>{/if}
                                                    {if in_array('empty_regcity', (array)$regaddress_error)}<li>Укажите город!</li>{/if}
                                                    {if in_array('empty_regstreet', (array)$regaddress_error)}<li>Укажите улицу!</li>{/if}
                                                    {if in_array('empty_reghousing', (array)$regaddress_error)}<li>Укажите дом!</li>{/if}
                                                </ul>
                                            </div>
                                        {/if}
                                        <div class="col-md-6">
                                            <div class="form-group {if in_array('empty_regregion', (array)$regaddress_error)}has-danger{/if}">
                                                <label class="control-label">Область</label>
                                                <div class="row no-gutters">
                                                    <div class="col-md-9  pr-1">
                                                        <input type="text" class="form-control js-dadata-region" data-cyrillic="with-numbers" name="Regregion" value="{$order->Regregion|escape}" placeholder="" required="true" />
                                                    </div>
                                                    <div class="col-md-3">
                                                        <input type="text" class="form-control js-dadata-region-type" data-cyrillic="with-numbers" name="Regregion_shorttype" value="{$order->Regregion_shorttype|escape}" placeholder="" />
                                                    </div>
                                                </div>
                                                {if in_array('empty_regregion', (array)$regaddress_error)}<small class="form-control-feedback">Укажите область!</small>{/if}
                                                {if in_array('symbols_regregion', (array)$regaddress_error)}<small class="form-control-feedback">Допускается ввод только русских букв</small>{/if}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group {if in_array('empty_regcity', (array)$regaddress_error)}has-danger{/if}">
                                                <label class="control-label">Город</label>
                                                <div class="row no-gutters">
                                                    <div class="col-md-9 pr-1">
                                                        <input type="text" class="form-control js-dadata-city" data-cyrillic="with-numbers" name="Regcity" value="{$order->Regcity|escape}" placeholder="" required="true" />
                                                    </div>
                                                    <div class="col-md-3">
                                                        <input type="text" class="form-control js-dadata-city-type" data-cyrillic="with-numbers" name="Regcity_shorttype" value="{$order->Regcity_shorttype|escape}" placeholder="" />
                                                    </div>
                                                </div>
                                                {if in_array('empty_regcity', (array)$regaddress_error)}<small class="form-control-feedback">Укажите город!</small>{/if}
                                                {if in_array('symbols_regcity', (array)$regaddress_error)}<small class="form-control-feedback">Допускается ввод только русских букв</small>{/if}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group {if in_array('empty_regstreet', (array)$regaddress_error)}has-danger{/if}">
                                                <label class="control-label">Улица</label>
                                                <div class="row no-gutters">
                                                    <div class="col-md-9 pr-1">
                                                        <input type="text" class="form-control js-dadata-street" data-cyrillic="with-numbers" name="Regstreet" value="{$order->Regstreet|escape}" placeholder="" />
                                                    </div>
                                                    <div class="col-md-3">
                                                        <input type="text" class="form-control js-dadata-street-type" data-cyrillic="with-numbers" name="Regstreet_shorttype" value="{$order->Regstreet_shorttype|escape}" placeholder="" />
                                                    </div>
                                                </div>
                                                {if in_array('empty_regstreet', (array)$regaddress_error)}<small class="form-control-feedback">Укажите улицу!</small>{/if}
                                                {if in_array('symbols_regstreet', (array)$regaddress_error)}<small class="form-control-feedback">Допускается ввод только русских букв</small>{/if}
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="control-label">Индекс</label>
                                                <input type="text" class="form-control js-dadata-index" name="Regindex" value="{$order->Regindex|escape}" placeholder="" />
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group {if in_array('empty_reghousing', (array)$regaddress_error)}has-danger{/if}">
                                                <label class="control-label">Дом</label>
                                                <input type="text" class="form-control js-dadata-house" data-cyrillic="with-numbers" name="Reghousing" value="{$order->Reghousing|intval}" placeholder="" required="true" />
                                                {if in_array('empty_reghousing', (array)$regaddress_error)}<small class="form-control-feedback">Укажите дом!</small>{/if}
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Строение</label>
                                                <input type="text" class="form-control js-dadata-building" name="Regbuilding" value="{$order->Regbuilding|intval}" placeholder="" />
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Квартира</label>
                                                <input type="text" class="form-control js-dadata-room" name="Regroom" value="{$order->Regroom|escape}" placeholder="" />
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-actions">
                                                <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                                <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <!-- /Прописка -->

                                <!-- Адрес проживания -->
                                <form action="{url}" class="js-order-item-form mb-3" id="faktaddress_form">

                                    <input type="hidden" name="action" value="fakt_address" />
                                    <input type="hidden" name="order_id" value="{$order->order_id}" />
                                    <input type="hidden" name="user_id" value="{$order->user_id}" />

                                    <h3 class="box-title">
                                        {if (in_array($order->status, [1, 5, 6, 7]) && ($manager->id == $order->manager_id)) || (in_array($order->status, [1, 5, 6, 2, 7, 3, 10]) && in_array($manager->role, ['admin', 'developer', 'chief_verificator', 'opr', 'ts_operator']))}
                                            <a href="javascript:void(0);" class="js-edit-form js-event-add-click"  data-event="14" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}">
                                                <span>Адрес проживания</span>
                                            </a>
                                        {else}
                                            <span>Адрес проживания</span>
                                        {/if}
                                        <a href="javascript:void(0);" class="ml-3 js-open-comment-form" data-block="faktaddress">
                                            <i class="mdi mdi-comment-text"></i>
                                        </a>
                                    </h3>
                                    <hr>

                                    <div class="row view-block {if $faktaddress_error}hide{/if}">
                                        <div class="js-comments-block-faktaddress">{display_comments block='faktaddress'}</div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <p class="form-control-static">
                                                    <strong>
                                                        {$order->Faktindex|escape},
                                                        {$order->Faktregion|escape} {$order->Faktregion_shorttype|escape},
                                                        {$order->Faktcity_shorttype|escape} {$order->Faktcity|escape},
                                                        {$order->Faktstreet_shorttype|escape} {$order->Faktstreet|escape},
                                                        д.{$order->Fakthousing|escape}{if $order->Faktbuilding}, стр. {$order->Faktbuilding|escape}{/if}{if $order->Faktroom}, кв.{$order->Faktroom|escape}{/if}
                                                    </strong>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row edit-block js-dadata-address {if !$faktaddress_error}hide{/if}">
                                        {if $faktaddress_error}
                                            <div class="col-md-12">
                                                <ul class="alert alert-danger">
                                                    {if in_array('empty_faktregion', (array)$faktaddress_error)}<li>Укажите область!</li>{/if}
                                                    {if in_array('empty_faktcity', (array)$faktaddress_error)}<li>Укажите город!</li>{/if}
                                                    {if in_array('empty_faktstreet', (array)$faktaddress_error)}<li>Укажите улицу!</li>{/if}
                                                    {if in_array('empty_fakthousing', (array)$faktaddress_error)}<li>Укажите дом!</li>{/if}
                                                </ul>
                                            </div>
                                        {/if}
                                        <div class="col-md-6">
                                            <div class="form-group {if in_array('empty_faktregion', (array)$faktaddress_error)}has-danger{/if}">
                                                <label class="control-label">Область</label>
                                                <div class="row no-gutters">
                                                    <div class="col-md-9 pr-1">
                                                        <input type="text" class="form-control js-dadata-region" data-cyrillic="with-numbers" name="Faktregion" value="{$order->Faktregion|escape}" placeholder="" required="true" />
                                                    </div>
                                                    <div class="col-md-3">
                                                        <input type="text" class="form-control js-dadata-region-type" data-cyrillic="with-numbers" name="Faktregion_shorttype" value="{$order->Faktregion_shorttype|escape}" placeholder=""  />
                                                    </div>
                                                </div>
                                                {if in_array('empty_faktregion', (array)$faktaddress_error)}<small class="form-control-feedback">Укажите область!</small>{/if}
                                                {if in_array('symbols_faktregion', (array)$faktaddress_error)}<small class="form-control-feedback">Допускается ввод только русских букв</small>{/if}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group {if in_array('empty_faktcity', (array)$faktaddress_error)}has-danger{/if}">
                                                <label class="control-label">Город</label>
                                                <div class="row no-gutters">
                                                    <div class="col-md-9 pr-1">
                                                        <input type="text" class="form-control js-dadata-city" data-cyrillic="with-numbers" name="Faktcity" value="{$order->Faktcity|escape}" placeholder="" required="true" />
                                                    </div>
                                                    <div class="col-md-3">
                                                        <input type="text" class="form-control js-dadata-city-type" data-cyrillic="with-numbers" name="Faktcity_shorttype" value="{$order->Faktcity_shorttype|escape}" placeholder="" />
                                                    </div>
                                                </div>
                                                {if in_array('empty_faktcity', (array)$faktaddress_error)}<small class="form-control-feedback">Укажите город!</small>{/if}
                                                {if in_array('symbols_faktcity', (array)$faktaddress_error)}<small class="form-control-feedback">Допускается ввод только русских букв</small>{/if}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group {if in_array('empty_faktstreet', (array)$faktaddress_error)}has-danger{/if}">
                                                <label class="control-label">Улица</label>
                                                <div class="row no-gutters">
                                                    <div class="col-md-9 pr-1">
                                                        <input type="text" class="form-control js-dadata-street" data-cyrillic="with-numbers" name="Faktstreet" value="{$order->Faktstreet|escape}" placeholder="" />
                                                    </div>
                                                    <div class="col-md-3">
                                                        <input type="text" class="form-control js-dadata-street-type" data-cyrillic="with-numbers" name="Faktstreet_shorttype" value="{$order->Faktstreet_shorttype|escape}" placeholder="" />
                                                    </div>
                                                </div>
                                                {if in_array('empty_faktstreet', (array)$faktaddress_error)}<small class="form-control-feedback">Укажите улицу!</small>{/if}
                                                {if in_array('symbols_faktstreet', (array)$faktaddress_error)}<small class="form-control-feedback">Допускается ввод только русских букв</small>{/if}
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="control-label">Индекс</label>
                                                <input type="text" class="form-control js-dadata-index" name="Faktindex" value="{$order->Faktindex|escape}" placeholder="" />
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group {if in_array('empty_fakthousing', (array)$faktaddress_error)}has-danger{/if}">
                                                <label class="control-label">Дом</label>
                                                <input type="text" class="form-control js-dadata-house" data-cyrillic="with-numbers" name="Fakthousing" value="{$order->Fakthousing|escape}" placeholder="" required="true" />
                                                {if in_array('empty_fakthousing', (array)$faktaddress_error)}<small class="form-control-feedback">Укажите дом!</small>{/if}
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Строение</label>
                                                <input type="text" class="form-control js-dadata-building" name="Faktbuilding" value="{$order->Faktbuilding|escape}" placeholder="" />
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Квартира</label>
                                                <input type="text" class="form-control js-dadata-room" name="Faktroom" value="{$order->Faktroom|escape}" placeholder="" />
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-actions">
                                                <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                                <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <!-- /Адрес проживания -->

                            </div>

                            {include file='order/photo_block.tpl'
                                manager=$manager
                                order=$order
                                scorista_step_files=$scorista_step_files
                                files=$files
                                front_url=$front_url
                                is_post=$is_post
                                config=$config
                                images_error=$images_error
                                socials_error=$socials_error
                            }
                        </div>


                        {if $manager->role != 'verificator_minus'}
                            <!-- Контактные лица -->
                            {include file='order/contact_persons.tpl'
                                manager=$manager
                                order=$order
                                contactpersons=$contactpersons
                                contacts_error=$contacts_error
                            }
                            <!-- /Контактные лица -->
                        {/if}

                        <div class="row">
                            <div class="col-md-6">
                                <!-- Данные о работе -->
                                {if $scorista_step_additional_data}
                                    <div class="label label-danger my-2">
                                        <h5>По оценке скоринга у клиента no_need_for_underwriter=1. Фото/работа не запрашиваются у клиента</h5>
                                    </div>
                                {else}
                                    <form action="{url}" class="js-order-item-form mb-0" id="work_data_form">

                                        <input type="hidden" name="action" value="workdata" />
                                        <input type="hidden" name="order_id" value="{$order->order_id}" />
                                        <input type="hidden" name="user_id" value="{$order->user_id}" />

                                        <h3 class="box-title">
                                            {if (in_array($order->status, [1, 5, 6, 7]) && ($manager->id == $order->manager_id)) || in_array($manager->role, ['admin', 'developer', 'opr', 'ts_operator'])}
                                                <a href="javascript:void(0);" class="js-edit-form js-event-add-click"  data-event="16" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}">
                                                    <span>Данные о работе</span>
                                                </a>
                                            {else}
                                                <span>Данные о работе</span>
                                            {/if}
                                            <a href="javascript:void(0);" class="ml-3 js-open-comment-form" data-block="work">
                                                <i class="mdi mdi-comment-text"></i>
                                            </a>
                                        </h3>
                                        <hr>

                                        <div class="row view-block {if $workdata_error}hide{/if}">
                                            <div class="js-comments-block-work">{display_comments block='work'}</div>
                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <label class="control-label col-md-4">Сфера деятельности:</label>
                                                    <div class="col-md-8">
                                                        <p class="form-control-static">
                                                            <strong>{$order->work_scope|escape}</strong>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            {if $order->profession}
                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-4">Должность:</label>
                                                        <div class="col-md-8">
                                                            <p class="form-control-static">
                                                                <strong>{$order->profession|escape}</strong>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/if}
                                            {if $order->work_phone}
                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-4">Рабочий телефон:</label>
                                                        <div class="col-md-8">
                                                            <p class="form-control-static">
                                                                <strong>{$order->work_phone|escape}</strong>
                                                                <button class="btn waves-effect waves-light btn-xs btn-info js-mango-call js-event-add-click" data-phone="{$order->work_phone|escape}" data-event="24" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}" title="Выполнить звонок"><i class="fas fa-phone-square"></i></button>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/if}
                                            {if $order->identified_phone}
                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-4">Выявленный рабочий телефон:</label>
                                                        <div class="col-md-8">
                                                            <p class="form-control-static">
                                                                <strong>{$order->identified_phone|escape}</strong>
                                                                <button class="btn waves-effect waves-light btn-xs btn-info js-mango-call js-event-add-click" data-phone="{$order->identified_phone}" data-event="24" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}" title="Выполнить звонок"><i class="fas fa-phone-square"></i></button>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/if}
                                            {if $order->workplace}
                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-4">Название организации:</label>
                                                        <div class="col-md-8">
                                                            <p class="form-control-static">
                                                                <strong>{$order->workplace|escape}</strong>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/if}
                                            {if $order->workdirector_name}
                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-4">ФИО руководителя:</label>
                                                        <div class="col-md-8">
                                                            <p class="form-control-static">
                                                                <strong>{$order->workdirector_name|escape}</strong>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/if}
                                            {if $manager->role != 'verificator_minus'}
                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-4">Доход:</label>
                                                        <div class="col-md-8">
                                                            <p class="form-control-static">
                                                                <strong>{$order->income_base|escape}</strong>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/if}
                                        </div>

                                        <div class="row edit-block {if !$workdata_error}hide{/if}">
                                            {if $workdata_error}
                                                <div class="col-md-12">
                                                    <ul class="alert alert-danger">
                                                        {if in_array('empty_work_scope', (array)$workdata_error)}<li>Укажите сферу деятельности!</li>{/if}
                                                        {if in_array('empty_income_base', (array)$workdata_error)}<li>Укажите доход!</li>{/if}
                                                    </ul>
                                                </div>
                                            {/if}
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_income_base', (array)$workdata_error)}has-danger{/if}">
                                                    <label class="control-label">Сфера деятельности</label>
                                                    <input type="text" class="form-control" name="work_scope" value="{$order->work_scope|escape}" placeholder="" required="true" />
                                                    {if in_array('empty_income_base', (array)$workdata_error)}<small class="form-control-feedback">Укажите сферу деятельности!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">Должность</label>
                                                    <input type="text" class="form-control" name="profession" value="{$order->profession|escape}" placeholder="" />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">Рабочий телефон</label>
                                                    <input type="text" class="form-control" name="work_phone" value="{$order->work_phone|escape}" placeholder="" />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">Название организации</label>
                                                    <input type="text" class="form-control" name="workplace" value="{$order->workplace|escape}" placeholder="" />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">ФИО руководителя</label>
                                                    <input type="text" class="form-control" name="workdirector_name" value="{$order->workdirector_name|escape}" placeholder="" />
                                                </div>
                                            </div>
                                            {if $manager->role != 'verificator_minus'}
                                                <div class="col-md-4">
                                                    <div class="form-group {if in_array('empty_income_base', (array)$workdata_error)}has-danger{/if}">
                                                        <label class="control-label">Доход</label>
                                                        <input type="text" class="form-control" name="income_base" value="{$order->income_base|escape}" placeholder="" required="true" />
                                                        {if in_array('empty_income_base', (array)$workdata_error)}<small class="form-control-feedback">Укажите доход!</small>{/if}
                                                    </div>
                                                </div>
                                            {/if}
                                            <div class="col-md-12">
                                                <div class="form-actions">
                                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                                    <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                {/if}

                                <!-- Данные -->
                                <form action="{url}" method="post">

                                    <input type="hidden" name="action" value="add_identified_phone" />
                                    <input type="hidden" name="order_id" value="{$order->order_id}" />
                                    <input type="hidden" name="user_id" value="{$order->user_id}" />
                                    <div class="row">
                                        <label for="identified_phone" class="col-md-4">Добавить выявленный рабочий телефон</label>
                                        <div class="col-md-8">
                                            <input type="text" name="identified_phone" id="identified_phone" class="form-control" style="width: 226px;"/>
                                            <button type="submit" class="btn btn-large btn-primary" style="margin-bottom: 3px;">Добавить</button>
                                        </div>
                                    </div>

                                </form>
                                <!-- /Данные о работе -->
                            </div>
                            <div class="col-md-6">
                                <!-- Рабочий адрес -->
                                <form action="{url}" class="js-order-item-form mb-3" id="work_address_form">

                                    <input type="hidden" name="action" value="work_address" />
                                    <input type="hidden" name="order_id" value="{$order->order_id}" />
                                    <input type="hidden" name="user_id" value="{$order->user_id}" />

                                    <h3 class="box-title">
                                        {if (in_array($order->status, [1, 5, 6, 7]) && ($manager->id == $order->manager_id)) || in_array($manager->role, ['admin', 'developer', 'opr', 'ts_operator'])}
                                            <a href="javascript:void(0);" class="js-edit-form js-event-add-click"  data-event="17" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}">
                                                <span>Адрес Организации</span>
                                            </a>
                                        {else}
                                            <span>Адрес Организации</span>
                                        {/if}
                                        <a href="javascript:void(0);" class="ml-3 js-open-comment-form" data-block="workaddress">
                                            <i class="mdi mdi-comment-text"></i>
                                        </a>
                                    </h3>
                                    <hr>

                                    <div class="row view-block {if $workaddress_error}hide{/if}">
                                        <div class="js-comments-block-workaddress">{display_comments block='workaddress'}</div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <p class="form-control-static">
                                                    {if $order->Workregion}
                                                        <strong>
                                                            {if $order->Workregion}{$order->Workregion|escape},{/if}
                                                            {if $order->Workcity}{$order->Workcity|escape},{/if}
                                                            {if $order->Workstreet}{$order->Workstreet|escape},{/if}
                                                            {if $order->Workhousing}д.{$order->Workhousing|escape},{/if}
                                                            {if $order->Workbuilding}стр. {$order->Workbuilding|escape},{/if}
                                                            {if $order->Workroom}оф.{$order->Workroom|escape}{/if}
                                                        </strong>
                                                    {elseif $order->work_address}
                                                        Адрес 1С: <strong>{$order->work_address|escape}</strong>
                                                    {/if}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row edit-block js-dadata-address {if !$workaddress_error}hide{/if}">
                                        {if $workaddress_error}
                                            <div class="col-md-12">
                                                <ul class="alert alert-danger">
                                                    {if in_array('empty_workregion', (array)$workaddress_error)}<li>Укажите область!</li>{/if}
                                                    {if in_array('empty_workcity', (array)$workaddress_error)}<li>Укажите город!</li>{/if}
                                                    {if in_array('empty_workstreet', (array)$workaddress_error)}<li>Укажите улицу!</li>{/if}
                                                    {if in_array('empty_workhousing', (array)$workaddress_error)}<li>Укажите дом!</li>{/if}
                                                </ul>
                                            </div>
                                        {/if}
                                        <div class="col-md-4">
                                            <div class="form-group {if in_array('empty_workregion', (array)$workaddress_error)}has-danger{/if}">
                                                <label class="control-label">Область</label>
                                                <input type="text" class="form-control js-dadata-region" name="Workregion" value="{$order->Workregion|escape}" placeholder="" required="true" />
                                                {if in_array('empty_workregion', (array)$workaddress_error)}<small class="form-control-feedback">Укажите область!</small>{/if}
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group {if in_array('empty_workcity', (array)$workaddress_error)}has-danger{/if}">
                                                <label class="control-label">Город</label>
                                                <input type="text" class="form-control js-dadata-city" name="Workcity" value="{$order->Workcity|escape}" placeholder="" required="true" />
                                                {if in_array('empty_workcity', (array)$workaddress_error)}<small class="form-control-feedback">Укажите город!</small>{/if}
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group {if in_array('empty_workstreet', (array)$workaddress_error)}has-danger{/if}">
                                                <label class="control-label">Улица</label>
                                                <input type="text" class="form-control js-dadata-street" name="Workstreet" value="{$order->Workstreet|escape}" placeholder="" />
                                                {if in_array('empty_workstreet', (array)$workaddress_error)}<small class="form-control-feedback">Укажите улицу!</small>{/if}
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group {if in_array('empty_workhousing', (array)$workaddress_error)}has-danger{/if}">
                                                <label class="control-label">Дом</label>
                                                <input type="text" class="form-control js-dadata-house" name="Workhousing" value="{$order->Workhousing|escape}" placeholder="" required="true" />
                                                {if in_array('empty_workhousing', (array)$workaddress_error)}<small class="form-control-feedback">Укажите дом!</small>{/if}
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Строение</label>
                                                <input type="text" class="form-control js-dadata-building" name="Workbuilding" value="{$order->Workbuilding|escape}" placeholder="" />
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Офис</label>
                                                <input type="text" class="form-control js-dadata-room" name="Workroom" value="{$order->Workroom|escape}" placeholder="" required="true" />
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-actions">
                                                <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                                <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <!-- /Рабочий адрес -->
                            </div>
                        </div>
                        {if $manager->role != 'verificator_minus'}
                            <div class="row mt-3">
                                <button class="btn btn-danger"
                                        data-target="#leaveComplaint" data-toggle="modal" type="button">
                                    Оставить жалобу
                                </button>
                            </div>
                        {/if}
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
