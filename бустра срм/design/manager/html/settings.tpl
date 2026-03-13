{$meta_title = 'Настройки' scope=parent}

{capture name='page_scripts'}

    <script src="design/{$settings->theme}/assets/plugins/nestable/jquery.nestable.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            // Nestable
            var updateOutput = function(e) {
                var list = e.length ? e : $(e.target),
                    output = list.data('output');
                if (window.JSON) {
                    output.val(window.JSON.stringify(list.nestable('serialize'))); //, null, 2));
                } else {
                    output.val('JSON browser support required for this demo.');
                }
            };

            $('#nestable2').nestable({
                group: 1
            }).on('change', updateOutput);

            updateOutput($('#nestable2').data('output', $('#nestable2-output')));

        });
    </script>

{/capture}

{capture name='page_styles'}

    <!--nestable CSS -->
    <link href="design/{$settings->theme}/assets/plugins/nestable/nestable.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        .onoffswitch {
            display:inline-block!important;
            vertical-align:top!important;
            width:60px!important;
            text-align:left;
        }
        .onoffswitch-switch {
            right:38px!important;
            border-width:1px!important;
        }
        .onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-switch {
            right:0px!important;
        }
        .onoffswitch-label {
            margin-bottom:0!important;
            border-width:1px!important;
        }
        .onoffswitch-inner::after,
        .onoffswitch-inner::before {
            height:18px!important;
            line-height:18px!important;
        }
        .onoffswitch-switch {
            width:20px!important;
            margin:1px!important;
        }
        .onoffswitch-inner::before {
            content:'ВКЛ'!important;
            padding-left: 10px!important;
            font-size:10px!important;
        }
        .onoffswitch-inner::after {
            content:'ВЫКЛ'!important;
            padding-right: 6px!important;
            font-size:10px!important;
        }

        .scoring-content {
            position:relative;
            z-index:999;
            border:1px solid rgba(120, 130, 140, 0.13);;
            border-top:0;
            background:#383f48;
            border-bottom-left-radius:4px;
            border-bottom-right-radius:4px;
            margin-top: -5px;
        }

        .collapsed .fa-minus-circle::before {
            content: "\f055";
        }
        h4.text-white {
            display:inline-block
        }
        .move-zone {
            display:inline-block;
            color:#fff;
            padding-right:15px;
            margin-right:10px;
            border-right:1px solid #30b2ff;
            cursor:move
        }
        .move-zone span {
            font-size:24px;
        }

        .dd {
            max-width:100%;
        }

        small.label {
            margin-left:10px;
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
                    Настройки
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Настройки</li>
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
        <!-- Row -->
        <form class="" method="POST" >

            <div class="row grid-stack" data-gs-width="12" data-gs-animate="yes">

                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                <input name="captcha_status" type="checkbox" class="custom-control-input js-settings-checkbox" id="captcha_status" value="1" {if $captcha_status}checked{/if} />
                                <label class="custom-control-label" for="captcha_status">
                                    ReCaptcha Вкл.
                                </label>
                            </div>
                            <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                <input name="new_flow_enabled" type="checkbox" class="custom-control-input js-settings-checkbox" id="new_flow_enabled" value="1" {if $new_flow_enabled}checked{/if} />
                                <label class="custom-control-label" for="new_flow_enabled">
                                    Новый флоу
                                </label>
                            </div>
                            <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                <input name="check_reports_for_loans_enable" type="checkbox" class="custom-control-input js-settings-checkbox" id="check_reports_for_loans_enable" value="1" {if $check_reports_for_loans_enable}checked{/if} />
                                <label class="custom-control-label" for="check_reports_for_loans_enable">
                                    Проверять актуальность ССП и КИ отчетов при выдаче займов
                                </label>
                            </div>
                            <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                <input name="auto_confirm_for_auto_approve_orders_enable" type="checkbox" class="custom-control-input js-settings-checkbox" id="auto_confirm_for_auto_approve_orders_enable" value="1" {if $auto_confirm_for_auto_approve_orders_enable}checked{/if} />
                                <label class="custom-control-label" for="auto_confirm_for_auto_approve_orders_enable">
                                    Авто-подтверждение авто-одобренных заявок
                                </label>
                            </div>
                            <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                <input name="need_notify_user_when_scorista_success" type="checkbox" class="custom-control-input js-settings-checkbox" id="need_notify_user_when_scorista_success" value="1" {if $need_notify_user_when_scorista_success}checked{/if} />
                                <label class="custom-control-label" for="need_notify_user_when_scorista_success">
                                    Отправлять смс при одобрении скористы
                                </label>
                            </div>
                            <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                <input name="bonon_enabled" type="checkbox" class="custom-control-input js-settings-checkbox" id="bonon_enabled" value="1" {if $bonon_enabled}checked{/if} />
                                <label class="custom-control-label" for="bonon_enabled">
                                    Продажа карт отказных НК клиентов (Bonon)
                                </label>
                            </div>
                            <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                <input name="short_flow_enabled" type="checkbox" class="custom-control-input js-settings-checkbox" id="short_flow_enabled" value="1" {if $short_flow_enabled}checked{/if} />
                                <label class="custom-control-label" for="short_flow_enabled">
                                    Короткое флоу регистрации (для части потока)
                                </label>
                            </div>
                            <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                <input name="pdn_organic_enabled" type="checkbox" class="custom-control-input js-settings-checkbox" id="pdn_organic_enabled" value="1" {if $pdn_organic_enabled}checked{/if} />
                                <label class="custom-control-label" for="pdn_organic_enabled">
                                    ПДН органика
                                </label>
                            </div>
                            <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                <input name="self_dec_before_loan_issuance_enabled" type="checkbox" class="custom-control-input js-settings-checkbox" id="self_dec_before_loan_issuance_enabled" value="1" {if $self_dec_before_loan_issuance_enabled}checked{/if} />
                                <label class="custom-control-label" for="self_dec_before_loan_issuance_enabled">
                                    Проверка самозапрета перед выдачей
                                </label>
                            </div>
                            <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                <input name="terrorist_check_before_loan_issuance_enabled" type="checkbox" class="custom-control-input js-settings-checkbox" id="terrorist_check_before_loan_issuance_enabled" value="1" {if $terrorist_check_before_loan_issuance_enabled}checked{/if} />
                                <label class="custom-control-label" for="terrorist_check_before_loan_issuance_enabled">
                                    Проверка на террориста перед выдачей
                                </label>
                            </div>
                            <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                <input name="axi_spr_enabled" type="checkbox" class="custom-control-input js-settings-checkbox" id="axi_spr_enabled" value="1" {if $axi_spr_enabled}checked{/if} />
                                <label class="custom-control-label" for="axi_spr_enabled">
                                    Акси СПР по части потока (Без отказа скористы, скориста не ставит суммы)
                                </label>
                            </div>
                            <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                <input name="cross_orders_nk_enabled" type="checkbox" class="custom-control-input js-settings-checkbox" id="cross_orders_nk_enabled" value="1" {if $cross_orders_nk_enabled}checked{/if} />
                                <label class="custom-control-label" for="cross_orders_nk_enabled">
                                    Кросс-заявки (Финлаб) для НК
                                </label>
                            </div>
                            <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                <input name="show_sbp_banks_for_autoapprove_orders" type="checkbox" class="custom-control-input js-settings-checkbox" id="$show_sbp_banks_for_autoapprove_orders" value="1" {if $show_sbp_banks_for_autoapprove_orders}checked{/if} />
                                <label class="custom-control-label" for="$show_sbp_banks_for_autoapprove_orders">
                                    Показывать список банков для автозаявок
                                </label>
                            </div>
                            <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                <input name="check_uprid_enabled" type="checkbox" class="custom-control-input js-settings-checkbox" id="check_uprid_enabled" value="1" {if $check_uprid_enabled}checked{/if} />
                                <label class="custom-control-label" for="check_uprid_enabled">
                                    Включить проверку УПРИД перед выдачей
                                </label>
                            </div>
                            <h4 class="card-title">Настройки скоринг-тестов</h4>
                            <div class="myadmin-dd-empty dd" id="nestable2">
                                <ol class="dd-list">
                                    {foreach $scoring_types as $type}
                                            <li class="dd-item dd3-item" data-id="{$type->id}">
                                                <div class="dd-handle dd3-handle">
                                                    <input type="hidden" name="position[]" value="{$type->id}" />
                                                    <input type="hidden" name="settings[{$type->id}][id]" value="{$type->id}" />
                                                </div>
                                                <div class="dd3-content">
                                                    <div class="row">
                                                        <div class="col-8 col-sm-9 col-md-10">
                                                            <a href="#content_{$type->id}" data-toggle="collapse" class="text-info collapsed">
                                                                <i class="fas fa-minus-circle"></i>
                                                                <span>
                                                        {$type->title}


                                                                    {*if $type->type == 'first'}<small class="label label-success">Первичная</small>
                                                                    {elseif $type->type == 'second'}<small class="label label-info">Вторичная</small>
                                                                    {else}<small class="label label-primary">{$type->type}</small>{/if*}

                                                                    {if $type->negative_action=='stop'}<small class="label label-danger">Остановить проверку</small>
                                                                    {elseif $type->negative_action=='next'}<small class="label label-warning">Продолжить проверку</small>
                                                                    {else}<small class="label label-primary">{$type->negative_action}</small>{/if}

                                                    </span>
                                                            </a>
                                                        </div>
                                                        <div class="col-4 col-sm-3 col-md-2">
                                                            <div class="onoffswitch">
                                                                <input type="checkbox" name="settings[{$type->id}][active]" class="onoffswitch-checkbox" value="1" id="active_{$type->id}" {if $type->active}checked="true"{/if} />
                                                                <label class="onoffswitch-label" for="active_{$type->id}">
                                                                    <span class="onoffswitch-inner"></span>
                                                                    <span class="onoffswitch-switch"></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div id="content_{$type->id}" class="card-body collapse scoring-content">
                                                    <div class="row">

                                                        <div class="col-md-6">
                                                            {*}
                                                            <div class="form-group ">
                                                                <label class="control-label">Тип проверки</label>
                                                                <select name="settings[{$type->id}][type]" class="form-control">
                                                                    <option value="first" {if $type->type=='first'}selected="true"{/if}>Первичная</option>
                                                                    <option value="second" {if $type->type=='second'}selected="true"{/if}>Вторичная</option>
                                                                </select>
                                                            </div>
                                                            {*}
                                                            <div class="form-group ">
                                                                <label class="control-label">Если получен негативный тест</label>
                                                                <select name="settings[{$type->id}][negative_action]" class="form-control">
                                                                    <option value="stop" {if $type->negative_action=='stop'}selected="true"{/if}>Остановить проверку</option>
                                                                    <option value="next" {if $type->negative_action=='next'}selected="true"{/if}>Продолжить проверку</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        {if $type->name == 'local_time'}
                                                            <div class="col-md-6">
                                                                <div class="form-group ">
                                                                    <label class="control-label">Максимальное отклонение, сек</label>
                                                                    <input type="text" name="settings[{$type->id}][params][max_diff]" value="{$type->params['max_diff']}" class="form-control" placeholder="" />
                                                                </div>
                                                            </div>
                                                        {elseif $type->name == 'age'}
                                                            <div class="col-md-3">
                                                                <div class="form-group ">
                                                                    <label class="control-label">Минимальный возраст</label>
                                                                    <input type="text" name="settings[{$type->id}][params][max_age]" value="{$type->params['max_age']}" class="form-control" placeholder="" />
                                                                </div>
                                                            </div>

                                                            <div class="col-md-3">
                                                                <div class="form-group ">
                                                                    <label class="control-label">Максимальный возраст (ПК)</label>
                                                                    <input type="text" name="settings[{$type->id}][params][max_age_pk]" value="{$type->params['max_age_pk']}" class="form-control" placeholder="" />
                                                                </div>
                                                            </div>

                                                        {elseif $type->name == 'fssp'}
                                                            <div class="col-md-6">
                                                                <div class="form-group ">
                                                                    <label class="control-label">Сумма долга, руб</label>
                                                                    <input type="text" name="settings[{$type->id}][params][amount]" value="{$type->params['amount']}" class="form-control" placeholder="" />
                                                                </div>
                                                            </div>

                                                        {elseif $type->name == 'fms'}


                                                        {elseif $type->name == 'fns'}


                                                        {elseif $type->name == 'scorista'}
                                                            <div class="col-md-3">
                                                                <div class="form-group ">
                                                                    <label class="control-label">Балл</label>
                                                                    <input type="text" name="settings[{$type->id}][params][scorebal]" value="{$type->params['scorebal']}" class="form-control" placeholder="" />
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <div class="form-group ">
                                                                    <label class="control-label">Проходной балл без прозвона</label>
                                                                    <input type="text" name="settings[{$type->id}][params][scorebal_nocall]" value="{$type->params['scorebal_nocall']}" class="form-control" placeholder="" />
                                                                </div>
                                                            </div>

                                                        {elseif $type->name == 'juicescore'}
                                                            <div class="col-md-6">
                                                                <div class="form-group ">
                                                                    <label class="control-label">Проходной балл</label>
                                                                    <input type="text" name="settings[{$type->id}][params][scorebal]" value="{$type->params['scorebal']}" class="form-control" placeholder="" />
                                                                </div>
                                                            </div>


                                                        {elseif $type->name == 'mbki'}


                                                        {elseif $type->name == 'pdn'}
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="control-label">Организации для проверки ПДН</label>
                                                                    <select name="settings[{$type->id}][params][organization_ids][]" class="form-control select2" multiple>
                                                                        {foreach $organizations as $org}
                                                                            <option value="{$org->id}" {if is_array($type->params['organization_ids']) && in_array($org->id, $type->params['organization_ids'])}selected{/if}>
                                                                                {$org->short_name}
                                                                            </option>
                                                                        {/foreach}
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="control-label">Максимальный порог ПДН (%)</label>
                                                                    <input type="number" name="settings[{$type->id}][params][max_pdn_threshold]" value="{$type->params['max_pdn_threshold']|default:80}" class="form-control" placeholder="80" />
                                                                </div>
                                                            </div>

                                                        {/if}

                                                    </div>
                                                </div>

                                            </li>
                                    {/foreach}
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>





                {*$z = 0}
                {foreach $scoring_settings as $scoring_name => $scoring_params}
                    {display_scoring scoring_name = $scoring_name scoring_params = $scoring_params z = $z}
                    {$z = $z + 1}
                {/foreach*}

            </div>

            <hr class="mb-3 mt-3" />

            <div class="row">
                <div class="col-12 grid-stack-item" data-gs-x="0" data-gs-y="0" data-gs-width="12">
                    <div class="form-actions">
                        <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                    </div>
                </div>
        </form>
        <!-- Row -->
        <!-- ============================================================== -->
        <!-- End PAge Content -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->
    {include file='footer.tpl'}
    <!-- ============================================================== -->
</div>


{capture name='page_scripts'}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                width: '100%',
                placeholder: 'Выберите организации'
            });
        });

        $('.js-settings-checkbox').on('change', function (){
            let key = $(this).attr('name'),
                value = $(this).prop('checked') ? 1 : 0;

            updateSettings(key, value);
        });
    </script>
{/capture}

