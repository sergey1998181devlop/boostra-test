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
                    <i class="mdi mdi-incognito"></i>
                    <span>Менеджеры</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item">Менеджеры</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
                {if in_array('create_managers', $manager->permissions)}
                <a href="manager" class="btn float-right hidden-sm-down btn-success"><i class="mdi mdi-plus-circle"></i> Создать менеджера</a>
                {/if}
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Start Page Content -->
        <!-- ============================================================== -->
        <!-- Row -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Список менеджеров</h4>
                                <div class="table-responsive">
                                    <table class="table no-wrap">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Имя</th>
                                                <th>Логин</th>
                                                <th>IP адрес</th>
                                                <th>Активность</th>
                                                <th>Роль</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {foreach $managers as $m}
                                            <tr>
                                                <td>{$m->id}</td>
                                                <td><a href="manager/{$m->id}">{$m->name}</a></td>
                                                <td>{$m->login}</td>
                                                <td>{$m->last_ip}</td>
                                                <td>{if $m->last_visit}{$m->last_visit|date} {$m->last_visit|time}{/if}</td>
                                                <td>
                                                     {if $m->role == 'developer'}<span class="label label-danger">Разработчик</span> 
                                                    {elseif $m->role == 'admin'}<span class="label label-success">{$roles[$m->role]|escape}</span>
                                                    {elseif $m->role == 'opr'}<span class="label label-success">Сотрудник ОПР</span>
                                                    {elseif $m->role == 'user'}<span class="label label-primary">Менеджер</span>
                                                    {elseif $m->role == 'ts_operator'}<span class="label label-primary">Оператор ТП</span>
                                                    {elseif $m->role == 'verificator'}<span class="label label-warning">Верификатор</span>
                                                    {elseif $m->role == 'edit_verificator'}<span class="label label-warning">Верификатор с расширенными правами</span>
                                                    {elseif $m->role == 'analitic'}<span class="label label-info">Аналитик</span>
                                                    {elseif $m->role == 'chief_verificator'}<span class="label label-info">Шеф-Верификатор</span>
                                                    {elseif $m->role == 'contact_center'}<span class="label label-info">Исходящий КЦ</span>
                                                    {elseif $m->role == 'sotrudnik_cc'}<span class="label label-info">Сотрудник КЦ</span>
                                                    {elseif $m->role == 'verificator_cc'}<span class="label label-info">Верификатор КЦ</span>
                                                    {elseif $m->role == 'yurist'}<span class="label label-info">Юрист</span>
                                                    {elseif $m->role == 'chief_cc'}<span class="label label-info">Нач. отдела по претензионной работе</span>
                                                    {elseif $m->role == 'individuals'}<span class="label label-info">Инд. рассмотрение</span>
                                                    {else}<span class="label label-danger">{$roles[$m->role]|escape}</span>{/if}
                                                    
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
        <!-- Row -->
    </div>
    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- footer -->
    <!-- ============================================================== -->
    {include file='footer.tpl'}
    <!-- ============================================================== -->
    <!-- End footer -->
    <!-- ============================================================== -->
</div>