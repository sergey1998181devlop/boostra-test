{$meta_title = 'Настройка тарифов для первого займа'}

{capture name='page_scripts'}
    <script>
        {literal}
        $(function () {
            function validateFields(modalBody) {
                let isValid = true;

                // Очищаем предыдущие ошибки
                modalBody.find('.form-group').removeClass('has-danger');
                modalBody.find('.form-control-feedback').remove();

                // Проверяем все required поля
                modalBody.find('input[required], select[required], textarea[required]').each(function () {
                    const input = $(this);
                    const value = input.val().trim();

                    if (!value || isNaN(value) && value === '') {
                        input.closest('.form-group')
                            .addClass('has-danger')
                            .append('<small class="form-control-feedback">Поле обязательно для заполнения</small>');
                        isValid = false;
                    }
                });

                return isValid;
            }

            // Открытие модального окна для редактирования
            $('.condition-row').click(function () {
                const row = $(this);
                const id = row.data('id');
                const fromAmount = row.find('td:eq(0)').text().trim();
                const toAmount = row.find('td:eq(1)').text().trim();
                const price = row.find('td:eq(2)').text().trim();
                const licenseKeyDays = row.find('td:eq(3)').text().trim();

                $('#edit_condition_id').val(id);
                $('#edit_condition_from_amount').val(fromAmount);
                $('#edit_condition_to_amount').val(toAmount);
                $('#edit_condition_price').val(price);
                $('#edit_license_key_days').val(licenseKeyDays);

                $('#editConditionModal').modal('show');
            });

            // Сохранение изменений из модалки
            $('#saveConditionBtn').click(function () {
                const modal = $('#editConditionModal');
                const modalBody = modal.find('.modal-body');

                if (!validateFields(modalBody)) {
                    alert('Заполните все обязательные поля');
                    return;
                }

                const id = $('#edit_condition_id').val();
                const fromAmount = $('#edit_condition_from_amount').val();
                const toAmount = $('#edit_condition_to_amount').val();
                const price = $('#edit_condition_price').val();
                const license_key_days = $('#edit_license_key_days').val();

                $.ajax({
                    url: '/fd_setting',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        id: id,
                        from_amount: fromAmount,
                        to_amount: toAmount,
                        price: price,
                        license_key_days: license_key_days
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Ошибка: ' + response.error);
                        }
                    },
                    error: function() {
                        alert('Произошла ошибка при сохранении');
                    }
                });

                $('#editConditionModal').modal('hide');
            });

            // Добавление новой записи
            $('#addConditionBtn').click(function () {
                const modal = $('#addConditionModal');
                const modalBody = modal.find('.modal-body');

                if (!validateFields(modalBody)) {
                    alert('Заполните все обязательные поля');
                    return;
                }

                const fromAmount = $('#new_condition_from_amount').val();
                const toAmount = $('#new_condition_to_amount').val();
                const price = $('#new_condition_price').val();
                const licenseKeyDays = $('#new_license_key_days').val();

                $.ajax({
                    url: '/fd_setting',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        from_amount: fromAmount,
                        to_amount: toAmount,
                        price: price,
                        license_key_days: licenseKeyDays,
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Ошибка: ' + response.error);
                        }
                    },
                    error: function() {
                        alert('Произошла ошибка при добавлении');
                    }
                });

                $('#addConditionModal').modal('hide');
            });
        });
        $(document).on('click', '.js-delete-condition', function () {
            const row = $(this).closest('tr');
            const id = $(this).data('id');

            if (!confirm('Вы уверены, что хотите удалить эту запись?')) {
                return;
            }

            $.ajax({
                url: '/fd_setting',
                method: 'DELETE',
                dataType: 'json',
                data: { id: id },
                success: function(response) {
                    if (response.success) {
                        row.remove();
                    } else {
                        alert('Ошибка при удалении: ' + response.error);
                    }
                }
            });
        });
        {/literal}
    </script>
{/capture}

{capture name='page_styles'}
    <style>
        .condition-row:hover {
            background-color: #f5f5f5;
            cursor: pointer;
        }
        .form-group.has-danger {
            color: #e53935;
        }

        .form-group.has-danger .form-control {
            border-color: #e53935;
        }

        .form-control-feedback {
            color: #e53935;
            font-size: 0.875em;
        }
        .js-delete-condition {
            margin: 2px 0;
        }
    </style>
{/capture}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">Настройка тарифов для безопасных ПК</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Настройка тарифов</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
                <button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#addConditionModal">
                    <i class="mdi mdi-plus-circle"></i> Добавить диапазон
                </button>
            </div>
        </div>

        <form method="POST">
            <div class="card">
                <div class="card-body">
                    <table id="conditions-table" class="table table-bordered table-striped table-hover">
                        <thead>
                        <tr>
                            <th>От суммы</th>
                            <th>До суммы</th>
                            <th>Цена</th>
                            <th>Срок действия ключа</th>
                            <th>Действия</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $conditions as $item}
                            <tr class="jsgrid-row js-promo-row condition-row" data-id="{$item->id}">
                                <td>{$item->from_amount}</td>
                                <td>{$item->to_amount}</td>
                                <td>{$item->price}</td>
                                <td>{$item->license_key_days}</td>
                                <td>
                                    <button class="btn btn-danger btn-sm js-delete-condition" data-id="{$item->id}">Удалить</button>
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Редактирование -->
<div class="modal fade" id="editConditionModal" tabindex="-1" role="dialog" aria-labelledby="editConditionModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editConditionModalLabel">Редактировать диапазон</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit_condition_id">

                <div class="form-group">
                    <label>От суммы</label>
                    <input type="number" min="0" required="true" class="form-control" id="edit_condition_from_amount">
                </div>

                <div class="form-group">
                    <label>До суммы</label>
                    <input type="number" min="0" required="true" class="form-control" id="edit_condition_to_amount">
                </div>

                <div class="form-group">
                    <label>Цена</label>
                    <input type="number" min="0" required="true" class="form-control" id="edit_condition_price">
                </div>

                <div class="form-group">
                    <label>Срок действия ключа</label>
                    <input type="number" min="0" required="true" class="form-control" id="edit_license_key_days">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-success" id="saveConditionBtn">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Добавление -->
<div class="modal fade" id="addConditionModal" tabindex="-1" role="dialog" aria-labelledby="addConditionModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addConditionModalLabel">Добавить новый диапазон</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <div class="form-group">
                    <label>От суммы</label>
                    <input type="number" required="true" min="0" class="form-control" id="new_condition_from_amount">
                </div>

                <div class="form-group">
                    <label>До суммы</label>
                    <input type="number" required="true" min="0" class="form-control" id="new_condition_to_amount">
                </div>

                <div class="form-group">
                    <label>Цена</label>
                    <input type="number" required="true" min="0" class="form-control" id="new_condition_price">
                </div>

                <div class="form-group">
                    <label>Срок действия ключа</label>
                    <input type="number" required="true" min="0" class="form-control" id="new_license_key_days">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-success" id="addConditionBtn">Добавить</button>
            </div>
        </div>
    </div>
</div>