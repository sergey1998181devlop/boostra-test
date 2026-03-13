/**
 * CB Request Detail Page - JavaScript Interactions
 *
 * Handles status updates, comments, subject changes, phone calls,
 * checkbox confirmation, response deadline, inline field editing,
 * client search, order search, and URL auto-linking.
 */

// ==================== Helper Functions ====================

/**
 * Wraps URLs found in text with clickable anchor tags.
 *
 * @param {string} text - Raw text that may contain URLs
 * @returns {string} HTML string with URLs wrapped in <a> tags
 */
const makeUrlsClickable = (text) => {
    const urlRegex = /((https?:\/\/|www\.)[^\s]+)/g;
    return text.replace(urlRegex, function (url) {
        let hyperlink = url;
        if (!hyperlink.match(/^https?:\/\//)) {
            hyperlink = 'http://' + hyperlink;
        }
        return '<a href="' + hyperlink + '" target="_blank" rel="noopener noreferrer">' + url + '</a>';
    });
};

/**
 * Formats a phone number for Voximplant calls.
 * Strips non-digit characters and replaces leading 8 with 7.
 *
 * @param {string|number} phone - Raw phone number
 * @returns {string} Cleaned phone number string
 */
const formatPhoneNumber = (phone) => {
    const cleanPhone = String(phone).replace(/[^0-9]/g, '');

    if (cleanPhone.charAt(0) === '8') {
        return '7' + cleanPhone.slice(1);
    }

    return cleanPhone;
};

/**
 * Creates an HTML string for a new comment element.
 *
 * @param {Object} comment - Comment data from server response
 * @param {string} comment.manager_name - Name of the comment author
 * @param {string} comment.created_at - Timestamp string
 * @param {string} comment.text - Comment text content
 * @returns {string} HTML string for the comment element
 */
const createCommentHtml = (comment) => {
    const initial = comment.manager_name
        ? comment.manager_name.charAt(0).toUpperCase()
        : '?';
    const safeName = $('<span>').text(comment.manager_name || '').html();
    const safeText = $('<div>').text(comment.text).html();

    return '<div class="comment-item mb-3">' +
        '<div class="media">' +
        '<div class="mr-3">' +
        '<div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white" ' +
        'style="width: 40px; height: 40px;">' +
        initial +
        '</div>' +
        '</div>' +
        '<div class="media-body">' +
        '<div class="d-flex justify-content-between align-items-center mb-1">' +
        '<span class="font-weight-bold">' + safeName + '</span>' +
        '<small class="text-muted"><i class="far fa-clock mr-1"></i>' + $('<span>').text(comment.created_at).html() + '</small>' +
        '</div>' +
        '<div class="comment-text p-3 bg-dark rounded">' +
        safeText +
        '</div>' +
        '</div>' +
        '</div>' +
        '</div>';
};

const getHistoryTitle = (action) => {
    if (action === 'creation') return 'Запрос создан';
    if (action === 'status_change') return 'Смена статуса';
    if (action === 'subject_change') return 'Смена темы';
    if (action === 'field_update') return 'Обновление поля';
    return action || 'Событие';
};

const formatHistoryDate = (rawDate) => {
    if (!rawDate) return '';
    const normalized = String(rawDate).replace(' ', 'T');
    const date = new Date(normalized);
    if (isNaN(date.getTime())) return rawDate;

    const dd = String(date.getDate()).padStart(2, '0');
    const mm = String(date.getMonth() + 1).padStart(2, '0');
    const yyyy = date.getFullYear();
    const hh = String(date.getHours()).padStart(2, '0');
    const ii = String(date.getMinutes()).padStart(2, '0');

    return dd + '.' + mm + '.' + yyyy + ' ' + hh + ':' + ii;
};

const createHistoryHtml = (history) => {
    if (!history || !history.length) {
        return '<li>' +
            '<div class="timeline-icon" style="background-color: #adb5bd;"></div>' +
            '<div class="timeline-content">' +
            '<p class="timeline-title mb-1">История отсутствует</p>' +
            '<p class="timeline-date mb-0">Нет записей в истории</p>' +
            '</div>' +
            '</li>';
    }

    const colors = ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8'];
    let html = '';

    for (let i = 0; i < history.length; i++) {
        const event = history[i] || {};
        const manager = $('<span>').text(event.manager_name || '').html();
        const details = $('<span>').text(event.details || '').html();
        const title = $('<span>').text(getHistoryTitle(event.action)).html();
        const createdAt = $('<span>').text(formatHistoryDate(event.created_at)).html();
        const color = colors[i % colors.length];

        html += '<li>' +
            '<span class="timeline-icon" style="background-color: ' + color + '"></span>' +
            '<div class="timeline-content">' +
            '<p class="timeline-title">' + title + '</p>';

        if (event.action !== 'creation' && details) {
            html += '<p class="timeline-text">' + details + '</p>';
        }

        html += '<p class="timeline-date">' +
            (manager ? manager + ' &mdash; ' : '') + createdAt +
            '</p>' +
            '</div>' +
            '</li>';
    }

    return html;
};

// ==================== Document Ready ====================

$(document).ready(function () {
    const requestId = $('#request_id').val();
    const $historyList = $('#request-history-list');
    const $historyCount = $('#request-history-count');

    const refreshRequestHistory = async () => {
        if (!$historyList.length || !requestId) {
            return;
        }

        try {
            const response = await $.ajax({
                url: '/cb-requests',
                type: 'POST',
                data: {
                    action: 'get_history',
                    request_id: requestId
                }
            });

            if (response && response.success) {
                const history = response.history || [];
                $historyList.html(createHistoryHtml(history));
                if ($historyCount.length) {
                    $historyCount.text(history.length);
                }
            }
        } catch (e) {}
    };

    // --------------------------------------------------
    // 1. Status Buttons
    // --------------------------------------------------
    $('.cb-status-btn').on('click', async function () {
        const $button = $(this);
        const statusField = $button.data('field');
        const originalHtml = $button.html();

        $button.prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin mr-1"></i>...');

        try {
            const response = await $.ajax({
                url: '/cb-requests',
                type: 'POST',
                data: {
                    action: 'update_status',
                    request_id: requestId,
                    status_field: statusField,
                    value: 1
                }
            });

            if (response.success) {
                // Update hidden input
                $('#' + statusField).val(1);

                $button.removeClass('btn-outline-primary btn-outline-success btn-outline-warning')
                    .addClass('btn-success')
                    .html(originalHtml)
                    .prop('disabled', true);

                Swal.fire({
                    type: 'success',
                    title: 'Сохранено',
                    text: 'Статус обновлён',
                    timer: 1500,
                    showConfirmButton: false
                });
                refreshRequestHistory();
            } else {
                throw new Error(response.message || 'Ошибка обновления статуса');
            }
        } catch (error) {
            $button.prop('disabled', false).html(originalHtml);

            Swal.fire({
                type: 'error',
                title: 'Ошибка',
                text: error.message || 'Не удалось выполнить запрос'
            });
        }
    });

    // --------------------------------------------------
    // 2. Comment Submission
    // --------------------------------------------------

    /**
     * Sends a comment for the given section.
     *
     * @param {string} section - One of: description, opr, okk, measures, lawyers
     */
    var commentSubmitting = false;

    const submitComment = async (section) => {
        if (commentSubmitting) return;

        const $textarea = $('#comment-text-' + section);
        const $btn = $textarea.closest('.card-body').find('.add-comment-btn[data-section="' + section + '"]');
        const text = $textarea.val().trim();

        if (!text) {
            Swal.fire({
                type: 'warning',
                title: 'Внимание',
                text: 'Введите текст комментария'
            });
            return;
        }

        commentSubmitting = true;
        var originalBtnHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Отправка...');
        $textarea.prop('disabled', true);

        try {
            const response = await $.ajax({
                url: '/cb-requests',
                type: 'POST',
                data: {
                    action: 'add_comment',
                    request_id: requestId,
                    section: section,
                    text: text
                }
            });

            if (response.success) {
                $textarea.val('');

                const $container = $('.comments-container[data-section="' + section + '"]');
                const $emptyMessage = $container.find('p:contains("Комментариев пока нет"), p:contains("Мероприятий пока нет")');

                if ($emptyMessage.length) {
                    $emptyMessage.closest('.text-center').remove();
                }

                const commentData = response.comment || {
                    manager_name: '',
                    created_at: new Date().toLocaleString('ru-RU'),
                    text: text
                };

                $(createCommentHtml(commentData)).appendTo($container);
                $container[0].scrollTop = $container[0].scrollHeight;

                // Update badge count
                var $badge = $container.closest('.card').find('.card-header .badge');
                if ($badge.length) {
                    var currentCount = parseInt($badge.text(), 10) || 0;
                    $badge.text(currentCount + 1);
                }
            } else {
                throw new Error(response.message || 'Ошибка добавления комментария');
            }
        } catch (error) {
            Swal.fire({
                type: 'error',
                title: 'Ошибка',
                text: error.message || 'Не удалось отправить комментарий'
            });
        } finally {
            commentSubmitting = false;
            $btn.prop('disabled', false).html(originalBtnHtml);
            $textarea.prop('disabled', false).focus();
        }
    };

    // Click handler for comment buttons
    $('.add-comment-btn').on('click', function () {
        const section = $(this).data('section');
        submitComment(section);
    });

    // Ctrl+Enter shortcut in comment textareas
    $('[id^="comment-text-"]').on('keydown', function (e) {
        if (e.ctrlKey && e.keyCode === 13) {
            e.preventDefault();
            const id = $(this).attr('id');
            const section = id.replace('comment-text-', '');
            submitComment(section);
        }
    });

    // --------------------------------------------------
    // 3. Subject Change
    // --------------------------------------------------
    var $subjectSelect = $('#subject-select');
    var previousSubjectVal = $subjectSelect.val();

    $subjectSelect.on('change', function () {
        var newSubjectId = $(this).val();
        if (!newSubjectId || newSubjectId === previousSubjectVal) return;

        $.ajax({
            url: '/cb-requests',
            method: 'POST',
            data: {
                action: 'change_subject',
                request_id: requestId,
                subject_id: newSubjectId
            },
            success: function (response) {
                if (response.success) {
                    previousSubjectVal = newSubjectId;
                    Swal.fire({
                        type: 'success',
                        title: 'Сохранено',
                        text: 'Тема изменена',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    refreshRequestHistory();
                } else {
                    $subjectSelect.val(previousSubjectVal);
                    Swal.fire({
                        type: 'error',
                        text: response.message || 'Ошибка смены темы'
                    });
                }
            },
            error: function () {
                $subjectSelect.val(previousSubjectVal);
                Swal.fire({
                    type: 'error',
                    text: 'Ошибка соединения с сервером'
                });
            }
        });
    });

    // --------------------------------------------------
    // 4. Vox Phone Call
    // --------------------------------------------------
    $(document).on('click', '.call', function () {
        try {
            const phone = $(this).data('phone');

            if (!phone) {
                throw new Error('Phone number not found');
            }

            if (!window.VoximplantKit) {
                throw new Error('VoximplantKit is not initialized');
            }

            const formattedPhone = formatPhoneNumber(phone);

            if (!formattedPhone || formattedPhone.length !== 11) {
                throw new Error('Invalid phone number format');
            }

            const $button = $(this);
            const originalHtml = $button.html();
            $button.prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin mr-2"></i>Calling...');

            window.VoximplantKit.Widget.maximize();

            try {
                window.VoximplantKit.App.call(formattedPhone);
            } catch (callError) {
                throw new Error('Call error: ' + callError.message);
            } finally {
                $button.prop('disabled', false).html(originalHtml);
            }
        } catch (error) {
            Swal.fire({
                text: error.message || 'Unknown error occurred',
                type: 'error',
                timer: 5000
            });

            console.error('Call error: ', error);
        }
    });

    // Tooltip for call buttons
    $('.call').attr('title', 'Позвонить клиенту через Vox');

    // --------------------------------------------------
    // 5. Checkbox Confirmed (Да/Нет)
    // --------------------------------------------------
    $('#checkbox-confirmed-group').on('click', 'button', function () {
        var val = $(this).data('value');
        var currentVal = parseInt($('#checkbox-confirmed').val(), 10);
        if (val === currentVal) return;

        var $group = $('#checkbox-confirmed-group');

        $group.find('button').removeClass('btn-success btn-danger').addClass('btn-outline-secondary');
        if (val == 1) {
            $(this).removeClass('btn-outline-secondary').addClass('btn-success');
        } else {
            $(this).removeClass('btn-outline-secondary').addClass('btn-danger');
        }
        $('#checkbox-confirmed').val(val);

        $.ajax({
            url: '/cb-requests',
            type: 'POST',
            data: {
                action: 'update_field',
                id: requestId,
                field: 'opr_contacted_client',
                value: val
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        type: 'success',
                        title: 'Сохранено',
                        timer: 1000,
                        showConfirmButton: false
                    });
                }
            },
            error: function () {
                Swal.fire({
                    type: 'error',
                    text: 'Ошибка сохранения'
                });
            }
        });
    });

    // --------------------------------------------------
    // 6. Response Deadline Datepicker
    // --------------------------------------------------
    const $responseDeadline = $('#response-deadline-input');

    if ($responseDeadline.length) {
        let lastSavedDeadline = $responseDeadline.val();

        const saveResponseDeadline = function () {
            const deadlineValue = $(this).val();
            if (deadlineValue === lastSavedDeadline) {
                return;
            }

            $.ajax({
                url: '/cb-requests',
                type: 'POST',
                data: {
                    action: 'update_field',
                    id: requestId,
                    field: 'response_deadline',
                    value: deadlineValue
                },
                success: function (response) {
                    if (response.success) {
                        lastSavedDeadline = deadlineValue;
                        Swal.fire({
                            type: 'success',
                            title: 'Saved',
                            text: 'Срок ответа обновлен',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            type: 'error',
                            text: response.message || 'Error updating deadline'
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        type: 'error',
                        text: 'Server request failed'
                    });
                }
            });
        };

        $responseDeadline.on('blur', saveResponseDeadline);
        $responseDeadline.on('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                saveResponseDeadline.call(this);
                $(this).blur();
            }
        });
    }

    // --------------------------------------------------
    // 7. Inline Field Save (Client & Loan)
    // --------------------------------------------------

    /**
     * Saves a single field value via AJAX.
     *
     * @param {string} field - Field name (must be in allowed list on backend)
     * @param {string|number} value - New value
     * @param {function} [onSuccess] - Optional callback on success
     */
    const saveField = (field, value, onSuccess, extraData, refreshHistoryAfterSave) => {
        var data = {
            action: 'update_field',
            id: requestId,
            field: field,
            value: value
        };
        if (extraData) {
            $.extend(data, extraData);
        }
        $.ajax({
            url: '/cb-requests',
            type: 'POST',
            data: data,
            success: function (response) {
                if (response.success) {
                    if (onSuccess) {
                        onSuccess(response);
                    }
                    if (refreshHistoryAfterSave) {
                        refreshRequestHistory();
                    }
                } else {
                    Swal.fire({
                        type: 'error',
                        text: response.message || 'Ошибка сохранения поля'
                    });
                }
            },
            error: function () {
                Swal.fire({
                    type: 'error',
                    text: 'Ошибка соединения с сервером'
                });
            }
        });
    };

    // Client FIO — save on blur
    $('#client-fio-input').on('blur', function () {
        var val = $(this).val().trim();
        saveField('client_fio', val);
    });

    // Client birth date — save on blur
    $('#client-birth-input').on('blur', function () {
        var val = $(this).val().trim();
        saveField('client_birth_date', val);
    });

    // Client email — save on blur
    $('#client-email-input').on('blur', function () {
        var val = $(this).val().trim();
        saveField('client_email', val);
    });

    // --------------------------------------------------
    // Phones rendering helper
    // --------------------------------------------------
    function renderPhoneRow(phone, label, badgeClass) {
        return '<div class="d-flex align-items-center mb-2 phone-row">' +
            '<span class="badge badge-' + badgeClass + ' mr-2">' + label + '</span>' +
            '<span class="phone-number mr-auto">' + $('<span>').text(phone).html() + '</span>' +
            '<button class="btn btn-xs btn-success call mr-1" data-phone="' + $('<span>').text(phone).html() + '" title="Позвонить"><i class="fas fa-phone"></i></button>' +
            '<a href="https://t.me/' + encodeURIComponent(phone) + '" target="_blank" class="btn btn-xs btn-primary mr-1" title="Telegram"><i class="fab fa-telegram"></i></a>' +
            '<a href="https://wa.me/' + encodeURIComponent(phone) + '" target="_blank" class="btn btn-xs btn-success" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>' +
            '</div>';
    }

    function renderPhones(mainPhone, additionalPhones) {
        var $container = $('#client-phones-container');
        $container.empty();

        if (mainPhone) {
            $container.append(renderPhoneRow(mainPhone, 'Основной', 'success'));
        }
        if (additionalPhones && additionalPhones.length) {
            for (var i = 0; i < additionalPhones.length; i++) {
                $container.append(renderPhoneRow(additionalPhones[i].phone, 'Доп.', 'secondary'));
            }
        }
        if (!mainPhone && (!additionalPhones || !additionalPhones.length)) {
            $container.append('<p class="text-muted mb-0" id="no-phones-msg">Телефоны не найдены. Привяжите клиента.</p>');
        }
    }

    function loadClientPhones(userId) {
        $.post('/cb-requests', { action: 'get_client_phones', user_id: userId }, function (resp) {
            if (resp.success) {
                renderPhones(resp.main_phone, resp.phones);
            }
        });
    }

    // Order number — save on blur
    $('#order-number-input').on('blur', function () {
        var val = $(this).val().trim();
        saveField('order_number', val);
    });

    // --------------------------------------------------
    // 8. Client Search
    // --------------------------------------------------
    $('#search-client-btn').on('click', function () {
        var $btn = $(this);
        var fio = $('#client-fio-input').val().trim();
        var birthDate = $('#client-birth-input').val().trim();
        var email = $('#client-email-input').val().trim();

        if (!fio && !birthDate && !email) {
            Swal.fire({
                type: 'warning',
                title: 'Внимание',
                text: 'Укажите ФИО, дату рождения или email для поиска'
            });
            return;
        }

        var originalHtml = $btn.html();
        $btn.prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin mr-2"></i>Поиск...');

        $.ajax({
            url: '/cb-requests',
            type: 'POST',
            data: {
                action: 'search_client',
                fio: fio,
                birth_date: birthDate,
                email: email
            },
            success: function (response) {
                $btn.prop('disabled', false).html(originalHtml);
                var $result = $('#client-search-result');
                var $info = $('#client-found-info');

                if (response.success && response.users && response.users.length > 0) {
                    var html = '<div class="alert alert-info py-2 mb-0">' +
                        '<strong>Найдено ' + response.users.length + ' клиентов. Выберите нужного:</strong></div>';

                    html += '<div class="list-group mt-2" style="max-height: 200px; overflow-y: auto;">';
                    for (var i = 0; i < response.users.length; i++) {
                        var u = response.users[i];
                        html += '<a href="#" class="list-group-item list-group-item-action py-2 select-client-result" ' +
                            'data-user-id="' + u.id + '" ' +
                            'data-user-name="' + $('<span>').text(u.full_name).html() + '" ' +
                            'data-user-phone="' + $('<span>').text(u.phone_mobile || '').html() + '" ' +
                            'data-user-email="' + $('<span>').text(u.email || '').html() + '" ' +
                            'data-user-birth="' + $('<span>').text(u.birth || '').html() + '" ' +
                            'style="background: #2a2f3d; color: #fff; border-color: rgba(255,255,255,0.1);">' +
                            '<strong>' + $('<span>').text(u.full_name).html() + '</strong>' +
                            (u.birth ? ' <small class="text-muted">(' + $('<span>').text(u.birth).html() + ')</small>' : '') +
                            (u.phone_mobile ? '<br><small class="text-muted"><i class="fas fa-phone mr-1"></i>' + $('<span>').text(u.phone_mobile).html() + '</small>' : '') +
                            '</a>';
                    }
                    html += '</div>';

                    $info.html(html);
                    $result.show();
                } else {
                    $info.html('<div class="alert alert-warning py-2 mb-0">' +
                        '<i class="fas fa-exclamation-triangle mr-2"></i>Клиент не найден</div>');
                    $result.show();
                }
            },
            error: function () {
                $btn.prop('disabled', false).html(originalHtml);
                Swal.fire({
                    type: 'error',
                    text: 'Ошибка соединения с сервером'
                });
            }
        });
    });

    // Click on a found client from search results
    $(document).on('click', '.select-client-result', function (e) {
        e.preventDefault();
        var user = {
            id: $(this).data('user-id'),
            full_name: $(this).data('user-name'),
            phone_mobile: $(this).data('user-phone'),
            email: $(this).data('user-email'),
            birth: $(this).data('user-birth')
        };
        applyClientData(user);
    });

    /**
     * Applies found client data to the form fields and saves client_id.
     * Switches from edit mode to view mode.
     *
     * @param {Object} user - User data from search results
     */
    function applyClientData(user) {
        // Set client_id
        $('#client_id').val(user.id);
        saveField('client_id', user.id, null, null, true);

        // Populate view mode fields
        var safeName = $('<span>').text(user.full_name || '').html();
        $('#client-view-fio-link')
            .attr('href', '/client/' + user.id)
            .html(safeName);
        $('#client-view-birth').text(user.birth || '—');
        $('#client-view-email').text(user.email || '—');

        // Switch to view mode
        $('#client-edit-mode').hide();
        $('#client-view-mode').show();

        // Reset search results
        $('#client-search-result').hide();
        $('#client-found-info').html('');

        // Load phones for the selected client
        loadClientPhones(user.id);

        Swal.fire({
            type: 'success',
            title: 'Клиент привязан',
            text: user.full_name,
            timer: 1500,
            showConfirmButton: false
        });
    }

    // "Изменить клиента" button — switch to edit mode with current data
    $('#change-client-btn').on('click', function () {
        // Pre-fill inputs with current view data
        var currentFio = $('#client-view-fio-link').text().trim();
        var currentBirth = $('#client-view-birth').text().trim();
        var currentEmail = $('#client-view-email').text().trim();

        $('#client-fio-input').val(currentFio !== '—' ? currentFio : '');
        $('#client-birth-input').val(currentBirth !== '—' ? currentBirth : '');
        $('#client-email-input').val(currentEmail !== '—' ? currentEmail : '');

        $('#client-view-mode').hide();
        $('#client-edit-mode').show();
        $('#cancel-client-edit-btn').show();
        $('#client-fio-input').focus();
    });

    // "Отменить" button — return to view mode without changes
    $('#cancel-client-edit-btn').on('click', function () {
        $('#client-edit-mode').hide();
        $('#client-view-mode').show();
        $('#cancel-client-edit-btn').hide();
        $('#client-search-result').hide();
        $('#client-found-info').html('');
    });

    // --------------------------------------------------
    // 9. Order Search
    // --------------------------------------------------

    function clearOrderSearchResult() {
        $('#order-search-result').hide();
        $('#order-found-info').html('');
    }

    function applyOrder(order) {
        $('#order_id').val(order.order_id);
        saveField('order_id', order.order_id, null, { order_number: order.contract_number || '' }, true);

        var displayNumber = order.contract_number || $('#order-number-input').val().trim();
        $('#order-view-number-link')
            .attr('href', '/order/' + order.order_id)
            .text(displayNumber);
        $('#order-view-amount').text(order.amount ? order.amount + ' р.' : '—');
        $('#order-view-date').text(order.date || '—');
        $('#order-view-overdue-days').text(order.overdue_days != null ? order.overdue_days : '—');
        $('#order-view-overdue-at-creation').text(order.overdue_days != null ? order.overdue_days : '—');
        $('#order-view-manager').text(order.manager_name || 'Не указан');

        if (order.sale_info === 'Договор продан') {
            $('#order-view-buyer').text(order.buyer_name || '—');
            $('#order-view-buyer-phone').text(order.buyer_phone || '—');
            $('#order-buyer-info').show();
        } else {
            $('#order-buyer-info').hide();
        }

        clearOrderSearchResult();
        $('#order-edit-mode').hide();
        $('#order-view-mode').show();
    }

    $('#search-order-btn').on('click', function () {
        var $btn = $(this);
        var contractNumber = $('#order-number-input').val().trim();
        var originalHtml = $btn.html();

        $btn.prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin mr-2"></i>Поиск...');

        $.ajax({
            url: '/cb-requests',
            type: 'POST',
            data: {
                action: 'search_order',
                request_id: requestId,
                contract_number: contractNumber
            },
            success: function (response) {
                $btn.prop('disabled', false).html(originalHtml);

                // Единичный результат по номеру контракта
                if (response.success && response.order) {
                    applyOrder(response.order);
                    Swal.fire({
                        type: 'success',
                        title: 'Займ найден',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    return;
                }

                // Список займов клиента
                if (response.success && response.orders && response.orders.length > 0) {
                    var html = '<div class="alert alert-info py-2 mb-0">' +
                        '<strong>Найдено займов: ' + response.orders.length + '</strong>' +
                        '</div>';
                    html += '<div class="list-group mt-2" style="max-height: 240px; overflow-y: auto;">';
                    for (var i = 0; i < response.orders.length; i++) {
                        var o = response.orders[i];
                        var number = o.contract_number || ('ID ' + o.order_id);
                        var amount = o.amount ? (o.amount + ' р.') : '—';
                        html += '<a href="#" class="list-group-item list-group-item-action py-2 select-order-result" ' +
                            'data-order=\'' + $('<span>').text(JSON.stringify(o)).html() + '\' ' +
                            'style="background: #2a2f3d; color: #fff; border-color: rgba(255,255,255,0.1);">' +
                            '<strong>' + $('<span>').text(number).html() + '</strong>' +
                            '<br><small class="text-muted">Сумма: ' + $('<span>').text(amount).html() + '</small>' +
                            '</a>';
                    }
                    html += '</div>';
                    $('#order-found-info').html(html);
                    $('#order-search-result').show();
                    return;
                }

                clearOrderSearchResult();
                Swal.fire({
                    type: 'warning',
                    title: 'Не найдено',
                    text: contractNumber
                        ? ('Займ с номером "' + contractNumber + '" не найден')
                        : 'Займы клиента не найдены'
                });
            },
            error: function () {
                $btn.prop('disabled', false).html(originalHtml);
                clearOrderSearchResult();
                Swal.fire({
                    type: 'error',
                    text: 'Ошибка соединения с сервером'
                });
            }
        });
    });

    // "Изменить займ" button — switch to edit mode
    $('#change-order-btn').on('click', function () {
        $('#order-number-input').val('');

        $('#order-view-mode').hide();
        $('#order-edit-mode').show();
        $('#cancel-order-edit-btn').show();
        $('#order-number-input').focus();
    });

    // "Отменить" (order) — return to view mode without changes
    $('#cancel-order-edit-btn').on('click', function () {
        $('#order-edit-mode').hide();
        $('#order-view-mode').show();
        $('#cancel-order-edit-btn').hide();
        clearOrderSearchResult();
    });

    // Click on a found order from search results
    $(document).on('click', '.select-order-result', function (e) {
        e.preventDefault();
        var raw = $(this).attr('data-order');
        if (!raw) return;
        try {
            var order = JSON.parse(raw);
            applyOrder(order);
            Swal.fire({
                type: 'success',
                title: 'Займ привязан',
                timer: 1500,
                showConfirmButton: false
            });
        } catch (e) {}
    });

    // --------------------------------------------------
    // 10. Auto-link URLs in Existing Comments
    // --------------------------------------------------
    $('.comment-text').each(function () {
        const $el = $(this);
        const rawHtml = $el.html();
        if (rawHtml.indexOf('<a ') === -1) {
            $el.html(makeUrlsClickable(rawHtml));
        }
    });
});
