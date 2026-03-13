{$meta_title='Список кодов участников новогодней акции' scope=parent}

{capture name='page_scripts'}
    <script src="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/fancybox3/dist/jquery.fancybox.js"></script>
    <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/promocodes.js?v=1.0"></script>
{/capture}

{capture name='page_styles'}
    <link href="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css"
          rel="stylesheet"/>
    <link href="design/{$settings->theme|escape}/assets/plugins/fancybox3/dist/jquery.fancybox.css" rel="stylesheet"/>
    <link type="text/css" rel="stylesheet"
          href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid.min.css"/>
    <link type="text/css" rel="stylesheet"
          href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid-theme.min.css"/>
    <style>
        .jsgrid-table {
            margin-bottom: 0
        }

        #level_select {
            width: 115px;
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
                    <i class="mdi mdi-animation"></i> Список кодов
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Список кодов</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                <form id="report_form">
                    <div class="col-6 mb-3 d-flex flex-column">
                        <label for="level_select">Выберите уровень кода для выгрузки:</label>
                        <select class="form-control mb-3" id="level_select" name="level">
                            <option value="">Все уровни</option>
                            <option value="1">Уровень 1</option>
                            <option value="2">Уровень 2</option>
                        </select>

                    </div>
                    <div class="col-6 col-md-4 mb-3">
                        <button type="button" class="btn btn-success" id="download_button"><i class="ti-save"></i> Выгрузить</button>
                    </div>
                </form>
            </div>
            <div class="col-6">
                <div class="alert alert-info" role="alert">
                    Количество клиентов уровня 1: {$count_level_1}<br>
                    Количество клиентов уровня 2: {$count_level_2}
                </div>
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
                        <div class="clearfix">
                            <h4 class="card-title float-left">Список кодов</h4>
                        </div>
                        <div id="basicgrid" class="jsgrid" style="position: relative; width: 100%;">
                            <table class="jsgrid-table table table-striped table-hover">
                                <thead>
                                <tr class="jsgrid-header-row">
                                    <th>ID</th>
                                    <th>ФИО</th>
                                    <th>Номер телефона</th>
                                    <th>Код участника</th>
                                    <th>Дата генерации кода</th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach $items as $code}
                                    <tr class="jsgrid-row js-promo-row">
                                        <td>{$code->id}</td>
                                        <td><a target="_blank"
                                               href="client/{$code->user_id}">{$code->lastname} {$code->firstname} {$code->patronymic}</a>
                                        </td>
                                        <td>+{$code->phone_mobile}</td>
                                        <td>{$code->code}</td>
                                        <td>{$code->updated_at}</td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>

                            {include file="html_blocks/pagination.tpl"}

                            <div class="jsgrid-load-shader"
                                 style="display: none; position: absolute; inset: 0px; z-index: 10;">
                            </div>
                            <div class="jsgrid-load-panel"
                                 style="display: none; position: absolute; top: 50%; left: 50%; z-index: 1000;">
                                Идет загрузка...
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
{literal}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('download_button').addEventListener('click', function () {
                const levelSelect = document.getElementById('level_select');
                const level = levelSelect ? levelSelect.value : '';

                const requestBody = new URLSearchParams();
                requestBody.append('action', 'download');
                requestBody.append('level', level);
                fetch('ajax/newyear_action.php', {
                    method: 'POST',
                    body: requestBody.toString(),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.blob();
                    })
                    .then(blob => {
                        const url = window.URL.createObjectURL(new Blob([blob]));
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'new_year_participant_codes.xls';
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        window.URL.revokeObjectURL(url);
                    })
                    .catch(error => {
                        console.error('There has been a problem with your fetch operation:', error);
                    });
            });
        });


    </script>
{/literal}