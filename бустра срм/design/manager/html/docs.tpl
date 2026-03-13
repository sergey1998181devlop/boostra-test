{$meta_title = 'Документы' scope=parent}

{capture name='page_styles'}
    <link href="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css"
          rel="stylesheet"/>
    <link rel="stylesheet" type="text/css"
          href="design/{$settings->theme|escape}/assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.css">
    <link rel="stylesheet" type="text/css"
          href="design/{$settings->theme|escape}/assets/plugins/datatables.net-bs4/css/responsive.dataTables.min.css">
    <style>
        .js-item {
            cursor: pointer;
        }

        .js-text-doc-name,
        .js-text-doc-created {
        }
    </style>
{/capture}

{capture name='page_scripts'}
    <script src="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/datatables.net-bs4/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.13.0/Sortable.min.js"></script>
    <script>
        $(document).ready(function () {
            new Sortable(document.getElementById('table-body'), {
                animation: 150,
                swapThreshold: 0.65,
                onEnd: function (evt) {
                    var itemEl = evt.item;
                    console.log(itemEl);

                    // Получить новый порядок элементов
                    var newOrder = Array.from(document.getElementById('table-body').children)
                        .map(function (item) {
                            return item.querySelector('[name=id]').value;
                        });

                    console.log(newOrder)

                    // Создание URLSearchParams
                    var params = new URLSearchParams();
                    params.append('action', 'update_positions');
                    newOrder.forEach((pos, i) => {
                        params.append('positions[' + i + ']', pos);
                    });

                    fetch('/docs/update_positions', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: params
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error("Ошибка HTTP, статус = " + response.status);
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.error) {
                                console.error(data.error);
                            } else {
                                console.log('Позиции документа успешно обновлены');
                            }
                        })
                        .catch(error => {
                            console.log('Произошла ошибка: ' + error.message);
                        });
                },
            });

            $('#form_add_item').on('submit', function (e) {
                e.preventDefault();

                var formData = new FormData(this);

                fetch('/docs/addDoc', {
                    method: 'POST',
                    body: formData,
                })
                    .then(response => response.json())
                    .then(data => {
                        console.log(data);
                        if (data.success) {

                            $('#modal_add_item').modal('hide');

                            $('#form_add_item')[0].reset();

                            location.reload();
                        }
                    })
                    .catch(error => console.error('Error:', error));

                return false;
            });

            const buttons = document.querySelectorAll('[id^="toggleButton"]');

            buttons.forEach(button => {
                button.addEventListener('click', async function () {
                    const id = this.getAttribute('data-id');
                    const newState = this.getAttribute('data-state') === 'show' ? 1 : 0;

                    console.log('id:', id);
                    console.log('newState:', newState);
                    try {
                        const params = new URLSearchParams();
                        params.append('id', id);
                        params.append('newState', newState);

                        const response = await fetch('/docs/updateVisibility', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: params.toString(),
                        });

                        if (!response.ok) {
                            throw new Error('HTTP error! status:' + response.status);
                        }

                        const json = await response.json();
                        if (json.success) {
                            // Если обновление прошло успешно
                            this.innerHTML = newState === 1 ? 'Скрыть' : 'Показать';
                            this.setAttribute('data-state', newState === 1 ? 'hide' : 'show');
                        } else {
                            // Обрабатываем ошибку, возвращенную сервером
                            alert('Ошибка: ' + json.error);
                        }
                    } catch (error) {
                        // Обрабатываем ошибки сети или кода
                        console.error('Ошибка при выполнении fetch запроса: ' + error);
                    }
                });
            });
        })

    </script>
    <script src="design/{$settings->theme|escape}/js/apps/docs.app.js"></script>
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
                    Документы
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Главная</a></li>
                    <li class="breadcrumb-item active">Документы</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
                <button class="btn float-right hidden-sm-down btn-success js-open-add-modal">
                    <i class="mdi mdi-plus-circle"></i> Добавить
                </button>
                <a href="/docs/history">
                    <button class="btn mr-1 float-right hidden-sm-down btn-success">
                        История
                    </button>
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title"></h4>
                        <h6 class="card-subtitle"></h6>
                        <div class="table-responsive m-t-40">
                            <div class="dataTables_wrapper container-fluid dt-bootstrap4 no-footer">
                                <table id="config-table" class="table display table-striped dataTable">
                                    <thead>
                                    <tr>
                                        <th class="">ID</th>
                                        <th class="">Имя документа</th>
                                        <th class="">Описание</th>
                                        <th class="">Отображение документа</th>
                                        <th class="">Дата создания</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody id="table-body">

                                    {foreach $docs as $doc}
{*                                        {$doc|print_r:true}*}
                                        <tr class="js-item">
                                            <td>
                                                <div class="js-text-id">
                                                    {$doc->id}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="js-visible-view js-text-doc-name">
                                                    <a href="{$config->front_url}/files/docs/{$doc->filename}" target="_blank">{$doc->name|escape}</a>
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <input type="hidden" name="id" value="{$doc->id}"/>
                                                    <input type="text" class="form-control form-control-sm" name="name"
                                                           value="{$doc->name|escape}"/>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="js-visible-view js-text-doc-description">
                                                    {$doc->description|escape}
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <input type="text" class="form-control form-control-sm"
                                                           name="description" value="{$doc->description|escape}"/>
                                                </div>
                                            </td>
                                            <td>
                                                <button type="button" id="toggleButton{$doc->id}"
                                                        class="btn btn-primary"
                                                        data-state="{if $doc->in_info}hide{else}show{/if}"
                                                        data-id="{$doc->id}">
                                                    {if $doc->in_info}
                                                        Скрыть
                                                    {else}
                                                        Показать
                                                    {/if}
                                                </button>
                                            </td>
                                            <td>
                                                <div class="js-visible-view js-text-doc-created">
                                                    {$doc->created|escape}
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <input type="text" class="form-control form-control-sm"
                                                           name="created" value="{$doc->created|escape}"/>
                                                </div>
                                            </td>
                                            <td class="text-right">
                                                <div class="js-visible-view">
                                                    <a href="#" class="text-info js-edit-item" title="Редактировать">
                                                        <i class=" fas fa-edit"></i>
                                                    </a>
                                                    <a href="#" class="text-danger js-delete-item" title="Удалить">
                                                        <i class="far fa-trash-alt"></i>
                                                    </a>
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <a href="#" class="text-success js-confirm-edit-item"
                                                       title="Сохранить"><i class="fas fa-check-circle"></i></a>
                                                    <a href="#" class="text-danger js-cancel-edit-item"
                                                       title="Отменить"><i class="fas fa-times-circle"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {include file='footer.tpl'}
</div>
<div id="modal_add_item" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog"
     aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Добавить документ</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_add_item" enctype="multipart/form-data">

                    <div class="alert" style="display:none"></div>
                    <div class="form-group">
                        <label for="name" class="control-label">Название документа:</label>
                        <input type="text" class="form-control" name="name" id="name" required/>
                    </div>
                    <div class="form-group">
                        <label for="description" class="control-label">Описание:</label>
                        <textarea class="form-control" name="description" id="description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="file" class="control-label">Файл:</label>
                        <input type="file" class="form-control" name="file" id="file"/>
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