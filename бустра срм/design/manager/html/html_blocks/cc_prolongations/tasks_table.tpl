{* ============================================ *}
{* БЛОК: Таблица задач                          *}
{* Отвечает за отображение таблицы задач        *}
{* пролонгации с фильтрацией, сортировкой       *}
{* и пагинацией                                  *}
{* ============================================ *}

{if !$tasks|count}
    <div class="alert alert-danger">
        <h3 class="text-danger">Нет распределенных договоров</h3>
    </div>
{/if}

<div id="basicgrid" class="jsgrid" style="position: relative; width: 100%;">
    {* ============================================ *}
    {* Заголовок таблицы с сортировкой              *}
    {* ============================================ *}
    <div class="jsgrid-grid-header jsgrid-header-scrollbar">
        <table class="jsgrid-table table table-striped table-hover">
            <tr class="jsgrid-header-row">
                <th style="width: 45px;" class="jsgrid-header-cell jsgrid-header-sortable">Call Blacklist</th>
                <th style="width: 90px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'fio_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'fio_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                    {if $sort == 'fio_asc'}<a href="{url page=null sort='fio_desc'}">ФИО</a>
                    {else}<a href="{url page=null sort='fio_asc'}">ФИО</a>{/if}
                </th>
                <th style="width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'phone_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'phone_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                    {if $sort == 'phone_asc'}<a href="{url page=null sort='phone_desc'}">Телефон</a>
                    {else}<a href="{url page=null sort='phone_asc'}">Телефон</a>{/if}
                </th>
                <th style="width: 40px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'timezone_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'timezone_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                    {if $sort == 'timezone_asc'}<a href="{url page=null sort='timezone_desc'}">Время</a>
                    {else}<a href="{url page=null sort='timezone_asc'}">Время</a>{/if}
                </th>
                <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'number_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'number_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                    {if $sort == 'number_asc'}<a href="{url page=null sort='number_desc'}">Займ</a>
                    {else}<a href="{url page=null sort='number_asc'}">Займ</a>{/if}
                </th>
                <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'date_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'date_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                    {if $sort == 'date_asc'}<a href="{url page=null sort='date_desc'}">Дата выдачи</a>
                    {else}<a href="{url page=null sort='date_asc'}">Дата выдачи</a>{/if}
                </th>
                <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'summ_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'summ_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                    {if $sort == 'summ_asc'}<a href="{url page=null sort='summ_desc'}">Сумма</a>
                    {else}<a href="{url page=null sort='summ_asc'}">Сумма</a>{/if}
                </th>
                <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'payment_date_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'payment_date_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                    {if $sort == 'payment_date_asc'}<a href="{url page=null sort='payment_date_desc'}">Дата возврата</a>
                    {else}<a href="{url page=null sort='payment_date_asc'}">Дата возврата</a>{/if}
                </th>
                <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'payment_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'payment_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                    {if $sort == 'payment_asc'}<a href="{url page=null sort='payment_desc'}">Сумма возврата</a>
                    {else}<a href="{url page=null sort='payment_asc'}">Сумма возврата</a>{/if}
                </th>
                <th style="width: 72px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'prolongation_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'prolongation_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                    {if $sort == 'prolongation_asc'}<a href="{url page=null sort='prolongation_desc'}">Пролонгация</a>
                    {else}<a href="{url page=null sort='prolongation_asc'}">Пролонгация</a>{/if}
                </th>
                <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'manager_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'manager_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                    {if $sort == 'manager_asc'}<a href="{url page=null sort='manager_desc'}">Ответственный</a>
                    {else}<a href="{url page=null sort='manager_asc'}">Ответственный</a>{/if}
                </th>
                <th style="width: 50px;" class="text-right jsgrid-header-cell jsgrid-header-sortable {if $sort == 'status_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'status_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                    {if $sort == 'status_asc'}<a href="{url page=null sort='status_desc'}">Статус</a>
                    {else}<a href="{url page=null sort='status_asc'}">Статус</a>{/if}
                </th>
                <th style="width:75px" class="text-right jsgrid-header-cell" ></th>
            </tr>

            {* ============================================ *}
            {* Строка фильтров                              *}
            {* ============================================ *}
            <tr class="jsgrid-filter-row" id="search_form">
                <td style="width: 45px;" class="jsgrid-cell jsgrid-align-center">
{*                                            <label for="sms_check_all" class="check_all_checkbox">*}
{*                                                <input  type="checkbox" name="sms_check_all" id="sms_check_all" value="0" />*}
{*                                                <div></div>*}
{*                                            </label>*}
                </td>
                <td style="width: 90px;" class="jsgrid-cell jsgrid-align-right">
                    <input type="hidden" name="sort" value="{$sort}" />
                    <input type="text" name="fio" value="{$search['fio']}" class="form-control input-sm">
                </td>
                <td style="width: 80px;" class="jsgrid-cell jsgrid-align-right">
                    <input type="text" name="phone" value="{$search['phone']}" class="form-control input-sm">
                </td>
                <td style="width: 40px;" class="jsgrid-cell jsgrid-align-right">

                </td>
                <td style="width: 60px;" class="jsgrid-cell jsgrid-align-right">
                    <input type="text" name="number" value="{$search['number']}" class="form-control input-sm">
                </td>
                <td style="width: 60px;" class="jsgrid-cell jsgrid-align-right">
                </td>
                <td style="width: 60px;" class="jsgrid-cell jsgrid-align-right">
                </td>
                <td style="width: 60px;" class="jsgrid-cell jsgrid-align-right">
                </td>
                <td style="width: 60px;" class="jsgrid-cell">
                </td>
                <td style="width: 60px;" class="jsgrid-cell"></td>
                <td style="width: 60px;" class="jsgrid-cell">
                    <select name="manager" class="form-control input-sm">
                        <option value=""></option>
                        {foreach $managers as $m}
                        {if $m->role == 'contact_center' || $m->role == 'contact_center_robo'}
                        <option value="{$m->id}" {if $m->id == $search['manager']}selected="true"{/if}>{$m->name|escape}</option>
                        {/if}
                        {/foreach}
                    </select>
                </td>
                <td style="width: 50px;" class="jsgrid-cell">
                    <select name="status" class="form-control input-sm">
                        <option value=""></option>
                        {foreach $pr_statuses as $ts_id => $ts}
                        <option value="{$ts_id}" {if $ts_id === $search['status']}selected="true"{/if}>{$ts|escape}</option>
                        {/foreach}
                    </select>
                </td>
                <td style="width: 75px;" class="jsgrid-cell">
                </td>
            </tr>

        </table>
    </div>
    {* ============================================ *}
    {* Тело таблицы с данными задач                 *}
    {* ============================================ *}
    <div class="jsgrid-grid-body">
        <table class="jsgrid-table table table-striped table-hover ">
            <tbody>
            {foreach $tasks as $task}
                <tr class="jsgrid-row" id="main_{$task->id}">
                    <td style="width: 45px;" class="jsgrid-cell jsgrid-align-center">
                        <label for="sms_check_{$task->user->id}" class="user_checkbox">
                            <input name="sms_check[]" type="checkbox" id="sms_check_{$task->user->id}" value="{$task->user->id}" />
                            {if empty($task->callsBlacklist)}<div></div>{/if}
                        </label>
                    </td>
                    <td style="width: 90px;" class="jsgrid-cell jsgrid-align-right">
                        <div class="button-toggle-wrapper">
                            <button class="js-open-order button-toggle" data-id="{$task->id}" data-site-id="{$task->user->site_id}" data-uid="{$task->user->UID}" data-number="{$task->balance->zaim_number}" type="button" title="Подробнее"></button>
                        </div>
                        <a href="client/{$task->user->id}" target="_blank">
                            {$task->user->lastname|escape} {$task->user->firstname|escape} {$task->user->patronymic|escape}
                        </a>
                        {$task->id}
                        {if $task->looker_link}
                            <a href="{$task->looker_link}" class="float-left btn-info btn waves-effect waves-light btn-xs" target="_blank">
                                <i class="fas fa-user"></i>
                            </a>
                        {/if}
                    </td>
                    <td style="width: 80px;" class="jsgrid-cell">
                        <strong>{$task->user->phone_mobile|escape}</strong>
                        <button class="btn waves-effect waves-light btn-xs btn-info float-right js-mango-call" data-phone="{$task->user->phone_mobile}" data-user="{$task->user->id}" title="Выполнить звонок">
                            <i class="fas fa-phone-square"></i>
                        </button>
                        {if empty($task->callsBlacklist)}
                            <button
                                class="btn waves-effect waves-light btn-xs btn-success js-voximplant-call"
                                data-phone="{$task->user->phone_mobile|escape}">
                            <i class="fas fa-phone-square"></i>
                        </button>
                        {/if}
                    </td>
                    <td style="width: 40px;" class="jsgrid-cell">
                        <div>{if $task->timezone >= 0} + {/if}{$task->timezone}:00 </div>
                    </td>
                    <td style="width: 60px;" class="jsgrid-cell">
                        {if $task->order}
                        <a href="order/{$task->order->order_id}">{$task->zaim_number}</a>
                        {else}
                        <div>{$task->balance->zaim_number}</div>
                        {/if}

                        {if $task->close}
                            <span class="label label-primary">Закрыт</span>
                        {/if}
                        {if $task->prolongation}
                            <span class="label label-success">Продлен</span>
                        {/if}
                    </td>
                    <td style="width: 60px;" class="jsgrid-cell">
                        {$task->balance->zaim_date|date}
                        <br />
                        <button class="js-get-movements btn btn-link btn-xs js-no-peni" data-number="{$task->balance->zaim_number}">Операции</button>
                    </td>
                    <td style="width: 60px;" class="jsgrid-cell zaim-sum">
                        {$task->balance->zaim_summ}
                        {if $task->balance->percent}<span class="label label-danger">{$task->balance->percent}%</span>
                        {else}<span class="label label-success">{$task->balance->percent}%</span>{/if}
                    </td>
                    <td style="width: 60px;" class="jsgrid-cell zaim-payment-date">
                        {$task->balance->payment_date|date}
                    </td>
                    <td style="width: 60px;" class="jsgrid-cell zaim-ostatok-odd">
                        <a class="" href="#" data-toggle="collapse" data-target="#ostatok_{$task->id}">
                            {if $task->balance->loan_type == 'IL' && $task->balance->overdue_debt_od_IL+ $task->balance->next_payment_od > 0}
                                {$task->balance->overdue_debt_od_IL + $task->balance->overdue_debt_percent_IL + $task->balance->next_payment_od+$task->balance->next_payment_percent}
                            {elseif $task->balance->ostatok_od > 0}
                                {$task->balance->ostatok_od + $task->balance->ostatok_percents + $task->balance->ostatok_peni}
                            {/if}
                        </a>
                        <div id="ostatok_{$task->id}" class="collapse">
                            <div>Основной долг:
                                <strong>
                                    {if $task->balance->loan_type == 'IL'}
                                        {$task->balance->overdue_debt_od_IL+ $task->balance->next_payment_percent}
                                    {else}
                                        {$task->balance->ostatok_od}
                                    {/if}
                                </strong>
                            </div>
                            <div>Проценты:
                                <strong>
                                    {if $task->balance->loan_type == 'IL'}
                                        {$task->balance->overdue_debt_percent_IL + $task->balance->next_payment_od}
                                    {else}
                                        {$task->balance->ostatok_percents}
                                    {/if}
                                </strong>
                            </div>
                            <div>Пени: <strong>{$task->balance->ostatok_peni}</strong></div>
                        </div>
                    </td>
                    <td style="width: 72px;" class="jsgrid-cell zaim-prolongation">
                        {if $task->balance->prolongation_count == 5 || $task->balance->loan_type == 'IL'}
                            <span data-toggle="collapse">Пролонгация недоступна</span>
                        {elseif $task->balance->prolongation_amount > 0}
                        <a class="" href="#" data-toggle="collapse" data-target="#prolongation_{$task->id}">{$task->balance->prolongation_amount + $tv_medical_price}</a>
                        {/if}
                        {if $task->balance->last_prolongation == 2}
                        <span class="label label-danger float-right" title="Количество пролонгаций займа">
                        {elseif $task->balance->last_prolongation == 1}
                        <span class="label label-warning float-right" title="Количество пролонгаций займа">
                        {else}
                        <span class="label label-primary float-right" title="Количество пролонгаций займа">
                        {/if}
                            <h6 class="m-0">{$task->balance->prolongation_count}</h6>
                        </span>
                        <div id="prolongation_{$task->id}" class="collapse">
                            {if $task->balance->prolongation_summ_percents > 0}
                            <div class="prolongation_percent">Проценты: <strong>{1 * $task->balance->prolongation_summ_percents}</strong></div>
                            {/if}
                            {if $task->balance->prolongation_summ_insurance > 0}
                            <div class="prolongation_insurer">Страховка: <strong>{1 * $task->balance->prolongation_summ_insurance}</strong></div>
                            {/if}
                            {if $task->balance->prolongation_summ_cost > 0}
                            <div class="prolongation_prolongation">Пролонгация: <strong>{1 * $task->balance->prolongation_summ_cost}</strong></div>
                            {/if}
                            {if $task->balance->prolongation_summ_sms > 0}
                            <div class="prolongation_sms">СМС-информ: <strong>{1 * $task->balance->prolongation_summ_sms}</strong></div>
                            {/if}
                            <div>Телемедицина (Лайт): <strong>{1 * $tv_medical_price}</strong></div>
                        </div>
                        {if $is_developer}
                        <br />
                        <small title="Дата обновления">{$task->balance->last_update|date}</small>
                        {/if}
                    </td>
                    <td style="width: 60px;" class="jsgrid-cell">
                        {$managers[$task->manager_id]->name|escape}
                    </td>
                    <td style="width: 50px;" class="jsgrid-cell text-right">
                        <div class="btn-group js-status-{$task->id}">
                            {if $task->status == 0}<button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Новая</button>{/if}
                            {if $task->status == 1}<button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Перезвон</button>{/if}
                            {if $task->status == 2}<button type="button" class="btn btn-warning btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Недозвон</button>{/if}
                            {if $task->status == 3}<button type="button" class="btn btn-success btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Перспектива</button>{/if}
                            {if $task->status == 4}<button type="button" class="btn btn-danger btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Отказ</button>{/if}
                            <div class="dropdown-menu" x-placement="bottom-start">
                                {if $task->status != 1}<a class="dropdown-item text-primary js-toggle-status-recall" data-status="1" data-task="{$task->id}" href="javascript:void(0)">Перезвон</a>{/if}
                                {if $task->status != 2}<a class="dropdown-item text-warning js-toggle-status" data-status="2" data-task="{$task->id}" href="javascript:void(0)">Недозвон</a>{/if}
                                {if $task->status != 3}<a class="dropdown-item text-success js-toggle-status-perspective" data-status="3" data-task="{$task->id}" href="javascript:void(0)">Перспектива</a>{/if}
                                {if $task->status != 4}<a class="dropdown-item text-danger js-toggle-status" data-status="4" data-task="{$task->id}" href="javascript:void(0)">Отказ</a>{/if}
                            </div>
                        </div>
                        {if $task->status == 1 && $task->recall_date}
                        <small>{$task->recall_date|date} {$task->recall_date|time}</small>
                        {/if}
                        {if $task->status == 3 && $task->perspective_date}
                        <small>{$task->perspective_date|date} {$task->perspective_date|time}</small>
                        {/if}
                    </td>
                    <td style="width:75px;" class="jsgrid-cell text-right">
                        <button type="button" class="btn btn-sm btn-warning update-user-balance" title="Обновить баланс" data-task="{$task->id}" data-user="{$task->user->id}">
                            <i class="mdi mdi-refresh"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-success js-open-comment-form" title="Добавить комментарий" data-order="{$task->order->order_id}" data-user="{$task->user->id}" data-task="{$task->id}" data-uid="{$task->user->UID}">
                            <i class="mdi mdi-comment-text"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-primary js-open-sms-modal" title="Отправить смс" data-user="{$task->user->id}">
                            <i class=" far fa-share-square"></i>
                        </button>
                    </td>
                </tr>
                {* ============================================ *}
                {* Детали задачи (комментарии и калькулятор)    *}
                {* ============================================ *}
                <tr class="order-details" id="changelog_{$task->id}" style="display:none">
                    <td colspan="11">
                        <div class="row">

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title">
                                            <span>Комментарии</span>
                                            <a href="javascript:void(0);" class="ml-3 js-open-comment-form btn btn-success btn-sm btn-rounded float-right" data-order="{$task->order->order_id}" data-user="{$task->user->id}" data-task="{$task->balance->id}" data-uid="{$task->user->UID}">
                                                <i class="mdi mdi-comment-text"></i> Добавить
                                            </a>
                                        </h4>
                                    </div>
                                    <div class="js-comments comment-widgets cctasks-comments">

                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <form class="js-calc-form">

                                        <input type="hidden" class="js-calc-zaim-summ" value="{$task->balance->zaim_summ}" />
                                        <input type="hidden" class="js-calc-percent" value="{$task->balance->percent}" />
                                        <input type="hidden" class="js-calc-ostatok-od" value="{$task->balance->ostatok_od}" />
                                        <input type="hidden" class="js-calc-ostatok-percents" value="{$task->balance->ostatok_percents}" />
                                        <input type="hidden" class="js-calc-ostatok-peni" value="{$task->balance->ostatok_peni}" />
                                        <input type="hidden" class="js-calc-payment-date" value="{$task->balance->payment_date}" />
                                        <input type="hidden" class="js-calc-allready-added" value="{$task->balance->allready_added}" />

                                        <input type="hidden" class="js-calc-prolongation-summ-insurance" value="{$task->balance->prolongation_summ_insurance}" />
                                        <input type="hidden" class="js-calc-prolongation-summ-sms" value="{$task->balance->prolongation_summ_sms}" />
                                        <input type="hidden" class="js-calc-prolongation-summ-cost" value="{$task->balance->prolongation_summ_cost}" />


                                        <div class="card-body">
                                            <h4 class="card-title">
                                                <span>Калькулятор</span>
                                            </h4>
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="input-group mb-3">
                                                        <input type="text" class="form-control singledate js-calc-input" value="" />
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">
                                                                <span class="ti-calendar"></span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <button type="submit" class="btn btn-primary js-calc-run">Рассчитать</button>
                                                </div>
                                                <div class="js-calc-result col-12">

                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        </div>
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>

    {* ============================================ *}
    {* Пагинация                                    *}
    {* ============================================ *}
    {if $total_pages_num>1}

    {* Количество выводимых ссылок на страницы *}
    {$visible_pages = 11}
    {* По умолчанию начинаем вывод со страницы 1 *}
    {$page_from = 1}

    {* Если выбранная пользователем страница дальше середины "окна" - начинаем вывод уже не с первой *}
    {if $current_page_num > floor($visible_pages/2)}
        {$page_from = max(1, $current_page_num-floor($visible_pages/2)-1)}
    {/if}

    {* Если выбранная пользователем страница близка к концу навигации - начинаем с "конца-окно" *}
    {if $current_page_num > $total_pages_num-ceil($visible_pages/2)}
        {$page_from = max(1, $total_pages_num-$visible_pages-1)}
    {/if}

    {* До какой страницы выводить - выводим всё окно, но не более ощего количества страниц *}
    {$page_to = min($page_from+$visible_pages, $total_pages_num-1)}

    <div class="jsgrid-pager-container" style="">
        <div class="jsgrid-pager">
            Страницы:

            {if $current_page_num == 2}
            <span class="jsgrid-pager-nav-button "><a href="{url page=null}">Пред.</a></span>
            {elseif $current_page_num > 2}
            <span class="jsgrid-pager-nav-button "><a href="{url page=$current_page_num-1}">Пред.</a></span>
            {/if}

            <span class="jsgrid-pager-page {if $current_page_num==1}jsgrid-pager-current-page{/if}">
                {if $current_page_num==1}1{else}<a href="{url page=null}">1</a>{/if}
            </span>
            {section name=pages loop=$page_to start=$page_from}
                {* Номер текущей выводимой страницы *}
                {$p = $smarty.section.pages.index+1}
                {* Для крайних страниц "окна" выводим троеточие, если окно не возле границы навигации *}
                {if ($p == $page_from + 1 && $p != 2) || ($p == $page_to && $p != $total_pages_num-1)}
                <span class="jsgrid-pager-page {if $p==$current_page_num}jsgrid-pager-current-page{/if}">
                    <a href="{url page=$p}">...</a>
                </span>
                {else}
                <span class="jsgrid-pager-page {if $p==$current_page_num}jsgrid-pager-current-page{/if}">
                    {if $p==$current_page_num}{$p}{else}<a href="{url page=$p}">{$p}</a>{/if}
                </span>
                {/if}
            {/section}
            <span class="jsgrid-pager-page {if $current_page_num==$total_pages_num}jsgrid-pager-current-page{/if}">
                {if $current_page_num==$total_pages_num}{$total_pages_num}{else}<a href="{url page=$total_pages_num}">{$total_pages_num}</a>{/if}
            </span>

            {if $current_page_num<$total_pages_num}
            <span class="jsgrid-pager-nav-button"><a href="{url page=$current_page_num+1}">След.</a></span>
            {/if}
            &nbsp;&nbsp; {$current_page_num} из {$total_pages_num}
        </div>
    </div>
    {/if}

    {* ============================================ *}
    {* Индикаторы загрузки                         *}
    {* ============================================ *}
    <div class="jsgrid-load-shader" style="display: none; position: absolute; inset: 0px; z-index: 10;">
    </div>
    <div class="jsgrid-load-panel" style="display: none; position: absolute; top: 50%; left: 50%; z-index: 1000;">
        Идет загрузка...
    </div>
</div>

