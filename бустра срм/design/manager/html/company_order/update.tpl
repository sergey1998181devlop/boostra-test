{$meta_title="Заявка ИП и ООО №{$order->id}" scope=parent}

{capture name='page_scripts'}

{/capture}

{capture name='page_styles'}

{/capture}
<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-animation"></i> Заявка ИП и ООО №{$order->id}
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="{$config->root_url}/company_order">Заявки</a></li>
                    <li class="breadcrumb-item active">Редактирование заяки {$order->id}</li>
                </ol>
            </div>
        </div>
        {if $update_message}
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {$update_message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        {/if}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="post">
                            {foreach $fields as $key_field => $field}
                                {if !in_array($key_field, ['information', 'payment'])}
                                    {continue}
                                {/if}
                                <fieldset class="">
                                    <legend>{$field['name']}</legend>
                                    {foreach $field['values'] as $key => $value}
                                        {if in_array($key, ['created_at', 'updated_at', 'credit_target_name'])}
                                            {continue}
                                        {/if}
                                        <div class="mb-3">
                                            <label for="{$key}" class="form-label">{$value}</label>
                                            {if $key === 'status'}
                                                <select name="update_data[{$key}]" id="{$key}" class="form-control">
                                                    {foreach $statuses as $key => $status}
                                                        <option value="{$key}" {if $key == $order->status}selected{/if}>{$status|escape}</option>
                                                    {/foreach}
                                                </select>
                                                <span class="form-text text-white">
                                                      {if $order->status == 1}
                                                          <span class="badge bg-primary">{$statuses[$order->status]}</span>
                                                      {elseif $order->status == 2}
                                                          <span class="badge bg-success">{$statuses[$order->status]}</span>
                                                     {elseif $order->status == 3}
                                                          <span class="badge bg-danger">{$statuses[$order->status]}</span>
                                                      {/if}
                                                </span>
                                            {elseif $key === 'tax'}
                                                <select name="update_data[{$key}]" id="{$key}" class="form-control">
                                                    {foreach $taxes as $tax}
                                                        <option value="{$tax}" {if $tax == $order->tax}selected{/if}>{$tax|escape}</option>
                                                    {/foreach}
                                                </select>
                                            {elseif $key === 'co_credit_target_id'}
                                                <select name="update_data[{$key}]" id="{$key}" class="form-control">
                                                    {foreach $credit_targets as $credit_target}
                                                        <option value="{$credit_target->id}" {if $credit_target->id == $order->co_credit_target_id}selected{/if}>{$credit_target->name|escape}</option>
                                                    {/foreach}
                                                </select>
                                            {else}
                                                <input  type="text"
                                                        value="{$order->{$key}}"
                                                        name="update_data[{$key}]"
                                                        id="{$key}"
                                                        class="form-control"
                                                        placeholder="" />
                                            {/if}
                                        </div>
                                    {/foreach}
                                </fieldset>
                            {/foreach}
                            <button class="btn btn-primary">Сохранить</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {include file='footer.tpl'}
</div>
