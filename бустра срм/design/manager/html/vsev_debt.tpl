{capture name='page_scripts'}
    <script src="design/{$settings->theme|escape}/assets/plugins/dropify/dist/js/dropify.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.dropify').dropify({
                messages: {
                    'default': 'Перетащите файл сюда или нажмите',
                    'replace': 'Перетащите или нажмите, чтобы заменить',
                    'remove':  'Удалить',
                    'error':   'Упс, что-то пошло не так.'
                }
            });

            $('.js-show-log').click(function(e) {
                e.preventDefault();
                var log = $(this).data('log');
                $('#log-content').text(log);
                $('#logModal').modal('show');
            });
        });
    </script>
{/capture}

{capture name='page_styles'}
    <link href="design/{$settings->theme|escape}/assets/plugins/dropify/dist/css/dropify.min.css" rel="stylesheet">
    <style>
        .dropify-wrapper {
            -webkit-transition: none !important;
            transition: none !important;
        }
        .dropify-wrapper:hover {
            -webkit-animation: none !important;
            animation: none !important;
            background-image: none !important;
        }
        #logModal .modal-dialog {
            max-width: 800px;
        }
        #log-content {
            background-color: #2b2b2b;
            color: #f8f8f2;
            font-family: Consolas, Monaco, 'Andale Mono', 'Ubuntu Mono', monospace;
            padding: 15px;
            border-radius: 5px;
            max-height: 500px;
            overflow: auto;
            white-space: pre;
        }
    </style>
{/capture}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">Уведомления от ВсеВернем</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Уведомления от ВсеВернем</li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Загрузить файл</h4>
                        <form action="" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <input type="file" name="file" class="dropify" data-allowed-file-extensions="csv" />
                            </div>
                            <button type="submit" class="btn btn-success">Загрузить</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">История загрузок</h4>
                        <form action="" method="get">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <input type="text" name="original_filename" class="form-control" placeholder="Имя файла" value="{$original_filename|escape}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-info">Найти</button>
                                </div>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Имя файла</th>
                                    <th>Статус</th>
                                    <th>
                                        <a href="{url sort='date_desc'}" {if $sort == 'date_desc' || !$sort}class="selected"{/if}>Дата загрузки</a>
                                        <a href="{url sort='date_desc'}" {if $sort == 'date_desc' || !$sort}class="selected"{/if}>&#9660;</a>
                                        <a href="{url sort='date_asc'}" {if $sort == 'date_asc'}class="selected"{/if}>&#9650;</a>
                                    </th>
                                    <th>Лог</th>
                                </tr>
                                </thead>
                                <tbody>
                                {if $tasks|@count > 0}
                                    {foreach $tasks as $task}
                                        {if !empty($task->id) && !empty($task->filename)}
                                            <tr>
                                                <td>{$task->id}</td>
                                                <td>{$task->original_filename}</td>
                                                <td>
                                                    {if $task->status == 'pending'}
                                                        <span class="label label-warning">Ожидает</span>
                                                    {elseif $task->status == 'processing'}
                                                        <span class="label label-info">В обработке</span>
                                                    {elseif $task->status == 'completed'}
                                                        <span class="label label-success">Завершен</span>
                                                    {elseif $task->status == 'error'}
                                                        <span class="label label-danger">Ошибка</span>
                                                    {/if}
                                                </td>
                                                <td>{$task->created_at|date_format:"%d.%m.%Y %H:%M"}</td>
                                                <td>
                                                    {if $task->status == 'pending'}
                                                        <span class="text-muted">Ожидает обработки</span>
                                                    {else}
                                                        <a href="#" class="js-show-log" data-log="{$task->log|escape}">Показать</a>
                                                    {/if}
                                                </td>
                                            </tr>
                                        {/if}
                                    {/foreach}
                                {else}
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">
                                            В данный момент нет загруженных файлов
                                        </td>
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

        <div id="logModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Лог обработки</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <div class="modal-body">
                        <pre id="log-content"></pre>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-info waves-effect" data-dismiss="modal">Закрыть</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
