{$meta_title = 'Автоматизация ошибок' scope=parent}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    Автоматизация ошибок
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Автоматизация ошибок</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
            </div>
        </div>
        <form class="" method="POST">
            <div class="card">
                <div class="card-body">
                    {foreach $items as $item}
                        <input type="hidden" name="items[{$item->id}][id]" value="{$item->id}">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="d-flex align-items-baseline pb-2">
                                    <h3 class="box-title mb-0">
                                        {$item->name}
                                    </h3>
                                    <small class="ml-2">
                                        {if $item->is_auto_active}
                                            Активируется автоматически по триггеру
                                        {else}
                                            Активируется вручную
                                        {/if}
                                    </small>
                                </div>
                                <div class="form-group mb-3">
                                    <div class="row pb-2">
                                        <div class="col-12 col-md-6">
                                            <label>Активен?</label>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <select class="form-control" name="items[{$item->id}][is_active]">
                                                <option value="0" {if !$item->is_active}selected{/if}>Выключено</option>
                                                <option value="1" {if $item->is_active}selected{/if}>Активно</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row pb-2">
                                        <div class="col-12 col-md-6">
                                            <label>Где отображать?</label>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <select class="form-control" name="items[{$item->id}][show_at]">
                                                <option value="main" {if $item->show_at === 'main'}selected{/if}>На
                                                    главной
                                                </option>
                                                <option value="cabinet" {if $item->show_at === 'cabinet'}selected{/if}>
                                                    Кабинет
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row pb-2">
                                        <div class="col-12 col-md-6">
                                            <label>Текст сообщения</label>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <textarea class="form-control"
                                                      name="items[{$item->id}][text]">{$item->text}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr class="mt-3 mb-4"/>
                    {/foreach}


                </div>
            </div>


            <div class="col-12 grid-stack-item" data-gs-x="0" data-gs-y="0" data-gs-width="12">
                <div class="form-actions">
                    <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Сохранить</button>
                </div>
            </div>
        </form>
    </div>
    {include file='footer.tpl'}
</div>
