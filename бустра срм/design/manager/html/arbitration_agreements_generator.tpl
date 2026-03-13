{$meta_title='Создание арбитражных соглашений + Оферты + АСП' scope=parent}

{capture name='page_scripts'}
    <script>
        $(function () {
            $(document).on('click', '#sendFile', function (e) {
                e.preventDefault();

                // Проверка, что файл выбран
                if ($('#file_upload')[0].files.length === 0) {
                    Swal.fire({
                        timer: 5000,
                        title: 'Ошибка!',
                        text: 'Пожалуйста, выберите файл для загрузки',
                        type: 'error',
                    });
                    return;
                }

                sendRefundForm();
            });

            // Функция для отправки формы
            const sendRefundForm = function () {
                let formData = new FormData();
                formData.append('file_upload', $('#file_upload')[0].files[0]);
                formData.append('action', 'arbitration_agreements_generation');
                formData.append('document_type', $('#document_type').val());

                let loadingTimer;
                const showLoading = function() {
                    loadingTimer = setTimeout(() => {
                        Swal.fire({
                            title: 'Магия в процессе...',
                            text: "Генерируем арбитражные соглашения... Если бы это делалось вручную, вы бы уже успели взять отпуск и вернуться! Так что 5 минут – сущий пустяк.",
                            allowEscapeKey: false,
                            allowOutsideClick: false,
                            onOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    }, 1000);
                };

                const hideLoading = function() {
                    if (loadingTimer) {
                        clearTimeout(loadingTimer);
                    }
                    Swal.close();
                };

                $.ajax({
                    url: 'ajax/ArbitrationAgreementsGenerator.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        showLoading();
                    },
                    success: function (response) {
                        hideLoading();

                        if (response.status !== true) {
                            Swal.fire({
                                timer: 5000,
                                title: 'Ошибка!',
                                text: response.message,
                                type: 'error',
                            });
                            return;
                        }

                        showProcessingResult(response.result);
                    },
                    error: function () {
                        hideLoading();

                        Swal.fire({
                            timer: 5000,
                            title: 'Ошибка!',
                            text: 'Произошла ошибка при отправке файла.',
                            type: 'error',
                        });
                    }
                });
            };

            // Функция для показа результатов обработки
            const showProcessingResult = function(response) {
                var title = 'Обработка завершена!';
                var contractStatuses = response.contract_statuses || [];
                var downloadButton = '';

                // Сортируем: сначала НЕТ (status: false), потом ДА (status: true)
                contractStatuses.sort(function(a, b) {
                    if (a.status === b.status) return 0;
                    return a.status ? 1 : -1; // false идет первым
                });

                // Строим таблицу с результатами
                var tableHtml = '<div style="max-height: 400px; overflow-y: auto; margin: 20px 0;">' +
                    '<table style="width: 100%; border-collapse: collapse; text-align: left;">' +
                    '<thead>' +
                    '<tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">' +
                    '<th style="padding: 12px; border: 1px solid #dee2e6;">Номер договора</th>' +
                    '<th style="padding: 12px; border: 1px solid #dee2e6;">Статус</th>' +
                    '</tr>' +
                    '</thead>' +
                    '<tbody>';

                for (var i = 0; i < contractStatuses.length; i++) {
                    var contract = contractStatuses[i];
                    var statusText = contract.status ? 'ДА' : 'НЕТ';
                    var statusColor = contract.status ? '#28a745' : '#dc3545'; // зеленый : красный

                    tableHtml += '<tr style="border-bottom: 1px solid #dee2e6;">' +
                        '<td style="padding: 10px; border: 1px solid #dee2e6;">' + contract.contract_number + '</td>' +
                        '<td style="padding: 10px; border: 1px solid #dee2e6; color: ' + statusColor + '; font-weight: bold;">' + statusText + '</td>' +
                        '</tr>';
                }

                tableHtml += '</tbody></table></div>';

                // Добавляем сводку
                var successCount = contractStatuses.filter(function(c) { return c.status; }).length;
                var failCount = contractStatuses.length - successCount;
                var summaryHtml = '<p style="margin-top: 15px; font-size: 14px;">' +
                    '<strong>Успешно обработано:</strong> <span style="color: #28a745;">' + successCount + '</span> | ' +
                    '<strong>Не обработано:</strong> <span style="color: #dc3545;">' + failCount + '</span>' +
                    '</p>';

                if (response.download_file) {
                    downloadButton = '<button type="button" class="btn btn-warning mt-3" id="downloadUnprocessed" data-filename="' + response.download_file + '">Скачать Необработанные договоры</button>';
                }

                Swal.fire({
                    title: title,
                    html: summaryHtml + tableHtml + downloadButton,
                    type: 'info',
                    width: '800px',
                    showConfirmButton: true,
                    confirmButtonText: 'Закрыть',
                    allowOutsideClick: false,
                    onOpen: function() {
                        $('#downloadUnprocessed').on('click', function() {
                            var filename = $(this).data('filename');
                            downloadUnprocessedContracts(filename);
                        });
                    }
                });
            };

            // Функция для скачивания необработанных договоров
            const downloadUnprocessedContracts = function(filename) {
                var form = $('<form>', {
                    'method': 'POST',
                    'action': 'ajax/ArbitrationAgreementsGenerator.php'
                });

                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'action',
                    'value': 'download_unprocessed_contracts'
                }));

                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'filename',
                    'value': filename
                }));

                form.appendTo('body').submit().remove();
            };
        });
    </script>
{/capture}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0">
                    <i class="mdi mdi-closed-caption"></i>
                    <span>Создание арбитражных соглашений + Оферты + АСП</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Создание арбитражных соглашений + Оферты + АСП</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <form method="POST" action="ajax/ArbitrationAgreementsGenerator.php" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="arbitration_agreements_generation">
                                <div class="form-group col">
                                    <label for="document_type">Тип документа</label>
                                    <select name="document_type" id="document_type" class="form-control">
                                        <option value="{Documents::ARBITRATION_AGREEMENT}">Арбитражное соглашение + Оферты + АСП</option>
                                        <option value="{Documents::PENALTY_CREDIT_DOCTOR}">Штрафной кредитный доктор</option>
                                    </select>
                                </div>
                                <div class="form-group col">
                                    <label for="file_upload">Файл</label>
                                    <input type="file" id="file_upload" name="file_upload" class="form-control" placeholder="Выберите файл" accept=".xlsx, .xls">
                                    <small class="form-text text-muted">Файл должен быть в формате .xlsx и содержать только 1 колонку с номерами договоров. Пример "А25-7000126"</small>
                                </div>
                                <div class="form-group col">
                                    <input type="submit" value="Обработать" class="btn btn-primary" id="sendFile">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {include file='footer.tpl'}
</div>