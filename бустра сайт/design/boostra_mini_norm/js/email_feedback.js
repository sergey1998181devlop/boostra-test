$('.feedback_name').on('keypress', function(event) {
    let key = String.fromCharCode(event.which);

    if (/^[\p{L}\s]+$/u.test(key)) {
        return true;
    }

    event.preventDefault();
    return false;
});

/** Действие при загрузке страницы */
jQuery(document).ready(function () {
    function openModal(params) {
        $.magnificPopup.open({
            items: {
              src: '#modal_email_feedback'
            },
            type: 'inline',
            showCloseBtn: true
        });
    }

    // Custom placement for showing the indicator inside the text area
    $('.feedback_text').keyup(function() {
        var $this = $(this);
        var currentText = $this.val();
        var maxLength = $this.attr('maxlength');
        $this.siblings('#count_message').text(currentText.length + '/' +  maxLength);
    });

    // Обработчик события нажатия на кнопку #send_complaint
    $('#send_complaint').on('click', function () {
        openModal(params);
    });

    // Получаем параметры запроса
    let params = window
        .location
        .search
        .replace('?', '')
        .split('&')
        .reduce(
            function (p, e) {
                var a = e.split('=');
                p[decodeURIComponent(a[0])] = decodeURIComponent(a[1]);
                return p;
            },
            {}
        );

    // Если в параметрах страницы есть page_action=open_feedback, открываем модальное окно
    if (typeof params['page_action'] !== 'undefined') {
        if (params['page_action'] === 'open_feedback') {
            openModal(params);
        }
    }
    
    // Добавляем кастомный метод pattern
    $.validator.addMethod("pattern", function(value, element, param) {
        if (this.optional(element)) {
            return true;
        }
        // Проверяем, является ли param регулярным выражением. Если это строка, преобразуем её в регулярное выражение.
        if (typeof param === "string") {
            param = new RegExp(param);
        }
        return param.test(value);
    }, "Неправильный формат ввода.");
    
    $('#modal_email_feedback').validate({
        rules: {
            feedback_name: {
                required: true,
                pattern: /.+\s.+/, // ФИО должно содержать минимум два слова
            },
            feedback_phone: {
                required: true,
            },
            feedback_email: {
                required: true,
                email: true
            },
            feedback_topic: {
                required: true
            },
            feedback_text: {
                required: true,
                minlength: 50,
                maxlength: 300
            },
        },
        messages: {
            feedback_name: {
                required: "Пожалуйста, введите ваше ФИО.",
                pattern: "ФИО должно состоять минимум из двух слов, разделенных пробелом."
            },
            feedback_phone: {
                required: "Пожалуйста, введите номер телефона.",
            },
            feedback_email: {
                required: "Пожалуйста, введите ваш E-mail.",
                email: "Введите корректный E-mail."
            },
            feedback_topic: {
                required: "Пожалуйста, выберите тему обращения."
            },
            feedback_text: {
                required: "Пожалуйста, введите текст обращения.",
                minlength: "Текст обращения должен содержать не менее 50 символов.",
                maxlength: "Текст обращения не должен превышать 300 символов."
            },
        },
        errorElement: 'span',
        errorPlacement: function(error, element) {
            error.insertAfter(element); // Добавляем сообщение об ошибке после элемента
        },
        submitHandler: function(form, event) {
            event.preventDefault();
            
            // Отправляем форму через AJAX только после успешной валидации
            var selectedTopic = $(form).find('select[name="feedback_topic"] option:selected');
            var yandexGoalId = selectedTopic.data('yandex-goal-id');
            let formData = new FormData(form);
    
            $.ajax({
                url: '/tickets/add',
                data: formData,
                method: 'POST',
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $('#modal_email_feedback button[type="submit"]').prop('disabled', true);
                },
                success: function (response) {
                    if (response.error) {
                        let errorMessage = '';
    
                        if (response.error === 'empty_required_fields') {
                            errorMessage = 'Не все обязательные поля заполнены';
                        } else if (response.error === 'limit_exceeded') {
                            errorMessage = 'Вы превысили лимит на количество обращений за сутки. Пожалуйста, попробуйте позже.';
                        }
    
                        $('#feedback_sended_message').html(errorMessage);
                        $.magnificPopup.close();
                        $.magnificPopup.open({
                            items: { src: '#modal_email_feedback_sended' },
                            type: 'inline',
                            showCloseBtn: true
                        });
                        return;
                    }
    
                    // Сообщение об успешной отправке
                    $('#feedback_sended_message').html(response.message || 'Обращение отправлено.');
                    $.magnificPopup.close();
                    $.magnificPopup.open({
                        items: { src: '#modal_email_feedback_sended' },
                        type: 'inline',
                        showCloseBtn: true
                    });
    
                    if (yandexGoalId) {
                        sendMetric('reachGoal', yandexGoalId);
                    }
                    sendMetric('reachGoal', 'complaint_sent');
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
                    alert(error);
                    $('#modal_email_feedback button[type="submit"]').prop('disabled', false);
                },
            }).done(function () {
                $('#modal_email_feedback button[type="submit"]').prop('disabled', false);
            });
        }
    });
});

$('#add_feedback_file').on('click', function (e) {
    e.preventDefault();
    $('#feedback_file_input').click();
});

// Обработка выбора файлов и обновление списка
$('#feedback_file_input').on('change', function (e) {
    let files = Array.from(this.files);
    let file_list = $('#feedback_file_list');
    file_list.empty();

    if (files.length > 0) {
        file_list.show();
    } else {
        file_list.hide();
    }

    files.forEach(function (file, index) {
        let listItem = $(`
            <li>
                ${file.name}
                <a href="javascript:void(0);" class="remove-attached-file" data-index="${index}"><i class="bi bi-x"></i></a>
            </li>
        `);
        file_list.append(listItem);
    });
});

// Обработка удаления файлов из списка
$(document).on('click', '.remove-attached-file', function () {
    let index = $(this).data('index');
    let fileInput = $('#feedback_file_input')[0];
    let files = Array.from(fileInput.files);

    files.splice(index, 1);

    let dataTransfer = new DataTransfer();
    files.forEach(file => dataTransfer.items.add(file));
    fileInput.files = dataTransfer.files;

    $(this).parent().remove();

    if ($('#feedback_file_list li').length === 0) {
        $('#feedback_file_list').hide();
    }
});

$('input[type="tel"]').inputmask("+7 (999) 999-99-99");
