<div class="col-md-6">
    <!-- Фото -->
    {if $scorista_step_files}
        <div class="label label-danger my-2">
            <h5>По оценке скоринга у клиента no_need_for_underwriter=1. Фото/работа не запрашиваются у клиента</h5>
        </div>
    {else}
        <form action="{url}" class="js-order-item-form mb-3 js-check-images" id="images_form" data-user="{$order->user_id}">

            <input type="hidden" name="action" value="images" />
            <input type="hidden" name="order_id" value="{$order->order_id}" />
            <input type="hidden" name="user_id" value="{$order->user_id}" />

            <h3 class="box-title">
                <span>Фотографии</span>
                <a href="javascript:void(0);" class="ml-3 js-open-comment-form" data-block="files">
                    <i class="mdi mdi-comment-text"></i>
                </a>
                <div class="spinner-border spinner-border-sm m-2 text-info float-right js-images-spinner" role="status"></div>
            </h3>
            <hr>

            <div class="row view-block {if $socials_error}hide{/if}">

                <div class="js-comments-block-files">{display_comments block='files'}</div>

                <ul class="col-md-12 list-inline order-images-list ">
                    {foreach $files as $file}
                        {if $file->visible == 1}
                            {if $file->status == 0}
                                {$item_class="border-warning"}
                                {$ribbon_class="ribbon-warning"}
                                {$ribbon_icon="fas fa-clock"}
                            {elseif $file->status == 1}
                                {$item_class="border-info"}
                                {$ribbon_class="ribbon-info"}
                                {$ribbon_icon="fas fa-question"}
                            {elseif $file->status == 2}
                                {$item_class="border-success border border-bg"}
                                {$ribbon_class="ribbon-success"}
                                {$ribbon_icon="fa fa-check-circle"}
                            {elseif $file->status == 3}
                                {$item_class="border-danger border"}
                                {$ribbon_class="ribbon-danger"}
                                {$ribbon_icon="fas fa-times-circle"}
                            {/if}
                            <li class="ribbon-wrapper border {$item_class} js-image-item" id="file_{$file->id}" data-id="{$file->id}" data-status="{$file->status}">
                                {*}<div class="ribbon ribbon-sm ribbon-corner {$ribbon_class}"><i class="{$ribbon_icon}"></i></div>{/*}
                                <a class="js-open-popup-image image-popup-fit-width js-event-add-click"  data-event="19" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}" data-fancybox="user_image" href="{$front_url}/files/users/{$file->name}">
                                    <img src="{$file->name|resize:100:100}" loading="lazy" alt="" class="img-responsive js-image-thumb" style="max-width:100px;max-height:100px;" />
                                </a>
                                <div class="label label-primary image-label" style="">
                                    {if $file->type == 'face1'}Профиль
                                    {elseif $file->type == 'face2'}Анфас
                                    {elseif $file->type == 'passport'}Документ
                                    {elseif $file->type == 'passport1'}Паспорт
                                    {elseif $file->type == 'passport2'}Прописка
                                    {elseif $file->type == 'passport3'}Брак
                                    {elseif $file->type == 'passport4'}Карта
                                    {elseif $file->type == 'selfi'}Селфи с паспортом
                                    {else}{$file->type}{/if}
                                </div>
                                <div class="label-exists js-label-exists">

                                    {*}
                                    <i class="text-success far fa-check-circle"></i>
                                    <i class="text-danger fas fa-ban"></i>
                                    {/*}
                                </div>

                                {if in_array($order->status, [1, 5, 6, 7]) && ($manager->id == $order->manager_id)}
                                    <div class="overlay-buttons">
                                        <a href="javascript:void(0);" data-id="{$file->id}" class="js-image-accept btn btn-xs js-event-add-click {if $file->status == 2}btn-success{else}btn-outline-success{/if}"  data-event="20" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <a href="javascript:void(0);" data-id="{$file->id}" class="js-image-reject btn btn-xs js-event-add-click {if $file->status == 3}btn-danger{else}btn-outline-danger{/if}" data-event="21" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </div>
                                {/if}
                            </li>
                        {/if}
                    {/foreach}
                </ul>

                <br>
                <br>

                <h4 class="box-title">
                    <span>Для внутреннего использования</span>
                    {if !$is_post}
                        <div class="spinner-border spinner-border-sm m-2 text-info float-right js-images-spinner" role="status"></div>
                    {/if}
                </h4>
                <hr>
                <ul class="col-md-12 list-inline order-images-list ">
                    {foreach $files as $file}
                        {if $file->visible == 0}
                            {if $file->status == 0}
                                {$item_class="border-warning"}
                                {$ribbon_class="ribbon-warning"}
                                {$ribbon_icon="fas fa-clock"}
                            {elseif $file->status == 1}
                                {$item_class="border-info"}
                                {$ribbon_class="ribbon-info"}
                                {$ribbon_icon="fas fa-question"}
                            {elseif $file->status == 2}
                                {$item_class="border-success border border-bg"}
                                {$ribbon_class="ribbon-success"}
                                {$ribbon_icon="fa fa-check-circle"}
                            {elseif $file->status == 3}
                                {$item_class="border-danger border"}
                                {$ribbon_class="ribbon-danger"}
                                {$ribbon_icon="fas fa-times-circle"}
                            {/if}
                            <li class="ribbon-wrapper border {$item_class} js-image-item" id="file_{$file->id}" data-id="{$file->id}">
                                {*}<div class="ribbon ribbon-sm ribbon-corner {$ribbon_class}"><i class="{$ribbon_icon}"></i></div>{/*}
                                <a class="js-open-popup-image image-popup-fit-width js-event-add-click"  data-event="19" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}" data-fancybox="user_image" href="{$front_url}/files/users/{$file->name}">
                                    <img src="{$file->name|resize:100:100}" loading="lazy" alt="" class="img-responsive js-image-thumb" style="max-width:100px;max-height:100px;" />
                                </a>
                                <div class="label label-primary image-label" style="">
                                    {if $file->type == 'face1'}Профиль
                                    {elseif $file->type == 'face2'}Анфас
                                    {elseif $file->type == 'passport'}Документ
                                    {elseif $file->type == 'passport1'}Паспорт
                                    {elseif $file->type == 'passport2'}Прописка
                                    {elseif $file->type == 'passport3'}Брак
                                    {elseif $file->type == 'passport4'}Карта
                                    {elseif $file->type == 'selfi'}Селфи
                                    {else}{$file->type}{/if}
                                </div>
                                <div class="label-exists js-label-exists">

                                    {*}
                                    <i class="text-success far fa-check-circle"></i>
                                    <i class="text-danger fas fa-ban"></i>
                                    {/*}
                                </div>

                                {if in_array($order->status, [1, 5, 6, 7]) && ($manager->id == $order->manager_id)}
                                    <div class="overlay-buttons">
                                        <a href="javascript:void(0);" data-id="{$file->id}" class="js-image-accept btn btn-xs js-event-add-click {if $file->status == 2}btn-success{else}btn-outline-success{/if}"  data-event="20" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <a href="javascript:void(0);" data-id="{$file->id}" class="js-image-reject btn btn-xs js-event-add-click {if $file->status == 3}btn-danger{else}btn-outline-danger{/if}" data-event="21" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </div>
                                {/if}
                            </li>
                        {/if}
                    {/foreach}
                </ul>
            </div>

            <div class="row edit-block {if !$images_error}hide{/if}">
                {foreach $files as $file}
                    <div class="col-md-4 col-lg-3 col-xlg-3">
                        <div class="card card-body">
                            <div class="row">
                                <div class="col-md-6 col-lg-4 text-center">
                                    <a class="js-open-popup-image image-popup-fit-width" href="{$front_url}/files/users/{$file->name}">
                                        <img src="{$front_url}/files/users/{$file->name}" alt="" class="img-responsive" />
                                    </a>
                                </div>
                                <div class="col-md-6 col-lg-8">
                                    <div class="form-group">
                                        <label class="control-label">Статус</label>
                                        <select id="status_{$file->id}" class="form-control custom-select js-file-status" name="status[{$file->id}]">
                                            <option value="0" {if $file->status == 0}selected="true"{/if}>Новый</option>
                                            <option value="1" {if $file->status == 1}selected="true"{/if}>На проверке</option>
                                            <option value="2" {if $file->status == 2}selected="true"{/if}>Принят</option>
                                            <option value="3" {if $file->status == 3}selected="true"{/if}>Отклонен</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                {/foreach}
                <div class="col-md-12">
                    <div class="form-actions">
                        <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                        <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                    </div>
                </div>
            </div>
        </form>
    {/if}

    <form
            method="POST"
            action="{$config->front_url}/ajax/upload_joxi.php"
            class="row"
            enctype="multipart/form-data"
    >
        <br>

        <input
                type="file"
                id="file_upload"
                name="file_upload"
                class="form-control col-md-4"
                placeholder="Выберите файл"
                style="margin: 0px 10px 10px 20px;"><br><br>


        <select id="type" name="type" class="form-control col-md-3" placeholder="type...">
            <option selected>выберите тип...</option>

            <option value="face1">Профиль</option>
            <option value="face2">Анфас</option>
            <option value="passport">Документ</option>
            <option value="passport1">Паспорт</option>
            <option value="passport2">Прописка</option>
            <option value="passport3">Брак</option>
            <option value="passport4">Карта</option>
            <option value="selfi">Селфи</option>
        </select><br><br>

        <input type="hidden" name="action" value="add">
        <input type="hidden" name="order_id" value="{$order->order_id}">
        <input type="hidden" name="user_id" value="{$order->user_id}">
        <input type="hidden" name="token" value="123ighdfgys_dfgd_1">

        <input type="submit" value="Добавить" class="btn btn-large btn-primary col-md-2" style="
                                                    height: 36px;
                                                    left: 10px;
                                                    width: 226px;
                                                ">
    </form>

    <form action="{url}" class="js-order-item-form mb-3" method="POST" id="socials_form">

        <input type="hidden" name="action" value="socials" />
        <input type="hidden" name="order_id" value="{$order->order_id}" />
        <input type="hidden" name="user_id" value="{$order->user_id}" />

        <h3 class="box-title">
            {if in_array($order->status, [1, 5, 6, 7]) && ($manager->id == $order->manager_id)}
                <a href="javascript:void(0);" class="js-edit-form js-event-add-click"  data-event="18" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}">
                    <span>Ссылки на профили в соц. сетях</span>
                </a>
            {else}
                <span>Ссылки на профили в соц. сетях</span>
            {/if}
            <a href="javascript:void(0);" class="ml-3 js-open-comment-form" data-block="socials">
                <i class="mdi mdi-comment-text"></i>
            </a>
        </h3>
        <hr>

        <div class="row view-block {if $socials_error}hide{/if}">

            <div class="js-comments-block-socials">{display_comments block='socials'}</div>

            {if $order->social_fb}
                <div class="col-md-12">
                    <div class="form-group row">
                        <label class="control-label col-md-4">Facebook:</label>
                        <div class="col-md-8">
                            <p class="form-control-static">
                                <strong>{$order->social_fb|escape}</strong>
                                <a href="{$order->social_fb|escape}" target="_blank" title="Открыть соцсеть"><i class="fas fa-external-link-square-alt"></i></a>
                            </p>
                        </div>
                    </div>
                </div>
            {/if}
            {if $order->social_inst}
                <div class="col-md-12">
                    <div class="form-group row">
                        <label class="control-label col-md-4">Instagram:</label>
                        <div class="col-md-8">
                            <p class="form-control-static">
                                <strong>{$order->social_inst|escape}</strong>
                                <a href="{$order->social_inst|escape}" target="_blank" title="Открыть соцсеть"><i class="fas fa-external-link-square-alt"></i></a>
                            </p>
                        </div>
                    </div>
                </div>
            {/if}
            {if $order->social_vk}
                <div class="col-md-12">
                    <div class="form-group row">
                        <label class="control-label col-md-4">В Контакте:</label>
                        <div class="col-md-8">
                            <p class="form-control-static">
                                <strong>{$order->social_vk|escape}</strong>
                                <a href="{$order->social_vk|escape}" target="_blank" title="Открыть соцсеть"><i class="fas fa-external-link-square-alt"></i></a>
                            </p>
                        </div>
                    </div>
                </div>
            {/if}
            {if $order->social_ok}
                <div class="col-md-12">
                    <div class="form-group row">
                        <label class="control-label col-md-4">Одноклассники:</label>
                        <div class="col-md-8">
                            <p class="form-control-static">
                                <strong>{$order->social_ok|escape}</strong>
                                <a href="{$order->social_ok|escape}" target="_blank" title="Открыть соцсеть"><i class="fas fa-external-link-square-alt"></i></a>
                            </p>
                        </div>
                    </div>
                </div>
            {/if}
        </div>

        <div class="row edit-block {if !$socials_error}hide{/if}">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label">Facebook</label>
                    <input type="text" class="form-control" name="social_fb" value="{$order->social_fb|escape}" placeholder="" />
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label">Instagram</label>
                    <input type="text" class="form-control" name="social_inst" value="{$order->social_inst|escape}" placeholder="" />
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label">В Контакте</label>
                    <input type="text" class="form-control" name="social_vk" value="{$order->social_vk|escape}" placeholder="" />
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label">Одноклассники</label>
                    <input type="text" class="form-control" name="social_ok" value="{$order->social_ok|escape}" placeholder="" />
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-actions">
                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                    <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                </div>
            </div>
        </div>
    </form>
</div>