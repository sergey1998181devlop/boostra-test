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
                    <span>Верификатор</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item">Менеджеры</li>
                </ol>
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
                        <h4 class="card-title">Список верификаторов</h4>
                        <div class="table-responsive">
                            <table class="table no-wrap">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Имя</th>
                                    <th>Логин</th>
                                    <th>IP адрес</th>
                                    <th>Активность</th>
                                    <th>Изменить права</th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach $verificators as $m}
                                    <tr>
                                        <td>{$m->id}</td>
                                        <td><a href="manager/{$m->id}">{$m->name}</a></td>
                                        <td>{$m->login}</td>
                                        <td>{$m->last_ip}</td>
                                        <td>{if $m->last_visit}{$m->last_visit|date} {$m->last_visit|time}{/if}</td>
                                        <td>
                                            <select class="form-control form-control-line" id="rules_{$m->id}"
                                                    onchange="change_rules({$m->id})">
                                                <option value="verificator" {if $m->role == 'verificator'}selected{/if}>Верификатор</option>
                                                <option value="edit_verificator" {if $m->role == 'edit_verificator'} selected {/if} >С
                                                    расширенными правами
                                                </option>
                                            </select>
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