{* report_missings_new.tpl (финальная версия) *}
{$meta_title = 'Отчёт по отвалам менеджера' scope=parent}

{capture name='page_styles'}
    <link href="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet" />
    <link href="design/{$settings->theme|escape}/assets/plugins/fancybox3/dist/jquery.fancybox.css" rel="stylesheet" />
    <link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid.min.css" />
    <link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid-theme.min.css" />
    <link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/css/report_missings.css" />

    <style>
        .stages-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }
        .stage-card {
            flex: 1;
            min-width: 100px;
            background: rgba(255,255,255,0.08);
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            color: #fff;
        }
        .stage-label {
            font-size: 13px;
            color: #bbb;
        }
        .stage-value {
            font-size: 18px;
            font-weight: bold;
        }
    </style>
{/capture}

<div class="page-wrapper">
    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-3">
            <div class="col-8">
                <h3>Отчёт по менеджеру — {$manager_data->name|default:'Не найдено'}</h3>
                <small class="text-muted">Период: {$date_from|default:'-'} — {$date_to|default:'-'}</small>
            </div>
        </div>

        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Главная</a></li>
            <li class="breadcrumb-item"><a href="/report_missings_new">Отчёт по отвалам</a></li>
            <li class="breadcrumb-item active">Отчёт по отвалам менеджера</li>
        </ol>

        <!-- KPI cards -->
        <div class="kpi-grid mt-3">

            <!-- Всего -->
            <div class="kpi-card kpi-blue">
                <div class="kpi-body">
                    <div class="kpi-label">Всего заявок</div>
                    <div class="kpi-value" data-animate="true">{$statistic->totals|default:'0'}</div>
                    <div class="kpi-sub">за период</div>
                </div>
            </div>

            <!-- Дозвонились -->
            <div class="kpi-card kpi-green">
                <div class="kpi-body">
                    <div class="kpi-label">Дозвонились</div>
                    <div class="kpi-value" data-animate="true">{$statistic->accepted_calls|default:'0'}</div>
                    <div class="kpi-sub">из {$statistic->total_calls|default:'0'} звонков</div>
                </div>
            </div>

            <!-- Продолжат оформление -->
            <div class="kpi-card kpi-yellow">
                <div class="kpi-body">
                    <div class="kpi-label">Продолжат оформление</div>
                    <div class="kpi-value" data-animate="true">{$statistic->continue_count|default:'0'}</div>
                    <div class="kpi-sub">Выдано: {$statistic->issued_from_continue|default:'0'} ({$statistic->conversion_continue|default:'0'}%)</div>
                </div>
            </div>

            <!-- Заполнено полностью -->
            <div class="kpi-card kpi-gray">
                <div class="kpi-body">
                    <div class="kpi-label">Заполнено полностью</div>
                    <div class="kpi-value" data-animate="true">{$statistic->completed_total|default:'0'}</div>
                    <div class="kpi-sub">С менеджером: {$statistic->completed_with_manager|default:'0'}, Самостоятельно: {$statistic->completed_self|default:'0'}</div>
                </div>
            </div>

            <!-- Выдано займов -->
            <div class="kpi-card kpi-green">
                <div class="kpi-body">
                    <div class="kpi-label">Выдано займов</div>
                    <div class="kpi-value" data-animate="true">{$statistic->users_loan_issued|default:'0'}</div>
                    <div class="kpi-sub">Всего: {$statistic->loans_issued_total|default:'0'} | %: {$statistic->conversion_total|default:'0'}%</div>
                </div>
            </div>

            <!-- Отказы -->
            <div class="kpi-card kpi-red">
                <div class="kpi-body">
                    <div class="kpi-label">Отказы</div>
                    <div class="kpi-value" data-animate="true">{$statistic->users_loan_rejected|default:'0'}</div>
                    <div class="kpi-sub">%: {$statistic->conversion_rejected|default:'0'}%</div>
                </div>
            </div>

            <!-- Средняя длительность -->
            {assign var="avgMin" value=$statistic->avg_duration_accepted_calls|default:0}
            {assign var="avgSec" value=$avgMin*60|round:0}
            <div class="kpi-card kpi-blue">
                <div class="kpi-body">
                    <div class="kpi-label">Средняя длит. разговора</div>
                    <div class="kpi-value" data-animate="true">{$avgMin|round:2}</div>
                    <div class="kpi-sub">({$avgSec} сек)</div>
                </div>
            </div>

            <!-- Новые клиенты -->
            <div class="kpi-card kpi-purple">
                <div class="kpi-body">
                    <div class="kpi-label">Новые клиенты (в тот же день)</div>
                    <div class="kpi-value" data-animate="true">{$statistic->new_clients_today|default:'0'}</div>
                </div>
            </div>

        </div>

        <!-- Этапы -->
        <div class="row mt-4">
            <div class="col-12">
                <h5>Распределение по этапам</h5>
                <div class="stages-grid">
                    {foreach from=[1,2,3,4,5,6,7] item=stage}
                        {assign var="count" value=$statistic->{"stage`$stage`"}|default:0}
                        <div class="stage-card">
                            <div class="stage-label">Этап {$stage}</div>
                            <div class="stage-value">{$count}</div>
                        </div>
                    {/foreach}
                </div>
            </div>
        </div>

        <!-- Filters -->
        <form method="get" class="mb-3">
            <input type="hidden" name="manager_id" value="{$manager_id|default:''}">
            <div class="form-row align-items-end">
                <div class="col-auto">
                    <label class="small text-muted">Дата от</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{$date_from|default:''}">
                </div>
                <div class="col-auto">
                    <label class="small text-muted">Дата до</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{$date_to|default:''}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Применить</button>
                </div>
            </div>
        </form>

        <!-- Table -->
        <div class="table-responsive mt-3">
            <table class="table table-sm table-hover table-bordered">
                <thead class="thead-light">
                <tr>
                    <th>№</th>
                    <th>Дата заявки</th>
                    <th>ID</th>
                    <th>Телефон</th>
                    <th>Менеджер</th>
                    <th>Время контакта</th>
                    <th>Этап</th>
                    <th>Статус звонка</th>
                    <th>Продолжит</th>
                    <th>Заполнено</th>
                    <th>Последний этап</th>
                    <th>Выдано</th>
                    <th>Источник</th>
                </tr>
                </thead>
                <tbody>
                {if $manager_details|@count > 0}
                    {foreach from=$manager_details key=idx item=row}
                        <tr>
                            <td>{$idx+1}</td>
                            <td>{$row->created|date_format:"%d.%m.%Y %H:%M:%S"|default:'—'}</td>
                            <td>{$row->id|default:'—'}</td>
                            <td>{$row->phone_mobile|default:'—'}</td>
                            <td>{$row->manager_name|default:'—'}</td>
                            <td>{if $row->last_call}{$row->last_call|date_format:"%d.%m.%Y %H:%M:%S"}{else}—{/if}</td>
                            <td>{$row->stage_in_contact|default:'—'}</td>
                            <td>{$row->call_status_text|default:'—'}</td>
                            <td>{$row->continue_order_text|default:'—'}</td>
                            <td>{$row->completed|default:'—'}</td>
                            <td>{$row->last_step|default:'—'}</td>
                            <td>{$row->loan_issued|default:'—'}</td>
                            <td>{$row->utm_source|default:'—'}</td>
                        </tr>
                    {/foreach}
                {else}
                    <tr>
                        <td colspan="13" class="text-center text-muted">Нет данных за выбранный период</td>
                    </tr>
                {/if}
                </tbody>
            </table>
        </div>
    </div>

    {include file='footer.tpl'}
</div>
