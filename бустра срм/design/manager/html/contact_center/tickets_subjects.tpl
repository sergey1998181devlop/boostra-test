{$meta_title = 'Темы жалоб' scope=parent}

{capture name='page_styles'}
    <link href="design/manager/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="design/manager/assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.css">
    <link rel="stylesheet" type="text/css" href="design/manager/assets/plugins/datatables.net-bs4/css/responsive.dataTables.min.css">
    <!-- Color picker plugins css -->
    <link href="design/manager/assets/plugins/jquery-asColorPicker-master/dist/css/asColorPicker.css" rel="stylesheet">
    <style>
        #config-table td {
            border-right: 1px solid black;
        }

        #config-table textarea:disabled {
            background: #383f48;
            color: white;
            opacity: .7;
            border: 0;
            padding-top: 5px;
            padding-left: 5px;
            resize: vertical;
            width: 100%;
        }

        input.form-control {
            margin-bottom: 1rem;
        }

        input.form-control:disabled {
            border: 0;
        }

        a.text-info.js-edit-item,
        a.text-danger.js-cancel-edit-item,
        a.text-success.js-confirm-edit-item,
        a.text-danger.js-delete-item {
            width: 40px;
            height: 40px;
            font-size: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        td.text-right {
            vertical-align: inherit;
        }

        .js-visible-view, .js-visible-edit {
            display: flex;
            flex-direction: row;
            justify-content: center;
            width: 100%;
        }

        #config-table textarea {
            background: #272c33;
            color: white;
            padding-top: 5px;
            padding-left: 5px;
            resize: vertical;
            width: 100%;
        }

        .hide-block-alert {
            height: 0px;
            width: 0%;
            opacity: 0;
            transition: .5s all ease-out;
        }

        .hide-block-alert.visible-cont {
            width: 100%;
            height: 20px;
            opacity: 1;
            transition: .5s all ease-in;
            color: white;
        }

        .block_info {
            display: flex;
            flex-direction: column;
        }

        .modal-body .form-group {
            display: flex;
            flex-direction: column;
        }

        .modal-body textarea {
            background: #272c33;
            color: white;
            resize: vertical;
            height: 60px;
        }

        .table-striped tbody tr:nth-of-type(even) td .form-control {
            background-color: #272c33 !important;
        }

        /* loader */
        .lds-roller-main, .lds-roller-main-table {
            position: absolute;
            height: 100%;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 1;
            background: #00000094;
            z-index: 10;
        }

        .lds-ellipsis {
            display: inline-block;
            position: relative;
            width: 80px;
            height: 80px;
        }

        .lds-ellipsis div {
            position: absolute;
            top: 33px;
            width: 13px;
            height: 13px;
            border-radius: 50%;
            background: #508cc5;
            animation-timing-function: cubic-bezier(0, 1, 1, 0);
        }

        .lds-ellipsis div:nth-child(1) {
            left: 8px;
            animation: lds-ellipsis1 0.6s infinite;
        }

        .lds-ellipsis div:nth-child(2) {
            left: 8px;
            animation: lds-ellipsis2 0.6s infinite;
        }

        .lds-ellipsis div:nth-child(3) {
            left: 32px;
            animation: lds-ellipsis2 0.6s infinite;
        }

        .lds-ellipsis div:nth-child(4) {
            left: 56px;
            animation: lds-ellipsis3 0.6s infinite;
        }

        @keyframes lds-ellipsis1 {
            0% {
                transform: scale(0);
            }
            100% {
                transform: scale(1);
            }
        }

        @keyframes lds-ellipsis3 {
            0% {
                transform: scale(1);
            }
            100% {
                transform: scale(0);
            }
        }

        @keyframes lds-ellipsis2 {
            0% {
                transform: translate(0, 0);
            }
            100% {
                transform: translate(24px, 0);
            }
        }


    </style>
{/capture}

{capture name='page_scripts'}
    <script src="design/manager/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
    <script src="design/manager/assets/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="design/manager/assets/plugins/datatables.net-bs4/js/dataTables.responsive.min.js"></script>
    <!-- Plugin JavaScript -->
    <script src="design/manager/assets/plugins/moment/moment.js?v=1.1"></script>
    <script src="design/manager/assets/plugins/jquery-asColor/dist/jquery-asColor.js?v=1.1"></script>
    <script src="design/manager/assets/plugins/jquery-asColorPicker-master/dist/jquery-asColorPicker.min.js?v=1.1"></script>
    <script>
        function TicketReasonsApp() {
            var app = this;

            // массив с дефолтом
            var histArrays = [];

            var num = 0;
            var _init_events_rez = function () {
                var numStart = 5988;

                // доб новой записи
                $(document).on('click', '.add_field_list', function (e) {
                    e.preventDefault();

                    let subject_id = $(this).data("subject_id");

                    numStart = numStart + 1;

                    let new_row = ' <tr class="js-item-res" id="res_' + numStart + '">';

                    new_row += '<td><div class="js-text-id"> - </div></td>';

                    new_row += '<td>';
                    new_row += '<input type="text" class="form-control" data-res_id="' + numStart + '" value="" name="name"/>';
                    new_row += '</td>';

                    new_row += '<td class="text-right"><div class="js-visible-edit">';

                    new_row += '<a href="#" class="text-success js-confirm-edit-item-res" data-subject_id="' + subject_id + '" data-empty-list="1" data-res_id="' + numStart + '" title="Сохранить"><i class="fas fa-check-circle"></i></a>';
                    new_row += '<a href="#" class="text-danger js-cancel-edit-item-res" data-empty-list="1" data-res_id="' + numStart + '" title="Удалить"><i class="fas fa-times-circle"></i></a>';
                    new_row += '</div> </td>';

                    new_row += '</tr>';

                    $('#table-body-res').append(new_row);
                });

                //сохранение записи
                $(document).on('click', '.js-confirm-edit-item-res', function (e) {
                    e.preventDefault();

                    var $item = $(this).closest('.js-item-res');

                    var _id = $(this).data('res_id');
                    var subject_id = $('.add_field_list').data('subject_id');

                    $('.lds-roller-main-table').show();
                    var _name = $item.find('[name=name]').val();


                    var data = $(this).data('empty-list') == '1'
                        ? {
                            new: true,
                            name: _name,
                            subject_id: subject_id,
                            action: 'result_save',
                        }
                        : {
                            new: false,
                            name: _name,
                            id: _id,
                            action: 'result_save',
                        }
                    ;

                    setTimeout(() => {
                        $.ajax({
                            type: 'POST',
                            url: '/ajax/ticket_actions.php',
                            data: data,
                            success: function (resp) {


                                if (!resp.success) {
                                    Swal.fire({
                                        text: 'Произошла ошибка!',
                                        type: 'error',
                                    });
                                }

                                if (data.new === true) {
                                    $item.find('.js-confirm-edit-item-res').data('res_id', resp.id);
                                    $item.find('.js-confirm-edit-item-res').data('empty-list', '0');
                                    $item.find('.js-cancel-edit-item-res').data('res_id', resp.id);
                                    $item.find('.js-cancel-edit-item-res').data('empty-list', '0');
                                    $item.find('.js-text-id').text(resp.id);
                                    $item.find('.js-item-res').attr("id", 'res_' + resp.id);
                                }


                                Swal.fire({
                                    timer: 5000,
                                    text: 'Запись успешно сохранена!',
                                    type: 'success',
                                });
                            }
                        });

                    }, 600);

                    $('.lds-roller-main-table').hide();

                });

            };


            var _init_events = function () {

                // редактирование записи
                $(document).on('click', '.js-edit-item', function (e) {
                        e.preventDefault();

                        let carfId = $(this).data('card_id');

                        if (typeof histArrays[carfId] === "undefined") {
                            histArrays[carfId] = [];

                            $('*[data-card-id="' + carfId + '"]').each(function (index) {
                                setTimeout(() => {
                                    histArrays[carfId][index] = $(this).serializeArray();
                                }, 15);
                            });
                        }

                        $('*[data-card-id="' + carfId + '"]').each(function (index) {
                            $(this).removeAttr('disabled');
                        });

                        var $item = $(this).closest('.js-item');

                        $item.find('.js-visible-view').hide();
                        $item.find('.js-visible-edit').fadeIn();
                    }
                );

                // Удаление записи
                $(document).on('click', '.js-delete-item', function (e) {
                    e.preventDefault();

                    var $item = $(this).closest('.js-item');

                    var _id = $(this).data('card_id');
                    var _name = $item.find('[name=name]').val();

                    Swal.fire({
                        html: "Вы действительно хотите удалить тему жалобы <b>" + _name + " </b>?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Да, удалить!",
                        cancelButtonText: "Отмена",
                        showLoaderOnConfirm: true,
                        preConfirm: () => {
                        },
                        allowOutsideClick: () => !Swal.isLoading()

                    }).then((result) => {

                        if (result.value) {
                            $('.lds-roller-main-table').show();

                            $("tr#card_" + _id).remove();

                            $.ajax({
                                type: 'POST',
                                data: {
                                    action: 'delete',
                                    id: _id
                                },
                                success: function () {
                                    $('.lds-roller-main-table').hide();

                                    Swal.fire({
                                        timer: 5000,
                                        text: 'Тема жалобы удалена!',
                                        type: 'success',
                                    });
                                }
                            })
                        }
                    });
                });

                var dataItem = [];

                // Сохранение редактируемой записи
                $(document).on('click', '.js-confirm-edit-item', function (e) {
                    e.preventDefault();
                
                    var $item = $(this).closest('.js-item');
                    var _id = $(this).data('card_id');
                
                    $('.lds-roller-main-table').show();
                
                    var dataItem = {};
                
                    // Собираем данные из всех input/select с соответствующим data-card-id
                    $('*[data-card-id="' + _id + '"]').each(function () {
                        var name = $(this).attr('name');
                        var value = $(this).val();
                        dataItem[name] = value;
                    });
                
                    // Логирование собранных данных
                    console.log("Отправляемые данные:", {
                        action: 'update',
                        id: _id,
                        data: dataItem
                    });
                
                    // Отправляем запрос с собранными данными
                    $.ajax({
                        type: 'POST',
                        data: {
                            action: 'update',
                            id: _id,
                            data: dataItem
                        },
                        success: function (resp) {
                            if (resp.error) {
                                Swal.fire({
                                    text: resp.error,
                                    type: 'error',
                                });
                            } else {
                                location.reload();
                            }
                            $('.lds-roller-main-table').hide();
                        },
                        error: function (xhr, status, error) {
                            console.error("Ошибка запроса:", status, error); // Логируем ошибку запроса
                            Swal.fire({
                                text: 'Произошла ошибка при отправке данных на сервер.',
                                type: 'error',
                            });
                            $('.lds-roller-main-table').hide();
                        }
                    });
                });

                // Отмена редактирования записи
                $(document).on('click', '.js-cancel-edit-item', function (e) {
                    e.preventDefault();
                    let carfId = $(this).data('card_id');

                    $('*[data-card-id="' + carfId + '"]').each(function (index) {
                        $(this).prop('disabled', true);
                    });


                    histArrays[carfId].forEach(function (item, i, arr) {
                        $('#card_' + carfId).find("*[name=" + item[0].name + "]").val(item[0].value);
                    });


                    var $item = $(this).closest('.js-item');

                    $item.find('.js-visible-edit').hide();
                    $item.find('.js-visible-view').fadeIn();

                    num = num + 1;


                    $(".block_info").append("<div id='num_" + num + "' class='hide-block-alert'>Поля для id:" + carfId + " вернулись к заводским! (была нажата отмена)</div>");

                    setTimeout(() => {
                        $("#num_" + num).addClass("visible-cont");
                    }, 1000)

                    setTimeout(() => {
                        $("#num_" + num).removeClass("visible-cont");
                    }, 5000)

                });

                // Открытие окна для добавления
                $(document).on('click', '.js-open-add-modal', function (e) {
                    e.preventDefault();

                    $('#modal_add_item').find('.alert').hide();
                    $('#modal_add_item').find('[name=name]').val('');

                    $('#modal_add_item').modal();

                    $('#modal_add_item').find('[name=name]').focus();

                    _init_colorpicker();
                });

                // Сохранение новой записи
                $(document).on('submit', '#form_add_item', function (e) {
                    e.preventDefault();

                    var $form = $(this);

                    var dataForm = $(this).serialize();

                    $('.lds-roller-main').show();

                    $.ajax({
                        type: 'POST',
                        data: {
                            action: 'add',
                            data: dataForm,
                        },
                        beforeSend: function () {

                        },
                        success: function (resp) {
                            if (resp.success) {
                                var new_row = ' <tr class="js-item" id="card_' + resp.id + '">';

                                new_row += '<td><div class="js-text-id">' + resp.id + '</div></td>';

                                new_row += '<td>';
                                new_row += '<input type="text" class="form-control" data-card-id="' + resp.id + '" value="' + resp.data.name + '" name="name" disabled/>';
                                new_row += '</td>';

                                new_row += '<td class="text-right"> <div class="js-visible-view">';
                                new_row += '<a href="#" class="text-info js-edit-item" data-card_id="' + resp.id + '" title="Редактировать"><i class=" fas fa-edit"></i></a>';
                                new_row += '<a href="#" class="text-danger js-delete-item" data-card_id="' + resp.id + '" title="Удалить"><i class="far fa-trash-alt"></i></a>';
                                new_row += '</div> <div class="js-visible-edit" style="display:none">';

                                new_row += '<a href="#" class="text-success js-confirm-edit-item" data-card_id="' + resp.id + '" title="Сохранить"><i class="fas fa-check-circle"></i></a>';
                                new_row += '<a href="#" class="text-danger js-cancel-edit-item" data-card_id="' + resp.id + '" title="Отменить"><i class="fas fa-times-circle"></i></a>';
                                new_row += '</div> </td>';


                                $('#table-body').append(new_row);
                                $('.lds-roller-main').hide();

                                $('#modal_add_item').modal('hide');
                                Swal.fire({
                                    timer: 5000,
                                    text: 'Тема "' + resp.data.name + '" добавлен!',
                                    type: 'success',
                                });
                            } else {
                                Swal.fire({
                                    text: resp.message,
                                    type: 'error',
                                });

                                $('.lds-roller-main').hide();
                            }


                        }

                    })
                });
            };

            ;(function () {
                _init_events();
                _init_events_rez();
            })();
        };
        $(function () {
            new TicketReasonsApp();
        })

    </script>
{/capture}

<div class="page-wrapper">

    <div class="lds-roller-main-table" style="display: none">
        <div class="lds-ellipsis">
            <div></div>
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>
    <!-- ============================================================== -->
    <!-- Container fluid  -->
    <!-- ============================================================== -->
    <div class="container-fluid">
        <!-- ============================================================== -->
        <!-- Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">Темы жалоб</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Главная</a></li>
                    <li class="breadcrumb-item active">Темы жалоб</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
                <button class="btn float-right hidden-sm-down btn-success js-open-add-modal">
                    <i class="mdi mdi-plus-circle"></i> Добавить
                </button>

            </div>
        </div>

        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Список тем жалоб</h4>

                        <div class="block_info"></div>

                        <div class="table-responsive">
                            <div class="dataTables_wrapper container-fluid dt-bootstrap4 no-footer">
                                <table id="config-table" class="table display table-striped dataTable">
                                    <thead>
                                    <tr>
                                        <th class="">ID</th>
                                        <th class="">Название</th>
                                        <th class="">Родительская тема</th>
                                        <th class="">Идентификатор цели</th>
                                        <th class="">Идентификатор в 1С</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody id="table-body">

                                    {foreach $subjects as $subject}
                                        <tr class="js-item" id="card_{$subject->id}">
                                            <td>
                                                <div class="js-text-id">
                                                    {$subject->id}
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" data-card-id="{$subject->id}" value="{$subject->name}" name="name" disabled/>
                                            </td>
                                            <td>
                                                <div class="js-visible-view">
                                                    <span class="js-text-parent-name">
                                                        {if $subject->parent_name}
                                                            {$subject->parent_name}
                                                        {else}
                                                            Нет родительской темы
                                                        {/if}
                                                    </span>
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <select class="form-control" name="parent_id" data-card-id="{$subject->id}">
                                                        <option value="">-- Нет родительской темы --</option>
                                                        {foreach $subjects as $parent}
                                                            <option value="{$parent->id}" {if $subject->parent_id === $parent->id}selected{/if}>
                                                                {$parent->name}
                                                            </option>
                                                        {/foreach}
                                                    </select>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="js-visible-view">
                                                    <span class="js-text-parent-name">
                                                        {if $subject->yandex_goal_id}
                                                            {$subject->yandex_goal_id}
                                                        {else}
                                                            Нет идентификатора цели
                                                        {/if}
                                                    </span>
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <input type="text" class="form-control" data-card-id="{$subject->id}" value="{$subject->yandex_goal_id}" name="yandex_goal_id"/>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="js-visible-view">
                                                    <span class="">
                                                        {if $subject->uid}
                                                            {$subject->uid}
                                                        {else}
                                                            Нет идентификатора 1С
                                                        {/if}
                                                    </span>
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <input type="text" class="form-control" data-card-id="{$subject->id}" value="{$subject->uid}" name="uid"/>
                                                </div>
                                            </td>
                                            <td class="text-right d-flex">
                                                <div class="js-visible-view">
                                                    <a href="#" class="text-info js-edit-item" data-card_id="{$subject->id}" title="Редактировать"><i class=" fas fa-edit"></i></a>
                                                    <a href="#" class="text-danger js-delete-item" data-card_id="{$subject->id}" title="Удалить"><i class="far fa-trash-alt"></i></a>
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <a href="#" class="text-success js-confirm-edit-item" data-card_id="{$subject->id}" title="Сохранить"><i class="fas fa-check-circle"></i></a>
                                                    <a href="#" class="text-danger js-cancel-edit-item" data-card_id="{$subject->id}" title="Отменить"><i class="fas fa-times-circle"></i></a>
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

<div id="modal_add_item" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="lds-roller-main" style="display: none">
                <div class="lds-ellipsis">
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
            </div>


            <div class="modal-header">
                <h4 class="modal-title text-center">Добавление новой темы жалобы</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_add_item">

                    <div class="alert" style="display:none"></div>

                    <div class="form-group">
                        <label for="name" class="control-label">Название:</label>
                        <input type="text" class="form-control" name="name" id="name" value="" required/>
                    </div>

                    <div class="form-group">
                        <label for="parent_id" class="control-label">Родительская тема:</label>
                        <select class="form-control" name="parent_id" id="parent_id">
                            <option value="">-- Нет родительской темы --</option>
                            {foreach $subjects as $subject}
                                <option value="{$subject->id}">{$subject->name}</option>
                            {/foreach}
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="yandex_goal_id" class="control-label">Идентификатор цели:</label>
                        <input type="text" class="form-control" name="yandex_goal_id" id="yandex_goal_id" value=""/>
                    </div>
                    
                    <div class="form-group">
                        <label for="uid" class="control-label">Идентификатор в 1С:</label>
                        <input type="text" class="form-control" name="uid" id="uid" value=""/>
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

<div id="edit_list_rez" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="lds-roller-main" style="display: none">
                <div class="lds-ellipsis">
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
            </div>


            <div class="modal-header">
                <h4 class="modal-title text-center"><span id="list_name_res"></span> - изменение решений</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">


                <div class="alert" style="display:none"></div>

                <table id="config-table" class="table display table-striped dataTable">
                    <thead>
                    <tr>
                        <th class="">ID</th>
                        <th class="">Название</th>
                        <th class="">Действие</th>
                    </tr>
                    </thead>
                    <tbody id="table-body-res">

                    </tbody>
                </table>

                <button class="btn float-right hidden-sm-down btn-info mt-2 add_field_list" data-subject_id="0">
                    <i class="mdi mdi-plus"></i> добавить поле
                </button>


            </div>
        </div>
    </div>
</div>