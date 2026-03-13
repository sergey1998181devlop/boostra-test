$(function() {
    // Обработчик отправки формы тикета с валидацией
    $("#add-ts-sla").submit(function(event) {
        event.preventDefault();

        // Проверка обязательных полей
        let isValid = true;

        // Проверка темы обращения
        if (!$("#quarter").val()) {
            $("#quarter").addClass('is-invalid');
            isValid = false;
        } else {
            $("#quarter").removeClass('is-invalid');
        }

        // Проверка года
        if (!$("#year").val()) {
            $("#year").addClass('is-invalid');
            isValid = false;
        } else {
            $("#year").removeClass('is-invalid');
        }

        // Проверка приоритета
        if (!$("select[name='priority']").val()) {
            $("select[name='priority']").addClass('is-invalid');
            isValid = false;
        } else {
            $("select[name='priority']").removeClass('is-invalid');
        }

        // Проверка числовых полей
        if (!$("#react_limit_minutes").val()) {
            $("#react_limit_minutes").addClass('is-invalid');
            isValid = false;
        } else {
            $("#react_limit_minutes").removeClass('is-invalid');
        }

        if (!$("#react_limit_percents").val()) {
            $("#react_limit_percents").addClass('is-invalid');
            isValid = false;
        } else {
            $("#react_limit_percents").removeClass('is-invalid');
        }

        if (!$("#resolve_limit_minutes").val()) {
            $("#resolve_limit_minutes").addClass('is-invalid');
            isValid = false;
        } else {
            $("#resolve_limit_minutes").removeClass('is-invalid');
        }

        if (!$("#resolve_limit_percents").val()) {
            $("#resolve_limit_percents").addClass('is-invalid');
            isValid = false;
        } else {
            $("#resolve_limit_percents").removeClass('is-invalid');
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
            quarter: $('select[name="quarter"]').val(),
            year: $('#year').val(),
            priority: $('select[name="priority"]').val(),
            react_limit_minutes: $('#react_limit_minutes').val(),
            react_limit_percents: $('#react_limit_percents').val(),
            resolve_limit_minutes: $('#resolve_limit_minutes').val(),
            resolve_limit_percents: $('#resolve_limit_percents').val()
        };

        showFullScreenLoading();

        $.ajax({
            url: '/technical-support/sla/save/',
            type: 'POST',
            data: formData,
            success: function(result) {
                hideFullScreenLoading();

                if (result.status) {
                    // Если SLA успешно создан
                    $('#block_history').removeClass('d-none');

                    Swal.fire({
                        timer: 5000,
                        text: 'SLA успешно добавлен!',
                        type: 'success',
                        onClose: function() {
                            window.location.href = '/technical-support/sla/list';
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
});


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