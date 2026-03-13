{$meta_title='Отчёт о действиях клиента после звонка в КЦ' scope=parent}

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
            const dateRange = $('input[name="daterange"]').val();
            const url = '{$reportUri}?action=download&daterange=' + dateRange;
        
            window.location.href = url;
        
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
                                    <th>Клиент</th>
                                    <th>Заявка</th>
                                    <th>Дата звонка</th>
                                    <th>Количество дней просрочки</th>
                                    <th>Действие после звонка</th>
                                    <th>Сумма платежа</th>
                                    <th>Дата платежа</th>
                                    <th>Ответственный сотрудник</th>
                                    <th>Запись звонка</th>
                                </tr>
                                </thead>
                                <tbody>
                                {if $items}
                                    {foreach $items as $item}
                                        <tr>
                                            <td>
                                                <a href="/client/{$item->user_id}">{$item->full_name}</a>
                                            </td>
                                            <td><a href="order/{$item->order_id}">{$item->order_id}</a></td>
                                            <td>{$item->call_date}</td>
                                            <td>{$item->days_overdue}</td>
                                            <td>{$item->client_action}</td>
                                            <td>{$item->payment_amount}</td>
                                            <td>{$item->payment_date}</td>
                                            <td>{$item->operator_name}</td>
                                            <td>
                                                {assign var="callResult" value=$item->call_comment|json_decode:1}
                                                
                                                Тег: {$callResult.tag} <br>
                                                Стадия: {$callResult.stage} <br>
                                                <audio controls src="{$callResult.record_url}" style="margin-top: 5px">
                                                    Ваш браузер не поддерживает воспроизведение аудио. Вот ссылка на запись: <a href="{$callResult.record_url}">Скачать</a>.
                                                </audio>
                                            </td>
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
