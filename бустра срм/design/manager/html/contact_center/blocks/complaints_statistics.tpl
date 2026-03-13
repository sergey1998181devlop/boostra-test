{*
    Универсальный шаблон статистики жалоб

    Параметры (передавать при include):
    - type: Тип статистики ('manager' или 'responsible')
*}

{* Конфигурация типов *}
{if $type == 'manager'}
    {$column_title = 'Менеджер'}
{else}
    {$column_title = 'Ответственный'}
{/if}

{* Автоматически вычисляемые значения на основе type *}
{$api_action = "complaints_by_"|cat:$type}
{$export_action = "download_complaints_by_"|cat:$type|cat:"_excel"}
{$tab_id = "complaints-by-"|cat:$type|cat:"-tab"}
{$tab_trigger_id = "complaints-"|cat:$type|cat:"-tab"}

<div class="tab-pane fade" id="{$tab_id}" role="tabpanel">
    <!-- Фильтры -->
    <div class="complaints-filters">
        <div class="row">
            <div class="col-md-3">
                <label for="complaints-{$type}-date-from">Дата с:</label>
                <input type="date" class="form-control" id="complaints-{$type}-date-from" value="{$date_from|default:''}">
            </div>
            <div class="col-md-3">
                <label for="complaints-{$type}-date-to">Дата по:</label>
                <input type="date" class="form-control" id="complaints-{$type}-date-to" value="{$date_to|default:''}">
            </div>
            <div class="col-md-3">
                <label>&nbsp;</label>
                <button class="btn btn-primary btn-block" onclick="ComplaintsStats.load('{$type}')">
                    <i class="fas fa-sync-alt"></i> Обновить
                </button>
            </div>
            <div class="col-md-3">
                <label>&nbsp;</label>
                <div class="btn-group btn-block">
                    <button class="btn btn-success" onclick="ComplaintsStats.exportExcel('{$type}', 'collection')">
                        <i class="fas fa-file-excel"></i> Экспорт (Взыскание)
                    </button>
                    <button class="btn btn-success" onclick="ComplaintsStats.exportExcel('{$type}', 'additional_services')">
                        <i class="fas fa-file-excel"></i> Экспорт (Допы)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица: Взыскание -->
    <h5 class="text-white mb-2">Взыскание</h5>
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-hover" id="complaints-{$type}-table-collection">
            <thead>
            <tr>
                <th class="align-middle">{$column_title}</th>
                <!-- Заголовки тем будут добавлены динамически -->
                <th class="align-middle text-center">Всего</th>
            </tr>
            </thead>
            <tbody id="complaints-{$type}-body-collection">
            <tr>
                <td colspan="2" class="text-center text-muted">
                    <i class="fas fa-info-circle"></i> Выберите период и нажмите "Обновить"
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <!-- Таблица: Допы и прочее -->
    <h5 class="text-white mb-2">Допы и прочее</h5>
    <div class="table-responsive">
        <table class="table table-bordered table-hover" id="complaints-{$type}-table-additional">
            <thead>
            <tr>
                <th class="align-middle">{$column_title}</th>
                <!-- Заголовки тем будут добавлены динамически -->
                <th class="align-middle text-center">Всего</th>
            </tr>
            </thead>
            <tbody id="complaints-{$type}-body-additional">
            <tr>
                <td colspan="2" class="text-center text-muted">
                    <i class="fas fa-info-circle"></i> Выберите период и нажмите "Обновить"
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
{literal}
// Регистрируем конфигурацию для этого экземпляра
(function() {
    if (typeof window.ComplaintsStats === 'undefined') {
        window.ComplaintsStats = {
            configs: {},

            registerConfig: function(prefix, config) {
                this.configs[prefix] = config;
            },

            getConfig: function(prefix) {
                return this.configs[prefix];
            },

            getTableIds: function(prefix, type) {
                const tableType = type === 'additional_services' ? 'additional' : 'collection';
                return {
                    tableId: '#complaints-' + prefix + '-table-' + tableType,
                    bodyId: 'complaints-' + prefix + '-body-' + tableType
                };
            },

            load: function(prefix) {
                const config = this.getConfig(prefix);
                const dateFrom = document.getElementById('complaints-' + prefix + '-date-from').value;
                const dateTo = document.getElementById('complaints-' + prefix + '-date-to').value;

                // Показываем загрузку для обеих таблиц
                this.showLoading(prefix, 'collection');
                this.showLoading(prefix, 'additional_services');

                const self = this;

                // Загружаем данные для обоих типов
                ['collection', 'additional_services'].forEach(function(type) {
                    fetch('tickets/statistics?action=' + config.apiAction + '&type=' + type + '&date_from=' + dateFrom + '&date_to=' + dateTo)
                        .then(function(response) { return response.json(); })
                        .then(function(data) {
                            if (data.success) {
                                self.renderStats(prefix, data.data, type);
                            } else {
                                self.showError(prefix, 'Ошибка загрузки данных', type);
                            }
                        })
                        .catch(function(error) {
                            console.error('Error:', error);
                            self.showError(prefix, 'Ошибка загрузки данных', type);
                        });
                });
            },

            renderStats: function(prefix, data, type) {
                this.renderHeaders(prefix, data.subjects, type);
                this.renderData(prefix, data, type);
            },

            renderHeaders: function(prefix, subjects, type) {
                const ids = this.getTableIds(prefix, type);
                const headerRow = document.querySelector(ids.tableId + ' thead tr');

                // Удаляем все промежуточные ячейки (кроме первой и последней)
                const cellsToRemove = headerRow.querySelectorAll('th:not(:first-child):not(:last-child)');
                cellsToRemove.forEach(function(cell) { cell.remove(); });

                // Добавляем заголовки тем перед последней ячейкой
                subjects.forEach(function(subject) {
                    const th = document.createElement('th');
                    th.className = 'align-middle text-center';
                    th.textContent = subject;
                    headerRow.insertBefore(th, headerRow.querySelector('th:last-child'));
                });
            },

            renderData: function(prefix, data, type) {
                const ids = this.getTableIds(prefix, type);
                const tbody = document.getElementById(ids.bodyId);
                let html = '';

                if (!data.data || Object.keys(data.data).length === 0) {
                    const totalColumns = data.subjects.length + 2;
                    tbody.innerHTML = '<tr><td colspan="' + totalColumns + '" class="text-center text-muted"><i class="fas fa-info-circle"></i> Нет данных для отображения</td></tr>';
                    return;
                }

                // Сортируем по алфавиту
                const sortedKeys = Object.keys(data.data).sort();

                sortedKeys.forEach(function(name) {
                    const rowData = data.data[name];
                    html += '<tr><td class="align-middle">' + name + '</td>';

                    // Добавляем данные по темам
                    data.subjects.forEach(function(subject) {
                        const count = rowData[subject] || 0;
                        html += '<td class="align-middle text-center">' + count + '</td>';
                    });

                    // Добавляем общий итог
                    html += '<td class="align-middle text-center font-weight-bold">' + (rowData.total || 0) + '</td></tr>';
                });

                // Добавляем итоговую строку
                html += this.createTotalRow(data);

                tbody.innerHTML = html;
            },

            createTotalRow: function(data) {
                const totals = {};
                let grandTotal = 0;

                // Считаем итоги по темам
                data.subjects.forEach(function(subject) {
                    totals[subject] = 0;
                    Object.values(data.data).forEach(function(rowData) {
                        totals[subject] += rowData[subject] || 0;
                    });
                    grandTotal += totals[subject];
                });

                let html = '<tr class="total-row"><td class="align-middle font-weight-bold">ИТОГО</td>';

                // Добавляем итоги по темам
                data.subjects.forEach(function(subject) {
                    html += '<td class="align-middle text-center font-weight-bold">' + totals[subject] + '</td>';
                });

                // Добавляем общий итог
                html += '<td class="align-middle text-center font-weight-bold">' + grandTotal + '</td></tr>';

                return html;
            },

            showLoading: function(prefix, type) {
                const ids = this.getTableIds(prefix, type);
                const headerRow = document.querySelector(ids.tableId + ' thead tr');
                const columnCount = headerRow.querySelectorAll('th').length;

                document.getElementById(ids.bodyId).innerHTML = '<tr><td colspan="' + columnCount + '" class="text-center"><div class="spinner-border text-primary" role="status"></div><div class="mt-2">Загрузка статистики</div></td></tr>';
            },

            showError: function(prefix, message, type) {
                const ids = this.getTableIds(prefix, type);
                const headerRow = document.querySelector(ids.tableId + ' thead tr');
                const columnCount = headerRow.querySelectorAll('th').length;

                document.getElementById(ids.bodyId).innerHTML = '<tr><td colspan="' + columnCount + '" class="text-center text-danger"><i class="fas fa-exclamation-triangle"></i> ' + message + '</td></tr>';
            },

            exportExcel: function(prefix, type) {
                const config = this.getConfig(prefix);
                const dateFrom = document.getElementById('complaints-' + prefix + '-date-from').value;
                const dateTo = document.getElementById('complaints-' + prefix + '-date-to').value;
                const url = 'tickets/statistics?action=' + config.exportAction + '&type=' + type + '&date_from=' + dateFrom + '&date_to=' + dateTo;
                window.open(url, '_blank');
            },

            initTab: function(prefix, tabTriggerId) {
                const self = this;
                document.addEventListener('DOMContentLoaded', function() {
                    // Загружаем при переключении на вкладку
                    const tabTrigger = document.getElementById(tabTriggerId);
                    if (tabTrigger) {
                        tabTrigger.addEventListener('click', function() {
                            setTimeout(function() { self.load(prefix); }, 100);
                        });

                        // Загружаем если вкладка уже активна
                        if (tabTrigger.classList.contains('active')) {
                            self.load(prefix);
                        }
                    }
                });
            }
        };
    }
})();
{/literal}

// Регистрируем конфигурацию для текущего экземпляра
ComplaintsStats.registerConfig('{$type}', {
    apiAction: '{$api_action}',
    exportAction: '{$export_action}'
});

// Инициализируем вкладку
ComplaintsStats.initTab('{$type}', '{$tab_trigger_id}');
</script>
