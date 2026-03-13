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
                    {if $user->id}Профиль {$user->name|escape}
                    {else}Создать нового менеджера{/if}
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="managers">Менеджеры</a></li>
                    <li class="breadcrumb-item active">Профиль</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
                {if in_array('create_managers', $manager->permissions)}
                <a href="manager" class="btn float-right hidden-sm-down btn-success"><i class="mdi mdi-plus-circle"></i> Создать менеджера</a>
                {/if}
            </div>
        </div>

        <ul class="mt-2 nav nav-tabs" role="tablist">
            <li class="nav-item"> 
                <a class="nav-link active" data-toggle="tab" href="#info" role="tab" aria-selected="true">
                    <span class="hidden-sm-up"><i class="ti-home"></i></span> 
                    <span class="hidden-xs-down">Основные</span>
                </a> 
            </li>
            {if $manager->id}
            <li class="nav-item"> 
                <a class="nav-link" data-toggle="tab" href="#orders" role="tab" aria-selected="true">
                    <span class="hidden-sm-up"><i class="mdi mdi-animation"></i></span> 
                    <span class="hidden-xs-down">Заявки</span>
                </a> 
            </li>
            {/if}
        </ul>

        <div class="tab-content ">
            
            <div id="info" class="tab-pane active" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <div class="row">

                            <div class="col-md-12 col-lg-4 col-xlg-3">
                                <!--
                                <center class="mt-4"> <img src="design/{$settings->theme|escape}/assets/images/users/{$user->avatar}" class="img-circle" width="150" />
                                    <h4 class="card-title mt-2">{$user->name}</h4>
                                    <h6 class="card-subtitle">
                                        {$roles[$user->role]}
                                    </h6>
                                    {*}
                                    <div class="row text-center justify-content-md-center">
                                        <div class="col-4"><a href="javascript:void(0)" class="link"><i class="icon-people"></i> <font class="font-medium">254</font></a></div>
                                        <div class="col-4"><a href="javascript:void(0)" class="link"><i class="icon-picture"></i> <font class="font-medium">54</font></a></div>
                                    </div>
                                    {*}
                                </center>
                                -->
                                    {if $user->blocked}
                                    <div class="text-center">
                                        <h2 class="btn btn-danger btn-block">Заблокирован</h2>
                                    </div>
                                    {/if}
                                    
                                    
                                <div class="card-body"> 
                                    <small class="text-muted">Последний IP адрес</small>
                                    <h6>{$user->last_ip}</h6> 
                                    <small class="text-muted p-t-30 db">Последняя активность</small>
                                    <h6>{if $user->last_visit}{$user->last_visit|date} {$user->last_visit|time}{/if}</h6>                         


                                    {if $access_blocked_managers}
                                    <form class="text-center mt-5">
                                        <input type="hidden" name="action" value="blocked" />
                                        {if $user->blocked}
                                        <input name="blocked" value="0" type="hidden" />
                                        <button type="submit" class="btn-lg btn-success">Разблокировать</button>                                        
                                        {else}
                                        <input name="blocked" value="1" type="hidden" />
                                        <button type="submit" class="btn-lg btn-danger">Заблокировать</button>
                                        {/if}
                                        
                                    </form>
                                    {/if}

                                </div>
                            </div>

                            <div class="col-md-12 col-lg-8 col-xlg-9">
                                <div class="card">
                                    <div class="card-body">
                                        <form class="form-horizontal" method="POST">
                                            <input type="hidden" name="id" value="{$user->id}" />
                                            
                                            {if $errors}
                                            <div class="col-md-12">
                                                <ul class="alert alert-danger">
                                                    {if in_array('empty_role', (array)$errors)}<li>Выберите роль пользователя!</li>{/if}
                                                    {if in_array('empty_name', (array)$errors)}<li>Укажите имя!</li>{/if}
                                                    {if in_array('empty_login', (array)$errors)}<li>Укажите логин для входа!</li>{/if}
                                                    {if in_array('empty_password', (array)$errors)}<li>Укажите пароль!</li>{/if}
                                                </ul>
                                            </div>
                                            {/if}
                                            
                                            {if $message_success}
                                            <div class="col-md-12">
                                                <div class="alert alert-success">
                                                    {if $message_success == 'added'}Новый пользователь добавлен{/if}
                                                    {if $message_success == 'updated'}Данные сохранены{/if}
                                                </div>
                                            </div>
                                            {/if}
                                            
                                            <div class="form-group {if in_array('empty_role', (array)$errors)}has-danger{/if}">
                                                <label class="col-sm-12">Роль</label>
                                                <div class="col-sm-12">
                                                    {if in_array('managers', $manager->permissions)}
                                                    <select name="role" class="form-control form-control-line" required="true">
                                                        <option value=""></option>
                                                        {foreach $roles as $role => $role_name}
                                                        <option value="{$role}" {if $user->role == $role}selected="true"{/if}>{$role_name|escape}</option>
                                                        {/foreach}
                                                    </select>
                                                    {if in_array('empty_role', (array)$errors)}<small class="form-control-feedback">Выберите роль!</small>{/if}
                                                    {else}
                                                        <input type="hidden" name="role" value="{$user->role}" />
                                                        {foreach $roles as $role => $role_name}
                                                        {if $user->role == $role}
                                                            <p style="text-white">{$role_name|escape}</p>
                                                        {/if}
                                                        {/foreach}
                                                    {/if}
                                                </div>
                                            </div>
                                            
                                            <div class="form-group">
                                                
                                            </div>
                                                
                                            
                                            
                                            <div class="form-group {if in_array('empty_name', (array)$errors)}has-danger{/if}">
                                                <label class="col-md-12">Имя</label>
                                                <div class="col-md-12">
                                                    <input type="text" name="name" value="{$user->name|escape}" class="form-control form-control-line" required="true" />
                                                    {if in_array('empty_name', (array)$errors)}<small class="form-control-feedback">Укажите имя!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="form-group {if in_array('empty_name_1c', (array)$errors)}has-danger{/if}">
                                                <label class="col-md-12">Имя для обмена 1С</label>
                                                <div class="col-md-12">
                                                    <input type="text" name="name_1c" value="{$user->name_1c|escape}" class="form-control form-control-line" required="true" />
                                                    {if in_array('empty_name_1c', (array)$errors)}<small class="form-control-feedback">Укажите имя для обмена 1С!</small>{/if}
                                                </div>
                                            </div>
        
                                            <div class="form-group {if in_array('empty_login', (array)$errors)}has-danger{/if}">
                                                <label for="example-email" class="col-md-12">Логин для входа</label>
                                                <div class="col-md-12">
                                                    <input type="text" name="login" value="{$user->login|escape}" class="form-control form-control-line" required="true" />
                                                    {if in_array('empty_login', (array)$errors)}<small class="form-control-feedback">Укажите логин!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="form-group {if in_array('empty_password', (array)$errors)}has-danger{/if}">
                                                <label class="col-md-12">{if $user->id}Новый пароль{else}Пароль{/if}</label>
                                                <div class="col-md-12">
                                                    <input type="password" name="password" value="" class="form-control form-control-line" {if !$user->id}required="true"{/if} />
                                                    {if in_array('empty_password', (array)$errors)}<small class="form-control-feedback">Укажите пароль!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-12">Mango-office внутренний номер</label>
                                                <div class="col-md-12">
                                                    <input type="text" name="mango_number" value="{$user->mango_number}" class="form-control form-control-line" />
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-sm-12">
                                                    <button class="btn btn-success" type="submit">Сохранить</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                    <!-- Column -->
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="orders" class="tab-pane" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <table class="table">
                                    <tr>
                                        <th>Номер</th>
                                        <th>Номер 1С</th>
                                        <th>Дата</th>
                                        <th>Клиент</th>
                                        <th class="text-center">Сумма</th>
                                        <th class="text-center">Период</th>
                                        <th class="text-right">Статус CRM</th>
                                    </tr>
                                    {foreach $orders as $order}
                                    <tr>
                                        <td>
                                            <a href="order/{$order->order_id}" target="_blank">{$order->order_id}</a>
                                        </td>
                                        <td>
                                            {$order->id_1c}
                                        </td>
                                        <td>{$order->date|date} {$order->date|time}</td>
                                        <td>
                                            <a href="client/{$order->user_id}">{$order->lastname} {$order->firstname} {$order->patronymic}</a>
                                        </td>
                                        <td class="text-center">{$order->amount}</td>
                                        <td class="text-center">{$order->period}</td>
                                        <td class="text-right">
                                            {if $order->status == 1}Принята
                                            {elseif $order->status == 2}Одобрена
                                            {elseif $order->status == 3}Отказ
                                            {/if}
                                        </td>
                                    </tr>
                                    {/foreach}
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>

    </div>
    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->
    {include file='footer.tpl'}
    <!-- ============================================================== -->
</div>