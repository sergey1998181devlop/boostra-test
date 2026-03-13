{$meta_title='Отчет по отказным скориста' scope=parent}

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
{/capture}

<style>
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

                        <div id="result" class="">
                            <table class="table table-bordered table-hover">
                                <thead class="position-sticky">
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
                                    <tr>
                                        <td colspan="6"></td>
                                        <td>
                                            <select class="form-control" name="filterStatus">
                                                <option></option>
                                                {foreach $data['statuses'] as $statusId => $statusName}
                                                    <option value="{$statusId}" {if $filterStatus === strval($statusId)}selected="selected"{/if}>{$statusName}</option>
                                                {/foreach}
                                            </select>
                                        </td>
                                        <td colspan="4"></td>
                                        <td>
                                            <select class="form-control" name="filterSource">
                                                <option></option>
                                                {foreach $data['sources'] as $sourceName}
                                                    <option value="{$sourceName}" {if $filterSource === $sourceName}selected="selected"{/if}>{$sourceName}</option>
                                                {/foreach}
                                            </select>
                                        </td>
                                    </tr>
                                </form>
                                <tr>
                                    <th>UserID</th>
                                    <th>Клиент</th>
                                    <th>ДР</th>
                                    <th>Дата заявки</th>
                                    <th>Заявка</th>
                                    <th>Заявка 1С</th>
                                    <th>Статус</th>
                                    <th>Дата одобрения</th>
                                    <th>Дата выдачи</th>
                                    <th>Менеджер</th>
                                    <th>pdn</th>
                                    <th>Источник</th>
                                </tr>
                                </thead>
                                <tbody>
                                {if $data['items']}
                                    {foreach $data['items'] as $item}
                                        <tr>
                                            <td>{$item->user_id}</td>
                                            <td>
                                                {if $can_see_client_url}
                                                    <a href="/client/{$item->user_id}">{$item->fio}</a>
                                                {else}
                                                    {$item->fio}
                                                {/if}
                                            </td>
                                            <td>{$item->birth}</td>
                                            <td>{$item->date}</td>
                                            <td><a href="/order/{$item->order_id}">{$item->order_id}</a></td>
                                            <td>{$item->id_1c}</td>
                                            <td>{$data['statuses'][$item->status]}</td>
                                            <td>{$item->approve_date}</td>
                                            <td>{$item->issuance_date}</td>
                                            <td>
                                                {if $can_see_manager_url}
                                                    <a href="/manager/{$item->manager_id}">{$item->name_1c}</a>
                                                {else}
                                                    {$item->name_1c}
                                                {/if}
                                            </td>
                                            <td>{$item->pdn}</td>
                                            <td>{$item->utm_source}</td>
                                        </tr>
                                    {/foreach}
                                {else}
                                    <tr>
                                        <td colspan="12" class="text-danger text-center">Данные не найдены</td>
                                    </tr>
                                {/if}
                                </tbody>
                            </table>

                            <table class="table table-bordered table-hover">
                                {if $data['managers']}
                                    {foreach $data['managers'] as $fio => $counts}
                                        <tr>
                                            <td>{$fio}</td>
                                            <td>
                                                {foreach $counts as $statusId => $count}
                                                    {$data['statuses'][$statusId]} — {$count} <br/>
                                                {/foreach}
                                            </td>
                                        </tr>
                                    {/foreach}
                                {/if}
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {include file='footer.tpl'}
</div>

<script>
    function download() {
        const dateRange = $('input[name="daterange"]').val();
        const filterStatus = $('select[name="filterStatus"]').val();
        const filterSource = $('select[name="filterSource"]').val();
        const query = (new URLSearchParams({
            daterange: dateRange,
            filterStatus: filterStatus,
            filterSource: filterSource,
        })).toString();
        window.open('{$storistaUri}?action=download&' + query, '_blank');
        return false;
    }
</script>
