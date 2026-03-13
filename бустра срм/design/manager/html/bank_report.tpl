{$meta_title='Отчетность' scope=parent}

{capture name='page_scripts'}

    <script src="design/manager/assets/plugins/moment/moment.js"></script>
    
    <script src="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <!-- Date range Plugin JavaScript -->
    <script src="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script src="design/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    <script>
    $(function(){
        $('.js-run-report').click(function(e){
            e.preventDefault();
            
            $('.js-loading-block').show();
            $('.js-form-block').hide();
            
            var _scale = 0;
            var $pb = $('.js-progress-bar');
            
            var _interval = setInterval(function(){
                _scale += Math.floor(Math.random() * 5);;
                if (_scale >= 100) {
                    _scale = 100;
                    clearInterval(_interval);

                    $('.js-loading-block').hide();
                    $('.js-loaded-block').show();
                }
                
                $pb.attr('aria-valuenow', _scale);
                $pb.css('width', _scale+'%');
            }, 1000);
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
    }
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
                    <span>Отчетность</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Отчетность</li>
                </ol>
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
                    <div class="card-body">
                        <form>
                            <div class="row js-form-block">
                                <div class="col-6 col-md-4">
                                    <div class="input-group mb-3">
                                        <select class="form-control" name="type">
                                            <option value="" selected="">Основной</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <button type="submit" class="btn btn-info js-run-report">Получить отчет</button>
                                </div>
                            </div>
                            <div class="row loading_block js-loading-block pb-5" style="display:none">
                                <div class="col-12">
                                    <h4 class="card-title">Подождите, отчет формируется</h4>
                                    <div class="progress mt-3">
                                        <div class="js-progress-bar progress-bar bg-info progress-bar-striped" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%; height:20px;" role="progressbar">  
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row loading_block js-loaded-block pb-5" style="display:none">
                                <div class="col-12 pt-3">
                                    <a href="files/reports/bank_report.xlsx" target="_blank" class="btn btn-lg btn-info "><i class="fas fa-file-excel"></i> Скачать отчет</a>
                                </div>
                            </div>
                            
                        </form>                        
                        
                        
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