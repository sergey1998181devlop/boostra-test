{$meta_title='Список заявок ИП и ООО' scope=parent}

{capture name='page_scripts'}

{/capture}

{capture name='page_styles'}

{/capture}
<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-animation"></i> Заявки ИП и ООО
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Заявки</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <!-- Column -->
                {if $delete_message}
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {$delete_message}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                {/if}
                <div class="card">
                    <div class="card-body">
                        <div class="clearfix">
                            <h4 class="card-title">Список заявок </h4>
                        </div>
                        <div id="basicgrid" class="jsgrid" style="position: relative; width: 100%;">
                            <div class="jsgrid-grid-body">
                                <form method="get">
                                    <table class="jsgrid-table table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>id</th>
                                                <th>ИНН</th>
                                                <th>Номер телефона</th>
                                                <th>Сумма</th>
                                                <th>Дата</th>
                                                <th>Статус</th>
                                                <th>Действия</th>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td>
                                                    <input name="filter[user][inn]" class="form-control"/>
                                                </td>
                                                <td>
                                                    <input name="filter[user][phone_mobile]" class="form-control"/>
                                                </td>
                                                <td></td>
                                                <td></td>
                                                <td>
                                                    <select name="filter[status]" class="form-control input-sm">
                                                        <option value=""></option>
                                                        {foreach $statuses as $key => $status}
                                                            <option value="{$key}"
                                                                    {if $key === $filter['status']}selected{/if}>{$status|escape}</option>
                                                        {/foreach}
                                                    </select>
                                                </td>
                                                <td>
                                                    <button type="submit" class="btn btn-primary">Отфильтровать</button>
                                                </td>
                                            </tr>
                                        </thead>
                                        {if $items}
                                            <tbody>
                                                {foreach $items as $order}
                                                    <tr>
                                                        <td>{$order->id}</td>
                                                        <td>{$order->inn}</td>
                                                        <td>{$order->phone_mobile}</td>
                                                        <td>{$order->amount}</td>
                                                        <td>{$order->created_at}</td>
                                                        <td>
                                                            {if $order->status == 1}
                                                                <span class="badge bg-primary">{$statuses[$order->status]}</span>
                                                            {elseif $order->status == 2}
                                                                <span class="badge bg-success">{$statuses[$order->status]}</span>
                                                            {elseif $order->status == 3}
                                                                <span class="badge bg-danger">{$statuses[$order->status]}</span>
                                                            {/if}
                                                        </td>
                                                        <td>
                                                            <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                                                <button onclick="deleteOrder({$order->id})" type="button" class="btn btn-danger"><i class="ti-trash"></i></button>
                                                                <a href="{$config->root_url}/company_order/update/{$order->id}" class="btn btn-warning"><i class="ti-pencil"></i></a>
                                                                <a href="{$config->root_url}/company_order/view/{$order->id}" class="btn btn-primary"><i class="ti-eye"></i></a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                {/foreach}
                                            </tbody>
                                        {else}
                                            <tfoot>
                                                <tr>
                                                    <td colspan="8">
                                                        <h4 class="text-danger">Заявок нет</h4>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        {/if}
                                    </table>
                                </form>
                            </div>
                            {include file='html_blocks/pagination.tpl'}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {include file='footer.tpl'}
</div>
{capture name='page_scripts'}
    <script>
        function deleteOrder(id) {
            Swal.fire({
                html: "Вы действительно хотите удалить заявку?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Да, удалить!",
                cancelButtonText: "Отмена",
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    $.ajax({
                        type: 'POST',
                        url:'{$config->root_url}/company_order/delete/' + id,
                        beforeSend: () => {
                            $('.preloader').show();
                        },
                        success:() => {
                            location.reload()
                        }
                    })
                },
                allowOutsideClick: () => !Swal.isLoading()
            });
        }
    </script>
{/capture}
