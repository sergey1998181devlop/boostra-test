{$meta_title='Отчеты НБКИ' scope=parent}

{capture name='page_scripts'}
    <script>
        $(function(){
            $('.js-sent-checkbox').change(function(e){
                e.preventDefault();
                
                var $this = $(this),
                report_id = $(this).data('report'),
                sent = $(this).is(':checked') ? 1 : 0;
                
                $.ajax({
                    type: 'POST',
                    data: {
                        action: 'set_sent',
                        report_id: report_id,
                        sent: sent
                    },
                    beforeSend: function(){
                        $this.addClass('loading');
                    },
                    success: function(resp){
                        $this.removeClass('loading');
                        if (!!resp.error) {
                            Swal.fire({
                                title: 'Ошибка!',
                                text: resp.error,
                                type: 'error',
                            });
                            
                        } else if (resp.success) {
                            Swal.fire({
                                timer: 5000,
                                title: 'Успешно!',
                                text: resp.success,
                                type: 'success',
                            });
                            if (!sent) {
                                $('[for="'+$this.attr('id')+'"]').removeClass('text-success').addClass('text-warning').text('Не отправлено');
                            } else {
                                $('[for="'+$this.attr('id')+'"]').removeClass('text-warning').addClass('text-success').text('Отправлено');                                
                            }
                        }
                    }
                })

            })
        })
    </script>
{/capture}

{capture name='page_styles'}
    <link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid.min.css" />
    <link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid-theme.min.css" />
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
                <h3 class="text-themecolor mb-0 mt-0">Отчеты НБКИ</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Отчеты НБКИ</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
                
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Отчеты НБКИ</h4>
                        <div id="basicgrid" class="jsgrid" style="position: relative; width: 100%;">
                            <div class="jsgrid-grid-body">
                                <table class="table">
                                    <tr>
                                        <th class="text-left">Создано</th>
                                        <th class="text-left">Название</th>
                                        <th class="text-left">Файл</th>
                                        <th>Отправлен</th>
                                        <th></th>
                                    </tr>
                                    {foreach $reports as $report}
                                    <tr>
                                        <td class="text-left">
                                            <small>{$report->created|date} {$report->created|time}</small>
                                        </td>
                                        <td class="text-left">
                                            <strong>{$report->name|escape}</strong>
                                        </td>
                                        <td class="text-left">
                                            <strong>{$report->filename|escape}</strong>
                                        </td>
                                        <td class="text-left">
                                            <div class="custom-control custom-checkbox ">
                                                <input type="checkbox" class="custom-control-input js-sent-checkbox" data-report="{$report->id}" id="sent_{$report->id}" value="1" {if $report->sent}checked="checked"{/if} />
                                                <label class="custom-control-label {if $report->sent}text-success{else}text-warning{/if}" for="sent_{$report->id}">
                                                    {if $report->sent}Отправлен
                                                    {else}Не отправлен{/if}
                                                </label>
                                            </div>
                                        </td>
                                        <td class="text-right">
                                            <a class="btn btn-sm btn-info" download target="_blank" href="files/nbki/{$report->filename}">
                                                <i class="fas fa-download"></i>
                                                <span> Скачать</span>
                                            </a>
                                        </td>
                                    </tr>
                                    {/foreach}
                                </table>
                            </div>
                        </div>
                        {include file='html_blocks/pagination.tpl' items=$reports}
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