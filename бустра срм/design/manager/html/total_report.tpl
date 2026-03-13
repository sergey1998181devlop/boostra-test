{$meta_title='Общий отчет' scope=parent}

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

        $('.js-scorista-open').click(function(e){
            e.preventDefault();

            var index = $(this).data('index');

            if ($(this).hasClass('active'))
            {
                $('.js-scorista-'+index).removeClass('open');
                $(this).removeClass('active');
                $(this).find('.fas').removeClass('fa-caret-down').addClass('fa-caret-down')
            }
            else
            {
                $('.js-scorista-'+index).addClass('open');
                $(this).find('.fas').removeClass('fa-caret-down').addClass('fa-caret-down')
                $(this).addClass('active');
            }


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
    .table td.align-right {
        text-align:right!important;
    }
    .js-scorista-item {
        display:none
    }
    .js-scorista-item.open {
        display:table-row;
    }
    .dropdown-menu {
        max-height:300px;
        overflow-y:auto;
        overflow-x:hidden;
        width:100%
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
                    <span>Общий отчет</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Общий отчет</li>
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
                        <h4 class="card-title">
                            Общий отчет за период {if $date_from}{$date_from|date} - {$date_to|date}{/if}
                            {if $filter_source}
                                {foreach $filter_source as $fs}
                                    {$fs}{if !$fs@last}, {/if}
                                {/foreach}
                            {/if}
                        </h4>
                        <form>
                            <div class="row">
                                <div class="col-6 col-md-2">
                                    <div class="input-group mb-3">
                                        <input type="text" name="daterange" class="form-control daterange" value="{if $from && $to}{$from}-{$to}{/if}">
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <span class="ti-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-2">
                                    <div class="mb-3">
                                        <select class="form-control" name="filter_order">
                                            <option value="" {if !$filter_order}selected{/if}>Все заявки</option>
                                            <option value="new" {if $filter_order == 'new'}selected{/if}>Новые заявки</option>
                                            <option value="repeat" {if $filter_order == 'repeat'}selected{/if}>Повторные заявки</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6 col-md-2">
                                    <div class="mb-3">
                                        <select class="form-control" name="filter_client">
                                            <option value="" {if !$filter_client}selected{/if}>Все клиенты</option>
                                            <option value="nk" {if $filter_client == 'nk'}selected{/if}>Новые клиенты</option>
                                            <option value="pk" {if $filter_client == 'pk'}selected{/if}>Повторные клиенты</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6 col-md-2">
                                    <div class="mb-3">
                                        <div class="btn-group btn-block">
                                            <button class="btn btn-block btn-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="false" aria-expanded="true" data-flip="false">
                                                Источники
                                            </button>
                                            <div class="dropdown-menu p-2" >
                                                {foreach $sources as $source}
                                                <div class="form-group">
                                                <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                                    <input name="filter_source[]" type="checkbox" class="custom-control-input" id="filter_source_{$source@index}" value="{$source}" {if in_array($source, (array)$filter_source)}checked="true"{/if} />
                                                    <label class="custom-control-label" for="filter_source_{$source@index}">
                                                        {if $source == 'leadgid'}
                                                            {$source}-все
                                                        {else}
                                                            {$source}
                                                        {/if}
                                                    </label>
                                                </div>
                                                </div>
                                                {/foreach}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-6 col-md-2">
                                    <div class="mb-3">
                                        <div class="btn-group btn-block">
                                            <button class="btn btn-block btn-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="false" aria-expanded="true" data-flip="false">
                                                Вебмастер/РК
                                            </button>
                                            <div class="dropdown-menu p-2" >
                                                {foreach $subSources as $source}
                                                <div class="form-group">
                                                <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                                    <input name="filter_sub_source[]" type="checkbox" class="custom-control-input" id="filter_sub_source_{$source@index}" value="{$source}" {if in_array($source, (array)$filter_sub_source)}checked="true"{/if} />
                                                    <label class="custom-control-label" for="filter_sub_source_{$source@index}">
                                                        {if $source == 'leadgid'}
                                                            {$source}-все
                                                        {else}
                                                            {$source}
                                                        {/if}
                                                    </label>
                                                </div>
                                                </div>
                                                {/foreach}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-6 col-md-2">
                                    <div class="mb-3">
                                        <a class="btn btn-primary btn-block" href="#scorista_filter" data-target="#scorista_filter" data-toggle="collapse">
                                            <span>Скориста</span>
                                            <i class=" fas fa-angle-down"></i>
                                        </a>
                                        <div id="scorista_filter" class="collapse pt-2">
                                            <div class="row pb-1">
                                                <div class="col-6">
                                                    <input class="form-control" name="scorista[from][]" value="{$filter_scorista['from'][0]}" />
                                                </div>
                                                <div class="col-6">
                                                    <input class="form-control" name="scorista[to][]" value="{$filter_scorista['to'][0]}" />
                                                </div>
                                            </div>

                                            <div class="row pb-1">
                                                <div class="col-6">
                                                    <input class="form-control" name="scorista[from][]" value="{$filter_scorista['from'][1]}" />
                                                </div>
                                                <div class="col-6">
                                                    <input class="form-control" name="scorista[to][]" value="{$filter_scorista['to'][1]}" />
                                                </div>
                                            </div>

                                            <div class="row pb-1">
                                                <div class="col-6">
                                                    <input class="form-control" name="scorista[from][]" value="{$filter_scorista['from'][2]}" />
                                                </div>
                                                <div class="col-6">
                                                    <input class="form-control" name="scorista[to][]" value="{$filter_scorista['to'][2]}" />
                                                </div>
                                            </div>

                                            <div class="row pb-1">
                                                <div class="col-6">
                                                    <input class="form-control" name="scorista[from][]" value="{$filter_scorista['from'][3]}" />
                                                </div>
                                                <div class="col-6">
                                                    <input class="form-control" name="scorista[to][]" value="{$filter_scorista['to'][3]}" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-2">
                                    <button type="submit" class="btn btn-info">Сформировать</button>
                                </div>
                            </div>

                        </form>
                        <table class="table table-bordered table-hover">

                            {$total_ref=0}
                            {$total_reg=0}
                            {$total_ord=0}
                            {$total_auto = 0}
                            {$total_fms=0}
                            {$total_contact=0}
                            {$total_actual=0}
                            {$total_scorista=0}
                            {$total_target=0}
                            {$total_credit=0}

                            {$total_scorista_groups = []}
                            {foreach $scorista_groups as $group}
                                {$total_scorista_groups[$group->interval] = []}

                                {$total_scorista_groups[$group->interval]['actual'] = 0}
                                {$total_scorista_groups[$group->interval]['scorista'] = 0}
                                {$total_scorista_groups[$group->interval]['target'] = 0}
                                {$total_scorista_groups[$group->interval]['credit'] = 0}
                            {/foreach}

                            {foreach $report as $item}

                                {$total_ref = $total_ref + $item->r_total}
                                {$total_reg = $total_reg + $item->u_total}
                                {$total_ord = $total_ord + $item->o_total}
                                {$total_auto = $total_auto + $item->auto}
                                {$total_fms = $total_fms + $item->fms}
                                {$total_contact = $total_contact + $item->contact}
                                {$total_actual = $total_actual + $item->actual}
                                {$total_scorista = $total_scorista + $item->scorista}
                                {$total_target = $total_target + $item->target}
                                {$total_credit = $total_credit + $item->credit}

                                {foreach $scorista_groups as $group}
                                    {$total_scorista_groups[$group->interval]['actual'] = $item->actual_scorista[$group->name] + $total_scorista_groups[$group->interval]['actual']}
                                    {$total_scorista_groups[$group->interval]['scorista'] = $item->scorista_scorista[$group->name] + $total_scorista_groups[$group->interval]['scorista']}
                                    {$total_scorista_groups[$group->interval]['target'] = $item->target_scorista[$group->name] + $total_scorista_groups[$group->interval]['target']}
                                    {$total_scorista_groups[$group->interval]['credit'] = $item->credit_scorista[$group->name] + $total_scorista_groups[$group->interval]['credit']}
                                {/foreach}

                            {/foreach}

                            <tr>
                                <th>Дата</th>
                                <th>Переходы</th>
                                <th>Регистрации</th>
                                <th class="text-center">
                                    Заявки
                                </th>
                                <th class="text-center">
                                    Авто
                                </th>
                                <th class="text-center">
                                    ФССП
                                </th>
                                <th class="text-center">
                                    Ответы
                                </th>
                                <th class="text-center">
                                    Актуальна
                                </th>
                                <th class="text-center">
                                    % от всех
                                </th>
                                <th class="text-center">
                                    Скориста
                                </th>
                                <th class="text-center">
                                    % от всех
                                </th>
                                <th class="text-center">
                                    Цель
                                </th>
                                <th class="text-center">
                                    % от всех
                                </th>
                                <th class="text-center">
                                    Выдача
                                </th>
                                <th class="text-center">
                                    % от всех
                                </th>
                                <th class="text-center">
                                    PD 1
                                </th>
                                <th class="text-center">
                                    PD 5
                                </th>
                                <th class="text-center">
                                    PD 30
                                </th>
                            </tr>

                            <tr class="bg-gray">
                                <td>
                                    Итог
                                    <br />
                                    <a href="javascript:void(0);" class="js-scorista-open" data-index="total1">
                                        <small>Скориста</small> <i class=" fas fa-caret-down"></i>
                                    </a>
                                </td>
                                <td>
                                    <strong class="text-warning">{$total_ref}</strong>
                                </td>
                                <td>
                                    <strong class="text-warning">{$total_reg}</strong>
                                    <br />
                                    <small class="text-success"><strong>{if $total_ref}{($total_reg / $total_ref * 100)|round}{else}0{/if}%</strong></small>
                                </td>
                                <td class="text-center">
                                    <strong class="text-warning">{$total_ord}</strong>
                                    <br />
                                    <small class="text-success"><strong>{if $total_ref}{($total_ord / $total_ref * 100)|round}{else}0{/if}%</strong></small>
                                </td>
                                <td class="text-center">
                                    <strong class="text-warning">{$total_auto}</strong>
                                    <br />
                                    <small class="text-success"><strong>{if $total_ref}{($total_auto / $total_ref * 100)|round}{else}0{/if}%</strong></small>
                                </td>
                                <td class="text-center">
                                    <strong class="text-warning">{$total_fms}</strong>
                                    <br />
                                    <small class="text-success"><strong>{if $total_ref}{($total_fms / $total_ref * 100)|round}{else}0{/if}%</strong></small>
                                </td>
                                <td class="text-center">
                                    <strong class="text-warning">{$total_contact}</strong>
                                    <br />
                                    <small class="text-success"><strong>{if $total_ref}{($total_contact / $total_ref * 100)|round}{else}0{/if}%</strong></small>
                                </td>
                                <td class="text-center">
                                    <strong class="text-warning">{$total_actual}</strong>
                                    <br />
                                    <small class="text-success"><strong>{if $total_ref}{($total_actual / $total_ref * 100)|round}{else}0{/if}%</strong></small>
                                </td>
                                <td class="text-center">

                                </td>
                                <td class="text-center">
                                    <strong class="text-warning">{$total_scorista}</strong>
                                    <br />
                                    <small class="text-success"><strong>{if $total_ref}{($total_scorista / $total_ref * 100)|round}{else}0{/if}%</strong></small>
                                </td>
                                <td class="text-center">

                                </td>
                                <td class="text-center">
                                    <strong class="text-warning">{$total_target}</strong>
                                    <br />
                                    <small class="text-success"><strong>{if $total_ref}{($total_target / $total_ref * 100)|round}{else}0{/if}%</strong></small>
                                </td>
                                <td class="text-center">

                                </td>
                                <td class="text-center">
                                    <strong class="text-warning">{$total_credit}</strong>
                                    <br />
                                    <small class="text-success"><strong>{if $total_ref}{($total_credit / $total_ref * 100)|round}{else}0{/if}%</strong></small>
                                </td>
                                <td class="text-center">

                                </td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>

                            {foreach $total_scorista_groups as $interval => $item}
                            <tr class="bg-grey js-scorista-item js-scorista-total1">
                                <td colspan="7" class="align-right">
                                    <strong >{$interval}</strong>
                                </td>
                                <td>
                                    <strong class="text-info">
                                    {if $item['actual']}{$item['actual']}{else}0{/if}
                                    </strong>
                                </td>
                                <td>
                                    {if $item['actual']}{($item['actual']/$total_actual*100)|round}{else}0{/if}
                                </td>
                                <td>
                                    <strong class="text-info">
                                        {if $item['scorista']}{$item['scorista']}{else}0{/if}
                                    </strong>
                                </td>
                                <td>
                                    {if $item['scorista']}{($item['scorista']/$total_scorista*100)|round}{else}0{/if}
                                </td>
                                <td>
                                    <strong class="text-info">
                                        {if $item['target']}{$item['target']}{else}0{/if}
                                    </strong>
                                </td>
                                <td>
                                    {if $item['target']}{($item['target']/$total_target*100)|round}{else}0{/if}
                                </td>
                                <td>
                                    <strong class="text-info">
                                        {if $item['credit']}{$item['credit']}{else}0{/if}
                                    </strong>
                                </td>
                                <td>
                                    {if $item['credit']}{($item['credit']/$total_credit*100)|round}{else}0{/if}
                                </td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            {/foreach}



                            {foreach $report as $item}

                            <tr class="">
                                <td>
                                    <strong >{$item->u_date|date}</strong>
                                    <br />
                                    <a href="javascript:void(0);" class="js-scorista-open" data-index="{$item@index}">
                                        <small>Скориста</small> <i class=" fas fa-caret-down"></i>
                                    </a>
                                </td>
                                <td>
                                    <strong class="text-info">{if $item->r_total}{$item->r_total}{else}0{/if}</strong>
                                </td>
                                <td>
                                    <strong class="text-info">{if $item->u_total}{$item->u_total}{else}0{/if}</strong>
                                    <br />
                                    <small class="text-success"><strong>{if $item->r_total}{($item->u_total / $item->r_total * 100)|round}{else}0{/if}%</strong></small>
                                </td>
                                <td>
                                    <strong class="text-info">
                                        {if $item->o_total}{$item->o_total}{else}0{/if}
                                    </strong>
                                    <br />
                                    <small class="text-success"><strong>{if $item->r_total}{($item->o_total / $item->r_total * 100)|round}{else}0{/if}%</strong></small>
                                </td>
                                <td>
                                    <strong class="text-info">
                                        {if $item->auto}{$item->auto}{else}0{/if}
                                    </strong>
                                    <br />
                                    <small class="text-success"><strong>{if $item->r_total}{($item->auto / $item->r_total * 100)|round}{else}0{/if}%</strong></small>
                                </td>
                                <td>
                                    <strong class="text-info">
                                        {if $item->fms}{$item->fms}{else}0{/if}
                                    </strong>
                                    <br />
                                    <small class="text-success"><strong>{if $item->r_total}{($item->fms / $item->r_total * 100)|round}{else}0{/if}%</strong></small>
                                </td>
                                <td>
                                    <strong class="text-info">
                                        {if $item->contact}{$item->contact}{else}0{/if}
                                    </strong>
                                    <br />
                                    <small class="text-success"><strong>{if $item->r_total}{($item->contact / $item->r_total * 100)|round}{else}0{/if}%</strong></small>
                                </td>
                                <td>
                                    <strong class="text-info">
                                        {if $item->actual}{$item->actual}{else}0{/if}
                                    </strong>
                                    <br />
                                    <small class="text-success"><strong>{if $item->r_total}{($item->actual / $item->r_total * 100)|round}{else}0{/if}%</strong></small>
                                </td>
                                <td>

                                </td>
                                <td>
                                    <strong class="text-info">
                                        {if $item->scorista}{$item->scorista}{else}0{/if}
                                    </strong>
                                    <br />
                                    <small class="text-success"><strong>{if $item->r_total}{($item->scorista / $item->r_total * 100)|round}{else}0{/if}%</strong></small>
                                </td>
                                <td></td>
                                <td>
                                    <strong class="text-info">
                                        {if $item->target}{$item->target}{else}0{/if}
                                    </strong>
                                    <br />
                                    <small class="text-success"><strong>{if $item->r_total}{($item->target / $item->r_total * 100)|round}{else}0{/if}%</strong></small>
                                </td>
                                <td></td>
                                <td>
                                    <strong class="text-info">
                                        {if $item->credit}{$item->credit}{else}0{/if}
                                    </strong>
                                    <br />
                                    <small class="text-success"><strong>{if $item->r_total}{($item->credit / $item->r_total * 100)|round}{else}0{/if}%</strong></small>
                                </td>
                                <td></td>
                                <td>
                                    {if $item->pd1 > 0}<span class="text-danger">{$item->pd1}</span>
                                    {else}0{/if}
                                </td>
                                <td>
                                    {if $item->pd5 > 0}<span class="text-danger">{$item->pd5}</span>
                                    {else}0{/if}
                                </td>
                                <td>
                                    {if $item->pd30 > 0}<span class="text-danger">{$item->pd30}</span>
                                    {else}0{/if}
                                </td>
                            </tr>

                            {foreach $scorista_groups as $group}
                            <tr class="bg-grey js-scorista-item js-scorista-{$item@index}">
                                <td colspan="7" class="align-right">
                                    <strong >{$group->interval}</strong>
                                </td>
                                <td>
                                    <strong class="text-info">
                                    {if $item->actual_scorista[$group->name]}{$item->actual_scorista[$group->name]}{else}0{/if}
                                    </strong>
                                </td>
                                <td>
                                    {if $item->actual_scorista[$group->name]}{($item->actual_scorista[$group->name]/$item->actual*100)|round}{else}0{/if}
                                </td>
                                <td>
                                    <strong class="text-info">
                                        {if $item->scorista_scorista[$group->name]}{$item->scorista_scorista[$group->name]}{else}0{/if}
                                    </strong>
                                </td>
                                <td>
                                    {if $item->scorista_scorista[$group->name]}{($item->scorista_scorista[$group->name]/$item->scorista*100)|round}{else}0{/if}
                                </td>
                                <td>
                                    <strong class="text-info">
                                        {if $item->target_scorista[$group->name]}{$item->target_scorista[$group->name]}{else}0{/if}
                                    </strong>
                                </td>
                                <td>
                                    {if $item->target_scorista[$group->name]}{($item->target_scorista[$group->name]/$item->target*100)|round}{else}0{/if}
                                </td>
                                <td>
                                    <strong class="text-info">
                                        {if $item->credit_scorista[$group->name]}{$item->credit_scorista[$group->name]}{else}0{/if}
                                    </strong>
                                </td>
                                <td>
                                    {if $item->credit_scorista[$group->name]}{($item->credit_scorista[$group->name]/$item->credit*100)|round}{else}0{/if}
                                </td>
                                <td>
                                    {if $item->credit_scorista_pd1[$group->name]}<span class="text-danger">{$item->credit_scorista_pd1[$group->name]}</span>
                                    {else}0{/if}
                                </td>
                                <td>
                                    {if $item->credit_scorista_pd5[$group->name]}<span class="text-danger">{$item->credit_scorista_pd5[$group->name]}</span>
                                    {else}0{/if}
                                </td>
                                <td>
                                    {if $item->credit_scorista_pd30[$group->name]}<span class="text-danger">{$item->credit_scorista_pd30[$group->name]}</span>
                                    {else}0{/if}
                                </td>
                            </tr>


                            {/foreach}



                            {/foreach}

                            <tr class="bg-gray">
                                <td>
                                    Итог
                                    <br />
                                    <a href="javascript:void(0);" class="js-scorista-open" data-index="total">
                                        <small>Скориста</small> <i class=" fas fa-caret-down"></i>
                                    </a>
                                </td>
                                <td>
                                    <strong class="text-warning">{$total_ref}</strong>
                                </td>
                                <td>
                                    <strong class="text-warning">{$total_reg}</strong>
                                    <br />
                                    <small class="text-success"><strong>{if $total_ref}{($total_reg / $total_ref * 100)|round}{else}0{/if}%</strong></small>
                                </td>
                                <td class="text-center">
                                    <strong class="text-warning">{$total_ord}</strong>
                                    <br />
                                    <small class="text-success"><strong>{if $total_ref}{($total_ord / $total_ref * 100)|round}{else}0{/if}%</strong></small>
                                </td>
                                <td class="text-center">
                                    <strong class="text-warning">{$total_auto}</strong>
                                    <br />
                                    <small class="text-success"><strong>{if $total_ref}{($total_auto / $total_ref * 100)|round}{else}0{/if}%</strong></small>
                                </td>
                                <td class="text-center">
                                    <strong class="text-warning">{$total_fms}</strong>
                                    <br />
                                    <small class="text-success"><strong>{if $total_ref}{($total_fms / $total_ref * 100)|round}{else}0{/if}%</strong></small>
                                </td>
                                <td class="text-center">
                                    <strong class="text-warning">{$total_contact}</strong>
                                    <br />
                                    <small class="text-success"><strong>{if $total_ref}{($total_contact / $total_ref * 100)|round}{else}0{/if}%</strong></small>
                                </td>
                                <td class="text-center">
                                    <strong class="text-warning">{$total_actual}</strong>
                                    <br />
                                    <small class="text-success"><strong>{if $total_ref}{($total_actual / $total_ref * 100)|round}{else}0{/if}%</strong></small>
                                </td>
                                <td class="text-center">

                                </td>
                                <td class="text-center">
                                    <strong class="text-warning">{$total_scorista}</strong>
                                    <br />
                                    <small class="text-success"><strong>{if $total_ref}{($total_scorista / $total_ref * 100)|round}{else}0{/if}%</strong></small>
                                </td>
                                <td class="text-center">

                                </td>
                                <td class="text-center">
                                    <strong class="text-warning">{$total_target}</strong>
                                    <br />
                                    <small class="text-success"><strong>{if $total_ref}{($total_target / $total_ref * 100)|round}{else}0{/if}%</strong></small>
                                </td>
                                <td class="text-center">

                                </td>
                                <td class="text-center">
                                    <strong class="text-warning">{$total_credit}</strong>
                                    <br />
                                    <small class="text-success"><strong>{if $total_ref}{($total_credit / $total_ref * 100)|round}{else}0{/if}%</strong></small>
                                </td>
                                <td class="text-center">

                                </td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>

                            {foreach $total_scorista_groups as $interval => $item}
                            <tr class="bg-grey js-scorista-item js-scorista-total">
                                <td colspan="7" class="align-right">
                                    <strong >{$interval}</strong>
                                </td>
                                <td>
                                    <strong class="text-info">
                                    {if $item['actual']}{$item['actual']}{else}0{/if}
                                    </strong>
                                </td>
                                <td>
                                    {if $item['actual']}{($item['actual']/$total_actual*100)|round}{else}0{/if}
                                </td>
                                <td>
                                    <strong class="text-info">
                                        {if $item['scorista']}{$item['scorista']}{else}0{/if}
                                    </strong>
                                </td>
                                <td>
                                    {if $item['scorista']}{($item['scorista']/$total_scorista*100)|round}{else}0{/if}
                                </td>
                                <td>
                                    <strong class="text-info">
                                        {if $item['target']}{$item['target']}{else}0{/if}
                                    </strong>
                                </td>
                                <td>
                                    {if $item['target']}{($item['target']/$total_target*100)|round}{else}0{/if}
                                </td>
                                <td>
                                    <strong class="text-info">
                                        {if $item['credit']}{$item['credit']}{else}0{/if}
                                    </strong>
                                </td>
                                <td>
                                    {if $item['credit']}{($item['credit']/$total_credit*100)|round}{else}0{/if}
                                </td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            {/foreach}

                        </table>
                        <strong class=""></strong>
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