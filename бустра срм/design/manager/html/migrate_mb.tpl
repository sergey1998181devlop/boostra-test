{$meta_title='Выгрузка в MindBox' scope=parent}
{capture name='page_scripts'}
    <script>
        $(document).ready(function() {
            $("#start_date").datepicker({
                dateFormat: "yy-mm-dd",
                maxDate: 0,
                changeMonth: true,
                changeYear: true
            });
            $("#start_date_orders").datepicker({
                dateFormat: "yy-mm-dd",
                maxDate: 0,
                changeMonth: true,
                changeYear: true
            });
        });

        let isImporting = false;
        // Скачивание CSV пользователей
        function downloadUsersCsv() {
            const startDate = document.getElementById('start_date').value;
            if (!startDate) {
                alert('Выберите дату начала выгрузки');
                return;
            }
            window.location.href = '?module=MigrateToMBView&action=downloadUsersCsv&start_date=' + startDate;
        }

        async function startImport() {
            if (isImporting) {
                alert('Импорт уже выполняется!');
                return;
            }

            const startDate = document.getElementById('start_date').value;
            if (!startDate) {
                alert('Выберите дату начала выгрузки');
                return;
            }

            if (!confirm('Начать массовый импорт клиентов в Mindbox?')) {
                return;
            }

            isImporting = true;
            const resultsContainer = document.getElementById('resultsContainer');

            // Показываем контейнер результатов
            resultsContainer.classList.add('active');
            resultsContainer.innerHTML = '<div class="alert alert-info">🔄 Идёт импорт данных...</div>';

            // Отключаем кнопки
            document.querySelectorAll('.btn').forEach(btn => btn.disabled = true);

            try {
                const response = await fetch('?module=MigrateToMBView&action=downloadUsers&start_date=' + startDate);
                const rawResponse = await response.text();

                let result = JSON.parse(rawResponse);

                if (result.result.status === 'error') {
                    throw new Error(result.result.message || result.result.error || 'Неизвестная ошибка импорта');
                }
                displayResults(result);
            } catch (error) {
                resultsContainer.innerHTML =
                    '<div class="alert alert-error">' +
                    '<strong>Ошибка импорта:</strong><br>' +
                    error.message +
                    '</div>';
            } finally {
                isImporting = false;
                document.querySelectorAll('.btn').forEach(btn => btn.disabled = false);
            }
        }

        function getStatusBadge(status) {
            const isSuccess = status === 'success';
            return {
                class: isSuccess ? 'status-success' : 'status-error',
                text: isSuccess ? '✅ Успешно' : '❌ Ошибка'
            };
        }

        function displayResults(result) {
            const resultsContainer = document.getElementById('resultsContainer');
            resultsContainer.classList.add('active');

            let html = '';

            if (result.result.status === 'success') {
                html +=
                    '<div class="alert alert-success">' +
                    '<strong>✅ Импорт успешно завершен!</strong><br>' +
                    'Всего клиентов: ' + (result.total_clients || '0') + '<br>' +
                    'Дата начала: ' + (result.start_date || '-') +
                    '</div>';
            } else {
                html +=
                    '<div class="alert alert-warning">' +
                    '<strong>⚠️ Импорт завершен с ошибкой</strong><br>' +
                    'Всего клиентов: ' + (result.total_clients || '0') + '<br>' +
                    'Дата начала: ' + (result.start_date || '-') +
                    '</div>';
            }

            html +=
                '<table class="results-table">' +
                '<thead>' +
                '<tr>' +
                '<th>Клиентов</th>' +
                '<th>Статус</th>' +
                '<th>Transaction ID</th>' +
                '<th>Детали</th>' +
                '</tr>' +
                '</thead>' +
                '<tbody>';

            const status = getStatusBadge(result.result.status);
            const details = result.result.error || result.result.message || 'OK';
            const transactionId = result.result.transaction_id || '-';
            const usersCount = result.result.users_count || '0';

            html +=
                '<tr>' +
                '<td>' + usersCount + '</td>' +
                '<td><span class="status-badge ' + status.class + '">' + status.text + '</span></td>' +
                '<td><small>' + transactionId + '</small></td>' +
                '<td><small>' + details + '</small></td>' +
                '</tr>';
            html +=
                '</tbody>' +
                '</table>';
            resultsContainer.innerHTML = html;
        }

        let isOrdersImporting = false;

        function downloadOrdersCsv() {
            const startDate = document.getElementById('start_date_orders').value;
            if (!startDate) {
                alert('Выберите дату начала выгрузки заказов');
                return;
            }
            window.location.href = '?module=MigrateToMBView&action=downloadOrdersCsv&start_date_orders=' + startDate;
        }

        async function startOrdersImport() {
            if (isOrdersImporting) {
                alert('Импорт заказов уже выполняется!');
                return;
            }
            const startDate = document.getElementById('start_date_orders').value;
            if (!startDate) {
                alert('Выберите дату начала выгрузки заказов');
                return;
            }
            if (!confirm('Начать массовый импорт ЗАКАЗОВ в Mindbox?')) {
                return;
            }
            isOrdersImporting = true;
            const resultsContainer = document.getElementById('resultsOrdersContainer');
            resultsContainer.classList.add('active');
            resultsContainer.innerHTML = '<div class="alert alert-info">🔄 Идёт импорт заказов...</div>';
            // Отключаем ВСЕ кнопки импорта
            document.querySelectorAll('.btn').forEach(btn => btn.disabled = true);

            try {
                const response = await fetch('?module=MigrateToMBView&action=downloadOrders&start_date_orders=' + startDate);
                const rawResponse = await response.text();
                let result = JSON.parse(rawResponse);
                if (result.result.status === 'error') {
                    throw new Error(result.result.message || result.result.error || 'Неизвестная ошибка импорта заказов');
                }
                displayOrdersResults(result);
            } catch (error) {
                resultsContainer.innerHTML =
                    '<div class="alert alert-error">' +
                    '<strong>Ошибка импорта заказов:</strong><br>' +
                    error.message +
                    '</div>';
            } finally {
                isOrdersImporting = false;
                document.querySelectorAll('.btn').forEach(btn => btn.disabled = false); // Включаем кнопки
            }
        }

        function displayOrdersResults(result) {
            const resultsContainer = document.getElementById('resultsOrdersContainer');
            resultsContainer.classList.add('active');
            let html = '';
            if (result.result.status === 'success') {
                html +=
                    '<div class="alert alert-success">' +
                    '<strong>✅ Импорт заказов успешно завершен!</strong><br>' +
                    'Всего заказов: ' + (result.total_orders || '0') + '<br>' +
                    'Дата начала: ' + (result.start_date || '-') +
                    '</div>';
            } else {
                html +=
                    '<div class="alert alert-warning">' +
                    '<strong>⚠️ Импорт заказов завершен с ошибкой</strong><br>' +
                    'Всего заказов: ' + (result.total_orders || '0') + '<br>' +
                    'Дата начала: ' + (result.start_date || '-') +
                    '</div>';
            }
            html +=
                '<table class="results-table">' +
                '<thead>' +
                '<tr>' +
                '<th>Заказов</th>' +
                '<th>Линий</th>' +
                '<th>Статус</th>' +
                '<th>Transaction ID</th>' +
                '<th>Детали</th>' +
                '</tr>' +
                '</thead>' +
                '<tbody>';
            const status = getStatusBadge(result.result.status);
            const details = result.result.error || result.result.message || 'OK';
            const transactionId = result.result.transaction_id || '-';
            const ordersCount = result.result.orders_count || '0';

            html +=
                '<tr>' +
                '<td>' + ordersCount + '</td>' +
                '<td><span class="status-badge ' + status.class + '">' + status.text + '</span></td>' +
                '<td><small>' + transactionId + '</small></td>' +
                '<td><small>' + details + '</small></td>' +
                '</tr>';
            html +=
                '</tbody>' +
                '</table>';

            resultsContainer.innerHTML = html;
        }

        // Предупреждение при закрытии страницы во время импорта
        window.addEventListener('beforeunload', (e) => {
            if (isImporting || isOrdersImporting) {
                e.preventDefault();
                e.returnValue = '';
                return '';
            }
        });
    </script>
{/capture}

{capture name='page_styles'}
    <style>
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input[type="text"] {
            padding: 8px;
            width: 200px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .results {
            margin-top: 30px;
            display: none;
        }
        .results.active {
            display: block;
        }
        .alert {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        .results-table th,
        .results-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        .results-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        .results-table tr:last-child td {
            border-bottom: none;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        .status-success {
            background: #d4edda;
            color: #155724;
        }
        .status-error {
            background: #f8d7da;
            color: #721c24;
        }
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
{/capture}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-cloud-upload"></i> Выгрузка в MindBox
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Выгрузка в MindBox</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Экспорт клиентов в MindBox</h4>
                        <form id="exportForm">
                            <div class="form-group">
                                <label for="start_date">📅 Дата начала выгрузки</label>
                                <input type="text" id="start_date" name="start_date" value="{$start_date_users}" required>
                            </div>
                            <div class="button-group">
                                <button type="button" class="btn btn-success" onclick="downloadUsersCsv()">
                                    💾 Скачать CSV
                                </button>
                                <button type="button" class="btn btn-primary" onclick="startImport()">
                                    <span class="spinner" style="display:none" id="importSpinner"></span>
                                    🚀 Начать импорт
                                </button>
                            </div>
                        </form>
                        <div id="resultsContainer" class="results"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">🚀 Экспорт заказов в MindBox</h4>
                        <form id="exportOrdersForm">
                            <div class="form-group">
                                <label for="start_date_orders">📅 Дата начала выгрузки заказов (изменения)</label>
                                <input type="text" id="start_date_orders" name="start_date_orders" value="{$start_date_orders}" required>
                            </div>
                            <div class="button-group">
                                <button type="button" class="btn btn-success" onclick="downloadOrdersCsv()">
                                    💾 Скачать CSV
                                </button>
                                <button type="button" class="btn btn-primary" onclick="startOrdersImport()">
                                    <span class="spinner" style="display:none" id="importOrdersSpinner"></span>
                                    🚀 Начать импорт
                                </button>
                            </div>
                        </form>
                        <div id="resultsOrdersContainer" class="results"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {include file='footer.tpl'}
</div>