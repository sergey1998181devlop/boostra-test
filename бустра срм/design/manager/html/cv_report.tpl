{$meta_title='Общая воронка' scope=parent}

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
                    <span>Общая воронка</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Общая воронка</li>
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
                        <h4 class="card-title">Общая воронка за период {if $date_from}{$date_from|date} - {$date_to|date}{/if}</h4>
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
                                <th>Переходы</th>
                                <th>Авторизации</th>
                                <th class="text-center">
                                    Заявки
                                    <br />
                                    <small class="text-info">НК</small> / <small class="text-warning">ПК</small>
                                </th>
                                <th class="text-center">
                                    Авто
                                    <br />
                                    <small class="text-info">НК</small> / <small class="text-warning">ПК</small>
                                </th>
                                <th class="text-center">
                                    ФССП
                                    <br />
                                    <small class="text-info">НК</small> / <small class="text-warning">ПК</small>
                                </th>
                                <th class="text-center">
                                    Ответы
                                    <br />
                                    <small class="text-info">НК</small> / <small class="text-warning">ПК</small>
                                </th>
                                <th class="text-center">
                                    Актуальна
                                    <br />
                                    <small class="text-info">НК</small> / <small class="text-warning">ПК</small>
                                </th>
                                <th class="text-center">
                                    Скориста
                                    <br />
                                    <small class="text-info">НК</small> / <small class="text-warning">ПК</small>
                                </th>
                                <th class="text-center">
                                    Цель
                                    <br />
                                    <small class="text-info">НК</small> / <small class="text-warning">ПК</small>
                                </th>
                                <th class="text-center">
                                    Выдача
                                    <br />
                                    <small class="text-info">НК</small> / <small class="text-warning">ПК</small>
                                </th>
                            </tr>
                            
                            {foreach $report as $item}
                            <tr>
                                <td>
                                    <strong >{$item->u_date|date}</strong>
                                </td>
                                <td>
                                    <strong class="text-info">{$item->r_total}</strong>
                                </td>
                                <td>
                                    <strong class="text-info">{$item->u_total}</strong>
                                    <br />
                                    <small class="text-info">{if $item->r_total}{(100-($item->r_total - $item->u_total)/$item->r_total*100)|round}{else}0{/if}%</small>
                                </td>
                                <td>
                                    <strong class="text-info">
                                        {if $item->o_total}{$item->o_total}{else}0{/if}
                                    </strong>
                                        /
                                    <strong class="text-warning">
                                        {if $item->o_total_repeat}{$item->o_total_repeat}{else}0{/if}
                                    </strong>
                                    <br />
                                    <small class="text-info">
                                        {if $item->r_total}{(100-($item->r_total - $item->o_total)/$item->r_total*100)|round}{else}0{/if}%
                                    </small>
                                        /
                                    <small class="text-warning">
                                        100%
                                    </small>
                                </td>
                                <td>
                                    <strong class="text-info">
                                        {if $item->auto}{$item->auto}{else}0{/if}
                                    </strong>
                                        /
                                    <strong class="text-warning">
                                        {if $item->auto_repeat}{$item->auto_repeat}{else}0{/if}
                                    </strong>
                                    <br />
                                    <small class="text-info">
                                        {if $item->r_total}{(100-($item->r_total - $item->auto)/$item->r_total*100)|round}{else}0{/if}%
                                    </small>
                                        /
                                    <small class="text-warning">
                                        {if $item->o_total_repeat}{(100-($item->o_total_repeat - $item->auto_repeat)/$item->o_total_repeat*100)|round}{else}0{/if}%
                                    </small>
                                </td>
                                <td>
                                    <strong class="text-info">
                                        {if $item->fms}{$item->fms}{else}0{/if}
                                    </strong>
                                        /
                                    <strong class="text-warning">
                                        {if $item->fms_repeat}{$item->fms_repeat}{else}0{/if}
                                    </strong>
                                    <br />
                                    <small class="text-info">
                                        {if $item->r_total}{(100-($item->r_total - $item->fms)/$item->r_total*100)|round}{else}0{/if}%
                                    </small>
                                        /
                                    <small class="text-warning">
                                        {if $item->o_total_repeat}{(100-($item->o_total_repeat - $item->fms_repeat)/$item->o_total_repeat*100)|round}{else}0{/if}%
                                    </small>
                                </td>
                                <td>
                                    <strong class="text-info">
                                        {if $item->contact}{$item->contact}{else}0{/if}
                                    </strong>
                                        /
                                    <strong class="text-warning">
                                        {if $item->contact_repeat}{$item->contact_repeat}{else}0{/if}
                                    </strong>
                                    <span class="ml-1 text-success"><small>{if $item->diff}{$item->diff}{else}0{/if}</small></span>
                                    <br />
                                    <small class="text-info">
                                        {if $item->r_total}{(100-($item->r_total - $item->contact)/$item->r_total*100)|round}{else}0{/if}%
                                    </small>
                                        /
                                    <small class="text-warning">
                                        {if $item->o_total_repeat}{(100-($item->o_total_repeat - $item->contact_repeat)/$item->o_total_repeat*100)|round}{else}0{/if}%
                                    </small>
                                </td>
                                <td>
                                    <strong class="text-info">
                                        {if $item->actual}{$item->actual}{else}0{/if}
                                    </strong>
                                        /
                                    <strong class="text-warning">
                                        {if $item->actual_repeat}{$item->actual_repeat}{else}0{/if}
                                    </strong>
                                    <br />
                                    <small class="text-info">
                                        {if $item->r_total}{(100-($item->r_total - $item->actual)/$item->r_total*100)|round}{else}0{/if}%
                                    </small>
                                        /
                                    <small class="text-warning">
                                        {if $item->o_total_repeat}{(100-($item->o_total_repeat - $item->actual_repeat)/$item->o_total_repeat*100)|round}{else}0{/if}%
                                    </small>
                                </td>
                                <td>
                                    <strong class="text-info">
                                        {if $item->scorista}{$item->scorista}{else}0{/if}
                                    </strong>
                                        /
                                    <strong class="text-warning">
                                        {if $item->scorista_repeat}{$item->scorista_repeat}{else}0{/if}
                                    </strong>
                                    <br />
                                    <small class="text-info">
                                        {if $item->r_total}{(100-($item->r_total - $item->scorista)/$item->r_total*100)|round}{else}0{/if}%
                                    </small>
                                        /
                                    <small class="text-warning">
                                        {if $item->o_total_repeat}{(100-($item->o_total_repeat - $item->scorista_repeat)/$item->o_total_repeat*100)|round}{else}0{/if}%
                                    </small>
                                </td>
                                <td>
                                    <strong class="text-info">
                                        {if $item->target}{$item->target}{else}0{/if}
                                    </strong>
                                        /
                                    <strong class="text-warning">
                                        {if $item->target_repeat}{$item->target_repeat}{else}0{/if}
                                    </strong>
                                    <br />
                                    <small class="text-info">
                                        {if $item->r_total}{(100-($item->r_total - $item->target)/$item->r_total*100)|round}{else}0{/if}%
                                    </small>
                                        /
                                    <small class="text-warning">
                                        {if $item->o_total_repeat}{(100-($item->o_total_repeat - $item->target_repeat)/$item->o_total_repeat*100)|round}{else}0{/if}%
                                    </small>
                                </td>
                                <td>
                                    <strong class="text-info">
                                        {if $item->credit}{$item->credit}{else}0{/if}
                                    </strong>
                                        /
                                    <strong class="text-warning">
                                        {if $item->credit_repeat}{$item->credit_repeat}{else}0{/if}
                                    </strong>
                                    <br />
                                    <small class="text-info">
                                        {if $item->r_total}{(100-($item->r_total - $item->credit)/$item->r_total*100)|round}{else}0{/if}%
                                    </small>
                                        /
                                    <small class="text-warning">
                                        {if $item->o_total_repeat}{(100-($item->o_total_repeat - $item->credit_repeat)/$item->o_total_repeat*100)|round}{else}0{/if}%
                                    </small>
                                </td>
                            </tr>
                            {/foreach}
                            
                            {$days = 0}
                            {$r_total = 0}
                            {$u_total = 0}
                            {$o_total = 0}
                            {$o_total_repeat = 0}
                            {$auto = 0}
                            {$auto_repeat = 0}
                            {$fms = 0}
                            {$fms_repeat = 0}
                            {$contact = 0}
                            {$contact_repeat = 0}
                            {$actual = 0}
                            {$actual_repeat = 0}
                            {$scorista = 0}
                            {$scorista_repeat = 0}
                            {$target = 0}
                            {$target_repeat = 0}
                            {$credit = 0}
                            {$credit_repeat = 0}
                            {foreach $report as $item}
                                {$days = $days + 1}
                                {$r_total= $r_total + $item->r_total}
                                {$u_total = $u_total + $item->u_total}
                                {$o_total = $o_total + $item->o_total}
                                {$o_total_repeat = $o_total_repeat + $item->o_total_repeat}
                                {$auto = $auto + $item->auto}
                                {$auto_repeat = $auto_repeat + $item->auto_repeat}
                                {$fms = $fms + $item->fms}
                                {$fms_repeat = $fms_repeat + $item->fms_repeat}
                                {$contact = $contact + $item->contact}
                                {$contact_repeat = $contact_repeat + $item->contact_repeat}
                                {$actual = $actual + $item->actual}
                                {$actual_repeat = $actual_repeat + $item->actual_repeat}
                                {$scorista = $scorista + $item->scorista}
                                {$scorista_repeat = $scorista_repeat + $item->scorista_repeat}
                                {$target = $target + $item->target}
                                {$target_repeat = $target_repeat + $item->target_repeat}
                                {$credit = $credit + $item->credit}
                                {$credit_repeat = $credit_repeat + $item->credit_repeat}
                            {/foreach}
                            
                            {if $days > 1}
                            <tr>
                                <td>
                                    <strong>Обший</strong>
                                </td>
                                <td>
                                    
                                    <strong class="text-info">{$r_total}</strong>
                                </td>
                                <td>
                                    <strong class="text-info">{$u_total}</strong>
                                    <br />
                                    <small class="text-info">{if $r_total}{(100-($r_total - $u_total)/$r_total*100)|round}{else}0{/if}%</small>
                                </td>
                                <td>
                                    <strong class="text-info">{if $o_total}{$o_total}{else}0{/if}</strong>
                                    /
                                    <strong class="text-warning">{if $o_total_repeat}{$o_total_repeat}{else}0{/if}</strong>
                                    <br />
                                    <small class="text-info">
                                    {if $r_total}{(100-($r_total - $o_total)/$r_total*100)|round}{else}0{/if}%
                                    </small>
                                    /
                                    <small class="text-warning">
                                    {if $r_total}100{else}0{/if}%
                                    </small>
                                </td>
                                <td>
                                    <strong class="text-info">{if $auto}{$auto}{else}0{/if}</strong>
                                    /
                                    <strong class="text-warning">{if $auto_repeat}{$auto_repeat}{else}0{/if}</strong>
                                    <br />
                                    <small class="text-info">{if $r_total}{(100-($r_total - $auto)/$r_total*100)|round}{else}0{/if}%</small>
                                    /
                                    <small class="text-warning">{if $o_total_repeat}{(100-($o_total_repeat - $auto_repeat)/$o_total_repeat*100)|round}{else}0{/if}%</small>
                                </td>
                                <td>
                                    <strong class="text-info">{if $fms}{$fms}{else}0{/if}</strong>
                                    /
                                    <strong class="text-warning">{if $fms_repeat}{$fms_repeat}{else}0{/if}</strong>
                                    <br />
                                    <small class="text-info">{if $r_total}{(100-($r_total - $fms)/$r_total*100)|round}{else}0{/if}%</small>
                                    /
                                    <small class="text-warning">{if $o_total_repeat}{(100-($o_total_repeat - $fms_repeat)/$o_total_repeat*100)|round}{else}0{/if}%</small>
                                </td>
                                <td>
                                    <strong class="text-info">{if $contact}{$contact}{else}0{/if}</strong>
                                    /
                                    <strong class="text-warning">{if $contact_repeat}{$contact_repeat}{else}0{/if}</strong>
                                    <br />
                                    <small class="text-info">{if $r_total}{(100-($r_total - $contact)/$r_total*100)|round}{else}0{/if}%</small>
                                    /
                                    <small class="text-warning">{if $o_total_repeat}{(100-($o_total_repeat - $contact_repeat)/$o_total_repeat*100)|round}{else}0{/if}%</small>
                                </td>
                                <td>
                                    <strong class="text-info">{if $actual}{$actual}{else}0{/if}</strong>
                                    /
                                    <strong class="text-warning">{if $actual_repeat}{$actual_repeat}{else}0{/if}</strong>
                                    <br />
                                    <small class="text-info">{if $r_total}{(100-($r_total - $actual)/$r_total*100)|round}{else}0{/if}%</small>
                                    /
                                    <small  class="text-warning">{if $o_total_repeat}{(100-($o_total_repeat - $actual_repeat)/$o_total_repeat*100)|round}{else}0{/if}%</small>
                                </td>
                                <td>
                                    <strong class="text-info">{if $scorista}{$scorista}{else}0{/if}</strong>
                                    /
                                    <strong class="text-warning">{if $scorista_repeat}{$scorista_repeat}{else}0{/if}</strong>
                                    <br />
                                    <small class="text-info">{if $r_total}{(100-($r_total - $scorista)/$r_total*100)|round}{else}0{/if}%</small>
                                    /
                                    <small class="text-warning">{if $o_total_repeat}{(100-($o_total_repeat - $scorista_repeat)/$o_total_repeat*100)|round}{else}0{/if}%</small>
                                </td>
                                <td>
                                    <strong class="text-info">{if $target}{$target}{else}0{/if}</strong>
                                    /
                                    <strong class="text-warning">{if $target_repeat}{$target_repeat}{else}0{/if}</strong>
                                    <br />
                                    <small class="text-info">{if $r_total}{(100-($r_total - $target)/$r_total*100)|round}{else}0{/if}%</small>
                                    /
                                    <small class="text-warning">{if $o_total_repeat}{(100-($o_total_repeat - $target_repeat)/$o_total_repeat*100)|round}{else}0{/if}%</small>
                                </td>
                                <td>
                                    <strong class="text-info">{if $credit}{$credit}{else}0{/if}</strong>
                                    /
                                    <strong class="text-warning">{if $credit_repeat}{$credit_repeat}{else}0{/if}</strong>
                                    <br />
                                    <small class="text-info">{if $r_total}{(100-($r_total - $credit)/$r_total*100)|round}{else}0{/if}%</small>
                                    /
                                    <small class="text-warning">{if $o_total_repeat}{(100-($o_total_repeat - $credit_repeat)/$o_total_repeat*100)|round}{else}0{/if}%</small>
                                </td>
                            </tr>
                            
                            <tr>
                                <td>
                                    <strong>Средний</strong>
                                </td>
                                <td>
                                    
                                    <strong class="text-info">{if $days}{($r_total/$days)|ceil}{/if}</strong>
                                </td>
                                <td>
                                    <strong class="text-info">{if $days}{($u_total/$days)|ceil}{else}0{/if}</strong>
                                    <br />
                                    <small class="text-info">{if $r_total}{(100-($r_total - $u_total)/$r_total*100)|round}{else}0{/if}%</small>
                                </td>
                                <td>
                                    <strong class="text-info">{if $o_total}{($o_total/$days)|ceil}{else}0{/if}</strong>
                                    /
                                    <strong class="text-warning">{if $o_total_repeat}{($o_total_repeat/$days)|ceil}{else}0{/if}</strong>
                                    <br />
                                    <small class="text-info">{if $r_total}{(100-($r_total - $o_total)/$r_total*100)|round}{else}0{/if}%</small>
                                    /
                                    <small class="text-warning">{if $o_total_repeat}100{else}0{/if}%</small>
                                </td>
                                <td>
                                    <strong class="text-info">{if $auto}{($auto/$days)|ceil}{else}0{/if}</strong>
                                    /
                                    <strong class="text-warning">{if $auto_repeat}{($auto_repeat/$days)|ceil}{else}0{/if}</strong>
                                    <br />
                                    <small class="text-info">{if $r_total}{(100-($r_total - $auto)/$r_total*100)|round}{else}0{/if}%</small>
                                    /
                                    <small class="text-warning">{if $o_total_repeat}{(100-($o_total_repeat - $auto_repeat)/$o_total_repeat*100)|round}{else}0{/if}%</small>
                                </td>
                                <td>
                                    <strong class="text-info">{if $fms}{($fms/$days)|ceil}{else}0{/if}</strong>
                                    /
                                    <strong class="text-warning">{if $fms_repeat}{($fms_repeat/$days)|ceil}{else}0{/if}</strong>
                                    <br />
                                    <small class="text-info">{if $r_total}{(100-($r_total - $fms)/$r_total*100)|round}{else}0{/if}%</small>
                                    /
                                    <small class="text-warning">{if $o_total_repeat}{(100-($o_total_repeat - $fms_repeat)/$o_total_repeat*100)|round}{else}0{/if}%</small>
                                </td>
                                <td>
                                    <strong class="text-info">{if $contact}{($contact/$days)|ceil}{else}0{/if}</strong>
                                    /
                                    <strong class="text-warning">{if $contact_repeat}{($contact_repeat/$days)|ceil}{else}0{/if}</strong>
                                    <br />
                                    <small class="text-info">{if $r_total}{(100-($r_total - $contact)/$r_total*100)|round}{else}0{/if}%</small>
                                    /
                                    <small class="text-warning">{if $o_total_repeat}{(100-($o_total_repeat - $contact_repeat)/$o_total_repeat*100)|round}{else}0{/if}%</small>
                                </td>
                                <td>
                                    <strong class="text-info">{if $actual}{($actual/$days)|ceil}{else}0{/if}</strong>
                                    /
                                    <strong class="text-warning">{if $actual_repeat}{($actual_repeat/$days)|ceil}{else}0{/if}</strong>
                                    <br />
                                    <small class="text-info">{if $r_total}{(100-($r_total - $actual)/$r_total*100)|round}{else}0{/if}%</small>
                                    /
                                    <small class="text-warning">{if $o_total_repeat}{(100-($o_total_repeat - $actual_repeat)/$o_total_repeat*100)|round}{else}0{/if}%</small>
                                </td>
                                <td>
                                    <strong class="text-info">{if $scorista}{($scorista/$days)|ceil}{else}0{/if}</strong>
                                    /
                                    <strong class="text-warning">{if $scorista_repeat}{($scorista_repeat/$days)|ceil}{else}0{/if}</strong>
                                    <br />
                                    <small class="text-info">{if $r_total}{(100-($r_total - $scorista)/$r_total*100)|round}{else}0{/if}%</small>
                                    /
                                    <small class="text-warning">{if $o_total_repeat}{(100-($o_total_repeat - $scorista_repeat)/$o_total_repeat*100)|round}{else}0{/if}%</small>
                                </td>
                                <td>
                                    <strong class="text-info">{if $target}{($target/$days)|ceil}{else}0{/if}</strong>
                                    /
                                    <strong class="text-warning">{if $target_repeat}{($target_repeat/$days)|ceil}{else}0{/if}</strong>
                                    <br />
                                    <small class="text-info">{if $r_total}{(100-($r_total - $target)/$r_total*100)|round}{else}0{/if}%</small>
                                    /
                                    <small class="text-warning">{if $o_total_repeat}{(100-($o_total_repeat - $target_repeat)/$o_total_repeat*100)|round}{else}0{/if}%</small>
                                </td>
                                <td>
                                    
                                    <strong class="text-info">{if $credit}{($credit/$days)|ceil}{else}0{/if}</strong>
                                    /
                                    <strong class="text-warning">{if $credit_repeat}{($credit_repeat/$days)|ceil}{else}0{/if}</strong>
                                    <br />
                                    <small class="text-info">{if $r_total}{(100-($r_total - $credit)/$r_total*100)|round}{else}0{/if}%</small>
                                    /
                                    <small class="text-warning">{if $o_total_repeat}{(100-($o_total_repeat - $credit_repeat)/$o_total_repeat*100)|round}{else}0{/if}%</small>
                                </td>
                            </tr>
                            
                            {/if}
                            
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