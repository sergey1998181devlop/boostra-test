{$meta_title='Автовозвраты доп услуг' scope=parent}

{capture name='page_scripts'}
    <script>
        $(function () {
            let refundPercent = 100;

            $(document).on('click', '#sendFile', function (e) {
                e.preventDefault();

                if ($('#file_upload')[0].files.length === 0) {
                    Swal.fire({
                        timer: 5000,
                        title: 'Ошибка!',
                        text: 'Пожалуйста, выберите файл для загрузки',
                        type: 'error',
                    });
                    return;
                }
                $('#refundPercentModal').modal('show');
            });

            $(document).on('click', '#confirmRefundPercent', function () {
                refundPercent = $('#refundPercent').val();
                $('#refundPercentModal').modal('hide');
                sendRefundForm();
            });

            // Функция для отправки формы
            const sendRefundForm = function () {
                let formData = new FormData();
                formData.append('file_upload', $('#file_upload')[0].files[0]);
                formData.append('action', 'auto_refund_services');
                formData.append('refund_percent', refundPercent);

                let loadingTimer;
                const showLoading = function() {
                    loadingTimer = setTimeout(() => {
                        Swal.fire({
                            title: 'Подождите<br> Идет обработка возвратов',
                            allowEscapeKey: false,
                            allowOutsideClick: false,
                            onOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    }, 2000);
                };

                const hideLoading = function() {
                    if (loadingTimer) {
                        clearTimeout(loadingTimer);
                    }
                    Swal.close();
                };

                $.ajax({
                    url: 'ajax/AutoRefundAdditionalServices.php',
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

                        Swal.fire({
                            timer: 5000,
                            title: 'Успешно!',
                            text: 'Файл успешно обработан.',
                            type: 'success',
                        }).then(() => {
                            updateTable(response.result);
                        });
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

            // Вставка данных в таблицу
            const updateTable = function (items) {
                const tableBody = $('#result tbody');

                tableBody.empty();

                if (items.length === 0) {
                    tableBody.html(`
                        <tr>
                            <td colspan="12" class="text-danger text-center">Заявки не найдены</td>
                        </tr>
                    `);
                    return;
                }

                {literal}
                    items.forEach(item => {
                        const row = `
                            <tr>
                                <td>${item.created}</td>
                                <td>${item.confirmation_date ?? '-'}</td>
                                <td>${item.key}</td>
                                <td><a href="order/${item.order_id}" target="_blank">${item.zaim_number}</a></td>
                                <td>${item.zaim_date}</td>
                                <td>${item.zaim_amount}</td>
                                <td>${item.service_price}</td>
                                <td><a href="client/${item.user_id}" target="_blank">${item.fio}</a></td>
                                <td>${item.birth_date}</td>
                                <td>${item.email}</td>
                                <td class="text-center">${item.status ? '✅' : '❌'}</td>
                                <td>${item.message}</td>
                                <td><a href="manager/${item.manager_id}" target="_blank">${item.manager_name}</a></td>
                            </tr>
                        `;
                        tableBody.append(row);
                    });
                {/literal}

                $('.download-button').removeClass('d-none');
                $('#result').removeClass('d-none');
            };
        });
    </script>
{/capture}

{capture name='page_styles'}
    <style>
      .table, table tr, table td, table th {
        font-size: 16px;
        border: 1px solid #f3f1f1 !important;
      }
    </style>
{/capture}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0">
                    <i class="mdi mdi-closed-caption"></i>
                    <span>Автовозвраты доп услуг</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Автовозвраты доп услуг</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Загрузите файл</h4>
                        <div class="row">
                            <form method="POST" action="ajax/AutoRefundAdditionalServices.php" class="col-md-8" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="auto_refund_services">
                                <input type="file" id="file_upload" name="file_upload" class="form-control col-md-4" placeholder="Выберите файл" accept=".xlsx, .xls">
                                <input type="submit" value="Обработать" class="btn btn-primary" style="margin-left: 10px; margin-top: -4px;" id="sendFile">
                            </form>
                            <div id="refundPercentModal" class="modal" tabindex="-1" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content" style="background-color: #2b2b2b; color: white;">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Выберите процент возврата</h5>
                                        </div>
                                        <div class="modal-body">
                                            <select id="refundPercent" class="form-control">
                                                <option value="100" selected>100%</option>
                                                <option value="75">75%</option>
                                                <option value="50">50%</option>
                                            </select>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                                            <button type="button" class="btn btn-success" id="confirmRefundPercent">Подтвердить</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 download-button d-none">
                                <a href="/files/refunds/auto_refunded_services.xlsx" download class="btn btn-success float-right">
                                    <i class="ti-save" style="padding-right: 10px"></i>Выгрузить
                                </a>
                            </div>
                        </div>

                        <div id="result" class="table-responsive mt-4 d-none">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Создан</th>
                                        <th>Подтвержден</th>
                                        <th>Ключ</th>
                                        <th>Договор</th>
                                        <th>Дата</th>
                                        <th>Займ</th>
                                        <th>Цена</th>
                                        <th>ФИО</th>
                                        <th>Дата Рождения</th>
                                        <th>Email</th>
                                        <th>Возвращен</th>
                                        <th>Причина</th>
                                        <th>Менеджер</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <!-- Сюда будут вставлены строки таблицы -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {include file='footer.tpl'}
</div>