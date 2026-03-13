{$meta_title='Стоп-лист web-id' scope=parent}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-file-chart"></i>
                    <span>Стоп-лист web-id</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Стоп-лист web-id</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            Выбрать источник и web id
                        </h4>
                        <form id="report_form">
                            <div class="row">
                                <div class="col-6 col-md-2">
                                    <div class="form-group">
                                        <label for="utm_source">Источник</label>
                                        <select class="form-control" id="utm_source" name="utm_source">
                                            {foreach $sources as $source}
                                                <option value="{$source}">{$source}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6 col-md-2">
                                    <div class="form-group">
                                        <label for="web-id">Webmaster id</label>
                                        <div class="btn-group btn-block dropdown-click-over">
                                            <button class="btn btn-block btn-secondary dropdown-toggle" id="dropdown-button-webmaster_id" type="button" data-toggle="dropdown" aria-haspopup="false" aria-expanded="true" data-flip="false">
                                                Выбор web-id
                                            </button>
                                            <div class="dropdown-menu p-2" id="dropdown-webmaster_id"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-auto">
                                    <div class="form-group">
                                        <label for="web-id">Добавить в стоп-лист</label>
                                        <div class="btn-group btn-block">
                                            <button onclick="addItems();" type="button" class="btn btn-primary"><i class="ti-save"></i> Внести в стоп-лист</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div id="result" class="table-responsive">
                            <table class="table table-bordered table-hover">
                                   <colgroup>
                                       <col />
                                   </colgroup>
                                    <thead>
                                        <tr class="small">
                                            <th>Источник</th>
                                            <th>Web-id в стоп листе</th>
                                            <th>Удалить из стоп-листа</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {foreach $results as $result}
                                            <tr id="item_{$result->id}">
                                                <td>{$result->utm_source}</td>
                                                <td>{$result->web_master_id}</td>
                                                <td>
                                                    <button data-toggle="tooltip" title="Удалить" class="btn btn-outline-danger" onclick="deleteItem({$result->id})">
                                                        <i class="ti-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        {/foreach}
                                    </tbody>
                            </table>
                        </div>
                        <strong class=""></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {include file='footer.tpl'}
</div>

{capture name='page_scripts'}
    <script type="text/template" id="tr-template">
        <tr id="item_%id%">
            <td>%utm_source%</td>
            <td>%web_master_id%</td>
            <td>
                <button data-toggle="tooltip" title="Удалить" class="btn btn-outline-danger" onclick="deleteItem('%id%')">
                    <i class="ti-trash"></i>
                </button>
            </td>
        </tr>
    </script>

    <script>

        // подгрузка webmaster_id
        $('#utm_source').on('change', function () {
            let utm_source = $(this).val();
            $("#report_form").addClass('data-loading');
            $("#dropdown-webmaster_id").load('{$smarty.server.REQUEST_URI}?ajax=1&action=getWebmasterIds&utm_source=' + utm_source, function () {
                $("#report_form").removeClass('data-loading');
            });
        });

        function addItems() {
            $.ajax({
                url: '{$smarty.server.REQUEST_URI}?action=addItems',
                data: $("#report_form").serialize(),
                method: 'POST',
                dataType: 'json',
                beforeSend: function () {
                    $(".page-wrapper").addClass('data-loading');
                },
                success: function (json) {
                    for (const val of json['results']) {
                        let compiled = $('#tr-template').html();
                        compiled = compiled
                            .split('%id%').join(val.id)
                            .split('%utm_source%').join(val.utm_source)
                            .split('%web_master_id%').join(val.web_master_id);

                        $("#result tbody").append(compiled);
                    }
                }
            }).done(function () {
                $(".page-wrapper").removeClass('data-loading');
            });
        }

        function deleteItem(id) {
            $.ajax({
                url: '{$smarty.server.REQUEST_URI}?action=deleteItem',
                data: {
                    id: id
                },
                method: 'POST',
                dataType: 'json',
                beforeSend: function () {
                    $(".page-wrapper").addClass('data-loading');
                },
                success: function () {
                    $("#item_" + id).remove();
                }
            }).done(function () {
                $(".page-wrapper").removeClass('data-loading');
            });
        }
    </script>
{/capture}
