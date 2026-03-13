{$meta_title='Отчет по восстановлению услуг' scope=parent}

{capture name='page_styles'}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .report-card {
        background-color: #2a2e33;
        border: 1px solid #404850;
        border-radius: .5rem;
        color: #e2e2e2;
        padding: 1.25rem;
        margin-bottom: 1rem;
    }
    .report-card .stat-value {
        font-size: 2rem;
        font-weight: 500;
        color: #fff;
    }
    .report-card .stat-label {
        font-size: .9rem;
        color: #a0a0a0;
    }
    .report-card .stat-value .mdi {
        vertical-align: middle;
    }
    .table-report {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .table-report th,
    .table-report td {
        vertical-align: middle;
        white-space: nowrap;
        border: 1px solid #2d3338;
        padding: 10px;
    }
    .table-report thead th {
        background-color: #2d3338;
        color: #fff;
    }
    .table-report tbody td {
        background-color: #212529;
        color: #c8c8c8;
    }
    .select2-container--default .select2-selection--multiple {
        background-color: #2a2e33;
        border-color: #404850;
        color: #e2e2e2;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #01c0c8;
        border-color: #01c0c8;
        color: #fff;
    }
</style>
{/capture}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-12 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-chart-bar"></i>
                    <span>Отчет по доходам от восстановленных услуг</span>
                </h3>
            </div>
        </div>

        {* Фильтры *}
        <div class="card">
            <div class="card-body">
                <form method="get" class="row">
                    <div class="form-group col-md-3">
                        <label>Дата восстановления (От)</label>
                        <input type="date" name="date_from" class="form-control" value="{$filters->getDateFrom()->format('Y-m-d')}">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Дата восстановления (До)</label>
                        <input type="date" name="date_to" class="form-control" value="{$filters->getDateTo()->format('Y-m-d')}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Правила</label>
                        <select name="rule_ids[]" class="form-control select2" multiple>
                            {foreach $all_rules as $rule}
                                <option value="{$rule->getId()}" {if in_array($rule->getId(), $filters->getRuleIds())}selected{/if}>
                                    {$rule->getName()|escape}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="form-group col-md-2 align-self-end">
                        <button type="submit" class="btn btn-primary btn-block">Применить</button>
                    </div>
                </form>
            </div>
        </div>

        {* Сводка *}
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="report-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-value text-success">{$report->getTotalNetRevenue()|number_format:2:".":" "}&nbsp;₽</div>
                    </div>
                    <div class="stat-label">Чистая прибыль</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="report-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-value">{$report->getTotalRevenue()|number_format:2:".":" "}&nbsp;₽</div>
                    </div>
                    <div class="stat-label">Всего поступлений</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="report-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-value">{$report->getTotalPaid()|number_format:0:".":" "} / {$report->getTotalReenabled()|number_format:0:".":" "}</div>
                    </div>
                    <div class="stat-label">Оплачено / Восстановлено</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="report-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-value text-danger">{$report->getTotalRefunds()|number_format:2:".":" "}&nbsp;₽</div>
                    </div>
                    <div class="stat-label">Всего возвратов</div>
                </div>
            </div>
        </div>

        {* Детализация по правилам *}
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Детализация по правилам</h5>
                <div class="table-responsive">
                    <table class="table-report">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Правило</th>
                            <th class="text-right">Восстановлено услуг</th>
                            <th class="text-right">Оплачено услуг</th>
                            <th class="text-right">Поступления</th>
                            <th class="text-right">Возвраты</th>
                            <th class="text-right">Чистая прибыль</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $report->getDetails() as $detail}
                            <tr>
                                <td>{$detail->rule_id}</td>
                                <td>{$detail->rule_name|escape}</td>
                                <td class="text-right">{$detail->reenabled_count|number_format:0:".":" "}</td>
                                <td class="text-right">{$detail->paid_count|number_format:0:".":" "}</td>
                                <td class="text-right text-success">{$detail->total_revenue|number_format:2:".":" "}</td>
                                <td class="text-right text-danger">{$detail->total_refunds|number_format:2:".":" "}</td>
                                <td class="text-right text-info font-weight-bold">{$detail->net_revenue|number_format:2:".":" "}</td>
                            </tr>
                            {foreachelse}
                            <tr>
                                <td colspan="7" class="text-center">Нет данных для отображения за выбранный период.</td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

{capture name='page_scripts'}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        {literal}
        $(document).ready(function() {
            $('.select2').select2();
        });
        {/literal}
    </script>
{/capture}
