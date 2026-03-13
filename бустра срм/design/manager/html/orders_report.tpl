{$meta_title='Воронка по заявкам (Новые клиенты)' scope=parent}

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
                    <span>Воронка по заявкам (Новые клиенты)</span>
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
                            Отчет за период {if $date_from}{$date_from|date} - {$date_to|date}{/if}
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
                                    <button type="submit" class="btn btn-info">Сформировать</button>
                                </div>
                            </div>
                            
                        </form>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <tr>
                                    <th>Дата</th>
                                    <th class="text-center">Заявки</th>
                                    <th class="text-center">Регион</th>
                                    <th class="text-center">Время</th>
                                    <th class="text-center">Возраст</th>
                                    <th class="text-center">ЧС</th>
                                    <th class="text-center">JuicyScore</th>
                                    <th class="text-center">ФМС</th>
                                    <th class="text-center">ФНС</th>
                                    <th class="text-center">Банкротство</th>
                                    <th class="text-center">ФССП долг</th>
                                    <th class="text-center">ФССП 46</th>
                                    <th class="text-center">Сверка анкеты</th>
                                    <th class="text-center">Скориста</th>
                                    <th class="text-center">Проверка карты</th>
                                    <th class="text-center">Выдан</th>
                                </tr>
                                {foreach $report as $item}
                                    <tr class="">
                                        <td>
                                            <strong>{$item->u_date|date}</strong>
                                        </td>
                                        <td>
                                            <strong class="text-info">{if $item->orders}{$item->orders}{else}0{/if}</strong>
                                        </td>
                                        <td>
                                            <strong class="text-info">{if $item->region}{$item->region}{else}0{/if}</strong>
                                            <br/>
                                            <small class="text-success"><strong>{if $item->orders}{($item->region / $item->orders * 100)|round}{else}0{/if}
                                                    %</strong></small>
                                        </td>
                                        <td>
                                            <strong class="text-info">
                                                {if $item->localtime}{$item->localtime}{else}0{/if}
                                            </strong>
                                            <br/>
                                            <small class="text-success"><strong>{if $item->orders}{($item->localtime / $item->orders * 100)|round}{else}0{/if}
                                                    %</strong></small>
                                        </td>
                                        <td>
                                            <strong class="text-info">
                                                {if $item->age}{$item->age}{else}0{/if}
                                            </strong>
                                            <br/>
                                            <small class="text-success"><strong>{if $item->orders}{($item->age / $item->orders * 100)|round}{else}0{/if}
                                                    %</strong></small>
                                        </td>
                                        <td>
                                            <strong class="text-info">
                                                {if $item->blacklist}{$item->blacklist}{else}0{/if}
                                            </strong>
                                            <br/>
                                            <small class="text-success"><strong>{if $item->orders}{($item->blacklist / $item->orders* 100)|round}{else}0{/if}
                                                    %</strong></small>
                                        </td>
                                        <td>
                                            <strong class="text-info">
                                                {if $item->juicescore}{$item->juicescore}{else}0{/if}
                                            </strong>
                                            <br/>
                                            <small class="text-success"><strong>{if $item->orders}{($item->juicescore / $item->orders * 100)|round}{else}0{/if}
                                                    %</strong></small>
                                        </td>
                                        <td>
                                            <strong class="text-info">
                                                {if $item->fms}{$item->fms}{else}0{/if}
                                            </strong>
                                            <br/>
                                            <small class="text-success"><strong>{if $item->orders}{($item->fms / $item->orders * 100)|round}{else}0{/if}
                                                    %</strong></small>
                                        </td>
                                        <td>
                                            <strong class="text-info">
                                                {if $item->fns}{$item->fns}{else}0{/if}
                                            </strong>
                                            <br/>
                                            <small class="text-success"><strong>{if $item->orders}{($item->fns / $item->orders * 100)|round}{else}0{/if}
                                                    %</strong></small>
                                        </td>
                                        <td>
                                            <strong class="text-info">
                                                {if $item->efrsb}{$item->efrsb}{else}0{/if}
                                            </strong>
                                            <br/>
                                            <small class="text-success"><strong>{if $item->orders}{($item->efrsb / $item->orders * 100)|round}{else}0{/if}
                                                    %</strong></small>
                                        </td>
                                        <td>
                                            <strong class="text-info">
                                                {if $item->fssp}{$item->fssp}{else}0{/if}
                                            </strong>
                                            <br/>
                                            <small class="text-success"><strong>{if $item->orders}{($item->fssp / $item->orders * 100)|round}{else}0{/if}
                                                    %</strong></small>
                                        </td>
                                        <td>
                                            <strong class="text-info">
                                                {if $item->fssp46}{$item->fssp46}{else}0{/if}
                                            </strong>
                                            <br/>
                                            <small class="text-success"><strong>{if $item->orders}{($item->fssp46 / $item->orders * 100)|round}{else}0{/if}
                                                    %</strong></small>
                                        </td>
                                        <td>
                                            <strong class="text-info">
                                                {if $item->anketa}{$item->anketa}{else}0{/if}
                                            </strong>
                                            <br/>
                                            <small class="text-success"><strong>{if $item->orders}{($item->anketa / $item->orders * 100)|round}{else}0{/if}
                                                    %</strong></small>
                                        </td>
                                        <td>
                                            <strong class="text-info">
                                                <small class="text-white">470-549</small> {if $item->scorista550}{$item->scorista550}{else}0{/if}
                                            </strong>
                                            <small class="text-success"><strong>{if $item->orders}{($item->scorista550 / $item->orders * 100)|round}{else}0{/if}
                                                    %</strong></small>
                                            <br/>
                                            <strong class="text-info">
                                                <small class="text-white">550-999</small> {if $item->scorista550plus}{$item->scorista550plus}{else}0{/if}
                                            </strong>
                                            <small class="text-success"><strong>{if $item->orders}{($item->scorista550plus / $item->orders * 100)|round}{else}0{/if}
                                                    %</strong></small>
                                        </td>
                                        <td>
                                            <strong class="text-info">
                                                <small class="text-white">470-549</small>
                                                {if $item->card550}{$item->card550}{else}0{/if}
                                            </strong>
                                            <small class="text-success"><strong>{if $item->orders}{($item->card550 / $item->orders * 100)|round}{else}0{/if}
                                                    %</strong></small>
                                            <br/>
                                            <strong class="text-info">
                                                <small class="text-white">550-999</small>
                                                {if $item->card550plus}{$item->card550plus}{else}0{/if}
                                            </strong>
                                            <small class="text-success"><strong>{if $item->orders}{($item->card550plus / $item->orders * 100)|round}{else}0{/if}
                                                    %</strong></small>
                                        </td>
                                        <td>
                                            <strong class="text-info">
                                                <small class="text-white">470-549</small>
                                                {if $item->getted550}{$item->getted550}{else}0{/if}
                                            </strong>
                                            <small class="text-success"><strong>{if $item->orders}{($item->getted550 / $item->orders * 100)|round}{else}0{/if}
                                                    %</strong></small>
                                            <br/>
                                            <strong class="text-info">
                                                <small class="text-white">550-999</small>
                                                {if $item->getted550plus}{$item->getted550plus}{else}0{/if}
                                            </strong>
                                            <small class="text-success"><strong>{if $item->orders}{($item->getted550plus / $item->orders * 100)|round}{else}0{/if}
                                                    %</strong></small>
                                        </td>
                                    </tr>
                                {/foreach}

                                <tr class="bg-gray">
                                    <td>
                                        Итог
                                    </td>
                                    <td>
                                        <strong class="text-warning">{$total['orders']}</strong>
                                    </td>
                                    <td>
                                        <strong class="text-warning">{$total['region']}</strong>
                                        <br/>
                                        <small class="text-success"><strong>{if $total['orders']}{($total['region'] / $total['orders'] * 100)|round}{else}0{/if}
                                                %</strong></small>
                                    </td>
                                    <td>
                                        <strong class="text-warning">{$total['localtime']}</strong>
                                        <br/>
                                        <small class="text-success"><strong>{if $total['orders']}{($total['localtime'] / $total['orders'] * 100)|round}{else}0{/if}
                                                %</strong></small>
                                    </td>
                                    <td>
                                        <strong class="text-warning">{$total['age']}</strong>
                                        <br/>
                                        <small class="text-success"><strong>{if $total['orders']}{($total['age'] / $total['orders'] * 100)|round}{else}0{/if}
                                                %</strong></small>
                                    </td>
                                    <td>
                                        <strong class="text-warning">{$total['blacklist']}</strong>
                                        <br/>
                                        <small class="text-success"><strong>{if $total['orders']}{($total['blacklist'] / $total['orders'] * 100)|round}{else}0{/if}
                                                %</strong></small>
                                    </td>
                                    <td>
                                        <strong class="text-warning">{$total['juicescore']}</strong>
                                        <br/>
                                        <small class="text-success"><strong>{if $total['orders']}{($total['juicescore'] / $total['orders'] * 100)|round}{else}0{/if}
                                                %</strong></small>
                                    </td>
                                    <td>
                                        <strong class="text-warning">{$total['fms']}</strong>
                                        <br/>
                                        <small class="text-success"><strong>{if $total['orders']}{($total['fms'] / $total['orders'] * 100)|round}{else}0{/if}
                                                %</strong></small>
                                    </td>
                                    <td>
                                        <strong class="text-warning">{$total['fns']}</strong>
                                        <br/>
                                        <small class="text-success"><strong>{if $total['orders']}{($total['fns'] / $total['orders'] * 100)|round}{else}0{/if}
                                                %</strong></small>
                                    </td>
                                    <td>
                                        <strong class="text-warning">{$total['efrsb']}</strong>
                                        <br/>
                                        <small class="text-success"><strong>{if $total['orders']}{($total['efrsb'] / $total['orders'] * 100)|round}{else}0{/if}
                                                %</strong></small>
                                    </td>
                                    <td>
                                        <strong class="text-warning">{$total['fssp']}</strong>
                                        <br/>
                                        <small class="text-success"><strong>{if $total['orders']}{($total['fssp'] / $total['orders'] * 100)|round}{else}0{/if}
                                                %</strong></small>
                                    </td>
                                    <td>
                                        <strong class="text-warning">{$total['fssp46']}</strong>
                                        <br/>
                                        <small class="text-success"><strong>{if $total['orders']}{($total['fssp46'] / $total['orders'] * 100)|round}{else}0{/if}
                                                %</strong></small>
                                    </td>
                                    <td>
                                        <strong class="text-warning">{$total['anketa']}</strong>
                                        <br/>
                                        <small class="text-success"><strong>{if $total['orders']}{($total['anketa'] / $total['orders'] * 100)|round}{else}0{/if}
                                                %</strong></small>
                                    </td>
                                    <td>
                                        <strong class="text-warning"><small
                                                    class="text-white">470-549</small> {$total['scorista550']}</strong>
                                        <small class="text-success"><strong>{if $total['orders']}{($total['scorista550'] / $total['orders'] * 100)|round}{else}0{/if}
                                                %</strong></small>
                                        <br/>
                                        <strong class="text-warning"><small
                                                    class="text-white">550-999</small> {$total['scorista550plus']}
                                        </strong>
                                        <small class="text-success"><strong>{if $total['orders']}{($total['scorista550plus'] / $total['orders'] * 100)|round}{else}0{/if}
                                                %</strong></small>
                                    </td>
                                    <td>
                                        <strong class="text-warning"><small
                                                    class="text-white">470-549</small> {$total['card550']}</strong>
                                        <small class="text-success"><strong>{if $total['orders']}{($total['card550'] / $total['orders'] * 100)|round}{else}0{/if}
                                                %</strong></small>
                                        <br/>
                                        <strong class="text-warning"><small
                                                    class="text-white">550-999</small> {$total['card550plus']}</strong>
                                        <small class="text-success"><strong>{if $total['orders']}{($total['card550plus'] / $total['orders'] * 100)|round}{else}0{/if}
                                                %</strong></small>
                                    </td>
                                    <td>
                                        <strong class="text-warning"><small
                                                    class="text-white">470-549</small> {$total['getted550']}</strong>
                                        <small class="text-success"><strong>{if $total['orders']}{($total['getted550'] / $total['orders'] * 100)|round}{else}0{/if}
                                                %</strong></small>
                                        <br/>
                                        <strong class="text-warning"><small
                                                    class="text-white">470-549</small> {$total['getted550plus']}
                                        </strong>
                                        <small class="text-success"><strong>{if $total['orders']}{($total['getted550plus'] / $total['orders'] * 100)|round}{else}0{/if}
                                                %</strong></small>
                                    </td>
                                </tr>
                            </table>
                        </div>
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