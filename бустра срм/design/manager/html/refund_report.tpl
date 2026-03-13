{$meta_title='Отчёт - по допам (возвраты)' scope=parent}

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
                startDate: '{$from}',
                endDate: '{$to}',
                locale: {
                    format: 'DD.MM.YYYY'
                },
                default:''
            }
            );



        })
    </script>
{/capture}

{capture name='page_styles'}

    <link href="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    <!-- Daterange picker plugins css -->
    <link href="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="design/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">

    <style>
        .table td {
        }
    </style>
{/capture}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-file-chart"></i>
                    <span>Отчёт - по допам (возвраты)</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Отчёт - по допам (возвраты)</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Отчёт - по допам (возвраты)</h4>
                        <!--h4 class="text-danger animate-flashing">Внимание! Отчёт формируется с 2023.09.01</h4-->
                        <form>
                            <div class="row">
                                <div class="col-4 col-md-6">
                                    <div class="input-group mb-3">
                                        <input type="text" name="daterange" class="form-control daterange" value="{if $from && $to}{$from} - {$to}{/if}">

                                        <div class="input-group-append">
                                                <span class="input-group-text">
                                                    <span class="ti-calendar"></span>
                                                </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-8 col-md-6">
                                    <button type="submit" class="btn btn-info mr-5">Сформировать</button>
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
                                        <th>Клиент</th>
                                        <th>Номер договора</th>
                                        <th>Источник займа</th>
                                        <th>Дата услуги</th>
                                        <th>Дата возврата</th>
                                        <th>Кто вернул</th>
                                        <th>Вид услуги</th>
                                        <th>Банковская карта</th>
                                        <th>Процент</th>
                                        <th>Сумма</th>
                                        <th>Повторный возврат</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {if $items}
                                        {foreach $items as $item}
                                            <tr>
                                                <td>{$item->fio} {$item->birth}</td>
                                                <td>
                                                    {if $item->order_id}
                                                        <a href="order/{$item->order_id}" target="_blank">{$item->loan_number}</a>
                                                    {else}
                                                        {$item->loan_number}
                                                    {/if}
                                                </td>
                                                <td>{$item->loan_source}</td>
                                                <td>{$item->service_date}</td>
                                                <td>{$item->return_date}</td>
                                                <td>{$item->returned_by}</td>
                                                <td>{$item->service_title}</td>
                                                <td>{$item->card_number}</td>
                                                <td>{$item->refund_percent}</td>
                                                <td>{$item->refund_amount}</td>
                                                <td>{$item->repeat_refund}</td>
                                            </tr>
                                        {/foreach}
                                    {else}
                                        <tr>
                                            <td colspan="11" class="text-danger text-center">Данные не найдены</td>
                                        </tr>
                                    {/if}
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

<script>
    function download() {
        var downloadURL = '{$reportUri}?action=download';
        var date_range = $("[name='daterange']").val();
        date_range = date_range ? 'daterange=' + date_range : (new URL(document.location)).searchParams.toString();
        if (date_range) {
            downloadURL += "&" + date_range;
        }

        Swal.fire({
            html: 'Формируем отчёт...<br><small>Подождите</small>',
            allowOutsideClick: false,
            allowEscapeKey: false,
            onOpen: function() {
                Swal.showLoading();
            }
        });

        fetch(downloadURL, { credentials: 'same-origin' })
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('Ошибка ' + response.status);
                }
                return response.blob();
            })
            .then(function(blob) {
                var url = URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url;
                a.download = 'report_extra_service_refund_' + new Date().toLocaleDateString('ru-RU').replace(/\./g, '-') + '.xlsx';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
                Swal.close();
            })
            .catch(function(err) {
                Swal.close();
                Swal.fire({
                    type: 'error',
                    title: 'Ошибка выгрузки',
                    text: err.message || 'Не удалось сформировать отчёт. Попробуйте снова.'
                });
            });

        return false;
    }
</script>