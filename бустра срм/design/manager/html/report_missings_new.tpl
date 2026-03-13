{$meta_title = 'Отчёт по отвалам' scope=parent}

{capture name='page_styles'}
    <link href="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet" />
    <link href="design/{$settings->theme|escape}/assets/plugins/fancybox3/dist/jquery.fancybox.css" rel="stylesheet" />
    <link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid.min.css" />
    <link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid-theme.min.css" />
    <link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/css/report_missings.css" />
{/capture}

<div class="page-wrapper">
    <div class="container-fluid mt-3">

        {* --- Табы --- *}
        <div class="row mb-2">
            <div class="col-12">
                <ul class="nav nav-tabs">
                    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab_calls">Отчёт по звонкам</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab_ivr">IVR статистика</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab_icr">ICR чаты</a></li>
                </ul>
            </div>
        </div>

        <div class="tab-content">
            <div id="tab_calls" class="tab-pane fade show active">

                {* --- Заголовок + кнопка --- *}
                <div class="d-flex align-items-center justify-content-between page-header">
                    <div>
                        <h3 class="mb-0">Отчёт по отвалам</h3>
                        <div class="small-muted">Клик по менеджеру в таблице ниже — переход к деталям.</div>
                        <div class="mini-muted">Период: {$date_from|escape} — {$date_to|escape}</div>
                    </div>
                    <div class="d-flex align-items-center" style="gap:8px;">
                        <a id="exportLink" href="{$report_uri}?action=download&date_from={$date_from|escape}&date_to={$date_to|escape}" class="btn btn-success">
                            <i class="fa fa-file-excel-o"></i> Выгрузить в Excel
                        </a>
                    </div>
                </div>

                {* --- Фильтры --- *}
                <form id="filtersForm" method="get" action="{$report_uri}" class="card mb-3 p-3 filters">
                    <div class="form-row align-items-end">
                        <div class="form-group">
                            <label for="date_from">Дата от</label>
                            <input type="date" id="date_from" name="date_from" value="{$date_from|escape}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="date_to">по</label>
                            <input type="date" id="date_to" name="date_to" value="{$date_to|escape}" class="form-control">
                        </div>
                        <div class="form-group ml-auto">
                            <button type="submit" class="btn btn-primary">Применить фильтры</button>
                        </div>
                    </div>
                </form>

                {* --- KPI блок --- *}
                <div class="card mb-3 kpi">
                    <div id="kpiBlock" class="card-body text-center">
                        <i class="fa fa-spinner fa-spin fa-2x"></i> Загрузка KPI...
                    </div>
                </div>

                <div id="stagesBlock" class="mb-3">
                    <h6 class="mb-2">Стадии (подробно)</h6>
                    <div class="row" id="stagesRow">
                        <div class="col-12 text-center text-muted">
                            <i class="fa fa-spinner fa-spin"></i> Загрузка стадий...
                        </div>
                    </div>
                </div>

                {* --- VOX: KPI сводка --- *}
                <div class="card mb-3">
                    <div class="card-header sticky">
                        <h5 class="mb-0 text-primary">VOX (автообзвон по отвалам) — сводка</h5>
                    </div>
                    <div id="voxKpiBlock" class="card-body text-center">
                        <i class="fa fa-spinner fa-spin fa-2x"></i> Загрузка VOX KPI...
                    </div>
                </div>

                {* --- VOX: динамика по дням (график + таблица) --- *}
                <div class="card mb-4">
                    <div class="card-header sticky d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0 text-primary">VOX звонки по дням</h5>
                            <small class="text-muted">Stacked: создано / не дозвонились / дозвонились / дошли до менеджера</small>
                        </div>
                        <button type="button" id="refreshVoxBtn" class="btn btn-outline-secondary btn-sm">
                            <i class="fa fa-refresh"></i> Обновить VOX
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <canvas id="voxChartCallsByDay" height="120"></canvas>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="thead-light">
                                <tr>
                                    <th>Дата</th>
                                    <th>Всего</th>
                                    <th>Создано</th>
                                    <th>Не дозвонились</th>
                                    <th>Дозвонились</th>
                                    <th>Дошли к менеджеру</th>
                                    <th>% создано</th>
                                    <th>% не дозв.</th>
                                    <th>% дозв.</th>
                                    <th>% дошли из дозв.</th>
                                </tr>
                                </thead>
                                <tbody id="voxDaysBody">
                                <tr><td colspan="10" class="text-center"><i class="fa fa-spinner fa-spin"></i> Загрузка...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {* --- Графики --- *}
                <div class="card chart-card">
                    <div class="card-header sticky">Динамика за период</div>
                    <div class="chart-wrap">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted">Заявки по дням</h6>
                                <canvas id="chartTotalsByDay" height="120"></canvas>
                                <div class="mini-muted">Сумма total_requests по всем менеджерам за день</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted">Звонки по дням (stacked)</h6>
                                <canvas id="chartCallsByDay" height="120"></canvas>
                                <div class="mini-muted">Принятые / Непринятые</div>
                            </div>
                        </div>
                    </div>
                </div>

                {* --- Метрики по менеджерам (сводная) --- *}
                <div class="card mb-4">
                    <div class="card-header sticky">
                        <h5 class="mb-0">Статистика по менеджерам (сводная)</h5>
                        <small class="text-muted">Включает заявки, конверсии, звонки и БОНОН</small>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="thead-light">
                                <tr>
                                    <th>Менеджер</th>
                                    <th>Всего отвалов</th>
                                    <th>Заявок</th>
                                    <th>Конв. заверш.</th>
                                    <th>Выдано</th>
                                    <th>Конв. выдано</th>
                                    <th>Одобрено</th>
                                    <th>Конв. одобрено</th>
                                    <th>Отказано</th>
                                    <th>Конв. отказано</th>
                                    <th>БОНОН</th>
                                    <th>Не БОНОН</th>
                                    <th>Конв. БОНОН</th>
                                    <th>Звонков</th>
                                    <th>Принятых</th>
                                    <th>Непринятых</th>
                                    <th>Общая длит. (сек)</th>
                                    <th>Сред. длит. принятого</th>
                                </tr>
                                </thead>
                                <tbody id="managersBody">
                                <tr><td colspan="18" class="text-center"><i class="fa fa-spinner fa-spin"></i> Загрузка...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {* --- По дням и менеджерам --- *}
                <div class="card mb-4">
                    <div class="card-header sticky">Статистика по менеджерам (по дням)</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="thead-light">
                                <tr>
                                    <th>Дата</th>
                                    <th>Менеджер</th>
                                    <th>Отвалов</th>
                                    <th>Заявок</th>
                                    <th>Выдано</th>
                                    <th>Одобрено</th>
                                    <th>Отказано</th>
                                    <th>Конв. заверш.</th>
                                    <th>Конв. выдач</th>
                                    <th>Конв. одобр.</th>
                                    <th>Конв. отказов</th>
                                    <th>Звонков</th>
                                    <th>Принятых</th>
                                    <th>Непринятых</th>
                                    <th>Сред. длит.</th>
                                </tr>
                                </thead>
                                <tbody id="byDaysBody">
                                <tr><td colspan="15" class="text-center"><i class="fa fa-spinner fa-spin"></i> Загрузка...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            <div id="tab_ivr" class="tab-pane fade"><div class="card"><div class="card-body"><em>IVR статистика — будет позже.</em></div></div></div>
            <div id="tab_icr" class="tab-pane fade"><div class="card"><div class="card-body"><em>ICR чаты — будет позже.</em></div></div></div>
        </div>

        <div id="report-config" data-report-uri="{$report_uri|escape:'html'}" style="display:none"></div>
    </div>

    {include file='footer.tpl'}
</div>

{capture name='page_scripts'}
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1"></script>

{literal}
    <script>
        $(function(){
            var reportUri = $('#report-config').data('report-uri');

            // --- KPI блок ---
            function loadKpi(){
                $.post(reportUri+'?action=loadKpi', {
                    date_from: $('#date_from').val(),
                    date_to: $('#date_to').val()
                }, function(resp){
                    if(resp.success){
                        var d = resp.data;
                        var html = `
                    <div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
                        <div class="p-2" style="min-width:220px;">
                            <h6 class="mb-1 text-muted">Отвалов за период</h6>
                            <div class="display-4">${d.totals ?? 0}</div>
                            <small class="text-muted">всего</small>
                        </div>
                        <div class="p-2 text-center" style="min-width:220px;">
                            <div class="small text-muted">Достигли заявки</div>
                            <div class="h4">${d.completed_total ?? 0}</div>
                            <small class="text-muted">
                                с менеджером: ${d.completed_with_manager ?? 0} /
                                самост.: ${d.completed_self ?? 0}
                            </small>
                        </div>
                        <div class="p-2 text-center" style="min-width:220px;">
                            <div class="small text-muted">Выдано / Одобрено / Отказано</div>
                            <div class="h4">${d.users_loan_issued ?? 0} /
                                ${d.users_loan_approved ?? 0} /
                                ${d.users_loan_rejected ?? 0}
                            </div>
                            <small class="text-muted">
                                <span class="badge-dot badge-acc"><i></i>выдачи: ${d.conversion_total ?? 0}%</span>
                                &nbsp;/&nbsp;
                                <span class="badge-dot"><i style="background:#28a745"></i>одобрено: ${d.conversion_approved ?? 0}%</span>
                                &nbsp;/&nbsp;
                                <span class="badge-dot"><i style="background:#ffc107"></i>отказов: ${d.conversion_rejected ?? 0}%</span>
                            </small>
                        </div>
                        <div class="p-2 text-center" style="min-width:220px;">
                            <div class="small text-muted">Продолжили заполнение</div>
                            <div class="h4">${d.continue_count ?? 0}</div>
                            <small class="text-muted">
                                выдач: ${d.issued_from_continue ?? 0} /
                                конверсия: ${d.conversion_continue ?? 0}%
                            </small>
                        </div>
                        <div class="p-2 text-center" style="min-width:220px;">
                            <div class="small text-muted">БОНОН</div>
                            <div class="h5">${d.bonon_count ?? 0} / ${d.not_bonon_count ?? 0}</div>
                        </div>
                        <div class="p-2 text-center" style="min-width:200px;">
                            <div class="small text-muted mt-2">Новые клиенты за период</div>
                            <div class="h5">${d.new_clients_today ?? 0}</div>
                        </div>
                    </div>
                `;
                        $('#kpiBlock').html(html);
                    }
                }, 'json');
            }

            // --- KPI блок ---
            function loadKpi(){
                $.post(reportUri+'?action=loadKpi', {
                    date_from: $('#date_from').val(),
                    date_to: $('#date_to').val()
                }, function(resp){
                    if(resp.success){
                        var d = resp.data;
                        var html = `
                    <div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
                        <div class="p-2" style="min-width:220px;">
                            <h6 class="mb-1 text-muted">Отвалов за период</h6>
                            <div class="display-4">${d.totals ?? 0}</div>
                            <small class="text-muted">всего</small>
                        </div>
                        <div class="p-2 text-center" style="min-width:220px;">
                            <div class="small text-muted">Достигли заявки</div>
                            <div class="h4">${d.completed_total ?? 0}</div>
                            <small class="text-muted">
                                с менеджером: ${d.completed_with_manager ?? 0} /
                                самост.: ${d.completed_self ?? 0}
                            </small>
                        </div>
                        <div class="p-2 text-center" style="min-width:220px;">
                            <div class="small text-muted">Выдано / Одобрено / Отказано</div>
                            <div class="h4">${d.users_loan_issued ?? 0} /
                                ${d.users_loan_approved ?? 0} /
                                ${d.users_loan_rejected ?? 0}
                            </div>
                            <small class="text-muted">
                                <span class="badge-dot badge-acc"><i></i>выдачи: ${d.conversion_total ?? 0}%</span>
                                &nbsp;/&nbsp;
                                <span class="badge-dot"><i style="background:#28a745"></i>одобрено: ${d.conversion_approved ?? 0}%</span>
                                &nbsp;/&nbsp;
                                <span class="badge-dot"><i style="background:#ffc107"></i>отказов: ${d.conversion_rejected ?? 0}%</span>
                            </small>
                        </div>
                        <div class="p-2 text-center" style="min-width:220px;">
                            <div class="small text-muted">Продолжили заполнение</div>
                            <div class="h4">${d.continue_count ?? 0}</div>
                            <small class="text-muted">
                                выдач: ${d.issued_from_continue ?? 0} /
                                конверсия: ${d.conversion_continue ?? 0}%
                            </small>
                        </div>
                        <div class="p-2 text-center" style="min-width:220px;">
                            <div class="small text-muted">БОНОН</div>
                            <div class="h5">${d.bonon_count ?? 0} / ${d.not_bonon_count ?? 0}</div>
                        </div>
                        <div class="p-2 text-center" style="min-width:200px;">
                            <div class="small text-muted mt-2">Новые клиенты за период</div>
                            <div class="h5">${d.new_clients_today ?? 0}</div>
                        </div>
                    </div>
                `;
                        $('#kpiBlock').html(html);

                        var totals = d.totals ?? 0;
                        var pct1 = totals ? (d.stage1 / totals * 100) : 0;
                        var pct2 = totals ? (d.stage2 / totals * 100) : 0;
                        var pct3 = totals ? (d.stage3 / totals * 100) : 0;
                        var pct4 = totals ? (d.stage4 / totals * 100) : 0;
                        var pct5 = totals ? (d.stage5 / totals * 100) : 0;
                        var pct6 = totals ? (d.stage6 / totals * 100) : 0;
                        var pct7 = totals ? (d.stage7 / totals * 100) : 0;

                        var stagesHtml = '';
                        stagesHtml += `
                            <div class="col-md-3 mb-2">
                                <div class="p-3" style="background:#4e73df; color:#fff; border-radius:6px;">
                                    <div style="font-weight:700">Стадия 1</div>
                                    <div style="font-size:20px; margin-top:6px;">${d.stage1 ?? 0}</div>
                                    <div class="small">${pct1.toFixed(1)}%</div>
                                    <div class="small text-light mt-2">
                                        Определение: Отвалы на этапе ввода паспортных данных.
                                    </div>
                                </div>
                            </div>
                        `;
                        stagesHtml += `
                        <div class="col-md-3 mb-2">
                            <div class="p-3" style="background:#1cc88a; color:#fff; border-radius:6px;">
                                <div style="font-weight:700">Стадия 2</div>
                                <div style="font-size:20px; margin-top:6px;">${d.stage2 ?? 0}</div>
                                <div class="small">${pct2.toFixed(1)}%</div>
                                <div class="small text-light mt-2">
                                    Определение: Отвалы на этапе ввода адресов.
                                </div>
                            </div>
                        </div>
                    `;
                        stagesHtml += `
                        <div class="col-md-3 mb-2">
                            <div class="p-3" style="background:#36b9cc; color:#fff; border-radius:6px;">
                                <div style="font-weight:700">Стадия 3</div>
                                <div style="font-size:20px; margin-top:6px;">${d.stage3 ?? 0}</div>
                                <div class="small">${pct3.toFixed(1)}%</div>
                                <div class="small text-light mt-2">
                                    Определение: Отвалы на странице с предварительным решением.
                                </div>
                            </div>
                        </div>
                    `;
                        stagesHtml += `
                        <div class="col-md-3 mb-2">
                            <div class="p-3" style="background:#f6c23e; color:#000; border-radius:6px;">
                                <div style="font-weight:700">Стадия 4</div>
                                <div style="font-size:20px; margin-top:6px;">${d.stage4 ?? 0}</div>
                                <div class="small">${pct4.toFixed(1)}%</div>
                                <div class="small text-dark mt-2">
                                    Определение: Отвалы на странице с привязкой карты.
                                </div>
                            </div>
                        </div>
                    `;
                        stagesHtml += `
                            <div class="col-md-3 mb-2">
                                <div class="p-3" style="background:#e74a3b; color:#fff; border-radius:6px;">
                                    <div style="font-weight:700">Стадия 5</div>
                                    <div style="font-size:20px; margin-top:6px;">${d.stage5 ?? 0}</div>
                                    <div class="small">${pct5.toFixed(1)}%</div>
                                    <div class="small text-light mt-2">
                                        Определение: Отвалы на странице с загрузкой фото.
                                    </div>
                                </div>
                            </div>
                        `;
                        stagesHtml += `
                            <div class="col-md-3 mb-2">
                                <div class="p-3" style="background:#858796; color:#fff; border-radius:6px;">
                                    <div style="font-weight:700">Стадия 6</div>
                                    <div style="font-size:20px; margin-top:6px;">${d.stage6 ?? 0}</div>
                                    <div class="small">${pct6.toFixed(1)}%</div>
                                    <div class="small text-light mt-2">
                                        Определение: Отвалы на этапе ввода данных о работе.
                                    </div>
                                </div>
                            </div>
                        `;
                        stagesHtml += `
                            <div class="col-md-3 mb-2">
                                <div class="p-3" style="background:#6f42c1; color:#fff; border-radius:6px;">
                                    <div style="font-weight:700">Стадия 7</div>
                                    <div style="font-size:20px; margin-top:6px;">${d.stage7 ?? 0}</div>
                                    <div class="small">${pct7.toFixed(1)}%</div>
                                    <div class="small text-light mt-2">
                                        Определение: Отвалы на этапе ввода дополнительных данных.
                                    </div>
                                </div>
                            </div>
                        `;
                        $('#stagesRow').html(stagesHtml);
                    }
                }, 'json');
            }


            // --- Таблица менеджеров (сводная) ---
            function loadManagers(){
                $.post(reportUri+'?action=loadManagers', {
                    date_from: $('#date_from').val(),
                    date_to: $('#date_to').val()
                }, function(resp){
                    if(resp.success){
                        var html = '';
                        resp.rows.forEach(function(m){
                            html += `
                        <tr>
                            <td><a href="javascript:void(0)" class="goto-manager" data-manager="${m.manager_id}">${m.manager_name ?? '—'}</a></td>
                            <td>${m.total_requests ?? 0}</td>
                            <td>${m.completed_total ?? 0}</td>
                            <td><span class="badge badge-info">${m.conversion_completed ?? 0}%</span></td>
                            <td>${m.issued_count ?? 0}</td>
                            <td><span class="badge badge-success">${m.conversion_issued ?? 0}%</span></td>
                            <td>${m.approved_count ?? 0}</td>
                            <td><span class="badge badge-success">${m.conversion_approved ?? 0}%</span></td>
                            <td>${m.rejected_count ?? 0}</td>
                            <td><span class="badge badge-danger">${m.conversion_rejected ?? 0}%</span></td>
                            <td>${m.bonon_count ?? 0}</td>
                            <td>${m.not_bonon_count ?? 0}</td>
                            <td><span class="badge badge-primary">${m.conversion_bonon ?? 0}%</span></td>
                            <td>${m.total_calls ?? 0}</td>
                            <td>${m.accepted_calls ?? 0}</td>
                            <td>${m.not_accepted_calls ?? 0}</td>
                            <td>${m.total_duration_accepted_calls ?? 0}</td>
                            <td>${m.avg_accepted_duration ?? 0}</td>
                        </tr>`;
                        });
                        $('#managersBody').html(html);
                    } else {
                        $('#managersBody').html('<tr><td colspan="18" class="text-center text-danger">Нет данных</td></tr>');
                    }
                }, 'json');
            }

            // --- Таблица по дням ---
            function loadByDays(){
                $.post(reportUri+'?action=loadByDays', {
                    date_from: $('#date_from').val(),
                    date_to: $('#date_to').val()
                }, function(resp){
                    if(resp.success){
                        var html = '';
                        resp.rows.forEach(function(r){
                            html += `
                        <tr>
                            <td>${r.day_created ?? ''}</td>
                            <td><a href="javascript:void(0)" class="goto-manager" data-manager="${r.manager_id}">${r.manager_name ?? '—'}</a></td>
                            <td>${r.total_requests ?? 0}</td>
                            <td>${r.completed_total ?? 0}</td>
                            <td>${r.issued_count ?? 0}</td>
                            <td>${r.approved_count ?? 0}</td>
                            <td>${r.rejected_count ?? 0}</td>
                            <td>${r.conversion_completed ?? 0}%</td>
                            <td>${r.conversion_issued ?? 0}%</td>
                            <td>${r.conversion_approved ?? 0}%</td>
                            <td>${r.conversion_rejected ?? 0}%</td>
                            <td>${r.total_calls ?? 0}</td>
                            <td>${r.accepted_calls ?? 0}</td>
                            <td>${r.not_accepted_calls ?? 0}</td>
                            <td>${r.avg_accepted_duration ?? 0}</td>
                        </tr>`;
                        });
                        $('#byDaysBody').html(html);
                    } else {
                        $('#byDaysBody').html('<tr><td colspan="15" class="text-center text-danger">Нет данных</td></tr>');
                    }
                }, 'json');
            }

            // --- VOX: shared state ---
            var voxChartRef = null;

            // --- VOX KPI ---
            function loadVoxSummary(){
                var reportUri = $('#report-config').data('report-uri');
                $.post(reportUri+'?action=loadVoxCallsSummary', {
                    date_from: $('#date_from').val(),
                    date_to: $('#date_to').val()
                }, function(resp){
                    if(resp && resp.success){
                        var d = resp.data || {};
                        var html = `
                        <div class="d-flex flex-wrap justify-content-between align-items-start">
                            <div class="p-2" style="min-width:200px;">
                                <div class="small text-muted">Всего звонков</div>
                                <div class="display-4">${d.total_calls ?? 0}</div>
                            </div>
                            <div class="p-2 text-center" style="min-width:200px;">
                                <div class="small text-muted">Создано</div>
                                <div class="h4 mb-1">${d.created_calls ?? 0}</div>
                                <small class="text-muted">${d.created_percent ?? 0}%</small>
                            </div>
                            <div class="p-2 text-center" style="min-width:200px;">
                                <div class="small text-muted">Не дозвонились</div>
                                <div class="h4 mb-1">${d.not_reached_calls ?? 0}</div>
                                <small class="text-muted">${d.not_reached_percent ?? 0}%</small>
                            </div>
                            <div class="p-2 text-center" style="min-width:200px;">
                                <div class="small text-muted">Дозвонились (успешно)</div>
                                <div class="h4 mb-1">${d.reached_calls ?? 0}</div>
                                <small class="text-muted">${d.success_percent ?? 0}%</small>
                            </div>
                            <div class="p-2 text-center" style="min-width:240px;">
                                <div class="small text-muted">Дошли до менеджера</div>
                                <div class="h4 mb-1">${d.redirected_to_manager ?? 0}</div>
                                <small class="text-muted">
                                    от всех: ${d.redirected_percent_all ?? 0}% /
                                    из дозв.: ${d.redirected_percent_of_reached ?? 0}%
                                </small>
                            </div>
                        </div>
                        `;
                        $('#voxKpiBlock').html(html);
                    } else {
                        $('#voxKpiBlock').html('<div class="text-danger">Не удалось загрузить VOX KPI</div>');
                    }
                }, 'json');
            }

            // --- VOX по дням + график ---
            function loadVoxByDays(){
                var reportUri = $('#report-config').data('report-uri');
                $.post(reportUri+'?action=loadVoxCallsByDays', {
                    date_from: $('#date_from').val(),
                    date_to: $('#date_to').val()
                }, function(resp){
                    if(!(resp && resp.success && Array.isArray(resp.rows))){
                        $('#voxDaysBody').html('<tr><td colspan="10" class="text-center text-danger">Нет данных VOX</td></tr>');
                        if (voxChartRef) { voxChartRef.destroy(); voxChartRef = null; }
                        return;
                    }

                    // Таблица
                    var html = '';
                    var labels = [];
                    var created = [], notReached = [], reached = [], redirected = [];
                    resp.rows.forEach(function(r){
                        labels.push(r.day_called);
                        created.push(Number(r.created_calls || 0));
                        notReached.push(Number(r.not_reached_calls || 0));
                        reached.push(Number(r.reached_calls || 0));
                        redirected.push(Number(r.redirected_to_manager || 0));

                        html += `
                            <tr>
                                <td>${r.day_called ?? ''}</td>
                                <td>${r.total_calls ?? 0}</td>
                                <td>${r.created_calls ?? 0}</td>
                                <td>${r.not_reached_calls ?? 0}</td>
                                <td>${r.reached_calls ?? 0}</td>
                                <td>${r.redirected_to_manager ?? 0}</td>
                                <td>${r.created_percent ?? 0}%</td>
                                <td>${r.not_reached_percent ?? 0}%</td>
                                <td>${r.success_percent ?? 0}%</td>
                                <td>${r.redirected_percent_of_reached ?? 0}%</td>
                            </tr>
                        `;
                    });
                    $('#voxDaysBody').html(html);

                    // График (stacked)
                    var ctx = document.getElementById('voxChartCallsByDay').getContext('2d');
                    if (voxChartRef) { voxChartRef.destroy(); voxChartRef = null; }
                    voxChartRef = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [
                                { label: 'Создано', data: created,   borderWidth: 1, backgroundColor: 'rgba(108, 117, 125, 0.7)' }, // secondary
                                { label: 'Не дозвонились', data: notReached, borderWidth: 1, backgroundColor: 'rgba(220, 53, 69, 0.7)' }, // danger
                                { label: 'Дозвонились', data: reached,  borderWidth: 1, backgroundColor: 'rgba(40, 167, 69, 0.7)' }, // success
                                { label: 'Дошли к менеджеру', data: redirected, borderWidth: 1, backgroundColor: 'rgba(23, 162, 184, 0.7)' } // info
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'top' },
                                tooltip: { mode: 'index', intersect: false }
                            },
                            scales: {
                                x: { stacked: true },
                                y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } }
                            }
                        }
                    });
                }, 'json');
            }

            // --- Кнопка "Обновить VOX"
            $(document).on('click', '#refreshVoxBtn', function(){
                loadVoxSummary();
                loadVoxByDays();
            });

            // загрузка при открытии
            loadKpi();
            loadManagers();
            loadByDays();

            // VOX
            loadVoxSummary();
            loadVoxByDays();
        });
    </script>
{/literal}
{/capture}

{$smarty.capture.page_styles}
{$smarty.capture.page_scripts}
