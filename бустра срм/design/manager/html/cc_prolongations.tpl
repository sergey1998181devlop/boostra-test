
{$meta_title='Мои задачи' scope=parent}

{capture name='page_scripts'}
<script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/prolongations.js?v=1.035"></script>
<script src="design/{$settings->theme|escape}/assets/plugins/moment/moment.js"></script>
<script src="design/{$settings->theme|escape}/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
<!-- Date range Plugin JavaScript -->
<script src="design/{$settings->theme|escape}/assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
<script src="design/{$settings->theme|escape}/assets/plugins/daterangepicker/daterangepicker.js"></script>

<script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/prtasks.app.js"></script>
<script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/movements.app.js"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script type="module" src="design/{$settings->theme|escape}/js/apps/collection.js?v=1.021"></script>
<script src="design/{$settings->theme|escape}/js/apps/cc_prolongations_helper.js?v=1.0"></script>
{* ============================================ *}
{* СЕКЦИЯ: Конфигурация для JavaScript          *}
{* ============================================ *}
<script>
    window.CCProlongationsConfig = {
        managerId: {$manager->id},
    tvMedicalPrice: {$tv_medical_price},
    managers: {$managers|json_encode}
    };
</script>
<script src="design/{$settings->theme|escape}/js/apps/cc_prolongations_page.js?v=1.1"></script>
{/capture}

{capture name='page_styles'}
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@4.2.0/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@4.2.0/main.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@4.2.0/main.js"></script>
<link href="https://cdn.jsdelivr.net/npm/@fullcalendar/core@4.2.0/main.min.css" rel="stylesheet"/>

<!-- Date picker plugins css -->
<link href="design/{$settings->theme|escape}/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
<!-- Daterange picker plugins css -->
<link href="design/{$settings->theme|escape}/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
<link href="design/{$settings->theme|escape}/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">

<link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid.min.css" />
<link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid-theme.min.css" />
<link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/css-chart/css-chart.css" />

<link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/css/cc_prolongations/modals.css" />
<link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/css/cc_prolongations/calendar.css" />
<link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/css/cc_prolongations/buttons.css" />
<link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/css/cc_prolongations/table.css" />
<link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/css/cc_prolongations/checkboxes.css" />
<link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/css/cc_prolongations/vox.css" />
<link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/css/cc_prolongations/sms.css" />
<link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/css/cc_prolongations/utilities.css" />
{/capture}


<style>
    @media screen and (min-width: 580px){
        body {
            padding-right: 0px !important;
        }
    }
</style>

<div class="modal-wrapper">
    <div class="modal-background"></div>
    <div class="div-modal">
        <div class="loader-icon"></div>
        <p>Заявки</p>
        <p class="employs-names"></p>
        <p class="third-p">будут распределены на оставшихся в смене</p>

        <div class="employs-names-buttons">
            <button class="employs-names-button-no btn btn-warning mr-3">Нет</button>
            <button class="employs-names-button-yes btn btn-success">Да</button>
        </div>
    </div>
</div>



<div class="modal-wrapper-vox">
    <div class="modal-background"></div>
    <div class="div-modal">

    </div>
</div>


<div class="page-wrapper">
    <!-- ============================================================== -->
    <!-- Container fluid  -->
    <!-- ============================================================== -->
    <div class="container-fluid">
        <!-- ============================================================== -->
        <!-- Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->

        {* ============================================ *}
        {* СЕКЦИЯ: Заголовок и фильтры                 *}
        {* ============================================ *}
        {include file='html_blocks/cc_prolongations/header.tpl'}

        <!-- ============================================================== -->
        <!-- End Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Start Page Content -->
        <!-- ============================================================== -->
        <div class="schedule-calendar-div" id="calendar-parent">
            <div class="left-side">
                <div class="left-side-buttons">
                    <button class="employs-button">Сотрудники</button>
                    <button> Дни просрочки</button>
                    <button class="employs-edit">Редактировать</button>
                </div>
                <div class="employs-div">

                </div>
            </div>
            <div id='calendar' style="width: 600px;"></div>
        </div>

        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <div class="clearfix js-filter-status">
                            <h4 class="card-title float-left">
                                Продление
                                {if $filter_period=='zero'}(день оплаты){/if}
                                {if $filter_period=='plus1'}(1 день просрочки){/if}
                                {if $filter_period=='minus1'}(1 день до просрочки){/if}
                                {if $filter_period=='minus3'}(3 дня до просрочки){/if}
                                {if $filter_period=='all'}(всего){/if}
                            </h4>
                            <div class="float-right div-float-right">
                                <div class="div-float-right-main" >
                                    <div>
                                        <button class="btn btn-success recall-robo-modal" data-toggle="modal" data-target="#recallRobo">Перезвон </button>
                                    </div>
                                </div>
                                <div>
                                    {if in_array($manager->role, ['developer', 'admin', 'opr', 'ts_operator'])}
                                    <form
                                            class="form_zero_payment mb-2"
                                            id="form_zero_days_payment"
                                            method="post"
                                            action="/ajax/send_to_vox_zero.php"
                                            style="float: left;display: flex;align-items: center;border: 1px solid #555555;border-radius: 6px;margin-right: 1rem;">

                                        <div class="check_box_block">
                                            <input type="checkbox" class="checkbox-mis color1 d-none" id="min5"
                                                   name="min5">
                                            <label class="labels-missing color1" for="min5">-5</label>
                                            <div class="input_block">
                                                <input type="text" name="min5_comp" class="form-control input-sm"
                                                       placeholder="ID"
                                                       value="{$settings->ccprolongations5|escape}">
                                            </div>
                                        </div>
                                        <div class="check_box_block">
                                            <input type="checkbox" class="checkbox-mis color1 d-none" id="min4"
                                                   name="min4">
                                            <label class="labels-missing color1" for="min4">-4</label>
                                            <div class="input_block">
                                                <input type="text" name="min4_comp" class="form-control input-sm"
                                                       placeholder="ID"
                                                       value="{$settings->ccprolongations4|escape}">
                                            </div>
                                        </div>

                                        <div class="check_box_block">
                                            <input type="checkbox" class="checkbox-mis color2 d-none" id="min3"
                                                   name="min3">
                                            <label class="labels-missing color2" for="min3">-3</label>
                                            <div class="input_block">
                                                <input type="text" name="min3_comp" class="form-control input-sm"
                                                       placeholder="ID"
                                                       value="{$settings->ccprolongations3|escape}">
                                            </div>
                                        </div>
                                        <div class="check_box_block">
                                            <input type="checkbox" class="checkbox-mis color2 d-none" id="min2"
                                                   name="min2">
                                            <label class="labels-missing color2" for="min2">-2</label>
                                            <div class="input_block">
                                                <input type="text" name="min2_comp" class="form-control input-sm"
                                                       placeholder="ID"
                                                       value="{$settings->ccprolongations2|escape}">
                                            </div>
                                        </div>
                                        <div class="check_box_block">
                                            <input type="checkbox" class="checkbox-mis color3 d-none" id="min1"
                                                   name="min1">
                                            <label class="labels-missing color3" for="min1">-1</label>
                                            <div class="input_block">
                                                <input type="text" name="min1_comp" class="form-control input-sm"
                                                       placeholder="ID"
                                                       value="{$settings->ccprolongations1|escape}">
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-secondary" onclick=""
                                                title="Отправить в VOX">
                                            Отправить в VOX <i class="fa fa-paper-plane" aria-hidden="true"></i>
                                        </button>

                                    </form>
                                    {/if}
                                    <div class="col-md-12 sms-report">
                                        <div class="input-group mb-3 mr-1">
                                            <input type="text" name="date_range" class="form-control sms-date"
                                                   value="{$request_date_from} - {$request_date_to}">
                                            <div class="input-group-append">
                                            <span class="input-group-text">
                                                <span class="ti-calendar"></span>
                                            </span>
                                            </div>
                                        </div>

                                        <button type="button" class="btn btn-sm btn-primary mb-3" id="send_message_to_users"
                                                onclick="getSmsToUsers();">
                                            Отчет о массовой отправке смс<i class=" far fa-share-square"></i>
                                        </button>
                                    </div>
                                    <div class=" input-group mb-3">
                                        <input type="number" class="form-control mr-1 days-count" placeholder="Напишите на сколько дней заблокировать">
                                        <button type="button" class="btn btn-sm btn-primary js-blacklist-call-user input-group-append" data-manager = {$manager->id}>
                                            Блокировать звонки клиентам </i>
                                        </button>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-info" id="send_push_to_users" onclick="sendPushToUsers();" title="Отправить уведомления">
                                        Отправка push/sms<i class=" far fa-share-square"></i>
                                    </button>
                                    <a href="{url period='minus3' page=null}" class="btn btn-xs {if $filter_period=='minus3'}btn-success{else}btn-outline-success{/if}">Минус 3</a>
                                    <a href="{url period='minus1' page=null}" class="btn btn-xs {if $filter_period=='minus1'}btn-info{else}btn-outline-info{/if}">Минус 1</a>
                                    <a href="{url period='zero' page=null}" class="btn btn-xs {if $filter_period=='zero'}btn-warning{else}btn-outline-warning{/if}">Ноль</a>
                                    <a href="{url period='plus1' page=null}" class="btn btn-xs {if $filter_period=='plus1'}btn-danger{else}btn-outline-danger{/if}">Плюс 1</a>
                                    <a href="{url period='all' page=null}" class="btn btn-xs {if $filter_period=='all'}btn-primary{else}btn-outline-primary{/if}">Всего</a>
                                    {if $filter_period}
                                    <input type="hidden" value="{$filter_period}" id="period" />
                                    {/if}
                                </div>
                            </div>

                            {* ============================================ *}
                            {* СЕКЦИЯ: Таблица задач                        *}
                            {* ============================================ *}
                            {include file='html_blocks/cc_prolongations/tasks_table.tpl'}
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
{* ============================================ *}
{* СЕКЦИЯ: Модальные окна                      *}
{* ============================================ *}
{include file='html_blocks/cc_prolongations/modals.tpl'}