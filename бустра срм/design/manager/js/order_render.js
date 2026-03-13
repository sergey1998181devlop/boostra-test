window.app = window.app || {};
window.app.scorings_data = null;
window.app.credit_history_data = null;
window.app.documents_data = null;
window.app.comments_data = null;
window.app.logs_data = null;
window.app.insures_data = null;
window.app.overpayments_data = null;

/**
 * Обрабатывает ссылки в комментариях из 1С.
 * В таблице "Комментарии из 1С" меняет текст ссылок:
 *  - ссылки на чек -> "Ссылка на чек"
 *  - слишком длинные ссылки укорачивает.
 *
 * @param {HTMLElement} root
 * @param commentColumnIndex
 */
function processCommentLinks(root, commentColumnIndex) {
    if (!root) return;

    const commentCells = root.querySelectorAll(
        'table tr td:nth-child(' + commentColumnIndex + ')'
    );

    commentCells.forEach(cell => {
        const links = cell.querySelectorAll('a[href]');

        links.forEach(link => {
            const url = link.getAttribute('href') || '';
            let label = link.textContent || url;

            if (url.indexOf('view_payment_receipt.php') !== -1) {
                label = 'Ссылка на чек';
            } else if (label.length > 50) {
                label = label.substring(0, 40) + '...';
            }

            link.textContent = label;
            link.setAttribute('target', '_blank');
            link.setAttribute('rel', 'noopener noreferrer');
        });
    });
}

/**
 * Загружает комментарии пользователя через AJAX.
 * Обновляет вкладку "Комментарии" на странице заявки.
 * 
 * @param {number} order_id - ID заявки.
 */
function load_comments(order_id) {
    if (!order_id) {
        console.error('load_comments: order_id is empty');
        return;
    }
    console.log('load_comments called for order_id:', order_id);

    const formData = new FormData();
    formData.append('action', 'get_comments');
    formData.append('order_id', order_id);

    fetch(window.location.pathname, {
        method: 'POST',
        body: formData,
        cache: 'no-cache'
    })
    .then(response => response.text())
    .then(respText => {
        let resp;
        try {
            resp = JSON.parse(respText);
        } catch (e) {
            try {
                let jsonStart = respText.indexOf('{');
                if (jsonStart !== -1) {
                    resp = JSON.parse(respText.substring(jsonStart));
                }
            } catch (e2) {
                console.error('Failed to parse JSON from response:', e2);
            }
        }

        if (typeof resp !== 'object' || resp === null) {
            console.error('Response is not an object:', respText);
            return;
        }

        console.log('load_comments success:', resp);
        if (resp.error) {
            console.error('Error loading comments:', resp.error);
            return;
        }

        window.app.comments_data = resp;
        
        if (resp.comments_html) {
            const container = document.getElementById('tab_comments');
            if (container) {
                container.classList.remove('loading');
                container.innerHTML = '<div id="tab_comments_container">' + resp.comments_html + '</div>';

                const oneCBlock = container.querySelector('.js-load-comments-block');
                if (oneCBlock) {
                    processCommentLinks(oneCBlock, 3);
                }

                const crmHeader = container.querySelector('h4.card-header');
                if (crmHeader && crmHeader.textContent.indexOf('Комментарии CRM') !== -1) {
                    const crmBlock = crmHeader.closest('.col-md-6') || crmHeader.parentElement;
                    if (crmBlock) {
                        processCommentLinks(crmBlock, 5);
                    }
                }
            }
        }

        // Обновляем блоки комментариев на вкладке заявки
        if (resp.comment_blocks) {
            for (const block in resp.comment_blocks) {
                const blockContainer = document.querySelector('.js-comments-block-' + block);
                if (blockContainer) {
                    blockContainer.innerHTML = resp.comment_blocks[block];
                }
            }

            const autodebitBlock = document.querySelector('.js-comments-block-autodebit');
            if (autodebitBlock) {
                autodebitBlock.querySelectorAll('a[href]').forEach(link => {
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
    })
    .catch(error => {
        console.error('load_comments error:', error);
        const container = document.getElementById('tab_comments');
        if (container) {
            container.classList.remove('loading');
        }
    });
}

/**
 * Загружает документы пользователя через AJAX.
 * Обновляет вкладку "Документы" на странице заявки.
 * 
 * @param {number} order_id - ID заявки.
 */
function load_documents(order_id) {
    if (!order_id) {
        console.error('load_documents: order_id is empty');
        return;
    }
    console.log('load_documents called for order_id:', order_id);

    const formData = new FormData();
    formData.append('action', 'get_documents');
    formData.append('order_id', order_id);

    fetch(window.location.pathname, {
        method: 'POST',
        body: formData,
        cache: 'no-cache'
    })
    .then(response => response.text())
    .then(respText => {
        let resp;
        try {
            resp = JSON.parse(respText);
        } catch (e) {
            try {
                let jsonStart = respText.indexOf('{');
                if (jsonStart !== -1) {
                    resp = JSON.parse(respText.substring(jsonStart));
                }
            } catch (e2) {
                console.error('Failed to parse JSON from response:', e2);
            }
        }

        if (typeof resp !== 'object' || resp === null) {
            console.error('Response is not an object:', respText);
            return;
        }

        console.log('load_documents success:', resp);
        if (resp.error) {
            console.error('Error loading documents:', resp.error);
            return;
        }

        window.app.documents_data = resp;
        
        if (resp.documents_html) {
            const container = document.getElementById('tab_documents_container');
            if (container) {
                container.innerHTML = resp.documents_html;
            }
        }
        
        if (resp.documents_minus_html) {
            const container = document.getElementById('tab_documents_minus_container');
            if (container) {
                container.innerHTML = resp.documents_minus_html;
            }
        }
    })
    .catch(error => {
        console.error('load_documents error:', error);
    });
}

/**
 * Загружает кредитную историю пользователя через AJAX.
 * Обновляет вкладку "История" на странице заявки.
 * 
 * @param {number} order_id - ID заявки.
 */
function load_credit_history(order_id) {
    if (!order_id) {
        console.error('load_credit_history: order_id is empty');
        return;
    }
    console.log('load_credit_history called for order_id:', order_id);

    const formData = new FormData();
    formData.append('action', 'get_credit_history');
    formData.append('order_id', order_id);

    fetch(window.location.pathname, {
        method: 'POST',
        body: formData,
        cache: 'no-cache'
    })
    .then(response => response.text())
    .then(respText => {
        let resp;
        try {
            resp = JSON.parse(respText);
        } catch (e) {
            try {
                let jsonStart = respText.indexOf('{');
                if (jsonStart !== -1) {
                    resp = JSON.parse(respText.substring(jsonStart));
                }
            } catch (e2) {
                console.error('Failed to parse JSON from response:', e2);
            }
        }

        if (typeof resp !== 'object' || resp === null) {
            console.error('Response is not an object:', respText);
            return;
        }

        console.log('load_credit_history success:', resp);
        if (resp.error) {
            console.error('Error loading credit history:', resp.error);
            return;
        }

        window.app.credit_history_data = resp;
        window.app.order_config.credit_history_html = resp.credit_history_html;

        const container = document.getElementById('tab_history_container');
        if (container) {
            container.innerHTML = resp.credit_history_html;
        }
    })
    .catch(error => {
        console.error('load_credit_history error:', error);
    });
}

/**
 * Загружает логи пользователя через AJAX.
 * Обновляет вкладку "Логирование" на странице заявки.
 * 
 * @param {number} order_id - ID заявки.
 * @param {Object} [filters={}] - Фильтры (поиск, сортировка).
 */
function load_logs(order_id, filters = {}) {
    if (!order_id) {
        console.error('load_logs: order_id is empty');
        return;
    }
    console.log('load_logs called for order_id:', order_id, 'filters:', filters);

    const formData = new FormData();
    formData.append('action', 'get_logs');
    formData.append('order_id', order_id);
    
    if (filters.search) {
        for (let key in filters.search) {
            formData.append('search[' + key + ']', filters.search[key]);
        }
    }
    
    if (filters.sort) {
        formData.append('sort', filters.sort);
    }

    const container = document.getElementById('tab_logs_container');
    if (container && filters.search) {
        container.classList.add('data-loading');
    }

    fetch(window.location.pathname, {
        method: 'POST',
        body: formData,
        cache: 'no-cache'
    })
    .then(response => response.text())
    .then(respText => {
        let resp;
        try {
            resp = JSON.parse(respText);
        } catch (e) {
            try {
                let jsonStart = respText.indexOf('{');
                if (jsonStart !== -1) {
                    resp = JSON.parse(respText.substring(jsonStart));
                }
            } catch (e2) {
                console.error('Failed to parse JSON from response:', e2);
            }
        }

        if (typeof resp !== 'object' || resp === null) {
            console.error('Response is not an object:', respText);
            return;
        }

        console.log('load_logs success:', resp);
        if (resp.error) {
            console.error('Error loading logs:', resp.error);
            return;
        }

        window.app.logs_data = resp;
        
        if (resp.logs_html) {
            if (container) {
                container.innerHTML = resp.logs_html;
                container.classList.remove('data-loading');
            }
        }
    })
    .catch(error => {
        console.error('load_logs error:', error);
        if (container) {
            container.classList.remove('data-loading');
        }
    });
}

/**
 * Загружает доп. услуги пользователя через AJAX.
 * Обновляет вкладку "Доп услуги" на странице заявки.
 * 
 * @param {number} order_id - ID заявки.
 */
function load_insures(order_id) {
    if (!order_id) {
        console.error('load_insures: order_id is empty');
        return;
    }
    console.log('load_insures called for order_id:', order_id);

    const formData = new FormData();
    formData.append('action', 'get_insures');
    formData.append('order_id', order_id);

    fetch(window.location.pathname, {
        method: 'POST',
        body: formData,
        cache: 'no-cache'
    })
    .then(response => response.text())
    .then(respText => {
        let resp;
        try {
            resp = JSON.parse(respText);
        } catch (e) {
            try {
                let jsonStart = respText.indexOf('{');
                if (jsonStart !== -1) {
                    resp = JSON.parse(respText.substring(jsonStart));
                }
            } catch (e2) {
                console.error('Failed to parse JSON from response:', e2);
            }
        }

        if (typeof resp !== 'object' || resp === null) {
            console.error('Response is not an object:', respText);
            return;
        }

        console.log('load_insures success:', resp);
        if (resp.error) {
            console.error('Error loading insures:', resp.error);
            return;
        }

        window.app.insures_data = resp;
        
        if (resp.insures_html) {
            const container = document.getElementById('tab_insures_container');
            if (container) {
                container.innerHTML = resp.insures_html;
            }
        }
    })
    .catch(error => {
        console.error('load_insures error:', error);
    });
}

/**
 * Загружает переплаты по договору через AJAX.
 * Обновляет вкладку "Переплаты" на странице заявки.
 * 
 * @param {number} order_id - ID заявки.
 */
function load_overpayments(order_id) {
    if (!order_id) {
        console.error('load_overpayments: order_id is empty');
        return;
    }
    console.log('load_overpayments called for order_id:', order_id);

    const formData = new FormData();
    formData.append('action', 'get_overpayments');
    formData.append('order_id', order_id);

    fetch(window.location.pathname, {
        method: 'POST',
        body: formData,
        cache: 'no-cache'
    })
    .then(response => response.text())
    .then(respText => {
        let resp;
        try {
            resp = JSON.parse(respText);
        } catch (e) {
            try {
                let jsonStart = respText.indexOf('{');
                if (jsonStart !== -1) {
                    resp = JSON.parse(respText.substring(jsonStart));
                }
            } catch (e2) {
                console.error('Failed to parse JSON from response:', e2);
            }
        }

        if (typeof resp !== 'object' || resp === null) {
            console.error('Response is not an object:', respText);
            return;
        }

        console.log('load_overpayments success:', resp);
        if (resp.error) {
            console.error('Error loading overpayments:', resp.error);
            return;
        }

        window.app.overpayments_data = resp;
        
        if (resp.overpayments_html) {
            const container = document.getElementById('tab_overpayments_container');
            if (container) {
                container.innerHTML = resp.overpayments_html;
            }
        }
    })
    .catch(error => {
        console.error('load_overpayments error:', error);
    });
}

/**
 * Загружает данные скоринга для указанной заявки через AJAX.
 * Обновляет таблицы скоринга, блок скоринга на основной вкладке и другие связанные элементы.
 * 
 * @param {number} order_id - ID заявки.
 */
function load_scorings(order_id) {
    if (!order_id) {
        console.error('load_scorings: order_id is empty');
        return;
    }
    console.log('load_scorings called for order_id:', order_id);

    const formData = new FormData();
    formData.append('action', 'get_scorings');
    formData.append('order_id', order_id);
    formData.append('open_scorings', 1);

    fetch(window.location.pathname, {
        method: 'POST',
        body: formData,
        cache: 'no-cache'
    })
    .then(response => response.text())
    .then(respText => {
        console.log('load_scorings raw response received');
        let resp;

        try {
            // Пытаемся распарсить как JSON
            resp = JSON.parse(respText);
        } catch (e) {
            // Если не удалось, пробуем извлечь JSON (на случай PHP ошибок в начале)
            try {
                let jsonStart = respText.indexOf('{');
                if (jsonStart !== -1) {
                    resp = JSON.parse(respText.substring(jsonStart));
                }
            } catch (e2) {
                console.error('Failed to parse JSON from response:', e2);
            }
        }

        const container = document.getElementById('scorings_tables_container');

        if (typeof resp !== 'object' || resp === null) {
            console.error('Response is not an object:', respText);
            if (container) {
                container.innerHTML = '<div class="alert alert-danger">Некорректный ответ сервера при загрузке скоринга</div>';
            }
            // Очищаем лоадеры в случае ошибки
            update_tab_order_scorings({});
            return;
        }

        console.log('load_scorings success:', resp);
        if (resp.error) {
            console.error('Error loading scorings:', resp.error);
            if (container) {
                container.innerHTML = '<div class="alert alert-danger">Ошибка загрузки данных скоринга: ' + resp.error + '</div>';
            }
            // Очищаем лоадеры в случае ошибки
            update_tab_order_scorings({});
            return;
        }

        window.app.scorings_data = resp;

        // Обновляем таблицы на вкладке скоринга
        if (container) {
            container.innerHTML = resp.scorings_tables;
        }

        // Обновляем блок скоринга на основной вкладке
        if (resp.scorings_block) {
            document.querySelectorAll('.js-scorings-block').forEach(block => {
                // Если блок внутри scorings_tables_container, он уже обновился через resp.scorings_tables
                if (!block.closest('#scorings_tables_container')) {
                    const temp = document.createElement('div');
                    temp.innerHTML = resp.scorings_block;
                    const newContent = temp.querySelector('.js-scorings-block');
                    if (newContent) {
                        block.innerHTML = newContent.innerHTML;
                        // Также обновляем атрибуты и классы самого блока, если нужно
                        block.className = newContent.className;
                    } else {
                        // Если почему-то не нашли вложенный js-scorings-block, вставляем всё как есть
                        block.innerHTML = resp.scorings_block;
                        // Но если в resp.scorings_block тоже есть корневой js-scorings-block, 
                        // то мы получим вложенность. В сообщении пользователя видно, что там есть корневой.
                        // Попробуем найти его еще раз более агрессивно если первый поиск не удался.
                    }
                }
            });
        }

        // Обновляем индикатор необходимости обновления
        const scoringsBlocks = document.querySelectorAll('.js-scorings-block');
        scoringsBlocks.forEach(block => {
            if (resp.need_update_scorings) {
                block.classList.add('js-need-update');
            } else {
                block.classList.remove('js-need-update');
            }
        });

        // Инициализируем обработчики событий (кнопки "Запустить" и т.д.)
        if (typeof RunScoringsApp === 'function') {
            if (!window.runScoringsAppInstance) {
                window.runScoringsAppInstance = new RunScoringsApp();
            } else if (typeof window.runScoringsAppInstance._init_run_link === 'function') {
                window.runScoringsAppInstance._init_run_link();
            }
        }

        // Инициализируем следующий цикл обновления, если нужно
        if (typeof window._init_scorings_block === 'function') {
            window._init_scorings_block();
        }

        // Обновляем блоки на основной вкладке
        update_tab_order_scorings(resp);
    })
    .catch(error => {
        console.error('load_scorings error:', error);
        const container = document.getElementById('scorings_tables_container');
        if (container) {
            container.innerHTML = '<div class="alert alert-danger">Системная ошибка при загрузке скоринга: ' + error + '</div>';
        }
        // Очищаем лоадеры в случае сетевой ошибки
        update_tab_order_scorings({});
    });
}

/**
 * Обновляет отображение данных скоринга на основной вкладке заявки.
 * Заполняет блоки рекомендованной суммы, срока и сообщений от скоринга.
 * 
 * @param {Object} data - Данные, полученные от сервера.
 */
function update_tab_order_scorings(data) {
    // Рекомендуемая сумма
    if (data.scor_amount) {
        let html = '';
        let approve_amount_increased = window.app.order_config.approve_amount_increased;
        if (approve_amount_increased !== '' && approve_amount_increased !== 'undefined' && approve_amount_increased !== undefined) {
            let total = parseInt(data.scor_amount) + parseInt(approve_amount_increased);
            html = '<label class="label label-primary">' +
                   '<span title="Скориста: рекомендуемая сумма">' + data.scor_amount + ' +</span> ' +
                   '<span title="Добавлено согласно настройкам">' + approve_amount_increased + ' =</span> ' +
                   '<span title="Итого: рекомендуемая сумма">' + total + ' руб</span>' +
                   '</label>';
        } else {
            html = '<label class="label label-primary" title="Скориста: рекомендуемая сумма">' + data.scor_amount + ' руб</label>';
        }
        
        document.querySelectorAll('.js-scor_amount-block').forEach(el => el.innerHTML = html);

        if (approve_amount_increased !== '' && approve_amount_increased !== 'undefined' && approve_amount_increased !== undefined) {
            document.querySelectorAll('.js-scor_amount_increased-block').forEach(el => {
                el.innerHTML = '<small><i class="text-primary" title="Добавлено согласно настройкам">Рекомендуемая сумма увеличена согласно настройкам на ' + parseInt(approve_amount_increased) + ' руб.</i></small>';
            });
        }
    } else {
        document.querySelectorAll('.js-scor_amount-block').forEach(el => el.innerHTML = '');
    }

    // Сумма для инстолментов
    if (data.installment_scor_amount && window.app.order_config.manager_role !== 'verificator_minus') {
        document.querySelectorAll('.js-installment_scor_amount-block').forEach(el => {
            el.innerHTML = '<label class="label label-info" title="Скориста: рекомендуемая сумма для инстолментов">' + data.installment_scor_amount + ' руб</label>';
        });
        document.querySelectorAll('.js-installment-option').forEach(el => {
            el.style.display = 'block';
        });
    } else {
        document.querySelectorAll('.js-installment-option').forEach(el => {
            el.style.display = 'none';
        });
    }

    // Сообщение
    if (data.scor_message && window.app.order_config.manager_role !== 'verificator_minus') {
        document.querySelectorAll('.js-scor_message-block').forEach(el => {
            el.innerHTML = '<br /><small><i class="text-primary" title="Скориста">Скориста: ' + data.scor_message + '</i></small>';
        });
    }

    // Сообщение инстолмент
    if (data.installment_scor_message && window.app.order_config.manager_role !== 'verificator_minus') {
        document.querySelectorAll('.js-installment_scor_message-block').forEach(el => {
            el.innerHTML = '<br /><small><i class="text-primary" title="Скориста">Скориста Инстолмент: ' + data.installment_scor_message + '</i></small>';
        });
    }

    // Рекомендуемый срок
    if (data.scor_period) {
        let plural_day = getPlural(data.scor_period, 'день', 'дней', 'дня');
        document.querySelectorAll('.js-scor_period-block').forEach(el => {
            el.innerHTML = '<label class="label label-primary" title="Скориста: рекомендуемый срок">' + data.scor_period + ' ' + plural_day + '</label>';
        });
    } else {
        document.querySelectorAll('.js-scor_period-block').forEach(el => el.innerHTML = '');
    }
}

/**
 * Возвращает правильную форму множественного числа для числительного.
 * 
 * @param {number} number - Число.
 * @param {string} one - Форма для 1 (день).
 * @param {string} many - Форма для 5 (дней).
 * @param {string} other - Форма для 2 (дня).
 * @returns {string} - Подходящее слово.
 */
function getPlural(number, one, many, other) {
    number = Math.abs(number);
    number %= 100;
    if (number >= 5 && number <= 20) {
        return many;
    }
    number %= 10;
    if (number === 1) {
        return one;
    }
    if (number >= 2 && number <= 4) {
        return other;
    }
    return many;
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM ready, window.app.order_config:', window.app.order_config);
    if (window.app.order_config && window.app.order_config.order_id) {
        load_scorings(window.app.order_config.order_id);
        load_comments(window.app.order_config.order_id);
        
        // Загрузка кредитной истории при клике на вкладку (проверяем кэш при каждом клике)
        const historyTabLink = document.querySelector('a[href="#tab_history"]');
        if (historyTabLink) {
            historyTabLink.addEventListener('click', function() {
                if (!window.app.credit_history_data) {
                    load_credit_history(window.app.order_config.order_id);
                }
            });
        }
        
        // Загрузка доп. услуг при клике на вкладку (проверяем кэш при каждом клике)
        const insuresTabLink = document.querySelector('a[href="#insures"]');
        if (insuresTabLink) {
            insuresTabLink.addEventListener('click', function() {
                if (!window.app.insures_data) {
                    load_insures(window.app.order_config.order_id);
                }
            });
        }

        // Загрузка переплат при клике на вкладку (проверяем кэш при каждом клике)
        const overpaymentsTabLink = document.querySelector('a[href="#overpayments"]');
        if (overpaymentsTabLink) {
            overpaymentsTabLink.addEventListener('click', function() {
                if (!window.app.overpayments_data) {
                    load_overpayments(window.app.order_config.order_id);
                }
            });
        }

        // Загрузка логов при клике на вкладку (проверяем кэш при каждом клике)
        const logsTabLink = document.querySelector('a[href="#logs"]');
        if (logsTabLink) {
            logsTabLink.addEventListener('click', function() {
                if (!window.app.logs_data) {
                    load_logs(window.app.order_config.order_id);
                }
            });
        }

        // Фильтрация и сортировка логов
        let logTimer;
        document.addEventListener('keyup', function(e) {
            const target = e.target;
            if (target.closest('#tab_logs_container .jsgrid-filter-row input')) {
                clearTimeout(logTimer);
                logTimer = setTimeout(function() {
                    const order_id = window.app.order_config.order_id;
                    const _searches = {};
                    const container = document.getElementById('tab_logs_container');
                    if (container) {
                        const inputs = container.querySelectorAll('.jsgrid-filter-row input, .jsgrid-filter-row select');
                        inputs.forEach(function(el) {
                            if (el.value !== '' && el.getAttribute('name') !== 'sort') {
                                _searches[el.getAttribute('name')] = el.value;
                            }
                        });
                        const sortInput = container.querySelector('.jsgrid-filter-row [name=sort]');
                        const _sort = sortInput ? sortInput.value : '';
                        load_logs(order_id, {search: _searches, sort: _sort});
                    }
                }, 500);
            }
        });

        document.addEventListener('change', function(e) {
            const target = e.target;
            if (target.closest('#tab_logs_container .jsgrid-filter-row select')) {
                const order_id = window.app.order_config.order_id;
                const _searches = {};
                const container = document.getElementById('tab_logs_container');
                if (container) {
                    const inputs = container.querySelectorAll('.jsgrid-filter-row input, .jsgrid-filter-row select');
                    inputs.forEach(function(el) {
                        if (el.value !== '' && el.getAttribute('name') !== 'sort') {
                            _searches[el.getAttribute('name')] = el.value;
                        }
                    });
                    const sortInput = container.querySelector('.jsgrid-filter-row [name=sort]');
                    const _sort = sortInput ? sortInput.value : '';
                    load_logs(order_id, {search: _searches, sort: _sort});
                }
            }
        });

        document.addEventListener('click', function(e) {
            const target = e.target.closest('#tab_logs_container .jsgrid-header-sortable a');
            if (target) {
                e.preventDefault();
                const order_id = window.app.order_config.order_id;
                
                // Парсим URL для получения параметров сортировки
                const url = new URL(target.getAttribute('href'), window.location.origin);
                const _sort = url.searchParams.get('sort');
                
                const _searches = {};
                const container = document.getElementById('tab_logs_container');
                if (container) {
                    const inputs = container.querySelectorAll('.jsgrid-filter-row input, .jsgrid-filter-row select');
                    inputs.forEach(function(el) {
                        if (el.value !== '' && el.getAttribute('name') !== 'sort') {
                            _searches[el.getAttribute('name')] = el.value;
                        }
                    });
                    
                    load_logs(order_id, {search: _searches, sort: _sort});
                }
            }
        });
        
        // Постраничная навигация (если она есть/будет)
        document.addEventListener('click', function(e) {
            const target = e.target.closest('#tab_logs_container .jsgrid-pager a');
            if (target) {
                e.preventDefault();
                const order_id = window.app.order_config.order_id;
                
                const url = new URL(target.getAttribute('href'), window.location.origin);
                const _page = url.searchParams.get('page');
                
                const container = document.getElementById('tab_logs_container');
                if (container) {
                    const sortInput = container.querySelector('.jsgrid-filter-row [name=sort]');
                    const _sort = sortInput ? sortInput.value : '';
                    
                    const _searches = {};
                    const inputs = container.querySelectorAll('.jsgrid-filter-row input, .jsgrid-filter-row select');
                    inputs.forEach(function(el) {
                        if (el.value !== '' && el.getAttribute('name') !== 'sort') {
                            _searches[el.getAttribute('name')] = el.value;
                        }
                    });
                    
                    const filters = {search: _searches, sort: _sort};
                    if (_page) filters.page = _page;
                    
                    load_logs(order_id, filters);
                }
            }
        });
        
        // Подробности лога (аккордеон)
        document.addEventListener('click', function(e) {
            const target = e.target.closest('#tab_logs_container .js-open-order');
            if (target) {
                e.preventDefault();
                const row = target.closest('tr');
                const detailsRow = row.nextElementSibling;
                
                if (target.classList.contains('open')) {
                    target.classList.remove('open');
                    if (detailsRow && detailsRow.classList.contains('order-details')) {
                        detailsRow.style.display = 'none';
                    }
                } else {
                    const openButtons = document.querySelectorAll('#tab_logs_container .js-open-order.open');
                    openButtons.forEach(function(btn) {
                        btn.classList.remove('open');
                        const r = btn.closest('tr');
                        const dR = r.nextElementSibling;
                        if (dR && dR.classList.contains('order-details')) {
                            dR.style.display = 'none';
                        }
                    });
                    target.classList.add('open');
                    if (detailsRow && detailsRow.classList.contains('order-details')) {
                        detailsRow.style.display = 'table-row';
                    }
                }
            }
        });
        
        // Обновление комментариев по кнопке
        document.addEventListener('click', function(e) {
            const target = e.target.closest('.js-refresh-comments');
            if (target) {
                e.preventDefault();
                const tabComments = document.getElementById('tab_comments');
                if (tabComments) {
                    tabComments.classList.add('loading');
                }
                load_comments(window.app.order_config.order_id);
            }
        });

        // Загрузка документов при клике на вкладку (проверяем кэш при каждом клике)
        const documentsTabLink = document.querySelector('a[href="#tab_documents"]');
        if (documentsTabLink) {
            documentsTabLink.addEventListener('click', function() {
                if (!window.app.documents_data) {
                    load_documents(window.app.order_config.order_id);
                }
            });
        }

        // Загрузка документов минус при клике на вкладку (проверяем кэш при каждом клике)
        const documentsMinusTabLink = document.querySelector('a[href="#tab_documents_minus"]');
        if (documentsMinusTabLink) {
            documentsMinusTabLink.addEventListener('click', function() {
                if (!window.app.documents_data) {
                    load_documents(window.app.order_config.order_id);
                }
            });
        }

        // Комментарии загружаются сразу при загрузке страницы (см. выше load_comments)
        // При клике на вкладку ничего дополнительно не делаем, данные уже загружены

    } else {
        console.warn('window.app.order_config or order_id missing');
    }
});