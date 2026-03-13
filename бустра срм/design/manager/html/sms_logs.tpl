{$meta_title = 'Журнал SMS сообщений' scope=parent}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">Журнал SMS сообщений</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Журнал SMS сообщений</li>
                </ol>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Номер телефона</th>
                                        <th>ФИО клиента</th>
                                        <th>Sms сообщение</th>
                                        <th>Дата время</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach $items as $item}
                                    <tr>
                                        <td>{$item->phone}</td>
                                        <td>
                                            {if $item->user_id}
                                                <a href="client/{$item->user_id}" target="_blank">
                                                    {$item->lastname} {$item->firstname} {$item->patronymic}
                                                </a>
                                            {else}
                                                -
                                            {/if}
                                        </td>
                                        <td>{$item->message}</td>
                                        <td>{$item->created|date} {$item->created|time}</td>
                                    </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                        </div>

                        {include file='html_blocks/pagination.tpl'}

                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>
