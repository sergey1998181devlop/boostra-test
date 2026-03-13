{$meta_title='Отчет Leadgid' scope=parent}

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
    
    .table{
        overflow-x: auto;
        display: block;
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
                    <span>Отчет Leadgens</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Отчет Leadgens</li>
                </ol>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Start Page Content -->
        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Отчет {$leadgen} за период {if $date_from}{$date_from|date} - {$date_to|date}{/if}</h4>
                        <p><a href="https://docs.google.com/spreadsheets/d/13ZH7Dref8yZQ5uh9dPjhzWzcGMmD35-M113IpDgdmQg/edit#gid=1108670912" target="_blank">Памятка постбеков</a></p>
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
                                        <select name="leadgen" class="form-control">
                                            <option {if $leadgen == 'leadgid'}selected{/if}>leadgid</option>
                                            <option {if $leadgen == 'leadcraft'}selected{/if}>leadcraft</option>
                                            <option {if $leadgen == 'bankiru'}selected{/if}>bankiru</option>
                                            <option {if $leadgen == 'mvp'}selected{/if}>mvp</option>
                                            <option {if $leadgen == 'mvp_dir'}selected{/if}>mvp_dir</option>
                                            <option {if $leadgen == 'leadstech'}selected{/if}>leadstech</option>
                                            <option {if $leadgen == 'leadstech2'}selected{/if}>leadstech2</option>
                                            <option {if $leadgen == 'beegl'}selected{/if}>beegl</option>
                                            <option {if $leadgen == 'c2m'}selected{/if}>c2m</option>
                                            <option {if $leadgen == 'unicom24'}selected{/if}>unicom24</option>
                                            <option value="cf" {if $leadgen == 'cf'}selected{/if}>ЦФ</option>
                                            <option {if $leadgen == 'guruleads'}selected{/if}>guruleads</option>
                                            <option {if $leadgen == 'cityads'}selected{/if}>cityads</option>
                                            <option {if $leadgen == 'leads.su'}selected{/if}>leads.su</option>
                                            <option {if $leadgen == 'leadssu2'}selected{/if}>leadssu2</option>
                                            <option {if $leadgen == 'LW'}selected{/if}>LW</option>
                                            <option {if $leadgen == 'LW2'}selected{/if}>LW2</option>
                                            <option {if $leadgen == 'LW3'}selected{/if}>LW3</option>
                                            <option {if $leadgen == 'leadtarget'}selected{/if}>leadtarget</option>
                                            <option {if $leadgen == 'alliance'}selected{/if}>alliance</option>
                                            <option {if $leadgen == 'kosmos'}selected{/if}>kosmos</option>
                                            <option {if $leadgen == 'vibery'}selected{/if}>vibery</option>
                                            <option {if $leadgen == 'sravni'}selected{/if}>sravni</option>
                                            <option {if $leadgen == 'sravni2'}selected{/if}>sravni2</option>
                                            <option {if $leadgen == 'rafinad'}selected{/if}>rafinad</option>
                                            <option {if $leadgen == 'leadfin'}selected{/if}>leadfin</option>
                                            <option {if $leadgen == 'finuslugi'}selected{/if}>finuslugi</option>
                                            <option {if $leadgen == 'bankiros'}selected{/if}>bankiros</option>
                                            <option {if $leadgen == 'guruleads_v2'}selected{/if}>guruleads_v2</option>
                                            <option {if $leadgen == 'Bonon'}selected{/if}>Bonon</option>
                                            <option {if $leadgen == 'weblab'}selected{/if}>WebLab</option>
                                            <option {if $leadgen == 'kekas'}selected{/if}>kekas</option>
                                            <option {if $leadgen == 'fin_cpa'}selected{/if}>fin_cpa</option>
                                            <option {if $leadgen == 'tbank'}selected{/if}>tbank</option>
                                            <option {if $leadgen == 'tpartners'}selected{/if}>tpartners</option>
                                            <option {if $leadgen == 'finuslugi-api'}selected{/if}>finuslugi-api</option>
                                            <option {if $leadgen == 'bankiru-api'}selected{/if}>bankiru-api</option>
                                            <option {if $leadgen == 'leadstech-api'}selected{/if}>leadstech-api</option>
                                            <option {if $leadgen == 'lead.su'}selected{/if}>lead.su</option>
                                            <option {if $leadgen == 'bonon-api'}selected{/if}>bonon-api</option>
                                            <option {if $leadgen == 'unicom_mob'}selected{/if}>unicom_mob</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <button type="submit" class="btn btn-info">Сформировать</button>
                                </div>
                                {if $date_from || $date_to}
                                <div class="col-12 col-md-4 text-right">
                                    <a href="{url download='excel'}" class="btn btn-success ">
                                        <i class="fas fa-file-excel"></i> Скачать
                                    </a>
                                </div>
                                {/if}
                            </div>
                            
                        </form>                        
                        <table class="table table-hover table-bordered">
                            


                            <thead style="position: sticky;top: 0;">
                            <tr class="bg-gray" >
                                <th class="text-center">ID вебмастера</th>
                                <th class="text-center">Источник</th>
                                <th class="text-center">Кликхеш</th>
                                <th class="text-center">ID заявки</th>
                                <th class="text-center">1C id заявки</th>
                                <th class="text-center">Дата заявки</th>
                                <th class="text-center">Статус</th>
                                <th class="text-center">Причина отказа</th>
                                <th class="text-center">Дата выдачи</th>
                                <th class="text-center">Скориста</th>
                                <th class="text-center">Дата Постбека</th>
                                <th class="text-center">Выплата</th>
                                <th class="text-center">Постбэк о выдачи НК</th>
                                <th class="text-center">Постбэк Заявка НК</th>
                            </tr>
                                <tr class="bg-dark text-danger">
                                    <td colspan="3">Итого:</td>
                                    <td>{$totals['orders']}</td>
                                    <td colspan="7"></td>
                                    <td>{$totals['pays']}</td>
                                    <td>{$totals['leadgen_postback']}</td>
                                    <td>{$totals['postback_hold']}</td>
                                </tr>
                            </thead>

                            {foreach $report as $item}
                            <tr>
                                <td>
                                    {$item->webmaster_id}
                                </td>
                                <td>
                                    {if $item->utm_source == 'cf'}
                                        ЦФ
                                    {else}
                                        {$item->utm_source}
                                    {/if}
                                </td>
                                <td>
                                    {$item->click_hash}
                                </td>
                                <td>
                                    <a href="order/{$item->order_id}" target="_blank"><strong>{$item->order_id}</strong></a>
                                </td>
                                <td>
                                    {$item->{'1c_id'}}
                                </td>
                                <td>
                                    {$item->order_date|date} {$item->order_date|time}
                                </td>
                                <td>
                                    {if $item->status == 1}<span class="label label-info">Новая</span>
                                    {elseif $item->status == 2}<span class="label label-success">Одобрена</span>
                                    {elseif $item->status == 3}<span class="label label-danger">Отказ</span>
                                    {elseif $item->status == 10 || $item->status_1c == '5.Выдан'}<span class="label label-light-success">Выдана</span>
                                    {/if}
                                </td>
                                <td>
                                    {$item->reason}
                                </td>
                                <td>
                                    {if $item->confirm_date}
                                        {$item->confirm_date|date} {$item->confirm_date|time}
                                    {/if}
                                </td>
                                <td>
                                    {$item->scorista_ball}
                                </td>
                                <td>
                                    {if $item->leadgid_postback_date}
                                    {$item->leadgid_postback_date|date} {$item->leadgid_postback_date|time}
                                    {/if}
                                </td>
                                <td>
                                    {$item->payout_grade}
                                </td>
                                <td>
                                    {$item->leadgen_postback}
                                </td>
                                <td>
                                    {$item->postback_hold}
                                </td>
                            </tr>
                            {/foreach}
                            
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
