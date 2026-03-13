<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-incognito"></i>
                    <span>Менеджеры КЦ</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item">Менеджеры</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Список менеджеров КЦ</h4>
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
                                {foreach $ccManagers as $manager}
                                    <tr>
                                        <td>{$manager->id}</td>
                                        <td><a href="manager/{$manager->id}">{$manager->name}</a></td>
                                        <td>{$manager->login}</td>
                                        <td>{$manager->last_ip}</td>
                                        <td>{if $manager->last_visit}{$manager->last_visit|date} {$manager->last_visit|time}{/if}</td>
                                        <td>
                                            <select class="form-control form-control-line" id="rules_{$manager->id}"
                                                    onchange="change_rules({$manager->id})">
                                                <option value="contact_center" {if $manager->role == 'contact_center'}selected{/if}>Исходящий КЦ</option>
                                                <option value="contact_center_plus" {if $manager->role == 'contact_center_plus'} selected {/if}>
                                                    Исходящий КЦ с расширенными правами
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
    </div>
    {include file='footer.tpl'}
</div>