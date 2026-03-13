/**
 * Управление статистикой менеджеров по тикетам
 * Оптимизированная версия
 */
$(document).ready(function() {
    // Обработчик клика по строке месяца
    $(document).on('click', '.toggle-month', function() {
        const month = $(this).data('month');
        const icon = $(this).find('.toggle-icon');

        // Проверяем, загружены ли уже данные для этого месяца
        const existingDayRows = $('tr.detailed-day-row[data-month="' + month + '"]');

        if (existingDayRows.length > 0) {
            // Если строки уже загружены, просто переключаем их видимость
            toggleRowsVisibility(existingDayRows, icon);
            return;
        }

        // Если данные еще не загружены, загружаем их
        loadMonthlyData(month, icon);
    });

    // Обработчик для повторной загрузки при ошибке
    $(document).on('click', '.retry-load-days', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const month = $(this).data('month');
        const icon = $('.total-row[data-month="' + month + '"] .toggle-icon');

        // Удаляем существующие строки с ошибкой
        $('.day-row-placeholder[data-month="' + month + '"]').html('');

        // Загружаем данные заново
        loadMonthlyData(month, icon);
    });

    /**
     * Загружает данные за месяц через AJAX
     *
     * @param {string} month - Месяц в формате "YYYY-MM"
     * @param {jQuery} icon - Элемент иконки для переключения
     */
    function loadMonthlyData(month, icon) {
        const placeholderRow = $('.day-row-placeholder[data-month="' + month + '"]');
        placeholderRow.show();
        placeholderRow.html('<td colspan="30%" class="text-center"><i class="fa fa-spinner fa-spin"></i> Загрузка данных...</td>');

        $.ajax({
            url: 'tickets/manager-statistics',
            type: 'GET',
            data: {
                action: 'get_monthly_data',
                month: month
            },
            dataType: 'json',
            success: function(response) {
                handleAjaxSuccess(response, month, icon, placeholderRow);
            },
            error: function(xhr, status, error) {
                handleAjaxError(error, month, placeholderRow);
            }
        });
    }

    /**
     * Обрабатывает успешный ответ AJAX
     *
     * @param {Object} response - Ответ от сервера
     * @param {string} month - Месяц в формате "YYYY-MM"
     * @param {jQuery} icon - Элемент иконки
     * @param {jQuery} placeholderRow - Строка-плейсхолдер
     */
    function handleAjaxSuccess(response, month, icon, placeholderRow) {
        if (!response.success) {
            handleAjaxError(response.message || 'Не удалось загрузить данные', month, placeholderRow);
            return;
        }

        try {
            // Генерируем HTML для дневных данных
            const dayRowsHtml = generateDailyRowsHtml(response.data, month);

            if (!dayRowsHtml || dayRowsHtml.trim() === '') {
                placeholderRow.html('<td colspan="30%" class="text-center text-warning">Нет данных для отображения</td>');
                return;
            }

            // Вставляем сгенерированные строки после плейсхолдера
            $(dayRowsHtml).insertAfter(placeholderRow);

            // Скрываем плейсхолдер и меняем иконку
            placeholderRow.hide();
            icon.removeClass('fa-plus-circle').addClass('fa-minus-circle');
        } catch (error) {
            console.error('Ошибка при генерации HTML:', error);
            handleAjaxError(error.message, month, placeholderRow);
        }
    }

    /**
     * Обрабатывает ошибки AJAX
     *
     * @param {string} error - Текст ошибки
     * @param {string} month - Месяц в формате "YYYY-MM"
     * @param {jQuery} placeholderRow - Строка-плейсхолдер
     */
    function handleAjaxError(error, month, placeholderRow) {
        placeholderRow.html(
            '<td colspan="30%" class="text-center text-danger">' +
            'Ошибка: ' + error +
            ' <a href="#" class="retry-load-days" data-month="' + month + '"><i class="fa fa-refresh"></i> Повторить</a></td>'
        );
    }

    /**
     * Переключает видимость строк и меняет иконку
     *
     * @param {jQuery} rows - Строки для переключения видимости
     * @param {jQuery} icon - Элемент иконки
     */
    function toggleRowsVisibility(rows, icon) {
        if (icon.hasClass('fa-plus-circle')) {
            icon.removeClass('fa-plus-circle').addClass('fa-minus-circle');
            rows.show();
        } else {
            icon.removeClass('fa-minus-circle').addClass('fa-plus-circle');
            rows.hide();
        }
    }
});

/**
 * Генерирует HTML для строк с ежедневной статистикой
 *
 * @param {Object} data - Данные статистики по дням
 * @param {string} month - Месяц в формате "YYYY-MM"
 * @returns {string} HTML-код строк таблицы
 */
function generateDailyRowsHtml(data, month) {
    let html = '';

    // Получаем список дней, сортированных по дате
    const sortedDates = Object.keys(data).sort();

    // Для каждого дня создаем строку таблицы
    for (let date of sortedDates) {
        const dayData = data[date];

        // Если у менеджеров нет тикетов за день, пропускаем
        if (!dayData.managers || dayData.totals.total === 0) {
            continue;
        }

        // Создаем строку для дня
        html += '<tr class="detailed-day-row" data-month="' + month + '">';

        // Добавляем ячейку с датой (форматируем её для отображения)
        const displayDate = new Date(date).getDate();
        html += '<td colspan="2" class="text-center">' + displayDate + '</td>';

        // Добавляем данные по каждому менеджеру
        const managerIds = Object.keys(dayData.managers);

        for (let managerId of managerIds) {
            const managerData = dayData.managers[managerId];

            // Добавляем ячейки с данными (решенные, нерешенные, всего)
            html += '<td class="text-center ' + (managerData.resolved > 0 ? 'font-weight-bold' : '') + '">' +
                formatValue(managerData.resolved) + '</td>';

            html += '<td class="text-center ' + (managerData.unresolved > 0 ? 'font-weight-bold' : '') + '">' +
                formatValue(managerData.unresolved) + '</td>';

            html += '<td class="text-center ' + (managerData.total > 0 ? 'font-weight-bold' : '') + '">' +
                formatValue(managerData.total) + '</td>';
        }

        html += '</tr>';
    }

    return html;
}

/**
 * Форматирует значение для отображения
 *
 * @param {number} value - Числовое значение
 * @returns {string} Отформатированное значение
 */
function formatValue(value) {
    return value > 0 ? value : '<span class="text-muted">0</span>';
}