{$meta_title='Отчёт по отказникам' scope=parent}

{capture name='page_styles'}

    <link href="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="design/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">
{/capture}

<style>
    tr.small td {
        padding: 0.25rem;
    }
    .btn-group {
        margin-bottom: 30px;
    }

    .daterange {
        width: 300px;
    }
</style>

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-file-chart"></i>
                    <span>Отчёт по отказникам</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Отчёт по отказникам</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            Отчет за период {if $date_from}{$date_from|date} - {$date_to|date}{/if}
                            {if $filter_source}
                                {foreach $filter_source as $fs}
                                    {$fs}{if !$fs@last}, {/if}
                                {/foreach}
                            {/if}
                        </h4>
                        <form id="report_form">
                            <div class="row">
                                <div class="col-6 col-md-3">
                                    <div class="input-group mb-3">
                                        <input type="text" name="date_range" class="form-control daterange" value="{if $from && $to}{$from}-{$to}{/if} ">
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <span class="ti-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-auto">
                                    <div class="mb-3">
                                        <select class="form-control" name="filter_client">
                                            <option value="NK" {if $filter_client == 'NK'}selected{/if}>НК</option>
                                            <option value="PK" {if $filter_client == 'PK'}selected{/if}>ПК </option>
                                            <option value="ALL" {if $filter_client == 'ALL'}selected{/if}>НК + ПК</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6 col-md-2">
                                    <div class="mb-3">
                                        <select class="form-control" name="filter_ondate">
                                            <option value="ORDER" {if $filter_ondate == 'ORDER'}selected{/if}>на дату заявки</option>
                                            <option value="REPORT" {if $filter_ondate == 'REPORT'}selected{/if}>на дату отчета </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6 col-md-2">
                                    <div class="mb-3">
                                        <select class="form-control" name="filter_scorista">
                                            <option value="0" {if $filter_scorista == '0'}selected{/if}>0-199 (скориста)</option>
                                            <option value="200" {if $filter_scorista == '200'}selected{/if}>200-399 (скориста)</option>
                                            <option value="400" {if $filter_scorista == '400'}selected{/if}>400-&#8734; (скориста)</option>

                                        </select>
                                    </div>
                                </div>
                                <div class="col-6 col-md-auto">
                                    <div class="custom-control custom-checkbox">
                                        <input name="filter_order_valid" type="checkbox" class="custom-control-input" id="filter_order_valid" value="1"  {if $filter_order_valid}checked="checked"{/if} />
                                        <label class="custom-control-label" for="filter_order_valid">
                                            Действует договор займа
                                        </label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input name="filter_remove_dublicate_by_phone" type="checkbox" class="custom-control-input" id="filter_remove_dublicate_by_phone" value="1" {if $filter_remove_dublicate_by_phone}checked="checked"{/if} />
                                        <label class="custom-control-label" for="filter_remove_dublicate_by_phone">
                                            Убрать дубли по телефону
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6 col-md-auto">
                                    <div class="btn-group">
                                        <button type="button" onclick="loadData();" class="btn btn-info"><i class="ti-reload"></i> Сформировать</button>
                                        <button onclick="download();" type="button" class="btn btn-success"><i class="ti-save"></i> Выгрузить</button>
                                    </div>
                                </div>
                            </div>

                        </form>

                        <div id="result" class="table-responsive">

                            <table class="table table-bordered table-hover">
                                <input type="hidden" value="{$results|@count}" name="total_rows" />
                                <thead>
                                <tr>
                                    <th>ФИО</th>
                                    <th>Телефон</th>
                                    <th>Дата подачи<br>заявки</th>
                                    <th>Причина отказа</th>
                                    <th>Балл<br>скористы</th>
                                </tr>

                                <tr class="bg-dark text-danger">
                                    <td colspan="5">Итого: {$total_results}</td>
                                </tr>
                                </thead>
                                <tbody>
                                {if $results}
                                {foreach $results as $item}
                                    <tr data-id="{$item->id}">
                                        <td data-userid="{$item->user_id}">
                                            {$item->fio}
                                        </td>
                                        <td>
                                            {$item->phone_mobile}
                                        </td>
                                        <td>
                                            {$item->date}
                                        </td>
                                        <td>
                                            {$item->reason}
                                        </td>
                                        <td>
                                            {$item->scorista_ball}
                                        </td>
                                     </tr>
                                {/foreach}
                                {else}
                                    <tr>
                                        <td colspan="5">
                                            <h3 class="text-danger">Данные не найдены</h3>
                                        </td>
                                    </tr>
                                {/if}
                                <tr>
                                    <td colspan="5" >Итого: {$total_results}</td>
                                </tr>
                                <tr>
                                    <td colspan="5" >{include file='html_blocks/pagination.tpl'}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <strong class=""></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {include file='footer.tpl'}
</div>

{capture name='page_scripts'}

    <script src="design/manager/assets/plugins/moment/moment.js"></script>
    <script src="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script src="design/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    <script src="design/manager/js/tinysort.min.js"></script>




    <script>
        $(function(){
            $('.daterange').daterangepicker({
                    autoApply: true,
                    startDate: '{$from}',
                    endDate: '{$to}',
                    locale: {
                        format: 'YYYY.MM.DD'
                    },
                    default:''
                }
            );
        });

        function loadData(){
            $('.preloader').show();
            let filter_data = $('#report_form').serialize();
            $("h4.card-title").text("Отчет за период " +$("[name='date_range']").val());
            $("#result").load('{$smarty.server.REQUEST_URI}?ajax=1&' + filter_data + ' #result table', function (response, status, xhr) {
                $('.preloader').hide();
                if (status == "error") {
                    alert('Произошла ошибка сервера подробности в консоли');
                    console.error('error load text: ' + xhr.status + " " + xhr.statusText);
                }
                initSortTable();
            });

        }

        function download() {
            let filter_data = $('#report_form').serialize();
            window.open(
                '{strtok($smarty.server.REQUEST_URI,'?')}?action=download&' + filter_data,
                '_blank' // <- This is what makes it open in a new window.
            );
        }


    </script>
{/capture}
