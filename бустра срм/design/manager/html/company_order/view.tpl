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
                    <li class="breadcrumb-item active">Заявка {$order->id}</li>
                </ol>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-auto">
                <a class="btn btn-warning" href="{$config->root_url}/company_order/update/{$order->id}">
                    <i class="mr-1 mdi mdi-pencil-box"></i>
                    Редактировать
                </a>
            </div>
            <div class="col-auto pl-0">
                <a class="btn btn-primary" target="_blank" href="{$assignment_doc_url}">
                    <i class="mr-1 mdi mdi-file-pdf"></i>
                    Поручение
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        {foreach $fields as $key_field => $field}
                            {if $field@iteration > 1}
                                <hr />
                            {/if}
                            <fieldset class="">
                                <legend>{$field['name']}</legend>
                                {foreach $field['values'] as $key => $value}
                                    <div class="mb-3">
                                        <label for="{$key}" class="form-label">{$value}</label>
                                        {if $key == 'co_credit_target_id'}
                                            <input type="text" value="{$order->credit_target_name}" name="{$key_field}[{$key}]" readonly id="{$key}" class="form-control" placeholder="" />
                                        {elseif in_array($key, ['ogrnip', 'company_form_email'])}
                                            <input type="text" value="{$user_data[$key]}" name="{$key_field}[{$key}]" readonly id="{$key}" class="form-control" placeholder="" />
                                        {elseif in_array($key_field, ['information', 'payment'])}
                                            <input type="text" value="{$order->{$key}}" name="{$key_field}[{$key}]" readonly id="{$key}" class="form-control" placeholder="" />
                                        {else}
                                            <input type="text" value="{$client->$key}" name="{$key_field}[{$key}]" readonly id="{$key}" class="form-control" placeholder="" />
                                        {/if}
                                        {if $key === 'status'}
                                            <span class="form-text text-white">
                                                  {if $order->status == 1}
                                                      <span class="badge bg-primary">{$statuses[$order->status]}</span>
                                                  {elseif $order->status == 2}
                                                      <span class="badge bg-success">{$statuses[$order->status]}</span>
                                                 {elseif $order->status == 3}
                                                      <span class="badge bg-danger">{$statuses[$order->status]}</span>
                                                 {/if}
                                            </span>
                                        {/if}
                                    </div>
                                {/foreach}
                            </fieldset>
                        {/foreach}
                    </div>
                </div>
            </div>
        </div>
    </div>
    {include file='footer.tpl'}
</div>
