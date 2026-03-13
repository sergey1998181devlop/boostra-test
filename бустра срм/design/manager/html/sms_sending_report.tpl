{$meta_title='Отчёт для отправки смс' scope=parent}

{capture name='page_styles'}
    <link href="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet"
          type="text/css"/>
    <link href="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="design/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">
    <link type="text/css" rel="stylesheet"
          href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid.min.css"/>
    <link type="text/css" rel="stylesheet"
          href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid-theme.min.css"/>
{/capture}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-file-chart"></i>
                    <span>Отчёт для отправки смс</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item">Аналитика маркетологу</li>
                    <li class="breadcrumb-item active">Отчёт для отправки смс</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            Отчёт для отправки смс
                        </h4>
                        <form id="report_form" onsubmit="showPreloader(event)" method="POST">
                            <div class="row">
                                <div class="col-6 col-md-3">
                                    <div class="mb-3">
                                        <label class="col-form-label text-white mr-2" for="day_of_delay_start">День
                                            начала просрочки</label>
                                    </div>
                                    <div class="mb-3">
                                        <label class="col-form-label text-white mr-2" for="day_of_delay_end">День
                                            окончания просрочки</label>
                                    </div>
                                    {foreach $filters as $filter}
                                        {if ($filter->show_label)}
                                            <div class="mb-3">
                                                <label class="col-form-label text-white mr-2"
                                                       for="{$filter->code}">{$filter->name}</label>
                                            </div>
                                        {/if}
                                    {/foreach}
                                </div>

                                <div class="col-3 col-md-3">
                                    <div class="input-group mb-3">
                                        <input type="text" id="day_of_delay_start" name="day_of_delay_start"
                                               class="form-control"
                                               value="{$day_of_delay_start}">
                                    </div>
                                    <div class="input-group mb-3">
                                        <input type="text" id="day_of_delay_end" name="day_of_delay_end"
                                               class="form-control"
                                               value="{$day_of_delay_end}">
                                    </div>

                                    {foreach $filters as $filter}
                                        {if ($filter->show_label)}
                                            <div class="input-group mb-3">
                                                {if ($filter->type === 'select')}
                                                    <select id="{$filter->code}" name="filters[{$filter->code}]"
                                                            class="form-control">
                                                        {foreach $filter->value as $option}
                                                            <option value="{$option->value}"
                                                                    {if ($option->selected)}selected{/if}>{$option->name}</option>
                                                        {/foreach}
                                                    </select>
                                                {elseif ($filter->type === 'multiselect')}
                                                    <select id="{$filter->code}" name="filters[{$filter->code}][]"
                                                            class="form-control" multiple size="4">
                                                        {foreach $filter->value as $option}
                                                            <option value="{$option->value}"
                                                                    {if ($option->selected)}selected{/if}>{$option->name}</option>
                                                        {/foreach}
                                                    </select>
                                                {else}
                                                    <input type="text" id="{$filter->code}"
                                                           name="filters[{$filter->code}]"
                                                           class="form-control {$filter->type}"
                                                           value="{$filter->value}">
                                                    {if ($filter->type === 'numberrange')}
                                                        <span class="col-form-label text-white mr-2 ml-2">-</span>
                                                        <input type="text" id="{$filter->pair_label}"
                                                               name="filters[{$filter->pair_label}]"
                                                               class="form-control {$filter->type}"
                                                               value="{$filters[$filter->pair_label]->value}">
                                                    {/if}
                                                {/if}

                                            </div>
                                        {/if}
                                    {/foreach}
                                </div>

                                <div class="col-3 col-md-6">
                                    <div class="row">
                                        <div class="col-3">
                                            <button class="btn btn-info w-100" value="update"><i class="ti-reload"></i> Обновить
                                            </button>
                                        </div>
                                        <div class="col-4">
                                            <button name="action" value="download_excel_sms"
                                                    class="btn btn-outline-success w-100"><i class="ti-download"></i>
                                                Скачать excel смс
                                            </button>
                                        </div>
                                        <div class="col-4">
                                            <button name="action" value="download_excel_ivr"
                                                    class="btn btn-outline-success w-100"><i class="ti-download"></i>
                                                Скачать excel IVR
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-4 offset-3">
                                            <button name="action" value="download_excel_sms_control"
                                                    class="btn btn-outline-success w-100"><i class="ti-download"></i>
                                                Скачать excel смс контроль
                                            </button>
                                        </div>
                                        <div class="col-4">
                                            <button name="action" value="download_excel_ivr_control"
                                                    class="btn btn-outline-success w-100"><i class="ti-download"></i>
                                                Скачать excel IVR контроль
                                            </button>
                                        </div>

                                    </div>
                                </div>

                            </div>
                        </form>

                        {if !empty($error)}
                            <div class="mb-4">
                                <h4 class="text-danger">Возникла ошибка!</h4>
                                <h4 class="text-danger">{$error}</h4>
                            </div>
                        {/if}

                        <p class="text-muted">Найдено заявок: {$total_items_amount}</p>
                        <table class="table table-striped table-hover table-responsive text-nowrap">
                            <tr class="table-gray">
                                {foreach $columns as $column}
                                    <td class="text-center jsgrid-cell">{$column->name}</td>
                                {/foreach}
                            </tr>

                            {if $items}
                                {foreach $items as $order}
                                    <tr class="table-gray-light">
                                        {foreach $columns as $column}
                                            <td class="text-center jsgrid-cell">{$order->{$column->code}}</td>
                                        {/foreach}
                                    </tr>
                                {/foreach}
                            {else}
                                <tr>
                                    <td colspan="14" class="text-center">Данные не найдены</td>
                                </tr>
                            {/if}
                        </table>
                    </div>
                </div>
            </div>


            {include file='html_blocks/pagination.tpl'}

            <div class="jsgrid-load-shader"
                 style="display: none; position: fixed; inset: 0px; z-index: 10; background: rgba(222, 222, 222, 0.7)">
            </div>
            <div class="jsgrid-load-panel"
                 style="display: none; position: fixed; top: 50%; left: 50%; z-index: 1000; color: #212529; font-weight: 400">
                Ожидайте. Идет загрузка...
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

        $(function () {
            $('.daterange').daterangepicker({
                autoApply: true,
                startDate: {if (!empty($filters["u.birth"]->value))}"{strstr($filters["u.birth"]->value, " - ", true)}"{else}"01.01.1900"{/if},
                endDate: {if (!empty($filters["u.birth"]->value))}"{str_replace(' - ' , '', strstr($filters["u.birth"]->value, " - "))}"{else}new Date(){/if},
                locale: {
                    format: 'DD.MM.YYYY'
                },
            });
        });

        function showPreloader(event) {
            const button = event.submitter

            if (button.value === 'update') {
                const shader = document.querySelector('.jsgrid-load-shader')
                shader.style.display = 'block'

                const preloader = document.querySelector('.jsgrid-load-panel')
                preloader.style.display = 'block'
            }
        }
    </script>
{/capture}