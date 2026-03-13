$(function () {
    /**
     * Назначение событий переключения видимости
     */
    function bindToggleHandlers() {
        $('.toggle-company-visibility').on('change', function () {
            const checkbox = $(this);
            const companyId = checkbox.data('id');
            const isActive = checkbox.is(':checked') ? 1 : 0;

            $.ajax({
                url: '/app/tickets/companies/' + companyId + '/use-in-tickets',
                method: 'PATCH',
                contentType: 'application/json',
                data: JSON.stringify({ use_in_tickets: isActive }),
                success: function (response) {
                    if (response.success) {
                        if (window.showSuccessMessage) {
                            window.showSuccessMessage('Статус компании обновлён');
                        }
                    } else {
                        if (window.showErrorMessage) {
                            window.showErrorMessage(response.message || 'Ошибка при обновлении');
                        }
                    }
                },
                error: function () {
                    if (window.showErrorMessage) {
                        window.showErrorMessage('Ошибка при соединении с сервером');
                    }
                }
            });
        });
    }

    /**
     * Отображение списка компаний в таблице
     * @param {Array} companies
     */
    function renderCompanies(companies) {
        const tbody = $('#companiesTable tbody');
        tbody.empty();

        companies.forEach(function (company) {
            const isActive = parseInt(company.use_in_tickets, 10) === 1;
            const companyId = company.id;
            const switchId = 'toggleCompany' + companyId;

            const row = `
                <tr data-id="${companyId}">
                    <td>${companyId}</td>
                    <td>${company.short_name}</td>
                    <td>
                        <div class="custom-control custom-switch">
                            <input type="checkbox"
                                   class="custom-control-input toggle-company-visibility"
                                   id="${switchId}"
                                   data-id="${companyId}"
                                   ${isActive ? 'checked' : ''}>
                            <label class="custom-control-label" for="${switchId}"></label>
                        </div>
                    </td>
                </tr>
            `;

            tbody.append(row);
        });

        // Назначение обработчиков на тумблеры
        bindToggleHandlers();
    }

    /**
     * Загрузка списка компаний с сервера
     */
    function loadCompanies() {
        $.ajax({
            url: '/app/tickets/companies',
            method: 'GET',
            success: function (response) {
                if (response.success) {
                    renderCompanies(response.data);
                } else {
                    if (window.showErrorMessage) {
                        window.showErrorMessage(response.message || 'Не удалось загрузить компании');
                    }
                }
            },
            error: function () {
                if (window.showErrorMessage) {
                    window.showErrorMessage('Ошибка при соединении с сервером');
                }
            }
        });
    }

    // Инициализация страницы
    loadCompanies();
});
