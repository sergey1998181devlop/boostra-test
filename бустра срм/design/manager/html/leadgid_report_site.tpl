{$meta_title='Отчет Leadgid (по сайтам)' scope=parent}

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
        
        // Списки leadgen для каждого сайта
        var leadgenBySite = {
            'boostra': [
                'leadgid', 'leadcraft', 'bankiru', 'mvp', 'leadstech', 'leadstech2', 
                'beegl', 'c2m', 'unicom24', 'cf', 'guruleads', 'cityads', 
                'leads.su', 'leadssu2', 'LW', 'LW2', 'leadtarget', 'alliance', 
                'kosmos', 'vibery', 'sravni', 'sravni2', 'rafinad', 'leadfin', 'finuslugi',
                'bankiros', 'guruleads_v2', 'Bonon', 'weblab', 'kekas', 'fin_cpa', 
                'tbank', 'tpartners', 'finuslugi-api', 'bankiru-api', 'leadstech-api'
            ],
            'soyaplace': [
                'leads_soy', 'LG_soy', 'tech_soy',
            ],
            'neomani': [
                'leads_neo', 'LG_neo', 'tech_neo', 'banki_neo', 'sravni_neo',
            ]
        };
        
        var leadgenLabels = {
            'cf': 'ЦФ'
        };
        
        function updateLeadgenList() {
            var siteId = $('select[name="site_id"]').val();
            var currentLeadgen = $('select[name="leadgen"]').val() || '{$leadgen}';
            var leadgenList = leadgenBySite[siteId] || leadgenBySite['boostra'];
            
            var $leadgenSelect = $('select[name="leadgen"]');
            $leadgenSelect.empty();
            
            $.each(leadgenList, function(index, value) {
                var label = leadgenLabels[value] || value;
                var selected = (currentLeadgen === value) ? 'selected' : '';
                $leadgenSelect.append('<option value="' + value + '" ' + selected + '>' + label + '</option>');
            });
        }

        updateLeadgenList();

        $('select[name="site_id"]').on('change', function() {
            updateLeadgenList();
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
                    url: 'leadgid_report_site',
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
                    <span>Отчет Leadgens (по сайтам)</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Отчет Leadgens (по сайтам)</li>
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
                                <div class="col-12 col-md-6">
                                    <div class="input-group mb-3">
                                        <input type="text" name="daterange" class="form-control daterange" value="{if $from && $to}{$from}-{$to}{/if}">
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <span class="ti-calendar"></span>
                                            </span>
                                        </div>
                                        <select name="leadgen" class="form-control">
                                            <!-- Опции генерируются динамически JavaScript -->
                                        </select>
                                        <select name="site_id" class="form-control">
                                            <option value="boostra" {if !$site_id || $site_id == 'boostra'}selected{/if}>boostra</option>
                                            <option value="soyaplace" {if $site_id == 'soyaplace'}selected{/if}>soyaplace</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <button type="submit" class="btn btn-info">Сформировать</button>
                                </div>
                                {if $date_from || $date_to}
                                <div class="col-12 col-md-3 text-right">
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
                                <th class="text-center">Site ID</th>
                            </tr>
                                <tr class="bg-dark text-danger">
                                    <td colspan="3">Итого:</td>
                                    <td>{$totals['orders']}</td>
                                    <td colspan="7"></td>
                                    <td>{$totals['pays']}</td>
                                    <td>{$totals['leadgen_postback']}</td>
                                    <td>{$totals['postback_hold']}</td>
                                    <td></td>
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
                                <td>
                                    {$item->site_id}
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
