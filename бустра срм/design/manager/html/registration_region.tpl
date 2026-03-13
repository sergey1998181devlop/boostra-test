{$meta_title = 'Настройки' scope=parent}

{capture name='page_scripts'}
    <script src="design/{$settings->theme}/assets/plugins/nestable/jquery.nestable.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            // Nestable
            var updateOutput = function (e) {
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <!--nestable CSS -->
    <link href="design/{$settings->theme}/assets/plugins/nestable/nestable.css" rel="stylesheet" type="text/css"/>
    <style>
        .onoffswitch {
            display: inline-block !important;
            vertical-align: top !important;
            width: 60px !important;
            text-align: left;
        }

        .onoffswitch-switch {
            right: 38px !important;
            border-width: 1px !important;
        }

        .onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-switch {
            right: 0px !important;
        }

        .onoffswitch-label {
            margin-bottom: 0 !important;
            border-width: 1px !important;
        }

        .onoffswitch-inner::after,
        .onoffswitch-inner::before {
            height: 18px !important;
            line-height: 18px !important;
        }

        .onoffswitch-switch {
            width: 20px !important;
            margin: 1px !important;
        }

        .onoffswitch-inner::before {
            content: 'ВКЛ' !important;
            padding-left: 10px !important;
            font-size: 10px !important;
        }

        .onoffswitch-inner::after {
            content: 'ВЫКЛ' !important;
            padding-right: 6px !important;
            font-size: 10px !important;
        }

        .scoring-content {
            position: relative;
            z-index: 999;
            border: 1px solid rgba(120, 130, 140, 0.13);;
            border-top: 0;
            background: #383f48;
            border-bottom-left-radius: 4px;
            border-bottom-right-radius: 4px;
            margin-top: -5px;
        }

        .collapsed .fa-minus-circle::before {
            content: "\f055";
        }

        h4.text-white {
            display: inline-block
        }

        .move-zone {
            display: inline-block;
            color: #fff;
            padding-right: 15px;
            margin-right: 10px;
            border-right: 1px solid #30b2ff;
            cursor: move
        }

        .move-zone span {
            font-size: 24px;
        }

        .dd {
            max-width: 100%;
        }

        small.label {
            margin-left: 10px;
        }

        .select2-container {
            display: block;
            width: unset !important;
        }

        .select2-search__field {
            color: #ffffff;
        }

        .form-control {
            /*width: 100%;*/
            height: auto;
            padding: 6px 12px;
            font-size: 14px;
            line-height: 1.42857143;
            color: #555;
            background-color: #fff;
            background-image: none;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .form-control[multiple] {
            height: auto;
            overflow-y: auto;
        }

        .form-control[multiple] option {
            padding: 4px 8px;
        }

        .form-control[multiple] option:checked {
            background-color: #f5f5f5;
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
        <form class="" method="POST">
            <div class="row grid-stack" data-gs-width="12" data-gs-animate="yes">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Проверка регистрации регионов</h4>
                            <div class="myadmin-dd-empty dd" id="nestable2">
                                <ol class="dd-list">
                                    {foreach $scoring_types as $type}
                                        {if $type->name == 'location'}
                                            <li class="dd-item dd3-item" data-id="{$type->id}">
                                                <div class="dd-handle dd3-handle">
                                                    <input type="hidden" name="position[]" value="{$type->id}"/>
                                                    <input type="hidden" name="settings[{$type->id}][id]"
                                                           value="{$type->id}"/>
                                                </div>
                                                <div id="content_{$type->id}"
                                                     class="card-body collapse show scoring-content">
                                                    <div class="row">
                                                        <div class="col-md-6 js-dadata-address">
                                                            <div class=" form-group">
                                                                <label class="control-label">Список регионов</label>
                                                                <select id="mySelect"
                                                                        name="settings[{$type->id}][params][regions][]"
                                                                        class="js-region-select js-dadata-region form-control"
                                                                        multiple="multiple">
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        {/if}
                                    {/foreach}
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="mb-3 mt-3"/>
            <div class="row">
                <div class="col-12 grid-stack-item" data-gs-x="0" data-gs-y="0" data-gs-width="12">
                    <div class="form-actions">
                        <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Сохранить</button>
                    </div>
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
    <script src="design/{$settings->theme|escape}/js/apps/registration_region_dadata.app.js?v=0.1"></script>
    {assign var="regions" value=$scoring_types['location']->params['regions']}
{literal}
    <script>
        let regions = {/literal}{$regions|@json_encode}{literal};
        let regionsArray = regions.split(',');
        console.log(regionsArray)
    </script>
{/literal}
    <script>

        $(document).ready(function () {
            let $select = $('.js-region-select');

            regionsArray.forEach(function (value) {
                let option = new Option(value, value, true, true);
                $select.append(option);
            });

            $select.trigger('change');

        });


        $('[name="captcha_status"]').on('change', function () {
            let key = $(this).attr('name'),
                value = $(this).prop('checked') ? 1 : 0;

            updateSettings(key, value);
        });
    </script>
{/capture}

