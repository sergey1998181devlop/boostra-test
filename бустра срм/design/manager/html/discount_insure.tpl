{$meta_title='Скидки на страховку' scope=parent}

{capture name='page_styles'}

    <link href="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="design/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">
{/capture}

<style>
    tr.small td {
        padding: 0.25rem;
    }
    i {
        margin: 0 .5rem;
    }
</style>

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-file-chart"></i>
                    <span>Скидки на страховку</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Скидки на страховку</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            Скидки на страховку
                        </h4>

                        <div class="row" style="margin-bottom: 1rem">
                            <div class="col-12">
                                <button onclick="$('#modal_discount').modal('show')" class="btn btn-success float-right">Добавить акцию<i class="ti-plus"></i></button>
                            </div>
                        </div>

                        <div id="result" class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>id</th>
                                        <th>Период акции</th>
                                        <th>Алгоритм скидок <small class="text-warning">(от большего к меньшему)</small></th>
                                        <th>Статус</th>
                                        <th>Действие</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {if $discounts}
                                        {foreach $discounts as $discount}
                                            <tr data-discount_insure_id="{$discount->id}">
                                                <td>{$discount@iteration}</td>
                                                <td>
                                                    <input type="text" name="date_discount" class="form-control date_discount" value="{$discount->date_start} - {$discount->date_end}">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">
                                                            <span class="ti-calendar"></span>
                                                        </span>
                                                     </div>
                                                </td>
                                                <td>
                                                    <table class="table edit-table table-dark table-sm">
                                                        <tbody>
                                                            {foreach $discount->prices|unserialize as $key => $price}
                                                                <tr>
                                                                    <td class="edit-col">
                                                                        {if $key == 'nk'}
                                                                            Новый клиент
                                                                        {else}
                                                                            <input required class="form-control" name="prices[{$price@iteration}][price]" value="{$price.price}" />
                                                                        {/if}
                                                                    </td>
                                                                    <td class="edit-col">
                                                                        {if $key == 'nk'}
                                                                            <input required class="form-control" name="prices[nk][coefficient]" value="{$price.coefficient}" />
                                                                        {else}
                                                                            <input required class="form-control" name="prices[{$price@iteration}][coefficient]" value="{$price.coefficient}" />
                                                                        {/if}
                                                                    </td>
                                                                    <td>
                                                                        {if $key != 'nk'}
                                                                            <button data-toggle="tooltip" title="Удалить строку" type="button" class="btn delete-operand btn-outline-danger">
                                                                                <i class="ti-trash"></i>
                                                                            </button>
                                                                        {/if}
                                                                    </td>
                                                                </tr>
                                                            {/foreach}
                                                        </tbody>
                                                        <tfoot>
                                                            <tr>
                                                                <td colspan="2">
                                                                    <button type="button" class="float-right btn add-operand btn-success"><i class="ti-plus"></i></button>
                                                                </td>
                                                                <td></td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </td>
                                                <td>
                                                    <div class="checkbox-status">
                                                        <label for="discount_{$discount->id}">
                                                            <input type="checkbox" name="status" value="1" id="discount_{$discount->id}" {if $discount->status} checked {/if}>
                                                            <div></div>
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button data-toggle="tooltip" title="Удалить" class="btn btn-danger" type="button" onclick="deleteDiscount({$discount->id});">
                                                            <i class="ti-trash"></i>
                                                        </button>
                                                        <button data-toggle="tooltip" title="Сохранить" class="btn btn-success" type="button" onclick="updateDiscount({$discount->id});">
                                                            <i class="ti-save-alt"></i>
                                                        </button>
                                                        <label data-toggle="tooltip" title="Загрузить список" class="btn btn-primary">
                                                            <i class="ti-download"></i>
                                                            <input class="d-none" type="file" name="upload" accept="application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" />
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="6" class="text-center">
                                                    <a href="javascript:void(0);" onclick="loadPhonesList({$discount->id})">Просмотреть телефоны <i class="ti-mobile"></i></a>
                                                    <div id="phones-list-{$discount->id}" style="display: none;"></div>
                                                </td>
                                            </tr>
                                        {/foreach}
                                    {else}
                                        <tr>
                                            <td colspan="6" class="text-danger text-center">Нет действующих акций</td>
                                        </tr>
                                    {/if}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {include file='footer.tpl'}
</div>

<div id="modal_discount" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Добавить акцию <small class="text-warning">(от большего к меньшему)</small></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-12">
                            <label for="date_discount" class="control-label">Дата акции:</label>
                            <input type="text" id="date_discount" required class="form-control date_discount" name="date_discount" />
                        </div>
                    </div>
                    <div class="row my-3">
                        <div class="checkbox-status col">
                            <label class="form-check-label" for="modal_status">
                                <input id="modal_status" type="checkbox" checked name="status" value="1" />
                                <div class="mr-3"></div>
                                Статус Вкл.
                            </label>
                        </div>
                    </div>
                    <fieldset class="form-group">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Сумма (больше)</th>
                                    <th>Коэффициент</th>
                                    <th>Удалить</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        Новый клиент
                                    </td>
                                    <td>
                                        <input required name="prices[nk][coefficient]" class="form-control" type="text" value="0.33" />
                                    </td>
                                    <td></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2">
                                        <button type="button" class="float-right btn add-operand btn-success"><i class="ti-plus"></i></button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </fieldset>
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
    <script src="design/manager/assets/plugins/moment/moment.js"></script>
    <script src="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script src="design/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    <script>
        function initDataPicker() {
            $('.date_discount').daterangepicker({
                autoApply: true,
                locale: {
                    format: 'YYYY.MM.DD'
                },
                default:''
            });
        }

        function loadData(resp){
            $('.preloader').show();
            $("#result").load('{$smarty.server.REQUEST_URI} #result > table', function (response, status, xhr) {
                $('.preloader').hide();
                showResult(resp);
                initDataPicker();
                $('#modal_discount').modal('hide');

                if (status == "error") {
                    alert('Произошла ошибка сервера подробности в консоли');
                    console.error('error load text: ' + xhr.status + " " + xhr.statusText);
                }
            });
        }

        function showResult(resp) {
            if (resp.success) {
                Swal.fire({
                    timer: 5000,
                    title: 'Результат редактирования',
                    text: 'Данные успешно отредактированы',
                    type: 'success',
                });
            } else {
                Swal.fire({
                    timer: 5000,
                    title: 'Результат редактирования',
                    text: 'Произошла ошибка',
                    type: 'error',
                });
            }
        }

        function deleteDiscount(id) {
            $.ajax({
                url: "{$smarty.server.REQUEST_URI}?action=deleteDiscount",
                data: {
                    id
                },
                method: 'POST',
                dataType: 'json',
                beforeSend: function () {
                    $('.preloader').show();
                },
                success: function(resp){
                    loadData(resp);
                }
            });
        }

        function deleteDiscountPhone(id, discount_insurer_id) {
            $.ajax({
                url: "{$smarty.server.REQUEST_URI}?action=deletePhone",
                data: {
                    id
                },
                method: 'POST',
                dataType: 'json',
                success: function(){
                    let $phoneList = $("#phones-list-" + discount_insurer_id),
                        $tr = $phoneList.closest('tr'),
                        $a = $phoneList.prev('a');

                        $tr.addClass('data-loading');
                        $a.prop('disabled', true);
                        $phoneList.load("{$smarty.server.REQUEST_URI}?action=getPhones&id=" + id, function () {
                            $phoneList.slideToggle();
                            $tr.removeClass('data-loading');
                            $a.prop('disabled', false);
                        });
                }
            });
        }

        function updateDiscount(id) {
            $.ajax({
                url: "{$smarty.server.REQUEST_URI}?action=updateDiscount&id=" + id,
                data: $("tr[data-discount_insure_id='" + id + "'] input").serialize(),
                method: 'POST',
                dataType: 'json',
                beforeSend: function () {
                    $('.preloader').show();
                },
                success: function(resp){
                    loadData(resp);
                }
            });
        }

        function loadPhonesList(id) {
            let $phoneList = $("#phones-list-" + id),
                $tr = $phoneList.closest('tr'),
                $a = $phoneList.prev('a');

            if($phoneList.is(":hidden")) {
                $tr.addClass('data-loading');
                $a.prop('disabled', true);
                $phoneList.load("{$smarty.server.REQUEST_URI}?action=getPhones&id=" + id, function () {
                    $phoneList.slideToggle();
                    $tr.removeClass('data-loading');
                    $a.prop('disabled', false);
                });
            } else {
                $phoneList.slideToggle().empty();
            }
        }

        $(document).on('change', '[name="upload"]', function () {
            let id = parseInt($(this).closest('[data-discount_insure_id]').data('discount_insure_id')),
                form_data = new FormData();

            form_data.append('upload', $(this).prop('files')[0]);

            $.ajax({
                url: "{$smarty.server.REQUEST_URI}?action=uploadPhones&id=" + id,
                data: form_data,
                type: 'POST',
                dataType: 'json',
                processData : false,
                contentType : false,
                beforeSend: function () {
                    $('.preloader').show();
                },
                success: function(resp){
                    $('.preloader').hide();
                    showResult(resp);
                }
            });
        });
        
        $(document).on('click', '.add-operand', function () {
           let $tbody = $(this).closest('table').find('tbody'),
               index = $tbody.find('tr').length;

            $tbody.append('<tr class="new-row"><td><input required name="prices[' + index + '][price]" class="form-control" type="number" /></td><td><input required name="prices[' + index + '][coefficient]" class="form-control" type="text" /></td><td><button type="button" class="btn delete-operand btn-outline-danger"><i class="ti-trash"></i></button></td></tr>');
        });

        $(document).on('click', '.delete-operand', function () {
            $(this).closest('tr').remove();
        });

        $(document).on('click', '.jsgrid-pager a', function (e) {
            e.preventDefault();
            let href = $(this).attr('href'),
                $tr = $(this).closest('tr');

            $tr.addClass('data-loading');

            $tr.find('[id^="phones-list-"]').load(href, function () {
                $tr.removeClass('data-loading');
            });
        });

        $("#modal_discount form").on('submit', function (e) {
            e.preventDefault();
            $.ajax({
                url: "{$smarty.server.REQUEST_URI}?action=addDiscount",
                data: $(this).serialize(),
                method: 'POST',
                dataType: 'json',
                beforeSend: function () {
                    $('.preloader').show();
                },
                success: function(resp){
                    loadData(resp);
                }
            });
        });

        $('#modal_discount').on('hidden.bs.modal', function (e) {
            $(e.target).find(".new-row").remove();
        })

        $(document).ready(function () {
            initDataPicker();
        });
    </script>
{/capture}
