{$meta_title='Воронка продаж ШКД ФинДзен' scope=parent}

{capture name='page_styles'}
    <link href="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="design/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">
{/capture}

<style>
    .metric-card {
        border-radius: 10px;
        padding: 20px;
        color: white;
        margin-bottom: 20px;
        min-height: 100px;
    }
    .metric-card h3 {
        margin: 0;
        font-size: 2.5rem;
        font-weight: bold;
    }
    .metric-card p {
        margin: 5px 0 0;
        opacity: 0.9;
        font-size: 12px;
    }
    .metric-1 { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
    .metric-2 { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .metric-3 { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
    .metric-4 { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
    .funnel-table th {
        background-color: #272c33;
        color: white;
    }
    .funnel-row-final {
        background-color: #d4edda !important;
    }
</style>

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-file-chart"></i>
                    <span>Воронка продаж ШКД ФинДзен</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Воронка ШКД ФинДзен</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Фильтры отчёта</h4>
                        <form id="report_form">
                            <div class="row">
                                <div class="col-6 col-md-3">
                                    <div class="input-group mb-3">
                                        <input type="text" name="date_range" class="form-control daterange" value="{if $dateStart}{$dateStart|date_format:'%Y.%m.%d'} - {$dateEnd|date_format:'%Y.%m.%d'}{/if}">
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <span class="ti-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <button onclick="return loadData();" type="button" class="btn btn-info"><i class="ti-reload"></i> Сформировать</button>
                                    <button onclick="return download();" type="button" class="btn btn-success"><i class="ti-save"></i> Выгрузить</button>
                                </div>
                            </div>
                        </form>

                        <div id="result">
                            <div class="row">
                                <div class="col-md-3 col-sm-6">
                                    <div class="metric-card metric-1">
                                        <h3>{$results.overdue_9plus_clients|default:0}</h3>
                                        <p>Клиенты с просрочкой 9+ дней</p>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="metric-card metric-2">
                                        <h3>{$results.finzen_charges|default:0}</h3>
                                        <p>Начисления ШКД (переход на ФинДзен)</p>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="metric-card metric-3">
                                        <h3>{$results.bot_clicks|default:0}</h3>
                                        <p>Использования бота</p>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="metric-card metric-4">
                                        <h3>{$results.active_users|default:0}</h3>
                                        <p>Активные пользователи бота</p>
                                    </div>
                                </div>
                            </div>

                            <table class="table table-bordered table-hover funnel-table mt-4">
                                <thead>
                                    <tr>
                                        <th>Этап воронки</th>
                                        <th>Количество</th>
                                        <th>Конверсия</th>
                                        <th>Описание</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>1. Клиенты с просрочкой 9+</strong></td>
                                        <td><strong>{$results.overdue_9plus_clients|default:0}</strong></td>
                                        <td>100%</td>
                                        <td>База клиентов с просрочкой 9+ дней за выбранный период</td>
                                    </tr>
                                    <tr>
                                        <td><strong>2. Начисления ШКД</strong></td>
                                        <td><strong>{$results.finzen_charges|default:0}</strong></td>
                                        <td>
                                            {if $results.overdue_9plus_clients > 0}
                                                {($results.finzen_charges / $results.overdue_9plus_clients * 100)|number_format:1}%
                                            {else}
                                                0%
                                            {/if}
                                        </td>
                                        <td>Переход на сайт ФинДзен (оплата КД)</td>
                                    </tr>
                                    <tr>
                                        <td><strong>3. Использования бота</strong></td>
                                        <td><strong>{$results.bot_clicks|default:0}</strong></td>
                                        <td>
                                            {if $results.finzen_charges > 0}
                                                {($results.bot_clicks / $results.finzen_charges * 100)|number_format:1}%
                                            {else}
                                                0%
                                            {/if}
                                        </td>
                                        <td>Клики/создание ID в Telegram боте</td>
                                    </tr>
                                    <tr class="funnel-row-final">
                                        <td><strong>4. Активные пользователи</strong></td>
                                        <td><strong>{$results.active_users|default:0}</strong></td>
                                        <td>
                                            {if $results.bot_clicks > 0}
                                                {($results.active_users / $results.bot_clicks * 100)|number_format:1}%
                                            {else}
                                                0%
                                            {/if}
                                        </td>
                                        <td>Уникальные клиенты, написавшие боту 1+ сообщений</td>
                                    </tr>
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

{capture name='page_scripts'}
    <script src="design/manager/assets/plugins/moment/moment.js"></script>
    <script src="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script src="design/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    <script>
        $(function(){
            $('.daterange').daterangepicker({
                autoApply: true,
                locale: {
                    format: 'YYYY.MM.DD'
                },
                default: moment(),
            });
        });

        function loadData(){
            $('.preloader').show();
            let filter_data = $('#report_form').serialize();
            $("#result").load('{$smarty.server.REQUEST_URI}?ajax=1&' + filter_data + ' #result', function (response, status, xhr) {
                $('.preloader').hide();
                if (status == "error") {
                    alert('Произошла ошибка сервера, подробности в консоли');
                    console.error('error load text: ' + xhr.status + " " + xhr.statusText);
                }
            });
        }

        function download() {
            let filter_data = $('#report_form').serialize();
            window.open(
                '{$smarty.server.REQUEST_URI}?action=download&' + filter_data,
                '_blank'
            );
            return false;
        }
    </script>
{/capture}
