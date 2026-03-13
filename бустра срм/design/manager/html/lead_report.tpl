{$meta_title='Воронка по партнерам' scope=parent}

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
    })
    </script>
    
    <script>
        $(function(){
            $(document).on('click', '.js-open-day', function(e){
                e.preventDefault();
                
                if ($(this).hasClass('open'))
                {
                    $('.js-details').remove();
                    $(this).removeClass('open');
                    return false;
                }
                
                var $this = $(this);
                var _day = $(this).data('day');
                var _scorista = $('#scorista_select').val();
                
                $.ajax({
                    url: 'lead_report',
                    data: {
                        day: _day,
                        action: 'details',
                        scorista: _scorista,
                    },
                    beforeSend: function(){
                        $this.addClass('open');
                    },
                    success: function(resp){
                        $('.js-details').remove();
                        var $details = $(resp).find('.js-details')
console.log($details)
                        $this.closest('tr').after($details);
                    }
                })
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
        .jsgrid-table { margin-bottom:0}
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
                    <span>Воронка по партнерам</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Воронка по партнерам</li>
                </ol>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Start Page Content -->
        <!-- 
        В работе(это если кликнуть по кнопке "Принять")/ФССП/Ответ/Актуальность/Скориста/Цель/Выдача
         -->
        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Воронка по партнерам за период {if $date_from}{$date_from|date} - {$date_to|date}{/if}</h4>
                        <form>
                            <div class="row">
                                <div class="col-6 col-md-4">
                                    <div class="input-group mb-3">
                                        <input type="text" name="daterange" class="form-control daterange" value="{if $from && $to}{$from}-{$to}{/if}">
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <span class="ti-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="mb-3">
                                        <select class="form-control" id="scorista_select" name="scorista">
                                            <option value="all" {if !$filter_scorista}selected{/if}>Все</option>
                                            <option value="0-449" {if $filter_scorista == '0-449'}selected{/if}>0-449</option>
                                            <option value="450-599" {if $filter_scorista == '450-599'}selected{/if}>450-599</option>
                                            <option value="600-699" {if $filter_scorista == '600-699'}selected{/if}>600-699</option>
                                            <option value="700+" {if $filter_scorista=='700+'}selected{/if}>700+</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <button type="submit" class="btn btn-info">Сформировать</button>
                                </div>
                            </div>
                            
                        </form>                        
                        <table class="table table-hover">
                            
                            <tr>
                                <th>Дата</th>
                                <th>В работе</th>
                                <th>ФССП</th>
                                <th>Ответы</th>
                                <th>Актуальна</th>
                                <th>Скориста</th>
                                <th>Цель</th>
                                <th>Приняты и выданы</th>
                                <th>Поданы и выданы</th>
                                <th>Не отказ и не выдача</th>
                            </tr>
                            
                            {foreach $report as $item}
                            <tr>
                                <td>
                                    <a href="javascript:void(0)" data-day="{$item->u_date|date}" class="js-open-day">{$item->u_date|date}</a>
                                </td>
                                <td>
                                    <strong>{if $item->in_work}{$item->in_work}{else}0{/if}</strong>
                                </td>
                                <td>
                                    <strong>{if $item->fssp}{$item->fssp}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($item->in_work - $item->fssp)/$item->in_work*100)|round}%</small>
                                </td>
                                <td>
                                    <strong>{if $item->contact}{$item->contact}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($item->in_work - $item->contact)/$item->in_work*100)|round}%</small>
                                </td>
                                <td>
                                    <strong>{if $item->actual}{$item->actual}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($item->in_work - $item->actual)/$item->in_work*100)|round}%</small>
                                </td>
                                <td>
                                    <strong>{if $item->scorista}{$item->scorista}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($item->in_work - $item->scorista)/$item->in_work*100)|round}%</small>
                                </td>
                                <td>
                                    <strong>{if $item->target}{$item->target}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($item->in_work - $item->target)/$item->in_work*100)|round}%</small>
                                </td>
                                <td>
                                    <strong>{if $item->credit}{$item->credit}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($item->in_work - $item->credit)/$item->in_work*100)|round}%</small>
                                </td>
                                <td>
                                    <strong>{if $item->today}{$item->today}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($item->in_work - $item->today)/$item->in_work*100)|round}%</small>
                                </td>
                                <td>
                                    <strong>{if $item->notend}{$item->notend}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($item->in_work - $item->notend)/$item->in_work*100)|round}%</small>
                                </td>
                            </tr>
                            {/foreach}
                            
                            {if $details}
                            {foreach $details as $source => $item}
                            <tr class="js-details">
                                <td>{$source}</td>
                                <td>
                                    <strong>{if $item->in_work}{$item->in_work}{else}0{/if}</strong>
                                </td>
                                <td>
                                    <strong>{if $item->fssp}{$item->fssp}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($item->in_work - $item->fssp)/$item->in_work*100)|round}%</small>
                                </td>
                                <td>
                                    <strong>{if $item->contact}{$item->contact}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($item->in_work - $item->contact)/$item->in_work*100)|round}%</small>
                                </td>
                                <td>
                                    <strong>{if $item->actual}{$item->actual}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($item->in_work - $item->actual)/$item->in_work*100)|round}%</small>
                                </td>
                                <td>
                                    <strong>{if $item->scorista}{$item->scorista}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($item->in_work - $item->scorista)/$item->in_work*100)|round}%</small>
                                </td>
                                <td>
                                    <strong>{if $item->target}{$item->target}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($item->in_work - $item->target)/$item->in_work*100)|round}%</small>
                                </td>
                                <td>
                                    <strong>{if $item->credit}{$item->credit}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($item->in_work - $item->credit)/$item->in_work*100)|round}%</small>
                                </td>
                                <td>
                                    <strong>{if $item->today}{$item->today}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($item->in_work - $item->today)/$item->in_work*100)|round}%</small>
                                </td>
                                <td>
                                    <strong>{if $item->notend}{$item->notend}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($item->in_work - $item->notend)/$item->in_work*100)|round}%</small>
                                </td>
                            </tr>
                            {/foreach}
                            {/if}
                            
                            {$days = 0}
                            {$in_work = 0}
                            {$fssp = 0}
                            {$contact = 0}
                            {$actual = 0}
                            {$scorista = 0}
                            {$target = 0}
                            {$credit = 0}
                            {$today = 0}
                            {$notend = 0}
                            {foreach $report as $item}
                                {$days = $days + 1}
                                {$in_work = $in_work + $item->in_work}
                                {$fssp = $fssp + $item->fssp}
                                {$contact = $contact + $item->contact}
                                {$actual = $actual + $item->actual}
                                {$scorista = $scorista + $item->scorista}
                                {$target = $target + $item->target}
                                {$credit = $credit + $item->credit}
                                {$today = $today + $item->today}
                                {$notend = $notend + $item->notend}
                            {/foreach}
                            
                            {if $days > 1}
                            
                            <tr>
                                <td>
                                    Общий
                                </td>
                                <td>
                                    <strong>{if $in_work}{$in_work}{else}0{/if}</strong>
                                </td>
                                <td>
                                    <strong>{if $fssp}{$fssp}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($in_work - $fssp)/$in_work*100)|round}%</small>
                                </td>
                                <td>
                                    <strong>{if $contact}{$contact}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($in_work - $contact)/$in_work*100)|round}%</small>
                                </td>
                                <td>
                                    <strong>{if $actual}{$actual}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($in_work - $actual)/$in_work*100)|round}%</small>
                                </td>
                                <td>
                                    <strong>{if $scorista}{$scorista}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($in_work - $scorista)/$in_work*100)|round}%</small>
                                </td>
                                <td>
                                    <strong>{if $target}{$target}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($in_work - $target)/$in_work*100)|round}%</small>
                                </td>
                                <td>
                                    <strong>{if $credit}{$credit}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($in_work - $credit)/$in_work*100)|round}%</small>
                                </td>
                                <td>
                                    <strong>{if $today}{$today}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($in_work - $today)/$in_work*100)|round}%</small>
                                </td>
                                <td>
                                    <strong>{if $notend}{$notend}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($in_work - $notend)/$in_work*100)|round}%</small>
                                </td>
                            </tr>
                            
                            <tr>
                                <td>
                                    Средний
                                </td>
                                <td>
                                    <strong>{if $in_work}{($in_work/$days)|ceil}{else}0{/if}</strong>
                                </td>
                                <td>
                                    <strong>{if $fssp}{($fssp/$days)|ceil}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($in_work - $fssp)/$in_work*100)|round}%</small>
                                </td>
                                <td>
                                    <strong>{if $contact}{($contact/$days)|ceil}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($in_work - $contact)/$in_work*100)|round}%</small>
                                </td>
                                <td>
                                    <strong>{if $actual}{($actual/$days)|ceil}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($in_work - $actual)/$in_work*100)|round}%</small>
                                </td>
                                <td>
                                    <strong>{if $scorista}{($scorista/$days)|ceil}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($in_work - $scorista)/$in_work*100)|round}%</small>
                                </td>
                                <td>
                                    <strong>{if $target}{($target/$days)|ceil}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($in_work - $target)/$in_work*100)|round}%</small>
                                </td>
                                <td>
                                    <strong>{if $credit}{($credit/$days)|ceil}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($in_work - $credit)/$in_work*100)|round}%</small>
                                </td>
                                <td>
                                    <strong>{if $today}{($today/$days)|ceil}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($in_work - $today)/$in_work*100)|round}%</small>
                                </td>
                                <td>
                                    <strong>{if $notend}{($notend/$days)|ceil}{else}0{/if}</strong>
                                    <small class="text-info">{(100-($in_work - $notend)/$in_work*100)|round}%</small>
                                </td>
                            </tr>
                            
                            {/if}
                            
                        </table>
                        
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