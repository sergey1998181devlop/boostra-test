{$meta_title='Отчёт ПДН' scope=parent}

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

<style>
    .jsgrid-cell.truncate {
        max-width: 120ch;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
</style>

<div id="popup" style="display:none; position:fixed; top:20%; left:50%; transform:translateX(-50%);
                        background:#fff; border:1px solid #ccc; padding:20px; z-index:1000;
                        box-shadow:0 2px 10px rgba(0,0,0,0.2); max-width:90%; max-height:80%; overflow:auto;">
    <pre id="popupText" style="white-space:pre-wrap; word-break:break-word;"></pre>
    <textarea id="copyBuffer" style="position:absolute; left:-9999px;"></textarea>
    <br>
    <button onclick="copyText()">📋 Копировать</button>
    <button onclick="closePopup()">✖ Закрыть</button>
</div>

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-file-chart"></i>
                    <span>Отчёт ПДН</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item">Аналитика маркетологу</li>
                    <li class="breadcrumb-item active">Отчёт ПДН</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            Отчёт ПДН
                        </h4>
                        <form id="report_form" onsubmit="showPreloader(event)" method="POST" enctype='multipart/form-data'>
                            <div class="row">
                                <div class="col-3">

                                    {foreach $filters as $filter}
                                        {if ($filter->show_label)}
                                            <div class="mb-3">
                                                <label class="col-form-label text-white mr-2"
                                                       for="{$filter->code}">{$filter->name}</label>
                                            </div>
                                        {/if}
                                    {/foreach}
                                </div>

                                <div class="col-4">
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

                                <div class="col-1">
                                    <div class="row">
                                        <div>
                                            <button class="btn btn-info w-100" value="update"><i class="ti-reload"></i>
                                                Обновить
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="row">
                                        <div class="ml-3">
                                            <div class="input-group mb-3">
                                                <input type="file" class="form-control" name="upload_orders_list_akvarius" id="upload_orders_list_akvarius">
                                                <button name="action" value="upload_orders_list_akvarius"
                                                        class="btn btn-outline-success w-100 mt-2"><i class="ti-upload"></i>
                                                    Загрузить файл Аквариус
                                                </button>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <div class="input-group mb-3">
                                                <input type="file" class="form-control" name="upload_orders_list_akvarius_2" id="upload_orders_list_akvarius_2">
                                                <button name="action" value="upload_orders_list_akvarius_2"
                                                        class="btn btn-outline-success w-100 mt-2"><i class="ti-upload"></i>
                                                    Загрузить файл Аквариус 2
                                                </button>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <input type="file" class="form-control" name="upload_orders_list_finlab" id="upload_orders_list_finlab">
                                            <button name="action" value="upload_orders_list_finlab"
                                                    class="btn btn-outline-success w-100 mt-2"><i class="ti-upload"></i>
                                                Загрузить файл Финлаб
                                            </button>
                                        </div>
                                        <div class="ml-3 mt-3">
                                            <button name="action" value="delete_failed_pdn_calculations_by_filter"
                                                    class="btn btn-outline-danger w-100">
                                                Удалить расчеты ПДН согласно фильтрам
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

                        <p class="text-muted">Найдено записей: {$total_items_amount}</p>
                        <table class="table table-striped table-hover table-responsive text-nowrap">
                            <tr class="table-gray">
                                {foreach $columns as $column}
                                    <td class="text-center jsgrid-cell">{$column->name}</td>
                                {/foreach}
                            </tr>

                            {if $items}
                                {foreach $items as $order}
                                    <tr class="{if $order->success === 'Не успешно'}bg-warning{else}table-gray-light{/if}">
                                        {foreach $columns as $column}
                                            {if $column->code !== 'result'}
                                                <td class="text-center">{$order->{$column->code}|escape}</td>
                                            {else}
                                                <td onclick="showFull(this)"
                                                    data-full="{$order->{$column->code}|escape}"
                                                    class="jsgrid-cell truncate"
                                                >
                                                    {$order->{$column->code}|escape}
                                                </td>
                                            {/if}
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
                startDate: {if (!empty($filters["u.birth"]->value))}"{strstr($filters["u.birth"]->value, " - ", true)}"
                {else}"01.01.1900"{/if},
                endDate: {if (!empty($filters["u.birth"]->value))}"{str_replace(' - ' , '', strstr($filters["u.birth"]->value, " - "))}"
                {else}new Date(){/if},
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

        function showFull(element) {
            const rawText = element.getAttribute('data-full');
            let formattedText = rawText;

            try {
                const parsed = JSON.parse(rawText);
                formattedText = JSON.stringify(parsed, null, 4);
            } catch (e) {

            }

            document.getElementById('popupText').textContent = formattedText;
            document.getElementById('copyBuffer').value = formattedText;
            document.getElementById('popup').style.display = 'block';
        }

        function copyText() {
            const textarea = document.getElementById('copyBuffer');
            textarea.select();
            document.execCommand('copy');
        }

        function closePopup() {
            document.getElementById('popup').style.display = 'none';
        }
    </script>
{/capture}