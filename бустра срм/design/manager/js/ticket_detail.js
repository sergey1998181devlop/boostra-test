let disputedComplaintAttempted = false;

const TimerManager = (function () {
    let intervalId = null;
    const $timer = $('#work-timer');
    if (!$timer.length) {
        return {
            start: () => null,
            stop: () => null
        };
    }

    // Получаем уже накопленное время (в секундах) и время начала открытого интервала
    const closedTime = parseInt($timer.data('closed-time')) || 0;
    const openStart = parseInt($timer.data('open-start'));

    function formatTime(totalSeconds) {
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;
        return [hours, minutes, seconds]
            .map(unit => String(unit).padStart(2, '0'))
            .join(':');
    }

    function updateDisplay() {
        const now = Math.floor(Date.now() / 1000);
        let currentInterval = 0;
        if (openStart) {
            currentInterval = now - openStart;
        }
        const totalTime = closedTime + currentInterval;
        $timer.text(formatTime(totalTime));
    }

    return {
        start: function () {
            updateDisplay();
            intervalId = setInterval(updateDisplay, 1000);
        },
        stop: function () {
            if (intervalId) {
                clearInterval(intervalId);
                intervalId = null;
            }
        }
    };
})();

const handleFeedbackButtonClick = (feedbackReceived) => {
    const ticketId = $('#feedback-ticket-id').val();
    const action = $('#feedback-action').val();
    const statusId = action === 'resolve' ? 4 : 2;
    const notifyUser = action === 'resolve' && $('#notifyUserCheckbox').is(':checked');

    $('#feedbackModal').modal('hide');

    updateTicketStatus(ticketId, statusId, feedbackReceived, notifyUser);
};

const handlePauseNotificationClick = (notifyUser) => {
    const ticketId = $('#pause-ticket-id').val();
    $('#pauseNotificationModal').modal('hide');
    updateTicketStatus(ticketId, 3, null, notifyUser);
};

// Функция обновления статуса тикета
const updateTicketStatus = async (ticketId, statusId, feedbackReceived = null, notifyUser = null) => {
    try {
        const data = {
            ticket_id: ticketId,
            status_id: statusId,
            action: 'update_status'
        };

        if (feedbackReceived !== null) {
            data.feedback_received = feedbackReceived;
        }

        if (notifyUser !== null) {
            data.notify_user = notifyUser ? 1 : 0;
        }

        const response = await $.ajax({
            url: '/tickets/update-status',
            type: 'POST',
            data: data
        });

        if (response.success) {
            TimerManager.stop();

            await Swal.fire({
                type: 'success',
                title: 'Успешно',
                text: response.message,
                timer: 1500
            });
            location.reload();
        } else {
            console.log('error', response)
            throw new Error(response.message || 'Ошибка при обновлении статуса');
        }
    } catch (error) {
        Swal.fire({
            type: 'error',
            title: 'Ошибка',
            text: error.message || 'Ошибка при отправке запроса на сервер'
        });
    }
};

const makeLinksClickable = (text) => {
    const urlRegex = /((https?:\/\/|www\.)[^\s]+)/g;
    return text.replace(urlRegex, function (url) {
        let hyperlink = url;
        if (!hyperlink.match(/^https?:\/\//)) {
            hyperlink = 'http://' + hyperlink;
        }
        return '<a href="' + hyperlink + '" target="_blank" rel="noopener noreferrer">' + url + '</a>';
    });
};

// Функция добавления комментария
const addComment = async (ticketId, commentText) => {
    try {
        const response = await $.ajax({
            url: '/tickets',
            type: 'POST',
            data: {
                ticket_id: ticketId,
                text: commentText,
                action: 'add_comment'
            }
        });

        if (response.success) {
            $('#comment-text').val('');

            const newComment = '<div class="comment-item mb-3" data-created-at="' + response.comment.created_at + '">' +
                '<div class="media">' +
                '<div class="media-body">' +
                '<div class="d-flex justify-content-between align-items-center mb-1">' +
                '<small class="text-muted">' +
                '<i class="far fa-clock mr-1"></i>' + response.comment.created_at +
                '</small>' +
                '</div>' +
                '<div class="p-3 bg-dark rounded">' +
                makeLinksClickable(response.comment.text) +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>';

            const commentsContainer = $('#comments');
            const emptyMessage = commentsContainer.find('p:contains("Комментариев пока нет")');

            if (emptyMessage.length) {
                emptyMessage.remove();
            }

            $(newComment).prependTo(commentsContainer);

            if (disputedComplaintAttempted && $('#btn-disputed-complaint').length) {
                disputedComplaintAttempted = false;

                setTimeout(() => {
                    Swal.fire({
                        type: 'info',
                        title: 'Комментарий добавлен',
                        text: 'Теперь вы можете отметить тикет как спорную жалобу',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    $('#btn-disputed-complaint').addClass('blink-animation');
                    setTimeout(() => {
                        $('#btn-disputed-complaint').removeClass('blink-animation');
                    }, 3000);
                }, 500);
            }

            if ($('#btn-disputed-complaint').length && $('#btn-disputed-complaint').prop('disabled')) {
                const hasComment = checkRecentCommentForDispute();
                if (hasComment) {
                    $('#comment-text').removeClass('comment-required-highlight');
                }
            }

            await Swal.fire({
                type: 'success',
                title: 'Комментарий добавлен',
                timer: 1000,
                showConfirmButton: false
            });
        } else {
            throw new Error(response.error || 'Ошибка при добавлении комментария');
        }
    } catch (error) {
        Swal.fire({
            type: 'error',
            title: 'Ошибка',
            text: error.message
        });
    }
};

// Управление аудиоплеером
const handleAudioPlayer = (playerId) => {
    const player = $(playerId);
    player.toggleClass('d-none');

    $('.audio-player').not(playerId).addClass('d-none')
        .find('audio').each(function () {
        this.pause();
        this.currentTime = 0;
    });
};

// Анимация карточек при скролле
const revealOnScroll = () => {
    const windowHeight = $(window).height();
    const scrollTop = $(window).scrollTop();
    const visibilityOffset = 150;

    $('.call-item').each(function () {
        const elementTop = $(this).offset().top;

        if (elementTop < (windowHeight + scrollTop) - visibilityOffset) {
            $(this).addClass('show');
        }
    });
};

const formatPhoneNumber = (phone) => {
    // Удаляем все символы, кроме цифр
    const cleanPhone = String(phone).replace(/[^0-9]/g, '');

    // Если первая цифра 8, заменяем на 7
    if (cleanPhone.charAt(0) === '8') {
        return '7' + cleanPhone.slice(1);
    }

    return cleanPhone;
};

const checkRecentCommentForDispute = () => {
    const comments = $('#comments .comment-item');
    if (comments.length === 0) return false;

    const lastComment = comments.first();
    const commentText = lastComment.find('.comment-text, .bg-dark.rounded').text().trim();

    if (commentText.length < 20) {
        return false;
    }

    const createdAt = lastComment.data('created-at');
    if (createdAt) {
        const commentTime = new Date(createdAt);
        const now = new Date();
        const diffMinutes = (now - commentTime) / (1000 * 60);
        return diffMinutes <= 5;
    }

    return false;
};

// Обработчик кнопки "Спорная жалоба"
$('#btn-disputed-complaint').on('click', async function(e) {
    e.preventDefault();

    const $button = $(this);
    const ticketId = $button.data('ticket-id');

    disputedComplaintAttempted = true;

    // Проверяем наличие недавнего комментария
    const hasRecentComment = checkRecentCommentForDispute();

    if (!hasRecentComment) {
        // Подсвечиваем поле комментария
        const $commentTextarea = $('#comment-text');
        $commentTextarea.addClass('comment-required-highlight blink-animation');

        // Убираем подсветку через 3 секунды
        setTimeout(() => {
            $commentTextarea.removeClass('comment-required-highlight blink-animation');
        }, 3000);

        // Показываем предупреждение
        await Swal.fire({
            type: 'warning',
            title: 'Требуется комментарий',
            text: 'Необходимо добавить комментарий с обоснованием решения о признании тикета спорным (минимум 20 символов).',
            confirmButtonText: 'Понятно',
            confirmButtonColor: '#f39c12'
        });

        // Фокусируемся на поле комментария
        $commentTextarea.focus();
        return;
    }

    // Подтверждение действия
    const result = await Swal.fire({
        type: 'question',
        title: 'Подтверждение',
        text: 'Вы уверены, что хотите отметить тикет как спорную жалобу? Исполнитель будет автоматически изменен.',
        showCancelButton: true,
        confirmButtonText: 'Да, отметить',
        cancelButtonText: 'Отмена',
        confirmButtonColor: '#f39c12',
        cancelButtonColor: '#6c757d'
    });

    if (!result.value) return;

    // Блокируем кнопку и показываем индикатор загрузки
    $button.prop('disabled', true)
        .html('<i class="fas fa-spinner fa-spin mr-2"></i>Обработка...');

    try {
        // Отправляем запрос на сервер
        const response = await $.ajax({
            url: window.location.href,
            type: 'POST',
            data: {
                action: 'disputed_complaint',
                ticket_id: ticketId
            },
            dataType: 'json'
        });

        if (response.success) {
            await Swal.fire({
                type: 'success',
                title: 'Успешно',
                text: response.message,
                timer: 2000,
                showConfirmButton: false
            });

            // Перезагружаем страницу для обновления данных
            window.location.reload();
        } else {
            throw new Error(response.message || 'Ошибка при обработке запроса');
        }
    } catch (error) {
        // Возвращаем кнопку в исходное состояние
        $button.prop('disabled', false)
            .html('<i class="fas fa-exclamation-triangle mr-2"></i>Спорная жалоба');

        await Swal.fire({
            type: 'error',
            title: 'Ошибка',
            text: error.message || 'Произошла ошибка при обработке запроса'
        });
    }
});

// Обработчик кнопки "Вернуть в работу" (для руководства ОПР)
$('#btn-return-to-new').on('click', async function(e) {
    e.preventDefault();

    const $button = $(this);
    const ticketId = $button.data('ticket-id');

    // Подтверждение действия
    const result = await Swal.fire({
        type: 'question',
        title: 'Подтверждение',
        text: 'Вернуть тикет в статус "Новый"? Спорная жалоба будет признана обоснованной.',
        showCancelButton: true,
        confirmButtonText: 'Да, вернуть',
        cancelButtonText: 'Отмена',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d'
    });

    if (!result.value) return;

    $button.prop('disabled', true)
        .html('<i class="fas fa-spinner fa-spin mr-2"></i>Обработка...');

    try {
        const response = await $.ajax({
            url: window.location.href,
            type: 'POST',
            data: {
                action: 'return_disputed_to_new',
                ticket_id: ticketId
            },
            dataType: 'json'
        });

        if (response.success) {
            await Swal.fire({
                type: 'success',
                title: 'Успешно',
                text: response.message,
                timer: 2000,
                showConfirmButton: false
            });

            window.location.reload();
        } else {
            throw new Error(response.message || 'Ошибка при обработке запроса');
        }
    } catch (error) {
        $button.prop('disabled', false)
            .html('<i class="fas fa-undo mr-2"></i>Вернуть в работу');

        await Swal.fire({
            type: 'error',
            title: 'Ошибка',
            text: error.message || 'Произошла ошибка при обработке запроса'
        });
    }
});

// Инициализация при загрузке документа
$(document).ready(() => {
    TimerManager.start();

    // Инициализация переменных
    const $currentManager = $("#current-manager");
    const $changeBtn = $("#change-manager-btn");
    const $selectContainer = $("#manager-select-container");
    const $select = $("#manager-select");

    // Обработчики статусов тикета
    $('.ticket-action').on('click', (e) => {
        const button = $(e.currentTarget);
        const action = button.data('action');
        const ticketId = button.data('ticket-id');

        const statusMap = {
            accept: 5,
            pause: 3,
            unpause: 5,
            resolve: 4,
            unresolve: 2,
            request_details: 7
        };

        if (action === 'resolve' || action === 'unresolve') {
            // Открываем модальное окно для получения информации об обратной связи
            $('#feedback-ticket-id').val(ticketId);
            $('#feedback-action').val(action);
            
            // Показываем блок уведомления только для статуса "Урегулирован"
            if (action === 'resolve') {
                $('#resolvedNotificationBlock').show();
            } else {
                $('#resolvedNotificationBlock').hide();
            }
            
            $('#feedbackModal').modal('show');
        } else if (action === 'pause') {
            // Открываем модальное окно для выбора отправки уведомления
            $('#pause-ticket-id').val(ticketId);
            $('#pauseNotificationModal').modal('show');
        } else {
            if (statusMap[action] !== undefined) {
                updateTicketStatus(ticketId, statusMap[action])
                    .then(response => {
                        if (response.success && action === 'request_details') {
                            button.hide();
                        }
                    });
            }
        }
    });

    // Обработчики комментариев
    $('#send-comment').on('click', () => {
        const commentText = $('#comment-text').val().trim();
        const ticketId = $('#comment-form').data('ticket-id');

        if (!commentText) {
            Swal.fire({
                type: 'warning',
                title: 'Внимание',
                text: 'Введите текст комментария'
            });
            return;
        }

        addComment(ticketId, commentText);
    });

    $('#comment-text').on('keydown', (e) => {
        if (e.ctrlKey && e.keyCode === 13) {
            e.preventDefault();
            $('#send-comment').click();
        }
    });

    // Обработчик изменения состояния чекбокса
    $('#notifyUserCheckbox').on('change', e => e.stopPropagation());

    // Обработчики аудио
    $('.play-record').on('click', (e) => {
        handleAudioPlayer('#player_' + $(e.currentTarget).data('call-id'));
    });

    // Звонок через Voximplant
    $('.call').on('click', function (e) {
        try {
            const phone = $(this).data('phone');

            if (!phone) {
                throw new Error('Номер телефона не найден');
            }

            // Проверяем наличие VoximplantKit
            if (!window.VoximplantKit) {
                throw new Error('VoximplantKit не инициализирован');
            }

            const formattedPhone = formatPhoneNumber(phone);

            if (!formattedPhone || formattedPhone.length !== 11) {
                throw new Error('Некорректный формат номера телефона');
            }

            // Добавляем индикатор загрузки на кнопку
            const $button = $(this);
            const originalText = $button.html();
            $button.prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin mr-2"></i>Звоним...');

            window.VoximplantKit.Widget.maximize();

            try {
                window.VoximplantKit.App.call(formattedPhone);
            } catch (callError) {
                throw new Error('Ошибка при выполнении звонка: ' + callError.message);
            } finally {
                $button.prop('disabled', false).html(originalText);
            }
        } catch (error) {
            const errorMessage = error.message || 'Произошла неизвестная ошибка';

            Swal.fire({
                text: errorMessage,
                type: 'error',
                timer: 5000
            });

            console.error('Ошибка при выполнении звонка: ', error);
        }
    });

    $(window).on('scroll', revealOnScroll);
    revealOnScroll();

    // Инициализация Select2
    $select.select2({
        width: '400px',
        placeholder: 'Начните вводить имя...',
        minimumInputLength: 2,
        ajax: {
            url: '/tickets/get-managers',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    term: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data.map(function (item) {
                        return {
                            id: item.id,
                            text: item.name
                        };
                    })
                };
            }
        }
    });

    // Обработка нажатия на кнопку смены
    $changeBtn.click(function () {
        $currentManager.hide();
        $changeBtn.hide();
        $selectContainer.show();
        $select.select2('open');
    });

    // Получение ID тикета для обработчиков
    const ticketId = $('#comment-form').data('ticket-id');

    // Обработка выбора исполнителя
    $select.on('select2:select', function (e) {
        $.ajax({
            url: '/tickets',
            method: 'POST',
            data: {
                action: 'change_manager',
                ticket_id: ticketId,
                manager_id: e.params.data.id
            },
            success: function (response) {
                if (response.status) {
                    window.location.reload();
                } else {
                    Swal.fire({
                        type: 'success',
                        text: response.message || 'Произошла ошибка при смене исполнителя',
                        timer: 1500
                    });
                    $currentManager.show();
                    $changeBtn.show();
                    $selectContainer.hide();
                }
            },
            error: function () {
                Swal.fire({
                    type: 'error',
                    text: 'Произошла ошибка при выполнении запроса',
                    timer: 1500
                });
                $currentManager.show();
                $changeBtn.show();
                $selectContainer.hide();
            }
        });
    });

    // Обработка закрытия без выбора
    $select.on('select2:close', function () {
        if (!$select.val()) {
            $currentManager.show();
            $changeBtn.show();
            $selectContainer.hide();
        }
    });

    // Инициализация Select2 для выбора темы
    $("#subject-select").select2({
        width: '400px',
        placeholder: 'Начните вводить тему...',
        minimumInputLength: 2,
        ajax: {
            url: '/tickets/get-subjects',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    term: params.term
                };
            },
            processResults: function (data) {
                let results = data.map(function (item) {
                    return {
                        id: item.id,
                        text: item.name
                    };
                });

                return {
                    results: results
                };
            }
        }
    });

    // Обработка нажатия на кнопку смены темы
    $("#change-subject-btn").click(function () {
        $("#current-subject").hide();
        $("#change-subject-btn").hide();
        $("#subject-select-container").show();
        $("#subject-select").select2('open');
    });

    // Обработка выбора темы
    $("#subject-select").on('select2:select', function (e) {
        $.ajax({
            url: '/tickets',
            method: 'POST',
            data: {
                action: 'change_subject',
                ticket_id: ticketId,
                subject_id: e.params.data.id
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        type: 'success',
                        text: response.message || 'Тема успешно изменена',
                        timer: 1500
                    }).then(function () {
                        if (response.new_subject_name) {
                            $("#current-subject").text(response.new_subject_name);
                        }
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        type: 'error',
                        text: response.message || 'Произошла ошибка при смене темы',
                        timer: 1500
                    });
                    $("#current-subject").show();
                    $("#change-subject-btn").show();
                    $("#subject-select-container").hide();
                }
            },
            error: function () {
                Swal.fire({
                    type: 'error',
                    text: 'Произошла ошибка при выполнении запроса',
                    timer: 1500
                });
                $("#current-subject").show();
                $("#change-subject-btn").show();
                $("#subject-select-container").hide();
            }
        });
    });

    // Обработка закрытия без выбора
    $("#subject-select").on('select2:close', function () {
        if (!$("#subject-select").val()) {
            $("#current-subject").show();
            $("#change-subject-btn").show();
            $("#subject-select-container").hide();
        }
    });

    // ===== Смена приоритета =====
    const $currentPriority = $("#current-priority");
    const $changePriorityBtn = $("#change-priority-btn");
    const $prioritySelectContainer = $("#priority-select-container");
    const $prioritySelect = $("#priority-select");

    if ($prioritySelect.length) {
        $prioritySelect.select2({
            width: '400px',
            minimumResultsForSearch: 0
        });
    }

    if ($changePriorityBtn && $changePriorityBtn.length) {
        $changePriorityBtn.click(function () {
            $currentPriority.hide();
            $changePriorityBtn.hide();
            $prioritySelectContainer.show();
            $prioritySelect.select2('open');
        });
    }

    if ($prioritySelect && $prioritySelect.length) {
        $prioritySelect.on('select2:select', function (e) {
            $.ajax({
                url: '/tickets',
                method: 'POST',
                data: {
                    action: 'change_priority',
                    ticket_id: ticketId,
                    priority_id: e.params.data.id
                },
                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            type: 'success',
                            text: response.message || 'Приоритет успешно изменен',
                            timer: 1500
                        }).then(function () {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            type: 'error',
                            text: response.message || 'Произошла ошибка при смене приоритета',
                            timer: 1500
                        });
                        $currentPriority.show();
                        $changePriorityBtn.show();
                        $prioritySelectContainer.hide();
                    }
                },
                error: function () {
                    Swal.fire({
                        type: 'error',
                        text: 'Произошла ошибка при выполнении запроса',
                        timer: 1500
                    });
                    $currentPriority.show();
                    $changePriorityBtn.show();
                    $prioritySelectContainer.hide();
                }
            });
        });

        $prioritySelect.on('select2:close', function () {
            if (!$prioritySelect.val()) {
                $currentPriority.show();
                $changePriorityBtn.show();
                $prioritySelectContainer.hide();
            }
        });
    }


    // Обработчики для чекбокса договоренностей и переноса договоренностей
    $(document).on('click change', '#agreement-checkbox, .open-agreement-modal', function () {
        if ($(this).is('#agreement-checkbox') && !$(this).is(':checked')) {
            return;
        }

        const mode = $(this).data('mode') || 'create';
        const ticketId = $(this).data('ticket-id');

        $('#agreement-mode').val(mode);
        $('#agreement-ticket-id').val(ticketId);

        if (mode === 'reschedule') {
            const sourceTicketId = $(this).data('source-ticket-id');
            const currentDate = $(this).data('current-date');
            const currentNote = $(this).data('current-note');

            $('#agreement-source-ticket-id').val(sourceTicketId);
            $('#current-agreement-info').text(`Дата: ${currentDate}, Суть: ${currentNote}`);
            $('#current-agreement-block').show();

            $('#agreementModalLabel').text('Перенести договоренность');
            $('#agreement-date-label').text('Новая дата повторного контакта');
            $('#agreement-note-label').text('Причина переноса (опционально)');
            $('#agreement-date-hint').hide();
            $('#agreement-note').attr('placeholder', 'Укажите причину переноса...').val('');
            $('#agreement-apply-text').text('Перенести');
        } else {
            $('#current-agreement-block').hide();
            $('#agreementModalLabel').text('Достигнуты договоренности');
            $('#agreement-date-label').text('Дата повторного контакта');
            $('#agreement-note-label').text('Суть договоренностей');
            $('#agreement-date-hint').show();
            $('#agreement-note').attr('placeholder', 'Опишите договоренности...').val('');
            $('#agreement-apply-text').text('Применить');
        }

        $('#agreement-date').val('');
        $('#agreementModal').modal('show');
    });

    $(document).on('click', '#agreement-apply', async function () {
        const $btn = $(this);

        if ($btn.prop('disabled')) {
            return;
        }
        const mode = $('#agreement-mode').val();
        const ticketId = $('#agreement-ticket-id').val();
        const date = $('#agreement-date').val();
        const note = $('#agreement-note').val().trim();

        if (!date) {
            return Swal.fire({
                type: 'warning',
                title: 'Внимание',
                text: 'Выберите дату'
            });
        }

        if (mode === 'create' && !note) {
            return Swal.fire({
                type: 'warning',
                title: 'Внимание',
                text: 'Заполните «Суть договоренностей»'
            });
        }

        const loadingText = mode === 'reschedule' ? 'Переносим...' : 'Сохранение...';
        const successText = mode === 'reschedule' ? 'Договоренность перенесена на новую дату' : 'Договоренность зафиксирована';
        const errorText = mode === 'reschedule' ? 'Не удалось перенести договоренность' : 'Не удалось сохранить договоренность';

        $btn.prop('disabled', true);
        $btn.html(`<i class="fas fa-spinner fa-spin mr-2"></i>${loadingText}`);

        try {
            const data = {
                ticket_id: ticketId,
                date: date,
                note: note
            };

            if (mode === 'reschedule') {
                data.action = 'reschedule_agreement';
                data.source_ticket_id = $('#agreement-source-ticket-id').val();
                data.new_date = date;
                data.reason = note;
                delete data.date;
                delete data.note;
            } else {
                data.action = 'save_agreement';
            }

            const response = await $.ajax({
                url: '/tickets',
                method: 'POST',
                data: data
            });

            if (response.success) {
                $('#agreementModal').modal('hide');
                if (mode === 'create') {
                    $('#agreement-checkbox').prop('checked', false);
                }
                await Swal.fire({
                    type: 'success',
                    title: mode === 'reschedule' ? 'Перенесено' : 'Сохранено',
                    text: successText,
                    timer: 1500
                });
                location.reload();
            } else {
                throw new Error(response.message || errorText);
            }
        } catch (error) {
            console.log(error);
            if (mode === 'create') {
                $('#agreement-checkbox').prop('checked', false);
            }
            Swal.fire({
                type: 'error',
                title: 'Ошибка',
                text: error.message || errorText
            });
        } finally {
            $btn.prop('disabled', false);
            $btn.html(`<i class="fas fa-check mr-2"></i><span id="agreement-apply-text">${mode === 'reschedule' ? 'Перенести' : 'Применить'}</span>`);
        }
    });

    $('#agreementModal').on('hidden.bs.modal', function () {
        $('#agreement-checkbox').prop('checked', false);
    });

    $('#agreementModal').on('show.bs.modal', function () {
        const today = new Date().toISOString().split('T')[0];
        $('#agreement-date').attr('min', today);
    });

    $(window).on('unload', () => {
        TimerManager.stop();
    });
});