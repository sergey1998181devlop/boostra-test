{$meta_title='Отчёт отключений дополнительных услуг' scope=parent}

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

            $('select.filter').on('change', function () {
                let dateRange = $('input[name="daterange"]').val();
                let organizations = $('select.organizations').val() || '';
                let products = $('select.products').val() || '';
                let types = $('select.types').val() || '';
                let nk_pk = $('select.nk_pk').val() || '';

                if (dateRange) {
                    params = (new URLSearchParams({
                        daterange: dateRange,
                        organizations: organizations,
                        products: products,
                        types: types,
                        nk_pk: nk_pk,
                    })).toString();
                } else {
                    params = (new URLSearchParams({
                        organizations: organizations,
                        products: products,
                        types: types,
                        nk_pk: nk_pk,
                    })).toString();
                }

                window.open('{$reportUri}?' + params, '_self');
                return false;
            });
        })
    </script>
{/capture}

{capture name='page_styles'}
    <link href="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    <!-- Daterange picker plugins css -->
    <link href="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="design/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">
{/capture}

<style>
    tr.small td {
        padding: 0.25rem;
    }
    .table thead th, .table th {
        border: 1px solid;
        font-size: 10px;
    }
    thead.position-sticky {
        top: 0;
        background-color: #272c33;
    }
</style>

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-file-chart"></i>
                    <span>{$meta_title}</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">{$meta_title}</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{$meta_title} за период {if $date_from}{$date_from|date} - {$date_to|date}{/if}</h4>

                        <form>
                            <div class="row">
                                <div class="col-12 col-md-4">
                                    <div class="input-group mb-3">
                                        <input type="text" name="daterange" class="form-control daterange" value="{if $from && $to}{$from}-{$to}{/if}">
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <span class="ti-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <button type="submit" class="btn btn-info">Сформировать</button>

                                    <button onclick="return download();" type="button" class="btn btn-success">
                                        <i class="ti-save"></i> Выгрузить
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="row mt-2 mb-4">
                            <div class="col-12 col-md-2">
                                <select name="organization" class="form-control form-control-sm filter organizations">
                                    <option selected disabled value="">Организация</option>
                                    {foreach $organizations as $organization}
                                        <option value="{$organization->id}"
                                                {if $organizations_filter == $organization->id}selected{/if}>{$organization->short_name}</option>
                                    {/foreach}
                                </select>
                            </div>

                            <div class="col-12 col-md-2">
                                <select name="product" class="form-control form-control-sm filter products">
                                    <option selected disabled value="">Продукт</option>
                                    <option value="additional_service_tv_med"
                                            {if ($products == 'additional_service_tv_med')}selected{/if}>Вита-мед</option>
                                    <option value="additional_service_multipolis"
                                            {if ($products == 'additional_service_multipolis')}selected{/if}>Консьерж сервис</option>
                                    <option value="financial_doctor"
                                             {if ($products == 'financial_doctor')}selected{/if}>Финансовый доктор</option>
                                    <option value="so_repayment"
                                             {if ($products == 'so_repayment')}selected{/if}>Звездный оракул</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-2">
                                <select name="type" class="form-control form-control-sm filter types">
                                    <option selected disabled>Тип</option>
                                    {foreach $order_statuses as $key=>$order_status}
                                        <option value="{$key}" {if ($types == $key)}selected{/if}>{$order_status}</option>
                                    {/foreach}
                                </select>
                            </div>

                            <div class="col-12 col-md-2">
                                <select name="nk_pk" class="form-control form-control-sm filter nk_pk">
                                    <option selected disabled>НК/ПК</option>
                                    <option value="NK" {if ($nk_pk == 'NK')}selected{/if}>НК</option>
                                    <option value="PK" {if ($nk_pk == 'PK')}selected{/if}>ПК</option>
                                </select>
                            </div>

                            <button onclick="return window.open('{$reportUri}','_self');"
                                    type="button" class="btn btn-warning">
                                <i class="ti-save"></i> Сбросить
                            </button>
                        </div>

                        {include file='html_blocks/pagination.tpl'}
                        <div id="result" class="">
                            <table class="table table-bordered table-hover">
                                <thead class="position-sticky">
                                <tr>
                                    <th>Клиент</th>
                                    <th>Дата рождения</th>
                                    <th>НК/ПК</th>
                                    <th>Договор</th>
                                    <th>Организация</th>
                                    <th>ФИО менеджера</th>
                                    <th>Продукт</th>
                                    <th>Тип</th>
                                    <th>Действие</th>
                                    <th>День отключения</th>
                                    <th>Дата отключения</th>
                                    <th>Время отключения</th>
                                </tr>
                                </thead>
                                <tbody>
                                {if $items}
                                    {foreach $items as $item}
                                        <tr>
                                            <td>
                                                {if $can_see_client_url}
                                                    <a href="/client/{$item->user_id}">{$item->firstname} {$item->lastname} {$item->patronymic|escape}</a>
                                                {else}
                                                    {$item->firstname} {$item->lastname} {$item->patronymic|escape}
                                                {/if}
                                            </td>
                                            <td>{$item->birth}</td>
                                            <td>
                                                {if ($item->user_type == 'NK')}
                                                    {ProlongationReportView::NK_USER}
                                                {else}
                                                    {ProlongationReportView::PK_USER}
                                                {/if}
                                            </td>
                                            <td>{$item->contract}</td>
                                            <td>{$item->organization}</td>
                                            <td>
                                                {if $item->cancellation_additional_services_by_phone === '1'}
                                                    Клиент
                                                {else}
                                                    {if $can_see_manager_url}
                                                        <a href="/manager/{$item->manager_id}">{$item->manager_name}</a>
                                                    {else}
                                                        {$item->manager_name}
                                                    {/if}
                                                {/if}
                                            </td>
                                            <td>
                                                {if $item->type == 'additional_service_multipolis'}
                                                    Консьерж сервис
                                                {elseif $item->type == 'disable_additional_service_on_issue'}
                                                    Финансовый доктор
                                                {elseif in_array($item->type, [
                                                    'additional_service_so_repayment',
                                                    'half_additional_service_so_repayment',
                                                    'additional_service_so_partial_repayment',
                                                    'half_additional_service_so_partial_repayment'
                                                ])}
                                                    Звездный оракул
                                                {else}
                                                    Вита-мед
                                                {/if}
                                            </td>
                                            <td>
                                                {if isset($order_statuses[$item->type])}
                                                    {$order_statuses[$item->type]}
                                                {else}
                                                    {$order_statuses['prolongation']}
                                                {/if}
                                            </td>
                                            <td>{$item->new_values}</td>
                                            <td>{$item->off_days_count}</td>
                                            <td>{$item->created|date_format:"%d.%m.%Y"}</td>
                                            <td>{$item->created|date_format:"%H:%M:%S"}</td>
                                        </tr>
                                    {/foreach}
                                {else}
                                    <tr>
                                        <td colspan="6" class="text-danger text-center">Данные не найдены</td>
                                    </tr>
                                {/if}
                                </tbody>
                            </table>
                        </div>
                        {include file='html_blocks/pagination.tpl'}
                    </div>
                </div>
            </div>
        </div>
    </div>
    {include file='footer.tpl'}
</div>

<script>
    function download() {
        let dateRange = $('input[name="daterange"]').val();
        dateRange = (new URLSearchParams({
            daterange: dateRange
        })).toString();
        window.open(
            '{$reportUri}?action=download&' + dateRange,
            '_blank'
        );
        return false;
    }
</script>
