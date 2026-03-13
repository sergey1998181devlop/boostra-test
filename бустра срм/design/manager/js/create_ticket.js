$(function() {
    // Инициализация Select2 для поля выбора исполнителя
    $("#manager_id").select2({
        placeholder: "Выберите исполнителя",
        allowClear: true,
        ajax: {
            url: '/tickets?action=get_managers',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return { term: params.term };
            },
            processResults: function(data) {
                return {
                    results: $.map(data, function(item) {
                        return { id: item.id, text: item.name };
                    })
                };
            },
            cache: true
        }
    });

    // Инициализация масок для полей ввода
    $("input[name='clientPhone'], #manualClientPhone").inputmask("+7 (999) 999-99-99");
    $("input[name='clientBirth'], #manualClientBirth").inputmask("99.99.9999");

    // Инициализация глобальных переменных
    window.foundClientLoans = [];
    window.foundClient = null;

    // Проверка наличия GET-параметров client_id и order_id при загрузке страницы
    const urlParams = new URLSearchParams(window.location.search);
    const clientIdParam = urlParams.get('client_id');
    const orderIdParam = urlParams.get('order_id');

    // Если есть client_id, но нет order_id, делаем запрос для получения займов клиента
    if (clientIdParam && !orderIdParam && $('#client-loans-table tbody tr').length === 0) {
        loadUserData(clientIdParam, function(userData) {
            // Обновляем информацию о клиенте если нужно
            if ($('#client-info-card').hasClass('d-none')) {
                updateClientInfoPanel(userData);
            }

            // Отображаем займы, если их нет в таблице
            if (window.foundClientLoans.length > 0 && $('#client-loans-table tbody tr').length === 0) {
                displayClientLoans(window.foundClientLoans);
            }
        });
    }

    // Если нет client_id, показываем блок поиска клиента
    if (!clientIdParam) {
        $('#manual-search-block').removeClass('d-none');
    }

    // Если есть order_id, отмечаем этот заем как выбранный
    if (orderIdParam) {
        setTimeout(() => {
            $(`#loan-row-${orderIdParam}`).addClass('active-loan');
        }, 500);
    }

    // Обработчик отправки формы тикета с валидацией
    $("#add-ticket").submit(function(event) {
        event.preventDefault();

        // Проверка обязательных полей
        let isValid = true;
        let isTechnicalSupportForm = $(this).hasClass('technical_support_ticket_form');

        // Проверка темы обращения
        if (!$("#subject").val()) {
            $("#subject").addClass('is-invalid');
            isValid = false;
        } else {
            $("#subject").removeClass('is-invalid');
        }

        if (!$("#priority").val()) {
            $("#priority").addClass('is-invalid');
            isValid = false;
        } else {
            $("#priority").removeClass('is-invalid');
        }

        // Проверка канала коммуникации
        if (!$("select[name='chanel']").val() && !isTechnicalSupportForm) {
            $("select[name='chanel']").addClass('is-invalid');
            isValid = false;
        } else {
            $("select[name='chanel']").removeClass('is-invalid');
        }

        // Проверка компании
        if (!$("select[name='company']").val()) {
            $("select[name='company']").addClass('is-invalid');
            isValid = false;
        } else {
            $("select[name='company']").removeClass('is-invalid');
        }

        // Проверка статуса клиента
        if (!$("select[name='client_status']").val()) {
            $("select[name='client_status']").addClass('is-invalid');
            isValid = false;
        } else {
            $("select[name='client_status']").removeClass('is-invalid');
        }

        // Проверка описания
        if (!$("textarea[name='description']").val().trim()) {
            $("textarea[name='description']").addClass('is-invalid');
            isValid = false;
        } else {
            $("textarea[name='description']").removeClass('is-invalid');
        }

        // Проверка выбора исполнителя
        if (!$("#manager_id").val()) {
            $("#manager_id").addClass('is-invalid');
            isValid = false;
        } else {
            $("#manager_id").removeClass('is-invalid');
        }

        // Проверка данных клиента при ручном вводе
        if ($('#manual-client-form').is(':visible')) {
            // Проверка ФИО
            if (!$('#manualClientFio').val().trim()) {
                $('#manualClientFio').addClass('is-invalid');
                isValid = false;
            } else {
                $('#manualClientFio').removeClass('is-invalid');

                // Обновляем значение скрытого поля для ФИО клиента
                $('#manual_client_fio').val($('#manualClientFio').val().trim());
            }

            // Проверка телефона
            const phone = $('#manualClientPhone').val();
            const cleanPhone = phone.replace(/\D/g, '');
            if (cleanPhone.length < 10) {
                $('#manualClientPhone').addClass('is-invalid');
                isValid = false;
            } else {
                $('#manualClientPhone').removeClass('is-invalid');

                // Обновляем значение скрытого поля для телефона клиента
                $('#manual_client_phone').val(phone);
            }

            // Обновляем значение скрытого поля для даты рождения, если она заполнена
            if ($('#manualClientBirth').val()) {
                $('#manual_client_birth').val($('#manualClientBirth').val());
            } else {
                $('#manual_client_birth').val('');
            }
        }

        if (!isValid) {
            Swal.fire({
                type: 'error',
                title: 'Ошибка валидации',
                text: 'Пожалуйста, заполните все обязательные поля'
            });
            return;
        }

        let formData = {
            action: 'save',
            client_id: $('#client_id').val(),
            order_id: $('#order_id').val(),
            subject: $('#subject').val(),
            priority_id: $('#priority').val(),
            chanel: $('select[name="chanel"]').val(),
            company: $('select[name="company"]').val(),
            client_status: $('select[name="client_status"]').val(),
            source: $('#source').val(),
            manager_id: $('#manager_id').val(),
            description: $('textarea[name="description"]').val(),
            is_repeat: $('#is_repeat').is(':checked') ? 'on' : '',
            remove_from_load: $('#remove_from_load').is(':checked') ? 'on' : '',
            additional_subject_id: $('#additional_subject_id').val(),
            is_add_ticket_subject: $('#is_add_ticket_subject').val()
        };

        if ($('#manual-client-form').is(':visible')) {
            formData.client_fio = $('#manual_client_fio').val();
            formData.clientPhone = $('#manual_client_phone').val();
            formData.clientBirth = $('#manual_client_birth').val();
            formData.clientEmail = $('#manual_client_email').val();
        }

        showFullScreenLoading();

        $.ajax({
            url: '/tickets/save/',
            type: 'POST',
            data: formData,
            success: function(result) {
                hideFullScreenLoading();

                if (result.status) {
                    // Если тикет успешно создан
                    $('#block_history').removeClass('d-none');
                    
                    Swal.fire({
                        timer: 5000,
                        text: 'Тикет успешно добавлен!',
                        type: 'success',
                        onClose: function() {
                            window.location.href = $('#subject :selected').text() === 'Подача тикета' ? '/technical-support/tickets' : '/tickets/';
                        }
                    });
                } else {
                    Swal.fire({
                        timer: 15000,
                        title: 'Произошла ошибка',
                        text: result.message || 'Обратитесь к администратору',
                        type: 'error',
                    });
                }
            },
            error: function(xhr) {
                hideFullScreenLoading()
                let errorMessage = 'Произошла ошибка при отправке формы';

                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMessage = response.message;
                    }
                } catch (e) {
                    console.error('Ошибка парсинга ответа:', e);
                }

                Swal.fire({
                    type: 'error',
                    title: 'Ошибка',
                    text: errorMessage
                });
            }
        });
    });

    // Обработчик формы для тех. поддержки
    $('#subject').on('change', function(){
        switchFormType($(this).val());
    });
});

// Глобальные переменные
var userLoans = [];
var clientTickets = [];

/**
 * Загрузка данных пользователя по ID или телефону
 * @param {string|number} idOrPhone - ID пользователя или номер телефона
 * @param {function} callback - Функция обратного вызова после успешной загрузки
 * @param {boolean} isPhone - Флаг, указывающий, что передан телефон, а не ID
 */
function loadUserData(idOrPhone, callback, isPhone = false) {
    if (!idOrPhone) return;

    showFullScreenLoading();

    const params = {
        action: 'search'
    };

    if (isPhone) {
        params.phone = idOrPhone;
    } else {
        params.id = idOrPhone;
    }

    $.ajax({
        url: '/ajax/users.php',
        type: 'GET',
        data: params,
        success: function(result) {
            hideFullScreenLoading()

            if (result.success && result.user) {
                // Сохраняем данные клиента и его займы
                window.foundClient = result.user;
                window.foundClientLoans = result.user.loans || [];

                if (typeof callback === 'function') {
                    callback(result.user);
                }
            } else {
                // Если клиент не найден и это был поиск по телефону
                if (isPhone) {
                    Swal.fire({
                        type: 'info',
                        title: 'Клиент не найден',
                        text: 'Проверьте номер телефона или используйте ручной ввод данных'
                    });
                }
            }
        },
        error: function() {
            hideFullScreenLoading()

            if (isPhone) {
                Swal.fire({
                    type: 'error',
                    title: 'Ошибка',
                    text: 'Произошла ошибка при поиске пользователя'
                });
            } else {
                console.error('Ошибка при загрузке данных о клиенте');
            }
        }
    });
}

// Функция поиска клиента по номеру телефона
function searchByPhone() {
    let inputVal = $('#clientPhone').val();
    let cleanPhone = inputVal.replace(/\D/g, '');

    if (cleanPhone.length < 10) {
        Swal.fire({
            type: 'warning',
            title: 'Некорректный номер',
            text: 'Введите полный номер телефона'
        });
        return;
    }

    loadUserData(cleanPhone, function(user) {
        $('#client-info-block').removeClass('d-none');

        // Заполняем данные клиента
        $('#clientFioInputNewTicket').val(user.full_name);
        $('#clientDateBirth').val(user.birth);
        $('#client_id').val(user.id);

        $('.search-clear-btn').removeClass('d-none');
    }, true);
}

// Показать форму для ручного ввода данных клиента
function showManualClientForm() {
    $('#search-by-phone-button').removeClass('d-none');
    $('#no-client-button').addClass('d-none');
    $('#phone-search-form').addClass('d-none');
    $('#client-info-block').addClass('d-none');
    $('#manual-client-form').removeClass('d-none');

    // Копируем номер телефона, если был введен
    if ($('#clientPhone').val()) {
        $('#manualClientPhone').val($('#clientPhone').val());
    }

    // Очищаем ID клиента
    $('#client_id').val('');
}

function showPhoneSearchForm() {
    $('#manual-client-form').addClass('d-none');
    $('#phone-search-form').removeClass('d-none');

    $('#search-by-phone-button').addClass('d-none');
    $('#no-client-button').removeClass('d-none');
}

// Подтверждение привязки найденного клиента
function confirmClientAttachment() {
    if (!window.foundClient || !window.foundClient.id) {
        Swal.fire({
            type: 'error',
            title: 'Ошибка',
            text: 'Клиент не найден или не выбран'
        });
        return;
    }

    // Скрываем блок поиска
    $('#manual-search-block').addClass('d-none');

    // Показываем блок привязки клиента
    $('#client-attached-info').removeClass('d-none');
    $('.attached-client-name').text(window.foundClient.full_name);

    // Обновляем информацию о клиенте
    updateClientInfoPanel(window.foundClient);

    // Загружаем займы клиента
    if (window.foundClientLoans && window.foundClientLoans.length > 0) {
        displayClientLoans(window.foundClientLoans);
    }

    // Загружаем тикеты клиента
    loadClientTickets(window.foundClient.id);
}

// Изменение привязанного клиента
function changeClient() {
    // Показываем блок поиска
    $('#manual-search-block').removeClass('d-none');

    // Скрываем информационные блоки
    $('#client-attached-info').addClass('d-none');
    $('#manual-client-info').addClass('d-none');

    // Очищаем отображение данных клиента
    $('#client-info-card').addClass('d-none');
    $('#client-loans-section').addClass('d-none');
    $('#client-tickets-section').addClass('d-none');
    $('#loan-info-card').addClass('d-none');

    // Сбрасываем форму поиска и ручного ввода
    $('#phone-search-form').removeClass('d-none');
    $('#manual-client-form').addClass('d-none');
    cleanSearchBlock();
}

function updateClientInfoPanel(user) {
    if (!user) return;

    // Формирование полного имени клиента
    let fullName = '';
    if (user.full_name) {
        fullName = user.full_name;
    } else {
        if (user.lastname) fullName += user.lastname + ' ';
        if (user.firstname) fullName += user.firstname + ' ';
        if (user.patronymic) fullName += user.patronymic;
        fullName = fullName.trim();
    }

    // Обновляем данные в карточке клиента
    $('.client-fullname').text(fullName);
    $('.client-birth').text(user.birth || '');

    if (user.phone_mobile) {
        $('.client-phone').html(`<a href="tel:${user.phone_mobile}" style="color: white;">${user.phone_mobile}</a>`);
    } else {
        $('.client-phone').text('Не указан');
    }

    if (user.email) {
        $('.client-email').html(`<a href="mailto:${user.email}" style="color: white;">${user.email}</a>`);
    } else {
        $('.client-email').text('Не указан');
    }

    // Показываем карточку с информацией о клиенте
    $('#client-info-card').removeClass('d-none');
}

// Функция для отображения займов клиента
function displayClientLoans(loans) {
    if (!loans || loans.length === 0) return;

    let loansTable = $('#client-loans-table tbody');
    loansTable.empty();

    loans.forEach(function(loan) {
        loansTable.append(`
            <tr id="loan-row-${loan.id}">
                <td><a href="/order/${loan.id}" target="_blank">${loan.id}</a></td>
                <td>${loan.amount} р.</td>
                <td>${loan.date}</td>
                <td>${loan.status_1c}</td>
                <td>
                    <button type="button" class="btn btn-info btn-sm loan-attach-btn" 
                            onclick="attachLoan('${loan.id}')">Привязать</button>
                </td>
            </tr>
        `);
    });

    $('#client-loans-section').removeClass('d-none');
}

// Функция для загрузки предыдущих тикетов клиента
function loadClientTickets(clientId) {
    if (!clientId) return;

    $.ajax({
        url: '/ajax/tickets.php',
        type: 'GET',
        data: {
            action: 'get_client_tickets',
            client_id: clientId
        },
        success: function(result) {
            if (result.success && result.tickets && result.tickets.length > 0) {
                clientTickets = result.tickets;
                displayClientTickets(clientTickets);
            } else {
                $('#client-tickets-table tbody').html('<tr><td colspan="5" class="text-center">У клиента нет предыдущих тикетов</td></tr>');
                $('#client-tickets-section').removeClass('d-none');
            }
        }
    });
}

// Функция для отображения предыдущих тикетов клиента
function displayClientTickets(tickets) {
    if (!tickets || tickets.length === 0) return;

    let ticketsTable = $('#client-tickets-table tbody');
    ticketsTable.empty();

    tickets.forEach(function(ticket) {
        ticketsTable.append(`
            <tr>
                <td><a href="/tickets/${ticket.id}" target="_blank">${ticket.id}</a></td>
                <td>${ticket.subject_name}</td>
                <td>${ticket.manager_name}</td>
                <td>${ticket.status_name}</td>
                <td>${ticket.created_at}</td>
            </tr>
        `);
    });

    $('#client-tickets-section').removeClass('d-none');
}

// Функция для привязки займа к тикету
function attachLoan(loanId) {
    let loan = null;

    // Ищем данные займа в глобальной переменной window.foundClientLoans
    if (window.foundClientLoans && window.foundClientLoans.length > 0) {
        loan = window.foundClientLoans.find(l => l.id.toString() === loanId.toString());
    }

    if (loan) {
        // Устанавливаем значение order_id
        $('#order_id').val(loanId);

        // Убираем выделение со всех строк и добавляем текущему займу
        $('#client-loans-table tbody tr').removeClass('active-loan');
        $(`#loan-row-${loanId}`).addClass('active-loan');

        // Скрываем таблицу с займами
        $('#client-loans-section').addClass('d-none');

        // Обновляем и показываем карточку с информацией о займе
        updateLoanInfoPanel(loan);
        //Устанавливает <select> company
        setCompanySelect(loan);
    }
}

// Функция для отвязки займа от тикета
function detachLoan() {
    // Очищаем поле order_id
    $('#order_id').val('');

    // Удаляем выделение строки в таблице займов
    $('#client-loans-table tbody tr').removeClass('active-loan');

    // Скрываем карточку с информацией о займе
    $('#loan-info-card').addClass('d-none');

    // Проверяем, есть ли у клиента займы в таблице
    if ($('#client-loans-table tbody tr').length === 0) {
        const clientId = $('#client_id').val();
        if (clientId) {
            loadUserData(clientId, function(user) {
                if (window.foundClientLoans.length > 0) {
                    displayClientLoans(window.foundClientLoans);
                } else {
                    // Если займов нет, просто показываем пустую таблицу
                    $('#client-loans-table tbody').html('<tr><td colspan="5" class="text-center">У клиента нет займов</td></tr>');
                    $('#client-loans-section').removeClass('d-none');
                }
            });
        } else {
            // Если ID клиента нет, просто показываем пустую таблицу
            $('#client-loans-section').removeClass('d-none');
        }
    } else {
        // Если займы уже отображены, просто показываем таблицу
        $('#client-loans-section').removeClass('d-none');
    }
}

// Обновление информации о займе
function updateLoanInfoPanel(loan) {
    if (!loan) return;

    // Обновляем данные в карточке займа
    $('.loan-id').html(`<a href="/order/${loan.id}" target="_blank">${loan.id}</a>`);
    $('.loan-amount').text(`${loan.amount}`);
    $('.loan-date').text(loan.date);
    $('.loan-status').text(loan.status_1c || 'Не указан');

    // Проверить, содержит ли значение amount символ "р."
    if ($('.loan-amount').text().indexOf('р.') === -1) {
        $('.loan-amount').text($('.loan-amount').text() + ' р.');
    }

    // Показываем карточку с информацией о займе
    $('#loan-info-card').removeClass('d-none');
}

//Устанавливает <select> company
function setCompanySelect(loan){
  if (!loan || !loan.organization_id) return;
  $('select[name="company"]').val(loan.organization_id);
}
// Функция для очистки данных поиска
function cleanSearchBlock() {
    // Очищаем поля ввода
    $('#clientPhone').val('');
    $('#clientFioInputNewTicket').val('');
    $('#clientDateBirth').val('');
    $('#manualClientFio').val('');
    $('#manualClientPhone').val('');
    $('#manualClientBirth').val('');
    $('#manualClientEmail').val('');
    $('#client_id').val('');
    $('#order_id').val('');

    // Скрываем блоки с результатами
    $('#client-info-block').addClass('d-none');
    $('#client-loans-section').addClass('d-none');
    $('#client-tickets-section').addClass('d-none');
    $('#client-info-card').addClass('d-none');
    $('#loan-info-card').addClass('d-none');
    $('#client-attached-info').addClass('d-none');
    $('#manual-client-info').addClass('d-none');

    // Очищаем таблицы
    $('#client-loans-table tbody').empty();
    $('#client-tickets-table tbody').empty();

    // Скрываем кнопку очистки
    $('.search-clear-btn').addClass('d-none');

    // Сбрасываем формы к начальному состоянию
    showPhoneSearchForm();

    // Очищаем глобальные переменные
    window.foundClient = null;
    window.foundClientLoans = [];
    userLoans = [];
    clientTickets = [];
}

function showFullScreenLoading(message = 'Пожалуйста, подождите...') {
    $('.loading-overlay').remove();

    const loadingOverlay = $(`
        <div class="loading-overlay">
            <div class="text-center">
                <div class="spinner-border" role="status">
                    <span class="sr-only">Загрузка...</span>
                </div>
                <div class="loading-text mt-3">${message}</div>
            </div>
        </div>
    `);

    $('body').append(loadingOverlay);
}

function hideFullScreenLoading() {
    $('.loading-overlay').remove();
}

function switchFormType(typeId) {
    $.ajax({
        url: '/ajax/tickets.php',
        type: 'GET',
        data: {
            action: 'get_technical_support_type_id',
        },
        success: function(result) {
            let parts = window.location.pathname.split('/');
            let lastPart = parts[parts.length - 1];

            if (Number(typeId) === result.id && lastPart !== 'technical-support') {
                window.location.pathname = '/tickets/create/technical-support';
                return;
            }

            if (Number(typeId) !== result.id && lastPart === 'technical-support') {
                window.location.pathname = '/tickets/create';
            }
        }
    });
}