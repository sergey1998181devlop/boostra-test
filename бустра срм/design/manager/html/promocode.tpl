{capture name='page_scripts'}

    <script src="design/manager/assets/plugins/moment/moment.js"></script>
    
    <script src="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="design/manager/assets/plugins//inputmask/dist/min/jquery.inputmask.bundle.min.js"></script>
    <!-- Date range Plugin JavaScript -->
    <script src="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script src="design/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    <script>
    $(function(){
        $('.daterange').daterangepicker({
            autoApply: true,
            singleDatePicker: true,
            showDropdowns: true,
            locale: {
                format: 'DD.MM.YYYY'
            },
            default:''
        });
        $("input[name='phone']").inputmask("+7 (999) 999-99-99");
    })
    </script>
{/capture}

{capture name='page_styles'}
    
    <link href="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    <!-- Daterange picker plugins css -->
    <link href="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="design/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">

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
                    {if $promocode}Настройки промокода {$promocode->promocode|escape} (Время жизни промокода 7 дней, до 23:59 мск)
                    {else}Создать новый промокод (Время жизни промокода 7 дней, до 23:59 мск){/if}
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="promocodes">Промокоды</a></li>
                    <li class="breadcrumb-item active">Промокод</li>
                </ol>
            </div>
        </div>

        <ul class="mt-2 nav nav-tabs" role="tablist">
            <li class="nav-item"> 
                <a class="nav-link active" data-toggle="tab" href="#info" role="tab" aria-selected="true">
                    <span class="hidden-sm-up"><i class="ti-home"></i></span> 
                    <span class="hidden-xs-down">Основные</span>
                </a> 
            </li>
        </ul>

        <div class="tab-content ">
            
            <div id="info" class="tab-pane active" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 col-lg-8 col-xlg-9">
                                <div class="card">
                                    <div class="card-body">
                                        <form class="form-horizontal" method="POST">
                                            
                                            {if $errors}
                                            <div class="col-md-12">
                                                <ul class="alert alert-danger">
                                                    {if in_array('empty_date', (array)$errors)}<li>Выберите дату начала действия!</li>{/if}
                                                    {if in_array('empty_title', (array)$errors)}<li>Укажите описание!</li>{/if}
                                                </ul>
                                            </div>
                                            {/if}
                                            <div class="form-group col-md-12 {if in_array('empty_title', (array)$errors)}has-danger{/if}">
                                                <label class="col-md-12">Описание</label>
                                                <div class="col-md-12">
                                                    <input
                                                        type="text"
                                                        name="title"
                                                        value="{$promocode->title|escape}"
                                                        class="form-control form-control-line"
                                                        required="true" />
                                                    {if in_array('empty_name', (array)$errors)}<small class="form-control-feedback">Укажите описание!</small>{/if}
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6 float-md-left">
                                                {if $promocode && $promocode->manager}
                                                    <div class="form-group">
                                                        <label class="col-md-12">Менеджер</label>
                                                        <div class="col-md-12">
                                                            <input
                                                                type="text"
                                                                name="manager"
                                                                value="{$promocode->manager|escape}"
                                                                class="form-control form-control-line"
                                                                disabled="true"
                                                                />
                                                        </div>
                                                    </div>
                                                {/if}
                                                <div class="form-group {if in_array('empty_rate', (array)$errors)}has-danger{/if}">
                                                    <label class="col-md-12">Ставка</label>
                                                    <div class="col-md-12">
                                                        <input
                                                            type="text"
                                                            name="rate"
                                                            value="{$promocode->rate}"
                                                            class="form-control form-control-line"
                                                            required="true" />
                                                        {if in_array('empty_rate', (array)$errors)}
                                                            <small class="form-control-feedback">Укажите ставку!</small>
                                                        {/if}
                                                    </div>
                                                </div>
                                                <div class="form-group {if in_array('empty_date', (array)$errors)}has-danger{/if}">
                                                    <label for="date_start" class="col-md-12">Начало действия</label>
                                                    <div class="col-md-12">
                                                        <input
                                                            type="text"
                                                            name="date_start"
                                                            value="{$promocode->date_start}"
                                                            class="form-control form-control-line daterange"
                                                            required="true" />
                                                        {if in_array('empty_date', (array)$errors)}
                                                            <small class="form-control-feedback">Укажите дату начала действия!</small>
                                                        {/if}
                                                    </div>
                                                </div>
                                                <div class="form-group {if in_array('date_end', (array)$errors)}has-danger{/if}">
                                                    <label for="date_start" class="col-md-12">Дата окончания</label>
                                                    <div class="col-md-12">
                                                        <input
                                                                type="text"
                                                                name="date_end"
                                                                value="{$promocode->date_end}"
                                                                class="form-control form-control-line daterange"
                                                                required="true" />
                                                        {if in_array('date_end', (array)$errors)}
                                                            <small class="form-control-feedback">Укажите дату окончания!</small>
                                                        {/if}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6 float-right">
                                                {if $promocode && $promocode->id}
                                                <div class="form-group">
                                                    <label class="col-md-12">Промокод</label>
                                                    <div class="col-md-12">
                                                        <input
                                                            type="text"
                                                            name="promocode"
                                                            value="{$promocode->promocode|escape}"
                                                            class="form-control form-control-line"
                                                            disabled="true"
                                                            />
                                                    </div>
                                                </div>
                                                {/if}
                                                <div class="form-group">
                                                    <label class="col-md-12">Номер телефона (для персонального помокода)</label>
                                                    <div class="col-md-12">
                                                        <input
                                                            type="text"
                                                            name="phone"
                                                            value="{$promocode->phone}"
                                                            class="form-control form-control-line"
                                                        />
                                                    </div>
                                                </div>
                                                <div class="form-group ">
                                                    <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                                        <input
                                                            type="checkbox"
                                                            class="custom-control-input"
                                                            id="quantity"
                                                            name="quantity"
                                                            value="1"
                                                            {if $promocode->quantity == 1}checked="true"{/if}
                                                        >
                                                        <label class="custom-control-label ml-3" for="quantity">Одноразовый промокод</label>
                                                    </div>
                                                </div>
                                            </div>
                                            {if !$promocode || !$promocode->id}
                                                <div class="col-12 form-group overflow-hidden">
                                                    <div class="col-sm-12">
                                                        <button class="btn btn-success" type="submit">Создать</button>
                                                    </div>
                                                </div>
                                            {/if}
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>

    </div>
    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->
    {include file='footer.tpl'}
    <!-- ============================================================== -->
</div>