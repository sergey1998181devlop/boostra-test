{$meta_title='Управление исключениями' scope=parent}

{capture name='page_styles'}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .report-card {
        background-color: #2a2e33;
        border: 1px solid #404850;
        border-radius: .5rem;
        color: #e2e2e2;
        padding: 1.25rem;
        margin-bottom: 1rem;
    }
    .report-card .stat-value {
        font-size: 2rem;
        font-weight: 500;
        color: #fff;
    }
    .report-card .stat-label {
        font-size: .9rem;
        color: #a0a0a0;
    }
    .report-card .stat-value .mdi {
        vertical-align: middle;
    }
    .table-report {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .table-report th,
    .table-report td {
        vertical-align: middle;
        white-space: nowrap;
        border: 1px solid #2d3338;
        padding: 10px;
    }
    .table-report thead th {
        background-color: #2d3338;
        color: #fff;
    }
    .table-report tbody td {
        background-color: #212529;
        color: #c8c8c8;
    }
    .select2-container--default .select2-selection--multiple {
        background-color: #2a2e33;
        border-color: #404850;
        color: #e2e2e2;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #01c0c8;
        border-color: #01c0c8;
        color: #fff;
    }
</style>
{/capture}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-12 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-cancel"></i>
                    <span>Управление исключениями восстановления</span>
                </h3>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Добавить исключение</h5>
                        <form method="post" id="addExclusionForm">
                            <input type="hidden" name="action" value="add_exclusion">
                            <div class="form-group">
                                <label for="user_id">ID Клиента</label>
                                <input type="number" class="form-control" name="user_id" required>
                            </div>
                            <div class="form-group">
                                <label for="order_id">ID Заявки (0 для всех заявок клиента)</label>
                                <input type="number" class="form-control" name="order_id">
                            </div>
                            <div class="form-group">
                                <label for="service_key">Ключ услуги (пусто для всех услуг)</label>
                                <input type="text" class="form-control" name="service_key">
                            </div>
                            <div class="form-group">
                                <label for="reason">Причина</label>
                                <textarea class="form-control" name="reason" rows="3" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="expires_at">Действует до (пусто = бессрочно)</label>
                                <input type="date" class="form-control" name="expires_at">
                            </div>
                            <button type="submit" class="btn btn-danger btn-block">Добавить</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Активные исключения</h5>
                        <div class="table-responsive">
                            <table class="table table-dark table-hover" id="exclusionsTable">
                                <thead>
                                <tr>
                                    <th>Клиент</th>
                                    <th>Заявка</th>
                                    <th>Услуга</th>
                                    <th>Причина</th>
                                    <th>Добавил</th>
                                    <th>Действует до</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach $exclusions as $ex}
                                    <tr>
                                        <td>{$ex->user_id} - {$ex->lastname|escape} {$ex->firstname|escape}</td>
                                        <td>{if $ex->order_id > 0}{$ex->order_id}{else}Все{/if}</td>
                                        <td>{if $ex->service_key}{$ex->service_key|escape}{else}Все{/if}</td>
                                        <td>{$ex->reason|escape}</td>
                                        <td>{$ex->manager_name|escape}</td>
                                        <td>{if $ex->expires_at}{$ex->expires_at|date_format:'%d.%m.%Y'}{else}Бессрочно{/if}</td>
                                        <td>
                                            <form method="post" class="deactivateForm" style="display:inline;">
                                                <input type="hidden" name="action" value="deactivate_exclusion">
                                                <input type="hidden" name="exclusion_id" value="{$ex->id}">
                                                <button type="submit" class="btn btn-sm btn-outline-warning" title="Деактивировать">
                                                    <i class="mdi mdi-close"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    {foreachelse}
                                    <tr>
                                        <td colspan="7" class="text-center">Нет активных исключений.</td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{capture name='page_scripts'}
    <script>
        {literal}
        $(function() {
            // Обработка отправки формы добавления
            $('#addExclusionForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const btn = form.find('button[type="submit"]');
                btn.prop('disabled', true).text('Добавление...');

                const formData = form.serialize();

                $.ajax({
                    url: window.location.href,
                    method: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                type: 'success',
                                title: 'Успешно',
                                text: 'Исключение успешно добавлено!'
                            }).then(function() {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                type: 'error',
                                title: 'Ошибка',
                                text: response.error || 'Неизвестная ошибка.'
                            });
                            btn.prop('disabled', false).text('Добавить');
                        }
                    },
                    error: function() {
                        Swal.fire({
                            type: 'error',
                            title: 'Ошибка',
                            text: 'Произошла критическая ошибка. Проверьте консоль.'
                        });
                        btn.prop('disabled', false).text('Добавить');
                    }
                });
            });

            $('#exclusionsTable').on('submit', '.deactivateForm', function(e) {
                e.preventDefault();

                const form = $(this);
                const btn = form.find('button[type="submit"]');

                Swal.fire({
                    type: 'question',
                    title: 'Подтверждение',
                    text: 'Вы уверены, что хотите деактивировать это исключение?',
                    showCancelButton: true,
                    confirmButtonText: 'Да, деактивировать',
                    cancelButtonText: 'Отмена'
                }).then(function(result) {
                    if (!result.value) {
                        return;
                    }

                    btn.prop('disabled', true);
                    const formData = form.serialize();

                    $.ajax({
                        url: window.location.href,
                        method: 'POST',
                        data: formData,
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    type: 'success',
                                    title: 'Успешно',
                                    text: 'Исключение деактивировано.'
                                }).then(function() {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    type: 'error',
                                    title: 'Ошибка',
                                    text: (response.error || 'Неизвестная ошибка.')
                                });
                                btn.prop('disabled', false);
                            }
                        },
                        error: function() {
                            Swal.fire({
                                type: 'error',
                                title: 'Ошибка',
                                text: 'Произошла критическая ошибка.'
                            });
                            btn.prop('disabled', false);
                        }
                    });
                });
            });
        });
        {/literal}
    </script>
{/capture}
