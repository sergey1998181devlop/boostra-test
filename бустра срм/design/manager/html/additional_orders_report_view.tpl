{$meta_title='Отчёт - по допам' scope=parent}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-file-chart"></i>
                    <span>Отчет по допам</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Отчет по допам</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Отчет по допам</h4>

                        <form>
                            <div class="row">
                                <div class="col-4 col-md-6">
                                    <div class="input-group mb-3">
                                        <input type="text" name="daterange" class="form-control daterange" value="{if $from && $to}{$from}-{$to}{/if}">
                                        <div class="input-group-append">
                                                <span class="input-group-text">
                                                    <span class="ti-calendar"></span>
                                                </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-8 col-md-6">
                                    <button type="submit" class="btn btn-info mr-5">Сформировать</button>
                                    <button onclick="return download();" type="button" class="btn btn-success"><i class="ti-save"></i> Выгрузить</button>
                                </div>
                            </div>
                        </form>

                        <div id="result" class="">
                            <table class="iksweb table table-bordered table-hover">
                                <tr>
                                    <td></td>
                                    <td colspan="7">Количество</td>
                                    <td colspan="7">Сумма</td>
                                </tr>
                                <tr>
                                    <td>{$from} - {$to}</td>
                                    <td colspan="2">Продано</td>
                                    <td colspan="2">Возвращено</td>
                                    <td colspan="2">% возврата</td>
                                    <td>% возврата общий</td>
                                    <td colspan="2">Продано</td>
                                    <td colspan="2">Возвращено</td>
                                    <td colspan="2">% возврата</td>
                                    <td>% возврата общий</td>
                                </tr>
                                <tr>
                                    <td><b><u>Выдача</u></b></td>
                                    <td><b>НК</b></td>
                                    <td><b>ПК</b></td>
                                    <td><b>НК</b></td>
                                    <td><b>ПК</b></td>
                                    <td><b>НК</b></td>
                                    <td><b>ПК</b></td>
                                    <td></td>
                                    <td><b>НК</b></td>
                                    <td><b>ПК</b></td>
                                    <td><b>НК</b></td>
                                    <td><b>ПК</b></td>
                                    <td><b>НК</b></td>
                                    <td><b>ПК</b></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Кредитный доктор</td>
                                    <td>{$items['count']['sold']['issuance']['s_credit_doctor_to_user'][0]}</td>
                                    <td>{$items['count']['sold']['issuance']['s_credit_doctor_to_user'][1]}</td>
                                    <td>{$items['count']['returned']['issuance']['s_credit_doctor_to_user'][0]}</td>
                                    <td>{$items['count']['returned']['issuance']['s_credit_doctor_to_user'][1]}</td>
                                    <td>{$items['count']['returned_percent']['issuance']['s_credit_doctor_to_user'][0]}</td>
                                    <td>{$items['count']['returned_percent']['issuance']['s_credit_doctor_to_user'][1]}</td>
                                    <td>{$items['count']['returned_percent_total']['issuance']['s_credit_doctor_to_user'][0]}</td>
                                    <td>{$items['sum']['sold']['issuance']['s_credit_doctor_to_user'][0]}</td>
                                    <td>{$items['sum']['sold']['issuance']['s_credit_doctor_to_user'][1]}</td>
                                    <td>{$items['sum']['returned']['issuance']['s_credit_doctor_to_user'][0]}</td>
                                    <td>{$items['sum']['returned']['issuance']['s_credit_doctor_to_user'][1]}</td>
                                    <td>{$items['sum']['returned_percent']['issuance']['s_credit_doctor_to_user'][0]}</td>
                                    <td>{$items['sum']['returned_percent']['issuance']['s_credit_doctor_to_user'][1]}</td>
                                    <td>{$items['sum']['returned_percent_total']['issuance']['s_credit_doctor_to_user'][0]}</td>
                                </tr>
                                <tr>
                                    <td><b><u>Пролонгация</u></b></td>
                                    <td><b>НК</b></td>
                                    <td><b>ПК</b></td>
                                    <td><b>НК</b></td>
                                    <td><b>ПК</b></td>
                                    <td><b>НК</b></td>
                                    <td><b>ПК</b></td>
                                    <td></td>
                                    <td><b>НК</b></td>
                                    <td><b>ПК</b></td>
                                    <td><b>НК</b></td>
                                    <td><b>ПК</b></td>
                                    <td><b>НК</b></td>
                                    <td><b>ПК</b></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Консьерж сервис</td>
                                    <td>{$items['count']['sold']['prolongation']['s_multipolis'][0]}</td>
                                    <td>{$items['count']['sold']['prolongation']['s_multipolis'][1]}</td>
                                    <td>{$items['count']['returned']['prolongation']['s_multipolis'][0]}</td>
                                    <td>{$items['count']['returned']['prolongation']['s_multipolis'][1]}</td>
                                    <td>{$items['count']['returned_percent']['prolongation']['s_multipolis'][0]}</td>
                                    <td>{$items['count']['returned_percent']['prolongation']['s_multipolis'][1]}</td>
                                    <td>{$items['count']['returned_percent_total']['prolongation']['s_multipolis'][0]}</td>
                                    <td>{$items['sum']['sold']['prolongation']['s_multipolis'][0]}</td>
                                    <td>{$items['sum']['sold']['prolongation']['s_multipolis'][1]}</td>
                                    <td>{$items['sum']['returned']['prolongation']['s_multipolis'][0]}</td>
                                    <td>{$items['sum']['returned']['prolongation']['s_multipolis'][1]}</td>
                                    <td>{$items['sum']['returned_percent']['prolongation']['s_multipolis'][0]}</td>
                                    <td>{$items['sum']['returned_percent']['prolongation']['s_multipolis'][1]}</td>
                                    <td>{$items['sum']['returned_percent_total']['prolongation']['s_multipolis'][0]}</td>
                                </tr>
                                <tr>
                                    <td>Вита-мед</td>
                                    <td>{$items['count']['sold']['prolongation']['s_tv_medical_payments'][0]}</td>
                                    <td>{$items['count']['sold']['prolongation']['s_tv_medical_payments'][1]}</td>
                                    <td>{$items['count']['returned']['prolongation']['s_tv_medical_payments'][0]}</td>
                                    <td>{$items['count']['returned']['prolongation']['s_tv_medical_payments'][1]}</td>
                                    <td>{$items['count']['returned_percent']['prolongation']['s_tv_medical_payments'][0]}</td>
                                    <td>{$items['count']['returned_percent']['prolongation']['s_tv_medical_payments'][1]}</td>
                                    <td>{$items['count']['returned_percent_total']['prolongation']['s_tv_medical_payments'][0]}</td>
                                    <td>{$items['sum']['sold']['prolongation']['s_tv_medical_payments'][0]}</td>
                                    <td>{$items['sum']['sold']['prolongation']['s_tv_medical_payments'][1]}</td>
                                    <td>{$items['sum']['returned']['prolongation']['s_tv_medical_payments'][0]}</td>
                                    <td>{$items['sum']['returned']['prolongation']['s_tv_medical_payments'][1]}</td>
                                    <td>{$items['sum']['returned_percent']['prolongation']['s_tv_medical_payments'][0]}</td>
                                    <td>{$items['sum']['returned_percent']['prolongation']['s_tv_medical_payments'][1]}</td>
                                    <td>{$items['sum']['returned_percent_total']['prolongation']['s_tv_medical_payments'][0]}</td>
                                </tr>
                                <tr>
                                    <td><b><u>Закрытие</u></b></td>
                                    <td><b>НК</b></td>
                                    <td><b>ПК</b></td>
                                    <td><b>НК</b></td>
                                    <td><b>ПК</b></td>
                                    <td><b>НК</b></td>
                                    <td><b>ПК</b></td>
                                    <td></td>
                                    <td><b>НК</b></td>
                                    <td><b>ПК</b></td>
                                    <td><b>НК</b></td>
                                    <td><b>ПК</b></td>
                                    <td><b>НК</b></td>
                                    <td><b>ПК</b></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Вита-мед</td>
                                    <td>{$items['count']['sold']['closing']['s_tv_medical_payments'][0]}</td>
                                    <td>{$items['count']['sold']['closing']['s_tv_medical_payments'][1]}</td>
                                    <td>{$items['count']['returned']['closing']['s_tv_medical_payments'][0]}</td>
                                    <td>{$items['count']['returned']['closing']['s_tv_medical_payments'][1]}</td>
                                    <td>{$items['count']['returned_percent']['closing']['s_tv_medical_payments'][0]}</td>
                                    <td>{$items['count']['returned_percent']['closing']['s_tv_medical_payments'][1]}</td>
                                    <td>{$items['count']['returned_percent_total']['closing']['s_tv_medical_payments'][0]}</td>
                                    <td>{$items['sum']['sold']['closing']['s_tv_medical_payments'][0]}</td>
                                    <td>{$items['sum']['sold']['closing']['s_tv_medical_payments'][1]}</td>
                                    <td>{$items['sum']['returned']['closing']['s_tv_medical_payments'][0]}</td>
                                    <td>{$items['sum']['returned']['closing']['s_tv_medical_payments'][1]}</td>
                                    <td>{$items['sum']['returned_percent']['closing']['s_tv_medical_payments'][0]}</td>
                                    <td>{$items['sum']['returned_percent']['closing']['s_tv_medical_payments'][1]}</td>
                                    <td>{$items['sum']['returned_percent_total']['closing']['s_tv_medical_payments'][0]}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {include file='footer.tpl'}
</div>

{capture name='page_styles'}
    <link href="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="design/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">
    <style>
        tr.small td {
            padding: 0.25rem;
        }
        .table thead th,
        .table th {
            border: 1px solid;
            font-size: 10px;
        }
        thead.position-sticky {
            top: 0;
            background-color: #272c33;
        }
        table.iksweb {
            width: 100%;
            border-collapse:collapse;
            border-spacing:0;
            height: auto;
            text-align: center;
        }
        table.iksweb,
        table.iksweb td,
        table.iksweb th {
            border: 1px solid #595959;
        }
        table.iksweb td,
        table.iksweb th {
            padding: 3px;
            width: 30px;
            height: 35px;
        }
        table.iksweb th {
            background: #4e6982;
            color: #fff;
            font-weight: normal;
        }
    </style>
{/capture}

{capture name='page_scripts'}
    <script src="design/manager/assets/plugins/moment/moment.js"></script>
    <script src="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script src="design/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    <script>
        function download() {
            window.open(
                '{$reportUri}?action=download&daterange={$from}-{$to}',
                '_blank'
            );
            return false;
        }

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