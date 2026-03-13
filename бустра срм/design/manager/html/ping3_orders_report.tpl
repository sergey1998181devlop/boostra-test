{$meta_title='Отчет заявки PING 3' scope=parent}

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
    </script>
{/capture}

{capture name='page_styles'}

    <link href="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    <!-- Daterange picker plugins css -->
    <link href="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="design/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">

    <style>
        .table td {
            text-align:center!important;
        }

        .table{
            overflow-x: auto;
            display: block;
        }

        .jsgrid-table { margin-bottom:0}
    </style>
{/capture}

<div class="page-wrapper">
    <!-- ============================================================== -->
    <!-- Container fluid  -->
    <!-- ============================================================== -->
    <div class="container-fluid">
        <!-- ============================================================== -->
        <!-- Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-file-chart"></i>
                    <span>Отчет заявки PING 3</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Отчет заявки PING 3</li>
                </ol>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Start Page Content -->
        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Отчет {$utm_source} за период {if $date_from}{$date_from|date} - {$date_to|date}{/if}</h4>
                       <form>
                            <div class="row">
                                <div class="col">
                                    <div class="input-group mb-3">
                                        <input type="text" name="daterange" class="form-control daterange" value="{if $from && $to}{$from}-{$to}{/if}">
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <span class="ti-calendar"></span>
                                            </span>
                                        </div>
                                        <select name="utm_source" class="form-control">
                                            <option {if $utm_source == ''}selected{/if} value="">Все партнеры</option>
                                            <option {if $utm_source == 'finuslugi-api'}selected{/if}>finuslugi-api</option>
                                            <option {if $utm_source == 'bankiru-api'}selected{/if}>bankiru-api</option>
                                            <option {if $utm_source == 'leadstech-api'}selected{/if}>leadstech-api</option>
                                            <option {if $utm_source == 'bonon-api'}selected{/if}>bonon-api</option>
                                        </select>
                                        <select name="status" class="form-control">
                                            <option value="">Статус</option>
                                            <option value="{Orders::ORDER_STATUS_CRM_ISSUED}" {if $status == Orders::ORDER_STATUS_CRM_ISSUED}selected{/if}>Выдан</option>
                                            <option value="{Orders::ORDER_STATUS_CRM_REJECT}" {if $status == Orders::ORDER_STATUS_CRM_REJECT}selected{/if}>Отказ</option>
                                        </select>
                                        <select name="client_type" class="form-control">
                                            <option value="">Тип клиента</option>
                                            <option value="{Ping3Data::CHECK_USER_RESPONSE_NEW}" {if $client_type == Ping3Data::CHECK_USER_RESPONSE_NEW}selected{/if}>Новые</option>
                                            <option value="{Ping3Data::CHECK_USER_RESPONSE_REPEAT}" {if $client_type == Ping3Data::CHECK_USER_RESPONSE_REPEAT}selected{/if}>Повторные</option>
                                        </select>
                                        <select name="order_type" class="form-control">
                                            <option value="">Тип заявки</option>
                                            <option value="{Ping3Data::PING3_CRM_CROSS_ORDER}" {if $order_type == Ping3Data::PING3_CRM_CROSS_ORDER}selected{/if}>Кросс ордера</option>
                                            <option value="{Ping3Data::PING3_CRM_AUTO_APPROVE}" {if $order_type == Ping3Data::PING3_CRM_AUTO_APPROVE}selected{/if}>Автозаявками</option>
                                        </select>
                                        <select name="date_type" class="form-control">
                                            <option value="">Тип даты</option>
                                            <option value="confirm_date" {if $date_type == 'confirm_date'}selected{/if}>Дата выдачи</option>
                                            <option value="reject_date" {if $date_type == 'reject_date'}selected{/if}>Дата отказа</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="btn-group btn-block">
                                        <button type="submit" class="btn btn-info">Сформировать</button>
                                        {if $date_from || $date_to}
                                            <a href="{url download='excel'}" class="btn btn-success ">
                                                <i class="fas fa-file-excel"></i> Скачать
                                            </a>
                                        {/if}
                                    </div>
                                </div>
                            </div>
                        </form>
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr class="bg-gray small" >
                                    <th class="text-center">ID вебмастера</th>
                                    <th class="text-center">Тип заявки</th>
                                    <th class="text-center">Источник</th>
                                    <th class="text-center">Кликхеш</th>
                                    <th class="text-center">ID заявки</th>
                                    <th class="text-center">1C id заявки</th>
                                    <th class="text-center">Дата заявки</th>
                                    <th class="text-center">Статус</th>
                                    <th class="text-center">Причина отказа</th>
                                    <th class="text-center">Дата выдачи</th>
                                    <th class="text-center">Скориста</th>
                                    <th class="text-center">Дата Постбека</th>
                                    <th class="text-center">Выплата</th>
                                    <th class="text-center">Постбэк выдача</th>
                                </tr>
                            </thead>

                            {foreach $items as $item}
                                <tr>
                                    <td>
                                        {$item->webmaster_id}
                                    </td>
                                    <td>
                                        {$item->loan_type}
                                    </td>
                                    <td>
                                        {if $item->utm_source == 'cf'}
                                            ЦФ
                                        {else}
                                            {$item->utm_source}
                                        {/if}
                                    </td>
                                    <td>
                                        {$item->click_hash}
                                    </td>
                                    <td>
                                        <a href="order/{$item->order_id}" target="_blank"><strong>{$item->order_id}</strong></a>
                                    </td>
                                    <td>
                                        {$item->{'1c_id'}}
                                    </td>
                                    <td>
                                        {$item->order_date|date} {$item->order_date|time}
                                    </td>
                                    <td>
                                        {if $item->status == 0}<span class="label label-rounded label-inverse">Заполнение</span>{/if}
                                        {if $item->status == 1}<span class="label label-rounded label-info">Новая</span>{/if}
                                        {if $item->status == 2}<span class="label label-rounded label-success">Одобрена</span>{/if}
                                        {if $item->status == 3}<span class="label label-rounded label-danger">Отказ</span>{/if}
                                        {if $item->status == 4}<span class="label label-rounded label-warning">Отказался сам</span>{/if}
                                        {if $item->status == 5}<span class="label label-rounded label-inverse">На исправлении</span>{/if}
                                        {if $item->status == 6}<span class="label label-rounded label-info">Исправлена</span>{/if}
                                        {if $item->status == 7}<span class="label label-rounded label-warning">Ожидание</span>{/if}

                                        {if $item->status == 8}<span class="label label-rounded label-info">Подписан</span>{/if}
                                        {if $item->status == 9}<span class="label label-rounded label-info">Готов к выдаче</span>{/if}
                                        {if $item->status == 10}<span class="label label-rounded label-primary">Выдан</span>{/if}
                                        {if $item->status == 11}<span class="label label-rounded label-danger">Не удалось выдать</span>{/if}
                                        {if $item->status == 12}<span class="label label-rounded label-success">Закрыт</span>{/if}
                                        {if $item->status == 13}<span class="label label-rounded label-warning">Выдача отложена</span>{/if}
                                        {if $item->status == 14}<span class="label label-rounded label-success">Предварительно одобрена</span>{/if}
                                        {if $item->status == 15}<span class="label label-rounded label-warning">Автоподписание</span>{/if}
                                        {if $item->status == 17}<span class="label label-rounded label-success">Охлаждение</span>{/if}
                                    </td>
                                    <td>
                                        {$item->reason}
                                    </td>
                                    <td>
                                        {if $item->confirm_date}
                                            {$item->confirm_date|date} {$item->confirm_date|time}
                                        {/if}
                                    </td>
                                    <td>
                                        {$item->scorista_ball}
                                    </td>
                                    <td>
                                        {if $item->leadgid_postback_date}
                                            {$item->leadgid_postback_date|date} {$item->leadgid_postback_date|time}
                                        {/if}
                                    </td>
                                    <td>
                                        {$item->payout_grade}
                                    </td>
                                    <td>
                                        {if $item->leadgen_postback}
                                            <a class="btn btn-primary btn-block" href="#postback_{$item->order_id}" data-target="#postback_{$item->order_id}" data-toggle="collapse">
                                                <span>Да</span>
                                                <i class=" fas fa-angle-down"></i>
                                            </a>
                                            <div id="postback_{$item->order_id}" class="collapse pt-2">
                                                <p class="text-secondary">{$item->leadgen_postback}</p>
                                            </div>
                                        {else}
                                            Нет
                                        {/if}
                                    </td>
                                </tr>
                            {/foreach}
                        </table>

                        {include file='html_blocks/pagination.tpl'}

                        <table class="table table-hover table-bordered">
                            <tfoot>
                                <tr class="bg-dark text-danger small">
                                    <td>Итого заявок - {$totals->total_orders} на сумму {$totals->total_amount|number_format:2:'.':' '} ₽</td>
                                    <td>Начислено к оплате {$totals->total_payout_grade|number_format:2:'.':' '} ₽</td>
                                    <td>Постбеков выдача - {$totals->total_leadgen_postback}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <!-- Column -->
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End PAge Content -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- footer -->
    <!-- ============================================================== -->
    {include file='footer.tpl'}
    <!-- ============================================================== -->
    <!-- End footer -->
    <!-- ============================================================== -->
</div>
