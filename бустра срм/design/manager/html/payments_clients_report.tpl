{$meta_title='Отчёт по пролонгациям/погашениям клиентов' scope=parent}

{capture name='page_scripts'}
    <script src="design/manager/assets/plugins/moment/moment.js"></script>

    <script src="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <!-- Date range Plugin JavaScript -->
    <script src="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script src="design/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    <script>
        $(function(){
            $('.daterange').daterangepicker({
                autoApply: true,
                locale: {
                    format: 'DD.MM.YYYY'
                },
                default:''
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
                    a.download = 'payments_clients_report' + new Date().toISOString().slice(0, 10) + '.xlsx';
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
    <link href="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    <!-- Daterange picker plugins css -->
    <link href="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="design/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">
{/capture}

<style>
    tr.small td {
        padding: 0.25rem;
    }
    .table thead th, .table th {
        border: 1px solid;
        font-size: 10px;
    }
    thead.position-sticky {
        top: 0;
        background-color: #272c33;
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
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{$meta_title} за период {if $date_from}{$date_from} - {$date_to}{/if}</h4>

                        <form>
                            <div class="row">
                                <div class="col-12 col-md-4">
                                    <div class="input-group mb-3">
                                        <input type="text" name="daterange" class="form-control daterange" value="{if $date_from && $date_to}{$date_from} - {$date_to}{/if}">
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <span class="ti-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <button type="submit" class="btn btn-info">Отфильтровать</button>

                                    <button onclick="return download();" type="button" class="btn btn-success">
                                        <i class="ti-save"></i> Выгрузить
                                    </button>
                                </div>
                            </div>
                        </form>

                        {include file='html_blocks/pagination.tpl'}
                        <div id="result" class="">
                            <table class="table table-bordered table-hover">
                                <thead class="position-sticky">
                                <tr>
                                    <th>№ договора</th>
                                    <th>Дата платежа</th>
                                    <th>Фио клиента</th>
                                    <th>Сумма оплаты</th>
                                    <th>Тип оплаты</th>
                                    <th>Номер телефона клиента</th>
                                    <th>Дата контакта с горячей линией</th>
                                    <th>Последний контакт</th>
                                    <th>Информация о доп подключенном</th>
                                    <th>Дата отключения доп</th>
                                    <th>Фио кто отключил</th>
                                    <th>Сумма доп услуги подключенной</th>
                                    <th>Сумма доп услуги отключенной</th>
                                </tr>
                                </thead>
                                <tbody>
                                {if $items}
                                    {foreach $items as $item}
                                        <tr>
                                            <td>
                                                {$item->contract_number}
{*                                                <br><a target="_blank" href="/order/{$item->order_id}">{$item->order_id}</a>*}
                                            </td>
                                            <td>{$item->created}</td>
                                            <td>
                                                <a target="_blank" href="/client/{$item->user_id}">{$item->user_fio}</a>
                                            </td>
                                            <td>{$item->amount}</td>
                                            <td>{if $item->prolongation}Продление{else}Погашение{/if}</td>
                                            <td>{$item->user_phone}</td>
                                            <td>{$item->support_last_update}</td>
                                            <td>{$item->support_fio}</td>
                                            <td>
                                                {if $item->tv_medical_amount > 0}Вита-мед<br>{/if}
                                                {if $item->multipolis_amount > 0}Консьерж сервис<br>{/if}
                                                {if $item->credit_doctor_to_user_amount > 0}Кредитный доктор{/if}
                                            </td>
                                            <td>
                                                {', '|implode:$item->refund_service}
                                                {', '|implode:$item->refund_created}
                                            </td>
                                            <td>{$item->refund_manager}</td>
                                            <td>{$item->tv_medical_amount + $item->multipolis_amount + $item->credit_doctor_to_user_amount}</td>
                                            <td>{$item->refund_sum}</td>
                                        </tr>
                                    {/foreach}
                                {else}
                                    <tr>
                                        <td colspan="6" class="text-danger text-center">Данные не найдены</td>
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
