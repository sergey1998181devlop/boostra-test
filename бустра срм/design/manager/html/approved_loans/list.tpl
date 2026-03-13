{$meta_title='Ордера' scope=parent}

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
    
    <script>
        $(function(){
            $(document).on('click', '.js-open-day', function(e){
                e.preventDefault();
                
                if ($(this).hasClass('open'))
                {
                    $('.js-details').remove();
                    $(this).removeClass('open');
                    return false;
                }
                
                var $this = $(this);
                var _day = $(this).data('day');
                var _scorista = $('#scorista_select').val();
                
                $.ajax({
                    url: 'lead_report',
                    data: {
                        day: _day,
                        action: 'details',
                        scorista: _scorista,
                    },
                    beforeSend: function(){
                        $this.addClass('open');
                    },
                    success: function(resp){
                        $('.js-details').remove();
                        var $details = $(resp).find('.js-details')
console.log($details)
                        $this.closest('tr').after($details);
                    }
                })
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
                <h3 class="text-themecolor mb-0 mt-0"><i class="mdi mdi-closed-caption"></i> заявки</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">заявки</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">

            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Start Page Content -->
        <!-- ============================================================== -->
        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                        <h4 class="card-title">Отчет за период {if $date_from}{$date_from|date} - {$date_to|date}{/if}</h4>
                        <form>
                            <div class="row">
                                <div class="col-6 col-md-4">
                                    <div class="input-group mb-3">
                                        <input type="hidden" name="module" value="ApprovedLoans">
                                        <input type="hidden" name="page" value="{$current_page_num}">
                                        <input type="text" name="daterange" class="form-control daterange" value="{if $from && $to}{$from}-{$to}{/if}">
                    
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <span class="ti-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <button type="submit" class="btn btn-info">Сформировать</button>
                                </div>
                                {if $date_from || $date_to}
                                <div class="col-12 col-md-4 text-right">
                                    <a href="http://45.147.176.183/excel?aproveds=1&date_from={$date_from}&date_to={$date_to}" class="btn btn-success ">
                                        <i class="fas fa-file-excel"></i> Скачать
                                    </a>
                                </div>
                                {/if}
                            </div>
                            
                        </form>  
                    <div class="card-body">
                        <h4 class="card-title">Заявки</h4>
                        <a href="http://45.147.176.183/excel?aproveds=1">excel</a>
                        <div id="basicgrid" class="jsgrid" style="position: relative; width: 100%;">
                            <div class="jsgrid-grid-body">
                                <table class="jsgrid-table table table-striped table-hover ">
                                    <tr>
                                        <th scope="col">номер займа</th>
                                        <th scope="col">Дата оформления</th>
                                        <th scope="col">Дата присвоения статуса "одобрен"</th>
                                        <th scope="col">Дата подписания договора</th>
                                        <th scope="col">Ошибка/Ответ от 1с при подписании договора</th>
                                        <th scope="col">Количество ошибок при подписании договора</th>
                                    </tr>
                                    <tbody>
                                    {foreach $approved_loans as $loan}
                                    <tr>
                                        <td>{$loan->id}</td>
                                        <td>{$loan->date}</td>
                                        <td>{$loan->approve_date}</td>
                                        <td>{$loan->confirm_date}</td>
                                        <td>{$loan->pay_result}</td>
                                        <td>{$loan->number_of_signing_errors}</td>
                                    </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                                {include file="../tickets/pagination.tpl"}
                            </div>
                        </div>
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