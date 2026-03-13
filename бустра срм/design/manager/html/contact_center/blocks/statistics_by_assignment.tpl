<div class="tab-pane fade" id="statistics-by-assignment-tab" role="tabpanel">
    <!-- Фильтры -->
    <div class="assignment-filters">
        <div class="row">
            <div class="col-md-3">
                <label for="assignment-date-from">Дата с:</label>
                <input type="date" class="form-control" id="assignment-date-from" value="{$date_from}">
            </div>
            <div class="col-md-3">
                <label for="assignment-date-to">Дата по:</label>
                <input type="date" class="form-control" id="assignment-date-to" value="{$date_to}">
            </div>
            <div class="col-md-3">
                <label>&nbsp;</label>
                <button class="btn btn-primary btn-block" onclick="loadAssignmentStats()">
                    <i class="fas fa-sync-alt"></i> Обновить
                </button>
            </div>
        </div>
    </div>

    <!-- Основная таблица статистики -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover" id="assignment-stats-table">
            <thead>
            <tr>
                <th rowspan="3" class="align-middle">Месяц</th>
                <th rowspan="3" class="align-middle">Тип тикета</th>
                <th rowspan="3" class="align-middle">Всего назначено</th>

                <!-- Заголовки для уровней сложности -->
                <th colspan="9" class="text-center">Распределение по уровням сложности</th>
            </tr>
            <tr>
                <th colspan="3" class="text-center" style="background-color: #28a745">Soft (1-7 дн.)</th>
                <th colspan="3" class="text-center" style="background-color: #ffc107">Middle (8-30 дн.)</th>
                <th colspan="3" class="text-center" style="background-color: #dc3545">Hard (>30 дн.)</th>
            </tr>
            <tr>
                <!-- Soft -->
                <th class="text-center">Кол-во</th>
                <th class="text-center">Ср. коэф.</th>
                <th class="text-center">Нагрузка</th>
                <!-- Middle -->
                <th class="text-center">Кол-во</th>
                <th class="text-center">Ср. коэф.</th>
                <th class="text-center">Нагрузка</th>
                <!-- Hard -->
                <th class="text-center">Кол-во</th>
                <th class="text-center">Ср. коэф.</th>
                <th class="text-center">Нагрузка</th>
            </tr>
            </thead>
            <tbody id="assignment-stats-body">
            <tr>
                <td colspan="12" class="loading-container">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Загрузка статистики...</span>
                    </div>
                    <div class="mt-2">Загрузка статистики автоназначения...</div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <!-- Таблица статистики по менеджерам -->
    <div class="mt-4">
        <h5 class="text-white mb-3">Статистика по менеджерам</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="manager-stats-table">
                <thead>
                <tr>
                    <th rowspan="2" class="align-middle">Менеджер</th>
                    <th colspan="6" class="text-center">Дополнительные услуги</th>
                    <th colspan="6" class="text-center">Взыскание</th>
                    <th rowspan="2" class="align-middle text-center">Общая нагрузка</th>
                </tr>
                <tr>
                    <!-- Допы -->
                    <th class="text-center">Soft</th>
                    <th class="text-center">Middle</th>
                    <th class="text-center">Hard</th>
                    <th class="text-center">Всего</th>
                    <th class="text-center">Ср. коэф.</th>
                    <th class="text-center">Нагрузка</th>
                    <!-- Взыскание -->
                    <th class="text-center">Soft</th>
                    <th class="text-center">Middle</th>
                    <th class="text-center">Hard</th>
                    <th class="text-center">Всего</th>
                    <th class="text-center">Ср. коэф.</th>
                    <th class="text-center">Нагрузка</th>
                </tr>
                </thead>
                <tbody id="manager-stats-body">
                <tr>
                    <td colspan="13" class="loading-container">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Загрузка статистики менеджеров...</span>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    {literal}
    function loadAssignmentStats() {
        const dateFrom = document.getElementById('assignment-date-from').value;
        const dateTo = document.getElementById('assignment-date-to').value;

        // Показываем загрузку
        showLoading();

        fetch(`tickets/statistics?action=assignment_stats&date_from=${dateFrom}&date_to=${dateTo}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderAssignmentStats(data.data);
                } else {
                    showError('Ошибка загрузки данных');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Ошибка загрузки данных');
            });
    }

    function renderAssignmentStats(data) {
        renderDistributionStats(data.distribution);
        renderManagerStats(data.managers);
    }

    function renderDistributionStats(stats) {
        const tbody = document.getElementById('assignment-stats-body');
        let html = '';

        // Группируем данные по месяцам
        const monthlyData = groupDataByMonth(stats);

        // Проверяем, что monthlyData существует и не пустой
        if (!monthlyData || Object.keys(monthlyData).length === 0) {
            tbody.innerHTML = `
            <tr>
                <td colspan="12" class="text-center text-muted">
                    <i class="fas fa-info-circle"></i> Нет данных для отображения
                </td>
            </tr>
        `;
            return;
        }

        // Итерируемся по объекту как по массиву
        Object.keys(monthlyData).forEach(month => {
            const monthData = monthlyData[month];

            // Строка для дополнительных услуг
            const dopData = monthData.additional_services || {};
            html += createDistributionRow(month, 'Дополнительные услуги', dopData);

            // Строка для взыскания
            const collData = monthData.collection || {};
            html += createDistributionRow(month, 'Взыскание', collData);

            // Итоговая строка для месяца
            html += createTotalRow(month, monthData);
        });

        tbody.innerHTML = html;
    }

    function createDistributionRow(month, type, data) {
        const total = (data.soft?.count || 0) + (data.middle?.count || 0) + (data.hard?.count || 0);

        return `
        <tr>
            <td class="align-middle">${month}</td>
            <td class="align-middle">${type}</td>
            <td class="align-middle text-center count-main font-weight-bold">${total}</td>
            
            <!-- Soft -->
            <td class="align-middle text-center level-soft">${data.soft?.count || 0}</td>
            <td class="align-middle text-center coefficient-cell">${(data.soft?.avg_coefficient || 0).toFixed(2)}</td>
            <td class="align-middle text-center load-cell">${(data.soft?.total_load || 0).toFixed(2)}</td>
            
            <!-- Middle -->
            <td class="align-middle text-center level-middle">${data.middle?.count || 0}</td>
            <td class="align-middle text-center coefficient-cell">${(data.middle?.avg_coefficient || 0).toFixed(2)}</td>
            <td class="align-middle text-center load-cell">${(data.middle?.total_load || 0).toFixed(2)}</td>
            
            <!-- Hard -->
            <td class="align-middle text-center level-hard">${data.hard?.count || 0}</td>
            <td class="align-middle text-center coefficient-cell">${(data.hard?.avg_coefficient || 0).toFixed(2)}</td>
            <td class="align-middle text-center load-cell">${(data.hard?.total_load || 0).toFixed(2)}</td>
        </tr>
    `;
    }

    function createTotalRow(month, monthData) {
        const dopTotal = (monthData.additional_services?.soft?.count || 0) +
            (monthData.additional_services?.middle?.count || 0) +
            (monthData.additional_services?.hard?.count || 0);
        const collTotal = (monthData.collection?.soft?.count || 0) +
            (monthData.collection?.middle?.count || 0) +
            (monthData.collection?.hard?.count || 0);
        const grandTotal = dopTotal + collTotal;

        return `
        <tr class="total-row">
            <td class="align-middle font-weight-bold">${month}</td>
            <td class="align-middle font-weight-bold">ИТОГО</td>
            <td class="align-middle text-center count-main font-weight-bold">${grandTotal}</td>
            
            <!-- Soft Total -->
            <td class="align-middle text-center level-soft font-weight-bold">
                ${(monthData.additional_services?.soft?.count || 0) + (monthData.collection?.soft?.count || 0)}
            </td>
            <td class="align-middle text-center coefficient-cell font-weight-bold">
                ${calculateAverageCoefficient(monthData, 'soft')}
            </td>
            <td class="align-middle text-center load-cell font-weight-bold">
                ${((monthData.additional_services?.soft?.total_load || 0) + (monthData.collection?.soft?.total_load || 0)).toFixed(2)}
            </td>
            
            <!-- Middle Total -->
            <td class="align-middle text-center level-middle font-weight-bold">
                ${(monthData.additional_services?.middle?.count || 0) + (monthData.collection?.middle?.count || 0)}
            </td>
            <td class="align-middle text-center coefficient-cell font-weight-bold">
                ${calculateAverageCoefficient(monthData, 'middle')}
            </td>
            <td class="align-middle text-center load-cell font-weight-bold">
                ${((monthData.additional_services?.middle?.total_load || 0) + (monthData.collection?.middle?.total_load || 0)).toFixed(2)}
            </td>
            
            <!-- Hard Total -->
            <td class="align-middle text-center level-hard font-weight-bold">
                ${(monthData.additional_services?.hard?.count || 0) + (monthData.collection?.hard?.count || 0)}
            </td>
            <td class="align-middle text-center coefficient-cell font-weight-bold">
                ${calculateAverageCoefficient(monthData, 'hard')}
            </td>
            <td class="align-middle text-center load-cell font-weight-bold">
                ${((monthData.additional_services?.hard?.total_load || 0) + (monthData.collection?.hard?.total_load || 0)).toFixed(2)}
            </td>
        </tr>
    `;
    }

    function renderManagerStats(managers) {
        const tbody = document.getElementById('manager-stats-body');
        let html = '';

        managers.forEach(manager => {
            const dopLoad = manager.additional_services?.total_load || 0;
            const collLoad = manager.collection?.total_load || 0;
            const totalLoad = dopLoad + collLoad;

            html += `
            <tr>
                <td class="align-middle">${manager.name}</td>
                
                <!-- Допы -->
                <td class="align-middle text-center level-soft">${manager.additional_services?.soft || 0}</td>
                <td class="align-middle text-center level-middle">${manager.additional_services?.middle || 0}</td>
                <td class="align-middle text-center level-hard">${manager.additional_services?.hard || 0}</td>
                <td class="align-middle text-center count-main">${(manager.additional_services?.soft || 0) + (manager.additional_services?.middle || 0) + (manager.additional_services?.hard || 0)}</td>
                <td class="align-middle text-center coefficient-cell">${(manager.additional_services?.avg_coefficient || 0).toFixed(2)}</td>
                <td class="align-middle text-center load-cell">${dopLoad.toFixed(2)}</td>
                
                <!-- Взыскание -->
                <td class="align-middle text-center level-soft">${manager.collection?.soft || 0}</td>
                <td class="align-middle text-center level-middle">${manager.collection?.middle || 0}</td>
                <td class="align-middle text-center level-hard">${manager.collection?.hard || 0}</td>
                <td class="align-middle text-center count-main">${(manager.collection?.soft || 0) + (manager.collection?.middle || 0) + (manager.collection?.hard || 0)}</td>
                <td class="align-middle text-center coefficient-cell">${(manager.collection?.avg_coefficient || 0).toFixed(2)}</td>
                <td class="align-middle text-center load-cell">${collLoad.toFixed(2)}</td>
                
                <!-- Общая нагрузка -->
                <td class="align-middle text-center load-cell font-weight-bold">${totalLoad.toFixed(2)}</td>
            </tr>
        `;
        });

        tbody.innerHTML = html;
    }

    function groupDataByMonth(stats) {
        const monthlyData = {};

        // Обрабатываем данные распределения
        Object.keys(stats).forEach(type => {
            Object.keys(stats[type]).forEach(level => {
                const data = stats[type][level];
                if (data.monthly) {
                    Object.keys(data.monthly).forEach(month => {
                        if (!monthlyData[month]) {
                            monthlyData[month] = {additional_services: {}, collection: {}};
                        }
                        if (!monthlyData[month][type]) {
                            monthlyData[month][type] = {};
                        }
                        monthlyData[month][type][level] = data.monthly[month];
                    });
                }
            });
        });

        // Сортируем месяцы
        return Object.keys(monthlyData)
            .sort()
            .reduce((sorted, month) => {
                sorted[month] = monthlyData[month];
                return sorted;
            }, {});
    }

    function calculateAverageCoefficient(monthData, level) {
        const dopCoeff = monthData.additional_services?.[level]?.avg_coefficient || 0;
        const collCoeff = monthData.collection?.[level]?.avg_coefficient || 0;
        const dopCount = monthData.additional_services?.[level]?.count || 0;
        const collCount = monthData.collection?.[level]?.count || 0;

        const totalCount = dopCount + collCount;
        if (totalCount === 0) return '0.00';

        const weightedAvg = ((dopCoeff * dopCount) + (collCoeff * collCount)) / totalCount;
        return weightedAvg.toFixed(2);
    }

    function showLoading() {
        document.getElementById('assignment-stats-body').innerHTML = `
        <tr>
            <td colspan="12" class="loading-container">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Загрузка статистики...</span>
                </div>
                <div class="mt-2">Загрузка статистики автоназначения...</div>
            </td>
        </tr>
    `;

        document.getElementById('manager-stats-body').innerHTML = `
        <tr>
            <td colspan="13" class="loading-container">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Загрузка статистики менеджеров...</span>
                </div>
            </td>
        </tr>
    `;
    }

    function showError(message) {
        document.getElementById('assignment-stats-body').innerHTML = `
        <tr>
            <td colspan="12" class="text-center text-danger">
                <i class="fas fa-exclamation-triangle"></i> ${message}
            </td>
        </tr>
    `;
    }

    // Загружаем статистику при открытии вкладки
    document.addEventListener('DOMContentLoaded', function () {
        // Загружаем при переключении на вкладку
        document.getElementById('assignment-tab').addEventListener('click', function () {
            setTimeout(loadAssignmentStats, 100);
        });

        // Загружаем если вкладка уже активна
        if (document.getElementById('assignment-tab').classList.contains('active')) {
            loadAssignmentStats();
        }
    });
    {/literal}
</script>
