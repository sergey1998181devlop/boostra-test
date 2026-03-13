/* jshint esversion: 11 */
window.app = window.app || {};
window.app.client_tickets = {
    data: null,
    isLoading: false,
};

class ClientApp {
    constructor() {
        this.initSyncUserEmails();

        this.ticketsPage = 1;

        this.initClientTicketsTab();
        this.initClientTicketsTab();
        this.initClientTicketsPagination();
        this.initClientTicketsFilters();
        this.initHighlightTicket();
        this.initVisibilityToggles();

        this.processReceiptLinks();
    }

    showLoadingModal(message = 'Отправляем email\'ы в 1С...') {
        if (!window.Swal) return;

        window.Swal.fire({
            title: '',
            text: message,
            allowOutsideClick: false,
            allowEscapeKey: false,
            onOpen: () => {
                window.Swal.showLoading();
            }
        });
    }

    closeModalIfAny() {
        if (!window.Swal) return;
        window.Swal.close();
    }

    async syncUserEmails(userId) {
        this.showLoadingModal();

        try {
            const response = await fetch('ajax/sync_user_emails.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                },
                body: new URLSearchParams({ user_id: String(userId) }),
            });

            const data = await response.json().catch(() => null);

            this.closeModalIfAny();

            if (!data || data.success !== true) {
                const errorText = data && data.error
                    ? data.error
                    : 'Не удалось отправить email в 1С';

                if (window.Swal) {
                    window.Swal.fire({
                        timer: 5000,
                        title: 'Ошибка!',
                        text: errorText,
                        type: 'error',
                    });
                } else {
                    alert(errorText);
                }
                return;
            }

            if (window.Swal) {
                window.Swal.fire({
                    timer: 5000,
                    title: '',
                    text: `Обработано email'ов: ${data.processed}`,
                    type: 'success',
                });
            }

            document.querySelectorAll('.js-additionalemail-row').forEach((row) => {
                if (row.dataset.synced === '0') {
                    row.dataset.synced = '1';

                    const badge = row.querySelector('.js-email-sync-badge');
                    if (badge) {
                        badge.classList.remove('badge-warning');
                        badge.classList.add('badge-success');
                        badge.textContent = 'Синхронизирован';
                    }
                }
            });

            const hasUnsynced = document.querySelector('.js-additionalemail-row[data-synced="0"]');
            if (!hasUnsynced) {
                const button = document.querySelector('.js-sync-user-emails');
                if (button) {
                    button.remove();
                }
            }
        } catch (e) {
            this.closeModalIfAny();

            if (window.Swal) {
                window.Swal.fire({
                    timer: 5000,
                    title: 'Ошибка!',
                    text: 'Ошибка сети при отправке email в 1С',
                    type: 'error',
                });
            } else {
                alert('Ошибка сети при отправке email в 1С');
            }
        }
    }

    processReceiptLinks() {
        document.querySelectorAll('#table__comments tr td:nth-child(5) a[href]').forEach(link => {
            const href = link.getAttribute('href') || '';
            let label = link.textContent || href;

            if (href.indexOf('view_payment_receipt.php') !== -1) {
                label = 'Ссылка на чек';
            } else if (label.length > 50) {
                label = label.substring(0, 40) + '...';
            }

            link.textContent = label;
            link.setAttribute('target', '_blank');
            link.setAttribute('rel', 'noopener noreferrer');
        });

        const oneCHeader = Array.from(document.querySelectorAll('h3'))
            .find(h3 => h3.textContent.indexOf('Комментарии из 1С') !== -1);
        const oneCTable = oneCHeader ? oneCHeader.nextElementSibling : null;

        if (oneCTable && oneCTable.tagName === 'TABLE') {
            oneCTable.querySelectorAll('tr td:nth-child(3) a[href]').forEach(link => {
                const href = link.getAttribute('href') || '';
                let label = link.textContent || href;

                if (href.indexOf('view_payment_receipt.php') !== -1) {
                    label = 'Ссылка на чек';
                } else if (label.length > 50) {
                    label = label.substring(0, 40) + '...';
                }

                link.textContent = label;
                link.setAttribute('target', '_blank');
                link.setAttribute('rel', 'noopener noreferrer');
            });
        }
    }

    /**
     * Инициализирует обработчик клика по вкладке Тикеты
     */
    initClientTicketsTab() {
        const clientConfig = (window.app && window.app.client_config) || {};
        this.clientId = clientConfig.client_id;

        if (!this.clientId) {
            console.warn('ClientApp: client_id не задан в window.app.client_config');
            return;
        }

        const ticketsTabLink = document.querySelector('a[href="#tickets"]');
        if (!ticketsTabLink) {
            return;
        }

        ticketsTabLink.addEventListener('click', () => {
            const state = window.app.client_tickets;
            if (!state.data && !state.isLoading) {
                this.loadClientTickets(1);
            }
        });
    }

    /**
     * Инициализирует пагинацию
     */
    initClientTicketsPagination() {
        const ticketsTab = document.getElementById('tickets');
        if (!ticketsTab) {
            return;
        }

        ticketsTab.addEventListener('click', (event) => {
            const link = event.target.closest('#pagination-nav .page-link');
            if (!link) {
                return;
            }

            const pageAttr = link.getAttribute('data-page');
            const page = parseInt(pageAttr, 10);

            if (!page || isNaN(page)) {
                return;
            }

            event.preventDefault();

            const state = window.app.client_tickets;
            if (state.isLoading) {
                return;
            }

            this.loadClientTickets(page);
        });
    }

    /**
     * Инициализирует кнопки фильтрации и сброса
     */
    initClientTicketsFilters() {
        const ticketsTab = document.getElementById('tickets');
        if (!ticketsTab) {
            return;
        }

        ticketsTab.addEventListener('click', (event) => {
            if (event.target.closest('#client-tickets-filter-btn')) {
                event.preventDefault();
                this.loadClientTickets(1);
            }

            if (event.target.closest('#client-tickets-reset-btn')) {
                event.preventDefault();
                const filterRow = document.getElementById('client-tickets-filter-row');
                if (filterRow) {
                    filterRow.querySelectorAll('input').forEach(function (input) { input.value = ''; });
                    filterRow.querySelectorAll('select').forEach(function (select) { select.selectedIndex = 0; });
                }
                this.loadClientTickets(1);
            }
        });
    }

    /**
     * Собирает значения фильтров из строки фильтрации
     */
    collectFilters() {
        const filters = {};
        const filterRow = document.getElementById('client-tickets-filter-row');
        if (!filterRow) {
            return filters;
        }

        filterRow.querySelectorAll('input[name], select[name]').forEach(function (el) {
            const val = el.value.trim();
            if (val) {
                filters[el.name] = val;
            }
        });

        return filters;
    }

    /**
     * Инициализирует верхний скроллбар для таблицы тикетов
     */
    initClientTicketsTopScroll() {
        const topScroll = document.getElementById('client-tickets-scrollbar-top');
        const tableContainer = document.getElementById('client-tickets-table-container');
        const table = document.getElementById('client-tickets-table');

        if (!topScroll || !tableContainer || !table) {
            return;
        }

        const dummy = topScroll.querySelector('.dummy');
        if (dummy) {
            dummy.style.width = table.scrollWidth + 'px';
        }

        topScroll.addEventListener('scroll', function () {
            tableContainer.scrollLeft = topScroll.scrollLeft;
        });

        tableContainer.addEventListener('scroll', function () {
            topScroll.scrollLeft = tableContainer.scrollLeft;
        });
    }

    /**
     * Инициализирует daterangepicker для фильтров дат
     */
    initClientTicketsDatepickers() {
        if (typeof jQuery === 'undefined' || typeof jQuery.fn.daterangepicker === 'undefined') {
            return;
        }

        jQuery('#client-tickets-filter-row .client-tickets-daterange').each(function () {
            const $input = jQuery(this);
            if ($input.data('daterangepicker')) {
                return;
            }
            $input.daterangepicker({
                autoUpdateInput: false,
                locale: {
                    format: 'DD.MM.YYYY',
                    applyLabel: 'Применить',
                    cancelLabel: 'Очистить',
                    daysOfWeek: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
                    monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
                    firstDay: 1
                }
            });
            $input.on('apply.daterangepicker', function (ev, picker) {
                $input.val(picker.startDate.format('DD.MM.YYYY') + ' - ' + picker.endDate.format('DD.MM.YYYY'));
            });
            $input.on('cancel.daterangepicker', function () {
                $input.val('');
            });
        });
    }

    /**
     * Загружает тикеты клиента и рендерит их во вкладку "Тикеты".
     */
    async loadClientTickets(page) {
        const clientId = this.clientId;
        if (!clientId) {
            console.error('ClientApp.loadClientTickets: clientId is empty');
            return;
        }

        const state = window.app.client_tickets;
        if (state.isLoading) {
            return;
        }

        const targetPage = page || this.ticketsPage || 1;

        const container = document.getElementById('tab_tickets_container');
        if (!container) {
            console.error('ClientApp.loadClientTickets: #tab_tickets_container not found');
            return;
        }

        state.isLoading = true;

        if (!container.innerHTML.trim()) {
            container.innerHTML = `
            <div class="text-center p-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Загрузка тикетов...</span>
                </div>
                <p>Загрузка тикетов...</p>
            </div>
        `;
        }

        const formData = new FormData();
        formData.append('action', 'get_tickets');
        formData.append('user_id', String(clientId));
        formData.append('page', String(targetPage));

        const filters = this.collectFilters();
        for (const key in filters) {
            if (filters.hasOwnProperty(key)) {
                formData.append('search[' + key + ']', filters[key]);
            }
        }

        try {
            const response = await fetch(window.location.pathname, {
                method: 'POST',
                body: formData,
                cache: 'no-cache',
            });

            const respText = await response.text();
            let resp = null;

            try {
                resp = JSON.parse(respText);
            } catch (e) {
                const jsonStart = respText.indexOf('{');
                if (jsonStart !== -1) {
                    try {
                        resp = JSON.parse(respText.substring(jsonStart));
                    } catch (e2) {
                        console.error('ClientApp.loadClientTickets: JSON parse error (2nd attempt)', e2);
                    }
                } else {
                    console.error('ClientApp.loadClientTickets: response is not JSON', respText);
                }
            }

            if (!resp || typeof resp !== 'object') {
                console.error('ClientApp.loadClientTickets: response is not an object', respText);
                return;
            }

            if (resp.error) {
                console.error('ClientApp.loadClientTickets: backend error', resp.error);
                container.innerHTML = `
                <div class="alert alert-danger m-3">
                    Не удалось загрузить тикеты клиента. Попробуйте обновить страницу.
                </div>
            `;
                return;
            }

            this.ticketsPage = resp.current_page || targetPage;
            state.data = resp;

            if (resp.tickets_html) {
                container.innerHTML = resp.tickets_html;
                this.initClientTicketsTopScroll();
                this.initClientTicketsDatepickers();
            } else {
                container.innerHTML = `
                <div class="alert alert-info m-3">
                    Для этого клиента тикеты не найдены.
                </div>
            `;
            }
        } catch (e) {
            console.error('ClientApp.loadClientTickets: network/runtime error', e);
            container.innerHTML = `
            <div class="alert alert-danger m-3">
                Ошибка при загрузке тикетов клиента. Проверьте соединение и обновите страницу.
            </div>
        `;
        } finally {
            state.isLoading = false;
        }
    }

    initSyncUserEmails() {
        document.addEventListener('click', (event) => {
            const button = event.target.closest('.js-sync-user-emails');
            if (!button) return;

            event.preventDefault();

            const userId = button.dataset.userId;
            if (!userId) return;

            this.syncUserEmails(userId);
        });
    }

    /**
     * Инициализирует обработчик подсветки тикета
     */
    initHighlightTicket() {
        document.addEventListener('click', (event) => {
            const btn = event.target.closest('.js-highlight-ticket');
            if (!btn) return;

            event.preventDefault();

            if (btn.classList.contains('disabled')) return;

            const ticketId = btn.dataset.ticketId;
            if (!ticketId) return;

            btn.classList.add('disabled');
            const originalText = btn.textContent;
            btn.textContent = 'В ПРОЦЕССЕ...';

            const formData = new FormData();
            formData.append('action', 'highlight_ticket');
            formData.append('ticket_id', ticketId);

            fetch('/tickets', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(res => res.json())
                .then(res => {
                    if (res && res.success) {
                        btn.classList.remove('btn-warning');
                        btn.classList.add('btn-success');
                        btn.textContent = 'ПОДСВЕЧЕНО';

                        if (window.Swal) {
                            window.Swal.fire({
                                timer: 3000,
                                title: 'Успех!',
                                text: res.message || 'Тикет успешно подсвечен',
                                type: 'success',
                            });
                        }
                    } else {
                        const errorMsg = res && res.message ? res.message : 'Ошибка подсветки тикета';

                        if (window.Swal) {
                            window.Swal.fire({
                                timer: 5000,
                                title: 'Ошибка!',
                                text: errorMsg,
                                type: 'error',
                            });
                        } else {
                            alert(errorMsg);
                        }

                        btn.classList.remove('disabled');
                        btn.textContent = originalText;
                    }
                })
                .catch(err => {
                    console.error('Highlight ticket error:', err);
                    const errorMsg = 'Ошибка запроса на сервер';

                    if (window.Swal) {
                        window.Swal.fire({
                            timer: 5000,
                            title: 'Ошибка!',
                            text: errorMsg,
                            type: 'error',
                        });
                    } else {
                        alert(errorMsg);
                    }

                    btn.classList.remove('disabled');
                    btn.textContent = originalText;
                });
        });
    }

    /**
     * Инициализирует обработчик переключателей видимости информации в ЛК (цессия, агенты)
     */
    initVisibilityToggles() {
        document.addEventListener('change', (event) => {
            const toggleId = event.target.id;

            if (toggleId === 'show_cession_info' || toggleId === 'show_agent_info') {
                const checkbox = event.target;
                const clientId = this.clientId || checkbox.dataset.user;
                if (!clientId) {
                    console.error(`ClientApp: client_id не найден для переключателя ${toggleId}`);
                    return;
                }

                checkbox.disabled = true;
                const originalState = checkbox.checked;

                const formData = new FormData();
                formData.append('user_id', String(clientId));
                formData.append('action', `toggle_${toggleId}`);
                formData.append(toggleId, originalState ? '1' : '0');

                fetch(`client/${clientId}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(res => res.json())
                    .then(res => {
                        if (res && res.success) {
                            if (window.Swal) {
                                window.Swal.fire({
                                    timer: 5000,
                                    title: 'Успешно',
                                    text: 'Настройки видимости изменены',
                                    type: 'success',
                                });
                            }
                        } else {
                            checkbox.checked = !originalState;
                            const errorMsg = res && res.error ? (typeof window.translateMessage === 'function' ? window.translateMessage(res.error) : res.error) : 'Ошибка при сохранении';
                            if (window.Swal) {
                                window.Swal.fire({
                                    timer: 5000,
                                    title: 'Ошибка!',
                                    text: errorMsg,
                                    type: 'error',
                                });
                            } else {
                                alert(errorMsg);
                            }
                        }
                    })
                    .catch(err => {
                        checkbox.checked = !originalState;
                        console.error(`${toggleId} toggle error:`, err);
                        const errorMsg = 'Ошибка запроса на сервер';
                        if (window.Swal) {
                            window.Swal.fire({
                                timer: 5000,
                                title: 'Ошибка!',
                                text: errorMsg,
                                type: 'error',
                            });
                        } else {
                            alert(errorMsg);
                        }
                    })
                    .finally(() => {
                        checkbox.disabled = false;
                    });
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.clientApp = new ClientApp();
});
