{literal}
    <script>
        jQuery(document).ready(function() {

            // Обновляем placeholder для поля даты рождения на мобильных устройствах
            if ($(window).width() < 480) {
                $('input[name="complaint_birth"]').attr('placeholder', 'дд.мм.гггг');
            }

            // Отображение счетчика символов для текстовой области
            $('.complaint_text').keyup(function() {
                var $this = $(this);
                var currentText = $this.val();
                var maxLength = $this.attr('maxlength');
                $this.siblings('#count_message').text(currentText.length + '/' + maxLength);
            });

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

            // Добавляем кастомный метод для проверки, что возраст не менее 18 лет
            $.validator.addMethod("adult", function(value, element) {
                if (this.optional(element)) {
                    return true;
                }
                var birthDate = new Date(value);
                var today = new Date();
                var age = today.getFullYear() - birthDate.getFullYear();
                var m = today.getMonth() - birthDate.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                return age >= 18;
            }, "Возраст должен быть не менее 18 лет.");

            let existingFiles = []; // Массив для хранения всех добавленных файлов

            $('#complaint').validate({
                rules: {
                    complaint_name: {
                        required: true,
                        pattern: /^[А-ЯЁа-яё]+(\s[А-ЯЁа-яё]+)+$/
                    },
                    complaint_phone: {
                        required: true,
                    },
                    complaint_email: {
                        required: true,
                        email: true
                    },
                    complaint_birth: {
                        required: true,
                        adult: true
                    },
                    complaint_topic: {
                        required: true
                    },
                    complaint_text: {
                        required: true,
                        minlength: 50,
                        maxlength: 300
                    },
                },
                messages: {
                    complaint_name: {
                        required: "Пожалуйста, введите ваше ФИО.",
                        pattern: "ФИО должно состоять минимум из двух слов, содержащих только русские буквы."
                    },
                    complaint_phone: {
                        required: "Пожалуйста, введите номер телефона.",
                    },
                    complaint_email: {
                        required: "Пожалуйста, введите ваш E-mail.",
                        email: "Введите корректный E-mail."
                    },
                    complaint_birth: {
                        required: "Пожалуйста, укажите дату рождения.",
                        adult: "Вы должны быть не младше 18 лет."
                    },
                    complaint_topic: {
                        required: "Пожалуйста, выберите тему обращения."
                    },
                    complaint_text: {
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
                    var selectedTopic = $(form).find('select[name="complaint_topic"] option:selected');
                    var yandexGoalId = selectedTopic.data('yandex-goal-id');
                    let formData = new FormData(form);
                    let files = formData.getAll('complaint_file[]');

                    if (!files.some(file => file instanceof File && file.size > 0)) {
                        formData.delete('complaint_file[]'); // Очищаем поле с файлами, если оно пустое
                    }

                    //for (let pair of formData.entries()) {
                    //    console.log(pair[0], pair[1]);
                    //}

                    let progressBar = $('#uploadProgressBar'); // Прогресс-бар
                    let progressText = $('#uploadProgressText'); // Текстовый процент

                    $.ajax({
                        url: '/complaint',
                        data: formData,
                        method: 'POST',
                        contentType: false,
                        processData: false,
                        xhr: function() {
                            let xhr = new window.XMLHttpRequest();
                            xhr.upload.addEventListener('progress', function(event) {
                                if (event.lengthComputable) {
                                    let percent = Math.round((event.loaded / event.total) * 100);
                                    progressBar.val(percent).attr('data-label', percent + '%');
                                }
                            });
                            return xhr;
                        },
                        beforeSend: function() {
                            $('.complaint_loader').addClass('loading');
                            progressBar.val(0).attr('data-label', '0%').show();
                            $('#complaint button[type="submit"]').prop('disabled', true);
                        },
                        success: function(response) {
                            if (response.error) {
                                let errorMessage = '';

                                if (response.error === 'empty_required_fields') {
                                    errorMessage = 'Не все обязательные поля заполнены';
                                } else if (response.error === 'max_files') {
                                    errorMessage = 'Вы можете выбрать не более 5 файлов.';
                                } else if (response.error === 'max_file_size') {
                                    errorMessage = 'Максимальный размер файла — 20 МБ.';
                                } else if (response.error === 'error_mix_files_types') {
                                    errorMessage = 'Невозможно отправить файлы разных типов.';
                                } else if (response.error === 'error_file_type') {
                                    errorMessage = 'Недопустимый тип файла.';
                                } else if (response.error === 'time_limit') {
                                    errorMessage = 'В данный момент форма не может быть отправлена. Пожалуйста, попробуйте отправить данные позже.';
                                }
                                progressBar.val(100).attr('data-label', 'Отправлено');
                                $('#complaint_sended_message').html(errorMessage);
                                $.magnificPopup.open({
                                    items: { src: '#modal_complaint_sended' },
                                    type: 'inline',
                                    showCloseBtn: true
                                });
                                return;
                            }

                            // Сообщение об успешной отправке
                            $('#complaint_sended_message').html(response.message || 'Обращение отправлено.');
                            $.magnificPopup.open({
                                items: { src: '#modal_complaint_sended' },
                                type: 'inline',
                                showCloseBtn: true
                            });

                            if (yandexGoalId) {
                                sendMetric('reachGoal', yandexGoalId);
                            }
                            sendMetric('reachGoal', 'complaint_sent');

                            // Очистка формы
                            $('#complaint').trigger('reset');
                            existingFiles = []; // Очистка массива файлов
                            $('#complaint_file_list').empty().hide();
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
                            alert(error);
                            $('.complaint_loader').removeClass('loading');
                            $('#complaint button[type="submit"]').prop('disabled', false);
                            progressBar.attr('data-label', 'Ошибка загрузки');
                        },
                    }).done(function() {
                        $('.complaint_loader').removeClass('loading');
                        $('#complaint button[type="submit"]').prop('disabled', false);
                    });
                }
            });

            $('#add_complaint_file').on('click', function(e) {
                e.preventDefault();
                $('#complaint_file_input').click();
            });

            // Обработка выбора файлов и обновление списка

            // Перестройка списка файлов
            function rebuildFileList(fileListElement, dataTransfer) {
                fileListElement.empty(); // Очищаем текущий список

                existingFiles.forEach((file, index) => {
                    let listItem = $(`<li class="complaint_file"><span>${file.name}</span><a href="javascript:void(0);" class="remove-complaint-file" data-index="${index}"><i class="bi bi-x"></i></a></li>`);

                    // превью
                    if (file.type.startsWith('image/')) {
                        let reader = new FileReader();
                        reader.onload = function(e) {
                            listItem.css({
                                backgroundImage: `url(${e.target.result})`,
                            });
                        };
                        reader.readAsDataURL(file);
                    }

                    fileListElement.append(listItem);
                    dataTransfer.items.add(file); // Добавляем файл в DataTransfer
                });

                // Показываем или скрываем список в зависимости от наличия файлов
                fileListElement.toggle(existingFiles.length > 0);

                if(existingFiles.length === 0){
                    $('#complaint_file_input').val('');
                }
            }

            $('#complaint_file_input').on('change', function(e) {
                let newFiles = Array.from(this.files);

                // Проверяем общее количество файлов, включая уже добавленные
                if (existingFiles.length + newFiles.length > 5) {
                    alert('Вы можете выбрать не более 5 файлов');
                    return;
                }

                let fileListElement = $('#complaint_file_list');
                let dataTransfer = new DataTransfer();

                // Добавляем новые файлы в массив existingFiles
                const MAX_FILE_SIZE = 20000000;
                newFiles.forEach((file) => {
                    if (file.size >= MAX_FILE_SIZE) {
                        alert(`Файл "${file.name}" слишком большой. Максимальный размер — 20 МБ.`);
                    } else {
                        existingFiles.push(file);
                    }
                });

                rebuildFileList(fileListElement, dataTransfer);

                // Обновляем input с файлами
                this.files = dataTransfer.files;
            });

            // Обработка удаления файлов из списка
            $(document).on('click', '.remove-complaint-file', function() {
                let index = $(this).data('index');

                // Удаляем файл из массива existingFiles
                existingFiles.splice(index, 1);

                let fileListElement = $('#complaint_file_list');
                let dataTransfer = new DataTransfer();

                rebuildFileList(fileListElement, dataTransfer);
            });
        });
    </script>
{/literal}