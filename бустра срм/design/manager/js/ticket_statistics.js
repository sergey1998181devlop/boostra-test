$(document).ready(function() {
    var ACTIVE_TAB_STORAGE_KEY = 'ticket_statistics_active_tab';

    function readStoredActiveTab() {
        try {
            return window.sessionStorage.getItem(ACTIVE_TAB_STORAGE_KEY) || '';
        } catch (e) {
            return '';
        }
    }

    function writeStoredActiveTab(tabId) {
        try {
            window.sessionStorage.setItem(ACTIVE_TAB_STORAGE_KEY, tabId);
        } catch (e) {
        }
    }

    function getTabIdFromLink($link) {
        var href = $link.attr('href') || '';

        if (href.indexOf('#') !== 0) {
            return '';
        }

        return href.replace('#', '');
    }

    function getCurrentTabId() {
        var $activeLink = $('#statsTab a.nav-link.active');
        if (!$activeLink.length) {
            return '';
        }

        return getTabIdFromLink($activeLink);
    }

    function ensureTabField($form) {
        var $input = $form.find('input[name="tab"]');
        if (!$input.length) {
            $input = $('<input type="hidden" name="tab">').appendTo($form);
        }

        return $input;
    }

    function syncTabInForms(tabId) {
        if (!tabId) {
            return;
        }

        $('.ticket-statistics form').each(function () {
            ensureTabField($(this)).val(tabId);
        });
    }

    function syncTabInUrl(tabId) {
        if (!tabId || !window.history || !window.history.replaceState) {
            return;
        }

        var url = new URL(window.location.href);
        url.searchParams.set('tab', tabId);
        window.history.replaceState({}, '', url.pathname + url.search + url.hash);
    }

    function setActiveTab(tabId, syncUrl) {
        if (!tabId) {
            return;
        }

        syncTabInForms(tabId);
        writeStoredActiveTab(tabId);

        if (syncUrl !== false) {
            syncTabInUrl(tabId);
        }
    }

    $('#statsTab a[data-toggle="tab"]').on('shown.bs.tab', function () {
        setActiveTab(getTabIdFromLink($(this)));
    });

    $('.ticket-statistics').on('submit', 'form', function () {
        setActiveTab(getCurrentTabId(), false);
    });

    var activeTabFromUrl = new URLSearchParams(window.location.search).get('tab');
    var activeTabFromStorage = readStoredActiveTab();
    var $initialTab = $();

    if (activeTabFromUrl) {
        $initialTab = $('#statsTab a[href="#' + activeTabFromUrl + '"]');
    }

    if (!$initialTab.length && activeTabFromStorage) {
        $initialTab = $('#statsTab a[href="#' + activeTabFromStorage + '"]');
    }

    if ($initialTab.length) {
        var initialTab = getTabIdFromLink($initialTab);
        $initialTab.tab('show');
        setActiveTab(initialTab);
    } else {
        setActiveTab(getCurrentTabId());
    }

    // Функция для переключения видимости деталей месяца в таблицах времени
    function toggleTimeTableDetails($row) {
        const month = $row.data('month');
        if (!month) return;

        const $table = $row.closest('table');
        const $icon = $row.find('.toggle-icon');
        const $detailRows = $table.find(`.daily-details-row[data-month="${month}"]`);

        $detailRows.toggle();

        // Синхронизируем состояние иконки с видимостью строк
        if ($detailRows.filter(':visible').length > 0) {
            $icon.removeClass('fa-plus-circle').addClass('fa-minus-circle');
        } else {
            $icon.removeClass('fa-minus-circle').addClass('fa-plus-circle');
        }
    }

    // Обработчик для таблиц во вкладке "Время обработки"
    $('#timing').on('click', 'tr.month-row', function(e) {
        const $table = $(this).closest('table');
        
        // Проверяем, что клик был в одной из наших таблиц
        if ($table.is('#reaction-time-table, #processing-time-table')) {
            toggleTimeTableDetails($(this));
            e.stopPropagation();
        }
    });

    // Обработчик для таблиц во вкладке "Статистика по статусам"
    $('#statistics-by-status-tab').on('click', '.month-row', function (e) {
        e.stopPropagation();

        const month = $(this).data('month');
        const icon = $(this).find('.toggle-icon');
        const details = $('#statistics-by-status-tab .daily-details-row[data-month="' + month + '"]');

        if (details.is(':visible')) {
            details.hide();
            icon.removeClass('fa-minus-circle').addClass('fa-plus-circle');
        } else {
            details.show();
            icon.removeClass('fa-plus-circle').addClass('fa-minus-circle');
        }
    });

    // Обработчик клика по строке месяца во вкладке "Детальная статистика"
    $('#detailed').on('click', '.month-row', function() {
        let month = $(this).data('month');
        let icon = $(this).find('.toggle-icon');

        // Проверяем, загружены ли уже строки для этого месяца
        let existingDayRows = $('tr.detailed-day-row[data-month="' + month + '"]');

        if (existingDayRows.length > 0) {
            // Если строки уже созданы, просто переключаем их видимость
            if (icon.hasClass('fa-plus-circle')) {
                icon.removeClass('fa-plus-circle').addClass('fa-minus-circle');
                existingDayRows.show();
            } else {
                icon.removeClass('fa-minus-circle').addClass('fa-plus-circle');
                existingDayRows.hide();
            }
            return;
        }

        // Если строки еще не загружены, делаем AJAX-запрос
        let placeholderRow = $('.day-row-placeholder[data-month="' + month + '"]');
        placeholderRow.show();
        placeholderRow.html('<td colspan="30%" class="text-center"><i class="fa fa-spinner fa-spin"></i> Загрузка данных...</td>');

        $.ajax({
            url: 'tickets/statistics',
            type: 'GET',
            data: {
                action: 'get_daily_stats',
                month: month
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    try {
                        // Генерируем HTML для строк с дневной статистикой
                        let dayRowsHtml = generateDailyRowsHtml(response.data, month);

                        // Если данных нет, показываем соответствующее сообщение
                        if (!dayRowsHtml || dayRowsHtml.trim() === '') {
                            placeholderRow.html('<td colspan="30%" class="text-center text-warning">Нет данных для отображения</td>');
                            return;
                        }

                        // Вставляем после плейсхолдера
                        $(dayRowsHtml).insertAfter(placeholderRow);

                        // Скрываем плейсхолдер и меняем иконку
                        placeholderRow.hide();
                        icon.removeClass('fa-plus-circle').addClass('fa-minus-circle');
                    } catch (error) {
                        console.error('Ошибка при генерации HTML:', error);
                        placeholderRow.html('<td colspan="30%" class="text-center text-danger">' +
                            'Ошибка при обработке данных: ' + error.message +
                            ' <a href="#" class="retry-load-days" data-month="' + month + '"><i class="fa fa-refresh"></i> Повторить</a></td>');
                    }
                } else {
                    // Если возникла ошибка
                    placeholderRow.html('<td colspan="30%" class="text-center text-danger">' +
                        'Ошибка: ' + (response.message || 'Не удалось загрузить данные') +
                        ' <a href="#" class="retry-load-days" data-month="' + month + '"><i class="fa fa-refresh"></i> Повторить</a></td>');
                }
            },
            error: function(xhr, status, error) {
                // Ошибка AJAX-запроса
                placeholderRow.html('<td colspan="30%" class="text-center text-danger">' +
                    'Ошибка сети: ' + error +
                    ' <a href="#" class="retry-load-days" data-month="' + month + '"><i class="fa fa-refresh"></i> Повторить</a></td>');
            }
        });
    });

    // Обработчик для повторной загрузки при ошибке
    $(document).on('click', '.retry-load-days', function(e) {
        e.preventDefault();
        e.stopPropagation();

        let month = $(this).data('month');
        $('.month-row[data-month="' + month + '"]').click();
    });

    // Добавить обработчик для новой вкладки
    $('#assignment-tab').on('shown.bs.tab', function (e) {
        loadAssignmentStats();
    });
});

/**
 * Генерирует HTML для строк с ежедневной статистикой
 *
 * @param {Object} data Данные статистики по дням
 * @param {string} month Месяц в формате "YYYY-MM"
 * @returns {string} HTML-код строк таблицы
 */
function generateDailyRowsHtml(data, month) {
    let html = '';

    // Получаем список дней, сортированных по дате
    const sortedDates = Object.keys(data).sort();

    // Для каждого дня создаем строку таблицы
    for (let date of sortedDates) {
        const dayData = data[date];

        // Создаем строку дня только если есть тикеты
        if (dayData.total === 0) continue;

        // Создаем строку дня
        html += '<tr class="detailed-day-row" data-month="' + month + '">';

        // Ячейка с датой (занимает 2 колонки)
        html += '<td colspan="2" class="pl-4 border-right">' + new Date(date).getDate() + '</td>';

        // Общая статистика по каналам
        // Проходим по всем возможным каналам
        for (let channelId in dayData.channels || {}) {
            const count = dayData.channels[channelId];
            html += '<td class="text-center ' + (count > 0 ? 'font-weight-bold' : '') + '">' +
                (count > 0 ? count : '<span class="text-muted">0</span>') + '</td>';
        }

        // Общее количество тикетов за день
        html += '<td class="font-weight-bold text-center border-right">' + dayData.total + '</td>';

        // Получаем список доступных статусов в данных
        let statusIds = Object.keys(dayData).filter(key =>
            !isNaN(parseInt(key)) &&
            parseInt(key) > 0 &&
            typeof dayData[key] === 'object'
        ).sort((a, b) => parseInt(a) - parseInt(b));

        // Для каждого статуса
        for (let statusId of statusIds) {
            // Получаем все родительские темы для этого статуса
            let subjectIds = Object.keys(dayData[statusId]).filter(key =>
                !isNaN(parseInt(key)) &&
                typeof dayData[statusId][key] === 'object'
            ).sort((a, b) => parseInt(a) - parseInt(b));

            // Для каждой родительской темы в данном статусе
            for (let subjectId of subjectIds) {
                const subjectData = dayData[statusId][subjectId];

                // Данные по каналам для этой темы и статуса
                for (let channelId in subjectData.channels || {}) {
                    const count = subjectData.channels[channelId];
                    html += '<td class="text-center ' + (count > 0 ? 'font-weight-bold' : '') + '">' +
                        (count > 0 ? count : '<span class="text-muted">0</span>') + '</td>';
                }

                // Итого для темы
                html += '<td class="text-center ' + (subjectData.total > 0 ? 'font-weight-bold' : '') + '">' +
                    (subjectData.total > 0 ? subjectData.total : '<span class="text-muted">0</span>') + '</td>';

                // Процент для темы
                const percentage = subjectData.percentage || 0;
                html += '<td class="text-center border-right">' +
                    (percentage > 0 ? percentage + '%' : '<span class="text-muted">0%</span>') + '</td>';
            }
        }

        // Добавляем данные по дочерним темам, если они есть
        if (dayData.childSubjects) {
            // Получаем все дочерние темы
            let childSubjectIds = Object.keys(dayData.childSubjects).sort((a, b) => parseInt(a) - parseInt(b));

            // Для каждой дочерней темы
            for (let childSubjectId of childSubjectIds) {
                const childSubjectData = dayData.childSubjects[childSubjectId];

                // Данные по каналам для дочерней темы
                for (let channelId in childSubjectData.channels || {}) {
                    const count = childSubjectData.channels[channelId];
                    html += '<td class="text-center ' + (count > 0 ? 'font-weight-bold' : '') + '">' +
                        (count > 0 ? count : '<span class="text-muted">0</span>') + '</td>';
                }
            }
        }

        html += '</tr>';
    }

    return html;
}
