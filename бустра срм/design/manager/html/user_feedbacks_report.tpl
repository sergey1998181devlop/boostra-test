{$meta_title='Отчёт по отзывам в ЛК' scope=parent}

{capture name='page_scripts'}
    <script src="design/manager/assets/plugins/moment/moment.js"></script>
    <script src="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <!-- Date range Plugin JavaScript -->
    <script src="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script src="design/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    <script>
        $(function () {
            $('.daterange').daterangepicker({
                autoApply: true,
                locale: {
                    format: 'DD.MM.YYYY'
                },
                default: ''
            });
        })

        function download() {
            let dateRange = $('input[name="daterange"]').val();
            let url = '{$reportUri}?action=download&daterange=' + dateRange;

            // Выполняем Ajax-запрос
            $.ajax({
                url: url,
                method: 'GET',
                xhrFields: {
                    responseType: 'blob'
                },
                success: function (data, textStatus, jqXHR) {
                    const contentType = jqXHR.getResponseHeader('Content-Type');
                    if (contentType && contentType.includes('application/json')) {
                        const response = JSON.parse(data);
                        if (response.status === 'error') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Ошибка',
                                text: response.message
                            });
                            return;
                        }
                    }

                    const blob = new Blob([data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                    const downloadUrl = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = downloadUrl;
                    a.download = 'user_feedbacks_report_' + new Date().toISOString().slice(0, 10) + '.xlsx';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(downloadUrl);
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ошибка',
                        text: 'Произошла ошибка при выполнении запроса'
                    });
                }
            });

            return false;
        }

        function downloadStatistics() {
            let dateRange = $('input[name="daterange"]').val();
            let url = '{$reportUri}?action=downloadStatistics&daterange=' + dateRange;

            $.ajax({
                url: url,
                method: 'GET',
                xhrFields: {
                    responseType: 'blob'
                },
                success: function (data, textStatus, jqXHR) {
                    const contentType = jqXHR.getResponseHeader('Content-Type');
                    if (contentType && contentType.includes('application/json')) {
                        const reader = new FileReader();
                        reader.onload = function () {
                            const response = JSON.parse(reader.result);
                            if (response.status === 'error') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Ошибка',
                                    text: response.message
                                });
                            }
                        };
                        reader.readAsText(data);
                        return;
                    }

                    const blob = new Blob([data], {
                        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    });

                    const downloadUrl = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = downloadUrl;
                    a.download = 'user_feedback_statistics_' + new Date().toISOString().slice(0, 10) + '.xlsx';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(downloadUrl);
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ошибка',
                        text: 'Произошла ошибка при выполнении запроса'
                    });
                }
            });

            return false;
        }
    </script>
{/capture}

{capture name='page_styles'}
    <link href="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet"
          type="text/css"/>
    <link href="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="design/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">
{/capture}

<style>
    tr.small td {
        padding: 0.25rem;
    }

    .table thead th, .table th {
        border: 1px solid;
        font-size: 12px;
        min-width: 300px;
    }

    .table thead td, .table td {
        font-size: 12px;
    }

    thead.position-sticky {
        top: 0;
        background-color: #272c33;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .table td, .table th {
        white-space: normal;
        word-wrap: break-word;
    }

    .limited-text {
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 5;
        -webkit-box-orient: vertical;
        cursor: pointer;
    }
</style>

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-file-chart"></i>
                    <span>{$meta_title}</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">{$meta_title}</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{$meta_title} за
                            период {if $date_from}{$date_from} - {$date_to}{/if}</h4>

                        <form>
                            <div class="row">
                                <div class="col-12 col-md-4">
                                    <div class="input-group mb-3">
                                        <input type="text" name="daterange" class="form-control daterange"
                                               value="{if $date_from && $date_to}{$date_from} - {$date_to}{/if}">
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <span class="ti-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-5">
                                    <button type="submit" class="btn btn-info">Отфильтровать</button>

                                    <button onclick="return download();" type="button" class="btn btn-success">
                                        <i class="ti-save"></i> Выгрузить
                                    </button>
                                    <button onclick="return downloadStatistics();" type="button" class="btn btn-warning">
                                        <i class="ti-bar-chart"></i>Выгрузить статистику
                                    </button>
                                </div>
                            </div>
                        </form>

                        {include file='html_blocks/pagination.tpl'}
                        <div id="result" class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="position-sticky">
                                <tr>
                                    <th>Клиент</th>
                                    <th>Тип клиента</th>
                                    <th>Заявка</th>
                                    <th>Дата оценки</th>
                                    <th>Время оценки</th>
                                    <th>Оценка</th>
                                    <th>Причина оценки</th>
                                </tr>
                                </thead>
                                <tbody>
                                {if $items}
                                    {foreach $items as $item}
                                        {$feedback_data=json_decode($item->feedback_data)}
                                        <tr>
                                            <td>
                                                {if $can_see_client_url}
                                                    <a href="/client/{$item->user_id}">{$item->lastname} {$item->firstname} {$item->patronymic|escape}</a>
                                                {else}
                                                    {$item->lastname} {$item->firstname} {$item->patronymic|escape}
                                                {/if}
                                            </td>
                                            <td>
                                                {if $item->have_close_credits == 1}
                                                    ПК
                                                {else}
                                                    НК
                                                {/if}
                                            </td>
                                            <td>
                                                {if $can_see_client_url}
                                                    <a href="/order/{$item->order_id}">{$item->order_id}</a>
                                                {else}
                                                    {$item->order_id}
                                                {/if}
                                            </td>
                                            <td>{date('d.m.Y', strtotime($item->created_at))}</td>
                                            <td>{date('H:i:s', strtotime($item->created_at))}</td>
                                            <td>{$feedback_data->rate}</td>
                                            <td>{$feedback_data->reason}</td>
                                        </tr>
                                    {/foreach}
                                    <tr>
                                        <td>Средняя оценка</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td>{$items[0]->avg_rate}</td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                {else}
                                    <tr>
                                        <td colspan="19" class="text-danger text-center">Данные не найдены</td>
                                    </tr>
                                {/if}
                                </tbody>
                            </table>
                        </div>
                        {include file='html_blocks/pagination.tpl'}
                    </div>
                </div>
            </div>
        </div>
    </div>
    {include file='footer.tpl'}
</div>
