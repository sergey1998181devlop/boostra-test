{$meta_title='Ссылки партнеров' scope=parent}
{capture name='page_styles'}{/capture}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-file-chart"></i>
                    <span>Ссылки партнеров в ЛК отказников</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Ссылки партнеров</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <ul class="mt-2 nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#partner" role="tab" aria-selected="true">
                            Партнерские ссылки
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#click_hunter" role="tab" aria-selected="true">
                            Click Hunter
                        </a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div id="partner" class="tab-pane active" role="tabpanel">
                        <!-- Column -->
                        <div class="card">
                            <div class="card-body">
                                <div id="result">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th>id</th>
                                            <th>наименование</th>
                                            <th>url</th>
                                            <th>дата добавления</th>
                                            <th>действие</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        {if $items}
                                            {foreach $items as $key => $item}
                                                <tr>
                                                    <td>{$key}</td>
                                                    <td>{$item->id}</td>
                                                    <td>{$item->name}</td>
                                                    <td>{$item->href}</td>
                                                    <td>{$item->date_added}</td>
                                                    <td>
                                                        <div class="float-right js-filter-status">
                                                            <button onclick="deleteItem({$item->id})"
                                                                    class="btn btn-xs btn-outline-danger">
                                                                <i class="fa fa-trash" aria-hidden="true"></i>
                                                            </button>
                                                            <button onclick="updateItem({$item->id})"
                                                                    class="btn btn-xs btn-outline-warning">
                                                                <i class="fa fa-edit" aria-hidden="true"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            {/foreach}
                                        {/if}
                                        </tbody>
                                        <tfoot>
                                        <tr>
                                            <td colspan="4"/>
                                            <td>
                                                <div class="float-right js-filter-status">
                                                    <button onclick="addNewItem()" class="btn btn-xs btn-outline-success"><i
                                                                class="fa fa-plus mr-2" aria-hidden="true"></i> Добавить ссылку
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <strong class=""></strong>
                            </div>
                        </div>
                    </div>
                    <div id="click_hunter" class="tab-pane" role="tabpanel">
                        <form method="post" action="/partner_href?action=click_hunter">
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="click_hunter[utm_source]" class="control-label">Имя партнера</label>
                                        <input required type="text" id="click_hunter[partner_name]" name="click_hunter[partner_name]" class="form-control" value="{$click_hunter['partner_name']}" />
                                    </div>
                                    <div class="form-group">
                                        <label for="click_hunter[url]" class="control-label">url</label>
                                        <input required type="text" id="click_hunter[url]" name="click_hunter[url]" class="form-control" value="{$click_hunter['url']}" />
                                    </div>
                                    <div class="form-group">
                                        <div class="onoffswitch">
                                            <input type="checkbox" name="click_hunter[status]" class="onoffswitch-checkbox" value="1" id="click_hunter[status]" {if $click_hunter['status']}checked{/if} />
                                            <label class="onoffswitch-label" for="click_hunter[status]">
                                                <span class="onoffswitch-inner"></span>
                                                <span class="onoffswitch-switch"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">Сохранить</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {include file='footer.tpl'}
</div>

<div id="modal_edit" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog"
     aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Изменение, добавление ссылки</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="partner_href" action="/">
                    <input type="hidden" name="id" value=""/>

                    <div class="form-group">
                        <label for="name" class="control-label text-white">Url</label>
                        <input type="text" required name="href" class="form-control js-perspective" value=""/>
                    </div>
                    <div class="form-group">
                        <label for="name" class="control-label text-white">Название</label>
                        <input type="text" required name="name" class="form-control js-perspective" value=""/>
                    </div>
                    <div class="form-action">
                        <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success waves-effect waves-light">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{capture name='page_scripts'}
    <script>
        function addNewItem() {
            $('#partner_href').attr('method', 'POST');
            document.getElementById('partner_href').reset();
            $('#partner_href [name="id"]').val('');

            $('#modal_edit').modal('show');
        }

        function deleteItem(id) {
            if (confirm('Вы точно желаете удалить ссылку с id: ' + id + ' ?')) {
                $.ajax({
                    url: "/partner_href?ajax=1&id=" + id,
                    method: 'DELETE',
                    dataType: 'json',
                    success: function (json) {
                        if (json['result']) {
                            reloadItems();
                        }
                    }
                });
            }
        }

        function updateItem(id) {
            $.ajax({
                url: "/partner_href?ajax=1&id=" + id,
                method: 'GET',
                dataType: 'json',
                success: function (json) {
                    if (json['result']) {
                        $('#partner_href [name="href"]').val(json['result']['href']);
                        $('#partner_href [name="name"]').val(json['result']['name']);
                        $('#partner_href [name="id"]').val(id);

                        $('#partner_href').attr('method', 'PUT');

                        $('#modal_edit').modal('show');
                    }
                }
            });
        }

        function reloadItems() {
            $('#modal_edit').modal('hide');
            $('#result').empty().load('/partner_href #result table');
        }

        $(document).on('submit', '#partner_href', function (e) {
            e.preventDefault();
            let url = "/partner_href?ajax=1";

            if ($(this).attr('method') === 'PUT') {
                let id = $('#partner_href [name="id"]').val();
                url += '&id=' + id;
            }

            $.ajax({
                url: url,
                data: $(this).serialize(),
                method: $(this).attr('method'),
                dataType: 'json',
                success: function (json) {
                    if (json['result']) {
                        reloadItems();
                    }
                }
            });
        });
    </script>
{/capture}
