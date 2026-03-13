{$meta_title = 'Организации' scope=parent}

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

        #config-table span {
            color: #007bff;
            margin-right: 10px;
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
            flex-direction: column;
            align-items: center;
            justify-content: center;
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


        .js-text-admin-name,
        .js-text-client-name {
        }

        .color-badge {
            display: inline-block;
            width: 64px;
            height: 24px;
            margin-right: 20px;
            vertical-align: top;
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
                                    console.log('1');
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
                    var _name = $item.find('[name=short_name]').val();

                    Swal.fire({
                        html: "Вы действительно хотите удалить организацию <b>" + _name + " </b>?",
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
                                        text: 'Организация удалена!',
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

                    $('*[data-card-id="' + _id + '"]').each(function (index) {
                        setTimeout(() => {
                            dataItem[index] = $(this).serializeArray();
                            histArrays[_id][index] = $(this).serializeArray();
                        }, 50);
                    });

                    setTimeout(() => {
                        $.ajax({
                            type: 'POST',
                            data: {
                                action: 'update',
                                id: _id,
                                data: dataItem,
                            },
                            success: function (resp) {
                                if (!!resp.error) {
                                    Swal.fire({
                                        text: resp.error,
                                        type: 'error',
                                    });

                                    $('.lds-roller-main-table').hide();
                                } else {
                                    $item.find('[name=name]').val(resp.name);
                                    $item.find('.js-text-name').html(resp.name);

                                    $item.find('.js-visible-edit').hide();
                                    $item.find('.js-visible-view').fadeIn();

                                    $('*[data-card-id="' + _id + '"]').each(function (index) {
                                        $(this).prop('disabled', true);
                                    });
                                }

                                $('.lds-roller-main-table').hide();
                            }
                        });

                    }, 600);

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
                            if (!!resp.error) {
                                $form.find('.alert').removeClass('alert-success').addClass('alert-danger').html(resp.error).fadeIn();
                                $('.lds-roller-main').hide();
                            } else {

                                var new_row = ' <tr class="js-item" id="card_' + resp.id + '">';

                                new_row += '<td><div class="js-text-id">' + resp.id + '</div></td>';

                                new_row += '<td>';
                                new_row += '<input type="text" class="form-control" data-card-id="' + resp.id + '" value="' + resp.data.short_name + '" name="short_name" disabled/>';
                                new_row += '<div class="d-flex align-items-baseline"> <span>Расш название:</span> </div>';
                                new_row += '<textarea style="height: auto;" data-card-id="' + resp.id + '" name="name" disabled>' + resp.data.name + '</textarea>';
                                new_row += '</td>';

                                new_row += '<td> <div class="d-flex align-items-baseline"> <span>Email:</span>';
                                new_row += '<input type="text" class="form-control" data-card-id="' + resp.id + '" value="' + resp.data.email + '" name="email" disabled/>';
                                new_row += '</div> <div class="d-flex align-items-baseline"> <span>Сайт:</span>';
                                new_row += '<input type="text" class="form-control" data-card-id="' + resp.id + '" value="' + resp.data.site + '" name="site" disabled/>';
                                new_row += '</div> </td>';

                                new_row += '<td> <div class="d-flex align-items-baseline"> <span>Номер1:</span>';
                                new_row += '<input type="text" class="form-control" data-card-id="' + resp.id + '" value="' + resp.data.phone + '" name="phone" disabled/>';
                                new_row += '</div> <div class="d-flex align-items-baseline"> <span>Номер2:</span>';
                                new_row += '<input type="text" class="form-control" data-card-id="' + resp.id + '" value="' + resp.data.phone2 + '" name="phone2" disabled/>';
                                new_row += '</div> </td>';

                                new_row += '<td> <div class="d-flex align-items-baseline"> <span>ИНН:</span>';
                                new_row += '<input type="text" class="form-control" data-card-id="' + resp.id + '" value="' + resp.data.inn + '" name="inn" disabled/>';
                                new_row += '</div> <div class="d-flex align-items-baseline"> <span>КПП:</span>';
                                new_row += '<input type="text" class="form-control" data-card-id="' + resp.id + '" value="' + resp.data.kpp + '" name="kpp" disabled/>';
                                new_row += '</div> </td>';

                                new_row += '<td>';
                                new_row += '<input type="text" class="form-control" data-card-id="' + resp.id + '" value="' + resp.data.bank + '" name="bank" disabled/>';
                                new_row += '</td>';

                                new_row += '<td>';
                                new_row += '<textarea style="height: auto;" data-card-id="' + resp.id + '" name="address" disabled>' + resp.data.address + '</textarea>';
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
                                    text: 'Причина "' + resp.name + '" добавлена!',
                                    type: 'success',
                                });

                            }
                        }
                    })
                });
            };

            ;(function () {
                _init_events();
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
                <h3 class="text-themecolor mb-0 mt-0">Организации</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Главная</a></li>
                    <li class="breadcrumb-item active">Организации</li>
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
                        <h4 class="card-title">Список организаций</h4>

                        <div class="block_info"></div>

                        <div class="table-responsive">
                            <div class="dataTables_wrapper container-fluid dt-bootstrap4 no-footer">
                                <table id="config-table" class="table display table-striped dataTable">
                                    <thead>
                                    <tr>
                                        <th class="">ID</th>
                                        <th class="">Название</th>
                                        <th class="">Email/Сайт</th>
                                        <th class="">Телефоны</th>
                                        <th class="">ИНН/КПП</th>
                                        <th class="">Банк</th>
                                        <th class="">Адрес</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody id="table-body">

                                    {foreach $items as $item}
                                        <tr class="js-item" id="card_{$item->id}">
                                            <td>
                                                <div class="js-text-id">
                                                    {$item->id}
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" data-card-id="{$item->id}" value="{$item->short_name|escape}" name="short_name" disabled/>
                                                <div class="d-flex align-items-baseline">
                                                    <span>Расш название:</span>
                                                </div>
                                                <textarea style="height: auto;" data-card-id="{$item->id}" name="name" disabled>{$item->name|escape}</textarea>

                                            </td>
                                            <td>
                                                <div class="d-flex align-items-baseline">
                                                    <span>Email:</span>
                                                    <input type="text" class="form-control" data-card-id="{$item->id}" value="{$item->email}" name="email" disabled/>
                                                </div>
                                                <div class="d-flex align-items-baseline">
                                                    <span>Сайт:</span>
                                                    <input type="text" class="form-control" data-card-id="{$item->id}" value="{$item->site}" name="site" disabled/>
                                                </div>


                                            </td>
                                            <td>
                                                <div class="d-flex align-items-baseline">
                                                    <span>Номер1:</span>
                                                    <input type="text" class="form-control" data-card-id="{$item->id}" value="{$item->phone}" name="phone" disabled/>
                                                </div>
                                                <div class="d-flex align-items-baseline">
                                                    <span>Номер2:</span>
                                                    <input type="text" class="form-control" data-card-id="{$item->id}" value="{$item->phone2}" name="phone2" disabled/>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-baseline">
                                                    <span>ИНН:</span>
                                                    <input type="text" class="form-control" data-card-id="{$item->id}" value="{$item->inn}" name="inn" disabled/>
                                                </div>
                                                <div class="d-flex align-items-baseline">
                                                    <span>КПП:</span>
                                                    <input type="text" class="form-control" data-card-id="{$item->id}" value="{$item->kpp}" name="kpp" disabled/>
                                                </div>

                                            </td>
                                            <td>
                                                <input type="text" class="form-control" data-card-id="{$item->id}" value="{$item->bank}" name="bank" disabled/>
                                            </td>
                                            <td>
                                                <textarea style="height: auto;" data-card-id="{$item->id}" name="address" disabled>{$item->address}</textarea>
                                            </td>
                                            <td class="text-right">
                                                <div class="js-visible-view">
                                                    <a href="#" class="text-info js-edit-item" data-card_id="{$item->id}" title="Редактировать"><i class=" fas fa-edit"></i></a>
                                                    <a href="#" class="text-danger js-delete-item" data-card_id="{$item->id}" title="Удалить"><i class="far fa-trash-alt"></i></a>
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <a href="#" class="text-success js-confirm-edit-item" data-card_id="{$item->id}" title="Сохранить"><i class="fas fa-check-circle"></i></a>
                                                    <a href="#" class="text-danger js-cancel-edit-item" data-card_id="{$item->id}" title="Отменить"><i class="fas fa-times-circle"></i></a>
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
                <h4 class="modal-title text-center">Добавление новой организации</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_add_item">

                    <div class="alert" style="display:none"></div>

                    <div class="form-group">
                        <label for="name" class="control-label">Название:</label>
                        <input type="text" class="form-control" name="short_name" value="" required/>
                    </div>
                    <div class="form-group">
                        <label for="name" class="control-label">Расш название:</label>
                        <textarea name="name" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="name" class="control-label">Email:</label>
                        <input type="text" class="form-control" name="email" value=""/>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">

                            <div class="form-group">
                                <label for="name" class="control-label">Телефон1:</label>
                                <input type="text" class="form-control" name="phone" value=""/>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="name" class="control-label">Телефон2:</label>
                                <input type="text" class="form-control" name="phone2" value=""/>
                            </div>

                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="name" class="control-label">ИНН:</label>
                                <input type="text" class="form-control" name="inn" value=""/>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="name" class="control-label">КПП:</label>
                                <input type="text" class="form-control" name="kpp" id="name" value=""/>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="name" class="control-label">Банк:</label>
                                <input type="text" class="form-control" name="bank" id="name" value=""/>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="name" class="control-label">Сайт:</label>
                                <input type="text" class="form-control" name="site" id="name" value=""/>
                            </div>
                        </div>
                    </div>


                    <div class="form-group">
                        <label for="name" class="control-label">Адрес:</label>
                        <textarea name="address"></textarea>
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